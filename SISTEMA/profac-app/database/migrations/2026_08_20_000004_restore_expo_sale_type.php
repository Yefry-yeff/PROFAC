<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tipo_venta')->updateOrInsert(
            ['descripcion' => 'Expo'],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('tipo_venta')
            ->whereRaw('LOWER(TRIM(descripcion)) = ?', ['expo'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('cotizacion')
                    ->whereColumn('cotizacion.tipo_venta_id', 'tipo_venta.id');
            })
            ->delete();
    }
};