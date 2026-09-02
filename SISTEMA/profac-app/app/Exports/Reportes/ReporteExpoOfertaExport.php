<?php

namespace App\Exports\Reportes;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteExpoOfertaExport implements FromArray, ShouldAutoSize, WithStyles
{
    private array $sectionRows = [];

    public function __construct(private array $detalle)
    {
    }

    public function array(): array
    {
        $oferta = $this->detalle['oferta'];
        $resumen = $this->detalle['resumen'];
        $rows = [
            ['REPORTE BI DE EXPO - OFERTA #' . $oferta['id']],
            ['Oferta', $oferta['id'], 'Flujo', $oferta['flujo_id'], 'Expo', $oferta['expo']],
            ['Cliente', $oferta['cliente'], 'RTN', $oferta['rtn']],
            ['Asesor', $oferta['asesor'], 'Teleasesor', $oferta['teleasesor']],
            ['Fecha', $oferta['fecha'], 'Hora', $oferta['hora'], 'Estado', $oferta['estado']],
            ['Tipo de venta', $oferta['tipo_venta'], 'Condicion de pago', $oferta['condicion_pago']],
            [],
            ['PRODUCTOS'],
            ['Codigo', 'Producto', 'Marca', 'Categoria', 'Cantidad', 'Escala', 'Precio base',
                'Precio antes descuento', 'Descuento', 'Descuento %', 'Precio final',
                'Subtotal final', 'ISV', 'Total', 'Costo', 'Utilidad', 'Margen %'],
        ];

        foreach ($this->detalle['productos'] as $producto) {
            $rows[] = [
                $producto['codigo'], $producto['producto'], $producto['marca'], $producto['categoria'],
                $producto['cantidad'], $producto['escala'], $producto['precio_base'],
                $producto['precio_antes_descuento'], $producto['descuento'], $producto['descuento_pct'],
                $producto['precio_final'], $producto['subtotal_final'], $producto['isv'],
                $producto['total'], $producto['costo_total'], $producto['utilidad'], $producto['margen_pct'],
            ];
        }

        $rows[] = [];
        $rows[] = ['RESUMEN'];
        $rows[] = ['Subtotal original', $resumen['subtotal_original']];
        $rows[] = ['Descuento', $resumen['descuento']];
        $rows[] = ['Subtotal final', $resumen['subtotal_final']];
        $rows[] = ['ISV', $resumen['isv']];
        $rows[] = ['Total', $resumen['total']];
        $rows[] = ['Costo', $resumen['costo']];
        $rows[] = ['Utilidad', $resumen['utilidad']];
        $rows[] = ['Margen %', $resumen['margen_pct']];

        $this->sectionRows = [1, 8, 9, count($this->detalle['productos']) + 11];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $this->array();
        $cantidadProductos = count($this->detalle['productos']);
        $ultimaFilaProducto = 9 + $cantidadProductos;
        $filaResumen = $cantidadProductos + 11;
        foreach ($this->sectionRows as $row) {
            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1A2035']],
            ]);
        }
        if ($cantidadProductos > 0) {
            $sheet->getStyle("E10:E{$ultimaFilaProducto}")->getNumberFormat()->setFormatCode('#,##0.00');
            foreach (['G', 'H', 'I', 'K', 'L', 'M', 'N', 'O', 'P'] as $columna) {
                $sheet->getStyle("{$columna}10:{$columna}{$ultimaFilaProducto}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
            foreach (['J', 'Q'] as $columna) {
                $sheet->getStyle("{$columna}10:{$columna}{$ultimaFilaProducto}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }
        $sheet->getStyle('B2')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D2')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . ($filaResumen + 1) . ':B' . ($filaResumen + 8))
            ->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->freezePane('A10');

        return [];
    }
}