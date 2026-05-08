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
use App\Models\ModelCodigoExoneracion;

class CodigoExoneracion extends Component
{
    public function render()
    {
        return view('livewire.ventas-estatal.codigo-exoneracion');
    }

    public function listarCodigoExoneracion(){
        try{


        $codigos = DB::table('codigo_exoneracion')
                                ->join('cliente', 'codigo_exoneracion.cliente_id', '=', 'cliente.id')
                                ->select('codigo_exoneracion.id', 'codigo_exoneracion.codigo','codigo_exoneracion.corrOrd','cliente.nombre','estado_id')

                                ->get();

        return Datatables::of($codigos)
                ->addColumn('opciones', function ($codigo) {

                    if($codigo->estado_id==1){
                        return

                        '
                            <div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start"
                                style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                <li>
                                    <a class="dropdown-item" onclick="datosCodigoExonerado('.$codigo->id.')" >
                                        <i class="fa-solid fa-arrows-to-eye text-info"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" onclick="desactivarCodigoExonerado('.$codigo->id.')" >
                                        <i class="fa-solid fa-xmark text-danger"></i> Desactivar
                                    </a>
                                </li>

                            </ul>
                        </div>
                            ';


                    }else{
                        return

                        '
                    <div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle not-allowed" aria-expanded="false" disabled>Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start"
                        style="position: absolute; top: 33px; left: 0px; will-change: top, left;">



                    </ul>
                </div>
                    ';
                }
            })




                ->rawColumns(['opciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarClientes(){
        try {

         $clientes = DB::SELECT("select id, nombre from cliente order by nombre asc");

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

    public function guardarCodigoExoneracion(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required',
                'cliente' => 'required',

            ], [
                'codigo' => 'Codigo es requerido',
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



            $codigo_exonerado = new ModelCodigoExoneracion;

            $codigo_exonerado->codigo = $request->codigo;
            $codigo_exonerado->cliente_id = $request->cliente;
            $codigo_exonerado->corrOrd = $request->corrOrd;
            $codigo_exonerado->estado_id= 1;

            $codigo_exonerado->save();


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

    public function obtenerCodigoExoneracion( Request $request){
        try {

        $datos = DB::SELECTONE("select
                    codigo_exoneracion.id,
                    codigo_exoneracion.codigo,
                    codigo_exoneracion.cliente_id,
                    codigo_exoneracion.corrOrd,
                    cliente.nombre
                    from codigo_exoneracion
                    inner join cliente
                    on codigo_exoneracion.cliente_id = cliente.id where codigo_exoneracion.id=".$request->id );

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

    public function editarCodigoExoneracion(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'idCodigo' => 'required',
                'codigo_editar' => 'required',
                'cliente_editar' => 'required',

            ], [
                'idCodigo' => 'ID es requerido',
                'codigo_editar' => 'Numero es requerido',
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


            $codigo_update =  ModelCodigoExoneracion::find($request->idCodigo);

            $codigo_update->codigo = $request->codigo_editar;
            $codigo_update->cliente_id = $request->cliente_editar;

            $codigo_update->update();


            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Codigo actualizado con exito.'
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

    public function desactivarCodigoExoneracion( Request $request){
        try {

            $codigo_off =  ModelCodigoExoneracion::find($request->id);

            $codigo_off->estado_id = 2;

            $codigo_off->update();

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
