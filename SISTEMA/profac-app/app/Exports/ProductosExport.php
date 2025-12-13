<?php

namespace App\Exports;

use App\Models\ModelProducto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ProductosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //return ModelProducto::all();
         return ModelProducto::select(
            "producto.id",
            "producto.nombre",
            "producto.descripcion",
            "producto.isv",
            "producto.precio_base",
            "producto.ultimo_costo_compra",
            "producto.costo_promedio",
            "producto.codigo_barra",
            "producto.codigo_estatal",
            "producto.unidadad_compra",
            "unidad_medida.nombre as unidad_medida",
            "producto.users_id",
            "producto.sub_categoria_id"
        )
        ->join("unidad_medida", "unidad_medida.id", "=", "producto.unidad_medida_compra_id")
        ->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Nombre',
            'Descripción',
            'ISV',
            'Precio Base',
            'Último Costo de Compra',
            'Costo Promedio',
            'Código de Barra',
            'Código Estatal',
            'Unidad de Compra',
            'unidad de medida',
            'ID Usuario',
            'Sub Categoria',
        ];
    }
}
