<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class NotasDebitoExport implements WithMultipleSheets
{
    protected $rows;
    protected $usuario;
    protected $titulo;

    public function __construct($rows, $usuario = 'Sistema', $titulo = 'Notas de Débito')
    {
        $this->rows    = $rows;
        $this->usuario = $usuario;
        $this->titulo  = $titulo;
    }

    public function sheets(): array
    {
        return [
            new NotasDebitoHoja($this->rows, $this->usuario, $this->titulo),
        ];
    }
}
