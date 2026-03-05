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
        $q        = trim($request->get('q', ''));
        $catId    = $request->get('categoria_id', '');
        $marcaId  = $request->get('marca_id', '');
        $conStock = (bool) $request->get('con_stock', 0);
        $page     = max(1, (int) $request->get('page', 1));
        $perPage  = 12;

        $words = $q !== ''
            ? array_values(array_filter(array_map('trim', explode(' ', $q))))
            : [];

        // ── Base query (SIN ORDER BY, para que el COUNT sea correcto) ──
        $query = DB::table('producto as p')
            ->leftJoin('recibido_bodega as rb', 'rb.producto_id', '=', 'p.id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->select([
                'p.id',
                'p.nombre',
                'p.codigo_barra',
                'p.codigo_estatal',
                'p.isv',
                'm.nombre as marca_nombre',
                DB::raw('COALESCE(SUM(rb.cantidad_disponible), 0) as stock'),
                DB::raw('(SELECT url_img FROM img_producto WHERE producto_id = p.id ORDER BY id ASC LIMIT 1) as imagen'),
            ])
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal', 'p.isv', 'm.nombre');

        // Filtro multi-palabra
        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $q->whereRaw('p.nombre LIKE ?', ["%{$word}%"])
                  ->orWhereRaw('p.codigo_barra LIKE ?', ["%{$word}%"])
                  ->orWhereRaw('p.codigo_estatal LIKE ?', ["%{$word}%"])
                  ->orWhereRaw('p.id = ?', [$word]);
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

        // Filtro de stock
        if ($conStock) {
            $query->havingRaw('stock > 0');
        }

        // ── COUNT antes de agregar ORDER BY (evita el mismatch de bindings en MySQL 8) ──
        $countBindings = $query->getBindings();
        $countSql      = "SELECT COUNT(*) as aggregate FROM ({$query->toSql()}) as cnt_tbl";
        $countResult   = DB::selectOne($countSql, $countBindings);
        $total         = $countResult ? (int) $countResult->aggregate : 0;

        // ── ORDER BY solo para la consulta de datos paginados ──
        if ($q !== '') {
            if (is_numeric($q) && ctype_digit($q)) {
                $query->orderByRaw('(p.id = ?) DESC', [(int) $q]);
            }
            $query->orderByRaw('(LOWER(p.nombre) = LOWER(?)) DESC', [$q])
                  ->orderByRaw('(LOWER(p.nombre) LIKE ?) DESC', [mb_strtolower($q) . '%']);
        }
        $query->orderBy('p.nombre');

        $items = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

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
        $marcas = DB::table('marca')
            ->select('id', DB::raw('nombre as text'))
            ->orderBy('nombre')
            ->get();

        return response()->json($marcas);
    }

    /**
     * Devuelve los 12 productos más vendidos (por cantidad en venta_has_producto).
     */
    public function topVendidos()
    {
        $items = DB::table('producto as p')
            ->join('venta_has_producto as vhp', 'vhp.producto_id', '=', 'p.id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->select([
                'p.id',
                'p.nombre',
                'p.codigo_barra',
                'p.codigo_estatal',
                'p.isv',
                'm.nombre as marca_nombre',
                DB::raw('(SELECT COALESCE(SUM(cantidad_disponible),0) FROM recibido_bodega WHERE producto_id = p.id) as stock'),
                DB::raw('(SELECT url_img FROM img_producto WHERE producto_id = p.id ORDER BY id ASC LIMIT 1) as imagen'),
                DB::raw('SUM(vhp.cantidad) as total_vendido'),
            ])
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal', 'p.isv', 'm.nombre')
            ->orderByDesc('total_vendido')
            ->limit(12)
            ->get();

        return response()->json($items);
    }
}
