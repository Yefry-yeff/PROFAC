<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoToOfertaTable extends Migration
{
    public function up()
    {
        Schema::table('oferta', function (Blueprint $table) {
            $table->enum('estado', ['activa', 'cancelada', 'ganadora'])
                  ->default('activa')
                  ->after('nota');
        });
    }

    public function down()
    {
        Schema::table('oferta', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
}
