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
        Schema::table('precios_producto_carga', function (Blueprint $table) {
            $table->unsignedBigInteger('users_id_actualizador')->nullable()->after('users_id_creador');
            $table->dateTime('fecha_ultima_actualizacion')->nullable()->after('users_id_actualizador');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('precios_producto_carga', function (Blueprint $table) {
            $table->dropColumn(['users_id_actualizador', 'fecha_ultima_actualizacion']);
        });
    }
};
