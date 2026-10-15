<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_no_identificados', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id')->nullable();
            $table->boolean('cliente_original_conocido');
            $table->integer('banco_id');
            $table->string('cuenta_bancaria', 45);
            $table->decimal('monto_original', 12, 2);
            $table->decimal('saldo_disponible', 12, 2);
            $table->date('fecha_pago')->index();
            $table->text('observaciones')->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->unsignedBigInteger('creado_por');
            $table->uuid('clave_registro')->unique();
            $table->unsignedBigInteger('identificado_por')->nullable();
            $table->timestamp('identificado_at')->nullable();
            $table->timestamps();
            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->foreign('banco_id')->references('id')->on('banco');
            $table->foreign('creado_por')->references('id')->on('users');
            $table->foreign('identificado_por')->references('id')->on('users');
            $table->index(['cliente_id', 'estado']);
        });

        Schema::create('pni_operaciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('clave')->unique();
            $table->string('huella', 64);
            $table->integer('cliente_id');
            $table->integer('factura_id');
            $table->decimal('monto', 12, 2);
            $table->decimal('saldo_factura_antes', 18, 2);
            $table->decimal('saldo_factura_despues', 18, 2);
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();
            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->foreign('factura_id')->references('id')->on('factura');
            $table->foreign('usuario_id')->references('id')->on('users');
        });

        Schema::create('pni_aplicaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operacion_id');
            $table->unsignedBigInteger('pago_id');
            $table->integer('abono_id')->unique();
            $table->decimal('monto', 12, 2);
            $table->decimal('saldo_antes', 12, 2);
            $table->decimal('saldo_despues', 12, 2);
            $table->timestamps();
            $table->foreign('operacion_id')->references('id')->on('pni_operaciones');
            $table->foreign('pago_id')->references('id')->on('pagos_no_identificados');
            $table->foreign('abono_id')->references('id')->on('abonos_creditos');
            $table->unique(['operacion_id', 'pago_id']);
            $table->index(['pago_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pni_aplicaciones');
        Schema::dropIfExists('pni_operaciones');
        Schema::dropIfExists('pagos_no_identificados');
    }
};
