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
 * Reporte Logístico — Pestaña "Por Factura" (también usado por el detalle
 * de facturas de la pestaña Resumen, que comparte la misma consulta con
 * filtros de clic en gráfico aplicados).
 *
 * Una fila por cada FACTURA de una distribución dentro del período.
 *
 * Columnas (A–K, 11 cols):
 *  A Distribución   B Fecha Prog.   C Hora Salida   D N° Factura
 *  E Cliente   F Total L.   G Equipo   H Estado   I Fecha Entrega
 *  J Motivo Anulación   K Motivo Confirmación
 */
class ReporteLogisticaPorFacturaExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;

    const LAST_COL  = 'K';
    const COL_COUNT = 11;

    public function __construct($data, string $fechaInicio, string $fechaFinal)
    {
        $this->data        = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // Distribución
            'B' => 13, // Fecha Prog.
            'C' => 16, // Hora Salida
            'D' => 24, // N° Factura
            'E' => 34, // Cliente
            'F' => 13, // Total L.
            'G' => 24, // Equipo
            'H' => 13, // Estado
            'I' => 16, // Fecha Entrega
            'J' => 30, // Motivo Anulación
            'K' => 30, // Motivo Confirmación
        ];
    }

    public function array(): array
    {
        $out = [];

        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'REPORTE LOGÍSTICO — POR FACTURA';
        $out[] = $r2;

        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        $out[] = [
            'DISTRIBUCIÓN', 'FECHA PROG.', 'HORA SALIDA', 'N° FACTURA', 'CLIENTE',
            'TOTAL L.', 'EQUIPO', 'ESTADO', 'FECHA ENTREGA',
            'MOTIVO ANULACIÓN', 'MOTIVO CONFIRMACIÓN',
        ];

        foreach ($this->data as $item) {
            $r = (array) $item;

            $out[] = [
                $r['distribucion_id']     ?? '',
                $r['fecha_programada']    ?? '',
                $r['hora_salida']         ?? '',
                $r['numero_factura']      ?? '',
                $r['cliente']             ?? '',
                $r['total']               ?? '',
                $r['equipo']              ?? '',
                $r['estado']              ?? '',
                $r['fecha_entrega_real']  ?? '',
                $r['motivo_anulacion']    ?? '',
                $r['motivo_confirmacion'] ?? '',
            ];
        }

        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTAL FACTURAS:';
        $tot[3] = count($this->data);
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
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0f4c4a']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0d9488']],
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
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d9488']],
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

                $sheet->getStyle("A5:{$lc}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                foreach (['E', 'G', 'J', 'K'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->getDefaultRowDimension()->setRowHeight(15);

                $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '0f4c4a']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2F1']],
                ]);
                $sheet->getRowDimension($lastRow)->setRowHeight(18);

                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->applyFromArray([
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => 'B2DFDB']],
                    'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0d9488']],
                ]);
                $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0f766e');
            },
        ];
    }
}
