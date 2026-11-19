<?php

namespace App\Livewire\Auditoria;

use App\Services\Auditoria\ConsultaComprasVentas;
use App\Services\Auditoria\SincronizadorComprasVentas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class ComprasVentas extends Component
{
    public function render()
    {
        return view('livewire.auditoria.compras-ventas');
    }

    public function listar(Request $request, ConsultaComprasVentas $consulta)
    {
        $filtros = $consulta->filtros($request->all());
        $query = $consulta->construir($filtros)->select('a.*');

        return DataTables::of($query)
            ->editColumn('cantidad_documento', fn ($fila) => number_format((float) $fila->cantidad_documento, 4, '.', ''))
            ->editColumn('cantidad_base', fn ($fila) => number_format((float) $fila->cantidad_base, 4, '.', ''))
            ->editColumn('cantidad_movimiento_base', fn ($fila) => number_format((float) $fila->cantidad_movimiento_base, 4, '.', ''))
            ->make(true);
    }

    public function resumen(Request $request, ConsultaComprasVentas $consulta): JsonResponse
    {
        $query = $consulta->construir($consulta->filtros($request->all()));

        $resumen = $query->selectRaw("
            COUNT(*) as filas,
            COUNT(DISTINCT CASE WHEN tipo_movimiento = 'COMPRA' THEN compra_id END) as compras,
            COUNT(DISTINCT CASE WHEN tipo_movimiento = 'VENTA' THEN factura_id END) as ventas,
            SUM(CASE WHEN tipo_movimiento = 'COMPRA' THEN 1 ELSE 0 END) as lotes_compras,
            SUM(CASE WHEN tipo_movimiento = 'VENTA' THEN 1 ELSE 0 END) as lotes_ventas,
            SUM(CASE WHEN tipo_movimiento = 'COMPRA' THEN cantidad_base ELSE 0 END) as entradas,
            SUM(CASE WHEN tipo_movimiento = 'VENTA' AND estado_documento = 'ACTIVO' THEN ABS(cantidad_base) ELSE 0 END) as salidas_activas,
            SUM(CASE WHEN estado_documento = 'ANULADO' THEN 1 ELSE 0 END) as anulados
        ")->first();

        return response()->json($resumen);
    }

    public function sincronizar(SincronizadorComprasVentas $sincronizador): JsonResponse
    {
        $resultado = $sincronizador->sincronizar();

        return response()->json([
            'message' => 'Sincronización incremental completada.',
            'data' => $resultado,
        ]);
    }

    public function exportar(Request $request, ConsultaComprasVentas $consulta): StreamedResponse
    {
        $filtros = $consulta->filtros($request->all());
        $nombre = 'Auditoria_Compras_Ventas_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($consulta, $filtros) {
            set_time_limit(0);
            $salida = fopen('php://output', 'wb');

            fwrite($salida, "\xEF\xBB\xBF");
            fwrite($salida, "sep=;\r\n");
            fputcsv($salida, [
                'Movimiento', 'Fecha movimiento', 'Fecha ingreso compra',
                'Fecha creación factura', 'Fecha emisión factura', 'Factura ID', 'Compra ID',
                'Documento', 'CAI', 'Clasificación', 'Estado', 'Producto ID', 'Producto',
                'Lote', 'Bodega', 'Sección', 'Unidad compra', 'Factor compra',
                'Unidad venta', 'Factor venta', 'Cantidad documento', 'Cantidad base',
                'Movimiento base', 'Cliente', 'Proveedor', 'Usuario',
            ], ';');

            $consulta->construir($filtros)
                ->select('a.*')
                ->orderBy('a.id')
                ->chunkById(2000, function ($filas) use ($salida) {
                    foreach ($filas as $fila) {
                        fputcsv($salida, [
                            $fila->tipo_movimiento,
                            $fila->fecha_movimiento,
                            $fila->fecha_ingreso_compra,
                            $fila->fecha_creacion_factura,
                            $fila->fecha_emision_factura,
                            $fila->factura_id,
                            $fila->compra_id,
                            $this->valorCsv($fila->numero_documento),
                            $this->valorCsv($fila->cai),
                            $fila->clasificacion_factura,
                            $fila->estado_documento,
                            $fila->producto_id,
                            $this->valorCsv($fila->producto_nombre),
                            $fila->lote_id,
                            $this->valorCsv($fila->bodega_nombre),
                            $this->valorCsv($fila->seccion_nombre),
                            $this->valorCsv($fila->unidad_compra_nombre),
                            $fila->factor_unidad_compra,
                            $this->valorCsv($fila->unidad_venta_nombre),
                            $fila->factor_unidad_venta,
                            $fila->cantidad_documento,
                            $fila->cantidad_base,
                            $fila->cantidad_movimiento_base,
                            $this->valorCsv($fila->cliente_nombre),
                            $this->valorCsv($fila->proveedor_nombre),
                            $this->valorCsv($fila->usuario_nombre),
                        ], ';');
                    }

                    fflush($salida);
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }, 'a.id', 'id');

            fclose($salida);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function ultimaSincronizacion(): ?object
    {
        return DB::table('auditoria_compras_ventas_sync')
            ->where('estado', 'COMPLETADA')
            ->orderByDesc('id')
            ->first();
    }

    private function valorCsv(?string $valor): ?string
    {
        if ($valor !== null && preg_match('/^[=+\-@]/', $valor)) {
            return "'" . $valor;
        }

        return $valor;
    }
}
