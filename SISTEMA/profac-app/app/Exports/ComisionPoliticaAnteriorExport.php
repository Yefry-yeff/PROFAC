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
 * Comisión Política Anterior — Detalle de líneas comisionadas
 *
 * Columnas (A-M, 13 cols):
 *  A Factura      B ID Factura   C Fecha Factura   D Fecha Pago
 *  E Cliente      F ID Producto  G Producto         H Tipo Pago
 *  I Subtotal Línea  J Clasificación  K % Aplicado  L Com. Total Línea  M Motivo
 */
class ComisionPoliticaAnteriorExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $data;
    protected string $titulo;
    protected string $periodo;

    const LAST_COL  = 'M';
    const COL_COUNT = 13;

    public function __construct(array $data, string $titulo = 'DETALLE COMISIÓN POLÍTICA ANTERIOR', string $periodo = '')
    {
        $this->data   = $data;
        $this->titulo = $titulo;
        $this->periodo = $periodo ?: now()->format('d/m/Y H:i');
    }

    public function array(): array
    {
        $out = [];

        // Fila 1 — empresa
        $r1    = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        // Fila 2 — título
        $r2    = array_fill(0, self::COL_COUNT, '');
        $r2[0] = strtoupper($this->titulo);
        $out[] = $r2;

        // Fila 3 — período / generado
        $r3    = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Generado: ' . $this->periodo;
        $out[] = $r3;

        // Fila 4 — cabeceras
        $out[] = [
            'FACTURA', 'ID FACTURA', 'FECHA FACTURA', 'FECHA PAGO',
            'CLIENTE', 'ID PRODUCTO', 'PRODUCTO', 'TIPO PAGO',
            'SUBTOTAL LÍNEA', 'CLASIFICACIÓN', '% APLICADO', 'COM. TOTAL LÍNEA', 'MOTIVO',
        ];

        $totSub = $totCom = 0.0;

        foreach ($this->data as $item) {
            $r   = (array) $item;
            $sub = (float) ($r['subtotal_linea']     ?? 0);
            $com = (float) ($r['comision_total_linea'] ?? 0);
            $pct = (float) ($r['porcentaje_aplicado'] ?? 0);

            $totSub += $sub;
            $totCom += $com;

            $out[] = [
                $r['factura']           ?? '',
                $r['factura_id']        ?? '',
                $r['fecha_factura']     ?? '',
                $r['fecha_pago_cierre'] ?? '',
                $r['cliente']           ?? '',
                $r['producto_id']       ?? '',
                $r['producto']          ?? '',
                $r['tipo_pago']         ?? '',
                $sub,
                $r['clasificacion']     ?? '',
                $pct,
                $com,
                $r['motivo_no_comision'] ?? '',
            ];
        }

        // Fila totales
        $tot    = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTALES';
        $tot[8] = $totSub;
        $tot[11] = $totCom;
        $out[]  = $tot;

        return $out;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, // Factura
            'B' => 12, // ID Factura
            'C' => 18, // Fecha Factura
            'D' => 14, // Fecha Pago
            'E' => 32, // Cliente
            'F' => 12, // ID Producto
            'G' => 40, // Producto
            'H' => 12, // Tipo Pago
            'I' => 16, // Subtotal Línea
            'J' => 20, // Clasificación
            'K' => 12, // % Aplicado
            'L' => 18, // Com. Total Línea
            'M' => 36, // Motivo
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lc = self::LAST_COL;

        // Filas 1–3 centradas y fusionadas
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

        // Fila 4 — cabecera naranja
        $sheet->getStyle("A4:{$lc}4")->getFont()->setBold(true)->setSize(8)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lc}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('e07000');
        $sheet->getStyle("A4:{$lc}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(28);

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
                $percent  = '0.00"%"';

                for ($row = 5; $row <= $lastRow; $row++) {
                    $aVal = (string) ($sheet->getCell("A{$row}")->getValue() ?? '');

                    // Fila totales
                    if ($aVal === 'TOTALES') {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(10)->getColor()->setRGB('7d3f00');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getBorders()->getTop()
                            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                        foreach (['I', 'L'] as $c) {
                            $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode($currency);
                            $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        }
                        $sheet->getRowDimension($row)->setRowHeight(18);
                        continue;
                    }

                    // Filas de datos — alternar color
                    $bg = ($row % 2 === 0) ? 'FFFBEB' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);

                    $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Columnas de texto alineadas a la izquierda
                    foreach (['A', 'C', 'D', 'E', 'G', 'H', 'J', 'M'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                    // Columnas numéricas
                    foreach (['B', 'F'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode($currency);
                    $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode($percent);
                    $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode($currency);
                    $sheet->getStyle("L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    // Comisión 0 o vacía = fila con motivo → gris
                    $comVal = (float) ($sheet->getCell("L{$row}")->getValue() ?? 0);
                    if ($comVal == 0) {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()->getColor()->setRGB('9CA3AF');
                    }

                    $sheet->getRowDimension($row)->setRowHeight(14);
                }

                // Borde exterior general
                $sheet->getStyle("A1:{$lc}{$lastRow}")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('e07000');
            },
        ];
    }
}
