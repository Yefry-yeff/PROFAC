<?php

namespace App\Http\Livewire\Logistica;

use Livewire\Component;
use App\Models\Logistica\DistribucionEntrega as ModelDistribucionEntrega;
use App\Models\Logistica\DistribucionEntregaFactura;
use App\Models\Logistica\EquipoEntrega;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use DataTables;
use Auth;

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
            $request->validate([
                'equipo_entrega_id' => 'required|exists:equipos_entrega,id',
                'fecha_programada' => 'required|date',
                'observaciones' => 'nullable|string',
                'facturas' => 'required|array|min:1',
                'facturas.*' => 'required|exists:facturacion,id',
            ], [
                'equipo_entrega_id.required' => 'Debe seleccionar un equipo',
                'fecha_programada.required' => 'La fecha programada es obligatoria',
                'facturas.required' => 'Debe agregar al menos una factura',
            ]);

            DB::beginTransaction();

            // Crear distribución
            $distribucion = ModelDistribucionEntrega::create([
                'equipo_entrega_id' => $request->equipo_entrega_id,
                'fecha_programada' => $request->fecha_programada,
                'observaciones' => trim($request->observaciones),
                'estado_id' => 1, // Pendiente
                'users_id_creador' => Auth::id(),
            ]);

            // Agregar facturas en el orden especificado
            foreach ($request->facturas as $index => $facturaId) {
                DistribucionEntregaFactura::create([
                    'distribucion_entrega_id' => $distribucion->id,
                    'factura_id' => $facturaId,
                    'orden_entrega' => $index + 1,
                    'estado_entrega' => 'sin_entrega',
                ]);
            }

            DB::commit();

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => 'Distribución creada con ' . count($request->facturas) . ' factura(s)',
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
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
    public function listarDistribuciones()
    {
        try {
            $datos = DB::select("
                SELECT 
                    d.id,
                    d.fecha_programada,
                    d.observaciones,
                    d.estado_id,
                    e.nombre_equipo,
                    u.name AS creador,
                    d.created_at,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id) as total_facturas,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'entregado') as facturas_entregadas,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'parcial') as facturas_parciales
                FROM distribuciones_entrega d
                INNER JOIN equipos_entrega e ON d.equipo_entrega_id = e.id
                INNER JOIN users u ON d.users_id_creador = u.id
                ORDER BY d.fecha_programada DESC, d.id DESC
            ");

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
                        return '
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-info" onclick="verFacturas(' . $datos->id . ')" title="Ver facturas">
                                    <i class="fa fa-file-text"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="abrirConfirmacion(' . $datos->id . ')" title="Confirmar entregas">
                                    <i class="fa fa-check-circle"></i>
                                </button>
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
                ->rawColumns(['estado', 'progreso', 'opciones'])
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
            $facturas = DB::select("
                SELECT 
                    df.id,
                    df.factura_id,
                    df.orden_entrega,
                    df.estado_entrega,
                    df.fecha_entrega_real,
                    f.numero_factura,
                    f.total,
                    c.nombre AS cliente,
                    c.direccion,
                    c.telefono,
                    (SELECT COUNT(*) FROM entregas_productos WHERE distribucion_factura_id = df.id AND entregado = 1) as productos_entregados,
                    (SELECT COUNT(*) FROM entregas_productos WHERE distribucion_factura_id = df.id) as total_productos
                FROM distribuciones_entrega_facturas df
                INNER JOIN facturacion f ON df.factura_id = f.id
                INNER JOIN clientes c ON f.cliente_id = c.id
                WHERE df.distribucion_entrega_id = ?
                ORDER BY df.orden_entrega ASC
            ", [$distribucionId]);

            return response()->json([
                'success' => true,
                'facturas' => $facturas
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
     * Buscar facturas para agregar a distribución
     */
    public function buscarFacturas(Request $request)
    {
        try {
            $busqueda = $request->input('q', '');
            
            $facturas = DB::select("
                SELECT 
                    f.id,
                    f.numero_factura,
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
                    f.numero_factura LIKE ?
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
                    f.numero_factura,
                    f.total,
                    f.fecha_emision as fecha_factura,
                    c.nombre AS cliente,
                    c.direccion
                FROM factura f
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE f.estado_factura_id = 1
                AND f.numero_factura = ?
                AND f.fecha_emision >= '2025-08-01'
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
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
                $facturaExiste = DB::select("SELECT id FROM factura WHERE numero_factura = ? LIMIT 1", [$numero]);
                
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
                        WHERE f.numero_factura = ?
                    ", [$numero]);
                    
                    if ($facturaInfo[0]->ya_entregada) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Esta factura ya fue entregada'
                        ], 422);
                    } elseif ($facturaInfo[0]->fecha_emision < '2025-08-01') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Esta factura es anterior al 01/08/2025 y no está disponible para entrega'
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
                    f.numero_factura,
                    f.total,
                    DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') as fecha_factura,
                    c.nombre AS cliente,
                    c.direccion
                FROM factura f
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE f.estado_factura_id = 1
                AND f.fecha_emision >= '2025-08-01'
                AND c.nombre LIKE ?
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
                )
                ORDER BY f.fecha_emision DESC, f.numero_factura DESC
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
                    f.numero_factura,
                    f.total,
                    c.nombre AS cliente
                FROM factura f
                INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE f.estado_factura_id = 1
                AND f.fecha_emision >= '2025-08-01'
                AND f.numero_factura LIKE ?
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
                )
                ORDER BY f.numero_factura DESC
                LIMIT 20
            ", ["%{$termino}%"]);

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
                     AND f2.fecha_emision >= '2025-08-01'
                     AND f2.estado_factura_id = 1
                     AND NOT EXISTS (
                         SELECT 1 FROM distribuciones_entrega_facturas def
                         WHERE def.factura_id = f2.id
                         AND def.estado_entrega = 'entregado'
                     )) as facturas_disponibles
                FROM cliente c
                INNER JOIN factura f ON c.id = f.cliente_id
                WHERE f.estado_factura_id = 1
                AND f.fecha_emision >= '2025-08-01'
                AND c.nombre LIKE ?
                AND EXISTS (
                    SELECT 1 FROM factura f2
                    WHERE f2.cliente_id = c.id
                    AND f2.fecha_emision >= '2025-08-01'
                    AND f2.estado_factura_id = 1
                    AND NOT EXISTS (
                        SELECT 1 FROM distribuciones_entrega_facturas def
                        WHERE def.factura_id = f2.id
                        AND def.estado_entrega = 'entregado'
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
                    f.numero_factura,
                    f.total,
                    DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') as fecha_factura,
                    (SELECT COUNT(*) FROM venta_has_producto vhp WHERE vhp.factura_id = f.id) as cantidad_productos
                FROM factura f
                WHERE f.cliente_id = ?
                AND f.estado_factura_id = 1
                AND f.fecha_emision >= '2025-08-01'
                AND NOT EXISTS (
                    SELECT 1 FROM distribuciones_entrega_facturas def
                    WHERE def.factura_id = f.id
                    AND def.estado_entrega = 'entregado'
                )
                ORDER BY f.fecha_emision DESC, f.numero_factura DESC
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
                    f.numero_factura,
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
                    vhp.id,
                    vhp.cantidad,
                    vhp.precio_unidad as precio_unitario,
                    vhp.sub_total_s as subtotal,
                    0 as descuento,
                    vhp.isv_s as impuesto,
                    vhp.total_s as total,
                    p.nombre as producto,
                    p.codigo
                FROM venta_has_producto vhp
                INNER JOIN producto p ON vhp.producto_id = p.id
                WHERE vhp.factura_id = ?
                ORDER BY vhp.id
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
    public function iniciarDistribucion($distribucionId)
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

            $distribucion->estado_id = 2; // En proceso
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
     * Completar distribución manualmente
     */
    public function completarDistribucion($distribucionId)
    {
        try {
            $distribucion = ModelDistribucionEntrega::findOrFail($distribucionId);
            
            if ($distribucion->estado_id != 2) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Estado inválido',
                    'text' => 'Solo se pueden completar distribuciones en proceso',
                ], 422);
            }

            $distribucion->estado_id = 3; // Completada
            $distribucion->save();

            return response()->json([
                'icon' => 'success',
                'title' => 'Distribución completada',
                'text' => 'La distribución ha sido marcada como completada',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al completar distribución: ' . $e->getMessage(),
            ], 500);
        }
    }
}
