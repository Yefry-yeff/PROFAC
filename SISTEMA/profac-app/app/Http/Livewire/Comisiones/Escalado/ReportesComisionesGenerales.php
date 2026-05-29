<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportesComisionesGenerales extends Component
{
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
            ->select('id', 'name')
            ->where('estado_id', 1)
            ->where('name', 'LIKE', "%{$search}%")
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
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';
        $empleadoId = $request->input('filtroEspecifico');

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
            ->join('comision_empleado as ce', function($join) use ($empleadoId) {
                $join->on('ce.rol_id', '=', 'fc.rol_id')
                     ->whereRaw('YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)')
                     ->whereRaw('MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)')
                     ->where('ce.estado_id', '=', 1)
                     ->where('ce.users_comision', '=', $empleadoId);
            })
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->select(
                'pc.id',
                'pc.id as registro_id',
                'u.id as empleado_id',
                'u.name as empleado',
                'f.cai as factura',
                'cl.nombre as cliente',
                'p.nombre as producto',
                'pc.cantidad',
                'pc.monto_comision',
                DB::raw('DATE_FORMAT(fc.fecha_cierre_factura, "%Y-%m-%d") as fecha')
            );

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte de comisiones por rol
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteRol(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';
        $rolId = $request->input('filtroEspecifico');

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
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';

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
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';

        $query = DB::table('producto_comision as pc')
            ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
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

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por factura
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteFacturas(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';

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

        return DataTables::of($query)->make(true);
    }

    /**
     * KPIs del dashboard para el período seleccionado
     */
    public function stats(Request $request)
    {
        $fi    = $request->input('fechaInicio') . ' 00:00:00';
        $ff    = $request->input('fechaFin')    . ' 23:59:59';
        $fiMes = date('Y-m-01', strtotime($request->input('fechaInicio')));
        $ffMes = date('Y-m-01', strtotime($request->input('fechaFin')));

        $totalComision  = DB::table('facturas_comision')
            ->whereBetween('fecha_cierre_factura', [$fi, $ff])
            ->where('estado_id', 1)
            ->sum('monto_rol') ?? 0;

        $totalFacturas  = DB::table('facturas_comision')
            ->whereBetween('fecha_cierre_factura', [$fi, $ff])
            ->where('estado_id', 1)
            ->distinct('factura_id')
            ->count('factura_id');

        $totalEmpleados = DB::table('comision_empleado')
            ->whereBetween('mes_comision', [$fiMes, $ffMes])
            ->where('estado_id', 1)
            ->distinct('users_comision')
            ->count('users_comision');

        $promedio = $totalEmpleados > 0
            ? round($totalComision / $totalEmpleados, 2)
            : 0;

        return response()->json([
            'total_comision'  => number_format($totalComision, 2),
            'total_facturas'  => $totalFacturas,
            'total_empleados' => $totalEmpleados,
            'promedio'        => number_format($promedio, 2),
        ]);
    }

    /**
     * Nómina: un registro por empleado + rol + mes (para RRHH / nómina)
     */
    public function reporteNomina(Request $request)
    {
        $fiMes = date('Y-m-01', strtotime($request->input('fechaInicio')));
        $ffMes = date('Y-m-01', strtotime($request->input('fechaFin')));

        $query = DB::table('comision_empleado as ce')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->join('rol as r',   'r.id', '=', 'ce.rol_id')
            ->whereBetween('ce.mes_comision', [$fiMes, $ffMes])
            ->where('ce.estado_id', 1)
            ->select(
                'ce.id',
                'u.name as empleado',
                'r.nombre as rol',
                DB::raw("DATE_FORMAT(ce.mes_comision,'%M %Y') as mes"),
                DB::raw("DATE_FORMAT(ce.mes_comision,'%Y-%m')  as mes_clave"),
                DB::raw('ce.comision_acumulada as total_comision'),
                DB::raw("(SELECT COUNT(DISTINCT fc2.factura_id)
                          FROM facturas_comision fc2
                          WHERE fc2.rol_id    = ce.rol_id
                            AND fc2.estado_id = 1
                            AND YEAR(fc2.fecha_cierre_factura)  = YEAR(ce.mes_comision)
                            AND MONTH(fc2.fecha_cierre_factura) = MONTH(ce.mes_comision)
                         ) as num_facturas")
            );

        if ($eid = $request->input('empleado_id')) $query->where('ce.users_comision', $eid);
        if ($rid = $request->input('rol_id'))      $query->where('ce.rol_id', $rid);

        return DataTables::of($query)->make(true);
    }

    /**
     * Ranking de empleados por comisión total en el período
     */
    public function reporteRanking(Request $request)
    {
        $fiMes = date('Y-m-01', strtotime($request->input('fechaInicio')));
        $ffMes = date('Y-m-01', strtotime($request->input('fechaFin')));

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

        return DataTables::of($query)->make(true);
    }

    /**
     * Comparativo mensual de comisiones
     */
    public function reporteComparativo(Request $request)
    {
        $fiMes = date('Y-m-01', strtotime($request->input('fechaInicio')));
        $ffMes = date('Y-m-01', strtotime($request->input('fechaFin')));

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

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte de comisiones reversadas (anulación de pagos)
     */
    public function reporteReversiones(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';

        $query = DB::table('comision_reversiones as cr')
            ->leftJoin('factura as f', 'f.id', '=', 'cr.factura_id')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'f.cliente_id')
            ->leftJoin('users as ua', 'ua.id', '=', 'cr.usr_anulo')
            ->whereBetween('cr.created_at', [$fechaInicio, $fechaFin])
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
