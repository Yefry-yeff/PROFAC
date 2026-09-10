<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_cotizacion_aumento_exclusion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_cotizacion_id');
            $table->integer('factura_id');
            $table->decimal('monto_exonerado', 15, 2);
            $table->unsignedBigInteger('excluido_por');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('anulada_at')->nullable();

            $table->unique(['expo_cotizacion_id', 'factura_id'], 'ecae_oferta_factura_unique');
            $table->foreign('expo_cotizacion_id')->references('id')->on('expo_cotizacion')->cascadeOnDelete();
            $table->foreign('excluido_por')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_cotizacion_aumento_exclusion');
    }
};