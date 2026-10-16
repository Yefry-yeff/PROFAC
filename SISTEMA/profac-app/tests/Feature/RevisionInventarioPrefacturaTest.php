<?php

namespace Tests\Feature;

use App\Events\FlujoAvanzadoEvent;
use App\Livewire\Flujo\RevicionInventario;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RevisionInventarioPrefacturaTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.revision_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $mysql = getenv('REVISION_TEST_MYSQL_DATABASE');
        if ($mysql !== false && $mysql !== '') {
            if (!preg_match('/^profac_revision_[a-f0-9]+_testing$/D', $mysql)) {
                throw new \RuntimeException('Nombre de base aislada de pruebas invalido.');
            }
            $connection = $app['config']->get('database.connections.mysql');
            $connection['database'] = $mysql;
            $app['config']->set('database.connections.revision_testing', $connection);
        }
        $app['config']->set('database.default', 'revision_testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('revision_testing', DB::connection()->getName());
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Requiere REVISION_TEST_MYSQL_DATABASE aislada para las consultas de reservas MySQL.');
        }
        Schema::dropAllTables();

        foreach (['cotizacion', 'prefactura'] as $table) {
            Schema::create($table, function (Blueprint $t) use ($table) {
                $t->id(); $t->integer('cliente_id')->default(1); $t->string('nombre_cliente')->default('Cliente');
                $t->string('RTN')->nullable(); $t->date('fecha_emision')->nullable(); $t->date('fecha_vencimiento')->nullable();
                foreach (['sub_total', 'sub_total_grabado', 'sub_total_excento', 'isv', 'total', 'porc_descuento', 'monto_descuento'] as $column) {
                    $t->decimal($column, 14, 4)->default(0);
                }
                $t->integer('tipo_venta_id')->default(2); $t->integer('vendedor')->default(1);
                $t->text('nota')->nullable(); $t->text('arregloIdInputs')->nullable(); $t->integer('numeroInputs')->default(1);
                if ($table === 'prefactura') {
                    $t->integer('cotizacion_id'); $t->integer('flujo_id')->nullable();
                    $t->string('estado'); $t->integer('users_id');
                }
                $t->timestamps();
            });
        }
        foreach (['cotizacion_has_producto', 'prefactura_has_producto'] as $table) {
            Schema::create($table, function (Blueprint $t) use ($table) {
                $t->id();
                if ($table === 'cotizacion_has_producto') {
                    $t->integer('cotizacion_id');
                } else {
                    $t->integer('prefactura_id'); $t->integer('cotizacion_has_producto_id');
                }
                $t->integer('producto_id'); $t->integer('indice'); $t->string('nombre_producto');
                $t->string('nombre_bodega'); $t->integer('bodega_id'); $t->integer('seccion_id');
                $t->integer('unidad_medida_venta_id'); $t->integer('resta_inventario');
                foreach (['precio_unidad', 'cantidad', 'sub_total', 'isv', 'total', 'isv_producto', 'monto_descProducto'] as $column) {
                    $t->decimal($column, 14, 4)->default(0);
                }
                $t->string('idPrecioSeleccionado')->nullable(); $t->decimal('precioSeleccionado')->nullable();
                $t->integer('precios_producto_carga_id')->nullable(); $t->timestamps();
            });
        }
        Schema::create('bodega', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('segmento', function (Blueprint $t) { $t->id(); $t->integer('bodega_id'); });
        Schema::create('seccion', function (Blueprint $t) { $t->id(); $t->integer('segmento_id'); });
        Schema::create('unidad_medida_venta', function (Blueprint $t) { $t->id(); $t->decimal('unidad_venta'); $t->integer('unidad_medida_id'); });
        Schema::create('unidad_medida', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('recibido_bodega', function (Blueprint $t) { $t->id(); $t->integer('producto_id'); $t->integer('seccion_id'); $t->decimal('cantidad_disponible'); });
        Schema::create('configuracion_prefactura', function (Blueprint $t) { $t->id(); $t->integer('dias_validez'); });
        Schema::create('credito_revision', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('cotizacion_id'); $t->string('estado');
            $t->integer('dias_credito_aprobados')->nullable(); $t->date('fecha_aprobacion')->nullable();
            $t->date('fecha_vencimiento_credito')->nullable();
        });
        Schema::create('tipos_tramites', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('historico_flujo', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('tipo_tramite_id'); $t->integer('tramite_id');
            $t->integer('estado_id'); $t->text('observaciones')->nullable();
            $t->integer('created_by')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('flujo', function (Blueprint $t) { $t->id(); $t->string('nombre'); $t->integer('tipo_tramite_id'); $t->integer('updated_by')->nullable(); $t->timestamps(); });
        Schema::create('expo_oferta_seccion', function (Blueprint $t) { $t->id(); $t->integer('cotizacion_id'); });
        Schema::create('revision_inventario_temporal', function (Blueprint $t) { $t->id(); $t->integer('flujo_id'); $t->integer('cotizacion_id'); });
        Schema::create('historico_cotizacion_producto_sin_existencia', function (Blueprint $t) {
            $t->id();
            foreach (['id_cotizacion', 'id_producto', 'indice_linea', 'id_bodega_origen', 'id_seccion_origen', 'id_bodega_actualizacion', 'id_seccion_actualizacion', 'created_by', 'updated_by'] as $column) {
                $t->integer($column);
            }
            $t->string('nombre_producto'); $t->string('nombre_bodega_origen'); $t->string('nombre_bodega_destino');
            $t->text('motivo'); $t->timestamps();
        });

        DB::table('cotizacion')->insert(['id' => 1]);
        DB::table('bodega')->insert(['id' => 2, 'nombre' => 'Destino']);
        DB::table('segmento')->insert(['id' => 2, 'bodega_id' => 2]);
        DB::table('seccion')->insert(['id' => 20, 'segmento_id' => 2]);
        DB::table('unidad_medida_venta')->insert(['id' => 1, 'unidad_venta' => 1, 'unidad_medida_id' => 1]);
        DB::table('unidad_medida')->insert(['id' => 1, 'nombre' => 'RESMA']);
        DB::table('recibido_bodega')->insert(['producto_id' => 2699, 'seccion_id' => 20, 'cantidad_disponible' => 100]);
        DB::table('tipos_tramites')->insert(['id' => 4, 'nombre' => 'Prefactura']);
        DB::table('flujo')->insert(['id' => 1, 'nombre' => 'Cliente', 'tipo_tramite_id' => 9]);
        DB::table('historico_flujo')->insert(['flujo_id' => 1, 'tramite_id' => 1, 'tipo_tramite_id' => 9, 'estado_id' => 5]);
        $this->linea(1, 30);
        $user = new User;
        $user->forceFill(['id' => 7]);
        Auth::setUser($user);
        Event::fake([FlujoAvanzadoEvent::class]);
    }

    private function linea(int $id, float $cantidad): void
    {
        DB::table('cotizacion_has_producto')->insert([
            'id' => $id, 'cotizacion_id' => 1, 'producto_id' => 2699, 'indice' => $id,
            'nombre_producto' => 'Papel', 'nombre_bodega' => 'Anterior', 'bodega_id' => 1,
            'seccion_id' => 10, 'resta_inventario' => 1, 'unidad_medida_venta_id' => 1,
            'cantidad' => $cantidad, 'precio_unidad' => 105.62, 'total' => 3643.89,
        ]);
    }

    private function revision(): RevicionInventario
    {
        $revision = new class extends RevicionInventario {
            public function cargar(): void {}
            public function cerrarDetalle(): void {}
        };
        $revision->flujoId = 1;
        $revision->cotizacionId = 1;
        foreach (DB::table('cotizacion_has_producto')->get() as $idx => $linea) {
            $revision->productos[] = [
                'idx' => $idx, 'cotizacion_has_producto_id' => $linea->id, 'producto_id' => $linea->producto_id,
                'nombre_producto' => $linea->nombre_producto, 'cantidad' => $linea->cantidad,
                'sin_existencia' => false,
                'destinos_bodega' => [['value' => '2|20', 'stock' => 100, 'bodega_nombre' => 'Destino']],
            ];
            $revision->productosRevisados[$idx] = true;
            $revision->bodegaExpoSeleccionada[$idx] = '2|20';
        }
        return $revision;
    }

    public function test_guarda_la_bodega_sugerida_aunque_la_linea_ya_controla_inventario(): void
    {
        $revision = $this->revision();
        $revision->pasarAPrefactura();

        $this->assertSame('', $revision->mensajeError);
        $this->assertDatabaseHas('cotizacion_has_producto', ['id' => 1, 'bodega_id' => 2, 'seccion_id' => 20]);
        $this->assertDatabaseHas('prefactura_has_producto', [
            'cotizacion_has_producto_id' => 1, 'bodega_id' => 2, 'seccion_id' => 20,
            'cantidad' => 30, 'precio_unidad' => 105.62, 'total' => 3643.89,
        ]);
        $this->assertDatabaseHas('historico_cotizacion_producto_sin_existencia', [
            'id_seccion_origen' => 10, 'id_seccion_actualizacion' => 20, 'created_by' => 7,
        ]);
        $this->assertDatabaseHas('recibido_bodega', ['cantidad_disponible' => 100]);
        $this->assertDatabaseHas('historico_flujo', ['tipo_tramite_id' => 9, 'estado_id' => 1]);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_revalida_stock_actual_no_el_valor_cacheado_del_selector(): void
    {
        $revision = $this->revision();
        DB::table('recibido_bodega')->update(['cantidad_disponible' => 10]);
        $revision->pasarAPrefactura();
        $this->assertStringContainsString('ya no cubre', $revision->mensajeError);
        $this->assertDatabaseCount('prefactura', 0);
        $this->assertDatabaseCount('historico_cotizacion_producto_sin_existencia', 0);
        $this->assertDatabaseHas('cotizacion_has_producto', ['id' => 1, 'seccion_id' => 10]);
    }

    public function test_consolida_lineas_del_mismo_producto_y_destino(): void
    {
        $this->linea(2, 80);
        $revision = $this->revision();
        $revision->pasarAPrefactura();
        $this->assertStringContainsString('ya no cubre', $revision->mensajeError);
        $this->assertDatabaseCount('prefactura', 0);
        $this->assertDatabaseCount('historico_cotizacion_producto_sin_existencia', 0);
        $this->assertSame(2, DB::table('cotizacion_has_producto')->where('seccion_id', 10)->count());
    }

    public function test_convierte_las_unidades_de_venta_a_inventario(): void
    {
        DB::table('unidad_medida_venta')->update(['unidad_venta' => 10]);
        $revision = $this->revision();
        $revision->pasarAPrefactura();
        $this->assertStringContainsString('ya no cubre', $revision->mensajeError);
        $this->assertDatabaseCount('prefactura', 0);
    }

    public function test_respeta_reservas_vigentes_de_otros_flujos(): void
    {
        DB::table('prefactura')->insert([
            'id' => 9, 'cotizacion_id' => 9, 'flujo_id' => 9, 'estado' => 'activo', 'users_id' => 7,
            'fecha_emision' => now()->toDateString(), 'created_at' => now(),
        ]);
        $linea = (array) DB::table('cotizacion_has_producto')->where('id', 1)->first();
        unset($linea['cotizacion_id']);
        DB::table('prefactura_has_producto')->insert(array_merge($linea, [
            'prefactura_id' => 9, 'cotizacion_has_producto_id' => 1,
            'bodega_id' => 2, 'seccion_id' => 20, 'cantidad' => 90,
        ]));
        $revision = $this->revision();
        $revision->pasarAPrefactura();
        $this->assertStringContainsString('ya no cubre', $revision->mensajeError);
        $this->assertDatabaseCount('prefactura', 1);
        $this->assertDatabaseHas('cotizacion_has_producto', ['id' => 1, 'seccion_id' => 10]);
    }

    public function test_conserva_la_reasignacion_de_lineas_originalmente_sin_existencia(): void
    {
        DB::table('cotizacion_has_producto')->update(['resta_inventario' => 0]);
        $revision = $this->revision();
        $revision->productos[0]['sin_existencia'] = true;
        $revision->pasarAPrefactura();
        $this->assertSame('', $revision->mensajeError);
        $this->assertDatabaseHas('prefactura_has_producto', ['seccion_id' => 20, 'resta_inventario' => 1]);
    }

    public function test_rechaza_una_seleccion_que_no_pertenece_a_la_linea(): void
    {
        $revision = $this->revision();
        $revision->bodegaExpoSeleccionada[0] = '3|99';
        $revision->pasarAPrefactura();
        $this->assertStringContainsString('Seleccione una bodega', $revision->mensajeError);
        $this->assertDatabaseCount('prefactura', 0);
        $this->assertDatabaseCount('historico_cotizacion_producto_sin_existencia', 0);
    }

    public function test_no_cambia_ubicacion_ni_audita_si_ya_coinciden(): void
    {
        DB::table('cotizacion_has_producto')->update(['bodega_id' => 2, 'seccion_id' => 20]);
        $revision = $this->revision();
        $revision->pasarAPrefactura();
        $this->assertSame('', $revision->mensajeError);
        $this->assertDatabaseHas('prefactura_has_producto', ['seccion_id' => 20]);
        $this->assertDatabaseCount('historico_cotizacion_producto_sin_existencia', 0);
    }

    public function test_revierte_la_reasignacion_si_falla_la_creacion_de_prefactura(): void
    {
        Schema::drop('prefactura');
        $revision = $this->revision();
        $revision->pasarAPrefactura();
        $this->assertStringContainsString('Error al crear la prefactura', $revision->mensajeError);
        $this->assertDatabaseHas('cotizacion_has_producto', ['id' => 1, 'seccion_id' => 10]);
        $this->assertDatabaseCount('historico_cotizacion_producto_sin_existencia', 0);
        $this->assertDatabaseHas('historico_flujo', ['tipo_tramite_id' => 9, 'estado_id' => 5]);
        $this->assertSame(0, DB::transactionLevel());
    }
}
