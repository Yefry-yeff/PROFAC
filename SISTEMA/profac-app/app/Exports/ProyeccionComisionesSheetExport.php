<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
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
 * Una pestaña del export de Proyección de Comisiones.
 *
 * Columnas (A–N, 14 cols):
 *  A Fecha Pago          B Fecha Creación Factura   C Factura
 *  D Producto            E Cliente                  F Escala Cliente
 *  G Escala Precio       H Cantidad                 I Rol Comisión
 *  J Usuario             K Base Unit. Comisionable  L Base Comisionable
 *  M % Promedio          N Comisión Proyectada
 */
class ProyeccionComisionesSheetExport implements FromArray, WithTitle, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    const LAST_COL  = 'N';
    const COL_COUNT = 14;

    protected array  $rows;
    protected string $sheetTitle;
    protected string $empresa;
    protected string $periodo;
    protected string $generadoPor;

    public function __construct(array $rows, string $sheetTitle, string $empresa, string $periodo, string $generadoPor)
    {
        $this->rows        = $rows;
        $this->sheetTitle  = $sheetTitle;
        $this->empresa     = $empresa;
        $this->periodo     = $periodo;
        $this->generadoPor = $generadoPor;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function array(): array
    {
        $out = [];
        $lc  = self::LAST_COL;
        $nc  = self::COL_COUNT;

        // Fila 1 — empresa
        $r1    = array_fill(0, $nc, '');
        $r1[0] = $this->empresa;
        $out[] = $r1;

        // Fila 2 — título
        $r2    = array_fill(0, $nc, '');
        $r2[0] = 'PROYECCIÓN DE COMISIONES — ' . strtoupper($this->sheetTitle);
        $out[] = $r2;

        // Fila 3 — período, generado y quién descargó
        $r3    = array_fill(0, $nc, '');
        $r3[0] = 'Período: ' . $this->periodo . '     Descargado: ' . now()->format('d/m/Y H:i') . '     Por: ' . $this->generadoPor;
        $out[] = $r3;

        // Fila 4 — cabeceras
        $out[] = [
            'FECHA PAGO', 'FECHA CREACIÓN FACTURA', 'FACTURA',
            'PRODUCTO', 'CLIENTE', 'ESCALA CLIENTE', 'ESCALA PRECIO VENDIDA',
            'CANTIDAD', 'ROL COMISIÓN', 'USUARIO',
            'BASE UNIT. COMISIONABLE', 'BASE COMISIONABLE', '% PROMEDIO', 'COMISIÓN PROYECTADA',
        ];

        $totBase = $totCom = 0.0;

        foreach ($this->rows as $item) {
            $r    = (array) $item;
            $base = (float) ($r['base_comisionable']          ?? 0);
            $com  = (float) ($r['comision_proyectada']        ?? 0);
            $buni = (float) ($r['base_comisionable_unitaria'] ?? 0);
            $pct  = (float) ($r['porcentaje_promedio']        ?? 0);
            $cant = (float) ($r['cantidad']                   ?? 0);

            $totBase += $base;
            $totCom  += $com;

            $out[] = [
                $r['fecha_pago']               ?? '',
                $r['fecha_creacion_factura']   ?? '',
                $r['factura']                  ?? '',
                $r['producto']                 ?? '',
                $r['cliente']                  ?? '',
                $r['escala_cliente']           ?? '',
                $r['escala_precio_vendida']    ?? '',
                $cant,
                $r['rol_nombre']               ?? '',
                $r['usuario']                  ?? '',
                $buni,
                $base,
                $pct,
                $com,
            ];
        }

        // Fila totales
        $tot     = array_fill(0, $nc, '');
        $tot[0]  = 'TOTALES (' . count($this->rows) . ' registros)';
        $tot[11] = $totBase;
        $tot[13] = $totCom;
        $out[]   = $tot;

        return $out;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13, 'B' => 22, 'C' => 24,
            'D' => 42, 'E' => 36, 'F' => 18,
            'G' => 46, 'H' => 10, 'I' => 20,
            'J' => 24, 'K' => 22, 'L' => 20,
            'M' => 13, 'N' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lc = self::LAST_COL;

        $sheet->mergeCells("A1:{$lc}1");
        $sheet->mergeCells("A2:{$lc}2");
        $sheet->mergeCells("A3:{$lc}3");

        // Fila 1 — empresa
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Fila 2 — título naranja
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('e07000');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Fila 3 — período
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // Fila 4 — cabecera naranja con texto blanco
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

                $currency = '"L." #,##0.00';
                $percent  = '0.00"%"';
                $number   = '#,##0.00';

                // Color de roles para columna I
                $roleColors = [
                    'Asesor Comercial' => ['bg' => 'DCFCE7', 'fg' => '166534'],
                    'Televendedor'     => ['bg' => 'DBEAFE', 'fg' => '1e40af'],
                    'Gestor de Entrega'=> ['bg' => 'FEF9C3', 'fg' => '854d0e'],
                ];

                for ($row = 5; $row <= $lastRow; $row++) {
                    $aVal = (string) ($sheet->getCell("A{$row}")->getValue() ?? '');

                    // Fila totales
                    if (str_starts_with($aVal, 'TOTALES')) {
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(10)->getColor()->setRGB('7d3f00');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getBorders()->getTop()
                            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getBorders()->getBottom()
                            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                        foreach (['K', 'L', 'N'] as $c) {
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

                    // Texto a la izquierda
                    foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'I', 'J'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    // Numéricos
                    $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode($number);
                    $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode($currency);
                    $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode($currency);
                    $sheet->getStyle("L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("M{$row}")->getNumberFormat()->setFormatCode($percent);
                    $sheet->getStyle("M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("N{$row}")->getNumberFormat()->setFormatCode($currency);
                    $sheet->getStyle("N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("N{$row}")->getFont()->setBold(true);

                    // Color por rol en columna I
                    $rol = (string) ($sheet->getCell("I{$row}")->getValue() ?? '');
                    if (isset($roleColors[$rol])) {
                        $sheet->getStyle("I{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($roleColors[$rol]['bg']);
                        $sheet->getStyle("I{$row}")->getFont()->setBold(true)->getColor()->setRGB($roleColors[$rol]['fg']);
                    }

                    $sheet->getRowDimension($row)->setRowHeight(14);
                }

                // Borde exterior
                $sheet->getStyle("A1:{$lc}{$lastRow}")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('e07000');
            },
        ];
    }
}
