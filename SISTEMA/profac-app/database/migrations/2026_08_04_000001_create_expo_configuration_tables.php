<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tipo_venta')->updateOrInsert(
            ['descripcion' => 'Expo'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        Schema::create('expo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Inactivo');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->unsignedBigInteger('expo_anterior_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('expo_anterior_id')->references('id')->on('expo')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        DB::statement("ALTER TABLE expo ADD activa_unica TINYINT GENERATED ALWAYS AS (CASE WHEN estado = 'Activo' THEN 1 ELSE NULL END) STORED");
        DB::statement('CREATE UNIQUE INDEX expo_solo_una_activa ON expo (activa_unica)');

        Schema::create('expo_bodega', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->integer('bodega_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['expo_id', 'bodega_id']);
            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
            $table->foreign('bodega_id')->references('id')->on('bodega');
        });

        Schema::create('expo_escala', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->integer('escala_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['expo_id', 'escala_id']);
            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
            $table->foreign('escala_id')->references('id')->on('categoria_precios');
        });

        Schema::create('expo_descuento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->decimal('venta_minima', 15, 2);
            $table->decimal('porcentaje_descuento', 5, 2);
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();

            $table->unique(['expo_id', 'venta_minima']);
            $table->foreign('expo_id')->references('id')->on('expo')->cascadeOnDelete();
        });

        Schema::create('expo_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expo_id');
            $table->integer('cotizacion_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->unique('cotizacion_id');
            $table->foreign('expo_id')->references('id')->on('expo');
            $table->foreign('cotizacion_id')->references('id')->on('cotizacion')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_cotizacion');
        Schema::dropIfExists('expo_descuento');
        Schema::dropIfExists('expo_escala');
        Schema::dropIfExists('expo_bodega');
        Schema::dropIfExists('expo');

        DB::table('tipo_venta')
            ->whereRaw('LOWER(TRIM(descripcion)) = ?', ['expo'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('cotizacion')
                    ->whereColumn('cotizacion.tipo_venta_id', 'tipo_venta.id');
            })
            ->delete();
    }
};