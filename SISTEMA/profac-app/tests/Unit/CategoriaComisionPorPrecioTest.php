<?php

namespace Tests\Unit;

use App\Support\Comisiones\CategoriaComisionPorPrecio;
use PHPUnit\Framework\TestCase;

class CategoriaComisionPorPrecioTest extends TestCase
{
    public function test_usa_el_mayor_precio_que_no_supera_el_precio_vendido(): void
    {
        $resolver = CategoriaComisionPorPrecio::desdeFilas([
            $this->candidato(1, 36, 7, 'F', 100),
            $this->candidato(1, 36, 8, 'G', 110),
        ]);

        $resultado = $resolver->resolver(36, 1, 26, 38, 'C', 'p1', 105);

        $this->assertSame(
            ['categoria_precios_id' => 7, 'categoria_precio' => 'F', 'ajustada' => true],
            $resultado
        );
    }

    public function test_no_cambia_lineas_que_ya_pertenecen_a_la_categoria_del_cliente(): void
    {
        $resolver = CategoriaComisionPorPrecio::desdeFilas([
            $this->candidato(1, 36, 7, 'F', 100),
        ]);

        $resultado = $resolver->resolver(36, 1, 7, 36, 'F', 'p1', 105);

        $this->assertSame(
            ['categoria_precios_id' => 7, 'categoria_precio' => 'F', 'ajustada' => false],
            $resultado
        );
    }

    public function test_usa_categoria_minima_del_cliente_si_el_precio_no_alcanza_la_escala_minima(): void
    {
        $resolver = CategoriaComisionPorPrecio::desdeFilas([
            $this->candidato(1, 36, 2, 'A', 100),
            $this->candidato(1, 36, 3, 'B', 110),
        ]);

        $resultado = $resolver->resolver(36, 1, 26, 38, 'C', 'p1', 99);

        $this->assertSame(
            ['categoria_precios_id' => 2, 'categoria_precio' => 'A', 'ajustada' => true],
            $resultado
        );
    }

    private function candidato(
        int $productoId,
        int $clienteCategoriaEscalaId,
        int $categoriaPrecioId,
        string $categoriaPrecio,
        float $precioA
    ): object {
        return (object) [
            'producto_id' => $productoId,
            'cliente_categoria_escala_id' => $clienteCategoriaEscalaId,
            'categoria_precios_id' => $categoriaPrecioId,
            'categoria_precio' => $categoriaPrecio,
            'precio_base_venta' => $precioA,
            'precio_a' => $precioA,
            'precio_b' => $precioA,
            'precio_c' => $precioA,
            'precio_d' => $precioA,
        ];
    }
}
