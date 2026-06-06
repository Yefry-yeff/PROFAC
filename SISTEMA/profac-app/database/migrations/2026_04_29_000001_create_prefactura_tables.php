<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePrefacturaTables extends Migration
{
    public function up(): void
    {
        // ── Configuración global de tiempo de vida de prefacturas ────────────
        Schema::create('configuracion_prefactura', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('dias_validez')->default(7)->comment('Días de validez desde creación');
            $table->string('descripcion_validez', 100)->default('1 semana')->comment('Texto legible de validez');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Tabla principal de prefacturas ────────────────────────────────────
        Schema::create('prefactura', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cotizacion_id')->nullable()->comment('Oferta ganadora de origen');
            $table->unsignedBigInteger('flujo_id')->nullable();
            $table->unsignedInteger('cliente_id')->nullable();
            $table->string('nombre_cliente');
            $table->string('RTN', 60)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->decimal('sub_total', 14, 4)->default(0);
            $table->decimal('sub_total_grabado', 14, 4)->default(0);
            $table->decimal('sub_total_excento', 14, 4)->default(0);
            $table->decimal('isv', 14, 4)->default(0);
            $table->decimal('total', 14, 4)->default(0);
            $table->decimal('porc_descuento', 8, 2)->default(0);
            $table->decimal('monto_descuento', 14, 4)->default(0);
            $table->unsignedInteger('tipo_venta_id')->nullable();
            $table->unsignedInteger('vendedor')->nullable();
            $table->text('nota')->nullable();
            $table->string('arregloIdInputs')->nullable();
            $table->unsignedInteger('numeroInputs')->default(0);
            $table->string('estado', 30)->default('activo')->comment('activo|vencida|convertida');
            $table->unsignedBigInteger('users_id')->nullable();
            $table->foreign('users_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Productos de la prefactura ────────────────────────────────────────
        Schema::create('prefactura_has_producto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prefactura_id');
            $table->foreign('prefactura_id')->references('id')->on('prefactura')->cascadeOnDelete();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedInteger('indice')->default(0);
            $table->string('nombre_producto');
            $table->string('nombre_bodega')->nullable();
            $table->decimal('precio_unidad', 14, 4)->default(0);
            $table->decimal('cantidad', 14, 4)->default(1);
            $table->decimal('sub_total', 14, 4)->default(0);
            $table->decimal('isv', 14, 4)->default(0);
            $table->decimal('total', 14, 4)->default(0);
            $table->decimal('isv_producto', 8, 2)->default(0);
            $table->unsignedBigInteger('Bodega_id')->nullable();
            $table->unsignedBigInteger('seccion_id')->nullable();
            $table->unsignedBigInteger('unidad_medida_venta_id')->nullable();
            $table->decimal('monto_descProducto', 14, 4)->default(0);
            $table->string('idPrecioSeleccionado', 10)->nullable();
            $table->string('precioSeleccionado', 20)->nullable();
            $table->unsignedBigInteger('precios_producto_carga_id')->nullable();
            $table->tinyInteger('resta_inventario')->default(0);
            $table->timestamps();
        });

        // ── Insertar configuración por defecto ────────────────────────────────
        DB::table('configuracion_prefactura')->insert([
            'dias_validez'       => 7,
            'descripcion_validez' => '1 semana',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('prefactura_has_producto');
        Schema::dropIfExists('prefactura');
        Schema::dropIfExists('configuracion_prefactura');
    }
}
