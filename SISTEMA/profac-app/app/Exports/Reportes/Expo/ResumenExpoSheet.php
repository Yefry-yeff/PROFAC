<?php

namespace App\Exports\Reportes\Expo;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Hoja "Resumen": KPIs de ganadoras, no ganadoras y conjunto (los mismos de
 * las tarjetas del reporte) y la distribución de ofertas por tipo.
 */
class ResumenExpoSheet implements FromArray, WithTitle, WithEvents
{
    use EstiloExpo;

    private const SEGMENTOS = [
        'ganadoras' => ['Ganadoras', '15803D'],
        'no_ganadoras' => ['No ganadoras', '64748B'],
        'conjunto' => ['Conjunto', '1D4ED8'],
    ];

    private const INDICADORES = [
        ['Ofertas', 'num_ofertas', 'entero'],
        ['Secciones', 'num_secciones', 'entero'],
        ['Facturas', 'num_facturas', 'entero'],
        ['Total ofertado', 'total_ofertado', 'moneda'],
        ['Total facturado', 'total_facturado', 'moneda'],
        ['% Avance de facturación', 'avance_pct', 'pct'],
        ['Utilidad a ganar', 'utilidad_a_ganar', 'moneda'],
        ['Utilidad facturada', 'utilidad_facturada', 'moneda'],
        ['Pagado', 'total_pagado', 'moneda'],
        ['% Pagado de lo facturado', 'pagado_pct', 'pct'],
        ['Utilidad ganada de pagos', 'utilidad_pagada', 'moneda'],
        ['% Utilidad ganada', 'utilidad_ganada_pct', 'pct'],
        ['Facturas distribuidas', 'num_entregadas', 'entero'],
        ['Facturas sin distribuir', 'num_sin_entregar', 'entero'],
        ['Facturas parcialmente distribuidas', 'num_parciales', 'entero'],
        ['% Entrega (facturas)', 'entrega_pct', 'pct'],
    ];

    public function __construct(private array $facturabilidad, private array $info, private array $notas = [])
    {
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s = $event->sheet->getDelegate();
                $f = $this->facturabilidad;
                $fila = $this->escribirTitulo($s, 'Reporte Expo · Resumen por tipo de oferta', $this->info, 7);

                $this->escribirBarra($s, $fila, 'INDICADORES POR SEGMENTO (montos sin ISV)', 4);
                $fila++;
                $encabezado = $fila;
                $s->setCellValue("A{$fila}", 'Indicador');
                $col = 2;
                foreach (self::SEGMENTOS as [$titulo, $color]) {
                    $letra = chr(ord('A') + $col - 1);
                    $s->setCellValue("{$letra}{$fila}", $titulo);
                    $s->getStyle("{$letra}{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
                    $col++;
                }
                $s->getStyle("A{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->colorMarca);
                $s->getStyle("A{$fila}:D{$fila}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $s->getRowDimension($fila)->setRowHeight(24);

                foreach (self::INDICADORES as $i => [$titulo, $campo, $formato]) {
                    $fila++;
                    $s->setCellValue("A{$fila}", $titulo);
                    $s->getStyle("A{$fila}")->getFont()->setBold(true);
                    $col = 2;
                    foreach (array_keys(self::SEGMENTOS) as $segmento) {
                        $this->escribirValor($s, $col++, $fila, $f[$segmento][$campo] ?? 0, $formato);
                    }
                    $s->getStyle("B{$fila}:D{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    if ($i % 2 === 1) {
                        $s->getStyle("A{$fila}:D{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                    }
                }
                $this->bordes($s, "A{$encabezado}:D{$fila}");
                $s->getColumnDimension('A')->setWidth(34);

                $fila += 2;
                $this->escribirBarra($s, $fila, 'DISTRIBUCIÓN DE OFERTAS', 7);
                $fila++;
                $totalOfertas = max((int) ($f['conjunto']['num_ofertas'] ?? 0), 0);
                $totalOfertado = (float) ($f['conjunto']['total_ofertado'] ?? 0);
                $totalFacturado = (float) ($f['conjunto']['total_facturado'] ?? 0);
                $pct = fn ($valor, $total) => $total > 0 ? round($valor * 100 / $total, 4) : 0;
                $filas = [];
                foreach (['ganadoras' => 'Ofertas ganadoras', 'no_ganadoras' => 'Ofertas no ganadoras'] as $clave => $titulo) {
                    $g = $f[$clave] ?? [];
                    $filas[] = [
                        $titulo,
                        $g['num_ofertas'] ?? 0, $pct($g['num_ofertas'] ?? 0, $totalOfertas),
                        $g['total_ofertado'] ?? 0, $pct($g['total_ofertado'] ?? 0, $totalOfertado),
                        $g['total_facturado'] ?? 0, $pct($g['total_facturado'] ?? 0, $totalFacturado),
                    ];
                }
                $this->escribirTabla($s, $fila, [
                    ['titulo' => 'Tipo', 'ancho' => 30],
                    ['titulo' => 'Ofertas', 'formato' => 'entero', 'total' => 'suma', 'ancho' => 18],
                    ['titulo' => '% de ofertas', 'formato' => 'pct', 'total' => 'suma', 'ancho' => 18],
                    ['titulo' => 'Ofertado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 18],
                    ['titulo' => '% del ofertado', 'formato' => 'pct', 'total' => 'suma'],
                    ['titulo' => 'Facturado', 'formato' => 'moneda', 'total' => 'suma', 'ancho' => 18],
                    ['titulo' => '% del facturado', 'formato' => 'pct', 'total' => 'suma'],
                ], $filas);
                $fila += count($filas) + 3;

                foreach ($this->notas as $nota) {
                    $this->escribirNota($s, $fila++, $nota, 7);
                }

                $s->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
                $s->setSelectedCell('A1');
            },
        ];
    }
}
