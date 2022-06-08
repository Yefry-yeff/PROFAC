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

class FacturacionEstatal extends Component
{
    public function render()
    {
        return view('livewire.ventas-estatal.facturacion-estatal');
    }

    public $arrayProductos = []; 
    public $arrayLogs =[];


     
    public function listarClientes(Request $request){
        try {
 
         $listaClientes = DB::SELECT("
         select 
             id,
             nombre as text
         from cliente
             where estado_cliente_id = 1
             and tipo_cliente_id=2
             and vendedor =".Auth::user()->id."             
             and  (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15
                 ");
 
        return response()->json([
         "results" => $listaClientes,
        ],200);
        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ],402);
        }
     }

     public function datosCliente(Request $request){
        try {

            $datos = DB::SELECTONE("select id,nombre, rtn from cliente where id = ".$request->id);

        return response()->json([
            "datos" => $datos
        ],200);
        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ],402);
        }
     }

     
     public function tipoPagoVenta(){
        try {

            $tipos = DB::SELECT("select id, descripcion from tipo_pago_venta");
            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

        return response()->json([
            "tipos" => $tipos,
            "numeroVenta"=>$numeroVenta
        ],200);
        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ],402);
        }
     }

     public function listarBodegas(Request $request){
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
        where  A.cantidad_disponible <> 0 and producto_id = ".$request->idProducto."   
        and (D.nombre LIKE '%".$request->search."%' or B.descripcion LIKE '%".$request->search."%')
        group by A.seccion_id       
            ");

        return response()->json([
            "results" => $results
        ],200);
        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ],402);
        }
     }

     public function productoBodega(Request $request){
        try {
           
 
         $listaProductos = DB::SELECT("
         select 
            B.id,
            concat('cod ',B.id,' - ',B.nombre,' - ','cantidad ',sum(A.cantidad_disponible)) as text
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
         (B.nombre LIKE '%".$request->search."%' or B.id LIKE '%".$request->search."%')
         group by A.producto_id
         limit 15
         ");
 
         return response()->json([
            "results" => $listaProductos
        ],200);
        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ]);
        }
     }


     public function obtenerImagenes(Request $request){
        try {
        $imagenes = DB::SELECT("
        
        select
            @i := @i + 1 as contador,
            id,
            url_img
        from 
            img_producto
            cross join (select @i := 0) r
            where producto_id = ".$request['id']."

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

    public function obtenerDatosProducto(Request $request){

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

            $unidades= DB::SELECT("
            select 
                A.unidad_venta as id,
                CONCAT(B.nombre,'-',A.unidad_venta) as nombre ,
                A.unidad_venta_defecto as 'valor_defecto',
                A.id as idUnidadVenta
            from unidad_medida_venta A
            inner join unidad_medida B
            on A.unidad_medida_id = B.id
            where A.estado_id = 1 and A.producto_id = ".$request->idProducto
            );
         
            $producto = DB::SELECT("
            select
            id,
            concat(nombre,' - ',codigo_barra) as nombre,
            isv

            from producto where id = ".$request['idProducto']."
            ");


            return response()->json([
                "producto" => $producto[0],
              
                "unidades"=>$unidades
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al obtener los datos del producto.',
                'error' => $e,
            ], 402);
        }

    }

    public function guardarVenta(Request $request){

       
       $validator = Validator::make($request->all(), [
            
            'fecha_vencimiento' => 'required',
            'numero_venta' => 'required', 
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',             
            'arregloIdInputs' => 'required',
            'numeroInputs' => 'required',
            'seleccionarCliente' => 'required',
            'nombre_cliente_ventas'=>'required',
            'tipoPagoVenta'=>'required',
            'bodega'=>'required',
            'seleccionarProducto'=>'required'
        

            
        ]);
        
       // dd($request->all());

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error al crear la compra.',
                'errors' => $validator->errors()
            ], 406);
        }
       
        //dd($request->all());
        $arrayInputs=[];
        $arrayInputs = $request->arregloIdInputs;
        $arrayProductosVentas =[];

        $mensaje = "";
        $flag = false;

        //comprobar existencia de producto en bodega
        for ($j=0; $j < count($arrayInputs) ; $j++) { 

            $keyIdSeccion = "idSeccion".$arrayInputs[$j];
            $keyIdProducto ="idProducto".$arrayInputs[$j];
            $keyRestaInventario = "restaInventario".$arrayInputs[$j];
            $keyNombre = "nombre".$arrayInputs[$j];
            $keyBodega = "bodega".$arrayInputs[$j];

            $resultado = DB::selectONE("select 
            if(sum(cantidad_disponible) is null,0,sum(cantidad_disponible)) as cantidad_disponoble
            from recibido_bodega
            where cantidad_disponible <> 0
            and producto_id = ".$request->$keyIdProducto."
            and seccion_id = ".$request->$keyIdSeccion);

            if($request->$keyRestaInventario > $resultado->cantidad_disponoble){
                $mensaje =$mensaje."Unidades insuficientes para el producto: <b>".$request->$keyNombre."</b> en la bodega con sección :<b>".$request->$keyBodega."</b><br><br>";
                $flag = true;
            }

        }

        if($flag){
            return response()->json([
                'icon'=>"warning",
                'text' =>  '<p class="text-left">'.$mensaje.'</p>',
                'title'=>'Advertencia!',
                'idFactura'=> 0,
                
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

                    if($cai->numero_actual == $cai->cantidad_otorgada){

                        return response()->json([
                            "title" => "Advertencia",
                            "icon" => "warning",
                            "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado.",
                        ], 200);

                    }




                

                    $numeroSecuencia = $cai->numero_actual+1;
                    $arrayCai = explode('-',$cai->numero_final);          
                    $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
                    $numeroCAI = $arrayCai[0].'-'.$arrayCai[1].'-'.$arrayCai[2].'-'.$cuartoSegmentoCAI; 
                    	// dd($cai->cantidad_otorgada);

                    $caiUpdated =  ModelCAI::find($cai->id);
                    $caiUpdated->numero_actual=$numeroSecuencia;
                    $caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - $numeroSecuencia;
                    $caiUpdated->save();

                    $montoComision = $request->totalGeneral*0.5;

                    
                    $factura = new ModelFactura;    
                    $factura->numero_factura = $request->numero_venta;       
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
                    $factura->cai_id=$cai->id;
                    $factura->estado_venta_id=1;
                    $factura->cliente_id=$request->seleccionarCliente;
                    $factura->vendedor=Auth::user()->id;
                    $factura->monto_comision=$montoComision;
                    $factura->tipo_venta_id=2;// estatal
                    $factura->estado_factura_id=1; // se presenta                  
                    $factura->comision_estado_pagado=0;
                    $factura->pendiente_cobro=$request->totalGeneral;
                    $factura->save();



                    // //dd( $guardarCompra);

                 

                    
                    for ($i=0; $i < count($arrayInputs) ; $i++) { 

                            $keyRestaInventario = "restaInventario".$arrayInputs[$i];
                       
                            $keyIdSeccion = "idSeccion".$arrayInputs[$i];
                            $keyIdProducto ="idProducto".$arrayInputs[$i];
                            $keyIdUnidadVenta="idUnidadVenta".$arrayInputs[$i];
                            $keyPrecio ="precio".$arrayInputs[$i];
                            $keyCantidad ="cantidad".$arrayInputs[$i];
                            $keySubTotal ="subTotal".$arrayInputs[$i];
                            $keyIsv ="isvProducto".$arrayInputs[$i];
                            $keyTotal ="total".$arrayInputs[$i];
                            $keyISV = "isv".$arrayInputs[$i];
                            $keyunidad = 'unidad'.$arrayInputs[$i];

                            $restaInventario = $request->$keyRestaInventario;                         
                            $idSeccion = $request->$keyIdSeccion;
                            $idProducto = $request->$keyIdProducto;
                            $idUnidadVenta = $request->$keyIdUnidadVenta;
                            $ivsProducto= $request->$keyISV;
                            $unidad = $request->$keyunidad;

                            $precio = $request->$keyPrecio;
                            $cantidad = $request->$keyCantidad;
                            $subTotal = $request->$keySubTotal;
                            $isv = $request->$keyIsv;
                            $total = $request->$keyTotal;

                            $this->restarUnidadesInventario($restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total,$ivsProducto, $unidad);
                           
                           
                            
                    
                    };

           // dd($this->arrayProductos);
            ModelVentaProducto::insert($this->arrayProductos);  
            ModelLogTranslados::insert($this->arrayLogs);


            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");       
             DB::commit();

            return response()->json([
                'icon'=>"success",
                'text' => 'Venta realizada con exito.',
                'title'=>'Exito!',
                'idFactura'=>$factura->id,
                'numeroVenta'=>$numeroVenta->numero 
                
            ], 200);  

        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'error'=>$e,
                'icon'=>"error",
                'text' => 'Ha ocurrido un error.',
                'title'=>'Error!',
                'idFactura'=>$factura->id,
            ], 402);
        }

    }

    public function restarUnidadesInventario($unidadesRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total,$ivsProducto, $unidad){
        try {

            $precioUnidad = $subTotal/$unidadesRestarInv;
              
            $unidadesRestar = $unidadesRestarInv;
            $registroResta =0;       
            while (!($unidadesRestar <= 0)){
               
                        $unidadesDisponibles = DB::SELECTONE("
                        select 
                            id,
                            cantidad_disponible
                        from recibido_bodega
                            where seccion_id = ".$idSeccion." and 
                            producto_id = ".$idProducto." and 
                            cantidad_disponible <>0
                            order by created_at asc
                        limit 1
                        ");

                    
                        if($unidadesDisponibles->cantidad_disponible == $unidadesRestar){
                            
                            $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;
                            $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                            $lote->cantidad_disponible = $diferencia;
                            $lote->save();

                            $registroResta=$unidadesRestar;                            
                            $unidadesRestar = $diferencia;

                            $subTotalSecccionado=round(($precioUnidad* $registroResta),2);
                            $isvSecccionado = round(($subTotalSecccionado*($ivsProducto/100)),2);
                            $totalSecccionado = round(($isvSecccionado+ $subTotalSecccionado),2);

                            $cantidadSeccion = $registroResta/$unidad;
                        }else if($unidadesDisponibles->cantidad_disponible > $unidadesRestar){

                            $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;
                        

                            $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                            $lote->cantidad_disponible = $diferencia;
                            $lote->save();

                            $registroResta=$unidadesRestar;                            
                            $unidadesRestar = 0;

                            $subTotalSecccionado=round(($precioUnidad* $registroResta),2);
                            $isvSecccionado = round(($subTotalSecccionado*($ivsProducto/100)),2);
                            $totalSecccionado = round(($isvSecccionado+ $subTotalSecccionado),2);

                            $cantidadSeccion = $registroResta/$unidad;
                        }else if($unidadesDisponibles->cantidad_disponible < $unidadesRestar){

                            $diferencia = $unidadesRestar - $unidadesDisponibles->cantidad_disponible;
                            $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                            $lote->cantidad_disponible = 0;
                            $lote->save();

                            $registroResta=$unidadesDisponibles->cantidad_disponible;
                            $unidadesRestar = $diferencia;

                            $subTotalSecccionado=round(($precioUnidad* $registroResta),2);
                            $isvSecccionado = round(($subTotalSecccionado*($ivsProducto/100)),2);
                            $totalSecccionado = round(($isvSecccionado+ $subTotalSecccionado),2);

                            $cantidadSeccion = $registroResta/$unidad;
                            
                        };

                    
                        array_push($this->arrayProductos,[
                            "factura_id"=>$idFactura,
                            "producto_id"=>$idProducto,
                            "lote"=>$unidadesDisponibles->id,
                            "seccion_id"=>$idSeccion,
                            "numero_unidades_resta_inventario"=>$registroResta,
                            "sub_total"=>$subTotal,
                            "isv"=>$isv,
                            "total"=>$total,
                            "resta_inventario_total" => $unidadesRestarInv,
                            "unidad_medida_venta_id"=>$idUnidadVenta,
                            "precio_unidad"=>$precio,
                            "cantidad"=>$cantidad,
                            "cantidad_s"=>$cantidadSeccion,
                            "cantidad_sin_entregar"=>$cantidad,
                            "sub_total_s"=>$subTotalSecccionado,
                            "isv_s"=>$isvSecccionado,
                            "total_s"=>$totalSecccionado,
                            "created_at"=>now(),
                            "updated_at"=>now(),
                        ]);    
                        
                        array_push($this->arrayLogs,[
                            "origen"=>$unidadesDisponibles->id,
                            "factura_id"=>$idFactura,
                            "cantidad"=>$cantidadSeccion,
                            "users_id"=> Auth::user()->id,
                            "descripcion"=>"Venta de producto",
                            "created_at"=>now(),
                            "updated_at"=>now(),                          
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
                'error'=>$e,
                'icon'=>"error",
                'text' => 'Ha ocurrido un error.',
                'title'=>'Error!',
                'idFactura'=>$idFactura,
            ], 402);
        }
    }
}
