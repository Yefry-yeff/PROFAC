<?php

namespace Tests\Unit;

use App\Services\Expo\GestorAumentoExpo;
use PHPUnit\Framework\TestCase;

class GestorAumentoExpoTest extends TestCase
{
    public function test_distribuye_el_aumento_segun_el_descuento_otorgado_en_cada_factura(): void
    {
        $distribucion = (new GestorAumentoExpo())->distribuir([
            ['id' => 101, 'descuento_otorgado' => 100.00],
            ['id' => 102, 'descuento_otorgado' => 200.00],
        ], 75.00);

        $this->assertSame([
            ['factura_id' => 101, 'monto' => 25.00],
            ['factura_id' => 102, 'monto' => 50.00],
        ], $distribucion);
    }

    public function test_asigna_el_residuo_de_redondeo_a_la_ultima_factura(): void
    {
        $distribucion = (new GestorAumentoExpo())->distribuir([
            ['id' => 201, 'descuento_otorgado' => 1.00],
            ['id' => 202, 'descuento_otorgado' => 1.00],
            ['id' => 203, 'descuento_otorgado' => 1.00],
        ], 10.00);

        $this->assertSame(10.00, array_sum(array_column($distribucion, 'monto')));
        $this->assertSame(3.34, $distribucion[2]['monto']);
    }

    public function test_no_genera_movimientos_cuando_no_hay_aumento(): void
    {
        $this->assertSame([], (new GestorAumentoExpo())->distribuir([
            ['id' => 301, 'descuento_otorgado' => 50.00],
        ], 0.00));
    }

    public function test_excluir_factura_no_redistribuye_su_aumento_a_las_demas(): void
    {
        $resultado = (new GestorAumentoExpo())->prepararDistribucion([
            ['id' => 401, 'descuento_otorgado' => 100.00],
            ['id' => 402, 'descuento_otorgado' => 200.00],
        ], 75.00, [401]);

        $this->assertSame([['factura_id' => 402, 'monto' => 50.00]], $resultado['movimientos']);
        $this->assertSame([['factura_id' => 401, 'monto' => 25.00]], $resultado['exclusiones']);
    }
}