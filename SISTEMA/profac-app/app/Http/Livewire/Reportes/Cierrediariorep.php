<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use PDF;
use Yajra\DataTables\DataTables;
use Luecano\NumeroALetras\NumeroALetras;
class CierreDiariorep extends Component
{
    public function render()
    {
        return view('livewire.reportes.cierrediariorep');
    }

    public function consulta($fecha)
    {
        try {
            $consulta = DB::select("CALL sp_repo_Cirre_Diario(?)", [$fecha]);

            return Datatables::of($consulta)
                ->rawColumns([])
                ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e,
            ], 402);
        }
    }

}
