<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use App\Models\User;
use App\Models\Modelproveedores;
use App\Models\TipoPersonalidad;
use App\Models\Categoria;
use App\Models\Retenciones;
use App\Models\RetencionesProveedores;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;


use DataTables;



class Proveedores extends Component
{
    public function render()
    {
        $users = User::all();
        $paises = Pais::all();
        $categorias = Categoria::all();
        $retenciones = Retenciones::all();
        $tipoPersonalidad = TipoPersonalidad::all();


       
        return view('livewire.proveedores', compact("users","paises","categorias","retenciones","tipoPersonalidad"));
    }

    public function proveerdoresModelInsert(Request $request){
        /* dd($request->telefono_prov_2); */
            $validator = Validator::make($request->all(), [
                'codigo_prov' => 'required',
                'nombre_prov' => 'required',
                'direccion_prov' => 'required',
                'contacto_prov' => 'required',
                'telefono_prov' => 'required',               
                'rtn_prov' => 'required',   
                'municipio_prov' => 'required',
                'giro_neg_prov' => 'required',
                'categoria_prov' => 'required',
                'retencion_prov' => 'required',
            ], [
                'codigo_prov' => 'Codigo es requerido',
                'nombre_prov' => 'nombre es requerido',
                'direccion_prov' => 'Direccion es requerido',
                'contacto_prov' => 'Contacto es requerido',
                'telefono_prov' => 'Telefono es requerido',
                'telefono_prov_2' => 'Telefono 2 es requerido',
                'rtn_prov' => 'RTN es requerido',     
                'municipio_prov' => 'Municipio es requerido',
                'giro_neg_prov' => 'Giro es requerido',
                'categoria_prov' => 'Categoria es requerido',
                'retencion_prov' => 'Retencion es requerido',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'mensaje' => 'Ha ocurrido un error.',
                    'errors' => $validator->errors()
                ], 422);
            }
         

            try {

                DB::beginTransaction();

                $crearProveedores = new Modelproveedores;
                $crearProveedores->codigo=trim($request->codigo_prov);
                $crearProveedores->nombre=trim($request->nombre_prov);
                $crearProveedores->direccion=trim($request->direccion_prov);
                $crearProveedores->contacto=trim($request->contacto_prov);
                $crearProveedores->telefono_1=trim($request->telefono_prov);
                $crearProveedores->telefono_2=trim($request->telefono_prov_2);
                $crearProveedores->correo_1=trim($request->correo_prov);
                $crearProveedores->correo_2=trim($request->correo_prov_2);
                $crearProveedores->rtn=trim($request->rtn_prov);
                $crearProveedores->registrado_por=Auth::user()->id;              
                $crearProveedores->municipio_id=$request->municipio_prov;
                $crearProveedores->tipo_personalidad_id=$request->giro_neg_prov;
                $crearProveedores->categoria_id=$request->categoria_prov;  
                $crearProveedores->estado_id=1;
                $crearProveedores->save();

                if($request->retencion_prov != 1){
                    $retencionesProveedor = new RetencionesProveedores;
                    $retencionesProveedor->retenciones_id = $request->retencion_prov;
                    $retencionesProveedor->proveedores_id = $crearProveedores->id;
                    $retencionesProveedor->save();
                }


                



                //$crearProveedores->retencion=$request->retencion_prov;

                DB::commit();

                return response()->json([
                    'message' => 'Creado con exito.',                   
                ], 200); 
               
            } catch (QueryException $e) {

                DB::rollback(); 
         
                return response()->json([
                    'message' => 'Ha ocurrido un error.',
                    'errorTh' => $e,
                 
                  
                ], 402);  
                
            }


    }

    public function obtenerDepartamentos(Request $request){
      
        try {
           
            // $departamentos = Departamento::where('pais_id','=',$request['id'])
            //                 ->SELECT("id","UPPER(nombre)")
            //                 ->get();
            $departamentos = DB::SELECT("
            select id, CONCAT(UPPER(SUBSTRING(nombre,1,1)),LOWER(SUBSTRING(nombre,2))) as 'nombre' from departamento where pais_id=".$request['id']."
            ");


            return response()->json([
                "departamentos" => $departamentos,
            ],200);

        } catch (QueryException $e) {
            return response()->json([
                "message" => "Ha ocurrido un error al obtener los departamentos",
                "error" => $e
            ],402);
          
        }
    }

    public function obtenerMunicipios(Request $request){
      
        try {
           
            // $departamentos = Departamento::where('pais_id','=',$request['id'])
            //                 ->SELECT("id","UPPER(nombre)")
            //                 ->get();
            $municipios = DB::SELECT("
            select id, CONCAT(UPPER(SUBSTRING(nombre,1,1)),LOWER(SUBSTRING(nombre,2))) as 'nombre' from municipio where departamento_id=".$request['id']."
            ");


            return response()->json([
                "departamentos" => $municipios,
            ],200);

        } catch (QueryException $e) {
            return response()->json([
                "message" => "Ha ocurrido un error al obtener los municipios",
                "error" => $e
            ],402);
          
        }
    }

    public function listarProveedores(){
        try {
         $listaProveedores = DB::SELECT("
         select
         id,
         codigo,
         nombre,
         direccion,
         contacto,
         correo_1,
         rtn,
         estado_id,
         (select retenciones_id from retenciones_has_proveedores where (retenciones_has_proveedores.proveedores_id = proveedores.id and retenciones_id = 2)  ) as 'uno_porciento'    
     from proveedores 

            ");

            return Datatables::of($listaProveedores)
            ->addColumn('opciones', function ($listaProveedores) {
                   
                    
                    if($listaProveedores->estado_id === 1){

                        return

                        '
                        <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start"
                            style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                            <li><a class="dropdown-item" href="#" onclick="mostrarModalEditar('.$listaProveedores->id.')"> <i class="fa fa-pencil m-r-5 text-warning"></i>
                                    Editar</a></li>
                            <li><a class="dropdown-item" href="#" onclick="desactivarProveedor('.$listaProveedores->id.')"> <i class="fa fa-times text-danger" aria-hidden="true"></i>
                                    Desactivar </a></li>
        
                        </ul>
                    </div>
                        ';


                    }else{

                        return

                        
                        '
                        <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start"
                            style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                            <li><a class="dropdown-item" href="#" onclick="mostrarModalEditar('.$listaProveedores->id.')"> <i class="fa fa-pencil m-r-5 text-warning"></i>
                                    Editar</a></li>
                            <li><a class="dropdown-item" href="#" onclick="activarProveedor('.$listaProveedores->id.')"> <i class="fa fa-check-circle text-success" aria-hidden="true"></i>
                                    Activar </a></li>
        
                        </ul>
                    </div>
                        ';

                    }
            
                    
                    

            })
            ->addColumn('retencion', function ($listaProveedores) {
                if ($listaProveedores->uno_porciento) {
                    return '<td><span class="badge bg-primary">Retencion</span></td>';
                } else {
    
                    return '<td><span class="badge bg-danger">Sin Retencion</span></td>';
                }
    
                    })
            ->addColumn('estado', function ($listaProveedores) {
                if ($listaProveedores->estado_id === 1) {
                    return '<td><span class="badge bg-primary">ACTIVO</span></td>';
                } else {
    
                    return '<td><span class="badge bg-danger">INACTIVO</span></td>';
                }
    
                    })    
           
            ->rawColumns(['opciones','retencion','estado'])         
            ->make(true);


        } catch (QueryException $e) {
        
            return response()->json([
                'message' => 'Ha ocurrido un error, por favor intente de nuevo.',               
                'exception' => $e,
            ],402);
            
        }
    }

    public function desactivarProveedor(Request $request){

        try {

            $proveedorEstado = Modelproveedores::find($request['id']);

            if($proveedorEstado->estado_id === 1){
                $proveedor = Modelproveedores::find($request['id']); 
                $proveedor->estado_id = 2;            
                $proveedor->save();

                return response()->json([
                    "message" => "Desactivado con exito"
                ],200);

            }else{

                $proveedor = Modelproveedores::find($request['id']); 
                $proveedor->estado_id = 1;            
                $proveedor->save();

                return response()->json([
                    "message" => "Activado con exito"
                ],200);

            }







          
        } catch (QueryException $e) {
            return response()->json([
                "message" => "Error al desactivar",
                "error" => $e
            ],402);
            
        }




    }

    public function obtenerProveedor(Request $request){
        try {

            $proveedor = Modelproveedores::find($request['id']);
         

            $municipioProveedor =  Municipio::find( $proveedor->municipio_id);
            $departamentoProveedor = Departamento::find($municipioProveedor->departamento_id);
            

            $municipioProveedorId = $proveedor->municipio_id;
            $departamentoProveedorId = $municipioProveedor->departamento_id;
            $paisProveedor = $departamentoProveedor->pais_id;

            $paises = Pais::all();

            $departamentos = DB::SELECT("
            select id, CONCAT(UPPER(SUBSTRING(nombre,1,1)),LOWER(SUBSTRING(nombre,2))) as 'nombre' from departamento ");


            $municipios = DB::SELECT("select id, CONCAT(UPPER(SUBSTRING(nombre,1,1)),LOWER(SUBSTRING(nombre,2))) as 'nombre' from municipio ");
            $tipoPersonalidad = TipoPersonalidad::all();
            $categoria = Categoria::all();

            return response()->json([
                "paises" => $paises,
                "departamentos" => $departamentos,
                "municipios" => $municipios,
                "municipioProveedorId" => $municipioProveedorId,
                "departamentoProveedorId" => $departamentoProveedorId,
                "paisProveedor" => $paisProveedor,
                "proveedor" => $proveedor,
                "tipoPersonalidad"=>$tipoPersonalidad,
                "categoria"=>$categoria

            ],200);

            
        } catch (QueryException $e) {
            return response()->json([
                "message" => "Error al obtener datos del proveedor",
                "error" => $e
            ],402);
          
        }

    }

    public function editarProveedor(Request $request){

        $validator = Validator::make($request->all(), [
            'idProveedor' => 'required',
            'editar_codigo_prov' => 'required',
            'editar_nombre_prov' => 'required',
            'editar_direccion_prov' => 'required',
            'editar_contacto_prov' => 'required',
            'editar_telefono_prov' => 'required',               
            'editar_rtn_prov' => 'required',   
            'editar_municipio_prov' => 'required',
            'editar_giro_neg_prov' => 'required',
            'editar_categoria_prov' => 'required',
            
        ], [
            'editar_codigo_prov' => 'Codigo es requerido',
            'editar_nombre_prov' => 'nombre es requerido',
            'editar_ireccion_prov' => 'Direccion es requerido',
            'editar_contacto_prov' => 'Contacto es requerido',
            'editar_telefono_prov' => 'Telefono es requerido',
            'editar_telefono_prov_2' => 'Telefono 2 es requerido',
            'editar_rtn_prov' => 'RTN es requerido',     
            'editar_municipio_prov' => 'Municipio es requerido',
            'editar_giro_neg_prov' => 'Giro es requerido',
            'editar_categoria_prov' => 'Categoria es requerido',
           
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error.',
                'errors' => $validator->errors()
            ], 422);
        }
        try {

                $crearProveedores = Modelproveedores::find($request['idProveedor']);
                $crearProveedores->codigo=trim($request->editar_codigo_prov);
                $crearProveedores->nombre=trim($request->editar_nombre_prov);
                $crearProveedores->direccion=trim($request->editar_direccion_prov);
                $crearProveedores->contacto=trim($request->editar_contacto_prov);
                $crearProveedores->telefono_1=trim($request->editar_telefono_prov);
                $crearProveedores->telefono_2=trim($request->editar_telefono_prov_2);
                $crearProveedores->correo_1=trim($request->editar_correo_prov);
                $crearProveedores->correo_2=trim($request->editar_correo_prov_2);
                $crearProveedores->rtn=trim($request->editar_rtn_prov);
                $crearProveedores->registrado_por=Auth::user()->id;              
                $crearProveedores->municipio_id=$request->editar_municipio_prov;
                $crearProveedores->tipo_personalidad_id=$request->editar_giro_neg_prov;
                $crearProveedores->categoria_id=$request->editar_categoria_prov;                 
                $crearProveedores->save();



            return response()->json([
                'message' => 'Editado con exito.',                   
            ], 200); 
           
        } catch (QueryException $e) {
          
         
            return response()->json([
                'message' => 'Ha ocurrido un error al editar el proveedor.',
                'error' => $e,
             
              
            ], 402);  
           
        }
    }
}
