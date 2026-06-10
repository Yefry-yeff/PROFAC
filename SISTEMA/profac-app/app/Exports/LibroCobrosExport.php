<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Libro de Cobros — Conciliación Bancaria
 *
 * Columnas (A–O, 15 cols):
 *  A  Fecha Pago      B  Cliente         C  Vendedor
 *  D  N° Factura      E  Monto Cobrado   F  Estado
 *  G  Banco           H  Cuenta          I  Observaciones
 *  J  Exonerado       K  Gravado         L  Exento
 *  M  Sub Total       N  ISV             O  Total Factura
 */
class LibroCobrosExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;

    const LAST_COL  = 'O';
    const COL_COUNT = 15;

    public function __construct($data, string $fechaInicio, string $fechaFinal)
    {
        // accept both array and collection of stdClass
        $this->data       = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
    }

    public function array(): array
    {
        $out = [];

        /* ── Fila 1 — empresa ── */
        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        /* ── Fila 2 — título ── */
        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'LIBRO DE COBROS — CONCILIACIÓN BANCARIA';
        $out[] = $r2;

        /* ── Fila 3 — rango ── */
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        /* ── Fila 4 — cabeceras ── */
        $out[] = [
            'FECHA PAGO', 'CLIENTE', 'VENDEDOR',
            'N° FACTURA', 'MONTO COBRADO', 'ESTADO',
            'BANCO', 'CUENTA',
            'OBSERVACIONES', 'EXONERADO', 'GRAVADO',
            'EXENTO', 'SUB TOTAL', 'ISV', 'TOTAL FACTURA',
        ];

        /* ── Filas de datos ── */
        $totCobrado  = 0.0;
        $totExon     = 0.0;
        $totGrav     = 0.0;
        $totExen     = 0.0;
        $totSub      = 0.0;
        $totIsv      = 0.0;
        $totFact     = 0.0;
        $totPagadas  = 0;

        foreach ($this->data as $r) {
            $r = (array) $r;

            $cobrado        = (float)($r['monto_cobrado']         ?? 0);
            $exon           = (float)($r['exonerado']              ?? 0);
            $grav           = (float)($r['gravado']                ?? 0);
            $exen           = (float)($r['excento']                ?? 0);
            $sub            = (float)($r['subtotal']               ?? 0);
            $isv            = (float)($r['isv']                    ?? 0);
            $fact           = (float)($r['total_factura']          ?? 0);
            $estado         = $r['estado_factura']                 ?? '';
            $factura        = $r['factura']                        ?? '';
            $tienePagada    = (int)($r['factura_tiene_pagada']     ?? 1);

            // Si la factura solo tiene abonos (sin pago final anulado), sub/fact = monto cobrado
            $soloAbonos     = ($tienePagada == 0);

            $totCobrado += $cobrado;

            if ($estado === 'PAGADA') {
                $totPagadas++;
                $totExon += $exon;
                $totGrav += $grav;
                $totExen += $exen;
                $totSub  += $sub;
                $totIsv  += $isv;
                $totFact += $fact;
            } elseif ($soloAbonos) {
                // Factura sin pago final: acumular monto cobrado en sub y fact
                $totSub  += $cobrado;
                $totFact += $cobrado;
            }

            $row = [
                $r['fecha_pago']    ?? '',
                $r['cliente']       ?? '',
                $r['vendedor']      ?? '',
                $r['factura']       ?? '',
                $cobrado,
                $estado,
                $r['banco']         ?? '',
                $r['cuenta_banco']  ?? '',
                $r['observaciones'] ?? '',
                $estado === 'PAGADA' ? $exon  : '',
                $estado === 'PAGADA' ? $grav  : '',
                $estado === 'PAGADA' ? $exen  : '',
                $estado === 'PAGADA' ? $sub   : ($soloAbonos ? $cobrado : ''),
                $estado === 'PAGADA' ? $isv   : '',
                $estado === 'PAGADA' ? $fact  : ($soloAbonos ? $cobrado : ''),
            ];
            $out[] = $row;
        }

        /* ── Fila totales ── */
        $totRow = array_fill(0, self::COL_COUNT, '');
        $totRow[0]  = 'TOTALES';
        $totRow[4]  = $totCobrado;          // MONTO COBRADO
        $totRow[9]  = $totExon;             // EXONERADO
        $totRow[10] = $totGrav;             // GRAVADO
        $totRow[11] = $totExen;             // EXENTO
        $totRow[12] = $totSub;              // SUB TOTAL
        $totRow[13] = $totIsv;              // ISV
        $totRow[14] = $totFact;             // TOTAL FACTURA
        $out[] = $totRow;

        return $out;
    }

    public function styles(Worksheet $sheet)
    {
        $lc = self::LAST_COL;
        $sheet->mergeCells("A1:{$lc}1");
        $sheet->mergeCells("A2:{$lc}2");
        $sheet->mergeCells("A3:{$lc}3");

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('e07000');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(16);

        $sheet->getStyle("A4:{$lc}4")->getFont()->setBold(true)->setSize(8)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lc}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('e07000');
        $sheet->getStyle("A4:{$lc}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(28);

        foreach (range('A', self::LAST_COL) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lc      = self::LAST_COL;

                if ($lastRow < 5) return;

                $sheet->setAutoFilter("A4:{$lc}4");
                $sheet->freezePane('A5');

                $currency = '"L" #,##0.00';

                // Alineación base
                $sheet->getStyle("A5:{$lc}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                // Texto izquierda
                foreach (['B','C','D','G','H','I'] as $c) {
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Formato moneda: E, J–O
                foreach (['E','J','K','L','M','N','O'] as $c) {
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getNumberFormat()->setFormatCode($currency);
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Separador visual columna J (detalle factura)
                $sheet->getStyle("J4:J{$lastRow}")->getBorders()->getLeft()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('f2a630');

                // Colorear filas
                for ($row = 5; $row <= $lastRow; $row++) {
                    $estado = (string)($sheet->getCell("F{$row}")->getValue() ?? '');
                    if (strtoupper($estado) === 'TOTALES' || $row === $lastRow) {
                        // Fila totales
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(9)->getColor()->setRGB('7d3f00');
                        $sheet->getRowDimension($row)->setRowHeight(16);
                    } elseif (strtoupper($estado) === 'PAGADA') {
                        $sheet->getStyle("A{$row}:I{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
                        $sheet->getStyle("J{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                        $sheet->getStyle("E{$row}")->getFont()->setBold(true)->getColor()->setRGB('1a7a4e');
                        $sheet->getStyle("F{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('bbf7d0');
                        $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB('065f46');
                        $sheet->getRowDimension($row)->setRowHeight(14);
                    } else {
                        // PARCIAL
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
                        $sheet->getStyle("E{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                        $sheet->getStyle("F{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fef3c7');
                        $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                        $sheet->getRowDimension($row)->setRowHeight(14);
                    }
                }

                // Bordes globales
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E8D5BF');
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('b05000');
                // Borde superior extra en fila totales
                $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->getBorders()->getTop()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
            },
        ];
    }
}


