<?php

namespace App\Services\Comisiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Aplica retenciones por mora sobre comisiones generadas.
 *
 * ─────────────────────────────────────────────────────────────────
 *  CONTADO  (tipo_pago_id = 1)
 * ─────────────────────────────────────────────────────────────────
 *  Referencia : fecha_emision
 *  Cálculo    : diasTranscurridos = fechaCierre − fechaEmision
 *  Condición  : diasTranscurridos > diasGracia
 *  Penalidad  : 100% de la comisión (monto_final = 0)
 *  Log        : 1 registro, periodo_numero = null, porcentaje_aplicado = 100
 *
 * ─────────────────────────────────────────────────────────────────
 *  CRÉDITO  (tipo_pago_id = 2)
 * ─────────────────────────────────────────────────────────────────
 *  Referencia : fecha_vencimiento
 *  Cálculo    : periodosVencidos = floor(diasTranscurridos / diasGracia)
 *  Fórmula    : montoPorPeriodo  = comisionOriginal × (porcentaje / 100)
 *               totalRetencion   = periodosVencidos × montoPorPeriodo
 *               comisionFinal    = max(0, comisionOriginal − totalRetencion)
 *  Log        : N registros independientes (uno por período) para auditoría completa
 *
 * ─────────────────────────────────────────────────────────────────
 *  IDEMPOTENCIA
 * ─────────────────────────────────────────────────────────────────
 *  Guardia por facturas_comision_id en retencion_mora_log.
 *  Si ya existe cualquier log para ese fc_id → se omite (no duplica).
 *
 * ─────────────────────────────────────────────────────────────────
 *  USO
 * ─────────────────────────────────────────────────────────────────
 *  $array = $generador->generar($facturaId, $apId, $fecha);
 *  $array = $retencion->aplicar($array, $facturaId, $fecha);
 *  foreach ($array as $fc) { $procesador->procesar($fc); }
 */
class AplicadorRetencionesMora
{
    public function aplicar(array $comisionesGeneradas, int $facturaId, ?string $fechaCierre = null): array
    {
        if (empty($comisionesGeneradas)) {
            return $comisionesGeneradas;
        }

        $fechaCierreCarbon = Carbon::parse($fechaCierre ?? now()->toDateString())->startOfDay();
        $fechaCierreStr    = $fechaCierreCarbon->toDateString();

        $factura = DB::table('factura')
            ->where('id', $facturaId)
            ->select('tipo_pago_id', 'fecha_emision', 'fecha_vencimiento', 'sub_total')
            ->first();

        if (!$factura) {
            return $comisionesGeneradas;
        }

        $esContado = (int) $factura->tipo_pago_id === 1;
        $tipoKey   = $esContado ? 'contado' : 'credito';
        $subTotal  = (float) $factura->sub_total;

        $fechaReferencia = $esContado
            ? Carbon::parse($factura->fecha_emision)->startOfDay()
            : Carbon::parse($factura->fecha_vencimiento)->startOfDay();

        // diffInDays con false → negativo si fechaCierre < fechaRef (pago anticipado)
        $diasTranscurridos = (int) $fechaReferencia->diffInDays($fechaCierreCarbon, false);

        if ($diasTranscurridos <= 0) {
            return $comisionesGeneradas; // Pago en tiempo o anticipado
        }

        $rolIds = array_unique(array_column($comisionesGeneradas, 'rol_id'));

        $configs = DB::table('dias_gracia_comision')
            ->whereIn('rol_id', $rolIds)
            ->where('tipo_factura', $tipoKey)
            ->where('dias_gracia', '>', 0)
            ->get()
            ->keyBy('rol_id');

        if ($configs->isEmpty()) {
            return $comisionesGeneradas;
        }

        $ejecutorId = Auth::id();

        foreach ($comisionesGeneradas as &$fc) {
            $rolId  = $fc['rol_id'];
            $fcId   = $fc['facturas_comision_id'] ?? null;
            $config = $configs->get($rolId);

            if (!$config || !$fcId) {
                continue;
            }

            // ── Guardia de idempotencia ──────────────────────────────────────
            if (DB::table('retencion_mora_log')->where('facturas_comision_id', $fcId)->exists()) {
                continue;
            }

            $diasGracia    = (int) $config->dias_gracia;
            $montoOriginal = (float) $fc['monto_rol'];
            $userId        = $fc['target_user_id'] ?? null;

            if ($diasTranscurridos <= $diasGracia) {
                continue; // Dentro del período de gracia
            }

            if ($esContado) {
                // ── CONTADO: pérdida total de la comisión ────────────────────
                $this->aplicarContado(
                    $fcId, $facturaId, $rolId, $userId,
                    $diasTranscurridos, $diasGracia,
                    $montoOriginal, $subTotal,
                    $fechaCierreStr, $ejecutorId
                );
                $fc['monto_rol'] = 0.0;

            } else {
                // ── CRÉDITO: acumulativo por períodos vencidos ───────────────
                $porcentaje = (float) $config->porcentaje_retencion;
                if ($porcentaje <= 0) {
                    continue;
                }

                $periodosVencidos = (int) floor($diasTranscurridos / $diasGracia);
                if ($periodosVencidos <= 0) {
                    continue;
                }

                $montoFinal = $this->aplicarCredito(
                    $fcId, $facturaId, $rolId, $userId,
                    $diasTranscurridos, $diasGracia,
                    $porcentaje, $periodosVencidos,
                    $montoOriginal, $subTotal,
                    $fechaCierreStr, $ejecutorId
                );
                $fc['monto_rol'] = $montoFinal;
            }
        }
        unset($fc);

        return $comisionesGeneradas;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CONTADO — 100% de la comisión
    // ─────────────────────────────────────────────────────────────────────────
    private function aplicarContado(
        int $fcId, int $facturaId, int $rolId, ?int $userId,
        int $diasTranscurridos, int $diasGracia,
        float $montoOriginal, float $subTotal,
        string $fechaCierre, ?int $ejecutorId
    ): void {
        DB::table('retencion_mora_log')->insert([
            'factura_id'               => $facturaId,
            'facturas_comision_id'     => $fcId,
            'rol_id'                   => $rolId,
            'user_id'                  => $userId,
            'tipo_factura'             => 'contado',
            'fecha_aplicacion'         => $fechaCierre,
            'dias_transcurridos'       => $diasTranscurridos,
            'dias_gracia_configurados' => $diasGracia,
            'porcentaje_aplicado'      => 100.00,
            'periodo_numero'           => null,
            'comision_original'        => round($montoOriginal, 4),
            'monto_retenido'           => round($montoOriginal, 4),
            'subtotal_factura'         => round($subTotal, 4),
            'usuario_ejecutor'         => $ejecutorId,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);

        DB::table('facturas_comision')->where('id', $fcId)->update([
            'monto_rol'            => 0,
            'retencion_mora_monto' => round($montoOriginal, 4),
            'retencion_mora_dias'  => $diasTranscurridos,
            'updated_at'           => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CRÉDITO — acumulativo por período completo de gracia vencido
    // ─────────────────────────────────────────────────────────────────────────
    private function aplicarCredito(
        int $fcId, int $facturaId, int $rolId, ?int $userId,
        int $diasTranscurridos, int $diasGracia,
        float $porcentaje, int $periodosVencidos,
        float $montoOriginal, float $subTotal,
        string $fechaCierre, ?int $ejecutorId
    ): float {
        // Nueva regla: la retención por período se calcula sobre la comisión original,
        // no sobre el subtotal de la factura.
        $montoPorPeriodo = round($montoOriginal * ($porcentaje / 100), 4);
        $totalRetencion  = round($montoPorPeriodo * $periodosVencidos, 4);
        $montoFinal      = max(0.0, round($montoOriginal - $totalRetencion, 4));

        // Un registro por período para trazabilidad completa
        $logRows = [];
        for ($p = 1; $p <= $periodosVencidos; $p++) {
            $logRows[] = [
                'factura_id'               => $facturaId,
                'facturas_comision_id'     => $fcId,
                'rol_id'                   => $rolId,
                'user_id'                  => $userId,
                'tipo_factura'             => 'credito',
                'fecha_aplicacion'         => $fechaCierre,
                'dias_transcurridos'       => $diasTranscurridos,
                'dias_gracia_configurados' => $diasGracia,
                'porcentaje_aplicado'      => $porcentaje,
                'periodo_numero'           => $p,
                'comision_original'        => round($montoOriginal, 4),
                'monto_retenido'           => $montoPorPeriodo,
                'subtotal_factura'         => round($subTotal, 4),
                'usuario_ejecutor'         => $ejecutorId,
                'created_at'               => now(),
                'updated_at'               => now(),
            ];
        }

        DB::table('retencion_mora_log')->insert($logRows);

        DB::table('facturas_comision')->where('id', $fcId)->update([
            'monto_rol'            => $montoFinal,
            'retencion_mora_monto' => $totalRetencion,
            'retencion_mora_dias'  => $diasTranscurridos,
            'updated_at'           => now(),
        ]);

        return $montoFinal;
    }
}
