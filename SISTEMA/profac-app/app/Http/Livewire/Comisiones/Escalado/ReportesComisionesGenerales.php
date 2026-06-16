<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportesComisionesGenerales extends Component
{
    private function normalizeDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y'] as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $value);
                if ($dt !== false) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // Intentar siguiente formato
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveDateRange(Request $request): array
    {
        $fi = $this->normalizeDate($request->input('fechaInicio'));
        $ff = $this->normalizeDate($request->input('fechaFin'));

        if (!$fi || !$ff) {
            $today = Carbon::today()->format('Y-m-d');
            $fi = $fi ?: $today;
            $ff = $ff ?: $today;
        }

        return [$fi, $ff];
    }

    public function render()
    {
        return view('livewire.comisiones.escalado.reportes-comisiones-generales');
    }

    /**
     * Lista de empleados para selector
     */
    public function listarEmpleados(Request $request)
    {
        $search = $request->input('q', '');

        $empleados = DB::table('users')
            ->join('estado', 'users.estado_id', '=', 'estado.id')
            ->select('users.id', 'users.name')
            ->where('estado.id', 1)
            ->where('name', 'LIKE', "%{$search}%")
            ->orderBy('users.name')
            ->limit(20)
            ->get();

        return response()->json($empleados);
    }

    /**
     * Lista de roles para selector
     */
    public function listarRoles(Request $request)
    {
        $search = $request->input('q', '');

        $roles = DB::table('rol')
            ->select('id', 'nombre as name')
            ->where('nombre', 'LIKE', "%{$search}%")
            ->where('estado_id', 1)
            ->limit(20)
            ->get();

        return response()->json($roles);
    }

    /**
     * Reporte de comisiones por empleado
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteEmpleado(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $fechaInicio = $fi . ' 00:00:00';
        $fechaFin = $ff . ' 23:59:59';
        $empleadoId = $request->input('filtroEspecifico');
        $rolId = (int) $request->input('rol_id', 0);

        // Empleado es OBLIGATORIO
        if (!$empleadoId) {
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Debe seleccionar un empleado'
            ]);
        }

        $query = DB::table('producto_comision as pc')
            ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->join('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->join('producto as p', 'p.id', '=', 'pc.producto_id')
            ->leftJoin('rol as r', 'r.id', '=', 'fc.rol_id')
            ->join('users as u', function ($join) use ($empleadoId) {
                $join->where('u.id', '=', $empleadoId);
            })
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->where('pc.monto_comision', '>', 0)
            ->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END = ?",
                [$empleadoId]
            )
            ->select(
                'pc.id',
                'pc.id as registro_id',
                'u.id as empleado_id',
                'u.name as empleado',
                'f.cai as factura',
                'cl.nombre as cliente',
                'p.nombre as producto',
                'r.nombre as rol_comisionado',
                'pc.cantidad',
                'pc.monto_comision',
                DB::raw('DATE_FORMAT(fc.fecha_cierre_factura, "%Y-%m-%d") as fecha')
            )
            ->orderByDesc('fc.fecha_cierre_factura')
            ->orderBy('f.cai')
            ->orderBy('p.nombre');

        if ($rolId > 0) {
            $query->where('fc.rol_id', $rolId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte de comisiones por rol
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteRol(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $fechaInicio = $fi . ' 00:00:00';
        $fechaFin = $ff . ' 23:59:59';
        $rolId = $request->input('filtroEspecifico');
        $empleadoId = (int) $request->input('empleado_id', 0);

        $subquery = DB::table('facturas_comision as fc')
            ->join('rol as r', 'r.id', '=', 'fc.rol_id')
            ->joinSub(
                DB::table('comision_empleado as ce2')
                    ->select('ce2.*')
                    ->whereRaw('ce2.id = (
                        SELECT MAX(ce3.id)
                        FROM comision_empleado ce3
                        WHERE ce3.rol_id = ce2.rol_id
                          AND ce3.estado_id = 1
                          AND YEAR(ce3.mes_comision) = YEAR(ce2.mes_comision)
                          AND MONTH(ce3.mes_comision) = MONTH(ce2.mes_comision)
                    )'),
                'ce',
                function($join) {
                    $join->on('ce.rol_id', '=', 'fc.rol_id')
                         ->whereRaw('YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)')
                         ->whereRaw('MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)')
                         ->where('ce.estado_id', '=', 1);
                }
            )
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->where('r.estado_id', 1)
            ->select(
                'r.id as rol_id',
                'r.nombre as rol',
                'u.id as user_id',
                'u.name as empleado',
                'fc.id as factura_id',
                'fc.monto_rol'
            );

        if ($rolId) {
            $subquery->where('r.id', $rolId);
        }
        if ($empleadoId > 0) {
            $subquery->where('u.id', $empleadoId);
        }

        $query = DB::table(DB::raw("({$subquery->toSql()}) as sub"))
            ->mergeBindings($subquery)
            ->select(
                DB::raw('CONCAT(sub.rol_id, "-", sub.user_id) as id'),
                'sub.rol',
                'sub.empleado',
                DB::raw('SUM(sub.monto_rol) as total_comisiones'),
                DB::raw('COUNT(DISTINCT sub.factura_id) as num_facturas')
            )
            ->groupBy('sub.rol_id', 'sub.rol', 'sub.user_id', 'sub.empleado');

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por usuario
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteUsuarios(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $fechaInicio = $fi . ' 00:00:00';
        $fechaFin = $ff . ' 23:59:59';

        $subquery = DB::table('facturas_comision as fc')
            ->join('rol as r', 'r.id', '=', 'fc.rol_id')
            ->joinSub(
                DB::table('comision_empleado as ce2')
                    ->select('ce2.*')
                    ->whereRaw('ce2.id = (
                        SELECT MAX(ce3.id)
                        FROM comision_empleado ce3
                        WHERE ce3.rol_id = ce2.rol_id
                          AND ce3.estado_id = 1
                          AND YEAR(ce3.mes_comision) = YEAR(ce2.mes_comision)
                          AND MONTH(ce3.mes_comision) = MONTH(ce2.mes_comision)
                    )'),
                'ce',
                function($join) {
                    $join->on('ce.rol_id', '=', 'fc.rol_id')
                         ->whereRaw('YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)')
                         ->whereRaw('MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)')
                         ->where('ce.estado_id', '=', 1);
                }
            )
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->join('producto_comision as pc', 'pc.facturas_comision_id', '=', 'fc.id')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->where('r.estado_id', 1)
            ->select(
                'u.id as user_id',
                'u.name as usuario',
                'r.id as rol_id',
                'r.nombre as rol',
                'fc.id as factura_id',
                'fc.monto_rol',
                'pc.producto_id'
            );

        $query = DB::table(DB::raw("({$subquery->toSql()}) as sub"))
            ->mergeBindings($subquery)
            ->select(
                'sub.user_id as id',
                'sub.usuario',
                'sub.rol',
                DB::raw('SUM(sub.monto_rol) as total_comisiones'),
                DB::raw('COUNT(DISTINCT sub.factura_id) as num_facturas'),
                DB::raw('COUNT(DISTINCT sub.producto_id) as num_productos')
            )
            ->groupBy('sub.user_id', 'sub.usuario', 'sub.rol_id', 'sub.rol');

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por producto
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteProductos(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $fechaInicio = $fi . ' 00:00:00';
        $fechaFin = $ff . ' 23:59:59';
        $empleadoId = (int) $request->input('empleado_id', 0);
        $rolId = (int) $request->input('rol_id', 0);

        $query = DB::table('producto_comision as pc')
            ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->join('producto as p', 'p.id', '=', 'pc.producto_id')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->select(
                'p.id',
                'p.nombre as producto',
                'p.codigo_barra',
                DB::raw('MAX(pc.cantidad) as cantidad_vendida'),
                DB::raw('SUM(pc.cantidad * pc.monto_comision) as total_comisiones')
            )
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra');

        if ($rolId > 0) {
            $query->where('fc.rol_id', $rolId);
        }
        if ($empleadoId > 0) {
            $query->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END = ?",
                [$empleadoId]
            );
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por factura
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteFacturas(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $fechaInicio = $fi . ' 00:00:00';
        $fechaFin = $ff . ' 23:59:59';
        $empleadoId = (int) $request->input('empleado_id', 0);
        $rolId = (int) $request->input('rol_id', 0);

        $query = DB::table('facturas_comision as fc')
            ->joinSub(
                DB::table('comision_empleado as ce2')
                    ->select('ce2.*')
                    ->whereRaw('ce2.id = (
                        SELECT MAX(ce3.id)
                        FROM comision_empleado ce3
                        WHERE ce3.rol_id = ce2.rol_id
                          AND ce3.estado_id = 1
                          AND YEAR(ce3.mes_comision) = YEAR(ce2.mes_comision)
                          AND MONTH(ce3.mes_comision) = MONTH(ce2.mes_comision)
                    )'),
                'ce',
                function($join) {
                    $join->on('ce.rol_id', '=', 'fc.rol_id')
                         ->whereRaw('YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)')
                         ->whereRaw('MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)')
                         ->where('ce.estado_id', '=', 1);
                }
            )
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->join('factura as v', 'v.id', '=', 'fc.factura_id')
            ->join('cliente as cl', 'cl.id', '=', 'v.cliente_id')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->select(
                DB::raw('CONCAT(fc.id, "-", u.id) as id'),
                'v.cai as factura',
                'cl.nombre as cliente',
                'u.name as empleado',
                'v.total as total_venta',
                'fc.monto_rol as total_comision',
                DB::raw('DATE_FORMAT(fc.fecha_cierre_factura, "%Y-%m-%d") as fecha')
            );

        if ($rolId > 0) {
            $query->where('fc.rol_id', $rolId);
        }
        if ($empleadoId > 0) {
            $query->where('u.id', $empleadoId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * KPIs del dashboard para el período seleccionado
     */
    public function stats(Request $request)
    {
        [$fiDate, $ffDate] = $this->resolveDateRange($request);
        $fi    = $fiDate . ' 00:00:00';
        $ff    = $ffDate . ' 23:59:59';
        $fiMes = date('Y-m-01', strtotime($fiDate));
        $ffMes = date('Y-m-01', strtotime($ffDate));
        $empleadoId = (int) $request->input('empleado_id', 0);
        $rolId = (int) $request->input('rol_id', 0);

        $baseCE = DB::table('comision_empleado as ce')
            ->whereBetween('ce.mes_comision', [$fiMes, $ffMes])
            ->where('ce.estado_id', 1);
        if ($empleadoId > 0) {
            $baseCE->where('ce.users_comision', $empleadoId);
        }
        if ($rolId > 0) {
            $baseCE->where('ce.rol_id', $rolId);
        }

        $totalComision = (float) (clone $baseCE)->sum('ce.comision_acumulada');

        $totalEmpleados = (clone $baseCE)
            ->distinct('ce.users_comision')
            ->count('ce.users_comision');

        $fcMap = DB::table('facturas_comision as fc')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->whereBetween('fc.fecha_cierre_factura', [$fi, $ff])
            ->where('fc.estado_id', 1)
            ->selectRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END as user_id,
                 fc.factura_id"
            );

        if ($empleadoId > 0) {
            $fcMap->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END = ?",
                [$empleadoId]
            );
        }
        if ($rolId > 0) {
            $fcMap->where('fc.rol_id', $rolId);
        }

        $totalFacturas = DB::table(DB::raw("({$fcMap->toSql()}) as fm"))
            ->mergeBindings($fcMap)
            ->whereNotNull('fm.user_id')
            ->distinct('fm.factura_id')
            ->count('fm.factura_id');

        $retencionQuery = DB::table('retencion_mora_log')
            ->whereBetween('fecha_aplicacion', [date('Y-m-d', strtotime($fi)), date('Y-m-d', strtotime($ff))]);
        if ($empleadoId > 0) {
            $retencionQuery->where('user_id', $empleadoId);
        }
        if ($rolId > 0) {
            $retencionQuery->where('rol_id', $rolId);
        }
        $totalRetenido = (float) $retencionQuery->sum('monto_retenido');

        $revRows = DB::table('comision_reversiones')
            ->whereBetween('created_at', [$fi, $ff])
            ->select('comisiones_revertidas')
            ->get();

        $totalRevertido = 0.0;
        foreach ($revRows as $row) {
            if (empty($row->comisiones_revertidas)) {
                continue;
            }
            $items = json_decode($row->comisiones_revertidas, true);
            if (!is_array($items)) {
                continue;
            }
            foreach ($items as $it) {
                if ($empleadoId > 0 && (int) ($it['usuario_id'] ?? 0) !== $empleadoId) {
                    continue;
                }
                if ($rolId > 0 && (int) ($it['rol_id'] ?? 0) !== $rolId) {
                    continue;
                }
                $totalRevertido += (float) ($it['monto_revertido'] ?? 0);
            }
        }

        $totalRevertido = round($totalRevertido, 2);

        return response()->json([
            'total_comision'   => number_format($totalComision, 2),
            'total_facturas'   => $totalFacturas,
            'total_empleados'  => $totalEmpleados,
            'total_retenido'   => number_format($totalRetenido, 2),
            'total_revertido'  => number_format($totalRevertido, 2),
        ]);
    }

    /**
     * Nómina consolidada: un registro por empleado + mes.
     */
    public function reporteNomina(Request $request)
    {
        [$fiDate, $ffDate] = $this->resolveDateRange($request);
        $fiFecha = $fiDate . ' 00:00:00';
        $ffFecha = $ffDate . ' 23:59:59';
        $empleadoId = (int) $request->input('empleado_id', 0);
        $rolId      = (int) $request->input('rol_id', 0);

        $nominaBase = DB::table('facturas_comision as fc')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->leftJoin('rol as r', 'r.id', '=', 'fc.rol_id')
            ->whereBetween('fc.fecha_cierre_factura', [$fiFecha, $ffFecha])
            ->where('fc.estado_id', 1)
            ->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END IS NOT NULL"
            )
            ->when($empleadoId > 0, function ($q) use ($empleadoId) {
                $q->whereRaw(
                    "CASE fc.tipo_comision
                        WHEN 1 THEN f.users_id
                        WHEN 2 THEN f.users_id
                        WHEN 3 THEN f.vendedor
                        WHEN 4 THEN f.gestor_entrega
                        ELSE NULL
                    END = ?",
                    [$empleadoId]
                );
            })
            ->when($rolId > 0, function ($q) use ($rolId) {
                $q->where('fc.rol_id', $rolId);
            })
            ->groupByRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END,
                 DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01')"
            )
            ->selectRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END as users_comision,
                 DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01') as mes_comision,
                 COUNT(DISTINCT fc.rol_id) as roles_cantidad,
                 GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') as roles_nombres,
                 COUNT(DISTINCT fc.factura_id) as facturas_comisionadas,
                 ROUND(SUM(fc.monto_rol), 2) as comision_total"
            );

        $query = DB::query()
            ->fromSub($nominaBase, 'n')
            ->join('users as u', 'u.id', '=', 'n.users_comision')
            ->selectRaw(
                "CONCAT(n.users_comision, '-', DATE_FORMAT(n.mes_comision, '%Y-%m')) as id,
                 n.users_comision as empleado_id,
                 u.name as empleado,
                 DATE_FORMAT(n.mes_comision, '%Y-%m') as mes_clave,
                 CONCAT(
                    ELT(MONTH(n.mes_comision), 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'),
                    ' ', YEAR(n.mes_comision)
                 ) as mes,
                 n.roles_cantidad,
                 COALESCE(n.roles_nombres, '') as roles_nombres,
                 COALESCE(n.facturas_comisionadas, 0) as facturas_comisionadas,
                 COALESCE(n.comision_total, 0) as comision_total"
            )
            ->orderByDesc('n.mes_comision')
            ->orderBy('u.name');

        $rows = $query->get()->map(function ($row) {
            return [
                'id'                    => (string) $row->id,
                'empleado_id'           => (int) $row->empleado_id,
                'empleado'              => (string) $row->empleado,
                'mes_clave'             => (string) $row->mes_clave,
                'mes'                   => (string) $row->mes,
                'roles_cantidad'        => (int) $row->roles_cantidad,
                'roles_nombres'         => (string) ($row->roles_nombres ?? ''),
                'facturas_comisionadas' => (int) $row->facturas_comisionadas,
                'comision_total'        => (float) $row->comision_total,
            ];
        })->values();

        $recordsTotal = $rows->count();

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $needle = mb_strtolower($search, 'UTF-8');
            $rows = $rows->filter(function (array $row) use ($needle) {
                $haystack = mb_strtolower(implode(' ', [
                    $row['empleado'],
                    $row['mes_clave'],
                    $row['mes'],
                    $row['roles_nombres'],
                    (string) $row['facturas_comisionadas'],
                    number_format((float) $row['comision_total'], 2, '.', ''),
                ]), 'UTF-8');

                return str_contains($haystack, $needle);
            })->values();
        }

        $recordsFiltered = $rows->count();

        $orderCol = (int) $request->input('order.0.column', 3);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderMap = [
            1 => 'empleado',
            2 => 'roles_cantidad',
            3 => 'mes_clave',
            4 => 'facturas_comisionadas',
            5 => 'comision_total',
        ];

        $sortKey = $orderMap[$orderCol] ?? 'mes_clave';
        $rows = $rows->sort(function (array $a, array $b) use ($sortKey, $orderDir) {
            $va = $a[$sortKey] ?? null;
            $vb = $b[$sortKey] ?? null;

            if (is_numeric($va) && is_numeric($vb)) {
                $cmp = ((float) $va <=> (float) $vb);
            } else {
                $cmp = strnatcasecmp((string) $va, (string) $vb);
            }

            return $orderDir === 'asc' ? $cmp : -$cmp;
        })->values();

        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 25);
        if ($length < 0) {
            $length = $recordsFiltered;
        }

        $data = $rows->slice($start, $length)->values()->all();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Detalle de nómina por empleado + mes.
     * Incluye activas y revertidas para trazabilidad de estado.
     */
    public function detalleNomina(Request $request)
    {
        $empleadoId = (int) $request->input('empleado_id', 0);
        $mesClave   = trim((string) $request->input('mes_clave', '')); // YYYY-MM

        if ($empleadoId <= 0 || !preg_match('/^\d{4}-\d{2}$/', $mesClave)) {
            return response()->json(['data' => []]);
        }

        [$fi, $ff] = $this->resolveDateRange($request);

        $mesInicioDt = Carbon::parse($mesClave . '-01 00:00:00');
        $mesFinDt    = Carbon::parse($mesClave . '-01')->endOfMonth()->setTime(23, 59, 59);
        $filtroInicioDt = Carbon::parse($fi . ' 00:00:00');
        $filtroFinDt    = Carbon::parse($ff . ' 23:59:59');

        // Intersección: detalle del mes seleccionado respetando el rango global filtrado.
        $inicioConsultaDt = $mesInicioDt->greaterThan($filtroInicioDt) ? $mesInicioDt : $filtroInicioDt;
        $finConsultaDt    = $mesFinDt->lessThan($filtroFinDt) ? $mesFinDt : $filtroFinDt;

        if ($inicioConsultaDt->greaterThan($finConsultaDt)) {
            return response()->json(['data' => []]);
        }

        $mesInicio = $inicioConsultaDt->format('Y-m-d H:i:s');
        $mesFin    = $finConsultaDt->format('Y-m-d H:i:s');
        $mesBase   = $mesClave . '-01';

        $rolesEmpleadoMes = DB::table('comision_empleado')
            ->where('users_comision', $empleadoId)
            ->where('mes_comision', $mesBase)
            ->where('estado_id', 1)
            ->distinct()
            ->pluck('rol_id')
            ->all();

        if (empty($rolesEmpleadoMes)) {
            return response()->json(['data' => []]);
        }

        $detalles = DB::table('facturas_comision as fc')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->join('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->leftJoin('rol as r', 'r.id', '=', 'fc.rol_id')
            ->whereBetween('fc.fecha_cierre_factura', [$mesInicio, $mesFin])
            ->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END = ?",
                [$empleadoId]
            )
            ->whereIn('fc.rol_id', $rolesEmpleadoMes)
            ->selectRaw(
                "fc.id,
                 f.cai as factura,
                 cl.nombre as cliente,
                 DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-%d') as fecha_cierre,
                 r.nombre as rol_comisionado,
                 fc.estado_id,
                 COALESCE(fc.retencion_mora_monto, 0) as retencion_aplicada,
                 COALESCE(fc.monto_rol, 0) as comision_final,
                 ROUND(COALESCE(fc.monto_rol, 0) + COALESCE(fc.retencion_mora_monto, 0), 4) as comision_original"
            )
            ->orderByDesc('fc.fecha_cierre_factura')
            ->get();

        $facturaIds = $detalles->pluck('id')->all();

        $observacionesPorFc = [];
        if (!empty($facturaIds)) {
            $reversiones = DB::table('comision_reversiones')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$mesClave])
                ->whereNotNull('comisiones_revertidas')
                ->get(['motivo', 'comisiones_revertidas']);

            foreach ($reversiones as $rev) {
                $items = json_decode($rev->comisiones_revertidas, true);
                if (!is_array($items)) {
                    continue;
                }
                foreach ($items as $it) {
                    $fcId = (int) ($it['facturas_comision_id'] ?? 0);
                    if ($fcId <= 0) {
                        continue;
                    }
                    if (!isset($observacionesPorFc[$fcId])) {
                        $observacionesPorFc[$fcId] = [];
                    }
                    if (!empty($rev->motivo)) {
                        $observacionesPorFc[$fcId][] = $rev->motivo;
                    }
                }
            }
        }

        $resumenProductosPorFc = [];
        $detalleProductosPorFc = [];
        if (!empty($facturaIds)) {
            $lineas = DB::table('producto_comision as pc')
                ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
                ->join('factura as f', 'f.id', '=', 'fc.factura_id')
                ->join('cliente as cl', 'cl.id', '=', 'f.cliente_id')
                ->leftJoin('producto as p', 'p.id', '=', 'pc.producto_id')
                ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'pc.precios_producto_carga_id')
                ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
                ->leftJoin('cliente_categoria_escala as cce', 'cce.id', '=', 'cl.cliente_categoria_escala_id')
                ->whereIn('pc.facturas_comision_id', $facturaIds)
                ->selectRaw(
                    "pc.facturas_comision_id as fc_id,
                     pc.producto_id,
                     p.nombre as producto_nombre,
                     cp.nombre as escala_precio_nombre,
                     cce.nombre_categoria as categoria_cliente_escala_nombre,
                     ppc.categoria_precios_id,
                     COALESCE(pc.cantidad, 0) as cantidad,
                     COALESCE(pc.precio_venta, 0) as precio_venta,
                     COALESCE(pc.monto_comision, 0) as monto_comision,
                     COALESCE((
                        SELECT ce.porcentaje_comision
                        FROM comision_escala ce
                        WHERE ce.rol_id = fc.rol_id
                          AND ce.categoria_precios_id = ppc.categoria_precios_id
                          AND ce.estado_id = 1
                        ORDER BY ce.id DESC
                        LIMIT 1
                     ), 0) as porcentaje_comision"
                )
                ->orderBy('pc.facturas_comision_id')
                ->orderBy('p.nombre')
                ->get();

            foreach ($lineas as $ln) {
                $fcId = (int) $ln->fc_id;
                if (!isset($resumenProductosPorFc[$fcId])) {
                    $resumenProductosPorFc[$fcId] = [];
                }

                $cantidadFmt = rtrim(rtrim(number_format((float) $ln->cantidad, 2, '.', ''), '0'), '.');
                if ($cantidadFmt === '') {
                    $cantidadFmt = '0';
                }

                $productoNombre = $ln->producto_nombre;
                if (empty($productoNombre)) {
                    $productoNombre = 'Producto #' . (int) $ln->producto_id;
                }

                $escalaNombre = $ln->escala_precio_nombre;
                if (empty($escalaNombre)) {
                    $catId = (int) ($ln->categoria_precios_id ?? 0);
                    $escalaNombre = $catId > 0 ? ('Categoria #' . $catId) : 'Sin categoria';
                }

                $categoriaClienteEscala = $ln->categoria_cliente_escala_nombre;
                if (empty($categoriaClienteEscala)) {
                    $categoriaClienteEscala = 'Sin categoria cliente';
                }

                if (!isset($detalleProductosPorFc[$fcId])) {
                    $detalleProductosPorFc[$fcId] = [];
                }

                $detalleProductosPorFc[$fcId][] = [
                    'producto' => $productoNombre,
                    'categoria_cliente_escala' => $categoriaClienteEscala,
                    'categoria_precio_vendida' => $escalaNombre,
                    'porcentaje_comision' => round((float) $ln->porcentaje_comision, 2),
                    'cantidad' => (float) $ln->cantidad,
                    'precio_venta' => round((float) $ln->precio_venta, 2),
                    'base_comisionable' => round(((float) $ln->cantidad) * ((float) $ln->precio_venta), 2),
                    'fuente_base_comisionable' => 'Cantidad x Precio venta (producto_comision)',
                    'comision' => round((float) $ln->monto_comision, 2),
                ];

                $resumenProductosPorFc[$fcId][] =
                    $productoNombre
                    . ' | Escala: ' . $escalaNombre
                    . ' | %: ' . number_format((float) $ln->porcentaje_comision, 2)
                    . ' | Cant: ' . $cantidadFmt
                    . ' | Precio: L. ' . number_format((float) $ln->precio_venta, 2)
                    . ' | Base: L. ' . number_format(((float) $ln->cantidad) * ((float) $ln->precio_venta), 2)
                    . ' | Comisión: L. ' . number_format((float) $ln->monto_comision, 2);
            }
        }

        $baseComisionablePorFc = [];
        foreach ($detalleProductosPorFc as $fcId => $items) {
            $baseComisionablePorFc[(int) $fcId] = (float) collect($items)->sum(function ($item) {
                return (float) ($item['base_comisionable'] ?? 0);
            });
        }

        $data = $detalles->map(function ($row) use ($observacionesPorFc, $resumenProductosPorFc, $detalleProductosPorFc, $baseComisionablePorFc) {
            $obs = $observacionesPorFc[(int) $row->id] ?? [];
            $obs = array_values(array_unique($obs));
            $resumenProductos = $resumenProductosPorFc[(int) $row->id] ?? [];
            $detalleProductos = $detalleProductosPorFc[(int) $row->id] ?? [];

            return [
                'id'                 => (int) $row->id,
                'factura'            => $row->factura,
                'cliente'            => $row->cliente,
                'fecha_cierre'       => $row->fecha_cierre,
                'rol_comisionado'    => $row->rol_comisionado,
                'comision_original'  => (float) $row->comision_original,
                'retencion_aplicada' => (float) $row->retencion_aplicada,
                'comision_final'     => (float) $row->comision_final,
                'base_comisionable'  => (float) ($baseComisionablePorFc[(int) $row->id] ?? 0),
                'fuente_base_comisionable' => !empty($detalleProductos)
                    ? 'Suma de (Cantidad x Precio venta) de líneas en producto_comision'
                    : 'Sin líneas de detalle para calcular base',
                'resumen_productos'  => !empty($resumenProductos) ? implode("\n", $resumenProductos) : null,
                'detalle_productos'  => $detalleProductos,
                'estado'             => (int) $row->estado_id === 1 ? 'ACTIVA' : 'REVERTIDA',
                'observacion_reversa'=> !empty($obs) ? implode(' | ', $obs) : null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Ranking de empleados por comisión total en el período
     */
    public function reporteRanking(Request $request)
    {
        [$fiDate, $ffDate] = $this->resolveDateRange($request);
        $fiMes = date('Y-m-01', strtotime($fiDate));
        $ffMes = date('Y-m-01', strtotime($ffDate));
        $empleadoId = (int) $request->input('empleado_id', 0);
        $rolId = (int) $request->input('rol_id', 0);

        $query = DB::table('comision_empleado as ce')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->join('rol as r',   'r.id', '=', 'ce.rol_id')
            ->whereBetween('ce.mes_comision', [$fiMes, $ffMes])
            ->where('ce.estado_id', 1)
            ->groupBy('u.id', 'u.name', 'r.id', 'r.nombre')
            ->select(
                DB::raw('CONCAT(u.id,"-",r.id) as id'),
                'u.name as empleado',
                'r.nombre as rol',
                DB::raw('SUM(ce.comision_acumulada) as total_comision'),
                DB::raw('COUNT(ce.id)               as meses_activos'),
                DB::raw('MAX(ce.comision_acumulada)  as mejor_mes'),
                DB::raw('AVG(ce.comision_acumulada)  as promedio_mes')
            )
            ->orderByRaw('SUM(ce.comision_acumulada) DESC');

        if ($empleadoId > 0) {
            $query->where('ce.users_comision', $empleadoId);
        }
        if ($rolId > 0) {
            $query->where('ce.rol_id', $rolId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Comparativo mensual de comisiones
     */
    public function reporteComparativo(Request $request)
    {
        [$fiDate, $ffDate] = $this->resolveDateRange($request);
        $fiMes = date('Y-m-01', strtotime($fiDate));
        $ffMes = date('Y-m-01', strtotime($ffDate));
        $empleadoId = (int) $request->input('empleado_id', 0);
        $rolId = (int) $request->input('rol_id', 0);

        $query = DB::table('comision_empleado as ce')
            ->whereBetween('ce.mes_comision', [$fiMes, $ffMes])
            ->where('ce.estado_id', 1)
            ->groupByRaw('YEAR(ce.mes_comision), MONTH(ce.mes_comision), ce.mes_comision')
            ->select(
                DB::raw("DATE_FORMAT(ce.mes_comision,'%Y-%m')  as mes_clave"),
                DB::raw("DATE_FORMAT(ce.mes_comision,'%M %Y')  as mes"),
                DB::raw('SUM(ce.comision_acumulada)           as total_comisiones'),
                DB::raw('COUNT(DISTINCT ce.users_comision)  as empleados'),
                DB::raw('COUNT(DISTINCT ce.rol_id)          as roles'),
                DB::raw('MAX(ce.comision_acumulada)          as mayor_comision'),
                DB::raw('MIN(ce.comision_acumulada)          as menor_comision')
            )
            ->orderBy('mes_clave');

        if ($empleadoId > 0) {
            $query->where('ce.users_comision', $empleadoId);
        }
        if ($rolId > 0) {
            $query->where('ce.rol_id', $rolId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte de comisiones reversadas (anulación de pagos)
     */
    public function reporteReversiones(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $fechaInicio = $fi . ' 00:00:00';
        $fechaFin = $ff . ' 23:59:59';
        $empleadoId = (int) $request->input('empleado_id', 0);
        $rolId = (int) $request->input('rol_id', 0);

        $query = DB::table('comision_reversiones as cr')
            ->leftJoin('factura as f', 'f.id', '=', 'cr.factura_id')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->leftJoin('users as ua', 'ua.id', '=', 'cr.usr_anulo')
            ->leftJoin('facturas_comision as fc', 'fc.factura_id', '=', 'cr.factura_id')
            ->whereBetween('cr.created_at', [$fechaInicio, $fechaFin])
            ->when($rolId > 0, function ($q) use ($rolId) {
                $q->where('fc.rol_id', $rolId);
            })
            ->when($empleadoId > 0, function ($q) use ($empleadoId) {
                $q->whereRaw(
                    "CASE fc.tipo_comision
                        WHEN 1 THEN f.users_id
                        WHEN 2 THEN f.users_id
                        WHEN 3 THEN f.vendedor
                        WHEN 4 THEN f.gestor_entrega
                        ELSE NULL
                    END = ?",
                    [$empleadoId]
                );
            })
            ->select(
                'cr.id',
                'cr.abono_id',
                'cr.factura_id',
                'cr.aplicacion_pagos_id',
                'cr.monto_abono_anulado',
                'cr.tenia_comisiones',
                'cr.comisiones_revertidas',
                'cr.motivo',
                'cr.factura_reabierta',
                'cr.created_at',
                'f.cai as factura',
                'cl.nombre as cliente',
                'ua.name as usuario_anulo'
            )
            ->groupBy(
                'cr.id',
                'cr.abono_id',
                'cr.factura_id',
                'cr.aplicacion_pagos_id',
                'cr.monto_abono_anulado',
                'cr.tenia_comisiones',
                'cr.comisiones_revertidas',
                'cr.motivo',
                'cr.factura_reabierta',
                'cr.created_at',
                'f.cai',
                'cl.nombre',
                'ua.name'
            );

        return DataTables::of($query)
            ->addColumn('total_revertido', function ($row) {
                $items = [];
                if (!empty($row->comisiones_revertidas)) {
                    $tmp = json_decode($row->comisiones_revertidas, true);
                    if (is_array($tmp)) {
                        $items = $tmp;
                    }
                }

                $total = 0;
                foreach ($items as $it) {
                    $total += (float) ($it['monto_revertido'] ?? 0);
                }

                return round($total, 2);
            })
            ->addColumn('comisiones_afectadas', function ($row) {
                $items = [];
                if (!empty($row->comisiones_revertidas)) {
                    $tmp = json_decode($row->comisiones_revertidas, true);
                    if (is_array($tmp)) {
                        $items = $tmp;
                    }
                }
                return count($items);
            })
            ->make(true);
    }

    /**
     * Descargar reporte en Excel
     */
    public function descargarExcel(Request $request)
    {
        $tipoReporte = $request->input('tipoReporte');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $filtroEspecifico = $request->input('filtroEspecifico');

        // Generar nombre de archivo con fecha
        $fecha = now()->format('Y-m-d_His');
        $nombreArchivo = "reporte_comisiones_{$tipoReporte}_{$fecha}.xlsx";

        // Aquí deberías crear una clase Export específica según el tipo
        // Por ahora retorno un mensaje
        return response()->json([
            'message' => 'Funcionalidad de export en desarrollo',
            'tipo' => $tipoReporte
        ]);
    }
}
