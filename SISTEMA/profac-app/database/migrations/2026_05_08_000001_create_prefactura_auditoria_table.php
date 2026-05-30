<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrefacturaAuditoriaTable extends Migration
{
    public function up(): void
    {
        Schema::create('prefactura_auditoria', function (Blueprint $table) {
            $table->id();

            // Tipo de acción realizada
            $table->string('tipo_accion', 60)->comment(
                'facturacion_directa | edicion_factura | anulacion_prefactura'
            );

            // Documentos afectados
            $table->unsignedBigInteger('prefactura_id')->nullable();
            $table->unsignedBigInteger('factura_id')->nullable();

            // Snapshot de datos
            $table->json('datos_anteriores')->nullable()->comment('Estado del documento antes de la acción');
            $table->json('datos_nuevos')->nullable()->comment('Estado resultante (si aplica)');

            // Razón ingresada por el operador
            $table->text('motivo')->nullable();

            // Usuario que ejecutó la acción
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();

            // Usuario dueño del código de autorización
            $table->unsignedBigInteger('autorizador_id')->nullable();
            $table->foreign('autorizador_id')->references('id')->on('users')->nullOnDelete();

            // Referencia al registro de código usado
            $table->integer('autorizacion_id')->nullable();

            // Sesión / IP
            $table->string('ip', 45)->nullable();
            $table->string('session_id', 100)->nullable();

            $table->timestamps();

            // Índices útiles para consultas desde administración
            $table->index('tipo_accion');
            $table->index('prefactura_id');
            $table->index('factura_id');
            $table->index('usuario_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prefactura_auditoria');
    }
}
