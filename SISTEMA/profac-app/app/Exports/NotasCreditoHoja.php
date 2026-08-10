<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Hoja del reporte Notas de Crédito.
 *
 * Columnas (A..P):
 *  A(0) #   B(1) FECHA   C(2) CÓDIGO NC   D(3) REGISTRO N°
 *  E(4) CLIENTE   F(5) N° FACTURA   G(6) MOTIVO
 *  H(7) COMENTARIO   I(8) SUB TOTAL   J(9) ISV   K(10) TOTAL FISCAL
 *  L(11) APLICADO   M(12) REEMBOLSADO   N(13) DISPONIBLE
 *  O(14) ESTADO DEL CRÉDITO   P(15) REGISTRADO POR
 */
class NotasCreditoHoja implements FromArray, WithTitle, WithStyles, WithEvents, WithStrictNullComparison
{
    protected $rows;
    protected $usuario;
    protected $titulo;

    const LAST_COL  = 'P';
    const COL_COUNT = 16;

    public function __construct($rows, $usuario = 'Sistema', $titulo = 'Notas de Crédito')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
        $this->titulo  = $titulo;
    }

    public function title(): string { return 'Notas de Crédito'; }

    private function fmt(?string $d): string
    {
        if (!$d) return '';
        $ts = strtotime($d);
        return $ts ? date('d/m/Y', $ts) : $d;
    }

    public function array(): array
    {
        $out = [];

        // Fila 1 – empresa
        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        // Fila 2 – título
        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = strtoupper($this->titulo);
        $out[] = $r2;

        // Fila 3 – generado por
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Generado: ' . now()->format('d/m/Y H:i') . '   |   Descargado por: ' . $this->usuario;
        $out[] = $r3;

        // Fila 4 – cabeceras
        $out[] = [
            '#', 'FECHA', 'CÓDIGO NC', 'REGISTRO N°', 'CLIENTE',
            'N° FACTURA', 'MOTIVO', 'COMENTARIO',
            'SUB TOTAL', 'ISV', 'TOTAL FISCAL', 'APLICADO', 'REEMBOLSADO',
            'DISPONIBLE', 'ESTADO DEL CRÉDITO', 'REGISTRADO POR',
        ];

        $totSubTotal   = 0.0;
        $totIsv        = 0.0;
        $totTotal      = 0.0;
        $totAplicado   = 0.0;
        $totReembolsado = 0.0;
        $totDisponible = 0.0;
        $item          = 0;

        foreach ($this->rows as $r) {
            $item++;
            $sub   = (float)($r->sub_total ?? 0);
            $isv   = (float)($r->isv       ?? 0);
            $total = (float)($r->total      ?? 0);
            $aplicado = (float)($r->monto_aplicado ?? 0);
            $reembolsado = (float)($r->monto_reembolsado ?? 0);
            $disponible = (float)($r->saldo_disponible ?? 0);

            $totSubTotal += $sub;
            $totIsv      += $isv;
            $totTotal    += $total;
            $totAplicado += $aplicado;
            $totReembolsado += $reembolsado;
            $totDisponible += $disponible;

            $out[] = [
                $item,
                $this->fmt($r->fecha_registro ?? ($r->created_at ?? '')),
                $r->codigo          ?? '',
                $r->cai             ?? '',
                $r->cliente         ?? '',
                $r->factura         ?? '',
                $r->motivo          ?? '',
                $r->comentario      ?? '',
                $sub,
                $isv,
                $total,
                $aplicado,
                $reembolsado,
                $disponible,
                $r->estado_credito ?? '',
                $r->registrado_por  ?? '',
            ];
        }

        // Fila totales
        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0]  = 'TOTALES';
        $tot[8]  = $totSubTotal;
        $tot[9]  = $totIsv;
        $tot[10] = $totTotal;
        $tot[11] = $totAplicado;
        $tot[12] = $totReembolsado;
        $tot[13] = $totDisponible;
        $out[]   = $tot;

        return $out;
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = self::LAST_COL;

                // ── Fila 1: empresa ──────────────────────────────────────────
                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // ── Fila 2: título ───────────────────────────────────────────
                $sheet->mergeCells('A2:' . $lastCol . '2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0392B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // ── Fila 3: meta ─────────────────────────────────────────────
                $sheet->mergeCells('A3:' . $lastCol . '3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '555555']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9EBEA']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(16);

                // ── Fila 4: cabeceras ─────────────────────────────────────────
                $sheet->getStyle('A4:' . $lastCol . '4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '5C0000']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FADBD8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'F5C6CB']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);

                // ── Filas de datos ────────────────────────────────────────────
                if ($lastRow >= 5) {
                    $dataEnd = $lastRow - 1; // última data (excluye totales)
                    // zebra
                    for ($row = 5; $row <= $dataEnd; $row++) {
                        $bg = ($row % 2 === 0) ? 'FFF5F5' : 'FFFFFF';
                        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                            'font'    => ['size' => 9],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'EEEEEE']]],
                        ]);
                    }

                    // columnas numéricas (I..N) → formato L #,##0.00
                    foreach (['I', 'J', 'K', 'L', 'M', 'N'] as $col) {
                        $sheet->getStyle($col . '5:' . $col . $dataEnd)
                              ->getNumberFormat()->setFormatCode('"L " #,##0.00');
                        $sheet->getStyle($col . '4:' . $col . $dataEnd)
                              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    // ── Fila totales ─────────────────────────────────────────
                    $sheet->getStyle('A' . $lastRow . ':' . $lastCol . $lastRow)->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0392B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '8B0000']]],
                    ]);
                    foreach (['I', 'J', 'K', 'L', 'M', 'N'] as $col) {
                        $sheet->getStyle($col . $lastRow)->getNumberFormat()->setFormatCode('"L " #,##0.00');
                    }
                    $sheet->getStyle('A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getRowDimension($lastRow)->setRowHeight(20);
                }

                // ── Anchos de columna ─────────────────────────────────────────
                $widths = ['A' => 5, 'B' => 14, 'C' => 10, 'D' => 26, 'E' => 30,
                           'F' => 26, 'G' => 24, 'H' => 30, 'I' => 14, 'J' => 12, 'K' => 14,
                           'L' => 14, 'M' => 14, 'N' => 14, 'O' => 22, 'P' => 22];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                // ── Freeze & autofilter ────────────────────────────────────────
                $sheet->freezePane('A5');
                $sheet->setAutoFilter('A4:' . $lastCol . '4');
            },
        ];
    }
}
