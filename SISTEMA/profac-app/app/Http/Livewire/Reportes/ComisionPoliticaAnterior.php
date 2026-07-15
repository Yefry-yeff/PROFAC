<?php

namespace App\Http\Livewire\Reportes;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComisionPoliticaAnteriorExport;

class ComisionPoliticaAnterior extends Component
{
    private const CAPACIDAD_ROL_ALIASES = [
        'FACTURADOR' => ['FACTURADOR', 'FACTURADORA'],
        'VENDEDOR' => ['VENDEDOR', 'VENTAS'],
        'ASESOR' => ['ASESOR', 'ASESORA'],
        'TELEASESOR' => ['TELEASESOR', 'TELEASESOR', 'TELE ASESOR', 'TELEASESOR COMERCIAL'],
        'GESTORENTREGA' => ['GESTORENTREGA', 'GESTOR ENTREGA', 'GESTOR DE ENTREGA', 'GESTOR DE ENTREGAS'],
    ];

    public function render()
    {
        $view = view('livewire.reportes.comision-politica-anterior');

        if (request()->boolean('embed')) {
            return $view->layout('layouts.embed');
        }

        return $view;
    }

    private function normalizarClaveTexto(?string $valor): string
    {
        return Str::of((string) $valor)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();
    }

    private function parseFechaSegura(?string $valor): ?Carbon
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }

        try {
            return Carbon::parse($valor);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function construirContextoFacturasPoliticaAnterior(array $filasInput): array
    {
        $contexto = [];

        foreach ($filasInput as $fila) {
            if (!is_array($fila)) {
                continue;
            }

            $facturaId = (int) ($fila['factura_id'] ?? 0);
            if ($facturaId <= 0) {
                continue;
            }

            $capacidad = (string) ($fila['capacidad'] ?? '');
            if (!$this->esCapacidadVendedorPoliticaAnterior($capacidad)) {
                continue;
            }

            $usuarioId = (int) ($fila['usuario_id'] ?? 0);

            if (!isset($contexto[$facturaId])) {
                $contexto[$facturaId] = [
                    'factura_id' => $facturaId,
                    'factura' => (string) ($fila['factura'] ?? ''),
                    'cliente' => (string) ($fila['cliente'] ?? ''),
                    'capacidad' => $capacidad,
                    'usuario_id' => $usuarioId,
                    'usuario' => (string) ($fila['usuario'] ?? ''),
                    'fecha_pago' => (string) ($fila['fecha_pago'] ?? ''),
                    'fecha_creacion_factura' => (string) ($fila['fecha_creacion_factura'] ?? ''),
                ];
                continue;
            }

            foreach (['factura', 'cliente', 'capacidad', 'usuario', 'fecha_pago', 'fecha_creacion_factura'] as $campo) {
                if (empty($contexto[$facturaId][$campo]) && !empty($fila[$campo])) {
                    $contexto[$facturaId][$campo] = (string) $fila[$campo];
                }
            }

            if (empty($contexto[$facturaId]['usuario_id']) && $usuarioId > 0) {
                $contexto[$facturaId]['usuario_id'] = $usuarioId;
            }
        }

        return $contexto;
    }

    private function esCapacidadVendedorPoliticaAnterior(?string $capacidad): bool
    {
        $capacidadNorm = $this->normalizarClaveTexto($capacidad);

        if ($capacidadNorm === '') {
            return false;
        }

        return in_array($capacidadNorm, ['asesor', 'vendedor', 'ventas'], true);
    }

    private function obtenerFechasCierrePagoPorFacturas(array $facturaIds): array
    {
        if (empty($facturaIds)) {
            return [];
        }

        $rows = DB::table('aplicacion_pagos as ap')
            ->leftJoin('abonos_creditos as ac', function ($join) {
                $join->on('ac.aplicacion_pagos_id', '=', 'ap.id')
                    ->where('ac.estado_abono', '=', 1);
            })
            ->whereIn('ap.factura_id', $facturaIds)
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', 2)
            ->groupBy('ap.factura_id')
            ->selectRaw('ap.factura_id, COALESCE(MAX(DATE(ac.fecha_pago)), MAX(DATE(ap.fecha_cierre_factura))) as fecha_pago_cierre')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $facturaId = (int) ($row->factura_id ?? 0);
            $fecha = trim((string) ($row->fecha_pago_cierre ?? ''));
            if ($facturaId > 0 && $fecha !== '') {
                $map[$facturaId] = $fecha;
            }
        }

        return $map;
    }

    private function resolverRolIdPorCapacidad(?string $capacidad): ?int
    {
        $capacidadNorm = $this->normalizarClaveTexto($capacidad);
        if ($capacidadNorm === '') {
            return null;
        }

        $roles = DB::table('rol')->select('id', 'nombre')->get();

        foreach ($roles as $rol) {
            $rolNorm = $this->normalizarClaveTexto($rol->nombre ?? '');
            if ($rolNorm !== '' && $rolNorm === $capacidadNorm) {
                return (int) $rol->id;
            }
        }

        foreach (self::CAPACIDAD_ROL_ALIASES as $aliasKey => $aliases) {
            if (!in_array($capacidadNorm, array_map(fn ($item) => $this->normalizarClaveTexto($item), $aliases), true)) {
                continue;
            }

            foreach ($roles as $rol) {
                $rolNorm = $this->normalizarClaveTexto($rol->nombre ?? '');
                if ($rolNorm === $aliasKey) {
                    return (int) $rol->id;
                }
            }
        }

        return null;
    }

    private function obtenerDiasGraciaPoliticaAnterior(array $rolIds): array
    {
        $rolIds = array_values(array_unique(array_filter(array_map('intval', $rolIds))));
        if (empty($rolIds)) {
            return [];
        }

        $rows = DB::table('dias_gracia_comision')
            ->whereIn('rol_id', $rolIds)
            ->whereIn('tipo_factura', ['contado', 'credito'])
            ->where('dias_gracia', '>', 0)
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $rolId = (int) ($row->rol_id ?? 0);
            $tipo = (string) ($row->tipo_factura ?? '');
            if ($rolId <= 0 || !in_array($tipo, ['contado', 'credito'], true)) {
                continue;
            }

            $map[$rolId][$tipo] = $row;
        }

        return $map;
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
            ->leftJoin('comision_producto_no_miselaneo as cpnm', function ($join) {
                $join->on('cpnm.producto_id', '=', 'p.id')
                    ->where('cpnm.estado_id', '=', 1);
            })
            ->whereNull('cpnm.producto_id')
            ->selectRaw('p.id as producto_id,
                         p.nombre as producto,
                         COALESCE(m.nombre, "SIN MARCA") as marca,
                         cp.descripcion as categoria,
                         sc.descripcion as sub_categoria,
                         CASE WHEN p.estado_producto_id = 1 THEN "ACTIVO" ELSE "INACTIVO" END as estado_producto,
                         0 as es_no_miselaneo,
                         NULL as updated_at');

        // Por defecto se listan activos. Si se busca por ID/nombre/codigo, permitir incluir inactivos.
        if ($search === '') {
            $query->where('p.estado_producto_id', 1);
        }

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
                'estado_producto' => (string) ($r->estado_producto ?? ''),
                'es_no_miselaneo' => (int) $r->es_no_miselaneo,
                'updated_at' => (string) ($r->updated_at ?? ''),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function listarNoMiselaneosRegistrados(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $estado = trim((string) $request->input('estado', 'todos'));

        $query = DB::table('comision_producto_no_miselaneo as cpnm')
            ->join('producto as p', 'p.id', '=', 'cpnm.producto_id')
            ->join('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
            ->join('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->where('p.estado_producto_id', 1)
            ->selectRaw('cpnm.id,
                         cpnm.producto_id,
                         p.nombre as producto,
                         COALESCE(m.nombre, "SIN MARCA") as marca,
                         cp.descripcion as categoria,
                         sc.descripcion as sub_categoria,
                         cpnm.estado_id,
                         cpnm.updated_at');

        if ($estado === 'activos') {
            $query->where('cpnm.estado_id', 1);
        } elseif ($estado === 'inactivos') {
            $query->where('cpnm.estado_id', 0);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.nombre', 'like', "%{$search}%")
                    ->orWhere('p.id', 'like', "%{$search}%")
                    ->orWhere('p.codigo_barra', 'like', "%{$search}%");
            });
        }

        $rows = $query
            ->orderByDesc('cpnm.updated_at')
            ->limit(1200)
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'id' => (int) $r->id,
                'producto_id' => (int) $r->producto_id,
                'producto' => (string) $r->producto,
                'marca' => (string) $r->marca,
                'categoria' => (string) $r->categoria,
                'sub_categoria' => (string) $r->sub_categoria,
                'estado_id' => (int) $r->estado_id,
                'estado' => (int) $r->estado_id === 1 ? 'ACTIVO' : 'INACTIVO',
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
        if (!DB::getSchemaBuilder()->hasTable('comision_producto_no_miselaneo')) {
            return response()->json([
                'message' => 'No existe la tabla de no miseláneos. Ejecute las migraciones pendientes.',
            ], 500);
        }

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

                if ($productoId <= 0) {
                    continue;
                }

                $producto = DB::table('producto')
                    ->where('id', $productoId)
                    ->first();

                if (!$producto) {
                    continue;
                }

                $existe = DB::table('comision_producto_no_miselaneo')
                    ->where('producto_id', $productoId)
                    ->first();

                if ($existe) {
                    DB::table('comision_producto_no_miselaneo')
                        ->where('producto_id', $productoId)
                        ->update([
                            'estado_id' => 1,
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('comision_producto_no_miselaneo')
                        ->insert([
                            'producto_id' => $productoId,
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

    public function actualizarEstadoNoMiselaneo(Request $request)
    {
        if (!DB::getSchemaBuilder()->hasTable('comision_producto_no_miselaneo')) {
            return response()->json([
                'message' => 'No existe la tabla de no miseláneos. Ejecute las migraciones pendientes.',
            ], 500);
        }

        $productoId = (int) $request->input('producto_id', 0);
        $accion = trim((string) $request->input('accion', ''));

        if ($productoId <= 0) {
            return response()->json(['message' => 'Producto inválido.'], 422);
        }

        if (!in_array($accion, ['activar', 'quitar'], true)) {
            return response()->json(['message' => 'Acción inválida.'], 422);
        }

        $producto = DB::table('producto')
            ->where('id', $productoId)
            ->where('estado_producto_id', 1)
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'El producto no existe o está inactivo.'], 422);
        }

        $userId = Auth::id();
        $estado = $accion === 'activar' ? 1 : 0;

        $existe = DB::table('comision_producto_no_miselaneo')
            ->where('producto_id', $productoId)
            ->first();

        if ($existe) {
            DB::table('comision_producto_no_miselaneo')
                ->where('producto_id', $productoId)
                ->update([
                    'estado_id' => $estado,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
        } else {
            if ($estado === 0) {
                return response()->json(['message' => 'No existe registro para quitar.'], 422);
            }

            DB::table('comision_producto_no_miselaneo')
                ->insert([
                    'producto_id' => $productoId,
                    'estado_id' => 1,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'message' => $estado === 1
                ? 'Producto marcado como NO MISELANEO.'
                : 'Producto quitado de NO MISELANEO.',
        ]);
    }

    public function importarNoMiselaneosDesdeExcel(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel.',
            'archivo_excel.file' => 'El archivo enviado no es válido.',
            'archivo_excel.mimes' => 'El archivo debe ser XLSX, XLS o CSV.',
            'archivo_excel.max' => 'El archivo no puede superar los 20 MB.',
        ]);

        if (!DB::getSchemaBuilder()->hasTable('comision_producto_no_miselaneo')) {
            return response()->json([
                'message' => 'No existe la tabla de no miseláneos. Ejecute las migraciones pendientes.',
            ], 500);
        }

        $file = $request->file('archivo_excel');
        $sheets = Excel::toArray([], $file);
        $sheet = $sheets[0] ?? [];

        if (empty($sheet)) {
            return response()->json(['message' => 'El archivo no contiene filas para procesar.'], 422);
        }

        [$productoIds, $filasInvalidas] = $this->extraerIdsProductoDesdeHoja($sheet);

        if (empty($productoIds)) {
            return response()->json([
                'message' => 'No se encontraron IDs de producto válidos en el archivo.',
                'filas_invalidas' => $filasInvalidas,
            ], 422);
        }

        $userId = Auth::id();
        $productosActivos = DB::table('producto')
            ->whereIn('id', $productoIds)
            ->where('estado_producto_id', 1)
            ->select('id', 'nombre')
            ->get();

        $activos = $productosActivos
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $nombresActivos = $productosActivos
            ->mapWithKeys(fn ($row) => [(int) $row->id => (string) $row->nombre])
            ->all();

        $activosSet = array_flip($activos);
        $noActivos = array_values(array_filter($productoIds, fn ($id) => !isset($activosSet[$id])));

        if (empty($activos)) {
            return response()->json([
                'message' => 'Ninguno de los productos del archivo está activo o existe.',
                'resumen' => [
                    'total_archivo' => count($productoIds),
                    'activos_validos' => 0,
                    'insertados' => 0,
                    'actualizados_a_no_miselaneo' => 0,
                    'omitidos_ya_no_miselaneos' => 0,
                    'omitidos_no_activos' => count($noActivos),
                    'filas_invalidas' => count($filasInvalidas),
                ],
                'producto_ids_no_activos' => $noActivos,
                'filas_invalidas' => $filasInvalidas,
            ], 422);
        }

        $existentes = DB::table('comision_producto_no_miselaneo')
            ->whereIn('producto_id', $activos)
            ->select('producto_id', 'estado_id', 'updated_at')
            ->get()
            ->keyBy('producto_id');

        $insertados = 0;
        $actualizados = 0;
        $omitidosYaNoMiselaneos = [];

        DB::beginTransaction();
        try {
            foreach ($activos as $productoId) {
                $registro = $existentes->get($productoId);

                if ($registro && (int) $registro->estado_id === 1) {
                    $omitidosYaNoMiselaneos[] = [
                        'producto_id' => (int) $productoId,
                        'producto' => (string) ($nombresActivos[$productoId] ?? ''),
                        'updated_at' => (string) ($registro->updated_at ?? ''),
                    ];
                    continue;
                }

                if ($registro) {
                    DB::table('comision_producto_no_miselaneo')
                        ->where('producto_id', $productoId)
                        ->update([
                            'estado_id' => 1,
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ]);
                    $actualizados++;
                    continue;
                }

                DB::table('comision_producto_no_miselaneo')
                    ->insert([
                        'producto_id' => $productoId,
                        'estado_id' => 1,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                $insertados++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'No se pudo procesar la carga masiva.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $mensaje = 'Carga procesada en tabla exclusiva de NO MISELANEO. '
            . 'Insertados: ' . $insertados
            . ', reactivados: ' . $actualizados
            . ', omitidos (ya estaban activos): ' . count($omitidosYaNoMiselaneos)
            . ', omitidos por inactivos/no existentes: ' . count($noActivos)
            . ', filas inválidas: ' . count($filasInvalidas) . '.';

        return response()->json([
            'message' => $mensaje,
            'resumen' => [
                'total_archivo' => count($productoIds),
                'activos_validos' => count($activos),
                'insertados' => $insertados,
                'actualizados_a_no_miselaneo' => $actualizados,
                'omitidos_ya_no_miselaneos' => count($omitidosYaNoMiselaneos),
                'omitidos_no_activos' => count($noActivos),
                'filas_invalidas' => count($filasInvalidas),
            ],
            'producto_ids_omitidos_ya_existian' => array_map(fn ($row) => (int) $row['producto_id'], $omitidosYaNoMiselaneos),
            'productos_omitidos_ya_existian' => $omitidosYaNoMiselaneos,
            'producto_ids_no_activos' => $noActivos,
            'filas_invalidas' => $filasInvalidas,
        ]);
    }

    public function calcularComisionesFacturas(Request $request)
    {
        if (!DB::getSchemaBuilder()->hasTable('comision_producto_no_miselaneo')) {
            return response()->json([
                'message' => 'No existe la tabla de no miseláneos. Ejecute las migraciones pendientes.',
            ], 500);
        }

        $facturaIds = collect($request->input('factura_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($facturaIds)) {
            return response()->json([
                'message' => 'Debe enviar al menos una factura válida para calcular comisiones.',
            ], 422);
        }

        $periodosInput = $request->input('periodos_por_factura', []);
        if (!is_array($periodosInput)) {
            $periodosInput = [];
        }

        $filasInput = $request->input('filas', []);
        if (!is_array($filasInput)) {
            $filasInput = [];
        }

        try {
            $contextoFacturas = $this->construirContextoFacturasPoliticaAnterior($filasInput);

            if (!empty($contextoFacturas)) {
                $facturaIdsConContexto = array_map('intval', array_keys($contextoFacturas));
                $facturaIds = array_values(array_intersect($facturaIds, $facturaIdsConContexto));
            }

            if (empty($facturaIds)) {
                return response()->json([
                    'message' => 'No hay facturas elegibles para política anterior con capacidad vendedor/asesor.',
                ], 422);
            }

            $resultado = $this->construirResultadoComisionFacturas($facturaIds, $contextoFacturas);
            $filtro = $this->filtrarFacturasGestionables(
                $resultado['detalle'],
                $periodosInput,
                array_values($resultado['factura_ids_omitidas_gracia'] ?? [])
            );
            $resultado = $this->filtrarResultadoPorFacturaIds($resultado, $filtro['elegibles_ids']);

            $bloqueadas = count($filtro['bloqueadas_ids']);
            $omitidasGracia = count($resultado['facturas_omitidas_gracia'] ?? []);
            $sinParametrizacion = count($resultado['advertencias_gracia'] ?? []);

            $msg = 'Cálculo de comisión generado correctamente.';
            $partes = [];
            if ($omitidasGracia > 0) {
                $partes[] = 'Se omitieron ' . $omitidasGracia . ' factura(s) por fuera del tiempo de gracia.';
            }
            if ($bloqueadas > 0) {
                $partes[] = 'Además, se bloquearon ' . $bloqueadas . ' factura(s) por ya registradas o por período conciliado.';
            }
            if ($sinParametrizacion > 0) {
                $partes[] = 'Hay ' . $sinParametrizacion . ' factura(s) sin parametrización de días de gracia; se calcularon sin bloquearlas.';
            }
            if (!empty($partes)) {
                $msg = 'Cálculo generado. ' . implode(' ', $partes);
            }

            return response()->json([
                'message' => $msg,
                'detalle' => $resultado['detalle'],
                'resumen' => $resultado['resumen'],
                'totales' => $resultado['totales'],
                'factura_ids_elegibles' => array_values($filtro['elegibles_ids']),
                'factura_ids_bloqueadas' => array_values($filtro['bloqueadas_ids']),
                'bloqueadas_ya_agregadas' => array_values($filtro['bloqueadas_ya_agregadas']),
                'bloqueadas_periodo_conciliado' => array_values($filtro['bloqueadas_periodo_conciliado']),
                'factura_ids_omitidas_gracia' => array_values($resultado['factura_ids_omitidas_gracia'] ?? []),
                'facturas_omitidas_gracia' => array_values($resultado['facturas_omitidas_gracia'] ?? []),
                'advertencias_gracia' => array_values($resultado['advertencias_gracia'] ?? []),
                'puede_agregar' => !empty($filtro['elegibles_ids']),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'No se pudo calcular la comisión: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function agregarComisionPoliticaAnteriorAConciliacion(Request $request)
    {
        if (!DB::getSchemaBuilder()->hasTable('comision_politica_anterior_factura')) {
            return response()->json([
                'message' => 'No existe la tabla de control de facturas de política anterior. Ejecute las migraciones pendientes.',
            ], 500);
        }

        $facturaIds = collect($request->input('factura_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($facturaIds)) {
            return response()->json([
                'message' => 'No hay facturas válidas para agregar a conciliación.',
            ], 422);
        }

        $periodosInput = $request->input('periodos_por_factura', []);
        if (!is_array($periodosInput)) {
            $periodosInput = [];
        }

        $resultado = $this->construirResultadoComisionFacturas($facturaIds);
        $filtro = $this->filtrarFacturasGestionables($resultado['detalle'], $periodosInput);
        $resultado = $this->filtrarResultadoPorFacturaIds($resultado, $filtro['elegibles_ids']);
        $detalle = $resultado['detalle'];

        if (empty($filtro['elegibles_ids'])) {
            return response()->json([
                'message' => 'No hay facturas elegibles para agregar. Todas ya fueron registradas o pertenecen a períodos conciliados.',
            ], 422);
        }

        if (empty($detalle)) {
            return response()->json([
                'message' => 'No hay líneas de comisión para agregar a conciliación.',
            ], 422);
        }

        $totalesPorPeriodo = [];
        $facturasPorPeriodo = [];

        foreach ($detalle as $linea) {
            $facturaId = (int) ($linea['factura_id'] ?? 0);
            if ($facturaId <= 0) {
                continue;
            }

            $periodoInput = $periodosInput[(string) $facturaId] ?? null;
            $periodo = $this->normalizarPeriodo($periodoInput);
            if (!$periodo) {
                $periodo = $this->normalizarPeriodo($linea['fecha_factura'] ?? null);
            }
            if (!$periodo) {
                continue;
            }

            if (!isset($totalesPorPeriodo[$periodo])) {
                $totalesPorPeriodo[$periodo] = 0.0;
                $facturasPorPeriodo[$periodo] = [];
            }

            if (!isset($facturasPorPeriodo[$periodo][$facturaId])) {
                $facturasPorPeriodo[$periodo][$facturaId] = 0.0;
            }

            $montoLinea = (float) ($linea['comision_total_linea'] ?? 0);
            $facturasPorPeriodo[$periodo][$facturaId] += $montoLinea;
            $totalesPorPeriodo[$periodo] += $montoLinea;
        }

        if (empty($totalesPorPeriodo)) {
            return response()->json([
                'message' => 'No se pudo determinar el período para las facturas seleccionadas.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $periodosActualizados = [];

            foreach ($totalesPorPeriodo as $periodo => $totalPoliticaNuevas) {
                $totalPoliticaNuevas = round((float) $totalPoliticaNuevas, 2);
                $cantFacturasPoliticaNuevas = count($facturasPorPeriodo[$periodo] ?? []);
                if ($cantFacturasPoliticaNuevas <= 0 || $totalPoliticaNuevas <= 0) {
                    continue;
                }

                $registro = DB::table('comision_periodo')->where('periodo', $periodo)->lockForUpdate()->first();
                if ($registro && (int) $registro->estado === 1) {
                    throw new \RuntimeException('El período ' . $periodo . ' ya está conciliado y no permite agregar nuevos montos.');
                }

                $aggPolitica = DB::table('comision_politica_anterior_factura')
                    ->where('periodo', $periodo)
                    ->lockForUpdate()
                    ->selectRaw('COALESCE(SUM(monto_comision),0) as total, COUNT(DISTINCT factura_id) as facturas')
                    ->first();

                $totalPoliticaExistente = round((float) ($aggPolitica->total ?? 0), 2);
                $cantFacturasPoliticaExistentes = (int) ($aggPolitica->facturas ?? 0);

                $totalPolitica = round($totalPoliticaExistente + $totalPoliticaNuevas, 2);
                $cantFacturasPolitica = $cantFacturasPoliticaExistentes + $cantFacturasPoliticaNuevas;

                $totalEscala = $registro
                    ? (float) ($registro->total_comision_escala ?? $registro->total_comision ?? 0)
                    : $this->calcularTotalEscalaPeriodoAbierto($periodo);

                $totalGlobal = round($totalEscala + $totalPolitica, 2);
                $cantidadFacturas = max((int) ($registro->cantidad_facturas ?? 0), $cantFacturasPolitica);

                $periodoId = null;
                if ($registro) {
                    DB::table('comision_periodo')
                        ->where('id', $registro->id)
                        ->update([
                            'total_comision_escala' => round($totalEscala, 2),
                            'total_comision_politica_anterior' => $totalPolitica,
                            'total_comision_global' => $totalGlobal,
                            'total_comision' => $totalGlobal,
                            'cantidad_facturas' => $cantidadFacturas,
                            'updated_at' => now(),
                        ]);
                    $periodoId = (int) $registro->id;
                } else {
                    $periodoId = (int) DB::table('comision_periodo')->insertGetId([
                        'periodo' => $periodo,
                        'estado' => 0,
                        'total_comision' => $totalGlobal,
                        'total_comision_escala' => round($totalEscala, 2),
                        'total_comision_politica_anterior' => $totalPolitica,
                        'total_comision_global' => $totalGlobal,
                        'cantidad_empleados' => 0,
                        'cantidad_facturas' => $cantidadFacturas,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $rowsFacturas = [];
                foreach (($facturasPorPeriodo[$periodo] ?? []) as $facturaId => $montoFactura) {
                    $rowsFacturas[] = [
                        'factura_id' => (int) $facturaId,
                        'periodo' => $periodo,
                        'monto_comision' => round((float) $montoFactura, 2),
                        'estado' => 0,
                        'comision_periodo_id' => $periodoId,
                        'usuario_agrego_id' => Auth::id(),
                        'fecha_agregado' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($rowsFacturas)) {
                    DB::table('comision_politica_anterior_factura')->insert($rowsFacturas);
                }

                $periodosActualizados[] = [
                    'periodo' => $periodo,
                    'total_escala' => round($totalEscala, 2),
                    'total_politica_anterior' => $totalPolitica,
                    'total_global' => $totalGlobal,
                ];
            }

            if (empty($periodosActualizados)) {
                throw new \RuntimeException('No se pudo agregar ninguna factura nueva a conciliación.');
            }

            DB::commit();

            return response()->json([
                'message' => 'Comisión de política anterior agregada a conciliación correctamente. Facturas bloqueadas u omitidas no se volvieron a registrar.',
                'periodos' => $periodosActualizados,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function construirResultadoComisionFacturas(array $facturaIds, array $contextoFacturas = []): array
    {
        $fechaCierrePagoPorFactura = $this->obtenerFechasCierrePagoPorFacturas($facturaIds);
        $contextoFacturas = $this->construirContextoFacturasPoliticaAnterior($contextoFacturas);

        $rolesRequeridos = [];
        foreach ($contextoFacturas as $facturaContexto) {
            $rolId = $this->resolverRolIdPorCapacidad($facturaContexto['capacidad'] ?? null);
            if ($rolId) {
                $rolesRequeridos[$rolId] = true;
            }
        }

        $diasGraciaPorRol = $this->obtenerDiasGraciaPoliticaAnterior(array_keys($rolesRequeridos));

        $rows = DB::table('factura as f')
            ->join('venta_has_producto as vhp', 'vhp.factura_id', '=', 'f.id')
            ->join('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->join('tipo_pago_venta as tpv', 'tpv.id', '=', 'f.tipo_pago_id')
            ->leftJoin('comision_producto_no_miselaneo as cpnm', function ($join) {
                $join->on('cpnm.producto_id', '=', 'p.id')
                    ->where('cpnm.estado_id', '=', 1);
            })
            ->whereIn('f.id', $facturaIds)
            ->where('f.estado_venta_id', 1)
            ->selectRaw('f.id as factura_id,
                         RIGHT(f.cai, 5) as factura,
                         DATE_FORMAT(f.created_at, "%Y-%m-%d %H:%i:%s") as fecha_factura,
                         DATE_FORMAT(f.fecha_emision, "%Y-%m-%d") as fecha_emision,
                         DATE_FORMAT(f.fecha_vencimiento, "%Y-%m-%d") as fecha_vencimiento,
                         f.vendedor as vendedor_id,
                         f.tipo_pago_id,
                         p.id as producto_id,
                         p.nombre as producto,
                         UPPER(tpv.descripcion) as tipo_pago,
                         COALESCE(vhp.numero_unidades_resta_inventario, 0) as cantidad,
                         COALESCE(vhp.sub_total_s, 0) as subtotal_linea,
                         CASE WHEN cpnm.producto_id IS NOT NULL THEN 1 ELSE 0 END as es_no_miselaneo')
            ->orderByDesc('f.created_at')
            ->orderBy('f.id')
            ->orderBy('p.nombre')
            ->get();

        $detalle = [];
        $resumenByFactura = [];
        $totales = [
            'total_lineas' => 0,
            'total_subtotal' => 0.0,
            'total_comision_no_miselaneo' => 0.0,
            'total_comision_miselanea' => 0.0,
            'total_comision' => 0.0,
        ];

        $facturasOmitidasGracia = [];
        $advertenciasGracia = [];
        $facturasEvaluadasGracia = [];

        foreach ($rows as $row) {
            $subtotal = (float) $row->subtotal_linea;
            $esNoMiselaneo = (int) $row->es_no_miselaneo === 1;
            $tipoPagoNorm = Str::upper(Str::ascii((string) $row->tipo_pago));
            $facturaId = (int) $row->factura_id;
            $tipoKey = ((int) $row->tipo_pago_id === 1) ? 'contado' : 'credito';
            $contexto = $contextoFacturas[$facturaId] ?? [];
            $capacidad = (string) ($contexto['capacidad'] ?? '');
            $usuarioIdContexto = (int) ($contexto['usuario_id'] ?? 0);
            $rolId = $this->resolverRolIdPorCapacidad($capacidad);
            $fechaPagoCierre = (string) ($contexto['fecha_pago'] ?? ($fechaCierrePagoPorFactura[$facturaId] ?? ''));
            $fechaVencimiento = (string) ($row->fecha_vencimiento ?? $row->fecha_emision ?? '');

            if (!empty($contexto)) {
                if (!$this->esCapacidadVendedorPoliticaAnterior($capacidad)) {
                    continue;
                }

                if ($usuarioIdContexto > 0 && (int) $row->vendedor_id > 0 && $usuarioIdContexto !== (int) $row->vendedor_id) {
                    continue;
                }
            }

            $motivoGracia = null;
            $aplicaGracia = false;
            $motivoFueraPolitica = null;
            $aplicaFueraPolitica = false;

            // — Regla "Fuera de política": pago después de vencimiento + 30 días —
            if ($fechaVencimiento !== '' && $fechaPagoCierre !== '') {
                $fechaPagoFP = $this->parseFechaSegura($fechaPagoCierre)?->startOfDay();
                $fechaVencFP = $this->parseFechaSegura($fechaVencimiento)?->startOfDay();
                if ($fechaPagoFP && $fechaVencFP) {
                    $limitesFP = $fechaVencFP->copy()->addDays(30);
                    if ($fechaPagoFP->gt($limitesFP)) {
                        $aplicaFueraPolitica = true;
                        $motivoFueraPolitica = 'Fuera de política';
                    }
                }
            }

            if ($rolId) {
                $configGracia = $diasGraciaPorRol[$rolId][$tipoKey] ?? null;
                if ($configGracia) {
                    $diasGracia = (int) $configGracia->dias_gracia;
                    $fechaBase = $fechaVencimiento !== '' ? $fechaVencimiento : (string) ($row->fecha_factura ?? '');

                    if ($fechaBase !== '' && $fechaPagoCierre !== '') {
                        $fechaPagoCarbon = $this->parseFechaSegura($fechaPagoCierre)?->startOfDay();
                        $fechaBaseCarbon = $this->parseFechaSegura($fechaBase)?->startOfDay();

                        if (!$fechaPagoCarbon || !$fechaBaseCarbon) {
                            $claveAdvertencia = $facturaId . '|' . $tipoKey . '|fechas';
                            if (!isset($facturasEvaluadasGracia[$claveAdvertencia])) {
                                $advertenciasGracia[] = [
                                    'factura_id' => $facturaId,
                                    'factura' => (string) $row->factura,
                                    'capacidad' => $capacidad !== '' ? $capacidad : '—',
                                    'tipo_pago' => $tipoKey,
                                    'mensaje' => 'No se pudo interpretar la fecha de pago/cierre o la fecha base de gracia; la comisión se calculó sin bloquearse.',
                                ];
                                $facturasEvaluadasGracia[$claveAdvertencia] = true;
                            }

                            $configGracia = null;
                        } else {
                            $fechaLimite = $fechaBaseCarbon->copy()->addDays($diasGracia);
                        if ($fechaPagoCarbon->gt($fechaLimite)) {
                            $motivoGracia = 'Sin comisión por fuera de tiempo de gracia';
                            $aplicaGracia = true;

                            if (!isset($facturasOmitidasGracia[$facturaId])) {
                                $facturasOmitidasGracia[$facturaId] = [
                                    'factura_id' => $facturaId,
                                    'factura' => (string) $row->factura,
                                    'capacidad' => $capacidad !== '' ? $capacidad : '—',
                                    'usuario' => (string) ($contexto['usuario'] ?? '—'),
                                    'tipo_pago' => $tipoKey,
                                    'fecha_pago_cierre' => $fechaPagoCarbon->toDateString(),
                                    'fecha_vencimiento' => $fechaBaseCarbon->toDateString(),
                                    'dias_gracia' => $diasGracia,
                                    'dias_transcurridos' => $fechaPagoCarbon->diffInDays($fechaBaseCarbon),
                                    'motivo' => 'Sin comisión por fuera de tiempo de gracia',
                                ];
                            }
                        }
                        }
                    }
                } else {
                    $claveAdvertencia = $facturaId . '|' . $tipoKey;
                    if (!isset($facturasEvaluadasGracia[$claveAdvertencia])) {
                        $advertenciasGracia[] = [
                            'factura_id' => $facturaId,
                            'factura' => (string) $row->factura,
                            'capacidad' => $capacidad !== '' ? $capacidad : '—',
                            'tipo_pago' => $tipoKey,
                            'mensaje' => 'No se encontró parametrización de días de gracia para este rol y tipo; la comisión se calculó sin bloquearse.',
                        ];
                        $facturasEvaluadasGracia[$claveAdvertencia] = true;
                    }
                }
            } elseif ($capacidad !== '') {
                $claveAdvertencia = $facturaId . '|' . $tipoKey;
                if (!isset($facturasEvaluadasGracia[$claveAdvertencia])) {
                    $advertenciasGracia[] = [
                        'factura_id' => $facturaId,
                        'factura' => (string) $row->factura,
                        'capacidad' => $capacidad,
                        'tipo_pago' => $tipoKey,
                        'mensaje' => 'No se pudo resolver el rol para aplicar días de gracia; la comisión se calculó sin bloquearse.',
                    ];
                    $facturasEvaluadasGracia[$claveAdvertencia] = true;
                }
            }

            $porcentajeAplicado = 0.0;
            $clasificacion = 'MISELANEO';
            $comisionNoMiselaneo = 0.0;
            $comisionMiselanea = 0.0;

            if ($esNoMiselaneo) {
                $clasificacion = 'NO MISELANEO';

                if (str_contains($tipoPagoNorm, 'CONTADO')) {
                    $porcentajeAplicado = 0.0175;
                } elseif (str_contains($tipoPagoNorm, 'CREDITO')) {
                    $porcentajeAplicado = 0.015;
                }

                $comisionNoMiselaneo = round($subtotal * $porcentajeAplicado, 2);
            } else {
                $porcentajeAplicado = 0.03;
                $comisionMiselanea = round($subtotal * $porcentajeAplicado, 2);
            }

            $comisionTotalLinea = round($comisionNoMiselaneo + $comisionMiselanea, 2);

            $linea = [
                'factura_id' => (int) $row->factura_id,
                'factura' => (string) ($row->factura ?? ''),
                'fecha_factura' => (string) ($row->fecha_factura ?? ''),
                'fecha_pago_cierre' => $fechaPagoCierre !== '' ? $fechaPagoCierre : null,
                'fecha_emision' => (string) ($row->fecha_emision ?? ''),
                'fecha_vencimiento' => (string) ($row->fecha_vencimiento ?? ''),
                'producto_id' => (int) $row->producto_id,
                'producto' => (string) $row->producto,
                'tipo_pago' => (string) $row->tipo_pago,
                'cantidad' => (float) $row->cantidad,
                'subtotal_linea' => round($subtotal, 2),
                'clasificacion' => $clasificacion,
                'porcentaje_aplicado' => round($porcentajeAplicado * 100, 4),
                'comision_no_miselaneo' => ($aplicaGracia || $aplicaFueraPolitica) ? 0.0 : $comisionNoMiselaneo,
                'comision_miselanea' => ($aplicaGracia || $aplicaFueraPolitica) ? 0.0 : $comisionMiselanea,
                'comision_total_linea' => ($aplicaGracia || $aplicaFueraPolitica) ? 0.0 : $comisionTotalLinea,
                'motivo_no_comision' => $aplicaFueraPolitica ? $motivoFueraPolitica : $motivoGracia,
                'dias_gracia' => isset($configGracia) ? (int) $configGracia->dias_gracia : null,
                'dias_transcurridos' => $aplicaGracia && isset($facturasOmitidasGracia[$facturaId])
                    ? (int) $facturasOmitidasGracia[$facturaId]['dias_transcurridos']
                    : null,
            ];

            $detalle[] = $linea;

            if (!isset($resumenByFactura[$linea['factura_id']])) {
                $resumenByFactura[$linea['factura_id']] = [
                    'factura_id' => $linea['factura_id'],
                    'factura' => $linea['factura'],
                    'fecha_factura' => $linea['fecha_factura'],
                    'tipo_pago' => $linea['tipo_pago'],
                    'lineas' => 0,
                    'total_subtotal' => 0.0,
                    'total_comision_no_miselaneo' => 0.0,
                    'total_comision_miselanea' => 0.0,
                    'total_comision' => 0.0,
                ];
            }

            $resumenByFactura[$linea['factura_id']]['lineas']++;
            $resumenByFactura[$linea['factura_id']]['total_subtotal'] += $linea['subtotal_linea'];
            $resumenByFactura[$linea['factura_id']]['total_comision_no_miselaneo'] += $linea['comision_no_miselaneo'];
            $resumenByFactura[$linea['factura_id']]['total_comision_miselanea'] += $linea['comision_miselanea'];
            $resumenByFactura[$linea['factura_id']]['total_comision'] += $linea['comision_total_linea'];

            $totales['total_lineas']++;
            $totales['total_subtotal'] += $linea['subtotal_linea'];
            $totales['total_comision_no_miselaneo'] += $linea['comision_no_miselaneo'];
            $totales['total_comision_miselanea'] += $linea['comision_miselanea'];
            $totales['total_comision'] += $linea['comision_total_linea'];
        }

        $resumen = collect(array_values($resumenByFactura))
            ->map(function ($row) {
                $row['total_subtotal'] = round((float) $row['total_subtotal'], 2);
                $row['total_comision_no_miselaneo'] = round((float) $row['total_comision_no_miselaneo'], 2);
                $row['total_comision_miselanea'] = round((float) $row['total_comision_miselanea'], 2);
                $row['total_comision'] = round((float) $row['total_comision'], 2);
                return $row;
            })
            ->values()
            ->all();

        $totales['total_subtotal'] = round((float) $totales['total_subtotal'], 2);
        $totales['total_comision_no_miselaneo'] = round((float) $totales['total_comision_no_miselaneo'], 2);
        $totales['total_comision_miselanea'] = round((float) $totales['total_comision_miselanea'], 2);
        $totales['total_comision'] = round((float) $totales['total_comision'], 2);

        return [
            'detalle' => $detalle,
            'resumen' => $resumen,
            'totales' => $totales,
            'factura_ids_omitidas_gracia' => array_values(array_keys($facturasOmitidasGracia)),
            'facturas_omitidas_gracia' => array_values($facturasOmitidasGracia),
            'advertencias_gracia' => array_values($advertenciasGracia),
        ];
    }

    private function filtrarFacturasGestionables(array $detalle, array $periodosInput, array $facturasBloqueadasGracia = []): array
    {
        $facturaPeriodo = [];
        foreach ($detalle as $linea) {
            $facturaId = (int) ($linea['factura_id'] ?? 0);
            if ($facturaId <= 0 || isset($facturaPeriodo[$facturaId])) {
                continue;
            }

            $periodoInput = $periodosInput[(string) $facturaId] ?? null;
            $periodo = $this->normalizarPeriodo($periodoInput);
            if (!$periodo) {
                $periodo = $this->normalizarPeriodo($linea['fecha_factura'] ?? null);
            }

            $facturaPeriodo[$facturaId] = $periodo;
        }

        $facturaIds = array_keys($facturaPeriodo);
        if (empty($facturaIds)) {
            return [
                'elegibles_ids' => [],
                'bloqueadas_ids' => [],
                'bloqueadas_ya_agregadas' => [],
                'bloqueadas_periodo_conciliado' => [],
                'bloqueadas_gracia' => [],
            ];
        }

        $bloqueadasGraciaSet = array_fill_keys(array_map(fn($id) => (int) $id, $facturasBloqueadasGracia), true);

        $periodos = collect($facturaPeriodo)
            ->filter(fn($p) => !empty($p))
            ->unique()
            ->values()
            ->all();

        $periodosConciliados = [];
        if (!empty($periodos)) {
            $periodosConciliados = DB::table('comision_periodo')
                ->whereIn('periodo', $periodos)
                ->where('estado', 1)
                ->pluck('periodo')
                ->map(fn($p) => (string) $p)
                ->all();
        }
        $periodosConciliados = array_fill_keys($periodosConciliados, true);

        $yaRegistradas = [];
        if (!empty($periodos) && DB::getSchemaBuilder()->hasTable('comision_politica_anterior_factura')) {
            $rows = DB::table('comision_politica_anterior_factura')
                ->whereIn('factura_id', $facturaIds)
                ->whereIn('periodo', $periodos)
                ->get(['factura_id', 'periodo']);

            foreach ($rows as $r) {
                $k = ((int) $r->factura_id) . '|' . ((string) $r->periodo);
                $yaRegistradas[$k] = true;
            }
        }

        $elegibles = [];
        $bloqueadas = [];
        $bloqueadasYaAgregadas = [];
        $bloqueadasConciliado = [];
        $bloqueadasGracia = [];

        foreach ($facturaPeriodo as $facturaId => $periodo) {
            if (isset($bloqueadasGraciaSet[(int) $facturaId])) {
                $bloqueadas[] = (int) $facturaId;
                $bloqueadasGracia[] = (int) $facturaId;
                continue;
            }

            if (!$periodo) {
                $bloqueadas[] = (int) $facturaId;
                continue;
            }

            if (isset($periodosConciliados[$periodo])) {
                $bloqueadas[] = (int) $facturaId;
                $bloqueadasConciliado[] = (int) $facturaId;
                continue;
            }

            $k = ((int) $facturaId) . '|' . $periodo;
            if (isset($yaRegistradas[$k])) {
                $bloqueadas[] = (int) $facturaId;
                $bloqueadasYaAgregadas[] = (int) $facturaId;
                continue;
            }

            $elegibles[] = (int) $facturaId;
        }

        return [
            'elegibles_ids' => array_values(array_unique($elegibles)),
            'bloqueadas_ids' => array_values(array_unique($bloqueadas)),
            'bloqueadas_ya_agregadas' => array_values(array_unique($bloqueadasYaAgregadas)),
            'bloqueadas_periodo_conciliado' => array_values(array_unique($bloqueadasConciliado)),
            'bloqueadas_gracia' => array_values(array_unique($bloqueadasGracia)),
        ];
    }

    private function filtrarResultadoPorFacturaIds(array $resultado, array $facturaIdsPermitidas): array
    {
        $permitidas = array_fill_keys(array_map(fn($id) => (int) $id, $facturaIdsPermitidas), true);

        $detalle = collect($resultado['detalle'] ?? [])
            ->filter(fn($linea) => isset($permitidas[(int) ($linea['factura_id'] ?? 0)]))
            ->values()
            ->all();

        $resumen = collect($resultado['resumen'] ?? [])
            ->filter(fn($row) => isset($permitidas[(int) ($row['factura_id'] ?? 0)]))
            ->values()
            ->all();

        $totales = [
            'total_lineas' => count($detalle),
            'total_subtotal' => round((float) collect($detalle)->sum('subtotal_linea'), 2),
            'total_comision_no_miselaneo' => round((float) collect($detalle)->sum('comision_no_miselaneo'), 2),
            'total_comision_miselanea' => round((float) collect($detalle)->sum('comision_miselanea'), 2),
            'total_comision' => round((float) collect($detalle)->sum('comision_total_linea'), 2),
        ];

        return [
            'detalle' => $detalle,
            'resumen' => $resumen,
            'totales' => $totales,
        ];
    }

    private function normalizarPeriodo($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $raw = trim((string) $valor);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $raw)) {
            return substr($raw, 0, 7) . '-01';
        }

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $raw, $m)) {
            return $m[3] . '-' . $m[2] . '-01';
        }

        return null;
    }

    private function calcularTotalEscalaPeriodoAbierto(string $periodo): float
    {
        $bruto = (float) DB::table('comision_empleado')
            ->where('mes_comision', $periodo)
            ->where('estado_id', 1)
            ->where('comision_acumulada', '>', 0)
            ->sum('comision_acumulada');

        $retencion = (float) DB::table('comision_retencion_fuente')
            ->where('periodo', $periodo)
            ->where('estado', 1)
            ->sum('monto_retencion');

        return round(max(0.0, $bruto - $retencion), 2);
    }

    private function extraerIdsProductoDesdeHoja(array $sheet): array
    {
        $firstRow = $sheet[0] ?? [];
        $headerIndex = $this->detectarIndiceColumnaProducto($firstRow);
        $startAt = $headerIndex >= 0 ? 1 : 0;
        $colIndex = $headerIndex >= 0 ? $headerIndex : 0;

        $ids = [];
        $filasInvalidas = [];

        for ($i = $startAt; $i < count($sheet); $i++) {
            $row = is_array($sheet[$i]) ? $sheet[$i] : [];
            if (empty($row)) {
                continue;
            }

            $raw = $row[$colIndex] ?? null;
            if (($raw === null || $raw === '') && $headerIndex < 0) {
                foreach ($row as $value) {
                    if ($value !== null && trim((string) $value) !== '') {
                        $raw = $value;
                        break;
                    }
                }
            }

            $id = $this->normalizarProductoId($raw);
            if ($id <= 0) {
                $filasInvalidas[] = $i + 1;
                continue;
            }

            $ids[] = $id;
        }

        return [array_values(array_unique($ids)), $filasInvalidas];
    }

    private function detectarIndiceColumnaProducto(array $headerRow): int
    {
        foreach ($headerRow as $index => $cell) {
            $normalized = Str::of((string) $cell)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_')
                ->value();

            if (in_array($normalized, [
                'producto_id',
                'id_producto',
                'id',
                'cod_producto',
                'codproducto',
                'cod_prod',
                'codigo_producto',
                'codigo_de_producto',
                'codigo',
            ], true)) {
                return (int) $index;
            }
        }

        return -1;
    }

    private function normalizarProductoId($raw): int
    {
        if ($raw === null) {
            return 0;
        }

        $value = trim((string) $raw);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/\.0+$/', '', $value);
        $value = preg_replace('/[^0-9]/', '', $value);

        return $value === '' ? 0 : (int) $value;
    }

    public function exportarDetalleExcel(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $rows = $request->input('rows', []);

        if (!is_array($rows)) {
            $rows = json_decode($rows, true) ?? [];
        }

        $titulo  = $request->input('titulo', 'DETALLE COMISIÓN POLÍTICA ANTERIOR');
        $periodo = $request->input('periodo', now()->format('d/m/Y H:i'));

        $response = Excel::download(
            new ComisionPoliticaAnteriorExport($rows, $titulo, $periodo),
            'comision_politica_anterior_' . now()->format('Ymd_His') . '.xlsx'
        );

        $token = (string) $request->input('download_token', '');
        if ($token !== '') {
            setcookie('pa_excel_token', $token, time() + 300, '/', '', false, false);
        }

        return $response;
    }
}
