<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToClienteTable extends Migration
{
    public function up()
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->integer('ano_operacion')->nullable()->after('cliente_categoria_escala_id');
            $table->string('dni_representante_legal', 20)->nullable()->after('ano_operacion');
        });
    }

    public function down()
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropColumn(['ano_operacion', 'dni_representante_legal']);
        });
    }
}
