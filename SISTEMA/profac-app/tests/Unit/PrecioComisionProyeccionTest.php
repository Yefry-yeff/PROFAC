<?php

namespace Tests\Unit;

use App\Support\Comisiones\PrecioComisionProyeccion;
use PHPUnit\Framework\TestCase;

class PrecioComisionProyeccionTest extends TestCase
{
    public function test_categoria_expo_usa_el_precio_neto_realmente_facturado(): void
    {
        $precio = PrecioComisionProyeccion::resolver(1, 121.38, 121.38, 72.83, true);

        $this->assertSame(72.83, $precio['precio_unitario']);
        $this->assertSame(72.83, $precio['precio_comision']);
        $this->assertSame(72.83, $precio['precio_categoria']);
        $this->assertSame('Subtotal neto facturado EXPO', $precio['fuente']);
    }

    public function test_categoria_expo_calcula_el_precio_unitario_para_varias_unidades(): void
    {
        $precio = PrecioComisionProyeccion::resolver(23, 121.38, 121.38, 72.83, true);

        $this->assertSame(72.83, $precio['precio_comision']);
    }

    public function test_venta_normal_conserva_el_precio_seleccionado(): void
    {
        $precio = PrecioComisionProyeccion::resolver(1, 72.83, 121.38, 72.83, false);

        $this->assertSame(72.83, $precio['precio_unitario']);
        $this->assertSame(121.38, $precio['precio_comision']);
        $this->assertSame('Cantidad x precioSeleccionado', $precio['fuente']);
    }

    public function test_categoria_expo_usa_centavos_visibles_para_resolver_la_escala(): void
    {
        $precio = PrecioComisionProyeccion::resolver(
            44,
            63.58,
            63.58,
            1678.51 / 44,
            true
        );

        $this->assertSame(38.15, $precio['precio_unitario']);
        $this->assertSame(38.15, $precio['precio_categoria']);
        $this->assertEqualsWithDelta(38.1479545, $precio['precio_comision'], 0.0000001);
    }
}
