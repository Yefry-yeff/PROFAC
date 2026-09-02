<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_importaciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('identificador')->unique();
            $table->unsignedBigInteger('users_id')->index();
            $table->string('archivo_nombre', 255);
            $table->unsignedInteger('registros_importados')->default(0);
            $table->unsignedInteger('registros_seleccionados')->default(0);
            $table->unsignedInteger('cantidad_creada')->default(0);
            $table->unsignedInteger('cantidad_rechazada')->default(0);
            $table->timestamps();
        });

        Schema::create('producto_importacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_importacion_id')->index();
            $table->unsignedInteger('fila_excel');
            $table->string('estado', 20);
            $table->integer('producto_id')->nullable()->index();
            $table->string('codigo_barra', 100)->nullable();
            $table->string('nombre', 1000)->nullable();
            $table->json('datos');
            $table->json('errores')->nullable();
            $table->timestamps();
        });

        Schema::table('producto', function (Blueprint $table) {
            $table->boolean('creado_masivamente')->default(false)->after('origen')->index();
            $table->unsignedBigInteger('producto_importacion_id')->nullable()->after('creado_masivamente')->index();
        });
    }

    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn(['creado_masivamente', 'producto_importacion_id']);
        });
        Schema::dropIfExists('producto_importacion_detalles');
        Schema::dropIfExists('producto_importaciones');
    }
};