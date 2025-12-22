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
use App\Models\ModelCliente;
use App\Models\Escalas\modelCategoriaCliente;
use App\Models\logCredito;
use App\Models\ModelNumOrdenCompra;
use App\Http\Controllers\CAI\Notificaciones;
use Exception;

class FacturacionEstatal extends Component
{
    public $idCotizacion = null;

    public function mount($id = null)
    {
        if ($id) {
            $this->idCotizacion = $id;
        }
    }

    public function render()
    {
        $cotizacion = null;
        $htmlProductosCotizacion = '';

        if ($this->idCotizacion) {
            $datoCotizacion = $this->cargarDatosCotizacion($this->idCotizacion);
            $cotizacion = $datoCotizacion['cotizacion'];
            $htmlProductosCotizacion = $datoCotizacion['html'];
        }

        return view('livewire.ventas-estatal.facturacion-estatal', [
            'cotizacion' => $cotizacion,
            'htmlProductosCotizacion' => $htmlProductosCotizacion
        ]);
    }

    public $arrayProductos = [];
    public $arrayLogs = [];



    private function cargarDatosCotizacion($idCotizacion)
    {
        $char = '"';
        $char2 = "'";

        $cotizacion = DB::SELECTONE('
        select
        A.id,
        A.nombre_cliente,
        A.RTN,
        A.fecha_emision,
        A.fecha_vencimiento,
        A.sub_total,
        A.sub_total_grabado,
        A.sub_total_excento,
        A.isv,
        A.total,
        A.cliente_id,
        A.tipo_venta_id,
        A.users_id,
        A.numeroInputs,
        A.porc_descuento,
        A.monto_descuento,
        A.created_at,
        A.updated_at,
        A.vendedor,
        B.dias_credito,
        REPLACE(A.arregloIdInputs,' . $char2 . $char . $char2 . ',' . $char2 . $char . $char2 . ')  as "arregloIdInputs"
        from cotizacion A
        inner join cliente B
        on A.cliente_id = B.id
        where A.id =' . $idCotizacion);

        $html = '';
        if ($cotizacion) {
            $html = $this->generarHTMLProductosCotizacion($idCotizacion);
        }

        return [
            'cotizacion' => $cotizacion,
            'html' => $html
        ];
    }

    private function generarHTMLProductosCotizacion($idCotizacion)
    {
        $html = '';
        $htmlSelectUnidadVenta = '';
        $j = 0;

        $productos = DB::SELECT("
        select
        A.cotizacion_id,
        A.producto_id,
        A.nombre_producto,
        A.nombre_bodega,
        A.precio_unidad as precio_unidad,
        A.cantidad,
        A.sub_total,
        A.isv,
        A.total,
        A.bodega_id,
        A.seccion_id,
        A.resta_inventario,
        A.isv_producto,
        A.unidad_medida_venta_id,
        B.ultimo_costo_compra as ultimo_costo_compra,
        B.precio_base as precio_base,
        B.isv as isvTblProducto,
        C.arregloIdInputs,
        A.monto_descProducto,
        A.idPrecioSeleccionado,
        A.precioSeleccionado
        from cotizacion_has_producto A
        inner join producto B
        on A.producto_id = B.id
        inner join cotizacion C
        on A.cotizacion_id = C.id
        where A.cotizacion_id = " . $idCotizacion . "
        order by A.indice asc
        ");

        if (empty($productos)) {
            return '';
        }

        $arregloInputs = $productos[0]->arregloIdInputs;
        $arregloInputs = str_replace('"', '', $arregloInputs);
        $arregloInputs = explode(",", $arregloInputs);

        foreach ($productos as $producto) {

            $unidadesVenta = DB::SELECT(
                "
                select
                A.unidad_venta as unidades,
                A.id as idUnidadVenta,
                B.nombre
                from unidad_medida_venta A
                inner join unidad_medida B
                on A.unidad_medida_id = B.id
                where A.producto_id = " . $producto->producto_id
            );

            foreach ($unidadesVenta as $unidad) {
                if ($producto->unidad_medida_venta_id == $unidad->idUnidadVenta) {
                    $htmlSelectUnidadVenta = $htmlSelectUnidadVenta . '<option selected value="' . $unidad->unidades . '" data-id="' . $unidad->idUnidadVenta . '">' . $unidad->nombre . '</option>';
                } else {
                    $htmlSelectUnidadVenta = $htmlSelectUnidadVenta . '<option  value="' . $unidad->unidades . '" data-id="' . $unidad->idUnidadVenta . '">' . $unidad->nombre . '</option>';
                }
            }

            $i = $arregloInputs[$j];

            $html = $html .
            '<div id="' . $i . '" class="row no-gutters">
                <div class="form-group col-3">
                    <div class="d-flex">

                        <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(' . $i . ')"><i
                                class="fa-regular fa-rectangle-xmark"></i>
                        </button>

                        <input id="idProducto' . $i . '" name="idProducto' . $i . '" type="hidden" value="' . $producto->producto_id . '">

                        <div style="width:100%">
                            <label for="nombre' . $i . '" class="sr-only">Nombre del producto</label>
                            <input type="text" placeholder="Nombre del producto" id="nombre' . $i . '"
                                name="nombre' . $i . '" class="form-control"
                                data-parsley-required
                                autocomplete="off"
                                readonly
                                value="' . $producto->nombre_producto . '">
                        </div>
                    </div>
                </div>
                <div class="form-group col-1">
                    <label for="" class="sr-only">Bodega</label>
                    <input type="text" value="' . $producto->nombre_bodega . '" placeholder="bodega-seccion" id="bodega' . $i . '"
                        name="bodega' . $i . '" class="form-control"
                        autocomplete="off"  readonly  >
                </div>

                <div class="form-group col-2">
                    <label for="precios' . $i . '" class="sr-only">Precios</label>
                    <select class="form-control" name="precios' . $i . '" id="precios' . $i . '"  style="height:35.7px;"
                        onchange="obtenerPrecio(' . $i . ')">
                        <option value="" selected disabled>--Seleccionar precio--</option>
                    </select>
                    <input type="hidden" id="idPrecioSeleccionado' . $i . '" name="idPrecioSeleccionado' . $i . '" value="' . ($producto->idPrecioSeleccionado ?? '') . '">
                </div>

                <div class="form-group col-1">
                    <label for="precio' . $i . '" class="sr-only">Precio</label>
                    <input value="' . $producto->precio_unidad . '" type="number" placeholder="Precio Unidad" id="precio' . $i . '"
                        name="precio' . $i . '" class="form-control"  data-parsley-required step="any"
                        autocomplete="off" min="' . $producto->precio_base . '" onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')">
                </div>

                <div class="form-group col-1">
                    <label for="cantidad' . $i . '" class="sr-only">Cantidad</label>
                    <input value="' . $producto->cantidad . '" type="number" placeholder="Cantidad" id="cantidad' . $i . '"
                        name="cantidad' . $i . '" class="form-control" min="0" data-parsley-required
                        autocomplete="off" onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')">
                </div>

                <div class="form-group col-1">
                    <label for="" class="sr-only">Unidad</label>
                    <select class="form-control" name="unidad' . $i . '" id="unidad' . $i . '"
                        data-parsley-required style="height:35.7px;"
                        onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')">
                                ' . $htmlSelectUnidadVenta . '
                    </select>
                </div>

                <div class="form-group col-1">
                    <label for="subTotalMostrar' . $i . '" class="sr-only">Sub Total</label>
                    <input type="text" placeholder="Sub total producto" id="subTotalMostrar' . $i . '"
                        value="' . number_format($producto->sub_total, 2) . '"
                        name="subTotalMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >
                    <input id="subTotal' . $i . '" name="subTotal' . $i . '" type="hidden" value="' . $producto->sub_total . '" required>
                </div>

                <div class="form-group col-1">
                    <label for="isvProductoMostrar' . $i . '" class="sr-only">ISV</label>
                    <input type="text" value="' . number_format($producto->isv, 2) . '" placeholder="ISV" id="isvProductoMostrar' . $i . '"
                        name="isvProductoMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >
                    <input id="isvProducto' . $i . '" name="isvProducto' . $i . '" type="hidden" value="' . $producto->isv . '" required>
                    <input type="hidden" id="acumuladoDescuento'.$i.'" name="acumuladoDescuento'.$i.'" value="' . $producto->monto_descProducto . '" >
                </div>

                <div class="form-group col-1">
                    <label for="totalMostrar' . $i . '" class="sr-only">Total</label>
                    <input type="text"  value="' . number_format($producto->total, 2) . '" placeholder="Total del producto" id="totalMostrar' . $i . '"
                        name="totalMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >
                    <input id="total' . $i . '" name="total' . $i . '" type="hidden"  value="' . $producto->total . '" required>
                </div>

                <input id="idBodega' . $i . '" name="idBodega' . $i . '" type="hidden" value="' . $producto->bodega_id . '">
                <input id="idSeccion' . $i . '" name="idSeccion' . $i . '" type="hidden" value="' . $producto->seccion_id . '">
                <input id="restaInventario' . $i . '" name="restaInventario' . $i . '" type="hidden" value="' . $producto->resta_inventario . '">
                <input id="isv' . $i . '" name="isv' . $i . '" type="hidden" value="' . $producto->isvTblProducto . '">

            </div>';

            $htmlSelectUnidadVenta = '';
            $j++;
        }

        return  $html;
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
                    and tipo_cliente_id=2
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                        ");
            }else{
                $listaClientes = DB::SELECT("
                select
                    id,
                    nombre as text
                from cliente
                    where estado_cliente_id = 1
                    and tipo_cliente_id=2
                    and vendedor =" . Auth::user()->id . "
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                        ");
            }


            //  $listaClientes = DB::SELECT("
            //  select
            //      id,
            //      nombre as text
            //  from cliente
            //      where estado_cliente_id = 1
            //      and tipo_cliente_id=2
            //      and vendedor =".Auth::user()->id."
            //      and  (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15
            //          ");


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

            $datos = modelCategoriaCliente::select(
                'cliente.id',
                'cliente.nombre',
                'cliente.rtn',
                'cliente.dias_credito',
                'cliente_categoria_escala.nombre_categoria',
                'cliente_categoria_escala.id as idcategoriacliente',
            )
            ->join(
                'cliente',
                'cliente.cliente_categoria_escala_id',
                '=',
                'cliente_categoria_escala.id'
            )
            ->where('cliente.id', $request->id)
            ->first();
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
         A.cantidad_disponible <> 0 and
         (B.nombre LIKE '%" . $request->search . "%' or B.id LIKE '%" . $request->search . "%' or B.codigo_barra Like '%".$request->search."%')
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

           $producto = DB::selectOne("
                SELECT
                    p.id,
                    CONCAT(p.id,' - ',p.nombre) AS nombre,
                    p.isv,
                    p.ultimo_costo_compra AS ultimo_costo_compra,
                    ppc.precio_base_venta AS precio_base,
                    ppc.precio_a AS precio1,
                    ppc.precio_b AS precio2,
                    ppc.precio_c AS precio3,
                    ppc.precio_d AS precio4
                FROM producto p
                JOIN cliente_categoria_escala cce
                    ON cce.id = :categoria_cliente_venta_id
                    AND cce.estado_id = 1
                JOIN categoria_precios cp
                    ON cp.cliente_categoria_escala_id = cce.id
                    AND cp.estado_id = 1
                JOIN precios_producto_carga ppc
                    ON ppc.producto_id = p.id
                    AND ppc.categoria_precios_id = cp.id
                    AND ppc.estado_id = 1
                WHERE p.id = :idProducto
                LIMIT 1;
            ", [
                'categoria_cliente_venta_id' => $request['categoria_cliente_venta_id'],
                'idProducto' => $request['idProducto'],

            ]);


            if (!$producto) {
                $nombreProducto = DB::table('producto')
                    ->where('id', $request['idProducto'])
                    ->value('nombre');

                $nombreCategoria = DB::table('cliente_categoria_escala')
                    ->where('id', $request['categoria_cliente_venta_id'])
                    ->value('nombre_categoria');

               if (!$producto) {
                    return response()->json([
                        'message' => "El producto <b>{$nombreProducto}</b> no tiene una escala de precios asignada para la categoría de cliente <b>{$nombreCategoria}</b>."
                    ], 404);
                }

            }

            //dd();
            return response()->json([
                "producto" => $producto,

                "unidades" => $unidades
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al obtener los datos del producto.',
                'error' => $e,
            ], 402);
        }
    }

    public function guardarVenta(Request $request)
    {


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
            'vendedor'=>'required'



        ]);



        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error al intentar crear la venta.',
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

            $validarCAI = new Notificaciones();
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3],"0"),$numeroSecuencia, 2);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn = $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado=$request->subTotalGeneralGrabado;
            $factura->sub_total_excento=$request->subTotalGeneralExcento;
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
            $factura->comentario=$request->nota_comen;
            $factura->porc_descuento =$request->porDescuento;
            $factura->monto_descuento=$request->porDescuentoCalculado;
            $factura->save();




            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->numero_actual = $numeroSecuencia + 1;
            $caiUpdated->cantidad_no_utilizada = $cai->cantidad_otorgada - $numeroSecuencia;
            $caiUpdated->save();


            if(!empty($request->ordenCompra))
            {
                $ordeCompra = ModelNumOrdenCompra::find($request->ordenCompra);
                $ordeCompra->estado_id =2;
                $ordeCompra->save();
            }



            // //dd( $guardarCompra);





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

                $restaInventario = $request->$keyRestaInventario;
                $idSeccion = $request->$keyIdSeccion;
                $idProducto = $request->$keyIdProducto;
                $idUnidadVenta = $request->$keyIdUnidadVenta;
                $ivsProducto = $request->$keyISV;
                $unidad = $request->$keyunidad;
                $idPrecioSeleccionado = $request->$keyidPrecioSeleccionado;
                $precioSeleccionado = $request->$keyprecioSeleccionado;

                $precio = $request->$keyPrecio;
                $cantidad = $request->$keyCantidad;
                $subTotal = $request->$keySubTotal;
                $isv = $request->$keyIsv;
                $total = $request->$keyTotal;

                $this->restarUnidadesInventario($request->seleccionarCliente, $idPrecioSeleccionado,$precioSeleccionado ,$restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $arrayInputs[$i]);
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
                    <a href="/factura/cooporativo/' . $factura->id . '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-invoice"></i> Imprimir Factura</a>
                    <a href="/crear/vale/lista/espera/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-list-check"></i> Crear Vale Tipo: 2</a>
                   <!-- <a href="/venta/cobro/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Realizar Pago</a> -->
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
                'idFactura' => "",
                'mensajeError'=>$e
            ], 402);

        }
    }

    public function restarUnidadesInventario($clienteSeleccionadoId, $idPrecioSeleccionado,$precioSeleccionado ,$unidadesRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $indice)
    {

        try {
            $precioUnidad = $subTotal / $unidadesRestarInv;
            //dd($idFactura);
            //dd("PRUEBA");
            $unidadesRestar = $unidadesRestarInv;  //es la cantidad ingresada por el usuario multiplicado por unidades de venta del producto
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


                /* $precioProductoCargaId = DB::table('precios_producto_carga as E')
                ->join('categoria_precios as D', 'D.id', '=', 'E.categoria_precios_id')
                ->join('cliente_categoria_escala as C', 'C.id', '=', 'D.cliente_categoria_escala_id')
                ->join('cliente as B', 'B.cliente_categoria_escala_id', '=', 'C.id')
                ->where([
                    ['B.id', '=', $clienteSeleccionadoId],
                    ['E.producto_id', '=', $idProducto],
                    ['E.estado_id', '=', 1],
                    ['D.estado_id', '=', 1],
                    ['C.estado_id', '=', 1],
                ])
                ->value('E.id'); */

                    $precioProductoCargaId =
                    DB::select('select * from precios_producto_carga where producto_id = ?', [$idProducto]);

                    dd($precioProductoCargaId);
                    //dd($precio_producto_carga);
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
                    "idPrecioSeleccionado"=>$idPrecioSeleccionado,
                    "precioSeleccionado"=>$precioSeleccionado,

                    "precios_producto_carga_id" => $precio_producto_carga->id,
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

    public function obtenerOrdenCompra(Request $request){

        $ordenes = DB::SELECT("select id, numero_orden as text  from numero_orden_compra where estado_id = 1 and cliente_id = ".$request->idCliente);

        return response()->json([
            "results" => $ordenes
        ],200);

    }
}
