<?php
$pdo = new PDO('mysql:host=localhost;dbname=profac_app', 'root', '');

// All columns of venta_has_producto
$cols = $pdo->query("SHOW COLUMNS FROM venta_has_producto")->fetchAll(PDO::FETCH_ASSOC);
echo "=== TODAS las columnas de venta_has_producto ===\n";
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

// For invoice 27620 (42536), show ALL columns of one row
$stmt = $pdo->query("SELECT * FROM venta_has_producto WHERE factura_id = 27620 LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\n=== Fila completa de factura exonerada 27620 ===\n";
foreach ($row as $k => $v) echo "  $k: $v\n";
