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
        5802, 5824, 5803, 5821, 5817, 5822, 5818, 5815, 5804, 5808,
        5805, 5807, 5809, 5806, 5820, 5811, 5810, 5813, 5801, 1945,
        1956, 1959, 1952, 1962, 4097, 4022, 5108, 1940, 1950, 1957,
        1953, 2301, 4346, 1954, 1963, 1951, 1958, 1949, 4354, 4355,
        4357, 2326, 2325, 3080, 5678, 4591, 4136, 2794, 4347, 4662,
        5764, 4459, 5143,
    ];
    private const INICIO_FACTURAS_B_EXCLUIDAS = '2026-01-01';
    private const FIN_FACTURAS_B_EXCLUIDAS = '2027-01-01';

    public function construir(array $filtros): Builder
    {
        $query = DB::table('auditoria_compras_ventas as a')
            ->whereIn('a.producto_id', self::PRODUCTOS_AUDITADOS)
            ->whereIn('a.tipo_movimiento', ['COMPRA', 'VENTA'])
            ->whereNot(function (Builder $facturasB2026) {
                $facturasB2026
                    ->where('a.tipo_movimiento', 'VENTA')
                    ->where('a.clasificacion_factura', 'B')
                    ->where(function (Builder $fechas2026) {
                        $fechas2026
                            ->where(function (Builder $creacion2026) {
                                $creacion2026
                                    ->where('a.fecha_creacion_factura', '>=', self::INICIO_FACTURAS_B_EXCLUIDAS)
                                    ->where('a.fecha_creacion_factura', '<', self::FIN_FACTURAS_B_EXCLUIDAS);
                            })
                            ->orWhere(function (Builder $emision2026) {
                                $emision2026
                                    ->where('a.fecha_emision_factura', '>=', self::INICIO_FACTURAS_B_EXCLUIDAS)
                                    ->where('a.fecha_emision_factura', '<', self::FIN_FACTURAS_B_EXCLUIDAS);
                            });
                    });
            });

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
                        ->orWhere('a.compra_id', (int) $documento);
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
            'tipo' => in_array($entrada['tipo'] ?? null, ['COMPRA', 'VENTA'], true)
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
            ->orderByRaw("CASE WHEN a.tipo_movimiento = 'COMPRA' THEN 0 ELSE 1 END")
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
