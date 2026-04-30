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
        Schema::create('cotizacion_estado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('flujo_id')->nullable();
            // 1 = ganadora, 2 = perdedora (quitada), 3 = anulada
            $table->tinyInteger('ganadora')->unsigned()->default(1)->comment('1=ganadora, 2=perdedora, 3=anulada');
            $table->string('comentario', 500)->nullable();
            $table->unsignedInteger('estado_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('cotizacion_id');
            $table->index('flujo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cotizacion_estado');
    }
};
