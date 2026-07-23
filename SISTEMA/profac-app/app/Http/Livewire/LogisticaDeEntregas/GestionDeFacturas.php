<?php

namespace App\Http\Livewire\LogisticaDeEntregas;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Logistica\FacturaTratamientoEntrega;
use App\Models\Logistica\FacturaTratamientoEntregaHistorial;
use App\Models\Logistica\DistribucionEntrega as ModelDistribucionEntrega;
use App\Models\Logistica\DistribucionEntregaFactura;
use App\Models\Logistica\EntregaProducto;
use App\Models\Logistica\EquipoEntrega;
use DataTables;

class GestionDeFacturas extends Component
{
    // Propiedades del componente
    public $titulo = 'Gestión de Distribución de Entregas';

    // Rol de "Gestor de entregas" (mismo usado en GeneradorFacturasComision)
    private const ROL_GESTOR_ENTREGA_ID = 16;

    // Misma fecha de corte usada en el resto del módulo de Logística de Entregas
    private const FECHA_CORTE_FACTURAS = '2026-05-16';

    // Roles con visión global (ven todas las facturas de todos los gestores):
    // Administrador (1) y Créditos y Cobros (4). Cualquier otro rol solo ve
    // las facturas cuyo gestor de entrega asignado sea el propio usuario.
    private const ROLES_VISTA_GLOBAL = [1, 4];

    public function mount()
    {
        //
    }

    public function render()
    {
        $gestores = DB::select("
            SELECT id, name FROM users WHERE rol_id = ? ORDER BY name ASC
        ", [self::ROL_GESTOR_ENTREGA_ID]);

        $equipos = EquipoEntrega::activos()->get();

        return view('livewire.logisticadeentregas.gestiondefacturas', [
            'gestores' => $gestores,
            'equipos' => $equipos,
            'esVistaGlobal' => $this->esVistaGlobal(),
        ]);
    }

    /* =========================================================================
     |  Helpers SQL reutilizables
     * ========================================================================= */

    /**
     * Condición base: facturas "vigentes" candidatas al flujo de distribución
     * (misma regla que AgrupacionesDeEntregas::condicionFacturaPendiente).
     * Requiere que la tabla factura esté aliasada como "f".
     */
    private function condicionFacturaBase(): string
    {
        $fecha = self::FECHA_CORTE_FACTURAS;
        return "
            f.estado_factura_id IN (1, 2)
            AND f.estado_venta_id = 1
            AND f.fecha_emision >= '{$fecha}'
        ";
    }

    /**
     * Excluye facturas ya entregadas o actualmente en una distribución activa
     * (Pendiente/En proceso) y no anulada. Requiere alias "f".
     */
    private function condicionNoAsignadaActiva(): string
    {
        return "
            AND NOT EXISTS (
                SELECT 1 FROM distribuciones_entrega_facturas def
                WHERE def.factura_id = f.id AND def.estado_entrega = 'entregado'
            )
            AND NOT EXISTS (
                SELECT 1 FROM distribuciones_entrega_facturas def
                INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                WHERE def.factura_id = f.id
                AND de.estado_id IN (1, 2)
                AND def.estado_entrega != 'anulada'
            )
        ";
    }

    /**
     * Indica si el usuario autenticado tiene visión global (ve las facturas
     * de todos los gestores). Roles: Administrador (1) y Créditos y Cobros (4).
     */
    private function esVistaGlobal(): bool
    {
        $rolId = (int) (Auth::user()->rol_id ?? 0);
        return in_array($rolId, self::ROLES_VISTA_GLOBAL, true);
    }

    /**
     * Filtro por gestor de entrega. Los roles con visión global (admin,
     * créditos y cobros) pueden filtrar opcionalmente por ?gestor_id= (o ver
     * todos). Cualquier otro usuario queda forzado a ver únicamente las
     * facturas que tiene asignadas como gestor de entrega, sin importar el
     * valor recibido en la petición.
     */
    private function aplicarFiltroGestor(Request $request, array &$params): string
    {
        if (!$this->esVistaGlobal()) {
            $params[] = Auth::id();
            return ' AND f.gestor_entrega = ? ';
        }

        $gestorId = $request->input('gestor_id');
        if (!empty($gestorId) && $gestorId !== 'todos') {
            $params[] = (int) $gestorId;
            return ' AND f.gestor_entrega = ? ';
        }
        return '';
    }

    /**
     * Registra un movimiento en la bitácora de tratamiento de entrega.
     */
    private function registrarHistorial($facturaId, string $estado, array $extra = []): void
    {
        FacturaTratamientoEntregaHistorial::create(array_merge([
            'factura_id' => $facturaId,
            'estado' => $estado,
            'distribucion_entrega_id' => null,
            'department_id' => null,
            'municipality_id' => null,
            'direccion_entrega' => null,
            'observaciones' => null,
            'user_id' => Auth::id(),
        ], $extra));
    }

    /* =========================================================================
     |  Listados (Yajra DataTables) de cada sección del flujo
     * ========================================================================= */

    /**
     * Facturas sin gestor de entrega asignado. Sección informativa: SOLO
     * lectura, sin acciones posibles desde aquí.
     */
    public function listarSinGestor(Request $request)
    {
        $sql = "
            SELECT
                f.id, f.cai, f.numero_factura, f.total, f.fecha_emision,
                c.nombre AS cliente,
                COALESCE(uv.name, '') AS asesor_comercial
            FROM factura f
            INNER JOIN cliente c ON c.id = f.cliente_id
            LEFT JOIN users uv ON uv.id = f.vendedor
            WHERE {$this->condicionFacturaBase()}
            AND f.gestor_entrega IS NULL
            ORDER BY f.fecha_emision DESC
        ";

        $datos = collect(DB::select($sql));

        return DataTables::of($datos)->make(true);
    }

    /**
     * Facturas con gestor asignado, pendientes de tratamiento (departamento/
     * municipio/dirección de entrega).
     */
    public function listarSinTratar(Request $request)
    {
        $params = [];
        $filtroGestor = $this->aplicarFiltroGestor($request, $params);

        $sql = "
            SELECT
                f.id, f.cai, f.numero_factura, f.total, f.fecha_emision,
                c.nombre AS cliente,
                COALESCE(g.name, '-') AS gestor,
                COALESCE(uv.name, '') AS asesor_comercial
            FROM factura f
            INNER JOIN cliente c ON c.id = f.cliente_id
            LEFT JOIN users g ON g.id = f.gestor_entrega
            LEFT JOIN users uv ON uv.id = f.vendedor
            WHERE {$this->condicionFacturaBase()}
            AND f.gestor_entrega IS NOT NULL
            {$filtroGestor}
            AND NOT EXISTS (
                SELECT 1 FROM factura_tratamiento_entrega fte WHERE fte.factura_id = f.id
            )
            {$this->condicionNoAsignadaActiva()}
            ORDER BY f.fecha_emision DESC
        ";

        $datos = collect(DB::select($sql, $params));

        return DataTables::of($datos)->make(true);
    }

    /**
     * Facturas ya tratadas (con departamento/municipio/dirección) pero aún
     * no asignadas a un equipo de entrega.
     */
    public function listarTratadas(Request $request)
    {
        $params = [];
        $filtroGestor = $this->aplicarFiltroGestor($request, $params);

        $sql = "
            SELECT
                f.id, f.cai, f.numero_factura, f.total, f.fecha_emision,
                c.nombre AS cliente,
                COALESCE(g.name, '-') AS gestor,
                COALESCE(uv.name, '') AS asesor_comercial,
                fte.direccion_entrega,
                d.nombre AS departamento,
                m.nombre AS municipio,
                fte.updated_at AS fecha_tratamiento
            FROM factura f
            INNER JOIN cliente c ON c.id = f.cliente_id
            INNER JOIN factura_tratamiento_entrega fte ON fte.factura_id = f.id
            LEFT JOIN users g ON g.id = f.gestor_entrega
            LEFT JOIN users uv ON uv.id = f.vendedor
            LEFT JOIN departamento d ON d.id = fte.department_id
            LEFT JOIN municipio m ON m.id = fte.municipality_id
            WHERE {$this->condicionFacturaBase()}
            AND f.gestor_entrega IS NOT NULL
            {$filtroGestor}
            {$this->condicionNoAsignadaActiva()}
            ORDER BY fte.updated_at DESC
        ";

        $datos = collect(DB::select($sql, $params));

        return DataTables::of($datos)->make(true);
    }

    /**
     * Facturas ya asignadas a un equipo de entrega (distribución activa),
     * aún no completadas.
     */
    public function listarAsignadas(Request $request)
    {
        $params = [];
        $filtroGestor = $this->aplicarFiltroGestor($request, $params);

        $sql = "
            SELECT
                f.id, f.cai, f.numero_factura, f.total, f.fecha_emision,
                c.nombre AS cliente,
                COALESCE(g.name, '-') AS gestor,
                COALESCE(uv.name, '') AS asesor_comercial,
                fte.direccion_entrega,
                d.nombre AS departamento,
                m.nombre AS municipio,
                de.id AS distribucion_id,
                de.fecha_programada,
                ee.nombre_equipo,
                def.estado_entrega
            FROM factura f
            INNER JOIN cliente c ON c.id = f.cliente_id
            INNER JOIN distribuciones_entrega_facturas def ON def.factura_id = f.id
            INNER JOIN distribuciones_entrega de ON de.id = def.distribucion_entrega_id
            INNER JOIN equipos_entrega ee ON ee.id = de.equipo_entrega_id
            LEFT JOIN factura_tratamiento_entrega fte ON fte.factura_id = f.id
            LEFT JOIN departamento d ON d.id = fte.department_id
            LEFT JOIN municipio m ON m.id = fte.municipality_id
            LEFT JOIN users g ON g.id = f.gestor_entrega
            LEFT JOIN users uv ON uv.id = f.vendedor
            WHERE de.estado_id IN (1, 2)
            AND def.estado_entrega NOT IN ('entregado', 'anulada')
            {$filtroGestor}
            ORDER BY de.fecha_programada DESC
        ";

        $datos = collect(DB::select($sql, $params));

        return DataTables::of($datos)->make(true);
    }

    /**
     * Facturas cuya entrega ya fue completada (estado derivado de
     * distribuciones_entrega_facturas.estado_entrega = 'entregado').
     */
    public function listarCompletadas(Request $request)
    {
        $params = [];
        $filtroGestor = $this->aplicarFiltroGestor($request, $params);

        $sql = "
            SELECT
                f.id, f.cai, f.numero_factura, f.total, f.fecha_emision,
                c.nombre AS cliente,
                COALESCE(g.name, '-') AS gestor,
                COALESCE(uv.name, '') AS asesor_comercial,
                fte.direccion_entrega,
                d.nombre AS departamento,
                m.nombre AS municipio,
                de.id AS distribucion_id,
                ee.nombre_equipo,
                def.fecha_entrega_real
            FROM factura f
            INNER JOIN cliente c ON c.id = f.cliente_id
            INNER JOIN distribuciones_entrega_facturas def ON def.factura_id = f.id
            INNER JOIN distribuciones_entrega de ON de.id = def.distribucion_entrega_id
            INNER JOIN equipos_entrega ee ON ee.id = de.equipo_entrega_id
            LEFT JOIN factura_tratamiento_entrega fte ON fte.factura_id = f.id
            LEFT JOIN departamento d ON d.id = fte.department_id
            LEFT JOIN municipio m ON m.id = fte.municipality_id
            LEFT JOIN users g ON g.id = f.gestor_entrega
            LEFT JOIN users uv ON uv.id = f.vendedor
            WHERE def.estado_entrega = 'entregado'
            {$filtroGestor}
            ORDER BY def.fecha_entrega_real DESC
        ";

        $datos = collect(DB::select($sql, $params));

        return DataTables::of($datos)->make(true);
    }

    /**
     * Resumen (contadores) para las tarjetas KPI de cada sección.
     */
    public function resumenEstados(Request $request)
    {
        try {
            $paramsSinTratar = [];
            $filtroSinTratar = $this->aplicarFiltroGestor($request, $paramsSinTratar);
            $sinTratar = DB::select("
                SELECT COUNT(*) AS total FROM factura f
                WHERE {$this->condicionFacturaBase()}
                AND f.gestor_entrega IS NOT NULL
                {$filtroSinTratar}
                AND NOT EXISTS (SELECT 1 FROM factura_tratamiento_entrega fte WHERE fte.factura_id = f.id)
                {$this->condicionNoAsignadaActiva()}
            ", $paramsSinTratar)[0]->total;

            $paramsTratadas = [];
            $filtroTratadas = $this->aplicarFiltroGestor($request, $paramsTratadas);
            $tratadas = DB::select("
                SELECT COUNT(*) AS total FROM factura f
                INNER JOIN factura_tratamiento_entrega fte ON fte.factura_id = f.id
                WHERE {$this->condicionFacturaBase()}
                AND f.gestor_entrega IS NOT NULL
                {$filtroTratadas}
                {$this->condicionNoAsignadaActiva()}
            ", $paramsTratadas)[0]->total;

            $paramsAsignadas = [];
            $filtroAsignadas = $this->aplicarFiltroGestor($request, $paramsAsignadas);
            $asignadas = DB::select("
                SELECT COUNT(*) AS total FROM factura f
                INNER JOIN distribuciones_entrega_facturas def ON def.factura_id = f.id
                INNER JOIN distribuciones_entrega de ON de.id = def.distribucion_entrega_id
                WHERE de.estado_id IN (1, 2) AND def.estado_entrega NOT IN ('entregado', 'anulada')
                {$filtroAsignadas}
            ", $paramsAsignadas)[0]->total;

            $paramsCompletadas = [];
            $filtroCompletadas = $this->aplicarFiltroGestor($request, $paramsCompletadas);
            $completadas = DB::select("
                SELECT COUNT(*) AS total FROM factura f
                INNER JOIN distribuciones_entrega_facturas def ON def.factura_id = f.id
                WHERE def.estado_entrega = 'entregado'
                {$filtroCompletadas}
            ", $paramsCompletadas)[0]->total;

            $sinGestor = DB::select("
                SELECT COUNT(*) AS total FROM factura f
                WHERE {$this->condicionFacturaBase()}
                AND f.gestor_entrega IS NULL
            ")[0]->total;

            return response()->json([
                'success' => true,
                'sin_gestor' => (int) $sinGestor,
                'sin_tratar' => (int) $sinTratar,
                'tratadas' => (int) $tratadas,
                'asignadas' => (int) $asignadas,
                'completadas' => (int) $completadas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el resumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================================================================
     |  Acciones
     * ========================================================================= */

    /**
     * Trata (asigna departamento/municipio/dirección de entrega) un lote de
     * facturas. Transición: pendiente -> tratada.
     */
    public function tratarFacturas(Request $request)
    {
        $data = $request->json()->all() ?: $request->all();

        $validator = Validator::make($data, [
            'factura_ids' => 'required|array|min:1',
            'factura_ids.*' => 'required|integer|exists:factura,id',
            'department_id' => 'required|integer|exists:departamento,id',
            'municipality_id' => 'required|integer|exists:municipio,id',
            'direccion_entrega' => 'required|string|max:255',
        ], [
            'factura_ids.required' => 'Debe seleccionar al menos una factura',
            'department_id.required' => 'Debe seleccionar un departamento',
            'municipality_id.required' => 'Debe seleccionar un municipio',
            'direccion_entrega.required' => 'La dirección de entrega es obligatoria',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error de validación',
                'text' => implode(', ', $validator->errors()->all()),
            ], 422);
        }

        try {
            $facturaIds = array_map('intval', $data['factura_ids']);
            $placeholders = implode(',', array_fill(0, count($facturaIds), '?'));

            // Revalida que las facturas sigan calificando como "sin tratar"
            // (evita saltos de estado por condiciones de carrera).
            $validas = DB::select("
                SELECT f.id FROM factura f
                WHERE f.id IN ({$placeholders})
                AND {$this->condicionFacturaBase()}
                AND f.gestor_entrega IS NOT NULL
                AND NOT EXISTS (SELECT 1 FROM factura_tratamiento_entrega fte WHERE fte.factura_id = f.id)
                {$this->condicionNoAsignadaActiva()}
            ", $facturaIds);

            $idsValidos = array_map(fn($r) => (int) $r->id, $validas);
            $noValidos = array_diff($facturaIds, $idsValidos);

            if (empty($idsValidos)) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Sin cambios',
                    'text' => 'Ninguna de las facturas seleccionadas está disponible para tratar (verifique que tengan gestor asignado y no estén ya tratadas/asignadas).',
                ], 422);
            }

            DB::beginTransaction();

            foreach ($idsValidos as $facturaId) {
                $factura = DB::selectOne('SELECT gestor_entrega FROM factura WHERE id = ?', [$facturaId]);

                FacturaTratamientoEntrega::create([
                    'factura_id' => $facturaId,
                    'department_id' => $data['department_id'],
                    'municipality_id' => $data['municipality_id'],
                    'direccion_entrega' => trim($data['direccion_entrega']),
                    'gestor_entrega_id' => $factura->gestor_entrega ?? null,
                    'usr_registro' => Auth::id(),
                ]);

                $this->registrarHistorial($facturaId, 'tratada', [
                    'department_id' => $data['department_id'],
                    'municipality_id' => $data['municipality_id'],
                    'direccion_entrega' => trim($data['direccion_entrega']),
                ]);
            }

            DB::commit();

            $mensaje = count($idsValidos) . ' factura(s) tratada(s) correctamente.';
            if (!empty($noValidos)) {
                $mensaje .= ' ' . count($noValidos) . ' factura(s) se omitieron por no estar ya disponibles.';
            }

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => $mensaje,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al tratar las facturas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Asigna un lote de facturas ya tratadas a un equipo de entrega, creando
     * la distribución correspondiente. Transición: tratada -> asignada.
     */
    public function asignarEquipo(Request $request)
    {
        $data = $request->json()->all() ?: $request->all();

        $validator = Validator::make($data, [
            'factura_ids' => 'required|array|min:1',
            'factura_ids.*' => 'required|integer|exists:factura,id',
            'equipo_entrega_id' => 'required|exists:equipos_entrega,id',
            'fecha_programada' => 'required|date',
            'observaciones' => 'nullable|string',
        ], [
            'factura_ids.required' => 'Debe seleccionar al menos una factura',
            'equipo_entrega_id.required' => 'Debe seleccionar un equipo',
            'fecha_programada.required' => 'La fecha programada es obligatoria',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error de validación',
                'text' => implode(', ', $validator->errors()->all()),
            ], 422);
        }

        try {
            $facturaIds = array_map('intval', $data['factura_ids']);
            $placeholders = implode(',', array_fill(0, count($facturaIds), '?'));

            // Solo facturas actualmente "tratadas" (con fila en
            // factura_tratamiento_entrega) y no ya asignadas/entregadas.
            $validas = DB::select("
                SELECT f.id FROM factura f
                INNER JOIN factura_tratamiento_entrega fte ON fte.factura_id = f.id
                WHERE f.id IN ({$placeholders})
                {$this->condicionNoAsignadaActiva()}
            ", $facturaIds);

            $idsValidos = array_map(fn($r) => (int) $r->id, $validas);

            if (empty($idsValidos)) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Sin cambios',
                    'text' => 'Ninguna de las facturas seleccionadas está disponible para asignar (deben estar tratadas y no asignadas ya a otra distribución).',
                ], 422);
            }

            DB::beginTransaction();

            $distribucion = ModelDistribucionEntrega::create([
                'equipo_entrega_id' => $data['equipo_entrega_id'],
                'fecha_programada' => $data['fecha_programada'],
                'observaciones' => trim($data['observaciones'] ?? ''),
                'estado_id' => 1, // Pendiente
                'users_id_creador' => Auth::id(),
            ]);

            foreach ($idsValidos as $index => $facturaId) {
                $defRecord = DistribucionEntregaFactura::create([
                    'distribucion_entrega_id' => $distribucion->id,
                    'factura_id' => $facturaId,
                    'orden_entrega' => $index + 1,
                    'estado_entrega' => 'sin_entrega',
                ]);

                // Pre-crear registros de entregas_productos (igual que
                // DistribucionEntrega::guardarDistribucion) para permitir
                // la confirmación inmediata de entrega.
                $productos = DB::table('venta_has_producto')
                    ->where('factura_id', $facturaId)
                    ->select('producto_id', 'cantidad')
                    ->get();

                foreach ($productos as $producto) {
                    EntregaProducto::firstOrCreate(
                        [
                            'distribucion_factura_id' => $defRecord->id,
                            'producto_id' => $producto->producto_id,
                        ],
                        [
                            'cantidad_facturada' => $producto->cantidad,
                            'cantidad_entregada' => 0,
                            'entregado' => 0,
                            'tiene_incidencia' => 0,
                            'user_id_registro' => Auth::id(),
                        ]
                    );
                }

                $this->registrarHistorial($facturaId, 'asignada', [
                    'distribucion_entrega_id' => $distribucion->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => count($idsValidos) . ' factura(s) asignada(s) a la distribución #' . $distribucion->id,
                'distribucion_id' => $distribucion->id,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al asignar las facturas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bitácora de movimientos de una factura dentro del flujo de tratamiento
     * y distribución (para el modal "Ver historial").
     */
    public function historialFactura($facturaId)
    {
        try {
            $historial = FacturaTratamientoEntregaHistorial::where('factura_id', $facturaId)
                ->orderBy('created_at', 'asc')
                ->get();

            $resultado = $historial->map(function ($h) {
                $departamento = $h->department_id
                    ? DB::selectOne('SELECT nombre FROM departamento WHERE id = ?', [$h->department_id])
                    : null;
                $municipio = $h->municipality_id
                    ? DB::selectOne('SELECT nombre FROM municipio WHERE id = ?', [$h->municipality_id])
                    : null;
                $usuario = $h->user_id
                    ? DB::selectOne('SELECT name FROM users WHERE id = ?', [$h->user_id])
                    : null;

                return [
                    'estado' => $h->estado,
                    'distribucion_entrega_id' => $h->distribucion_entrega_id,
                    'departamento' => $departamento->nombre ?? null,
                    'municipio' => $municipio->nombre ?? null,
                    'direccion_entrega' => $h->direccion_entrega,
                    'observaciones' => $h->observaciones,
                    'usuario' => $usuario->name ?? '-',
                    'fecha' => $h->created_at,
                ];
            });

            // Verificar si la factura ya fue entregada (estado derivado, sin bitácora propia)
            $entregada = DB::selectOne("
                SELECT def.fecha_entrega_real FROM distribuciones_entrega_facturas def
                WHERE def.factura_id = ? AND def.estado_entrega = 'entregado'
                ORDER BY def.fecha_entrega_real DESC LIMIT 1
            ", [$facturaId]);

            if ($entregada) {
                $resultado->push([
                    'estado' => 'completada',
                    'distribucion_entrega_id' => null,
                    'departamento' => null,
                    'municipio' => null,
                    'direccion_entrega' => null,
                    'observaciones' => 'Entrega confirmada (estado derivado del registro de entrega de productos)',
                    'usuario' => '-',
                    'fecha' => $entregada->fecha_entrega_real,
                ]);
            }

            return response()->json([
                'success' => true,
                'historial' => $resultado->values(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }
}
