<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cotizacion', 'zone_group_id')) {
            Schema::table('cotizacion', function (Blueprint $table) {
                $table->unsignedBigInteger('zone_group_id')->nullable()->after('vendedor');
            });
        }

        if (!Schema::hasColumn('cotizacion', 'direccion_entrega')) {
            Schema::table('cotizacion', function (Blueprint $table) {
                $table->string('direccion_entrega', 255)->nullable()->after('zone_group_id');
            });
        }
    }

    public function down(): void
    {
        $columnas = [];
        if (Schema::hasColumn('cotizacion', 'direccion_entrega')) {
            $columnas[] = 'direccion_entrega';
        }
        if (Schema::hasColumn('cotizacion', 'zone_group_id')) {
            $columnas[] = 'zone_group_id';
        }

        if ($columnas) {
            Schema::table('cotizacion', function (Blueprint $table) use ($columnas) {
                $table->dropColumn($columnas);
            });
        }
    }
};