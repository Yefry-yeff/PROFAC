<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expo_asistencia', function (Blueprint $table) {
            $table->string('comentario', 1000)->nullable()->after('recibio_regalo');
        });
    }

    public function down(): void
    {
        Schema::table('expo_asistencia', function (Blueprint $table) {
            $table->dropColumn('comentario');
        });
    }
};