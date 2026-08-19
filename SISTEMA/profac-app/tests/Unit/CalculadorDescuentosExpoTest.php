<?php

namespace Tests\Unit;

use App\Services\Expo\CalculadorDescuentosExpo;
use PHPUnit\Framework\TestCase;

class CalculadorDescuentosExpoTest extends TestCase
{
    public function test_acumula_la_marca_y_elige_un_solo_escalon_mayor(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 1, 'subtotal_bruto' => 3000.00],
            ['marca_id' => 1, 'subtotal_bruto' => 2000.00],
        ], [
            'marcas' => [
                ['marca_id' => 1, 'venta_minima' => 5000, 'porcentaje_descuento' => 5],
                ['marca_id' => 1, 'venta_minima' => 10000, 'porcentaje_descuento' => 10],
            ],
            'generales' => [],
        ]);

        $this->assertSame(5.0, $resultado['porcentajes_marca'][1]);
        $this->assertSame(250.0, $resultado['descuento_ganado']);
    }

    public function test_aplica_marca_antes_del_escalon_general(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 1, 'subtotal_bruto' => 10000.00],
        ], [
            'marcas' => [
                ['marca_id' => 1, 'venta_minima' => 5000, 'porcentaje_descuento' => 5],
                ['marca_id' => 1, 'venta_minima' => 10000, 'porcentaje_descuento' => 10],
            ],
            'generales' => [
                ['venta_minima' => 6000, 'porcentaje_descuento' => 5],
                ['venta_minima' => 10000, 'porcentaje_descuento' => 10],
            ],
        ]);

        $this->assertSame(10.0, $resultado['porcentajes_marca'][1]);
        $this->assertSame(10.0, $resultado['porcentaje_general']);
        $this->assertSame(1900.0, $resultado['descuento_ganado']);
    }
}