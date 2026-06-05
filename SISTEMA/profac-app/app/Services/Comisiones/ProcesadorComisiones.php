<?php

namespace App\Services\Comisiones;

use Carbon\Carbon;
use App\Models\Comisiones\Escalado\modelcomision_empleado;
use App\Models\Comisiones\ModelComisionPeriodo;
use Illuminate\Support\Facades\DB;

class ProcesadorComisiones
{
    /**
     * Acredita la comisión de una entrada de facturas_comision al usuario concreto
     * indicado por 'target_user_id' (campo efímero inyectado por GeneradorFacturasComision).
     *
     * BLOQUEO DE PERÍODO: Si el mes al que correspondería la comisión ya fue
     * conciliado en comision_periodo (estado=1), la comisión NO se acredita
     * y el método retorna silenciosamente. La factura_comision ya fue insertada
     * por GeneradorFacturasComision; solo el acumulado del empleado se omite.
     */
    public function procesar(array $factura): void
    {
        $userId = $factura['target_user_id'] ?? null;
        $rolId  = $factura['rol_id']         ?? null;
        $monto  = (float) ($factura['monto_rol'] ?? 0);
        $fecha  = $factura['fecha_cierre_factura'] ?? now();

        if (!$userId || !$rolId || $monto <= 0) {
            return;
        }

        $mes = Carbon::parse($fecha)->startOfMonth()->toDateString();

        // ── Verificar si el período está conciliado ───────────────────────
        $periodoConciliado = DB::table('comision_periodo')
            ->where('periodo', $mes)
            ->where('estado', ModelComisionPeriodo::ESTADO_CONCILIADO)
            ->exists();

        if ($periodoConciliado) {
            // Período cerrado — no se acredita la comisión al empleado.
            // El registro en facturas_comision queda como evidencia pero
            // comision_empleado no se toca.
            return;
        }

        // ── Acreditar ─────────────────────────────────────────────────────
        // Nombre del empleado (desnormalizado para reportes rápidos)
        $nombre = DB::table('users')->where('id', $userId)->value('name') ?? 'Desconocido';

        // Una sola fila por (usuario, rol, mes) — acumula todas las capacidades
        $comision = modelcomision_empleado::firstOrCreate(
            [
                'users_comision' => $userId,
                'rol_id'         => $rolId,
                'mes_comision'   => $mes,
                'estado_id'      => 1,
            ],
            [
                'comision_acumulada'     => 0,
                'nombre_empleado'        => $nombre,
                'fecha_ult_modificacion' => now(),
            ]
        );

        $comision->increment('comision_acumulada', $monto, ['fecha_ult_modificacion' => now()]);
    }
}
