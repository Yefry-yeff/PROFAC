<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductosSinImagenesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected Collection $rows;

    public function __construct(array $rows = [])
    {
        $this->rows = collect($rows);
    }

    public function collection()
    {
        return $this->rows->map(function (array $row) {
            return [
                $row['codigo_referencia'] ?? '',
                $row['producto'] ?? '',
                $row['categoria'] ?? '',
                $row['sub_categoria'] ?? '',
                $row['marca'] ?? '',
                $row['precio_base'] ?? 0,
                $row['estado'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Código',
            'Producto',
            'Categoría',
            'Subcategoría',
            'Marca',
            'Precio base',
            'Estado',
        ];
    }
}