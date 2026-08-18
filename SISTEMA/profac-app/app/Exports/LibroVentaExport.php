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
 * Columnas (A–K, 11 cols):
 *  A Asesor Comercial  B Teleasesor  C Cliente  D Factura  E Exonerado
 *  F Gravado  G Exento  H Subtotal  I ISV  J Total  K Fecha Compra
 */
class LibroVentaExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;

    const LAST_COL  = 'K';
    const COL_COUNT = 11;

    public function __construct($data, string $fechaInicio, string $fechaFinal)
    {
        $this->data       = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, // Asesor Comercial
            'B' => 22, // Teleasesor
            'C' => 35, // Cliente
            'D' => 22, // Factura
            'E' => 14, // Exonerado
            'F' => 14, // Gravado
            'G' => 14, // Exento
            'H' => 14, // Subtotal
            'I' => 12, // ISV
            'J' => 14, // Total
            'K' => 20, // Fecha Venta
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
            'ASESOR COMERCIAL', 'TELEASESOR', 'CLIENTE', 'FACTURA',
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
                $r['ASESOR_COMERCIAL'] ?? '',
                $r['TELEASESOR']       ?? '',
                $r['CLIENTE']          ?? '',
                $r['FACTURA']          ?? '',
                $exon, $grav, $exen, $sub, $isv, $total,
                $fechaVal,
            ];
        }

        // Fila de totales
        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTALES:';
        $tot[4] = round($totExon,  2);
        $tot[5] = round($totGrav,  2);
        $tot[6] = round($totExen,  2);
        $tot[7] = round($totSub,   2);
        $tot[8] = round($totIsv,   2);
        $tot[9] = round($totTotal, 2);
        $out[] = $tot;

        return $out;
    }

    public function styles(Worksheet $sheet)
    {
        // Fusionar celdas de cabecera
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');

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
        $sheet->getStyle('A4:K4')->applyFromArray([
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
                $lpsCol  = ['F', 'G', 'H', 'I', 'J']; // con prefijo L
                $exonCol = 'E'; // sin prefijo

                if ($lastRow >= 5) {
                    $sheet->getStyle("E5:E{$lastRow}")->getNumberFormat()->setFormatCode($numFmt);
                    foreach ($lpsCol as $col) {
                        $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getNumberFormat()->setFormatCode($numFmt);
                    }
                    // Fecha por columna
                    $sheet->getStyle("K5:K{$lastRow}")->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
                }

                // ── Alineación de toda la tabla de datos en bloque ──
                $sheet->getStyle("A5:K{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Asesor, teleasesor y cliente — izquierda (por columna completa)
                $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B5:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C5:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // ── Alto de filas por defecto (evita iterar fila por fila) ──
                $sheet->getDefaultRowDimension()->setRowHeight(15);

                // ── Fila de totales ──
                $sheet->getStyle("A{$lastRow}:K{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '7D3F00']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3E0']],
                ]);
                $sheet->getRowDimension($lastRow)->setRowHeight(18);

                // ── Bordes de toda la tabla en una sola llamada ──
                $tableRange = "A4:K{$lastRow}";
                $sheet->getStyle($tableRange)->getBorders()->applyFromArray([
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E8D5BF']],
                    'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'E07000']],
                ]);
                $sheet->getStyle("A4:K4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('B05000');
            },
        ];
    }
}

