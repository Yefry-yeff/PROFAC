<?php

namespace Tests\Unit;

use App\Services\Expo\DescuentoFirmadoLineaExpo;
use PHPUnit\Framework\TestCase;

class DescuentoFirmadoLineaExpoTest extends TestCase
{
    public function test_prorratea_el_descuento_firmado_segun_la_cantidad_facturada(): void
    {
        $calculador = new DescuentoFirmadoLineaExpo();

        $this->assertSame(80.0, $calculador->prorratear(100, 100, 80));
    }

    public function test_no_aplica_mas_descuento_que_el_firmado(): void
    {
        $calculador = new DescuentoFirmadoLineaExpo();

        $this->assertSame(100.0, $calculador->prorratear(100, 100, 120));
    }

    public function test_sin_cantidad_o_descuento_devuelve_cero(): void
    {
        $calculador = new DescuentoFirmadoLineaExpo();

        $this->assertSame(0.0, $calculador->prorratear(100, 0, 80));
        $this->assertSame(0.0, $calculador->prorratear(0, 100, 80));
    }
}