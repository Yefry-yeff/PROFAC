<?php

namespace App\Http\Livewire\Clientes;

use Livewire\Component;
use App\Models\User;


use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use App\Models\Escalas\clienteCategoriaEscalaLog;

use App\Models\ModelCliente;
use App\Models\ModelContacto;
use App\Models\logCredito;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientesExport;

use App\Exports\Escalas\ClientesCategoriaPlantillaExport;
use App\Imports\Escalas\ClientesCategoriaMasivaImport;
use Illuminate\Support\Facades\Validator;
use ZipArchive;
use Illuminate\Support\Facades\Log;


class Cliente extends Component
{
    public function render()
    {
        $clientes = DB::SELECT("
        select
        id, name
        from users
        where rol_id=2
        order by name ASC
        ");

        return view('livewire.clientes.cliente',compact('clientes'));
    }

    public function opbtenerPais(){

        $listaPais = DB::SELECT("select id, nombre from pais");

        return response()->json([
            'listaPais' => $listaPais
        ],200);

    }

    public function obtenerDepartamentos(Request $request){

        $listaDeptos = DB::SELECT("
        select id , nombre from departamento where pais_id =". $request['id']." order by nombre asc "
        );

        return response()->json([
            'listaDeptos' => $listaDeptos
        ],200);

    }

    public function obtenerMunicipio(Request $request){
        $listaMunicipios = DB::SELECT("
        select id, nombre from municipio where departamento_id = ". $request['id']." order by nombre asc "
        );

        return response()->json([
            'listaMunicipios' => $listaMunicipios
        ],200);
    }

    public function tipoPersonalidad(){
        $tipoPersonalidad = DB::SELECT("
        select id,nombre from tipo_personalidad
        ");

        return response()->json([
            'tipoPersonalidad' => $tipoPersonalidad
        ],200);
    }

    public function tipoCliente(){
        $tipoCliente = DB::SELECT("
        select id, descripcion from tipo_cliente
        ");

        return response()->json([
            'tipoCliente' => $tipoCliente
        ],200);
    }

    public function listaVendedores(){
        $vendedor = DB::SELECT("
        select id, name from users where rol_id = 2
        ");

        return response()->json([
            'vendedor' => $vendedor
        ],200);
    }

    public function guardarCliente(Request $request){
       try {

       DB::beginTransaction();

        //dd($request->all());
        //dd(str_replace(",","",$request->credito));

        if ($request->file('foto_cliente') <> null) {
            $estado_img =1;

            $archivo = $request->file('foto_cliente');
            $name = 'IMG_'. time().".". $archivo->getClientOriginalExtension();
            $path = public_path() . '/img_cliente';
            $archivo->move($path, $name);

            $nombreCliente = str_replace("'"," ",$request->nombre_cliente);
            $nombreCliente = str_replace('"'," ",$nombreCliente);
            $nombreCliente = str_replace('´'," ",$nombreCliente);

            $cliente = new ModelCliente;
            $cliente->nombre = TRIM($nombreCliente) ;
            $cliente->direccion = TRIM($request->direccion_cliente) ;
            $cliente->telefono_empresa = trim($request->telefono_cliente) ;
            $cliente->rtn = TRIM($request->rtn_cliente);
            $cliente->correo = TRIM($request->correo_cliente) ;
            $cliente->url_imagen = $name;
            $cliente->credito_inicial = str_replace(",","",$request->credito);
            $cliente->credito = str_replace(",","",$request->credito);
            $cliente->dias_credito=$request->dias_credito;
            $cliente->latitud =TRIM($request->latitud_cliente);
            $cliente->longitud =TRIM($request->longitud_cliente);
            $cliente->tipo_cliente_id = $request->categoria_cliente;
            $cliente->tipo_personalidad_id = $request->tipo_personalidad ;
            $cliente->categoria_id = $request->categoria_cliente ;
            $cliente->vendedor = $request->vendedor_cliente ;
            $cliente->users_id = Auth::user()->id;
            $cliente->estado_cliente_id = 1;
            $cliente->municipio_id = $request->municipio_cliente;
            $cliente->cliente_categoria_escala_id = $request->cliente_categoria_escala_id_crear;
            $cliente->save();


            $contactos = $request->contacto;
            $telefonos = $request->telefono;


            for ($i=0; $i < count($contactos) ; $i++) {
                if( is_null($contactos[$i]) || is_null($telefonos[$i]) ){
                    continue;
                }
                $contaco = new ModelContacto;
                $contaco->nombre = $contactos[$i];
                $contaco->telefono = $telefonos[$i];
                $contaco->cliente_id = $cliente->id;
                $contaco->estado_id = 1;
                $contaco->save();

            }

        }else{
            $estado_img =2;

                $nombreCliente = str_replace("'"," ",$request->nombre_cliente);
                $nombreCliente = str_replace('"'," ", $nombreCliente);
                $nombreCliente = str_replace('´'," ",$nombreCliente);

                $cliente = new ModelCliente;
                $cliente->nombre = TRIM($nombreCliente);
                $cliente->direccion = TRIM($request->direccion_cliente) ;
                $cliente->telefono_empresa = TRIM($request->telefono_cliente) ;
                $cliente->rtn = TRIM($request->rtn_cliente);
                $cliente->correo = TRIM($request->correo_cliente) ;
                $cliente->credito_inicial = str_replace(",","",$request->credito);
                $cliente->credito = str_replace(",","",$request->credito);
                $cliente->dias_credito=TRIM($request->dias_credito);
                $cliente->latitud =TRIM($request->latitud_cliente);
                $cliente->longitud =TRIM($request->longitud_cliente);
                $cliente->tipo_cliente_id = $request->categoria_cliente;
                $cliente->tipo_personalidad_id = $request->tipo_personalidad ;
                $cliente->categoria_id = $request->categoria_cliente ;
                $cliente->vendedor = $request->vendedor_cliente ;
                $cliente->users_id = Auth::user()->id;
                $cliente->estado_cliente_id = 1;
                $cliente->municipio_id = $request->municipio_cliente;

            $cliente->cliente_categoria_escala_id = $request->cliente_categoria_escala_id_crear;
                $cliente->save();


                $contactos = $request->contacto;
                $telefonos = $request->telefono;


                for ($i=0; $i < count($contactos) ; $i++) {

                 if( is_null($contactos[$i]) || is_null($telefonos[$i]) ){
                    continue;
                }
                $contaco = new ModelContacto;
                $contaco->nombre = $contactos[$i];
                $contaco->telefono = $telefonos[$i];
                $contaco->cliente_id = $cliente->id;
                $contaco->estado_id = 1;
                $contaco->save();



            }

        }

        DB::commit();
        return response()->json([
            "icon" => "success",
            "text" => "Registro realizado con exito!",
            "title"=>"Exito!"
        ],200);

       } catch (QueryException $e) {
        DB::rollback();

        if($estado_img == 1){
            $carpetaPublic = public_path();
            $path = $carpetaPublic.'/img_cliente/'.$name;
            File::delete($path);
        }


        return response()->json([
            "icon" => "error",
            "text" => "Ha ocurrido un error al registrar el cliente",
            "title"=>"Error!",
            "error" => $e
        ],402);
       }
    }

    public function listarClientes(){
       try {

            $clientes = DB::SELECT("
            select
                cliente.id as idCliente,
                (select nombre_categoria from cliente_categoria_escala where id = cliente_categoria_escala_id ) as categoria_escala_cliente,
                nombre,
                direccion,
                telefono_empresa,
                correo,
                rtn,
                estado_cliente.descripcion,
                name,
                cliente.estado_cliente_id,
                cliente.created_at
            from cliente
            inner join estado_cliente on estado_cliente.id = cliente.estado_cliente_id
            inner join users on users.id = cliente.users_id
            ");


            return Datatables::of($clientes)
            ->addColumn('opciones', function ($cliente) {

                if($cliente->estado_cliente_id == 1){
                    return
                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" onclick="modalEditarCliente('.$cliente->idCliente.')" > <i class="fa fa-pencil m-r-5 text-warning"></i> Editar Cliente </a>
                                <a class="dropdown-item" onclick="modalEditarFotografia('.$cliente->idCliente.')" > <i class="fa-solid fa-camera  m-r-5 text-success"></i> Cambiar Fotografia del cliente </a>
                                <a class="dropdown-item" onclick="desactivarClienteModal('.$cliente->idCliente.')" > <i class="fa fa-times text-danger" aria-hidden="true"></i> Desactivar Cliente </a>

                            </li>



                        </ul>
                    </div>';
                }else{
                    return
                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" onclick="modalEditarCliente('.$cliente->idCliente.')" > <i class="fa fa-pencil m-r-5 text-warning"></i> Editar Cliente </a>
                                <a class="dropdown-item" onclick="modalEditarFotografia('.$cliente->idCliente.')" > <i class="fa-solid fa-camera  m-r-5 text-success"></i> Cambiar Fotografia del cliente </a>
                                <a class="dropdown-item" onclick="activarCliente('.$cliente->idCliente.')" > <i class="fa fa-check-circle text-info" aria-hidden="true"></i> Activar Cliente </a>

                            </li>



                        </ul>
                    </div>';

                }


            })
            ->addColumn('estado', function ($cliente) {
                if ($cliente->estado_cliente_id === 1) {
                    return '<td><span class="badge bg-primary">ACTIVO</span></td>';
                } else {

                    return '<td><span class="badge bg-danger">INACTIVO</span></td>';
                }

            })
            ->rawColumns(['opciones','estado'])
            ->make(true);


       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }

    public function datosCliente(Request $request){
       try {

        $datosCliente = DB::SELECTONE("
        select
            id,
            nombre,
            direccion,
            telefono_empresa,
            rtn,
            correo,
            latitud,
            longitud,
            url_imagen,
            credito_inicial,
            credito,
            dias_credito,
            tipo_cliente_id,
            tipo_personalidad_id,
            categoria_id,
            vendedor,
            users_id,
            estado_cliente_id,
            municipio_id,
            cliente_categoria_escala_id,
            (select nombre_categoria from cliente_categoria_escala where id = cliente_categoria_escala_id) as nombre_cat_escala,
            created_at,
            updated_at
        from cliente
        where id =".$request['id']);

        $datosContacto = DB::SELECT("
        select
            @i := @i + 1 as contador,
            id,
            nombre,
            telefono
        from contacto
        CROSS JOIN (select @i := 0) r
            where estado_id =1 and cliente_id = ".$request['id']
        );

        $datosUbicacion = DB::SELECTONE("
        select
            C.id as 'idPais',
            A.id as 'idDepto',
            B.id as 'idMunicipio'
        from departamento A
            inner join municipio B
            on A.id = B.departamento_id
            inner join pais C
            on C.id = A.pais_id
        where B.id =".$datosCliente->municipio_id
        );

        $paises = DB::SELECT("select id,nombre from pais ");
        $deptos = DB::SELECT("select id,nombre from departamento where pais_id = ".$datosUbicacion->idPais);
        $municipios = DB::SELECT("select id, nombre from municipio where departamento_id = ".$datosUbicacion->idDepto);

        $tipoPersonalidad = DB::SELECT("select id, nombre from tipo_personalidad");
        $tipoCliente = DB::SELECT("select id, descripcion from tipo_cliente");
        $vendedores = DB::SELECT("select id, name from users where rol_id = 2");

       return response()->json([
           'datosCliente' => $datosCliente,
           'datosContacto' => $datosContacto,
           'datosUbicacion' => $datosUbicacion,
           'paises' =>$paises,
           'deptos' => $deptos,
           'municipios'=>$municipios,
           'tipoPersonalidad' => $tipoPersonalidad,
           'tipoCliente' => $tipoCliente,
           'vendedores'=>$vendedores
       ],200);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }

    }

    public function editarCliente(Request $request){
       try {
           //dd($request->all());
           $nombreCliente = str_replace("'"," ",$request->nombre_cliente_editar);
           $nombreCliente = str_replace('"'," ",$nombreCliente);
           $nombreCliente = str_replace('´'," ",$nombreCliente);

        DB::beginTransaction();
        $cliente =  ModelCliente::find($request->idCliente);
        $cliente->nombre = trim($nombreCliente) ;
        $cliente->direccion = trim($request->direccion_cliente_editar) ;
        $cliente->telefono_empresa = trim($request->telefono_cliente_editar);
        $cliente->rtn = trim($request->rtn_cliente_editar);
        $cliente->correo = trim($request->correo_cliente_editar);
        $cliente->credito_inicial = trim($request->credito_inicial_editar);
        $cliente->credito = trim($request->credito_editar);
        $cliente->dias_credito = trim($request->dias_credito_editar);
        $cliente->latitud = trim($request->latitud_cliente_editar);
        $cliente->longitud = trim($request->longitud_cliente_editar);
        $cliente->tipo_cliente_id = $request->categoria_cliente_editar;
        $cliente->tipo_personalidad_id = $request->tipo_personalidad_editar;
        $cliente->categoria_id = $request->categoria_cliente_editar;
        $cliente->vendedor = $request->vendedor_cliente_editar;
        $cliente->users_id = Auth::user()->id;
        $cliente->estado_cliente_id = 1;
        $cliente->municipio_id = $request->municipio_cliente_editar;
        $cliente->cliente_categoria_escala_id = $request->categoria_cliente_escala_editar;
        $cliente->save();

        ModelContacto::where('cliente_id','=', $request->idCliente)
        ->update(['estado_id' => 2]);

        $contaco = new ModelContacto;
        $contaco->nombre = trim($request->contacto_1_editar);
        $contaco->telefono = trim($request->telefono_1_editar);;
        $contaco->cliente_id = $request->idCliente;
        $contaco->estado_id = 1;
        $contaco->save();

        $contaco2 = new ModelContacto;
        $contaco2->nombre = trim($request->contacto_2_editar);
        $contaco2->telefono = trim($request->telefono_2_editar);;
        $contaco2->cliente_id = $request->idCliente;
        $contaco2->estado_id = 1;
        $contaco2->save();


        //-------------------------comprobar cambios de credito-----------------------------//



        $creditoInicial = new logCredito();
        $creditoInicial->descripcion = "Credito inicial editado.";
        $creditoInicial->monto = trim($request->credito_inicial_editar);
        $creditoInicial->users_id = Auth::user()->id;
        $creditoInicial->cliente_id = $request->idCliente;
        $creditoInicial->save();

        $credito = new logCredito();
        $credito->descripcion = "Credito disponible editado.";
        $credito->monto = trim($request->credito_editar);
        $credito->users_id = Auth::user()->id;
        $credito->cliente_id = $request->idCliente;
        $credito->save();





        DB::commit();
        return response()->json([
            "text" => "Cliente editado con éxito.",
            "icon" => "success",
            "title"=>"Exito!"
        ], 200);
       } catch (QueryException $e) {
            DB::rollback();
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e,
           "text" => "Ha ocurrido un error, al editar el cliente.",
           "icon" => "error",
           "title"=>"Error!"
       ],402);
       }

    }

    public function obtenerImagen(Request $request){

        $cliente =  ModelCliente::find($request->idCliente);
        //dd($cliente);

        return response()->json([
            "img"=>$cliente->url_imagen,
        ],200);


    }

    public function cambiarImagenCliente(Request $request){
       try {

        if ($request->file('foto_cliente_editar') <> null) {
            //dd("llego");
            $archivo = $request->file('foto_cliente_editar');
            $nameFile = $archivo->getClientOriginalName();


                if($nameFile <> "noimage.png"){
                    $name = 'IMG_'. time().".". $archivo->getClientOriginalExtension();
                    $path = public_path() . '/img_cliente';
                    $archivo->move($path, $name);

                    $cliente =  ModelCliente::find($request->clienteId);
                    $imgEliminar = $cliente->url_imagen;
                    $cliente->url_imagen =  $name;
                    $cliente->save();

                    $carpetaPublic = public_path();
                    $path = $carpetaPublic.'/img_cliente/'. $imgEliminar;
                    File::delete($path);



                }




        }else{
            return response()->json([
                "text" => "No ha seleccionado ninguna imagen.",
                "icon" => "warning",
                "title"=>"Advertencia!"
            ], 200);
        }


        return response()->json([
            "text" => "Cliente editado con éxito.",
            "icon" => "success",
            "title"=>"Exito!"
        ], 200);
       return response()->json([
       ]);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e,
           "text" => "Ha ocurrido un error.",
           "icon" => "error",
           "title"=>"Error!"
       ],402);
       }
    }

    public function desactivarCliente(Request $request){
        try {

                if($request->clienteId==1){
                    return response()->json([
                        "text" => "Este cliente no puede ser desactivado.",
                        "icon" => "warning",
                        "title"=>"Acción no permitida !"
                    ],402);
                }

                $cliente =  ModelCliente::find($request->clienteId);
                $cliente->estado_cliente_id =  2;
                $cliente->save();

            return response()->json([
                "text" => "Cliente desactivado con éxito.",
                "icon" => "success",
                "title"=>"Exito!"
            ],200);
       } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e,
                "text" => "Ha ocurrido un error.",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
       }

    }

    public function activarCliente(Request $request){
        try {
                $cliente =  ModelCliente::find($request->clienteId);
                $cliente->estado_cliente_id =  1;
                $cliente->save();

            return response()->json([
                "text" => "Cliente activado con éxito.",
                "icon" => "success",
                "title"=>"Exito!"
            ],200);
       } catch (QueryException $e) {
            return response()->json([

                'error' => $e,
                "text" => "Ha ocurrido un error.",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
       }

    }

    public function export(){
        try {

            return Excel::download(new ClientesExport, 'DatosClientes.xlsx');

        } catch (QueryException $e) {
            return response()->json([

                'error' => $e,
                "text" => "Ha ocurrido un error.",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
        }

    }

    public function descargarPlantillaCategoriaClientes()
    {
        $fecha = date('Y-m-d_H-i-s');
        return \Maatwebsite\Excel\Facades\Excel::download(
            new ClientesCategoriaPlantillaExport,
            'Plantilla_Categorias_Clientes_' . $fecha . '.xlsx'
        );
    }

    public function procesarPreviewCategorias(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => [
                    'required',
                    'file',
                    'max:20480',
                    'mimes:xlsx',
                    'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
            ], [
                'file.mimes' => 'El archivo debe ser de formato .xlsx',
                'file.mimetypes' => 'El archivo debe ser de formato .xlsx',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Validación',
                    'text'  => $validator->errors()->first(),
                ], 422);
            }

            $file = $request->file('file');
            $storedPath = $file->storeAs('imports', 'preview_categorias_' . time() . '.' . $file->getClientOriginalExtension());
            $fullPath = storage_path('app/' . $storedPath);

            $ext = strtolower($file->getClientOriginalExtension());
            if ($err = $this->assertExcelPathIsReadable($fullPath, $ext)) {
                Log::error("[PreviewCategorias] Validación previa falló: {$err}");
                return response()->json([
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'Ocurrió un problema al procesar el archivo.',
                    'error' => $err,
                ], 400);
            }

            // Leer el archivo y generar preview
            $data = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\Escalas\ClientesCategoriaMasivaImport(), $fullPath);
            
            $paraActualizar = [];
            $noActualizables = [];
            
            foreach ($data[0] as $rawRow) {
                // Normalizar llaves
                $norm = [];
                foreach ($rawRow as $k => $v) {
                    $k = is_string($k) ? trim($k) : $k;
                    $k = mb_strtolower($k, 'UTF-8');
                    $k = str_replace(
                        [' ', '-', 'á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
                        ['_', '_','a','e','i','o','u','a','e','i','o','u','n','N'],
                        $k
                    );
                    $norm[$k] = is_string($v) ? trim($v) : $v;
                }
                $row = collect($norm);

                $idCliente = $row->get('id');
                $nuevaCat = $row->get('nueva_categoria_id');
                
                if ($nuevaCat === null || $nuevaCat === '') {
                    $nuevaCat = $row->get('cliente_categoria_escala_id');
                }
                if ($nuevaCat === null || $nuevaCat === '') {
                    $nuevaCat = $row->get('nueva_categoria');
                }

                // Saltar filas sin ID de cliente o sin nueva categoría (no es un error, simplemente no se procesa)
                if ($idCliente === null || $idCliente === '' || $nuevaCat === null || $nuevaCat === '') {
                    continue;
                }

                // Validaciones
                $error = null;
                
                if (!is_numeric((string)$idCliente) || !is_numeric((string)$nuevaCat)) {
                    $error = 'Valores no numéricos';
                } else {
                    $cliente = \App\Models\ModelCliente::find((int)$idCliente);
                    if (!$cliente) {
                        $error = 'Cliente no existe';
                    } else {
                        // Verificar si la categoría existe y está activa
                        $categoriaInfo = DB::selectOne("SELECT id, nombre_categoria, estado_id FROM cliente_categoria_escala WHERE id = ?", [(int)$nuevaCat]);
                        
                        if (!$categoriaInfo) {
                            $error = 'Categoría no existe';
                        } elseif ($categoriaInfo->estado_id == 2) {
                            $error = 'Categoría de cliente inactiva';
                        } else {
                            $old = (int)($cliente->cliente_categoria_escala_id ?? 0);
                            $new = (int)$nuevaCat;
                            
                            if ($old === $new) {
                                $error = 'Categoría sin cambios';
                            } else {
                                // Obtener nombre de categoría antigua
                                $categoriaAntigua = DB::selectOne("SELECT nombre_categoria FROM cliente_categoria_escala WHERE id = ?", [$old]);
                                
                                $paraActualizar[] = [
                                    'id' => $cliente->id,
                                    'nombre' => $cliente->nombre,
                                    'rtn' => $cliente->rtn,
                                    'categoria_antigua_id' => $old,
                                    'categoria_antigua_nombre' => $categoriaAntigua->nombre_categoria ?? 'Sin categoría',
                                    'categoria_nueva_id' => $new,
                                    'categoria_nueva_nombre' => $categoriaInfo->nombre_categoria,
                                ];
                            }
                        }
                    }
                }
                
                if ($error) {
                    $noActualizables[] = [
                        'id' => $idCliente ?? 'N/A',
                        'nombre' => $row->get('nombre') ?? 'N/A',
                        'rtn' => $row->get('rtn') ?? 'N/A',
                        'categoria_propuesta' => $nuevaCat ?? 'N/A',
                        'motivo' => $error,
                    ];
                }
            }

            // Guardar el path del archivo para usarlo después
            session(['preview_categorias_file' => $storedPath]);

            return response()->json([
                'icon' => 'success',
                'title' => 'Preview generado',
                'text' => 'Se han procesado ' . (count($paraActualizar) + count($noActualizables)) . ' registros.',
                'para_actualizar' => $paraActualizar,
                'no_actualizables' => $noActualizables,
            ], 200);

        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $trace = $e->getTraceAsString();
            Log::error("[PreviewCategorias] Excepción: {$msg}", ['trace' => $trace]);

            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Ocurrió un problema al procesar el archivo.',
                'error' => $msg,
            ], 500);
        }
    }

   public function listaCategoriasEscala(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $cats = \DB::table('cliente_categoria_escala')
            ->select('id', 'nombre_categoria')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('nombre_categoria', 'like', '%'.$q.'%');
            })
            ->orderBy('nombre_categoria')
            ->limit(50)
            ->get();

        return response()->json(['categorias' => $cats], 200);
    }

    public function importarCategoriaClientes(Request $request)
    {
        try {
            // Obtener el archivo previamente procesado de la sesión
            $storedPath = session('preview_categorias_file');
            
            if (!$storedPath) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Advertencia',
                    'text'  => 'No hay un archivo procesado. Por favor, procese el archivo primero.',
                ], 422);
            }

            $fullPath = storage_path('app/' . $storedPath);

            if (!file_exists($fullPath)) {
                session()->forget('preview_categorias_file');
                return response()->json([
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'El archivo procesado no existe. Por favor, procese el archivo nuevamente.',
                ], 400);
            }

            // Validar tamaño del archivo
            if (filesize($fullPath) == 0) {
                session()->forget('preview_categorias_file');
                @unlink($fullPath);
                return response()->json([
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'El archivo está vacío. Por favor, procese el archivo nuevamente.',
                ], 400);
            }

            // USAR toCollection CON LA MISMA CLASE DEL PREVIEW
            // Esto asegura que la lectura sea idéntica al preview (usa WithHeadingRow)
            $data = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\Escalas\ClientesCategoriaMasivaImport(), $fullPath);
            
            $actualizados = 0;
            $saltados = 0;
            $errores = [];
            
            foreach ($data[0] as $rawRow) {
                // Normalizar llaves (igual que en el preview)
                $norm = [];
                foreach ($rawRow as $k => $v) {
                    $k = is_string($k) ? trim($k) : $k;
                    $k = mb_strtolower($k, 'UTF-8');
                    $k = str_replace(
                        [' ', '-', 'á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
                        ['_', '_','a','e','i','o','u','a','e','i','o','u','n','N'],
                        $k
                    );
                    $norm[$k] = is_string($v) ? trim($v) : $v;
                }
                $row = collect($norm);

                $idCliente = $row->get('id');
                
                // Buscar nueva categoría en múltiples campos posibles (igual que preview)
                $nuevaCat = $row->get('nueva_categoria_id');
                if ($nuevaCat === null || $nuevaCat === '') {
                    $nuevaCat = $row->get('cliente_categoria_escala_id');
                }
                if ($nuevaCat === null || $nuevaCat === '') {
                    $nuevaCat = $row->get('nueva_categoria');
                }

                // Saltar filas sin datos (igual que preview)
                if ($idCliente === null || $idCliente === '' || $nuevaCat === null || $nuevaCat === '') {
                    $saltados++;
                    continue;
                }

                // Validaciones
                if (!is_numeric((string)$idCliente) || !is_numeric((string)$nuevaCat)) {
                    $errores[] = "Cliente ID '{$idCliente}': valores no numéricos";
                    continue;
                }

                \DB::beginTransaction();
                try {
                    $cliente = \App\Models\ModelCliente::lockForUpdate()->find((int)$idCliente);
                    if (!$cliente) {
                        $errores[] = "Cliente ID {$idCliente} no existe";
                        \DB::rollBack();
                        continue;
                    }

                    // Verificar que la categoría exista y esté activa
                    $categoriaInfo = DB::selectOne("SELECT id, nombre_categoria, estado_id FROM cliente_categoria_escala WHERE id = ?", [(int)$nuevaCat]);
                    
                    if (!$categoriaInfo) {
                        $errores[] = "Cliente ID {$idCliente}: Categoría {$nuevaCat} no existe";
                        \DB::rollBack();
                        continue;
                    }
                    
                    if ($categoriaInfo->estado_id == 2) {
                        $errores[] = "Cliente ID {$idCliente}: Categoría inactiva";
                        \DB::rollBack();
                        continue;
                    }

                    $old = (int)($cliente->cliente_categoria_escala_id ?? 0);
                    $new = (int)$nuevaCat;

                    if ($old === $new) {
                        $saltados++;
                        \DB::commit();
                        continue;
                    }

                    // Actualizar cliente
                    $cliente->cliente_categoria_escala_id = $new;
                    $cliente->save();

                    // Registrar log
                    DB::table('cliente_categoria_escala_logs')->insert([
                        'cliente_id'        => $cliente->id,
                        'antigua_categoria' => $old ?: null,
                        'nueva_categoria'   => $new,
                        'comentario'        => 'Actualización masiva por Excel',
                        'users_id'          => Auth::id() ?? 1,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                    \DB::commit();
                    $actualizados++;
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    $errores[] = "Cliente ID {$idCliente}: {$e->getMessage()}";
                }
            }

            // Limpiar la sesión
            session()->forget('preview_categorias_file');
            
            // Eliminar el archivo temporal
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }

            return response()->json([
                'icon'    => 'success',
                'title'   => 'Importación completada',
                'text'    => "Actualizados: {$actualizados} | Saltados: {$saltados} | Errores: " . count($errores),
                'errores' => array_slice($errores, 0, 10),
            ], 200);

        } catch (\Throwable $e) {
            // Guardar detalle completo del error en un log dedicado (útil para cPanel)
            $msg = $e->getMessage();
            $trace = $e->getTraceAsString();
            Log::error("[ImportarCategorias] Excepción: {$msg}", ['trace' => $trace]);
            file_put_contents(storage_path('logs/import_categorias_exception.log'), date('c') . " " . $msg . PHP_EOL . $trace . PHP_EOL . PHP_EOL, FILE_APPEND);

            // Mensajes específicos según el error
            $userMessage = 'Ocurrió un problema al procesar el archivo.';
            
            if (strpos($msg, 'Document is empty') !== false || 
                strpos($msg, 'simplexml_load_string') !== false) {
                $userMessage = 'El archivo Excel está vacío o corrupto. Verificá que el archivo contenga datos válidos.';
            } elseif (strpos($msg, 'parse error') !== false) {
                $userMessage = 'El archivo no se puede leer. Asegurate de que sea un archivo Excel válido (.xlsx).';
            } elseif (strpos($msg, 'ZIP') !== false) {
                $userMessage = 'El archivo no es un Excel válido. Intentá guardarlo nuevamente desde Excel.';
            }

            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => $userMessage,
                'error' => $msg,
            ], 500);
        }
    }

    /**
     * Valida que el archivo subido sea legible para el tipo indicado.
     * @param string $path Ruta completa al archivo en disco
     * @param string $ext  extensión (xlsx|xls|csv)
     * @return string|null Mensaje de error o null si OK
     */
    private function assertExcelPathIsReadable(string $path, string $ext): ?string
    {
        // 1) existe y legible
        if (!file_exists($path) || !is_readable($path)) {
            return "Archivo no existe o no es legible en path: {$path}";
        }

        // 2) tamaño > 0
        if (filesize($path) === 0) {
            return "Archivo con tamaño 0 bytes (posible subida fallida)";
        }

        // 3) comportamiento por extensión
        if ($ext === 'xlsx') {
            // xlsx es un ZIP con XMLs: requiere Zip extension y que abra como ZIP
            if (!extension_loaded('zip')) {
                return "Extensión PHP 'zip' no está instalada/activada. Habilítala en cPanel (Select PHP Version) y reinténtalo.";
            }
            $zip = new ZipArchive;
            $res = $zip->open($path);
            if ($res !== true) {
                return "No se pudo abrir el archivo .xlsx como ZIP. Código ZipArchive: {$res}. El archivo podría estar corrupto o no ser .xlsx.";
            }
            // chequear que existan archivos internos (al menos 1)
            if ($zip->numFiles === 0) {
                $zip->close();
                return "El archivo .xlsx parece estar vacío (numFiles = 0).";
            }
            // opción: comprobar existencia de [Content_Types].xml mínimo
            $hasContentTypes = ($zip->locateName('[Content_Types].xml') !== false);
            $zip->close();
            if (!$hasContentTypes) {
                return "El archivo .xlsx no contiene [Content_Types].xml (podría estar corrupto o no ser un .xlsx estándar).";
            }
        } elseif ($ext === 'csv') {
            // csv: comprobación simple de que se puede abrir como texto
            $h = @fopen($path, 'r');
            if ($h === false) return "No se pudo abrir el archivo CSV para lectura.";
            $line = @fgets($h);
            @fclose($h);
            if ($line === false) return "El CSV parece vacío o ilegible.";
        } elseif ($ext === 'xls') {
            // xls (BIFF): PhpSpreadsheet lo maneja pero no podemos abrir con zip.
            // comprobación básica: fichero no vacío (ya hecho) y opcional: detectar cabecera BIFF (D0 CF 11 E0 ...)
            $fh = @fopen($path, 'rb');
            if ($fh === false) return "No se pudo abrir el archivo .xls para lectura.";
            $header = fread($fh, 8);
            fclose($fh);
            if ($header === false || strlen($header) < 4) {
                return "El archivo .xls no tiene cabecera válida o está corrupto.";
            }
            // no hacemos más validaciones porque .xls es binario complejo
        } else {
            return "Extensión no soportada: {$ext}";
        }

        return null; // todo OK
    }

}
