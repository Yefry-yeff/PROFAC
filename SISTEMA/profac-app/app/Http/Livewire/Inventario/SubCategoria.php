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
use App\Models\ModelSubCategoria;

class SubCategoria extends Component
{
    public function render()
    {
        return view('livewire.inventario.sub-categoria');
    }

    public function listarSubCategorias(){
        try{

        $sub_categorias = DB::table('sub_categoria')
                                ->join('categoria_producto', 'sub_categoria.categoria_producto_id', '=', 'categoria_producto.id')
                                ->select('sub_categoria.id', 'sub_categoria.descripcion', 'sub_categoria.categoria_producto_id', 'categoria_producto.descripcion as categoria_descripcion')
                                //->where('sub_categoria.estado_id', '=', '1') Eliminar id categoria
                                ->get();

        return Datatables::of($sub_categorias)
                ->addColumn('opciones', function ($sub_categoria) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                        <a class="dropdown-item" onclick="datosSubCategoria('.$sub_categoria->id.')" >
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

    public function listarCategorias(){
        try {
 
         $categorias = DB::SELECT("select id, descripcion from categoria_producto");
 
        return response()->json([
         "categorias"=>$categorias,
        ],200);
        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error', 
         'error' => $e
        ],402);
        }
    }

    public function guardarSubCategoria(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                
                'descripcion_sub_categoria' => 'required',
                'categoria_producto_id' => 'required',
                                  
            ], [
                
                'descripcion_sub_categoria' => 'Requerido',
                'categoria_producto_id' => 'Requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        

            $sub_categoria_create = new ModelSubCategoria;
                
            $sub_categoria_create->descripcion = $request->descripcion_sub_categoria;
            $sub_categoria_create->categoria_producto_id = $request->categoria_producto_id;
                        
            $sub_categoria_create->save();
 
            

             
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Sub-Categoria guardado con exito.'
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
 
         //$datos = DB::SELECTONE("select id, descripcion from subcategoria where id=".$request->id);

         $datos = DB::SELECTONE("select 
                    sub_categoria.id,
                    sub_categoria.descripcion,
                    sub_categoria.categoria_producto_id,
                    categoria_producto.descripcion as categoria_descripcion
                    from sub_categoria
                    inner join categoria_producto
                    on sub_categoria.categoria_producto_id = categoria_producto.id where sub_categoria.id=".$request->id );
 
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

     public function editarSubCategoria(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                'idSubCategoria' => 'required',
                'descripcion_sub_categoria_editar' => 'required',
                'categoria_producto_id_editar' => 'required',
                
                                  
            ], [
                'idSubCategoria' => 'ID es requerido',
                'descripcion_sub_categoria_editar' => 'Requerido',
                'categoria_producto_id_editar' => 'Requerido'                
               
            ]);

 
            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',              
                    'errors' => $validator->errors()
                ], 402);
            }

        
            $sub_categoria_update =  ModelSubCategoria::find($request->idSubCategoria);
                
            $sub_categoria_update->descripcion = $request->descripcion_sub_categoria_editar;
            $sub_categoria_update->categoria_producto_id = $request->categoria_producto_id_editar;
            
            
            $sub_categoria_update->update();
 
            
             
            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>' Sub-Categoria actualizado con exito.'
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
