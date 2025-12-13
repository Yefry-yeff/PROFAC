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

        $listaProductos = DB::select("
        select
            B.id ,
            B.nombre,
            concat(B.id,' - ',B.nombre) as text

        from recibido_bodega A
            inner join producto B
            on A.producto_id = B.id
            inner join seccion
            on A.seccion_id = seccion.id
            inner join segmento
            on seccion.segmento_id = segmento.id
            inner join bodega
            on segmento.bodega_id = bodega.id
            where bodega.id=".$request->idBodega." and (B.nombre like '%".$request->search."%' or B.id like '%".$request->search."%') limit 15
        ");

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
        $trasladoID->save();
        $IDtraslado = $trasladoID->id;

        for ($i = 0; $i < count($arregloIdInputs); $i++) {

        $keyIdRecibido = "idRecibido".$arregloIdInputs[$i];
        $keyUnidadMedidaId = "unidadMedidaId".$arregloIdInputs[$i];
        $keyCantidad = "cantidad".$arregloIdInputs[$i];
        $keyIdSeccion = "idSeccion".$arregloIdInputs[$i];

        $idRecibido = $request->$keyIdRecibido;
        $unidadMedidaId = $request->$keyUnidadMedidaId;
        $cantidad = $request->$keyCantidad;
        $idSeccion = $request->$keyIdSeccion;


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
                $logTranslados->translado_id= $IDtraslado;
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


