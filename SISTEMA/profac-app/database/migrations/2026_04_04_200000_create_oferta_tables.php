<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfertaTables extends Migration
{
    public function up()
    {
        // ── Tabla principal de ofertas (replica de cotizacion + pedido_id) ───
        if (!Schema::hasTable('oferta')) {
            Schema::create('oferta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pedido_id')->nullable()->index();
                $table->string('nombre_cliente', 255)->nullable();
                $table->string('RTN', 20)->nullable();
                $table->date('fecha_emision')->nullable();
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('sub_total', 15, 2)->default(0);
                $table->decimal('sub_total_grabado', 15, 2)->default(0);
                $table->decimal('sub_total_excento', 15, 2)->default(0);
                $table->decimal('isv', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->unsignedBigInteger('cliente_id')->nullable();
                $table->tinyInteger('tipo_venta_id')->default(1);
                $table->unsignedBigInteger('vendedor')->nullable();
                $table->unsignedBigInteger('users_id')->nullable();
                $table->text('arregloIdInputs')->nullable();
                $table->integer('numeroInputs')->default(0);
                $table->decimal('porc_descuento', 5, 2)->default(0);
                $table->decimal('monto_descuento', 15, 2)->default(0);
                $table->text('nota')->nullable();
                $table->timestamps();

                $table->index('cliente_id');
                $table->index('users_id');
            });
        }

        // ── Detalle de productos de la oferta (replica de cotizacion_has_producto) ──
        if (!Schema::hasTable('oferta_has_producto')) {
            Schema::create('oferta_has_producto', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('oferta_id')->index();
                $table->unsignedBigInteger('producto_id')->nullable();
                $table->integer('indice')->default(0);
                $table->string('nombre_producto', 255)->nullable();
                $table->string('nombre_bodega', 255)->nullable();
                $table->decimal('precio_unidad', 15, 4)->default(0);
                $table->string('tipo_precio', 10)->nullable();
                $table->decimal('cantidad', 12, 4)->default(0);
                $table->decimal('sub_total', 15, 2)->default(0);
                $table->decimal('isv', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->unsignedBigInteger('bodega_id')->nullable();
                $table->unsignedBigInteger('seccion_id')->nullable();
                $table->decimal('resta_inventario', 12, 4)->nullable();
                $table->decimal('isv_producto', 5, 2)->nullable();
                $table->unsignedBigInteger('unidad_medida_venta_id')->nullable();
                $table->decimal('monto_descProducto', 15, 2)->default(0);
                $table->string('idPrecioSeleccionado', 10)->nullable();
                $table->decimal('precioSeleccionado', 15, 4)->nullable();
                $table->unsignedBigInteger('precios_producto_carga_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('oferta_has_producto');
        Schema::dropIfExists('oferta');
    }
}
