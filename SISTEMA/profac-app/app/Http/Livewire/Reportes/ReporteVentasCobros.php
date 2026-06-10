<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteVentasCobrosExport;
use Illuminate\Support\Facades\Auth;

class ReporteVentasCobros extends Component
{
    public function render()
    {
        $vendedores  = DB::select("SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC");
        $clientes    = DB::select("SELECT id, nombre FROM cliente ORDER BY nombre ASC");
        $bancos      = DB::select("SELECT id, nombre, cuenta FROM banco ORDER BY nombre ASC");
        $estadosF01  = DB::select("SELECT id, descripcion FROM estado_venta ORDER BY id ASC");
        $modosPago   = DB::select("SELECT id, descripcion FROM tipo_pago_venta ORDER BY descripcion ASC");
        return view('livewire.reportes.ventascobros', compact('vendedores', 'clientes', 'bancos', 'estadosF01', 'modosPago'));
    }

    /* ─────────────────────────────────────────────────────────────────
     *  SQL central del reporte — v2 con todos los filtros y estado_cobro_v2
     * ───────────────────────────────────────────────────────────────── */
    private function sqlReporte(
        $vendedorId      = null,
        $clienteId       = null,
        $mes             = null,
        $anio            = null,
        $factura         = null,
        $fechaDesde      = null,
        $fechaHasta      = null,
        $estadoCobro     = null,
        $estadoF01       = null,
        $modoPago        = null,
        $bancoId         = null,
        $cuenta          = null,
        $fechaPagoDesde  = null,
        $fechaPagoHasta  = null
    ) {
        $where  = "1=1";
        $params = [];

        if ($vendedorId) {
            $where .= " AND f.vendedor = ?";
            $params[] = $vendedorId;
        }
        if ($clienteId) {
            $where .= " AND f.cliente_id = ?";
            $params[] = $clienteId;
        }
        if ($mes) {
            $where .= " AND MONTH(f.fecha_emision) = ?";
            $params[] = $mes;
        }
        if ($anio) {
            $where .= " AND YEAR(f.fecha_emision) = ?";
            $params[] = $anio;
        }
        if ($factura) {
            $where .= " AND (f.numero_secuencia_cai LIKE ? OR CAST(f.id AS CHAR) LIKE ?)";
            $params[] = '%' . $factura . '%';
            $params[] = '%' . $factura . '%';
        }
        if ($fechaDesde) {
            $where .= " AND f.fecha_emision >= ?";
            $params[] = $fechaDesde;
        }
        if ($fechaHasta) {
            $where .= " AND f.fecha_emision <= ?";
            $params[] = $fechaHasta;
        }
        if ($estadoF01) {
            $where .= " AND UPPER(ev.descripcion) = ?";
            $params[] = strtoupper($estadoF01);
        }
        if ($modoPago) {
            $where .= " AND f.tipo_pago_id = ?";
            $params[] = $modoPago;
        }
        if ($bancoId) {
            $where .= " AND EXISTS (
                SELECT 1 FROM abonos_creditos acf
                INNER JOIN aplicacion_pagos apf ON apf.id = acf.aplicacion_pagos_id
                WHERE apf.factura_id = f.id AND acf.estado_abono = 1 AND acf.banco_id = ?
            )";
            $params[] = $bancoId;
        }
        if ($cuenta) {
            $where .= " AND EXISTS (
                SELECT 1 FROM abonos_creditos acf
                INNER JOIN aplicacion_pagos apf ON apf.id = acf.aplicacion_pagos_id
                LEFT JOIN banco bf ON bf.id = acf.banco_id
                WHERE apf.factura_id = f.id AND acf.estado_abono = 1 AND bf.cuenta LIKE ?
            )";
            $params[] = '%' . $cuenta . '%';
        }
        if ($fechaPagoDesde) {
            $where .= " AND EXISTS (
                SELECT 1 FROM abonos_creditos acf
                INNER JOIN aplicacion_pagos apf ON apf.id = acf.aplicacion_pagos_id
                WHERE apf.factura_id = f.id AND acf.estado_abono = 1 AND acf.fecha_pago >= ?
            )";
            $params[] = $fechaPagoDesde;
        }
        if ($fechaPagoHasta) {
            $where .= " AND EXISTS (
                SELECT 1 FROM abonos_creditos acf
                INNER JOIN aplicacion_pagos apf ON apf.id = acf.aplicacion_pagos_id
                WHERE apf.factura_id = f.id AND acf.estado_abono = 1 AND acf.fecha_pago <= ?
            )";
            $params[] = $fechaPagoHasta;
        }

        $innerSql = "
        SELECT
            f.id                                                        AS factura_id,
            f.credito,
            CASE MONTH(f.fecha_emision)
                WHEN 1  THEN 'Enero'     WHEN 2  THEN 'Febrero'
                WHEN 3  THEN 'Marzo'     WHEN 4  THEN 'Abril'
                WHEN 5  THEN 'Mayo'      WHEN 6  THEN 'Junio'
                WHEN 7  THEN 'Julio'     WHEN 8  THEN 'Agosto'
                WHEN 9  THEN 'Septiembre' WHEN 10 THEN 'Octubre'
                WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
            END                                                         AS mes,
            MONTH(f.fecha_emision)                                      AS mes_num,
            YEAR(f.fecha_emision)                                       AS anio,

            /* ── Identificación ── */
            COALESCE(u.name, '')                                        AS vendedor,
            c.nombre                                                    AS cliente,
            f.numero_secuencia_cai,
            COALESCE(f.comentario, '')                                  AS observacion,
            COALESCE(noc.numero_orden, '')                              AS orden_compra,
            COALESCE(flujo_doc.numero_orden_compra, '')                  AS flujo_orden_compra,

            /* ── Clasificación fiscal ── */
            COALESCE(tpv.descripcion, '')                               AS modo_pago,
            UPPER(ev.descripcion)                                       AS estado_f01,
            COALESCE(flujo_doc.numero_forma_f01, '')                    AS flujo_forma_f01,

            /* ── Montos factura ── */
            GREATEST(
                COALESCE(f.sub_total, 0)
                - COALESCE(f.sub_total_grabado, 0)
                - COALESCE(f.sub_total_excento, 0),
            0)                                                          AS exonerado,
            COALESCE(f.sub_total_grabado, 0)                           AS gravado,
            COALESCE(f.sub_total_excento, 0)                           AS exento,
            COALESCE(f.sub_total, 0)                                   AS sub_total,
            COALESCE(f.isv, 0)                                         AS isv,
            COALESCE(f.total, 0)                                       AS total,

            /* ── Abonos ── */
            COALESCE(
                (SELECT SUM(ac.monto_abonado)
                 FROM abonos_creditos ac
                 INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                 WHERE ap.factura_id = f.id AND ac.estado_abono = 1),
            0)                                                          AS abonos,

            COALESCE(
                (SELECT GROUP_CONCAT(
                        CONCAT(DATE_FORMAT(ac.fecha_pago,'%d/%m/%Y'),' L ',FORMAT(ac.monto_abonado,2),
                            IF(ac.id=(SELECT MAX(acx.id) FROM abonos_creditos acx
                               INNER JOIN aplicacion_pagos apx ON apx.id=acx.aplicacion_pagos_id
                               WHERE apx.factura_id=f.id AND acx.estado_abono=1),' (ULTIMO)',''))
                        ORDER BY ac.id SEPARATOR ' | ')
                 FROM abonos_creditos ac
                 INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                 WHERE ap.factura_id = f.id AND ac.estado_abono = 1),
            'No aplica')                                               AS detalle_abonos,

            /* ── Monto pagado ── */
            CASE
                WHEN apc.id IS NOT NULL THEN GREATEST(COALESCE(f.total,0) - COALESCE(apc.saldo,0), 0)
                ELSE COALESCE(
                    (SELECT SUM(pv.monto) FROM pago_venta pv
                     WHERE pv.factura_id = f.id AND pv.estado_venta_id = 1), 0)
            END                                                        AS monto_pagado,

            /* ── Retención ISV ── */
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN COALESCE(apc.retencion_isv_factura,0) ELSE 0
            END                                                        AS monto_retencion,
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN COALESCE(NULLIF(TRIM(apc.comentario_retencion),''),'No aplica') ELSE 'No aplica'
            END                                                        AS numero_retencion,
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN DATE(apc.updated_at) ELSE NULL
            END                                                        AS fecha_retencion,
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN COALESCE((SELECT name FROM users WHERE id = apc.usr_cerro),'')  ELSE ''
            END                                                        AS usuario_retencion,

            /* ── Saldo pendiente ── */
            CASE
                WHEN apc.id IS NOT NULL THEN COALESCE(apc.saldo,0)
                ELSE COALESCE(f.total,0)
                    - COALESCE((SELECT SUM(ac.monto_abonado) FROM abonos_creditos ac
                       INNER JOIN aplicacion_pagos ap ON ap.id=ac.aplicacion_pagos_id
                       WHERE ap.factura_id=f.id AND ac.estado_abono=1), 0)
                    - COALESCE((SELECT SUM(pv.monto) FROM pago_venta pv
                       WHERE pv.factura_id=f.id AND pv.estado_venta_id=1), 0)
            END                                                        AS saldo_pendiente,

            /* ── Fechas ── */
            f.fecha_emision                                            AS fecha_venta,
            f.fecha_vencimiento,
            NULL                                                       AS dias_vencidos,

            /* ── Estado crédito (legacy) ── */
            CASE
                WHEN f.credito = 0 THEN 'Contado'
                WHEN (COALESCE(apc.estado_cerrado,0)=2 OR COALESCE(apc.saldo,0)<=0) THEN 'Cancelada'
                WHEN f.fecha_vencimiento < CURDATE() THEN 'Vencida'
                ELSE 'Vigente'
            END                                                         AS creditos_vencidos,

            /* ── Último abono ── */
            COALESCE((SELECT ac3.fecha_pago FROM abonos_creditos ac3
                INNER JOIN aplicacion_pagos ap3 ON ap3.id=ac3.aplicacion_pagos_id
                WHERE ap3.factura_id=f.id AND ac3.estado_abono=1
                ORDER BY ac3.id DESC LIMIT 1),'')                      AS fecha_pago,

            COALESCE((SELECT tpc.descripcion
                FROM abonos_creditos ac3
                INNER JOIN aplicacion_pagos ap3 ON ap3.id=ac3.aplicacion_pagos_id
                LEFT JOIN tipo_pago_cobro tpc ON tpc.id = ac3.id_tipo_pago_cobro
                WHERE ap3.factura_id=f.id AND ac3.estado_abono=1
                ORDER BY ac3.id DESC LIMIT 1),'')                      AS forma_pago,

            COALESCE((SELECT CONCAT(b.nombre,' - ',b.cuenta) FROM abonos_creditos ac3
                INNER JOIN aplicacion_pagos ap3 ON ap3.id=ac3.aplicacion_pagos_id
                LEFT JOIN banco b ON b.id=ac3.banco_id
                WHERE ap3.factura_id=f.id AND ac3.estado_abono=1
                ORDER BY ac3.id DESC LIMIT 1),'')                      AS cuenta_banco,

            COALESCE((SELECT ac3.numero_recibo FROM abonos_creditos ac3
                INNER JOIN aplicacion_pagos ap3 ON ap3.id=ac3.aplicacion_pagos_id
                WHERE ap3.factura_id=f.id AND ac3.estado_abono=1
                ORDER BY ac3.id DESC LIMIT 1),'')                      AS recibo,

            /* ── Fecha de entrega ── */
            COALESCE((SELECT def2.fecha_entrega_real FROM distribuciones_entrega_facturas def2
                WHERE def2.factura_id=f.id ORDER BY def2.id DESC LIMIT 1),'') AS fecha_entrega

        FROM factura f
        INNER JOIN cliente c              ON c.id  = f.cliente_id
        LEFT  JOIN users u                ON u.id  = f.vendedor
        LEFT  JOIN estado_venta ev        ON ev.id = f.estado_venta_id
        LEFT  JOIN tipo_pago_venta tpv    ON tpv.id = f.tipo_pago_id
        LEFT  JOIN numero_orden_compra noc ON noc.id = f.numero_orden_compra_id
        LEFT  JOIN (
            SELECT hf.tramite_id, fl.numero_forma_f01, fl.numero_orden_compra
            FROM historico_flujo hf
            INNER JOIN flujo fl ON fl.id = hf.flujo_id
            WHERE hf.tipo_tramite_id = 3
        ) AS flujo_doc ON flujo_doc.tramite_id = f.id
        LEFT  JOIN aplicacion_pagos apc   ON apc.id = (
            SELECT apx.id FROM aplicacion_pagos apx
            WHERE apx.factura_id = f.id AND apx.estado = 1
            ORDER BY apx.id DESC LIMIT 1
        )
        WHERE {$where}
        ORDER BY f.numero_secuencia_cai DESC
        ";

        $rows = DB::select($innerSql, $params);

        // Compute estado_cobro_v2 in PHP (avoids double-nested SQL wrapper)
        foreach ($rows as &$r) {
            $saldo   = (float) ($r->saldo_pendiente ?? 0);
            $abonos  = (float) ($r->abonos          ?? 0);
            $credito = (int)   ($r->credito         ?? -1);

            // Días vencidos:
            // - Factura abierta (saldo > 0): CURDATE - fecha_vencimiento
            // - Factura pagada (saldo = 0):  fecha_ultimo_pago - fecha_vencimiento
            $fechaVcto = !empty($r->fecha_vencimiento) ? $r->fecha_vencimiento : null;
            if ($fechaVcto) {
                $ref  = ($saldo <= 0.01 && !empty($r->fecha_pago))
                        ? $r->fecha_pago
                        : date('Y-m-d');
                $dias = (int) round((strtotime($ref) - strtotime($fechaVcto)) / 86400);
            } else {
                $dias = 0;
            }
            $r->dias_vencidos = $dias;

            if ($credito === 0) {
                $r->estado_cobro_v2 = 'Contado';
            } elseif ($saldo <= 0.01) {
                $r->estado_cobro_v2 = 'Pagada';
            } elseif ($dias > 60) {
                $r->estado_cobro_v2 = 'Vencida Crítica';
            } elseif ($dias > 0) {
                $r->estado_cobro_v2 = 'Vencida';
            } elseif ($abonos > 0) {
                $r->estado_cobro_v2 = 'Parcialmente Pagada';
            } else {
                $r->estado_cobro_v2 = 'Pendiente';
            }
        }
        unset($r);

        // Apply estado_cobro filter in PHP
        if ($estadoCobro) {
            $rows = array_values(array_filter($rows, fn($r) => $r->estado_cobro_v2 === $estadoCobro));
        }

        return $rows;
    }

    /* ─────────────────────────────────────────────────────────────────
     *  SELECT ligero — sólo lo que muestra el DataTable.
     *  Incluye estado_cobro_v2 como CASE inline (sin wrapper extra).
     * ───────────────────────────────────────────────────────────────── */
    private function lightSql(string $where): string
    {
        return "
        SELECT
            f.id                                                        AS factura_id,
            f.credito,
            f.numero_secuencia_cai,
            c.nombre                                                    AS cliente,
            COALESCE(u.name, '')                                        AS vendedor,
            f.fecha_emision                                             AS fecha_venta,
            COALESCE(tpv.descripcion, '')                               AS modo_pago,
            UPPER(COALESCE(ev.descripcion, ''))                         AS estado_f01,
            COALESCE(f.total, 0)                                        AS total,
            CASE
                WHEN apc.id IS NOT NULL
                    THEN GREATEST(COALESCE(f.total,0) - COALESCE(apc.saldo,0), 0)
                ELSE COALESCE(
                    (SELECT SUM(pv.monto) FROM pago_venta pv
                     WHERE pv.factura_id = f.id AND pv.estado_venta_id = 1), 0)
            END                                                         AS monto_pagado,
            CASE
                WHEN apc.id IS NOT NULL THEN COALESCE(apc.saldo, 0)
                ELSE COALESCE(f.total, 0)
                    - COALESCE((SELECT SUM(ac.monto_abonado) FROM abonos_creditos ac
                       INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                       WHERE ap.factura_id = f.id AND ac.estado_abono = 1), 0)
                    - COALESCE((SELECT SUM(pv.monto) FROM pago_venta pv
                       WHERE pv.factura_id = f.id AND pv.estado_venta_id = 1), 0)
            END                                                         AS saldo_pendiente,
            COALESCE(
                (SELECT SUM(ac.monto_abonado) FROM abonos_creditos ac
                 INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                 WHERE ap.factura_id = f.id AND ac.estado_abono = 1), 0) AS abonos,
            DATEDIFF(
                CASE
                    WHEN (CASE WHEN apc.id IS NOT NULL THEN COALESCE(apc.saldo, 0)
                               ELSE COALESCE(f.total, 0)
                                    - COALESCE((SELECT SUM(ac_sp.monto_abonado) FROM abonos_creditos ac_sp
                                        INNER JOIN aplicacion_pagos ap_sp ON ap_sp.id = ac_sp.aplicacion_pagos_id
                                        WHERE ap_sp.factura_id = f.id AND ac_sp.estado_abono = 1), 0)
                                    - COALESCE((SELECT SUM(pv_sp.monto) FROM pago_venta pv_sp
                                        WHERE pv_sp.factura_id = f.id AND pv_sp.estado_venta_id = 1), 0)
                          END) <= 0.01
                    THEN COALESCE(
                            (SELECT MAX(ac_dv.fecha_pago) FROM abonos_creditos ac_dv
                             INNER JOIN aplicacion_pagos ap_dv ON ap_dv.id = ac_dv.aplicacion_pagos_id
                             WHERE ap_dv.factura_id = f.id AND ac_dv.estado_abono = 1),
                            CURDATE())
                    ELSE CURDATE()
                END, f.fecha_vencimiento
            )                                                            AS dias_vencidos,
            CASE
                WHEN f.credito = 0 THEN 'Contado'
                WHEN (CASE WHEN apc.id IS NOT NULL THEN COALESCE(apc.saldo, 0)
                           ELSE COALESCE(f.total, 0)
                                - COALESCE((SELECT SUM(ac2.monto_abonado) FROM abonos_creditos ac2
                                    INNER JOIN aplicacion_pagos ap2 ON ap2.id = ac2.aplicacion_pagos_id
                                    WHERE ap2.factura_id = f.id AND ac2.estado_abono = 1), 0)
                                - COALESCE((SELECT SUM(pv2.monto) FROM pago_venta pv2
                                    WHERE pv2.factura_id = f.id AND pv2.estado_venta_id = 1), 0)
                      END) <= 0.01 THEN 'Pagada'
                WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) > 60 THEN 'Vencida Crítica'
                WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) > 0  THEN 'Vencida'
                WHEN COALESCE((SELECT SUM(ac3.monto_abonado) FROM abonos_creditos ac3
                     INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                     WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1), 0) > 0 THEN 'Parcialmente Pagada'
                ELSE 'Pendiente'
            END                                                         AS estado_cobro_v2,

            /* ── Último pago (forma de pago desde tipo_cobro_cierre) ── */
            COALESCE((SELECT tpc.descripcion
                FROM abonos_creditos ac3
                INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                LEFT JOIN tipo_pago_cobro tpc ON tpc.id = ac3.id_tipo_pago_cobro
                WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1
                ORDER BY ac3.id DESC LIMIT 1),'')                       AS forma_pago,

            COALESCE((SELECT ac3.fecha_pago FROM abonos_creditos ac3
                INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1
                ORDER BY ac3.id DESC LIMIT 1),'')                       AS fecha_pago,

            COALESCE((SELECT CONCAT(b.nombre,' - ',b.cuenta)
                FROM abonos_creditos ac3
                INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                LEFT JOIN banco b ON b.id = ac3.banco_id
                WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1
                ORDER BY ac3.id DESC LIMIT 1),'')                       AS cuenta_banco,

            /* ── Retención ISV ── */
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN COALESCE(apc.retencion_isv_factura,0) ELSE 0
            END                                                         AS monto_retencion,
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN COALESCE(NULLIF(TRIM(apc.comentario_retencion),''),'No aplica') ELSE 'No aplica'
            END                                                         AS numero_retencion,
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN DATE(apc.updated_at) ELSE NULL
            END                                                         AS fecha_retencion,
            CASE WHEN COALESCE(apc.estado_retencion_isv,0) = 2
                THEN COALESCE((SELECT name FROM users WHERE id = apc.usr_cerro),'')
                ELSE ''
            END                                                         AS usuario_retencion
        FROM factura f
        INNER JOIN cliente c              ON c.id  = f.cliente_id
        LEFT  JOIN users u                ON u.id  = f.vendedor
        LEFT  JOIN estado_venta ev        ON ev.id = f.estado_venta_id
        LEFT  JOIN tipo_pago_venta tpv    ON tpv.id = f.tipo_pago_id
        LEFT  JOIN aplicacion_pagos apc   ON apc.id = (
            SELECT apx.id FROM aplicacion_pagos apx
            WHERE apx.factura_id = f.id AND apx.estado = 1
            ORDER BY apx.id DESC LIMIT 1
        )
        WHERE {$where}
        ";
    }

    /* ─────────────────────────────────────────────────────────────────
     *  Normalizar parámetros
     * ───────────────────────────────────────────────────────────────── */
    private function norm($v) { return (!$v || $v === 'null') ? null : $v; }

    /* ─────────────────────────────────────────────────────────────────
     *  DataTable AJAX — ruta legacy (segmentos URL)
     * ───────────────────────────────────────────────────────────────── */
    public function consulta(Request $request, $vendedorId = null, $clienteId = null, $mes = null, $anio = null)
    {
        try {
            $rows = $this->sqlReporte(
                $this->norm($vendedorId),
                $this->norm($clienteId),
                $this->norm($mes),
                $this->norm($anio),
                $this->norm($request->query('factura'))
            );
            $item = 0;
            foreach ($rows as &$r) { $r->item = ++$item; }

            return DataTables::of($rows)->rawColumns([])->make(true);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ─────────────────────────────────────────────────────────────────
     *  DataTable AJAX v2 — paginación real SQL (COUNT + LIMIT/OFFSET)
     * ───────────────────────────────────────────────────────────────── */
    public function consultaDatos(Request $request)
    {
        try {
            $draw   = (int) $request->query('draw',   1);
            $start  = (int) $request->query('start',  0);
            $length = max(1, min((int) $request->query('length', 25), 200));
            $estadoCobro = $this->norm($request->query('estado_cobro'));

            $where  = '1=1';
            $params = [];

            $p = $this->norm($request->query('vendedor'));
            if ($p) { $where .= ' AND f.vendedor = ?';   $params[] = $p; }

            $p = $this->norm($request->query('cliente'));
            if ($p) { $where .= ' AND f.cliente_id = ?'; $params[] = $p; }

            $p = $this->norm($request->query('factura'));
            if ($p) {
                $where .= ' AND (f.numero_secuencia_cai LIKE ? OR CAST(f.id AS CHAR) LIKE ?)';
                $params[] = '%' . $p . '%';
                $params[] = '%' . $p . '%';
            }

            // Buscador nativo de DataTables (search[value])
            $dtSearch = $this->norm(($request->query('search') ?? [])['value'] ?? null);
            if ($dtSearch) {
                $where .= ' AND (f.numero_secuencia_cai LIKE ? OR c.nombre LIKE ?)';
                $params[] = '%' . $dtSearch . '%';
                $params[] = '%' . $dtSearch . '%';
            }

            $p = $this->norm($request->query('fecha_desde'));
            if ($p) { $where .= ' AND f.fecha_emision >= ?'; $params[] = $p; }

            $p = $this->norm($request->query('fecha_hasta'));
            if ($p) { $where .= ' AND f.fecha_emision <= ?'; $params[] = $p; }

            $p = $this->norm($request->query('estado_f01'));
            if ($p) { $where .= ' AND UPPER(ev.descripcion) = ?'; $params[] = strtoupper($p); }

            $p = $this->norm($request->query('modo_pago'));
            if ($p) { $where .= ' AND f.tipo_pago_id = ?'; $params[] = $p; }

            $p = $this->norm($request->query('banco'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND acf.banco_id=?)';
                $params[] = $p;
            }

            $p = $this->norm($request->query('cuenta'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id LEFT JOIN banco bf ON bf.id=acf.banco_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND bf.cuenta LIKE ?)';
                $params[] = '%' . $p . '%';
            }

            $p = $this->norm($request->query('fecha_pago_desde'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND acf.fecha_pago>=?)';
                $params[] = $p;
            }

            $p = $this->norm($request->query('fecha_pago_hasta'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND acf.fecha_pago<=?)';
                $params[] = $p;
            }

            $inner = $this->lightSql($where);

            // ── Ordenamiento dinámico desde DataTables (whitelist para evitar SQL injection)
            $dtColMap = [
                1  => 'numero_secuencia_cai',
                2  => 'cliente',
                3  => 'vendedor',
                4  => 'fecha_venta',
                5  => 'modo_pago',
                6  => 'total',
                7  => 'monto_pagado',
                8  => 'saldo_pendiente',
                9  => 'estado_cobro_v2',
                10 => 'dias_vencidos',
            ];
            $dtOrder     = $request->query('order', []);
            $dtColIdx    = isset($dtOrder[0]['column']) ? (int) $dtOrder[0]['column'] : 1;
            $dtDir       = (isset($dtOrder[0]['dir']) && strtolower($dtOrder[0]['dir']) === 'desc') ? 'DESC' : 'ASC';
            $dtOrderCol  = $dtColMap[$dtColIdx] ?? 'numero_secuencia_cai';

            if ($estadoCobro) {
                $cntSql  = "SELECT COUNT(*) AS cnt FROM ({$inner}) AS _c WHERE estado_cobro_v2 = ?";
                $cntP    = array_merge($params, [$estadoCobro]);
                $datSql  = "SELECT * FROM ({$inner}) AS _d WHERE estado_cobro_v2 = ? ORDER BY {$dtOrderCol} {$dtDir} LIMIT ? OFFSET ?";
                $datP    = array_merge($params, [$estadoCobro, $length, $start]);
            } else {
                $cntSql  = "SELECT COUNT(*) AS cnt FROM ({$inner}) AS _c";
                $cntP    = $params;
                $datSql  = "SELECT * FROM ({$inner}) AS _d ORDER BY {$dtOrderCol} {$dtDir} LIMIT ? OFFSET ?";
                $datP    = array_merge($params, [$length, $start]);
            }

            $total = (int) DB::selectOne($cntSql, $cntP)->cnt;
            $rows  = DB::select($datSql, $datP);

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $total,
                'recordsFiltered' => $total,
                'data'            => $rows,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    /* ─────────────────────────────────────────────────────────────────
     *  KPIs — agregados directos en SQL (sin cargar filas en PHP)
     * ───────────────────────────────────────────────────────────────── */
    public function kpis(Request $request)
    {
        try {
            $estadoCobro = $this->norm($request->query('estado_cobro'));

            $where  = '1=1';
            $params = [];

            $p = $this->norm($request->query('vendedor'));
            if ($p) { $where .= ' AND f.vendedor = ?';   $params[] = $p; }

            $p = $this->norm($request->query('cliente'));
            if ($p) { $where .= ' AND f.cliente_id = ?'; $params[] = $p; }

            $p = $this->norm($request->query('factura'));
            if ($p) {
                $where .= ' AND (f.numero_secuencia_cai LIKE ? OR CAST(f.id AS CHAR) LIKE ?)';
                $params[] = '%' . $p . '%';
                $params[] = '%' . $p . '%';
            }

            $p = $this->norm($request->query('fecha_desde'));
            if ($p) { $where .= ' AND f.fecha_emision >= ?'; $params[] = $p; }

            $p = $this->norm($request->query('fecha_hasta'));
            if ($p) { $where .= ' AND f.fecha_emision <= ?'; $params[] = $p; }

            $p = $this->norm($request->query('estado_f01'));
            if ($p) { $where .= ' AND UPPER(ev.descripcion) = ?'; $params[] = strtoupper($p); }

            $p = $this->norm($request->query('modo_pago'));
            if ($p) { $where .= ' AND f.tipo_pago_id = ?'; $params[] = $p; }

            $p = $this->norm($request->query('banco'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND acf.banco_id=?)';
                $params[] = $p;
            }

            $p = $this->norm($request->query('cuenta'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id LEFT JOIN banco bf ON bf.id=acf.banco_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND bf.cuenta LIKE ?)';
                $params[] = '%' . $p . '%';
            }

            $p = $this->norm($request->query('fecha_pago_desde'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND acf.fecha_pago>=?)';
                $params[] = $p;
            }

            $p = $this->norm($request->query('fecha_pago_hasta'));
            if ($p) {
                $where .= ' AND EXISTS (SELECT 1 FROM abonos_creditos acf INNER JOIN aplicacion_pagos apf ON apf.id=acf.aplicacion_pagos_id WHERE apf.factura_id=f.id AND acf.estado_abono=1 AND acf.fecha_pago<=?)';
                $params[] = $p;
            }

            $inner     = $this->lightSql($where);
            $aggWhere  = $estadoCobro ? "WHERE estado_cobro_v2 = ?" : "";
            $aggParams = $estadoCobro ? array_merge($params, [$estadoCobro]) : $params;

            $kpiRow = DB::selectOne("
                SELECT
                    COUNT(*)                                                                AS total_facturas,
                    COALESCE(SUM(total), 0)                                                 AS total_facturado,
                    COALESCE(SUM(monto_pagado), 0)                                          AS total_cobrado,
                    COALESCE(SUM(saldo_pendiente), 0)                                       AS total_pendiente,
                    COALESCE(SUM(CASE WHEN estado_cobro_v2 IN ('Vencida','Vencida Crítica')
                                 THEN saldo_pendiente ELSE 0 END), 0)                       AS total_vencido,
                    SUM(CASE WHEN estado_cobro_v2 = 'Pagada'           THEN 1 ELSE 0 END)  AS facturas_pagadas,
                    SUM(CASE WHEN estado_cobro_v2 IN ('Pendiente','Parcialmente Pagada',
                                                       'Vencida','Vencida Crítica')
                             THEN 1 ELSE 0 END)                                             AS facturas_pendientes,
                    SUM(CASE WHEN estado_cobro_v2 IN ('Vencida','Vencida Crítica')
                             THEN 1 ELSE 0 END)                                             AS facturas_vencidas
                FROM ({$inner}) AS _agg
                {$aggWhere}
            ", $aggParams);

            return response()->json(['success' => true, 'kpis' => [
                'total_facturas'      => (int)   ($kpiRow->total_facturas      ?? 0),
                'total_facturado'     => (float) ($kpiRow->total_facturado     ?? 0),
                'total_cobrado'       => (float) ($kpiRow->total_cobrado       ?? 0),
                'total_pendiente'     => (float) ($kpiRow->total_pendiente     ?? 0),
                'total_vencido'       => (float) ($kpiRow->total_vencido       ?? 0),
                'facturas_pagadas'    => (int)   ($kpiRow->facturas_pagadas    ?? 0),
                'facturas_pendientes' => (int)   ($kpiRow->facturas_pendientes ?? 0),
                'facturas_vencidas'   => (int)   ($kpiRow->facturas_vencidas   ?? 0),
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }
    /* ─────────────────────────────────────────────────────────────────
     *  Expediente financiero completo de una factura
     * ───────────────────────────────────────────────────────────────── */
    public function expediente(Request $request, $facturaId)
    {
        try {
            $facturaId = (int) $facturaId;

            /* ── Cabecera ── */
            $cab = DB::selectOne("
                SELECT
                    f.id,
                    f.numero_secuencia_cai,
                    f.credito,
                    c.nombre                                                AS cliente,
                    COALESCE(u.name, '')                                    AS vendedor,
                    f.fecha_emision                                         AS fecha_venta,
                    COALESCE(noc.numero_orden, '')                          AS orden_compra,
                    COALESCE(f.comentario, '')                              AS observacion,
                    COALESCE(tpv.descripcion, '')                           AS modo_pago,
                    UPPER(COALESCE(ev.descripcion, ''))                     AS estado_f01,
                    COALESCE(flujo_doc.numero_forma_f01, '')                AS flujo_forma_f01,
                    COALESCE((SELECT def2.fecha_entrega_real
                        FROM distribuciones_entrega_facturas def2
                        WHERE def2.factura_id = f.id ORDER BY def2.id DESC LIMIT 1), NULL) AS fecha_entrega,
                    GREATEST(COALESCE(f.sub_total,0)-COALESCE(f.sub_total_grabado,0)-COALESCE(f.sub_total_excento,0),0) AS exonerado,
                    COALESCE(f.sub_total_grabado, 0)                        AS gravado,
                    COALESCE(f.sub_total_excento, 0)                        AS exento,
                    COALESCE(f.sub_total, 0)                                AS sub_total,
                    COALESCE(f.isv, 0)                                      AS isv,
                    COALESCE(f.total, 0)                                    AS total_factura,
                    f.fecha_vencimiento,
                    DATEDIFF(
                        CASE
                            WHEN COALESCE(apc_exp.saldo, f.total) <= 0.01
                            THEN COALESCE(
                                    (SELECT MAX(ac_exp.fecha_pago)
                                     FROM abonos_creditos ac_exp
                                     INNER JOIN aplicacion_pagos ap_exp ON ap_exp.id = ac_exp.aplicacion_pagos_id
                                     WHERE ap_exp.factura_id = f.id AND ac_exp.estado_abono = 1),
                                    CURDATE())
                            ELSE CURDATE()
                        END, f.fecha_vencimiento)                    AS dias_vencidos,
                    CASE WHEN f.credito = 0
                        THEN 0
                        ELSE DATEDIFF(f.fecha_vencimiento, f.fecha_emision)
                    END                                                     AS dias_credito
                FROM factura f
                INNER JOIN cliente c              ON c.id  = f.cliente_id
                LEFT  JOIN users u                ON u.id  = f.vendedor
                LEFT  JOIN estado_venta ev        ON ev.id = f.estado_venta_id
                LEFT  JOIN tipo_pago_venta tpv    ON tpv.id = f.tipo_pago_id
                LEFT  JOIN numero_orden_compra noc ON noc.id = f.numero_orden_compra_id
                LEFT  JOIN (
                    SELECT hf.tramite_id, fl.numero_forma_f01
                    FROM historico_flujo hf
                    INNER JOIN flujo fl ON fl.id = hf.flujo_id
                    WHERE hf.tipo_tramite_id = 3
                ) AS flujo_doc ON flujo_doc.tramite_id = f.id
                LEFT JOIN aplicacion_pagos apc_exp ON apc_exp.id = (
                    SELECT apx.id FROM aplicacion_pagos apx
                    WHERE apx.factura_id = f.id AND apx.estado = 1
                    ORDER BY apx.id DESC LIMIT 1
                )
                WHERE f.id = ?
            ", [$facturaId]);

            if (!$cab) {
                return response()->json(['success' => false, 'mensaje' => 'Factura no encontrada'], 404);
            }

            // Verificar si tiene flujo para habilitar edición de F-01
            $flujoRow = DB::selectOne("
                SELECT fl.id AS flujo_id
                FROM historico_flujo hf
                INNER JOIN flujo fl ON fl.id = hf.flujo_id
                WHERE hf.tramite_id = ? AND hf.tipo_tramite_id = 3
                LIMIT 1
            ", [$facturaId]);
            $cab->flujo_id = $flujoRow ? $flujoRow->flujo_id : null;

            /* ── Movimientos ── */
            $movimientos = DB::select("
                SELECT tipo, fecha, documento, monto, banco_nombre, banco_cuenta,
                       recibo, descripcion, responsable, forma_pago, orden_tipo
                FROM (
                    /* Venta */
                    SELECT 'VENTA' AS tipo, f.fecha_emision AS fecha,
                           f.numero_secuencia_cai AS documento, f.total AS monto,
                           NULL AS banco_nombre, NULL AS banco_cuenta, NULL AS recibo,
                           COALESCE(f.comentario,'Venta registrada') AS descripcion,
                           COALESCE(u.name,'') AS responsable,
                           COALESCE(tpv.descripcion,'') AS forma_pago, 1 AS orden_tipo
                    FROM factura f
                    LEFT JOIN users u ON u.id = f.vendedor
                    LEFT JOIN tipo_pago_venta tpv ON tpv.id = f.tipo_pago_id
                    WHERE f.id = ?

                    UNION ALL

                    /* Abono crédito */
                    SELECT 'ABONO', ac.fecha_pago, COALESCE(ac.numero_recibo,''),
                           ac.monto_abonado, COALESCE(b.nombre,''), COALESCE(b.cuenta,''),
                           COALESCE(ac.numero_recibo,''), COALESCE(ac.comentario,''),
                           COALESCE(u_reg.name,''), COALESCE(tpc_ab.descripcion,''), 3
                    FROM abonos_creditos ac
                    INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                    LEFT JOIN banco b ON b.id = ac.banco_id
                    LEFT JOIN tipo_pago_cobro tpc_ab ON tpc_ab.id = ac.id_tipo_pago_cobro
                    LEFT JOIN users u_reg ON u_reg.id = ac.usr_registro
                    WHERE ap.factura_id = ? AND ac.estado_abono = 1

                    UNION ALL

                    /* Pago contado */
                    SELECT 'PAGO', pv.fecha, CAST(pv.id AS CHAR),
                           pv.monto, NULL, NULL, NULL,
                           'Pago de venta', COALESCE(u_pago.name,''), NULL, 4
                    FROM pago_venta pv
                    LEFT JOIN users u_pago ON u_pago.id = pv.users_id
                    WHERE pv.factura_id = ? AND pv.estado_venta_id = 1

                    UNION ALL

                    /* Nota de crédito */
                    SELECT 'NOTA_CREDITO', nc.fecha, nc.numero_secuencia_cai,
                           nc.total, NULL, NULL, NULL,
                           COALESCE(nc.comentario,''), COALESCE(u_nc.name,''), NULL, 5
                    FROM nota_credito nc
                    LEFT JOIN users u_nc ON u_nc.id = nc.users_id
                    WHERE nc.factura_id = ? AND nc.estado_nota_id = 1

                    UNION ALL

                    /* Nota de débito */
                    SELECT 'NOTA_DEBITO', nd.fechaEmision, nd.correlativoND,
                           nd.monto_asignado, NULL, NULL, NULL,
                           COALESCE(nd.motivoDescripcion,''), COALESCE(u_nd.name,''), NULL, 6
                    FROM notadebito nd
                    LEFT JOIN users u_nd ON u_nd.id = nd.users_registra_id
                    WHERE nd.factura_id = ?

                    UNION ALL

                    /* Retención ISV */
                    SELECT 'RETENCION', apc_ret.updated_at,
                           COALESCE(NULLIF(TRIM(apc_ret.comentario_retencion),''), 'Retención ISV'),
                           apc_ret.retencion_isv_factura, NULL, NULL,
                           COALESCE(NULLIF(TRIM(apc_ret.comentario_retencion),''), ''),
                           'Retención ISV aplicada',
                           COALESCE(u_ret.name,''), NULL, 7
                    FROM aplicacion_pagos apc_ret
                    LEFT JOIN users u_ret ON u_ret.id = apc_ret.usr_cerro
                    WHERE apc_ret.factura_id = ? AND apc_ret.estado_retencion_isv = 2
                ) AS _movs
                ORDER BY fecha ASC, orden_tipo ASC
            ", [$facturaId, $facturaId, $facturaId, $facturaId, $facturaId, $facturaId]);

            /* ── Calcular saldo progresivo ── */
            $saldo = (float) $cab->total_factura;
            foreach ($movimientos as $mov) {
                $monto = (float) ($mov->monto ?? 0);
                if ($mov->tipo === 'VENTA') {
                    $mov->saldo_resultante = $saldo;
                } elseif (in_array($mov->tipo, ['ABONO', 'PAGO', 'NOTA_CREDITO', 'RETENCION'])) {
                    $saldo -= $monto;
                    $mov->saldo_resultante = max($saldo, 0);
                } else {
                    $mov->saldo_resultante = null; // ENTREGA no cambia saldo
                }
            }

            $cabArray = (array) $cab;
            $cabArray['factura_id']  = $facturaId;
            $cabArray['tiene_flujo'] = !empty($cab->flujo_id);

            return response()->json([
                'success'     => true,
                'cabecera'    => $cabArray,
                'movimientos' => $movimientos,
                'saldo_final' => max($saldo, 0),
                'tiene_flujo' => !empty($cab->flujo_id),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }

    /* ─────────────────────────────────────────────────────────────────
     *  Exportar PDF
     * ───────────────────────────────────────────────────────────────── */
    public function exportarPdf(Request $request, $vendedorId = null, $clienteId = null, $mes = null, $anio = null)
    {
        try {
            $rows = $this->sqlReporte(
                $this->norm($request->input('vendedor',    $vendedorId)),
                $this->norm($request->input('cliente',     $clienteId)),
                $this->norm($mes),
                $this->norm($anio),
                $this->norm($request->input('factura')),
                $this->norm($request->input('fecha_desde')),
                $this->norm($request->input('fecha_hasta')),
                $this->norm($request->input('estado_cobro')),
                $this->norm($request->input('estado_f01')),
                $this->norm($request->input('modo_pago')),
                $this->norm($request->input('banco')),
                $this->norm($request->input('cuenta')),
                $this->norm($request->input('fecha_pago_desde')),
                $this->norm($request->input('fecha_pago_hasta'))
            );
            $item = 0;
            foreach ($rows as &$r) { $r->item = ++$item; }

            $pdf = Pdf::loadView('pdf.reporteventascobros', compact('rows'))
                      ->setPaper('legal', 'landscape');

            return $pdf->download("ReporteVentasCobros_" . now()->format('Y-m-d') . ".pdf");
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ─────────────────────────────────────────────────────────────────
     *  Exportar Excel
     * ───────────────────────────────────────────────────────────────── */
    public function exportarExcel(Request $request, $vendedorId = null, $clienteId = null, $mes = null, $anio = null)
    {
        try {
            $rows = $this->sqlReporte(
                $this->norm($request->input('vendedor',    $vendedorId)),
                $this->norm($request->input('cliente',     $clienteId)),
                $this->norm($mes),
                $this->norm($anio),
                $this->norm($request->input('factura')),
                $this->norm($request->input('fecha_desde')),
                $this->norm($request->input('fecha_hasta')),
                $this->norm($request->input('estado_cobro')),
                $this->norm($request->input('estado_f01')),
                $this->norm($request->input('modo_pago')),
                $this->norm($request->input('banco')),
                $this->norm($request->input('cuenta')),
                $this->norm($request->input('fecha_pago_desde')),
                $this->norm($request->input('fecha_pago_hasta'))
            );
            $item = 0;
            foreach ($rows as &$r) { $r->item = ++$item; }

            $usuario = Auth::user() ? Auth::user()->name : 'Sistema';

            $facturaIds  = array_map(fn($r) => (int)$r->factura_id, $rows);
            $movimientos = $this->getMovimientosBulk($facturaIds);

            return Excel::download(
                new ReporteVentasCobrosExport($rows, $usuario, $movimientos, $movimientos),
                "ReporteVentasCobros_" . now()->format('Y-m-d') . ".xlsx"
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ─────────────────────────────────────────────────────────────────
     *  Actualizar Estado F-01 (numero_forma_f01) en flujo
     * ───────────────────────────────────────────────────────────────── */
    public function actualizarF01(Request $request, $facturaId)
    {
        $facturaId = (int) $facturaId;
        $valor     = trim($request->input('valor', ''));

        // Buscar el flujo ligado a esta factura
        $flujo = DB::selectOne("
            SELECT fl.id
            FROM historico_flujo hf
            INNER JOIN flujo fl ON fl.id = hf.flujo_id
            WHERE hf.tramite_id = ? AND hf.tipo_tramite_id = 3
            LIMIT 1
        ", [$facturaId]);

        if (!$flujo) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Esta factura no cuenta con flujo asociado. No se puede modificar el Estado F-01.',
            ], 422);
        }

        DB::table('flujo')->where('id', $flujo->id)->update([
            'numero_forma_f01' => $valor ?: null,
            'updated_at'       => now(),
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Estado F-01 actualizado correctamente.',
            'valor'   => $valor ?: 'N/A',
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────
     *  Movimientos de varias facturas en un solo query (para Excel)
     * ───────────────────────────────────────────────────────────────── */
    private function getMovimientosBulk(array $facturaIds): array
    {
        if (empty($facturaIds)) return [];

        // Embed IDs as integer literals to avoid MySQL's 65 535-placeholder limit.
        // Safe: every value is cast to int before interpolation.
        $ph = implode(',', array_map('intval', $facturaIds));

        $movs = DB::select("
            SELECT tipo, factura_id, fecha, documento, monto,
                   banco_nombre, banco_cuenta, recibo, descripcion,
                   responsable, forma_pago
            FROM (
                SELECT 'VENTA' AS tipo, f.id AS factura_id, f.fecha_emision AS fecha,
                       f.numero_secuencia_cai AS documento, f.total AS monto,
                       NULL AS banco_nombre, NULL AS banco_cuenta, NULL AS recibo,
                       COALESCE(f.comentario,'Venta registrada') AS descripcion,
                       COALESCE(u.name,'') AS responsable,
                       COALESCE(tpv.descripcion,'') AS forma_pago, 1 AS orden_tipo
                FROM factura f
                LEFT JOIN users u ON u.id = f.vendedor
                LEFT JOIN tipo_pago_venta tpv ON tpv.id = f.tipo_pago_id
                WHERE f.id IN ({$ph})

                UNION ALL

                SELECT 'ABONO', ap.factura_id, ac.fecha_pago,
                       COALESCE(ac.numero_recibo,''), ac.monto_abonado,
                       COALESCE(b.nombre,''), COALESCE(b.cuenta,''),
                       COALESCE(ac.numero_recibo,''), COALESCE(ac.comentario,''),
                       COALESCE(u_reg.name,''), COALESCE(tpc_ab.descripcion,''), 3
                FROM abonos_creditos ac
                INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                LEFT JOIN banco b ON b.id = ac.banco_id
                LEFT JOIN tipo_pago_cobro tpc_ab ON tpc_ab.id = ac.id_tipo_pago_cobro
                LEFT JOIN users u_reg ON u_reg.id = ac.usr_registro
                WHERE ap.factura_id IN ({$ph}) AND ac.estado_abono = 1

                UNION ALL

                SELECT 'PAGO', pv.factura_id, pv.fecha,
                       CAST(pv.id AS CHAR), pv.monto,
                       NULL, NULL, NULL,
                       'Pago de venta', COALESCE(u_pago.name,''), NULL, 4
                FROM pago_venta pv
                LEFT JOIN users u_pago ON u_pago.id = pv.users_id
                WHERE pv.factura_id IN ({$ph}) AND pv.estado_venta_id = 1

                UNION ALL

                SELECT 'NOTA_CREDITO', nc.factura_id, nc.fecha,
                       nc.numero_secuencia_cai, nc.total,
                       NULL, NULL, NULL,
                       COALESCE(nc.comentario,''), COALESCE(u_nc.name,''), NULL, 5
                FROM nota_credito nc
                LEFT JOIN users u_nc ON u_nc.id = nc.users_id
                WHERE nc.factura_id IN ({$ph}) AND nc.estado_nota_id = 1

                UNION ALL

                SELECT 'NOTA_DEBITO', nd.factura_id, nd.fechaEmision,
                       nd.correlativoND, nd.monto_asignado,
                       NULL, NULL, NULL,
                       COALESCE(nd.motivoDescripcion,''), COALESCE(u_nd.name,''), NULL, 6
                FROM notadebito nd
                LEFT JOIN users u_nd ON u_nd.id = nd.users_registra_id
                WHERE nd.factura_id IN ({$ph})
            ) AS _movs
            ORDER BY factura_id ASC, fecha ASC, orden_tipo ASC
        ");

        $grouped = [];
        foreach ($movs as $m) {
            $grouped[$m->factura_id][] = $m;
        }
        return $grouped;
    }
}
