<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Libro de Ventas — exportación optimizada con FromArray.
 *
 * Columnas (A–J, 10 cols):
 *  A Vendedor  B Cliente   C Factura   D Exonerado  E Gravado
 *  F Exento    G Subtotal  H ISV       I Total       J Fecha Compra
 */
class LibroVentaExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;

    const LAST_COL  = 'J';
    const COL_COUNT = 10;

    public function __construct($data, string $fechaInicio, string $fechaFinal)
    {
        $this->data       = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, // Vendedor
            'B' => 35, // Cliente
            'C' => 22, // Factura
            'D' => 14, // Exonerado
            'E' => 14, // Gravado
            'F' => 14, // Exento
            'G' => 14, // Subtotal
            'H' => 12, // ISV
            'I' => 14, // Total
            'J' => 20, // Fecha Venta
        ];
    }

    public function array(): array
    {
        $out = [];

        // Fila 1 — empresa
        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        // Fila 2 — título
        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'LIBRO GENERAL DE VENTAS';
        $out[] = $r2;

        // Fila 3 — rango de fechas
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        // Fila 4 — cabeceras
        $out[] = [
            'VENDEDOR', 'CLIENTE', 'FACTURA',
            'EXONERADO', 'GRAVADO', 'EXENTO',
            'SUBTOTAL', 'ISV', 'TOTAL', 'FECHA VENTA',
        ];

        // Acumuladores de totales
        $totExon = $totGrav = $totExen = $totSub = $totIsv = $totTotal = 0.0;

        foreach ($this->data as $item) {
            $r = (array) $item;

            $exon  = (float) ($r['EXONERADO']    ?? 0);
            $grav  = (float) ($r['GRAVADO']       ?? 0);
            $exen  = (float) ($r['EXENTO']        ?? 0);
            $sub   = (float) ($r['SUBTOTAL']      ?? 0);
            $isv   = (float) ($r['ISV']           ?? 0);
            $total = (float) ($r['TOTAL']         ?? 0);

            // Convertir fecha a número Excel para formato nativo
            $fechaRaw = $r['FECHA VENTA'] ?? '';
            $fechaVal = '';
            if ($fechaRaw) {
                $ts = strtotime((string) $fechaRaw);
                $fechaVal = $ts !== false ? ExcelDate::PHPToExcel($ts) : $fechaRaw;
            }

            $totExon  += $exon;
            $totGrav  += $grav;
            $totExen  += $exen;
            $totSub   += $sub;
            $totIsv   += $isv;
            $totTotal += $total;

            $out[] = [
                $r['VENDEDOR'] ?? '',
                $r['CLIENTE']  ?? '',
                $r['FACTURA']  ?? '',
                $exon, $grav, $exen, $sub, $isv, $total,
                $fechaVal,
            ];
        }

        // Fila de totales
        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTALES:';
        $tot[3] = round($totExon,  2);
        $tot[4] = round($totGrav,  2);
        $tot[5] = round($totExen,  2);
        $tot[6] = round($totSub,   2);
        $tot[7] = round($totIsv,   2);
        $tot[8] = round($totTotal, 2);
        $out[] = $tot;

        return $out;
    }

    public function styles(Worksheet $sheet)
    {
        // Fusionar celdas de cabecera
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        // Fila 1
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '7D3F00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Fila 2
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'E07000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Fila 3
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '404040']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // Fila 4 — cabeceras
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E07000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastRow  = $sheet->getHighestRow();
                $dataEnd  = $lastRow - 1; // última fila de datos (antes de totales)

                if ($lastRow < 5) {
                    return;
                }

                // ── Formato numérico por columna (una sola llamada por columna) ──
                $numFmt  = '#,##0.00';
                $lpsCol  = ['E', 'F', 'G', 'H', 'I']; // con prefijo L
                $exonCol = 'D'; // sin prefijo

                if ($lastRow >= 5) {
                    $sheet->getStyle("D5:D{$lastRow}")->getNumberFormat()->setFormatCode($numFmt);
                    foreach ($lpsCol as $col) {
                        $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getNumberFormat()->setFormatCode($numFmt);
                    }
                    // Fecha por columna
                    $sheet->getStyle("J5:J{$lastRow}")->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
                }

                // ── Alineación de toda la tabla de datos en bloque ──
                $sheet->getStyle("A5:J{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Vendedor y Cliente — izquierda (por columna completa)
                $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B5:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // ── Alto de filas por defecto (evita iterar fila por fila) ──
                $sheet->getDefaultRowDimension()->setRowHeight(15);

                // ── Fila de totales ──
                $sheet->getStyle("A{$lastRow}:J{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '7D3F00']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3E0']],
                ]);
                $sheet->getRowDimension($lastRow)->setRowHeight(18);

                // ── Bordes de toda la tabla en una sola llamada ──
                $tableRange = "A4:J{$lastRow}";
                $sheet->getStyle($tableRange)->getBorders()->applyFromArray([
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E8D5BF']],
                    'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'E07000']],
                ]);
                $sheet->getStyle("A4:J4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('B05000');
            },
        ];
    }
}

