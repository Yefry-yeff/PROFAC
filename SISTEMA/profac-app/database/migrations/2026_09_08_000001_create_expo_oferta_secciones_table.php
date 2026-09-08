<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tipos_tramites')->updateOrInsert(
            ['id' => 11],
            [
                'nombre' => 'Secciones de Ofertas',
                'estado' => 'activo',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if (Schema::hasTable('flujo_etapas')) {
            DB::table('flujo_etapas')->updateOrInsert(
                ['tipo_tramite_id' => 11],
                [
                    'nombre_display' => 'Secciones de Ofertas',
                    'icono' => 'fa-object-group',
                    'orden' => 3,
                    'es_opcional' => 1,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            foreach ([11 => 3, 10 => 4, 9 => 5, 4 => 6, 3 => 7, 5 => 8, 6 => 9, 7 => 10, 8 => 11] as $tipoId => $orden) {
                DB::table('flujo_etapas')->where('tipo_tramite_id', $tipoId)->update(['orden' => $orden]);
            }
        }

        Schema::create('expo_oferta_seccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flujo_id');
            $table->integer('cotizacion_origen_id');
            $table->integer('cotizacion_id');
            $table->unsignedInteger('numero');
            $table->string('nombre', 150);
            $table->enum('estado', ['EN_REVISION', 'PREFACTURADA', 'ANULADA'])->default('EN_REVISION');
            $table->boolean('finaliza_seccionado')->default(false);
            $table->unsignedBigInteger('prefactura_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->unique('cotizacion_id');
            $table->unique(['cotizacion_origen_id', 'numero']);
            $table->index(['flujo_id', 'estado']);
            $table->foreign('flujo_id')->references('id')->on('flujo')->cascadeOnDelete();
            $table->foreign('cotizacion_origen_id')->references('id')->on('cotizacion')->cascadeOnDelete();
            $table->foreign('cotizacion_id')->references('id')->on('cotizacion')->cascadeOnDelete();
            $table->foreign('prefactura_id')->references('id')->on('prefactura')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_oferta_seccion');

        if (Schema::hasTable('flujo_etapas')) {
            DB::table('flujo_etapas')->where('tipo_tramite_id', 11)->delete();
            foreach ([9 => 3, 10 => 4, 4 => 5, 3 => 6, 5 => 7, 6 => 8, 7 => 9, 8 => 10] as $tipoId => $orden) {
                DB::table('flujo_etapas')->where('tipo_tramite_id', $tipoId)->update(['orden' => $orden]);
            }
        }

        DB::table('tipos_tramites')->where('id', 11)->delete();
    }
};