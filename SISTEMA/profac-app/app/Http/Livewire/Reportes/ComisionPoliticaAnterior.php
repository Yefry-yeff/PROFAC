<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComisionPoliticaAnterior extends Component
{
    public function render()
    {
        return view('livewire.reportes.comision-politica-anterior');
    }

    public function listarProductosActivos(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $query = DB::table('producto as p')
            ->leftJoin('comision_producto_clasificacion as cpc', 'cpc.producto_id', '=', 'p.id')
            ->where('p.estado_producto_id', 1)
            ->selectRaw('p.id, p.nombre, cpc.es_miselaneo');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.nombre', 'like', "%{$search}%")
                  ->orWhere('p.id', 'like', "%{$search}%");
            });
        }

        $rows = $query
            ->orderBy('p.nombre')
            ->limit(30)
            ->get();

        $results = $rows->map(function ($r) {
            $tipo = is_null($r->es_miselaneo)
                ? 'SIN PARAMETRIZAR'
                : ((int) $r->es_miselaneo === 1 ? 'MISELANEO' : 'NO MISELANEO');

            return [
                'id' => (int) $r->id,
                'text' => $r->id . ' - ' . $r->nombre . ' [' . $tipo . ']',
            ];
        });

        return response()->json($results);
    }

    public function catalogoMarcas()
    {
        $rows = DB::table('marca')
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json($rows);
    }

    public function catalogoCategorias()
    {
        $rows = DB::table('categoria_producto')
            ->select('id', 'descripcion as nombre')
            ->orderBy('descripcion')
            ->get();

        return response()->json($rows);
    }

    public function catalogoSubCategorias(Request $request)
    {
        $categoriaId = (int) $request->input('categoria_id', 0);

        $query = DB::table('sub_categoria')
            ->select('id', 'descripcion as nombre', 'categoria_producto_id')
            ->orderBy('descripcion');

        if ($categoriaId > 0) {
            $query->where('categoria_producto_id', $categoriaId);
        }

        return response()->json($query->get());
    }

    public function listarProductosChecklist(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $marcaId = (int) $request->input('marca_id', 0);
        $categoriaId = (int) $request->input('categoria_id', 0);
        $subCategoriaId = (int) $request->input('sub_categoria_id', 0);

        $query = DB::table('producto as p')
            ->join('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
            ->join('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->leftJoin('comision_producto_clasificacion as cpc', 'cpc.producto_id', '=', 'p.id')
            ->where('p.estado_producto_id', 1)
            ->selectRaw('p.id as producto_id,
                         p.nombre as producto,
                         COALESCE(m.nombre, "SIN MARCA") as marca,
                         cp.descripcion as categoria,
                         sc.descripcion as sub_categoria,
                         COALESCE(cpc.es_miselaneo, 0) as es_miselaneo,
                         cpc.updated_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.nombre', 'like', "%{$search}%")
                  ->orWhere('p.id', 'like', "%{$search}%")
                  ->orWhere('p.codigo_barra', 'like', "%{$search}%");
            });
        }

        if ($marcaId > 0) {
            $query->where('p.marca_id', $marcaId);
        }

        if ($categoriaId > 0) {
            $query->where('cp.id', $categoriaId);
        }

        if ($subCategoriaId > 0) {
            $query->where('sc.id', $subCategoriaId);
        }

        $rows = $query
            ->orderBy('p.nombre')
            ->limit(1200)
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'producto_id' => (int) $r->producto_id,
                'producto' => (string) $r->producto,
                'marca' => (string) $r->marca,
                'categoria' => (string) $r->categoria,
                'sub_categoria' => (string) $r->sub_categoria,
                'es_miselaneo' => (int) $r->es_miselaneo,
                'updated_at' => (string) ($r->updated_at ?? ''),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function listarClasificacionProductos()
    {
        $rows = DB::table('comision_producto_clasificacion as cpc')
            ->join('producto as p', 'p.id', '=', 'cpc.producto_id')
            ->where('p.estado_producto_id', 1)
            ->selectRaw('cpc.id, cpc.producto_id, p.nombre as producto, cpc.es_miselaneo, cpc.updated_at')
            ->orderByDesc('cpc.updated_at')
            ->limit(1000)
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'id' => (int) $r->id,
                'producto_id' => (int) $r->producto_id,
                'producto' => (string) $r->producto,
                'es_miselaneo' => (int) $r->es_miselaneo,
                'tipo' => ((int) $r->es_miselaneo === 1 ? 'MISELANEO' : 'NO MISELANEO'),
                'updated_at' => (string) ($r->updated_at ?? ''),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function guardarClasificacionProducto(Request $request)
    {
        $productoId = (int) $request->input('producto_id', 0);
        $tipo = trim((string) $request->input('tipo', ''));

        if ($productoId <= 0) {
            return response()->json(['message' => 'Debe seleccionar un producto válido.'], 422);
        }

        if (!in_array($tipo, ['miselaneo', 'no_miselaneo'], true)) {
            return response()->json(['message' => 'Debe seleccionar un tipo válido.'], 422);
        }

        $producto = DB::table('producto')
            ->where('id', $productoId)
            ->where('estado_producto_id', 1)
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'El producto no existe o está inactivo.'], 422);
        }

        $esMiselaneo = $tipo === 'miselaneo' ? 1 : 0;
        $userId = Auth::id();

        $existe = DB::table('comision_producto_clasificacion')
            ->where('producto_id', $productoId)
            ->first();

        if ($existe) {
            DB::table('comision_producto_clasificacion')
                ->where('producto_id', $productoId)
                ->update([
                    'es_miselaneo' => $esMiselaneo,
                    'estado_id' => 1,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('comision_producto_clasificacion')
                ->insert([
                    'producto_id' => $productoId,
                    'es_miselaneo' => $esMiselaneo,
                    'estado_id' => 1,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'message' => 'Parametrización guardada correctamente.',
        ]);
    }

    public function guardarChecklist(Request $request)
    {
        $items = $request->input('items', []);
        if (!is_array($items) || empty($items)) {
            return response()->json(['message' => 'No hay productos para guardar.'], 422);
        }

        $userId = Auth::id();
        $procesados = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $productoId = isset($item['producto_id']) ? (int) $item['producto_id'] : 0;
                $esMiselaneo = !empty($item['es_miselaneo']) ? 1 : 0;

                if ($productoId <= 0) {
                    continue;
                }

                $producto = DB::table('producto')
                    ->where('id', $productoId)
                    ->where('estado_producto_id', 1)
                    ->first();

                if (!$producto) {
                    continue;
                }

                $existe = DB::table('comision_producto_clasificacion')
                    ->where('producto_id', $productoId)
                    ->first();

                if ($existe) {
                    DB::table('comision_producto_clasificacion')
                        ->where('producto_id', $productoId)
                        ->update([
                            'es_miselaneo' => $esMiselaneo,
                            'estado_id' => 1,
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('comision_producto_clasificacion')
                        ->insert([
                            'producto_id' => $productoId,
                            'es_miselaneo' => $esMiselaneo,
                            'estado_id' => 1,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                $procesados++;
            }

            DB::commit();

            return response()->json([
                'message' => 'Checklist guardado correctamente.',
                'procesados' => $procesados,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'No se pudo guardar el checklist.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
