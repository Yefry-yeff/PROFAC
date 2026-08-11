<?php

namespace App\Services\Comisiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AjustadorComisionNotaCredito
{
    public function aplicar(int $notaCreditoId): array
    {
        return DB::transaction(fn() => $this->aplicarEnTransaccion($notaCreditoId));
    }

    private function aplicarEnTransaccion(int $notaCreditoId): array
    {
        $nota = DB::table('nota_credito')->where('id', $notaCreditoId)->lockForUpdate()->first();
        if (!$nota || (int) $nota->estado_nota_id !== 1) {
            return [];
        }

        $comisiones = DB::table('facturas_comision')
            ->where('factura_id', $nota->factura_id)
            ->where('estado_id', 1)
            ->lockForUpdate()
            ->get();

        if ($comisiones->isEmpty()) {
            return [];
        }

        $factura = DB::table('factura')->where('id', $nota->factura_id)->first();
        $lineasNota = DB::table('nota_credito_has_producto')
            ->where('nota_credito_id', $notaCreditoId)
            ->get();
        $resultado = [];

        foreach ($comisiones as $comision) {
            $existente = DB::table('nota_credito_ajustes_comision')
                ->where('nota_credito_id', $notaCreditoId)
                ->where('facturas_comision_id', $comision->id)
                ->first();
            if ($existente) {
                $resultado[] = $existente;
                continue;
            }

            [$montoCalculado, $detalle] = $this->calcularMonto($nota, $factura, $comision, $lineasNota);
            $ajustadoAnterior = (float) DB::table('nota_credito_ajustes_comision')
                ->where('facturas_comision_id', $comision->id)
                ->sum('monto');
            $disponible = max(0, round((float) $comision->monto_rol - $ajustadoAnterior, 2));
            $monto = min(round($montoCalculado, 2), $disponible);

            if ($monto <= 0) {
                continue;
            }

            $userId = $this->resolverEmpleado($factura, (int) $comision->tipo_comision);
            if (!$userId) {
                continue;
            }

            $periodoOriginal = Carbon::parse($comision->fecha_cierre_factura)->startOfMonth();
            $pagada = DB::table('comision_periodo')
                ->where('periodo', $periodoOriginal->toDateString())
                ->where('estado', 1)
                ->exists();
            $inicioRebaja = $periodoOriginal->copy()->addMonth();
            $periodoNota = Carbon::parse($nota->fecha)->startOfMonth();
            if ($inicioRebaja->lt($periodoNota)) {
                $inicioRebaja = $periodoNota;
            }
            $periodoAplicado = $pagada ? $this->siguientePeriodoAbierto($inicioRebaja) : $periodoOriginal;

            DB::table('nota_credito_ajustes_comision')->insert([
                'nota_credito_id' => $notaCreditoId,
                'facturas_comision_id' => $comision->id,
                'factura_id' => $nota->factura_id,
                'user_id' => $userId,
                'rol_id' => $comision->rol_id,
                'periodo_original' => $periodoOriginal->toDateString(),
                'periodo_aplicado' => $periodoAplicado->toDateString(),
                'monto' => $monto,
                'comision_pagada' => $pagada,
                'detalle_calculo' => json_encode($detalle),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->rebajarAcumulado($userId, (int) $comision->rol_id, $periodoAplicado, $monto);
            $resultado[] = [
                'facturas_comision_id' => $comision->id,
                'user_id' => $userId,
                'rol_id' => $comision->rol_id,
                'monto' => $monto,
                'periodo_aplicado' => $periodoAplicado->toDateString(),
                'comision_pagada' => $pagada,
            ];
        }

        return $resultado;
    }

    private function calcularMonto(object $nota, object $factura, object $comision, $lineasNota): array
    {
        if ($lineasNota->isEmpty()) {
            $baseFactura = max((float) $factura->sub_total, 0.01);
            $proporcion = min(1, max(0, (float) $nota->sub_total / $baseFactura));
            return [
                (float) $comision->monto_rol * $proporcion,
                ['tipo' => 'descuento', 'proporcion' => $proporcion],
            ];
        }

        $productosComision = DB::table('producto_comision')
            ->where('facturas_comision_id', $comision->id)
            ->where('estado_id', 1)
            ->get();
        $total = 0.0;
        $detalle = [];

        foreach ($lineasNota as $linea) {
            $precioCargaId = $linea->precios_producto_carga_id
                ?: $this->resolverPrecioCarga($nota->factura_id, $linea);
            $candidatas = $productosComision->where('producto_id', $linea->producto_id);
            if ($precioCargaId) {
                $porPrecio = $candidatas->where('precios_producto_carga_id', $precioCargaId);
                if ($porPrecio->isNotEmpty()) {
                    $candidatas = $porPrecio;
                }
            }

            $cantidadOriginal = (float) $candidatas->sum('cantidad');
            $comisionOriginal = (float) $candidatas->sum('monto_comision');
            if ($cantidadOriginal <= 0 || $comisionOriginal <= 0) {
                continue;
            }

            $montoLinea = ($comisionOriginal / $cantidadOriginal) * (float) $linea->cantidad;
            $total += $montoLinea;
            $detalle[] = [
                'producto_id' => (int) $linea->producto_id,
                'cantidad' => (float) $linea->cantidad,
                'precios_producto_carga_id' => $precioCargaId,
                'monto' => round($montoLinea, 4),
            ];
        }

        return [$total, ['tipo' => 'productos', 'lineas' => $detalle]];
    }

    private function resolverPrecioCarga(int $facturaId, object $linea): ?int
    {
        $id = DB::table('venta_has_producto')
            ->where('factura_id', $facturaId)
            ->where('producto_id', $linea->producto_id)
            ->where('seccion_id', $linea->seccion_id)
            ->where('unidad_medida_venta_id', $linea->unidad_medida_venta_id)
            ->orderByRaw('ABS(precio_unidad - ?) ASC', [(float) $linea->precio_unidad])
            ->value('precios_producto_carga_id');

        return $id ? (int) $id : null;
    }

    private function resolverEmpleado(object $factura, int $tipoComision): ?int
    {
        $id = match ($tipoComision) {
            1, 2 => $factura->users_id,
            3 => $factura->vendedor,
            4 => $factura->gestor_entrega,
            default => null,
        };

        return $id ? (int) $id : null;
    }

    private function siguientePeriodoAbierto(Carbon $periodo): Carbon
    {
        for ($intento = 0; $intento < 24; $intento++) {
            $conciliado = DB::table('comision_periodo')
                ->where('periodo', $periodo->toDateString())
                ->where('estado', 1)
                ->exists();
            if (!$conciliado) {
                return $periodo;
            }
            $periodo->addMonth();
        }

        throw new \RuntimeException('No se encontró un período abierto para rebajar la comisión.');
    }

    private function rebajarAcumulado(int $userId, int $rolId, Carbon $periodo, float $monto): void
    {
        $registro = DB::table('comision_empleado')
            ->where('users_comision', $userId)
            ->where('rol_id', $rolId)
            ->where('mes_comision', $periodo->toDateString())
            ->where('estado_id', 1)
            ->lockForUpdate()
            ->first();

        if ($registro) {
            DB::table('comision_empleado')->where('id', $registro->id)->update([
                'comision_acumulada' => round((float) $registro->comision_acumulada - $monto, 2),
                'fecha_ult_modificacion' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('comision_empleado')->insert([
            'comision_acumulada' => -$monto,
            'fecha_ult_modificacion' => now(),
            'mes_comision' => $periodo->toDateString(),
            'nombre_empleado' => DB::table('users')->where('id', $userId)->value('name') ?? 'Desconocido',
            'users_comision' => $userId,
            'rol_id' => $rolId,
            'estado_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}