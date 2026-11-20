<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditoria_compras_ventas', function (Blueprint $table) {
            $table->string('tipo_movimiento', 30)->change();
            $table->unsignedInteger('comprobante_id')->nullable()->after('compra_id');
            $table->unsignedInteger('ajuste_id')->nullable()->after('comprobante_id');
            $table->string('motivo_documento', 255)->nullable()->after('estado_documento');
            $table->text('comentario_documento')->nullable()->after('motivo_documento');
            $table->boolean('afecta_inventario')->default(true)->after('cantidad_movimiento_base');

            $table->index(
                ['comprobante_id', 'producto_id'],
                'idx_auditoria_comprobante_producto'
            );
            $table->index(
                ['ajuste_id', 'producto_id'],
                'idx_auditoria_ajuste_producto'
            );
        });

        Schema::table('auditoria_compras_ventas_sync', function (Blueprint $table) {
            $table->unsignedBigInteger('filas_comprobantes')->default(0)->after('filas_ventas');
            $table->unsignedBigInteger('filas_ajustes')->default(0)->after('filas_comprobantes');
        });
    }

    public function down(): void
    {
        Schema::table('auditoria_compras_ventas_sync', function (Blueprint $table) {
            $table->dropColumn(['filas_comprobantes', 'filas_ajustes']);
        });

        Schema::table('auditoria_compras_ventas', function (Blueprint $table) {
            $table->dropIndex('idx_auditoria_comprobante_producto');
            $table->dropIndex('idx_auditoria_ajuste_producto');
            $table->dropColumn([
                'comprobante_id',
                'ajuste_id',
                'motivo_documento',
                'comentario_documento',
                'afecta_inventario',
            ]);
            $table->string('tipo_movimiento', 10)->change();
        });
    }
};
