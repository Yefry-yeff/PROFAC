<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\Reportes\AnaliticaProductosExport;
use App\Exports\Reportes\ReporteExpoOfertaExport;
use App\Services\Reportes\ReporteExpoDetalleService;
use App\Support\ExpoConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reporte BI Dinámico para Expo.
 *
 * Capa de SOLO CONSULTA/ANÁLISIS sobre el modelo de datos existente de
 * Expo/Oferta/Prefactura/Facturación. No modifica ningún proceso de negocio.
 *
 * Fuente de verdad usada:
 *  - "Ofertado"  => cotizacion_has_producto (chp), líneas de la oferta.
 *  - "Facturado" => venta_has_producto (vhp) enlazado por
 *                   vhp.cotizacion_has_producto_id = chp.id, INNER JOIN
 *                   factura (f.estado_venta_id = 1 => factura activa/no anulada).
 *    Se evita usar prefactura_has_producto para totales porque una misma
 *    línea ofertada puede facturarse parcialmente en varias facturas; usar
 *    directamente venta_has_producto es lo único que no duplica montos.
 *  - Costo real de línea facturada => precios_producto_carga.costoproducto,
 *    tomando primero el "price load" usado al facturar (vhp.precios_producto_carga_id)
 *    y si no existe, el de la oferta (chp.precios_producto_carga_id), y como
 *    último recurso producto.costo_promedio.
 */
class ReporteExpo extends Component
{
    /**
     * Flujos verificados manualmente en BD como registros DUPLICADOS del
     * mismo cliente durante la Expo (por error el cliente quedó registrado
     * en más de un flujo distinto). Se excluyen POR COMPLETO del reporte.
     * El flujo que sí se conserva ya arrastra, de forma natural, su propia
     * oferta vigente (la más reciente registrada en su cadena):
     *
     *   - 4508, 4476  -> duplicados; se conserva 4487 (oferta vigente 40865)
     *   - 4504        -> duplicado; se conserva 4489 (oferta vigente 40856)
     *   - 4493        -> duplicado; se conserva 4497 (oferta vigente 40837)
     *   - 4485        -> duplicado; se conserva 4498 (oferta vigente 40834)
     *   - 4490        -> caso normal, sin duplicado (oferta vigente 40862)
     *
     * Validado contra la base de datos y confirmado con el usuario el
     * 2026-09-02 (el caso de 4485/4498 se corrigió: se conserva 4498).
     */
    private const FLUJOS_EXCLUIDOS_DUPLICADOS = [4508, 4476, 4504, 4493, 4485];

    private const ESTADOS_VALIDOS = [
        'PENDIENTE_FACTURACION',
        'FACTURACION_PARCIAL',
        'PENDIENTE_LIQUIDACION',
        'LIQUIDADA',
    ];

    public $titulo = 'Reporte BI de Expo';

    /** Caché en memoria (por request) de cotizacion_id vigentes por expo. */
    private array $cacheVigentes = [];

    public function mount()
    {
        //
    }

    public function render()
    {
        $expos = DB::table('expo')
            ->orderByDesc('fecha_inicio')
            ->get(['id', 'nombre', 'estado', 'fecha_inicio', 'fecha_fin']);

        $expoActivo = ExpoConfig::activa();

        return view('livewire.reportes.reporteexpo', [
            'expos' => $expos,
            'expoActivoId' => $expoActivo->id ?? optional($expos->first())->id,
        ])->layout('layouts.app', ['title' => 'Reporte BI de Expo']);
    }

    // ═══════════════════════════════ Helpers internos ═══════════════════

    private function expoIdDesdeRequest(Request $r): int
    {
        $id = (int) ($r->expo_id ?? 0);
        if ($id > 0) {
            return $id;
        }

        $activo = ExpoConfig::activa();
        if ($activo) {
            return (int) $activo->id;
        }

        return (int) (DB::table('expo')->orderByDesc('fecha_inicio')->value('id') ?? 0);
    }

    /**
     * Devuelve los cotizacion_id que representan la oferta VIGENTE de cada
     * flujo de la Expo, aplicando:
     *  1. Exclusión total de los flujos duplicados conocidos.
     *  2. Para cada flujo restante, solo su oferta más reciente cuenta
     *     (las anteriores son versiones de carrito superadas).
     *  3. Ofertas de Expo sin ningún flujo asociado (registro directo) se
     *     incluyen siempre, pues no hay forma de que estén duplicadas.
     */
    private function cotizacionIdsVigentes(int $expoId): array
    {
        if (isset($this->cacheVigentes[$expoId])) {
            return $this->cacheVigentes[$expoId];
        }

        $excluidos = implode(',', array_map('intval', self::FLUJOS_EXCLUIDOS_DUPLICADOS));

        $rows = DB::select("
            SELECT ec.cotizacion_id
            FROM expo_cotizacion ec
            WHERE ec.expo_id = ?
              AND (
                    NOT EXISTS (
                        SELECT 1 FROM historico_flujo hf
                        WHERE hf.tramite_id = ec.cotizacion_id AND hf.tipo_tramite_id = 2
                    )
                    OR ec.cotizacion_id IN (
                        SELECT hf.tramite_id
                        FROM historico_flujo hf
                        INNER JOIN (
                            SELECT flujo_id, MAX(id) AS max_id
                            FROM historico_flujo
                            WHERE tipo_tramite_id = 2 AND flujo_id NOT IN ($excluidos)
                            GROUP BY flujo_id
                        ) ult ON ult.flujo_id = hf.flujo_id AND ult.max_id = hf.id
                        WHERE hf.tipo_tramite_id = 2
                    )
              )
        ", [$expoId]);

        $ids = array_map(fn ($row) => (int) $row->cotizacion_id, $rows);
        if (empty($ids)) {
            $ids = [0];
        }

        return $this->cacheVigentes[$expoId] = $ids;
    }

    private function inVigentes(int $expoId): string
    {
        return implode(',', $this->cotizacionIdsVigentes($expoId));
    }

    private function costoUnitarioExpr(): string
    {
        return 'COALESCE(ppc_vhp.costoproducto, ppc.costoproducto, p.costo_promedio, 0)';
    }

    private function idsDesdeRequest(Request $r, string $plural, string $singular): array
    {
        $valor = $r->input($plural, $r->input($singular, []));
        $valores = is_array($valor) ? $valor : preg_split('/\s*,\s*/', (string) $valor, -1, PREG_SPLIT_NO_EMPTY);

        return collect($valores)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    }

    /** FROM + JOINs comunes al lado "ofertado" (sin facturación). 1 fila = 1 línea ofertada. */
    private function joinsOfertado(): string
    {
        return "
            FROM cotizacion_has_producto chp
            INNER JOIN cotizacion c ON c.id = chp.cotizacion_id
            INNER JOIN producto p ON p.id = chp.producto_id
            LEFT JOIN marca m ON m.id = p.marca_id
            LEFT JOIN precios_producto_carga ppc ON ppc.id = chp.precios_producto_carga_id
            LEFT JOIN categoria_precios cp ON cp.id = ppc.categoria_precios_id
            LEFT JOIN categoria_producto cat ON cat.id = ppc.categoria_producto_id
        ";
    }

    /** JOINs adicionales para el lado "facturado". INNER: solo líneas realmente facturadas. */
    private function joinsFacturado(): string
    {
        return "
            INNER JOIN venta_has_producto vhp ON vhp.cotizacion_has_producto_id = chp.id
            INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_venta_id = 1
            LEFT JOIN precios_producto_carga ppc_vhp ON ppc_vhp.id = vhp.precios_producto_carga_id
        ";
    }

    private function joinExpoCotizacion(int $expoId): string
    {
        return "LEFT JOIN expo_cotizacion ec ON ec.cotizacion_id = c.id AND ec.expo_id = {$expoId}";
    }

    private function whereVigentes(int $expoId): string
    {
        return "WHERE chp.cotizacion_id IN ({$this->inVigentes($expoId)})";
    }

    /**
     * Filtros opcionales de UI. Todos los valores se validan/castean antes
     * de interpolarse (enteros con (int), fechas con regex, estado con
     * whitelist) para evitar inyección SQL.
     */
    private function filtrosExtra(Request $r): string
    {
        $sql = '';

        $marcas = $this->idsDesdeRequest($r, 'marca_ids', 'marca_id');
        if ($marcas) {
            $sql .= ' AND p.marca_id IN (' . implode(',', $marcas) . ')';
        }
        $escalas = $this->idsDesdeRequest($r, 'escala_ids', 'escala_id');
        if ($escalas) {
            $sql .= ' AND cp.id IN (' . implode(',', $escalas) . ')';
        }
        $vendedores = $this->idsDesdeRequest($r, 'vendedor_ids', 'vendedor_id');
        if ($vendedores) {
            $sql .= ' AND c.vendedor IN (' . implode(',', $vendedores) . ')';
        }
        $teleasesores = $this->idsDesdeRequest($r, 'teleasesor_ids', 'teleasesor_id');
        if ($teleasesores) {
            $sql .= ' AND c.users_id IN (' . implode(',', $teleasesores) . ')';
        }
        if ($r->producto_id !== null && $r->producto_id !== '') {
            $sql .= ' AND p.id = ' . (int) $r->producto_id;
        }
        if ($r->cliente_id !== null && $r->cliente_id !== '') {
            $sql .= ' AND c.cliente_id = ' . (int) $r->cliente_id;
        }
        if ($r->estado && in_array($r->estado, self::ESTADOS_VALIDOS, true)) {
            $sql .= ' AND ec.estado = ' . DB::getPdo()->quote($r->estado);
        }
        if ($r->fecha_desde && preg_match('/^\d{4}-\d{2}-\d{2}$/', $r->fecha_desde)) {
            $sql .= ' AND c.fecha_emision >= ' . DB::getPdo()->quote($r->fecha_desde);
        }
        if ($r->fecha_hasta && preg_match('/^\d{4}-\d{2}-\d{2}$/', $r->fecha_hasta)) {
            $sql .= ' AND c.fecha_emision <= ' . DB::getPdo()->quote($r->fecha_hasta);
        }

        return $sql;
    }

    // ═══════════════════════════════ KPIs ════════════════════════════════

    public function kpis(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $rowOfertado = DB::selectOne("
            SELECT
                COUNT(DISTINCT c.id) AS num_ofertas,
                COUNT(DISTINCT COALESCE(c.cliente_id, c.nombre_cliente)) AS clientes_unicos,
                COALESCE(SUM(chp.precio_unidad * chp.cantidad), 0) AS total_ofertado,
                COALESCE(SUM(chp.sub_total), 0) AS total_neto,
                COALESCE(SUM(GREATEST((chp.precio_unidad * chp.cantidad) - chp.sub_total, 0)), 0) AS total_descuento,
                COALESCE(SUM(COALESCE(ppc.precio_base_venta, 0) * chp.cantidad), 0) AS total_costo
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
        ");

        $costoExpr = $this->costoUnitarioExpr();
        $rowFacturado = DB::selectOne("
            SELECT
                COUNT(DISTINCT f.id) AS num_facturas,
                COALESCE(SUM(vhp.sub_total_s), 0) AS total_facturado,
                COALESCE(SUM(GREATEST((vhp.precio_unidad * vhp.cantidad_s) - vhp.sub_total_s, 0)), 0) AS total_descuento,
                COALESCE(SUM(($costoExpr) * vhp.cantidad_s), 0) AS total_costo
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
        ");

        $totalOfertado = (float) $rowOfertado->total_ofertado;
        $totalNeto = (float) $rowOfertado->total_neto;
        $totalFacturado = (float) $rowFacturado->total_facturado;
        $totalCosto = (float) $rowOfertado->total_costo;
        $utilidad = $totalNeto - $totalCosto;

        return response()->json([
            'expo_id' => $expoId,
            'num_ofertas' => (int) $rowOfertado->num_ofertas,
            'clientes_unicos' => (int) $rowOfertado->clientes_unicos,
            'num_facturas' => (int) $rowFacturado->num_facturas,
            'total_ofertado' => round($totalOfertado, 2),
            'total_descuento' => round((float) $rowOfertado->total_descuento, 2),
            'total_facturado' => round($totalFacturado, 2),
            'total_costo' => round($totalCosto, 2),
            'total_utilidad' => round($utilidad, 2),
            'margen_pct' => $totalNeto > 0 ? round(($utilidad / $totalNeto) * 100, 2) : null,
            'avance_pct' => $totalNeto > 0 ? round(($totalFacturado / $totalNeto) * 100, 2) : 0,
        ]);
    }

    // ═══════════════════════════════ Gráficas ════════════════════════════

    public function estadoOfertas(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $rows = DB::select("
            SELECT COALESCE(ec.estado,'SIN_REGISTRO') AS estado,
                   COUNT(DISTINCT c.id) AS total,
                     COALESCE(SUM(chp.sub_total),0) AS monto
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY COALESCE(ec.estado,'SIN_REGISTRO')
            ORDER BY total DESC
        ");

        return response()->json($rows);
    }

    public function ventasPorMarca(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $ofertado = DB::select("
            SELECT p.marca_id, COALESCE(m.nombre,'Sin marca') AS marca, SUM(chp.sub_total) AS ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY p.marca_id, m.nombre
        ");

        $facturado = DB::select("
            SELECT p.marca_id, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY p.marca_id
        ");
        $fMap = collect($facturado)->keyBy('marca_id');

        $rows = collect($ofertado)->map(function ($row) use ($fMap) {
            $f = $fMap->get($row->marca_id);
            return [
                'marca_id' => $row->marca_id,
                'marca' => $row->marca,
                'ofertado' => round((float) $row->ofertado, 2),
                'facturado' => round((float) ($f->facturado ?? 0), 2),
            ];
        })->sortByDesc('ofertado')->values();

        return response()->json($rows);
    }

    public function ventasPorAsesor(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $ofertado = DB::select("
            SELECT c.vendedor AS vendedor_id, COALESCE(u.name,'Sin asesor') AS asesor, SUM(chp.sub_total) AS ofertado
            {$this->joinsOfertado()}
            LEFT JOIN users u ON u.id = c.vendedor
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY c.vendedor, u.name
        ");

        $facturado = DB::select("
            SELECT c.vendedor AS vendedor_id, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY c.vendedor
        ");
        $fMap = collect($facturado)->keyBy('vendedor_id');

        $rows = collect($ofertado)->map(function ($row) use ($fMap) {
            $f = $fMap->get($row->vendedor_id);
            return [
                'vendedor_id' => $row->vendedor_id,
                'asesor' => $row->asesor,
                'ofertado' => round((float) $row->ofertado, 2),
                'facturado' => round((float) ($f->facturado ?? 0), 2),
            ];
        })->sortByDesc('ofertado')->values();

        return response()->json($rows);
    }

    public function ventasPorTeleasesor(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $ofertado = DB::select("
            SELECT c.users_id AS teleasesor_id, COALESCE(ut.name,'Sin teleasesor') AS teleasesor,
                   COUNT(DISTINCT c.id) AS ofertas, SUM(chp.sub_total) AS ofertado,
                   SUM(chp.monto_descProducto) AS descuento
            {$this->joinsOfertado()}
            LEFT JOIN users ut ON ut.id = c.users_id
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY c.users_id, ut.name
        ");

        $costoExpr = $this->costoUnitarioExpr();
        $facturado = DB::select("
            SELECT c.users_id AS teleasesor_id, COUNT(DISTINCT c.id) AS ofertas_ganadas,
                   SUM(vhp.sub_total_s) AS facturado,
                   SUM(($costoExpr) * vhp.cantidad_s) AS costo
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY c.users_id
        ");
        $facturadoPorTeleasesor = collect($facturado)->keyBy('teleasesor_id');

        $rows = collect($ofertado)->map(function ($row) use ($facturadoPorTeleasesor) {
            $factura = $facturadoPorTeleasesor->get($row->teleasesor_id);
            $facturado = (float) ($factura->facturado ?? 0);
            $costo = (float) ($factura->costo ?? 0);
            $utilidad = $facturado - $costo;
            $ofertas = (int) $row->ofertas;
            $ganadas = (int) ($factura->ofertas_ganadas ?? 0);

            return [
                'teleasesor_id' => $row->teleasesor_id ? (int) $row->teleasesor_id : null,
                'teleasesor' => $row->teleasesor,
                'ofertas' => $ofertas,
                'ofertas_ganadas' => $ganadas,
                'conversion_pct' => $ofertas > 0 ? round(($ganadas / $ofertas) * 100, 2) : 0,
                'ofertado' => round((float) $row->ofertado, 2),
                'facturado' => round($facturado, 2),
                'descuento' => round((float) $row->descuento, 2),
                'costo' => round($costo, 2),
                'utilidad' => round($utilidad, 2),
                'margen_pct' => $facturado > 0 ? round(($utilidad / $facturado) * 100, 2) : null,
            ];
        })->sortByDesc('ofertado')->values();

        return response()->json($rows);
    }

    public function evolucionDiaria(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $ofertado = DB::select("
            SELECT DATE(c.fecha_emision) AS fecha, SUM(chp.precio_unidad * chp.cantidad) AS ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY DATE(c.fecha_emision)
        ");

        $facturado = DB::select("
            SELECT DATE(f.fecha_emision) AS fecha, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY DATE(f.fecha_emision)
        ");

        $map = [];
        foreach ($ofertado as $row) {
            $map[$row->fecha]['ofertado'] = (float) $row->ofertado;
        }
        foreach ($facturado as $row) {
            $map[$row->fecha]['facturado'] = (float) $row->facturado;
        }
        ksort($map);

        $result = [];
        foreach ($map as $fecha => $vals) {
            $result[] = [
                'fecha' => $fecha,
                'ofertado' => round($vals['ofertado'] ?? 0, 2),
                'facturado' => round($vals['facturado'] ?? 0, 2),
            ];
        }

        return response()->json($result);
    }

    // ═══════════════════════════════ Tablas ═════════════════════════════

    private function datosProductos(Request $r): array
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $ofertado = DB::select("
             SELECT p.id AS producto_id, p.codigo_barra AS codigo, p.nombre AS producto,
                 COALESCE(m.nombre,'Sin marca') AS marca,
                 COALESCE(cat.descripcion,'Sin categoria') AS categoria,
                 COUNT(DISTINCT c.id) AS numero_ofertas,
                 SUM(chp.cantidad) AS cantidad_ofertada,
                 SUM(chp.sub_total) AS total_ofertado,
                 SUM(chp.monto_descProducto) AS descuento,
                  SUM(COALESCE(ppc.precio_base_venta, 0) * chp.cantidad) AS costo_ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY p.id, p.codigo_barra, p.nombre, m.nombre, cat.descripcion
        ");

        $costoExpr = $this->costoUnitarioExpr();
        $facturado = DB::select("
            SELECT p.id AS producto_id,
                     SUM(COALESCE(NULLIF(vhp.cantidad_oferta_aplicada,0), vhp.cantidad_s)) AS cantidad_facturada,
                     SUM(vhp.sub_total_s) AS total_facturado,
                     SUM(GREATEST((vhp.precio_unidad * vhp.cantidad_s) - vhp.sub_total_s, 0)) AS descuento_facturado,
                     SUM(($costoExpr) * vhp.cantidad_s) AS total_costo
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY p.id
        ");
        $fMap = collect($facturado)->keyBy('producto_id');

        $baseFacturas = $r->input('rentabilidad_base') === 'facturas';

        return collect($ofertado)->map(function ($row) use ($fMap, $baseFacturas) {
            $f = $fMap->get($row->producto_id);
            $totalFacturado = round((float) ($f->total_facturado ?? 0), 2);
            $totalOfertado = round((float) $row->total_ofertado, 2);
            $costoOfertado = round((float) $row->costo_ofertado, 2);
            $costoFacturado = round((float) ($f->total_costo ?? 0), 2);
            $totalBase = $baseFacturas ? $totalFacturado : $totalOfertado;
            $totalCosto = $baseFacturas ? $costoFacturado : $costoOfertado;
            $descuento = $baseFacturas
                ? round((float) ($f->descuento_facturado ?? 0), 2)
                : round((float) $row->descuento, 2);
            $utilidad = round($totalBase - $totalCosto, 2);

            return [
                'producto_id' => (int) $row->producto_id,
                'codigo' => $row->codigo,
                'producto' => $row->producto,
                'marca' => $row->marca,
                'categoria' => $row->categoria,
                'numero_ofertas' => (int) $row->numero_ofertas,
                'cantidad_ofertada' => (float) $row->cantidad_ofertada,
                'cantidad_facturada' => (float) ($f->cantidad_facturada ?? 0),
                'rentabilidad_base' => $baseFacturas ? 'facturas' : 'ofertas',
                'total_ofertado' => $totalOfertado,
                'total_facturado' => $totalFacturado,
                'descuento' => $descuento,
                'total_base' => $totalBase,
                'total_costo' => $totalCosto,
                'utilidad' => $utilidad,
                'margen_pct' => $totalBase > 0 ? round(($utilidad / $totalBase) * 100, 2) : null,
            ];
        })->sortByDesc('total_ofertado')->values()->all();
    }

    public function tablaProductos(Request $r)
    {
        return response()->json($this->datosProductos($r));
    }

    public function exportarProductos(Request $r)
    {
        $data = $this->datosProductos($r);
        $baseFacturas = $r->input('rentabilidad_base') === 'facturas';
        $entidad = $baseFacturas ? 'Factura' : 'Oferta';

        $headings = ['Codigo', 'Producto', 'Marca', 'Categoria', 'Ofertas', 'Cant. ' . ($baseFacturas ? 'Facturada' : 'Ofertada'), 'Venta ' . $entidad . ' (L)', 'Descuento (L)', 'Costo ' . $entidad . ' (L)', 'Utilidad ' . $entidad . ' (L)', 'Margen ' . $entidad . ' %'];
        $rows = array_map(fn ($p) => [
            $p['codigo'], $p['producto'], $p['marca'], $p['categoria'], $p['numero_ofertas'],
            $baseFacturas ? $p['cantidad_facturada'] : $p['cantidad_ofertada'], $p['total_base'],
            $p['descuento'], $p['total_costo'], $p['utilidad'], $p['margen_pct'],
        ], $data);

        return Excel::download(new AnaliticaProductosExport($headings, $rows), 'reporte_expo_productos.xlsx');
    }

    private function datosOfertas(Request $r): array
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);

        $rows = DB::select("
            SELECT
                c.id AS oferta_id,
                c.nombre_cliente,
                COALESCE(u.name, 'N/A') AS asesor,
                COALESCE(ut.name, 'Sin asignar') AS teleasesor,
                c.fecha_emision,
                COALESCE(ec.estado, 'SIN_REGISTRO') AS estado,
                COALESCE(ec.flujo_id, (SELECT hf.flujo_id FROM historico_flujo hf WHERE hf.tipo_tramite_id = 2 AND hf.tramite_id = c.id ORDER BY hf.id DESC LIMIT 1)) AS flujo_id,
                SUM(chp.sub_total) AS total_ofertado,
                SUM(chp.monto_descProducto) AS descuento,
                SUM(COALESCE(ppc.precio_base_venta, 0) * chp.cantidad) AS total_costo_oferta,
                SUM(chp.cantidad) AS cantidad_ofertada,
                COALESCE(fact.total_facturado, 0) AS total_facturado,
                COALESCE(fact.total_costo, 0) AS total_costo,
                COALESCE(fact.cantidad_facturada, 0) AS cantidad_facturada,
                COALESCE(fact.num_facturas, 0) AS num_facturas
            {$this->joinsOfertado()}
            LEFT JOIN users u ON u.id = c.vendedor
            LEFT JOIN users ut ON ut.id = c.users_id
            {$this->joinExpoCotizacion($expoId)}
            LEFT JOIN (
                SELECT chp2.cotizacion_id,
                       SUM(vhp2.sub_total_s) AS total_facturado,
                       SUM(COALESCE(ppc_vhp2.costoproducto, ppc_chp2.costoproducto, p2.costo_promedio, 0) * vhp2.cantidad_s) AS total_costo,
                       SUM(COALESCE(NULLIF(vhp2.cantidad_oferta_aplicada, 0), vhp2.cantidad_s)) AS cantidad_facturada,
                       COUNT(DISTINCT f2.id) AS num_facturas
                FROM cotizacion_has_producto chp2
                INNER JOIN venta_has_producto vhp2 ON vhp2.cotizacion_has_producto_id = chp2.id
                INNER JOIN factura f2 ON f2.id = vhp2.factura_id AND f2.estado_venta_id = 1
                INNER JOIN producto p2 ON p2.id = chp2.producto_id
                LEFT JOIN precios_producto_carga ppc_vhp2 ON ppc_vhp2.id = vhp2.precios_producto_carga_id
                LEFT JOIN precios_producto_carga ppc_chp2 ON ppc_chp2.id = chp2.precios_producto_carga_id
                GROUP BY chp2.cotizacion_id
            ) fact ON fact.cotizacion_id = c.id
            {$this->whereVigentes($expoId)}
            $extra
            GROUP BY c.id, c.nombre_cliente, u.name, ut.name, c.fecha_emision, ec.estado,
                     ec.flujo_id, fact.total_facturado, fact.total_costo,
                     fact.cantidad_facturada, fact.num_facturas
            ORDER BY total_ofertado DESC
        ");

        return array_map(function ($row) {
            $totalOfertado = (float) $row->total_ofertado;
            $totalFacturado = (float) $row->total_facturado;
            $totalCostoOferta = (float) $row->total_costo_oferta;
            $utilidad = $totalOfertado - $totalCostoOferta;
            $cantidadFacturada = (float) $row->cantidad_facturada;
            $cantidadOfertada = (float) $row->cantidad_ofertada;
            $estadoFacturacion = $cantidadFacturada <= 0.0001
                ? 'NO_FACTURADA'
                : ($cantidadFacturada + 0.0001 >= $cantidadOfertada ? 'FACTURADA' : 'PARCIALMENTE_FACTURADA');

            return [
                'oferta_id' => (int) $row->oferta_id,
                'flujo_id' => $row->flujo_id ? (int) $row->flujo_id : null,
                'cliente' => $row->nombre_cliente,
                'asesor' => $row->asesor,
                'teleasesor' => $row->teleasesor,
                'fecha_emision' => $row->fecha_emision,
                'estado' => $row->estado,
                'estado_facturacion' => $estadoFacturacion,
                'num_facturas' => (int) $row->num_facturas,
                'total_ofertado' => round($totalOfertado, 2),
                'total_facturado' => round($totalFacturado, 2),
                'descuento' => round((float) $row->descuento, 2),
                'utilidad' => round($utilidad, 2),
                'margen_pct' => $totalOfertado > 0 ? round(($utilidad / $totalOfertado) * 100, 2) : null,
                'avance_pct' => $totalOfertado > 0 ? round(($totalFacturado / $totalOfertado) * 100, 2) : 0,
            ];
        }, $rows);
    }

    public function tablaOfertas(Request $r)
    {
        return response()->json($this->datosOfertas($r));
    }

    public function exportarOfertas(Request $r)
    {
        $data = $this->datosOfertas($r);

        $headings = ['Oferta #', 'Flujo', 'Cliente', 'Asesor', 'Teleasesor', 'Fecha', 'Estado', 'Estado Facturacion', 'Facturas', 'Total Ofertado (L)', 'Total Facturado (L)', 'Margen Oferta %', 'Descuento (L)', 'Utilidad Oferta (L)', 'Avance %'];
        $rows = array_map(fn ($o) => [
            $o['oferta_id'], $o['flujo_id'], $o['cliente'], $o['asesor'], $o['teleasesor'],
            $o['fecha_emision'], $o['estado'], $o['estado_facturacion'], $o['num_facturas'],
            $o['total_ofertado'], $o['total_facturado'], $o['margen_pct'], $o['descuento'],
            $o['utilidad'], $o['avance_pct'],
        ], $data);

        return Excel::download(new AnaliticaProductosExport($headings, $rows), 'reporte_expo_ofertas.xlsx');
    }

    // ═══════════════════════════════ Catálogos de filtro ═════════════════

    public function catalogoFiltros(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);

        $marcas = DB::select("
            SELECT DISTINCT p.marca_id AS id, COALESCE(m.nombre,'Sin marca') AS nombre
            {$this->joinsOfertado()}
            {$this->whereVigentes($expoId)}
            ORDER BY nombre
        ");

        $escalas = DB::select("
            SELECT DISTINCT cp.id, cp.nombre
            {$this->joinsOfertado()}
            {$this->whereVigentes($expoId)}
            AND cp.id IS NOT NULL
            ORDER BY cp.nombre
        ");

        $vendedores = DB::select("
            SELECT DISTINCT c.vendedor AS id, u.name AS nombre
            FROM cotizacion c
            LEFT JOIN users u ON u.id = c.vendedor
            WHERE c.id IN ({$this->inVigentes($expoId)})
              AND c.vendedor IS NOT NULL
            ORDER BY u.name
        ");

        $teleasesores = DB::select("
            SELECT DISTINCT c.users_id AS id, u.name AS nombre
            FROM cotizacion c
            LEFT JOIN users u ON u.id = c.users_id
            WHERE c.id IN ({$this->inVigentes($expoId)})
              AND c.users_id IS NOT NULL
            ORDER BY u.name
        ");

        return response()->json(compact('marcas', 'escalas', 'vendedores', 'teleasesores'));
    }

    public function buscarProductos(Request $r)
    {
        session()->save();
        $expoId = $this->expoIdDesdeRequest($r);
        $cotizacionIds = $this->cotizacionIdsFiltrados($r, $expoId);
        $pagina = max(1, (int) $r->input('page', 1));
        $porPagina = 12;
        $texto = trim((string) $r->input('q', ''));
        $palabras = array_values(array_filter(preg_split('/\s+/', $texto) ?: []));
        $escalas = $this->idsDesdeRequest($r, 'escala_ids', 'escala_id');

        $query = DB::table('producto as p')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->where('p.estado_producto_id', 1)
            ->whereExists(function ($sub) use ($cotizacionIds, $escalas) {
                $sub->select(DB::raw(1))
                    ->from('cotizacion_has_producto as chp_busqueda');
                if ($escalas) {
                    $sub->join('precios_producto_carga as ppc_busqueda', 'ppc_busqueda.id', '=', 'chp_busqueda.precios_producto_carga_id')
                        ->whereIn('ppc_busqueda.categoria_precios_id', $escalas);
                }
                $sub->whereColumn('chp_busqueda.producto_id', 'p.id')
                    ->whereIn('chp_busqueda.cotizacion_id', $cotizacionIds);
            })
            ->select([
                'p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal',
                'p.isv', 'm.nombre as marca_nombre',
            ]);

        foreach ($palabras as $palabra) {
            $query->where(function ($sub) use ($palabra) {
                $sub->where('p.nombre', 'like', "%{$palabra}%")
                    ->orWhere('p.codigo_barra', 'like', "%{$palabra}%")
                    ->orWhere('p.codigo_estatal', 'like', "%{$palabra}%");
                if (ctype_digit($palabra)) {
                    $sub->orWhere('p.id', (int) $palabra);
                }
            });
        }

        if ($r->filled('categoria_id')) {
            $query->join('sub_categoria as sc_busqueda', 'sc_busqueda.id', '=', 'p.sub_categoria_id')
                ->where('sc_busqueda.categoria_producto_id', (int) $r->categoria_id);
        }
        if ($r->filled('marca_id')) {
            $query->where('p.marca_id', (int) $r->marca_id);
        }
        if ($r->boolean('con_stock')) {
            $query->whereExists(function ($sub) {
                $sub->select(DB::raw(1))->from('recibido_bodega as rb_busqueda')
                    ->whereColumn('rb_busqueda.producto_id', 'p.id')
                    ->where('rb_busqueda.cantidad_disponible', '>', 0);
            });
        }

        $total = (clone $query)->count('p.id');
        if ($texto !== '' && ctype_digit($texto)) {
            $query->orderByRaw('(p.id = ?) DESC', [(int) $texto]);
        }
        $items = $query->orderBy('p.nombre')
            ->offset(($pagina - 1) * $porPagina)
            ->limit($porPagina)
            ->get();

        if ($items->isNotEmpty()) {
            $ids = $items->pluck('id')->all();
            $stock = DB::table('recibido_bodega')->whereIn('producto_id', $ids)
                ->select('producto_id', DB::raw('SUM(cantidad_disponible) as stock'))
                ->groupBy('producto_id')->get()->keyBy('producto_id');
            $imagenes = DB::table('img_producto')->whereIn('producto_id', $ids)
                ->orderBy('producto_id')->orderBy('id')->get(['producto_id', 'url_img'])
                ->unique('producto_id')->keyBy('producto_id');

            $items->each(function ($item) use ($stock, $imagenes) {
                $item->stock = (float) ($stock->get($item->id)->stock ?? 0);
                $item->imagen = $imagenes->get($item->id)->url_img ?? null;
            });
        }

        return response()->json([
            'data' => $items,
            'total' => $total,
            'current_page' => $pagina,
            'per_page' => $porPagina,
            'last_page' => max(1, (int) ceil($total / $porPagina)),
        ]);
    }

    private function cotizacionIdsFiltrados(Request $r, int $expoId): array
    {
        $extra = $this->filtrosExtra($r);
        $rows = DB::select("
            SELECT DISTINCT c.id
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereVigentes($expoId)}
            $extra
        ");

        return array_map(fn ($row) => (int) $row->id, $rows);
    }

    public function detalleOferta(Request $r, ReporteExpoDetalleService $detalle)
    {
        $data = $detalle->oferta((int) $r->oferta_id);
        abort_unless($data, 404);

        $expoId = (int) ($r->expo_id ?? 0);
        abort_if($expoId > 0 && $data['oferta']['expo_id'] !== $expoId, 404);

        return response()->json($data);
    }

    public function detalleProducto(Request $r, ReporteExpoDetalleService $detalle)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $data = $detalle->producto(
            (int) $r->producto_id,
            $expoId,
            $this->cotizacionIdsFiltrados($r, $expoId)
        );
        abort_unless($data, 404);

        $baseFacturas = $r->input('rentabilidad_base') === 'facturas';
        $data['rentabilidad_base'] = $baseFacturas ? 'facturas' : 'ofertas';
        $producto = &$data['producto'];
        $producto['cantidad_base'] = $baseFacturas ? $producto['cantidad_vendida'] : $producto['cantidad_ofertada'];
        $producto['total_base'] = $baseFacturas ? $producto['total_vendido'] : $producto['total_ofertado'];
        $producto['descuento_base'] = $baseFacturas ? $producto['descuento_facturado'] : $producto['descuento_acumulado'];
        $producto['costo_base'] = $baseFacturas ? $producto['costo_facturado'] : $producto['costo_ofertado'];
        $producto['utilidad_base'] = $baseFacturas ? $producto['utilidad_facturada'] : $producto['utilidad_ofertada'];
        $producto['margen_base_pct'] = $baseFacturas ? $producto['margen_facturado_pct'] : $producto['margen_ofertado_pct'];
        unset($producto);

        foreach ($data['ofertas'] as &$oferta) {
            $totalBase = $baseFacturas ? $oferta['total_facturado'] : $oferta['subtotal_final'];
            $costoBase = $baseFacturas ? $oferta['costo_facturado'] : $oferta['costo_total'];
            $utilidadBase = $totalBase - $costoBase;
            $oferta['cantidad_base'] = $baseFacturas ? $oferta['cantidad_facturada'] : $oferta['cantidad'];
            $oferta['total_base'] = round($totalBase, 2);
            $oferta['precio_base_transaccion'] = $oferta['cantidad_base'] > 0
                ? round($totalBase / $oferta['cantidad_base'], 4)
                : 0;
            $oferta['descuento_base'] = $baseFacturas ? $oferta['descuento_facturado'] : $oferta['descuento'];
            $oferta['isv_base'] = $baseFacturas ? $oferta['isv_facturado'] : $oferta['isv'];
            $oferta['total_con_impuesto_base'] = $baseFacturas ? $oferta['total_con_impuesto_facturado'] : $oferta['total'];
            $oferta['costo_base'] = round($costoBase, 2);
            $oferta['utilidad_base'] = round($utilidadBase, 2);
            $oferta['margen_base_pct'] = $totalBase > 0 ? round(($utilidadBase / $totalBase) * 100, 2) : null;
        }
        unset($oferta);

        return response()->json($data);
    }

    public function exportarOferta(Request $r, ReporteExpoDetalleService $detalle)
    {
        $data = $detalle->oferta((int) $r->oferta_id);
        abort_unless($data, 404);

        return Excel::download(
            new ReporteExpoOfertaExport($data),
            'reporte_expo_oferta_' . $data['oferta']['id'] . '.xlsx'
        );
    }

}
