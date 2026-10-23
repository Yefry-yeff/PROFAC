<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('proveedor_id')->index();
            $table->unsignedInteger('usuario_id');
            $table->string('numero_factura');
            $table->string('cai')->nullable();
            $table->unsignedInteger('tipo_pago_id');
            $table->date('fecha_emision');
            $table->date('fecha_recepcion');
            $table->date('fecha_vencimiento');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('isv', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('saldo', 15, 2);
            $table->string('estado', 20)->default('activo')->index();
            $table->unsignedInteger('anulado_por')->nullable();
            $table->timestamp('anulado_at')->nullable();
            $table->string('motivo_anulacion', 1000)->nullable();
            $table->timestamps();
        });

        Schema::create('gasto_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos');
            $table->string('nombre');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio', 11, 2);
            $table->unsignedTinyInteger('isv_porcentaje');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('isv', 15, 2);
            $table->decimal('total', 15, 2);
        });

        Schema::create('gasto_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos');
            $table->unsignedInteger('usuario_id');
            $table->decimal('monto', 15, 2);
            $table->decimal('saldo_restante', 15, 2);
            $table->date('fecha_pago');
            $table->string('comprobante');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_pagos');
        Schema::dropIfExists('gasto_detalles');
        Schema::dropIfExists('gastos');
    }
};
