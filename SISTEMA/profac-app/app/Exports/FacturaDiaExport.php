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
 * Facturación Diaria por Rango de Fechas
 *
 * Columnas (A–K, 11 cols):
 *  A Fecha    B Mes        C N° Factura    D Cliente
 *  E Vendedor F Facturador G Gestor Entrega
 *  H Subtotal I ISV         J Total         K Tipo
 */
class FacturaDiaExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
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
            'A' => 18, // Fecha
            'B' => 13, // Mes
            'C' => 24, // N° Factura
            'D' => 34, // Cliente
            'E' => 22, // Vendedor
            'F' => 22, // Facturador
            'G' => 22, // Gestor Entrega
            'H' => 14, // Subtotal
            'I' => 14, // ISV
            'J' => 14, // Total
            'K' => 12, // Tipo
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
        $r2[0] = 'REPORTE DE FACTURACIÓN DIARIA';
        $out[] = $r2;

        // Fila 3 — rango
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        // Fila 4 — cabeceras
        $out[] = [
            'FECHA', 'MES', 'N° FACTURA', 'CLIENTE',
            'ASESOR COMERCIAL', 'TELE ASESOR', 'GESTOR ENTREGA',
            'SUBTOTAL', 'ISV', 'TOTAL', 'TIPO',
        ];

        $totSub = $totIsv = $totTotal = 0.0;

        foreach ($this->data as $item) {
            $r = (array) $item;

            $sub   = (float) str_replace(',', '', $r['subtotal']  ?? 0);
            $isv   = (float) str_replace(',', '', $r['imp_venta'] ?? 0);
            $total = (float) str_replace(',', '', $r['total']     ?? 0);

            $totSub   += $sub;
            $totIsv   += $isv;
            $totTotal += $total;

            $out[] = [
                $r['fecha']          ?? '',
                $r['mes']            ?? '',
                $r['factura']        ?? '',
                $r['cliente']        ?? '',
                $r['vendedor']       ?? '',
                $r['facturador']     ?? '',
                $r['gestor_entrega'] ?? '',
                $sub,
                $isv,
                $total,
                $r['tipo']           ?? '',
            ];
        }

        // Fila de totales
        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0]  = 'TOTALES:';
        $tot[7]  = round($totSub,   2);
        $tot[8]  = round($totIsv,   2);
        $tot[9]  = round($totTotal, 2);
        $out[] = $tot;

        return $out;
    }

    public function styles(Worksheet $sheet)
    {
        $lc = self::LAST_COL;
        $sheet->mergeCells("A1:{$lc}1");
        $sheet->mergeCells("A2:{$lc}2");
        $sheet->mergeCells("A3:{$lc}3");

        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '7D3F00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'E07000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '404040']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(16);

        $sheet->getStyle("A4:{$lc}4")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E07000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

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

                // Formatos numéricos por columna
                $numFmt = '#,##0.00';
                foreach (['H', 'I', 'J'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getNumberFormat()->setFormatCode($numFmt);
                }

                // Alineación general
                $sheet->getStyle("A5:{$lc}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Izquierda: cliente, vendedor, facturador, gestor
                foreach (['D', 'E', 'F', 'G'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Numéricos derecha
                foreach (['H', 'I', 'J'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Altura de filas por defecto
                $sheet->getDefaultRowDimension()->setRowHeight(15);

                // Fila de totales
                $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '7D3F00']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3E0']],
                ]);
                $sheet->getRowDimension($lastRow)->setRowHeight(18);

                // Bordes tabla
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->applyFromArray([
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => 'E8D5BF']],
                    'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'E07000']],
                ]);
                $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('B05000');
            },
        ];
    }
}
