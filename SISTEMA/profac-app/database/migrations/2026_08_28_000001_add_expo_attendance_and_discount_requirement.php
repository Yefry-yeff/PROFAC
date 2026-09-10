<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_asistencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->integer('cliente_id');
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();

            $table->unique(['expo_id', 'cliente_id'], 'expo_asistencia_expo_cliente_unique');
            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
            $table->foreign('cliente_id')->references('id')->on('cliente')->cascadeOnDelete();
            $table->foreign('registrado_por')->references('id')->on('users');
        });

        Schema::table('expo_descuento_marca', function (Blueprint $table) {
            $table->boolean('requiere_asistencia')->default(false)->after('porcentaje_descuento');
        });
    }

    public function down(): void
    {
        Schema::table('expo_descuento_marca', function (Blueprint $table) {
            $table->dropColumn('requiere_asistencia');
        });

        Schema::dropIfExists('expo_asistencia');
    }
};