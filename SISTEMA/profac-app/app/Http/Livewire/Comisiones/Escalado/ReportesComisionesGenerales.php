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
     */
    public function reporteEmpleado(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $empleadoId = $request->input('filtroEspecifico');

        $query = DB::table('comision_empleado as ce')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->leftJoin('facturas_comision as fc', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('fc.rol_id', '=', 'ce.rol_id')
                     ->where('fc.estado_id', '=', 1)
                     ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin]);
            })
            ->leftJoin('producto_comision as pc', 'pc.facturas_comision_id', '=', 'fc.id')
            ->leftJoin('producto as p', 'p.id', '=', 'pc.producto_id')
            ->leftJoin('factura as f', 'f.id', '=', 'fc.factura_id')
            ->select(
                DB::raw('COALESCE(pc.id, ce.id) as id'),
                'u.id as empleado_id',
                'u.name as empleado',
                'f.cai as factura',
                'p.nombre as producto',
                DB::raw('COALESCE(pc.cantidad, 0) as cantidad'),
                DB::raw('COALESCE(pc.monto_comision, 0) as monto_comision'),
                DB::raw('DATE_FORMAT(fc.created_at, "%Y-%m-%d") as fecha')
            )
            ->where('ce.estado_id', 1);

        if ($empleadoId) {
            $query->where('u.id', $empleadoId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte de comisiones por rol
     */
    public function reporteRol(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $rolId = $request->input('filtroEspecifico');

        $query = DB::table('rol as r')
            ->leftJoin('comision_empleado as ce', 'ce.rol_id', '=', 'r.id')
            ->leftJoin('users as u', 'u.id', '=', 'ce.users_comision')
            ->leftJoin('facturas_comision as fc', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('fc.rol_id', '=', 'r.id')
                     ->where('fc.estado_id', '=', 1)
                     ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin]);
            })
            ->select(
                'r.id',
                'r.nombre as rol',
                DB::raw('COALESCE(u.name, "Sin empleado") as empleado'),
                DB::raw('COALESCE(SUM(fc.monto_rol), 0) as total_comisiones'),
                DB::raw('COUNT(DISTINCT fc.id) as num_facturas')
            )
            ->where('r.estado_id', 1)
            ->groupBy('r.id', 'r.nombre', 'u.id', 'u.name');

        if ($rolId) {
            $query->where('r.id', $rolId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por usuario
     */
    public function reporteUsuarios(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $query = DB::table('facturas_comision as fc')
            ->join('comision_empleado as ce', 'ce.rol_id', '=', 'fc.rol_id')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->leftJoin('rol as r', 'r.id', '=', 'fc.rol_id')
            ->join('producto_comision as pc', 'pc.facturas_comision_id', '=', 'fc.id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'u.id',
                'u.name as usuario',
                DB::raw('COALESCE(r.nombre, "Sin rol") as rol'),
                DB::raw('SUM(fc.monto_rol) as total_comisiones'),
                DB::raw('COUNT(DISTINCT fc.id) as num_facturas'),
                DB::raw('COUNT(DISTINCT pc.producto_id) as num_productos')
            )
            ->groupBy('u.id', 'u.name', 'r.nombre');

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por producto
     */
    public function reporteProductos(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $query = DB::table('producto_comision as pc')
            ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
            ->join('producto as p', 'p.id', '=', 'pc.producto_id')
            ->join('comision_empleado as ce', 'ce.rol_id', '=', 'fc.rol_id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'p.id',
                'p.nombre as producto',
                'p.codigo_barra',
                DB::raw('SUM(pc.cantidad) as cantidad_vendida'),
                DB::raw('SUM(pc.monto_comision) as total_comisiones'),
                DB::raw('COUNT(DISTINCT ce.users_comision) as num_empleados')
            )
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra');

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por factura
     */
    public function reporteFacturas(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $query = DB::table('facturas_comision as fc')
            ->join('comision_empleado as ce', 'ce.rol_id', '=', 'fc.rol_id')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->join('factura as v', 'v.id', '=', 'fc.factura_id')
            ->join('cliente as cl', 'cl.id', '=', 'v.cliente_id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'fc.id',
                'v.cai as factura',
                'cl.nombre as cliente',
                'u.name as empleado',
                'v.total as total_venta',
                'fc.monto_rol as total_comision',
                DB::raw('DATE_FORMAT(fc.created_at, "%Y-%m-%d") as fecha')
            );

        return DataTables::of($query)->make(true);
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
