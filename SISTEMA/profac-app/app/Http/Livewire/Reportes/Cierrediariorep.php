<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

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
}
