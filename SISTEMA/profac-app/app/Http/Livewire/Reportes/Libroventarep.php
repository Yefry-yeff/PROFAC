<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Exports\LibroVentaExport;
use Maatwebsite\Excel\Facades\Excel;

class Libroventarep extends Component
{
    public function render()
    {
        $clientes    = DB::select("SELECT id, nombre FROM cliente ORDER BY nombre ASC");
        $vendedores  = DB::select("SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC");
        $modosPago   = DB::select("SELECT id, descripcion FROM tipo_pago_venta ORDER BY descripcion ASC");
        return view('livewire.reportes.libroventarep', compact('clientes', 'vendedores', 'modosPago'));
    }


    public function consulta(Request $request, $tipo = null, $fechaInicio = null, $fechaFinal = null)
    {
        try {
            // Obtener filtros desde la request (query string o parámetros)
            $tipo = $request->input('tipo', $tipo ?? '4');
            $fechaInicio = $request->input('fecha_desde', $fechaInicio ?? '1900-01-01');
            $fechaFinal = $request->input('fecha_hasta', $fechaFinal ?? date('Y-m-d'));
            $cliente = $request->input('cliente');
            $vendedor = $request->input('vendedor');
            $factura = $request->input('factura');
            $modoPago = $request->input('modo_pago');

            // Si fecha_desde está vacía, buscar desde el inicio; si fecha_hasta está vacía, usar hoy
            if ($request->has('fecha_desde') && !$request->input('fecha_desde')) {
                $fechaInicio = '1900-01-01';
            }
            if ($request->has('fecha_hasta') && !$request->input('fecha_hasta')) {
                $fechaFinal = date('Y-m-d');
            }

            // Consulta base con filtros
            $query = DB::table('factura')
                ->leftJoin('cliente', 'factura.cliente_id', '=', 'cliente.id')
                ->leftJoin('users', 'factura.vendedor', '=', 'users.id')
                ->leftJoin('tipo_pago_venta', 'factura.tipo_pago_id', '=', 'tipo_pago_venta.id')
                ->select(
                    'users.name as VENDEDOR',
                    'cliente.nombre as CLIENTE',
                    'factura.numero_secuencia_cai as FACTURA',
                    DB::raw("ROUND(CASE WHEN factura.tipo_venta_id = 3 THEN COALESCE(factura.sub_total, 0) ELSE 0 END, 2) as EXONERADO"),
                    DB::raw("ROUND(factura.sub_total_grabado, 2) as GRAVADO"),
                    DB::raw("ROUND(factura.sub_total_excento, 2) as EXCENTO"),
                    DB::raw("ROUND(factura.sub_total, 2) as SUBTOTAL"),
                    DB::raw("ROUND(CASE WHEN factura.tipo_venta_id = 3 THEN 0 ELSE COALESCE(factura.isv, 0) END, 2) as ISV"),
                    DB::raw("ROUND(factura.total, 2) as TOTAL"),
                    'factura.fecha_emision as FECHA COMPRA'
                )
                ->whereBetween('factura.fecha_emision', [$fechaInicio, $fechaFinal])
                ;

            // Aplicar filtros adicionales
            if ($cliente) {
                $query->where('factura.cliente_id', $cliente);
            }
            if ($vendedor) {
                $query->where('factura.vendedor', $vendedor);
            }
            if ($factura) {
                $query->where('factura.numero_secuencia_cai', 'LIKE', '%' . $factura . '%');
            }
            if ($modoPago) {
                $query->where('factura.tipo_pago_id', $modoPago);
            }

            $consulta = $query->orderBy('factura.fecha_emision', 'DESC')->get();

            return Datatables::of($consulta)
                ->rawColumns([])
                ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al listar el reporte solicitado.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }

    public function exportarPdf(Request $request, $tipo, $fechaInicio,$fechaFinal)
    {
        try {
 // Validación de parámetros
 if (!$tipo || !$fechaInicio ||!$fechaFinal ) {
    return response()->json([
        'message' => 'Faltan parámetros requeridos para la exportación del PDF.'
    ], 400);
}

// Obtener datos del procedimiento almacenado
$consulta = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fechaInicio,$fechaFinal]);

// Convertir los datos a arreglo para la vista
$data = json_decode(json_encode($consulta), true);

// Generar el PDF usando DomPDF
$pdf = PDF::loadView('pdf.libroventarep', compact('data','fechaInicio','fechaFinal'))
          ->setPaper('oficio', 'landscape');

// Retornar el PDF para descarga
return $pdf->download("Libroventa_{$fechaInicio}_a_{$fechaFinal}.pdf");

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el PDF.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }
    public function exportarExcel(Request $request, $tipo, $fechaInicio, $fechaFinal)
    {
        try {
            if (!$tipo || !$fechaInicio || !$fechaFinal) {
                return response()->json([
                    'message' => 'Faltan parámetros requeridos para la exportación del Excel.'
                ], 400);
            }

            $consulta = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fechaInicio, $fechaFinal]);
            $data = json_decode(json_encode($consulta), true);

            return Excel::download(new LibroVentaExport($data, $fechaInicio, $fechaFinal), "LibroVenta_{$fechaInicio}_a_{$fechaFinal}.xlsx");

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el Excel.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }
}
