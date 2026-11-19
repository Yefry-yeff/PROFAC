<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('auditoria_compras_ventas')) {
            Schema::create('auditoria_compras_ventas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipo_movimiento', 10);
            $table->unsignedInteger('origen_id')->nullable();
            $table->string('clave_origen', 100);
            $table->dateTime('fecha_movimiento')->nullable();
            $table->date('fecha_ingreso_compra')->nullable();
            $table->dateTime('fecha_creacion_factura')->nullable();
            $table->date('fecha_emision_factura')->nullable();
            $table->unsignedInteger('factura_id')->nullable();
            $table->unsignedInteger('compra_id')->nullable();
            $table->string('numero_documento', 90)->nullable();
            $table->string('cai', 45)->nullable();
            $table->char('clasificacion_factura', 1)->nullable();
            $table->string('estado_documento', 15);
            $table->unsignedInteger('producto_id');
            $table->string('producto_nombre', 1000);
            $table->unsignedInteger('lote_id')->nullable();
            $table->unsignedInteger('bodega_id')->nullable();
            $table->string('bodega_nombre', 100)->nullable();
            $table->unsignedInteger('seccion_id')->nullable();
            $table->string('seccion_nombre', 100)->nullable();
            $table->unsignedInteger('unidad_compra_id')->nullable();
            $table->string('unidad_compra_nombre', 45)->nullable();
            $table->decimal('factor_unidad_compra', 18, 4)->nullable();
            $table->unsignedInteger('unidad_venta_id')->nullable();
            $table->string('unidad_venta_nombre', 45)->nullable();
            $table->decimal('factor_unidad_venta', 18, 4)->nullable();
            $table->decimal('cantidad_documento', 18, 4);
            $table->decimal('cantidad_base', 18, 4);
            $table->decimal('cantidad_movimiento_base', 18, 4);
            $table->unsignedInteger('cliente_id')->nullable();
            $table->string('cliente_nombre', 500)->nullable();
            $table->unsignedInteger('proveedor_id')->nullable();
            $table->string('proveedor_nombre', 500)->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_nombre', 255)->nullable();
            $table->dateTime('sincronizado_at');
            $table->timestamps();

            $table->unique(['tipo_movimiento', 'clave_origen'], 'uq_auditoria_movimiento_origen');
            $table->index(['fecha_movimiento', 'tipo_movimiento'], 'idx_auditoria_fecha_tipo');
            $table->index(['producto_id', 'fecha_movimiento'], 'idx_auditoria_producto_fecha');
            $table->index(['factura_id', 'producto_id', 'lote_id'], 'idx_auditoria_factura_producto_lote');
            $table->index(['compra_id', 'producto_id', 'lote_id'], 'idx_auditoria_compra_producto_lote');
            $table->index(['clasificacion_factura', 'fecha_emision_factura'], 'idx_auditoria_clasificacion_fecha');
            $table->index(['estado_documento', 'fecha_movimiento'], 'idx_auditoria_estado_fecha');
            $table->index('cai', 'idx_auditoria_cai');
            $table->index('lote_id', 'idx_auditoria_lote');
            });
        }

        if (!Schema::hasTable('auditoria_compras_ventas_sync')) {
            Schema::create('auditoria_compras_ventas_sync', function (Blueprint $table) {
                $table->id();
                $table->string('estado', 15);
                $table->string('modo', 15);
                $table->dateTime('iniciado_at');
                $table->dateTime('finalizado_at')->nullable();
                $table->unsignedBigInteger('filas_compras')->default(0);
                $table->unsignedBigInteger('filas_ventas')->default(0);
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }

        $menuId = DB::table('menu')->whereIn('nombre_menu', ['Kardex', 'Cardex'])->value('id');
        if ($menuId) {
            $submenuId = DB::table('sub_menu')->where('url', 'auditoria/compras-ventas')->value('id');
            if (!$submenuId) {
                $submenuId = DB::table('sub_menu')->insertGetId([
                    'url' => 'auditoria/compras-ventas',
                    'nombre' => 'Auditoría Compras y Ventas',
                    'menu_id' => $menuId,
                    'orden' => 3,
                    'estado_id' => 1,
                    'icono' => 'fa fa-balance-scale',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ([1, 5, 7] as $rolId) {
                DB::table('rol_submenu')->updateOrInsert(
                    ['rol_id' => $rolId, 'sub_menu_id' => $submenuId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        $submenuId = DB::table('sub_menu')->where('url', 'auditoria/compras-ventas')->value('id');
        if ($submenuId) {
            DB::table('rol_submenu')->where('sub_menu_id', $submenuId)->delete();
            DB::table('sub_menu')->where('id', $submenuId)->delete();
        }

        Schema::dropIfExists('auditoria_compras_ventas_sync');
        Schema::dropIfExists('auditoria_compras_ventas');
    }
};
