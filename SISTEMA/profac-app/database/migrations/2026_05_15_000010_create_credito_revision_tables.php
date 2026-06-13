<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración: Revisión de Crédito
 *
 * Crea las tablas credito_revision y credito_revision_historial,
 * y registra el tipo de trámite id=10 (Revisión de Crédito).
 */
class CreateCreditoRevisionTables extends Migration
{
    public function up(): void
    {
        // ── 1. tipo_tramite id=10 ─────────────────────────────────────────
        $existeTipo = DB::table('tipos_tramites')->where('id', 10)->exists();
        if (!$existeTipo) {
            DB::table('tipos_tramites')->insert([
                'id'         => 10,
                'nombre'     => 'Revision de Credito',
                'estado'     => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── 2. estado_venta id=10 ─────────────────────────────────────────
        $existeEst = DB::table('estado_venta')->where('id', 10)->exists();
        if (!$existeEst) {
            DB::table('estado_venta')->insert([
                'id'          => 10,
                'descripcion' => 'Revision de Credito',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // ── 3. credito_revision ───────────────────────────────────────────
        if (!Schema::hasTable('credito_revision')) {
            Schema::create('credito_revision', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('flujo_id');
                $table->unsignedBigInteger('cotizacion_id')->nullable()
                      ->comment('Oferta ganadora vinculada');

                $table->string('estado', 20)->default('pendiente')
                      ->comment('pendiente | aprobado | rechazado');

                $table->date('fecha_aprobacion')->nullable()
                      ->comment('Fecha en que el crédito fue autorizado');
                    $table->date('fecha_emision_solicitada')->nullable()
                        ->comment('Fecha de emisión solicitada por el flujo');
                    $table->date('fecha_vencimiento_solicitada')->nullable()
                        ->comment('Fecha de vencimiento solicitada por el flujo');
                    $table->unsignedInteger('dias_credito_solicitados')->nullable()
                        ->comment('Días de crédito solicitados por el flujo');
                $table->date('fecha_vencimiento_credito')->nullable()
                      ->comment('Fecha hasta la que es válida la autorización');

                $table->text('motivo_rechazo')->nullable();
                $table->text('observaciones')->nullable();

                $table->unsignedBigInteger('usuario_revision')->nullable()
                      ->comment('Usuario que aprobó o rechazó');

                $table->string('ip_revision', 45)->nullable()
                      ->comment('IP del usuario al aprobar/rechazar');

                $table->timestamps();

                $table->index('flujo_id');
                $table->index('estado');
            });
        }

        // ── 4. credito_revision_historial ─────────────────────────────────
        if (!Schema::hasTable('credito_revision_historial')) {
            Schema::create('credito_revision_historial', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('credito_revision_id')
                      ->nullable()
                      ->comment('FK a credito_revision.id');
                $table->unsignedBigInteger('flujo_id');

                $table->string('estado_anterior', 20)->nullable();
                $table->string('estado_nuevo', 20)->nullable();

                $table->string('accion', 60)
                      ->comment('creado | aprobado | rechazado | reenviado | devolucion_oferta | retorno_inventario | cancelado');

                $table->text('descripcion')->nullable();

                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('ip', 45)->nullable();

                $table->timestamp('fecha_evento')->useCurrent();

                $table->timestamp('created_at')->useCurrent();
                // Sin updated_at: nunca se modifica, solo se inserta

                $table->index('flujo_id');
                $table->index('credito_revision_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('credito_revision_historial');
        Schema::dropIfExists('credito_revision');
        DB::table('tipos_tramites')->where('id', 10)->delete();
        DB::table('estado_venta')->where('id', 10)->delete();
    }
}
