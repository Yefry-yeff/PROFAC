<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportingIndexesComisionesGenerales extends Migration
{
    public function up()
    {
        // Base mensual del consolidado de nómina (empleado + mes + rol)
        Schema::table('comision_empleado', function (Blueprint $table) {
            $table->index(['estado_id', 'mes_comision', 'users_comision', 'rol_id'], 'idx_ce_estado_mes_user_rol');
            $table->index(['users_comision', 'mes_comision', 'estado_id'], 'idx_ce_user_mes_estado');
        });

        // Facturas comisionadas activas por rango y joins de detalle
        Schema::table('facturas_comision', function (Blueprint $table) {
            $table->index(['estado_id', 'fecha_cierre_factura', 'tipo_comision', 'rol_id'], 'idx_fc_estado_fecha_tipo_rol');
            $table->index(['factura_id', 'estado_id'], 'idx_fc_factura_estado');
        });

        // Detalle por línea de comisión original en modal de nómina
        Schema::table('producto_comision', function (Blueprint $table) {
            $table->index(['facturas_comision_id', 'estado_id'], 'idx_pc_fc_estado');
        });

        // KPIs de retención por mora del período
        Schema::table('retencion_mora_log', function (Blueprint $table) {
            $table->index(['fecha_aplicacion', 'user_id'], 'idx_rml_fecha_user');
            $table->index(['facturas_comision_id', 'factura_id'], 'idx_rml_fc_factura');
        });

        // KPI y auditoría de reversas por rango temporal
        Schema::table('comision_reversiones', function (Blueprint $table) {
            $table->index(['created_at', 'factura_id'], 'idx_cr_created_factura');
        });
    }

    public function down()
    {
        Schema::table('comision_reversiones', function (Blueprint $table) {
            $table->dropIndex('idx_cr_created_factura');
        });

        Schema::table('retencion_mora_log', function (Blueprint $table) {
            $table->dropIndex('idx_rml_fecha_user');
            $table->dropIndex('idx_rml_fc_factura');
        });

        Schema::table('producto_comision', function (Blueprint $table) {
            $table->dropIndex('idx_pc_fc_estado');
        });

        Schema::table('facturas_comision', function (Blueprint $table) {
            $table->dropIndex('idx_fc_estado_fecha_tipo_rol');
            $table->dropIndex('idx_fc_factura_estado');
        });

        Schema::table('comision_empleado', function (Blueprint $table) {
            $table->dropIndex('idx_ce_estado_mes_user_rol');
            $table->dropIndex('idx_ce_user_mes_estado');
        });
    }
}
