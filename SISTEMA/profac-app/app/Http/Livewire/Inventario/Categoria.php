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
use App\Models\ModelCategoria;

class Categoria extends Component
{
    public function render()
    {
        return view('livewire.inventario.categoria');
    }

    public function listarCategorias(){
        try{

        $categorias = DB::SELECT("select 
        id,
        descripcion
        from categoria_producto ");

        return Datatables::of($categorias)
                ->addColumn('opciones', function ($categoria) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                        <a class="dropdown-item" onclick="datosCategoria('.$categoria->id.')" >
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
                'message' => 'Ha ocurrido un error al listar las categorias.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function guardarCategoria(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                
                'descripcion_categoria' => 'required',
                                  
            ], [
                
                'descripcion_categoria' => 'Requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        

            $categoria = new ModelCategoria;
                
            $categoria->descripcion = $request->descripcion_categoria;
            
            
            $categoria->save();
 
            

             
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Categoria guardado con exito.'
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
 
         $datos = DB::SELECTONE("select id, descripcion from categoria_producto where id=".$request->id);
 
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

     public function editarCategoria(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                'idCategoria' => 'required',
                'descripcion_categoria_editar' => 'required',
                
                                  
            ], [
                'idCategoria' => 'ID es requerido',
                'descripcion_categoria_editar' => 'Nombre Banco es requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        
            $categoria_update =  ModelCategoria::find($request->idCategoria);
                
            $categoria_update->descripcion = $request->descripcion_categoria_editar;
            
            
            $categoria_update->update();
 
            
             
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'categoria actualizado con exito.'
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
