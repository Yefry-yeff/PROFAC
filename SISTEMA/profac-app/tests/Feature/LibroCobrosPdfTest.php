<?php

namespace Tests\Feature;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LibroCobrosPdfTest extends TestCase
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

    public static function tipos(): array
    {
        return [[1], [2], [3]];
    }

    #[DataProvider('tipos')]
    public function test_el_pdf_se_genera_en_papel_carta_horizontal(int $tipo): void
    {
        $user = new User;
        $user->forceFill(['name' => 'Usuario de pruebas']);
        Auth::setUser($user);

        $pdf = Pdf::loadView('pdf.librocobrosrep', [
            'data' => [],
            'grouped' => [],
            'tipo' => $tipo,
            'fechaInicio' => '2026-10-01',
            'fechaFinal' => '2026-10-17',
        ])->setPaper('letter', 'landscape');

        $contenido = $pdf->output();
        $this->assertStringStartsWith('%PDF-', $contenido);
        $this->assertSame(1, preg_match('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+([\d.]+)\s+([\d.]+)\s*\]/', $contenido, $dimensiones));
        $this->assertEqualsWithDelta(792, (float) $dimensiones[1], 0.1);
        $this->assertEqualsWithDelta(612, (float) $dimensiones[2], 0.1);
    }

    #[DataProvider('tipos')]
    public function test_todas_las_columnas_y_textos_caben_dentro_de_la_hoja(int $tipo): void
    {
        $user = new User;
        $user->forceFill(['name' => 'Usuario de pruebas']);
        Auth::setUser($user);
        $row = $tipo === 3 ? [
            'fecha_venta' => '2026-09-01', 'fecha_vencimiento' => '2026-10-01', 'fecha_pago' => '2026-09-15',
            'cliente' => 'INVERSIONES Y DISTRIBUCIONES COMERCIALES DE HONDURAS S. DE R.L.',
            'asesor_comercial' => 'Asesor comercial de pruebas', 'teleasesor' => 'Teleasesor de pruebas',
            'factura' => '000-001-01-00043786', 'monto_cobrado' => 1234567.89, 'estado_factura' => 'COMPLETO',
            'observaciones' => 'Observacion extensa del deposito y su aplicacion a factura. Referencia: ' . str_repeat('1234567890', 12),
            'exonerado' => 0, 'gravado' => 1234567.89, 'excento' => 0, 'isv' => 185185.18, 'total_factura' => 1419753.07,
        ] : [
            'VENDEDOR' => 'Asesor comercial de pruebas', 'CLIENTE' => 'DISTRIBUCIONES COMERCIALES DE HONDURAS',
            'FACTURA' => '000-001-01-00043786', 'EXONERADO' => 0, 'GRAVADO' => 1234567.89,
            'EXCENTO' => 0, 'ABONO' => 1234567.89, 'SUBTOTAL' => 1234567.89, 'ISV' => 185185.18,
            'TOTAL' => 1419753.07, 'RETENCION' => 0, 'TOTAL PAGADO' => 1419753.07,
            'FECHA DE COMPRA' => '01/09/2026', 'FECHA DE VENCIMIENTO' => '01/10/2026', 'FECHA DE PAGO' => '15/09/2026',
            'BANCO' => 'BAC CREDOMATIC', 'OBSERVACIONES' => 'Referencia: ' . str_repeat('1234567890', 12),
        ];
        $data = array_fill(0, 24, $row);
        $pdf = Pdf::loadView('pdf.librocobrosrep', [
            'data' => $data, 'tipo' => $tipo, 'fechaInicio' => '2026-09-01', 'fechaFinal' => '2026-09-30',
            'grouped' => [['banco' => 'BAC CREDOMATIC', 'cuenta' => '722523011', 'rows' => $data]],
        ])->setPaper('letter', 'landscape');
        $bounds = [];
        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_frame',
            'f' => function ($frame) use (&$bounds) {
                if (in_array($frame->get_node()->nodeName, ['table', 'td', 'th', '#text'], true)
                    && trim($frame->get_node()->textContent) !== '') {
                    $bounds[] = [$frame->get_position('x'), $frame->get_margin_width(), $frame->get_node()->nodeName];
                }
            },
        ]]);
        $pdf->output();
        $this->assertNotEmpty($bounds);
        foreach ($bounds as [$x, $width, $tag]) {
            $this->assertGreaterThanOrEqual(17.5, $x, $tag . ' fuera del margen izquierdo');
            $this->assertLessThanOrEqual(774.5, $x + $width, $tag . ' fuera del margen derecho');
        }
    }
}
