<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusquedaProductoController extends Controller
{
    /**
     * Búsqueda rápida de productos con soporte multi-palabra.
     * Ejemplo: "pegamento cola blanca" encuentra "pegamento blanco cola blanca"
     * porque requiere que TODAS las palabras aparezcan en algún campo.
     */
    public function buscar(Request $request)
    {
        session()->save(); // Libera el lock del archivo de sesión para no bloquear otras peticiones
        $q        = trim($request->get('q', ''));
        $catId    = $request->get('categoria_id', '');
        $marcaId  = $request->get('marca_id', '');
        $conStock = (bool) $request->get('con_stock', 0);
        $bodegaId = $request->get('bodega_id', '');
        $page     = max(1, (int) $request->get('page', 1));
        $perPage  = 12;

        $words = $q !== ''
            ? array_values(array_filter(array_map('trim', explode(' ', $q))))
            : [];

        // ── Query principal SIN GROUP BY ────────────────────────────
        // recibido_bodega e img_producto se consultan aparte SOLO para los ~12
        // resultados de la página, evitando el costoso JOIN+GROUP BY global.
        $query = DB::table('producto as p')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->select([
                'p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal',
                'p.isv', 'm.nombre as marca_nombre',
            ])
            ->where('p.estado_producto_id', 1);

        // Filtro multi-palabra
        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $q->whereRaw('p.nombre LIKE ?', ["%{$word}%"])
                  ->orWhereRaw('p.codigo_barra LIKE ?', ["%{$word}%"])
                  ->orWhereRaw('p.codigo_estatal LIKE ?', ["%{$word}%"]);
                if (is_numeric($word) && ctype_digit($word)) {
                    $q->orWhere('p.id', (int) $word);
                }
            });
        }

        // Filtro por categoría
        if ($catId !== '' && $catId !== null && $catId !== '0') {
            $query->join('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
                  ->where('sc.categoria_producto_id', (int) $catId);
        }

        // Filtro por marca
        if ($marcaId !== '' && $marcaId !== null && $marcaId !== '0') {
            $query->where('p.marca_id', (int) $marcaId);
        }

        // Filtro de stock: WHERE EXISTS (más rápido que JOIN+GROUP BY+HAVING)
        if ($conStock) {
            $query->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('recibido_bodega')
                    ->whereColumn('producto_id', 'p.id')
                    ->whereRaw('cantidad_disponible > 0');
            });
        }

        // Filtro por bodega (opcional — solo cuando se pasa bodega_id explícitamente)
        if ($bodegaId !== '' && $bodegaId !== null) {
            $query->whereExists(function ($sub) use ($bodegaId) {
                $sub->select(DB::raw(1))
                    ->from('recibido_bodega as rb_bq')
                    ->join('seccion as sc_bq', 'sc_bq.id', '=', 'rb_bq.seccion_id')
                    ->join('segmento as sg_bq', 'sg_bq.id', '=', 'sc_bq.segmento_id')
                    ->whereColumn('rb_bq.producto_id', 'p.id')
                    ->whereRaw('rb_bq.cantidad_disponible > 0')
                    ->where('sg_bq.bodega_id', (int) $bodegaId);
            });
        }

        // ── COUNT simple: sin subquery envuelta, sin GROUP BY ──────────────
        $total = (clone $query)->count('p.id');

        // ── ORDER BY ──────────────────────────────────────
        if ($q !== '') {
            if (is_numeric($q) && ctype_digit($q)) {
                $query->orderByRaw('(p.id = ?) DESC', [(int) $q]);
            }
            $query->orderByRaw('(LOWER(p.nombre) = LOWER(?)) DESC', [$q])
                  ->orderByRaw('(LOWER(p.nombre) LIKE ?) DESC', [mb_strtolower($q) . '%']);
        }
        $query->orderBy('p.nombre');

        $items = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        // ── Stock (filtrado por bodega si corresponde) ──────────────────────
        if ($items->isNotEmpty()) {
            $ids = $items->pluck('id')->all();

            $stockQ = DB::table('recibido_bodega')
                ->select('producto_id', DB::raw('SUM(cantidad_disponible) as stock'))
                ->whereIn('producto_id', $ids)
                ->whereRaw('cantidad_disponible > 0');
            if ($bodegaId !== '' && $bodegaId !== null) {
                $stockQ->join('seccion as sc_sk', 'sc_sk.id', '=', 'recibido_bodega.seccion_id')
                       ->join('segmento as sg_sk', 'sg_sk.id', '=', 'sc_sk.segmento_id')
                       ->where('sg_sk.bodega_id', (int) $bodegaId);
            }
            $stockMap = $stockQ->groupBy('producto_id')->get()->keyBy('producto_id');

            $imgMap = DB::table('img_producto')
                ->select('producto_id', 'url_img')
                ->whereIn('producto_id', $ids)
                ->orderBy('producto_id')->orderBy('id')
                ->get()->unique('producto_id')->keyBy('producto_id');

            $items->each(function ($item) use ($stockMap, $imgMap) {
                $item->stock  = isset($stockMap[$item->id]) ? (float) $stockMap[$item->id]->stock : 0;
                $item->imagen = isset($imgMap[$item->id])  ? $imgMap[$item->id]->url_img : null;
            });
        }

        return response()->json([
            'data'         => $items,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    /**
     * Devuelve las categorías de productos para los filtros del buscador.
     */
    public function categorias()
    {
        session()->save();
        $cats = DB::table('categoria_producto')
            ->select('id', DB::raw('descripcion as text'))
            ->orderBy('descripcion')
            ->get();

        return response()->json($cats);
    }

    /**
     * Devuelve las marcas de productos para los filtros del buscador.
     */
    public function marcas()
    {
        session()->save();
        $marcas = DB::table('marca')
            ->select('id', DB::raw('nombre as text'))
            ->orderBy('nombre')
            ->get();

        return response()->json($marcas);
    }

    /**
     * Devuelve los 12 productos más vendidos (por cantidad en venta_has_producto).
     */
    public function topVendidos(Request $request)
    {
        session()->save();
        $bodegaId = $request->get('bodega_id', '');

        // Solo JOIN con venta_has_producto (necesario para SUM+GROUP BY de ventas)
        // recibido_bodega e img_producto se consultan aparte para evitar producto
        // cartesiano entre vhp y rb que infla el SUM incorrecto.
        $tvQuery = DB::table('producto as p')
            ->join('venta_has_producto as vhp', 'vhp.producto_id', '=', 'p.id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->select([
                'p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal', 'p.isv',
                'm.nombre as marca_nombre',
                DB::raw('SUM(vhp.cantidad) as total_vendido'),
            ])
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal', 'p.isv', 'm.nombre')
            ->orderByDesc('total_vendido')
            ->limit(12);

        // Filtro por bodega (solo en traslados)
        if ($bodegaId !== '' && $bodegaId !== null) {
            $tvQuery->whereExists(function ($sub) use ($bodegaId) {
                $sub->select(DB::raw(1))
                    ->from('recibido_bodega as rb_tv')
                    ->join('seccion as sc_tv', 'sc_tv.id', '=', 'rb_tv.seccion_id')
                    ->join('segmento as sg_tv', 'sg_tv.id', '=', 'sc_tv.segmento_id')
                    ->whereColumn('rb_tv.producto_id', 'p.id')
                    ->whereRaw('rb_tv.cantidad_disponible > 0')
                    ->where('sg_tv.bodega_id', (int) $bodegaId);
            });
        }

        $items = $tvQuery->get();

        if ($items->isNotEmpty()) {
            $ids = $items->pluck('id')->all();

            $stockMap = DB::table('recibido_bodega')
                ->select('producto_id', DB::raw('SUM(cantidad_disponible) as stock'))
                ->whereIn('producto_id', $ids)
                ->groupBy('producto_id')
                ->get()->keyBy('producto_id');

            $imgMap = DB::table('img_producto')
                ->select('producto_id', 'url_img')
                ->whereIn('producto_id', $ids)
                ->orderBy('producto_id')->orderBy('id')
                ->get()->unique('producto_id')->keyBy('producto_id');

            $items->each(function ($item) use ($stockMap, $imgMap) {
                $item->stock  = isset($stockMap[$item->id]) ? (float) $stockMap[$item->id]->stock : 0;
                $item->imagen = isset($imgMap[$item->id])  ? $imgMap[$item->id]->url_img : null;
            });
        }

        return response()->json($items);
    }
}
