<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_retencion_seguimiento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factura_id')->unique();
            $table->unsignedBigInteger('aplicacion_pagos_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->string('observacion_marcado', 500)->nullable();
            $table->string('observacion_resolucion', 500)->nullable();
            $table->unsignedBigInteger('usr_marcado')->nullable();
            $table->unsignedBigInteger('usr_resolvio')->nullable();
            $table->timestamp('fecha_marcado')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->string('numero_retencion', 100)->nullable();
            $table->string('archivo_retencion', 255)->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha_marcado'], 'frs_estado_fecha_idx');
            $table->index(['cliente_id', 'estado'], 'frs_cliente_estado_idx');
            $table->index('aplicacion_pagos_id', 'frs_aplicacion_pago_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_retencion_seguimiento');
    }
};
