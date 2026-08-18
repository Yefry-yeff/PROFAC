<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteAsesorAuditoriaTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cliente_asesor_auditoria')) {
            return;
        }

        Schema::create('cliente_asesor_auditoria', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->unsignedBigInteger('asesor_id')->comment('usuario afectado (agregado o removido)');
            $table->string('tipo', 60)->nullable()->comment('Rol del usuario al momento del cambio: Asesor Comercial / Tele Asesor / etc.');
            $table->string('accion', 20)->comment('INSERT | DELETE | UPDATE');
            $table->unsignedBigInteger('usuario')->nullable()->comment('quién realizó el cambio');
            $table->string('comentario', 255)->nullable();
            $table->string('lote_id', 40)->nullable()->comment('Identificador de operación masiva (agrupa varios cambios de una misma acción)');
            $table->timestamp('fecha')->useCurrent();

            $table->index('cliente_id');
            $table->index('asesor_id');
            $table->index('lote_id');

            $table->foreign('cliente_id')->references('id')->on('cliente')->cascadeOnDelete();
            $table->foreign('asesor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('usuario')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_asesor_auditoria');
    }
}
