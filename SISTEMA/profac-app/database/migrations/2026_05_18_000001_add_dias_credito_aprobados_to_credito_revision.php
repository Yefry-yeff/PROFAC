<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega columna dias_credito_aprobados a credito_revision.
 * Almacena los días de crédito aprobados explícitamente para cada
 * transacción, evitando dependencia del campo global cliente.dias_credito.
 */
class AddDiasCreditoAprobadosToCreditoRevision extends Migration
{
    public function up(): void
    {
        Schema::table('credito_revision', function (Blueprint $table) {
            $table->unsignedInteger('dias_credito_aprobados')
                  ->nullable()
                  ->after('fecha_vencimiento_credito')
                  ->comment('Días de crédito aprobados para esta transacción específica');
        });
    }

    public function down(): void
    {
        Schema::table('credito_revision', function (Blueprint $table) {
            $table->dropColumn('dias_credito_aprobados');
        });
    }
}
