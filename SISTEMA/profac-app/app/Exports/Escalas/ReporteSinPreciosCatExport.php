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
 * Categorías de cliente que NO tienen ninguna categoría de precio creada.
 */
class ReporteSinPreciosCatExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;

    const LAST_COL = 'E';

    public function query()
    {
        return DB::table('cliente_categoria_escala as cce')
            ->leftJoin('categoria_precios as cp', 'cp.cliente_categoria_escala_id', '=', 'cce.id')
            ->whereNull('cp.id')
            ->select([
                'cce.id',
                'cce.nombre_categoria',
                DB::raw("COALESCE(cce.descripcion_categoria, '') as descripcion_categoria"),
                DB::raw("IF(cce.estado_id = 1, 'ACTIVO', 'INACTIVO') as estado"),
                'cce.created_at',
            ])
            ->orderByDesc('cce.id');
    }

    public function headings(): array
    {
        return ['ID', 'Categoría Cliente', 'Descripción', 'Estado', 'Fecha de Creación'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->nombre_categoria,
            $row->descripcion_categoria,
            $row->estado,
            $row->created_at,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 35, 'C' => 35, 'D' => 12, 'E' => 22];
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
                $sheet->getStyle('D2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(18);
            },
        ];
    }
}
