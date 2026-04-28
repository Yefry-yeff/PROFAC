<?php

namespace App\Exports\Escalas;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;

/**
 * Exporta todos los productos con sus precios activos para una
 * categoría de cliente determinada, agrupados por categoría de precio.
 */
class ProductosPreciosPorClienteExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;

    protected int $clienteCatId;
    protected ?int $categoriaPrecioId;

    public function __construct(int $clienteCatId, ?int $categoriaPrecioId = null)
    {
        $this->clienteCatId      = $clienteCatId;
        $this->categoriaPrecioId = $categoriaPrecioId;
    }

    public function query()
    {
        return DB::table('precios_producto_carga as PPC')
            ->join('categoria_precios as CP', 'CP.id', '=', 'PPC.categoria_precios_id')
            ->join('cliente_categoria_escala as CCE', 'CCE.id', '=', 'CP.cliente_categoria_escala_id')
            ->join('producto as P', 'P.id', '=', 'PPC.producto_id')
            ->join('marca as M', 'M.id', '=', 'P.marca_id')
            ->join('sub_categoria as SC', 'SC.id', '=', 'P.sub_categoria_id')
            ->join('categoria_producto as CAT', 'CAT.id', '=', 'SC.categoria_producto_id')
            ->leftJoin('unidad_medida as UM', 'UM.id', '=', 'P.unidad_medida_compra_id')
            ->where('CP.cliente_categoria_escala_id', $this->clienteCatId)
            ->where('PPC.estado_id', 1)
            ->where('CP.estado_id', 1)
            ->where('P.estado_producto_id', 1)
            ->when($this->categoriaPrecioId, fn($q) => $q->where('PPC.categoria_precios_id', $this->categoriaPrecioId))
            ->selectRaw("
                CCE.nombre_categoria    AS cat_cliente,
                CP.nombre              AS categoria_precio,
                P.id                   AS producto_id,
                P.nombre               AS producto,
                P.descripcion          AS descripcion,
                M.nombre               AS marca,
                CAT.descripcion        AS categoria,
                SC.descripcion         AS subcategoria,
                COALESCE(UM.nombre,'') AS unidad_medida,
                IF(P.isv > 0,'SI','NO') AS isv,
                PPC.precio_base_venta,
                PPC.precio_a,
                PPC.precio_b,
                PPC.precio_c,
                PPC.precio_d,
                PPC.fecha_ultima_actualizacion
            ")
            ->orderBy('CP.nombre')
            ->orderBy('P.nombre');
    }

    public function headings(): array
    {
        return [
            'Categoría Cliente',
            'Categoría Precio',
            'Cód. Producto',
            'Nombre',
            'Descripción',
            'Marca',
            'Categoría',
            'Subcategoría',
            'Unidad Medida',
            'ISV',
            'Precio Base',
            'Precio A',
            'Precio B',
            'Precio C',
            'Precio D',
            'Últ. Actualización',
        ];
    }

    public function map($row): array
    {
        return [
            $row->cat_cliente,
            $row->categoria_precio,
            $row->producto_id,
            $row->producto,
            $row->descripcion,
            $row->marca,
            $row->categoria,
            $row->subcategoria,
            $row->unidad_medida,
            $row->isv,
            $row->precio_base_venta,
            $row->precio_a,
            $row->precio_b,
            $row->precio_c,
            $row->precio_d,
            $row->fecha_ultima_actualizacion,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28, // Categoría Cliente
            'B' => 24, // Categoría Precio
            'C' => 14, // Cód. Producto
            'D' => 32, // Nombre
            'E' => 32, // Descripción
            'F' => 20, // Marca
            'G' => 22, // Categoría
            'H' => 22, // Subcategoría
            'I' => 18, // Unidad Medida
            'J' => 6,  // ISV
            'K' => 14, // Precio Base
            'L' => 14, // Precio A
            'M' => 14, // Precio B
            'N' => 14, // Precio C
            'O' => 14, // Precio D
            'P' => 22, // Fecha
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Fila de encabezados: fondo naranja, texto blanco, negrita, centrado
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 10,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E67E22'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $lastRow    = $sheet->getHighestRow();
                $lastCol    = 'P';
                $dataRange  = "A2:{$lastCol}{$lastRow}";

                // Bordes para toda la tabla
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'D5C5B5'],
                        ],
                    ],
                ]);

                // Alineación centrada para columnas numéricas y de fecha
                $sheet->getStyle("C2:C{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J2:O{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("P2:P{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Filas de datos: fondo alternado para legibilidad
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FDF6EE'],
                            ],
                        ]);
                    }
                }

                // Fijar la primera fila (freeze pane)
                $sheet->freezePane('A2');

                // Tamaño de fuente base para datos
                $sheet->getStyle($dataRange)->getFont()->setSize(9);

                // Altura de la fila de encabezado
                $sheet->getRowDimension(1)->setRowHeight(18);
            },
        ];
    }
}
