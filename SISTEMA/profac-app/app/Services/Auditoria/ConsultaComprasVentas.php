<?php

namespace App\Services\Auditoria;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ConsultaComprasVentas
{
    public const PRODUCTOS_AUDITADOS = [
        7498, 7505, 7510, 7517, 7494, 7501, 7515, 7508, 5591, 7512,
        7518, 3685, 4138, 2795, 4139, 8069, 5830, 1947, 1948, 1946,
        4030, 7927, 2527, 3577, 3578, 5823, 5812, 5816, 5814, 5919,
        5802, 1960, 5824, 5803, 5821, 5817, 5822, 5818, 5815, 5804, 5808,
        5805, 5807, 5809, 5806, 5820, 5811, 5810, 5813, 5801, 1945,
        1956, 1959, 1952, 1962, 4097, 4022, 5108, 1940, 1950, 1957,
        1953, 2301, 4346, 1954, 1963, 1951, 1958, 1949, 4354, 4355,
        4357, 2326, 2325, 3080, 5678, 4591, 4136, 2794, 4347, 4662,
        5764, 4459, 5143,
    ];
    public function construir(array $filtros): Builder
    {
        $base = DB::table('auditoria_compras_ventas as a')
            ->whereIn('a.producto_id', self::PRODUCTOS_AUDITADOS)
            ->whereIn('a.tipo_movimiento', [
                'COMPRA',
                'VENTA',
                'COMPROBANTE',
                'AJUSTE',
                'ANULACION_AJUSTE',
            ]);

        $compras = (clone $base)
            ->where('a.tipo_movimiento', 'COMPRA')
            ->groupBy('a.compra_id', 'a.producto_id')
            ->selectRaw("
                MIN(a.id) AS id,
                'COMPRA' AS tipo_movimiento,
                NULL AS origen_id,
                CONCAT('COMPRA:', a.compra_id, ':', a.producto_id) AS clave_origen,
                MIN(a.fecha_movimiento) AS fecha_movimiento,
                MIN(a.fecha_ingreso_compra) AS fecha_ingreso_compra,
                NULL AS fecha_creacion_factura,
                NULL AS fecha_emision_factura,
                NULL AS factura_id,
                a.compra_id,
                NULL AS comprobante_id,
                NULL AS ajuste_id,
                MAX(a.numero_documento) AS numero_documento,
                MAX(a.cai) AS cai,
                NULL AS clasificacion_factura,
                MAX(a.estado_documento) AS estado_documento,
                NULL AS motivo_documento,
                NULL AS comentario_documento,
                a.producto_id,
                MAX(a.producto_nombre) AS producto_nombre,
                NULL AS lote_id,
                NULL AS bodega_id,
                CASE
                    WHEN COUNT(DISTINCT a.bodega_id) = 1 THEN MAX(a.bodega_nombre)
                    WHEN COUNT(DISTINCT a.bodega_id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END AS bodega_nombre,
                NULL AS seccion_id,
                CASE
                    WHEN COUNT(DISTINCT a.seccion_id) = 1 THEN MAX(a.seccion_nombre)
                    WHEN COUNT(DISTINCT a.seccion_id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END AS seccion_nombre,
                MAX(a.unidad_compra_id) AS unidad_compra_id,
                MAX(a.unidad_compra_nombre) AS unidad_compra_nombre,
                MAX(a.factor_unidad_compra) AS factor_unidad_compra,
                NULL AS unidad_venta_id,
                NULL AS unidad_venta_nombre,
                NULL AS factor_unidad_venta,
                MAX(a.cantidad_documento) AS cantidad_documento,
                SUM(a.cantidad_base) AS cantidad_base,
                SUM(a.cantidad_movimiento_base) AS cantidad_movimiento_base,
                MAX(a.afecta_inventario) AS afecta_inventario,
                NULL AS cliente_id,
                NULL AS cliente_nombre,
                MAX(a.proveedor_id) AS proveedor_id,
                MAX(a.proveedor_nombre) AS proveedor_nombre,
                MAX(a.usuario_id) AS usuario_id,
                MAX(a.usuario_nombre) AS usuario_nombre,
                MAX(a.sincronizado_at) AS sincronizado_at,
                MIN(a.created_at) AS created_at,
                MAX(a.updated_at) AS updated_at
            ");

        $otrosMovimientos = (clone $base)
            ->where('a.tipo_movimiento', '<>', 'COMPRA')
            ->select('a.*');

        $query = DB::query()->fromSub($compras->unionAll($otrosMovimientos), 'a');

        if (!empty($filtros['desde'])) {
            $query->whereDate('a.fecha_movimiento', '>=', $filtros['desde']);
        }
        if (!empty($filtros['hasta'])) {
            $query->whereDate('a.fecha_movimiento', '<=', $filtros['hasta']);
        }
        if (!empty($filtros['tipo'])) {
            $query->where('a.tipo_movimiento', $filtros['tipo']);
        }
        if (!empty($filtros['clasificacion'])) {
            $query->where('a.clasificacion_factura', $filtros['clasificacion']);
        }
        if (!empty($filtros['estado'])) {
            $query->where('a.estado_documento', $filtros['estado']);
        }
        if (!empty($filtros['producto'])) {
            $producto = trim($filtros['producto']);
            $query->where(function (Builder $subquery) use ($producto) {
                $subquery->where('a.producto_nombre', 'like', "%{$producto}%");
                if (ctype_digit($producto)) {
                    $subquery->orWhere('a.producto_id', (int) $producto);
                }
            });
        }
        if (!empty($filtros['documento'])) {
            $documento = trim($filtros['documento']);
            $query->where(function (Builder $subquery) use ($documento) {
                $subquery->where('a.numero_documento', 'like', "%{$documento}%")
                    ->orWhere('a.cai', 'like', "%{$documento}%");
                if (ctype_digit($documento)) {
                    $subquery->orWhere('a.factura_id', (int) $documento)
                        ->orWhere('a.compra_id', (int) $documento)
                        ->orWhere('a.comprobante_id', (int) $documento)
                        ->orWhere('a.ajuste_id', (int) $documento);
                }
            });
        }
        if (!empty($filtros['lote']) && ctype_digit((string) $filtros['lote'])) {
            $query->where('a.lote_id', (int) $filtros['lote']);
        }

        return $query;
    }

    public function filtros(array $entrada): array
    {
        return [
            'desde' => $this->fecha($entrada['desde'] ?? null),
            'hasta' => $this->fecha($entrada['hasta'] ?? null),
            'tipo' => in_array($entrada['tipo'] ?? null, [
                'COMPRA',
                'VENTA',
                'COMPROBANTE',
                'AJUSTE',
                'ANULACION_AJUSTE',
            ], true)
                ? $entrada['tipo']
                : null,
            'clasificacion' => in_array($entrada['clasificacion'] ?? null, ['A', 'B'], true)
                ? $entrada['clasificacion']
                : null,
            'estado' => in_array($entrada['estado'] ?? null, ['ACTIVO', 'ANULADO'], true)
                ? $entrada['estado']
                : null,
            'producto' => mb_substr(trim((string) ($entrada['producto'] ?? '')), 0, 250),
            'documento' => mb_substr(trim((string) ($entrada['documento'] ?? '')), 0, 100),
            'lote' => mb_substr(trim((string) ($entrada['lote'] ?? '')), 0, 20),
        ];
    }

    public function ordenarCronologicamente(Builder $query): Builder
    {
        return $query
            ->orderBy('a.fecha_movimiento')
            ->orderByRaw("
                CASE a.tipo_movimiento
                    WHEN 'COMPRA' THEN 0
                    WHEN 'COMPROBANTE' THEN 1
                    WHEN 'VENTA' THEN 2
                    WHEN 'AJUSTE' THEN 3
                    WHEN 'ANULACION_AJUSTE' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('a.numero_documento')
            ->orderBy('a.producto_id')
            ->orderBy('a.lote_id')
            ->orderBy('a.id');
    }

    private function fecha(?string $fecha): ?string
    {
        if (!$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return null;
        }

        return $fecha;
    }
}
