<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteObservacionesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cliente_observaciones')) { return; }
        Schema::create('cliente_observaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->text('observacion');
            $table->integer('users_id');
            $table->timestamps();

            $table->index('cliente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_observaciones');
    }
}
