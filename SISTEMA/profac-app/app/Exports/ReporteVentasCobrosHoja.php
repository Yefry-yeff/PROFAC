<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Hoja del reporte Ventas & Cobros (v6).
 *
 * 30 columnas (A..AD):
 *  A(0)   #               B(1)   MES             C(2)   FECHA
 *  D(3)   USUARIO         E(4)   CLIENTE         F(5)   DOCUMENTO
 *  G(6)   TIPO DOCUMENTO  H(7)   NRO DOCUMENTO   I(8)   OBSERVACION
 *  J(9)   ORDEN COMPRA    K(10)  MODO PAGO       L(11)  ESTADO F01
 *  M(12)  EXONERADO       N(13)  GRAVADO         O(14)  EXENTO
 *  P(15)  SUBTOTAL        Q(16)  ISV             R(17)  TOTAL
 *  S(18)  DISMINUCION     T(19)  AUMENTO         U(20)  MONTO PAGADO
 *  V(21)  SALDO PENDIENTE W(22)  ESTADO COBRO    X(23)  FECHA VENTA
 *  Y(24)  FECHA VCTO.     Z(25)  DIAS VCTOS.     AA(26) FECHA PAGO
 *  AB(27) FORMA DE PAGO   AC(28) BANCO           AD(29) CUENTA
 *
 * Reglas:
 *  - DEBITOS  (T): monto de movimientos que DISMINUYEN el saldo
 *                  (ABONO, PAGO, NOTA_CREDITO, VALE, RETENCION)
 *  - CREDITOS (U): monto de movimientos que AUMENTAN el saldo
 *                  (NOTA_DEBITO)
 *  - SALDO PENDIENTE: siempre numerico, muestra 0.00 cuando esta saldado
 *  - MONTO PAGADO (factura): suma de todos los abonos ($r->abonos)
 *  - DIAS VCTOS. (factura): celda verde si <= 0, roja si > 0
 *  - Sub-filas: sin fondo; celda DEBITOS en rojo claro, celda CREDITOS en verde claro
 */
class ReporteVentasCobrosHoja implements FromArray, WithTitle, WithStyles, WithDrawings, WithEvents, WithStrictNullComparison
{
    protected $rows;
    protected $usuario;
    protected $movimientos;
    protected $fastMode;
    protected $superFastMode;
    protected $rowMeta = [];

    const LAST_COL  = 'AD';
    const COL_COUNT = 30;

    const T_FACTURA   = 'FACTURA';
    const T_ENTREGA   = 'ENTREGA';
    const T_ABONO     = 'ABONO';
    const T_PAGO      = 'PAGO';
    const T_NOTA_C    = 'NOTA_CREDITO';
    const T_NOTA_D    = 'NOTA_DEBITO';
    const T_VALE      = 'VALE';
    const T_RETENCION = 'RETENCION';

    // DEBITO  = ajuste que disminuye el saldo (Nota Credito, Vale, Retencion) → col T
    // PAGO    = cobro que disminuye el saldo (Abono, Pago Contado) → col V (MONTO PAGADO)
    // CREDITO = ajuste que aumenta el saldo (Nota Debito) → col U
    private static $TIPOS_DEBITO  = ['NOTA_CREDITO', 'VALE', 'RETENCION'];
    private static $TIPOS_CREDITO = ['NOTA_DEBITO'];
    private static $TIPOS_PAGO    = ['ABONO', 'PAGO'];

    private static $TIPO_LABEL = [
        'VENTA'        => 'Factura',
        'FACTURA'      => 'Factura',
        'ENTREGA'      => 'Entrega',
        'ABONO'        => 'Abono',
        'PAGO'         => 'Pago Contado',
        'NOTA_CREDITO' => 'Nota de Credito',
        'NOTA_DEBITO'  => 'Nota de Debito',
        'VALE'         => 'Vale',
        'RETENCION'    => 'Retencion ISV',
    ];

    private static $MESES = [
        1 => 'Enero',    2 => 'Febrero',  3 => 'Marzo',     4 => 'Abril',
        5 => 'Mayo',     6 => 'Junio',    7 => 'Julio',     8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function __construct($rows, $usuario = 'Sistema', $movimientos = [], $fastMode = false, $superFastMode = false)
    {
        $this->rows          = $rows;
        $this->usuario       = $usuario;
        $this->movimientos   = $movimientos;
        $this->fastMode      = (bool) $fastMode;
        $this->superFastMode = (bool) $superFastMode;
    }

    public function title(): string { return 'Ventas y Cobros'; }

    private function fmt(?string $d): string
    {
        if (!$d) return '';
        $ts = strtotime($d);
        return $ts ? date('d/m/Y', $ts) : '';
    }

    private function excelDate($d)
    {
        if (!$d) return '';
        $ts = strtotime((string) $d);
        return $ts ? ExcelDate::PHPToExcel($ts) : '';
    }

    private function mesNombre(?string $d): string
    {
        if (!$d) return '';
        $ts = strtotime($d);
        if (!$ts) return '';
        return self::$MESES[(int) date('n', $ts)] ?? '';
    }

    private function tipoLabel(string $tipo): string
    {
        return self::$TIPO_LABEL[$tipo] ?? ucfirst(strtolower($tipo));
    }

    private function money($value): float
    {
        $number = (float) ($value ?? 0);
        if (abs($number) < 0.005) {
            return 0.0;
        }

        return round($number, 2);
    }

    /* ─────────────────────────────────────────────────────────────── */

    public function array(): array
    {
        $this->rowMeta = [];
        $out  = [];
        $item = 0;

        // Acumuladores de totales (solo filas FACTURA)
        $totExon   = 0.0;
        $totGrav   = 0.0;
        $totExen   = 0.0;
        $totSub    = 0.0;
        $totIsv    = 0.0;
        $totTotal  = 0.0;
        $totDeb    = 0.0;
        $totCred   = 0.0;
        $totPagado = 0.0;
        $totSaldo  = 0.0;

        /* Fila 1 */
        $r1 = array_fill(0, self::COL_COUNT, '');
        $r1[0] = 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652';
        $out[] = $r1;

        /* Fila 2 */
        $r2 = array_fill(0, self::COL_COUNT, '');
        $r2[0] = 'REPORTE DE VENTAS Y COBROS';
        $out[] = $r2;

        /* Fila 3 */
        $r3 = array_fill(0, self::COL_COUNT, '');
        $r3[0] = 'Generado: ' . now()->format('d/m/Y H:i') . '   |   Descargado por: ' . $this->usuario;
        $out[] = $r3;

        /* Fila 4 – cabeceras */
        $out[] = [
            '#','MES','FECHA','USUARIO','CLIENTE',
            'DOCUMENTO','TIPO DOCUMENTO','NRO DOCUMENTO','OBSERVACION','ORDEN COMPRA',
            'CONDICION DE VENTA','ESTADO F01','EXONERADO','GRAVADO','EXENTO',
            'SUBTOTAL','ISV','TOTAL','DISMINUCION EN FACT.','AUMENTO EN FACT.',
            'MONTO PAGADO','SALDO PENDIENTE','ESTADO COBRO','FECHA VENTA',
            'FECHA VCTO.','DIAS VCTOS.','FECHA PAGO','FORMA DE PAGO','BANCO',
            'CUENTA',
        ];

        foreach ($this->rows as $r) {
            $factId      = (int)($r->factura_id ?? 0);
            $facturaNum  = $r->numero_secuencia_cai ?? '';
            $movs         = $this->movimientos[$factId] ?? [];
            $saldoFactura = (float)($r->total ?? 0); // progresivo: se mantiene lineal para cuadrar totales
            $pagosAcum    = (float)($r->pagos_directos ?? 0);
            $dias         = (int)($r->dias_vencidos ?? 0);
            $estadoCobro = $r->estado_cobro_v2 ?? ($r->creditos_vencidos ?? '');
            $estadoF01 = strtoupper(trim((string)($r->estado_f01 ?? '')));
            $esAnulada = str_starts_with($estadoF01, 'ANULAD');

            /* ── PRE-CALCULAR TOTALES DEBITOS / CREDITOS ───── */
            $totalDebitos  = 0.0;
            $totalCreditos = 0.0;
            $totalPagos    = (float)($r->pagos_directos ?? 0);
            $_sfCalc       = (float)($r->total ?? 0);
            foreach ($movs as $_mov) {
                if ($_mov->tipo === 'VENTA') continue;
                $_monto = (float)($_mov->monto ?? 0);
                if (in_array($_mov->tipo, self::$TIPOS_DEBITO)) {
                    $totalDebitos += $_monto;
                    $_sfCalc      -= $_monto; // saldo factura baja
                }
                if (in_array($_mov->tipo, self::$TIPOS_CREDITO)) {
                    $totalCreditos += $_monto;
                    $_sfCalc      += $_monto;                   // saldo factura sube
                }
                if (in_array($_mov->tipo, self::$TIPOS_PAGO)) {
                    $totalPagos += $_monto;  // pagos NO afectan saldo de factura
                }
            }
            // Sumar retencion ISV al total de debitos
            $_montoRet = (float)($r->monto_retencion ?? 0);
            if ($_montoRet > 0) { $totalDebitos += $_montoRet; $_sfCalc -= $_montoRet; }
            $finalSaldoFactura   = $this->money($_sfCalc);
            $finalSaldoPendiente = $this->money($finalSaldoFactura - $totalPagos);

            /* ── FILA FACTURA ──────────────────────────────── */
            $item++;
            $excelRow = count($out) + 1;
            if (!$this->fastMode && !$this->superFastMode) {
                $this->rowMeta[$excelRow] = [
                    'type'          => self::T_FACTURA,
                    'estado_cobro'  => $estadoCobro,
                    'estado_f01'    => $r->estado_f01 ?? '',
                    'dias_vencidos' => $dias,
                ];
            }

            $row = array_fill(0, self::COL_COUNT, '');
            $row[0]  = $item;
            $row[1]  = strtoupper($r->mes ?? '');
            $row[2]  = $this->excelDate($r->fecha_venta);
            $row[3]  = $r->vendedor ?? '';
            $row[4]  = $r->cliente ?? '';
            $row[5]  = $facturaNum;
            $row[6]  = 'Factura';
            $row[7]  = '';
            $row[8]  = $r->observacion ?? '';
            $row[9]  = (strtotime($r->fecha_venta ?? '') >= strtotime('2026-05-15'))
                        ? (trim($r->flujo_orden_compra ?? '') ?: ($r->orden_compra ?? ''))
                        : ($r->orden_compra ?? '');
            $row[10] = $r->modo_pago ?? '';
            $row[11] = trim($r->flujo_forma_f01 ?? '') ?: 'N/A';
            $row[12] = $esAnulada ? '' : ($this->money($r->exonerado ?? 0) > 0 ? $this->money($r->exonerado ?? 0) : '');
            $row[13] = $esAnulada ? '' : ($this->money($r->gravado ?? 0) > 0 ? $this->money($r->gravado ?? 0) : '');
            $row[14] = $esAnulada ? '' : ($this->money($r->exento ?? 0) > 0 ? $this->money($r->exento ?? 0) : '');
            $row[15] = $esAnulada ? '' : $this->money($r->sub_total ?? 0);
            $row[16] = $esAnulada ? '' : $this->money($r->isv ?? 0);
            $row[17] = $esAnulada ? '' : $this->money($r->total ?? 0);
            $row[18] = $this->money($totalDebitos) > 0 ? -$this->money($totalDebitos) : '';  // DISMINUCION EN FACT. (negativo)
            $row[19] = $this->money($totalCreditos) > 0 ? $this->money($totalCreditos) : '';   // AUMENTO EN FACT.
            // MONTO PAGADO: negativo (es un egreso)
            $_pagosVal = $totalPagos > 0 ? $totalPagos : (
                       (float)($r->abonos ?? 0) > 0 ? (float)$r->abonos : (
                       (float)($r->monto_pagado ?? 0) > 0 ? (float)$r->monto_pagado : 0
                       ));
            $_pagosVal = $this->money($_pagosVal);
            $row[20] = $_pagosVal > 0 ? -$_pagosVal : '';
            $row[21] = $esAnulada ? '' : $finalSaldoPendiente; // SALDO PENDIENTE
            $row[22] = $estadoCobro;         // ESTADO COBRO
            $row[23] = $esAnulada ? '' : $this->excelDate($r->fecha_venta);
            $row[24] = $esAnulada ? '' : $this->excelDate($r->fecha_vencimiento);
            $row[25] = $esAnulada ? '' : $dias;                // DIAS VCTOS.
            $row[26] = '';
            $row[27] = '';
            $row[28] = '';
            $row[29] = '';
            $out[] = $row;

            // Acumular totales de fila factura
            $totExon   += $this->money($r->exonerado ?? 0);
            $totGrav   += $this->money($r->gravado ?? 0);
            $totExen   += $this->money($r->exento ?? 0);
            $totSub    += $this->money($r->sub_total ?? 0);
            $totIsv    += $this->money($r->isv ?? 0);
            $totTotal  += $this->money($r->total ?? 0);
            $totDeb    += $this->money($totalDebitos);
            $totCred   += $this->money($totalCreditos);
            $totPagado += $_pagosVal;
            $totSaldo  += $finalSaldoPendiente;

            /* ── MOVIMIENTOS ───────────────────────────────── */
            foreach ($movs as $mov) {
                if ($mov->tipo === 'VENTA') continue;

                $tipo  = $mov->tipo;
                $monto = (float)($mov->monto ?? 0);

                $esDebito  = in_array($tipo, self::$TIPOS_DEBITO);
                $esCredito = in_array($tipo, self::$TIPOS_CREDITO);
                $esPago    = in_array($tipo, self::$TIPOS_PAGO);

                // Actualizar saldo progresivo
                if ($esDebito)       $saldoFactura -= $monto;
                elseif ($esCredito)  $saldoFactura += $monto;
                elseif ($esPago)     $pagosAcum   += $monto;
                // ENTREGA no cambia saldo
                $saldoPendiente = $this->money($saldoFactura - $pagosAcum);

                // Ocultar subfilas de Pago Contado en el Excel,
                // pero conservar su impacto en los calculos.
                if ($tipo === self::T_PAGO) {
                    continue;
                }

                $item++;
                $excelRow = count($out) + 1;
                if (!$this->fastMode && !$this->superFastMode) {
                    $this->rowMeta[$excelRow] = [
                        'type'      => $tipo,
                        'dir'       => $esDebito ? 'debito' : ($esCredito ? 'credito' : ($esPago ? 'pago' : 'neutral')),
                        'saldo_dir' => $esDebito ? 'down' : ($esCredito ? 'up' : 'neutral'),
                        'has_monto' => ($tipo !== 'ENTREGA' && $monto > 0),
                    ];
                }

                $movRow = array_fill(0, self::COL_COUNT, '');
                $movRow[0]  = $item;
                $movRow[1]  = $this->mesNombre($mov->fecha);
                $movRow[2]  = $this->excelDate($mov->fecha);
                $movRow[3]  = $mov->responsable ?? '';
                $movRow[4]  = $r->cliente ?? '';
                $movRow[5]  = $facturaNum;
                $movRow[6]  = $this->tipoLabel($tipo);
                $movRow[7]  = trim((string)($mov->documento ?? ''));
                $movRow[8]  = $mov->descripcion ?? '';
                // cols 9-17: datos de factura en blanco (TOTAL solo en factura, no en sub-filas)
                $monto = $this->money($monto);
                $movRow[18] = $esDebito  ? -$monto : ''; // DISMINUCION EN FACT. (negativo)
                $movRow[19] = $esCredito ? $monto  : ''; // AUMENTO EN FACT.
                $movRow[20] = $esPago    ? -$monto : ''; // MONTO PAGADO (negativo)
                $movRow[21] = $saldoPendiente; // SALDO PENDIENTE siempre
                $movRow[22] = '';              // ESTADO COBRO: en blanco en sub-filas
                // cols 23-25: fechas venta/vcto/dias en blanco
                $movRow[26] = $this->excelDate($mov->fecha); // FECHA PAGO
                $movRow[27] = $mov->forma_pago ?? '';
                $movRow[28] = $mov->banco_nombre ?? ''; // BANCO
                $movRow[29] = $mov->banco_cuenta ?? ''; // CUENTA
                $out[] = $movRow;
            }

            /* ── RETENCIÓN ISV ─────────────────────────────── */
            $montoRet = (float)($r->monto_retencion ?? 0);
            if ($montoRet > 0) {
                $saldoFactura   -= $montoRet;
                $saldoPendiente = $this->money($saldoFactura - $pagosAcum);
                $item++;
                $excelRow = count($out) + 1;
                if (!$this->fastMode && !$this->superFastMode) {
                    $this->rowMeta[$excelRow] = [
                        'type'      => self::T_RETENCION,
                        'dir'       => 'debito',
                        'saldo_dir' => 'down',
                        'has_monto' => true,
                    ];
                }

                $retRow = array_fill(0, self::COL_COUNT, '');
                $retRow[0]  = $item;
                $retRow[1]  = $r->fecha_retencion ? $this->mesNombre($r->fecha_retencion) : '';
                $retRow[2]  = $this->excelDate($r->fecha_retencion ?? '');
                $retRow[3]  = $r->usuario_retencion ?? '';
                $retRow[4]  = $r->cliente ?? '';
                $retRow[5]  = $facturaNum;
                $retRow[6]  = 'Retencion ISV';
                $retRow[7]  = trim((string)($r->numero_retencion ?? ''));
                $retRow[8]  = 'Retencion ISV aplicada';
                $retRow[18] = -$this->money($montoRet);      // DISMINUCION EN FACT. (negativo)
                $retRow[19] = '';              // AUMENTO EN FACT.
                $retRow[20] = '';              // MONTO PAGADO (retención no es pago)
                $retRow[21] = $saldoPendiente; // SALDO PENDIENTE
                $out[] = $retRow;
            }
        }

        /* ── FILA TOTALES ─────────────────────────────────── */
        $totRow = array_fill(0, self::COL_COUNT, '');
        $totRow[0]  = '';
        $totRow[4]  = 'TOTALES';              // CLIENTE col (label)
        $totExon = $this->money($totExon);
        $totGrav = $this->money($totGrav);
        $totExen = $this->money($totExen);
        $totSub = $this->money($totSub);
        $totIsv = $this->money($totIsv);
        $totTotal = $this->money($totTotal);
        $totDeb = $this->money($totDeb);
        $totCred = $this->money($totCred);
        $totPagado = $this->money($totPagado);
        $totRow[12] = $totExon   > 0 ? $totExon   : '';  // EXONERADO
        $totRow[13] = $totGrav   > 0 ? $totGrav   : '';  // GRAVADO
        $totRow[14] = $totExen   > 0 ? $totExen   : '';  // EXENTO
        $totRow[15] = $totSub;                            // SUBTOTAL
        $totRow[16] = $totIsv;                            // ISV
        $totRow[17] = $totTotal;                          // TOTAL
        $totRow[18] = $totDeb    > 0 ? -$totDeb   : '';  // DISMINUCION (negativo)
        $totRow[19] = $totCred   > 0 ? $totCred   : '';  // AUMENTO
        $totRow[20] = $totPagado > 0 ? -$totPagado : '';  // MONTO PAGADO (negativo)
        $totRow[21] = $this->money($totTotal - $totDeb + $totCred - $totPagado); // SALDO PENDIENTE (cuadra con fórmula)
        $excelRow = count($out) + 1;
        if (!$this->fastMode && !$this->superFastMode) {
            $this->rowMeta[$excelRow] = ['type' => 'TOTALES'];
        }
        $out[] = $totRow;

        return $out;
    }

    /* ─────────────────────────────────────────────────────────────── */

    public function drawings()
    {
        if ($this->fastMode) {
            return [];
        }

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

        $sheet->mergeCells("A1:{$lc}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(65);

        $sheet->mergeCells("A2:{$lc}2");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('e07000');
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $sheet->mergeCells("A3:{$lc}3");
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(16);

        $sheet->getStyle("A4:{$lc}4")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("A4:{$lc}4")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lc}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('e07000');
        $sheet->getStyle("A4:{$lc}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(30);

        // Anchos fijos siempre — setAutoSize en 30 columnas es el mayor cuello de botella
        $fixedWidths = [
            'A' => 6,  'B' => 10, 'C' => 12, 'D' => 20, 'E' => 34,
            'F' => 18, 'G' => 18, 'H' => 16, 'I' => 28, 'J' => 16,
            'K' => 16, 'L' => 12, 'M' => 12, 'N' => 12, 'O' => 12,
            'P' => 12, 'Q' => 12, 'R' => 12, 'S' => 14, 'T' => 14,
            'U' => 14, 'V' => 14, 'W' => 18, 'X' => 12, 'Y' => 12,
            'Z' => 10, 'AA' => 12, 'AB' => 18, 'AC' => 18, 'AD' => 20,
        ];
        foreach ($fixedWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // TOTAL se mantiene visible en el archivo.
        $sheet->getColumnDimension('R')->setVisible(true);

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

                // Alineacion base
                $sheet->getStyle("A5:{$lc}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Texto alineado a la izquierda
                foreach (['D','E','F','G','H','I','J','K','AB','AC','AD'] as $c) {
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Moneda: M..W (EXONERADO..SALDO PENDIENTE) = cols M..W
                $currency    = '"L" #,##0.00';
                // Mostrar negativos como positivos (solo visual): conserva el valor real para formulas.
                $currencyAbs = '"L" #,##0.00;"L" #,##0.00';
                foreach (['M','N','O','P','Q','R','S','T','U','V'] as $c) {
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    // S = DISMINUCION, U = MONTO PAGADO: mostrar en positivo sin alterar formulas.
                    $fmt = in_array($c, ['S','U']) ? $currencyAbs : $currency;
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getNumberFormat()->setFormatCode($fmt);
                }

                // Dias vencidos (col Z, index 25): formato numerico simple
                $sheet->getStyle("Z5:Z{$lastRow}")
                    ->getNumberFormat()->setFormatCode('0');

                // Fechas como formato fecha (los valores ya vienen en serial Excel desde array()).
                foreach (['C', 'X', 'Y', 'AA'] as $c) {
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                }

                if ($this->superFastMode) {
                    // superFastMode: solo estilos en bloque, cero loops por fila.
                    // Para 27K+ filas el loop individual tarda minutos extra.
                    $sheet->getStyle("A5:{$lc}{$lastRow}")->getFont()->setSize(8);
                    $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E8D5BF');
                    $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getOutline()
                        ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                    $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                        ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('b05000');
                    // Fila de totales
                    $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                    $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->getFont()
                        ->setBold(true)->setSize(9)->getColor()->setRGB('7d3f00');
                    return;
                }

                if ($this->fastMode) {
                    // fastMode: sin recorrido fila-por-fila para maximizar velocidad.
                    // Se aplican solo estilos globales y una marca visual para la fila final.
                    $sheet->getStyle("A5:{$lc}{$lastRow}")->getFont()->setSize(8);
                    $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E8D5BF');
                    $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getOutline()
                        ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                    $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                        ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('b05000');
                    $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                    $sheet->getStyle("A{$lastRow}:{$lc}{$lastRow}")->getFont()
                        ->setBold(true)->setSize(9)->getColor()->setRGB('7d3f00');
                    return;
                }

                // ── Loop por fila ──────────────────────────────
                for ($row = 5; $row <= $lastRow; $row++) {
                    $meta = $this->rowMeta[$row] ?? ['type' => ''];
                    $type = $meta['type'] ?? '';
                    $h    = 13;

                    if ($type === 'TOTALES') {
                        // ── Fila totales ──────────────────────────
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(9)->getColor()->setRGB('7d3f00');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getBorders()->getTop()
                            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                        $h = 16;
                    } elseif ($type === self::T_FACTURA) {                        // ── Fila factura: fondo naranja, negrita ──
                        $estadoF01 = strtoupper(trim((string)($meta['estado_f01'] ?? '')));
                        $esAnu = str_starts_with($estadoF01, 'ANULAD');
                        $bg    = $esAnu ? 'EBEBEB' : 'FFF3E0';

                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(8.5);
                        if ($esAnu) {
                            $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                                ->setStrikethrough(true)->getColor()->setRGB('999999');
                        }

                        // Dias vencidos (col Z, index 25): verde si <= 0, rojo si > 0
                        $dias = (int)($meta['dias_vencidos'] ?? 0);
                        if ($dias > 0) {
                            $sheet->getStyle("Z{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FADBD8');
                            $sheet->getStyle("Z{$row}")->getFont()
                                ->setBold(true)->getColor()->setRGB('922B21');
                        } else {
                            $sheet->getStyle("Z{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D5F5E3');
                            $sheet->getStyle("Z{$row}")->getFont()
                                ->getColor()->setRGB('1E8449');
                        }

                        // Estado cobro (col W, index 22)
                        $ec   = (string)($meta['estado_cobro'] ?? '');
                        $bgEc = match(true) {
                            $ec === 'Anuladas'                                            => 'EBEBEB',
                            $ec === 'Pagada'                                               => 'D5F5E3',
                            $ec === 'Contado'                                              => 'D6EAF8',
                            $ec === 'Parcialmente Pagada'                                  => 'DBEAFE',
                            str_starts_with($ec, 'Vencida') && str_contains($ec, 'tica')  => 'F5B7B1',
                            $ec === 'Vencida'                                              => 'FADBD8',
                            $ec === 'Pendiente'                                            => 'FDEBD0',
                            default                                                        => 'FDFEFE',
                        };
                        $sheet->getStyle("W{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgEc);
                        $h = 16;

                    } else {
                        // ── Sub-fila (movimiento): sin fondo ──────
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setSize(7.5);

                        $dir = $meta['dir'] ?? 'neutral';

                        // Celda DEBITOS (col S, idx 18): rojo
                        if ($dir === 'debito') {
                            $sheet->getStyle("S{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FADBD8');
                            $sheet->getStyle("S{$row}")->getFont()
                                ->setBold(true)->getColor()->setRGB('922B21');
                        }

                        // Celda CREDITOS (col T, idx 19): verde
                        if ($dir === 'credito') {
                            $sheet->getStyle("T{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D5F5E3');
                            $sheet->getStyle("T{$row}")->getFont()
                                ->setBold(true)->getColor()->setRGB('1E8449');
                        }

                        // Celda MONTO PAGADO (col U, idx 20): azul claro para pagos
                        if ($dir === 'pago') {
                            $sheet->getStyle("U{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D6EAF8');
                            $sheet->getStyle("U{$row}")->getFont()
                                ->setBold(true)->getColor()->setRGB('1A5276');
                            // Saldo pendiente (col V) tambien en azul tras cada pago
                            $sheet->getStyle("V{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EBF5FB');
                            $sheet->getStyle("V{$row}")->getFont()->getColor()->setRGB('1A5276');
                        }
                    }

                    $sheet->getRowDimension($row)->setRowHeight($h);
                }

                // Bordes globales
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E8D5BF');
                $sheet->getStyle("A4:{$lc}{$lastRow}")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('e07000');
                $sheet->getStyle("A4:{$lc}4")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('b05000');
            },
        ];
    }
}