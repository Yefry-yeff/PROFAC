<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('comision_politica_anterior_factura')) {
            return;
        }

        Schema::create('comision_politica_anterior_factura', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('factura_id');
            $table->date('periodo');
            $table->decimal('monto_comision', 16, 2)->default(0);
            $table->tinyInteger('estado')->default(0)->comment('0=abierto,1=conciliado');
            $table->unsignedBigInteger('comision_periodo_id')->nullable();
            $table->unsignedBigInteger('usuario_agrego_id')->nullable();
            $table->dateTime('fecha_agregado')->nullable();
            $table->unsignedBigInteger('usuario_concilio_id')->nullable();
            $table->dateTime('fecha_conciliacion')->nullable();
            $table->timestamps();

            $table->unique(['factura_id', 'periodo'], 'uk_cpa_factura_periodo');
            $table->index('periodo', 'idx_cpa_periodo');
            $table->index('estado', 'idx_cpa_estado');
            $table->index('comision_periodo_id', 'idx_cpa_comision_periodo');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('comision_politica_anterior_factura')) {
            Schema::dropIfExists('comision_politica_anterior_factura');
        }
    }
};
