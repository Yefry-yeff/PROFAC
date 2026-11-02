<?php

namespace Tests\Feature;

use App\Livewire\Reportes\CarteraDeCobros;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class CarteraDeCobrosTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.cartera_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        $app['config']->set('database.default', 'cartera_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-02 12:00:00'));
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('rol_id')->default(1); });
        Schema::create('rol', fn (Blueprint $t) => $t->id());
        Schema::create('usuario_rol', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('rol_id'); });
        Schema::create('usuario_submenu', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('sub_menu_id'); $t->boolean('permitido'); });
        Schema::create('sub_menu', function (Blueprint $t) { $t->id(); $t->string('url'); $t->integer('estado_id')->default(1); $t->integer('orden')->default(1); });
        Schema::create('rol_submenu', function (Blueprint $t) { $t->integer('rol_id'); $t->integer('sub_menu_id'); });
        Schema::create('cliente', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('factura', function (Blueprint $t) {
            $t->id(); $t->integer('cliente_id'); $t->string('cai'); $t->integer('numero_secuencia_cai');
            $t->date('fecha_emision')->nullable(); $t->date('fecha_vencimiento')->nullable();
            $t->integer('vendedor')->nullable(); $t->integer('users_id')->nullable(); $t->integer('gestor_entrega')->nullable();
            $t->integer('estado_venta_id')->default(1); $t->integer('tipo_venta_id')->default(1);
            $t->decimal('total', 12, 2)->default(100); $t->decimal('sub_total', 12, 2)->default(100);
        });
        Schema::create('aplicacion_pagos', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado')->default(1);
            $t->integer('estado_cerrado')->nullable(); $t->decimal('saldo', 12, 2);
            $t->decimal('total_factura_cargo', 12, 2)->nullable();
        });
        Schema::create('abonos_creditos', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado_abono')->default(1);
            $t->decimal('monto_abonado', 12, 2); $t->dateTime('fecha_pago');
            $t->integer('usr_registro')->nullable();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'Ana'], ['id' => 2, 'name' => 'Luis'], ['id' => 3, 'name' => 'Maria']]);
        DB::table('cliente')->insert([['id' => 1, 'nombre' => 'Cliente A'], ['id' => 2, 'nombre' => 'Cliente B']]);
        DB::table('usuario_submenu')->insert(['usuario_id' => 1, 'sub_menu_id' => 1, 'permitido' => true]);
        DB::table('sub_menu')->insert(['id' => 1, 'url' => 'reportes/cartera_de_cobros']);
        $this->actingAs(User::findOrFail(1));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function factura(int $id, array $valores = [], ?string $saldo = '100.00', array $cuenta = []): void
    {
        DB::table('factura')->insert(array_merge([
            'id' => $id, 'cliente_id' => 1, 'cai' => 'F-' . $id, 'numero_secuencia_cai' => $id,
            'fecha_emision' => '2026-01-01', 'fecha_vencimiento' => '2026-10-01',
            'vendedor' => 1, 'users_id' => 2, 'gestor_entrega' => 3,
        ], $valores));
        if ($saldo !== null) {
            DB::table('aplicacion_pagos')->insert(array_merge([
                'factura_id' => $id, 'saldo' => $saldo, 'total_factura_cargo' => '100.00',
            ], $cuenta));
        }
    }

    public function test_current_balance_rules_include_unsynchronized_invoices_without_writes(): void
    {
        $this->factura(1, ['total' => 150], '60.00');
        $this->factura(2, [], null);
        $this->factura(3, ['estado_venta_id' => 0]);
        $this->factura(4, [], '100', ['estado_cerrado' => 2]);
        $this->factura(5, [], '0');
        $this->factura(6, [], '-10');
        $this->factura(7, ['tipo_venta_id' => 3, 'sub_total' => 80, 'total' => 92], '50');

        Livewire::test(CarteraDeCobros::class)
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 24000 && $m['facturas'] === 3)
            ->assertSee('L 240.00');
        $this->assertSame(6, DB::table('aplicacion_pagos')->count());
        $this->assertEquals(60, DB::table('aplicacion_pagos')->where('factura_id', 1)->value('saldo'));
    }

    public function test_aging_boundaries_missing_dates_and_weighted_mora_are_exact(): void
    {
        foreach ([0, 1, 30, 31, 60, 61, 90, 91, 180, 181] as $i => $dias) {
            $this->factura($i + 1, ['fecha_vencimiento' => CarbonImmutable::today()->subDays($dias)->toDateString()]);
        }
        $this->factura(11, ['fecha_vencimiento' => null, 'vendedor' => null]);
        Livewire::test(CarteraDeCobros::class)
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 110000 && $m['vencido'] === 90000
                && $m['corriente'] === 10000 && $m['sinFecha'] === 10000 && $m['critica'] === 30000 && $m['mora'] === 80.6)
            ->call('seleccionar', 'tramo', '1-30')
            ->assertViewHas('metricas', fn ($m) => $m['facturas'] === 2)
            ->call('seleccionar', 'tramo', '1-30')
            ->assertViewHas('metricas', fn ($m) => $m['facturas'] === 11)
            ->call('seleccionar', 'estado', 'sin-fecha')
            ->assertSee('Sin asignar')
            ->assertViewHas('metricas', fn ($m) => $m['facturas'] === 1);
    }

    public function test_clicks_cross_filter_all_panels_and_reset_restores_portfolio(): void
    {
        $this->factura(1, [], '300');
        $this->factura(2, ['cliente_id' => 2, 'vendedor' => 2, 'gestor_entrega' => 1], '200');
        $this->factura(3, ['cliente_id' => 2, 'vendedor' => 1, 'fecha_vencimiento' => '2026-12-01'], '50');
        Livewire::test(CarteraDeCobros::class)
            ->call('seleccionar', 'cliente', '2')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 25000 && $m['clientes'] === 1)
            ->assertViewHas('responsables', fn ($r) => $r['asesor']['filas']->sum('saldo') === 25000)
            ->call('seleccionar', 'asesor', '1')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 5000 && $m['vencido'] === 0)
            ->assertViewHas('meses', fn ($m) => $m->keys()->all() === ['2026-12'])
            ->call('limpiar')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 55000)
            ->set('buscar', 'F-2')
            ->assertViewHas('detalle', fn ($d) => $d->total() === 1)
            ->set('buscar', 'No existe')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 0 && $m['mora'] === 0)
            ->assertSee('No hay deuda pendiente');
    }

    public function test_oldest_debt_and_pagination_use_all_filtered_invoices(): void
    {
        for ($id = 1; $id <= 25; $id++) {
            $this->factura($id, ['fecha_emision' => $id === 25 ? '2020-01-01' : '2026-01-01']);
        }
        Livewire::test(CarteraDeCobros::class)
            ->assertViewHas('antigua', fn ($f) => $f->id === 25)
            ->assertViewHas('detalle', fn ($d) => $d->total() === 25 && $d->count() === 20)
            ->call('setPage', 2)
            ->assertViewHas('detalle', fn ($d) => $d->count() === 5)
            ->set('orden', 'antiguedad')
            ->assertViewHas('detalle', fn ($d) => $d->currentPage() === 1 && $d->first()->id === 25);
    }

    public function test_responsible_and_month_selections_update_every_aggregate(): void
    {
        $this->factura(1, [], '250');
        $this->factura(2, ['users_id' => 3, 'gestor_entrega' => 2], '150');
        $this->factura(3, ['fecha_vencimiento' => '2026-09-01'], '75');

        Livewire::test(CarteraDeCobros::class)
            ->call('seleccionar', 'teleasesor', '2')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 32500)
            ->call('seleccionar', 'gestor', '3')
            ->call('seleccionar', 'mes', '2026-09')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 7500 && $m['facturas'] === 1)
            ->assertViewHas('clientes', fn ($c) => $c->sum('saldo') === 7500)
            ->assertViewHas('tramos', fn ($t) => $t->sum('saldo') === 7500)
            ->assertViewHas('detalle', fn ($d) => $d->first()->id === 3)
            ->call('seleccionar', 'mes', '2026-09')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 32500);
    }

    public function test_multiple_payment_accounts_do_not_duplicate_invoice_debt(): void
    {
        $this->factura(1);
        DB::table('aplicacion_pagos')->insert([
            'factura_id' => 1, 'saldo' => 40, 'total_factura_cargo' => 100,
        ]);
        Livewire::test(CarteraDeCobros::class)
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 4000 && $m['facturas'] === 1);
    }

    public function test_filter_action_rejects_unknown_dimensions(): void
    {
        Livewire::test(CarteraDeCobros::class)
            ->call('seleccionar', 'titulo', 'Otro')->assertStatus(422);
    }

    public function test_revoked_permission_blocks_component(): void
    {
        DB::table('usuario_submenu')->update(['permitido' => false]);
        Livewire::test(CarteraDeCobros::class)->assertForbidden();
    }

    public function test_recovery_includes_paid_invoices_and_uses_independent_ranges_and_net_abonos(): void
    {
        $this->factura(1, [], '60');
        $this->factura(2, ['cliente_id' => 2], '0', ['estado_cerrado' => 2]);
        $this->factura(3, ['estado_venta_id' => 0]);
        DB::table('abonos_creditos')->insert([
            ['factura_id' => 1, 'monto_abonado' => 40, 'fecha_pago' => '2026-11-02 23:59:59', 'usr_registro' => 1, 'estado_abono' => 1],
            ['factura_id' => 2, 'monto_abonado' => 100, 'fecha_pago' => '2026-11-01 00:00:00', 'usr_registro' => 2, 'estado_abono' => 1],
            ['factura_id' => 1, 'monto_abonado' => 20, 'fecha_pago' => '2026-10-10 12:00:00', 'usr_registro' => 1, 'estado_abono' => 1],
            ['factura_id' => 1, 'monto_abonado' => 900, 'fecha_pago' => '2026-11-01 12:00:00', 'usr_registro' => 1, 'estado_abono' => 0],
            ['factura_id' => 3, 'monto_abonado' => 900, 'fecha_pago' => '2026-11-01 12:00:00', 'usr_registro' => 1, 'estado_abono' => 1],
        ]);
        Livewire::test(CarteraDeCobros::class)
            ->assertViewHas('recuperacion', fn ($r) => $r['comparacion'][0]['cobrado'] === 14000
                && $r['comparacion'][0]['porcentaje'] === 70.0
                && $r['comparacion'][1]['cobrado'] === 2000
                && $r['comparacion'][1]['porcentaje'] === 25.0)
            ->assertViewHas('recuperacion', fn ($r) => $r['grupos']['asesor']->sum('actual') === 14000
                && $r['grupos']['teleasesor']->sum('actual') === 14000 && $r['grupos']['gestor']->sum('actual') === 14000)
            ->call('seleccionar', 'usuario', '2')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 0)
            ->assertViewHas('recuperacion', fn ($r) => $r['comparacion'][0]['porcentaje'] === 100.0)
            ->call('limpiar')
            ->set('modoPeriodo', 'detalle')
            ->set('fin', '2026-10-01')
            ->assertHasErrors('fin')
            ->assertSee('Corrige los rangos');
    }

    public function test_complete_rankings_and_excel_export_are_not_limited_to_ten_or_current_page(): void
    {
        for ($id = 3; $id <= 30; $id++) {
            DB::table('cliente')->insert(['id' => $id, 'nombre' => 'Cliente ' . $id]);
            $this->factura($id, ['cliente_id' => $id]);
        }
        $componente = Livewire::test(CarteraDeCobros::class)
            ->assertViewHas('clientes', fn ($c) => $c->count() === 28)
            ->assertViewHas('recuperacion', fn ($r) => $r['grupos']['cliente']->count() === 28)
            ->call('descargar', 'detalle')->assertFileDownloaded('Cartera-detalle-2026-11-02.xlsx');

        $export = new \App\Exports\CarteraCobrosExport([
            ['Detalle', ['Cliente', 'Factura', 'Saldo'], [['=NO_ES_FORMULA', '000-001', 12.34]]],
        ]);
        $bytes = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $archivo = tempnam(sys_get_temp_dir(), 'cartera-test-');
        try {
            file_put_contents($archivo, base64_decode($componente->effects['download']['content']));
            $completo = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
            $this->assertSame(29, $completo->getSheetByName('Detalle cartera')->getHighestRow());
            $this->assertNotNull($completo->getSheetByName('Criterios'));
            $componente->call('descargar')->assertFileDownloaded('Cartera-todas-2026-11-02.xlsx');
            file_put_contents($archivo, base64_decode($componente->effects['download']['content']));
            $completo = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
            $this->assertSame(16, $completo->getSheetCount());
            $this->assertSame(29, $completo->getSheetByName('Recuperacion cliente')->getHighestRow());
            file_put_contents($archivo, $bytes);
            $libro = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
            $hoja = $libro->getActiveSheet();
            $this->assertSame('=NO_ES_FORMULA', $hoja->getCell('A2')->getValue());
            $this->assertSame('s', $hoja->getCell('A2')->getDataType());
            $this->assertSame('000-001', $hoja->getCell('B2')->getValue());
            $this->assertSame(12.34, $hoja->getCell('C2')->getValue());
        } finally {
            unlink($archivo);
        }
    }

    public function test_date_only_payments_null_registrars_and_overlapping_periods(): void
    {
        $this->factura(1, [], '60');
        DB::table('abonos_creditos')->insert([
            ['factura_id' => 1, 'monto_abonado' => 40, 'fecha_pago' => '2026-11-01', 'usr_registro' => null],
            ['factura_id' => 1, 'monto_abonado' => 10, 'fecha_pago' => '2026-11-03 00:00:00', 'usr_registro' => null],
        ]);
        Livewire::test(CarteraDeCobros::class)
            ->set('modoPeriodo', 'detalle')
            ->set('compararFin', '2026-11-02')
            ->set('compararInicio', '2026-11-01')
            ->call('seleccionar', 'usuario', '0')
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 6000)
            ->assertViewHas('recuperacion', fn ($r) => $r['comparacion'][0]['cobrado'] === 4000
                && $r['comparacion'][1]['cobrado'] === 4000
                && $r['grupos']['usuario']->first()['porcentaje'] === 40.0)
            ->set('fin', '2026-11-03')->assertHasErrors('fin')
            ->call('descargar')->assertNoFileDownloaded();
    }

    public function test_monthly_comparison_defaults_and_switching_to_detailed_ranges(): void
    {
        $this->factura(1, [], '60');
        DB::table('abonos_creditos')->insert([
            ['factura_id' => 1, 'monto_abonado' => 10, 'fecha_pago' => '2024-02-01', 'usr_registro' => 1],
            ['factura_id' => 1, 'monto_abonado' => 20, 'fecha_pago' => '2024-02-29 23:59:59', 'usr_registro' => 1],
            ['factura_id' => 1, 'monto_abonado' => 40, 'fecha_pago' => '2024-03-01', 'usr_registro' => 1],
        ]);
        $componente = Livewire::test(CarteraDeCobros::class)
            ->assertSet('modoPeriodo', 'mensual')->assertSet('mesA', '2026-11')->assertSet('mesB', '2026-10')
            ->assertSet('fin', '2026-11-02')
            ->assertSee('Cartera pendiente')->assertSee('Recuperación y recaudación')
            ->set('mesA', '2024-02')->set('mesB', '2024-03')
            ->assertSet('inicio', '2024-02-01')->assertSet('fin', '2024-02-29')
            ->assertSet('compararInicio', '2024-03-01')->assertSet('compararFin', '2024-03-31')
            ->assertViewHas('recuperacion', fn ($r) => $r['comparacion'][0]['cobrado'] === 3000
                && $r['comparacion'][1]['cobrado'] === 4000)
            ->set('modoPeriodo', 'detalle')->set('fin', '2024-02-10')
            ->assertViewHas('recuperacion', fn ($r) => $r['comparacion'][0]['cobrado'] === 1000)
            ->set('modoPeriodo', 'mensual')
            ->assertSet('fin', '2024-02-29')
            ->call('seleccionar', 'asesor', '1')->call('limpiar')
            ->assertSet('mesA', '2024-02')->assertSet('mesB', '2024-03')
            ->assertViewHas('recuperacion', fn ($r) => $r['comparacion'][0]['cobrado'] === 3000)
            ->call('descargar', 'comparacion')->assertFileDownloaded('Cartera-comparacion-2026-11-02.xlsx');
        $archivo = tempnam(sys_get_temp_dir(), 'cartera-meses-');
        try {
            file_put_contents($archivo, base64_decode($componente->effects['download']['content']));
            $libro = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
            $hoja = $libro->getSheetByName('Comparacion');
            $this->assertSame('2024-02-01', $hoja->getCell('B2')->getValue());
            $this->assertSame('2024-02-29', $hoja->getCell('C2')->getValue());
            $this->assertEquals(30, $hoja->getCell('D2')->getValue());
            $this->assertEquals(40, $hoja->getCell('D3')->getValue());
            $this->assertSame('Mensual (mes actual hasta hoy)', $libro->getSheetByName('Criterios')->getCell('B3')->getValue());
        } finally {
            unlink($archivo);
        }
    }

    public function test_invalid_or_future_months_block_recovery_and_exports(): void
    {
        Livewire::test(CarteraDeCobros::class)
            ->set('mesA', '2026-13')->assertHasErrors('mesA')
            ->assertViewHas('recuperacion', fn ($r) => !$r['valida'])
            ->call('descargar')->assertNoFileDownloaded()
            ->set('mesA', '2026-12')->assertHasErrors('mesA')
            ->set('mesA', '')->assertHasErrors('mesA')
            ->set('mesA', '2026-10')->assertHasNoErrors()
            ->set('mesB', '2026-12')->assertHasErrors('mesB')
            ->call('descargar')->assertNoFileDownloaded();
    }
}
