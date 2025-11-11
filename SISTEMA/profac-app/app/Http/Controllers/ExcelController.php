<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\Escalas\ProductosPlantillaExport;
use App\Exports\Escalas\ProductosPlantillaExportManual;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Imports\Escalas\PreciosProductoCargaImport;
// Arriba de tu controlador:
use Maatwebsite\Excel\Excel as ExcelFormat;

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
    'archivo_excel'      => 'required|file|max:20480', // 20 MB
    'tipoCategoria'      => 'required|in:escalable,manual',
    'tipoFiltro'         => 'required|in:1,2',
    'valorFiltro'        => 'required|integer',
    'categoriaPrecioId'  => 'required|exists:categoria_precios,id',
    'defaultUnidadMedidaId' => 'nullable|integer|exists:unidad_medida_venta,id',
], [
    'archivo_excel.required' => 'Subí un archivo.',
    'archivo_excel.file'     => 'Archivo inválido.',
    'archivo_excel.max'      => 'El archivo no puede superar 20 MB.',
]);
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
            // Construcción del import SIN argumentos nombrados (compat PHP 7.x)
$import = new PreciosProductoCargaImport(
    $request->input('tipoCategoria'),
    (int)$request->input('tipoFiltro'),
    (int)$request->input('valorFiltro'),
    (int)$request->input('categoriaPrecioId'),
    (int)$userId,
    $request->input('defaultUnidadMedidaId') ? (int)$request->input('defaultUnidadMedidaId') : null
);


// 0) Forzar carpeta temp en runtime (por si config está cacheado)
config(['excel.temporary_files.local_path' => storage_path('app/excel-temp')]);

// 1) Tomar archivo y extensión
$file = $request->file('archivo_excel');
$ext  = strtolower($file->getClientOriginalExtension()); // ext real (xlsx/xls/csv)

// 2) Validar extensión permitida
$allowed = ['xlsx','xls','csv'];
if (!in_array($ext, $allowed)) {
    return response()->json([
        'icon'  => 'error',
        'title' => 'Validación',
        'text'  => 'El archivo debe ser XLSX, XLS o CSV.',
    ], 422);
}

// 3) Elegir lector
$readerType = ($ext === 'csv') ? ExcelFormat::CSV : ExcelFormat::XLSX;

// 4) Guardar SOLO UNA VEZ y preparar ruta completa
$storedPath = $file->storeAs('imports', 'probe.'.$ext, 'local'); // storage/app/imports/probe.ext
$full = storage_path('app/'.$storedPath);

// (opcional) ajustes CSV
if ($readerType === ExcelFormat::CSV) {
    config([
        'excel.csv.input_encoding' => 'UTF-8', // o 'ISO-8859-1'
        'excel.csv.delimiter'      => ',',     // o ';'
    ]);
}

// 5) Logs útiles
\Log::info('UPLOAD_META', [
  'ext'  => $ext,
  'name' => $file->getClientOriginalName(),
  'mime' => $file->getMimeType(),
  'size' => $file->getSize(),
]);

// 6) Diagnóstico ZIP/XML (opcional, deja si te sirve)
$zipArchiveExists = class_exists(\ZipArchive::class);
$rc = null; $contentTypesLen = null; $workbookLen = null;
if ($zipArchiveExists) {
    $zip = new \ZipArchive();
    $rc = $zip->open($full);
    if ($rc === true || $rc === 0) {
        $ct = $zip->getFromName('[Content_Types].xml');
        $wb = $zip->getFromName('xl/workbook.xml');
        $contentTypesLen = is_string($ct) ? strlen($ct) : null;
        $workbookLen     = is_string($wb) ? strlen($wb) : null;
        $zip->close();
    }
}
\Log::info('IMPORT_DIAG', [
    'exists_ziparchive' => $zipArchiveExists,
    'file_size' => @filesize($full),
    'open_rc' => $rc,
    'content_types_xml_len' => $contentTypesLen,
    'workbook_xml_len' => $workbookLen,
    'sys_temp_dir' => ini_get('sys_temp_dir'),
    'excel_temp_path' => config('excel.temporary_files.local_path'),
]);

// (opcional) chequeos extra de XML
$sheet1Len = $sharedLen = $stylesLen = null;
$zip = new \ZipArchive();
if ($zip->open($full) === true) {
    $sheet1Len = strlen((string)$zip->getFromName('xl/worksheets/sheet1.xml'));
    $sharedLen = strlen((string)$zip->getFromName('xl/sharedStrings.xml'));
    $stylesLen = strlen((string)$zip->getFromName('xl/styles.xml'));
    $zip->close();
}
\Log::info('IMPORT_XML_CHECK', compact('sheet1Len','sharedLen','stylesLen'));

$emptyXml = [];
$missingXml = [];
$allXml = [];
$zip = new \ZipArchive();
if ($zip->open($full) === true) {
    $index = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $st = $zip->statIndex($i);
        $index[$st['name']] = true;
        if (substr($st['name'], -4) === '.xml') {
            $allXml[] = $st['name'];
            $content = $zip->getFromIndex($i);
            $len = is_string($content) ? strlen($content) : 0;
            if ($len === 0) $emptyXml[] = $st['name'];
        }
    }
    $critical = [
        '_rels/.rels','docProps/core.xml','docProps/app.xml',
        'xl/workbook.xml','xl/_rels/workbook.xml.rels',
        'xl/sharedStrings.xml','xl/styles.xml','xl/theme/theme1.xml','xl/calcChain.xml',
        'xl/worksheets/sheet1.xml','xl/worksheets/sheet2.xml','xl/worksheets/sheet3.xml',
    ];
    foreach ($critical as $name) {
        if (!isset($index[$name])) $missingXml[] = $name;
    }
    $zip->close();
}
\Log::info('IMPORT_XML_SCAN', [
    'emptyXml'   => $emptyXml,
    'missingXml' => $missingXml,
    'allXmlCnt'  => count($allXml)
]);

// 7) Importar desde disco y con lector forzado
Excel::import(
    $import,
    $storedPath,
    'local',
    $readerType
);

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
                'text'  => "Leídas: {$stats['rows_read']} | Insertadas: {$stats['rows_inserted']} | Omitidas: {$stats['rows_skipped']}",
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
