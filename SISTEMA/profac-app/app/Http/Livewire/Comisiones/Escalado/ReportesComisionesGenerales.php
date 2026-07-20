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
use App\Exports\ProyeccionComisionesExport;
use App\Exports\Comisiones\ProyeccionNominaSheet;
use App\Models\Comisiones\ModelComisionPeriodo;
use App\Services\Comisiones\GeneradorFacturasComision;
use App\Services\Comisiones\AplicadorRetencionesMora;
use App\Services\Comisiones\ProcesadorComisiones;

class ReportesComisionesGenerales extends Component
{
    private function totalNominaComisionPorRango(string $fechaInicio, string $fechaFin, int $usuarioId = 0, int $rolId = 0): float
    {
        $fi = $fechaInicio . ' 00:00:00';
        $ff = $fechaFin . ' 23:59:59';

        $query = DB::table('facturas_comision as fc')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->whereBetween('fc.fecha_cierre_factura', [$fi, $ff])
            ->where('fc.estado_id', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('aplicacion_pagos as ap')
                    ->whereColumn('ap.id', 'fc.aplicacion_pagos_id')
                    ->where('ap.estado', 1)
                    ->where('ap.estado_cerrado', 2)
                    ->where('ap.saldo', '<=', 0.0001);
            })
            ->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END IS NOT NULL"
            )
            ->when($usuarioId > 0, function ($q) use ($usuarioId) {
                $q->whereRaw(
                    "CASE fc.tipo_comision
                        WHEN 1 THEN f.users_id
                        WHEN 2 THEN f.users_id
                        WHEN 3 THEN f.vendedor
                        WHEN 4 THEN f.gestor_entrega
                        ELSE NULL
                    END = ?",
                    [$usuarioId]
                );
            })
            ->when($rolId > 0, function ($q) use ($rolId) {
                $q->where('fc.rol_id', $rolId);
            });

        return (float) $query->sum('fc.monto_rol');
    }

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
        $search = trim((string) $request->input('q', ''));

        $empleados = DB::table('users as u')
            ->leftJoin('rol as r', 'r.id', '=', 'u.rol_id')
            ->select('u.id', 'u.name')
            ->where('u.estado_id', 1)
            ->where(function ($q) {
                $q->whereNull('u.rol_id')
                    ->orWhere('r.estado_id', 1);
            })
            ->orderBy('u.name');

        if ($search !== '') {
            $empleados->where('u.name', 'LIKE', "%{$search}%");
        }

        return response()->json(
            $empleados
                ->distinct()
                ->limit(50)
                ->get()
        );
    }

    /**
     * Lista todos los usuarios (activos e inactivos) con su rol y estado,
     * para el selector de proyecciones donde se necesita visibilidad completa.
     */
    public function listarEmpleadosTodos(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $query = DB::table('users as u')
            ->leftJoin('rol as r', 'r.id', '=', 'u.rol_id')
            ->selectRaw("u.id,
                         u.name,
                         COALESCE(r.nombre, 'Sin rol') AS rol_nombre,
                         CASE WHEN u.estado_id = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado_label,
                         u.estado_id")
            ->orderByRaw("u.estado_id ASC, u.name ASC");

        if ($search !== '') {
            $query->where('u.name', 'LIKE', "%{$search}%");
        }

        return response()->json($query->limit(100)->get());
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
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('aplicacion_pagos as ap')
                    ->whereColumn('ap.id', 'fc.aplicacion_pagos_id')
                    ->where('ap.estado', 1)
                    ->where('ap.estado_cerrado', 2)
                    ->where('ap.saldo', '<=', 0.0001);
            })
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
            ->where('fc.estado_id', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('aplicacion_pagos as ap')
                    ->whereColumn('ap.id', 'fc.aplicacion_pagos_id')
                    ->where('ap.estado', 1)
                    ->where('ap.estado_cerrado', 2)
                    ->where('ap.saldo', '<=', 0.0001);
            })
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
        $totalNomina = round($this->totalNominaComisionPorRango($fi, $ff, $usuarioId, $rolIdFiltro), 4);

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
                         COALESCE(MAX(DATE(ac.fecha_pago)), DATE(ap.fecha_cierre_factura)) as fecha_pago_cierre")
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
                    'comision_proyectada_total' => $totalNomina,
                    'comision_recalculada_total' => 0,
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
            ->leftJoin('tipo_factura as tf', 'tf.id', '=', 'f.tipo_factura_id')
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
                         cce.nombre_categoria as escala_cliente,
                         f.tipo_pago_id,
                         DATE(f.fecha_vencimiento) as fecha_vencimiento,
                         tf.codigo as tipo_factura_codigo")
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
                    'comision_proyectada_total' => $totalNomina,
                    'comision_recalculada_total' => 0,
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
            ->groupBy('vhp.factura_id', 'vhp.producto_id', 'vhp.precios_producto_carga_id', 'vhp.precio_unidad', 'vhp.precioSeleccionado', 'ppc.categoria_precios_id', 'p.nombre', 'cp.nombre')
            ->selectRaw("vhp.factura_id,
                         vhp.producto_id,
                         p.nombre as producto,
                         COALESCE(SUM(vhp.cantidad_s), SUM(vhp.cantidad)) as cantidad,
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

        // ── Mapas para regla SR ────────────────────────────────────────────
        // Para facturas SR: la categoría más baja solo aplica si precioSeleccionado
        // es estrictamente mayor al precio_a de esa categoría para el producto.
        $tiposSR = ['sin_restriccion_gobierno', 'sin_restriccion_precio'];

        // escala_id → categoria_precio_mas_baja_id
        $escalaMasBajaMap = [];
        $escalaIdsConSR = $facturas
            ->filter(fn($f) => in_array((string) ($f->tipo_factura_codigo ?? ''), $tiposSR, true))
            ->pluck('cliente_categoria_escala_id')
            ->filter(fn($v) => (int) $v > 0)
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        if (!empty($escalaIdsConSR)) {
            $catMasBajas = DB::table('categoria_precios')
                ->whereIn('cliente_categoria_escala_id', $escalaIdsConSR)
                ->where('estado_id', 1)
                ->orderByRaw('CASE WHEN porc_precio_a IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('porc_precio_a', 'asc')
                ->orderBy('id', 'asc')
                ->get(['id', 'cliente_categoria_escala_id']);

            foreach ($catMasBajas as $row) {
                $eid = (int) $row->cliente_categoria_escala_id;
                if (!isset($escalaMasBajaMap[$eid])) {
                    $escalaMasBajaMap[$eid] = (int) $row->id;
                }
            }
        }

        // (producto_id, categoria_forzada_id) → precio_a de referencia
        $precioRefSRMap = [];
        if (!empty($escalaMasBajaMap)) {
            $catForzadasIds = array_unique(array_values($escalaMasBajaMap));
            $productoIdsSR  = $lineasFactura->flatten(1)
                ->pluck('producto_id')
                ->filter(fn($v) => (int) $v > 0)
                ->map(fn($v) => (int) $v)
                ->unique()
                ->values()
                ->all();

            if (!empty($productoIdsSR)) {
                $refs = DB::table('precios_producto_carga')
                    ->whereIn('categoria_precios_id', $catForzadasIds)
                    ->whereIn('producto_id', $productoIdsSR)
                    ->select('categoria_precios_id', 'producto_id', 'precio_a')
                    ->get();

                foreach ($refs as $ref) {
                    $precioRefSRMap[(int) $ref->producto_id . '_' . (int) $ref->categoria_precios_id] = (float) $ref->precio_a;
                }
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // Cargar configuración de días de gracia para crédito (1% por período)
        $diasGraciaMap = DB::table('dias_gracia_comision')
            ->whereIn('rol_id', [2, 3, 16])
            ->where('tipo_factura', 'credito')
            ->where('dias_gracia', '>', 0)
            ->get()
            ->keyBy('rol_id');

        $filas = [];
        $excluidas = [];
        $facturasProyectadas = [];
        $facturasExcluidas = [];

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

                if ($lineas->isEmpty()) {
                    $excluidas[] = [
                        'factura_id' => $facturaId,
                        'factura' => (string) ($factura->cai ?? ('#' . $facturaId)),
                        'fecha_pago' => (string) ($cierre->fecha_pago_cierre ?? ''),
                        'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                        'cliente' => (string) ($factura->cliente ?? 'N/A'),
                        'producto' => 'Sin producto',
                        'categoria_precio' => 'N/A',
                        'capacidad' => (string) $target['capacidad'],
                        'rol_nombre' => (string) $target['rol_nombre'],
                        'usuario_id' => (int) $target['user_id'],
                        'usuario' => (string) ($target['usuario'] ?: ('Usuario #' . (int) $target['user_id'])),
                        'razon_no_comisionable' => 'Factura sin lineas de productos para comision',
                        'motivos' => ['Factura sin lineas de productos para comision'],
                        'cantidad' => 0.0,
                        'precio_unidad' => 0.0,
                        'precio_seleccionado' => 0.0,
                        'base_unitaria' => 0.0,
                        'base_comisionable' => 0.0,
                        'porcentaje_promedio' => 0.0,
                        'comision_proyectada' => 0.0,
                        'detalle_lineas' => [],
                    ];
                    $facturasExcluidas[$facturaId] = true;
                    continue;
                }

                foreach ($lineas as $linea) {
                    $cantidad = (float) ($linea->cantidad ?? 0);
                    $precioUnitario = (float) ($linea->precio_unidad ?? 0);
                    $precioSeleccionado = (float) ($linea->precioSeleccionado ?? 0);
                    $precioParaComision = $precioSeleccionado > 0 ? $precioSeleccionado : $precioUnitario;

                    $baseUnitaria = round($cantidad * $precioUnitario, 4);
                    $baseComisionable = round($cantidad * $precioParaComision, 4);
                    $categoriaPrecioId = (int) ($linea->categoria_precios_id ?? 0);

                    // ── Regla SR: solo forzar categoría más baja si precio vendido > precio_a de esa categoría ──
                    $esSR = in_array((string) ($factura->tipo_factura_codigo ?? ''), $tiposSR, true);
                    $escalaId = (int) ($factura->cliente_categoria_escala_id ?? 0);
                    if ($esSR && $escalaId > 0 && isset($escalaMasBajaMap[$escalaId])) {
                        $catForzada = $escalaMasBajaMap[$escalaId];
                        $productoId = (int) ($linea->producto_id ?? 0);
                        $precioRefKey = $productoId . '_' . $catForzada;
                        $precioRef = $precioRefSRMap[$precioRefKey] ?? null;
                        // Penalizar con categoría más baja SOLO si vendió por DEBAJO del precio de esa categoría.
                        if ($precioRef !== null && $precioParaComision < $precioRef) {
                            $categoriaPrecioId = $catForzada;
                        }
                        // Si vendió igual o por encima, comisiona por la categoría real
                    }
                    // ────────────────────────────────────────────────────────────────────────────

                    $categoriaPrecioNombre = (string) ($linea->categoria_precio ?? ('Categoria #' . $categoriaPrecioId));
                    $productoNombre = (string) ($linea->producto ?? ('Producto #' . (int) $linea->producto_id));

                    $motivos = [];
                    if (empty($linea->precios_producto_carga_id)) {
                        $motivos[] = 'Linea sin precios_producto_carga_id';
                    }
                    if ($categoriaPrecioId <= 0) {
                        $motivos[] = 'Linea sin categoria de precio (categoria_precios_id)';
                    }
                    if ((int) ($factura->cliente_categoria_escala_id ?? 0) <= 0) {
                        $motivos[] = 'Cliente sin categoria de escala configurada';
                    }

                    $escalaKey = $categoriaPrecioId > 0
                        ? ((int) $target['rol_id'] . '|' . (int) $factura->cliente_categoria_escala_id . '|' . $categoriaPrecioId)
                        : null;

                    if ($escalaKey && !isset($escalaMap[$escalaKey])) {
                        $motivos[] = 'Sin escala activa para rol/categoria cliente/categoria precio';
                    }

                    if (!empty($motivos)) {
                        $excluidas[] = [
                            'factura_id' => $facturaId,
                            'factura' => (string) ($factura->cai ?? ('#' . $facturaId)),
                            'fecha_pago' => (string) ($cierre->fecha_pago_cierre ?? ''),
                            'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                            'cliente' => (string) ($factura->cliente ?? 'N/A'),
                            'producto' => $productoNombre,
                            'categoria_precio' => $categoriaPrecioNombre,
                            'capacidad' => (string) $target['capacidad'],
                            'rol_nombre' => (string) $target['rol_nombre'],
                            'usuario_id' => (int) $target['user_id'],
                            'usuario' => (string) ($target['usuario'] ?: ('Usuario #' . (int) $target['user_id'])),
                            'razon_no_comisionable' => (string) ($motivos[0] ?? 'No aplica para comision'),
                            'motivos' => array_values(array_unique($motivos)),
                            'cantidad' => round($cantidad, 4),
                            'precio_unidad' => round($precioUnitario, 4),
                            'precio_seleccionado' => round($precioSeleccionado, 4),
                            'base_unitaria' => round($baseUnitaria, 4),
                            'base_comisionable' => round($baseComisionable, 4),
                            'porcentaje_promedio' => 0.0,
                            'comision_proyectada' => 0.0,
                            'detalle_lineas' => [
                                [
                                    'producto' => $productoNombre,
                                    'categoria_precio' => $categoriaPrecioNombre,
                                    'cantidad' => $cantidad,
                                    'precio_unitario' => round($precioUnitario, 4),
                                    'precio_para_comision' => round($precioParaComision, 4),
                                    'base_unitaria' => round($baseUnitaria, 4),
                                    'base_comisionable' => round($baseComisionable, 4),
                                    'porcentaje' => 0.0,
                                    'comision_linea' => 0.0,
                                    'fuente_base_comisionable' => $precioSeleccionado > 0
                                        ? 'Cantidad x precioSeleccionado'
                                        : 'Cantidad x precio_unidad (fallback)',
                                ],
                            ],
                        ];
                        $facturasExcluidas[$facturaId] = true;
                        continue;
                    }

                    $porcentaje = (float) ($escalaMap[$escalaKey]->porcentaje_comision ?? 0);
                    $comisionLinea = round($baseComisionable * ($porcentaje / 100), 4);

                    // ── Retención por mora de crédito (1% por período vencido) ──────
                    $retencionMoraAplicada  = 0.0;
                    $periodosVencidosMora   = 0;
                    $diasGracia             = 0;
                    if ($comisionLinea > 0) {
                        $tipoPagoId = (int) ($factura->tipo_pago_id ?? 0);
                        if ($tipoPagoId === 2 && !empty($factura->fecha_vencimiento) && !empty($cierre->fecha_pago_cierre)) {
                            $fechaVenc  = \Carbon\Carbon::parse($factura->fecha_vencimiento)->startOfDay();
                            $fechaPago  = \Carbon\Carbon::parse($cierre->fecha_pago_cierre)->startOfDay();
                            $diasTransc = (int) $fechaVenc->diffInDays($fechaPago, false);
                            $confGracia = $diasGraciaMap->get($target['rol_id']);
                            if ($confGracia && $diasTransc > 0) {
                                $diasGracia = (int) $confGracia->dias_gracia;
                                if ($diasTransc > $diasGracia) {
                                    $pct              = (float) $confGracia->porcentaje_retencion;
                                    $periodosVencidosMora = (int) ceil(($diasTransc - $diasGracia) / $diasGracia);
                                    $montoPorPeriodo  = $comisionLinea * ($pct / 100);
                                    $retencionMoraAplicada = min($comisionLinea, round($periodosVencidosMora * $montoPorPeriodo, 4));
                                    $comisionLinea    = round(max(0, $comisionLinea - $retencionMoraAplicada), 4);
                                }
                            }
                        }
                    }
                    // ─────────────────────────────────────────────────────────────

                    if ($comisionLinea <= 0) {
                        $excluidas[] = [
                            'factura_id' => $facturaId,
                            'factura' => (string) ($factura->cai ?? ('#' . $facturaId)),
                            'fecha_pago' => (string) ($cierre->fecha_pago_cierre ?? ''),
                            'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                            'cliente' => (string) ($factura->cliente ?? 'N/A'),
                            'producto' => $productoNombre,
                            'categoria_precio' => $categoriaPrecioNombre,
                            'capacidad' => (string) $target['capacidad'],
                            'rol_nombre' => (string) $target['rol_nombre'],
                            'usuario_id' => (int) $target['user_id'],
                            'usuario' => (string) ($target['usuario'] ?: ('Usuario #' . (int) $target['user_id'])),
                            'razon_no_comisionable' => 'Comision proyectada igual a 0',
                            'motivos' => ['Comision proyectada igual a 0'],
                            'cantidad' => round($cantidad, 4),
                            'precio_unidad' => round($precioUnitario, 4),
                            'precio_seleccionado' => round($precioSeleccionado, 4),
                            'base_unitaria' => round($baseUnitaria, 4),
                            'base_comisionable' => round($baseComisionable, 4),
                            'porcentaje_promedio' => round($porcentaje, 4),
                            'comision_proyectada' => 0.0,
                            'detalle_lineas' => [
                                [
                                    'producto' => $productoNombre,
                                    'categoria_precio' => $categoriaPrecioNombre,
                                    'cantidad' => $cantidad,
                                    'precio_unitario' => round($precioUnitario, 4),
                                    'precio_para_comision' => round($precioParaComision, 4),
                                    'base_unitaria' => round($baseUnitaria, 4),
                                    'base_comisionable' => round($baseComisionable, 4),
                                    'porcentaje' => round($porcentaje, 4),
                                    'comision_linea' => 0.0,
                                    'fuente_base_comisionable' => $precioSeleccionado > 0
                                        ? 'Cantidad x precioSeleccionado'
                                        : 'Cantidad x precio_unidad (fallback)',
                                ],
                            ],
                        ];
                        $facturasExcluidas[$facturaId] = true;
                        continue;
                    }

                    $filas[] = [
                        'factura_id' => $facturaId,
                        'factura' => (string) ($factura->cai ?? ('#' . $facturaId)),
                        'fecha_pago' => (string) ($cierre->fecha_pago_cierre ?? ''),
                        'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                        'cliente' => (string) ($factura->cliente ?? 'N/A'),
                        'producto' => $productoNombre,
                        'escala_cliente' => (string) ($factura->escala_cliente ?? 'N/A'),
                        'escala_precio_vendida' => $categoriaPrecioNombre,
                        'cantidad' => round($cantidad, 4),
                        'precio_unitario' => round($precioUnitario, 4),
                        'precio_seleccionado' => round($precioSeleccionado, 4),
                        'capacidad' => (string) $target['capacidad'],
                        'rol_id' => (int) $target['rol_id'],
                        'rol_nombre' => (string) $target['rol_nombre'],
                        'usuario_id' => (int) $target['user_id'],
                        'usuario' => (string) ($target['usuario'] ?: ('Usuario #' . (int) $target['user_id'])),
                        'base_comisionable_unitaria' => round($baseUnitaria, 4),
                        'base_comisionable' => round($baseComisionable, 4),
                        'comision_bruta' => round($comisionLinea + $retencionMoraAplicada, 4),
                        'comision_proyectada' => round($comisionLinea, 4),
                        'porcentaje_promedio' => round($porcentaje, 4),
                        'retencion_mora' => round($retencionMoraAplicada, 4),
                        'periodos_vencidos_mora' => $periodosVencidosMora,
                        'dias_gracia_mora' => $diasGracia,
                        'detalle_lineas' => [
                            [
                                'producto' => $productoNombre,
                                'categoria_precio' => $categoriaPrecioNombre,
                                'cantidad' => $cantidad,
                                'precio_unitario' => round($precioUnitario, 4),
                                'precio_para_comision' => round($precioParaComision, 4),
                                'base_unitaria' => round($baseUnitaria, 4),
                                'base_comisionable' => round($baseComisionable, 4),
                                'porcentaje' => round($porcentaje, 4),
                                'comision_linea' => round($comisionLinea, 4),
                                'retencion_mora' => round($retencionMoraAplicada, 4),
                                'fuente_base_comisionable' => $precioSeleccionado > 0
                                    ? 'Cantidad x precioSeleccionado'
                                    : 'Cantidad x precio_unidad (fallback)',
                            ],
                        ],
                    ];

                    $facturasProyectadas[$facturaId] = true;
                }
            }
        }

        $filas = collect($filas)
            ->sortBy([
                ['fecha_pago', 'asc'],
                ['factura', 'asc'],
                ['producto', 'asc'],
                ['escala_precio_vendida', 'asc'],
                ['capacidad', 'asc'],
            ])
            ->values()
            ->all();

        $excluidas = collect($excluidas)
            ->sortBy([
                ['fecha_pago', 'asc'],
                ['factura', 'asc'],
                ['producto', 'asc'],
                ['capacidad', 'asc'],
            ])
            ->values()
            ->all();

        $comisionRecalculadaTotal = round(array_sum(array_map(fn($r) => (float) $r['comision_proyectada'], $filas)), 4);
        $retencionMoraTotal        = round(array_sum(array_map(fn($r) => (float) $r['retencion_mora'], $filas)), 4);
        $comisionBrutaTotal        = round(array_sum(array_map(fn($r) => (float) $r['comision_bruta'], $filas)), 4);

        // Base comisionable y base unitaria deduplicadas:
        // cada línea de producto puede aparecer una vez por rol en $filas,
        // pero la base es la misma → contar cada línea (factura+producto+escala) solo una vez.
        $baseUnitariaUnica     = 0.0;
        $baseComisionableUnica = 0.0;
        $lineasContadas        = [];
        foreach ($filas as $fila) {
            $lineaKey = ($fila['factura_id'] ?? '') . '|' . ($fila['producto'] ?? '') . '|' . ($fila['escala_precio_vendida'] ?? '');
            if (!isset($lineasContadas[$lineaKey])) {
                $lineasContadas[$lineaKey] = true;
                $baseUnitariaUnica     += (float) ($fila['base_comisionable_unitaria'] ?? 0);
                $baseComisionableUnica += (float) ($fila['base_comisionable'] ?? 0);
            }
        }

        $totales = [
            'facturas_proyectadas' => count($facturasProyectadas),
            'registros_proyectados' => count($filas),
            'base_unitaria_total' => round($baseUnitariaUnica, 4),
            'base_comisionable_total' => round($baseComisionableUnica, 4),
            'comision_proyectada_total' => $totalNomina,
            'comision_recalculada_total' => $comisionRecalculadaTotal,
            'retencion_mora_total' => $retencionMoraTotal,
            'comision_bruta_total' => $comisionBrutaTotal,
            'facturas_excluidas' => count($facturasExcluidas),
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
                         COALESCE(MAX(DATE(ac.fecha_pago)), MAX(DATE(ap.fecha_cierre_factura))) as fecha_pago_cierre")
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
                                 COALESCE(MAX(DATE(ac.fecha_pago)), DATE(ap.fecha_cierre_factura)) as fecha_pago_cierre")
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
                         COALESCE(MAX(DATE(ac.fecha_pago)), DATE(ap.fecha_cierre_factura)) as fecha_pago_revision,
                         COALESCE(SUM(ac.monto_abonado), 0) as monto_abonado_total,
                         COUNT(ac.id) as cantidad_abonos,
                         MAX(DATE(ac.fecha_pago)) as fecha_ultimo_abono")
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
            'monto_abonado_total' => round(array_sum(
                array_map(fn($r) => (float) $r['monto_abonado_total'],
                    array_values(array_reduce($rows, function ($carry, $r) {
                        $id = (int) $r['factura_id'];
                        if (!isset($carry[$id])) { $carry[$id] = $r; }
                        return $carry;
                    }, []))
                )
            ), 4),
            'sub_total_total' => round(array_sum(
                array_map(fn($r) => (float) $r['sub_total_factura'],
                    array_values(array_reduce($rows, function ($carry, $r) {
                        $id = (int) $r['factura_id'];
                        if (!isset($carry[$id])) { $carry[$id] = $r; }
                        return $carry;
                    }, []))
                )
            ), 4),
            'total_factura_total' => round(array_sum(
                array_map(fn($r) => (float) $r['total_factura'],
                    array_values(array_reduce($rows, function ($carry, $r) {
                        $id = (int) $r['factura_id'];
                        if (!isset($carry[$id])) { $carry[$id] = $r; }
                        return $carry;
                    }, []))
                )
            ), 4),
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

    public function exportarProyeccionesExcel(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $rows = $request->input('rows', []);
        if (!is_array($rows)) {
            $rows = json_decode($rows, true) ?? [];
        }

        $periodo     = $request->input('periodo', now()->format('d/m/Y'));
        $generadoPor = Auth::user()->name ?? 'Sistema';
        $empresa     = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';

        $response = Excel::download(
            new ProyeccionComisionesExport($rows, $empresa, $periodo, $generadoPor),
            'proyeccion_comisiones_' . now()->format('Ymd_His') . '.xlsx'
        );

        $token = (string) $request->input('download_token', '');
        if ($token !== '') {
            setcookie('proy_excel_token', $token, time() + 300, '/', '', false, false);
        }

        return $response;
    }

    /**
     * Descarga el Excel de nómina de proyección con el formato naranja estilizado.
     * Recibe los mismos filtros que reporteProyecciones y recalcula internamente.
     */
    public function exportarProyeccionesNomina(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        [$fi, $ff] = $this->resolveDateRange($request);
        $usuarioId   = (int) $request->input('usuario_id', 0);
        $rolIdFiltro = (int) $request->input('rol_id', 0);
        $generadoPor = Auth::user()->name ?? 'Sistema';

        // Nombre del empleado
        $empleado = 'Usuario';
        if ($usuarioId > 0) {
            $u = DB::table('users')->where('id', $usuarioId)->value('name');
            if ($u) {
                $empleado = (string) $u;
            }
        }

        // Periodo label
        $periodoLabel = Carbon::parse($fi)->format('d/m/Y') . ' - ' . Carbon::parse($ff)->format('d/m/Y');

        // Reutilizar la lógica de proyecciones
        $resp = $this->reporteProyecciones($request);
        $payload = json_decode($resp->getContent(), true);
        $filas   = $payload['data']    ?? [];
        $totales = $payload['totales'] ?? [];

        $totalFacturas      = (int)   ($totales['facturas_proyectadas']  ?? 0);
        $baseComisionable   = (float) ($totales['base_comisionable_total'] ?? 0);

        // Desglosar comisión por rol (2=Asesor, 3=Teleasesor, 16=Gestor)
        $comisionAsesor     = 0.0;
        $comisionTeleasesor = 0.0;
        $comisionGestor     = 0.0;
        foreach ($filas as $fila) {
            $rolId   = (int)   ($fila['rol_id']           ?? 0);
            $monto   = (float) ($fila['comision_proyectada'] ?? 0);
            if ($rolId === 2)  { $comisionAsesor     += $monto; }
            if ($rolId === 3)  { $comisionTeleasesor += $monto; }
            if ($rolId === 16) { $comisionGestor     += $monto; }
        }
        $comisionAsesor     = round($comisionAsesor, 4);
        $comisionTeleasesor = round($comisionTeleasesor, 4);
        $comisionGestor     = round($comisionGestor, 4);

        // Agrupar facturas únicas por mes de pago
        $facturasPorMes = [];
        $todasFacturaIds = [];
        foreach ($filas as $fila) {
            $facturaId = (int) ($fila['factura_id'] ?? 0);
            $fechaPago = (string) ($fila['fecha_pago'] ?? '');
            if ($facturaId <= 0 || $fechaPago === '') {
                continue;
            }
            $mesKey = Carbon::parse($fechaPago)->format('Y-m');
            if (!isset($facturasPorMes[$mesKey])) {
                $mesLabel = Carbon::parse($fechaPago)->locale('es')->isoFormat('MMMM YYYY');
                $mesLabel = mb_convert_case($mesLabel, MB_CASE_TITLE, 'UTF-8');
                $facturasPorMes[$mesKey] = ['mes_label' => $mesLabel, 'cantidad' => 0, 'total' => 0.0, 'vistas' => []];
            }
            if (!isset($facturasPorMes[$mesKey]['vistas'][$facturaId])) {
                $facturasPorMes[$mesKey]['vistas'][$facturaId] = true;
                $facturasPorMes[$mesKey]['cantidad']++;
                $todasFacturaIds[$facturaId] = $mesKey;
            }
        }

        $uniqueFacturaIds = array_keys($todasFacturaIds);

        // Base comisionable: suma de (cantidad * precioSeleccionado) por factura única,
        // sin duplicar por rol. Fuente: venta_has_producto.
        $baseComisionable = 0.0;
        if (!empty($uniqueFacturaIds)) {
            $baseRows = DB::table('venta_has_producto as vhp')
                ->whereIn('vhp.factura_id', $uniqueFacturaIds)
                ->groupBy('vhp.factura_id', 'vhp.producto_id', 'vhp.precios_producto_carga_id',
                          'vhp.precio_unidad', 'vhp.precioSeleccionado')
                ->selectRaw("vhp.factura_id,
                             COALESCE(SUM(vhp.cantidad_s), SUM(vhp.cantidad)) as cantidad,
                             COALESCE(vhp.precioSeleccionado, vhp.precio_unidad) as precio")
                ->get();
            foreach ($baseRows as $br) {
                $baseComisionable += (float) $br->cantidad * (float) $br->precio;
            }
            $baseComisionable = round($baseComisionable, 2);
        }

        // Total cobrado: directo desde abonos_creditos, igual que Libro de Cobros
        // (mismo filtro: fecha_pago en rango, banco no excluido, estado_abono=1)
        if (!empty($uniqueFacturaIds)) {
            $cobradoRows = DB::table('abonos_creditos as ac')
                ->whereIn('ac.factura_id', $uniqueFacturaIds)
                ->where('ac.estado_abono', 1)
                ->whereNotIn('ac.banco_id', [12, 13])
                ->whereBetween(DB::raw('DATE(ac.fecha_pago)'), [$fi, $ff])
                ->groupBy('ac.factura_id')
                ->selectRaw('ac.factura_id, SUM(ac.monto_abonado) as cobrado')
                ->get()
                ->keyBy('factura_id');

            foreach ($todasFacturaIds as $facturaId => $mesKey) {
                $cobrado = (float) ($cobradoRows->get($facturaId)->cobrado ?? 0);
                $facturasPorMes[$mesKey]['total'] += $cobrado;
            }
        }

        ksort($facturasPorMes);
        $mesesCobrados = array_values(array_map(function ($m) {
            return ['mes_label' => $m['mes_label'], 'cantidad' => $m['cantidad'], 'total' => round($m['total'], 2)];
        }, $facturasPorMes));
        $totalCobrado = round(array_sum(array_column($mesesCobrados, 'total')), 2);

        // ── Política Anterior — IDs y totales ─────────────────────────────
        $basePoliticaAnterior     = (float) $request->input('pol_base_comisionable', 0);
        $comisionPoliticaAnterior = (float) $request->input('pol_total_comision',     0);

        // IDs de facturas política anterior enviados desde el frontend
        $facturasPoliticaIds = collect($request->input('pol_factura_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values()
            ->all();

        // Fallback si no vienen del frontend
        if ($basePoliticaAnterior <= 0 && $comisionPoliticaAnterior <= 0 && $usuarioId > 0) {
            $cierresParaPolitica = DB::table('aplicacion_pagos as ap')
                ->leftJoin('abonos_creditos as ac', function ($j) {
                    $j->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                      ->where('ac.estado_abono', '=', 1);
                })
                ->where('ap.estado', 1)
                ->where('ap.estado_cerrado', 2)
                ->where('ap.saldo', '<=', 0.0001)
                ->groupBy('ap.id', 'ap.factura_id', 'ap.fecha_cierre_factura')
                ->selectRaw("ap.factura_id,
                             COALESCE(MAX(DATE(ac.fecha_pago)), DATE(ap.fecha_cierre_factura)) AS fecha_pago_cierre")
                ->havingRaw('fecha_pago_cierre IS NOT NULL')
                ->havingBetween('fecha_pago_cierre', [$fi, $ff])
                ->get()
                ->pluck('factura_id')
                ->map(fn($v) => (int) $v)
                ->all();

            $enEscalaIds = !empty($cierresParaPolitica)
                ? DB::table('facturas_comision')
                    ->whereIn('factura_id', $cierresParaPolitica)
                    ->where('estado_id', 1)
                    ->distinct()
                    ->pluck('factura_id')
                    ->map(fn($v) => (int) $v)
                    ->flip()
                    ->all()
                : [];

            if (!empty($cierresParaPolitica)) {
                $facturasFiltradas = DB::table('factura')
                    ->whereIn('id', $cierresParaPolitica)
                    ->where(function ($q) use ($usuarioId) {
                        $q->where('users_id', $usuarioId)
                          ->orWhere('vendedor', $usuarioId)
                          ->orWhere('gestor_entrega', $usuarioId);
                    })
                    ->pluck('id')
                    ->map(fn($v) => (int) $v)
                    ->all();
                foreach ($facturasFiltradas as $fid) {
                    if (!isset($enEscalaIds[$fid])) {
                        $facturasPoliticaIds[] = $fid;
                    }
                }
            }

            if (!empty($facturasPoliticaIds)) {
                $lineaPolRows = DB::table('venta_has_producto as vhp')
                    ->whereIn('vhp.factura_id', $facturasPoliticaIds)
                    ->groupBy('vhp.factura_id', 'vhp.producto_id', 'vhp.precios_producto_carga_id',
                              'vhp.precio_unidad', 'vhp.precioSeleccionado')
                    ->selectRaw("COALESCE(SUM(vhp.cantidad_s), SUM(vhp.cantidad)) as cantidad,
                                 COALESCE(vhp.precioSeleccionado, vhp.precio_unidad) as precio")
                    ->get();
                foreach ($lineaPolRows as $lr) {
                    $basePoliticaAnterior += (float) $lr->cantidad * (float) $lr->precio;
                }
                $basePoliticaAnterior = round($basePoliticaAnterior, 2);
            }
        }

        // ── Agregar facturas de política anterior a la sección "Facturas por Mes" ──
        // Solo incluir IDs que no están ya en las facturas de escala
        $facturasPoliticaIds = array_values(array_diff($facturasPoliticaIds, $uniqueFacturaIds));

        if (!empty($facturasPoliticaIds)) {
            // Obtener fecha de pago (desde AP cerrada) para agrupar por mes
            $cierresPol = DB::table('aplicacion_pagos as ap')
                ->leftJoin('abonos_creditos as ac', function ($j) {
                    $j->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                      ->where('ac.estado_abono', '=', 1);
                })
                ->where('ap.estado', 1)
                ->where('ap.estado_cerrado', 2)
                ->whereIn('ap.factura_id', $facturasPoliticaIds)
                ->groupBy('ap.factura_id', 'ap.fecha_cierre_factura')
                ->selectRaw("ap.factura_id,
                             COALESCE(MAX(DATE(ac.fecha_pago)), DATE(ap.fecha_cierre_factura)) AS fecha_pago_cierre")
                ->get()
                ->keyBy('factura_id');

            // Cobrado en el rango para estas facturas (= mismo criterio que Libro de Cobros)
            $cobradoPolRows = DB::table('abonos_creditos as ac')
                ->whereIn('ac.factura_id', $facturasPoliticaIds)
                ->where('ac.estado_abono', 1)
                ->whereNotIn('ac.banco_id', [12, 13])
                ->whereBetween(DB::raw('DATE(ac.fecha_pago)'), [$fi, $ff])
                ->groupBy('ac.factura_id')
                ->selectRaw('ac.factura_id, SUM(ac.monto_abonado) as cobrado')
                ->get()
                ->keyBy('factura_id');

            foreach ($facturasPoliticaIds as $fid) {
                $cierreFila = $cierresPol->get($fid);
                $fechaPago  = $cierreFila ? (string) $cierreFila->fecha_pago_cierre : null;
                if (!$fechaPago) continue;

                $mesKey = Carbon::parse($fechaPago)->format('Y-m');
                if (!isset($facturasPorMes[$mesKey])) {
                    $mesLabel = Carbon::parse($fechaPago)->locale('es')->isoFormat('MMMM YYYY');
                    $mesLabel = mb_convert_case($mesLabel, MB_CASE_TITLE, 'UTF-8');
                    $facturasPorMes[$mesKey] = ['mes_label' => $mesLabel, 'cantidad' => 0, 'total' => 0.0, 'vistas' => []];
                }
                if (!isset($facturasPorMes[$mesKey]['vistas'][$fid])) {
                    $facturasPorMes[$mesKey]['vistas'][$fid] = true;
                    $facturasPorMes[$mesKey]['cantidad']++;
                    $cobrado = (float) ($cobradoPolRows->get($fid)->cobrado ?? 0);
                    $facturasPorMes[$mesKey]['total'] += $cobrado;
                }
            }

            ksort($facturasPorMes);
            $mesesCobrados = array_values(array_map(function ($m) {
                return ['mes_label' => $m['mes_label'], 'cantidad' => $m['cantidad'], 'total' => round($m['total'], 2)];
            }, $facturasPorMes));
            $totalCobrado  = round(array_sum(array_column($mesesCobrados, 'total')), 2);
            $totalFacturas = array_sum(array_column($mesesCobrados, 'cantidad'));
        }

        $sheet = new ProyeccionNominaSheet(
            $empleado,
            $periodoLabel,
            $totalFacturas,
            $baseComisionable,
            $comisionAsesor,
            $comisionTeleasesor,
            $comisionGestor,
            $basePoliticaAnterior,
            $comisionPoliticaAnterior,
            $mesesCobrados,
            $totalCobrado,
            $generadoPor
        );

        $filename = 'proyeccion_nomina_' . now()->format('Ymd_His') . '.xlsx';

        $response = Excel::download(new class($sheet) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            private $s;
            public function __construct($s) { $this->s = $s; }
            public function sheets(): array { return [$this->s]; }
        }, $filename);

        $token = (string) $request->input('download_token', '');
        if ($token !== '') {
            setcookie('proy_nomina_token', $token, time() + 300, '/', '', false, false);
        }

        return $response;
    }

    /**
    public function listarEmpleadosPorRol(Request $request)
    {
        $rolId  = (int) $request->input('rol_id', 0);
        $search = trim((string) $request->input('q', ''));

        $query = DB::table('users as u')
            ->leftJoin('rol as r', 'r.id', '=', 'u.rol_id')
            ->select('u.id', 'u.name')
            ->where('u.estado_id', 1);

        if ($rolId > 0) {
            $query->where('u.rol_id', $rolId);
        } else {
            $query->where(function ($q) {
                $q->whereNull('u.rol_id')->orWhere('r.estado_id', 1);
            });
        }

        if ($search !== '') {
            $query->where('u.name', 'LIKE', "%{$search}%");
        }

        return response()->json($query->orderBy('u.name')->distinct()->limit(100)->get());
    }

    /**
     * Retorna los actores (asesor, teleasesor, gestor) para los filtros de
     * "Factura por Actor".
     *
     * Regla: deben aparecer absolutamente todos los usuarios ACTIVOS del sistema
     * (users.estado_id = 1), congruente con el módulo /usuarios
     * (App\Http\Livewire\Usuarios\ListarUsuarios, donde estado_id = 1 es Activo
     * y estado_id = 2 es Inactivo). No se restringe por facturas del período,
     * ya que el filtro es de selección de actor, no de resultados existentes.
     *
     * Respuesta: { asesores: [...], teleasesores: [...], gestores: [...] }
     */
    public function actoresPorPeriodo(Request $request)
    {
        $usuariosActivos = DB::table('users as u')
            ->where('u.estado_id', 1)
            ->select('u.id', 'u.name')
            ->orderBy('u.name')
            ->get()
            ->toArray();

        return response()->json([
            'asesores'     => $usuariosActivos,
            'teleasesores' => $usuariosActivos,
            'gestores'     => $usuariosActivos,
        ]);
    }

    /**
     * Facturas cerradas agrupadas por actor (Asesor, Tele Asesor, Gestor).
     *
     * Condiciones de cierre:
        *   - aplicacion_pagos.estado = 1 AND estado_cerrado = 2 AND saldo <= 0.0001
        *   - La fecha de último pago REAL (MAX abonos_creditos.fecha_pago) cae dentro del período
     *
     * Filtros opcionales independientes:
     *   - asesor_id   → factura.vendedor
     *   - teleasesor_id → factura.users_id
     *   - gestor_id   → factura.gestor_entrega
     */
    public function facturasPorActor(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $asesorId      = (int) $request->input('asesor_id', 0);
        $teleasesorId  = (int) $request->input('teleasesor_id', 0);
        $gestorId      = (int) $request->input('gestor_id', 0);

        // Sub-query estricto: fecha del último pago real por factura
        $ultimoPago = DB::table('aplicacion_pagos as ap2')
            ->leftJoin('abonos_creditos as ac2', function ($j) {
                $j->on('ac2.aplicacion_pagos_id', '=', 'ap2.id')
                  ->where('ac2.estado_abono', '=', 1);
            })
            ->where('ap2.estado', 1)
            ->where('ap2.estado_cerrado', 2)
            ->where('ap2.saldo', '<=', 0.0001)
            ->groupBy('ap2.id', 'ap2.factura_id', 'ap2.fecha_cierre_factura')
            ->selectRaw('ap2.factura_id,
                         MAX(DATE(ac2.fecha_pago)) as fecha_ultimo_pago')
            ->havingRaw('fecha_ultimo_pago IS NOT NULL');

        $query = DB::table('factura as f')
            ->joinSub($ultimoPago, 'up', 'up.factura_id', '=', 'f.id')
            ->leftJoin('users as uv', 'uv.id', '=', 'f.vendedor')
            ->leftJoin('users as uf', 'uf.id', '=', 'f.users_id')
            ->leftJoin('users as ug', 'ug.id', '=', 'f.gestor_entrega')
            ->leftJoin('tipo_pago_venta as tp', 'tp.id', '=', 'f.tipo_pago_id')
            ->where('f.estado_venta_id', '<>', 2)        // excluir anuladas
            ->whereBetween('up.fecha_ultimo_pago', [$fi, $ff])
            ->selectRaw("f.id,
                         f.cai                                            as factura,
                         COALESCE(uv.name, '—')                          as asesor_comercial,
                         COALESCE(uf.name, '—')                          as tele_asesor,
                         COALESCE(ug.name, '—')                          as gestor_entregas,
                         DATE_FORMAT(f.created_at, '%Y-%m-%d')           as fecha_creacion,
                         up.fecha_ultimo_pago                             as fecha_ultimo_pago,
                         COALESCE(tp.descripcion, 'N/A')                 as tipo_factura,
                         CASE
                             WHEN EXISTS (SELECT 1 FROM comision_politica_anterior_factura cpa WHERE cpa.factura_id = f.id) THEN 'Política Anterior'
                             WHEN EXISTS (SELECT 1 FROM facturas_comision fc WHERE fc.factura_id = f.id) THEN 'Nueva Política'
                             ELSE 'Sin asignar'
                         END                                              as politica,
                         ROUND(COALESCE(f.sub_total, 0), 2)              as subtotal,
                         ROUND(COALESCE(f.isv, 0), 2)                    as isv,
                         ROUND(COALESCE(f.total, 0), 2)                  as total");

        if ($asesorId > 0) {
            $query->where('f.vendedor', $asesorId);
        }
        if ($teleasesorId > 0) {
            $query->where('f.users_id', $teleasesorId);
        }
        if ($gestorId > 0) {
            $query->where('f.gestor_entrega', $gestorId);
        }

        $rows = $query->orderBy('up.fecha_ultimo_pago', 'DESC')->get();

        return response()->json([
            'data'   => $rows,
            'totales' => [
                'facturas'  => $rows->count(),
                'subtotal'  => round($rows->sum('subtotal'), 2),
                'isv'       => round($rows->sum('isv'), 2),
                'total'     => round($rows->sum('total'), 2),
            ],
        ]);
    }

    /**
     * Cuadre Libro de Cobros vs Base Comisionable (Proyección).
     *
     * Compara, para el mismo rango de fechas y vendedor, lo que el Libro de Cobros
     * registra como cobrado (monto_abonado) versus la base comisionable de líneas
     * de producto, explicando por factura las razones de la brecha.
     */
    public function reporteCuadreLibroCobros(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $usuarioId = (int) $request->input('usuario_id', 0);   // mismo parámetro que Proyección

        // ── PASO 1: Facturas cerradas en el rango ─────────────────────────────
        // Idéntico al inicio de reporteProyecciones: AP cerrada, saldo<=0,
        // fecha_pago_cierre dentro del rango.
        $cierres = DB::table('aplicacion_pagos as ap')
            ->leftJoin('abonos_creditos as ac', function ($join) {
                $join->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                    ->where('ac.estado_abono', '=', 1);
            })
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', 2)
            ->where('ap.saldo', '<=', 0.0001)
            ->groupBy('ap.id', 'ap.factura_id', 'ap.fecha_cierre_factura')
            ->selectRaw("ap.id AS aplicacion_pagos_id,
                         ap.factura_id,
                         COALESCE(MAX(DATE(ac.fecha_pago)), DATE(ap.fecha_cierre_factura)) AS fecha_pago_cierre")
            ->havingRaw('fecha_pago_cierre IS NOT NULL')
            ->havingBetween('fecha_pago_cierre', [$fi, $ff])
            ->get();

        if ($cierres->isEmpty()) {
            return response()->json(['data' => [], 'totales' => $this->cuadreTotalesVacios()]);
        }

        $cierresPorFactura = $cierres->keyBy('factura_id');
        $facturaIds        = $cierres->pluck('factura_id')->map(fn($v) => (int) $v)->all();

        // ── PASO 2: Datos de la factura (mismo join que Proyección) ──────────
        $facturas = DB::table('factura as f')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->leftJoin('cliente_categoria_escala as cce', 'cce.id', '=', 'cl.cliente_categoria_escala_id')
            ->leftJoin('users as uf', 'uf.id', '=', 'f.users_id')
            ->leftJoin('users as uv', 'uv.id', '=', 'f.vendedor')
            ->leftJoin('users as ug', 'ug.id', '=', 'f.gestor_entrega')
            ->whereIn('f.id', $facturaIds)
            // Filtro de usuario igual que Proyección: cualquier rol
            ->when($usuarioId > 0, function ($q) use ($usuarioId) {
                $q->where(function ($or) use ($usuarioId) {
                    $or->where('f.users_id', $usuarioId)
                       ->orWhere('f.vendedor', $usuarioId)
                       ->orWhere('f.gestor_entrega', $usuarioId);
                });
            })
            ->selectRaw("f.id,
                         f.cai,
                         f.nombre_cliente                                     AS cliente,
                         ROUND(COALESCE(f.sub_total, 0), 2)                  AS sub_total_factura,
                         ROUND(COALESCE(f.total, 0), 2)                      AS total_factura,
                         ROUND(COALESCE(f.total,0) - COALESCE(f.sub_total,0),2) AS isv_factura,
                         DATE_FORMAT(f.created_at, '%Y-%m-%d %H:%i:%s')      AS fecha_creacion_factura,
                         f.users_id  AS facturador_id, uf.name AS facturador,
                         f.vendedor  AS vendedor_id,   uv.name AS vendedor,
                         f.gestor_entrega AS gestor_id, ug.name AS gestor,
                         cl.cliente_categoria_escala_id,
                         cce.nombre_categoria AS escala_cliente")
            ->get()
            ->keyBy('id');

        if ($facturas->isEmpty()) {
            return response()->json(['data' => [], 'totales' => $this->cuadreTotalesVacios()]);
        }

        $facturaIdsFiltrados = $facturas->keys()->map(fn($v) => (int) $v)->all();

        // ── PASO 3: Total cobrado histórico por factura (todos los abonos) ───
        $totalAbonado = DB::table('abonos_creditos')
            ->whereIn('factura_id', $facturaIdsFiltrados)
            ->where('estado_abono', 1)
            ->groupBy('factura_id')
            ->selectRaw('factura_id, ROUND(SUM(monto_abonado), 2) AS total_abonado')
            ->get()
            ->keyBy('factura_id');

        // ── PASO 4: Líneas de producto (idéntico a Proyección) ───────────────
        $lineasFactura = DB::table('venta_has_producto as vhp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'vhp.precios_producto_carga_id')
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->leftJoin('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->whereIn('vhp.factura_id', $facturaIdsFiltrados)
            ->groupBy('vhp.factura_id', 'vhp.producto_id', 'vhp.precios_producto_carga_id',
                      'vhp.precio_unidad', 'vhp.precioSeleccionado', 'ppc.categoria_precios_id',
                      'p.nombre', 'cp.nombre')
            ->selectRaw("vhp.factura_id,
                         vhp.producto_id,
                         p.nombre                                            AS producto,
                         COALESCE(SUM(vhp.cantidad_s), SUM(vhp.cantidad))   AS cantidad,
                         vhp.precio_unidad,
                         vhp.precioSeleccionado,
                         vhp.precios_producto_carga_id,
                         ppc.categoria_precios_id,
                         cp.nombre                                           AS categoria_precio")
            ->get()
            ->groupBy('factura_id');

        // ── PASO 5: Mapa de escalas (idéntico a Proyección) ──────────────────
        $clienteEscalaIds = $facturas->pluck('cliente_categoria_escala_id')
            ->filter(fn($v) => (int) $v > 0)->unique()->values()->all();
        $categoriaIds = $lineasFactura->flatten(1)->pluck('categoria_precios_id')
            ->filter(fn($v) => (int) $v > 0)->unique()->values()->all();

        $escalaMap = [];
        if (!empty($clienteEscalaIds) && !empty($categoriaIds)) {
            DB::table('comision_escala')
                ->where('estado_id', 1)
                ->whereIn('rol_id', [2, 3, 16])
                ->whereIn('cliente_categoria_escala_id', $clienteEscalaIds)
                ->whereIn('categoria_precios_id', $categoriaIds)
                ->select('rol_id', 'cliente_categoria_escala_id', 'categoria_precios_id')
                ->get()
                ->each(function ($e) use (&$escalaMap) {
                    $escalaMap[$e->rol_id . '|' . $e->cliente_categoria_escala_id . '|' . $e->categoria_precios_id] = true;
                });
        }

        // ── PASO 6: Construir filas de cuadre ────────────────────────────────
        $data = [];
        $sumCobrado              = 0.0;
        $sumCobradoSinIsv        = 0.0;
        $sumIsvCobrado           = 0.0;
        $sumSubTotalFacturas     = 0.0;
        $sumBaseComisionable     = 0.0;
        $sumBaseComisionableCierreEnRango = 0.0;
        $cntFacturasCompletas    = 0;
        $cntFacturasCierreEnRango = 0;

        foreach ($facturas as $factura) {
            $facturaId    = (int) $factura->id;
            $cierre       = $cierresPorFactura->get($facturaId);
            $totalFact    = (float) $factura->total_factura;
            $subTotalFact = (float) $factura->sub_total_factura;
            $isvFact      = (float) $factura->isv_factura;

            // Cobrado total histórico de la factura
            $cobradoTotal = (float) ($totalAbonado[$facturaId]->total_abonado ?? 0);
            $saldo        = max(round($totalFact - $cobradoTotal, 2), 0);
            $estadoPago   = $saldo <= 0.01 ? 'PAGADA' : 'PARCIAL';

            // Cobrado proporcional: para facturas PAGADAS cobradoTotal ≈ total_factura
            // Se usa total_factura como base de la proporción sin ISV
            $cobradoSinIsv = $totalFact > 0
                ? round($cobradoTotal * $subTotalFact / $totalFact, 2)
                : $cobradoTotal;
            $isvCobrado    = round($cobradoTotal - $cobradoSinIsv, 2);

            // fecha_pago_cierre del AP (mismo que Proyección)
            $fechaCierreAp = $cierre ? (string) $cierre->fecha_pago_cierre : null;
            // Siempre true aquí porque ya filtramos por fecha_pago_cierre en Paso 1
            $cierreEnRango = true;

            // Base comisionable de líneas (idéntico a Proyección)
            $baseComisionable   = 0.0;
            $lineasComisionables = 0;
            $lineasExcluidas    = 0;
            $razonesExclusion   = [];
            $escalaClienteId    = (int) ($factura->cliente_categoria_escala_id ?? 0);

            $lineas = collect($lineasFactura->get($facturaId, collect([])));
            foreach ($lineas as $linea) {
                $cantidad           = (float) ($linea->cantidad ?? 0);
                $precioSeleccionado = (float) ($linea->precioSeleccionado ?? 0);
                $precioUnitario     = (float) ($linea->precio_unidad ?? 0);
                $precioParaComision = $precioSeleccionado > 0 ? $precioSeleccionado : $precioUnitario;
                $catId              = (int) ($linea->categoria_precios_id ?? 0);

                $motivosLinea = [];
                if (empty($linea->precios_producto_carga_id)) $motivosLinea[] = 'sin precios_producto_carga_id';
                if ($catId <= 0)          $motivosLinea[] = 'sin categoría de precio';
                if ($escalaClienteId <= 0) $motivosLinea[] = 'cliente sin categoría de escala';

                if (empty($motivosLinea)) {
                    $tieneEscala = false;
                    foreach ([2, 3, 16] as $rolId) {
                        if (isset($escalaMap[$rolId . '|' . $escalaClienteId . '|' . $catId])) {
                            $tieneEscala = true;
                            break;
                        }
                    }
                    if (!$tieneEscala) $motivosLinea[] = 'sin escala configurada';
                }

                if (empty($motivosLinea)) {
                    $baseComisionable += $cantidad * $precioParaComision;
                    $lineasComisionables++;
                } else {
                    $lineasExcluidas++;
                    foreach ($motivosLinea as $m) $razonesExclusion[] = $m;
                }
            }
            $baseComisionable = round($baseComisionable, 2);

            // Razones de la diferencia cobrado_sin_isv vs base_comisionable
            $razones = [];
            if ($estadoPago === 'PARCIAL') {
                $razones[] = 'Pago parcial (saldo L.' . number_format($saldo, 2) . ')';
            }
            if ($isvFact > 0) {
                $razones[] = 'ISV L.' . number_format($isvFact, 2);
            }
            if ($lineasExcluidas > 0) {
                $razones[] = $lineasExcluidas . ' línea(s) sin escala: ' . implode(', ', array_unique($razonesExclusion));
            }
            if ($lineas->isEmpty()) {
                $razones[] = 'Sin líneas de producto';
            }

            $diferencia = round($cobradoSinIsv - $baseComisionable, 2);

            $sumCobrado                      += $cobradoTotal;
            $sumCobradoSinIsv                += $cobradoSinIsv;
            $sumIsvCobrado                   += $isvCobrado;
            $sumSubTotalFacturas             += $subTotalFact;
            $sumBaseComisionable             += $baseComisionable;
            $sumBaseComisionableCierreEnRango += $baseComisionable;
            $cntFacturasCierreEnRango++;
            if ($estadoPago === 'PAGADA') $cntFacturasCompletas++;

            $data[] = [
                'factura_id'             => $facturaId,
                'factura'                => (string) ($factura->cai ?? '#' . $facturaId),
                'cliente'                => (string) ($factura->cliente ?? ''),
                'facturador'             => (string) ($factura->facturador ?? ''),
                'vendedor'               => (string) ($factura->vendedor ?? ''),
                'gestor'                 => (string) ($factura->gestor ?? ''),
                'fecha_creacion_factura' => (string) ($factura->fecha_creacion_factura ?? ''),
                'fecha_pago_cierre'      => $fechaCierreAp ?? '',
                'total_cobrado_factura'  => $cobradoTotal,
                'cobrado_sin_isv'        => $cobradoSinIsv,
                'isv_cobrado'            => $isvCobrado,
                'sub_total_factura'      => $subTotalFact,
                'isv_factura'            => $isvFact,
                'total_factura'          => $totalFact,
                'saldo_pendiente'        => $saldo,
                'estado_pago'            => $estadoPago,
                'lineas_comisionables'   => $lineasComisionables,
                'lineas_excluidas'       => $lineasExcluidas,
                'base_comisionable'      => $baseComisionable,
                'diferencia'             => $diferencia,
                'razones_diferencia'     => implode(' | ', $razones),
            ];
        }

        usort($data, fn($a, $b) => strcmp(
            $a['fecha_pago_cierre'] . $a['factura'],
            $b['fecha_pago_cierre'] . $b['factura']
        ));

        return response()->json([
            'data'    => $data,
            'totales' => [
                'facturas_en_rango'                 => count($data),
                'facturas_completas'                => $cntFacturasCompletas,
                'facturas_cierre_en_rango'          => $cntFacturasCierreEnRango,
                'total_cobrado'                     => round($sumCobrado, 2),
                'total_cobrado_sin_isv'             => round($sumCobradoSinIsv, 2),
                'total_isv_cobrado'                 => round($sumIsvCobrado, 2),
                'total_sub_total_facturas'          => round($sumSubTotalFacturas, 2),
                'brecha_parciales'                  => round($sumSubTotalFacturas - $sumCobradoSinIsv, 2),
                'total_base_comisionable'           => round($sumBaseComisionable, 2),
                'base_comisionable_cierre_en_rango' => round($sumBaseComisionableCierreEnRango, 2),
                'diferencia'                        => round($sumCobradoSinIsv - $sumBaseComisionable, 2),
            ],
        ]);
    }

    private function cuadreTotalesVacios(): array
    {
        return [
            'facturas_en_rango'                 => 0,
            'facturas_completas'                => 0,
            'facturas_cierre_en_rango'          => 0,
            'total_cobrado'                     => 0,
            'total_cobrado_sin_isv'             => 0,
            'total_isv_cobrado'                 => 0,
            'total_sub_total_facturas'          => 0,
            'brecha_parciales'                  => 0,
            'total_base_comisionable'           => 0,
            'base_comisionable_cierre_en_rango' => 0,
            'diferencia'                        => 0,
        ];
    }

    /**
     * Auditoría contable: para todas las facturas de un vendedor/usuario
     * que tuvieron pagos en el rango (= universo del Libro de Cobros),
     * muestra por factura:
     *   - Si está contemplada en comisiones (escala o política anterior)
     *   - Si está completamente pagada
     *   - Si SUM(abonos) == total_factura (cuadre contable)
     *   - El total cobrado en el rango y el total histórico
     */
    public function reporteAuditoriaContable(Request $request)
    {
        [$fi, $ff] = $this->resolveDateRange($request);
        $vendedorId = (int) $request->input('vendedor_id', 0);

        // 1. Todas las facturas con abonos en el rango (= universo Libro de Cobros)
        $abonosRango = DB::table('abonos_creditos as ac')
            ->join('factura as f', 'f.id', '=', 'ac.factura_id')
            ->leftJoin('users as uv', 'uv.id', '=', 'f.vendedor')
            ->leftJoin('users as uf', 'uf.id', '=', 'f.users_id')
            ->where('ac.estado_abono', 1)
            ->whereNotIn('ac.banco_id', [12, 13])
            ->whereBetween(DB::raw('DATE(ac.fecha_pago)'), [$fi, $ff])
            ->when($vendedorId > 0, fn($q) => $q->where('f.vendedor', $vendedorId))
            ->groupBy('f.id', 'f.cai', 'f.nombre_cliente', 'f.vendedor', 'f.users_id',
                      'f.sub_total', 'f.total', 'f.created_at', 'f.estado_venta_id',
                      'uv.name', 'uf.name')
            ->selectRaw("
                f.id                                                    AS factura_id,
                f.cai,
                f.nombre_cliente                                        AS cliente,
                f.vendedor                                              AS vendedor_id,
                uv.name                                                 AS vendedor,
                uf.name                                                 AS facturador,
                ROUND(COALESCE(f.sub_total, 0), 2)                     AS sub_total_factura,
                ROUND(COALESCE(f.total, 0), 2)                         AS total_factura,
                ROUND(COALESCE(f.total,0) - COALESCE(f.sub_total,0),2) AS isv_factura,
                DATE_FORMAT(f.created_at, '%Y-%m-%d')                  AS fecha_creacion_factura,
                ROUND(SUM(ac.monto_abonado), 2)                        AS cobrado_en_rango,
                COUNT(DISTINCT ac.id)                                   AS num_abonos_en_rango,
                MAX(DATE(ac.fecha_pago))                                AS ultima_fecha_pago_rango,
                f.estado_venta_id
            ")
            ->orderByRaw('MAX(DATE(ac.fecha_pago)) ASC, f.cai ASC')
            ->get();

        if ($abonosRango->isEmpty()) {
            return response()->json(['data' => [], 'totales' => []]);
        }

        $facturaIds = $abonosRango->pluck('factura_id')->map(fn($v) => (int) $v)->all();

        // 2. Total abonado HISTÓRICO por factura (todos los abonos, no solo los del rango)
        $totalHistorico = DB::table('abonos_creditos')
            ->whereIn('factura_id', $facturaIds)
            ->where('estado_abono', 1)
            ->groupBy('factura_id')
            ->selectRaw('factura_id, ROUND(SUM(monto_abonado), 2) AS total_abonado_historico, COUNT(*) AS num_abonos_total')
            ->get()
            ->keyBy('factura_id');

        // 3. AP cerrada por factura (indica que el sistema la cerró formalmente)
        $apCerrada = DB::table('aplicacion_pagos')
            ->where('estado', 1)
            ->where('estado_cerrado', 2)
            ->where('saldo', '<=', 0.0001)
            ->whereIn('factura_id', $facturaIds)
            ->selectRaw('factura_id, MIN(fecha_cierre_factura) AS fecha_cierre_ap')
            ->groupBy('factura_id')
            ->get()
            ->keyBy('factura_id');

        // 4. ¿Está en facturas_comision (escala nueva)?
        $enEscala = DB::table('facturas_comision')
            ->whereIn('factura_id', $facturaIds)
            ->where('estado_id', 1)
            ->distinct()
            ->pluck('factura_id')
            ->map(fn($v) => (int) $v)
            ->flip()
            ->all();   // hash map id => 0

        // 5a. ¿Ya registrada en comision_politica_anterior_factura? (registrada formalmente)
        $politicaRegistrada = DB::getSchemaBuilder()->hasTable('comision_politica_anterior_factura')
            ? DB::table('comision_politica_anterior_factura')
                ->whereIn('factura_id', $facturaIds)
                ->distinct()
                ->pluck('factura_id')
                ->map(fn($v) => (int) $v)
                ->flip()
                ->all()
            : [];

        // 5b. AP cerrada en el rango (congruente con Proyección):
        //     facturas que tienen aplicacion_pagos cerrada con fecha_pago_cierre en el rango.
        //     Estas son exactamente las que la Proyección y Política Anterior procesan.
        $apEnRango = DB::table('aplicacion_pagos as ap')
            ->leftJoin('abonos_creditos as ac', function ($j) {
                $j->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                  ->where('ac.estado_abono', '=', 1);
            })
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', 2)
            ->where('ap.saldo', '<=', 0.0001)
            ->whereIn('ap.factura_id', $facturaIds)
            ->groupBy('ap.id', 'ap.factura_id', 'ap.fecha_cierre_factura')
            ->selectRaw("ap.factura_id,
                         COALESCE(MAX(DATE(ac.fecha_pago)), DATE(ap.fecha_cierre_factura)) AS fecha_pago_cierre")
            ->havingRaw('fecha_pago_cierre IS NOT NULL')
            ->havingBetween('fecha_pago_cierre', [$fi, $ff])
            ->get()
            ->keyBy('factura_id');

        // 5c. ¿Es elegible para política anterior?
        //     = AP cerrada en rango + NO en escala (facturas_comision)
        //     Exactamente las que la Proyección marca como "excluidas" y envía a Política Anterior.
        $elegiblePoliticaAnterior = [];
        foreach ($apEnRango as $fid => $row) {
            if (!isset($enEscala[(int)$fid])) {
                $elegiblePoliticaAnterior[(int)$fid] = true;
            }
        }

        // 6. Construir filas de auditoría
        $data = [];
        $kpi  = [
            'total_cobrado_rango'                  => 0.0,
            'facturas_total'                       => 0,
            'facturas_en_escala'                   => 0,
            'facturas_en_politica_registrada'      => 0,
            'facturas_en_politica_elegible'        => 0,
            'facturas_en_comisiones'               => 0,
            'facturas_sin_comisiones'              => 0,
            'facturas_sin_ap_en_rango'             => 0,
            'facturas_pagadas_completas'           => 0,
            'facturas_parciales'                   => 0,
            'facturas_con_cuadre_ok'               => 0,
            'facturas_con_cuadre_error'            => 0,
            'total_facturas_valor'                 => 0.0,
            'total_cobrado_historico'              => 0.0,
        ];

        foreach ($abonosRango as $row) {
            $fid          = (int) $row->factura_id;
            $totalFact    = (float) $row->total_factura;
            $subTotal     = (float) $row->sub_total_factura;
            $isvFact      = (float) $row->isv_factura;
            $cobradoRango = (float) $row->cobrado_en_rango;

            $hist         = $totalHistorico[$fid] ?? null;
            $totalHist    = $hist ? (float) $hist->total_abonado_historico : 0.0;
            $numAbonos    = $hist ? (int) $hist->num_abonos_total : 0;

            $ap           = $apCerrada[$fid] ?? null;
            $tieneAp      = $ap !== null;
            $fechaCierreAp = $tieneAp ? (string) $ap->fecha_cierre_ap : null;

            $saldo        = max(round($totalFact - $totalHist, 2), 0);
            $estadoPago   = $saldo <= 0.01 ? 'PAGADA' : 'PARCIAL';

            // Cuadre contable: ¿SUM(abonos) == total_factura?
            $diferenciaCuadre = round($totalFact - $totalHist, 2);
            $cuadreOk = abs($diferenciaCuadre) <= 0.01;

            $enEsc             = isset($enEscala[$fid]);
            $enPolRegistrada   = isset($politicaRegistrada[$fid]);
            $enPolElegible     = isset($elegiblePoliticaAnterior[$fid]);
            $tieneApEnRango    = isset($apEnRango[$fid]);
            // En comisiones = en escala O en política anterior (registrada o elegible)
            $enCom = $enEsc || $enPolRegistrada || $enPolElegible;

            if ($enEsc && $enPolRegistrada)        $politica = 'Escala + Pol.Ant (Registrada)';
            elseif ($enEsc && $enPolElegible)      $politica = 'Escala (Nueva Política)';
            elseif ($enEsc)                        $politica = 'Escala (Nueva Política)';
            elseif ($enPolRegistrada)              $politica = 'Política Anterior (Registrada)';
            elseif ($enPolElegible)                $politica = 'Política Anterior (Elegible)';
            else                                   $politica = '—';

            // Alertas
            $alertas = [];
            if (!$enCom && !$tieneApEnRango) {
                $alertas[] = 'Sin AP cerrada en rango — solo abonos parciales (no aplica comisión)';
            } elseif (!$enCom) {
                $alertas[] = 'FACTURA AP-CERRADA EN RANGO PERO SIN COMISIÓN ASIGNADA';
            }
            if ($estadoPago === 'PARCIAL') {
                $alertas[] = 'Pago parcial — saldo pendiente L.' . number_format($saldo, 2);
            }
            if (!$cuadreOk) {
                $alertas[] = 'CUADRE CONTABLE FALLA: sum_abonos ≠ total_factura (dif L.' . number_format(abs($diferenciaCuadre), 2) . ')';
            }
            if ($tieneAp && $estadoPago === 'PARCIAL') {
                $alertas[] = 'AP cerrada pero saldo > 0.01 (revisar)';
            }
            if ((int)($row->estado_venta_id ?? 0) === 2) {
                $alertas[] = 'FACTURA ANULADA';
            }

            // KPIs
            $kpi['total_cobrado_rango']               += $cobradoRango;
            $kpi['total_cobrado_historico']           += $totalHist;
            $kpi['total_facturas_valor']              += $totalFact;
            $kpi['facturas_total']++;
            if ($enEsc)           $kpi['facturas_en_escala']++;
            if ($enPolRegistrada) $kpi['facturas_en_politica_registrada']++;
            if ($enPolElegible)   $kpi['facturas_en_politica_elegible']++;
            if ($enCom)           $kpi['facturas_en_comisiones']++;
            else                  $kpi['facturas_sin_comisiones']++;
            if (!$tieneApEnRango) $kpi['facturas_sin_ap_en_rango']++;
            if ($estadoPago === 'PAGADA')   $kpi['facturas_pagadas_completas']++;
            else                           $kpi['facturas_parciales']++;
            if ($cuadreOk) $kpi['facturas_con_cuadre_ok']++;
            else           $kpi['facturas_con_cuadre_error']++;

            $data[] = [
                'factura_id'               => $fid,
                'factura'                       => (string) ($row->cai ?? '#' . $fid),
                'cliente'                       => (string) ($row->cliente ?? ''),
                'vendedor'                      => (string) ($row->vendedor ?? ''),
                'facturador'                    => (string) ($row->facturador ?? ''),
                'fecha_creacion_factura'        => (string) ($row->fecha_creacion_factura ?? ''),
                'ultima_fecha_pago_rango'       => (string) ($row->ultima_fecha_pago_rango ?? ''),
                'num_abonos_en_rango'           => (int) $row->num_abonos_en_rango,
                'cobrado_en_rango'              => $cobradoRango,
                'total_abonado_historico'       => $totalHist,
                'num_abonos_total'              => $numAbonos,
                'sub_total_factura'             => $subTotal,
                'isv_factura'                   => $isvFact,
                'total_factura'                 => $totalFact,
                'saldo_pendiente'               => $saldo,
                'diferencia_cuadre'             => $diferenciaCuadre,
                'cuadre_ok'                     => $cuadreOk,
                'tiene_ap_cerrada'              => $tieneAp,
                'tiene_ap_en_rango'             => $tieneApEnRango,
                'fecha_cierre_ap'               => $fechaCierreAp,
                'estado_pago'                   => $estadoPago,
                'en_comisiones'                 => $enCom,
                'en_escala'                     => $enEsc,
                'en_politica_registrada'        => $enPolRegistrada,
                'en_politica_elegible'          => $enPolElegible,
                'politica'                      => $politica,
                'alertas'                       => implode(' | ', $alertas),
            ];
        }

        // Redondear KPIs
        $kpi['total_cobrado_rango']     = round($kpi['total_cobrado_rango'], 2);
        $kpi['total_cobrado_historico'] = round($kpi['total_cobrado_historico'], 2);
        $kpi['total_facturas_valor']    = round($kpi['total_facturas_valor'], 2);

        return response()->json(['data' => $data, 'kpi' => $kpi]);
    }
}
