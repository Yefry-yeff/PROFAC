<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntregasIncidenciasTratamientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entregas_incidencias_tratamientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrega_producto_incidencia_id');
            $table->text('tratamiento');
            $table->unsignedBigInteger('user_id_registro');
            $table->timestamps();

            // Foreign keys
            $table->foreign('entrega_producto_incidencia_id', 'fk_tratamiento_incidencia')
                  ->references('id')
                  ->on('entregas_productos_incidencias')
                  ->onDelete('cascade');

            $table->foreign('user_id_registro', 'fk_tratamiento_usuario')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entregas_incidencias_tratamientos');
    }
}
