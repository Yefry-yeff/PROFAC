<?php

namespace Tests\Unit\Services\Inventario;

use App\Services\Inventario\DistribuidorLotesAjuste;
use DomainException;
use PHPUnit\Framework\TestCase;

class DistribuidorLotesAjusteTest extends TestCase
{
    public function test_ignores_non_positive_lots(): void
    {
        $loteNegativo = (object) ['id' => 1, 'cantidad_disponible' => -10];
        $loteCero = (object) ['id' => 2, 'cantidad_disponible' => 0];
        $lotePositivo = (object) ['id' => 3, 'cantidad_disponible' => 100];

        $resultado = (new DistribuidorLotesAjuste())->distribuir(
            collect([$loteNegativo, $loteCero, $lotePositivo]),
            5
        );

        $this->assertCount(1, $resultado);
        $this->assertSame($lotePositivo, $resultado[0]['lote']);
        $this->assertSame(5, $resultado[0]['unidades']);
    }

    public function test_distributes_requested_units_between_positive_lots(): void
    {
        $primerLote = (object) ['id' => 1, 'cantidad_disponible' => 3];
        $segundoLote = (object) ['id' => 2, 'cantidad_disponible' => 10];

        $resultado = (new DistribuidorLotesAjuste())->distribuir(
            collect([$primerLote, $segundoLote]),
            5
        );

        $this->assertSame([3, 2], array_column($resultado, 'unidades'));
    }

    public function test_rejects_insufficient_positive_stock(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('La existencia positiva disponible no es suficiente');

        (new DistribuidorLotesAjuste())->distribuir(
            collect([
                (object) ['id' => 1, 'cantidad_disponible' => -10],
                (object) ['id' => 2, 'cantidad_disponible' => 4],
            ]),
            5
        );
    }
}
