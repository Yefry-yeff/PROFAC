<?php

namespace App\Http\Livewire;

use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;
use App\Models\ModelBanco;


class Bancos extends Component
{
    public function render()
    {
        return view('livewire.bancos');
    }

    public function listarBancos(){
        try{

        $bancos = DB::SELECT("select
        banco.id,
        banco.nombre,
        banco.cuenta,
        users.name
        from banco
        inner join users
        on banco.users_id = users.id");

        return Datatables::of($bancos)
                ->addColumn('opciones', function ($banco) {
                    return '
                <div style="display:flex; gap:5px; justify-content:center;">
                    <button onclick="datosBanco('.$banco->id.')"
                            class="banco-btn-edit"
                            title="Editar banco">
                        <i class="fa fa-pencil mr-1"></i>Editar
                    </button>
                </div>';
                })
                ->editColumn('id', function ($banco) {
                    return '<span class="banco-badge-cod">#'.$banco->id.'</span>';
                })


                ->rawColumns(['opciones', 'id'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar los bancos.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function guardarBanco(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'nombre_banco' => 'required',
                'cuenta' => 'required',

            ], [
                'nombre_banco' => 'Nombre Banco es requerido',
                'cuenta' => 'Cuenta es requerido'

            ]);


            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',
                    'errors' => $validator->errors()
                ], 402);
            }



            $banco = new ModelBanco;

            $banco->nombre = $request->nombre_banco;
            $banco->cuenta = $request->cuenta;
            $banco->users_id = Auth::user()->id;

            $banco->save();




            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Banco guardado con exito.'
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

    public function obtenerDatos( Request $request){
        try {

         $datos = DB::SELECTONE("select id, nombre, cuenta from banco where id=".$request->id);

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

     public function editarBanco(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'idBanco' => 'required',
                'nombre_banco_editar' => 'required',
                'cuenta_editar' => 'required',

            ], [
                'idBanco' => 'ID es requerido',
                'nombre_banco_editar' => 'Nombre Banco es requerido',
                'cuenta_editar' => 'Cuenta es requerido'

            ]);


            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',
                    'errors' => $validator->errors()
                ], 402);
            }


            $banco_update =  ModelBanco::find($request->idBanco);

            $banco_update->nombre = $request->nombre_banco_editar;
            $banco_update->cuenta = $request->cuenta_editar;

            $banco_update->update();



            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Banco actualizado con exito.'
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

}
