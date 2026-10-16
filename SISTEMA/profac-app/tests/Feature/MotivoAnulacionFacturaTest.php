<?php

namespace Tests\Feature;

use App\Livewire\Ventas\ListadoFacturaEstatal;
use App\Livewire\Ventas\ListadoFacturas;
use App\Livewire\Ventas\ListadoFacturasAnuladas;
use App\Livewire\VentasExoneradas\ListadoFacturasExonerads;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MotivoAnulacionFacturaTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.anulacion_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $mysql = getenv('ANULACION_TEST_MYSQL_DATABASE');
        if ($mysql !== false && $mysql !== '') {
            if (!preg_match('/^profac_anulacion_[a-f0-9]+_testing$/D', $mysql)) {
                throw new \RuntimeException('Nombre de base aislada de pruebas invalido.');
            }
            $connection = $app['config']->get('database.connections.mysql');
            $connection['database'] = $mysql;
            $app['config']->set('database.connections.anulacion_testing', $connection);
        }
        $app['config']->set('database.default', 'anulacion_testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('anulacion_testing', DB::connection()->getName());
        if (DB::getDriverName() === 'mysql') {
            Schema::dropAllTables();
        } else {
            DB::connection()->getPdo()->sqliteCreateFunction('DATE_FORMAT', fn ($date, $format) => substr($date ?? '', 0, 7) . '-01', 2);
        }
        Schema::create('factura', function (Blueprint $t) {
            $t->id(); $t->string('cai')->default('000-001-01-00000001');
            $t->integer('estado_venta_id'); $t->integer('cliente_id');
            $t->decimal('total'); $t->integer('users_id')->default(1);
            $t->integer('vendedor')->default(1); $t->integer('gestor_entrega')->default(1); $t->timestamps();
        });
        Schema::create('cliente', function (Blueprint $t) {
            $t->id(); $t->decimal('credito'); $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name');
        });
        Schema::create('pago_venta', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado_venta_id');
        });
        Schema::create('log_estado_factura', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado_venta_id_anterior');
            $t->integer('users_id'); $t->text('motivo'); $t->timestamps();
        });
        Schema::create('venta_has_producto', function (Blueprint $t) {
            $t->integer('factura_id'); $t->integer('lote'); $t->decimal('cantidad_s');
            $t->decimal('numero_unidades_resta_inventario'); $t->integer('unidad_medida_venta_id');
        });
        Schema::create('facturas_comision', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado_id');
            $t->integer('rol_id'); $t->integer('tipo_comision'); $t->decimal('monto_rol');
            $t->date('fecha_cierre_factura');
        });
        Schema::create('comision_periodo', function (Blueprint $t) {
            $t->date('periodo'); $t->integer('estado');
        });
        Schema::create('aplicacion_pagos', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('estado');
        });
        DB::table('factura')->insert(['id' => 1, 'estado_venta_id' => 1, 'cliente_id' => 1, 'total' => 100]);
        DB::table('cliente')->insert(['id' => 1, 'credito' => 20]);
        DB::table('aplicacion_pagos')->insert(['factura_id' => 1, 'estado' => 1]);
        DB::table('users')->insert(['id' => 7, 'name' => 'Usuario de pruebas']);
        $user = new User;
        $user->forceFill(['id' => 7, 'rol_id' => 1]);
        Auth::setUser($user);
    }

    public static function listados(): array
    {
        return [
            'corporativo' => [ListadoFacturas::class],
            'estatal' => [ListadoFacturaEstatal::class],
            'exonerado' => [ListadoFacturasExonerads::class],
        ];
    }

    #[DataProvider('listados')]
    public function test_guarda_el_motivo_real_sin_sustituirlo_y_conserva_la_anulacion(string $componente): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('La anulacion usa SQL heredado de MySQL; requiere ANULACION_TEST_MYSQL_DATABASE aislada.');
        }
        $motivo = "Cliente solicita corregir el RTN.\nSe emitira una nueva factura.";
        $response = (new $componente)->anularVentaRegistro(new Request([
            'idFactura' => 1, 'motivo' => '  ' . $motivo . '  ',
        ]));

        $this->assertSame(200, $response->getStatusCode(), isset($response->getOriginalContent()['error'])
            ? $response->getOriginalContent()['error']->getMessage() : '');
        $this->assertSame('success', $response->getData()->icon);
        $this->assertDatabaseHas('log_estado_factura', [
            'factura_id' => 1, 'motivo' => $motivo, 'users_id' => 7, 'estado_venta_id_anterior' => 1,
        ]);
        $this->assertDatabaseHas('factura', ['id' => 1, 'estado_venta_id' => 2]);
        $this->assertDatabaseHas('cliente', ['id' => 1, 'credito' => 120]);
        $this->assertDatabaseHas('aplicacion_pagos', ['factura_id' => 1, 'estado' => 2]);
        $this->assertSame(0, DB::transactionLevel());
        $detalle = (new ListadoFacturasAnuladas)->detalleFacturaAnulada(new Request(['id' => 1]));
        $this->assertSame(200, $detalle->getStatusCode());
        $this->assertSame($motivo, $detalle->getData()->datos->motivo);
    }

    public static function motivosInvalidos(): array
    {
        $cases = [];
        foreach (self::listados() as $nombre => [$componente]) {
            foreach (['ausente' => null, 'vacio' => '', 'espacios' => " \n ", 'arreglo' => ['motivo']] as $tipo => $valor) {
                $cases[$nombre . '-' . $tipo] = [$componente, $valor];
            }
        }
        return $cases;
    }

    #[DataProvider('motivosInvalidos')]
    public function test_rechaza_motivos_invalidos_antes_de_modificar_la_factura(string $componente, mixed $motivo): void
    {
        try {
            (new $componente)->anularVentaRegistro(new Request(['idFactura' => 1, 'motivo' => $motivo]));
            $this->fail('Debio rechazar el motivo invalido.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('motivo', $error->errors());
        }
        $this->assertDatabaseHas('factura', ['id' => 1, 'estado_venta_id' => 1]);
        $this->assertDatabaseHas('cliente', ['id' => 1, 'credito' => 20]);
        $this->assertDatabaseCount('log_estado_factura', 0);
        $this->assertSame(0, DB::transactionLevel());
    }
}
