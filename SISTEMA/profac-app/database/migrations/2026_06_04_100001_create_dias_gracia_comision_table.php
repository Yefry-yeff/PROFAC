<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiasGraciaComisionTable extends Migration
{
    public function up()
    {
        Schema::create('dias_gracia_comision', function (Blueprint $table) {
            $table->id();
            $table->integer('rol_id');
            // tipo_factura: 'contado' | 'credito'
            $table->enum('tipo_factura', ['contado', 'credito']);
            // días desde la fecha de pago (contado) o desde vencimiento (crédito)
            $table->unsignedSmallInteger('dias_gracia')->default(0);
            // notas adicionales opcionales
            $table->string('descripcion', 200)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('rol_id')->references('id')->on('rol')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Un rol sólo puede tener UN registro por tipo de factura
            $table->unique(['rol_id', 'tipo_factura'], 'uq_rol_tipo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dias_gracia_comision');
    }
}
