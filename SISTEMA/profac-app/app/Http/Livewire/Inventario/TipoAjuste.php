<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;
use App\Models\ModelTipoAjuste;

class TipoAjuste extends Component
{
    public function render()
    {
        return view('livewire.inventario.tipo-ajuste');
    }

    public function listarTipoAjuste(){
        try{


        $tipos = DB::table('tipo_ajuste')
                                ->join('users', 'tipo_ajuste.users_id', '=', 'users.id')
                                ->select('tipo_ajuste.id', 'tipo_ajuste.nombre','users.name')
                                ->get();

        return Datatables::of($tipos)
                ->addColumn('opciones', function ($tipo) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                        <a class="dropdown-item" onclick="datosTipoAjuste('.$tipo->id.')" >
                            <i class="fa-solid fa-arrows-to-eye text-info"></i> Editar 
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" onclick="desactivarTipoAjuste('.$tipo->id.')" >
                            <i class="fa-solid fa-xmark text-danger"></i> Desactivar
                        </a>
                    </li>

                </ul>
            </div>
                ';
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


    public function guardarTipoAjuste(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required',
                
                                  
            ], [
                'nombre' => 'nombre es requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        

            $tipo_ajuste = new ModelTipoAjuste;
                
            $tipo_ajuste->nombre = $request->nombre;
            $tipo_ajuste->users_id= Auth::user()->id;
                        
            $tipo_ajuste->save();
 
             
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Tipo guardado con exito.'
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

    public function obtenerTipoAjuste( Request $request){
        try {
 
        $datos = DB::SELECTONE("select id, nombre from tipo_ajuste where id=".$request->id );
 
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

    public function editarTipoAjuste(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                'idAjuste' => 'required',
                'nombre_editar' => 'required',
                
                                  
            ], [
                'idAjuste' => 'ID es requerido',
                'nombre_editar' => 'Numero es requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        
            $tipo_update =  ModelTipoAjuste::find($request->idAjuste);
                
            $tipo_update->nombre = $request->nombre_editar;
            $tipo_update->users_id = Auth::user()->id;           
                                   
            $tipo_update->update();
 
            
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Tipo actualizado con exito.'
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

    /*public function desactivarTipoAjuste( Request $request){
        try {
 
            $tipo_off =  ModelTipoAjuste::find($request->id);
                
            $tipo_off->estado_id = 2;           
                                   
            $tipo_off->update();
             
            return response()->json([
                'icon'=>'success',
                'title'=>'Exito!',
                'text'=>'Tipo Orden desactivado con exito.'
           ],200);
 
        } catch (QueryException $e) {
            return response()->json([
             'message' => 'Ha ocurrido un error', 
             'error' => $e
            ],402);
        }
    }*/


}
