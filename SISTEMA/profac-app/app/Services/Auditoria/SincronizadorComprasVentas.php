<?php

namespace App\Services\Auditoria;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SincronizadorComprasVentas
{
    public function sincronizar(bool $completa = false): array
    {
        $enCurso = DB::table('auditoria_compras_ventas_sync')
            ->where('estado', 'EN_CURSO')
            ->where('iniciado_at', '>=', now()->subHours(2))
            ->exists();

        if ($enCurso) {
            throw new RuntimeException('Ya existe una sincronización de auditoría en curso.');
        }

        $desde = $completa ? null : $this->ultimaSincronizacionExitosa();
        $inicio = CarbonImmutable::now();
        $syncId = DB::table('auditoria_compras_ventas_sync')->insertGetId([
            'estado' => 'EN_CURSO',
            'modo' => $completa ? 'COMPLETA' : 'INCREMENTAL',
            'iniciado_at' => $inicio,
            'created_at' => $inicio,
            'updated_at' => $inicio,
        ]);

        try {
            $comprasDerivadasEliminadas = $this->eliminarComprasDerivadasDeTraslados();
            $compras = $this->sincronizarCompras($desde, $inicio);
            $ventas = $this->sincronizarVentas($desde, $inicio);

            DB::table('auditoria_compras_ventas_sync')->where('id', $syncId)->update([
                'estado' => 'COMPLETADA',
                'finalizado_at' => now(),
                'filas_compras' => $compras,
                'filas_ventas' => $ventas,
                'updated_at' => now(),
            ]);

            return [
                'modo' => $completa ? 'COMPLETA' : 'INCREMENTAL',
                'compras' => $compras,
                'compras_derivadas_eliminadas' => $comprasDerivadasEliminadas,
                'ventas' => $ventas,
            ];
        } catch (Throwable $error) {
            DB::table('auditoria_compras_ventas_sync')->where('id', $syncId)->update([
                'estado' => 'FALLIDA',
                'finalizado_at' => now(),
                'error' => mb_substr($error->getMessage(), 0, 65000),
                'updated_at' => now(),
            ]);

            throw $error;
        }
    }

    private function ultimaSincronizacionExitosa(): ?CarbonImmutable
    {
        $fecha = DB::table('auditoria_compras_ventas_sync')
            ->where('estado', 'COMPLETADA')
            ->max('iniciado_at');

        return $fecha ? CarbonImmutable::parse($fecha)->subMinute() : null;
    }

    private function sincronizarCompras(?CarbonImmutable $desde, CarbonImmutable $inicio): int
    {
        $candidatos = DB::table('recibido_bodega as rb')
            ->join('compra as c', 'c.id', '=', 'rb.compra_id')
            ->join('compra_has_producto as chp', function ($join) {
                $join->on('chp.compra_id', '=', 'rb.compra_id')
                    ->on('chp.producto_id', '=', 'rb.producto_id');
            })
            ->whereNotNull('rb.compra_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('log_translado as lt')
                    ->whereColumn('lt.destino', 'rb.id')
                    ->whereNotNull('lt.translado_id');
            });
        if ($desde) {
            $candidatos->where(function ($query) use ($desde) {
                $query->where('c.updated_at', '>=', $desde)
                    ->orWhere('rb.updated_at', '>=', $desde)
                    ->orWhere('chp.updated_at', '>=', $desde);
            });
        }
        $totalCandidatos = $candidatos->count();

        $filtro = $desde
            ? 'AND (c.updated_at >= ? OR rb.updated_at >= ? OR chp.updated_at >= ?)'
            : '';
        $bindings = [$inicio, $inicio, $inicio];
        if ($desde) {
            array_push($bindings, $desde, $desde, $desde);
        }

        DB::affectingStatement("
            INSERT INTO auditoria_compras_ventas (
                tipo_movimiento, origen_id, clave_origen, fecha_movimiento,
                fecha_ingreso_compra, fecha_creacion_factura, fecha_emision_factura,
                factura_id, compra_id, numero_documento, cai, clasificacion_factura,
                estado_documento, producto_id, producto_nombre, lote_id,
                bodega_id, bodega_nombre, seccion_id, seccion_nombre,
                unidad_compra_id, unidad_compra_nombre, factor_unidad_compra,
                unidad_venta_id, unidad_venta_nombre, factor_unidad_venta,
                cantidad_documento, cantidad_base, cantidad_movimiento_base,
                cliente_id, cliente_nombre, proveedor_id, proveedor_nombre,
                usuario_id, usuario_nombre, sincronizado_at, created_at, updated_at
            )
            SELECT
                'COMPRA', rb.id, CONCAT('RECIBIDO_BODEGA:', rb.id),
                COALESCE(rb.created_at, CONCAT(rb.fecha_recibido, ' 00:00:00'), c.created_at),
                rb.fecha_recibido, NULL, NULL,
                NULL, c.id, c.numero_factura, c.codigo_cai, NULL,
                CASE WHEN c.estado_compra_id = 1 THEN 'ACTIVO' ELSE 'ANULADO' END,
                rb.producto_id, p.nombre, rb.id,
                b.id, b.nombre, s.id, s.descripcion,
                rb.unidad_compra_id, uc.nombre, rb.unidades_compra,
                NULL, NULL, NULL,
                rb.cantidad_compra_lote, rb.cantidad_inicial_seccion, rb.cantidad_inicial_seccion,
                NULL, NULL, c.proveedores_id, pr.nombre,
                c.users_id, u.name, ?, ?, ?
            FROM recibido_bodega rb
            INNER JOIN compra c ON c.id = rb.compra_id
            INNER JOIN compra_has_producto chp
                ON chp.compra_id = rb.compra_id AND chp.producto_id = rb.producto_id
            INNER JOIN producto p ON p.id = rb.producto_id
            LEFT JOIN unidad_medida uc ON uc.id = rb.unidad_compra_id
            LEFT JOIN seccion s ON s.id = rb.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN proveedores pr ON pr.id = c.proveedores_id
            LEFT JOIN users u ON u.id = c.users_id
            WHERE rb.compra_id IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1
                  FROM log_translado lt
                  WHERE lt.destino = rb.id
                    AND lt.translado_id IS NOT NULL
              )
            $filtro
            ON DUPLICATE KEY UPDATE
                fecha_movimiento = VALUES(fecha_movimiento),
                fecha_ingreso_compra = VALUES(fecha_ingreso_compra),
                compra_id = VALUES(compra_id),
                numero_documento = VALUES(numero_documento),
                cai = VALUES(cai),
                estado_documento = VALUES(estado_documento),
                producto_id = VALUES(producto_id),
                producto_nombre = VALUES(producto_nombre),
                lote_id = VALUES(lote_id),
                bodega_id = VALUES(bodega_id),
                bodega_nombre = VALUES(bodega_nombre),
                seccion_id = VALUES(seccion_id),
                seccion_nombre = VALUES(seccion_nombre),
                unidad_compra_id = VALUES(unidad_compra_id),
                unidad_compra_nombre = VALUES(unidad_compra_nombre),
                factor_unidad_compra = VALUES(factor_unidad_compra),
                cantidad_documento = VALUES(cantidad_documento),
                cantidad_base = VALUES(cantidad_base),
                cantidad_movimiento_base = VALUES(cantidad_movimiento_base),
                proveedor_id = VALUES(proveedor_id),
                proveedor_nombre = VALUES(proveedor_nombre),
                usuario_id = VALUES(usuario_id),
                usuario_nombre = VALUES(usuario_nombre),
                sincronizado_at = VALUES(sincronizado_at),
                updated_at = VALUES(updated_at)
        ", $bindings);

        return $totalCandidatos;
    }

    private function eliminarComprasDerivadasDeTraslados(): int
    {
        return DB::affectingStatement("
            DELETE a
            FROM auditoria_compras_ventas a
            INNER JOIN log_translado lt ON lt.destino = a.origen_id
            WHERE a.tipo_movimiento = 'COMPRA'
              AND lt.translado_id IS NOT NULL
        ");
    }

    private function sincronizarVentas(?CarbonImmutable $desde, CarbonImmutable $inicio): int
    {
        $candidatos = DB::table('venta_has_producto as v')
            ->join('factura as f', 'f.id', '=', 'v.factura_id');
        if ($desde) {
            $candidatos->where(function ($query) use ($desde) {
                $query->where('f.updated_at', '>=', $desde)
                    ->orWhere('v.updated_at', '>=', $desde);
            });
        }
        $totalCandidatos = $candidatos->count();

        $filtro = $desde
            ? 'WHERE (f.updated_at >= ? OR v.updated_at >= ?)'
            : '';
        $bindings = [$inicio, $inicio, $inicio];
        if ($desde) {
            array_push($bindings, $desde, $desde);
        }

        DB::affectingStatement("
            INSERT INTO auditoria_compras_ventas (
                tipo_movimiento, origen_id, clave_origen, fecha_movimiento,
                fecha_ingreso_compra, fecha_creacion_factura, fecha_emision_factura,
                factura_id, compra_id, numero_documento, cai, clasificacion_factura,
                estado_documento, producto_id, producto_nombre, lote_id,
                bodega_id, bodega_nombre, seccion_id, seccion_nombre,
                unidad_compra_id, unidad_compra_nombre, factor_unidad_compra,
                unidad_venta_id, unidad_venta_nombre, factor_unidad_venta,
                cantidad_documento, cantidad_base, cantidad_movimiento_base,
                cliente_id, cliente_nombre, proveedor_id, proveedor_nombre,
                usuario_id, usuario_nombre, sincronizado_at, created_at, updated_at
            )
            SELECT
                'VENTA',
                NULL,
                CONCAT('VENTA:', v.factura_id, ':', v.producto_id, ':', v.lote),
                COALESCE(CONCAT(f.fecha_emision, ' 00:00:00'), f.created_at, v.created_at),
                NULL, f.created_at, f.fecha_emision,
                f.id, NULL, COALESCE(f.numero_factura, f.cai), f.cai,
                CASE f.estado_factura_id WHEN 1 THEN 'A' WHEN 2 THEN 'B' ELSE NULL END,
                CASE WHEN f.estado_venta_id = 2 THEN 'ANULADO' ELSE 'ACTIVO' END,
                v.producto_id, p.nombre, v.lote,
                b.id, b.nombre, s.id, s.descripcion,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra,
                v.unidad_medida_venta_id, uv.nombre, umv.unidad_venta,
                v.cantidad_s, v.numero_unidades_resta_inventario,
                -1 * v.numero_unidades_resta_inventario,
                f.cliente_id, COALESCE(cl.nombre, f.nombre_cliente), NULL, NULL,
                f.users_id, u.name, ?, ?, ?
            FROM venta_has_producto v
            INNER JOIN factura f ON f.id = v.factura_id
            INNER JOIN producto p ON p.id = v.producto_id
            LEFT JOIN recibido_bodega rb ON rb.id = v.lote
            LEFT JOIN seccion s ON s.id = COALESCE(rb.seccion_id, v.seccion_id)
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN unidad_medida uc ON uc.id = p.unidad_medida_compra_id
            LEFT JOIN unidad_medida_venta umv ON umv.id = v.unidad_medida_venta_id
            LEFT JOIN unidad_medida uv ON uv.id = umv.unidad_medida_id
            LEFT JOIN cliente cl ON cl.id = f.cliente_id
            LEFT JOIN users u ON u.id = f.users_id
            $filtro
            ON DUPLICATE KEY UPDATE
                fecha_movimiento = VALUES(fecha_movimiento),
                fecha_creacion_factura = VALUES(fecha_creacion_factura),
                fecha_emision_factura = VALUES(fecha_emision_factura),
                factura_id = VALUES(factura_id),
                numero_documento = VALUES(numero_documento),
                cai = VALUES(cai),
                clasificacion_factura = VALUES(clasificacion_factura),
                estado_documento = VALUES(estado_documento),
                producto_id = VALUES(producto_id),
                producto_nombre = VALUES(producto_nombre),
                lote_id = VALUES(lote_id),
                bodega_id = VALUES(bodega_id),
                bodega_nombre = VALUES(bodega_nombre),
                seccion_id = VALUES(seccion_id),
                seccion_nombre = VALUES(seccion_nombre),
                unidad_compra_id = VALUES(unidad_compra_id),
                unidad_compra_nombre = VALUES(unidad_compra_nombre),
                factor_unidad_compra = VALUES(factor_unidad_compra),
                unidad_venta_id = VALUES(unidad_venta_id),
                unidad_venta_nombre = VALUES(unidad_venta_nombre),
                factor_unidad_venta = VALUES(factor_unidad_venta),
                cantidad_documento = VALUES(cantidad_documento),
                cantidad_base = VALUES(cantidad_base),
                cantidad_movimiento_base = VALUES(cantidad_movimiento_base),
                cliente_id = VALUES(cliente_id),
                cliente_nombre = VALUES(cliente_nombre),
                usuario_id = VALUES(usuario_id),
                usuario_nombre = VALUES(usuario_nombre),
                sincronizado_at = VALUES(sincronizado_at),
                updated_at = VALUES(updated_at)
        ", $bindings);

        return $totalCandidatos;
    }
}
