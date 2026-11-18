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
                'monto_recibido' => '316.93',
                'comision_cobrada' => '9.51',
                'comision_banco' => '8.49',
                'monto_cobrado' => '316.93',
                'monto_movimiento' => '317.95',
                'retencion_sin_aplicar' => '40.00',
                'otro' => '1.02',
                'gestor_entrega' => 'Gestor Uno',
            ],
            [
                'banco' => 'PROMERICA PAPERLAND',
                'cuenta_banco' => '10000001211337',
                'monto_recibido' => '100.00',
                'comision_cobrada' => '3.00',
                'comision_banco' => '2.68',
                'pago_porcentaje' => '5.53',
                'monto_movimiento' => '94.79',
                'monto_cobrado' => '100.00',
                'otro' => '0.00',
            ],
        ], '2026-01-01', '2026-01-31');
        $sheets = $export->sheets();

        $this->assertSame('Todos', $sheets[0]->title());
        $todosRows = $sheets[0]->array();
        $this->assertCount(21, $todosRows[3]);
        $this->assertSame('GESTOR DE ENTREGAS', $todosRows[3][6]);
        $this->assertSame('Gestor Uno', $todosRows[4][6]);
        $this->assertSame('OTRO', $todosRows[3][20]);
        $this->assertEquals(1.02, $todosRows[4][20]);
        $this->assertEquals(1.02, end($todosRows)[20]);
        $this->assertEquals(412.74, end($todosRows)[8]);
        $this->assertCount(3, $sheets);
        $this->assertSame('Promerica - 80000000578176', $sheets[1]->title());
        $this->assertSame('PROMERICA PAPERLAND - 100000012', $sheets[2]->title());
        $promericaRows = $sheets[1]->array();
        $this->assertCount(24, $promericaRows[3]);
        $this->assertSame('N° FACTURA', $promericaRows[3][7]);
        $this->assertSame('MOVIMIENTO', $promericaRows[3][8]);
        $this->assertSame('COMISIÓN COBRADA (3%)', $promericaRows[3][9]);
        $this->assertSame('COMISIÓN DEL BANCO (2.6%)', $promericaRows[3][10]);
        $this->assertSame('MONTO MOVIMIENTO', $promericaRows[3][11]);
        $this->assertEquals([316.93, 9.51, 8.49, 317.95], array_slice($promericaRows[4], 8, 4));
        $this->assertSame('RETENCIÓN SIN APLICAR', $promericaRows[3][12]);
        $this->assertEquals(40, $promericaRows[4][12]);
        $this->assertEquals(1.02, $promericaRows[4][23]);
        $this->assertEquals(1.02, end($promericaRows)[23]);
        $this->assertEquals(317.95, $todosRows[4][8]);

        $paperlandRows = $sheets[2]->array();
        $this->assertCount(25, $paperlandRows[3]);
        $this->assertSame('RETENCIÓN SIN APLICAR', $paperlandRows[3][13]);
        $this->assertSame(['COMISIÓN DEL BANCO (2.6%)', 'PAGO PORCENTAJE', 'MONTO MOVIMIENTO'], array_slice($paperlandRows[3], 10, 3));
        $this->assertEquals([100.00, 3.00, 2.68, 5.53, 94.79], array_slice($paperlandRows[4], 8, 5));
        $this->assertEquals([5.53, 94.79], array_slice(end($paperlandRows), 11, 2));

        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet();
        $accountCell = $worksheet->getCell('P5');
        $sheets[1]->bindValue($accountCell, '80000000578176');
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $accountCell->getDataType());
        $this->assertSame('80000000578176', $accountCell->getValue());
        $todosAccountCell = $worksheet->getCell('M5');
        $sheets[0]->bindValue($todosAccountCell, '80000000578176');
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $todosAccountCell->getDataType());
        $this->assertSame('80000000578176', $todosAccountCell->getValue());
        $paperlandAccountCell = $worksheet->getCell('Q5');
        $sheets[2]->bindValue($paperlandAccountCell, '10000001211337');
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $paperlandAccountCell->getDataType());
        $this->assertSame('10000001211337', $paperlandAccountCell->getValue());
    }

    public function test_promerica_net_movement_uses_principal_plus_three_percent_less_bank_fee(): void
    {
        $promerica = ComisionDepositoPromerica::desgloseLibroCobros(31693, false);
        $paperland = ComisionDepositoPromerica::desgloseLibroCobros(31693, true);

        $this->assertSame(951, $promerica['comision_cobrada']);
        $this->assertSame(849, $promerica['comision_banco']);
        $this->assertSame(31795, $promerica['monto_movimiento']);
        $this->assertSame(31242, $paperland['monto_movimiento']);
    }
}
