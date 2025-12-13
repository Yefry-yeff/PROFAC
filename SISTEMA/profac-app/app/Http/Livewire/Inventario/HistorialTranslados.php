<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;

class HistorialTranslados extends Component
{
    public function render()
    {

        $fechaActual = date('n');
        $resta = $fechaActual - 2;
        if($resta<=0){
            $resta=12;
        }

        if($resta<10){
            $resta = '0'.$resta;
        }

        $fechaInicio = date('Y').'-'.$resta.'-01';
        return view('livewire.inventario.historial-translados',compact('fechaInicio'));
    }

    public function historialTranslados(Request $request){
       try {

        $listado = DB::SELECT("
        select
        D.id,
        D.codigo,
        C.nombre,
        A.cantidad,
        users.name,
        D.created_at

        from translado D
        inner join log_translado A
        on D.id = A.translado_id
        inner join recibido_bodega B
        on A.origen = B.id
        inner join producto C
        on B.producto_id = C.id
        inner join users
        on A.users_id = users.id
        where A.descripcion='Translado de bodega' and DATE(A.created_at) BETWEEN '".$request->fechaInicio."' and '".$request->fechaFinal."'
        ");

        return Datatables::of($listado)
        ->addColumn('opciones', function ($translado) {

            return

            '<div class="text-center">

            <a href="/translado/imprimir/'.$translado->id.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir</a>



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
