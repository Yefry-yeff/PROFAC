<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flujo_id')->nullable()->index();
            $table->unsignedBigInteger('cotizacion_id')->index();
            $table->unsignedBigInteger('pedido_id')->nullable()->index();
            $table->tinyInteger('ganadora')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('flujo_id')->references('id')->on('flujo')->nullOnDelete();
            $table->foreign('cotizacion_id')->references('id')->on('cotizacion')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas_detalle');
    }
};
