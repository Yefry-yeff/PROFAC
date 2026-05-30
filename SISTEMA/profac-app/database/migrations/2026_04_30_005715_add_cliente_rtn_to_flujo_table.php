<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClienteRtnToFlujoTable extends Migration
{
    public function up()
    {
        Schema::table('flujo', function (Blueprint $table) {
            $table->string('cliente_rtn', 30)->nullable()->after('nombre');
        });
    }

    public function down()
    {
        Schema::table('flujo', function (Blueprint $table) {
            $table->dropColumn('cliente_rtn');
        });
    }
}
