<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;

class DashboardVentas extends Component
{
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
                COALESCE(SUM(f.isv), 0)                       AS total_isv
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
                   COALESCE(AVG(f.total),0) AS ticket_promedio
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
                AVG(f.total)                               AS ticket_promedio
            FROM factura f
            INNER JOIN users u         ON u.id  = f.vendedor
            INNER JOIN cliente cli     ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id  = cli.tipo_cliente_id
            WHERE $where
            GROUP BY u.id, u.name
            ORDER BY total_ventas DESC
        ");

        $totalGlobal = array_sum(array_column($rows, 'total_ventas'));

        foreach ($rows as &$r) {
            $r->participacion = $totalGlobal > 0
                ? round(($r->total_ventas / $totalGlobal) * 100, 2)
                : 0;
            $r->total_ventas    = round((float)$r->total_ventas, 2);
            $r->ticket_promedio = round((float)$r->ticket_promedio, 2);
        }

        return response()->json($rows);
    }

    // ─── PESTAÑA 3: Top clientes ────────────────────────────────────────────
    public function topClientes(Request $request)
    {
        $fi    = $request->fecha_inicio ?? date('Y-01-01');
        $ff    = $request->fecha_final  ?? date('Y-m-d');
        $vend  = $request->vendedor      ? (int)$request->vendedor      : null;
        $limit = $request->limite        ? (int)$request->limite        : 20;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($vend) $where .= " AND f.vendedor = $vend";

        $rows = DB::SELECT("
            SELECT
                cli.nombre                                  AS cliente,
                tc.descripcion                              AS tipo_cliente,
                COUNT(DISTINCT f.id)                        AS facturas,
                SUM(f.total)                                AS total_comprado,
                AVG(f.total)                                AS ticket_promedio,
                MAX(f.fecha_emision)                        AS ultima_compra,
                DATEDIFF(CURDATE(), MAX(f.fecha_emision))   AS dias_sin_comprar
            FROM factura f
            INNER JOIN cliente cli     ON cli.id = f.cliente_id
            INNER JOIN tipo_cliente tc ON tc.id  = cli.tipo_cliente_id
            WHERE $where
            GROUP BY f.cliente_id, cli.nombre, tc.descripcion
            ORDER BY total_comprado DESC
            LIMIT $limit
        ");

        // Clasificación ABC: A=70%, B=20%, C=10%
        $total_global = array_sum(array_column($rows, 'total_comprado'));
        $acumulado = 0;
        foreach ($rows as &$r) {
            $acumulado += $r->total_comprado;
            $pct = $total_global > 0 ? ($acumulado / $total_global) * 100 : 0;
            $r->clasificacion_abc = $pct <= 70 ? 'A' : ($pct <= 90 ? 'B' : 'C');
            $r->total_comprado    = round((float)$r->total_comprado, 2);
            $r->ticket_promedio   = round((float)$r->ticket_promedio, 2);
            $r->recurrente        = $r->facturas >= 3;
            $r->inactivo          = $r->dias_sin_comprar > 60;
        }

        return response()->json($rows);
    }

    // ─── PESTAÑA 3: Top productos ───────────────────────────────────────────
    public function topProductos(Request $request)
    {
        $fi    = $request->fecha_inicio ?? date('Y-01-01');
        $ff    = $request->fecha_final  ?? date('Y-m-d');
        $cat   = $request->categoria    ? (int)$request->categoria    : null;
        $limit = $request->limite       ? (int)$request->limite       : 20;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($cat) $where .= " AND sc.categoria_producto_id = $cat";

        $rows = DB::SELECT("
            SELECT
                p.nombre                                    AS producto,
                cp.descripcion                              AS categoria,
                sc.descripcion                              AS subcategoria,
                SUM(vhp.cantidad)                           AS unidades_vendidas,
                SUM(vhp.sub_total_s)                        AS ingresos,
                COUNT(DISTINCT f.id)                        AS apariciones,
                AVG(vhp.precio_unidad)                      AS precio_promedio
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id   = f.id
            INNER JOIN producto p              ON p.id             = vhp.producto_id
            INNER JOIN sub_categoria sc        ON sc.id            = p.sub_categoria_id
            INNER JOIN categoria_producto cp   ON cp.id            = sc.categoria_producto_id
            WHERE $where
            GROUP BY p.id, p.nombre, cp.descripcion, sc.descripcion
            ORDER BY ingresos DESC
            LIMIT $limit
        ");

        $total_global = array_sum(array_column($rows, 'ingresos'));
        $acumulado = 0;
        foreach ($rows as &$r) {
            $acumulado += $r->ingresos;
            $r->pareto       = $total_global > 0 ? round(($acumulado / $total_global) * 100, 2) : 0;
            $r->ingresos     = round((float)$r->ingresos, 2);
            $r->precio_promedio = round((float)$r->precio_promedio, 2);
        }

        return response()->json($rows);
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
            ORDER BY u.name
        ");

        $tiposCliente = DB::SELECT("SELECT id, descripcion FROM tipo_cliente ORDER BY descripcion");

        $categorias = DB::SELECT("SELECT id, descripcion FROM categoria_producto ORDER BY descripcion");

        $anios = DB::SELECT("
            SELECT DISTINCT YEAR(fecha_emision) AS anio
            FROM factura WHERE estado_venta_id = 1
            ORDER BY anio DESC
        ");

        return response()->json(compact('vendedores', 'tiposCliente', 'categorias', 'anios'));
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

    // ─── Top marcas por ingresos ─────────────────────────────────────────────
    public function topMarcas(Request $request)
    {
        $fi   = $request->fecha_inicio ?? date('Y-01-01');
        $ff   = $request->fecha_final  ?? date('Y-m-d');
        $vend = $request->vendedor      ? (int)$request->vendedor : null;
        $cat  = $request->categoria     ? (int)$request->categoria : null;

        $where = "f.estado_venta_id = 1 AND f.fecha_emision BETWEEN '$fi' AND '$ff'";
        if ($vend) $where .= " AND f.vendedor = $vend";
        if ($cat)  $where .= " AND sc.categoria_producto_id = $cat";

        $rows = DB::SELECT("
            SELECT
                m.id                                        AS marca_id,
                m.nombre                                    AS marca,
                COUNT(DISTINCT f.id)                        AS facturas,
                COUNT(DISTINCT p.id)                        AS productos,
                SUM(vhp.cantidad)                           AS unidades_vendidas,
                SUM(vhp.sub_total_s)                        AS ingresos,
                AVG(vhp.precio_unidad)                      AS precio_promedio
            FROM factura f
            INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
            INNER JOIN producto p              ON p.id = vhp.producto_id
            INNER JOIN marca m                 ON m.id = p.marca_id
            INNER JOIN sub_categoria sc        ON sc.id = p.sub_categoria_id
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
            $r->precio_promedio = round((float)$r->precio_promedio, 2);
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
                SUM(f.total)                                AS total,
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
}
