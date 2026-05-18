<?php

namespace App\Exports\Usuarios;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;

/**
 * Reporte de todos los accesos (submenús) disponibles por rol.
 * Filas: ROL | MENÚ | SUBMENÚ | URL
 */
class ReporteAccesosRolExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    use Exportable;

    const LAST_COL = 'D';

    public function title(): string
    {
        return 'Accesos por Rol';
    }

    public function query()
    {
        return DB::table('rol as r')
            ->join('rol_submenu as rs',  'rs.rol_id',    '=', 'r.id')
            ->join('sub_menu as sm',     'sm.id',        '=', 'rs.sub_menu_id')
            ->join('menu as m',          'm.id',         '=', 'sm.menu_id')
            ->where('r.estado_id',  1)
            ->where('sm.estado_id', 1)
            ->where('m.estado_id',  1)
            ->select([
                'r.nombre  as rol_nombre',
                'm.nombre_menu',
                'sm.nombre as submenu_nombre',
                'sm.url    as submenu_url',
            ])
            ->orderBy('r.nombre')
            ->orderBy('m.orden')
            ->orderBy('m.nombre_menu')
            ->orderBy('sm.orden')
            ->orderBy('sm.nombre');
    }

    public function headings(): array
    {
        return ['Rol', 'Menú', 'Submenú / Opción', 'URL'];
    }

    public function map($row): array
    {
        return [
            $row->rol_nombre,
            $row->nombre_menu,
            $row->submenu_nombre,
            $row->submenu_url,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 30, 'C' => 36, 'D' => 40];
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

                // Bordes
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5C5B5']]],
                ]);

                // Filas alternas
                for ($r = 2; $r <= $lastRow; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:" . self::LAST_COL . "{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDF6EE']],
                        ]);
                    }
                }

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(18);

                // Autofilter
                $sheet->setAutoFilter('A1:' . self::LAST_COL . '1');
            },
        ];
    }
}
