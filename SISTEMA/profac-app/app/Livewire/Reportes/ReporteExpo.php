<?php

namespace App\Livewire\Reportes;

use App\Exports\Reportes\Expo\EtiquetasExpo;
use App\Exports\Reportes\Expo\HojaTablaExpo;
use App\Exports\Reportes\Expo\LibroExpoExport;
use App\Exports\Reportes\Expo\ResumenExpoSheet;
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
 *  - Costo de línea facturada => precio_base_venta de la línea de la oferta
 *    (mismo costo que la utilidad ofertada); si no existe, el del price load
 *    usado al facturar.
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
    private array $cachePrincipales = [];
    private array $cacheTipos = [];
    private array $cacheFacturasCotizacion = [];
    private array $estadosEntregaActuales = [];
    private array $cacheEstadoEntregaFactura = [];

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

    /**
     * Costo unitario de una línea facturada: el precio base de la línea de la
     * oferta (el mismo que usa la utilidad ofertada). costoproducto viene por
     * empaque y no sirve como costo por unidad vendida.
     */
    private function costoUnitarioExpr(): string
    {
        return 'COALESCE(ppc.precio_base_venta, ppc_vhp.precio_base_venta, 0)';
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

    /**
     * Ids de las ofertas principales de la Expo (las que no son sección de
     * otra). Lo ofertado se mide solo sobre ellas: sus líneas ya contienen
     * todo el pedido y las secciones son particiones que lo duplicarían.
     */
    private function ofertasPrincipalesExpo(int $expoId): array
    {
        if (isset($this->cachePrincipales[$expoId])) {
            return $this->cachePrincipales[$expoId];
        }

        $ids = $this->cotizacionIdsExpo($expoId);
        $secciones = DB::table('expo_oferta_seccion')
            ->whereIn('cotizacion_id', $ids)
            ->pluck('cotizacion_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $principales = array_values(array_diff($ids, $secciones));

        return $this->cachePrincipales[$expoId] = $principales ?: [0];
    }

    /**
     * FROM + JOINs comunes al lado "ofertado" (sin facturación). 1 fila = 1 línea ofertada.
     * Con $principal = true el alias "c" (y por ende ec y los filtros) apunta a la
     * oferta principal aunque la línea pertenezca a una sección; así lo facturado
     * en secciones se atribuye al cliente, asesor, estado y fecha de su oferta.
     */
    private function joinsOfertado(bool $principal = false): string
    {
        $joinCotizacion = $principal
            ? "LEFT JOIN expo_oferta_seccion eos_principal ON eos_principal.cotizacion_id = chp.cotizacion_id
            INNER JOIN cotizacion c ON c.id = COALESCE(eos_principal.cotizacion_origen_id, chp.cotizacion_id)"
            : 'INNER JOIN cotizacion c ON c.id = chp.cotizacion_id';

        return "
            FROM cotizacion_has_producto chp
            $joinCotizacion
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
    private function joinsFacturado(int $expoId): string
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
                  AND pf_fact.cotizacion_id IN ({$this->inExpo($expoId)})
            ) vhp ON vhp.chp_id_resuelta = chp.id
                 AND (vhp.cotizacion_id_prefactura IS NULL OR vhp.cotizacion_id_prefactura = chp.cotizacion_id)
            INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_venta_id = 1
            LEFT JOIN precios_producto_carga ppc_vhp ON ppc_vhp.id = vhp.precios_producto_carga_id
        ";
    }

    private function joinExpoCotizacion(int $expoId): string
    {
        return "LEFT JOIN expo_cotizacion ec ON ec.cotizacion_id = c.id AND ec.expo_id = {$expoId}";
    }

    private function joinUltimoHistoricoOferta(int $expoId): string
    {
        return "
            LEFT JOIN (
                SELECT tramite_id, MAX(id) AS ultimo_id
                FROM historico_flujo
                WHERE tipo_tramite_id = 2
                  AND tramite_id IN ({$this->inExpo($expoId)})
                GROUP BY tramite_id
            ) ultimo_hf ON ultimo_hf.tramite_id = c.id
            LEFT JOIN historico_flujo hf_oferta ON hf_oferta.id = ultimo_hf.ultimo_id
        ";
    }

    private function whereExpo(int $expoId): string
    {
        return "WHERE chp.cotizacion_id IN ({$this->inExpo($expoId)})";
    }

    private function whereOfertaPrincipal(int $expoId): string
    {
        return 'WHERE chp.cotizacion_id IN (' . implode(',', $this->ofertasPrincipalesExpo($expoId)) . ')';
    }

    /** Ofertas principales que cumplen los filtros, con su teleasesor. */
    private function principalesFiltradas(int $expoId, string $extra): array
    {
        return DB::select("
            SELECT DISTINCT c.id, c.users_id
            {$this->joinsOfertado(true)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
        ");
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
        $tipo = strtoupper((string) $r->input('tipo_oferta', ''));
        if (in_array($tipo, [self::TIPO_GANADORA, self::TIPO_NO_GANADORA, self::TIPO_NO_GANADORA_CON_SECCIONES], true)) {
            $sql .= ' AND c.id IN (' . implode(',', $this->idsPorTipoOferta($this->expoIdDesdeRequest($r), $tipo)) . ')';
        }
        $estadoEntrega = strtoupper((string) $r->input('estado_entrega', ''));
        if (in_array($estadoEntrega, self::ESTADOS_ENTREGA, true)) {
            $sql .= ' AND c.id IN (' . implode(',', $this->idsPorEstadoEntrega($this->expoIdDesdeRequest($r), $estadoEntrega)) . ')';
        }
        $descartadas = $this->idsDescartados($this->expoIdDesdeRequest($r));
        if ($descartadas) {
            $sql .= ' AND c.id NOT IN (' . implode(',', $descartadas) . ')';
        }

        return $sql;
    }

    /**
     * Tipo de cada oferta principal de la Expo (GANADORA, NO_GANADORA o
     * NO_GANADORA_CON_SECCIONES) junto con los ids de sus secciones.
     * Una NO_GANADORA sin secciones cuyo flujo ya tiene una ganadora queda
     * "descartada": fue una alternativa que perdió y no se muestra en el
     * reporte (ni tablas, ni gráficas, ni métricas).
     */
    private function tiposOfertaExpo(int $expoId): array
    {
        if (isset($this->cacheTipos[$expoId])) {
            return $this->cacheTipos[$expoId];
        }

        $principales = array_values(array_filter($this->ofertasPrincipalesExpo($expoId)));
        $seccionesPorOrigen = [];
        DB::table('expo_oferta_seccion')
            ->whereIn('cotizacion_id', $this->cotizacionIdsExpo($expoId))
            ->get(['cotizacion_id', 'cotizacion_origen_id'])
            ->each(function ($s) use (&$seccionesPorOrigen) {
                $seccionesPorOrigen[(int) $s->cotizacion_origen_id][] = (int) $s->cotizacion_id;
            });
        $ganadoras = $this->ofertasGanadorasVigentes($principales);

        $flujos = [];
        if ($principales) {
            foreach (DB::select("
                SELECT c.id, COALESCE(ec.flujo_id, hf_oferta.flujo_id) AS flujo_id
                FROM cotizacion c
                {$this->joinExpoCotizacion($expoId)}
                {$this->joinUltimoHistoricoOferta($expoId)}
                WHERE c.id IN (" . implode(',', $principales) . ')
            ') as $row) {
                $flujos[(int) $row->id] = $row->flujo_id ? (int) $row->flujo_id : null;
            }
        }

        $tipos = [];
        $flujosConGanadora = [];
        foreach ($principales as $id) {
            $secciones = $seccionesPorOrigen[$id] ?? [];
            $tipo = isset($ganadoras[$id])
                ? self::TIPO_GANADORA
                : ($secciones ? self::TIPO_NO_GANADORA_CON_SECCIONES : self::TIPO_NO_GANADORA);
            $flujo = $flujos[$id] ?? null;
            if ($tipo === self::TIPO_GANADORA && $flujo !== null) {
                $flujosConGanadora[$flujo] = true;
            }
            $tipos[$id] = ['tipo' => $tipo, 'secciones' => $secciones, 'flujo_id' => $flujo, 'descartada' => false];
        }
        foreach ($tipos as $id => $info) {
            if ($info['tipo'] === self::TIPO_NO_GANADORA && $info['flujo_id'] !== null && isset($flujosConGanadora[$info['flujo_id']])) {
                $tipos[$id]['descartada'] = true;
            }
        }

        return $this->cacheTipos[$expoId] = $tipos;
    }

    /** Ofertas no ganadoras sin secciones de un flujo que ya tiene ganadora. */
    private function idsDescartados(int $expoId): array
    {
        return array_keys(array_filter($this->tiposOfertaExpo($expoId), fn ($info) => $info['descartada']));
    }

    /**
     * Ids (ofertas principales + sus secciones) de un tipo de oferta.
     * NO_GANADORA incluye también las no ganadoras que conservan secciones.
     */
    private function idsPorTipoOferta(int $expoId, string $tipo): array
    {
        $tipos = $tipo === self::TIPO_NO_GANADORA
            ? [self::TIPO_NO_GANADORA, self::TIPO_NO_GANADORA_CON_SECCIONES]
            : [$tipo];
        $ids = [];
        foreach ($this->tiposOfertaExpo($expoId) as $id => $info) {
            if (in_array($info['tipo'], $tipos, true) && !$info['descartada']) {
                $ids[] = $id;
                array_push($ids, ...$info['secciones']);
            }
        }

        return $ids ?: [0];
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
            {$this->joinsOfertado(true)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
        ");

        $costoExpr = $this->costoUnitarioExpr();
        $rowFacturado = DB::selectOne("
            SELECT
                COUNT(DISTINCT f.id) AS num_facturas,
                COALESCE(SUM(vhp.sub_total_s), 0) AS total_facturado,
                COALESCE(SUM(GREATEST((vhp.precio_unidad * vhp.cantidad_s) - vhp.sub_total_s, 0)), 0) AS total_descuento,
                COALESCE(SUM(($costoExpr) * vhp.cantidad_s), 0) AS total_costo
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
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
        $principales = array_map(fn ($row) => (int) $row->id, $this->principalesFiltradas($expoId, $extra));
        $numGanadoras = count(array_intersect_key($this->ofertasGanadorasVigentes($principales), array_flip($principales)));

        return response()->json([
            'expo_id' => $expoId,
            'num_ofertas' => (int) $rowOfertado->num_ofertas,
            'num_ganadoras' => $numGanadoras,
            'num_no_ganadoras' => (int) $rowOfertado->num_ofertas - $numGanadoras,
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
            {$this->joinsOfertado(true)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
            GROUP BY COALESCE(ec.estado,'SIN_REGISTRO')
            ORDER BY total DESC
        ");

        $facturado = DB::select("
            SELECT COALESCE(ec.estado,'SIN_REGISTRO') AS estado,
                   COALESCE(SUM(vhp.sub_total_s),0) AS facturado
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
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
            {$this->joinsOfertado(true)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
            GROUP BY p.marca_id, m.nombre
        ");

        $facturado = DB::select("
            SELECT p.marca_id, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
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
            {$this->joinsOfertado(true)}
            LEFT JOIN users u ON u.id = c.vendedor
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
            GROUP BY c.vendedor, u.name
        ");

        $facturado = DB::select("
            SELECT c.vendedor AS vendedor_id, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
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
            {$this->joinsOfertado(true)}
            LEFT JOIN users ut ON ut.id = c.users_id
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
            GROUP BY c.users_id, ut.name
        ");

        $costoExpr = $this->costoUnitarioExpr();
        $facturado = DB::select("
            SELECT c.users_id AS teleasesor_id,
                   SUM(vhp.sub_total_s) AS facturado,
                   SUM(($costoExpr) * vhp.cantidad_s) AS costo
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY c.users_id
        ");
        $facturadoPorTeleasesor = collect($facturado)->keyBy('teleasesor_id');

        // "Ganadas" = ofertas principales que son ganadoras vigentes.
        $principales = $this->principalesFiltradas($expoId, $extra);
        $ganadoras = $this->ofertasGanadorasVigentes(array_map(fn ($row) => (int) $row->id, $principales));
        $ganadasPorTeleasesor = [];
        foreach ($principales as $row) {
            if (isset($ganadoras[(int) $row->id])) {
                $clave = (string) ($row->users_id ?? '');
                $ganadasPorTeleasesor[$clave] = ($ganadasPorTeleasesor[$clave] ?? 0) + 1;
            }
        }

        $rows = collect($ofertado)->map(function ($row) use ($facturadoPorTeleasesor, $ganadasPorTeleasesor) {
            $factura = $facturadoPorTeleasesor->get($row->teleasesor_id);
            $facturado = (float) ($factura->facturado ?? 0);
            $costo = (float) ($factura->costo ?? 0);
            $utilidad = $facturado - $costo;
            $ofertas = (int) $row->ofertas;
            $ganadas = $ganadasPorTeleasesor[(string) ($row->teleasesor_id ?? '')] ?? 0;

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
            {$this->joinsOfertado(true)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
            GROUP BY COALESCE(c.cliente_id, 0), COALESCE(NULLIF(c.nombre_cliente, ''), 'Sin cliente')
        ");

        $facturado = DB::select("
            SELECT COALESCE(c.cliente_id, 0) AS cliente_id,
                   SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
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
            {$this->joinsOfertado(true)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
            $extra
            GROUP BY DATE(c.fecha_emision)
        ");

        $facturado = DB::select("
            SELECT DATE(f.fecha_emision) AS fecha, SUM(vhp.sub_total_s) AS facturado
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
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
            {$this->joinsOfertado(true)}
            {$this->joinExpoCotizacion($expoId)}
            {$this->whereOfertaPrincipal($expoId)}
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
            {$this->joinsOfertado(true)}
            {$this->joinsFacturado($expoId)}
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
                 COALESCE(ec.flujo_id, hf_oferta.flujo_id) AS flujo_id,
                 COALESCE(um.nombre, 'N/A') AS unidad_medida,
                 chp.cantidad AS cantidad_ofertada,
                 $netoOfertaExpr AS total_ofertado,
                 GREATEST(($brutoOfertaExpr) - ($netoOfertaExpr), 0) AS descuento,
                 COALESCE(ppc.precio_base_venta, 0) * chp.cantidad AS costo_ofertado
            {$this->joinsOfertado()}
            LEFT JOIN unidad_medida um ON um.id = uv.unidad_medida_id
            {$this->joinExpoCotizacion($expoId)}
            {$this->joinUltimoHistoricoOferta($expoId)}
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
            {$this->joinsFacturado($expoId)}
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
        $baseFacturas = $r->input('rentabilidad_base') === 'facturas';
        $entidad = $baseFacturas ? 'factura' : 'oferta';
        $info = $this->infoExcel($r);
        $claveVenta = $baseFacturas ? 'facturado' : 'ofertado';
        $columnasRentabilidad = [
            ['titulo' => 'Descuento ' . $entidad, 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Costo ' . $entidad, 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Utilidad ' . $entidad, 'clave' => 'utilidad', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Margen ' . $entidad . ' %', 'formato' => 'pct', 'total' => ['ratio', 'utilidad', $claveVenta], 'ancho' => 12],
        ];

        $productos = new HojaTablaExpo('Productos', 'Reporte Expo · Analítica de productos por ' . $entidad . 's', $info, array_merge([
            ['titulo' => 'Código', 'ancho' => 16],
            ['titulo' => 'Producto', 'ancho' => 42],
            ['titulo' => 'Marca'],
            ['titulo' => 'Categoría'],
            ['titulo' => 'Ofertas', 'formato' => 'entero', 'ancho' => 10],
            ['titulo' => 'Cant. ofertada', 'formato' => 'cantidad', 'total' => 'suma'],
            ['titulo' => 'Cant. facturada', 'formato' => 'cantidad', 'total' => 'suma'],
            ['titulo' => 'Ofertado', 'clave' => 'ofertado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
            ['titulo' => 'Facturado', 'clave' => 'facturado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
            ['titulo' => '% Avance', 'formato' => 'pct', 'total' => ['ratio', 'facturado', 'ofertado'], 'ancho' => 11],
        ], $columnasRentabilidad), array_map(fn ($p) => [
            $p['codigo'], $p['producto'], $p['marca'], $p['categoria'], $p['numero_ofertas'],
            $p['cantidad_ofertada'], $p['cantidad_facturada'], $p['total_ofertado'], $p['total_facturado'],
            $p['total_ofertado'] > 0 ? $p['total_facturado'] * 100 / $p['total_ofertado'] : 0,
            $p['descuento'], $p['total_costo'], $p['utilidad'], $p['margen_pct'],
        ], $this->datosProductos($r)), [
            'Una fila por producto. Descuento, costo, utilidad y margen se calculan sobre lo ' . ($baseFacturas ? 'facturado' : 'ofertado') . ' (opción "Ver resultados por" del reporte).',
        ], 2);

        $tipos = $this->tiposOfertaExpo($this->expoIdDesdeRequest($r));
        $detalle = new HojaTablaExpo('Detalle por oferta', 'Reporte Expo · Productos por oferta', $info, array_merge([
            ['titulo' => 'Oferta #', 'formato' => 'id', 'ancho' => 10],
            ['titulo' => 'Tipo', 'ancho' => 22, 'colores' => EtiquetasExpo::COLORES_TIPO],
            ['titulo' => 'Cliente', 'ancho' => 32],
            ['titulo' => 'Flujo', 'formato' => 'id', 'ancho' => 9],
            ['titulo' => 'Código', 'ancho' => 16],
            ['titulo' => 'ID producto', 'formato' => 'id', 'ancho' => 11],
            ['titulo' => 'Producto', 'ancho' => 42],
            ['titulo' => 'Marca'],
            ['titulo' => 'Categoría'],
            ['titulo' => 'Unidad', 'ancho' => 12],
            ['titulo' => 'Cant. ofertada', 'formato' => 'cantidad', 'total' => 'suma'],
            ['titulo' => 'Cant. facturada', 'formato' => 'cantidad', 'total' => 'suma'],
            ['titulo' => 'Ofertado', 'clave' => 'ofertado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
            ['titulo' => 'Facturado', 'clave' => 'facturado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
        ], $columnasRentabilidad), array_map(fn ($p) => [
            $p['oferta_id'], EtiquetasExpo::estado($tipos[$p['oferta_id']]['tipo'] ?? null), $p['cliente'], $p['flujo_id'],
            $p['codigo'], $p['producto_id'], $p['producto'], $p['marca'], $p['categoria'], $p['unidad_medida'],
            $p['cantidad_ofertada'], $p['cantidad_facturada'], $p['total_ofertado'], $p['total_facturado'],
            $p['descuento'], $p['total_costo'], $p['utilidad'], $p['margen_pct'],
        ], $this->datosProductosDetalle($r)), [], 1);

        return Excel::download(new LibroExpoExport([$productos, $detalle]), $this->nombreExcel($r, 'productos'));
    }

    /** Líneas de contexto de los Excel: Expo, fecha de generación y filtros aplicados. */
    private function infoExcel(Request $r): array
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $expo = DB::table('expo')->where('id', $expoId)->first(['nombre', 'fecha_inicio', 'fecha_fin']);
        $fecha = fn ($valor) => $valor ? date('d/m/Y', strtotime((string) $valor)) : null;
        $periodo = $expo && $expo->fecha_inicio
            ? ' (' . $fecha($expo->fecha_inicio) . ($expo->fecha_fin ? ' al ' . $fecha($expo->fecha_fin) : '') . ')'
            : '';
        $usuario = optional(auth()->user())->name;

        return [
            'Expo: ' . ($expo->nombre ?? '#' . $expoId) . $periodo,
            'Generado el ' . now()->format('d/m/Y H:i') . ($usuario ? ' por ' . $usuario : '') . ' · Montos en lempiras sin ISV',
            'Filtros: ' . $this->descripcionFiltros($r),
        ];
    }

    private function descripcionFiltros(Request $r): string
    {
        $partes = [];
        $nombres = function (string $tabla, string $columna, array $ids): string {
            return DB::table($tabla)->whereIn('id', $ids)->orderBy($columna)->pluck($columna)->implode(', ');
        };

        $tipo = strtoupper((string) $r->input('tipo_oferta', ''));
        if ($tipo === self::TIPO_GANADORA) {
            $partes[] = 'Tipo: ganadoras';
        } elseif (in_array($tipo, [self::TIPO_NO_GANADORA, self::TIPO_NO_GANADORA_CON_SECCIONES], true)) {
            $partes[] = 'Tipo: no ganadoras';
        }
        if ($r->estado && in_array($r->estado, self::ESTADOS_VALIDOS, true)) {
            $partes[] = 'Estado: ' . EtiquetasExpo::estado($r->estado);
        }
        $estadoEntrega = strtoupper((string) $r->input('estado_entrega', ''));
        if (in_array($estadoEntrega, self::ESTADOS_ENTREGA, true)) {
            $partes[] = 'Entrega: ' . mb_strtolower(EtiquetasExpo::estado($estadoEntrega));
        }
        foreach ([
            ['marca_ids', 'marca_id', 'marca', 'nombre', 'Marca'],
            ['escala_ids', 'escala_id', 'categoria_precios', 'nombre', 'Escala'],
            ['vendedor_ids', 'vendedor_id', 'users', 'name', 'Asesor'],
            ['teleasesor_ids', 'teleasesor_id', 'users', 'name', 'Teleasesor'],
        ] as [$plural, $singular, $tabla, $columna, $etiqueta]) {
            $ids = $this->idsDesdeRequest($r, $plural, $singular);
            if ($ids) {
                $partes[] = $etiqueta . ': ' . ($nombres($tabla, $columna, $ids) ?: implode(', ', $ids));
            }
        }
        if ($r->filled('cliente_id')) {
            $partes[] = 'Cliente: ' . (DB::table('cotizacion')->where('cliente_id', (int) $r->cliente_id)->value('nombre_cliente') ?? '#' . (int) $r->cliente_id);
        }
        if ($r->filled('producto_id')) {
            $partes[] = 'Producto: ' . (DB::table('producto')->where('id', (int) $r->producto_id)->value('nombre') ?? '#' . (int) $r->producto_id);
        }
        $desde = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $r->fecha_desde) ? date('d/m/Y', strtotime($r->fecha_desde)) : null;
        $hasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $r->fecha_hasta) ? date('d/m/Y', strtotime($r->fecha_hasta)) : null;
        if ($desde || $hasta) {
            $partes[] = 'Fecha de oferta: ' . ($desde ? 'desde ' . $desde : '') . ($desde && $hasta ? ' ' : '') . ($hasta ? 'hasta ' . $hasta : '');
        }
        if ($r->input('rentabilidad_base') === 'facturas') {
            $partes[] = 'Rentabilidad por facturas';
        }

        return $partes ? implode(' · ', $partes) : 'ninguno (toda la Expo)';
    }

    private function nombreExcel(Request $r, string $tipo): string
    {
        $expo = DB::table('expo')->where('id', $this->expoIdDesdeRequest($r))->value('nombre') ?? 'expo';
        $slug = trim(preg_replace('/[^a-z0-9]+/', '_', mb_strtolower(\Illuminate\Support\Str::ascii($expo))), '_') ?: 'expo';

        return "reporte_expo_{$tipo}_{$slug}_" . now()->format('Ymd_His') . '.xlsx';
    }

    public const TIPO_GANADORA = 'GANADORA';
    public const TIPO_NO_GANADORA = 'NO_GANADORA';
    public const TIPO_NO_GANADORA_CON_SECCIONES = 'NO_GANADORA_CON_SECCIONES';

    public const ENTREGA_ENTREGADA = 'ENTREGADA';
    public const ENTREGA_PARCIAL = 'PARCIALMENTE_DISTRIBUIDA';
    public const ENTREGA_NO_ENTREGADA = 'NO_ENTREGADA';
    private const ESTADOS_ENTREGA = [self::ENTREGA_ENTREGADA, self::ENTREGA_PARCIAL, self::ENTREGA_NO_ENTREGADA];

    /**
     * Estado del último envío activo (no anulado ni cancelado) de cada
     * factura: entregado, parcial o sin_entrega. Las facturas sin envío no
     * aparecen y cuentan como no distribuidas.
     */
    private function estadosEntregaFacturas(array $facturaIds): array
    {
        $facturaIds = array_values(array_unique(array_filter(array_map('intval', $facturaIds))));
        $estados = [];
        foreach (array_chunk($facturaIds, 1000) as $lote) {
            DB::table('distribuciones_entrega_facturas as def')
                ->joinSub(
                    DB::table('distribuciones_entrega_facturas as def2')
                        ->join('distribuciones_entrega as d', 'd.id', '=', 'def2.distribucion_entrega_id')
                        ->where('def2.estado_entrega', '<>', 'anulada')
                        ->where('d.estado_id', '<>', 4)
                        ->whereIn('def2.factura_id', $lote)
                        ->groupBy('def2.factura_id')
                        ->selectRaw('def2.factura_id, MAX(def2.id) AS ultimo_id'),
                    'ult',
                    'ult.ultimo_id',
                    '=',
                    'def.id'
                )
                ->get(['def.factura_id', 'def.estado_entrega'])
                ->each(function ($row) use (&$estados) {
                    $estados[(int) $row->factura_id] = $row->estado_entrega;
                });
        }

        return $estados;
    }

    /**
     * Conteo de facturas por estado de entrega (último envío activo):
     * distribuidas (entregado), parciales (no se distribuyeron todos los
     * productos) y sin distribuir (pendientes o sin envío). Las tres suman
     * el total de facturas.
     */
    private function conteoEntregaFacturas(array $facturaIds): array
    {
        $conteo = ['num_entregadas' => 0, 'num_parciales' => 0, 'num_sin_entregar' => 0];
        foreach (array_unique($facturaIds) as $id) {
            $estado = $this->estadosEntregaActuales[$id] ?? null;
            $conteo[$estado === 'entregado' ? 'num_entregadas' : ($estado === 'parcial' ? 'num_parciales' : 'num_sin_entregar')]++;
        }
        $total = array_sum($conteo);

        return $conteo + [
            'num_facturas_entrega' => $total,
            'entrega_pct' => $total > 0 ? round(($conteo['num_entregadas'] / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Estado de entrega de una oferta según sus facturas: ENTREGADA si todas
     * se entregaron completas, NO_ENTREGADA si ninguna tiene productos
     * distribuidos y PARCIALMENTE_DISTRIBUIDA si no se distribuyeron todos
     * los productos facturados. Null cuando la oferta no tiene facturas.
     */
    private function estadoEntregaOferta(array $facturaIds, array $estados): ?string
    {
        $facturaIds = array_unique($facturaIds);
        if (!$facturaIds) {
            return null;
        }
        $entregadas = 0;
        $parciales = 0;
        foreach ($facturaIds as $id) {
            $estado = $estados[$id] ?? null;
            if ($estado === 'entregado') {
                $entregadas++;
            } elseif ($estado === 'parcial') {
                $parciales++;
            }
        }

        if ($entregadas === count($facturaIds)) {
            return self::ENTREGA_ENTREGADA;
        }

        return $entregadas + $parciales === 0 ? self::ENTREGA_NO_ENTREGADA : self::ENTREGA_PARCIAL;
    }

    /**
     * Líneas de factura resueltas a su línea de oferta: directas o a través
     * de la prefactura (producto + índice) de una cotización de la Expo.
     */
    private function lineasFacturadasSql(int $expoId): string
    {
        return "
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
              AND pf_fact2.cotizacion_id IN ({$this->inExpo($expoId)})
        ";
    }

    /** Estado de entrega (ENTREGADA / PARCIALMENTE_DISTRIBUIDA / NO_ENTREGADA) de cada factura de la Expo. */
    private function estadoEntregaPorFactura(int $expoId): array
    {
        if (isset($this->cacheEstadoEntregaFactura[$expoId])) {
            return $this->cacheEstadoEntregaFactura[$expoId];
        }
        $facturas = $this->facturasPorCotizacion($expoId);
        $estados = $this->estadosEntregaFacturas(array_merge(...array_values($facturas)));
        $resultado = [];
        foreach ($facturas as $ids) {
            foreach ($ids as $id) {
                $resultado[$id] = match ($estados[$id] ?? null) {
                    'entregado' => self::ENTREGA_ENTREGADA,
                    'parcial' => self::ENTREGA_PARCIAL,
                    default => self::ENTREGA_NO_ENTREGADA,
                };
            }
        }

        return $this->cacheEstadoEntregaFactura[$expoId] = $resultado;
    }

    /** Ids de las facturas de la Expo que están en el estado de entrega indicado. */
    private function facturasPorEstadoEntrega(int $expoId, string $estado): array
    {
        return array_keys(array_filter($this->estadoEntregaPorFactura($expoId), fn ($e) => $e === $estado)) ?: [0];
    }

    /** [cotizacion_id => [factura_id, ...]] con las facturas vigentes de la Expo. */
    private function facturasPorCotizacion(int $expoId): array
    {
        if (!isset($this->cacheFacturasCotizacion[$expoId])) {
            $facturas = [];
            foreach (DB::select("
                SELECT DISTINCT chp2.cotizacion_id, f2.id AS factura_id
                FROM cotizacion_has_producto chp2
                INNER JOIN ({$this->lineasFacturadasSql($expoId)}) vhp2
                    ON vhp2.chp_id_resuelta = chp2.id
                   AND (vhp2.cotizacion_id_prefactura IS NULL OR vhp2.cotizacion_id_prefactura = chp2.cotizacion_id)
                INNER JOIN factura f2 ON f2.id = vhp2.factura_id AND f2.estado_venta_id = 1
                WHERE chp2.cotizacion_id IN ({$this->inExpo($expoId)})
            ") as $row) {
                $facturas[(int) $row->cotizacion_id][] = (int) $row->factura_id;
            }
            $this->cacheFacturasCotizacion[$expoId] = $facturas;
        }

        return $this->cacheFacturasCotizacion[$expoId];
    }

    /** Ids (ofertas principales + secciones) de la Expo con un estado de entrega. */
    private function idsPorEstadoEntrega(int $expoId, string $estado): array
    {
        $facturas = $this->facturasPorCotizacion($expoId);
        $estadoFactura = $this->estadoEntregaPorFactura($expoId);

        // Oferta (con sus secciones) que tenga al menos una factura en ese estado.
        $ids = [];
        foreach ($this->tiposOfertaExpo($expoId) as $id => $info) {
            if ($info['descartada']) {
                continue;
            }
            $idsOferta = [$id, ...$info['secciones']];
            foreach ($idsOferta as $cotizacionId) {
                foreach ($facturas[$cotizacionId] ?? [] as $facturaId) {
                    if (($estadoFactura[$facturaId] ?? null) === $estado) {
                        array_push($ids, ...$idsOferta);
                        continue 3;
                    }
                }
            }
        }

        return $ids ?: [0];
    }

    /**
     * Devuelve los ids de las ofertas cuyo ÚLTIMO movimiento en
     * cotizacion_estado es ganadora = 1 (1 = ganadora, 2 = quitada,
     * 3 = anulada, 4 = devuelta desde revisión). Se evalúa por oferta y no
     * por flujo: un flujo puede tener varias ganadoras vigentes y marcar otra
     * oferta del mismo flujo no revoca a la ganadora anterior.
     */
    private function ofertasGanadorasVigentes(array $cotizacionIds): array
    {
        $cotizacionIds = array_values(array_unique(array_filter(array_map('intval', $cotizacionIds))));
        if (empty($cotizacionIds)) {
            return [];
        }

        $ids = DB::table('cotizacion_estado as ce')
            ->joinSub(
                DB::table('cotizacion_estado')
                    ->selectRaw('cotizacion_id, MAX(id) AS ultimo_id')
                    ->whereIn('cotizacion_id', $cotizacionIds)
                    ->groupBy('cotizacion_id'),
                'ult',
                'ult.ultimo_id',
                '=',
                'ce.id'
            )
            ->where('ce.ganadora', 1)
            ->pluck('ce.cotizacion_id');

        return array_fill_keys($ids->map(fn ($id) => (int) $id)->all(), true);
    }

    /**
     * Arma una fila por oferta principal siguiendo la jerarquía
     * Flujo → Oferta → Secciones → Facturas y la clasifica como:
     *  - GANADORA: ganadora vigente, con sus secciones (si las tiene).
     *  - NO_GANADORA: no ganadora y sin secciones.
     *  - NO_GANADORA_CON_SECCIONES: dejó de ser ganadora pero conserva secciones.
     *
     * El total ofertado se toma de la oferta principal (sus líneas ya incluyen
     * todo el pedido; las secciones son particiones de ella) y lo facturado
     * suma la oferta principal + sus secciones, porque cada factura referencia
     * líneas propias de la cotización facturada.
     */
    private function agruparOfertas(array $filas, array $tiposCanonicos = [], array $estadosEntrega = []): array
    {
        $raices = [];
        $seccionesPorOrigen = [];
        foreach ($filas as $fila) {
            if ($fila['oferta_origen_id'] === null) {
                $raices[$fila['oferta_id']] = $fila;
            } else {
                $seccionesPorOrigen[$fila['oferta_origen_id']][] = $fila;
            }
        }

        // Secciones cuya oferta principal quedó fuera por los filtros: se
        // agrupan bajo una fila sintética de su origen para no perderlas.
        foreach ($seccionesPorOrigen as $origenId => $secciones) {
            if (isset($raices[$origenId])) {
                continue;
            }
            $base = $secciones[0];
            $raices[$origenId] = array_merge($base, [
                'oferta_id' => (int) $origenId,
                'oferta_origen_id' => null,
                'numero_seccion' => null,
                'nombre_seccion' => null,
                'estado_seccion' => null,
                'num_facturas' => 0,
                'total_ofertado' => round(array_sum(array_column($secciones, 'total_ofertado')), 2),
                'total_facturado' => 0,
                'descuento' => round(array_sum(array_column($secciones, 'descuento')), 2),
                'cantidad_ofertada' => array_sum(array_column($secciones, 'cantidad_ofertada')),
                'cantidad_facturada' => 0,
                'total_costo_oferta' => array_sum(array_column($secciones, 'total_costo_oferta')),
                'total_costo_facturado' => 0,
                'total_pagado' => 0,
                'utilidad_pagada' => 0,
                'factura_ids' => [],
                'estado_facturacion' => 'NO_FACTURADA',
                'estado_entrega' => null,
                'origen_filtrado' => true,
            ]);
        }

        $ganadoras = $this->ofertasGanadorasVigentes(array_keys($raices));

        $resultado = [];
        foreach ($raices as $ofertaId => $raiz) {
            $secciones = $seccionesPorOrigen[$ofertaId] ?? [];
            usort($secciones, fn ($a, $b) => ($a['numero_seccion'] ?? 0) <=> ($b['numero_seccion'] ?? 0));

            $esGanadora = isset($ganadoras[$ofertaId]);
            // El tipo se toma de la Expo completa para que coincida con el filtro
            // por tipo aunque marca/producto oculten alguna sección.
            $tipo = $tiposCanonicos[$ofertaId]['tipo'] ?? ($esGanadora
                ? self::TIPO_GANADORA
                : (empty($secciones) ? self::TIPO_NO_GANADORA : self::TIPO_NO_GANADORA_CON_SECCIONES));

            $totalFacturado = (float) $raiz['total_facturado'];
            $cantidadFacturada = (float) $raiz['cantidad_facturada'];
            $costoFacturado = (float) $raiz['total_costo_facturado'];
            $totalPagado = (float) $raiz['total_pagado'];
            $utilidadPagada = (float) $raiz['utilidad_pagada'];
            $facturaIds = $raiz['factura_ids'];
            foreach ($secciones as $seccion) {
                $totalFacturado += (float) $seccion['total_facturado'];
                $cantidadFacturada += (float) $seccion['cantidad_facturada'];
                $costoFacturado += (float) $seccion['total_costo_facturado'];
                $totalPagado += (float) $seccion['total_pagado'];
                $utilidadPagada += (float) $seccion['utilidad_pagada'];
                $facturaIds = array_merge($facturaIds, $seccion['factura_ids']);
            }
            $facturaIds = array_values(array_unique($facturaIds));

            $totalOfertado = (float) $raiz['total_ofertado'];
            $cantidadOfertada = (float) $raiz['cantidad_ofertada'];
            $estadoFacturacion = $cantidadFacturada <= 0.0001
                ? 'NO_FACTURADA'
                : ($cantidadFacturada + 0.0001 >= $cantidadOfertada ? 'FACTURADA' : 'PARCIALMENTE_FACTURADA');

            $seccionesSalida = array_map(fn ($s) => [
                'oferta_id' => $s['oferta_id'],
                'numero_seccion' => $s['numero_seccion'],
                'nombre_seccion' => $s['nombre_seccion'],
                'estado' => $s['estado_seccion'],
                'estado_facturacion' => $s['estado_facturacion'],
                'estado_entrega' => $s['estado_entrega'] ?? null,
                'total_ofertado' => $s['total_ofertado'],
                'total_facturado' => $s['total_facturado'],
                'num_facturas' => $s['num_facturas'],
                'total_pagado' => round((float) $s['total_pagado'], 2),
                'utilidad_pagada' => round((float) $s['utilidad_pagada'], 2),
            ], $secciones);

            // Acceso directo a las líneas propias de la oferta principal: si no
            // hay secciones es la única forma de ver sus productos; si las hay,
            // permite revisar lo que aún no se repartió en ninguna sección.
            if (empty($raiz['origen_filtrado'])) {
                array_unshift($seccionesSalida, [
                    'oferta_id' => $raiz['oferta_id'],
                    'numero_seccion' => null,
                    'nombre_seccion' => 'Productos sin sección',
                    'estado' => null,
                    'estado_facturacion' => $raiz['estado_facturacion'],
                    'estado_entrega' => $raiz['estado_entrega'] ?? null,
                    'total_ofertado' => $raiz['total_ofertado'],
                    'total_facturado' => $raiz['total_facturado'],
                    'num_facturas' => $raiz['num_facturas'],
                    'total_pagado' => round((float) $raiz['total_pagado'], 2),
                    'utilidad_pagada' => round((float) $raiz['utilidad_pagada'], 2),
                    'es_raiz' => true,
                ]);
            }

            if ($estadoFacturacion === 'PARCIALMENTE_FACTURADA') {
                $seccionesSalida[] = [
                    'oferta_id' => null,
                    'numero_seccion' => null,
                    'nombre_seccion' => 'Productos sin factura',
                    'estado' => null,
                    'estado_facturacion' => 'NO_FACTURADA',
                    'estado_entrega' => null,
                    'total_ofertado' => round(max($totalOfertado - $totalFacturado, 0), 2),
                    'total_facturado' => 0,
                    'num_facturas' => 0,
                    'total_pagado' => 0,
                    'utilidad_pagada' => 0,
                    'es_pendiente' => true,
                ];
            }

            $utilidad = $totalOfertado - (float) $raiz['total_costo_oferta'];

            $resultado[] = array_merge([
                'oferta_id' => $raiz['oferta_id'],
                'flujo_id' => $raiz['flujo_id'],
                'tipo' => $tipo,
                'es_ganadora' => $esGanadora,
                'cliente_id' => $raiz['cliente_id'] ?? null,
                'cliente' => $raiz['cliente'],
                'asesor' => $raiz['asesor'],
                'teleasesor' => $raiz['teleasesor'],
                'fecha_emision' => $raiz['fecha_emision'],
                'estado' => $raiz['estado'],
                'oferta_origen_id' => null,
                'numero_seccion' => null,
                'nombre_seccion' => null,
                'estado_facturacion' => $estadoFacturacion,
                'estado_entrega' => $this->estadoEntregaOferta($facturaIds, $estadosEntrega),
                'num_facturas' => count($facturaIds),
                'factura_ids' => $facturaIds,
                'total_ofertado' => round($totalOfertado, 2),
                'total_facturado' => round($totalFacturado, 2),
                'total_costo_oferta' => round((float) $raiz['total_costo_oferta'], 2),
                'total_costo_facturado' => round($costoFacturado, 2),
                'utilidad_facturada' => round($totalFacturado - $costoFacturado, 2),
                'total_pagado' => round($totalPagado, 2),
                'utilidad_pagada' => round($utilidadPagada, 2),
                'descuento' => $raiz['descuento'],
                'utilidad' => round($utilidad, 2),
                'margen_pct' => $totalOfertado > 0 ? round(($utilidad / $totalOfertado) * 100, 2) : null,
                'avance_pct' => $totalOfertado > 0 ? round(($totalFacturado / $totalOfertado) * 100, 2) : 0,
                'num_secciones' => count($secciones),
                'secciones' => $seccionesSalida,
            ], $this->conteoEntregaFacturas($facturaIds));
        }

        $orden = [self::TIPO_GANADORA => 0, self::TIPO_NO_GANADORA_CON_SECCIONES => 1, self::TIPO_NO_GANADORA => 2];
        usort($resultado, fn ($a, $b) => $orden[$a['tipo']] <=> $orden[$b['tipo']]
            ?: $b['total_ofertado'] <=> $a['total_ofertado']
            ?: $b['oferta_id'] <=> $a['oferta_id']);

        return $resultado;
    }

    /**
     * Métricas de facturabilidad (monto facturado ÷ monto ofertado, sin ISV)
     * y conteo de ofertas por cliente a partir de las filas de agruparOfertas.
     * La métrica "conjunto" incluye los tres tipos de oferta.
     */
    private function resumenDesdeOfertas(array $ofertas): array
    {
        $vacio = fn () => [
            'num_ofertas' => 0, 'num_secciones' => 0, 'factura_ids' => [],
            'total_ofertado' => 0.0, 'total_facturado' => 0.0, 'total_costo_oferta' => 0.0,
            'utilidad_facturada' => 0.0, 'total_pagado' => 0.0, 'utilidad_pagada' => 0.0,
        ];
        $grupos = [
            'ganadoras' => $vacio(),
            'no_ganadoras' => $vacio(),
            'no_ganadoras_con_secciones' => $vacio(),
            'conjunto' => $vacio(),
        ];
        // "no_ganadoras" incluye a las que conservan secciones; además se
        // detallan aparte en "no_ganadoras_con_secciones".
        $claveGrupo = [
            self::TIPO_GANADORA => ['ganadoras'],
            self::TIPO_NO_GANADORA => ['no_ganadoras'],
            self::TIPO_NO_GANADORA_CON_SECCIONES => ['no_ganadoras', 'no_ganadoras_con_secciones'],
        ];

        $clientes = [];
        foreach ($ofertas as $o) {
            foreach ([...$claveGrupo[$o['tipo']], 'conjunto'] as $clave) {
                $grupos[$clave]['num_ofertas']++;
                $grupos[$clave]['num_secciones'] += $o['num_secciones'];
                $grupos[$clave]['total_ofertado'] += $o['total_ofertado'];
                $grupos[$clave]['total_facturado'] += $o['total_facturado'];
                $grupos[$clave]['total_costo_oferta'] += $o['total_costo_oferta'];
                $grupos[$clave]['utilidad_facturada'] += $o['utilidad_facturada'];
                $grupos[$clave]['total_pagado'] += $o['total_pagado'];
                $grupos[$clave]['utilidad_pagada'] += $o['utilidad_pagada'];
                foreach ($o['factura_ids'] as $facturaId) {
                    $grupos[$clave]['factura_ids'][$facturaId] = true;
                }
            }

            $claveCliente = $o['cliente_id'] !== null ? 'id_' . $o['cliente_id'] : 'nombre_' . mb_strtoupper(trim((string) $o['cliente']));
            $clientes[$claveCliente] ??= [
                'cliente_id' => $o['cliente_id'],
                'cliente' => $o['cliente'],
                'num_ofertas' => 0,
                'ganadoras' => 0,
                'no_ganadoras' => 0,
                'num_secciones' => 0,
                'total_ofertado' => 0.0,
                'total_facturado' => 0.0,
                'utilidad_a_ganar' => 0.0,
                'total_pagado' => 0.0,
                'utilidad_pagada' => 0.0,
                'factura_ids' => [],
            ];
            foreach ($o['factura_ids'] as $facturaId) {
                $clientes[$claveCliente]['factura_ids'][$facturaId] = true;
            }
            $clientes[$claveCliente]['num_ofertas']++;
            $clientes[$claveCliente][$o['tipo'] === self::TIPO_GANADORA ? 'ganadoras' : 'no_ganadoras']++;
            $clientes[$claveCliente]['num_secciones'] += $o['num_secciones'];
            $clientes[$claveCliente]['total_ofertado'] += $o['total_ofertado'];
            $clientes[$claveCliente]['total_facturado'] += $o['total_facturado'];
            $clientes[$claveCliente]['utilidad_a_ganar'] += $o['utilidad'];
            $clientes[$claveCliente]['total_pagado'] += $o['total_pagado'];
            $clientes[$claveCliente]['utilidad_pagada'] += $o['utilidad_pagada'];
        }

        $cerrar = function (array $g): array {
            $g['total_ofertado'] = round($g['total_ofertado'], 2);
            $g['total_facturado'] = round($g['total_facturado'], 2);
            foreach (['utilidad_a_ganar', 'total_pagado', 'utilidad_pagada'] as $campo) {
                if (isset($g[$campo])) {
                    $g[$campo] = round($g[$campo], 2);
                }
            }
            $g['facturabilidad_pct'] = $g['total_ofertado'] > 0
                ? round(($g['total_facturado'] / $g['total_ofertado']) * 100, 2)
                : 0;
            $g += $this->conteoEntregaFacturas(array_keys($g['factura_ids']));
            return $g;
        };

        $clientes = array_map(function ($c) use ($cerrar) {
            $c = $cerrar($c);
            unset($c['factura_ids']);
            return $c;
        }, array_values($clientes));
        usort($clientes, fn ($a, $b) => $b['num_ofertas'] <=> $a['num_ofertas']
            ?: $b['total_ofertado'] <=> $a['total_ofertado']
            ?: strcmp((string) $a['cliente'], (string) $b['cliente']));

        // KPIs de cada segmento, todos sin ISV. La utilidad a ganar es la de la
        // oferta (ofertado - costo ofertado); la utilidad ganada es la parte
        // cobrada de la utilidad real facturada.
        $cerrarGrupo = function (array $g) use ($cerrar): array {
            $g = $cerrar($g);
            $g['num_facturas'] = count($g['factura_ids']);
            unset($g['factura_ids']);
            $g['avance_pct'] = $g['facturabilidad_pct'];
            $g['utilidad_a_ganar'] = round($g['total_ofertado'] - $g['total_costo_oferta'], 2);
            unset($g['total_costo_oferta']);
            foreach (['utilidad_facturada', 'total_pagado', 'utilidad_pagada'] as $campo) {
                $g[$campo] = round($g[$campo], 2);
            }
            $g['pagado_pct'] = $g['total_facturado'] > 0 ? round(($g['total_pagado'] / $g['total_facturado']) * 100, 2) : 0;
            $g['utilidad_ganada_pct'] = $g['utilidad_a_ganar'] > 0 ? round(($g['utilidad_pagada'] / $g['utilidad_a_ganar']) * 100, 2) : 0;
            return $g;
        };

        return [
            'facturabilidad' => array_map($cerrarGrupo, $grupos),
            'clientes' => $clientes,
        ];
    }

    /**
     * Filtros de producto (marca, escala, producto) y de estado de entrega para
     * la subconsulta de facturado de datosOfertas. Con estado de entrega solo
     * se cuentan las facturas en ese estado.
     */
    private function filtrosProductoFacturado(Request $r): string
    {
        $sql = '';
        $estadoEntrega = strtoupper((string) $r->input('estado_entrega', ''));
        if (in_array($estadoEntrega, self::ESTADOS_ENTREGA, true)) {
            $sql .= ' AND f2.id IN (' . implode(',', $this->facturasPorEstadoEntrega($this->expoIdDesdeRequest($r), $estadoEntrega)) . ')';
        }
        $marcas = $this->idsDesdeRequest($r, 'marca_ids', 'marca_id');
        if ($marcas) {
            $sql .= ' AND p2.marca_id IN (' . implode(',', $marcas) . ')';
        }
        $escalas = $this->idsDesdeRequest($r, 'escala_ids', 'escala_id');
        if ($escalas) {
            $sql .= ' AND ppc_chp2.categoria_precios_id IN (' . implode(',', $escalas) . ')';
        }
        if ($r->producto_id !== null && $r->producto_id !== '') {
            $sql .= ' AND p2.id = ' . (int) $r->producto_id;
        }

        return $sql;
    }

    private function datosOfertas(Request $r): array
    {
        $expoId = $this->expoIdDesdeRequest($r);
        $extra = $this->filtrosExtra($r);
        $netoOfertaExpr = $this->netoOfertaExpr();
        $brutoOfertaExpr = $this->brutoOfertaExpr();
        // Porción cobrada de cada factura (abonos ÷ total con ISV). Se aplica a
        // los montos sin ISV de la línea para obtener lo pagado sin ISV.
        $ratioPagadoExpr = 'LEAST(COALESCE(ap2.credito_abonos, 0) * 1.0 / NULLIF(f2.total, 0), 1)';
        // Mismo costo unitario que la utilidad ofertada (precio base de la línea
        // de la oferta); costoproducto viene por empaque y no por unidad vendida.
        $costoBaseExpr = 'COALESCE(ppc_chp2.precio_base_venta, ppc_vhp2.precio_base_venta, 0)';

        $rows = DB::select("
            SELECT
                chp.cotizacion_id AS oferta_id,
                c.cliente_id,
                c.nombre_cliente,
                COALESCE(u.name, 'N/A') AS asesor,
                COALESCE(ut.name, 'Sin asignar') AS teleasesor,
                c.fecha_emision,
                COALESCE(ec.estado, 'SIN_REGISTRO') AS estado,
                COALESCE(ec.flujo_id, hf_oferta.flujo_id) AS flujo_id,
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
                COALESCE(fact.num_facturas, 0) AS num_facturas,
                fact.factura_ids,
                COALESCE(fact.total_pagado, 0) AS total_pagado,
                COALESCE(fact.utilidad_pagada, 0) AS utilidad_pagada
            {$this->joinsOfertado(true)}
            LEFT JOIN users u ON u.id = c.vendedor
            LEFT JOIN users ut ON ut.id = c.users_id
            LEFT JOIN expo_oferta_seccion eos ON eos.cotizacion_id = chp.cotizacion_id
            {$this->joinExpoCotizacion($expoId)}
            {$this->joinUltimoHistoricoOferta($expoId)}
            LEFT JOIN (
                SELECT chp2.cotizacion_id,
                       SUM(vhp2.sub_total_s) AS total_facturado,
                       SUM($costoBaseExpr * vhp2.cantidad_s) AS total_costo,
                       SUM(COALESCE(NULLIF(vhp2.cantidad_oferta_aplicada, 0), vhp2.cantidad_s)) AS cantidad_facturada,
                       COUNT(DISTINCT f2.id) AS num_facturas,
                       GROUP_CONCAT(DISTINCT f2.id) AS factura_ids,
                       SUM(vhp2.sub_total_s * $ratioPagadoExpr) AS total_pagado,
                       SUM((vhp2.sub_total_s - $costoBaseExpr * vhp2.cantidad_s) * $ratioPagadoExpr) AS utilidad_pagada
                FROM cotizacion_has_producto chp2
                INNER JOIN ({$this->lineasFacturadasSql($expoId)}) vhp2 ON vhp2.chp_id_resuelta = chp2.id
                      AND (vhp2.cotizacion_id_prefactura IS NULL OR vhp2.cotizacion_id_prefactura = chp2.cotizacion_id)
                INNER JOIN factura f2 ON f2.id = vhp2.factura_id AND f2.estado_venta_id = 1
                LEFT JOIN aplicacion_pagos ap2 ON ap2.factura_id = f2.id AND ap2.estado = 1
                INNER JOIN producto p2 ON p2.id = chp2.producto_id
                LEFT JOIN precios_producto_carga ppc_vhp2 ON ppc_vhp2.id = vhp2.precios_producto_carga_id
                LEFT JOIN precios_producto_carga ppc_chp2 ON ppc_chp2.id = chp2.precios_producto_carga_id
                WHERE chp2.cotizacion_id IN ({$this->inExpo($expoId)})
                {$this->filtrosProductoFacturado($r)}
                GROUP BY chp2.cotizacion_id
            ) fact ON fact.cotizacion_id = chp.cotizacion_id
            {$this->whereExpo($expoId)}
            $extra
            GROUP BY chp.cotizacion_id, c.id, c.cliente_id, c.nombre_cliente, u.name, ut.name, c.fecha_emision, ec.estado,
                     eos.cotizacion_origen_id, eos.numero, eos.nombre, eos.estado,
                     ec.flujo_id, hf_oferta.flujo_id, fact.total_facturado, fact.total_costo,
                     fact.cantidad_facturada, fact.num_facturas, fact.factura_ids,
                     fact.total_pagado, fact.utilidad_pagada
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
                'cliente_id' => $row->cliente_id ? (int) $row->cliente_id : null,
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
                'total_costo_facturado' => (float) $row->total_costo,
                'total_pagado' => (float) $row->total_pagado,
                'utilidad_pagada' => (float) $row->utilidad_pagada,
                'factura_ids' => $row->factura_ids ? array_map('intval', explode(',', $row->factura_ids)) : [],
            ];
        }, $rows);

        $estadosEntrega = $this->estadosEntregaFacturas(array_merge(...array_column($filas, 'factura_ids')));
        $this->estadosEntregaActuales = $estadosEntrega;
        foreach ($filas as &$fila) {
            $fila['estado_entrega'] = $this->estadoEntregaOferta($fila['factura_ids'], $estadosEntrega);
        }
        unset($fila);

        return $this->agruparOfertas($filas, $this->tiposOfertaExpo($expoId), $estadosEntrega);
    }

    public function tablaOfertas(Request $r)
    {
        return response()->json($this->datosOfertas($r));
    }

    public function resumenOfertas(Request $r)
    {
        $ofertas = $this->datosOfertas($r);

        return response()->json($this->resumenDesdeOfertas($ofertas) + ['ofertas' => $ofertas]);
    }

    public function exportarOfertas(Request $r)
    {
        $ofertas = $this->datosOfertas($r);
        $resumen = $this->resumenDesdeOfertas($ofertas);
        $info = $this->infoExcel($r);
        $f = $resumen['facturabilidad'];
        $otras = $f['no_ganadoras_con_secciones'];
        $notaSecciones = $otras['num_ofertas']
            ? 'No ganadoras incluye ' . $otras['num_ofertas'] . ' ofertas que dejaron de ser ganadoras pero conservan '
                . $otras['num_secciones'] . ' secciones (L ' . number_format($otras['total_facturado'], 2) . ' facturado de L '
                . number_format($otras['total_ofertado'], 2) . ' ofertado).'
            : null;
        $definiciones = [
            'Ganadoras: ofertas marcadas como ganadoras en su flujo, con sus secciones y facturas. No ganadoras: el resto; se omiten las alternativas sin secciones de un flujo que ya tiene ganadora.',
            'Montos sin ISV. Utilidad a ganar = ofertado − costo. Pagado = abonos aplicados a las facturas, prorrateados sin ISV. Utilidad ganada = parte cobrada de la utilidad facturada.',
            'Entrega (por factura, según su último envío activo): Distribuidas = facturas entregadas; Parciales = facturas a las que no se les distribuyeron todos los productos; Sin distribuir = facturas pendientes o sin envío. Las tres suman el total de facturas. % Entrega = distribuidas ÷ facturas. Estado de la oferta: Entregada = todas sus facturas distribuidas; Parcialmente distribuida = algunas; No entregada = ninguna.',
        ];

        $columnasMontos = [
            ['titulo' => 'Ofertado', 'clave' => 'ofertado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
            ['titulo' => 'Facturado', 'clave' => 'facturado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
            ['titulo' => '% Facturabilidad', 'formato' => 'pct', 'total' => ['ratio', 'facturado', 'ofertado'], 'ancho' => 13],
            ['titulo' => 'Utilidad a ganar', 'clave' => 'utilidad', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
        ];
        $columnasPagos = [
            ['titulo' => 'Pagado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 16],
            ['titulo' => 'Utilidad ganada', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 16],
        ];
        $columnasEntrega = [
            ['titulo' => 'Distribuidas', 'clave' => 'distribuidas', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 12],
            ['titulo' => 'Sin distribuir', 'clave' => 'sin_distribuir', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 12],
            ['titulo' => 'Parciales', 'clave' => 'parciales', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 12],
            ['titulo' => '% Entrega', 'formato' => 'pct', 'total' => ['ratio', 'distribuidas', ['distribuidas', 'sin_distribuir', 'parciales']], 'ancho' => 11],
        ];

        $clientes = new HojaTablaExpo('Clientes', 'Reporte Expo · Ofertas por cliente', $info, array_merge([
            ['titulo' => 'Cliente', 'ancho' => 40],
            ['titulo' => 'Total ofertas', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 11],
            ['titulo' => 'Ganadoras', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 11],
            ['titulo' => 'No ganadoras', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 11],
            ['titulo' => 'Secciones', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 11],
        ], $columnasMontos, $columnasPagos, $columnasEntrega), array_map(fn ($c) => [
            $c['cliente'] ?: 'Sin nombre', $c['num_ofertas'], $c['ganadoras'], $c['no_ganadoras'], $c['num_secciones'],
            $c['total_ofertado'], $c['total_facturado'], $c['facturabilidad_pct'], $c['utilidad_a_ganar'],
            $c['total_pagado'], $c['utilidad_pagada'],
            $c['num_entregadas'], $c['num_sin_entregar'], $c['num_parciales'], $c['entrega_pct'],
        ], $resumen['clientes']), array_merge(array_filter([$notaSecciones]), [$definiciones[2]]));

        $columnasMontos[2]['titulo'] = '% Avance';
        $hojaOfertas = new HojaTablaExpo('Ofertas', 'Reporte Expo · Ofertas', $info, array_merge([
            ['titulo' => 'Oferta #', 'formato' => 'id', 'ancho' => 10],
            ['titulo' => 'Tipo', 'ancho' => 22, 'colores' => EtiquetasExpo::COLORES_TIPO],
            ['titulo' => 'Cliente', 'ancho' => 34],
            ['titulo' => 'Flujo', 'formato' => 'id', 'ancho' => 9],
            ['titulo' => 'Secciones', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 10],
            ['titulo' => 'Asesor', 'ancho' => 22],
            ['titulo' => 'Teleasesor', 'ancho' => 22],
            ['titulo' => 'Fecha', 'formato' => 'fecha', 'ancho' => 11],
            ['titulo' => 'Facturación', 'ancho' => 20, 'colores' => EtiquetasExpo::COLORES_FACTURACION],
            ['titulo' => 'Facturas', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 10],
        ], $columnasMontos, [
            ['titulo' => 'Margen %', 'formato' => 'pct', 'total' => ['ratio', 'utilidad', 'ofertado'], 'ancho' => 11],
            ['titulo' => 'Descuento', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 15],
            ['titulo' => 'Utilidad facturada', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 16],
        ], $columnasPagos, $columnasEntrega, [
            ['titulo' => 'Entrega', 'ancho' => 22, 'colores' => EtiquetasExpo::COLORES_ENTREGA],
        ]), array_map(fn ($o) => [
            $o['oferta_id'], EtiquetasExpo::estado($o['tipo']), $o['cliente'], $o['flujo_id'], $o['num_secciones'],
            $o['asesor'], $o['teleasesor'], $o['fecha_emision'],
            EtiquetasExpo::estado($o['estado_facturacion']), $o['num_facturas'],
            $o['total_ofertado'], $o['total_facturado'], $o['avance_pct'], $o['utilidad'], $o['margen_pct'],
            $o['descuento'], $o['utilidad_facturada'], $o['total_pagado'], $o['utilidad_pagada'],
            $o['num_entregadas'], $o['num_sin_entregar'], $o['num_parciales'], $o['num_facturas'] ? $o['entrega_pct'] : null,
            EtiquetasExpo::estado($o['estado_entrega']),
        ], $ofertas), array_merge(array_filter([$notaSecciones]), [$definiciones[2]]));

        $filasSecciones = [];
        foreach ($ofertas as $o) {
            foreach ($o['secciones'] as $sec) {
                $nombre = !empty($sec['es_raiz']) || !empty($sec['es_pendiente'])
                    ? $sec['nombre_seccion']
                    : trim(($sec['numero_seccion'] ? 'Sección ' . $sec['numero_seccion'] . ' · ' : '') . ($sec['nombre_seccion'] ?? ''));
                $filasSecciones[] = [
                    $o['oferta_id'], EtiquetasExpo::estado($o['tipo']), $o['cliente'], $o['flujo_id'], $nombre,
                    empty($sec['es_raiz']) ? $sec['oferta_id'] : null, EtiquetasExpo::estado($sec['estado']),
                    EtiquetasExpo::estado($sec['estado_facturacion']), $sec['num_facturas'],
                    $sec['total_ofertado'], $sec['total_facturado'], $sec['total_pagado'], $sec['utilidad_pagada'],
                    EtiquetasExpo::estado($sec['estado_entrega'] ?? null),
                ];
            }
        }
        $secciones = new HojaTablaExpo('Secciones', 'Reporte Expo · Secciones por oferta', $info, [
            ['titulo' => 'Oferta #', 'formato' => 'id', 'ancho' => 10],
            ['titulo' => 'Tipo', 'ancho' => 22, 'colores' => EtiquetasExpo::COLORES_TIPO],
            ['titulo' => 'Cliente', 'ancho' => 34],
            ['titulo' => 'Flujo', 'formato' => 'id', 'ancho' => 9],
            ['titulo' => 'Sección', 'ancho' => 30],
            ['titulo' => 'Oferta de la sección', 'formato' => 'id', 'ancho' => 12],
            ['titulo' => 'Estado sección', 'ancho' => 20],
            ['titulo' => 'Facturación', 'ancho' => 20, 'colores' => EtiquetasExpo::COLORES_FACTURACION],
            ['titulo' => 'Facturas', 'formato' => 'entero', 'ancho' => 10],
            ['titulo' => 'Ofertado', 'formato' => 'moneda', 'ancho' => 17],
            ['titulo' => 'Facturado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 17],
            ['titulo' => 'Pagado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 16],
            ['titulo' => 'Utilidad ganada', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 16],
            ['titulo' => 'Entrega', 'ancho' => 22, 'colores' => EtiquetasExpo::COLORES_ENTREGA],
        ], $filasSecciones, [
            '"Productos sin sección" muestra el total ofertado de la oferta principal; las secciones son partes de ese monto, por eso el ofertado no se totaliza. "Productos sin factura" es lo que falta por facturar.',
        ]);

        return Excel::download(new LibroExpoExport([
            new ResumenExpoSheet($f, $info, array_merge(array_filter([$notaSecciones]), $definiciones)),
            $clientes,
            $hojaOfertas,
            $secciones,
        ]), $this->nombreExcel($r, 'ofertas'));
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
        $o = $data['oferta'];
        $usuario = optional(auth()->user())->name;
        $info = [
            'Expo: ' . ($o['expo'] ?: '#' . $o['expo_id']) . ($o['flujo_id'] ? ' · Flujo ' . $o['flujo_id'] : ''),
            'Generado el ' . now()->format('d/m/Y H:i') . ($usuario ? ' por ' . $usuario : '') . ' · Montos en lempiras',
        ];

        return Excel::download(
            new ReporteExpoOfertaExport($data, $info),
            'reporte_expo_oferta_' . $o['id'] . '_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

}
