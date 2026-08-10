<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_credito_movimientos', function (Blueprint $table) {
            $table->string('cuenta_reembolso', 150)->nullable()->after('banco_id');
        });

        DB::statement("
            UPDATE nota_credito_movimientos m
            INNER JOIN banco b ON b.id = m.banco_id
            SET m.cuenta_reembolso = b.cuenta
            WHERE m.tipo = 'reembolso'
              AND m.cuenta_reembolso IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('nota_credito_movimientos', function (Blueprint $table) {
            $table->dropColumn('cuenta_reembolso');
        });
    }
};
