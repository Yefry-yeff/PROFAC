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

        DB::table('expo_oferta_seccion')
            ->orderBy('id')
            ->each(function ($seccion) {
                DB::table('expo_oferta_seccion')->where('id', $seccion->id)->update([
                    'nombre' => 'Seccion ' . $seccion->numero . ' - Oferta #' . $seccion->cotizacion_id,
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
    }
};