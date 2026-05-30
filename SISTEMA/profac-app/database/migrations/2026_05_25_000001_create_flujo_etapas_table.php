<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo de etapas del flujo de venta.
 *
 * Centraliza el orden, nombre visual e ícono de cada paso del pipeline,
 * de modo que el sistema de notificaciones y el stepper puedan consultarlo
 * sin depender de valores hardcodeados en el código.
 *
 * Relación: flujo_etapas.tipo_tramite_id → tipos_tramites.id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flujo_etapas', function (Blueprint $table) {
            $table->tinyIncrements('id');

            // Referencia al catálogo base de trámites
            $table->unsignedBigInteger('tipo_tramite_id')->unique()
                  ->comment('FK a tipos_tramites.id');

            // Etiqueta corta para UI (stepper, dropdowns, notificaciones)
            $table->string('nombre_display', 60)
                  ->comment('Nombre legible para el usuario');

            // Ícono Font Awesome (sin el prefijo "fa ")
            $table->string('icono', 60)->default('fa-circle')
                  ->comment('Clase Font Awesome, ej: fa-shopping-cart');

            // Posición en el pipeline (1 = primer paso)
            $table->tinyInteger('orden')->unsigned()
                  ->comment('Orden de aparición en el flujo');

            // Algunos pasos solo aparecen bajo ciertas condiciones
            $table->tinyInteger('es_opcional')->default(0)
                  ->comment('1 = el paso puede no existir en todos los flujos');

            // Permite desactivar un paso sin borrarlo
            $table->tinyInteger('activo')->default(1);

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('orden');
            $table->index('activo');
        });

        // ── Semilla inicial: todos los pasos actuales del flujo ──────────────
        DB::table('flujo_etapas')->insert([
            // tipo_tramite_id referenciado en tipos_tramites
            ['tipo_tramite_id' => 1,  'nombre_display' => 'Pedido',            'icono' => 'fa-shopping-cart', 'orden' => 1,  'es_opcional' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 2,  'nombre_display' => 'Ofertas',           'icono' => 'fa-tag',           'orden' => 2,  'es_opcional' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 9,  'nombre_display' => 'Rev. Inventario',   'icono' => 'fa-search',        'orden' => 3,  'es_opcional' => 1, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 10, 'nombre_display' => 'Rev. Crédito',      'icono' => 'fa-credit-card',   'orden' => 4,  'es_opcional' => 1, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 4,  'nombre_display' => 'Prefactura',        'icono' => 'fa-file-o',        'orden' => 5,  'es_opcional' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 3,  'nombre_display' => 'Factura',           'icono' => 'fa-file-text',     'orden' => 6,  'es_opcional' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 5,  'nombre_display' => 'Entrega',           'icono' => 'fa-truck',         'orden' => 7,  'es_opcional' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 6,  'nombre_display' => 'Cobro',             'icono' => 'fa-dollar',        'orden' => 8,  'es_opcional' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 7,  'nombre_display' => 'Entrega y Cobro',   'icono' => 'fa-handshake-o',   'orden' => 9,  'es_opcional' => 1, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['tipo_tramite_id' => 8,  'nombre_display' => 'Finalizado',        'icono' => 'fa-check-circle',  'orden' => 10, 'es_opcional' => 0, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('flujo_etapas');
    }
};
