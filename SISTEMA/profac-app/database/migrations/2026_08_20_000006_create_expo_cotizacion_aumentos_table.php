<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_cotizacion_aumento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_cotizacion_id');
            $table->unsignedBigInteger('otros_movimientos_id');
            $table->integer('factura_id');
            $table->decimal('monto', 15, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('disminucion_movimiento_id')->nullable();
            $table->unsignedBigInteger('revertido_por')->nullable();
            $table->timestamp('revertido_at')->nullable();

            $table->index(['expo_cotizacion_id', 'factura_id'], 'eca_expo_factura_idx');
            $table->foreign('expo_cotizacion_id')->references('id')->on('expo_cotizacion')->cascadeOnDelete();
            $table->index('otros_movimientos_id', 'eca_movimiento_idx');
            $table->unique('disminucion_movimiento_id', 'eca_disminucion_unique');
            $table->foreign('revertido_por')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_cotizacion_aumento');
    }
};