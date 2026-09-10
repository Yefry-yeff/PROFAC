<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('expo_cotizacion', 'aumento_calculado')) {
            Schema::table('expo_cotizacion', function (Blueprint $table) {
                $table->decimal('aumento_calculado', 15, 2)->default(0)->after('porcentaje_descuento_final');
            });
        }

        if (!Schema::hasColumn('expo_cotizacion', 'aumento_aplicado')) {
            Schema::table('expo_cotizacion', function (Blueprint $table) {
                $table->decimal('aumento_aplicado', 15, 2)->default(0)->after('aumento_calculado');
            });
        }
    }

    public function down(): void
    {
        // Estos campos pertenecen a la migración original de facturación parcial.
    }
};