<?php



namespace App\Http\Livewire\VentasEstatal;

use Livewire\Component;


use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\ModelNumOrdenCompra;
use App\Http\Controllers\CAI\Notificaciones;
use Exception;

class FacturacionEstatal extends Component
{
    public function render()
    {
        return view('livewire.ventas-estatal.facturacion-estatal');
    }

    public $arrayProductos = [];
    public $arrayLogs = [];



    public function listarClientes(Request $request)
    {
        try {

            if (Auth::user()->rol_id == 1 or Auth::user()->rol_id == 3) {
                $listaClientes = DB::SELECT("
                select
                    id,
                    nombre as text
                from cliente
                    where estado_cliente_id = 1
                    and tipo_cliente_id=2
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                        ");
            }else{
                $listaClientes = DB::SELECT("
                select
                    id,
                    nombre as text
                from cliente
                    where estado_cliente_id = 1
                    and tipo_cliente_id=2
                    and vendedor =" . Auth::user()->id . "
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                        ");
            }


            //  $listaClientes = DB::SELECT("
            //  select
            //      id,
            //      nombre as text
            //  from cliente
            //      where estado_cliente_id = 1
            //      and tipo_cliente_id=2
            //      and vendedor =".Auth::user()->id."
            //      and  (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15
            //          ");


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

    public function datosCliente(Request $request)
    {
        try {

            $datos = DB::SELECTONE("select id,nombre, rtn,dias_credito  from cliente where id = " . $request->id);

            return response()->json([
                "datos" => $datos
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }


    public function tipoPagoVenta()
    {
        try {

            $tipos = DB::SELECT("select id, descripcion from tipo_pago_venta");
            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

            return response()->json([
                "tipos" => $tipos,
                "numeroVenta" => $numeroVenta
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
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
        where  A.cantidad_disponible <> 0 and producto_id = " . $request->idProducto . "
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
         A.cantidad_disponible <> 0 and
         (B.nombre LIKE '%" . $request->search . "%' or B.id LIKE '%" . $request->search . "%' or B.codigo_barra Like '%".$request->search."%')
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


    public function obtenerImagenes(Request $request)
    {
        try {
            $imagenes = DB::SELECT("

        select
            @i := @i + 1 as contador,
            id,
            url_img
        from
            img_producto
            cross join (select @i := 0) r
            where producto_id = " . $request['id'] . "

        ");

            return response()->json([
                "imagenes" => $imagenes,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las imagenes.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function obtenerDatosProducto(Request $request)
    {

        try {


            $unidades = DB::SELECT(
                "
            select
                A.unidad_venta as id,
                CONCAT(B.nombre,'-',A.unidad_venta) as nombre ,
                A.unidad_venta_defecto as 'valor_defecto',
                A.id as idUnidadVenta
            from unidad_medida_venta A
            inner join unidad_medida B
            on A.unidad_medida_id = B.id
            where A.estado_id = 1 and A.producto_id = " . $request->idProducto
            );

            $producto = DB::SELECTONE("
            select
            id,
            concat(id,' - ',nombre) as nombre,
            isv,
            FORMAT(ultimo_costo_compra,2) as ultimo_costo_compra,
            FORMAT(precio_base,2) as precio_base
            from producto where id = " . $request['idProducto'] . "
            ");


            return response()->json([
                "producto" => $producto,

                "unidades" => $unidades
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al obtener los datos del producto.',
                'error' => $e,
            ], 402);
        }
    }

    public function guardarVenta(Request $request)
    {


        $validator = Validator::make($request->all(), [

            'fecha_vencimiento' => 'required',
            'subTotalGeneralGrabado' => 'required',
            'subTotalGeneralGrabadoMostrar' => 'required',
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',
            'numeroInputs' => 'required',
            'seleccionarCliente' => 'required',
            'nombre_cliente_ventas' => 'required',
            'tipoPagoVenta' => 'required',
            'restriccion' => 'required',
            'vendedor'=>'required'



        ]);



        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error al intentar crear la venta.',
                'errors' => $validator->errors()
            ], 406);
        }

        if ($request->restriccion == 1) {
            $facturaVencida = $this->comprobarFacturaVencida($request->seleccionarCliente);

            if ($facturaVencida) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Advertencia!',
                    'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', cuenta con facturas vencidas. Por el momento no se puede emitir factura a este cliente.',

                ], 401);
            }
        }

        if ($request->tipoPagoVenta == 2) {
            $comprobarCredito = $this->comprobarCreditoCliente($request->seleccionarCliente, $request->totalGeneral);

            if ($comprobarCredito) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Advertencia!',
                    'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', no cuenta con crédito suficiente . Por el momento no se puede emitir factura a este cliente.',

                ], 401);
            }
        }

        //dd($request->all());
        $arrayTemporal = $request->arregloIdInputs;
        $arrayInputs = explode(',', $arrayTemporal);
        $arrayProductosVentas = [];



        $mensaje = "";
        $flag = false;

        //comprobar existencia de producto en bodega
        for ($j = 0; $j < count($arrayInputs); $j++) {

            $keyIdSeccion = "idSeccion" . $arrayInputs[$j];
            $keyIdProducto = "idProducto" . $arrayInputs[$j];
            $keyRestaInventario = "restaInventario" . $arrayInputs[$j];
            $keyNombre = "nombre" . $arrayInputs[$j];
            $keyBodega = "bodega" . $arrayInputs[$j];

            $resultado = DB::selectONE("select
            if(sum(cantidad_disponible) is null,0,sum(cantidad_disponible)) as cantidad_disponoble
            from recibido_bodega
            where cantidad_disponible <> 0
            and producto_id = " . $request->$keyIdProducto . "
            and seccion_id = " . $request->$keyIdSeccion);

            if ($request->$keyRestaInventario > $resultado->cantidad_disponoble) {
                $mensaje = $mensaje . "Unidades insuficientes para el producto: <b>" . $request->$keyNombre . "</b> en la bodega con sección :<b>" . $request->$keyBodega . "</b><br><br>";
                $flag = true;
            }
        }

        if ($flag) {
            return response()->json([
                'icon' => "warning",
                'text' =>  '<p class="text-left">' . $mensaje . '</p>',
                'title' => 'Advertencia!',
                'idFactura' => 0,

            ], 200);
        }



        try {


            DB::beginTransaction();

            $cai = DB::SELECTONE("select
                    id,
                    numero_inicial,
                    numero_final,
                    cantidad_otorgada,
                    numero_actual
                    from cai
                    where tipo_documento_fiscal_id = 1 and estado_id = 1");

            $arrayNumeroFinal = explode('-', $cai->numero_final);
            $numero_final= (string)((int)($arrayNumeroFinal[3]));

            if ($cai->numero_actual > $numero_final) {

                return response()->json([
                    "title" => "Advertencia",
                    "icon" => "warning",
                    "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado.",
                ], 401);
            }






            $numeroSecuencia = $cai->numero_actual;
            $arrayCai = explode('-', $cai->numero_final);
            $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
            $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;
            // dd($cai->cantidad_otorgada);





            $montoComision = $request->totalGeneral * 0.5;

            if ($request->tipoPagoVenta == 1) {
                $diasCredito = 0;
            } else {
                $dias = DB::SELECTONE("select dias_credito from cliente where id = " . $request->seleccionarCliente);
                $diasCredito = $dias->dias_credito;
            }

            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

            $validarCAI = new Notificaciones();
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3],"0"),$numeroSecuencia, 2);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn = $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado=$request->subTotalGeneralGrabado;
            $factura->sub_total_excento=$request->subTotalGeneralExcento;
            $factura->isv = $request->isvGeneral;
            $factura->total = $request->totalGeneral;
            $factura->credito = $request->totalGeneral;
            $factura->fecha_emision = $request->fecha_emision;
            $factura->fecha_vencimiento = $request->fecha_vencimiento;
            $factura->tipo_pago_id = $request->tipoPagoVenta;
            $factura->dias_credito = $diasCredito;
            $factura->cai_id = $cai->id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 2; // estatal
            $factura->estado_factura_id = 1; // se presenta
            $factura->users_id = Auth::user()->id;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->numero_orden_compra_id=$request->ordenCompra;
            $factura->comentario=$request->nota_comen;
            $factura->porc_descuento =$request->porDescuento;
            $factura->monto_descuento=$request->porDescuentoCalculado;
            $factura->save();




            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->numero_actual = $numeroSecuencia + 1;
            $caiUpdated->cantidad_no_utilizada = $cai->cantidad_otorgada - $numeroSecuencia;
            $caiUpdated->save();


            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */
            //Tabla de listado
            // DB::INSERT("INSERT INTO listado(
            //          numero, secuencia, numero_inicial, numero_final, cantidad_otorgada, cai_id, created_at, updated_at, eliminado) VALUES
            //         ('" . $numeroCAI . "','" . $numeroSecuencia . "','" . $cai->numero_inicial . "','" . $cai->numero_final . "','" . $cai->cantidad_otorgada . "','" . $cai->id . "','" . NOW() . "','" . NOW() . "',0)");




            if(!empty($request->ordenCompra))
            {
                $ordeCompra = ModelNumOrdenCompra::find($request->ordenCompra);
                $ordeCompra->estado_id =2;
                $ordeCompra->save();
            }



            // //dd( $guardarCompra);





            for ($i = 0; $i < count($arrayInputs); $i++) {

                $keyRestaInventario = "restaInventario" . $arrayInputs[$i];

                $keyIdSeccion = "idSeccion" . $arrayInputs[$i];
                $keyIdProducto = "idProducto" . $arrayInputs[$i];
                $keyIdUnidadVenta = "idUnidadVenta" . $arrayInputs[$i];
                $keyPrecio = "precio" . $arrayInputs[$i];
                $keyCantidad = "cantidad" . $arrayInputs[$i];
                $keySubTotal = "subTotal" . $arrayInputs[$i];
                $keyIsv = "isvProducto" . $arrayInputs[$i];
                $keyTotal = "total" . $arrayInputs[$i];
                $keyISV = "isv" . $arrayInputs[$i];
                $keyunidad = 'unidad' . $arrayInputs[$i];

                $restaInventario = $request->$keyRestaInventario;
                $idSeccion = $request->$keyIdSeccion;
                $idProducto = $request->$keyIdProducto;
                $idUnidadVenta = $request->$keyIdUnidadVenta;
                $ivsProducto = $request->$keyISV;
                $unidad = $request->$keyunidad;

                $precio = $request->$keyPrecio;
                $cantidad = $request->$keyCantidad;
                $subTotal = $request->$keySubTotal;
                $isv = $request->$keyIsv;
                $total = $request->$keyTotal;

                $this->restarUnidadesInventario($restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $arrayInputs[$i]);
            };

            if ($request->tipoPagoVenta == 2) { //si el tipo de pago es credito
                $this->restarCreditoCliente($request->seleccionarCliente, $request->totalGeneral, $factura->id);
            }

            // dd($this->arrayProductos);
            ModelVentaProducto::insert($this->arrayProductos);
            ModelLogTranslados::insert($this->arrayLogs);


            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");
            DB::commit();

            return response()->json([
                'icon' => "success",
                'text' =>  '
                <div class="d-flex justify-content-between">
                    <a href="/factura/cooporativo/' . $factura->id . '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-invoice"></i> Imprimir Factura</a>
                    <a href="/crear/vale/lista/espera/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-list-check"></i> Crear Vale Tipo: 2</a>
                   <!-- <a href="/venta/cobro/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Realizar Pago</a> -->
                    <a href="/detalle/venta/' . $factura->id . '" target="_blank" class="btn btn-sm btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Detalle de Factura</a>
                </div>',
                'title' => 'Exito!',
                'idFactura' => $factura->id,
                'numeroVenta' => $numeroVenta->numero

            ], 200);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Ha ocurrido un error al realizar la factura.',
                'icon' => "error",
                'text' => 'Ha ocurrido un error.',
                'title' => 'Error!',
                'idFactura' => "",
                'mensajeError'=>$e
            ], 402);

        }
    }

    public function restarUnidadesInventario($unidadesRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $indice)
    {
        try {

            $precioUnidad = $subTotal / $unidadesRestarInv;

            $unidadesRestar = $unidadesRestarInv;  //es la cantidad ingresada por el usuario multiplicado por unidades de venta del producto
            $registroResta = 0;
            while (!($unidadesRestar <= 0)) {

                $unidadesDisponibles = DB::SELECTONE("
                        select
                            id,
                            cantidad_disponible
                        from recibido_bodega
                            where seccion_id = " . $idSeccion . " and
                            producto_id = " . $idProducto . " and
                            cantidad_disponible <>0
                            order by created_at asc
                        limit 1
                        ");


                if ($unidadesDisponibles->cantidad_disponible == $unidadesRestar) {

                    $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;
                    $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                    $lote->cantidad_disponible = $diferencia;
                    $lote->save();

                    $registroResta = $unidadesRestar;
                    $unidadesRestar = $diferencia;

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_disponible > $unidadesRestar) {

                    $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;


                    $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                    $lote->cantidad_disponible = $diferencia;
                    $lote->save();

                    $registroResta = $unidadesRestar;
                    $unidadesRestar = 0;

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_disponible < $unidadesRestar) {

                    $diferencia = $unidadesRestar - $unidadesDisponibles->cantidad_disponible;
                    $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                    $lote->cantidad_disponible = 0;
                    $lote->save();

                    $registroResta = $unidadesDisponibles->cantidad_disponible;
                    $unidadesRestar = $diferencia;

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                    $cantidadSeccion = $registroResta / $unidad;
                };


                array_push($this->arrayProductos, [
                    "factura_id" => $idFactura,
                    "producto_id" => $idProducto,
                    "lote" => $unidadesDisponibles->id,
                    "indice" => $indice,

                    // "numero_unidades_resta_inventario" => $registroResta, //el numero de unidades que se va restar del inventario pero en unidad base
                    "seccion_id" => $idSeccion,
                    "sub_total" => $subTotal,
                    "isv" => $isv,
                    "total" => $total,
                    "numero_unidades_resta_inventario" => $registroResta, //La cantidad de unidades que se resta por lote - esta canitdad es ingresada por el usuario - se **multipla** por la unidad de medida venta para convertir a unidad base y restar de la tabla recibido bodega **la cantidad que se resta por lote**
                    "unidades_nota_credito_resta_inventario" => $registroResta, // Este campo tiene el mismo valor que **numero_unidades_resta_inventario** - se utiliza para registrar las unidades a devolver en la nota de credito - resta las unidades y las devuelve a la tabla **recibido_bodega**
                    "resta_inventario_total" => $unidadesRestarInv, //Es la cantidad ingresada por el usuario en la pantalla de factura - misma cantidad se **multiplica** por la unidad de venta - registra la cantidad total a restar en la seccion_id- se repite para el lote
                    "unidad_medida_venta_id" => $idUnidadVenta, //la unidad de medida que selecciono el usuario para la venta
                    "precio_unidad" => $precio, // precio de venta ingresado por el usuario
                    "cantidad" => $cantidad, //Es la cantidad escrita por el usuario en la pantalla de factura la cual se va restar a la seccion - esta cantidad no sufre ningun tipo de alteracion - se guardar tal cual la ingresa el usuario
                    "cantidad_nota_credito"=> $cantidad, //Este campo contiene el mismo valor que el campo **cantidad** - es la cantidad ingresada por el usuario en la pantalla de factura - a este campo se le restan la cantidad a devolver en la nota de credito
                    "cantidad_s" => $cantidadSeccion, //Es la cantidad que se resta por lote - esta cantidad se convierte de unidad base a la unidad de venta seleccionada en la pantalla de factura - al realizar esta convercion es posible obtener decimales como resultado.
                    "cantidad_para_entregar" => $registroResta, //las unidades basica 1 disponible para vale
                    "sub_total_s" => $subTotalSecccionado,
                    "isv_s" => $isvSecccionado,
                    "total_s" => $totalSecccionado,
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);

                array_push($this->arrayLogs, [
                    "origen" => $unidadesDisponibles->id,
                    "factura_id" => $idFactura,
                    "cantidad" => $registroResta,
                    "unidad_medida_venta_id" => $idUnidadVenta,
                    "users_id" => Auth::user()->id,
                    "descripcion" => "Venta de producto",
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);

            };

            //dd($arrarVentasProducto);
            //ModelVentaProducto::created($arrarVentasProducto);
            //ModelVentaProducto::insert($arrarVentasProducto);
            //DB::table('venta_has_producto')->insert($arrarVentasProducto);


            return;
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'error' => $e,
                'icon' => "error",
                'text' => 'Ha ocurrido un error.',
                'title' => 'Error!',
                'idFactura' => $idFactura,
            ], 402);
        }
    }

    public function comprobarCreditoCliente($idCliente, $totalFactura)
    {



        $credito = DB::SELECTONE(
            "
        select credito from cliente where  id = " . $idCliente
        );

        if ($totalFactura > $credito->credito) {
            return true;
        }

        return false;
    }

    public function comprobarFacturaVencida($idCliente)
    {
        /* $facturasVencidas = DB::SELECT(
            "
            select
            id
            from factura
            where
            pendiente_cobro > 0
            and fecha_vencimiento < curdate()
            and estado_venta_id = 1
            and tipo_pago_id = 2 and cliente_id=" . $idCliente
        ); */

        $facturasVencidas = DB::SELECT(
            "
            select
            id
            from factura fa
            inner join aplicacion_pagos ap on ap.factura_id = fa.id
            where
            ap.estado_cerrado <> 2
            and ap.saldo <> 0
            and ap.estado = 1
            and fa.fecha_vencimiento < curdate()
            and fa.estado_venta_id = 1
            and fa.tipo_pago_id = 2 and fa.cliente_id=" . $idCliente
        );

        if (!empty($facturasVencidas)) {
            return true;
        }

        return false;
    }



    public function restarCreditoCliente($idCliente, $totalFactura, $idFactura)
    {

        $cliente = ModelCliente::find($idCliente);
        $resta = $cliente->credito - $totalFactura;
        $cliente->credito = $resta;
        $cliente->save();

        $logCredito = new logCredito;
        $logCredito->descripcion = 'Reducción  de credito por factura.';
        $logCredito->monto = $totalFactura;
        $logCredito->factura_id = $idFactura;
        $logCredito->cliente_id = $idCliente;
        $logCredito->users_id = Auth::user()->id;
        $logCredito->save();

        return true;
    }

    public function obtenerOrdenCompra(Request $request){

        $ordenes = DB::SELECT("select id, numero_orden as text  from numero_orden_compra where estado_id = 1 and cliente_id = ".$request->idCliente);

        return response()->json([
            "results" => $ordenes
        ],200);

    }
}
