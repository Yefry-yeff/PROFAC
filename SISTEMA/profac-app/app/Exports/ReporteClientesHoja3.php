<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReporteClientesHoja3 implements FromArray, WithTitle, WithStyles, WithDrawings, WithEvents
{
    protected $rows;
    protected $usuario;
    const LAST_COL = 'F';

    public function __construct($rows, $usuario = 'Sistema')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
    }
    public function title(): string { return 'Corporativo (B)'; }

    public function array(): array
    {
        $out = [];

        // Row 1: company name
        $out[] = ['DISTRIBUCIONES VALENCIA S.A. DE C.V.   |   RTN: 08011986138652', '', '', '', '', ''];

        // Row 2: report title
        $out[] = ['REPORTE DE CLIENTES CORPORATIVO (B)', '', '', '', '', ''];

        // Row 3: date + user
        $out[] = ['Generado: ' . now()->format('d/m/Y H:i') . '   |   Descargado por: ' . $this->usuario, '', '', '', '', ''];

        // Row 4: column headers
        $out[] = ['#', 'VENDEDOR', 'CLIENTE', 'CÓDIGO', 'PLAZO CRÉDITO', 'ESTADO'];

        foreach ($this->rows as $r) {
            $out[] = [
                $r->item,
                $r->vendedor,
                $r->cliente,
                $r->codigo,
                $r->plazo_credito > 0 ? (int) $r->plazo_credito : '',
                $r->estado_cliente,
            ];
        }

        return $out;
    }

    public function drawings()
    {
        $d = new Drawing();
        $d->setName('Logo Valencia');
        $d->setPath(public_path('img/membrete/Logo3.png'));
        $d->setHeight(55);
        $d->setCoordinates('A1');
        $d->setOffsetX(4)->setOffsetY(4);
        return $d;
    }

    public function styles(Worksheet $sheet)
    {
        $lc = self::LAST_COL;

        $sheet->mergeCells("A1:{$lc}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(60);

        $sheet->mergeCells("A2:{$lc}2");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('1ab394');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $sheet->mergeCells("A3:{$lc}3");
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(16);

        $sheet->getStyle("A4:{$lc}4")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("A4:{$lc}4")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lc}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1ab394');
        $sheet->getStyle("A4:{$lc}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(22);

        foreach (['A','B','C','D','E','F'] as $c) { $sheet->getColumnDimension($c)->setAutoSize(true); }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lc      = self::LAST_COL;

                for ($row = 5; $row <= $lastRow; $row++) {
                    $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Days format
                    $val = $sheet->getCell("E{$row}")->getValue();
                    if ($val !== '' && $val !== null) {
                        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0" días"');
                    }

                    $bg = ($row % 2 === 0) ? 'E8F7F5' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                    $sheet->getRowDimension($row)->setRowHeight(15);
                }

                $range = "A4:{$lc}{$lastRow}";
                $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B2DDD5');
                $sheet->getStyle($range)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('1ab394');
                $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0d8a77');

                $fd = new Drawing();
                $fd->setPath(public_path('img/membrete/Logo3.png'));
                $fd->setHeight(45);
                $fd->setCoordinates('B' . ($lastRow + 3));
                $fd->setOffsetX(10)->setOffsetY(5);
                $fd->setWorksheet($sheet);
            },
        ];
    }
}
