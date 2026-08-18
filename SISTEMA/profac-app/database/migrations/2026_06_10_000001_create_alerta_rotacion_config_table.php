<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('alerta_rotacion_config')) {
            Schema::create('alerta_rotacion_config', function (Blueprint $table) {
                $table->increments('id');

                // Identificador legible de la regla
                $table->string('nombre', 120)->comment('Nombre descriptivo de la alerta');

                // Categoría de alerta
                $table->enum('tipo', [
                    'recuperacion_proxima',
                    'recuperacion_vencida',
                    'sin_ventas',
                    'baja_rotacion',
                    'sobreinventario',
                    'incremento_demanda',
                ])->comment('Tipo de alerta de inventario');

                // Parámetro numérico principal:
                //   recuperacion_proxima → días de aviso previo (ej: 15, 7, 1)
                //   sin_ventas          → días sin movimiento (ej: 30, 60, 90)
                //   sobreinventario     → meses de cobertura máxima (ej: 6)
                //   baja_rotacion       → unidades vendidas mínimas en 60d (ej: 5)
                //   incremento_demanda  → % de crecimiento mínimo (ej: 40)
                //   recuperacion_vencida → null (sin parámetro)
                $table->integer('parametro_dias')->nullable()
                    ->comment('Días (aviso previo o sin ventas)');
                $table->decimal('parametro_umbral', 8, 2)->nullable()
                    ->comment('Umbral (meses cobertura / % crecimiento / ventas mínimas)');

                // Destino de la notificación
                $table->integer('rol_id')->nullable()->index()
                    ->comment('Notificar a todos los usuarios de este rol');
                $table->integer('area_id')->nullable()->index()
                    ->comment('Notificar a todos los usuarios de esta área');

                // Apariencia en la campana
                $table->string('icono', 40)->default('fa-exclamation-triangle');
                $table->string('color', 20)->default('#f59e0b');
                $table->enum('prioridad', ['informativa', 'media', 'alta', 'critica'])->default('media');

                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        // Reglas predeterminadas
        $now = now();

        $reglas = [
            // ── Recuperación próxima ────────────────────────────────────────────
            [
                'nombre'           => 'Recuperación próxima — 15 días',
                'tipo'             => 'recuperacion_proxima',
                'parametro_dias'   => 15,
                'parametro_umbral' => null,
                'rol_id'           => 1,   // Administrador
                'area_id'          => null,
                'icono'            => 'fa-clock-o',
                'color'            => '#3b82f6',
                'prioridad'        => 'media',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'nombre'           => 'Recuperación próxima — 7 días',
                'tipo'             => 'recuperacion_proxima',
                'parametro_dias'   => 7,
                'parametro_umbral' => null,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-clock-o',
                'color'            => '#f59e0b',
                'prioridad'        => 'alta',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'nombre'           => 'Recuperación próxima — 1 día',
                'tipo'             => 'recuperacion_proxima',
                'parametro_dias'   => 1,
                'parametro_umbral' => null,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-exclamation-circle',
                'color'            => '#ef4444',
                'prioridad'        => 'critica',
                'activo'           => false,
            ],
            [
                'nombre'           => 'Recuperación vencida con stock activo',
                'tipo'             => 'recuperacion_vencida',
                'parametro_dias'   => null,
                'parametro_umbral' => null,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-calendar-times-o',
                'color'            => '#dc2626',
                'prioridad'        => 'alta',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // ── Sin ventas recientes ────────────────────────────────────────────
            [
                'nombre'           => 'Sin ventas — 30 días',
                'tipo'             => 'sin_ventas',
                'parametro_dias'   => 30,
                'parametro_umbral' => null,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-shopping-cart',
                'color'            => '#6366f1',
                'prioridad'        => 'media',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'nombre'           => 'Sin ventas — 60 días',
                'tipo'             => 'sin_ventas',
                'parametro_dias'   => 60,
                'parametro_umbral' => null,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-shopping-cart',
                'color'            => '#f59e0b',
                'prioridad'        => 'alta',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'nombre'           => 'Sin ventas — 90 días',
                'tipo'             => 'sin_ventas',
                'parametro_dias'   => 90,
                'parametro_umbral' => null,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-ban',
                'color'            => '#ef4444',
                'prioridad'        => 'critica',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // ── Baja rotación ───────────────────────────────────────────────────
            [
                'nombre'           => 'Baja rotación (< 5 ventas en 60 días)',
                'tipo'             => 'baja_rotacion',
                'parametro_dias'   => null,
                'parametro_umbral' => 5,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-arrow-down',
                'color'            => '#a855f7',
                'prioridad'        => 'media',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // ── Sobreinventario ─────────────────────────────────────────────────
            [
                'nombre'           => 'Sobreinventario (cobertura > 6 meses)',
                'tipo'             => 'sobreinventario',
                'parametro_dias'   => null,
                'parametro_umbral' => 6,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-archive',
                'color'            => '#06b6d4',
                'prioridad'        => 'media',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // ── Incremento de demanda ───────────────────────────────────────────
            [
                'nombre'           => 'Incremento de demanda (≥ 40%)',
                'tipo'             => 'incremento_demanda',
                'parametro_dias'   => null,
                'parametro_umbral' => 40,
                'rol_id'           => 1,
                'area_id'          => null,
                'icono'            => 'fa-line-chart',
                'color'            => '#22c55e',
                'prioridad'        => 'informativa',
                'activo'           => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        DB::table('alerta_rotacion_config')->insert($reglas);
    }

    public function down(): void
    {
        Schema::dropIfExists('alerta_rotacion_config');
    }
};
