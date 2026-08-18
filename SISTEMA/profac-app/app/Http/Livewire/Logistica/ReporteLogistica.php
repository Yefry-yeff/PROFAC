<?php

namespace App\Http\Livewire\Logistica;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteLogisticaPorEquipoExport;
use App\Exports\ReporteLogisticaPorDistribucionExport;
use App\Exports\ReporteLogisticaPorFacturaExport;

class ReporteLogistica extends Component
{
    // Fecha de corte usada por el módulo de Logística de Entregas para considerar
    // una factura "pendiente" (misma regla que AgrupacionesDeEntregas::FECHA_CORTE_FACTURAS,
    // usada en "Nueva Distribución" → Facturas por Zona / Sin clasificar).
    private const FECHA_CORTE_FACTURAS = '2026-05-16';

    public function mount() {}

    public function render()
    {
        return view('livewire.logistica.reportelogistica');
    }

    /**
     * Aplica los filtros de "clic en gráfico" (cf_estado/cf_equipo/cf_fecha) que
     * llegan desde el dashboard cuando el usuario hace clic en una serie/segmento
     * de un gráfico del Resumen, para acotar los demás KPIs/gráficos/tabla a esa
     * misma información. cf_estado='pendientes' agrupa sin_entrega+parcial (igual
     * que el resto del reporte); cualquier otro valor filtra por coincidencia exacta.
     */
    private function aplicarFiltroClick($query, Request $request, string $colEstado = 'def.estado_entrega', string $colEquipo = 'd.equipo_entrega_id', string $colFecha = 'd.fecha_programada')
    {
        $cfEstado = $request->cf_estado ?? null;
        $cfEquipo = $request->cf_equipo ?? null;
        $cfFecha  = $request->cf_fecha  ?? null;

        if ($cfEstado === 'pendientes') {
            $query->whereIn($colEstado, ['sin_entrega', 'parcial']);
        } elseif ($cfEstado) {
            $query->where($colEstado, $cfEstado);
        }

        if ($cfEquipo) $query->where($colEquipo, $cfEquipo);
        if ($cfFecha)  $query->whereDate($colFecha, $cfFecha);

        return $query;
    }

    /* ─────────────────── FILTROS / CATÁLOGOS ─────────────────── */

    public function obtenerFiltros()
    {
        $equipos = DB::table('equipos_entrega')
            ->where('estado_id', 1)
            ->orderBy('nombre_equipo')
            ->select('id', 'nombre_equipo')
            ->get();

        $usuarios = DB::table('users')
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return response()->json(['equipos' => $equipos, 'usuarios' => $usuarios]);
    }

    /* ─────────────────── KPIs ─────────────────────────────────── */

    public function obtenerKPIs(Request $request)
    {
        $fi     = $request->fi    ?? date('Y-m-01');
        $ff     = $request->ff    ?? date('Y-m-d');
        $equipo = $request->equipo ?? null;
        $estado = $request->estado ?? null;

        // Métricas de facturas
        $qFact = DB::table('distribuciones_entrega as d')
            ->join('distribuciones_entrega_facturas as def', 'def.distribucion_entrega_id', '=', 'd.id')
            ->whereBetween('d.fecha_programada', [$fi, $ff]);
        if ($equipo) $qFact->where('d.equipo_entrega_id', $equipo);
        if ($estado) $qFact->where('d.estado_id', $estado);

        $this->aplicarFiltroClick($qFact, $request);

        $facturas = $qFact->select(
            DB::raw('COUNT(def.id) as total'),
            DB::raw("SUM(CASE WHEN def.estado_entrega = 'entregado'              THEN 1 ELSE 0 END) as entregadas"),
            DB::raw("SUM(CASE WHEN def.estado_entrega IN ('sin_entrega','parcial') THEN 1 ELSE 0 END) as pendientes"),
            DB::raw("SUM(CASE WHEN def.estado_entrega = 'anulada'                THEN 1 ELSE 0 END) as anuladas")
        )->first();

        // Métricas de distribuciones
        $qDist = DB::table('distribuciones_entrega')
            ->whereBetween('fecha_programada', [$fi, $ff]);
        if ($equipo) $qDist->where('equipo_entrega_id', $equipo);
        if ($estado) $qDist->where('estado_id', $estado);

        $distStats = $qDist->select(
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN estado_id = 3 THEN 1 ELSE 0 END) as completadas')
        )->first();

        $total      = (int)($facturas->total      ?? 0);
        $entregadas = (int)($facturas->entregadas  ?? 0);
        $pendientes = (int)($facturas->pendientes  ?? 0);
        $anuladas   = (int)($facturas->anuladas    ?? 0);
        $base       = $total - $anuladas;
        $efectividad = $base > 0 ? round(($entregadas / $base) * 100, 1) : 0;

        return response()->json([
            'distribuciones' => (int)($distStats->total      ?? 0),
            'completadas'    => (int)($distStats->completadas ?? 0),
            'total_facturas' => $total,
            'entregadas'     => $entregadas,
            'pendientes'     => $pendientes,
            'anuladas'       => $anuladas,
            'efectividad'    => $efectividad,
        ]);
    }

    /* ─────────────────── EVOLUCIÓN ────────────────────────────── */

    public function obtenerEvolucion(Request $request)
    {
        $fi     = $request->fi    ?? date('Y-m-01');
        $ff     = $request->ff    ?? date('Y-m-d');
        $equipo = $request->equipo ?? null;

        $query = DB::table('distribuciones_entrega as d')
            ->join('distribuciones_entrega_facturas as def', 'def.distribucion_entrega_id', '=', 'd.id')
            ->whereBetween('d.fecha_programada', [$fi, $ff]);
        if ($equipo) $query->where('d.equipo_entrega_id', $equipo);
        $this->aplicarFiltroClick($query, $request);

        $rows = $query->select(
            DB::raw('DATE(d.fecha_programada) as fecha'),
            DB::raw("SUM(CASE WHEN def.estado_entrega = 'entregado'              THEN 1 ELSE 0 END) as entregadas"),
            DB::raw("SUM(CASE WHEN def.estado_entrega IN ('sin_entrega','parcial') THEN 1 ELSE 0 END) as pendientes"),
            DB::raw("SUM(CASE WHEN def.estado_entrega = 'anulada'                THEN 1 ELSE 0 END) as anuladas")
        )
        ->groupBy(DB::raw('DATE(d.fecha_programada)'))
        ->orderBy('fecha')
        ->get();

        return response()->json($rows);
    }

    /* ─────────────────── POR EQUIPO ───────────────────────────── */

    public function obtenerPorEquipo(Request $request)
    {
        $fi = $request->fi ?? date('Y-m-01');
        $ff = $request->ff ?? date('Y-m-d');

        $query = DB::table('distribuciones_entrega as d')
            ->join('equipos_entrega as e', 'e.id', '=', 'd.equipo_entrega_id')
            ->join('distribuciones_entrega_facturas as def', 'def.distribucion_entrega_id', '=', 'd.id')
            ->whereBetween('d.fecha_programada', [$fi, $ff]);
        $this->aplicarFiltroClick($query, $request);

        $rows = $query->select(
                'e.id as equipo_id',
                'e.nombre_equipo as equipo',
                DB::raw("SUM(CASE WHEN def.estado_entrega = 'entregado'              THEN 1 ELSE 0 END) as entregadas"),
                DB::raw("SUM(CASE WHEN def.estado_entrega IN ('sin_entrega','parcial') THEN 1 ELSE 0 END) as pendientes"),
                DB::raw("SUM(CASE WHEN def.estado_entrega = 'anulada'                THEN 1 ELSE 0 END) as anuladas"),
                DB::raw('COUNT(def.id) as total')
            )
            ->groupBy('e.id', 'e.nombre_equipo')
            ->orderByDesc('entregadas')
            ->get();

        return response()->json($rows);
    }

    /* ─────────────────── ESTADOS (DONUT) ──────────────────────── */

    public function obtenerEstados(Request $request)
    {
        $fi     = $request->fi    ?? date('Y-m-01');
        $ff     = $request->ff    ?? date('Y-m-d');
        $equipo = $request->equipo ?? null;

        $query = DB::table('distribuciones_entrega as d')
            ->join('distribuciones_entrega_facturas as def', 'def.distribucion_entrega_id', '=', 'd.id')
            ->whereBetween('d.fecha_programada', [$fi, $ff]);
        if ($equipo) $query->where('d.equipo_entrega_id', $equipo);
        $this->aplicarFiltroClick($query, $request);

        $rows = $query->select(
                'def.estado_entrega',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('def.estado_entrega')
            ->get();

        return response()->json($rows);
    }

    /**
     * Condición base ("factura sin asignar a ninguna distribución activa") —
     * misma regla que AgrupacionesDeEntregas::condicionFacturaPendiente(), pero
     * acotada al rango fi/ff del reporte (por fecha de EMISIÓN de la factura,
     * ya que estas facturas no tienen fecha_programada de ninguna distribución).
     */
    private function queryBaseFacturasSinAsignar(string $fi, string $ff)
    {
        return DB::table('factura as f')
            ->whereIn('f.estado_factura_id', [1, 2])
            ->where('f.estado_venta_id', 1)
            ->whereBetween('f.fecha_emision', [$fi, $ff])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('distribuciones_entrega_facturas as def')
                    ->whereColumn('def.factura_id', 'f.id')
                    ->where('def.estado_entrega', 'entregado');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('distribuciones_entrega_facturas as def')
                    ->join('distribuciones_entrega as de', 'de.id', '=', 'def.distribucion_entrega_id')
                    ->whereColumn('def.factura_id', 'f.id')
                    ->whereIn('de.estado_id', [1, 2])
                    ->where('def.estado_entrega', '!=', 'anulada');
            });
    }

    /**
     * Construye (sin ejecutar) la consulta de detalle de facturas SIN ASIGNAR
     * (para la tabla/export de la pestaña Resumen), acotada a fi/ff. Respeta
     * los filtros de clic en gráfico: como estas facturas no tienen equipo ni
     * un estado_entrega real, si el clic filtra por un estado distinto de
     * "pendientes" o por un equipo puntual, no aplica (no arroja filas).
     */
    private function construirQueryFacturasSinAsignar(Request $request)
    {
        $fi = $request->fi ?? date('Y-m-01');
        $ff = $request->ff ?? date('Y-m-d');

        $cfEstado = $request->cf_estado ?? null;
        $cfEquipo = $request->cf_equipo ?? null;
        $cfFecha  = $request->cf_fecha  ?? null;

        $query = $this->queryBaseFacturasSinAsignar($fi, $ff)
            ->join('cliente as c', 'c.id', '=', 'f.cliente_id');

        if (($cfEstado && $cfEstado !== 'pendientes') || $cfEquipo) {
            $query->whereRaw('1 = 0');
        }
        if ($cfFecha) {
            $query->whereDate('f.fecha_emision', $cfFecha);
        }

        return $query->select(
            'f.id as factura_id',
            'f.cai as numero_factura',
            'c.nombre as cliente',
            DB::raw('FORMAT(f.total, 2) as total'),
            DB::raw('DATE_FORMAT(f.fecha_emision, "%d/%m/%Y") as fecha_emision')
        )->orderByDesc('f.fecha_emision');
    }

    public function obtenerFacturasSinAsignar(Request $request)
    {
        return response()->json($this->construirQueryFacturasSinAsignar($request)->get());
    }

    /**
     * Total de facturas "generadas" (universo de facturas válidas emitidas)
     * dentro del rango fi/ff — misma regla base de validez que se usa para
     * "Sin clasificar"/"Pendientes" (estado_factura_id IN (1,2), estado_venta_id=1),
     * pero SIN las condiciones NOT EXISTS (o sea, incluye asignadas y sin asignar,
     * entregadas, pendientes o anuladas por entrega). Es el denominador de la
     * "Efectividad" del período.
     */
    private function contarFacturasGeneradas(string $fi, string $ff): int
    {
        return DB::table('factura as f')
            ->whereIn('f.estado_factura_id', [1, 2])
            ->where('f.estado_venta_id', 1)
            ->whereBetween('f.fecha_emision', [$fi, $ff])
            ->count();
    }

    /**
     * KPI adicional: total de facturas realmente pendientes dentro del rango
     * fi/ff del reporte — combina (a) facturas YA asignadas a una distribución
     * activa que aún no se han entregado, con (b) facturas que NO han sido
     * agregadas a ninguna distribución (misma regla que "Sin clasificar" en
     * Nueva Distribución), ambas por fecha de emisión de la factura. También
     * incluye el total de facturas generadas del período y la "Efectividad"
     * real: % de las facturas generadas que YA NO están pendientes.
     */
    public function obtenerPendientesReales(Request $request)
    {
        $fi = $request->fi ?? date('Y-m-01');
        $ff = $request->ff ?? date('Y-m-d');

        $asignadasPendientes = DB::table('distribuciones_entrega_facturas as def')
            ->join('distribuciones_entrega as d', 'd.id', '=', 'def.distribucion_entrega_id')
            ->join('factura as f', 'f.id', '=', 'def.factura_id')
            ->whereIn('def.estado_entrega', ['sin_entrega', 'parcial'])
            ->whereIn('d.estado_id', [1, 2])
            ->whereBetween('f.fecha_emision', [$fi, $ff])
            ->count();

        $sinAsignar      = $this->queryBaseFacturasSinAsignar($fi, $ff)->count();
        $totalGeneradas  = $this->contarFacturasGeneradas($fi, $ff);
        $totalPendientes = $asignadasPendientes + $sinAsignar;
        $efectividad     = $totalGeneradas > 0
            ? round((($totalGeneradas - $totalPendientes) / $totalGeneradas) * 100, 1)
            : 0;

        return response()->json([
            'asignadas_pendientes' => $asignadasPendientes,
            'sin_asignar'          => $sinAsignar,
            'total'                => $totalPendientes,
            'total_generadas'      => $totalGeneradas,
            'efectividad'          => $efectividad,
            'fi'                   => $fi,
            'ff'                   => $ff,
        ]);
    }

    /* ─────────────────── TABLA POR FACTURA ────────────────────── */

    /**
     * Construye (sin ejecutar) la consulta de facturas de la pestaña "Por
     * Factura", reutilizada tanto por el JSON de la tabla como por el export
     * a Excel (y por el detalle de la pestaña Resumen, que pasa cf_* en vez
     * de estado).
     */
    private function construirQueryFacturas(Request $request)
    {
        $fi     = $request->fi     ?? date('Y-m-01');
        $ff     = $request->ff     ?? date('Y-m-d');
        $equipo = $request->equipo ?? null;
        $estado = $request->estado ?? null;   // estado_entrega de la factura

        $query = DB::table('distribuciones_entrega as d')
            ->join('equipos_entrega as e',                      'e.id',   '=', 'd.equipo_entrega_id')
            ->join('distribuciones_entrega_facturas as def',    'def.distribucion_entrega_id', '=', 'd.id')
            ->join('factura as f',                              'f.id',   '=', 'def.factura_id')
            ->join('cliente as c',                              'c.id',   '=', 'f.cliente_id')
            ->whereBetween('d.fecha_programada', [$fi, $ff]);

        if ($equipo) $query->where('d.equipo_entrega_id', $equipo);
        if ($estado) $query->where('def.estado_entrega', $estado);
        $this->aplicarFiltroClick($query, $request);

        return $query->select(
            'def.id as def_id',
            'd.id as distribucion_id',
            DB::raw('DATE_FORMAT(d.fecha_programada, "%d/%m/%Y") as fecha_programada'),
            DB::raw('IF(d.hora_salida IS NOT NULL,
                DATE_FORMAT(d.hora_salida, "%d/%m/%Y %H:%i"),
                NULL) as hora_salida'),
            'f.cai as numero_factura',
            'c.nombre as cliente',
            DB::raw('FORMAT(f.total, 2) as total'),
            'e.nombre_equipo as equipo',
            'def.estado_entrega',
            DB::raw('IF(def.fecha_entrega_real IS NOT NULL,
                DATE_FORMAT(def.fecha_entrega_real, "%d/%m/%Y %H:%i"),
                NULL) as fecha_entrega_real'),
            'def.motivo_anulacion',
            'def.motivo_confirmacion'
        )
        ->orderByDesc('d.fecha_programada')
        ->orderBy('f.cai');
    }

    public function obtenerTablaFacturas(Request $request)
    {
        return response()->json($this->construirQueryFacturas($request)->get());
    }

    /**
     * Export a Excel de la pestaña "Por Factura" (una fila por factura).
     * Reutiliza construirQueryFacturas(), por lo que también sirve para
     * exportar el detalle filtrado de la pestaña Resumen (clic en gráfico).
     */
    public function exportarExcelFacturas(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $fi = $request->fi ?? date('Y-m-01');
        $ff = $request->ff ?? date('Y-m-d');

        $rows = $this->construirQueryFacturas($request)->get();

        $estadoLabels = [
            'entregado'   => 'Entregado',
            'parcial'     => 'Parcial',
            'sin_entrega' => 'Sin Entregar',
            'anulada'     => 'Anulada',
        ];

        $filas = $rows->map(function ($r) use ($estadoLabels) {
            return [
                'distribucion_id'     => $r->distribucion_id,
                'fecha_programada'    => $r->fecha_programada,
                'hora_salida'         => $r->hora_salida ?? '',
                'numero_factura'      => $r->numero_factura,
                'cliente'             => $r->cliente,
                'total'               => $r->total,
                'equipo'              => $r->equipo,
                'estado'              => $estadoLabels[$r->estado_entrega] ?? $r->estado_entrega,
                'fecha_entrega_real'  => $r->fecha_entrega_real ?? '',
                'motivo_anulacion'    => $r->motivo_anulacion ?? '',
                'motivo_confirmacion' => $r->motivo_confirmacion ?? '',
            ];
        })->toArray();

        return Excel::download(
            new ReporteLogisticaPorFacturaExport($filas, $fi, $ff),
            "ReporteLogistico_PorFactura_{$fi}_a_{$ff}.xlsx"
        );
    }

    /**
     * Export a Excel del "Detalle de Facturas" de la pestaña Resumen: combina
     * las facturas asignadas a distribuciones (construirQueryFacturas) con las
     * facturas SIN ASIGNAR (construirQueryFacturasSinAsignar), ambas acotadas
     * al mismo rango fi/ff y a los mismos filtros de clic en gráfico.
     */
    public function exportarExcelResumenDetalle(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $fi = $request->fi ?? date('Y-m-01');
        $ff = $request->ff ?? date('Y-m-d');

        $estadoLabels = [
            'entregado'   => 'Entregado',
            'parcial'     => 'Parcial',
            'sin_entrega' => 'Sin Entregar',
            'anulada'     => 'Anulada',
        ];

        $asignadas = $this->construirQueryFacturas($request)->get()->map(function ($r) use ($estadoLabels) {
            return [
                'distribucion_id'     => $r->distribucion_id,
                'fecha_programada'    => $r->fecha_programada,
                'hora_salida'         => $r->hora_salida ?? '',
                'numero_factura'      => $r->numero_factura,
                'cliente'             => $r->cliente,
                'total'               => $r->total,
                'equipo'              => $r->equipo,
                'estado'              => $estadoLabels[$r->estado_entrega] ?? $r->estado_entrega,
                'fecha_entrega_real'  => $r->fecha_entrega_real ?? '',
                'motivo_anulacion'    => $r->motivo_anulacion ?? '',
                'motivo_confirmacion' => $r->motivo_confirmacion ?? '',
            ];
        });

        $sinAsignar = $this->construirQueryFacturasSinAsignar($request)->get()->map(function ($r) {
            return [
                'distribucion_id'     => '',
                'fecha_programada'    => $r->fecha_emision,
                'hora_salida'         => '',
                'numero_factura'      => $r->numero_factura,
                'cliente'             => $r->cliente,
                'total'               => $r->total,
                'equipo'              => 'Sin asignar',
                'estado'              => 'Sin Asignar',
                'fecha_entrega_real'  => '',
                'motivo_anulacion'    => '',
                'motivo_confirmacion' => '',
            ];
        });

        $filas = $asignadas->concat($sinAsignar)->values()->toArray();

        return Excel::download(
            new ReporteLogisticaPorFacturaExport($filas, $fi, $ff),
            "ReporteLogistico_Resumen_Detalle_{$fi}_a_{$ff}.xlsx"
        );
    }

    /* ─────────────────── TABLA DETALLE ────────────────────────── */

    /**
     * Construye (sin ejecutar) la consulta de distribuciones de la pestaña
     * "Por Distribución", reutilizada tanto por el JSON de la tabla como por
     * el export a Excel.
     */
    private function construirQueryDistribuciones(Request $request)
    {
        $fi     = $request->fi    ?? date('Y-m-01');
        $ff     = $request->ff    ?? date('Y-m-d');
        $equipo = $request->equipo ?? null;
        $estado = $request->estado ?? null;

        $query = DB::table('distribuciones_entrega as d')
            ->join('equipos_entrega as e', 'e.id', '=', 'd.equipo_entrega_id')
            ->join('users as u', 'u.id', '=', 'd.users_id_creador')
            ->leftJoin('distribuciones_entrega_facturas as def', 'def.distribucion_entrega_id', '=', 'd.id')
            ->whereBetween('d.fecha_programada', [$fi, $ff]);
        if ($equipo) $query->where('d.equipo_entrega_id', $equipo);
        if ($estado) $query->where('d.estado_id', $estado);

        return $query->select(
            'd.id',
            DB::raw('DATE_FORMAT(d.fecha_programada, "%d/%m/%Y") as fecha'),
            'e.nombre_equipo',
            'u.name as creador',
            DB::raw('COUNT(def.id) as total_facturas'),
            DB::raw("SUM(CASE WHEN def.estado_entrega = 'entregado'              THEN 1 ELSE 0 END) as entregadas"),
            DB::raw("SUM(CASE WHEN def.estado_entrega IN ('sin_entrega','parcial') THEN 1 ELSE 0 END) as pendientes"),
            DB::raw("SUM(CASE WHEN def.estado_entrega = 'anulada'                THEN 1 ELSE 0 END) as anuladas"),
            DB::raw("CASE d.estado_id
                WHEN 1 THEN 'Pendiente'
                WHEN 2 THEN 'En Proceso'
                WHEN 3 THEN 'Completada'
                WHEN 4 THEN 'Cancelada'
                ELSE 'Desconocido' END as estado_label"),
            'd.estado_id'
        )
        ->groupBy('d.id', 'd.fecha_programada', 'e.nombre_equipo', 'u.name', 'd.estado_id')
        ->orderByDesc('d.fecha_programada');
    }

    public function obtenerTabla(Request $request)
    {
        return response()->json($this->construirQueryDistribuciones($request)->get());
    }

    /**
     * Export a Excel de la pestaña "Por Distribución" (una fila por
     * distribución, con la efectividad calculada igual que en el frontend).
     */
    public function exportarExcelDistribucion(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $fi = $request->fi ?? date('Y-m-01');
        $ff = $request->ff ?? date('Y-m-d');

        $rows = $this->construirQueryDistribuciones($request)->get();

        $filas = $rows->map(function ($r) {
            $base        = $r->total_facturas - $r->anuladas;
            $efectividad = $base > 0 ? round(($r->entregadas / $base) * 100) : 0;

            return [
                'id'             => $r->id,
                'fecha'          => $r->fecha,
                'equipo'         => $r->nombre_equipo,
                'creador'        => $r->creador,
                'total_facturas' => $r->total_facturas,
                'entregadas'     => $r->entregadas,
                'pendientes'     => $r->pendientes,
                'anuladas'       => $r->anuladas,
                'efectividad'    => $efectividad . '%',
                'estado'         => $r->estado_label,
            ];
        })->toArray();

        return Excel::download(
            new ReporteLogisticaPorDistribucionExport($filas, $fi, $ff),
            "ReporteLogistico_PorDistribucion_{$fi}_a_{$ff}.xlsx"
        );
    }

    /* ─────────────────── TABLA POR EQUIPO ─────────────────────── */

    /**
     * Resumen "por equipo", agrupado por equipo + fecha + composición exacta
     * de miembros (no solo por equipo). Un mismo equipo puede tener varias
     * salidas el mismo día con distintos miembros (p.ej. una a las 9am con 2
     * miembros y otra a las 12pm con un miembro adicional) — eso genera
     * registros SEPARADOS. Pero si dos salidas del mismo día tienen
     * exactamente los mismos miembros, se consolidan en un solo registro.
     */
    public function obtenerTablaEquipos(Request $request)
    {
        $fi     = $request->fi     ?? date('Y-m-01');
        $ff     = $request->ff     ?? date('Y-m-d');
        $equipo = $request->equipo ?? null;

        return response()->json($this->construirGruposEquipos($fi, $ff, $equipo));
    }

    /**
     * Construye los grupos equipo+fecha+firma de miembros (misma lógica usada por
     * obtenerTablaEquipos y por el export detallado en Excel) con sus horas de
     * salida/última entrega/llegada agregadas y solo grupos con facturas entregadas.
     */
    private function construirGruposEquipos($fi, $ff, $equipo = null)
    {
        $distQuery = DB::table('distribuciones_entrega as d')
            ->join('equipos_entrega as e', 'e.id', '=', 'd.equipo_entrega_id')
            ->where('e.estado_id', 1)
            ->whereBetween('d.fecha_programada', [$fi, $ff]);

        if ($equipo) $distQuery->where('e.id', $equipo);

        $distribuciones = $distQuery->select(
            'd.id as distribucion_id',
            'e.id as equipo_id',
            'e.nombre_equipo as equipo',
            'd.fecha_programada as fecha',
            'd.hora_salida as hora_salida',
            'd.hora_llegada as hora_llegada'
        )->get();

        if ($distribuciones->isEmpty()) {
            return collect();
        }

        $distIds = $distribuciones->pluck('distribucion_id');

        // Facturas entregadas por distribución (salida)
        $entregadasPorDist = DB::table('distribuciones_entrega_facturas')
            ->whereIn('distribucion_entrega_id', $distIds)
            ->where('estado_entrega', 'entregado')
            ->select('distribucion_entrega_id', DB::raw('COUNT(*) as total'))
            ->groupBy('distribucion_entrega_id')
            ->pluck('total', 'distribucion_entrega_id');

        // Hora de la última factura entregada en cada distribución ("Hora Última Entrega").
        $ultimaEntregaPorDist = DB::table('distribuciones_entrega_facturas')
            ->whereIn('distribucion_entrega_id', $distIds)
            ->where('estado_entrega', 'entregado')
            ->whereNotNull('fecha_entrega_real')
            ->select('distribucion_entrega_id', DB::raw('MAX(fecha_entrega_real) as ultima_entrega'))
            ->groupBy('distribucion_entrega_id')
            ->pluck('ultima_entrega', 'distribucion_entrega_id');

        // Miembros (snapshot) por distribución
        $miembrosPorDist = DB::table('distribuciones_entrega_miembros as dem')
            ->join('users as u', 'u.id', '=', 'dem.user_id')
            ->whereIn('dem.distribucion_entrega_id', $distIds)
            ->orderBy('u.name')
            ->select('dem.distribucion_entrega_id', 'dem.user_id', 'u.name', 'dem.porcentaje_comision')
            ->get()
            ->groupBy('distribucion_entrega_id');

        // Agrupar por equipo + fecha + firma de miembros (ids ordenados)
        $grupos = [];
        foreach ($distribuciones as $d) {
            $miembros = $miembrosPorDist->get($d->distribucion_id, collect());
            $firma    = $miembros->pluck('user_id')->sort()->values()->implode(',');
            $key      = $d->equipo_id . '|' . $d->fecha . '|' . $firma;

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'equipo_id'            => $d->equipo_id,
                    'equipo'               => $d->equipo,
                    'fecha'                => $d->fecha,
                    'fecha_fmt'            => date('d/m/Y', strtotime($d->fecha)),
                    'dist_ids'             => [],
                    'miembros'             => $miembros->map(function ($m) {
                        return [
                            'name' => $m->name,
                            'porcentaje_comision' => (float) $m->porcentaje_comision,
                        ];
                    })->values(),
                    'facturas_entregadas'  => 0,
                    'horas_salida'         => [],
                    'horas_ultima_entrega' => [],
                    'horas_llegada'        => [],
                ];
            }

            $grupos[$key]['dist_ids'][]           = $d->distribucion_id;
            $grupos[$key]['facturas_entregadas'] += (int) ($entregadasPorDist[$d->distribucion_id] ?? 0);

            if (!empty($d->hora_salida)) {
                $grupos[$key]['horas_salida'][] = $d->hora_salida;
            }

            if (!empty($d->hora_llegada)) {
                $grupos[$key]['horas_llegada'][] = $d->hora_llegada;
            }

            $ultimaEntrega = $ultimaEntregaPorDist[$d->distribucion_id] ?? null;
            if ($ultimaEntrega) {
                $grupos[$key]['horas_ultima_entrega'][] = $ultimaEntrega;
            }
        }

        return collect($grupos)->map(function ($g) {
            $g['dist_ids'] = implode(',', $g['dist_ids']);

            sort($g['horas_salida']);
            $primeraSalida    = $g['horas_salida'][0] ?? null;
            $g['hora_salida'] = $primeraSalida ? substr($primeraSalida, 0, 5) : null;
            unset($g['horas_salida']);

            sort($g['horas_ultima_entrega']);
            $ultimaEntrega          = end($g['horas_ultima_entrega']) ?: null;
            $g['hora_ultima_entrega'] = $ultimaEntrega ? date('H:i', strtotime($ultimaEntrega)) : null;
            unset($g['horas_ultima_entrega']);

            sort($g['horas_llegada']);
            $ultimaLlegada     = end($g['horas_llegada']) ?: null;
            $g['hora_llegada'] = $ultimaLlegada ? substr($ultimaLlegada, 0, 5) : null;
            unset($g['horas_llegada']);

            return $g;
        })
        ->filter(fn ($g) => $g['facturas_entregadas'] > 0)
        ->sortByDesc('fecha')
        ->values();
    }

    /**
     * Detalle de facturas entregadas de una o varias distribuciones (salidas)
     * ya agrupadas por equipo+fecha+miembros: número de factura, cliente,
     * dirección de entrega y si tuvo algún hallazgo (incidencia registrada en
     * algún producto de la entrega).
     */
    public function obtenerDetalleEquipo(Request $request)
    {
        $distIds = collect(explode(',', (string) $request->dist_ids))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values();

        if ($distIds->isEmpty()) {
            return response()->json(['success' => false, 'mensaje' => 'Debe indicar la(s) distribución(es)'], 422);
        }

        return response()->json($this->obtenerFacturasDeDistribuciones($distIds));
    }

    /**
     * Facturas entregadas de una o varias distribuciones (salidas), ordenadas
     * por hora de entrega ascendente. Usado tanto por el modal de detalle como
     * por el export detallado en Excel.
     */
    private function obtenerFacturasDeDistribuciones($distIds)
    {
        return DB::table('distribuciones_entrega_facturas as def')
            ->join('factura as f', 'f.id', '=', 'def.factura_id')
            ->join('cliente as c', 'c.id', '=', 'f.cliente_id')
            ->leftJoin('factura_tratamiento_entrega as fte', 'fte.factura_id', '=', 'f.id')
            ->whereIn('def.distribucion_entrega_id', $distIds)
            ->where('def.estado_entrega', 'entregado')
            ->select(
                'f.cai as numero_factura',
                'c.nombre as cliente',
                DB::raw("COALESCE(fte.direccion_entrega, '') as direccion_entrega"),
                DB::raw('IF(def.fecha_entrega_real IS NOT NULL,
                    DATE_FORMAT(def.fecha_entrega_real, "%d/%m/%Y %H:%i"),
                    NULL) as fecha_entrega_real'),
                DB::raw('IF(def.fecha_entrega_real IS NOT NULL,
                    DATE_FORMAT(def.fecha_entrega_real, "%H:%i"),
                    NULL) as hora_entrega'),
                DB::raw("EXISTS (
                    SELECT 1 FROM entregas_productos ep
                    WHERE ep.distribucion_factura_id = def.id AND ep.tiene_incidencia = 1
                ) as tiene_hallazgo")
            )
            ->orderByRaw('def.fecha_entrega_real IS NULL, def.fecha_entrega_real ASC')
            ->get();
    }

    /**
     * Export detallado a Excel de la pestaña "Por Equipo": una fila por FACTURA
     * entregada, repitiendo los datos de la entrega (equipo, fecha, horas,
     * miembros) por cada una de las N facturas de ese grupo.
     */
    public function exportarExcelEquipos(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $fi     = $request->fi     ?? date('Y-m-01');
        $ff     = $request->ff     ?? date('Y-m-d');
        $equipo = $request->equipo ?? null;

        $grupos = $this->construirGruposEquipos($fi, $ff, $equipo);

        $filas = [];

        foreach ($grupos as $g) {
            $miembrosTxt = collect($g['miembros'])
                ->map(fn ($m) => $m['name'] . ' (' . number_format($m['porcentaje_comision'], 1) . '%)')
                ->implode(' | ');

            $distIds  = collect(explode(',', $g['dist_ids']))->filter()->values();
            $facturas = $this->obtenerFacturasDeDistribuciones($distIds);

            foreach ($facturas as $f) {
                $filas[] = [
                    'equipo'              => $g['equipo'],
                    'fecha'               => $g['fecha_fmt'],
                    'hora_salida'         => $g['hora_salida'] ?? '',
                    'hora_ultima_entrega' => $g['hora_ultima_entrega'] ?? '',
                    'hora_llegada'        => $g['hora_llegada'] ?? '',
                    'miembros'            => $miembrosTxt,
                    'numero_factura'      => $f->numero_factura,
                    'cliente'             => $f->cliente,
                    'direccion_entrega'   => $f->direccion_entrega,
                    'hora_entrega'        => $f->hora_entrega ?? '',
                    'hallazgo'            => $f->tiene_hallazgo ? 'Sí' : 'No',
                ];
            }
        }

        return Excel::download(
            new ReporteLogisticaPorEquipoExport($filas, $fi, $ff),
            "ReporteLogistico_PorEquipo_{$fi}_a_{$ff}.xlsx"
        );
    }
}
