<?php

namespace App\Exports\Comisiones;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Pestaña "Política Anterior" en la nómina proyectada.
 * Columnas: FACTURA, ID FACTURA, FECHA FACTURA, FECHA PAGO, CLIENTE, ID PRODUCTO, PRODUCTO,
 *           TIPO PAGO, SUBTOTAL LÍNEA, CLASIFICACIÓN, % APLICADO,
 *           COM. TOTAL LÍNEA, MOTIVO
 */
class PoliticaAnteriorDetalleSheet implements FromArray, WithTitle, WithEvents, WithStrictNullComparison
{
    protected array  $rows;
    protected string $empresa;
    protected string $periodo;
    protected string $generadoPor;

    const HEADERS = [
        'FACTURA', 'ID FACTURA', 'FECHA FACTURA', 'FECHA PAGO', 'CLIENTE', 'ID PRODUCTO', 'PRODUCTO',
        'TIPO PAGO', 'SUBTOTAL LÍNEA', 'CLASIFICACIÓN', '% APLICADO',
        'COM. TOTAL LÍNEA', 'MOTIVO',
    ];
    const LAST_COL  = 'M';
    const COL_COUNT = 13;

    public function __construct(array $rows, string $empresa, string $periodo, string $generadoPor)
    {
        $this->rows        = $rows;
        $this->empresa     = $empresa;
        $this->periodo     = $periodo;
        $this->generadoPor = $generadoPor;
    }

    public function title(): string
    {
        return 'Política Anterior';
    }

    public function array(): array
    {
        $nc  = self::COL_COUNT;
        $out = [];

        // Fila 1 — empresa
        $r1    = array_fill(0, $nc, '');
        $r1[0] = $this->empresa;
        $out[] = $r1;

        // Fila 2 — título
        $r2    = array_fill(0, $nc, '');
        $r2[0] = 'COMISIONES POLÍTICA ANTERIOR — DETALLE POR LÍNEA';
        $out[] = $r2;

        // Fila 3 — período / generado
        $r3    = array_fill(0, $nc, '');
        $r3[0] = 'Período: ' . $this->periodo . '     Descargado: ' . now()->format('d/m/Y H:i') . '     Por: ' . $this->generadoPor;
        $out[] = $r3;

        // Fila 4 — cabeceras
        $out[] = self::HEADERS;

        // Filas de datos
        foreach ($this->rows as $row) {
            $r = (array) $row;
            $out[] = [
                $r['factura']            ?? '',
                $r['factura_id']         ?? '',
                $r['fecha_factura']      ?? '',
                $r['fecha_pago_cierre']  ?? '',
                $r['cliente']            ?? '',
                $r['producto_id']        ?? '',
                $r['producto']           ?? '',
                $r['tipo_pago']          ?? '',
                (float) ($r['subtotal_linea']        ?? 0),
                $r['clasificacion']      ?? '',
                (float) ($r['porcentaje_aplicado']   ?? 0),
                (float) ($r['comision_total_linea']  ?? 0),
                $r['motivo_no_comision'] ?? '',
            ];
        }

        return $out;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lc    = self::LAST_COL;

                // Merge filas 1-3
                $sheet->mergeCells('A1:' . $lc . '1');
                $sheet->mergeCells('A2:' . $lc . '2');
                $sheet->mergeCells('A3:' . $lc . '3');

                // Fila 1 — empresa (igual que otras pestañas: texto azul oscuro)
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1F3864');
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Fila 2 — título (igual que otras pestañas: texto naranja)
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('e07000');
                $sheet->getStyle('A2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // Fila 3 — período
                $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(3)->setRowHeight(16);

                // Fila 4 — cabeceras (fondo naranja, texto blanco)
                $sheet->getStyle('A4:' . $lc . '4')->getFont()
                    ->setBold(true)->setSize(8)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle('A4:' . $lc . '4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('e07000');
                $sheet->getStyle('A4:' . $lc . '4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getRowDimension(4)->setRowHeight(28);

                // Filas de datos
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 5) {
                    $moneyFmt = '"L." #,##0.00';
                    $pctFmt   = '0.00"%"';

                    for ($row = 5; $row <= $lastRow; $row++) {
                        $bg = ($row % 2 === 0) ? 'FFF3E0' : 'FFFFFF';
                        $sheet->getStyle('A' . $row . ':' . $lc . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                        $sheet->getStyle('A' . $row . ':' . $lc . $row)->getFont()->setSize(9);
                        $sheet->getStyle('A' . $row . ':' . $lc . $row)->getBorders()->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('FFD580');

                        // Columna I (subtotal), K (%), L (comisión) — formato y alineación derecha
                        $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode($moneyFmt);
                        $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode($pctFmt);
                        $sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode($moneyFmt);
                        $sheet->getStyle('L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                }

                // Anchos de columna
                $widths = ['A'=>16,'B'=>10,'C'=>20,'D'=>14,'E'=>32,'F'=>10,'G'=>42,'H'=>12,'I'=>14,'J'=>16,'K'=>12,'L'=>14,'M'=>40];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                // Auto-filter y freeze
                $sheet->setAutoFilter('A4:' . $lc . '4');
                $sheet->freezePane('A5');
            },
        ];
    }
}
