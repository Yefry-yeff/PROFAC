<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Models\ModelAjuste;
use App\Models\ModelTipoAjuste;
use App\Models\ModelLogTranslados;
use App\Models\ModelRecibirBodega;
use App\Models\ModelAjusteProducto;

use Auth;


class AjusteIngresoProducto extends Component
{
    public function render()
    {
        return view('livewire.inventario.ajuste-ingreso-producto');
    }

    public function obtenerProducto(Request $request){
        try {


            $productos = DB::SELECT("select id, concat(id,' - ',nombre) as text   from producto where nombre like '%".$request->search."%' or id like '%".$request->search."%' or codigo_barra like '%".$request->search."%' limit 15");

 
        return response()->json([
            "results" => $productos 
        ]);
        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ]);
        }
    }

    public function datosProducto(Request $request){
        try {

         $producto = DB::SELECTONE("select id, nombre, precio_base from producto where id=".$request->id);
 
         $datosBodega = DB::SELECTONE("
         select
         UPPER(D.nombre) as bodega,
         UPPER(B.descripcion) as seccion         
         from  seccion B        
         inner join segmento C
         on C.id = B.segmento_id
         inner join bodega D
         on D.id = C.bodega_id
         where B.id = ".$request->idSeccion);
 
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

      public function realizarAjuste(Request $request){
        try {

            $arrayTemporal = $request->arregloIdInputs;            
            $arregloIdInputs  = explode(',', $arrayTemporal);

            DB::beginTransaction();

                
            $numeroOrden = DB::SELECTONE("select concat(YEAR(NOW()),'-',count(id)+1) as numero_orden from ajuste");

            $ajuste = new ModelAjuste;
            $ajuste->numero_ajuste = $numeroOrden->numero_orden;
            $ajuste->comentario = trim($request->comentario);
            $ajuste->tipo_ajuste_id = $request->tipo_ajuste_id;
            $ajuste->solicitado_por = $request->solicitado_por;
            $ajuste->fecha = $request->fecha;   
            $ajuste->users_id = Auth::user()->id;          
            $ajuste->save();

            for ($i = 0; $i < count($arregloIdInputs); $i++) {
                 
                $keyIdSeccion = "idSeccion".$arregloIdInputs[$i];
                $keyAritmetica = "aritmetica".$arregloIdInputs[$i];
                $keyIdProducto = "idProducto".$arregloIdInputs[$i];          
                $keyPrecio_producto = "precio_producto".$arregloIdInputs[$i];
                $keyCantidad = "cantidad".$arregloIdInputs[$i];
                $keyTotal_unidades = "total_unidades".$arregloIdInputs[$i];   
                $keyIdUnidadVenta = "idUnidadVenta".$arregloIdInputs[$i];

        
                $idSeccion = $request->$keyIdSeccion;                 
                $aritmetica = $request->$keyAritmetica;
                $idProducto = $request->$keyIdProducto;  
                $precio_producto = $request-> $keyPrecio_producto;
                $cantidad = $request->$keyCantidad;
                $total_unidades = $request->$keyTotal_unidades;
                $idUnidadVenta = $request->$keyIdUnidadVenta;



                $recibir = new ModelRecibirBodega();  
                //para ingreso de producto por ajuste el idCompra y unidad_compra_id se crean con valor null              
                $recibir->producto_id = $idProducto;
                $recibir->seccion_id =  $idSeccion;
                $recibir->cantidad_compra_lote = 0;
                $recibir->cantidad_inicial_seccion = $total_unidades;
                $recibir->cantidad_disponible = $total_unidades;
                $recibir->fecha_recibido = now();               
                $recibir->estado_recibido = 4;
                $recibir->recibido_por = Auth::user()->id;
                $recibir->unidades_compra = $cantidad;     
                $recibir->comentario ="Ingreso de producto por ajuste";          
                $recibir->save();


                $ajusteProducto = new ModelAjusteProducto;
                $ajusteProducto->ajuste_id = $ajuste->id;
                $ajusteProducto->producto_id = $idProducto;         
                $ajusteProducto->tipo_aritmetica = 1; // ingreso de producto
                $ajusteProducto->precio_producto = $precio_producto;
                $ajusteProducto->cantidad_inicial = $total_unidades;
                $ajusteProducto->cantidad = $cantidad;
                $ajusteProducto->cantidad_total = $total_unidades;
                $ajusteProducto->unidad_medida_venta_id = $idUnidadVenta;
                $ajusteProducto->recibido_bodega_id = $recibir->id;
                $ajusteProducto->save();

                $logRegistro = new ModelLogTranslados;
                $logRegistro->origen=$recibir->id;
                $logRegistro->destino=$recibir->id;
                $logRegistro->cantidad= $total_unidades;
                $logRegistro->unidad_medida_venta_id = $idUnidadVenta;
                $logRegistro->users_id=Auth::user()->id;
                $logRegistro->descripcion="Ajuste por ingreso de producto";
                $logRegistro->ajuste_id=$ajuste->id;
                $logRegistro->save();
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
       }
     }

     public function listarBodegas(Request $request){
        try {

         $listaBodegas = DB::SELECT("
             select id ,nombre as 'text' from bodega  where estado_id = 1 and (nombre like '%".$request->search."%' or id like '%".$request->search."%') limit 15
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
                where B.bodega_id = ".$request->bodegaId." and A.descripcion like  '%".$request->search."%'
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
}
