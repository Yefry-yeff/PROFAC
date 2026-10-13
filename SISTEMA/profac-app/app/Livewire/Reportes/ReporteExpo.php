<?php

namespace App\Livewire\Reportes;

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

    /** Devuelve todas las ofertas registradas para la Expo, incluidas versiones anteriores y flujos duplicados. */
    private function cotizacionIdsExpo(int $expoId): array
    {
        if (isset($this->cacheVigentes[$expoId])) {
            return $this->cacheVigentes[$expoId];
        }

        $ids = DB::table('expo_cotizacion')
            ->where('expo_id', $expoId)
            ->orderBy('cotizacion_id')
            ->pluck('cotizacion_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        if (empty($ids)) {
            $ids = [0];
        }

        return $this->cacheVigentes[$expoId] = $ids;
    }

    private function inExpo(int $expoId): string
    {
        return implode(',', $this->cotizacionIdsExpo($expoId));
    }

    private function costoUnitarioExpr(): string
    {
        return 'COALESCE(ppc_vhp.costoproducto, ppc.costoproducto, p.costo_promedio, 0)';
    }
    private function netoOfertaExpr(string $alias = 'chp'): string
    {
        return "ROUND(ROUND(CASE WHEN {$alias}.cantidad > 0 THEN {$alias}.sub_total / {$alias}.cantidad ELSE {$alias}.precio_unidad END, 2) * {$alias}.cantidad, 2)";
    }

    private function brutoOfertaExpr(string $alias = 'chp', string $unidadAlias = 'uv'): string
    {
        return "ROUND({$alias}.precio_unidad * {$alias}.cantidad * COALESCE(NULLIF({$unidadAlias}.unidad_venta, 0), 1), 2)";
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
            LEFT JOIN unidad_medida_venta uv ON uv.id = chp.unidad_medida_venta_id
        ";
    }

    /**
     * JOINs adicionales para el lado "facturado". INNER: solo líneas realmente facturadas.
     *
     * NOTA DE RENDIMIENTO: anteriormente este JOIN resolvía el enlace indirecto
     * (vía prefactura_auditoria) con una condición "OR ... EXISTS (subconsulta
     * correlacionada)" dentro del ON. Como el 99.9% de venta_has_producto tiene
     * cotizacion_has_producto_id NULL, MySQL debía re-evaluar esa subconsulta para
     * ~170k filas POR CADA línea ofertada (O(chp × vhp)), provocando consultas de
     * varios minutos que colgaban todo el sistema (servido por un único proceso).
     * Se reescribe como una derivada con UNION ALL (1 pasada O(vhp), apoyada en
     * índices) que es exactamente equivalente: misma multiplicidad de filas y
     * mismas condiciones de validación (incluida pf_fact.cotizacion_id = c.id).
     */
    private function joinsFacturado(): string
    {
        return "
            INNER JOIN (
                SELECT v.*, v.cotizacion_has_producto_id AS chp_id_resuelta, NULL AS cotizacion_id_prefactura
                FROM venta_has_producto v
                WHERE v.cotizacion_has_producto_id IS NOT NULL

                UNION ALL

                SELECT v.*, php_fact.cotizacion_has_producto_id AS chp_id_resuelta, pf_fact.cotizacion_id AS cotizacion_id_prefactura
                FROM venta_has_producto v
                INNER JOIN prefactura_auditoria pa_fact ON pa_fact.factura_id = v.factura_id
                INNER JOIN prefactura pf_fact ON pf_fact.id = pa_fact.prefactura_id
                INNER JOIN prefactura_has_producto php_fact
                    ON php_fact.prefactura_id = pf_fact.id
                   AND php_fact.producto_id = v.producto_id
                   AND php_fact.indice = v.indice
                WHERE v.cotizacion_has_producto_id IS NULL
            ) vhp ON vhp.chp_id_resuelta = chp.id
                 AND (vhp.cotizacion_id_prefactura IS NULL OR vhp.cotizacion_id_prefactura = c.id)
            INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_venta_id = 1
            LEFT JOIN precios_producto_carga ppc_vhp ON ppc_vhp.id = vhp.precios_producto_carga_id
        ";
    }

    private function joinExpoCotizacion(int $expoId): string
    {
        return "LEFT JOIN expo_cotizacion ec ON ec.cotizacion_id = c.id AND ec.expo_id = {$expoId}";
    }

    private function whereExpo(int $expoId): string
    {
        return "WHERE chp.cotizacion_id IN ({$this->inExpo($expoId)})";
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
        $netoOfertaExpr = $this->netoOfertaExpr();
        $brutoOfertaExpr = $this->brutoOfertaExpr();

        $rowOfertado = DB::selectOne("
            SELECT
                COUNT(DISTINCT c.id) AS num_ofertas,
                COUNT(DISTINCT COALESCE(c.cliente_id, c.nombre_cliente)) AS clientes_unicos,
                COALESCE(SUM($brutoOfertaExpr), 0) AS total_ofertado,
                COALESCE(SUM($netoOfertaExpr), 0) AS total_neto,
                COALESCE(SUM(GREATEST(($brutoOfertaExpr) - ($netoOfertaExpr), 0)), 0) AS total_descuento,
                COALESCE(SUM(ROUND(($netoOfertaExpr) * chp.isv_producto / 100, 2)), 0) AS total_isv,
                COALESCE(SUM(COALESCE(ppc.precio_base_venta, 0) * chp.cantidad), 0) AS total_costo
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
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
            {$this->whereExpo($expoId)}
            $extra
        ");

        $totalOfertado = (float) $rowOfertado->total_ofertado;
        $totalNeto = (float) $rowOfertado->total_neto;
        $totalIsv = (float) $rowOfertado->total_isv;
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
            'total_oferta_sin_isv' => round($totalNeto, 2),
            'total_oferta_con_isv' => round($totalNeto + $totalIsv, 2),
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
                $netoOfertaExpr = $this->netoOfertaExpr();

                $ofertado = DB::select("
            SELECT COALESCE(ec.estado,'SIN_REGISTRO') AS estado,
                   COUNT(DISTINCT c.id) AS total,
                                     COALESCE(SUM($netoOfertaExpr),0) AS ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY COALESCE(ec.estado,'SIN_REGISTRO')
            ORDER BY total DESC
        ");

        $facturado = DB::select("
            SELECT COALESCE(ec.estado,'SIN_REGISTRO') AS estado,
                   COALESCE(SUM(vhp.sub_total_s),0) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY COALESCE(ec.estado,'SIN_REGISTRO')
        ");
        $facturadoPorEstado = collect($facturado)->keyBy('estado');
        $rows = collect($ofertado)->map(function ($row) use ($facturadoPorEstado) {
            return [
                'estado' => $row->estado,
                'total' => (int) $row->total,
                'ofertado' => round((float) $row->ofertado, 2),
                'facturado' => round((float) ($facturadoPorEstado->get($row->estado)->facturado ?? 0), 2),
            ];
        })->values();

        return response()->json($rows);
    }

    public function ventasPorMarca(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);
        $netoOfertaExpr = $this->netoOfertaExpr();

        $ofertado = DB::select("
            SELECT p.marca_id, COALESCE(m.nombre,'Sin marca') AS marca, SUM($netoOfertaExpr) AS ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY p.marca_id, m.nombre
        ");

        $facturado = DB::select("
            SELECT p.marca_id, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
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
        $netoOfertaExpr = $this->netoOfertaExpr();

        $ofertado = DB::select("
            SELECT c.vendedor AS vendedor_id, COALESCE(u.name,'Sin asesor') AS asesor, SUM($netoOfertaExpr) AS ofertado
            {$this->joinsOfertado()}
            LEFT JOIN users u ON u.id = c.vendedor
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY c.vendedor, u.name
        ");

        $facturado = DB::select("
            SELECT c.vendedor AS vendedor_id, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
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
        $netoOfertaExpr = $this->netoOfertaExpr();

        $ofertado = DB::select("
            SELECT c.users_id AS teleasesor_id, COALESCE(ut.name,'Sin teleasesor') AS teleasesor,
                   COUNT(DISTINCT c.id) AS ofertas, SUM($netoOfertaExpr) AS ofertado,
                   SUM(chp.monto_descProducto) AS descuento
            {$this->joinsOfertado()}
            LEFT JOIN users ut ON ut.id = c.users_id
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
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
            {$this->whereExpo($expoId)}
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

    public function topClientes(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);
        $netoOfertaExpr = $this->netoOfertaExpr();

        $ofertado = DB::select("
            SELECT COALESCE(c.cliente_id, 0) AS cliente_id,
                   COALESCE(NULLIF(c.nombre_cliente, ''), 'Sin cliente') AS cliente,
                   COUNT(DISTINCT c.id) AS ofertas,
                   SUM($netoOfertaExpr) AS ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY COALESCE(c.cliente_id, 0), COALESCE(NULLIF(c.nombre_cliente, ''), 'Sin cliente')
        ");

        $facturado = DB::select("
            SELECT COALESCE(c.cliente_id, 0) AS cliente_id,
                   SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY COALESCE(c.cliente_id, 0)
        ");
        $facturadoPorCliente = collect($facturado)->keyBy('cliente_id');
        $baseFacturas = $r->input('rentabilidad_base') === 'facturas';

        $rows = collect($ofertado)->map(function ($row) use ($facturadoPorCliente, $baseFacturas) {
            $totalOfertado = round((float) $row->ofertado, 2);
            $totalFacturado = round((float) ($facturadoPorCliente->get($row->cliente_id)->facturado ?? 0), 2);

            return [
                'cliente_id' => (int) $row->cliente_id,
                'cliente' => $row->cliente,
                'ofertas' => (int) $row->ofertas,
                'ofertado' => $totalOfertado,
                'facturado' => $totalFacturado,
                'total_base' => $baseFacturas ? $totalFacturado : $totalOfertado,
            ];
        })->sortByDesc('total_base')->take(10)->values();

        return response()->json($rows);
    }

    public function topProductos(Request $r)
    {
        $rows = collect($this->datosProductos($r))
            ->sortByDesc('total_base')
            ->take(10)
            ->map(fn ($row) => [
                'producto_id' => $row['producto_id'],
                'producto' => $row['producto'],
                'codigo' => $row['codigo'],
                'ofertado' => $row['total_ofertado'],
                'facturado' => $row['total_facturado'],
                'total_base' => $row['total_base'],
            ])->values();

        return response()->json($rows);
    }

    public function evolucionDiaria(Request $r)
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);
        $netoOfertaExpr = $this->netoOfertaExpr();

        $ofertado = DB::select("
            SELECT DATE(c.fecha_emision) AS fecha, SUM($netoOfertaExpr) AS ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY DATE(c.fecha_emision)
        ");

        $facturado = DB::select("
            SELECT DATE(f.fecha_emision) AS fecha, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
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
        $netoOfertaExpr = $this->netoOfertaExpr();
        $brutoOfertaExpr = $this->brutoOfertaExpr();

        $ofertado = DB::select("
             SELECT p.id AS producto_id, p.codigo_barra AS codigo, p.nombre AS producto,
                 COALESCE(m.nombre,'Sin marca') AS marca,
                 COALESCE(cat.descripcion,'Sin categoria') AS categoria,
                 COUNT(DISTINCT c.id) AS numero_ofertas,
                 SUM(chp.cantidad) AS cantidad_ofertada,
                 SUM($netoOfertaExpr) AS total_ofertado,
                 SUM(GREATEST(($brutoOfertaExpr) - ($netoOfertaExpr), 0)) AS descuento,
                  SUM(COALESCE(ppc.precio_base_venta, 0) * chp.cantidad) AS costo_ofertado
            {$this->joinsOfertado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
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
            {$this->whereExpo($expoId)}
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

    /**
     * Igual que datosProductos() pero sin agrupar: 1 fila = 1 línea de
     * producto dentro de 1 oferta concreta. Se usa solo para el Excel,
     * que necesita Cliente y Flujo por fila (datos que no existen en la
     * tabla agregada por producto que se muestra en pantalla).
     */
    private function datosProductosDetalle(Request $r): array
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);
        $netoOfertaExpr = $this->netoOfertaExpr();
        $brutoOfertaExpr = $this->brutoOfertaExpr();

        $ofertado = DB::select("
             SELECT chp.id AS linea_id, p.id AS producto_id, p.codigo_barra AS codigo, p.nombre AS producto,
                 COALESCE(m.nombre,'Sin marca') AS marca,
                 COALESCE(cat.descripcion,'Sin categoria') AS categoria,
                 c.id AS oferta_id, COALESCE(c.nombre_cliente, 'Sin cliente') AS cliente,
                 COALESCE(ec.flujo_id, (SELECT hf.flujo_id FROM historico_flujo hf WHERE hf.tipo_tramite_id = 2 AND hf.tramite_id = c.id ORDER BY hf.id DESC LIMIT 1)) AS flujo_id,
                 COALESCE(um.nombre, 'N/A') AS unidad_medida,
                 chp.cantidad AS cantidad_ofertada,
                 $netoOfertaExpr AS total_ofertado,
                 GREATEST(($brutoOfertaExpr) - ($netoOfertaExpr), 0) AS descuento,
                 COALESCE(ppc.precio_base_venta, 0) * chp.cantidad AS costo_ofertado
            {$this->joinsOfertado()}
            LEFT JOIN unidad_medida um ON um.id = uv.unidad_medida_id
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            ORDER BY c.id, chp.indice
        ");

        $costoExpr = $this->costoUnitarioExpr();
        $facturado = DB::select("
            SELECT chp.id AS linea_id,
                     SUM(COALESCE(NULLIF(vhp.cantidad_oferta_aplicada,0), vhp.cantidad_s)) AS cantidad_facturada,
                     SUM(vhp.sub_total_s) AS total_facturado,
                     SUM(GREATEST((vhp.precio_unidad * vhp.cantidad_s) - vhp.sub_total_s, 0)) AS descuento_facturado,
                     SUM(($costoExpr) * vhp.cantidad_s) AS total_costo
            {$this->joinsOfertado()}
            {$this->joinsFacturado()}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY chp.id
        ");
        $fMap = collect($facturado)->keyBy('linea_id');

        $baseFacturas = $r->input('rentabilidad_base') === 'facturas';

        return collect($ofertado)->map(function ($row) use ($fMap, $baseFacturas) {
            $f = $fMap->get($row->linea_id);
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
                'linea_id' => (int) $row->linea_id,
                'producto_id' => (int) $row->producto_id,
                'codigo' => $row->codigo,
                'producto' => $row->producto,
                'marca' => $row->marca,
                'categoria' => $row->categoria,
                'oferta_id' => (int) $row->oferta_id,
                'cliente' => $row->cliente,
                'flujo_id' => $row->flujo_id ? (int) $row->flujo_id : null,
                'unidad_medida' => $row->unidad_medida,
                'cantidad_ofertada' => (float) $row->cantidad_ofertada,
                'cantidad_facturada' => (float) ($f->cantidad_facturada ?? 0),
                'total_ofertado' => $totalOfertado,
                'total_facturado' => $totalFacturado,
                'descuento' => $descuento,
                'total_base' => $totalBase,
                'total_costo' => $totalCosto,
                'utilidad' => $utilidad,
                'margen_pct' => $totalBase > 0 ? round(($utilidad / $totalBase) * 100, 2) : null,
            ];
        })->all();
    }

    public function exportarProductos(Request $r)
    {
        $data = $this->datosProductosDetalle($r);
        $baseFacturas = $r->input('rentabilidad_base') === 'facturas';
        $entidad = $baseFacturas ? 'Factura' : 'Oferta';

        $headings = [
            'Codigo', 'Cliente', 'Flujo', 'Codigo Producto (ID)', 'Unidad de Medida',
            'Producto', 'Marca', 'Categoria', 'Cant. ' . ($baseFacturas ? 'Facturada' : 'Ofertada'),
            'Venta ' . $entidad . ' (L)', 'Descuento (L)', 'Costo ' . $entidad . ' (L)',
            'Utilidad ' . $entidad . ' (L)', 'Margen ' . $entidad . ' %',
        ];
        $rows = array_map(fn ($p) => [
            $p['codigo'], $p['cliente'], $p['flujo_id'], $p['producto_id'], $p['unidad_medida'],
            $p['producto'], $p['marca'], $p['categoria'],
            $baseFacturas ? $p['cantidad_facturada'] : $p['cantidad_ofertada'], $p['total_base'],
            $p['descuento'], $p['total_costo'], $p['utilidad'], $p['margen_pct'],
        ], $data);

        return Excel::download(new AnaliticaProductosExport($headings, $rows), 'reporte_expo_productos.xlsx');
    }

    /**
     * Para cada flujo_id dado devuelve el id de la cotización actualmente
     * marcada como "ganadora" (según el último movimiento registrado en
     * cotizacion_estado). Si el último movimiento de un flujo fue "quitar
     * ganadora", ese flujo simplemente no aparece en el mapa devuelto.
     */
    private function ofertasGanadorasPorFlujo(array $flujoIds): array
    {
        $flujoIds = array_values(array_unique(array_filter(array_map('intval', $flujoIds))));
        if (empty($flujoIds)) {
            return [];
        }

        $rows = DB::select("
            SELECT ce.flujo_id, ce.cotizacion_id
            FROM cotizacion_estado ce
            INNER JOIN (
                SELECT flujo_id, MAX(id) AS ultimo_id
                FROM cotizacion_estado
                WHERE flujo_id IN (" . implode(',', $flujoIds) . ")
                GROUP BY flujo_id
            ) ult ON ult.flujo_id = ce.flujo_id AND ult.ultimo_id = ce.id
            WHERE ce.ganadora = 1
        ");

        $mapa = [];
        foreach ($rows as $row) {
            $mapa[(int) $row->flujo_id] = (int) $row->cotizacion_id;
        }

        return $mapa;
    }

    /**
     * Agrupa las filas planas (oferta principal + secciones) por flujo y
     * elige UNA oferta representativa por flujo: primero la marcada como
     * "ganadora" (cotizacion_estado.ganadora vigente); si no hay ganadora
     * vigente, la última oferta generada (mayor fecha_emision / id).
     *
     * El total ofertado se toma únicamente de la oferta representativa
     * (sus propias líneas en cotizacion_has_producto, que ya incluyen la
     * totalidad del pedido), mientras que lo facturado se agrega sumando
     * la oferta representativa + todas sus secciones (ya que facturar una
     * sección no duplica montos: cada factura referencia líneas propias
     * de esa sección, distintas de las líneas de la oferta origen).
     */
    private function agruparPorFlujo(array $filas): array
    {
        $grupos = [];
        foreach ($filas as $fila) {
            $clave = $fila['flujo_id'] !== null ? 'flujo_' . $fila['flujo_id'] : 'oferta_' . $fila['oferta_id'];
            $grupos[$clave][] = $fila;
        }

        $flujoIds = array_values(array_unique(array_filter(array_column($filas, 'flujo_id'))));
        $ganadoras = $this->ofertasGanadorasPorFlujo($flujoIds);

        $resultado = [];
        foreach ($grupos as $grupo) {
            $raices = array_values(array_filter($grupo, fn ($f) => $f['oferta_origen_id'] === null));
            if (empty($raices)) {
                // No debería ocurrir (toda sección tiene un origen), pero por
                // seguridad se trata el grupo completo como si fueran raíces.
                $raices = $grupo;
            }

            $flujoId = $grupo[0]['flujo_id'];
            $ganadoraId = $flujoId !== null ? ($ganadoras[$flujoId] ?? null) : null;

            $representativa = null;
            if ($ganadoraId !== null) {
                foreach ($raices as $raiz) {
                    if ($raiz['oferta_id'] === $ganadoraId) {
                        $representativa = $raiz;
                        break;
                    }
                }
            }
            if ($representativa === null) {
                usort($raices, function ($a, $b) {
                    $fechaA = $a['fecha_emision'] ?? '';
                    $fechaB = $b['fecha_emision'] ?? '';
                    return $fechaB <=> $fechaA ?: $b['oferta_id'] <=> $a['oferta_id'];
                });
                $representativa = $raices[0];
            }

            $secciones = array_values(array_filter(
                $grupo,
                fn ($f) => $f['oferta_origen_id'] === $representativa['oferta_id']
            ));

            $totalFacturado = (float) $representativa['total_facturado'];
            $cantidadFacturada = (float) $representativa['cantidad_facturada'];
            $numFacturas = (int) $representativa['num_facturas'];
            foreach ($secciones as $seccion) {
                $totalFacturado += (float) $seccion['total_facturado'];
                $cantidadFacturada += (float) $seccion['cantidad_facturada'];
                $numFacturas += (int) $seccion['num_facturas'];
            }

            $totalOfertado = (float) $representativa['total_ofertado'];
            $cantidadOfertada = (float) $representativa['cantidad_ofertada'];
            $estadoFacturacion = $cantidadFacturada <= 0.0001
                ? 'NO_FACTURADA'
                : ($cantidadFacturada + 0.0001 >= $cantidadOfertada ? 'FACTURADA' : 'PARCIALMENTE_FACTURADA');

            $seccionesSalida = array_map(fn ($s) => [
                'oferta_id' => $s['oferta_id'],
                'numero_seccion' => $s['numero_seccion'],
                'estado' => $s['estado_seccion'],
                'estado_facturacion' => $s['estado_facturacion'],
                'total_ofertado' => $s['total_ofertado'],
                'total_facturado' => $s['total_facturado'],
                'num_facturas' => $s['num_facturas'],
            ], $secciones);

            // Acceso directo a las líneas propias de la oferta raíz (sus
            // cotizacion_has_producto). Siempre se muestra: si no hay
            // secciones es la única forma de ver el detalle de productos; si
            // ya hay secciones, permite revisar lo que aún no se ha
            // repartido/trabajado en ninguna sección.
            array_unshift($seccionesSalida, [
                'oferta_id' => $representativa['oferta_id'],
                'numero_seccion' => null,
                'nombre_seccion' => 'Productos sin ofertar',
                'estado' => null,
                'estado_facturacion' => $representativa['estado_facturacion'],
                'total_ofertado' => $representativa['total_ofertado'],
                'total_facturado' => $representativa['total_facturado'],
                'num_facturas' => $representativa['num_facturas'],
                'es_raiz' => true,
            ]);

            if ($estadoFacturacion === 'PARCIALMENTE_FACTURADA') {
                $seccionesSalida[] = [
                    'oferta_id' => null,
                    'numero_seccion' => null,
                    'nombre_seccion' => 'Productos sin factura',
                    'estado' => null,
                    'estado_facturacion' => 'NO_FACTURADA',
                    'total_ofertado' => round(max($totalOfertado - $totalFacturado, 0), 2),
                    'total_facturado' => 0,
                    'num_facturas' => 0,
                    'es_pendiente' => true,
                ];
            }

            $utilidad = $totalOfertado - (float) $representativa['total_costo_oferta'];

            $resultado[] = [
                'oferta_id' => $representativa['oferta_id'],
                'flujo_id' => $flujoId,
                'cliente' => $representativa['cliente'],
                'asesor' => $representativa['asesor'],
                'teleasesor' => $representativa['teleasesor'],
                'fecha_emision' => $representativa['fecha_emision'],
                'estado' => $representativa['estado'],
                'oferta_origen_id' => null,
                'numero_seccion' => null,
                'nombre_seccion' => null,
                'estado_facturacion' => $estadoFacturacion,
                'num_facturas' => $numFacturas,
                'total_ofertado' => round($totalOfertado, 2),
                'total_facturado' => round($totalFacturado, 2),
                'descuento' => $representativa['descuento'],
                'utilidad' => round($utilidad, 2),
                'margen_pct' => $totalOfertado > 0 ? round(($utilidad / $totalOfertado) * 100, 2) : null,
                'avance_pct' => $totalOfertado > 0 ? round(($totalFacturado / $totalOfertado) * 100, 2) : 0,
                'num_secciones' => count($secciones),
                'secciones' => $seccionesSalida,
            ];
        }

        usort($resultado, fn ($a, $b) => $b['total_ofertado'] <=> $a['total_ofertado']);

        return $resultado;
    }

    private function datosOfertas(Request $r): array
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);
        $netoOfertaExpr = $this->netoOfertaExpr();
        $brutoOfertaExpr = $this->brutoOfertaExpr();

        $rows = DB::select("
            SELECT
                c.id AS oferta_id,
                c.nombre_cliente,
                COALESCE(u.name, 'N/A') AS asesor,
                COALESCE(ut.name, 'Sin asignar') AS teleasesor,
                c.fecha_emision,
                COALESCE(ec.estado, 'SIN_REGISTRO') AS estado,
                COALESCE(ec.flujo_id, (SELECT hf.flujo_id FROM historico_flujo hf WHERE hf.tipo_tramite_id = 2 AND hf.tramite_id = c.id ORDER BY hf.id DESC LIMIT 1)) AS flujo_id,
                eos.cotizacion_origen_id AS oferta_origen_id,
                eos.numero AS numero_seccion,
                eos.nombre AS nombre_seccion,
                eos.estado AS estado_seccion,
                SUM($netoOfertaExpr) AS total_ofertado,
                SUM(GREATEST(($brutoOfertaExpr) - ($netoOfertaExpr), 0)) AS descuento,
                SUM(COALESCE(ppc.precio_base_venta, 0) * chp.cantidad) AS total_costo_oferta,
                SUM(chp.cantidad) AS cantidad_ofertada,
                COALESCE(fact.total_facturado, 0) AS total_facturado,
                COALESCE(fact.total_costo, 0) AS total_costo,
                COALESCE(fact.cantidad_facturada, 0) AS cantidad_facturada,
                COALESCE(fact.num_facturas, 0) AS num_facturas
            {$this->joinsOfertado()}
            LEFT JOIN users u ON u.id = c.vendedor
            LEFT JOIN users ut ON ut.id = c.users_id
            LEFT JOIN expo_oferta_seccion eos ON eos.cotizacion_id = c.id
            {$this->joinExpoCotizacion($expoId)}
            LEFT JOIN (
                SELECT chp2.cotizacion_id,
                       SUM(vhp2.sub_total_s) AS total_facturado,
                       SUM(COALESCE(ppc_vhp2.costoproducto, ppc_chp2.costoproducto, p2.costo_promedio, 0) * vhp2.cantidad_s) AS total_costo,
                       SUM(COALESCE(NULLIF(vhp2.cantidad_oferta_aplicada, 0), vhp2.cantidad_s)) AS cantidad_facturada,
                       COUNT(DISTINCT f2.id) AS num_facturas
                FROM cotizacion_has_producto chp2
                INNER JOIN (
                    SELECT v2.*, v2.cotizacion_has_producto_id AS chp_id_resuelta, NULL AS cotizacion_id_prefactura
                    FROM venta_has_producto v2
                    WHERE v2.cotizacion_has_producto_id IS NOT NULL

                    UNION ALL

                    SELECT v2.*, php_fact2.cotizacion_has_producto_id AS chp_id_resuelta, pf_fact2.cotizacion_id AS cotizacion_id_prefactura
                    FROM venta_has_producto v2
                    INNER JOIN prefactura_auditoria pa_fact2 ON pa_fact2.factura_id = v2.factura_id
                    INNER JOIN prefactura pf_fact2 ON pf_fact2.id = pa_fact2.prefactura_id
                    INNER JOIN prefactura_has_producto php_fact2
                        ON php_fact2.prefactura_id = pf_fact2.id
                       AND php_fact2.producto_id = v2.producto_id
                       AND php_fact2.indice = v2.indice
                    WHERE v2.cotizacion_has_producto_id IS NULL
                ) vhp2 ON vhp2.chp_id_resuelta = chp2.id
                      AND (vhp2.cotizacion_id_prefactura IS NULL OR vhp2.cotizacion_id_prefactura = chp2.cotizacion_id)
                INNER JOIN factura f2 ON f2.id = vhp2.factura_id AND f2.estado_venta_id = 1
                INNER JOIN producto p2 ON p2.id = chp2.producto_id
                LEFT JOIN precios_producto_carga ppc_vhp2 ON ppc_vhp2.id = vhp2.precios_producto_carga_id
                LEFT JOIN precios_producto_carga ppc_chp2 ON ppc_chp2.id = chp2.precios_producto_carga_id
                WHERE chp2.cotizacion_id IN ({$this->inExpo($expoId)})
                GROUP BY chp2.cotizacion_id
            ) fact ON fact.cotizacion_id = c.id
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY c.id, c.nombre_cliente, u.name, ut.name, c.fecha_emision, ec.estado,
                     eos.cotizacion_origen_id, eos.numero, eos.nombre, eos.estado,
                     ec.flujo_id, fact.total_facturado, fact.total_costo,
                     fact.cantidad_facturada, fact.num_facturas
            ORDER BY total_ofertado DESC
        ");

        $filas = array_map(function ($row) {
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
                'oferta_origen_id' => $row->oferta_origen_id ? (int) $row->oferta_origen_id : null,
                'numero_seccion' => $row->numero_seccion ? (int) $row->numero_seccion : null,
                'nombre_seccion' => $row->nombre_seccion,
                'estado_seccion' => $row->estado_seccion,
                'estado_facturacion' => $estadoFacturacion,
                'num_facturas' => (int) $row->num_facturas,
                'total_ofertado' => round($totalOfertado, 2),
                'total_facturado' => round($totalFacturado, 2),
                'descuento' => round((float) $row->descuento, 2),
                'utilidad' => round($utilidad, 2),
                'margen_pct' => $totalOfertado > 0 ? round(($utilidad / $totalOfertado) * 100, 2) : null,
                'avance_pct' => $totalOfertado > 0 ? round(($totalFacturado / $totalOfertado) * 100, 2) : 0,
                // Campos crudos (no redondeados) usados solo para la agregación por flujo.
                'cantidad_ofertada' => $cantidadOfertada,
                'cantidad_facturada' => $cantidadFacturada,
                'total_costo_oferta' => $totalCostoOferta,
            ];
        }, $rows);

        return $this->agruparPorFlujo($filas);
    }

    public function tablaOfertas(Request $r)
    {
        return response()->json($this->datosOfertas($r));
    }

    public function exportarOfertas(Request $r)
    {
        $data = $this->datosOfertas($r);

        $headings = ['Oferta #', 'Flujo', '# Secciones', 'Cliente', 'Asesor', 'Teleasesor', 'Fecha', 'Estado', 'Estado Facturacion', 'Facturas', 'Total Ofertado (L)', 'Total Facturado (L)', 'Margen Oferta %', 'Ganancia Oferta (L)', 'Descuento (L)', 'Avance %'];
        $rows = array_map(fn ($o) => [
            $o['oferta_id'], $o['flujo_id'], $o['num_secciones'],
            $o['cliente'], $o['asesor'], $o['teleasesor'],
            $o['fecha_emision'], $o['estado'], $o['estado_facturacion'], $o['num_facturas'],
            $o['total_ofertado'], $o['total_facturado'], $o['margen_pct'], $o['utilidad'],
            $o['descuento'], $o['avance_pct'],
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
            {$this->whereExpo($expoId)}
            ORDER BY nombre
        ");

        $escalas = DB::select("
            SELECT DISTINCT cp.id, cp.nombre
            {$this->joinsOfertado()}
            {$this->whereExpo($expoId)}
            AND cp.id IS NOT NULL
            ORDER BY cp.nombre
        ");

        $vendedores = DB::select("
            SELECT DISTINCT c.vendedor AS id, u.name AS nombre
            FROM cotizacion c
            LEFT JOIN users u ON u.id = c.vendedor
            WHERE c.id IN ({$this->inExpo($expoId)})
              AND c.vendedor IS NOT NULL
            ORDER BY u.name
        ");

        $teleasesores = DB::select("
            SELECT DISTINCT c.users_id AS id, u.name AS nombre
            FROM cotizacion c
            LEFT JOIN users u ON u.id = c.users_id
            WHERE c.id IN ({$this->inExpo($expoId)})
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
            {$this->whereExpo($expoId)}
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
