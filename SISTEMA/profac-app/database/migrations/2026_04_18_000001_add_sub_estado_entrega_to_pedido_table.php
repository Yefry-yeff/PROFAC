<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->enum('sub_estado_entrega', ['sin_entrega', 'en_camino', 'entregado'])
                  ->default('sin_entrega')
                  ->after('estado')
                  ->comment('Estado del sub-flujo de entrega del pedido');
        });
    }

    public function down()
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('sub_estado_entrega');
        });
    }
};
