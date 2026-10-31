<?php

namespace Tests\Unit;

use App\Exports\LibroCobrosExport;
use App\Services\ComisionDepositoPromerica;
use PHPUnit\Framework\TestCase;

class ComisionDepositoPromericaTest extends TestCase
{
    public function test_promerica_commission_is_rounded_to_cents_and_other_banks_are_unchanged(): void
    {
        $promerica = ComisionDepositoPromerica::desgloseCentavos(30000, true);
        $otraCuenta = ComisionDepositoPromerica::desgloseCentavos(30000, false);

        $this->assertSame(['monto_recibido' => 30000, 'comision_cobrada' => 900, 'monto_aplicado' => 29100], $promerica);
        $this->assertSame(['monto_recibido' => 30000, 'comision_cobrada' => 0, 'monto_aplicado' => 30000], $otraCuenta);
        $this->assertSame(780, ComisionDepositoPromerica::comisionBancoCentavos(30000));
    }

    public function test_excel_adds_promerica_sheet_without_changing_all_sheet_shape(): void
    {
        $export = new LibroCobrosExport([
            [
                'banco' => 'Promerica',
                'cuenta_banco' => '80000000578176',
                'monto_recibido' => '300.00',
                'comision_cobrada' => '9.00',
                'monto_cobrado' => '291.00',
                'otro' => '0.00',
            ],
            [
                'banco' => 'PROMERICA PAPERLAND',
                'cuenta_banco' => '10000001211337',
                'monto_recibido' => '100.00',
                'comision_cobrada' => '0.00',
                'monto_cobrado' => '100.00',
                'otro' => '0.00',
            ],
        ], '2026-01-01', '2026-01-31');
        $sheets = $export->sheets();

        $this->assertSame('Todos', $sheets[0]->title());
        $this->assertCount(18, $sheets[0]->array()[3]);
        $this->assertCount(3, $sheets);
        $this->assertSame('Promerica - 80000000578176', $sheets[1]->title());
        $this->assertSame('PROMERICA PAPERLAND - 100000012', $sheets[2]->title());
        $promericaRows = $sheets[1]->array();
        $this->assertCount(22, $promericaRows[3]);
        $this->assertSame('N° FACTURA', $promericaRows[3][6]);
        $this->assertSame('MOVIMIENTO', $promericaRows[3][7]);
        $this->assertSame('COMISIÓN COBRADA (3%)', $promericaRows[3][8]);
        $this->assertSame('COMISIÓN DEL BANCO (2.6%)', $promericaRows[3][9]);
        $this->assertSame('MONTO MOVIMIENTO', $promericaRows[3][10]);
        $this->assertEquals(300.0, $promericaRows[4][7]);
        $this->assertEquals(9.0, $promericaRows[4][8]);
        $this->assertEquals(7.8, $promericaRows[4][9]);
        $this->assertEquals(292.2, $promericaRows[4][10]);
        $this->assertEquals(0.0, $promericaRows[4][21]);
        $this->assertSame(18, count($sheets[2]->array()[3]));
    }
}
