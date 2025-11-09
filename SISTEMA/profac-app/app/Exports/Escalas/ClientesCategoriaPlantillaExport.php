<?php
namespace App\Exports\Escalas;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;

use App\Models\ModelCliente;
use Maatwebsite\Excel\Concerns\{FromQuery, WithHeadings, WithMapping, ShouldAutoSize};

class ClientesCategoriaPlantillaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
{
    return \DB::table('cliente as c')
        ->leftJoin('cliente_categoria_escala as cat', 'cat.id', '=', 'c.cliente_categoria_escala_id')
        ->select(
            'c.id',
            'c.nombre',
            'c.rtn',
            'c.correo',
            'c.cliente_categoria_escala_id',
            \DB::raw('COALESCE(cat.nombre_categoria, "") as categoria_nombre')
        )
        ->where('c.estado_cliente_id','=', 1)
        ->orderBy('c.id');
}

public function headings(): array
{
    return ['id','nombre','rtn','correo','categoria_actual_id','categoria_actual_nombre','nueva_categoria_id'];
}

public function map($c): array
{
    return [
        $c->id,
        $c->nombre,
        $c->rtn,
        $c->correo,
        $c->cliente_categoria_escala_id,        // lo que ya tiene
        $c->categoria_nombre,                   // solo informativo
        ''                                      // usuario rellena nueva
    ];
}
}
