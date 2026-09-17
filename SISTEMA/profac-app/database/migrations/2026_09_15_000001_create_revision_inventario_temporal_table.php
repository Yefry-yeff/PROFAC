<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_inventario_temporal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('flujo_id');
            $table->unsignedBigInteger('cotizacion_id');
            $table->longText('contenido');
            $table->timestamp('expira_at');
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(
                ['usuario_id', 'flujo_id', 'cotizacion_id'],
                'rev_inv_temp_usuario_flujo_cot_unique'
            );
            $table->index('expira_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_inventario_temporal');
    }
};