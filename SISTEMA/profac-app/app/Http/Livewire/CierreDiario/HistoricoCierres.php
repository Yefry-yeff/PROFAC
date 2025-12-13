<?php

namespace App\Http\Livewire\CierreDiario;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelParametro;
use App\Models\ModelLista;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\User;

use App\Models\CierreDiario as ModelCierreDiario;
use App\Models\bitacoraCierre;

use App\Exports\CierreExport;
use App\Exports\CierreExportGeneral;


class HistoricoCierres extends Component
{
    public function render()
    {
        return view('livewire.cierre-diario.historico-cierres');
    }

    public function listadoHistorico(){
        try {

                //dd($fecha);

            $consulta = DB::SELECT("
                select
                    bitacoracierre.id,
                    bitacoracierre.fechaCierre,
                    users.name as 'user_cierre_id',
                    bitacoracierre.comentario,
                    bitacoracierre.estado_cierre,
                    bitacoracierre.totalContado,
                    bitacoracierre.totalCredito,
                    bitacoracierre.totalAnulado,
                    bitacoracierre.created_at
                from bitacoracierre
                inner join users on users.id = bitacoracierre.user_cierre_id
                where bitacoracierre.estado_cierre = 1;
                ");

        return Datatables::of($consulta)
        ->addColumn('acciones', function($consulta){
                return '<a class="btn btn-success" href="/cajaChica/excel/'.$consulta->id.'"> Reporte <i class="fa-solid fa-file-excel"></i></a>';

        })
        ->rawColumns(['acciones'])
        ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function export($bitacoraCierre){
        try {
            return Excel::download(new CierreExport($bitacoraCierre), 'Bitacora-'.$bitacoraCierre.'.xlsx');

        } catch (QueryException $e) {
            return response()->json([

                'error' => $e,
                "text" => "Ha ocurrido un error al generar el reporte de la bitacora #".$bitacoraCierre,
                "icon" => "error",
                "title"=>"Error!"
            ],402);
        }

    }

    public function exportGeneral(){
        try {
            return Excel::download(new CierreExportGeneral, 'Bitacora-General.xlsx');

        } catch (QueryException $e) {
            return response()->json([

                'error' => $e,
                "text" => "Ha ocurrido un error al generar el reporte de la bitacora general",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
        }

    }
}
