<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::affectingStatement("
            DELETE a
            FROM auditoria_compras_ventas a
            INNER JOIN log_translado lt ON lt.destino = a.origen_id
            WHERE a.tipo_movimiento = 'COMPRA'
              AND lt.translado_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        // Las filas correctas se reconstruyen mediante la sincronización completa.
    }
};
