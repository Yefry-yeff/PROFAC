<?php

namespace App\Exports\Reportes;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnaliticaProductosExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    use Exportable;

    private array $headings;
    private array $rows;

    public function __construct(array $headings, array $rows)
    {
        $this->headings = $headings;
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $ultimaFila = count($this->rows) + 1;

        foreach ($this->headings as $indice => $encabezado) {
            $columna = Coordinate::stringFromColumnIndex($indice + 1);
            if (preg_match('/\(L\)|Cant\.|Cantidad|Margen|Avance/i', $encabezado)) {
                $sheet->getStyle("{$columna}2:{$columna}{$ultimaFila}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            } elseif (preg_match('/^(Oferta #|Flujo|Ofertas|Facturas)$/i', $encabezado)) {
                $sheet->getStyle("{$columna}2:{$columna}{$ultimaFila}")
                    ->getNumberFormat()->setFormatCode('#,##0');
            }
        }

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A2035']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}