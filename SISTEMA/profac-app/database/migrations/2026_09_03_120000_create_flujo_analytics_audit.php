<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flujo_analitica_umbrales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_tramite_id')->unique();
            $table->unsignedInteger('normal_minutos');
            $table->unsignedInteger('advertencia_minutos');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('flujo_analitica_umbrales')->insert([
            ['tipo_tramite_id' => 1, 'normal_minutos' => 30, 'advertencia_minutos' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 2, 'normal_minutos' => 30, 'advertencia_minutos' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 9, 'normal_minutos' => 30, 'advertencia_minutos' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 10, 'normal_minutos' => 60, 'advertencia_minutos' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 4, 'normal_minutos' => 30, 'advertencia_minutos' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 3, 'normal_minutos' => 60, 'advertencia_minutos' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 5, 'normal_minutos' => 120, 'advertencia_minutos' => 240, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 6, 'normal_minutos' => 1440, 'advertencia_minutos' => 4320, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 7, 'normal_minutos' => 240, 'advertencia_minutos' => 480, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 8, 'normal_minutos' => 1, 'advertencia_minutos' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('flujo_auditoria_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flujo_id');
            $table->unsignedBigInteger('historico_flujo_id')->nullable();
            $table->string('entidad_tipo', 60);
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->unsignedBigInteger('tipo_tramite_id')->nullable();
            $table->string('estado_anterior', 80)->nullable();
            $table->string('estado_nuevo', 80)->nullable();
            $table->dateTime('fecha_hora_entrada')->nullable();
            $table->dateTime('fecha_hora_salida')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('accion', 100);
            $table->text('observacion')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['flujo_id', 'created_at'], 'fae_flujo_fecha_idx');
            $table->index(['tipo_tramite_id', 'created_at'], 'fae_etapa_fecha_idx');
            $table->index(['usuario_id', 'created_at'], 'fae_usuario_fecha_idx');
            $table->index('historico_flujo_id', 'fae_historico_idx');
        });

        DB::statement("INSERT INTO flujo_auditoria_eventos
            (flujo_id, historico_flujo_id, entidad_tipo, entidad_id, tipo_tramite_id,
                  estado_nuevo, fecha_hora_entrada, fecha_hora_salida, usuario_id, area_id, accion,
             observacion, created_at)
            SELECT hf.flujo_id, hf.id, 'historico_flujo', hf.tramite_id, hf.tipo_tramite_id,
                   CAST(hf.estado_id AS CHAR), hf.created_at,
                   CASE WHEN hf.updated_at > hf.created_at THEN hf.updated_at ELSE NULL END,
                     hf.created_by, hf.tipo_tramite_id, 'reconstruccion_historica', hf.observaciones, hf.created_at
            FROM historico_flujo hf");

        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("CREATE TRIGGER trg_historico_flujo_analytics_insert
                AFTER INSERT ON historico_flujo FOR EACH ROW
                INSERT INTO flujo_auditoria_eventos
                    (flujo_id, historico_flujo_id, entidad_tipo, entidad_id, tipo_tramite_id,
                     estado_nuevo, fecha_hora_entrada, fecha_hora_salida, usuario_id, area_id, accion,
                     observacion, payload, created_at)
                VALUES
                    (NEW.flujo_id, NEW.id, 'historico_flujo', NEW.tramite_id, NEW.tipo_tramite_id,
                     CAST(NEW.estado_id AS CHAR), NEW.created_at,
                     IF(NEW.updated_at > NEW.created_at, NEW.updated_at, NULL), NEW.created_by, NEW.tipo_tramite_id,
                     'registro_evento', NEW.observaciones,
                     JSON_OBJECT('created_by', NEW.created_by, 'updated_by', NEW.updated_by), NOW())");

            DB::unprepared("CREATE TRIGGER trg_historico_flujo_analytics_update
                AFTER UPDATE ON historico_flujo FOR EACH ROW
                INSERT INTO flujo_auditoria_eventos
                    (flujo_id, historico_flujo_id, entidad_tipo, entidad_id, tipo_tramite_id,
                     estado_anterior, estado_nuevo, fecha_hora_entrada, fecha_hora_salida,
                     usuario_id, area_id, accion, observacion, payload, created_at)
                VALUES
                    (NEW.flujo_id, NEW.id, 'historico_flujo', NEW.tramite_id, NEW.tipo_tramite_id,
                     CAST(OLD.estado_id AS CHAR), CAST(NEW.estado_id AS CHAR), OLD.created_at,
                     NEW.updated_at, COALESCE(NEW.updated_by, NEW.created_by), NEW.tipo_tramite_id, 'actualizacion_evento',
                     NEW.observaciones,
                     JSON_OBJECT('tipo_tramite_anterior', OLD.tipo_tramite_id,
                                 'tipo_tramite_nuevo', NEW.tipo_tramite_id,
                                 'observacion_anterior', OLD.observaciones), NOW())");

            DB::unprepared("CREATE TRIGGER trg_flujo_analytics_update
                AFTER UPDATE ON flujo FOR EACH ROW
                INSERT INTO flujo_auditoria_eventos
                    (flujo_id, entidad_tipo, entidad_id, tipo_tramite_id, estado_anterior,
                     estado_nuevo, fecha_hora_entrada, fecha_hora_salida, usuario_id, accion,
                     area_id, observacion, payload, created_at)
                SELECT NEW.id, 'flujo', NEW.id, NEW.tipo_tramite_id,
                       CONCAT(COALESCE(OLD.tipo_tramite_id, ''), ':', COALESCE(OLD.estado_id, '')),
                       CONCAT(COALESCE(NEW.tipo_tramite_id, ''), ':', COALESCE(NEW.estado_id, '')),
                       OLD.updated_at, NEW.updated_at, COALESCE(NEW.updated_by, NEW.created_by),
                       'cambio_estado_flujo', NEW.tipo_tramite_id, 'Cambio directo en etapa o estado del flujo',
                       JSON_OBJECT('etapa_anterior', OLD.tipo_tramite_id, 'etapa_nueva', NEW.tipo_tramite_id,
                                   'estado_anterior', OLD.estado_id, 'estado_nuevo', NEW.estado_id), NOW()
                WHERE NOT (OLD.tipo_tramite_id <=> NEW.tipo_tramite_id)
                   OR NOT (OLD.estado_id <=> NEW.estado_id)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_flujo_analytics_update');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_historico_flujo_analytics_update');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_historico_flujo_analytics_insert');
        }
        Schema::dropIfExists('flujo_auditoria_eventos');
        Schema::dropIfExists('flujo_analitica_umbrales');
    }
};