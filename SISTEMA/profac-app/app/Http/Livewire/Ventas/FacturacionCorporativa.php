<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;


use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelParametro;
use App\Models\ModelLista;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\User;
use App\Http\Controllers\CAI\Notificaciones;

class FacturacionCorporativa extends Component
{
    public $arrayProductos = [];
    public $arrayLogs = [];

    public function render()
    {
        return view('livewire.ventas.facturacion-corporativa');
    }

    public function listarClientes(Request $request)
    {
        try {


            /* $listaClientes = DB::SELECT("
                select
                    id,
                    nombre as text
                from cliente
                    where estado_cliente_id = 1
                    and tipo_cliente_id=1
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                        ");*/


            if (Auth::user()->rol_id == 1 or Auth::user()->rol_id == 3) {
                $listaClientes = DB::SELECT("
                select
                    cliente.id,
                    cliente.nombre as text,
                    users.id as 'idVendedor',
                    users.name as 'vendedor'
                from cliente
                inner join users on users.id = cliente.vendedor
                    where cliente.estado_cliente_id = 1

                    and cliente.tipo_cliente_id=1
                    and  (cliente.id LIKE '%" . $request->search . "%' or cliente.nombre Like '%" . $request->search . "%') limit 15
                        ");
            } else {
                $listaClientes = DB::SELECT("
                select
                    cliente.id,
                    cliente.nombre as text,
                    users.id as 'idVendedor',
                    users.name as 'vendedor'
                from cliente
                inner join users on users.id = cliente.vendedor
                    where cliente.estado_cliente_id = 1

                    and cliente.tipo_cliente_id=1
                    and  (cliente.id LIKE '%" . $request->search . "%' or cliente.nombre Like '%" . $request->search . "%') limit 15
                        ");
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

    public function datosCliente(Request $request)
    {
        try {

            $datos = DB::SELECTONE("select cliente.id,cliente.nombre, cliente.rtn, cliente.dias_credito ,

                    users.id as 'idVendedor',
                    users.name as 'vendedor'
                from cliente
                inner join users on users.id = cliente.vendedor where cliente.id = " . $request->id);

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

         (B.nombre LIKE '%" . $request->search . "%' or B.id LIKE '%" . $request->search . "%' or B.codigo_barra Like '%" . $request->search . "%')

         and B.id not in (
            4088,
            4036,
            1157,
            1321,
            2665,
            2585,
            2409,
            2464,
            1569,
            1506,
            2708,
            2937,
            2645,
            1118,
            2652,
            3355,
            3356,
            3358,
            3359,
            3360,
            3361,
            3362,
            1259,
            1231,
            2452,
            3386,
            3387,
            3084,
            3391,
            3390,
            3077,
            3375,
            3378,
            3384,
            3383,
            3381,
            3382,
            2948,
            3554,
            2714,
            2021,
            2026,
            2469,
            2025,
            2470,
            2024,
            2471,
            2022,
            2473,
            2921,
            2023,
            2472,
            2597,
            2277,
            2252,
            2544,
            3389,
            3388,
            3385,
            3357,
            2417,
            3887,
            3888
                    )
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

            // $secciones = DB::SELECT("
            // select
            //     B.id,
            //     B.descripcion,
            //     D.nombre
            // from recibido_bodega A
            //     inner join seccion B
            //     on A.seccion_id = B.id
            //     inner join segmento C
            //     on B.segmento_id = C.id
            //     inner join bodega D
            //     on C.bodega_id = D.id
            // where producto_id = ".$request->idProducto." and D.id =".$request->idBodega."
            // group by B.id
            // ");

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
            /* CAMBIO 20230725 FORMAT(ultimo_costo_compra,2):FORMAT(precio_base,2)*/
            $producto = DB::SELECTONE("
            select
            id,
            concat(id,' - ',nombre) as nombre,
            isv,
            ultimo_costo_compra as ultimo_costo_compra,
            precio_base as precio_base
            from producto where id = " . $request['idProducto'] . "
            ");


            return response()->json([
                "producto" => $producto,

                "unidades" => $unidades
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'ERROR AL OBTENER PRODUCTO PARA EL CARRITO.',
                'error' => $e,
            ], 402);
        }
    }

    public function guardarVenta(Request $request)
    {
        // dd($request->nota_comen);
        try {



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
                'vendedor' => 'required'



            ]);



            if ($validator->fails()) {
                return response()->json([
                    'icon' => 'error',
                    'title' => 'error',
                    'text' => 'Por favor, verificar que todos los campos esten completados.',
                    'mensaje' => 'Ha ocurrido un error al crear la compra.',
                    'errors' => $validator->errors()
                ], 401);
            }
            //


            if ($request->restriccion == 1) {
                //dd($request);
                $facturaVencida = $this->comprobarFacturaVencida($request->seleccionarCliente);

                //dd('llego dentro de funcion facturas vencidas despues de llamarlo');
                if ($facturaVencida) {
                    return response()->json([
                        'icon' => 'warning',
                        'title' => 'Advertencia!',
                        'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', cuenta con facturas vencidas y sin cerrar en estado de cuenta. Por el momento no se puede emitir factura a este cliente.',

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




            $arrayTemporal = $request->arregloIdInputs;
            $arrayInputs = explode(',', $arrayTemporal);


            $numeroSecuencia = null;
            $mensaje = "";
            $flag = false;
            // $turno = null;
            $factura = null;


            //comprobar existencia de producto en bodega
            for ($j = 0; $j < count($arrayInputs); $j++) {

                $keyIdSeccion = "idSeccion" . $arrayInputs[$j];
                $keyIdProducto = "idProducto" . $arrayInputs[$j];
                $keyRestaInventario = "restaInventario" . $arrayInputs[$j];
                $keyNombre = "nombre" . $arrayInputs[$j];
                $keyBodega = "bodega" . $arrayInputs[$j];

                $resultado = DB::selectONE("
            select
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
            //comprobar existencia de producto en bodega

            $flagEstado = DB::SELECTONE("select estado_encendido from parametro where id = 1");

            if ($flagEstado->estado_encendido == 1) {
                $estado = 1;
            } else {
                $estado = 2;
            }







            DB::beginTransaction();




            if ($estado == 1) {
                //presenta

                $cai = DB::SELECTONE("select
                id,
                numero_inicial,
                numero_final,
                cantidad_otorgada,
                numero_actual
                from cai
                where tipo_documento_fiscal_id = 1 and estado_id = 1");


                if ($cai->numero_actual > $cai->cantidad_otorgada) {

                    return response()->json([
                        "title" => "Advertencia",
                        "icon" => "warning",
                        "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado.",
                    ], 200);
                }






                $numeroSecuencia = $cai->numero_actual;
                $arrayCai = explode('-', $cai->numero_final);
                $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
                $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;



                $montoComision = $request->totalGeneral * 0.5;

                if ($request->tipoPagoVenta == 1) {
                    $diasCredito = 0;
                } else {
                    $dias = DB::SELECTONE("select dias_credito from cliente where id = " . $request->seleccionarCliente);
                    $diasCredito = $dias->dias_credito;
                }

                $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");


                $validarCAI = new Notificaciones();
                $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);


                $factura = new ModelFactura;
                $factura->numero_factura = $numeroVenta->numero;
                $factura->cai = $numeroCAI;
                $factura->numero_secuencia_cai = $numeroSecuencia;
                $factura->nombre_cliente = $request->nombre_cliente_ventas;
                $factura->rtn = $request->rtn_ventas;
                $factura->sub_total = $request->subTotalGeneral;
                $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
                $factura->sub_total_excento = $request->subTotalGeneralExcento;
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
                $factura->tipo_venta_id = 1; // corporativa
                $factura->estado_factura_id = 1; // se presenta
                $factura->users_id = Auth::user()->id;
                $factura->comision_estado_pagado = 0;
                $factura->pendiente_cobro = $request->totalGeneral;
                $factura->estado_editar = 1;
                $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
                $factura->comprovante_entrega_id = $request->idComprobante;
                $factura->numero_orden_compra_id = $request->ordenCompra;
                $factura->comentario = $request->nota_comen;
                $factura->porc_descuento = $request->porDescuento;
                $factura->monto_descuento = $request->porDescuentoCalculado;
                $factura->save();

                $caiUpdated =  ModelCAI::find($cai->id);
                $caiUpdated->numero_actual = $numeroSecuencia + 1;
                $caiUpdated->cantidad_no_utilizada = $cai->cantidad_otorgada - $numeroSecuencia;
                $caiUpdated->save();

                /* $aplicacionPagos = DB::select("

                CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);"
                );


                if ($aplicacionPagos[0]->estado == -1) {
                    return response()->json([
                        "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                        "icon" => "error",
                        "title"=>"Error!"
                    ],400);
                } */
            } else {

                // alterna
                $lista = DB::SELECT("select id, numero from listado where eliminado = 0 order by secuencia ASC");
                $espera = DB::SELECT("select id from enumeracion where eliminado = 0 order by secuencia ASC");

                // $contadorCai = DB::SELECTONE("select numero_actual, serie from cai where estado_id = 1 and tipo_documento_fiscal_id=1");
                // $diferenciaContador = $contadorCai->numero_actual - $contadorCai->serie;

                if (!empty($lista)) {

                    $factura = $this->metodoLista($request);
                } else if (!empty($espera)) {

                    $factura = $this->enumerar($request);
                } else {

                    //$factura = $this->alternar($request);
                    $factura = $this->guardarVentaND($request);
                }
            }



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

                // dd($factura);

                $this->restarUnidadesInventario($restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $arrayInputs[$i]);
            };

            if ($request->tipoPagoVenta == 2) { //si el tipo de pago es credito
                $this->restarCreditoCliente($request->seleccionarCliente, $request->totalGeneral, $factura->id);
            }




            ModelVentaProducto::insert($this->arrayProductos);
            ModelLogTranslados::insert($this->arrayLogs);


            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");
            DB::commit();



            /*  <a href="/venta/cobro/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Realizar Pago</a> */
            return response()->json([
                'icon' => "success",
                'text' => '
                <div class="d-flex justify-content-between">
                    <a href="/factura/cooporativo/' . $factura->id . '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-invoice"></i> Imprimir Factura</a>
                    <a href="/crear/vale/lista/espera/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-list-check"></i> Crear Vale Tipo: 2</a>
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
                'idFactura' => $factura->id,
                'mensajeError' => $e
            ], 402);
        }
    }

    public function alternar($request)
    {


        try {
            $numeroSecuencia = 0;
            $numeroSecuenciaUpdated = 0;

            $turno = DB::SELECTONE("select turno from parametro where id =1");



            if ($turno->turno == 1) {
                $turnoActualizar = 2;

                $cai = DB::SELECTONE("select
                                id,
                                numero_inicial,
                                numero_final,
                                cantidad_otorgada,
                                numero_actual as 'numero_actual'
                                from cai
                                where tipo_documento_fiscal_id = 1 and estado_id = 1");
            } else {

                $turnoActualizar = 1;

                $cai = DB::SELECTONE("select
                                id,
                                numero_inicial,
                                numero_final,
                                cantidad_otorgada,
                                serie as 'numero_actual'
                                from cai
                                where tipo_documento_fiscal_id = 1 and estado_id = 1");
            }

            $estado = $turno->turno;

            $arrayCai = explode('-', $cai->numero_final);
            $cuartoSegmentoCAI = sprintf("%'.08d", $cai->numero_actual);
            $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

            $duplicado = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai_id=" . $cai->id . " and cai='" . $numeroCAI . "'");

            // if($duplicado->contador>=1){

            //     $numeroSecuencia = $numeroSecuencia + $cai->numero_actual + 1;
            //     $numeroSecuenciaUpdated = $cai->numero_actual+2;

            // }

            $existencia = DB::SELECTONE(
                "select
                            id
                            from factura
                            where  estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $cai->id . " and numero_secuencia_cai=" . $cai->numero_actual
            );

            // if(!empty($existencia)){
            //     //existe
            //     $numeroSecuencia = $cai->numero_actual+1;
            //     $numeroSecuenciaUpdated = $cai->numero_actual+2;

            // }else{
            //     //no existe
            //     $numeroSecuencia = $cai->numero_actual;
            //     $numeroSecuenciaUpdated = $cai->numero_actual+1;

            // }

            if ($duplicado->contador >= 2 || !empty($existencia)) {
                $numeroSecuencia =  $this->comprobacionRecursiva($request, $cai, $cai->numero_actual, $estado);
                $numeroSecuenciaUpdated = $numeroSecuencia + 1;
            } else {
                $numeroSecuencia = $cai->numero_actual;
                $numeroSecuenciaUpdated = $cai->numero_actual + 1;
            }

            $arrayNumeroFinal = explode('-', $cai->numero_final);
            $numero_final = (string)((int)($arrayNumeroFinal[3]));

            if ($numeroSecuencia > $numero_final) {
                return response()->json([
                    "title" => "Advertencia",
                    "icon" => "warning",
                    "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 2.",
                ], 200);
            }

            // if ($numeroSecuencia > $cai->numero_actual) {
            //     $this->guardarEnumeracion($cai->numero_actual, $cai, $estado);
            // }

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
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn = $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
            $factura->sub_total_excento = $request->subTotalGeneralExcento;
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
            $factura->tipo_venta_id = 1; //coorporativo;
            $factura->estado_factura_id = $estado; // se presenta
            $factura->users_id = Auth::user()->id;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            $factura->comprovante_entrega_id = $request->idComprobante;
            $factura->comentario = $request->nota_comen;
            $factura->save();

            if ($turno->turno == 1) {
                $caiUpdated =  ModelCAI::find($cai->id);
                $caiUpdated->numero_actual = $numeroSecuenciaUpdated;
                $caiUpdated->save();
            } else {
                $caiUpdated =  ModelCAI::find($cai->id);
                $caiUpdated->serie = $numeroSecuenciaUpdated;
                $caiUpdated->save();
            }


            // if(empty($existencia)){
            //     $caiUpdated =  ModelCAI::find($cai->id);
            //     $caiUpdated->serie=$numeroSecuencia;
            //     //$caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }else{
            //     $caiUpdated =  ModelCAI::find($cai->id);
            //     $caiUpdated->serie=$numeroSecuencia+1;
            // // $caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }

            $parametro = ModelParametro::find('1');
            $parametro->turno = $turnoActualizar;
            $parametro->save();

            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */

            return $factura;
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error, meotodo alternar',
                'error' => $e
            ], 402);
        }
    }


    public function nivelacion($request)
    {



        $numeroSecuencia = 0;
        $numeroSecuenciaUpdated = 0;



        $cai = DB::SELECTONE("select
            id,
            numero_inicial,
            numero_final,
            cantidad_otorgada,
            serie as 'numero_actual'
            from cai
            where tipo_documento_fiscal_id = 1 and estado_id = 1");



        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $cai->numero_actual);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

        $duplicado = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai_id=" . $cai->id . " and cai='" . $numeroCAI . "'");



        $existencia = DB::SELECTONE(
            "select
                            id
                            from factura
                            where  estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $cai->id . " and numero_secuencia_cai=" . $cai->numero_actual
        );



        if ($duplicado->contador >= 2 || !empty($existencia)) {
            $numeroSecuencia =  $this->comprobacionRecursiva($request, $cai, $cai->numero_actual, 2);
            $numeroSecuenciaUpdated = $numeroSecuencia + 1;
        } else {
            $numeroSecuencia = $cai->numero_actual;
            $numeroSecuenciaUpdated = $cai->numero_actual + 1;
        }

        $arrayNumeroFinal = explode('-', $cai->numero_final);
        $numero_final = (string)((int)($arrayNumeroFinal[3]));

        if ($numeroSecuencia > $numero_final) {
            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 2.",
            ], 200);
        }


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
        $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

        $factura = new ModelFactura;
        $factura->numero_factura = $numeroVenta->numero;
        $factura->cai = $numeroCAI;
        $factura->numero_secuencia_cai = $numeroSecuencia;
        $factura->nombre_cliente = $request->nombre_cliente_ventas;
        $factura->rtn = $request->rtn_ventas;
        $factura->sub_total = $request->subTotalGeneral;
        $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
        $factura->sub_total_excento = $request->subTotalGeneralExcento;
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
        $factura->tipo_venta_id = 1; //coorporativo;
        $factura->estado_factura_id = 2; // se presenta
        $factura->users_id = Auth::user()->id;
        $factura->comision_estado_pagado = 0;
        $factura->pendiente_cobro = $request->totalGeneral;
        $factura->estado_editar = 1;
        $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
        $factura->comprovante_entrega_id = $request->idComprobante;
        $factura->comentario = $request->nota_comen;
        $factura->save();


        $caiUpdated =  ModelCAI::find($cai->id);
        $caiUpdated->serie = $numeroSecuenciaUpdated;
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

        return $factura;
        // } catch (QueryException $e) {
        //     DB::rollback();
        //     return response()->json([
        //         'message' => 'Ha ocurrido un error, meotodo alternar',
        //         'error' => $e
        //     ], 402);
        // }
    }



    public function metodoLista($request)
    {
        try {

            //dd("lista");
            $numeroSecuencia = null;
            $cai = DB::SELECTONE("select * from listado where eliminado = 0 order by secuencia ASC limit 1");

            $comprobarDuplicados = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai ='" . $cai->numero . "'");


            if ($comprobarDuplicados->contador >= 2) {
                // DB::delete("DELETE FROM listado WHERE id = ".$cai->id);
                DB::update("UPDATE listado SET eliminado =  1 WHERE id = " . $cai->id);

                return $this->alternar($request);

                // return response()->json([
                //     "icon" => "error",
                //     "title" => "Error!",
                //     "text" => "Por favor intentar facturar a otro cliente en este momento."
                // ], 402);
            }

            $existencia = DB::SELECTONE(
                "
                select
                id
                from factura
                where estado_factura_id=2  and  estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $cai->cai_id . " and numero_secuencia_cai=" . $cai->secuencia

            );




            if (!empty($existencia)) {
                return $this->alternar($request);


                // return response()->json([
                //     "icon" => "error",
                //     "title" => "Error!",
                //     "text" => "Por favor intentar facturar a otro cliente en este momento."
                // ], 402);
            } else {
                $numeroSecuencia = $cai->secuencia;
            }

            if ($numeroSecuencia < $cai->cantidad_otorgada) { //$cai->numero_actual > $cai->cantidad_otorgada//$numeroSecuencia > $cai->cantidad_otorgada

                return response()->json([
                    "title" => "Advertencia",
                    "icon" => "warning",
                    "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 3.",
                ], 200);
            }


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
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn = $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
            $factura->sub_total_excento = $request->subTotalGeneralExcento;
            $factura->isv = $request->isvGeneral;
            $factura->total = $request->totalGeneral;
            $factura->credito = $request->totalGeneral;
            $factura->fecha_emision = $request->fecha_emision;
            $factura->fecha_vencimiento = $request->fecha_vencimiento;
            $factura->tipo_pago_id = $request->tipoPagoVenta;
            $factura->dias_credito = $diasCredito;
            $factura->cai_id = $cai->cai_id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 1; //coorporativo;
            $factura->estado_factura_id = 2; // se presenta
            $factura->users_id = Auth::user()->id;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            $factura->comprovante_entrega_id = $request->idComprobante;
            $factura->comentario = $request->nota_comen;
            $factura->save();



            // if(!empty($existencia)){
            //     $caiUpdated =  ModelCAI::find($cai->cai_id);
            //     $caiUpdated->serie = $numeroSecuencia;
            //     //$caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }else{
            //     $caiUpdated =  ModelCAI::find($cai->cai_id);
            //     $caiUpdated->serie = $numeroSecuencia+1;
            // // $caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }

            //DB::delete("DELETE FROM listado WHERE id = ".$cai->id);
            //DB::update("UPDATE cai SET serie = serie + 1 WHERE id =".$cai->cai_id);
            DB::update("UPDATE listado SET eliminado =  1 WHERE id = " . $cai->id);


            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */

            return $factura;
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'error' => $e,
                'icon' => "error",
                'text' => 'Ha ocurrido un error. Metodo Lista',
                'title' => 'Error!',
            ], 402);
        }
    }

    public function restarUnidadesInventario($unidadesRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $indice)
    {
        try {

            $precioUnidad = $subTotal / $unidadesRestarInv;

            $unidadesRestar = $unidadesRestarInv; //es la cantidad ingresada por el usuario multiplicado por unidades de venta del producto
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

                    /* CAMBIO 20230725 round(($precioUnidad * $registroResta), 2):round(($subTotalSecccionado * ($ivsProducto / 100)), 2):round(($isvSecccionado + $subTotalSecccionado), 2)*/
                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 4);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 4);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 4);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_disponible > $unidadesRestar) {

                    $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;


                    $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                    $lote->cantidad_disponible = $diferencia;
                    $lote->save();

                    $registroResta = $unidadesRestar;
                    $unidadesRestar = 0;
                    /* CAMBIO 20230725 round(($precioUnidad * $registroResta), 2):round(($subTotalSecccionado * ($ivsProducto / 100)), 2):round(($isvSecccionado + $subTotalSecccionado), 2)*/
                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 4);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 4);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 4);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_disponible < $unidadesRestar) {

                    $diferencia = $unidadesRestar - $unidadesDisponibles->cantidad_disponible;
                    $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                    $lote->cantidad_disponible = 0;
                    $lote->save();

                    $registroResta = $unidadesDisponibles->cantidad_disponible;
                    $unidadesRestar = $diferencia;

                    /* CAMBIO 20230725 round(($precioUnidad * $registroResta), 2):round(($subTotalSecccionado * ($ivsProducto / 100)), 2):round(($isvSecccionado + $subTotalSecccionado), 2)*/

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 4);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 4);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 4);

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
                    "cantidad_nota_credito" => $cantidad, //Este campo contiene el mismo valor que el campo **cantidad** - es la cantidad ingresada por el usuario en la pantalla de factura - a este campo se le restan la cantidad a devolver en la nota de credito
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

    public function imprimirFacturaCoorporativa($idFactura)
    {

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        A.comentario,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        name,
        D.id as factura

       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       where A.id = " . $idFactura);

        $cliente = DB::SELECTONE("
       select
        factura.nombre_cliente as nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        cliente.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = " . $idFactura);

        $importes = DB::SELECTONE("
        select
        total,
        isv,
        sub_total,
        sub_total_grabado,
        sub_total_excento,
        porc_descuento,
        monto_descuento
        from factura
        where id = " . $idFactura);

        /* CAMBIO 20230725 FORMAT(total,2) as total:FORMAT(isv,2) as isv:FORMAT(sub_total,2) as sub_total,:FORMAT(sub_total_grabado,2) as sub_total_grabado:FORMAT(sub_total_excento,2) as sub_total_excento*/
        $importesConCentavos = DB::SELECTONE("
        select
        FORMAT(total,2) as total,
        FORMAT(isv,2) as isv,
        FORMAT(sub_total,2) as sub_total,
        FORMAT(sub_total_grabado,2) as sub_total_grabado,
        FORMAT(sub_total_excento,2) as sub_total_excento,
        FORMAT(porc_descuento,2) as porc_descuento,
        FORMAT(monto_descuento,2) as monto_descuento
        from factura where factura.id = " . $idFactura);


        /* CAMBIO 20230725 FORMAT(B.sub_total/B.cantidad,2) as precio:FORMAT(sum(B.cantidad_s),2) as cantidad:FORMAT(sum(B.sub_total_s),2) as importe*/
        // linea cambiada FORMAT(TRUNCATE(B.sub_total/B.cantidad, 2),2) as precio,
        $productos = DB::SELECT(
            "
            select
            *
            from (
            select
                B.producto_id as codigo,
                concat(C.nombre) as descripcion,
                UPPER(J.nombre) as medida,
                if(C.isv = 0, 'SI' , 'NO' ) as excento,
                H.nombre as bodega,
                REPLACE(REPLACE(F.descripcion,'Seccion',''),' ', '') as seccion,
                FORMAT(B.precio_unidad,2) as precio,
                REPLACE(sum(B.cantidad_s), '.00', '') as cantidad,
                FORMAT(sum(B.sub_total_s),2) as importe
            from factura A
            inner join venta_has_producto B
            on A.id = B.factura_id
            inner join producto C
            on B.producto_id = C.id
            inner join unidad_medida_venta D
            on B.unidad_medida_venta_id = D.id
            inner join unidad_medida J
            on J.id = D.unidad_medida_id
            inner join recibido_bodega E
            on B.lote = E.id
            inner join seccion F
            on E.seccion_id = F.id
            inner join segmento G
            on F.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where A.id=" . $idFactura . "
            group by codigo, descripcion, medida, bodega, seccion, precio,B.indice
            order by B.indice asc
            ) A


            union

            Select
            D.id,
            D.nombre as descripcion,
            F.nombre as medida,
            if(C.isv = 0, 'SI' , 'NO' ) as excento,
            'N/A',
            'N/A',
            FORMAT(C.precio,2) as precio,
            C.cantidad as cantidad,
            FORMAT(C.sub_total,2)
            from factura A
            inner join vale B
            on A.id = B.factura_id
            inner join espera_has_producto C
            on B.id = C.vale_id
            inner join producto D
            on C.producto_id = D.id
            inner join unidad_medida_venta E
            on C.unidad_medida_venta_id = E.id
            inner join unidad_medida F
            on F.id = E.unidad_medida_id
            where B.estado_id=1 and A.id = " . $idFactura

        );
        // for ($i=0; $i < 15 ; $i++) {
        //     echo($productos[$i]);
        // }

        //dd($productos);

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ["numero_orden" => " N/A"];
        } else {
            $ordenCompra = ["numero_orden" => $ordenCompra->numero_orden];
        }


        if (fmod($importes->total, 1) == 0.0) {
            $flagCentavos = false;
        } else {
            $flagCentavos = true;
        }
        /*CAMBIO 20230725 $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');*/
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/factura', compact('cai', 'cliente', 'importes', 'productos', 'numeroLetras', 'importesConCentavos', 'flagCentavos', 'ordenCompra'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura . ".pdf");
    }

    public function imprimirFacturaCoorporativaCopia($idFactura)
    {

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        A.comentario,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        name,
        D.id as factura

       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       where A.id = " . $idFactura);

        $cliente = DB::SELECTONE("
       select
        factura.nombre_cliente as nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        cliente.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = " . $idFactura);

        $importes = DB::SELECTONE("
        select
        total,
        isv,
        sub_total,
        sub_total_grabado,
        sub_total_excento,
        porc_descuento,
        monto_descuento
        from factura
        where id = " . $idFactura);

        /* CAMBIO 20230725 FORMAT(total,2) as total:FORMAT(isv,2) as isv:FORMAT(sub_total,2) as sub_total,:FORMAT(sub_total_grabado,2) as sub_total_grabado:FORMAT(sub_total_excento,2) as sub_total_excento*/
        $importesConCentavos = DB::SELECTONE("
        select
        FORMAT(total,2) as total,
        FORMAT(isv,2) as isv,
        FORMAT(sub_total,2) as sub_total,
        FORMAT(sub_total_grabado,2) as sub_total_grabado,
        FORMAT(sub_total_excento,2) as sub_total_excento,
        FORMAT(porc_descuento,2) as porc_descuento,
        FORMAT(monto_descuento,2) as monto_descuento
        from factura where factura.id = " . $idFactura);


        /* CAMBIO 20230725 FORMAT(B.sub_total/B.cantidad,2) as precio:FORMAT(sum(B.cantidad_s),2) as cantidad:FORMAT(sum(B.sub_total_s),2) as importe*/
        // linea cambiada FORMAT(TRUNCATE(B.sub_total/B.cantidad, 2),2) as precio,
        $productos = DB::SELECT(
            "
            select
            *
            from (
            select
                B.producto_id as codigo,
                concat(C.nombre) as descripcion,
                UPPER(J.nombre) as medida,
                if(C.isv = 0, 'SI' , 'NO' ) as excento,
                H.nombre as bodega,
                REPLACE(REPLACE(F.descripcion,'Seccion',''),' ', '') as seccion,
                B.precio_unidad as precio,
                REPLACE(sum(B.cantidad_s), '.00', '') as cantidad,
                format(sum(B.sub_total_s),2) as importe
            from factura A
            inner join venta_has_producto B
            on A.id = B.factura_id
            inner join producto C
            on B.producto_id = C.id
            inner join unidad_medida_venta D
            on B.unidad_medida_venta_id = D.id
            inner join unidad_medida J
            on J.id = D.unidad_medida_id
            inner join recibido_bodega E
            on B.lote = E.id
            inner join seccion F
            on E.seccion_id = F.id
            inner join segmento G
            on F.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where A.id=" . $idFactura . "
            group by codigo, descripcion, medida, bodega, seccion, precio,B.indice
            order by B.indice asc
            ) A


            union

            Select
            D.id,
            D.nombre as descripcion,
            F.nombre as medida,
            if(C.isv = 0, 'SI' , 'NO' ) as excento,
            'N/A',
            'N/A',
            C.precio as precio,
            C.cantidad as cantidad,
            C.sub_total
            from factura A
            inner join vale B
            on A.id = B.factura_id
            inner join espera_has_producto C
            on B.id = C.vale_id
            inner join producto D
            on C.producto_id = D.id
            inner join unidad_medida_venta E
            on C.unidad_medida_venta_id = E.id
            inner join unidad_medida F
            on F.id = E.unidad_medida_id
            where B.estado_id=1 and A.id = " . $idFactura

        );
        // for ($i=0; $i < 15 ; $i++) {
        //     echo($productos[$i]);
        // }

        //dd($productos);

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ["numero_orden" => " N/A"];
        } else {
            $ordenCompra = ["numero_orden" => $ordenCompra->numero_orden];
        }


        if (fmod($importes->total, 1) == 0.0) {
            $flagCentavos = false;
        } else {
            $flagCentavos = true;
        }
        /*CAMBIO 20230725 $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');*/
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/facturaCopia', compact('cai', 'cliente', 'importes', 'productos', 'numeroLetras', 'importesConCentavos', 'flagCentavos', 'ordenCompra'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura . ".pdf");
    }


    public function guardarEnumeracion($numeroSecuencia, $cai, $estado)
    {

        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;


        DB::INSERT("INSERT INTO enumeracion(
            numero, secuencia, numero_inicial, numero_final, cantidad_otorgada, cai_id, estado, created_at, updated_at, eliminado) VALUES
           ('" . $numeroCAI . "','" . $numeroSecuencia . "','" . $cai->numero_inicial . "','" . $cai->numero_final . "','" . $cai->cantidad_otorgada . "','" . $cai->id . "'," . $estado . ",'" . NOW() . "','" . NOW() . "',0)");

        return;
    }

    public function enumerar($request)
    {
        try {


            $listado = DB::SELECTONE("
            select
            id,
            numero,
            secuencia,
            numero_inicial,
            numero_final,
            cantidad_otorgada,
            cai_id,
            estado
            from enumeracion
            where eliminado = 0
            order by secuencia asc
            limit 1");



            //comprobar si esta dos veces
            $duplicado = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai_id=" . $listado->cai_id . " and cai='" . $listado->numero . "'");



            $existencia = DB::SELECTONE(
                "
            select
            count(id) as contador
            from factura
            where estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $listado->cai_id . " and numero_secuencia_cai=" . $listado->secuencia

            );



            //
            if ($duplicado->contador >= 2) {
                DB::update("UPDATE enumeracion SET eliminado =  1 WHERE id = " . $listado->id);
                return $this->alternar($request);
            }

            if (!empty($existencia->contador >= 2)) {
                DB::update("UPDATE enumeracion SET eliminado =  1 WHERE id = " . $listado->id);
                return $this->alternar($request);
            }

            if ($existencia->contador != 0) {
                return $this->alternar($request);
            }

            $arrayCai = explode('-', $listado->numero_final);
            $cuartoSegmentoCAI = sprintf("%'.08d", $listado->secuencia);
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
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $listado->secuencia, 1);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $listado->secuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn = $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
            $factura->sub_total_excento = $request->subTotalGeneralExcento;
            $factura->isv = $request->isvGeneral;
            $factura->total = $request->totalGeneral;
            $factura->credito = $request->totalGeneral;
            $factura->fecha_emision = $request->fecha_emision;
            $factura->fecha_vencimiento = $request->fecha_vencimiento;
            $factura->tipo_pago_id = $request->tipoPagoVenta;
            $factura->dias_credito =  $diasCredito;
            $factura->cai_id = $listado->cai_id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 1; //coorporativo;
            $factura->estado_factura_id = $listado->estado; // se presenta
            $factura->users_id = Auth::user()->id;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            $factura->comprovante_entrega_id = $request->idComprobante;
            $factura->comentario = $request->nota_comen;
            $factura->save();


            //DB::delete("DELETE FROM enumeracion WHERE id = ".$listado->id);
            DB::update("UPDATE enumeracion SET eliminado =  1 WHERE id = " . $listado->id);

            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */
            return $factura;
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

    public function comprobacionRecursiva($request, $cai, $numeroActual, $estado)
    {

        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroActual);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

        $duplicado = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai_id=" . $cai->id . " and cai='" . $numeroCAI . "'");


        $existencia = DB::SELECTONE(
            "
            select
            id
            from factura
            where  estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $cai->id . " and numero_secuencia_cai=" . $numeroActual .
                " and UPPER(REPLACE(nombre_cliente,' ','')) = UPPER(REPLACE('" . $request->nombre_cliente_ventas . "',' ',''))"
        );



        if ($duplicado->contador >= 2) {
            $numeroActual = $numeroActual + 1;
            return $this->comprobacionRecursiva($request, $cai, $numeroActual, $estado);
        } else if (!empty($existencia)) {

            $this->guardarEnumeracion($numeroActual, $cai, $estado);
            $numeroActual = $numeroActual + 1;
            return $this->comprobacionRecursiva($request, $cai, $numeroActual, $estado);
        } else {
            return $numeroActual;
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


        //dd('llego dentro de funcion facturas vencidas Inicio');
        $facturasVencidas = DB::SELECT(
            "
            select
            fa.id
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
       // dd($facturasVencidas);

       // dd('llego dentro de funcion facturas vencidas');
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

    public function listadoVendedores()
    {



        $listadoVendedores = DB::SELECT("select id, name as text from users where rol_id = 2  ");



        return response()->json([
            'results' => $listadoVendedores,
        ], 200);
    }

    public function guardarVentaND($request)
    {



        $numeroSecuencia = 0;
        $numeroSecuenciaUpdated = 0;
        $estado = 2;

        $cai = DB::SELECTONE("select
                                id,
                                numero_inicial,
                                numero_final,
                                cantidad_otorgada,
                                serie as 'numero_actual'
                                from cai
                                where tipo_documento_fiscal_id = 1 and estado_id = 1");




        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $cai->numero_actual);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

        $duplicado = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai_id=" . $cai->id . " and cai='" . $numeroCAI . "'");



        $existencia = DB::SELECTONE(
            "select
                            id
                            from factura
                            where  estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $cai->id . " and numero_secuencia_cai=" . $cai->numero_actual
        );



        if ($duplicado->contador >= 2 || !empty($existencia)) {
            $numeroSecuencia =  $this->comprobacionRecursiva($request, $cai, $cai->numero_actual, $estado);
            $numeroSecuenciaUpdated = $numeroSecuencia + 1;
        } else {
            $numeroSecuencia = $cai->numero_actual;
            $numeroSecuenciaUpdated = $cai->numero_actual + 1;
        }

        $arrayNumeroFinal = explode('-', $cai->numero_final);
        $numero_final = (string)((int)($arrayNumeroFinal[3]));

        if ($numeroSecuencia > $numero_final) {
            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 2.",
            ], 200);
        }



        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

        $montoComision = $request->totalGeneral * 0.5;



        if ($request->tipoPagoVenta == 1) {
            $diasCredito = 0;
        } else {
            $dias = DB::SELECTONE("select dias_credito from cliente where id = " . $request->seleccionarCliente);
            $diasCredito = $dias->dias_credito;
        }

        $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

        $validarCAI = new Notificaciones();
        $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

        $factura = new ModelFactura;
        $factura->numero_factura = $numeroVenta->numero;
        $factura->cai = $numeroCAI;
        $factura->numero_secuencia_cai = $numeroSecuencia;
        $factura->nombre_cliente = $request->nombre_cliente_ventas;
        $factura->rtn = $request->rtn_ventas;
        $factura->sub_total = $request->subTotalGeneral;
        $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
        $factura->sub_total_excento = $request->subTotalGeneralExcento;
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
        $factura->tipo_venta_id = 1; //coorporativo;
        $factura->estado_factura_id = $estado;
        $factura->users_id = Auth::user()->id;
        $factura->comision_estado_pagado = 0;
        $factura->pendiente_cobro = $request->totalGeneral;
        $factura->estado_editar = 1;
        $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
        $factura->comprovante_entrega_id = $request->idComprobante;
        $factura->numero_orden_compra_id = $request->ordenCompra;
        $factura->comentario = $request->nota_comen;
        $factura->porc_descuento = $request->porDescuento;
        $factura->monto_descuento = $request->porDescuentoCalculado;
        $factura->save();


        $caiUpdated =  ModelCAI::find($cai->id);
        $caiUpdated->serie = $numeroSecuenciaUpdated;
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
        return $factura;
    }


    public function imprimirActaCoorporativa($idFactura)
    {

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        A.comentario,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        name,
        D.id as factura

       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       where A.id = " . $idFactura);

        $cliente = DB::SELECTONE("
       select
        factura.nombre_cliente as nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        cliente.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = " . $idFactura);

        $importes = DB::SELECTONE("
        select
         total,
         isv,
         sub_total,
         sub_total_grabado,
         sub_total_excento,
        porc_descuento,
         monto_descuento
        from factura
        where id = " . $idFactura);


        $importesConCentavos = DB::SELECTONE("
        select
        FORMAT(total,2) as total,
        FORMAT(isv,2) as isv,
        FORMAT(sub_total,2) as sub_total,
        FORMAT(sub_total_grabado,2) as sub_total_grabado,
        FORMAT(sub_total_excento,2) as sub_total_excento,
        FORMAT(porc_descuento,2) as porc_descuento,
        FORMAT(monto_descuento,2) as monto_descuento
        from factura where factura.id = " . $idFactura);






        $productos = DB::SELECT(
            "

            select
            *
            from (
            select
                B.producto_id as codigo,
                concat(C.nombre) as descripcion,
                UPPER(J.nombre) as medida,
                if(C.isv = 0, 'SI' , 'NO' ) as excento,
                H.nombre as bodega,
                REPLACE(REPLACE(F.descripcion,'Seccion',''),' ', '') as seccion,
                FORMAT(B.precio_unidad,2) as precio,
                REPLACE(sum(B.cantidad_s), '.00', '') as cantidad,
                FORMAT(sum(B.sub_total_s),2) as importe

            from factura A
            inner join venta_has_producto B
            on A.id = B.factura_id
            inner join producto C
            on B.producto_id = C.id
            inner join unidad_medida_venta D
            on B.unidad_medida_venta_id = D.id
            inner join unidad_medida J
            on J.id = D.unidad_medida_id
            inner join recibido_bodega E
            on B.lote = E.id
            inner join seccion F
            on E.seccion_id = F.id
            inner join segmento G
            on F.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where A.id=" . $idFactura . "
            group by codigo, descripcion, medida, bodega, seccion, precio,B.indice
            order by B.indice asc
            ) A


            union

            select
            D.id,
            D.nombre as descripcion,
            F.nombre as medida,
            if(C.isv = 0, 'SI' , 'NO' ) as excento,
            'N/A',
            'N/A',
            FORMAT(C.precio,2) as precio,
            C.cantidad as cantidad,
            FORMAT(C.sub_total,2) as sub_total

            from factura A
            inner join vale B
            on A.id = B.factura_id
            inner join espera_has_producto C
            on B.id = C.vale_id
            inner join producto D
            on C.producto_id = D.id
            inner join unidad_medida_venta E
            on C.unidad_medida_venta_id = E.id
            inner join unidad_medida F
            on F.id = E.unidad_medida_id
            where B.estado_id=1 and A.id = " . $idFactura

        );
        // for ($i=0; $i < 15 ; $i++) {
        //     echo($productos[$i]);
        // }

        //dd($productos);

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ["numero_orden" => ""];
        } else {
            $ordenCompra = ["numero_orden" => $ordenCompra->numero_orden];
        }


        if (fmod($importes->total, 1) == 0.0) {
            $flagCentavos = false;
        } else {
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/actaRecepcion', compact('cai', 'cliente', 'importes', 'productos', 'numeroLetras', 'importesConCentavos', 'flagCentavos', 'ordenCompra'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura . ".pdf");
    }
}
