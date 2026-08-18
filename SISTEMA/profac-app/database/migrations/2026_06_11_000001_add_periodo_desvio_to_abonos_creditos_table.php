<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodoDesvioToAbonosCreditosTable extends Migration
{
    public function up()
    {
        Schema::table('abonos_creditos', function (Blueprint $table) {
            // Mes del pago original (puede estar conciliado)
            $table->date('periodo_comision_original')->nullable()
                  ->comment('Mes de la fecha_pago ingresada, aunque esté conciliado')
                  ->after('fecha_pago');

            // Mes donde realmente se acreditará la comisión (próximo abierto)
            $table->date('periodo_comision_asignado')->nullable()
                  ->comment('Mes real donde se acredita la comisión si hubo desvío')
                  ->after('periodo_comision_original');

            // Usuario que aceptó el aviso de desvío
            $table->unsignedBigInteger('desvio_confirmado_por')->nullable()
                  ->comment('FK users.id — quien confirmó el desvío de período')
                  ->after('periodo_comision_asignado');

            $table->foreign('desvio_confirmado_por')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->index('periodo_comision_asignado');
            $table->index('periodo_comision_original');
        });
    }

    public function down()
    {
        Schema::table('abonos_creditos', function (Blueprint $table) {
            $table->dropForeign(['desvio_confirmado_por']);
            $table->dropIndex(['periodo_comision_asignado']);
            $table->dropIndex(['periodo_comision_original']);
            $table->dropColumn(['periodo_comision_original', 'periodo_comision_asignado', 'desvio_confirmado_por']);
        });
    }
}
