<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE expo MODIFY estado ENUM('Activo','Inactivo','Cerrada') NOT NULL DEFAULT 'Inactivo'");
    }

    public function down(): void
    {
        DB::table('expo')->where('estado', 'Cerrada')->update(['estado' => 'Inactivo']);
        DB::statement("ALTER TABLE expo MODIFY estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Inactivo'");
    }
};