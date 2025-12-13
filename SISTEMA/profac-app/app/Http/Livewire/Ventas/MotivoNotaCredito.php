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
use App\Models\ModelMotivoNotaCredito;

class MotivoNotaCredito extends Component
{
    public function render()
    {
        return view('livewire.ventas.motivo-nota-credito');
    }

    public function listarMotivoNotaCredito(){
        try{


        $motivos = DB::table('motivo_nota_credito')
                                ->join('users', 'motivo_nota_credito.users_id', '=', 'users.id')
                                ->select('motivo_nota_credito.id', 'motivo_nota_credito.descripcion','users.name')
                                ->get();

        return Datatables::of($motivos)
                ->addColumn('opciones', function ($motivo) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                        <a class="dropdown-item" onclick="datosMotivoNotaCredito('.$motivo->id.')" >
                            <i class="fa-solid fa-arrows-to-eye text-info"></i> Editar 
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" onclick="desactivarMotivoNotaCredito('.$motivo->id.')" >
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


    public function guardarMotivoNotaCredito(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                'descripcion' => 'required',
                
                                  
            ], [
                'descripcion' => 'descripcion es requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        

            $motivo_nota_credito = new ModelMotivoNotaCredito;
                
            $motivo_nota_credito->descripcion = $request->descripcion;
            $motivo_nota_credito->users_id= Auth::user()->id;
                        
            $motivo_nota_credito->save();
 
             
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Motivo guardado con exito.'
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

    public function obtenerMotivoNotaCredito( Request $request){
        try {
 
        $datos = DB::SELECTONE("select id, descripcion from motivo_nota_credito where id=".$request->id );
 
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

    public function editarMotivoNotaCredito(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                'idMotivo' => 'required',
                'descripcion_editar' => 'required',
                
                                  
            ], [
                'idMotivo' => 'ID es requerido',
                'descripcion_editar' => 'Numero es requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        
            $motivo_nota_credito_update =  ModelMotivoNotaCredito::find($request->idMotivo);
                
            $motivo_nota_credito_update->descripcion = $request->descripcion_editar;
            $motivo_nota_credito_update->users_id = Auth::user()->id;           
                                   
            $motivo_nota_credito_update->update();
 
            
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Motivo actualizado con exito.'
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
