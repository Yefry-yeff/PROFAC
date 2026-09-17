<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expo_asistencia', function (Blueprint $table) {
            $table->unsignedInteger('tickets')->default(0)->after('registrado_por');
            $table->boolean('recibio_regalo')->default(false)->after('tickets');
        });
    }

    public function down(): void
    {
        Schema::table('expo_asistencia', function (Blueprint $table) {
            $table->dropColumn(['tickets', 'recibio_regalo']);
        });
    }
};