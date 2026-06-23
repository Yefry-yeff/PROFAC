<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoricoCotizacionProductoSinExistenciaTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('historico_cotizacion_producto_sin_existencia')) {
            Schema::create('historico_cotizacion_producto_sin_existencia', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_cotizacion')->index();
                $table->unsignedBigInteger('id_producto')->index();
                $table->unsignedInteger('indice_linea')->nullable()->index();
                $table->string('nombre_producto', 255)->nullable();
                $table->unsignedBigInteger('id_bodega_origen')->nullable()->index();
                $table->unsignedBigInteger('id_seccion_origen')->nullable()->index();
                $table->unsignedBigInteger('id_bodega_actualizacion')->nullable()->index();
                $table->unsignedBigInteger('id_seccion_actualizacion')->nullable()->index();
                $table->string('nombre_bodega_origen', 255)->nullable();
                $table->string('nombre_bodega_destino', 255)->nullable();
                $table->text('motivo')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_cotizacion_producto_sin_existencia');
    }
}
