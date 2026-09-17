<?php

namespace App\Services\Expo;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiquidacionOfertaExpo
{
    private const TOLERANCIA = 0.005;

    public function __construct(
        private SaldoLineasOferta $saldosLineas,
        private CalculadorDescuentosExpo $calculador,
        private GestorAumentoExpo $gestorAumento
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
        $facturas = DB::table('factura as f')
            ->leftJoin('users as asesor', 'asesor.id', '=', 'f.vendedor')
            ->leftJoin('users as teleasesor', 'teleasesor.id', '=', 'f.users_id')
            ->leftJoin('users as gestor', 'gestor.id', '=', 'f.gestor_entrega')
            ->whereIn('f.id', $facturaIds)
            ->where('f.estado_venta_id', 1)
            ->orderBy('f.fecha_vencimiento')
            ->orderBy('f.fecha_emision')
            ->orderBy('f.id')
            ->get([
                'f.id', 'f.cai', 'f.fecha_emision', 'f.fecha_vencimiento', 'f.sub_total',
                'f.sub_total_grabado', 'f.sub_total_excento', 'f.isv', 'f.total',
                'f.cliente_id', 'f.nombre_cliente', 'asesor.name as asesor',
                'teleasesor.name as teleasesor', 'gestor.name as gestor',
            ]);

        $detallesFacturados = DB::table('venta_has_producto as vhp')
            ->join('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'vhp.precios_producto_carga_id')
            ->leftJoin('cotizacion_has_producto as chp', 'chp.id', '=', 'vhp.cotizacion_has_producto_id')
            ->whereIn('vhp.factura_id', $facturaIds)
            ->where(function ($query) use ($cotizacionId) {
                $query->where('chp.cotizacion_id', $cotizacionId)
                    ->orWhereNull('vhp.cotizacion_has_producto_id');
            })
            ->get([
                'vhp.factura_id',
                'vhp.cotizacion_has_producto_id as linea_id',
                'vhp.cantidad_s',
                'vhp.precio_unidad as precio_facturado',
                'vhp.sub_total_s as subtotal_neto_facturado',
                'p.marca_id as marca_producto_id',
                'ppc.categoria_precios_id as escala_producto_id',
                'chp.cantidad as cantidad_ofertada',
                'chp.precio_unidad as precio_ofertado',
                'chp.monto_descProducto as descuento_linea_oferta',
            ]);
        $subtotalesBrutos = $detallesFacturados
            ->groupBy('factura_id')
            ->map(fn($detalles) => (float) $detalles->sum(
                fn($detalle) => (float) $detalle->precio_facturado * (float) $detalle->cantidad_s
            ));

        $totalFacturado = round((float) $subtotalesBrutos->sum(), 2);
        $reglas = $this->reglas($expoCotizacion);
        $usaEscalas = ($reglas['tipo'] ?? null) === 'escala' || (int) ($reglas['version'] ?? 0) >= 5;
        $lineasSnapshot = collect($reglas['lineas'])->keyBy('linea_id');
        $lineasCalculo = [];
        $descuentoOtorgado = 0.0;
        $descuentosOtorgadosFactura = [];
        $descuentosOtorgadosMarca = [];
        foreach ($detallesFacturados as $detalle) {
            $lineaSnapshot = $lineasSnapshot->get((int) $detalle->linea_id, []);
            $marcaId = (int) ($usaEscalas
                ? ($lineaSnapshot['escala_id'] ?? $detalle->escala_producto_id ?? 0)
                : ($lineaSnapshot['marca_id'] ?? $detalle->marca_producto_id ?? 0));
            $subtotalBruto = round((float) $detalle->precio_facturado * (float) $detalle->cantidad_s, 4);
            $lineasCalculo[] = [
                $usaEscalas ? 'escala_id' : 'marca_id' => $marcaId,
                'subtotal_bruto' => $subtotalBruto,
            ];
            $descuentoLinea = max($subtotalBruto - (float) $detalle->subtotal_neto_facturado, 0);
            $descuentoOtorgado += $descuentoLinea;
            $facturaId = (int) $detalle->factura_id;
            $descuentosOtorgadosFactura[$facturaId] = ($descuentosOtorgadosFactura[$facturaId] ?? 0)
                + $descuentoLinea;
            $descuentosOtorgadosMarca[$marcaId] = ($descuentosOtorgadosMarca[$marcaId] ?? 0)
                + $descuentoLinea;
        }
        $descuentoOtorgado = round($descuentoOtorgado, 2);
        $calculo = $this->calculador->calcular($lineasCalculo, $reglas);
        $descuentoGanado = (float) $calculo['descuento_ganado'];
        $aumento = round(max($descuentoOtorgado - $descuentoGanado, 0), 2);

        $descuentosMarca = [];
        $porcentajesGrupo = $usaEscalas ? ($calculo['porcentajes_escala'] ?? []) : $calculo['porcentajes_marca'];
        $reglasGrupo = $usaEscalas ? ($reglas['escalas'] ?? []) : ($reglas['marcas'] ?? []);
        foreach ($porcentajesGrupo as $marcaId => $porcentajeMarca) {
            $reglaMarca = collect($reglasGrupo)->first(fn($regla) => (int) ($regla[$usaEscalas ? 'escala_id' : 'marca_id'] ?? 0) === (int) $marcaId);
            $nombreGrupo = $reglaMarca[$usaEscalas ? 'escala' : 'marca'] ?? (($usaEscalas ? 'Escala ' : 'Marca ') . $marcaId);
            $descuentosMarca[] = [
                'marca_id' => (int) $marcaId,
                'marca' => $nombreGrupo,
                'escala_id' => $usaEscalas ? (int) $marcaId : null,
                'escala' => $usaEscalas ? $nombreGrupo : null,
                'porcentaje_descuento' => (float) $porcentajeMarca,
            ];
        }
        $detalleMarcas = collect($calculo['detalle_marcas'])->map(function (array $detalle) use ($reglas, $reglasGrupo, $descuentosOtorgadosMarca, $usaEscalas) {
            $marcaId = (int) $detalle['marca_id'];
            $claveGrupo = $usaEscalas ? 'escala_id' : 'marca_id';
            $nombreGrupo = $usaEscalas ? 'escala' : 'marca';
            $reglaMarca = collect($reglasGrupo)->first(fn($regla) => (int) ($regla[$claveGrupo] ?? 0) === $marcaId);
            $lineaMarca = collect($reglas['lineas'])->first(fn($linea) => (int) ($linea[$claveGrupo] ?? 0) === $marcaId);

            return array_merge($detalle, [
                'marca' => $reglaMarca[$nombreGrupo] ?? $lineaMarca[$nombreGrupo] ?? (($usaEscalas ? 'Escala ' : 'Marca ') . $marcaId),
                'descuento_otorgado' => round((float) ($descuentosOtorgadosMarca[$marcaId] ?? 0), 2),
            ]);
        })->values()->all();

        $lineas = $this->saldosLineas->pendientes($cotizacionId);
        $cantidadPendiente = (float) $lineas->sum('cantidad_pendiente');
        $totalOferta = (float) DB::table('cotizacion')->where('id', $cotizacionId)->value('total');

        return [
            'cotizacion_id' => $cotizacionId,
            'flujo_id' => $flujoId,
            'estado' => $expoCotizacion->estado,
            'tipo_descuento' => $usaEscalas ? 'escala' : 'marca',
            'total_oferta' => round($totalOferta, 2),
            'reglas' => $reglas,
            'factura_ids' => $facturaIds,
            'facturas' => $facturas->map(fn($factura) => [
                'id' => (int) $factura->id,
                'numero' => $factura->cai,
                'fecha' => $factura->fecha_emision,
                'subtotal_bruto' => round((float) ($subtotalesBrutos[$factura->id] ?? 0), 2),
                'total' => round((float) $factura->total, 2),
                'descuento_otorgado' => round((float) ($descuentosOtorgadosFactura[$factura->id] ?? 0), 2),
                'cliente' => $factura->nombre_cliente,
                'asesor' => $factura->asesor ?: 'Sin asignar',
                'teleasesor' => $factura->teleasesor ?: 'Sin asignar',
                'gestor' => $factura->gestor ?: 'Sin asignar',
            ])->values()->all(),
            'total_facturado' => $totalFacturado,
            'base_general' => round($totalFacturado - (float) $calculo['descuento_marca'], 2),
            'porcentaje_descuento' => (float) $calculo['porcentaje_general'],
            'descuento_general' => (float) $calculo['descuento_general'],
            'descuentos_marca' => $descuentosMarca,
            'detalle_marcas' => $detalleMarcas,
            'descuento_marca_total' => (float) $calculo['descuento_marca'],
            'descuento_otorgado' => $descuentoOtorgado,
            'descuento_ganado' => $descuentoGanado,
            'aumento_calculado' => $aumento,
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
        bool $confirmarLiquidacion = false,
        array $facturasExcluidas = []
    ): array {
        if (!DB::transactionLevel()) {
            return DB::transaction(fn() => $this->procesar(
                $cotizacionId,
                $flujoId,
                $facturaAdicionalId,
                $ultimaFactura,
                $motivoCierre,
                $usuarioId,
                $confirmarLiquidacion,
                $facturasExcluidas
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
                'reapertura_autorizada' => false,
                'motivo_cierre' => $resumen['cierre_automatico'] ? null : trim((string) $motivoCierre),
                'cerrado_por' => $usuarioId,
                'cerrado_at' => now(),
                'reglas_descuento_snapshot' => json_encode($resumen['reglas']),
                'total_facturado' => $resumen['total_facturado'],
                'porcentaje_descuento_final' => $resumen['porcentaje_descuento'],
                'aumento_calculado' => $resumen['aumento_calculado'],
                'aumento_aplicado' => 0,
            ]);
            $resumen['estado'] = 'PENDIENTE_LIQUIDACION';
            $resumen['tipo_cierre'] = $resumen['cierre_automatico'] ? 'FACTURADA_TOTAL' : 'CERRADA_PARCIAL';
        }

        if ($resumen['aumento_calculado'] <= self::TOLERANCIA) {
            DB::table('expo_cotizacion')->where('id', $expoCotizacion->id)->update([
                'estado' => 'LIQUIDADA',
                'liquidado_por' => $usuarioId,
                'liquidado_at' => now(),
            ]);
            $resumen['estado'] = 'LIQUIDADA';
            return $resumen;
        }

        $movimientos = $this->gestorAumento->aplicar(
            (int) $expoCotizacion->id,
            $resumen['facturas'],
            (float) $resumen['aumento_calculado'],
            $usuarioId,
            $facturasExcluidas
        );
        $aumentoAplicado = round((float) collect($movimientos)->sum('monto'), 2);
        $exclusiones = DB::table('expo_cotizacion_aumento_exclusion')
            ->where('expo_cotizacion_id', $expoCotizacion->id)
            ->whereNull('anulada_at')
            ->get(['factura_id', 'monto_exonerado'])
            ->map(fn ($exclusion) => [
                'factura_id' => (int) $exclusion->factura_id,
                'monto' => (float) $exclusion->monto_exonerado,
            ])->all();
        DB::table('expo_cotizacion')->where('id', $expoCotizacion->id)->update([
            'estado' => 'LIQUIDADA',
            'aumento_aplicado' => $aumentoAplicado,
            'liquidado_por' => $usuarioId,
            'liquidado_at' => now(),
        ]);

        $resumen['estado'] = 'LIQUIDADA';
        $resumen['aumentos_realizados'] = $movimientos;
        $resumen['facturas_excluidas'] = $exclusiones;
        $resumen['aumento_aplicado'] = $aumentoAplicado;

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
                    'tipo' => $snapshot['tipo'] ?? 'marca',
                    'generales' => $snapshot['generales'] ?? [],
                    'marcas' => $snapshot['marcas'] ?? [],
                    'escalas' => $snapshot['escalas'] ?? [],
                    'lineas' => $snapshot['lineas'] ?? [],
                    'descuentos_forzados' => $snapshot['descuentos_forzados'] ?? [],
                    'descuento_modo' => $snapshot['descuento_modo'] ?? 'automatico',
                    'descuento_escalon' => $snapshot['descuento_escalon'] ?? null,
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
        $clienteId = DB::table('cotizacion')
            ->where('id', $expoCotizacion->cotizacion_id)
            ->value('cliente_id');
        $clienteAsistio = $clienteId && DB::table('expo_asistencia')
            ->where('expo_id', $expoCotizacion->expo_id)
            ->where('cliente_id', $clienteId)
            ->exists();
        $marcas = DB::table('expo_descuento_marca as edm')
            ->join('marca as m', 'm.id', '=', 'edm.marca_id')
            ->where('edm.expo_id', $expoCotizacion->expo_id)
            ->when(!$clienteAsistio, fn ($query) => $query->where('edm.requiere_asistencia', false))
            ->orderBy('edm.orden')
            ->get(['edm.marca_id', 'm.nombre as marca', 'edm.venta_minima', 'edm.porcentaje_descuento', 'edm.requiere_asistencia', 'edm.orden'])
            ->map(fn($regla) => [
                'marca_id' => (int) $regla->marca_id,
                'marca' => $regla->marca,
                'venta_minima' => (float) $regla->venta_minima,
                'porcentaje_descuento' => (float) $regla->porcentaje_descuento,
                'requiere_asistencia' => (bool) $regla->requiere_asistencia,
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

}