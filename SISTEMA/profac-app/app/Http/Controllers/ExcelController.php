<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\Escalas\ProductosPlantillaExport;
use App\Exports\Escalas\ProductosPlantillaExportManual;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function descargarPlantilla(Request $request)
    {
         $tipoCategoria = $request->input('tipoCategoria'); // escalable o manual
        $tipoFiltro = $request->input('tipoFiltro');
        $valorFiltro = $request->input('listaTipoFiltro');

        if ($tipoCategoria === 'manual') {
            return Excel::download(
                new ProductosPlantillaExportManual($tipoFiltro, $valorFiltro),
                'plantilla_productos_manual.xlsx'
            );
        }

        return Excel::download(
            new ProductosPlantillaExport($tipoFiltro, $valorFiltro),
            'plantilla_productos_escalable.xlsx'
        );
    }
}
