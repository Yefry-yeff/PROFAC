<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factura_interes', function (Blueprint $table) {
            // Enlace directo al abono que originó este registro de interés
            // abonos_creditos.id es INT (firmado) — usar integer para compatibilidad
            $table->integer('abonos_creditos_id')
                  ->nullable()
                  ->after('factura_id')
                  ->comment('Abono de crédito que originó este registro de interés.');

            $table->index('abonos_creditos_id', 'idx_fi_abono');

            $table->foreign('abonos_creditos_id')
                  ->references('id')
                  ->on('abonos_creditos')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('factura_interes', function (Blueprint $table) {
            $table->dropForeign('factura_interes_abonos_creditos_id_foreign');
            $table->dropIndex('idx_fi_abono');
            $table->dropColumn('abonos_creditos_id');
        });
    }
};
