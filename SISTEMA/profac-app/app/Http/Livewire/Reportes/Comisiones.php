<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Exports\LibroCobrosExport;
use Maatwebsite\Excel\Facades\Excel;

class Comisiones extends Component
{
    public function render()
    {
        return view('livewire.reportes.comisiones');
    }
    public function consulta($fechaInicio,$fechaFinal,$vendedor)
    {
        try {
            $consulta = DB::select("Call obt_comis_vend (?, ?,?)", [$fechaInicio,$fechaFinal,$vendedor]);

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
}
