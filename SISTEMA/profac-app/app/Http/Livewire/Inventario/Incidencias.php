<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class Incidencias extends Component
{
    public $idCompra;
    public function mount($id)
    {

        $this->idCompra = $id;

    }
    public function render()
     
    {
        $idCompra = $this->idCompra;
        return view('livewire.inventario.incidencias',compact('idCompra'));
    }

    public function incidenciasBodega($id){
       try {

        $bodega = DB::SELECT("
        select 
            A.numero_factura,
            C.id as 'cod_producto',
            C.nombre,
            bodega.nombre as 'bodega',
            E.descripcion as 'seccion', 
            D.descripcion,   
            D.url_img,
            D.created_at,
            users.name
      from compra A
        inner join recibido_bodega B
        on A.id = B.compra_id
        inner join producto C
        on B.producto_id = C.id
        inner join incidencia D
        on B.id = D.recibido_bodega_id
        inner join users
        on D.users_id = users.id
        inner join seccion E
        on B.seccion_id = E.id
        inner join segmento
        on E.segmento_id = segmento.id
        inner join bodega
        on segmento.bodega_id = bodega.id
          where B.compra_id = ".$id);

          return Datatables::of($bodega)

          ->addColumn('img', function ($bodega){

            if($bodega->url_img){
                return
                '
                <div class="text-center ">
                        <a href="/incidencias_bodega/'.$bodega->url_img.'" target="_blank" class=""><i class="fa-solid fa-file-image text-success" style="font-size: 2rem;"></i></a>
                </div>';

            }else{
                return
                '
                <div class="text-center ">
                        <a   class="a-none"><i class="fa-solid fa-file-circle-xmark" style="font-size: 1.5rem;"></i></a>
                </div>';
            }    



             
          })

          ->rawColumns(['img'])
          ->make(true);   


       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error', 
           'error' => $e
       ],402);
       }

    }

    public function incidenciaCompra($id){
       try {

                $compra = DB::SELECT("
                select 
                    B.numero_factura,
                    C.id as 'cod_producto',
                    C.nombre,
                    A.descripcion,
                    A.url_img,
                    A.created_at,
                    users.name
                from 
                    incidencia_compra A
                    inner join compra B
                    on A.compra_id = B.id
                    inner join producto C
                    on A.producto_id = C.id 
                    inner join users 
                    on A.users_id = users.id
                    where A.compra_id = ".$id
            ); 

            return Datatables::of($compra)

            ->addColumn('img', function ($compra){

            if($compra->url_img){
                return
                '
                <div class="text-center ">
                        <a href="/incidencias_compra/'.$compra->url_img.'" target="_blank" class=""><i class="fa-solid fa-file-image text-success" style="font-size: 2rem;"></i></a>
                </div>';

            }else{
                return
                '
                <div class="text-center ">
                        <a   class="a-none"><i class="fa-solid fa-file-circle-xmark" style="font-size: 1.5rem;"></i></a>
                </div>';
                
                

            }       
            })

            ->rawColumns(['img'])
            ->make(true);   

       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error', 
           'error' => $e
       ],402);
       }

    }
}
