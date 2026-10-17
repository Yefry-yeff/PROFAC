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
}
