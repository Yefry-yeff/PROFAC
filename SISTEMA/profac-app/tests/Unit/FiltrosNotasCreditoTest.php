<?php

namespace Tests\Unit;

use App\Support\FiltrosNotasCredito;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class FiltrosNotasCreditoTest extends TestCase
{
    public function test_busca_nota_por_numero_cai_secuencia_o_id_sin_restringir_fecha(): void
    {
        $request = new Request([
            'fechaInicio' => '2026-01-01',
            'fechaFinal' => '2026-11-30',
            'numero_secuencia_cai' => '2024-153',
        ]);
        $bindings = [];

        $sql = FiltrosNotasCredito::fechasYSecuencias($request, $bindings);

        $this->assertStringContainsString('A.numero_secuencia_cai', $sql);
        $this->assertStringContainsString('A.numero_nota', $sql);
        $this->assertStringContainsString('A.cai', $sql);
        $this->assertStringContainsString('A.id', $sql);
        $this->assertStringNotContainsString('A.fecha BETWEEN', $sql);
        $this->assertSame(array_fill(0, 4, '%2024-153%'), $bindings);
    }

    public function test_aplica_fechas_cuando_no_hay_busqueda_directa(): void
    {
        $request = new Request([
            'fechaInicio' => '2026-01-01',
            'fechaFinal' => '2026-11-30',
        ]);
        $bindings = [];

        $sql = FiltrosNotasCredito::fechasYSecuencias($request, $bindings);

        $this->assertStringContainsString('A.fecha BETWEEN ? AND ?', $sql);
        $this->assertSame(['2026-01-01', '2026-11-30'], $bindings);
    }
}
