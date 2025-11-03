<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\Escalas\ProductosPlantillaExport;
use App\Exports\Escalas\ProductosPlantillaExportManual;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Imports\Escalas\PreciosProductoCargaImport;

class ExcelController extends Controller
{
    public function descargarPlantilla(Request $request)
    {
         $tipoCategoria = $request->input('tipoCategoria'); // escalable o manual
        $tipoFiltro = $request->input('tipoFiltro');
        $valorFiltro = $request->input('listaTipoFiltro');
        $valorCategoria = $request->input('listaTipoFiltroCatPrecios');

        if ($tipoCategoria === 'manual') {
            return Excel::download(
                new ProductosPlantillaExportManual($tipoFiltro, $valorFiltro, $valorCategoria),
                'plantilla_productos_manual.xlsx'
            );
        }

        return Excel::download(
            new ProductosPlantillaExport($tipoFiltro, $valorFiltro, $valorCategoria),
            'plantilla_productos_escalable.xlsx'
        );
    }


    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        // Aquí podés procesarlo o guardarlo
        // Excel::import(new TuImportClass, $request->file('archivo_excel'));

        return response()->json([
            'icon' => 'success',
            'title' => 'Archivo recibido',
            'text' => 'El archivo Excel se subió correctamente.',
        ]);
    }

    public function procesarExcelPrecios(Request $request)
    {
        $v = Validator::make($request->all(), [
            'archivo_excel'      => 'required|file|mimes:xlsx,xls|max:20480',
            'tipoCategoria'      => 'required|in:escalable,manual',
            'tipoFiltro'         => 'required|in:1,2',
            'valorFiltro'        => 'required|integer',
            'categoriaPrecioId'  => 'required|exists:categoria_precios,id',
            'defaultUnidadMedidaId' => 'nullable|integer|exists:unidad_medida_venta,id',
        ], [
            'defaultUnidadMedidaId.exists' => 'El defaultUnidadMedidaId no existe en unidad_medida_venta.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'icon'  => 'error',
                'title' => 'Validación',
                'text'  => $v->errors()->first(),
            ], 422);
        }

        try {
            $userId = auth()->id() ?? 1;

            $import = new PreciosProductoCargaImport(
                tipoCategoria: $request->input('tipoCategoria'),
                tipoFiltro: (int)$request->input('tipoFiltro'),
                valorFiltro: (int)$request->input('valorFiltro'),
                categoriaPrecioId: (int)$request->input('categoriaPrecioId'),
                userId: (int)$userId,
                defaultUnidadMedidaId: $request->input('defaultUnidadMedidaId') ? (int)$request->input('defaultUnidadMedidaId') : null
            );

            Excel::import($import, $request->file('archivo_excel'));

            $stats = $import->getStats();

            if (!empty($stats['missing_headers'])) {
                return response()->json([
                    'icon'  => 'error',
                    'title' => 'Encabezados inválidos',
                    'text'  => 'Faltan columnas: ' . implode(', ', $stats['missing_headers']),
                    'debug' => $stats,
                ], 422);
            }

            return response()->json([
                'icon'  => 'success',
                'title' => 'Procesado correctamente',
                'text'  => "Leídas: {$stats['rows_read']} | Insertadas: {$stats['rows_inserted']} | Inactivadas: {$stats['rows_inactivated']} | Omitidas: {$stats['rows_skipped']}",
                'debug' => $stats,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[procesarExcelPrecios] Error general', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'icon'  => 'error',
                'title' => 'Error al procesar',
                'text'  => 'Revisá el archivo/encabezados y volvé a intentar.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }


}
