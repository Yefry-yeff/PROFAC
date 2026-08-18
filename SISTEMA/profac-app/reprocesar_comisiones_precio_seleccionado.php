<?php

/**
 * Reprocesa comisiones activas que se calcularon con precio_unidad
 * cuando la linea de venta tenia precioSeleccionado distinto.
 *
 * Uso:
 *   php reprocesar_comisiones_precio_seleccionado.php --dry-run
 *   php reprocesar_comisiones_precio_seleccionado.php --run
 */

use App\Services\Comisiones\AplicadorRetencionesMora;
use App\Services\Comisiones\GeneradorFacturasComision;
use App\Services\Comisiones\ProcesadorComisiones;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$dryRun = true;
if (in_array('--run', $argv, true)) {
    $dryRun = false;
}
if (in_array('--dry-run', $argv, true)) {
    $dryRun = true;
}

$facturas = DB::select(
    "SELECT DISTINCT fc.factura_id, f.cai
     FROM facturas_comision fc
     JOIN producto_comision pc
       ON pc.facturas_comision_id = fc.id
      AND pc.estado_id = 1
     JOIN factura f
       ON f.id = fc.factura_id
     JOIN venta_has_producto vhp
       ON vhp.factura_id = pc.factura_id
      AND vhp.producto_id = pc.producto_id
      AND ((vhp.precios_producto_carga_id = pc.precios_producto_carga_id)
        OR (vhp.precios_producto_carga_id IS NULL AND pc.precios_producto_carga_id IS NULL))
     WHERE fc.estado_id = 1
       AND vhp.precioSeleccionado IS NOT NULL
       AND vhp.precioSeleccionado > 0
       AND ABS(vhp.precioSeleccionado - vhp.precio_unidad) > 0.0001
       AND ABS(pc.precio_venta - vhp.precio_unidad) < 0.01
     ORDER BY fc.factura_id DESC"
);

echo PHP_EOL;
echo '=== REPROCESO COMISIONES POR PRECIO SELECCIONADO ===' . PHP_EOL;
echo 'Modo: ' . ($dryRun ? 'DRY RUN' : 'RUN') . PHP_EOL;
echo 'Facturas candidatas: ' . count($facturas) . PHP_EOL;

if (empty($facturas)) {
    echo 'No hay facturas pendientes de reproceso.' . PHP_EOL;
    exit(0);
}

$generador = app(GeneradorFacturasComision::class);
$aplicador = app(AplicadorRetencionesMora::class);
$procesador = app(ProcesadorComisiones::class);

$totalOk = 0;
$totalError = 0;
$sumAntes = 0.0;
$sumDespues = 0.0;
$detalles = [];

foreach ($facturas as $f) {
    $facturaId = (int) $f->factura_id;
    $cai = (string) ($f->cai ?? ('ID-' . $facturaId));

    try {
        DB::beginTransaction();

        $activos = DB::select(
            "SELECT fc.id, fc.rol_id, fc.tipo_comision, fc.monto_rol, fc.fecha_cierre_factura,
                    CASE fc.tipo_comision
                        WHEN 1 THEN fa.users_id
                        WHEN 2 THEN fa.users_id
                        WHEN 3 THEN fa.vendedor
                        WHEN 4 THEN fa.gestor_entrega
                        ELSE NULL
                    END AS target_user_id
             FROM facturas_comision fc
             JOIN factura fa ON fa.id = fc.factura_id
             WHERE fc.factura_id = ? AND fc.estado_id = 1
             ORDER BY fc.id",
            [$facturaId]
        );

        if (empty($activos)) {
            DB::rollBack();
            continue;
        }

        $aplicacionPagoId = 0;
        $fechaPago = null;
        $fcIds = [];
        $montoAntes = 0.0;

        foreach ($activos as $row) {
            $fcIds[] = (int) $row->id;
            $montoAntes += (float) $row->monto_rol;
            if ($fechaPago === null && !empty($row->fecha_cierre_factura)) {
                $fechaPago = substr((string) $row->fecha_cierre_factura, 0, 10);
            }
        }

        $apRow = DB::selectOne(
            "SELECT aplicacion_pagos_id
             FROM facturas_comision
             WHERE factura_id = ? AND estado_id = 1 AND aplicacion_pagos_id IS NOT NULL
             ORDER BY id DESC LIMIT 1",
            [$facturaId]
        );
        if ($apRow && $apRow->aplicacion_pagos_id !== null) {
            $aplicacionPagoId = (int) $apRow->aplicacion_pagos_id;
        }

        // Revertir acumulado previamente acreditado en comision_empleado.
        foreach ($activos as $row) {
            $targetUserId = isset($row->target_user_id) ? (int) $row->target_user_id : 0;
            $rolId = (int) $row->rol_id;
            $montoRol = (float) $row->monto_rol;
            $mes = date('Y-m-01', strtotime((string) $row->fecha_cierre_factura));

            if ($targetUserId > 0 && $rolId > 0 && $montoRol > 0) {
                DB::update(
                    "UPDATE comision_empleado
                     SET comision_acumulada = GREATEST(0, comision_acumulada - ?),
                         fecha_ult_modificacion = NOW(),
                         updated_at = NOW()
                     WHERE users_comision = ?
                       AND rol_id = ?
                       AND estado_id = 1
                       AND mes_comision = ?",
                    [$montoRol, $targetUserId, $rolId, $mes]
                );
            }
        }

        // Inactivar comisiones previas y sus lineas.
        DB::table('facturas_comision')
            ->whereIn('id', $fcIds)
            ->update(['estado_id' => 2, 'updated_at' => now()]);

        DB::table('producto_comision')
            ->whereIn('facturas_comision_id', $fcIds)
            ->update(['estado_id' => 2, 'updated_at' => now()]);

        // Regenerar con logica nueva (precioSeleccionado como base).
        $nuevas = $generador->generar($facturaId, $aplicacionPagoId, $fechaPago);
        if (!empty($nuevas)) {
            $nuevas = $aplicador->aplicar($nuevas, $facturaId, $fechaPago);
            foreach ($nuevas as $fila) {
                $procesador->procesar($fila);
            }
        }

        $montoDespues = DB::table('facturas_comision')
            ->where('factura_id', $facturaId)
            ->where('estado_id', 1)
            ->sum('monto_rol');

        if ($dryRun) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        $totalOk++;
        $sumAntes += (float) $montoAntes;
        $sumDespues += (float) $montoDespues;

        $detalles[] = [
            'factura_id' => $facturaId,
            'cai' => $cai,
            'antes' => round($montoAntes, 2),
            'despues' => round((float) $montoDespues, 2),
        ];
    } catch (Throwable $e) {
        DB::rollBack();
        $totalError++;
        $detalles[] = [
            'factura_id' => $facturaId,
            'cai' => $cai,
            'error' => $e->getMessage(),
        ];
    }
}

echo PHP_EOL;
echo 'Procesadas OK: ' . $totalOk . PHP_EOL;
echo 'Con error: ' . $totalError . PHP_EOL;
echo 'Total monto antes: ' . number_format($sumAntes, 2) . PHP_EOL;
echo 'Total monto despues: ' . number_format($sumDespues, 2) . PHP_EOL;

echo PHP_EOL . 'Muestra de resultados:' . PHP_EOL;
$maxRows = min(20, count($detalles));
for ($i = 0; $i < $maxRows; $i++) {
    $d = $detalles[$i];
    if (isset($d['error'])) {
        echo '- ' . $d['factura_id'] . ' (' . $d['cai'] . ') ERROR: ' . $d['error'] . PHP_EOL;
        continue;
    }

    echo '- ' . $d['factura_id'] . ' (' . $d['cai'] . ') antes=' . number_format((float) $d['antes'], 2) . ' despues=' . number_format((float) $d['despues'], 2) . PHP_EOL;
}

if (count($detalles) > $maxRows) {
    echo '... (' . (count($detalles) - $maxRows) . ' filas mas)' . PHP_EOL;
}

exit($totalError > 0 ? 1 : 0);
