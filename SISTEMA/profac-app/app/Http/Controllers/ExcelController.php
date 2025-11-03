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
    /**
     * Descarga la plantilla de Excel para carga de precios de productos.
     * - Lee parámetros de filtro desde la request (tipo de categoría, tipo de filtro, valor de filtro y categoría de precio).
     * - Si la categoría es "manual", genera un archivo con columnas para precios manuales.
     * - Si es "escalable", genera un archivo con columnas para precios base escalables.
     * - Devuelve un archivo .xlsx para ser descargado por el cliente.
     */
    public function descargarPlantilla(Request $request)
    {
        // Parámetros de la UI: tipo de categoría (escalable/manual), filtros y categoría de precios seleccionada.
         $tipoCategoria = $request->input('tipoCategoria'); // escalable o manual
        $tipoFiltro = $request->input('tipoFiltro');
        $valorFiltro = $request->input('listaTipoFiltro');
        $valorCategoria = $request->input('listaTipoFiltroCatPrecios');

        // En caso de "manual", se usa el export específico con encabezados/estructura manual.
        if ($tipoCategoria === 'manual') {
            return Excel::download(
                new ProductosPlantillaExportManual($tipoFiltro, $valorFiltro, $valorCategoria),
                'plantilla_productos_manual.xlsx'
            );
        }

        // Por defecto (o si es "escalable"), se usa el export escalable.
        return Excel::download(
            new ProductosPlantillaExport($tipoFiltro, $valorFiltro, $valorCategoria),
            'plantilla_productos_escalable.xlsx'
        );
    }


    /**
     * Punto de entrada simple para subir un Excel (demo/placeholder).
     * - Valida que se envíe un archivo de tipo Excel.
     * - No procesa el archivo; únicamente confirma la recepción exitosa.
     * - Responde en JSON para consumo por la UI (Swal/axios).
     */
    public function importarExcel(Request $request)
    {
        // Validación básica del archivo (tipo y tamaño).
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        // Aquí podría realizarse un import real con Excel::import(...).
        // Se deja comentado para ilustrar que es un endpoint de prueba/ejemplo.
        // Excel::import(new TuImportClass, $request->file('archivo_excel'));

        // Respuesta estándar de éxito para la UI.
        return response()->json([
            'icon' => 'success',
            'title' => 'Archivo recibido',
            'text' => 'El archivo Excel se subió correctamente.',
        ]);
    }

    /**
     * Procesa el Excel de precios:
     * - Valida inputs (archivo, tipo de categoría, filtros, existencia de categoría de precios).
     * - Instancia el importador PreciosProductoCargaImport con parámetros de contexto (usuario, filtros, fallback de unidad).
     * - Ejecuta la importación con Maatwebsite\Excel.
     * - Devuelve estadísticas de filas leídas/insertadas/inactivadas/omitidas.
     * - Maneja errores de validación y errores generales, registrándolos en logs y respondiendo en JSON.
     */
    public function procesarExcelPrecios(Request $request)
    {
        // Validación de parámetros recibidos desde el frontend.
        // Incluye validaciones de pertenencia (in) y existencia en BD (exists).
        $v = Validator::make($request->all(), [
            'archivo_excel'      => 'required|file|mimes:xlsx,xls|max:20480',
            'tipoCategoria'      => 'required|in:escalable,manual',
            'tipoFiltro'         => 'required|in:1,2',
            'valorFiltro'        => 'required|integer',
            'categoriaPrecioId'  => 'required|exists:categoria_precios,id',
            'defaultUnidadMedidaId' => 'nullable|integer|exists:unidad_medida_venta,id',
        ], [
            // Mensaje específico para cuando el id por defecto de unidad de medida no existe.
            'defaultUnidadMedidaId.exists' => 'El defaultUnidadMedidaId no existe en unidad_medida_venta.',
        ]);

        // Si la validación falla, se retorna error 422 con el primer mensaje relevante.
        if ($v->fails()) {
            return response()->json([
                'icon'  => 'error',
                'title' => 'Validación',
                'text'  => $v->errors()->first(),
            ], 422);
        }

        try {
            // Determinar el usuario autenticado (fallback a 1 si no hay auth disponible).
            $userId = auth()->id() ?? 1;

            // Construcción del importador con todos los parámetros necesarios para procesar el archivo.
            // - tipoCategoria: 'escalable' o 'manual'
            // - tipoFiltro/valorFiltro: reglas para acotar productos (marca o categoría)
            // - categoriaPrecioId: categoría de precios a aplicar
            // - userId: auditoría del creador de los registros
            // - defaultUnidadMedidaId: fallback opcional para unidad de medida en caso de ausencia
            $import = new PreciosProductoCargaImport(
                tipoCategoria: $request->input('tipoCategoria'),
                tipoFiltro: (int)$request->input('tipoFiltro'),
                valorFiltro: (int)$request->input('valorFiltro'),
                categoriaPrecioId: (int)$request->input('categoriaPrecioId'),
                userId: (int)$userId,
                defaultUnidadMedidaId: $request->input('defaultUnidadMedidaId') ? (int)$request->input('defaultUnidadMedidaId') : null
            );

            // Ejecución de la importación con Maatwebsite\Excel.
            Excel::import($import, $request->file('archivo_excel'));

            // Obtención de estadísticas generadas por el import (lecturas, inserciones, inactivaciones, omisiones, etc.).
            $stats = $import->getStats();

            // Si faltaron encabezados requeridos en el Excel, se responde con error 422 y detalle de columnas faltantes.
            if (!empty($stats['missing_headers'])) {
                return response()->json([
                    'icon'  => 'error',
                    'title' => 'Encabezados inválidos',
                    'text'  => 'Faltan columnas: ' . implode(', ', $stats['missing_headers']),
                    'debug' => $stats,
                ], 422);
            }

            // Respuesta de éxito con resumen de resultados de procesamiento.
            return response()->json([
                'icon'  => 'success',
                'title' => 'Procesado correctamente',
                'text'  => "Leídas: {$stats['rows_read']} | Insertadas: {$stats['rows_inserted']} | Inactivadas: {$stats['rows_inactivated']} | Omitidas: {$stats['rows_skipped']}",
                'debug' => $stats,
            ]);
        } catch (\Throwable $e) {
            // Log detallado del error (mensaje, archivo y línea) para auditoría/diagnóstico.
            \Log::error('[procesarExcelPrecios] Error general', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Respuesta genérica de error para la UI, con el mensaje técnico en "debug".
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error al procesar',
                'text'  => 'Revisá el archivo/encabezados y volvé a intentar.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }


}
