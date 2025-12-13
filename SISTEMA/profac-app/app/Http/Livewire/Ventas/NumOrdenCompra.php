<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;
use App\Models\ModelNumOrdenCompra;

class NumOrdenCompra extends Component
{
    public function render()
    {
        return view('livewire.ventas.num-orden-compra');
    }

    public function listarNumOrdenCompraCoorporativo(){
        try{


        $num_orden_compras = DB::table('numero_orden_compra')
                                ->join('cliente', 'numero_orden_compra.cliente_id', '=', 'cliente.id')
                                ->join('users', 'numero_orden_compra.users_id', '=', 'users.id')
                                ->select('numero_orden_compra.id', 'numero_orden_compra.numero_orden','cliente.nombre', 'users.name','estado_id')
                                ->where('numero_orden_compra.estado_id', '=', '1')          
                                ->where('cliente.tipo_cliente_id', '=', '1')                           
                                ->get();

        return Datatables::of($num_orden_compras)
                ->addColumn('opciones', function ($num_orden_compra) {

                    if($num_orden_compra->estado_id==2){
                        return

                                    '
                            <div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle disabled" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start"
                                style="position: absolute; top: 33px; left: 0px; will-change: top, left;">


                            </ul>
                        </div>
                            ';
                    }{
                        return

                                    '
                            <div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start"
                                style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                <li>
                                    <a class="dropdown-item" onclick="datosNumOrdenCompra('.$num_orden_compra->id.')" >
                                        <i class="fa-solid fa-arrows-to-eye text-info"></i> Editar 
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" onclick="desactivarNumOrdenCompra('.$num_orden_compra->id.')" >
                                        <i class="fa-solid fa-xmark text-danger"></i> Desactivar
                                    </a>
                                </li>

                            </ul>
                        </div>
                            ';
                    }

                })
            

                ->rawColumns(['opciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar los bancos.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarClientesCoorporativo(){
        try {
 
         $clientes = DB::SELECT("select id, nombre from cliente where tipo_cliente_id = 1 order by nombre asc");
 
        return response()->json([
         "clientes"=>$clientes,
        ],200);
        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error', 
         'error' => $e
        ],402);
        }
    }
}
