<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FacturaInteres;
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
                (SELECT fecha_emision  FROM factura WHERE id = ap.factura_id)
                                            AS fechaFactura,
                (SELECT fecha_vencimiento FROM factura WHERE id = ap.factura_id)
                                            AS fechaVencimiento,
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
                ap.updated_at               AS ultimoRegistro,

                -- ── Interés por mora (misma fórmula que sp_calcular_intereses_factura) ──
                IFNULL(ci_activa.tasa_mensual, 0)  AS tasaInteres,
                GREATEST(DATEDIFF(CURDATE(), (SELECT fecha_vencimiento FROM factura WHERE id = ap.factura_id)), 0)
                                            AS diasVencidos,
                IF(
                    (SELECT estado_venta_id FROM factura WHERE id = ap.factura_id) = 1
                    AND ap.saldo > 0
                    AND DATEDIFF(CURDATE(), (SELECT fecha_vencimiento FROM factura WHERE id = ap.factura_id)) > 0
                    AND ci_activa.id IS NOT NULL,
                    ROUND(
                        ap.saldo
                        * (ci_activa.tasa_mensual / 100.0)
                        * (DATEDIFF(CURDATE(), (SELECT fecha_vencimiento FROM factura WHERE id = ap.factura_id)) / 30.0),
                        2
                    ),
                    0.00
                )                           AS interes,
                ci_activa.id                AS configuracionInteresId

            FROM aplicacion_pagos ap

            LEFT JOIN (
                SELECT id, tasa_mensual
                FROM   configuracion_intereses
                WHERE  estado = 1
                  AND  fecha_vigencia <= CURDATE()
                ORDER  BY fecha_vigencia DESC
                LIMIT  1
            ) ci_activa ON 1 = 1

            WHERE ap.cliente_id = ?
              AND ap.estado = 1
              AND ap.estado_cerrado <> 2
              AND ap.saldo > 0
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
            ->addColumn('interesBadge', function ($cuenta) {
                $interes = (float) ($cuenta->interes ?? 0);
                if ($interes > 0) {
                    $dias    = (int)   ($cuenta->diasVencidos ?? 0);
                    $tasa    = (float) ($cuenta->tasaInteres  ?? 0);
                    $capital = (float) ($cuenta->saldo        ?? 0);
                    // Fórmula manual: Interés = Capital × (Tasa% / 100) × (Días / 30)
                    $formula = 'Fórmula: L ' . number_format($capital, 2) . ' × (' . number_format($tasa, 4) . '% / 100) × (' . $dias . ' días / 30) = L ' . number_format($interes, 2);
                    return '<span class="badge" style="background:#fee2e2;color:#111;font-size:11.5px;padding:4px 8px;" title="' . htmlspecialchars($formula) . '">'
                         . '<i class="fa fa-clock-o mr-1" style="color:#c0392b;"></i>'
                         . $dias . ' días — L. ' . number_format($interes, 2)
                         . '</span>';
                }
                return '<span class="badge" style="background:#f0f4f8;color:#666;font-size:11px;">—</span>';
            })
            ->addColumn('estadoBadge', function ($cuenta) {
                if ($cuenta->estadoCierre) {
                    return '<span class="badge badge-secondary">Cerrada</span>';
                }
                return '<span class="badge badge-warning text-dark">Pendiente</span>';
            })
            ->rawColumns(['acciones', 'saldoBadge', 'interesBadge', 'estadoBadge'])
            ->make(true);
    }

    // ─── Consultar interés de una factura (idempotente — no persiste) ─────────
    public function consultarInteres($facturaId)
    {
        $facturaId    = (int) $facturaId;
        $fechaCalculo = request('fecha_pago')
            ? date('Y-m-d', strtotime(request('fecha_pago')))
            : date('Y-m-d');

        $resultado = DB::select("CALL sp_calcular_intereses_factura(?, ?)", [
            $facturaId,
            $fechaCalculo,
        ]);

        if (empty($resultado)) {
            return response()->json(['aplica' => false, 'monto_interes' => 0], 200);
        }

        return response()->json($resultado[0], 200);
    }

    // ─── Registrar decisión de no cobrar interés ──────────────────────────────
    public function registrarNoCobrarInteres(Request $request)
    {
        $request->validate([
            'factura_id'    => 'required|integer|exists:factura,id',
            'motivo'        => 'nullable|string|max:500',
        ]);

        $facturaId = (int) $request->factura_id;

        // Solo se registra si hay un interés pendiente ya persistido
        $interesExistente = FacturaInteres::pendientePorFactura($facturaId);

        if ($interesExistente) {
            $interesExistente->update([
                'usr_no_cobro'  => Auth::id(),
                'fecha_no_cobro' => now(),
                'motivo_no_cobro' => $request->motivo,
            ]);
        }

        return response()->json([
            'icon'  => 'success',
            'title' => 'Registrado',
            'text'  => 'Decisión de no cobrar interés registrada.',
        ], 200);
    }

    // ─── Movimientos (solo lectura) ──────────────────────────────────────────
    public function listarMovimientos(Request $request, $id)
    {
        $id = (int) $id;
        $consulta = DB::select("
            SELECT
                ot.id                AS codigoMovimiento,
                ot.aplicacion_pagos_id AS codigoPago,
                (SELECT cai FROM factura WHERE id = ot.factura_id) AS correlativo,
                FORMAT(ot.monto, 2)  AS monto,
                ot.tipo_movimiento,
                ot.comentario,
                ot.estado            AS estadoMov,
                (SELECT name FROM users WHERE id = ot.usr_registro) AS userRegistro,
                ot.created_at        AS fechaRegistro,
                ot.factura_id
            FROM otros_movimientos ot
            INNER JOIN aplicacion_pagos ap ON ap.id = ot.aplicacion_pagos_id
            WHERE ap.cliente_id = ? AND ap.estado = 1 AND ot.estado = 1
        ", [$id]);

        return DataTables::of($consulta)
            ->rawColumns([])
            ->make(true);
    }

    public function exportarMovimientosExcel($id)
    {
        $id = (int) $id;
        $rows = DB::select("
            SELECT
                ot.id                AS 'ID',
                ot.aplicacion_pagos_id AS 'Cod. Pago',
                (SELECT cai FROM factura WHERE id = ot.factura_id) AS 'Correlativo',
                FORMAT(ot.monto, 2)  AS 'Monto',
                ot.tipo_movimiento   AS 'Tipo Movimiento',
                ot.comentario        AS 'Comentario',
                ot.estado            AS 'Estado',
                (SELECT name FROM users WHERE id = ot.usr_registro) AS 'Usuario',
                ot.created_at        AS 'Fecha Registro'
            FROM otros_movimientos ot
            INNER JOIN aplicacion_pagos ap ON ap.id = ot.aplicacion_pagos_id
            WHERE ap.cliente_id = ? AND ap.estado = 1 AND ot.estado = 1
            ORDER BY ot.id DESC
        ", [$id]);

        $cliente = DB::table('cliente')->where('id', $id)->value('nombre') ?? "cliente_{$id}";
        $slug    = preg_replace('/[^a-zA-Z0-9_]/', '_', substr($cliente, 0, 30));
        $fecha   = now()->format('Y-m-d_His');

        $headers = ['ID', 'Cod. Pago', 'Correlativo', 'Monto', 'Tipo Movimiento', 'Comentario', 'Estado', 'Usuario', 'Fecha Registro'];
        $data    = array_map(fn($r) => (array) $r, $rows);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ArrayExport($headers, $data),
            "movimientos_{$slug}_{$fecha}.xlsx"
        );
    }

    // ─── Créditos y Abonos (solo lectura) ────────────────────────────────────
    public function listarAbonos(Request $request, $id)
    {
        $id = (int) $id;
        $consulta = DB::select("
            SELECT
                ac.id                AS codigoAbono,
                ac.aplicacion_pagos_id AS codigoPago,
                (SELECT cai FROM factura WHERE id = ac.factura_id) AS correlativo,
                FORMAT(ac.monto_abonado, 2) AS monto,
                ac.comentario        AS comentarioabono,
                ac.estado_abono      AS estadoAbono,
                (SELECT name FROM users WHERE id = ac.usr_registro) AS userRegistro,
                ac.fecha_pago        AS fechaDeposito,
                ac.created_at        AS fechaRegistro,
                ac.factura_id
            FROM abonos_creditos ac
            INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
            WHERE ap.cliente_id = ? AND ap.estado = 1 AND ac.estado_abono = 1
        ", [$id]);

        return DataTables::of($consulta)
            ->rawColumns([])
            ->make(true);
    }

    public function exportarAbonosExcel($id)
    {
        $id = (int) $id;
        $rows = DB::select("
            SELECT
                ac.id                AS 'ID',
                ac.aplicacion_pagos_id AS 'Cod. Pago',
                (SELECT cai FROM factura WHERE id = ac.factura_id) AS 'Correlativo',
                FORMAT(ac.monto_abonado, 2) AS 'Monto Abonado',
                ac.comentario        AS 'Comentario',
                ac.estado_abono      AS 'Estado',
                (SELECT name FROM users WHERE id = ac.usr_registro) AS 'Usuario',
                ac.fecha_pago        AS 'Fecha Depósito',
                ac.created_at        AS 'Fecha Registro'
            FROM abonos_creditos ac
            INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
            WHERE ap.cliente_id = ? AND ap.estado = 1 AND ac.estado_abono = 1
            ORDER BY ac.id DESC
        ", [$id]);

        $cliente = DB::table('cliente')->where('id', $id)->value('nombre') ?? "cliente_{$id}";
        $slug    = preg_replace('/[^a-zA-Z0-9_]/', '_', substr($cliente, 0, 30));
        $fecha   = now()->format('Y-m-d_His');

        $headers = ['ID', 'Cod. Pago', 'Correlativo', 'Monto Abonado', 'Comentario', 'Estado', 'Usuario', 'Fecha Depósito', 'Fecha Registro'];
        $data    = array_map(fn($r) => (array) $r, $rows);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ArrayExport($headers, $data),
            "abonos_{$slug}_{$fecha}.xlsx"
        );
    }

    // ─── Imprimir estado de cuenta PDF ───────────────────────────────────────
    public function imprimirEstadoCuenta($idClientepdf)
    {
        $estadoCuenta = DB::select("CALL estadoCuenta_sp(?)", [$idClientepdf]);

        $estadoCuenta = array_map(function ($row) {
            $row->acumulado = $row->acumulado ?? $row->Acumulado ?? 0;
            $row->interes   = $row->interes ?? 0;
            return $row;
        }, $estadoCuenta);

        $estadoCuenta = array_values(array_filter($estadoCuenta, function ($row) {
            return (float) ($row->saldo ?? 0) > 0;
        }));

        // Recalcular acumulado = saldo + interés acumulados, después del filtrado
        $runningTotal = 0;
        foreach ($estadoCuenta as $row) {
            $runningTotal   += (float) ($row->saldo ?? 0) + (float) ($row->interes ?? 0);
            $row->acumulado  = $runningTotal;
        }

        if (empty($estadoCuenta)) {
            $nombreCliente  = DB::table('cliente')->where('id', (int) $idClientepdf)->value('nombre') ?? 'Cliente #'.$idClientepdf;
            $sinMovimientos = true;
        } else {
            $nombreCliente  = $estadoCuenta[0]->cliente;
            $sinMovimientos = false;
        }

        $pdf = PDF::loadView('/pdf/estadocuentaAplicacion', compact('estadoCuenta', 'nombreCliente', 'sinMovimientos'))
                  ->setPaper('A4', 'landscape');

        return $pdf->stream('ESTADO_CUENTA.pdf');
    }
}
