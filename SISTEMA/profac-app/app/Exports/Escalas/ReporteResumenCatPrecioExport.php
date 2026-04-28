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
 * Resumen de cada categoría de precio con su cantidad de productos configurados.
 * Soporta filtro opcional por categoría de cliente y por estado.
 */
class ReporteResumenCatPrecioExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;

    const LAST_COL = 'J';

    protected ?int $catClienteId;
    protected ?int $estadoId;

    public function __construct(?int $catClienteId = null, ?int $estadoId = null)
    {
        $this->catClienteId = $catClienteId;
        $this->estadoId     = $estadoId;
    }

    public function query()
    {
        return DB::table('categoria_precios as cp')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->leftJoin(
                DB::raw('(SELECT categoria_precios_id, COUNT(DISTINCT producto_id) AS cnt
                          FROM precios_producto_carga WHERE estado_id = 1
                          GROUP BY categoria_precios_id) AS pc'),
                'pc.categoria_precios_id',
                '=',
                'cp.id'
            )
            ->select([
                'cp.id',
                'cp.nombre as categoria_precio',
                'cce.nombre_categoria as categoria_cliente',
                'cp.porc_precio_a',
                'cp.porc_precio_b',
                'cp.porc_precio_c',
                'cp.porc_precio_d',
                DB::raw("IF(cp.estado_id = 1, 'ACTIVO', 'INACTIVO') as estado"),
                DB::raw('COALESCE(pc.cnt, 0) as total_productos'),
                'cp.fecha_ultima_actualizacion',
            ])
            ->when($this->catClienteId, fn ($q) => $q->where('cp.cliente_categoria_escala_id', $this->catClienteId))
            ->when($this->estadoId !== null, fn ($q) => $q->where('cp.estado_id', $this->estadoId))
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Categoría Precio',
            'Categoría Cliente',
            '% A',
            '% B',
            '% C',
            '% D',
            'Estado',
            'Total Productos',
            'Últ. Actualización',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->categoria_precio,
            $row->categoria_cliente,
            $row->porc_precio_a,
            $row->porc_precio_b,
            $row->porc_precio_c,
            $row->porc_precio_d,
            $row->estado,
            $row->total_productos,
            $row->fecha_ultima_actualizacion,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8, 'B' => 26, 'C' => 28, 'D' => 8,
            'E' => 8, 'F' => 8,  'G' => 8,  'H' => 12,
            'I' => 16, 'J' => 22,
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

                $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D2:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(18);
            },
        ];
    }
}
