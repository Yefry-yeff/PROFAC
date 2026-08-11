<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_credito_creditos', function (Blueprint $table) {
            $table->id();
            $table->integer('nota_credito_id')->unique();
            $table->integer('cliente_id');
            $table->decimal('monto_original', 18, 2);
            $table->decimal('monto_aplicado', 18, 2)->default(0);
            $table->decimal('monto_reembolsado', 18, 2)->default(0);
            $table->decimal('saldo_disponible', 18, 2);
            $table->string('estado', 30)->default('disponible');
            $table->timestamps();

            $table->foreign('nota_credito_id')->references('id')->on('nota_credito');
            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->index(['cliente_id', 'estado']);
            $table->index(['cliente_id', 'saldo_disponible']);
        });

        Schema::create('nota_credito_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credito_id');
            $table->string('tipo', 30);
            $table->decimal('monto', 18, 2);
            $table->integer('factura_id')->nullable();
            $table->integer('aplicacion_pagos_id')->nullable();
            $table->integer('banco_id')->nullable();
            $table->integer('tipo_pago_cobro_id')->nullable();
            $table->string('referencia', 100)->nullable();
            $table->string('comprobante', 500)->nullable();
            $table->text('comentario')->nullable();
            $table->unsignedBigInteger('movimiento_origen_id')->nullable();
            $table->unsignedBigInteger('users_id');
            $table->date('fecha_movimiento');
            $table->timestamps();

            $table->foreign('credito_id')->references('id')->on('nota_credito_creditos');
            $table->foreign('factura_id')->references('id')->on('factura');
            $table->foreign('aplicacion_pagos_id')->references('id')->on('aplicacion_pagos');
            $table->foreign('banco_id')->references('id')->on('banco');
            $table->foreign('tipo_pago_cobro_id')->references('id')->on('tipo_pago_cobro');
            $table->foreign('movimiento_origen_id')->references('id')->on('nota_credito_movimientos');
            $table->foreign('users_id')->references('id')->on('users');
            $table->index(['credito_id', 'tipo']);
            $table->index(['factura_id', 'tipo']);
            $table->index(['fecha_movimiento', 'tipo']);
            $table->index(['banco_id', 'fecha_movimiento']);
        });

        DB::statement("
            INSERT INTO nota_credito_creditos
                (nota_credito_id, cliente_id, monto_original, monto_aplicado,
                 monto_reembolsado, saldo_disponible, estado, created_at, updated_at)
            SELECT nc.id, f.cliente_id, nc.total,
                   CASE WHEN nc.estado_rebajado = 1 THEN nc.total ELSE 0 END,
                   0,
                   CASE WHEN nc.estado_nota_id = 1 AND nc.estado_rebajado = 2 THEN nc.total ELSE 0 END,
                   CASE
                       WHEN nc.estado_nota_id = 2 THEN 'anulado'
                       WHEN nc.estado_rebajado = 1 THEN 'legado_consumido'
                       ELSE 'disponible'
                   END,
                   nc.created_at, COALESCE(nc.updated_at, nc.created_at)
            FROM nota_credito nc
            INNER JOIN factura f ON f.id = nc.factura_id
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_credito_movimientos');
        Schema::dropIfExists('nota_credito_creditos');
    }
};