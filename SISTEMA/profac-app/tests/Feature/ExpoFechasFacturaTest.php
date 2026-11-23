<?php

namespace Tests\Feature;

use App\Livewire\Flujo\PrefacturaController;
use App\Livewire\Ventas\FacturacionCorporativa;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExpoFechasFacturaTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.expo_fechas_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $mysql = getenv('EXPO_FECHAS_TEST_MYSQL_DATABASE');
        if ($mysql !== false && $mysql !== '') {
            if (!preg_match('/^profac_expo_fechas_[a-f0-9]+_testing$/D', $mysql)) {
                throw new \RuntimeException('Nombre de base aislada de pruebas invalido.');
            }
            $connection = $app['config']->get('database.connections.mysql');
            $connection['url'] = null;
            $connection['database'] = $mysql;
            $app['config']->set('database.connections.expo_fechas_testing', $connection);
        }
        $app['config']->set('database.default', 'expo_fechas_testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('expo_fechas_testing', DB::connection()->getName());
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Requiere EXPO_FECHAS_TEST_MYSQL_DATABASE aislada para facturar con MySQL.');
        }
        $this->assertMatchesRegularExpression(
            '/^profac_expo_fechas_[a-f0-9]+_testing$/D',
            DB::connection()->getDatabaseName()
        );
        Schema::dropAllTables();
        $this->crearTablas();
        $this->prepararSeccion();
        $this->travelTo(\Carbon\Carbon::parse('2026-11-06 16:16:30'));

        // Las fechas y la numeracion se guardan realmente; no se mueve inventario ni credito.
        $facturacion = $this->partialMock(FacturacionCorporativa::class);
        $facturacion->shouldReceive('restarUnidadesInventario')->andReturnNull();
        $facturacion->shouldReceive('restarCreditoCliente')->andReturnNull();
        $this->actingAs(User::findOrFail(1));
    }

    protected function tearDown(): void
    {
        try {
            if ($this->app && DB::connection()->getName() === 'expo_fechas_testing') {
                while (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                DB::disconnect();
            }
        } finally {
            parent::tearDown();
        }
    }

    public static function vencimientos(): array
    {
        return [
            'vencida como flujo 4669' => ['2026-11-03'],
            'vence el dia de emision' => ['2026-11-06'],
            'vence despues de emision' => ['2026-12-03'],
        ];
    }

    #[DataProvider('vencimientos')]
    public function test_facturacion_directa_conserva_vencimiento_y_credito_de_la_seccion(string $vencimiento): void
    {
        DB::table('prefactura')->where('id', 3651)->update(['fecha_vencimiento' => $vencimiento]);
        DB::table('cotizacion')->where('id', 43956)->update(['fecha_vencimiento' => $vencimiento]);

        $response = app(PrefacturaController::class)->facturarDirectamente(
            Request::create('/prefactura/3651/facturar-directo', 'POST', ['tele_asesor' => 1]),
            3651
        );
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $payload = $response->getData(true);
        $this->assertSame('success', $payload['icon'], $response->getContent());
        $this->assertFactura($payload['factura_id'], $vencimiento, 2);
        $this->assertDatabaseHas('prefactura', ['id' => 3651, 'estado' => 'convertida']);
        $this->assertDatabaseHas('expo_oferta_seccion', ['prefactura_id' => 3651, 'estado' => 'FACTURADA']);
        $auditoria = DB::table('prefactura_auditoria')->where('factura_id', $payload['factura_id'])->first();
        $this->assertSame($vencimiento, json_decode($auditoria->datos_nuevos, true)['fecha_vencimiento']);
    }

    public static function numeraciones(): array
    {
        return [
            'presentada' => ['presentada'],
            'sin declarar' => ['sin_declarar'],
            'listado' => ['listado'],
            'enumeracion' => ['enumeracion'],
            'alterna por numero duplicado' => ['alterna'],
        ];
    }

    #[DataProvider('numeraciones')]
    public function test_facturacion_directa_vencida_en_cada_numeracion(string $numeracion): void
    {
        $this->prepararNumeracion($numeracion);
        $response = app(PrefacturaController::class)->facturarDirectamente(
            Request::create('/prefactura/3651/facturar-directo', 'POST', ['tele_asesor' => 1]),
            3651
        );
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertFactura($response->getData(true)['factura_id'], '2026-11-03', 2);
    }

    #[DataProvider('numeraciones')]
    public function test_formulario_ignora_fecha_recalculada_y_conserva_la_prefactura(string $numeracion): void
    {
        $this->prepararNumeracion($numeracion);
        $response = app(FacturacionCorporativa::class)->guardarVenta($this->solicitudVenta());
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $payload = $response->getData(true);
        $this->assertSame('success', $payload['icon'], $response->getContent());
        $this->assertFactura($payload['idFactura'], '2026-11-03', 2);
    }

    public function test_oferta_expo_sin_seccion_tambien_conserva_la_fecha(): void
    {
        DB::table('expo_oferta_seccion')->delete();
        $response = app(FacturacionCorporativa::class)->guardarVenta($this->solicitudVenta());
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertFactura($response->getData(true)['idFactura'], '2026-11-03', 2);
    }

    public function test_editar_desde_prefactura_ignora_fechas_enviadas_por_el_formulario(): void
    {
        $request = $this->solicitudVenta();
        $request->merge([
            'modo' => 'editar_factura',
            'fecha_emision' => '2026-11-11',
            'fecha_vencimiento' => '2027-01-10',
        ]);
        $response = app(FacturacionCorporativa::class)->guardarVenta($request);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertFactura($response->getData(true)['idFactura'], '2026-11-03', 2);
    }

    public function test_factura_normal_sigue_calculando_los_dias_aprobados(): void
    {
        DB::table('expo_oferta_seccion')->delete();
        DB::table('expo_cotizacion')->delete();
        $request = $this->solicitudVenta();
        $request->merge(['tipoPagoVenta' => 2]);
        $response = app(FacturacionCorporativa::class)->guardarVenta($request);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $factura = DB::table('factura')->find($response->getData(true)['idFactura']);
        $this->assertSame('2027-01-05', $factura->fecha_vencimiento);
        $this->assertSame(60, (int) $factura->dias_credito);
        $this->assertSame(2, (int) $factura->tipo_pago_id);
    }

    #[DataProvider('numeraciones')]
    public function test_impide_confirmar_si_la_base_reemplaza_el_vencimiento(string $numeracion): void
    {
        $this->prepararNumeracion($numeracion);
        $facturasAntes = DB::table('factura')->count();
        $numeroAntes = DB::table('cai')->where('id', 1)->value('numero_actual');
        DB::unprepared("CREATE TRIGGER prueba_vencimiento BEFORE INSERT ON factura
            FOR EACH ROW SET NEW.fecha_vencimiento = '2027-01-05'");

        $response = app(FacturacionCorporativa::class)->guardarVenta($this->solicitudVenta());
        $this->assertSame(422, $response->getStatusCode(), $response->getContent());
        $this->assertArrayHasKey('condiciones_pago_expo', $response->getData(true)['errors']);
        $this->assertSame($facturasAntes, DB::table('factura')->count());
        $this->assertSame($numeroAntes, DB::table('cai')->where('id', 1)->value('numero_actual'));
        if (in_array($numeracion, ['listado', 'enumeracion', 'alterna'], true)) {
            $tabla = $numeracion === 'listado' ? 'listado' : 'enumeracion';
            $this->assertSame(0, (int) DB::table($tabla)->where('id', 1)->value('eliminado'));
        }
    }

    public function test_rechaza_prefactura_sin_vencimiento_en_vez_de_inventar_una_fecha(): void
    {
        DB::table('prefactura')->where('id', 3651)->update(['fecha_vencimiento' => null]);
        $response = app(FacturacionCorporativa::class)->guardarVenta($this->solicitudVenta());
        $this->assertSame(422, $response->getStatusCode(), $response->getContent());
        $this->assertArrayHasKey('fecha_vencimiento', $response->getData(true)['errors']);
        $this->assertSame(0, DB::table('factura')->count());
    }

    public function test_prefactura_expo_de_contado_conserva_su_tipo_de_pago(): void
    {
        DB::table('cotizacion')->where('id', 43956)->update(['tipo_pago_id' => 1]);
        DB::table('prefactura')->where('id', 3651)->update(['fecha_vencimiento' => '2026-11-06']);
        $request = $this->solicitudVenta();
        $request->merge(['tipoPagoVenta' => 2]);
        $response = app(FacturacionCorporativa::class)->guardarVenta($request);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertFactura($response->getData(true)['idFactura'], '2026-11-06', 1);
    }

    public function test_oferta_antigua_sin_tipo_de_pago_conserva_inferencia_por_fechas(): void
    {
        DB::table('cotizacion')->where('id', 43956)->update(['tipo_pago_id' => null]);
        DB::table('prefactura')->where('id', 3651)->update(['fecha_vencimiento' => '2026-12-03']);
        $response = app(FacturacionCorporativa::class)->guardarVenta($this->solicitudVenta());
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertFactura($response->getData(true)['idFactura'], '2026-12-03', 2);
    }

    public function test_factura_normal_de_contado_no_se_convierte_a_credito(): void
    {
        DB::table('expo_oferta_seccion')->delete();
        DB::table('expo_cotizacion')->delete();
        $response = app(FacturacionCorporativa::class)->guardarVenta($this->solicitudVenta());
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertFactura($response->getData(true)['idFactura'], '2026-11-06', 1);
    }

    private function assertFactura(int $id, string $vencimiento, int $tipoPago): void
    {
        $factura = DB::table('factura')->find($id);
        $this->assertNotNull($factura);
        $this->assertSame($vencimiento, $factura->fecha_vencimiento);
        $this->assertSame('2026-11-06', $factura->fecha_emision);
        $this->assertSame($tipoPago, (int) $factura->tipo_pago_id);
        $dias = max(0, (int) \Carbon\Carbon::parse('2026-11-06')
            ->diffInDays(\Carbon\Carbon::parse($vencimiento), false));
        $this->assertSame($dias, (int) $factura->dias_credito);
        $this->assertEquals(100, $factura->total);
    }

    private function solicitudVenta(): Request
    {
        return Request::create('/ventas/guardar', 'POST', [
            'prefactura_id' => 3651, 'flujo_id' => 4669,
            'fecha_emision' => '2026-11-06', 'fecha_vencimiento' => '2027-01-05',
            'tipoPagoVenta' => 1, 'seleccionarCliente' => 1,
            'nombre_cliente_ventas' => 'Cliente de prueba', 'rtn_ventas' => '00000000000000',
            'vendedor' => 1, 'tele_asesor' => 1, 'restriccion' => 0,
            'subTotalGeneralGrabado' => 0, 'subTotalGeneral' => 100,
            'subTotalGeneralExcento' => 100, 'isvGeneral' => 0, 'totalGeneral' => 100,
            'porDescuento' => 0, 'porDescuentoCalculado' => 0,
            'numeroInputs' => 1, 'arregloIdInputs' => '1',
            'idProducto1' => 1, 'idSeccion1' => 1, 'idUnidadVenta1' => 1,
            'restaInventario1' => 1, 'nombre1' => 'Producto de prueba', 'bodega1' => 'Bodega',
            'cantidad1' => 1, 'precio1' => 100, 'subTotal1' => 100,
            'isvProducto1' => 0, 'isv1' => 0, 'total1' => 100, 'unidad1' => 1,
        ]);
    }

    private function prepararNumeracion(string $modo): void
    {
        DB::table('parametro')->update(['estado_encendido' => $modo === 'presentada' ? 1 : 0]);
        if (in_array($modo, ['listado', 'enumeracion', 'alterna'], true)) {
            $secuencia = $modo === 'listado' ? 100 : 1;
            DB::table($modo === 'listado' ? 'listado' : 'enumeracion')->insert([
                'id' => 1, 'numero' => sprintf('000-001-01-%08d', $secuencia), 'secuencia' => $secuencia,
                'numero_inicial' => '000-001-01-00000001', 'numero_final' => '000-001-01-00000100',
                'cantidad_otorgada' => 100, 'cai_id' => 1, 'estado' => 2, 'eliminado' => 0,
            ]);
        }
        if ($modo === 'alterna') {
            DB::table('factura')->insert([
                'id' => 1, 'cai' => '000-001-01-00000001', 'cai_id' => 1,
                'numero_secuencia_cai' => 1, 'estado_venta_id' => 1, 'cliente_id' => 1,
            ]);
        }
    }

    private function prepararSeccion(): void
    {
        DB::table('users')->insert(['id' => 1, 'name' => 'Usuario de prueba', 'estado_id' => 1]);
        DB::table('cliente')->insert(['id' => 1, 'nombre' => 'Cliente de prueba', 'rtn' => '00000000000000', 'dias_credito' => 90]);
        DB::table('cliente_usuario')->insert([
            ['cliente_id' => 1, 'usuario_id' => 1, 'rol_id' => 2],
            ['cliente_id' => 1, 'usuario_id' => 1, 'rol_id' => 3],
        ]);
        DB::table('flujo')->insert(['id' => 4669, 'identificacion' => 'OFERTA', 'numero_orden_compra' => '']);
        DB::table('cotizacion')->insert([
            'id' => 43956, 'cliente_id' => 1, 'tipo_pago_id' => 2,
            'fecha_emision' => '2026-09-04', 'fecha_vencimiento' => '2026-11-03',
        ]);
        DB::table('expo_cotizacion')->insert(['cotizacion_id' => 43956]);
        DB::table('expo_oferta_seccion')->insert([
            'flujo_id' => 4669, 'numero' => 1, 'prefactura_id' => 3651,
            'cotizacion_origen_id' => 41091, 'cotizacion_id' => 43956, 'estado' => 'PREFACTURADA',
        ]);
        DB::table('credito_revision')->insert([
            'flujo_id' => 4669, 'cotizacion_id' => 43956, 'estado' => 'aprobado',
            'dias_credito_aprobados' => 60, 'fecha_aprobacion' => '2026-11-06',
            'fecha_vencimiento_credito' => '2026-11-03',
        ]);
        DB::table('prefactura')->insert([
            'id' => 3651, 'cotizacion_id' => 43956, 'flujo_id' => 4669, 'cliente_id' => 1,
            'nombre_cliente' => 'Cliente de prueba', 'RTN' => '00000000000000',
            'fecha_emision' => '2026-11-06', 'fecha_vencimiento' => '2026-11-03',
            'sub_total_grabado' => 0, 'sub_total' => 100, 'sub_total_excento' => 100,
            'isv' => 0, 'total' => 100, 'numeroInputs' => 1, 'vendedor' => 1, 'estado' => 'activo',
            'created_at' => '2026-11-06 13:23:33',
        ]);
        DB::table('prefactura_has_producto')->insert([
            'prefactura_id' => 3651, 'producto_id' => 1, 'indice' => 1,
            'nombre_producto' => 'Producto de prueba', 'nombre_bodega' => 'Bodega', 'seccion_id' => 1,
            'cantidad' => 1, 'resta_inventario' => 1, 'precio_unidad' => 100,
            'sub_total' => 100, 'isv' => 0, 'total' => 100, 'isv_producto' => 0,
        ]);
        DB::table('recibido_bodega')->insert(['producto_id' => 1, 'seccion_id' => 1, 'cantidad_disponible' => 100]);
        DB::table('parametro')->insert(['id' => 1, 'estado_encendido' => 1, 'turno' => 1]);
        DB::table('cai')->insert([
            'id' => 1, 'numero_inicial' => '000-001-01-00000001', 'numero_final' => '000-001-01-00000100',
            'cantidad_otorgada' => 100, 'numero_actual' => 1, 'tipo_documento_fiscal_id' => 1, 'estado_id' => 1,
        ]);
    }

    private function crearTablas(): void
    {
        $tablas = [
            'users' => ['name', 'estado_id'],
            'cliente' => ['nombre', 'rtn', 'dias_credito'],
            'cliente_usuario' => ['cliente_id', 'usuario_id', 'rol_id'],
            'flujo' => ['identificacion', 'numero_orden_compra', 'tipo_tramite_id', 'updated_by'],
            'cotizacion' => ['cliente_id', 'tipo_pago_id', 'fecha_emision', 'fecha_vencimiento', 'zone_group_id', 'direccion_entrega'],
            'expo_cotizacion' => ['cotizacion_id'],
            'expo_oferta_seccion' => ['flujo_id', 'numero', 'prefactura_id', 'cotizacion_origen_id', 'cotizacion_id', 'estado', 'updated_by'],
            'credito_revision' => ['flujo_id', 'cotizacion_id', 'estado', 'dias_credito_aprobados', 'fecha_aprobacion', 'fecha_vencimiento_credito'],
            'prefactura' => ['cotizacion_id', 'flujo_id', 'cliente_id', 'nombre_cliente', 'RTN', 'fecha_emision', 'fecha_vencimiento', 'sub_total_grabado', 'sub_total', 'sub_total_excento', 'isv', 'total', 'numeroInputs', 'vendedor', 'estado', 'porc_descuento', 'monto_descuento', 'nota'],
            'prefactura_has_producto' => ['prefactura_id', 'producto_id', 'indice', 'nombre_producto', 'nombre_bodega', 'seccion_id', 'cantidad', 'resta_inventario', 'precio_unidad', 'sub_total', 'isv', 'total', 'isv_producto', 'unidad_medida_venta_id', 'idPrecioSeleccionado', 'precioSeleccionado', 'precios_producto_carga_id'],
            'cotizacion_has_producto' => ['cotizacion_id', 'producto_id', 'indice'],
            'categoria_precios' => ['nombre', 'cliente_categoria_escala_id'],
            'precios_producto_carga' => ['categoria_precios_id', 'producto_id', 'estado_id'],
            'recibido_bodega' => ['producto_id', 'seccion_id', 'cantidad_disponible'],
            'configuracion_prefactura' => ['dias_validez'],
            'parametro' => ['estado_encendido', 'turno'],
            'cai' => ['numero_inicial', 'numero_final', 'cantidad_otorgada', 'numero_actual', 'tipo_documento_fiscal_id', 'estado_id', 'cantidad_no_utilizada'],
            'listado' => ['numero', 'secuencia', 'numero_inicial', 'numero_final', 'cantidad_otorgada', 'cai_id', 'estado', 'eliminado'],
            'enumeracion' => ['numero', 'secuencia', 'numero_inicial', 'numero_final', 'cantidad_otorgada', 'cai_id', 'estado', 'eliminado'],
            'factura' => ['numero_factura', 'cai', 'numero_secuencia_cai', 'nombre_cliente', 'rtn', 'sub_total', 'sub_total_grabado', 'sub_total_excento', 'isv', 'total', 'credito', 'fecha_emision', 'fecha_vencimiento', 'tipo_pago_id', 'dias_credito', 'cai_id', 'estado_venta_id', 'cliente_id', 'vendedor', 'gestor_entrega', 'monto_comision', 'tipo_venta_id', 'estado_factura_id', 'users_id', 'comision_estado_pagado', 'pendiente_cobro', 'estado_editar', 'codigo_autorizacion_id', 'comprovante_entrega_id', 'comentario', 'numero_orden_compra_id', 'porc_descuento', 'monto_descuento', 'tipo_factura_id'],
            'venta_has_producto' => ['factura_id'],
            'log_translado' => [],
            'factura_tratamiento_entrega' => ['factura_id'],
            'aplicacion_pagos' => ['factura_id', 'cliente_id', 'total_factura_cargo', 'retencion_isv_factura', 'estado_retencion_isv', 'retencion_aplicada', 'total_notas_credito', 'total_nodas_debito', 'credito_abonos', 'movimiento_suma', 'movimiento_resta', 'saldo', 'ultimo_usr_actualizo', 'estado', 'estado_cerrado'],
            'historico_flujo' => ['flujo_id', 'tipo_tramite_id', 'tramite_id', 'estado_id', 'observaciones', 'created_by', 'updated_by'],
            'prefactura_auditoria' => ['tipo_accion', 'prefactura_id', 'factura_id', 'datos_anteriores', 'datos_nuevos', 'motivo', 'usuario_id', 'autorizador_id', 'autorizacion_id', 'ip', 'session_id'],
        ];
        foreach ($tablas as $nombre => $columnas) {
            Schema::create($nombre, function (Blueprint $t) use ($columnas) {
                $t->id();
                foreach ($columnas as $columna) {
                    $t->text($columna)->nullable();
                }
                $t->timestamps();
            });
        }
    }
}
