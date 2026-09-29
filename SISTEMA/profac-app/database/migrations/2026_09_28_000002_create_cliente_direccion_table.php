<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cliente_direccion')) {
            Schema::create('cliente_direccion', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('cliente_id');
                $table->string('etiqueta', 100)->default('Principal');
                $table->integer('pais_id')->nullable();
                $table->integer('departamento_id')->nullable();
                $table->integer('municipio_id')->nullable();
                $table->text('direccion');
                $table->decimal('latitud', 10, 7)->nullable();
                $table->decimal('longitud', 10, 7)->nullable();
                $table->boolean('principal')->default(false);
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index(['cliente_id', 'activo'], 'idx_cliente_direccion_cliente_activo');
                $table->index('municipio_id', 'idx_cliente_direccion_municipio');
                $table->foreign('cliente_id', 'fk_cliente_direccion_cliente')
                    ->references('id')->on('cliente')
                    ->cascadeOnDelete()->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_direccion');
    }
};
