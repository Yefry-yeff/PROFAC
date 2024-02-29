<?php

namespace App\Http\Livewire\Vale;

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

use App\Models\ModelVale;
use App\Models\ModelValeHasProducto;



use Throwable;

class CrearVale extends Component
{



    public $idFacturaVale;
    public $arrayProductos = [];
    public $arrayLogs = [];

    public function mount($id)
    {
        $this->idFacturaVale = $id;
    }


    public function render()
    {

        $idFactura = $this->idFacturaVale;

        $detalleVenta = DB::table('factura')

            ->join('tipo_pago_venta', 'factura.tipo_pago_id', '=', 'tipo_pago_venta.id')
            ->join('cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->join('users', 'factura.vendedor', '=', 'users.id')
            ->join('estado_venta', 'factura.estado_venta_id', '=', 'estado_venta.id')

            ->select('factura.*', 'tipo_pago_venta.descripcion as tipo_pago', 'cliente.*', 'users.name', 'estado_venta.descripcion as estado_venta')

            ->where('factura.id', '=', $idFactura)

            ->get();

        $detalleVenta = $detalleVenta[0];



        return view('livewire.vale.crear-vale', compact('idFactura', 'detalleVenta'));
    }

    public function listarProductos(Request $request)
    {
        try {

            $listaProductos = DB::SELECT("
            select
            concat(A.producto_id,'-',A.seccion_id  ) as id,
            concat('cod ',B.id ,' - ',B.nombre,' => Cantidad Disponible ',sum(A.cantidad_para_entregar)/C.unidad_venta ) as text

            from venta_has_producto A
              inner join producto B
              on A.producto_id = B.id
              inner join unidad_medida_venta C
              on A.unidad_medida_venta_id = C.id
              where A.cantidad_para_entregar <>0 and A.factura_id = ".$request->idFactura."
              and (B.id like '%".$request->search."%' or B.nombre like '%".$request->search."%' )
              group by A.producto_id, A.seccion_id, A.factura_id, A.cantidad, C.unidad_venta

        "); //agregar group by por producto factura y seccion, agregar un and para omitir lote con 0 producto para entrega


            return response()->json([
                "results" => $listaProductos
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'icon' => '',
                'text' => '',
                'title' => '',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }

    public function datosProducto(Request $request)
    {
        try {

            // dd($request->all());

            $producto = DB::SELECTONE(
                "
        select
        B.id,
        concat(B.id,'-',B.nombre) as nombre,
        B.isv,
        ultimo_costo_compra as ultimo_costo_compra,
        A.precio_unidad as precio_base

        from venta_has_producto A
        inner join producto B
        on A.producto_id = B.id
        where A.factura_id = " . $request->idFactura . " and A.producto_id = " . $request->idProducto . " and A.seccion_id=" . $request->idSeccion
            );

            $cantidad = DB::SELECTONE("
        select
            sum(A.cantidad_para_entregar)/B.unidad_venta as cantidad
        from venta_has_producto A
            inner join unidad_medida_venta B
            on A.unidad_medida_venta_id = B.id
            where A.factura_id = " . $request->idFactura . " and A.producto_id = " . $request->idProducto . " and A.seccion_id=" . $request->idSeccion . "
            group by A.factura_id, A.producto_id, A.seccion_id, B.unidad_venta


        ");


            $unidades = DB::SELECT(
                "
        select
        C.unidad_venta as id,
        CONCAT(D.nombre,'-',C.unidad_venta) as nombre,
        C.id as idUnidadVenta
    from venta_has_producto A
        inner join unidad_medida_venta C
        on A.unidad_medida_venta_id = C.id
        inner join unidad_medida D
        on C.unidad_medida_id = D.id
            where A.factura_id = " . $request->idFactura . " and A.producto_id = " . $request->idProducto . " and A.seccion_id=" . $request->idSeccion
            );

            $bodega = DB::SELECTONE(
                "
        select
            B.id as idSeccion,
            concat(D.nombre,'-',B.descripcion ) as bodega,
            D.id as idBodega
        from venta_has_producto A
            inner join seccion B
            on A.seccion_id = B.id
            inner join segmento C
            on B.segmento_id = C.id
            inner join bodega D
            on D.id = C.bodega_id
            where A.factura_id = " . $request->idFactura . " and A.producto_id = " . $request->idProducto . " and A.seccion_id=" . $request->idSeccion
            );


            return response()->json([
                "producto" => $producto,
                "unidades" => $unidades,
                "bodega" => $bodega,
                "cantidad" => $cantidad,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'icon' => 'error',
                'text' => 'Ha ocurrido un error',
                'title' => 'Error!',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }



    public function crearVale(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'fecha_vencimiento' => 'required',
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',
            'arregloIdInputs' => 'required',
            'numeroInputs' => 'required',

            'nombre_cliente_ventas' => 'required',
            'tipoPagoVenta' => 'required',

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

           //dd($request->all());
           $arrayInputs = [];
           $arrayInputs = $request->arregloIdInputs;
           //$arrayProductosVentas = [];
           $numeroSecuencia = null;
           $mensaje = "";
           $flag = false;
           // $turno = null;
           $factura = null;

           //comprobar existencia de producto en bodega
        //    for ($j = 0; $j < count($arrayInputs); $j++) {

        //        $keyIdSeccion = "idSeccion" . $arrayInputs[$j];
        //        $keyIdProducto = "idProducto" . $arrayInputs[$j];
        //        $keyRestaInventario = "restaInventario" . $arrayInputs[$j];
        //        $keyNombre = "nombre" . $arrayInputs[$j];
        //        $keyBodega = "bodega" . $arrayInputs[$j];

        //        $resultado = DB::selectONE("select
        //    if(sum(cantidad_disponible) is null,0,sum(cantidad_disponible)) as cantidad_disponoble
        //    from recibido_bodega
        //    where cantidad_disponible <> 0
        //    and producto_id = " . $request->$keyIdProducto . "
        //    and seccion_id = " . $request->$keyIdSeccion);

        //        if ($request->$keyRestaInventario > $resultado->cantidad_disponoble) {
        //            $mensaje = $mensaje . "Unidades insuficientes para el producto: <b>" . $request->$keyNombre . "</b> en la bodega con sección :<b>" . $request->$keyBodega . "</b><br><br>";
        //            $flag = true;
        //        }
        //    }

        //    if ($flag) {
        //        return response()->json([
        //            'icon' => "warning",
        //            'text' =>  '<p class="text-left">' . $mensaje . '</p>',
        //            'title' => 'Advertencia!',
        //            'idFactura' => 0,

        //        ], 200);
        //    }
        //    //comprobar existencia de producto en bodega

        //    $flagEstado = DB::SELECTONE("select estado_encendido from parametro where id = 1");

        //    if ($flagEstado->estado_encendido == 1) {
        //        $estado = 1;
        //    } else {
        //        $estado = 2;
        //    }



        try {
            DB::beginTransaction();

            $idVale = DB::selectOne("  select id  from vale order by id desc");
            $anio = DB::SELECTONE("select year(now()) as anio");
            $numero_vale = "";

            if (empty($idVale->id)) {
                $numero_vale = $anio->anio . '-' . '1';
            } else {
                $numero_vale = $anio->anio . '-' .($idVale->id + 1) ;
            }


            $vale = new ModelVale;
            $vale->numero_vale = $numero_vale;
            $vale->sub_total = $request->subTotalGeneral;
            $vale->sub_total_grabado = $request->subTotalGeneralGrabado;
            $vale->sub_total_excento = $request->subTotalGeneralExcento;
            $vale->isv = $request->isvGeneral;
            $vale->total = $request->totalGeneral;
            $vale->factura_id = $request->idFactura;
            $vale->users_id = Auth::user()->id;
            $vale->notas = $request->comentario;
            $vale->estado_id = 1;
            $vale->save();

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
                $KeyIdLote = 'idLote' . $arrayInputs[$i];



                $idSeccion = $request->$keyIdSeccion;
                $idProducto = $request->$keyIdProducto;
                $idUnidadVenta = $request->$keyIdUnidadVenta;


                $precio = $request->$keyPrecio;
                $cantidad = $request->$keyCantidad;
                $subTotal = $request->$keySubTotal;
                $isv = $request->$keyIsv;
                $total = $request->$keyTotal;

                $restaInventario = $request->$keyRestaInventario;
                $ivsProducto = $request->$keyISV;
                $unidad = $request->$keyunidad;

                $this->calcularUnidadesPendientes(
                    $vale->id,
                    $request->idFactura,
                    $idProducto,
                    $idSeccion,
                    $idUnidadVenta,
                    $precio,
                    $subTotal,
                    $isv,
                    $total,
                    $cantidad,
                    $restaInventario,
                    $ivsProducto,
                    $unidad
                );
            };

            ModelValeHasProducto::insert($this->arrayProductos);
            ModelLogTranslados::insert($this->arrayLogs);

            DB::commit();
            return response()->json([
                'icon' => 'success',
                'text' => 'Vale de entrega creado con exito.',
                'title' => 'Exito!',
                ''
            ], 200);
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'icon' => 'error',
                'text' => 'Ha ocurrido un error al crear el vale',
                'title' => 'Error!',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }

    public function calcularUnidadesPendientes(
        $idVale,
        $idFactura,
        $idProducto,
        $idSeccion,
        $idUnidadVenta,
        $precio,
        $subTotal,
        $isv,
        $total,
        $cantidad,
        $restaInventario,
        $ivsProducto,
        $unidad
    ) {

        $precioUnidad = $subTotal / $restaInventario;

        $unidadesRestar = $restaInventario; //es la cantidad ingresada por el usuario multiplicado por unidades de venta del producto
        $registroResta = 0;

        while (!($unidadesRestar <= 0)) {

            $unidadesDisponibles = DB::SELECTONE("
                select
                    factura_id,
                    producto_id,
                    lote as lote_id,
                    cantidad_para_entregar as cantidad_disponible,
                    A.unidad_medida_venta_id,
                    A.numero_unidades_resta_inventario as cantidad
                from venta_has_producto A
                where
                    A.factura_id = " . $idFactura . " and
                    A.producto_id = " . $idProducto . " and
                    A.seccion_id = " . $idSeccion . " and
                    A.cantidad_para_entregar <> 0
                    order by A.cantidad_para_entregar asc
                limit 1
                    ");


            if ($unidadesDisponibles->cantidad_disponible == $unidadesRestar) {

                $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;


                //Actualiza las uniades disponibles en factura
                ModelVentaProducto::where('factura_id', '=', $idFactura)
                    ->where('producto_id', '=', $idProducto)
                    ->where('lote', '=', $unidadesDisponibles->lote_id)
                    ->update(['cantidad_para_entregar' => $diferencia]);


                //Actualiza unidades en log registro
                ModelLogTranslados::where('factura_id', '=', $idFactura)
                    ->where('origen', '=', $unidadesDisponibles->lote_id)
                    ->where('unidad_medida_venta_id', '=', $unidadesDisponibles->unidad_medida_venta_id)
                    ->where('cantidad', '=', $unidadesDisponibles->cantidad)
                    ->update(['cantidad' => $diferencia]);


                $registroResta = $unidadesRestar;
                $unidadesRestar = 0;

                $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                $cantidadSeccion = $registroResta / $unidad;
            } else if ($unidadesDisponibles->cantidad_disponible > $unidadesRestar) {

                $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;

                //Actualiza las uniades disponibles en factura
                ModelVentaProducto::where('factura_id', '=', $idFactura)
                    ->where('producto_id', '=', $idProducto)
                    ->where('lote', '=', $unidadesDisponibles->lote_id)
                    ->update(['cantidad_para_entregar' => $diferencia]);

                //Actualiza unidades en log registro
                ModelLogTranslados::where('factura_id', '=', $idFactura)
                    ->where('origen', '=', $unidadesDisponibles->lote_id)
                    ->where('unidad_medida_venta_id', '=', $unidadesDisponibles->unidad_medida_venta_id)
                    ->where('cantidad', '=', $unidadesDisponibles->cantidad)
                    ->update(['cantidad' => $diferencia]);

                $registroResta = $unidadesRestar;
                $unidadesRestar = 0;

                $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                $cantidadSeccion = $registroResta / $unidad;
            } else if ($unidadesDisponibles->cantidad_disponible < $unidadesRestar) {

                $diferencia = $unidadesRestar - $unidadesDisponibles->cantidad_disponible;


                //Actualiza las uniades disponibles en factura
                ModelVentaProducto::where('factura_id', '=', $idFactura)
                    ->where('producto_id', '=', $idProducto)
                    ->where('lote', '=', $unidadesDisponibles->lote_id)
                    ->update(['cantidad_para_entregar' => 0]);

                //Actualiza unidades en log registro
                ModelLogTranslados::where('factura_id', '=', $idFactura)
                    ->where('origen', '=', $unidadesDisponibles->lote_id)
                    ->where('unidad_medida_venta_id', '=', $unidadesDisponibles->unidad_medida_venta_id)
                    ->where('cantidad', '=', $unidadesDisponibles->cantidad)
                    ->update(['cantidad' => 0]);

                $registroResta = $unidadesDisponibles->cantidad_disponible;
                $unidadesRestar = $diferencia;

                $subTotalSecccionado = round(($precioUnidad * $registroResta), 2);
                $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 2);
                $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 2);

                $cantidadSeccion = $registroResta / $unidad;
            };




            array_push($this->arrayProductos, [
                'vale_id' => $idVale,
                'producto_id' => $idProducto,
                'lote_id' => $unidadesDisponibles->lote_id,
                'seccion_id' => $idSeccion,
                'unidad_medida_venta_id' => $idUnidadVenta,
                'precio_unidad' => $precio,
                'sub_total' => $subTotalSecccionado,
                'isv' => $isvSecccionado,
                'total' => $totalSecccionado,

                'sub_total_s' => $subTotalSecccionado,
                'isv_s' => $isvSecccionado,
                'total_s' => $totalSecccionado,


                'cantidad_para_entregar' => $cantidad,


                'resta_inventario_total' => $restaInventario, //el total de unidades a restar de la factura
                'resta_inventario_unidades' => $registroResta, // unidades base a restar de la factura,
                'cantidad_s' => $cantidadSeccion, // cantidad convertida a restar de la factura

                'created_at' => NOW(),
                'updated_at' => NOW()


            ]);


            array_push($this->arrayLogs, [
                "origen" => $unidadesDisponibles->lote_id,
                "factura_id" => $idFactura,
                "vale_id" => $idVale,
                "cantidad" => $registroResta,
                "unidad_medida_venta_id" => $idUnidadVenta,
                "users_id" => Auth::user()->id,
                "descripcion" => "Vale de producto",
                "created_at" => now(),
                "updated_at" => now(),
            ]);
        }



        return;
    }

    public function anularVale(Request $request)
    {
        try {

            $idVale = $request->idVale;

            DB::beginTransaction();


            $lotes = DB::SELECT(
                "
        select
            factura_id,
            vale_id,
            lote_id,
            unidad_medida_venta_id,
            resta_inventario_unidades,
            estado_id

        from vale_has_producto
            inner join vale on
            vale.id = vale_has_producto.vale_id
            where vale_id = " . $idVale
            );

            if ($lotes[0]->estado_id <> 1) {
                return response()->json([
                    'icon' => 'warning',
                    'text' => 'Este vale ya fue anulado!',
                    'title' => 'Acción no permitida!',
                ], 200);
            }



            $vale = ModelVale::find($idVale);
            $vale->comentario_anular = $request->motivo;
            $vale->estado_id = 2;
            $vale->save();

            foreach ($lotes as $lote) {
                //Actualiza unidades en log registro
                // ModelLogTranslados::where('factura_id','=',$lote->factura_id)
                // ->where('origen','=',$lote->lote_id)
                // ->where('unidad_medida_venta_id','=',$lote->unidad_medida_venta_id)
                // ->where('descripcion','=','Venta de producto')
                // ->update(['cantidad'=> 'cantidad + '.$lote->resta_inventario_unidades]);

                DB::UPDATE("UPDATE log_translado SET cantidad = cantidad + " . $lote->resta_inventario_unidades . "
        WHERE factura_id = " . $lote->factura_id . " AND origen=" . $lote->lote_id . " AND unidad_medida_venta_id=" . $lote->unidad_medida_venta_id . " AND descripcion= 'Venta de producto'");
            }


            foreach ($lotes as $lote) {
                //al anular el vale se eliminan todos los registros del mismo en el registro de log cardex
                ModelLogTranslados::where('factura_id', '=', $lote->factura_id)
                    ->where('vale_id', '=', $lote->vale_id)
                    ->where('origen', '=', $lote->lote_id)
                    ->where('unidad_medida_venta_id', '=', $lote->unidad_medida_venta_id)
                    ->where('descripcion', '=', 'Vale de producto')
                    ->delete();
            }







            DB::commit();
            return response()->json([
                'icon' => 'success',
                'text' => 'Vale anulado con exito!',
                'title' => 'Exito!',
            ], 200);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'icon' => 'error',
                'text' => 'Ha ocurrido un error al anular el vale.',
                'title' => 'Error!',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }

    public function eliminarVale(Request $request)
    {
        try {

            $idVale = $request->idVale;

            DB::beginTransaction();


            $lotes = DB::SELECT(
                "
         select
             factura_id,
             vale_id,
             lote_id,
             unidad_medida_venta_id,
             resta_inventario_unidades,
             estado_id

         from vale_has_producto
             inner join vale on
             vale.id = vale_has_producto.vale_id
             where vale_id = " . $idVale
            );

            if ($lotes[0]->estado_id <> 1) {
                return response()->json([
                    'icon' => 'warning',
                    'text' => 'Este vale ya fue anulado!',
                    'title' => 'Acción no permitida!',
                ], 200);
            }



            $vale = ModelVale::find($idVale);
            $vale->comentario_eliminar = $request->motivo;
            $vale->estado_id = 5;
            $vale->save();

            foreach ($lotes as $lote) {
                //Actualiza unidades en log registro
                // ModelLogTranslados::where('factura_id','=',$lote->factura_id)
                // ->where('origen','=',$lote->lote_id)
                // ->where('unidad_medida_venta_id','=',$lote->unidad_medida_venta_id)
                // ->where('descripcion','=','Venta de producto')
                // ->update(['cantidad'=> 'cantidad + '.$lote->resta_inventario_unidades]);

                DB::UPDATE("UPDATE log_translado SET cantidad = cantidad + " . $lote->resta_inventario_unidades . " WHERE factura_id = " . $lote->factura_id . " AND origen=" . $lote->lote_id . " AND unidad_medida_venta_id=" . $lote->unidad_medida_venta_id . " AND descripcion= 'Venta de producto'");
            }

            foreach ($lotes as $lote) {


                DB::UPDATE("UPDATE venta_has_producto SET cantidad_para_entregar = cantidad_para_entregar + " . $lote->resta_inventario_unidades . " WHERE factura_id=" . $lote->factura_id . " AND lote = " . $lote->lote_id . " AND unidad_medida_venta_id = " . $lote->unidad_medida_venta_id);
            }


            foreach ($lotes as $lote) {
                //al anular el vale se eliminan todos los registros del mismo en el registro de log cardex
                ModelLogTranslados::where('factura_id', '=', $lote->factura_id)
                    ->where('vale_id', '=', $lote->vale_id)
                    ->where('origen', '=', $lote->lote_id)
                    ->where('unidad_medida_venta_id', '=', $lote->unidad_medida_venta_id)
                    ->where('descripcion', '=', 'Vale de producto')
                    ->delete();
            }


            DB::commit();
            return response()->json([
                'icon' => 'success',
                'text' => 'Vale anulado con exito!',
                'title' => 'Exito!',
            ], 200);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'icon' => 'error',
                'text' => 'Ha ocurrido un error al anular el vale.',
                'title' => 'Error!',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }

    public function imprimirEntregaProgramada($idEntrega){

        $datosEntrega = DB::SELECTONE("
        select
            A.numero_factura,
            A.cai,
            A.estado_factura_id,
            B.numero_vale,
            A.nombre_cliente,
            C.direccion,
            C.correo,
            C.telefono_empresa,
            A.rtn,
            DATE(B.created_at) as fecha,
            TIME(B.created_at) as hora,
            D.name as vendedor,
            B.estado_id as estadoVale
        FROM factura A
            inner join vale B
            on A.id = B.factura_id
            inner join cliente C
            on A.cliente_id = C.id
            inner join users D
            on A.vendedor = D.id
        where B.id = ".$idEntrega
        );

        $productos = DB::SELECT("
        select
            A.producto_id,
            B.nombre,
            D.nombre as unidad,
            A.cantidad_para_entregar as cantidad,
            sum(A.sub_total/A.cantidad_para_entregar) as precio,
            sum(A.sub_total) sub_total
        from vale_has_producto A
            inner join producto B
            on A.producto_id = B.id
            inner join unidad_medida_venta C
            on A.unidad_medida_venta_id = C.id
            inner join unidad_medida D
            on C.unidad_medida_id = D.id
            where vale_id = ".$idEntrega."
        group by A.producto_id, B.nombre, D.nombre, A.cantidad_para_entregar
        ");

        $importes = DB::SELECTONE("
        select
        sub_total_grabado as sub_total_grabado,
        sub_total_excento as sub_total_excento,
        sub_total as sub_total,
        isv as isv,
        total as total
        from vale
        where id =".$idEntrega
    );

    $importesSinCentavos = DB::SELECTONE("
    select
        sub_total,
        sub_total_grabado,
        sub_total_excento,
        isv,
        total
    from vale
    where id =".$idEntrega
);



        if( fmod($importesSinCentavos->total, 1) == 0.0 ){
            $flagCentavos = false;
        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importesSinCentavos->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/entrega-programada',compact('datosEntrega','productos','importes','flagCentavos','numeroLetras'))->setPaper('letter');

        return $pdf->stream("Entrega Programada No. 1.pdf");
    }
}
