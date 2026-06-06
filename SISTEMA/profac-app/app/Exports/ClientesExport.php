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
        return ModelCliente::select(
        'cliente.id',
        'cliente_categoria_escala.nombre_categoria as nombre_cat_escala',
        'cliente.nombre',
        'cliente.direccion',
        'cliente.telefono_empresa',
        'cliente.rtn',
        'cliente.correo',
        'cliente.credito',
        'cliente.dias_credito',
        'cliente.users_id'
    )
    ->join('cliente_categoria_escala', 'cliente_categoria_escala.id', '=', 'cliente.cliente_categoria_escala_id')
    ->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Categoría Escala',
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
