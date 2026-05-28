<?php
/**
 * REPROCESAMIENTO DE ANULACIONES
 * ------------------------------------------------
 * Restaura inventario y crédito para facturas que fueron anuladas
 * desde el flujo antes del fix implementado el 27/05/2026.
 *
 * Facturas afectadas: 2026-27177 | 2026-27219 | 2026-27323
 *
 * MODO SEGURO (DRY_RUN = true):
 *   Solo muestra lo que haría, SIN modificar la base de datos.
 *   Cambiar a false para ejecutar en producción.
 */

define('DRY_RUN', true); // ← cambiar a false para ejecutar en producción

// ─── Bootstrap Laravel ────────────────────────────────────────────────────────
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// ─── Facturas a reprocesar ────────────────────────────────────────────────────
$FACTURAS_IDS = [27325, 27367, 27471]; // ids internos de 2026-27177, 2026-27219, 2026-27323

// ─────────────────────────────────────────────────────────────────────────────

echo PHP_EOL;
echo '╔══════════════════════════════════════════════════════════════╗' . PHP_EOL;
echo '║     REPROCESAMIENTO DE ANULACIONES DE FACTURAS               ║' . PHP_EOL;
echo '║     Modo: ' . (DRY_RUN ? 'DRY RUN (sin cambios en BD)           ' : 'PRODUCCIÓN *** MODIFICANDO BD ***     ') . '║' . PHP_EOL;
echo '╚══════════════════════════════════════════════════════════════╝' . PHP_EOL;

foreach ($FACTURAS_IDS as $facturaId) {

    $factura = DB::selectOne(
        'SELECT id, numero_factura, cliente_id, total, estado_venta_id, users_id FROM factura WHERE id = ?',
        [$facturaId]
    );

    echo PHP_EOL . "══════════════════════════════════════════════════════════" . PHP_EOL;
    echo "  Factura #{$factura->numero_factura} (id={$facturaId}) | Total: L " . number_format($factura->total, 2) . PHP_EOL;
    echo "══════════════════════════════════════════════════════════" . PHP_EOL;

    // ── Guardia 1: la factura debe estar anulada ──────────────────────────────
    if ($factura->estado_venta_id != 2) {
        echo "  [SKIP] La factura NO está anulada (estado_venta_id={$factura->estado_venta_id}). Se omite." . PHP_EOL;
        continue;
    }

    // ── Guardia 2: idempotencia — no reprocesar si ya tiene log de anulación ──
    $yaProcessado = DB::selectOne(
        "SELECT COUNT(id) AS cnt FROM log_translado WHERE factura_id = ? AND descripcion = 'Factura Anulada'",
        [$facturaId]
    );
    if ($yaProcessado->cnt > 0) {
        echo "  [SKIP] Ya tiene {$yaProcessado->cnt} registro(s) en log_translado con 'Factura Anulada'. Se omite para evitar duplicados." . PHP_EOL;
        continue;
    }

    // ── Obtener lotes de venta_has_producto ───────────────────────────────────
    $lotes = DB::select(
        'SELECT vhp.lote, vhp.numero_unidades_resta_inventario, vhp.unidad_medida_venta_id, p.nombre AS producto
         FROM venta_has_producto vhp
         INNER JOIN producto p ON p.id = vhp.producto_id
         WHERE vhp.factura_id = ?',
        [$facturaId]
    );

    if (empty($lotes)) {
        echo "  [SKIP] No se encontraron lotes en venta_has_producto. Se omite." . PHP_EOL;
        continue;
    }

    // ── Obtener cliente ───────────────────────────────────────────────────────
    $cliente = DB::selectOne(
        'SELECT id, nombre, credito FROM cliente WHERE id = ?',
        [$factura->cliente_id]
    );

    // ── Mostrar plan de acción ────────────────────────────────────────────────
    echo "  PLAN:" . PHP_EOL;
    foreach ($lotes as $l) {
        $rb = DB::selectOne('SELECT cantidad_disponible FROM recibido_bodega WHERE id = ?', [$l->lote]);
        $despues = $rb->cantidad_disponible + $l->numero_unidades_resta_inventario;
        echo "  [INV] Lote {$l->lote} | {$l->producto}" . PHP_EOL;
        echo "        recibido_bodega.cantidad_disponible: {$rb->cantidad_disponible} → {$despues} (+{$l->numero_unidades_resta_inventario})" . PHP_EOL;
    }
    $creditoDespues = $cliente->credito + $factura->total;
    echo "  [CLI] cliente.credito: L " . number_format($cliente->credito, 2) . " → L " . number_format($creditoDespues, 2) . " (+L " . number_format($factura->total, 2) . ")" . PHP_EOL;
    echo "  [LOG] Insertar " . count($lotes) . " registro(s) en log_translado con descripcion='Factura Anulada'" . PHP_EOL;

    if (DRY_RUN) {
        echo PHP_EOL . "  [DRY RUN] No se realizaron cambios." . PHP_EOL;
        continue;
    }

    // ── Ejecutar dentro de transacción ────────────────────────────────────────
    DB::beginTransaction();
    try {
        $logRows = [];

        // 1) Devolver unidades a recibido_bodega
        foreach ($lotes as $l) {
            DB::table('recibido_bodega')
                ->where('id', $l->lote)
                ->increment('cantidad_disponible', $l->numero_unidades_resta_inventario);

            $logRows[] = [
                'origen'                 => $l->lote,
                'destino'                => $l->lote,
                'factura_id'             => $facturaId,
                'cantidad'               => $l->numero_unidades_resta_inventario,
                'unidad_medida_venta_id' => $l->unidad_medida_venta_id,
                'users_id'               => $factura->users_id ?? 2, // usuario que creó la factura
                'descripcion'            => 'Factura Anulada',
                'created_at'             => now(),
                'updated_at'             => now(),
            ];
        }

        // 2) Registrar en log_translado
        DB::table('log_translado')->insert($logRows);

        // 3) Restaurar crédito del cliente
        DB::table('cliente')
            ->where('id', $factura->cliente_id)
            ->increment('credito', $factura->total);

        // 4) Inactivar aplicacion_pagos (si los hubiera)
        DB::table('aplicacion_pagos')
            ->where('factura_id', $facturaId)
            ->update(['estado' => 2]);

        DB::commit();
        echo PHP_EOL . "  [OK] Reprocesamiento completado con éxito." . PHP_EOL;

    } catch (\Exception $e) {
        DB::rollBack();
        echo PHP_EOL . "  [ERROR] Se hizo rollback. Mensaje: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "══════════════════════════════════════════════════════════" . PHP_EOL;
echo "  Proceso finalizado." . (DRY_RUN ? " (DRY RUN — ningún cambio aplicado)" : "") . PHP_EOL;
