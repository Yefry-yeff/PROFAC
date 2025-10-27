<?php

namespace App\Http\Controllers;

use App\Exports\Escalas\ProductosPlantillaExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function descargarPlantilla()
    {
        return Excel::download(new ProductosPlantillaExport, 'plantilla_productos_marca.xlsx');
    }
}
