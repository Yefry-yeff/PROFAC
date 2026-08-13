<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE cotizacion_has_producto '
            . 'ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, '
            . 'ADD UNIQUE INDEX chp_linea_id_unique (id)'
        );

        Schema::table('prefactura_has_producto', function (Blueprint $table) {
            $table->unsignedBigInteger('cotizacion_has_producto_id')
                ->nullable()
                ->after('prefactura_id')
                ->index('php_cotizacion_linea_idx');
        });

        Schema::table('venta_has_producto', function (Blueprint $table) {
            $table->unsignedBigInteger('cotizacion_has_producto_id')
                ->nullable()
                ->after('factura_id')
                ->index('vhp_cotizacion_linea_idx');
        });
    }

    public function down(): void
    {
        Schema::table('prefactura_has_producto', function (Blueprint $table) {
            $table->dropIndex('php_cotizacion_linea_idx');
            $table->dropColumn('cotizacion_has_producto_id');
        });

        Schema::table('venta_has_producto', function (Blueprint $table) {
            $table->dropIndex('vhp_cotizacion_linea_idx');
            $table->dropColumn('cotizacion_has_producto_id');
        });

        DB::statement('ALTER TABLE cotizacion_has_producto DROP COLUMN id');
    }
};