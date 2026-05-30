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
        $vendedores = DB::select("SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC");
        $clientes   = DB::select("SELECT id, nombre FROM cliente ORDER BY nombre ASC");
        return view('livewire.reportes.ventascobros', compact('vendedores', 'clientes'));
    }

    /* ─────────────────────────────────────────────────────────────────
     *  SQL central del reporte
     * ───────────────────────────────────────────────────────────────── */
    private function sqlReporte($vendedorId = null, $clienteId = null, $mes = null, $anio = null, $factura = null)
    {
        $where  = "1=1";            // mostrar todos los estados
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

        $sql = "
        SELECT
            f.id                                                        AS factura_id,
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

            /* ── Clasificación fiscal ── */
            COALESCE(tpv.descripcion, '')                               AS modo_pago,
            UPPER(ev.descripcion)                                       AS estado_f01,

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

            /* ── Abonos (suma de abonos_creditos activos) ── */
            COALESCE(
                (SELECT SUM(ac.monto_abonado)
                 FROM abonos_creditos ac
                 INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                 WHERE ap.factura_id = f.id
                   AND ac.estado_abono = 1),
            0)                                                          AS abonos,

            COALESCE(
                (SELECT GROUP_CONCAT(
                        CONCAT(
                            DATE_FORMAT(ac.fecha_pago, '%d/%m/%Y'),
                            ' L ', FORMAT(ac.monto_abonado, 2),
                            IF(
                                ac.id = (
                                    SELECT MAX(acx.id)
                                    FROM abonos_creditos acx
                                    INNER JOIN aplicacion_pagos apx ON apx.id = acx.aplicacion_pagos_id
                                    WHERE apx.factura_id = f.id
                                      AND acx.estado_abono = 1
                                ),
                                ' (ULTIMO)',
                                ''
                            )
                        )
                        ORDER BY ac.id
                        SEPARATOR ' | '
                    )
                 FROM abonos_creditos ac
                 INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                 WHERE ap.factura_id = f.id
                   AND ac.estado_abono = 1),
            'No aplica')                                               AS detalle_abonos,

                        /* ── Monto pagado (si la retención = subtotal, cuenta como pago total) ── */
                        CASE
                                WHEN apc.id IS NOT NULL THEN GREATEST(COALESCE(f.total, 0) - COALESCE(apc.saldo, 0), 0)
                                ELSE COALESCE(
                                        (SELECT SUM(pv.monto)
                                         FROM pago_venta pv
                                         WHERE pv.factura_id = f.id
                                             AND pv.estado_venta_id = 1),
                                0)
                        END                                                        AS monto_pagado,

                        /* ── Retención ISV (solo cuando estado_retencion_isv = 2) ── */
            CASE
                WHEN COALESCE(apc.estado_retencion_isv, 0) = 2
                    THEN COALESCE(apc.retencion_isv_factura, 0)
                ELSE 0
            END                                                        AS monto_retencion,

            CASE
                WHEN COALESCE(apc.estado_retencion_isv, 0) = 2
                    THEN COALESCE(NULLIF(TRIM(apc.comentario_retencion), ''), 'No aplica')
                ELSE 'No aplica'
            END                                                        AS numero_retencion,

            /* ── Saldo pendiente (calculado) ── */
            CASE
                WHEN apc.id IS NOT NULL THEN COALESCE(apc.saldo, 0)
                ELSE COALESCE(f.total, 0)
                    - COALESCE(
                        (SELECT SUM(ac.monto_abonado)
                         FROM abonos_creditos ac
                         INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                         WHERE ap.factura_id = f.id
                           AND ac.estado_abono = 1),
                    0)
                    - COALESCE(
                        (SELECT SUM(pv.monto)
                         FROM pago_venta pv
                         WHERE pv.factura_id = f.id
                           AND pv.estado_venta_id = 1),
                    0)
            END                                                        AS saldo_pendiente,

            /* ── Fechas ── */
            f.fecha_emision                                             AS fecha_venta,
            f.fecha_vencimiento                                         AS fecha_vencimiento,

            /* ── Días vencidos (calculado; 0 si aún no vence) ── */
            GREATEST(DATEDIFF(CURDATE(), f.fecha_vencimiento), 0)       AS dias_vencidos,

            /* ── Créditos vencidos ── */
            CASE
                WHEN f.credito = 0 THEN 'Contado'
                WHEN (COALESCE(apc.estado_cerrado, 0) = 2 OR COALESCE(apc.saldo, 0) <= 0) THEN 'Cancelada'
                WHEN f.fecha_vencimiento < CURDATE() THEN 'Vencida'
                ELSE 'Vigente'
            END                                                         AS creditos_vencidos,

            /* ── Último abono: fecha, forma de pago, banco, recibo ── */
            COALESCE(
                (SELECT ac3.fecha_pago
                 FROM abonos_creditos ac3
                 INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                 WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1
                 ORDER BY ac3.id DESC LIMIT 1),
            '')                                                         AS fecha_pago,

            COALESCE(
                (SELECT ac3.comentario
                 FROM abonos_creditos ac3
                 INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                 WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1
                 ORDER BY ac3.id DESC LIMIT 1),
            '')                                                         AS forma_pago,

            COALESCE(
                (SELECT CONCAT(b.nombre, ' - ', b.cuenta)
                 FROM abonos_creditos ac3
                 INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                 LEFT  JOIN banco b ON b.id = ac3.banco_id
                 WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1
                 ORDER BY ac3.id DESC LIMIT 1),
            '')                                                         AS cuenta_banco,

            COALESCE(
                (SELECT ac3.numero_recibo
                 FROM abonos_creditos ac3
                 INNER JOIN aplicacion_pagos ap3 ON ap3.id = ac3.aplicacion_pagos_id
                 WHERE ap3.factura_id = f.id AND ac3.estado_abono = 1
                 ORDER BY ac3.id DESC LIMIT 1),
            '')                                                         AS recibo,

            /* ── Fecha de entrega (logística) ── */
            COALESCE(
                (SELECT def2.fecha_entrega_real
                 FROM distribuciones_entrega_facturas def2
                 WHERE def2.factura_id = f.id
                 ORDER BY def2.id DESC LIMIT 1),
            '')                                                         AS fecha_entrega

        FROM factura f
        INNER JOIN cliente c            ON c.id  = f.cliente_id
        LEFT  JOIN users u              ON u.id  = f.vendedor
        LEFT  JOIN estado_venta ev      ON ev.id = f.estado_venta_id
        LEFT  JOIN tipo_pago_venta tpv  ON tpv.id = f.tipo_pago_id
        LEFT  JOIN numero_orden_compra noc ON noc.id = f.numero_orden_compra_id
                LEFT  JOIN aplicacion_pagos apc ON apc.id = (
                        SELECT apx.id
                        FROM aplicacion_pagos apx
                        WHERE apx.factura_id = f.id
                            AND apx.estado = 1
                        ORDER BY apx.id DESC
                        LIMIT 1
                )
        WHERE {$where}
        ORDER BY f.numero_secuencia_cai ASC
        ";

        return DB::select($sql, $params);
    }

    /* ─────────────────────────────────────────────────────────────────
     *  Normalizar parámetros
     * ───────────────────────────────────────────────────────────────── */
    private function norm($v) { return (!$v || $v === 'null') ? null : $v; }

    /* ─────────────────────────────────────────────────────────────────
     *  DataTable AJAX
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
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ─────────────────────────────────────────────────────────────────
     *  Exportar PDF
     * ───────────────────────────────────────────────────────────────── */
    public function exportarPdf(Request $request, $vendedorId = null, $clienteId = null, $mes = null, $anio = null)
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

            $pdf = Pdf::loadView('pdf.reporteventascobros', compact('rows'))
                      ->setPaper('legal', 'landscape');

            return $pdf->download("ReporteVentasCobros_" . now()->format('Y-m-d') . ".pdf");
        } catch (\Exception $e) {
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
                $this->norm($vendedorId),
                $this->norm($clienteId),
                $this->norm($mes),
                $this->norm($anio),
                $this->norm($request->query('factura'))
            );
            $item = 0;
            foreach ($rows as &$r) { $r->item = ++$item; }

            $usuario = Auth::user() ? Auth::user()->name : 'Sistema';

            return Excel::download(
                new ReporteVentasCobrosExport($rows, $usuario),
                "ReporteVentasCobros_" . now()->format('Y-m-d') . ".xlsx"
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
