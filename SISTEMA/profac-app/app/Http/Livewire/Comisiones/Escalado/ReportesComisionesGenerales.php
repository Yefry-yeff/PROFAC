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
use App\Models\Comisiones\ModelComisionPeriodo;
use App\Services\Comisiones\GeneradorFacturasComision;
use App\Services\Comisiones\AplicadorRetencionesMora;
use App\Services\Comisiones\ProcesadorComisiones;

class ReportesComisionesGenerales extends Component
{
    private function resolveDetalleProductoMode(float $esperado, float $sumaRaw, float $sumaWeighted): string
    {
        $tolerance = 0.05;

        if ($esperado > 0) {
            $diffRaw = abs($sumaRaw - $esperado);
            $diffWeighted = abs($sumaWeighted - $esperado);

            if ($diffWeighted + $tolerance < $diffRaw) {
                return 'unitario';
            }

            if ($diffRaw + $tolerance < $diffWeighted) {
                return 'linea';
            }
        }

        if ($sumaWeighted > $sumaRaw + $tolerance) {
            return 'unitario';
        }

        return 'linea';
    }

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
     * Lista de roles con parametrización activa de comisiones y habilitados para calcular.
     */
    public function listarRolesComisionables(Request $request)
    {
        $search = $request->input('q', '');

        $roles = DB::table('rol as r')
            ->join('comision_escala as ce', function ($join) {
                $join->on('ce.rol_id', '=', 'r.id')
                    ->where('ce.estado_id', 1);
            })
            ->leftJoin('comision_rol_config as crc', 'crc.rol_id', '=', 'r.id')
            ->where('r.estado_id', 1)
            ->where(function ($q) {
                $q->whereNull('crc.calcular')
                    ->orWhere('crc.calcular', 1);
            })
            ->where('r.nombre', 'LIKE', "%{$search}%")
            ->select('r.id', 'r.nombre as name')
            ->distinct()
            ->orderBy('r.nombre')
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

        // Nota: el consolidado de nómina se arma desde facturas_comision.
        // No filtrar por comision_empleado aquí, porque las filas con comisión final 0
        // (por retención total) pueden no tener acumulado y aun así deben verse en detalle.

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
            $comisionOriginalEsperadaPorFc = $detalles
                ->mapWithKeys(fn($row) => [(int) $row->id => (float) $row->comision_original])
                ->all();

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
                     COALESCE(pc.precio_venta, 0) as precio_unitario,
                     COALESCE(pc.precio_venta, 0) as precio_venta,
                     COALESCE(pc.monto_comision, 0) as monto_comision"
                )
                ->orderBy('pc.facturas_comision_id')
                ->orderBy('p.nombre')
                ->get();

            foreach ($lineas->groupBy('fc_id') as $fcId => $lineasFactura) {
                $fcId = (int) $fcId;
                if (!isset($resumenProductosPorFc[$fcId])) {
                    $resumenProductosPorFc[$fcId] = [];
                }

                // Consolidar duplicados históricos por producto+precio+escala dentro de la misma factura_comision.
                // Esto evita mostrar renglones repetidos cuando existen múltiples inserts idénticos en producto_comision.
                $lineasConsolidadas = collect($lineasFactura)
                    ->groupBy(function ($ln) {
                        return implode('|', [
                            (int) ($ln->producto_id ?? 0),
                            (int) ($ln->categoria_precios_id ?? 0),
                            (string) ($ln->precio_venta ?? '0'),
                            (string) ($ln->monto_comision ?? '0'),
                        ]);
                    })
                    ->map(function ($grupo) {
                        $base = clone $grupo->first();
                        $base->cantidad = $grupo->sum(function ($item) {
                            return (float) ($item->cantidad ?? 0);
                        });
                        return $base;
                    })
                    ->values();

                $sumaRaw = 0.0;
                $sumaWeighted = 0.0;

                foreach ($lineasConsolidadas as $lineaFactura) {
                    $cantidad = (float) $lineaFactura->cantidad;
                    $montoRaw = (float) $lineaFactura->monto_comision;
                    $sumaRaw += $montoRaw;
                    $sumaWeighted += $montoRaw * $cantidad;
                }

                $modoMonto = $this->resolveDetalleProductoMode(
                    (float) ($comisionOriginalEsperadaPorFc[$fcId] ?? 0),
                    $sumaRaw,
                    $sumaWeighted
                );

                foreach ($lineasConsolidadas as $ln) {
                    $cantidad = (float) $ln->cantidad;
                    $precioUnitario = (float) $ln->precio_unitario;
                    $precioVenta = (float) $ln->precio_venta;
                    $montoRaw = (float) $ln->monto_comision;
                    $baseComisionable = round($cantidad * $precioVenta, 2);
                    $montoNormalizado = $modoMonto === 'unitario'
                        ? round($montoRaw * $cantidad, 2)
                        : round($montoRaw, 2);
                    $porcentajeHistorico = $baseComisionable > 0
                        ? round(($montoNormalizado / $baseComisionable) * 100, 2)
                        : 0.0;

                    $cantidadFmt = rtrim(rtrim(number_format($cantidad, 2, '.', ''), '0'), '.');
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
                        'porcentaje_comision' => $porcentajeHistorico,
                        'cantidad' => $cantidad,
                        'precio_unitario' => round($precioUnitario, 2),
                        'precio_venta' => round($precioVenta, 2),
                        'base_comisionable' => $baseComisionable,
                        'fuente_base_comisionable' => 'Cantidad x Precio venta (producto_comision)',
                        'comision' => $montoNormalizado,
                    ];

                    $resumenProductosPorFc[$fcId][] =
                        $productoNombre
                        . ' | Escala: ' . $escalaNombre
                        . ' | %: ' . number_format($porcentajeHistorico, 2)
                        . ' | Cant: ' . $cantidadFmt
                        . ' | Precio Escala: L. ' . number_format($precioVenta, 2)
                        . ' | Base: L. ' . number_format($baseComisionable, 2)
                        . ' | Comisión: L. ' . number_format($montoNormalizado, 2);
                }
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
     * Proyección unificada de comisiones (Asesor, Teleasesor y Gestor de Entrega).
     *
     * Reglas:
     * - Facturas con pago/cierre registrado en CxC dentro del rango solicitado.
     * - Facturas cerradas: estado_cerrado = 2 o saldo <= 0.
     * - Base comisionable unitaria: cantidad * precio_unidad.
     * - Base comisionable: cantidad * COALESCE(precioSeleccionado, precio_unidad).
     * - Excluir y reportar facturas no aplicables por faltantes de escala o líneas inválidas.
     */
    public function reporteProyecciones(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $usuarioId = (int) $request->input('usuario_id', 0);
        $rolIdFiltro = (int) $request->input('rol_id', 0);

        $cierres = DB::table('aplicacion_pagos as ap')
            ->leftJoin('abonos_creditos as ac', function ($join) {
                $join->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                    ->where('ac.estado_abono', '=', 1);
            })
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', 2)
            ->groupBy('ap.id', 'ap.factura_id', 'ap.fecha_cierre_factura')
            ->selectRaw("ap.id as aplicacion_pagos_id,
                         ap.factura_id,
                         COALESCE(DATE(ap.fecha_cierre_factura), MAX(DATE(COALESCE(ac.fecha_pago, ac.created_at)))) as fecha_pago_cierre")
            ->havingRaw('fecha_pago_cierre IS NOT NULL')
            ->havingBetween('fecha_pago_cierre', [$fi, $ff])
            ->get();

        if ($cierres->isEmpty()) {
            return response()->json([
                'data' => [],
                'excluidas' => [],
                'totales' => [
                    'facturas_proyectadas' => 0,
                    'registros_proyectados' => 0,
                    'base_unitaria_total' => 0,
                    'base_comisionable_total' => 0,
                    'comision_proyectada_total' => 0,
                    'facturas_excluidas' => 0,
                    'registros_excluidos' => 0,
                ],
            ]);
        }

        $cierresPorFactura = $cierres->keyBy('factura_id');
        $facturaIds = $cierres->pluck('factura_id')->map(fn($v) => (int) $v)->all();

        $facturas = DB::table('factura as f')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->leftJoin('cliente_categoria_escala as cce', 'cce.id', '=', 'cl.cliente_categoria_escala_id')
            ->leftJoin('users as uf', 'uf.id', '=', 'f.users_id')
            ->leftJoin('users as uv', 'uv.id', '=', 'f.vendedor')
            ->leftJoin('users as ug', 'ug.id', '=', 'f.gestor_entrega')
            ->whereIn('f.id', $facturaIds)
            ->selectRaw("f.id,
                         f.cai,
                         COALESCE(f.sub_total, 0) as sub_total_factura,
                         DATE_FORMAT(f.created_at, '%Y-%m-%d %H:%i:%s') as fecha_creacion_factura,
                         f.users_id as facturador_id,
                         uf.name as facturador_nombre,
                         f.vendedor as vendedor_id,
                         uv.name as vendedor_nombre,
                         f.gestor_entrega as gestor_id,
                         ug.name as gestor_nombre,
                         cl.nombre as cliente,
                         cl.cliente_categoria_escala_id,
                         cce.nombre_categoria as escala_cliente")
            ->get();

        if ($facturas->isEmpty()) {
            return response()->json([
                'data' => [],
                'excluidas' => [],
                'totales' => [
                    'facturas_proyectadas' => 0,
                    'registros_proyectados' => 0,
                    'base_unitaria_total' => 0,
                    'base_comisionable_total' => 0,
                    'comision_proyectada_total' => 0,
                    'facturas_excluidas' => 0,
                    'registros_excluidos' => 0,
                ],
            ]);
        }

        $lineasFactura = DB::table('venta_has_producto as vhp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'vhp.precios_producto_carga_id')
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->leftJoin('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->whereIn('vhp.factura_id', $facturaIds)
            ->selectRaw("vhp.factura_id,
                         vhp.producto_id,
                         p.nombre as producto,
                         vhp.cantidad,
                         vhp.precio_unidad,
                         vhp.precioSeleccionado,
                         vhp.precios_producto_carga_id,
                         ppc.categoria_precios_id,
                         cp.nombre as categoria_precio")
            ->get()
            ->groupBy('factura_id');

        $clienteEscalaIds = $facturas
            ->pluck('cliente_categoria_escala_id')
            ->filter(fn($v) => (int) $v > 0)
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        $categoriaIds = $lineasFactura
            ->flatten(1)
            ->pluck('categoria_precios_id')
            ->filter(fn($v) => (int) $v > 0)
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        $escalaMap = [];
        if (!empty($clienteEscalaIds) && !empty($categoriaIds)) {
            $escalaRows = DB::table('comision_escala')
                ->where('estado_id', 1)
                ->whereIn('rol_id', [2, 3, 16])
                ->whereIn('cliente_categoria_escala_id', $clienteEscalaIds)
                ->whereIn('categoria_precios_id', $categoriaIds)
                ->select('id', 'rol_id', 'cliente_categoria_escala_id', 'categoria_precios_id', 'porcentaje_comision')
                ->get();

            foreach ($escalaRows as $esc) {
                $key = (int) $esc->rol_id . '|' . (int) $esc->cliente_categoria_escala_id . '|' . (int) $esc->categoria_precios_id;
                $escalaMap[$key] = $esc;
            }
        }

        $filas = [];
        $excluidas = [];

        foreach ($facturas as $factura) {
            $facturaId = (int) $factura->id;
            $cierre = $cierresPorFactura->get($facturaId);
            $lineas = collect($lineasFactura->get($facturaId, collect([])))->values();

            $targets = [
                [
                    'capacidad' => 'TELEASESOR',
                    'rol_id' => 3,
                    'rol_nombre' => 'Televendedor',
                    'user_id' => (int) ($factura->facturador_id ?? 0),
                    'usuario' => (string) ($factura->facturador_nombre ?? ''),
                ],
                [
                    'capacidad' => 'ASESOR',
                    'rol_id' => 2,
                    'rol_nombre' => 'Asesor Comercial',
                    'user_id' => (int) ($factura->vendedor_id ?? 0),
                    'usuario' => (string) ($factura->vendedor_nombre ?? ''),
                ],
                [
                    'capacidad' => 'GESTOR_ENTREGA',
                    'rol_id' => 16,
                    'rol_nombre' => 'Gestor de Entrega',
                    'user_id' => (int) ($factura->gestor_id ?? 0),
                    'usuario' => (string) ($factura->gestor_nombre ?? ''),
                ],
            ];

            foreach ($targets as $target) {
                if ((int) $target['user_id'] <= 0) {
                    continue;
                }

                if ($rolIdFiltro > 0 && (int) $target['rol_id'] !== $rolIdFiltro) {
                    continue;
                }

                if ($usuarioId > 0 && (int) $target['user_id'] !== $usuarioId) {
                    continue;
                }

                $motivos = [];

                if ((int) ($factura->cliente_categoria_escala_id ?? 0) <= 0) {
                    $motivos[] = 'Cliente sin categoria de escala configurada';
                }

                if ($lineas->isEmpty()) {
                    $motivos[] = 'Factura sin lineas de productos para comision';
                }

                $categoriaBuckets = [];
                $comisionTotal = 0.0;

                foreach ($lineas as $linea) {
                    $cantidad = (float) ($linea->cantidad ?? 0);
                    $precioUnitario = (float) ($linea->precio_unidad ?? 0);
                    $precioSeleccionado = (float) ($linea->precioSeleccionado ?? 0);
                    $precioParaComision = $precioSeleccionado > 0 ? $precioSeleccionado : $precioUnitario;

                    $baseUnitaria = round($cantidad * $precioUnitario, 4);
                    $baseComisionable = round($cantidad * $precioParaComision, 4);

                    if (empty($linea->precios_producto_carga_id)) {
                        $motivos[] = 'Linea sin precios_producto_carga_id';
                        continue;
                    }

                    $categoriaPrecioId = (int) ($linea->categoria_precios_id ?? 0);
                    if ($categoriaPrecioId <= 0) {
                        $motivos[] = 'Linea sin categoria de precio (categoria_precios_id)';
                        continue;
                    }

                    $escalaKey = (int) $target['rol_id'] . '|' . (int) $factura->cliente_categoria_escala_id . '|' . $categoriaPrecioId;
                    if (!isset($escalaMap[$escalaKey])) {
                        $motivos[] = 'Sin escala activa para rol/categoria cliente/categoria precio';
                        continue;
                    }

                    $porcentaje = (float) ($escalaMap[$escalaKey]->porcentaje_comision ?? 0);
                    $comisionLinea = round($baseComisionable * ($porcentaje / 100), 4);

                    $comisionTotal += $comisionLinea;

                    $categoriaPrecioNombre = (string) ($linea->categoria_precio ?? ('Categoria #' . $categoriaPrecioId));
                    $bucketKey = $categoriaPrecioId . '|' . $categoriaPrecioNombre;

                    if (!isset($categoriaBuckets[$bucketKey])) {
                        $categoriaBuckets[$bucketKey] = [
                            'categoria_precio_id' => $categoriaPrecioId,
                            'categoria_precio' => $categoriaPrecioNombre,
                            'cantidad' => 0.0,
                            'base_unitaria' => 0.0,
                            'base_comisionable' => 0.0,
                            'comision' => 0.0,
                            'porcentaje' => $porcentaje,
                            'detalle_lineas' => [],
                        ];
                    }

                    $categoriaBuckets[$bucketKey]['cantidad'] += $cantidad;
                    $categoriaBuckets[$bucketKey]['base_unitaria'] += $baseUnitaria;
                    $categoriaBuckets[$bucketKey]['base_comisionable'] += $baseComisionable;
                    $categoriaBuckets[$bucketKey]['comision'] += $comisionLinea;
                    $categoriaBuckets[$bucketKey]['porcentaje'] = $porcentaje;

                    $categoriaBuckets[$bucketKey]['detalle_lineas'][] = [
                        'producto' => (string) ($linea->producto ?? ('Producto #' . (int) $linea->producto_id)),
                        'categoria_precio' => $categoriaPrecioNombre,
                        'cantidad' => $cantidad,
                        'precio_unitario' => round($precioUnitario, 4),
                        'precio_para_comision' => round($precioParaComision, 4),
                        'base_unitaria' => round($baseUnitaria, 4),
                        'base_comisionable' => round($baseComisionable, 4),
                        'porcentaje' => round($porcentaje, 4),
                        'comision_linea' => round($comisionLinea, 4),
                        'fuente_base_comisionable' => $precioSeleccionado > 0
                            ? 'Cantidad x precioSeleccionado'
                            : 'Cantidad x precio_unidad (fallback)',
                    ];
                }

                $motivos = array_values(array_unique($motivos));

                if (!empty($motivos) || $comisionTotal <= 0 || empty($categoriaBuckets)) {
                    if ($comisionTotal <= 0 && empty($motivos)) {
                        $motivos[] = 'Comision proyectada igual a 0';
                    }

                    $excluidas[] = [
                        'factura_id' => $facturaId,
                        'factura' => (string) ($factura->cai ?? ('#' . $facturaId)),
                        'fecha_pago' => (string) ($cierre->fecha_pago_cierre ?? ''),
                        'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                        'cliente' => (string) ($factura->cliente ?? 'N/A'),
                        'capacidad' => (string) $target['capacidad'],
                        'usuario_id' => (int) $target['user_id'],
                        'usuario' => (string) ($target['usuario'] ?: ('Usuario #' . (int) $target['user_id'])),
                        'razon_no_comisionable' => (string) ($motivos[0] ?? 'No aplica para comision'),
                        'motivos' => $motivos,
                    ];
                    continue;
                }

                foreach ($categoriaBuckets as $bucket) {
                    $filas[] = [
                        'factura_id' => $facturaId,
                        'factura' => (string) ($factura->cai ?? ('#' . $facturaId)),
                        'fecha_pago' => (string) ($cierre->fecha_pago_cierre ?? ''),
                        'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                        'cliente' => (string) ($factura->cliente ?? 'N/A'),
                        'escala_cliente' => (string) ($factura->escala_cliente ?? 'N/A'),
                        'escala_precio_vendida' => (string) ($bucket['categoria_precio'] ?? 'N/A'),
                        'cantidad' => round((float) ($bucket['cantidad'] ?? 0), 4),
                        'capacidad' => (string) $target['capacidad'],
                        'rol_id' => (int) $target['rol_id'],
                        'rol_nombre' => (string) $target['rol_nombre'],
                        'usuario_id' => (int) $target['user_id'],
                        'usuario' => (string) ($target['usuario'] ?: ('Usuario #' . (int) $target['user_id'])),
                        'base_comisionable_unitaria' => round((float) ($bucket['base_unitaria'] ?? 0), 4),
                        'base_comisionable' => round((float) ($bucket['base_comisionable'] ?? 0), 4),
                        'comision_proyectada' => round((float) ($bucket['comision'] ?? 0), 4),
                        'porcentaje_promedio' => round((float) ($bucket['porcentaje'] ?? 0), 4),
                        'detalle_lineas' => $bucket['detalle_lineas'] ?? [],
                    ];
                }
            }
        }

        $filas = collect($filas)
            ->sortBy([
                ['fecha_pago', 'asc'],
                ['factura', 'asc'],
                ['escala_precio_vendida', 'asc'],
                ['capacidad', 'asc'],
            ])
            ->values()
            ->all();

        $excluidas = collect($excluidas)
            ->sortBy([
                ['fecha_pago', 'asc'],
                ['factura', 'asc'],
                ['capacidad', 'asc'],
            ])
            ->values()
            ->all();

        $totales = [
            'facturas_proyectadas' => count(array_unique(array_map(fn($r) => (int) $r['factura_id'], $filas))),
            'registros_proyectados' => count($filas),
            'base_unitaria_total' => round(array_sum(array_map(fn($r) => (float) $r['base_comisionable_unitaria'], $filas)), 4),
            'base_comisionable_total' => round(array_sum(array_map(fn($r) => (float) $r['base_comisionable'], $filas)), 4),
            'comision_proyectada_total' => round(array_sum(array_map(fn($r) => (float) $r['comision_proyectada'], $filas)), 4),
            'facturas_excluidas' => count(array_unique(array_map(fn($r) => (string) $r['factura'] . '|' . (string) $r['capacidad'], $excluidas))),
            'registros_excluidos' => count($excluidas),
        ];

        return response()->json([
            'data' => $filas,
            'excluidas' => $excluidas,
            'totales' => $totales,
        ]);
    }

    /**
     * Auditoría de brecha AP vs FC:
     * - Base AP: facturas cerradas/pagadas por aplicación de pagos.
     * - Base FC: facturas con comisión generada en facturas_comision.
     *
     * Retorna facturas pagadas con brecha:
     * - sin_comision: no existe FC activa para la factura.
     * - desfase_mes: existe FC activa pero no en el mes del pago/cierre AP.
     */
    public function reporteBrechaApFc(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $tipoBrecha = trim((string) $request->input('tipo_brecha', 'all')); // all|sin_comision|desfase_mes

        if (!in_array($tipoBrecha, ['all', 'sin_comision', 'desfase_mes'], true)) {
            $tipoBrecha = 'all';
        }

        $cierresAp = DB::table('aplicacion_pagos as ap')
            ->leftJoin('abonos_creditos as ac', function ($join) {
                $join->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                    ->where('ac.estado_abono', '=', 1);
            })
            ->where('ap.estado', 1)
            ->where(function ($q) {
                $q->where('ap.estado_cerrado', 2)
                    ->orWhere('ap.saldo', '<=', 0.0001);
            })
            ->groupBy('ap.factura_id')
            ->selectRaw("ap.factura_id,
                         MAX(COALESCE(DATE(ap.fecha_cierre_factura), DATE(COALESCE(ac.fecha_pago, ac.created_at)))) as fecha_pago_cierre")
            ->havingRaw('fecha_pago_cierre IS NOT NULL')
            ->havingBetween('fecha_pago_cierre', [$fi, $ff])
            ->get();

        if ($cierresAp->isEmpty()) {
            return response()->json([
                'data' => [],
                'totales' => [
                    'facturas_pagadas_ap' => 0,
                    'facturas_con_brecha' => 0,
                    'sin_comision' => 0,
                    'desfase_mes' => 0,
                ],
                'meta' => [
                    'fecha_inicio' => $fi,
                    'fecha_fin' => $ff,
                    'tipo_brecha' => $tipoBrecha,
                ],
            ]);
        }

        $cierresPorFactura = $cierresAp->keyBy('factura_id');
        $facturaIds = $cierresAp->pluck('factura_id')->map(fn($v) => (int) $v)->values()->all();

        $facturasInfo = DB::table('factura as f')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->leftJoin('users as uf', 'uf.id', '=', 'f.users_id')
            ->leftJoin('users as uv', 'uv.id', '=', 'f.vendedor')
            ->leftJoin('users as ug', 'ug.id', '=', 'f.gestor_entrega')
            ->whereIn('f.id', $facturaIds)
            ->selectRaw("f.id,
                         f.cai,
                         COALESCE(f.sub_total, 0) as sub_total_factura,
                         cl.nombre as cliente,
                         uf.name as facturador,
                         uv.name as vendedor,
                         ug.name as gestor_entrega")
            ->get()
            ->keyBy('id');

        $fcRows = DB::table('facturas_comision')
            ->where('estado_id', 1)
            ->whereIn('factura_id', $facturaIds)
            ->selectRaw("factura_id,
                         DATE_FORMAT(fecha_cierre_factura, '%Y-%m-01') as mes_fc,
                         COUNT(*) as registros_fc,
                         SUM(monto_rol) as total_comision_fc")
            ->groupBy('factura_id', 'mes_fc')
            ->get();

        $fcByFactura = [];
        foreach ($fcRows as $row) {
            $fid = (int) $row->factura_id;
            if (!isset($fcByFactura[$fid])) {
                $fcByFactura[$fid] = [
                    'meses' => [],
                    'registros' => 0,
                    'total_comision' => 0.0,
                ];
            }

            $fcByFactura[$fid]['meses'][] = (string) $row->mes_fc;
            $fcByFactura[$fid]['registros'] += (int) $row->registros_fc;
            $fcByFactura[$fid]['total_comision'] += (float) $row->total_comision_fc;
        }

        $data = [];
        $sinComision = 0;
        $desfaseMes = 0;

        foreach ($facturaIds as $facturaId) {
            $cierre = $cierresPorFactura->get($facturaId);
            if (!$cierre || empty($cierre->fecha_pago_cierre)) {
                continue;
            }

            $mesAp = Carbon::parse($cierre->fecha_pago_cierre)->startOfMonth()->format('Y-m-01');
            $fcMeta = $fcByFactura[$facturaId] ?? null;
            $tieneFc = !empty($fcMeta) && ($fcMeta['registros'] ?? 0) > 0;
            $tieneFcMismoMes = $tieneFc && in_array($mesAp, $fcMeta['meses'], true);

            if ($tieneFcMismoMes) {
                continue;
            }

            $tipo = $tieneFc ? 'desfase_mes' : 'sin_comision';

            if ($tipoBrecha !== 'all' && $tipoBrecha !== $tipo) {
                continue;
            }

            if ($tipo === 'sin_comision') {
                $sinComision++;
            } else {
                $desfaseMes++;
            }

            $info = $facturasInfo->get($facturaId);

            $data[] = [
                'factura_id' => (int) $facturaId,
                'factura' => (string) ($info->cai ?? ('#' . $facturaId)),
                'fecha_pago_cierre_ap' => (string) $cierre->fecha_pago_cierre,
                'mes_ap' => $mesAp,
                'tipo_brecha' => $tipo,
                'cliente' => (string) ($info->cliente ?? 'N/A'),
                'facturador' => (string) ($info->facturador ?? 'N/A'),
                'vendedor' => (string) ($info->vendedor ?? 'N/A'),
                'gestor_entrega' => (string) ($info->gestor_entrega ?? 'N/A'),
                'sub_total_factura' => round((float) ($info->sub_total_factura ?? 0), 4),
                'fc_registros' => (int) ($fcMeta['registros'] ?? 0),
                'fc_meses' => !empty($fcMeta['meses']) ? array_values(array_unique($fcMeta['meses'])) : [],
                'fc_total_comision' => round((float) ($fcMeta['total_comision'] ?? 0), 4),
            ];
        }

        $data = collect($data)
            ->sortBy([
                ['fecha_pago_cierre_ap', 'asc'],
                ['factura', 'asc'],
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'totales' => [
                'facturas_pagadas_ap' => count($facturaIds),
                'facturas_con_brecha' => count($data),
                'sin_comision' => $sinComision,
                'desfase_mes' => $desfaseMes,
            ],
            'meta' => [
                'fecha_inicio' => $fi,
                'fecha_fin' => $ff,
                'tipo_brecha' => $tipoBrecha,
            ],
        ]);
    }

    /**
     * Reprocesa facturas marcadas como sin_comision (AP cerrada/pagada sin FC activa).
     */
    public function reprocesarBrechaApFc(Request $request)
    {
        $facturaIds = collect($request->input('factura_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($facturaIds)) {
            return response()->json([
                'error' => 'Debe enviar al menos una factura para reprocesar.'
            ], 422);
        }

        $generador = app(GeneradorFacturasComision::class);
        $aplicadorRetencion = app(AplicadorRetencionesMora::class);
        $procesador = app(ProcesadorComisiones::class);

        $resultados = [];
        $creadas = 0;
        $omitidas = 0;
        $errores = 0;

        foreach ($facturaIds as $facturaId) {
            DB::beginTransaction();
            try {
                // Si ya existe comisión activa, ya no es sin_comision; omitir.
                if (DB::table('facturas_comision')->where('factura_id', $facturaId)->where('estado_id', 1)->exists()) {
                    $omitidas++;
                    $resultados[] = [
                        'factura_id' => $facturaId,
                        'estado' => 'omitida',
                        'motivo' => 'La factura ya tiene comisión activa.',
                    ];
                    DB::commit();
                    continue;
                }

                $apCierre = DB::table('aplicacion_pagos as ap')
                    ->leftJoin('abonos_creditos as ac', function ($join) {
                        $join->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                            ->where('ac.estado_abono', '=', 1);
                    })
                    ->where('ap.estado', 1)
                    ->where('ap.factura_id', $facturaId)
                    ->where(function ($q) {
                        $q->where('ap.estado_cerrado', 2)
                            ->orWhere('ap.saldo', '<=', 0.0001);
                    })
                    ->groupBy('ap.id', 'ap.factura_id', 'ap.fecha_cierre_factura')
                    ->selectRaw("ap.id as aplicacion_pagos_id,
                                 COALESCE(DATE(ap.fecha_cierre_factura), MAX(DATE(COALESCE(ac.fecha_pago, ac.created_at)))) as fecha_pago_cierre")
                    ->havingRaw('fecha_pago_cierre IS NOT NULL')
                    ->orderByDesc('fecha_pago_cierre')
                    ->first();

                if (!$apCierre) {
                    $omitidas++;
                    $resultados[] = [
                        'factura_id' => $facturaId,
                        'estado' => 'omitida',
                        'motivo' => 'No se encontró cierre/pago válido en aplicación de pagos.',
                    ];
                    DB::commit();
                    continue;
                }

                $arrayFacturasComision = $generador->generar(
                    $facturaId,
                    (int) $apCierre->aplicacion_pagos_id,
                    (string) $apCierre->fecha_pago_cierre
                );

                if (empty($arrayFacturasComision)) {
                    $omitidas++;
                    $resultados[] = [
                        'factura_id' => $facturaId,
                        'estado' => 'omitida',
                        'motivo' => 'No fue posible generar comisión (escala/configuración no aplicable).',
                    ];
                    DB::commit();
                    continue;
                }

                $arrayFacturasComision = $aplicadorRetencion->aplicar($arrayFacturasComision, $facturaId);

                $montoTotal = 0.0;
                foreach ($arrayFacturasComision as $factura) {
                    $montoTotal += (float) ($factura['monto_rol'] ?? 0);
                    $procesador->procesar($factura);
                }

                $creadas++;
                $resultados[] = [
                    'factura_id' => $facturaId,
                    'estado' => 'creada',
                    'motivo' => 'Comisión generada y procesada correctamente.',
                    'registros_fc' => count($arrayFacturasComision),
                    'monto_total' => round($montoTotal, 4),
                ];

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $errores++;
                $resultados[] = [
                    'factura_id' => $facturaId,
                    'estado' => 'error',
                    'motivo' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'resultados' => $resultados,
            'totales' => [
                'solicitadas' => count($facturaIds),
                'creadas' => $creadas,
                'omitidas' => $omitidas,
                'errores' => $errores,
            ],
        ]);
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

    private function obtenerRolesComisionablesActivos(): array
    {
        return DB::table('rol as r')
            ->join('comision_escala as ce', function ($join) {
                $join->on('ce.rol_id', '=', 'r.id')
                    ->where('ce.estado_id', 1);
            })
            ->leftJoin('comision_rol_config as crc', 'crc.rol_id', '=', 'r.id')
            ->where('r.estado_id', 1)
            ->where(function ($q) {
                $q->whereNull('crc.calcular')
                    ->orWhere('crc.calcular', 1);
            })
            ->distinct()
            ->pluck('r.id')
            ->map(fn($v) => (int) $v)
            ->values()
            ->all();
    }

    private function construirFilasRevisionFacturas(string $fi, string $ff, int $usuarioId, int $rolIdFiltro): array
    {
        $rolesComisionables = $this->obtenerRolesComisionablesActivos();
        $rolesComisionablesSet = array_flip($rolesComisionables);

        $apRows = DB::table('aplicacion_pagos as ap')
            ->leftJoin('abonos_creditos as ac', function ($join) {
                $join->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                    ->where('ac.estado_abono', '=', 1);
            })
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', 2)
            ->groupBy('ap.id', 'ap.factura_id', 'ap.estado_cerrado', 'ap.saldo', 'ap.fecha_cierre_factura')
            ->selectRaw("ap.id as aplicacion_pagos_id,
                         ap.factura_id,
                         ap.estado_cerrado,
                         COALESCE(ap.saldo, 0) as saldo,
                         COALESCE(DATE(ap.fecha_cierre_factura), MAX(DATE(COALESCE(ac.fecha_pago, ac.created_at)))) as fecha_pago_revision,
                         COALESCE(SUM(ac.monto_abonado), 0) as monto_abonado_total,
                         COUNT(ac.id) as cantidad_abonos,
                         MAX(DATE(COALESCE(ac.fecha_pago, ac.created_at))) as fecha_ultimo_abono")
            ->havingRaw('fecha_pago_revision IS NOT NULL')
            ->havingBetween('fecha_pago_revision', [$fi, $ff])
            ->get();

        if ($apRows->isEmpty()) {
            return [];
        }

        $facturaIds = $apRows->pluck('factura_id')->map(fn($v) => (int) $v)->unique()->values()->all();

        $facturas = DB::table('factura as f')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->leftJoin('users as uf', 'uf.id', '=', 'f.users_id')
            ->leftJoin('users as uv', 'uv.id', '=', 'f.vendedor')
            ->leftJoin('users as ug', 'ug.id', '=', 'f.gestor_entrega')
            ->leftJoin('cliente_categoria_escala as cce', 'cce.id', '=', 'cl.cliente_categoria_escala_id')
            ->whereIn('f.id', $facturaIds)
            ->selectRaw("f.id,
                         f.cai,
                         COALESCE(f.sub_total, 0) as sub_total,
                         COALESCE(f.total, 0) as total_factura,
                         DATE_FORMAT(f.created_at, '%Y-%m-%d %H:%i:%s') as fecha_creacion_factura,
                         f.users_id as facturador_id,
                         uf.name as facturador,
                         f.vendedor as vendedor_id,
                         uv.name as vendedor,
                         f.gestor_entrega as gestor_id,
                         ug.name as gestor_entrega,
                         cl.nombre as cliente,
                         cl.cliente_categoria_escala_id,
                         cce.nombre_categoria as escala_cliente")
            ->get()
            ->keyBy('id');

        $rolesNombre = DB::table('rol')
            ->whereIn('id', $rolesComisionables)
            ->pluck('nombre', 'id')
            ->mapWithKeys(fn($v, $k) => [(int) $k => (string) $v])
            ->all();

        $rows = [];

        foreach ($apRows as $ap) {
            $facturaId = (int) $ap->factura_id;
            $factura = $facturas->get($facturaId);
            if (!$factura) {
                continue;
            }

            $targets = [
                [
                    'capacidad' => 'TELEASESOR',
                    'rol_id' => 3,
                    'user_id' => (int) ($factura->facturador_id ?? 0),
                    'usuario' => (string) ($factura->facturador ?? ''),
                ],
                [
                    'capacidad' => 'ASESOR',
                    'rol_id' => 2,
                    'user_id' => (int) ($factura->vendedor_id ?? 0),
                    'usuario' => (string) ($factura->vendedor ?? ''),
                ],
                [
                    'capacidad' => 'GESTOR_ENTREGA',
                    'rol_id' => 16,
                    'user_id' => (int) ($factura->gestor_id ?? 0),
                    'usuario' => (string) ($factura->gestor_entrega ?? ''),
                ],
            ];

            foreach ($targets as $target) {
                $targetRolId = (int) $target['rol_id'];
                $targetUserId = (int) $target['user_id'];

                if ($targetUserId <= 0) {
                    continue;
                }

                if (!isset($rolesComisionablesSet[$targetRolId])) {
                    continue;
                }

                if ($rolIdFiltro > 0 && $targetRolId !== $rolIdFiltro) {
                    continue;
                }

                if ($usuarioId > 0 && $targetUserId !== $usuarioId) {
                    continue;
                }

                $rows[] = [
                    'aplicacion_pagos_id' => (int) $ap->aplicacion_pagos_id,
                    'factura_id' => $facturaId,
                    'factura' => (string) ($factura->cai ?? ('#' . $facturaId)),
                    'fecha_pago_revision' => (string) ($ap->fecha_pago_revision ?? ''),
                    'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                    'estado_cerrado' => (int) ($ap->estado_cerrado ?? 0),
                    'saldo' => round((float) ($ap->saldo ?? 0), 4),
                    'monto_abonado_total' => round((float) ($ap->monto_abonado_total ?? 0), 4),
                    'cantidad_abonos' => (int) ($ap->cantidad_abonos ?? 0),
                    'fecha_ultimo_abono' => (string) ($ap->fecha_ultimo_abono ?? ''),
                    'cliente' => (string) ($factura->cliente ?? 'N/A'),
                    'escala_cliente' => (string) ($factura->escala_cliente ?? 'N/A'),
                    'facturador' => (string) ($factura->facturador ?? 'N/A'),
                    'vendedor' => (string) ($factura->vendedor ?? 'N/A'),
                    'gestor_entrega' => (string) ($factura->gestor_entrega ?? 'N/A'),
                    'capacidad' => (string) $target['capacidad'],
                    'rol_id' => $targetRolId,
                    'rol_nombre' => (string) ($rolesNombre[$targetRolId] ?? ('Rol #' . $targetRolId)),
                    'usuario_id' => $targetUserId,
                    'usuario' => (string) ($target['usuario'] ?: ('Usuario #' . $targetUserId)),
                    'sub_total_factura' => round((float) ($factura->sub_total ?? 0), 4),
                    'total_factura' => round((float) ($factura->total_factura ?? 0), 4),
                    'cliente_categoria_escala_id' => (int) ($factura->cliente_categoria_escala_id ?? 0),
                ];
            }
        }

        return collect($rows)
            ->sortBy([
                ['fecha_pago_revision', 'asc'],
                ['factura', 'asc'],
                ['capacidad', 'asc'],
            ])
            ->values()
            ->all();
    }

    public function reporteRevisionFacturasFactura(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $usuarioId = (int) $request->input('usuario_id', 0);
        $rolIdFiltro = (int) $request->input('rol_id', 0);

        $rows = $this->construirFilasRevisionFacturas($fi, $ff, $usuarioId, $rolIdFiltro);

        $totales = [
            'facturas' => count(array_unique(array_map(fn($r) => (int) $r['factura_id'], $rows))),
            'registros' => count($rows),
            'monto_abonado_total' => round(array_sum(array_map(fn($r) => (float) $r['monto_abonado_total'], $rows)), 4),
            'sub_total_total' => round(array_sum(array_map(fn($r) => (float) $r['sub_total_factura'], $rows)), 4),
            'total_factura_total' => round(array_sum(array_map(fn($r) => (float) $r['total_factura'], $rows)), 4),
        ];

        return response()->json([
            'data' => $rows,
            'totales' => $totales,
            'meta' => [
                'fecha_inicio' => $fi,
                'fecha_fin' => $ff,
            ],
        ]);
    }

    public function reporteRevisionFacturasProductos(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $usuarioId = (int) $request->input('usuario_id', 0);
        $rolIdFiltro = (int) $request->input('rol_id', 0);

        $filasFactura = $this->construirFilasRevisionFacturas($fi, $ff, $usuarioId, $rolIdFiltro);

        if (empty($filasFactura)) {
            return response()->json([
                'data' => [],
                'totales' => [
                    'facturas' => 0,
                    'registros' => 0,
                    'cantidad_total' => 0,
                    'base_unitaria_total' => 0,
                    'base_precio_seleccionado_total' => 0,
                ],
                'meta' => [
                    'fecha_inicio' => $fi,
                    'fecha_fin' => $ff,
                ],
            ]);
        }

        $facturaIds = array_values(array_unique(array_map(fn($r) => (int) $r['factura_id'], $filasFactura)));

        $lineasFactura = DB::table('venta_has_producto as vhp')
            ->leftJoin('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'vhp.precios_producto_carga_id')
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->whereIn('vhp.factura_id', $facturaIds)
            ->selectRaw("vhp.factura_id,
                         vhp.producto_id,
                         p.nombre as producto,
                         COALESCE(vhp.cantidad, 0) as cantidad,
                         COALESCE(vhp.precio_unidad, 0) as precio_unidad,
                         COALESCE(vhp.precioSeleccionado, 0) as precio_seleccionado,
                         vhp.precios_producto_carga_id,
                         ppc.categoria_precios_id,
                         cp.nombre as categoria_precio")
            ->get()
            ->groupBy('factura_id');

        $clienteEscalaIds = collect($filasFactura)
            ->pluck('cliente_categoria_escala_id')
            ->filter(fn($v) => (int) $v > 0)
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        $categoriaIds = $lineasFactura
            ->flatten(1)
            ->pluck('categoria_precios_id')
            ->filter(fn($v) => (int) $v > 0)
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        $escalaMap = [];
        if (!empty($clienteEscalaIds) && !empty($categoriaIds)) {
            $escalaRows = DB::table('comision_escala')
                ->where('estado_id', 1)
                ->whereIn('cliente_categoria_escala_id', $clienteEscalaIds)
                ->whereIn('categoria_precios_id', $categoriaIds)
                ->select('rol_id', 'cliente_categoria_escala_id', 'categoria_precios_id', 'porcentaje_comision')
                ->get();

            foreach ($escalaRows as $esc) {
                $key = (int) $esc->rol_id . '|' . (int) $esc->cliente_categoria_escala_id . '|' . (int) $esc->categoria_precios_id;
                $escalaMap[$key] = (float) $esc->porcentaje_comision;
            }
        }

        $rows = [];
        foreach ($filasFactura as $filaFactura) {
            $facturaId = (int) $filaFactura['factura_id'];
            $lineas = collect($lineasFactura->get($facturaId, collect([])))->values();

            foreach ($lineas as $linea) {
                $cantidad = (float) ($linea->cantidad ?? 0);
                $precioUnidad = (float) ($linea->precio_unidad ?? 0);
                $precioSeleccionado = (float) ($linea->precio_seleccionado ?? 0);
                $precioParaBase = $precioSeleccionado > 0 ? $precioSeleccionado : $precioUnidad;

                $baseUnitaria = round($cantidad * $precioUnidad, 4);
                $basePrecioSeleccionado = round($cantidad * $precioParaBase, 4);

                $categoriaPrecioId = (int) ($linea->categoria_precios_id ?? 0);
                $escalaKey = (int) $filaFactura['rol_id'] . '|' . (int) $filaFactura['cliente_categoria_escala_id'] . '|' . $categoriaPrecioId;
                $porcentaje = isset($escalaMap[$escalaKey]) ? (float) $escalaMap[$escalaKey] : null;
                $comisionProyectada = $porcentaje !== null
                    ? round($basePrecioSeleccionado * ($porcentaje / 100), 4)
                    : null;

                $rows[] = [
                    'aplicacion_pagos_id' => (int) $filaFactura['aplicacion_pagos_id'],
                    'factura_id' => $facturaId,
                    'factura' => (string) $filaFactura['factura'],
                    'fecha_pago_revision' => (string) $filaFactura['fecha_pago_revision'],
                    'cliente' => (string) $filaFactura['cliente'],
                    'escala_cliente' => (string) $filaFactura['escala_cliente'],
                    'capacidad' => (string) $filaFactura['capacidad'],
                    'rol_id' => (int) $filaFactura['rol_id'],
                    'rol_nombre' => (string) $filaFactura['rol_nombre'],
                    'usuario_id' => (int) $filaFactura['usuario_id'],
                    'usuario' => (string) $filaFactura['usuario'],
                    'producto_id' => (int) ($linea->producto_id ?? 0),
                    'producto' => (string) ($linea->producto ?? ('Producto #' . (int) ($linea->producto_id ?? 0))),
                    'cantidad' => round($cantidad, 4),
                    'precio_unidad' => round($precioUnidad, 4),
                    'precio_seleccionado' => round($precioSeleccionado, 4),
                    'categoria_precio' => (string) ($linea->categoria_precio ?? 'N/A'),
                    'base_unitaria' => $baseUnitaria,
                    'base_precio_seleccionado' => $basePrecioSeleccionado,
                    'porcentaje_comision' => $porcentaje,
                    'comision_proyectada' => $comisionProyectada,
                ];
            }
        }

        $rows = collect($rows)
            ->sortBy([
                ['fecha_pago_revision', 'asc'],
                ['factura', 'asc'],
                ['producto', 'asc'],
            ])
            ->values()
            ->all();

        $totales = [
            'facturas' => count(array_unique(array_map(fn($r) => (int) $r['factura_id'], $rows))),
            'registros' => count($rows),
            'cantidad_total' => round(array_sum(array_map(fn($r) => (float) $r['cantidad'], $rows)), 4),
            'base_unitaria_total' => round(array_sum(array_map(fn($r) => (float) $r['base_unitaria'], $rows)), 4),
            'base_precio_seleccionado_total' => round(array_sum(array_map(fn($r) => (float) $r['base_precio_seleccionado'], $rows)), 4),
        ];

        return response()->json([
            'data' => $rows,
            'totales' => $totales,
            'meta' => [
                'fecha_inicio' => $fi,
                'fecha_fin' => $ff,
            ],
        ]);
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
