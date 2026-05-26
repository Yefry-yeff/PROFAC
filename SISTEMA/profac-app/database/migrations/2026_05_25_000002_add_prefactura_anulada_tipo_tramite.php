<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Insertar nuevo tipo de trámite para "Prefactura Anulada"
        $tipoId = DB::table('tipos_tramites')->insertGetId([
            'nombre'     => 'Prefactura Anulada',
            'estado'     => 'activo',
            'estado_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Registrar en el catálogo de etapas del flujo (visible en config. de notificaciones)
        DB::table('flujo_etapas')->insert([
            'tipo_tramite_id' => $tipoId,
            'nombre_display'  => 'Pref. Anulada',
            'icono'           => 'fa-times-circle',
            'orden'           => 11,
            'es_opcional'     => 1,
            'activo'          => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // 3. Invalidar caché para que el dropdown de notificaciones lo incluya de inmediato
        Cache::forget('flujo_etapas_activas');
    }

    public function down(): void
    {
        $tipoId = DB::table('tipos_tramites')->where('nombre', 'Prefactura Anulada')->value('id');
        if ($tipoId) {
            DB::table('flujo_etapas')->where('tipo_tramite_id', $tipoId)->delete();
            DB::table('tipos_tramites')->where('id', $tipoId)->delete();
        }
        Cache::forget('flujo_etapas_activas');
    }
};
