<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expo', function (Blueprint $table) {
            $table->unsignedBigInteger('cerrada_por')->nullable()->after('fecha_fin');
            $table->timestamp('cerrada_at')->nullable()->after('cerrada_por');
            $table->text('motivo_cierre')->nullable()->after('cerrada_at');
            $table->foreign('cerrada_por')->references('id')->on('users');
        });

        Schema::table('expo_cotizacion', function (Blueprint $table) {
            $table->boolean('reapertura_autorizada')->default(false)->after('cierre_manual');
            $table->text('motivo_reapertura')->nullable()->after('reapertura_autorizada');
            $table->unsignedBigInteger('reabierto_por')->nullable()->after('motivo_reapertura');
            $table->timestamp('reabierto_at')->nullable()->after('reabierto_por');
            $table->foreign('reabierto_por')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('expo_cotizacion', function (Blueprint $table) {
            $table->dropForeign(['reabierto_por']);
            $table->dropColumn(['reapertura_autorizada', 'motivo_reapertura', 'reabierto_por', 'reabierto_at']);
        });
        Schema::table('expo', function (Blueprint $table) {
            $table->dropForeign(['cerrada_por']);
            $table->dropColumn(['cerrada_por', 'cerrada_at', 'motivo_cierre']);
        });
    }
};