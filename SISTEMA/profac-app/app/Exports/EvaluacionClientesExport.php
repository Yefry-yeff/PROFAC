<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EvaluacionClientesExport implements WithMultipleSheets
{
    protected $rows;
    protected $usuario;

    public function __construct($rows, $usuario = 'Sistema')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
    }

    public function sheets(): array
    {
        return [
            new EvaluacionClientesHoja($this->rows, $this->usuario),
        ];
    }
}
