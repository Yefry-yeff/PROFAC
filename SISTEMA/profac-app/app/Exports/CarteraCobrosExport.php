<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CarteraCobrosExport implements Export, WithMultipleSheets
{
    public function __construct(private array $tablas) {}

    public function sheets(): array
    {
        return array_values(array_map(fn ($tabla) => new CarteraCobrosSheet(...$tabla), $this->tablas));
    }
}

class CarteraCobrosSheet extends DefaultValueBinder implements FromArray, WithTitle, WithStyles, WithCustomValueBinder, WithStrictNullComparison, ShouldAutoSize
{
    public function __construct(private string $nombre, private array $encabezados, private array $filas) {}

    public function title(): string
    {
        return $this->nombre;
    }

    public function array(): array
    {
        return array_merge([$this->encabezados], $this->filas);
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $ultima = $sheet->getHighestColumn();
        $sheet->setAutoFilter("A1:{$ultima}1");
        $sheet->freezePane('A2');
        $sheet->getStyle("A1:{$ultima}1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A1:{$ultima}1")->getFill()->setFillType('solid')->getStartColor()->setRGB('E67E22');
        foreach ($this->encabezados as $i => $nombre) {
            if (preg_match('/Saldo|Vencido|Cobrado|Diferencia|Aplicado|%/', $nombre)) {
                $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->getStyle("{$columna}2:{$columna}" . max(2, $sheet->getHighestRow()))
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }
        return [];
    }
}
