<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop if exists to recreate with correct schema
        Schema::dropIfExists('entregas_evidencias');
        
        Schema::create('entregas_evidencias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('entrega_producto_incidencia_id')->comment('ID de la incidencia del producto');
            $table->string('ruta_archivo', 255)->comment('Ruta del archivo de evidencia');
            $table->text('descripcion')->nullable()->comment('Descripción de la evidencia');
            $table->unsignedBigInteger('user_id_registro')->nullable()->comment('Usuario que subió la evidencia');
            $table->timestamps();

            $table->index('entrega_producto_incidencia_id', 'idx_incidencia');
            $table->index('user_id_registro', 'idx_user');

            $table->foreign('entrega_producto_incidencia_id', 'fk_evidencias_incidencia')
                ->references('id')->on('entregas_productos_incidencias')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id_registro', 'fk_evidencias_user')
                ->references('id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas_evidencias');
    }
};
