<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nota_credito_ajustes_comision', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('nota_credito_id');
            $table->integer('facturas_comision_id');
            $table->integer('factura_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('rol_id');
            $table->date('periodo_original');
            $table->date('periodo_aplicado');
            $table->decimal('monto', 16, 2);
            $table->boolean('comision_pagada')->default(false);
            $table->json('detalle_calculo')->nullable();
            $table->timestamps();

            $table->unique(['nota_credito_id', 'facturas_comision_id'], 'uk_nc_ajuste_comision');
            $table->index(['user_id', 'rol_id', 'periodo_aplicado'], 'idx_nc_ajuste_empleado_periodo');
            $table->index(['factura_id', 'nota_credito_id'], 'idx_nc_ajuste_factura_nota');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_credito_ajustes_comision');
    }
};