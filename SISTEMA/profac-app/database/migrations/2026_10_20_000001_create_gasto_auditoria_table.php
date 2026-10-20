<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gasto_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos');
            $table->unsignedInteger('usuario_id');
            $table->string('accion', 40);
            $table->json('antes')->nullable();
            $table->json('despues');
            $table->string('motivo', 1000)->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_auditoria');
    }
};
