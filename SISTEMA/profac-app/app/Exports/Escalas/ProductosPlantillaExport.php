<?php
namespace App\Exports\Escalas;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Clase de exportación que genera la plantilla de productos
 * para carga de precios por categoría.
 *
 * Implementa:
 *  - FromQuery: para generar el Excel directamente desde una consulta SQL.
 *  - WithHeadings: para definir encabezados personalizados.
 *  - WithMapping: para mapear los resultados a las columnas del archivo.
 *  - WithEvents: para aplicar protección de celdas.
 */
class ProductosPlantillaExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    use Exportable; // Habilita métodos como ->download() o ->store() propios de Maatwebsite Excel

    // Variables recibidas desde el controlador o la vista
    protected $tipoFiltro;     // Define si el filtro es por marca (1) o por categoría (2)
    protected $valorFiltro;    // ID del filtro seleccionado (marca_id o categoria_id)
    protected $valorCategoria; // ID de la categoría de precios asociada a esta exportación

    /**
     * Constructor: recibe parámetros de contexto para generar el archivo.
     */
    public function __construct($tipoFiltro = null, $valorFiltro = null, $valorCategoria = null)
    {
        $this->tipoFiltro = $tipoFiltro;
        $this->valorFiltro = $valorFiltro;
        $this->valorCategoria = $valorCategoria;
    }

    /**
     * Construye la consulta SQL base utilizada para generar el Excel.
     * Se ejecuta directamente sobre la base de datos, aplicando joins y filtros dinámicos.
     */
    public function query()
    {
        /**
         * Estructura principal de la consulta:
         * Se realiza un join entre producto, marca, subcategoría, categoría y unidad de medida.
         * Los campos se renombran con alias legibles y se definen algunos valores fijos (por ejemplo, tipo de categoría).
         */
        $query = DB::table('producto as A')
            ->join('marca as B', 'B.id', '=', 'A.marca_id')
            ->join('sub_categoria as C', 'C.id', '=', 'A.sub_categoria_id')
            ->join('categoria_producto as D', 'D.id', '=', 'C.categoria_producto_id')
            ->join('unidad_medida as E', 'E.id', '=', 'A.unidad_medida_compra_id')
            ->leftJoin('precios_producto_carga as F', function($join) {
                $join->on('F.producto_id', '=', 'A.id')
                     ->where('F.estado_id', '=', 1)
                     ->where('F.categoria_precios_id', '=', $this->valorCategoria);
            })
            ->selectRaw("
                1 as idtipocategoria,                             -- Tipo de categoría por defecto (1 = Escalable)
                'Escalable' as tipocategoriaprecio,               -- Descripción del tipo de categoría
                A.id as idproducto,                               -- ID del producto
                A.nombre as nombreProducto,                       -- Nombre comercial del producto
                A.descripcion as descripcionProducto,              -- Descripción o detalle adicional
                E.id as idUnidadMedida,                           -- ID de la unidad de medida
                E.nombre as unidadMedia,                          -- Nombre de la unidad de medida
                B.id as idMarca,                                  -- ID de la marca
                B.nombre as nombreMarca,                          -- Nombre de la marca
                D.id as idCategoria,                              -- ID de la categoría principal
                D.descripcion as nombreCategoria,                 -- Nombre de la categoría principal
                C.id as idsubCategoria,                           -- ID de la subcategoría
                C.descripcion as subcategoriaProducto,             -- Nombre de la subcategoría
                IF(A.isv > 0,'SI','NO') as isv,                   -- Indicador de ISV (Impuesto sobre ventas)
                A.ultimo_costo_compra as costoProducto,            -- Último costo de compra registrado
                COALESCE(F.precio_base_venta, A.precio_base) as precioBase  -- Precio base: primero de precios_producto_carga activo, si no existe toma de producto
            ")
            ->where('A.estado_producto_id','=', 1)
            ->orderBy('A.id', 'asc');

        /**
         * Aplicación dinámica de filtros:
         *  - tipoFiltro = 1 → Filtra por marca
         *  - tipoFiltro = 2 → Filtra por categoría
         */
        if ($this->tipoFiltro == 1 && $this->valorFiltro) {
            $query->where('A.marca_id', $this->valorFiltro);
        } elseif ($this->tipoFiltro == 2 && $this->valorFiltro) {
            $query->where('D.id', $this->valorFiltro);
        }

        return $query;
    }

    /**
     * Define los encabezados del archivo Excel.
     * Estos nombres son los que se mostrarán en la primera fila del archivo exportado.
     */
    public function headings(): array
    {
        return [
            'categoria_precios_id',   // ID de la categoría de precios a la que pertenece este producto
            'idtipocategoria',        // ID del tipo de categoría (1=Escalable, 2=Manual)
            'tipocategoriaprecio',    // Nombre o descripción del tipo de categoría
            'producto_id',            // ID único del producto
            'nombreproducto',         // Nombre del producto
            'descripcionproducto',    // Descripción del producto
            'unidad_medida_compra_id',// ID de unidad de medida de compra
            'unidad_medida_compra',   // Nombre de unidad de medida de compra
            'marca_id',               // ID de la marca
            'nombremarca',            // Nombre de la marca
            'categoria_producto_id',  // ID de la categoría de producto
            'nombrecategoria',        // Nombre de la categoría de producto
            'sub_categoria_id',       // ID de subcategoría
            'subcategoriaproducto',   // Nombre de la subcategoría
            'isv',                    // Indica si aplica ISV
            'costoproducto',          // Costo del producto
            'precio_base_venta',      // Precio base de venta
            'precio_compra_usd',      // Precio de compra en USD (campo vacío para rellenar luego)
            'tipo_cambio_usd',        // Tipo de cambio USD → HNL (campo vacío)
            'precio_hnl',             // Precio en Lempiras (campo vacío)
            'flete',                  // Flete (campo vacío)
            'arancel',                // Arancel (campo vacío)
            'porc_flete',             // % Flete (campo vacío)
            'porc_arancel',           // % Arancel (campo vacío)
            'comentario'              // Comentarios o notas del producto
        ];
    }

    /**
     * Mapea los resultados de la consulta SQL a las columnas del Excel.
     * Aquí se ordenan y estructuran los datos según el encabezado definido.
     */
    public function map($row): array
    {
        return [
            $this->valorCategoria,     // ID de categoría de precios actual
            $row->idtipocategoria,     // ID del tipo de categoría (fijo en 1 por ahora)
            $row->tipocategoriaprecio, // Descripción del tipo de categoría
            $row->idproducto,          // ID del producto
            $row->nombreProducto,      // Nombre del producto
            $row->descripcionProducto, // Descripción del producto
            $row->idUnidadMedida,      // ID de la unidad de medida
            $row->unidadMedia,         // Nombre de la unidad de medida
            $row->idMarca,             // ID de la marca
            $row->nombreMarca,         // Nombre de la marca
            $row->idCategoria,         // ID de la categoría principal
            $row->nombreCategoria,     // Nombre de la categoría principal
            $row->idsubCategoria,      // ID de la subcategoría
            $row->subcategoriaProducto,// Nombre de la subcategoría
            $row->isv,                 // Indica si aplica ISV (SI/NO)
            $row->costoProducto,       // Último costo de compra
            $row->precioBase,          // Precio base de venta
            '',                        // Precio de compra en USD (vacío para rellenar)
            '',                        // Tipo de cambio USD (vacío)
            '',                        // Precio en HNL (vacío)
            '',                        // Flete (vacío)
            '',                        // Arancel (vacío)
            '',                        // % Flete (vacío)
            '',                        // % Arancel (vacío)
            '',                        // Comentario (vacío)
        ];
    }

    /**
     * Registra eventos para proteger las columnas especificadas.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Obtener el número total de filas
                $highestRow = $sheet->getHighestRow();

                // Desproteger toda la hoja primero
                $sheet->getProtection()->setSheet(false);

                // Columnas a bloquear (A-O): categoria_precios_id hasta isv
                $columnasProtegidas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O','P'];

                // Aplicar estilo bloqueado a las columnas protegidas (desde fila 2 hasta el final)
                foreach ($columnasProtegidas as $columna) {
                    $rango = $columna . '2:' . $columna . $highestRow;
                    $sheet->getStyle($rango)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

                    // Aplicar color de fondo gris claro para indicar que están bloqueadas
                    $sheet->getStyle($rango)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFE0E0E0');
                }

                // Desbloquear las columnas editables (P en adelante): costoproducto, precio_base_venta, etc.
                $columnasEditables = ['Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];
                foreach ($columnasEditables as $columna) {
                    $rango = $columna . '2:' . $columna . $highestRow;
                    $sheet->getStyle($rango)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                }

                // Proteger también los encabezados (fila 1 completa)
                $sheet->getStyle('A1:Y1')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

                // Activar la protección de la hoja
                $sheet->getProtection()->setPassword(''); // Sin contraseña para que puedan desproteger si necesitan
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setSort(false);
                $sheet->getProtection()->setInsertRows(false);
                $sheet->getProtection()->setDeleteRows(false);
                $sheet->getProtection()->setInsertColumns(false);
                $sheet->getProtection()->setDeleteColumns(false);
                $sheet->getProtection()->setFormatCells(false);
            },
        ];
    }
}
