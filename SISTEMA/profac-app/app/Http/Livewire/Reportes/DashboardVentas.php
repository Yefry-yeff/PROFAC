<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use DataTables;

class DashboardVentas extends Component
{
    private function estadoFacturaLabelColumn()
    {
        if (Schema::hasColumn('estado_factura', 'descripcion')) return 'descripcion';
        if (Schema::hasColumn('estado_factura', 'nombre')) return 'nombre';
        if (Schema::hasColumn('estado_factura', 'estado')) return 'estado';
        return null;
    }

    private function estadoFacturaLabelExpr($alias = 'ef')
    {
        $col = $this->estadoFacturaLabelColumn();
        return $col ? "$alias.$col" : "CONCAT('Estado #', $alias.id)";
    }

    private function productoCodigoExpr($alias = 'p')
    {
        return "CAST($alias.id AS CHAR)";
    }

    private function vhpCostoExpr($alias = 'vhp')
    {
        if (Schema::hasColumn('venta_has_producto', 'costo_total')) {
            return "$alias.costo_total";
        }
        if (Schema::hasColumn('venta_has_producto', 'costo_unitario')) {
            return "($alias.costo_unitario * $alias.cantidad)";
        }
        if (Schema::hasColumn('venta_has_producto', 'costo')) {
            return "$alias.costo";
        }

        return "0";
    }

    private function productoExistenciaExpr($alias = 'p')
    {
        if (Schema::hasColumn('producto', 'existencia')) return "$alias.existencia";
        if (Schema::hasColumn('producto', 'stock')) return "$alias.stock";
        if (Schema::hasColumn('producto', 'cantidad')) return "$alias.cantidad";
        return "0";
    }

    private function productoPrecioBaseExpr($alias = 'p')
    {
        if (Schema::hasColumn('producto', 'precio_base')) return "$alias.precio_base";
        if (Schema::hasColumn('producto', 'precio_compra')) return "$alias.precio_compra";
        if (Schema::hasColumn('producto', 'costo')) return "$alias.costo";
        if (Schema::hasColumn('producto', 'precio')) return "$alias.precio";
        return "0";
    }

    private function productoExistenciaGlobalSinPaperlandExpr($alias = 'p')
    {
        return "GREATEST(0,
            COALESCE((
                SELECT SUM(rb.cantidad_disponible)
                FROM recibido_bodega rb
                INNER JOIN seccion srb ON srb.id = rb.seccion_id
                INNER JOIN segmento sgrb ON sgrb.id = srb.segmento_id
                WHERE rb.producto_id = $alias.id
                  AND rb.cantidad_disponible > 0
                  AND sgrb.bodega_id <> 18
            ), 0)
            -
            COALESCE((
                SELECT SUM(php.cantidad)
                FROM prefactura_has_producto php
                INNER JOIN prefactura pf ON pf.id = php.prefactura_id
                INNER JOIN seccion sp ON sp.id = php.seccion_id
                INNER JOIN segmento sgp ON sgp.id = sp.segmento_id
                WHERE php.producto_id = $alias.id
                  AND php.resta_inventario = 1
                  AND pf.estado = 'activo'
                  AND sgp.bodega_id <> 18
            ), 0)
        )";
    }

    private function clienteCategoriaEscalaLabelColumn()
    {
        if (Schema::hasColumn('cliente_categoria_escala', 'nombre_categoria')) return 'nombre_categoria';
        if (Schema::hasColumn('cliente_categoria_escala', 'nombre')) return 'nombre';
        if (Schema::hasColumn('cliente_categoria_escala', 'descripcion')) return 'descripcion';
        return null;
    }

    private function clienteCategoriaEscalaLabelExpr($alias = 'cce')
    {
        $col = $this->clienteCategoriaEscalaLabelColumn();
        return $col ? "$alias.$col" : "NULL";
    }

    private function categoriaPreciosLabelColumn()
    {
        if (Schema::hasColumn('categoria_precios', 'nombre')) return 'nombre';
        if (Schema::hasColumn('categoria_precios', 'descripcion')) return 'descripcion';
        return null;
    }

    private function categoriaPreciosLabelExpr($alias = 'cpesc')
    {
        $col = $this->categoriaPreciosLabelColumn();
        return $col ? "$alias.$col" : "NULL";
    }

    private function estadoFacturaSimpleExpr($alias = 'f')
    {
        return "CASE
            WHEN $alias.estado_factura_id = 0 THEN 'Anulada'
            WHEN $alias.estado_factura_id = 1 THEN 'Facturado'
            ELSE CONCAT('Estado #', $alias.estado_factura_id)
        END";
    }

    private function canalVentaExpr($alias = 'f')
    {
        if (Schema::hasColumn('factura', 'canal_venta')) return "$alias.canal_venta";
        if (Schema::hasColumn('factura', 'canal')) return "$alias.canal";
        return null;
    }

    public function render()
    {
        $vendedores = DB::SELECT("
            SELECT DISTINCT u.id, u.name
            FROM users u
            INNER JOIN factura f ON f.vendedor = u.id
            WHERE f.estado_venta_id = 1
            ORDER BY u.name
        ");

        return view('livewire.reportes.dashboard-ventas', compact('vendedores'))
            ->layout('layouts.app', ['title' => 'Dashboard de Ventas']);
    }

    // ─── PESTAÑA 1: KPIs generales ──────────────────────────────────────────
    public function kpis(Request $request)
    {
        $anios = $request->anios        ?? null;
        $fi   = $request->fecha_inicio  ?? date('Y-01-01');
        $ff   = $request->fecha_final   ?? date('Y-m-d');
        $vend = $request->vendedor      ?? null;
        $tc   = $request->tipo_cliente  ?? null;
        $cat  = $request->categoria     ?? null;

        $join = "INNER JOIN cliente cli ON cli.id = f.cliente_id
                 INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id";

        $extraVend = $vend ? " AND f.vendedor = " . (int)$vend : "";
        $extraTc   = $tc   ? " AND tc.id = "     . (int)$tc   : "";
        $extraCat  = "";
        if ($cat) {
            $join .= " INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
                       INNER JOIN producto p ON p.id = vhp.producto_id
                       INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id";
            $extraCat = " AND sc.categoria_producto_id = " . (int)$cat;
        }

        if ($anios && count((array)$anios) > 0) {
            $anios     = array_map('intval', (array)$anios);
            sort($anios);
            $inAnios   = implode(',', $anios);
            $where     = "f.estado_venta_id = 1 AND YEAR(f.fecha_emision) IN ($inAnios)$extraVend$extraTc$extraCat";
            $span      = count($anios);
            $prevAnios = array_map(fn($a) => $a - $span, $anios);
            $inPrev    = implode(',', $prevAnios);
            $wherePrev = "f.estado_venta_id = 1 AND YEAR(f.fecha_emision) IN ($inPrev)$extraVend$extraTc$extraCat";
        } else {
            $where     = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'$extraVend$extraTc$extraCat";
            $dias      = (strtotime($ff) - strtotime($fi)) / 86400 + 1;
            $fiPrev    = date('Y-m-d', strtotime($fi) - $dias * 86400);
            $ffPrev    = date('Y-m-d', strtotime($fi) - 86400);
            $wherePrev = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fiPrev' AND '$ffPrev'$extraVend$extraTc$extraCat";
        }

        $row = DB::SELECTONE("
            SELECT
                COUNT(DISTINCT f.id)                          AS total_facturas,
                COALESCE(SUM(f.total), 0)                     AS total_ventas,
                COALESCE(AVG(f.total), 0)                     AS ticket_promedio,
                COUNT(DISTINCT f.cliente_id)                  AS clientes_unicos,
                COALESCE(SUM(f.monto_descuento), 0)           AS total_descuentos,
                COALESCE(SUM(f.isv), 0)                       AS total_isv,
                COALESCE(SUM(f.sub_total), 0)                 AS total_sin_isv
            FROM factura f
            $join
            WHERE $where
        ");

        // Mes con más ventas
        $mejorMes = DB::SELECTONE("
            SELECT DATE_FORMAT(f.fecha_emision,'%Y-%m') AS periodo,
                   SUM(f.total) AS monto
            FROM factura f $join
            WHERE $where
            GROUP BY periodo ORDER BY monto DESC LIMIT 1
        ");

        // Mejor vendedor en el período
        $mejorVend = DB::SELECTONE("
            SELECT u.name, SUM(f.total) AS monto
            FROM factura f $join
            INNER JOIN users u ON u.id = f.vendedor
            WHERE $where
            GROUP BY u.id, u.name ORDER BY monto DESC LIMIT 1
        ");

        $prev = DB::SELECTONE("
            SELECT COALESCE(SUM(f.total), 0) AS total_ventas
            FROM factura f $join WHERE $wherePrev
        ");

        $crecimiento = ($prev->total_ventas > 0)
            ? round((($row->total_ventas - $prev->total_ventas) / $prev->total_ventas) * 100, 2)
            : null;

        return response()->json([
            'total_facturas'   => (int)$row->total_facturas,
            'total_ventas'     => round((float)$row->total_ventas, 2),
            'ticket_promedio'  => round((float)$row->ticket_promedio, 2),
            'total_sin_isv'    => round((float)$row->total_sin_isv, 2),
            'clientes_unicos'  => (int)$row->clientes_unicos,
            'total_descuentos' => round((float)$row->total_descuentos, 2),
            'total_isv'        => round((float)$row->total_isv, 2),
            'crecimiento'      => $crecimiento,
            'mejor_mes'        => $mejorMes ? $mejorMes->periodo : null,
            'mejor_mes_monto'  => $mejorMes ? round((float)$mejorMes->monto, 2) : 0,
            'mejor_vendedor'   => $mejorVend ? $mejorVend->name : null,
        ]);
    }

    // ─── PESTAÑA 1: Ventas por mes (comparativo de años) ───────────────────
    public function ventasPorMes(Request $request)
    {
        $anios = $request->anios ?? [date('Y')];
        if (!is_array($anios)) $anios = [$anios];
        $anios = array_map('intval', $anios);
        $inAnios = implode(',', $anios);

        $vend = $request->vendedor     ? (int)$request->vendedor     : null;
        $tc   = $request->tipo_cliente ? (int)$request->tipo_cliente : null;

        $whereExtra = "";
        $joinExtra  = "INNER JOIN cliente cli ON cli.id = f.cliente_id
                       INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id";
        if ($vend) $whereExtra .= " AND f.vendedor = $vend";
        if ($tc)   $whereExtra .= " AND tc.id = $tc";

        $rows = DB::SELECT("
            SELECT
                YEAR(f.fecha_emision)  AS anio,
                MONTH(f.fecha_emision) AS mes,
                SUM(f.total)           AS total,
                COUNT(f.id)            AS facturas
            FROM factura f
            $joinExtra
            WHERE f.estado_venta_id = 1
              AND YEAR(f.fecha_emision) IN ($inAnios)
              $whereExtra
            GROUP BY anio, mes
            ORDER BY anio, mes
        ");

        return response()->json($rows);
    }

    // ─── PESTAÑA 1: Heatmap ventas por mes/año ──────────────────────────────
    public function heatmap(Request $request)
    {
        $fi = $request->fecha_inicio ?? '2022-01-01';
        $ff = $request->fecha_final  ?? date('Y-m-d');

        $rows = DB::SELECT("
            SELECT
                YEAR(fecha_emision)  AS anio,
                MONTH(fecha_emision) AS mes,
                SUM(total)           AS total,
                COUNT(id)            AS facturas
            FROM factura
            WHERE estado_venta_id = 1
              AND fecha_emision BETWEEN '$fi' AND '$ff'
            GROUP BY anio, mes
            ORDER BY anio, mes
        ");

        return response()->json($rows);
    }

    // ─── PESTAÑA 2: Ventas semanales (tabla) ────────────────────────────────
    public function ventasSemanales(Request $request)
    {
        $fi         = $request->fecha_inicio ?? date('Y-m-01');
        $ff         = $request->fecha_final  ?? date('Y-m-d');
        $vend       = $request->vendedor      ? (int)$request->vendedor     : null;
        $tc         = $request->tipo_cliente  ? (int)$request->tipo_cliente : null;
        $diaSemana  = $request->dia_semana    ?? null;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($vend)      $where .= " AND f.vendedor = $vend";
        if ($tc)        $where .= " AND tc.id = $tc";
        if ($diaSemana) $where .= " AND DAYNAME(f.fecha_emision) = '" . addslashes($diaSemana) . "'";

        $rows = DB::SELECT("
            SELECT
                f.id,
                DATE_FORMAT(f.fecha_emision, '%Y-%m-%d') AS fecha,
                DAYNAME(f.fecha_emision)                  AS dia_semana,
                WEEK(f.fecha_emision, 1)                  AS semana_iso,
                f.cai                                     AS documento,
                f.nombre_cliente                          AS cliente,
                u.name                                    AS vendedor,
                tc.descripcion                            AS tipo_cliente,
                FORMAT(f.sub_total, 2)                    AS subtotal,
                FORMAT(f.isv, 2)                          AS impuesto,
                FORMAT(f.monto_descuento, 2)              AS descuento,
                FORMAT(f.total, 2)                        AS total,
                f.total                                   AS total_raw
            FROM factura f
            INNER JOIN users u       ON u.id  = f.vendedor
            INNER JOIN cliente cli   ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id
            WHERE $where
            ORDER BY f.fecha_emision DESC
        ");

        return Datatables::of($rows)->rawColumns([])->make(true);
    }

    // ─── PESTAÑA 2: Exportar detalle semanal (sin paginación) ───────────────
    public function exportarDetalleSemanal(Request $request)
    {
        $fi        = $request->fecha_inicio ?? date('Y-m-01');
        $ff        = $request->fecha_final  ?? date('Y-m-d');
        $vend      = $request->vendedor     ? (int)$request->vendedor     : null;
        $tc        = $request->tipo_cliente ? (int)$request->tipo_cliente : null;
        $diaSemana = $request->dia_semana   ?? null;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($vend)      $where .= " AND f.vendedor = $vend";
        if ($tc)        $where .= " AND tc.id = $tc";
        if ($diaSemana) $where .= " AND DAYNAME(f.fecha_emision) = '" . addslashes($diaSemana) . "'";

        $rows = DB::SELECT("
            SELECT
                DATE_FORMAT(f.fecha_emision, '%Y-%m-%d') AS fecha,
                DAYNAME(f.fecha_emision)                  AS dia_semana,
                WEEK(f.fecha_emision, 1)                  AS semana_iso,
                f.cai                                     AS documento,
                f.nombre_cliente                          AS cliente,
                u.name                                    AS vendedor,
                tc.descripcion                            AS tipo_cliente,
                f.sub_total                               AS subtotal,
                f.isv                                     AS impuesto,
                f.monto_descuento                         AS descuento,
                f.total                                   AS total
            FROM factura f
            INNER JOIN users u         ON u.id  = f.vendedor
            INNER JOIN cliente cli     ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id  = cli.tipo_cliente_id
            WHERE $where
            ORDER BY f.fecha_emision DESC
        ");

        return response()->json($rows);
    }

    // ─── PESTAÑA 2: Resumen semanal KPIs ────────────────────────────────────
    public function resumenSemanal(Request $request)
    {
        $fi         = $request->fecha_inicio ?? date('Y-m-01');
        $ff         = $request->fecha_final  ?? date('Y-m-d');
        $vend       = $request->vendedor      ? (int)$request->vendedor     : null;
        $tc         = $request->tipo_cliente  ? (int)$request->tipo_cliente : null;
        $diaSemana  = $request->dia_semana    ?? null;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($vend)      $where .= " AND f.vendedor = $vend";
        if ($tc)        $where .= " AND tc.id = $tc";
        if ($diaSemana) $where .= " AND DAYNAME(f.fecha_emision) = '" . addslashes($diaSemana) . "'";

        $totales = DB::SELECTONE("
            SELECT COUNT(f.id) AS facturas, COALESCE(SUM(f.total),0) AS total,
                   COALESCE(AVG(f.total),0) AS ticket_promedio,
                   COALESCE(SUM(f.sub_total),0) AS total_sin_isv
            FROM factura f
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id
            WHERE $where
        ");

        $mejorVend = DB::SELECTONE("
            SELECT u.name, SUM(f.total) AS monto FROM factura f
            INNER JOIN users u ON u.id = f.vendedor
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id
            WHERE $where GROUP BY u.id, u.name ORDER BY monto DESC LIMIT 1
        ");

        $mejorCliente = DB::SELECTONE("
            SELECT f.nombre_cliente AS nombre, SUM(f.total) AS monto FROM factura f
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id
            WHERE $where GROUP BY f.cliente_id, f.nombre_cliente ORDER BY monto DESC LIMIT 1
        ");

        $mejorDia = DB::SELECTONE("
            SELECT DATE_FORMAT(f.fecha_emision,'%Y-%m-%d') AS fecha, SUM(f.total) AS monto
            FROM factura f
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id
            WHERE $where GROUP BY fecha ORDER BY monto DESC LIMIT 1
        ");

        $porDia = DB::SELECT("
            SELECT DAYNAME(f.fecha_emision) AS dia, SUM(f.total) AS total, COUNT(f.id) AS facturas
            FROM factura f
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id = cli.tipo_cliente_id
            WHERE $where GROUP BY dia ORDER BY total DESC
        ");

        return response()->json([
            'facturas'       => (int)$totales->facturas,
            'total'          => round((float)$totales->total, 2),
            'ticket_promedio'=> round((float)$totales->ticket_promedio, 2),
            'total_sin_isv'  => round((float)$totales->total_sin_isv, 2),
            'mejor_vendedor' => $mejorVend   ? $mejorVend->name          : '-',
            'mejor_cliente'  => $mejorCliente ? $mejorCliente->nombre    : '-',
            'mejor_dia'      => $mejorDia    ? $mejorDia->fecha          : '-',
            'mejor_dia_monto'=> $mejorDia    ? round((float)$mejorDia->monto, 2) : 0,
            'por_dia'        => $porDia,
        ]);
    }

    // ─── PESTAÑA 3: Top vendedores ──────────────────────────────────────────
    public function topVendedores(Request $request)
    {
        $fi         = $request->fecha_inicio ?? date('Y-01-01');
        $ff         = $request->fecha_final  ?? date('Y-m-d');
        $tc         = $request->tipo_cliente  ? (int)$request->tipo_cliente : null;
        $vend       = $request->vendedor      ? (int)$request->vendedor     : null;
        $diaSemana  = $request->dia_semana    ?? null;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($tc)        $where .= " AND tc.id = $tc";
        if ($vend)      $where .= " AND f.vendedor = $vend";
        if ($diaSemana) $where .= " AND DAYNAME(f.fecha_emision) = '" . addslashes($diaSemana) . "'";

        $rows = DB::SELECT("
            SELECT
                u.id                                       AS vendedor_id,
                u.name                                     AS vendedor,
                COUNT(DISTINCT f.id)                       AS facturas,
                COUNT(DISTINCT f.cliente_id)               AS clientes_atendidos,
                SUM(f.total)                               AS total_ventas,
                AVG(f.total)                               AS ticket_promedio,
                COALESCE(SUM(f.sub_total), 0)              AS total_sin_isv
            FROM factura f
            INNER JOIN users u         ON u.id  = f.vendedor
            INNER JOIN cliente cli     ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id  = cli.tipo_cliente_id
            WHERE $where
            GROUP BY u.id, u.name
            ORDER BY total_ventas DESC
        ");

        $totalGlobal    = array_sum(array_column($rows, 'total_ventas'));
        $totalSinIsvGlobal = array_sum(array_column($rows, 'total_sin_isv'));

        foreach ($rows as &$r) {
            $r->participacion = $totalGlobal > 0
                ? round(($r->total_ventas / $totalGlobal) * 100, 2)
                : 0;
            $r->participacion_sin_isv = $totalSinIsvGlobal > 0
                ? round(($r->total_sin_isv / $totalSinIsvGlobal) * 100, 2)
                : 0;
            $r->total_ventas    = round((float)$r->total_ventas, 2);
            $r->ticket_promedio = round((float)$r->ticket_promedio, 2);
            $r->total_sin_isv   = round((float)$r->total_sin_isv, 2);
        }

        return response()->json($rows);
    }

    // ─── PESTAÑA 3: Top clientes ────────────────────────────────────────────
    public function topClientes(Request $request)
    {
        $fi      = $request->fecha_inicio  ?? date('Y-01-01');
        $ff      = $request->fecha_final   ?? date('Y-m-d');
        $vend    = $request->vendedor      ? (int)$request->vendedor      : null;
        $tc      = $request->tipo_cliente  ? (int)$request->tipo_cliente  : null;
        $cat     = $request->categoria     ? (int)$request->categoria     : null;
        $marca   = $request->marca         ? (int)$request->marca         : null;
        $cliente = trim((string)($request->cliente ?? ''));
        $prod    = trim((string)($request->producto ?? ''));
        $limit   = $request->limite        ? (int)$request->limite        : 9999;

        $where  = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?";
        $params = [$fi, $ff];
        if ($vend)    { $where .= " AND f.vendedor = ?";           $params[] = $vend; }
        if ($tc)      { $where .= " AND cli.tipo_cliente_id = ?";  $params[] = $tc; }
        if ($cliente) { $where .= " AND cli.nombre LIKE ?";        $params[] = "%$cliente%"; }

        /* Si hay filtro de categoría, marca o producto necesitamos el JOIN de productos */
        if ($cat || $marca || $prod) {
            if ($cat)   { $where .= " AND sc.categoria_producto_id = ?"; $params[] = $cat; }
            if ($marca) { $where .= " AND p.marca_id = ?";              $params[] = $marca; }
            if ($prod) {
                if (is_numeric($prod)) { $where .= " AND p.id = ?"; $params[] = intval($prod); }
                else                   { $where .= " AND p.nombre LIKE ?"; $params[] = "%$prod%"; }
            }

            $rows = DB::select("
                SELECT
                    cli.id                                              AS cliente_id,
                    cli.nombre                                          AS cliente,
                    tc.descripcion                                      AS tipo_cliente,
                    DATE_FORMAT(f.fecha_emision, '%Y-%m')               AS mes,
                    COUNT(DISTINCT f.id)                                AS facturas,
                    COALESCE(SUM(vhp.sub_total_s), 0)                   AS total_comprado,
                    COALESCE(AVG(vhp.sub_total_s), 0)                   AS ticket_promedio,
                    COALESCE(SUM(vhp.cantidad), 0)                      AS total_unidades,
                    MAX(f.fecha_emision)                                AS ultima_compra,
                    DATEDIFF(CURDATE(), MAX(f.fecha_emision))           AS dias_sin_comprar
                FROM factura f
                INNER JOIN cliente cli                  ON cli.id = f.cliente_id
                INNER JOIN tipo_cliente tc              ON tc.id  = cli.tipo_cliente_id
                INNER JOIN venta_has_producto vhp       ON vhp.factura_id = f.id
                INNER JOIN producto p                   ON p.id   = vhp.producto_id
                INNER JOIN sub_categoria sc             ON sc.id  = p.sub_categoria_id
                WHERE $where
                GROUP BY cli.id, cli.nombre, tc.descripcion, DATE_FORMAT(f.fecha_emision, '%Y-%m')
                ORDER BY total_comprado DESC, cli.nombre ASC, mes ASC
                LIMIT $limit
            ", $params);
        } else {
            $rows = DB::select("
                SELECT
                    cli.id                                          AS cliente_id,
                    cli.nombre                                      AS cliente,
                    tc.descripcion                                  AS tipo_cliente,
                    DATE_FORMAT(f.fecha_emision, '%Y-%m')           AS mes,
                    COUNT(DISTINCT f.id)                            AS facturas,
                    COALESCE(SUM(vhp.sub_total_s), 0)               AS total_comprado,
                    COALESCE(AVG(vhp.sub_total_s), 0)               AS ticket_promedio,
                    COALESCE(SUM(vhp.cantidad), 0)                  AS total_unidades,
                    MAX(f.fecha_emision)                            AS ultima_compra,
                    DATEDIFF(CURDATE(), MAX(f.fecha_emision))       AS dias_sin_comprar
                FROM factura f
                INNER JOIN cliente cli     ON cli.id = f.cliente_id
                INNER JOIN tipo_cliente tc ON tc.id  = cli.tipo_cliente_id
                INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
                WHERE $where
                GROUP BY cli.id, cli.nombre, tc.descripcion, DATE_FORMAT(f.fecha_emision, '%Y-%m')
                ORDER BY total_comprado DESC, cli.nombre ASC, mes ASC
                LIMIT $limit
            ", $params);
        }

        // Clasificación ABC: A=70%, B=20%, C=10%
        $total_global = array_sum(array_column($rows, 'total_comprado'));
        $acumulado = 0;
        foreach ($rows as &$r) {
            $acumulado += $r->total_comprado;
            $pct = $total_global > 0 ? ($acumulado / $total_global) * 100 : 0;
            $r->clasificacion_abc = $pct <= 70 ? 'A' : ($pct <= 90 ? 'B' : 'C');
            $r->total_comprado    = round((float)$r->total_comprado, 2);
            $r->ticket_promedio   = round((float)$r->ticket_promedio, 2);
            $r->total_unidades    = intval($r->total_unidades ?? 0);
            $r->recurrente        = $r->facturas >= 3;
            $r->inactivo          = $r->dias_sin_comprar > 60;
        }

        return response()->json($rows);
    }

    // ─── PESTAÑA Clientes: Top N productos comprados ──────────────────────
    public function topProductosCli(Request $request)
    {
        $fi      = $request->fecha_inicio ?? date('Y-01-01');
        $ff      = $request->fecha_final  ?? date('Y-m-d');
        $vend    = $request->vendedor     ? (int)$request->vendedor     : null;
        $tc      = $request->tipo_cliente ? (int)$request->tipo_cliente : null;
        $cliente = trim((string)($request->cliente  ?? ''));
        $marca   = $request->marca        ? (int)$request->marca        : null;
        $prod    = trim((string)($request->producto ?? ''));
        $limit   = $request->limite       ? (int)$request->limite       : 5;

        $where  = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?";
        $params = [$fi, $ff];
        if ($vend)    { $where .= " AND f.vendedor = ?";           $params[] = $vend; }
        if ($tc)      { $where .= " AND cli.tipo_cliente_id = ?";  $params[] = $tc; }
        if ($cliente) { $where .= " AND cli.nombre LIKE ?";        $params[] = "%$cliente%"; }
        if ($marca)   { $where .= " AND p.marca_id = ?";           $params[] = $marca; }
        if ($prod) {
            if (is_numeric($prod)) { $where .= " AND p.id = ?"; $params[] = intval($prod); }
            else                   { $where .= " AND p.nombre LIKE ?"; $params[] = "%$prod%"; }
        }

        $rows = DB::select("
            SELECT
                p.id                               AS producto_id,
                p.nombre                           AS producto,
                COALESCE(m.nombre, 'N/A')          AS marca,
                cp.descripcion                     AS categoria,
                COUNT(DISTINCT f.id)               AS facturas,
                COALESCE(SUM(vhp.cantidad), 0)     AS unidades,
                COALESCE(SUM(vhp.sub_total_s), 0)  AS total_vendido
            FROM factura f
            INNER JOIN venta_has_producto vhp     ON vhp.factura_id = f.id
            INNER JOIN producto p                 ON p.id = vhp.producto_id
            LEFT  JOIN marca m                    ON m.id = p.marca_id
            INNER JOIN sub_categoria sc           ON sc.id = p.sub_categoria_id
            INNER JOIN categoria_producto cp      ON cp.id = sc.categoria_producto_id
            INNER JOIN cliente cli                ON cli.id = f.cliente_id
            WHERE $where
            GROUP BY p.id, p.nombre, m.nombre, cp.descripcion
            ORDER BY total_vendido DESC
            LIMIT $limit
        ", $params);

        foreach ($rows as &$r) {
            $r->total_vendido = round((float)$r->total_vendido, 2);
            $r->unidades      = round((float)$r->unidades, 2);
        }

        return response()->json($rows);
    }

    // ─── PESTAÑA Clientes: Detalle productos × cliente ────────────────────
    public function productosXCliente(Request $request)
    {
        $fi      = $request->fecha_inicio ?? date('Y-01-01');
        $ff      = $request->fecha_final  ?? date('Y-m-d');
        $vend    = $request->vendedor     ? (int)$request->vendedor     : null;
        $tc      = $request->tipo_cliente ? (int)$request->tipo_cliente : null;
        $cliente = trim((string)($request->cliente  ?? ''));
        $marca   = $request->marca        ? (int)$request->marca        : null;
        $prod    = trim((string)($request->producto ?? ''));
        $limit   = $request->limite       ? (int)$request->limite       : 1000;

        $where  = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?";
        $params = [$fi, $ff];
        if ($vend)    { $where .= " AND f.vendedor = ?";           $params[] = $vend; }
        if ($tc)      { $where .= " AND cli.tipo_cliente_id = ?";  $params[] = $tc; }
        if ($cliente) { $where .= " AND cli.nombre LIKE ?";        $params[] = "%$cliente%"; }
        if ($marca)   { $where .= " AND p.marca_id = ?";           $params[] = $marca; }
        if ($prod) {
            if (is_numeric($prod)) { $where .= " AND p.id = ?"; $params[] = intval($prod); }
            else                   { $where .= " AND p.nombre LIKE ?"; $params[] = "%$prod%"; }
        }

        $rows = DB::select("
            SELECT
                cli.nombre                                          AS cliente,
                tc.descripcion                                      AS tipo_cliente,
                DATE_FORMAT(f.fecha_emision, '%Y-%m')               AS mes,
                p.nombre                                            AS producto,
                COALESCE(m.nombre, 'N/A')                           AS marca,
                cp.descripcion                                      AS categoria,
                COUNT(DISTINCT f.id)                                AS facturas,
                COALESCE(SUM(vhp.cantidad), 0)                      AS unidades,
                COALESCE(SUM(vhp.sub_total_s), 0)                   AS total_comprado,
                DATE_FORMAT(MAX(f.fecha_emision), '%Y-%m-%d')       AS ultima_compra
            FROM factura f
            INNER JOIN cliente cli                ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc            ON tc.id  = cli.tipo_cliente_id
            INNER JOIN venta_has_producto vhp     ON vhp.factura_id = f.id
            INNER JOIN producto p                 ON p.id = vhp.producto_id
            LEFT  JOIN marca m                    ON m.id = p.marca_id
            INNER JOIN sub_categoria sc           ON sc.id = p.sub_categoria_id
            INNER JOIN categoria_producto cp      ON cp.id = sc.categoria_producto_id
            WHERE $where
            GROUP BY cli.id, cli.nombre, tc.descripcion, DATE_FORMAT(f.fecha_emision, '%Y-%m'),
                     p.id, p.nombre, m.nombre, cp.descripcion
            ORDER BY cli.nombre ASC, mes ASC, total_comprado DESC
            LIMIT $limit
        ", $params);

        foreach ($rows as &$r) {
            $r->total_comprado = round((float)$r->total_comprado, 2);
            $r->unidades       = round((float)$r->unidades, 2);
        }

        return response()->json($rows);
    }

    // ─── PESTAÑA 3: Top productos ───────────────────────────────────────────
    public function topProductos(Request $request)
    {
        $fi    = $request->fecha_inicio ?? date('Y-01-01');
        $ff    = $request->fecha_final  ?? date('Y-m-d');
        $productoId = $request->producto_id ? (int)$request->producto_id : null;
        $limit = $request->limite !== null ? (int)$request->limite : 20;
        $precioBaseExpr = $this->productoPrecioBaseExpr('p');
        $precioBaseVentaEscalaExpr = "COALESCE(
            ppc.precio_base_venta,
            (
                SELECT ppc2.precio_base_venta
                FROM categoria_precios cp2
                INNER JOIN precios_producto_carga ppc2 ON ppc2.categoria_precios_id = cp2.id
                WHERE cp2.cliente_categoria_escala_id = cli.cliente_categoria_escala_id
                  AND ppc2.producto_id = p.id
                  AND cp2.estado_id = 1
                  AND ppc2.estado_id = 1
                ORDER BY cp2.id ASC
                LIMIT 1
            ),
            $precioBaseExpr
        )";
        $cantidadFacturadaExpr = "(CASE
            WHEN COALESCE(vhp.sub_total_s, 0) > 0 THEN COALESCE(NULLIF(vhp.cantidad_s, 0), (vhp.sub_total_s / NULLIF(vhp.precio_unidad, 0)), vhp.cantidad)
            ELSE 0
        END)";
        $costoExpr = "($precioBaseVentaEscalaExpr * $cantidadFacturadaExpr)";
        $ventaFacturaExpr = "(vhp.precio_unidad * $cantidadFacturadaExpr)";
        $utilidadExpr = "($costoExpr - $ventaFacturaExpr)";

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($productoId) $where .= " AND p.id = $productoId";
        else $where .= " AND 1 = 0";

        $limitSql = $limit > 0 ? " LIMIT $limit" : "";

        $escalaClienteExpr = $this->clienteCategoriaEscalaLabelExpr('cce');
        $escalaPrecioExpr = $this->categoriaPreciosLabelExpr('cpesc');
        $estadoFacturaExpr = $this->estadoFacturaSimpleExpr('f');

        $rows = DB::SELECT("
            SELECT
                f.id AS factura_id,
                COALESCE(NULLIF(f.cai, ''), NULLIF(f.numero_factura, ''), CONCAT('FAC-', f.id)) AS numero_factura,
                DATE_FORMAT(f.fecha_emision, '%Y-%m-%d') AS fecha,
                MAX(CONCAT(
                    COALESCE(NULLIF(cli.nombre, ''), f.nombre_cliente, 'N/A'),
                    ' (',
                    COALESCE($escalaClienteExpr, CONCAT('Escala #', cli.cliente_categoria_escala_id), 'Sin escala'),
                    ')'
                )) AS cliente,
                COALESCE(u.name, 'N/A') AS vendedor,
                MAX(COALESCE($escalaPrecioExpr, CONCAT('Categoria #', cpesc.id), 'Sin categoria precio')) AS escala,
                p.id AS producto_id,
                {$this->productoCodigoExpr('p')} AS codigo,
                p.nombre AS producto,
                COALESCE(m.nombre, 'N/A') AS marca,
                cp.descripcion AS categoria,
                COALESCE(SUM($cantidadFacturadaExpr), 0) AS cantidad,
                COALESCE(AVG(vhp.precio_unidad), 0) AS precio_unitario,
                COALESCE(AVG($precioBaseVentaEscalaExpr), 0) AS precio_base_venta,
                COALESCE(SUM($ventaFacturaExpr), 0) AS venta_factura,
                COALESCE(SUM(vhp.sub_total_s), 0) AS subtotal,
                COALESCE(SUM($costoExpr), 0) AS costo_total,
                COALESCE(SUM(vhp.sub_total_s), 0) - COALESCE(SUM($costoExpr), 0) AS utilidad_bruta,
                $estadoFacturaExpr AS estado
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p ON p.id = vhp.producto_id
            LEFT JOIN marca m ON m.id = p.marca_id
            INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id
            INNER JOIN categoria_producto cp ON cp.id = sc.categoria_producto_id
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            LEFT JOIN users u ON u.id = f.vendedor
            LEFT JOIN estado_factura ef ON ef.id = f.estado_factura_id
            LEFT JOIN precios_producto_carga ppc ON ppc.id = vhp.precios_producto_carga_id
            LEFT JOIN categoria_precios cpesc ON cpesc.id = ppc.categoria_precios_id
            LEFT JOIN cliente_categoria_escala cce ON cce.id = COALESCE(cpesc.cliente_categoria_escala_id, cli.cliente_categoria_escala_id)
            WHERE $where
            GROUP BY
                f.id, f.cai, f.numero_factura, f.fecha_emision, cli.nombre, f.nombre_cliente,
                u.name, p.id, p.nombre, m.nombre, cp.descripcion,
                f.estado_factura_id
            ORDER BY f.fecha_emision DESC, f.id DESC
            $limitSql
        ");

        foreach ($rows as &$r) {
            $r->cantidad      = round((float)$r->cantidad, 2);
            $r->precio_unitario = round((float)$r->precio_unitario, 2);
            $r->precio_base_venta = round((float)$r->precio_base_venta, 2);
            $r->venta_factura = round((float)$r->venta_factura, 2);
            $r->costo_total  = round((float)$r->costo_total, 2);
            $r->utilidad_bruta = round((float)$r->utilidad_bruta, 2);
        }

        return response()->json($rows);
    }

    // ─── PESTAÑA 3: Resumen + facturas por producto/cliente ───────────────
    public function detalleProductoFacturas(Request $request)
    {
        $fi       = $request->fecha_inicio ?? date('Y-01-01');
        $ff       = $request->fecha_final  ?? date('Y-m-d');
        $cat      = $request->categoria    ? (int)$request->categoria    : null;
        $marca    = $request->marca        ? (int)$request->marca        : null;
        $vend     = $request->vendedor     ? (int)$request->vendedor     : null;
        $tc       = $request->tipo_cliente ? (int)$request->tipo_cliente : null;
        $producto = trim((string) ($request->producto ?? ''));
        $cliente  = trim((string) ($request->cliente  ?? ''));

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($cat)   $where .= " AND sc.categoria_producto_id = $cat";
        if ($marca) $where .= " AND p.marca_id = $marca";
        if ($vend)  $where .= " AND f.vendedor = $vend";
        if ($tc)    $where .= " AND tc.id = $tc";

        if ($producto !== '') {
            $where .= " AND p.nombre LIKE '%" . addslashes($producto) . "%'";
        }

        if ($cliente !== '') {
            $clienteLike = addslashes($cliente);
            $where .= " AND (cli.nombre LIKE '%$clienteLike%' OR f.nombre_cliente LIKE '%$clienteLike%')";
        }

        $resumen = DB::SELECTONE(" 
            SELECT
                COUNT(DISTINCT f.id)            AS facturas,
                COALESCE(SUM(vhp.cantidad), 0)  AS unidades,
                COALESCE(SUM(vhp.sub_total_s),0) AS monto
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p              ON p.id = vhp.producto_id
            INNER JOIN sub_categoria sc        ON sc.id = p.sub_categoria_id
            INNER JOIN cliente cli             ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc         ON tc.id = cli.tipo_cliente_id
            WHERE $where
        ");

        $rows = DB::SELECT(" 
            SELECT
                f.id                                                 AS factura_id,
                COALESCE(NULLIF(f.cai, ''), NULLIF(f.numero_factura, ''), CONCAT('FAC-', f.id)) AS numero_factura,
                DATE_FORMAT(f.fecha_emision, '%Y-%m-%d')            AS fecha_facturacion,
                COALESCE(u.name, 'N/A')                              AS vendedor,
                COALESCE(NULLIF(cli.nombre, ''), f.nombre_cliente, 'N/A') AS cliente,
                SUM(vhp.cantidad)                                    AS cantidad_total,
                SUM(vhp.sub_total_s)                                 AS monto_producto
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p              ON p.id = vhp.producto_id
            INNER JOIN sub_categoria sc        ON sc.id = p.sub_categoria_id
            INNER JOIN cliente cli             ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc         ON tc.id = cli.tipo_cliente_id
            LEFT JOIN users u                  ON u.id = f.vendedor
            WHERE $where
            GROUP BY f.id, f.cai, f.numero_factura, f.fecha_emision, u.name, cli.nombre, f.nombre_cliente
            ORDER BY f.fecha_emision DESC, f.id DESC
            LIMIT 500
        ");

        foreach ($rows as &$r) {
            $r->cantidad_total = round((float)$r->cantidad_total, 2);
            $r->monto_producto = round((float)$r->monto_producto, 2);
        }

        $escala = [
            'Fechas: ' . $fi . ' a ' . $ff,
            $producto !== '' ? ('Producto: ' . $producto) : 'Producto: Todos',
            $cliente !== '' ? ('Cliente: ' . $cliente) : 'Cliente: Todos',
        ];

        return response()->json([
            'resumen' => [
                'facturas' => (int)($resumen->facturas ?? 0),
                'unidades' => round((float)($resumen->unidades ?? 0), 2),
                'monto'    => round((float)($resumen->monto ?? 0), 2),
            ],
            'escala_seleccionada' => implode(' | ', $escala),
            'facturas' => $rows,
        ]);
    }

    // ─── Top clientes por vendedor (P2) ─────────────────────────────────────
    public function topClientesPorVendedor(Request $request)
    {
        $fi    = $request->fecha_inicio ?? date('Y-m-01');
        $ff    = $request->fecha_final  ?? date('Y-m-d');
        $vend  = $request->vendedor      ? (int)$request->vendedor : null;
        $limit = $request->limite        ? (int)$request->limite   : 5;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($vend) $where .= " AND f.vendedor = $vend";

        $rows = DB::SELECT("
            SELECT
                cli.nombre                                  AS cliente,
                COUNT(DISTINCT f.id)                        AS facturas,
                SUM(f.total)                                AS total_comprado
            FROM factura f
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            WHERE $where
            GROUP BY f.cliente_id, cli.nombre
            ORDER BY total_comprado DESC
            LIMIT $limit
        ");

        foreach ($rows as &$r) {
            $r->total_comprado = round((float)$r->total_comprado, 2);
        }

        return response()->json($rows);
    }

    // ─── Filtros / catálogos ─────────────────────────────────────────────────
    public function catalogoFiltros()
    {
        $vendedores = DB::SELECT("
            SELECT DISTINCT u.id, u.name
            FROM users u
            INNER JOIN factura f ON f.vendedor = u.id
            WHERE f.estado_venta_id = 1
              AND u.estado_id = 1
            ORDER BY u.name
        ");

        $tiposCliente = DB::SELECT("SELECT id, descripcion FROM tipo_cliente ORDER BY descripcion");

        $categorias = DB::SELECT("SELECT id, descripcion FROM categoria_producto ORDER BY descripcion");

        $anios = DB::SELECT("
            SELECT DISTINCT YEAR(fecha_emision) AS anio
            FROM factura WHERE estado_venta_id = 1
            ORDER BY anio DESC
        ");

        $marcas = DB::SELECT("
            SELECT DISTINCT m.id, m.nombre
            FROM marca m
            INNER JOIN producto p ON p.marca_id = m.id
            INNER JOIN venta_has_producto vhp ON vhp.producto_id = p.id
            ORDER BY m.nombre
        ");

        $productoCodigoExpr = $this->productoCodigoExpr('p');

        $productos = DB::SELECT(" 
            SELECT DISTINCT p.id,
                   p.nombre AS nombre_producto,
                   $productoCodigoExpr AS codigo,
                   CASE
                       WHEN COALESCE($productoCodigoExpr, '') <> '' THEN CONCAT($productoCodigoExpr, ' - ', p.nombre)
                       ELSE p.nombre
                   END AS nombre
            FROM producto p
            ORDER BY p.id ASC
        ");

        $clientes = DB::SELECT(" 
            SELECT DISTINCT cli.id, cli.nombre
            FROM cliente cli
            INNER JOIN factura f ON f.cliente_id = cli.id
            WHERE f.estado_venta_id = 1
            ORDER BY cli.nombre
            LIMIT 1000
        ");

        $sucursales = DB::SELECT(" 
            SELECT DISTINCT b.id, b.nombre
            FROM bodega b
            INNER JOIN segmento sg ON sg.bodega_id = b.id
            INNER JOIN seccion s ON s.segmento_id = sg.id
            INNER JOIN venta_has_producto vhp ON vhp.seccion_id = s.id
            INNER JOIN factura f ON f.id = vhp.factura_id
            WHERE f.estado_venta_id = 1
            ORDER BY b.nombre
        ");

        $estadoFacturaExpr = $this->estadoFacturaLabelExpr('ef');
        $estadosFactura = DB::SELECT("SELECT ef.id, $estadoFacturaExpr AS descripcion FROM estado_factura ef ORDER BY descripcion");

        $canalesVenta = [];
        $canalExpr = $this->canalVentaExpr('f');
        if ($canalExpr) {
            $canalesVenta = DB::SELECT(" 
                SELECT DISTINCT $canalExpr AS canal
                FROM factura f
                WHERE f.estado_venta_id = 1
                  AND COALESCE($canalExpr, '') <> ''
                ORDER BY canal
            ");
        }

        return response()->json(compact(
            'vendedores',
            'tiposCliente',
            'categorias',
            'anios',
            'marcas',
            'productos',
            'clientes',
            'sucursales',
            'estadosFactura',
            'canalesVenta'
        ));
    }

    // ─── Evolución mensual de top clientes ─────────────────────────────────
    public function evolucionClientes(Request $request)
    {
        $fi      = $request->fecha_inicio ?? date('Y-01-01');
        $ff      = $request->fecha_final  ?? date('Y-m-d');
        $vend    = $request->vendedor      ? intval($request->vendedor)     : null;
        $tc      = $request->tipo_cliente  ? intval($request->tipo_cliente) : null;
        $cliente = trim($request->cliente  ?? '');
        $marca   = trim($request->marca    ?? '');
        $prod    = trim($request->producto ?? '');

        $where   = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?";
        $params  = [$fi, $ff];

        if ($vend)   { $where .= " AND f.vendedor = ?";           $params[] = $vend; }
        if ($tc)     { $where .= " AND f.tipo_cliente_id = ?";    $params[] = $tc;   }
        if ($cliente){ $where .= " AND cli.nombre LIKE ?";        $params[] = "%$cliente%"; }
        if ($marca)  {
            // si es numérico → id de marca; si no → like nombre
            if (is_numeric($marca)) {
                $where .= " AND p.marca_id = ?"; $params[] = intval($marca);
            } else {
                $where .= " AND m.nombre LIKE ?"; $params[] = "%$marca%";
            }
        }
        if ($prod) {
            if (is_numeric($prod)) { $where .= " AND p.id = ?"; $params[] = intval($prod); }
            else                   { $where .= " AND p.nombre LIKE ?"; $params[] = "%$prod%"; }
        }

        // Top 5 clientes en el rango
        $topSql = "
            SELECT cli.id, cli.nombre
            FROM factura f
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            LEFT JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            LEFT JOIN producto p ON p.id = vhp.producto_id
            LEFT JOIN marca m ON m.id = p.marca_id
            WHERE $where
            GROUP BY cli.id, cli.nombre
            ORDER BY SUM(vhp.sub_total_s) DESC
            LIMIT 5
        ";
        $top5 = DB::select($topSql, $params);

        if (empty($top5)) {
            return response()->json(['series' => [], 'meses' => []]);
        }

        // Meses en el rango
        $mesesSql = "
            SELECT DISTINCT DATE_FORMAT(f.fecha_emision, '%Y-%m') AS mes
            FROM factura f
            WHERE f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?
            ORDER BY mes
        ";
        $mesesRows = DB::select($mesesSql, [$fi, $ff]);
        $meses = array_column($mesesRows, 'mes');

        $series = [];
        foreach ($top5 as $cli) {
            $evWhere  = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ? AND cli.id = ?";
            $evParams = [$fi, $ff, $cli->id];
            if ($marca) {
                if (is_numeric($marca)) { $evWhere .= " AND p.marca_id = ?"; $evParams[] = intval($marca); }
                else                    { $evWhere .= " AND m.nombre LIKE ?"; $evParams[] = "%$marca%"; }
            }
            if ($prod) {
                if (is_numeric($prod)) { $evWhere .= " AND p.id = ?"; $evParams[] = intval($prod); }
                else                   { $evWhere .= " AND p.nombre LIKE ?"; $evParams[] = "%$prod%"; }
            }
            $evSql = "
                SELECT DATE_FORMAT(f.fecha_emision, '%Y-%m') AS mes,
                       COALESCE(SUM(vhp.sub_total_s),0) AS total
                FROM factura f
                INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
                INNER JOIN cliente cli ON cli.id = f.cliente_id
                LEFT JOIN producto p ON p.id = vhp.producto_id
                LEFT JOIN marca m ON m.id = p.marca_id
                WHERE $evWhere
                GROUP BY mes
            ";
            $evRows = DB::select($evSql, $evParams);
            $evMap  = [];
            foreach ($evRows as $r) { $evMap[$r->mes] = floatval($r->total); }
            $data = array_map(fn($m) => $evMap[$m] ?? 0, $meses);
            $series[] = ['name' => $cli->nombre, 'data' => $data];
        }

        return response()->json(['series' => $series, 'meses' => $meses]);
    }

    public function evolucionCantidadCli(Request $request)
    {
        $fi      = $request->fecha_inicio ?? date('Y-01-01');
        $ff      = $request->fecha_final  ?? date('Y-m-d');
        $vend    = $request->vendedor      ? intval($request->vendedor)     : null;
        $tc      = $request->tipo_cliente  ? intval($request->tipo_cliente) : null;
        $cliente = trim($request->cliente  ?? '');
        $marca   = trim($request->marca    ?? '');
        $prod    = trim($request->producto ?? '');

        $where  = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?";
        $params = [$fi, $ff];

        if ($vend)   { $where .= " AND f.vendedor = ?";           $params[] = $vend; }
        if ($tc)     { $where .= " AND f.tipo_cliente_id = ?";    $params[] = $tc;   }
        if ($cliente){ $where .= " AND cli.nombre LIKE ?";        $params[] = "%$cliente%"; }
        if ($marca)  {
            if (is_numeric($marca)) {
                $where .= " AND p.marca_id = ?"; $params[] = intval($marca);
            } else {
                $where .= " AND m.nombre LIKE ?"; $params[] = "%$marca%";
            }
        }
        if ($prod) {
            if (is_numeric($prod)) { $where .= " AND p.id = ?"; $params[] = intval($prod); }
            else                   { $where .= " AND p.nombre LIKE ?"; $params[] = "%$prod%"; }
        }

        $topSql = "
            SELECT cli.id, cli.nombre
            FROM factura f
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p ON p.id = vhp.producto_id
            LEFT JOIN marca m ON m.id = p.marca_id
            WHERE $where
            GROUP BY cli.id, cli.nombre
            ORDER BY SUM(vhp.cantidad) DESC
            LIMIT 5
        ";
        $top5 = DB::select($topSql, $params);

        if (empty($top5)) {
            return response()->json(['series' => [], 'meses' => []]);
        }

        $mesesSql = "
            SELECT DISTINCT DATE_FORMAT(f.fecha_emision, '%Y-%m') AS mes
            FROM factura f
            WHERE f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?
            ORDER BY mes
        ";
        $mesesRows = DB::select($mesesSql, [$fi, $ff]);
        $meses = array_column($mesesRows, 'mes');

        $series = [];
        foreach ($top5 as $cli) {
            $evWhere  = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ? AND cli.id = ?";
            $evParams = [$fi, $ff, $cli->id];
            if ($marca) {
                if (is_numeric($marca)) { $evWhere .= " AND p.marca_id = ?"; $evParams[] = intval($marca); }
                else                    { $evWhere .= " AND m.nombre LIKE ?"; $evParams[] = "%$marca%"; }
            }
            if ($prod) {
                if (is_numeric($prod)) { $evWhere .= " AND p.id = ?"; $evParams[] = intval($prod); }
                else                   { $evWhere .= " AND p.nombre LIKE ?"; $evParams[] = "%$prod%"; }
            }
            $evSql = "
                SELECT DATE_FORMAT(f.fecha_emision, '%Y-%m') AS mes,
                       COALESCE(SUM(vhp.cantidad), 0) AS total
                FROM factura f
                INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
                INNER JOIN cliente cli ON cli.id = f.cliente_id
                LEFT JOIN producto p ON p.id = vhp.producto_id
                LEFT JOIN marca m ON m.id = p.marca_id
                WHERE $evWhere
                GROUP BY mes
            ";
            $evRows = DB::select($evSql, $evParams);
            $evMap  = [];
            foreach ($evRows as $r) { $evMap[$r->mes] = floatval($r->total); }
            $data = array_map(fn($m) => $evMap[$m] ?? 0, $meses);
            $series[] = ['name' => $cli->nombre, 'data' => $data];
        }

        return response()->json(['series' => $series, 'meses' => $meses]);
    }

    // ─── PESTAÑA 3: Dashboard completo de productos ───────────────────────
    public function productosAnalitica(Request $request)
    {
        $fi            = $request->fecha_inicio ?? date('Y-01-01');
        $ff            = $request->fecha_final  ?? date('Y-m-d');
        $productoId    = $request->producto_id    ? (int)$request->producto_id    : null;
        $precioBaseExpr = $this->productoPrecioBaseExpr('p');
        $precioBaseVentaEscalaExpr = "COALESCE(
            ppc.precio_base_venta,
            (
                SELECT ppc2.precio_base_venta
                FROM categoria_precios cp2
                INNER JOIN precios_producto_carga ppc2 ON ppc2.categoria_precios_id = cp2.id
                WHERE cp2.cliente_categoria_escala_id = cli.cliente_categoria_escala_id
                  AND ppc2.producto_id = p.id
                  AND cp2.estado_id = 1
                  AND ppc2.estado_id = 1
                ORDER BY cp2.id ASC
                LIMIT 1
            ),
            $precioBaseExpr
        )";
        $cantidadFacturadaExpr = "(CASE
            WHEN COALESCE(vhp.sub_total_s, 0) > 0 THEN COALESCE(NULLIF(vhp.cantidad_s, 0), (vhp.sub_total_s / NULLIF(vhp.precio_unidad, 0)), vhp.cantidad)
            ELSE 0
        END)";
        $costoExpr = "($precioBaseVentaEscalaExpr * $cantidadFacturadaExpr)";
        $ventaFacturaExpr = "(vhp.precio_unidad * $cantidadFacturadaExpr)";
        $existenciaExpr = $this->productoExistenciaGlobalSinPaperlandExpr('p');

        $baseJoin = "
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p ON p.id = vhp.producto_id
            INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id
            INNER JOIN categoria_producto cp ON cp.id = sc.categoria_producto_id
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            LEFT JOIN users u ON u.id = f.vendedor
            LEFT JOIN seccion s ON s.id = vhp.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            LEFT JOIN estado_factura ef ON ef.id = f.estado_factura_id
            LEFT JOIN precios_producto_carga ppc ON ppc.id = vhp.precios_producto_carga_id
            LEFT JOIN categoria_precios cpesc ON cpesc.id = ppc.categoria_precios_id
            LEFT JOIN cliente_categoria_escala cce ON cce.id = COALESCE(cpesc.cliente_categoria_escala_id, cli.cliente_categoria_escala_id)
        ";

        $whereCommon = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";

        $whereWithProduct = $whereCommon;
        if ($productoId) {
            $whereWithProduct .= " AND p.id = $productoId";
        }

        $resumenGeneral = DB::SELECTONE(" 
            SELECT
                COALESCE(SUM(vhp.sub_total_s), 0) AS total_vendido,
                COALESCE(SUM($costoExpr), 0) AS costo_total,
                COALESCE(SUM(vhp.sub_total_s), 0) - COALESCE(SUM($costoExpr), 0) AS utilidad_bruta,
                COUNT(DISTINCT f.id) AS total_facturas,
                COUNT(DISTINCT f.cliente_id) AS total_clientes,
                COUNT(DISTINCT p.id) AS total_productos,
                COUNT(DISTINCT p.marca_id) AS total_marcas,
                COUNT(DISTINCT sc.categoria_producto_id) AS total_categorias,
                COALESCE(SUM($existenciaExpr), 0) AS inventario_unidades,
                COALESCE(SUM(vhp.cantidad), 0) AS unidades_vendidas,
                COALESCE(AVG(f.total), 0) AS ticket_promedio
            $baseJoin
            WHERE $whereWithProduct
        ");

        $resumenProducto = null;
        if ($productoId) {
            $resumenProducto = DB::SELECTONE(" 
                SELECT
                    p.id AS producto_id,
                    p.nombre AS producto,
                    {$this->productoCodigoExpr('p')} AS codigo,
                    COALESCE(m.nombre, 'N/A') AS marca,
                    cp.descripcion AS categoria,
                    COALESCE(MAX($precioBaseExpr), 0) AS precio_costo,
                    COALESCE(MAX($existenciaExpr), 0) AS existencia,
                    COALESCE(SUM(vhp.sub_total_s), 0) AS total_vendido,
                    COALESCE(SUM(vhp.cantidad), 0) AS unidades_vendidas,
                    COUNT(DISTINCT f.cliente_id) AS clientes_compraron,
                    MAX(f.fecha_emision) AS ultima_venta,
                    COALESCE(AVG(mensual.total_mes), 0) AS promedio_mensual
                FROM factura f
                INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
                INNER JOIN producto p ON p.id = vhp.producto_id
                LEFT JOIN marca m ON m.id = p.marca_id
                INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id
                INNER JOIN categoria_producto cp ON cp.id = sc.categoria_producto_id
                LEFT JOIN (
                    SELECT DATE_FORMAT(f2.fecha_emision, '%Y-%m') AS ym,
                           SUM(vhp2.sub_total_s) AS total_mes
                    FROM factura f2
                    INNER JOIN venta_has_producto vhp2 ON vhp2.factura_id = f2.id
                    WHERE f2.estado_venta_id = 1
                      AND f2.fecha_emision BETWEEN '$fi' AND '$ff'
                      AND vhp2.producto_id = $productoId
                    GROUP BY ym
                ) mensual ON 1 = 1
                WHERE f.estado_venta_id = 1
                  AND f.fecha_emision BETWEEN '$fi' AND '$ff'
                  AND vhp.producto_id = $productoId
                GROUP BY p.id, p.nombre, m.nombre, cp.descripcion
            ");
        }

        $serieDia = DB::SELECT(" 
            SELECT DATE_FORMAT(f.fecha_emision, '%Y-%m-%d') AS periodo,
                   COALESCE(SUM(vhp.sub_total_s), 0) AS total,
                   COALESCE(SUM(vhp.cantidad), 0) AS unidades
            $baseJoin
            WHERE $whereWithProduct
            GROUP BY periodo
            ORDER BY periodo
        ");

        $serieSemana = DB::SELECT(" 
            SELECT DATE_FORMAT(f.fecha_emision, '%x-W%v') AS periodo,
                   COALESCE(SUM(vhp.sub_total_s), 0) AS total,
                   COALESCE(SUM(vhp.cantidad), 0) AS unidades
            $baseJoin
            WHERE $whereWithProduct
            GROUP BY periodo
            ORDER BY periodo
        ");

        $serieMes = DB::SELECT(" 
            SELECT DATE_FORMAT(f.fecha_emision, '%Y-%m') AS periodo,
                   COALESCE(SUM(vhp.sub_total_s), 0) AS total,
                   COALESCE(SUM(vhp.cantidad), 0) AS unidades
            $baseJoin
            WHERE $whereWithProduct
            GROUP BY periodo
            ORDER BY periodo
        ");

        $topClientes = DB::SELECT(" 
            SELECT cli.id AS cliente_id,
                   cli.nombre AS cliente,
                   COUNT(DISTINCT f.id) AS compras,
                   COALESCE(SUM(vhp.sub_total_s), 0) AS monto,
                   COALESCE(SUM(vhp.cantidad), 0) AS unidades,
                   MAX(f.fecha_emision) AS ultima_compra
            $baseJoin
            WHERE $whereWithProduct
            GROUP BY cli.id, cli.nombre
            ORDER BY monto DESC
            LIMIT 10
        ");

        /* Histórico completo de clientes (para tabla paginada) */
        $rankingClientesTotal = DB::SELECT("
            SELECT cli.id AS cliente_id,
                   cli.nombre AS cliente,
                   COUNT(DISTINCT f.id) AS compras,
                   COALESCE(SUM(vhp.sub_total_s), 0) AS monto,
                   COALESCE(SUM(vhp.cantidad), 0) AS unidades,
                   MAX(f.fecha_emision) AS ultima_compra
            $baseJoin
            WHERE $whereWithProduct
            GROUP BY cli.id, cli.nombre
            ORDER BY monto DESC
        ");

        /* Top vendedores que mueven este producto */
        $topVendedores = DB::SELECT("
            SELECT u.id AS vendedor_id,
                   COALESCE(u.name, 'Sin asignar') AS vendedor,
                   COUNT(DISTINCT f.id) AS facturas,
                   COALESCE(SUM(vhp.sub_total_s), 0) AS monto,
                   COALESCE(SUM(vhp.cantidad), 0) AS unidades
            $baseJoin
            WHERE $whereWithProduct
            GROUP BY u.id, u.name
            ORDER BY monto DESC
            LIMIT 15
        ");

        $productosCliente = [];

        $comparativoProductos = DB::SELECT(" 
            SELECT p.id AS producto_id,
                   p.nombre AS producto,
                   COALESCE(SUM(vhp.sub_total_s), 0) AS total,
                   COALESCE(SUM(vhp.cantidad), 0) AS unidades
            $baseJoin
            WHERE $whereCommon
            GROUP BY p.id, p.nombre
            ORDER BY total DESC
            LIMIT 3
        ");

        $totalGeneral = (float)($resumenGeneral->total_vendido ?? 0);
        $totalProducto = $productoId ? (float)DB::SELECTONE(" 
            SELECT COALESCE(SUM(vhp.sub_total_s), 0) AS total
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p ON p.id = vhp.producto_id
            INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id
            LEFT JOIN seccion s ON s.id = vhp.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            WHERE $whereCommon AND p.id = $productoId
        ")->total : 0;

        $escalaClienteExpr = $this->clienteCategoriaEscalaLabelExpr('cce');
        $escalaPrecioExpr = $this->categoriaPreciosLabelExpr('cpesc');
        $estadoFacturaExpr = $this->estadoFacturaSimpleExpr('f');

        $whereFacturas = $whereCommon;
        if ($productoId) {
            $whereFacturas .= " AND p.id = $productoId";
        }

        $facturas = DB::SELECT(" 
            SELECT
                f.id AS factura_id,
                COALESCE(NULLIF(f.cai, ''), NULLIF(f.numero_factura, ''), CONCAT('FAC-', f.id)) AS numero_factura,
                DATE_FORMAT(f.fecha_emision, '%Y-%m-%d') AS fecha,
                MAX(CONCAT(
                    COALESCE(NULLIF(cli.nombre, ''), f.nombre_cliente, 'N/A'),
                    ' (',
                    COALESCE($escalaClienteExpr, CONCAT('Escala #', cli.cliente_categoria_escala_id), 'Sin escala'),
                    ')'
                )) AS cliente,
                COALESCE(u.name, 'N/A') AS vendedor,
                MAX(COALESCE($escalaPrecioExpr, CONCAT('Categoria #', cpesc.id), 'Sin categoria precio')) AS escala,
                p.nombre AS producto,
                COALESCE(SUM($cantidadFacturadaExpr), 0) AS cantidad,
                COALESCE(AVG(vhp.precio_unidad), 0) AS precio_unitario,
                COALESCE(AVG($precioBaseVentaEscalaExpr), 0) AS precio_base_venta,
                COALESCE(SUM(vhp.sub_total_s), 0) AS subtotal,
                COALESCE(SUM($costoExpr), 0) AS costo_total,
                COALESCE(SUM(vhp.sub_total_s), 0) - COALESCE(SUM($costoExpr), 0) AS utilidad_bruta,
                COALESCE(f.monto_descuento, 0) AS descuento,
                COALESCE(f.total, 0) AS total_factura,
                $estadoFacturaExpr AS estado
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p ON p.id = vhp.producto_id
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            LEFT JOIN users u ON u.id = f.vendedor
            LEFT JOIN estado_factura ef ON ef.id = f.estado_factura_id
            LEFT JOIN seccion s ON s.id = vhp.seccion_id
            LEFT JOIN segmento sg ON sg.id = s.segmento_id
            LEFT JOIN bodega b ON b.id = sg.bodega_id
            INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id
            LEFT JOIN precios_producto_carga ppc ON ppc.id = vhp.precios_producto_carga_id
            LEFT JOIN categoria_precios cpesc ON cpesc.id = ppc.categoria_precios_id
            LEFT JOIN cliente_categoria_escala cce ON cce.id = COALESCE(cpesc.cliente_categoria_escala_id, cli.cliente_categoria_escala_id)
                        WHERE $whereFacturas
            GROUP BY f.id, f.cai, f.numero_factura, f.fecha_emision, cli.nombre, f.nombre_cliente, u.name, p.nombre, f.monto_descuento, f.total, f.estado_factura_id
            ORDER BY f.fecha_emision DESC, f.id DESC
        ");

        $relacionados = [];
        if ($productoId) {
            $relacionados = DB::SELECT(" 
                SELECT
                    p2.id AS producto_id,
                    p2.nombre AS producto,
                    COUNT(DISTINCT f.id) AS veces_juntos,
                    COALESCE(SUM(v2.sub_total_s), 0) AS total_generado,
                    ROUND(
                        (COUNT(DISTINCT f.id) / NULLIF((
                            SELECT COUNT(DISTINCT fx.id)
                            FROM factura fx
                            INNER JOIN venta_has_producto vx ON vx.factura_id = fx.id
                            WHERE fx.estado_venta_id = 1
                              AND fx.fecha_emision BETWEEN '$fi' AND '$ff'
                              AND vx.producto_id = $productoId
                        ), 0)) * 100, 2
                    ) AS porcentaje_coincidencia
                FROM factura f
                INNER JOIN venta_has_producto v2 ON v2.factura_id = f.id
                INNER JOIN producto p2 ON p2.id = v2.producto_id
                WHERE f.estado_venta_id = 1
                  AND f.fecha_emision BETWEEN '$fi' AND '$ff'
                  AND f.id IN (
                      SELECT DISTINCT f1.id
                      FROM factura f1
                      INNER JOIN venta_has_producto v1 ON v1.factura_id = f1.id
                      WHERE f1.estado_venta_id = 1
                        AND f1.fecha_emision BETWEEN '$fi' AND '$ff'
                        AND v1.producto_id = $productoId
                  )
                  AND v2.producto_id <> $productoId
                GROUP BY p2.id, p2.nombre
                ORDER BY veces_juntos DESC, total_generado DESC
                LIMIT 10
            ");
        }

        $clienteMasCompra = collect($topClientes)->sortByDesc('monto')->first();
        $clienteMayorFrecuencia = collect($topClientes)->sortByDesc('compras')->first();
        $clienteMayorVolumen = collect($topClientes)->sortByDesc('unidades')->first();

        return response()->json([
            'resumen_general' => [
                'total_vendido'    => round((float)($resumenGeneral->total_vendido ?? 0), 2),
                'costo_total'      => round((float)($resumenGeneral->costo_total ?? 0), 2),
                'utilidad_bruta'   => round((float)($resumenGeneral->utilidad_bruta ?? 0), 2),
                'margen_porcentaje'=> ((float)($resumenGeneral->total_vendido ?? 0)) > 0
                    ? round((((float)$resumenGeneral->utilidad_bruta / (float)$resumenGeneral->total_vendido) * 100), 2)
                    : 0,
                'total_facturas'   => (int)($resumenGeneral->total_facturas ?? 0),
                'total_clientes'   => (int)($resumenGeneral->total_clientes ?? 0),
                'total_productos'  => (int)($resumenGeneral->total_productos ?? 0),
                'total_marcas'     => (int)($resumenGeneral->total_marcas ?? 0),
                'total_categorias' => (int)($resumenGeneral->total_categorias ?? 0),
                'inventario_unidades' => round((float)($resumenGeneral->inventario_unidades ?? 0), 2),
                'unidades_vendidas'=> round((float)($resumenGeneral->unidades_vendidas ?? 0), 2),
                'ticket_promedio'  => round((float)($resumenGeneral->ticket_promedio ?? 0), 2),
                'margen_generado'  => round((float)($resumenGeneral->utilidad_bruta ?? 0), 2),
            ],
            'resumen_producto' => $resumenProducto ? [
                'producto_id' => (int)$resumenProducto->producto_id,
                'producto' => $resumenProducto->producto,
                'codigo' => $resumenProducto->codigo,
                'marca' => $resumenProducto->marca,
                'categoria' => $resumenProducto->categoria,
                'precio_costo' => round((float)$resumenProducto->precio_costo, 2),
                'existencia' => round((float)$resumenProducto->existencia, 2),
                'total_vendido' => round((float)$resumenProducto->total_vendido, 2),
                'unidades_vendidas' => round((float)$resumenProducto->unidades_vendidas, 2),
                'clientes_compraron' => (int)$resumenProducto->clientes_compraron,
                'ultima_venta' => $resumenProducto->ultima_venta,
                'promedio_mensual' => round((float)$resumenProducto->promedio_mensual, 2),
            ] : null,
            'evolucion' => [
                'dia' => $serieDia,
                'semana' => $serieSemana,
                'mes' => $serieMes,
            ],
            'top_clientes' => $topClientes,
            'top_vendedores' => $topVendedores,
            'participacion' => [
                'producto' => round($totalProducto, 2),
                'resto' => round(max(0, $totalGeneral - $totalProducto), 2),
                'porcentaje_producto' => $totalGeneral > 0 ? round(($totalProducto / $totalGeneral) * 100, 2) : 0,
            ],
            'comparativo_productos' => $comparativoProductos,
            'tendencia_unidades' => $serieDia,
            'facturas' => $facturas,
            'productos_cliente' => $productosCliente,
            'ranking_clientes' => $rankingClientesTotal,
            'indicadores_clientes' => [
                'cliente_mas_compra' => $clienteMasCompra,
                'cliente_mayor_frecuencia' => $clienteMayorFrecuencia,
                'cliente_mayor_volumen' => $clienteMayorVolumen,
            ],
            'productos_relacionados' => $relacionados,
            'escala_seleccionada' => implode(' | ', [
                'Producto: ' . ($productoId ? $productoId : 'Todos'),
                'Fechas: ' . $fi . ' a ' . $ff,
            ]),
        ]);
    }

    // ─── Ventas por vendedor por día (semana) ────────────────────────────────
    public function ventasPorVendedorDia(Request $request)
    {
        $fi   = $request->fecha_inicio ?? date('Y-m-01');
        $ff   = $request->fecha_final  ?? date('Y-m-d');

        $rows = DB::SELECT("
            SELECT
                DATE_FORMAT(f.fecha_emision,'%Y-%m-%d') AS fecha,
                u.name                                   AS vendedor,
                SUM(f.total)                             AS total
            FROM factura f
            INNER JOIN users u ON u.id = f.vendedor
            WHERE f.estado_venta_id = 1
              AND f.fecha_emision BETWEEN '$fi' AND '$ff'
            GROUP BY fecha, u.id, u.name
            ORDER BY fecha, total DESC
        ");

        return response()->json($rows);
    }

    // ─── Participación por tipo cliente ─────────────────────────────────────
    public function participacionTipoCliente(Request $request)
    {
        $fi         = $request->fecha_inicio  ?? date('Y-01-01');
        $ff         = $request->fecha_final   ?? date('Y-m-d');
        $vend       = $request->vendedor       ? (int)$request->vendedor      : null;
        $tc         = $request->tipo_cliente   ? (int)$request->tipo_cliente  : null;
        $diaSemana  = $request->dia_semana     ?? null;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($vend)      $where .= " AND f.vendedor = $vend";
        if ($tc)        $where .= " AND tc.id = $tc";
        if ($diaSemana) $where .= " AND DAYNAME(f.fecha_emision) = '" . addslashes($diaSemana) . "'";

        $rows = DB::SELECT("
            SELECT
                tc.id                        AS tipo_id,
                tc.descripcion               AS tipo_cliente,
                COUNT(DISTINCT f.id)         AS facturas,
                SUM(f.total)                 AS total
            FROM factura f
            INNER JOIN cliente cli     ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id  = cli.tipo_cliente_id
            WHERE $where
            GROUP BY tc.id, tc.descripcion
            ORDER BY total DESC
        ");

        return response()->json($rows);
    }

    // ─── Marcas que más mueven los clientes (filtro cliente/producto/fecha) ──
    public function topMarcasCli(Request $request)
    {
        $fi      = $request->fecha_inicio ?? date('Y-01-01');
        $ff      = $request->fecha_final  ?? date('Y-m-d');
        $cliente = trim((string)($request->cliente  ?? ''));
        $prod    = trim((string)($request->producto ?? ''));

        $where  = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN ? AND ?";
        $params = [$fi, $ff];

        if ($cliente) { $where .= " AND cli.nombre LIKE ?"; $params[] = "%$cliente%"; }
        if ($prod) {
            if (is_numeric($prod)) { $where .= " AND p.id = ?"; $params[] = intval($prod); }
            else                   { $where .= " AND p.nombre LIKE ?"; $params[] = "%$prod%"; }
        }

        $rows = DB::select("
            SELECT
                m.id                                        AS marca_id,
                m.nombre                                    AS marca,
                COUNT(DISTINCT f.id)                        AS facturas,
                COUNT(DISTINCT f.cliente_id)                AS clientes,
                COUNT(DISTINCT p.id)                        AS productos,
                COALESCE(SUM(vhp.cantidad), 0)              AS unidades,
                COALESCE(SUM(vhp.sub_total_s), 0)           AS total_vendido
            FROM factura f
            INNER JOIN cliente cli             ON cli.id = f.cliente_id
            INNER JOIN venta_has_producto vhp  ON vhp.factura_id = f.id
            INNER JOIN producto p              ON p.id = vhp.producto_id
            INNER JOIN marca m                 ON m.id = p.marca_id
            WHERE $where
            GROUP BY m.id, m.nombre
            ORDER BY total_vendido DESC
        ", $params);

        $total = array_sum(array_column($rows, 'total_vendido'));
        foreach ($rows as &$r) {
            $r->total_vendido = round((float)$r->total_vendido, 2);
            $r->unidades      = intval($r->unidades);
            $r->participacion = $total > 0 ? round(($r->total_vendido / $total) * 100, 2) : 0;
        }

        return response()->json($rows);
    }

    // ─── Top marcas por ingresos ─────────────────────────────────────────────
    public function topMarcas(Request $request)
    {
        $fi   = $request->fecha_inicio ?? date('Y-01-01');
        $ff   = $request->fecha_final  ?? date('Y-m-d');
        $anio = $request->anio         ? (int)$request->anio : null;
        $mes  = $request->mes          ? (int)$request->mes  : null;
        $cli  = $request->cliente_id   ? (int)$request->cliente_id : null;
        $vend = $request->vendedor      ? (int)$request->vendedor : null;
        $cat  = $request->categoria     ? (int)$request->categoria : null;
        $suc  = $request->sucursal_id   ? (int)$request->sucursal_id : null;
        $canal = trim((string)($request->canal_venta ?? ''));
        $canalExpr = $this->canalVentaExpr('f');
        $costoExpr = $this->vhpCostoExpr('vhp');

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($anio) $where .= " AND YEAR(f.fecha_emision) = $anio";
        if ($mes)  $where .= " AND MONTH(f.fecha_emision) = $mes";
        if ($cli)  $where .= " AND f.cliente_id = $cli";
        if ($vend) $where .= " AND f.vendedor = $vend";
        if ($cat)  $where .= " AND sc.categoria_producto_id = $cat";
        if ($suc)  $where .= " AND b.id = $suc";
        if ($canalExpr && $canal !== '') $where .= " AND $canalExpr = '" . addslashes($canal) . "'";

        $rows = DB::SELECT("
            SELECT
                m.id                                        AS marca_id,
                m.nombre                                    AS marca,
                COUNT(DISTINCT f.id)                        AS facturas,
                COUNT(DISTINCT p.id)                        AS productos,
                SUM(vhp.cantidad)                           AS unidades_vendidas,
                SUM(vhp.sub_total_s)                        AS ingresos,
                SUM($costoExpr)                             AS costo_total,
                SUM(vhp.sub_total_s) - SUM($costoExpr)      AS utilidad,
                AVG(vhp.precio_unidad)                      AS precio_promedio
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p              ON p.id = vhp.producto_id
            INNER JOIN marca m                 ON m.id = p.marca_id
            INNER JOIN sub_categoria sc        ON sc.id = p.sub_categoria_id
            LEFT JOIN seccion s                ON s.id = vhp.seccion_id
            LEFT JOIN segmento sg              ON sg.id = s.segmento_id
            LEFT JOIN bodega b                 ON b.id = sg.bodega_id
            WHERE $where
            GROUP BY m.id, m.nombre
            ORDER BY ingresos DESC
        ");

        $total_global = array_sum(array_column($rows, 'ingresos'));
        $acumulado = 0;
        foreach ($rows as &$r) {
            $acumulado += $r->ingresos;
            $r->pareto          = $total_global > 0 ? round(($acumulado / $total_global) * 100, 2) : 0;
            $r->participacion   = $total_global > 0 ? round(($r->ingresos / $total_global) * 100, 2) : 0;
            $r->ingresos        = round((float)$r->ingresos, 2);
            $r->costo_total     = round((float)$r->costo_total, 2);
            $r->utilidad        = round((float)$r->utilidad, 2);
            $r->margen          = ((float)$r->ingresos) > 0 ? round((((float)$r->utilidad / (float)$r->ingresos) * 100), 2) : 0;
            $r->precio_promedio = round((float)$r->precio_promedio, 2);
        }

        return response()->json($rows);
    }

    // ─── Top categorías por ingresos/utilidad ───────────────────────────────
    public function topCategorias(Request $request)
    {
        $fi   = $request->fecha_inicio ?? date('Y-01-01');
        $ff   = $request->fecha_final  ?? date('Y-m-d');
        $anio = $request->anio         ? (int)$request->anio : null;
        $mes  = $request->mes          ? (int)$request->mes  : null;
        $cli  = $request->cliente_id   ? (int)$request->cliente_id : null;
        $vend = $request->vendedor     ? (int)$request->vendedor : null;
        $cat  = $request->categoria    ? (int)$request->categoria : null;
        $marca = $request->marca       ? (int)$request->marca : null;
        $suc  = $request->sucursal_id  ? (int)$request->sucursal_id : null;
        $canal = trim((string)($request->canal_venta ?? ''));

        $canalExpr = $this->canalVentaExpr('f');
        $costoExpr = $this->vhpCostoExpr('vhp');

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($anio) $where .= " AND YEAR(f.fecha_emision) = $anio";
        if ($mes)  $where .= " AND MONTH(f.fecha_emision) = $mes";
        if ($cli)  $where .= " AND f.cliente_id = $cli";
        if ($vend) $where .= " AND f.vendedor = $vend";
        if ($cat)  $where .= " AND sc.categoria_producto_id = $cat";
        if ($marca) $where .= " AND p.marca_id = $marca";
        if ($suc)  $where .= " AND b.id = $suc";
        if ($canalExpr && $canal !== '') $where .= " AND $canalExpr = '" . addslashes($canal) . "'";

        $rows = DB::SELECT(" 
            SELECT
                cp.id                                        AS categoria_id,
                cp.descripcion                               AS categoria,
                COUNT(DISTINCT p.id)                         AS productos,
                COUNT(DISTINCT f.id)                         AS facturas,
                SUM(vhp.cantidad)                            AS unidades_vendidas,
                SUM(vhp.sub_total_s)                         AS ingresos,
                SUM($costoExpr)                              AS costo_total,
                SUM(vhp.sub_total_s) - SUM($costoExpr)       AS utilidad
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p              ON p.id = vhp.producto_id
            INNER JOIN sub_categoria sc        ON sc.id = p.sub_categoria_id
            INNER JOIN categoria_producto cp   ON cp.id = sc.categoria_producto_id
            LEFT JOIN seccion s                ON s.id = vhp.seccion_id
            LEFT JOIN segmento sg              ON sg.id = s.segmento_id
            LEFT JOIN bodega b                 ON b.id = sg.bodega_id
            WHERE $where
            GROUP BY cp.id, cp.descripcion
            ORDER BY ingresos DESC
        ");

        $totalGlobal = array_sum(array_column($rows, 'ingresos'));
        foreach ($rows as &$r) {
            $r->ingresos = round((float)$r->ingresos, 2);
            $r->costo_total = round((float)$r->costo_total, 2);
            $r->utilidad = round((float)$r->utilidad, 2);
            $r->participacion = $totalGlobal > 0 ? round((((float)$r->ingresos / $totalGlobal) * 100), 2) : 0;
            $r->margen = ((float)$r->ingresos) > 0 ? round((((float)$r->utilidad / (float)$r->ingresos) * 100), 2) : 0;
        }

        return response()->json($rows);
    }

    // ─── Ventas mensuales por múltiples vendedores (comparación) ────────────
    public function ventasMesVendedores(Request $request)
    {
        $fi    = $request->fecha_inicio ?? date('Y-01-01');
        $ff    = $request->fecha_final  ?? date('Y-m-d');
        $vends = $request->vendedores   ?? [];

        if (!is_array($vends)) $vends = explode(',', $vends);
        $vends = array_filter(array_map('intval', $vends));

        if (empty($vends)) {
            return response()->json([]);
        }

        $inVends = implode(',', $vends);

        $rows = DB::SELECT("
            SELECT
                u.id                                        AS vendedor_id,
                u.name                                      AS vendedor,
                MONTH(f.fecha_emision)                      AS mes,
                COALESCE(SUM(f.sub_total), 0)               AS total,
                COUNT(DISTINCT f.id)                        AS facturas
            FROM factura f
            INNER JOIN users u ON u.id = f.vendedor
            WHERE f.estado_venta_id = 1
              AND f.fecha_emision BETWEEN '$fi' AND '$ff'
              AND f.vendedor IN ($inVends)
            GROUP BY u.id, u.name, mes
            ORDER BY u.name, mes
        ");

        foreach ($rows as &$r) {
            $r->total = round((float)$r->total, 2);
        }

        return response()->json($rows);
    }

    // ─── COMPARAR: escalas por vendedor ────────────────────────────────────
    public function escalasComparacion(Request $request)
    {
        $fi           = $request->fecha_inicio ?? date('Y-01-01');
        $ff           = $request->fecha_final  ?? date('Y-m-d');
        $vendedoresRaw = $request->vendedores  ?? '';

        if (!$vendedoresRaw) return response()->json([]);

        if (is_array($vendedoresRaw)) {
            $ids = array_filter(array_map('intval', $vendedoresRaw));
        } else {
            $ids = array_filter(array_map('intval', explode(',', $vendedoresRaw)));
        }
        if (empty($ids)) return response()->json([]);

        $inIds = implode(',', $ids);
        $escalaNombreExpr = $this->categoriaPreciosLabelExpr('cpesc');

        $rows = DB::SELECT("
            SELECT
                u.id                                             AS vendedor_id,
                u.name                                           AS vendedor,
                COALESCE(cpesc.id, 0)                            AS escala_id,
                COALESCE($escalaNombreExpr, 'Sin categoría')     AS escala,
                COUNT(DISTINCT f.id)                             AS facturas,
                COALESCE(SUM(vhp.sub_total_s), 0)               AS total_sin_isv
            FROM factura f
            INNER JOIN users u                   ON u.id  = f.vendedor
            INNER JOIN venta_has_producto vhp    ON vhp.factura_id = f.id
            LEFT  JOIN precios_producto_carga ppc ON ppc.id = vhp.precios_producto_carga_id
            LEFT  JOIN categoria_precios cpesc   ON cpesc.id = ppc.categoria_precios_id
            WHERE f.estado_venta_id = 1
              AND f.fecha_emision BETWEEN '$fi' AND '$ff'
              AND f.vendedor IN ($inIds)
            GROUP BY u.id, u.name, cpesc.id, $escalaNombreExpr
            ORDER BY u.name, total_sin_isv DESC
        ");

        /* Agrupar por vendedor y calcular porcentaje */
        $byVend = [];
        foreach ($rows as $r) {
            $vid = $r->vendedor_id;
            if (!isset($byVend[$vid])) {
                $byVend[$vid] = [
                    'vendedor_id' => $vid,
                    'vendedor'    => $r->vendedor,
                    'total'       => 0,
                    'escalas'     => [],
                ];
            }
            $byVend[$vid]['total'] += (float)$r->total_sin_isv;
            $byVend[$vid]['escalas'][] = [
                'escala_id'    => (int)$r->escala_id,
                'escala'       => $r->escala,
                'facturas'     => (int)$r->facturas,
                'total_sin_isv' => round((float)$r->total_sin_isv, 2),
            ];
        }

        foreach ($byVend as &$vd) {
            $vd['total'] = round($vd['total'], 2);
            foreach ($vd['escalas'] as &$esc) {
                $esc['pct'] = $vd['total'] > 0
                    ? round($esc['total_sin_isv'] / $vd['total'] * 100, 1)
                    : 0;
            }
        }

        return response()->json(array_values($byVend));
    }
}
