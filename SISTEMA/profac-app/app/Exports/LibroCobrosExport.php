<?php

namespace App\Exports;

use App\Services\ComisionDepositoPromerica;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
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
class LibroCobrosExport implements Export, WithMultipleSheets
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
                $label,
                ComisionDepositoPromerica::esPromerica($group['bank'])
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

class LibroCobrosSheet extends DefaultValueBinder implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths, WithTitle, WithCustomValueBinder
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;
    protected string $sheetTitle;
    protected string $label;
    protected bool $promerica;

    public function __construct(
        $data,
        string $fechaInicio,
        string $fechaFinal,
        string $sheetTitle,
        string $label,
        bool $promerica = false
    )
    {
        $this->data       = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
        $this->sheetTitle = $sheetTitle;
        $this->label = $label;
        $this->promerica = $promerica;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        $accountColumn = $this->promerica ? 'N' : 'K';
        if ($cell->getRow() >= 5 && $cell->getColumn() === $accountColumn && $value !== null) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function array(): array
    {
        $out = [];

        /* ── Fila 1 — empresa ── */
        $columnCount = $this->promerica ? 22 : 19;
        $r1 = array_fill(0, $columnCount, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        /* ── Fila 2 — título ── */
        $r2 = array_fill(0, $columnCount, '');
        $r2[0] = 'LIBRO DE COBROS — CONCILIACIÓN BANCARIA' . ($this->label === 'Todos' ? '' : ' | ' . $this->label);
        $out[] = $r2;

        /* ── Fila 3 — rango ── */
        $r3 = array_fill(0, $columnCount, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        /* ── Fila 4 — cabeceras ── */
        $headers = [
            'FECHA VENTA', 'FECHA VCTO.', 'FECHA PAGO',
            'CLIENTE', 'ASESOR COMERCIAL', 'TELEASESOR', 'N° FACTURA',
            'MONTO MOVIMIENTO', 'ESTADO',
            'BANCO', 'CUENTA', 'OBSERVACIONES',
            'EXONERADO', 'GRAVADO', 'EXENTO',
            'SUB TOTAL', 'ISV', 'TOTAL FACTURA',
        ];
        $out[] = $this->promerica
            ? array_merge(
                array_slice($headers, 0, 7),
                ['MOVIMIENTO', 'COMISIÓN COBRADA (3%)', 'COMISIÓN DEL BANCO (2.6%)'],
                array_slice($headers, 7),
                ['OTRO']
            )
            : array_merge($headers, ['OTRO']);

        $grandCobrado = $grandExon = $grandGrav = $grandExen = $grandSub = $grandIsv = $grandFact = 0.0;
        $grandRecibido = $grandComision = $grandBanco = $grandNeto = $grandOtro = 0.0;

        foreach ($this->data as $item) {
            $r = (array) $item;
            $cobrado = (float)($r['monto_cobrado'] ?? 0);
            $montoMovimiento = (float) ($r['monto_movimiento'] ?? $cobrado);
            $exon    = (float)($r['exonerado']     ?? 0);
            $grav    = (float)($r['gravado']       ?? 0);
            $exen    = (float)($r['excento']       ?? 0);
            $sub     = (float)($r['subtotal']      ?? 0);
            $isv     = (float)($r['isv']           ?? 0);
            $fact    = (float)($r['total_factura'] ?? 0);

            $grandCobrado += $montoMovimiento;
            $grandExon    += $exon;
            $grandGrav    += $grav;
            $grandExen    += $exen;
            $grandSub     += $sub;
            $grandIsv     += $isv;
            $grandFact    += $fact;
            $otroCentavos = ComisionDepositoPromerica::centavos(
                number_format((float) ($r['otro'] ?? 0), 2, '.', '')
            );
            $grandOtro += $otroCentavos / 100;

            $row = [
                $r['fecha_venta']       ?? '',
                $r['fecha_vencimiento'] ?? '',
                $r['fecha_pago']        ?? '',
                $r['cliente']           ?? '',
                $r['asesor_comercial']  ?? '',
                $r['teleasesor']        ?? '',
                $r['factura']           ?? '',
                $montoMovimiento,
                ($r['estado_factura'] ?? '') === 'PAGADA' ? 'COMPLETO' : ($r['estado_factura'] ?? ''),
                $r['banco']             ?? '',
                $r['cuenta_banco']      ?? '',
                $r['observaciones']     ?? '',
                isset($r['exonerado']) ? $exon : null,
                isset($r['gravado']) ? $grav : null,
                isset($r['excento']) ? $exen : null,
                isset($r['subtotal']) ? $sub : null,
                isset($r['isv']) ? $isv : null,
                isset($r['total_factura']) ? $fact : null,
            ];
            if (!$this->promerica) {
                $row[] = $otroCentavos / 100;
            }
            if ($this->promerica) {
                $recibidoCentavos = ComisionDepositoPromerica::centavos(
                    number_format((float) ($r['monto_recibido'] ?? 0), 2, '.', '')
                );
                $comisionCentavos = ComisionDepositoPromerica::centavos(
                    number_format((float) ($r['comision_cobrada'] ?? 0), 2, '.', '')
                );
                $bancoCentavos = ComisionDepositoPromerica::comisionBancoCentavos($recibidoCentavos);
                $netoCentavos = ComisionDepositoPromerica::centavos(
                    number_format((float) ($r['monto_movimiento'] ?? ($r['monto_recibido'] ?? 0)), 2, '.', '')
                );
                $grandRecibido += $recibidoCentavos / 100;
                $grandComision += $comisionCentavos / 100;
                $grandBanco += $bancoCentavos / 100;
                $grandNeto += $netoCentavos / 100;
                $grandOtro += $otroCentavos / 100;
                $row = array_merge(
                    array_slice($row, 0, 7),
                    [
                        $recibidoCentavos / 100,
                        $comisionCentavos / 100,
                        $bancoCentavos / 100,
                    ],
                    [$netoCentavos / 100],
                    array_slice($row, 8),
                    [$otroCentavos / 100]
                );
            }
            $out[] = $row;
        }

        /* ── Fila totales generales ── */
        $totRow     = array_fill(0, $columnCount, '');
        $totRow[0]  = 'TOTALES';
        if ($this->promerica) {
            $totRow[0] = 'TOTALES';
            $totRow[7] = $grandRecibido;
            $totRow[8] = $grandComision;
            $totRow[9] = $grandBanco;
            $totRow[10] = $grandNeto;
            $totRow[15] = $grandExon;
            $totRow[16] = $grandGrav;
            $totRow[17] = $grandExen;
            $totRow[18] = $grandSub;
            $totRow[19] = $grandIsv;
            $totRow[20] = $grandFact;
            $totRow[21] = $grandOtro;
        } else {
            $totRow[7]  = $grandCobrado;
            $totRow[12] = $grandExon;
            $totRow[13] = $grandGrav;
            $totRow[14] = $grandExen;
            $totRow[15] = $grandSub;
            $totRow[16] = $grandIsv;
            $totRow[17] = $grandFact;
            $totRow[18] = $grandOtro;
        }
        $out[]      = $totRow;

        return $out;
    }

    public function columnWidths(): array
    {
        if ($this->promerica) {
            return [
                'A' => 12, 'B' => 12, 'C' => 12, 'D' => 32, 'E' => 20,
                'F' => 20, 'G' => 20, 'H' => 14, 'I' => 16, 'J' => 18,
                'K' => 14, 'L' => 10, 'M' => 20, 'N' => 18, 'O' => 28,
                'P' => 13, 'Q' => 13, 'R' => 13, 'S' => 13, 'T' => 12,
                'U' => 14, 'V' => 14,
            ];
        }

        return [
            'A' => 12, 'B' => 12, 'C' => 12, 'D' => 32, 'E' => 20,
            'F' => 20, 'G' => 20, 'H' => 14, 'I' => 10, 'J' => 20,
            'K' => 18, 'L' => 28, 'M' => 13, 'N' => 13, 'O' => 13,
            'P' => 13, 'Q' => 12, 'R' => 14, 'S' => 14,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        $lc = $this->promerica ? 'V' : 'S';
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
                $lc = $this->promerica ? 'V' : 'S';
                $moneyColumns = $this->promerica
                    ? ['H', 'I', 'J', 'K', 'P', 'Q', 'R', 'S', 'T', 'U', 'V']
                    : ['H', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
                $textColumns = $this->promerica
                    ? ['D', 'E', 'F', 'G', 'M', 'N', 'O']
                    : ['D', 'E', 'F', 'G', 'J', 'K', 'L'];
                $stateColumn = $this->promerica ? 'L' : 'I';

                if ($lastRow < 5) return;

                $sheet->setAutoFilter("A4:{$lc}4");
                $sheet->freezePane('A5');

                $currency = '"L" #,##0.00';

                for ($row = 5; $row <= $lastRow; $row++) {
                    $aVal = (string)($sheet->getCell("A{$row}")->getValue() ?? '');

                    /* ── Separador vacío ── */
                    if ($aVal === '' && $sheet->getCell("C{$row}")->getValue() === null) {
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
                        foreach ($moneyColumns as $c) {
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
                        foreach ($moneyColumns as $c) {
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
                    foreach ($textColumns as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                    $accountColumn = $this->promerica ? 'N' : 'K';
                    $sheet->getStyle("{$accountColumn}{$row}")->getNumberFormat()->setFormatCode('@');
                    foreach ($moneyColumns as $c) {
                        $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode($currency);
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    $estado = (string)($sheet->getCell("{$stateColumn}{$row}")->getValue() ?? '');
                    if (in_array(strtoupper($estado), ['PAGADA', 'COMPLETO'], true)) {
                        $sheet->getStyle("A{$row}:" . ($this->promerica ? 'O' : 'L') . "{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
                        $sheet->getStyle(($this->promerica ? 'P' : 'M') . "{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                        $sheet->getStyle(($this->promerica ? 'K' : 'H') . "{$row}")->getFont()->setBold(true)->getColor()->setRGB('1a7a4e');
                        $sheet->getStyle("{$stateColumn}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('bbf7d0');
                        $sheet->getStyle("{$stateColumn}{$row}")->getFont()->setBold(true)->getColor()->setRGB('065f46');
                    } else {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
                        $sheet->getStyle(($this->promerica ? 'K' : 'H') . "{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                        $sheet->getStyle("{$stateColumn}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fef3c7');
                        $sheet->getStyle("{$stateColumn}{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400e');
                    }
                    $sheet->getRowDimension($row)->setRowHeight(14);
                }

                // Separador visual columna M (primera columna de datos fiscales)
                $separatorColumn = $this->promerica ? 'P' : 'M';
                $sheet->getStyle("{$separatorColumn}4:{$separatorColumn}{$lastRow}")->getBorders()->getLeft()
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
