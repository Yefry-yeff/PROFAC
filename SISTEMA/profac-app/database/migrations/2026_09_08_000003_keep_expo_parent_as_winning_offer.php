<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expo_oferta_seccion')) {
            return;
        }

        DB::transaction(function () {
            $secciones = DB::table('expo_oferta_seccion')
                ->get(['id', 'flujo_id', 'cotizacion_origen_id', 'cotizacion_id', 'numero']);

            foreach ($secciones as $seccion) {
                DB::table('historico_flujo')
                    ->where('flujo_id', $seccion->flujo_id)
                    ->where('tipo_tramite_id', 2)
                    ->where('tramite_id', $seccion->cotizacion_id)
                    ->delete();

                DB::table('historico_flujo')
                    ->where('flujo_id', $seccion->flujo_id)
                    ->where('tipo_tramite_id', 2)
                    ->where('tramite_id', $seccion->cotizacion_origen_id)
                    ->whereIn('observaciones', ['ganadora', 'ganadora_origen_expo'])
                    ->update([
                        'observaciones' => 'ganadora',
                        'updated_at' => now(),
                    ]);

                DB::table('expo_oferta_seccion')->where('id', $seccion->id)->update([
                    'nombre' => 'Seccion ' . $seccion->numero,
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
    }
};