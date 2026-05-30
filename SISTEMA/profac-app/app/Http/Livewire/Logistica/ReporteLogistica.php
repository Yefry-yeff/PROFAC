<?php

namespace App\Http\Livewire\Logistica;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReporteLogistica extends Component
{
    public function mount() {}

    public function render()
    {
        return view('livewire.logistica.reportelogistica');
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

        $rows = DB::table('distribuciones_entrega as d')
            ->join('equipos_entrega as e', 'e.id', '=', 'd.equipo_entrega_id')
            ->join('distribuciones_entrega_facturas as def', 'def.distribucion_entrega_id', '=', 'd.id')
            ->whereBetween('d.fecha_programada', [$fi, $ff])
            ->select(
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

        $rows = $query->select(
                'def.estado_entrega',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('def.estado_entrega')
            ->get();

        return response()->json($rows);
    }

    /* ─────────────────── TABLA POR FACTURA ────────────────────── */

    public function obtenerTablaFacturas(Request $request)
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

        $rows = $query->select(
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
        ->orderBy('f.cai')
        ->get();

        return response()->json($rows);
    }

    /* ─────────────────── TABLA DETALLE ────────────────────────── */

    public function obtenerTabla(Request $request)
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

        $rows = $query->select(
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
        ->orderByDesc('d.fecha_programada')
        ->get();

        return response()->json($rows);
    }
}
