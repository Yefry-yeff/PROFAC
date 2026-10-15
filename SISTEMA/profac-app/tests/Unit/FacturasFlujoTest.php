<?php

namespace Tests\Unit;

use App\Livewire\Flujo\ModalFlujoPedido;
use App\Services\Expo\SeccionadorOfertaExpo;
use App\Support\FacturasFlujo;
use Illuminate\Auth\AuthManager;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FacturasFlujoTest extends TestCase
{
    private Manager $database;
    private ?Container $previousApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApplication = Facade::getFacadeApplication();
        $this->database = new Manager();
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $container = $this->database->getContainer();
        $container->instance('db', $this->database->getDatabaseManager());
        DB::clearResolvedInstance('db');
        Facade::setFacadeApplication($container);

        $schema = $this->database->getConnection()->getSchemaBuilder();
        $schema->create('factura', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('estado_venta_id');
        });
        $schema->create('historico_flujo', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('flujo_id');
            $table->integer('tipo_tramite_id');
            $table->integer('tramite_id');
            $table->integer('estado_id')->default(1);
            $table->string('observaciones')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        $this->database->getDatabaseManager()->disconnect();
        DB::clearResolvedInstance('db');
        Auth::clearResolvedInstance('auth');
        Facade::clearResolvedInstance('validator');
        Facade::setFacadeApplication($this->previousApplication);
        \Mockery::close();
        parent::tearDown();
    }

    public function test_todas_las_facturas_anuladas_no_bloquean_aunque_el_historico_siga_activo(): void
    {
        for ($id = 1; $id <= 8; $id++) {
            $this->registrarFactura($id, 2);
        }
        $this->assertFalse(FacturasFlujo::tieneVigentes(4577));
        $this->assertSame(8, DB::table('historico_flujo')->count());
    }

    public function test_una_factura_vigente_bloquea_aunque_las_demas_esten_anuladas(): void
    {
        $this->registrarFactura(1, 2);
        $this->registrarFactura(2, 1);
        $this->assertTrue(FacturasFlujo::tieneVigentes(4577));
    }

    public function test_factura_vencida_bloquea_incluso_con_historico_inactivo(): void
    {
        $this->registrarFactura(1, 4);
        DB::table('historico_flujo')->update(['estado_id' => 7]);
        $this->assertTrue(FacturasFlujo::tieneVigentes(4577));
    }

    public function test_no_confunde_id_de_entrega_con_factura_de_otro_flujo(): void
    {
        $this->registrarFactura(1, 2);
        DB::table('factura')->insert(['id' => 99, 'estado_venta_id' => 1]);
        DB::table('historico_flujo')->insert([
            'flujo_id' => 4577, 'tipo_tramite_id' => 5, 'tramite_id' => 99,
        ]);
        $this->assertFalse(FacturasFlujo::tieneVigentes(4577));
    }

    public function test_soporta_factura_legacy_en_tipo_cinco_y_su_anulacion(): void
    {
        $this->registrarFactura(1, 1, 5);
        $this->assertTrue(FacturasFlujo::tieneVigentes(4577));
        DB::table('factura')->update(['estado_venta_id' => 2]);
        $this->assertFalse(FacturasFlujo::tieneVigentes(4577));
    }

    public function test_flujo_sin_facturas_no_se_bloquea_por_facturas_de_otro_flujo(): void
    {
        $this->registrarFactura(1, 1);
        $this->assertFalse(FacturasFlujo::tieneVigentes(9000));
    }

    public function test_quitar_ganadora_y_seleccionar_nueva_con_facturas_anuladas_conserva_el_historial(): void
    {
        $this->prepararOfertas();
        $this->registrarFactura(1, 2);
        $modal = new ModalFlujoPedido();
        $modal->flujoId = 4577;
        $modal->ofertaSeleccionada = ['id' => 100];
        $modal->motivoAnulOferta = 'Descuento adicional autorizado';
        $modal->quitarGanadora();

        $this->assertSame('', $modal->mensajeError);
        $this->assertSame(2, (int) DB::table('flujo')->value('tipo_tramite_id'));
        $this->assertSame(0, DB::table('historico_flujo')->where('observaciones', 'ganadora')->count());
        $this->assertSame(2, (int) DB::table('cotizacion_estado')->value('ganadora'));

        (new SeccionadorOfertaExpo())->iniciarSeccionado(4577, 200, 1);
        $this->assertSame(11, (int) DB::table('flujo')->value('tipo_tramite_id'));
        $this->assertSame(200, (int) DB::table('historico_flujo')->where('observaciones', 'ganadora')->value('tramite_id'));
        $this->assertSame(1, DB::table('historico_flujo')->where('tipo_tramite_id', 3)->count());
        $this->assertSame(2, (int) DB::table('factura')->value('estado_venta_id'));
    }

    public function test_servidor_rechaza_quitar_y_marcar_ganadora_con_factura_vigente(): void
    {
        $this->prepararOfertas();
        $this->registrarFactura(1, 1);
        $modal = new ModalFlujoPedido();
        $modal->flujoId = 4577;
        $modal->ofertaSeleccionada = ['id' => 100];
        $modal->motivoAnulOferta = 'Descuento adicional';
        $modal->quitarGanadora();
        $this->assertStringContainsString('Debe anular todas las facturas', $modal->mensajeError);
        $this->assertSame(100, (int) DB::table('historico_flujo')->where('observaciones', 'ganadora')->value('tramite_id'));
        $this->assertSame(0, DB::table('cotizacion_estado')->count());

        $modal->ofertaSeleccionada = ['id' => 200];
        $modal->ganadoraOferta();
        $this->assertStringContainsString('Debe anular todas las facturas', $modal->mensajeError);
        $this->expectException(ValidationException::class);
        (new SeccionadorOfertaExpo())->iniciarSeccionado(4577, 200, 1);
    }

    private function prepararOfertas(): void
    {
        Auth::swap(\Mockery::mock(AuthManager::class)->shouldReceive('id')->andReturn(1)->getMock());
        $this->database->getContainer()->instance('validator', new Factory(new Translator(new ArrayLoader(), 'es')));
        $schema = $this->database->getConnection()->getSchemaBuilder();
        $schema->create('flujo', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('tipo_tramite_id');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        $schema->create('expo_cotizacion', function (Blueprint $table) {
            $table->integer('cotizacion_id')->primary();
        });
        $schema->create('prefactura', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('flujo_id');
            $table->integer('cotizacion_id');
            $table->string('estado');
            $table->timestamps();
        });
        $schema->create('cotizacion_estado', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cotizacion_id');
            $table->integer('flujo_id');
            $table->integer('ganadora');
            $table->string('comentario');
            $table->integer('estado_id');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
        DB::table('flujo')->insert(['id' => 4577, 'tipo_tramite_id' => 3]);
        DB::table('expo_cotizacion')->insert([['cotizacion_id' => 100], ['cotizacion_id' => 200]]);
        DB::table('historico_flujo')->insert([
            ['flujo_id' => 4577, 'tipo_tramite_id' => 2, 'tramite_id' => 100, 'observaciones' => 'ganadora'],
            ['flujo_id' => 4577, 'tipo_tramite_id' => 2, 'tramite_id' => 200, 'observaciones' => null],
        ]);
    }

    private function registrarFactura(int $id, int $estado, int $tipoTramite = 3): void
    {
        DB::table('factura')->insert(['id' => $id, 'estado_venta_id' => $estado]);
        DB::table('historico_flujo')->insert([
            'flujo_id' => 4577, 'tipo_tramite_id' => $tipoTramite, 'tramite_id' => $id,
        ]);
    }
}
