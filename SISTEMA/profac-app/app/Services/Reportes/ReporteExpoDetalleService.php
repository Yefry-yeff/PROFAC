<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\DB;

class ReporteExpoDetalleService
{
    public function oferta(int $cotizacionId): ?array
    {
        $oferta = DB::table('cotizacion as c')
            ->join('expo_cotizacion as ec', 'ec.cotizacion_id', '=', 'c.id')
            ->join('expo as e', 'e.id', '=', 'ec.expo_id')
            ->leftJoin('users as asesor', 'asesor.id', '=', 'c.vendedor')
            ->leftJoin('users as teleasesor', 'teleasesor.id', '=', 'c.users_id')
            ->leftJoin('users as creador', 'creador.id', '=', 'c.created_by')
            ->leftJoin('users as modificador', 'modificador.id', '=', 'c.updated_by')
            ->leftJoin('tipo_venta as tv', 'tv.id', '=', 'c.tipo_venta_id')
            ->leftJoin('tipo_pago_venta as tpv', 'tpv.id', '=', 'c.tipo_pago_id')
            ->where('c.id', $cotizacionId)
            ->first([
                'c.id', 'c.nombre_cliente', 'c.RTN as rtn', 'c.fecha_emision',
                'c.fecha_vencimiento', 'c.created_at', 'c.updated_at', 'c.nota',
                'c.porc_descuento', 'c.monto_descuento', 'ec.estado',
                'ec.reglas_descuento_snapshot', 'e.id as expo_id', 'e.nombre as expo',
                'asesor.name as asesor', 'teleasesor.name as teleasesor',
                'creador.name as creado_por', 'modificador.name as modificado_por',
                'tv.descripcion as tipo_venta', 'tpv.descripcion as condicion_pago',
                DB::raw('COALESCE(ec.flujo_id, (SELECT hf.flujo_id FROM historico_flujo hf WHERE hf.tipo_tramite_id = 2 AND hf.tramite_id = c.id ORDER BY hf.id DESC LIMIT 1)) as flujo_id'),
            ]);

        if (!$oferta) {
            return null;
        }

        $snapshot = json_decode((string) $oferta->reglas_descuento_snapshot, true) ?: [];
        $escalasSnapshot = collect($snapshot['lineas'] ?? [])->keyBy('linea_id');

        $lineas = DB::table('cotizacion_has_producto as chp')
            ->join('producto as p', 'p.id', '=', 'chp.producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->leftJoin('categoria_producto as cat', 'cat.id', '=', 'ppc.categoria_producto_id')
            ->where('chp.cotizacion_id', $cotizacionId)
            ->orderBy('chp.indice')
            ->get([
                'chp.id as linea_id', 'chp.producto_id', 'p.codigo_barra as codigo',
                DB::raw("COALESCE(NULLIF(chp.nombre_producto, ''), p.nombre) as producto"),
                DB::raw("COALESCE(m.nombre, 'Sin marca') as marca"),
                DB::raw("COALESCE(cat.descripcion, 'Sin categoria') as categoria"),
                'chp.cantidad', 'chp.precioSeleccionado', 'chp.precio_unidad',
                'chp.monto_descProducto', 'chp.sub_total', 'chp.isv_producto',
                'chp.isv', 'chp.total', 'ppc.precio_base_venta',
                'ppc.costoproducto', 'cp.id as escala_id', 'cp.nombre as escala',
            ])->map(function ($linea) use ($escalasSnapshot) {
                $snapshotLinea = $escalasSnapshot->get((int) $linea->linea_id, []);
                $linea->escala_id = $snapshotLinea['escala_id'] ?? $linea->escala_id;
                $linea->escala = $snapshotLinea['escala'] ?? $linea->escala;

                return $this->normalizarLineaOferta($linea);
            })->values();

        $facturas = $this->facturasOferta($cotizacionId);
        $resumen = $this->resumenLineas($lineas->all());
        $cantidadOfertada = (float) $lineas->sum('cantidad');
        $cantidadFacturada = (float) collect($facturas)->sum('cantidad_aplicada');
        $netoFacturado = (float) collect($facturas)->sum('subtotal_relacionado');

        return [
            'oferta' => [
                'id' => (int) $oferta->id,
                'flujo_id' => $oferta->flujo_id ? (int) $oferta->flujo_id : null,
                'expo_id' => (int) $oferta->expo_id,
                'expo' => $oferta->expo,
                'cliente' => $oferta->nombre_cliente ?: 'Sin cliente',
                'rtn' => $oferta->rtn,
                'asesor' => $oferta->asesor ?: 'Sin asignar',
                'teleasesor' => $oferta->teleasesor ?: 'Sin asignar',
                'fecha' => $oferta->fecha_emision,
                'hora' => $oferta->created_at ? date('H:i:s', strtotime($oferta->created_at)) : null,
                'estado' => $oferta->estado,
                'tipo_venta' => $oferta->tipo_venta ?: 'Sin asignar',
                'condicion_pago' => $oferta->condicion_pago ?: 'Sin asignar',
                'creado_por' => $oferta->creado_por ?: $oferta->teleasesor ?: 'Sin asignar',
                'modificado_por' => $oferta->modificado_por,
                'creado_at' => $oferta->created_at,
                'modificado_at' => $oferta->updated_at,
                'nota' => $oferta->nota,
            ],
            'resumen' => array_merge($resumen, [
                'total_facturado' => round($netoFacturado, 2),
                'diferencia_pendiente' => round(max($resumen['subtotal_final'] - $netoFacturado, 0), 2),
                'estado_facturacion' => $this->estadoFacturacion($cantidadOfertada, $cantidadFacturada),
            ]),
            'productos' => $lineas->all(),
            'facturas' => $facturas,
        ];
    }

    public function producto(int $productoId, int $expoId, array $cotizacionIds): ?array
    {
        $ids = collect($cotizacionIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return null;
        }

        $facturado = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->join('cotizacion_has_producto as chp_fact', 'chp_fact.id', '=', 'vhp.cotizacion_has_producto_id')
            ->join('producto as p_fact', 'p_fact.id', '=', 'vhp.producto_id')
            ->leftJoin('precios_producto_carga as ppc_fact', 'ppc_fact.id', '=', 'vhp.precios_producto_carga_id')
            ->leftJoin('precios_producto_carga as ppc_oferta', 'ppc_oferta.id', '=', 'chp_fact.precios_producto_carga_id')
            ->where('f.estado_venta_id', 1)
            ->whereNotNull('vhp.cotizacion_has_producto_id')
            ->groupBy('vhp.cotizacion_has_producto_id')
            ->selectRaw('vhp.cotizacion_has_producto_id, SUM(COALESCE(NULLIF(vhp.cantidad_oferta_aplicada, 0), vhp.cantidad_s)) as cantidad_facturada, SUM(vhp.sub_total_s) as total_facturado, SUM(GREATEST((vhp.precio_unidad * vhp.cantidad_s) - vhp.sub_total_s, 0)) as descuento_facturado, SUM(vhp.isv_s) as isv_facturado, SUM(vhp.total_s) as total_con_impuesto_facturado, SUM(COALESCE(ppc_fact.costoproducto, ppc_oferta.costoproducto, p_fact.costo_promedio, 0) * vhp.cantidad_s) as costo_facturado');

        $rows = DB::table('cotizacion_has_producto as chp')
            ->join('cotizacion as c', 'c.id', '=', 'chp.cotizacion_id')
            ->join('expo_cotizacion as ec', function ($join) use ($expoId) {
                $join->on('ec.cotizacion_id', '=', 'c.id')->where('ec.expo_id', '=', $expoId);
            })
            ->join('producto as p', 'p.id', '=', 'chp.producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->leftJoin('users as asesor', 'asesor.id', '=', 'c.vendedor')
            ->leftJoin('users as teleasesor', 'teleasesor.id', '=', 'c.users_id')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->leftJoin('categoria_producto as cat', 'cat.id', '=', 'ppc.categoria_producto_id')
            ->leftJoinSub($facturado, 'facturado', fn ($join) => $join->on('facturado.cotizacion_has_producto_id', '=', 'chp.id'))
            ->whereIn('c.id', $ids->all())
            ->where('chp.producto_id', $productoId)
            ->orderByDesc('c.fecha_emision')
            ->get([
                'chp.id as linea_id', 'chp.producto_id', 'p.codigo_barra as codigo',
                DB::raw("COALESCE(NULLIF(chp.nombre_producto, ''), p.nombre) as producto"),
                DB::raw("COALESCE(m.nombre, 'Sin marca') as marca"),
                DB::raw("COALESCE(cat.descripcion, 'Sin categoria') as categoria"),
                'c.id as oferta_id', 'c.fecha_emision as fecha', 'c.nombre_cliente as cliente',
                DB::raw("COALESCE(asesor.name, 'Sin asignar') as asesor"),
                DB::raw("COALESCE(teleasesor.name, 'Sin asignar') as teleasesor"),
                'ec.estado', 'chp.cantidad', 'chp.precioSeleccionado',
                'chp.precio_unidad', 'chp.monto_descProducto', 'chp.sub_total',
                'chp.isv_producto', 'chp.isv', 'chp.total', 'ppc.precio_base_venta',
                'ppc.costoproducto', 'cp.id as escala_id', 'cp.nombre as escala',
                DB::raw('COALESCE(facturado.cantidad_facturada, 0) as cantidad_facturada'),
                DB::raw('COALESCE(facturado.total_facturado, 0) as total_facturado'),
                DB::raw('COALESCE(facturado.descuento_facturado, 0) as descuento_facturado'),
                DB::raw('COALESCE(facturado.isv_facturado, 0) as isv_facturado'),
                DB::raw('COALESCE(facturado.total_con_impuesto_facturado, 0) as total_con_impuesto_facturado'),
                DB::raw('COALESCE(facturado.costo_facturado, 0) as costo_facturado'),
                'ec.reglas_descuento_snapshot',
                DB::raw('COALESCE(ec.flujo_id, (SELECT hf.flujo_id FROM historico_flujo hf WHERE hf.tipo_tramite_id = 2 AND hf.tramite_id = c.id ORDER BY hf.id DESC LIMIT 1)) as flujo_id'),
            ])->map(function ($row) {
                $snapshot = json_decode((string) $row->reglas_descuento_snapshot, true) ?: [];
                $snapshotLinea = collect($snapshot['lineas'] ?? [])->firstWhere('linea_id', (int) $row->linea_id);
                if ($snapshotLinea) {
                    $row->escala_id = $snapshotLinea['escala_id'] ?? $row->escala_id;
                    $row->escala = $snapshotLinea['escala'] ?? $row->escala;
                }
                $linea = $this->normalizarLineaOferta($row);
                $linea['oferta_id'] = (int) $row->oferta_id;
                $linea['flujo_id'] = $row->flujo_id ? (int) $row->flujo_id : null;
                $linea['fecha'] = $row->fecha;
                $linea['cliente'] = $row->cliente;
                $linea['asesor'] = $row->asesor;
                $linea['teleasesor'] = $row->teleasesor;
                $linea['estado'] = $row->estado;
                $linea['cantidad_facturada'] = round((float) $row->cantidad_facturada, 4);
                $linea['total_facturado'] = round((float) $row->total_facturado, 2);
                $linea['descuento_facturado'] = round((float) $row->descuento_facturado, 2);
                $linea['isv_facturado'] = round((float) $row->isv_facturado, 2);
                $linea['total_con_impuesto_facturado'] = round((float) $row->total_con_impuesto_facturado, 2);
                $linea['costo_facturado'] = round((float) $row->costo_facturado, 2);
                $linea['estado_facturacion'] = $this->estadoFacturacion((float) $row->cantidad, (float) $row->cantidad_facturada);

                return $linea;
            })->values();

        if ($rows->isEmpty()) {
            return null;
        }

        $primero = $rows->first();
        $totalOfertado = (float) $rows->sum('subtotal_final');
        $costoOfertado = (float) $rows->sum('costo_total');
        $utilidadOfertada = $totalOfertado - $costoOfertado;
        $totalVendido = (float) $rows->sum('total_facturado');
        $costoVendido = (float) $rows->sum('costo_facturado');
        $utilidadFacturada = $totalVendido - $costoVendido;

        return [
            'producto' => [
                'id' => $productoId,
                'codigo' => $primero['codigo'],
                'producto' => $primero['producto'],
                'marca' => $primero['marca'],
                'categoria' => $primero['categoria'],
                'cantidad_ofertada' => round((float) $rows->sum('cantidad'), 4),
                'cantidad_vendida' => round((float) $rows->sum('cantidad_facturada'), 4),
                'total_ofertado' => round($totalOfertado, 2),
                'total_vendido' => round($totalVendido, 2),
                'descuento_acumulado' => round((float) $rows->sum('descuento'), 2),
                'descuento_facturado' => round((float) $rows->sum('descuento_facturado'), 2),
                'costo_ofertado' => round($costoOfertado, 2),
                'utilidad_ofertada' => round($utilidadOfertada, 2),
                'margen_ofertado_pct' => $totalOfertado > 0 ? round(($utilidadOfertada / $totalOfertado) * 100, 2) : null,
                'costo_facturado' => round($costoVendido, 2),
                'utilidad_facturada' => round($utilidadFacturada, 2),
                'margen_facturado_pct' => $totalVendido > 0 ? round(($utilidadFacturada / $totalVendido) * 100, 2) : null,
                'numero_ofertas' => $rows->pluck('oferta_id')->unique()->count(),
            ],
            'ofertas' => $rows->all(),
        ];
    }

    private function facturasOferta(int $cotizacionId): array
    {
        $facturas = DB::table('factura as f')
            ->join('venta_has_producto as vhp', 'vhp.factura_id', '=', 'f.id')
            ->join('cotizacion_has_producto as chp', function ($join) use ($cotizacionId) {
                $join->on('chp.id', '=', 'vhp.cotizacion_has_producto_id')
                    ->where('chp.cotizacion_id', '=', $cotizacionId);
            })
            ->leftJoin('users as asesor', 'asesor.id', '=', 'f.vendedor')
            ->leftJoin('users as teleasesor', 'teleasesor.id', '=', 'f.users_id')
            ->leftJoin('estado_venta as ev', 'ev.id', '=', 'f.estado_venta_id')
            ->where('f.estado_venta_id', 1)
            ->groupBy('f.id', 'f.cai', 'f.numero_factura', 'f.fecha_emision', 'f.nombre_cliente',
                'f.sub_total', 'f.monto_descuento', 'f.isv', 'f.total', 'asesor.name',
                'teleasesor.name', 'ev.descripcion')
            ->orderBy('f.fecha_emision')
            ->get([
                'f.id', 'f.cai', 'f.numero_factura', 'f.fecha_emision', 'f.nombre_cliente',
                'f.sub_total', 'f.monto_descuento', 'f.isv', 'f.total',
                DB::raw("COALESCE(asesor.name, 'Sin asignar') as asesor"),
                DB::raw("COALESCE(teleasesor.name, 'Sin asignar') as teleasesor"),
                DB::raw("COALESCE(ev.descripcion, 'Activa') as estado_factura"),
                DB::raw('SUM(COALESCE(NULLIF(vhp.cantidad_oferta_aplicada, 0), vhp.cantidad_s)) as cantidad_aplicada'),
                DB::raw('SUM(vhp.sub_total_s) as subtotal_relacionado'),
                DB::raw('SUM(vhp.isv_s) as isv_relacionado'),
                DB::raw('SUM(vhp.total_s) as total_relacionado'),
            ]);

        if ($facturas->isEmpty()) {
            return [];
        }

        $lineas = DB::table('venta_has_producto as vhp')
            ->join('cotizacion_has_producto as chp', 'chp.id', '=', 'vhp.cotizacion_has_producto_id')
            ->join('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'vhp.precios_producto_carga_id')
            ->leftJoin('precios_producto_carga as ppc_oferta', 'ppc_oferta.id', '=', 'chp.precios_producto_carga_id')
            ->whereIn('vhp.factura_id', $facturas->pluck('id'))
            ->where('chp.cotizacion_id', $cotizacionId)
            ->groupBy('vhp.factura_id', 'chp.id', 'p.id', 'p.codigo_barra', 'p.nombre',
                'm.nombre', 'vhp.precio_unidad')
            ->get([
                'vhp.factura_id', 'chp.id as linea_id', 'p.id as producto_id',
                'p.codigo_barra as codigo', 'p.nombre as producto',
                DB::raw("COALESCE(m.nombre, 'Sin marca') as marca"),
                'vhp.precio_unidad',
                DB::raw('SUM(vhp.cantidad_s) as cantidad'),
                DB::raw('SUM(vhp.sub_total_s) as subtotal'),
                DB::raw('SUM(vhp.isv_s) as isv'),
                DB::raw('SUM(vhp.total_s) as total'),
                DB::raw('SUM(COALESCE(ppc.costoproducto, ppc_oferta.costoproducto, p.costo_promedio, 0) * vhp.cantidad_s) as costo'),
            ])->groupBy('factura_id');

        return $facturas->map(function ($factura) use ($lineas) {
            $detalles = collect($lineas->get($factura->id, []))->map(function ($linea) {
                $subtotalOriginal = (float) $linea->precio_unidad * (float) $linea->cantidad;
                $subtotal = (float) $linea->subtotal;
                $costo = (float) $linea->costo;
                $utilidad = $subtotal - $costo;

                return [
                    'linea_id' => (int) $linea->linea_id,
                    'producto_id' => (int) $linea->producto_id,
                    'codigo' => $linea->codigo,
                    'producto' => $linea->producto,
                    'marca' => $linea->marca,
                    'cantidad' => round((float) $linea->cantidad, 4),
                    'precio' => round((float) $linea->precio_unidad, 4),
                    'descuento' => round(max($subtotalOriginal - $subtotal, 0), 2),
                    'isv' => round((float) $linea->isv, 2),
                    'subtotal' => round($subtotal, 2),
                    'total' => round((float) $linea->total, 2),
                    'costo' => round($costo, 2),
                    'utilidad' => round($utilidad, 2),
                    'margen_pct' => $subtotal > 0 ? round(($utilidad / $subtotal) * 100, 2) : null,
                ];
            })->values();
            $subtotal = (float) $factura->subtotal_relacionado;
            $costo = (float) $detalles->sum('costo');
            $utilidad = $subtotal - $costo;

            return [
                'id' => (int) $factura->id,
                'numero' => $factura->cai ?: $factura->numero_factura,
                'fecha' => $factura->fecha_emision,
                'cliente' => $factura->nombre_cliente,
                'asesor' => $factura->asesor,
                'teleasesor' => $factura->teleasesor,
                'subtotal' => round((float) $factura->sub_total, 2),
                'descuento' => round((float) $factura->monto_descuento, 2),
                'isv' => round((float) $factura->isv, 2),
                'total' => round((float) $factura->total, 2),
                'subtotal_relacionado' => round($subtotal, 2),
                'costo' => round($costo, 2),
                'utilidad' => round($utilidad, 2),
                'margen_pct' => $subtotal > 0 ? round(($utilidad / $subtotal) * 100, 2) : null,
                'estado' => $factura->estado_factura,
                'cantidad_aplicada' => round((float) $factura->cantidad_aplicada, 4),
                'productos' => $detalles->all(),
            ];
        })->values()->all();
    }

    private function normalizarLineaOferta(object $linea): array
    {
        $cantidad = (float) $linea->cantidad;
        $precioAntesDescuento = (float) ($linea->precioSeleccionado ?: $linea->precio_unidad);
        $subtotalFinal = (float) $linea->sub_total;
        $subtotalOriginal = $precioAntesDescuento * $cantidad;
        $descuento = (float) $linea->monto_descProducto;
        if ($descuento <= 0) {
            $descuento = max($subtotalOriginal - $subtotalFinal, 0);
        }
        $costoUnitario = (float) $linea->precio_base_venta;
        $costoTotal = $costoUnitario * $cantidad;
        $utilidad = $subtotalFinal - $costoTotal;

        return [
            'linea_id' => (int) $linea->linea_id,
            'producto_id' => (int) $linea->producto_id,
            'codigo' => $linea->codigo,
            'producto' => $linea->producto,
            'marca' => $linea->marca,
            'categoria' => $linea->categoria,
            'cantidad' => round($cantidad, 4),
            'escala_id' => $linea->escala_id ? (int) $linea->escala_id : null,
            'escala' => $linea->escala ?: 'Sin escala',
            'precio_base' => round((float) $linea->precio_base_venta, 4),
            'precio_antes_descuento' => round($precioAntesDescuento, 4),
            'descuento' => round($descuento, 2),
            'descuento_pct' => $subtotalOriginal > 0 ? round(($descuento / $subtotalOriginal) * 100, 2) : 0,
            'precio_final' => $cantidad > 0 ? round($subtotalFinal / $cantidad, 4) : 0,
            'subtotal_original' => round($subtotalOriginal, 2),
            'subtotal_final' => round($subtotalFinal, 2),
            'isv_pct' => round((float) $linea->isv_producto, 2),
            'isv' => round((float) $linea->isv, 2),
            'total' => round((float) $linea->total, 2),
            'costo_unitario' => round($costoUnitario, 4),
            'costo_total' => round($costoTotal, 2),
            'utilidad' => round($utilidad, 2),
            'margen_pct' => $subtotalFinal > 0 ? round(($utilidad / $subtotalFinal) * 100, 2) : null,
        ];
    }

    private function resumenLineas(array $lineas): array
    {
        $items = collect($lineas);
        $subtotalFinal = (float) $items->sum('subtotal_final');
        $costo = (float) $items->sum('costo_total');
        $utilidad = $subtotalFinal - $costo;

        return [
            'subtotal_original' => round((float) $items->sum('subtotal_original'), 2),
            'descuento' => round((float) $items->sum('descuento'), 2),
            'subtotal_final' => round($subtotalFinal, 2),
            'isv' => round((float) $items->sum('isv'), 2),
            'total' => round((float) $items->sum('total'), 2),
            'costo' => round($costo, 2),
            'utilidad' => round($utilidad, 2),
            'margen_pct' => $subtotalFinal > 0 ? round(($utilidad / $subtotalFinal) * 100, 2) : null,
        ];
    }

    private function estadoFacturacion(float $ofertado, float $facturado): string
    {
        if ($facturado <= 0.0001) {
            return 'NO_FACTURADA';
        }

        return $facturado + 0.0001 >= $ofertado ? 'FACTURADA' : 'PARCIALMENTE_FACTURADA';
    }
}