<?php

namespace App\Exports\Comisiones;

class ProyeccionEspecial15NominaSheet extends ProyeccionNominaSheet
{
    public function title(): string
    {
        return 'Nómina Proyectada';
    }

    public function array(): array
    {
        $rows = parent::array();
        $periodoUpper = mb_strtoupper($this->periodoLabel, 'UTF-8');

        $rows[9][0] = 'BASE COMISIONABLE (FIJO 15% + ESCALA) ' . $periodoUpper;
        $rows[13][0] = 'TOTAL COMISIÓN MIXTA';
        $rows[16][0] = 'TOTAL COMISIÓN PROYECTADA';

        return $rows;
    }
}