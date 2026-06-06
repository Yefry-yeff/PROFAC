<?php

namespace App\Http\Livewire\Logistica;

use Livewire\Component;
use App\Models\Logistica\DistribucionEntrega as ModelDistribucionEntrega;
use App\Models\Logistica\DistribucionEntregaFactura;
use App\Models\Logistica\EquipoEntrega;
use App\Models\Logistica\EntregaProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DataTables;
use App\Events\FlujoAvanzadoEvent;

class DistribucionEntrega extends Component
{
    public function render()
    {
        $equipos = EquipoEntrega::activos()->get();
        return view('livewire.logistica.distribucion-entrega', compact('equipos'));
    }

    /**
     * Vista de nueva distribución
     */
    public function nuevaDistribucion()
    {
        $equipos = EquipoEntrega::activos()->get();
        return view('livewire.logistica.nueva-distribucion', compact('equipos'));
    }

    /**
     * Guardar nueva distribución
     */
    public function guardarDistribucion(Request $request)
    {
        try {
            Log::info('=== INICIO guardarDistribucion ===');
            Log::info('Request completo:', $request->all());
            Log::info('Request JSON:', $request->json()->all());

            // Obtener datos del request (ya sea JSON o form-data)
            $data = $request->json()->all() ?: $request->all();

            Log::info('Datos procesados:', $data);

            $validator = Validator::make($data, [
                'equipo_entrega_id' => 'required|exists:equipos_entrega,id',
                'fecha_programada' => 'required|date',
                'observaciones' => 'nullable|string',
                'facturas' => 'required|array|min:1',
                'facturas.*' => 'required|exists:factura,id',
            ], [
                'equipo_entrega_id.required' => 'Debe seleccionar un equipo',
                'fecha_programada.required' => 'La fecha programada es obligatoria',
                'facturas.required' => 'Debe agregar al menos una factura',
                'facturas.*.exists' => 'Una o más facturas no existen',
            ]);

            if ($validator->fails()) {
                Log::warning('Validación fallida:', $validator->errors()->toArray());
                return response()->json([
                    'icon' => 'error',
                    'title' => 'Error de Validación',
                    'text' => implode(', ', $validator->errors()->all()),
                ], 422);
            }

            Log::info('Validación exitosa');
            Log::info('Datos validados:', [
                'equipo_entrega_id' => $data['equipo_entrega_id'],
                'fecha_programada' => $data['fecha_programada'],
                'observaciones' => $data['observaciones'] ?? null,
                'facturas' => $data['facturas'],
                'total_facturas' => count($data['facturas'])
            ]);

            // Verificar que ninguna factura esté ya en otra distribución activa
            $editarId     = $data['editar_id'] ?? null;
            $facturaIds   = $data['facturas'];
            $placeholders = implode(',', array_fill(0, count($facturaIds), '?'));
            $whereEditar  = $editarId ? ' AND de.id != ?' : '';
            $params       = $facturaIds;
            if ($editarId) {
                $params[] = (int) $editarId;
            }

            $bloqueadas = DB::select("
                SELECT def.factura_id, f.cai, de.id as distribucion_id, ee.nombre_equipo
                FROM distribuciones_entrega_facturas def
                INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                INNER JOIN factura f ON def.factura_id = f.id
                INNER JOIN equipos_entrega ee ON de.equipo_entrega_id = ee.id
                WHERE def.factura_id IN ({$placeholders})
                AND de.estado_id IN (1, 2)
                AND def.estado_entrega != 'anulada'
                {$whereEditar}
            ", $params);

            if (!empty($bloqueadas)) {
                $detalle = implode('; ', array_map(
                    fn($b) => "#$b->cai (Equipo: $b->nombre_equipo)",
                    $bloqueadas
                ));
                Log::warning('Facturas ya asignadas detectadas al guardar:', ['bloqueadas' => $bloqueadas]);
                return response()->json([
                    'icon'       => 'warning',
                    'title'      => 'Facturas ya asignadas',
                    'text'       => "Las siguientes facturas ya están en otra distribución activa: $detalle",
                    'bloqueadas' => $bloqueadas,
                ], 422);
            }

            DB::beginTransaction();
            Log::info('Transacción iniciada');

            $editarId = $data['editar_id'] ?? null;

            if ($editarId) {
                // ---- MODO EDICIÓN ----
                $distribucion = ModelDistribucionEntrega::findOrFail($editarId);
                $distribucion->equipo_entrega_id = $data['equipo_entrega_id'];
                $distribucion->fecha_programada  = $data['fecha_programada'];
                $distribucion->observaciones     = trim($data['observaciones'] ?? '');
                $distribucion->save();

                // Eliminar registros de entrega asociados y facturas anteriores
                $defIds = DistribucionEntregaFactura::where('distribucion_entrega_id', $distribucion->id)->pluck('id');
                EntregaProducto::whereIn('distribucion_factura_id', $defIds)->delete();
                DistribucionEntregaFactura::where('distribucion_entrega_id', $distribucion->id)->delete();
                Log::info('Facturas y registros de entrega anteriores eliminados para edición');
            } else {
                // ---- MODO CREACIÓN ----
                $distribucion = ModelDistribucionEntrega::create([
                    'equipo_entrega_id' => $data['equipo_entrega_id'],
                    'fecha_programada'  => $data['fecha_programada'],
                    'observaciones'     => trim($data['observaciones'] ?? ''),
                    'estado_id'         => 1, // Pendiente
                    'users_id_creador'  => Auth::id(),
                ]);
            }

            Log::info('Distribución ' . ($editarId ? 'actualizada' : 'creada') . ':', [
                'id' => $distribucion->id,
            ]);

            // Agregar facturas en el orden especificado
            foreach ($data['facturas'] as $index => $facturaId) {
                Log::info("Procesando factura {$index}", [
                    'factura_id' => $facturaId,
                    'orden' => $index + 1
                ]);

                $defRecord = DistribucionEntregaFactura::create([
                    'distribucion_entrega_id' => $distribucion->id,
                    'factura_id'              => $facturaId,
                    'orden_entrega'           => $index + 1,
                    'estado_entrega'          => 'sin_entrega',
                ]);

                // Pre-crear registros de entregas_productos para confirmación inmediata
                $productos = DB::table('venta_has_producto')
                    ->where('factura_id', $facturaId)
                    ->select('producto_id', 'cantidad')
                    ->get();

                foreach ($productos as $producto) {
                    EntregaProducto::firstOrCreate(
                        [
                            'distribucion_factura_id' => $defRecord->id,
                            'producto_id'             => $producto->producto_id,
                        ],
                        [
                            'cantidad_facturada'  => $producto->cantidad,
                            'cantidad_entregada'  => 0,
                            'entregado'           => 0,
                            'tiene_incidencia'    => 0,
                            'user_id_registro'    => Auth::id(),
                        ]
                    );
                }
            }

            Log::info('Todas las facturas procesadas correctamente');

            DB::commit();
            Log::info('Transacción confirmada - Distribución guardada exitosamente');

            return response()->json([
                'icon'            => 'success',
                'title'           => '¡Éxito!',
                'text'            => 'Distribución ' . (isset($editarId) && $editarId ? 'actualizada' : 'creada') . ' con ' . count($data['facturas']) . ' factura(s)',
                'distribucion_id' => $distribucion->id,
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar distribución:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al crear distribución: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar distribuciones
     */
    public function listarDistribuciones(Request $request)
    {
        try {
            // Obtener filtro de tipo de tabla
            $tipoTabla = $request->get('tipo');

            // Construir la consulta base con subconsultas
            $query = "
                SELECT
                    d.id,
                    d.fecha_programada,
                    d.observaciones,
                    d.estado_id,
                    e.nombre_equipo,
                    u.name AS creador,
                    d.created_at,
                    d.updated_at,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id) as total_facturas,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'entregado') as facturas_entregadas,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'parcial') as facturas_parciales,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'sin_entrega') as facturas_sin_entrega,
                    (SELECT MAX(df.updated_at)
                     FROM distribuciones_entrega_facturas df
                     WHERE df.distribucion_entrega_id = d.id
                     AND df.estado_entrega IN ('entregado', 'parcial')
                    ) as fecha_ultima_confirmacion,
                    (SELECT u2.name
                     FROM distribuciones_entrega_facturas df2
                     LEFT JOIN entregas_productos ep ON ep.distribucion_factura_id = df2.id
                     LEFT JOIN users u2 ON u2.id = ep.user_id_registro
                     WHERE df2.distribucion_entrega_id = d.id
                     AND df2.estado_entrega IN ('entregado', 'parcial')
                     ORDER BY df2.updated_at DESC
                     LIMIT 1
                    ) as usuario_confirmacion
                FROM distribuciones_entrega d
                INNER JOIN equipos_entrega e ON d.equipo_entrega_id = e.id
                INNER JOIN users u ON d.users_id_creador = u.id
            ";

            $whereConditions = [];

            if ($tipoTabla === 'pendientes') {
                // Tabla 1: Distribuciones pendientes de tratar
                // Estado pendiente (1) O (estado en proceso Y todas las facturas tienen estado distinto a 'sin_entrega')
                $whereConditions[] = "(
                    d.estado_id = 1
                    OR (
                        d.estado_id = 2
                        AND (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'sin_entrega') = 0
                        AND (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id) > 0
                    )
                )";
            } elseif ($tipoTabla === 'sin_finalizar') {
                // Tabla 2: Distribuciones sin finalizar
                // Estado en proceso (2) Y tienen una o varias facturas sin entregar
                // EXCLUYE estado pendiente (1)
                $whereConditions[] = "d.estado_id = 2";
                $whereConditions[] = "(SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'sin_entrega') > 0";
            } elseif ($tipoTabla === 'completadas') {
                // Tabla 3: Distribuciones completadas (sin cambios)
                $whereConditions[] = "d.estado_id = 3";
            }

            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            $query .= " ORDER BY d.fecha_programada DESC, d.id DESC";

            $datos = DB::select($query);

            return Datatables::of($datos)
                ->addColumn('estado', function ($datos) {
                    $estados = [
                        1 => '<span class="badge badge-warning">PENDIENTE</span>',
                        2 => '<span class="badge badge-info">EN PROCESO</span>',
                        3 => '<span class="badge badge-success">COMPLETADA</span>',
                        4 => '<span class="badge badge-danger">CANCELADA</span>',
                    ];
                    return $estados[$datos->estado_id] ?? '<span class="badge badge-secondary">DESCONOCIDO</span>';
                })
                ->addColumn('fecha_actualizacion', function ($datos) {
                    if (!empty($datos->fecha_ultima_confirmacion)) {
                        $fecha = \Carbon\Carbon::parse($datos->fecha_ultima_confirmacion);
                        return $fecha->format('d/m/Y H:i');
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('usuario_autorizacion', function ($datos) {
                    if (!empty($datos->usuario_confirmacion)) {
                        return htmlspecialchars($datos->usuario_confirmacion);
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->editColumn('observaciones', function ($datos) {
                    if (empty($datos->observaciones)) {
                        return '<span class="text-muted">Sin descripción</span>';
                    }
                    $texto = htmlspecialchars($datos->observaciones);
                    if (strlen($texto) > 50) {
                        return '<span title="' . $texto . '">' . substr($texto, 0, 50) . '...</span>';
                    }
                    return $texto;
                })
                ->addColumn('progreso', function ($datos) {
                    $total = $datos->total_facturas;
                    $entregadas = $datos->facturas_entregadas;
                    $parciales = $datos->facturas_parciales;
                    $sinEntregar = $total - $entregadas - $parciales;

                    $porcentaje = $total > 0 ? round(($entregadas / $total) * 100) : 0;

                    return "
                        <div>
                            <small>
                                <span class='badge badge-success'>{$entregadas}</span>
                                <span class='badge badge-warning'>{$parciales}</span>
                                <span class='badge badge-danger'>{$sinEntregar}</span>
                                / {$total} facturas
                            </small>
                            <div class='progress mt-1' style='height: 10px;'>
                                <div class='progress-bar bg-success' style='width: {$porcentaje}%'></div>
                            </div>
                        </div>
                    ";
                })
                ->addColumn('opciones', function ($datos) {
                    if ($datos->estado_id == 1) {
                        return '
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-info" onclick="verFacturas(' . $datos->id . ')" title="Ver facturas">
                                    <i class="fa fa-file-text"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-success" onclick="iniciarDistribucion(' . $datos->id . ')" title="Iniciar">
                                    <i class="fa fa-play"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="cancelarDistribucion(' . $datos->id . ')" title="Cancelar">
                                    <i class="fa fa-ban"></i>
                                </button>
                            </div>
                        ';
                    } elseif ($datos->estado_id == 2) {
                        // Solo mostrar botón de confirmar si NO hay facturas sin entregar
                        $facturasSinEntregar = $datos->facturas_sin_entrega ?? 0;
                        $botonConfirmar = '';

                        if ($facturasSinEntregar == 0) {
                            $botonConfirmar = '
                                <button type="button" class="btn btn-sm btn-primary" onclick="abrirConfirmacion(' . $datos->id . ')" title="Confirmar entregas">
                                    <i class="fa fa-check-circle"></i>
                                </button>';
                        }

                        return '
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-info" onclick="verFacturas(' . $datos->id . ')" title="Ver facturas">
                                    <i class="fa fa-file-text"></i>
                                </button>
                                ' . $botonConfirmar . '
                            </div>
                        ';
                    } else {
                        return '
                            <button type="button" class="btn btn-sm btn-info" onclick="verFacturas(' . $datos->id . ')" title="Ver facturas">
                                <i class="fa fa-file-text"></i>
                            </button>
                        ';
                    }
                })
                ->rawColumns(['estado', 'observaciones', 'progreso', 'fecha_actualizacion', 'usuario_autorizacion', 'opciones'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar distribuciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener facturas de una distribución
     */
    public function obtenerFacturas($distribucionId)
    {
        try {
            // Obtener información de la distribución
            $distribucion = ModelDistribucionEntrega::with('equipo')->findOrFail($distribucionId);

            $facturas = DB::select("
                SELECT
                    df.id,
                    df.factura_id,
                    df.orden_entrega,
                    df.estado_entrega,
                    df.fecha_entrega_real,
                    f.cai,
                    f.total,
                    c.nombre AS cliente,
                    c.direccion,
                    (SELECT COUNT(*) FROM entregas_productos WHERE distribucion_factura_id = df.id AND entregado = 1) as productos_entregados,
                    (SELECT COUNT(*) FROM entregas_productos WHERE distribucion_factura_id = df.id) as total_productos,
                    (SELECT COUNT(DISTINCT i.id)
                     FROM entregas_productos ep
                     INNER JOIN entregas_productos_incidencias i ON i.entrega_producto_id = ep.id
                     WHERE ep.distribucion_factura_id = df.id
                    ) as total_incidencias,
                    (SELECT COUNT(DISTINCT i.id)
                     FROM entregas_productos ep
                     INNER JOIN entregas_productos_incidencias i ON i.entrega_producto_id = ep.id
                     INNER JOIN entregas_incidencias_tratamientos t ON t.entrega_producto_incidencia_id = i.id
                     WHERE ep.distribucion_factura_id = df.id
                    ) as incidencias_tratadas
                FROM distribuciones_entrega_facturas df
                INNER JOIN factura f ON df.factura_id = f.id
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE df.distribucion_entrega_id = ?
                ORDER BY df.orden_entrega ASC
            ", [$distribucionId]);

            return response()->json([
                'success' => true,
                'facturas' => $facturas,
                'distribucion' => [
                    'id' => $distribucion->id,
                    'estado_id' => $distribucion->estado_id,
                    'nombre_equipo' => $distribucion->equipo->nombre_equipo ?? 'N/A',
                    'fecha_programada' => $distribucion->fecha_programada
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de distribución
     */
    public function verDistribucion($id)
    {
        return redirect()->route('logistica.distribuciones', ['ver' => $id]);
    }

    /**
     * Obtener datos completos de una distribución para edición
     */
    public function obtenerDatosDistribucion($id)
    {
        try {
            $distribucion = ModelDistribucionEntrega::with('equipo')->findOrFail($id);

            $facturas = DB::select("
                SELECT
                    df.factura_id  AS id,
                    f.cai          AS numero,
                    c.nombre       AS cliente,
                    c.direccion,
                    f.total
                FROM distribuciones_entrega_facturas df
                INNER JOIN factura  f ON df.factura_id  = f.id
                INNER JOIN cliente  c ON f.cliente_id   = c.id
                WHERE df.distribucion_entrega_id = ?
                ORDER BY df.orden_entrega ASC
            ", [$id]);

            return response()->json([
                'equipo_entrega_id' => $distribucion->equipo_entrega_id,
                'fecha_programada'  => $distribucion->fecha_programada->format('Y-m-d'),
                'observaciones'     => $distribucion->observaciones,
                'facturas'          => $facturas,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar carta de entrega en PDF
     */
    public function descargarCartaEntrega($id)
    {
        try {
            $distribucion = ModelDistribucionEntrega::with('equipo', 'creador')
                ->findOrFail($id);

            // Obtener facturas de la distribución ordenadas por orden de entrega
            $facturas = DB::select("
                SELECT
                    df.factura_id,
                    df.orden_entrega,
                    f.cai                    AS numero,
                    FORMAT(f.total, 2)       AS total,
                    DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') AS fecha,
                    c.nombre                 AS cliente,
                    c.direccion
                FROM distribuciones_entrega_facturas df
                INNER JOIN factura  f ON df.factura_id  = f.id
                INNER JOIN cliente  c ON f.cliente_id   = c.id
                WHERE df.distribucion_entrega_id = ?
                ORDER BY c.nombre ASC, df.orden_entrega ASC
            ", [$id]);

            // Agrupar por cliente -> facturas -> productos
            $clientesMap = [];
            foreach ($facturas as $fac) {
                $clienteKey = $fac->cliente;

                if (!isset($clientesMap[$clienteKey])) {
                    $clientesMap[$clienteKey] = [
                        'nombre'    => $fac->cliente,
                        'direccion' => $fac->direccion,
                        'facturas'  => [],
                    ];
                }

                // Obtener productos con la misma query de la factura corporativa
                $productos = DB::select("
                    SELECT
                        B.producto_id                                       AS codigo,
                        C.nombre                                            AS descripcion,
                        UPPER(J.nombre)                                     AS medida,
                        REPLACE(SUM(B.cantidad_s), '.00', '')              AS cantidad
                    FROM venta_has_producto B
                    INNER JOIN producto          C ON B.producto_id            = C.id
                    INNER JOIN unidad_medida_venta D ON B.unidad_medida_venta_id = D.id
                    INNER JOIN unidad_medida      J ON J.id                    = D.unidad_medida_id
                    WHERE B.factura_id = ?
                    GROUP BY B.producto_id, descripcion, medida, B.indice
                    ORDER BY B.indice ASC
                ", [$fac->factura_id]);

                $clientesMap[$clienteKey]['facturas'][] = [
                    'numero'        => $fac->numero,
                    'orden_entrega' => $fac->orden_entrega,
                    'fecha'         => $fac->fecha,
                    'total'         => $fac->total,
                    'productos'     => $productos,
                ];
            }

            $clientes = array_values($clientesMap);

            $pdf = \PDF::loadView('pdf/carta-entrega', compact('distribucion', 'clientes'))
                       ->setPaper('letter');

            return $pdf->stream("carta-entrega-{$distribucion->id}.pdf");

        } catch (\Exception $e) {
            \Log::error('Error generando carta entrega:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Buscar facturas para agregar a distribución
     */
    public function buscarFacturas(Request $request)
    {
        try {
            $busqueda = $request->input('q', '');

            $facturas = DB::select("
                SELECT
                    f.id,
                    f.cai,
                    f.total,
                    f.fecha_factura,
                    c.nombre AS cliente,
                    c.direccion,
                    c.telefono,
                    (
                        SELECT COUNT(*)
                        FROM distribuciones_entrega_facturas df
                        INNER JOIN distribuciones_entrega d ON df.distribucion_entrega_id = d.id
                        WHERE df.factura_id = f.id
                        AND d.estado_id IN (1, 2)
                    ) as entregas_pendientes,
                    (
                        SELECT COUNT(*)
                        FROM distribuciones_entrega_facturas df
                        WHERE df.factura_id = f.id
                        AND df.estado_entrega = 'entregado'
                    ) as entregas_completadas
                FROM facturacion f
                INNER JOIN clientes c ON f.cliente_id = c.id
                WHERE f.estado_id = 1
                AND (
                    f.cai LIKE ?
                    OR c.nombre LIKE ?
                    OR c.telefono LIKE ?
                )
                ORDER BY f.fecha_factura DESC
                LIMIT 50
            ", ["%{$busqueda}%", "%{$busqueda}%", "%{$busqueda}%"]);

            return response()->json([
                'success' => true,
                'facturas' => $facturas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener factura por número exacto
     * Valida que fecha_factura >= 2025-08-01 y que no esté entregada
     */
    public function obtenerFacturaPorNumero(Request $request)
    {
        try {
            $numero = $request->input('numero', '');

            $factura = DB::select("
                SELECT
                    f.id,
                    f.cai,
                    f.total,
                    f.fecha_emision as fecha_factura,
                    c.nombre AS cliente,
                    c.direccion
                FROM factura f
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE f.estado_factura_id IN (1, 2)
                AND f.estado_venta_id = 1
                AND f.cai = ?
                AND f.fecha_emision >= '2026-05-16'
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
                )
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                    WHERE def.factura_id = f.id
                    AND de.estado_id IN (1, 2)
                    AND def.estado_entrega != 'anulada'
                )
                LIMIT 1
            ", [$numero]);

            if (!empty($factura)) {
                return response()->json([
                    'success' => true,
                    'factura' => $factura[0]
                ], 200);
            } else {
                // Verificar si existe pero no cumple condiciones
                $facturaExiste = DB::select("SELECT id FROM factura WHERE cai = ? LIMIT 1", [$numero]);

                if (!empty($facturaExiste)) {
                    // Verificar por qué no es válida
                    $facturaInfo = DB::select("
                        SELECT
                            f.fecha_emision,
                            CASE WHEN EXISTS (
                                SELECT 1 FROM distribuciones_entrega_facturas def
                                WHERE def.factura_id = f.id AND def.estado_entrega = 'entregado'
                            ) THEN 1 ELSE 0 END as ya_entregada
                        FROM factura f
                        WHERE f.cai = ?
                    ", [$numero]);

                    if ($facturaInfo[0]->ya_entregada) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Esta factura ya fue entregada'
                        ], 422);
                    } elseif ($facturaInfo[0]->fecha_emision < date('Y-m-d', strtotime('-7 days'))) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Esta factura es anterior al 16/05/2026 y no está disponible para distribución'
                        ], 422);
                    }

                    // Verificar si está en distribución pendiente
                    $enPendiente = DB::select("
                        SELECT de.id as distribucion_id
                        FROM distribuciones_entrega_facturas def
                        INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                        WHERE def.factura_id = (SELECT id FROM factura WHERE cai = ? LIMIT 1)
                        AND de.estado_id IN (1, 2)
                        AND def.estado_entrega != 'anulada'
                        LIMIT 1
                    ", [$numero]);
                    if (!empty($enPendiente)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'La distribución de esta factura ya se encuentra asignada en una distribución pendiente o en proceso. Debe eliminarla de dicha distribución antes de volver a asignarla.'
                        ], 422);
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada o no disponible'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener facturas por cliente
     * Busca por nombre o teléfono, filtra fecha >= 2025-08-01 y no entregadas
     */
    public function obtenerFacturasPorCliente(Request $request)
    {
        try {
            $termino = $request->input('termino', '');

            if (strlen($termino) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ingrese al menos 3 caracteres para buscar'
                ], 422);
            }

            $facturas = DB::select("
                SELECT
                    f.id,
                    f.cai,
                    f.total,
                    DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') as fecha_factura,
                    c.nombre AS cliente,
                    c.direccion
                FROM factura f
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE f.estado_factura_id IN (1, 2)
                AND f.estado_venta_id = 1
                AND f.fecha_emision >= '2026-05-16'
                AND c.nombre LIKE ?
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
                )
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                    WHERE def.factura_id = f.id
                    AND de.estado_id IN (1, 2)
                    AND def.estado_entrega != 'anulada'
                )
                ORDER BY f.fecha_emision DESC, f.cai DESC
                LIMIT 50
            ", ["%{$termino}%"]);

            return response()->json([
                'success' => true,
                'facturas' => $facturas,
                'total' => count($facturas)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Autocompletado de facturas por número
     */
    public function autocompletadoFacturas(Request $request)
    {
        try {
            $termino = $request->input('termino', '');

            Log::info('Búsqueda de facturas autocompletado', [
                'termino' => $termino,
                'longitud' => strlen($termino)
            ]);

            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ingrese al menos 2 caracteres'
                ], 422);
            }

            $facturas = DB::select("
                SELECT
                    f.id,
                    f.cai,
                    f.total,
                    f.fecha_emision,
                    f.estado_factura_id AS estado_id,
                    c.nombre AS cliente,
                    (SELECT COUNT(*) FROM venta_has_producto vhp WHERE vhp.factura_id = f.id) AS cantidad_productos
                FROM factura f
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE f.estado_factura_id IN (1, 2)
                AND f.estado_venta_id = 1
                AND f.fecha_emision >= '2026-05-16'
                AND (f.cai LIKE ? OR f.numero_factura LIKE ? OR CAST(f.id AS CHAR) = ?)
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
                )
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                    WHERE def.factura_id = f.id
                    AND de.estado_id IN (1, 2)
                    AND def.estado_entrega != 'anulada'
                )
                ORDER BY f.fecha_emision DESC, f.cai DESC
                LIMIT 20
            ", ["%{$termino}%", "%{$termino}%", $termino]);

            Log::info('Facturas encontradas', [
                'total' => count($facturas)
            ]);

            return response()->json([
                'success' => true,
                'facturas' => $facturas
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en autocompletadoFacturas', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Autocompletado de clientes con facturas disponibles
     */
    public function autocompletadoClientes(Request $request)
    {
        try {
            $termino = $request->input('termino', '');

            Log::info('Búsqueda de clientes autocompletado', [
                'termino' => $termino,
                'longitud' => strlen($termino)
            ]);

            if (strlen($termino) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ingrese al menos 3 caracteres'
                ], 422);
            }

            // Mostrar TODOS los clientes activos que tienen facturas disponibles
            // sin filtrar por el término de búsqueda aún
            $clientes = DB::select("
                SELECT DISTINCT
                    c.id,
                    c.nombre,
                    (SELECT COUNT(*)
                     FROM factura f2
                     WHERE f2.cliente_id = c.id
                     AND f2.fecha_emision >= '2026-05-16'
                     AND f2.estado_factura_id IN (1, 2)
                     AND f2.estado_venta_id = 1
                     AND NOT EXISTS (
                         SELECT 1 FROM distribuciones_entrega_facturas def
                         WHERE def.factura_id = f2.id
                         AND def.estado_entrega = 'entregado'
                     )
                     AND NOT EXISTS (
                         SELECT 1 FROM distribuciones_entrega_facturas def
                         INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                         WHERE def.factura_id = f2.id
                         AND de.estado_id IN (1, 2)
                         AND def.estado_entrega != 'anulada'
                     )) as facturas_disponibles
                FROM cliente c
                INNER JOIN factura f ON c.id = f.cliente_id
                WHERE f.estado_factura_id IN (1, 2)
                AND f.estado_venta_id = 1
                AND f.fecha_emision >= '2026-05-16'
                AND c.nombre LIKE ?
                AND EXISTS (
                    SELECT 1 FROM factura f2
                    WHERE f2.cliente_id = c.id
                    AND f2.fecha_emision >= '2026-05-16'
                    AND f2.estado_factura_id IN (1, 2)
                    AND f2.estado_venta_id = 1
                    AND NOT EXISTS (
                        SELECT 1 FROM distribuciones_entrega_facturas def
                        WHERE def.factura_id = f2.id
                        AND def.estado_entrega = 'entregado'
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM distribuciones_entrega_facturas def
                        INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                        WHERE def.factura_id = f2.id
                        AND de.estado_id IN (1, 2)
                        AND def.estado_entrega != 'anulada'
                    )
                )
                ORDER BY c.nombre
                LIMIT 50
            ", ["%{$termino}%"]);

            Log::info('Clientes encontrados', [
                'total' => count($clientes),
                'clientes' => $clientes
            ]);

            return response()->json([
                'success' => true,
                'clientes' => $clientes
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en autocompletadoClientes', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Obtener facturas de un cliente específico por ID
     */
    public function obtenerFacturasPorClienteId(Request $request)
    {
        try {
            $clienteId = $request->input('cliente_id');

            Log::info('Obtener facturas por cliente ID', [
                'cliente_id' => $clienteId
            ]);

            $facturas = DB::select("
                SELECT
                    f.id,
                    f.cai,
                    f.total,
                    DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') as fecha_factura,
                    (SELECT COUNT(*) FROM venta_has_producto vhp WHERE vhp.factura_id = f.id) as cantidad_productos
                FROM factura f
                WHERE f.cliente_id = ?
                AND f.estado_factura_id IN (1, 2)
                AND f.estado_venta_id = 1
                AND f.fecha_emision >= '2026-05-16'
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
                )
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                    WHERE def.factura_id = f.id
                    AND de.estado_id IN (1, 2)
                    AND def.estado_entrega != 'anulada'
                )
                ORDER BY f.fecha_emision DESC, f.cai DESC
            ", [$clienteId]);

            Log::info('Facturas del cliente encontradas', [
                'total' => count($facturas),
                'cliente_id' => $clienteId
            ]);

            return response()->json([
                'success' => true,
                'facturas' => $facturas
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en obtenerFacturasPorClienteId', [
                'message' => $e->getMessage(),
                'cliente_id' => $request->input('cliente_id'),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalle de una factura
     */
    public function obtenerDetalleFactura(Request $request)
    {
        try {
            $facturaId = $request->input('factura_id');

            Log::info('Obtener detalle de factura', [
                'factura_id' => $facturaId
            ]);

            // Datos de la factura
            $factura = DB::selectOne("
                SELECT
                    f.id,
                    f.cai,
                    f.total,
                    f.sub_total as subtotal,
                    0 as descuento,
                    f.isv as impuesto,
                    DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') as fecha_factura,
                    c.nombre as cliente,
                    c.direccion,
                    c.telefono_empresa
                FROM factura f
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE f.id = ?
            ", [$facturaId]);

            // Detalle de productos
            $productos = DB::select("
                SELECT
                    vhp.producto_id as id,
                    vhp.cantidad,
                    vhp.precio_unidad as precio_unitario,
                    vhp.sub_total_s as subtotal,
                    0 as descuento,
                    vhp.isv_s as impuesto,
                    vhp.total_s as total,
                    p.nombre as producto,
                    CAST(p.id AS CHAR) as codigo
                FROM venta_has_producto vhp
                INNER JOIN producto p ON vhp.producto_id = p.id
                WHERE vhp.factura_id = ?
                ORDER BY p.nombre
            ", [$facturaId]);

            return response()->json([
                'success' => true,
                'factura' => $factura,
                'productos' => $productos
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en obtenerDetalleFactura', [
                'message' => $e->getMessage(),
                'factura_id' => $request->input('factura_id'),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle de factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar distribución (cambiar de Pendiente a En proceso)
     */
    public function iniciarDistribucion($distribucionId, Request $request)
    {
        try {
            $distribucion = ModelDistribucionEntrega::findOrFail($distribucionId);

            if ($distribucion->estado_id != 1) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Estado inválido',
                    'text' => 'Solo se pueden iniciar distribuciones pendientes',
                ], 422);
            }

            // Validar que ninguna factura de esta distribución esté en otra distribución en proceso o completada
            $facturasConflicto = DB::table('distribuciones_entrega_facturas as def')
                ->join('distribuciones_entrega as de', 'def.distribucion_entrega_id', '=', 'de.id')
                ->whereIn('def.factura_id', function($query) use ($distribucionId) {
                    $query->select('factura_id')
                        ->from('distribuciones_entrega_facturas')
                        ->where('distribucion_entrega_id', $distribucionId);
                })
                ->where('de.id', '!=', $distribucionId)
                ->whereIn('de.estado_id', [2, 3]) // En proceso o Completada
                ->where('def.estado_entrega', '!=', 'anulada') // Ignorar facturas anuladas
                ->select('def.factura_id', 'de.id as otra_distribucion_id', 'de.estado_id')
                ->get();

            if ($facturasConflicto->count() > 0) {
                $estadosTexto = $facturasConflicto->map(function($item) {
                    return $item->estado_id == 2 ? 'En Proceso' : 'Completada';
                })->unique()->implode(', ');

                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Facturas en uso',
                    'text' => 'Esta distribución contiene ' . $facturasConflicto->count() .
                             ' factura(s) que ya están en otra distribución con estado: ' . $estadosTexto .
                             '. No se puede iniciar hasta que esas distribuciones finalicen.',
                ], 422);
            }

            $distribucion->estado_id          = 2; // En proceso
            $distribucion->hora_salida        = $request->input('hora_salida');
            $distribucion->observaciones_salida = $request->input('observaciones_salida');
            $distribucion->save();

            return response()->json([
                'icon' => 'success',
                'title' => 'Distribución iniciada',
                'text' => 'El equipo puede comenzar las entregas',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al iniciar distribución: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancelar distribución
     */
    public function cancelarDistribucion($distribucionId)
    {
        try {
            $distribucion = ModelDistribucionEntrega::findOrFail($distribucionId);

            if (!in_array($distribucion->estado_id, [1, 2])) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'No se puede cancelar',
                    'text' => 'La distribución ya está completada o cancelada',
                ], 422);
            }

            $distribucion->estado_id = 4; // Cancelada
            $distribucion->save();

            return response()->json([
                'icon' => 'success',
                'title' => 'Distribución cancelada',
                'text' => 'La distribución ha sido cancelada',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al cancelar distribución: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validar que una distribución pueda ser completada
     */
    public function validarCompletarDistribucion($distribucionId)
    {
        try {
            Log::info("=== Validando si se puede completar distribución ID: {$distribucionId} ===");

            $errores = [];

            // Validar que no haya facturas con estado "sin_entrega"
            $facturasSinEntrega = DB::select("
                SELECT
                    df.id,
                    f.cai
                FROM distribuciones_entrega_facturas df
                INNER JOIN factura f ON df.factura_id = f.id
                WHERE df.distribucion_entrega_id = ?
                    AND df.estado_entrega = 'sin_entrega'
            ", [$distribucionId]);

            if (count($facturasSinEntrega) > 0) {
                $listaFacturas = array_map(function($f) {
                    return "Factura #{$f->cai}";
                }, $facturasSinEntrega);

                $errores[] = '<strong>Facturas sin entrega:</strong><ul class="mb-2">' .
                            implode('', array_map(fn($f) => "<li>{$f}</li>", $listaFacturas)) .
                            '</ul>';
            }

            // Validar que no haya incidencias sin tratamiento
            $facturasConIncidenciasSinTratamiento = DB::select("
                SELECT DISTINCT
                    def.id as factura_distribucion_id,
                    f.cai,
                    COUNT(DISTINCT i.id) as total_incidencias
                FROM distribuciones_entrega_facturas def
                INNER JOIN factura f ON def.factura_id = f.id
                INNER JOIN entregas_productos ep ON ep.distribucion_factura_id = def.id
                INNER JOIN entregas_productos_incidencias i ON i.entrega_producto_id = ep.id
                LEFT JOIN entregas_incidencias_tratamientos t ON t.entrega_producto_incidencia_id = i.id
                WHERE def.distribucion_entrega_id = ?
                    AND t.id IS NULL
                GROUP BY def.id, f.cai
            ", [$distribucionId]);

            if (count($facturasConIncidenciasSinTratamiento) > 0) {
                $listaIncidencias = array_map(function($f) {
                    return "Factura #{$f->cai} ({$f->total_incidencias} incidencia(s) sin tratar)";
                }, $facturasConIncidenciasSinTratamiento);

                $errores[] = '<strong>Facturas con incidencias sin tratamiento:</strong><ul class="mb-2">' .
                            implode('', array_map(fn($f) => "<li>{$f}</li>", $listaIncidencias)) .
                            '</ul>';
            }

            if (count($errores) > 0) {
                $mensaje = '<div class="text-left">';
                $mensaje .= '<p class="mb-2">No se puede completar la distribución por los siguientes motivos:</p>';
                $mensaje .= implode('', $errores);
                $mensaje .= '<p class="mb-0 mt-2"><strong>Por favor, corrija estos problemas antes de completar la distribución.</strong></p>';
                $mensaje .= '</div>';

                Log::info("Validación fallida. Errores encontrados: " . count($errores));

                return response()->json([
                    'puede_completar' => false,
                    'mensaje' => $mensaje
                ], 200);
            }

            Log::info("Validación exitosa. La distribución puede ser completada.");

            return response()->json([
                'puede_completar' => true,
                'mensaje' => 'Todas las validaciones pasaron correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al validar distribución:", [
                'distribucion_id' => $distribucionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al validar la distribución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Completar distribución manualmente
     */
    public function completarDistribucion($distribucionId)
    {
        try {
            Log::info("=== Completando distribución ID: {$distribucionId} ===");

            $distribucion = ModelDistribucionEntrega::findOrFail($distribucionId);

            Log::info("Distribución encontrada:", [
                'id' => $distribucion->id,
                'estado_actual' => $distribucion->estado_id,
                'fecha_programada' => $distribucion->fecha_programada
            ]);

            if ($distribucion->estado_id != 2) {
                Log::warning("Intento de completar distribución con estado inválido:", [
                    'distribucion_id' => $distribucion->id,
                    'estado_actual' => $distribucion->estado_id
                ]);

                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Estado inválido',
                    'text' => 'Solo se pueden completar distribuciones en proceso',
                ], 422);
            }

            // Validar que no haya facturas con estado "sin_entrega"
            $facturasSinEntrega = DB::select("
                SELECT
                    df.id,
                    f.cai
                FROM distribuciones_entrega_facturas df
                INNER JOIN factura f ON df.factura_id = f.id
                WHERE df.distribucion_entrega_id = ?
                    AND df.estado_entrega = 'sin_entrega'
            ", [$distribucionId]);

            if (count($facturasSinEntrega) > 0) {
                $listaFacturas = array_map(function($f) {
                    return "Factura #{$f->cai}";
                }, $facturasSinEntrega);

                Log::warning("Intento de completar distribución con facturas sin entrega:", [
                    'distribucion_id' => $distribucion->id,
                    'facturas_sin_entrega' => $listaFacturas
                ]);

                $mensaje = 'Las siguientes facturas aún están sin entrega: ' . implode(', ', $listaFacturas);

                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Facturas pendientes',
                    'text' => $mensaje,
                ], 422);
            }

            // Validar que no haya incidencias sin tratamiento
            $facturasConIncidenciasSinTratamiento = DB::select("
                SELECT DISTINCT
                    def.id as factura_distribucion_id,
                    f.cai,
                    COUNT(DISTINCT i.id) as total_incidencias
                FROM distribuciones_entrega_facturas def
                INNER JOIN factura f ON def.factura_id = f.id
                INNER JOIN entregas_productos ep ON ep.distribucion_factura_id = def.id
                INNER JOIN entregas_productos_incidencias i ON i.entrega_producto_id = ep.id
                LEFT JOIN entregas_incidencias_tratamientos t ON t.entrega_producto_incidencia_id = i.id
                WHERE def.distribucion_entrega_id = ?
                    AND t.id IS NULL
                GROUP BY def.id, f.cai
            ", [$distribucionId]);

            if (count($facturasConIncidenciasSinTratamiento) > 0) {
                $listaIncidencias = array_map(function($f) {
                    return "Factura #{$f->cai} ({$f->total_incidencias} incidencia(s))";
                }, $facturasConIncidenciasSinTratamiento);

                Log::warning("Intento de completar distribución con incidencias sin tratamiento:", [
                    'distribucion_id' => $distribucion->id,
                    'facturas_con_incidencias' => $listaIncidencias
                ]);

                $mensaje = 'Las siguientes facturas tienen incidencias sin tratamiento: ' . implode(', ', $listaIncidencias);

                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Incidencias pendientes',
                    'text' => $mensaje,
                ], 422);
            }

            DB::beginTransaction();

            $distribucion->estado_id = 3; // Completada
            $distribucion->save();

            // Sincronizar historico_flujo (tipo_tramite_id = 5) por cada factura
            // de la distribución para registrar el tramite_id de la entrega.
            $facturaIds = DB::table('distribuciones_entrega_facturas')
                ->where('distribucion_entrega_id', $distribucionId)
                ->pluck('factura_id');

            $notifFlujoIds = [];

            foreach ($facturaIds as $facturaId) {
                $flujoId = DB::table('historico_flujo')
                    ->whereIn('tipo_tramite_id', [3, 5])
                    ->where('tramite_id', $facturaId)
                    ->where('estado_id', '!=', 7)
                    ->orderByDesc('id')
                    ->value('flujo_id');

                if (!$flujoId) {
                    continue;
                }

                $notifFlujoIds[] = $flujoId;

                $registroEntrega = DB::table('historico_flujo')
                    ->where('flujo_id', $flujoId)
                    ->where('tipo_tramite_id', 5)
                    ->where('estado_id', '!=', 7)
                    ->orderByDesc('id')
                    ->first(['id', 'tramite_id', 'estado_id']);

                if (!$registroEntrega) {
                    continue;
                }

                $actualizacionEntrega = [
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ];

                if ((int) ($registroEntrega->tramite_id ?? 0) !== (int) $distribucionId) {
                    $actualizacionEntrega['tramite_id'] = $distribucionId;
                }

                if ((int) $registroEntrega->estado_id !== 1) {
                    $actualizacionEntrega['estado_id'] = 1;
                    $actualizacionEntrega['observaciones'] = 'Entrega completada en distribución #' . $distribucionId;
                }

                DB::table('historico_flujo')
                    ->where('id', $registroEntrega->id)
                    ->update($actualizacionEntrega);
            }

            DB::commit();

            // Notificar a los responsables de Entrega (tipo 5) por cada flujo completado
            foreach (array_unique($notifFlujoIds) as $notifFlujoId) {
                try {
                    $flujoCtx = DB::table('flujo')
                        ->where('id', $notifFlujoId)
                        ->first(['nombre']);
                    event(new FlujoAvanzadoEvent(
                        (int) $notifFlujoId,
                        5,
                        [
                            'cliente'    => $flujoCtx?->nombre ?? 'N/A',
                            'referencia' => 'Distribución #' . $distribucionId,
                        ]
                    ));
                } catch (\Throwable $notifEx) {
                    Log::error('NotificacionFlujo dispatch failed (DistribucionEntrega tipo=5)', [
                        'flujo_id'        => $notifFlujoId,
                        'distribucion_id' => $distribucionId,
                        'error'           => $notifEx->getMessage(),
                    ]);
                }
            }

            Log::info("Distribución completada exitosamente:", [
                'distribucion_id' => $distribucion->id,
                'nuevo_estado' => $distribucion->estado_id
            ]);

            return response()->json([
                'icon' => 'success',
                'title' => 'Distribución completada',
                'text' => 'La distribución ha sido marcada como completada',
            ], 200);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("Error al completar distribución:", [
                'distribucion_id' => $distribucionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al completar distribución: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener incidencias de una factura
     */
    public function obtenerIncidenciasFactura($facturaId)
    {
        try {
            Log::info("=== Obteniendo incidencias para factura ID: {$facturaId} ===");

            $factura = DistribucionEntregaFactura::with('factura')->findOrFail($facturaId);
            Log::info("Factura encontrada:", ['factura_id' => $factura->id, 'cai' => $factura->factura->cai ?? 'N/A']);

            // Obtener todos los productos de entrega de esta factura con sus incidencias
            $incidencias = DB::select("
                SELECT
                    i.id,
                    i.tipo,
                    i.descripcion,
                    i.created_at,
                    p.id as producto_id,
                    p.nombre as producto_nombre,
                    ep.id as entrega_producto_id,
                    (SELECT COUNT(*) FROM entregas_evidencias ee WHERE ee.entrega_producto_incidencia_id = i.id) as evidencias_count
                FROM entregas_productos_incidencias i
                INNER JOIN entregas_productos ep ON i.entrega_producto_id = ep.id
                INNER JOIN distribuciones_entrega_facturas def ON ep.distribucion_factura_id = def.id
                INNER JOIN producto p ON ep.producto_id = p.id
                WHERE def.id = ?
                ORDER BY i.created_at DESC
            ", [$facturaId]);

            // Obtener TODOS los tratamientos para las incidencias de esta factura
            $tratamientos = DB::select("
                SELECT
                    t.tratamiento,
                    t.created_at as tratamiento_fecha,
                    u.name as usuario_registro
                FROM entregas_incidencias_tratamientos t
                INNER JOIN entregas_productos_incidencias i ON t.entrega_producto_incidencia_id = i.id
                INNER JOIN entregas_productos ep ON i.entrega_producto_id = ep.id
                INNER JOIN users u ON t.user_id_registro = u.id
                WHERE ep.distribucion_factura_id = ?
                GROUP BY t.tratamiento, t.created_at, u.name
                ORDER BY t.created_at DESC
            ", [$facturaId]);

            Log::info("Total de incidencias encontradas: " . count($incidencias));
            Log::info("Total de tratamientos encontrados: " . count($tratamientos));

            return response()->json([
                'success' => true,
                'factura' => [
                    'id' => $factura->id,
                    'cai' => $factura->factura->cai ?? 'N/A',
                    'cliente' => $factura->factura->cliente->nombre_completo ?? 'N/A',
                    'estado_entrega' => $factura->estado_entrega,
                ],
                'incidencias' => $incidencias,
                'tratamientos' => $tratamientos,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al obtener incidencias de factura:", [
                'factura_id' => $facturaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener incidencias',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Guardar tratamiento para todas las incidencias de una factura
     */
    public function guardarTratamientoIncidencias(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'factura_id' => 'required|exists:distribuciones_entrega_facturas,id',
                'tratamiento' => 'required|string|min:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facturaId = $request->factura_id;
            $tratamiento = $request->tratamiento;

            // Verificar que la factura NO esté en estado 'sin_entrega'
            $factura = DistribucionEntregaFactura::findOrFail($facturaId);
            if ($factura->estado_entrega === 'sin_entrega') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede registrar tratamiento mientras la factura esté en estado "Sin Entrega". Primero debe confirmar la entrega.'
                ], 422);
            }

            // Obtener todas las incidencias de esta factura que aún no tienen tratamiento
            // Permitir múltiples tratamientos para la misma factura
            $incidencias = DB::select("
                SELECT i.id
                FROM entregas_productos_incidencias i
                INNER JOIN entregas_productos ep ON i.entrega_producto_id = ep.id
                WHERE ep.distribucion_factura_id = ?
            ", [$facturaId]);

            if (empty($incidencias)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron incidencias para esta factura'
                ], 404);
            }

            $userId = Auth::id();
            $registrados = 0;

            // Insertar tratamiento para cada incidencia
            foreach ($incidencias as $inc) {
                DB::table('entregas_incidencias_tratamientos')->insert([
                    'entrega_producto_incidencia_id' => $inc->id,
                    'tratamiento' => $tratamiento,
                    'user_id_registro' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $registrados++;
            }

            Log::info("Tratamiento registrado para {$registrados} incidencias de factura {$facturaId}");

            return response()->json([
                'success' => true,
                'message' => "Tratamiento registrado para {$registrados} incidencia(s)",
                'registrados' => $registrados
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al guardar tratamiento de incidencias:", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el tratamiento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Desbloquear factura (cambiar estado a sin_entrega)
     */
    public function desbloquearFactura($facturaId)
    {
        try {
            Log::info("=== Desbloqueando factura ID: {$facturaId} ===");

            $factura = DistribucionEntregaFactura::findOrFail($facturaId);
            Log::info("Factura encontrada:", [
                'id' => $factura->id,
                'estado_actual' => $factura->estado_entrega
            ]);

            // Cambiar estado a sin_entrega para desbloquear
            $factura->estado_entrega = 'sin_entrega';
            $factura->fecha_entrega_real = null;
            $factura->save();

            Log::info("Factura desbloqueada exitosamente:", [
                'factura_id' => $factura->id,
                'nuevo_estado' => $factura->estado_entrega
            ]);

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'title' => 'Desbloqueada',
                'text' => 'La factura ha sido cambiada a estado "Sin Entrega"'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al desbloquear factura:", [
                'factura_id' => $facturaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo desbloquear la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Anular entrega de una factura
     */
    public function anularEntrega($facturaId)
    {
        try {
            Log::info("=== Anulando entrega de factura ID: {$facturaId} ===");

            $request = request();
            $motivo = trim($request->input('motivo', ''));

            $factura = DistribucionEntregaFactura::findOrFail($facturaId);
            Log::info("Factura encontrada:", [
                'id' => $factura->id,
                'estado_actual' => $factura->estado_entrega
            ]);

            // Cambiar estado a anulada
            $factura->estado_entrega = 'anulada';
            $factura->fecha_entrega_real = null;
            $factura->motivo_anulacion = $motivo ?: null;
            $factura->save();

            Log::info("Entrega anulada exitosamente:", [
                'factura_id' => $factura->id,
                'nuevo_estado' => $factura->estado_entrega,
                'motivo' => $motivo
            ]);

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'title' => 'Anulada',
                'text' => 'La entrega ha sido anulada correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al anular entrega:", [
                'factura_id' => $facturaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo anular la entrega: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar entrega completa de una factura
     */
    public function confirmarEntregaFactura($facturaId)
    {
        try {
            Log::info("=== Confirmando entrega de factura ID: {$facturaId} ===");

            $request = request();
            $motivo = trim($request->input('motivo', ''));

            $factura = DistribucionEntregaFactura::findOrFail($facturaId);
            Log::info("Factura encontrada:", [
                'id' => $factura->id,
                'estado_actual' => $factura->estado_entrega
            ]);

            // Cambiar estado a entregado
            $factura->estado_entrega = 'entregado';
            $factura->fecha_entrega_real = now();
            $factura->motivo_confirmacion = $motivo ?: null;
            $factura->save();

            Log::info("Entrega confirmada exitosamente:", [
                'factura_id' => $factura->id,
                'nuevo_estado' => $factura->estado_entrega,
                'fecha_entrega' => $factura->fecha_entrega_real,
                'motivo' => $motivo
            ]);

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'title' => 'Confirmada',
                'text' => 'La entrega ha sido confirmada como completa'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al confirmar entrega:", [
                'factura_id' => $facturaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo confirmar la entrega: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar que no existan incidencias sin tratamiento en la distribución
     */
    public function validarIncidenciasSinTratamiento($distribucionId)
    {
        try {
            Log::info("=== Validando incidencias sin tratamiento para distribución ID: {$distribucionId} ===");

            // Obtener todas las facturas de la distribución que tienen incidencias sin tratamiento
            $facturasConIncidenciasSinTratamiento = DB::select("
                SELECT DISTINCT
                    def.id as factura_distribucion_id,
                    f.cai,
                    COUNT(DISTINCT i.id) as total_incidencias
                FROM distribuciones_entrega_facturas def
                INNER JOIN factura f ON def.factura_id = f.id
                INNER JOIN entregas_productos ep ON ep.distribucion_factura_id = def.id
                INNER JOIN entregas_productos_incidencias i ON i.entrega_producto_id = ep.id
                LEFT JOIN entregas_incidencias_tratamientos t ON t.entrega_producto_incidencia_id = i.id
                WHERE def.distribucion_entrega_id = ?
                    AND t.id IS NULL
                GROUP BY def.id, f.cai
            ", [$distribucionId]);

            if (count($facturasConIncidenciasSinTratamiento) > 0) {
                $listaFacturas = array_map(function($f) {
                    return "Factura #{$f->cai} ({$f->total_incidencias} incidencia(s))";
                }, $facturasConIncidenciasSinTratamiento);

                $mensaje = '<div class="text-left">';
                $mensaje .= '<p class="mb-2">Las siguientes facturas tienen incidencias sin tratamiento:</p>';
                $mensaje .= '<ul class="mb-2">';
                foreach ($listaFacturas as $item) {
                    $mensaje .= "<li>{$item}</li>";
                }
                $mensaje .= '</ul>';
                $mensaje .= '<p class="mb-0"><strong>Debe registrar el tratamiento de las incidencias antes de confirmar la entrega.</strong></p>';
                $mensaje .= '</div>';

                Log::info("Facturas con incidencias sin tratamiento encontradas: " . count($facturasConIncidenciasSinTratamiento));

                return response()->json([
                    'puede_confirmar' => false,
                    'mensaje' => $mensaje,
                    'facturas_pendientes' => $facturasConIncidenciasSinTratamiento
                ], 200);
            }

            Log::info("No hay incidencias sin tratamiento. Puede confirmar entrega.");

            return response()->json([
                'puede_confirmar' => true,
                'mensaje' => 'No hay incidencias pendientes de tratamiento'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al validar incidencias sin tratamiento:", [
                'distribucion_id' => $distribucionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al validar incidencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si una lista de facturas están disponibles para distribución
     * (no en distribución pendiente con estado_entrega != anulada)
     */
    public function verificarDisponibilidad(Request $request)
    {
        try {
            $facturaIds = $request->input('facturas', []);
            if (empty($facturaIds)) {
                return response()->json(['disponibles' => true, 'bloqueadas' => []], 200);
            }

            $editarId    = $request->input('editar_id');
            $placeholders = implode(',', array_fill(0, count($facturaIds), '?'));
            $whereEditar  = $editarId ? ' AND de.id != ?' : '';
            $params       = $facturaIds;
            if ($editarId) {
                $params[] = (int) $editarId;
            }

            $bloqueadas = DB::select("
                SELECT def.factura_id, f.cai, de.id as distribucion_id, ee.nombre_equipo
                FROM distribuciones_entrega_facturas def
                INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                INNER JOIN factura f ON def.factura_id = f.id
                INNER JOIN equipos_entrega ee ON de.equipo_entrega_id = ee.id
                WHERE def.factura_id IN ({$placeholders})
                AND de.estado_id IN (1, 2)
                AND def.estado_entrega != 'anulada'
                {$whereEditar}
            ", $params);

            return response()->json([
                'disponibles' => count($bloqueadas) === 0,
                'bloqueadas'  => $bloqueadas,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar disponibilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar distribución: anula todas las facturas pendientes (sin_entrega)
     * con un motivo general y luego marca la distribución como completada.
     */
    public function finalizarEntregaDistribucion($distribucionId)
    {
        try {
            Log::info("=== Finalizando distribución ID: {$distribucionId} ===");

            $request = request();
            $motivo  = trim($request->input('motivo', ''));

            $distribucion = ModelDistribucionEntrega::findOrFail($distribucionId);

            if ($distribucion->estado_id != 2) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Estado inválido',
                    'text'  => 'Solo se pueden finalizar distribuciones en proceso',
                ], 422);
            }

            // Bloquear si hay incidencias sin tratamiento
            $incidenciasSinTratar = DB::select("
                SELECT COUNT(DISTINCT i.id) as total
                FROM distribuciones_entrega_facturas def
                INNER JOIN entregas_productos ep ON ep.distribucion_factura_id = def.id
                INNER JOIN entregas_productos_incidencias i ON i.entrega_producto_id = ep.id
                LEFT JOIN entregas_incidencias_tratamientos t ON t.entrega_producto_incidencia_id = i.id
                WHERE def.distribucion_entrega_id = ?
                    AND t.id IS NULL
            ", [$distribucionId]);

            if ((int)($incidenciasSinTratar[0]->total ?? 0) > 0) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Incidencias sin tratar',
                    'text'  => 'No se puede finalizar porque hay incidencias sin tratamiento registrado.',
                ], 422);
            }

            DB::beginTransaction();

            // Anular todas las facturas con estado 'sin_entrega'
            DB::table('distribuciones_entrega_facturas')
                ->where('distribucion_entrega_id', $distribucionId)
                ->where('estado_entrega', 'sin_entrega')
                ->update([
                    'estado_entrega'   => 'anulada',
                    'fecha_entrega_real' => null,
                    'motivo_anulacion' => $motivo ?: 'Anulada al finalizar la distribución',
                    'updated_at'       => now(),
                ]);

            // Marcar distribución como completada
            $distribucion->estado_id = 3;
            $distribucion->save();

            DB::commit();

            Log::info("Distribución finalizada exitosamente: {$distribucionId}");

            return response()->json([
                'icon'  => 'success',
                'title' => 'Entrega finalizada',
                'text'  => 'Las facturas pendientes fueron anuladas y la distribución ha sido completada.',
            ], 200);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("Error al finalizar distribución:", [
                'distribucion_id' => $distribucionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'No se pudo finalizar la distribución: ' . $e->getMessage(),
            ], 500);
        }
    }
}
