<?php

namespace Tests\Feature;

use App\Livewire\Logistica\ConfirmacionEntrega;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CantidadDevolucionIncidenciaTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set([
            'database.default' => 'incidencias_testing',
            'database.connections.incidencias_testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('entregas_productos', function (Blueprint $table) {
            $table->id();
            $table->integer('producto_id');
            $table->integer('distribucion_factura_id')->nullable();
            $table->decimal('cantidad_facturada', 10, 2);
            $table->decimal('cantidad_entregada', 10, 2)->default(0);
            $table->boolean('entregado')->default(false);
            $table->boolean('tiene_incidencia')->default(false);
            $table->string('tipo_incidencia')->nullable();
            $table->text('descripcion_incidencia')->nullable();
            $table->integer('user_id_registro')->nullable();
            $table->timestamp('fecha_registro')->nullable();
            $table->timestamps();
        });
        Schema::create('entregas_productos_incidencias', function (Blueprint $table) {
            $table->id();
            $table->integer('entrega_producto_id');
            $table->string('tipo');
            $table->text('descripcion');
            $table->integer('user_id_registro')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations\2026_11_17_000001_add_cantidad_devolucion_to_entregas_productos_incidencias.php'))->up();
        DB::table('entregas_productos')->insert(['id' => 1, 'producto_id' => 97, 'cantidad_facturada' => '2.50']);
    }

    public function test_both_endpoints_share_the_remaining_quantity_and_accept_the_exact_limit(): void
    {
        $controller = app(ConfirmacionEntrega::class);
        $this->assertSame(201, $controller->registrarIncidenciaProducto(
            Request::create('/', 'POST', $this->incidencia('1.25')), 1
        )->getStatusCode());

        $respuesta = $controller->confirmarEntregaProductos(Request::create('/', 'POST', [
            'hora_entrega' => '10:00',
            'incidencias' => [$this->incidencia('0.50'), $this->incidencia('0.76')],
        ]));
        $this->assertSame(422, $respuesta->getStatusCode());
        $this->assertDatabaseCount('entregas_productos_incidencias', 1);

        $respuesta = $controller->confirmarEntregaProductos(Request::create('/', 'POST', [
            'hora_entrega' => '10:00',
            'incidencias' => [$this->incidencia('0.50'), $this->incidencia('0.75')],
        ]));
        $this->assertSame(200, $respuesta->getStatusCode(), $respuesta->getContent());
        $this->assertDatabaseCount('entregas_productos_incidencias', 3);
        $this->assertEquals(2.50, DB::table('entregas_productos_incidencias')->sum('cantidad_devolucion'));
        $this->assertSame(422, $controller->registrarIncidenciaProducto(
            Request::create('/', 'POST', $this->incidencia('0.01')), 1
        )->getStatusCode());
    }

    public function test_quantity_is_required_positive_and_has_at_most_two_decimals(): void
    {
        $controller = app(ConfirmacionEntrega::class);
        foreach ([null, '0', '-1', '0.015'] as $cantidad) {
            $respuesta = $controller->registrarIncidenciaProducto(
                Request::create('/', 'POST', $this->incidencia($cantidad)), 1
            );
            $this->assertSame(422, $respuesta->getStatusCode());
            $this->assertArrayHasKey('cantidad_devolucion', $respuesta->getData(true)['errors']);
        }
        $this->assertDatabaseCount('entregas_productos_incidencias', 0);
    }

    private function incidencia(?string $cantidad): array
    {
        return [
            'producto_id' => 1,
            'tipo' => 'producto_danado',
            'descripcion' => 'Empaque dañado',
            'cantidad_devolucion' => $cantidad,
        ];
    }
}
