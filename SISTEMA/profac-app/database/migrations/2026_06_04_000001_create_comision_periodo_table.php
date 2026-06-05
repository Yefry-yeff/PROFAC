<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComisionPeriodoTable extends Migration
{
    public function up()
    {
        Schema::create('comision_periodo', function (Blueprint $table) {
            $table->increments('id');

            // Primer día del mes: 2026-05-01, 2026-06-01, etc.
            $table->date('periodo')->unique()->comment('Primer día del mes al que aplica este control');

            // 0 = Abierto (Sin Conciliar), 1 = Conciliado
            $table->tinyInteger('estado')->default(0)->comment('0=Abierto, 1=Conciliado');

            // Snapshot de totales al momento de conciliar (puede actualizarse si se reabre y vuelve a conciliar)
            $table->decimal('total_comision', 16, 2)->default(0)->comment('Total comisión acumulada del período');
            $table->unsignedInteger('cantidad_empleados')->default(0)->comment('Empleados con comisión en el período');
            $table->unsignedInteger('cantidad_facturas')->default(0)->comment('Facturas comisionadas en el período');

            // Conciliación
            $table->text('observacion_conciliacion')->nullable()->comment('Nota del admin al conciliar');
            $table->unsignedBigInteger('usuario_concilio')->nullable()->comment('FK users.id — quien concilió');
            $table->dateTime('fecha_conciliacion')->nullable()->comment('Fecha y hora de la conciliación');

            $table->timestamps();

            $table->index('estado');
            $table->index('periodo');
            $table->foreign('usuario_concilio')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comision_periodo');
    }
}
