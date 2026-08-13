<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expo_cotizacion', function (Blueprint $table) {
            $table->string('estado', 40)->default('PENDIENTE_FACTURACION')->after('created_by');
            $table->unsignedBigInteger('flujo_id')->nullable()->after('estado')->index('ec_flujo_idx');
            $table->boolean('cierre_manual')->default(false)->after('flujo_id');
            $table->text('motivo_cierre')->nullable()->after('cierre_manual');
            $table->unsignedBigInteger('cerrado_por')->nullable()->after('motivo_cierre');
            $table->timestamp('cerrado_at')->nullable()->after('cerrado_por');
            $table->json('reglas_descuento_snapshot')->nullable()->after('cerrado_at');
            $table->decimal('total_facturado', 15, 2)->default(0)->after('reglas_descuento_snapshot');
            $table->decimal('porcentaje_descuento_final', 5, 2)->default(0)->after('total_facturado');
            $table->decimal('descuento_calculado', 15, 2)->default(0)->after('porcentaje_descuento_final');
            $table->decimal('saldo_aplicable', 15, 2)->default(0)->after('descuento_calculado');
            $table->decimal('diferencia_contabilidad', 15, 2)->default(0)->after('saldo_aplicable');
            $table->integer('nota_credito_id')->nullable()->after('diferencia_contabilidad')->index('ec_nota_credito_idx');
            $table->unsignedBigInteger('liquidado_por')->nullable()->after('nota_credito_id');
            $table->timestamp('liquidado_at')->nullable()->after('liquidado_por');

            $table->foreign('flujo_id')->references('id')->on('flujo');
            $table->foreign('nota_credito_id')->references('id')->on('nota_credito');
            $table->foreign('cerrado_por')->references('id')->on('users');
            $table->foreign('liquidado_por')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('expo_cotizacion', function (Blueprint $table) {
            $table->dropForeign(['flujo_id']);
            $table->dropForeign(['nota_credito_id']);
            $table->dropForeign(['cerrado_por']);
            $table->dropForeign(['liquidado_por']);
            $table->dropIndex('ec_flujo_idx');
            $table->dropIndex('ec_nota_credito_idx');
            $table->dropColumn([
                'estado',
                'flujo_id',
                'cierre_manual',
                'motivo_cierre',
                'cerrado_por',
                'cerrado_at',
                'reglas_descuento_snapshot',
                'total_facturado',
                'porcentaje_descuento_final',
                'descuento_calculado',
                'saldo_aplicable',
                'diferencia_contabilidad',
                'nota_credito_id',
                'liquidado_por',
                'liquidado_at',
            ]);
        });
    }
};