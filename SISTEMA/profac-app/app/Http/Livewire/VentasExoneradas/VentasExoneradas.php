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
        $idCliente = (int) $request->input('idCliente', 0);

        if ($idCliente <= 0) {
            return response()->json([
                'results' => []
            ], 200);
        }

        $codigos = DB::SELECT(
            'select id, codigo as text from codigo_exoneracion where estado_id = 1 and cliente_id = ?',
            [$idCliente]
        );

        return response()->json([
            'results'=>$codigos
        ],200);
    }

    public function guardarVenta(Request $request)
    {

        $factura = null;


        $validator = Validator::make($request->all(), [

            'fecha_vencimiento'    => 'required',
            'numero_venta'         => 'required',
            'subTotalGeneral'      => 'required',
            'isvGeneral'           => 'required',
            'totalGeneral'         => 'required',
            'arregloIdInputs'      => 'required',
            'numeroInputs'         => 'required',
            'seleccionarCliente'   => 'required',
            'nombre_cliente_ventas'=> 'required',
            'tipoPagoVenta'        => 'required',
            'restriccion'          => 'required',
            'tipo_venta_id'        => 'required|integer|between:3,3',
            'codigo'               => 'required',

        ], [
            'codigo.required'               => 'Debe seleccionar el Código de Exoneración.',
            'seleccionarCliente.required'   => 'Debe seleccionar un Cliente.',
            'nombre_cliente_ventas.required'=> 'El nombre del cliente es obligatorio.',
            'tipoPagoVenta.required'        => 'Debe seleccionar el Tipo de Pago.',
            'fecha_vencimiento.required'    => 'La fecha de vencimiento es obligatoria.',
            'subTotalGeneral.required'      => 'El sub-total es obligatorio. Agregue al menos un producto.',
            'totalGeneral.required'         => 'El total es obligatorio. Agregue al menos un producto.',
            'restriccion.required'          => 'El campo restricción es obligatorio.',
            'tipo_venta_id.required'        => 'El tipo de venta no es válido.',
            'tipo_venta_id.between'         => 'El tipo de venta debe ser Exonerada.',
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

        // Si la venta proviene de una prefactura, excluirla del stock reservado
        $prefacturaExcluirId = (int) ($request->prefactura_id ?? 0);

        // En modo editar_factura: sumar de vuelta el stock de la factura original
        $facturaEditAddBackId = 0;
        if (($request->modo ?? '') === 'editar_factura') {
            $flujoIdEdit = (int) ($request->flujo_id ?? 0);
            if ($flujoIdEdit > 0) {
                $histF = DB::table('historico_flujo')
                    ->where('flujo_id', $flujoIdEdit)
                    ->where('tipo_tramite_id', 3)
                    ->whereNotNull('tramite_id')
                    ->where('estado_id', '!=', 7)
                    ->orderByDesc('id')
                    ->value('tramite_id');
                if (!$histF) {
                    // Fallback legacy: factura guardada en tipo_tramite_id=5
                    $histF = DB::table('historico_flujo as hf')
                        ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
                        ->where('hf.flujo_id', $flujoIdEdit)
                        ->where('hf.tipo_tramite_id', 5)
                        ->whereNotNull('hf.tramite_id')
                        ->where('hf.estado_id', '!=', 7)
                        ->orderByDesc('hf.id')
                        ->value('hf.tramite_id');
                }
                $facturaEditAddBackId = $histF ? (int) $histF : 0;
            }
        }

        //comprobar existencia de producto en bodega
        for ($j = 0; $j < count($arrayInputs); $j++) {

            $keyIdSeccion = "idSeccion" . $arrayInputs[$j];
            $keyIdProducto = "idProducto" . $arrayInputs[$j];
            $keyRestaInventario = "restaInventario" . $arrayInputs[$j];
            $keyNombre = "nombre" . $arrayInputs[$j];
            $keyBodega = "bodega" . $arrayInputs[$j];

            $excludePfClause = $prefacturaExcluirId > 0
                ? "AND pf2.id != {$prefacturaExcluirId}"
                : '';

            $addBackFacturaClause = $facturaEditAddBackId > 0
                ? "+ IFNULL((SELECT SUM(vhp_e.cantidad)
                              FROM venta_has_producto vhp_e
                              WHERE vhp_e.factura_id = {$facturaEditAddBackId}
                                AND vhp_e.producto_id = " . (int)$request->$keyIdProducto . "
                                AND vhp_e.seccion_id  = " . (int)$request->$keyIdSeccion . "
                                AND vhp_e.resta_inventario = 1), 0)"
                : '';

            $resultado = DB::selectONE("
                SELECT GREATEST(0,
                    IFNULL((SELECT SUM(rb2.cantidad_disponible) FROM recibido_bodega rb2
                             WHERE rb2.cantidad_disponible > 0
                               AND rb2.producto_id = " . (int)$request->$keyIdProducto . "
                               AND rb2.seccion_id  = " . (int)$request->$keyIdSeccion . "), 0)
                    {$addBackFacturaClause}
                    -
                    IFNULL((SELECT SUM(php2.cantidad)
                             FROM prefactura_has_producto php2
                             INNER JOIN prefactura pf2 ON pf2.id = php2.prefactura_id
                             WHERE pf2.estado = 'activo'
                               {$excludePfClause}
                               AND php2.producto_id = " . (int)$request->$keyIdProducto . "
                               AND php2.seccion_id  = " . (int)$request->$keyIdSeccion . "
                               AND php2.resta_inventario = 1), 0)
                ) AS cantidad_disponoble
            ");

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

            if (!$cai) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Advertencia!',
                    'text' => 'No hay un CAI activo configurado para facturacion.',
                ], 401);
            }

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
            $subTotalFactura = (float) ($request->subTotalGeneral ?? 0);

            if ($request->tipoPagoVenta == 1) {
                $diasCredito = 0;
            } else {
                $dias = DB::SELECTONE("select dias_credito from cliente where id = " . $request->seleccionarCliente);
                $diasCredito = $dias->dias_credito;
            }

            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

            // Obtener datos reales del cliente desde la base de datos basado en cliente_id seleccionado
            $clienteData = DB::table('cliente')
                ->where('id', (int) $request->seleccionarCliente)
                ->select('nombre', 'rtn')
                ->first();

            $validarCAI = new Notificaciones();
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3],"0"),$numeroSecuencia, 3);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $clienteData->nombre ?? $request->nombre_cliente_ventas;
            $factura->rtn = $clienteData->rtn ?? $request->rtn_ventas;
            $factura->sub_total = $subTotalFactura;
            $factura->isv = 0;
            $factura->total = $subTotalFactura;
            $factura->credito = $subTotalFactura;
            $factura->fecha_emision = $request->fecha_emision;
            $factura->fecha_vencimiento = $request->fecha_vencimiento;
            $factura->tipo_pago_id = $request->tipoPagoVenta;
            $factura->dias_credito = $diasCredito;
            $factura->cai_id = $cai->id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->gestor_entrega = $request->gestor_entrega ?: null;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 3; // exonerado
            $factura->estado_factura_id = 1; // se presenta
            $factura->users_id = Auth::user()->id;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $subTotalFactura;
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


            $codigoExoneracion = ModelCodigoExoneracion::find($request->codigo);
            if (!$codigoExoneracion) {
                DB::rollBack();
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Advertencia!',
                    'text' => 'El codigo de exoneracion seleccionado no existe o ya no esta disponible.',
                ], 401);
            }
            $codigoExoneracionTexto = $codigoExoneracion->codigo ?? null;
            $correlativoExoneracion = $codigoExoneracion->corrOrd ?? null;
            $codigoExoneracion->estado_id = 2;
            $codigoExoneracion->save();

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
                $keyidPrecioSeleccionado = 'idPrecioSeleccionado'.$arrayInputs[$i];
                $keyprecioSeleccionado = 'precios'.$arrayInputs[$i];
                $keyprecios_producto_carga_id = 'precios_producto_carga_id'.$arrayInputs[$i];

                $restaInventario = $request->$keyRestaInventario;
                $idSeccion = $request->$keyIdSeccion;
                $idProducto = $request->$keyIdProducto;
                $idUnidadVenta = $request->$keyIdUnidadVenta;
                $ivsProducto = $request->$keyISV;
                $unidad = $request->$keyunidad;

                $idPrecioSeleccionado = $request->$keyidPrecioSeleccionado;
                $precioSeleccionado = $request->$keyprecioSeleccionado;
                $precios_producto_carga_id = $request->$keyprecios_producto_carga_id;

                $precio = $request->$keyPrecio;
                $cantidad = $request->$keyCantidad;
                $subTotal = $request->$keySubTotal;
                $tipoPrecio = ($ivsProducto > 0) ? '2' : '1'; // '2' = gravado, '1' = exento
                $isv = 0;
                $total = $subTotal;
                $ivsProducto = 0;

                //dd($factura);

                $this->restarUnidadesInventario($precios_producto_carga_id, $idPrecioSeleccionado,$precioSeleccionado,$restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad,$arrayInputs[$i], $tipoPrecio);
            };

            if ($request->tipoPagoVenta == 2) { //si el tipo de pago es credito
                $this->restarCreditoCliente($request->seleccionarCliente, $subTotalFactura, $factura->id);
            }

            // dd($this->arrayProductos);
            ModelVentaProducto::insert($this->arrayProductos);
            ModelLogTranslados::insert($this->arrayLogs);


            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

            // Persistir documentos comerciales en flujo si viene de un flujo vinculado
            if (!empty($request->flujo_id)) {
                $docUpdate = array_filter([
                    'numero_orden_compra'  => $request->numero_orden_compra  ?: null,
                    'archivo_orden_compra' => $request->archivo_orden_compra ?: null,
                    'numero_forma_f01'     => $request->numero_forma_f01     ?: null,
                    'archivo_forma_f01'    => $request->archivo_forma_f01    ?: null,
                    // Exoneradas: guardar snapshot en flujo (correlativo y codigo)
                    'numero_exoneracion'   => $request->numero_exoneracion   ?: $correlativoExoneracion,
                    'archivo_exoneracion'  => $request->archivo_exoneracion  ?: $codigoExoneracionTexto,
                ], fn($v) => $v !== null);
                if (!empty($docUpdate)) {
                    $docUpdate['updated_at'] = now();
                    DB::table('flujo')->where('id', (int) $request->flujo_id)->update($docUpdate);
                }
            }

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
                'idFactura' => $factura ? $factura->id : 0,
                'mensajeError'=>$e
            ], 402);
        }
    }

    public function restarUnidadesInventario($precios_producto_carga_id,$idPrecioSeleccionado,$precioSeleccionado,$unidadesRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $indice, $tipoPrecio = '1')
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
                    "tipo_precio" => $tipoPrecio,
                    "idPrecioSeleccionado"=>$idPrecioSeleccionado,
                    "precioSeleccionado"=>$precioSeleccionado,
                    "precios_producto_carga_id" => $precios_producto_carga_id,
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
        COALESCE(F.archivo_exoneracion, E.codigo) as codigo_exoneracion,
        COALESCE(F.numero_exoneracion, E.corrOrd) as correlativoexo,
        A.estado_venta_id,
        users.name as vendedor,
        (select name from users where id = A.users_id ) as facturador,
        (select name from users where id = A.gestor_entrega) as asesor_entrega
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
    left join historico_flujo HF
    on HF.tipo_tramite_id = 3 and HF.tramite_id = A.id
    left join flujo F
    on F.id = HF.flujo_id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        cliente.id as clienteId,
        cliente.nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        factura.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = ".$idFactura);

        $importes = DB::SELECTONE("
        select
         total,
         COALESCE((select sum(round(vhp.sub_total_s * (p.isv / 100), 2)) from venta_has_producto vhp inner join producto p on vhp.producto_id = p.id where p.isv != 0 and vhp.factura_id = ".$idFactura."),0) as isv,
         sub_total,
         FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) > 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '2'))),2) as sub_total_grabado,
         COALESCE(sub_total_excento, 0) as sub_total_excento,
         porc_descuento,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) = 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '1'))),2) as subtotal_excentovale,
         monto_descuento
         from factura
         where id = ".$idFactura);

         $importesConCentavos= DB::SELECTONE("
         select
         FORMAT(total,2) as total,
         FORMAT(COALESCE((select sum(round(vhp.sub_total_s * (p.isv / 100), 2)) from venta_has_producto vhp inner join producto p on vhp.producto_id = p.id where p.isv != 0 and vhp.factura_id = ".$idFactura."),0),2) as isv,
         FORMAT(sub_total,2) as sub_total,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) > 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '2'))),2) as sub_total_grabado,
        FORMAT(COALESCE(sub_total_excento, 0),2) as sub_total_excento,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) = 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '1'))),2) as subtotal_excentovale,
         FORMAT(porc_descuento,2) as porc_descuento,
         FORMAT(monto_descuento,2) as monto_descuento
         from factura where factura.id = ".$idFactura);

                // En facturas exoneradas el impuesto sobre venta debe ser siempre 0.
                $importes->isv = 0;
                $importes->total = round((float) $importes->sub_total, 2);

                $importesConCentavos->isv = '0.00';
                $importesConCentavos->total = number_format(
                    (float) str_replace(',', '', $importesConCentavos->sub_total),
                    2,
                    '.',
                    ','
                );

       $productos = DB::SELECT("
            select
                    B.producto_id as codigo,
                    concat(C.nombre) as descripcion,
                    UPPER(J.nombre) as medida,
                if(((DATE(A.fecha_emision) < '2026-06-07' and MIN(COALESCE(B.isv,0)) = 0) or (DATE(A.fecha_emision) >= '2026-06-07' and MIN(B.tipo_precio) = '1')), 'SI' , 'NO' ) as excento,
                if(B.seccion_id = 0, 'N/A',H.nombre) as bodega,
                if(B.seccion_id = 0, 'N/A',REPLACE(REPLACE(F.descripcion,'Seccion',''),' ', '')) as seccion,
                    FORMAT(B.precio_unidad,2) as precio,
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
                group by codigo, descripcion, medida, bodega, seccion, precio, B.indice
                order by B.indice asc"




        );

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        $flujoFacturaId = DB::table('historico_flujo')
            ->where('tipo_tramite_id', 3)
            ->where('tramite_id', $idFactura)
            ->value('flujo_id');

        $flujoDocData = $flujoFacturaId
            ? DB::table('flujo')->where('id', $flujoFacturaId)->first([
                'numero_orden_compra',
                'numero_forma_f01',
                'numero_exoneracion',
                'archivo_exoneracion',
            ])
            : null;

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ['numero_orden' => ($flujoDocData->numero_orden_compra ?? null) ?: 'N/A'];
        } else {
            $ordenCompra = ['numero_orden' => $ordenCompra->numero_orden];
        }

        $formaF01 = ($flujoDocData->numero_forma_f01 ?? null) ?: null;
        $correlativoExonerado = ($flujoDocData->numero_exoneracion ?? null) ?: ($cai->correlativoexo ?? null);
        $constanciaExonerado = ($flujoDocData->archivo_exoneracion ?? null) ?: ($cai->codigo_exoneracion ?? null);

        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }




        $formatter = new NumeroALetras();
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $esExonerada = true;

        $pdf = PDF::loadView('/pdf/factura', compact(
            'cai',
            'cliente',
            'importes',
            'productos',
            'numeroLetras',
            'importesConCentavos',
            'flagCentavos',
            'ordenCompra',
            'formaF01',
            'correlativoExonerado',
            'constanciaExonerado',
            'esExonerada'
        ))->setPaper('letter');

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
        COALESCE(F.archivo_exoneracion, E.codigo) as codigo_exoneracion,
        COALESCE(F.numero_exoneracion, E.corrOrd) as correlativoexo,
        A.estado_venta_id,
        users.name as vendedor,
        (select name from users where id = A.users_id ) as facturador,
        (select name from users where id = A.gestor_entrega) as asesor_entrega
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
    left join historico_flujo HF
    on HF.tipo_tramite_id = 3 and HF.tramite_id = A.id
    left join flujo F
    on F.id = HF.flujo_id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        cliente.id as clienteId,
        cliente.nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        factura.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = ".$idFactura);

       $importes = DB::SELECTONE("
       select
        total,
        COALESCE((select sum(round(vhp.sub_total_s * (p.isv / 100), 2)) from venta_has_producto vhp inner join producto p on vhp.producto_id = p.id where p.isv != 0 and vhp.factura_id = ".$idFactura."),0) as isv,
        sub_total,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) > 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '2'))),2) as sub_total_grabado,
        COALESCE(sub_total_excento, 0) as sub_total_excento,
        porc_descuento,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) = 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '1'))),2) as subtotal_excentovale,
        monto_descuento
        from factura
        where id = ".$idFactura);

        $importesConCentavos= DB::SELECTONE("
        select
        FORMAT(total,2) as total,
        FORMAT(COALESCE((select sum(round(vhp.sub_total_s * (p.isv / 100), 2)) from venta_has_producto vhp inner join producto p on vhp.producto_id = p.id where p.isv != 0 and vhp.factura_id = ".$idFactura."),0),2) as isv,
        FORMAT(sub_total,2) as sub_total,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) > 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '2'))),2) as sub_total_grabado,
        FORMAT(COALESCE(sub_total_excento, 0),2) as sub_total_excento,
        FORMAT(porc_descuento,2) as porc_descuento,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) = 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '1'))),2) as subtotal_excentovale,
        FORMAT(monto_descuento,2) as monto_descuento
        from factura where factura.id = ".$idFactura);

        // En facturas exoneradas el impuesto sobre venta debe ser siempre 0.
        $importes->isv = 0;
        $importes->total = round((float) $importes->sub_total, 2);

        $importesConCentavos->isv = '0.00';
        $importesConCentavos->total = number_format(
            (float) str_replace(',', '', $importesConCentavos->sub_total),
            2,
            '.',
            ','
        );

        $productos = DB::SELECT("
            select
                    B.producto_id as codigo,
                    concat(C.nombre) as descripcion,
                    UPPER(J.nombre) as medida,
                if(((DATE(A.fecha_emision) < '2026-06-07' and MIN(COALESCE(B.isv,0)) = 0) or (DATE(A.fecha_emision) >= '2026-06-07' and MIN(B.tipo_precio) = '1')), 'SI' , 'NO' ) as excento,
                if(B.seccion_id = 0, 'N/A',H.nombre) as bodega,
                if(B.seccion_id = 0, 'N/A',REPLACE(REPLACE(F.descripcion,'Seccion',''),' ', '')) as seccion,
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
                where A.id=".$idFactura."
                group by codigo, descripcion, medida, bodega, seccion, precio, B.indice
                order by B.indice asc

                "




        );

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        $flujoFacturaId = DB::table('historico_flujo')
            ->where('tipo_tramite_id', 3)
            ->where('tramite_id', $idFactura)
            ->value('flujo_id');

        $flujoDocData = $flujoFacturaId
            ? DB::table('flujo')->where('id', $flujoFacturaId)->first([
                'numero_orden_compra',
                'numero_forma_f01',
                'numero_exoneracion',
                'archivo_exoneracion',
            ])
            : null;

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ['numero_orden' => ($flujoDocData->numero_orden_compra ?? null) ?: 'N/A'];
        } else {
            $ordenCompra = ['numero_orden' => $ordenCompra->numero_orden];
        }

        $formaF01 = ($flujoDocData->numero_forma_f01 ?? null) ?: null;
        $correlativoExonerado = ($flujoDocData->numero_exoneracion ?? null) ?: ($cai->correlativoexo ?? null);
        $constanciaExonerado = ($flujoDocData->archivo_exoneracion ?? null) ?: ($cai->codigo_exoneracion ?? null);

        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }




        $formatter = new NumeroALetras();
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');




        $esExonerada = true;

        $pdf = PDF::loadView('/pdf/facturaCopia', compact(
            'cai',
            'cliente',
            'importes',
            'productos',
            'numeroLetras',
            'importesConCentavos',
            'flagCentavos',
            'ordenCompra',
            'formaF01',
            'correlativoExonerado',
            'constanciaExonerado',
            'esExonerada'
        ))->setPaper('letter');

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
        COALESCE(F.archivo_exoneracion, E.codigo) as codigo_exoneracion,
        COALESCE(F.numero_exoneracion, E.corrOrd) as correlativoexo,
        A.estado_venta_id,
        users.name as vendedor,
        (select name from users where id = A.users_id ) as facturador
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
    left join historico_flujo HF
    on HF.tipo_tramite_id = 3 and HF.tramite_id = A.id
    left join flujo F
    on F.id = HF.flujo_id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        cliente.id as clienteId,
        cliente.nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        factura.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = ".$idFactura);

        $importes = DB::SELECTONE("
        select
         total,
         isv,
         sub_total,
         FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) > 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '2'))),2) as sub_total_grabado,
         COALESCE(sub_total_excento, 0) as sub_total_excento,
         porc_descuento,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) = 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '1'))),2) as subtotal_excentovale,
         monto_descuento
         from factura
         where id = ".$idFactura);

         $importesConCentavos= DB::SELECTONE("
         select
         FORMAT(total,2) as total,
         FORMAT(isv,2) as isv,
         FORMAT(sub_total,2) as sub_total,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) > 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '2'))),2) as sub_total_grabado,
        FORMAT(COALESCE(sub_total_excento, 0),2) as sub_total_excento,
        FORMAT((select sum(vhp.sub_total_s) from venta_has_producto vhp where vhp.factura_id = ".$idFactura." and ((DATE(factura.fecha_emision) < '2026-06-07' and COALESCE(vhp.isv,0) = 0) or (DATE(factura.fecha_emision) >= '2026-06-07' and vhp.tipo_precio = '1'))),2) as subtotal_excentovale,
         FORMAT(porc_descuento,2) as porc_descuento,
         FORMAT(monto_descuento,2) as monto_descuento
         from factura where factura.id = ".$idFactura);

        $productos = DB::SELECT("
            select
                    B.producto_id as codigo,
                    concat(C.nombre) as descripcion,
                    UPPER(J.nombre) as medida,
                if(((DATE(A.fecha_emision) < '2026-06-07' and MIN(COALESCE(B.isv,0)) = 0) or (DATE(A.fecha_emision) >= '2026-06-07' and MIN(B.tipo_precio) = '1')), 'SI' , 'NO' ) as excento,
                if(B.seccion_id = 0, 'N/A',H.nombre) as bodega,
                if(B.seccion_id = 0, 'N/A',REPLACE(REPLACE(F.descripcion,'Seccion',''),' ', '')) as seccion,
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
                where A.id=".$idFactura."
                group by codigo, descripcion, medida, bodega, seccion, precio, B.indice
                order by B.indice asc

                 "




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


