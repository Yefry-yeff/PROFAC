<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('revision_inventario_temporal')
            || !Schema::hasColumn('revision_inventario_temporal', 'id')) {
            return;
        }

        $primary = DB::selectOne("SELECT COUNT(*) AS total
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'revision_inventario_temporal'
              AND index_name = 'PRIMARY'");

        if ((int) ($primary->total ?? 0) === 0) {
            DB::statement('ALTER TABLE revision_inventario_temporal ADD PRIMARY KEY (`id`)');
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM revision_inventario_temporal WHERE Field = 'id'");
        if (stripos((string) ($idColumn->Extra ?? ''), 'auto_increment') === false) {
            DB::statement('ALTER TABLE revision_inventario_temporal MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
    }
};