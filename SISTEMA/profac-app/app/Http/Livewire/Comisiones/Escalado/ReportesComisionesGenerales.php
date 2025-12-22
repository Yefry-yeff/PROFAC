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
        
        $roles = DB::table('roles')
            ->select('id', 'name')
            ->where('name', 'LIKE', "%{$search}%")
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

        $query = DB::table('producto_comision as pc')
            ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
            ->join('comision_empleado as ce', 'ce.id', '=', 'pc.comision_empleado_id')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->join('producto as p', 'p.id', '=', 'pc.producto_id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'pc.id',
                'u.name as empleado',
                'fc.num_factura as factura',
                'p.nombre as producto',
                'pc.cantidad',
                'pc.monto_comision',
                DB::raw('DATE_FORMAT(fc.created_at, "%Y-%m-%d") as fecha')
            );

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

        $query = DB::table('facturas_comision as fc')
            ->join('comision_empleado as ce', 'ce.id', '=', 'fc.comision_empleado_id')
            ->join('users as u', 'u.id', '=', 'ce.users_id')
            ->join('model_has_roles as mhr', 'mhr.model_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'r.id',
                'r.name as rol',
                'u.name as empleado',
                DB::raw('SUM(fc.total_comision) as total_comisiones'),
                DB::raw('COUNT(DISTINCT fc.id) as num_facturas')
            )
            ->groupBy('r.id', 'r.name', 'u.id', 'u.name');

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
            ->join('comision_empleado as ce', 'ce.id', '=', 'fc.comision_empleado_id')
            ->join('users as u', 'u.id', '=', 'ce.users_id')
            ->leftJoin('model_has_roles as mhr', 'mhr.model_id', '=', 'u.id')
            ->leftJoin('roles as r', 'r.id', '=', 'mhr.role_id')
            ->join('producto_comision as pc', 'pc.facturas_comision_id', '=', 'fc.id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'u.id',
                'u.name as usuario',
                DB::raw('COALESCE(r.name, "Sin rol") as rol'),
                DB::raw('SUM(fc.total_comision) as total_comisiones'),
                DB::raw('COUNT(DISTINCT fc.id) as num_facturas'),
                DB::raw('COUNT(DISTINCT pc.producto_id) as num_productos')
            )
            ->groupBy('u.id', 'u.name', 'r.name');

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
            ->leftJoin('categoria as c', 'c.id', '=', 'p.categoria_id')
            ->join('comision_empleado as ce', 'ce.id', '=', 'fc.comision_empleado_id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'p.id',
                'p.nombre as producto',
                DB::raw('COALESCE(c.nombre, "Sin categoría") as categoria'),
                DB::raw('SUM(pc.cantidad) as cantidad_vendida'),
                DB::raw('SUM(pc.monto_comision) as total_comisiones'),
                DB::raw('COUNT(DISTINCT ce.users_id) as num_empleados')
            )
            ->groupBy('p.id', 'p.nombre', 'c.nombre');

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
            ->join('comision_empleado as ce', 'ce.id', '=', 'fc.comision_empleado_id')
            ->join('users as u', 'u.id', '=', 'ce.users_id')
            ->join('venta as v', 'v.id', '=', 'fc.venta_id')
            ->join('cliente as cl', 'cl.id', '=', 'v.cliente_id')
            ->whereBetween('fc.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'fc.id',
                'fc.num_factura as factura',
                'cl.nombre as cliente',
                'u.name as empleado',
                'v.total_venta',
                'fc.total_comision',
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
