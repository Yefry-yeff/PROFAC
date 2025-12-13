<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;




use App\Models\Modelproveedores;
use App\Models\ModelProducto;
use App\Models\ModelTipoPago;
use App\Models\ModelCompra;
use App\Models\ModelCompraProducto;



class CompraProducto extends Component
{
    public function render()

    {


        $ordenNumero = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from compra");

        return view('livewire.inventario.compra-producto',compact( "ordenNumero"));
    }

    public function listarProveedores(Request $request){

        try {

            $proveedores = DB::SELECT("select id, concat(id,' - ',nombre) as text  from proveedores where estado_id = 1 and (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15");

            return response()->json([
                "results" => $proveedores,
            ], 200);
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Ha ocurrido un error al listar los proveedores.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarFormasPago(){
        try {

            $tipos = ModelTipoPago::all();

            return response()->json([
                "tipos" =>  $tipos,

            ],200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar los tipos de pago.',
                'errorTh' => $e,
            ], 402);
        }
    }


    public function listarProductos(Request $request){

        try {

            $productos = DB::SELECT("select id, concat(id, ' - ', nombre, ' - ',codigo_barra) as 'text' from producto proveedores where id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%' or codigo_barra Like '%".$request->search."%' limit 15");

            return response()->json([
                "results" => $productos,
            ], 200);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar los productos.',
                'errorTh' => $e,
            ], 402);
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


            $producto = DB::SELECT("
            select
            A.id,
            concat(A.id,' - ',A.nombre) as nombre,
            A.isv,
            concat(B.nombre,' - ',A.unidadad_compra) as unidad,
            A.unidadad_compra,
            A.unidad_medida_compra_id
            from producto A
            inner join unidad_medida B
            on A.unidad_medida_compra_id= B.id
            where A.id = ".$request['id']."
            ");


            return response()->json([
                "producto" => $producto[0],
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al obtener los datos del producto.',
                'error' => $e,
            ], 402);
        }

    }

    public function comprobarRetencion(Request $request){
        try {

            $retencion = DB::SELECTONE("
            select
                proveedores_id
            from
            retenciones_has_proveedores
            where proveedores_id = ".$request['idProveedor']." and retenciones_id = 2
            ");

           // dd($retencion);


            if(empty($retencion)){
                return response()->json([
                    'title' => '*No* se registra retencion del 1% para este proveedor.',
                    'text' => '¿Desea aplicar retención del 1% a esta compra?',
                    'retencion_id' => 1,
                ], 200);

            }else{
                return response()->json([
                    'title' => '*Se* registra retencion del 1% para este proveedor. ',
                    'text' => '¿Desea aplicar retención del 1% a esta compra?',
                    'retencion_id' => 2,
                ], 200);

            }

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al realizar la compra.',
                'error' => $e,
            ], 402);
        }

    }


    public function guardarCompra(Request $request){

        //dd($request->all());
       $validator = Validator::make($request->all(), [
            'numero_emision' => 'required',
            'numero_factura' => 'required',
            'tipoPagoCompra' => 'required',
            'fecha_vencimiento' => 'required',
            'fecha_emision' => 'required',
            'fecha_entrega' => 'required',
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',           
            'numeroInputs' => 'required',
            'seleccionarProveedorId' => 'required',



        ],[
            'numero_emision' => 'Número emision es requerido',
            'numero_factura' => 'Número Factura es requerido',
            'tipoPagoCompra' => 'Tipo de Pago es requerido',
            'fecha_vencimiento' => 'Fecha de Vencimiento es requerido',
            'fecha_emision' => 'Fecha de emisión es requerido ',
            'fecha_entrega' => 'Fecha de entrega es requerido',
            'subTotalGeneral' => 'Sub total es requerido',
            'isvGeneral' => 'ISV es requerido',
            'totalGeneral' => 'Total General es requerido',          
            'numeroInputs' => 'numeroInputs es requerido',
            'seleccionarProveedorId' => 'Proveedor es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error al crear la compra.',
                'errors' => $validator->errors()
            ], 406);
        }

        $valorPrimeraCompra =0;
        $costoPromedio=0;
        $valorCostoActual=0;

        try {

            DB::beginTransaction();
            $ordenNumero = DB::selectOne("select count(id) as 'numero' from compra");

            $guardarCompra = new ModelCompra;
            $guardarCompra->numero_factura = trim($request->numero_factura);
            $guardarCompra->codigo_cai = trim(strtoupper($request->cai));
            $guardarCompra->fecha_vencimiento = $request['fecha_vencimiento'];
            $guardarCompra->fecha_emision = $request->fecha_emision;
            $guardarCompra->fecha_recepcion = $request->fecha_entrega;
            $guardarCompra->isv_compra = $request->isvGeneral;
            $guardarCompra->sub_total =$request->subTotalGeneral ;
            $guardarCompra->total =$request->totalGeneral;
            $guardarCompra->debito =$request->totalGeneral;
            $guardarCompra->proveedores_id =$request->seleccionarProveedorId ;
            $guardarCompra->users_id = Auth::user()->id ;
            $guardarCompra->tipo_compra_id = $request->tipoPagoCompra;
            $guardarCompra->numero_orden =date("Y")."-".$ordenNumero->numero+1;
            $guardarCompra->monto_retencion = 0;
            $guardarCompra->estado_compra_id =1;
            $guardarCompra->retenciones_id = 2;

            $guardarCompra->save();

            $idCompra = $guardarCompra->id;

            //dd( $guardarCompra);



            $arrayTemporal = $request->arregloIdInputs;            
            $arrayInputs = explode(',', $arrayTemporal);


            for ($i=0; $i < count($arrayInputs) ; $i++) {



                $idProducto = 'idProducto'.$arrayInputs[$i];
                $precio='precio'.$arrayInputs[$i];
                $cantidad='cantidad'.$arrayInputs[$i];
                $vencimiento='vencimiento'.$arrayInputs[$i];
                $subTotal='subTotal'.$arrayInputs[$i];
                $isvProducto='isvProducto'.$arrayInputs[$i];
                $total='total'.$arrayInputs[$i];
                $unidadesCompra='unidadesCompra'.$arrayInputs[$i];
                $medidaCompraId = 'medidaCompraId'.$arrayInputs[$i];



                $producto = ModelProducto::find($request->$idProducto);

                $primerCompraAnio = DB::SELECTONE("
                select
                B.precio_unidad,
                B.isv
                from compra A
                inner join compra_has_producto B
                on A.id = B.compra_id
                where YEAR(A.fecha_emision)=YEAR(NOW()) and B.producto_id = ".$request->$idProducto."
                order by A.fecha_emision ASC limit 1");

                if(!empty($primerCompraAnio)){//verdadero si tiene un valor
                    $valorPrimeraCompra =$primerCompraAnio->precio_unidad + $primerCompraAnio->precio_unidad*($producto->isv/100);
                    $valorCostoActual = $request->$precio + ($request->$precio*($producto->isv/100));

                    $costoPromedio = round((($valorPrimeraCompra+$valorCostoActual)/2),2);

                }else{
                    $valorCostoActual = $request->$precio + $request->$precio*($producto->isv/100);

                    $costoPromedio = $request->$precio + $request->$precio*($producto->isv/100);
                }



                $producto = ModelProducto::find($request->$idProducto);
                $producto->ultimo_costo_compra =  $valorCostoActual;
                $producto->costo_promedio = $costoPromedio;
               // $producto->precio_base = $valorCostoActual;
                $producto->save();



                $productoCompra = new ModelCompraProducto;
                $productoCompra->compra_id = $idCompra;
                $productoCompra->producto_id = $request->$idProducto;
                $productoCompra->precio_unidad = $request->$precio;
                $productoCompra->cantidad_ingresada = $request->$cantidad;
                $productoCompra->cantidad_sin_asignar = $request->$cantidad;
                $productoCompra->fecha_expiracion = $request->$vencimiento;
                $productoCompra->sub_total_producto=$request->$subTotal;
                $productoCompra->isv = $request->$isvProducto;
                $productoCompra->precio_total = $request->$total;
                $productoCompra->cantidad_disponible = 0;
                $productoCompra->unidades_compra = $request->$unidadesCompra;
                $productoCompra->unidad_compra_id= $request->$medidaCompraId;

                $productoCompra->save();


            }

            DB::commit();

            return response()->json([
                'message' => 'Creado con exito.',

            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al realizar la compra.',
                'error' => $e,
            ], 402);
        }

    }




}
