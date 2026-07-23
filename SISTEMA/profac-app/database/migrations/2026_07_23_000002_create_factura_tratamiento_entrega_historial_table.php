<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturaTratamientoEntregaHistorialTable extends Migration
{
    /**
     * Bitácora de transiciones de estado del flujo de "Gestión de Distribución
     * de Entregas": pendiente -> tratada -> asignada -> completada.
     *
     * Nota: la transición a "completada" NO se registra aquí (se deriva en
     * tiempo real desde distribuciones_entrega_facturas.estado_entrega, que ya
     * es actualizado por los triggers existentes trg_actualizar_estado_factura_*
     * sobre entregas_productos). Solo "tratada" y "asignada" generan bitácora,
     * ya que son acciones propias de este módulo.
     */
    public function up()
    {
        if (Schema::hasTable('factura_tratamiento_entrega_historial')) { return; }

        Schema::create('factura_tratamiento_entrega_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factura_id');
            $table->string('estado', 20); // pendiente | tratada | asignada | completada
            $table->unsignedBigInteger('distribucion_entrega_id')->nullable();

            // Snapshot de los datos de tratamiento en el momento del movimiento
            $table->integer('department_id')->nullable();
            $table->integer('municipality_id')->nullable();
            $table->string('direccion_entrega', 255)->nullable();

            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->index('factura_id', 'idx_fteh_factura');
            $table->index('estado', 'idx_fteh_estado');
            $table->index('distribucion_entrega_id', 'idx_fteh_distribucion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('factura_tratamiento_entrega_historial');
    }
}
