<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteZonasTables extends Migration
{
    public function up()
    {
        Schema::create('cliente_zonas', function (Blueprint $table) {
            $table->id();
            $table->integer('departamento_id');
            $table->string('nombre', 120);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('asesor_comercial_id')->nullable();
            $table->unsignedBigInteger('teleasesor_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['departamento_id', 'nombre'], 'cliente_zonas_departamento_nombre_unique');
            $table->index('activo');
            $table->foreign('departamento_id')->references('id')->on('departamento');
            $table->foreign('asesor_comercial_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('teleasesor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('cliente_zona_miembros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zona_id');
            $table->integer('cliente_id');
            $table->unsignedBigInteger('asignado_por')->nullable();
            $table->timestamps();

            $table->unique('cliente_id', 'cliente_zona_miembros_cliente_unique');
            $table->index('zona_id');
            $table->foreign('zona_id')->references('id')->on('cliente_zonas')->cascadeOnDelete();
            $table->foreign('cliente_id')->references('id')->on('cliente')->cascadeOnDelete();
            $table->foreign('asignado_por')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('cliente_zona_auditoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->integer('cliente_id')->nullable();
            $table->string('accion', 40);
            $table->text('detalle')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['zona_id', 'created_at']);
            $table->index(['cliente_id', 'created_at']);
            $table->foreign('zona_id')->references('id')->on('cliente_zonas')->nullOnDelete();
            $table->foreign('cliente_id')->references('id')->on('cliente')->nullOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_zona_auditoria');
        Schema::dropIfExists('cliente_zona_miembros');
        Schema::dropIfExists('cliente_zonas');
    }
}