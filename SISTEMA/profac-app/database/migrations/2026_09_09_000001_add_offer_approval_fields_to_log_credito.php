<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_credito', function (Blueprint $table) {
            $table->string('tipo', 50)->nullable()->after('descripcion');
            $table->unsignedBigInteger('flujo_id')->nullable()->after('factura_id');
            $table->unsignedBigInteger('cotizacion_id')->nullable()->after('flujo_id');
            $table->decimal('saldo_anterior', 60, 2)->nullable()->after('cotizacion_id');
            $table->decimal('saldo_resultante', 60, 2)->nullable()->after('saldo_anterior');

            $table->index(['cliente_id', 'created_at'], 'log_credito_cliente_fecha_idx');
            $table->index('flujo_id', 'log_credito_flujo_idx');
            $table->index('cotizacion_id', 'log_credito_cotizacion_idx');
        });
    }

    public function down(): void
    {
        Schema::table('log_credito', function (Blueprint $table) {
            $table->dropIndex('log_credito_cliente_fecha_idx');
            $table->dropIndex('log_credito_flujo_idx');
            $table->dropIndex('log_credito_cotizacion_idx');
            $table->dropColumn([
                'tipo',
                'flujo_id',
                'cotizacion_id',
                'saldo_anterior',
                'saldo_resultante',
            ]);
        });
    }
};