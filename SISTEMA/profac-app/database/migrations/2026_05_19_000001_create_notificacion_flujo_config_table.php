<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla nativa de notificaciones de Laravel (DatabaseChannel)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // Tabla de configuración dinámica: qué rol/área recibe notificación por cada estado de flujo
        if (!Schema::hasTable('notificacion_flujo_config')) {
            Schema::create('notificacion_flujo_config', function (Blueprint $table) {
                $table->increments('id');

                // Estado de flujo que dispara la notificación
                $table->unsignedBigInteger('tipo_tramite_id')->index();

                // Targeting: por rol específico O por área
                $table->integer('rol_id')->nullable()->index()->comment('Notificar a este rol específico');
                $table->integer('area_id')->nullable()->index()->comment('Notificar a todos los roles de esta área');
                $table->integer('nivel_max_id')->nullable()->index()->comment('Solo notificar niveles con orden >= este nivel');

                // Escalación
                $table->boolean('escalar_activo')->default(false);
                $table->unsignedSmallInteger('escalar_horas')->nullable()->comment('Si no se lee en N horas, escalar');
                $table->integer('escalar_nivel_id')->nullable()->index()->comment('Escalar a este nivel jerárquico en la misma área');

                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_flujo_config');
        Schema::dropIfExists('notifications');
    }
};
