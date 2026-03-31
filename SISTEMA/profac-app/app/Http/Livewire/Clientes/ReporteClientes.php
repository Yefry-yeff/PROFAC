<?php

namespace App\Http\Livewire\Clientes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteClientesExport;
use Illuminate\Support\Facades\Auth;

class ReporteClientes extends Component
{
    public function render()
    {
        $vendedores = DB::select("SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC");
        return view('livewire.clientes.reporteclientes', compact('vendedores'));
    }

    /* ─────────────────────────────────────────────
     *  SQL reutilizable para los tres conjuntos
     * ───────────────────────────────────────────── */
    private function sqlGeneral($vendedorId = null, $estado = null)
    {
        $where = "1=1";
        $params = [];

        if ($vendedorId) {
            $where .= " AND c.vendedor = ?";
            $params[] = $vendedorId;
        }
        if ($estado) {
            $where .= " AND c.estado_cliente_id = ?";
            $params[] = $estado;
        }

        $sql = "
        SELECT
            c.id                                                   AS id,
            YEAR(c.created_at)                                     AS anio_ingreso,
            COALESCE(u_vend.name,'')                               AS vendedor,
            c.nombre                                               AS cliente,
            c.id                                                   AS codigo,

            /* SOLICITUD DE CREDITO */
            CASE WHEN cc.id IS NOT NULL AND cc.credito_activo=1 THEN 'X' ELSE 'SOLICITAR' END
                                                                   AS solicitud_credito,

            /* CONDICIONES DE CREDITO */
            CASE
                WHEN cc.id IS NOT NULL AND cc.credito_activo=1 THEN 'X'
                WHEN cc.id IS NOT NULL AND cc.credito_activo=0 THEN 'SOLICITAR'
                ELSE 'N/A'
            END                                                    AS condiciones_credito,

            /* DOCUMENTOS */
            CASE WHEN d_escritura.id IS NOT NULL THEN 'X' ELSE 'SOLICITAR' END AS doc_escritura,
            CASE WHEN d_dni.id       IS NOT NULL THEN 'X' ELSE 'SOLICITAR' END AS doc_dni,
            CASE WHEN d_rtn.id       IS NOT NULL THEN 'X' ELSE 'SOLICITAR' END AS doc_rtn,
            CASE WHEN d_permiso.id   IS NOT NULL THEN 'X' ELSE 'SOLICITAR' END AS doc_permiso,

            /* AÑO INICIO OPERACION */
            COALESCE(c.ano_operacion,'')                           AS anio_operacion,

            CASE WHEN d_croquis.id   IS NOT NULL THEN 'X' ELSE 'SOLICITAR' END AS doc_croquis,

            /* REFERENCIAS BANCARIAS */
            CASE
                WHEN cc.id IS NOT NULL AND cc.credito_activo=1 THEN
                    CASE WHEN cc.referencias_bancarias IS NOT NULL AND cc.referencias_bancarias!='' THEN 'X' ELSE 'SOLICITAR' END
                ELSE 'N/A'
            END                                                    AS ref_bancarias,

            /* REFERENCIAS COMERCIALES */
            CASE
                WHEN cc.id IS NOT NULL AND cc.credito_activo=1 THEN
                    CASE WHEN cc.referencias_comerciales IS NOT NULL AND cc.referencias_comerciales!='' THEN 'X' ELSE 'SOLICITAR' END
                ELSE 'N/A'
            END                                                    AS ref_comerciales,

            COALESCE(c.ref_referencias,'')                         AS ref_referencias,
            COALESCE(c.ref_tiempo_relacion,'')                     AS ref_tiempo_relacion,

            CASE
                WHEN cc.id IS NOT NULL AND cc.credito_activo=1 THEN COALESCE(c.ref_tiempo_credito,'')
                ELSE 'N/A'
            END                                                    AS ref_tiempo_credito,

            COALESCE(c.ref_limite_credito,'')                      AS ref_limite_credito,
            COALESCE(c.metodo_pago,'')                             AS metodo_pago,
            COALESCE(u_cred.name,'')                               AS confirmacion,
            COALESCE(c.ref_observaciones,'')                       AS obs_referencias,
            COALESCE(cc.created_at,'')                             AS fecha_validacion_ref,
            COALESCE(u_cred.name,'')                               AS realizo,

            /* LETRA DE CAMBIO */
            CASE
                WHEN cc.id IS NOT NULL AND cc.credito_activo=1 THEN
                    CASE WHEN cc.letra_cambio=1 THEN 'X' ELSE 'SOLICITAR' END
                ELSE 'N/A'
            END                                                    AS letra_cambio,

            /* AVAL SOLIDARIO */
            CASE
                WHEN cc.id IS NOT NULL AND cc.credito_activo=1 THEN
                    CASE WHEN cc.aval_solidario=1 THEN 'X' ELSE 'SOLICITAR' END
                ELSE 'N/A'
            END                                                    AS aval_solidario,

            CASE WHEN d_contrato.id  IS NOT NULL THEN 'X' ELSE 'SOLICITAR' END AS doc_contrato,
            CASE WHEN d_fotos.id     IS NOT NULL THEN 'X' ELSE 'SOLICITAR' END AS doc_fotos,

            COALESCE(ec.descripcion,'')                            AS estado_cliente,
            COALESCE(cc.credito,0)                                 AS monto_credito,
            COALESCE(cc.dias_credito,0)                            AS plazo_credito,
            COALESCE(obs_last.observacion,'')                      AS observaciones,
            COALESCE(cc.autorizacion_gerencia,'')                  AS autorizado_gerencia,
            COALESCE(cc.fecha_vigencia,'')                         AS fecha_notif_limite

        FROM cliente c
        LEFT JOIN users u_vend        ON u_vend.id        = c.vendedor
        LEFT JOIN cliente_credito cc  ON cc.cliente_id    = c.id AND cc.activo = 1
        LEFT JOIN users u_cred        ON u_cred.id        = cc.users_id
        LEFT JOIN estado_cliente ec   ON ec.id            = c.estado_cliente_id
        LEFT JOIN cliente_documentos d_escritura ON d_escritura.cliente_id = c.id AND d_escritura.tipo_documento = 'escritura_empresa'
        LEFT JOIN cliente_documentos d_dni       ON d_dni.cliente_id       = c.id AND d_dni.tipo_documento       = 'dni_representante'
        LEFT JOIN cliente_documentos d_rtn       ON d_rtn.cliente_id       = c.id AND d_rtn.tipo_documento       = 'rtn'
        LEFT JOIN cliente_documentos d_permiso   ON d_permiso.cliente_id   = c.id AND d_permiso.tipo_documento   = 'permiso_operacion'
        LEFT JOIN cliente_documentos d_croquis   ON d_croquis.cliente_id   = c.id AND d_croquis.tipo_documento   = 'croquis'
        LEFT JOIN cliente_documentos d_contrato  ON d_contrato.cliente_id  = c.id AND d_contrato.tipo_documento  = 'contrato_arrendamiento'
        LEFT JOIN cliente_documentos d_fotos     ON d_fotos.cliente_id     = c.id AND d_fotos.tipo_documento     = 'fotos_establecimiento'
        LEFT JOIN (
            SELECT cliente_id, observacion
            FROM cliente_observaciones
            WHERE id IN (SELECT MAX(id) FROM cliente_observaciones GROUP BY cliente_id)
        ) obs_last ON obs_last.cliente_id = c.id
        WHERE {$where}
        ORDER BY c.nombre ASC
        ";

        return DB::select($sql, $params);
    }

    private function sqlSinCredito($vendedorId = null, $estado = null)
    {
        $where = "cc.id IS NULL";
        $params = [];
        if ($vendedorId) { $where .= " AND c.vendedor = ?"; $params[] = $vendedorId; }
        if ($estado)     { $where .= " AND c.estado_cliente_id = ?"; $params[] = $estado; }

        return DB::select("
        SELECT
            c.id                              AS id,
            COALESCE(u_vend.name,'')          AS vendedor,
            c.nombre                          AS cliente,
            c.id                              AS codigo,
            COALESCE(ec.descripcion,'')       AS estado,
            COALESCE(obs_last.observacion,'') AS observaciones
        FROM cliente c
        LEFT JOIN users u_vend       ON u_vend.id     = c.vendedor
        LEFT JOIN estado_cliente ec  ON ec.id         = c.estado_cliente_id
        LEFT JOIN cliente_credito cc ON cc.cliente_id = c.id AND cc.activo = 1 AND cc.credito_activo = 1
        LEFT JOIN (
            SELECT cliente_id, observacion
            FROM cliente_observaciones
            WHERE id IN (SELECT MAX(id) FROM cliente_observaciones GROUP BY cliente_id)
        ) obs_last ON obs_last.cliente_id = c.id
        WHERE {$where}
        ORDER BY c.nombre ASC
        ", $params);
    }

    private function sqlGobierno($vendedorId = null, $estado = null)
    {
        $where = "c.tipo_cliente_id = 1";   /* Corporativo (B) */
        $params = [];
        if ($vendedorId) { $where .= " AND c.vendedor = ?"; $params[] = $vendedorId; }
        if ($estado)     { $where .= " AND c.estado_cliente_id = ?"; $params[] = $estado; }

        return DB::select("
        SELECT
            c.id                            AS id,
            COALESCE(u_vend.name,'')        AS vendedor,
            c.nombre                        AS cliente,
            c.id                            AS codigo,
            COALESCE(cc.dias_credito,0)     AS plazo_credito,
            COALESCE(ec.descripcion,'')     AS estado_cliente
        FROM cliente c
        LEFT JOIN users u_vend       ON u_vend.id     = c.vendedor
        LEFT JOIN estado_cliente ec  ON ec.id         = c.estado_cliente_id
        LEFT JOIN cliente_credito cc ON cc.cliente_id = c.id AND cc.activo = 1
        WHERE {$where}
        ORDER BY c.nombre ASC
        ", $params);
    }

    /* ─────────────────────────────────────────────
     *  DataTable AJAX – hoja 1
     * ───────────────────────────────────────────── */
    private function normId($v) { return (!$v || $v === 'null') ? null : $v; }

    public function consultaGeneral($vendedorId = null, $estado = null)
    {
        try {
            $rows = $this->sqlGeneral($this->normId($vendedorId), $this->normId($estado));
            $item = 0;
            foreach ($rows as &$r) { $r->item = ++$item; }
            return DataTables::of($rows)->rawColumns([])->make(true);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* DataTable AJAX – hoja 2 */
    public function consultaSinCredito($vendedorId = null, $estado = null)
    {
        try {
            $rows = $this->sqlSinCredito($this->normId($vendedorId), $this->normId($estado));
            $item = 0;
            foreach ($rows as &$r) { $r->item = ++$item; }
            return DataTables::of($rows)->rawColumns([])->make(true);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* DataTable AJAX – hoja 3 */
    public function consultaGobierno($vendedorId = null, $estado = null)
    {
        try {
            $rows = $this->sqlGobierno($this->normId($vendedorId), $this->normId($estado));
            $item = 0;
            foreach ($rows as &$r) { $r->item = ++$item; }
            return DataTables::of($rows)->rawColumns([])->make(true);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ─────────────────────────────────────────────
     *  Exportar PDF
     * ───────────────────────────────────────────── */
    public function exportarPdf(Request $request, $vendedorId = null, $estado = null)
    {
        try {
            $vid = $this->normId($vendedorId);
            $eid = $this->normId($estado);

            $general    = $this->sqlGeneral($vid, $eid);
            $sinCredito = $this->sqlSinCredito($vid, $eid);
            $gobierno   = $this->sqlGobierno($vid, $eid);

            $item = 0; foreach ($general    as &$r) { $r->item = ++$item; }
            $item = 0; foreach ($sinCredito as &$r) { $r->item = ++$item; }
            $item = 0; foreach ($gobierno   as &$r) { $r->item = ++$item; }

            $pdf = Pdf::loadView('pdf.reporteclientes', compact('general','sinCredito','gobierno'))
                      ->setPaper('legal', 'landscape');

            return $pdf->download("ReporteClientes_" . now()->format('Y-m-d') . ".pdf");
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ─────────────────────────────────────────────
     *  Exportar Excel
     * ───────────────────────────────────────────── */
    public function exportarExcel(Request $request, $vendedorId = null, $estado = null)
    {
        try {
            $vid = $this->normId($vendedorId);
            $eid = $this->normId($estado);

            $general    = $this->sqlGeneral($vid, $eid);
            $sinCredito = $this->sqlSinCredito($vid, $eid);
            $gobierno   = $this->sqlGobierno($vid, $eid);

            $item = 0; foreach ($general    as &$r) { $r->item = ++$item; }
            $item = 0; foreach ($sinCredito as &$r) { $r->item = ++$item; }
            $item = 0; foreach ($gobierno   as &$r) { $r->item = ++$item; }

            $usuario = Auth::user() ? Auth::user()->name : 'Sistema';

            return Excel::download(
                new ReporteClientesExport($general, $sinCredito, $gobierno, $usuario),
                "ReporteClientes_" . now()->format('Y-m-d') . ".xlsx"
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
