<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_asistencia_descuento_escala', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_asistencia_id');
            $table->integer('escala_id');
            $table->string('descuento_modo', 20);
            $table->unsignedSmallInteger('descuento_escalon')->nullable();
            $table->unsignedBigInteger('asignado_por');
            $table->timestamps();

            $table->unique(['expo_asistencia_id', 'escala_id'], 'expo_asistencia_descuento_escala_unique');
            $table->foreign('expo_asistencia_id', 'expo_asistencia_descuento_asistencia_fk')
                ->references('id')->on('expo_asistencia')->cascadeOnDelete();
            $table->foreign('escala_id', 'expo_asistencia_descuento_escala_fk')
                ->references('id')->on('categoria_precios')->cascadeOnDelete();
            $table->foreign('asignado_por', 'expo_asistencia_descuento_usuario_fk')
                ->references('id')->on('users');
        });

        $asistencias = DB::table('expo_asistencia')
            ->whereIn('descuento_modo', ['escalon', 'maximo'])
            ->get(['id', 'expo_id', 'descuento_modo', 'descuento_escalon', 'descuento_asignado_por']);
        foreach ($asistencias as $asistencia) {
            $escalaIds = DB::table('expo_descuento_escala')
                ->where('expo_id', $asistencia->expo_id)
                ->distinct()
                ->pluck('escala_id');
            foreach ($escalaIds as $escalaId) {
                DB::table('expo_asistencia_descuento_escala')->insert([
                    'expo_asistencia_id' => $asistencia->id,
                    'escala_id' => $escalaId,
                    'descuento_modo' => $asistencia->descuento_modo,
                    'descuento_escalon' => $asistencia->descuento_escalon,
                    'asignado_por' => $asistencia->descuento_asignado_por ?: DB::table('expo_asistencia')->where('id', $asistencia->id)->value('registrado_por'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_asistencia_descuento_escala');
    }
};