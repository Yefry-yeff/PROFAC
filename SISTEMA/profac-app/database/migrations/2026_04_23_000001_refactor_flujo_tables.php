<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cambios aplicados:
     *  - cotizacion      : drop pedido_id · add estado_id (FK→estado_venta) · add created_by/updated_by (FK→users)
     *  - tipos_estatus   : add estado_id (FK→estado_venta) · add FK created_by/updated_by (→users) · RENAME → tipos_tramites
     *  - flujo           : rename estatus_id → tipo_tramite_id (FK→tipos_tramites) · add estado_id (FK→estado_venta) · add FK created_by/updated_by (→users)
     *  - historico_flujo : add tipo_tramite_id (FK→tipos_tramites) · add estado_id (FK→estado_venta) · add FK created_by/updated_by (→users)
     */
    public function up(): void
    {
        // ── 1. tipos_estatus: agregar/corregir estado_id (INT, para coincidir con estado_venta.id INT(11)) + FK ──
        if (!Schema::hasColumn('tipos_estatus', 'estado_id')) {
            DB::statement('ALTER TABLE tipos_estatus ADD COLUMN estado_id INT NULL AFTER updated_by');
        } else {
            // La columna pudo haberse creado como BIGINT UNSIGNED en un intento previo; normalizar a INT
            DB::statement('ALTER TABLE tipos_estatus CHANGE COLUMN estado_id estado_id INT NULL');
        }
        Schema::table('tipos_estatus', function (Blueprint $table) {
            $table->foreign('estado_id')->references('id')->on('estado_venta')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 2. Renombrar tipos_estatus → tipos_tramites ──────────────────────────────
        //    MySQL actualiza automáticamente las FK que apuntan a esta tabla.
        DB::statement('RENAME TABLE tipos_estatus TO tipos_tramites');

        // ── 3. flujo: soltar FK de estatus_id, renombrar columna, reestablecer FK ────
        Schema::table('flujo', function (Blueprint $table) {
            $table->dropForeign('flujo_estatus_id_foreign');
        });

        // CHANGE COLUMN requiere el tipo completo (MySQL 5.7, sin doctrine/dbal)
        DB::statement('ALTER TABLE flujo CHANGE COLUMN estatus_id tipo_tramite_id BIGINT UNSIGNED NOT NULL');

        Schema::table('flujo', function (Blueprint $table) {
            $table->foreign('tipo_tramite_id')->references('id')->on('tipos_tramites');
            $table->integer('estado_id')->nullable()->after('updated_by'); // INT para coincidir con estado_venta.id INT(11)
            $table->foreign('estado_id')->references('id')->on('estado_venta')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 4. historico_flujo: agregar tipo_tramite_id + estado_id + FK users ───────
        Schema::table('historico_flujo', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_tramite_id')->nullable()->after('tramite_id');
            $table->foreign('tipo_tramite_id')->references('id')->on('tipos_tramites')->nullOnDelete();
            $table->integer('estado_id')->nullable()->after('updated_by'); // INT para coincidir con estado_venta.id INT(11)
            $table->foreign('estado_id')->references('id')->on('estado_venta')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 5. cotizacion: eliminar pedido_id, agregar estado_id + created_by/updated_by ──
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->dropColumn('pedido_id');
        });

        Schema::table('cotizacion', function (Blueprint $table) {
            $table->integer('estado_id')->nullable()->after('nota'); // INT para coincidir con estado_venta.id INT(11)
            $table->unsignedBigInteger('created_by')->nullable()->after('estado_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->foreign('estado_id')->references('id')->on('estado_venta')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ── cotizacion: revertir ─────────────────────────────────────────────────────
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->dropForeign(['estado_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['estado_id', 'created_by', 'updated_by']);
            $table->unsignedBigInteger('pedido_id')->nullable()->index();
        });

        // ── historico_flujo: revertir ────────────────────────────────────────────────
        Schema::table('historico_flujo', function (Blueprint $table) {
            $table->dropForeign(['tipo_tramite_id']);
            $table->dropForeign(['estado_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['tipo_tramite_id', 'estado_id']);
        });

        // ── flujo: revertir ──────────────────────────────────────────────────────────
        Schema::table('flujo', function (Blueprint $table) {
            $table->dropForeign(['tipo_tramite_id']);
            $table->dropForeign(['estado_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn('estado_id');
        });

        DB::statement('ALTER TABLE flujo CHANGE COLUMN tipo_tramite_id estatus_id BIGINT UNSIGNED NOT NULL');

        Schema::table('flujo', function (Blueprint $table) {
            $table->foreign('estatus_id')->references('id')->on('tipos_tramites');
        });

        // ── Renombrar tipos_tramites → tipos_estatus ─────────────────────────────────
        DB::statement('RENAME TABLE tipos_tramites TO tipos_estatus');

        Schema::table('tipos_estatus', function (Blueprint $table) {
            $table->dropForeign(['estado_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn('estado_id');
        });

        // Re-apuntar FK de flujo.estatus_id a tipos_estatus
        Schema::table('flujo', function (Blueprint $table) {
            $table->dropForeign(['estatus_id']);
            $table->foreign('estatus_id')->references('id')->on('tipos_estatus');
        });
    }
};
