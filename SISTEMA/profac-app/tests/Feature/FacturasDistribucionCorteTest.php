<?php

namespace Tests\Feature;

use App\Livewire\Logistica\DistribucionEntrega;
use App\Livewire\LogisticaDeEntregas\GestionDeFacturas;
use App\Livewire\LogisticaDeEntregas\AgrupacionesDeEntregas;
use App\Support\Logistica\FacturasDistribucion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FacturasDistribucionCorteTest extends TestCase
{
    private string $conexionOriginal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conexionOriginal = config('database.default');
        config([
            'database.default' => 'logistica_test',
            'database.connections.logistica_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);
        Carbon::setTestNow('2026-10-13 21:00:00');
        Auth::shouldReceive('user')->andReturn((object) ['rol_id' => 1]);

        foreach ([
            'CREATE TABLE factura (id INTEGER PRIMARY KEY, cai TEXT, numero_factura TEXT, total NUMERIC, fecha_emision TEXT, estado_factura_id INTEGER, estado_venta_id INTEGER, cliente_id INTEGER, gestor_entrega INTEGER, vendedor INTEGER)',
            'CREATE TABLE cliente (id INTEGER PRIMARY KEY, nombre TEXT, direccion TEXT)',
            'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)',
            'CREATE TABLE departamento (id INTEGER PRIMARY KEY, nombre TEXT)',
            'CREATE TABLE municipio (id INTEGER PRIMARY KEY, nombre TEXT)',
            'CREATE TABLE equipos_entrega (id INTEGER PRIMARY KEY, nombre_equipo TEXT)',
            'CREATE TABLE distribuciones_entrega (id INTEGER PRIMARY KEY, estado_id INTEGER, equipo_entrega_id INTEGER, fecha_programada TEXT)',
            'CREATE TABLE distribuciones_entrega_facturas (id INTEGER PRIMARY KEY, factura_id INTEGER, distribucion_entrega_id INTEGER, estado_entrega TEXT, fecha_entrega_real TEXT)',
            'CREATE TABLE factura_tratamiento_entrega (id INTEGER PRIMARY KEY, factura_id INTEGER, zone_group_id INTEGER, department_id INTEGER, municipality_id INTEGER, direccion_entrega TEXT, updated_at TEXT)',
            'CREATE TABLE factura_tratamiento_entrega_historial (id INTEGER PRIMARY KEY AUTOINCREMENT, factura_id INTEGER, estado TEXT, distribucion_entrega_id INTEGER, department_id INTEGER, municipality_id INTEGER, direccion_entrega TEXT, observaciones TEXT, user_id INTEGER, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE venta_has_producto (id INTEGER PRIMARY KEY, factura_id INTEGER)',
            'CREATE TABLE zone_groups (id INTEGER PRIMARY KEY, name TEXT, description TEXT, status INTEGER, orden INTEGER)',
            'CREATE TABLE zone_group_details (id INTEGER PRIMARY KEY, zone_group_id INTEGER, department_id INTEGER, municipality_id INTEGER, status INTEGER)',
        ] as $sql) {
            DB::statement($sql);
        }

        DB::table('cliente')->insert(['id' => 1, 'nombre' => 'Cliente de prueba']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Gestor']);
        DB::table('equipos_entrega')->insert(['id' => 1, 'nombre_equipo' => 'Equipo']);
        DB::table('distribuciones_entrega')->insert([
            'id' => 1, 'estado_id' => 1, 'equipo_entrega_id' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::purge('logistica_test');
        config(['database.default' => $this->conexionOriginal]);
        parent::tearDown();
    }

    private function factura(int $id, string $fecha, int $estadoVenta = 1): void
    {
        DB::table('factura')->insert([
            'id' => $id, 'cai' => "FAC-{$id}", 'numero_factura' => (string) $id,
            'total' => 100, 'fecha_emision' => $fecha, 'estado_factura_id' => 1,
            'estado_venta_id' => $estadoVenta, 'cliente_id' => 1, 'gestor_entrega' => 1,
        ]);
    }

    private function migracion()
    {
        return require database_path('migrations/2026_10_13_000001_completar_facturas_historicas_glpi_330.php');
    }

    public function test_rango_incluye_el_corte_y_todo_hoy_pero_no_manana(): void
    {
        $this->factura(1, '2026-05-18 23:59:59');
        $this->factura(2, '2026-05-19 00:00:00');
        $this->factura(3, '2026-10-13 23:59:59');
        $this->factura(4, '2026-10-14 00:00:00');

        $this->assertSame([2, 3], DB::table('factura as f')
            ->whereRaw(FacturasDistribucion::condicionFecha())
            ->orderBy('id')->pluck('id')->all());
        $this->assertSame([2, 3], DB::table('factura as f2')
            ->whereRaw(FacturasDistribucion::condicionFecha('f2'))
            ->orderBy('id')->pluck('id')->all());

        $distribucion = new DistribucionEntrega();
        $facturas = $distribucion->autocompletadoFacturas(new Request(['termino' => 'FAC']))->getData(true);
        $this->assertTrue($facturas['success']);
        $this->assertSame([3, 2], array_column($facturas['facturas'], 'id'));
        $clientes = $distribucion->autocompletadoClientes(new Request(['termino' => 'Cliente']))->getData(true);
        $this->assertTrue($clientes['success']);
        $this->assertSame(2, $clientes['clientes'][0]['facturas_disponibles']);

        $agrupaciones = new AgrupacionesDeEntregas();
        $resumen = $agrupaciones->resumenZonas()->getData(true);
        $this->assertTrue($resumen['success']);
        $this->assertSame(2, $resumen['sin_clasificar']);
        $zona = $agrupaciones->facturasPorZona(new Request(['zona_id' => 'sin_clasificar']))->getData(true);
        $this->assertTrue($zona['success']);
        $this->assertSame([3, 2], array_column($zona['facturas'], 'id'));
    }

    public function test_cierre_persiste_estado_anterior_y_no_modifica_entregas_ni_ventas(): void
    {
        foreach (range(1, 4) as $id) {
            $this->factura($id, '2026-05-18');
        }
        $this->factura(5, '2026-05-19');
        $this->factura(6, '2026-05-18', 2);
        DB::table('factura_tratamiento_entrega')->insert([
            'factura_id' => 2, 'direccion_entrega' => 'Direccion conservada',
        ]);
        DB::table('distribuciones_entrega_facturas')->insert([
            ['id' => 1, 'factura_id' => 3, 'distribucion_entrega_id' => 1, 'estado_entrega' => 'parcial'],
            ['id' => 2, 'factura_id' => 4, 'distribucion_entrega_id' => 1, 'estado_entrega' => 'entregado'],
        ]);
        $facturasAntes = DB::table('factura')->get()->toArray();
        $entregasAntes = DB::table('distribuciones_entrega_facturas')->get()->toArray();

        $migracion = $this->migracion();
        $migracion->up();
        $migracion->up();

        $historial = DB::table('factura_tratamiento_entrega_historial')->orderBy('factura_id')->get();
        $this->assertCount(2, $historial);
        foreach (['Pendiente', 'Tratada'] as $indice => $estado) {
            $this->assertSame('completada', $historial[$indice]->estado);
            $this->assertSame("Cambio de estado de {$estado} a Completada, solicitado en GLPI-330", $historial[$indice]->observaciones);
        }
        $this->assertSame('Direccion conservada', $historial[1]->direccion_entrega);
        $this->assertSame([1, 2], $historial->pluck('factura_id')->all());
        $this->assertEquals($facturasAntes, DB::table('factura')->get()->toArray());
        $this->assertEquals($entregasAntes, DB::table('distribuciones_entrega_facturas')->get()->toArray());
    }

    public function test_completadas_y_contadores_incluyen_cierre_sin_entrega_fisica(): void
    {
        $this->factura(1, '2026-05-18');
        $this->factura(2, '2026-05-19');
        $this->factura(3, '2026-10-14');
        $this->factura(4, '2026-05-18');
        DB::table('distribuciones_entrega_facturas')->insert([
            'id' => 1, 'factura_id' => 4, 'distribucion_entrega_id' => 1, 'estado_entrega' => 'parcial',
        ]);
        $this->migracion()->up();
        $componente = new GestionDeFacturas();
        $resumen = $componente->resumenEstados(new Request())->getData(true);
        $this->assertTrue($resumen['success']);
        $this->assertSame(1, $resumen['completadas']);
        $this->assertSame(1, $resumen['sin_tratar']);
        $this->assertSame(1, $resumen['asignadas']);

        $listado = $componente->listarCompletadas(new Request())->getData(true);
        $this->assertCount(1, $listado['data']);
        $this->assertSame(1, $listado['data'][0]['id']);
        $this->assertNull($listado['data'][0]['fecha_entrega_real']);
        $this->assertNull($listado['data'][0]['nombre_equipo']);
        $this->assertStringContainsString('GLPI-330', $listado['data'][0]['comentario_cierre']);

        $historial = $componente->historialFactura(1)->getData(true);
        $this->assertTrue($historial['success']);
        $this->assertSame('completada', $historial['historial'][0]['estado']);
        $this->assertStringContainsString('Pendiente', $historial['historial'][0]['observaciones']);
    }

    public function test_busqueda_por_numero_no_rechaza_factura_antigua_dentro_del_rango(): void
    {
        $this->factura(1, '2026-05-18');
        $this->factura(2, '2026-05-19');
        $this->factura(3, '2026-10-14');
        $componente = new DistribucionEntrega();
        $this->assertSame(422, $componente->obtenerFacturaPorNumero(new Request(['numero' => 'FAC-1']))->getStatusCode());
        $this->assertSame(200, $componente->obtenerFacturaPorNumero(new Request(['numero' => 'FAC-2']))->getStatusCode());
        $this->assertNotSame(200, $componente->obtenerFacturaPorNumero(new Request(['numero' => 'FAC-3']))->getStatusCode());
    }

    public function test_guardado_rechaza_facturas_anteriores_y_futuras(): void
    {
        $this->factura(1, '2026-05-18');
        $this->factura(2, '2026-10-14');
        foreach ([1, 2] as $id) {
            $request = new Request([
                'equipo_entrega_id' => 1, 'fecha_programada' => '2026-10-13',
                'facturas' => [$id], 'personal' => [['user_id' => 1, 'porcentaje' => 100]],
            ]);
            $respuesta = (new DistribucionEntrega())->guardarDistribucion($request);
            $this->assertSame(422, $respuesta->getStatusCode());
            $this->assertSame('Facturas fuera de rango', $respuesta->getData(true)['title']);
        }
    }

    public function test_entregadas_siguen_en_completadas_sin_agregar_cierre_administrativo(): void
    {
        $this->factura(1, '2026-05-18');
        DB::table('distribuciones_entrega_facturas')->insert([
            'id' => 1, 'factura_id' => 1, 'distribucion_entrega_id' => 1,
            'estado_entrega' => 'entregado', 'fecha_entrega_real' => '2026-05-18 10:00:00',
        ]);
        $this->migracion()->up();
        $this->assertSame(0, DB::table('factura_tratamiento_entrega_historial')->count());
        $componente = new GestionDeFacturas();
        $listado = $componente->listarCompletadas(new Request())->getData(true);
        $this->assertCount(1, $listado['data']);
        $this->assertSame('Equipo', $listado['data'][0]['nombre_equipo']);
        $this->assertSame('2026-05-18 10:00:00', $listado['data'][0]['fecha_entrega_real']);
        $this->assertNull($listado['data'][0]['comentario_cierre']);
        $this->assertSame(1, $componente->resumenEstados(new Request())->getData(true)['completadas']);
    }

    public function test_rollback_conserva_historial_preexistente(): void
    {
        $this->factura(1, '2026-05-18');
        DB::table('factura_tratamiento_entrega_historial')->insert([
            'factura_id' => 1, 'estado' => 'tratada', 'observaciones' => 'Tratamiento original',
        ]);
        $migracion = $this->migracion();
        $migracion->up();
        $migracion->down();
        $this->assertSame(1, DB::table('factura_tratamiento_entrega_historial')->count());
        $this->assertSame('Tratamiento original', DB::table('factura_tratamiento_entrega_historial')->value('observaciones'));
    }
}
