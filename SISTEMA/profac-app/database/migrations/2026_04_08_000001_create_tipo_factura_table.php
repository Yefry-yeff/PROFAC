<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipo_factura', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->comment('Nombre visible del tipo de facturación (debe coincidir con sub_menu.nombre)');
            $table->string('codigo', 50)->unique()->comment('Identificador interno único (ej: estatal, corporativa, sin_restriccion_gobierno)');
            $table->string('ruta_menu', 255)->nullable()->comment('URL del menú (sin / inicial), debe coincidir con sub_menu.url');
            $table->unsignedTinyInteger('tipo_venta_id')->comment('1=Corporativo, 2=Estatal, 3=Exonerado');
            $table->unsignedTinyInteger('restriccion')->default(1)->comment('1=Con restricción de precio, 2=Sin restricción');
            $table->unsignedInteger('max_descuento')->default(50)->comment('Descuento máximo permitido en %');
            $table->boolean('requiere_codigo_autorizacion')->default(false)->comment('Si requiere modal de código de autorización');
            $table->boolean('requiere_codigo_exoneracion')->default(false)->comment('Si requiere código de exoneración');
            $table->boolean('requiere_orden_compra')->default(false)->comment('Si muestra campo orden de compra');
            $table->boolean('aplica_isv')->default(true)->comment('Si se calcula ISV');
            $table->boolean('multiples_precios')->default(false)->comment('Si muestra opciones de precio A/B/C/D');
            $table->decimal('comision_fija', 5, 2)->nullable()->comment('Comisión fija (ej: 0.50 para exoneradas). NULL = cálculo estándar');
            $table->boolean('estado')->default(true)->comment('Activo/Inactivo');
            $table->unsignedInteger('orden')->default(0)->comment('Orden de aparición en el selector');
            $table->timestamps();
        });

        // Agregar columna tipo_factura_id a la tabla factura
        Schema::table('factura', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_factura_id')->nullable()->after('tipo_venta_id')
                  ->comment('FK a tipo_factura para identificar el tipo exacto de facturación');
        });
    }

    public function down()
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->dropColumn('tipo_factura_id');
        });

        Schema::dropIfExists('tipo_factura');
    }
};
