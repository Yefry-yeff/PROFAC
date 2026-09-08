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

        DB::statement("ALTER TABLE expo_oferta_seccion MODIFY estado ENUM('EN_REVISION','EN_REVISION_CREDITO','EN_REVISION_INVENTARIO','PREFACTURADA','FACTURADA','RECHAZADA_CREDITO','DEVUELTA_INVENTARIO','ANULADA') NOT NULL DEFAULT 'EN_REVISION_CREDITO'");
        DB::table('expo_oferta_seccion')
            ->where('estado', 'EN_REVISION')
            ->update(['estado' => 'EN_REVISION_CREDITO']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('expo_oferta_seccion')) {
            return;
        }

        DB::table('expo_oferta_seccion')
            ->whereIn('estado', ['EN_REVISION_CREDITO', 'EN_REVISION_INVENTARIO'])
            ->update(['estado' => 'EN_REVISION']);
        DB::table('expo_oferta_seccion')
            ->whereIn('estado', ['RECHAZADA_CREDITO', 'DEVUELTA_INVENTARIO', 'FACTURADA'])
            ->update(['estado' => 'ANULADA']);
        DB::statement("ALTER TABLE expo_oferta_seccion MODIFY estado ENUM('EN_REVISION','PREFACTURADA','ANULADA') NOT NULL DEFAULT 'EN_REVISION'");
    }
};
