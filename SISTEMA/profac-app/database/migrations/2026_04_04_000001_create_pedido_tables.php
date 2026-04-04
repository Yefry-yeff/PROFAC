<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pedido')) {
            Schema::create('pedido', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cliente_id');
                $table->unsignedBigInteger('users_id');
                $table->string('estado', 50)->default('pendiente');
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index('cliente_id');
                $table->index('users_id');
            });
        }

        if (!Schema::hasTable('pedido_detalle')) {
            Schema::create('pedido_detalle', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pedido_id');
                $table->string('nombre_producto', 255);
                $table->decimal('cantidad', 12, 4);
                $table->timestamps();

                $table->index('pedido_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pedido_detalle');
        Schema::dropIfExists('pedido');
    }
}
