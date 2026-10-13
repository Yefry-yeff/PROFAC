<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_inventario_lineas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flujo_id');
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('cotizacion_has_producto_id');
            $table->boolean('revisado')->default(false);
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->timestamp('accion_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['flujo_id', 'cotizacion_id', 'cotizacion_has_producto_id'],
                'rev_inv_linea_flujo_cot_linea_unique'
            );
            $table->index(['flujo_id', 'updated_at'], 'rev_inv_linea_flujo_updated_idx');
            $table->foreign('revisado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_inventario_lineas');
    }
};