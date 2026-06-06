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
        Schema::create('historico_flujo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flujo_id')->constrained('flujo');
            $table->string('tramite_tipo'); // e.g., 'pedido', 'factura'
            $table->unsignedBigInteger('tramite_id')->nullable();
            $table->text('detalle')->nullable();
            $table->integer('cantidad_productos')->nullable();
            $table->string('numero_pedido')->nullable();
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
        Schema::dropIfExists('historico_flujo');
    }
};
