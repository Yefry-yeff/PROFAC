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
 * Productos activos que no tienen ninguna configuración de precios activa.
 */
class ReporteProductosSinPreciosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;

    const LAST_COL = 'C';

    public function query()
    {
        return DB::table('producto as p')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('precios_producto_carga as ppc')
                    ->whereRaw('ppc.producto_id = p.id')
                    ->where('ppc.estado_id', 1);
            })
            ->select(['p.id', 'p.codigo_barra', 'p.nombre'])
            ->orderByDesc('p.id');
    }

    public function headings(): array
    {
        return ['ID', 'Código de Barra', 'Nombre del Producto'];
    }

    public function map($row): array
    {
        return [$row->id, $row->codigo_barra ?? '—', $row->nombre];
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 22, 'C' => 48];
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

                $sheet->getStyle('A2:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(18);
            },
        ];
    }
}
