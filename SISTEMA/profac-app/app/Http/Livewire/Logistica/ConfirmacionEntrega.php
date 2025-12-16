<?php

namespace App\Http\Livewire\Logistica;

use Livewire\Component;
use App\Models\Logistica\DistribucionEntrega as ModelDistribucionEntrega;
use App\Models\Logistica\DistribucionEntregaFactura;
use App\Models\Logistica\EntregaProducto;
use App\Models\Logistica\EntregaProductoIncidencia;
use App\Models\Logistica\EntregaEvidencia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConfirmacionEntrega extends Component
{
    public function render()
    {
        return view('livewire.logistica.confirmacion-entrega');
    }

    /**
     * Listar distribuciones en proceso por fecha
     */
    public function listarDistribucionesPorFecha(Request $request)
    {
        try {
            $fecha = $request->input('fecha', date('Y-m-d'));
            
            $distribuciones = DB::select("
                SELECT 
                    d.id,
                    d.fecha_programada,
                    d.observaciones,
                    e.nombre_equipo,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id) as total_facturas,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'entregado') as facturas_entregadas
                FROM distribuciones_entrega d
                INNER JOIN equipos_entrega e ON d.equipo_entrega_id = e.id
                WHERE d.estado_id = 2
                AND DATE(d.fecha_programada) = ?
                ORDER BY d.id ASC
            ", [$fecha]);

            return response()->json([
                'success' => true,
                'distribuciones' => $distribuciones
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar distribuciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener facturas con productos para confirmación
     */
    public function obtenerFacturasParaConfirmacion($distribucionId)
    {
        try {
            $facturas = DB::select("
                SELECT 
                    df.id as distribucion_factura_id,
                    df.factura_id,
                    df.orden_entrega,
                    df.estado_entrega,
                    f.numero_factura,
                    f.total,
                    c.nombre AS cliente,
                    c.direccion,
                       c.telefono_empresa
                FROM distribuciones_entrega_facturas df
                   INNER JOIN factura f ON df.factura_id = f.id
                   INNER JOIN cliente c ON f.cliente_id = c.id
                WHERE df.distribucion_entrega_id = ?
                ORDER BY df.orden_entrega ASC
            ", [$distribucionId]);

            // Para cada factura, obtener sus productos
            foreach ($facturas as &$factura) {
                $factura->productos = $this->obtenerProductosFactura($factura->distribucion_factura_id);
            }

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
     * Obtener productos de una factura en distribución
     */
    private function obtenerProductosFactura($distribucionFacturaId)
    {
        // Primero verificar si ya existen registros de entrega
        $productosExistentes = DB::select("
            SELECT 
                ep.id,
                ep.producto_id,
                p.nombre AS nombre_producto,
                ep.cantidad_facturada,
                ep.cantidad_entregada,
                ep.entregado,
                ep.tiene_incidencia,
                ep.descripcion_incidencia,
                ep.tipo_incidencia,
                (
                    SELECT COUNT(*)
                    FROM entregas_productos_incidencias epi
                    WHERE epi.entrega_producto_id = ep.id
                ) AS incidencias_registradas
            FROM entregas_productos ep
            INNER JOIN producto p ON ep.producto_id = p.id
            WHERE ep.distribucion_factura_id = ?
            ORDER BY p.nombre ASC
        ", [$distribucionFacturaId]);

        if (!empty($productosExistentes)) {
            return $productosExistentes;
        }

        // Si no existen, crear registros iniciales desde los detalles de la factura
        $distribucionFactura = DistribucionEntregaFactura::findOrFail($distribucionFacturaId);
        
           $productosFactura = DB::select("
               SELECT 
                   vhp.producto_id,
                   p.nombre AS nombre_producto,
                   vhp.cantidad
               FROM venta_has_producto vhp
               INNER JOIN producto p ON vhp.producto_id = p.id
               WHERE vhp.factura_id = ?
               ORDER BY p.nombre ASC
           ", [$distribucionFactura->factura_id]);

        // Crear registros iniciales o reutilizar los existentes para evitar duplicados
        foreach ($productosFactura as $producto) {
            EntregaProducto::firstOrCreate(
                [
                    'distribucion_factura_id' => $distribucionFacturaId,
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

        // Retornar los productos recién creados
        return DB::select("
            SELECT 
                ep.id,
                ep.producto_id,
                p.nombre AS nombre_producto,
                ep.cantidad_facturada,
                ep.cantidad_entregada,
                ep.entregado,
                ep.tiene_incidencia,
                ep.descripcion_incidencia,
                ep.tipo_incidencia,
                (
                    SELECT COUNT(*)
                    FROM entregas_productos_incidencias epi
                    WHERE epi.entrega_producto_id = ep.id
                ) AS incidencias_registradas
            FROM entregas_productos ep
            INNER JOIN producto p ON ep.producto_id = p.id
            WHERE ep.distribucion_factura_id = ?
            ORDER BY p.nombre ASC
        ", [$distribucionFacturaId]);
    }

    /**
     * Confirmar entrega de productos
     */
    public function confirmarEntregaProductos(Request $request)
    {
        try {
            $request->validate([
                'productos' => 'required|array|min:1',
                'productos.*.id' => 'required|exists:entregas_productos,id',
                'productos.*.entregado' => 'required|boolean',
                'productos.*.cantidad_entregada' => 'nullable|numeric|min:0',
                'productos.*.tiene_incidencia' => 'nullable|boolean',
                'productos.*.tipo_incidencia' => 'nullable|string',
                'productos.*.descripcion_incidencia' => 'nullable|string',
                'hora_entrega' => 'required|date_format:H:i',
            ]);

            $productosIds = collect($request->productos)->pluck('id')->unique()->values();

            $facturasCerradas = DB::table('entregas_productos as ep')
                ->join('distribuciones_entrega_facturas as df', 'ep.distribucion_factura_id', '=', 'df.id')
                ->join('factura as f', 'df.factura_id', '=', 'f.id')
                ->whereIn('ep.id', $productosIds)
                ->whereIn('df.estado_entrega', ['entregado', 'parcial'])
                ->select('f.numero_factura')
                ->distinct()
                ->pluck('numero_factura');

            if ($facturasCerradas->isNotEmpty()) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Factura cerrada',
                    'text' => 'La factura #' . $facturasCerradas->first() . ' ya fue confirmada y no puede editarse.',
                ], 422);
            }

            [$hora, $minuto] = explode(':', $request->hora_entrega);
            $fechaRegistro = Carbon::now()->setTime((int) $hora, (int) $minuto);

            DB::beginTransaction();

            foreach ($request->productos as $productoData) {
                $producto = EntregaProducto::findOrFail($productoData['id']);
                
                $producto->entregado = $productoData['entregado'];
                $producto->cantidad_entregada = $productoData['cantidad_entregada'] ?? 0;
                $producto->tiene_incidencia = $productoData['tiene_incidencia'] ?? 0;
                $producto->tipo_incidencia = $productoData['tipo_incidencia'] ?? null;
                $producto->descripcion_incidencia = $productoData['descripcion_incidencia'] ?? null;
                $producto->user_id_registro = Auth::id();
                $producto->fecha_registro = $fechaRegistro;
                $producto->save();
            }

            // Los triggers actualizarán automáticamente el estado_entrega de la factura

            DB::commit();

            return response()->json([
                'icon' => 'success',
                'title' => 'Confirmacion exitosa',
                'text' => 'Los productos han sido actualizados correctamente',
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al confirmar entrega: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registrar evidencia (foto, firma, etc.)
     */
    public function registrarEvidencia(Request $request)
    {
        try {
            $request->validate([
                'incidencia_id' => 'required|exists:entregas_productos_incidencias,id',
                'archivo' => 'required|file|max:10240', // 10MB máximo
            ]);

            $archivo = $request->file('archivo');
            $extension = $archivo->getClientOriginalExtension();
            $nombreArchivo = 'evidencia_' . time() . '_' . uniqid() . '.' . $extension;

            // Guardar directamente en public/incidencia_entrega
            $destinoPath = public_path('incidencia_entrega');
            if (!file_exists($destinoPath)) {
                mkdir($destinoPath, 0755, true);
            }
            
            $archivo->move($destinoPath, $nombreArchivo);
            $ruta = 'public/incidencia_entrega/' . $nombreArchivo;

            EntregaEvidencia::create([
                'entrega_producto_incidencia_id' => $request->incidencia_id,
                'ruta_archivo' => $ruta,
                'user_id_registro' => Auth::id(),
                'descripcion' => $request->input('descripcion', null),
            ]);

            return response()->json([
                'icon' => 'success',
                'title' => 'Evidencia guardada',
                'text' => 'La evidencia ha sido registrada correctamente',
                'ruta' => asset('incidencia_entrega/' . $nombreArchivo),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al guardar evidencia: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar incidencias de un producto entregado
     */
    public function listarIncidenciasProducto($productoId)
    {
        try {
            $producto = EntregaProducto::findOrFail($productoId);

            $incidencias = EntregaProductoIncidencia::where('entrega_producto_id', $productoId)
                ->orderByDesc('created_at')
                ->get(['id', 'tipo', 'descripcion', 'created_at']);

            return response()->json([
                'success' => true,
                'producto' => [
                    'id' => $producto->id,
                    'producto_id' => $producto->producto_id,
                ],
                'incidencias' => $incidencias,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener incidencias',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registrar una nueva incidencia para un producto
     */
    public function registrarIncidenciaProducto(Request $request, $productoId)
    {
        try {
            $producto = EntregaProducto::findOrFail($productoId);

            $request->validate([
                'tipo' => 'required|string|max:60',
                'descripcion' => 'required|string|min:5',
            ]);

            $incidencia = EntregaProductoIncidencia::create([
                'entrega_producto_id' => $productoId,
                'tipo' => $request->tipo,
                'descripcion' => $request->descripcion,
                'user_id_registro' => Auth::id(),
            ]);

            $producto->tiene_incidencia = 1;
            $producto->tipo_incidencia = $request->tipo;
            $producto->descripcion_incidencia = $request->descripcion;
            $producto->user_id_registro = Auth::id();
            $producto->save();

            $incidencias = EntregaProductoIncidencia::where('entrega_producto_id', $productoId)
                ->orderByDesc('created_at')
                ->get(['id', 'tipo', 'descripcion', 'created_at']);

            return response()->json([
                'icon' => 'success',
                'title' => 'Incidencia registrada',
                'text' => 'La incidencia se guardó correctamente.',
                'incidencia' => [
                    'id' => $incidencia->id,
                ],
                'incidencias' => $incidencias,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo registrar la incidencia: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener evidencias de una factura
     */
    public function obtenerEvidencias($distribucionFacturaId)
    {
        try {
            $evidencias = DB::select(
                "SELECT 
                    ee.id,
                    ee.ruta_archivo,
                    ee.created_at
                FROM entregas_evidencias ee
                INNER JOIN entregas_productos_incidencias epi ON ee.entrega_producto_incidencia_id = epi.id
                INNER JOIN entregas_productos ep ON epi.entrega_producto_id = ep.id
                WHERE ep.distribucion_factura_id = ?
                ORDER BY ee.created_at DESC",
                [$distribucionFacturaId]
            );

            foreach ($evidencias as &$evidencia) {
                // La ruta viene como "public/incidencia_entrega/archivo.jpg"
                $nombreArchivo = basename($evidencia->ruta_archivo);
                $evidencia->url = asset('incidencia_entrega/' . $nombreArchivo);
            }

            return response()->json([
                'success' => true,
                'evidencias' => $evidencias
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener evidencias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar todos los productos como entregados
     */
    public function marcarTodosEntregados($distribucionFacturaId)
    {
        try {
            $factura = DistribucionEntregaFactura::findOrFail($distribucionFacturaId);

            if ($factura->estado_entrega === 'entregado') {
                return response()->json([
                    'icon' => 'info',
                    'title' => 'Factura confirmada',
                    'text' => 'Esta factura ya se encuentra confirmada.',
                ], 422);
            }

            DB::table('entregas_productos')
                ->where('distribucion_factura_id', $distribucionFacturaId)
                ->where('tiene_incidencia', 0)
                ->update([
                    'entregado' => 1,
                    'cantidad_entregada' => DB::raw('cantidad_facturada'),
                    'user_id_registro' => Auth::id(),
                    'updated_at' => now(),
                ]);

            // El trigger actualizará el estado_entrega automáticamente

            return response()->json([
                'icon' => 'success',
                'title' => 'Productos marcados',
                'text' => 'Todos los productos han sido marcados como entregados',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al marcar productos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener reporte de entregas por distribución
     */
    public function obtenerReporteDistribucion($distribucionId)
    {
        try {
            $reporte = DB::select("
                SELECT 
                    d.id,
                    d.fecha_programada,
                    e.nombre_equipo,
                    d.observaciones,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id) as total_facturas,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'entregado') as facturas_entregadas,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'parcial') as facturas_parciales,
                    (SELECT COUNT(*) FROM distribuciones_entrega_facturas WHERE distribucion_entrega_id = d.id AND estado_entrega = 'sin_entrega') as facturas_sin_entrega,
                    (SELECT COUNT(*) 
                     FROM entregas_productos ep
                     INNER JOIN distribuciones_entrega_facturas df ON ep.distribucion_factura_id = df.id
                     WHERE df.distribucion_entrega_id = d.id AND ep.tiene_incidencia = 1
                    ) as total_incidencias
                FROM distribuciones_entrega d
                INNER JOIN equipos_entrega e ON d.equipo_entrega_id = e.id
                WHERE d.id = ?
            ", [$distribucionId]);

            return response()->json([
                'success' => true,
                'reporte' => $reporte[0] ?? null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
