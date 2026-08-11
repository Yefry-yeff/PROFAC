<?php

namespace App\Services\NotaCredito;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class GestorCreditoNota
{
    public function previsualizarAplicacion(int $clienteId, float $monto): array
    {
        $disponible = round(max($monto, 0), 2);
        $aplicaciones = [];

        foreach ($this->cuentasPendientesCliente($clienteId)->get() as $cuenta) {
            if ($disponible <= 0.005) {
                break;
            }

            $montoAplicado = round(min($disponible, (float) $cuenta->saldo), 2);
            $aplicaciones[] = [
                'factura_id' => (int) $cuenta->factura_id,
                'factura' => $cuenta->cai,
                'saldo_anterior' => round((float) $cuenta->saldo, 2),
                'monto' => $montoAplicado,
                'saldo_posterior' => round(max((float) $cuenta->saldo - $montoAplicado, 0), 2),
            ];
            $disponible = round($disponible - $montoAplicado, 2);
        }

        return [
            'aplicaciones' => $aplicaciones,
            'monto_aplicado' => round($monto - $disponible, 2),
            'saldo_sin_aplicar' => max($disponible, 0),
        ];
    }

    public function asegurarCredito(int $notaCreditoId): object
    {
        $credito = DB::table('nota_credito_creditos')
            ->where('nota_credito_id', $notaCreditoId)
            ->first();

        if ($credito) {
            return $credito;
        }

        $nota = DB::table('nota_credito as nc')
            ->join('factura as f', 'f.id', '=', 'nc.factura_id')
            ->where('nc.id', $notaCreditoId)
            ->select('nc.id', 'nc.total', 'nc.estado_nota_id', 'nc.estado_rebajado', 'f.cliente_id')
            ->first();

        if (!$nota) {
            throw new RuntimeException('La nota de crédito no existe.');
        }

        $disponible = (int) $nota->estado_nota_id === 1 && (int) $nota->estado_rebajado === 2;
        $creditoId = DB::table('nota_credito_creditos')->insertGetId([
            'nota_credito_id' => $nota->id,
            'cliente_id' => $nota->cliente_id,
            'monto_original' => $nota->total,
            'monto_aplicado' => $disponible ? 0 : $nota->total,
            'monto_reembolsado' => 0,
            'saldo_disponible' => $disponible ? $nota->total : 0,
            'estado' => (int) $nota->estado_nota_id === 2
                ? 'anulado'
                : ($disponible ? 'disponible' : 'legado_consumido'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('nota_credito_creditos')->where('id', $creditoId)->first();
    }

    public function procesar(int $notaCreditoId, string $destino, array $reembolso, int $usuarioId): array
    {
        if (!in_array($destino, ['saldos', 'reembolso', 'mixto'], true)) {
            throw new RuntimeException('Seleccione un destino válido para la nota de crédito.');
        }

        return DB::transaction(function () use ($notaCreditoId, $destino, $reembolso, $usuarioId) {
            $this->asegurarCredito($notaCreditoId);

            $credito = DB::table('nota_credito_creditos')
                ->where('nota_credito_id', $notaCreditoId)
                ->lockForUpdate()
                ->first();

            if (!$credito || $credito->estado === 'anulado') {
                throw new RuntimeException('La nota de crédito no está disponible.');
            }

            $disponible = round((float) $credito->saldo_disponible, 2);
            if ($disponible <= 0) {
                throw new RuntimeException('La nota de crédito ya no tiene saldo disponible.');
            }

            $this->registrarAsientoEmision($notaCreditoId, $usuarioId);

            $aplicaciones = [];
            $totalAplicado = 0.0;
            if (in_array($destino, ['saldos', 'mixto'], true)) {
                $cuentas = $this->cuentasPendientesCliente((int) $credito->cliente_id)
                    ->lockForUpdate()
                    ->get();

                foreach ($cuentas as $cuenta) {
                    if ($disponible <= 0.005) {
                        break;
                    }

                    $monto = round(min($disponible, (float) $cuenta->saldo), 2);
                    if ($monto <= 0) {
                        continue;
                    }

                    $nuevoSaldo = round((float) $cuenta->saldo - $monto, 2);
                    DB::table('aplicacion_pagos')->where('id', $cuenta->id)->update([
                        'total_notas_credito' => DB::raw('COALESCE(total_notas_credito, 0) + ' . $monto),
                        'saldo' => max($nuevoSaldo, 0),
                        'ultimo_usr_actualizo' => $usuarioId,
                        'updated_at' => now(),
                    ]);

                    if ($nuevoSaldo <= 0.005) {
                        DB::table('aplicacion_pagos')->where('id', $cuenta->id)->update([
                            'saldo' => 0,
                            'estado_cerrado' => 2,
                            'usr_cerro' => $usuarioId,
                            'fecha_cierre_factura' => now(),
                            'comentario' => 'CIERRE AUTOMÁTICO POR NOTA DE CRÉDITO',
                        ]);
                    }

                    $movimientoId = DB::table('nota_credito_movimientos')->insertGetId([
                        'credito_id' => $credito->id,
                        'tipo' => 'aplicacion',
                        'monto' => $monto,
                        'factura_id' => $cuenta->factura_id,
                        'aplicacion_pagos_id' => $cuenta->id,
                        'comentario' => 'Aplicación automática a saldo pendiente',
                        'users_id' => $usuarioId,
                        'fecha_movimiento' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->registrarAsiento(
                        $notaCreditoId,
                        $movimientoId,
                        'aplicacion',
                        'Aplicación de nota de crédito a ' . $cuenta->cai,
                        $usuarioId,
                        [
                            ['CREDITO_CLIENTE', 'Crédito a favor del cliente', $monto, 0],
                            ['CUENTAS_POR_COBRAR', 'Cuentas por cobrar clientes', 0, $monto],
                        ]
                    );

                    $aplicaciones[] = [
                        'factura_id' => (int) $cuenta->factura_id,
                        'factura' => $cuenta->cai,
                        'monto' => $monto,
                    ];
                    $totalAplicado = round($totalAplicado + $monto, 2);
                    $disponible = round($disponible - $monto, 2);
                }
            }

            $totalReembolsado = 0.0;
            if (in_array($destino, ['reembolso', 'mixto'], true) && $disponible > 0.005) {
                if (empty($reembolso['banco_id']) || empty($reembolso['tipo_pago_cobro_id']) || empty($reembolso['fecha'])) {
                    throw new RuntimeException('Seleccione la cuenta, el método y la fecha del reembolso.');
                }

                $bancoReembolso = DB::table('banco')
                    ->where('id', (int) $reembolso['banco_id'])
                    ->first(['id', 'cuenta']);
                if (!$bancoReembolso) {
                    throw new RuntimeException('La cuenta seleccionada para el reembolso no existe.');
                }

                $totalReembolsado = $disponible;
                $movimientoId = DB::table('nota_credito_movimientos')->insertGetId([
                    'credito_id' => $credito->id,
                    'tipo' => 'reembolso',
                    'monto' => $totalReembolsado,
                    'banco_id' => (int) $reembolso['banco_id'],
                    'cuenta_reembolso' => $bancoReembolso->cuenta,
                    'tipo_pago_cobro_id' => (int) $reembolso['tipo_pago_cobro_id'],
                    'referencia' => $reembolso['referencia'] ?? null,
                    'comprobante' => $reembolso['comprobante'] ?? null,
                    'comentario' => $reembolso['comentario'] ?? 'Reembolso automático de nota de crédito',
                    'users_id' => $usuarioId,
                    'fecha_movimiento' => $reembolso['fecha'] ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->registrarAsiento(
                    $notaCreditoId,
                    $movimientoId,
                    'reembolso',
                    'Reembolso de nota de crédito',
                    $usuarioId,
                    [
                        ['CREDITO_CLIENTE', 'Crédito a favor del cliente', $totalReembolsado, 0],
                        ['BANCO_CAJA', 'Banco o caja', 0, $totalReembolsado],
                    ],
                    $reembolso['fecha'] ?? now()->toDateString()
                );
                $disponible = 0.0;
            }

            $montoAplicado = round((float) $credito->monto_aplicado + $totalAplicado, 2);
            $montoReembolsado = round((float) $credito->monto_reembolsado + $totalReembolsado, 2);
            $estado = $disponible <= 0.005 ? 'consumido' : ($montoAplicado + $montoReembolsado > 0 ? 'parcial' : 'disponible');

            DB::table('nota_credito_creditos')->where('id', $credito->id)->update([
                'monto_aplicado' => $montoAplicado,
                'monto_reembolsado' => $montoReembolsado,
                'saldo_disponible' => max($disponible, 0),
                'estado' => $estado,
                'updated_at' => now(),
            ]);

            DB::table('nota_credito')->where('id', $notaCreditoId)->update([
                'estado_rebajado' => $estado === 'consumido' ? 1 : ($estado === 'parcial' ? 3 : 2),
                'user_registra_rebaja' => $usuarioId,
                'fecha_rebajado' => now()->toDateString(),
                'comentario_rebajado' => 'Gestión automática: ' . $destino,
                'updated_at' => now(),
            ]);

            return [
                'nota_credito_id' => $notaCreditoId,
                'aplicaciones' => $aplicaciones,
                'monto_aplicado' => $totalAplicado,
                'monto_reembolsado' => $totalReembolsado,
                'saldo_disponible' => max($disponible, 0),
                'estado' => $estado,
            ];
        }, 3);
    }

    private function cuentasPendientesCliente(int $clienteId)
    {
        return DB::table('aplicacion_pagos as ap')
            ->join('factura as f', 'f.id', '=', 'ap.factura_id')
            ->where('ap.cliente_id', $clienteId)
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', '<>', 2)
            ->where('ap.saldo', '>', 0.005)
            ->orderByRaw('CASE WHEN f.fecha_vencimiento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('f.fecha_vencimiento')
            ->orderBy('f.fecha_emision')
            ->orderBy('ap.id')
            ->select('ap.id', 'ap.factura_id', 'ap.saldo', 'f.cai');
    }

    private function registrarAsientoEmision(int $notaCreditoId, int $usuarioId): void
    {
        if (DB::table('nota_credito_asientos')
            ->where('nota_credito_id', $notaCreditoId)
            ->where('tipo', 'emision')
            ->exists()) {
            return;
        }

        $nota = DB::table('nota_credito')->where('id', $notaCreditoId)->first();
        if (!$nota) {
            throw new RuntimeException('No se pudo generar el asiento de emisión de la nota.');
        }

        $total = round((float) $nota->total, 2);
        $isv = round(min((float) $nota->isv, $total), 2);
        $base = round($total - $isv, 2);
        $detalles = [];
        if ($base > 0) {
            $detalles[] = ['DEVOLUCIONES_VENTAS', 'Devoluciones y descuentos sobre ventas', $base, 0];
        }
        if ($isv > 0) {
            $detalles[] = ['ISV_DEBITO_FISCAL', 'ISV débito fiscal por ajustar', $isv, 0];
        }
        $detalles[] = ['CREDITO_CLIENTE', 'Crédito a favor del cliente', 0, $total];

        $this->registrarAsiento(
            $notaCreditoId,
            null,
            'emision',
            'Emisión de nota de crédito ' . $nota->cai,
            $usuarioId,
            $detalles,
            $nota->fecha
        );
    }

    private function registrarAsiento(
        int $notaCreditoId,
        ?int $movimientoId,
        string $tipo,
        string $descripcion,
        int $usuarioId,
        array $detalles,
        $fecha = null
    ): void {
        $debe = round(array_sum(array_column($detalles, 2)), 2);
        $haber = round(array_sum(array_column($detalles, 3)), 2);
        if (abs($debe - $haber) > 0.005) {
            throw new RuntimeException('El ajuste contable de la nota no está balanceado.');
        }

        $asientoId = DB::table('nota_credito_asientos')->insertGetId([
            'nota_credito_id' => $notaCreditoId,
            'movimiento_id' => $movimientoId,
            'tipo' => $tipo,
            'fecha' => $fecha ?: now()->toDateString(),
            'descripcion' => $descripcion,
            'users_id' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('nota_credito_asiento_detalles')->insert(array_map(function ($detalle) use ($asientoId) {
            return [
                'asiento_id' => $asientoId,
                'cuenta_codigo' => $detalle[0],
                'cuenta_nombre' => $detalle[1],
                'debe' => $detalle[2],
                'haber' => $detalle[3],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $detalles));
    }
}