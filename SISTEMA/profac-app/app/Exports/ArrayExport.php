<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayExport implements FromArray, WithHeadings
{
    protected array $headers;
    protected array $rows;

    public function __construct(array $headers, array $rows)
    {
        $this->headers = $headers;
        $this->rows    = $rows;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function array(): array
    {
        return $this->rows;
    }
}
