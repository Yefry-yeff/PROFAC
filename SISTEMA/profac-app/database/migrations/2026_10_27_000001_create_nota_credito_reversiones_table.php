<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_credito_reversiones', function (Blueprint $table) {
            $table->id();
            $table->integer('nota_credito_id');
            $table->string('alcance', 20);
            $table->unsignedBigInteger('movimiento_id')->nullable();
            $table->text('motivo');
            $table->json('detalle')->nullable();
            $table->unsignedBigInteger('users_id');
            $table->timestamps();

            $table->foreign('nota_credito_id')->references('id')->on('nota_credito');
            $table->foreign('users_id')->references('id')->on('users');
            $table->index(['nota_credito_id', 'alcance']);
        });

        Schema::table('nota_credito_ajustes_comision', function (Blueprint $table) {
            $table->timestamp('anulado_at')->nullable()->after('detalle_calculo');
            $table->unsignedBigInteger('anulado_por')->nullable()->after('anulado_at');
        });
    }

    public function down(): void
    {
        Schema::table('nota_credito_ajustes_comision', function (Blueprint $table) {
            $table->dropColumn(['anulado_at', 'anulado_por']);
        });

        Schema::dropIfExists('nota_credito_reversiones');
    }
};
