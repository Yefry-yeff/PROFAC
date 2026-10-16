<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $trigger = DB::selectOne('SHOW CREATE TRIGGER trg_after_insert_log_translado_unificado');
        if (!$trigger || !isset($trigger->{'SQL Original Statement'})) {
            throw new RuntimeException('No se encontro la definicion del trigger del kardex.');
        }

        $original = $trigger->{'SQL Original Statement'};
        $definition = preg_replace_callback(
            "~IF NEW\.descripcion = 'Factura Anulada' AND NEW\.destino IS NOT NULL THEN.*?END IF;~s",
            function (array $match): string {
                $old = "CONCAT(NEW.descripcion, ' (-)')";
                $new = "CONCAT(NEW.descripcion, ' (+)')";
                if (substr_count($match[0], $old) === 1 && !str_contains($match[0], $new)) {
                    return str_replace($old, $new, $match[0]);
                }
                if (substr_count($match[0], $new) === 1 && !str_contains($match[0], $old)) {
                    return $match[0];
                }
                throw new RuntimeException('La descripcion de anulacion del trigger tiene un formato inesperado.');
            },
            $original,
            -1,
            $count
        );

        if ($definition === null || $count !== 1) {
            throw new RuntimeException('No se pudo identificar una unica rama de factura anulada en el trigger.');
        }

        if ($definition !== $original) {
            $session = DB::selectOne('SELECT @@SESSION.sql_mode AS sql_mode');
            DB::statement('SET SESSION sql_mode = ?', [$trigger->sql_mode]);
            try {
                DB::unprepared('DROP TRIGGER `trg_after_insert_log_translado_unificado`');
                try {
                    DB::unprepared($definition);
                } catch (Throwable $error) {
                    // MySQL no revierte el DDL: restaurar el trigger si falla su reemplazo.
                    DB::unprepared($original);
                    throw $error;
                }
            } finally {
                DB::statement('SET SESSION sql_mode = ?', [$session->sql_mode]);
            }
        }

        DB::table('cardex')
            ->where('descripcion', 'Factura Anulada (-)')
            ->update(['descripcion' => 'Factura Anulada (+)']);
    }

    public function down(): void
    {
        throw new RuntimeException('Esta correccion no se revierte: una factura anulada restituye inventario (+).');
    }
};
