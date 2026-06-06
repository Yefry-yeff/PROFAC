<?php

namespace App\Http\Livewire\Escalas;

use Livewire\Component;

use App\Models\Escalas\modelCategoriaCliente;
use App\Models\Escalas\modelCategoriaPrecios;
use App\Exports\Escalas\ReporteProductosPreciosFiltro;
use App\Exports\Escalas\ReporteSinPreciosCatExport;
use App\Exports\Escalas\ReporteCoberturaExport;
use App\Exports\Escalas\ReporteProductosSinPreciosExport;
use App\Exports\Escalas\ReporteComparativoExport;
use App\Exports\Escalas\ReporteResumenCatPrecioExport;
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

    /* ================================================================
     *  TAB 1 — Precios por producto (existente)
     * ================================================================ */
    public function descargarPrecios(Request $request)
    {
        $catClienteId  = $request->input('cat_cliente_id');
        $catPrecioId   = $request->input('cat_precio_id');
        $tipoFiltro    = $request->input('tipoFiltro');
        $valorFiltro   = $request->input('listaTipoFiltro');
        $fecha = now()->format('Y-m-d_His');
        return Excel::download(
            new ReporteProductosPreciosFiltro($catClienteId, $catPrecioId, $tipoFiltro, $valorFiltro),
            "reporte_precios_{$fecha}.xlsx"
        );
    }

    public function listarProductosFiltrados(Request $request)
    {
        $catClienteIds = array_slice(array_map('intval', array_filter(explode(',', $request->input('cat_cliente_ids', '')))), 0, 20);
        $catPrecioIds  = array_slice(array_map('intval', array_filter(explode(',', $request->input('cat_precio_ids',  '')))), 0, 20);
        $tipoFiltro    = $request->input('tipoFiltro');
        $listaIds      = array_slice(array_map('intval', array_filter(explode(',', $request->input('lista_filtro_ids', '')))), 0, 20);

        // Cap de página para evitar consultas masivas
        if (isset($request['length']) && (int)$request['length'] > 100) {
            $request->merge(['length' => 100]);
        }

        $query = DB::table('precios_producto_carga as ppc')
            ->join('producto as p', 'p.id', '=', 'ppc.producto_id')
            ->join('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->leftJoin('marca as m', 'm.id', '=', 'ppc.marca_id')
            ->leftJoin('categoria_producto as c', 'c.id', '=', 'ppc.categoria_producto_id')
            ->where('ppc.estado_id', 1)
            ->select(
                'p.id',
                'cce.nombre_categoria as categoria_cliente',
                'p.nombre as producto',
                'p.codigo_barra as codigo',
                'm.nombre as marca',
                'c.descripcion as categoria',
                'cp.nombre as escala_precio',
                'ppc.precio_base_venta',
                'ppc.precio_a',
                'ppc.precio_b',
                'ppc.precio_c',
                'ppc.precio_d'
            );

        if (!empty($catClienteIds)) {
            $query->whereIn('cce.id', $catClienteIds);
        }
        if (!empty($catPrecioIds)) {
            $query->whereIn('cp.id', $catPrecioIds);
        }
        if ($tipoFiltro == '1' && !empty($listaIds)) {
            $query->whereIn('ppc.marca_id', $listaIds);
        } elseif ($tipoFiltro == '2' && !empty($listaIds)) {
            $query->whereIn('ppc.categoria_producto_id', $listaIds);
        }

        return DataTables::of($query)
            ->addColumn('precio_base_formatted', fn ($r) => 'L. ' . number_format($r->precio_base_venta ?? 0, 2))
            ->addColumn('precio_A_formatted', fn ($r) => 'L. ' . number_format($r->precio_a, 2))
            ->addColumn('precio_B_formatted', fn ($r) => 'L. ' . number_format($r->precio_b, 2))
            ->addColumn('precio_C_formatted', fn ($r) => 'L. ' . number_format($r->precio_c, 2))
            ->addColumn('precio_D_formatted', fn ($r) => 'L. ' . number_format($r->precio_d, 2))
            ->filterColumn('id',               fn ($q, $k) => $q->where('p.id', 'like', "%{$k}%"))
            ->filterColumn('categoria_cliente', fn ($q, $k) => $q->where('cce.nombre_categoria', 'like', "%{$k}%"))
            ->filterColumn('codigo',            fn ($q, $k) => $q->where('p.codigo_barra', 'like', "%{$k}%"))
            ->filterColumn('producto',          fn ($q, $k) => $q->where('p.nombre', 'like', "%{$k}%"))
            ->filterColumn('marca',             fn ($q, $k) => $q->where('m.nombre', 'like', "%{$k}%"))
            ->filterColumn('categoria',         fn ($q, $k) => $q->where('c.descripcion', 'like', "%{$k}%"))
            ->filterColumn('escala_precio',     fn ($q, $k) => $q->where('cp.nombre', 'like', "%{$k}%"))
            ->filterColumn('precio_base_formatted', fn ($q, $k) => $q->where('ppc.precio_base_venta', 'like', "%{$k}%"))
            ->filterColumn('precio_A_formatted', fn ($q, $k) => $q->where('ppc.precio_a', 'like', "%{$k}%"))
            ->filterColumn('precio_B_formatted', fn ($q, $k) => $q->where('ppc.precio_b', 'like', "%{$k}%"))
            ->filterColumn('precio_C_formatted', fn ($q, $k) => $q->where('ppc.precio_c', 'like', "%{$k}%"))
            ->filterColumn('precio_D_formatted', fn ($q, $k) => $q->where('ppc.precio_d', 'like', "%{$k}%"))
            ->rawColumns(['precio_base_formatted', 'precio_A_formatted', 'precio_B_formatted', 'precio_C_formatted', 'precio_D_formatted'])
            ->make(true);
    }

    /* ================================================================
     *  TAB 2 — Cobertura por categoría de cliente
     * ================================================================ */
    public function coberturaJson()
    {
        $data = DB::table('cliente_categoria_escala as cce')
            ->select([
                'cce.id',
                'cce.nombre_categoria',
                DB::raw("IF(cce.estado_id = 1, 'Activo', 'Inactivo') as estado"),
                DB::raw("(SELECT COUNT(*) FROM categoria_precios WHERE cliente_categoria_escala_id = cce.id) as total_cat_precios"),
                DB::raw("(SELECT COUNT(*) FROM categoria_precios WHERE cliente_categoria_escala_id = cce.id AND estado_id = 1) as cat_activas"),
                DB::raw("(SELECT COUNT(DISTINCT ppc.producto_id)
                          FROM precios_producto_carga ppc
                          JOIN categoria_precios cp2 ON cp2.id = ppc.categoria_precios_id
                          WHERE cp2.cliente_categoria_escala_id = cce.id AND ppc.estado_id = 1) as total_productos"),
                'cce.created_at',
            ])
            ->orderByDesc('cce.id')
            ->get();

        return response()->json($data);
    }

    public function descargarCobertura()
    {
        $fecha = now()->format('Y-m-d_His');
        return Excel::download(new ReporteCoberturaExport(), "cobertura_categorias_{$fecha}.xlsx");
    }

    /* ================================================================
     *  TAB 3 — Categorías de cliente sin categorías de precio
     * ================================================================ */
    public function sinPreciosCatJson()
    {
        $data = DB::table('cliente_categoria_escala as cce')
            ->leftJoin('categoria_precios as cp', 'cp.cliente_categoria_escala_id', '=', 'cce.id')
            ->whereNull('cp.id')
            ->select([
                'cce.id',
                'cce.nombre_categoria',
                DB::raw("COALESCE(cce.descripcion_categoria, '') as descripcion_categoria"),
                DB::raw("IF(cce.estado_id = 1, 'Activo', 'Inactivo') as estado"),
                'cce.created_at',
            ])
            ->orderByDesc('cce.id')
            ->get();

        return response()->json($data);
    }

    public function descargarSinPreciosCat()
    {
        $fecha = now()->format('Y-m-d_His');
        return Excel::download(new ReporteSinPreciosCatExport(), "categorias_sin_precios_{$fecha}.xlsx");
    }

    /* ================================================================
     *  TAB 4 — Productos sin precios configurados
     * ================================================================ */
    public function productosSinPreciosJson()
    {
        $data = DB::table('producto as p')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('precios_producto_carga as ppc')
                    ->whereRaw('ppc.producto_id = p.id')
                    ->where('ppc.estado_id', 1);
            })
            ->select(['p.id', 'p.codigo_barra', 'p.nombre'])
            ->orderByDesc('p.id')
            ->get();

        return response()->json($data);
    }

    public function descargarProductosSinPrecios()
    {
        $fecha = now()->format('Y-m-d_His');
        return Excel::download(new ReporteProductosSinPreciosExport(), "productos_sin_precios_{$fecha}.xlsx");
    }

    /* ================================================================
     *  TAB 5 — Comparativo de precios por producto
     * ================================================================ */
    public function comparativoJson(Request $request)
    {
        $produtoId = (int) $request->input('produto_id');
        if (!$produtoId) {
            return response()->json([]);
        }

        $data = DB::table('precios_producto_carga as ppc')
            ->join('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->where('ppc.producto_id', $produtoId)
            ->select([
                'cce.nombre_categoria as categoria_cliente',
                'cp.nombre as categoria_precio',
                'cp.porc_precio_a',
                'cp.porc_precio_b',
                'cp.porc_precio_c',
                'cp.porc_precio_d',
                'ppc.precio_base_venta',
                'ppc.precio_a',
                'ppc.precio_b',
                'ppc.precio_c',
                'ppc.precio_d',
                DB::raw("IF(ppc.estado_id = 1, 'Activo', 'Inactivo') as estado"),
            ])
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre')
            ->get();

        return response()->json($data);
    }

    public function descargarComparativo(Request $request)
    {
        $produtoId = (int) $request->input('produto_id');
        if (!$produtoId) {
            abort(422, 'Seleccione un producto.');
        }
        $produto = DB::table('producto')->where('id', $produtoId)->value('nombre') ?? "producto_{$produtoId}";
        $slug    = preg_replace('/[^a-zA-Z0-9_]/', '_', substr($produto, 0, 30));
        $fecha   = now()->format('Y-m-d_His');
        return Excel::download(
            new ReporteComparativoExport($produtoId, $produto),
            "comparativo_{$slug}_{$fecha}.xlsx"
        );
    }

    /* ================================================================
     *  TAB 6 — Resumen de categorías de precio
     * ================================================================ */
    public function resumenCatPrecioJson(Request $request)
    {
        $catClienteId = $request->input('cat_cliente_id') ? (int) $request->input('cat_cliente_id') : null;
        $estadoId     = $request->input('estado_id') !== null && $request->input('estado_id') !== ''
                            ? (int) $request->input('estado_id')
                            : null;

        $data = DB::table('categoria_precios as cp')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->leftJoin(
                DB::raw('(SELECT categoria_precios_id, COUNT(DISTINCT producto_id) AS cnt
                          FROM precios_producto_carga WHERE estado_id = 1
                          GROUP BY categoria_precios_id) AS pc'),
                'pc.categoria_precios_id', '=', 'cp.id'
            )
            ->select([
                'cp.id',
                'cp.nombre as categoria_precio',
                'cce.nombre_categoria as categoria_cliente',
                'cp.porc_precio_a',
                'cp.porc_precio_b',
                'cp.porc_precio_c',
                'cp.porc_precio_d',
                DB::raw("IF(cp.estado_id = 1, 'Activo', 'Inactivo') as estado"),
                DB::raw('COALESCE(pc.cnt, 0) as total_productos'),
                'cp.fecha_ultima_actualizacion',
            ])
            ->when($catClienteId, fn ($q) => $q->where('cp.cliente_categoria_escala_id', $catClienteId))
            ->when($estadoId !== null, fn ($q) => $q->where('cp.estado_id', $estadoId))
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre')
            ->get();

        return response()->json($data);
    }

    public function descargarResumenCatPrecio(Request $request)
    {
        $catClienteId = $request->input('cat_cliente_id') ? (int) $request->input('cat_cliente_id') : null;
        $estadoId     = $request->input('estado_id') !== null && $request->input('estado_id') !== ''
                            ? (int) $request->input('estado_id')
                            : null;
        $fecha = now()->format('Y-m-d_His');
        return Excel::download(
            new ReporteResumenCatPrecioExport($catClienteId, $estadoId),
            "resumen_categorias_precio_{$fecha}.xlsx"
        );
    }

    /* ================================================================
     *  TAB 7 — Categorías de precio con comisiones asignadas
     * ================================================================ */
    public function comisionesJson(Request $request)
    {
        $catClienteId = $request->input('cat_cliente_id') ? (int) $request->input('cat_cliente_id') : null;
        $rolId        = $request->input('rol_id')         ? (int) $request->input('rol_id')         : null;
        $estadoId     = ($request->input('estado_id') !== null && $request->input('estado_id') !== '')
                            ? (int) $request->input('estado_id') : null;

        $data = DB::table('comision_escala as ce')
            ->join('rol as r', 'r.id', '=', 'ce.rol_id')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'ce.cliente_categoria_escala_id')
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ce.categoria_precios_id')
            ->select([
                'ce.id',
                'cce.nombre_categoria as categoria_cliente',
                'cp.nombre as categoria_precio',
                'r.nombre as rol',
                'ce.porcentaje_comision',
                DB::raw("IF(ce.estado_id = 1, 'Activo', 'Inactivo') as estado"),
                'cp.porc_precio_a',
                'cp.porc_precio_b',
                'cp.porc_precio_c',
                'cp.porc_precio_d',
            ])
            ->when($catClienteId, fn ($q) => $q->where('ce.cliente_categoria_escala_id', $catClienteId))
            ->when($rolId,        fn ($q) => $q->where('ce.rol_id', $rolId))
            ->when($estadoId !== null, fn ($q) => $q->where('ce.estado_id', $estadoId))
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre')
            ->orderBy('r.nombre')
            ->get();

        return response()->json($data);
    }
}
