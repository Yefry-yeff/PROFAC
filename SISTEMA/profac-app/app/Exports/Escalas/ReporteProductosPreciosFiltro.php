<?php

namespace App\Exports\Escalas;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;

class ReporteProductosPreciosFiltro implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $tipoFiltro;
    protected $valorFiltro;
    protected $valorCategoria;

    /**
     * Constructor: inicializa los parámetros del exportador.
     *
     * @param int|null $tipoFiltro   Define si se filtra por marca o categoría.
     * @param int|null $valorFiltro  ID del filtro seleccionado.
     * @param int|null $valorCategoria ID de la categoría de precios seleccionada.
     */
    public function __construct($tipoFiltro = null, $valorFiltro = null, $valorCategoria = null)
    {
        $this->tipoFiltro = $tipoFiltro;
        $this->valorFiltro = $valorFiltro;
        $this->valorCategoria = $valorCategoria;
    }


    public function query()
    {
        $query = DB::table('precios_producto_carga as A')
            ->join('producto as B', 'B.id', '=', 'A.producto_id')
            ->join('categoria_precios as C', 'C.id', '=', 'A.categoria_precios_id')
            ->join('tipo_categoria_precio as D', 'D.id', '=', 'A.tipo_categoria_precio_id')
            ->join('marca as E', 'E.id', '=', 'A.marca_id')
            ->join('sub_categoria as F', 'F.id', '=', 'A.sub_categoria_id')
            ->join('categoria_producto as G', 'G.id', '=', 'A.categoria_producto_id')
            ->join('unidad_medida as H', 'H.id', '=', 'A.unidad_medida_compra_id')
            ->selectRaw("
                    A.id as 'ID_PRECIO',
                    A.producto_id as 'ID_PRODUCTO',
                    C.id AS 'ID_CATEGORIA_DE_PRECIO',
                    C.nombre AS 'CATEGORIA_DE_PRECIO',
                    D.nombre AS 'TIPO_CATEGORIA_PRECIO',
                    E.nombre AS 'MARCA',
                    H.nombre AS 'UNIDAD_DE_MEDIDA_VENTA',
                    B.codigo_barra AS 'CODIGO_DE_BARRA',
                    B.nombre AS 'PRODUCTO',
                    C.porc_precio_a AS 'PORC_A',
                    C.porc_precio_b AS 'PORC_B',
                    C.porc_precio_c AS 'PORC_C',
                    C.porc_precio_d AS 'PORC_D',
                    A.porc_flete AS 'PORC_FLETE',
                    A.porc_arancel AS 'PORC_ARANCEL',
                    A.flete AS 'FLETE_LPS',
                    A.arancel AS 'ARANCEL_LPS',
                    A.precio_base_venta AS 'PRECIO_BASE',
                    A.precio_a AS 'PRECIO_A',
                    A.precio_b AS 'PRECIO_B',
                    A.precio_c AS 'PRECIO_C',
                    A.precio_d AS 'PRECIO_D',
                    B.isv AS 'ISV'
            ")
            ->where('A.estado_id', '=', 1)
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
            'ID_PRECIO',
            'ID_PRODUCTO',
            'ID_CATEGORIA_DE_PRECIO',
            'CATEGORIA_DE_PRECIO',
            'TIPO_CATEGORIA_PRECIO',
            'MARCA',
            'UNIDAD_DE_MEDIDA_VENTA',
            'CODIGO_DE_BARRA',
            'PRODUCTO',
            'PORC_A',
            'PORC_B',
            'PORC_C',
            'PORC_D',
            'PORC_FLETE',
            'PORC_ARANCEL',
            'FLETE_LPS',
            'ARANCEL_LPS',
            'PRECIO_BASE',
            'PRECIO_A',
            'PRECIO_B',
            'PRECIO_C',
            'PRECIO_D',
            'ISV'
        ];
    }


    public function map($row): array
    {
        return [
            $row->ID_PRECIO,
            $row->ID_PRODUCTO,
            $row->ID_CATEGORIA_DE_PRECIO,
            $row->CATEGORIA_DE_PRECIO,
            $row->TIPO_CATEGORIA_PRECIO,
            $row->MARCA,
            $row->UNIDAD_DE_MEDIDA_VENTA,
            $row->CODIGO_DE_BARRA,
            $row->PRODUCTO,
            $row->PORC_A,
            $row->PORC_B,
            $row->PORC_C,
            $row->PORC_D,
            $row->PORC_FLETE,
            $row->PORC_ARANCEL,
            $row->FLETE_LPS,
            $row->ARANCEL_LPS,
            $row->PRECIO_BASE,
            $row->PRECIO_A,
            $row->PRECIO_B,
            $row->PRECIO_C,
            $row->PRECIO_D,
            $row->ISV
        ];
    }
}
