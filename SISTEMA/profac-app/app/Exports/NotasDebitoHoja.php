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
 * Hoja del reporte Notas de Débito.
 *
 * Columnas (A..K):
 *  A(0) #  B(1) FECHA REGISTRO  C(2) NOTA DÉBITO  D(3) CÓDIGO FACTURA
 *  E(4) CLIENTE  F(5) FECHA EMISIÓN  G(6) MONTO ASIGNADO
 *  H(7) REGISTRADO POR  I(8) ESTADO
 */
class NotasDebitoHoja implements FromArray, WithTitle, WithStyles, WithEvents, WithStrictNullComparison
{
    protected $rows;
    protected $usuario;
    protected $titulo;

    const LAST_COL  = 'I';
    const COL_COUNT = 9;

    public function __construct($rows, $usuario = 'Sistema', $titulo = 'Notas de Débito')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
        $this->titulo  = $titulo;
    }

    public function title(): string { return 'Notas de Débito'; }

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
            '#', 'FECHA REGISTRO', 'NOTA DÉBITO', 'CÓDIGO FACTURA',
            'CLIENTE', 'FECHA EMISIÓN', 'MONTO ASIGNADO',
            'REGISTRADO POR', 'ESTADO',
        ];

        $totMonto = 0.0;
        $item     = 0;

        foreach ($this->rows as $r) {
            $item++;
            $monto = (float)($r->monto_asignado ?? 0);
            $totMonto += $monto;

            $estado = '';
            if (isset($r->estado_id)) {
                $estado = $r->estado_id == 1 ? 'Activo' : 'Anulado';
            } elseif (isset($r->estado)) {
                $estado = strip_tags($r->estado ?? '');
            }

            $out[] = [
                $item,
                $this->fmt($r->created_at ?? ''),
                $r->correlativoND ?? '',
                $r->cai           ?? '',
                $r->cliente       ?? '',
                $this->fmt($r->fechaEmision ?? ''),
                $monto,
                $r->user          ?? '',
                $estado,
            ];
        }

        // Fila totales
        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTALES';
        $tot[6] = $totMonto;
        $out[]  = $tot;

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
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = self::LAST_COL;

                // Fila 1
                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7D3900']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Fila 2
                $sheet->mergeCells('A2:' . $lastCol . '2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E67E22']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Fila 3
                $sheet->mergeCells('A3:' . $lastCol . '3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '555555']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(16);

                // Fila 4 cabeceras
                $sheet->getStyle('A4:' . $lastCol . '4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '7D3900']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDF4E7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'F2D49A']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);

                if ($lastRow >= 5) {
                    $dataEnd = $lastRow - 1;
                    for ($row = 5; $row <= $dataEnd; $row++) {
                        $bg = ($row % 2 === 0) ? 'FFFCF5' : 'FFFFFF';
                        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                            'font'    => ['size' => 9],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'EEEEEE']]],
                        ]);
                    }

                    // Columna G (monto)
                    $sheet->getStyle('G5:G' . $dataEnd)->getNumberFormat()->setFormatCode('"L " #,##0.00');
                    $sheet->getStyle('G4:G' . $dataEnd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Fila totales
                    $sheet->getStyle('A' . $lastRow . ':' . $lastCol . $lastRow)->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E67E22']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '7D3900']]],
                    ]);
                    $sheet->getStyle('G' . $lastRow)->getNumberFormat()->setFormatCode('"L " #,##0.00');
                    $sheet->getStyle('A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getRowDimension($lastRow)->setRowHeight(20);
                }

                // Anchos
                $widths = ['A' => 5, 'B' => 16, 'C' => 26, 'D' => 26,
                           'E' => 30, 'F' => 16, 'G' => 16, 'H' => 22, 'I' => 12];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                $sheet->freezePane('A5');
                $sheet->setAutoFilter('A4:' . $lastCol . '4');
            },
        ];
    }
}
