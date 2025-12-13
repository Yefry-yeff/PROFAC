<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Auth;
use DataTables;
use Validator;
use Illuminate\Support\Facades\File;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

class ListadoAjustes extends Component
{
    public function render()
    {
        $fechaActual = date('n');
        $resta = $fechaActual - 2;
        $anio = date("Y");

        if($resta<=0){
            $resta=12;
            $anio = $anio - 1;
        }

        if($resta<10){
            $resta = '0'.$resta;
        }

        $fechaInicio = $anio.'-'.$resta.'-01';
       
        return view('livewire.inventario.listado-ajustes',compact('fechaInicio'));
    }

    public function listarAjustes(Request $request){
   
        try{
         
        $listado = DB::SELECT("
        select
        ajuste.id as codigo,
        comentario,
        tipo_ajuste.nombre as motivo,
        numero_ajuste,
        fecha,
        name,
        ajuste.created_at
        from ajuste
        inner join users
        on users.id = ajuste.users_id
        inner join tipo_ajuste
        on ajuste.tipo_ajuste_id = tipo_ajuste.id
        where fecha BETWEEN '".$request->fechaInicio."' and '".$request->fechaFinal."'"
        );

        

        return Datatables::of($listado)
        ->addColumn('opciones', function ($ajuste) {

            return

            '<div class="text-center">
    
            <a href="/ajustes/imprimir/ajuste/'.$ajuste->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir</a>
     
     
                
            </div>';
        })

        ->rawColumns(['opciones',])
        ->make(true);

     

       } catch (QueryException $e) {
       return response()->json([
        'icon' => '',
        'text' => '',
        'title' => '',
        'message' => 'Ha ocurrido un error', 
        'error' => $e,
       ],402);
       }
    }
}
