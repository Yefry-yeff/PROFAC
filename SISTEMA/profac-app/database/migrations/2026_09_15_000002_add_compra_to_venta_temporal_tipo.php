<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE venta_temporal MODIFY tipo ENUM('oferta', 'factura', 'compra') NOT NULL");
    }

    public function down(): void
    {
        DB::table('venta_temporal')->where('tipo', 'compra')->delete();
        DB::statement("ALTER TABLE venta_temporal MODIFY tipo ENUM('oferta', 'factura') NOT NULL");
    }
};