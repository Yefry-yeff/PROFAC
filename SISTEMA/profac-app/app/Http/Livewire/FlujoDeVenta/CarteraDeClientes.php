<?php

namespace App\Http\Livewire\FlujoDeVenta;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DataTables;

class CarteraDeClientes extends Component
{
    public $titulo = 'Cartera de Clientes';

    const ROL_ASESOR_COMERCIAL = 2;
    const ROL_TELE_ASESOR = 3;

    /**
     * Inicializar el componente
     */
    public function mount()
    {
        // Inicialización si es necesaria
    }

    /**
     * Renderizar la vista
     */
    public function render()
    {
        return view('livewire.flujodeventa.carteradeclientes');
    }

    /**
     * Construye el WHERE + bindings según los filtros combinables recibidos por querystring.
     * Filtros soportados: nombre, asesor, teleasesor, estado_cliente_id, sin_asignar,
     * municipio_id, departamento_id.
     */
    private function construirFiltros(Request $request): array
    {
        $where = ['1=1'];
        $bindings = [];

        $nombre = trim((string) $request->get('nombre', ''));
        if ($nombre !== '') {
            $where[] = '(cliente.nombre LIKE ? OR SOUNDEX(cliente.nombre) = SOUNDEX(?))';
            $bindings[] = "%{$nombre}%";
            $bindings[] = $nombre;
        }

        $asesor = trim((string) $request->get('asesor', ''));
        if ($asesor !== '') {
            $where[] = 'cliente.id IN (
                SELECT cu.cliente_id FROM cliente_usuario cu
                INNER JOIN users u ON u.id = cu.usuario_id
                WHERE cu.rol_id = ' . self::ROL_ASESOR_COMERCIAL . '
                AND (u.name LIKE ? OR SOUNDEX(u.name) = SOUNDEX(?))
            )';
            $bindings[] = "%{$asesor}%";
            $bindings[] = $asesor;
        }

        $teleasesor = trim((string) $request->get('teleasesor', ''));
        if ($teleasesor !== '') {
            $where[] = 'cliente.id IN (
                SELECT cu.cliente_id FROM cliente_usuario cu
                INNER JOIN users u ON u.id = cu.usuario_id
                WHERE cu.rol_id = ' . self::ROL_TELE_ASESOR . '
                AND (u.name LIKE ? OR SOUNDEX(u.name) = SOUNDEX(?))
            )';
            $bindings[] = "%{$teleasesor}%";
            $bindings[] = $teleasesor;
        }

        $estado = $request->get('estado_cliente_id');
        if ($estado !== null && $estado !== '') {
            $where[] = 'cliente.estado_cliente_id = ?';
            $bindings[] = (int) $estado;
        }

        if ($request->boolean('sin_asignar')) {
            $where[] = 'NOT EXISTS (SELECT 1 FROM cliente_usuario cu WHERE cu.cliente_id = cliente.id)';
        }

        $municipioId = $request->get('municipio_id');
        if ($municipioId) {
            $where[] = 'cliente.municipio_id = ?';
            $bindings[] = (int) $municipioId;
        }

        $departamentoId = $request->get('departamento_id');
        if ($departamentoId) {
            $where[] = 'departamento.id = ?';
            $bindings[] = (int) $departamentoId;
        }

        return [implode(' AND ', $where), $bindings];
    }

    /**
     * Listado individual (server-side DataTables). También se usa para el drill-down
     * lazy-load de un grupo específico (municipio_id/departamento_id como filtro extra).
     */
    public function listar(Request $request)
    {
        try {
            [$where, $bindings] = $this->construirFiltros($request);

            $sql = "
                SELECT
                    cliente.id,
                    cliente.nombre,
                    cliente.rtn,
                    cliente.telefono_empresa,
                    cliente.estado_cliente_id,
                    estado_cliente.descripcion AS estado_descripcion,
                    municipio.id AS municipio_id,
                    municipio.nombre AS municipio_nombre,
                    departamento.id AS departamento_id,
                    departamento.nombre AS departamento_nombre
                FROM cliente
                LEFT JOIN municipio ON municipio.id = cliente.municipio_id
                LEFT JOIN departamento ON departamento.id = municipio.departamento_id
                LEFT JOIN estado_cliente ON estado_cliente.id = cliente.estado_cliente_id
                WHERE {$where}
                ORDER BY cliente.nombre ASC
            ";

            $clientes = DB::select($sql, $bindings);

            $ids = array_map(fn($c) => $c->id, $clientes);
            $asignaciones = [];
            if (!empty($ids)) {
                $rows = DB::table('cliente_usuario as cu')
                    ->join('users as u', 'u.id', '=', 'cu.usuario_id')
                    ->whereIn('cu.cliente_id', $ids)
                    ->orderByDesc('cu.fecha_asignacion')
                    ->select('cu.cliente_id', 'u.id as usuario_id', 'u.name', 'cu.rol_id')
                    ->get();

                foreach ($rows as $row) {
                    $asignaciones[$row->cliente_id][$row->rol_id][] = ['id' => $row->usuario_id, 'name' => $row->name];
                }
            }

            foreach ($clientes as $c) {
                $c->asesores_comerciales = $asignaciones[$c->id][self::ROL_ASESOR_COMERCIAL] ?? [];
                $c->teleasesores = $asignaciones[$c->id][self::ROL_TELE_ASESOR] ?? [];
            }

            return DataTables::of($clientes)
                ->addColumn('seleccionar', function ($c) {
                    return '<input type="checkbox" class="cdc-chk-cliente" value="' . $c->id . '">';
                })
                ->addColumn('ubicacion', function ($c) {
                    return trim(($c->municipio_nombre ?? '-') . ', ' . ($c->departamento_nombre ?? '-'), ', ');
                })
                ->addColumn('asesores_html', function ($c) {
                    return $this->renderChips($c->asesores_comerciales, 'asesor');
                })
                ->addColumn('teleasesores_html', function ($c) {
                    return $this->renderChips($c->teleasesores, 'teleasesor');
                })
                ->addColumn('acciones', function ($c) {
                    $nombreJs = e(addslashes($c->nombre));
                    return '<button type="button" class="btn-cdc-accion" onclick="cdcAbrirAsignacion(' . $c->id . ')" title="Editar asignación"><i class="fa fa-user-tag"></i></button>'
                         . '<button type="button" class="btn-cdc-accion" onclick="cdcAbrirHistorial(' . $c->id . ', \'' . $nombreJs . '\')" title="Historial"><i class="fa fa-history"></i></button>';
                })
                ->rawColumns(['seleccionar', 'asesores_html', 'teleasesores_html', 'acciones'])
                ->make(true);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'mensaje' => 'Error al listar clientes: ' . $e->getMessage()], 500);
        }
    }

    private function renderChips(array $usuarios, string $tipoClase): string
    {
        if (empty($usuarios)) {
            return '<span class="text-muted small">Sin asignar</span>';
        }
        $html = '';
        foreach ($usuarios as $u) {
            $html .= '<span class="cdc-chip cdc-chip-' . $tipoClase . '">' . e($u['name']) . '</span> ';
        }
        return $html;
    }

    /**
     * Conteos agrupados por municipio o departamento (para la vista de árbol/acordeón).
     */
    public function listarAgrupado(Request $request)
    {
        try {
            $tipo = $request->get('tipo', 'municipio');
            [$where, $bindings] = $this->construirFiltros($request);

            if ($tipo === 'departamento') {
                $sql = "
                    SELECT departamento.id, departamento.nombre, COUNT(cliente.id) AS total
                    FROM cliente
                    INNER JOIN municipio ON municipio.id = cliente.municipio_id
                    INNER JOIN departamento ON departamento.id = municipio.departamento_id
                    WHERE {$where}
                    GROUP BY departamento.id, departamento.nombre
                    ORDER BY departamento.nombre ASC
                ";
            } else {
                $sql = "
                    SELECT municipio.id, municipio.nombre, departamento.nombre AS departamento_nombre, COUNT(cliente.id) AS total
                    FROM cliente
                    INNER JOIN municipio ON municipio.id = cliente.municipio_id
                    LEFT JOIN departamento ON departamento.id = municipio.departamento_id
                    WHERE {$where}
                    GROUP BY municipio.id, municipio.nombre, departamento.nombre
                    ORDER BY departamento.nombre ASC, municipio.nombre ASC
                ";
            }

            $grupos = DB::select($sql, $bindings);

            return response()->json(['success' => true, 'data' => $grupos]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'mensaje' => 'Error al agrupar clientes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Búsqueda de usuarios activos para los select2 (ajax) de asignación. Filtra por rol_id opcional.
     * Multi-rol: un usuario aparece si el rol buscado es su rol_id principal O si lo tiene como
     * rol adicional en usuario_rol (p.ej. un Tele Asesor que también es Asesor Comercial debe
     * aparecer en ambas búsquedas).
     */
    public function buscarUsuarios(Request $request)
    {
        $term = trim((string) $request->get('q', ''));
        $rolId = $request->get('rol_id');

        $query = DB::table('users')->where('estado_id', 1);
        if ($rolId) {
            $rolId = (int) $rolId;
            $query->where(function ($q) use ($rolId) {
                $q->where('rol_id', $rolId)
                  ->orWhereIn('id', function ($sub) use ($rolId) {
                      $sub->select('usuario_id')->from('usuario_rol')->where('rol_id', $rolId);
                  });
            });
        }
        if ($term !== '') {
            $query->where('name', 'like', "%{$term}%");
        }

        $usuarios = $query->orderBy('name')->limit(30)->get(['id', 'name']);

        return response()->json([
            'results' => $usuarios->map(fn($u) => ['id' => (int) $u->id, 'text' => $u->name])->values(),
        ]);
    }

    /**
     * Datos completos de un cliente: info básica + asesores/teleasesores actualmente asignados.
     */
    public function datosCliente($id)
    {
        $clienteId = (int) $id;

        $cliente = DB::selectOne("
            SELECT cliente.id, cliente.nombre, cliente.rtn,
                   municipio.nombre AS municipio_nombre, departamento.nombre AS departamento_nombre
            FROM cliente
            LEFT JOIN municipio ON municipio.id = cliente.municipio_id
            LEFT JOIN departamento ON departamento.id = municipio.departamento_id
            WHERE cliente.id = ?
        ", [$clienteId]);

        if (!$cliente) {
            return response()->json(['success' => false, 'mensaje' => 'Cliente no encontrado'], 404);
        }

        $asignados = DB::table('cliente_usuario as cu')
            ->join('users as u', 'u.id', '=', 'cu.usuario_id')
            ->where('cu.cliente_id', $clienteId)
            ->orderByDesc('cu.fecha_asignacion')
            ->select('u.id', 'u.name', 'cu.rol_id')
            ->get();

        $asesores = $asignados->where('rol_id', self::ROL_ASESOR_COMERCIAL)->map(fn($u) => ['id' => (int) $u->id, 'text' => $u->name])->values();
        $teleasesores = $asignados->where('rol_id', self::ROL_TELE_ASESOR)->map(fn($u) => ['id' => (int) $u->id, 'text' => $u->name])->values();

        return response()->json([
            'success' => true,
            'cliente' => $cliente,
            'asesores_comerciales' => $asesores,
            'teleasesores' => $teleasesores,
        ]);
    }

    /**
     * Historial de cambios de asignación de un solo cliente.
     */
    public function historialCliente($id)
    {
        $clienteId = (int) $id;

        $historial = DB::table('cliente_asesor_auditoria as a')
            ->join('users as asesor', 'asesor.id', '=', 'a.asesor_id')
            ->leftJoin('users as resp', 'resp.id', '=', 'a.usuario')
            ->where('a.cliente_id', $clienteId)
            ->orderByDesc('a.fecha')
            ->select('a.*', 'asesor.name as asesor_nombre', 'resp.name as usuario_nombre')
            ->get();

        return response()->json(['success' => true, 'data' => $historial]);
    }

    /**
     * Historial agregado de varios clientes (usado en la selección masiva).
     */
    public function historialMasivo(Request $request)
    {
        $ids = array_map('intval', $request->get('cliente_ids', []));
        if (empty($ids)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $historial = DB::table('cliente_asesor_auditoria as a')
            ->join('cliente as c', 'c.id', '=', 'a.cliente_id')
            ->join('users as asesor', 'asesor.id', '=', 'a.asesor_id')
            ->leftJoin('users as resp', 'resp.id', '=', 'a.usuario')
            ->whereIn('a.cliente_id', $ids)
            ->orderByDesc('a.fecha')
            ->select('a.*', 'c.nombre as cliente_nombre', 'asesor.name as asesor_nombre', 'resp.name as usuario_nombre')
            ->get();

        return response()->json(['success' => true, 'data' => $historial]);
    }

    /**
     * Asignación individual (un solo cliente). El modal envía la lista final y completa de
     * usuarios asignados por tipo (resultado de agregar/quitar en la UI); el backend siempre
     * aplica "reemplazar" (diff insert/delete), lo cual es un no-op si la lista no cambió.
     */
    public function asignarIndividual(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:cliente,id',
            'asesores_comerciales' => 'array',
            'asesores_comerciales.*' => 'integer|exists:users,id',
            'teleasesores' => 'array',
            'teleasesores.*' => 'integer|exists:users,id',
        ]);

        try {
            DB::beginTransaction();
            $loteId = (string) Str::uuid();

            $this->aplicarAsignacion(
                (int) $request->cliente_id,
                $request->asesores_comerciales ?? [],
                'reemplazar',
                $request->teleasesores ?? [],
                'reemplazar',
                $loteId,
                'Asignación individual'
            );

            $this->marcarExcepcionesZona(
                (int) $request->cliente_id,
                [self::ROL_ASESOR_COMERCIAL, self::ROL_TELE_ASESOR],
                'Asignación individual fuera de la configuración de zona'
            );

            DB::commit();
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Asignación actualizada correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo actualizar la asignación.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Asignación masiva (varios clientes seleccionados).
     */
    public function asignarMasivo(Request $request)
    {
        $request->validate([
            'cliente_ids' => 'required|array|min:1',
            'cliente_ids.*' => 'integer|exists:cliente,id',
            'modo_asesores' => 'required|in:sin_cambios,agregar,reemplazar',
            'modo_teleasesores' => 'required|in:sin_cambios,agregar,reemplazar',
            'asesores_comerciales' => 'array',
            'asesores_comerciales.*' => 'integer|exists:users,id',
            'teleasesores' => 'array',
            'teleasesores.*' => 'integer|exists:users,id',
        ]);

        if ($request->modo_asesores === 'sin_cambios' && $request->modo_teleasesores === 'sin_cambios') {
            return response()->json(['icon' => 'warning', 'title' => 'Atención', 'text' => 'No se seleccionó ningún cambio para aplicar.'], 422);
        }

        try {
            DB::beginTransaction();
            $loteId = (string) Str::uuid();

            foreach ($request->cliente_ids as $clienteId) {
                $this->aplicarAsignacion(
                    (int) $clienteId,
                    $request->asesores_comerciales ?? [],
                    $request->modo_asesores,
                    $request->teleasesores ?? [],
                    $request->modo_teleasesores,
                    $loteId,
                    'Asignación masiva'
                );

                $rolesModificados = [];
                if ($request->modo_asesores !== 'sin_cambios') {
                    $rolesModificados[] = self::ROL_ASESOR_COMERCIAL;
                }
                if ($request->modo_teleasesores !== 'sin_cambios') {
                    $rolesModificados[] = self::ROL_TELE_ASESOR;
                }
                $this->marcarExcepcionesZona((int) $clienteId, $rolesModificados, 'Asignación masiva fuera de la configuración de zona');
            }

            DB::commit();
            return response()->json([
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Asignación masiva aplicada a ' . count($request->cliente_ids) . ' cliente(s).',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo aplicar la asignación masiva.', 'error' => $e->getMessage()], 500);
        }
    }

    public function listarZonas(Request $request)
    {
        $buscar = trim((string) $request->get('q', ''));
        $query = DB::table('cliente_zonas as z')
            ->join('departamento as d', 'd.id', '=', 'z.departamento_id')
            ->leftJoin('cliente_zona_miembros as m', 'm.zona_id', '=', 'z.id')
            ->selectRaw('z.id, z.nombre, z.departamento_id, d.nombre as departamento_nombre,
                z.activo, z.observaciones, COUNT(m.id) as total_clientes')
            ->groupBy('z.id', 'z.nombre', 'z.departamento_id', 'd.nombre', 'z.activo', 'z.observaciones');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('z.nombre', 'like', "%{$buscar}%")
                    ->orWhere('d.nombre', 'like', "%{$buscar}%");
            });
        }
        if ($request->filled('activo')) {
            $query->where('z.activo', (int) $request->activo);
        }

        $zonas = $query->orderBy('d.nombre')->orderBy('z.nombre')->get();
        $responsables = $this->responsablesDeZonas($zonas->pluck('id')->all());
        foreach ($zonas as $zona) {
            $zona->asesores_comerciales = $responsables[$zona->id][self::ROL_ASESOR_COMERCIAL] ?? [];
            $zona->teleasesores = $responsables[$zona->id][self::ROL_TELE_ASESOR] ?? [];
        }

        return response()->json(['success' => true, 'data' => $zonas]);
    }

    public function catalogosZonas()
    {
        return response()->json([
            'success' => true,
            'departamentos' => DB::table('departamento')->orderBy('nombre')->get(['id', 'nombre']),
            'zonas' => DB::table('cliente_zonas as z')
                ->join('departamento as d', 'd.id', '=', 'z.departamento_id')
                ->where('z.activo', 1)
                ->orderBy('d.nombre')->orderBy('z.nombre')
                ->get(['z.id', 'z.nombre', 'z.departamento_id', 'd.nombre as departamento_nombre']),
        ]);
    }

    public function datosZona($id)
    {
        $zona = DB::table('cliente_zonas')->where('id', (int) $id)->first();
        if (!$zona) {
            return response()->json(['success' => false, 'mensaje' => 'Zona no encontrada.'], 404);
        }

        $miembros = DB::table('cliente_zona_miembros as m')
            ->join('cliente as c', 'c.id', '=', 'm.cliente_id')
            ->leftJoin('municipio as mu', 'mu.id', '=', 'c.municipio_id')
            ->leftJoin('departamento as d', 'd.id', '=', 'mu.departamento_id')
            ->leftJoin('estado_cliente as e', 'e.id', '=', 'c.estado_cliente_id')
            ->where('m.zona_id', $zona->id)
            ->orderBy('c.nombre')
            ->get([
                'c.id', 'c.nombre', 'c.rtn', 'c.telefono_empresa',
                'mu.nombre as municipio_nombre', 'd.nombre as departamento_nombre',
                'e.descripcion as estado_descripcion',
            ]);

        $asignaciones = $this->asignacionesDeClientes($miembros->pluck('id')->all());
        foreach ($miembros as $miembro) {
            $miembro->asesores_comerciales = $asignaciones[$miembro->id][self::ROL_ASESOR_COMERCIAL] ?? [];
            $miembro->teleasesores = $asignaciones[$miembro->id][self::ROL_TELE_ASESOR] ?? [];
        }

        $responsables = $this->responsablesDeZonas([$zona->id]);
        $zona->asesores_comerciales = $responsables[$zona->id][self::ROL_ASESOR_COMERCIAL] ?? [];
        $zona->teleasesores = $responsables[$zona->id][self::ROL_TELE_ASESOR] ?? [];

        return response()->json(['success' => true, 'zona' => $zona, 'miembros' => $miembros]);
    }

    public function buscarClientesZona(Request $request)
    {
        $request->validate(['departamento_id' => 'required|integer|exists:departamento,id']);
        $term = trim((string) $request->get('q', ''));
        $query = DB::table('cliente as c')
            ->join('municipio as m', 'm.id', '=', 'c.municipio_id')
            ->leftJoin('cliente_zona_miembros as zm', 'zm.cliente_id', '=', 'c.id')
            ->leftJoin('cliente_zonas as z', 'z.id', '=', 'zm.zona_id')
            ->where('m.departamento_id', (int) $request->departamento_id)
            ->select('c.id', 'c.nombre', 'c.rtn', 'zm.zona_id', 'z.nombre as zona_nombre');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('c.nombre', 'like', "%{$term}%")->orWhere('c.rtn', 'like', "%{$term}%");
            });
        }

        $clientes = $query->orderBy('c.nombre')->limit(40)->get();
        return response()->json(['results' => $clientes->map(function ($c) {
            $zona = $c->zona_nombre ? " - Zona actual: {$c->zona_nombre}" : '';
            return ['id' => (int) $c->id, 'text' => $c->nombre . $zona, 'zona_id' => $c->zona_id];
        })->values()]);
    }

    public function guardarZona(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:cliente_zonas,id',
            'departamento_id' => 'required|integer|exists:departamento,id',
            'nombre' => 'required|string|max:120',
            'activo' => 'required|boolean',
            'observaciones' => 'nullable|string|max:1000',
            'asesores_comerciales' => 'nullable|array',
            'asesores_comerciales.*' => 'integer|distinct|exists:users,id',
            'teleasesores' => 'nullable|array',
            'teleasesores.*' => 'integer|distinct|exists:users,id',
        ]);

        $duplicada = DB::table('cliente_zonas')
            ->where('departamento_id', $request->departamento_id)
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower(trim($request->nombre))])
            ->when($request->id, fn($q) => $q->where('id', '<>', (int) $request->id))
            ->exists();
        if ($duplicada) {
            return response()->json(['icon' => 'warning', 'title' => 'Zona duplicada', 'text' => 'Ya existe una zona con ese nombre en el departamento.'], 422);
        }

        try {
            DB::beginTransaction();
            $anterior = $request->id ? DB::table('cliente_zonas')->where('id', (int) $request->id)->first() : null;
            if ($anterior && (int) $anterior->departamento_id !== (int) $request->departamento_id) {
                $fuera = DB::table('cliente_zona_miembros as zm')
                    ->join('cliente as c', 'c.id', '=', 'zm.cliente_id')
                    ->join('municipio as m', 'm.id', '=', 'c.municipio_id')
                    ->where('zm.zona_id', $anterior->id)
                    ->where('m.departamento_id', '<>', (int) $request->departamento_id)
                    ->exists();
                if ($fuera) {
                    DB::rollBack();
                    return response()->json(['icon' => 'warning', 'title' => 'Departamento incompatible', 'text' => 'Retire o mueva los clientes actuales antes de cambiar el departamento.'], 422);
                }
            }

            $datos = [
                'departamento_id' => (int) $request->departamento_id,
                'nombre' => trim($request->nombre),
                'activo' => $request->boolean('activo'),
                'observaciones' => trim((string) $request->observaciones) ?: null,
                'asesor_comercial_id' => $request->input('asesores_comerciales.0'),
                'teleasesor_id' => $request->input('teleasesores.0'),
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ];

            if ($anterior) {
                DB::table('cliente_zonas')->where('id', $anterior->id)->update($datos);
                $zonaId = (int) $anterior->id;
                $accion = 'ACTUALIZAR_ZONA';
            } else {
                $datos['created_by'] = Auth::id();
                $datos['created_at'] = now();
                $zonaId = (int) DB::table('cliente_zonas')->insertGetId($datos);
                $accion = 'CREAR_ZONA';
            }

            $this->guardarResponsablesZona($zonaId, self::ROL_ASESOR_COMERCIAL, $request->input('asesores_comerciales', []));
            $this->guardarResponsablesZona($zonaId, self::ROL_TELE_ASESOR, $request->input('teleasesores', []));

            $zona = DB::table('cliente_zonas')->where('id', $zonaId)->first();
            $miembros = DB::table('cliente_zona_miembros')->where('zona_id', $zonaId)->pluck('cliente_id')->all();
            foreach ($miembros as $clienteId) {
                if ($zona->activo) {
                    $this->aplicarHerenciaZona($zona, (int) $clienteId, false);
                } else {
                    $this->retirarClienteDeZona($zonaId, (int) $clienteId, 'Zona desactivada');
                }
            }
            $this->auditarZona($zonaId, null, $accion, null, $anterior, $zona);
            DB::commit();
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Zona guardada correctamente.', 'id' => $zonaId]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo guardar la zona.', 'error' => $e->getMessage()], 500);
        }
    }

    public function asignarClientesZona(Request $request)
    {
        $request->validate([
            'zona_id' => 'required|integer|exists:cliente_zonas,id',
            'cliente_ids' => 'required|array|min:1',
            'cliente_ids.*' => 'integer|exists:cliente,id',
            'confirmar_movimiento' => 'nullable|boolean',
        ]);

        $zona = DB::table('cliente_zonas')->where('id', (int) $request->zona_id)->first();
        if (!$zona || !$zona->activo) {
            return response()->json(['icon' => 'warning', 'title' => 'Zona no disponible', 'text' => 'Solo puede agregar clientes a una zona activa.'], 422);
        }

        $ids = array_values(array_unique(array_map('intval', $request->cliente_ids)));
        $fueraDepartamento = DB::table('cliente as c')
            ->join('municipio as m', 'm.id', '=', 'c.municipio_id')
            ->whereIn('c.id', $ids)
            ->where('m.departamento_id', '<>', $zona->departamento_id)
            ->pluck('c.nombre');
        if ($fueraDepartamento->isNotEmpty()) {
            return response()->json([
                'icon' => 'warning',
                'title' => 'Departamento incompatible',
                'text' => 'Estos clientes no pertenecen al departamento de la zona: ' . $fueraDepartamento->implode(', '),
            ], 422);
        }

        $conflictos = DB::table('cliente_zona_miembros as m')
            ->join('cliente_zonas as z', 'z.id', '=', 'm.zona_id')
            ->join('cliente as c', 'c.id', '=', 'm.cliente_id')
            ->whereIn('m.cliente_id', $ids)
            ->where('m.zona_id', '<>', $zona->id)
            ->get(['c.id', 'c.nombre', 'z.nombre as zona_nombre']);
        $responsables = $this->responsablesDeZonas([$zona->id]);
        $destino = [
            self::ROL_ASESOR_COMERCIAL => $responsables[$zona->id][self::ROL_ASESOR_COMERCIAL] ?? [],
            self::ROL_TELE_ASESOR => $responsables[$zona->id][self::ROL_TELE_ASESOR] ?? [],
        ];
        $actuales = $this->asignacionesDeClientes($ids);
        $clientes = DB::table('cliente')->whereIn('id', $ids)->get(['id', 'nombre'])->keyBy('id');
        $vistaPrevia = [];
        foreach ($ids as $clienteId) {
            $asesoresActuales = $actuales[$clienteId][self::ROL_ASESOR_COMERCIAL] ?? [];
            $teleasesoresActuales = $actuales[$clienteId][self::ROL_TELE_ASESOR] ?? [];
            $conflicto = $conflictos->firstWhere('id', $clienteId);
            if ($conflicto
                || $this->idsUsuarios($asesoresActuales) !== $this->idsUsuarios($destino[self::ROL_ASESOR_COMERCIAL])
                || $this->idsUsuarios($teleasesoresActuales) !== $this->idsUsuarios($destino[self::ROL_TELE_ASESOR])) {
                $vistaPrevia[] = [
                    'id' => $clienteId,
                    'nombre' => $clientes[$clienteId]->nombre ?? ('Cliente ' . $clienteId),
                    'zona_actual' => $conflicto->zona_nombre ?? null,
                    'asesores_actuales' => $asesoresActuales,
                    'teleasesores_actuales' => $teleasesoresActuales,
                    'asesores_nuevos' => $destino[self::ROL_ASESOR_COMERCIAL],
                    'teleasesores_nuevos' => $destino[self::ROL_TELE_ASESOR],
                ];
            }
        }
        if (!empty($vistaPrevia) && !$request->boolean('confirmar_movimiento')) {
            return response()->json([
                'requiere_confirmacion' => true,
                'zona' => ['id' => $zona->id, 'nombre' => $zona->nombre],
                'cambios' => $vistaPrevia,
            ], 409);
        }

        try {
            DB::beginTransaction();
            foreach ($ids as $clienteId) {
                $anterior = DB::table('cliente_zona_miembros')->where('cliente_id', $clienteId)->first();
                if ($anterior && (int) $anterior->zona_id !== (int) $zona->id) {
                    $this->retirarClienteDeZona((int) $anterior->zona_id, $clienteId, 'Movido a otra zona');
                }
                DB::table('cliente_zona_miembros')->updateOrInsert(
                    ['cliente_id' => $clienteId],
                    ['zona_id' => $zona->id, 'asignado_por' => Auth::id(), 'created_at' => now(), 'updated_at' => now()]
                );
                DB::table('cliente_zona_excepciones')->where('cliente_id', $clienteId)->delete();
                $this->aplicarHerenciaZona($zona, $clienteId, true);
                $this->auditarZona((int) $zona->id, $clienteId, $anterior ? 'MOVER_CLIENTE' : 'AGREGAR_CLIENTE', 'Cliente incorporado a la zona');
            }
            DB::commit();
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => count($ids) . ' cliente(s) agregados a la zona.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudieron agregar los clientes.', 'error' => $e->getMessage()], 500);
        }
    }

    public function quitarClienteZona(Request $request)
    {
        $request->validate([
            'zona_id' => 'required|integer|exists:cliente_zonas,id',
            'cliente_id' => 'required|integer|exists:cliente,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $this->retirarClienteDeZona((int) $request->zona_id, (int) $request->cliente_id, 'Retirado manualmente de la zona');
            });
            return response()->json(['icon' => 'success', 'title' => 'Éxito', 'text' => 'Cliente retirado de la zona.']);
        } catch (\Throwable $e) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'No se pudo retirar el cliente.', 'error' => $e->getMessage()], 500);
        }
    }

    public function historialZona($id)
    {
        $rows = DB::table('cliente_zona_auditoria as a')
            ->leftJoin('cliente as c', 'c.id', '=', 'a.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'a.usuario_id')
            ->where('a.zona_id', (int) $id)
            ->orderByDesc('a.created_at')
            ->get(['a.*', 'c.nombre as cliente_nombre', 'u.name as usuario_nombre']);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    private function aplicarAsignacion(int $clienteId, array $asesoresComerciales, string $modoAsesores, array $teleasesores, string $modoTeleasesores, string $loteId, string $comentario)
    {
        if ($modoAsesores !== 'sin_cambios') {
            $this->sincronizarTipo($clienteId, self::ROL_ASESOR_COMERCIAL, $asesoresComerciales, $modoAsesores, $loteId, $comentario);
        }
        if ($modoTeleasesores !== 'sin_cambios') {
            $this->sincronizarTipo($clienteId, self::ROL_TELE_ASESOR, $teleasesores, $modoTeleasesores, $loteId, $comentario);
        }
        $this->sincronizarVendedorPrincipal($clienteId);
    }

    /**
     * Inserta los nuevos usuarios de un tipo (rol) para un cliente y, si el modo es "reemplazar",
     * elimina los que ya no están en la nueva lista (solo los de ese rol, sin tocar el otro tipo).
     */
    private function sincronizarTipo(int $clienteId, int $rolId, array $nuevosUsuarioIds, string $modo, string $loteId, string $comentario)
    {
        $nuevosUsuarioIds = array_values(array_unique(array_map('intval', $nuevosUsuarioIds)));

        $actuales = DB::table('cliente_usuario')
            ->where('cliente_id', $clienteId)
            ->where('rol_id', $rolId)
            ->pluck('usuario_id')
            ->map(fn($id) => (int) $id)
            ->all();

        $rolNombre = $rolId === self::ROL_ASESOR_COMERCIAL ? 'Asesor Comercial' : 'Tele Asesor';

        $aAgregar = array_diff($nuevosUsuarioIds, $actuales);
        foreach ($aAgregar as $usuarioId) {
            DB::table('cliente_usuario')->insert([
                'cliente_id' => $clienteId,
                'usuario_id' => $usuarioId,
                'rol_id' => $rolId,
                'fecha_asignacion' => now(),
                'asignado_por' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('cliente_asesor_auditoria')->insert([
                'cliente_id' => $clienteId,
                'asesor_id' => $usuarioId,
                'tipo' => $rolNombre,
                'accion' => 'INSERT',
                'usuario' => Auth::id(),
                'comentario' => $comentario,
                'lote_id' => $loteId,
                'fecha' => now(),
            ]);
        }

        if ($modo === 'reemplazar') {
            $aEliminar = array_diff($actuales, $nuevosUsuarioIds);
            foreach ($aEliminar as $usuarioId) {
                DB::table('cliente_usuario')
                    ->where('cliente_id', $clienteId)
                    ->where('usuario_id', $usuarioId)
                    ->where('rol_id', $rolId)
                    ->delete();
                DB::table('cliente_asesor_auditoria')->insert([
                    'cliente_id' => $clienteId,
                    'asesor_id' => $usuarioId,
                    'tipo' => $rolNombre,
                    'accion' => 'DELETE',
                    'usuario' => Auth::id(),
                    'comentario' => $comentario,
                    'lote_id' => $loteId,
                    'fecha' => now(),
                ]);
            }
        }
    }

    /**
     * Mantiene sincronizado el campo legacy cliente.vendedor con el asesor comercial
     * más recientemente asignado en el nuevo módulo, para no romper reportes/comisiones
     * que aún consultan ese campo directamente.
     */
    private function sincronizarVendedorPrincipal(int $clienteId)
    {
        $principal = DB::table('cliente_usuario')
            ->where('cliente_id', $clienteId)
            ->where('rol_id', self::ROL_ASESOR_COMERCIAL)
            ->orderByDesc('fecha_asignacion')
            ->value('usuario_id');

        if ($principal) {
            DB::table('cliente')->where('id', $clienteId)->update(['vendedor' => $principal]);
        }
    }

    private function aplicarHerenciaZona(object $zona, int $clienteId, bool $reiniciar): void
    {
        $responsables = $this->responsablesDeZonas([$zona->id]);
        $configuracion = [
            self::ROL_ASESOR_COMERCIAL => $responsables[$zona->id][self::ROL_ASESOR_COMERCIAL] ?? [],
            self::ROL_TELE_ASESOR => $responsables[$zona->id][self::ROL_TELE_ASESOR] ?? [],
        ];
        $loteId = (string) Str::uuid();

        foreach ($configuracion as $rolId => $usuarios) {
            if ($reiniciar) {
                $asignacionesActuales = DB::table('cliente_usuario')
                    ->where('cliente_id', $clienteId)
                    ->where('rol_id', $rolId)
                    ->pluck('usuario_id');
                foreach ($asignacionesActuales as $usuarioActualId) {
                    DB::table('cliente_asesor_auditoria')->insert([
                        'cliente_id' => $clienteId, 'asesor_id' => $usuarioActualId,
                        'tipo' => $rolId === self::ROL_ASESOR_COMERCIAL ? 'Asesor Comercial' : 'Tele Asesor',
                        'accion' => 'DELETE', 'usuario' => Auth::id(), 'comentario' => 'Reemplazo por asignación de zona',
                        'lote_id' => $loteId, 'fecha' => now(),
                    ]);
                }
                DB::table('cliente_usuario')->where('cliente_id', $clienteId)->where('rol_id', $rolId)->delete();
                DB::table('cliente_zona_asignaciones')->where('cliente_id', $clienteId)->where('rol_id', $rolId)->delete();
            }

            if (!$reiniciar && DB::table('cliente_zona_excepciones')->where('cliente_id', $clienteId)->where('rol_id', $rolId)->exists()) {
                continue;
            }

            $heredadas = $reiniciar ? collect() : DB::table('cliente_zona_asignaciones')->where('cliente_id', $clienteId)->where('rol_id', $rolId)->get();
            $usuariosIds = array_map(fn($usuario) => (int) $usuario['id'], $usuarios);
            foreach ($heredadas->whereNotIn('usuario_id', $usuariosIds) as $heredada) {
                DB::table('cliente_usuario')->where('cliente_id', $clienteId)->where('usuario_id', $heredada->usuario_id)->where('rol_id', $rolId)->delete();
                DB::table('cliente_asesor_auditoria')->insert([
                    'cliente_id' => $clienteId, 'asesor_id' => $heredada->usuario_id,
                    'tipo' => $rolId === self::ROL_ASESOR_COMERCIAL ? 'Asesor Comercial' : 'Tele Asesor',
                    'accion' => 'DELETE', 'usuario' => Auth::id(), 'comentario' => 'Actualización heredada de zona',
                    'lote_id' => $loteId, 'fecha' => now(),
                ]);
            }

            DB::table('cliente_zona_asignaciones')->where('cliente_id', $clienteId)->where('rol_id', $rolId)->delete();
            foreach ($usuariosIds as $usuarioId) {
                $existe = DB::table('cliente_usuario')->where('cliente_id', $clienteId)->where('usuario_id', $usuarioId)->where('rol_id', $rolId)->exists();
                $asignacionPerteneceZona = $reiniciar || $heredadas->contains(fn($heredada) => (int) $heredada->usuario_id === $usuarioId);
                if (!$existe) {
                    DB::table('cliente_usuario')->insert([
                        'cliente_id' => $clienteId, 'usuario_id' => $usuarioId, 'rol_id' => $rolId,
                        'fecha_asignacion' => now(), 'asignado_por' => Auth::id(), 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    DB::table('cliente_asesor_auditoria')->insert([
                        'cliente_id' => $clienteId, 'asesor_id' => $usuarioId,
                        'tipo' => $rolId === self::ROL_ASESOR_COMERCIAL ? 'Asesor Comercial' : 'Tele Asesor',
                        'accion' => 'INSERT', 'usuario' => Auth::id(), 'comentario' => 'Asignación heredada de zona',
                        'lote_id' => $loteId, 'fecha' => now(),
                    ]);
                    $asignacionPerteneceZona = true;
                }
                if ($asignacionPerteneceZona) {
                    DB::table('cliente_zona_asignaciones')->insert([
                        'zona_id' => $zona->id, 'cliente_id' => $clienteId, 'usuario_id' => $usuarioId,
                        'rol_id' => $rolId, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }
        $this->sincronizarVendedorPrincipal($clienteId);
    }

    private function marcarExcepcionesZona(int $clienteId, array $roles, string $detalle): void
    {
        $zonaId = DB::table('cliente_zona_miembros')->where('cliente_id', $clienteId)->value('zona_id');
        if (!$zonaId) {
            return;
        }

        foreach ($roles as $rolId) {
            DB::table('cliente_zona_excepciones')->updateOrInsert(
                ['cliente_id' => $clienteId, 'rol_id' => $rolId],
                ['usuario_id' => Auth::id(), 'created_at' => now(), 'updated_at' => now()]
            );
            DB::table('cliente_zona_asignaciones')->where('cliente_id', $clienteId)->where('rol_id', $rolId)->delete();
        }
        $this->auditarZona((int) $zonaId, $clienteId, 'EXCEPCION_INDIVIDUAL', $detalle);
    }

    private function retirarClienteDeZona(int $zonaId, int $clienteId, string $detalle): void
    {
        $heredadas = DB::table('cliente_zona_asignaciones')->where('zona_id', $zonaId)->where('cliente_id', $clienteId)->get();
        foreach ($heredadas as $heredada) {
            DB::table('cliente_usuario')->where('cliente_id', $clienteId)->where('usuario_id', $heredada->usuario_id)->where('rol_id', $heredada->rol_id)->delete();
        }
        DB::table('cliente_zona_asignaciones')->where('zona_id', $zonaId)->where('cliente_id', $clienteId)->delete();
        DB::table('cliente_zona_miembros')->where('zona_id', $zonaId)->where('cliente_id', $clienteId)->delete();
        DB::table('cliente_zona_excepciones')->where('cliente_id', $clienteId)->delete();
        $this->auditarZona($zonaId, $clienteId, 'QUITAR_CLIENTE', $detalle);
    }

    private function auditarZona(int $zonaId, ?int $clienteId, string $accion, ?string $detalle = null, $anterior = null, $nuevo = null): void
    {
        DB::table('cliente_zona_auditoria')->insert([
            'zona_id' => $zonaId,
            'cliente_id' => $clienteId,
            'accion' => $accion,
            'detalle' => $detalle,
            'datos_anteriores' => $anterior ? json_encode($anterior, JSON_UNESCAPED_UNICODE) : null,
            'datos_nuevos' => $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null,
            'usuario_id' => Auth::id(),
            'created_at' => now(),
        ]);
    }

    private function responsablesDeZonas(array $zonaIds): array
    {
        if (empty($zonaIds)) {
            return [];
        }

        $rows = DB::table('cliente_zona_responsables as r')
            ->join('users as u', 'u.id', '=', 'r.usuario_id')
            ->whereIn('r.zona_id', $zonaIds)
            ->orderBy('u.name')
            ->get(['r.zona_id', 'r.rol_id', 'u.id', 'u.name']);
        $resultado = [];
        foreach ($rows as $row) {
            $resultado[$row->zona_id][$row->rol_id][] = ['id' => (int) $row->id, 'name' => $row->name];
        }
        return $resultado;
    }

    private function asignacionesDeClientes(array $clienteIds): array
    {
        if (empty($clienteIds)) {
            return [];
        }

        $rows = DB::table('cliente_usuario as cu')
            ->join('users as u', 'u.id', '=', 'cu.usuario_id')
            ->whereIn('cu.cliente_id', $clienteIds)
            ->whereIn('cu.rol_id', [self::ROL_ASESOR_COMERCIAL, self::ROL_TELE_ASESOR])
            ->orderBy('u.name')
            ->get(['cu.cliente_id', 'cu.rol_id', 'u.id', 'u.name']);
        $resultado = [];
        foreach ($rows as $row) {
            $resultado[$row->cliente_id][$row->rol_id][] = ['id' => (int) $row->id, 'name' => $row->name];
        }
        return $resultado;
    }

    private function guardarResponsablesZona(int $zonaId, int $rolId, array $usuarioIds): void
    {
        DB::table('cliente_zona_responsables')->where('zona_id', $zonaId)->where('rol_id', $rolId)->delete();
        foreach (array_unique(array_map('intval', $usuarioIds)) as $usuarioId) {
            DB::table('cliente_zona_responsables')->insert([
                'zona_id' => $zonaId,
                'usuario_id' => $usuarioId,
                'rol_id' => $rolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function idsUsuarios(array $usuarios): array
    {
        $ids = array_map(fn($usuario) => (int) $usuario['id'], $usuarios);
        sort($ids);
        return $ids;
    }
}
