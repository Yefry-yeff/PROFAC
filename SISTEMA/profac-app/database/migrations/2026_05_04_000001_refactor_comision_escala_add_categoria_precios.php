<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorComisionEscalaAddCategoriaPrecios extends Migration
{
    public function up()
    {
        Schema::table('comision_escala', function (Blueprint $table) {
            // Agregar la nueva columna de categoría de precio
            $table->unsignedBigInteger('categoria_precios_id')->nullable()->after('cliente_categoria_escala_id');

            // Eliminar columnas que ya no aplican
            $table->dropColumn(['escala_precio', 'rango_inicial', 'rango_final']);
        });
    }

    public function down()
    {
        Schema::table('comision_escala', function (Blueprint $table) {
            $table->dropColumn('categoria_precios_id');
            $table->string('escala_precio', 1)->nullable();
            $table->decimal('rango_inicial', 16, 2)->nullable();
            $table->decimal('rango_final', 16, 2)->nullable();
        });
    }
}
