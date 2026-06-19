<?php

namespace App\Exports\Comisiones;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ConciliacionEmpleadoResumenSheet implements FromArray, WithTitle, WithEvents, WithStrictNullComparison
{
    protected array $data;
    protected string $periodo;
    protected string $periodoLabel;
    protected int $sheetIndex;

    public function __construct(array $data, string $periodo, string $periodoLabel, int $sheetIndex = 1)
    {
        $this->data = $data;
        $this->periodo = $periodo;
        $this->periodoLabel = $periodoLabel;
        $this->sheetIndex = $sheetIndex;
    }

    public function title(): string
    {
        $base = (string) ($this->data['empleado'] ?? ('Empleado ' . $this->sheetIndex));
        $clean = str_replace(['\\', '/', '*', '?', ':', '[', ']'], '', $base);
        $clean = trim((string) $clean);

        if ($clean === '') {
            $clean = 'Empleado ' . $this->sheetIndex;
        }

        if (mb_strlen($clean) > 31) {
            $clean = mb_substr($clean, 0, 31);
        }

        return $clean;
    }

    public function array(): array
    {
        $periodoUpper = mb_strtoupper((string) $this->periodoLabel, 'UTF-8');
        $mesSolo = mb_strtoupper((string) explode(' ', (string) $this->periodoLabel)[0], 'UTF-8');
        $empleado = mb_strtoupper((string) ($this->data['empleado'] ?? 'EMPLEADO'), 'UTF-8');

        $fechaConciliacion = !empty($this->data['fecha_conciliacion'])
            ? Carbon::parse($this->data['fecha_conciliacion'])->format('d/m/Y H:i')
            : 'No registrada';

        $rows = [];

        $rows[] = ['DISTRIBUCIONES VALENCIA', '', '', '', ''];
        $rows[] = ['TEGUCIGALPA, M.D.C.', '', '', '', ''];
        $rows[] = ['Col. Godoy una cuadra arriba de fuerza aerea', '', '', '', ''];
        $rows[] = ['Tels.: (504)2234-9877 / 22349914', '', '', '', ''];
        $rows[] = ['E-mail: seyli.torres@distribucionesvalencia.hn  R.T.N. 08011986138652', '', '', '', ''];
        $rows[] = ['', '', '', '', ''];

        $rows[] = ['NOMBRE: ' . $empleado, '', '', '', 'CUENTA BANCARIA'];
        $rows[] = ['MES DE COMISION: ' . $periodoUpper, '', '', '', ''];
        $rows[] = ['FACTURAS COBRADAS ' . $mesSolo . ' COMISIONABLES', '', '', '', ''];
        $rows[] = ['L', '', (float) ($this->data['total_cobrado'] ?? 0), '', ''];
        $rows[] = ['BASE COMISIONABLE ' . $mesSolo, 'L', (float) ($this->data['total_cobrado'] ?? 0), '', ''];
        $rows[] = ['COMISION A PAGAR', 'L', (float) ($this->data['comision_bruta'] ?? 0), '', ''];
        $rows[] = ['DEDUCCION RETENCION EN LA FUENTE', 'L', (float) ($this->data['retencion_fuente'] ?? 0), '', ''];
        $rows[] = ['TOTAL A PAGAR', 'L', (float) ($this->data['comision_neta'] ?? 0), '', ''];
        $rows[] = ['Conciliado por: ' . (string) ($this->data['conciliado_por'] ?? 'No registrado'), '', 'Fecha conciliacion: ' . $fechaConciliacion, '', ''];
        $rows[] = ['MESES COBRADOS EN ' . $periodoUpper, '', '', '', ''];
        $rows[] = ['Mes cobrado', 'Cantidad de facturas', 'Total cobrado por abonos (L.)', '', ''];

        $meses = $this->data['meses_cobrados'] ?? [];
        foreach ($meses as $mes) {
            $mesLabel = (string) ($mes['mes_label'] ?? '');
            $mesTxt = $mesLabel !== '' ? ('Facturas de ' . $mesLabel . ' cobradas') : 'Facturas cobradas';
            $rows[] = [
                $mesTxt,
                (int) ($mes['cantidad_facturas'] ?? 0),
                (float) ($mes['total_cobrado'] ?? 0),
                '',
                '',
            ];
        }

        $rows[] = [
            'TOTAL COBROS',
            (int) ($this->data['total_facturas'] ?? 0),
            (float) ($this->data['total_cobrado'] ?? 0),
            '',
            '',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $logoPath = public_path('img/LOGO_VALENCIA.jpg');

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');
                $sheet->mergeCells('A5:E5');
                $sheet->mergeCells('A7:C7');
                $sheet->mergeCells('D7:E7');
                $sheet->mergeCells('A8:C8');
                $sheet->mergeCells('D8:E8');
                $sheet->mergeCells('A9:C9');
                $sheet->mergeCells('D9:E14');
                $sheet->mergeCells('A16:E16');

                $sheet->getStyle('A1:E5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E2E8F0']]],
                ]);

                $sheet->getStyle('A3:E5')->getFont()->setSize(10);
                $sheet->getStyle('A1:E2')->getFont()->setSize(14);
                $sheet->getStyle('A5:E5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
                $sheet->getStyle('A5:E5')->getBorders()->getBottom()->getColor()->setRGB('CBD5E1');

                $sheet->getStyle('A7:E14')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E97824']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9A3412']]],
                ]);

                $sheet->getStyle('A7:E9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CC6218']],
                ]);

                $sheet->getStyle('A7:E8')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('E7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D7:E7')->getAlignment()->setWrapText(true);

                // Caja de escritura manual para cuenta bancaria.
                $sheet->getStyle('D8:E8')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                    'font' => ['bold' => false, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9A3412']]],
                ]);
                $sheet->setCellValue('D8', 'Ceunta Bancaria: ____________________');
                $sheet->getStyle('D8')->getFont()->setItalic(true);

                $sheet->getStyle('A9:C9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A10:C14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('B10:B14')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('C10:C14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('C10:C14')->getNumberFormat()->setFormatCode('"L "#,##0.00');

                $sheet->getStyle('A14:C14')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                ]);

                $sheet->getStyle('D9:E14')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF7ED']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9A3412']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle('A7:E14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
                $sheet->getStyle('A7:E14')->getBorders()->getOutline()->getColor()->setRGB('7C2D12');

                $sheet->getStyle('A15:E15')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->getStyle('A16:E16')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A17:C17')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                if ($lastRow >= 18) {
                    $sheet->getStyle('A18:C' . $lastRow)->applyFromArray([
                        'font' => ['size' => 10],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);

                    for ($r = 18; $r <= $lastRow - 1; $r++) {
                        $bg = ($r % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
                        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()->getStartColor()->setRGB($bg);
                    }
                }

                $sheet->getStyle('B18:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C18:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('C18:C' . $lastRow)->getNumberFormat()->setFormatCode('"L "#,##0.00');

                $sheet->getStyle('A' . $lastRow . ':E' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '115E59']]],
                ]);

                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo Valencia');
                    $drawing->setDescription('Logo Distribuciones Valencia');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(132);
                    $drawing->setCoordinates('D9');
                    $drawing->setOffsetX(16);
                    $drawing->setOffsetY(10);
                    $drawing->setWorksheet($sheet);
                }

                foreach (['A' => 42, 'B' => 16, 'C' => 25, 'D' => 19, 'E' => 19] as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                $sheet->getRowDimension(1)->setRowHeight(23);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(19);
                $sheet->getRowDimension(5)->setRowHeight(19);
                $sheet->getRowDimension(7)->setRowHeight(22);
                $sheet->getRowDimension(8)->setRowHeight(22);
                for ($r = 9; $r <= 14; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(26);
                }

                $sheet->freezePane('A18');
            },
        ];
    }
}
