<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InventarioEdicionFacturaTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.inventario_edicion_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $mysql = getenv('INVENTARIO_EDICION_TEST_MYSQL_DATABASE');
        if ($mysql !== false && $mysql !== '') {
            if (!preg_match('/^profac_inventario_edicion_[a-f0-9]+_testing$/D', $mysql)) {
                throw new \RuntimeException('Nombre de base aislada de pruebas invalido.');
            }
            $connection = $app['config']->get('database.connections.mysql');
            $connection['url'] = null;
            $connection['database'] = $mysql;
            $app['config']->set('database.connections.inventario_edicion_testing', $connection);
        }
        $app['config']->set('database.default', 'inventario_edicion_testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('inventario_edicion_testing', DB::connection()->getName());
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Requiere INVENTARIO_EDICION_TEST_MYSQL_DATABASE aislada para las consultas MySQL.');
        }
        $this->assertMatchesRegularExpression(
            '/^profac_inventario_edicion_[a-f0-9]+_testing$/D',
            DB::connection()->getDatabaseName()
        );
        Schema::dropAllTables();

        Schema::create('factura', function (Blueprint $t) { $t->id(); });
        Schema::create('historico_flujo', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('tipo_tramite_id');
            $t->integer('tramite_id'); $t->integer('estado_id');
        });
        Schema::create('venta_has_producto', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('producto_id'); $t->integer('seccion_id');
            $t->integer('lote'); $t->decimal('cantidad', 14, 4);
            $t->decimal('numero_unidades_resta_inventario', 14, 4);
            $t->decimal('resta_inventario_total', 14, 4);
        });
        Schema::create('recibido_bodega', function (Blueprint $t) {
            $t->id(); $t->integer('producto_id'); $t->integer('seccion_id'); $t->decimal('cantidad_disponible', 14, 4);
        });
        Schema::create('bodega', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('segmento', function (Blueprint $t) { $t->id(); $t->integer('bodega_id'); });
        Schema::create('seccion', function (Blueprint $t) {
            $t->id(); $t->integer('segmento_id'); $t->string('descripcion');
        });
        Schema::create('prefactura', function (Blueprint $t) {
            $t->id(); $t->integer('cotizacion_id')->default(1); $t->string('estado');
            $t->date('fecha_emision'); $t->date('fecha_vencimiento'); $t->timestamps();
        });
        Schema::create('prefactura_has_producto', function (Blueprint $t) {
            $t->id(); $t->integer('prefactura_id'); $t->integer('producto_id');
            $t->integer('seccion_id'); $t->decimal('cantidad', 14, 4); $t->integer('resta_inventario');
        });
        Schema::create('configuracion_prefactura', function (Blueprint $t) { $t->id(); $t->integer('dias_validez'); });
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('cliente_usuario', function (Blueprint $t) {
            $t->integer('cliente_id'); $t->integer('usuario_id'); $t->integer('rol_id');
        });
        Schema::create('credito_revision', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('cotizacion_id'); $t->string('estado');
        });
        Schema::create('expo_oferta_seccion', function (Blueprint $t) { $t->id(); $t->integer('prefactura_id'); });
        Schema::create('expo_cotizacion', function (Blueprint $t) { $t->id(); $t->integer('cotizacion_id'); });
        Schema::create('unidad_medida', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('unidad_medida_venta', function (Blueprint $t) {
            $t->id(); $t->integer('unidad_medida_id');
        });
        Schema::create('producto', function (Blueprint $t) { $t->id(); $t->integer('unidad_medida_compra_id'); });

        DB::table('factura')->insert(['id' => 30350]);
        DB::table('historico_flujo')->insert([
            'flujo_id' => 4487, 'tipo_tramite_id' => 3, 'tramite_id' => 30350, 'estado_id' => 1,
        ]);
        DB::table('bodega')->insert(['id' => 1, 'nombre' => 'Bodega']);
        DB::table('segmento')->insert(['id' => 1, 'bodega_id' => 1]);
        DB::table('seccion')->insert(['id' => 149, 'segmento_id' => 1, 'descripcion' => 'Seccion 1']);
        DB::table('recibido_bodega')->insert(['producto_id' => 2814, 'seccion_id' => 149, 'cantidad_disponible' => 5]);

        // Dos cajas de 12 unidades repartidas entre dos lotes: sumar 24, no 4 ni 48.
        $linea = [
            'factura_id' => 30350, 'producto_id' => 2814, 'seccion_id' => 149,
            'lote' => 1, 'cantidad' => 2, 'resta_inventario_total' => 24,
            'numero_unidades_resta_inventario' => 7,
        ];
        DB::table('venta_has_producto')->insert([
            $linea,
            array_replace($linea, ['lote' => 2, 'numero_unidades_resta_inventario' => 17]),
            array_replace($linea, ['factura_id' => 30351, 'numero_unidades_resta_inventario' => 100]),
            array_replace($linea, ['producto_id' => 2815, 'numero_unidades_resta_inventario' => 100]),
            array_replace($linea, ['seccion_id' => 150, 'numero_unidades_resta_inventario' => 100]),
        ]);
        DB::table('configuracion_prefactura')->insert(['dias_validez' => 7]);
        foreach ([
            [3220, 'activo', now(), 24, 1],
            [3221, 'activo', now(), 3, 1],
            [3222, 'activo', now()->subDays(8), 100, 1],
            [3223, 'inactivo', now(), 100, 1],
            [3224, 'activo', now(), 100, 0],
        ] as [$id, $estado, $fecha, $cantidad, $resta]) {
            DB::table('prefactura')->insert([
                'id' => $id, 'estado' => $estado, 'created_at' => $fecha,
                'fecha_emision' => $fecha->toDateString(), 'fecha_vencimiento' => $fecha->toDateString(),
            ]);
            DB::table('prefactura_has_producto')->insert([
                'prefactura_id' => $id, 'producto_id' => 2814, 'seccion_id' => 149,
                'cantidad' => $cantidad, 'resta_inventario' => $resta,
            ]);
        }
        DB::table('expo_cotizacion')->insert(['cotizacion_id' => 1]);
        DB::table('users')->insert(['id' => 7, 'name' => 'Usuario de pruebas']);
        DB::table('cliente_usuario')->insert([
            ['cliente_id' => 1, 'usuario_id' => 7, 'rol_id' => 2],
            ['cliente_id' => 1, 'usuario_id' => 7, 'rol_id' => 3],
        ]);
        $user = new User;
        $user->forceFill(['id' => 7, 'rol_id' => 1]);
        $this->actingAs($user)->withoutMiddleware();
    }

    public static function listados(): array
    {
        $casos = [];
        foreach (['estatal' => '/estatal/listar/bodegas/2814', 'corporativo' => '/ventas/listar/bodegas/2814'] as $tipo => $ruta) {
            foreach ([
                'edicion' => 26,
                'nueva' => 2,
                'legacy' => 26,
                'sin_factura' => 2,
                'sin_stock_actual' => 21,
                'reserva_superior' => null,
            ] as $escenario => $disponible) {
                $casos[$tipo . '-' . $escenario] = [$ruta, $escenario, $disponible];
            }
        }
        return $casos;
    }

    #[DataProvider('listados')]
    public function test_bodegas_muestran_stock_base_sin_duplicar_lotes(string $ruta, string $escenario, ?int $disponible): void
    {
        $parametros = ['modo' => 'editar_factura', 'flujo_id' => 4487, 'prefactura_id' => 3220];
        if ($escenario === 'nueva') {
            unset($parametros['modo']);
        } elseif ($escenario === 'legacy') {
            DB::table('historico_flujo')->update(['tipo_tramite_id' => 5]);
        } elseif ($escenario === 'sin_factura') {
            DB::table('historico_flujo')->delete();
        } elseif ($escenario === 'sin_stock_actual') {
            DB::table('recibido_bodega')->update(['cantidad_disponible' => 0]);
        } elseif ($escenario === 'reserva_superior') {
            DB::table('prefactura_has_producto')->where('prefactura_id', 3221)->update(['cantidad' => 100]);
        }

        $response = $this->getJson($ruta . '?' . http_build_query($parametros));
        $response->assertOk();
        if ($disponible === null) {
            $response->assertExactJson(['results' => []]);
        } else {
            $response->assertJsonCount(1, 'results')
                ->assertJsonPath('results.0.id', 149)
                ->assertJsonPath('results.0.text', 'Bodega -  1 - cantidad ' . $disponible);
        }
    }

    public static function validaciones(): array
    {
        $casos = [];
        foreach ([
            'sr-gobierno' => '/ventas/estatal/guardar',
            'sr-corporativo' => '/ventas/corporativo/guardar',
            'exonerado' => '/exonerado/venta/guardar',
        ] as $tipo => $ruta) {
            foreach ([
                'edicion-limite' => ['editar_factura', 26, false],
                'edicion-exceso' => ['editar_factura', 27, true],
                'nueva-limite' => ['', 2, false],
                'nueva-exceso' => ['', 3, true],
            ] as $escenario => [$modo, $cantidad, $insuficiente]) {
                $casos[$tipo . '-' . $escenario] = [$ruta, $modo, $cantidad, $insuficiente];
            }
        }
        return $casos;
    }

    #[DataProvider('validaciones')]
    public function test_guardado_valida_el_limite_real_antes_de_crear_factura(
        string $ruta, string $modo, int $cantidad, bool $insuficiente
    ): void {
        $response = $this->postJson($ruta, [
            'modo' => $modo, 'flujo_id' => 4487, 'prefactura_id' => 3220,
            'fecha_emision' => now()->toDateString(), 'fecha_vencimiento' => now()->toDateString(),
            'numero_venta' => 'PRUEBA', 'subTotalGeneralGrabado' => 100, 'subTotalGeneral' => 100,
            'isvGeneral' => 15, 'totalGeneral' => 115, 'numeroInputs' => 2, 'arregloIdInputs' => '0,1',
            'seleccionarCliente' => 1, 'nombre_cliente_ventas' => 'Cliente de prueba',
            'tipoPagoVenta' => 1, 'restriccion' => 0, 'vendedor' => 7, 'tele_asesor' => 7,
            'tipo_venta_id' => 3, 'codigo' => 'PRUEBA',
            'idProducto0' => 2814, 'idSeccion0' => 149, 'restaInventario0' => $cantidad,
            'cantidad0' => $cantidad, 'idUnidadVenta0' => 1, 'nombre0' => 'Producto probado', 'bodega0' => 'Bodega',
            // Esta segunda linea detiene el guardado incluso cuando la primera tiene stock suficiente.
            'idProducto1' => 9999, 'idSeccion1' => 149, 'restaInventario1' => 1,
            'cantidad1' => 1, 'idUnidadVenta1' => 1, 'nombre1' => 'Producto sin stock', 'bodega1' => 'Bodega',
        ]);
        $response->assertOk()->assertJsonPath('icon', 'warning')->assertJsonPath('idFactura', 0);
        $this->assertStringContainsString('Producto sin stock', $response->json('text'));
        if ($insuficiente) {
            $this->assertStringContainsString('Producto probado', $response->json('text'));
        } else {
            $this->assertStringNotContainsString('Producto probado', $response->json('text'));
        }
        $this->assertDatabaseCount('factura', 1);
        $this->assertDatabaseCount('venta_has_producto', 5);
        $this->assertDatabaseHas('recibido_bodega', ['cantidad_disponible' => 5]);
    }
}
