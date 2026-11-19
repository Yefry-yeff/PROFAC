<?php

namespace App\Services\Auditoria;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ConsultaComprasVentas
{
    public const PRODUCTOS_AUDITADOS = [5830, 2794];

    public function construir(array $filtros): Builder
    {
        $query = DB::table('auditoria_compras_ventas as a')
            ->where(function (Builder $alcance) {
                $alcance->where(function (Builder $compras) {
                    $compras->where('a.tipo_movimiento', 'COMPRA')
                        ->whereIn('a.producto_id', self::PRODUCTOS_AUDITADOS);
                })->orWhere(function (Builder $ventas) {
                    $ventas->where('a.tipo_movimiento', 'VENTA')
                        ->whereExists(function ($facturaConProducto) {
                            $facturaConProducto->selectRaw('1')
                                ->from('auditoria_compras_ventas as objetivo')
                                ->whereColumn('objetivo.factura_id', 'a.factura_id')
                                ->where('objetivo.tipo_movimiento', 'VENTA')
                                ->whereIn('objetivo.producto_id', self::PRODUCTOS_AUDITADOS);
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

    private function fecha(?string $fecha): ?string
    {
        if (!$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return null;
        }

        return $fecha;
    }
}
