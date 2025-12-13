<?php

namespace App\Http\Livewire\Inventario;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\File;
use App\Models\ModelMarca;


use Livewire\Component;

class Marca extends Component
{
    public function render()
    {
        return view('livewire.inventario.marca');
    }

    public function listarMarcas(){
        try{

        $marcas = DB::SELECT("select 
        marca.id,
        marca.nombre,
        marca.created_at,
        users.name
        from marca
        inner join users
        on marca.users_id = users.id");

        return Datatables::of($marcas)
                ->addColumn('opciones', function ($marca) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                        <a class="dropdown-item" onclick="datosMarca('.$marca->id.')" >
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
                'message' => 'Ha ocurrido un error al listar los productos.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function guardarMarca(Request $request){
      
       try {
        $name = null;
        $validator = Validator::make($request->all(), [
            'nombre_producto' => 'required',          
            

        ], [
            'nombre_producto' => 'Nombre es requerido',
           
        ]);

        if ($validator->fails()) {
            return response()->json([
                'icon'=>'error',
                'title'=>'Error',
                'text'=>'Ha ocurrido un error, el nombre de marca es obligatorio.',              
                'errors' => $validator->errors()
            ], 402);
        }

        if ($request->file('files') <> null) {
          
            $files =  $request->file('files');
            $file =  $files[0];

            $name = 'IMG_'. time().".".$file->getClientOriginalExtension();
            $path = public_path() . '/marcas';
            $url =  $name;
            $file->move($path, $name);

            $marca = new ModelMarca;
            $marca->nombre = trim($request['nombre_producto']);
            $marca->url_img = $url;
            $marca->users_id = Auth::user()->id;
            $marca->save();

            return response()->json([
                'icon'=>'success',
                'title'=>'Exito!',
                'text'=>'Marca guardada con exito.'
            ],200);

        }else{
            $marca = new ModelMarca;
            $marca->nombre = trim($request->nombre_producto);
           
            $marca->users_id =Auth::user()->id;
            $marca->save();

            return response()->json([
                'icon'=>'success',
                'title'=>'Exito!',
                'text'=>'Marca guardada con exito.'
            ],200);

        }



       } catch (QueryException $e) {

        if ($request->file('files') <> null){
            $carpetaPublic = public_path();
            $path = $carpetaPublic.'/marcas/'.$name;
    
            File::delete($path);
    
        }

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

        $datos = DB::SELECTONE("select id, nombre, url_img from marca where id=".$request->id);

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

    public function editarMarca(Request $request){
      
        try {
         $name = null;
         $validator = Validator::make($request->all(), [
             'nombre_producto_editar' => 'required',  
             'idMarca' => 'required',          
             
 
         ], [
             'nombre_producto_editar' => 'Nombre es requerido',
             'idMarca'=>'ID es obligatorio'
            
         ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'icon'=>'error',
                 'title'=>'Error',
                 'text'=>'Ha ocurrido un error, el nombre de marca es obligatorio.',              
                 'errors' => $validator->errors()
             ], 402);
         }
 
         if ($request->file('files') <> null) {
           
             $files =  $request->file('files');
             $file =  $files[0];
 
             $name = 'IMG_'. time().".".$file->getClientOriginalExtension();
             $path = public_path() . '/marcas';
             $url =  $name;
             $file->move($path, $name);
 
             $marca =  ModelMarca::find($request->idMarca);

             
             if($marca->url_img){
                $carpetaPublic = public_path();
                $path = $carpetaPublic.'/marcas/'.$marca->url_img;
                File::delete($path);
             }


             $marca->nombre = trim($request['nombre_producto_editar']);
             $marca->url_img = $url;
             $marca->users_id = Auth::user()->id;
             $marca->save();
 
             return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Marca guardada con exito.'
             ],200);
 
         }else{
             $marca =  ModelMarca::find($request->idMarca);
           // dd($marca->url_img);
             if($marca->url_img){
                $carpetaPublic = public_path();
                $path = $carpetaPublic.'/marcas/'.$marca->url_img;
                File::delete($path);
             }

             $marca->nombre = $request['nombre_producto_editar'];            
             $marca->users_id =Auth::user()->id;
             $marca->save();
 
             return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Marca guardada con exito.'
             ],200);
 
         }
 
 
 
        } catch (QueryException $e) {
 
         if ($request->file('files') <> null){
             $carpetaPublic = public_path();
             $path = $carpetaPublic.'/marcas/'.$name;
     
             File::delete($path);
     
         }
 
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
