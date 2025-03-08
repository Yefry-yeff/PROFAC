<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class Librocobrosrep extends Component
{
    public function render()
    {
        return view('livewire.reportes.librocobrosrep');
    }


    public function consulta($tipo, $fecha)
    {
        try {
            if (!$tipo || !$fecha) {
                return response()->json([
                    'message' => 'Faltan parámetros requeridos para la consulta.'
                ], 400);
            }

            $consulta = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fecha, $fecha]);
            return datatables()->of($consulta)->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al listar el reporte solicitado.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }

    public function exportarPdf($tipo, $fecha)
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
            $pdf = Pdf::loadView('pdf.librocobrosrep', compact('data', 'fecha'))
                ->setPaper('a4', 'landscape');

            // Retornar el PDF para descarga
            return $pdf->download("LibroCobros_{$fecha}.pdf");
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el PDF.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }

}
