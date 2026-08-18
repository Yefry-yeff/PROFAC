<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturaTratamientoEntregaTable extends Migration
{
    /**
     * Guarda el "tratamiento" (departamento, municipio, dirección de entrega)
     * que el gestor de entrega asigna a una factura antes de poder asignarla
     * a un equipo de entrega. 1 registro vigente por factura.
     */
    public function up()
    {
        if (Schema::hasTable('factura_tratamiento_entrega')) { return; }

        Schema::create('factura_tratamiento_entrega', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factura_id');
            $table->integer('department_id');
            $table->integer('municipality_id');
            $table->string('direccion_entrega', 255);
            $table->unsignedBigInteger('gestor_entrega_id')->nullable();

            // Campos de auditoría (misma convención que zone_group_details)
            $table->unsignedBigInteger('usr_registro')->nullable();
            $table->unsignedBigInteger('usr_actualizo')->nullable();

            $table->timestamps();

            $table->unique('factura_id', 'uk_fte_factura');
            $table->index('department_id', 'idx_fte_department');
            $table->index('municipality_id', 'idx_fte_municipality');
        });
    }

    public function down()
    {
        Schema::dropIfExists('factura_tratamiento_entrega');
    }
}
