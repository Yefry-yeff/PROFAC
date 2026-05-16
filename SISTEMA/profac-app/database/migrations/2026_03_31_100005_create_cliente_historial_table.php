<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteHistorialTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cliente_historial')) { return; }
        Schema::create('cliente_historial', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->string('accion', 255);
            $table->text('descripcion')->nullable();
            $table->integer('users_id')->nullable();
            $table->timestamps();
            $table->index('cliente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_historial');
    }
}
