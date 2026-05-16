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

class ReporteClientesHoja1 implements FromArray, WithTitle, WithStyles, WithDrawings, WithEvents
{
    protected $rows;
    protected $usuario;

    /* Column letter of last data column — 34 columns total (A..AH) */
    const LAST_COL = 'AH';
    const COL_COUNT = 34; // item + 33 data cols

    public function __construct($rows, $usuario = 'Sistema')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
    }

    public function title(): string { return 'Clientes Generales'; }

    public function array(): array
    {
        $out = [];
        // Row 1: company name (will be merged)
        $out[] = array_fill(0, self::COL_COUNT, '');
        $out[0][0] = 'DISTRIBUCIONES VALENCIA S.A. DE C.V.   |   RTN: 08011986138652';

        // Row 2: report title
        $titleRow = array_fill(0, self::COL_COUNT, '');
        $titleRow[0] = 'REPORTE DE CLIENTES EN GENERAL';
        $out[] = $titleRow;

        // Row 3: generation date + user
        $dateRow = array_fill(0, self::COL_COUNT, '');
        $dateRow[0] = 'Generado: ' . now()->format('d/m/Y H:i') . '   |   Descargado por: ' . $this->usuario;
        $out[] = $dateRow;

        // Row 4: column headers
        $out[] = [
            '#', 'AÑO INGRESO', 'VENDEDOR', 'CLIENTE', 'CÓDIGO',
            'SOL. CRÉDITO', 'COND. CRÉDITO',
            'ESCRITURA', 'DNI REP. LEGAL', 'RTN', 'PERMISO OPERAC.',
            'AÑO INICIO OP.', 'CROQUIS',
            'REF. BANCARIAS', 'REF. COMERCIALES',
            'REFERENCIA REF.', 'T. RELACIÓN REF.',
            'T. CRÉDITO REF.', 'LÍM. CRÉD. REF.',
            'MÉTODO PAGO', 'CONFIRMACIÓN',
            'OBS. REFERENCIAS', 'FECHA VAL. REF.', 'REALIZÓ',
            'LETRAS CAMBIO', 'AVAL SOLIDARIO',
            'CONTRATO ARR.', 'FOTOS ESTAB.',
            'ESTADO CLIENTE', 'MONTO CRÉDITO', 'PLAZO CRÉDITO',
            'OBSERVACIONES', 'AUTORIZADO GER.', 'F. NOTIF. LÍMITE',
        ];

        // Data rows
        foreach ($this->rows as $r) {
            $out[] = [
                $r->item,
                $r->anio_ingreso,
                $r->vendedor,
                $r->cliente,
                $r->codigo,
                $r->solicitud_credito,
                $r->condiciones_credito,
                $r->doc_escritura,
                $r->doc_dni,
                $r->doc_rtn,
                $r->doc_permiso,
                $r->anio_operacion,
                $r->doc_croquis,
                $r->ref_bancarias,
                $r->ref_comerciales,
                $r->ref_referencias,
                $r->ref_tiempo_relacion,
                $r->ref_tiempo_credito,
                $r->ref_limite_credito ? (float) $r->ref_limite_credito : '',
                $r->metodo_pago,
                $r->confirmacion,
                $r->obs_referencias,
                $r->fecha_validacion_ref ? date('d/m/Y', strtotime($r->fecha_validacion_ref)) : '',
                $r->realizo,
                $r->letra_cambio,
                $r->aval_solidario,
                $r->doc_contrato,
                $r->doc_fotos,
                $r->estado_cliente,
                $r->monto_credito > 0 ? (float) $r->monto_credito : '',
                $r->plazo_credito  > 0 ? (int)   $r->plazo_credito  : '',
                $r->observaciones,
                $r->autorizado_gerencia,
                $r->fecha_notif_limite ? date('d/m/Y', strtotime($r->fecha_notif_limite)) : '',
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
        $lastCol = self::LAST_COL;

        // Row 1 — company name
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(65);

        // Row 2 — report title
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('1ab394');
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Row 3 — date
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // Row 4 — column headers
        $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("A4:{$lastCol}4")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lastCol}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1ab394');
        $sheet->getStyle("A4:{$lastCol}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Auto size
        foreach (range('A', 'Z') as $c) { $sheet->getColumnDimension($c)->setAutoSize(true); }
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);
        $sheet->getColumnDimension('AE')->setAutoSize(true);
        $sheet->getColumnDimension('AF')->setAutoSize(true);
        $sheet->getColumnDimension('AG')->setAutoSize(true);
        $sheet->getColumnDimension('AH')->setAutoSize(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = self::LAST_COL;

                // Columns with currency (S=monto L.19, S is col index 30 → column 'AD')
                // Numeric cols: S (lim credito, col T=col19+1=col 19 → 'S'), AD (monto credito=col30)
                // We'll apply by column letter:
                $currencyCols = ['S', 'AD']; // ref_limite_credito, monto_credito
                $intCols      = ['AE'];      // plazo_credito

                for ($row = 5; $row <= $lastRow; $row++) {
                    // Center all cells
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(false);

                    // Left-align text columns
                    foreach (['C','D','P','Q','V','W','AF','AG'] as $c) {
                        $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    // Currency format
                    foreach ($currencyCols as $c) {
                        $val = $sheet->getCell("{$c}{$row}")->getValue();
                        if ($val !== '' && $val !== null) {
                            $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('"L" #,##0.00');
                        }
                    }

                    // Int days format
                    foreach ($intCols as $c) {
                        $val = $sheet->getCell("{$c}{$row}")->getValue();
                        if ($val !== '' && $val !== null) {
                            $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('0" días"');
                        }
                    }

                    // Alternating row colors
                    $bg = ($row % 2 === 0) ? 'E8F7F5' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);

                    $sheet->getRowDimension($row)->setRowHeight(15);
                }

                // Borders on data+header block
                $range = "A4:{$lastCol}{$lastRow}";
                $sheet->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B2DDD5');
                $sheet->getStyle($range)->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('1ab394');
                $sheet->getStyle("A4:{$lastCol}4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0d8a77');

                // Footer logo
                $footerRow = $lastRow + 3;
                $fd = new Drawing();
                $fd->setPath(public_path('img/membrete/Logo3.png'));
                $fd->setHeight(50);
                $fd->setCoordinates('M' . $footerRow);
                $fd->setOffsetX(10)->setOffsetY(5);
                $fd->setWorksheet($sheet);
            },
        ];
    }
}
