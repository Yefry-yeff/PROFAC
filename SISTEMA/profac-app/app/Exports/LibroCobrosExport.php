<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Libro de Cobros — Conciliación Bancaria
 *
 * Columnas (A–R, 18 cols):
 *  A  Fecha Venta     B  Fecha Vcto.     C  Fecha Pago
 *  D  Cliente         E  Asesor Comercial F Teleasesor
 *  G  N° Factura      H  Monto Cobrado   I  Estado
 *  J  Banco           K  Cuenta          L  Observaciones
 *  M  Exonerado       N  Gravado         O  Exento
 *  P  Sub Total       Q  ISV             R  Total Factura
 */
class LibroCobrosExport implements WithMultipleSheets
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;

    public function __construct($data, string $fechaInicio, string $fechaFinal)
    {
        $this->data = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal = $fechaFinal;
    }

    public function sheets(): array
    {
        $sheets = [
            new LibroCobrosSheet($this->data, $this->fechaInicio, $this->fechaFinal, 'Todos', 'Todos'),
        ];
        $groups = [];

        foreach ($this->data as $item) {
            $row = (array) $item;
            $bank = trim((string) ($row['banco'] ?? '')) ?: 'SIN BANCO';
            $account = trim((string) ($row['cuenta_banco'] ?? '')) ?: 'SIN CUENTA';
            $key = $bank . "\x00" . $account;
            $groups[$key]['bank'] = $bank;
            $groups[$key]['account'] = $account;
            $groups[$key]['rows'][] = $row;
        }

        $usedTitles = ['todos' => true];
        foreach ($groups as $group) {
            $label = $group['bank'] . ' - ' . $group['account'];
            $title = $this->uniqueSheetTitle($label, $usedTitles);
            $sheets[] = new LibroCobrosSheet(
                $group['rows'],
                $this->fechaInicio,
                $this->fechaFinal,
                $title,
                $label
            );
        }

        return $sheets;
    }

    private function uniqueSheetTitle(string $label, array &$usedTitles): string
    {
        $clean = trim((string) preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $label));
        $clean = $clean !== '' ? $clean : 'Banco - Cuenta';
        $truncate = static fn(string $value, int $length): string => function_exists('mb_substr')
            ? mb_substr($value, 0, $length)
            : substr($value, 0, $length);
        $title = $truncate($clean, 31);
        $candidate = $title;
        $suffix = 2;

        while (isset($usedTitles[strtolower($candidate)])) {
            $ending = ' (' . $suffix++ . ')';
            $candidate = $truncate($clean, 31 - strlen($ending)) . $ending;
        }

        $usedTitles[strtolower($candidate)] = true;

        return $candidate;
    }
}

class LibroCobrosSheet implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths, WithTitle
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;
    protected string $sheetTitle;
    protected string $label;

    const LAST_COL  = 'R';
    const COL_COUNT = 18;

    public function __construct(
        $data,
        string $fechaInicio,
        string $fechaFinal,
        string $sheetTitle,
        string $label
    )
    {
        $this->data       = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
        $this->sheetTitle = $sheetTitle;
        $this->label = $label;
    }

    public function title(): string
    {
        return $this->sheetTitle;
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
        $r2[0] = 'LIBRO DE COBROS — CONCILIACIÓN BANCARIA' . ($this->label === 'Todos' ? '' : ' | ' . $this->label);
        $out[] = $r2;

        /* ── Fila 3 — rango ── */
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        /* ── Fila 4 — cabeceras ── */
        $out[] = [
            'FECHA VENTA', 'FECHA VCTO.', 'FECHA PAGO',
            'CLIENTE', 'ASESOR COMERCIAL', 'TELEASESOR', 'N° FACTURA',
            'MONTO MOVIMIENTO', 'ESTADO',
            'BANCO', 'CUENTA', 'OBSERVACIONES',
            'EXONERADO', 'GRAVADO', 'EXENTO',
            'SUB TOTAL', 'ISV', 'TOTAL FACTURA',
        ];

        $grandCobrado = $grandExon = $grandGrav = $grandExen = $grandSub = $grandIsv = $grandFact = 0.0;

        foreach ($this->data as $item) {
            $r = (array) $item;
            $cobrado = (float)($r['monto_cobrado'] ?? 0);
            $exon    = (float)($r['exonerado']     ?? 0);
            $grav    = (float)($r['gravado']       ?? 0);
            $exen    = (float)($r['excento']       ?? 0);
            $sub     = (float)($r['subtotal']      ?? 0);
            $isv     = (float)($r['isv']           ?? 0);
            $fact    = (float)($r['total_factura'] ?? 0);

            $grandCobrado += $cobrado;
            $grandExon    += $exon;
            $grandGrav    += $grav;
            $grandExen    += $exen;
            $grandSub     += $sub;
            $grandIsv     += $isv;
            $grandFact    += $fact;

            $out[] = [
                $r['fecha_venta']       ?? '',
                $r['fecha_vencimiento'] ?? '',
                $r['fecha_pago']        ?? '',
                $r['cliente']           ?? '',
                $r['asesor_comercial']  ?? '',
                $r['teleasesor']        ?? '',
                $r['factura']           ?? '',
                $cobrado,
                ($r['estado_factura'] ?? '') === 'PAGADA' ? 'COMPLETO' : ($r['estado_factura'] ?? ''),
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

        /* ── Fila totales generales ── */
        $totRow     = array_fill(0, self::COL_COUNT, '');
        $totRow[0]  = 'TOTALES';
        $totRow[7]  = $grandCobrado;
        $totRow[12] = $grandExon;
        $totRow[13] = $grandGrav;
        $totRow[14] = $grandExen;
        $totRow[15] = $grandSub;
        $totRow[16] = $grandIsv;
        $totRow[17] = $grandFact;
        $out[]      = $totRow;

        return $out;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 12, 'C' => 12, 'D' => 32, 'E' => 20,
            'F' => 20, 'G' => 20, 'H' => 14, 'I' => 10, 'J' => 20,
            'K' => 18, 'L' => 28, 'M' => 13, 'N' => 13, 'O' => 13,
            'P' => 13, 'Q' => 12, 'R' => 14,
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
                        foreach (['H', 'M', 'N', 'O', 'P', 'Q', 'R'] as $c) {
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
                        foreach (['H', 'M', 'N', 'O', 'P', 'Q', 'R'] as $c) {
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
                    foreach (['D', 'E', 'F', 'G', 'J', 'K', 'L'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                    foreach (['H', 'M', 'N', 'O', 'P', 'Q', 'R'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode($currency);
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    $estado = (string)($sheet->getCell("I{$row}")->getValue() ?? '');
                    if (in_array(strtoupper($estado), ['PAGADA', 'COMPLETO'], true)) {
                        $sheet->getStyle("A{$row}:L{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
                        $sheet->getStyle("M{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                        $sheet->getStyle("H{$row}")->getFont()->setBold(true)->getColor()->setRGB('1a7a4e');
                        $sheet->getStyle("I{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('bbf7d0');
                        $sheet->getStyle("I{$row}")->getFont()->setBold(true)->getColor()->setRGB('065f46');
                    } else {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
                        $sheet->getStyle("H{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                        $sheet->getStyle("I{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fef3c7');
                        $sheet->getStyle("I{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                    }
                    $sheet->getRowDimension($row)->setRowHeight(14);
                }

                // Separador visual columna M (primera columna de datos fiscales)
                $sheet->getStyle("M4:M{$lastRow}")->getBorders()->getLeft()
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
