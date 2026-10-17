<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_no_identificados', function (Blueprint $table) {
            $table->string('referencia_bancaria', 500)->nullable()->after('cuenta_bancaria');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_no_identificados', function (Blueprint $table) {
            $table->dropColumn('referencia_bancaria');
        });
    }
};
