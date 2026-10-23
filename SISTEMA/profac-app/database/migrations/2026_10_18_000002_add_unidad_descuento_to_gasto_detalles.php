<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gasto_detalles', function (Blueprint $table) {
            $table->unsignedInteger('unidad_medida_id')->nullable();
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('descuento_monto', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('gasto_detalles', function (Blueprint $table) {
            $table->dropColumn(['unidad_medida_id', 'descuento_porcentaje', 'descuento_monto']);
        });
    }
};
