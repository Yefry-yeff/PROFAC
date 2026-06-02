<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteVentasCobrosExport implements WithMultipleSheets
{
    protected $rows;
    protected $usuario;
    protected $movimientos;

    public function __construct($rows, $usuario = 'Sistema', $movimientos = [])
    {
        $this->rows        = $rows;
        $this->usuario     = $usuario;
        $this->movimientos = $movimientos;
    }

    public function sheets(): array
    {
        return [
            new ReporteVentasCobrosHoja($this->rows, $this->usuario, $this->movimientos),
        ];
    }
}
