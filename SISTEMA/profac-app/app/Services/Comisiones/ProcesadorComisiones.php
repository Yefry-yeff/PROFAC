<?php

namespace App\Services\Comisiones;

use Carbon\Carbon;
use App\Models\Comisiones\Escalado\modelcomision_empleado;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        //dd($factura);
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
                'rol_id'         => $factura['rol_id'], // ✅ OBLIGATORIO
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
        // Blindaje extra: si por error entra un rol especial, no procesa
        if (in_array($factura['rol_id'], [2, 3])) {
            return;
        }

        $mesComision = Carbon::parse($factura['fecha_cierre_factura'])
            ->startOfMonth()
            ->toDateString();

        // Obtener usuarios cuyo rol coincida con el de la factura
        $usuarios = DB::select(
            'SELECT id
            FROM users
            WHERE rol_id = ?',
            [$factura['rol_id']]
        );

        //dd($factura);
        // Si no hay usuarios para ese rol, no hay nada que hacer
        if (empty($usuarios)) {
            return;
        }

        // Extraer IDs
        $idsUsuarios = array_map(
            fn ($u) => $u->id,
            $usuarios
        );

        // Crear registros de comisión si no existen
/*         foreach ($idsUsuarios as $userId) {
            modelcomision_empleado::firstOrCreate(
                [
                    'users_comision' => $userId,
                    'estado_id'      => 1,
                    'mes_comision'   => $mesComision,
                ],
                [
                    'comision_acumulada' => 0,
                ]
            );
        } */

        // Sumar comisión SOLO a usuarios del rol de la factura
        modelcomision_empleado::whereIn('users_comision', $idsUsuarios)
            ->where('estado_id', 1)
            ->where('mes_comision', $mesComision)
            ->increment(
                'comision_acumulada',
                (float) $factura['monto_rol'],
                ['fecha_ult_modificacion' => now()]
            );
    }


}
