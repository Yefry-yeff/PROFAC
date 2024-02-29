<?php

namespace App\Http\Livewire\Inventario;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\ModelPagoCompra;
use App\Models\ModelCompra;
use App\Models\ModelIncidencia;
use App\Models\ModelRecibirBodega;
use App\Models\ModelIncidenciaCompra;
use App\Models\ModelLogTranslados;
use Auth;
use DataTables;
use Validator;
use Illuminate\Support\Facades\File;
use Mail;

use Livewire\Component;

class RecibirProducto extends Component
{
    public $idCompra;
    public function mount($id)
    {

        $this->idCompra = $id;

    }

    public function render()
    {
        $idCompra = $this->idCompra;

        $datosCompra = DB::SELECTONE("
     select
        A.numero_factura,
        A.numero_orden,
        B.nombre
      from
      compra A
      inner join proveedores B
      on A.proveedores_id = B.id
      where A.id = ".$idCompra);



        return view('livewire.inventario.recibir-producto', compact('idCompra','datosCompra'));
    }

    public function listarProductos($id)
    {
        try {

            $listaCompra = DB::SELECT("

        select
            @i := @i + 1 as 'contador',
            A.compra_id,
            A.producto_id as 'producto_id',
            producto.nombre as nombre,
            A.precio_unidad,
            A.cantidad_ingresada as cantidad_comprada,
            A.cantidad_sin_asignar,
            A.sub_total_producto,
            A.isv,
            A.precio_total,
            if(A.fecha_expiracion is null, 'No definido',A.fecha_expiracion) as fecha_expiracion,
            (select sum(cantidad_inicial_seccion) from recibido_bodega where producto_id = A.producto_id and compra_id = A.compra_id ) as 'cantidad_ingresada_bodega',
            A.unidades_compra

        from
            compra_has_producto A
            inner join producto
            on producto.id = A.producto_id
            cross join (select @i := 0) r
            where A.compra_id = ".$id."


        ");

            return Datatables::of($listaCompra)
                ->addColumn('opciones', function ($listaCompra) {

                    if($listaCompra->cantidad_sin_asignar > 0){

                        return
                        '<div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                <li>
                                    <a class="dropdown-item" onclick="mostratModal(' . $listaCompra->compra_id . ',' . $listaCompra->producto_id . ')" > <i class="fa-solid fa-circle-exclamation text-success"></i> Recibir producto </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" onclick="mostrarModalExcedente(' . $listaCompra->compra_id . ',' . $listaCompra->producto_id . ')"> <i class="fa-solid fa-circle-plus text-info"></i> Registrar Producto Excedente </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" onclick="mostrarModalIncidenciaSinAlmacenar(' . $listaCompra->compra_id . ',' . $listaCompra->producto_id . ')" > <i class="fa-solid fa-circle-exclamation text-warning"></i> Registrar Incidencia </a>
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
                                <a class="dropdown-item" onclick="mostrarModalExcedente(' . $listaCompra->compra_id . ',' . $listaCompra->producto_id . ')"> <i class="fa-solid fa-circle-plus text-info"></i> Registrar Producto Excedente </a>
                            </li>
                            <li>
                                <a class="dropdown-item" onclick="mostrarModalIncidenciaSinAlmacenar(' . $listaCompra->compra_id . ',' . $listaCompra->producto_id . ')" > <i class="fa-solid fa-circle-exclamation text-warning"></i> Registrar Incidencia antes de almacenar</a>
                            </li>




                        </ul>
                    </div>';
                    }


                })
                ->addColumn('estado_recibido', function ($listaCompra){
                    $cantidadBodega = $listaCompra->cantidad_ingresada_bodega/$listaCompra->unidades_compra;
                    if($listaCompra->cantidad_comprada == $cantidadBodega){
                        return
                        '

                        <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.95rem">Recibido</span></p>
                        ';

                    }else if($cantidadBodega < $listaCompra->cantidad_comprada  ){
                        return
                        '
                        <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.95rem">Incompleto</span></p>
                        ';
                    }else if($cantidadBodega > $listaCompra->cantidad_comprada){
                        return
                        '
                        <p class="text-center"><span class="badge badge-info p-2" style="font-size:0.95rem">Excedente</span></p>
                        ';

                    }else if($cantidadBodega >= 0){
                        return
                        '
                        <p class="text-center"><span class="badge p-2" style="font-size:0.95rem">No recibido</span></p>
                        ';
                    }

                })

                ->rawColumns(['opciones','estado_recibido'])
                ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ],402);
        }
    }

    public function bodegasLista()
    {


        try {

            $listaBodegasRecibir = DB::SELECT("
            select
            id,
                nombre
            From
                bodega
            ");

            //$listaBodegasRecibir = modelBodega::all();

            return response()->json([
                "listaBodegas" => $listaBodegasRecibir
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

    public function listarSegmentos(Request $request)
    {
        try {

            $listaSegmentos = DB::select("
         select id, descripcion from segmento where bodega_id = " . $request->idBodega);



            return response()->json([
                "listaSegmentos" => $listaSegmentos
            ],200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ],402);
        }
    }

    public function listarSecciones(Request $request)
    {
        try {

            $listaSecciones = DB::select("
         select id, descripcion from seccion where segmento_id = " . $request->idSegmento);

            return response()->json([
                "listaSecciones" => $listaSecciones
            ],200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ],402);
        }
    }
    public function listarUmedidas($idProducto)
    {
        try {

            $listaUmedidas = DB::select("
                select
                unidad_medida_venta.id as id,
                concat(unidad_medida.nombre,' ',unidad_medida_venta.unidad_venta) as unidad,
                unidad_medida_venta.unidad_venta_defecto
                from producto
                inner join unidad_medida_venta
                on producto.id = unidad_medida_venta.producto_id
                inner join unidad_medida
                on unidad_medida_venta.unidad_medida_id = unidad_medida.id
                where producto.id = ".$idProducto."
            " );

            return response()->json([
                "listaUmedidas" => $listaUmedidas
            ],200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ],402);
        }
    }
    public function guardarEnBodega(Request $request){
        try {

            //dd($request->all());
            $datosCompra = DB::SELECTONE("
            select
            cantidad_ingresada,
            fecha_expiracion,
            cantidad_sin_asignar,
            cantidad_disponible,
            unidades_compra,
            unidad_compra_id
            from compra_has_producto
            where compra_id = ".$request->idCompra."  and producto_id=".$request->idProducto
        );

        if($datosCompra->cantidad_sin_asignar <= 0){
            return response()->json([
                "text" => "El producto ya fue ingresado en su totalidad. No se puede realizar más registros de recepcion en bodega para este producto.",
                "icon" => "warning",
                "title"=>"Advertencia!"
            ],402);
        }

        $restaCantidad = $datosCompra->cantidad_sin_asignar - $request->cantidad;

        if($restaCantidad < 0){
            return response()->json([
                "text" => "Esta intentando ingresar una cantidad mayor de producto con respecto a la cantidad faltante de ingresar.",
                "icon" => "warning",
                "title"=>"Advertencia!"
            ], 402);

        }






        //     DB::table('compra_has_producto')
        //     ->where('compra_id','=', $request->idCompra)
        //     ->where('producto_id','=', $request->idProducto)
        //     ->update([
        //         'seccion_id' => $request->idSeccion,
        //         'estado_recibido' => 4,
        //         'recibido_por' => Auth::user()->id,
        //         'fecha_recibido' => now(),
        // ]);

        DB::beginTransaction();

        $cantidadCompraLote = $datosCompra->cantidad_ingresada * $datosCompra->unidades_compra;
        $cantidadInicial = $request->cantidad * $datosCompra->unidades_compra;

        $recibir = new ModelRecibirBodega();
        $recibir->compra_id = $request->idCompra;
        $recibir->producto_id = $request->idProducto;
        $recibir->seccion_id = $request->seccion;
        $recibir->cantidad_compra_lote = $cantidadCompraLote;
        $recibir->cantidad_inicial_seccion = $cantidadInicial;
        $recibir->cantidad_disponible = $cantidadInicial;
        $recibir->fecha_recibido = now();
        $recibir->fecha_expiracion = $datosCompra->fecha_expiracion;
        $recibir->estado_recibido = 4;
        $recibir->recibido_por = Auth::user()->id;
        $recibir->unidades_compra = $datosCompra->unidades_compra;
        $recibir->unidad_compra_id = $datosCompra->unidad_compra_id;
        $recibir->save();

        $log = new ModelLogTranslados();
        $log->compra_id = $request->idCompra;
        $log->origen = $recibir->id;
        $log->cantidad = $cantidadInicial;
        $log->users_id = Auth::user()->id;
        $log->descripcion = "Ingreso de producto por compra";
        $log->save();




            DB::table('compra_has_producto')
            ->where('compra_id','=', $request->idCompra)
            ->where('producto_id','=', $request->idProducto)
            ->update([
                'cantidad_sin_asignar' => $restaCantidad,
                'cantidad_disponible' => ($datosCompra->cantidad_disponible+$request->cantidad)

        ]);


        $this->comprobarVales();

        DB::commit();



        return response()->json([
            "text" => "Producto registrado en bodega con éxito.",
            "icon" => "success",
            "title"=>"Exito!"
        ], 200);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }

    }

    public function productoBodega($id){
       try {

            $listaBodega =     DB::SELECT("
            select

            B.id,
            B.nombre as 'producto',
            A.cantidad_compra_lote,
            departamento.nombre as 'departamento',
            municipio.nombre as 'municipio',
            bodega.direccion ,
            bodega.nombre as 'bodega',
            seccion.numeracion as 'seccion',
            A.cantidad_disponible,
            A.cantidad_inicial_seccion,
            name,
            A.created_at,
            A.id as 'idRecibido',
            A.compra_id,
            A.producto_id
        from recibido_bodega A
        inner join producto B
        on A.producto_id = B.id
        inner join seccion
        on A.seccion_id = seccion.id
        inner join segmento
        on seccion.segmento_id = segmento.id
        inner join bodega
        on segmento.bodega_id = bodega.id
        inner join municipio
        on bodega.municipio_id = municipio.id
        inner join departamento
        on municipio.departamento_id = departamento.id
        inner join users
        on A.recibido_por = users.id
        where A.compra_id =".$id
            );

        return Datatables::of($listaBodega)
        ->addColumn('opciones', function ($listaBodega) {


                return
                '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                        <li>
                            <a class="dropdown-item" onclick="mostrarModalIncidencias('.$listaBodega->idRecibido.')" > <i class="fa-solid fa-circle-exclamation text-warning"></i> Registrar Incidencia </a>
                        </li>




                    </ul>
                </div>';




        })
        ->rawColumns(['opciones'])
        ->make(true);



       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ], 402);
       }
    }

    public function datosGeneralesCompra(Request $request){
       try {

        $datosCompra = DB::SELECTONE("
        select
        cantidad_ingresada,
        fecha_expiracion,
        cantidad_sin_asignar,
        compra_id,
        producto_id,
        B.nombre

        from compra_has_producto A
        inner join producto B
        on A.producto_id = B.id
        where compra_id = ".$request->compraId."  and producto_id=".$request->productoId
     );



       return response()->json([
           'datosCompra' => $datosCompra,
       ],200);

       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }

    public function guardarEnBodegaExcedente(Request $request){
        try {

            $productoExistente  = DB::SELECTONE("
                select
                count(id) as contador
                from recibido_bodega
                where compra_id = ".$request->idCompra." and producto_id = ".$request->idProducto
            );

            if($productoExistente->contador <= 0){
                return response()->json([
                    "text" => "No se puede registrar producto excedente, sin antes haber ingresado el producto a bodega.",
                    "icon" => "warning",
                    "title"=>"Advertencia!"
                ], 200);
            }

            $datosCompra = DB::SELECTONE("
            select
            cantidad_ingresada,
            fecha_expiracion,
            cantidad_sin_asignar,
            unidad_compra_id,
            unidades_compra
            from compra_has_producto
            where compra_id = ".$request->idCompra."  and producto_id=".$request->idProducto
        );





        //     DB::table('compra_has_producto')
        //     ->where('compra_id','=', $request->idCompra)
        //     ->where('producto_id','=', $request->idProducto)
        //     ->update([
        //         'seccion_id' => $request->idSeccion,
        //         'estado_recibido' => 4,
        //         'recibido_por' => Auth::user()->id,
        //         'fecha_recibido' => now(),
        // ]);


        DB::beginTransaction();

        $recibir = new ModelRecibirBodega();
        $recibir->compra_id = $request->idCompra;
        $recibir->producto_id = $request->idProducto;
        $recibir->seccion_id = $request->seccionExcedente;
        $recibir->cantidad_compra_lote = $datosCompra->cantidad_ingresada;
        $recibir->cantidad_inicial_seccion = $request->cantidadExcedente;
        $recibir->cantidad_disponible = $request->cantidadExcedente;
        $recibir->fecha_recibido = now();
        $recibir->fecha_expiracion = $datosCompra->fecha_expiracion;
        $recibir->estado_recibido = 4;
        $recibir->recibido_por = Auth::user()->id;
        $recibir->unidad_compra_id = $datosCompra->unidad_compra_id;
        $recibir->unidades_compra = $datosCompra->unidades_compra;
        $recibir->save();

        $log = new ModelLogTranslados();
        $log->compra_id = $request->idCompra;
        $log->origen = $recibir->id;
        $log->cantidad = $request->cantidadExcedente;
        $log->users_id = Auth::user()->id;
        $log->descripcion = "Ingreso de producto por compra - Excedente";
        $log->save();

        $incidencia = new ModelIncidencia();
        $incidencia->descripcion = "Excedente de producto";
        $incidencia->recibido_bodega_id =  $recibir->id;
        $incidencia->users_id = Auth::user()->id;
        $incidencia->save();

        DB::commit();

        return response()->json([
            "text" => "Excedente de producto registrado en bodega con éxito.",
            "icon" => "success",
            "title"=>"Exito!"
        ], 200);
        } catch (QueryException $e) {

            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }

    }

    public function incidenciaBodega(Request $request){
       try {



        if($request->imagen){
            $validator = Validator::make($request->all(), [

                'imagen' => 'mimes:png,jpeg,jpg,pdf'

            ], [

                'imagen' => 'Formato de imagen invalido'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'tilte' => 'Ha ocurrido un error.',
                    'text' => $validator->errors(),
                    'icon' => 'error'
                ], 402);
            }

            $file = $request->file('imagen');
            $name = 'IMG_'. time(). '.' . $file->getClientOriginalExtension();
            $path = public_path() . '/incidencias_bodega';
            $file->move($path, $name);

            $incidencia = new ModelIncidencia;
            $incidencia->descripcion = $request->comentario;
            $incidencia->url_img = $name;
            $incidencia->recibido_bodega_id = $request->idRecibido;
            $incidencia->users_id = Auth::user()->id;
            $incidencia->save();

        }else{
            $incidencia = new ModelIncidencia;
            $incidencia->descripcion = $request->comentario;
            $incidencia->recibido_bodega_id = $request->idRecibido;
            $incidencia->users_id = Auth::user()->id;
            $incidencia->save();

        }





       return response()->json([
           "text" => "Indicencia registrada con exito.",
           "title" => "Exito!",
           "icon" => "success"
       ],200);

       } catch (QueryException $e) {
        $carpetaPublic = public_path();
        $path = $carpetaPublic.'/incidencias_bodega/'.$name;
        File::delete($path);

       return response()->json([
          'error' => $e,
          "text" => "Ha ocurrido un error.",
          "title" => "Error!",
          "icon" => "error"
       ],402);
       }

    }

    public function incidenciaCompra(Request $request){

       try {

        if($request->imagenCompra){
            $validator = Validator::make($request->all(), [

                'imagenCompra' => 'mimes:png,jpeg,jpg,pdf'

            ], [

                'imagenCompra' => 'Formato de imagen invalido'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'tilte' => 'Ha ocurrido un error.',
                    'text' => 'Formato de imagen invalido.',
                    'icon' => 'error'
                ], 402);
            }

            $file = $request->file('imagenCompra');
            $name = 'IMG_'. time(). '.' . $file->getClientOriginalExtension();
            $path = public_path() . '/incidencias_compra';
            $file->move($path, $name);

            $incidencia = new ModelIncidenciaCompra;
            $incidencia->descripcion = $request->comentarioCompra;
            $incidencia->url_img = $name;
            $incidencia->compra_id = $request->idCompra;
            $incidencia->producto_id = $request->idProducto;
            $incidencia->users_id = Auth::user()->id;
            $incidencia->save();

        }else{
            $incidencia = new ModelIncidenciaCompra;
            $incidencia->descripcion = $request->comentarioCompra;
            $incidencia->compra_id = $request->idCompra;
            $incidencia->producto_id = $request->idProducto;
            $incidencia->users_id = Auth::user()->id;
            $incidencia->save();

        }

        return response()->json([
            "text" => "Indicencia registrada con exito.",
            "title" => "Exito!",
            "icon" => "success"
        ],200);
       } catch (QueryException $e) {
        $carpetaPublic = public_path();
        $path = $carpetaPublic.'/incidencias_compra/'.$name;
        File::delete($path);

       return response()->json([
          'error' => $e,
          "text" => "Ha ocurrido un error.",
          "title" => "Error!",
          "icon" => "error"
       ],402);
       }
       }

       public function comprobarVales(){

         $flagVale = false;
         $valesArray =[];

         $valesAnuladosLista = DB::SELECT("
         select
         A.id,
         A.numero_vale
         from vale A
         inner join espera_has_producto B
         on A.id = B.vale_id
         where estado_id = 1
         order by id ASC
         ");



             for ($i=0; $i < count($valesAnuladosLista) ; $i++) {
                 $flagVale = true;

                 /*OBTIENE LOS PRODUCTOS DEL VALE */
                 $productos = DB::SELECT("select producto_id, resta_inventario_total from espera_has_producto where vale_id =".$valesAnuladosLista[$i]->id);

                 /*COMPRUEBA LA EXISTENCIA EN INVENTARIOS DE LOS PRODUCTOS CONTENIDOS EN EL VALE*/
                 for ($j=0; $j <  count($productos) ; $j++) {
                     $disponible = DB::SELECTONE("select sum(cantidad_disponible) as cantidad_disponible from recibido_bodega where cantidad_disponible <> 0 and producto_id = ".$productos[$j]->producto_id);

                     /*SI UN PRODUCTO NO CUENTA CON EXISTENCIA SUFICIENTE EN INVENTARIO EL VALE NO SE PUEDE ANULAR */
                     if($productos[$j]->resta_inventario_total > $disponible->cantidad_disponible){
                         $flagVale = false;
                     }


                 }

                 if($flagVale){
                     array_push($valesArray,$valesAnuladosLista[$i]->numero_vale);
                 }

             }


              if(empty($valesArray)){
                return;
             }


             $correosDB = DB::SELECT("select email from users where rol_id = 1 or rol_id = 5");
             $correos =[];

             foreach ($correosDB as $correo) {
                array_push( $correos,$correo->email);
             }



             $subject = "Vales disponibles para anular";
            // $for = ['williams.villalta@distribucionesvalencia.hn','cristian.zelaya@distribucionesvalencia.hn' ];

            $for =  $correos;



             Mail::send('email/vales-disponibles',['valesArray' => $valesArray], function($msj) use($subject,$for){
                 $msj->from(env('MAIL_FROM_ADRESS'),"Soporte Técnico Distribuciones Valencia ");
                 $msj->subject($subject);
                 $msj->to($for);
             });

             return;

     }


}
