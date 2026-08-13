<?php

namespace App\Services\Expo;

use App\Services\NotaCredito\GestorCreditoNota;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiquidacionOfertaExpo
{
    private const TOLERANCIA = 0.005;

    public function __construct(
        private SaldoLineasOferta $saldosLineas,
        private GestorCreditoNota $gestorCredito
    ) {
    }

    public function previsualizar(int $cotizacionId, int $flujoId, ?int $facturaAdicionalId = null): array
    {
        $expoCotizacion = DB::table('expo_cotizacion as ec')
            ->join('expo as e', 'e.id', '=', 'ec.expo_id')
            ->where('ec.cotizacion_id', $cotizacionId)
            ->first(['ec.*', 'e.nombre as expo_nombre']);

        if (!$expoCotizacion) {
            throw ValidationException::withMessages([
                'cotizacion_id' => 'La oferta indicada no pertenece a una Expo.',
            ]);
        }

        $facturaIds = $this->facturasActivasFlujo($flujoId, $facturaAdicionalId);
        $facturas = DB::table('factura')
            ->whereIn('id', $facturaIds)
            ->where('estado_venta_id', 1)
            ->orderBy('fecha_vencimiento')
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->get(['id', 'cai', 'fecha_emision', 'fecha_vencimiento', 'sub_total', 'sub_total_grabado', 'sub_total_excento', 'isv', 'total']);

        $detallesFacturados = DB::table('venta_has_producto as vhp')
            ->join('cotizacion_has_producto as chp', 'chp.id', '=', 'vhp.cotizacion_has_producto_id')
            ->whereIn('vhp.factura_id', $facturaIds)
            ->where('chp.cotizacion_id', $cotizacionId)
            ->get(['vhp.factura_id', 'vhp.cotizacion_has_producto_id as linea_id', 'vhp.sub_total_s']);
        $subtotalesBrutos = $detallesFacturados
            ->groupBy('factura_id')
            ->map(fn($detalles) => (float) $detalles->sum('sub_total_s'));

        $totalFacturado = round((float) $subtotalesBrutos->sum(), 2);
        $reglas = $this->reglas($expoCotizacion);
        $lineasSnapshot = collect($reglas['lineas'])->keyBy('linea_id');
        $subtotalesMarcaFactura = [];
        foreach ($detallesFacturados as $detalle) {
            $marcaId = (int) ($lineasSnapshot[(int) $detalle->linea_id]['marca_id'] ?? 0);
            if ($marcaId <= 0) {
                continue;
            }
            $clave = (int) $detalle->factura_id . ':' . $marcaId;
            $subtotalesMarcaFactura[$clave] = ($subtotalesMarcaFactura[$clave] ?? 0) + (float) $detalle->sub_total_s;
        }

        $descuentosMarca = [];
        $totalDescuentoMarca = 0.0;
        foreach ($facturas as $factura) {
            foreach ($reglas['marcas'] as $reglaMarca) {
                $clave = (int) $factura->id . ':' . (int) $reglaMarca['marca_id'];
                $subtotalMarca = round((float) ($subtotalesMarcaFactura[$clave] ?? 0), 2);
                if ($subtotalMarca <= self::TOLERANCIA) {
                    continue;
                }
                $cumple = $subtotalMarca + self::TOLERANCIA >= (float) $reglaMarca['venta_minima'];
                $descuentoMarca = $cumple
                    ? round($subtotalMarca * (float) $reglaMarca['porcentaje_descuento'] / 100, 2)
                    : 0.0;
                $totalDescuentoMarca += $descuentoMarca;
                $descuentosMarca[] = [
                    'factura_id' => (int) $factura->id,
                    'factura' => $factura->cai,
                    'marca_id' => (int) $reglaMarca['marca_id'],
                    'marca' => $reglaMarca['marca'],
                    'subtotal_bruto' => $subtotalMarca,
                    'venta_minima' => (float) $reglaMarca['venta_minima'],
                    'porcentaje_descuento' => (float) $reglaMarca['porcentaje_descuento'],
                    'cumple' => $cumple,
                    'descuento' => $descuentoMarca,
                ];
            }
        }
        $totalDescuentoMarca = round($totalDescuentoMarca, 2);

        $porcentaje = 0.0;
        foreach ($reglas['generales'] as $regla) {
            if ($totalFacturado + self::TOLERANCIA >= (float) $regla['venta_minima']) {
                $porcentaje = (float) $regla['porcentaje_descuento'];
            }
        }

        $baseGeneral = round(max($totalFacturado - $totalDescuentoMarca, 0), 2);
        $descuentoGeneral = round($baseGeneral * $porcentaje / 100, 2);
        $descuento = round($totalDescuentoMarca + $descuentoGeneral, 2);
        $cuentas = DB::table('aplicacion_pagos as ap')
            ->join('factura as f', 'f.id', '=', 'ap.factura_id')
            ->whereIn('ap.factura_id', $facturaIds)
            ->where('f.estado_venta_id', 1)
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', '<>', 2)
            ->where('ap.saldo', '>', self::TOLERANCIA)
            ->orderByRaw('CASE WHEN f.fecha_vencimiento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('f.fecha_vencimiento')
            ->orderBy('f.fecha_emision')
            ->orderBy('ap.id')
            ->get(['ap.factura_id', 'ap.saldo', 'f.cai']);

        $disponible = min($descuento, round((float) $cuentas->sum('saldo'), 2));
        $restante = $disponible;
        $aplicaciones = [];
        foreach ($cuentas as $cuenta) {
            if ($restante <= self::TOLERANCIA) {
                break;
            }
            $monto = round(min($restante, (float) $cuenta->saldo), 2);
            $aplicaciones[] = [
                'factura_id' => (int) $cuenta->factura_id,
                'factura' => $cuenta->cai,
                'saldo' => round((float) $cuenta->saldo, 2),
                'monto' => $monto,
            ];
            $restante = round($restante - $monto, 2);
        }

        $lineas = $this->saldosLineas->pendientes($cotizacionId);
        $cantidadPendiente = (float) $lineas->sum('cantidad_pendiente');
        $totalOferta = (float) DB::table('cotizacion')->where('id', $cotizacionId)->value('total');

        return [
            'cotizacion_id' => $cotizacionId,
            'flujo_id' => $flujoId,
            'estado' => $expoCotizacion->estado,
            'total_oferta' => round($totalOferta, 2),
            'reglas' => $reglas,
            'factura_ids' => $facturaIds,
            'facturas' => $facturas->map(fn($factura) => [
                'id' => (int) $factura->id,
                'numero' => $factura->cai,
                'fecha' => $factura->fecha_emision,
                'subtotal_bruto' => round((float) ($subtotalesBrutos[$factura->id] ?? 0), 2),
                'total' => round((float) $factura->total, 2),
            ])->values()->all(),
            'total_facturado' => $totalFacturado,
            'base_general' => $baseGeneral,
            'porcentaje_descuento' => $porcentaje,
            'descuento_general' => $descuentoGeneral,
            'descuentos_marca' => $descuentosMarca,
            'descuento_marca_total' => $totalDescuentoMarca,
            'descuento_calculado' => $descuento,
            'saldo_aplicable' => $disponible,
            'diferencia' => round(max($descuento - $disponible, 0), 2),
            'aplicaciones' => $aplicaciones,
            'cantidad_pendiente' => round($cantidadPendiente, 4),
            'lineas_pendientes' => $lineas->filter(fn($linea) => (float) $linea->cantidad_pendiente > 0.0001)
                ->map(fn($linea) => [
                    'linea_id' => (int) $linea->id,
                    'producto_id' => (int) $linea->producto_id,
                    'producto' => $linea->nombre_producto,
                    'cantidad_ofertada' => (float) $linea->cantidad,
                    'cantidad_facturada' => (float) $linea->cantidad_facturada,
                    'cantidad_pendiente' => (float) $linea->cantidad_pendiente,
                ])->values()->all(),
            'cierre_automatico' => $cantidadPendiente <= 0.0001,
        ];
    }

    public function procesar(
        int $cotizacionId,
        int $flujoId,
        ?int $facturaAdicionalId,
        bool $ultimaFactura,
        ?string $motivoCierre,
        int $usuarioId,
        bool $confirmarLiquidacion = false
    ): array {
        if (!DB::transactionLevel()) {
            return DB::transaction(fn() => $this->procesar(
                $cotizacionId,
                $flujoId,
                $facturaAdicionalId,
                $ultimaFactura,
                $motivoCierre,
                $usuarioId,
                $confirmarLiquidacion
            ), 3);
        }

        $expoCotizacion = DB::table('expo_cotizacion')
            ->where('cotizacion_id', $cotizacionId)
            ->lockForUpdate()
            ->first();
        if (!$expoCotizacion) {
            throw ValidationException::withMessages(['cotizacion_id' => 'La oferta Expo no existe.']);
        }

        $liquidacionPendiente = $expoCotizacion->estado === 'PENDIENTE_LIQUIDACION';
        $estadosPermitidos = $confirmarLiquidacion
            ? ['PENDIENTE_LIQUIDACION']
            : ['PENDIENTE_FACTURACION', 'FACTURACION_PARCIAL'];
        if (!in_array($expoCotizacion->estado, $estadosPermitidos, true)) {
            throw ValidationException::withMessages([
                'cotizacion_id' => "La oferta Expo no admite esta operación en estado {$expoCotizacion->estado}.",
            ]);
        }

        $resumen = $this->previsualizar($cotizacionId, $flujoId, $facturaAdicionalId);
        $debeCerrar = $liquidacionPendiente || $resumen['cierre_automatico'] || $ultimaFactura;
        if (!$liquidacionPendiente && $ultimaFactura && !$resumen['cierre_automatico'] && trim((string) $motivoCierre) === '') {
            throw ValidationException::withMessages([
                'motivo_cierre' => 'Debe indicar por qué el cliente no comprará las cantidades pendientes.',
            ]);
        }

        if (!$debeCerrar) {
            $estado = empty($resumen['factura_ids']) ? 'PENDIENTE_FACTURACION' : 'FACTURACION_PARCIAL';
            DB::table('expo_cotizacion')->where('id', $expoCotizacion->id)->update([
                'flujo_id' => $flujoId,
                'estado' => $estado,
            ]);
            $resumen['estado'] = $estado;
            return $resumen;
        }

        if (!$liquidacionPendiente) {
            DB::table('expo_cotizacion')->where('id', $expoCotizacion->id)->update([
                'flujo_id' => $flujoId,
                'estado' => 'PENDIENTE_LIQUIDACION',
                'cierre_manual' => !$resumen['cierre_automatico'],
                'motivo_cierre' => $resumen['cierre_automatico'] ? null : trim((string) $motivoCierre),
                'cerrado_por' => $usuarioId,
                'cerrado_at' => now(),
                'reglas_descuento_snapshot' => json_encode($resumen['reglas']),
                'total_facturado' => $resumen['total_facturado'],
                'porcentaje_descuento_final' => $resumen['porcentaje_descuento'],
                'descuento_calculado' => $resumen['descuento_calculado'],
                'saldo_aplicable' => $resumen['saldo_aplicable'],
                'diferencia_contabilidad' => $resumen['diferencia'],
            ]);
            $resumen['estado'] = 'PENDIENTE_LIQUIDACION';
            $resumen['tipo_cierre'] = $resumen['cierre_automatico'] ? 'FACTURADA_TOTAL' : 'CERRADA_PARCIAL';

            return $resumen;
        }

        if ($resumen['descuento_calculado'] <= self::TOLERANCIA) {
            DB::table('expo_cotizacion')->where('id', $expoCotizacion->id)->update([
                'estado' => 'LIQUIDADA',
                'liquidado_por' => $usuarioId,
                'liquidado_at' => now(),
            ]);
            $resumen['estado'] = 'LIQUIDADA';
            return $resumen;
        }

        $notaCreditoId = $expoCotizacion->nota_credito_id
            ? (int) $expoCotizacion->nota_credito_id
            : $this->crearNotaCredito($resumen, $usuarioId);

        if (!$notaCreditoId) {
            DB::table('expo_cotizacion')->where('id', $expoCotizacion->id)->update(['estado' => 'PENDIENTE_CONTABILIDAD']);
            $resumen['estado'] = 'PENDIENTE_CONTABILIDAD';
            $resumen['mensaje'] = 'No existe un CAI vigente para emitir la nota de crédito. Favor comunicarse con Contabilidad.';
            return $resumen;
        }

        $resultado = $this->gestorCredito->procesarFacturas($notaCreditoId, $resumen['factura_ids'], $usuarioId);
        app(\App\Services\Comisiones\AjustadorComisionNotaCredito::class)->aplicar($notaCreditoId);
        $estado = $resumen['diferencia'] > self::TOLERANCIA ? 'PENDIENTE_CONTABILIDAD' : 'LIQUIDADA';
        DB::table('expo_cotizacion')->where('id', $expoCotizacion->id)->update([
            'estado' => $estado,
            'nota_credito_id' => $notaCreditoId,
            'liquidado_por' => $estado === 'LIQUIDADA' ? $usuarioId : null,
            'liquidado_at' => $estado === 'LIQUIDADA' ? now() : null,
        ]);

        $resumen['estado'] = $estado;
        $resumen['nota_credito_id'] = $notaCreditoId;
        $resumen['aplicaciones_realizadas'] = $resultado['aplicaciones'];
        if ($estado === 'PENDIENTE_CONTABILIDAD') {
            $resumen['mensaje'] = 'El saldo pendiente de las facturas es menor que el descuento calculado. Favor comunicarse con el departamento de Contabilidad.';
        }

        return $resumen;
    }

    public function confirmar(int $cotizacionId, int $flujoId, int $usuarioId): array
    {
        return $this->procesar($cotizacionId, $flujoId, null, true, null, $usuarioId, true);
    }

    private function reglas(object $expoCotizacion): array
    {
        if (!empty($expoCotizacion->reglas_descuento_snapshot)) {
            $snapshot = json_decode($expoCotizacion->reglas_descuento_snapshot, true) ?: [];
            if (array_key_exists('generales', $snapshot)) {
                return [
                    'version' => (int) ($snapshot['version'] ?? 2),
                    'generales' => $snapshot['generales'] ?? [],
                    'marcas' => $snapshot['marcas'] ?? [],
                    'lineas' => $snapshot['lineas'] ?? [],
                ];
            }

            return ['version' => 1, 'generales' => $snapshot, 'marcas' => [], 'lineas' => []];
        }

        $generales = DB::table('expo_descuento')
            ->where('expo_id', $expoCotizacion->expo_id)
            ->orderBy('venta_minima')
            ->orderBy('orden')
            ->get(['venta_minima', 'porcentaje_descuento'])
            ->map(fn($regla) => [
                'venta_minima' => (float) $regla->venta_minima,
                'porcentaje_descuento' => (float) $regla->porcentaje_descuento,
            ])->all();
        $marcas = DB::table('expo_descuento_marca as edm')
            ->join('marca as m', 'm.id', '=', 'edm.marca_id')
            ->where('edm.expo_id', $expoCotizacion->expo_id)
            ->orderBy('edm.orden')
            ->get(['edm.marca_id', 'm.nombre as marca', 'edm.venta_minima', 'edm.porcentaje_descuento', 'edm.orden'])
            ->map(fn($regla) => [
                'marca_id' => (int) $regla->marca_id,
                'marca' => $regla->marca,
                'venta_minima' => (float) $regla->venta_minima,
                'porcentaje_descuento' => (float) $regla->porcentaje_descuento,
                'orden' => (int) $regla->orden,
            ])->all();
        $lineas = DB::table('cotizacion_has_producto as chp')
            ->join('producto as p', 'p.id', '=', 'chp.producto_id')
            ->join('marca as m', 'm.id', '=', 'p.marca_id')
            ->where('chp.cotizacion_id', $expoCotizacion->cotizacion_id)
            ->get(['chp.id as linea_id', 'chp.producto_id', 'p.marca_id', 'm.nombre as marca'])
            ->map(fn($linea) => [
                'linea_id' => (int) $linea->linea_id,
                'producto_id' => (int) $linea->producto_id,
                'marca_id' => (int) $linea->marca_id,
                'marca' => $linea->marca,
            ])->all();

        return ['version' => 2, 'generales' => $generales, 'marcas' => $marcas, 'lineas' => $lineas];
    }

    /** @return array<int, int> */
    private function facturasActivasFlujo(int $flujoId, ?int $facturaAdicionalId): array
    {
        $ids = DB::table('historico_flujo as hf')
            ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $flujoId)
            ->whereIn('hf.tipo_tramite_id', [3, 5])
            ->where('hf.estado_id', '!=', 7)
            ->where('f.estado_venta_id', 1)
            ->pluck('f.id');

        if ($facturaAdicionalId && DB::table('factura')->where('id', $facturaAdicionalId)->where('estado_venta_id', 1)->exists()) {
            $ids->push($facturaAdicionalId);
        }

        return $ids->map(fn($id) => (int) $id)->unique()->values()->all();
    }

    private function crearNotaCredito(array $resumen, int $usuarioId): ?int
    {
        $facturaAncla = DB::table('factura')
            ->whereIn('id', $resumen['factura_ids'])
            ->where('estado_venta_id', 1)
            ->orderBy('fecha_vencimiento')
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->first();
        if (!$facturaAncla) {
            return null;
        }

        $tipoCliente = (int) DB::table('cliente')->where('id', $facturaAncla->cliente_id)->value('tipo_cliente_id');
        $cai = DB::table('cai')
            ->where('tipo_documento_fiscal_id', 3)
            ->where('estado_id', 1)
            ->where('cantidad_no_utilizada', '>', 0)
            ->whereDate('fecha_limite_emision', '>=', now()->toDateString())
            ->lockForUpdate()
            ->first();
        if (!$cai) {
            return null;
        }

        $secuencia = (int) ($tipoCliente === 1 ? $cai->serie : $cai->numero_actual);
        $segmentos = explode('-', $cai->numero_final);
        $secuenciaFinal = count($segmentos) >= 4 ? (int) ltrim($segmentos[3], '0') : 0;
        if (count($segmentos) < 4 || $secuencia <= 0 || $secuenciaFinal <= 0 || $secuencia > $secuenciaFinal) {
            return null;
        }
        $numeroFiscal = $segmentos[0] . '-' . $segmentos[1] . '-' . $segmentos[2] . '-' . sprintf('%08d', $secuencia);

        $porcentaje = $resumen['total_facturado'] > self::TOLERANCIA
            ? (float) $resumen['descuento_calculado'] / (float) $resumen['total_facturado'] * 100
            : 0.0;
        $facturas = DB::table('factura')->whereIn('id', $resumen['factura_ids'])->where('estado_venta_id', 1)->get();
        $subTotal = round((float) $facturas->sum('sub_total') * $porcentaje / 100, 2);
        $grabado = round((float) $facturas->sum('sub_total_grabado') * $porcentaje / 100, 2);
        $isv = round((float) $facturas->sum('isv') * $porcentaje / 100, 2);
        $total = (float) $resumen['descuento_calculado'];
        $exento = round(max($total - $grabado - $isv, 0), 2);

        $notaId = DB::table('nota_credito')->insertGetId([
            'numero_nota' => now()->format('Y') . '-' . (DB::table('nota_credito')->count() + 1),
            'comentario' => json_encode([
                'descripcion' => 'Descuento consolidado de Oferta Expo #' . $resumen['cotizacion_id'],
                'flujo_id' => $resumen['flujo_id'],
                'factura_ids' => $resumen['factura_ids'],
            ]),
            'cai' => $numeroFiscal,
            'numero_secuencia_cai' => $secuencia,
            'fecha' => now(),
            'sub_total' => $subTotal,
            'sub_total_grabado' => $grabado,
            'sub_total_excento' => $exento,
            'isv' => $isv,
            'total' => $total,
            'factura_id' => $facturaAncla->id,
            'cai_id' => $cai->id,
            'motivo_nota_credito_id' => 11,
            'users_id' => $usuarioId,
            'estado_nota_id' => 1,
            'estado_nota_dec' => $tipoCliente === 1 ? 1 : 2,
            'estado_rebajado' => 2,
            'user_registra_rebaja' => 0,
            'comentario_rebajado' => 'Pendiente de aplicación Expo',
            'fecha_rebajado' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cai')->where('id', $cai->id)->update([
            $tipoCliente === 1 ? 'serie' : 'numero_actual' => $secuencia + 1,
            'cantidad_no_utilizada' => max((int) $cai->cantidad_no_utilizada - 1, 0),
        ]);

        return $notaId;
    }
}