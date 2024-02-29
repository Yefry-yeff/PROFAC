<?php

namespace App\Http\Livewire\VentasEstatal;

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
        return view('livewire.ventas-estatal.num-orden-compra');
    }

    public function listarNumOrdenCompra(){
        try{


        $num_orden_compras = DB::table('numero_orden_compra')
                                ->join('cliente', 'numero_orden_compra.cliente_id', '=', 'cliente.id')
                                ->join('users', 'numero_orden_compra.users_id', '=', 'users.id')
                                ->select('numero_orden_compra.id', 'numero_orden_compra.numero_orden','cliente.nombre', 'users.name','estado_id')
                                ->where('numero_orden_compra.estado_id', '=', '1')
                                ->where('cliente.tipo_cliente_id', '=', '2')
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

    public function listarClientes(){
        try {

         $clientes = DB::SELECT("select id, nombre from cliente where tipo_cliente_id = 2 order by nombre asc");

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

    public function guardarNumOrdenCompra(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'numero_orden' => 'required',
                'cliente' => 'required',

            ], [
                'numero_orden' => 'Numero es requerido',
                'cliente' => 'Cliente es requerido'

            ]);


            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',
                    'errors' => $validator->errors()
                ], 402);
            }


            $numOrdenValidador = DB::SELECTONE("select count(*) as existe from numero_orden_compra
             where numero_orden = ".$request->numero_orden);

            if($numOrdenValidador->existe > 0){

                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'No puede registrar mas de una vez el numero de orden de compra.',
                    'errors' => $validator->errors()
                ], 402);
            }

            $num_orden = new ModelNumOrdenCompra;

            $num_orden->numero_orden = $request->numero_orden;
            $num_orden->cliente_id = $request->cliente;
            $num_orden->estado_id= 1;
            $num_orden->users_id = Auth::user()->id;

            $num_orden->save();


            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Numero guardado con exito.'
            ],200);

        } catch (QueryException $e) {

        return response()->json([
         'icon'=>'error',
         'title'=>'Error!',
         'text'=>'Ha ocurrido un error, intente de nuevo.',
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }

    }

    public function obtenerNumOrdenCompra( Request $request){
        try {

        $datos = DB::SELECTONE("select
                    numero_orden_compra.id,
                    numero_orden_compra.numero_orden,
                    numero_orden_compra.cliente_id,
                    cliente.nombre
                    from numero_orden_compra
                    inner join cliente
                    on numero_orden_compra.cliente_id = cliente.id where numero_orden_compra.id=".$request->id );

        return response()->json([
         "datos"=>$datos,
        ],200);
        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
    }

    public function editarNumOrdenCompra(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'idNumOrden' => 'required',
                'numero_orden_editar' => 'required',
                'cliente_editar' => 'required',

            ], [
                'idNumOrden' => 'ID es requerido',
                'numero_orden_editar' => 'Numero es requerido',
                'cliente_editar' => 'Cliente es requerido'

            ]);


            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',
                    'errors' => $validator->errors()
                ], 402);
            }


            $num_orden_update =  ModelNumOrdenCompra::find($request->idNumOrden);

            $num_orden_update->numero_orden = $request->numero_orden_editar;
            $num_orden_update->cliente_id = $request->cliente_editar;
            $num_orden_update->users_id = Auth::user()->id;


            $num_orden_update->update();


            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Numero Orden actualizado con exito.'
            ],200);

        } catch (QueryException $e) {

        return response()->json([
         'icon'=>'error',
         'title'=>'Error!',
         'text'=>'Ha ocurrido un error, intente de nuevo.',
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }

    }

    public function desactivarNumOrdenCompra( Request $request){
        try {

            $num_orden_off =  ModelNumOrdenCompra::find($request->id);

            $num_orden_off->estado_id = 2;

            $num_orden_off->update();

            return response()->json([
                'icon'=>'success',
                'title'=>'Exito!',
                'text'=>'Numero Orden desactivado con exito.'
           ],200);

        } catch (QueryException $e) {
            return response()->json([
             'message' => 'Ha ocurrido un error',
             'error' => $e
            ],402);
        }
    }

}
