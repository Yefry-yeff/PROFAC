<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_intereses', function (Blueprint $table) {
            $table->id();
            $table->decimal('tasa_mensual', 8, 4)->default(3.2500)->comment('Tasa mensual en porcentaje. Ejemplo: 3.25 = 3.25%');
            $table->boolean('estado')->default(true)->comment('1=Activo, 0=Inactivo. Nunca eliminar registros utilizados.');
            $table->date('fecha_vigencia')->comment('Fecha desde la cual aplica esta configuración.');
            $table->date('fecha_fin_vigencia')->nullable()->comment('Fecha hasta la cual aplica. NULL = indefinida.');
            $table->string('observaciones', 500)->nullable();

            // Auditoría
            $table->unsignedBigInteger('usr_creador')->nullable();
            $table->unsignedBigInteger('usr_modificador')->nullable();
            $table->timestamps();

            // Extensibilidad futura — columnas preparadas pero opcionales
            $table->unsignedBigInteger('empresa_id')->nullable()->comment('Para tasas diferenciadas por empresa en el futuro.');
            $table->unsignedBigInteger('sucursal_id')->nullable()->comment('Para tasas diferenciadas por sucursal en el futuro.');
            $table->unsignedBigInteger('tipo_documento_id')->nullable()->comment('Para tasas por tipo de documento en el futuro.');
            $table->unsignedBigInteger('categoria_cliente_id')->nullable()->comment('Para tasas por categoría de cliente en el futuro.');

            // Índices de búsqueda frecuente
            $table->index(['estado', 'fecha_vigencia'], 'idx_ci_estado_vigencia');
            $table->index('fecha_vigencia', 'idx_ci_fecha_vigencia');

            $table->foreign('usr_creador')->references('id')->on('users')->nullOnDelete();
            $table->foreign('usr_modificador')->references('id')->on('users')->nullOnDelete();
        });

        // Semilla con la tasa inicial de negocio
        DB::table('configuracion_intereses')->insert([
            'tasa_mensual'    => 3.2500,
            'estado'          => 1,
            'fecha_vigencia'  => now()->toDateString(),
            'observaciones'   => 'Configuración inicial del sistema — tasa del 3.25% mensual.',
            'usr_creador'     => null,
            'usr_modificador' => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_intereses');
    }
};
