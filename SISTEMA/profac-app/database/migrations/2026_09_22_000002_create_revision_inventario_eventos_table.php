<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_inventario_eventos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flujo_id');
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('cotizacion_has_producto_id');
            $table->boolean('revisado');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamp('procesado_at');
            $table->index(['flujo_id', 'cotizacion_id', 'id'], 'rev_inv_evento_cursor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_inventario_eventos');
    }
};