<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdenCompraFormaF01ToCotizacion extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->string('numero_orden_compra', 100)->nullable()->after('tipo_pago_id');
            $table->string('archivo_orden_compra', 500)->nullable()->after('numero_orden_compra');
            $table->string('numero_forma_f01', 100)->nullable()->after('archivo_orden_compra');
            $table->string('archivo_forma_f01', 500)->nullable()->after('numero_forma_f01');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->dropColumn(['numero_orden_compra', 'archivo_orden_compra', 'numero_forma_f01', 'archivo_forma_f01']);
        });
    }
}
