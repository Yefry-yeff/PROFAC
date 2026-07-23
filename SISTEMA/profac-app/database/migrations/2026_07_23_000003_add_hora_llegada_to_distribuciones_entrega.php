<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHoraLlegadaToDistribucionesEntrega extends Migration
{
    public function up()
    {
        Schema::table('distribuciones_entrega', function (Blueprint $table) {
            $table->time('hora_llegada')->nullable()->after('observaciones_salida');
        });
    }

    public function down()
    {
        Schema::table('distribuciones_entrega', function (Blueprint $table) {
            $table->dropColumn('hora_llegada');
        });
    }
}
