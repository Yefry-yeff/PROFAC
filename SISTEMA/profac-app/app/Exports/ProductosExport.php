<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filtros;

    public function __construct(array $filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $excluidos = [
            1157,1321,2665,2585,2409,2464,1569,1506,2708,2937,2645,1118,2652,3355,3356,
            3358,3359,3360,3361,3362,1259,1231,2452,3386,3387,3084,3391,3390,3077,3375,
            3378,3384,3383,3381,3382,2948,3554,2714,2021,2026,2469,2025,2470,2024,2471,
            2022,2473,2921,2023,2472,2597,2277,2252,2544,3389,3388,3385,3357,2417,3887,3888
        ];

        $query = DB::table('producto as A')
            ->select(
                'A.id',
                'A.nombre',
                'A.descripcion',
                'A.isv',
                'A.precio_base',
                'A.ultimo_costo_compra',
                'A.costo_promedio',
                'A.codigo_barra',
                'A.codigo_estatal',
                'A.unidadad_compra',
                DB::raw('um.nombre as unidad_medida'),
                'A.users_id',
                'A.sub_categoria_id'
            )
            ->join('sub_categoria as B', 'A.sub_categoria_id', '=', 'B.id')
            ->join('categoria_producto as C', 'C.id', '=', 'B.categoria_producto_id')
            ->leftJoin('marca as M', 'M.id', '=', 'A.marca_id')
            ->leftJoin('unidad_medida as um', 'um.id', '=', 'A.unidad_medida_compra_id')
            ->whereNotIn('A.id', $excluidos);

        $q = $this->filtros['q'] ?? '';
        if ($q !== '') {
            $words = array_values(array_filter(array_map('trim', explode(' ', $q))));
            foreach ($words as $word) {
                $query->where(function ($wq) use ($word) {
                    $wq->where('A.nombre', 'LIKE', "%{$word}%")
                       ->orWhere('A.codigo_barra', 'LIKE', "%{$word}%")
                       ->orWhere('A.codigo_estatal', 'LIKE', "%{$word}%");
                    if (is_numeric($word) && ctype_digit($word)) {
                        $wq->orWhere('A.id', (int) $word);
                    }
                });
            }
        }

        $desc = $this->filtros['descripcion'] ?? '';
        if ($desc !== '') {
            $query->where('A.descripcion', 'LIKE', "%{$desc}%");
        }

        $isv = $this->filtros['isv'] ?? '';
        if ($isv === '0') {
            $query->where('A.isv', 0);
        } elseif ($isv === 'con') {
            $query->where('A.isv', '>', 0);
        }

        $catId = (int) ($this->filtros['categoria_id'] ?? 0);
        if ($catId) {
            $query->where('C.id', $catId);
        }

        $marcaId = (int) ($this->filtros['marca_id'] ?? 0);
        if ($marcaId) {
            $query->where('A.marca_id', $marcaId);
        }

        return $query->get();
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
            'Unidad de Medida',
            'ID Usuario',
            'Sub Categoría',
        ];
    }
}

