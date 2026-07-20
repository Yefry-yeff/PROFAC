<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteVentasCobrosExport implements WithMultipleSheets
{
    protected $rows;
    protected $usuario;
    protected $movimientos;
    protected $fastMode;
    protected $superFastMode;
    protected $summaryMode;

    public function __construct($rows, $usuario = 'Sistema', $movimientos = [], $fastMode = false, $superFastMode = false, $summaryMode = false)
    {
        $this->rows         = $rows;
        $this->usuario      = $usuario;
        $this->movimientos  = $movimientos;
        $this->fastMode     = (bool) $fastMode;
        $this->superFastMode = (bool) $superFastMode;
        $this->summaryMode  = (bool) $summaryMode;
    }

    public function sheets(): array
    {
        return [
            new ReporteVentasCobrosHoja($this->rows, $this->usuario, $this->movimientos, $this->fastMode, $this->superFastMode, $this->summaryMode),
        ];
    }
}
