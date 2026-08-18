<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_credito_asientos', function (Blueprint $table) {
            $table->id();
            $table->integer('nota_credito_id');
            $table->unsignedBigInteger('movimiento_id')->nullable()->unique();
            $table->string('tipo', 30);
            $table->date('fecha');
            $table->string('descripcion', 255);
            $table->unsignedBigInteger('users_id');
            $table->timestamps();

            $table->foreign('nota_credito_id')->references('id')->on('nota_credito');
            $table->foreign('movimiento_id')->references('id')->on('nota_credito_movimientos');
            $table->foreign('users_id')->references('id')->on('users');
            $table->index(['nota_credito_id', 'tipo']);
            $table->index(['fecha', 'tipo']);
        });

        Schema::create('nota_credito_asiento_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asiento_id');
            $table->string('cuenta_codigo', 50);
            $table->string('cuenta_nombre', 150);
            $table->decimal('debe', 18, 2)->default(0);
            $table->decimal('haber', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('asiento_id')->references('id')->on('nota_credito_asientos')->onDelete('cascade');
            $table->index(['cuenta_codigo', 'asiento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_credito_asiento_detalles');
        Schema::dropIfExists('nota_credito_asientos');
    }
};