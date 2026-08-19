<?php

namespace App\Services\Expo;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GestorAumentoExpo
{
    private const TOLERANCIA = 0.005;

    /**
     * @param array<int, array{id:int, descuento_otorgado:float}> $facturas
     */
    public function aplicar(int $expoCotizacionId, array $facturas, float $monto, int $usuarioId): array
    {
        $existentes = DB::table('expo_cotizacion_aumento')
            ->where('expo_cotizacion_id', $expoCotizacionId)
            ->whereNull('disminucion_movimiento_id')
            ->get(['otros_movimientos_id', 'factura_id', 'monto']);

        if ($existentes->isNotEmpty()) {
            return $existentes->map(fn ($movimiento) => [
                'movimiento_id' => (int) $movimiento->otros_movimientos_id,
                'factura_id' => (int) $movimiento->factura_id,
                'monto' => (float) $movimiento->monto,
            ])->all();
        }

        $distribucion = $this->distribuir($facturas, $monto);
        $movimientos = [];

        foreach ($distribucion as $asignacion) {
            $cuenta = DB::table('aplicacion_pagos')
                ->where('factura_id', $asignacion['factura_id'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first(['id']);

            if (!$cuenta) {
                throw ValidationException::withMessages([
                    'factura' => "La factura {$asignacion['factura_id']} no tiene una cuenta por cobrar para aplicar el aumento.",
                ]);
            }

            $comentario = "Aumento por descuento Expo no alcanzado. Oferta #{$expoCotizacionId}.";
            $movimientoId = DB::table('otros_movimientos')->insertGetId([
                'aplicacion_pagos_id' => $cuenta->id,
                'factura_id' => $asignacion['factura_id'],
                'monto' => $asignacion['monto'],
                'comentario' => $comentario,
                'usr_registro' => $usuarioId,
                'estado' => 1,
                'tipo_movimiento' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $resultado = DB::select(
                'CALL sp_aplicacion_pagos(?, ?, ?, ?, ?, ?, ?, ?, @estado, @msjResultado)',
                [7, 0, $usuarioId, $asignacion['factura_id'], $comentario, $cuenta->id, 1, $asignacion['monto']]
            );

            if ((int) ($resultado[0]->estado ?? -1) === -1) {
                throw ValidationException::withMessages([
                    'aumento' => "No se pudo aplicar el aumento a la factura {$asignacion['factura_id']}.",
                ]);
            }

            DB::table('expo_cotizacion_aumento')->insert([
                'expo_cotizacion_id' => $expoCotizacionId,
                'otros_movimientos_id' => $movimientoId,
                'factura_id' => $asignacion['factura_id'],
                'monto' => $asignacion['monto'],
                'created_at' => now(),
            ]);

            $movimientos[] = [
                'movimiento_id' => (int) $movimientoId,
                'factura_id' => $asignacion['factura_id'],
                'monto' => $asignacion['monto'],
            ];
        }

        return $movimientos;
    }

    public function revertir(int $expoCotizacionId, int $usuarioId): array
    {
        $aumentos = DB::table('expo_cotizacion_aumento')
            ->where('expo_cotizacion_id', $expoCotizacionId)
            ->whereNull('disminucion_movimiento_id')
            ->lockForUpdate()
            ->get();
        $disminuciones = [];

        foreach ($aumentos as $aumento) {
            $movimientoAumento = DB::table('otros_movimientos')
                ->where('id', $aumento->otros_movimientos_id)
                ->first(['aplicacion_pagos_id']);
            if (!$movimientoAumento) {
                throw ValidationException::withMessages([
                    'disminucion' => "No se encontró el aumento Expo {$aumento->otros_movimientos_id} que debe revertirse.",
                ]);
            }

            $comentario = "Disminución por reapertura de Oferta Expo #{$expoCotizacionId}. Revierte aumento #{$aumento->otros_movimientos_id}.";
            $movimientoId = DB::table('otros_movimientos')->insertGetId([
                'aplicacion_pagos_id' => $movimientoAumento->aplicacion_pagos_id,
                'factura_id' => $aumento->factura_id,
                'monto' => $aumento->monto,
                'comentario' => $comentario,
                'usr_registro' => $usuarioId,
                'estado' => 1,
                'tipo_movimiento' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $resultado = DB::select(
                'CALL sp_aplicacion_pagos(?, ?, ?, ?, ?, ?, ?, ?, @estado, @msjResultado)',
                [7, 0, $usuarioId, $aumento->factura_id, $comentario, $movimientoAumento->aplicacion_pagos_id, 2, $aumento->monto]
            );
            if ((int) ($resultado[0]->estado ?? -1) === -1) {
                throw ValidationException::withMessages([
                    'disminucion' => "No se pudo aplicar la disminución a la factura {$aumento->factura_id}.",
                ]);
            }

            DB::table('expo_cotizacion_aumento')->where('id', $aumento->id)->update([
                'disminucion_movimiento_id' => $movimientoId,
                'revertido_por' => $usuarioId,
                'revertido_at' => now(),
            ]);
            $disminuciones[] = [
                'movimiento_id' => (int) $movimientoId,
                'factura_id' => (int) $aumento->factura_id,
                'monto' => (float) $aumento->monto,
            ];
        }

        return $disminuciones;
    }

    /**
     * @param array<int, array{id:int, descuento_otorgado:float}> $facturas
     * @return array<int, array{factura_id:int, monto:float}>
     */
    public function distribuir(array $facturas, float $monto): array
    {
        $monto = round(max($monto, 0), 2);
        if ($monto <= self::TOLERANCIA) {
            return [];
        }

        $facturas = array_values(array_filter(
            $facturas,
            fn (array $factura) => (int) ($factura['id'] ?? 0) > 0
                && (float) ($factura['descuento_otorgado'] ?? 0) > self::TOLERANCIA
        ));
        $totalOtorgado = array_sum(array_column($facturas, 'descuento_otorgado'));

        if (!$facturas || $totalOtorgado <= self::TOLERANCIA) {
            throw ValidationException::withMessages([
                'aumento' => 'No hay descuentos otorgados por factura para distribuir el aumento Expo.',
            ]);
        }

        $restante = $monto;
        $ultima = count($facturas) - 1;

        return array_map(function (array $factura, int $indice) use ($monto, $totalOtorgado, &$restante, $ultima) {
            $asignado = $indice === $ultima
                ? $restante
                : round($monto * (float) $factura['descuento_otorgado'] / $totalOtorgado, 2);
            $restante = round($restante - $asignado, 2);

            return [
                'factura_id' => (int) $factura['id'],
                'monto' => $asignado,
            ];
        }, $facturas, array_keys($facturas));
    }
}