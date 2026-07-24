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
}
