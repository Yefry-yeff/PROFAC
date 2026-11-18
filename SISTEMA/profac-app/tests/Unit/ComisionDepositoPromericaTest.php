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

    public function test_direct_promerica_payment_adds_commission_to_the_amount_applied_to_debt(): void
    {
        $promerica = ComisionDepositoPromerica::desgloseMontoAplicadoCentavos(117299, true);
        $otraCuenta = ComisionDepositoPromerica::desgloseMontoAplicadoCentavos(117299, false);

        $this->assertSame([
            'monto_recibido' => 120818,
            'comision_cobrada' => 3519,
            'monto_aplicado' => 117299,
        ], $promerica);
        $this->assertSame([
            'monto_recibido' => 117299,
            'comision_cobrada' => 0,
            'monto_aplicado' => 117299,
        ], $otraCuenta);
    }

    public function test_excel_adds_promerica_sheet_and_other_column_to_all_sheet(): void
    {
        $export = new LibroCobrosExport([
            [
                'banco' => 'Promerica',
                'cuenta_banco' => '80000000578176',
                'monto_recibido' => '1172.99',
                'comision_cobrada' => '35.19',
                'monto_cobrado' => '1172.99',
                'monto_movimiento' => '1177.68',
                'otro' => '4.69',
                'gestor_entrega' => 'Gestor Uno',
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
        $todosRows = $sheets[0]->array();
        $this->assertCount(20, $todosRows[3]);
        $this->assertSame('GESTOR DE ENTREGAS', $todosRows[3][6]);
        $this->assertSame('Gestor Uno', $todosRows[4][6]);
        $this->assertSame('OTRO', $todosRows[3][19]);
        $this->assertEquals(4.69, $todosRows[4][19]);
        $this->assertEquals(4.69, $todosRows[count($todosRows) - 1][19]);
        $this->assertCount(3, $sheets);
        $this->assertSame('Promerica - 80000000578176', $sheets[1]->title());
        $this->assertSame('PROMERICA PAPERLAND - 100000012', $sheets[2]->title());
        $promericaRows = $sheets[1]->array();
        $this->assertCount(23, $promericaRows[3]);
        $this->assertSame('N° FACTURA', $promericaRows[3][7]);
        $this->assertSame('MOVIMIENTO', $promericaRows[3][8]);
        $this->assertSame('COMISIÓN COBRADA (3%)', $promericaRows[3][9]);
        $this->assertSame('COMISIÓN DEL BANCO (2.6%)', $promericaRows[3][10]);
        $this->assertSame('MONTO MOVIMIENTO', $promericaRows[3][11]);
        $this->assertEquals(1172.99, $promericaRows[4][8]);
        $this->assertEquals(35.19, $promericaRows[4][9]);
        $this->assertEquals(30.5, $promericaRows[4][10]);
        $this->assertEquals(1177.68, $promericaRows[4][11]);
        $this->assertEquals(4.69, $promericaRows[4][22]);
        $this->assertEquals(1177.68, $sheets[0]->array()[4][8]);
        $this->assertSame(20, count($sheets[2]->array()[3]));

        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet();
        $accountCell = $worksheet->getCell('O5');
        $sheets[1]->bindValue($accountCell, '80000000578176');
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $accountCell->getDataType());
        $this->assertSame('80000000578176', $accountCell->getValue());
        $todosAccountCell = $worksheet->getCell('L5');
        $sheets[0]->bindValue($todosAccountCell, '80000000578176');
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $todosAccountCell->getDataType());
        $this->assertSame('80000000578176', $todosAccountCell->getValue());
    }

    public function test_promerica_net_movement_uses_principal_plus_three_percent_less_bank_fee(): void
    {
        $principal = ComisionDepositoPromerica::centavos('1172.99');
        $commission = ComisionDepositoPromerica::centavos('35.19');
        $bankFee = ComisionDepositoPromerica::comisionBancoCentavos($principal);

        $this->assertSame(3050, $bankFee);
        $this->assertSame(117768, $principal + $commission - $bankFee);
    }
}
