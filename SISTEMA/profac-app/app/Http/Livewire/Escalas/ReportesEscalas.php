<?php

namespace App\Http\Livewire\Escalas;


use Livewire\Component;

use App\Models\Escalas\modelCategoriaCliente;
use App\Models\Escalas\modelCategoriaPrecios;
use App\Exports\Escalas\ReporteProductosPreciosFiltro;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportesEscalas extends Component
{
    public function render()
    {
        return view('livewire.escalas.reportes-escalas');
    }

    public function descargarPrecios(Request $request)
    {
        $tipoFiltro = $request->input('tipoFiltro');
        $valorFiltro = $request->input('listaTipoFiltro');
        $valorCategoria = $request->input('listaTipoFiltroCatPrecios');

        return Excel::download(
            new ReporteProductosPreciosFiltro($tipoFiltro, $valorFiltro, $valorCategoria),
            'reporte_precios_productos_escalados.xlsx'
        );
    }
}
