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

use App\Models\ModelCliente;
use App\Models\ModelContacto;
use App\Models\logCredito;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientesExport;


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
            "text" => "Registro de pago realizo con exito!",
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
            inner join estado_cliente
            on estado_cliente.id = cliente.estado_cliente_id
            inner join users
            on users.id = cliente.users_id
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


}
