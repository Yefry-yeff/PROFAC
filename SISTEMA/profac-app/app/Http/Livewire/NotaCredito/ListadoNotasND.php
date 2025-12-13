<?php

namespace App\Http\Livewire\NotaCredito;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DataTables;


use Livewire\Component;

class ListadoNotasND extends Component
{
    public function render()
    {
        $fechaActual = date('n');
        $resta = $fechaActual - 2;

        $mesActual =0;
        $AnioActual = date('Y');

        if($resta<=0){
            $mesActual=12;
            $AnioActual = $AnioActual - 1;
        }elseif($resta>0 && $resta<10){
            $mesActual = '0'.$resta;
        }else{
            $mesActual = date('m');
        }


        $fechaInicio = $AnioActual.'-'.$mesActual.'-01';
        return view('livewire.nota-credito.listado-notas-nd',compact('fechaInicio'));
    }

    public function listadoNotaCreditoND(Request $request){
        try{
            $listado = DB::SELECT("
            select
            fa.id as idFactura,
            A.id as codigo,
            A.cai,
            cli.nombre as cliente,
            fa.cai as factura,
            B.descripcion as motivo,
            A.comentario,
            A.sub_total as sub_total,
            A.isv as isv,
            A.total as total,
            A.created_at as fecha_registro,
            name as registrado_por
            from nota_credito A
            inner join motivo_nota_credito B on A.motivo_nota_credito_id = B.id
            inner join users on A.users_id = users.id
            inner join factura fa on fa.id = A.factura_id
            inner join cliente cli on cli.id = fa.cliente_id
            where

            fa.tipo_venta_id = 1
            and fa.estado_venta_id <> 2
            
            and estado_nota_id <>2
            and fecha BETWEEN '".$request->fechaInicio."' and '".$request->fechaFinal."'"
            );

            /* A.estado_nota_dec = 2 */
            return Datatables::of($listado)
            ->addColumn('opciones', function ($nota) {

               
                    return

                    '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
    
                        
                        <li><a class="dropdown-item" onclick="anularNota('.$nota->codigo.','.$nota->idFactura.' )" class="btn btn-sm btn-warning "><i class="fa-solid fa-trash"></i> Anular</a></li>
    
    
    
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Orginal</a></li>
    
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/copia/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Copia</a></li>
    
                        </ul>
    
    
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
