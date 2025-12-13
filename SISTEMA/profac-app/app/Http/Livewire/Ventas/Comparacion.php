<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;

use App\Models\ModelFactura;

class Comparacion extends Component
{
    public function render()
    {
        return view('livewire.ventas.comparacion');
    }

    public function listadoUno(Request $request)
    {
        try {
            $fechaInicio = $request->fechaInicio;
            $fechaFinal = $request->fechaFinal;

            $listado=DB::SELECT("
            select 
                A.id,
                D.id as cod_cai,
                D.cai as cai, 
                A.cai as correlativo,
                numero_secuencia_cai,
                A.nombre_cliente,
                A.rtn,
                sub_total,
                isv,
                total,
                fecha_emision,
                C.descripcion,
                C.id as idTipoCliente
            from factura A
            inner join cliente B
            on A.cliente_id = B.id
            inner join tipo_cliente C
            on B.tipo_cliente_id = C.id
            inner join cai D
            on A.cai_id = D.id           
            where (A.estado_venta_id=1) and estado_editar = 1 and A.estado_factura_id=1 and A.fecha_emision   BETWEEN '". $fechaInicio."' AND '".$fechaFinal."';

            ");

            return Datatables::of($listado)
            ->addColumn('opciones', function ($elemento) {

                if($elemento->idTipoCliente==2){
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
    
                            <li>
                                <a class="dropdown-item" href="/detalle/venta/'.$elemento->id.'" target="_blank" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de Venta </a>
                            </li>
                            <li>
                                <a class="dropdown-item btn disabled"  > <i class="fa-solid fa-arrows-rotate text-warning " disabled></i> Registrar como N/D </a>
                            </li>
                           
    
                            
                        </ul>
                    </div>';
                }else{
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
    
                            <li>
                                <a class="dropdown-item" href="/detalle/venta/'.$elemento->id.'" target="_blank" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de Venta </a>
                            </li>
                            <li>
                                <a class="dropdown-item" onclick="modalTransladoND('.$elemento->id.')" > <i class="fa-solid fa-arrows-rotate text-warning"></i> Registrar como N/D </a>
                            </li>
                           
    
                            
                        </ul>
                    </div>';
                }


            })

            ->rawColumns(['opciones'])
            ->make(true);

        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ],402);
        }
    }

    public function listadoDos(Request $request){
        try {
            $fechaInicio = $request->fechaInicio;
            $fechaFinal = $request->fechaFinal;

            $listado=DB::SELECT("
            select 
                A.id,
                D.id as cod_cai,
                D.cai as cai, 
                A.cai as correlativo,
                numero_secuencia_cai,
                A.nombre_cliente,
                A.rtn,
                sub_total,
                isv,
                total,
                fecha_emision,
                C.descripcion,
                C.id as idTipoCliente
            from factura A
            inner join cliente B
            on A.cliente_id = B.id
            inner join tipo_cliente C
            on B.tipo_cliente_id = C.id
            inner join cai D
            on A.cai_id = D.id
            where (A.estado_venta_id=1) and estado_editar = 1 and  A.estado_factura_id=2 and A.fecha_emision   BETWEEN '". $fechaInicio."' AND '".$fechaFinal."'"
        );

            return Datatables::of($listado)
            ->addColumn('opciones', function ($elemento) {

                return

                '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                        <li>
                            <a class="dropdown-item" href="/detalle/venta/'.$elemento->id.'" target="_blank" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de Venta </a>
                        </li>
                        <li>
                            <a class="dropdown-item" onclick="modalTransladoDC('.$elemento->id.')" > <i class="fa-solid fa-arrows-rotate text-warning"></i> Registrar como D/C </a>
                        </li>
                       

                        
                    </ul>
                </div>';
            })

            ->rawColumns(['opciones'])
            ->make(true);

        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error', 
            'error' => $e
        ],402);
        }
    }

    public function cambioEstadoND($idFactura){
       try {

   


        $cai = DB::SELECTONE("select cai, cai_id from factura where  estado_factura_id = 1  and id = ".$idFactura);

        $idFacturaND = DB::selectone("select id, cai, cai_id from factura where estado_factura_id = 2 and estado_venta_id = 1 and cai = '".$cai->cai."' and cai_id = ". $cai->cai_id);

        if(empty($idFacturaND)){
            return response()->json([
                "text"=>"Acción no permitida.",
                "title"=>"Advertencia!",
                "icon"=>"warning",
               ],200);
        }




     



            ModelFactura::where("id", "=",$idFacturaND->id)->update(["estado_factura_id" => '1']);
            ModelFactura::where("id", "=",$idFactura)->update(["estado_factura_id" => '2']);






       return response()->json([
        "text"=>"Editado con exito.",
        "title"=>"Exito.",
        "icon"=>"success",
       ],200);
       } catch (QueryException $e) {
       return response()->json([
        "text"=>"Ha ocurrido un error.",
        "title"=>"Error.",
        "icon"=>"error",
        'message' => 'Ha ocurrido un error', 
        'error' => $e
       ],402);
       }
    }

    public function cambioEstadoDC($idFactura){
        try {

        $cai = DB::SELECTONE("select cai, cai_id from factura where estado_factura_id = 2 and id = ".$idFactura); 
        
        $contraParte = DB::select("
        select 
        A.id 
        from factura A
        inner join cliente B
        on A.cliente_id = B.id
        where A.estado_factura_id = 1 and A.estado_venta_id=1 and B.tipo_cliente_id=2 and A.cai ='".$cai->cai."' and cai_id =".$cai->cai_id
        );

        if(!empty($contraParte)){        
            return response()->json([
            "text"=>"Acción no permitida.",
            "title"=>"Advertencia!",
            "icon"=>"warning",
           ],200);

        }
        
        



        $idFacturaDC = DB::SELECTONE("select id from factura where  estado_factura_id=1  and estado_venta_id = 1  and  cai='".$cai->cai."' and cai_id = ".$cai->cai_id); 
        if(!empty($idFacturaDC->id)){
            ModelFactura::where("id", "=",$idFacturaDC->id)->update(["estado_factura_id" => '2']);
            ModelFactura::where("id", "=",$idFactura)->update(["estado_factura_id" => '1']);
        }
        

         
 
 
        return response()->json([
         "text"=>"Editado con exito.",
         "title"=>"Exito.",
         "icon"=>"success",
        ],200);
        } catch (QueryException $e) {
        return response()->json([
         "text"=>"Ha ocurrido un error.",
         "title"=>"Error.",
         "icon"=>"error",
         'message' => 'Ha ocurrido un error', 
         'error' => $e
        ],402);
        }
     }
}
