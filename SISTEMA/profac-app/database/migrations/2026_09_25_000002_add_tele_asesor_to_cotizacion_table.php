<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cotizacion', 'tele_asesor')) {
            Schema::table('cotizacion', function (Blueprint $table) {
                $table->unsignedBigInteger('tele_asesor')->nullable()->after('direccion_entrega');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cotizacion', 'tele_asesor')) {
            Schema::table('cotizacion', function (Blueprint $table) {
                $table->dropColumn('tele_asesor');
            });
        }
    }
};