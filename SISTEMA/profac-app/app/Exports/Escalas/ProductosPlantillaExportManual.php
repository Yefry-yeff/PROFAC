<?php

namespace App\Exports\Escalas;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;

class ProductosPlantillaExportManual implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $tipoFiltro;
    protected $valorFiltro;
    protected $valorCategoria;

    public function __construct($tipoFiltro = null, $valorFiltro = null, $valorCategoria = null)
    {
        $this->tipoFiltro = $tipoFiltro;
        $this->valorFiltro = $valorFiltro;
        $this->valorCategoria = $valorCategoria;
    }

    public function query()
    {
        $query = DB::table('producto as A')
            ->join('marca as B', 'B.id', '=', 'A.marca_id')
            ->join('sub_categoria as C', 'C.id', '=', 'A.sub_categoria_id')
            ->join('categoria_producto as D', 'D.id', '=', 'C.categoria_producto_id')
            ->join('unidad_medida as E', 'E.id', '=', 'A.unidad_medida_compra_id')
            ->selectRaw("
                2 as idtipocategoria,
                'Manual' as tipocategoriaprecio,
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
                IF(A.isv > 0,'SI','NO') as isv,
                A.ultimo_costo_compra as costoProducto,
                A.precio_base as precioBase
            ")
            ->orderBy('A.id', 'asc');

        if ($this->tipoFiltro == 1 && $this->valorFiltro) {
            $query->where('A.marca_id', $this->valorFiltro);
        } elseif ($this->tipoFiltro == 2 && $this->valorFiltro) {
            $query->where('D.id', $this->valorFiltro);
        }

        return $query;
    }

    public function headings(): array
    {
         return [
            'categoria_precios_id',
            'idtipocategoria',
            'tipocategoriaprecio',
            'producto_id',
            'nombreproducto',
            'descripcionproducto',
            'unidad_medida_venta_id',
            'unidad_medida_venta',
            'marca_id',
            'nombremarca',
            'categoria_producto_id',
            'nombrecategoria',
            'sub_categoria_id',
            'subcategoriaproducto',
            'isv',
            'costoproducto',
            'precio_base_venta',
            'precio_a',
            'precio_b',
            'precio_c',
            'precio_d',
            'precio_compra_usd',
            'tipo_cambio_usd',
            'precio_hnl',
            'flete',
            'arancel',
            'porc_flete',
            'porc_arancel',
            'comentario'
        ];
    }

    public function map($row): array
    {
        return [
            $this->valorCategoria,
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
            $row->isv,
            $row->costoProducto,
            $row->precioBase,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
    }
}
