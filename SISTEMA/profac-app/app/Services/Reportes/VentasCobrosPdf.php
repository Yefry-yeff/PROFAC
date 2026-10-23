<?php

namespace App\Services\Reportes;

use Dompdf\Canvas;
use Dompdf\Dompdf;

class VentasCobrosPdf
{
    private const MARGIN = 18;
    private const FONT_SIZE = 4.5;
    private const LINE_HEIGHT = 6;
    private const COLUMNS = [
        ['item', '#', 18, 'text'],
        ['asesor_comercial', 'ASESOR COMERCIAL', 40, 'text'],
        ['teleasesor', 'TELEASESOR', 40, 'text'],
        ['cliente', 'CLIENTE', 60, 'text'],
        ['numero_secuencia_cai', 'FACTURA', 27, 'text'],
        ['observacion', 'OBSERVACIÓN', 45, 'text'],
        ['orden_compra', 'ORD. COMPRA', 30, 'text'],
        ['modo_pago', 'MODO PAGO', 25, 'text'],
        ['estado_f01', 'ESTADO F01', 28, 'text'],
        ['exonerado', 'EXONERADO', 30, 'money'],
        ['gravado', 'GRAVADO', 30, 'money'],
        ['exento', 'EXENTO', 30, 'money'],
        ['abonos', 'ABONOS', 30, 'money'],
        ['detalle_abonos', 'DETALLE ABONOS', 65, 'text'],
        ['sub_total', 'SUBTOTAL', 30, 'money'],
        ['isv', 'ISV', 30, 'money'],
        ['total', 'TOTAL', 30, 'money'],
        ['saldo_pendiente', 'SALDO PEND.', 30, 'money'],
        ['fatal_retencion', 'FATAL RET.', 20, 'text'],
        ['monto_pagado', 'MONTO PAG.', 30, 'money'],
        ['monto_retencion', 'MONTO RET.', 30, 'money'],
        ['numero_retencion', 'NRO RET.', 30, 'text'],
        ['fecha_venta', 'F. VENTA', 29, 'date'],
        ['fecha_vencimiento', 'F. VCTO.', 29, 'date'],
        ['dias_vencidos', 'DÍAS VCTOS.', 23, 'text'],
        ['creditos_vencidos', 'ESTADO CRED.', 27, 'text'],
        ['fecha_pago', 'F. PAGO', 29, 'date'],
        ['forma_pago', 'FORMA PAGO', 30, 'text'],
        ['cuenta_banco', 'CUENTA/BANCO', 35, 'text'],
        ['fecha_entrega', 'F. ENTREGA', 29, 'date'],
        ['recibo', 'RECIBO', 25, 'text'],
    ];

    public function generar(iterable $rows): string
    {
        $dompdf = app(Dompdf::class);
        $dompdf->setPaper('legal', 'landscape');
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $bold = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'bold');
        $scale = ($canvas->get_width() - 2 * self::MARGIN) / array_sum(array_column(self::COLUMNS, 2));
        $widths = array_map(fn ($column) => $column[2] * $scale, self::COLUMNS);
        $generated = now()->format('d/m/Y H:i');
        $month = null;
        $item = 0;
        $y = 0;

        // Draw directly on the PDF canvas instead of retaining an HTML DOM for every invoice.
        foreach ($rows as $row) {
            $title = mb_strtoupper($row->mes . ' ' . $row->anio);
            if ($title !== $month) {
                if ($month !== null) {
                    $canvas->new_page();
                }
                $month = $title;
                $y = $this->header($canvas, $font, $bold, $widths, $month, $generated);
            }

            $row->item = ++$item;
            $cells = [];
            foreach (self::COLUMNS as $index => [$field, , , $type]) {
                $cells[] = $this->wrap($canvas, $font, $this->value($row, $field, $type), $widths[$index] - 4);
            }

            $offset = 0;
            $lines = max(array_map('count', $cells));
            $height = $lines * self::LINE_HEIGHT + 4;
            $bottom = $canvas->get_height() - self::MARGIN - 12;
            if ($y + $height > $bottom && $height <= $bottom - 102) {
                $canvas->new_page();
                $y = $this->header($canvas, $font, $bold, $widths, $month, $generated);
            }
            while ($offset < $lines) {
                $available = (int) floor(($canvas->get_height() - self::MARGIN - 12 - $y - 4) / self::LINE_HEIGHT);
                if ($available < 1) {
                    $canvas->new_page();
                    $y = $this->header($canvas, $font, $bold, $widths, $month, $generated);
                    continue;
                }
                $count = min($available, $lines - $offset);
                $height = $count * self::LINE_HEIGHT + 4;
                $x = self::MARGIN;
                foreach ($cells as $index => $cell) {
                    if ($item % 2 === 0) {
                        $canvas->filled_rectangle($x, $y, $widths[$index], $height, [0.96, 0.99, 0.98]);
                    }
                    $canvas->rectangle($x, $y, $widths[$index], $height, [0.8, 0.8, 0.8], 0.3);
                    $visible = $index === 0 && $offset > 0
                        ? [(string) $item]
                        : array_slice($cell, $offset, $count);
                    foreach ($visible as $line => $text) {
                        $textX = $x + 2;
                        if (self::COLUMNS[$index][3] === 'money') {
                            $textX = $x + $widths[$index] - 2 - $canvas->get_text_width($text, $font, self::FONT_SIZE);
                        }
                        $canvas->text($textX, $y + 2 + $line * self::LINE_HEIGHT, $text, $font, self::FONT_SIZE);
                    }
                    $x += $widths[$index];
                }
                $y += $height;
                $offset += $count;
            }
        }

        if ($item === 0) {
            $y = $this->header($canvas, $font, $bold, $widths, 'SIN RESULTADOS', $generated);
            $canvas->text(self::MARGIN, $y + 8, 'No hay facturas para los filtros seleccionados.', $font, 8);
        }
        $canvas->page_text(self::MARGIN, $canvas->get_height() - self::MARGIN, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 7);

        return $dompdf->output();
    }

    private function header(Canvas $canvas, string $font, string $bold, array $widths, string $month, string $generated): float
    {
        $logo = public_path('img' . DIRECTORY_SEPARATOR . 'membrete' . DIRECTORY_SEPARATOR . 'Logo3.png');
        $dimensions = getimagesize($logo);
        if ($dimensions === false) {
            throw new \RuntimeException('No se pudieron leer las dimensiones del logo del reporte.');
        }
        $logoHeight = 35;
        $logoWidth = $logoHeight * $dimensions[0] / $dimensions[1];
        $textX = self::MARGIN + $logoWidth + 18;
        $canvas->image($logo, self::MARGIN, self::MARGIN, $logoWidth, $logoHeight);
        $canvas->text($textX, 18, 'DISTRIBUCIONES VALENCIA - RTN: 08011986138652', $bold, 10, [0.1, 0.23, 0.36]);
        $canvas->text($textX, 33, 'REPORTE DE VENTAS Y COBROS', $bold, 9);
        $canvas->text($textX, 46, 'Generado: ' . $generated, $font, 7);
        $canvas->filled_rectangle(self::MARGIN, 63, array_sum($widths), 15, [0.1, 0.23, 0.36]);
        $canvas->text(self::MARGIN + 4, 65, $month, $bold, 8, [1, 1, 1]);
        $x = self::MARGIN;
        foreach (self::COLUMNS as $index => [, $label]) {
            $canvas->filled_rectangle($x, 78, $widths[$index], 24, [0.1, 0.7, 0.58]);
            foreach ($this->wrap($canvas, $bold, $label, $widths[$index] - 4) as $line => $text) {
                $canvas->text($x + 2, 80 + $line * self::LINE_HEIGHT, $text, $bold, self::FONT_SIZE, [1, 1, 1]);
            }
            $x += $widths[$index];
        }

        return 102;
    }

    private function wrap(Canvas $canvas, string $font, string $text, float $width): array
    {
        $remaining = trim(preg_replace('/\s+/u', ' ', $text));
        $lines = [];
        while ($remaining !== '') {
            $length = mb_strlen($remaining);
            $low = 1;
            $high = $length;
            while ($low < $high) {
                $mid = (int) ceil(($low + $high) / 2);
                if ($canvas->get_text_width(mb_substr($remaining, 0, $mid), $font, self::FONT_SIZE) <= $width) {
                    $low = $mid;
                } else {
                    $high = $mid - 1;
                }
            }
            $cut = $low;
            if ($cut < $length) {
                $space = mb_strrpos(mb_substr($remaining, 0, $cut + 1), ' ');
                if ($space !== false && $space > 0) {
                    $cut = $space;
                }
            }
            $lines[] = mb_substr($remaining, 0, $cut);
            $remaining = ltrim(mb_substr($remaining, $cut));
        }

        return $lines ?: [''];
    }

    private function value(object $row, string $field, string $type): string
    {
        if ($field === 'fatal_retencion') {
            $balance = round((float) $row->saldo_pendiente, 2);
            $tax = round((float) $row->isv, 2);
            return $balance > 0 && $tax > 0 && abs($balance - $tax) <= 0.10 ? 'X' : '';
        }
        $value = $row->{$field} ?? '';
        if ($type === 'money') {
            return (float) $value != 0 ? 'L ' . number_format((float) $value, 2) : '-';
        }
        if ($type === 'date') {
            return $value ? date('d/m/Y', strtotime($value)) : '-';
        }

        return (string) $value;
    }
}
