<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReporteVentasCobrosHoja implements FromArray, WithTitle, WithStyles, WithDrawings, WithEvents
{
    protected $rows;
    protected $usuario;

    /* 27 columnas: A..AA */
    const LAST_COL  = 'AA';
    const COL_COUNT = 27;

    public function __construct($rows, $usuario = 'Sistema')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
    }

    public function title(): string { return 'Ventas y Cobros'; }

    public function array(): array
    {
        $out = [];

        // Row 1 – razón social
        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA S.A. DE C.V.   |   RTN: 08011986138652';
        $out[] = $r1;

        // Row 2 – título
        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'REPORTE DE VENTAS Y COBROS';
        $out[] = $r2;

        // Row 3 – fecha/usuario
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Generado: ' . now()->format('d/m/Y H:i') . '   |   Descargado por: ' . $this->usuario;
        $out[] = $r3;

        // Row 4 – cabeceras
        $out[] = [
            '#',
            'MES',
            'VENDEDOR',
            'CLIENTE',
            'FACTURA',
            'OBSERVACIÓN',
            'ORDEN COMPRA',
            'MODO PAGO',
            'ESTADO F01',
            'EXONERADO',
            'GRAVADO',
            'EXENTO',
            'ABONOS',
            'SUBTOTAL',
            'ISV',
            'TOTAL',
            'SALDO PENDIENTE',
            'MONTO PAGADO',
            'FECHA VENTA',
            'FECHA VCTO.',
            'DÍAS VCTOS.',
            'ESTADO CRÉDITO',
            'FECHA PAGO',
            'FORMA DE PAGO',
            'CUENTA/BANCO',
            'FECHA ENTREGA',
            'RECIBO',
        ];

        // Filas de datos (agrupadas por mes)
        $meActual = null;
        foreach ($this->rows as $r) {
            // Fila de agrupación por mes
            if ($r->mes !== $meActual) {
                $meActual = $r->mes;
                $gr = array_fill(0, self::COL_COUNT, '');
                $gr[0] = strtoupper($r->mes . ' ' . $r->anio);
                $out[] = $gr;
            }

            $out[] = [
                $r->item,
                strtoupper($r->mes),
                $r->vendedor,
                $r->cliente,
                $r->factura,
                $r->observacion,
                $r->orden_compra,
                $r->modo_pago,
                $r->estado_f01,
                $r->exonerado > 0  ? (float) $r->exonerado  : '',
                $r->gravado   > 0  ? (float) $r->gravado    : '',
                $r->exento    > 0  ? (float) $r->exento     : '',
                $r->abonos    > 0  ? (float) $r->abonos     : '',
                (float) $r->sub_total,
                (float) $r->isv,
                (float) $r->total,
                (float) $r->saldo_pendiente,
                $r->monto_pagado > 0 ? (float) $r->monto_pagado : '',
                $r->fecha_venta       ? date('d/m/Y', strtotime($r->fecha_venta))       : '',
                $r->fecha_vencimiento ? date('d/m/Y', strtotime($r->fecha_vencimiento)) : '',
                (int) $r->dias_vencidos,
                $r->creditos_vencidos,
                $r->fecha_pago      ? date('d/m/Y', strtotime($r->fecha_pago))      : '',
                $r->forma_pago,
                $r->cuenta_banco,
                $r->fecha_entrega   ? date('d/m/Y', strtotime($r->fecha_entrega))   : '',
                $r->recibo,
            ];
        }

        return $out;
    }

    public function drawings()
    {
        $d = new Drawing();
        $d->setName('Logo Valencia');
        $d->setPath(public_path('img/membrete/Logo3.png'));
        $d->setHeight(60);
        $d->setCoordinates('A1');
        $d->setOffsetX(4)->setOffsetY(4);
        return $d;
    }

    public function styles(Worksheet $sheet)
    {
        $lc = self::LAST_COL;

        // Fila 1 – empresa
        $sheet->mergeCells("A1:{$lc}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(65);

        // Fila 2 – título
        $sheet->mergeCells("A2:{$lc}2");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('1ab394');
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Fila 3 – generado por
        $sheet->mergeCells("A3:{$lc}3");
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // Fila 4 – cabeceras
        $sheet->getStyle("A4:{$lc}4")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("A4:{$lc}4")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lc}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1ab394');
        $sheet->getStyle("A4:{$lc}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(30);

        // Auto-size columnas
        foreach (range('A', 'Z') as $c) { $sheet->getColumnDimension($c)->setAutoSize(true); }
        $sheet->getColumnDimension('AA')->setAutoSize(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lc      = self::LAST_COL;

                // Columnas de moneda (J..R = índices 9..17 → J,K,L,M,N,O,P,Q,R)
                $moneyCols = ['J','K','L','M','N','O','P','Q','R'];
                // Columnas de fecha
                $dateCols  = ['S','T','W','Z'];
                // Columnas de texto izquierda
                $leftCols  = ['C','D','F','G','H','I','V','X','Y','AA'];

                for ($row = 5; $row <= $lastRow; $row++) {
                    $cellA = $sheet->getCell("A{$row}")->getValue();

                    // Fila de agrupación por mes
                    if (!is_numeric($cellA)) {
                        $sheet->mergeCells("A{$row}:{$lc}{$row}");
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F3864');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getRowDimension($row)->setRowHeight(16);
                        continue;
                    }

                    // Alinear centro por defecto
                    $sheet->getStyle("A{$row}:{$lc}{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Alinear izquierda texto
                    foreach ($leftCols as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    // Formato moneda
                    foreach ($moneyCols as $c) {
                        $val = $sheet->getCell("{$c}{$row}")->getValue();
                        if ($val !== '' && $val !== null) {
                            $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('"L" #,##0.00');
                        }
                    }

                    // Formato días vencidos (columna U = índice 21)
                    $diasVal = $sheet->getCell("U{$row}")->getValue();
                    if ($diasVal !== '' && $diasVal !== null) {
                        $sheet->getStyle("U{$row}")->getNumberFormat()->setFormatCode('0" días"');
                    }

                    // Colorear estado crédito (columna V = índice 22)
                    $estado = $sheet->getCell("V{$row}")->getValue();
                    $bgEstado = match($estado) {
                        'Vencida'   => 'FADBD8',
                        'Cancelada' => 'D5F5E3',
                        'Contado'   => 'D6EAF8',
                        default     => 'FDFEFE',
                    };
                    $sheet->getStyle("V{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgEstado);

                    // Colores alternados de fila
                    $bg = ($row % 2 === 0) ? 'E8F7F5' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);

                    $sheet->getRowDimension($row)->setRowHeight(15);
                }

                // Bordes bloque datos
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B2DDD5');
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('1ab394');
                $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0d8a77');
            },
        ];
    }
}
