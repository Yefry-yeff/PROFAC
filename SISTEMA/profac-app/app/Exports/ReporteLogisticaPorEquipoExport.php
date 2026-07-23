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
 * Reporte Logístico — Detalle "Por Equipo" (por factura)
 *
 * Una fila por cada FACTURA entregada. Los datos de la entrega (equipo,
 * fecha, horas, miembros/comisión) se repiten por cada una de las N
 * facturas que pertenezcan a esa misma salida/grupo.
 *
 * Columnas (A–K, 11 cols):
 *  A Equipo   B Fecha   C Hora Salida   D Hora Última Entrega
 *  E Hora Llegada   F Miembros / % Comisión   G N° Factura   H Cliente
 *  I Dirección de Entrega   J Hora de Entrega   K Hallazgo
 */
class ReporteLogisticaPorEquipoExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
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
            'A' => 24, // Equipo
            'B' => 13, // Fecha
            'C' => 13, // Hora Salida
            'D' => 16, // Hora Última Entrega
            'E' => 13, // Hora Llegada
            'F' => 42, // Miembros / % Comisión
            'G' => 24, // N° Factura
            'H' => 34, // Cliente
            'I' => 34, // Dirección de Entrega
            'J' => 13, // Hora de Entrega
            'K' => 12, // Hallazgo
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
        $r2[0] = 'REPORTE LOGÍSTICO — DETALLE POR EQUIPO';
        $out[] = $r2;

        // Fila 3 — rango
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal
               . '     Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        // Fila 4 — cabeceras
        $out[] = [
            'EQUIPO', 'FECHA', 'HORA SALIDA', 'HORA ÚLTIMA ENTREGA',
            'HORA LLEGADA', 'MIEMBROS / % COMISIÓN', 'N° FACTURA', 'CLIENTE',
            'DIRECCIÓN DE ENTREGA', 'HORA DE ENTREGA', 'HALLAZGO',
        ];

        foreach ($this->data as $item) {
            $r = (array) $item;

            $out[] = [
                $r['equipo']              ?? '',
                $r['fecha']                ?? '',
                $r['hora_salida']          ?? '',
                $r['hora_ultima_entrega']  ?? '',
                $r['hora_llegada']         ?? '',
                $r['miembros']             ?? '',
                $r['numero_factura']       ?? '',
                $r['cliente']              ?? '',
                $r['direccion_entrega']    ?? '',
                $r['hora_entrega']         ?? '',
                $r['hallazgo']             ?? '',
            ];
        }

        // Fila de totales (cantidad de facturas)
        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTAL FACTURAS:';
        $tot[6] = count($this->data);
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

                // Alineación general
                $sheet->getStyle("A5:{$lc}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Izquierda: equipo, miembros, cliente, dirección
                foreach (['A', 'F', 'H', 'I'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Altura de filas por defecto
                $sheet->getDefaultRowDimension()->setRowHeight(15);

                // Fila de totales
                $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '0f4c4a']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2F1']],
                ]);
                $sheet->getRowDimension($lastRow)->setRowHeight(18);

                // Bordes tabla
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
