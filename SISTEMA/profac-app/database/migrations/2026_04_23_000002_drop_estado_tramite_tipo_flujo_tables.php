<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cambios:
     *  - flujo           : DROP columna estado
     *  - historico_flujo : DROP columna tramite_tipo · DROP columna estado (reemplazada por estado_id FK→estado_venta)
     *  - estado_venta    : agregar registros faltantes: pendiente, activa, cancelado, pre_factura
     *
     * Antes de eliminar las columnas se migran los datos a estado_id.
     */
    public function up(): void
    {
        // ── 1. Crear registros en estado_venta que aún no existen ─────────────────
        $nuevos = ['pendiente', 'activa', 'cancelado', 'pre_factura'];
        foreach ($nuevos as $desc) {
            DB::table('estado_venta')->updateOrInsert(
                ['descripcion' => $desc],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // ── 2. Poblar flujo.estado_id desde flujo.estado ──────────────────────────
        DB::statement(
            'UPDATE flujo f
             INNER JOIN estado_venta ev ON ev.descripcion = f.estado
             SET f.estado_id = ev.id'
        );

        // ── 3. Poblar historico_flujo.estado_id desde historico_flujo.estado ──────
        DB::statement(
            'UPDATE historico_flujo hf
             INNER JOIN estado_venta ev ON ev.descripcion = hf.estado
             SET hf.estado_id = ev.id'
        );

        // ── 4. Poblar historico_flujo.tipo_tramite_id desde tramite_tipo ──────────
        //    (por si quedaron registros sin tipo_tramite_id del migration anterior)
        DB::statement(
            "UPDATE historico_flujo hf
             INNER JOIN tipos_tramites tt ON tt.nombre = hf.tramite_tipo
             SET hf.tipo_tramite_id = tt.id
             WHERE hf.tipo_tramite_id IS NULL"
        );

        // ── 5. Eliminar flujo.estado ──────────────────────────────────────────────
        Schema::table('flujo', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // ── 6. Eliminar historico_flujo.tramite_tipo ──────────────────────────────
        Schema::table('historico_flujo', function (Blueprint $table) {
            $table->dropColumn('tramite_tipo');
        });

        // ── 7. Eliminar historico_flujo.estado ────────────────────────────────────
        Schema::table('historico_flujo', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar historico_flujo.tramite_tipo y historico_flujo.estado
        Schema::table('historico_flujo', function (Blueprint $table) {
            $table->string('tramite_tipo')->nullable()->after('tramite_id');
            $table->string('estado')->nullable()->after('observaciones');
        });

        // Poblar desde los ids
        DB::statement(
            'UPDATE historico_flujo hf
             INNER JOIN estado_venta ev ON ev.id = hf.estado_id
             SET hf.estado = ev.descripcion'
        );
        DB::statement(
            'UPDATE historico_flujo hf
             INNER JOIN tipos_tramites tt ON tt.id = hf.tipo_tramite_id
             SET hf.tramite_tipo = tt.nombre'
        );

        // Restaurar flujo.estado
        Schema::table('flujo', function (Blueprint $table) {
            $table->string('estado')->nullable()->after('nombre');
        });

        DB::statement(
            'UPDATE flujo f
             INNER JOIN estado_venta ev ON ev.id = f.estado_id
             SET f.estado = ev.descripcion'
        );
    }
};
