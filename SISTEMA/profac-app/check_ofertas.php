<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'DB: ' . DB::connection()->getDatabaseName() . PHP_EOL;

$rows = DB::select('SELECT estado, COUNT(*) as cnt FROM oferta GROUP BY estado');
foreach ($rows as $r) {
    echo $r->estado . ': ' . $r->cnt . PHP_EOL;
}

$sample = DB::select('SELECT id, pedido_id, nombre_cliente, estado FROM oferta ORDER BY id DESC LIMIT 5');
echo "\nLast 5 ofertas:\n";
foreach ($sample as $r) {
    echo "  id={$r->id} pedido={$r->pedido_id} cliente={$r->nombre_cliente} estado={$r->estado}\n";
}
