<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_has_producto', function (Blueprint $table) {
            $table->decimal('cantidad_oferta_aplicada', 18, 4)
                ->nullable()
                ->after('cotizacion_has_producto_id');
        });
    }

    public function down(): void
    {
        Schema::table('venta_has_producto', function (Blueprint $table) {
            $table->dropColumn('cantidad_oferta_aplicada');
        });
    }
};