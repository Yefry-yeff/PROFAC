<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['expo_id', 'usuario_id']);
            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_usuario');
    }
};