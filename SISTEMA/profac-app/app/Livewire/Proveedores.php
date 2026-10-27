<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        $this->normalizarRegistroProveedor($request);
            $validator = Validator::make($request->all(), [
                'nombre_prov' => 'required|string|max:100',
                'direccion_prov' => 'required|string',
                'contacto_prov' => 'nullable|string|max:45',
                'telefono_prov' => ['required_unless:telefono_1_sin_registro,1', 'nullable', 'string', 'regex:/^[0-9]{4}-[0-9]{4}$/D'],
                'telefono_prov_2' => ['nullable', 'string', 'regex:/^[0-9]{4}-[0-9]{4}$/D'],
                'correo_prov' => 'nullable|email|max:45',
                'correo_prov_2' => 'nullable|email|max:45',
                'rtn_prov' => ['required', 'string', 'max:45',
                    Rule::when(!$request->boolean('rtn_sin_registro'), ['regex:/^[A-Za-z0-9]+$/D', Rule::unique('proveedores', 'rtn')])],
                'rtn_sin_registro' => 'sometimes|boolean',
                'telefono_1_sin_registro' => 'sometimes|boolean',
                'telefono_2_sin_registro' => 'sometimes|boolean',
                'pais_prov' => 'sometimes|required|integer|exists:pais,id',
                'depto_prov' => ['required_with:pais_prov', 'integer', Rule::exists('departamento', 'id')->where('pais_id', $request->pais_prov)],
                'municipio_prov' => ['required', 'integer', $request->filled('depto_prov')
                    ? Rule::exists('municipio', 'id')->where('departamento_id', $request->depto_prov)
                    : Rule::exists('municipio', 'id')],
                'giro_neg_prov' => 'required|integer|exists:tipo_personalidad,id',
                'categoria_prov' => 'required|integer|exists:categoria,id',
                'retencion_prov' => 'nullable|integer|exists:retenciones,id',
            ], [
                'nombre_prov' => 'nombre es requerido',
                'direccion_prov' => 'Direccion es requerido',
                'contacto_prov' => 'Contacto es requerido',
                'telefono_prov.required_unless' => 'Ingrese el teléfono 1 o marque Sin registro.',
                'telefono_prov.regex' => 'El teléfono 1 debe tener el formato ####-####.',
                'telefono_prov_2.regex' => 'El teléfono 2 debe tener el formato ####-####.',
                'rtn_prov.required' => 'El RTN es obligatorio.',
                'rtn_prov.unique' => 'Ya existe un proveedor con este RTN.',
                'rtn_prov.regex' => 'El RTN solo puede contener letras y números; si no tiene registro, marque Sin registro.',
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

                $crearProveedores = DB::transaction(function () use ($request) {
                $this->validarRtnDisponible($request->rtn_prov, 'rtn_prov');

                $crearProveedores = new Modelproveedores;
                $crearProveedores->nombre=trim($request->nombre_prov);
                $crearProveedores->direccion=trim($request->direccion_prov);
                $crearProveedores->contacto=trim((string) $request->contacto_prov);
                $crearProveedores->telefono_1=trim((string) $request->telefono_prov);
                $crearProveedores->telefono_2=trim((string) $request->telefono_prov_2);
                $crearProveedores->correo_1=trim((string) $request->correo_prov);
                $crearProveedores->correo_2=trim((string) $request->correo_prov_2);
                $crearProveedores->rtn=trim($request->rtn_prov);
                $crearProveedores->registrado_por=Auth::user()->id;              
                $crearProveedores->municipio_id=$request->municipio_prov;
                $crearProveedores->tipo_personalidad_id=$request->giro_neg_prov;
                $crearProveedores->categoria_id=$request->categoria_prov;  
                $crearProveedores->estado_id=1;
                $crearProveedores->save();
                $crearProveedores->codigo = (string) $crearProveedores->id;
                $crearProveedores->save();

                if($request->filled('retencion_prov') && $request->retencion_prov != 1){
                    $retencionesProveedor = new RetencionesProveedores;
                    $retencionesProveedor->retenciones_id = $request->retencion_prov;
                    $retencionesProveedor->proveedores_id = $crearProveedores->id;
                    $retencionesProveedor->save();
                }


                



                //$crearProveedores->retencion=$request->retencion_prov;

                return $crearProveedores;
                }, 3);

                return response()->json([
                    'message' => 'Creado con exito.',
                    'proveedor' => [
                        'id' => $crearProveedores->id,
                        'text' => $crearProveedores->id . ' - ' . $crearProveedores->nombre,
                        'rtn' => $crearProveedores->rtn,
                    ],
                ], 200); 
               
            } catch (ValidationException $e) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            } catch (QueryException $e) {

                report($e);
         
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
         telefono_1,
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
                        <button type="button" data-toggle="dropdown" data-boundary="viewport" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu dropdown-menu-right">
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
                        <button type="button" data-toggle="dropdown" data-boundary="viewport" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu dropdown-menu-right">
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
            select id, pais_id, CONCAT(UPPER(SUBSTRING(nombre,1,1)),LOWER(SUBSTRING(nombre,2))) as 'nombre' from departamento ");


            $municipios = DB::SELECT("select id, departamento_id, CONCAT(UPPER(SUBSTRING(nombre,1,1)),LOWER(SUBSTRING(nombre,2))) as 'nombre' from municipio ");
            $tipoPersonalidad = TipoPersonalidad::all();
            $categoria = Categoria::all();
            $retencionProveedor = RetencionesProveedores::where('proveedores_id', $proveedor->id)
                ->where('retenciones_id', '<>', 1)->value('retenciones_id') ?? 1;

            return response()->json([
                "paises" => $paises,
                "departamentos" => $departamentos,
                "municipios" => $municipios,
                "municipioProveedorId" => $municipioProveedorId,
                "departamentoProveedorId" => $departamentoProveedorId,
                "paisProveedor" => $paisProveedor,
                "proveedor" => $proveedor,
                "tipoPersonalidad"=>$tipoPersonalidad,
                "categoria"=>$categoria,
                "retencionProveedor" => $retencionProveedor,

            ],200);

            
        } catch (QueryException $e) {
            return response()->json([
                "message" => "Error al obtener datos del proveedor",
                "error" => $e
            ],402);
          
        }

    }

    public function editarProveedor(Request $request){
        $this->normalizarRegistroProveedor($request, 'editar_');
        $rtnSinCambios = is_string($request->editar_rtn_prov) && Modelproveedores::whereKey((int) $request->idProveedor)
            ->where('rtn', $request->editar_rtn_prov)->exists();

        $validator = Validator::make($request->all(), [
            'idProveedor' => 'required|integer|exists:proveedores,id',
            'editar_nombre_prov' => 'required|string|max:100',
            'editar_direccion_prov' => 'required|string',
            'editar_contacto_prov' => 'nullable|string|max:45',
            'editar_telefono_prov' => ['required_unless:editar_telefono_1_sin_registro,1', 'nullable', 'string', 'regex:/^[0-9]{4}-[0-9]{4}$/D'],
            'editar_telefono_prov_2' => ['nullable', 'string', 'regex:/^[0-9]{4}-[0-9]{4}$/D'],
            'editar_correo_prov' => 'nullable|email|max:45',
            'editar_correo_prov_2' => 'nullable|email|max:45',
            'editar_rtn_prov' => ['required', 'string', 'max:45',
                Rule::when(!$request->boolean('editar_rtn_sin_registro'), ['regex:/^[A-Za-z0-9]+$/D']),
                Rule::when(!$request->boolean('editar_rtn_sin_registro') && !$rtnSinCambios,
                    [Rule::unique('proveedores', 'rtn')->ignore((int) $request->idProveedor)])],
            'editar_rtn_sin_registro' => 'sometimes|boolean',
            'editar_telefono_1_sin_registro' => 'sometimes|boolean',
            'editar_telefono_2_sin_registro' => 'sometimes|boolean',
            'editar_retencion_prov' => 'sometimes|required|integer|exists:retenciones,id',
            'editar_pais_prov' => 'sometimes|required|integer|exists:pais,id',
            'editar_depto_prov' => ['required_with:editar_pais_prov', 'integer', Rule::exists('departamento', 'id')->where('pais_id', $request->editar_pais_prov)],
            'editar_municipio_prov' => ['required', 'integer', $request->filled('editar_depto_prov')
                ? Rule::exists('municipio', 'id')->where('departamento_id', $request->editar_depto_prov)
                : Rule::exists('municipio', 'id')],
            'editar_giro_neg_prov' => 'required|integer|exists:tipo_personalidad,id',
            'editar_categoria_prov' => 'required|integer|exists:categoria,id',
            
        ], [
            'editar_nombre_prov' => 'nombre es requerido',
            'editar_direccion_prov' => 'Direccion es requerido',
            'editar_contacto_prov' => 'Contacto es requerido',
            'editar_telefono_prov.required_unless' => 'Ingrese el teléfono 1 o marque Sin registro.',
            'editar_telefono_prov.regex' => 'El teléfono 1 debe tener el formato ####-####.',
            'editar_telefono_prov_2.regex' => 'El teléfono 2 debe tener el formato ####-####.',
            'editar_rtn_prov.required' => 'El RTN es obligatorio.',
            'editar_rtn_prov.unique' => 'Ya existe un proveedor con este RTN.',
            'editar_rtn_prov.regex' => 'El RTN solo puede contener letras y números; si no tiene registro, marque Sin registro.',
            'editar_retencion_prov.exists' => 'Seleccione una retención válida.',
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
                DB::transaction(function () use ($request) {
                $this->validarRtnDisponible($request->editar_rtn_prov, 'editar_rtn_prov', (int) $request->idProveedor);

                $crearProveedores = Modelproveedores::find($request['idProveedor']);
                $crearProveedores->nombre=trim($request->editar_nombre_prov);
                $crearProveedores->direccion=trim($request->editar_direccion_prov);
                if ($request->has('editar_contacto_prov')) {
                    $crearProveedores->contacto=trim((string) $request->editar_contacto_prov);
                }
                $crearProveedores->telefono_1=trim((string) $request->editar_telefono_prov);
                $crearProveedores->telefono_2=trim((string) $request->editar_telefono_prov_2);
                $crearProveedores->correo_1=trim((string) $request->editar_correo_prov);
                if ($request->has('editar_correo_prov_2')) {
                    $crearProveedores->correo_2=trim((string) $request->editar_correo_prov_2);
                }
                $crearProveedores->rtn=trim($request->editar_rtn_prov);
                $crearProveedores->registrado_por=Auth::user()->id;              
                $crearProveedores->municipio_id=$request->editar_municipio_prov;
                $crearProveedores->tipo_personalidad_id=$request->editar_giro_neg_prov;
                $crearProveedores->categoria_id=$request->editar_categoria_prov;                 
                $crearProveedores->save();
                if ($request->has('editar_retencion_prov')) {
                    $retenciones = RetencionesProveedores::where('proveedores_id', $crearProveedores->id);
                    $ids = (clone $retenciones)->pluck('retenciones_id');
                    $seleccion = (int) $request->editar_retencion_prov;
                    if (($seleccion === 1 && $ids->isNotEmpty())
                        || ($seleccion !== 1 && ($ids->count() !== 1 || (int) $ids->first() !== $seleccion))) {
                        $retenciones->delete();
                        if ($seleccion !== 1) {
                            $retencion = new RetencionesProveedores;
                            $retencion->proveedores_id = $crearProveedores->id;
                            $retencion->retenciones_id = $seleccion;
                            $retencion->save();
                        }
                    }
                }
                }, 3);


            return response()->json([
                'message' => 'Editado con exito.',                   
            ], 200); 
           
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            report($e);
          
         
            return response()->json([
                'message' => 'Ha ocurrido un error al editar el proveedor.',
                'error' => $e,
             
              
            ], 402);  
           
        }
    }

    private function validarRtnDisponible(string $rtn, string $campo, ?int $excepto = null): void
    {
        if ($rtn === 'N/A') {
            return;
        }
        // Serialize provider writes until legacy duplicates can be replaced by a unique RTN index.
        Modelproveedores::orderBy('id')->lockForUpdate()->first(['id']);
        if ($excepto !== null && Modelproveedores::whereKey($excepto)->where('rtn', $rtn)->exists()) {
            return;
        }
        $proveedor = Modelproveedores::where('rtn', $rtn)
            ->when($excepto !== null, fn ($query) => $query->where('id', '<>', $excepto))
            ->lockForUpdate()
            ->first(['id']);

        if ($proveedor !== null) {
            throw ValidationException::withMessages([$campo => 'Ya existe un proveedor con este RTN.']);
        }
    }

    private function normalizarRegistroProveedor(Request $request, string $prefijo = ''): void
    {
        $rtn = $request->input($prefijo . 'rtn_prov');
        $request->merge([$prefijo . 'rtn_prov' => $request->boolean($prefijo . 'rtn_sin_registro')
            ? 'N/A' : (is_string($rtn) ? trim($rtn) : $rtn)]);

        foreach (['telefono_prov' => 'telefono_1_sin_registro', 'telefono_prov_2' => 'telefono_2_sin_registro'] as $campo => $check) {
            $telefono = $request->input($prefijo . $campo);
            if ($request->boolean($prefijo . $check)) {
                $telefono = '';
            } elseif (is_string($telefono)) {
                $telefono = trim($telefono);
                if (preg_match('/^[0-9]{8}$/D', $telefono)) {
                    $telefono = substr($telefono, 0, 4) . '-' . substr($telefono, 4);
                }
            }
            $request->merge([$prefijo . $campo => $telefono]);
        }
    }
}
