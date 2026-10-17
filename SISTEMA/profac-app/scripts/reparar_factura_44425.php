<?php

use App\Services\Comisiones\AplicadorRetencionesMora;
use App\Services\Comisiones\GeneradorFacturasComision;
use App\Services\Comisiones\ProcesadorComisiones;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

define('LARAVEL_START', microtime(true));

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$cai = '000-001-01-00044425';
$fechaCierre = '2026-09-24 15:43:26';
$montoMovimiento = 0.04;
$aplicar = in_array('--apply', $argv, true);

$fallar = static function (string $mensaje): void {
    throw new RuntimeException($mensaje);
};

try {
    $factura = DB::table('factura')->where('cai', $cai)->first();
    if (!$factura) {
        $fallar("No existe la factura {$cai}.");
    }

    $aplicaciones = DB::table('aplicacion_pagos')
        ->where('factura_id', $factura->id)
        ->where('estado', 1)
        ->get();

    if ($aplicaciones->count() !== 1) {
        $fallar('Se esperaba exactamente una aplicacion de pago activa; encontradas: ' . $aplicaciones->count());
    }

    $aplicacion = $aplicaciones->first();
    $movimientos = DB::table('otros_movimientos')
        ->where('factura_id', $factura->id)
        ->where('aplicacion_pagos_id', $aplicacion->id)
        ->where('tipo_movimiento', 2)
        ->where('estado', 1)
        ->where('monto', $montoMovimiento)
        ->get();

    $comisionesActivas = DB::table('facturas_comision')
        ->where('factura_id', $factura->id)
        ->where('estado_id', 1)
        ->count();

    echo "Factura: {$cai} (ID {$factura->id})" . PHP_EOL;
    echo "Aplicacion: {$aplicacion->id}" . PHP_EOL;
    echo 'Saldo: L ' . number_format((float) $aplicacion->saldo, 2, '.', ',') . PHP_EOL;
    echo "Estado cerrado: {$aplicacion->estado_cerrado}" . PHP_EOL;
    echo "Movimientos activos de L 0.04: {$movimientos->count()}" . PHP_EOL;
    echo "Comisiones activas existentes: {$comisionesActivas}" . PHP_EOL;

    if ((int) $aplicacion->estado_cerrado === 2 && $comisionesActivas > 0) {
        echo 'La factura ya fue reparada. No se aplico ningun cambio.' . PHP_EOL;
        exit(0);
    }

    if (!$aplicar) {
        echo PHP_EOL;
        echo 'Modo consulta: no se aplico ningun cambio.' . PHP_EOL;
        echo 'Si todos los datos coinciden, ejecute nuevamente con --apply.' . PHP_EOL;
        exit(0);
    }

    $resultado = DB::transaction(function () use (
        $cai,
        $fechaCierre,
        $montoMovimiento,
        $fallar
    ): array {
        $factura = DB::table('factura')
            ->where('cai', $cai)
            ->lockForUpdate()
            ->first();

        if (!$factura) {
            $fallar("No existe la factura {$cai}.");
        }

        $aplicaciones = DB::table('aplicacion_pagos')
            ->where('factura_id', $factura->id)
            ->where('estado', 1)
            ->lockForUpdate()
            ->get();

        if ($aplicaciones->count() !== 1) {
            $fallar('Se esperaba exactamente una aplicacion de pago activa; encontradas: ' . $aplicaciones->count());
        }

        $aplicacion = $aplicaciones->first();
        $movimientos = DB::table('otros_movimientos')
            ->where('factura_id', $factura->id)
            ->where('aplicacion_pagos_id', $aplicacion->id)
            ->where('tipo_movimiento', 2)
            ->where('estado', 1)
            ->where('monto', $montoMovimiento)
            ->lockForUpdate()
            ->get();

        if ($movimientos->count() !== 1) {
            $fallar('Se esperaba exactamente un movimiento activo de deduccion por L 0.04; encontrados: ' . $movimientos->count());
        }

        $movimiento = $movimientos->first();
        $comisionesActivas = DB::table('facturas_comision')
            ->where('factura_id', $factura->id)
            ->where('estado_id', 1)
            ->lockForUpdate()
            ->count();

        if ((int) $aplicacion->estado_cerrado === 2) {
            if ($comisionesActivas > 0) {
                return [
                    'ya_reparada' => true,
                    'factura_id' => (int) $factura->id,
                    'aplicacion_id' => (int) $aplicacion->id,
                    'comisiones' => $comisionesActivas,
                ];
            }

            $fallar('La aplicacion esta cerrada, pero no tiene comisiones activas. Se requiere auditoria manual.');
        }

        if ($comisionesActivas > 0) {
            $fallar('La factura esta abierta, pero ya tiene comisiones activas. Se requiere auditoria manual.');
        }

        if (abs((float) $aplicacion->saldo) > 0.0001) {
            $fallar('El saldo no es cero. Valor encontrado: ' . $aplicacion->saldo);
        }

        if (abs((float) $aplicacion->movimiento_resta - $montoMovimiento) > 0.0001) {
            $fallar('El acumulado de deducciones no es L 0.04. Valor encontrado: ' . $aplicacion->movimiento_resta);
        }

        $usuarioId = (int) $movimiento->usr_registro;
        if ($usuarioId <= 0 || !DB::table('users')->where('id', $usuarioId)->exists()) {
            $fallar('El usuario que registro el movimiento no es valido.');
        }

        Auth::loginUsingId($usuarioId);

        DB::table('aplicacion_pagos')
            ->where('id', $aplicacion->id)
            ->update([
                'saldo' => 0,
                'estado_cerrado' => 2,
                'usr_cerro' => $usuarioId,
                'fecha_cierre_factura' => $fechaCierre,
                'comentario' => 'CIERRE POR OTRO MOVIMIENTO: ' . trim((string) $movimiento->comentario),
                'ultimo_usr_actualizo' => $usuarioId,
                'updated_at' => now(),
            ]);

        $fechaComision = substr($fechaCierre, 0, 10);
        $comisiones = app(GeneradorFacturasComision::class)->generar(
            (int) $factura->id,
            (int) $aplicacion->id,
            $fechaComision
        );

        if (empty($comisiones)) {
            $fallar('El generador no produjo comisiones. Toda la transaccion fue revertida.');
        }

        $comisiones = app(AplicadorRetencionesMora::class)->aplicar(
            $comisiones,
            (int) $factura->id,
            $fechaComision
        );

        $procesador = app(ProcesadorComisiones::class);
        foreach ($comisiones as $comision) {
            $procesador->procesar($comision);
        }

        return [
            'ya_reparada' => false,
            'factura_id' => (int) $factura->id,
            'aplicacion_id' => (int) $aplicacion->id,
            'comisiones' => count($comisiones),
        ];
    }, 3);

    if ($resultado['ya_reparada']) {
        echo 'La factura ya habia sido reparada. No se duplico ningun registro.' . PHP_EOL;
        exit(0);
    }

    echo PHP_EOL;
    echo 'Reparacion completada correctamente.' . PHP_EOL;
    echo "Factura ID: {$resultado['factura_id']}" . PHP_EOL;
    echo "Aplicacion ID: {$resultado['aplicacion_id']}" . PHP_EOL;
    echo "Comisiones generadas: {$resultado['comisiones']}" . PHP_EOL;
    echo "Fecha de cierre aplicada: {$fechaCierre}" . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
