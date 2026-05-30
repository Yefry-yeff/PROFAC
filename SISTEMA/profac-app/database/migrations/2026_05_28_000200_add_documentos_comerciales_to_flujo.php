<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flujo', function (Blueprint $table) {
            if (!Schema::hasColumn('flujo', 'numero_orden_compra')) {
                $table->string('numero_orden_compra', 100)->nullable()->after('cliente_rtn');
            }
            if (!Schema::hasColumn('flujo', 'archivo_orden_compra')) {
                $table->string('archivo_orden_compra', 500)->nullable()->after('numero_orden_compra');
            }
            if (!Schema::hasColumn('flujo', 'numero_forma_f01')) {
                $table->string('numero_forma_f01', 100)->nullable()->after('archivo_orden_compra');
            }
            if (!Schema::hasColumn('flujo', 'archivo_forma_f01')) {
                $table->string('archivo_forma_f01', 500)->nullable()->after('numero_forma_f01');
            }
            if (!Schema::hasColumn('flujo', 'numero_exoneracion')) {
                $table->string('numero_exoneracion', 100)->nullable()->after('archivo_forma_f01');
            }
            if (!Schema::hasColumn('flujo', 'archivo_exoneracion')) {
                $table->string('archivo_exoneracion', 500)->nullable()->after('numero_exoneracion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('flujo', function (Blueprint $table) {
            $drop = [];
            foreach ([
                'numero_orden_compra',
                'archivo_orden_compra',
                'numero_forma_f01',
                'archivo_forma_f01',
                'numero_exoneracion',
                'archivo_exoneracion',
            ] as $column) {
                if (Schema::hasColumn('flujo', $column)) {
                    $drop[] = $column;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
