<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Auth;
use DataTables;
use Validator;
use Illuminate\Support\Facades\File;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;


use App\Models\ModelAjuste;
use App\Models\ModelTipoAjuste;
use App\Models\ModelLogTranslados;
use App\Models\ModelRecibirBodega;
use App\Models\ModelAjusteProducto;

class Ajustes extends Component

{
    public function render()
    {
        return view('livewire.inventario.ajustes');
    }

    public function listarBodegas(Request $request){
        try {

         $listaBodegas = DB::SELECT("
             select id ,nombre as 'text' from bodega  where nombre like '%".$request->search."%' or id like '%".$request->search."%' limit 15
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


     public function seccionesLista(Request $request){
        try {

            $listaSecciones = DB::SELECT("
                select
                A.id,
                UPPER(A.descripcion) as text
                from seccion A
                inner join segmento B
                on A.segmento_id = B.id
                inner join recibido_bodega C
                on A.id = C.seccion_id
                where C.cantidad_disponible <> 0 and B.bodega_id = ".$request->bodegaId." and A.descripcion like  '%".$request->search."%'
                group by A.id, text
                "
            );

            return response()->json([
                "results" => $listaSecciones
            ],200);

        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
        ],402);
        }
     }



     public function listarProductos(Request $request){
        try {



            //dd($request->all());

         $listaProductos = DB::select("
         select
         B.id,
         UPPER(CONCAT(B.id,' - ',B.nombre,' _ Lote ',A.id )) as text
         from recibido_bodega A
         inner join producto B
         on A.producto_id = B.id
         inner join seccion
         on A.seccion_id = seccion.id
         inner join segmento
         on seccion.segmento_id = segmento.id
         inner join bodega
         on segmento.bodega_id = bodega.id
         where A.cantidad_disponible <> 0 and bodega.id=".$request->idBodega." and seccion.id = ".$request->idSeccion." and (B.nombre like '%".$request->search."%' or B.id like '%".$request->search."%') limit 15
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

     public function obtenerTiposAjuste(){


        $tiposAjuste = DB::select("select id, nombre as text from tipo_ajuste order by nombre asc");
        $usuarios = DB::SELECT("select id, name from users order by name asc");

        return response()->json([
            "results" => $tiposAjuste,
            "usuarios"=>$usuarios
        ]);
     }

     public function datosProducto(Request $request){
       try {

        $producto = DB::SELECTONE("select id, nombre, precio_base from producto where id=".$request->id);

        $datosBodega = DB::SELECTONE("
        select
        UPPER(D.nombre) as bodega,
        UPPER(B.descripcion) as seccion

        from recibido_bodega A
        inner join seccion B
        on A.seccion_id = B.id
        inner join segmento C
        on C.id = B.segmento_id
        inner join bodega D
        on D.id = C.bodega_id
        where A.id = ".$request->idRecibido);

        $unidadesMedida = DB::SELECT("
        select
        B.id,
        B.unidad_venta,
        B.unidad_venta_defecto,
        CONCAT(C.nombre,'-',B.unidad_venta) as nombre
        from producto A
        inner join unidad_medida_venta B
        on A.id = B.producto_id
        inner join unidad_medida C
        on B.unidad_medida_id = C.id
        where A.id = ".$request->id);

       return response()->json([
        'unidadesMedida'=>$unidadesMedida,
        'producto'=>$producto,
        'datosBodega' => $datosBodega
       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'icon' => 'error',
        'text' => 'Ha ocurrido un error al obtener los datos de producto',
        'title' => 'Error!',
        'message' => 'Ha ocurrido un error',
        'error' => $e,
       ],402);
       }
     }

     public function listarProducto(Request $request){
       try {
        $request->validate(['idProducto' => 'required|integer|min:1']);

        $listaProductos = DB::select("
            select
                min(A.id) as idRecibido,
                group_concat(A.id order by A.created_at, A.id separator ',') as idsRecibido,
                B.id as idProducto,
                B.nombre,
                C.nombre as simbolo,
                sum(A.cantidad_disponible) as cantidad_disponible,
                bodega.id as idBodega,
                bodega.nombre as bodega,
                seccion.id as idSeccion,
                seccion.descripcion,
                min(A.created_at) as created_at
            from recibido_bodega A
                inner join producto B on A.producto_id = B.id
                inner join seccion on A.seccion_id = seccion.id
                inner join segmento on seccion.segmento_id = segmento.id
                inner join bodega on segmento.bodega_id = bodega.id
                inner join unidad_medida C on B.unidad_medida_compra_id = C.id
            where A.cantidad_disponible <> 0
                and A.producto_id = ?
            group by B.id, B.nombre, C.nombre, bodega.id, bodega.nombre, seccion.id, seccion.descripcion
            order by bodega.nombre, seccion.descripcion
        ", [(int) $request->idProducto]);

        return Datatables::of($listaProductos)
        ->addColumn('opciones', function ($producto) {

            return

            '<div class="text-center">
                <button class="btn btn-sm btn-primary" onclick="datosProducto('.$producto->idProducto.',\''.$producto->idsRecibido.'\','.$producto->cantidad_disponible.')">
                    <i class="fa fa-check mr-1"></i> Seleccionar
                </button>

            </div>';
        })

        ->rawColumns(['opciones',])
        ->make(true);



       } catch (QueryException $e) {
       return response()->json([
        'icon' => '',
        'text' => '',
        'title' => '',
        'message' => 'Ha ocurrido un error',
        'error' => $e,
       ],402);
       }
     }

    public function realizarAjuste(Request $request){
        // Blindaje anti-duplicado: solo permite un guardado en curso por usuario.
        // Si llega una segunda petición (doble clic, doble submit, reintento) mientras
        // la primera sigue procesándose, se rechaza sin tocar la BD.
        $lockKey = 'ajuste_guardar_user_' . Auth::id();
        $lock = Cache::lock($lockKey, 20);

        if (!$lock->get()) {
            return response()->json([
                'icon' => 'warning',
                'title' => 'Ajuste en proceso',
                'text' => 'Ya hay un ajuste en proceso para su usuario. Espere a que finalice antes de reintentar.',
                'message' => 'Solicitud duplicada evitada',
            ], 409);
        }

        try
        {
                // dd($request->all());

                    $arrayTemporal = $request->arregloIdInputs;
                    $arregloIdInputs  = explode(',', $arrayTemporal);

                    $msjCantidadRestarEncabezado ="Ajuste no realizado, los siguientes productos no tienen cantidad suficiente en bodega: <br>";
                    $msjCantidadRestarCuerpo ="";

                    for ($i = 0; $i < count($arregloIdInputs); $i++) {

                        $keyIdRecibido = "idRecibido".$arregloIdInputs[$i];
                        $keyAritmetica = "aritmetica".$arregloIdInputs[$i];
                        $keyIdProducto = "idProducto".$arregloIdInputs[$i];
                        $keyNombre_producto = "nombre_producto".$arregloIdInputs[$i];
                        $keyCantidad = "cantidad".$arregloIdInputs[$i];
                        $keyTotal_unidades = "total_unidades".$arregloIdInputs[$i];


                        $idsRecibido = array_values(array_filter(array_map('intval', explode(',', (string) $request->$keyIdRecibido))));
                        $aritmetica = $request->$keyAritmetica;
                        $idProducto = $request->$keyIdProducto;
                        $nombre_producto = $request->$keyNombre_producto;
                        $cantidad = $request->$keyCantidad;
                        $total_unidades = $request->$keyTotal_unidades;


                        $lotes = ModelRecibirBodega::whereIn('id', $idsRecibido)
                            ->where('producto_id', $idProducto)
                            ->get();

                        if($aritmetica==1){
                            $operacion = $lotes->sum('cantidad_disponible') + $total_unidades;
                        }else{
                            $operacion = $lotes->sum('cantidad_disponible') - $total_unidades;
                            if($operacion<0){
                                $msjCantidadRestarCuerpo = "<p> <b>".$msjCantidadRestarCuerpo.$idProducto ."-".$nombre_producto.".</b> </p> <br>";
                            }
                        }

                    }


                    //Comprobar si hay productos que exceden
                    if($msjCantidadRestarCuerpo <> ""){

                        return response()->json([
                            'icon' => 'warning',
                            'text' => "<div> ".$msjCantidadRestarEncabezado.$msjCantidadRestarCuerpo." </div>",
                            'title' => 'Advertencia!',
                            'message' => 'Ha ocurrido un error',
                        ],402);
                    }



                    ////REALIZAR LA OPERACION DE REALIZAR AJUSTE////
                    DB::beginTransaction();


                    $ajuste = new ModelAjuste;
                    $ajuste->numero_ajuste = 'TEMP-' . uniqid('', true);
                    $ajuste->comentario = trim($request->comentario);
                    $ajuste->tipo_ajuste_id = $request->tipo_ajuste_id;
                    $ajuste->solicitado_por = $request->solicitado_por;
                    $ajuste->fecha = $request->fecha;
                    $ajuste->users_id = Auth::user()->id;
                    $ajuste->save();
                    $ajuste->numero_ajuste = date('Y') . '-' . $ajuste->id;
                    $ajuste->save();


                    for ($i = 0; $i < count($arregloIdInputs); $i++) {

                        $keyIdRecibido = "idRecibido".$arregloIdInputs[$i];
                        $keyAritmetica = "aritmetica".$arregloIdInputs[$i];
                        $keyIdProducto = "idProducto".$arregloIdInputs[$i];
                        $keyNombre_producto = "nombre_producto".$arregloIdInputs[$i];
                        $keyCantidad_dispo = "cantidad_dispo".$arregloIdInputs[$i];
                        // $keyUnidad = "unidad".$arregloIdInputs[$i];
                        $keyPrecio_producto = "precio_producto".$arregloIdInputs[$i];
                        $keyCantidad = "cantidad".$arregloIdInputs[$i];
                        $keyTotal_unidades = "total_unidades".$arregloIdInputs[$i];
                        $keyIdUnidadVenta = "idUnidadVenta".$arregloIdInputs[$i];



                        $idsRecibido = array_values(array_filter(array_map('intval', explode(',', (string) $request->$keyIdRecibido))));
                        $aritmetica = $request->$keyAritmetica;
                        $idProducto = $request->$keyIdProducto;
                        $nombre_producto = $request->$keyNombre_producto;
                        $cantidad_dispo = $request->$keyCantidad_dispo;
                        // $unidad = $request->$keyUnidad;
                        $precio_producto = $request-> $keyPrecio_producto;
                        $cantidad = $request->$keyCantidad;
                        $total_unidades = $request->$keyTotal_unidades;
                        $idUnidadVenta = $request->$keyIdUnidadVenta;



                        $lotes = ModelRecibirBodega::whereIn('id', $idsRecibido)
                            ->where('producto_id', $idProducto)
                            ->orderBy('created_at')
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get();

                        if($aritmetica==1){
                            $ajusteTipoAritmetica = "Ajuste de tipo suma de unidades";
                            $distribucion = [[$lotes->first(), $total_unidades]];
                        }elseif($aritmetica==2){
                            $ajusteTipoAritmetica = "Ajuste de tipo resta de unidades";
                            $distribucion = [];
                            $unidadesPendientes = $total_unidades;

                            foreach ($lotes as $lote) {
                                if ($unidadesPendientes <= 0) {
                                    break;
                                }

                                $unidadesLote = min($lote->cantidad_disponible, $unidadesPendientes);
                                $distribucion[] = [$lote, $unidadesLote];
                                $unidadesPendientes -= $unidadesLote;
                            }

                        }

                        foreach ($distribucion as [$lote, $unidadesLote]) {
                        $cantidadLote = $total_unidades > 0
                            ? $cantidad * ($unidadesLote / $total_unidades)
                            : 0;


                        $ajusteProducto = new ModelAjusteProducto;
                        $ajusteProducto->ajuste_id = $ajuste->id;
                        $ajusteProducto->producto_id = $idProducto;
                        $ajusteProducto->recibido_bodega_id = $lote->id;
                        $ajusteProducto->tipo_aritmetica = $aritmetica;
                        $ajusteProducto->precio_producto = $precio_producto;
                        $ajusteProducto->cantidad_inicial = $lote->cantidad_disponible;
                        $ajusteProducto->cantidad = $cantidadLote;
                        $ajusteProducto->cantidad_total = $unidadesLote;
                        $ajusteProducto->unidad_medida_venta_id = $idUnidadVenta;
                        $ajusteProducto->save();

                        $lote->cantidad_disponible += $aritmetica == 1 ? $unidadesLote : -$unidadesLote;
                        $lote->save();


                        $logRegistro = new ModelLogTranslados;
                        $logRegistro->origen=$lote->id;
                        $logRegistro->cantidad= $unidadesLote;
                        $logRegistro->unidad_medida_venta_id = $idUnidadVenta;
                        $logRegistro->users_id=Auth::user()->id;
                        $logRegistro->descripcion=$ajusteTipoAritmetica;
                        $logRegistro->ajuste_id=$ajuste->id;
                        $logRegistro->save();

                        }
                    }




                DB::commit();


                return response()->json([
                    'icon' => "success",
                    'text' => '
                    <p class="text-center">Ajuste realizado con exito.<p>
                    <div class="text-center">
                        <a href="/ajustes/imprimir/ajuste/'.$ajuste->id.'" target="_blank" class="btn btn-sm btn-success btn-lg"><i class="fa-solid fa-file-invoice"></i> Imprimir Documento de Ajuste</a>

                    </div>',
                    'title' => 'Exito!',


                    ], 200);
        } catch (QueryException $e) {
            DB::rollback();
        return response()->json([
            'icon' => 'error',
            'text' => 'Ha ocurrido un error al realizar el ajuste, los cambios no fueron guardados.',
            'title' => 'Error!',
            'message' => 'Ha ocurrido un error',
            'error' => $e,
        ],402);
        } finally {
            $lock->release();
        }
        }

        public function imprimirAjuste($idAjuste){
/*
            $productos = DB::SELECT("
            select
            C.id,
            C.nombre,
            H.nombre as bodega,
            F.descripcion as seccion,
            J.nombre as medida,
            FORMAT( (A.precio_producto * A.cantidad_total)/A.cantidad,2 ) as precio,
            FORMAT(A.cantidad_total,2) as cantidad,
            FORMAT(A.precio_producto * A.cantidad_total,2) as total
            from ajuste A
            inner join recibido_bodega B
            on A.recibido_bodega_id = B.id
            inner join producto C
            on B.producto_id = C.id
            inner join unidad_medida_venta D
            on A.unidad_medida_venta_id = D.id
            inner join unidad_medida J
            on J.id = D.unidad_medida_id
            inner join seccion F
            on B.seccion_id = F.id
            inner join segmento G
            on F.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where A.id = ".$idAjuste);

            $ajuste = DB::SELECTONE("
            select
            cantidad_inicial,
            cantidad_total,
            tipo_aritmetica,
            FORMAT(precio_producto*cantidad_total,2) as costo,
            numero_ajuste
            from ajuste where id = ".$idAjuste
            );

            */


            $datos = DB::SELECTONE("
            select
            DATE_FORMAT(fecha,'%d/%m/%Y') as fecha,
            comentario,
            tipo_ajuste.nombre as motivo,
            name as realizado_por,
            (select name from users where id = ajuste.solicitado_por ) as solicitado_por
            from ajuste
            inner join users
            on ajuste.users_id = users.id
            inner join tipo_ajuste
            on ajuste.tipo_ajuste_id = tipo_ajuste.id
            where ajuste.id = ".$idAjuste
            );

            $productos = DB::SELECT("
            select
            ajuste_has_producto.producto_id,
            producto.nombre as 'producto',
            unidad_medida.nombre as 'medida',
            ajuste_has_producto.cantidad,
            ajuste_has_producto.precio_producto,
            bodega.nombre as 'bodega',
            seccion.descripcion as 'seccion',
            (
              ajuste_has_producto.precio_producto * ajuste_has_producto.cantidad
            ) as 'total',
            if(ajuste_has_producto.tipo_aritmetica = 1,'+','-') as aritmetica
          from
            bodega
            inner join segmento on (segmento.bodega_id = bodega.id)
            inner join seccion on ( seccion.segmento_id = segmento.id )
            inner join recibido_bodega on ( recibido_bodega.seccion_id = seccion.id )
            inner join ajuste_has_producto on (ajuste_has_producto.recibido_bodega_id = recibido_bodega.id )
            inner join producto on (producto.id = ajuste_has_producto.producto_id)
            inner join unidad_medida_venta on (unidad_medida_venta.id = ajuste_has_producto.unidad_medida_venta_id)
            inner join unidad_medida on (unidad_medida.id = unidad_medida_venta.unidad_medida_id  )

          where
            ajuste_has_producto.ajuste_id = ".$idAjuste
            );



            $ajuste = DB::SELECTone("
            select
            cantidad_inicial,
            cantidad_total,
            tipo_aritmetica,
            (precio_producto*cantidad_total) as costo,
             ajuste_id as numero_ajuste
            from ajuste_has_producto where ajuste_id = ".$idAjuste
            );


            $pdf = PDF::loadView('/pdf/ajuste', compact('datos', 'productos', 'ajuste'))->setPaper('letter');

            return $pdf->stream("Ajuste numero ".$idAjuste.".pdf");

        }



    }
