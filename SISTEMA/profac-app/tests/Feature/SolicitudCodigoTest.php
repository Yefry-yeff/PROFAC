<?php

namespace Tests\Feature;

use App\Livewire\Ventas\SinRestriccionPrecio;
use App\Models\ConfiguracionCodigoAutorizacion;
use App\Models\ModelCodigoAutorizacion;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class SolicitudCodigoTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.solicitud_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $app['config']->set('database.default', 'solicitud_testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('configuracion_codigo_autorizacion', function (Blueprint $table) {
            $table->id();
            $table->integer('tiempo_expiracion_minutos');
            $table->boolean('expiracion_activa');
            $table->timestamps();
        });
        Schema::create('codigo_autorizacion', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo');
            $table->integer('users_id');
            $table->integer('estado_id');
            $table->integer('flujo_id')->nullable();
            $table->string('tipo_tramite');
            $table->integer('estado_codigo_id');
            $table->dateTime('fecha_expiracion')->nullable();
            $table->timestamps();
        });

        $usuario = new User();
        $usuario->id = 73;
        $usuario->name = 'Solicitante';
        $this->actingAs($usuario);
        $this->travelTo(now()->startOfSecond());
    }

    public function test_envio_exitoso_conserva_codigo_y_datos_del_correo(): void
    {
        $productos = [['nombre' => 'Producto', 'precioOpc' => 100, 'precioUnitario' => 90]];
        Mail::shouldReceive('send')->once()->withArgs(function ($vista, $datos, $callback) use ($productos) {
            $this->assertSame('email/solicitud', $vista);
            $this->assertSame($productos, $datos['productos']);
            $this->assertSame('Solicitante', $datos['usuario']);
            $this->assertSame(4487, $datos['flujoId']);
            $this->assertSame('FAC-1', $datos['numeroVenta']);
            $this->assertSame($datos['codigo'], ModelCodigoAutorizacion::sole()->codigo);
            $this->assertIsCallable($callback);

            return true;
        });

        $response = (new SinRestriccionPrecio())->enviarCodigo(new Request([
            'productos' => $productos, 'flujo_id' => 4487, 'numero_venta' => 'FAC-1',
        ]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['message' => 'exito'], $response->getData(true));
        $codigo = ModelCodigoAutorizacion::sole();
        $this->assertSame(73, $codigo->users_id);
        $this->assertSame(4487, $codigo->flujo_id);
        $this->assertSame('facturacion', $codigo->tipo_tramite);
        $this->assertSame(1, $codigo->estado_id);
        $this->assertSame(1, $codigo->estado_codigo_id);
        $this->assertTrue($codigo->fecha_expiracion->equalTo(now()->addMinutes(10)));
    }

    public function test_fallo_smtp_responde_503_sin_dejar_un_codigo_pendiente(): void
    {
        Mail::shouldReceive('send')->once()->andThrow(new TransportException('certificate verify failed'));

        $response = (new SinRestriccionPrecio())->enviarCodigo(new Request(['flujo_id' => 4487]));

        $this->assertSame(503, $response->getStatusCode());
        $this->assertStringContainsString('No se pudo enviar el correo', $response->getData(true)['message']);
        $this->assertStringNotContainsString('certificate', $response->getData(true)['message']);
        $this->assertSame(0, ModelCodigoAutorizacion::count());
    }

    public function test_reintento_exitoso_despues_de_fallo_solo_registra_un_codigo(): void
    {
        Mail::shouldReceive('send')->once()->ordered()->andThrow(new TransportException('SMTP unavailable'));
        Mail::shouldReceive('send')->once()->ordered()->andReturnNull();
        $controller = new SinRestriccionPrecio();

        $this->assertSame(503, $controller->enviarCodigo(new Request())->getStatusCode());
        $this->assertSame(200, $controller->enviarCodigo(new Request())->getStatusCode());
        $this->assertSame(1, ModelCodigoAutorizacion::count());
    }

    public function test_sin_flujo_y_sin_expiracion_conserva_comportamiento_anterior(): void
    {
        ConfiguracionCodigoAutorizacion::create([
            'tiempo_expiracion_minutos' => 10, 'expiracion_activa' => false,
        ]);
        Mail::shouldReceive('send')->once()->andReturnNull();

        $response = (new SinRestriccionPrecio())->enviarCodigo(new Request());

        $this->assertSame(200, $response->getStatusCode());
        $codigo = ModelCodigoAutorizacion::sole();
        $this->assertNull($codigo->flujo_id);
        $this->assertNull($codigo->fecha_expiracion);
    }
}
