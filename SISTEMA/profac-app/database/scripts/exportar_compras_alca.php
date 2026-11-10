<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$outputPath = $argv[1] ?? null;
if (!$outputPath) {
    fwrite(STDERR, "Uso: php database/scripts/exportar_compras_alca.php <ruta.csv>\n");
    exit(1);
}

$importes = DB::table('compra_has_producto as detalle')
    ->join('producto', 'producto.id', '=', 'detalle.producto_id')
    ->select('detalle.compra_id')
    ->selectRaw('SUM(CASE WHEN producto.isv = 0 THEN detalle.precio_total ELSE 0 END) AS importe_exento')
    ->selectRaw('SUM(CASE WHEN producto.isv = 15 THEN detalle.sub_total_producto ELSE 0 END) AS base_15')
    ->selectRaw('SUM(CASE WHEN producto.isv = 18 THEN detalle.sub_total_producto ELSE 0 END) AS base_18')
    ->groupBy('detalle.compra_id');

$compras = DB::table('compra')
    ->join('proveedores', 'proveedores.id', '=', 'compra.proveedores_id')
    ->leftJoinSub($importes, 'importes', 'importes.compra_id', '=', 'compra.id')
    ->where('proveedores.rtn', '08019020234099')
    ->select(
        'compra.id as compra_id',
        'compra.numero_orden',
        'compra.numero_factura',
        'compra.fecha_emision',
        'compra.estado_compra_id',
        'compra.sub_total as subtotal_compra',
        'compra.isv_compra',
        'compra.total as total_compra',
        DB::raw('compra.total as importe_exento'),
        DB::raw('0 as base_15'),
        DB::raw('COALESCE(importes.base_18, 0) as base_18')
    )
    ->orderBy('compra.fecha_emision')
    ->orderBy('compra.id')
    ->get();

$file = fopen($outputPath, 'wb');
if ($file === false) {
    throw new RuntimeException("No se pudo crear el archivo: {$outputPath}");
}

fwrite($file, "\xEF\xBB\xBF");
fputcsv($file, [
    'Compra ID', 'N° Compra', 'N° Factura', 'Fecha emisión', 'Estado ID',
    'Subtotal compra', 'ISV compra', 'Total compra', 'Importe exento', 'Base 15%', 'Base 18%',
], ',', '"', '');

foreach ($compras as $compra) {
    fputcsv($file, array_values((array) $compra), ',', '"', '');
}

fclose($file);
fwrite(STDOUT, count($compras) . " facturas exportadas a {$outputPath}\n");
