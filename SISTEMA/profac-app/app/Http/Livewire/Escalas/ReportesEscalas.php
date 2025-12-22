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

    public function listarProductosFiltrados(Request $request)
    {
        $tipoFiltro = $request->input('tipoFiltro');
        $valorFiltro = $request->input('listaTipoFiltro');
        $categoriaPrecios = $request->input('listaTipoFiltroCatPrecios');

        $query = DB::table('precios_producto_carga as ppc')
            ->join('producto as p', 'p.id', '=', 'ppc.producto_id')
            ->join('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->join('cliente_categoria_escala as cce', 'cp.cliente_categoria_escala_id', '=', 'cce.id')
            ->leftJoin('marca as m', 'm.id', '=', 'ppc.marca_id')
            ->leftJoin('categoria_producto as c', 'c.id', '=', 'ppc.categoria_producto_id')
            ->where('ppc.estado_id', 1) // Solo precios activos
            ->select(
                'p.id',
                'p.nombre as producto',
                'p.codigo_barra as codigo',
                'm.nombre as marca',
                'c.descripcion as categoria',
                'cp.nombre as escala_precio',
                'ppc.precio_A',
                'ppc.precio_B',
                'ppc.precio_C',
                'ppc.precio_D'
            );

        // Aplicar filtros
        if ($tipoFiltro == '1' && $valorFiltro) {
            // Filtrar por marca
            $query->where('ppc.marca_id', $valorFiltro);
        } elseif ($tipoFiltro == '2' && $valorFiltro) {
            // Filtrar por categoría
            $query->where('ppc.categoria_producto_id', $valorFiltro);
        }

        if ($categoriaPrecios) {
            // Filtrar por categoría de precios - CORREGIDO para usar cp.id
            $query->where('cp.id', $categoriaPrecios);
        }

        return DataTables::of($query)
            ->filterColumn('id', function($query, $keyword) {
                $query->whereRaw("CAST(p.id AS CHAR) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('codigo', function($query, $keyword) {
                $query->whereRaw("p.codigo_barra LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('producto', function($query, $keyword) {
                $query->whereRaw("p.nombre LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('marca', function($query, $keyword) {
                $query->whereRaw("m.nombre LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('categoria', function($query, $keyword) {
                $query->whereRaw("c.descripcion LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('escala_precio', function($query, $keyword) {
                $query->whereRaw("cp.nombre LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('precio_A_formatted', function ($row) {
                return 'L. ' . number_format($row->precio_A, 2);
            })
            ->addColumn('precio_B_formatted', function ($row) {
                return 'L. ' . number_format($row->precio_B, 2);
            })
            ->addColumn('precio_C_formatted', function ($row) {
                return 'L. ' . number_format($row->precio_C, 2);
            })
            ->addColumn('precio_D_formatted', function ($row) {
                return 'L. ' . number_format($row->precio_D, 2);
            })
            ->rawColumns(['precio_A_formatted', 'precio_B_formatted', 'precio_C_formatted', 'precio_D_formatted'])
            ->make(true);
    }
}
