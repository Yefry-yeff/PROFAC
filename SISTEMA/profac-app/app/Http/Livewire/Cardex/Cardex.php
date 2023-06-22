<?php

namespace App\Http\Livewire\Cardex;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use DataTables;

class Cardex extends Component
{
    public function render()
    {
        return view('livewire.cardex.cardex');
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

            $listaCardex = DB::SELECT("CALL obtrCardex(".$idBodega.",". $idProducto.")");

            

            return Datatables::of($listaCardex)
            ->addColumn('doc_factura', function($elemento){
                if($elemento->factura != null){
                    return '<a target="_blank" href="/detalle/venta/'.$elemento->factura.'"><i class="fas fa-receipt"></i> FACTURA # '.$elemento->factura_cod.'</a>';
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
                    return '<a target="_blank" href="/comprobante/imprimir/'.$elemento->comprobante.'"><i class="fas fa-receipt"></i> COMPROBANTE DE ENTREGA #'.$elemento->comprobante_cod.' </a>';
                }
            })

            ->addColumn('vale_tipo_1', function($elemento){
                if($elemento->vale_tipo_1 != null){
                    return '<a target="_blank" href="/imprimir/entrega/'.$elemento->vale_tipo_1.'"><i class="fas fa-receipt"></i> VALE TIPO 1 #'.$elemento->vale_tipo_1_cod.' </a>';
                }
            })

            ->addColumn('vale_tipo_2', function($elemento){
                if($elemento->vale_tipo_2 != null){
                    return '<a target="_blank" href="/vale/imprimir/'.$elemento->vale_tipo_2.'"><i class="fas fa-receipt"></i> VALE TIPO 2 #'.$elemento->vale_tipo_2_cod.' </a>';
                }
            })

            ->addColumn('nota_credito', function($elemento){
                if($elemento->nota_credito != null){
                    return '<a target="_blank" href="/nota/credito/imprimir/'.$elemento->nota_credito.'"><i class="fas fa-receipt"></i> NOTA DE CREDITO #'.$elemento->nota_credito_cod.' </a>';
                }
            })
            ->rawColumns(['doc_factura','doc_ajuste', 'detalleCompra','comprobante_entrega','vale_tipo_1','vale_tipo_2','nota_credito'])
            ->make(true);

        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error al listar el cardex solicitado.",
                "error" => $e
            ]);
        }

    }
}
