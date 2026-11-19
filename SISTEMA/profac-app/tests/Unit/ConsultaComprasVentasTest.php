<?php

namespace Tests\Unit;

use App\Services\Auditoria\ConsultaComprasVentas;
use PHPUnit\Framework\TestCase;

class ConsultaComprasVentasTest extends TestCase
{
    public function test_normaliza_filtros_permitidos(): void
    {
        $filtros = (new ConsultaComprasVentas)->filtros([
            'desde' => '2022-09-01',
            'hasta' => '2026-11-19',
            'tipo' => 'VENTA',
            'clasificacion' => 'B',
            'estado' => 'ANULADO',
            'producto' => '  Papel bond  ',
            'documento' => ' 000-001-01-00012345 ',
            'lote' => ' 456 ',
        ]);

        $this->assertSame([
            'desde' => '2022-09-01',
            'hasta' => '2026-11-19',
            'tipo' => 'VENTA',
            'clasificacion' => 'B',
            'estado' => 'ANULADO',
            'producto' => 'Papel bond',
            'documento' => '000-001-01-00012345',
            'lote' => '456',
        ], $filtros);
    }

    public function test_descarta_opciones_y_fechas_invalidas(): void
    {
        $filtros = (new ConsultaComprasVentas)->filtros([
            'desde' => '01/09/2022',
            'hasta' => 'todo',
            'tipo' => 'AJUSTE',
            'clasificacion' => 'C',
            'estado' => 'PENDIENTE',
        ]);

        $this->assertNull($filtros['desde']);
        $this->assertNull($filtros['hasta']);
        $this->assertNull($filtros['tipo']);
        $this->assertNull($filtros['clasificacion']);
        $this->assertNull($filtros['estado']);
    }
}
