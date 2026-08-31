<?php

namespace Tests\Unit;

use App\Services\Expo\CalculadorDescuentosExpo;
use PHPUnit\Framework\TestCase;

class CalculadorDescuentosExpoTest extends TestCase
{
    public function test_el_escalon_se_evalua_con_el_subtotal_neto_despues_del_descuento(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 10, 'subtotal_bruto' => 500000.00],
        ], [
            'version' => 4,
            'marcas' => [
                ['marca_id' => 10, 'venta_minima' => 500000, 'porcentaje_descuento' => 30],
            ],
            'generales' => [],
        ]);

        $this->assertSame(0.0, $resultado['porcentajes_marca'][10]);
        $this->assertSame(500000.0, $resultado['subtotal_neto']);
    }

    public function test_concede_el_escalon_cuando_el_subtotal_neto_alcanza_el_minimo(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 10, 'subtotal_bruto' => 500000.00],
        ], [
            'version' => 4,
            'marcas' => [
                ['marca_id' => 10, 'venta_minima' => 350000, 'porcentaje_descuento' => 30],
            ],
            'generales' => [],
        ]);

        $this->assertSame(30.0, $resultado['porcentajes_marca'][10]);
        $this->assertSame(150000.0, $resultado['descuento_ganado']);
        $this->assertSame(350000.0, $resultado['subtotal_neto']);
    }

    public function test_suma_todas_las_marcas_para_alcanzar_el_subtotal_neto_requerido(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 10, 'subtotal_bruto' => 500000.00],
            ['marca_id' => 20, 'subtotal_bruto' => 750000.00],
        ], [
            'version' => 4,
            'marcas' => [
                ['marca_id' => 10, 'venta_minima' => 1000000, 'porcentaje_descuento' => 20],
                ['marca_id' => 20, 'venta_minima' => 1000000, 'porcentaje_descuento' => 20],
            ],
            'generales' => [],
        ]);

        $this->assertSame(20.0, $resultado['porcentajes_marca'][10]);
        $this->assertSame(20.0, $resultado['porcentajes_marca'][20]);
        $this->assertSame(1000000.0, $resultado['subtotal_neto']);
    }

    public function test_el_total_ofertado_activa_el_escalon_de_cada_marca_parametrizada(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 10, 'subtotal_bruto' => 400000.00],
            ['marca_id' => 20, 'subtotal_bruto' => 600000.00],
        ], [
            'version' => 3,
            'marcas' => [
                ['marca_id' => 10, 'venta_minima' => 1000000, 'porcentaje_descuento' => 20],
                ['marca_id' => 20, 'venta_minima' => 1000000, 'porcentaje_descuento' => 20],
            ],
            'generales' => [],
        ]);

        $this->assertSame(20.0, $resultado['porcentajes_marca'][10]);
        $this->assertSame(20.0, $resultado['porcentajes_marca'][20]);
        $this->assertSame(200000.0, $resultado['descuento_ganado']);
    }

    public function test_snapshot_version_dos_conserva_escalones_por_subtotal_de_marca(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 10, 'subtotal_bruto' => 400000.00],
            ['marca_id' => 20, 'subtotal_bruto' => 600000.00],
        ], [
            'version' => 2,
            'marcas' => [
                ['marca_id' => 10, 'venta_minima' => 1000000, 'porcentaje_descuento' => 20],
                ['marca_id' => 20, 'venta_minima' => 1000000, 'porcentaje_descuento' => 20],
            ],
            'generales' => [],
        ]);

        $this->assertSame(0.0, $resultado['porcentajes_marca'][10]);
        $this->assertSame(0.0, $resultado['porcentajes_marca'][20]);
        $this->assertSame(0.0, $resultado['descuento_ganado']);
    }

    public function test_acumula_la_marca_y_elige_un_solo_escalon_mayor(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 1, 'subtotal_bruto' => 3000.00],
            ['marca_id' => 1, 'subtotal_bruto' => 2000.00],
        ], [
            'version' => 3,
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
            'version' => 3,
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

    public function test_recalcula_cada_marca_y_el_total_al_agregar_productos(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 102, 'subtotal_bruto' => 10000.00],
            ['marca_id' => 102, 'subtotal_bruto' => 30000.00],
            ['marca_id' => 74, 'subtotal_bruto' => 10000.00],
        ], [
            'version' => 3,
            'marcas' => [
                ['marca_id' => 102, 'venta_minima' => 10000, 'porcentaje_descuento' => 2],
                ['marca_id' => 102, 'venta_minima' => 40000, 'porcentaje_descuento' => 5],
                ['marca_id' => 74, 'venta_minima' => 10000, 'porcentaje_descuento' => 1],
            ],
            'generales' => [
                ['venta_minima' => 10000, 'porcentaje_descuento' => 2],
            ],
        ]);

        $this->assertSame(5.0, $resultado['porcentajes_marca'][102]);
        $this->assertSame(1.0, $resultado['porcentajes_marca'][74]);
        $this->assertSame(2.0, $resultado['porcentaje_general']);
        $this->assertSame(3058.0, $resultado['descuento_ganado']);
    }

    public function test_desglosa_por_marca_y_conserva_el_total_ganado(): void
    {
        $resultado = (new CalculadorDescuentosExpo())->calcular([
            ['marca_id' => 10, 'subtotal_bruto' => 1000.00],
            ['marca_id' => 10, 'subtotal_bruto' => 500.00],
            ['marca_id' => 20, 'subtotal_bruto' => 500.00],
        ], [
            'version' => 3,
            'marcas' => [
                ['marca_id' => 10, 'venta_minima' => 1000, 'porcentaje_descuento' => 10],
                ['marca_id' => 20, 'venta_minima' => 500, 'porcentaje_descuento' => 5],
            ],
            'generales' => [
                ['venta_minima' => 2000, 'porcentaje_descuento' => 2],
            ],
        ]);

        $marcas = collect($resultado['detalle_marcas'])->keyBy('marca_id');

        $this->assertSame(1500.0, $marcas[10]['subtotal_bruto']);
        $this->assertSame(150.0, $marcas[10]['descuento_marca']);
        $this->assertSame(27.0, $marcas[10]['descuento_general']);
        $this->assertSame(177.0, $marcas[10]['descuento_ganado']);
        $this->assertSame(34.5, $marcas[20]['descuento_ganado']);
        $this->assertSame($resultado['descuento_ganado'], collect($resultado['detalle_marcas'])->sum('descuento_ganado'));
    }
}