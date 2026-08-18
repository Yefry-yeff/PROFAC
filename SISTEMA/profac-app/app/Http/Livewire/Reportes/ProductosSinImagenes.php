<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosSinImagenesExport;

class ProductosSinImagenes extends Component
{
    public $categorias = [];
    public $marcas = [];
    public $totalSinImagenes = 0;

    public function mount()
    {
        $this->categorias = DB::table('categoria_producto')
            ->orderBy('descripcion')
            ->get(['id', 'descripcion'])
            ->map(function ($categoria) {
                return [
                    'id' => $categoria->id,
                    'descripcion' => $categoria->descripcion,
                ];
            })
            ->toArray();

        $this->marcas = DB::table('marca')
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(function ($marca) {
                return [
                    'id' => $marca->id,
                    'nombre' => $marca->nombre,
                ];
            })
            ->toArray();

        $this->totalSinImagenes = $this->contarProductosSinImagenes();
    }

    public function render()
    {
        return view('livewire.reportes.productos-sin-imagenes');
    }

    public function consulta(Request $request)
    {
        $query = $this->baseQuery($request);

        return DataTables::of($query)
            ->addColumn('estado', function ($registro) {
                return (int) $registro->estado_producto_id === 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-secondary">Inactivo</span>';
            })
            ->addColumn('acciones', function ($registro) {
                return '<a href="' . url('/producto/detalle/' . $registro->id) . '" class="btn btn-sm btn-primary" target="_blank" rel="noopener noreferrer"><i class="fa fa-eye"></i> Ver detalle</a>';
            })
            ->rawColumns(['estado', 'acciones'])
            ->make(true);
    }

    public function exportarExcel(Request $request)
    {
        try {
            $data = $this->rowsForExport($request);

            return Excel::download(
                new ProductosSinImagenesExport($data),
                'productos_sin_imagenes.xlsx'
            );
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el Excel.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }

    public function exportarPdf(Request $request)
    {
        try {
            $data = $this->rowsForExport($request);

            $pdf = Pdf::loadView('pdf.productos-sin-imagenes', [
                'data' => $data,
                'filtros' => [
                    'categoria' => $this->nombreCategoria($request->input('categoria_id')),
                    'marca' => $this->nombreMarca($request->input('marca_id')),
                ],
                'total' => count($data),
            ])->setPaper('oficio', 'landscape');

            return $pdf->download('productos_sin_imagenes.pdf');
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el PDF.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }

    private function contarProductosSinImagenes()
    {
        return $this->baseQuery()->count();
    }

    private function rowsForExport(Request $request): array
    {
        return $this->baseQuery($request)
            ->get()
            ->map(function ($registro) {
                return [
                    'codigo_referencia' => $registro->codigo_referencia,
                    'producto' => $registro->producto,
                    'categoria' => $registro->categoria,
                    'sub_categoria' => $registro->sub_categoria,
                    'marca' => $registro->marca,
                    'precio_base' => $registro->precio_base,
                    'estado' => (int) $registro->estado_producto_id === 1 ? 'Activo' : 'Inactivo',
                ];
            })
            ->toArray();
    }

    private function nombreCategoria($categoriaId): string
    {
        if (!$categoriaId) {
            return 'Todas';
        }

        return DB::table('categoria_producto')->where('id', $categoriaId)->value('descripcion') ?? 'Todas';
    }

    private function nombreMarca($marcaId): string
    {
        if (!$marcaId) {
            return 'Todas';
        }

        return DB::table('marca')->where('id', $marcaId)->value('nombre') ?? 'Todas';
    }

    private function baseQuery(Request $request = null)
    {
        $categoriaId = $request ? $request->get('categoria_id') : null;
        $marcaId = $request ? $request->get('marca_id') : null;

        return DB::table('producto as p')
            ->leftJoin('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
            ->leftJoin('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('img_producto as ip')
                    ->whereColumn('ip.producto_id', 'p.id');
            })
            ->when($categoriaId, function ($consulta) use ($categoriaId) {
                $consulta->where('cp.id', $categoriaId);
            })
            ->when($marcaId, function ($consulta) use ($marcaId) {
                $consulta->where('m.id', $marcaId);
            })
            ->selectRaw("\n                p.id,\n                COALESCE(NULLIF(p.codigo_barra, ''), NULLIF(p.codigo_estatal, ''), CONCAT('P-', p.id)) as codigo_referencia,\n                p.nombre as producto,\n                COALESCE(m.nombre, 'Sin marca') as marca,\n                COALESCE(cp.descripcion, 'Sin categoría') as categoria,\n                COALESCE(sc.descripcion, 'Sin subcategoría') as sub_categoria,\n                p.precio_base,\n                p.estado_producto_id\n            ")
            ->orderBy('p.nombre');
    }
}