<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\Escalas\ProductosPlantillaExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function descargarPlantilla(Request $request)
    {
        $tipoFiltro = $request->input('tipoFiltro');       // 1 = Marca, 2 = Categoría
        $valorFiltro = $request->input('listaTipoFiltro');

        return Excel::download(new ProductosPlantillaExport($tipoFiltro, $valorFiltro), 'plantilla_productos.xlsx');
    }
}
