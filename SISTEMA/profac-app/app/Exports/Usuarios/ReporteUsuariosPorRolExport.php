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
 * Reporte de usuarios activos agrupados por rol.
 * Columnas: ROL | NOMBRE | EMAIL | IDENTIDAD | TELÉFONO | INGRESO
 */
class ReporteUsuariosPorRolExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    use Exportable;

    const LAST_COL = 'F';

    public function title(): string
    {
        return 'Usuarios por Rol';
    }

    public function query()
    {
        return DB::table('users as u')
            ->join('rol as r', 'r.id', '=', 'u.rol_id')
            ->where('u.estado_id', 1)
            ->where('r.estado_id', 1)
            ->select([
                'r.nombre  as rol_nombre',
                'u.name    as usuario_nombre',
                'u.email',
                'u.identidad',
                'u.telefono',
                'u.created_at',
            ])
            ->orderBy('r.nombre')
            ->orderBy('u.name');
    }

    public function headings(): array
    {
        return ['Rol', 'Nombre', 'Correo', 'Identidad', 'Teléfono', 'Ingreso al Sistema'];
    }

    public function map($row): array
    {
        return [
            $row->rol_nombre,
            $row->usuario_nombre,
            $row->email,
            $row->identidad ?? '—',
            $row->telefono  ?? '—',
            $row->created_at ? date('d/m/Y', strtotime($row->created_at)) : '—',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 32, 'C' => 38, 'D' => 18, 'E' => 16, 'F' => 20];
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

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(18);
                $sheet->setAutoFilter('A1:' . self::LAST_COL . '1');
            },
        ];
    }
}
