<?php

namespace App\Exports;

use App\Models\ModelCliente;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ClientesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //return ModelCliente::all();
        return ModelCliente::select("id","nombre","direccion","telefono_empresa","rtn","correo","credito","dias_credito","users_id")->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Nombre',
            'Direccion',
            'Teléfono',
            'RTN',
            'Correo',
            'Crédito',
            'Dias de Crédito',
            'ID Usuario',
        ];
    }
}
