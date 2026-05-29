<?php

namespace App\Exports;

use App\Models\AlertaRotacionConfig;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AlertasRotacionExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly AlertaRotacionConfig $regla
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        $tipo    = $this->regla->tipo;
        $rows    = $this->regla->getProductosAfectados()->map(fn ($p) => (array) $p);
        $fecha   = now()->format('d/m/Y H:i');

        return $rows->map(function (array $p) use ($tipo, $fecha) {
            $base = [
                'Código'       => $p['codigo_barra']      ?? '',
                'Producto'     => $p['producto_nombre']   ?? '',
                'Subcategoría' => $p['sub_categoria']     ?? '',
                'Stock actual' => $p['stock_actual']      ?? 0,
                'Precio base'  => number_format((float)($p['precio_base'] ?? 0), 2),
                'Último costo' => number_format((float)($p['ultimo_costo_compra'] ?? 0), 2),
                'Costo prom.'  => number_format((float)($p['costo_promedio'] ?? 0), 2),
            ];

            $extra = match ($tipo) {
                'recuperacion_proxima' => [
                    'Última compra'       => $p['ultima_compra'] ?? '',
                    'Fecha límite'        => $p['fecha_limite']  ?? '',
                    'T. recuper. (meses)' => $p['tiempo_recuperacion_meses'] ?? '',
                ],
                'recuperacion_vencida' => [
                    'Última compra'  => $p['ultima_compra'] ?? '',
                    'Fecha límite'   => $p['fecha_limite']  ?? '',
                    'Días vencido'   => $p['dias_vencido']  ?? 0,
                ],
                'sin_ventas' => [
                    'Última venta' => $p['ultima_venta'] ?? 'Sin ventas',
                ],
                'baja_rotacion' => [
                    'Ventas 60 días'  => $p['ventas_60d']           ?? 0,
                    'Umbral mínimo'   => $this->regla->parametro_umbral ?? '',
                ],
                'sobreinventario' => [
                    'Cobertura (meses)' => isset($p['cobertura_meses']) && $p['cobertura_meses'] >= 9000
                                            ? 'Sin salida' : number_format((float)($p['cobertura_meses'] ?? 0), 1),
                    'Prom. mensual'     => number_format((float)($p['prom_mensual'] ?? 0), 0),
                    'Límite config.'    => $this->regla->parametro_umbral ?? '',
                ],
                'incremento_demanda' => [
                    'Ventas 30d'       => $p['ventas_30d']      ?? 0,
                    'Ventas período ant.' => $p['ventas_30d_ant']  ?? 0,
                    'Crecimiento %'    => number_format((float)($p['pct_crecimiento'] ?? 0), 1) . '%',
                ],
                default => [],
            };

            return array_merge($base, $extra, ['Generado' => $fecha]);
        });
    }

    public function headings(): array
    {
        // Headings are the keys of the first row
        $first = $this->collection()->first();
        return $first ? array_keys($first) : [];
    }

    public function title(): string
    {
        return mb_substr($this->regla->nombre, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = $sheet->getHighestColumn();
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF065F46']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A1:{$lastCol}1" => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF065F46']],
            ],
        ];
    }
}
