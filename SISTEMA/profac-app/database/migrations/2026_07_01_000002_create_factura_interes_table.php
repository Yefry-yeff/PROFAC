<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_interes', function (Blueprint $table) {
            $table->id();

            // Relaciones principales
            $table->integer('factura_id')->comment('Factura a la que pertenece este interés.');
            $table->unsignedBigInteger('configuracion_interes_id')->comment('Configuración vigente al momento del cálculo.');

            // Período de cálculo
            $table->date('fecha_inicio')->comment('Fecha de vencimiento de la factura (inicio de mora).');
            $table->date('fecha_fin')->comment('Fecha en que se calculó/persistió el interés.');

            // Datos de cálculo — inmutables una vez registrados (integridad histórica)
            $table->decimal('capital_base', 15, 2)->comment('Saldo de la factura al momento del cálculo.');
            $table->decimal('porcentaje_aplicado', 8, 4)->comment('Tasa mensual utilizada en este movimiento.');
            $table->integer('dias_vencidos')->comment('Días transcurridos desde el vencimiento.');
            $table->decimal('monto_interes', 15, 2)->comment('Monto del interés calculado.');

            // Estado del registro
            $table->tinyInteger('estado')->default(1)->comment('1=Activo, 0=Inactivo.');

            // Cobro
            $table->boolean('cobrado')->default(false);
            $table->date('fecha_cobro')->nullable();
            $table->unsignedBigInteger('usuario_cobro')->nullable();

            // Registro de decisión de no cobrar
            $table->unsignedBigInteger('usr_no_cobro')->nullable()->comment('Usuario que decidió no cobrar el interés.');
            $table->timestamp('fecha_no_cobro')->nullable();
            $table->string('motivo_no_cobro', 500)->nullable();

            // Anulación (reversión, nunca eliminación física)
            $table->boolean('anulado')->default(false);
            $table->timestamp('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('usuario_anulacion')->nullable();
            $table->string('motivo_anulacion', 500)->nullable();

            $table->timestamps();

            // ── Índices optimizados ────────────────────────────────────────────
            $table->index('factura_id',                    'idx_fi_factura');
            $table->index('estado',                        'idx_fi_estado');
            $table->index('cobrado',                       'idx_fi_cobrado');
            $table->index('anulado',                       'idx_fi_anulado');
            $table->index(['factura_id', 'estado'],        'idx_fi_factura_estado');
            $table->index(['cobrado', 'anulado'],          'idx_fi_cobrado_anulado');
            $table->index(['factura_id', 'cobrado', 'anulado'], 'idx_fi_busqueda_pago');
            $table->index('fecha_cobro',                   'idx_fi_fecha_cobro');

            // ── Claves foráneas ───────────────────────────────────────────────
            $table->foreign('factura_id')
                  ->references('id')->on('factura')
                  ->restrictOnDelete();

            $table->foreign('configuracion_interes_id')
                  ->references('id')->on('configuracion_intereses')
                  ->restrictOnDelete();

            $table->foreign('usuario_cobro')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->foreign('usuario_anulacion')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->foreign('usr_no_cobro')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_interes');
    }
};
