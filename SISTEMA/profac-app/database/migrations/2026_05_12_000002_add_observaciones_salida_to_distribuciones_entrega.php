<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObservacionesSalidaToDistribucionesEntrega extends Migration
{
    public function up()
    {
        Schema::table('distribuciones_entrega', function (Blueprint $table) {
            $table->text('observaciones_salida')->nullable()->after('hora_salida');
        });
    }

    public function down()
    {
        Schema::table('distribuciones_entrega', function (Blueprint $table) {
            $table->dropColumn('observaciones_salida');
        });
    }
}
