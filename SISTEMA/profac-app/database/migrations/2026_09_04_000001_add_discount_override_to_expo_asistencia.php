<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expo_asistencia', function (Blueprint $table) {
            $table->string('descuento_modo', 20)->default('automatico')->after('comentario');
            $table->unsignedSmallInteger('descuento_escalon')->nullable()->after('descuento_modo');
            $table->unsignedBigInteger('descuento_asignado_por')->nullable()->after('descuento_escalon');
            $table->timestamp('descuento_asignado_at')->nullable()->after('descuento_asignado_por');

            $table->foreign('descuento_asignado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expo_asistencia', function (Blueprint $table) {
            $table->dropForeign(['descuento_asignado_por']);
            $table->dropColumn([
                'descuento_modo',
                'descuento_escalon',
                'descuento_asignado_por',
                'descuento_asignado_at',
            ]);
        });
    }
};