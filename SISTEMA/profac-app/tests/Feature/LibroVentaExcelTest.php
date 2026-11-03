<?php

namespace Tests\Feature;

use App\Livewire\Reportes\Libroventarep;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class LibroVentaExcelTest extends TestCase
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
        DB::connection()->getPdo()->sqliteCreateFunction('IF', fn ($condition, $yes, $no) => $condition ? $yes : $no);
        Schema::create('cliente', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('tipo_pago_venta', function (Blueprint $t) { $t->id(); });
        Schema::create('producto', function (Blueprint $t) { $t->id(); $t->decimal('isv'); });
        Schema::create('venta_has_producto', function (Blueprint $t) {
            $t->id(); $t->integer('factura_id'); $t->integer('producto_id');
            $t->string('tipo_precio')->nullable(); $t->decimal('sub_total_s'); $t->decimal('isv');
        });
        Schema::create('factura', function (Blueprint $t) {
            $t->id(); $t->integer('numero_secuencia_cai'); $t->date('fecha_emision');
            $t->integer('cliente_id')->default(1); $t->integer('vendedor')->default(1);
            $t->integer('users_id')->default(1); $t->integer('tipo_pago_id')->default(1);
            $t->integer('estado_venta_id')->default(1); $t->integer('tipo_venta_id')->default(1);
            $t->decimal('sub_total')->default(100); $t->decimal('isv')->default(15); $t->decimal('total')->default(115);
        });
        DB::table('cliente')->insert([['id' => 1, 'nombre' => 'Cliente A'], ['id' => 2, 'nombre' => 'Cliente B']]);
        DB::table('users')->insert(['id' => 1, 'name' => 'Asesor']);
        DB::table('tipo_pago_venta')->insert(['id' => 1]);
        DB::table('factura')->insert([
            ['id' => 1, 'numero_secuencia_cai' => 100, 'fecha_emision' => '2026-10-01', 'cliente_id' => 1],
            ['id' => 2, 'numero_secuencia_cai' => 9, 'fecha_emision' => '2026-10-20', 'cliente_id' => 1],
            ['id' => 3, 'numero_secuencia_cai' => 10, 'fecha_emision' => '2026-10-05', 'cliente_id' => 2],
            ['id' => 4, 'numero_secuencia_cai' => 1, 'fecha_emision' => '2026-09-30', 'cliente_id' => 1],
        ]);
    }

    private function export(array $filters = []): array
    {
        $response = (new Libroventarep())->exportarExcel(
            Request::create('/reporte/Libroventarep/exportar-excel/4/2026-10-01/2026-10-31', 'POST', $filters),
            4, '2026-10-01', '2026-10-31'
        );
        $this->assertSame(200, $response->getStatusCode());
        $path = $response->getFile()->getPathname();
        try {
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            $spreadsheet->disconnectWorksheets();
            return $rows;
        } finally {
            unlink($path);
        }
    }

    public function test_excel_is_sorted_numerically_by_invoice_not_by_emission_date(): void
    {
        $rows = $this->export();
        $this->assertSame('FACTURA', $rows[3][3]);
        $this->assertSame([9, 10, 100], array_map(fn ($row) => (int) $row[3], array_slice($rows, 4, -1)));
        $this->assertSame('TOTALES:', $rows[7][0]);
        $this->assertEquals(345, $rows[7][9]);
    }

    public function test_invoice_order_preserves_date_customer_and_other_filters(): void
    {
        $rows = $this->export(['cliente' => 1, 'vendedor' => 1, 'modo_pago' => 1]);
        $this->assertSame([9, 100], array_map(fn ($row) => (int) $row[3], array_slice($rows, 4, -1)));
        $this->assertEquals(230, $rows[6][9]);
        $rows = $this->export(['factura' => '10']);
        $this->assertSame([10, 100], array_map(fn ($row) => (int) $row[3], array_slice($rows, 4, -1)));
    }
}
