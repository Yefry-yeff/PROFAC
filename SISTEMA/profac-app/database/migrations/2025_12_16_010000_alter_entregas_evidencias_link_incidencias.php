<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entregas_evidencias', function (Blueprint $table) {
            if (!Schema::hasColumn('entregas_evidencias', 'entrega_producto_incidencia_id')) {
                $table->unsignedBigInteger('entrega_producto_incidencia_id')->after('id');
                $table->index('entrega_producto_incidencia_id', 'idx_incidencia');
                $table->foreign('entrega_producto_incidencia_id', 'fk_evidencias_incidencia')
                    ->references('id')->on('entregas_productos_incidencias')
                    ->onDelete('cascade')->onUpdate('cascade');
            }
            // Optional: make distribucion_factura_id nullable for backward compatibility
            if (Schema::hasColumn('entregas_evidencias', 'distribucion_factura_id')) {
                $table->unsignedBigInteger('distribucion_factura_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('entregas_evidencias', function (Blueprint $table) {
            if (Schema::hasColumn('entregas_evidencias', 'entrega_producto_incidencia_id')) {
                $table->dropForeign('fk_evidencias_incidencia');
                $table->dropIndex('idx_incidencia');
                $table->dropColumn('entrega_producto_incidencia_id');
            }
            // Optional: revert distribucion_factura_id to not nullable
            if (Schema::hasColumn('entregas_evidencias', 'distribucion_factura_id')) {
                $table->unsignedBigInteger('distribucion_factura_id')->nullable(false)->change();
            }
        });
    }
};
