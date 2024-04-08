<?php

namespace App\Http\Livewire\VentasExoneradas;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use Luecano\NumeroALetras\NumeroALetras;
use PDF;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\ModelCodigoExoneracion;
use App\Http\Controllers\CAI\Notificaciones;

class VentasExoneradas extends Component
{


    public $arrayProductos = [];
    public $arrayLogs = [];

    public function render()
    {
        return view('livewire.ventas-exoneradas.ventas-exoneradas');
    }

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
                    and id<>1
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                        ");

            }else{
                $listaClientes = DB::SELECT("
                select
                    id,
                    nombre as text
                from cliente
                    where estado_cliente_id = 1
                    and id<>1
                    and vendedor =" . Auth::user()->id . "
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
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

    public function obtenerCodigoExoneracion(Request $request){
        $codigos = DB::SELECT("select id, codigo as text from codigo_exoneracion where estado_id = 1 and cliente_id = ".$request->idCliente);

        return response()->json([
            'results'=>$codigos
        ],200);
    }

    public function guardarVenta(Request $request)
    {


        $validator = Validator::make($request->all(), [

            'fecha_vencimiento' => 'required',
            'numero_venta' => 'required',
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',
            'arregloIdInputs' => 'required',
            'numeroInputs' => 'required',
            'seleccionarCliente' => 'required',
            'nombre_cliente_ventas' => 'required',
            'tipoPagoVenta' => 'required',
            'bodega' => 'required',
            'restriccion' => 'required',
            'tipo_venta_id'=>'required|integer|between:3,3',
            'codigo'=>'required'



        ]);




        if ($validator->fails()) {
            return response()->json([
                'icon'=>'error',
                'title'=>'Error!',
                'text'=>'Ha ingresado datos invalidos, por favor revisar que todos los campos esten correctos.',
                'mensaje' => 'Ha ocurrido un error al crear la compra.',
                'errors' => $validator->errors()
            ], 406);
        }

        /* if ($request->restriccion == 1) {
            $facturaVencida = $this->comprobarFacturaVencida($request->seleccionarCliente);

            if ($facturaVencida) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Advertencia!',
                    'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', cuenta con facturas vencidas. Por el momento no se puede emitir factura a este cliente.',

                ], 401);
            }
        } */



        /*if ($request->tipoPagoVenta == 2) {
            $comprobarCredito = $this->comprobarCreditoCliente($request->seleccionarCliente, $request->totalGeneral);

            if ($comprobarCredito) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Advertencia!',
                    'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', no cuenta con cr��dito suficiente . Por el momento no se puede emitir factura a este cliente.',

                ], 401);
            }
        }*/


        $arrayTemporal = $request->arregloIdInputs;
        $arrayInputs = explode(',', $arrayTemporal);
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
                $mensaje = $mensaje . "Unidades insuficientes para el producto: <b>" . $request->$keyNombre . "</b> en la bodega con secci��n :<b>" . $request->$keyBodega . "</b><br><br>";
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
                    "text" => "La factura no puede proceder, debido que ha alcanzadado el n��mero maximo de facturacion otorgado.",
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
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3],"0"),$numeroSecuencia, 3);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn = $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
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
            $factura->tipo_venta_id = 3; // exonerado
            $factura->estado_factura_id = 1; // se presenta
            $factura->users_id = Auth::user()->id;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->codigo_exoneracion_id = $request->codigo;
            $factura->estado_editar = 1;
            $factura->sub_total_grabado = 0;
            $factura->numero_orden_compra_id=$request->ordenCompra;
            $factura->comentario=$request->nota_comen;
            $factura->porc_descuento =$request->porDescuento;
            $factura->monto_descuento=$request->porDescuentoCalculado;
            $factura->save();

            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->numero_actual = $numeroSecuencia + 1;
            $caiUpdated->cantidad_no_utilizada = $cai->cantidad_otorgada - $numeroSecuencia;
            $caiUpdated->save();

            //Tabla de listado

            // DB::INSERT("INSERT INTO listado(
            //          numero, secuencia, numero_inicial, numero_final, cantidad_otorgada, cai_id, created_at, updated_at, eliminado) VALUES
            //         ('" . $numeroCAI . "','" . $numeroSecuencia . "','" . $cai->numero_inicial . "','" . $cai->numero_final . "','" . $cai->cantidad_otorgada . "','" . $cai->id . "','" . NOW() . "','" . NOW() . "',0)");




            $codigoExoneracion = ModelCodigoExoneracion::find($request->codigo);
            $codigoExoneracion->estado_id = 2;
            $codigoExoneracion->save();

            // //dd( $guardarCompra);



            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */
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

                //dd($factura);

                $this->restarUnidadesInventario($restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad,$arrayInputs[$i]);
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
                    <a href="/exonerado/factura/'. $factura->id . '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-invoice"></i> Imprimir Factura</a>
                    <!-- <a href="/venta/cobro/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Realizar Pago</a> -->
                    <a href="/crear/vale/lista/espera/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-list-check"></i> Crear Vale Tipo: 2</a>
                    <a href="/detalle/venta/' . $factura->id . '" target="_blank" class="btn btn-sm btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Detalle de Factura</a>
                </div>',
                'title' => 'Exito!',
                'idFactura' => $factura->id,
                'numeroVenta' => $numeroVenta->numero

            ], 200,array('Content-Type'=>'application/json; charset=utf-8' ));
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'error' => 'Ha ocurrido un error al realizar la factura.',
                'icon' => "error",
                'text' => 'Ha ocurrido un error.',
                'title' => 'Error!',
                'idFactura' => $factura->id,
                'mensajeError'=>$e
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
        $logCredito->descripcion = 'Reduccion  de credito por factura.';
        $logCredito->monto = $totalFactura;
        $logCredito->factura_id = $idFactura;
        $logCredito->cliente_id = $idCliente;
        $logCredito->users_id = Auth::user()->id;
        $logCredito->save();

        return true;
    }

    public function imprimirFacturaExonerada($idFactura)
    {

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
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
        D.id as factura,
        E.codigo as codigo_exoneracion,
        E.corrOrd as correlativoexo,
        A.estado_venta_id
       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       inner join codigo_exoneracion E
       on A.codigo_exoneracion_id = E.id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        cliente.nombre,
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
        where factura.id = ".$idFactura);

        $importes = DB::SELECTONE("
        select
         total,
         isv,
         sub_total,
         porc_descuento,
         monto_descuento
         from factura
         where id = ".$idFactura);

         $importesConCentavos= DB::SELECTONE("
         select
         total as total,
         isv as isv,
         sub_total as sub_total,
         porc_descuento as porc_descuento,
         monto_descuento as monto_descuento
         from factura where factura.id = ".$idFactura);

       $productos = DB::SELECT("
            select
                    B.producto_id as codigo,
                    concat(C.nombre) as descripcion,
                    UPPER(J.nombre) as medida,
                    H.nombre as bodega,
                    F.descripcion as seccion,
                    (B.sub_total/B.cantidad) as precio,
                    sum(B.cantidad_s) as cantidad,
                    sum(B.sub_total_s) as importe

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
                where A.id=".$idFactura."
                group by codigo, descripcion, medida, bodega, seccion, precio

                union

                select
                    D.id,
                    D.nombre as descripcion,
                    F.nombre as medida,
                    'Pendiente',
                    'Pendiente',
                    C.precio as precio,
                    C.cantidad as cantidad,
                    C.sub_total as sub_total
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
                where B.estado_id=1 and A.id = ".$idFactura




        );

        $ordenCompraExiste = DB::SELECTONE("
        select
        count(*) as 'existe'
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        if ($ordenCompraExiste->existe > 0) {
            $ordenCompra = DB::SELECTONE("
            select
            B.numero_orden
            from factura A
            inner join numero_orden_compra B
            on A.numero_orden_compra_id = B.id
            where A.id =" . $idFactura);



        }else{
            $ordenCompra = DB::SELECTONE("
            select
            'N/A' as numero_orden
            from factura
            where id =" . $idFactura);
        }

        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }




        $formatter = new NumeroALetras();
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/factura-exoneracion', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos', 'ordenCompra'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura.".pdf");


    }

    public function imprimirFacturaExoneradaCopia($idFactura)
    {

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
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
        D.id as factura,
        E.codigo as codigo_exoneracion,
        E.corrOrd as correlativoexo,
        A.estado_venta_id
       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       inner join codigo_exoneracion E
       on A.codigo_exoneracion_id = E.id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        cliente.nombre,
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
        where factura.id = ".$idFactura);

       $importes = DB::SELECTONE("
       select
        total,
        isv,
        sub_total,
        porc_descuento,
        monto_descuento
        from factura
        where id = ".$idFactura);

        $importesConCentavos= DB::SELECTONE("
        select
        total as total,
        isv as isv,
        sub_total as sub_total,
        porc_descuento as porc_descuento,
        monto_descuento as monto_descuento
        from factura where factura.id = ".$idFactura);

        $productos = DB::SELECT("
            select
                    B.producto_id as codigo,
                    concat(C.nombre) as descripcion,
                    UPPER(J.nombre) as medida,
                    H.nombre as bodega,
                    F.descripcion as seccion,
                    B.precio_unidad as precio,
                    sum(B.cantidad_s) as cantidad,
                    sum(B.sub_total_s) as importe

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
                where A.id=".$idFactura."
                group by codigo, descripcion, medida, bodega, seccion, precio

                union

                select
                    D.id,
                    D.nombre as descripcion,
                    F.nombre as medida,
                    'Pendiente',
                    'Pendiente',
                    C.precio as precio,
                    C.cantidad as cantidad,
                    C.sub_total as sub_total
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
                where B.estado_id=1 and A.id = ".$idFactura




        );

        $ordenCompraExiste = DB::SELECTONE("
        select
        count(*) as 'existe'
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        if ($ordenCompraExiste->existe > 0) {
            $ordenCompra = DB::SELECTONE("
            select
            B.numero_orden
            from factura A
            inner join numero_orden_compra B
            on A.numero_orden_compra_id = B.id
            where A.id =" . $idFactura);



        }else{
            $ordenCompra = DB::SELECTONE("
            select
            'N/A' as numero_orden
            from factura
            where id =" . $idFactura);
        }

        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }




        $formatter = new NumeroALetras();
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');




        $pdf = PDF::loadView('/pdf/facturacopia-exoneracion', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos', 'ordenCompra'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura.".pdf");


    }


    public function imprimirActarepExonerada($idFactura)
    {

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
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
        D.id as factura,
        E.codigo as codigo_exoneracion,
        E.corrOrd as correlativoexo,
        A.estado_venta_id
       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       inner join codigo_exoneracion E
       on A.codigo_exoneracion_id = E.id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        cliente.nombre,
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
        where factura.id = ".$idFactura);

        $importes = DB::SELECTONE("
        select
         total,
         isv,
         sub_total,
         porc_descuento,
         monto_descuento
         from factura
         where id = ".$idFactura);

         $importesConCentavos= DB::SELECTONE("
         select
         total as total,
         isv as isv,
         sub_total as sub_total,
         porc_descuento as porc_descuento,
         monto_descuento as monto_descuento
         from factura where factura.id = ".$idFactura);

        $productos = DB::SELECT("
            select
                    B.producto_id as codigo,
                    concat(C.nombre) as descripcion,
                    UPPER(J.nombre) as medida,
                    H.nombre as bodega,
                    F.descripcion as seccion,
                    B.precio_unidad as precio,
                    sum(B.cantidad_s) as cantidad,
                    sum(B.sub_total_s) as importe

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
                where A.id=".$idFactura."
                group by codigo, descripcion, medida, bodega, seccion, precio

                union

                select
                    D.id,
                    D.nombre as descripcion,
                    F.nombre as medida,
                    'Pendiente',
                    'Pendiente',
                    C.precio as precio,
                    C.cantidad as cantidad,
                    C.sub_total as sub_total
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
                where B.estado_id=1 and A.id = ".$idFactura




        );



        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }




        $formatter = new NumeroALetras();
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/actaRecepcion-exoneracion', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura.".pdf");


    }
}

