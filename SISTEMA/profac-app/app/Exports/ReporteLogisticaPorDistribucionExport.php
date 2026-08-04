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
 * Reporte Logístico — Pestaña "Por Distribución"
 *
 * Una fila por cada distribución (salida) del período.
 *
 * Columnas (A–J, 10 cols):
 *  A ID   B Fecha   C Equipo   D Creador   E Total Facturas
 *  F Entregadas   G Pendientes   H Anuladas   I Efectividad   J Estado
 */
class ReporteLogisticaPorDistribucionExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $data;
    protected string $fechaInicio;
    protected string $fechaFinal;

    const LAST_COL  = 'J';
    const COL_COUNT = 10;

    public function __construct($data, string $fechaInicio, string $fechaFinal)
    {
        $this->data        = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // ID
            'B' => 13, // Fecha
            'C' => 26, // Equipo
            'D' => 22, // Creador
            'E' => 14, // Total Facturas
            'F' => 12, // Entregadas
            'G' => 12, // Pendientes
            'H' => 11, // Anuladas
            'I' => 12, // Efectividad
            'J' => 14, // Estado
        ];
    }

    public function array(): array
    {
        $out = [];

        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'REPORTE LOGÍSTICO — POR DISTRIBUCIÓN';
        $out[] = $r2;

        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        $out[] = [
            'ID', 'FECHA', 'EQUIPO', 'CREADOR', 'TOTAL FACTURAS',
            'ENTREGADAS', 'PENDIENTES', 'ANULADAS', 'EFECTIVIDAD', 'ESTADO',
        ];

        foreach ($this->data as $item) {
            $r = (array) $item;

            $out[] = [
                $r['id']              ?? '',
                $r['fecha']            ?? '',
                $r['equipo']           ?? '',
                $r['creador']          ?? '',
                $r['total_facturas']   ?? 0,
                $r['entregadas']       ?? 0,
                $r['pendientes']       ?? 0,
                $r['anuladas']         ?? 0,
                $r['efectividad']      ?? '',
                $r['estado']           ?? '',
            ];
        }

        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTAL DISTRIBUCIONES:';
        $tot[4] = count($this->data);
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

                foreach (['C', 'D'] as $col) {
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
