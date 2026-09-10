<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_descuento_escala', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->integer('escala_id');
            $table->decimal('venta_minima', 15, 2);
            $table->decimal('porcentaje_descuento', 5, 2);
            $table->boolean('requiere_asistencia')->default(false);
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();

            $table->unique(['expo_id', 'escala_id', 'venta_minima'], 'expo_descuento_escala_unique');
            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
            $table->foreign('escala_id')->references('id')->on('categoria_precios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_descuento_escala');
    }
};
