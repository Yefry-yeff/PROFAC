<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditoria_compras_ventas', function (Blueprint $table) {
            $table->unsignedInteger('nota_credito_id')->nullable()->after('ajuste_id');
            $table->index(
                ['nota_credito_id', 'producto_id'],
                'idx_auditoria_nota_credito_producto'
            );
        });

        Schema::table('auditoria_compras_ventas_sync', function (Blueprint $table) {
            $table->unsignedBigInteger('filas_notas_credito')->default(0)->after('filas_ajustes');
        });
    }

    public function down(): void
    {
        Schema::table('auditoria_compras_ventas_sync', function (Blueprint $table) {
            $table->dropColumn('filas_notas_credito');
        });

        Schema::table('auditoria_compras_ventas', function (Blueprint $table) {
            $table->dropIndex('idx_auditoria_nota_credito_producto');
            $table->dropColumn('nota_credito_id');
        });
    }
};
