<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EvaluacionClientesHoja implements FromArray, WithTitle, WithStyles, WithEvents
{
    protected $rows;
    protected $usuario;

    // 9 columnas: A..I
    const LAST_COL  = 'I';
    const COL_COUNT = 9;

    public function __construct($rows, $usuario = 'Sistema')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
    }

    public function title(): string
    {
        return 'Evaluación Clientes';
    }

    public function array(): array
    {
        $out = [];

        // Fila 1 – razón social
        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA S.A. DE C.V.   |   RTN: 08011986138652';
        $out[] = $r1;

        // Fila 2 – título del reporte
        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'EVALUACIÓN DE CLIENTES POR NIVEL DE FACTURACIÓN';
        $out[] = $r2;

        // Fila 3 – fecha y usuario
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Generado: ' . now()->format('d/m/Y H:i') . '   |   Descargado por: ' . $this->usuario;
        $out[] = $r3;

        // Fila 4 – cabeceras
        $out[] = [
            'CÓDIGO',
            'NOMBRE CLIENTE',
            'ESTADO',
            'VENDEDOR',
            'N° ÚLTIMA FACTURA',
            'FECHA ÚLTIMA FACTURA',
            'MONTO ÚLTIMA FACTURA',
            'SALDO PENDIENTE',
            'REQUIERE ATENCIÓN',
        ];

        // Filas de datos
        foreach ($this->rows as $r) {
            $out[] = [
                $r->codigo_cliente,
                $r->nombre_cliente,
                $r->estado,
                $r->vendedor,
                $r->numero_ultima_factura ?? 'Sin historial de facturación',
                $r->fecha_ultima_factura
                    ? date('d/m/Y', strtotime($r->fecha_ultima_factura))
                    : '—',
                $r->monto_ultima_factura > 0 ? (float) $r->monto_ultima_factura : 0,
                $r->saldo_pendiente > 0     ? (float) $r->saldo_pendiente      : 0,
                $r->requiere_atencion,
            ];
        }

        return $out;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F5C99']],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
            ],
            3 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '555555']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F5C99']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = count($this->rows) + 4;
                $lastCol = self::LAST_COL;

                // Merge header rows
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->mergeCells("A3:{$lastCol}3");

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(42);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(26);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(22);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(18);

                // Row heights
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->getRowDimension(2)->setRowHeight(26);
                $sheet->getRowDimension(4)->setRowHeight(20);

                // Alignment for header rows
                $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A2:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A4:{$lastCol}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Borders on data area
                if ($lastRow >= 4) {
                    $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                    ]);
                }

                // Number format for monto and saldo columns (G, H)
                if ($lastRow > 4) {
                    $sheet->getStyle("G5:H{$lastRow}")
                          ->getNumberFormat()
                          ->setFormatCode('#,##0.00');
                }

                // Alternate row fill
                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                              ->getFill()
                              ->setFillType(Fill::FILL_SOLID)
                              ->getStartColor()->setRGB('EEF4FB');
                    }
                }

                // Highlight "Requiere Atención = Sí" rows (col I now)
                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($sheet->getCell("I{$row}")->getValue() === 'Sí') {
                        $sheet->getStyle("I{$row}")
                              ->getFont()
                              ->setBold(true)
                              ->getColor()->setRGB('C0392B');
                        $sheet->getStyle("A{$row}:I{$row}")
                              ->getFill()
                              ->setFillType(Fill::FILL_SOLID)
                              ->getStartColor()->setRGB('FDECEA');
                    }
                }
            },
        ];
    }
}
