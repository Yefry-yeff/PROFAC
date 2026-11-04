<?php

namespace Tests\Feature;

use App\Exports\NotasCreditoExport;
use App\Livewire\NotaCredito\ListadoNotaCredito;
use App\Livewire\NotaCredito\ListadoNotasND;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FiltrosNotasCreditoTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', fn (...$parts) => implode('', $parts));
        Schema::create('cliente', function (Blueprint $t) {
            $t->id(); $t->string('nombre');
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name');
        });
        Schema::create('motivo_nota_credito', function (Blueprint $t) {
            $t->id(); $t->string('descripcion');
        });
        Schema::create('factura', function (Blueprint $t) {
            $t->id(); $t->integer('tipo_venta_id'); $t->integer('estado_venta_id')->default(1);
            $t->integer('cliente_id')->default(1); $t->integer('numero_secuencia_cai'); $t->string('cai');
            $t->date('fecha_emision');
        });
        Schema::create('nota_credito', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('numero_secuencia_cai'); $t->string('cai');
            $t->integer('users_id')->default(1); $t->integer('motivo_nota_credito_id')->default(1);
            $t->integer('estado_nota_id')->default(1); $t->string('comentario')->default('Prueba');
            $t->decimal('sub_total')->default(100); $t->decimal('isv')->default(15);
            $t->decimal('total')->default(115); $t->date('fecha'); $t->timestamps();
        });
        Schema::create('nota_credito_creditos', function (Blueprint $t) {
            $t->integer('nota_credito_id'); $t->decimal('monto_aplicado');
            $t->decimal('monto_reembolsado'); $t->decimal('saldo_disponible'); $t->string('estado');
        });
        Schema::create('nota_credito_reversiones', function (Blueprint $t) {
            $t->id(); $t->integer('nota_credito_id'); $t->string('alcance'); $t->string('motivo');
        });
        Schema::create('aplicacion_pagos', function (Blueprint $t) {
            $t->integer('cliente_id'); $t->integer('estado'); $t->integer('estado_cerrado'); $t->decimal('saldo');
        });
        DB::table('cliente')->insert(['id' => 1, 'nombre' => 'Cliente de prueba']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Usuario de prueba']);
        DB::table('motivo_nota_credito')->insert(['id' => 1, 'descripcion' => 'Devolucion']);

        foreach ([1, 2] as $tipo) {
            foreach ([1 => '2025-01-05', 2 => '2026-11-05'] as $numero => $fecha) {
                $id = $tipo * 10 + $numero;
                DB::table('factura')->insert([
                    'id' => $id, 'tipo_venta_id' => $tipo,
                    'numero_secuencia_cai' => 45000 + $id, 'cai' => '000-001-01-000' . (45000 + $id),
                    'fecha_emision' => $fecha,
                ]);
                DB::table('nota_credito')->insert([
                    'id' => $id, 'factura_id' => $id, 'numero_secuencia_cai' => 2000 + $id,
                    'cai' => 'NC-' . $id, 'fecha' => $fecha, 'created_at' => $fecha . ' 12:00:00',
                ]);
            }
        }
    }

    public static function escenarios(): array
    {
        $cases = [];
        foreach ([1, 2] as $tipo) {
            $anterior = $tipo * 10 + 1;
            $actual = $tipo * 10 + 2;
            $cases["tipo-$tipo-fechas"] = [$tipo, [], [$actual]];
            $cases["tipo-$tipo-factura-antigua"] = [$tipo, ['numero_secuencia_cai_factura' => '  ' . (45000 + $anterior) . '  '], [$anterior]];
            $cases["tipo-$tipo-cai-completo"] = [$tipo, ['numero_secuencia_cai_factura' => '000-001-01-000' . (45000 + $anterior)], [$anterior]];
            $cases["tipo-$tipo-vacio"] = [$tipo, ['numero_secuencia_cai_factura' => '   '], [$actual]];
            $cases["tipo-$tipo-no-existe"] = [$tipo, ['numero_secuencia_cai_factura' => '999999'], []];
            $cases["tipo-$tipo-parametrizado"] = [$tipo, ['numero_secuencia_cai_factura' => "' OR 1=1 --"], []];
            $cases["tipo-$tipo-cliente"] = [$tipo, ['numero_secuencia_cai_factura' => (string) (45000 + $anterior), 'cliente_id' => 999], []];
            $cases["tipo-$tipo-motivo"] = [$tipo, ['numero_secuencia_cai_factura' => (string) (45000 + $anterior), 'motivo_id' => 999], []];
            $cases["tipo-$tipo-usuario"] = [$tipo, ['numero_secuencia_cai_factura' => (string) (45000 + $anterior), 'user_id' => 999], []];
            $cases["tipo-$tipo-nota"] = [$tipo, ['numero_secuencia_cai' => (string) (2000 + $actual)], [$actual]];
            $cases["tipo-$tipo-nota-no-es-factura"] = [$tipo, ['numero_secuencia_cai_factura' => (string) (2000 + $actual)], []];
            $cases["tipo-$tipo-ambas-secuencias"] = [$tipo, [
                'numero_secuencia_cai_factura' => (string) (45000 + $anterior),
                'numero_secuencia_cai' => (string) (2000 + $anterior),
            ], [$anterior]];
        }
        return $cases;
    }

    #[DataProvider('escenarios')]
    public function test_listado_indicadores_y_excel_comparten_la_prioridad_de_factura(int $tipo, array $filtros, array $ids): void
    {
        $request = new Request(array_merge([
            'fechaInicio' => '2026-11-01', 'fechaFinal' => '2026-11-30', 'tipo_venta' => $tipo,
        ], $filtros));
        $componente = $tipo === 2 ? new ListadoNotaCredito : new ListadoNotasND;
        $response = $tipo === 2
            ? $componente->listadoNotaCredito($request)
            : $componente->listadoNotaCreditoND($request);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $data = $response->getData(true);
        $this->assertSame($ids, array_map(fn ($row) => (int) $row['codigo'], $data['data']));

        $kpis = (new ListadoNotaCredito)->kpis($request)->getData(true);
        $this->assertTrue($kpis['success']);
        $this->assertSame(count($ids), (int) $kpis['total']);
        $this->assertEquals(115 * count($ids), $kpis['total_monto']);

        Excel::shouldReceive('download')->once()->withArgs(function (NotasCreditoExport $export, string $filename) use ($ids) {
            $rows = $export->sheets()[0]->array();
            $this->assertSame($ids, array_map(fn ($row) => (int) $row[2], array_slice($rows, 4, -1)));
            return true;
        })->andReturn(response()->download(__FILE__));
        $excel = $componente->exportarExcel($request);
        $this->assertSame(200, $excel->getStatusCode(), (string) $excel->getContent());
    }
}
