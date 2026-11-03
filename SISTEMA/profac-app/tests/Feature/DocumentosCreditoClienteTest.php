<?php

namespace Tests\Feature;

use App\Exports\ReporteClientesHoja1;
use App\Livewire\Clientes\Cliente;
use App\Livewire\Clientes\ReporteClientes;
use App\Models\ClienteDocumento;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ReflectionMethod;
use Tests\TestCase;

class DocumentosCreditoClienteTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.documentos_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        $app['config']->set('database.default', 'documentos_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($date) => (int) substr($date, 0, 4));
        Schema::create('cliente', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->integer('vendedor')->default(1);
            $t->integer('estado_cliente_id')->default(1);
            $t->timestamps();
            foreach (['ano_operacion', 'ref_referencias', 'ref_tiempo_relacion', 'ref_tiempo_credito',
                'ref_limite_credito', 'metodo_pago', 'ref_observaciones'] as $column) {
                $t->string($column)->nullable();
            }
        });
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('estado_cliente', function (Blueprint $t) { $t->id(); $t->string('descripcion'); });
        Schema::create('cliente_credito', function (Blueprint $t) {
            $t->id(); $t->integer('cliente_id'); $t->integer('users_id')->default(1);
            $t->boolean('activo')->default(true); $t->boolean('credito_activo')->default(true);
            $t->boolean('letra_cambio')->default(false); $t->boolean('aval_solidario')->default(false);
            $t->decimal('credito', 12, 2)->default(1000); $t->integer('dias_credito')->default(30);
            $t->string('referencias_bancarias')->nullable(); $t->string('referencias_comerciales')->nullable();
            $t->string('autorizacion_gerencia')->nullable(); $t->date('fecha_vigencia')->nullable(); $t->timestamps();
        });
        Schema::create('cliente_documentos', function (Blueprint $t) {
            $t->id(); $t->integer('cliente_id'); $t->string('tipo_documento', 60);
            $t->string('nombre_original'); $t->string('ruta_archivo'); $t->integer('users_id');
            $t->timestamps(); $t->unique(['cliente_id', 'tipo_documento']);
        });
        Schema::create('cliente_doc_fisico', function (Blueprint $t) {
            $t->id(); $t->integer('cliente_id'); $t->string('tipo_documento', 60); $t->timestamps();
        });
        Schema::create('cliente_observaciones', function (Blueprint $t) {
            $t->id(); $t->integer('cliente_id'); $t->string('observacion');
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Vendedor']);
        DB::table('estado_cliente')->insert(['id' => 1, 'descripcion' => 'Activo']);
        DB::table('cliente')->insert([
            ['id' => 1, 'nombre' => 'Cliente con credito', 'created_at' => '2026-01-01'],
            ['id' => 2, 'nombre' => 'Cliente sin credito', 'created_at' => '2026-01-01'],
        ]);
        DB::table('cliente_credito')->insert(['cliente_id' => 1]);
    }

    private function general(?int $vendedor = null, ?int $estado = null): array
    {
        $method = new ReflectionMethod(ReporteClientes::class, 'sqlGeneral');
        return $method->invoke(new ReporteClientes(), $vendedor, $estado);
    }

    private function documento(int $cliente, string $tipo): void
    {
        ClienteDocumento::create([
            'cliente_id' => $cliente, 'tipo_documento' => $tipo,
            'nombre_original' => $tipo . '.pdf', 'ruta_archivo' => $tipo . '.pdf', 'users_id' => 1,
        ]);
    }

    public function test_new_document_types_are_accepted_by_upload_validation(): void
    {
        foreach (['solicitud_credito', 'condiciones_credito'] as $tipo) {
            $this->assertArrayHasKey($tipo, ClienteDocumento::$tipos);
            $request = Request::create('/clientes/documento/subir', 'POST', ['tipo_documento' => $tipo]);
            $response = (new Cliente())->subirDocumento($request);
            $this->assertSame(422, $response->getStatusCode());
            $this->assertSame('No se recibió ningún archivo.', $response->getData()->text);
        }
    }

    public function test_report_requires_actual_documents_not_credit_approval(): void
    {
        foreach ($this->general() as $row) {
            $this->assertSame('SOLICITAR', $row->solicitud_credito);
            $this->assertSame('SOLICITAR', $row->condiciones_credito);
        }
        $this->documento(2, 'solicitud_credito');
        DB::table('cliente_doc_fisico')->insert(['cliente_id' => 2, 'tipo_documento' => 'condiciones_credito']);
        $rows = $this->general();
        $this->assertCount(2, $rows);
        $this->assertSame('X', $rows[1]->solicitud_credito);
        $this->assertSame('FISICO', $rows[1]->condiciones_credito);
        $this->assertSame(0, $rows[1]->monto_credito);
    }

    public function test_digital_has_priority_and_removing_documents_updates_report_and_preserves_filters(): void
    {
        foreach (['solicitud_credito', 'condiciones_credito'] as $tipo) {
            $this->documento(1, $tipo);
            DB::table('cliente_doc_fisico')->insert(['cliente_id' => 1, 'tipo_documento' => $tipo]);
        }
        $rows = $this->general(1, 1);
        $this->assertCount(2, $rows);
        $this->assertSame('X', $rows[0]->solicitud_credito);
        $this->assertSame('X', $rows[0]->condiciones_credito);
        ClienteDocumento::query()->delete();
        $rows = $this->general();
        $this->assertSame('FISICO', $rows[0]->solicitud_credito);
        $this->assertSame('FISICO', $rows[0]->condiciones_credito);
        DB::table('cliente_doc_fisico')->delete();
        $this->assertSame('SOLICITAR', $this->general()[0]->solicitud_credito);
        $this->assertSame([], $this->general(99));
        $this->assertSame([], $this->general(null, 99));
    }

    public function test_excel_and_pdf_use_same_document_statuses_without_adding_duplicate_columns(): void
    {
        $this->documento(1, 'solicitud_credito');
        DB::table('cliente_doc_fisico')->insert(['cliente_id' => 1, 'tipo_documento' => 'condiciones_credito']);
        $rows = $this->general();
        foreach ($rows as $index => $row) {
            $row->item = $index + 1;
        }
        $export = new ReporteClientesHoja1($rows, 'Prueba');
        $array = $export->array();
        $this->assertCount(34, $array[3]);
        $this->assertSame('X', $array[4][5]);
        $this->assertSame('FISICO', $array[4][6]);
        $this->assertSame('SOLICITAR', $array[5][5]);
        $this->assertSame('SOLICITAR', $array[5][6]);
        $path = tempnam(sys_get_temp_dir(), 'credito-xlsx-');
        try {
            file_put_contents($path, Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $this->assertSame('X', $sheet->getCell('F5')->getValue());
            $this->assertSame('FISICO', $sheet->getCell('G5')->getValue());
            $this->assertSame('SOLICITAR', $sheet->getCell('F6')->getValue());
            $spreadsheet->disconnectWorksheets();
        } finally {
            unlink($path);
        }
        $html = view('pdf.reporteclientes', ['general' => $rows, 'sinCredito' => [], 'gobierno' => []])->render();
        $this->assertMatchesRegularExpression('/<td><span class="badge-x">.*?<\/span><\/td>\s*<td>FISICO<\/td>/s', $html);
        $this->assertStringContainsString('SOL.CRED.', $html);
        $this->assertStringContainsString('COND.CRED.', $html);
    }
}
