<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comision_retencion_fuente', function (Blueprint $table) {
            $table->id();
            $table->date('periodo')->index();
            $table->unsignedBigInteger('users_comision')->index();
            $table->decimal('monto_retencion', 15, 2);
            $table->text('comentario');
            $table->tinyInteger('estado')->default(1)->index()->comment('1=activa, 0=revertida');

            $table->unsignedBigInteger('usuario_aplico')->nullable();
            $table->string('usuario_nombre_aplico', 150)->nullable();
            $table->unsignedBigInteger('usuario_revirtio')->nullable();
            $table->string('usuario_nombre_revirtio', 150)->nullable();
            $table->timestamp('fecha_reversion')->nullable();
            $table->text('comentario_reversion')->nullable();

            $table->timestamps();

            $table->index(['periodo', 'users_comision', 'estado'], 'idx_crf_periodo_user_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comision_retencion_fuente');
    }
};
