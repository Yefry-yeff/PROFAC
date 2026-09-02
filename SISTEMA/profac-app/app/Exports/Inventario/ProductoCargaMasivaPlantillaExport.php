<?php

namespace App\Exports\Inventario;

use App\Services\Inventario\ProductoCargaMasivaService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductoCargaMasivaPlantillaExport implements WithMultipleSheets
{
    public function __construct(private array $catalogos)
    {
    }

    public function sheets(): array
    {
        return [
            new ProductoCargaMasivaInstruccionesSheet(),
            new ProductoCargaMasivaProductosSheet($this->catalogos),
            new ProductoCargaMasivaCatalogosSheet($this->catalogos),
        ];
    }
}

class ProductoCargaMasivaInstruccionesSheet implements FromArray, ShouldAutoSize, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            ['CARGA MASIVA DE PRODUCTOS'],
            ['Complete la hoja Productos sin modificar sus encabezados.'],
            ['Seleccione en las listas desplegables la marca, categoría, subcategoría, unidades e ISV; no escriba esos valores manualmente.'],
            ['ISV disponible: 0, 15 o 18. Los costos, precios y cantidades deben ser numéricos positivos.'],
            ['Procesar el archivo solo genera una previsualización; ningún producto se crea hasta confirmar Guardar Productos.'],
            ['Código de barras es opcional, pero no puede repetirse en el archivo ni existir en otro producto.'],
        ];
    }

    public function title(): string
    {
        return 'Instrucciones';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        return [];
    }
}

class ProductoCargaMasivaProductosSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private array $catalogos)
    {
    }

    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return ProductoCargaMasivaService::HEADERS;
    }

    public function title(): string
    {
        return 'Productos';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->getStyle('A:B')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Q1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE05A00');
        $sheet->getStyle('A1:Q1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $this->agregarLista($sheet, 'E', '"0,15,18"', 'ISV');
        $this->agregarLista($sheet, 'F', $this->formulaCatalogo('A', count($this->catalogos['marcas'])), 'Marca');
        $this->agregarLista($sheet, 'G', $this->formulaCatalogo('B', count($this->catalogos['categorias'])), 'Categoría');
        $this->agregarLista($sheet, 'H', $this->formulaCatalogo('C', count($this->catalogos['subcategorias'])), 'Subcategoría');
        $formulaUnidades = $this->formulaCatalogo('E', count($this->catalogos['unidades']));
        $this->agregarLista($sheet, 'I', $formulaUnidades, 'Unidad de compra');
        $this->agregarLista($sheet, 'K', $formulaUnidades, 'Unidad de venta');

        return [];
    }

    private function agregarLista(Worksheet $sheet, string $columna, string $formula, string $titulo): void
    {
        $ultimaFila = ProductoCargaMasivaService::MAX_FILAS + 1;
        $validacion = new DataValidation();
        $validacion->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Valor no permitido')
            ->setError('Seleccione un valor de la lista.')
            ->setPromptTitle($titulo)
            ->setPrompt('Seleccione un valor de la lista desplegable.')
            ->setFormula1($formula)
            ->setSqref($columna . '2:' . $columna . $ultimaFila);

        $sheet->setDataValidation($columna . '2', $validacion);
    }

    private function formulaCatalogo(string $columna, int $cantidad): string
    {
        $ultimaFila = max(2, $cantidad + 1);

        return 'INDIRECT("Catalogos!$' . $columna . '$2:$' . $columna . '$' . $ultimaFila . '")';
    }
}

class ProductoCargaMasivaCatalogosSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private array $catalogos)
    {
    }

    public function array(): array
    {
        $marcas = array_values($this->catalogos['marcas']);
        $categorias = array_values($this->catalogos['categorias']);
        $subcategorias = array_values($this->catalogos['subcategorias']);
        $unidades = array_values($this->catalogos['unidades']);
        $isv = [0, 15, 18];
        $categoriasPorId = collect($categorias)->keyBy('id');
        $total = max(count($marcas), count($categorias), count($subcategorias), count($unidades), count($isv));
        $filas = [];

        for ($indice = 0; $indice < $total; $indice++) {
            $subcategoria = $subcategorias[$indice] ?? null;
            $filas[] = [
                $marcas[$indice]->nombre ?? '',
                $categorias[$indice]->descripcion ?? '',
                $subcategoria->descripcion ?? '',
                $subcategoria ? optional($categoriasPorId->get($subcategoria->categoria_producto_id))->descripcion : '',
                $unidades[$indice]->nombre ?? '',
                $isv[$indice] ?? '',
            ];
        }

        return $filas;
    }

    public function headings(): array
    {
        return ['Marca', 'Categoría', 'Subcategoría', 'Categoría de subcategoría', 'Unidad de medida', 'ISV'];
    }

    public function title(): string
    {
        return 'Catalogos';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        return [];
    }
}