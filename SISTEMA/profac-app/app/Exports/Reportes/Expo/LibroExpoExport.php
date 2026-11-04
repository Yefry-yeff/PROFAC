<?php

namespace App\Exports\Reportes\Expo;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LibroExpoExport implements Export, WithMultipleSheets
{
    public function __construct(private array $hojas)
    {
    }

    public function sheets(): array
    {
        return $this->hojas;
    }
}
