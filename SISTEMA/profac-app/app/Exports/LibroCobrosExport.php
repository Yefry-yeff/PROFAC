<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Libro de Cobros — Conciliación Bancaria
 *
 * Columnas (A–Q, 17 cols):
 *  A  Fecha Venta     B  Fecha Vcto.     C  Fecha Pago
 *  D  Cliente         E  Vendedor        F  N° Factura
 *  G  Monto Cobrado   H  Estado          I  Banco
 *  J  Cuenta          K  Observaciones   L  Exonerado
 *  M  Gravado         N  Exento          O  Sub Total
 *  P  ISV             Q  Total Factura
 */
class LibroCobrosExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;

    const LAST_COL  = 'Q';
    const COL_COUNT = 17;

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
            'FECHA VENTA', 'FECHA VCTO.', 'FECHA PAGO',
            'CLIENTE', 'VENDEDOR', 'N° FACTURA',
            'MONTO COBRADO', 'ESTADO',
            'BANCO', 'CUENTA', 'OBSERVACIONES',
            'EXONERADO', 'GRAVADO', 'EXENTO',
            'SUB TOTAL', 'ISV', 'TOTAL FACTURA',
        ];

        /* ── Agrupar por banco + cuenta ── */
        $groups = [];
        foreach ($this->data as $item) {
            $r   = (array) $item;
            $key = ($r['banco'] ?? '') . "\x00" . ($r['cuenta_banco'] ?? '');
            $groups[$key][] = $r;
        }

        $grandCobrado = $grandExon = $grandGrav = $grandExen = $grandSub = $grandIsv = $grandFact = 0.0;

        foreach ($groups as $key => $rows) {
            [$bancoNombre, $cuentaNombre] = array_pad(explode("\x00", $key, 2), 2, '');

            /* ── Cabecera de grupo ── */
            $gh    = array_fill(0, self::COL_COUNT, '');
            $gh[0] = 'BANCO: ' . strtoupper($bancoNombre) . '   |   CUENTA: ' . strtoupper($cuentaNombre);
            $out[] = $gh;

            $gCobrado = $gExon = $gGrav = $gExen = $gSub = $gIsv = $gFact = 0.0;

            foreach ($rows as $r) {
                $cobrado = (float)($r['monto_cobrado'] ?? 0);
                $exon    = (float)($r['exonerado']     ?? 0);
                $grav    = (float)($r['gravado']       ?? 0);
                $exen    = (float)($r['excento']       ?? 0);
                $sub     = (float)($r['subtotal']      ?? 0);
                $isv     = (float)($r['isv']           ?? 0);
                $fact    = (float)($r['total_factura'] ?? 0);
                $estado  = $r['estado_factura']        ?? '';

                $gCobrado += $cobrado;
                $gExon    += $exon;
                $gGrav    += $grav;
                $gExen    += $exen;
                $gSub     += $sub;
                $gIsv     += $isv;
                $gFact    += $fact;

                $out[] = [
                    $r['fecha_venta']       ?? '',
                    $r['fecha_vencimiento'] ?? '',
                    $r['fecha_pago']        ?? '',
                    $r['cliente']           ?? '',
                    $r['vendedor']          ?? '',
                    $r['factura']           ?? '',
                    $cobrado,
                    $estado,
                    $r['banco']             ?? '',
                    $r['cuenta_banco']      ?? '',
                    $r['observaciones']     ?? '',
                    $exon,
                    $grav,
                    $exen,
                    $sub,
                    $isv,
                    $fact,
                ];
            }

            /* ── Subtotal del grupo ── */
            $st     = array_fill(0, self::COL_COUNT, '');
            $st[0]  = 'SUBTOTAL: ' . strtoupper($bancoNombre);
            $st[6]  = $gCobrado;
            $st[11] = $gExon;
            $st[12] = $gGrav;
            $st[13] = $gExen;
            $st[14] = $gSub;
            $st[15] = $gIsv;
            $st[16] = $gFact;
            $out[]  = $st;

            /* ── Separador vacío ── */
            $out[] = array_fill(0, self::COL_COUNT, '');

            $grandCobrado += $gCobrado;
            $grandExon    += $gExon;
            $grandGrav    += $gGrav;
            $grandExen    += $gExen;
            $grandSub     += $gSub;
            $grandIsv     += $gIsv;
            $grandFact    += $gFact;
        }

        /* ── Fila totales generales ── */
        $totRow     = array_fill(0, self::COL_COUNT, '');
        $totRow[0]  = 'TOTALES';
        $totRow[6]  = $grandCobrado;
        $totRow[11] = $grandExon;
        $totRow[12] = $grandGrav;
        $totRow[13] = $grandExen;
        $totRow[14] = $grandSub;
        $totRow[15] = $grandIsv;
        $totRow[16] = $grandFact;
        $out[]      = $totRow;

        return $out;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 12, 'C' => 12, 'D' => 32, 'E' => 20,
            'F' => 20, 'G' => 14, 'H' => 10, 'I' => 20, 'J' => 18,
            'K' => 28, 'L' => 13, 'M' => 13, 'N' => 13, 'O' => 13,
            'P' => 12, 'Q' => 14,
        ];
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

        // Anchos fijos definidos en columnWidths() — no se usa setAutoSize()

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

                for ($row = 5; $row <= $lastRow; $row++) {
                    $aVal = (string)($sheet->getCell("A{$row}")->getValue() ?? '');

                    /* ── Separador vacío ── */
                    if ($aVal === '') {
                        $sheet->getRowDimension($row)->setRowHeight(5);
                        continue;
                    }

                    /* ── Cabecera de banco/cuenta ── */
                    if (str_starts_with($aVal, 'BANCO:')) {
                        $sheet->mergeCells("A{$row}:{$lc}{$row}");
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F3864');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
                        $sheet->getRowDimension($row)->setRowHeight(18);
                        continue;
                    }

                    /* ── Subtotal de grupo ── */
                    if (str_starts_with($aVal, 'SUBTOTAL')) {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(9)->getColor()->setRGB('7d3f00');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getBorders()->getTop()
                            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getBorders()->getBottom()
                            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                        foreach (['G', 'L', 'M', 'N', 'O', 'P', 'Q'] as $c) {
                            $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode($currency);
                            $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        }
                        $sheet->getRowDimension($row)->setRowHeight(16);
                        continue;
                    }

                    /* ── Total general ── */
                    if ($aVal === 'TOTALES') {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(10)->getColor()->setRGB('7d3f00');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getBorders()->getTop()
                            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                        foreach (['G', 'L', 'M', 'N', 'O', 'P', 'Q'] as $c) {
                            $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode($currency);
                            $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        }
                        $sheet->getRowDimension($row)->setRowHeight(18);
                        continue;
                    }

                    /* ── Fila de datos ── */
                    $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    foreach (['D', 'E', 'F', 'I', 'J', 'K'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                    foreach (['G', 'L', 'M', 'N', 'O', 'P', 'Q'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode($currency);
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    $estado = (string)($sheet->getCell("H{$row}")->getValue() ?? '');
                    if (strtoupper($estado) === 'PAGADA') {
                        $sheet->getStyle("A{$row}:K{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
                        $sheet->getStyle("L{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                        $sheet->getStyle("G{$row}")->getFont()->setBold(true)->getColor()->setRGB('1a7a4e');
                        $sheet->getStyle("H{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('bbf7d0');
                        $sheet->getStyle("H{$row}")->getFont()->setBold(true)->getColor()->setRGB('065f46');
                    } else {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
                        $sheet->getStyle("G{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                        $sheet->getStyle("H{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fef3c7');
                        $sheet->getStyle("H{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                    }
                    $sheet->getRowDimension($row)->setRowHeight(14);
                }

                // Separador visual columna L (primera columna de datos fiscales)
                $sheet->getStyle("L4:L{$lastRow}")->getBorders()->getLeft()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('f2a630');

                // Bordes globales
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E8D5BF');
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('b05000');
            },
        ];
    }
}
