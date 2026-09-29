<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('equipos_entrega_zonas')) {
            Schema::create('equipos_entrega_zonas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('equipo_entrega_id');
                $table->unsignedBigInteger('zone_group_id');
                $table->decimal('volumen_minimo', 14, 2)->default(0);
                $table->decimal('volumen_maximo', 14, 2)->default(0);
                $table->decimal('monto_minimo_venta', 14, 2)->default(0);
                $table->decimal('peso_maximo', 14, 2)->default(0);
                $table->timestamps();

                $table->unique(['equipo_entrega_id', 'zone_group_id'], 'uk_equipo_entrega_zona');
                $table->foreign('equipo_entrega_id', 'fk_equipo_entrega_zona_equipo')
                    ->references('id')->on('equipos_entrega')->cascadeOnDelete();
                $table->foreign('zone_group_id', 'fk_equipo_entrega_zona_zona')
                    ->references('id')->on('zone_groups')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos_entrega_zonas');
    }
};
