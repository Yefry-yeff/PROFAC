<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boleta_compra', function (Blueprint $table) {
            $table->boolean('incluir_reporte_compras')->default(false);
        });
        Schema::create('boleta_compra_auditoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('boleta_compra_id')->index();
            $table->unsignedInteger('usuario_id');
            $table->string('accion', 40);
            $table->json('antes');
            $table->json('despues');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boleta_compra_auditoria');
        Schema::table('boleta_compra', function (Blueprint $table) {
            $table->dropColumn('incluir_reporte_compras');
        });
    }
};
