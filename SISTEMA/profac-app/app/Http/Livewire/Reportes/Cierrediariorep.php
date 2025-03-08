<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CierreDiariorep extends Component
{
    public function render()
    {
        return view('livewire.reportes.cierrediariorep');
    }

    public function consulta($tipo, $fecha)
    {
        try {
            // Pasamos los dos parámetros al procedimiento almacenado
            $consulta = DB::select("Call sp_reportesxfecha (?, ?,?)", [$tipo, $fecha, $fecha]);

            return Datatables::of($consulta)
                ->rawColumns([])
                ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }
    public function exportarPdf(Request $request, $tipo, $fecha)
{
    try {
        // Validación de parámetros
        if (!$tipo || !$fecha) {
            return response()->json([
                'message' => 'Faltan parámetros requeridos para la exportación del PDF.'
            ], 400);
        }

        // Obtener datos del procedimiento almacenado
        $consulta = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fecha, $fecha]);

        // Convertir los datos a arreglo para la vista
        $data = json_decode(json_encode($consulta), true);

        // Generar el PDF usando DomPDF
        $pdf = PDF::loadView('pdf.cierrediariorep', compact('data', 'fecha'))
                  ->setPaper('a4', 'landscape');

        // Retornar el PDF para descarga
        return $pdf->download("Cierre_De_Caja_{$fecha}.pdf");
    } catch (QueryException $e) {
        return response()->json([
            'message' => 'Error al generar el PDF.',
            'errorTh' => $e->getMessage(),
        ], 402);
    }
}
}
