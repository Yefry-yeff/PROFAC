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
        DB::connection()->getPdo()->sqliteCreateFunction('LEAST', fn (...$values) => in_array(null, $values, true) ? null : min($values));
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
            $t->decimal('isv_producto')->default(15); $t->decimal('monto_descProducto')->default(0);
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
            $t->id(); $t->integer('estado_venta_id'); $t->date('fecha_emision')->default('2026-11-05');
            $t->decimal('total')->default(0);
        });
        Schema::create('aplicacion_pagos', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->decimal('credito_abonos'); $t->integer('estado')->default(1);
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
        DB::table('factura')->insert([['id' => 1, 'estado_venta_id' => 1, 'total' => 46], ['id' => 2, 'estado_venta_id' => 2, 'total' => 11.5]]);
        // Factura 1 cobrada a la mitad (23 de 46 con ISV).
        DB::table('aplicacion_pagos')->insert([
            ['factura_id' => 1, 'credito_abonos' => 23, 'estado' => 1],
            ['factura_id' => 2, 'credito_abonos' => 11.5, 'estado' => 2],
        ]);
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
        // Número fijo de consultas (clasificación de tipos + detalle), no una por oferta.
        $this->assertLessThanOrEqual(7, count($queries));
        $sqls = array_column($queries, 'query');
        $this->assertEmpty(array_filter($sqls, fn ($q) => str_contains($q, 'ORDER BY hf.id DESC LIMIT 1')));
        $this->assertNotEmpty(array_filter($sqls, fn ($q) => str_contains($q, 'MAX(id) AS ultimo_id')));
    }

    private function resumen(array $filtros = []): array
    {
        return (new ReporteExpo)->resumenOfertas(new Request(array_merge(['expo_id' => 7], $filtros)))->getData(true);
    }

    public function test_clasifica_ganadoras_por_ultimo_estado_de_cada_oferta(): void
    {
        DB::table('cotizacion')->insert(['id' => 12, 'nombre_cliente' => 'Cliente 12', 'fecha_emision' => '2026-11-02']);
        DB::table('expo_cotizacion')->insert(['expo_id' => 7, 'cotizacion_id' => 12, 'flujo_id' => 100, 'estado' => 'PENDIENTE_FACTURACION']);
        DB::table('cotizacion_has_producto')->insert(['id' => 12, 'cotizacion_id' => 12, 'cantidad' => 2, 'sub_total' => 20]);
        DB::table('cotizacion_estado')->insert([
            ['flujo_id' => 100, 'cotizacion_id' => 12, 'ganadora' => 1],
            ['flujo_id' => 200, 'cotizacion_id' => 20, 'ganadora' => 1],
            ['flujo_id' => 200, 'cotizacion_id' => 20, 'ganadora' => 2],
        ]);

        $ofertas = collect($this->ofertas())->keyBy('oferta_id');

        $this->assertSame('GANADORA', $ofertas[10]['tipo']);
        $this->assertSame('GANADORA', $ofertas[12]['tipo']);
        $this->assertSame('NO_GANADORA', $ofertas[20]['tipo']);
        $this->assertSame('NO_GANADORA', $ofertas[40]['tipo']);
        $this->assertSame(1, $ofertas[10]['num_secciones']);
        $this->assertSame(11, $ofertas[10]['secciones'][1]['oferta_id']);
        $this->assertSame(1, $ofertas[10]['secciones'][1]['num_facturas']);
        $this->assertSame([10, 12], array_slice(array_column($this->ofertas(), 'oferta_id'), 0, 2));
    }

    public function test_no_ganadora_sin_secciones_de_un_flujo_con_ganadora_se_oculta_en_todo_el_reporte(): void
    {
        // Oferta 15: alternativa que perdió en el flujo 100 (la ganadora es la 10).
        DB::table('cotizacion')->insert(['id' => 15, 'nombre_cliente' => 'Cliente 10', 'fecha_emision' => '2026-11-01']);
        DB::table('expo_cotizacion')->insert(['expo_id' => 7, 'cotizacion_id' => 15, 'flujo_id' => 100, 'estado' => 'PENDIENTE_FACTURACION']);
        DB::table('cotizacion_has_producto')->insert(['id' => 15, 'cotizacion_id' => 15, 'cantidad' => 8, 'sub_total' => 80]);
        // Oferta 16: perdió en el flujo 100 pero conserva una sección, se mantiene.
        DB::table('cotizacion')->insert([
            ['id' => 16, 'nombre_cliente' => 'Cliente 10', 'fecha_emision' => '2026-11-01'],
            ['id' => 17, 'nombre_cliente' => 'Cliente 10', 'fecha_emision' => '2026-11-01'],
        ]);
        DB::table('expo_cotizacion')->insert([
            ['expo_id' => 7, 'cotizacion_id' => 16, 'flujo_id' => 100, 'estado' => 'PENDIENTE_FACTURACION'],
            ['expo_id' => 7, 'cotizacion_id' => 17, 'flujo_id' => 100, 'estado' => 'PENDIENTE_FACTURACION'],
        ]);
        DB::table('cotizacion_has_producto')->insert([
            ['id' => 16, 'cotizacion_id' => 16, 'cantidad' => 1, 'sub_total' => 10],
            ['id' => 17, 'cotizacion_id' => 17, 'cantidad' => 1, 'sub_total' => 10],
        ]);
        DB::table('expo_oferta_seccion')->insert([
            'cotizacion_id' => 17, 'cotizacion_origen_id' => 16, 'numero' => 1, 'nombre' => 'Seccion 1', 'estado' => 'PENDIENTE',
        ]);

        $reporte = new ReporteExpo;
        $request = new Request(['expo_id' => 7]);
        $ids = array_column($reporte->tablaOfertas($request)->getData(true), 'oferta_id');
        $this->assertNotContains(15, $ids);
        $this->assertContains(16, $ids);

        // Sin la 15: 100 (10) + 10 (16) + 30 + 20 + 10 = 170.
        $this->assertEquals(170, $reporte->ventasPorMarca($request)->getData(true)[0]['ofertado']);
        $this->assertEquals(170, array_sum(array_column($reporte->tablaProductos($request)->getData(true), 'total_ofertado')));
        $f = $reporte->resumenOfertas($request)->getData(true)['facturabilidad'];
        $this->assertEquals(170, $f['conjunto']['total_ofertado']);
        $this->assertSame(3, $f['no_ganadoras']['num_ofertas']);
        $noGanadoras = array_column($reporte->tablaOfertas(new Request(['expo_id' => 7, 'tipo_oferta' => 'NO_GANADORA']))->getData(true), 'oferta_id');
        $this->assertNotContains(15, $noGanadoras);
    }

    public function test_ganadora_revocada_que_conserva_secciones_se_separa(): void
    {
        DB::table('cotizacion_estado')->insert(['flujo_id' => 100, 'cotizacion_id' => 10, 'ganadora' => 2]);

        $raiz = collect($this->ofertas())->firstWhere('oferta_id', 10);

        $this->assertSame('NO_GANADORA_CON_SECCIONES', $raiz['tipo']);
        $this->assertFalse($raiz['es_ganadora']);
        $this->assertEquals(40, $raiz['total_facturado']);
    }

    public function test_filtros_se_aplican_a_la_oferta_principal_y_arrastran_sus_secciones(): void
    {
        // La sección tiene fecha y teleasesor propios; manda la oferta principal.
        DB::table('cotizacion')->where('id', 11)->update(['fecha_emision' => '2026-10-01', 'users_id' => 2]);

        $ofertas = collect($this->ofertas(['fecha_desde' => '2026-10-15', 'teleasesor_ids' => [1]]))->keyBy('oferta_id');
        $this->assertSame(1, $ofertas[10]['num_secciones']);
        $this->assertEquals(40, $ofertas[10]['total_facturado']);

        DB::table('cotizacion')->where('id', 10)->update(['fecha_emision' => '2026-10-01']);
        $ofertas = collect($this->ofertas(['fecha_desde' => '2026-10-15']))->keyBy('oferta_id');
        $this->assertArrayNotHasKey(10, $ofertas);
        $this->assertArrayNotHasKey(11, $ofertas);
    }

    public function test_kpis_y_graficas_no_duplican_secciones_y_cuadran_con_la_tabla(): void
    {
        DB::table('cotizacion')->where('id', 11)->update(['users_id' => 2, 'fecha_emision' => '2026-10-01']);
        $request = new Request(['expo_id' => 7]);
        $reporte = new ReporteExpo;

        $kpis = $reporte->kpis($request)->getData(true);
        $this->assertSame(4, $kpis['num_ofertas']);
        $this->assertSame(1, $kpis['num_ganadoras']);
        $this->assertSame(3, $kpis['num_no_ganadoras']);
        $this->assertEquals(160, $kpis['total_oferta_sin_isv']);
        $this->assertEquals(40, $kpis['total_facturado']);
        $this->assertEquals(25, $kpis['avance_pct']);

        $tele = $reporte->ventasPorTeleasesor($request)->getData(true);
        $this->assertCount(1, $tele);
        $this->assertSame(4, $tele[0]['ofertas']);
        $this->assertSame(1, $tele[0]['ofertas_ganadas']);
        $this->assertEquals(160, $tele[0]['ofertado']);
        $this->assertEquals(40, $tele[0]['facturado']);

        $evolucion = collect($reporte->evolucionDiaria($request)->getData(true));
        $this->assertEquals(160, $evolucion->sum('ofertado'));
        $this->assertNull($evolucion->firstWhere('fecha', '2026-10-01'));

        $estado = $reporte->estadoOfertas($request)->getData(true);
        $this->assertSame(4, array_sum(array_column($estado, 'total')));
        $this->assertEquals(40, array_sum(array_column($estado, 'facturado')));

        $productos = $reporte->tablaProductos($request)->getData(true);
        $this->assertEquals(160, array_sum(array_column($productos, 'total_ofertado')));
        $this->assertEquals(40, array_sum(array_column($productos, 'total_facturado')));

        $conjunto = $reporte->resumenOfertas($request)->getData(true)['facturabilidad']['conjunto'];
        $this->assertEquals($kpis['total_oferta_sin_isv'], $conjunto['total_ofertado']);
        $this->assertEquals($kpis['total_facturado'], $conjunto['total_facturado']);
    }

    public function test_filtro_por_tipo_aplica_a_graficas_tablas_y_segmentos(): void
    {
        $reporte = new ReporteExpo;
        $ganadoras = new Request(['expo_id' => 7, 'tipo_oferta' => 'GANADORA']);

        $ofertas = $reporte->tablaOfertas($ganadoras)->getData(true);
        $this->assertSame([10], array_column($ofertas, 'oferta_id'));
        $this->assertSame(1, $ofertas[0]['num_secciones']);

        $resumen = $reporte->resumenOfertas($ganadoras)->getData(true);
        $f = $resumen['facturabilidad'];
        $this->assertSame([10], array_column($resumen['ofertas'], 'oferta_id'));
        $this->assertSame($resumen['clientes'][0]['cliente_id'], $resumen['ofertas'][0]['cliente_id']);
        $this->assertSame(0, $f['no_ganadoras']['num_ofertas']);
        $this->assertEquals(100, $f['conjunto']['total_ofertado']);
        $this->assertEquals(40, $f['conjunto']['total_facturado']);

        $marcas = $reporte->ventasPorMarca($ganadoras)->getData(true);
        $this->assertEquals(100, $marcas[0]['ofertado']);
        $this->assertEquals(40, $marcas[0]['facturado']);
        $productos = $reporte->tablaProductos($ganadoras)->getData(true);
        $this->assertEquals(100, array_sum(array_column($productos, 'total_ofertado')));

        $noGanadoras = new Request(['expo_id' => 7, 'tipo_oferta' => 'NO_GANADORA']);
        $this->assertEqualsCanonicalizing([20, 30, 40], array_column($reporte->tablaOfertas($noGanadoras)->getData(true), 'oferta_id'));
        $marcas = $reporte->ventasPorMarca($noGanadoras)->getData(true);
        $this->assertEquals(60, $marcas[0]['ofertado']);
        $this->assertEquals(0, $marcas[0]['facturado']);

        $this->assertSame([], $reporte->tablaOfertas(new Request(['expo_id' => 7, 'tipo_oferta' => 'NO_GANADORA_CON_SECCIONES']))->getData(true));
        $this->assertCount(4, $reporte->tablaOfertas(new Request(['expo_id' => 7, 'tipo_oferta' => 'otro']))->getData(true));
    }

    public function test_kpis_por_segmento_incluyen_facturas_utilidad_y_pagos_sin_isv(): void
    {
        $f = (new ReporteExpo)->resumenOfertas(new Request(['expo_id' => 7]))->getData(true)['facturabilidad'];

        $g = $f['ganadoras'];
        $this->assertSame([1, 1, 1], [$g['num_ofertas'], $g['num_secciones'], $g['num_facturas']]);
        $this->assertEquals(100, $g['total_ofertado']);
        $this->assertEquals(40, $g['total_facturado']);
        $this->assertEquals(40, $g['avance_pct']);
        $this->assertEquals(60, $g['utilidad_a_ganar']);
        $this->assertEquals(24, $g['utilidad_facturada']);
        $this->assertEquals(20, $g['total_pagado']);
        $this->assertEquals(50, $g['pagado_pct']);
        $this->assertEquals(12, $g['utilidad_pagada']);
        $this->assertEquals(20, $g['utilidad_ganada_pct']);

        $n = $f['no_ganadoras'];
        $this->assertSame([3, 0, 0], [$n['num_ofertas'], $n['num_secciones'], $n['num_facturas']]);
        $this->assertEquals(60, $n['total_ofertado']);
        $this->assertEquals(36, $n['utilidad_a_ganar']);
        $this->assertEquals(0, $n['total_pagado']);

        $c = $f['conjunto'];
        $this->assertSame([4, 1, 1], [$c['num_ofertas'], $c['num_secciones'], $c['num_facturas']]);
        $this->assertEquals(160, $c['total_ofertado']);
        $this->assertEquals(96, $c['utilidad_a_ganar']);
        $this->assertEquals(20, $c['total_pagado']);
        $this->assertEquals(12, $c['utilidad_pagada']);
    }

    public function test_resumen_calcula_facturabilidad_por_tipo_y_ofertas_por_cliente(): void
    {
        DB::table('cotizacion')->where('id', 20)->update(['cliente_id' => 2]);

        $resumen = $this->resumen();
        $f = $resumen['facturabilidad'];

        $this->assertSame(['num_ofertas' => 1, 'num_secciones' => 1, 'total_ofertado' => 100, 'total_facturado' => 40, 'facturabilidad_pct' => 40],
            array_intersect_key($f['ganadoras'], array_flip(['num_ofertas', 'num_secciones', 'total_ofertado', 'total_facturado', 'facturabilidad_pct'])));
        $this->assertSame(3, $f['no_ganadoras']['num_ofertas']);
        $this->assertEquals(60, $f['no_ganadoras']['total_ofertado']);
        $this->assertEquals(0, $f['no_ganadoras']['facturabilidad_pct']);
        $this->assertSame(0, $f['no_ganadoras_con_secciones']['num_ofertas']);
        $this->assertSame(4, $f['conjunto']['num_ofertas']);
        $this->assertEquals(160, $f['conjunto']['total_ofertado']);
        $this->assertEquals(40, $f['conjunto']['total_facturado']);
        $this->assertEquals(25, $f['conjunto']['facturabilidad_pct']);

        $this->assertCount(2, $resumen['clientes']);
        $cliente = $resumen['clientes'][0];
        $this->assertSame(1, $cliente['cliente_id']);
        $this->assertSame(3, $cliente['num_ofertas']);
        $this->assertSame(1, $cliente['ganadoras']);
        $this->assertSame(2, $cliente['no_ganadoras']);
        $this->assertSame(1, $cliente['num_secciones']);
        $this->assertEquals(130, $cliente['total_ofertado']);
        $this->assertEquals(30.77, $cliente['facturabilidad_pct']);
        $this->assertSame(['cliente_id' => 2, 'num_ofertas' => 1, 'ganadoras' => 0, 'no_ganadoras' => 1],
            array_intersect_key($resumen['clientes'][1], array_flip(['cliente_id', 'num_ofertas', 'ganadoras', 'no_ganadoras'])));
    }
}
