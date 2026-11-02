<?php

namespace Tests\Feature;

use App\Livewire\Reportes\AnalisisDeNegocio;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AnalisisDeNegocioTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.analisis_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        $app['config']->set('database.default', 'analisis_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('rol_id')->default(1); });
        Schema::create('rol', fn (Blueprint $t) => $t->id());
        Schema::create('usuario_rol', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('rol_id'); });
        Schema::create('usuario_submenu', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('sub_menu_id'); $t->boolean('permitido'); });
        Schema::create('sub_menu', function (Blueprint $t) { $t->id(); $t->string('url'); $t->integer('estado_id')->default(1); $t->integer('orden')->default(1); });
        Schema::create('rol_submenu', function (Blueprint $t) { $t->integer('rol_id'); $t->integer('sub_menu_id'); });
        DB::table('users')->insert(['id' => 1, 'name' => 'Usuario prueba', 'rol_id' => 2]);
        $rutas = array_merge(['reportes/analisis_de_negocio'], array_column(AnalisisDeNegocio::MODULOS, 'ruta'));
        foreach ($rutas as $i => $ruta) {
            DB::table('sub_menu')->insert(['id' => $i + 1, 'url' => $ruta]);
            DB::table('usuario_submenu')->insert(['usuario_id' => 1, 'sub_menu_id' => $i + 1, 'permitido' => true]);
        }
        $this->actingAs(User::findOrFail(1));
    }

    public function test_selector_contains_six_authorized_dashboards_without_loading_them(): void
    {
        Livewire::test(AnalisisDeNegocio::class)
            ->assertViewHas('modulos', fn ($m) => $m->pluck('nombre')->all() === [
                'Logistica', 'Cartera de Cobros', 'Ventas', 'Clientes', 'Productos', 'Expo',
            ] && $m->every(fn ($modulo) => $modulo['permitido'] && str_ends_with($modulo['url'], '?analisis_embed=1')))
            ->assertSee('Selecciona un dashboard')->assertSee('Cargando');
    }

    public function test_revoked_modules_are_not_selectable_and_hub_requires_its_own_permission(): void
    {
        DB::table('usuario_submenu')->where('sub_menu_id', 3)->update(['permitido' => false]);
        Livewire::test(AnalisisDeNegocio::class)
            ->assertViewHas('modulos', fn ($m) => !$m->firstWhere('id', 'cartera')['permitido'])
            ->assertSee('Cartera de Cobros (sin acceso)');
        DB::table('usuario_submenu')->where('sub_menu_id', 1)->update(['permitido' => false]);
        Livewire::test(AnalisisDeNegocio::class)->assertForbidden();
    }

    public function test_dashboard_link_preselects_cartera_and_preserves_module_permissions(): void
    {
        Livewire::withQueryParams(['modulo' => 'cartera'])->test(AnalisisDeNegocio::class)
            ->assertSet('moduloInicial', 'cartera');
        DB::table('usuario_submenu')->where('sub_menu_id', 3)->update(['permitido' => false]);
        Livewire::withQueryParams(['modulo' => 'cartera'])->test(AnalisisDeNegocio::class)
            ->assertSet('moduloInicial', '');
    }

    public function test_unknown_or_malformed_initial_module_is_not_loaded(): void
    {
        foreach (['desconocido', '<script>', ['cartera']] as $modulo) {
            Livewire::withQueryParams(['modulo' => $modulo])->test(AnalisisDeNegocio::class)
                ->assertSet('moduloInicial', '');
        }
    }

    public function test_cartera_summary_displays_amounts_rates_and_authorized_link(): void
    {
        $datos = [
            'periodoLabel' => 'Noviembre 2026',
            'kpisCobros' => [
                'saldo_pendiente' => 1000, 'total_recuperado' => 250,
                'facturas_pendientes' => 4, 'facturas_vencidas' => 2, 'clientes_morosos' => 1,
                'antiguedad' => ['al_dia' => 100, '0_30' => 200, '31_60' => 100, '61_90' => 100, '90_mas' => 400],
            ],
        ];
        $this->view('dashboard.cartera-resumen', $datos)
            ->assertSee('L. 1,000.00')->assertSee('L. 800.00')
            ->assertSee('80.00%')->assertSee('20.00%')
            ->assertSee('Sin clasificar')->assertSee('L. 100.00')
            ->assertSee('modulo=cartera')->assertSee('Noviembre 2026');
        DB::table('usuario_submenu')->where('sub_menu_id', 3)->update(['permitido' => false]);
        $this->view('dashboard.cartera-resumen', $datos)->assertDontSee('modulo=cartera');
    }

    public function test_empty_cartera_summary_does_not_divide_by_zero_or_claim_a_recovery_rate(): void
    {
        $this->view('dashboard.cartera-resumen', ['periodoLabel' => 'Noviembre 2026', 'kpisCobros' => []])
            ->assertSee('Sin base')->assertSee('No hay saldo pendiente por cobrar.')
            ->assertDontSee('chart-ger-antiguedad');
    }

    public function test_embedded_presentation_is_restricted_to_catalog_routes(): void
    {
        foreach (AnalisisDeNegocio::MODULOS as $modulo) {
            $this->app->instance('request', \Illuminate\Http\Request::create('/' . $modulo['ruta'] . '?analisis_embed=1'));
            $this->assertTrue(AnalisisDeNegocio::esModuloIntegrado());
        }
        $this->app->instance('request', \Illuminate\Http\Request::create('/reportes/cartera_de_cobros'));
        $this->assertFalse(AnalisisDeNegocio::esModuloIntegrado());
        $this->app->instance('request', \Illuminate\Http\Request::create('/reportes/analisis_de_negocio?analisis_embed=1'));
        $this->assertFalse(AnalisisDeNegocio::esModuloIntegrado());
    }

    public function test_administrator_can_access_all_business_reports_even_without_individual_permissions(): void
    {
        DB::table('users')->where('id', 1)->update(['rol_id' => 1]);
        $this->actingAs(User::findOrFail(1));
        DB::table('usuario_submenu')->update(['permitido' => false]);
        Livewire::test(AnalisisDeNegocio::class)
            ->assertViewHas('modulos', fn ($m) => $m->count() === 6 && $m->every('permitido'))
            ->assertDontSee('(sin acceso)');
        foreach (AnalisisDeNegocio::MODULOS as $modulo) {
            $this->assertTrue(\App\Http\Controllers\MenuHelper::tieneAcceso($modulo['ruta']));
        }
        $this->assertFalse(\App\Http\Controllers\MenuHelper::tieneAcceso('usuarios/roles'));
        DB::table('usuario_submenu')->delete();
        Livewire::test(AnalisisDeNegocio::class)
            ->assertViewHas('modulos', fn ($m) => $m->every('permitido'));
    }

    public function test_additional_administrator_role_also_grants_access_and_revoking_it_restores_permissions(): void
    {
        DB::table('rol')->insert(['id' => 1]);
        DB::table('usuario_rol')->insert(['usuario_id' => 1, 'rol_id' => 1]);
        DB::table('usuario_submenu')->update(['permitido' => false]);
        Livewire::test(AnalisisDeNegocio::class)
            ->assertViewHas('modulos', fn ($m) => $m->every('permitido'));
        DB::table('usuario_rol')->delete();
        Livewire::test(AnalisisDeNegocio::class)->assertForbidden();
    }
}
