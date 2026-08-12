<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_temporal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo', ['oferta', 'factura']);
            $table->string('codigo_tipo', 80);
            $table->string('titulo', 180)->nullable();
            $table->text('url_reanudacion');
            $table->longText('contenido');
            $table->timestamp('expira_at');
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['usuario_id', 'tipo', 'expira_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_temporal');
    }
};