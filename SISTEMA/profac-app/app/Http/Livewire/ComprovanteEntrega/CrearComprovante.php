<?php

namespace App\Http\Livewire\ComprovanteEntrega;

use App\Http\Livewire\Inventario\Producto;
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

use App\Models\ModelComprovanteEntrega;
use App\Models\ModelComprovanteHasProducto;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;


class CrearComprovante extends Component
{

    public $arrayProductos = [];
    public $arrayLogs = [];

    public function render()
    {
        return view('livewire.comprovante-entrega.crear-comprovante');
    }

    public function clientesObtener(Request $request)
    {


            $listaClientes = DB::SELECT("
            select
                id,
                nombre as text
            from cliente
                where estado_cliente_id = 1

                and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                    ");


                    return response()->json([
                        "results" => $listaClientes,
                    ], 200);
    }


    public function guardarComprovante(Request $request)
    {

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
            'bodega' => 'required',
            'comentario'=>'required'






        ]);

         //dd($request->all());

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error al crear la compra.',
                'errors' => $validator->errors()
            ], 406);
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

           $tipoVenta = DB::SELECTONE("select tipo_cliente_id from cliente where id =".$request->seleccionarCliente);

        try {




            // $numeroComprovante= DB::SELECTONE("
            // select
            // @contador:=count(id),
            // if(@contador = 0, 1 , @contador) as contador,
            // YEAR(NOW()) as anio

            // from comprovante_entrega
            // ");
            $numeroComprovante= DB::SELECTONE("
            select
            id,
            YEAR(NOW()) as anio

            from comprovante_entrega
            order by id desc
            ");
            if(empty($numeroComprovante->id)){
                $suma= 1;
                $numeroOrden = date('Y')."-".$suma;
            }else{
                $suma= $numeroComprovante->id+1;
                $numeroOrden = $numeroComprovante->anio."-".$suma;
            }



            DB::beginTransaction();

            $comprovante = new ModelComprovanteEntrega;
            $comprovante->numero_comprovante =  $numeroOrden;
            $comprovante->nombre_cliente =  $request->nombre_cliente_ventas;
            $comprovante->RTN =  $request->rtn_ventas;
            $comprovante->fecha_emision = $request->fecha_emision;
            $comprovante->fecha_vencimineto = $request->fecha_vencimiento;
            $comprovante->sub_total = $request->subTotalGeneral;

            $comprovante->sub_total_grabado = $request->subTotalGeneralGrabado;
            $comprovante->sub_total_excento = $request->subTotalGeneralExcento;

            $comprovante->isv =  $request->isvGeneral;
            $comprovante->total =  $request->totalGeneral;
            $comprovante->cliente_id =  $request->seleccionarCliente;
            $comprovante->tipo_venta_id =  $tipoVenta->tipo_cliente_id;
            $comprovante->arregloIdInputs =  json_encode($request->arregloIdInputs);
            // $comprovante->vendedor =  $numeroOrden;
            $comprovante->comentario = $request->comentario;
            $comprovante->users_id =  Auth::user()->id;
            $comprovante->numeroInputs =  $request->numeroInputs;
            $comprovante->estado_id =  1;
            $comprovante->porc_descuento = $request->porDescuento;
            $comprovante->monto_descuento = $request->porDescuentoCalculado;
            $comprovante->save();

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
                $keyacumuladoDescuento = 'acumuladoDescuento' . $arrayInputs[$i];

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
                $acumuladoDescuento = $request->$keyacumuladoDescuento;

                $this->restarUnidadesInventario($restaInventario, $idProducto, $idSeccion, $comprovante->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad,$acumuladoDescuento);
            };

            ModelComprovanteHasProducto::insert($this->arrayProductos);
            ModelLogTranslados::insert($this->arrayLogs);


            DB::commit();
            return response()->json([
                'icon' => "success",
                'text' =>  '
                <div class="">

                <a href="/comprobante/imprimir/' . $comprovante->id . '"    class="btn btn-sm btn-warning" target="_blank"  > <i class="fa-solid fa-print"></i> Imprimir Comprobante </a>
                <a href="/orden/entrega/facturar/' . $comprovante->id . '"    class="btn btn-sm btn-success" target="_blank"  > <i class="fa-solid fa-print"></i> Facturar Comprobante </a>

                </div>',
                'title' => 'Exito!',
                'idComprobante' => $comprovante->id,


            ], 200);
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'error' => $e,
                'icon' => "error",
                'text' => 'Ha ocurrido un error.',
                'title' => 'Error!',
                'idFactura' => $comprovante->id,
            ], 402);
        }
    }

    public function restarUnidadesInventario($unidadesRestarInv, $idProducto, $idSeccion, $idOrdenEntrega, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad,$acumuladoDescuento)
    {


            $precioUnidad = $subTotal / $unidadesRestarInv;

            $unidadesRestar = $unidadesRestarInv;
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
                    "comprovante_id" => $idOrdenEntrega,
                    "producto_id" => $idProducto,
                    "lote_id" => $unidadesDisponibles->id,
                    "seccion_id" => $idSeccion,
                    "numero_unidades_resta_inventario" => $registroResta,
                    "sub_total" => $subTotal,
                    "isv" => $isv,
                    "total" => $total,
                    "monto_descProducto"=>$acumuladoDescuento,
                    "resta_inventario_total" => $unidadesRestarInv,
                    "unidad_medida_venta_id" => $idUnidadVenta,
                    "precio_unidad" => $precio,
                    "cantidad" => $cantidad,
                    "cantidad_s" => $cantidadSeccion,
                    "cantidad_para_entregar" => $cantidadSeccion,
                    "sub_total_s" => $subTotalSecccionado,
                    "isv_s" => $isvSecccionado,
                    "total_s" => $totalSecccionado,
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);

                array_push($this->arrayLogs, [
                    "origen" => $unidadesDisponibles->id,
                    "comprovante_entrega_id" => $idOrdenEntrega,
                    "cantidad" => $registroResta,
                    "unidad_medida_venta_id" => $idUnidadVenta,
                    "users_id" => Auth::user()->id,
                    "descripcion" => "Orden de Entrega",
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);
            };

            //dd($arrarVentasProducto);
            //ModelVentaProducto::created($arrarVentasProducto);
            //ModelVentaProducto::insert($arrarVentasProducto);
            //DB::table('venta_has_producto')->insert($arrarVentasProducto);


            return;

    }


    public function imprimirComprobanteEntrega($idComprobante)
    {

        $datos = DB::SELECTONE("
        select
        A.numero_comprovante,
        A.nombre_cliente,
        B.direccion,
        B.telefono_empresa,
        B.correo,
        A.RTN,
        DATE(A.created_at) as fecha,
        time(A.created_at) as hora,
        C.numero_factura,
        C.cai,
        C.estado_factura_id,
        D.name as registrado_por,
        A.comentario
        from comprovante_entrega A
        inner join cliente B
        on A.cliente_id = B.id
        inner join users D
        on A.users_id = D.id
        left join factura C
        on A.id = C.comprovante_entrega_id
        where A.id=".$idComprobante
        );

        $productos = DB::SELECT("

        select
        A.producto_id,
        B.nombre,
        D.nombre as unidad,
        A.cantidad,
        (A.precio_unidad) as precio,
        A.sub_total

        from comprovante_has_producto A
        inner join producto B
        on A.producto_id = B.id
        inner join unidad_medida_venta C
        on A.unidad_medida_venta_id = C.id
        inner join unidad_medida D
        on C.unidad_medida_id = D.id
        where A.comprovante_id = ".$idComprobante."
        group by A.producto_id, B.nombre, D.nombre, A.cantidad, A.sub_total, A.precio_unidad
        ");

        $importes = DB::SELECTONE("
        select
            format(sub_total_grabado,2) as sub_total_grabado,
            format(sub_total_excento,2) as sub_total_excento,
            format(sub_total,2) as sub_total,
            format(isv,2) as isv,
            format(total,2) as total,
            format(monto_descuento ,2) as monto_descuento,
            porc_descuento as porc_descuento
        from comprovante_entrega A
            where id =".$idComprobante
        );

        $importesSinCentavos = DB::SELECTONE("
        select
            A.sub_total,
            A.isv,
            A.total,
            A.monto_descuento,
            A.porc_descuento
        from comprovante_entrega A
            where id = ".$idComprobante

        );



        if( fmod($importesSinCentavos->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importesSinCentavos->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/orden-entrega',compact('datos','productos','flagCentavos','importes','numeroLetras'))->setPaper('letter');

        return $pdf->stream("comprobante_numero " . $datos->numero_comprovante.".pdf");


    }

    public function imprimirComprobanteEntregaCopia($idComprobante)
    {

        $datos = DB::SELECTONE("
        select
        A.numero_comprovante,
        A.nombre_cliente,
        B.direccion,
        B.telefono_empresa,
        B.correo,
        A.RTN,
        DATE(A.created_at) as fecha,
        time(A.created_at) as hora,
        C.numero_factura,
        C.cai,
        C.estado_factura_id,
        D.name as registrado_por,
        A.comentario
        from comprovante_entrega A
        inner join cliente B
        on A.cliente_id = B.id
        inner join users D
        on A.users_id = D.id
        left join factura C
        on A.id = C.comprovante_entrega_id
        where A.id=".$idComprobante
        );

        $productos = DB::SELECT("

        select
        A.producto_id,
        B.nombre,
        D.nombre as unidad,
        A.cantidad,
        (A.precio_unidad) as precio,
        A.sub_total

        from comprovante_has_producto A
        inner join producto B
        on A.producto_id = B.id
        inner join unidad_medida_venta C
        on A.unidad_medida_venta_id = C.id
        inner join unidad_medida D
        on C.unidad_medida_id = D.id
        where A.comprovante_id = ".$idComprobante."
        group by A.producto_id, B.nombre, D.nombre, A.cantidad, A.sub_total, A.precio_unidad
        ");

        $importes = DB::SELECTONE("
        select
            format(sub_total_grabado,2) as sub_total_grabado,
            format(sub_total_excento,2) as sub_total_excento,
            format(sub_total,2) as sub_total,
            format(isv,2) as isv,
            format(total,2) as total,
            format(monto_descuento ,2) as monto_descuento,
            porc_descuento as porc_descuento
        from comprovante_entrega A
            where id =".$idComprobante
        );

        $importesSinCentavos = DB::SELECTONE("
        select
            A.sub_total,
            A.isv,
            A.total,
            A.monto_descuento,
            A.porc_descuento
        from comprovante_entrega A
            where id = ".$idComprobante

        );




        if( fmod($importesSinCentavos->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importesSinCentavos->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/orden-entrega-copia',compact('datos','productos','flagCentavos','importes','numeroLetras'))->setPaper('letter');

        return $pdf->stream("comprobante_numero " . $datos->numero_comprovante.".pdf");


    }
}
