<?php

namespace Tests\Feature;

use App\Livewire\Flujo\ModalFlujoPedido;
use App\Livewire\Ventas\ListadoFacturaEstatal;
use App\Livewire\Ventas\ListadoFacturas;
use App\Livewire\VentasExoneradas\ListadoFacturasExonerads;
use App\Models\User;
use App\Services\Expo\DevolverSeccionAnulada;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DevolverSeccionAnuladaTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.expo_anulacion_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $mysql = getenv('ANULACION_TEST_MYSQL_DATABASE');
        if ($mysql !== false && $mysql !== '') {
            if (!preg_match('/^profac_anulacion_[a-f0-9]+_testing$/D', $mysql)) {
                throw new \RuntimeException('Nombre de base aislada de pruebas invalido.');
            }
            $connection = $app['config']->get('database.connections.mysql');
            $connection['database'] = $mysql;
            $app['config']->set('database.connections.expo_anulacion_testing', $connection);
        }
        $app['config']->set('database.default', 'expo_anulacion_testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::getDriverName() === 'mysql') {
            Schema::dropAllTables();
        }
        $user = new User;
        $user->forceFill(['id' => 7, 'rol_id' => 1]);
        $this->actingAs($user);

        Schema::create('flujo', function (Blueprint $t) {
            $t->id(); $t->integer('tipo_tramite_id'); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('expo_oferta_seccion', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('cotizacion_origen_id');
            $t->integer('cotizacion_id'); $t->integer('numero'); $t->string('estado');
            $t->integer('prefactura_id')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('prefactura', function (Blueprint $t) {
            $t->id(); $t->string('estado'); $t->timestamps();
        });
        Schema::create('prefactura_auditoria', function (Blueprint $t) {
            $t->id(); $t->integer('prefactura_id'); $t->integer('factura_id'); $t->string('tipo_accion');
        });
        Schema::create('cotizacion_has_producto', function (Blueprint $t) {
            $t->id(); $t->integer('cotizacion_id');
        });
        Schema::create('venta_has_producto', function (Blueprint $t) {
            $t->integer('factura_id'); $t->integer('cotizacion_has_producto_id')->nullable();
        });
        Schema::create('historico_flujo', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('tipo_tramite_id'); $t->integer('tramite_id');
            $t->integer('estado_id'); $t->string('observaciones')->nullable();
            $t->integer('created_by')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('credito_revision', function (Blueprint $t) {
            $t->id(); $t->integer('flujo_id'); $t->integer('cotizacion_id'); $t->string('estado');
            $t->string('observaciones')->nullable(); $t->integer('usuario_revision')->nullable();
            $t->string('ip_revision')->nullable(); $t->timestamps();
        });
        Schema::create('credito_revision_historial', function (Blueprint $t) {
            $t->id(); $t->integer('credito_revision_id'); $t->integer('flujo_id');
            $t->string('estado_anterior'); $t->string('estado_nuevo'); $t->string('accion');
            $t->string('descripcion'); $t->integer('usuario_id'); $t->string('ip')->nullable();
            $t->timestamp('fecha_evento'); $t->timestamp('created_at')->nullable();
        });

        DB::table('flujo')->insert(['id' => 1, 'tipo_tramite_id' => 7]);
        foreach ([1, 2] as $id) {
            DB::table('prefactura')->insert(['id' => $id, 'estado' => 'convertida']);
            DB::table('expo_oferta_seccion')->insert([
                'id' => $id, 'flujo_id' => 1, 'cotizacion_origen_id' => 100,
                'cotizacion_id' => 100 + $id, 'numero' => $id, 'estado' => 'FACTURADA', 'prefactura_id' => $id,
            ]);
            DB::table('credito_revision')->insert([
                'flujo_id' => 1, 'cotizacion_id' => 100 + $id, 'estado' => 'aprobado',
            ]);
            foreach ([4 => $id, 9 => 100 + $id, 10 => 100 + $id, 3 => 200 + $id] as $tipo => $tramite) {
                DB::table('historico_flujo')->insert([
                    'flujo_id' => 1, 'tipo_tramite_id' => $tipo, 'tramite_id' => $tramite, 'estado_id' => 1,
                ]);
            }
        }
        DB::table('historico_flujo')->insert([
            'flujo_id' => 1, 'tipo_tramite_id' => 2, 'tramite_id' => 100,
            'estado_id' => 1, 'observaciones' => 'ganadora',
        ]);
    }

    public function test_factura_directa_devuelve_solo_su_seccion_y_conserva_la_ganadora(): void
    {
        DB::table('prefactura_auditoria')->insert([
            'prefactura_id' => 1, 'factura_id' => 201, 'tipo_accion' => 'facturacion_directa',
        ]);
        DB::transaction(fn () => app(DevolverSeccionAnulada::class)->porFactura(201));

        $this->assertSeccionDevuelta();
        $this->assertDatabaseHas('credito_revision_historial', [
            'accion' => 'factura_anulada', 'estado_anterior' => 'aprobado', 'estado_nuevo' => 'cancelado',
        ]);
    }

    public function test_factura_manual_localiza_la_seccion_por_sus_lineas_sin_duplicar_la_devolucion(): void
    {
        DB::table('cotizacion_has_producto')->insert(['id' => 501, 'cotizacion_id' => 101]);
        DB::table('venta_has_producto')->insert([
            ['factura_id' => 201, 'cotizacion_has_producto_id' => 501],
            ['factura_id' => 201, 'cotizacion_has_producto_id' => 501],
        ]);
        DB::transaction(fn () => app(DevolverSeccionAnulada::class)->porFactura(201));
        DB::transaction(fn () => app(DevolverSeccionAnulada::class)->porFactura(201));

        $this->assertSeccionDevuelta();
        $this->assertDatabaseCount('credito_revision_historial', 1);
        $this->assertSame(1, DB::table('historico_flujo')->where('tipo_tramite_id', 11)->count());
    }

    public function test_prefactura_conserva_la_devolucion_a_secciones(): void
    {
        DB::table('expo_oferta_seccion')->where('id', 1)->update(['estado' => 'PREFACTURADA']);
        $devuelta = DB::transaction(fn () => app(DevolverSeccionAnulada::class)->porPrefactura(1, 1));

        $this->assertTrue($devuelta);
        $this->assertSeccionDevuelta();
        $this->assertDatabaseHas('credito_revision_historial', ['accion' => 'prefactura_anulada']);
    }

    public function test_documentos_ajenos_a_expo_no_modifican_secciones_ni_el_flujo(): void
    {
        DB::transaction(fn () => app(DevolverSeccionAnulada::class)->porFactura(999));
        $this->assertFalse(DB::transaction(fn () => app(DevolverSeccionAnulada::class)->porPrefactura(2, 1)));
        $this->assertDatabaseHas('flujo', ['id' => 1, 'tipo_tramite_id' => 7]);
        $this->assertSame(2, DB::table('expo_oferta_seccion')->where('estado', 'FACTURADA')->count());
        $this->assertDatabaseCount('credito_revision_historial', 0);
    }

    public function test_la_devolucion_se_revierte_si_falla_la_transaccion_de_anulacion(): void
    {
        DB::beginTransaction();
        app(DevolverSeccionAnulada::class)->porPrefactura(1, 1);
        DB::rollBack();

        $this->assertDatabaseHas('expo_oferta_seccion', ['id' => 1, 'estado' => 'FACTURADA', 'prefactura_id' => 1]);
        $this->assertDatabaseHas('prefactura', ['id' => 1, 'estado' => 'convertida']);
        $this->assertDatabaseHas('flujo', ['id' => 1, 'tipo_tramite_id' => 7]);
        $this->assertDatabaseCount('credito_revision_historial', 0);
    }

    public static function listados(): array
    {
        return [
            'corporativa' => [ListadoFacturas::class],
            'estatal' => [ListadoFacturaEstatal::class],
            'exonerada' => [ListadoFacturasExonerads::class],
        ];
    }

    #[DataProvider('listados')]
    public function test_anulacion_desde_listado_devuelve_la_seccion(string $componente): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('La anulacion usa SQL heredado de MySQL; requiere ANULACION_TEST_MYSQL_DATABASE aislada.');
        }
        $this->prepararAnulacionFactura();
        $response = (new $componente)->anularVentaRegistro(new Request([
            'idFactura' => 201, 'motivo' => 'Corregir documento Expo',
        ]));

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('success', $response->getData()->icon);
        $this->assertDatabaseHas('factura', ['id' => 201, 'estado_venta_id' => 2]);
        $this->assertSeccionDevuelta();
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_anulacion_desde_modal_devuelve_la_seccion(): void
    {
        $this->prepararAnulacionFactura();
        Schema::create('expo_cotizacion', function (Blueprint $t) {
            $t->integer('cotizacion_id'); $t->string('estado'); $t->integer('nota_credito_id')->nullable();
        });
        Schema::create('nota_credito_movimientos', function (Blueprint $t) {
            $t->integer('factura_id'); $t->string('tipo');
        });
        Schema::create('abonos_creditos', function (Blueprint $t) {
            $t->integer('factura_id'); $t->integer('estado_abono');
        });
        Schema::create('distribuciones_entrega_facturas', function (Blueprint $t) {
            $t->integer('factura_id'); $t->string('estado_entrega'); $t->string('motivo_anulacion')->nullable(); $t->timestamps();
        });
        $modal = new ModalFlujoPedido;
        $modal->flujoId = 1;
        $modal->facturaSeleccionadaId = 201;
        $modal->motivoAnulacionFactura = 'Corregir documento Expo';
        $modal->anularFactura();

        $this->assertSame('', $modal->mensajeError);
        $this->assertDatabaseHas('factura', ['id' => 201, 'estado_venta_id' => 2]);
        $this->assertSeccionDevuelta();
        $this->assertSame(0, DB::transactionLevel());
    }

    private function prepararAnulacionFactura(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction('DATE_FORMAT', fn ($date, $format) => substr($date ?? '', 0, 7) . '-01', 2);
        }
        Schema::create('factura', function (Blueprint $t) {
            $t->id(); $t->integer('estado_venta_id'); $t->integer('cliente_id'); $t->decimal('total');
            $t->integer('users_id')->default(7); $t->integer('vendedor')->default(7);
            $t->integer('gestor_entrega')->default(7); $t->timestamps();
        });
        Schema::create('cliente', function (Blueprint $t) {
            $t->id(); $t->decimal('credito'); $t->timestamps();
        });
        Schema::create('pago_venta', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado_venta_id');
        });
        Schema::create('log_estado_factura', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado_venta_id_anterior');
            $t->integer('users_id'); $t->string('motivo'); $t->timestamps();
        });
        Schema::create('facturas_comision', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado_id'); $t->integer('rol_id');
            $t->integer('tipo_comision'); $t->decimal('monto_rol'); $t->date('fecha_cierre_factura');
        });
        Schema::create('comision_periodo', function (Blueprint $t) {
            $t->date('periodo'); $t->integer('estado');
        });
        Schema::create('aplicacion_pagos', function (Blueprint $t) {
            $t->integer('factura_id'); $t->integer('estado'); $t->timestamps();
        });
        Schema::table('venta_has_producto', function (Blueprint $t) {
            $t->integer('lote')->nullable(); $t->decimal('cantidad_s')->nullable();
            $t->decimal('numero_unidades_resta_inventario')->nullable(); $t->integer('unidad_medida_venta_id')->nullable();
        });
        DB::table('factura')->insert(['id' => 201, 'estado_venta_id' => 1, 'cliente_id' => 1, 'total' => 100]);
        DB::table('cliente')->insert(['id' => 1, 'credito' => 20]);
        DB::table('prefactura_auditoria')->insert([
            'prefactura_id' => 1, 'factura_id' => 201, 'tipo_accion' => 'facturacion_directa',
        ]);
    }

    private function assertSeccionDevuelta(): void
    {
        $this->assertDatabaseHas('expo_oferta_seccion', [
            'id' => 1, 'estado' => 'DEVUELTA_SECCION', 'prefactura_id' => null, 'updated_by' => 7,
        ]);
        $this->assertDatabaseHas('prefactura', ['id' => 1, 'estado' => 'inactive']);
        $this->assertDatabaseHas('credito_revision', ['cotizacion_id' => 101, 'estado' => 'cancelado']);
        $this->assertDatabaseHas('historico_flujo', [
            'flujo_id' => 1, 'tipo_tramite_id' => 11, 'tramite_id' => 100, 'estado_id' => 5,
        ]);
        foreach ([4 => 1, 9 => 101, 10 => 101] as $tipo => $tramite) {
            $this->assertDatabaseHas('historico_flujo', [
                'tipo_tramite_id' => $tipo, 'tramite_id' => $tramite, 'estado_id' => 7,
            ]);
        }
        $this->assertDatabaseHas('flujo', ['id' => 1, 'tipo_tramite_id' => 11]);
        $this->assertDatabaseHas('historico_flujo', ['tipo_tramite_id' => 2, 'observaciones' => 'ganadora']);
        $this->assertDatabaseHas('expo_oferta_seccion', ['id' => 2, 'estado' => 'FACTURADA', 'prefactura_id' => 2]);
        $this->assertDatabaseHas('credito_revision', ['cotizacion_id' => 102, 'estado' => 'aprobado']);
        $this->assertDatabaseHas('prefactura', ['id' => 2, 'estado' => 'convertida']);
    }
}
