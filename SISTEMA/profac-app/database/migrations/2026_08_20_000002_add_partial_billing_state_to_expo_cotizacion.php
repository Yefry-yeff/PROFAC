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
            $table->decimal('aumento_calculado', 15, 2)->default(0)->after('porcentaje_descuento_final');
            $table->decimal('aumento_aplicado', 15, 2)->default(0)->after('aumento_calculado');
            $table->unsignedBigInteger('liquidado_por')->nullable()->after('aumento_aplicado');
            $table->timestamp('liquidado_at')->nullable()->after('liquidado_por');

            $table->foreign('flujo_id')->references('id')->on('flujo');
            $table->foreign('cerrado_por')->references('id')->on('users');
            $table->foreign('liquidado_por')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('expo_cotizacion', function (Blueprint $table) {
            $table->dropForeign(['flujo_id']);
            $table->dropForeign(['cerrado_por']);
            $table->dropForeign(['liquidado_por']);
            $table->dropIndex('ec_flujo_idx');
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
                'aumento_calculado',
                'aumento_aplicado',
                'liquidado_por',
                'liquidado_at',
            ]);
        });
    }
};