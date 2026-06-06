<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoPagoIdToCotizacion extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->unsignedInteger('tipo_pago_id')->nullable()->after('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->dropColumn('tipo_pago_id');
        });
    }
}
