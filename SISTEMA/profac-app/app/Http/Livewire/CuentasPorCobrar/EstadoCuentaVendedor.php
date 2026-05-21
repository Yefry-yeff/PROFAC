<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use DataTables;
use PDF;

class EstadoCuentaVendedor extends Component
{
    public function render()
    {
        return view('livewire.cuentas-por-cobrar.estado-cuenta-vendedor');
    }

    // ─── Buscar clientes (Select2) ────────────────────────────────────────────
    public function listarClientes(Request $request)
    {
        $search = $request->search ?? '';
        $clientes = DB::select(
            "SELECT id, CONCAT(id,' - ',nombre) AS text
             FROM cliente
             WHERE (id LIKE ? OR nombre LIKE ?)
             LIMIT 15",
            ["%{$search}%", "%{$search}%"]
        );

        return response()->json(['results' => $clientes], 200);
    }

    // ─── Listado de cuentas por cobrar (DataTable, solo lectura) ─────────────
    public function listarEstadoCuenta($id)
    {
        // Inicializar registros del cliente en aplicacion_pagos si no existen
        $existencia = DB::selectOne(
            "SELECT COUNT(*) AS existe
             FROM aplicacion_pagos ap
             INNER JOIN factura fa ON fa.id = ap.factura_id
             INNER JOIN cliente cli ON cli.id = fa.cliente_id
             WHERE ap.estado = 1 AND cli.id = ?",
            [$id]
        );

        $facturasActivas = DB::selectOne(
            "SELECT COUNT(*) AS num
             FROM factura fa
             INNER JOIN cliente cli ON cli.id = fa.cliente_id
             WHERE fa.estado_venta_id = 1 AND cli.id = ?",
            [$id]
        );

        $facturasEnPagos = DB::selectOne(
            "SELECT COUNT(*) AS num
             FROM aplicacion_pagos
             WHERE factura_id IN (
                 SELECT fa.id FROM factura fa
                 INNER JOIN cliente cli ON cli.id = fa.cliente_id
                 WHERE fa.estado_venta_id = 1 AND cli.id = ?
             )",
            [$id]
        );

        if ($existencia->existe == 0) {
            DB::select(
                "CALL sp_aplicacion_pagos('1', ?, ?, '0','na','0','0','0', @estado, @msjResultado)",
                [$id, Auth::user()->id]
            );
        } elseif ($facturasActivas->num > $facturasEnPagos->num) {
            DB::select(
                "CALL sp_aplicacion_pagos('3', ?, ?, '0','na','0','0','0', @estado, @msjResultado)",
                [$id, Auth::user()->id]
            );
        }

        $cuentas = DB::select("
            SELECT
                ap.id                       AS codigoPago,
                ap.factura_id               AS idFactura,
                (SELECT cai FROM factura WHERE id = ap.factura_id)
                                            AS codigoFactura,
                (SELECT fecha_emision FROM factura WHERE id = ap.factura_id)
                                            AS fechaFactura,
                ap.total_factura_cargo      AS cargo,
                ap.total_notas_credito      AS notasCredito,
                ap.total_nodas_debito       AS notasDebito,
                ap.credito_abonos           AS abonosCargo,
                ap.movimiento_suma          AS movSuma,
                ap.movimiento_resta         AS movResta,
                ap.retencion_isv_factura    AS isv,
                ap.saldo                    AS saldo,
                ap.estado_retencion_isv     AS estadoRetencion,
                ap.retencion_aplicada       AS retencionAplicada,
                ap.estado                   AS estado,
                ap.estado_cerrado           AS estadoCierre,
                ap.created_at               AS fechaRegistro,
                ap.updated_at               AS ultimoRegistro
            FROM aplicacion_pagos ap
            WHERE ap.cliente_id = ?
              AND ap.estado = 1
              AND ap.estado_cerrado <> 2
              AND ap.saldo <> 0
        ", [$id]);

        return DataTables::of($cuentas)
            ->addColumn('acciones', function ($cuenta) use ($id) {
                $btnDetalle = '<a href="/detalle/venta/' . $cuenta->idFactura . '"
                                  class="btn btn-sm btn-outline-info ec-btn-detalle"
                                  title="Ver detalle de venta" target="_blank">
                                  <i class="fa-solid fa-eye"></i> Detalle
                              </a>';

                $btnPdf = '<a href="/factura/cooporativo/' . $cuenta->idFactura . '"
                              class="btn btn-sm btn-outline-secondary ec-tbl-pdf ms-1"
                              title="Imprimir factura" target="_blank">
                              <i class="fa-solid fa-file-pdf"></i>
                           </a>';

                return '<div class="d-flex gap-1">' . $btnDetalle . $btnPdf . '</div>';
            })
            ->addColumn('saldoBadge', function ($cuenta) {
                $saldo = number_format($cuenta->saldo, 2);
                $cls   = $cuenta->saldo > 0 ? 'danger' : 'success';
                return '<span class="badge badge-' . $cls . '">L. ' . $saldo . '</span>';
            })
            ->addColumn('estadoBadge', function ($cuenta) {
                if ($cuenta->estadoCierre) {
                    return '<span class="badge badge-secondary">Cerrada</span>';
                }
                return '<span class="badge badge-warning text-dark">Pendiente</span>';
            })
            ->rawColumns(['acciones', 'saldoBadge', 'estadoBadge'])
            ->make(true);
    }

    // ─── Imprimir estado de cuenta PDF ───────────────────────────────────────
    public function imprimirEstadoCuenta($idClientepdf)
    {
        $estadoCuenta = DB::select("CALL estadoCuenta_sp(?)", [$idClientepdf]);

        if (empty($estadoCuenta)) {
            abort(404, 'No se encontró información de estado de cuenta para este cliente.');
        }

        $pdf = PDF::loadView('/pdf/estadocuentaAplicacion', compact('estadoCuenta'))
                  ->setPaper('A4', 'landscape');

        return $pdf->stream('ESTADO_CUENTA.pdf');
    }
}
