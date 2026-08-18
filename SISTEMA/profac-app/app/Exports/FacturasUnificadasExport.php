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
 * Listado de Facturas Unificado
 *
 * Columnas (A–M, 13 cols):
 *  A Tipo       B N° Factura  C Fecha       D Cliente    E Tipo Pago
 *  F Gravado    G Exento      H Exonerado   I Subtotal   J ISV
 *  K Total      L Estado      M Vendedor
 */
class FacturasUnificadasExport implements FromArray, WithStyles, WithEvents, WithStrictNullComparison, WithColumnWidths
{
    protected array $data;
    protected string $tipoLabel;
    protected string $fechaInicio;
    protected string $fechaFinal;

    const LAST_COL  = 'M';
    const COL_COUNT = 13;

    public function __construct($data, string $tipoLabel, string $fechaInicio = '', string $fechaFinal = '')
    {
        $this->data       = is_array($data) ? $data : json_decode(json_encode($data), true);
        $this->tipoLabel  = $tipoLabel;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal  = $fechaFinal;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, // Tipo
            'B' => 26, // N° Factura
            'C' => 14, // Fecha
            'D' => 34, // Cliente
            'E' => 14, // Tipo Pago
            'F' => 13, // Gravado
            'G' => 13, // Exento
            'H' => 13, // Exonerado
            'I' => 13, // Subtotal
            'J' => 12, // ISV
            'K' => 14, // Total
            'L' => 11, // Estado
            'M' => 22, // Vendedor
        ];
    }

    public function array(): array
    {
        $out = [];

        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'LISTADO DE FACTURAS — ' . strtoupper($this->tipoLabel);
        $out[] = $r2;

        $r3 = array_fill(0, self::COL_COUNT, '');
        $periodo = ($this->fechaInicio && $this->fechaFinal)
            ? 'Período: ' . $this->fechaInicio . '  a  ' . $this->fechaFinal . '     '
            : '';
        $r3[0] = $periodo . 'Generado: ' . now()->format('d/m/Y H:i');
        $out[] = $r3;

        $out[] = [
            'TIPO', 'N° FACTURA', 'FECHA', 'CLIENTE', 'TIPO PAGO',
            'GRAVADO', 'EXENTO', 'EXONERADO', 'SUBTOTAL', 'ISV',
            'TOTAL', 'ESTADO', 'VENDEDOR',
        ];

        $totGrav = $totExen = $totExon = $totSub = $totIsv = $totTotal = 0.0;

        foreach ($this->data as $item) {
            $r = (array) $item;

            $tipoVentaMap = [1 => 'Cliente B', 2 => 'Cliente A', 3 => 'Exonerada', 4 => 'Pedido'];
            $tipo  = $tipoVentaMap[(int)($r['tipo_venta_id'] ?? 0)] ?? ($r['tipo_label'] ?? '');

            $grav  = (float) str_replace(',', '', $r['gravado']   ?? 0);
            $exen  = (float) str_replace(',', '', $r['exento']    ?? 0);
            $exon  = (float) str_replace(',', '', $r['exonerado'] ?? 0);
            $sub   = (float) str_replace(',', '', $r['sub_total'] ?? 0);
            $isv   = (float) str_replace(',', '', $r['isv']       ?? 0);
            $total = (float) str_replace(',', '', $r['total']     ?? 0);

            $totGrav  += $grav;
            $totExen  += $exen;
            $totExon  += $exon;
            $totSub   += $sub;
            $totIsv   += $isv;
            $totTotal += $total;

            $out[] = [
                $tipo,
                $r['cai']          ?? '',
                $r['fecha_emision'] ?? '',
                $r['nombre']       ?? '',
                $r['descripcion']  ?? '',
                $grav, $exen, $exon, $sub, $isv, $total,
                $r['estado_cobro_raw'] ?? ($r['credito'] == 0 ? 'Contado' : 'Crédito'),
                $r['vendedor']     ?? $r['creado_por'] ?? '',
            ];
        }

        $tot = array_fill(0, self::COL_COUNT, '');
        $tot[0] = 'TOTALES:';
        $tot[5]  = round($totGrav,  2);
        $tot[6]  = round($totExen,  2);
        $tot[7]  = round($totExon,  2);
        $tot[8]  = round($totSub,   2);
        $tot[9]  = round($totIsv,   2);
        $tot[10] = round($totTotal, 2);
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
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1F3864']],
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

                // Formatos numéricos por columna (F–K)
                $numFmt = '#,##0.00';
                foreach (['F', 'G', 'H', 'I', 'J', 'K'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Alineación general datos
                $sheet->getStyle("A5:{$lc}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Izquierda: cliente, vendedor
                foreach (['D', 'M'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
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

                // Auto-filter en cabecera
                $sheet->setAutoFilter("A4:{$lc}4");
                $sheet->freezePane('A5');
            },
        ];
    }
}
