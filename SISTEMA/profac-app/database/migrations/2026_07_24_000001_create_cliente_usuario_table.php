<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteUsuarioTable extends Migration
{
    /**
     * Relación flexible entre clientes y usuarios (sin tipo).
     * El "tipo" de la relación (Asesor Comercial, Tele Asesor, etc.) se deriva
     * dinámicamente del rol_id del usuario asignado (users.rol_id) al momento
     * de mostrar la información — no se almacena aquí.
     */
    public function up()
    {
        if (Schema::hasTable('cliente_usuario')) {
            return;
        }

        Schema::create('cliente_usuario', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->unsignedBigInteger('usuario_id');
            $table->dateTime('fecha_asignacion')->useCurrent();
            $table->unsignedBigInteger('asignado_por')->nullable();
            $table->timestamps();

            $table->index('cliente_id');
            $table->index('usuario_id');
            $table->unique(['cliente_id', 'usuario_id'], 'cliente_usuario_unique');

            $table->foreign('cliente_id')->references('id')->on('cliente')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('asignado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_usuario');
    }
}
