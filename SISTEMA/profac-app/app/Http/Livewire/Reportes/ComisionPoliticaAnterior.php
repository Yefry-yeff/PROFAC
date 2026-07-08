<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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
            ->leftJoin('comision_producto_no_miselaneo as cpnm', function ($join) {
                $join->on('cpnm.producto_id', '=', 'p.id')
                    ->where('cpnm.estado_id', '=', 1);
            })
            ->where('p.estado_producto_id', 1)
            ->whereNull('cpnm.producto_id')
            ->selectRaw('p.id as producto_id,
                         p.nombre as producto,
                         COALESCE(m.nombre, "SIN MARCA") as marca,
                         cp.descripcion as categoria,
                         sc.descripcion as sub_categoria,
                         0 as es_no_miselaneo,
                         NULL as updated_at');

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
                    ->where('estado_producto_id', 1)
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

        foreach ($rows as $row) {
            $subtotal = (float) $row->subtotal_linea;
            $esNoMiselaneo = (int) $row->es_no_miselaneo === 1;
            $tipoPagoNorm = Str::upper(Str::ascii((string) $row->tipo_pago));

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
                'producto_id' => (int) $row->producto_id,
                'producto' => (string) $row->producto,
                'tipo_pago' => (string) $row->tipo_pago,
                'cantidad' => (float) $row->cantidad,
                'subtotal_linea' => round($subtotal, 2),
                'clasificacion' => $clasificacion,
                'porcentaje_aplicado' => round($porcentajeAplicado * 100, 4),
                'comision_no_miselaneo' => $comisionNoMiselaneo,
                'comision_miselanea' => $comisionMiselanea,
                'comision_total_linea' => $comisionTotalLinea,
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

        return response()->json([
            'message' => 'Cálculo de comisión generado correctamente.',
            'detalle' => $detalle,
            'resumen' => $resumen,
            'totales' => $totales,
        ]);
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
}
