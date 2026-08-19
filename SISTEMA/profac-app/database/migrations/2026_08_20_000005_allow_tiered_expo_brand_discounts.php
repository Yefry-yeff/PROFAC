<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expo_descuento_marca', function (Blueprint $table) {
            $table->index('expo_id', 'expo_descuento_marca_expo_id_index');
            $table->dropUnique(['expo_id', 'marca_id']);
            $table->unique(
                ['expo_id', 'marca_id', 'venta_minima'],
                'expo_descuento_marca_escalon_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('expo_descuento_marca', function (Blueprint $table) {
            $table->dropUnique('expo_descuento_marca_escalon_unique');
            $table->unique(['expo_id', 'marca_id']);
            $table->dropIndex('expo_descuento_marca_expo_id_index');
        });
    }
};