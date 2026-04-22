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
 * Cobertura de categorías de precio por categoría de cliente.
 * Muestra cuántas categorías de precio y productos tiene configurada cada categoría.
 */
class ReporteCoberturaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;

    const LAST_COL = 'G';

    public function query()
    {
        return DB::table('cliente_categoria_escala as cce')
            ->select([
                'cce.id',
                'cce.nombre_categoria',
                DB::raw("IF(cce.estado_id = 1, 'ACTIVO', 'INACTIVO') as estado"),
                DB::raw("(SELECT COUNT(*) FROM categoria_precios WHERE cliente_categoria_escala_id = cce.id) as total_cat_precios"),
                DB::raw("(SELECT COUNT(*) FROM categoria_precios WHERE cliente_categoria_escala_id = cce.id AND estado_id = 1) as cat_activas"),
                DB::raw("(SELECT COUNT(DISTINCT ppc.producto_id)
                          FROM precios_producto_carga ppc
                          JOIN categoria_precios cp2 ON cp2.id = ppc.categoria_precios_id
                          WHERE cp2.cliente_categoria_escala_id = cce.id AND ppc.estado_id = 1) as total_productos"),
                'cce.created_at',
            ])
            ->orderByDesc('cce.id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Categoría Cliente',
            'Estado',
            'Total Cat. Precio',
            'Cat. Precio Activas',
            'Productos Configurados',
            'Fecha Creación',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->nombre_categoria,
            $row->estado,
            $row->total_cat_precios,
            $row->cat_activas,
            $row->total_productos,
            $row->created_at,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 34, 'C' => 12, 'D' => 18, 'E' => 20, 'F' => 22, 'G' => 22];
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

                $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C2:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(18);
            },
        ];
    }
}
