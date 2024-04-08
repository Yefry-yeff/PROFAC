<?php

namespace App\Http\Livewire\Vale;

use Livewire\Component;
use DataTables;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

use App\Models\ModelFactura;

use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;

use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\ModelVale;
use Luecano\NumeroALetras\NumeroALetras;

use PDF;

class RestarVale extends Component
{
    public $arrayProductos = [];
    public $arrayLogs = [];

    public function render()
    {
        return view('livewire.vale.restar-vale');
    }

    public function listarVales(){
       try {

        $listaVales = DB::SELECT("
        select

        A.id,
        A.numero_vale,
        FORMAT(A.sub_total,2) as sub_total,
        FORMAT(A.isv,2) as isv,
        FORMAT(A.total,2) as total,
        C.numero_factura,
        C.nombre_cliente,
        A.factura_id,
        A.created_at,
        D.name,
        A.estado_id
        from vale A

        inner join factura C
        on A.factura_id = C.id
        inner join users D
        on A.users_id = D.id
        INNER join espera_has_producto E
        on A.id = E.vale_id


        order by A.created_at desc

        ");
        return Datatables::of($listaVales)
                ->addColumn('opciones', function ($vale) {

                    if($vale->estado_id == 1){
                        return
                        '<div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                    <a class="dropdown-item" target="_blank"  onclick="anularVale('.$vale->id.')"> <i class="fa-solid fa-ban text-info"></i> Anular Vale </a>
                    </li>

                    <li>
                    <a class="dropdown-item" target="_blank"  href="/vale/imprimir/'.$vale->id.'"> <i class="fa-solid fa-file-invoice text-success"></i> Imprimir Vale </a>
                    </li>

                    <li>
                    <a class="dropdown-item" target="_blank"   onclick="eliminarVale('.$vale->id.')"> <i class="fa-regular fa-trash-can text-danger"></i> Eliminar Vale </a>
                    </li>

                    <li>
                    <a class="dropdown-item" target="_blank"  onclick="comentarios('.$vale->id.')"> <i class="fa-solid fa-file-invoice text-info"></i> Ver notas y comentarios </a>
                    </li>

                </ul>
            </div>';
                    }else if($vale->estado_id == 2 || $vale->estado_id == 5){
                        return
                        '<div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">


                <li>
                <a class="dropdown-item" target="_blank"  href="/vale/imprimir/'.$vale->id.'"> <i class="fa-solid fa-file-invoice text-success"></i> Imprimir Vale </a>
                </li>

                    <li>
                    <a class="dropdown-item" target="_blank"  onclick="comentarios('.$vale->id.')"> <i class="fa-solid fa-file-invoice text-info"></i> Ver notas y comentarios </a>
                    </li>



                </ul>
            </div>';
                    }


                })
                ->addColumn('estado', function ($vale) {

                    if($vale->estado_id==1){
                        return '<p class="text-center"><span class="badge badge-warning p-2" style="font-size:0.75rem">Pendiente</span></p>';
                    }else if($vale->estado_id==2){

                       return  '<p class="text-center"><span class="badge badge-primary p-2" style="font-size:0.75rem">Anulado</span></p>';
                    }else{

                        return  '<p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Eliminado</span></p>';
                    }


                })
                ->rawColumns(['opciones','estado'])
                ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'icon' => 'error',
                'text' => 'Ha ocurrido un error al listar los comprobantes de entrega.',
                'title' => 'Erro!',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }



    }

    public function anularVale(Request $request){
           try {
            DB::beginTransaction();
            $listaProductos = DB::SELECT("
            select
                B.resta_inventario_total,
                B.producto_id,
                B.unidad_medida_venta_id as idUnidadVenta,
                B.precio,
                B.cantidad,
                B.sub_total,
                B.isv,
                B.total,
                C.isv as ivsProducto,
                D.unidad_venta,
                E.id as idFactura,
                C.nombre
            from vale A
            inner join espera_has_producto B on
            A.id = B.vale_id
            inner join producto C
            on C.id = B.producto_id
            inner join unidad_medida_venta D
            on B.unidad_medida_venta_id = D.id
            inner join factura E
            on A.factura_id = E.id
            where A.id = ".$request->idVale
            );

            $mensaje = "";
            $flag = false;

            foreach($listaProductos as $producto){
                $resultado = DB::SELECTONE("
                select
                if(sum(cantidad_disponible) is null,0,sum(cantidad_disponible)) as cantidad_disponoble
                from recibido_bodega
                where cantidad_disponible <> 0
                and producto_id = " .$producto->producto_id);

                    if ($producto->resta_inventario_total > $resultado->cantidad_disponoble) {
                        $mensaje = $mensaje . "Unidades insuficientes para el producto: <b>Cod." . $producto->producto_id ." - "."$producto->nombre  </b>.";
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

            foreach($listaProductos as $producto){
                $this->restarUnidadesInventario(
                    $producto->resta_inventario_total,
                    $producto->producto_id,
                    $producto->idFactura,
                    $producto->idUnidadVenta,
                    $producto->precio,
                    $producto->cantidad,
                    $producto->sub_total,
                    $producto->isv,
                    $producto->total,
                    $producto->ivsProducto,
                    $producto->unidad_venta
                );
            }


            ModelVentaProducto::insert($this->arrayProductos);
            ModelLogTranslados::insert($this->arrayLogs);

            $vale =  ModelVale::find($request->idVale);
            $vale->estado_id = 2;
            $vale->comentario_anular = $request->motivo;
            $vale->save();


            DB::commit();
           return response()->json([
            'icon' => 'success',
            'text' => 'Exito al anular el vale',
            'title' => 'Exito.',
           ],200);
           } catch (QueryException $e) {
            DB::rollback();
           return response()->json([
            'icon' => '',
            'text' => '',
            'title' => '',
            'message' => 'Ha ocurrido un error',
            'error' => $e,
           ],402);
           }
    }

    public function restarUnidadesInventario($unidadesRestarInv, $idProducto, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad)
    {


            $precioUnidad = $subTotal / $unidadesRestarInv;

            $unidadesRestar = $unidadesRestarInv;//es la cantidad ingresada por el usuario multiplicado por unidades de venta del producto
            $registroResta = 0;
            while (!($unidadesRestar <= 0)) {

                $unidadesDisponibles = DB::SELECTONE("
                        select
                            id,
                            cantidad_disponible,
                            seccion_id
                        from recibido_bodega
                        where
                            producto_id = " . $idProducto . " and
                            cantidad_disponible <> 0
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
                    "seccion_id" => $unidadesDisponibles->seccion_id,
                    "numero_unidades_resta_inventario" => $registroResta, //el numero de unidades que se va restar del inventario pero en unidad base
                    "sub_total" => $subTotal,
                    "isv" => $isv,
                    "total" => $total,
                    "resta_inventario_total" => $unidadesRestarInv, //Es la cantidad que ingresa el usuario para la venta
                    "unidad_medida_venta_id" => $idUnidadVenta, //la unidad de medida que selecciono el usuario para la venta
                    "precio_unidad" => $precio, // precio de venta ingresado por el usuario
                    "cantidad" => $cantidad, //cantidad ingresada por el usuario
                    "cantidad_s" => $cantidadSeccion, //la unidad que se resta del inventario  pero convertida a la unidad de venta seleccionada por el usuario
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
                    "unidad_medida_venta_id"=>$idUnidadVenta,
                    "users_id" => Auth::user()->id,
                    "descripcion" => "Venta de producto - vale",
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

    public function eliminarVale(Request $request){
       try {
        DB::beginTransaction();

        $vale = ModelVale::find($request->idVale);
        $vale->estado_id = 5;
        $vale->comentario_eliminar = $request->motivo;
        $vale->save();

        $factura = ModelFactura::find($vale->factura_id);
        $factura->total = ROUND($factura->total - $vale->total,2);
        $factura->isv = ROUND($factura->isv -  $vale->isv,2);
        $factura->sub_total = ROUND($factura->sub_total - $vale->sub_total,2);
        $factura->pendiente_cobro = ROUND($factura->pendiente_cobro - $vale->total,2);



        if($factura->tipo_pago_id == 2){
            $factura->credito = ROUND(($factura->credito - $vale->total),2);

            $cliente = ModelCliente::find($factura->cliente_id);
            $cliente->credito = ROUND($cliente->credito + $request->totalGeneralVP,2);
            $cliente->save();

            $credito = new logCredito();
            $credito->descripcion = "Aumento de credito por vale eliminado.";
            $credito->monto =  $vale->total;
            $credito->users_id = Auth::user()->id;
            $credito->factura_id = $factura->id;
            $credito->cliente_id = $factura->cliente_id;
            $credito->save();
        }

        $factura->save();



        DB::commit();
       return response()->json([
        'icon' => 'success',
        'text' => 'Vale eliminado con éxito.',
        'title' => 'Exito!',
       ],200);
       } catch (QueryException $e) {
        DB::rollback();
       return response()->json([
        'icon' => 'error',
        'text' => 'Ha ocurrido un error al eliminar el vale.',
        'title' => 'Error!',
        'message' => 'Ha ocurrido un error',
        'error' => $e,
       ],402);
       }
    }

    public function mostrarNotas($idVale){
       try {

        $notas = DB::SELECTONE("select notas, comentario_anular, comentario_eliminar from vale where id=".$idVale);

       return response()->json([

        'notas' => $notas->notas,
        'comentario_anular' =>$notas->comentario_anular,
        'comentario_eliminar'=> $notas->comentario_eliminar,

       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'icon' => 'error',
        'text' => 'Ha ocurrido un error al obtener los comentarios.',
        'title' => 'Error!',
        'message' => 'Ha ocurrido un error',
        'error' => $e,
       ],402);
       }
    }

    public function comprobarVales(){
       try {

        $flagVale = false;
        $valesArray =[];

        $valesAnuladosLista = DB::SELECT("select
        id,
        numero_vale
        from vale
        where estado_id = 1
        order by id ASC");



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



            return;
       return response()->json([
        'icon' => '',
        'text' => '',
        'title' => '',
       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'icon' => '',
        'text' => '',
        'title' => '',
        'message' => 'Ha ocurrido un error',
        'error' => $e,
       ],402);
       }
    }


    public function imprimirVale($idVale)
    {

        $vale = DB::SELECTONE("
        select
        A.id,
        A.numero_vale,
        A.sub_total,
        A.isv,
        A.total,
        A.estado_id as estado_id_vale,
        B.numero_factura,
        B.cai,
        B.cliente_id,
        DATE(A.created_at) as fecha_emision,
        TIME(A.created_at) as hora,
        A.estado_id,
        C.name,
        B.estado_factura_id as estado_factura,
        B.numero_factura,
        B.estado_venta_id
        from vale A
        inner join factura B
        on A.factura_id = B.id
        inner join users C
        on A.users_id = C.id
        where A.id =".$idVale
        );

       $cliente = DB::SELECTONE("
       select
        factura.nombre_cliente as nombre,
        SUBSTRING(cliente.direccion,1,142) as direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        cliente.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where cliente.id = ".$vale->cliente_id);

       $importes = DB::SELECTONE("
       select
       total,
       isv,
       sub_total,
       sub_total_grabado,
       sub_total_excento
       from factura
        where id = ".$idVale);


        $importesConCentavos= DB::SELECTONE("
        select
        total as total,
        isv as isv,
        sub_total as sub_total,
        sub_total_grabado as sub_total_grabado,
        sub_total_excento as sub_total_excento,
        monto_descuento as monto_descuento,
        porc_descuento
        from vale where id = ".$idVale);


            $productos = DB::SELECT("
            select
            B.id as codigo,
            B.nombre as descripcion,
            D.nombre as medida,
            A.precio as precio,
            A.cantidad,
            A.sub_total as importe
            from espera_has_producto A
            inner join producto B
            on A.producto_id = B.id
            inner join unidad_medida_venta C
            on A.unidad_medida_venta_id = C.id
            inner join unidad_medida D
            on C.unidad_medida_id = D.id
            where A.vale_id =".$idVale."
            order by  A.index asc
            ");

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =".$idVale);

        if(empty($ordenCompra->numero_orden)){
            $ordenCompra=["numero_orden"=>""];
        }else{
            $ordenCompra=["numero_orden"=>$ordenCompra->numero_orden];
        }


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/vale', compact( 'vale','cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos','ordenCompra'))->setPaper('letter');

        return $pdf->stream("vale_numero" .$vale->numero_vale.".pdf");


    }

    public function imprimirValeCopia($idVale)
    {

        $vale = DB::SELECTONE("
        select
        A.id,
        A.numero_vale,
        A.sub_total,
        A.isv,
        A.total,
        A.estado_id as estado_id_vale,
        B.numero_factura,
        B.cai,
        B.cliente_id,
        DATE(A.created_at) as fecha_emision,
        TIME(A.created_at) as hora,
        A.estado_id,
        C.name,
        B.estado_factura_id as estado_factura,
        B.numero_factura,
        B.estado_venta_id
        from vale A
        inner join factura B
        on A.factura_id = B.id
        inner join users C
        on A.users_id = C.id
        where A.id =".$idVale
        );

        $cliente = DB::SELECTONE("
        select
         factura.nombre_cliente as nombre,
         SUBSTRING(cliente.direccion,1,142) as direccion,
         cliente.correo,
         factura.fecha_emision,
         factura.fecha_vencimiento,
         TIME(factura.created_at) as hora,
         cliente.telefono_empresa,
         cliente.rtn
         from factura
         inner join cliente
         on factura.cliente_id = cliente.id
         where cliente.id = ".$vale->cliente_id);

       $importes = DB::SELECTONE("
       select
       total,
       isv,
       sub_total,
       sub_total_grabado,
       sub_total_excento
       from factura
        where id = ".$idVale);


        $importesConCentavos= DB::SELECTONE("
        select
        total as total,
        isv as isv,
        sub_total as sub_total,
        sub_total_grabado as sub_total_grabado,
        sub_total_excento as sub_total_excento,
        monto_descuento as monto_descuento,
        porc_descuento
        from vale where id = ".$idVale);


            $productos = DB::SELECT("
            select
            B.id as codigo,
            B.nombre as descripcion,
            D.nombre as medida,
            A.precio as precio,
            A.cantidad,
            A.sub_total as importe
            from espera_has_producto A
            inner join producto B
            on A.producto_id = B.id
            inner join unidad_medida_venta C
            on A.unidad_medida_venta_id = C.id
            inner join unidad_medida D
            on C.unidad_medida_id = D.id
            where A.vale_id =".$idVale."
            order by  A.index asc
            ");

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =".$idVale);

        if(empty($ordenCompra->numero_orden)){
            $ordenCompra=["numero_orden"=>""];
        }else{
            $ordenCompra=["numero_orden"=>$ordenCompra->numero_orden];
        }


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/vale_copia', compact( 'vale','cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos','ordenCompra'))->setPaper('letter');

        return $pdf->stream("vale_numero" .$vale->numero_vale.".pdf");


    }
}
