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
            $comprobantes = $this->sincronizarComprobantes($desde, $inicio);
            $ajustes = $this->sincronizarAjustes($desde, $inicio);
            $anulacionesAjustes = $this->sincronizarAnulacionesAjustes($desde, $inicio);
            $desdeNotasCredito = DB::table('auditoria_compras_ventas')
                ->whereIn('tipo_movimiento', ['NOTA_CREDITO', 'ANULACION_NOTA_CREDITO'])
                ->exists()
                    ? $desde
                    : null;
            $notasCredito = $this->sincronizarNotasCredito($desdeNotasCredito, $inicio);
            $anulacionesNotasCredito = $this->sincronizarAnulacionesNotasCredito($desdeNotasCredito, $inicio);

            DB::table('auditoria_compras_ventas_sync')->where('id', $syncId)->update([
                'estado' => 'COMPLETADA',
                'finalizado_at' => now(),
                'filas_compras' => $compras,
                'filas_ventas' => $ventas,
                'filas_comprobantes' => $comprobantes,
                'filas_ajustes' => $ajustes + $anulacionesAjustes,
                'filas_notas_credito' => $notasCredito + $anulacionesNotasCredito,
                'updated_at' => now(),
            ]);

            return [
                'modo' => $completa ? 'COMPLETA' : 'INCREMENTAL',
                'compras' => $compras,
                'compras_derivadas_eliminadas' => $comprasDerivadasEliminadas,
                'ventas' => $ventas,
                'comprobantes' => $comprobantes,
                'ajustes' => $ajustes,
                'anulaciones_ajustes' => $anulacionesAjustes,
                'notas_credito' => $notasCredito,
                'anulaciones_notas_credito' => $anulacionesNotasCredito,
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
                CASE f.tipo_venta_id WHEN 1 THEN 'B' WHEN 2 THEN 'A' ELSE NULL END,
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

    private function sincronizarComprobantes(?CarbonImmutable $desde, CarbonImmutable $inicio): int
    {
        $candidatos = DB::table('comprovante_has_producto as chp')
            ->join('comprovante_entrega as ce', 'ce.id', '=', 'chp.comprovante_id');
        if ($desde) {
            $candidatos->where(function ($query) use ($desde) {
                $query->where('ce.updated_at', '>=', $desde)
                    ->orWhere('chp.updated_at', '>=', $desde);
            });
        }
        $totalCandidatos = $candidatos
            ->distinct()
            ->count(DB::raw("CONCAT(chp.comprovante_id, ':', chp.producto_id)"));

        $filtro = $desde
            ? 'WHERE (ce.updated_at >= ? OR chp.updated_at >= ?)'
            : '';
        $bindings = [$inicio, $inicio, $inicio];
        if ($desde) {
            array_push($bindings, $desde, $desde);
        }

        DB::affectingStatement("
            INSERT INTO auditoria_compras_ventas (
                tipo_movimiento, origen_id, clave_origen, fecha_movimiento,
                fecha_ingreso_compra, fecha_creacion_factura, fecha_emision_factura,
                factura_id, compra_id, comprobante_id, ajuste_id,
                numero_documento, cai, clasificacion_factura,
                estado_documento, motivo_documento, comentario_documento,
                producto_id, producto_nombre, lote_id,
                bodega_id, bodega_nombre, seccion_id, seccion_nombre,
                unidad_compra_id, unidad_compra_nombre, factor_unidad_compra,
                unidad_venta_id, unidad_venta_nombre, factor_unidad_venta,
                cantidad_documento, cantidad_base, cantidad_movimiento_base,
                afecta_inventario, cliente_id, cliente_nombre,
                proveedor_id, proveedor_nombre, usuario_id, usuario_nombre,
                sincronizado_at, created_at, updated_at
            )
            SELECT
                'COMPROBANTE', ce.id,
                CONCAT('COMPROBANTE:', ce.id, ':', chp.producto_id),
                COALESCE(ce.created_at, CONCAT(ce.fecha_emision, ' 00:00:00')),
                NULL, NULL, NULL,
                NULL, NULL, ce.id, NULL,
                ce.numero_comprovante, NULL, NULL,
                CASE WHEN ce.estado_id = 2 THEN 'ANULADO' ELSE 'ACTIVO' END,
                NULL,
                CASE
                    WHEN ce.estado_id = 2 AND ce.comentarioAnulado IS NOT NULL
                        THEN CONCAT(ce.comentario, ' | ', ce.comentarioAnulado)
                    ELSE ce.comentario
                END,
                chp.producto_id, p.nombre, NULL,
                NULL,
                CASE
                    WHEN COUNT(DISTINCT b.id) = 1 THEN MAX(b.nombre)
                    WHEN COUNT(DISTINCT b.id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END,
                NULL,
                CASE
                    WHEN COUNT(DISTINCT s.id) = 1 THEN MAX(s.descripcion)
                    WHEN COUNT(DISTINCT s.id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra,
                MAX(chp.unidad_medida_venta_id), MAX(uv.nombre), MAX(umv.unidad_venta),
                SUM(chp.cantidad_s),
                SUM(chp.numero_unidades_resta_inventario),
                0,
                0,
                ce.cliente_id, COALESCE(cl.nombre, ce.nombre_cliente),
                NULL, NULL, ce.users_id, u.name,
                ?, ?, ?
            FROM comprovante_has_producto chp
            INNER JOIN comprovante_entrega ce ON ce.id = chp.comprovante_id
            INNER JOIN producto p ON p.id = chp.producto_id
            LEFT JOIN seccion s ON s.id = chp.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN unidad_medida uc ON uc.id = p.unidad_medida_compra_id
            LEFT JOIN unidad_medida_venta umv ON umv.id = chp.unidad_medida_venta_id
            LEFT JOIN unidad_medida uv ON uv.id = umv.unidad_medida_id
            LEFT JOIN cliente cl ON cl.id = ce.cliente_id
            LEFT JOIN users u ON u.id = ce.users_id
            $filtro
            GROUP BY
                ce.id, ce.created_at, ce.fecha_emision, ce.numero_comprovante,
                ce.estado_id, ce.comentario, ce.comentarioAnulado,
                ce.cliente_id, cl.nombre, ce.nombre_cliente, ce.users_id, u.name,
                chp.producto_id, p.nombre, p.unidad_medida_compra_id,
                uc.nombre, p.unidadad_compra
            ON DUPLICATE KEY UPDATE
                fecha_movimiento = VALUES(fecha_movimiento),
                comprobante_id = VALUES(comprobante_id),
                numero_documento = VALUES(numero_documento),
                estado_documento = VALUES(estado_documento),
                comentario_documento = VALUES(comentario_documento),
                producto_nombre = VALUES(producto_nombre),
                bodega_nombre = VALUES(bodega_nombre),
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
                afecta_inventario = VALUES(afecta_inventario),
                cliente_id = VALUES(cliente_id),
                cliente_nombre = VALUES(cliente_nombre),
                usuario_id = VALUES(usuario_id),
                usuario_nombre = VALUES(usuario_nombre),
                sincronizado_at = VALUES(sincronizado_at),
                updated_at = VALUES(updated_at)
        ", $bindings);

        return $totalCandidatos;
    }

    private function sincronizarAjustes(?CarbonImmutable $desde, CarbonImmutable $inicio): int
    {
        $candidatos = DB::table('ajuste_has_producto as ahp')
            ->join('ajuste as a', 'a.id', '=', 'ahp.ajuste_id');
        if ($desde) {
            $candidatos->where(function ($query) use ($desde) {
                $query->where('a.updated_at', '>=', $desde)
                    ->orWhere('ahp.updated_at', '>=', $desde);
            });
        }
        $totalCandidatos = $candidatos
            ->distinct()
            ->count(DB::raw("CONCAT(ahp.ajuste_id, ':', ahp.producto_id, ':', ahp.tipo_aritmetica)"));

        $filtro = $desde
            ? 'WHERE (a.updated_at >= ? OR ahp.updated_at >= ?)'
            : '';
        $bindings = [$inicio, $inicio, $inicio];
        if ($desde) {
            array_push($bindings, $desde, $desde);
        }

        DB::affectingStatement("
            INSERT INTO auditoria_compras_ventas (
                tipo_movimiento, origen_id, clave_origen, fecha_movimiento,
                fecha_ingreso_compra, fecha_creacion_factura, fecha_emision_factura,
                factura_id, compra_id, comprobante_id, ajuste_id,
                numero_documento, cai, clasificacion_factura,
                estado_documento, motivo_documento, comentario_documento,
                producto_id, producto_nombre, lote_id,
                bodega_id, bodega_nombre, seccion_id, seccion_nombre,
                unidad_compra_id, unidad_compra_nombre, factor_unidad_compra,
                unidad_venta_id, unidad_venta_nombre, factor_unidad_venta,
                cantidad_documento, cantidad_base, cantidad_movimiento_base,
                afecta_inventario, cliente_id, cliente_nombre,
                proveedor_id, proveedor_nombre, usuario_id, usuario_nombre,
                sincronizado_at, created_at, updated_at
            )
            SELECT
                'AJUSTE', a.id,
                CONCAT('AJUSTE:', a.id, ':', ahp.producto_id, ':', ahp.tipo_aritmetica),
                COALESCE(CONCAT(a.fecha, ' 00:00:00'), a.created_at),
                NULL, NULL, NULL,
                NULL, NULL, NULL, a.id,
                a.numero_ajuste, NULL, NULL,
                CASE WHEN a.anulado = 1 THEN 'ANULADO' ELSE 'ACTIVO' END,
                ta.nombre, a.comentario,
                ahp.producto_id, p.nombre, NULL,
                NULL,
                CASE
                    WHEN COUNT(DISTINCT b.id) = 1 THEN MAX(b.nombre)
                    WHEN COUNT(DISTINCT b.id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END,
                NULL,
                CASE
                    WHEN COUNT(DISTINCT s.id) = 1 THEN MAX(s.descripcion)
                    WHEN COUNT(DISTINCT s.id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra,
                MAX(ahp.unidad_medida_venta_id), MAX(uv.nombre), MAX(umv.unidad_venta),
                SUM(ahp.cantidad),
                SUM(ahp.cantidad_total),
                SUM(CASE
                    WHEN ahp.tipo_aritmetica = 1 THEN ahp.cantidad_total
                    ELSE -1 * ahp.cantidad_total
                END),
                1,
                NULL, NULL, NULL, NULL, a.users_id, u.name,
                ?, ?, ?
            FROM ajuste_has_producto ahp
            INNER JOIN ajuste a ON a.id = ahp.ajuste_id
            INNER JOIN tipo_ajuste ta ON ta.id = a.tipo_ajuste_id
            INNER JOIN producto p ON p.id = ahp.producto_id
            LEFT JOIN recibido_bodega rb ON rb.id = ahp.recibido_bodega_id
            LEFT JOIN seccion s ON s.id = rb.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN unidad_medida uc ON uc.id = p.unidad_medida_compra_id
            LEFT JOIN unidad_medida_venta umv ON umv.id = ahp.unidad_medida_venta_id
            LEFT JOIN unidad_medida uv ON uv.id = umv.unidad_medida_id
            LEFT JOIN users u ON u.id = a.users_id
            $filtro
            GROUP BY
                a.id, a.fecha, a.created_at, a.numero_ajuste, a.anulado,
                ta.nombre, a.comentario, a.users_id, u.name,
                ahp.producto_id, ahp.tipo_aritmetica, p.nombre,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra
            ON DUPLICATE KEY UPDATE
                fecha_movimiento = VALUES(fecha_movimiento),
                ajuste_id = VALUES(ajuste_id),
                numero_documento = VALUES(numero_documento),
                estado_documento = VALUES(estado_documento),
                motivo_documento = VALUES(motivo_documento),
                comentario_documento = VALUES(comentario_documento),
                producto_nombre = VALUES(producto_nombre),
                bodega_nombre = VALUES(bodega_nombre),
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
                afecta_inventario = VALUES(afecta_inventario),
                usuario_id = VALUES(usuario_id),
                usuario_nombre = VALUES(usuario_nombre),
                sincronizado_at = VALUES(sincronizado_at),
                updated_at = VALUES(updated_at)
        ", $bindings);

        return $totalCandidatos;
    }

    private function sincronizarAnulacionesAjustes(?CarbonImmutable $desde, CarbonImmutable $inicio): int
    {
        $candidatos = DB::table('log_translado as lt')
            ->join('ajuste as a', 'a.id', '=', 'lt.ajuste_id')
            ->where('lt.descripcion', 'like', 'Anulación ajuste%');
        if ($desde) {
            $candidatos->where(function ($query) use ($desde) {
                $query->where('a.updated_at', '>=', $desde)
                    ->orWhere('lt.updated_at', '>=', $desde);
            });
        }
        $totalCandidatos = $candidatos
            ->distinct()
            ->count(DB::raw("CONCAT(lt.ajuste_id, ':', lt.origen)"));

        $filtro = $desde
            ? 'AND (a.updated_at >= ? OR lt.updated_at >= ?)'
            : '';
        $bindings = [$inicio, $inicio, $inicio];
        if ($desde) {
            array_push($bindings, $desde, $desde);
        }

        DB::affectingStatement("
            INSERT INTO auditoria_compras_ventas (
                tipo_movimiento, origen_id, clave_origen, fecha_movimiento,
                fecha_ingreso_compra, fecha_creacion_factura, fecha_emision_factura,
                factura_id, compra_id, comprobante_id, ajuste_id,
                numero_documento, cai, clasificacion_factura,
                estado_documento, motivo_documento, comentario_documento,
                producto_id, producto_nombre, lote_id,
                bodega_id, bodega_nombre, seccion_id, seccion_nombre,
                unidad_compra_id, unidad_compra_nombre, factor_unidad_compra,
                unidad_venta_id, unidad_venta_nombre, factor_unidad_venta,
                cantidad_documento, cantidad_base, cantidad_movimiento_base,
                afecta_inventario, cliente_id, cliente_nombre,
                proveedor_id, proveedor_nombre, usuario_id, usuario_nombre,
                sincronizado_at, created_at, updated_at
            )
            SELECT
                'ANULACION_AJUSTE', a.id,
                CONCAT('ANULACION_AJUSTE:', a.id, ':', rb.producto_id),
                COALESCE(a.anulado_at, MAX(lt.created_at)),
                NULL, NULL, NULL,
                NULL, NULL, NULL, a.id,
                a.numero_ajuste, NULL, NULL,
                'ANULADO', ta.nombre, COALESCE(a.motivo_anulacion, MAX(lt.comentario)),
                rb.producto_id, p.nombre, NULL,
                NULL,
                CASE
                    WHEN COUNT(DISTINCT b.id) = 1 THEN MAX(b.nombre)
                    WHEN COUNT(DISTINCT b.id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END,
                NULL,
                CASE
                    WHEN COUNT(DISTINCT s.id) = 1 THEN MAX(s.descripcion)
                    WHEN COUNT(DISTINCT s.id) > 1 THEN 'VARIAS'
                    ELSE NULL
                END,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra,
                MAX(lt.unidad_medida_venta_id), MAX(uv.nombre), MAX(umv.unidad_venta),
                SUM(lt.cantidad),
                SUM(lt.cantidad),
                SUM(CASE
                    WHEN lt.descripcion LIKE '%suma (-)%' THEN -1 * lt.cantidad
                    WHEN lt.descripcion LIKE '%resta (+)%' THEN lt.cantidad
                    ELSE 0
                END),
                1,
                NULL, NULL, NULL, NULL, MAX(lt.users_id), MAX(u.name),
                ?, ?, ?
            FROM log_translado lt
            INNER JOIN ajuste a ON a.id = lt.ajuste_id
            INNER JOIN tipo_ajuste ta ON ta.id = a.tipo_ajuste_id
            INNER JOIN recibido_bodega rb ON rb.id = lt.origen
            INNER JOIN producto p ON p.id = rb.producto_id
            LEFT JOIN seccion s ON s.id = rb.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN unidad_medida uc ON uc.id = p.unidad_medida_compra_id
            LEFT JOIN unidad_medida_venta umv ON umv.id = lt.unidad_medida_venta_id
            LEFT JOIN unidad_medida uv ON uv.id = umv.unidad_medida_id
            LEFT JOIN users u ON u.id = lt.users_id
            WHERE lt.descripcion LIKE 'Anulación ajuste%'
            $filtro
            GROUP BY
                a.id, a.anulado_at, a.numero_ajuste, ta.nombre,
                a.motivo_anulacion, rb.producto_id, p.nombre,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra
            ON DUPLICATE KEY UPDATE
                fecha_movimiento = VALUES(fecha_movimiento),
                ajuste_id = VALUES(ajuste_id),
                numero_documento = VALUES(numero_documento),
                motivo_documento = VALUES(motivo_documento),
                comentario_documento = VALUES(comentario_documento),
                producto_nombre = VALUES(producto_nombre),
                bodega_nombre = VALUES(bodega_nombre),
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
                usuario_id = VALUES(usuario_id),
                usuario_nombre = VALUES(usuario_nombre),
                sincronizado_at = VALUES(sincronizado_at),
                updated_at = VALUES(updated_at)
        ", $bindings);

        return $totalCandidatos;
    }

    private function sincronizarNotasCredito(?CarbonImmutable $desde, CarbonImmutable $inicio): int
    {
        $candidatos = DB::table('log_translado as lt')
            ->join('nota_credito as nc', 'nc.id', '=', 'lt.nota_credito_id')
            ->where('lt.descripcion', 'Devolucion de producto');
        if ($desde) {
            $candidatos->where(function ($query) use ($desde) {
                $query->where('nc.updated_at', '>=', $desde)
                    ->orWhere('lt.updated_at', '>=', $desde);
            });
        }
        $totalCandidatos = $candidatos->count();

        $filtro = $desde
            ? 'AND (nc.updated_at >= ? OR lt.updated_at >= ?)'
            : '';
        $bindings = [$inicio, $inicio, $inicio];
        if ($desde) {
            array_push($bindings, $desde, $desde);
        }

        DB::affectingStatement("
            INSERT INTO auditoria_compras_ventas (
                tipo_movimiento, origen_id, clave_origen, fecha_movimiento,
                fecha_ingreso_compra, fecha_creacion_factura, fecha_emision_factura,
                factura_id, compra_id, comprobante_id, ajuste_id, nota_credito_id,
                numero_documento, cai, clasificacion_factura,
                estado_documento, motivo_documento, comentario_documento,
                producto_id, producto_nombre, lote_id,
                bodega_id, bodega_nombre, seccion_id, seccion_nombre,
                unidad_compra_id, unidad_compra_nombre, factor_unidad_compra,
                unidad_venta_id, unidad_venta_nombre, factor_unidad_venta,
                cantidad_documento, cantidad_base, cantidad_movimiento_base,
                afecta_inventario, cliente_id, cliente_nombre,
                proveedor_id, proveedor_nombre, usuario_id, usuario_nombre,
                sincronizado_at, created_at, updated_at
            )
            SELECT
                'NOTA_CREDITO', lt.id, CONCAT('NOTA_CREDITO:', lt.id),
                COALESCE(nc.fecha, nc.created_at, lt.created_at),
                NULL, f.created_at, f.fecha_emision,
                f.id, NULL, NULL, NULL, nc.id,
                nc.numero_nota, nc.cai,
                CASE f.tipo_venta_id WHEN 1 THEN 'B' WHEN 2 THEN 'A' ELSE NULL END,
                CASE WHEN nc.estado_nota_id = 2 THEN 'ANULADO' ELSE 'ACTIVO' END,
                mnc.descripcion, nc.comentario,
                rb.producto_id, p.nombre, rb.id,
                b.id, b.nombre, s.id, s.descripcion,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra,
                lt.unidad_medida_venta_id, uv.nombre, umv.unidad_venta,
                lt.cantidad / COALESCE(NULLIF(umv.unidad_venta, 0), 1),
                lt.cantidad, lt.cantidad,
                1,
                f.cliente_id, COALESCE(cl.nombre, f.nombre_cliente),
                NULL, NULL, nc.users_id, u.name,
                ?, ?, ?
            FROM log_translado lt
            INNER JOIN nota_credito nc ON nc.id = lt.nota_credito_id
            INNER JOIN factura f ON f.id = nc.factura_id
            INNER JOIN recibido_bodega rb ON rb.id = lt.destino
            INNER JOIN producto p ON p.id = rb.producto_id
            LEFT JOIN motivo_nota_credito mnc ON mnc.id = nc.motivo_nota_credito_id
            LEFT JOIN seccion s ON s.id = rb.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN unidad_medida uc ON uc.id = p.unidad_medida_compra_id
            LEFT JOIN unidad_medida_venta umv ON umv.id = lt.unidad_medida_venta_id
            LEFT JOIN unidad_medida uv ON uv.id = umv.unidad_medida_id
            LEFT JOIN cliente cl ON cl.id = f.cliente_id
            LEFT JOIN users u ON u.id = nc.users_id
            WHERE lt.descripcion = 'Devolucion de producto'
            $filtro
            ON DUPLICATE KEY UPDATE
                fecha_movimiento = VALUES(fecha_movimiento),
                fecha_creacion_factura = VALUES(fecha_creacion_factura),
                fecha_emision_factura = VALUES(fecha_emision_factura),
                factura_id = VALUES(factura_id),
                nota_credito_id = VALUES(nota_credito_id),
                numero_documento = VALUES(numero_documento),
                cai = VALUES(cai),
                clasificacion_factura = VALUES(clasificacion_factura),
                estado_documento = VALUES(estado_documento),
                motivo_documento = VALUES(motivo_documento),
                comentario_documento = VALUES(comentario_documento),
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
                afecta_inventario = VALUES(afecta_inventario),
                cliente_id = VALUES(cliente_id),
                cliente_nombre = VALUES(cliente_nombre),
                usuario_id = VALUES(usuario_id),
                usuario_nombre = VALUES(usuario_nombre),
                sincronizado_at = VALUES(sincronizado_at),
                updated_at = VALUES(updated_at)
        ", $bindings);

        return $totalCandidatos;
    }

    private function sincronizarAnulacionesNotasCredito(?CarbonImmutable $desde, CarbonImmutable $inicio): int
    {
        $candidatos = DB::table('log_translado as lt')
            ->join('nota_credito as nc', 'nc.id', '=', 'lt.nota_credito_id')
            ->where('lt.descripcion', 'Nota credito anulado');
        if ($desde) {
            $candidatos->where(function ($query) use ($desde) {
                $query->where('nc.updated_at', '>=', $desde)
                    ->orWhere('lt.updated_at', '>=', $desde);
            });
        }
        $totalCandidatos = $candidatos->count();

        $filtro = $desde
            ? 'AND (nc.updated_at >= ? OR lt.updated_at >= ?)'
            : '';
        $bindings = [$inicio, $inicio, $inicio];
        if ($desde) {
            array_push($bindings, $desde, $desde);
        }

        DB::affectingStatement("
            INSERT INTO auditoria_compras_ventas (
                tipo_movimiento, origen_id, clave_origen, fecha_movimiento,
                fecha_ingreso_compra, fecha_creacion_factura, fecha_emision_factura,
                factura_id, compra_id, comprobante_id, ajuste_id, nota_credito_id,
                numero_documento, cai, clasificacion_factura,
                estado_documento, motivo_documento, comentario_documento,
                producto_id, producto_nombre, lote_id,
                bodega_id, bodega_nombre, seccion_id, seccion_nombre,
                unidad_compra_id, unidad_compra_nombre, factor_unidad_compra,
                unidad_venta_id, unidad_venta_nombre, factor_unidad_venta,
                cantidad_documento, cantidad_base, cantidad_movimiento_base,
                afecta_inventario, cliente_id, cliente_nombre,
                proveedor_id, proveedor_nombre, usuario_id, usuario_nombre,
                sincronizado_at, created_at, updated_at
            )
            SELECT
                'ANULACION_NOTA_CREDITO', lt.id,
                CONCAT('ANULACION_NOTA_CREDITO:', lt.id),
                lt.created_at,
                NULL, f.created_at, f.fecha_emision,
                f.id, NULL, NULL, NULL, nc.id,
                nc.numero_nota, nc.cai,
                CASE f.tipo_venta_id WHEN 1 THEN 'B' WHEN 2 THEN 'A' ELSE NULL END,
                'ANULADO',
                'Anulación de nota de crédito', lt.comentario,
                rb.producto_id, p.nombre, rb.id,
                b.id, b.nombre, s.id, s.descripcion,
                p.unidad_medida_compra_id, uc.nombre, p.unidadad_compra,
                lt.unidad_medida_venta_id, uv.nombre, umv.unidad_venta,
                lt.cantidad / COALESCE(NULLIF(umv.unidad_venta, 0), 1),
                lt.cantidad, -1 * lt.cantidad,
                1,
                f.cliente_id, COALESCE(cl.nombre, f.nombre_cliente),
                NULL, NULL, lt.users_id, u.name,
                ?, ?, ?
            FROM log_translado lt
            INNER JOIN nota_credito nc ON nc.id = lt.nota_credito_id
            INNER JOIN factura f ON f.id = nc.factura_id
            INNER JOIN recibido_bodega rb ON rb.id = lt.origen
            INNER JOIN producto p ON p.id = rb.producto_id
            LEFT JOIN seccion s ON s.id = rb.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN unidad_medida uc ON uc.id = p.unidad_medida_compra_id
            LEFT JOIN unidad_medida_venta umv ON umv.id = lt.unidad_medida_venta_id
            LEFT JOIN unidad_medida uv ON uv.id = umv.unidad_medida_id
            LEFT JOIN cliente cl ON cl.id = f.cliente_id
            LEFT JOIN users u ON u.id = lt.users_id
            WHERE lt.descripcion = 'Nota credito anulado'
            $filtro
            ON DUPLICATE KEY UPDATE
                fecha_movimiento = VALUES(fecha_movimiento),
                fecha_creacion_factura = VALUES(fecha_creacion_factura),
                fecha_emision_factura = VALUES(fecha_emision_factura),
                factura_id = VALUES(factura_id),
                nota_credito_id = VALUES(nota_credito_id),
                numero_documento = VALUES(numero_documento),
                cai = VALUES(cai),
                clasificacion_factura = VALUES(clasificacion_factura),
                comentario_documento = VALUES(comentario_documento),
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
                afecta_inventario = VALUES(afecta_inventario),
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
