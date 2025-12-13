<?php

namespace App\Http\Livewire\NotaCredito;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\File;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelParametro;
use App\Models\ModelLista;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\User;

class ListadoNotaCredito extends Component
{
    public function render()
    {
        $fechaActual = date('n');
        $resta = $fechaActual - 2;

        $mesActual =0;
        $AnioActual = date('Y');

        if($resta<=0){
            $mesActual=12;
            $AnioActual = $AnioActual - 1;
        }elseif($resta>0 && $resta<10){
            $mesActual = '0'.$resta;
        }else{
            $mesActual = date('m');
        }


        $fechaInicio = $AnioActual.'-'.$mesActual.'-01';



        return view('livewire.nota-credito.listado-nota-credito',compact('fechaInicio'));
    }

    public function listadoNotaCredito(Request $request){
        try{
            $listado = DB::SELECT("
            select
            fa.id as idFactura,
            A.id as codigo,
            A.cai,
            cli.nombre as cliente,
            fa.cai as factura,
            B.descripcion as motivo,
            A.comentario,
            A.sub_total as sub_total,
            A.isv as isv,
            A.total as total,
            A.created_at as fecha_registro,
            name as registrado_por
            from nota_credito A
            inner join motivo_nota_credito B
            on A.motivo_nota_credito_id = B.id
            inner join users
            on A.users_id = users.id
            inner join factura fa on fa.id = A.factura_id
            inner join cliente cli on cli.id = fa.cliente_id
            where

            fa.tipo_venta_id = 2
            and estado_nota_id <>2
            and fecha BETWEEN '".$request->fechaInicio."' and '".$request->fechaFinal."'"
            );
            //dd($listado);
            /* A.estado_nota_dec = 1 */
            return Datatables::of($listado)
            ->addColumn('opciones', function ($nota) {
                    return

                    '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
    
                        
                             <li><a class="dropdown-item" onclick="anularNota('.$nota->codigo.','.$nota->idFactura.' )" class="btn btn-sm btn-warning "><i class="fa-solid fa-trash"></i> Anular</a></li>
    
    
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Orginal</a></li>
    
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/copia/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Copia</a></li>
    
                        </ul>
    
    
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


    public function imprimirFacturaCoorporativa($idFactura)
    {
            /*CONSULTA PARA LISTAR PRODUCTOS NOTA DE CRÉDITO*/

            /*


        select
            D.id AS codigo,
            D.nombre as descripcion,
            F.nombre as medida,
            H.nombre AS bodega,
            FF.descripcion as seccion,
            FORMAT(C.precio_unidad,2) as precio,
            FORMAT(C.cantidad,2) as cantidad,
            FORMAT(C.sub_total,2) as sub_total
        from factura A
        inner join nota_credito B
        on A.id = B.factura_id
        inner join nota_credito_has_producto C
        on B.id = C.nota_credito_id
        inner join producto D
        on C.producto_id = D.id
        inner join unidad_medida_venta E
        on C.unidad_medida_venta_id = E.id
        inner join unidad_medida F
        on F.id = E.unidad_medida_id
        inner join seccion FF
        on C.seccion_id = FF.id
        inner join segmento G
        on FF.segmento_id = G.id
        inner join bodega H
        on G.bodega_id = H.id
        where B.estado_nota_id=1 and A.id = 1422
        group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total
            */
        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        name,
        D.id as factura

       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        factura.nombre_cliente as nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        cliente.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = ".$idFactura);


            $importes = DB::SELECTONE("
            select
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from factura
            where id = " . $idFactura);


            $importesConCentavos = DB::SELECTONE("
            select
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from factura where factura.id = " . $idFactura);




        $productos = DB::SELECT("

                select
                D.id AS codigo,
                D.nombre as descripcion,
                F.nombre as medida,
                H.nombre AS bodega,
                FF.descripcion as seccion,
                FORMAT(C.precio_unidad,2) as precio,
                FORMAT(C.cantidad,2) as cantidad,
                FORMAT(C.sub_total,2) as sub_total
            from factura A
            inner join nota_credito B
            on A.id = B.factura_id
            inner join nota_credito_has_producto C
            on B.id = C.nota_credito_id
            inner join producto D
            on C.producto_id = D.id
            inner join unidad_medida_venta E
            on C.unidad_medida_venta_id = E.id
            inner join unidad_medida F
            on F.id = E.unidad_medida_id
            inner join seccion FF
            on C.seccion_id = FF.id
            inner join segmento G
            on FF.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where B.estado_nota_id=1 and A.id = ".$idFactura."
            group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total
            "
        );

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =".$idFactura);

        if(empty($ordenCompra->numero_orden)){
            $ordenCompra=["numero_orden"=>""];
        }else{
            $ordenCompra=["numero_orden"=>$ordenCompra->numero_orden];
        }


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/notaCredito', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos','ordenCompra'))->setPaper('letter');

        return $pdf->stream("nota_credito" . $cai->numero_factura.".pdf");


    }

    public function imprimirnotaCreditoOriginal($idNota)
    {
        $cai = DB::SELECTONE("
        select
        A.cai nota_credito_cai,
        B.cai factura,
        C.cai,
        CONCAT(DAY(C.fecha_limite_emision),'/',MONTH(C.fecha_limite_emision),'/',YEAR(C.fecha_limite_emision)) fecha_limite_emision,
        C.numero_inicial,
        C.numero_final,
        DATE_FORMAT(A.created_at,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(B.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        U.name, B.estado_factura_id as estado_factura, B.estado_venta_id, B.numero_factura
        from nota_credito A
        inner join factura B
        on A.factura_id = B.id
        inner join cai C
        on A.cai_id = C.id
        inner join users U on (U.id = A.users_id)
        where A.id =".$idNota
        );



        $cliente = DB::SELECTONE("
        select
         factura.nombre_cliente as nombre,
         cliente.direccion,
         cliente.correo,
         factura.fecha_emision,
         factura.fecha_vencimiento,
         TIME(factura.created_at) as hora,
         cliente.telefono_empresa,
         cliente.rtn
         from factura
         inner join cliente
         on factura.cliente_id = cliente.id
         inner join nota_credito
         on nota_credito.factura_id = factura.id
         where nota_credito.id = ".$idNota
        );




            $importes = DB::SELECTONE("
            select
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from nota_credito
            where id = " . $idNota);


            $importesConCentavos = DB::SELECTONE("
            select
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from nota_credito where nota_credito.id = " . $idNota);


            $productos = DB::SELECT("

            select * from (
                select
            D.id AS codigo,
            D.nombre as descripcion,
            F.nombre as medida,
            H.nombre AS bodega,
            FF.descripcion as seccion,
            FORMAT(C.precio_unidad,2) as precio,
            FORMAT(C.cantidad,2) as cantidad,
            FORMAT(C.sub_total,2) as sub_total,
            C.indice
        from factura A
        inner join nota_credito B
        on A.id = B.factura_id
        inner join nota_credito_has_producto C
        on B.id = C.nota_credito_id
        inner join producto D
        on C.producto_id = D.id
        inner join unidad_medida_venta E
        on C.unidad_medida_venta_id = E.id
        inner join unidad_medida F
        on F.id = E.unidad_medida_id
        inner join seccion FF
        on C.seccion_id = FF.id
        inner join segmento G
        on FF.segmento_id = G.id
        inner join bodega H
        on G.bodega_id = H.id
        where B.estado_nota_id=1 and B.id = ".$idNota."
        group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total,C.indice
        ) A
        order by A.indice asc
                "
            );


            if( fmod($importes->total, 1) == 0.0 ){
                $flagCentavos = false;

            }else{
                $flagCentavos = true;
            }

            $formatter = new NumeroALetras();
            $formatter->apocope = true;
            $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

            $pdf = PDF::loadView('/pdf/notaCredito', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos'))->setPaper('letter');

            return $pdf->stream("nota_credito" . $cai->nota_credito_cai.".pdf");






    }

    public function imprimirnotaCreditoCopia($idNota)
    {
        $cai = DB::SELECTONE("
        select
        A.cai nota_credito_cai,
        B.cai factura,
        C.cai,
        CONCAT(DAY(C.fecha_limite_emision),'/',MONTH(C.fecha_limite_emision),'/',YEAR(C.fecha_limite_emision)) fecha_limite_emision,
        C.numero_inicial,
        C.numero_final,
        DATE_FORMAT(A.created_at,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(B.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        U.name, B.estado_factura_id as estado_factura, B.estado_venta_id, B.numero_factura
        from nota_credito A
        inner join factura B
        on A.factura_id = B.id
        inner join cai C
        on A.cai_id = C.id
        inner join users U on (U.id = A.users_id)
        where A.id =".$idNota
        );



        $cliente = DB::SELECTONE("
        select
         factura.nombre_cliente as nombre,
         cliente.direccion,
         cliente.correo,
         factura.fecha_emision,
         factura.fecha_vencimiento,
         TIME(factura.created_at) as hora,
         cliente.telefono_empresa,
         cliente.rtn
         from factura
         inner join cliente
         on factura.cliente_id = cliente.id
         inner join nota_credito
         on nota_credito.factura_id = factura.id
         where nota_credito.id = ".$idNota
        );




            $importes = DB::SELECTONE("
            select
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from nota_credito
            where id = " . $idNota);


            $importesConCentavos = DB::SELECTONE("
            select
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from nota_credito where nota_credito.id = " . $idNota);


            $productos = DB::SELECT("

            select * from (
                select
            D.id AS codigo,
            D.nombre as descripcion,
            F.nombre as medida,
            H.nombre AS bodega,
            FF.descripcion as seccion,
            FORMAT(C.precio_unidad,2) as precio,
            FORMAT(C.cantidad,2) as cantidad,
            FORMAT(C.sub_total,2) as sub_total,
            C.indice
        from factura A
        inner join nota_credito B
        on A.id = B.factura_id
        inner join nota_credito_has_producto C
        on B.id = C.nota_credito_id
        inner join producto D
        on C.producto_id = D.id
        inner join unidad_medida_venta E
        on C.unidad_medida_venta_id = E.id
        inner join unidad_medida F
        on F.id = E.unidad_medida_id
        inner join seccion FF
        on C.seccion_id = FF.id
        inner join segmento G
        on FF.segmento_id = G.id
        inner join bodega H
        on G.bodega_id = H.id
        where B.estado_nota_id=1 and B.id = ".$idNota."
        group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total,C.indice
        ) A
        order by A.indice asc
                "
            );


            if( fmod($importes->total, 1) == 0.0 ){
                $flagCentavos = false;

            }else{
                $flagCentavos = true;
            }

            $formatter = new NumeroALetras();
            $formatter->apocope = true;
            $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

            $pdf = PDF::loadView('/pdf/notaCredito_copia', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos'))->setPaper('letter');

            return $pdf->stream("nota_credito" . $cai->nota_credito_cai.".pdf");






    }
}
