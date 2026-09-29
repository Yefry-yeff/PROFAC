<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropEquipoMiembrosSnapshotTriggers extends Migration
{
    /**
     * Los equipos de entrega ya no tienen miembros fijos: el personal encargado
     * se elige manualmente por distribución (ver DistribucionEntrega::guardarDistribucion).
     * Se eliminan los triggers que copiaban automáticamente equipos_entrega_miembros
     * hacia distribuciones_entrega_miembros.
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_snapshot_miembros_distribucion');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualizar_snapshot_miembros_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualizar_snapshot_miembros_after_update');
    }

    public function down()
    {
        // No se recrean: la lógica de equipos_entrega_miembros quedó en desuso.
    }
}
