<?php

namespace Tests\Feature;

use App\Livewire\Inventario\CompraProducto;
use App\Livewire\Inventario\Gastos;
use App\Livewire\Inventario\ListarCompras;
use App\Exports\ComprasMesExport;
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
        $pdf = (new Gastos)->imprimir($id);
        $this->assertSame('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
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
        $this->assertStringStartsWith('%PDF-', (new Gastos)->imprimir($id)->getContent());
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

    public function test_listado_sin_detalle_abonos_ni_saldos_y_pdf_autenticado(): void
    {
        $id = $this->guardar();
        Livewire::test(Gastos::class)->assertSee('GASTO-001')->assertSee('Imprimir gasto')
            ->assertDontSee('Detalle y pagos')->assertDontSee('Historial de pagos')
            ->assertDontSee('Saldo')->assertDontSee('Registrar abono')->assertSee('Registrado')
            ->set('buscar', 'Inexistente')->assertSee('No hay gastos registrados.');
        $this->assertFalse(method_exists(Gastos::class, 'registrarPago'));
        $this->assertFalse(method_exists(Gastos::class, 'ver'));
        $this->assertFalse(method_exists(Gastos::class, 'anular'));
        $this->assertSame(0, DB::table('gasto_pagos')->count());
        $response = (new Gastos)->imprimir($id);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $contenido = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $contenido);
        $this->assertSame(1, preg_match('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+612(?:\.0+)?\s+792(?:\.0+)?\s*\]/', $contenido));
        Auth::logout();
        $this->get('/gastos')->assertRedirect();
        $this->get('/gastos/imprimir/' . $id)->assertRedirect();
    }

    public function test_casilla_clasifica_exclusivamente_y_excel_filtra_mes_y_anio(): void
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
        Livewire::test(Gastos::class)->assertSee('SOLO-GASTOS')->assertDontSee('GASTO-EN-COMPRAS')
            ->assertDontSee('OTRO-ANIO')->assertDontSee('ANULADO');

        $this->crearCompraDePrueba();
        $response = (new ListarCompras)->listarCompras();
        $this->assertSame(200, $response->getStatusCode());
        $rows = collect($response->getData(true)['data']);
        $this->assertFalse($rows->contains('numero_factura', 'SOLO-GASTOS'));
        $this->assertFalse($rows->contains('numero_factura', 'ANULADO'));
        $row = $rows->firstWhere('numero_factura', 'GASTO-EN-COMPRAS');
        $this->assertSame('G-' . $compra, $row['id']);
        $this->assertStringContainsString('/gastos/imprimir/' . $compra, $row['opciones']);
        $this->assertStringNotContainsString('/recibir/', $row['opciones']);
        $this->assertStringNotContainsString('/pagos/', $row['opciones']);
        $this->assertStringNotContainsString('anularCompra(', $row['anular']);
        $this->assertTrue($rows->contains('tipo_documento', 'compra'));

        $export = new ComprasMesExport('2026-10');
        $datos = $export->collection();
        $this->assertCount(2, $datos);
        $gastoExportado = $datos->last();
        $this->assertSame('GASTO-EN-COMPRAS', $gastoExportado['numero_factura']);
        $this->assertEquals(207, $gastoExportado['total']);
        $this->assertNull($gastoExportado['debito']);
        $this->assertCount(count($export->headings()), $gastoExportado);
        $xlsx = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertStringStartsWith('PK', $xlsx);
    }

    public function test_guardado_devuelve_url_del_documento_sin_crear_abonos(): void
    {
        $response = (new Gastos)->guardar(new Request($this->datos()));
        $data = $response->getData(true);
        $this->assertSame(url('/gastos/imprimir/' . $data['gasto']['id']), $data['gasto']['impresion_url']);
        $this->assertSame(0, DB::table('gasto_pagos')->count());
    }

    public function test_pdf_multipagina_muestra_unidades_descuentos_y_totales_sin_abonos(): void
    {
        $id = $this->guardar(['incluir_reporte_compras' => true, 'productos' => array_fill(0, 60, [
            'nombre' => 'Concepto extenso de gasto para impresion ' . str_repeat('1234567890', 8),
            'cantidad' => 2, 'precio' => '100.00', 'isv_porcentaje' => 15,
            'unidad_medida_id' => 2, 'descuento_porcentaje' => 10,
        ])]);
        $gasto = DB::table('gastos as g')->join('proveedores as p', 'p.id', '=', 'g.proveedor_id')
            ->join('users as u', 'u.id', '=', 'g.usuario_id')->where('g.id', $id)
            ->first(['g.*', 'p.nombre as proveedor', 'p.rtn', 'p.direccion', 'u.name as usuario']);
        $lineas = DB::table('gasto_detalles as gd')->leftJoin('unidad_medida as um', 'um.id', '=', 'gd.unidad_medida_id')
            ->where('gd.gasto_id', $id)->get(['gd.*', 'um.nombre as unidad_medida']);
        $html = view('pdf.gasto', compact('gasto', 'lineas'))->render();
        $this->assertStringContainsString('COMPROBANTE DE GASTO', $html);
        $this->assertStringContainsString('RESMA', $html);
        $this->assertStringContainsString('10.00%', $html);
        $this->assertStringContainsString('12,420.00', $html);
        $this->assertStringNotContainsString('Saldo', $html);
        $this->assertStringNotContainsString('Abono', $html);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.gasto', compact('gasto', 'lineas'))->setPaper('letter');
        $bounds = [];
        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_frame',
            'f' => function ($frame) use (&$bounds) {
                if (in_array($frame->get_node()->nodeName, ['td', 'th', '#text'], true)
                    && trim($frame->get_node()->textContent) !== '') {
                    $bounds[] = [$frame->get_position('x'), $frame->get_margin_width()];
                }
            },
        ]]);
        $contenido = $pdf->output();
        $this->assertStringStartsWith('%PDF-', $contenido);
        $this->assertGreaterThan(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
        foreach ($bounds as [$x, $width]) {
            $this->assertGreaterThanOrEqual(27.5, $x);
            $this->assertLessThanOrEqual(584.5, $x + $width);
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

    public function test_compra_conserva_unidades_impuesto_y_costos_del_catalogo(): void
    {
        $this->crearCompraDePrueba();
        $this->assertSame(0, DB::table('gastos')->count());
    }

    private function crearCompraDePrueba(): void
    {
        $response = (new CompraProducto)->guardarCompra(new Request($this->datos([
            'productos' => [['producto_id' => 1, 'precio' => '10', 'cantidad' => 2, 'isv_porcentaje' => 0]],
        ])));
        $this->assertSame(200, $response->getStatusCode());
        $compra = DB::table('compra')->first();
        $this->assertEquals(240, $compra->sub_total);
        $this->assertEquals(36, $compra->isv_compra);
        $this->assertEquals(276, $compra->total);
        $this->assertEquals(276, $compra->debito);
        $this->assertEquals(11.5, DB::table('producto')->find(1)->ultimo_costo_compra);
        $this->assertSame(1, DB::table('compra_has_producto')->count());
    }
}
