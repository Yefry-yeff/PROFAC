<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComisionReversionesTable extends Migration
{
    public function up()
    {
        Schema::create('comision_reversiones', function (Blueprint $table) {
            $table->increments('id');

            // Abono que se anuló
            $table->unsignedInteger('abono_id');
            // Factura afectada
            $table->unsignedInteger('factura_id');
            // Registro de aplicacion_pagos correspondiente
            $table->unsignedInteger('aplicacion_pagos_id');

            // Monto del abono anulado
            $table->decimal('monto_abono_anulado', 18, 2);

            // Si había comisiones registradas que fueron revertidas
            $table->boolean('tenia_comisiones')->default(false);

            // JSON snapshot de las comisiones revertidas (para auditoría)
            // Estructura: [{facturas_comision_id, rol_id, tipo_comision, monto_revertido,
            //               usuario_id, usuario_nombre, mes_afectado, comision_empleado_id}]
            $table->json('comisiones_revertidas')->nullable();

            // Motivo ingresado por el usuario
            $table->text('motivo');

            // Si la factura fue reabierta (estaba cerrada)
            $table->boolean('factura_reabierta')->default(false);

            // Usuario que ejecutó la anulación
            $table->unsignedBigInteger('usr_anulo');

            $table->timestamps();

            $table->index('factura_id');
            $table->index('abono_id');
            $table->index('usr_anulo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comision_reversiones');
    }
}
