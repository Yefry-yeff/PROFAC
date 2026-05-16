<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('historico_flujo', function (Blueprint $table) {
            $table->string('estado')->after('tramite_id');
            $table->text('observaciones')->nullable()->after('tramite_id');
            $table->dropColumn(['detalle', 'cantidad_productos', 'numero_pedido']);
        });
    }

    public function down()
    {
        Schema::table('historico_flujo', function (Blueprint $table) {
            $table->dropColumn('estado');
            $table->dropColumn('observaciones');
            $table->text('detalle')->nullable()->after('tramite_id');
            $table->integer('cantidad_productos')->nullable()->after('detalle');
            $table->string('numero_pedido')->nullable()->after('cantidad_productos');
        });
    }
};
