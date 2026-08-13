<?php

namespace App\Exports\Comisiones;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FacturasProyectadasExport implements FromArray, WithColumnWidths, WithEvents, WithStrictNullComparison, WithStyles
{
    private array $facturas;
    private string $periodo;
    private string $generadoPor;

    public function __construct(array $facturas, string $periodo, string $generadoPor)
    {
        $this->facturas = $facturas;
        $this->periodo = $periodo;
        $this->generadoPor = $generadoPor;
    }

    public function array(): array
    {
        $rows = [
            ['DISTRIBUCIONES VALENCIA | RTN: 08011986138652', '', '', '', '', '', '', '', '', '', '', ''],
            ['FACTURAS PROYECTADAS - DETALLE FISCAL', '', '', '', '', '', '', '', '', '', '', ''],
            ['Periodo: ' . $this->periodo . ' | Generado por: ' . $this->generadoPor, '', '', '', '', '', '', '', '', '', '', ''],
            ['ID FACTURA', 'FACTURA', 'FECHA CIERRE (ULTIMO PAGO)', 'POLITICA COMISION', 'ESTADO COMISIÓN', 'SUBTOTAL', 'ISV', 'TOTAL', 'DESCUENTO', 'CLIENTE', 'CAI', 'BANCO COBRO CIERRE'],
        ];

        $subtotal = 0.0;
        $isv = 0.0;
        $total = 0.0;
        $descuento = 0.0;

        foreach ($this->facturas as $factura) {
            $subtotal += (float) $factura['subtotal'];
            $isv += (float) $factura['isv'];
            $total += (float) $factura['total'];
            $descuento += (float) $factura['descuento'];

            $rows[] = [
                (int) $factura['factura_id'],
                $factura['numero_factura'],
                $factura['fecha_cierre'],
                $factura['politica_comision'],
                $factura['estado_comision'],
                (float) $factura['subtotal'],
                (float) $factura['isv'],
                (float) $factura['total'],
                (float) $factura['descuento'],
                $factura['cliente'],
                $factura['cai'],
                $factura['banco_cierre'],
            ];
        }

        $rows[] = ['TOTALES', '', '', '', '', $subtotal, $isv, $total, $descuento, '', '', ''];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13, 'B' => 20, 'C' => 25, 'D' => 26, 'E' => 22, 'F' => 16,
            'G' => 16, 'H' => 16, 'I' => 16, 'J' => 38, 'K' => 24, 'L' => 34,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1F3864');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('047857');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A4:L4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:L4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('047857');
        $sheet->getStyle('A4:L4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->setAutoFilter('A4:L4');
                $sheet->freezePane('A5');
                $sheet->getStyle("F5:I{$lastRow}")->getNumberFormat()->setFormatCode('"L." #,##0.00');
                for ($row = 5; $row < $lastRow; $row++) {
                    $estado = (string) ($sheet->getCell("E{$row}")->getValue() ?? '');
                    $sheet->getStyle("E{$row}")->getFont()->setBold(true)->getColor()
                        ->setRGB($estado === 'COMISIONA' ? '047857' : 'B91C1C');
                }
                $sheet->getStyle("A{$lastRow}:L{$lastRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$lastRow}:L{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
            },
        ];
    }
}