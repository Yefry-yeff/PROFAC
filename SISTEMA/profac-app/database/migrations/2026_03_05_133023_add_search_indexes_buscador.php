<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $this->addIndex('producto',           'idx_p_nombre', 'ALTER TABLE producto ADD INDEX idx_p_nombre (nombre(80))');
        $this->addIndex('producto',           'idx_p_codbar', 'ALTER TABLE producto ADD INDEX idx_p_codbar (codigo_barra(40))');
        $this->addIndex('producto',           'idx_p_codest', 'ALTER TABLE producto ADD INDEX idx_p_codest (codigo_estatal(40))');
        $this->addIndex('recibido_bodega',    'idx_rb_prod',  'ALTER TABLE recibido_bodega ADD INDEX idx_rb_prod (producto_id)');
        $this->addIndex('img_producto',       'idx_img_prod', 'ALTER TABLE img_producto ADD INDEX idx_img_prod (producto_id)');
        $this->addIndex('venta_has_producto', 'idx_vhp_prod', 'ALTER TABLE venta_has_producto ADD INDEX idx_vhp_prod (producto_id)');
    }

    public function down()
    {
        foreach ([
            'producto'           => ['idx_p_nombre', 'idx_p_codbar', 'idx_p_codest'],
            'recibido_bodega'    => ['idx_rb_prod'],
            'img_producto'       => ['idx_img_prod'],
            'venta_has_producto' => ['idx_vhp_prod'],
        ] as $table => $idxs) {
            foreach ($idxs as $idx) {
                $this->dropIndex($table, $idx);
            }
        }
    }

    private function addIndex(string $table, string $name, string $sql): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->contains($name);
        if (!$exists) {
            DB::statement($sql);
        }
    }

    private function dropIndex(string $table, string $name): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->contains($name);
        if ($exists) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
        }
    }
};
