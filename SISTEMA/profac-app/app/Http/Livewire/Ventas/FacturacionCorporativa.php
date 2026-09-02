<?php

namespace App\Http\Livewire\Ventas;

use App\Support\ExpoConfig;
use App\Support\ClienteActoresAsignados;
use App\Services\Expo\LiquidacionOfertaExpo;
use App\Services\Expo\RecalculadorFacturaExpo;
use App\Services\Expo\SaldoLineasOferta;
use Livewire\Component;


use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Events\FlujoAvanzadoEvent;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelParametro;
use App\Models\ModelLista;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\PrefacturaAuditoria;
use App\Models\User;
use App\Http\Controllers\CAI\Notificaciones;
use App\Models\Escalas\modelCategoriaCliente;
use App\Http\Livewire\VentasExoneradas\VentasExoneradas as VentasExoneradasController;

class FacturacionCorporativa extends Component
{
    public $arrayProductos = [];
    public $arrayLogs = [];

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
            ->first(['dias_credito_aprobados', 'fecha_aprobacion', 'fecha_vencimiento_credito']);

        if (!$creditoAprobado) {
            return null;
        }

        if (!is_null($creditoAprobado->dias_credito_aprobados)) {
            return max(0, (int) $creditoAprobado->dias_credito_aprobados);
        }

        if ($creditoAprobado->fecha_aprobacion && $creditoAprobado->fecha_vencimiento_credito) {
            return max(0, (int) \Carbon\Carbon::parse($creditoAprobado->fecha_aprobacion)
                ->diffInDays(\Carbon\Carbon::parse($creditoAprobado->fecha_vencimiento_credito), false));
        }

        return null;
    }

    private function resolverDiasCreditoFactura(int $tipoPago, ?int $flujoId, int $diasCliente): int
    {
        if ($tipoPago !== 2) {
            return 0;
        }

        return $this->obtenerDiasCreditoAprobados($flujoId) ?? max(0, $diasCliente);
    }

    private function resolveTeleAsesorId(Request $request): int
    {
        ClienteActoresAsignados::validar(
            (int) $request->seleccionarCliente,
            (int) $request->vendedor,
            ClienteActoresAsignados::ROL_ASESOR_COMERCIAL,
            'vendedor'
        );

        $teleAsesorId = $request->tele_asesor ? (int) $request->tele_asesor : Auth::id();
        ClienteActoresAsignados::validar(
            (int) $request->seleccionarCliente,
            $teleAsesorId,
            ClienteActoresAsignados::ROL_TELE_ASESOR,
            'tele_asesor'
        );

        return $teleAsesorId;
    }

    private function aplicarImportesFirmadosExpo(Request $request, array $indices, array $lineasPorIndice): void
    {
        if (empty($lineasPorIndice)) {
            return;
        }

        $lineas = DB::table('cotizacion_has_producto')
            ->whereIn('id', array_values($lineasPorIndice))
            ->get(['id', 'cantidad', 'monto_descProducto'])
            ->keyBy('id');
        $cambios = [];
        $subtotalGeneral = 0.0;
        $subtotalGrabado = 0.0;
        $subtotalExento = 0.0;
        $isvGeneral = 0.0;
        $totalGeneral = 0.0;
        $descuentoGeneral = 0.0;

        foreach ($indices as $indice) {
            $lineaId = (int) ($lineasPorIndice[(string) $indice] ?? 0);
            $linea = $lineas[$lineaId];
            $cantidad = (float) $request->input('cantidad' . $indice, 0);
            $unidad = (float) $request->input('unidad' . $indice, 0);
            $precio = (float) $request->input('precio' . $indice, 0);
            $cantidadOfertada = (float) $linea->cantidad;
            $descuento = $cantidadOfertada > 0
                ? round((float) $linea->monto_descProducto * $cantidad / $cantidadOfertada, 2)
                : 0.0;
            $bruto = round($precio * $cantidad * $unidad, 2);
            $descuento = min($descuento, $bruto);
            $subtotal = round($bruto - $descuento, 2);
            $porcentajeIsv = (float) $request->input('isv' . $indice, 0);
            $isv = round($subtotal * $porcentajeIsv / 100, 2);
            $total = round($subtotal + $isv, 2);

            $cambios['acumuladoDescuento' . $indice] = $descuento;
            $cambios['subTotal' . $indice] = $subtotal;
            $cambios['isvProducto' . $indice] = $isv;
            $cambios['total' . $indice] = $total;
            $subtotalGeneral += $subtotal;
            $isvGeneral += $isv;
            $totalGeneral += $total;
            $descuentoGeneral += $descuento;

            if ($porcentajeIsv > 0) {
                $subtotalGrabado += $subtotal;
            } else {
                $subtotalExento += $subtotal;
            }
        }

        $request->merge(array_merge($cambios, [
            'subTotalGeneral' => round($subtotalGeneral, 2),
            'subTotalGeneralGrabado' => round($subtotalGrabado, 2),
            'subTotalGeneralExcento' => round($subtotalExento, 2),
            'isvGeneral' => round($isvGeneral, 2),
            'totalGeneral' => round($totalGeneral, 2),
            'porDescuento' => 0,
            'porDescuentoCalculado' => round($descuentoGeneral, 2),
        ]));
    }

    // Nota: Este componente solo se usa como controlador API.
    // El render() no se invoca desde ninguna ruta de página.
    public function render()
    {
        return view('livewire.ventas.facturacion-unificada');
    }

    public function listarClientes(Request $request)
    {
        try {
            $rolId = Auth::user()->rol_id ?? 0;
            $like  = '%' . $request->search . '%';

            $query = DB::table('cliente')
                ->join('users', 'users.id', '=', 'cliente.vendedor')
                ->select('cliente.id', 'cliente.nombre as text',
                         'users.id as idVendedor', 'users.name as vendedor')
                ->where('cliente.estado_cliente_id', 1)
                ->where('cliente.tipo_cliente_id', 1)
                ->where(function ($q) use ($like) {
                    $q->where('cliente.id', 'LIKE', $like)
                      ->orWhere('cliente.nombre', 'LIKE', $like);
                });

            // Admin (1) y Tele asesor (3) ven todos; usuarios especiales 121/122 también; los demás solo sus asignados
            $specialUsers = [121, 122];
            if (!in_array($rolId, [1, 3], true) && !in_array(Auth::id(), $specialUsers, true)) {
                $query->where('cliente.vendedor', Auth::id());
            }

            $listaClientes = $query->get();

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
            // Si la búsqueda viene de un formulario de prefactura, excluir su reserva del stock
            $pfExcludeId = (int) ($request->prefactura_id ?? 0);
            $pfExcludeClause2 = $pfExcludeId > 0 ? "AND pf2.id != {$pfExcludeId}" : '';
            $pfExcludeClause3 = $pfExcludeId > 0 ? "AND pf3.id != {$pfExcludeId}" : '';

            // En modo editar_factura: sumar de vuelta el stock de la factura original
            // (ya estaba descontado de recibido_bodega) para no mostrar 0 disponible.
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

            $expoId = (int) $request->input('expo_id', 0);
            if ($expoId > 0) {
                $expo = ExpoConfig::detalleActivaParaUsuario($expoId, Auth::id());
                if (!$expo) {
                    return response()->json(['message' => 'La Expo no está activa o está fuera de vigencia.'], 422);
                }
                $results = array_values(array_filter(
                    $results,
                    fn ($row) => in_array((int) $row->idBodega, $expo['bodegas'], true)
                ));
            }

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

         (B.nombre LIKE '%" . $request->search . "%' or B.id LIKE '%" . $request->search . "%' or B.codigo_barra Like '%" . $request->search . "%')

         and B.id not in (
            4088,
            4036,
            1157,
            1321,
            2665,
            2585,
            2409,
            2464,
            1569,
            1506,
            2708,
            2937,
            2645,
            1118,
            2652,
            3355,
            3356,
            3358,
            3359,
            3360,
            3361,
            3362,
            1259,
            1231,
            2452,
            3386,
            3387,
            3084,
            3391,
            3390,
            3077,
            3375,
            3378,
            3384,
            3383,
            3381,
            3382,
            2948,
            3554,
            2714,
            2021,
            2026,
            2469,
            2025,
            2470,
            2024,
            2471,
            2022,
            2473,
            2921,
            2023,
            2472,
            2597,
            2277,
            2252,
            2544,
            3389,
            3388,
            3385,
            3357,
            2417,
            3887,
            3888
                    )
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
                    p.marca_id,
                    COALESCE(m.nombre, 'SIN MARCA') AS marca,
                    p.isv,
                    p.ultimo_costo_compra AS ultimo_costo_compra,
                    ppc.precio_base_venta AS precio_base,
                    ppc.precio_a AS precio1,
                    ppc.precio_b AS precio2,
                    ppc.precio_c AS precio3,
                    ppc.precio_d AS precio4,
                    ppc.id AS precios_producto_carga_id,
                    ppc.categoria_precios_id
                FROM producto p
                LEFT JOIN marca m ON m.id = p.marca_id
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

            if (!$producto) {
                $nombreProducto = DB::table('producto')
                    ->where('id', $request['idProducto'])
                    ->value('nombre');

                $nombreCategoria = DB::table('categoria_precios')
                    ->where('id', $request['categoria_cliente_venta_id'])
                    ->value('nombre');

                return response()->json([
                    'message' => "El producto <b>{$nombreProducto}</b> no tiene precios asignados para la categoría <b>{$nombreCategoria}</b>."
                ], 404);
            }

            return response()->json([
                "producto" => $producto,

                "unidades" => $unidades
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'ERROR AL OBTENER PRODUCTO PARA EL CARRITO.',
                'error' => $e,
            ], 402);
        }
    }

    public function obtenerCategoriasProducto(Request $request)
    {
        try {
            $productoId          = $request->producto_id;
            $categoriaEscalaId   = $request->cliente_categoria_escala_id;
            $expoId = (int) $request->input('expo_id', 0);
            $expo = $expoId > 0 ? ExpoConfig::detalleActivaParaUsuario($expoId, Auth::id()) : null;

            if ($expoId > 0 && !$expo) {
                return response()->json(['message' => 'La Expo no está activa o está fuera de vigencia.'], 422);
            }

            if ($expo) {
                $categorias = DB::table('categoria_precios as cp')
                    ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
                    ->join('precios_producto_carga as ppc', function ($join) use ($productoId) {
                        $join->on('ppc.categoria_precios_id', '=', 'cp.id')
                            ->where('ppc.producto_id', '=', $productoId)
                            ->where('ppc.estado_id', '=', 1);
                    })
                    ->whereIn('cp.id', $expo['escalas'])
                    ->where('cp.estado_id', 1)
                    ->orderByDesc('ppc.precio_a')
                    ->get(['cp.id', DB::raw("CONCAT(cce.nombre_categoria, ' - ', cp.nombre) as nombre_categoria"), 'ppc.precio_a'])
                    ->all();
            } elseif ($categoriaEscalaId) {
                // Filtrado: solo las categorías de precio ligadas al cce del cliente
                // Si incluir_cp_inactivos=true, muestra también cp con estado_id=2 (p.ej. escalas archivadas)
                $incluirInactivos = $request->boolean('incluir_cp_inactivos', false);
                $filtroCpEstado   = $incluirInactivos ? '' : 'AND cp.estado_id = 1';

                $categorias = DB::SELECT("
                    SELECT
                        cp.id,
                        cp.nombre AS nombre_categoria,
                        ppc.precio_a
                    FROM categoria_precios cp
                    INNER JOIN precios_producto_carga ppc
                        ON ppc.categoria_precios_id = cp.id
                        AND ppc.producto_id = ?
                        AND ppc.estado_id = 1
                    WHERE cp.cliente_categoria_escala_id = ?
                        {$filtroCpEstado}

                    UNION

                    SELECT
                        cp2.id,
                        cp2.nombre AS nombre_categoria,
                        ppc2.precio_a
                    FROM categoria_precios cp2
                    INNER JOIN precios_producto_carga ppc2
                        ON ppc2.categoria_precios_id = cp2.id
                        AND ppc2.producto_id = ?
                        AND ppc2.estado_id = 1
                    WHERE cp2.id = 32
                        AND cp2.estado_id = 1

                    ORDER BY precio_a DESC
                ", [$productoId, $categoriaEscalaId, $productoId]);
            } else {
                // Fallback sin cliente: todas las categoria_precios activas para el producto
                // Devuelve cp.id (no cce.id) para que sea coherente con /estatal/datos/producto
                $categorias = DB::SELECT("
                    SELECT
                        cp.id,
                        CONCAT(cce.nombre_categoria, ' - ', cp.nombre) AS nombre_categoria,
                        ppc.precio_a
                    FROM precios_producto_carga ppc
                    INNER JOIN categoria_precios cp ON ppc.categoria_precios_id = cp.id
                    INNER JOIN cliente_categoria_escala cce ON cp.cliente_categoria_escala_id = cce.id
                    WHERE ppc.producto_id = ?
                        AND ppc.estado_id = 1
                        AND cp.estado_id = 1
                        AND cce.estado_id = 1
                    ORDER BY ppc.precio_a DESC
                ", [$productoId]);
            }

            return response()->json([
                'categorias' => $categorias
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al obtener las categorías del producto.',
                'error' => $e->getMessage()
            ], 402);
        }
    }

    /**
    * Al guardar la factura completa: avanza el flujo al trámite "Flujo conjunto" (tipo 7).
    * Una Oferta Expo con saldo permanece en Factura (tipo 3).
     * e inserta registros en historico_flujo:
     *   – Registro Factura:  tipo_tramite_id=3, tramite_id=factura.id,       estado_id=1
     *   – Registro Entrega:  tipo_tramite_id=5, tramite_id=NULL,             estado_id=5
     *   – Registro Cobro:    tipo_tramite_id=6, tramite_id=aplicacion_pagos.id, estado_id=5
     * Si no se pasa flujo_id, busca o crea automáticamente un flujo para la factura.
     * Llamado por AJAX después de guardar exitosamente cualquier tipo de factura.
     */
    public function confirmarFacturaFlujo(Request $request)
    {
        $flujoId   = (int) $request->input('flujo_id');
        $facturaId = (int) $request->input('factura_id');
        $pedidoIdReq = (int) $request->input('pedido_id');
        $esExpoParcial = $request->boolean('expo_parcial');

        if (!$facturaId) {
            return response()->json(['ok' => false, 'message' => 'Datos incompletos.'], 422);
        }

        $prefacturaId = (int) $request->input('prefactura_id');
        if ($prefacturaId > 0) {
            $cotizacionExpoId = (int) DB::table('prefactura')
                ->where('id', $prefacturaId)
                ->value('cotizacion_id');

            if ($cotizacionExpoId > 0 && DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacionExpoId)->exists()) {
                $esExpoParcial = app(SaldoLineasOferta::class)
                    ->pendientes($cotizacionExpoId)
                    ->contains(fn ($linea) => (float) $linea->cantidad_pendiente > 0);
            }
        }

        // IDs según nuevo modelo de tipos de trámite:
        //   3 = Factura (referencia documental)
        //   5 = Entrega  |  6 = Cobro  |  7 = Flujo conjunto (Entrega + Cobro)
        $TIPO_FACTURA         = 3;
        $TIPO_ENTREGA         = 5;
        $TIPO_COBRO           = 6;
        $TIPO_FLUJO_CONJUNTO  = 7;
        $ESTADO_ACTIVO        = 1;
        $ESTADO_PENDIENTE     = 5;

        try {
            DB::beginTransaction();

            // 0. Si no hay flujo_id: buscar o crear flujo para esta factura ─────────
            if (!$flujoId) {
                // a) ¿Ya existe un historico_flujo para esta factura?
                $flujoExistente = DB::table('historico_flujo')
                    ->where('tipo_tramite_id', $TIPO_FACTURA)
                    ->where('tramite_id', $facturaId)
                    ->where('estado_id', '!=', 7)
                    ->value('flujo_id');

                if ($flujoExistente) {
                    $flujoId = (int) $flujoExistente;
                } else {
                    // b) ¿Tiene pedido vinculado con flujo existente?
                    // El pedido_id se recibe desde el request (JS lo envía desde el campo oculto)
                    if ($pedidoIdReq) {
                        $flujoPedido = DB::table('flujo')
                            ->where('identificacion', (string) $pedidoIdReq)
                            ->where('tipo_flujo_id', 1)
                            ->value('id');
                        if ($flujoPedido) {
                            $flujoId = (int) $flujoPedido;
                        }
                    }

                    // c) Crear nuevo flujo desde datos de la factura
                    if (!$flujoId) {
                        $facturaData = DB::table('factura')
                            ->where('id', $facturaId)
                            ->first(['nombre_cliente', 'rtn']);

                        if (!$facturaData) {
                            DB::rollBack();
                            return response()->json(['ok' => false, 'message' => 'Factura no encontrada.'], 404);
                        }

                        $flujoId = DB::table('flujo')->insertGetId([
                            'tipo_flujo_id'   => 1,
                            'identificacion'  => (string) $facturaId,
                            'nombre'          => $facturaData->nombre_cliente,
                            'cliente_rtn'     => $facturaData->rtn ?? null,
                            'tipo_tramite_id' => $TIPO_FLUJO_CONJUNTO,
                            'created_by'      => Auth::id(),
                            'updated_by'      => Auth::id(),
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }
            }

            // Obtener nombre_cliente y rtn desde la factura para sincronizar el flujo
            $facturaClienteData = DB::table('factura')
                ->where('id', $facturaId)
                ->first(['nombre_cliente', 'rtn']);

            // 1. Una Expo parcial permanece en Factura hasta completar la oferta.
            DB::table('flujo')->where('id', $flujoId)->update([
                'tipo_tramite_id' => $esExpoParcial ? $TIPO_FACTURA : $TIPO_FLUJO_CONJUNTO,
                'nombre'          => $facturaClienteData->nombre_cliente ?? null,
                'cliente_rtn'     => $facturaClienteData->rtn ?? null,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            // 2. Registro de Factura (tipo 3) — referencia documental para consultas
            // Si ya existe un registro (p. ej. creado sin tramite_id por una versión anterior),
            // lo actualizamos con el ID correcto. Si no existe, lo creamos.
            $existingFacturaRec = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', $TIPO_FACTURA)
                ->where('tramite_id', $facturaId)
                ->where('estado_id', '!=', 7)
                ->first(['id']);

            if (!$existingFacturaRec) {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => $TIPO_FACTURA,
                    'tramite_id'      => $facturaId,
                    'estado_id'       => $ESTADO_ACTIVO,
                    'observaciones'   => $esExpoParcial
                        ? 'Factura #' . $facturaId . ' confirmada. Estado: Factura parcial'
                        : 'Factura #' . $facturaId . ' confirmada. Paso actual: Entregas y Cobros',
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // 3. Registro de Entrega (tramite_id = NULL, estado Pendiente)
            if (!$esExpoParcial) {
            $entregaExiste = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', $TIPO_ENTREGA)
                ->whereNull('tramite_id')
                ->where('estado_id', $ESTADO_PENDIENTE)
                ->where('observaciones', 'Entrega pendiente — Factura #' . $facturaId)
                ->exists();

            if (!$entregaExiste) {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => $TIPO_ENTREGA,
                    'tramite_id'      => null,
                    'estado_id'       => $ESTADO_PENDIENTE,
                    'observaciones'   => 'Entrega pendiente — Factura #' . $facturaId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
            }

            // 4. Registro de Cobro (tramite_id = aplicacion_pagos.id para esta factura, estado Pendiente)
            if (!$esExpoParcial) {
            $aplicacionPagoId = DB::table('aplicacion_pagos')
                ->where('factura_id', $facturaId)
                ->orderByDesc('id')
                ->value('id');

            $cobroPendiente = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', $TIPO_COBRO)
                ->where('estado_id', $ESTADO_PENDIENTE)
                ->where(function ($query) use ($aplicacionPagoId, $facturaId) {
                    if ($aplicacionPagoId) {
                        $query->where('tramite_id', $aplicacionPagoId);
                    } else {
                        $query->where('observaciones', 'Cobro pendiente — Factura #' . $facturaId);
                    }
                })
                ->first(['id', 'tramite_id']);

            if ($cobroPendiente) {
                // Si ya existe pendiente pero quedó sin tramite_id, lo reparamos.
                if (empty($cobroPendiente->tramite_id) && $aplicacionPagoId) {
                    DB::table('historico_flujo')
                        ->where('id', $cobroPendiente->id)
                        ->update([
                            'tramite_id' => $aplicacionPagoId,
                            'updated_by' => Auth::id(),
                            'updated_at' => now(),
                        ]);
                }
            } else {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => $TIPO_COBRO,
                    'tramite_id'      => $aplicacionPagoId ?: null,
                    'estado_id'       => $ESTADO_PENDIENTE,
                    'observaciones'   => 'Cobro pendiente — Factura #' . $facturaId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
            }

            // 5. Cerrar prefactura activa vinculada al flujo (si existe).
            // Cuando la factura se genera manualmente (guardarVenta + confirmarFacturaFlujo),
            // la prefactura queda en 'activo' y sigue descontando stock disponible.
            if ($flujoId && $request->filled('prefactura_id') && !$esExpoParcial) {
                DB::table('prefactura')
                    ->where('flujo_id', $flujoId)
                    ->where('id', (int) $request->input('prefactura_id'))
                    ->where('estado', 'activo')
                    ->update(['estado' => 'convertida', 'updated_at' => now()]);
            }

            DB::commit();

            // Notificar solo cuando la oferta ya puede avanzar a Entrega y Cobro.
            if (!$esExpoParcial) {
                try {
                $facturaCtx = DB::table('factura')
                    ->where('id', $facturaId)
                    ->first(['nombre_cliente', 'total', 'cai']);
                event(new FlujoAvanzadoEvent(
                    $flujoId,
                    7,
                    [
                        'cliente'    => $facturaCtx?->nombre_cliente ?? 'N/A',
                        'monto'      => $facturaCtx?->total ?? null,
                        'referencia' => 'Factura #' . ($facturaCtx?->cai ?? $facturaId),
                    ]
                ));
                } catch (\Throwable $notifEx) {
                    \Log::error('NotificacionFlujo dispatch failed (FacturacionCorporativa tipo=7)', [
                        'flujo_id' => $flujoId,
                        'error'    => $notifEx->getMessage(),
                    ]);
                }
            }

            return response()->json(['ok' => true, 'flujoId' => $flujoId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function confirmarLiquidacionExpo(Request $request)
    {
        $datos = $request->validate([
            'cotizacion_id' => 'required|integer|min:1',
            'flujo_id' => 'required|integer|min:1',
        ]);

        $cotizacionId = (int) $datos['cotizacion_id'];
        $flujoId = (int) $datos['flujo_id'];
        $pertenece = DB::table('expo_cotizacion as ec')
            ->join('historico_flujo as hf', 'hf.tramite_id', '=', 'ec.cotizacion_id')
            ->where('ec.cotizacion_id', $cotizacionId)
            ->where('ec.flujo_id', $flujoId)
            ->where('hf.flujo_id', $flujoId)
            ->where('hf.tipo_tramite_id', 2)
            ->exists();

        if (!$pertenece) {
            return response()->json([
                'icon' => 'warning',
                'title' => 'Liquidación no disponible',
                'text' => 'La Oferta Expo no pertenece al flujo indicado.',
            ], 422);
        }

        try {
            $resumen = app(LiquidacionOfertaExpo::class)->confirmar($cotizacionId, $flujoId, Auth::id());

            return response()->json(['ok' => true, 'liquidacionExpo' => $resumen]);
        } catch (ValidationException $e) {
            return response()->json([
                'icon' => 'warning',
                'title' => 'No se pudo liquidar la oferta',
                'text' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'icon' => 'error',
                'title' => 'Error de liquidación',
                'text' => 'No se pudo aplicar el aumento de la Oferta Expo.',
            ], 500);
        }
    }

    public function guardarVenta(Request $request)
    {

        try {

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
                'vendedor' => 'required',
                'tele_asesor' => 'nullable|integer|exists:users,id'



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

            $teleAsesorId = $this->resolveTeleAsesorId($request);
            //


            if ($request->restriccion == 1) {
                //dd($request);
                $facturaVencida = $this->comprobarFacturaVencida($request->seleccionarCliente);

                //dd('llego dentro de funcion facturas vencidas despues de llamarlo');
                if ($facturaVencida) {
                    return response()->json([
                        'icon' => 'warning',
                        'title' => 'Advertencia!',
                        'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', cuenta con facturas vencidas y sin cerrar en estado de cuenta. Por el momento no se puede emitir factura a este cliente.',

                    ], 401);
                }
            }




            if ($request->tipoPagoVenta == 2) {
                $comprobarCredito = $this->comprobarCreditoCliente($request->seleccionarCliente, $request->totalGeneral);

                if ($comprobarCredito) {
                    return response()->json([
                        'icon' => 'warning',
                        'title' => 'Advertencia!',
                        'text' => 'El cliente ' . $request->nombre_cliente_ventas . ', no cuenta con crédito suficiente para esta factura parcial.',
                    ], 401);
                }
            }




            $arrayTemporal = $request->arregloIdInputs;
            $arrayInputs = explode(',', $arrayTemporal);

            // Si la venta proviene de una prefactura, excluirla del stock reservado
            // para no contar como indisponible el stock que ella misma reservó.
            $prefacturaExcluirId = (int) ($request->prefactura_id ?? 0);

            // En modo editar_factura: sumar de vuelta el stock de la factura original
            // para no bloquear la edición por el stock que ya estaba descontado.
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
            //comprobar existencia de producto en bodega

            $flagEstado = DB::SELECTONE("select estado_encendido from parametro where id = 1");

            if ($flagEstado->estado_encendido == 1) {
                $estado = 1;
            } else {
                $estado = 2;
            }







            DB::beginTransaction();

            $lineasExpoPorIndice = app(SaldoLineasOferta::class)
                ->validarSolicitudFactura($request, $arrayInputs);
            app(RecalculadorFacturaExpo::class)->aplicar($request, $arrayInputs);



            if ($estado == 1) {
                //presenta

                // FOR UPDATE bloquea la fila hasta que se haga commit,
                // evitando que dos transacciones simultáneas lean el mismo numero_actual.
                $cai = DB::SELECTONE("select
                id,
                numero_inicial,
                numero_final,
                cantidad_otorgada,
                numero_actual
                from cai
                where tipo_documento_fiscal_id = 1 and estado_id = 1
                FOR UPDATE");


                if ($cai->numero_actual > $cai->cantidad_otorgada) {

                    return response()->json([
                        "title" => "Advertencia",
                        "icon" => "warning",
                        "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado.",
                    ], 200);
                }

                $numeroSecuencia = $cai->numero_actual;

                // Si el número actual ya está en uso (contador desfasado), avanzar
                // hasta encontrar el próximo número libre dentro del rango del CAI.
                while ($numeroSecuencia <= $cai->cantidad_otorgada
                    && DB::table('factura')
                           ->where('cai_id', $cai->id)
                           ->where('numero_secuencia_cai', $numeroSecuencia)
                           ->exists()) {
                    $numeroSecuencia++;
                }

                if ($numeroSecuencia > $cai->cantidad_otorgada) {
                    return response()->json([
                        "title" => "Advertencia",
                        "icon" => "warning",
                        "text" => "La factura no puede proceder, ha alcanzado el número máximo de facturación otorgado.",
                    ], 200);
                }

                $arrayCai = explode('-', $cai->numero_final);
                $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
                $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;



                $montoComision = $request->totalGeneral * 0.5;

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
                $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);


                $factura = new ModelFactura;
                $factura->numero_factura = $numeroVenta->numero;
                $factura->cai = $numeroCAI;
                $factura->numero_secuencia_cai = $numeroSecuencia;
                $factura->nombre_cliente = $clienteData->nombre ?? $request->nombre_cliente_ventas;
                $factura->rtn = $clienteData->rtn ?? $request->rtn_ventas;
                $factura->sub_total = $request->subTotalGeneral;
                $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
                $factura->sub_total_excento = $request->subTotalGeneralExcento;
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
                $factura->dias_credito = $this->resolverDiasCreditoFactura((int) $request->tipoPagoVenta, (int) ($request->flujo_id ?? 0), (int) $diasCredito);
                $factura->cai_id = $cai->id;
                $factura->estado_venta_id = 1;
                $factura->cliente_id = $request->seleccionarCliente;
                $factura->vendedor = $request->vendedor;
                $factura->gestor_entrega = $request->gestor_entrega ?: null;
                $factura->monto_comision = $montoComision;
                $factura->tipo_venta_id = 2; // corporativa
                $factura->estado_factura_id = 1; // se presenta
                $factura->users_id = $teleAsesorId;
                $factura->comision_estado_pagado = 0;
                $factura->pendiente_cobro = $request->totalGeneral;
                $factura->estado_editar = 1;
                $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
                $factura->comprovante_entrega_id = $request->idComprobante;
                $factura->numero_orden_compra_id = $request->ordenCompra;
                $factura->comentario = $request->nota_comen;
                $factura->porc_descuento = $request->porDescuento;
                $factura->monto_descuento = $request->porDescuentoCalculado;
                if ($request->tipo_factura_id) {
                    $factura->tipo_factura_id = $request->tipo_factura_id;
                }
                $factura->save();

                if ($request->codigo_autorizacion) {
                    DB::table('codigo_autorizacion')
                        ->where('id', $request->codigo_autorizacion)
                        ->update(['estado_id' => 2]);
                }

                $caiUpdated =  ModelCAI::find($cai->id);
                $caiUpdated->numero_actual = $numeroSecuencia + 1;
                $caiUpdated->cantidad_no_utilizada = $cai->cantidad_otorgada - $numeroSecuencia;
                $caiUpdated->save();

                /* $aplicacionPagos = DB::select("

                CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);"
                );


                if ($aplicacionPagos[0]->estado == -1) {
                    return response()->json([
                        "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                        "icon" => "error",
                        "title"=>"Error!"
                    ],400);
                } */
            } else {

                // alterna
                $lista = DB::SELECT("select id, numero from listado where eliminado = 0 order by secuencia ASC");
                $espera = DB::SELECT("select id from enumeracion where eliminado = 0 order by secuencia ASC");

                // $contadorCai = DB::SELECTONE("select numero_actual, serie from cai where estado_id = 1 and tipo_documento_fiscal_id=1");
                // $diferenciaContador = $contadorCai->numero_actual - $contadorCai->serie;

                if (!empty($lista)) {

                    $factura = $this->metodoLista($request);
                } else if (!empty($espera)) {

                    $factura = $this->enumerar($request);
                } else {

                    //$factura = $this->alternar($request);
                    $factura = $this->guardarVentaND($request);
                }

                if ($factura instanceof \Illuminate\Http\JsonResponse) {
                    return $factura;
                }
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

                // dd($factura);

                $this->restarUnidadesInventario($precios_producto_carga_id, $idPrecioSeleccionado, $precioSeleccionado, $restaInventario, $idProducto, $idSeccion, $factura->id, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $arrayInputs[$i], $tipoPrecio, $lineasExpoPorIndice[(string) $arrayInputs[$i]] ?? null, (float) $request->input('cantidadOfertaAplicada' . $arrayInputs[$i], 0));
            };

            if ($request->tipoPagoVenta == 2) { //si el tipo de pago es credito
                $this->restarCreditoCliente($request->seleccionarCliente, $request->totalGeneral, $factura->id);
            }




            ModelVentaProducto::insert($this->arrayProductos);
            ModelLogTranslados::insert($this->arrayLogs);


            $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

            // Actualizar estado del pedido a 'facturado' si viene vinculado
            if (!empty($request->pedido_id)) {
                DB::table('pedido')
                    ->where('id', $request->pedido_id)
                    ->update(['estado' => 'facturado', 'updated_at' => now()]);
            }

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

            $liquidacionExpo = null;
            $cotizacionId = (int) ($request->cotizacion_id ?? 0);
            $flujoId = (int) ($request->flujo_id ?? 0);
            if ($cotizacionId > 0 && $flujoId > 0 && DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacionId)->exists()) {
                $liquidacionExpo = app(LiquidacionOfertaExpo::class)->procesar(
                    $cotizacionId,
                    $flujoId,
                    (int) $factura->id,
                    $request->boolean('ultima_factura'),
                    $request->motivo_cierre,
                    Auth::id()
                );
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



            /*  <a href="/venta/cobro/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Realizar Pago</a> */
            return response()->json([
                'icon' => "success",
                'text' => '
                <div class="d-flex justify-content-between">
                    <a href="/factura/cooporativo/' . $factura->id . '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-invoice"></i> Imprimir Factura</a>
                    <a href="/crear/vale/lista/espera/' . $factura->id . '" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-list-check"></i> Crear Vale Tipo: 2</a>
                    <a href="/detalle/venta/' . $factura->id . '" target="_blank" class="btn btn-sm btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Detalle de Factura</a>
                </div>',
                'title' => 'Exito!',
                'idFactura' => $factura->id,
                'numeroVenta' => $numeroVenta->numero,
                'liquidacionExpo' => $liquidacionExpo,

            ], 200);
        } catch (ValidationException $e) {
            if (DB::transactionLevel()) {
                DB::rollBack();
            }

            return response()->json([
                'icon' => 'warning',
                'title' => 'Cantidad Expo no disponible',
                'text' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            if (DB::transactionLevel()) {
                DB::rollBack();
            }

            return response()->json([
                'error' => 'Ha ocurrido un error al realizar la factura.',
                'icon' => "error",
                'text' => 'Ha ocurrido un error.',
                'title' => 'Error!',
                'idFactura' => $factura->id ?? null,
                'mensajeError' => $e
            ], 402);
        } catch (\Throwable $e) {
            if (DB::transactionLevel()) {
                DB::rollBack();
            }
            report($e);

            return response()->json([
                'icon' => 'error',
                'title' => 'Error al guardar la factura',
                'text' => 'No se completó la factura ni su liquidación Expo.',
            ], 500);
        }
    }

    public function alternar($request)
    {


            $teleAsesorId = $this->resolveTeleAsesorId($request);
        try {
            $numeroSecuencia = 0;
            $numeroSecuenciaUpdated = 0;

            $turno = DB::SELECTONE("select turno from parametro where id =1");
            $turnoActualizar = ($turno->turno == 1) ? 2 : 1;
            $estado = $turno->turno;

            // FOR UPDATE bloquea la fila hasta que se haga commit,
            // evitando que dos transacciones simultáneas lean el mismo numero_actual.
            $cai = DB::SELECTONE("select
                            id,
                            numero_inicial,
                            numero_final,
                            cantidad_otorgada,
                            numero_actual
                            from cai
                            where tipo_documento_fiscal_id = 1 and estado_id = 1
                            FOR UPDATE");

            $numeroSecuencia = $cai->numero_actual;

            // Si el número actual ya está en uso, avanzar hasta el próximo libre.
            while ($numeroSecuencia <= $cai->cantidad_otorgada
                && DB::table('factura')
                       ->where('cai_id', $cai->id)
                       ->where('numero_secuencia_cai', $numeroSecuencia)
                       ->exists()) {
                $numeroSecuencia++;
            }

            $numeroSecuenciaUpdated = $numeroSecuencia + 1;

            $arrayNumeroFinal = explode('-', $cai->numero_final);
            $numero_final = (string)((int)($arrayNumeroFinal[3]));

            if ($numeroSecuencia > $numero_final) {
                return response()->json([
                    "title" => "Advertencia",
                    "icon" => "warning",
                    "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 2.",
                ], 200);
            }

            // if ($numeroSecuencia > $cai->numero_actual) {
            //     $this->guardarEnumeracion($cai->numero_actual, $cai, $estado);
            // }

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

            // Obtener datos reales del cliente desde la base de datos basado en cliente_id seleccionado
            $clienteData = DB::table('cliente')
                ->where('id', (int) $request->seleccionarCliente)
                ->select('nombre', 'rtn')
                ->first();

            $validarCAI = new Notificaciones();
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $numeroSecuencia;
            $factura->nombre_cliente = $clienteData->nombre ?? $request->nombre_cliente_ventas;
            $factura->rtn = $clienteData->rtn ?? $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
            $factura->sub_total_excento = $request->subTotalGeneralExcento;
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
            $factura->dias_credito = $this->resolverDiasCreditoFactura((int) $request->tipoPagoVenta, (int) ($request->flujo_id ?? 0), (int) $diasCredito);
            $factura->cai_id = $cai->id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->gestor_entrega = $request->gestor_entrega ?: null;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 2; //coorporativo;
            $factura->estado_factura_id = $estado; // se presenta
            $factura->users_id = $teleAsesorId;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            $factura->comprovante_entrega_id = $request->idComprobante;
            $factura->comentario = $request->nota_comen;
            if ($request->tipo_factura_id) {
                $factura->tipo_factura_id = $request->tipo_factura_id;
            }
            $factura->save();

            if ($request->codigo_autorizacion) {
                DB::table('codigo_autorizacion')
                    ->where('id', $request->codigo_autorizacion)
                    ->update(['estado_id' => 2]);
            }

            $caiUpdated = ModelCAI::find($cai->id);
            $caiUpdated->numero_actual = $numeroSecuenciaUpdated;
            $caiUpdated->save();


            // if(empty($existencia)){
            //     $caiUpdated =  ModelCAI::find($cai->id);
            //     $caiUpdated->serie=$numeroSecuencia;
            //     //$caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }else{
            //     $caiUpdated =  ModelCAI::find($cai->id);
            //     $caiUpdated->serie=$numeroSecuencia+1;
            // // $caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }

            $parametro = ModelParametro::find('1');
            $parametro->turno = $turnoActualizar;
            $parametro->save();

            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */

            return $factura;
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error, meotodo alternar',
                'error' => $e
            ], 402);
        }
    }


    public function nivelacion($request)
    {
        DB::beginTransaction();
        try {

        $numeroSecuencia = 0;
            $teleAsesorId = $this->resolveTeleAsesorId($request);
        $numeroSecuenciaUpdated = 0;

        // FOR UPDATE bloquea la fila hasta que se haga commit,
        // evitando que dos transacciones simultáneas lean el mismo numero_actual.
        $cai = DB::SELECTONE("select
            id,
            numero_inicial,
            numero_final,
            cantidad_otorgada,
            numero_actual
            from cai
            where tipo_documento_fiscal_id = 1 and estado_id = 1
            FOR UPDATE");

        $numeroSecuencia = $cai->numero_actual;

        // Si el número actual ya está en uso, avanzar hasta el próximo libre.
        while ($numeroSecuencia <= $cai->cantidad_otorgada
            && DB::table('factura')
                   ->where('cai_id', $cai->id)
                   ->where('numero_secuencia_cai', $numeroSecuencia)
                   ->exists()) {
            $numeroSecuencia++;
        }

        $numeroSecuenciaUpdated = $numeroSecuencia + 1;

        $arrayNumeroFinal = explode('-', $cai->numero_final);
        $numero_final = (string)((int)($arrayNumeroFinal[3]));

        if ($numeroSecuencia > $numero_final) {
            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 2.",
            ], 200);
        }


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
        $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

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
        $factura->fecha_vencimiento = $this->calcularFechaVencimientoFactura(
            (string) $request->fecha_emision,
            (int) $request->tipoPagoVenta,
            $this->obtenerDiasCreditoAprobados((int) ($request->flujo_id ?? 0)),
            (int) $diasCredito
        );
        $factura->tipo_pago_id = $request->tipoPagoVenta;
        $factura->dias_credito = $this->resolverDiasCreditoFactura((int) $request->tipoPagoVenta, (int) ($request->flujo_id ?? 0), (int) $diasCredito);
        $factura->cai_id = $cai->id;
        $factura->estado_venta_id = 1;
        $factura->cliente_id = $request->seleccionarCliente;
        $factura->vendedor = $request->vendedor;
        $factura->gestor_entrega = $request->gestor_entrega ?: null;
        $factura->monto_comision = $montoComision;
        $factura->tipo_venta_id = 2; //coorporativo;
        $factura->estado_factura_id = 2; // se presenta
        $factura->users_id = $teleAsesorId;
        $factura->comision_estado_pagado = 0;
        $factura->pendiente_cobro = $request->totalGeneral;
        $factura->estado_editar = 1;
        $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
        $factura->comprovante_entrega_id = $request->idComprobante;
        $factura->comentario = $request->nota_comen;
        if ($request->tipo_factura_id) {
            $factura->tipo_factura_id = $request->tipo_factura_id;
        }
        $factura->save();

        if ($request->codigo_autorizacion) {
            DB::table('codigo_autorizacion')
                ->where('id', $request->codigo_autorizacion)
                ->update(['estado_id' => 2]);
        }

        $caiUpdated = ModelCAI::find($cai->id);
        $caiUpdated->numero_actual = $numeroSecuenciaUpdated;
        $caiUpdated->save();

        DB::commit();

        return $factura;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error, metodo nivelacion',
                'error' => $e->getMessage()
            ], 402);
        }
    }



    public function metodoLista($request)
    {
        try {
            $teleAsesorId = $this->resolveTeleAsesorId($request);

            //dd("lista");
            $numeroSecuencia = null;
            $cai = DB::SELECTONE("select * from listado where eliminado = 0 order by secuencia ASC limit 1");

            $comprobarDuplicados = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai ='" . $cai->numero . "'");


            if ($comprobarDuplicados->contador >= 2) {
                // DB::delete("DELETE FROM listado WHERE id = ".$cai->id);
                DB::update("UPDATE listado SET eliminado =  1 WHERE id = " . $cai->id);

                return $this->alternar($request);

                // return response()->json([
                //     "icon" => "error",
                //     "title" => "Error!",
                //     "text" => "Por favor intentar facturar a otro cliente en este momento."
                // ], 402);
            }

            $existencia = DB::SELECTONE(
                "
                select
                id
                from factura
                where estado_factura_id=2  and  estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $cai->cai_id . " and numero_secuencia_cai=" . $cai->secuencia

            );




            if (!empty($existencia)) {
                return $this->alternar($request);


                // return response()->json([
                //     "icon" => "error",
                //     "title" => "Error!",
                //     "text" => "Por favor intentar facturar a otro cliente en este momento."
                // ], 402);
            } else {
                $numeroSecuencia = $cai->secuencia;
            }

            if ($numeroSecuencia < $cai->cantidad_otorgada) { //$cai->numero_actual > $cai->cantidad_otorgada//$numeroSecuencia > $cai->cantidad_otorgada

                return response()->json([
                    "title" => "Advertencia",
                    "icon" => "warning",
                    "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 3.",
                ], 200);
            }


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
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

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
            $factura->fecha_vencimiento = $this->calcularFechaVencimientoFactura(
                (string) $request->fecha_emision,
                (int) $request->tipoPagoVenta,
                $this->obtenerDiasCreditoAprobados((int) ($request->flujo_id ?? 0)),
                (int) $diasCredito
            );
            $factura->tipo_pago_id = $request->tipoPagoVenta;
            $factura->dias_credito = $this->resolverDiasCreditoFactura((int) $request->tipoPagoVenta, (int) ($request->flujo_id ?? 0), (int) $diasCredito);
            $factura->cai_id = $cai->cai_id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->gestor_entrega = $request->gestor_entrega ?: null;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 2; //coorporativo;
            $factura->estado_factura_id = 2; // se presenta
            $factura->users_id = $teleAsesorId;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            $factura->comprovante_entrega_id = $request->idComprobante;
            $factura->comentario = $request->nota_comen;
            if ($request->tipo_factura_id) {
                $factura->tipo_factura_id = $request->tipo_factura_id;
            }
            $factura->save();

            if ($request->codigo_autorizacion) {
                DB::table('codigo_autorizacion')
                    ->where('id', $request->codigo_autorizacion)
                    ->update(['estado_id' => 2]);
            }

            // if(!empty($existencia)){
            //     $caiUpdated =  ModelCAI::find($cai->cai_id);
            //     $caiUpdated->serie = $numeroSecuencia;
            //     //$caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }else{
            //     $caiUpdated =  ModelCAI::find($cai->cai_id);
            //     $caiUpdated->serie = $numeroSecuencia+1;
            // // $caiUpdated->cantidad_no_utilizada=$cai->cantidad_otorgada - 1;
            //     $caiUpdated->save();
            // }

            //DB::delete("DELETE FROM listado WHERE id = ".$cai->id);
            //DB::update("UPDATE cai SET serie = serie + 1 WHERE id =".$cai->cai_id);
            DB::update("UPDATE listado SET eliminado =  1 WHERE id = " . $cai->id);


            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */

            return $factura;
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'error' => $e,
                'icon' => "error",
                'text' => 'Ha ocurrido un error. Metodo Lista',
                'title' => 'Error!',
            ], 402);
        }
    }

    public function restarUnidadesInventario($precios_producto_carga_id,$idPrecioSeleccionado,$precioSeleccionado , $unidadesRestarInv, $idProducto, $idSeccion, $idFactura, $idUnidadVenta, $precio, $cantidad, $subTotal, $isv, $total, $ivsProducto, $unidad, $indice, $tipoPrecio = '2', $cotizacionLineaId = null, $cantidadOfertaAplicada = 0)
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

                    /* CAMBIO 20230725 round(($precioUnidad * $registroResta), 2):round(($subTotalSecccionado * ($ivsProducto / 100)), 2):round(($isvSecccionado + $subTotalSecccionado), 2)*/
                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 4);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 4);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 4);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_disponible > $unidadesRestar) {

                    $diferencia = $unidadesDisponibles->cantidad_disponible - $unidadesRestar;


                    $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                    $lote->cantidad_disponible = $diferencia;
                    $lote->save();

                    $registroResta = $unidadesRestar;
                    $unidadesRestar = 0;
                    /* CAMBIO 20230725 round(($precioUnidad * $registroResta), 2):round(($subTotalSecccionado * ($ivsProducto / 100)), 2):round(($isvSecccionado + $subTotalSecccionado), 2)*/
                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 4);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 4);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 4);

                    $cantidadSeccion = $registroResta / $unidad;
                } else if ($unidadesDisponibles->cantidad_disponible < $unidadesRestar) {

                    $diferencia = $unidadesRestar - $unidadesDisponibles->cantidad_disponible;
                    $lote = ModelRecibirBodega::find($unidadesDisponibles->id);
                    $lote->cantidad_disponible = 0;
                    $lote->save();

                    $registroResta = $unidadesDisponibles->cantidad_disponible;
                    $unidadesRestar = $diferencia;

                    /* CAMBIO 20230725 round(($precioUnidad * $registroResta), 2):round(($subTotalSecccionado * ($ivsProducto / 100)), 2):round(($isvSecccionado + $subTotalSecccionado), 2)*/

                    $subTotalSecccionado = round(($precioUnidad * $registroResta), 4);
                    $isvSecccionado = round(($subTotalSecccionado * ($ivsProducto / 100)), 4);
                    $totalSecccionado = round(($isvSecccionado + $subTotalSecccionado), 4);

                    $cantidadSeccion = $registroResta / $unidad;
                };

                $cantidadOfertaSeccion = min((float) $cantidadOfertaAplicada, (float) $cantidadSeccion);
                $cantidadOfertaAplicada -= $cantidadOfertaSeccion;



                array_push($this->arrayProductos, [
                    "factura_id" => $idFactura,
                    "cotizacion_has_producto_id" => $cotizacionLineaId,
                    "cantidad_oferta_aplicada" => $cotizacionLineaId ? $cantidadOfertaSeccion : 0,
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
                    "cantidad_nota_credito" => $cantidad, //Este campo contiene el mismo valor que el campo **cantidad** - es la cantidad ingresada por el usuario en la pantalla de factura - a este campo se le restan la cantidad a devolver en la nota de credito
                    "cantidad_s" => $cantidadSeccion, //Es la cantidad que se resta por lote - esta cantidad se convierte de unidad base a la unidad de venta seleccionada en la pantalla de factura - al realizar esta convercion es posible obtener decimales como resultado.
                    "cantidad_para_entregar" => $registroResta, //las unidades basica 1 disponible para vale
                    "sub_total_s" => $subTotalSecccionado,
                    "isv_s" => $isvSecccionado,
                    "total_s" => $totalSecccionado,
                    "tipo_precio" => $tipoPrecio,
                    "precioSeleccionado" => $precioSeleccionado,
                    "idPrecioSeleccionado" => $idPrecioSeleccionado,
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

    public function imprimirFacturaCoorporativa($idFactura)
    {
        $tipoVentaId = (int) (DB::table('factura')->where('id', $idFactura)->value('tipo_venta_id') ?? 0);
        if ($tipoVentaId === 3) {
            return (new VentasExoneradasController())->imprimirFacturaExonerada($idFactura);
        }

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        A.comentario,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        users.name as vendedor,
        (select name from users where id = A.users_id ) as facturador,
        (select name from users where id = A.gestor_entrega) as asesor_entrega,
        D.id as factura,
                COALESCE(
                        (select hf.flujo_id
                         from historico_flujo hf
                         where hf.tramite_id = A.id
                             and hf.tipo_tramite_id in (3, 5)
                         order by hf.id desc
                         limit 1),
                        (select pf.flujo_id
                         from prefactura_auditoria pa
                         inner join prefactura pf on pf.id = pa.prefactura_id
                         where pa.factura_id = A.id
                             and pa.prefactura_id is not null
                         order by pa.id desc
                         limit 1)
                ) as flujo_id

       from factura A
       inner join cai B  on A.cai_id = B.id
       inner join tipo_pago_venta C on A.tipo_pago_id = C.id
       inner join users on A.vendedor = users.id
       inner join estado_factura D on A.estado_factura_id = D.id
       where A.id = " . $idFactura);

        $cliente = DB::SELECTONE("
       select
        cliente.id as clienteId,
        factura.nombre_cliente as nombre,
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
        where factura.id = " . $idFactura);

        $importes = DB::SELECTONE("
        select
        total,
        isv,
        sub_total,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv != 0 and factura_id = ".$idFactura.") ,2) as sub_total_grabado,
        sub_total_excento,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv = 0 and factura_id = ".$idFactura."),2) as subtotal_excentovale,
        porc_descuento,
        monto_descuento
        from factura
        where id = ".$idFactura);

        /* CAMBIO 20230725 FORMAT(total,2) as total:FORMAT(isv,2) as isv:FORMAT(sub_total,2) as sub_total,:FORMAT(sub_total_grabado,2) as sub_total_grabado:FORMAT(sub_total_excento,2) as sub_total_excento*/
        $importesConCentavos = DB::SELECTONE("
        select
        FORMAT(total,2) as total,
        FORMAT(isv,2) as isv,
        FORMAT(sub_total,2) as sub_total,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv != 0 and factura_id = ".$idFactura.") ,2) as sub_total_grabado,
        FORMAT(sub_total_excento,2) as sub_total_excento,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv = 0 and factura_id = ".$idFactura."),2) as subtotal_excentovale,
        FORMAT(porc_descuento,2) as porc_descuento,
        FORMAT(monto_descuento,2) as monto_descuento
        from factura where factura.id = ".$idFactura);


        /* CAMBIO 20230725 FORMAT(B.sub_total/B.cantidad,2) as precio:FORMAT(sum(B.cantidad_s),2) as cantidad:FORMAT(sum(B.sub_total_s),2) as importe*/
        // linea cambiada FORMAT(TRUNCATE(B.sub_total/B.cantidad, 2),2) as precio,
        $productos = DB::SELECT(
            "
            select
            *
            from (
            select
                B.producto_id as codigo,
                concat(C.nombre) as descripcion,
                UPPER(J.nombre) as medida,
                if(COALESCE(NULLIF(MIN(B.tipo_precio), ''), if(MIN(B.isv_s) = 0, '1', '2')) = '1', 'SI' , 'NO' ) as excento,
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
            where A.id=" . $idFactura . "
            group by codigo, descripcion, medida, bodega, seccion, precio,B.indice
            order by B.indice asc
            ) A"

        );
        // for ($i=0; $i < 15 ; $i++) {
        //     echo($productos[$i]);
        // }

        //dd($productos);

        // 1. Intentar desde el FK a numero_orden_compra
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
            ? DB::table('flujo')->where('id', $flujoFacturaId)->first(['numero_orden_compra', 'numero_forma_f01'])
            : null;

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ['numero_orden' => ($flujoDocData->numero_orden_compra ?? null) ?: 'N/A'];
        } else {
            $ordenCompra = ["numero_orden" => $ordenCompra->numero_orden];
        }

        $formaF01 = ($flujoDocData->numero_forma_f01 ?? null) ?: null;

        if (fmod($importes->total, 1) == 0.0) {
            $flagCentavos = false;
        } else {
            $flagCentavos = true;
        }
        /*CAMBIO 20230725 $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');*/
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/factura', compact('cai', 'cliente', 'importes', 'productos', 'numeroLetras', 'importesConCentavos', 'flagCentavos', 'ordenCompra', 'formaF01'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura . ".pdf");
    }

    public function imprimirFacturaCoorporativaCopia($idFactura)
    {
        $tipoVentaId = (int) (DB::table('factura')->where('id', $idFactura)->value('tipo_venta_id') ?? 0);
        if ($tipoVentaId === 3) {
            return (new VentasExoneradasController())->imprimirFacturaExoneradaCopia($idFactura);
        }

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        A.comentario,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        users.name as vendedor,
        (select name from users where id = A.users_id ) as facturador,
        (select name from users where id = A.gestor_entrega) as asesor_entrega,
                D.id as factura,
                COALESCE(
                        (select hf.flujo_id
                         from historico_flujo hf
                         where hf.tramite_id = A.id
                             and hf.tipo_tramite_id in (3, 5)
                         order by hf.id desc
                         limit 1),
                        (select pf.flujo_id
                         from prefactura_auditoria pa
                         inner join prefactura pf on pf.id = pa.prefactura_id
                         where pa.factura_id = A.id
                             and pa.prefactura_id is not null
                         order by pa.id desc
                         limit 1)
                ) as flujo_id

       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       where A.id = " . $idFactura);

        $cliente = DB::SELECTONE("
       select
        cliente.id as clienteId,
        factura.nombre_cliente as nombre,
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
        where factura.id = " . $idFactura);


        $importes = DB::SELECTONE("
        select
        total,
        isv,
        sub_total,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv != 0 and factura_id = ".$idFactura.") ,2) as sub_total_grabado,
        sub_total_excento,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv = 0 and factura_id = ".$idFactura."),2) as subtotal_excentovale,
        porc_descuento,
        monto_descuento
        from factura
        where id = ".$idFactura);

        /* CAMBIO 20230725 FORMAT(total,2) as total:FORMAT(isv,2) as isv:FORMAT(sub_total,2) as sub_total,:FORMAT(sub_total_grabado,2) as sub_total_grabado:FORMAT(sub_total_excento,2) as sub_total_excento*/
        $importesConCentavos = DB::SELECTONE("
        select
        FORMAT(total,2) as total,
        FORMAT(isv,2) as isv,
        FORMAT(sub_total,2) as sub_total,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv != 0 and factura_id = ".$idFactura.") ,2) as sub_total_grabado,
        FORMAT(sub_total_excento,2) as sub_total_excento,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv = 0 and factura_id = ".$idFactura."),2) as subtotal_excentovale,
        FORMAT(porc_descuento,2) as porc_descuento,
        FORMAT(monto_descuento,2) as monto_descuento
        from factura where factura.id = ".$idFactura);


        /* CAMBIO 20230725 FORMAT(B.sub_total/B.cantidad,2) as precio:FORMAT(sum(B.cantidad_s),2) as cantidad:FORMAT(sum(B.sub_total_s),2) as importe*/
        // linea cambiada FORMAT(TRUNCATE(B.sub_total/B.cantidad, 2),2) as precio,
        $productos = DB::SELECT(
            "
            select
            *
            from (
            select
                B.producto_id as codigo,
                concat(C.nombre) as descripcion,
                UPPER(J.nombre) as medida,
                if(COALESCE(NULLIF(MIN(B.tipo_precio), ''), if(MIN(B.isv_s) = 0, '1', '2')) = '1', 'SI' , 'NO' ) as excento,
                if(B.seccion_id = 0, 'N/A',H.nombre) as bodega,
                if(B.seccion_id = 0, 'N/A',REPLACE(REPLACE(F.descripcion,'Seccion',''),' ', '')) as seccion,
                B.precio_unidad as precio,
                REPLACE(sum(B.cantidad_s), '.00', '') as cantidad,
                format(sum(B.sub_total_s),2) as importe
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
            where A.id=" . $idFactura . "
            group by codigo, descripcion, medida, bodega, seccion, precio,B.indice
            order by B.indice asc
            ) A"

        );
        // for ($i=0; $i < 15 ; $i++) {
        //     echo($productos[$i]);
        // }

        //dd($productos);

        // 1. Intentar desde el FK a numero_orden_compra
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
            ? DB::table('flujo')->where('id', $flujoFacturaId)->first(['numero_orden_compra', 'numero_forma_f01'])
            : null;

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ['numero_orden' => ($flujoDocData->numero_orden_compra ?? null) ?: 'N/A'];
        } else {
            $ordenCompra = ["numero_orden" => $ordenCompra->numero_orden];
        }

        $formaF01 = ($flujoDocData->numero_forma_f01 ?? null) ?: null;

        if (fmod($importes->total, 1) == 0.0) {
            $flagCentavos = false;
        } else {
            $flagCentavos = true;
        }
        /*CAMBIO 20230725 $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');*/
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/facturaCopia', compact('cai', 'cliente', 'importes', 'productos', 'numeroLetras', 'importesConCentavos', 'flagCentavos', 'ordenCompra', 'formaF01'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura . ".pdf");
    }


    public function guardarEnumeracion($numeroSecuencia, $cai, $estado)
    {

        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;


        DB::INSERT("INSERT INTO enumeracion(
            numero, secuencia, numero_inicial, numero_final, cantidad_otorgada, cai_id, estado, created_at, updated_at, eliminado) VALUES
           ('" . $numeroCAI . "','" . $numeroSecuencia . "','" . $cai->numero_inicial . "','" . $cai->numero_final . "','" . $cai->cantidad_otorgada . "','" . $cai->id . "'," . $estado . ",'" . NOW() . "','" . NOW() . "',0)");

        return;
    }

    public function enumerar($request)
    {
        try {


            $teleAsesorId = $this->resolveTeleAsesorId($request);
            $listado = DB::SELECTONE("
            select
            id,
            numero,
            secuencia,
            numero_inicial,
            numero_final,
            cantidad_otorgada,
            cai_id,
            estado
            from enumeracion
            where eliminado = 0
            order by secuencia asc
            limit 1");



            //comprobar si esta dos veces
            $duplicado = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai_id=" . $listado->cai_id . " and cai='" . $listado->numero . "'");



            $existencia = DB::SELECTONE(
                "
            select
            count(id) as contador
            from factura
            where estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $listado->cai_id . " and numero_secuencia_cai=" . $listado->secuencia

            );



            //
            if ($duplicado->contador >= 2) {
                DB::update("UPDATE enumeracion SET eliminado =  1 WHERE id = " . $listado->id);
                return $this->alternar($request);
            }

            if (!empty($existencia->contador >= 2)) {
                DB::update("UPDATE enumeracion SET eliminado =  1 WHERE id = " . $listado->id);
                return $this->alternar($request);
            }

            if ($existencia->contador != 0) {
                return $this->alternar($request);
            }

            $arrayCai = explode('-', $listado->numero_final);
            $cuartoSegmentoCAI = sprintf("%'.08d", $listado->secuencia);
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
            $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $listado->secuencia, 1);

            $factura = new ModelFactura;
            $factura->numero_factura = $numeroVenta->numero;
            $factura->cai = $numeroCAI;
            $factura->numero_secuencia_cai = $listado->secuencia;
            $factura->nombre_cliente = $request->nombre_cliente_ventas;
            $factura->rtn = $request->rtn_ventas;
            $factura->sub_total = $request->subTotalGeneral;
            $factura->sub_total_grabado = $request->subTotalGeneralGrabado;
            $factura->sub_total_excento = $request->subTotalGeneralExcento;
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
            $factura->dias_credito = $this->resolverDiasCreditoFactura((int) $request->tipoPagoVenta, (int) ($request->flujo_id ?? 0), (int) $diasCredito);
            $factura->cai_id = $listado->cai_id;
            $factura->estado_venta_id = 1;
            $factura->cliente_id = $request->seleccionarCliente;
            $factura->vendedor = $request->vendedor;
            $factura->gestor_entrega = $request->gestor_entrega ?: null;
            $factura->monto_comision = $montoComision;
            $factura->tipo_venta_id = 2; //coorporativo;
            $factura->estado_factura_id = $listado->estado; // se presenta
            $factura->users_id = $teleAsesorId;
            $factura->comision_estado_pagado = 0;
            $factura->pendiente_cobro = $request->totalGeneral;
            $factura->estado_editar = 1;
            $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
            $factura->comprovante_entrega_id = $request->idComprobante;
            $factura->comentario = $request->nota_comen;
            if ($request->tipo_factura_id) {
                $factura->tipo_factura_id = $request->tipo_factura_id;
            }
            $factura->save();

            if ($request->codigo_autorizacion) {
                DB::table('codigo_autorizacion')
                    ->where('id', $request->codigo_autorizacion)
                    ->update(['estado_id' => 2]);
            }

            //DB::delete("DELETE FROM enumeracion WHERE id = ".$listado->id);
            DB::update("UPDATE enumeracion SET eliminado =  1 WHERE id = " . $listado->id);

            /* $aplicacionPagos = DB::select("

            CALL sp_aplicacion_pagos('2','".$factura->cliente_id."', '".Auth::user()->id."', '".$factura->id."','na','0','0','0', @estado, @msjResultado);");


            if ($aplicacionPagos[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al insertar factura ".$factura->id."en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],400);
            } */
            return $factura;
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

    public function comprobacionRecursiva($request, $cai, $numeroActual, $estado)
    {

        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroActual);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

        $duplicado = DB::SELECTONE("select count(id) as contador from factura where estado_venta_id=1 and cai_id=" . $cai->id . " and cai='" . $numeroCAI . "'");


        $existencia = DB::SELECTONE(
            "
            select
            id
            from factura
            where  estado_venta_id=1 and cliente_id=" . $request->seleccionarCliente . " and cai_id=" . $cai->id . " and numero_secuencia_cai=" . $numeroActual .
                " and UPPER(REPLACE(nombre_cliente,' ','')) = UPPER(REPLACE('" . $request->nombre_cliente_ventas . "',' ',''))"
        );



        if ($duplicado->contador >= 2) {
            $numeroActual = $numeroActual + 1;
            return $this->comprobacionRecursiva($request, $cai, $numeroActual, $estado);
        } else if (!empty($existencia)) {

            $this->guardarEnumeracion($numeroActual, $cai, $estado);
            $numeroActual = $numeroActual + 1;
            return $this->comprobacionRecursiva($request, $cai, $numeroActual, $estado);
        } else {
            return $numeroActual;
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


        //dd('llego dentro de funcion facturas vencidas Inicio');
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
       // dd($facturasVencidas);

       // dd('llego dentro de funcion facturas vencidas');
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

    public function listadoVendedores(Request $request)
    {
        $search = trim($request->get('search', ''));
        $clienteId = (int) $request->get('cliente_id');

        $query = DB::table('cliente_usuario as cu')
            ->join('users as u', 'u.id', '=', 'cu.usuario_id')
            ->where('cu.cliente_id', $clienteId)
            ->where('cu.rol_id', ClienteActoresAsignados::ROL_ASESOR_COMERCIAL)
            ->where('u.estado_id', 1);
        if ($search !== '') {
            $query->where('u.name', 'like', '%' . $search . '%');
        }
        $listadoVendedores = $query
            ->select('u.id', DB::raw('u.name as text'))
            ->distinct()
            ->orderBy('u.name')
            ->get();

        return response()->json([
            'results' => $listadoVendedores,
        ], 200);
    }

    public function guardarVentaND($request)
    {
        DB::beginTransaction();
        try {
            $teleAsesorId = $this->resolveTeleAsesorId($request);

        $numeroSecuencia = 0;
        $numeroSecuenciaUpdated = 0;
        $estado = 2;

        // FOR UPDATE bloquea la fila hasta que se haga commit,
        // evitando que dos transacciones simultáneas lean el mismo numero_actual.
        $cai = DB::SELECTONE("select
                                id,
                                numero_inicial,
                                numero_final,
                                cantidad_otorgada,
                                numero_actual
                                from cai
                                where tipo_documento_fiscal_id = 1 and estado_id = 1
                                FOR UPDATE");

        $numeroSecuencia = $cai->numero_actual;

        // Si el número actual ya está en uso, avanzar hasta el próximo libre.
        while ($numeroSecuencia <= $cai->cantidad_otorgada
            && DB::table('factura')
                   ->where('cai_id', $cai->id)
                   ->where('numero_secuencia_cai', $numeroSecuencia)
                   ->exists()) {
            $numeroSecuencia++;
        }

        $numeroSecuenciaUpdated = $numeroSecuencia + 1;

        $arrayNumeroFinal = explode('-', $cai->numero_final);
        $numero_final = (string)((int)($arrayNumeroFinal[3]));

        if ($numeroSecuencia > $numero_final) {
            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La factura no puede proceder, debido que ha alcanzadado el número maximo de facturacion otorgado 2.",
            ], 200);
        }



        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

        $montoComision = $request->totalGeneral * 0.5;



        if ($request->tipoPagoVenta == 1) {
            $diasCredito = 0;
        } else {
            $dias = DB::SELECTONE("select dias_credito from cliente where id = " . $request->seleccionarCliente);
            $diasCredito = $dias->dias_credito;
        }

        $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");

        $validarCAI = new Notificaciones();
        $validarCAI->validarAlertaCAI(ltrim($arrayCai[3], "0"), $numeroSecuencia, 1);

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
        $factura->fecha_vencimiento = $this->calcularFechaVencimientoFactura(
            (string) $request->fecha_emision,
            (int) $request->tipoPagoVenta,
            $this->obtenerDiasCreditoAprobados((int) ($request->flujo_id ?? 0)),
            (int) $diasCredito
        );
        $factura->tipo_pago_id = $request->tipoPagoVenta;
        $factura->dias_credito = $this->resolverDiasCreditoFactura((int) $request->tipoPagoVenta, (int) ($request->flujo_id ?? 0), (int) $diasCredito);
        $factura->cai_id = $cai->id;
        $factura->estado_venta_id = 1;
        $factura->cliente_id = $request->seleccionarCliente;
        $factura->vendedor = $request->vendedor;
        $factura->gestor_entrega = $request->gestor_entrega ?: null;
        $factura->monto_comision = $montoComision;
        $factura->tipo_venta_id = 2; //coorporativo;
        $factura->estado_factura_id = $estado;
        $factura->users_id = $teleAsesorId;
        $factura->comision_estado_pagado = 0;
        $factura->pendiente_cobro = $request->totalGeneral;
        $factura->estado_editar = 1;
        $factura->codigo_autorizacion_id = $request->codigo_autorizacion;
        $factura->comprovante_entrega_id = $request->idComprobante;
        $factura->numero_orden_compra_id = $request->ordenCompra;
        $factura->comentario = $request->nota_comen;
        $factura->porc_descuento = $request->porDescuento;
        $factura->monto_descuento = $request->porDescuentoCalculado;
        if ($request->tipo_factura_id) {
            $factura->tipo_factura_id = $request->tipo_factura_id;
        }
        $factura->save();

        if ($request->codigo_autorizacion) {
            DB::table('codigo_autorizacion')
                ->where('id', $request->codigo_autorizacion)
                ->update(['estado_id' => 2]);
        }

        $caiUpdated = ModelCAI::find($cai->id);
        $caiUpdated->numero_actual = $numeroSecuenciaUpdated;
        $caiUpdated->save();

        DB::commit();

        return $factura;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error, metodo guardarVentaND',
                'error' => $e->getMessage()
            ], 402);
        }
    }


    public function imprimirActaCoorporativa($idFactura)
    {

        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        A.comentario,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        users.name as vendedor,
        (select name from users where id = A.users_id ) as facturador,
        D.id as factura

       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       where A.id = " . $idFactura);

        $cliente = DB::SELECTONE("
       select
        cliente.id as clienteId,
        factura.nombre_cliente as nombre,
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
        where factura.id = " . $idFactura);


        $importes = DB::SELECTONE("
        select
        total,
        isv,
        sub_total,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv != 0 and factura_id = ".$idFactura.") ,2) as sub_total_grabado,
        sub_total_excento,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv = 0 and factura_id = ".$idFactura."),2) as subtotal_excentovale,
        porc_descuento,
        monto_descuento
        from factura
        where id = ".$idFactura);

        /* CAMBIO 20230725 FORMAT(total,2) as total:FORMAT(isv,2) as isv:FORMAT(sub_total,2) as sub_total,:FORMAT(sub_total_grabado,2) as sub_total_grabado:FORMAT(sub_total_excento,2) as sub_total_excento*/
        $importesConCentavos = DB::SELECTONE("
        select
        FORMAT(total,2) as total,
        FORMAT(isv,2) as isv,
        FORMAT(sub_total,2) as sub_total,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv != 0 and factura_id = ".$idFactura.") ,2) as sub_total_grabado,
        FORMAT(sub_total_excento,2) as sub_total_excento,
        FORMAT((select sum(sub_total_s) from venta_has_producto where isv = 0 and factura_id = ".$idFactura."),2) as subtotal_excentovale,
        FORMAT(porc_descuento,2) as porc_descuento,
        FORMAT(monto_descuento,2) as monto_descuento
        from factura where factura.id = ".$idFactura);






        $productos = DB::SELECT(
            "
            select
            *
            from (
            select
                B.producto_id as codigo,
                concat(C.nombre) as descripcion,
                UPPER(J.nombre) as medida,
                if(COALESCE(NULLIF(MIN(B.tipo_precio), ''), if(MIN(B.isv_s) = 0, '1', '2')) = '1', 'SI' , 'NO' ) as excento,
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
            where A.id=" . $idFactura . "
            group by codigo, descripcion, medida, bodega, seccion, precio,B.indice
            order by B.indice asc
            ) A"

        );
        // for ($i=0; $i < 15 ; $i++) {
        //     echo($productos[$i]);
        // }

        //dd($productos);

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =" . $idFactura);

        if (empty($ordenCompra->numero_orden)) {
            $ordenCompra = ["numero_orden" => ""];
        } else {
            $ordenCompra = ["numero_orden" => $ordenCompra->numero_orden];
        }


        if (fmod($importes->total, 1) == 0.0) {
            $flagCentavos = false;
        } else {
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/actaRecepcion', compact('cai', 'cliente', 'importes', 'productos', 'numeroLetras', 'importesConCentavos', 'flagCentavos', 'ordenCompra'))->setPaper('letter');

        return $pdf->stream("factura_numero" . $cai->numero_factura . ".pdf");
    }
}
