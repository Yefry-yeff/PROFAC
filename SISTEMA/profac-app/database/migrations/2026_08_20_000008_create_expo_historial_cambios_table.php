<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_historial_cambios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->string('accion', 40);
            $table->text('detalle');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['expo_id', 'created_at'], 'expo_historial_expo_fecha_idx');
        });

        $registros = DB::table('expo')->get();
        foreach ($registros as $expo) {
            DB::table('expo_historial_cambios')->insert([
                'expo_id' => $expo->id,
                'accion' => 'CREACION',
                'detalle' => 'Registro inicial de la Expo.',
                'datos_nuevos' => json_encode([
                    'nombre' => $expo->nombre,
                    'estado' => $expo->estado,
                    'fecha_inicio' => $expo->fecha_inicio,
                    'fecha_fin' => $expo->fecha_fin,
                ]),
                'user_id' => $expo->created_by,
                'created_at' => $expo->created_at,
            ]);

            if ((string) $expo->updated_at !== (string) $expo->created_at) {
                DB::table('expo_historial_cambios')->insert([
                    'expo_id' => $expo->id,
                    'accion' => 'ACTUALIZACION',
                    'detalle' => 'Última modificación anterior a la habilitación de la bitácora.',
                    'datos_nuevos' => json_encode(['estado' => $expo->estado]),
                    'user_id' => $expo->updated_by,
                    'created_at' => $expo->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_historial_cambios');
    }
};
