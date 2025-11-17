<?php

namespace App\Http\Livewire\Cotizaciones;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelCotizacion;
use App\Models\ModelCotizacionProducto;

class expo extends Component

{

    public $tipoCotizacion;

    public function mount($id)
    {

        $this->tipoCotizacion = $id;
    }

    public function render()
    {
        $tipoCotizacion = $this->tipoCotizacion;
        return view('livewire.cotizaciones.expo', compact('tipoCotizacion'));
    }



    public function infoProducto($id){
                   $producto = DB::selectOne("
                SELECT
                p.id,
                CONCAT(p.id,' - ',p.nombre) AS nombre,
                p.isv,
                p.ultimo_costo_compra AS ultimo_costo_compra,
                ppc.precio_base_venta AS precio_base,
                ppc.precio_a AS precio1,
                ppc.precio_b AS precio2,
                ppc.precio_c AS precio3,
                ppc.precio_d AS precio4
                FROM producto p
                JOIN cliente cli
                ON cli.id = :idCliente
                JOIN cliente_categoria_escala cce
                ON cce.id = cli.cliente_categoria_escala_id
                AND cce.estado_id = 1
                JOIN categoria_precios cp
                ON cp.cliente_categoria_escala_id = cce.id
                AND cp.estado_id = 1
                JOIN precios_producto_carga ppc
                ON ppc.producto_id = p.id
                AND ppc.categoria_precios_id = cp.id
                AND ppc.estado_id = 1
                WHERE p.id = :idProducto
                LIMIT 1
            ", [
                'idCliente'  => $request['idCliente'],
                'idProducto' => $request['idProducto'],
            ]);
        return $producto;
    }

    public function obtenerDatosProductoExpo(Request $request)
    {
        try {
            $codigoBarra = $request->input('barraProd');

            Log::info('Buscando producto con código de barras: ' . $codigoBarra);

            if (empty($codigoBarra)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de barras no proporcionado'
                ], 400);
            }

            // Buscar producto por código de barras (misma lógica que obtenerDatosProducto) foo no me daba
            $producto = DB::selectOne("
                SELECT
                    id,
                    CONCAT(id,' - ',nombre) as nombre,
                    isv,
                    ultimo_costo_compra as ultimo_costo_compra,
                    precio_base as precio_base,
                    precio1 as precio1,
                    precio2 as precio2,
                    precio3 as precio3,
                    precio4 as precio4,
                    codigo_barra,
                    estado_producto_id
                FROM producto
                WHERE codigo_barra = ? AND estado_producto_id = 1
            ", [$codigoBarra]);

            if (!$producto) {
                Log::info('Producto no encontrado con código: ' . $codigoBarra);
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                    'codigo' => $codigoBarra
                ], 404);
            }

            // Obtener unidades del producto (misma lógica que obtenerDatosProducto)
            $unidades = DB::select("
                SELECT
                    A.unidad_venta as id,
                    CONCAT(B.nombre,'-',A.unidad_venta) as nombre,
                    A.unidad_venta_defecto as 'valor_defecto',
                    A.id as idUnidadVenta
                FROM unidad_medida_venta A
                INNER JOIN unidad_medida B ON A.unidad_medida_id = B.id
                WHERE A.estado_id = 1 AND A.producto_id = ?
            ", [$producto->id]);

            Log::info('Producto encontrado: ' . $producto->nombre);

            return response()->json([
                'success' => true,
                'producto' => $producto,
                'unidades' => $unidades
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en obtenerDatosProductoExpo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function listarClientes(Request $request)
    {
        try {


           $tipoCotizacion = $request->tipoCotizacion;

            if($tipoCotizacion==3){
                $listaClientes = $this->clientesExpo($request);
            }

            return response()->json([
                "results" => $listaClientes,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

    public function clientesExpo(Request $request)
    {

            $listaClientes = DB::SELECT("
                    select
                        id,
                        nombre as text
                    from cliente
                        where estado_cliente_id = 1
                        and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                            ");


        return $listaClientes;
    }


    public function guardarCotizacion(Request $request){
       try {
        Log::info('=== INICIO GUARDADO COTIZACIÓN ===');
        Log::info('Datos recibidos:', $request->all());
        Log::info('Número de inputs: ' . $request->numeroInputs);
        Log::info('Arreglo de IDs: ' . $request->arregloIdInputs);

        $validator = Validator::make($request->all(), [
            'subTotalGeneralGrabado' => 'required',
            'subTotalGeneralGrabadoMostrar' => 'required',
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',
            'numeroInputs' => 'required',
            'seleccionarCliente' => 'required',
            'nombre_cliente_ventas' => 'required',
            // seleccionarProducto ya no es requerido para productos escaneados
        ]);

        if ($validator->fails()) {
            Log::error('Error de validación:', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'icon' => 'error',
                'title' => 'error',
                'text' => 'Por favor, verificar que todos los campos esten completados.',
                'mensaje' => 'Ha ocurrido un error al crear la compra.',
                'errors' => $validator->errors()
            ], 401);
        }

        $arrayTemporal = $request->arregloIdInputs;
        $arrayInputs = explode(',', $arrayTemporal);
        $arrayProductos = [];

        Log::info('Array de inputs procesado:', ['arrayInputs' => $arrayInputs]);

        DB::beginTransaction();



        if($request->pedido_id == null)
        {
            $cotizacion = new ModelCotizacion();
            $cotizacion->nombre_cliente = $request->nombre_cliente_ventas;
            $cotizacion->RTN = $request->rtn_ventas;
            $cotizacion->fecha_emision = $request->fecha_emision;
            $cotizacion->fecha_vencimiento = $request->fecha_emision;
            $cotizacion->sub_total = $request->subTotalGeneral;
            $cotizacion->sub_total_grabado=$request->subTotalGeneralGrabado;
            $cotizacion->sub_total_excento=$request->subTotalGeneralExcento;
            $cotizacion->isv= $request->isvGeneral;
            $cotizacion->total = $request->totalGeneral;
            $cotizacion->cliente_id = $request->seleccionarCliente;
            $cotizacion->tipo_venta_id = 4;
            $cotizacion->vendedor = $request->vendedor;
            $cotizacion->nota = $request->nota;
            $cotizacion->users_id = Auth::user()->id;
            $cotizacion->arregloIdInputs = json_encode($request->arregloIdInputs);
            $cotizacion->numeroInputs = $request->numeroInputs;
            $cotizacion->porc_descuento = $request->porDescuento;
            $cotizacion->monto_descuento = $request->descuentoGeneral;
            $cotizacion->save();

           /*  ALTER TABLE cotizacion ADD COLUMN nota VARCHAR(255) NULL DEFAULT NULL; */

            for ($i = 0; $i < count($arrayInputs); $i++) {

                Log::info("=== PROCESANDO PRODUCTO $i ===");
                Log::info("ID del input: " . $arrayInputs[$i]);

                $keyRestaInventario = "restaInventario" . $arrayInputs[$i];
                $keyIdSeccion = "idSeccion" . $arrayInputs[$i];
                $keyIdProducto = "idProducto" . $arrayInputs[$i];
                $keyIdUnidadVenta = "idUnidadVenta" . $arrayInputs[$i];
                $keyPrecio = "precio" . $arrayInputs[$i];
                $keyCantidad = "cantidad" . $arrayInputs[$i];
                $keySubTotal = "subTotal" . $arrayInputs[$i];
                $keyIsvPagar = "isvProducto" . $arrayInputs[$i];
                $keyTotal = "total" . $arrayInputs[$i];
                $keyIsvAsigando = "isv" . $arrayInputs[$i];
                $keyunidad = 'unidad' . $arrayInputs[$i];
                $keyidBodega = 'idBodega'.$arrayInputs[$i];
                $keyidPrecioSeleccionado = 'idPrecioSeleccionado'.$arrayInputs[$i];
                $keyprecioSeleccionado = 'precios'.$arrayInputs[$i];
                $keyNombreProducto = 'nombre'.$arrayInputs[$i];
                $keyBodegaNombre = 'bodega'.$arrayInputs[$i];
                $keymonto_descProducto = 'acumuladoDescuento'.$arrayInputs[$i];

                Log::info("Claves generadas:", [
                    'keyIdProducto' => $keyIdProducto,
                    'keyIdUnidadVenta' => $keyIdUnidadVenta,
                    'keyPrecio' => $keyPrecio,
                    'keyCantidad' => $keyCantidad,
                    'keyidPrecioSeleccionado' => $keyidPrecioSeleccionado,
                    'keyprecioSeleccionado' => $keyprecioSeleccionado
                ]);

                // Verificar si existen los campos en el request
                $camposExistentes = [
                    $keyRestaInventario => $request->has($keyRestaInventario),
                    $keyIdSeccion => $request->has($keyIdSeccion),
                    $keyIdProducto => $request->has($keyIdProducto),
                    $keyIdUnidadVenta => $request->has($keyIdUnidadVenta),
                    $keyPrecio => $request->has($keyPrecio),
                    $keyCantidad => $request->has($keyCantidad),
                    $keySubTotal => $request->has($keySubTotal),
                    $keyIsvPagar => $request->has($keyIsvPagar),
                    $keyTotal => $request->has($keyTotal),
                    $keyIsvAsigando => $request->has($keyIsvAsigando),
                    $keyunidad => $request->has($keyunidad),
                    $keyidBodega => $request->has($keyidBodega),
                    $keyidPrecioSeleccionado => $request->has($keyidPrecioSeleccionado),
                    $keyprecioSeleccionado => $request->has($keyprecioSeleccionado),
                    $keyNombreProducto => $request->has($keyNombreProducto),
                    $keyBodegaNombre => $request->has($keyBodegaNombre),
                    $keymonto_descProducto => $request->has($keymonto_descProducto)
                ];

                Log::info("Campos existentes en request:", ['campos' => $camposExistentes]);

                // Mostrar campos faltantes
                $camposFaltantes = array_filter($camposExistentes, function($existe) {
                    return !$existe;
                });

                if (!empty($camposFaltantes)) {
                    Log::warning("CAMPOS FALTANTES:", array_keys($camposFaltantes));
                }

                $restaInventario = $request->$keyRestaInventario;
                $idSeccion = $request->$keyIdSeccion;
                $idProducto = $request->$keyIdProducto;
                $idUnidadVenta = $request->$keyIdUnidadVenta;
                $isvProductoPagar = $request->$keyIsvPagar;
                $idPrecioSeleccionado = $request->$keyidPrecioSeleccionado;
                $precioSeleccionado = $request->$keyprecioSeleccionado;
                //$unidad = $request->$keyunidad;
                $precio = $request->$keyPrecio;
                $cantidad = $request->$keyCantidad;
                $subTotal = $request->$keySubTotal;

                $total = $request->$keyTotal;
                $idBodega = $request->$keyidBodega;
                $ivsProductoAsignado = $request->$keyIsvAsigando;
                $nombreProducto = $request->$keyNombreProducto;
                $nombreBodega = $request->$keyBodegaNombre;
                $monto_descProducto = $request->$keymonto_descProducto;

                Log::info("Valores obtenidos:", [
                    'idProducto' => $idProducto,
                    'idUnidadVenta' => $idUnidadVenta,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'idPrecioSeleccionado' => $idPrecioSeleccionado,
                    'precioSeleccionado' => $precioSeleccionado,
                    'nombreProducto' => $nombreProducto,
                    'subTotal' => $subTotal,
                    'total' => $total
                ]);


                array_push($arrayProductos,[
                'cotizacion_id'=> $cotizacion->id,
                'producto_id'=> $idProducto,
                'indice'=>$arrayInputs[$i],
                'nombre_producto'=>$nombreProducto,
                'nombre_bodega'=> $nombreBodega,
                'precio_unidad'=>$precio,
                'cantidad'=>$cantidad,
                'sub_total'=>$subTotal,
                'isv'=> $isvProductoPagar,
                'total'=> $total,
                'Bodega_id'=>$idBodega,
                'seccion_id'=>$idSeccion,
                'resta_inventario'=>$restaInventario,
                'isv_producto'=>$ivsProductoAsignado,
                'unidad_medida_venta_id'=>$idUnidadVenta,
                'monto_descProducto'=>$monto_descProducto,
                'idPrecioSeleccionado'=>$idPrecioSeleccionado,
                'precioSeleccionado'=>$precioSeleccionado,
                'created_at'=>now(),
                'updated_at'=>now()

                ]);

            };

            ModelCotizacionProducto::insert($arrayProductos);

        }else{
             $cotizacion = ModelCotizacion::find($request->pedido_id);
             $cotizacion->nombre_cliente = $request->nombre_cliente_ventas;
             $cotizacion->RTN = $request->rtn_ventas;
             $cotizacion->fecha_emision = $request->fecha_emision;
             $cotizacion->fecha_vencimiento = $request->fecha_emision;
             $cotizacion->sub_total = $request->subTotalGeneral;
             $cotizacion->sub_total_grabado=$request->subTotalGeneralGrabado;
             $cotizacion->sub_total_excento=$request->subTotalGeneralExcento;
             $cotizacion->isv= $request->isvGeneral;
             $cotizacion->total = $request->totalGeneral;
             $cotizacion->cliente_id = $request->seleccionarCliente;
             $cotizacion->tipo_venta_id = $request->tipo_venta_id;
             $cotizacion->vendedor = $request->vendedor;
             $cotizacion->users_id = Auth::user()->id;
             $cotizacion->arregloIdInputs = json_encode($request->arregloIdInputs);
             $cotizacion->numeroInputs = $request->numeroInputs;
             $cotizacion->porc_descuento = $request->porDescuento;
             $cotizacion->monto_descuento =  $request->descuentoGeneral;
             $cotizacion->save();


             for ($i = 0; $i < count($arrayInputs); $i++) {

                 $keyRestaInventario = "restaInventario" . $arrayInputs[$i];
                 $keyIdSeccion = "idSeccion" . $arrayInputs[$i];
                 $keyIdProducto = "idProducto" . $arrayInputs[$i];
                 $keyIdUnidadVenta = "idUnidadVenta" . $arrayInputs[$i];
                 $keyPrecio = "precio" . $arrayInputs[$i];
                 $keyCantidad = "cantidad" . $arrayInputs[$i];
                 $keySubTotal = "subTotal" . $arrayInputs[$i];
                 $keyIsvPagar = "isvProducto" . $arrayInputs[$i];
                 $keyTotal = "total" . $arrayInputs[$i];
                 $keyIsvAsigando = "isv" . $arrayInputs[$i];
                 $keyunidad = 'unidad' . $arrayInputs[$i];
                 $keyidBodega = 'idBodega'.$arrayInputs[$i];

                 $keyNombreProducto = 'nombre'.$arrayInputs[$i];
                 $keyBodegaNombre = 'bodega'.$arrayInputs[$i];
                 $keymonto_descProducto = 'acumuladoDescuento'.$arrayInputs[$i];



                 $restaInventario = $request->$keyRestaInventario;
                 $idSeccion = $request->$keyIdSeccion;
                 $idProducto = $request->$keyIdProducto;
                 $idUnidadVenta = $request->$keyIdUnidadVenta;
                 $isvProductoPagar = $request->$keyIsvPagar;
                 //$unidad = $request->$keyunidad;
                 $precio = $request->$keyPrecio;
                 $cantidad = $request->$keyCantidad;
                 $subTotal = $request->$keySubTotal;

                 $total = $request->$keyTotal;
                 $idBodega = $request->$keyidBodega;
                 $ivsProductoAsignado = $request->$keyIsvAsigando;
                 $nombreProducto = $request->$keyNombreProducto;
                 $nombreBodega = $request->$keyBodegaNombre;
                 $monto_descProducto = $request->$keymonto_descProducto;


                 array_push($arrayProductos,[
                 'cotizacion_id'=> $request->pedido_id,
                 'producto_id'=> $idProducto,
                 'indice'=>$arrayInputs[$i],
                 'nombre_producto'=>$nombreProducto,
                 'nombre_bodega'=> $nombreBodega,
                 'precio_unidad'=>$precio,
                 'cantidad'=>$cantidad,
                 'sub_total'=>$subTotal,
                 'isv'=> $isvProductoPagar,
                 'total'=> $total,
                 'Bodega_id'=>$idBodega,
                 'seccion_id'=>$idSeccion,
                 'resta_inventario'=>$restaInventario,
                 'isv_producto'=>$ivsProductoAsignado,
                 'unidad_medida_venta_id'=>$idUnidadVenta,
                 'monto_descProducto'=>$monto_descProducto,
                 'created_at'=>now(),
                 'updated_at'=>now()

                 ]);

             };

             //dd('hasta aqui llega');
             //dd($arrayProductos);
                DB::table('cotizacion_has_producto')->where('cotizacion_id', $request->pedido_id)->delete();
                ModelCotizacionProducto::insert($arrayProductos);
        }




        DB::commit();

        Log::info('=== COTIZACIÓN GUARDADA EXITOSAMENTE ===');
        Log::info('ID de cotización creada: ' . $cotizacion->id);

        return response()->json([
            'icon'=>'success',
            'text'=>'Cotización guardada con éxito.',
            'title'=>'Exito!',
            'pedido_id'=> $cotizacion->id
        ],200);

        } catch (\Exception $e) {

        DB::rollback();

        Log::error('=== ERROR AL GUARDAR COTIZACIÓN ===');
        Log::error('Mensaje: ' . $e->getMessage());
        Log::error('Archivo: ' . $e->getFile());
        Log::error('Línea: ' . $e->getLine());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'icon'=>'error',
            'text'=>'Ha ocurrido un error al guardar la cotización: ' . $e->getMessage(),
            'title'=>'Error!',
            'message' => $e->getMessage(),
            'error' => $e->getMessage()
        ],402);

        } catch (QueryException $e) {

        DB::rollback();

        Log::error('=== ERROR DE BASE DE DATOS ===');
        Log::error('Mensaje: ' . $e->getMessage());
        Log::error('SQL: ' . $e->getSql());
        Log::error('Bindings: ', ['bindings' => $e->getBindings()]);

        return response()->json([
            'icon'=>'error',
            'text'=>'Ha ocurrido un error de base de datos al guardar la cotización.',
            'title'=>'Error!',
            'message' => $e->getMessage(),
            'error' => $e->getMessage()
        ],402);
        }
    }

    public function imprimirCotizacion($idFactura)
    {

        $datos = DB::SELECTONE("
            select
            concat(YEAR(NOW()),'-',A.id) as codigo,
            B.nombre,
            B.direccion,
            B.correo,
            B.telefono_empresa,
            A.fecha_emision,
            time(A.created_at) as hora,
            A.fecha_vencimiento,
            B.rtn,
            users.name,
            (select name from users where id = A.vendedor) as vendedor,
            A.nota
            from cotizacion A
            inner join cliente B
            on A.cliente_id = B.id
            inner join users
            ON users.id = A.users_id
            where A.id =".$idFactura
        );

        $productos = DB::SELECT("
            select
            C.id as codigo,
            C.nombre,
            C.descripcion,
            if(C.isv = 0, 'SI' , 'NO' ) as excento,
            FORMAT(B.precio_unidad,2) as precio,
            FORMAT(B.cantidad,2) as cantidad,
            FORMAT(B.sub_total,2) as importe,
            J.nombre as medida

            from cotizacion A
            inner join cotizacion_has_producto B
            on A.id=B.cotizacion_id
            inner join producto C
            on B.producto_id = C.id
            inner join unidad_medida_venta D
            on B.unidad_medida_venta_id = D.id
            inner join unidad_medida J
            on J.id = D.unidad_medida_id
            where A.id = ".$idFactura."
            order by B.indice asc
            "
        );

        $importes = DB::SELECTONE("
            select
            porc_descuento,
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from cotizacion
            where id = ".$idFactura
        );


        $importesConCentavos= DB::SELECTONE("
            select
            FORMAT(monto_descuento,2) as monto_descuento,
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from cotizacion where id = ".$idFactura
        );

        $tipoCot = 4;
        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/cotizacion',compact('datos','productos','importes','importesConCentavos','flagCentavos','numeroLetras', 'tipoCot'))->setPaper('letter');

        return $pdf->stream("Pedido_NO_".$datos->codigo.".pdf");


    }

    public function imprimirCatalogo($idCotizacion)
    {
        $datos = DB::SELECT(
            "
                select
                      C.id as codigoProducto,
                    C.nombre as nombre1,
                    C.descripcion as nombre,
                    if(C.isv = 0, 'SI' , 'NO' ) as excento,
                    FORMAT(B.precio_unidad,2) as precio,
                    B.cantidad as cantidad,
                    FORMAT(B.sub_total,2) as importe,
                    J.nombre as medida,
                    C.codigo_barra,
                    E.descripcion as 'subcategoria',
                    F.descripcion as 'categoria',
                    G.nombre as 'marca',
                    imagen.url_img as 'imagen',
                    A.nombre_cliente,
                    A.fecha_emision,
                    A.RTN,
                    A.id,
                    CONCAT(YEAR(A.fecha_emision),'-',A.id) as 'cotizacion'

                from cotizacion A
                    inner join cotizacion_has_producto B on A.id=B.cotizacion_id
                    inner join producto C on B.producto_id = C.id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join sub_categoria E on E.id = C.sub_categoria_id
                    inner join categoria_producto F on F.id = E.categoria_producto_id
                    inner join marca G on G.id = C.marca_id
                    inner join img_producto imagen on imagen.producto_id = C.id
                where A.id = ".$idCotizacion."
                order by B.indice asc
            "
        );
        $pdf = PDF::loadView('/pdf/catalogo',compact('datos'))->setPaper("A4", "portrait");

        return $pdf->stream("catalogo.pdf");

    }

    public function imprimirProforma($idFactura)
    {

        $datos = DB::SELECTONE("
            select
            concat(YEAR(NOW()),'-',A.id) as codigo,
            B.nombre,
            B.direccion,
            B.correo,
            B.telefono_empresa,
            A.fecha_emision,
            time(A.created_at) as hora,
            A.fecha_vencimiento,
            B.rtn,
            users.name,
            (select name from users where id = A.vendedor) as vendedor,
            A.nota
            from cotizacion A
            inner join cliente B
            on A.cliente_id = B.id
            inner join users
            ON users.id = A.users_id
            where A.id =".$idFactura
        );

            $productos = DB::SELECT("
                select
                C.id as codigo,
                C.nombre,
                C.descripcion,
                H.nombre as bodega,
                F.descripcion as seccion,
                if(C.isv = 0, 'SI' , 'NO' ) as excento,
                FORMAT(B.precio_unidad,2) as precio,
                FORMAT(B.cantidad,2) as cantidad,
                FORMAT(B.sub_total,2) as importe,
                J.nombre as medida

                from cotizacion A
                    inner join cotizacion_has_producto B on A.id=B.cotizacion_id
                    inner join producto C on B.producto_id = C.id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join seccion F on B.seccion_id = F.id
                    inner join segmento G on F.segmento_id = G.id
                    inner join bodega H on G.bodega_id = H.id
                where A.id = ".$idFactura."
                order by B.indice asc
                "
            );


            $importes = DB::SELECTONE("
            select
            porc_descuento,
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from cotizacion
            where id = ".$idFactura
        );


        $importesConCentavos= DB::SELECTONE("
            select
            FORMAT(monto_descuento,2) as monto_descuento,
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from cotizacion where id = ".$idFactura
        );


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }
         $tipoCot = 4;
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/proforma',compact('datos','productos','importes','importesConCentavos','flagCentavos','numeroLetras', 'tipoCot'))->setPaper('letter');

        return $pdf->stream("proforma_NO_".$datos->codigo.".pdf");


    }

    public function listarBodegas(Request $request)
    {
        try {

            $results = DB::SELECT("
        select
            A.seccion_id as id,
            D.id as 'idBodega',
            CONCAT(D.nombre,'',REPLACE(B.descripcion,'Seccion','')) as 'bodegaSeccion',
            concat(D.nombre,' - ', REPLACE(B.descripcion,'Seccion',''),' - cantidad ',sum(A.cantidad_disponible)) as 'text'
        from recibido_bodega A
            inner join seccion B
            on A.seccion_id = B.id
            inner join segmento C
            on B.segmento_id = C.id
            inner join bodega D
            on C.bodega_id = D.id
        where  producto_id = " . $request->idProducto . "
        and (D.nombre LIKE '%" . $request->search . "%' or B.descripcion LIKE '%" . $request->search . "%')
        group by A.seccion_id
            ");

            return response()->json([
                "results" => $results
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }
    public function productoBodega(Request $request)
    {
        try {


            $listaProductos = DB::SELECT("
         select
            B.id,
            concat('cod ',B.id,' - ',B.nombre,' - ',B.codigo_barra,' - ','cantidad ',sum(A.cantidad_disponible)) as text
         from
            recibido_bodega A
            inner join producto B
            on A.producto_id = B.id
            inner join seccion
            on A.seccion_id = seccion.id
            inner join segmento
            on seccion.segmento_id = segmento.id
            inner join bodega
            on segmento.bodega_id = bodega.id
         where

         (B.nombre LIKE '%" . $request->search . "%' or B.id LIKE '%" . $request->search . "%' or B.codigo_barra Like '%" . $request->search . "%')

            and bodega.id = 16
         group by A.producto_id
         limit 15
         ");

            return response()->json([
                "results" => $listaProductos
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ]);
        }
    }
}

