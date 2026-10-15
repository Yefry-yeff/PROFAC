<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Comisiones\AplicadorRetencionesMora;
use App\Services\Comisiones\GeneradorFacturasComision;
use App\Services\Comisiones\ProcesadorComisiones;
use App\Services\PagosNoIdentificadosService as Pagos;
use App\Livewire\CuentasPorCobrar\PagosNoIdentificados;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery;
use Livewire\Livewire;
use Tests\TestCase;

class PagosNoIdentificadosTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        // Conexión aislada: estas pruebas nunca escriben en la base operativa.
        $mysql = getenv('PNI_TEST_MYSQL_DATABASE');
        if ($mysql !== false && $mysql !== '') {
            if (!preg_match('/^profac_pni_[a-f0-9]+_testing$/D', $mysql)) {
                throw new \RuntimeException('Nombre de base aislada de pruebas inválido.');
            }
            $connection = $app['config']->get('database.connections.mysql');
            $connection['database'] = $mysql;
            $app['config']->set('database.connections.pni_testing', $connection);
        } else {
            $app['config']->set('database.connections.pni_testing', [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ]);
        }
        $app['config']->set('database.default', 'pni_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertStringEndsWith('_testing', DB::connection()->getName());
        if (DB::getDriverName() === 'mysql') {
            Schema::dropAllTables();
        }
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('rol_id'); });
        Schema::create('rol', function (Blueprint $t) { $t->id(); });
        Schema::create('usuario_rol', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('rol_id'); });
        Schema::create('usuario_submenu', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('sub_menu_id'); $t->boolean('permitido'); });
        Schema::create('sub_menu', function (Blueprint $t) { $t->id(); $t->string('url'); $t->integer('estado_id'); $t->integer('orden')->default(1); });
        Schema::create('rol_submenu', function (Blueprint $t) { $t->integer('rol_id'); $t->integer('sub_menu_id'); });
        Schema::create('banco', function (Blueprint $t) { $t->integer('id')->primary(); $t->string('nombre'); $t->string('cuenta'); });
        Schema::create('cliente', function (Blueprint $t) { $t->integer('id')->primary(); $t->string('nombre'); $t->decimal('credito_inicial', 12, 2)->default(1000); $t->decimal('credito', 12, 2)->default(0); });
        Schema::create('factura', function (Blueprint $t) {
            $t->integer('id')->primary(); $t->integer('cliente_id'); $t->string('cai'); $t->integer('numero_secuencia_cai');
            $t->integer('estado_venta_id')->default(1); $t->integer('vendedor')->default(1);
            $t->integer('users_id')->default(1); $t->integer('gestor_entrega')->default(1);
            $t->integer('tipo_venta_id')->default(1); $t->decimal('sub_total', 12, 2)->default(800);
            $t->decimal('total', 12, 2)->default(800); $t->decimal('isv', 12, 2)->default(0);
            $t->date('fecha_emision')->default('2026-01-01'); $t->date('fecha_vencimiento')->default('2026-02-01');
        });
        Schema::create('aplicacion_pagos', function (Blueprint $t) {
            $t->integer('id')->primary(); $t->integer('factura_id'); $t->integer('cliente_id');
            $t->decimal('saldo', 12, 2); $t->decimal('credito_abonos', 12, 2)->default(0);
            $t->decimal('total_factura_cargo', 12, 2)->default(800);
            $t->decimal('retencion_isv_factura', 12, 2)->default(0);
            $t->integer('estado')->default(1); $t->integer('estado_cerrado')->default(0);
            $t->integer('ultimo_usr_actualizo')->nullable(); $t->integer('usr_cerro')->nullable();
            $t->timestamp('fecha_cierre_factura')->nullable(); $t->timestamps();
        });
        Schema::create('abonos_creditos', function (Blueprint $t) {
            $t->integer('id', true); $t->integer('aplicacion_pagos_id'); $t->integer('factura_id');
            $t->integer('banco_id'); $t->integer('estado_abono'); $t->integer('id_tipo_pago_cobro');
            $t->decimal('monto_abonado', 12, 2); $t->integer('usr_registro'); $t->text('comentario');
            $t->date('fecha_pago'); $t->string('numero_recibo'); $t->date('periodo_comision_original')->nullable();
            $t->date('periodo_comision_asignado')->nullable(); $t->integer('desvio_confirmado_por')->nullable(); $t->timestamps();
        });
        Schema::create('comision_periodo', function (Blueprint $t) { $t->id(); $t->date('periodo')->unique(); $t->integer('estado')->default(0); $t->timestamps(); });
        (require database_path('migrations\2026_10_15_000001_create_pagos_no_identificados_tables.php'))->up();
        (require database_path('migrations\2026_10_15_000002_add_hora_pago_to_pagos_no_identificados.php'))->up();
        DB::table('users')->insert(['id' => 1, 'name' => 'Operador', 'rol_id' => 1]);
        DB::table('rol')->insert(['id' => 1]);
        DB::table('sub_menu')->insert(['id' => 1, 'url' => Pagos::URL, 'estado_id' => 1]);
        DB::table('rol_submenu')->insert(['rol_id' => 1, 'sub_menu_id' => 1]);
        DB::table('banco')->insert([['id' => 1, 'nombre' => 'Banco A', 'cuenta' => '123'], ['id' => 2, 'nombre' => 'Banco B', 'cuenta' => '456']]);
        DB::table('cliente')->insert([['id' => 1, 'nombre' => 'Cliente A'], ['id' => 2, 'nombre' => 'Cliente B']]);
        DB::table('factura')->insert([
            ['id' => 1, 'cliente_id' => 1, 'cai' => 'F-001', 'numero_secuencia_cai' => 1],
            ['id' => 2, 'cliente_id' => 1, 'cai' => 'F-002', 'numero_secuencia_cai' => 2],
            ['id' => 3, 'cliente_id' => 2, 'cai' => 'F-003', 'numero_secuencia_cai' => 3],
        ]);
        foreach ([1 => 1, 2 => 1, 3 => 2] as $id => $cliente) {
            DB::table('aplicacion_pagos')->insert(['id' => $id, 'factura_id' => $id, 'cliente_id' => $cliente, 'saldo' => '800.00']);
        }
        Auth::setUser(User::findOrFail(1));
        $this->app->instance(GeneradorFacturasComision::class, Mockery::mock(GeneradorFacturasComision::class)->shouldReceive('generar')->andReturn([])->getMock());
        $this->app->instance(AplicadorRetencionesMora::class, Mockery::mock(AplicadorRetencionesMora::class)->shouldReceive('aplicar')->andReturn([])->getMock());
    }

    private function registrar(string $monto = '1000.00', ?int $cliente = null, string $fecha = '2026-01-01', int $banco = 1): int
    {
        return app(Pagos::class)->registrar([
            'monto' => $monto, 'cliente_id' => $cliente, 'fecha_pago' => $fecha,
            'banco_id' => $banco, 'cuenta_bancaria' => $banco === 1 ? '123' : '456', 'observaciones' => 'Prueba',
        ], (string) Str::uuid());
    }

    private function solicitud(array $ids, string $monto, int $factura = 1, int $cliente = 1): array
    {
        return [
            'pagos' => $ids, 'monto' => $monto, 'factura_id' => $factura, 'cliente_id' => $cliente,
            'clave' => (string) Str::uuid(), 'saldos' => DB::table('pagos_no_identificados')->whereIn('id', $ids)
                ->get()->mapWithKeys(fn ($p) => [$p->id => number_format($p->saldo_disponible, 2, '.', '')])->all(),
            'saldo_factura' => number_format(DB::table('aplicacion_pagos')->where('factura_id', $factura)->value('saldo'), 2, '.', ''),
            'confirmar_periodo' => false,
        ];
    }

    public function test_registers_without_client_and_does_not_create_an_invoice_payment(): void
    {
        $id = $this->registrar();
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $id, 'cliente_id' => null, 'estado' => 'pendiente', 'saldo_disponible' => 1000, 'creado_por' => 1]);
        $this->assertDatabaseCount('abonos_creditos', 0);
    }

    public function test_mixed_selection_is_consumed_by_date_and_preserves_each_bank(): void
    {
        $p2 = $this->registrar('500.00', null, '2026-01-02', 2);
        $p1 = $this->registrar('600.00', 1, '2026-01-01');
        app(Pagos::class)->aplicar($this->solicitud([$p2, $p1], '800.00'));
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $p1, 'estado' => 'aplicado', 'saldo_disponible' => 0]);
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $p2, 'cliente_id' => 1, 'estado' => 'parcial', 'saldo_disponible' => 300, 'identificado_por' => 1]);
        $this->assertDatabaseHas('aplicacion_pagos', ['id' => 1, 'saldo' => 0, 'credito_abonos' => 800, 'estado_cerrado' => 2]);
        $this->assertDatabaseHas('abonos_creditos', ['banco_id' => 1, 'monto_abonado' => 600, 'fecha_pago' => '2026-01-01']);
        $this->assertDatabaseHas('abonos_creditos', ['banco_id' => 2, 'monto_abonado' => 200, 'fecha_pago' => '2026-01-02']);
        $this->assertDatabaseHas('cliente', ['id' => 1, 'credito' => 800]);
    }

    public function test_successive_applications_show_every_linked_invoice(): void
    {
        $pago = $this->registrar();
        app(Pagos::class)->aplicar($this->solicitud([$pago], '300.00'));
        app(Pagos::class)->aplicar($this->solicitud([$pago], '500.00', 2));
        app(Pagos::class)->aplicar($this->solicitud([$pago], '200.00'));
        $historial = app(Pagos::class)->historial($pago);
        $this->assertCount(3, $historial);
        $this->assertSame(['F-001', 'F-002', 'F-001'], $historial->pluck('cai')->all());
        $this->assertEquals([700, 200, 0], $historial->pluck('saldo_despues')->all());
        $this->assertCount(1, app(Pagos::class)->consulta(['factura' => 'F-001'])->get());
        $this->assertCount(3, app(Pagos::class)->historial($pago));
    }

    public function test_idempotent_retries_do_not_consume_the_same_funds_twice(): void
    {
        $pago = $this->registrar();
        $datos = $this->solicitud([$pago], '100.00');
        $id = app(Pagos::class)->aplicar($datos);
        $this->assertSame($id, app(Pagos::class)->aplicar($datos));
        $this->assertDatabaseCount('pni_operaciones', 1);
        $this->assertDatabaseCount('abonos_creditos', 1);
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'saldo_disponible' => 900]);
    }

    public function test_distinct_known_clients_are_rejected(): void
    {
        $a = $this->registrar('100.00', 1);
        $b = $this->registrar('100.00', 2);
        $this->expectException(ValidationException::class);
        app(Pagos::class)->aplicar($this->solicitud([$a, $b], '100.00'));
    }

    public function test_stale_balances_are_rejected_without_partial_effects(): void
    {
        $pago = $this->registrar();
        $datos = $this->solicitud([$pago], '100.00');
        DB::table('aplicacion_pagos')->where('id', 1)->update(['saldo' => 700]);
        try {
            app(Pagos::class)->aplicar($datos);
            $this->fail('Se permitió un saldo desactualizado.');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('abonos_creditos', 0);
            $this->assertDatabaseCount('pni_operaciones', 0);
            $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'saldo_disponible' => 1000]);
        }
    }

    public function test_unconsumed_unidentified_payments_remain_unassigned(): void
    {
        $a = $this->registrar('600.00', 1);
        $b = $this->registrar('500.00');
        app(Pagos::class)->aplicar($this->solicitud([$a, $b], '100.00'));
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $b, 'cliente_id' => null, 'saldo_disponible' => 500, 'estado' => 'pendiente']);
        $this->assertDatabaseCount('pni_aplicaciones', 1);
    }

    public function test_closed_period_requires_confirmation_and_records_the_shift(): void
    {
        DB::table('comision_periodo')->insert(['periodo' => '2026-01-01', 'estado' => 1]);
        $pago = $this->registrar();
        $datos = $this->solicitud([$pago], '100.00');
        try {
            app(Pagos::class)->aplicar($datos);
            $this->fail('No se exigió confirmar el traslado.');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('abonos_creditos', 0);
        }
        $datos['confirmar_periodo'] = true;
        app(Pagos::class)->aplicar($datos);
        $this->assertDatabaseHas('abonos_creditos', [
            'fecha_pago' => '2026-01-01', 'periodo_comision_original' => '2026-01-01',
            'periodo_comision_asignado' => '2026-02-01', 'desvio_confirmado_por' => 1,
        ]);
    }

    public function test_failure_in_commission_generation_rolls_back_all_effects(): void
    {
        $this->app->instance(GeneradorFacturasComision::class, Mockery::mock(GeneradorFacturasComision::class)
            ->shouldReceive('generar')->andThrow(new \RuntimeException('Fallo simulado'))->getMock());
        $pago = $this->registrar('800.00');
        try {
            app(Pagos::class)->aplicar($this->solicitud([$pago], '800.00'));
            $this->fail('La excepción debe propagarse.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo simulado', $e->getMessage());
            $this->assertDatabaseCount('pni_operaciones', 0);
            $this->assertDatabaseCount('abonos_creditos', 0);
            $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'cliente_id' => null, 'saldo_disponible' => 800]);
            $this->assertDatabaseHas('aplicacion_pagos', ['id' => 1, 'saldo' => 800, 'estado_cerrado' => 0]);
        }
    }

    public function test_one_cent_is_a_valid_pending_balance_and_payment(): void
    {
        $pago = $this->registrar('0.01', 1);
        DB::table('aplicacion_pagos')->where('id', 1)->update(['saldo' => '0.01']);
        app(Pagos::class)->aplicar($this->solicitud([$pago], '0.01'));
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'estado' => 'aplicado', 'saldo_disponible' => 0]);
    }

    public function test_direct_permission_revocation_prevents_writes(): void
    {
        DB::table('usuario_submenu')->insert(['usuario_id' => 1, 'sub_menu_id' => 1, 'permitido' => 0]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->registrar();
    }

    public function test_invalid_bank_account_and_future_date_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(Pagos::class)->registrar([
            'cliente_id' => null, 'monto' => '0.00', 'fecha_pago' => now()->addDay()->toDateString(),
            'banco_id' => 1, 'cuenta_bancaria' => '456',
        ], (string) Str::uuid());
    }

    public function test_account_must_match_bank_even_with_valid_amount_and_date(): void
    {
        $this->expectException(ValidationException::class);
        app(Pagos::class)->registrar([
            'monto' => '10.00', 'fecha_pago' => '2026-01-01',
            'banco_id' => 1, 'cuenta_bancaria' => '456',
        ], (string) Str::uuid());
    }

    public function test_amount_cannot_exceed_invoice_or_origin_balance(): void
    {
        $pago = $this->registrar('100.00');
        foreach (['0.00', '100.01', '800.01', '1.001'] as $monto) {
            try {
                app(Pagos::class)->aplicar($this->solicitud([$pago], $monto));
                $this->fail('Se permitió el importe ' . $monto);
            } catch (ValidationException $e) {
                $this->assertDatabaseCount('abonos_creditos', 0);
                $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'saldo_disponible' => 100]);
            }
        }
    }

    public function test_cannot_apply_to_another_client_invoice_or_a_cancelled_invoice(): void
    {
        $pago = $this->registrar('100.00', 1);
        foreach ([3, 1] as $factura) {
            if ($factura === 1) {
                DB::table('factura')->where('id', 1)->update(['estado_venta_id' => 2]);
            }
            try {
                app(Pagos::class)->aplicar($this->solicitud([$pago], '10.00', $factura));
                $this->fail('Se aceptó una factura no elegible.');
            } catch (ValidationException $e) {
                $this->assertDatabaseCount('pni_operaciones', 0);
            }
        }
    }

    public function test_reusing_an_idempotency_key_with_different_data_is_rejected(): void
    {
        $pago = $this->registrar();
        $datos = $this->solicitud([$pago], '100.00');
        app(Pagos::class)->aplicar($datos);
        $datos['monto'] = '101.00';
        $this->expectException(ValidationException::class);
        app(Pagos::class)->aplicar($datos);
    }

    public function test_registration_is_idempotent(): void
    {
        $datos = [
            'cliente_id' => null, 'monto' => '10.00', 'fecha_pago' => '2026-01-01',
            'banco_id' => 1, 'cuenta_bancaria' => '123',
        ];
        $clave = (string) Str::uuid();
        $id = app(Pagos::class)->registrar($datos, $clave);
        $this->assertSame($id, app(Pagos::class)->registrar($datos, $clave));
        $this->assertDatabaseCount('pagos_no_identificados', 1);
    }

    public function test_invoice_charge_changes_are_included_in_the_available_balance(): void
    {
        DB::table('factura')->where('id', 1)->update(['total' => '900.00']);
        $this->assertEquals(900, app(Pagos::class)->cuentas(1)->where('f.id', 1)->first()->saldo);
        $pago = $this->registrar('900.00');
        $datos = $this->solicitud([$pago], '900.00');
        $datos['saldo_factura'] = '900.00';
        app(Pagos::class)->aplicar($datos);
        $this->assertDatabaseHas('aplicacion_pagos', ['id' => 1, 'total_factura_cargo' => 900, 'saldo' => 0]);
    }

    public function test_livewire_registers_loads_bank_account_and_shows_all_applications(): void
    {
        $component = Livewire::test(PagosNoIdentificados::class)
            ->assertSee('Nuevo Pago No Identificado')
            ->call('abrirNuevo')
            ->set('nuevo.banco_id', '1')
            ->assertSet('nuevo.cuenta_bancaria', '123')
            ->set('nuevo.monto', '1000.00')
            ->set('nuevo.fecha_pago', '2026-01-01')
            ->call('guardar')->assertHasNoErrors()->assertSet('modal', '');
        $pago = (int) DB::table('pagos_no_identificados')->value('id');
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00'));
        app(Pagos::class)->aplicar($this->solicitud([$pago], '200.00', 2));
        $component->call('verDetalle', $pago)->assertSee('F-001')->assertSee('F-002')
            ->assertSee('Todas las aplicaciones de este pago')
            ->call('verOperacion', 1)->assertSee('Orígenes de la operación #1');
    }

    public function test_livewire_applies_a_partial_payment_and_clears_selection(): void
    {
        $pago = $this->registrar('1000.00', 1);
        Livewire::test(PagosNoIdentificados::class)
            ->set('seleccionados', [(string) $pago])->call('abrirAplicacion')
            ->assertSet('clienteDestino', '1')
            ->set('facturaDestino', '1')->assertSet('montoAplicar', '800.00')
            ->set('montoAplicar', '200.00')->call('aplicar')
            ->assertHasNoErrors()->assertSet('modal', '')->assertSet('seleccionados', []);
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'saldo_disponible' => 800, 'estado' => 'parcial']);
    }

    public function test_linked_invoice_download_uses_existing_pdf_renderer(): void
    {
        $pago = $this->registrar('1000.00', 1);
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00'));
        $pdf = "%PDF-1.4\nFactura de prueba\n%%EOF";
        $this->mock(\App\Livewire\Ventas\FacturacionCorporativa::class)
            ->shouldReceive('imprimirFacturaCoorporativa')->once()->with(1)
            ->andReturn(response($pdf, 200, ['Content-Type' => 'application/pdf']));
        Livewire::test(PagosNoIdentificados::class)->call('verDetalle', $pago)
            ->assertSee('Saldo disponible para aplicar')
            ->assertSee('Saldo del depósito')
            ->assertSee('Descargar factura F-001 en PDF', false)
            ->call('descargarFactura', 1)->assertFileDownloaded('factura-1.pdf', $pdf, 'application/pdf')
            ->assertSet('modal', 'detalle');
    }

    public function test_invoice_download_rejects_invoice_not_linked_to_the_open_payment(): void
    {
        $pago = $this->registrar('1000.00', 1);
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00'));
        $this->mock(\App\Livewire\Ventas\FacturacionCorporativa::class)
            ->shouldNotReceive('imprimirFacturaCoorporativa');
        Livewire::test(PagosNoIdentificados::class)->call('verDetalle', $pago)
            ->call('descargarFactura', 2)->assertStatus(404);
        Livewire::test(PagosNoIdentificados::class)
            ->call('descargarFactura', 1)->assertStatus(404);
    }

    public function test_invoice_download_requires_module_permission(): void
    {
        $pago = $this->registrar('1000.00', 1);
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00'));
        $component = Livewire::test(PagosNoIdentificados::class)->call('verDetalle', $pago);
        DB::table('rol_submenu')->delete();
        $component->call('descargarFactura', 1)->assertStatus(403);
    }

    public function test_date_filters_are_inclusive_and_invalid_ranges_show_validation(): void
    {
        $this->registrar('10.00', 1, '2026-01-01');
        $this->registrar('20.00', 1, '2026-01-02');
        $this->assertCount(1, app(Pagos::class)->consulta(['desde' => '2026-01-01', 'hasta' => '2026-01-01'])->get());
        Livewire::test(PagosNoIdentificados::class)->set('filtros.desde', '2026-02-01')
            ->set('filtros.hasta', '2026-01-01')->assertHasErrors('filtros.hasta');
    }

    public function test_registration_keeps_payment_time_and_rejects_future_time(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $datos = [
            'cliente_id' => '', 'monto' => '12500.50', 'fecha_pago' => now()->toDateString(),
            'hora_pago' => '11:35', 'banco_id' => '1', 'cuenta_bancaria' => '123',
            'observaciones' => '',
        ];
        $clave = (string) Str::uuid();
        $id = app(Pagos::class)->registrar($datos, $clave);
        $this->assertSame($id, app(Pagos::class)->registrar($datos, $clave));
        $this->assertDatabaseHas('pagos_no_identificados', [
            'id' => $id, 'hora_pago' => '11:35:00', 'monto_original' => '12500.50',
        ]);
        $this->assertCount(1, app(Pagos::class)->consulta([
            'desde' => now()->toDateString(), 'hasta' => now()->toDateString(),
        ])->get());
        Livewire::test(PagosNoIdentificados::class)->call('verDetalle', $id)
            ->assertSee('11:35')->assertSee(now()->format('d/m/Y'));
        $this->expectException(ValidationException::class);
        app(Pagos::class)->registrar(array_replace($datos, ['hora_pago' => '13:00']), (string) Str::uuid());
    }

    public function test_client_and_pending_invoice_selects_are_not_truncated(): void
    {
        for ($id = 4; $id <= 110; $id++) {
            DB::table('cliente')->insert(['id' => $id, 'nombre' => "Cliente {$id}"]);
            DB::table('factura')->insert(['id' => $id, 'cliente_id' => 1, 'cai' => "F-{$id}", 'numero_secuencia_cai' => $id]);
            DB::table('aplicacion_pagos')->insert(['id' => $id, 'factura_id' => $id, 'cliente_id' => 1, 'saldo' => '800.00']);
        }
        Livewire::test(PagosNoIdentificados::class)->call('abrirNuevo')
            ->assertViewHas('clientes', fn ($clientes) => $clientes->count() === 109)
            ->assertSee('pniSelect', false)->assertDontSee('id="pni-factura-destino"', false)
            ->set('nuevo.hora_pago', '')->call('guardar')->assertHasErrors('nuevo.hora_pago');
        $pago = $this->registrar('100.00');
        Livewire::test(PagosNoIdentificados::class)->set('seleccionados', [(string) $pago])
            ->call('abrirAplicacion')->set('clienteDestino', '1')
            ->assertViewHas('facturas', fn ($facturas) => $facturas->count() === 109)
            ->set('facturaDestino', '110')->assertSet('montoAplicar', '100.00')
            ->set('clienteDestino', '2')->assertSet('facturaDestino', '')
            ->assertViewHas('facturas', fn ($facturas) => $facturas->count() === 1);
    }

    public function test_filter_modal_applies_only_after_search_and_displays_active_filters(): void
    {
        $pago = $this->registrar('10.00', 1);
        $this->registrar('20.00', 2);
        Livewire::test(PagosNoIdentificados::class)
            ->assertViewHas('resumen', fn ($r) => (int) $r->cantidad === 2 && (float) $r->depositos === 30.0)
            ->assertDontSee('id="pni-desde"', false)
            ->set('seleccionados', [(string) $pago])
            ->call('abrirFiltros')->assertSet('modal', 'filtros')
            ->set('filtrosEdicion.cliente', '1')
            ->assertSet('filtros.cliente', '')
            ->assertViewHas('resumen', fn ($r) => (int) $r->cantidad === 2)
            ->call('aplicarFiltros')->assertHasNoErrors()
            ->assertSet('modal', '')->assertSet('seleccionados', [])
            ->assertSet('filtros.cliente', '1')
            ->assertSee('Filtros activos')->assertSee('Cliente A')
            ->assertViewHas('resumen', fn ($r) => (int) $r->cantidad === 1 && (float) $r->disponible === 10.0)
            ->call('quitarFiltro', 'cliente')->assertSet('filtros.cliente', '')
            ->assertViewHas('resumen', fn ($r) => (int) $r->cantidad === 2);
    }

    public function test_closing_filter_modal_discards_draft_and_clear_restores_all_payments(): void
    {
        $this->registrar();
        Livewire::test(PagosNoIdentificados::class)->call('abrirFiltros')
            ->set('filtrosEdicion.estado', 'aplicado')->call('cerrar')
            ->assertSet('filtros.estado', '')
            ->call('abrirFiltros')->assertSet('filtrosEdicion.estado', '')
            ->set('filtrosEdicion.estado', 'aplicado')->call('aplicarFiltros')
            ->assertViewHas('resumen', fn ($r) => (int) $r->cantidad === 0)
            ->call('abrirFiltros')->call('limpiarFiltros')
            ->assertSet('filtros.estado', '')->assertSet('filtrosEdicion.estado', '')
            ->assertViewHas('resumen', fn ($r) => (int) $r->cantidad === 1);
    }

    public function test_filter_modal_keeps_invalid_dates_open_without_changing_active_filters(): void
    {
        Livewire::test(PagosNoIdentificados::class)->call('abrirFiltros')
            ->set('filtrosEdicion.desde', '2026-02-01')
            ->set('filtrosEdicion.hasta', '2026-01-01')->call('aplicarFiltros')
            ->assertHasErrors('filtrosEdicion.hasta')->assertSet('modal', 'filtros')
            ->assertSet('filtros.desde', '');
    }

    public function test_closing_invoice_runs_commissions_and_retention_using_the_original_date(): void
    {
        $commission = ['target_user_id' => 1, 'rol_id' => 1, 'monto_rol' => 10];
        $generator = Mockery::mock(GeneradorFacturasComision::class);
        $generator->shouldReceive('generar')->once()->with(1, 1, '2026-01-15')->andReturn([$commission]);
        $retention = Mockery::mock(AplicadorRetencionesMora::class);
        $retention->shouldReceive('aplicar')->once()->with([$commission], 1, '2026-01-15')->andReturn([$commission]);
        $processor = Mockery::mock(ProcesadorComisiones::class);
        $processor->shouldReceive('procesar')->once()->with($commission);
        $this->app->instance(GeneradorFacturasComision::class, $generator);
        $this->app->instance(AplicadorRetencionesMora::class, $retention);
        $this->app->instance(ProcesadorComisiones::class, $processor);
        $pago = $this->registrar('800.00', 1, '2026-01-15');
        app(Pagos::class)->aplicar($this->solicitud([$pago], '800.00'));
        $this->assertDatabaseHas('aplicacion_pagos', ['id' => 1, 'estado_cerrado' => 2]);
    }

    public function test_filtering_by_invoice_fields_does_not_duplicate_deposits(): void
    {
        $pago = $this->registrar();
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00'));
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00', 2));
        $otro = $this->registrar();
        $consulta = app(Pagos::class)->consulta(['vendedor' => '1', 'teleasesor' => '1', 'gestor' => '1']);
        $this->assertCount(1, $consulta->get());
        $this->assertSame($pago, (int) $consulta->first()->id);
        $this->assertNotSame($otro, $pago);
    }

    public function test_legacy_cancellation_preview_rejects_deposit_applications(): void
    {
        $pago = $this->registrar();
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00'));
        $response = app(\App\Livewire\CuentasPorCobrar\Pagos::class)
            ->impactoAnularAbono(\Illuminate\Http\Request::create('/', 'GET', ['abono_id' => 1]));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('no puede anularse', $response->getData()->error);
    }

    public function test_mysql_legacy_cancellation_cannot_desynchronize_deposit_balances(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('El flujo legado utiliza SELECT FOR UPDATE de MySQL.');
        }
        $pago = $this->registrar();
        app(Pagos::class)->aplicar($this->solicitud([$pago], '100.00'));
        $response = app(\App\Livewire\CuentasPorCobrar\Pagos::class)
            ->anularAbono(\Illuminate\Http\Request::create('/', 'POST', ['abono_id' => 1, 'motivo' => 'Prueba']));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertDatabaseHas('abonos_creditos', ['id' => 1, 'estado_abono' => 1]);
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'saldo_disponible' => 900]);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_mysql_concurrent_operators_cannot_consume_the_same_available_balance(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('La concurrencia real se verifica en MySQL aislado.');
        }
        DB::table('users')->insert(['id' => 2, 'name' => 'Segundo operador', 'rol_id' => 1]);
        $pago = $this->registrar();
        $datos = $this->solicitud([$pago], '600.00');
        $code = <<<'PHP'
require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection = $app['config']->get('database.connections.mysql');
$connection['database'] = $argv[1];
$app['config']->set('database.connections.pni_testing', $connection);
$app['config']->set('database.default', 'pni_testing');
Illuminate\Support\Facades\Auth::setUser(App\Models\User::findOrFail((int) $argv[3]));
try {
    App::make(App\Services\PagosNoIdentificadosService::class)->aplicar(json_decode(base64_decode($argv[2]), true));
    echo 'APPLIED';
} catch (Illuminate\Validation\ValidationException $e) {
    echo 'CONFLICT';
}
PHP;
        $processes = [];
        foreach ([1, 2] as $usuario) {
            $datos['clave'] = (string) Str::uuid();
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, '-r', $code, DB::connection()->getDatabaseName(), base64_encode(json_encode($datos)), (string) $usuario],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
                base_path()
            );
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $processes[] = [$process, $pipes];
        }
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $result = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $error . $result);
            $results[] = $result;
        }
        sort($results);
        $this->assertSame(['APPLIED', 'CONFLICT'], $results);
        $this->assertDatabaseCount('abonos_creditos', 1);
        $this->assertDatabaseCount('pni_operaciones', 1);
        $this->assertDatabaseHas('pagos_no_identificados', ['id' => $pago, 'saldo_disponible' => 400]);
        $this->assertDatabaseHas('aplicacion_pagos', ['id' => 1, 'saldo' => 200, 'credito_abonos' => 600]);
    }
}
