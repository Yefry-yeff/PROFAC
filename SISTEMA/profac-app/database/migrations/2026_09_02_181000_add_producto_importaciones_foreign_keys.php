<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_importaciones', function (Blueprint $table) {
            $table->foreign('users_id')->references('id')->on('users')->restrictOnDelete();
        });
        Schema::table('producto_importacion_detalles', function (Blueprint $table) {
            $table->foreign('producto_importacion_id')->references('id')->on('producto_importaciones')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('producto')->nullOnDelete();
        });
        Schema::table('producto', function (Blueprint $table) {
            $table->foreign('producto_importacion_id')->references('id')->on('producto_importaciones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropForeign(['producto_importacion_id']);
        });
        Schema::table('producto_importacion_detalles', function (Blueprint $table) {
            $table->dropForeign(['producto_importacion_id']);
            $table->dropForeign(['producto_id']);
        });
        Schema::table('producto_importaciones', function (Blueprint $table) {
            $table->dropForeign(['users_id']);
        });
    }
};