<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoEstadoCodigoAndAlterCodigoAutorizacion extends Migration
{
    public function up(): void
    {
        // ── 1. Catálogo de estados de código ─────────────────────────────────
        Schema::create('catalogo_estado_codigo', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->timestamps();
        });

        DB::table('catalogo_estado_codigo')->insert([
            ['id' => 1, 'nombre' => 'Pendiente',  'descripcion' => 'Código generado, aún no utilizado',    'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Utilizado',  'descripcion' => 'Código consumido correctamente',        'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'Expirado',   'descripcion' => 'Código venció sin ser utilizado',       'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'Cancelado',  'descripcion' => 'Código cancelado manualmente',          'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── 2. Configuración de códigos de autorización ───────────────────────
        Schema::create('configuracion_codigo_autorizacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tiempo_expiracion_minutos')->default(10)
                ->comment('Minutos de vigencia de cada código');
            $table->boolean('expiracion_activa')->default(true)
                ->comment('Si false, los códigos no expiran automáticamente');
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->foreign('actualizado_por')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // Fila única de configuración
        DB::table('configuracion_codigo_autorizacion')->insert([
            'tiempo_expiracion_minutos' => 10,
            'expiracion_activa'         => true,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        // ── 3. Alterar tabla codigo_autorizacion ──────────────────────────────
        Schema::table('codigo_autorizacion', function (Blueprint $table) {
            // Relación al flujo (nullable para compatibilidad con registros SR sin flujo)
            $table->unsignedBigInteger('flujo_id')->nullable()->after('estado_id')
                ->comment('Flujo al que pertenece este código');
            $table->string('tipo_tramite', 100)->nullable()->after('flujo_id')
                ->comment('Identificador del trámite: prefactura, facturacion, editar_factura, etc.');
            $table->timestamp('fecha_expiracion')->nullable()->after('tipo_tramite')
                ->comment('Fecha/hora en que expira el código');
            $table->timestamp('fecha_utilizacion')->nullable()->after('fecha_expiracion')
                ->comment('Fecha/hora en que fue consumido');
            $table->unsignedTinyInteger('estado_codigo_id')->default(1)->after('fecha_utilizacion')
                ->comment('FK catalogo_estado_codigo: 1=Pendiente, 2=Utilizado, 3=Expirado, 4=Cancelado');

            $table->foreign('flujo_id')->references('id')->on('flujo')->nullOnDelete();
            $table->foreign('estado_codigo_id')->references('id')->on('catalogo_estado_codigo');
        });
    }

    public function down(): void
    {
        Schema::table('codigo_autorizacion', function (Blueprint $table) {
            $table->dropForeign(['flujo_id']);
            $table->dropForeign(['estado_codigo_id']);
            $table->dropColumn(['flujo_id', 'tipo_tramite', 'fecha_expiracion', 'fecha_utilizacion', 'estado_codigo_id']);
        });

        Schema::dropIfExists('configuracion_codigo_autorizacion');
        Schema::dropIfExists('catalogo_estado_codigo');
    }
}
