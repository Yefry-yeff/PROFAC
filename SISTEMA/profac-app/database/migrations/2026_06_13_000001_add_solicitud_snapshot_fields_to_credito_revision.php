<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos de solicitud a credito_revision.
 * Guarda el snapshot de lo solicitado por el flujo, separado de lo aprobado.
 */
class AddSolicitudSnapshotFieldsToCreditoRevision extends Migration
{
    public function up(): void
    {
        Schema::table('credito_revision', function (Blueprint $table) {
            $table->date('fecha_emision_solicitada')
                  ->nullable()
                  ->after('fecha_aprobacion')
                  ->comment('Fecha de emisión solicitada por el flujo');

            $table->date('fecha_vencimiento_solicitada')
                  ->nullable()
                  ->after('fecha_emision_solicitada')
                  ->comment('Fecha de vencimiento solicitada por el flujo');

            $table->unsignedInteger('dias_credito_solicitados')
                  ->nullable()
                  ->after('fecha_vencimiento_solicitada')
                  ->comment('Días de crédito solicitados por el flujo');
        });
    }

    public function down(): void
    {
        Schema::table('credito_revision', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_emision_solicitada',
                'fecha_vencimiento_solicitada',
                'dias_credito_solicitados',
            ]);
        });
    }
}