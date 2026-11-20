<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::affectingStatement("
            UPDATE auditoria_compras_ventas a
            INNER JOIN factura f ON f.id = a.factura_id
            SET a.clasificacion_factura = CASE f.tipo_venta_id
                WHEN 1 THEN 'B'
                WHEN 2 THEN 'A'
                ELSE NULL
            END
            WHERE a.tipo_movimiento = 'VENTA'
        ");
    }

    public function down(): void
    {
        DB::affectingStatement("
            UPDATE auditoria_compras_ventas a
            INNER JOIN factura f ON f.id = a.factura_id
            SET a.clasificacion_factura = CASE f.estado_factura_id
                WHEN 1 THEN 'A'
                WHEN 2 THEN 'B'
                ELSE NULL
            END
            WHERE a.tipo_movimiento = 'VENTA'
        ");
    }
};
