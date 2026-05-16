<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Carbon\Carbon;

class MisComisiones extends Component
{
    public function render()
    {
        $userId = Auth::id();

        $info = DB::table('users as u')
            ->join('rol as r', 'r.id', '=', 'u.rol_id')
            ->select('u.name', 'u.id', 'r.nombre as rol')
            ->where('u.id', $userId)
            ->first();

        // KPIs resumen global del empleado
        $kpis = DB::selectOne("
            SELECT
                COALESCE(SUM(ce.comision_acumulada), 0)                        AS total_historico,
                COALESCE(SUM(CASE WHEN YEAR(ce.mes_comision)  = YEAR(CURDATE())
                                   AND MONTH(ce.mes_comision) = MONTH(CURDATE())
                               THEN ce.comision_acumulada ELSE 0 END), 0)      AS total_mes_actual,
                COALESCE(SUM(CASE WHEN YEAR(ce.mes_comision) = YEAR(CURDATE())
                               THEN ce.comision_acumulada ELSE 0 END), 0)      AS total_anio_actual,
                COUNT(DISTINCT ce.mes_comision)                                AS meses_activos,
                COUNT(DISTINCT fc.factura_id)                                  AS facturas_totales,
                COUNT(DISTINCT CASE
                    WHEN YEAR(fc.fecha_cierre_factura)  = YEAR(CURDATE())
                     AND MONTH(fc.fecha_cierre_factura) = MONTH(CURDATE())
                    THEN fc.factura_id END)                                    AS facturas_mes_actual
            FROM comision_empleado ce
            LEFT JOIN facturas_comision fc
                ON  fc.rol_id    = ce.rol_id
                AND fc.estado_id = 1
                AND YEAR(fc.fecha_cierre_factura)  = YEAR(ce.mes_comision)
                AND MONTH(fc.fecha_cierre_factura) = MONTH(ce.mes_comision)
            WHERE ce.users_comision = ?
        ", [$userId]);

        // Histórico mensual para gráfica
        $historicoMeses = DB::select("
            SELECT
                DATE_FORMAT(mes_comision, '%Y-%m') AS periodo,
                CONCAT(
                    CASE MONTH(mes_comision)
                        WHEN 1 THEN 'Ene' WHEN 2 THEN 'Feb' WHEN 3 THEN 'Mar'
                        WHEN 4 THEN 'Abr' WHEN 5 THEN 'May' WHEN 6 THEN 'Jun'
                        WHEN 7 THEN 'Jul' WHEN 8 THEN 'Ago' WHEN 9 THEN 'Sep'
                        WHEN 10 THEN 'Oct' WHEN 11 THEN 'Nov' WHEN 12 THEN 'Dic'
                    END, ' ', YEAR(mes_comision)
                ) AS etiqueta,
                SUM(comision_acumulada) AS comision_acumulada,
                mes_comision
            FROM comision_empleado
            WHERE users_comision = ?
              AND estado_id = 1
            GROUP BY mes_comision
            ORDER BY mes_comision DESC
            LIMIT 12
        ", [$userId]);

        // Mes actual info
        $mesActual = Carbon::now()->startOfMonth()->toDateString();
        $comisionMesActual = DB::table('comision_empleado')
            ->where('users_comision', $userId)
            ->where('mes_comision', $mesActual)
            ->where('estado_id', 1)
            ->sum('comision_acumulada');

        // Mejor mes (total por mes)
        $mejorMes = DB::selectOne("
            SELECT mes_comision,
                   SUM(comision_acumulada) AS comision_acumulada
            FROM comision_empleado
            WHERE users_comision = ?
              AND estado_id = 1
            GROUP BY mes_comision
            ORDER BY comision_acumulada DESC
            LIMIT 1
        ", [$userId]);

        return view('livewire.comisiones.escalado.mis-comisiones', compact(
            'info', 'kpis', 'historicoMeses', 'comisionMesActual', 'mejorMes'
        ));
    }

    /* ── Histórico mensual (DataTable) ─────────────────────────────────── */
    public function listarComisionesEmpleado()
    {
        $userId = Auth::id();

        $datos = DB::select("
            SELECT
                ce.mes_comision,
                CASE MONTH(ce.mes_comision)
                    WHEN 1 THEN 'Enero'    WHEN 2 THEN 'Febrero'  WHEN 3 THEN 'Marzo'
                    WHEN 4 THEN 'Abril'    WHEN 5 THEN 'Mayo'     WHEN 6 THEN 'Junio'
                    WHEN 7 THEN 'Julio'    WHEN 8 THEN 'Agosto'   WHEN 9 THEN 'Septiembre'
                    WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                END AS mes_letra,
                YEAR(ce.mes_comision)          AS anio,
                r.nombre                       AS rol,
                ce.comision_acumulada,
                ce.fecha_ult_modificacion,
                COUNT(DISTINCT fc.factura_id)  AS cantidad_facturas,
                COALESCE(SUM(pc.monto_comision * pc.cantidad), 0) AS monto_productos,
                CASE
                    WHEN YEAR(ce.mes_comision)  = YEAR(CURDATE())
                     AND MONTH(ce.mes_comision) = MONTH(CURDATE()) THEN 1
                    ELSE 0
                END AS es_mes_actual
            FROM comision_empleado ce
            LEFT JOIN rol r ON r.id = ce.rol_id
            LEFT JOIN facturas_comision fc
                ON  fc.rol_id    = ce.rol_id
                AND fc.estado_id = 1
                AND YEAR(fc.fecha_cierre_factura)  = YEAR(ce.mes_comision)
                AND MONTH(fc.fecha_cierre_factura) = MONTH(ce.mes_comision)
            LEFT JOIN producto_comision pc
                ON pc.facturas_comision_id = fc.id
                AND pc.estado_id = 1
            WHERE ce.users_comision = ?
            GROUP BY
                ce.mes_comision, mes_letra, anio, r.nombre,
                ce.comision_acumulada, ce.fecha_ult_modificacion, es_mes_actual
            ORDER BY ce.mes_comision DESC
        ", [$userId]);

        return Datatables::of($datos)
            ->addColumn('badge_mes', function ($row) {
                if ($row->es_mes_actual) {
                    return '<span style="background:#10b981;color:#fff;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;">MES ACTUAL</span>';
                }
                return '';
            })
            ->addColumn('comision_fmt', function ($row) {
                return 'L ' . number_format($row->comision_acumulada, 2);
            })
            ->rawColumns(['badge_mes'])
            ->make(true);
    }

    /* ── Top 10 productos más comisionados ─────────────────────────────── */
    public function topProductos(Request $request)
    {
        $userId  = Auth::id();
        $periodo = $request->input('periodo'); // 'mes', 'anio', 'todo'

        $whereTime = match ($periodo) {
            'mes'  => "AND YEAR(fc.fecha_cierre_factura)  = YEAR(CURDATE())
                        AND MONTH(fc.fecha_cierre_factura) = MONTH(CURDATE())",
            'anio' => "AND YEAR(fc.fecha_cierre_factura)  = YEAR(CURDATE())",
            default => "",
        };

        $datos = DB::select("
            SELECT
                p.nombre                             AS producto,
                SUM(pc.cantidad)                     AS unidades,
                SUM(pc.monto_comision * pc.cantidad) AS monto_total,
                AVG(pc.precio_venta)                 AS precio_promedio,
                COUNT(DISTINCT fc.factura_id)        AS en_facturas
            FROM producto_comision pc
            INNER JOIN facturas_comision fc  ON fc.id = pc.facturas_comision_id AND fc.estado_id = 1
            INNER JOIN producto p            ON p.id  = pc.producto_id
            WHERE pc.estado_id = 1
              AND fc.rol_id IN (
                  SELECT rol_id FROM comision_empleado WHERE users_comision = ?
              )
              $whereTime
            GROUP BY p.id, p.nombre
            ORDER BY monto_total DESC
            LIMIT 10
        ", [$userId]);

        return response()->json(['data' => $datos]);
    }

    /* ── Resumen por mes para gráficas (AJAX) ───────────────────────────── */
    public function chartMensual(Request $request)
    {
        $userId = Auth::id();

        $datos = DB::select("
            SELECT
                DATE_FORMAT(mes_comision, '%Y-%m') AS periodo,
                CONCAT(
                    CASE MONTH(mes_comision)
                        WHEN 1 THEN 'Ene' WHEN 2 THEN 'Feb' WHEN 3 THEN 'Mar'
                        WHEN 4 THEN 'Abr' WHEN 5 THEN 'May' WHEN 6 THEN 'Jun'
                        WHEN 7 THEN 'Jul' WHEN 8 THEN 'Ago' WHEN 9 THEN 'Sep'
                        WHEN 10 THEN 'Oct' WHEN 11 THEN 'Nov' WHEN 12 THEN 'Dic'
                    END, ' ', YEAR(mes_comision)
                ) AS etiqueta,
                SUM(comision_acumulada) AS comision_acumulada
            FROM comision_empleado
            WHERE users_comision = ?
              AND estado_id = 1
            GROUP BY mes_comision
            ORDER BY mes_comision ASC
            LIMIT 18
        ", [$userId]);

        return response()->json(['data' => $datos]);
    }

    /* ── Detalle de facturas de un mes específico ───────────────────────── */
    public function detalleFacturasMes(Request $request)
    {
        $userId  = Auth::id();
        $periodo = $request->input('periodo'); // formato 'YYYY-MM'

        if (!$periodo) {
            return response()->json(['data' => []]);
        }

        $datos = DB::select("
            SELECT
                fc.factura_id,
                fc.fecha_cierre_factura,
                fc.monto_rol,
                r.nombre        AS rol,
                cl.nombre       AS cliente,
                COUNT(pc.id)    AS productos,
                SUM(pc.cantidad) AS unidades
            FROM facturas_comision fc
            INNER JOIN rol r        ON r.id  = fc.rol_id
            INNER JOIN factura f    ON f.id  = fc.factura_id
            INNER JOIN cliente cl   ON cl.id = f.cliente_id
            LEFT  JOIN producto_comision pc ON pc.facturas_comision_id = fc.id AND pc.estado_id = 1
            WHERE fc.estado_id = 1
              AND fc.rol_id IN (
                  SELECT rol_id FROM comision_empleado WHERE users_comision = ?
              )
              AND DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m') = ?
            GROUP BY fc.id, fc.factura_id, fc.fecha_cierre_factura, fc.monto_rol, r.nombre, cl.nombre
            ORDER BY fc.fecha_cierre_factura DESC
        ", [$userId, $periodo]);

        return Datatables::of($datos)
            ->addColumn('monto_fmt', fn($r) => 'L ' . number_format($r->monto_rol, 2))
            ->rawColumns([])
            ->make(true);
    }
}
