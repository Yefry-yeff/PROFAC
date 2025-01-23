<?php

namespace App\Http\Livewire\Cardex;

use Livewire\Component;


use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use DataTables;

class Cardextres extends Component
{
    public function render()
    {
        return view('livewire.cardex.cardextres');
    }
    public function listarBodegas(Request $request){
        try {

            $bodegas = DB::SELECT("select id, concat(id,' - ',nombre) as text  from bodega where estado_id = 1 and (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15");

            return response()->json([
                "results" => $bodegas,
            ], 200);

        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Ha ocurrido un error al listar las bodegas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarProductos(Request $request){
        try {

            $productos = DB::SELECT("
                SELECT producto.id as id, concat(producto.id,' - ',producto.nombre) as text FROM producto
                INNER JOIN recibido_bodega on (producto.id = recibido_bodega.producto_id)
                INNER JOIN seccion on (seccion.id = recibido_bodega.seccion_id)
                INNER JOIN segmento on (segmento.id = seccion.segmento_id)
                INNER JOIN bodega on (segmento.bodega_id = bodega.id)
                WHERE
                estado_producto_id = 1
                and (  producto.nombre like  '%".$request->search."%' or  producto.id like  '%".$request->search."%')
                and bodega.id = ".$request->idBodega);

            return response()->json([
                "results" => $productos,
            ], 200);

        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Ha ocurrido un error al listar las bodegas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarCardex($idBodega, $idProducto){
        //dd($idBodega, $idProducto);
        try {

            $listaCardex = DB::SELECT("CALL sp_ConsultaCardex(".$idProducto.",". $idBodega.")");



            return Datatables::of($listaCardex)
            ->addColumn('doc_factura', function($elemento){
                if($elemento->id_factura != null){
                    return '<a target="_blank" href="/detalle/venta/'.$elemento->id_factura.'"><i class="fas fa-receipt"></i> FACTURA # '.$elemento->numero_factura.'</a>';
                }
            })

            ->addColumn('doc_ajuste', function($elemento){
                if($elemento->ajuste != null){
                    return '<a target="_blank" href="/ajustes/imprimir/ajuste/'.$elemento->ajuste.'"><i class="fas fa-receipt"></i> VER DETALLE DE AJUSTE #'.$elemento->ajuste_cod.'</a>';
                }
            })

            ->addColumn('detalleCompra', function($elemento){
                if($elemento->detalleCompra != null){
                    return '<a target="_blank" href="/producto/compras/detalle/'.$elemento->detalleCompra.'"><i class="fas fa-receipt"></i> DETALLE DE COMPRA </a>';
                }
            })
            ->addColumn('comprobante_entrega', function($elemento){
                if($elemento->comprobante != null){
                    return '<a target="_blank" href="/comprobante/imprimir/'.$elemento->comprobante.'"><i class="fas fa-receipt"></i> COMPROBANTE DE ENTREGA #'.$elemento->numero_comprovante.' </a>';
                }
            })


            ->addColumn('vale_tipo_1', function($elemento){
                if($elemento->vale_tipo_1 != null){
                    return '<a target="_blank" href="/imprimir/entrega/'.$elemento->vale_tipo_1.'"><i class="fas fa-receipt"></i> VALE TIPO 1 #'.$elemento->vale_tipo_1_cod.' </a>';
                }
            })
            ->addColumn('vale_tipo_2', function($elemento){
                if($elemento->vale_tipo_2 != null){
                    return '<a target="_blank" href="/imprimir/entrega/'.$elemento->vale_tipo_2.'"><i class="fas fa-receipt"></i> VALE TIPO 1 #'.$elemento->vale_tipo_2_cod.' </a>';
                }
            })

            ->addColumn('nota_credito', function($elemento){
                if($elemento->nota_credito != null){
                    return '<a target="_blank" href="/nota/credito/imprimir/'.$elemento->nota_credito.'"><i class="fas fa-receipt"></i> NOTA DE CREDITO #'.$elemento->numero_nota.' </a>';
                }
            })
            ->rawColumns(['doc_factura','doc_ajuste','detalleCompra','comprobante_entrega','vale_tipo_1','vale_tipo_2','nota_credito'])
            ->make(true);

        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error al listar el cardex solicitado.",
                "error" => $e
            ]);
        }

    }


    public function listarCardexNuevo($idProducto,$idBodega){
        //dd($idBodega, $idProducto);
       // dd("Entro");
        try {
            $listaCardex = DB::SELECT("CALL sp_ConsultaCardex(".$idProducto.",". $idBodega.")");
            /* $listaCardex = DB::SELECT("
                SELECT
        c.fecha_creacion,
        c.producto,
        c.id_producto,
        f.id factura,
        f.numero_factura,
        a.id ajuste,
        a.numero_ajuste ajuste_cod,
        c.id_compra detalleCompra,
        ce.id comprobante,
        ce.numero_comprovante comprobante_cod,
        v.id vale_tipo_1,
        v.numero_vale vale_tipo_1_cod,
        nc.id nota_credito,
        nc.numero_nota nota_credito_cod,
        c.descripcion,
        CONCAT(b.nombre, ' ', s.descripcion) AS origen,
        CONCAT(bo.nombre, ' ', se.descripcion) AS destino,
        c.cantidad,
        c.usuario
    FROM cardex c
    LEFT JOIN factura f ON f.id = c.id_factura
    LEFT JOIN ajuste a ON a.id = c.id_ajuste
    LEFT JOIN comprovante_entrega ce ON ce.id = c.id_comprobante_entrega
    LEFT JOIN vale v ON v.id = c.id_vale_tipo_1
    LEFT JOIN nota_credito nc ON nc.id = c.id_nota_de_credito
    LEFT JOIN bodega b ON b.id = c.id_Bodega_origen
    LEFT JOIN seccion s ON s.id = c.id_seccion_origen
    LEFT JOIN bodega bo ON bo.id = c.id_bodega_destino
    LEFT JOIN seccion se ON se.id = c.id_seccion_destino
    WHERE c.id_producto = 2266
      AND (c.id_Bodega_origen = 1 OR c.id_bodega_destino = 1)
      ORDER BY c.fecha_creacion  ;




            "); */
           // dd($listaCardex);
            return Datatables::of($listaCardex)
            ->addColumn('doc_factura', function($elemento){
                if($elemento->id_factura != null){
                    return '<a target="_blank" href="/detalle/venta/'.$elemento->id_factura.'"><i class="fas fa-receipt"></i> FACTURA # '.$elemento->numero_factura.'</a>';
                }
            })

            ->addColumn('doc_ajuste', function($elemento){
                if($elemento->ajuste != null){
                    return '<a target="_blank" href="/ajustes/imprimir/ajuste/'.$elemento->ajuste.'"><i class="fas fa-receipt"></i> VER DETALLE DE AJUSTE #'.$elemento->ajuste_cod.'</a>';
                }
            })

            ->addColumn('detalleCompra', function($elemento){
                if($elemento->detalleCompra != null){
                    return '<a target="_blank" href="/producto/compras/detalle/'.$elemento->detalleCompra.'"><i class="fas fa-receipt"></i> DETALLE DE COMPRA </a>';
                }
            })
            ->addColumn('comprobante_entrega', function($elemento){
                if($elemento->comprobante != null){
                    return '<a target="_blank" href="/comprobante/imprimir/'.$elemento->comprobante.'"><i class="fas fa-receipt"></i> COMPROBANTE DE ENTREGA #'.$elemento->numero_comprovante.' </a>';
                }
            })


            ->addColumn('vale_tipo_2', function($elemento){
                if($elemento->vale_tipo_2 != null){
                    return '<a target="_blank" href="/imprimir/entrega/'.$elemento->vale_tipo_2.'"><i class="fas fa-receipt"></i> VALE TIPO 1 #'.$elemento->vale_tipo_2_cod.' </a>';
                }
            })/*
            ->addColumn('vale_tipo_2', function($elemento){
                if($elemento->vale_tipo_2 != null){
                    return '<a target="_blank" href="/vale/imprimir/'.$elemento->vale_tipo_2.'"><i class="fas fa-receipt"></i> VALE TIPO 2 #'.$elemento->vale_tipo_2_cod.' </a>';
                }*/

            ->addColumn('nota_credito', function($elemento){
                if($elemento->nota_credito != null){
                    return '<a target="_blank" href="/nota/credito/imprimir/'.$elemento->nota_credito.'"><i class="fas fa-receipt"></i> NOTA DE CREDITO #'.$elemento->numero_nota.' </a>';
                }
            })
            ->rawColumns(['doc_factura','doc_ajuste','detalleCompra','comprobante_entrega','vale_tipo_2','nota_credito'])
            ->make(true);

        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error al listar el cardex solicitado.",
                "error" => $e
            ]);
        }

    }
}
