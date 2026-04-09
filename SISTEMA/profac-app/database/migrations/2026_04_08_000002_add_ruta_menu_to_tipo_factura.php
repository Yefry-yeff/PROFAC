<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Eliminar todas las columnas url_* que no se necesitan
        Schema::table('tipo_factura', function (Blueprint $table) {
            $table->dropColumn([
                'url_guardar',
                'url_listar_clientes',
                'url_datos_cliente',
                'url_datos_producto',
                'url_tipo_pago',
                'url_bodegas',
                'url_imprimir',
                'url_historial_precios',
                'url_vendedores',
                'url_orden_compra',
                'url_codigos_exoneracion',
            ]);
        });

        // 2. Agregar columna ruta_menu
        Schema::table('tipo_factura', function (Blueprint $table) {
            $table->string('ruta_menu', 255)->nullable()->after('codigo')
                  ->comment('URL del menú (sin / inicial), debe coincidir con sub_menu.url');
        });

        // 3. Renombrar tipos para que coincidan con el menú
        DB::table('tipo_factura')->where('codigo', 'estatal')->update([
            'nombre'    => 'Facturación Clientes A',
            'ruta_menu' => 'ventas/estatal',
        ]);

        DB::table('tipo_factura')->where('codigo', 'sin_restriccion_gobierno')->update([
            'nombre'    => 'Facturación SR/Clientes A',
            'ruta_menu' => 'ventas/sin/restriccion/gobierno',
        ]);

        DB::table('tipo_factura')->where('codigo', 'corporativa')->update([
            'nombre'    => 'Facturación Clientes B',
            'ruta_menu' => 'ventas/coporativo',
        ]);

        DB::table('tipo_factura')->where('codigo', 'sin_restriccion_precio')->update([
            'nombre'    => 'Facturación SR/P Clientes B',
            'ruta_menu' => 'ventas/sin/restriccion/precio',
        ]);

        DB::table('tipo_factura')->where('codigo', 'exoneradas')->update([
            'nombre'    => 'Facturación Exonerada',
            'ruta_menu' => 'ventas/exonerado/factura',
        ]);

        // 4. Reemplazar "Comisiones" por "Cotización Clientes A"
        DB::table('tipo_factura')->where('codigo', 'comisiones')->update([
            'nombre'                        => 'Cotización Clientes A',
            'codigo'                        => 'cotizacion_clientes_a',
            'tipo_venta_id'                 => 2,
            'restriccion'                   => 1,
            'max_descuento'                 => 50,
            'requiere_codigo_autorizacion'  => false,
            'requiere_codigo_exoneracion'   => false,
            'requiere_orden_compra'         => false,
            'aplica_isv'                    => true,
            'multiples_precios'             => false,
            'comision_fija'                 => null,
            'ruta_menu'                     => 'proforma/cotizacion/2',
            'estado'                        => true,
            'orden'                         => 6,
        ]);
    }

    public function down()
    {
        // Revertir cotización a comisiones
        DB::table('tipo_factura')->where('codigo', 'cotizacion_clientes_a')->update([
            'nombre'    => 'Comisiones',
            'codigo'    => 'comisiones',
            'ruta_menu' => null,
        ]);

        // Revertir nombres
        DB::table('tipo_factura')->where('codigo', 'estatal')->update(['nombre' => 'Venta Estatal', 'ruta_menu' => null]);
        DB::table('tipo_factura')->where('codigo', 'sin_restriccion_gobierno')->update(['nombre' => 'Sin Restricción Gobierno', 'ruta_menu' => null]);
        DB::table('tipo_factura')->where('codigo', 'corporativa')->update(['nombre' => 'Venta Corporativa', 'ruta_menu' => null]);
        DB::table('tipo_factura')->where('codigo', 'sin_restriccion_precio')->update(['nombre' => 'Sin Restricción Precio', 'ruta_menu' => null]);
        DB::table('tipo_factura')->where('codigo', 'exoneradas')->update(['nombre' => 'Ventas Exoneradas', 'ruta_menu' => null]);

        Schema::table('tipo_factura', function (Blueprint $table) {
            $table->dropColumn('ruta_menu');
        });

        // Re-agregar columnas url_*
        Schema::table('tipo_factura', function (Blueprint $table) {
            $table->string('url_guardar', 255)->default('');
            $table->string('url_listar_clientes', 255)->default('');
            $table->string('url_datos_cliente', 255)->default('');
            $table->string('url_datos_producto', 255)->default('');
            $table->string('url_tipo_pago', 255)->default('');
            $table->string('url_bodegas', 255)->default('');
            $table->string('url_imprimir', 255)->nullable();
            $table->string('url_historial_precios', 255)->nullable();
            $table->string('url_vendedores', 255)->nullable();
            $table->string('url_orden_compra', 255)->nullable();
            $table->string('url_codigos_exoneracion', 255)->nullable();
        });
    }
};
