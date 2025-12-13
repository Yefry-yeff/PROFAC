<?php

namespace App\Http\Livewire\ComprovanteEntrega;

use App\Http\Livewire\Ventas\FacturacionCorporativa;

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

use App\Models\ModelComprovanteProducto;
use App\Models\ModelLogTranslados; 
use App\Models\ModelVentaProducto;

class FacturarComprobante extends FacturacionCorporativa
{

    public $idComprobante;
    public $arrayInputs = [];
    public $numeroInputs =0;
    public function mount($id)
    {

        $this->idComprobante = $id;
    }

    public function render()
    {
        $urlFactura ="";
        $idComprobante = $this->idComprobante;



        $comprobante = DB::SELECTONE("
        select 
        A.id,
        A.numero_comprovante,
        A.nombre_cliente,
        A.RTN,
        A.fecha_emision,
        A.fecha_vencimineto,
        A.sub_total,       
        A.sub_total_grabado,
        A.sub_total_excento,
        A.isv,
        A.total,
        A.cliente_id,
        A.tipo_venta_id,
        A.tipo_venta_id,
        A.vendedor,
        A.users_id,
        A.porc_descuento,
        A.monto_descuento,        
        B.dias_credito,
        B.tipo_cliente_id,
        A.comentario 
        from comprovante_entrega A 
        inner join cliente B on
        A.cliente_id = B.id
        where A.id =".$idComprobante
         
        );


        $htmlProductos =  $this->generarHTML($idComprobante);
        
        if($comprobante->tipo_cliente_id == 1){
            $urlFactura ="/orden/entrega/guardar/factura";
            //$urlFactura ="/ventas/corporativo/guardar";
        }else{
            $urlFactura ="/orden/entrega/estatal/factura";
            //$urlFactura ="/ventas/estatal/guardar";
        }

        $arrayInputs = $this->arrayInputs;
        $numeroInputs =$this->numeroInputs;

        return view('livewire.comprovante-entrega.facturar-comprobante', compact('idComprobante','comprobante','htmlProductos','urlFactura','arrayInputs','numeroInputs'));
    }

    public function generarHTML($idComprobant){
     
        $html = '';
        $htmlSelectUnidadVenta = '';
        $i = 1;
       

        $productos = DB::SELECT("
        select 
        B.producto_id,
        B.comprovante_id,
        concat(B.producto_id,' - ', C.nombre ) as nombre_producto,      
        concat(bodega.nombre,' - ',seccion.descripcion ) as nombre_bodega,
        bodega.id as bodega_id,
        B.seccion_id as seccion_id,
        B.precio_unidad,
        concat(E.nombre,' - ',D.unidad_venta  ) as unidad,
        sum(B.cantidad_para_entregar) as cantidad,   
        B.unidad_medida_venta_id,
        C.isv as isvTblProducto,
        B.resta_inventario_total, 
        B.monto_descProducto,
        B.precio_unidad * sum(B.cantidad_para_entregar)  as sub_total,
        B.precio_unidad * sum(B.cantidad_para_entregar) * ( C.isv/100) as isv,
        (B.precio_unidad * sum(B.cantidad_para_entregar))  + (B.precio_unidad * sum(B.cantidad_para_entregar) * ( C.isv/100)) as total       
        
        from comprovante_entrega A
        inner join comprovante_has_producto B
        on A.id = B.comprovante_id
        inner join producto C
        on B.producto_id = C.id
        inner join seccion 
        on seccion.id = B.seccion_id
        inner join segmento
        on seccion.segmento_id = segmento.id
        inner join bodega 
        on bodega.id = segmento.bodega_id
        inner join unidad_medida_venta D
        on D.id = B.unidad_medida_venta_id
        inner join unidad_medida E
        on E.id = D.unidad_medida_id
        where A.id = ".$idComprobant."
        and B.cantidad_para_entregar <> 0
        group by producto_id, comprovante_id, nombre_producto, nombre_bodega, bodega_id, seccion_id, precio_unidad, unidad, cantidad, unidad_medida_venta_id, isvTblProducto,  B.resta_inventario_total, B.monto_descProducto
        ");

     
        foreach ($productos as $producto) {

            array_push($this->arrayInputs, $i);
            $this->numeroInputs +=  $i;

            $unidadesVenta = DB::SELECT(
                "
                select 
                A.unidad_venta as unidades,
                A.id as idUnidadVenta,
                concat(B.nombre,'-',A.unidad_venta ) as nombre
                from unidad_medida_venta A 
                inner join unidad_medida B
                on A.unidad_medida_id = B.id
                where A.producto_id = " . $producto->producto_id
                    );

            foreach ($unidadesVenta as $unidad) {

                if ($producto->unidad_medida_venta_id == $unidad->idUnidadVenta) {
                    $htmlSelectUnidadVenta =$htmlSelectUnidadVenta. '<option selected value="' . $unidad->unidades . '" data-id="' . $unidad->idUnidadVenta . '">' . $unidad->nombre . '</option>';
                } 
            }



            $html = $html. 
                '<div id="'.$i.'" class="row no-gutters">
                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                        <div class="d-flex">

                            <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(' . $i . ')"><i
                                    class="fa-regular fa-rectangle-xmark"></i>
                            </button>

                            <input id="idProducto' . $i . '" name="idProducto' . $i . '" type="hidden" value="' . $producto->producto_id . '">

                            <div style="width:100%">
                                <label for="nombre' . $i . '" class="sr-only">Nombre del producto</label>
                                <input type="text" placeholder="Nombre del producto" id="nombre' . $i . '"
                                    name="nombre' . $i . '" class="form-control" 
                                    data-parsley-required "
                                    autocomplete="off"
                                    readonly 
                                    value="' . $producto->nombre_producto . '"
                                    
                                    >
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="" class="sr-only">cantidad</label>
                        <input type="text" value="' . $producto->nombre_bodega . '" placeholder="bodega-seccion" id="bodega' . $i . '"
                            name="bodega' . $i . '" class="form-control" 
                            autocomplete="off"  readonly  >
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="precio' . $i . '" class="sr-only">Precio</label>
                        <input value="'.$producto->precio_unidad.'" type="number" placeholder="Precio Unidad" id="precio' . $i . '"
                            name="precio' . $i . '" class="form-control"  data-parsley-required step="any"
                            autocomplete="off" min="' . $producto->precio_unidad . '" onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isv . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')" readonly>
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="cantidad' . $i . '" class="sr-only">cantidad</label>
                        <input value="'.$producto->cantidad.'" max="'.$producto->cantidad.'" min="1" type="number" placeholder="Cantidad" id="cantidad' . $i . '"
                            name="cantidad' . $i . '" class="form-control" min="0" data-parsley-required
                            autocomplete="off" onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')">
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="" class="sr-only">unidad</label>
                        <select class="form-control" name="unidad' . $i . '" id="unidad' . $i . '"
                            data-parsley-required style="height:35.7px;" 
                            onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')" readonly>
                                    ' . $htmlSelectUnidadVenta . ' 
                        </select> 
                    
                        
                    </div>


                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                    <label for="subTotalMostrar' . $i . '" class="sr-only">Sub Total</label>
                    <input type="text" placeholder="Sub total producto" id="subTotalMostrar' . $i . '"
                        value="' . $producto->sub_total . '"
                        name="subTotalMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >

                    <input id="subTotal' . $i . '" name="subTotal' . $i . '" type="hidden" value="' . $producto->sub_total . '" required>
                </div>



                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                    <label for="isvProductoMostrar' . $i . '" class="sr-only">ISV</label>
                    <input type="text" value="' . $producto->isv . '" placeholder="ISV" id="isvProductoMostrar' . $i . '"
                        name="isvProductoMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >
                        
                        <input id="isvProducto' . $i . '" name="isvProducto' . $i . '" type="hidden" value="' . $producto->isv . '" required>
                        <input type="hidden" id="acumuladoDescuento'. $i .'" name="acumuladoDescuento'. $i .'" value="' . $producto->monto_descProducto . '">
                </div>


                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                    <label for="totalMostrar' . $i . '" class="sr-only">Total</label>
                    <input type="text"  value="' . $producto->total . '" placeholder="Total del producto" id="totalMostrar' . $i . '"
                        name="totalMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >

                        <input id="total' . $i . '" name="total' . $i . '" type="hidden"  value="' . $producto->total . '" required>


                </div>


                    <input id="idBodega'.$i.'" name="idBodega' . $i . '" type="hidden" value="'.$producto->bodega_id.'">
                    <input id="idSeccion' . $i . '" name="idSeccion' . $i . '" type="hidden" value="' . $producto->seccion_id . '">
                    <input id="restaInventario' . $i . '" name="restaInventario' . $i . '" type="hidden" value="' . $producto->resta_inventario_total . '">
                    <input id="isv' . $i . '" name="isv' . $i . '" type="hidden" value="' . $producto->isvTblProducto . '">  
                                     

                    </div>';
            $htmlSelectUnidadVenta='';        
            $i++;
        }

        return  $html;


    }

    public function facturarComprobante(Request $request){
     
    }


    public function facturarComprobanteCoorporativo(Request $request){
      try {

        $validator = Validator::make($request->all(), [

            'fecha_vencimiento' => 'required',                
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',
            'arregloIdInputs' => 'required',
            'numeroInputs' => 'required',
            'seleccionarCliente' => 'required',
            'nombre_cliente_ventas' => 'required',
            'tipoPagoVenta' => 'required',    
            'restriccion' => 'required',
            'vendedor'=>'required'



        ]);

         //dd($request->all());

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
            $facturaVencida = $this->comprobarFacturaVencida($request->seleccionarCliente);

            if ($facturaVencida) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Advertencia!',
                    'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', cuenta con facturas vencidas. Por el momento no se puede emitir factura a este cliente.',

                ], 401);
            }
            
        }

        if($request->tipoPagoVenta == 2){
            $comprobarCredito = $this->comprobarCreditoCliente($request->seleccionarCliente,$request->totalGeneral);

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

        //comprobar existencia de producto en el comprobante de entrega
        for ($j = 0; $j < count($arrayInputs); $j++) {

          
            $keyIdProducto = "idProducto" . $arrayInputs[$j];
            $keyRestaInventario = "restaInventario" . $arrayInputs[$j];
            $keyNombre = "nombre" . $arrayInputs[$j];
          

            $resultado = DB::selectONE("
            select 
            if(sum(cantidad_para_entregar) is null,0,sum(cantidad_para_entregar)) as cantidad_disponoble
            from comprovante_has_producto
            where cantidad_para_entregar <> 0
            and producto_id = ". $request->$keyIdProducto."
            and comprovante_id = ".$request->idComprobante."
            ");

            if ($request->$keyRestaInventario > $resultado->cantidad_disponoble) {
                $mensaje = $mensaje . "Unidades insuficientes para el producto: <b>" .$request->$keyIdProducto ."-".$request->$keyNombre . "</b> en el comprobante de entrega.<br><br>";
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
        $estado = 0;   
        if ($flagEstado->estado_encendido == 1) {
            $estado = 1;
        } else {
            $estado = 2;
        }

        DB::beginTransaction();




        if ($estado == 1) 
        {
            //presenta

            $cai = DB::SELECTONE("select
            id,
            numero_inicial,
            numero_final,
            cantidad_otorgada,
            numero_actual
            from cai 
            where tipo_documento_fiscal_id = 1 and estado_id = 1");

            if($cai->numero_actual > $cai->cantidad_otorgada){

                return response()->json([
                    "title" => "Advertencia",
                    "icon" => "warning",
                    "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 1.",
                ], 401);

            }
        

            $numeroSecuencia = $cai->numero_actual;
            $arrayCai = explode('-',$cai->numero_final);          
            $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
            $numeroCAI = $arrayCai[0].'-'.$arrayCai[1].'-'.$arrayCai[2].'-'.$cuartoSegmentoCAI;         

            $montoComision = $request->totalGeneral*0.5;

            if($request->tipoPagoVenta==1){
                $diasCredito = 0;
            }else{
                $dias = DB::SELECTONE("select dias_credito from cliente where id = ".$request->seleccionarCliente);
                $diasCredito = $dias->dias_credito;
            }

            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");
            
            $factura = new ModelFactura;    
            $factura->numero_factura = $numeroVenta->numero;       
            $factura->cai=$numeroCAI; 
            $factura->numero_secuencia_cai=$numeroSecuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn=$request->rtn_ventas;
            $factura->sub_total=$request->subTotalGeneral;
            $factura->isv=$request->isvGeneral;
            $factura->total=$request->totalGeneral;
            $factura->credito=$request->totalGeneral;
            $factura->fecha_emision=$request->fecha_emision;
            $factura->fecha_vencimiento=$request->fecha_vencimiento;                    
            $factura->tipo_pago_id=$request->tipoPagoVenta;
            $factura->dias_credito=$diasCredito;
            $factura->cai_id=$cai->id;
            $factura->estado_venta_id=1;
            $factura->cliente_id=$request->seleccionarCliente;
            $factura->vendedor=$request->vendedor;
            $factura->monto_comision=$montoComision;
            $factura->tipo_venta_id=2;// estatal
            $factura->estado_factura_id=1; // se presenta     
            $factura->users_id = Auth::user()->id;              
            $factura->comision_estado_pagado=0;
            $factura->pendiente_cobro=$request->totalGeneral;
            $factura->estado_editar = 1;              
            $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            $factura->comprovante_entrega_id = $request->idComprobante;
            $factura->save();

            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->numero_actual=$numeroSecuencia+1;
            $caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - $numeroSecuencia;
            $caiUpdated->save();


        } 

        else 

        {
            // alterna
            // $lista = DB::SELECT("select id, numero from listado where eliminado = 0");
            // $espera = DB::SELECT("select id from enumeracion where eliminado = 0");

            // if (!empty($lista)) {

            //     $factura = $this->metodoLista($request);
            // } else if (!empty($espera)) {

            //     $factura = $this->enumerar($request);
            // } else {

            //     //$factura = $this->alternar($request);
            //     $factura = $this->guardarVentaND($request);
            // }

            $factura = $this->guardarVentaND($request);
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
            $comprobanteId = $request->idComprobante;

            //dd($factura);

            $this->restarUnidadesComprovanteEntrega($comprobanteId, $restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad);

                     
        };

        if($request->tipoPagoVenta==2 ){//si el tipo de pago es credito
            $this->restarCreditoCliente($request->seleccionarCliente,$request->totalGeneral,$factura->id);
        }

        

        // dd($this->arrayProductos);
        ModelVentaProducto::insert($this->arrayProductos);
        ModelLogTranslados::insert($this->arrayLogs);


    

        DB::commit();
        return response()->json([
            'icon' => "success",
            'text' => '
            <div class="d-flex justify-content-between">
                <a href="/factura/cooporativo/' . $factura->id . '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-invoice"></i> Imprimir Factura</a>
                <a href="/venta/cobro/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Realizar Pago</a>
                <a href="/detalle/venta/' . $factura->id . '" target="_blank" class="btn btn-sm btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Detalle de Factura</a>
            </div>',
            'title' => 'Exito!',
            'idFactura' => $factura->id,
           

        ], 200);
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



    public function restarUnidadesComprovanteEntrega($idComprobante, $cantidadRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad)
    {
     

            $precioUnidad = $subTotal / $cantidadRestarInv;

            $unidadesRestar = $cantidadRestarInv;
            $registroResta = 0;
            while (!($unidadesRestar <= 0)) {

                $unidadesDisponibles = DB::SELECTONE("
                select 
                lote_id,
                comprovante_id,
                producto_id,
                cantidad_para_entregar 
                from comprovante_has_producto
                where
                cantidad_para_entregar <> 0 and
                producto_id = ".$idProducto." and 
                comprovante_id = ".$idComprobante." and     
                seccion_id = ".$idSeccion."            
                order by cantidad_s asc
                limit 1                
                ");


                if ($unidadesDisponibles->cantidad_para_entregar == $unidadesRestar) {

                    $diferencia = $unidadesDisponibles->cantidad_para_entregar - $unidadesRestar;

                    ModelComprovanteProducto::where('comprovante_id','=', $unidadesDisponibles->comprovante_id)
                                      ->where('producto_id','=',  $unidadesDisponibles->producto_id)
                                      ->where('lote_id','=',  $unidadesDisponibles->lote_id)
                                      ->update(['cantidad_para_entregar' =>  $diferencia, 'updated_at'=>NOW()]); 
                    
                    

                    
                    ModelLogTranslados::where('comprovante_entrega_id','=', $unidadesDisponibles->comprovante_id)                                  
                                      ->where('origen','=',  $unidadesDisponibles->lote_id)
                                      ->update(['cantidad' =>  $diferencia, 'updated_at'=>NOW()]); 


                    $registroResta = $unidadesRestar;
                    $unidadesRestar = $diferencia;

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_para_entregar > $unidadesRestar) {

                    $diferencia = $unidadesDisponibles->cantidad_para_entregar - $unidadesRestar;


                    ModelComprovanteProducto::where('comprovante_id','=', $unidadesDisponibles->comprovante_id)
                                      ->where('producto_id','=',  $unidadesDisponibles->producto_id)
                                      ->where('lote_id','=',  $unidadesDisponibles->lote_id)
                                      ->update(['cantidad_para_entregar' =>  $diferencia, 'updated_at'=>NOW()]);

                    ModelLogTranslados::where('comprovante_entrega_id','=', $unidadesDisponibles->comprovante_id)                                  
                                    ->where('origen','=',  $unidadesDisponibles->lote_id)
                                    ->update(['cantidad' =>  $diferencia, 'updated_at'=>NOW()]);                     

                    $registroResta = $unidadesRestar;
                    $unidadesRestar = 0;

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_para_entregar < $unidadesRestar) {
                    

                    $diferencia = $unidadesRestar - $unidadesDisponibles->cantidad_para_entregar;


                    ModelComprovanteProducto::where('comprovante_id','=', $unidadesDisponibles->comprovante_id)
                                      ->where('producto_id','=',  $unidadesDisponibles->producto_id)
                                      ->where('lote_id','=',  $unidadesDisponibles->lote_id)
                                      ->update(['cantidad_para_entregar' =>  0, 'updated_at'=>NOW()]);

                    ModelLogTranslados::where('comprovante_entrega_id','=', $unidadesDisponibles->comprovante_id)                                  
                                    ->where('origen','=',  $unidadesDisponibles->lote_id)
                                    ->update(['cantidad' =>  0, 'updated_at'=>NOW()]);                                       
                
                    $registroResta = $unidadesDisponibles->cantidad_para_entregar;
                    $unidadesRestar = $diferencia;

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                    $cantidadSeccion = $registroResta / $unidad;
                };


                array_push($this->arrayProductos, [
                    "factura_id" => $idFactura,
                    "producto_id" => $idProducto,
                    "lote" => $unidadesDisponibles->lote_id,
                    "seccion_id" => $idSeccion,
                    "numero_unidades_resta_inventario" => $registroResta,
                    "sub_total" => $subTotal,
                    "isv" => $isv,
                    "total" => $total,
                    "resta_inventario_total" => $cantidadRestarInv,
                    "unidad_medida_venta_id" => $idUnidadVenta,
                    "precio_unidad" => $precio,
                    "cantidad" => $cantidad,
                    "cantidad_s" => $cantidadSeccion,
                    "cantidad_para_entregar" => $cantidad,
                    "sub_total_s" => $subTotalSecccionado,
                    "isv_s" => $isvSecccionado,
                    "total_s" => $totalSecccionado,
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);

                array_push($this->arrayLogs, [
                    "origen" => $unidadesDisponibles->lote_id,
                    "factura_id" => $idFactura,
                    "cantidad" => $registroResta,
                    "unidad_medida_venta_id"=>$idUnidadVenta,
                    "users_id" => Auth::user()->id,
                    "descripcion" => "Venta de producto/Orden de Entrega",
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);
            };

       
            return;

    }


    public function facturarComprobanteEstatal(Request $request){
        try {
         $validator = Validator::make($request->all(), [
 
             'fecha_vencimiento' => 'required',            
             'subTotalGeneral' => 'required',
             'isvGeneral' => 'required',
             'totalGeneral' => 'required',
             'arregloIdInputs' => 'required',
             'numeroInputs' => 'required',
             'seleccionarCliente' => 'required',
             'nombre_cliente_ventas' => 'required',
             'tipoPagoVenta' => 'required',
             'vendedor'=>'required',     
             'restriccion' => 'required',

 
 
 
         ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'mensaje' => 'Ha ocurrido un error al crear la compra.',
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
 
         $arrayTemporal = $request->arregloIdInputs;
         $arrayInputs = explode(',', $arrayTemporal);
 
         $mensaje = "";
         $flag = false;
 
                 //comprobar existencia de producto en el comprobante de entrega
                 for ($j = 0; $j < count($arrayInputs); $j++) {
 
           
                     $keyIdProducto = "idProducto" . $arrayInputs[$j];
                     $keyRestaInventario = "restaInventario" . $arrayInputs[$j];
                     $keyNombre = "nombre" . $arrayInputs[$j];
                   
         
                     $resultado = DB::selectONE("
                     select 
                     if(sum(cantidad_para_entregar) is null,0,sum(cantidad_para_entregar)) as cantidad_disponoble
                     from comprovante_has_producto
                     where cantidad_para_entregar <> 0
                     and producto_id = ". $request->$keyIdProducto."
                     and comprovante_id = ".$request->idComprobante."
                     ");
         
                     if ($request->$keyRestaInventario > $resultado->cantidad_disponoble) {
                         $mensaje = $mensaje . "Unidades insuficientes para el producto: <b>" .$request->$keyIdProducto ."-".$request->$keyNombre . "</b> en el comprobante de entrega.<br><br>";
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
                 $factura->tipo_venta_id = 2; // estatal
                 $factura->estado_factura_id = 1; // se presenta     
                 $factura->users_id = Auth::user()->id;
                 $factura->comision_estado_pagado = 0;
                 $factura->pendiente_cobro = $request->totalGeneral;
                 $factura->estado_editar = 1;
                 $factura->numero_orden_compra_id=$request->ordenCompra;
                 $factura->comentario = $request->nota_comen;
                 $factura->porc_descuento=$request->porDescuento;
                 $factura->monto_descuento = $request->porDescuentoCalculado;
                 
                 $factura->save();
     
                 $caiUpdated =  ModelCAI::find($cai->id);
                 $caiUpdated->numero_actual = $numeroSecuencia + 1;
                 $caiUpdated->cantidad_no_utilizada = $cai->cantidad_otorgada - $numeroSecuencia;
                 $caiUpdated->save();
     
                 //Tabla de listado
                //  DB::INSERT("INSERT INTO listado(
                //           numero, secuencia, numero_inicial, numero_final, cantidad_otorgada, cai_id, created_at, updated_at, eliminado) VALUES 
                //          ('" . $numeroCAI . "','" . $numeroSecuencia . "','" . $cai->numero_inicial . "','" . $cai->numero_final . "','" . $cai->cantidad_otorgada . "','" . $cai->id . "','" . NOW() . "','" . NOW() . "',0)");
                
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
                    $comprobanteId = $request->idComprobante;
        
                    //dd($factura);
        
                    $this->restarUnidadesComprovanteEntrega($comprobanteId, $restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad);
        
                             
                }; 

                if($request->tipoPagoVenta==2 ){//si el tipo de pago es credito
                    $this->restarCreditoCliente($request->seleccionarCliente,$request->totalGeneral,$factura->id);
                }
        
                
        
                // dd($this->arrayProductos);
                ModelVentaProducto::insert($this->arrayProductos);
                ModelLogTranslados::insert($this->arrayLogs);
        
        
            
        
                DB::commit();
                return response()->json([
                    'icon' => "success",
                    'text' => '
                    <div class="d-flex justify-content-between">
                        <a href="/factura/cooporativo/' . $factura->id . '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-invoice"></i> Imprimir Factura</a>
                        <a href="/venta/cobro/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Realizar Pago</a>
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
                'mensajeError'=>$e
            ], 402);
          }
    }
}
 
    

