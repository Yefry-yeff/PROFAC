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

/**
 * Hoja del reporte Ventas & Cobros (v6).
 *
 * 31 columnas (A..AE):
 *  A(0)   #               B(1)   MES             C(2)   FECHA
 *  D(3)   USUARIO         E(4)   CLIENTE         F(5)   DOCUMENTO
 *  G(6)   TIPO DOCUMENTO  H(7)   NRO DOCUMENTO   I(8)   OBSERVACION
 *  J(9)   ORDEN COMPRA    K(10)  MODO PAGO       L(11)  ESTADO F01
 *  M(12)  EXONERADO       N(13)  GRAVADO         O(14)  EXENTO
 *  P(15)  SUBTOTAL        Q(16)  ISV             R(17)  TOTAL
 *  S(18)  SALDO PENDIENTE T(19)  DEBITOS         U(20)  CREDITOS
 *  V(21)  MONTO PAGADO    W(22)  FECHA VENTA     X(23)  FECHA VCTO.
 *  Y(24)  DIAS VCTOS.     Z(25)  ESTADO COBRO    AA(26) FECHA PAGO
 *  AB(27) FORMA DE PAGO   AC(28) CUENTA/BANCO    AD(29) FECHA ENTREGA
 *  AE(30) RECIBO
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
    protected $rowMeta = [];

    const LAST_COL  = 'AF';
    const COL_COUNT = 32;

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

    public function __construct($rows, $usuario = 'Sistema', $movimientos = [])
    {
        $this->rows        = $rows;
        $this->usuario     = $usuario;
        $this->movimientos = $movimientos;
    }

    public function title(): string { return 'Ventas y Cobros'; }

    private function fmt(?string $d): string
    {
        if (!$d) return '';
        $ts = strtotime($d);
        return $ts ? date('d/m/Y', $ts) : '';
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

    /* ─────────────────────────────────────────────────────────────── */

    public function array(): array
    {
        $this->rowMeta = [];
        $out  = [];
        $item = 0;

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
            'MODO PAGO','ESTADO F01','EXONERADO','GRAVADO','EXENTO',
            'SUBTOTAL','ISV','TOTAL','DEBITOS','CREDITOS',
            'SALDO DE FACTURA','MONTO PAGADO','SALDO PENDIENTE','ESTADO COBRO','FECHA VENTA',
            'FECHA VCTO.','DIAS VCTOS.','FECHA PAGO','FORMA DE PAGO','CUENTA/BANCO',
            'FECHA ENTREGA','RECIBO',
        ];

        foreach ($this->rows as $r) {
            $factId      = (int)($r->factura_id ?? 0);
            $facturaNum  = $r->numero_secuencia_cai ?? '';
            $movs         = $this->movimientos[$factId] ?? [];
            $saldoFactura = (float)($r->total ?? 0); // progresivo: cambia con DEBITO/CREDITO
            $pagosAcum    = 0.0;                     // acumula PAGO/ABONO
            $dias         = (int)($r->dias_vencidos ?? 0);
            $estadoCobro = $r->estado_cobro_v2 ?? ($r->creditos_vencidos ?? '');

            /* ── PRE-CALCULAR TOTALES DEBITOS / CREDITOS ───── */
            $totalDebitos  = 0.0;
            $totalCreditos = 0.0;
            $totalPagos    = 0.0;
            $_sfCalc       = (float)($r->total ?? 0);
            foreach ($movs as $_mov) {
                if ($_mov->tipo === 'VENTA') continue;
                $_monto = (float)($_mov->monto ?? 0);
                if (in_array($_mov->tipo, self::$TIPOS_DEBITO)) {
                    $totalDebitos += $_monto;
                    $_sfCalc       = max($_sfCalc - $_monto, 0); // saldo factura baja
                }
                if (in_array($_mov->tipo, self::$TIPOS_CREDITO)) {
                    $totalCreditos += $_monto;
                    $_sfCalc       += $_monto;                   // saldo factura sube
                }
                if (in_array($_mov->tipo, self::$TIPOS_PAGO)) {
                    $totalPagos += $_monto;  // pagos NO afectan saldo de factura
                }
            }
            // Sumar retencion ISV al total de debitos
            $_montoRet = (float)($r->monto_retencion ?? 0);
            if ($_montoRet > 0) { $totalDebitos += $_montoRet; $_sfCalc = max($_sfCalc - $_montoRet, 0); }
            $finalSaldoFactura   = $_sfCalc;
            $finalSaldoPendiente = max($finalSaldoFactura - $totalPagos, 0);

            /* ── FILA FACTURA ──────────────────────────────── */
            $item++;
            $excelRow = count($out) + 1;
            $this->rowMeta[$excelRow] = [
                'type'          => self::T_FACTURA,
                'estado_cobro'  => $estadoCobro,
                'estado_f01'    => $r->estado_f01 ?? '',
                'dias_vencidos' => $dias,
            ];

            $row = array_fill(0, self::COL_COUNT, '');
            $row[0]  = $item;
            $row[1]  = strtoupper($r->mes ?? '');
            $row[2]  = $this->fmt($r->fecha_venta);
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
            $row[12] = (float)($r->exonerado ?? 0) > 0 ? (float)$r->exonerado : '';
            $row[13] = (float)($r->gravado   ?? 0) > 0 ? (float)$r->gravado   : '';
            $row[14] = (float)($r->exento    ?? 0) > 0 ? (float)$r->exento    : '';
            $row[15] = (float)($r->sub_total ?? 0);
            $row[16] = (float)($r->isv       ?? 0);
            $row[17] = (float)($r->total     ?? 0);
            $row[18] = $totalDebitos  > 0 ? $totalDebitos  : '';  // DEBITOS: Nota Credito + Vale + Retencion
            $row[19] = $totalCreditos > 0 ? $totalCreditos : '';  // CREDITOS: Nota Debito
            $row[20] = $finalSaldoFactura;   // SALDO DE FACTURA = (total + creditos) - debitos
            // MONTO PAGADO: suma de Abono + Pago (calculada del detalle; fallback a campo abonos)
            $row[21] = $totalPagos > 0 ? $totalPagos : (
                       (float)($r->abonos ?? 0) > 0 ? (float)$r->abonos : (
                       (float)($r->monto_pagado ?? 0) > 0 ? (float)$r->monto_pagado : ''
                       ));
            $row[22] = $finalSaldoPendiente; // SALDO PENDIENTE = Saldo de Factura - Monto Pagado
            $row[23] = $estadoCobro;         // ESTADO COBRO
            $row[24] = $this->fmt($r->fecha_venta);
            $row[25] = $this->fmt($r->fecha_vencimiento);
            $row[26] = $dias;                // DIAS VCTOS. - numerico para colorear celda
            $row[27] = '';
            $row[28] = '';
            $row[29] = '';
            $row[30] = $this->fmt($r->fecha_entrega);
            $row[31] = '';
            $out[] = $row;

            /* ── MOVIMIENTOS ───────────────────────────────── */
            foreach ($movs as $mov) {
                if ($mov->tipo === 'VENTA') continue;

                $tipo  = $mov->tipo;
                $monto = (float)($mov->monto ?? 0);

                $esDebito  = in_array($tipo, self::$TIPOS_DEBITO);
                $esCredito = in_array($tipo, self::$TIPOS_CREDITO);
                $esPago    = in_array($tipo, self::$TIPOS_PAGO);

                // Actualizar saldo progresivo
                if ($esDebito)       $saldoFactura  = max($saldoFactura - $monto, 0);
                elseif ($esCredito)  $saldoFactura += $monto;
                elseif ($esPago)     $pagosAcum    += $monto;
                // ENTREGA no cambia saldo
                $saldoPendiente = max($saldoFactura - $pagosAcum, 0);

                $item++;
                $excelRow = count($out) + 1;
                $this->rowMeta[$excelRow] = [
                    'type'      => $tipo,
                    'dir'       => $esDebito ? 'debito' : ($esCredito ? 'credito' : ($esPago ? 'pago' : 'neutral')),
                    'saldo_dir' => $esDebito ? 'down' : ($esCredito ? 'up' : 'neutral'),
                    'has_monto' => ($tipo !== 'ENTREGA' && $monto > 0),
                ];

                $banco = '';
                if (!empty($mov->banco_nombre)) {
                    $banco = $mov->banco_nombre;
                    if (!empty($mov->banco_cuenta)) $banco .= ' - ' . $mov->banco_cuenta;
                }

                $movRow = array_fill(0, self::COL_COUNT, '');
                $movRow[0]  = $item;
                $movRow[1]  = $this->mesNombre($mov->fecha);
                $movRow[2]  = $this->fmt($mov->fecha);
                $movRow[3]  = $mov->responsable ?? '';
                $movRow[4]  = $r->cliente ?? '';
                $movRow[5]  = $facturaNum;
                $movRow[6]  = $this->tipoLabel($tipo);
                $movRow[7]  = trim((string)($mov->documento ?? ''));
                $movRow[8]  = $mov->descripcion ?? '';
                // cols 9-17: datos de factura en blanco (TOTAL solo en factura, no en sub-filas)
                $movRow[18] = $esDebito  ? $monto : ''; // DEBITOS
                $movRow[19] = $esCredito ? $monto : ''; // CREDITOS
                $movRow[20] = ($esDebito || $esCredito) ? $saldoFactura : ''; // SALDO DE FACTURA (solo cuando cambia)
                $movRow[21] = $esPago    ? $monto : ''; // MONTO PAGADO
                $movRow[22] = $saldoPendiente; // SALDO PENDIENTE siempre (0.00 cuando saldado)
                $movRow[23] = '';                       // ESTADO COBRO: en blanco en sub-filas
                // cols 24-26: fechas venta/vcto/dias en blanco
                $movRow[27] = ($tipo !== 'ENTREGA') ? $this->fmt($mov->fecha) : ''; // FECHA PAGO
                $movRow[28] = $mov->forma_pago ?? '';
                $movRow[29] = $banco;
                $movRow[30] = ($tipo === 'ENTREGA') ? $this->fmt($mov->fecha) : ''; // FECHA ENTREGA
                $movRow[31] = $mov->recibo ?? '';
                $out[] = $movRow;
            }

            /* ── RETENCIÓN ISV ─────────────────────────────── */
            $montoRet = (float)($r->monto_retencion ?? 0);
            if ($montoRet > 0) {
                $saldoFactura   = max($saldoFactura - $montoRet, 0);
                $saldoPendiente = max($saldoFactura - $pagosAcum, 0);
                $item++;
                $excelRow = count($out) + 1;
                $this->rowMeta[$excelRow] = [
                    'type'      => self::T_RETENCION,
                    'dir'       => 'debito',
                    'saldo_dir' => 'down',
                    'has_monto' => true,
                ];

                $retRow = array_fill(0, self::COL_COUNT, '');
                $retRow[0]  = $item;
                $retRow[4]  = $r->cliente ?? '';
                $retRow[5]  = $facturaNum;
                $retRow[6]  = 'Retencion ISV';
                $retRow[7]  = trim((string)($r->numero_retencion ?? ''));
                $retRow[8]  = 'Retencion ISV aplicada';
                $retRow[18] = $montoRet;       // DEBITOS
                $retRow[19] = '';              // CREDITOS
                $retRow[20] = $saldoFactura;   // SALDO DE FACTURA
                $retRow[22] = $saldoPendiente; // SALDO PENDIENTE
                $out[] = $retRow;
            }
        }

        return $out;
    }

    /* ─────────────────────────────────────────────────────────────── */

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

        foreach (range('A', 'Z') as $c) { $sheet->getColumnDimension($c)->setAutoSize(true); }
        foreach (['AA','AB','AC','AD','AE','AF'] as $c) { $sheet->getColumnDimension($c)->setAutoSize(true); }

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
                foreach (['D','E','F','G','H','I','J','K','AC','AD','AF'] as $c) {
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Moneda: M..W (EXONERADO..SALDO PENDIENTE) = cols M..W
                $currency = '"L" #,##0.00';
                foreach (['M','N','O','P','Q','R','S','T','U','V','W'] as $c) {
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("{$c}5:{$c}{$lastRow}")
                        ->getNumberFormat()->setFormatCode($currency);
                }

                // Dias vencidos (col AA, index 26): formato numerico simple
                $sheet->getStyle("AA5:AA{$lastRow}")
                    ->getNumberFormat()->setFormatCode('0');

                // ── Loop por fila ──────────────────────────────
                for ($row = 5; $row <= $lastRow; $row++) {
                    $meta = $this->rowMeta[$row] ?? ['type' => ''];
                    $type = $meta['type'] ?? '';
                    $h    = 13;

                    if ($type === self::T_FACTURA) {
                        // ── Fila factura: fondo naranja, negrita ──
                        $esAnu = strtoupper((string)($meta['estado_f01'] ?? '')) === 'ANULADO';
                        $bg    = $esAnu ? 'EBEBEB' : 'FFF3E0';

                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                        $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                            ->setBold(true)->setSize(8.5);
                        if ($esAnu) {
                            $sheet->getStyle("A{$row}:{$lc}{$row}")->getFont()
                                ->setStrikethrough(true)->getColor()->setRGB('999999');
                        }

                        // Dias vencidos (col AA, index 26): verde si <= 0, rojo si > 0
                        $dias = (int)($meta['dias_vencidos'] ?? 0);
                        if ($dias > 0) {
                            $sheet->getStyle("AA{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FADBD8');
                            $sheet->getStyle("AA{$row}")->getFont()
                                ->setBold(true)->getColor()->setRGB('922B21');
                        } else {
                            $sheet->getStyle("AA{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D5F5E3');
                            $sheet->getStyle("AA{$row}")->getFont()
                                ->getColor()->setRGB('1E8449');
                        }

                        // Estado cobro (col X, index 23)
                        $ec   = (string)($meta['estado_cobro'] ?? '');
                        $bgEc = match(true) {
                            $ec === 'Pagada'                                               => 'D5F5E3',
                            $ec === 'Contado'                                              => 'D6EAF8',
                            $ec === 'Parcialmente Pagada'                                  => 'DBEAFE',
                            str_starts_with($ec, 'Vencida') && str_contains($ec, 'tica')  => 'F5B7B1',
                            $ec === 'Vencida'                                              => 'FADBD8',
                            $ec === 'Pendiente'                                            => 'FDEBD0',
                            default                                                        => 'FDFEFE',
                        };
                        $sheet->getStyle("X{$row}")->getFill()
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

                        // Celda SALDO DE FACTURA (col U, idx 20): rojo si baja, verde si sube
                        $saldoDir = $meta['saldo_dir'] ?? 'neutral';
                        if ($saldoDir === 'down') {
                            $sheet->getStyle("U{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FADBD8');
                            $sheet->getStyle("U{$row}")->getFont()->getColor()->setRGB('922B21');
                        } elseif ($saldoDir === 'up') {
                            $sheet->getStyle("U{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D5F5E3');
                            $sheet->getStyle("U{$row}")->getFont()->getColor()->setRGB('1E8449');
                        }

                        // Celda MONTO PAGADO (col V, idx 21): azul claro para pagos
                        if ($dir === 'pago') {
                            $sheet->getStyle("V{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D6EAF8');
                            $sheet->getStyle("V{$row}")->getFont()
                                ->setBold(true)->getColor()->setRGB('1A5276');
                            // Saldo pendiente (col W) tambien en azul tras cada pago
                            $sheet->getStyle("W{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EBF5FB');
                            $sheet->getStyle("W{$row}")->getFont()->getColor()->setRGB('1A5276');
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