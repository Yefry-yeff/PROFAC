<?php

namespace Tests\Feature;

use App\Livewire\Reportes\ReporteVentasCobros;
use App\Services\Reportes\VentasCobrosPdf;
use Dompdf\Canvas;
use Dompdf\Dompdf;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VentasCobrosPdfTest extends TestCase
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

    private function row(array $overrides = []): object
    {
        return (object) array_merge([
            'mes' => 'Octubre', 'anio' => 2026, 'factura_id' => 1,
            'cliente' => 'Cliente de prueba', 'numero_secuencia_cai' => 45300,
            'saldo_pendiente' => 150, 'isv' => 150, 'credito' => 1150,
            'abonos' => 1000, 'estado_f01' => 'ACTIVA', 'fecha_pago' => '2026-10-17',
            'fecha_venta' => '2026-10-15', 'fecha_vencimiento' => '2026-11-15',
            'total' => 1150, 'observacion' => 'Observación de prueba',
        ], $overrides);
    }

    public function test_exporta_pdf_sin_filtros_con_papel_legal_y_token_legacy(): void
    {
        DB::shouldReceive('select')->once()->withArgs(
            fn ($sql, $bindings) => str_contains($sql, 'ORDER BY f.fecha_emision ASC, f.id ASC') && $bindings === []
        )->andReturn([$this->row()]);

        $request = Request::create('/', 'POST', ['download_token' => 'pdf_test']);
        $response = app(ReporteVentasCobros::class)->exportarPdf($request, 'null', 'null', 'null', 'null');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertMatchesRegularExpression('/\/MediaBox\s*\[0\.000 0\.000 1008\.000 612\.000\]/', $response->getContent());
        $this->assertSame('pdf_test', $response->headers->getCookies()[0]->getValue());
    }

    public function test_conserva_todos_los_filtros_de_exportacion(): void
    {
        DB::shouldReceive('select')->once()->withArgs(function ($sql, $bindings) {
            return $bindings === [
                2, 3, 4, 10, 2026, '%45300%', '%45300%', '2026-10-01', '2026-10-31',
                'ACTIVA', 2, 5, '%1234%', '2026-10-01', '2026-10-31',
            ];
        })->andReturn([$this->row()]);
        $request = Request::create('/', 'POST', [
            'vendedor' => 2, 'teleasesor' => 3, 'cliente' => 4, 'factura' => '45300',
            'fecha_desde' => '2026-10-01', 'fecha_hasta' => '2026-10-31',
            'estado_cobro' => 'Parcialmente Pagada', 'estado_f01' => 'ACTIVA',
            'modo_pago' => 2, 'banco' => 5, 'cuenta' => '1234',
            'fecha_pago_desde' => '2026-10-01', 'fecha_pago_hasta' => '2026-10-31',
        ]);
        $response = app(ReporteVentasCobros::class)->exportarPdf($request, null, null, 10, 2026);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_no_exporta_filas_excluidas_por_estado_de_cobro(): void
    {
        DB::shouldReceive('select')->once()->andReturn([$this->row()]);
        $renderer = $this->mock(VentasCobrosPdf::class);
        $renderer->shouldReceive('generar')->once()->with([])->andReturn('%PDF-test');
        $request = Request::create('/', 'POST', ['estado_cobro' => 'Pagada']);
        $response = app(ReporteVentasCobros::class)->exportarPdf($request);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_genera_pdf_sin_resultados_y_admite_exportaciones_repetidas(): void
    {
        foreach ([[], [$this->row()]] as $rows) {
            $this->assertStringStartsWith('%PDF-', app(VentasCobrosPdf::class)->generar($rows));
        }
    }

    public function test_logo_conserva_proporcion_y_no_se_superpone_al_encabezado(): void
    {
        $dompdf = app(Dompdf::class);
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $bold = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'bold');
        $canvas = \Mockery::mock(Canvas::class)->shouldIgnoreMissing();
        $logoRight = 0;
        $canvas->shouldReceive('image')->once()->withArgs(function ($path, $x, $y, $width, $height) use (&$logoRight) {
            [$originalWidth, $originalHeight] = getimagesize($path);
            $this->assertEqualsWithDelta($originalWidth / $originalHeight, $width / $height, 0.001);
            $this->assertGreaterThan(300, $width);
            $this->assertLessThan(63, $y + $height);
            $logoRight = $x + $width;
            return true;
        });
        $canvas->shouldReceive('get_text_width')->andReturn(1);
        $canvas->shouldReceive('text')->withArgs(function ($x, $y, $text, $font, $size, ...$options) use (&$logoRight, $dompdf) {
            if ($y < 63) {
                $this->assertGreaterThan($logoRight, $x);
                $this->assertLessThanOrEqual(990, $x + $dompdf->getCanvas()->get_text_width($text, $font, $size));
            }
            return true;
        });
        $method = new \ReflectionMethod(VentasCobrosPdf::class, 'header');
        $method->invoke(app(VentasCobrosPdf::class), $canvas, $font, $bold, array_fill(0, 31, 30), 'OCTUBRE 2026', '23/10/2026 01:22');
    }

    public function test_pagina_por_mes_y_conserva_textos_que_superan_una_pagina(): void
    {
        $dompdf = app(Dompdf::class);
        $this->app->instance('dompdf', $dompdf);
        $rows = [
            $this->row(['mes' => 'Septiembre']),
            $this->row(['detalle_abonos' => str_repeat('Referencia de abono extensa ', 600)]),
        ];
        $pdf = app(VentasCobrosPdf::class)->generar($rows);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(2, $dompdf->getCanvas()->get_page_count());
        $this->assertSame(1, $rows[0]->item);
        $this->assertSame(2, $rows[1]->item);
    }

    public function test_divide_texto_y_referencias_largas_sin_perder_contenido_ni_rebasar_columnas(): void
    {
        $dompdf = app(Dompdf::class);
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $method = new \ReflectionMethod(VentasCobrosPdf::class, 'wrap');
        $text = 'Observación con acentos y referencia ' . str_repeat('1234567890', 12);
        $lines = $method->invoke(app(VentasCobrosPdf::class), $canvas, $font, $text, 30);
        $this->assertSame(preg_replace('/\s+/u', '', $text), preg_replace('/\s+/u', '', implode('', $lines)));
        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(30, $canvas->get_text_width($line, $font, 4.5));
        }
    }
}
