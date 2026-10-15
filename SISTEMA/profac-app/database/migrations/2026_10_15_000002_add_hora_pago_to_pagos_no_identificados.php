<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_no_identificados', function (Blueprint $table) {
            $table->time('hora_pago')->nullable()->after('fecha_pago');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_no_identificados', function (Blueprint $table) {
            $table->dropColumn('hora_pago');
        });
    }
};
