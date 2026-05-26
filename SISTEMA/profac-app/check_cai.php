<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = array_column(\Illuminate\Support\Facades\DB::select('DESCRIBE factura'), 'Field');
echo implode(', ', $cols) . PHP_EOL;
