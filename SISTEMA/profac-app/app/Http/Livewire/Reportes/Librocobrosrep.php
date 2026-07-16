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
            $banco   = $request->input('banco');
            $factura = $request->input('factura');

            $hasFecha = !empty($fechaInicio) && $fechaInicio !== 'todos'
                     && !empty($fechaFinal)   && $fechaFinal   !== 'todos';

            if ($tipo == 3) {
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
                                u.name                                           AS vendedor,
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
                            INNER JOIN banco b    ON b.id  = ac.banco_id
                                                        LEFT JOIN (
                                                                SELECT
                                                                        factura_id,
                                                                        SUM(total) AS total_notas_credito
                                                                FROM nota_credito
                                                                WHERE estado_nota_id = 1
                                                                    AND estado_rebajado = 2
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

                $sql .= ' ORDER BY sub.banco ASC, sub.cuenta_banco ASC, sub.fecha_pago ASC, sub.cliente ASC';

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
                $bancoFiltro    = $request->input('banco_id',    $request->input('banco'));
                $facturaFiltro  = $request->filled('factura') ? $request->input('factura') : null;

                $rows = $this->queryTipo3Data($fechaInicio, $fechaFinal, $clienteFiltro, $vendedorFiltro, $bancoFiltro, $facturaFiltro);
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
                        u.name                                           AS vendedor,
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
                    INNER JOIN banco b    ON b.id  = ac.banco_id
                                        LEFT JOIN (
                                                SELECT
                                                        factura_id,
                                                        SUM(total) AS total_notas_credito
                                                FROM nota_credito
                                                WHERE estado_nota_id = 1
                                                    AND estado_rebajado = 2
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

        $sql .= ' ORDER BY sub.banco ASC, sub.cuenta_banco ASC, sub.fecha_pago ASC, sub.cliente ASC';

        return DB::select($sql, $bindings);
    }
}
