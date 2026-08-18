<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteZonaAsignacionesTables extends Migration
{
    public function up()
    {
        Schema::create('cliente_zona_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zona_id');
            $table->integer('cliente_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('rol_id');
            $table->timestamps();

            $table->unique(['cliente_id', 'rol_id'], 'cliente_zona_asignaciones_cliente_rol_unique');
            $table->foreign('zona_id')->references('id')->on('cliente_zonas')->cascadeOnDelete();
            $table->foreign('cliente_id')->references('id')->on('cliente')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('cliente_zona_excepciones', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'rol_id'], 'cliente_zona_excepciones_cliente_rol_unique');
            $table->foreign('cliente_id')->references('id')->on('cliente')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_zona_excepciones');
        Schema::dropIfExists('cliente_zona_asignaciones');
    }
}