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
        Schema::create('flujo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_flujo_id')->constrained('tipos_flujo');
            $table->string('identificacion');
            $table->string('nombre');
            $table->string('estado');
            $table->foreignId('estatus_id')->constrained('tipos_estatus');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flujo');
    }
};
