<?php

namespace App\Exports\Comisiones;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ConciliacionResumenMasivoExport implements WithMultipleSheets
{
    protected array $empleados;
    protected string $periodo;
    protected string $periodoLabel;

    public function __construct(array $empleados, string $periodo, string $periodoLabel)
    {
        $this->empleados = $empleados;
        $this->periodo = $periodo;
        $this->periodoLabel = $periodoLabel;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->empleados as $index => $empleadoData) {
            $sheets[] = new ConciliacionEmpleadoResumenSheet(
                $empleadoData,
                $this->periodo,
                $this->periodoLabel,
                $index + 1
            );
        }

        return $sheets;
    }
}
