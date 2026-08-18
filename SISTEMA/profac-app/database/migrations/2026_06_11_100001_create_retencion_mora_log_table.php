<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetencionMoraLogTable extends Migration
{
    public function up()
    {
        Schema::create('retencion_mora_log', function (Blueprint $table) {
            $table->id();
            $table->integer('factura_id')->index();
            $table->integer('facturas_comision_id')->index();
            $table->integer('rol_id');
            $table->bigInteger('user_id')->unsigned()->nullable();         // Propietario de la comisión
            $table->enum('tipo_factura', ['contado', 'credito']);
            $table->date('fecha_aplicacion');
            $table->smallInteger('dias_transcurridos');
            $table->smallInteger('dias_gracia_configurados');
            $table->decimal('porcentaje_aplicado', 5, 2);                 // 100.00 para contado
            $table->smallInteger('periodo_numero')->nullable();            // null=contado, 1/2/3...=crédito
            $table->decimal('comision_original', 16, 2);                  // monto_rol antes de retención
            $table->decimal('monto_retenido', 16, 2);                     // monto descontado en este evento
            $table->decimal('subtotal_factura', 16, 2);
            $table->bigInteger('usuario_ejecutor')->unsigned()->nullable(); // quien registró el pago
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retencion_mora_log');
    }
}
