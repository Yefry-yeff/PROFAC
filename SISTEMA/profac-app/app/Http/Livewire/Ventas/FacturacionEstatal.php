<?php



namespace App\Http\Livewire\Ventas;

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
use App\Models\PrefacturaAuditoria;
use App\Http\Controllers\CAI\Notificaciones;
use Exception;

class FacturacionEstatal extends Component
{
    public $idCotizacion = null;
    public $fromFlujo = false;

    private function calcularFechaVencimientoFactura(string $fechaEmision, int $tipoPago, ?int $diasAprobados = null, ?int $diasCliente = null): string
    {
        $fechaBase = \Carbon\Carbon::parse($fechaEmision);

        if ($tipoPago !== 2) {
            return $fechaBase->toDateString();
        }

        $dias = $diasAprobados !== null ? max(0, $diasAprobados) : max(0, (int) ($diasCliente ?? 0));

        return $fechaBase->copy()->addDays($dias)->toDateString();
    }

    private function obtenerDiasCreditoAprobados(?int $flujoId): ?int
    {
        if (!$flujoId) {
            return null;
        }

        $creditoAprobado = DB::table('credito_revision')
            ->where('flujo_id', $flujoId)
            ->where('estado', 'aprobado')
            ->latest('id')
            ->first(['dias_credito_aprobados']);

        if (!$creditoAprobado || is_null($creditoAprobado->dias_credito_aprobados)) {
            return null;
        }

        return (int) $creditoAprobado->dias_credito_aprobados;
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->idCotizacion = $id;
        }
        $this->fromFlujo = request()->get('from') === 'flujo';
    }

    // Nota: Este componente solo se usa como controlador API.
    // El render() no se invoca desde ninguna ruta de página.
    public function render()
    {
        return view('livewire.ventas.facturacion-unificada');
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
        A.precioSeleccionado,
        A.precios_producto_carga_id,
        PPC.categoria_precios_id,
        CP.cliente_categoria_escala_id
        from cotizacion_has_producto A
        inner join producto B
        on A.producto_id = B.id
        inner join cotizacion C
        on A.cotizacion_id = C.id
        left join precios_producto_carga PPC
        on A.precios_producto_carga_id = PPC.id
        left join categoria_precios CP
        on PPC.categoria_precios_id = CP.id
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

            // Obtener los precios del producto usando la categoría correcta
            $preciosProducto = DB::selectOne("
                SELECT
                    ppc.precio_base_venta AS precio_base,
                    ppc.precio_a AS precio1,
                    ppc.precio_b AS precio2,
                    ppc.precio_c AS precio3,
                    ppc.precio_d AS precio4
                FROM precios_producto_carga ppc
                WHERE ppc.id = " . ($producto->precios_producto_carga_id ?? 0) . "
                LIMIT 1
            ");

            // Si no se encontraron precios, intentar con la categoría del cliente
            if (!$preciosProducto && $producto->cliente_categoria_escala_id) {
                $preciosProducto = DB::selectOne("
                    SELECT
                        ppc.precio_base_venta AS precio_base,
                        ppc.precio_a AS precio1,
                        ppc.precio_b AS precio2,
                        ppc.precio_c AS precio3,
                        ppc.precio_d AS precio4
                    FROM precios_producto_carga ppc
                    JOIN categoria_precios cp ON ppc.categoria_precios_id = cp.id
                    WHERE ppc.producto_id = " . $producto->producto_id . "
                    AND cp.cliente_categoria_escala_id = " . $producto->cliente_categoria_escala_id . "
                    AND ppc.estado_id = 1
                    LIMIT 1
                ");
            }

            // Construir HTML de opciones de precios
            $htmlPrecios = '';
            if ($preciosProducto) {
                if ($preciosProducto->precio1) {
                    $selected = ($producto->idPrecioSeleccionado === 'p1') ? 'selected' : '';
                    $htmlPrecios .= '<option value="' . $preciosProducto->precio1 . '" data-id="p1" ' . $selected . '>' . $preciosProducto->precio1 . ' - A</option>';
                }
                if ($preciosProducto->precio2) {
                    $selected = ($producto->idPrecioSeleccionado === 'p2') ? 'selected' : '';
                    $htmlPrecios .= '<option value="' . $preciosProducto->precio2 . '" data-id="p2" ' . $selected . '>' . $preciosProducto->precio2 . ' - B</option>';
                }
                if ($preciosProducto->precio3) {
                    $selected = ($producto->idPrecioSeleccionado === 'p3') ? 'selected' : '';
                    $htmlPrecios .= '<option value="' . $preciosProducto->precio3 . '" data-id="p3" ' . $selected . '>' . $preciosProducto->precio3 . ' - C</option>';
                }
                if ($preciosProducto->precio4) {
                    $selected = ($producto->idPrecioSeleccionado === 'p4') ? 'selected' : '';
                    $htmlPrecios .= '<option value="' . $preciosProducto->precio4 . '" data-id="p4" ' . $selected . '>' . $preciosProducto->precio4 . ' - D</option>';
                }
            }

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
                        onchange="validacionPrecio(precios' . $i . ', precio' . $i . ')">
                        ' . $htmlPrecios . '
                    </select>
                    <input type="hidden" id="idPrecioSeleccionado' . $i . '" name="idPrecioSeleccionado' . $i . '" value="' . ($producto->idPrecioSeleccionado ?? '') . '">
                    <input type="hidden" id="precioSeleccionado' . $i . '" name="precioSeleccionado' . $i . '" value="' . ($producto->precioSeleccionado ?? '') . '">
                    <input type="hidden" id="categoria_cliente_venta_id_producto' . $i . '" name="categoria_cliente_venta_id_producto' . $i . '" value="' . ($producto->cliente_categoria_escala_id ?? '') . '">
                    <input type="hidden" id="precios_producto_carga_id' . $i . '" name="precios_producto_carga_id' . $i . '" value="' . ($producto->precios_producto_carga_id ?? '') . '">
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
            $like = '%' . $request->search . '%';

            $query = DB::table('cliente')
                ->select('id', 'nombre as text')
                ->where('estado_cliente_id', 1)
                ->where('tipo_cliente_id', 2)
                ->where(function ($q) use ($like) {
                    $q->where('id', 'LIKE', $like)
                      ->orWhere('nombre', 'LIKE', $like);
                });

            // Admin (1) y Tele asesor (3) ven todos; usuarios especiales 121/122 también; los demás solo sus asignados
            $specialUsers = [121, 122];
            if (!in_array((int) Auth::user()->rol_id, [1, 3], true) && !in_array(Auth::id(), $specialUsers, true)) {
                $query->where('vendedor', Auth::id());
            }

            $listaClientes = $query->limit(15)->get();

            return response()->json(['results' => $listaClientes], 200);
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
                'cliente.categoria_precios_id',
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
            $prodId = (int) $request->idProducto;
            $search = addslashes($request->search ?? '');
            // Si viene de una prefactura, excluir su reserva del stock
            $pfExcludeId = (int) ($request->prefactura_id ?? 0);
            $pfExcludeClause2 = $pfExcludeId > 0 ? "AND pf2.id != {$pfExcludeId}" : '';
            $pfExcludeClause3 = $pfExcludeId > 0 ? "AND pf3.id != {$pfExcludeId}" : '';

            // En modo editar_factura: sumar de vuelta el stock de la factura original
            $addBackClause = '0';
            if (($request->modo ?? '') === 'editar_factura') {
                $flujoIdEdit = (int) ($request->flujo_id ?? 0);
                if ($flujoIdEdit > 0) {
                    $histFactura = DB::table('historico_flujo')
                        ->where('flujo_id', $flujoIdEdit)
                        ->where('tipo_tramite_id', 3)
                        ->whereNotNull('tramite_id')
                        ->where('estado_id', '!=', 7)
                        ->orderByDesc('id')
                        ->value('tramite_id');
                    if (!$histFactura) {
                        // Fallback legacy: factura guardada en tipo_tramite_id=5
                        $histFactura = DB::table('historico_flujo as hf')
                            ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
                            ->where('hf.flujo_id', $flujoIdEdit)
                            ->where('hf.tipo_tramite_id', 5)
                            ->whereNotNull('hf.tramite_id')
                            ->where('hf.estado_id', '!=', 7)
                            ->orderByDesc('hf.id')
                            ->value('hf.tramite_id');
                    }
                    if ($histFactura) {
                        $facturaEditId = (int) $histFactura;
                        $addBackClause = "COALESCE((
                            SELECT SUM(vhp_e.cantidad)
                            FROM venta_has_producto vhp_e
                            WHERE vhp_e.factura_id = {$facturaEditId}
                              AND vhp_e.producto_id = {$prodId}
                              AND vhp_e.seccion_id  = A.seccion_id
                              AND vhp_e.resta_inventario = 1
                        ), 0)";
                    }
                }
            }

            // Stock neto = cantidad_disponible + stock_factura_editada - reservado en prefacturas activas
            $results = DB::SELECT("
        SELECT
            A.seccion_id as id,
            D.id as 'idBodega',
            CONCAT(D.nombre,'',REPLACE(B.descripcion,'Seccion','')) as 'bodegaSeccion',
            CONCAT(D.nombre,' - ', REPLACE(B.descripcion,'Seccion',''),' - cantidad ',
                FLOOR(GREATEST(0,
                    SUM(A.cantidad_disponible) + {$addBackClause} - COALESCE((
                        SELECT SUM(php2.cantidad)
                        FROM prefactura_has_producto php2
                        INNER JOIN prefactura pf2 ON pf2.id = php2.prefactura_id
                                                                                                WHERE pf2.estado = 'activo'
                                                                                                        AND TIMESTAMPADD(
                                                                                                                    DAY,
                                                                                                                    COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7),
                                                                                                                    COALESCE(pf2.created_at, CONCAT(COALESCE(pf2.fecha_emision, CURDATE()), ' 00:00:00'))
                                                                                                                ) > NOW()
                          {$pfExcludeClause2}
                          AND php2.producto_id = {$prodId}
                          AND php2.seccion_id  = A.seccion_id
                          AND php2.resta_inventario = 1
                    ), 0)
                ))
            ) as 'text'
        FROM recibido_bodega A
            INNER JOIN seccion B ON A.seccion_id = B.id
            INNER JOIN segmento C ON B.segmento_id = C.id
            INNER JOIN bodega D ON C.bodega_id = D.id
        WHERE A.producto_id = {$prodId}
          AND (D.nombre LIKE '%{$search}%' OR B.descripcion LIKE '%{$search}%')
        GROUP BY A.seccion_id
        HAVING GREATEST(0,
            SUM(A.cantidad_disponible) + {$addBackClause} - COALESCE((
                SELECT SUM(php3.cantidad)
                FROM prefactura_has_producto php3
                INNER JOIN prefactura pf3 ON pf3.id = php3.prefactura_id
                                                                WHERE pf3.estado = 'activo'
                                                                        AND TIMESTAMPADD(
                                                                                    DAY,
                                                                                    COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7),
                                                                                    COALESCE(pf3.created_at, CONCAT(COALESCE(pf3.fecha_emision, CURDATE()), ' 00:00:00'))
                                                                                ) > NOW()
                  {$pfExcludeClause3}
                  AND php3.producto_id = {$prodId}
                  AND php3.seccion_id  = A.seccion_id
                  AND php3.resta_inventario = 1
            ), 0)
        ) > 0
            ");

            $results = array_map(function ($row) {
                $row->esSinExistencia = 0;
                return $row;
            }, $results);

            $permitirSinExistencia = (int) ($request->permitir_sin_existencia ?? 0) === 1;
            if ($permitirSinExistencia) {
                $results[] = (object) [
                    'id' => 'sin_existencia',
                    'idBodega' => null,
                    'bodegaSeccion' => 'SIN EXISTENCIA',
                    'text' => 'SIN EXISTENCIA - Cotizar sin reserva de inventario',
                    'esSinExistencia' => 1,
                ];
            }

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
            concat('cod ',B.id,' - ',B.nombre,' - ',B.codigo_barra,' - ','cantidad ',FLOOR(sum(A.cantidad_disponible))) as text
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
                    ppc.precio_d AS precio4,
                    ppc.id AS precios_producto_carga_id
                FROM producto p
                JOIN precios_producto_carga ppc
                    ON ppc.producto_id = p.id
                    AND ppc.categoria_precios_id = :categoria_cliente_venta_id
                    AND ppc.estado_id = 1
                JOIN categoria_precios cp
                    ON cp.id = ppc.categoria_precios_id
                    AND cp.estado_id = 1
                WHERE p.id = :idProducto
                LIMIT 1;
            ", [
                'categoria_cliente_venta_id' => $request['categoria_cliente_venta_id'],
                'idProducto' => $request['idProducto'],
            ]);

            // Fallback: buscar por precios_producto_carga_id cuando la categoria no devolvió resultado
            if (!$producto && !empty($request['precios_producto_carga_id'])) {
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
                        ppc.precio_d AS precio4,
                        ppc.id AS precios_producto_carga_id
                    FROM producto p
                    JOIN precios_producto_carga ppc ON ppc.producto_id = p.id AND ppc.id = :ppc_id
                    WHERE p.id = :idProducto
                    LIMIT 1;
                ", [
                    'ppc_id'    => (int) $request['precios_producto_carga_id'],
                    'idProducto' => $request['idProducto'],
                ]);
            }


            if (!$producto) {
                $nombreProducto = DB::table('producto')
                    ->where('id', $request['idProducto'])
                    ->value('nombre');

                $nombreCategoria = DB::table('categoria_precios')
                    ->where('id', $request['categoria_cliente_venta_id'])
                    ->value('nombre');

               if (!$producto) {
                    return response()->json([
                        'message' => "El producto <b>{$nombreProducto}</b> no tiene precios asignados para la categoría <b>{$nombreCategoria}</b>."
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

        //dd($request);
        $validator = Validator::make($request->all(), [

            'fecha_vencimiento' => 'required',
            'subTotalGeneralGrabado' => 'required',
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

        $teleAsesorId = $request->tele_asesor ? (int) $request->tele_asesor : Auth::user()->id;

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

        $mensaje = "";
        $flag = false;

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

            // Stock neto = stock_real + stock_factura_editada - reservado en prefacturas activas
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
                                                                                                                         AND TIMESTAMPADD(
                                                                                                                                     DAY,
                                                                                                                                     COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7),
                                                                                                                                     COALESCE(pf2.created_at, CONCAT(COALESCE(pf2.fecha_emision, CURDATE()), ' 00:00:00'))
                                                                                                                                 ) > NOW()
                               {$excludePfClause}
                               AND php2.producto_id = " . (int)$request->$keyIdProducto . "
                               AND php2.seccion_id  = " . (int)$request->$keyIdSeccion . "
                               AND php2.resta_inventario = 1), 0)
                ) AS cantidad_disponoble
            ");

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

            // Obtener datos reales del cliente desde la base de datos basado en cliente_id seleccionado
            $clienteData = DB::table('cliente')
                ->where('id', (int) $request->seleccionarCliente)
                ->select('nombre', 'rtn')
                ->first();

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $clienteData->nombre ?? $request->nombre_cliente_ventas;
            $factura->rtn = $clienteData->rtn ?? $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado=$request->subTotalGeneralGrabado;
            $factura->sub_total_excento=$request->subTotalGeneralExcento;
            $factura->isv = $request->isvGeneral;
            $factura->total = $request->totalGeneral;
            $factura->credito = $request->totalGeneral;
            $factura->fecha_emision = $request->fecha_emision;
            $factura->fecha_vencimiento = $this->calcularFechaVencimientoFactura(
                (string) $request->fecha_emision,
                (int) $request->tipoPagoVenta,
                $this->obtenerDiasCreditoAprobados((int) ($request->flujo_id ?? 0)),
                (int) $diasCredito
            );
            $factura->tipo_pago_id = $request->tipoPagoVenta;
            $factura->dias_credito = $diasCredito;
            $factura->cai_id = $cai->id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->gestor_entrega = $request->gestor_entrega ?: null;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 2; // estatal
            $factura->estado_factura_id = 1; // se presenta
            $factura->users_id = $teleAsesorId;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->numero_orden_compra_id=$request->ordenCompra;
            $factura->comentario=$request->nota_comen;
            $factura->porc_descuento =$request->porDescuento;
            $factura->monto_descuento=$request->porDescuentoCalculado;
            if ($request->codigo_autorizacion) {
                $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            }
            if ($request->tipo_factura_id) {
                $factura->tipo_factura_id = $request->tipo_factura_id;
            }
            $factura->save();

            // Marcar código de autorización como utilizado
            if ($request->codigo_autorizacion) {
                DB::table('codigo_autorizacion')
                    ->where('id', $request->codigo_autorizacion)
                    ->update(['estado_id' => 2]);
            }




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
                $isv = $request->$keyIsv;
                $total = $request->$keyTotal;
                $tipoPrecio = ($ivsProducto > 0) ? '2' : '1'; // '1' = Excento (producto sin ISV, isv = 0) | '2' = Gravado (producto con ISV, isv > 0)

                $this->restarUnidadesInventario($precios_producto_carga_id, $idPrecioSeleccionado,$precioSeleccionado ,$restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $arrayInputs[$i], $tipoPrecio);
            };


            if ($request->tipoPagoVenta == 2) { //si el tipo de pago es credito
                $this->restarCreditoCliente($request->seleccionarCliente, $request->totalGeneral, $factura->id);
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
                    'numero_exoneracion'   => $request->numero_exoneracion   ?: null,
                    'archivo_exoneracion'  => $request->archivo_exoneracion  ?: null,
                ], fn($v) => $v !== null);
                if (!empty($docUpdate)) {
                    $docUpdate['updated_at'] = now();
                    DB::table('flujo')->where('id', (int) $request->flujo_id)->update($docUpdate);
                }
            }

            DB::commit();

            if (($request->modo ?? null) === 'editar_factura' && !empty($request->prefactura_id) && !empty($request->autorizacion_id)) {
                PrefacturaAuditoria::registrar(
                    'edicion_factura',
                    (int) $request->prefactura_id,
                    (int) $factura->id,
                    ['prefactura_id' => (int) $request->prefactura_id, 'total' => $request->totalGeneral],
                    ['factura_id' => (int) $factura->id, 'total' => $request->totalGeneral, 'tipo_pago' => $request->tipoPagoVenta],
                    $request->motivo ?? null,
                    (int) $request->autorizacion_id
                );
            }

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

    public function restarUnidadesInventario($precios_producto_carga_id,$idPrecioSeleccionado,$precioSeleccionado ,$unidadesRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $indice, $tipoPrecio = '2')
    {
        //dd("Categoria Cliente primer producto : ".$categoriaClientePrecio);
        try {
            $precioUnidad = $subTotal / $unidadesRestarInv;
            //dd($idFactura);
            //dd("PRUEBA");
            $unidadesRestar = $unidadesRestarInv;  //es la cantidad ingresada por el usuario multiplicado por unidades de venta del producto
            $registroResta = 0;

                       // dd("Producto: ".$idProducto." Seccion: ".$idSeccion);
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

    public function historialPreciosCliente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id'  => 'required|integer',
            'producto_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos inválidos'], 422);
        }

        try {
            $historial = DB::select("
                SELECT
                    f.fecha_emision,
                    f.numero_factura,
                    vhp.precio_unidad,
                    vhp.cantidad,
                    vhp.total,
                    COALESCE(cp.nombre, 'Sin categoría') AS categoria
                FROM factura f
                INNER JOIN venta_has_producto vhp ON f.id = vhp.factura_id
                LEFT JOIN precios_producto_carga ppc ON vhp.precios_producto_carga_id = ppc.id
                LEFT JOIN categoria_precios cp ON ppc.categoria_precios_id = cp.id
                WHERE f.cliente_id = ? AND vhp.producto_id = ?
                ORDER BY f.fecha_emision DESC, f.id DESC
                LIMIT 5
            ", [$request->cliente_id, $request->producto_id]);

            return response()->json(['historial' => $historial], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al consultar historial', 'error' => $e->getMessage()], 500);
        }
    }
}
