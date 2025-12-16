<?php

namespace App\Services\Comisiones;

use Carbon\Carbon;
use App\Models\Comisiones\Escalado\modelcomision_empleado;
use App\Models\User;

class ProcesadorComisiones
{
    protected array $handlers = [

        /* Asignación de casos especiales de comisi+on */

        3 => 'TeleVendedor',
        2 => 'AsesorComercial',
    ];

    public function procesar(array $factura, array $contexto): void
    {
        $handler = $this->handlers[$factura['rol_id']] ?? 'Global';
        $method = 'procesar' . ucfirst($handler);


        if (!method_exists($this, $method)) {
            return; // o throw si preferís
        }

        $this->{$method}($factura, $contexto);
    }

    /* ================= HANDLERS ================= */

    protected function procesarTeleVendedor(array $factura, array $contexto): void
    {
        $fecha = Carbon::parse($factura['fecha_cierre_factura'])->startOfMonth();

        $comision = modelcomision_empleado::firstOrCreate(
            [
                'users_comision' => $contexto['televendedor_id'],
                'estado_id' => 1,
                'mes_comision' => $fecha,
            ],
            ['comision_acumulada' => 0]
        );

        $comision->increment(
            'comision_acumulada',
            (float) $factura['monto_rol'],
            ['fecha_ult_modificacion' => now()]
        );
    }

    protected function procesarAsesorComercial(array $factura, array $contexto): void
    {
        $mesComision = Carbon::parse($factura['fecha_cierre_factura'])
        ->startOfMonth()
        ->toDateString(); // YYYY-MM-01


        $comision = modelcomision_empleado::firstOrCreate(
            [
                'users_comision' => $contexto['vendedor_id'],
                'estado_id' => 1,
                'mes_comision' => $mesComision,
            ],
            ['comision_acumulada' => 0]
        );

        $comision->increment(
            'comision_acumulada',
            (float) $factura['monto_rol'],
            ['fecha_ult_modificacion' => now()]
        );
    }

    protected function procesarGlobal(array $factura, array $contexto): void
    {
        $mesComision = Carbon::parse($factura['fecha_cierre_factura'])
            ->startOfMonth()
            ->toDateString();

        /* Traigo todos los usuarios de los roles que previamente están configurados en el array de la factura */
        $usuarios = User::where('rol_id', $factura['rol_id'])
            ->pluck('id');

        if ($usuarios->isEmpty()) {
            return;
        }

        /* Si no los encuentra en la tabla, donde comisionan, los crea */
        foreach ($usuarios as $userId) {
            modelcomision_empleado::firstOrCreate(
                [
                    'users_comision' => $userId,
                    'estado_id'      => 1,
                    'mes_comision'   => $mesComision,
                ],
                ['comision_acumulada' => 0]
            );
        }

        /* Actualización masiva de la comision para el mes donde se cierra la factura */
        modelcomision_empleado::whereIn('users_comision', $usuarios)
            ->where('estado_id', 1)
            ->where('mes_comision', $mesComision)
            ->increment(
                'comision_acumulada',
                (float) $factura['monto_rol'],
                ['fecha_ult_modificacion' => now()]
            );
    }


}
