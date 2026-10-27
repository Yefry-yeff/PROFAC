<?php

namespace Tests\Feature;

use App\Livewire\Inventario\CompraProducto;
use App\Livewire\Inventario\Gastos;
use App\Livewire\Inventario\ListarCompras;
use App\Exports\ComprasMesExport;
use App\Exports\GastosExport;
use App\Livewire\Proveedores;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GastosTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.gastos_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $mysql = getenv('GASTOS_TEST_MYSQL_DATABASE');
        if ($mysql !== false && $mysql !== '') {
            if (!preg_match('/^profac_gastos_[a-f0-9]+_testing$/D', $mysql)) {
                throw new \RuntimeException('Nombre de base aislada de pruebas invalido.');
            }
            $connection = $app['config']->get('database.connections.mysql');
            $connection['database'] = $mysql;
            $app['config']->set('database.connections.gastos_testing', $connection);
        }
        $app['config']->set('database.default', 'gastos_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-20 00:00:00'));
        $this->assertSame('gastos_testing', DB::connection()->getName());
        if (DB::getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', fn (...$partes) => implode('', $partes));
            DB::connection()->getPdo()->sqliteCreateFunction('IF', fn ($condicion, $si, $no) => $condicion ? $si : $no);
        } else {
            Schema::dropAllTables();
        }
        (require database_path('migrations\2026_10_18_000001_create_gastos_tables.php'))->up();
        (require database_path('migrations\2026_10_18_000002_add_unidad_descuento_to_gasto_detalles.php'))->up();
        (require database_path('migrations\2026_10_18_000003_add_incluir_reporte_compras_to_gastos.php'))->up();
        (require database_path('migrations\2026_10_20_000001_create_gasto_auditoria_table.php'))->up();
        Schema::create('unidad_medida', function (Blueprint $t) {
            $t->increments('id'); $t->string('nombre'); $t->string('simbolo');
        });
        DB::table('unidad_medida')->insert([
            ['id' => 1, 'nombre' => 'UNIDAD', 'simbolo' => 'UN'],
            ['id' => 2, 'nombre' => 'RESMA', 'simbolo' => 'RM'],
        ]);
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name');
        });
        Schema::create('proveedores', function (Blueprint $t) {
            $t->id();
            foreach (['codigo', 'nombre', 'direccion', 'contacto', 'telefono_1', 'telefono_2', 'correo_1', 'correo_2', 'rtn'] as $campo) {
                $t->string($campo)->nullable();
            }
            foreach (['registrado_por', 'estado_id', 'municipio_id', 'tipo_personalidad_id', 'categoria_id'] as $campo) {
                $t->integer($campo)->nullable();
            }
            $t->timestamps();
        });
        foreach (['tipo_compra', 'municipio', 'tipo_personalidad', 'categoria', 'retenciones'] as $tabla) {
            Schema::create($tabla, function (Blueprint $t) {
                $t->id(); $t->string('nombre')->nullable();
            });
            DB::table($tabla)->insert(['id' => 1, 'nombre' => 'Catalogo prueba']);
        }
        Schema::create('retenciones_has_proveedores', function (Blueprint $t) {
            $t->integer('proveedores_id'); $t->integer('retenciones_id'); $t->timestamps();
        });
        Schema::create('producto', function (Blueprint $t) {
            $t->id(); $t->string('nombre'); $t->integer('estado_producto_id');
            $t->integer('isv'); $t->integer('unidadad_compra'); $t->integer('unidad_medida_compra_id');
            $t->decimal('ultimo_costo_compra', 15, 2); $t->decimal('costo_promedio', 15, 2); $t->timestamps();
        });
        Schema::create('compra', function (Blueprint $t) {
            $t->id();
            foreach (['numero_factura', 'codigo_cai', 'fecha_vencimiento', 'fecha_emision', 'fecha_recepcion', 'numero_orden'] as $campo) {
                $t->string($campo);
            }
            foreach (['isv_compra', 'sub_total', 'total', 'debito', 'monto_retencion'] as $campo) {
                $t->decimal($campo, 15, 2);
            }
            foreach (['proveedores_id', 'users_id', 'tipo_compra_id', 'estado_compra_id', 'retenciones_id'] as $campo) {
                $t->integer($campo);
            }
            $t->timestamps();
        });
        Schema::create('compra_has_producto', function (Blueprint $t) {
            foreach (['compra_id', 'producto_id', 'cantidad_ingresada', 'cantidad_sin_asignar', 'cantidad_disponible', 'unidades_compra', 'unidad_compra_id'] as $campo) {
                $t->integer($campo);
            }
            foreach (['precio_unidad', 'sub_total_producto', 'isv', 'precio_total'] as $campo) {
                $t->decimal($campo, 15, 2);
            }
            $t->date('fecha_expiracion')->nullable(); $t->timestamps();
        });
        Schema::create('recibido_bodega', function (Blueprint $t) {
            $t->id(); $t->integer('compra_id');
        });
        Schema::create('boleta_compra', function (Blueprint $t) {
            $t->increments('id');
            $t->string('numero_boleta'); $t->string('cliente'); $t->string('direccion')->default('');
            $t->string('rtn_dni')->nullable(); $t->string('telefono')->nullable(); $t->text('comentario')->nullable();
            $t->date('fecha'); $t->decimal('sub_total', 15, 2); $t->decimal('total', 15, 2);
            $t->integer('estado')->default(1); $t->integer('users_id'); $t->integer('cai_boleta_id')->nullable();
            $t->string('editado_por')->nullable(); $t->timestamp('editado_at')->nullable(); $t->timestamps();
        });
        Schema::create('boleta_compra_detalle', function (Blueprint $t) {
            $t->id(); $t->integer('boleta_compra_id'); $t->integer('linea'); $t->string('descripcion');
            $t->decimal('precio', 15, 2); $t->decimal('cantidad', 15, 2); $t->decimal('importe', 15, 2); $t->timestamps();
        });
        Schema::create('cai_boleta_compra', function (Blueprint $t) {
            $t->increments('id'); $t->string('cai');
        });
        (require database_path('migrations\2026_10_20_000002_add_reporte_compras_to_boletas.php'))->up();
        DB::table('users')->insert(['id' => 1, 'name' => 'Usuario prueba']);
        DB::table('proveedores')->insert(['id' => 1, 'nombre' => 'Proveedor prueba', 'rtn' => '08011999000001', 'estado_id' => 1]);
        DB::table('producto')->insert([
            'id' => 1, 'nombre' => 'Producto catalogo', 'estado_producto_id' => 1, 'isv' => 15,
            'unidadad_compra' => 12, 'unidad_medida_compra_id' => 1,
            'ultimo_costo_compra' => 5, 'costo_promedio' => 5,
        ]);
        $user = new User;
        $user->forceFill(['id' => 1, 'name' => 'Usuario prueba']);
        Auth::setUser($user);
    }

    private function datos(array $cambios = []): array
    {
        $datos = array_replace([
            'numero_factura' => 'GASTO-001', 'cai' => '', 'seleccionarProveedorId' => 1,
            'tipoPagoCompra' => 1, 'fecha_emision' => '2026-10-17',
            'fecha_entrega' => '2026-10-17', 'fecha_vencimiento' => '2026-10-30',
            'productos' => [
                ['nombre' => 'Exento', 'cantidad' => 2, 'precio' => '10.00', 'isv_porcentaje' => 0],
                ['nombre' => 'Gravado 15', 'cantidad' => 3, 'precio' => '20.00', 'isv_porcentaje' => 15],
                ['nombre' => 'Gravado 18', 'cantidad' => 1, 'precio' => '100.00', 'isv_porcentaje' => 18],
            ],
        ], $cambios);
        $datos['productos'] = array_map(fn ($producto) => $producto + [
            'unidad_medida_id' => 1, 'descuento_porcentaje' => 0,
        ], $datos['productos']);
        return $datos;
    }

    private function guardar(array $cambios = []): int
    {
        $response = (new Gastos)->guardar(new Request($this->datos($cambios)));
        $this->assertSame(201, $response->getStatusCode());
        return $response->getData(true)['gasto']['id'];
    }

    public function test_calcula_tres_tasas_y_no_modifica_catalogo_ni_compras(): void
    {
        $antes = DB::table('producto')->get()->toJson();
        $id = $this->guardar(['total' => 1, 'isv' => 0]);
        $gasto = DB::table('gastos')->find($id);
        $this->assertEquals(180, $gasto->subtotal);
        $this->assertEquals(27, $gasto->isv);
        $this->assertEquals(207, $gasto->total);
        $this->assertEquals(207, $gasto->saldo);
        $this->assertSame(1, $gasto->usuario_id);
        $this->assertSame(3, DB::table('gasto_detalles')->count());
        $this->assertSame(0, DB::table('compra')->count());
        $this->assertSame(0, DB::table('compra_has_producto')->count());
        $this->assertSame($antes, DB::table('producto')->get()->toJson());
    }

    public function test_redondea_por_linea_y_admite_nombres_repetidos(): void
    {
        $producto = ['nombre' => 'Servicio', 'cantidad' => 3, 'precio' => '0.01', 'isv_porcentaje' => 18];
        $id = $this->guardar(['productos' => [$producto, $producto]]);
        $this->assertEquals(0.06, DB::table('gastos')->find($id)->subtotal);
        $this->assertEquals(0.02, DB::table('gastos')->find($id)->isv);
        $this->assertEquals(0.08, DB::table('gastos')->find($id)->total);
    }

    public function test_guarda_unidad_y_descuento_antes_de_calcular_isv(): void
    {
        $id = $this->guardar(['productos' => [
            ['nombre' => 'Resmas', 'cantidad' => 2, 'precio' => '100.00', 'isv_porcentaje' => 15,
                'unidad_medida_id' => 2, 'descuento_porcentaje' => '10.00'],
            ['nombre' => 'Servicio', 'cantidad' => 1, 'precio' => '50.00', 'isv_porcentaje' => 18,
                'unidad_medida_id' => 1, 'descuento_porcentaje' => '5.50'],
        ], 'subtotal' => 1, 'total' => 1]);
        $lineas = DB::table('gasto_detalles')->where('gasto_id', $id)->orderBy('id')->get();
        $this->assertSame(2, $lineas[0]->unidad_medida_id);
        $this->assertEquals(10, $lineas[0]->descuento_porcentaje);
        $this->assertEquals(20, $lineas[0]->descuento_monto);
        $this->assertEquals(180, $lineas[0]->subtotal);
        $this->assertEquals(27, $lineas[0]->isv);
        $this->assertEquals(207, $lineas[0]->total);
        $this->assertEquals(2.75, $lineas[1]->descuento_monto);
        $this->assertEquals(47.25, $lineas[1]->subtotal);
        $this->assertEquals(8.51, $lineas[1]->isv);
        $gasto = DB::table('gastos')->find($id);
        $this->assertEquals(227.25, $gasto->subtotal);
        $this->assertEquals(35.51, $gasto->isv);
        $this->assertEquals(262.76, $gasto->total);
        $this->assertEquals(262.76, $gasto->saldo);
        $this->assertSame(0, DB::table('compra')->count());
        $this->assertEquals(5, DB::table('producto')->find(1)->ultimo_costo_compra);
    }

    public function test_descuento_100_y_redondeo_sobre_centavos(): void
    {
        $id = $this->guardar(['productos' => [
            ['nombre' => 'Descuento maximo', 'cantidad' => 1, 'precio' => '100.00', 'isv_porcentaje' => 18, 'descuento_porcentaje' => 100],
            ['nombre' => 'Redondeo', 'cantidad' => 3, 'precio' => '0.01', 'isv_porcentaje' => 15, 'descuento_porcentaje' => 50],
        ]]);
        $lineas = DB::table('gasto_detalles')->where('gasto_id', $id)->orderBy('id')->get();
        $this->assertEquals(100, $lineas[0]->descuento_monto);
        $this->assertEquals(0, $lineas[0]->subtotal);
        $this->assertEquals(0, $lineas[0]->isv);
        $this->assertEquals(0, $lineas[0]->total);
        $this->assertEquals(0.02, $lineas[1]->descuento_monto);
        $this->assertEquals(0.01, $lineas[1]->subtotal);
        $this->assertEquals(0.01, DB::table('gastos')->find($id)->total);
    }

    public function test_detalles_anteriores_conservan_totales_y_no_inventan_unidad(): void
    {
        $id = $this->guardar();
        DB::table('gasto_detalles')->where('gasto_id', $id)->update(['unidad_medida_id' => null]);
        $this->assertEquals(207, DB::table('gastos')->find($id)->total);
    }

    public function test_admite_2000_lineas_y_rechaza_2001(): void
    {
        $producto = ['nombre' => 'Linea de gasto', 'cantidad' => 1, 'precio' => '1.00', 'isv_porcentaje' => 15];
        $id = $this->guardar(['productos' => array_fill(0, 2000, $producto)]);
        $this->assertSame(2000, DB::table('gasto_detalles')->where('gasto_id', $id)->count());
        $this->assertEquals(2300, DB::table('gastos')->find($id)->total);
        try {
            $this->guardar(['productos' => array_fill(0, 2001, $producto)]);
            $this->fail('Se aceptaron mas de 2000 lineas.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('productos', $exception->errors());
        }
        $this->assertSame(1, DB::table('gastos')->count());
    }

    public function test_rechaza_desbordamiento_del_total_sin_guardado_parcial(): void
    {
        try {
            $this->guardar(['productos' => [
                ['nombre' => 'Excesivo', 'cantidad' => 1000000, 'precio' => '999999999.99', 'isv_porcentaje' => 18],
            ]]);
            $this->fail('Se acepto un total fuera del rango decimal.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('productos', $exception->errors());
        }
        $this->assertSame(0, DB::table('gastos')->count());
        $this->assertSame(0, DB::table('gasto_detalles')->count());
    }

    public static function entradasInvalidas(): array
    {
        return [
            'sin lineas' => ['productos', [], 'productos'],
            'nombre vacio' => ['productos.0.nombre', '   ', 'productos.0.nombre'],
            'precio negativo' => ['productos.0.precio', -1, 'productos.0.precio'],
            'precision excesiva' => ['productos.0.precio', '1.001', 'productos.0.precio'],
            'cantidad cero' => ['productos.0.cantidad', 0, 'productos.0.cantidad'],
            'cantidad fraccionaria' => ['productos.0.cantidad', 1.5, 'productos.0.cantidad'],
            'tasa inventada' => ['productos.0.isv_porcentaje', 12, 'productos.0.isv_porcentaje'],
            'unidad inexistente' => ['productos.0.unidad_medida_id', 999, 'productos.0.unidad_medida_id'],
            'sin unidad' => ['productos.0.unidad_medida_id', null, 'productos.0.unidad_medida_id'],
            'descuento negativo' => ['productos.0.descuento_porcentaje', -1, 'productos.0.descuento_porcentaje'],
            'descuento enorme' => ['productos.0.descuento_porcentaje', '1000000000000', 'productos.0.descuento_porcentaje'],
            'descuento mayor a cien' => ['productos.0.descuento_porcentaje', '100.01', 'productos.0.descuento_porcentaje'],
            'precision descuento' => ['productos.0.descuento_porcentaje', '10.001', 'productos.0.descuento_porcentaje'],
            'descuento vacio' => ['productos.0.descuento_porcentaje', '', 'productos.0.descuento_porcentaje'],
            'proveedor inexistente' => ['seleccionarProveedorId', 999, 'seleccionarProveedorId'],
            'pago inexistente' => ['tipoPagoCompra', 999, 'tipoPagoCompra'],
            'fecha invalida' => ['fecha_vencimiento', '2026-01-01', 'fecha_vencimiento'],
            'clasificacion invalida' => ['incluir_reporte_compras', 'compras', 'incluir_reporte_compras'],
        ];
    }

    #[DataProvider('entradasInvalidas')]
    public function test_rechaza_datos_invalidos_sin_escrituras(string $campo, mixed $valor, string $error): void
    {
        $datos = $this->datos();
        data_set($datos, $campo, $valor);
        try {
            (new Gastos)->guardar(new Request($datos));
            $this->fail('Se aceptaron datos invalidos.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($error, $exception->errors());
        }
        $this->assertSame(0, DB::table('gastos')->count());
        $this->assertSame(0, DB::table('gasto_detalles')->count());
    }

    public function test_bloquea_proveedor_inactivo_y_total_cero(): void
    {
        DB::table('proveedores')->where('id', 1)->update(['estado_id' => 2]);
        try {
            $this->guardar();
            $this->fail('Proveedor inactivo aceptado.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('seleccionarProveedorId', $exception->errors());
        }
        DB::table('proveedores')->where('id', 1)->update(['estado_id' => 1]);
        $this->expectException(ValidationException::class);
        $this->guardar(['productos' => [['nombre' => 'Cero', 'cantidad' => 1, 'precio' => '0', 'isv_porcentaje' => 0]]]);
    }

    public function test_listado_sin_impresion_detalle_abonos_ni_saldos(): void
    {
        $this->guardar();
        Livewire::test(Gastos::class)->assertSee('GASTO-001')->assertDontSee('Imprimir gasto')
            ->assertDontSee('Documento')
            ->assertDontSee('Detalle y pagos')->assertDontSee('Historial de pagos')
            ->assertDontSee('Saldo')->assertDontSee('Registrar abono')->assertSee('Registrado')
            ->set('filtros.factura', 'Inexistente')->call('aplicarFiltros')->assertSee('No hay gastos registrados.');
        $this->assertFalse(method_exists(Gastos::class, 'registrarPago'));
        $this->assertFalse(method_exists(Gastos::class, 'ver'));
        $this->assertFalse(method_exists(Gastos::class, 'anular'));
        $this->assertSame(0, DB::table('gasto_pagos')->count());
        Auth::logout();
        $this->get('/gastos')->assertRedirect();
        $this->get('/gastos/imprimir/1')->assertNotFound();
    }

    public function test_gastos_permanecen_visibles_y_excel_compras_filtra_mes_anio_y_estado(): void
    {
        $normal = $this->guardar(['numero_factura' => 'SOLO-GASTOS']);
        $compra = $this->guardar(['numero_factura' => 'GASTO-EN-COMPRAS', 'incluir_reporte_compras' => true]);
        $this->guardar(['numero_factura' => 'OTRO-ANIO', 'incluir_reporte_compras' => true,
            'fecha_emision' => '2025-10-17', 'fecha_vencimiento' => '2025-10-30']);
        $this->guardar(['numero_factura' => 'OTRO-MES', 'incluir_reporte_compras' => true,
            'fecha_emision' => '2026-09-17', 'fecha_vencimiento' => '2026-09-30']);
        $anulado = $this->guardar(['numero_factura' => 'ANULADO', 'incluir_reporte_compras' => true]);
        DB::table('gastos')->where('id', $anulado)->update(['estado' => 'anulado']);
        $this->assertEquals(0, DB::table('gastos')->find($normal)->incluir_reporte_compras);
        $this->assertEquals(1, DB::table('gastos')->find($compra)->incluir_reporte_compras);
        Livewire::test(Gastos::class)->assertSee('SOLO-GASTOS')->assertSee('GASTO-EN-COMPRAS')
            ->assertDontSee('OTRO-ANIO')->assertDontSee('OTRO-MES')
            ->assertSee('ANULADO')->assertSee('Pasado al reporte de compras');

        $this->crearCompraDePrueba();
        $response = (new ListarCompras)->listarCompras();
        $this->assertSame(200, $response->getStatusCode());
        $rows = collect($response->getData(true)['data']);
        $this->assertFalse($rows->contains('numero_factura', 'SOLO-GASTOS'));
        $this->assertFalse($rows->contains('numero_factura', 'ANULADO'));
        $row = $rows->firstWhere('numero_factura', 'GASTO-EN-COMPRAS');
        $this->assertSame('G-' . $compra, $row['id']);
        $this->assertStringContainsString('No aplica (gasto)', $row['opciones']);
        $this->assertStringNotContainsString('Imprimir gasto', $row['opciones']);
        $this->assertStringNotContainsString('/gastos/imprimir/', $row['opciones']);
        $this->assertStringNotContainsString('/recibir/', $row['opciones']);
        $this->assertStringNotContainsString('/pagos/', $row['opciones']);
        $this->assertStringNotContainsString('anularCompra(', $row['anular']);
        $this->assertTrue($rows->contains('tipo_documento', 'compra'));

        $export = new ComprasMesExport('2026-10');
        $datos = $export->collection();
        $this->assertCount(2, $datos);
        $gastoExportado = $datos->last();
        $this->assertSame('GASTO-EN-COMPRAS', $gastoExportado['numero_factura']);
        $this->assertEquals(20, $gastoExportado['importe_exento']);
        $this->assertEquals(60, $gastoExportado['base_15']);
        $this->assertSame('FA-factura', $gastoExportado['clase_documento']);
        $this->assertSame('FA-factura', $datos->first()['clase_documento']);
        $this->assertCount(count($export->headings()), $gastoExportado);
        $xlsx = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertStringStartsWith('PK', $xlsx);
    }

    public function test_seleccionar_todos_incluye_todas_las_paginas_y_respeta_busqueda_y_estado(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->guardar(['numero_factura' => 'LOTE-' . $i, 'incluir_reporte_compras' => $i === 1]);
        }
        $otro = $this->guardar(['numero_factura' => 'OTRO']);
        $anulado = $this->guardar(['numero_factura' => 'LOTE-ANULADO']);
        DB::table('gastos')->where('id', $anulado)->update(['estado' => 'anulado']);

        Livewire::test(Gastos::class)->set('filtros.factura', 'LOTE-')->call('aplicarFiltros')
            ->set('seleccionarTodos', true)
            ->assertSet('seleccionados', array_map('strval', range(1, 25)))
            ->call('setPage', 2)->assertSet('seleccionarTodos', true)
            ->set('seleccionados', ['1'])->assertSet('seleccionarTodos', false)
            ->set('seleccionarTodos', true)->set('seleccionarTodos', false)
            ->assertSet('seleccionados', [])
            ->set('seleccionarTodos', true)->call('pasarACompras')->assertHasNoErrors()
            ->assertSet('seleccionados', [])->assertSet('seleccionarTodos', false);

        $this->assertSame(25, DB::table('gastos')->where('incluir_reporte_compras', true)->count());
        $this->assertEquals(0, DB::table('gastos')->find($otro)->incluir_reporte_compras);
        $this->assertEquals(0, DB::table('gastos')->find($anulado)->incluir_reporte_compras);
        Livewire::test(Gastos::class)->set('filtros.factura', 'LOTE-')->call('aplicarFiltros')->set('seleccionarTodos', true)
            ->set('filtros.factura', 'OTRO')->call('aplicarFiltros')->assertSet('seleccionados', [])->assertSet('seleccionarTodos', false)
            ->set('filtros.factura', 'INEXISTENTE')->call('aplicarFiltros')->set('seleccionarTodos', true)
            ->assertSet('seleccionados', [])->assertSet('seleccionarTodos', false);
    }

    public function test_anula_varios_y_guarda_usuario_hora_y_motivo_sin_modificar_importes(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-19 23:58:12'));
        $primero = $this->guardar();
        $segundo = $this->guardar(['numero_factura' => 'SEGUNDO']);
        $otro = $this->guardar(['numero_factura' => 'SIN-CAMBIOS']);

        Livewire::test(Gastos::class)->set('seleccionados', [(string) $primero, (string) $segundo])
            ->set('motivoAnulacion', 'Factura duplicada')->call('anularSeleccionados')
            ->assertHasNoErrors()->assertDispatched('gastos-accion-completada')->assertSet('seleccionados', [])
            ->assertSee('Usuario prueba')->assertSee('19/10/2026 23:58:12')->assertSee('Factura duplicada');

        foreach ([$primero, $segundo] as $id) {
            $gasto = DB::table('gastos')->find($id);
            $this->assertSame('anulado', $gasto->estado);
            $this->assertSame(1, $gasto->anulado_por);
            $this->assertSame('2026-10-19 23:58:12', $gasto->anulado_at);
            $this->assertSame('Factura duplicada', $gasto->motivo_anulacion);
            $this->assertEquals(207, $gasto->total);
        }
        $this->assertSame('activo', DB::table('gastos')->find($otro)->estado);
        $this->assertCount(0, (new ComprasMesExport('2026-10'))->collection());
        $this->assertSame(0, DB::table('compra')->count());
        $this->assertSame(0, DB::table('gasto_pagos')->count());
        $this->assertSame(9, DB::table('gasto_detalles')->count());
        $this->travelBack();
    }

    public function test_acciones_rechazan_selecciones_invalidas_sin_cambios_parciales(): void
    {
        $activo = $this->guardar();
        $anulado = $this->guardar(['numero_factura' => 'ANULADO']);
        DB::table('gastos')->where('id', $anulado)->update([
            'estado' => 'anulado', 'anulado_por' => 1, 'anulado_at' => '2026-10-18 10:00:00',
        ]);

        foreach (['anularSeleccionados', 'pasarACompras', 'quitarDeCompras'] as $accion) {
            Livewire::test(Gastos::class)->call($accion)->assertHasErrors('seleccionados');
            Livewire::test(Gastos::class)->set('motivoAnulacion', 'Registro incorrecto')->set('seleccionados', [(string) $activo, '99999'])
                ->call($accion)->assertHasErrors('seleccionados');
            Livewire::test(Gastos::class)->set('motivoAnulacion', 'Registro incorrecto')->set('seleccionados', [(string) $activo, (string) $anulado])
                ->call($accion)->assertHasErrors('seleccionados');
            Livewire::test(Gastos::class)->set('seleccionados', ['1', '1'])
                ->call($accion)->assertHasErrors('seleccionados.0');
        }
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $activo])
            ->set('motivoAnulacion', str_repeat('x', 1001))->call('anularSeleccionados')
            ->assertHasErrors('motivoAnulacion')->assertNotDispatched('gastos-accion-completada');

        $this->assertSame('activo', DB::table('gastos')->find($activo)->estado);
        $this->assertEquals(0, DB::table('gastos')->find($activo)->incluir_reporte_compras);
        $this->assertSame('2026-10-18 10:00:00', DB::table('gastos')->find($anulado)->anulado_at);
    }

    public function test_traslado_individual_no_duplica_y_gasto_sigue_visible(): void
    {
        $id = $this->guardar();
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->call('pasarACompras')->assertHasNoErrors()->assertDispatched('gastos-accion-completada')->assertSee('GASTO-001')
            ->assertSee('Pasado al reporte de compras')
            ->set('seleccionados', [(string) $id])->call('pasarACompras')
            ->assertHasErrors('seleccionados');
        $this->assertSame(1, DB::table('gastos')->count());
        $this->assertSame(0, DB::table('compra')->count());
        $this->assertCount(1, (new ComprasMesExport('2026-10'))->collection());
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->set('motivoAnulacion', 'Registro incorrecto')
            ->call('anularSeleccionados')->assertHasErrors('seleccionados');
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->call('quitarDeCompras')->assertHasNoErrors()
            ->set('seleccionados', [(string) $id])->set('motivoAnulacion', 'Registro incorrecto')
            ->call('anularSeleccionados')->assertHasNoErrors()->assertSee('Anulado');
        $this->assertCount(0, (new ComprasMesExport('2026-10'))->collection());
    }

    public function test_anulacion_exige_motivo_no_vacio_y_conserva_el_modal_si_falta(): void
    {
        $id = $this->guardar();
        foreach (['', '   ', "\t\n"] as $motivo) {
            Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
                ->set('motivoAnulacion', $motivo)->call('anularSeleccionados')
                ->assertHasErrors(['motivoAnulacion' => 'required'])
                ->assertNotDispatched('gastos-accion-completada');
            $gasto = DB::table('gastos')->find($id);
            $this->assertSame('activo', $gasto->estado);
            $this->assertNull($gasto->anulado_por);
            $this->assertNull($gasto->anulado_at);
            $this->assertNull($gasto->motivo_anulacion);
        }
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->set('motivoAnulacion', '  Documento duplicado  ')->call('anularSeleccionados')
            ->assertHasNoErrors()->assertDispatched('gastos-accion-completada');
        $this->assertSame('Documento duplicado', DB::table('gastos')->find($id)->motivo_anulacion);
    }

    public function test_ciclo_de_compras_y_anulacion_con_auditoria_inmutable_y_bloqueos_atomicos(): void
    {
        $id = $this->guardar();
        $incluido = $this->guardar(['numero_factura' => 'INCLUIDO', 'incluir_reporte_compras' => true]);
        $this->assertSame(2, DB::table('gasto_auditoria')->where('accion', 'registrar')->count());
        $inicial = DB::table('gasto_auditoria')->where('gasto_id', $incluido)->first();
        $this->assertTrue(json_decode($inicial->despues, true)['incluir_reporte_compras']);

        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id, (string) $incluido])
            ->set('motivoAnulacion', 'Intento bloqueado')->call('anularSeleccionados')
            ->assertHasErrors('seleccionados')->assertNotDispatched('gastos-accion-completada');
        $this->assertSame(2, DB::table('gasto_auditoria')->count());
        $this->assertSame('activo', DB::table('gastos')->find($id)->estado);

        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->call('pasarACompras')->assertHasNoErrors()
            ->set('seleccionados', [(string) $id])->call('pasarACompras')->assertHasErrors('seleccionados');
        $this->assertSame(3, DB::table('gasto_auditoria')->count());
        DB::table('users')->insert(['id' => 2, 'name' => 'Segundo usuario']);
        $user = new User;
        $user->forceFill(['id' => 2, 'name' => 'Segundo usuario']);
        Auth::setUser($user);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-20 02:00:00'));
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->call('quitarDeCompras')->assertHasNoErrors()->assertSee('Retiro de compras')
            ->assertSee('Segundo usuario')->assertSee('20/10/2026 02:00:00')
            ->set('seleccionados', [(string) $id])->call('quitarDeCompras')->assertHasErrors('seleccionados');
        $this->assertEquals(0, DB::table('gastos')->find($id)->incluir_reporte_compras);
        $this->assertFalse((new ComprasMesExport('2026-10'))->collection()->contains('numero_factura', DB::table('gastos')->find($id)->numero_factura));
        $evento = DB::table('gasto_auditoria')->where('gasto_id', $id)->where('accion', 'quitar_compras')->first();
        $this->assertSame(2, $evento->usuario_id);
        $this->assertTrue(json_decode($evento->antes, true)['incluir_reporte_compras']);
        $this->assertFalse(json_decode($evento->despues, true)['incluir_reporte_compras']);
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->set('motivoAnulacion', 'Documento duplicado')->call('anularSeleccionados')->assertHasNoErrors();
        $antes = DB::table('gasto_auditoria')->orderBy('id')->get()->toJson();
        foreach (['pasarACompras', 'quitarDeCompras', 'anularSeleccionados'] as $accion) {
            Livewire::test(Gastos::class)->set('seleccionados', [(string) $id, (string) $incluido])
                ->set('motivoAnulacion', 'Intento bloqueado')->call($accion)->assertHasErrors('seleccionados');
        }
        $this->assertSame($antes, DB::table('gasto_auditoria')->orderBy('id')->get()->toJson());
        $this->assertEquals(1, DB::table('gastos')->find($incluido)->incluir_reporte_compras);
        $this->assertSame(['registrar', 'incluir_compras', 'quitar_compras', 'anular'],
            DB::table('gasto_auditoria')->where('gasto_id', $id)->orderBy('id')->pluck('accion')->all());
    }

    public function test_acciones_y_exportacion_requieren_usuario_autenticado(): void
    {
        $id = $this->guardar();
        Auth::logout();
        foreach (['anularSeleccionados', 'pasarACompras', 'quitarDeCompras', 'exportarExcel'] as $accion) {
            Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
                ->call($accion)->assertForbidden();
        }
        $this->assertSame('activo', DB::table('gastos')->find($id)->estado);
        $this->assertEquals(0, DB::table('gastos')->find($id)->incluir_reporte_compras);
    }

    public function test_seleccion_mixta_quita_solo_incluidos_y_no_duplica_auditoria(): void
    {
        $normal = $this->guardar();
        $incluido = $this->guardar(['numero_factura' => 'EN-COMPRAS', 'incluir_reporte_compras' => true]);
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $normal, (string) $incluido])
            ->call('quitarDeCompras')->assertHasNoErrors()->assertDispatched('gastos-accion-completada');
        $this->assertSame(1, DB::table('gasto_auditoria')->where('accion', 'quitar_compras')->count());
        $this->assertSame($incluido, DB::table('gasto_auditoria')->where('accion', 'quitar_compras')->value('gasto_id'));
        $this->assertSame(0, DB::table('gastos')->where('incluir_reporte_compras', true)->count());
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $normal, (string) $incluido])
            ->call('pasarACompras')->assertHasNoErrors();
        $this->assertSame(2, DB::table('gasto_auditoria')->where('accion', 'incluir_compras')->count());
        $this->assertSame(0, DB::table('compra')->count());
        $this->assertCount(2, (new ComprasMesExport('2026-10'))->collection());
    }

    public function test_filtros_compras_se_aplican_a_compras_gastos_y_excel_con_limites_inclusivos(): void
    {
        $this->crearCompraDePrueba();
        DB::table('compra')->where('id', 1)->update(['numero_factura' => 'MATCH-COMPRA', 'fecha_emision' => '2026-09-30']);
        DB::table('proveedores')->insert(['id' => 2, 'nombre' => 'Otro proveedor', 'estado_id' => 1]);
        $this->guardar(['numero_factura' => 'MATCH-GASTO', 'incluir_reporte_compras' => true]);
        $this->guardar(['numero_factura' => 'MATCH-OTRO', 'incluir_reporte_compras' => true, 'seleccionarProveedorId' => 2]);
        $this->guardar(['numero_factura' => 'NO-COINCIDE', 'incluir_reporte_compras' => true]);
        $this->guardar(['numero_factura' => 'MATCH-FUERA', 'incluir_reporte_compras' => true,
            'fecha_emision' => '2026-10-18']);
        $this->guardar(['numero_factura' => 'MATCH-SOLO-GASTOS']);
        $anulado = $this->guardar(['numero_factura' => 'MATCH-ANULADO', 'incluir_reporte_compras' => true]);
        DB::table('gastos')->where('id', $anulado)->update(['estado' => 'anulado']);
        $filtros = ['desde' => '2026-09-30', 'hasta' => '2026-10-17', 'factura' => 'MATCH-', 'proveedor' => '1'];
        $response = (new ListarCompras)->listarCompras(new Request($filtros));
        $this->assertSame(200, $response->getStatusCode());
        $facturas = collect($response->getData(true)['data'])->pluck('numero_factura')->sort()->values()->all();
        $this->assertSame(['MATCH-COMPRA', 'MATCH-GASTO'], $facturas);
        $export = new ComprasMesExport($filtros);
        $this->assertSame($facturas, $export->collection()->pluck('numero_factura')->sort()->values()->all());
        $actual = (new ListarCompras)->listarCompras(new Request());
        $this->assertNotContains('MATCH-COMPRA', collect($actual->getData(true)['data'])->pluck('numero_factura')->all());
        DB::table('compra')->where('id', 1)->update(['estado_compra_id' => 2]);
        $this->assertSame(['MATCH-GASTO'], (new ComprasMesExport($filtros))->collection()->pluck('numero_factura')->all());
    }

    public function test_reporte_compras_rechaza_filtros_invalidos(): void
    {
        foreach ([
            ['desde' => 'no-fecha'], ['hasta' => '2026-09-01'], ['proveedor' => '99999'],
            ['factura' => str_repeat('x', 256)],
        ] as $filtros) {
            try {
                (new ListarCompras)->listarCompras(new Request($filtros));
                $this->fail('Se aceptaron filtros invalidos.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_excel_compras_formato_fiscal_importes_por_producto_y_ceros_iniciales(): void
    {
        $this->crearCompraDePrueba();
        $detalle = (array) DB::table('compra_has_producto')->first();
        foreach ([2 => 0, 3 => 18] as $id => $tasa) {
            $producto = (array) DB::table('producto')->find(1);
            $producto['id'] = $id;
            $producto['isv'] = $tasa;
            DB::table('producto')->insert($producto);
            DB::table('compra_has_producto')->insert(array_replace($detalle, [
                'producto_id' => $id, 'sub_total_producto' => $id === 2 ? 40 : 100,
                'isv' => $id === 2 ? 0 : 18, 'precio_total' => $id === 2 ? 40 : 118,
            ]));
        }
        DB::table('compra')->where('id', 1)->update([
            'numero_factura' => '001-002-01-00000001', 'codigo_cai' => '000123',
            'sub_total' => 999, 'total' => 999,
        ]);
        $this->guardar(['numero_factura' => 'G-FISCAL', 'cai' => '000456', 'incluir_reporte_compras' => true,
            'productos' => [
                ['nombre' => 'Exento', 'cantidad' => 2, 'precio' => '10.00', 'descuento_porcentaje' => 10, 'isv_porcentaje' => 0],
                ['nombre' => 'Base 15', 'cantidad' => 3, 'precio' => '20.00', 'descuento_porcentaje' => 25, 'isv_porcentaje' => 15],
                ['nombre' => 'Base 18 excluida', 'cantidad' => 2, 'precio' => '100.00', 'isv_porcentaje' => 18],
            ]]);
        $this->guardar(['numero_factura' => 'SOLO-18', 'incluir_reporte_compras' => true,
            'productos' => [
                ['nombre' => 'Solo 18', 'cantidad' => 1, 'precio' => '100.00', 'isv_porcentaje' => 18],
            ]]);
        $export = new ComprasMesExport('2026-10');
        $this->assertSame([
            '200-RTN', '600-Clase de documento', '7-CAI',
            '8-Nº documento (establecimiento - punto de emisión - tipo documento - correlativo)',
            '71-Nº Nombre del proveedor', '900-Fecha emisión', '100-Fecha contable',
            '110-Importe exento', '1511-Importe base 15%', '270-Monto al costo', '280-Monto al gasto',
        ], $export->headings());
        [$compra, $gasto, $solo18] = $export->collection()->all();
        $this->assertCount(11, $compra);
        $this->assertCount(11, $gasto);
        $this->assertSame('08011999000001', $compra['rtn']);
        $this->assertSame('Proveedor prueba', $compra['proveedor']);
        $this->assertSame('FA-factura', $compra['clase_documento']);
        $this->assertSame('FA-factura', $gasto['clase_documento']);
        $this->assertSame('17/10/2026', $compra['fecha_emision']);
        $this->assertSame($compra['fecha_emision'], $compra['fecha_contable']);
        $this->assertSame($gasto['fecha_emision'], $gasto['fecha_contable']);
        $this->assertSame([40.0, 240.0, 280.0, null], array_values(array_slice($compra, 7)));
        $this->assertSame([18.0, 45.0, null, 63.0], array_values(array_slice($gasto, 7)));
        $this->assertSame([0.0, 0.0, null, 0.0], array_values(array_slice($solo18, 7)));
        $xlsx = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $archivo = tempnam(sys_get_temp_dir(), 'compras-fiscal-');
        try {
            file_put_contents($archivo, $xlsx);
            $libro = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
            $hoja = $libro->getActiveSheet();
            $this->assertSame(11, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($hoja->getHighestColumn()));
            $this->assertSame('08011999000001', $hoja->getCell('A6')->getValue());
            $this->assertSame('000123', $hoja->getCell('C6')->getValue());
            $this->assertSame('001-002-01-00000001', $hoja->getCell('D6')->getValue());
            $this->assertSame('FA-factura', $hoja->getCell('B6')->getValue());
            $this->assertSame('FA-factura', $hoja->getCell('B7')->getValue());
            $this->assertSame('s', $hoja->getCell('A6')->getDataType());
            $this->assertSame('n', $hoja->getCell('H6')->getDataType());
            $this->assertEquals(0, $hoja->getCell('H8')->getValue());
            $this->assertEquals(280, $hoja->getCell('J6')->getValue());
            $this->assertNull($hoja->getCell('K6')->getValue());
            $this->assertNull($hoja->getCell('J7')->getValue());
            $this->assertEquals(63, $hoja->getCell('K7')->getValue());
            $this->assertNull($hoja->getCell('J8')->getValue());
            $this->assertSame('n', $hoja->getCell('K8')->getDataType());
            $this->assertEquals(0, $hoja->getCell('K8')->getValue());
            $this->assertSame('#,##0.00', $hoja->getStyle('K7')->getNumberFormat()->getFormatCode());
            $this->assertSame('REPORTE DE COMPRAS', $hoja->getCell('A2')->getValue());
            $this->assertStringContainsString('Usuario prueba', $hoja->getCell('A3')->getValue());
            $this->assertStringContainsString('2026-10-01 al 2026-10-31', $hoja->getCell('A4')->getValue());
            $this->assertSame($export->headings(), $hoja->rangeToArray('A5:K5')[0]);
            $this->assertCount(1, $hoja->getDrawingCollection());
            $this->assertSame('E07000', $hoja->getStyle('A5')->getFill()->getStartColor()->getRGB());
            $this->assertSame('FFF4E8', $hoja->getStyle('A6')->getFill()->getStartColor()->getRGB());
            $this->assertSame('FFFFFF', $hoja->getStyle('A7')->getFill()->getStartColor()->getRGB());
            $this->assertSame('126B5B', $hoja->getStyle('J6')->getFont()->getColor()->getRGB());
            $this->assertSame('126B5B', $hoja->getStyle('K6')->getFont()->getColor()->getRGB());
            $this->assertSame('A5:K8', $hoja->getAutoFilter()->getRange());
            $this->assertSame('E6', $hoja->getFreezePane());
            $this->assertFalse($hoja->getShowGridlines());
            $libro->disconnectWorksheets();
        } finally {
            unlink($archivo);
        }
    }

    public function test_excel_compras_sin_resultados_conserva_formato_y_encabezados(): void
    {
        $export = new ComprasMesExport('2026-10');
        $this->assertCount(0, $export->collection());
        $ruta = tempnam(sys_get_temp_dir(), 'compras-vacio-');
        try {
            file_put_contents($ruta, \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));
            $libro = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
            $hoja = $libro->getActiveSheet();
            $this->assertSame('Compras', $hoja->getTitle());
            $this->assertSame(5, $hoja->getHighestRow());
            $this->assertSame($export->headings(), $hoja->rangeToArray('A5:K5')[0]);
            $this->assertCount(1, $hoja->getDrawingCollection());
            $this->assertSame('E07000', $hoja->getStyle('A5')->getFill()->getStartColor()->getRGB());
            $this->assertSame('A5:K5', $hoja->getAutoFilter()->getRange());
            $libro->disconnectWorksheets();
        } finally {
            unlink($ruta);
        }
    }

    public function test_excel_gastos_incluye_estados_auditoria_y_filtro(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-19 23:58:12'));
        $registrado = $this->guardar(['numero_factura' => 'REPORTE-REGISTRADO']);
        $trasladado = $this->guardar(['numero_factura' => 'REPORTE-COMPRAS', 'incluir_reporte_compras' => true]);
        $anulado = $this->guardar(['numero_factura' => 'REPORTE-ANULADO', 'incluir_reporte_compras' => true]);
        $this->guardar(['numero_factura' => 'FUERA-DE-FILTRO']);
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $anulado])
            ->call('quitarDeCompras')->assertHasNoErrors()
            ->set('seleccionados', [(string) $anulado])
            ->set('motivoAnulacion', 'Error de registro')->call('anularSeleccionados')->assertHasNoErrors();
        $export = new GastosExport([
            'desde' => '2026-10-01', 'hasta' => '2026-10-31', 'factura' => 'REPORTE-', 'proveedor' => '',
        ]);
        $rows = $export->collection()->keyBy('id');
        $this->assertCount(3, $rows);
        $this->assertSame('Registrado', $rows['G-' . $registrado]['estado']);
        $this->assertSame('Pasado al reporte de compras', $rows['G-' . $trasladado]['estado']);
        $this->assertSame('Anulado', $rows['G-' . $anulado]['estado']);
        $this->assertSame('No', $rows['G-' . $anulado]['reporte_compras']);
        $this->assertSame('Usuario prueba', $rows['G-' . $anulado]['anulado_por']);
        $this->assertSame(now()->format('d/m/Y H:i:s'), $rows['G-' . $anulado]['anulado_at']);
        $this->assertSame('Error de registro', $rows['G-' . $anulado]['motivo_anulacion']);
        foreach ($rows as $row) {
            $this->assertCount(count($export->headings()), $row);
        }
        $xlsx = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertStringStartsWith('PK', $xlsx);
        $ruta = tempnam(sys_get_temp_dir(), 'gastos_excel_');
        try {
            file_put_contents($ruta, $xlsx);
            $archivo = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
            $sheet = $archivo->getActiveSheet();
            $hoja = $sheet->toArray();
            $this->assertSame($export->headings(), $hoja[4]);
            $this->assertSame('Anulado', $hoja[5][11]);
            $this->assertSame('Usuario prueba', $hoja[5][13]);
            $this->assertSame('19/10/2026 23:58:12', $hoja[5][14]);
            $this->assertSame('Pasado al reporte de compras', $hoja[6][11]);
            $this->assertSame('REPORTE DE GASTOS', $sheet->getCell('A2')->getValue());
            $this->assertStringContainsString('Usuario prueba', $sheet->getCell('A3')->getValue());
            $this->assertSame('E07000', $sheet->getStyle('A5')->getFill()->getStartColor()->getRGB());
            $this->assertSame('FFFFFF', $sheet->getStyle('A5')->getFont()->getColor()->getRGB());
            $this->assertSame('FFF4E8', $sheet->getStyle('A6')->getFill()->getStartColor()->getRGB());
            $this->assertSame('126B5B', $sheet->getStyle('K6')->getFont()->getColor()->getRGB());
            $this->assertCount(1, $sheet->getDrawingCollection());
            $this->assertSame('Logo Valencia', $sheet->getDrawingCollection()[0]->getName());
            $this->assertSame('A1', $sheet->getDrawingCollection()[0]->getCoordinates());
            $this->assertSame('A5:P8', $sheet->getAutoFilter()->getRange());
            $this->assertSame('E6', $sheet->getFreezePane());
            $this->assertFalse($sheet->getShowGridlines());
            $this->assertSame(20.0, (float) $sheet->getCell('H6')->getValue());
            $this->assertSame(160.0, (float) $sheet->getCell('I6')->getValue());
            $this->assertSame('"L. " #,##0.00', $sheet->getStyle('H6')->getNumberFormat()->getFormatCode());
            $archivo->disconnectWorksheets();
        } finally {
            unlink($ruta);
        }
        Livewire::test(Gastos::class)->set('filtros.factura', 'REPORTE-')->call('aplicarFiltros')->call('exportarExcel')
            ->assertFileDownloaded('Gastos_20261019_235812.xlsx');
        $this->travelBack();
    }

    public function test_excel_gastos_respeta_orden_e_importes_netos_por_tasa_y_preserva_documentos(): void
    {
        $id = $this->guardar([
            'numero_factura' => '00001234', 'cai' => '00005678',
            'productos' => [
                ['nombre' => 'Exento', 'cantidad' => 2, 'precio' => '100.00',
                    'isv_porcentaje' => 0, 'descuento_porcentaje' => 10],
                ['nombre' => 'Gravado 15', 'cantidad' => 3, 'precio' => '20.00',
                    'isv_porcentaje' => 15, 'descuento_porcentaje' => 50],
                ['nombre' => 'Gravado 18', 'cantidad' => 1, 'precio' => '100.00',
                    'isv_porcentaje' => 18, 'descuento_porcentaje' => 0],
                ['nombre' => 'Gravado descontado', 'cantidad' => 1, 'precio' => '100.00',
                    'isv_porcentaje' => 15, 'descuento_porcentaje' => 100],
            ],
        ]);
        $export = new GastosExport([
            'desde' => '2026-10-01', 'hasta' => '2026-10-31', 'factura' => '', 'proveedor' => '',
        ]);
        $this->assertSame([
            'ID', 'Nombre del proveedor', 'CAI', 'Numero de documento', 'Fecha de emision',
            'Fecha de recepcion', 'Fecha de vencimiento', 'Importe exento', 'Importe gravado', 'ISV', 'Total',
        ], array_slice($export->headings(), 0, 11));
        $row = $export->collection()->first();
        $this->assertSame('G-' . $id, $row['id']);
        $this->assertSame('Proveedor prueba', $row['proveedor']);
        $this->assertSame(180.0, $row['importe_exento']);
        $this->assertSame(130.0, $row['importe_gravado']);
        $this->assertSame(22.5, $row['isv']);
        $this->assertSame(332.5, $row['total']);
        $this->assertSame($row['total'], $row['importe_exento'] + $row['importe_gravado'] + $row['isv']);

        $ruta = tempnam(sys_get_temp_dir(), 'gastos_formato_');
        try {
            file_put_contents($ruta, \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));
            $archivo = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
            $sheet = $archivo->getActiveSheet();
            $this->assertSame('00005678', $sheet->getCell('C6')->getValue());
            $this->assertSame('00001234', $sheet->getCell('D6')->getValue());
            $this->assertSame('17/10/2026', $sheet->getCell('E6')->getValue());
            $this->assertSame('s', $sheet->getCell('D6')->getDataType());
            $this->assertSame('n', $sheet->getCell('H6')->getDataType());
            $this->assertSame('n', $sheet->getCell('K6')->getDataType());
            $archivo->disconnectWorksheets();
        } finally {
            unlink($ruta);
        }
    }

    public function test_excel_gastos_sin_resultados_conserva_logo_y_encabezados(): void
    {
        $export = new GastosExport([
            'desde' => '2026-10-01', 'hasta' => '2026-10-31', 'factura' => '', 'proveedor' => '',
        ]);
        $this->assertCount(0, $export->collection());
        $ruta = tempnam(sys_get_temp_dir(), 'gastos_vacio_');
        try {
            file_put_contents($ruta, \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));
            $archivo = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
            $sheet = $archivo->getActiveSheet();
            $this->assertSame(5, $sheet->getHighestRow());
            $this->assertSame($export->headings(), $sheet->toArray()[4]);
            $this->assertCount(1, $sheet->getDrawingCollection());
            $archivo->disconnectWorksheets();
        } finally {
            unlink($ruta);
        }
    }

    public function test_guardado_solo_devuelve_el_registro_sin_crear_abonos(): void
    {
        $response = (new Gastos)->guardar(new Request($this->datos()));
        $data = $response->getData(true);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(1, $data['gasto']['id']);
        $this->assertArrayNotHasKey('impresion_url', $data['gasto']);
        $this->assertSame(0, DB::table('gasto_pagos')->count());
    }

    public function test_acciones_presentan_modales_sin_confirmacion_nativa(): void
    {
        $id = $this->guardar();
        Livewire::test(Gastos::class)->set('seleccionados', [(string) $id])
            ->assertSee('data-target="#modalAnularGastos"', false)
            ->assertSee('data-target="#modalPasarComprasGastos"', false)
            ->assertSee('wire:submit="anularSeleccionados"', false)
            ->assertSee('wire:submit="pasarACompras"', false)
            ->assertSee('Confirmar anulación')->assertSee('Confirmar traslado')
            ->assertDontSee('wire:confirm', false);
        $this->assertSame('activo', DB::table('gastos')->find($id)->estado);
        $this->assertEquals(0, DB::table('gastos')->find($id)->incluir_reporte_compras);
    }

    public function test_filtros_del_mes_fechas_inclusivas_factura_y_proveedor_se_comparten_con_excel_y_seleccion(): void
    {
        DB::table('proveedores')->insert(['id' => 2, 'nombre' => 'Segundo proveedor', 'estado_id' => 1]);
        $inicio = $this->guardar(['numero_factura' => 'FILTRO-INICIO', 'fecha_emision' => '2026-10-01']);
        $fin = $this->guardar(['numero_factura' => 'FILTRO-FIN', 'fecha_emision' => '2026-10-31', 'fecha_vencimiento' => '2026-11-05']);
        $this->guardar(['numero_factura' => 'OTRA-FACTURA']);
        $this->guardar(['numero_factura' => 'FILTRO-OTRO-PROVEEDOR', 'seleccionarProveedorId' => 2]);
        $septiembre = $this->guardar(['numero_factura' => 'FILTRO-SEPTIEMBRE', 'fecha_emision' => '2026-09-30']);
        $this->guardar(['numero_factura' => 'FILTRO-NOVIEMBRE', 'fecha_emision' => '2026-11-01', 'fecha_vencimiento' => '2026-11-05']);

        $component = Livewire::test(Gastos::class)
            ->assertSet('filtros.desde', '2026-10-01')->assertSet('filtros.hasta', '2026-10-31')
            ->assertSee('FILTRO-INICIO')->assertSee('FILTRO-FIN')
            ->assertDontSee('FILTRO-SEPTIEMBRE')->assertDontSee('FILTRO-NOVIEMBRE')
            ->set('filtros.factura', 'FILTRO-')->set('filtros.proveedor', '1')
            ->assertSee('OTRA-FACTURA')
            ->call('aplicarFiltros')->assertHasNoErrors()->assertDispatched('gastos-filtros-aplicados')
            ->assertDontSee('OTRA-FACTURA')->assertDontSee('FILTRO-OTRO-PROVEEDOR')
            ->set('seleccionarTodos', true)->assertSet('seleccionados', [(string) $inicio, (string) $fin]);

        $export = new GastosExport($component->get('filtrosAplicados'));
        $this->assertSame(['G-' . $fin, 'G-' . $inicio], $export->collection()->pluck('id')->all());
        $component->set('filtros.desde', '2026-09-01')->set('filtros.hasta', '2026-09-30')
            ->call('aplicarFiltros')->assertSet('seleccionados', [])
            ->assertSee('FILTRO-SEPTIEMBRE')->assertDontSee('FILTRO-INICIO')
            ->set('seleccionarTodos', true)->assertSet('seleccionados', [(string) $septiembre])
            ->call('limpiarFiltros')->assertHasNoErrors()
            ->assertSet('filtros.desde', '2026-10-01')->assertSet('filtros.hasta', '2026-10-31')
            ->assertSet('filtros.factura', '')->assertSet('filtros.proveedor', '')
            ->assertSet('seleccionados', [])->assertSee('OTRA-FACTURA');
    }

    public function test_filtros_invalidos_no_reemplazan_los_aplicados_ni_la_seleccion(): void
    {
        $id = $this->guardar();
        foreach ([
            ['desde', '', 'filtros.desde'],
            ['desde', 'no-fecha', 'filtros.desde'],
            ['hasta', '2026-09-30', 'filtros.hasta'],
            ['proveedor', '99999', 'filtros.proveedor'],
            ['factura', str_repeat('x', 256), 'filtros.factura'],
        ] as [$campo, $valor, $error]) {
            Livewire::test(Gastos::class)->set('seleccionarTodos', true)
                ->set('filtros.' . $campo, $valor)->call('aplicarFiltros')->assertHasErrors($error)
                ->assertSet('filtrosAplicados.desde', '2026-10-01')
                ->assertSet('seleccionados', [(string) $id])->assertSee('GASTO-001');
        }
    }

    public function test_proveedores_devuelven_rtn_y_creacion_inline_seleccionable(): void
    {
        $busqueda = (new CompraProducto)->listarProveedores(new Request(['search' => '08011999']));
        $this->assertSame('08011999000001', $busqueda->getData(true)['results'][0]['rtn']);
        $request = new Request([
            'codigo_prov' => 'P2', 'nombre_prov' => 'Proveedor nuevo', 'direccion_prov' => 'Direccion',
            'contacto_prov' => 'Contacto', 'telefono_prov' => '99999999', 'rtn_prov' => '08011999000002',
            'municipio_prov' => 1, 'giro_neg_prov' => 1, 'categoria_prov' => 1, 'retencion_prov' => 1,
        ]);
        $response = (new Proveedores)->proveerdoresModelInsert($request);
        $this->assertSame(200, $response->getStatusCode());
        $proveedor = $response->getData(true)['proveedor'];
        $this->assertSame('08011999000002', $proveedor['rtn']);
        $this->assertSame($proveedor['id'] . ' - Proveedor nuevo', $proveedor['text']);
        $this->assertSame(1, DB::table('proveedores')->find($proveedor['id'])->registrado_por);
        $response = (new Proveedores)->proveerdoresModelInsert(new Request(['nombre_prov' => 'Incompleto']));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertArrayHasKey('rtn_prov', $response->getData(true)['errors']);
    }

    public function test_compra_respeta_tasa_isv_seleccionada_y_calcula_costos(): void
    {
        $this->crearCompraDePrueba();
        $this->assertSame(0, DB::table('gastos')->count());
    }

    public function test_compra_rechaza_tasa_isv_no_permitida(): void
    {
        $response = (new CompraProducto)->guardarCompra(new Request($this->datos([
            'productos' => [['producto_id' => 1, 'precio' => '10', 'cantidad' => 2, 'isv_porcentaje' => 16]],
        ])));

        $this->assertSame(406, $response->getStatusCode());
        $this->assertArrayHasKey('productos.0.isv_porcentaje', $response->getData(true)['errors']);
        $this->assertSame(0, DB::table('compra')->count());
    }

    private function crearCompraDePrueba(): void
    {
        $response = (new CompraProducto)->guardarCompra(new Request($this->datos([
            'productos' => [['producto_id' => 1, 'precio' => '10', 'cantidad' => 2, 'isv_porcentaje' => 0]],
        ])));
        $this->assertSame(200, $response->getStatusCode());
        $compra = DB::table('compra')->first();
        $this->assertEquals(240, $compra->sub_total);
        $this->assertEquals(0, $compra->isv_compra);
        $this->assertEquals(240, $compra->total);
        $this->assertEquals(240, $compra->debito);
        $this->assertEquals(10, DB::table('producto')->find(1)->ultimo_costo_compra);
        $this->assertSame(1, DB::table('compra_has_producto')->count());
        $this->assertEquals(0, DB::table('compra_has_producto')->first()->isv);
        $this->assertEquals(240, DB::table('compra_has_producto')->first()->precio_total);
    }
}
