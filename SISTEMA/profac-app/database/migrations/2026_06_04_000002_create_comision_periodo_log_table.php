<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComisionPeriodoLogTable extends Migration
{
    public function up()
    {
        Schema::create('comision_periodo_log', function (Blueprint $table) {
            $table->increments('id');

            // Período afectado (primer día del mes)
            $table->date('periodo')->comment('Período al que corresponde el log');

            // FK al registro de comision_periodo (puede ser null si el periodo aún no existía)
            $table->unsignedInteger('comision_periodo_id')->nullable();

            // Acción realizada
            $table->enum('accion', ['conciliacion', 'reapertura'])->comment('Tipo de acción registrada');

            // Estados antes y después
            $table->tinyInteger('estado_anterior')->comment('Estado previo: 0=Abierto, 1=Conciliado');
            $table->tinyInteger('estado_nuevo')->comment('Estado resultante: 0=Abierto, 1=Conciliado');

            // ─── SNAPSHOT COMPLETO al momento de la acción ───────────────
            // Totales globales del período
            $table->decimal('snapshot_total_comision', 16, 2)->default(0);
            $table->unsignedInteger('snapshot_cantidad_empleados')->default(0);
            $table->unsignedInteger('snapshot_cantidad_facturas')->default(0);

            // Detalle por empleado como JSON para trazabilidad completa
            // Estructura: [{user_id, nombre, rol, rol_id, mes_comision,
            //               comision_acumulada, cantidad_facturas}]
            $table->json('snapshot_detalle_empleados')->nullable()
                  ->comment('Detalle por empleado en el momento de la acción');

            // Detalle por factura como JSON
            // Estructura: [{factura_id, fecha_cierre, monto_rol, rol, tipo_comision}]
            $table->json('snapshot_detalle_facturas')->nullable()
                  ->comment('Detalle de facturas comisionadas en el período');

            // Motivo ingresado por el usuario
            $table->text('observacion')->nullable()->comment('Nota del admin para este registro');

            // Usuario que ejecutó la acción
            $table->unsignedBigInteger('usuario_id');
            $table->string('usuario_nombre', 150)->comment('Desnormalizado para trazabilidad');

            $table->timestamps();

            $table->index('periodo');
            $table->index('accion');
            $table->index('usuario_id');
            $table->index(['periodo', 'accion']);
            $table->foreign('comision_periodo_id')->references('id')->on('comision_periodo')->nullOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comision_periodo_log');
    }
}
