<?php

namespace App\Http\Livewire\NotaCredito;


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

use App\Models\ModelNotaCredito;
use App\Models\ModelNotaCreditoProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelRecibirBodega;
use App\Models\ModelCAI;
use App\Http\Controllers\CAI\Notificaciones;

class CrearNotaCredito extends Component
{
    public function render()
    {
        return view('livewire.nota-credito.crear-nota-credito');
    }

    public function obtenerClientes(Request $request){

        $clientes = DB::SELECT("select id, concat(id,'-',nombre) as text from cliente where id <> 1 and ((nombre like '%".$request->search."%') or (id like '%".$request->search."%')) limit 15");

        return response()->json([
            "results"=>$clientes,
        ],200);

    }

    public function obtenerFactura(Request $request){

        $clientes = DB::SELECT("select id, concat('cor: ',cai,' _ cod: ',numero_factura) as text from factura where estado_venta_id = 1 and cliente_id = ".$request->idCliente." and (cai like '%".$request->search."%' or numero_factura like '%".$request->search."%' ) limit 15");

        return response()->json([
            "results"=>$clientes,
        ],200);

    }

    public function obtenerDetalleFactura(Request $request){

        $datosFactura = DB::SELECTONE("
        select
        factura.id,
        fecha_emision,
        B.descripcion as tipoPago,
        C.descripcion as tipoFactura,
        D.id as idCliente,
        D.rtn,
        D.nombre as nombreCliente,
        E.name vendedor,
        (select name from users where id = factura.users_id) as facturador,
        factura.created_at as fechaRegistro,
        sub_total,
        sub_total_grabado,
        sub_total_excento,
        total,
        isv


        from factura
        inner join tipo_pago_venta B
        on factura.tipo_pago_id = B.id
        inner join tipo_venta C
        on factura.tipo_venta_id = C.id
        inner join cliente D
        on factura.cliente_id = D.id
        inner join users E
        on factura.vendedor = E.id
        where factura.id = ".$request->idFactura
        );

        return response()->json([
            'datosFactura'=>$datosFactura
        ],200);

    }

    public function obtenerProductos(Request $request){

        $productos = DB::SELECT("
        select
            B.factura_id,
            B.producto_id,
            B.seccion_id,
            C.nombre,
            concat(F.nombre,' - ',D.descripcion ) as bodega,
            format(B.precio_unidad,2) as precio_unidad,
            sum(B.cantidad) as cantidad,
            concat(H.nombre,' - ', G.unidad_venta ) as unidad_medida,
            format(B.sub_total,2) as sub_total,
            format(B.isv,2) as isv,
            format(B.total,2) as total,
            C.isv as productoISV
        from factura A
            inner join venta_has_producto B
            on A.id = B.factura_id
            inner join producto C
            on C.id = B.producto_id
            inner join seccion D
            on D.id = B.seccion_id
            inner join segmento E
            on E.id = D.segmento_id
            inner join bodega F
            on F.id = E.bodega_id
            inner join unidad_medida_venta G
            on G.id = B.unidad_medida_venta_id
            inner join unidad_medida H
            on H.id = G.unidad_medida_id
        where A.id =".$request->idFactura."
        group by B.seccion_id,  B.factura_id, B.producto_id,  C.nombre,  B.cantidad_nota_credito,  H.nombre, unidad_medida, C.isv, B.precio_unidad, B.sub_total,B.isv,B.total
        "
        );

        return Datatables::of($productos)
        ->addColumn('opciones', function ($producto) {


                return

                '<div class="text-center">
                    <button  onclick="infoProducto('.$producto->factura_id.','.$producto->producto_id.','.$producto->seccion_id.')" class="btn btn-warning " >Devolución</button>

                </div>';

        })

        ->rawColumns(['opciones'])
        ->make(true);

    }

    public function datosProducto(Request $request){
       try {

        $datos = DB::SELECTONE("
        select
            B.factura_id,
            B.producto_id,

            C.nombre as producto,
            concat(F.nombre,' - ',D.descripcion ) as bodega,

            F.id as bodegaId,
            F.nombre as nombreBodega,
            E.id as segmentoId,
            E.descripcion as segmento,
            D.id as seccionId,
            D.descripcion as seccion,

            B.precio_unidad,
            B.cantidad_nota_credito as cantidad,
            H.nombre as unidad_medida,
            B.unidad_medida_venta_id as idUnidadVenta ,
            G.unidad_venta,
            C.isv as porcentajeISV

        from factura A
        inner join venta_has_producto B
        on A.id = B.factura_id
        inner join producto C
        on C.id = B.producto_id
        inner join seccion D
        on D.id = B.seccion_id
        inner join segmento E
        on E.id = D.segmento_id
        inner join bodega F
        on F.id = E.bodega_id
        inner join unidad_medida_venta G
        on G.id = B.unidad_medida_venta_id
        inner join unidad_medida H
        on H.id = G.unidad_medida_id
        where B.producto_id = ".$request->idProducto." and A.id =".$request->idFactura." and B.seccion_id =".$request->idSeccion);




       return response()->json([
        'datos' => $datos,


       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'icon' => 'error',
        'text' => 'Ha ocurrido un error.',
        'title' => 'Error',
        'message' => 'Ha ocurrido un error',
        'error' => $e,
       ],402);
       }
    }

    public function obtenerMotivos(){



        $obtenerMotivos = DB::SELECT("select id, descripcion as text from motivo_nota_credito");


       return response()->json([
        'results' => $obtenerMotivos

       ],200);

    }

    public function guardarNotaCredito(Request $request){
       try {

        $estadoCuenta = DB::selectone('select estado_cerrado from aplicacion_pagos where estado = 1 and factura_id = '.$request->idFactura);
            // dd($saldoActual->saldo);
            if($estadoCuenta != null){
                if($estadoCuenta->estado_cerrado == 2){
                    return response()->json([
                        "icon" => "warning",
                        "text"=>"Esta factura esta cerrada, no se puede crear nota.",
                        "title"=>"Advertencia!"

                    ],400);

                }

             }

        $flagError = false;
        $text1 ="<p>Los siguientes productos exceden la cantidad disponible para realizar la nota de credito: <p><ul>";
        $nombreProducto ="";

        $arregloIdInputs = $request->arregloIdInputs;



        /***VERIFICA LA EXISTENCIA DEL PRODUCTO EN LA FACTURA PARA REALIZAR LA DEVOLUCION - SI NO ENCUENTRA CANTIDAD DISPONIBLE, NO REALIZAR LA NOTA DE CREDITO */
        for ($i=0; $i < count($arregloIdInputs) ; $i++) {

            $keyIdProducto = "IdProducto".$arregloIdInputs[$i];
            $keyNombreProducto = "nombreProducto".$arregloIdInputs[$i];
            $keyIdSeccion = "IdSeccion".$arregloIdInputs[$i];
            $keyCantidad = "cantidad".$arregloIdInputs[$i];

            $idProducto = $request->$keyIdProducto;
            $idSeccion = $request->$keyIdSeccion;
            $cantidad = $request->$keyCantidad;
            $nombreProducto = $request->$keyNombreProducto;


            $cantidadDisponible = DB::SELECTONE("select sum(unidades_nota_credito_resta_inventario) as cantidad from venta_has_producto where factura_id=".$request->idFactura." and producto_id= ".$idProducto." and seccion_id = ".$idSeccion);

            if($cantidad  > $cantidadDisponible){
                $flagError = true;
                $text1 =  $text1."<li>".$idProducto."-".$nombreProducto."</li>";
            }
        }

        if($flagError){
            $text1 =  $text1."</ul>";
            return response()->json([
                "text" =>  $text1,
                "icon" => "warning",
                "title"=>"Advertencia!"
            ], 200);
        }



        $numeroNota = DB::SELECTONE("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from nota_credito");

        $facturaClienteId = DB::SELECTONE("select cliente_id from factura where id = ". $request->idFactura);
            //tipo_cliente 1 = B y 2 = A
        $tipoCliente = DB::SELECTONE("select tipo_cliente_id from cliente where id = ". $facturaClienteId->cliente_id);

            //tipo_cliente 1 = B-coorporativo-noDeclara y 2 = A-estatal-Sideclara
        if ($tipoCliente->tipo_cliente_id === 1 ) {
           $estado = 1;

            $cai = DB::SELECTONE("select
                            id,
                            numero_inicial,
                            numero_final,
                            cantidad_otorgada,
                            serie as 'numero_actual',
                            if( DATE(NOW()) > fecha_limite_emision ,'TRUE','FALSE') as fecha_limite_emision,
                            cantidad_no_utilizada
                            from cai
                            where tipo_documento_fiscal_id = 3 and estado_id = 1");

            //tipo_cliente 1 = B-coorporativo-noDeclara y 2 = A-estatal-Sideclara
        } elseif($tipoCliente->tipo_cliente_id === 2) {

            $estado = 2;

            $cai = DB::SELECTONE("select
                            id,
                            numero_inicial,
                            numero_final,
                            cantidad_otorgada,
                            numero_actual as 'numero_actual',
                            if( DATE(NOW()) > fecha_limite_emision ,'TRUE','FALSE') as fecha_limite_emision,
                            cantidad_no_utilizada
                            from cai
                            where tipo_documento_fiscal_id = 3 and estado_id = 1");
        }

        if(empty($cai)){

            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La nota de credito no puede proceder, debido a que no se cuenta con CAI valido.",
            ], 200);

        }

        if($cai->fecha_limite_emision == 'TRUE'){
            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La nota de crédito no puede proceder, debido a que la fecha límite de emisión de CAI a sido superada.",
            ], 200);
        }

        $limite = explode('-',$cai->numero_final);
        $limite = ltrim($limite[3],"0");

        if($cai->numero_actual > $limite){

            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La nota de credito no puede proceder, debido que ha alcanzadado el número maximo  de CAI otorgado.",
            ], 200);

        }


        DB::beginTransaction();
           //SE CREA LA NOTA DE CREDITO

        $numeroSecuencia = $cai->numero_actual;
        $arrayCai = explode('-',$cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
        $numeroCAI = $arrayCai[0].'-'.$arrayCai[1].'-'.$arrayCai[2].'-'.$cuartoSegmentoCAI;

        $validarCAI = new Notificaciones();
        $validarCAI->validarAlertaCAI(ltrim($arrayCai[3],"0"),$numeroSecuencia, 4);

        $notaCredito = new ModelNotaCredito();
        $notaCredito->numero_nota =$numeroNota->numero;
        $notaCredito->cai = $numeroCAI;
        $notaCredito->numero_secuencia_cai = $numeroSecuencia;
        $notaCredito->fecha = now();
        /* $notaCredito->sub_total = round($request->subTotalGeneralCredito,2);
        $notaCredito->sub_total_grabado = round($request->subTotalGeneralGrabadoCredito,2);
        $notaCredito->sub_total_excento = round($request->subTotalGeneralExcentoCredito,2);
        $notaCredito->isv = round($request->isvGeneralCredito,2);
        $notaCredito->total = round($request->totalGeneralCredito,2); */

        $notaCredito->sub_total = $request->subTotalGeneralCredito;
        $notaCredito->sub_total_grabado = $request->subTotalGeneralGrabadoCredito;
        $notaCredito->sub_total_excento = $request->subTotalGeneralExcentoCredito;
        $notaCredito->isv =$request->isvGeneralCredito;
        $notaCredito->total = $request->totalGeneralCredito;
        $notaCredito->factura_id = $request->idFactura;
        $notaCredito->cai_id = $cai->id;
        $notaCredito->motivo_nota_credito_id = $request->motivo_nota;
        $notaCredito->users_id = Auth::user()->id;
        $notaCredito->estado_nota_id = 1;
        $notaCredito->estado_nota_dec = $estado;
        $notaCredito->comentario = $request->comentario;

        /*Se agregan los nuevos campos en la nota de credito*/
        $notaCredito->estado_rebajado = 2;
        $notaCredito->user_registra_rebaja = 0;
        $notaCredito->comentario_rebajado = 'N/A';
        $notaCredito->fecha_rebajado = NULL;


        $notaCredito->save();



        //GUARDA LOS PRODUCTOS DE LA NOTA DE CREDITO

        for ($i=0; $i < count($arregloIdInputs) ; $i++) {

            $keyIdProducto = "IdProducto".$arregloIdInputs[$i];
            $keyIdSeccion = "IdSeccion".$arregloIdInputs[$i];
            $keyCantidad = "cantidad".$arregloIdInputs[$i];

            $keyIdUnidadMedida = "idUnidadMedida".$arregloIdInputs[$i];
            $keySubTotal = "subTotal".$arregloIdInputs[$i];
            $keyISV = "isv".$arregloIdInputs[$i];
            $keyTotal = "total".$arregloIdInputs[$i];

            $keyPrecio = "precio".$arregloIdInputs[$i];

            $idProducto = $request->$keyIdProducto;
            $idSeccion = $request->$keyIdSeccion;
            $cantidad = $request->$keyCantidad;
            $idUnidadMedida = $request->$keyIdUnidadMedida;
            $subTotal = $request->$keySubTotal;
            $isv = $request->$keyISV;
            $total = $request->$keyTotal;
            $precio = $request->$keyPrecio;


            $productoNota = new ModelNotaCreditoProducto();
            $productoNota->nota_credito_id = $notaCredito->id;
            $productoNota->producto_id = $idProducto;
            $productoNota->seccion_id = $idSeccion;
            $productoNota->indice = $arregloIdInputs[$i];
            $productoNota->cantidad = $cantidad;
            $productoNota->precio_unidad = $precio;
            $productoNota->sub_total = $subTotal;
            $productoNota->isv =  $isv;
            $productoNota->total = $total;
            $productoNota->unidad_medida_venta_id = $idUnidadMedida;
            $productoNota->save();

        }




        //RESTA LOS PRODUCTOS DE LA FACTURA

        for ($i=0; $i < count($arregloIdInputs) ; $i++) {

            $keyIdProducto = "IdProducto".$arregloIdInputs[$i];
            $keyIdSeccion = "IdSeccion".$arregloIdInputs[$i];
            $keyCantidad = "cantidad".$arregloIdInputs[$i];

            $keyIdUnidadMedida = "idUnidadMedida".$arregloIdInputs[$i];

            $keySubTotal = "subTotal".$arregloIdInputs[$i];
            $keyISV = "isv".$arregloIdInputs[$i];
            $keyTotal = "total".$arregloIdInputs[$i];

            $idProducto = $request->$keyIdProducto;
            $idSeccion = $request->$keyIdSeccion;
            $cantidad = $request->$keyCantidad;
            $idUnidadMedida = $request->$keyIdUnidadMedida;

            $unidadVenta = DB::SELECTONE("select unidad_venta from unidad_medida_venta where id =".$idUnidadMedida);
            $cantidadBaseSeccion = $unidadVenta->unidad_venta*$cantidad;

            $this->restarUnidadesDevolver($request->idFactura,$idProducto,$idSeccion,$cantidadBaseSeccion,$unidadVenta->unidad_venta, $notaCredito->id);

        }




            //tipo_cliente 1 = B-coorporativo-noDeclara y 2 = A-estatal-Sideclara
        if ($tipoCliente->tipo_cliente_id === 2 ) {
            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->numero_actual = $caiUpdated->numero_actual + 1;
            $caiUpdated->cantidad_no_utilizada=  $caiUpdated->cantidad_no_utilizada - 1;
            $caiUpdated->save();

            //tipo_cliente 1 = B-coorporativo-noDeclara y 2 = A-estatal-Sideclara
        } elseif($tipoCliente->tipo_cliente_id === 1 ) {
            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->serie = $caiUpdated->serie + 1;
            $caiUpdated->cantidad_no_utilizada=  $caiUpdated->cantidad_no_utilizada - 1;
            $caiUpdated->save();
        }





        DB::commit();
       return response()->json([
        'icon' => 'success',
        'text' => 'Nota de credito creada correctamente.',
        'title' => 'Exito!',
        'idNota'=> $notaCredito->id
       ],200);
       } catch (QueryException $e) {
        DB::rollback();
       return response()->json([
        'icon' => 'error',
        'text' => 'Ha ocurrido un error al guardar la nota de credito',
        'title' => 'Error',
        'message' => 'Ha ocurrido un error',
        'error' => $e,
       ],402);
       }
    }

    public function restarUnidadesDevolver($idFactura, $idProducto, $idSeccion, $cantidadBaseSeccion, $unidadVenta, $idNotaCredito ){
        $i = 0;


        $cantidadSeccionRestar = $cantidadBaseSeccion; //unidad base a restar
        $lotes = DB::SELECT("
        select
        lote,
        unidades_nota_credito_resta_inventario as unidadesLote,
        unidad_medida_venta_id,
        resta_inventario_total,
        cantidad_nota_credito
        from venta_has_producto
        where factura_id=".$idFactura." and producto_id=".$idProducto." and seccion_id=".$idSeccion
        );

        // if($idProducto==1943 && $idSeccion==137){
        //     DB::rollBack();

        //     dd($lotes,$i );
        // }

        while($cantidadSeccionRestar > 0){

            $lote = $lotes[$i];


            if($lote->unidadesLote > $cantidadSeccionRestar){

                $cantidadLoteRestarUpdate = $lote->unidadesLote - $cantidadSeccionRestar ;
                $cantidadDevolver = $cantidadSeccionRestar;
                $cantidadSeccionRestar = 0;
            }

            if($lote->unidadesLote == $cantidadSeccionRestar){
                $cantidadDevolver = $cantidadSeccionRestar;
                $cantidadSeccionRestar = 0;
                $cantidadLoteRestarUpdate = 0;


            }

            if($lote->unidadesLote < $cantidadSeccionRestar){
                $cantidadSeccionRestar = $cantidadSeccionRestar - $lote->unidadesLote;
                $cantidadLoteRestarUpdate = 0;
                $cantidadDevolver = $lote->unidadesLote;
            }


            //***QUERY***//

            DB::table('venta_has_producto')
            ->where('factura_id',$idFactura)
            ->where('producto_id', $idProducto)
            ->where('seccion_id', $idSeccion)
            ->where('lote', $lote->lote)
            ->update([
                //'numero_unidades_resta_inventario' => $cantidadLoteRestarUpdate,
                //'cantidad_para_entregar' => $cantidadLoteRestarUpdate,
                //'cantidad_s' => ($cantidadLoteRestarUpdate/$unidadVenta)

                'unidades_nota_credito_resta_inventario' => $cantidadLoteRestarUpdate,
                'cantidad_para_entregar' => $cantidadLoteRestarUpdate,
                // 'cantidad_nota_credito'=>$cantidadLoteRestarUpdate/$unidadVenta
            ]);


            DB::table('log_translado')
            ->where('origen',$lote->lote)
            ->where('factura_id',$idFactura)
            ->update([
               'cantidad' => $cantidadLoteRestarUpdate,
               'updated_at' => now()
            ]);


            $logTranslados = new ModelLogTranslados;
            $logTranslados->origen = $lote->lote ;
            $logTranslados->destino = $lote->lote;
            $logTranslados->cantidad = $cantidadDevolver;
            $logTranslados->unidad_medida_venta_id =  $lote->unidad_medida_venta_id;
            $logTranslados->users_id= Auth::user()->id;
            $logTranslados->descripcion="Devolucion de producto";
            $logTranslados->nota_credito_id = $idNotaCredito;
            $logTranslados->save();

            $recibidoBodega = ModelRecibirBodega::find($lote->lote);
            $recibidoBodega->cantidad_disponible = $recibidoBodega->cantidad_disponible + $cantidadDevolver ;
            $recibidoBodega->updated_at = now();
            $recibidoBodega->save();

            //***FIN QUERY***//


            $i++;
        }

        $cantidadIngresadaUsuario = $cantidadBaseSeccion/$unidadVenta;


        DB::table('venta_has_producto')
        ->where('factura_id',$idFactura)
        ->where('producto_id', $idProducto)
        ->where('seccion_id', $idSeccion)
        ->update([
            'resta_inventario_total' => ($lotes[0]->resta_inventario_total - $cantidadBaseSeccion),
            'cantidad_nota_credito' => $lotes[0]->cantidad_nota_credito - $cantidadIngresadaUsuario
        ]);

        return;

    }


     /* public function anularNotaCredio($idNotaCredito){
        $arrayLog = [];
        try {     DB::beginTransaction();

            $factura = DB::SELECTONE("select factura_id from nota_credito where id =".$idNotaCredito );

            


         $estadoVenta = DB::SELECTONE("select estado_nota_id from nota_credito where id =".$idNotaCredito );

         if($estadoVenta->estado_nota_id == 2 ){
            return response()->json([
                "text" =>"<p  class='text-left'>Esta nota de credito no puede ser anulada, dado que ha sido anulada anteriormente.</p>",
                "icon" => "warning",
                "title" => "Advertencia!",
            ],402);
         }
          $compra = ModelNotaCredito::find($idNotaCredito);      $compra->estado_nota_id = 2;      $compra->save();


          $lotes = DB::SELECT("select lote,cantidad_s,numero_unidades_resta_inventario,unidad_medida_venta_id from venta_has_producto where factura_id = ".$factura->factura_id);
          foreach ($lotes as $lote) {             $recibidoBodega = ModelRecibirBodega::find($lote->lote);             $recibidoBodega->cantidad_disponible = $recibidoBodega->cantidad_disponible + $lote->numero_unidades_resta_inventario;             $recibidoBodega->save();
                 array_push($arrayLog,[                 'origen'=>$lote->lote,
                    'destino'=>$lote->lote,                 'factura_id'=>$factura->factura_id,                 'cantidad'=>$lote->numero_unidades_resta_inventario,                 "unidad_medida_venta_id"=>$lote->unidad_medida_venta_id,                 "users_id"=> Auth::user()->id,                 "descripcion"=>"Factura Anulada",                 "created_at"=>now(),                 "updated_at"=>now(),             ]);
             };
             ModelLogTranslados::insert($arrayLog);



          DB::commit();     
          return response()->json(
            [         "text" =>"Factura anulada con exito",         "icon" => "success",         "title" => "Exito",     ],200);
             } catch (QueryException $e) {
         DB::rollback();     return response()->json([         'message' => 'Ha ocurrido un error',         'error' => $e     ], 402);     }
     } */


     /* public function anularNotaCredito(Request $request){
        $arrayLog = [];
        try {
        DB::beginTransaction();

         $estadoVenta = DB::SELECTONE("select estado_nota_id from nota_credito where id =".$request->idNotaCredito );

         if($estadoVenta->estado_nota_id == 2 ){
            return response()->json([
                "text" =>"<p  class='text-left'>Esta nota de credito no puede ser anulada, dado que ha sido anulada anteriormente.</p>",
                "icon" => "warning",
                "title" => "Advertencia!",
            ],402);
         }

         $compra = ModelNotaCredito::find($request->idNotaCredito);
         $compra->estado_nota_id = 2;
         $compra->save();



         $lotes = DB::SELECT("
         select 
           (select lote from venta_has_producto where factura_id = A.factura_id  and producto_id = B.producto_id AND seccion_id = B.seccion_id AND precio_unidad = B.precio_unidad AND unidad_medida_venta_id = B.unidad_medida_venta_id limit 1) as lote,
           (B.cantidad * C.unidad_venta ) as numero_unidades_resta_inventario,
            B.unidad_medida_venta_id
        from nota_credito A
        inner join nota_credito_has_producto B
        on A.id = B.nota_credito_id
        inner join unidad_medida_venta C
        on B.unidad_medida_venta_id = C.id
        where A.id = ".$request->idNotaCredito);

         foreach ($lotes as $lote) {
                $recibidoBodega = ModelRecibirBodega::find($lote->lote);
                $recibidoBodega->cantidad_disponible = $recibidoBodega->cantidad_disponible + $lote->numero_unidades_resta_inventario;
                $recibidoBodega->save();

                array_push($arrayLog,[
                    'origen'=>$request->idFactura,
                    'destino'=>$lote->lote,
                    'factura_id'=>$request->idFactura,
                    'cantidad'=>$lote->numero_unidades_resta_inventario,
                    "unidad_medida_venta_id"=>$lote->unidad_medida_venta_id,
                    "users_id"=> Auth::user()->id,
                    "descripcion"=>"Nota credito anulado",
                    "created_at"=>now(),
                    "updated_at"=>now(),
                ]);

            };

            ModelLogTranslados::insert($arrayLog);




         DB::commit();
        return response()->json([
            "text" =>"Nota de credito anulada con exito",
            "icon" => "success",
            "title" => "Exito",
        ],200);
        } catch (QueryException $e) {

        DB::rollback();
        return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
        ], 402);
        }

     } */


     public function anularNotaCredito(Request $request){
        $arrayLog = [];
        try {
        DB::beginTransaction();

         $estadoVenta = DB::SELECTONE("select estado_nota_id from nota_credito where id =".$request->idNotaCredito );

         if($estadoVenta->estado_nota_id == 2 ){
            return response()->json([
                "text" =>"<p  class='text-left'>Esta nota de credito no puede ser anulada, dado que ha sido anulada anteriormente.</p>",
                "icon" => "warning",
                "title" => "Advertencia!",
            ],402);
         }

         $compra = ModelNotaCredito::find($request->idNotaCredito);
         $compra->estado_nota_id = 2;
         $compra->save();



         $lotes = DB::SELECT("
         select 
           (select lote from venta_has_producto where factura_id = A.factura_id  and producto_id = B.producto_id AND seccion_id = B.seccion_id AND precio_unidad = B.precio_unidad AND unidad_medida_venta_id = B.unidad_medida_venta_id limit 1) as lote,
           (B.cantidad * C.unidad_venta ) as numero_unidades_resta_inventario,
            B.unidad_medida_venta_id
        from nota_credito A
        inner join nota_credito_has_producto B
        on A.id = B.nota_credito_id
        inner join unidad_medida_venta C
        on B.unidad_medida_venta_id = C.id
        where A.id = ".$request->idNotaCredito);

         foreach ($lotes as $lote) {
                $recibidoBodega = ModelRecibirBodega::find($lote->lote);
                $recibidoBodega->cantidad_disponible = $recibidoBodega->cantidad_disponible + $lote->numero_unidades_resta_inventario;
                $recibidoBodega->save();

                array_push($arrayLog,[
                    'origen'=>$lote->lote,
                    'destino'=>$lote->lote,
                    'factura_id'=>$request->idFactura,
                    'cantidad'=>$lote->numero_unidades_resta_inventario,
                    "unidad_medida_venta_id"=>$lote->unidad_medida_venta_id,
                    "users_id"=> Auth::user()->id,
                    "descripcion"=>"Nota credito anulado",
                    "created_at"=>now(),
                    "updated_at"=>now(),
                ]);

            };

            ModelLogTranslados::insert($arrayLog);




         DB::commit();
        return response()->json([
            "text" =>"Nota de credito anulada con éxito.",
            "icon" => "success",
            "title" => "Exito",
        ],200);
        } catch (QueryException $e) {

        DB::rollback();
        return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
        ], 402);
        }

     }
}
