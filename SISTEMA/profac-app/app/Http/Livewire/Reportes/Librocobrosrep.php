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
        $clientes  = DB::table('cliente')->orderBy('nombre')->get(['id','nombre']);
        $vendedores = DB::table('users')->orderBy('name')->get(['id','name']);
        $bancos    = DB::table('banco')->orderBy('nombre')->get(['id','nombre']);

        return view('livewire.reportes.librocobrosrep', compact('clientes','vendedores','bancos'));
    }



    public function consulta(Request $request, $tipo, $fechaInicio, $fechaFinal)
    {
        try {
            // Tipo 3 = Libro de Cobros (conciliación bancaria) — sólo abonos reales
            if ($tipo == 3) {
                $sql = "
                    SELECT
                        DATE_FORMAT(ac.fecha_pago, '%Y-%m-%d')          AS fecha_pago,
                        f.nombre_cliente                                 AS cliente,
                        u.name                                           AS vendedor,
                        f.numero_secuencia_cai                          AS factura,
                        ROUND(ac.monto_abonado, 2)                      AS monto_cobrado,
                        CASE WHEN ROUND(ap.saldo, 2) <= 0.01
                             THEN 'PAGADA' ELSE 'PARCIAL'
                        END                                              AS estado_factura,
                        ROUND(ap.saldo, 2)                              AS saldo_pendiente,
                        b.nombre                                         AS banco,
                        ac.comentario                                    AS observaciones,
                        ROUND(IF(f.tipo_venta_id = 3, f.isv, 0), 2)    AS exonerado,
                        ROUND(f.sub_total_grabado, 2)                   AS gravado,
                        ROUND(f.sub_total_excento, 2)                   AS excento,
                        ROUND(f.sub_total, 2)                           AS subtotal,
                        ROUND(f.isv, 2)                                 AS isv,
                        ROUND(f.total, 2)                               AS total_factura,
                        b.cuenta                                         AS cuenta_banco
                    FROM abonos_creditos ac
                    INNER JOIN factura f            ON f.id  = ac.factura_id
                    INNER JOIN users u              ON u.id  = f.vendedor
                    INNER JOIN aplicacion_pagos ap  ON ap.factura_id = f.id AND ap.estado = 1
                    INNER JOIN banco b              ON b.id  = ac.banco_id
                    WHERE DATE(ac.fecha_pago) BETWEEN ? AND ?
                ";

                $bindings = [$fechaInicio, $fechaFinal];

                if ($request->filled('cliente_id')) {
                    $sql .= ' AND f.cliente_id = ?';
                    $bindings[] = $request->cliente_id;
                }
                if ($request->filled('vendedor_id')) {
                    $sql .= ' AND f.vendedor = ?';
                    $bindings[] = $request->vendedor_id;
                }
                if ($request->filled('banco_id')) {
                    $sql .= ' AND ac.banco_id = ?';
                    $bindings[] = $request->banco_id;
                }
                if ($request->filled('factura')) {
                    $sql .= ' AND f.numero_secuencia_cai LIKE ?';
                    $bindings[] = '%' . $request->factura . '%';
                }

                $sql .= ' ORDER BY ac.fecha_pago ASC, f.nombre_cliente ASC';

                $consulta = DB::select($sql, $bindings);

                return Datatables::of($consulta)
                    ->rawColumns([])
                    ->make(true);
            }

            // Otros tipos usan el SP original
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

    public function exportarPdf(Request $request, $tipo, $fechaInicio,$fechaFinal)
    {
        try {
    // Validación de parámetros
    if (!$tipo || !$fechaInicio ||!$fechaFinal ) {
        return response()->json([
            'message' => 'Faltan parámetros requeridos para la exportación del PDF.'
        ], 400);
    }

    // Obtener datos del procedimiento almacenado
    $consulta = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fechaInicio,$fechaFinal]);

    // Convertir los datos a arreglo para la vista
    $data = json_decode(json_encode($consulta), true);

    // Generar el PDF usando DomPDF
    $pdf = PDF::loadView('pdf.librocobrosrep', compact('data','fechaInicio','fechaFinal'))
              ->setPaper('oficio', 'landscape');

    // Retornar el PDF para descarga
    return $pdf->download(filename: "LibroCobros_{$fechaInicio}_a_{$fechaFinal}.pdf");

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el PDF.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }
    public function exportarExcel(Request $request, $tipo, $fechaInicio, $fechaFinal)
    {
        try {
            if (!$tipo || !$fechaInicio || !$fechaFinal) {
                return response()->json([
                    'message' => 'Faltan parámetros requeridos para la exportación del Excel.'
                ], 400);
            }

            $consulta = DB::select("CALL sp_reportesxfecha(?, ?, ?)", [$tipo, $fechaInicio, $fechaFinal]);
            $data = json_decode(json_encode($consulta), true);

            return Excel::download(new LibroCobrosExport($data, $fechaInicio, $fechaFinal), "LibroCobros_{$fechaInicio}_a_{$fechaFinal}.xlsx");

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el Excel.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }
}
