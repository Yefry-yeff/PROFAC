<?php

namespace App\Exports\Comisiones;

use App\Support\Comisiones\ProyeccionEspecial15;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProyeccionEspecial15AuditoriaSheet implements FromArray, WithTitle, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $rows;
    protected string $periodo;
    protected string $generadoPor;

    public function __construct(array $rows, string $periodo, string $generadoPor)
    {
        $this->rows = $rows;
        $this->periodo = $periodo;
        $this->generadoPor = $generadoPor;
    }

    public function title(): string
    {
        return 'Auditoría Facturas';
    }

    public function array(): array
    {
        $facturas = [];

        foreach ($this->rows as $item) {
            $row = (array) $item;
            $key = (int) ($row['factura_id'] ?? 0);
            $calculo = ProyeccionEspecial15::calcular($row);

            if (!isset($facturas[$key])) {
                $facturas[$key] = [
                    'fecha_pago' => (string) ($row['fecha_pago'] ?? ''),
                    'factura' => (string) ($row['factura'] ?? ''),
                    'cliente' => (string) ($row['cliente'] ?? ''),
                    'roles' => [],
                    'usuarios' => [],
                    'regla' => $calculo['regla'],
                    'porcentajes' => [],
                    'lineas' => 0,
                    'base' => 0.0,
                    'comision_normal' => 0.0,
                    'retencion_mora' => 0.0,
                    'comision_aplicada' => 0.0,
                ];
            }

            $porcentaje = (float) $calculo['porcentaje'];
            $facturas[$key]['porcentajes'][number_format($porcentaje, 2, '.', '')] = true;
            $facturas[$key]['roles'][(string) ($row['rol_nombre'] ?? '')] = true;
            $facturas[$key]['usuarios'][(string) ($row['usuario'] ?? '')] = true;
            $facturas[$key]['lineas']++;
            $facturas[$key]['base'] += (float) ($row['base_comisionable'] ?? 0);
            $facturas[$key]['comision_normal'] += (float) ($row['comision_proyectada'] ?? 0);
            $facturas[$key]['retencion_mora'] += (float) ($row['retencion_mora'] ?? 0);
            $facturas[$key]['comision_aplicada'] += (float) $calculo['comision'];
        }

        uasort($facturas, function (array $left, array $right) {
            return [$left['fecha_pago'], $left['factura']]
                <=> [$right['fecha_pago'], $right['factura']];
        });

        $out = [
            ['AUDITORÍA DE FACTURAS - REGLA ESPECIAL 15%', '', '', '', '', '', '', '', '', '', '', ''],
            ['Período: ' . $this->periodo . '     Generado por: ' . $this->generadoPor, '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            [
                'FECHA PAGO', 'FACTURA', 'CLIENTE', 'ROL COMISIÓN', 'USUARIO',
                'REGLA APLICADA', '% APLICADO', 'LÍNEAS', 'BASE COMISIONABLE',
                'COMISIÓN NORMAL (NETA)', 'RETENCIÓN MORA NORMAL', 'COMISIÓN APLICADA',
            ],
        ];

        $totales = ['base' => 0.0, 'normal' => 0.0, 'mora' => 0.0, 'aplicada' => 0.0];
        $conteoReglas = ['FIJO 15%' => 0, 'ESCALA NORMAL' => 0];

        foreach ($facturas as $factura) {
            $porcentajes = implode(', ', array_keys($factura['porcentajes'])) . '%';
            $out[] = [
                $factura['fecha_pago'],
                $factura['factura'],
                $factura['cliente'],
                implode(', ', array_filter(array_keys($factura['roles']))),
                implode(', ', array_filter(array_keys($factura['usuarios']))),
                $factura['regla'],
                $porcentajes,
                $factura['lineas'],
                round($factura['base'], 4),
                round($factura['comision_normal'], 4),
                round($factura['retencion_mora'], 4),
                round($factura['comision_aplicada'], 4),
            ];

            $conteoReglas[$factura['regla']] = ($conteoReglas[$factura['regla']] ?? 0) + 1;
            $totales['base'] += $factura['base'];
            $totales['normal'] += $factura['comision_normal'];
            $totales['mora'] += $factura['retencion_mora'];
            $totales['aplicada'] += $factura['comision_aplicada'];
        }

        $out[] = [
            'TOTALES: ' . count($facturas) . ' facturas | Fijo 15%: ' . $conteoReglas['FIJO 15%']
                . ' | Escala normal: ' . $conteoReglas['ESCALA NORMAL'],
            '', '', '', '', '', '', '',
            round($totales['base'], 4),
            round($totales['normal'], 4),
            round($totales['mora'], 4),
            round($totales['aplicada'], 4),
        ];

        return $out;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, 'B' => 24, 'C' => 38, 'D' => 22,
            'E' => 25, 'F' => 18, 'G' => 18, 'H' => 10,
            'I' => 20, 'J' => 22, 'K' => 22, 'L' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                $sheet->freezePane('A5');
                $sheet->setAutoFilter('A4:L4');

                $sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CC6218']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A2:L2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2:L2')->getFont()->setItalic(true)->setSize(9);
                $sheet->getStyle('A4:L4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                for ($row = 5; $row < $lastRow; $row++) {
                    $background = $row % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("A{$row}:L{$row}")->getFill()->getStartColor()->setRGB($background);
                    $sheet->getStyle("A{$row}:L{$row}")->getBorders()->getBottom()
                        ->setBorderStyle(Border::BORDER_HAIR)->getColor()->setRGB('E2E8F0');

                    $rule = (string) $sheet->getCell("F{$row}")->getValue();
                    $ruleColor = $rule === 'FIJO 15%' ? 'DCFCE7' : 'DBEAFE';
                    $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("F{$row}")->getFill()->getStartColor()->setRGB($ruleColor);
                    $sheet->getStyle("F{$row}")->getFont()->setBold(true);
                }

                $currency = '"L." #,##0.00';
                foreach (['I', 'J', 'K', 'L'] as $column) {
                    $sheet->getStyle("{$column}5:{$column}{$lastRow}")->getNumberFormat()->setFormatCode($currency);
                    $sheet->getStyle("{$column}5:{$column}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                $sheet->getStyle("A{$lastRow}:L{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FED7AA']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9A3412']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(32);
            },
        ];
    }
}