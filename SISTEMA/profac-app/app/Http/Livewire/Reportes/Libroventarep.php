<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Exports\LibroVentaExport;
use Maatwebsite\Excel\Facades\Excel;

class Libroventarep extends Component
{
    public function render()
    {
        $clientes    = DB::select("SELECT id, nombre FROM cliente ORDER BY nombre ASC");
        $vendedores  = DB::select("SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC");
        $modosPago   = DB::select("SELECT id, descripcion FROM tipo_pago_venta ORDER BY descripcion ASC");
        return view('livewire.reportes.libroventarep', compact('clientes', 'vendedores', 'modosPago'));
    }


    public function consulta(Request $request, $tipo = null, $fechaInicio = null, $fechaFinal = null)
    {
        try {
            $fechaInicio = $request->input('fecha_desde', $fechaInicio ?? '1900-01-01');
            $fechaFinal = $request->input('fecha_hasta', $fechaFinal ?? date('Y-m-d'));
            $fechaInicio = $this->normalizarFecha($fechaInicio, '1900-01-01');
            $fechaFinal  = $this->normalizarFecha($fechaFinal, date('Y-m-d'));

            $consulta = $this->buildLibroVentaQuery($request, $fechaInicio, $fechaFinal)
                ->orderBy('factura.fecha_emision', 'ASC')
                ->get();

            $kpiTotalVendido = 0.0;
            $kpiTotalIsv = 0.0;
            $kpiTotalGravado = 0.0;
            $kpiTotalRegistros = 0;

            foreach ($consulta as $row) {
                $kpiTotalRegistros++;
                $kpiTotalVendido += (float) ($row->TOTAL ?? 0);
                $kpiTotalIsv += (float) ($row->ISV ?? 0);
                $kpiTotalGravado += (float) ($row->GRAVADO ?? 0);
            }

            return Datatables::of($consulta)
                ->with([
                    'kpi_total_vendido' => round($kpiTotalVendido, 2),
                    'kpi_total_isv' => round($kpiTotalIsv, 2),
                    'kpi_total_gravado' => round($kpiTotalGravado, 2),
                    'kpi_total_registros' => $kpiTotalRegistros,
                ])
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
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            $fechaInicio = $this->normalizarFecha($request->input('fecha_desde', $fechaInicio), '1900-01-01');
            $fechaFinal  = $this->normalizarFecha($request->input('fecha_hasta', $fechaFinal), date('Y-m-d'));

            $consulta = $this->buildLibroVentaQuery($request, $fechaInicio, $fechaFinal)
                ->orderBy('factura.fecha_emision', 'DESC')
                ->get()
                ->map(function ($row) {
                    return (array) $row;
                })
                ->values()
                ->all();

            $data = $consulta;

            $pdf = PDF::loadView('pdf.libroventarep', compact('data','fechaInicio','fechaFinal'))
                ->setPaper('oficio', 'landscape');

            $response = $pdf->download("Libroventa_{$fechaInicio}_a_{$fechaFinal}.pdf");

            $downloadToken = (string) $request->input('download_token', '');
            if ($downloadToken !== '') {
                $response->withCookie(cookie('lv_pdf_download_token', $downloadToken, 5, '/', null, false, false, false, 'Lax'));
            }

            return $response;

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el PDF.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }
    public function exportarExcel(Request $request, $tipo, $fechaInicio, $fechaFinal)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            $fechaInicio = $this->normalizarFecha($request->input('fecha_desde', $fechaInicio), '1900-01-01');
            $fechaFinal  = $this->normalizarFecha($request->input('fecha_hasta', $fechaFinal), date('Y-m-d'));

            $data = $this->buildLibroVentaQuery($request, $fechaInicio, $fechaFinal)
                ->orderBy('factura.fecha_emision', 'ASC')
                ->get()
                ->map(function ($row) {
                    return (array) $row;
                })
                ->values()
                ->all();

            $response = Excel::download(
                new LibroVentaExport($data, $fechaInicio, $fechaFinal),
                "LibroVenta_{$fechaInicio}_a_{$fechaFinal}.xlsx"
            );

            $downloadToken = (string) $request->input('download_token', '');
            if ($downloadToken !== '') {
                setcookie('lv_excel_download_token', $downloadToken, time() + 300, '/', '', false, false);
            }

            return $response;

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al generar el Excel.',
                'errorTh' => $e->getMessage(),
            ], 402);
        }
    }

    private function normalizarFecha($fecha, string $default): string
    {
        if (empty($fecha) || $fecha === 'todos' || $fecha === 'null') {
            return $default;
        }

        return $fecha;
    }

    private function buildLibroVentaQuery(Request $request, string $fechaInicio, string $fechaFinal)
    {
        $cliente = $request->input('cliente', $request->input('cliente_id'));
        $vendedor = $request->input('vendedor', $request->input('vendedor_id'));
        $factura = $request->input('factura');
        $modoPago = $request->input('modo_pago');

        $query = DB::table('factura')
            ->leftJoin('cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->leftJoin('users', 'factura.vendedor', '=', 'users.id')
            ->leftJoin('tipo_pago_venta', 'factura.tipo_pago_id', '=', 'tipo_pago_venta.id')
            ->select(
                'users.name as VENDEDOR',
                'cliente.nombre as CLIENTE',
                'factura.numero_secuencia_cai as FACTURA',
                DB::raw("ROUND(CASE WHEN factura.tipo_venta_id = 3 THEN
                    COALESCE(factura.sub_total, 0) - COALESCE((
                        SELECT SUM(vhp.sub_total_s) FROM venta_has_producto vhp
                        WHERE vhp.factura_id = factura.id
                        AND ((DATE(factura.fecha_emision) < '2026-06-07' AND COALESCE(vhp.isv,0) = 0)
                             OR (DATE(factura.fecha_emision) >= '2026-06-07' AND vhp.tipo_precio = '1'))
                    ), 0)
                ELSE 0 END, 2) as EXONERADO"),
                DB::raw("ROUND(COALESCE(factura.sub_total_grabado, 0), 2) as GRAVADO"),
                DB::raw("ROUND(CASE WHEN factura.tipo_venta_id = 3 THEN
                    COALESCE((
                        SELECT SUM(vhp.sub_total_s) FROM venta_has_producto vhp
                        WHERE vhp.factura_id = factura.id
                        AND ((DATE(factura.fecha_emision) < '2026-06-07' AND COALESCE(vhp.isv,0) = 0)
                             OR (DATE(factura.fecha_emision) >= '2026-06-07' AND vhp.tipo_precio = '1'))
                    ), 0)
                ELSE COALESCE(factura.sub_total_excento, 0) END, 2) as EXCENTO"),
                DB::raw("ROUND(COALESCE(factura.sub_total, 0), 2) as SUBTOTAL"),
                DB::raw("ROUND(CASE WHEN factura.tipo_venta_id = 3 THEN 0 ELSE COALESCE(factura.isv, 0) END, 2) as ISV"),
                DB::raw("ROUND(COALESCE(factura.total, 0), 2) as TOTAL"),
                'factura.fecha_emision as FECHA VENTA'
            )
            ->whereBetween('factura.fecha_emision', [$fechaInicio, $fechaFinal]);

        if (!empty($cliente)) {
            $query->where('factura.cliente_id', $cliente);
        }
        if (!empty($vendedor)) {
            $query->where('factura.vendedor', $vendedor);
        }
        if (!empty($factura)) {
            $query->where('factura.numero_secuencia_cai', 'LIKE', '%' . $factura . '%');
        }
        if (!empty($modoPago)) {
            $query->where('factura.tipo_pago_id', $modoPago);
        }

        return $query;
    }
}
