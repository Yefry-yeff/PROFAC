<?php

namespace App\Services\NotaCredito;

use App\Services\Comisiones\AjustadorComisionNotaCredito;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Anula notas de crédito o revierte aplicaciones individuales dejando el crédito disponible.
 *
 * Toda reversión es contable y atómica: los movimientos originales conservan su historial
 * (cambian a un tipo "anulado"), se registran movimientos y asientos de reverso, se restauran
 * saldos de cuentas por cobrar, comisiones, ajustes de comisión e inventario.
 */
class AnuladorNotaCredito
{
    public const TIPO_APLICACION = 'aplicacion';
    public const TIPO_REEMBOLSO = 'reembolso';
    public const TIPO_APLICACION_ANULADA = 'aplicacion_anulada';
    public const TIPO_REEMBOLSO_ANULADO = 'reembolso_anulado';
    public const TIPO_REVERSION_APLICACION = 'reversion_aplicacion';
    public const TIPO_REVERSION_REEMBOLSO = 'reversion_reembolso';

    private const DESCRIPCION_LOG_ANULACION = 'Nota credito anulado';
    private const DESCRIPCION_LOG_DEVOLUCION = 'Devolucion de producto';

    public function __construct(
        private GestorCreditoNota $gestor,
        private AjustadorComisionNotaCredito $ajustador
    ) {
    }

    public function previsualizarNota(int $notaCreditoId): array
    {
        $plan = $this->planearNota($notaCreditoId, false);

        return [
            'puede' => empty($plan['bloqueos']),
            'bloqueos' => $plan['bloqueos'],
            'advertencias' => $plan['advertencias'],
            'nota' => [
                'id' => (int) $plan['nota']->id,
                'cai' => $plan['nota']->cai,
                'total' => round((float) $plan['nota']->total, 2),
            ],
            'aplicaciones' => array_values($plan['aplicaciones']),
            'reembolsos' => array_values($plan['reembolsos']),
            'ajustes_comision' => [
                'cantidad' => $plan['ajustes']->count(),
                'monto' => round((float) $plan['ajustes']->sum('monto'), 2),
            ],
            'inventario' => array_values($plan['inventario']),
            'asientos' => count($plan['asientos']),
        ];
    }

    public function anularNota(int $notaCreditoId, string $motivo, int $usuarioId, array $bodegasSeleccionadas = []): array
    {
        $motivo = $this->validarMotivo($motivo);

        return DB::transaction(function () use ($notaCreditoId, $motivo, $usuarioId, $bodegasSeleccionadas) {
            if (!DB::table('nota_credito')->where('id', $notaCreditoId)->exists()) {
                throw new DomainException('La nota de crédito no existe.');
            }

            $this->gestor->asegurarCredito($notaCreditoId);
            $plan = $this->planearNota($notaCreditoId, true, $bodegasSeleccionadas);
            if (!empty($plan['bloqueos'])) {
                throw new DomainException(implode(' ', $plan['bloqueos']));
            }

            $nota = $plan['nota'];
            $credito = $plan['credito'];
            $asientosPorMovimiento = collect($plan['asientos'])->whereNotNull('movimiento_id')->keyBy('movimiento_id');
            $efectos = ['aplicaciones' => [], 'reembolsos' => []];

            foreach ($plan['movimientos'] as $movimiento) {
                if ($movimiento->tipo === self::TIPO_APLICACION) {
                    $efectos['aplicaciones'][] = $this->revertirMovimientoAplicacion(
                        $movimiento,
                        $asientosPorMovimiento->get($movimiento->id),
                        $nota,
                        $usuarioId,
                        $motivo
                    );
                    continue;
                }

                $efectos['reembolsos'][] = $this->revertirMovimientoReembolso(
                    $movimiento,
                    $asientosPorMovimiento->get($movimiento->id),
                    $nota,
                    $usuarioId,
                    $motivo
                );
            }

            foreach ($plan['asientos'] as $asiento) {
                if ($asiento->tipo === 'emision') {
                    $this->reversarAsiento($asiento, 'anulacion_emision', null, $nota->id, $usuarioId);
                }
            }

            $ajustes = $this->ajustador->revertir($notaCreditoId, $usuarioId);
            $inventario = $this->revertirInventario(
                $nota,
                $plan['devoluciones'],
                $plan['lineas'],
                $plan['inventario'],
                $usuarioId,
                $motivo
            );

            DB::table('nota_credito_creditos')->where('id', $credito->id)->update([
                'monto_aplicado' => 0,
                'monto_reembolsado' => 0,
                'saldo_disponible' => 0,
                'estado' => 'anulado',
                'updated_at' => now(),
            ]);
            DB::table('nota_credito')->where('id', $notaCreditoId)->update([
                'estado_nota_id' => 2,
                'updated_at' => now(),
            ]);

            $resultado = [
                'nota_credito_id' => $notaCreditoId,
                'aplicaciones' => $efectos['aplicaciones'],
                'reembolsos' => $efectos['reembolsos'],
                'ajustes_comision' => $ajustes,
                'inventario' => $inventario,
                'asientos_revertidos' => count($plan['asientos']),
                'advertencias' => $plan['advertencias'],
            ];
            $this->registrarAuditoria($notaCreditoId, 'nota', null, $motivo, $resultado, $usuarioId);

            return $resultado;
        }, 3);
    }

    public function previsualizarAplicacion(int $movimientoId): array
    {
        $plan = $this->planearAplicacion($movimientoId, false);

        return [
            'puede' => empty($plan['bloqueos']),
            'bloqueos' => $plan['bloqueos'],
            'advertencias' => [],
            'nota' => $plan['nota'] ? [
                'id' => (int) $plan['nota']->id,
                'cai' => $plan['nota']->cai,
                'total' => round((float) $plan['nota']->total, 2),
            ] : null,
            'aplicaciones' => array_values($plan['aplicaciones']),
            'saldo_resultante' => $plan['saldo_resultante'],
        ];
    }

    public function revertirAplicacion(int $movimientoId, string $motivo, int $usuarioId): array
    {
        $motivo = $this->validarMotivo($motivo);

        return DB::transaction(function () use ($movimientoId, $motivo, $usuarioId) {
            $plan = $this->planearAplicacion($movimientoId, true);
            if (!empty($plan['bloqueos'])) {
                throw new DomainException(implode(' ', $plan['bloqueos']));
            }

            $nota = $plan['nota'];
            $movimiento = $plan['movimiento'];
            $efecto = $this->revertirMovimientoAplicacion($movimiento, $plan['asiento'], $nota, $usuarioId, $motivo);

            $credito = $plan['credito'];
            $monto = round((float) $movimiento->monto, 2);
            $aplicado = max(round((float) $credito->monto_aplicado - $monto, 2), 0);
            $saldo = round((float) $credito->saldo_disponible + $monto, 2);
            $reembolsado = round((float) $credito->monto_reembolsado, 2);
            $estado = $saldo <= 0.005 ? 'consumido' : (($aplicado + $reembolsado) > 0.005 ? 'parcial' : 'disponible');

            DB::table('nota_credito_creditos')->where('id', $credito->id)->update([
                'monto_aplicado' => $aplicado,
                'saldo_disponible' => $saldo,
                'estado' => $estado,
                'updated_at' => now(),
            ]);

            $notaActualiza = [
                'estado_rebajado' => $estado === 'consumido' ? 1 : ($estado === 'parcial' ? 3 : 2),
                'updated_at' => now(),
            ];
            if ($estado === 'disponible') {
                $notaActualiza += [
                    'user_registra_rebaja' => 0,
                    'fecha_rebajado' => null,
                    'comentario_rebajado' => 'N/A',
                ];
            }
            DB::table('nota_credito')->where('id', $nota->id)->update($notaActualiza);

            $resultado = [
                'nota_credito_id' => (int) $nota->id,
                'movimiento_id' => (int) $movimiento->id,
                'aplicacion' => $efecto,
                'saldo_disponible' => $saldo,
                'estado' => $estado,
            ];
            $this->registrarAuditoria((int) $nota->id, 'aplicacion', (int) $movimiento->id, $motivo, $resultado, $usuarioId);

            return $resultado;
        }, 3);
    }

    private function validarMotivo(string $motivo): string
    {
        $motivo = trim($motivo);
        if (mb_strlen($motivo) < 5) {
            throw new DomainException('Ingrese el motivo de la anulación (mínimo 5 caracteres).');
        }

        return Str::limit($motivo, 1000, '');
    }

    private function tabla(string $tabla, bool $bloquear)
    {
        $consulta = DB::table($tabla);

        return $bloquear ? $consulta->lockForUpdate() : $consulta;
    }

    private function planearNota(int $notaCreditoId, bool $bloquear, array $bodegasSeleccionadas = []): array
    {
        $nota = $this->tabla('nota_credito', $bloquear)->where('id', $notaCreditoId)->first();
        if (!$nota) {
            throw new DomainException('La nota de crédito no existe.');
        }

        $bloqueos = [];
        $advertencias = [];
        if ((int) $nota->estado_nota_id === 2) {
            $bloqueos[] = 'Esta nota de crédito ya fue anulada anteriormente.';
        }

        if (DB::table('expo_cotizacion')->where('nota_credito_id', $notaCreditoId)->exists()) {
            $bloqueos[] = 'La nota está vinculada a la liquidación de una Oferta Expo. Revierta primero esa liquidación.';
        }

        $credito = $this->tabla('nota_credito_creditos', $bloquear)->where('nota_credito_id', $notaCreditoId)->first();
        $movimientos = $credito
            ? $this->tabla('nota_credito_movimientos', $bloquear)
                ->where('credito_id', $credito->id)
                ->whereIn('tipo', [self::TIPO_APLICACION, self::TIPO_REEMBOLSO])
                ->orderBy('id')
                ->get()
            : collect();

        $idsMovimientos = $movimientos->pluck('id')->all();
        $asientos = DB::table('nota_credito_asientos')
            ->where('nota_credito_id', $notaCreditoId)
            ->where(function ($query) use ($idsMovimientos) {
                $query->where('tipo', 'emision');
                if ($idsMovimientos) {
                    $query->orWhereIn('movimiento_id', $idsMovimientos);
                }
            })
            ->orderBy('id')
            ->get()
            ->all();

        $aplicaciones = [];
        $reembolsos = [];
        $simulacion = [];
        $apsRestauradas = [];
        foreach ($movimientos as $movimiento) {
            if ($movimiento->tipo === self::TIPO_REEMBOLSO) {
                $reembolsos[$movimiento->id] = [
                    'movimiento_id' => (int) $movimiento->id,
                    'monto' => round((float) $movimiento->monto, 2),
                    'cuenta' => $movimiento->cuenta_reembolso,
                    'fecha' => $movimiento->fecha_movimiento,
                ];
                continue;
            }

            $aplicaciones[$movimiento->id] = $this->describirAplicacion($movimiento, $bloquear, $simulacion);
            if ($aplicaciones[$movimiento->id]['vigente']) {
                $apsRestauradas[] = (int) $movimiento->aplicacion_pagos_id;
            }
        }

        $esLegado = (!$credito || $credito->estado === 'legado_consumido') && $movimientos->isEmpty();
        if ($esLegado && (int) $nota->estado_rebajado === 1) {
            $advertencias[] = 'La nota es anterior al esquema de créditos: el sistema no registró en qué cuenta se aplicó, por lo que no se modifica la cuenta por cobrar de la factura.';
        }

        $lineas = DB::table('nota_credito_has_producto as l')
            ->leftJoin('unidad_medida_venta as u', 'u.id', '=', 'l.unidad_medida_venta_id')
            ->where('l.nota_credito_id', $notaCreditoId)
            ->get(['l.producto_id', 'l.seccion_id', 'l.cantidad', 'l.unidad_medida_venta_id', 'u.unidad_venta']);

        $devoluciones = $this->resolverDevoluciones($nota, $lineas);
        $inventario = [];
        foreach ($devoluciones as $lote => $devolucion) {
            $origen = DB::table('recibido_bodega as rb')
                ->leftJoin('seccion as s', 's.id', '=', 'rb.seccion_id')
                ->leftJoin('segmento as sg', 'sg.id', '=', 's.segmento_id')
                ->leftJoin('bodega as b', 'b.id', '=', 'sg.bodega_id')
                ->leftJoin('producto as p', 'p.id', '=', 'rb.producto_id')
                ->where('rb.id', $lote)
                ->first([
                    'rb.producto_id',
                    'b.id as bodega_id',
                    'b.nombre as bodega',
                    'p.nombre as producto',
                ]);
            $productoId = (int) ($origen->producto_id ?? $devolucion['producto_id'] ?? 0);
            if ($productoId === 0) {
                $productoId = (int) DB::table('venta_has_producto')
                    ->where('factura_id', $nota->factura_id)
                    ->where('lote', $lote)
                    ->value('producto_id');
            }
            $producto = $origen->producto ?? DB::table('producto')->where('id', $productoId)->value('nombre');
            $inventario[$productoId] ??= [
                'producto_id' => $productoId,
                'codigo' => $productoId,
                'producto' => $producto,
                'cantidad' => 0.0,
                'bodegas_origen' => [],
                'bodegas' => [],
            ];
            $devoluciones[$lote]['producto_id'] = $productoId;
            $inventario[$productoId]['cantidad'] += (float) $devolucion['cantidad'];
            if ($origen && $origen->bodega_id) {
                $inventario[$productoId]['bodegas_origen'][(int) $origen->bodega_id] = $origen->bodega;
            }
        }

        foreach ($inventario as $productoId => &$item) {
            $stock = DB::table('recibido_bodega as rb')
                ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
                ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
                ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
                ->where('rb.producto_id', $productoId)
                ->when($bloquear, fn ($query) => $query->lockForUpdate())
                ->get([
                    'rb.id as lote',
                    'rb.cantidad_disponible',
                    'b.id as bodega_id',
                    'b.nombre as bodega',
                ]);
            $bodegas = [];
            foreach ($stock as $fila) {
                $bodegaId = (int) $fila->bodega_id;
                $bodegas[$bodegaId] ??= [
                    'id' => $bodegaId,
                    'nombre' => $fila->bodega,
                    'disponible' => 0.0,
                ];
                $bodegas[$bodegaId]['disponible'] += max(0, (float) $fila->cantidad_disponible);
            }
            foreach ($item['bodegas_origen'] as $bodegaId => $nombre) {
                $bodegas[$bodegaId] ??= [
                    'id' => $bodegaId,
                    'nombre' => $nombre,
                    'disponible' => 0.0,
                ];
            }
            $item['bodegas'] = array_values($bodegas);
            $origenId = (int) (array_key_first($item['bodegas_origen']) ?? 0);
            $seleccionada = (int) ($bodegasSeleccionadas[$productoId] ?? $origenId);
            $bodegaElegida = collect($item['bodegas'])->firstWhere('id', $seleccionada);
            $item['bodega_id'] = $bodegaElegida ? $seleccionada : null;
            $item['bodega'] = $item['bodegas_origen']
                ? implode(', ', array_unique(array_values($item['bodegas_origen'])))
                : 'No disponible';
            $item['disponible'] = (float) ($bodegaElegida['disponible'] ?? 0);
            $item['suficiente'] = (bool) ($bodegaElegida
                && $item['disponible'] + 0.0001 >= $item['cantidad']);
            $item['bloqueo'] = $item['suficiente']
                ? null
                : "No hay inventario suficiente de {$item['producto']} (código {$productoId}) en la bodega seleccionada: requiere {$item['cantidad']} y hay {$item['disponible']}.";
            if (!$item['suficiente']) {
                $bloqueos[] = $item['bloqueo'];
            }
            unset($item['bodegas_origen']);
        }
        unset($item);

        $comisionesNetas = $lineas->isNotEmpty()
            ? DB::table('facturas_comision')
                ->where('factura_id', $nota->factura_id)
                ->where('estado_id', 1)
                ->where('created_at', '>=', $nota->created_at)
                ->when($apsRestauradas, fn ($query) => $query->whereNotIn('aplicacion_pagos_id', $apsRestauradas))
                ->exists()
            : false;
        if ($comisionesNetas) {
            $advertencias[] = 'Las comisiones de la factura de origen se calcularon después de esta nota descontando sus devoluciones. No se recalculan automáticamente: revíselas con Comisiones.';
        }

        return [
            'nota' => $nota,
            'credito' => $credito,
            'movimientos' => $movimientos,
            'asientos' => $asientos,
            'aplicaciones' => $aplicaciones,
            'reembolsos' => $reembolsos,
            'ajustes' => DB::table('nota_credito_ajustes_comision')
                ->where('nota_credito_id', $notaCreditoId)
                ->whereNull('anulado_at')
                ->get(),
            'lineas' => $lineas,
            'devoluciones' => $devoluciones,
            'inventario' => $inventario,
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
        ];
    }

    private function planearAplicacion(int $movimientoId, bool $bloquear): array
    {
        $bloqueos = [];
        $movimiento = $this->tabla('nota_credito_movimientos', $bloquear)->where('id', $movimientoId)->first();
        $credito = $movimiento
            ? $this->tabla('nota_credito_creditos', $bloquear)->where('id', $movimiento->credito_id)->first()
            : null;
        $nota = $credito
            ? $this->tabla('nota_credito', $bloquear)->where('id', $credito->nota_credito_id)->first()
            : null;

        if (!$movimiento || !$credito || !$nota) {
            $bloqueos[] = 'La aplicación de la nota de crédito no existe.';
        } elseif ($movimiento->tipo === self::TIPO_APLICACION_ANULADA) {
            $bloqueos[] = 'Esta aplicación ya fue anulada anteriormente.';
        } elseif ($movimiento->tipo !== self::TIPO_APLICACION) {
            $bloqueos[] = 'Solo se pueden anular aplicaciones de la nota a facturas.';
        } elseif ((int) $nota->estado_nota_id === 2) {
            $bloqueos[] = 'La nota de crédito ya está anulada.';
        }

        $aplicaciones = [];
        $saldoResultante = null;
        $asiento = null;
        if (empty($bloqueos)) {
            $simulacion = [];
            $aplicaciones[$movimiento->id] = $this->describirAplicacion($movimiento, $bloquear, $simulacion);
            $saldoResultante = round((float) $credito->saldo_disponible + (float) $movimiento->monto, 2);
            $asiento = DB::table('nota_credito_asientos')->where('movimiento_id', $movimiento->id)->first();
        }

        return [
            'movimiento' => $movimiento,
            'credito' => $credito,
            'nota' => $nota,
            'asiento' => $asiento,
            'aplicaciones' => $aplicaciones,
            'saldo_resultante' => $saldoResultante,
            'bloqueos' => $bloqueos,
        ];
    }

    private function describirAplicacion(object $movimiento, bool $bloquear, array &$simulacion): array
    {
        $apId = (int) $movimiento->aplicacion_pagos_id;
        $ap = $this->tabla('aplicacion_pagos', $bloquear)->where('id', $apId)->first();
        $factura = DB::table('factura')->where('id', $movimiento->factura_id)->first(['id', 'cai', 'estado_venta_id']);
        $vigente = $ap && (int) $ap->estado === 1 && $factura && (int) $factura->estado_venta_id === 1;

        $estado = $simulacion[$apId] ?? null;
        if ($estado === null && $ap) {
            $estado = [
                'saldo' => (float) $ap->saldo,
                'cerrada' => (int) $ap->estado_cerrado === 2,
                'comisiones' => $vigente ? $this->comisionesActivas((int) $movimiento->factura_id, $apId) : collect(),
            ];
        }

        $saldoNuevo = $estado ? round($estado['saldo'] + (float) $movimiento->monto, 2) : null;
        $reabre = $vigente && $estado['cerrada'] && $saldoNuevo > 0.005;
        $comisiones = $vigente ? $estado['comisiones'] : collect();
        if ($vigente) {
            $simulacion[$apId] = [
                'saldo' => $saldoNuevo,
                'cerrada' => false,
                'comisiones' => collect(),
            ];
        }

        return [
            'movimiento_id' => (int) $movimiento->id,
            'factura_id' => (int) $movimiento->factura_id,
            'factura' => $factura->cai ?? null,
            'monto' => round((float) $movimiento->monto, 2),
            'vigente' => (bool) $vigente,
            'motivo_omision' => $vigente ? null : 'La factura ya está anulada; solo se registra el reverso contable.',
            'saldo_posterior' => $vigente ? $saldoNuevo : null,
            'reabre_factura' => (bool) $reabre,
            'comisiones' => $comisiones->count(),
            'comisiones_monto' => round((float) $comisiones->sum('monto_rol'), 2),
        ];
    }

    private function comisionesActivas(int $facturaId, int $apId)
    {
        return DB::table('facturas_comision')
            ->where('factura_id', $facturaId)
            ->where('aplicacion_pagos_id', $apId)
            ->where('estado_id', 1)
            ->get();
    }

    /**
     * La devolución de inventario se obtiene de la bitácora de lotes que generó la nota.
     * Si una nota antigua no tiene bitácora, se estima desde sus líneas.
     *
     * @return array<int, array{cantidad: float, unidad_medida_venta_id: int|null}>
     */
    private function resolverDevoluciones(object $nota, $lineas): array
    {
        $devoluciones = [];
        $registros = DB::table('log_translado')
            ->where('nota_credito_id', $nota->id)
            ->where('descripcion', self::DESCRIPCION_LOG_DEVOLUCION)
            ->orderBy('id')
            ->get(['origen', 'cantidad', 'unidad_medida_venta_id']);

        foreach ($registros as $registro) {
            $lote = (int) $registro->origen;
            $devoluciones[$lote]['cantidad'] = ($devoluciones[$lote]['cantidad'] ?? 0) + (float) $registro->cantidad;
            $devoluciones[$lote]['unidad_medida_venta_id'] ??= $registro->unidad_medida_venta_id;
        }

        if ($devoluciones || $lineas->isEmpty()) {
            return $devoluciones;
        }

        foreach ($lineas as $linea) {
            $lote = DB::table('venta_has_producto')
                ->where('factura_id', $nota->factura_id)
                ->where('producto_id', $linea->producto_id)
                ->where('seccion_id', $linea->seccion_id)
                ->where('unidad_medida_venta_id', $linea->unidad_medida_venta_id)
                ->orderBy('lote')
                ->value('lote');
            if (!$lote) {
                continue;
            }

            $devoluciones[(int) $lote]['cantidad'] = ($devoluciones[(int) $lote]['cantidad'] ?? 0)
                + (float) $linea->cantidad * (float) ($linea->unidad_venta ?: 1);
            $devoluciones[(int) $lote]['unidad_medida_venta_id'] ??= $linea->unidad_medida_venta_id;
        }

        return $devoluciones;
    }

    private function revertirMovimientoAplicacion(object $movimiento, ?object $asiento, object $nota, int $usuarioId, string $motivo): array
    {
        $efecto = $this->restaurarCuentaPorCobrar($movimiento, $usuarioId);

        $reversionId = $this->registrarMovimientoReverso($movimiento, self::TIPO_REVERSION_APLICACION, $usuarioId, $motivo);
        DB::table('nota_credito_movimientos')->where('id', $movimiento->id)->update([
            'tipo' => self::TIPO_APLICACION_ANULADA,
            'updated_at' => now(),
        ]);
        $this->reversarAsiento($asiento, 'anulacion_aplicacion', $reversionId, (int) $nota->id, $usuarioId);

        return $efecto + ['movimiento_reverso_id' => $reversionId];
    }

    private function revertirMovimientoReembolso(object $movimiento, ?object $asiento, object $nota, int $usuarioId, string $motivo): array
    {
        $reversionId = $this->registrarMovimientoReverso($movimiento, self::TIPO_REVERSION_REEMBOLSO, $usuarioId, $motivo);
        DB::table('nota_credito_movimientos')->where('id', $movimiento->id)->update([
            'tipo' => self::TIPO_REEMBOLSO_ANULADO,
            'updated_at' => now(),
        ]);
        $this->reversarAsiento($asiento, 'anulacion_reembolso', $reversionId, (int) $nota->id, $usuarioId);

        return [
            'movimiento_id' => (int) $movimiento->id,
            'movimiento_reverso_id' => $reversionId,
            'monto' => round((float) $movimiento->monto, 2),
            'cuenta' => $movimiento->cuenta_reembolso,
        ];
    }

    private function registrarMovimientoReverso(object $movimiento, string $tipo, int $usuarioId, string $motivo): int
    {
        return DB::table('nota_credito_movimientos')->insertGetId([
            'credito_id' => $movimiento->credito_id,
            'tipo' => $tipo,
            'monto' => $movimiento->monto,
            'factura_id' => $movimiento->factura_id,
            'aplicacion_pagos_id' => $movimiento->aplicacion_pagos_id,
            'banco_id' => $movimiento->banco_id,
            'cuenta_reembolso' => $movimiento->cuenta_reembolso,
            'tipo_pago_cobro_id' => $movimiento->tipo_pago_cobro_id,
            'referencia' => $movimiento->referencia,
            'comentario' => $motivo,
            'movimiento_origen_id' => $movimiento->id,
            'users_id' => $usuarioId,
            'fecha_movimiento' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function reversarAsiento(?object $original, string $tipo, ?int $movimientoId, int $notaCreditoId, int $usuarioId): void
    {
        if (!$original) {
            return;
        }

        $detalles = DB::table('nota_credito_asiento_detalles')->where('asiento_id', $original->id)->orderBy('id')->get();
        if ($detalles->isEmpty()) {
            return;
        }

        $debe = round((float) $detalles->sum('haber'), 2);
        $haber = round((float) $detalles->sum('debe'), 2);
        if (abs($debe - $haber) > 0.005) {
            throw new DomainException('El asiento original de la nota no está balanceado; no se puede generar su reverso.');
        }

        $asientoId = DB::table('nota_credito_asientos')->insertGetId([
            'nota_credito_id' => $notaCreditoId,
            'movimiento_id' => $movimientoId,
            'tipo' => $tipo,
            'fecha' => now()->toDateString(),
            'descripcion' => Str::limit('Anulación de: ' . $original->descripcion, 255, ''),
            'users_id' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('nota_credito_asiento_detalles')->insert($detalles->map(fn ($detalle) => [
            'asiento_id' => $asientoId,
            'cuenta_codigo' => $detalle->cuenta_codigo,
            'cuenta_nombre' => $detalle->cuenta_nombre,
            'debe' => $detalle->haber,
            'haber' => $detalle->debe,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }

    /**
     * Devuelve a la cuenta por cobrar lo que la nota había rebajado y deshace el cierre
     * (y las comisiones generadas por ese cierre) cuando la factura vuelve a tener saldo.
     */
    private function restaurarCuentaPorCobrar(object $movimiento, int $usuarioId): array
    {
        $monto = round((float) $movimiento->monto, 2);
        $ap = DB::table('aplicacion_pagos')->where('id', $movimiento->aplicacion_pagos_id)->lockForUpdate()->first();
        $factura = DB::table('factura')->where('id', $movimiento->factura_id)->first(['id', 'cai', 'estado_venta_id']);
        $base = [
            'movimiento_id' => (int) $movimiento->id,
            'factura_id' => (int) $movimiento->factura_id,
            'factura' => $factura->cai ?? null,
            'monto' => $monto,
        ];

        if (!$ap || (int) $ap->estado !== 1 || !$factura || (int) $factura->estado_venta_id !== 1) {
            return $base + ['cuenta_restaurada' => false, 'factura_reabierta' => false, 'comisiones' => []];
        }

        $saldo = round((float) $ap->saldo + $monto, 2);
        $actualizacion = [
            'total_notas_credito' => max(round((float) $ap->total_notas_credito - $monto, 2), 0),
            'saldo' => $saldo,
            'ultimo_usr_actualizo' => $usuarioId,
            'updated_at' => now(),
        ];

        $reabre = (int) $ap->estado_cerrado === 2 && $saldo > 0.005;
        if ($reabre) {
            $actualizacion += [
                'estado_cerrado' => 0,
                'usr_cerro' => 0,
                'fecha_cierre_factura' => null,
            ];
        }
        DB::table('aplicacion_pagos')->where('id', $ap->id)->update($actualizacion);

        return $base + [
            'cuenta_restaurada' => true,
            'saldo_posterior' => $saldo,
            'factura_reabierta' => $reabre,
            'comisiones' => $this->revertirComisionesGeneradas((int) $movimiento->factura_id, (int) $ap->id),
        ];
    }

    private function revertirComisionesGeneradas(int $facturaId, int $apId): array
    {
        $comisiones = DB::table('facturas_comision')
            ->where('factura_id', $facturaId)
            ->where('aplicacion_pagos_id', $apId)
            ->where('estado_id', 1)
            ->lockForUpdate()
            ->get();
        if ($comisiones->isEmpty()) {
            return [];
        }

        $factura = DB::table('factura')->where('id', $facturaId)->first(['users_id', 'vendedor', 'gestor_entrega']);
        $revertidas = [];
        foreach ($comisiones as $comision) {
            $userId = match ((int) $comision->tipo_comision) {
                1, 2 => $factura->users_id,
                3 => $factura->vendedor,
                4 => $factura->gestor_entrega,
                default => null,
            };
            $monto = round((float) $comision->monto_rol, 2);

            $revertidas[] = [
                'facturas_comision_id' => (int) $comision->id,
                'user_id' => $userId ? (int) $userId : null,
                'rol_id' => (int) $comision->rol_id,
                'monto' => $monto,
            ] + ($userId && $monto > 0 ? $this->descontarComision($comision, (int) $userId, $monto) : ['modo' => 'sin_monto']);
        }

        $ids = $comisiones->pluck('id')->all();
        DB::table('facturas_comision')->whereIn('id', $ids)->update([
            'estado_id' => 2,
            'monto_rol' => 0,
            'retencion_mora_monto' => 0,
            'retencion_mora_dias' => 0,
            'updated_at' => now(),
        ]);
        DB::table('producto_comision')->whereIn('facturas_comision_id', $ids)->update([
            'estado_id' => 2,
            'updated_at' => now(),
        ]);

        return $revertidas;
    }

    /**
     * Descuenta una comisión ya acreditada. En períodos conciliados no se altera el histórico:
     * si la comisión se había acreditado antes de la conciliación se rebaja en el siguiente
     * período abierto; si se generó después, nunca se acreditó y no hay nada que descontar.
     */
    private function descontarComision(object $comision, int $userId, float $monto): array
    {
        $mes = Carbon::parse($comision->fecha_cierre_factura ?? $comision->created_at)->startOfMonth();
        $periodo = DB::table('comision_periodo')
            ->where('periodo', $mes->toDateString())
            ->where('estado', 1)
            ->first();

        if ($periodo) {
            $acreditada = !$periodo->fecha_conciliacion
                || Carbon::parse($comision->created_at)->lte(Carbon::parse($periodo->fecha_conciliacion));
            if (!$acreditada) {
                return ['modo' => 'periodo_conciliado_sin_acreditar'];
            }

            $destino = $this->ajustador->siguientePeriodoAbierto(
                $mes->copy()->addMonth()->max(now()->startOfMonth())
            );
            $this->ajustador->rebajarAcumulado($userId, (int) $comision->rol_id, $destino, $monto);

            return ['modo' => 'ajuste_periodo_abierto', 'periodo' => $destino->toDateString()];
        }

        $registro = DB::table('comision_empleado')
            ->where('users_comision', $userId)
            ->where('rol_id', $comision->rol_id)
            ->where('mes_comision', $mes->toDateString())
            ->where('estado_id', 1)
            ->lockForUpdate()
            ->first();
        if ($registro) {
            DB::table('comision_empleado')->where('id', $registro->id)->update([
                'comision_acumulada' => max(round((float) $registro->comision_acumulada - $monto, 2), 0),
                'fecha_ult_modificacion' => now(),
                'updated_at' => now(),
            ]);
        }

        return ['modo' => 'periodo_abierto', 'periodo' => $mes->toDateString()];
    }

    /**
     * Quita del inventario lo que la devolución había reingresado y restaura los contadores
     * de la factura para que esas unidades puedan volver a devolverse en otra nota.
     */
    private function revertirInventario(object $nota, array $devoluciones, $lineas, array $inventario, int $usuarioId, string $motivo): array
    {
        $resultado = [];
        foreach ($devoluciones as $lote => $devolucion) {
            $cantidad = (float) $devolucion['cantidad'];
            $recibidoOrigen = DB::table('recibido_bodega')->where('id', $lote)->first();
            $productoId = (int) ($recibidoOrigen->producto_id ?? $devolucion['producto_id'] ?? 0);
            if (!$productoId) {
                $productoId = (int) DB::table('venta_has_producto')
                    ->where('factura_id', $nota->factura_id)
                    ->where('lote', $lote)
                    ->value('producto_id');
            }
            $filaOrigen = DB::table('venta_has_producto')
                ->where('factura_id', $nota->factura_id)
                ->where('lote', $lote)
                ->where('producto_id', $productoId)
                ->first(['seccion_id']);
            $seccionOrigen = $recibidoOrigen->seccion_id ?? $filaOrigen->seccion_id ?? 0;
            $bodegaId = (int) ($inventario[$productoId]['bodega_id'] ?? 0);
            $stocks = DB::table('recibido_bodega as rb')
                ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
                ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
                ->where('rb.producto_id', $productoId)
                ->where('sg.bodega_id', $bodegaId)
                ->where('rb.cantidad_disponible', '>', 0)
                ->orderByRaw('CASE WHEN rb.id = ? THEN 0 ELSE 1 END', [(int) $lote])
                ->orderBy('rb.id')
                ->lockForUpdate()
                ->get(['rb.id', 'rb.cantidad_disponible']);
            $pendienteInventario = $cantidad;
            foreach ($stocks as $stock) {
                $descontar = min($pendienteInventario, (float) $stock->cantidad_disponible);
                if ($descontar <= 0) {
                    continue;
                }
                DB::table('recibido_bodega')->where('id', $stock->id)->update([
                    'cantidad_disponible' => round((float) $stock->cantidad_disponible - $descontar, 4),
                    'updated_at' => now(),
                ]);
                DB::table('log_translado')->insert([
                    'origen' => $stock->id,
                    'destino' => null,
                    'cantidad' => $descontar,
                    'users_id' => $usuarioId,
                    'descripcion' => self::DESCRIPCION_LOG_ANULACION,
                    'comentario' => $motivo,
                    'factura_id' => $nota->factura_id,
                    'nota_credito_id' => $nota->id,
                    'unidad_medida_venta_id' => $devolucion['unidad_medida_venta_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $resultado[] = [
                    'producto_id' => $productoId,
                    'bodega_id' => $bodegaId,
                    'lote' => (int) $stock->id,
                    'cantidad' => $descontar,
                ];
                $pendienteInventario -= $descontar;
            }
            if ($pendienteInventario > 0.0001) {
                throw new DomainException("No hay inventario suficiente en la bodega seleccionada para revertir la devolución del producto {$productoId}.");
            }

            $pendiente = $cantidad;
            $netoLote = 0.0;
            $filas = DB::table('venta_has_producto')
                ->where('factura_id', $nota->factura_id)
                ->where('producto_id', $productoId)
                ->where('seccion_id', $seccionOrigen)
                ->where('lote', $lote)
                ->lockForUpdate()
                ->get();
            foreach ($filas as $fila) {
                $vendido = (float) $fila->numero_unidades_resta_inventario;
                $suma = min($pendiente, max($vendido - (float) $fila->unidades_nota_credito_resta_inventario, 0));
                $pendiente -= $suma;
                $nuevo = (float) $fila->unidades_nota_credito_resta_inventario + $suma;
                $netoLote = max($netoLote, $nuevo);

                DB::table('venta_has_producto')
                    ->where('factura_id', $fila->factura_id)
                    ->where('producto_id', $fila->producto_id)
                    ->where('seccion_id', $fila->seccion_id)
                    ->where('lote', $fila->lote)
                    ->where('unidad_medida_venta_id', $fila->unidad_medida_venta_id)
                    ->where('precio_unidad', $fila->precio_unidad)
                    ->update([
                        'unidades_nota_credito_resta_inventario' => $nuevo,
                        'cantidad_para_entregar' => min($vendido, (float) $fila->cantidad_para_entregar + $suma),
                    ]);
            }

            DB::table('log_translado')
                ->where('origen', $lote)
                ->where('factura_id', $nota->factura_id)
                ->where('descripcion', '<>', self::DESCRIPCION_LOG_ANULACION)
                ->update(['cantidad' => $netoLote, 'updated_at' => now()]);

        }

        foreach ($lineas as $linea) {
            $base = (float) $linea->cantidad * (float) ($linea->unidad_venta ?: 1);
            $filas = DB::table('venta_has_producto')
                ->where('factura_id', $nota->factura_id)
                ->where('producto_id', $linea->producto_id)
                ->where('seccion_id', $linea->seccion_id)
                ->lockForUpdate()
                ->get();
            $totalVendido = (float) $filas->sum('numero_unidades_resta_inventario');

            foreach ($filas as $fila) {
                DB::table('venta_has_producto')
                    ->where('factura_id', $fila->factura_id)
                    ->where('producto_id', $fila->producto_id)
                    ->where('seccion_id', $fila->seccion_id)
                    ->where('lote', $fila->lote)
                    ->where('unidad_medida_venta_id', $fila->unidad_medida_venta_id)
                    ->where('precio_unidad', $fila->precio_unidad)
                    ->update([
                        'resta_inventario_total' => (int) round(min($totalVendido, (float) $fila->resta_inventario_total + $base)),
                        'cantidad_nota_credito' => (int) round(min((float) $fila->cantidad, (float) $fila->cantidad_nota_credito + (float) $linea->cantidad)),
                    ]);
            }
        }

        return $resultado;
    }

    private function registrarAuditoria(int $notaCreditoId, string $alcance, ?int $movimientoId, string $motivo, array $detalle, int $usuarioId): void
    {
        DB::table('nota_credito_reversiones')->insert([
            'nota_credito_id' => $notaCreditoId,
            'alcance' => $alcance,
            'movimiento_id' => $movimientoId,
            'motivo' => $motivo,
            'detalle' => json_encode($detalle, JSON_UNESCAPED_UNICODE),
            'users_id' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
