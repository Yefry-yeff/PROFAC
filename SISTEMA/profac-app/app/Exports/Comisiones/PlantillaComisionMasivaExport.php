<?php

namespace App\Exports\Comisiones;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class PlantillaComisionMasivaExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithTitle
{
    use Exportable;

    protected ?array $catCliIds    = null;
    protected ?array $catPrecioIds = null;
    protected ?array $rolIds       = null;

    public function __construct(?array $catCliIds = null, ?array $catPrecioIds = null, ?array $rolIds = null)
    {
        $this->catCliIds    = $catCliIds;
        $this->catPrecioIds = $catPrecioIds;
        $this->rolIds       = $rolIds;
    }

    public function title(): string
    {
        return 'Plantilla Comisiones';
    }

    /**
     * Genera todas las combinaciones activas:
     *  rol × cliente_categoria_escala × categoria_precios
     * Si ya existe un registro activo en comision_escala, carga el % actual.
     */
    public function collection()
    {
        $q = DB::table('rol as r')
            ->crossJoin('cliente_categoria_escala as cce')
            ->join('categoria_precios as cp', 'cp.cliente_categoria_escala_id', '=', 'cce.id')
            ->leftJoin('comision_escala as ce', function ($join) {
                $join->on('ce.rol_id', '=', 'r.id')
                     ->on('ce.cliente_categoria_escala_id', '=', 'cce.id')
                     ->on('ce.categoria_precios_id', '=', 'cp.id')
                     ->where('ce.estado_id', 1);
            })
            ->where('cce.estado_id', 1)
            ->where('cp.estado_id', 1);

        if (!empty($this->rolIds)) {
            $q->whereIn('r.id', $this->rolIds);
        }
        if (!empty($this->catCliIds)) {
            $q->whereIn('cce.id', $this->catCliIds);
        }
        if (!empty($this->catPrecioIds)) {
            $q->whereIn('cp.id', $this->catPrecioIds);
        }

        return $q->select(
                'r.id       AS rol_id',
                'r.nombre   AS rol_nombre',
                'cce.id     AS cliente_cat_id',
                'cce.nombre_categoria AS cliente_cat_nombre',
                'cp.id      AS cat_precio_id',
                'cp.nombre  AS cat_precio_nombre',
                DB::raw('IFNULL(ce.porcentaje_comision, "") AS porcentaje_comision')
            )
            ->orderBy('r.nombre')
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'rol_id',
            'Rol',
            'cliente_categoria_id',
            'Categoría Cliente',
            'categoria_precio_id',
            'Categoría Precio',
            '% Comisión (editar aquí)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->rol_id,
            $row->rol_nombre,
            $row->cliente_cat_id,
            $row->cliente_cat_nombre,
            $row->cat_precio_id,
            $row->cat_precio_nombre,
            $row->porcentaje_comision,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // ── Encabezados ─────────────────────────────────────────────────
                $headerStyle = [
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBDBDBD']]],
                ];
                $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // ── Columnas de sólo lectura (A-F) – fondo gris claro ───────────
                if ($lastRow > 1) {
                    $readonlyStyle = [
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F4F7']],
                        'font'      => ['color' => ['argb' => 'FF4A5568'], 'size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                    ];
                    $sheet->getStyle('A2:F' . $lastRow)->applyFromArray($readonlyStyle);

                    // ── Columna G editable – fondo amarillo claro ───────────────
                    $editStyle = [
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFDE7']],
                        'font'      => ['bold' => true, 'color' => ['argb' => 'FF1A237E'], 'size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFC107']]],
                    ];
                    $sheet->getStyle('G2:G' . $lastRow)->applyFromArray($editStyle);

                    // Filas alternas
                    for ($i = 2; $i <= $lastRow; $i++) {
                        if ($i % 2 === 0) {
                            $sheet->getStyle('A' . $i . ':F' . $i)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFE8EDF5');
                        }
                    }
                }

                // ── Anchos de columna ────────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(10);   // rol_id
                $sheet->getColumnDimension('B')->setWidth(28);   // Rol
                $sheet->getColumnDimension('C')->setWidth(20);   // cliente_cat_id
                $sheet->getColumnDimension('D')->setWidth(30);   // Categoría Cliente
                $sheet->getColumnDimension('E')->setWidth(20);   // cat_precio_id
                $sheet->getColumnDimension('F')->setWidth(30);   // Categoría Precio
                $sheet->getColumnDimension('G')->setWidth(28);   // % Comisión

                // ── Proteger columnas de referencia (A-F) ───────────────────────
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword('profac2026');

                // Desproteger solo G (la editable)
                if ($lastRow > 1) {
                    $sheet->getStyle('G2:G' . $lastRow)->getProtection()
                        ->setLocked(Protection::PROTECTION_UNPROTECTED);
                }

                // ── Nota instruccional en H1 ────────────────────────────────────
                $sheet->setCellValue('H1', '⚠ Solo edite la columna G (% Comisión). Deje en blanco para omitir. Los IDs de las columnas A, C y E son usados por el sistema.');
                $sheet->getStyle('H1')->applyFromArray([
                    'font'      => ['italic' => true, 'color' => ['argb' => 'FF856404'], 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF3CD']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getColumnDimension('H')->setWidth(90);

                // Freeze primera fila
                $sheet->freezePane('A2');
            },
        ];
    }
}
