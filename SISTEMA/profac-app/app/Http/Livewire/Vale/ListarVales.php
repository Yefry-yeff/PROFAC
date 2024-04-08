<?php

namespace App\Http\Livewire\Vale;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;

class ListarVales extends Component
{
    public function render()
    {
        return view('livewire.vale.listar-vales');
    }

    public function MostrarlistarVales(){
       try {

        $listaVales = DB::SELECT("
        select
        @i := @i + 1 as contador,
         vale.id,
         vale.numero_vale,
         factura.nombre_cliente,
         factura.rtn,
         factura.numero_factura,
         format(vale.sub_total,2) as sub_total,
         format(vale.isv,2) as isv,
         format(vale.total,2) as total,
         vale.estado_id,
         name,
         vale.created_at
       from vale
       inner join factura
       on vale.factura_id = factura.id
       inner join users
       on vale.users_id = users.id


       cross join (select @i := 0) r

        ");
     /*where  YEAR(vale.created_at) = 2023*/
        return Datatables::of($listaVales)
        ->addColumn('opciones', function ($vale) {

            if($vale->estado_id <> 1){
                return

                '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                    <li>
                        <a class="dropdown-item" href="/imprimir/entrega/'.$vale->id.'"> <i class="fa-solid  fa-print text-success"></i> Imprimir Entrega Original</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="/imprimir/entrega/'.$vale->id.'"> <i class="fa-solid  fa-print text-success"></i> Imprimir Entrega Copia</a>
                    </li>

                    </ul>
                </div>';
            }else{
                return

                '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">


                        <li>
                        <a class="dropdown-item" onclick="anularValeMensaje('.$vale->id.')"> <i class="fa-solid fa-ban text-warning"></i> Anular Vale </a>
                        </li>

                        <li>
                        <a class="dropdown-item" onclick="eliminarValeMensaje('.$vale->id.')"> <i class="fa-solid fa-trash-can text-danger"></i> Eliminar Vale </a>
                        </li>

                        <li>
                             <a class="dropdown-item" href="/imprimir/entrega/'.$vale->id.'"> <i class="fa-solid  fa-print text-success"></i> Imprimir Entrega</a>
                         </li>
                    </ul>
                </div>';
            }


        })
        ->addColumn('estado_entrega', function ($vale) {


            if($vale->estado_id == 1){

                return
                '
                <p class="text-center"><span class="badge badge-warning p-2" style="font-size:0.75rem">Pendiente</span></p>

                ';

            }else if($vale->estado_id == 2){
                return
                '
                <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Anulado</span></p>
                ';
            }else{
                return
                '
                <p class="text-center" ><span class="badge badge-danger p-2" style="font-size:0.75rem">Eliminado</span></p>
                ';
            }

       })
        ->rawColumns(['opciones','estado_entrega'])
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
