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
use App\Models\ModelUnidadMedida;

class UnidadesMedida extends Component
{
    public function render()
    {
        return view('livewire.inventario.unidades-medida');
    }


    public function listarUnidades(){
        try{

        $unidades = DB::SELECT("select 
        id,
        nombre,
        simbolo
        from unidad_medida
        ");

        return Datatables::of($unidades)
                ->addColumn('opciones', function ($unidad) {

                    return

                        '
                <div class="btn-group ">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                        <a class="dropdown-item" onclick="datosUnidad('.$unidad->id.')" >
                            <i class="fa-solid fa-arrows-to-eye text-info"></i> Editar 
                        </a>
                    </li>
                    <li>
                    <a class="dropdown-item"   ><i class="fa-solid fa-xmark text-danger"></i> Desactivar </a>
                    </li>

                </ul>
            </div>
                ';
                })
            

                ->rawColumns(['opciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las unidades.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function guardarUnidad(Request $request){
      
        try {
         $name = null;
         $validator = Validator::make($request->all(), [
             'nombre_producto' => 'required',          
             
 
         ], [
             'nombre_producto' => 'Nombre es requerido',
            
         ], [
            'simbolo_producto' => 'required',          
            

        ], [
            'simbolo_producto' => 'Simbolo es requerido',
           
        ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'icon'=>'error',
                 'title'=>'Error',
                 'text'=>'Ha ocurrido un error, el nombre de unidad es obligatorio.',              
                 'errors' => $validator->errors()
             ], 402);
        }
 
            $unidad = new ModelUnidadMedida;
            $unidad->unidad = 1;
            $unidad->nombre = trim($request['nombre_producto']);
            $unidad->simbolo =trim($request['simbolo_producto']);//$request->simbolo_producto;
            $unidad->save();
 
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Unidad guardada con exito.'
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
 
         $datos = DB::SELECTONE("select id, nombre, simbolo from unidad_medida where id=".$request->id);
 
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

     public function editarUnidad(Request $request){
      
        try {
         
         $validator = Validator::make($request->all(), [
             'nombre_producto_editar' => 'required',
             'simbolo_producto_editar' => 'required',  
             'idUnidad' => 'required',          
             
 
         ], [
             'nombre_producto_editar' => 'Nombre es requerido',
             'simbolo_producto_editar' => 'Simbolo es requerido',
             'idUnidad'=>'ID es obligatorio'
            
         ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'icon'=>'error',
                 'title'=>'Error',
                 'text'=>'Ha ocurrido un error, el nombre y simbolo de la unidad es obligatorio.',              
                 'errors' => $validator->errors()
             ], 402);
         }

            $unidad = ModelUnidadMedida::find($request->idUnidad);
            $unidad->nombre = trim($request['nombre_producto_editar']);
            $unidad->simbolo = trim($request['simbolo_producto_editar']);//$request->simbolo_producto;
            $unidad->save();
 

         return response()->json([
             'icon'=>'success',
             'title'=>'Exito!',
             'text'=>'Unidad actualizada con exito.'
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
