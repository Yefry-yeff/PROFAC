<?php

namespace App\Services\Comisiones;

use Carbon\Carbon;
use App\Models\Comisiones\Escalado\modelcomision_empleado;
use Illuminate\Support\Facades\DB;

class ProcesadorComisiones
{
    /**
     * Acredita la comisión de una entrada de facturas_comision al usuario concreto
     * indicado por 'target_user_id' (campo efímero inyectado por GeneradorFacturasComision).
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

        // Nombre del empleado (desnormalizado para reportes rápidos)
        $nombre = DB::table('users')->where('id', $userId)->value('name') ?? 'Desconocido';

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
