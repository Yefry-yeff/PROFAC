<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_descuento_marca', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->integer('marca_id');
            $table->decimal('venta_minima', 15, 2);
            $table->decimal('porcentaje_descuento', 5, 2);
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();

            $table->unique(['expo_id', 'marca_id']);
            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
            $table->foreign('marca_id')->references('id')->on('marca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_descuento_marca');
    }
};