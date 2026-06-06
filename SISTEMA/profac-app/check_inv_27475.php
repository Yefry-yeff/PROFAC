<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo PHP_EOL;
echo '=== PRODUCTOS EN FACTURA #27475 (venta_has_producto) ===' . PHP_EOL;
$vhp = DB::select('
    SELECT vhp.lote, vhp.numero_unidades_resta_inventario, vhp.cantidad,
           vhp.unidad_medida_venta_id, p.nombre AS producto, vhp.seccion_id
    FROM venta_has_producto vhp
    INNER JOIN producto p ON p.id = vhp.producto_id
    WHERE vhp.factura_id = 27475
');

foreach ($vhp as $r) {
    echo sprintf(
        "Lote ID: %d | Producto: %s | Seccion ID: %s | Cant.vendida: %s | Unid.base restadas: %s\n",
        $r->lote, $r->producto, $r->seccion_id, $r->cantidad, $r->numero_unidades_resta_inventario
    );
}

echo PHP_EOL . '=== INVENTARIO ACTUAL EN recibido_bodega ===' . PHP_EOL;
$loteIds = implode(',', array_column($vhp, 'lote'));
if ($loteIds) {
    $inv = DB::select("
        SELECT rb.id, rb.cantidad_disponible, p.nombre AS producto
        FROM recibido_bodega rb
        INNER JOIN producto p ON p.id = rb.producto_id
        WHERE rb.id IN ({$loteIds})
    ");
    foreach ($inv as $r) {
        echo sprintf("Lote ID: %d | Producto: %s | Disponible ACTUAL: %s\n",
            $r->id, $r->producto, $r->cantidad_disponible);
    }
}

echo PHP_EOL . '=== CREDITO ACTUAL DEL CLIENTE ===' . PHP_EOL;
$cli = DB::selectOne('
    SELECT c.nombre, c.credito, f.total AS total_factura
    FROM cliente c
    INNER JOIN factura f ON f.cliente_id = c.id
    WHERE f.id = 27475
');
echo "Cliente: {$cli->nombre} | Credito disponible: L " . number_format($cli->credito, 2) . " | Total factura: L " . number_format($cli->total_factura, 2) . PHP_EOL;
echo PHP_EOL . 'Credito esperado DESPUÉS de anular: L ' . number_format($cli->credito + $cli->total_factura, 2) . PHP_EOL;
echo PHP_EOL;
