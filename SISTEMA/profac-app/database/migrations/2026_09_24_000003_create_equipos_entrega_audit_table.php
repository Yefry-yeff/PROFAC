<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipoEntregaAuditTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('equipos_entrega_audit')) { return; }
        Schema::create('equipos_entrega_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_entrega_id')->nullable();
            $table->string('action', 50); // CREATE, UPDATE
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('equipo_entrega_id');
            $table->index(['action', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipos_entrega_audit');
    }
}
