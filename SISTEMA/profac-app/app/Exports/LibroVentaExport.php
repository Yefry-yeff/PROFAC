<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class LibroVentaExport implements FromView, WithStyles, WithDrawings, WithEvents
{
    protected $data;
    protected $fechaInicio;
    protected $fechaFinal;

    public function __construct($data, $fechaInicio, $fechaFinal)
    {
        $this->data = $data;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal = $fechaFinal;
    }

    public function view(): View
    {
        return view('Excel.libroventarep', [
            'data'        => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFinal'  => $this->fechaFinal,
        ]);
    }

    /**
     * Header logo placed at A1 (top-left of the spreadsheet).
     */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo Valencia');
        $drawing->setDescription('Logo Distribuciones Valencia');
        $drawing->setPath(public_path('img/membrete/Logo3.png'));
        $drawing->setHeight(65);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(4);
        $drawing->setOffsetY(4);

        return $drawing;
    }

    public function styles(Worksheet $sheet)
    {
        // ── Header rows ─────────────────────────────────────────────────────
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        // Row 1 – company name + RTN (right-aligned so text shows beside logo)
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('7D3F00');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setIndent(2);
        $sheet->getRowDimension(1)->setRowHeight(68);

        // Row 2 – report title
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('E07000');
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Row 3 – date range
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true);
        $sheet->getStyle('A3')->getFont()->getColor()->setRGB('404040');
        $sheet->getStyle('A3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Row 4 – column headers (dark blue background, white bold text)
        $sheet->getStyle('A4:J4')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A4:J4')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:J4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E07000');
        $sheet->getStyle('A4:J4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(22);

        // Auto-size all columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastRow  = $sheet->getHighestRow();
                $dataStart = 5; // rows 1-3 = header; row 4 = col headers; row 5+ = data

                // ── Process data rows + totals row ───────────────────────────
                for ($row = $dataStart; $row <= $lastRow; $row++) {

                    // Convert numeric columns D-I to real numbers + apply format
                    foreach (['D', 'E', 'F', 'G', 'H', 'I'] as $col) {
                        $cell  = $sheet->getCell($col . $row);
                        $value = $cell->getValue();
                        if ($value !== null && trim((string) $value) !== '') {
                            $numeric = (float) str_replace(',', '', (string) $value);
                            $cell->setValue($numeric);
                            // EXONERADO keeps plain number; GRAVADO-TOTAL get Lempira symbol
                            $formatCode = ($col === 'D') ? '#,##0.00' : '"L"\  #,##0.00';
                            $sheet->getStyle($col . $row)
                                ->getNumberFormat()
                                ->setFormatCode($formatCode);
                        }
                    }

                    // Convert date column J to proper Excel date with long format
                    $dateCell  = $sheet->getCell('J' . $row);
                    $dateValue = $dateCell->getValue();
                    if ($dateValue !== null && trim((string) $dateValue) !== '') {
                        $timestamp = strtotime((string) $dateValue);
                        if ($timestamp !== false) {
                            $dateCell->setValue(ExcelDate::PHPToExcel($timestamp));
                            $sheet->getStyle('J' . $row)
                                ->getNumberFormat()
                                ->setFormatCode('dd/mm/yyyy hh:mm:ss');
                        }
                    }

                    // Center-align all cells in the row
                    $sheet->getStyle("A{$row}:J{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // VENDEDOR and CLIENTE always left-aligned
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Alternating row colors for data rows (exclude totals row)
                    if ($row < $lastRow) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:J{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFFBEB');
                        } else {
                            $sheet->getStyle("A{$row}:J{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFFFFF');
                        }
                        $sheet->getRowDimension($row)->setRowHeight(16);
                    }
                }

                // ── Totals row styling ───────────────────────────────────────
                $sheet->getStyle("A{$lastRow}:J{$lastRow}")
                    ->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle("A{$lastRow}:J{$lastRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF3E0');
                $sheet->getStyle("A{$lastRow}:J{$lastRow}")
                    ->getFont()->getColor()->setRGB('7D3F00');
                $sheet->getRowDimension($lastRow)->setRowHeight(18);

                // ── Borders for the full table (row 4 → lastRow) ─────────────
                $tableRange = "A4:J{$lastRow}";
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('E8D5BF');

                $sheet->getStyle($tableRange)->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('E07000');

                // Thicker bottom border on column-header row
                $sheet->getStyle("A4:J4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('B05000');

                // ── Footer logo (same image, placed below the totals row) ─────
                $footerRow = $lastRow + 3;
                $footerDrawing = new Drawing();
                $footerDrawing->setName('Footer Logo Valencia');
                $footerDrawing->setDescription('Logo footer');
                $footerDrawing->setPath(public_path('img/membrete/Logo3.png'));
                $footerDrawing->setHeight(60);
                $footerDrawing->setCoordinates('D' . $footerRow);
                $footerDrawing->setOffsetX(10);
                $footerDrawing->setOffsetY(5);
                $footerDrawing->setWorksheet($sheet);
            },
        ];
    }
}

