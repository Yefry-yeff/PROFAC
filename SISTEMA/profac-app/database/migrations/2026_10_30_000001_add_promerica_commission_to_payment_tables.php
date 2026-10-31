<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonos_creditos', function (Blueprint $table) {
            $table->decimal('monto_recibido', 12, 2)->nullable()->after('monto_abonado');
            $table->decimal('comision_cobrada', 12, 2)->nullable()->after('monto_recibido');
        });

        Schema::table('pagos_no_identificados', function (Blueprint $table) {
            $table->decimal('comision_cobrada', 12, 2)->default(0)->after('monto_original');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_no_identificados', function (Blueprint $table) {
            $table->dropColumn('comision_cobrada');
        });

        Schema::table('abonos_creditos', function (Blueprint $table) {
            $table->dropColumn(['monto_recibido', 'comision_cobrada']);
        });
    }
};
