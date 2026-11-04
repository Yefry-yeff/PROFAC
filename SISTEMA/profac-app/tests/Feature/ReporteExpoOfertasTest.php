<?php

namespace Tests\Feature;

use App\Livewire\Reportes\ReporteExpo;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReporteExpoOfertasTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::connection()->getPdo()->sqliteCreateFunction('GREATEST', fn (...$values) => max($values));
        Schema::create('cotizacion', function (Blueprint $t) {
            $t->id(); $t->string('nombre_cliente'); $t->integer('cliente_id')->default(1);
            $t->integer('vendedor')->default(1); $t->integer('users_id')->default(1); $t->date('fecha_emision');
        });
        Schema::create('expo_cotizacion', function (Blueprint $t) {
            $t->integer('expo_id'); $t->integer('cotizacion_id'); $t->integer('flujo_id')->nullable(); $t->string('estado');
        });
        Schema::create('cotizacion_has_producto', function (Blueprint $t) {
            $t->id(); $t->integer('cotizacion_id'); $t->integer('producto_id')->default(1);
            $t->integer('precios_producto_carga_id')->default(1); $t->integer('unidad_medida_venta_id')->default(1);
            $t->integer('indice')->default(1); $t->decimal('precio_unidad')->default(10);
            $t->decimal('cantidad'); $t->decimal('sub_total');
        });
        Schema::create('producto', function (Blueprint $t) {
            $t->id(); $t->integer('marca_id'); $t->decimal('costo_promedio'); $t->string('codigo_barra'); $t->string('nombre');
        });
        Schema::create('marca', function (Blueprint $t) {
            $t->id(); $t->string('nombre');
        });
        Schema::create('precios_producto_carga', function (Blueprint $t) {
            $t->id(); $t->integer('categoria_precios_id'); $t->integer('categoria_producto_id');
            $t->decimal('precio_base_venta'); $t->decimal('costoproducto');
        });
        Schema::create('categoria_precios', fn (Blueprint $t) => $t->id());
        Schema::create('categoria_producto', function (Blueprint $t) {
            $t->id(); $t->string('descripcion');
        });
        Schema::create('unidad_medida_venta', function (Blueprint $t) {
            $t->id(); $t->integer('unidad_venta'); $t->integer('unidad_medida_id');
        });
        Schema::create('unidad_medida', function (Blueprint $t) {
            $t->id(); $t->string('nombre');
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name');
        });
        Schema::create('expo_oferta_seccion', function (Blueprint $t) {
            $t->integer('cotizacion_id'); $t->integer('cotizacion_origen_id');
            $t->integer('numero'); $t->string('nombre'); $t->string('estado');
        });
        Schema::create('historico_flujo', function (Blueprint $t) {
            $t->id(); $t->integer('tramite_id'); $t->integer('tipo_tramite_id');
            $t->integer('flujo_id'); $t->integer('estado_id')->default(1);
        });
        Schema::create('cotizacion_estado', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('cotizacion_id'); $t->integer('ganadora');
        });
        Schema::create('venta_has_producto', function (Blueprint $t) {
            $t->integer('factura_id'); $t->integer('producto_id')->default(1); $t->integer('indice')->default(1);
            $t->integer('cotizacion_has_producto_id')->nullable(); $t->integer('precios_producto_carga_id')->default(1);
            $t->decimal('cantidad_s'); $t->decimal('cantidad_oferta_aplicada')->nullable();
            $t->decimal('sub_total_s'); $t->decimal('precio_unidad')->default(10);
        });
        Schema::create('factura', function (Blueprint $t) {
            $t->id(); $t->integer('estado_venta_id');
        });
        Schema::create('prefactura_auditoria', function (Blueprint $t) {
            $t->integer('factura_id'); $t->integer('prefactura_id');
        });
        Schema::create('prefactura', function (Blueprint $t) {
            $t->id(); $t->integer('cotizacion_id');
        });
        Schema::create('prefactura_has_producto', function (Blueprint $t) {
            $t->integer('prefactura_id'); $t->integer('producto_id'); $t->integer('indice');
            $t->integer('cotizacion_has_producto_id');
        });

        DB::table('producto')->insert(['id' => 1, 'marca_id' => 1, 'costo_promedio' => 3, 'codigo_barra' => 'P1', 'nombre' => 'Producto']);
        DB::table('marca')->insert(['id' => 1, 'nombre' => 'Marca']);
        DB::table('precios_producto_carga')->insert([
            'id' => 1, 'categoria_precios_id' => 1, 'categoria_producto_id' => 1, 'precio_base_venta' => 4, 'costoproducto' => 3,
        ]);
        DB::table('categoria_precios')->insert(['id' => 1]);
        DB::table('categoria_producto')->insert(['id' => 1, 'descripcion' => 'Categoria']);
        DB::table('unidad_medida_venta')->insert(['id' => 1, 'unidad_venta' => 1, 'unidad_medida_id' => 1]);
        DB::table('unidad_medida')->insert(['id' => 1, 'nombre' => 'Unidad']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Asesor']);

        foreach ([10 => 10, 11 => 5, 20 => 3, 30 => 2, 40 => 1, 50 => 7] as $id => $cantidad) {
            DB::table('cotizacion')->insert(['id' => $id, 'nombre_cliente' => 'Cliente ' . $id, 'fecha_emision' => '2026-11-01']);
            DB::table('expo_cotizacion')->insert([
                'expo_id' => $id === 50 ? 8 : 7, 'cotizacion_id' => $id,
                'flujo_id' => match ($id) { 10, 11 => 100, 20 => 200, default => null },
                'estado' => 'FACTURACION_PARCIAL',
            ]);
            DB::table('cotizacion_has_producto')->insert(['id' => $id, 'cotizacion_id' => $id, 'cantidad' => $cantidad, 'sub_total' => $cantidad * 10]);
        }
        DB::table('expo_oferta_seccion')->insert([
            'cotizacion_id' => 11, 'cotizacion_origen_id' => 10, 'numero' => 1, 'nombre' => 'Seccion 1', 'estado' => 'FACTURADA',
        ]);
        DB::table('historico_flujo')->insert([
            ['id' => 1, 'tramite_id' => 20, 'tipo_tramite_id' => 2, 'flujo_id' => 999],
            ['id' => 2, 'tramite_id' => 30, 'tipo_tramite_id' => 2, 'flujo_id' => 300],
            ['id' => 3, 'tramite_id' => 30, 'tipo_tramite_id' => 2, 'flujo_id' => 301],
            ['id' => 4, 'tramite_id' => 30, 'tipo_tramite_id' => 9, 'flujo_id' => 999],
        ]);
        DB::table('cotizacion_estado')->insert(['flujo_id' => 100, 'cotizacion_id' => 10, 'ganadora' => 1]);
        DB::table('factura')->insert([['id' => 1, 'estado_venta_id' => 1], ['id' => 2, 'estado_venta_id' => 2]]);
        DB::table('venta_has_producto')->insert([
            ['factura_id' => 1, 'cotizacion_has_producto_id' => 11, 'cantidad_s' => 4, 'sub_total_s' => 40],
            ['factura_id' => 2, 'cotizacion_has_producto_id' => 11, 'cantidad_s' => 1, 'sub_total_s' => 10],
        ]);
    }

    private function ofertas(array $filtros = []): array
    {
        return (new ReporteExpo)->tablaOfertas(new Request(array_merge(['expo_id' => 7], $filtros)))->getData(true);
    }

    public function test_resuelve_el_ultimo_historico_solo_de_oferta_y_prioriza_el_flujo_explicito(): void
    {
        $ofertas = collect($this->ofertas())->keyBy('oferta_id');
        $this->assertSame(200, $ofertas[20]['flujo_id']);
        $this->assertSame(301, $ofertas[30]['flujo_id']);
        $this->assertNull($ofertas[40]['flujo_id']);
        $this->assertArrayNotHasKey(50, $ofertas);
        $this->assertCount(4, $ofertas);
    }

    public function test_historial_repetido_no_duplica_totales_ni_secciones_y_excluye_facturas_anuladas(): void
    {
        DB::table('historico_flujo')->insert([
            ['tramite_id' => 10, 'tipo_tramite_id' => 2, 'flujo_id' => 100],
            ['tramite_id' => 10, 'tipo_tramite_id' => 2, 'flujo_id' => 100],
        ]);
        $raiz = collect($this->ofertas())->firstWhere('oferta_id', 10);
        $this->assertSame(100, $raiz['flujo_id']);
        $this->assertEquals(100, $raiz['total_ofertado']);
        $this->assertEquals(40, $raiz['total_facturado']);
        $this->assertEquals(60, $raiz['utilidad']);
        $this->assertSame(1, $raiz['num_facturas']);
        $this->assertSame(1, $raiz['num_secciones']);
        $this->assertSame('PARCIALMENTE_FACTURADA', $raiz['estado_facturacion']);
        $this->assertSame(11, $raiz['secciones'][1]['oferta_id']);
    }

    public function test_conserva_filtros_y_devuelve_lista_vacia_sin_ofertas(): void
    {
        $this->assertCount(4, $this->ofertas(['marca_id' => 1, 'vendedor_id' => 1, 'fecha_desde' => '2026-11-01']));
        $this->assertSame([], $this->ofertas(['fecha_desde' => '2026-12-01']));
        $this->assertSame([], $this->ofertas(['expo_id' => 999]));
    }

    public function test_enlaces_por_prefactura_conservan_facturacion_sin_incluir_otras_expos(): void
    {
        DB::table('factura')->insert([
            ['id' => 3, 'estado_venta_id' => 1],
            ['id' => 4, 'estado_venta_id' => 1],
            ['id' => 5, 'estado_venta_id' => 1],
        ]);
        DB::table('prefactura')->insert([
            ['id' => 1, 'cotizacion_id' => 20],
            ['id' => 2, 'cotizacion_id' => 50],
            ['id' => 3, 'cotizacion_id' => 50],
        ]);
        DB::table('prefactura_auditoria')->insert([
            ['factura_id' => 3, 'prefactura_id' => 1],
            ['factura_id' => 4, 'prefactura_id' => 2],
            ['factura_id' => 5, 'prefactura_id' => 3],
        ]);
        DB::table('prefactura_has_producto')->insert([
            ['prefactura_id' => 1, 'producto_id' => 1, 'indice' => 1, 'cotizacion_has_producto_id' => 20],
            ['prefactura_id' => 2, 'producto_id' => 1, 'indice' => 1, 'cotizacion_has_producto_id' => 50],
            ['prefactura_id' => 3, 'producto_id' => 1, 'indice' => 1, 'cotizacion_has_producto_id' => 20],
        ]);
        DB::table('venta_has_producto')->insert([
            ['factura_id' => 3, 'cantidad_s' => 2, 'sub_total_s' => 20],
            ['factura_id' => 4, 'cantidad_s' => 7, 'sub_total_s' => 70],
            ['factura_id' => 5, 'cantidad_s' => 9, 'sub_total_s' => 90],
        ]);

        $oferta = collect($this->ofertas())->firstWhere('oferta_id', 20);
        $this->assertEquals(20, $oferta['total_facturado']);
        $this->assertSame(1, $oferta['num_facturas']);
        $productos = (new ReporteExpo)->tablaProductos(new Request(['expo_id' => 7]))->getData(true);
        $this->assertCount(1, $productos);
        $this->assertEquals(60, $productos[0]['total_facturado']);
        $this->assertEquals(6, $productos[0]['cantidad_facturada']);
    }

    public function test_resuelve_historial_en_lote_tambien_para_el_detalle_exportado(): void
    {
        DB::enableQueryLog();
        try {
            $report = new ReporteExpo;
            $filas = (new \ReflectionMethod($report, 'datosProductosDetalle'))->invoke($report, new Request(['expo_id' => 7]));
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
        }
        $detalle = collect($filas)->keyBy('oferta_id');
        $this->assertSame(200, $detalle[20]['flujo_id']);
        $this->assertSame(301, $detalle[30]['flujo_id']);
        $this->assertNull($detalle[40]['flujo_id']);
        $this->assertCount(5, $detalle);
        $this->assertCount(3, $queries);
        $this->assertStringNotContainsString('ORDER BY hf.id DESC LIMIT 1', $queries[1]['query']);
        $this->assertStringContainsString('MAX(id) AS ultimo_id', $queries[1]['query']);
    }
}
