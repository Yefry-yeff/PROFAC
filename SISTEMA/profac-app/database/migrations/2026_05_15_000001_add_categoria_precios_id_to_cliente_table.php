<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna categoria_precios_id a la tabla cliente.
     *
     * Esta columna almacena el tier de precio específico asignado a cada cliente
     * (ej: "Co-Distribuidor F") dentro de su categoría de cliente consolidada
     * (ej: "Co-Distribuidor").
     *
     * Después de correr esta migración, ejecutar el script SQL:
     *   MISELANIOS/sp/migracion_consolidar_categorias_clientes.sql
     */
    public function up(): void
    {
        if (!Schema::hasColumn('cliente', 'categoria_precios_id')) {
            Schema::table('cliente', function (Blueprint $table) {
                $table->integer('categoria_precios_id')->nullable()->after('cliente_categoria_escala_id');
                $table->index('categoria_precios_id', 'idx_cliente_categoria_precios');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cliente', function (Blueprint $table) {
            if (Schema::hasColumn('cliente', 'categoria_precios_id')) {
                $table->dropIndex('idx_cliente_categoria_precios');
                $table->dropColumn('categoria_precios_id');
            }
        });
    }
};
