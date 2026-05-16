<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use App\Models\Comisiones\Escalado\modelcomision_escala;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Comisiones\PlantillaComisionMasivaExport;
use App\Imports\Comisiones\ComisionMasivaImport;

class Configuracion extends Component
{
    public function render()
    {
        return view('livewire.comisiones.escalado.configuracion');
    }

    /* ── SELECT2 helpers ─────────────────────────────────────── */

    public function listaRolesUsuario(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $roles = DB::table('rol')
            ->select('id', 'nombre')
            ->when($q !== '', fn($qq) => $qq->where('nombre', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->limit(50)
            ->get();

        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Devuelve las categorías de precio activas ligadas a una categoría de cliente.
     * Incluye el % ya configurado para ese rol (si existe), para pre-llenar en edición.
     */
    public function categoriasPrecioPorCliente(Request $request)
    {
        $clienteCategoriaId = (int) $request->get('cliente_categoria_escala_id');
        $rolId              = (int) $request->get('rol_id', 0);
        $excludeId          = (int) $request->get('exclude_id', 0); // al editar, excluir el propio registro

        if (!$clienteCategoriaId) {
            return response()->json(['categorias' => []], 200);
        }

        $categorias = DB::table('categoria_precios as cp')
            ->where('cp.cliente_categoria_escala_id', $clienteCategoriaId)
            ->where('cp.estado_id', 1)
            ->select('cp.id', 'cp.nombre')
            ->orderBy('cp.nombre')
            ->get();

        // Si se pasó un rol, pre-cargar los % ya guardados
        $existentes = collect();
        if ($rolId) {
            $existentes = DB::table('comision_escala')
                ->where('rol_id', $rolId)
                ->where('cliente_categoria_escala_id', $clienteCategoriaId)
                ->where('estado_id', 1)
                ->when($excludeId, fn($q) => $q->where('id', '<>', $excludeId))
                ->select('categoria_precios_id', 'porcentaje_comision', 'id')
                ->get()
                ->keyBy('categoria_precios_id');
        }

        $result = $categorias->map(function ($cat) use ($existentes) {
            $ex = $existentes->get($cat->id);
            return [
                'id'                  => $cat->id,
                'nombre'              => $cat->nombre,
                'porcentaje_comision' => $ex ? $ex->porcentaje_comision : null,
                'comision_escala_id'  => $ex ? $ex->id : null,
            ];
        });

        return response()->json(['categorias' => $result], 200);
    }

    /* ── CRUD ────────────────────────────────────────────────── */

    /**
     * Guarda UN registro por cada categoría de precio que el usuario haya llenado con %.
     */
    public function guardarParametroComision(Request $request)
    {
        try {
            $rolId              = (int) $request->rol_id;
            $clienteCategoriaId = (int) $request->categoria_cliente_id;
            $nombre             = trim($request->nombre_comescala ?? '');
            $filas              = $request->input('filas', []); // [{categoria_precios_id, porcentaje}]

            if (!$rolId || !$clienteCategoriaId || empty($filas)) {
                return response()->json([
                    'icon' => 'warning', 'title' => 'Datos incompletos',
                    'text' => 'Debe seleccionar rol, categoría de cliente y al menos un porcentaje.',
                ], 422);
            }

            DB::beginTransaction();

            $insertados = 0;
            foreach ($filas as $fila) {
                $categoriaPreId = (int) ($fila['categoria_precios_id'] ?? 0);
                $porcentaje     = $fila['porcentaje'] ?? null;

                if (!$categoriaPreId || $porcentaje === null || $porcentaje === '') {
                    continue; // fila vacía, saltar
                }

                // Verificar duplicado activo
                $existe = modelcomision_escala::where('rol_id', $rolId)
                    ->where('cliente_categoria_escala_id', $clienteCategoriaId)
                    ->where('categoria_precios_id', $categoriaPreId)
                    ->where('estado_id', 1)
                    ->exists();

                if ($existe) {
                    continue; // ya existe, no duplicar
                }

                $p = new modelcomision_escala;
                $p->nombre                     = $nombre ?: 'Comisión';
                $p->descripcion                = $nombre ?: 'Comisión';
                $p->cliente_categoria_escala_id = $clienteCategoriaId;
                $p->categoria_precios_id       = $categoriaPreId;
                $p->rol_id                     = $rolId;
                $p->porcentaje_comision        = (float) $porcentaje;
                $p->estado_id                  = 1;
                $p->users_registro             = Auth::user()->id;
                $p->save();
                $insertados++;
            }

            DB::commit();

            if ($insertados === 0) {
                return response()->json([
                    'icon' => 'warning', 'title' => 'Sin cambios',
                    'text' => 'Todos los registros ya existían o no se ingresó ningún porcentaje.',
                ], 200);
            }

            return response()->json([
                'icon' => 'success', 'title' => '¡Éxito!',
                'text' => "Se guardaron {$insertados} parámetro(s) de comisión.",
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'icon' => 'error', 'title' => 'Error',
                'text' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function listarParametroComision()
    {
        try {
            $datos = DB::select("
                SELECT
                    ce.id,
                    ce.nombre,
                    ce.porcentaje_comision,
                    r.nombre                    AS rol,
                    cce.nombre_categoria        AS cliente_cat_escala,
                    cp.nombre                   AS categoria_precio,
                    u.name                      AS userRegistro,
                    ce.created_at               AS fechaRegistro,
                    ce.estado_id
                FROM comision_escala ce
                INNER JOIN rol r                    ON r.id  = ce.rol_id
                INNER JOIN cliente_categoria_escala cce ON cce.id = ce.cliente_categoria_escala_id
                LEFT  JOIN categoria_precios cp        ON cp.id  = ce.categoria_precios_id
                INNER JOIN users u                 ON u.id  = ce.users_registro
                ORDER BY ce.id DESC
            ");

            return DataTables::of($datos)
                ->addColumn('estado', function ($row) {
                    return $row->estado_id === 1
                        ? '<span class="badge bg-primary">ACTIVO</span>'
                        : '<span class="badge bg-danger">INACTIVO</span>';
                })
                ->addColumn('opciones', function ($row) {
                    if ($row->estado_id == 1) {
                        return '
                            <div class="btn-group">
                                <button data-toggle="dropdown" class="btn btn-warning btn-sm dropdown-toggle">Ver más</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" onclick="editarParametro(' . $row->id . ')">
                                        <i class="fa fa-edit text-primary"></i> Editar %</a></li>
                                    <li><a class="dropdown-item" onclick="desactivarCategoria(' . $row->id . ', \'' . $row->rol . '\')">
                                        <i class="fa fa-times text-danger"></i> Desactivar</a></li>
                                </ul>
                            </div>';
                    }
                    return '<span class="badge badge-secondary px-3 py-2"><i class="fa fa-ban mr-1"></i> Sin acciones</span>';
                })
                ->rawColumns(['opciones', 'estado'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function desactivarParametro($id)
    {
        try {
            $parametro = DB::table('comision_escala')->where('id', $id)->first();
            if (!$parametro) {
                return response()->json(['icon' => 'error', 'title' => 'No encontrado', 'text' => 'El parámetro no existe.'], 404);
            }
            if ($parametro->estado_id == 2) {
                return response()->json(['icon' => 'warning', 'title' => 'Ya desactivado', 'text' => 'Este parámetro ya está inactivo.'], 200);
            }

            DB::table('comision_escala')->where('id', $id)->update([
                'estado_id'     => 2,
                'fechadesactivo' => now(),
                'userdesactivo' => Auth::user()->id,
                'updated_at'    => now(),
            ]);

            return response()->json(['icon' => 'success', 'title' => 'Desactivado', 'text' => 'Parámetro desactivado correctamente.'], 200);

        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()], 500);
        }
    }

    public function obtenerParametro($id)
    {
        $parametro = DB::table('comision_escala')
            ->select('id', 'nombre', 'descripcion', 'porcentaje_comision',
                     'cliente_categoria_escala_id', 'categoria_precios_id', 'rol_id')
            ->where('id', $id)
            ->first();

        if (!$parametro) {
            return response()->json(['icon' => 'error', 'title' => 'No encontrado', 'text' => 'No existe.'], 404);
        }

        return response()->json($parametro, 200);
    }

    /** Editar sólo el porcentaje de un registro específico */
    public function actualizarParametro(Request $request, $id)
    {
        try {
            $parametro = DB::table('comision_escala')->where('id', $id)->first();
            if (!$parametro) {
                return response()->json(['icon' => 'error', 'title' => 'No encontrado', 'text' => 'No existe.'], 404);
            }

            DB::table('comision_escala')->where('id', $id)->update([
                'porcentaje_comision'     => (float) $request->porcentaje_comision,
                'fechaultimamodificacion' => now(),
                'usermodifico'            => Auth::user()->id,
                'updated_at'              => now(),
            ]);

            return response()->json(['icon' => 'success', 'title' => 'Actualizado', 'text' => 'Porcentaje actualizado correctamente.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()], 500);
        }
    }

    /* ── CARGA MASIVA ─────────────────────────────────────────────────────── */

    public function descargarPlantillaMasiva()
    {
        $fecha = now()->format('Ymd_His');
        return Excel::download(new PlantillaComisionMasivaExport(), "plantilla_comisiones_{$fecha}.xlsx");
    }

    public function cargarMasivaComisiones(Request $request)
    {
        if (!$request->hasFile('archivo_comision') || !$request->file('archivo_comision')->isValid()) {
            return response()->json(['icon' => 'error', 'title' => 'Archivo inválido', 'text' => 'Suba un archivo Excel (.xlsx) válido.'], 422);
        }

        $ext = strtolower($request->file('archivo_comision')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            return response()->json(['icon' => 'error', 'title' => 'Formato no permitido', 'text' => 'Solo se aceptan archivos .xlsx o .xls.'], 422);
        }

        try {
            $import = new ComisionMasivaImport(Auth::user()->id);
            Excel::import($import, $request->file('archivo_comision'));

            $texto = "Insertados: {$import->insertados} | Actualizados: {$import->actualizados} | Omitidos: {$import->omitidos}";
            if (!empty($import->errores)) {
                $texto .= " | Errores en filas: " . implode('; ', array_slice($import->errores, 0, 5));
            }

            return response()->json([
                'icon'        => 'success',
                'title'       => 'Carga completada',
                'text'        => $texto,
                'insertados'  => $import->insertados,
                'actualizados'=> $import->actualizados,
                'omitidos'    => $import->omitidos,
                'errores'     => $import->errores,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error al procesar', 'text' => $e->getMessage()], 500);
        }
    }

    /* ── CARGA SELECTIVA ──────────────────────────────────────────────────── */

    /** Roles activos para el filtro de carga selectiva */
    public function listaRolesParaFiltro()
    {
        $roles = DB::table('rol')
            ->orderBy('nombre')
            ->select('id', 'nombre')
            ->get();
        return response()->json(['roles' => $roles], 200);
    }

    /** KPIs agregados para las tarjetas de resumen */
    public function statsComision()
    {
        $activos   = DB::table('comision_escala')->where('estado_id', 1)->count();
        $inactivos = DB::table('comision_escala')->where('estado_id', 2)->count();
        $roles     = DB::table('comision_escala')->where('estado_id', 1)->distinct('rol_id')->count('rol_id');
        $catCli    = DB::table('comision_escala')->where('estado_id', 1)->distinct('cliente_categoria_escala_id')->count('cliente_categoria_escala_id');
        $prom      = DB::table('comision_escala')->where('estado_id', 1)->avg('porcentaje_comision');

        return response()->json([
            'activos'   => $activos,
            'inactivos' => $inactivos,
            'roles'     => $roles,
            'cat_cli'   => $catCli,
            'promedio'  => round((float) $prom, 2),
        ]);
    }

    /** Resumen agrupado por Rol x Categoría Cliente para el tab de resumen */
    public function resumenPorRol()
    {
        $data = DB::table('comision_escala as ce')
            ->join('rol as r', 'r.id', '=', 'ce.rol_id')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'ce.cliente_categoria_escala_id')
            ->where('ce.estado_id', 1)
            ->select(
                'r.nombre as rol',
                'cce.nombre_categoria as cat_cli',
                DB::raw('COUNT(*) as total_configs'),
                DB::raw('ROUND(MIN(ce.porcentaje_comision), 2) as pct_min'),
                DB::raw('ROUND(AVG(ce.porcentaje_comision), 2) as pct_prom'),
                DB::raw('ROUND(MAX(ce.porcentaje_comision), 2) as pct_max')
            )
            ->groupBy('ce.rol_id', 'r.nombre', 'ce.cliente_categoria_escala_id', 'cce.nombre_categoria')
            ->orderBy('r.nombre')
            ->orderBy('cce.nombre_categoria')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Lista todos los roles con su estado de cálculo de comisión (comision_rol_config)
     * e indica si el rol tiene al menos una escala activa configurada.
     */
    public function listaRolesCalculo()
    {
        $data = DB::table('rol as r')
            ->leftJoin('comision_rol_config as crc', 'crc.rol_id', '=', 'r.id')
            ->leftJoin('users as ub', 'ub.id', '=', 'crc.updated_by')
            ->select(
                'r.id',
                'r.nombre',
                DB::raw('COALESCE(crc.calcular, 1) AS calcular'),
                DB::raw('COALESCE(crc.updated_at, NULL) AS ultima_modificacion'),
                DB::raw('ub.name AS modificado_por'),
                DB::raw('EXISTS(SELECT 1 FROM comision_escala ce WHERE ce.rol_id = r.id AND ce.estado_id = 1) AS tiene_escala')
            )
            ->orderBy('r.nombre')
            ->get();

        return response()->json(['roles' => $data]);
    }

    /**
     * Activa / desactiva el cálculo de comisión para un rol específico.
     * Hace UPSERT en comision_rol_config.
     */
    public function toggleCalculoRol(Request $request)
    {
        $rolId = (int) $request->input('rol_id');
        if (!$rolId) {
            return response()->json(['error' => 'rol_id requerido'], 422);
        }

        $actual = DB::table('comision_rol_config')->where('rol_id', $rolId)->value('calcular');
        // Si no existe el registro aún lo insertamos (debe existir por la migración, pero por seguridad)
        $nuevoEstado = $actual === null ? 0 : ($actual ? 0 : 1);

        DB::table('comision_rol_config')->updateOrInsert(
            ['rol_id' => $rolId],
            [
                'calcular'   => $nuevoEstado,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]
        );

        $rolNombre = DB::table('rol')->where('id', $rolId)->value('nombre');

        return response()->json([
            'calcular'    => $nuevoEstado,
            'rol_id'      => $rolId,
            'rol_nombre'  => $rolNombre,
        ]);
    }

    /** Categorías de cliente activas para el filtro */
    public function listaCategoriasClienteActivas()
    {
        $categorias = DB::table('cliente_categoria_escala')
            ->where('estado_id', 1)
            ->orderBy('nombre_categoria')
            ->select('id', 'nombre_categoria')
            ->get();
        return response()->json(['categorias' => $categorias], 200);
    }

    /** Categorías de precio activas para los catCli seleccionados (filtro) */
    public function categoriasPrecioParaFiltro(Request $request)
    {
        $catCliIds = array_filter(array_map('intval', (array) $request->input('cat_cli_ids', [])));

        $q = DB::table('categoria_precios as cp')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->where('cp.estado_id', 1)
            ->where('cce.estado_id', 1)
            ->select('cp.id', 'cp.nombre', 'cce.nombre_categoria AS cat_cli_nombre')
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre');

        if (!empty($catCliIds)) {
            $q->whereIn('cp.cliente_categoria_escala_id', $catCliIds);
        }

        return response()->json(['categorias' => $q->get()], 200);
    }

    /** Descarga plantilla filtrada por rol, catCli y/o catPrecio */
    public function descargarPlantillaFiltrada(Request $request)
    {
        $catCliIds    = array_filter(array_map('intval', (array) $request->input('cat_cli', [])));
        $catPrecioIds = array_filter(array_map('intval', (array) $request->input('cat_precio', [])));
        $rolIds       = array_filter(array_map('intval', (array) $request->input('rol', [])));

        $fecha  = now()->format('Ymd_His');
        $sufijo = empty($catCliIds) && empty($catPrecioIds) && empty($rolIds) ? 'todas' : 'filtrada';

        return Excel::download(
            new PlantillaComisionMasivaExport(
                empty($catCliIds)    ? null : array_values($catCliIds),
                empty($catPrecioIds) ? null : array_values($catPrecioIds),
                empty($rolIds)       ? null : array_values($rolIds)
            ),
            "plantilla_comisiones_{$sufijo}_{$fecha}.xlsx"
        );
    }

    /** Preview (sin guardar): cuenta existentes/nuevos/omitidos */
    public function previewCargaFiltrada(Request $request)
    {
        if (!$request->hasFile('archivo_comision') || !$request->file('archivo_comision')->isValid()) {
            return response()->json(['icon' => 'error', 'title' => 'Archivo inválido', 'text' => 'Suba un archivo Excel (.xlsx) válido.'], 422);
        }
        $ext = strtolower($request->file('archivo_comision')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            return response()->json(['icon' => 'error', 'title' => 'Formato no permitido', 'text' => 'Solo se aceptan archivos .xlsx o .xls.'], 422);
        }
        try {
            $import = new ComisionMasivaImport(Auth::user()->id, previewMode: true);
            Excel::import($import, $request->file('archivo_comision'));
            return response()->json([
                'existentes' => $import->previewExistentes,
                'nuevos'     => $import->previewNuevos,
                'omitidos'   => $import->previewOmitidos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error al leer archivo', 'text' => $e->getMessage()], 500);
        }
    }

    /** Procesa la carga selectiva (real) */
    public function procesarCargaFiltrada(Request $request)
    {
        if (!$request->hasFile('archivo_comision') || !$request->file('archivo_comision')->isValid()) {
            return response()->json(['icon' => 'error', 'title' => 'Archivo inválido', 'text' => 'Suba un archivo Excel (.xlsx) válido.'], 422);
        }
        $ext = strtolower($request->file('archivo_comision')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            return response()->json(['icon' => 'error', 'title' => 'Formato no permitido', 'text' => 'Solo se aceptan archivos .xlsx o .xls.'], 422);
        }
        try {
            $import = new ComisionMasivaImport(Auth::user()->id, previewMode: false);
            Excel::import($import, $request->file('archivo_comision'));

            return response()->json([
                'icon'        => 'success',
                'title'       => 'Carga completada',
                'text'        => "Insertados: {$import->insertados} | Actualizados: {$import->actualizados} | Omitidos: {$import->omitidos}",
                'insertados'  => $import->insertados,
                'actualizados'=> $import->actualizados,
                'omitidos'    => $import->omitidos,
                'errores'     => $import->errores,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error al procesar', 'text' => $e->getMessage()], 500);
        }
    }
}
