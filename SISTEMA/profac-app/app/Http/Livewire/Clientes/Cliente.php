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
use App\Models\ClienteCredito;
use App\Services\CreditoService;
use App\Models\ClienteObservacion;
use App\Models\ClienteDocumento;

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

        $metodosPago = DB::select("SELECT id, descripcion FROM tipo_pago_cobro ORDER BY descripcion ASC");

        return view('livewire.clientes.cliente', compact('clientes', 'metodosPago'));
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
        $validator = Validator::make($request->all(), [
            'correo_cliente' => 'required|email',
        ], [
            'correo_cliente.required' => 'El correo del cliente es obligatorio.',
            'correo_cliente.email' => 'El correo del cliente no tiene un formato valido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'icon' => 'warning',
                'title' => 'Validacion',
                'text' => $validator->errors()->first(),
            ], 422);
        }

        // Verificar RTN duplicado antes de cualquier operación
        $rtnNuevo = trim($request->rtn_cliente ?? '');
        if ($rtnNuevo !== '' && ModelCliente::where('rtn', $rtnNuevo)->exists()) {
            return response()->json([
                'icon'  => 'warning',
                'title' => 'RTN duplicado',
                'text'  => 'Ya existe un cliente registrado con el RTN "' . $rtnNuevo . '". Verifique el número e intente de nuevo.',
                'type'  => 'rtn_duplicado',
            ], 422);
        }

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
            $cliente->ano_operacion           = $request->ano_operacion ?? null;
            $cliente->dni_representante_legal = trim($request->dni_representante_legal ?? '');
            $cliente->metodo_pago             = trim($request->metodo_pago ?? '');
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
                $cliente->ano_operacion           = $request->ano_operacion ?? null;
                $cliente->dni_representante_legal = trim($request->dni_representante_legal ?? '');
                $cliente->metodo_pago             = trim($request->metodo_pago ?? '');
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

                $activarDesactivar = $cliente->estado_cliente_id == 1
                    ? '<a class="dropdown-item" onclick="desactivarClienteModal('.$cliente->idCliente.')"><i class="fa fa-times text-danger mr-2"></i>Desactivar</a>'
                    : '<a class="dropdown-item" onclick="activarCliente('.$cliente->idCliente.')"><i class="fa fa-check-circle text-success mr-2"></i>Activar</a>';

                return
                '<div class="cli-dropdown dropdown">
                    <button class="btn-cli-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="/clientes/form/'.$cliente->idCliente.'">
                            <i class="fa fa-pencil text-warning mr-2"></i>Editar cliente
                        </a>
                        <a class="dropdown-item" onclick="modalEditarFotografia('.$cliente->idCliente.')">
                            <i class="fa fa-camera text-info mr-2"></i>Cambiar fotografía
                        </a>
                        <div class="dropdown-divider"></div>
                        '.$activarDesactivar.'
                    </div>
                </div>';
            })
            ->addColumn('estado', function ($cliente) {
                if ($cliente->estado_cliente_id === 1) {
                    return '<span class="estado-pill activo">● ACTIVO</span>';
                } else {
                    return '<span class="estado-pill inactivo">● INACTIVO</span>';
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
            ->where('estado_id', 1)
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
    /**
     * GET /clientes/form/datos/{id}  — datos completos para la vista de formulario
     */
    public function datosFormCliente(Request $request)
    {
        try {
            $id = $request->route('id');
            $datosCliente = DB::selectOne("
                SELECT c.*,
                       (SELECT nombre_categoria FROM cliente_categoria_escala WHERE id = c.cliente_categoria_escala_id) AS nombre_cat_escala
                FROM cliente c
                WHERE c.id = ?", [$id]);

            if (!$datosCliente) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            $contactos = DB::select("SELECT id, nombre, telefono FROM contacto WHERE estado_id = 1 AND cliente_id = ? ORDER BY id ASC LIMIT 2", [$id]);

            $ubicacion = $datosCliente->municipio_id
                ? DB::selectOne("SELECT C.id as idPais, A.id as idDepto, B.id as idMunicipio
                    FROM departamento A
                    INNER JOIN municipio B ON A.id = B.departamento_id
                    INNER JOIN pais C ON C.id = A.pais_id
                    WHERE B.id = ?", [$datosCliente->municipio_id])
                : (object)['idPais' => null, 'idDepto' => null, 'idMunicipio' => null];

            $paises     = DB::select("SELECT id, nombre FROM pais ORDER BY nombre ASC");
            $deptos     = $ubicacion->idPais   ? DB::select("SELECT id, nombre FROM departamento WHERE pais_id = ? ORDER BY nombre ASC", [$ubicacion->idPais])   : [];
            $municipios = $ubicacion->idDepto  ? DB::select("SELECT id, nombre FROM municipio WHERE departamento_id = ? ORDER BY nombre ASC", [$ubicacion->idDepto]) : [];

            $tipoPersonalidad = DB::select("SELECT id, nombre FROM tipo_personalidad");
            $tipoCliente      = DB::select("SELECT id, descripcion FROM tipo_cliente");
            $vendedores       = DB::select("SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC");
            $metodosPago      = DB::select("SELECT id, descripcion FROM tipo_pago_cobro ORDER BY id ASC");

            $credito = DB::selectOne("SELECT * FROM cliente_credito WHERE cliente_id = ? ORDER BY id DESC LIMIT 1", [$id]);
            $historicoCredito = DB::select("SELECT cc.*, u.name as usuario, v.name as nombre_vendedor FROM cliente_credito cc LEFT JOIN users u ON u.id = cc.users_id LEFT JOIN users v ON v.id = cc.vendedor_id WHERE cc.cliente_id = ? ORDER BY cc.id DESC", [$id]);

            $observaciones = DB::select("SELECT co.*, u.name as usuario FROM cliente_observaciones co LEFT JOIN users u ON u.id = co.users_id WHERE co.cliente_id = ? ORDER BY co.id DESC", [$id]);

            $documentos = DB::select("SELECT * FROM cliente_documentos WHERE cliente_id = ? ORDER BY tipo_documento ASC, id DESC", [$id]);

            /* Tipos de documento marcados como "tiene físico" */
            $docFisico = DB::select("SELECT tipo_documento FROM cliente_doc_fisico WHERE cliente_id = ?", [$id]);
            $docFisicoList = array_column((array)$docFisico, 'tipo_documento');

            $montoDisponible = CreditoService::calcularDisponible((int)$id, (float)($datosCliente->credito_inicial ?? 0));

            return response()->json([
                'datosCliente'     => $datosCliente,
                'contactos'        => $contactos,
                'ubicacion'        => $ubicacion,
                'paises'           => $paises,
                'deptos'           => $deptos,
                'municipios'       => $municipios,
                'tipoPersonalidad' => $tipoPersonalidad,
                'tipoCliente'      => $tipoCliente,
                'vendedores'       => $vendedores,
                'metodosPago'      => $metodosPago,
                'credito'          => $credito,
                'historicoCredito' => $historicoCredito,
                'observaciones'    => $observaciones,
                'documentos'       => $documentos,
                'doc_fisico'       => $docFisicoList,
                'monto_disponible' => $montoDisponible,
            ], 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /clientes/crear-completo — crear cliente con todos los tabs
     */
    public function crearClienteCompleto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
        ], [
            'correo.required' => 'El correo del cliente es obligatorio.',
            'correo.email' => 'El correo del cliente no tiene un formato valido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'icon'  => 'warning',
                'title' => 'Validacion',
                'text'  => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ---- foto ----
            $nombreImagen = null;
            if ($request->hasFile('foto_cliente')) {
                $archivo = $request->file('foto_cliente');
                $nombreImagen = 'IMG_' . time() . '.' . $archivo->getClientOriginalExtension();
                $archivo->move(public_path('img_cliente'), $nombreImagen);
            }

            $nombre = trim(str_replace(["'", '"', '´'], ' ', $request->nombre_cliente));

            $cliente = new ModelCliente;
            $cliente->nombre                     = $nombre;
            $cliente->rtn                        = trim($request->rtn_cliente ?? '');
            $cliente->tipo_personalidad_id       = $request->tipo_personalidad_id;
            $cliente->tipo_cliente_id            = $request->tipo_cliente_id;
            $cliente->categoria_id               = $request->tipo_cliente_id;
            $cliente->ano_operacion              = $request->ano_operacion ?? null;
            $cliente->dni_representante_legal    = trim($request->dni_representante ?? '');
            $cliente->estado_cliente_id          = $request->estado_activo ? 1 : 2;
            $cliente->correo                     = trim($request->correo ?? '');
            $cliente->telefono_empresa           = trim($request->telefono ?? '');
            $cliente->direccion                  = trim($request->direccion ?? '');
            $cliente->municipio_id               = $request->municipio_id ?? 1;
            $cliente->vendedor                   = $request->vendedor_id ?? Auth::user()->id;
            $cliente->credito_inicial            = str_replace(',', '', $request->credito ?? '0');
            $cliente->credito                    = str_replace(',', '', $request->credito ?? '0');
            $cliente->dias_credito               = $request->dias_credito ?? 0;
            $cliente->latitud                    = trim($request->latitud ?? '');
            $cliente->longitud                   = trim($request->longitud ?? '');
            $cliente->metodo_pago                = trim($request->dp_metodo_pago ?? '');
            $cliente->users_id                   = Auth::user()->id;
            $cliente->cliente_categoria_escala_id = $request->cliente_categoria_escala_id ?? null;
            if ($nombreImagen) $cliente->url_imagen = $nombreImagen;
            $cliente->save();

            // ---- contactos ----
            foreach ([
                ['nombre' => 'nombre_contacto1', 'telefono' => 'telefono_contacto1'],
                ['nombre' => 'nombre_contacto2', 'telefono' => 'telefono_contacto2'],
            ] as $c) {
                $nom = trim($request->input($c['nombre'], ''));
                $tel = trim($request->input($c['telefono'], ''));
                if ($nom !== '' || $tel !== '') {
                    ModelContacto::create(['nombre' => $nom, 'telefono' => $tel, 'cliente_id' => $cliente->id, 'estado_id' => 1]);
                }
            }

            // ---- crédito ----
            if ($request->filled('credito')) {
                ClienteCredito::where('cliente_id', $cliente->id)->update(['activo' => 0]);
                ClienteCredito::create([
                    'cliente_id'              => $cliente->id,
                    'activo'                  => 1,
                    'credito_activo'          => $request->credito_activo ? 1 : 0,
                    'credito'                 => str_replace(',', '', $request->credito ?? '0'),
                    'dias_credito'            => $request->dias_credito ?? 0,
                    'vendedor_id'             => $request->vendedor_id,
                    'referencias_bancarias'   => trim($request->referencias_bancarias ?? ''),
                    'referencias_comerciales' => trim($request->referencias_comerciales ?? ''),
                    'metodo_pago'             => trim($request->metodo_pago ?? ''),
                    'letra_cambio'            => $request->boolean('letra_cambio') ? 1 : 0,
                    'obs_letra_cambio'        => trim($request->obs_letra_cambio ?? ''),
                    'aval_solidario'          => $request->boolean('aval_solidario') ? 1 : 0,
                    'obs_aval_solidario'      => trim($request->obs_aval_solidario ?? ''),
                    'autorizacion_gerencia'   => trim($request->autorizacion_gerencia ?? ''),
                    'users_id'               => Auth::user()->id,
                ]);
            }

            DB::commit();
            try { $this->logHistorial($cliente->id, 'Cliente registrado', $cliente->nombre); } catch (\Throwable $e) {}
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Cliente registrado con éxito.', 'id' => $cliente->id], 200);

        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al registrar el cliente.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /clientes/editar-completo — editar datos principales + contacto + dirección
     */
    public function editarClienteCompleto(Request $request)
    {
        try {
            DB::beginTransaction();

            $id     = $request->cliente_id;
            $cliente = ModelCliente::findOrFail($id);

            $nombre = trim(str_replace(["'", '"', '´'], ' ', $request->nombre_cliente));
            $cliente->nombre                     = $nombre;
            $cliente->rtn                        = trim($request->rtn_cliente ?? '');
            $cliente->tipo_personalidad_id       = $request->tipo_personalidad_id;
            $cliente->tipo_cliente_id            = $request->tipo_cliente_id;
            $cliente->categoria_id               = $request->tipo_cliente_id;
            $cliente->ano_operacion              = $request->ano_operacion ?? null;
            $cliente->dni_representante_legal    = trim($request->dni_representante ?? '');
            $cliente->estado_cliente_id          = $request->estado_activo ? 1 : 2;
            $cliente->correo                     = trim($request->correo ?? '');
            $cliente->telefono_empresa           = trim($request->telefono ?? '');
            $cliente->direccion                  = trim($request->direccion ?? '');
            $cliente->municipio_id               = $request->municipio_id ?? $cliente->municipio_id;
            $cliente->vendedor                   = $request->dp_vendedor_id ?? $request->vendedor_id ?? $cliente->vendedor;
            $cliente->metodo_pago                = trim($request->dp_metodo_pago ?? $cliente->metodo_pago ?? '');
            $cliente->users_id                   = Auth::user()->id;
            $cliente->cliente_categoria_escala_id = $request->cliente_categoria_escala_id ?? $cliente->cliente_categoria_escala_id;

            // Track exact fields changed using Laravel dirty detection
            $fieldLabels = [
                'nombre' => 'Nombre', 'rtn' => 'RTN', 'tipo_personalidad_id' => 'Tipo Personalidad',
                'tipo_cliente_id' => 'Tipo Cliente', 'cliente_categoria_escala_id' => 'Categoría',
                'ano_operacion' => 'Año Operación', 'dni_representante_legal' => 'DNI Representante',
                'estado_cliente_id' => 'Estado', 'correo' => 'Correo', 'telefono_empresa' => 'Teléfono',
                'direccion' => 'Dirección', 'municipio_id' => 'Municipio',
                'vendedor' => 'Vendedor', 'metodo_pago' => 'Método de Pago',
            ];
            $dirty = $cliente->getDirty();
            $changed = array_values(array_filter(array_map(fn($f, $l) => array_key_exists($f, $dirty) ? $l : null, array_keys($fieldLabels), $fieldLabels)));
            $logDesc = count($changed) > 0 ? 'Campos: ' . implode(', ', $changed) : 'Sin cambios en datos principales';

            $cliente->save();

            // ---- contactos ----
            ModelContacto::where('cliente_id', $id)->update(['estado_id' => 2]);
            foreach ([
                ['nombre' => 'nombre_contacto1', 'telefono' => 'telefono_contacto1'],
                ['nombre' => 'nombre_contacto2', 'telefono' => 'telefono_contacto2'],
            ] as $c) {
                $nom = trim($request->input($c['nombre'], ''));
                $tel = trim($request->input($c['telefono'], ''));
                if ($nom !== '' || $tel !== '') {
                    ModelContacto::create(['nombre' => $nom, 'telefono' => $tel, 'cliente_id' => $id, 'estado_id' => 1]);
                }
            }

            DB::commit();
            try { $this->logHistorial($id, 'Cliente actualizado', $logDesc); } catch (\Throwable $e) {}
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Cliente actualizado con éxito.'], 200);

        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al actualizar el cliente.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /clientes/credito/guardar
     */
    public function guardarCredito(Request $request)
    {
        try {
            DB::beginTransaction();
            $id = $request->cliente_id;

            // Actualizar crédito en tabla cliente
            $cliente = ModelCliente::findOrFail($id);
            $credito = str_replace(',', '', $request->credito ?? '0');
            $cliente->credito_inicial = $credito;
            $cliente->dias_credito    = $request->dias_credito ?? $cliente->dias_credito;
            $cliente->vendedor        = $request->vendedor_id ?? $cliente->vendedor;
            $cliente->save();

            // Inactivar registro anterior y guardar nuevo historial de crédito
            ClienteCredito::where('cliente_id', $id)->update(['activo' => 0]);
            ClienteCredito::create([
                'cliente_id'              => $id,
                'activo'                  => 1,
                'credito_activo'          => $request->credito_activo ? 1 : 0,
                'credito'                 => $credito,
                'dias_credito'            => $request->dias_credito ?? 0,
                'fecha_vigencia'          => $request->fecha_vigencia ?: null,
                'vendedor_id'             => $request->vendedor_id,
                'referencias_bancarias'   => trim($request->referencias_bancarias ?? ''),
                'referencias_comerciales' => trim($request->referencias_comerciales ?? ''),
                'metodo_pago'             => trim($request->metodo_pago ?? ''),
                'letra_cambio'            => $request->boolean('letra_cambio') ? 1 : 0,
                'obs_letra_cambio'        => trim($request->obs_letra_cambio ?? ''),
                'aval_solidario'          => $request->boolean('aval_solidario') ? 1 : 0,
                'obs_aval_solidario'      => trim($request->obs_aval_solidario ?? ''),
                'autorizacion_gerencia'   => trim($request->autorizacion_gerencia ?? ''),
                'users_id'               => Auth::user()->id,
            ]);

            // Recalcular monto disponible con el nuevo límite y persistir en cliente.credito
            $montoDisponible = CreditoService::actualizarDisponible((int)$id, (float)$credito);

            DB::commit();
            try { $this->logHistorial($id, 'Crédito actualizado', 'Monto: L ' . number_format((float)$credito, 2) . ' | Días: ' . ($request->dias_credito ?? 0)); } catch (\Throwable $e) {}
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Crédito actualizado con éxito.', 'monto_disponible' => $montoDisponible], 200);

        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al guardar crédito.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /clientes/credito/historico/{id}
     */
    public function historicoCredito(Request $request)
    {
        $id   = $request->route('id');
        $rows = DB::select("SELECT cc.*, u.name as usuario, v.name as nombre_vendedor FROM cliente_credito cc LEFT JOIN users u ON u.id = cc.users_id LEFT JOIN users v ON v.id = cc.vendedor_id WHERE cc.cliente_id = ? ORDER BY cc.id DESC", [$id]);
        return response()->json(['historico' => $rows], 200);
    }

    /**
     * POST /clientes/observacion/guardar
     */
    public function guardarObservacion(Request $request)
    {
        try {
            $obs = ClienteObservacion::create([
                'cliente_id' => $request->cliente_id,
                'observacion' => trim($request->observacion),
                'users_id'   => Auth::user()->id,
            ]);
            $obs->usuario = Auth::user()->name;
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Observación guardada.', 'observacion' => $obs], 200);
        } catch (QueryException $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al guardar observación.'], 500);
        }
    }

    /**
     * GET /clientes/observaciones/{id}
     */
    public function listarObservaciones(Request $request)
    {
        $id   = $request->route('id');
        $rows = DB::select("SELECT co.*, u.name as usuario FROM cliente_observaciones co LEFT JOIN users u ON u.id = co.users_id WHERE co.cliente_id = ? ORDER BY co.id DESC", [$id]);
        return response()->json(['observaciones' => $rows], 200);
    }

    /**
     * POST /clientes/documento/subir
     */
    public function subirDocumento(Request $request)
    {
        try {
            $tiposPermitidos = array_keys(ClienteDocumento::$tipos);
            if (!in_array($request->tipo_documento, $tiposPermitidos, true)) {
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Tipo de documento inválido.'], 422);
            }

            if (!$request->hasFile('documento')) {
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'No se recibió ningún archivo.'], 422);
            }

            $archivo    = $request->file('documento');
            $ext        = strtolower($archivo->getClientOriginalExtension());
            $permitidos = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'doc', 'docx', 'xlsx', 'xls'];

            if (!in_array($ext, $permitidos)) {
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Extensión de archivo no permitida.'], 422);
            }

            $carpeta = public_path('archivo_clientes');
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

            // Nombre: {nombre_cliente}_{id}_{tipo}.{ext}
            $cliente = ModelCliente::findOrFail($request->cliente_id);
            $nombreSanitizado = mb_strtolower($cliente->nombre, 'UTF-8');
            $nombreSanitizado = str_replace(
                ['á','é','í','ó','ú','ñ','ü','Á','É','Í','Ó','Ú','Ñ','Ü',' '],
                ['a','e','i','o','u','n','u','a','e','i','o','u','n','u','_'],
                $nombreSanitizado
            );
            $nombreSanitizado = preg_replace('/[^a-z0-9_]/', '', $nombreSanitizado);
            $nombreSanitizado = substr(preg_replace('/_+/', '_', trim($nombreSanitizado, '_')), 0, 50);
            $nombreArchivo    = $nombreSanitizado . '_' . $cliente->id . '_' . $request->tipo_documento . '.' . $ext;

            $nombreOriginal = $archivo->getClientOriginalName();

            // Reemplazar si ya existe un documento de este tipo para este cliente
            $docExistente = ClienteDocumento::where('cliente_id', $request->cliente_id)
                ->where('tipo_documento', $request->tipo_documento)
                ->first();

            if ($docExistente) {
                $rutaAntigua = public_path('archivo_clientes/' . $docExistente->ruta_archivo);
                if (file_exists($rutaAntigua)) @unlink($rutaAntigua);
                $archivo->move($carpeta, $nombreArchivo);
                $docExistente->update([
                    'nombre_original' => $nombreOriginal,
                    'ruta_archivo'    => $nombreArchivo,
                    'users_id'        => Auth::user()->id,
                ]);
                $doc = $docExistente->fresh();
            } else {
                $archivo->move($carpeta, $nombreArchivo);
                $doc = ClienteDocumento::create([
                    'cliente_id'      => $request->cliente_id,
                    'tipo_documento'  => $request->tipo_documento,
                    'nombre_original' => $nombreOriginal,
                    'ruta_archivo'    => $nombreArchivo,
                    'users_id'        => Auth::user()->id,
                ]);
            }

            try { $this->logHistorial($request->cliente_id, 'Documento subido', ClienteDocumento::$tipos[$request->tipo_documento] ?? $request->tipo_documento); } catch (\Throwable $e) {}

            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Documento subido.', 'documento' => $doc], 200);

        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al subir documento.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /clientes/documentos/{id}
     */
    public function listarDocumentos(Request $request)
    {
        $id      = $request->route('id');
        $rows    = DB::select("SELECT * FROM cliente_documentos WHERE cliente_id = ? ORDER BY tipo_documento ASC, id DESC", [$id]);
        $fisico  = DB::select("SELECT tipo_documento FROM cliente_doc_fisico WHERE cliente_id = ?", [$id]);
        return response()->json([
            'documentos' => $rows,
            'doc_fisico' => array_column((array)$fisico, 'tipo_documento'),
        ], 200);
    }

    /**
     * POST /clientes/documento/fisico/toggle
     * Marca o desmarca un tipo de documento como "tiene físico sin digital"
     */
    public function toggleDocFisico(Request $request)
    {
        try {
            $clienteId = (int)$request->cliente_id;
            $tipo      = $request->tipo_documento;
            $tiposPermitidos = array_keys(ClienteDocumento::$tipos);

            if (!$clienteId || !in_array($tipo, $tiposPermitidos, true)) {
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Datos inválidos.'], 422);
            }

            $exists = DB::selectOne(
                "SELECT id FROM cliente_doc_fisico WHERE cliente_id = ? AND tipo_documento = ?",
                [$clienteId, $tipo]
            );

            if ($exists) {
                DB::delete("DELETE FROM cliente_doc_fisico WHERE cliente_id = ? AND tipo_documento = ?", [$clienteId, $tipo]);
                $activo = false;
                try { $this->logHistorial($clienteId, 'Documento físico desmarcado', ClienteDocumento::$tipos[$tipo] ?? $tipo); } catch (\Throwable $e) {}
            } else {
                DB::insert(
                    "INSERT INTO cliente_doc_fisico (cliente_id, tipo_documento, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
                    [$clienteId, $tipo]
                );
                $activo = true;
                try { $this->logHistorial($clienteId, 'Documento físico marcado', ClienteDocumento::$tipos[$tipo] ?? $tipo); } catch (\Throwable $e) {}
            }

            return response()->json(['activo' => $activo], 200);
        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al guardar.'], 500);
        }
    }

    /**
     * DELETE /clientes/documento/{id}
     */
    public function eliminarDocumento(Request $request)
    {
        try {
            $id  = $request->route('id');
            $doc = ClienteDocumento::findOrFail($id);
            $ruta = public_path('archivo_clientes/' . $doc->ruta_archivo);
            if (file_exists($ruta)) @unlink($ruta);
            $clienteId = $doc->cliente_id;
            $tipoLabel = ClienteDocumento::$tipos[$doc->tipo_documento] ?? $doc->tipo_documento;
            $doc->delete();
            try { $this->logHistorial($clienteId, 'Documento eliminado', $tipoLabel); } catch (\Throwable $e) {}
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Documento eliminado.'], 200);
        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al eliminar.'], 500);
        }
    }

    /**
     * GET /clientes/documento/descargar/{id}
     */
    public function descargarDocumento(Request $request)
    {
        $id  = $request->route('id');
        $doc = ClienteDocumento::findOrFail($id);
        $ruta = public_path('archivo_clientes/' . $doc->ruta_archivo);
        if (!file_exists($ruta)) abort(404);
        return response()->download($ruta, $doc->nombre_original);
    }

    /**
     * GET /clientes/documento/ver/{id}  — sirve el archivo en línea (para vista previa)
     */
    public function verDocumento(Request $request)
    {
        $id  = $request->route('id');
        $doc = ClienteDocumento::findOrFail($id);
        $ruta = public_path('archivo_clientes/' . $doc->ruta_archivo);
        if (!file_exists($ruta)) abort(404);
        $ext = strtolower(pathinfo($doc->ruta_archivo, PATHINFO_EXTENSION));
        $mimes = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
        ];
        $mime = $mimes[$ext] ?? 'application/octet-stream';
        return response()->file($ruta, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($doc->nombre_original) . '"',
        ]);
    }

    /**
     * GET /clientes/historial/{id}  — historial de cambios del cliente
     */
    public function historialCambios(Request $request)
    {
        $id   = $request->route('id');
        $rows = DB::select(
            "SELECT ch.*, u.name as usuario FROM cliente_historial ch LEFT JOIN users u ON u.id = ch.users_id WHERE ch.cliente_id = ? ORDER BY ch.id DESC LIMIT 100",
            [$id]
        );
        return response()->json(['historial' => $rows], 200);
    }

    /**
     * GET /clientes/form/{id?} — vista de formulario (crear o editar)
     */
    public function vistaFormCliente(Request $request)
    {
        $id = $request->route('id');
        $clientes     = DB::select("SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC");
        $metodosPago  = DB::select("SELECT id, descripcion FROM tipo_pago_cobro ORDER BY id ASC");
        return view('livewire.clientes.cliente-form', compact('id', 'clientes', 'metodosPago'));
    }

    private function logHistorial(int $clienteId, string $accion, ?string $descripcion = null): void
    {
        DB::table('cliente_historial')->insert([
            'cliente_id'  => $clienteId,
            'accion'      => $accion,
            'descripcion' => $descripcion,
            'users_id'    => Auth::id(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * POST /clientes/referencias/guardar
     */
    public function guardarReferencias(Request $request)
    {
        try {
            $id      = $request->cliente_id;
            $cliente = ModelCliente::findOrFail($id);
            $cliente->ref_referencias    = trim($request->ref_referencias ?? '');
            $cliente->ref_tiempo_relacion = trim($request->ref_tiempo_relacion ?? '');
            $cliente->ref_tiempo_credito  = trim($request->ref_tiempo_credito ?? '');
            $cliente->ref_limite_credito  = $request->ref_limite_credito ? str_replace(',', '', $request->ref_limite_credito) : null;
            $cliente->ref_observaciones   = trim($request->ref_observaciones ?? '');
            $cliente->save();
            try { $this->logHistorial($id, 'Comentarios/Referencias actualizados', null); } catch (\Throwable $e) {}
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Comentarios/Referencias guardados.'], 200);
        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al guardar referencias.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /clientes/autorizacion/guardar
     * Actualiza solo autorizacion_gerencia en el registro de crédito activo
     */
    public function guardarAutorizacionGerencia(Request $request)
    {
        try {
            $id      = $request->cliente_id;
            $credito = ClienteCredito::where('cliente_id', $id)->where('activo', 1)->first();
            if (!$credito) {
                return response()->json(['icon' => 'warning', 'title' => 'Sin crédito activo', 'text' => 'Debe guardar un registro de crédito primero.'], 422);
            }
            $credito->autorizacion_gerencia = trim($request->autorizacion_gerencia ?? '');
            $credito->save();
            try { $this->logHistorial($id, 'Autorización de Gerencia actualizada', null); } catch (\Throwable $e) {}
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Autorización de Gerencia guardada.'], 200);
        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Error al guardar autorización.', 'error' => $e->getMessage()], 500);
        }
    }

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
