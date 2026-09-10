<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Exports\LibroCobrosExport;
use Maatwebsite\Excel\Facades\Excel;

class Librocobrosrep extends Component
{
    public function render()
    {
        $clientes   = DB::table('cliente')->orderBy('nombre')->get(['id','nombre']);
        $vendedores = DB::table('users')->orderBy('name')->get(['id','name']);
        $bancos     = DB::table('banco')->orderBy('nombre')->whereNotIn('id', [12, 13])->get(['id','nombre']);

        return view('livewire.reportes.librocobrosrep', compact('clientes','vendedores','bancos'));
    }

    public function consulta(Request $request, $tipo = null, $fechaInicio = null, $fechaFinal = null)
    {
        try {
            $tipo        = $request->input('tipo', $tipo ?? '3');
            $fechaInicio = $request->input('fecha_desde', $fechaInicio ?? '1900-01-01');
            $fechaFinal  = $request->input('fecha_hasta', $fechaFinal  ?? date('Y-m-d'));

            if ($request->has('fecha_desde') && !$request->input('fecha_desde')) {
                $fechaInicio = '1900-01-01';
            }
            if ($request->has('fecha_hasta') && !$request->input('fecha_hasta')) {
                $fechaFinal = date('Y-m-d');
            }

            $cliente = $request->input('cliente');
            $vendedor = $request->input('vendedor');
            $teleasesor = $request->input('teleasesor');
            $gestor = $request->input('gestor');
            $banco   = $request->input('banco');
            $factura = $request->input('factura');

            $hasFecha = !empty($fechaInicio) && $fechaInicio !== 'todos'
                     && !empty($fechaFinal)   && $fechaFinal   !== 'todos';

            if ($tipo == 3) {
                $consulta = $this->queryTipo3Data($fechaInicio, $fechaFinal, $cliente, $vendedor, $teleasesor, $gestor, $banco, $factura);
                $kpiTotalCobrado = array_sum(array_map(
                    fn($row) => (int) ($row->impacta_kpi ?? 0) === 1 ? (float) $row->monto_cobrado : 0,
                    $consulta
                ));
                $facturasPagadas = [];
                foreach ($consulta as $row) {
                    if ($row->estado_factura === 'PAGADA') {
                        $facturasPagadas[$row->factura] = true;
                    }
                }

                return Datatables::of($consulta)
                    ->with([
                        'kpi_cobros' => count($consulta),
                        'kpi_total_cobrado' => round($kpiTotalCobrado, 2),
                        'kpi_completas' => count($facturasPagadas),
                    ])
                    ->rawColumns([])
                    ->make(true);

                $sql = "
                    SELECT *
                    FROM (
                        SELECT
                            inner_sub.*,
                            MAX(CASE WHEN inner_sub.estado_factura = 'PAGADA' THEN 1 ELSE 0 END)
                                OVER (PARTITION BY inner_sub.factura) AS factura_tiene_pagada
                        FROM (
                            SELECT
                                DATE_FORMAT(f.fecha_emision, '%Y-%m-%d')         AS fecha_venta,
                                DATE_FORMAT(f.fecha_vencimiento, '%Y-%m-%d')     AS fecha_vencimiento,
                                DATE_FORMAT(ac.fecha_pago, '%Y-%m-%d')          AS fecha_pago,
                                f.nombre_cliente                                 AS cliente,
                                COALESCE(u.name, '')                              AS asesor_comercial,
                                COALESCE(tele.name, '')                           AS teleasesor,
                                f.numero_secuencia_cai                          AS factura,
                                ROUND(ac.monto_abonado, 2)                      AS monto_cobrado,
                                GREATEST(
                                    ROUND(
                                        f.total - COALESCE(nc.total_notas_credito, 0) - SUM(ac.monto_abonado) OVER (
                                            PARTITION BY ac.factura_id
                                            ORDER BY ac.fecha_pago ASC, ac.id ASC
                                            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                                        ), 2
                                    ), 0
                                )                                                AS saldo_pendiente,
                                CASE
                                    WHEN GREATEST(
                                        ROUND(
                                            f.total - COALESCE(nc.total_notas_credito, 0) - SUM(ac.monto_abonado) OVER (
                                                PARTITION BY ac.factura_id
                                                ORDER BY ac.fecha_pago ASC, ac.id ASC
                                                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                                            ), 2
                                        ), 0
                                    ) <= 0.01 THEN 'PAGADA'
                                    ELSE 'PARCIAL'
                                END                                              AS estado_factura,
                                b.nombre                                         AS banco,
                                ac.comentario                                    AS observaciones,
                                ROUND(
                                    CASE
                                        WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                            (CASE
                                                WHEN COALESCE(f.total, 0) > 0
                                                    THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                                ELSE ROUND(ac.monto_abonado, 2)
                                            END)
                                            * (CASE
                                                WHEN f.tipo_venta_id = 3 THEN COALESCE(f.sub_total, 0)
                                                ELSE GREATEST(
                                                    COALESCE(f.sub_total, 0)
                                                    - COALESCE(f.sub_total_grabado, 0)
                                                    - COALESCE(f.sub_total_excento, 0),
                                                    0
                                                )
                                            END)
                                            / COALESCE(f.sub_total, 1)
                                        )
                                        ELSE 0
                                    END,
                                2)                                               AS exonerado,
                                ROUND(
                                    CASE
                                        WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                            (CASE
                                                WHEN COALESCE(f.total, 0) > 0
                                                    THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                                ELSE ROUND(ac.monto_abonado, 2)
                                            END)
                                            * COALESCE(f.sub_total_grabado, 0)
                                            / COALESCE(f.sub_total, 1)
                                        )
                                        ELSE 0
                                    END,
                                2)                                               AS gravado,
                                ROUND(
                                    (CASE
                                        WHEN COALESCE(f.total, 0) > 0
                                            THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                        ELSE ROUND(ac.monto_abonado, 2)
                                    END)
                                    -
                                    ROUND(
                                        CASE
                                            WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                                (CASE
                                                    WHEN COALESCE(f.total, 0) > 0
                                                        THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                                    ELSE ROUND(ac.monto_abonado, 2)
                                                END)
                                                * (CASE
                                                    WHEN f.tipo_venta_id = 3 THEN COALESCE(f.sub_total, 0)
                                                    ELSE GREATEST(
                                                        COALESCE(f.sub_total, 0)
                                                        - COALESCE(f.sub_total_grabado, 0)
                                                        - COALESCE(f.sub_total_excento, 0),
                                                        0
                                                    )
                                                END)
                                                / COALESCE(f.sub_total, 1)
                                            )
                                            ELSE 0
                                        END,
                                    2)
                                    -
                                    ROUND(
                                        CASE
                                            WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                                (CASE
                                                    WHEN COALESCE(f.total, 0) > 0
                                                        THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                                    ELSE ROUND(ac.monto_abonado, 2)
                                                END)
                                                * COALESCE(f.sub_total_grabado, 0)
                                                / COALESCE(f.sub_total, 1)
                                            )
                                            ELSE 0
                                        END,
                                    2),
                                2)                                               AS excento,
                                ROUND(
                                    CASE
                                        WHEN COALESCE(f.total, 0) > 0
                                            THEN ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1)
                                        ELSE ac.monto_abonado
                                    END,
                                2)                                               AS subtotal,
                                ROUND(
                                    CASE
                                        WHEN f.tipo_venta_id = 3 THEN 0
                                        WHEN COALESCE(f.total, 0) > 0 THEN
                                            ac.monto_abonado - ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                        ELSE 0
                                    END,
                                2)                                               AS isv,
                                ROUND(ac.monto_abonado, 2)                      AS total_factura,
                                b.cuenta                                         AS cuenta_banco,
                                f.cliente_id                                     AS _cliente_id,
                                f.vendedor                                       AS _vendedor_id,
                                ac.banco_id                                      AS _banco_id
                            FROM abonos_creditos ac
                            INNER JOIN factura f  ON f.id  = ac.factura_id
                            INNER JOIN users u    ON u.id  = f.vendedor
                            LEFT JOIN users tele  ON tele.id = f.users_id
                            INNER JOIN banco b    ON b.id  = ac.banco_id
                                                        LEFT JOIN (
                                                                SELECT
                                                                        factura_id,
                                                                SUM(monto) AS total_notas_credito
                                                            FROM nota_credito_movimientos
                                                            WHERE tipo = 'aplicacion'
                                                                GROUP BY factura_id
                                                        ) nc ON nc.factura_id = f.id
                            WHERE ac.estado_abono = 1
                              AND ac.banco_id NOT IN (12, 13)
                ";

                $bindings = [];

                if ($hasFecha) {
                    $sql .= "
                              AND ac.factura_id IN (
                                SELECT DISTINCT factura_id
                                FROM abonos_creditos
                                WHERE DATE(fecha_pago) BETWEEN ? AND ?
                                  AND estado_abono = 1
                            )
                    ";
                    $bindings[] = $fechaInicio;
                    $bindings[] = $fechaFinal;
                }

                $sql .= "
                        ) inner_sub
                    ) sub
                    WHERE 1=1
                ";

                if ($hasFecha) {
                    $sql .= ' AND DATE(sub.fecha_pago) BETWEEN ? AND ?';
                    $bindings[] = $fechaInicio;
                    $bindings[] = $fechaFinal;
                }

                if ($cliente) {
                    $sql .= ' AND sub._cliente_id = ?';
                    $bindings[] = $cliente;
                }
                if ($vendedor) {
                    $sql .= ' AND sub._vendedor_id = ?';
                    $bindings[] = $vendedor;
                }
                if ($banco) {
                    $sql .= ' AND sub._banco_id = ?';
                    $bindings[] = $banco;
                }
                if ($factura) {
                    $sql .= ' AND sub.factura LIKE ?';
                    $bindings[] = '%' . $factura . '%';
                }

                $sql .= ' ORDER BY sub.fecha_pago ASC, sub.banco ASC, sub.cuenta_banco ASC, sub.cliente ASC';

                $consulta = DB::select($sql, $bindings);

                $kpiCobros       = 0;
                $kpiTotalCobrado = 0.0;
                $facturasPagadas = [];

                foreach ($consulta as $row) {
                    $kpiCobros++;
                    $kpiTotalCobrado += (float) ($row->monto_cobrado ?? 0);
                    if (($row->estado_factura ?? '') === 'PAGADA' && !empty($row->factura)) {
                        $facturasPagadas[(string) $row->factura] = true;
                    }
                }

                return Datatables::of($consulta)
                    ->with([
                        'kpi_cobros'        => $kpiCobros,
                        'kpi_total_cobrado' => round($kpiTotalCobrado, 2),
                        'kpi_completas'     => count($facturasPagadas),
                    ])
                    ->rawColumns([])
                    ->make(true);
            }

            $consulta = DB::select("Call sp_reportesxfecha (?, ?, ?)", [$tipo, $fechaInicio, $fechaFinal]);

            return Datatables::of($consulta)
                ->rawColumns([])
                ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al listar el reporte solicitado.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }

    public function exportarPdf(Request $request, $tipo, $fechaInicio, $fechaFinal)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            if (!$tipo || !$fechaInicio || !$fechaFinal) {
                return response()->json([
                    'message' => 'Faltan parámetros requeridos para la exportación del PDF.'
                ], 400);
            }

            if ($tipo == 3) {
                $clienteFiltro  = $request->input('cliente_id',  $request->input('cliente'));
                $vendedorFiltro = $request->input('vendedor_id', $request->input('vendedor'));
                $teleasesorFiltro = $request->input('teleasesor_id', $request->input('teleasesor'));
                $gestorFiltro = $request->input('gestor_id', $request->input('gestor'));
                $bancoFiltro    = $request->input('banco_id',    $request->input('banco'));
                $facturaFiltro  = $request->filled('factura') ? $request->input('factura') : null;

                $rows = $this->queryTipo3Data($fechaInicio, $fechaFinal, $clienteFiltro, $vendedorFiltro, $teleasesorFiltro, $gestorFiltro, $bancoFiltro, $facturaFiltro);
                $data = array_map(fn($r) => (array) $r, $rows);

                $grouped = [];
                foreach ($data as $row) {
                    $gKey = ($row['banco'] ?? '') . "\x00" . ($row['cuenta_banco'] ?? '');
                    if (!isset($grouped[$gKey])) {
                        $grouped[$gKey] = [
                            'banco'  => $row['banco']        ?? '',
                            'cuenta' => $row['cuenta_banco'] ?? '',
                            'rows'   => [],
                        ];
                    }
                    $grouped[$gKey]['rows'][] = $row;
                }

                $pdf = PDF::loadView('pdf.librocobrosrep', [
                    'data'        => $data,
                    'grouped'     => $grouped,
                    'fechaInicio' => $fechaInicio,
                    'fechaFinal'  => $fechaFinal,
                    'tipo'        => 3,
                ])->setPaper('legal', 'landscape');
            } else {
                $consulta = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fechaInicio, $fechaFinal]);
                $data = json_decode(json_encode($consulta), true);
                $pdf  = PDF::loadView('pdf.librocobrosrep', compact('data', 'fechaInicio', 'fechaFinal'))
                           ->setPaper('oficio', 'landscape');
            }

            $response = $pdf->download("LibroCobros_{$fechaInicio}_a_{$fechaFinal}.pdf");

            $downloadToken = (string) $request->input('download_token', '');
            if ($downloadToken !== '') {
                $response->withCookie(cookie('lc_pdf_download_token', $downloadToken, 5, '/', null, false, false, false, 'Lax'));
            }

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar el PDF.',
                'errorTh' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportarExcel(Request $request, $tipo, $fechaInicio, $fechaFinal)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            if (!$tipo || !$fechaInicio || !$fechaFinal) {
                return response()->json([
                    'message' => 'Faltan parámetros requeridos para la exportación del Excel.'
                ], 400);
            }

            if ($tipo == 3) {
                $data = $this->queryTipo3Data(
                    $fechaInicio,
                    $fechaFinal,
                    $request->input('cliente_id', $request->input('cliente')),
                    $request->input('vendedor_id', $request->input('vendedor')),
                    $request->input('teleasesor_id', $request->input('teleasesor')),
                    $request->input('gestor_id',     $request->input('gestor')),
                    $request->input('banco_id',    $request->input('banco')),
                    $request->filled('factura') ? $request->input('factura') : null
                );
            } else {
                $data = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fechaInicio, $fechaFinal]);
            }

            $response = Excel::download(
                new LibroCobrosExport($data, $fechaInicio, $fechaFinal),
                "LibroCobros_{$fechaInicio}_a_{$fechaFinal}.xlsx"
            );

            $downloadToken = (string) $request->input('download_token', '');
            if ($downloadToken !== '') {
                setcookie('lc_excel_download_token', $downloadToken, time() + 300, '/', '', false, false);
            }

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar el Excel.',
                'errorTh' => $e->getMessage(),
            ], 500);
        }
    }

    private function queryTipo3Data(
        string $fechaInicio,
        string $fechaFinal,
        ?string $cliente  = null,
        ?string $vendedor = null,
        ?string $teleasesor = null,
        ?string $gestor = null,
        ?string $banco    = null,
        ?string $factura  = null
    ): array {
        $sql = "
            WITH movimientos AS (
                SELECT
                    ac.id AS movimiento_id,
                    1 AS orden_tipo,
                    ac.factura_id,
                    DATE(ac.fecha_pago) AS fecha_pago,
                    ac.fecha_pago AS fecha_orden,
                    ROUND(ac.monto_abonado, 2) AS monto_cobrado,
                    ROUND(ac.monto_abonado, 2) AS monto_saldo,
                    1 AS impacta_kpi,
                    b.nombre AS banco,
                    b.cuenta AS cuenta_banco,
                    ac.comentario AS observaciones,
                    ac.banco_id AS _banco_id
                FROM abonos_creditos ac
                INNER JOIN banco b ON b.id = ac.banco_id
                WHERE ac.estado_abono = 1
                  AND ac.banco_id NOT IN (12, 13)
                                    AND DATE(ac.fecha_pago) <= ?

                UNION ALL

                                SELECT
                                        nc.id AS movimiento_id,
                                        2 AS orden_tipo,
                                        nc.factura_id,
                                        COALESCE(nc.fecha_rebajado, DATE(nc.fecha)) AS fecha_pago,
                                        COALESCE(nc.fecha_rebajado, nc.created_at) AS fecha_orden,
                                        -ROUND(nc.total, 2) AS monto_cobrado,
                                        ROUND(nc.total, 2) AS monto_saldo,
                                        0 AS impacta_kpi,
                                        'NOTA DE CRÉDITO' AS banco,
                                        'APLICACIÓN LEGADA' AS cuenta_banco,
                                        COALESCE(NULLIF(TRIM(nc.comentario), ''), CONCAT('Nota de crédito ', nc.cai)) AS observaciones,
                                        NULL AS _banco_id
                                FROM nota_credito nc
                                WHERE nc.estado_nota_id = 1
                                    AND nc.estado_rebajado = 1
                                    AND NOT EXISTS (
                                            SELECT 1
                                            FROM nota_credito_creditos cc_legacy
                                            INNER JOIN nota_credito_movimientos m_legacy
                                                ON m_legacy.credito_id = cc_legacy.id
                                                AND m_legacy.tipo = 'aplicacion'
                                            WHERE cc_legacy.nota_credito_id = nc.id
                                    )
                                    AND DATE(COALESCE(nc.fecha_rebajado, nc.fecha)) <= ?

                                UNION ALL

                                SELECT
                                        nc.id AS movimiento_id,
                                        2 AS orden_tipo,
                                        nc.factura_id,
                                        DATE(nc.fecha) AS fecha_pago,
                                        nc.created_at AS fecha_orden,
                                        -ROUND(nc.total, 2) AS monto_cobrado,
                                        0 AS monto_saldo,
                                        0 AS impacta_kpi,
                                        'NOTA DE CRÉDITO' AS banco,
                                        'EMISIÓN' AS cuenta_banco,
                                        CONCAT('Emisión de nota de crédito ', nc.cai, ' en esta factura') AS observaciones,
                                        NULL AS _banco_id
                                FROM nota_credito nc
                                INNER JOIN nota_credito_creditos cc ON cc.nota_credito_id = nc.id
                                WHERE nc.estado_nota_id = 1
                                    AND EXISTS (
                                            SELECT 1 FROM nota_credito_movimientos me
                                            WHERE me.credito_id = cc.id AND me.tipo IN ('aplicacion', 'reembolso')
                                    )
                                    AND DATE(nc.fecha) <= ?

                                UNION ALL

                SELECT
                    m.id AS movimiento_id,
                                        4 AS orden_tipo,
                    m.factura_id,
                    m.fecha_movimiento AS fecha_pago,
                                        m.created_at AS fecha_orden,
                    ROUND(m.monto, 2) AS monto_cobrado,
                                        ROUND(m.monto, 2) AS monto_saldo,
                                        1 AS impacta_kpi,
                    'NOTA DE CRÉDITO' AS banco,
                    'APLICACIÓN A SALDO' AS cuenta_banco,
                                        CONCAT('Recibido de nota de crédito ', nc.cai, ' originada en factura ', fo.cai) AS observaciones,
                    NULL AS _banco_id
                FROM nota_credito_movimientos m
                INNER JOIN nota_credito_creditos cc ON cc.id = m.credito_id
                INNER JOIN nota_credito nc ON nc.id = cc.nota_credito_id
                                INNER JOIN factura fo ON fo.id = nc.factura_id
                WHERE m.tipo = 'aplicacion'
                                    AND DATE(m.fecha_movimiento) <= ?

                                UNION ALL

                                SELECT
                                        m.id AS movimiento_id,
                                    3 AS orden_tipo,
                                        nc.factura_id,
                                        m.fecha_movimiento AS fecha_pago,
                                        m.created_at AS fecha_orden,
                                        -ROUND(m.monto, 2) AS monto_cobrado,
                                        0 AS monto_saldo,
                                    0 AS impacta_kpi,
                                        'NOTA DE CRÉDITO' AS banco,
                                    'DETALLE DE APLICACIÓN' AS cuenta_banco,
                                    CONCAT('De la nota ', nc.cai, ' se aplicaron L ', FORMAT(m.monto, 2), ' a factura ', fd.cai) AS observaciones,
                                        NULL AS _banco_id
                                FROM nota_credito_movimientos m
                                INNER JOIN nota_credito_creditos cc ON cc.id = m.credito_id
                                INNER JOIN nota_credito nc ON nc.id = cc.nota_credito_id
                                INNER JOIN factura fd ON fd.id = m.factura_id
                                WHERE m.tipo = 'aplicacion'
                                    AND nc.factura_id <> m.factura_id
                                    AND DATE(m.fecha_movimiento) <= ?

                                UNION ALL

                                SELECT
                                        m.id AS movimiento_id,
                                        4 AS orden_tipo,
                                        nc.factura_id,
                                        m.fecha_movimiento AS fecha_pago,
                                        m.created_at AS fecha_orden,
                                        -ROUND(m.monto, 2) AS monto_cobrado,
                                        0 AS monto_saldo,
                                        0 AS impacta_kpi,
                                        b.nombre AS banco,
                                        COALESCE(m.cuenta_reembolso, b.cuenta) AS cuenta_banco,
                                        CONCAT('Reembolso de nota de crédito ', nc.cai, ' - ', COALESCE(tpc.descripcion, 'Método no indicado')) AS observaciones,
                                        m.banco_id AS _banco_id
                                FROM nota_credito_movimientos m
                                INNER JOIN nota_credito_creditos cc ON cc.id = m.credito_id
                                INNER JOIN nota_credito nc ON nc.id = cc.nota_credito_id
                                LEFT JOIN banco b ON b.id = m.banco_id
                                LEFT JOIN tipo_pago_cobro tpc ON tpc.id = m.tipo_pago_cobro_id
                                WHERE m.tipo = 'reembolso'
                                    AND DATE(m.fecha_movimiento) <= ?
            ), calculados AS (
                SELECT
                    mov.*,
                    f.fecha_emision,
                    f.fecha_vencimiento,
                    f.nombre_cliente,
                    f.cai,
                    f.numero_secuencia_cai,
                    f.cliente_id,
                    f.vendedor,
                    f.users_id,
                    f.gestor_entrega,
                    f.total,
                    f.sub_total,
                    f.sub_total_grabado,
                    f.sub_total_excento,
                    f.tipo_venta_id,
                    GREATEST(ROUND(
                        f.total - SUM(mov.monto_saldo) OVER (
                            PARTITION BY mov.factura_id
                            ORDER BY mov.fecha_pago, mov.orden_tipo, mov.movimiento_id
                            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                        ), 2
                    ), 0) AS saldo_pendiente
                FROM movimientos mov
                INNER JOIN factura f ON f.id = mov.factura_id
            )
            SELECT
                DATE_FORMAT(c.fecha_emision, '%Y-%m-%d') AS fecha_venta,
                DATE_FORMAT(c.fecha_vencimiento, '%Y-%m-%d') AS fecha_vencimiento,
                DATE_FORMAT(c.fecha_pago, '%Y-%m-%d') AS fecha_pago,
                c.nombre_cliente AS cliente,
                COALESCE(u.name, '') AS asesor_comercial,
                COALESCE(tele.name, '') AS teleasesor,
                COALESCE(NULLIF(TRIM(c.cai), ''), c.numero_secuencia_cai) AS factura,
                c.monto_cobrado,
                c.impacta_kpi,
                c.saldo_pendiente,
                CASE WHEN c.saldo_pendiente <= 0.01 THEN 'PAGADA' ELSE 'PARCIAL' END AS estado_factura,
                c.banco,
                c.cuenta_banco,
                c.observaciones,
                ROUND(CASE
                    WHEN COALESCE(c.sub_total, 0) <= 0 THEN 0
                    WHEN c.tipo_venta_id = 3 THEN c.monto_cobrado * c.sub_total / NULLIF(c.total, 0)
                    ELSE c.monto_cobrado * GREATEST(c.sub_total - COALESCE(c.sub_total_grabado, 0) - COALESCE(c.sub_total_excento, 0), 0) / NULLIF(c.total, 0)
                END, 2) AS exonerado,
                ROUND(CASE WHEN COALESCE(c.total, 0) > 0 THEN c.monto_cobrado * COALESCE(c.sub_total_grabado, 0) / c.total ELSE 0 END, 2) AS gravado,
                ROUND(CASE WHEN COALESCE(c.total, 0) > 0 THEN c.monto_cobrado * COALESCE(c.sub_total_excento, 0) / c.total ELSE 0 END, 2) AS excento,
                ROUND(CASE WHEN COALESCE(c.total, 0) > 0 THEN c.monto_cobrado * COALESCE(c.sub_total, 0) / c.total ELSE c.monto_cobrado END, 2) AS subtotal,
                ROUND(CASE WHEN c.tipo_venta_id = 3 OR COALESCE(c.total, 0) <= 0 THEN 0 ELSE c.monto_cobrado - (c.monto_cobrado * COALESCE(c.sub_total, 0) / c.total) END, 2) AS isv,
                c.monto_cobrado AS total_factura,
                c.cliente_id AS _cliente_id,
                c.vendedor AS _vendedor_id,
                c._banco_id
            FROM calculados c
            LEFT JOIN users u ON u.id = c.vendedor
            LEFT JOIN users tele ON tele.id = c.users_id
            WHERE c.fecha_pago BETWEEN ? AND ?
                            AND c.orden_tipo = 1
        ";

        $bindings = [$fechaFinal, $fechaFinal, $fechaFinal, $fechaFinal, $fechaFinal, $fechaFinal, $fechaInicio, $fechaFinal];
        if (!empty($cliente)) {
            $sql .= ' AND c.cliente_id = ?';
            $bindings[] = $cliente;
        }
        if (!empty($vendedor)) {
            $sql .= ' AND c.vendedor = ?';
            $bindings[] = $vendedor;
        }
        if (!empty($teleasesor)) {
            $sql .= ' AND c.users_id = ?';
            $bindings[] = $teleasesor;
        }
        if (!empty($gestor)) {
            $sql .= ' AND c.gestor_entrega = ?';
            $bindings[] = $gestor;
        }
        if (!empty($banco)) {
            $sql .= ' AND c._banco_id = ?';
            $bindings[] = $banco;
        }
        if (!empty($factura)) {
            $sql .= ' AND (c.cai LIKE ? OR c.numero_secuencia_cai LIKE ?)';
            $bindings[] = '%' . $factura . '%';
            $bindings[] = '%' . $factura . '%';
        }

        $sql .= ' ORDER BY c.fecha_orden ASC, c.orden_tipo ASC, c.movimiento_id ASC';

        return DB::select($sql, $bindings);
    }

    private function queryTipo3DataAnterior(
        string $fechaInicio,
        string $fechaFinal,
        ?string $cliente  = null,
        ?string $vendedor = null,
        ?string $banco    = null,
        ?string $factura  = null
    ): array {
        $sql = "
            SELECT *
            FROM (
                SELECT
                    inner_sub.*,
                    MAX(CASE WHEN inner_sub.estado_factura = 'PAGADA' THEN 1 ELSE 0 END)
                        OVER (PARTITION BY inner_sub.factura) AS factura_tiene_pagada
                FROM (
                    SELECT
                        DATE_FORMAT(f.fecha_emision, '%Y-%m-%d')         AS fecha_venta,
                        DATE_FORMAT(f.fecha_vencimiento, '%Y-%m-%d')     AS fecha_vencimiento,
                        DATE_FORMAT(ac.fecha_pago, '%Y-%m-%d')          AS fecha_pago,
                        f.nombre_cliente                                 AS cliente,
                        COALESCE(u.name, '')                              AS asesor_comercial,
                        COALESCE(tele.name, '')                           AS teleasesor,
                        f.numero_secuencia_cai                          AS factura,
                        ROUND(ac.monto_abonado, 2)                      AS monto_cobrado,
                        GREATEST(
                            ROUND(
                                f.total - COALESCE(nc.total_notas_credito, 0) - SUM(ac.monto_abonado) OVER (
                                    PARTITION BY ac.factura_id
                                    ORDER BY ac.fecha_pago ASC, ac.id ASC
                                    ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                                ), 2
                            ), 0
                        )                                                AS saldo_pendiente,
                        CASE
                            WHEN GREATEST(
                                ROUND(
                                    f.total - COALESCE(nc.total_notas_credito, 0) - SUM(ac.monto_abonado) OVER (
                                        PARTITION BY ac.factura_id
                                        ORDER BY ac.fecha_pago ASC, ac.id ASC
                                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                                    ), 2
                                ), 0
                            ) <= 0.01 THEN 'PAGADA'
                            ELSE 'PARCIAL'
                        END                                              AS estado_factura,
                        b.nombre                                         AS banco,
                        ac.comentario                                    AS observaciones,
                        ROUND(
                            CASE
                                WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                    (CASE
                                        WHEN COALESCE(f.total, 0) > 0
                                            THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                        ELSE ROUND(ac.monto_abonado, 2)
                                    END)
                                    * (CASE
                                        WHEN f.tipo_venta_id = 3 THEN COALESCE(f.sub_total, 0)
                                        ELSE GREATEST(
                                            COALESCE(f.sub_total, 0)
                                            - COALESCE(f.sub_total_grabado, 0)
                                            - COALESCE(f.sub_total_excento, 0),
                                            0
                                        )
                                    END)
                                    / COALESCE(f.sub_total, 1)
                                )
                                ELSE 0
                            END,
                        2)                                               AS exonerado,
                        ROUND(
                            CASE
                                WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                    (CASE
                                        WHEN COALESCE(f.total, 0) > 0
                                            THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                        ELSE ROUND(ac.monto_abonado, 2)
                                    END)
                                    * COALESCE(f.sub_total_grabado, 0)
                                    / COALESCE(f.sub_total, 1)
                                )
                                ELSE 0
                            END,
                        2)                                               AS gravado,
                        ROUND(
                            (CASE
                                WHEN COALESCE(f.total, 0) > 0
                                    THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                ELSE ROUND(ac.monto_abonado, 2)
                            END)
                            -
                            ROUND(
                                CASE
                                    WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                        (CASE
                                            WHEN COALESCE(f.total, 0) > 0
                                                THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                            ELSE ROUND(ac.monto_abonado, 2)
                                        END)
                                        * (CASE
                                            WHEN f.tipo_venta_id = 3 THEN COALESCE(f.sub_total, 0)
                                            ELSE GREATEST(
                                                COALESCE(f.sub_total, 0)
                                                - COALESCE(f.sub_total_grabado, 0)
                                                - COALESCE(f.sub_total_excento, 0),
                                                0
                                            )
                                        END)
                                        / COALESCE(f.sub_total, 1)
                                    )
                                    ELSE 0
                                END,
                            2)
                            -
                            ROUND(
                                CASE
                                    WHEN COALESCE(f.sub_total, 0) > 0 THEN (
                                        (CASE
                                            WHEN COALESCE(f.total, 0) > 0
                                                THEN ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                            ELSE ROUND(ac.monto_abonado, 2)
                                        END)
                                        * COALESCE(f.sub_total_grabado, 0)
                                        / COALESCE(f.sub_total, 1)
                                    )
                                    ELSE 0
                                END,
                            2),
                        2)                                               AS excento,
                        ROUND(
                            CASE
                                WHEN COALESCE(f.total, 0) > 0
                                    THEN ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1)
                                ELSE ac.monto_abonado
                            END,
                        2)                                               AS subtotal,
                        ROUND(
                            CASE
                                WHEN f.tipo_venta_id = 3 THEN 0
                                WHEN COALESCE(f.total, 0) > 0 THEN
                                    ac.monto_abonado - ROUND(ac.monto_abonado * COALESCE(f.sub_total, 0) / COALESCE(f.total, 1), 2)
                                ELSE 0
                            END,
                        2)                                               AS isv,
                        ROUND(ac.monto_abonado, 2)                      AS total_factura,
                        b.cuenta                                         AS cuenta_banco,
                        f.cliente_id                                     AS _cliente_id,
                        f.vendedor                                       AS _vendedor_id,
                        ac.banco_id                                      AS _banco_id
                    FROM abonos_creditos ac
                    INNER JOIN factura f  ON f.id  = ac.factura_id
                    INNER JOIN users u    ON u.id  = f.vendedor
                    LEFT JOIN users tele  ON tele.id = f.users_id
                    INNER JOIN banco b    ON b.id  = ac.banco_id
                                        LEFT JOIN (
                                                SELECT
                                                        factura_id,
                                                SUM(monto) AS total_notas_credito
                                            FROM nota_credito_movimientos
                                            WHERE tipo = 'aplicacion'
                                                GROUP BY factura_id
                                        ) nc ON nc.factura_id = f.id
                    WHERE ac.estado_abono = 1
                      AND ac.banco_id NOT IN (12, 13)
                      AND ac.factura_id IN (
                        SELECT DISTINCT factura_id
                        FROM abonos_creditos
                        WHERE DATE(fecha_pago) BETWEEN ? AND ?
                          AND estado_abono = 1
                    )
                ) inner_sub
            ) sub
            WHERE DATE(sub.fecha_pago) BETWEEN ? AND ?
        ";

        $bindings = [$fechaInicio, $fechaFinal, $fechaInicio, $fechaFinal];

        if (!empty($cliente)) {
            $sql .= ' AND sub._cliente_id = ?';
            $bindings[] = $cliente;
        }
        if (!empty($vendedor)) {
            $sql .= ' AND sub._vendedor_id = ?';
            $bindings[] = $vendedor;
        }
        if (!empty($banco)) {
            $sql .= ' AND sub._banco_id = ?';
            $bindings[] = $banco;
        }
        if (!empty($factura)) {
            $sql .= ' AND sub.factura LIKE ?';
            $bindings[] = '%' . $factura . '%';
        }

        $sql .= ' ORDER BY sub.fecha_pago ASC, sub.banco ASC, sub.cuenta_banco ASC, sub.cliente ASC';

        return DB::select($sql, $bindings);
    }
}
