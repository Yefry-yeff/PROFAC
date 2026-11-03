<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\DashboardComercial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardComercialTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.dashboard_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        $app['config']->set('database.default', 'dashboard_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-02 12:00:00');
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('rol_id'); });
        Schema::create('rol', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('usuario_rol', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('rol_id'); });
        Schema::create('usuario_submenu', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('sub_menu_id'); $t->boolean('permitido'); });
        Schema::create('sub_menu', function (Blueprint $t) { $t->id(); $t->string('url'); $t->integer('estado_id')->default(1); $t->integer('orden')->default(1); });
        Schema::create('rol_submenu', function (Blueprint $t) { $t->integer('rol_id'); $t->integer('sub_menu_id'); });
        Schema::create('cliente', function (Blueprint $t) { $t->id(); $t->string('nombre'); $t->integer('vendedor'); $t->dateTime('created_at'); $t->integer('estado_cliente_id')->default(1); });
        Schema::create('factura', function (Blueprint $t) {
            $t->id(); $t->string('cai')->nullable(); $t->integer('cliente_id'); $t->integer('vendedor');
            $t->integer('estado_venta_id'); $t->dateTime('fecha_emision'); $t->decimal('total', 14, 2);
        });
        Schema::create('producto', function (Blueprint $t) { $t->id(); $t->string('nombre'); $t->string('codigo_barra'); $t->integer('estado_producto_id'); });
        Schema::create('img_producto', function (Blueprint $t) { $t->id(); $t->integer('producto_id'); });
        Schema::create('cardex', function (Blueprint $t) { $t->id(); $t->integer('id_producto'); $t->decimal('cantidad', 14, 2); });
        Schema::create('venta_has_producto', function (Blueprint $t) { $t->id(); $t->integer('producto_id'); $t->integer('factura_id'); });
        Schema::create('compra', function (Blueprint $t) { $t->id(); $t->dateTime('fecha_emision'); $t->integer('estado_compra_id'); });
        Schema::create('compra_has_producto', function (Blueprint $t) { $t->id(); $t->integer('producto_id'); $t->integer('compra_id'); });
        Schema::create('recibido_bodega', function (Blueprint $t) {
            $t->id(); $t->integer('producto_id'); $t->integer('compra_id')->nullable();
            $t->date('fecha_recibido')->nullable(); $t->decimal('cantidad_disponible', 14, 2);
        });
        foreach ([1, 2, 4, 5, 6, 8, 9, 18, 19] as $rol) {
            DB::table('rol')->insert(['id' => $rol, 'nombre' => "Rol {$rol}"]);
        }
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Administrador', 'rol_id' => 1],
            ['id' => 2, 'name' => 'Asesor prueba', 'rol_id' => 2],
        ]);
        foreach (['reportes/analisis_de_negocio', 'reportes/cartera_de_cobros', 'reporte/dashboard-ventas'] as $i => $ruta) {
            DB::table('sub_menu')->insert(['id' => $i + 1, 'url' => $ruta]);
            DB::table('usuario_submenu')->insert(['usuario_id' => 2, 'sub_menu_id' => $i + 1, 'permitido' => true]);
        }
        DB::table('cliente')->insert([
            ['id' => 1, 'nombre' => 'Cliente antiguo', 'vendedor' => 1, 'created_at' => '2025-01-01'],
            ['id' => 2, 'nombre' => 'Creado sin compra', 'vendedor' => 2, 'created_at' => '2026-11-02 23:59:59'],
            ['id' => 3, 'nombre' => 'Creado otro mes', 'vendedor' => 2, 'created_at' => '2026-12-01 00:00:00'],
        ]);
        DB::table('factura')->insert([
            ['id' => 1, 'cai' => 'CAI-001', 'cliente_id' => 1, 'vendedor' => 1, 'estado_venta_id' => 1, 'fecha_emision' => '2026-11-30 23:59:59', 'total' => 100],
            ['id' => 2, 'cai' => 'CAI-002', 'cliente_id' => 1, 'vendedor' => 2, 'estado_venta_id' => 1, 'fecha_emision' => '2026-11-01', 'total' => 200],
            ['id' => 3, 'cai' => 'CAI-003', 'cliente_id' => 1, 'vendedor' => 2, 'estado_venta_id' => 2, 'fecha_emision' => '2026-11-01', 'total' => 300],
            ['id' => 4, 'cai' => 'CAI-004', 'cliente_id' => 1, 'vendedor' => 1, 'estado_venta_id' => 1, 'fecha_emision' => '2026-12-01', 'total' => 400],
        ]);
        for ($i = 1; $i <= 27; $i++) {
            DB::table('producto')->insert(['id' => $i, 'nombre' => "Producto {$i}", 'codigo_barra' => "P-{$i}", 'estado_producto_id' => $i === 27 ? 2 : 1]);
            DB::table('cardex')->insert(['id_producto' => $i, 'cantidad' => $i === 26 ? 10 : 9.5]);
        }
        $this->actingAs(User::findOrFail(1));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_kpis_match_invoice_and_customer_modals_including_end_of_day_and_clients_without_purchases(): void
    {
        Livewire::test(DashboardComercialFixture::class)
            ->assertViewHas('kpis', fn ($k) => $k['num_facturas'] === 2 && $k['venta_total'] === 300.0 && $k['clientes_nuevos'] === 2)
            ->assertSee('Saldo pendiente de pago')->assertDontSee('Ticket promedio')
            ->call('abrirDetalle', 'facturas')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 2)
            ->assertSee('CAI-001')->assertSee('CAI-002')->assertDontSee('CAI-003')->assertDontSee('CAI-004')
            ->call('cerrarDetalle')->assertSet('detalleKpi', '')
            ->call('abrirDetalle', 'clientes_nuevos')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 2)
            ->assertSee('Creado sin compra')
            ->call('abrirDetalle', 'clientes')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 1);
    }

    public function test_inactive_customers_and_products_are_excluded_from_kpis_details_search_and_excel(): void
    {
        DB::table('cliente')->insert([
            ['id' => 4, 'nombre' => 'Cliente inactivo antiguo', 'vendedor' => 1, 'created_at' => '2020-01-01', 'estado_cliente_id' => 2],
            ['id' => 5, 'nombre' => 'Cliente activo antiguo', 'vendedor' => 1, 'created_at' => '2020-01-01', 'estado_cliente_id' => 1],
            ['id' => 6, 'nombre' => 'Cliente inactivo reciente', 'vendedor' => 1, 'created_at' => '2020-01-01', 'estado_cliente_id' => 2],
        ]);
        foreach ([4, 5, 6] as $id) {
            DB::table('factura')->insert(['id' => $id + 10, 'cliente_id' => $id, 'vendedor' => 1,
                'estado_venta_id' => 1, 'fecha_emision' => $id === 6 ? '2026-11-01' : '2020-01-02', 'total' => 50]);
        }
        $componente = Livewire::test(DashboardComercialFixture::class)
            ->assertViewHas('kpisGenerales', fn ($k) => $k['clientes_act'] === 2
                && $k['clientes_nuevos'] === 3 && $k['requieren_atencion'] === 1 && $k['poco_movimiento'] === 26);
        foreach (['clientes' => 2, 'clientes_nuevos' => 3, 'atencion' => 1, 'inventario' => 25, 'poco_movimiento' => 26] as $tipo => $total) {
            $componente->call('abrirDetalle', $tipo)
                ->assertViewHas('detalleFilas', fn ($p) => $p->total() === $total)
                ->call('descargarDetalle');
            $hoja = $this->leerExcel($componente)->getSheet(0);
            $this->assertSame($total + 1, $hoja->getHighestRow());
            $contenido = json_encode($hoja->toArray());
            $this->assertStringNotContainsString('Cliente inactivo', $contenido);
            $this->assertStringNotContainsString('Producto 27', $contenido);
            $componente->set('buscarDetalle', in_array($tipo, ['inventario', 'poco_movimiento'], true) ? 'P-27' : 'Cliente inactivo')
                ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 0)
                ->call('descargarDetalle');
            $filas = $this->leerExcel($componente)->getSheet(0)->toArray();
            $this->assertCount(0, array_filter(array_slice($filas, 1), fn ($fila) => count(array_filter($fila, fn ($valor) => $valor !== null)) > 0));
        }
    }

    public function test_products_without_images_detail_and_excel_match_kpi_and_exclude_inactive_products(): void
    {
        DB::table('img_producto')->insert([['producto_id' => 1], ['producto_id' => 1]]);
        $componente = Livewire::test(DashboardComercialFixture::class)
            ->assertViewHas('kpisInventario', fn ($k) => $k['sin_imagen'] === 25)
            ->call('abrirDetalle', 'sin_imagen')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 25 && $p->count() === 20 && $p->first()->id === 2)
            ->call('gotoPage', 2, 'detallePage')
            ->assertViewHas('detalleFilas', fn ($p) => $p->count() === 5)
            ->assertDontSee('Producto 27')
            ->call('descargarDetalle')->assertFileDownloaded('Dashboard-sin_imagen-2026-11-02.xlsx');
        $hoja = $this->leerExcel($componente)->getSheetByName('Productos sin imagen');
        $this->assertSame(26, $hoja->getHighestRow());
        $this->assertSame('P-2', $hoja->getCell('B2')->getValue());
        $componente->set('buscarDetalle', 'P-26')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 1 && $p->currentPage() === 1)
            ->call('descargarDetalle');
        $this->assertSame(2, $this->leerExcel($componente)->getSheet(0)->getHighestRow());
        foreach (['P-1', 'P-27'] as $busqueda) {
            $componente->set('buscarDetalle', $busqueda)
                ->assertViewHas('detalleFilas', fn ($p) => !$p->getCollection()->contains(fn ($r) => in_array($r->id, [1, 27], true)));
        }
        $this->actingAs(User::findOrFail(2));
        Livewire::test(DashboardComercialFixture::class)->call('abrirDetalle', 'sin_imagen')->assertForbidden();
    }

    public function test_low_stock_modal_includes_all_matching_products_with_pagination_and_search(): void
    {
        Livewire::test(DashboardComercialFixture::class)
            ->assertViewHas('kpisInventario', fn ($k) => $k['bajos_stock'] === 25)
            ->call('abrirDetalle', 'inventario')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 25 && $p->count() === 20)
            ->call('gotoPage', 2, 'detallePage')
            ->assertViewHas('detalleFilas', fn ($p) => $p->currentPage() === 2 && $p->count() === 5)
            ->assertSee('Producto 25')->assertDontSee('Producto 26')->assertDontSee('Producto 27')
            ->set('buscarDetalle', 'P-25')
            ->assertViewHas('detalleFilas', fn ($p) => $p->currentPage() === 1 && $p->total() === 1)
            ->assertSee('9.50');
    }

    public function test_historical_kpis_ignore_selected_dates_and_top_limit_and_sales_is_first(): void
    {
        DB::table('cliente')->insert(['id' => 4, 'nombre' => 'Historico sin compra reciente', 'vendedor' => 1, 'created_at' => '2020-01-01']);
        DB::table('factura')->insert(['id' => 5, 'cai' => 'HISTORICA', 'cliente_id' => 4, 'vendedor' => 1, 'estado_venta_id' => 1, 'fecha_emision' => '2020-01-02', 'total' => 50]);
        Livewire::test(DashboardComercialFixture::class)
            ->assertSeeInOrder(['Venta total', 'Saldo pendiente de pago'])
            ->assertViewHas('kpisGenerales', fn ($k) => $k['clientes_act'] === 2 && $k['clientes_nuevos'] === 3 && $k['requieren_atencion'] === 1)
            ->set('filtroMes', 1)->set('topLimit', 50)
            ->assertViewHas('kpisGenerales', fn ($k) => $k['clientes_act'] === 2 && $k['clientes_nuevos'] === 3 && $k['requieren_atencion'] === 1)
            ->call('abrirDetalle', 'clientes')->assertSee('Historico sin compra reciente')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 2)
            ->call('abrirDetalle', 'clientes_nuevos')->assertViewHas('detalleFilas', fn ($p) => $p->total() === 3);
    }

    public function test_slow_products_use_exact_90_day_boundary_active_sales_and_current_warehouse_stock(): void
    {
        foreach ([
            [5, now()->startOfDay()->subDays(90)->toDateTimeString(), 1],
            [6, now()->startOfDay()->subDays(90)->subSecond()->toDateTimeString(), 1],
            [7, '2026-11-01', 2],
            [8, '2026-11-03', 1],
        ] as [$id, $fecha, $estado]) {
            DB::table('factura')->insert(['id' => $id, 'cai' => "TEST-{$id}", 'cliente_id' => 1, 'vendedor' => 1, 'estado_venta_id' => $estado, 'fecha_emision' => $fecha, 'total' => 10]);
            DB::table('venta_has_producto')->insert(['producto_id' => $id - 4, 'factura_id' => $id]);
        }
        DB::table('recibido_bodega')->insert([['producto_id' => 2, 'cantidad_disponible' => 2.5], ['producto_id' => 2, 'cantidad_disponible' => 5]]);
        DB::table('compra')->insert([
            ['id' => 1, 'fecha_emision' => '2026-07-01', 'estado_compra_id' => 1],
            ['id' => 2, 'fecha_emision' => '2026-10-01', 'estado_compra_id' => 2],
        ]);
        DB::table('compra_has_producto')->insert([['producto_id' => 2, 'compra_id' => 1], ['producto_id' => 2, 'compra_id' => 2]]);
        $componente = Livewire::test(DashboardComercialFixture::class);
        $this->assertSame(25, $componente->viewData('kpisGenerales')['poco_movimiento']);
        $componente
            ->set('filtroMes', 1)
            ->call('abrirDetalle', 'poco_movimiento')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 25 && $p->count() === 20)
            ->assertSee('Fecha de última compra')->assertSee('Fecha de última venta')->assertSee('Sin ventas registradas')
            ->set('buscarDetalle', 'P-2')
            ->assertViewHas('detalleFilas', fn ($p) => $p->getCollection()->contains(fn ($r) => $r->id === 2 && (float) $r->existencia === 7.5 && $r->ultima_compra === '2026-07-01'))
            ->assertSee('01/07/2026')->assertSee('7.50')
            ->set('buscarDetalle', 'P-1')
            ->assertViewHas('detalleFilas', fn ($p) => !$p->getCollection()->contains('id', 1));
    }

    public function test_dates_refresh_the_period_and_close_stale_modals_and_invalid_ranges_are_explicit(): void
    {
        Livewire::test(DashboardComercialFixture::class)
            ->call('abrirDetalle', 'facturas')
            ->set('filtroMes', 10)
            ->assertSet('detalleKpi', '')
            ->assertSet('fechaInicio', '2026-10-01')->assertSet('fechaFin', '2026-10-31')
            ->assertViewHas('kpis', fn ($k) => $k['num_facturas'] === 0)
            ->set('fechaInicio', '2026-10-02')
            ->assertSet('periodoLabel', '02/10/2026 al 31/10/2026')
            ->set('fechaFin', '2026-10-01')
            ->assertViewHas('periodoValido', false)->assertSee('Selecciona fechas válidas');
    }

    public function test_warehouse_receipts_without_purchase_are_identified_and_purchase_receipts_also_resolve_dates(): void
    {
        DB::table('recibido_bodega')->insert([
            ['producto_id' => 1, 'compra_id' => null, 'fecha_recibido' => '2026-04-09', 'cantidad_disponible' => 35],
            ['producto_id' => 2, 'compra_id' => 1, 'fecha_recibido' => '2026-07-02', 'cantidad_disponible' => 12],
            ['producto_id' => 1, 'compra_id' => null, 'fecha_recibido' => '2026-05-01', 'cantidad_disponible' => 0],
        ]);
        DB::table('compra')->insert(['id' => 1, 'fecha_emision' => '2026-07-01', 'estado_compra_id' => 1]);
        Livewire::test(DashboardComercialFixture::class)
            ->call('abrirDetalle', 'poco_movimiento')
            ->assertViewHas('detalleFilas', fn ($p) => $p->getCollection()->contains(fn ($r) => $r->id === 1
                && $r->ultima_compra === null && (float) $r->existencia_sin_compra === 35.0 && $r->ultimo_ingreso_sin_compra === '2026-04-09'))
            ->assertViewHas('detalleFilas', fn ($p) => $p->getCollection()->contains(fn ($r) => $r->id === 2
                && $r->ultima_compra === '2026-07-01' && (float) $r->existencia === 12.0 && (float) $r->existencia_sin_compra === 0.0))
            ->assertSee('Sin compra vinculada')->assertSee('09/04/2026');
    }

    public function test_attention_modal_and_excel_match_kpi_without_top_limit_and_preserve_monthly_rule(): void
    {
        for ($i = 4; $i <= 27; $i++) {
            DB::table('cliente')->insert(['id' => $i, 'nombre' => "Cliente atención {$i}", 'vendedor' => 1, 'created_at' => '2020-01-01']);
            DB::table('factura')->insert(['id' => $i + 1, 'cai' => "A-{$i}", 'cliente_id' => $i, 'vendedor' => 1,
                'estado_venta_id' => 1, 'fecha_emision' => '2020-01-02', 'total' => 50]);
        }
        DB::table('factura')->insert(['id' => 50, 'cai' => 'OCT', 'cliente_id' => 1, 'vendedor' => 1,
            'estado_venta_id' => 1, 'fecha_emision' => '2026-10-10', 'total' => 300]);
        $componente = Livewire::test(DashboardComercialFixture::class)
            ->assertSee('Clientes que requieren atención')
            ->assertViewHas('kpisGenerales', fn ($k) => $k['requieren_atencion'] === 25)
            ->call('abrirDetalle', 'atencion')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 25 && $p->count() === 20)
            ->assertSee('Sin compra por más de 90 días')
            ->call('gotoPage', 2, 'detallePage')
            ->assertSee('Caída mensual de al menos 20%')
            ->call('descargarDetalle')->assertFileDownloaded('Dashboard-atencion-2026-11-02.xlsx');
        $libro = $this->leerExcel($componente);
        $hoja = $libro->getSheetByName('Clientes requieren atencion');
        $this->assertSame(26, $hoja->getHighestRow());
        $this->assertSame('Caída mensual de al menos 20%', $hoja->getCell('H26')->getValue());
        $this->assertEqualsWithDelta(-33.33, $hoja->getCell('G26')->getValue(), 0.01);
        $this->assertNotNull($libro->getSheetByName('Criterios'));
        $componente->set('buscarDetalle', 'Cliente atención 27')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 1)
            ->call('descargarDetalle');
        $this->assertSame(2, $this->leerExcel($componente)->getSheet(0)->getHighestRow());
    }

    public function test_every_detail_exports_all_filtered_rows_and_keeps_excel_text_safe(): void
    {
        DB::table('producto')->where('id', 1)->update(['nombre' => '=NO_ES_FORMULA', 'codigo_barra' => '000001']);
        $componente = Livewire::test(DashboardComercialFixture::class);
        foreach ([
            'facturas' => ['Facturas emitidas', 2],
            'clientes' => ['Clientes activos', 1],
            'clientes_nuevos' => ['Clientes creados', 2],
            'inventario' => ['Inventario bajo', 25],
            'poco_movimiento' => ['Productos poco movimiento', 26],
        ] as $tipo => [$hoja, $total]) {
            $componente->call('abrirDetalle', $tipo)->call('descargarDetalle')
                ->assertFileDownloaded("Dashboard-{$tipo}-2026-11-02.xlsx");
            $libro = $this->leerExcel($componente);
            $this->assertSame($total + 1, $libro->getSheetByName($hoja)->getHighestRow());
            if ($tipo === 'inventario') {
                $this->assertSame('000001', $libro->getSheet(0)->getCell('B2')->getValue());
                $this->assertSame('=NO_ES_FORMULA', $libro->getSheet(0)->getCell('C2')->getValue());
                $this->assertSame('s', $libro->getSheet(0)->getCell('C2')->getDataType());
                $this->assertEquals(9.5, $libro->getSheet(0)->getCell('D2')->getValue());
            }
        }
    }

    private function leerExcel($componente): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $archivo = tempnam(sys_get_temp_dir(), 'dashboard-test-');
        try {
            file_put_contents($archivo, base64_decode($componente->effects['download']['content']));
            return \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
        } finally {
            unlink($archivo);
        }
    }

    public function test_stock_threshold_includes_zero_and_missing_movements_but_not_negative_or_ten_units(): void
    {
        DB::table('cardex')->where('id_producto', 1)->update(['cantidad' => 0]);
        DB::table('cardex')->where('id_producto', 2)->delete();
        DB::table('cardex')->where('id_producto', 3)->update(['cantidad' => -1]);
        Livewire::test(DashboardComercialFixture::class)
            ->assertViewHas('kpisInventario', fn ($k) => $k['bajos_stock'] === 24)
            ->call('abrirDetalle', 'inventario')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 24 && $p->first()->id === 1)
            ->set('buscarDetalle', 'P-2')
            ->assertViewHas('detalleFilas', fn ($p) => $p->getCollection()->contains(fn ($r) => $r->id === 2 && (float) $r->stock === 0.0));
    }

    public function test_salesperson_detail_is_scoped_even_if_the_filter_is_tampered_with(): void
    {
        $this->actingAs(User::findOrFail(2));
        Livewire::test(DashboardComercialFixture::class)
            ->set('filtroVendedorId', 1)
            ->call('abrirDetalle', 'facturas')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 1 && $p->first()->id === 2)
            ->call('abrirDetalle', 'clientes_nuevos')
            ->assertViewHas('detalleFilas', fn ($p) => $p->total() === 1 && $p->first()->id === 2);
        Livewire::test(DashboardComercialFixture::class)->call('abrirDetalle', 'inventario')->assertForbidden();
        Livewire::test(DashboardComercialFixture::class)->call('abrirDetalle', 'poco_movimiento')->assertForbidden();
    }

    public function test_report_links_require_both_permissions_and_cobros_role_cannot_open_commercial_details(): void
    {
        DB::table('users')->where('id', 2)->update(['rol_id' => 4]);
        $this->actingAs(User::findOrFail(2));
        $component = new DashboardComercial;
        $this->assertStringEndsWith('?modulo=cartera', $component->enlaceAnalisis('cartera'));
        $this->assertStringEndsWith('?modulo=ventas', $component->enlaceAnalisis('ventas'));
        DB::table('usuario_submenu')->where('usuario_id', 2)->where('sub_menu_id', 3)->update(['permitido' => false]);
        $this->assertNull($component->enlaceAnalisis('ventas'));
        DB::table('usuario_submenu')->where('usuario_id', 2)->where('sub_menu_id', 1)->update(['permitido' => false]);
        $this->assertNull($component->enlaceAnalisis('cartera'));
        Livewire::test(DashboardComercialFixture::class)
            ->assertSee('Saldo pendiente de pago')->assertDontSee('Productos bajos de inventario')
            ->call('abrirDetalle', 'facturas')->assertForbidden();
    }

    public function test_cobros_profile_shows_portfolio_summary_and_link_only_with_report_permissions(): void
    {
        DB::table('users')->where('id', 2)->update(['rol_id' => 4]);
        $this->actingAs(User::findOrFail(2));
        Livewire::test(DashboardComercialFixture::class)
            ->assertSee('Control de cartera')
            ->assertSee('Recuperación de cartera')
            ->assertSee(url('reportes/analisis_de_negocio') . '?modulo=cartera', false);
        DB::table('usuario_submenu')->where('usuario_id', 2)->where('sub_menu_id', 2)->update(['permitido' => false]);
        Livewire::test(DashboardComercialFixture::class)
            ->assertSee('Control de cartera')
            ->assertDontSee(url('reportes/analisis_de_negocio') . '?modulo=cartera', false);
    }

    public function test_download_rechecks_permissions_and_salesperson_exports_only_their_invoices(): void
    {
        $this->actingAs(User::findOrFail(2));
        $componente = Livewire::test(DashboardComercialFixture::class)
            ->set('filtroVendedorId', 1)->call('abrirDetalle', 'facturas')->call('descargarDetalle');
        $hoja = $this->leerExcel($componente)->getSheet(0);
        $this->assertSame(2, $hoja->getHighestRow());
        $this->assertSame('CAI-002', $hoja->getCell('B2')->getValue());
        $this->actingAs(User::findOrFail(1));
        $componente = Livewire::test(DashboardComercialFixture::class)->call('abrirDetalle', 'poco_movimiento');
        DB::table('users')->where('id', 1)->update(['rol_id' => 4]);
        $this->actingAs(User::findOrFail(1));
        $componente->call('descargarDetalle')->assertForbidden();
    }
}

class DashboardComercialFixture extends DashboardComercial
{
    public function getVendedores(): array { return []; }
    public function getTopClientes(): array { return []; }
    public function getTendenciaMensual(): array { return []; }
    public function getProductividadVendedores(): array { return []; }
    public function getParticipacionMercado(): array { return []; }
    public function getComparativaMesAnterior(): array { return []; }
    public function getKpisCobros(): array { return ['saldo_pendiente' => 1000]; }
    public function getKpisInventario(): array
    {
        return [
            'bajos_stock' => DB::query()->fromSub($this->productosBajoInventario(), 'bajos')->count(),
            'sin_imagen' => $this->productosSinImagen()->count(),
        ];
    }
}
