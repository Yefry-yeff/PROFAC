<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddZoneGroupToFacturaTratamientoEntregaTable extends Migration
{
    /**
     * Permite registrar el "envío" de una factura (Actores de la Factura /
     * Gestor de Entrega) seleccionando directamente una zona de Agrupaciones
     * de Entregas, en lugar de depto/municipio (flujo de "Gestión de
     * Facturas"). department_id/municipality_id/direccion_entrega pasan a
     * ser opcionales porque ese flujo ya no aplica cuando se guarda por zona.
     */
    public function up()
    {
        if (!Schema::hasColumn('factura_tratamiento_entrega', 'zone_group_id')) {
            Schema::table('factura_tratamiento_entrega', function (Blueprint $table) {
                $table->unsignedBigInteger('zone_group_id')->nullable()->after('factura_id');
                $table->index('zone_group_id', 'idx_fte_zone_group');
            });
        }

        DB::statement('ALTER TABLE factura_tratamiento_entrega MODIFY department_id INT NULL');
        DB::statement('ALTER TABLE factura_tratamiento_entrega MODIFY municipality_id INT NULL');
        DB::statement('ALTER TABLE factura_tratamiento_entrega MODIFY direccion_entrega VARCHAR(255) NULL');
    }

    public function down()
    {
        if (Schema::hasColumn('factura_tratamiento_entrega', 'zone_group_id')) {
            Schema::table('factura_tratamiento_entrega', function (Blueprint $table) {
                $table->dropIndex('idx_fte_zone_group');
                $table->dropColumn('zone_group_id');
            });
        }
    }
}
