<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteClientesExport implements WithMultipleSheets
{
    protected $general;
    protected $sinCredito;
    protected $gobierno;
    protected $usuario;

    public function __construct($general, $sinCredito, $gobierno, $usuario = 'Sistema')
    {
        $this->general    = $general;
        $this->sinCredito = $sinCredito;
        $this->gobierno   = $gobierno;
        $this->usuario    = $usuario;
    }

    public function sheets(): array
    {
        return [
            new ReporteClientesHoja1($this->general,    $this->usuario),
            new ReporteClientesHoja2($this->sinCredito, $this->usuario),
            new ReporteClientesHoja3($this->gobierno,   $this->usuario),
        ];
    }
}
