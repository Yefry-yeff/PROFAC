<?php

namespace App\Http\Livewire\Inventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Auth;
use DataTables;
use Validator;
use Illuminate\Support\Facades\File;
use PDF;

use App\Models\modelBodega;
use App\Models\ModelRecibirBodega;
use App\Models\ModelLogTranslados;
use App\Models\ModelTranslado;
use Livewire\Component;

class Translados extends Component
{
    public function render()
    {
        return view('livewire.inventario.translados');
    }

    public function listarBodegas(){
       try {

        $listaBodegas = DB::SELECT("
            select id ,nombre as 'text' from bodega
        ");



       return response()->json([
           "results" => $listaBodegas

       ],200);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }

    /* =========================================================
     *  Métodos optimizados para el buscador en vista traslados
     * ========================================================= */

    /**
     * Búsqueda de productos filtrada por bodega.
     * Usa joinSub (tabla derivada) en lugar de WHERE EXISTS correlacionado
     * para que el motor de BD pueda materializar el subconjunto una sola vez.
     */
    public function buscarProductos(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $catId    = $request->get('categoria_id', '');
        $marcaId  = $request->get('marca_id', '');
        $bodegaId = (int) $request->get('bodega_id', 0);
        $page     = max(1, (int) $request->get('page', 1));
        $perPage  = 12;

        if (!$bodegaId) {
            return response()->json(['data'=>[],'total'=>0,'current_page'=>1,'per_page'=>$perPage,'last_page'=>1]);
        }

        // Subquery: IDs y stock de productos disponibles en esta bodega
        $stockSub = DB::table('recibido_bodega as rb')
            ->join('seccion as sc', 'sc.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 'sc.segmento_id')
            ->select('rb.producto_id', DB::raw('SUM(rb.cantidad_disponible) as stock'))
            ->where('sg.bodega_id', $bodegaId)
            ->whereRaw('rb.cantidad_disponible > 0')
            ->groupBy('rb.producto_id');

        $query = DB::table('producto as p')
            ->joinSub($stockSub, 'bq', 'bq.producto_id', '=', 'p.id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->select(['p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal',
                      'p.isv', 'm.nombre as marca_nombre', 'bq.stock']);

        $words = $q !== '' ? array_values(array_filter(array_map('trim', explode(' ', $q)))) : [];
        foreach ($words as $word) {
            $query->where(function ($wq) use ($word) {
                $wq->whereRaw('p.nombre LIKE ?', ["%{$word}%"])
                   ->orWhereRaw('p.codigo_barra LIKE ?', ["%{$word}%"])
                   ->orWhereRaw('p.codigo_estatal LIKE ?', ["%{$word}%"]);
                if (is_numeric($word) && ctype_digit($word)) {
                    $wq->orWhere('p.id', (int) $word);
                }
            });
        }

        if ($catId !== '' && $catId !== null && $catId !== '0') {
            $query->join('sub_categoria as sc2', 'sc2.id', '=', 'p.sub_categoria_id')
                  ->where('sc2.categoria_producto_id', (int) $catId);
        }
        if ($marcaId !== '' && $marcaId !== null && $marcaId !== '0') {
            $query->where('p.marca_id', (int) $marcaId);
        }

        $total = (clone $query)->count('p.id');

        if ($q !== '') {
            if (is_numeric($q) && ctype_digit($q)) {
                $query->orderByRaw('(p.id = ?) DESC', [(int) $q]);
            }
            $query->orderByRaw('(LOWER(p.nombre) = LOWER(?)) DESC', [$q])
                  ->orderByRaw('(LOWER(p.nombre) LIKE ?) DESC', [mb_strtolower($q) . '%']);
        }
        $query->orderBy('p.nombre');

        $items = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        if ($items->isNotEmpty()) {
            $ids    = $items->pluck('id')->all();
            $imgMap = DB::table('img_producto')
                ->select('producto_id', 'url_img')
                ->whereIn('producto_id', $ids)
                ->orderBy('producto_id')->orderBy('id')
                ->get()->unique('producto_id')->keyBy('producto_id');

            $items->each(function ($item) use ($imgMap) {
                $item->imagen = isset($imgMap[$item->id]) ? $imgMap[$item->id]->url_img : null;
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
     * Top 12 productos más trasladados desde esta bodega.
     */
    public function topTrasladados(Request $request)
    {
        $bodegaId = (int) $request->get('bodega_id', 0);

        if (!$bodegaId) return response()->json([]);

        $items = DB::table('log_translado as lt')
            ->join('recibido_bodega as rb', 'rb.id', '=', 'lt.origen')
            ->join('seccion as sc', 'sc.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 'sc.segmento_id')
            ->join('producto as p', 'p.id', '=', 'rb.producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->select([
                'p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal', 'p.isv',
                'm.nombre as marca_nombre',
                DB::raw('SUM(lt.cantidad) as total_vendido'),
            ])
            ->where('sg.bodega_id', $bodegaId)
            ->where('lt.descripcion', 'Translado de bodega')
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra', 'p.codigo_estatal', 'p.isv', 'm.nombre')
            ->orderByDesc('total_vendido')
            ->limit(12)
            ->get();

        if ($items->isNotEmpty()) {
            $ids = $items->pluck('id')->all();

            $stockSub2 = DB::table('recibido_bodega as rb2')
                ->join('seccion as sc2', 'sc2.id', '=', 'rb2.seccion_id')
                ->join('segmento as sg2', 'sg2.id', '=', 'sc2.segmento_id')
                ->select('rb2.producto_id', DB::raw('SUM(rb2.cantidad_disponible) as stock'))
                ->whereIn('rb2.producto_id', $ids)
                ->where('sg2.bodega_id', $bodegaId)
                ->whereRaw('rb2.cantidad_disponible > 0')
                ->groupBy('rb2.producto_id')
                ->get()->keyBy('producto_id');

            $imgMap = DB::table('img_producto')
                ->select('producto_id', 'url_img')
                ->whereIn('producto_id', $ids)
                ->orderBy('producto_id')->orderBy('id')
                ->get()->unique('producto_id')->keyBy('producto_id');

            $items->each(function ($item) use ($stockSub2, $imgMap) {
                $item->stock  = isset($stockSub2[$item->id]) ? (float) $stockSub2[$item->id]->stock : 0;
                $item->imagen = isset($imgMap[$item->id])   ? $imgMap[$item->id]->url_img : null;
            });
        }

        return response()->json($items);
    }

    /**
     * Categorías que tienen productos DISPONIBLES en la bodega indicada.
     */
    public function categoriasBodega(Request $request)
    {
        $bodegaId = (int) $request->get('bodega_id', 0);

        $cats = DB::table('categoria_producto as cp')
            ->select('cp.id', DB::raw('cp.descripcion as text'))
            ->whereExists(function ($sub) use ($bodegaId) {
                $sub->select(DB::raw(1))
                    ->from('recibido_bodega as rb')
                    ->join('producto as pr', 'pr.id', '=', 'rb.producto_id')
                    ->join('sub_categoria as sca', 'sca.id', '=', 'pr.sub_categoria_id')
                    ->join('seccion as sc', 'sc.id', '=', 'rb.seccion_id')
                    ->join('segmento as sg', 'sg.id', '=', 'sc.segmento_id')
                    ->whereColumn('sca.categoria_producto_id', 'cp.id')
                    ->whereRaw('rb.cantidad_disponible > 0')
                    ->where('sg.bodega_id', $bodegaId);
            })
            ->orderBy('cp.descripcion')
            ->get();

        return response()->json($cats);
    }

    /**
     * Marcas que tienen productos DISPONIBLES en la bodega indicada.
     */
    public function marcasBodega(Request $request)
    {
        $bodegaId = (int) $request->get('bodega_id', 0);

        $marcas = DB::table('marca as m')
            ->select('m.id', DB::raw('m.nombre as text'))
            ->whereExists(function ($sub) use ($bodegaId) {
                $sub->select(DB::raw(1))
                    ->from('recibido_bodega as rb')
                    ->join('producto as pr', 'pr.id', '=', 'rb.producto_id')
                    ->join('seccion as sc', 'sc.id', '=', 'rb.seccion_id')
                    ->join('segmento as sg', 'sg.id', '=', 'sc.segmento_id')
                    ->whereColumn('pr.marca_id', 'm.id')
                    ->whereRaw('rb.cantidad_disponible > 0')
                    ->where('sg.bodega_id', $bodegaId);
            })
            ->orderBy('m.nombre')
            ->get();

        return response()->json($marcas);
    }

    public function listarSecciones(Request $request){
       try {

        $listaSecciones = DB::SELECT("select
        seccion.id,
        seccion.descripcion
        from bodega
        inner join segmento
        on bodega.id = segmento.bodega_id
        inner join seccion
        on seccion.segmento_id = segmento.id
        where bodega.id = ".$request->idBodega);

       return response()->json([
           "listaSecciones" =>  $listaSecciones
       ]);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ]);
       }
    }



    public function listarProductos(Request $request){
       try {

        $search = '%' . $request->search . '%';
        $listaProductos = DB::select("
        select
            B.id,
            B.nombre,
            SUM(A.cantidad_disponible) as cantidad_total,
            CONCAT(B.id, ' - ', B.nombre, '  (Disp: ', SUM(A.cantidad_disponible), ')') as text
        from recibido_bodega A
            inner join producto B
            on A.producto_id = B.id
            inner join seccion
            on A.seccion_id = seccion.id
            inner join segmento
            on seccion.segmento_id = segmento.id
            inner join bodega
            on segmento.bodega_id = bodega.id
        where bodega.id = ? and A.cantidad_disponible > 0
            and (B.nombre like ? or CAST(B.id AS CHAR) like ?)
        group by B.id, B.nombre
        limit 15
        ", [$request->idBodega, $search, $search]);

       return response()->json([
           "results" => $listaProductos
       ]);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ]);
       }
    }

    public function productoBodega($idBodega,$idProducto){
       try {
           //dd($idBodega,$idProducto);

        $listaProductos = DB::SELECT("
        select
        A.id as 'idRecibido',
        B.id as 'idProducto',
        B.nombre,
        UPPER(D.nombre) as 'simbolo',
        A.cantidad_disponible,
        bodega.nombre as bodega,
        seccion.id as 'idSeccion',
        seccion.descripcion,
        A.created_at
    from recibido_bodega A
        inner join producto B
        on A.producto_id = B.id
        inner join seccion
        on A.seccion_id = seccion.id
        inner join segmento
        on seccion.segmento_id = segmento.id
        inner join bodega
        on segmento.bodega_id = bodega.id
        inner join unidad_medida_venta C
        on B.id = C.producto_id
        inner join unidad_medida D
        on C.unidad_medida_id = D.id
        inner join compra
        on A.compra_id = compra.id
        where C.unidad_venta_defecto = 1 and A.cantidad_disponible <> 0 and compra.estado_compra_id = 1 and bodega.id = ".$idBodega." and A.producto_id = ".$idProducto."

        union

        select
        A.id as 'idRecibido',
        B.id as 'idProducto',
        B.nombre,
        UPPER(D.nombre) as 'simbolo',
        A.cantidad_disponible,
        bodega.nombre as bodega,
        seccion.id as 'idSeccion',
        seccion.descripcion,
        A.created_at
    from recibido_bodega A
        inner join producto B
        on A.producto_id = B.id
        inner join seccion
        on A.seccion_id = seccion.id
        inner join segmento
        on seccion.segmento_id = segmento.id
        inner join bodega
        on segmento.bodega_id = bodega.id
        inner join unidad_medida_venta C
        on B.id = C.producto_id
        inner join unidad_medida D
        on C.unidad_medida_id = D.id

        where C.unidad_venta_defecto = 1 and A.cantidad_disponible <> 0 and A.compra_id is null and bodega.id = ".$idBodega." and A.producto_id = ".$idProducto


        );

        return Datatables::of($listaProductos)
        ->addColumn('opciones', function ($producto) {

            return

            '<div class="text-center">
                <button class="btn btn-warning" onclick="modalTranslado('.$producto->idRecibido.','.$producto->cantidad_disponible.','.$producto->idProducto.')">
                    Transladar
                </button>

            </div>';
        })

        ->rawColumns(['opciones',])
        ->make(true);

       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ]);
       }
    }

    public function ejectarTranslado(Request $request){

       try {

        $contadorTranslados = 0;


        $arrayTemporal = $request->arregloIdInputs;
        $arregloIdInputs  = explode(',', $arrayTemporal);

        $flagError = false;


        $text2 = "<p>Los siguientes productos exceden la cantidad disponible para translado: <p><ul>";

        DB::beginTransaction();

        $trasladoID = new ModelTranslado();
        $trasladoID->comentario = $request->motivo ?? '';
        $trasladoID->save();
        $IDtraslado = $trasladoID->id;

        for ($i = 0; $i < count($arregloIdInputs); $i++) {

        $keyIdRecibido = "idRecibido".$arregloIdInputs[$i];
        $keyUnidadMedidaId = "unidadMedidaId".$arregloIdInputs[$i];
        $keyCantidad = "cantidad".$arregloIdInputs[$i];
        $keyIdSeccion = "idSeccion".$arregloIdInputs[$i];
        $keyComentarioItem = "comentarioItem".$arregloIdInputs[$i];

        $idRecibido = $request->$keyIdRecibido;
        $unidadMedidaId = $request->$keyUnidadMedidaId;
        $cantidad = $request->$keyCantidad;
        $idSeccion = $request->$keyIdSeccion;
        $comentarioItem = $request->$keyComentarioItem ?? '';


        $productoEnBodega = ModelRecibirBodega::find($idRecibido);
        $unidadesVenta = DB::SELECTONE("select id, unidad_venta from unidad_medida_venta where id =".$unidadMedidaId);
        $unidadesTraslado = $cantidad * $unidadesVenta->unidad_venta;

        $nombreProducto = DB::SELECTONE("select nombre from producto where id = ".$productoEnBodega->producto_id);

            if($productoEnBodega->cantidad_disponible >= $unidadesTraslado ){

                $transladarBodega = new ModelRecibirBodega();
                $transladarBodega->compra_id = $productoEnBodega->compra_id;
                $transladarBodega->producto_id = $productoEnBodega->producto_id;
                $transladarBodega->seccion_id = $idSeccion;
                $transladarBodega->cantidad_compra_lote = $productoEnBodega->cantidad_compra_lote;
                $transladarBodega->cantidad_inicial_seccion = $unidadesTraslado;
                $transladarBodega->cantidad_disponible = $unidadesTraslado;
                $transladarBodega->fecha_recibido = now();
                $transladarBodega->fecha_expiracion = $productoEnBodega->fecha_expiracion;
                $transladarBodega->estado_recibido = 4;
                $transladarBodega->recibido_por = Auth::user()->id;
                $transladarBodega->unidad_compra_id = $productoEnBodega->unidad_compra_id;
                $transladarBodega->unidades_compra = $productoEnBodega->unidades_compra;
                $transladarBodega->save();


                $logTranslados = new ModelLogTranslados;
                $logTranslados->origen = $productoEnBodega->id ;
                $logTranslados->destino = $transladarBodega->id;
                $logTranslados->cantidad = $unidadesTraslado;
                $logTranslados->unidad_medida_venta_id =  $unidadMedidaId;
                $logTranslados->users_id= Auth::user()->id;
                $logTranslados->descripcion="Translado de bodega";
                $logTranslados->comentario = $comentarioItem;
                $logTranslados->translado_id= $IDtraslado;
                $logTranslados->created_at = now();
                $logTranslados->updated_at = now();
                $logTranslados->save();


                $productoEnBodega->cantidad_disponible = $productoEnBodega->cantidad_disponible - $unidadesTraslado;
                $productoEnBodega->save();
                $contadorTranslados++;

                }else{
                    $flagError = true;
                    $text2 =  $text2."<li>".$productoEnBodega->producto_id."-".$nombreProducto."</li>";
                }



        }

        $updateCodeTrasnlado = ModelTranslado::find($IDtraslado);
        $updateCodeTrasnlado->codigo = date('Y')."-".$IDtraslado;
        $updateCodeTrasnlado->save();

        DB::commit();

        if($flagError){
            $text2 =  $text2."</ul>";
            return response()->json([
                "text" =>  $text2,
                "icon" => "warning",
                "title"=>"Advertencia!",
                "contadorTranslados" => $contadorTranslados
            ], 200);


        }else{
             return response()->json([
                 "text" => "El producto ha sido transladado de bodega con éxito.",
                 "icon" => "success",
                 "title"=>"Exito!",
                 "contadorTranslados" => $contadorTranslados
             ], 200);
        }




       } catch (QueryException $e) {
        DB::rollback();
        return response()->json([
            "text" => "Ha ocurrido un error, al intentar transladar el producto",
            "icon" => "error",
            "title"=>"Error!",
            'error' => $e
        ], 402);
       }
    }

    public function productoGeneralBodega($numeroFilas){
        try {

            // $idBodega = DB::SELECTONE("
            //     select
            //         bodega.id
            //     from seccion
            //         inner join segmento
            //         on seccion.segmento_id = segmento.id
            //         inner join bodega
            //         on bodega.id = segmento.bodega_id
            //         where seccion.id =".$idSeccion);



         $listaProductos = DB::SELECT("
         select
         A.id as 'idRecibido',
         B.id as 'idProducto',
         B.nombre,
         C.nombre as 'simbolo',
         A.cantidad_disponible,
         bodega.nombre as bodega,
         seccion.id as 'idSeccion',
         seccion.descripcion,
         A.created_at
       from log_translado Z
         inner join recibido_bodega A
         on Z.destino = A.id
         inner join producto B
         on A.producto_id = B.id
         inner join seccion
         on A.seccion_id = seccion.id
         inner join segmento
         on seccion.segmento_id = segmento.id
         inner join bodega
         on segmento.bodega_id = bodega.id
         inner join unidad_medida C
         on B.unidad_medida_compra_id = C.id
         inner join compra
         on A.compra_id = compra.id
         where  A.cantidad_disponible <> 0 and compra.estado_compra_id = 1 and Z.descripcion ='Translado de bodega'
         order by Z.id desc
         limit ".$numeroFilas
         );

         return Datatables::of($listaProductos)

         ->make(true);

        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
        ]);
        }
     }

    public function imprimirTranslado($idTranslado){

        $translados = DB::SELECT("
        select
        C.id,

               C.nombre,
               C.descripcion,
               H.nombre as medida,
               CONCAT(F.nombre,' - ',D.descripcion)as origen,

               (        select
               CONCAT(E.nombre,' - ',C.descripcion)
               from translado F
               inner join log_translado A
               on F.id = A.translado_id
               inner join recibido_bodega B
               on A.destino = B.id
               inner join seccion C
               on B.seccion_id = C.id
               inner join segmento D
               on D.id = C.segmento_id
               inner join bodega E
               on E.id = D.bodega_id
               where A.descripcion ='Translado de bodega' and B.id = AA.destino and F.id = ".$idTranslado.") as destino,
               AA.cantidad
               from translado I
               inner join log_translado AA
               on I.id = AA.translado_id
               inner join recibido_bodega B
               on AA.origen = B.id
               inner join producto C
               on B.producto_id = C.id
               inner join seccion D
               on D.id = B.seccion_id
               inner join segmento E
               on E.id = D.segmento_id
               inner join bodega F
               on F.id = E.bodega_id
               inner join unidad_medida_venta G
               on C.id = G.producto_id
               inner join unidad_medida H
               on G.unidad_medida_id = H.id
               where AA.descripcion ='Translado de bodega' and G.unidad_venta_defecto = 1  and I.id = ".$idTranslado);


               //dd($idTranslado);
        $datos = DB::SELECTONE("
        select
        CONCAT(A.origen,A.destino,'-' ,A.id) as codigo,
        DATE_FORMAT(tr.created_at,'%d/%m/%Y') as fecha,
        B.name,
        A.descripcion

        from log_translado A
        inner join translado tr on A.translado_id = tr.id
        inner join users B on A.users_id = B.id
        where tr.id = ".$idTranslado);

        $pdf = PDF::loadView('/pdf/translado',compact('translados','datos'))->setPaper('letter');

        return $pdf->stream("traslado.pdf");

     }
}


