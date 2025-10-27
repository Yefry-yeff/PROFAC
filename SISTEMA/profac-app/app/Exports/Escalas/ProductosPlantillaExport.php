<?php

namespace App\Exports\Escalas;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class ProductosPlantillaExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
     return DB::table('producto as A')
        ->join('marca as B', 'B.id', '=', 'A.marca_id')
        ->join('sub_categoria as C', 'C.id', '=', 'A.sub_categoria_id')
        ->join('categoria_producto as D', 'D.id', '=', 'C.categoria_producto_id')
        ->join('unidad_medida as E', 'E.id', '=', 'A.unidad_medida_compra_id')
        ->selectRaw("
            1 as idtipocategoria,
            'Escalable' as tipocategoriaprecio,
            A.id as idproducto,
            A.nombre as nombreProducto,
            A.descripcion as descripcionProducto,
            E.id as idUnidadMedida,
            E.nombre as unidadMedia,
            B.id as idMarca,
            B.nombre as nombreMarca,
            D.id as idCategoria,
            D.descripcion as nombreCategoria,
            C.id as idsubCategoria,
            C.descripcion as subcategoriaProducto,
            IF(A.isv > 0,'SI','NO') as ISV,
            A.ultimo_costo_compra as costoProducto,
            A.precio_base as precioBase
        ")
        ->orderBy('A.id', 'asc');
    }

    public function headings(): array
    {
        return [
            'idtipocategoria',
            'tipocategoriaprecio',
            'idproducto',
            'nombreproducto',
            'descripcionproducto',
            'idmedida',
            'unidadmedida',
            'idmarca',
            'nombremarca',
            'idcategoria',
            'nombrecategoria',
            'idsubcategoria',
            'subcategoriaproducto',
            'costoproducto',
            'preciobaseventa',
            'Observaciones (llenar)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->idtipocategoria,
            $row->tipocategoriaprecio,
            $row->idproducto,
            $row->nombreProducto,
            $row->descripcionProducto,
            $row->idUnidadMedida,
            $row->unidadMedia,
            $row->idMarca,
            $row->nombreMarca,
            $row->idCategoria,
            $row->nombreCategoria,
            $row->idsubCategoria,
            $row->subcategoriaProducto,
            $row->costoProducto,
            $row->precioBase,
            '', // campo vacío para llenar manualmente
            '', // campo vacío para llenar manualmente
        ];
    }
}
