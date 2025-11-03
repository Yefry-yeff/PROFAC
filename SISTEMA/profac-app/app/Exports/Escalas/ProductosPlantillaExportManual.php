<?php

namespace App\Exports\Escalas;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;

/**
 * Clase: ProductosPlantillaExportManual
 * -------------------------------------
 * Genera la plantilla de Excel para carga manual de precios de productos.
 *
 * Esta clase se encarga de construir una consulta dinámica hacia la base de datos,
 * estructurar los datos en columnas estándar, y exportarlos con encabezados fijos.
 *
 * Implementa:
 *   - FromQuery:   Genera el Excel directamente a partir de una consulta SQL.
 *   - WithHeadings: Define los encabezados visibles en la hoja de cálculo.
 *   - WithMapping:  Permite mapear cada registro de la consulta a las columnas exportadas.
 *
 * Contexto:
 *   Se usa cuando el usuario selecciona el modo "Manual" para definir precios de productos,
 *   a diferencia del modo "Escalable".
 */

class ProductosPlantillaExportManual implements FromQuery, WithHeadings, WithMapping
{
    use Exportable; // Permite ejecutar métodos como ->download() o ->store() del paquete Excel

    // Parámetros dinámicos inyectados desde el controlador
    protected $tipoFiltro;     // Define el tipo de filtro (1 = Marca, 2 = Categoría)
    protected $valorFiltro;    // Valor del filtro (ID de la marca o categoría)
    protected $valorCategoria; // ID de la categoría de precios seleccionada

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

    /**
     * Construye la consulta SQL principal que alimenta el archivo Excel.
     *
     * Se realiza un JOIN entre las tablas relacionadas (producto, marca, subcategoría,
     * categoría de producto y unidad de medida). La información se normaliza con alias
     * legibles y campos predefinidos.
     */
    public function query()
    {
        $query = DB::table('producto as A')
            ->join('marca as B', 'B.id', '=', 'A.marca_id')
            ->join('sub_categoria as C', 'C.id', '=', 'A.sub_categoria_id')
            ->join('categoria_producto as D', 'D.id', '=', 'C.categoria_producto_id')
            ->join('unidad_medida as E', 'E.id', '=', 'A.unidad_medida_compra_id')
            ->selectRaw("
                2 as idtipocategoria,                          -- Identificador del tipo de categoría (2 = Manual)
                'Manual' as tipocategoriaprecio,               -- Descripción del tipo de categoría
                A.id as idproducto,                            -- ID del producto
                A.nombre as nombreProducto,                    -- Nombre comercial del producto
                A.descripcion as descripcionProducto,          -- Descripción o detalle del producto
                E.id as idUnidadMedida,                        -- ID de unidad de medida de compra/venta
                E.nombre as unidadMedia,                       -- Nombre de la unidad de medida
                B.id as idMarca,                               -- ID de la marca
                B.nombre as nombreMarca,                       -- Nombre de la marca
                D.id as idCategoria,                           -- ID de la categoría principal
                D.descripcion as nombreCategoria,              -- Descripción de la categoría principal
                C.id as idsubCategoria,                        -- ID de la subcategoría
                C.descripcion as subcategoriaProducto,         -- Descripción de la subcategoría
                IF(A.isv > 0,'SI','NO') as isv,                -- Indicador si el producto aplica ISV
                A.ultimo_costo_compra as costoProducto,        -- Último costo de compra del producto
                A.precio_base as precioBase                    -- Precio base actual del producto
            ")
            ->orderBy('A.id', 'asc'); // Orden ascendente por ID de producto

        /**
         * Aplicación de filtros condicionales según la selección del usuario.
         * Si el filtro es de tipo 1, se limita por marca.
         * Si es de tipo 2, se filtra por categoría.
         */
        if ($this->tipoFiltro == 1 && $this->valorFiltro) {
            $query->where('A.marca_id', $this->valorFiltro);
        } elseif ($this->tipoFiltro == 2 && $this->valorFiltro) {
            $query->where('D.id', $this->valorFiltro);
        }

        // Se retorna la consulta lista para ser procesada por Maatwebsite Excel
        return $query;
    }

    /**
     * Define los encabezados del Excel (fila 1).
     * Cada columna corresponde al orden que se respetará en el método `map()`.
     */
    public function headings(): array
    {
        return [
            'categoria_precios_id',   // ID de la categoría de precios
            'idtipocategoria',        // ID del tipo de categoría (Manual)
            'tipocategoriaprecio',    // Descripción del tipo de categoría
            'producto_id',            // ID del producto
            'nombreproducto',         // Nombre del producto
            'descripcionproducto',    // Descripción del producto
            'unidad_medida_venta_id', // ID de unidad de medida de venta
            'unidad_medida_venta',    // Nombre de unidad de medida de venta
            'marca_id',               // ID de la marca
            'nombremarca',            // Nombre de la marca
            'categoria_producto_id',  // ID de la categoría del producto
            'nombrecategoria',        // Descripción de la categoría
            'sub_categoria_id',       // ID de subcategoría
            'subcategoriaproducto',   // Descripción de la subcategoría
            'isv',                    // Indicador si aplica ISV
            'costoproducto',          // Costo de compra del producto
            'precio_base_venta',      // Precio base de venta
            'precio_a',               // Precio A (editable por el usuario)
            'precio_b',               // Precio B (editable por el usuario)
            'precio_c',               // Precio C (editable por el usuario)
            'precio_d',               // Precio D (editable por el usuario)
            'precio_compra_usd',      // Precio de compra en USD
            'tipo_cambio_usd',        // Tipo de cambio USD a HNL
            'precio_hnl',             // Precio total en HNL
            'flete',                  // Costo de flete
            'arancel',                // Costo de arancel
            'porc_flete',             // Porcentaje de flete
            'porc_arancel',           // Porcentaje de arancel
            'comentario'              // Comentarios adicionales
        ];
    }

    /**
     * Mapea los resultados de la consulta SQL a las columnas del Excel.
     * Este método garantiza que el orden de los datos coincida con los encabezados definidos.
     */
    public function map($row): array
    {
        return [
            $this->valorCategoria,     // Categoría de precios asignada al producto
            $row->idtipocategoria,     // Tipo de categoría (Manual)
            $row->tipocategoriaprecio, // Nombre del tipo de categoría
            $row->idproducto,          // ID del producto
            $row->nombreProducto,      // Nombre del producto
            $row->descripcionProducto, // Descripción del producto
            $row->idUnidadMedida,      // ID de unidad de medida de venta
            $row->unidadMedia,         // Nombre de la unidad de medida
            $row->idMarca,             // ID de la marca
            $row->nombreMarca,         // Nombre de la marca
            $row->idCategoria,         // ID de la categoría principal
            $row->nombreCategoria,     // Nombre de la categoría
            $row->idsubCategoria,      // ID de la subcategoría
            $row->subcategoriaProducto,// Descripción de la subcategoría
            $row->isv,                 // Indica si aplica ISV
            $row->costoProducto,       // Último costo de compra
            $row->precioBase,          // Precio base actual
            '',                        // Precio A (campo vacío para completar)
            '',                        // Precio B (campo vacío para completar)
            '',                        // Precio C (campo vacío para completar)
            '',                        // Precio D (campo vacío para completar)
            '',                        // Precio de compra en USD
            '',                        // Tipo de cambio USD
            '',                        // Precio total en HNL
            '',                        // Flete
            '',                        // Arancel
            '',                        // % Flete
            '',                        // % Arancel
            '',                        // Comentario adicional
        ];
    }
}
