<?php

namespace App\Exports\Escalas;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;

/**
 * Comparativo de precios de un producto específico en todas sus categorías.
 */
class ReporteComparativoExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;

    const LAST_COL = 'L';

    protected int $produtoId;
    protected string $produtoNombre;

    public function __construct(int $produtoId, string $produtoNombre = '')
    {
        $this->produtoId     = $produtoId;
        $this->produtoNombre = $produtoNombre;
    }

    public function query()
    {
        return DB::table('precios_producto_carga as ppc')
            ->join('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->where('ppc.producto_id', $this->produtoId)
            ->select([
                'cce.nombre_categoria as categoria_cliente',
                'cp.nombre as categoria_precio',
                'cp.porc_precio_a',
                'cp.porc_precio_b',
                'cp.porc_precio_c',
                'cp.porc_precio_d',
                'ppc.precio_base_venta',
                'ppc.precio_a',
                'ppc.precio_b',
                'ppc.precio_c',
                'ppc.precio_d',
                DB::raw("IF(ppc.estado_id = 1, 'ACTIVO', 'INACTIVO') as estado"),
            ])
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre');
    }

    public function headings(): array
    {
        return [
            'Categoría Cliente',
            'Categoría Precio',
            '% A',
            '% B',
            '% C',
            '% D',
            'Precio Base',
            'Precio A',
            'Precio B',
            'Precio C',
            'Precio D',
            'Estado',
        ];
    }

    public function map($row): array
    {
        return [
            $row->categoria_cliente,
            $row->categoria_precio,
            $row->porc_precio_a,
            $row->porc_precio_b,
            $row->porc_precio_c,
            $row->porc_precio_d,
            number_format((float) $row->precio_base_venta, 2),
            number_format((float) $row->precio_a, 2),
            number_format((float) $row->precio_b, 2),
            number_format((float) $row->precio_c, 2),
            number_format((float) $row->precio_d, 2),
            $row->estado,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28, 'B' => 24, 'C' => 8, 'D' => 8,
            'E' => 8,  'F' => 8,  'G' => 14, 'H' => 14,
            'I' => 14, 'J' => 14, 'K' => 14, 'L' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E67E22']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $range   = 'A1:' . self::LAST_COL . $lastRow;

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5C5B5']]],
                ]);

                for ($r = 2; $r <= $lastRow; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:" . self::LAST_COL . "{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDF6EE']],
                        ]);
                    }
                }

                $sheet->getStyle('C2:L' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(18);
            },
        ];
    }
}
