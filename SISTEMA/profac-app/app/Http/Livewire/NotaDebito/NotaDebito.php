<?php

namespace App\Http\Livewire\NotaDebito;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;

use PDF;
use App\Models\NotaDebito\montoNotaDebito;
use App\Models\NotaDebito\notaDebito as mNotaDebito;
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
use App\Http\Controllers\CAI\Notificaciones;

class NotaDebito extends Component
{
    public function render(){
        $cai_nd_existencia = DB::SELECTONE("select count(id) as 'existe' from cai where cai.tipo_documento_fiscal_id = 4 and cai.estado_id = 1 and cai.cantidad_no_utilizada > 0");
        return view('livewire.nota-debito.nota-debito', compact('cai_nd_existencia'));
    }

    public function listarFacturas(){

        try {

            $listaFacturas = DB::SELECT("
            select
            factura.id as id,
            @i := @i + 1 as contador,
            numero_factura,
            factura.cai as correlativo,
            fecha_emision,
            cliente.nombre,
            tipo_pago_venta.descripcion,
            fecha_vencimiento,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(isv,2) as isv,
            FORMAT(total,2) as total,
            factura.credito,
            users.name as creado_por,
            factura.pendiente_cobro as monto_pagado,
            factura.estado_venta_id

            from factura
            inner join cliente
            on factura.cliente_id = cliente.id
            inner join tipo_pago_venta
            on factura.tipo_pago_id = tipo_pago_venta.id
            inner join users
            on factura.vendedor = users.id

            cross join (select @i := 0) r
            where factura.fecha_emision > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            order by factura.created_at desc
            ");






            return Datatables::of($listaFacturas)
            ->addColumn('opciones', function ($listaFacturas) {

                $existencianDebito = DB::SELECTONE("select COUNT(factura_id) as 'existe' from notadebito
                where notadebito.factura_id = ".$listaFacturas->id." and notadebito.estado_id = 1");


                    if ($existencianDebito->existe == 0) {

                        $montoDebito = DB::SELECTONE("select monto, id from montonotadebito where estado_id = 1");

                        return

                        '<div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                <li>
                                    <a class="dropdown-item" onclick="llenadoModalDebito('.$listaFacturas->id.', '.$montoDebito->monto.', '.$montoDebito->id.')" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Asignar Noda Débito </a>
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
                                    <a class="dropdown-item" href="/debito/imprimir/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Imprimir Nota Débito </a>
                                </li>

                            </ul>
                        </div>';
                    }

            })
            ->addColumn('estado_cobro', function ($listaFacturas) {


                //if( round($listaFacturas->monto_pagado,2) >= str_replace(",","",$listaFacturas->total) ){

                    if( $listaFacturas->monto_pagado != 0) {

                    return
                    '
                    <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Pendiente</span></p>
                    ';

                }else{
                    return
                    '

                    <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Completo</span></p>
                    ';
                }

           })
           ->addColumn('estado_ndebito', function ($listaFacturas) {

                $existencianDebito = DB::SELECTONE("select COUNT(factura_id) as 'existe' from notadebito
                where notadebito.factura_id = ".$listaFacturas->id." and notadebito.estado_id = 1");
               if( $existencianDebito->existe == 0 ){
                   return
                   '
                   <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Nota Sin Asignar</span></p>
                   ';

               }else{

                return
                '

                <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Nota Asignada</span></p>
                ';
               }

          })
            ->rawColumns(['opciones','estado_cobro','estado_ndebito'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las compras.',
                'errorTh' => $e,
            ], 402);

        }

    }

    public function listarMontos(){
        try {

            $listaMontos = DB::SELECT("
                select
                id,
                monto,
                (select name from users where id = montonotadebito.users_registra_id) as 'user',
                created_at
                from montonotadebito
                where estado_id = 1
            ");

            return Datatables::of($listaMontos)
            ->addColumn('estado_monto', function ($listaMontos) {
                $ESTADOmONTO = DB::SELECTONE("select estado_id from montonotadebito where id = ".$listaMontos->id);
                if( $ESTADOmONTO->estado_id == 1){

                    return
                    '
                    <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Activo</span></p>
                    ';

                }

           })
            ->rawColumns(['estado_monto'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar los montos.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function guardarMonto(Request $request){
        try {
            DB::beginTransaction();

                DB::update('
                update
                montonotadebito
                set estado_id = 2');

                $montoNotaDebito = new montoNotaDebito;
                $montoNotaDebito->monto = $request->monto;
                $montoNotaDebito->descripcion = $request->descripcion;
                $montoNotaDebito->estado_id = 1;
                $montoNotaDebito->users_registra_id = Auth::user()->id;
                $montoNotaDebito->save();

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Registro de monto de débito con éxito!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al registrar el débito.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    public function guardarNotaDebito(Request $request){

       try {
        $estadoCuenta = DB::selectone('select estado_cerrado from aplicacion_pagos where estado = 1 and factura_id = '.$request->factura_id);
        // dd($saldoActual->saldo);
        if($estadoCuenta != null){
            if($estadoCuenta->estado_cerrado == 2){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"Esta factura esta cerrada, no se puede crear nota.",
                    "title"=>"Advertencia!"

                ],400);

            }

         }

            // dd($request);

        $facturaClienteId = DB::SELECTONE("select cliente_id from factura where id = ". $request->factura_id);
            //tipo_cliente 1 = B y 2 = A
        $tipoCliente = DB::SELECTONE("select tipo_cliente_id from cliente where id = ". $facturaClienteId->cliente_id);

            //tipo_cliente 1 = B-coorporativo-noDeclara-serie y 2 = A-estatal-Sideclara-numeroActual
        if ($tipoCliente->tipo_cliente_id === 2 ) {
            $estado = 2;

             $cai = DB::SELECTONE("select
                             id,
                             numero_inicial,
                             numero_final,
                             cantidad_otorgada,
                             numero_actual as 'numero_actual',
                             if( DATE(NOW()) > fecha_limite_emision ,'TRUE','FALSE') as fecha_limite_emision,
                             cantidad_no_utilizada
                             from cai
                             where tipo_documento_fiscal_id = 4 and estado_id = 1");

         } elseif($tipoCliente->tipo_cliente_id === 1) {

             $estado = 1;

             $cai = DB::SELECTONE("select
                             id,
                             numero_inicial,
                             numero_final,
                             cantidad_otorgada,
                             serie as 'numero_actual',
                             if( DATE(NOW()) > fecha_limite_emision ,'TRUE','FALSE') as fecha_limite_emision,
                             cantidad_no_utilizada
                             from cai
                             where tipo_documento_fiscal_id = 4 and estado_id = 1");
         }

         $limite = explode('-',$cai->numero_final);
         $limite = ltrim($limite[3],"0");

        if($cai->numero_actual >  $limite ){

            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La Nota de débito no puede proceder, debido que ha alcanzadado el número maximo de unidades CAI.",
            ], 401);

        }
        $numeroSecuencia = $cai->numero_actual;
        $arrayCai = explode('-',$cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
        $numeroCAI = $arrayCai[0].'-'.$arrayCai[1].'-'.$arrayCai[2].'-'.$cuartoSegmentoCAI;
        $fechaActual = date('Y');
        $correlativo = $fechaActual.'-'.$numeroSecuencia;

        /* GUARDANDO LO DE LA NOTA DE DÉBITO */

        $validarCAI = new Notificaciones();
        $validarCAI->validarAlertaCAI(ltrim($arrayCai[3],"0"),$numeroSecuencia, 5);

        DB::beginTransaction();

        $NotaDebito = new mNotaDebito;
        $NotaDebito->factura_id = $request->factura_id;
        $NotaDebito->montoNotaDebito_id = $request->montoNotaDebito_id;
        $NotaDebito->monto_asignado = $request->monto_;
        $NotaDebito->fechaEmision = $request->fechaEmision;
        $NotaDebito->motivoDescripcion = $request->motivoDescripcion;
        $NotaDebito->cai_ndebito = $cai->id;
        $NotaDebito->numeroCai = $numeroCAI;
        $NotaDebito->correlativoND = $correlativo;
        $NotaDebito->estado_id = 1;
        $NotaDebito->estado_nota_dec = $estado;
        $NotaDebito->users_registra_id = Auth::user()->id;



        $NotaDebito->estado_sumado =2;
        $NotaDebito->user_registra_sumado = 0;
        $NotaDebito->comentario_sumado = 'N/A';
        $NotaDebito->fecha_sumado = NULL;

        $NotaDebito->save();

        /* SE AGREGA LA FUNCION DE SUMAR EL MONTO DE NOTA DE DEBITO A LA FACTURA */
        /*
                $facturaTotal = DB::SELECTONE("select total from factura where id = ". $request->factura_id);

                $totalFacturaMasnDebito = $request->monto_ + $facturaTotal->total;

                DB::table('factura')->where('id',$request->factura_id)->update(array('total'=>$totalFacturaMasnDebito));
        */

        /* ============================================================================ */


        if ($tipoCliente->tipo_cliente_id === 2 ) {
            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->numero_actual = $caiUpdated->numero_actual + 1;
            $caiUpdated->cantidad_no_utilizada=  $caiUpdated->cantidad_no_utilizada - 1;
            $caiUpdated->save();
        }  elseif($tipoCliente->tipo_cliente_id === 1 )  {
            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->serie = $caiUpdated->serie + 1;
            $caiUpdated->cantidad_no_utilizada=  $caiUpdated->cantidad_no_utilizada - 1;
            $caiUpdated->save();
        }


        DB::commit();
       return response()->json([
        'icon' => 'success',
        'text' => 'Nota de debito realizada con éxito.',
        'title' => 'Exito!',
       ],200);
       } catch (QueryException $e) {
        DB::rollback();
        return response()->json([
            'icon' => 'error',
            'text' => 'Ha ocurrido un error al guardar la nota de debito',
            'title' => 'Error',
            'message' => 'Ha ocurrido un error',
            'error' => $e,
           ],402);
       }



    }

    public function listarnotasDebito(){
        try {

            $listanotaDebito = DB::SELECT("
                select
                id
                ,factura_id
                ,monto_asignado
                ,fechaEmision
                ,motivoDescripcion
                ,cai_ndebito
                ,numeroCai
                ,correlativoND
                ,(select name from users where id = notadebito.users_registra_id) as 'user'
                ,created_at
                from notadebito
                where estado_id = 1
            ");

            return Datatables::of($listanotaDebito)
            ->addColumn('estado', function ($listanotaDebito) {
                $ESTADO = DB::SELECTONE("select estado_id from notadebito where id = ".$listanotaDebito->id);
                if( $ESTADO->estado_id == 1){

                    return
                    '
                    <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Activo</span></p>
                    ';

                }else if($ESTADO->estado_id == 2) {
                    return
                    '
                    <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Inactivo</span></p>
                    ';
                }

           })
           ->addColumn('file', function ($listanotaDebito) {

                    return
                    '
                        <a class="btn btn-success" href="/debito/imprimir/'.$listanotaDebito->factura_id.'" > Ver <i class="fa-solid fa-file-pdf"></i></a>
                    ';

            })
            ->rawColumns(['estado','file'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las notas de debito.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function descargarNota($idFactura){

            $notaDebito = DB::SELECTONE("
                select
                    id
                    ,factura_id
                    ,monto_asignado
                    ,fechaEmision
                    ,motivoDescripcion
                    ,cai_ndebito
                    ,numeroCai
                    ,correlativoND
                    ,(select name from users where id = notadebito.users_registra_id) as 'user'
                    ,created_at
                    ,estado_id
                from notadebito
                where notadebito.factura_id = ".$idFactura
            );

            $cai = DB::SELECTONE("select
                *
            from cai
            where tipo_documento_fiscal_id = 4 and estado_id = 1 and id = ".$notaDebito->cai_ndebito);


            $cliente = DB::SELECTONE("select nombre_cliente, cai, estado_factura_id, numero_factura   from factura where id = ".$idFactura);

            $formatter = new NumeroALetras();
            $formatter->apocope = true;
            $numeroLetras = $formatter->toMoney($notaDebito->monto_asignado, 2, 'LEMPIRAS', 'CENTAVOS');

            $montoConCentavos= DB::SELECTONE("
            select
                FORMAT(monto_asignado,2) as total
            from notadebito where factura_id = ".$idFactura);

            $pdf = PDF::loadView('/pdf/nodaDeDebito', compact('numeroLetras','notaDebito', 'cliente', 'cai', 'montoConCentavos'))->setPaper('letter');

            return $pdf->stream("nota_debito_" . $notaDebito->factura_id.".pdf");

    }
    public function descargarNotaCopia($idFactura){

        $notaDebito = DB::SELECTONE("
            select
                id
                ,factura_id
                ,monto_asignado
                ,fechaEmision
                ,motivoDescripcion
                ,cai_ndebito
                ,numeroCai
                ,correlativoND
                ,(select name from users where id = notadebito.users_registra_id) as 'user'
                ,created_at
                ,estado_id
            from notadebito
            where notadebito.factura_id = ".$idFactura
        );

        $cai = DB::SELECTONE("select
            *
        from cai
        where tipo_documento_fiscal_id = 4 and estado_id = 1 and id = ".$notaDebito->cai_ndebito);


        $cliente = DB::SELECTONE("select nombre_cliente, cai, estado_factura_id, numero_factura   from factura where id = ".$idFactura);

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($notaDebito->monto_asignado, 2, 'LEMPIRAS', 'CENTAVOS');

        $montoConCentavos= DB::SELECTONE("
        select
            FORMAT(monto_asignado,2) as total
        from notadebito where factura_id = ".$idFactura);

        $pdf = PDF::loadView('/pdf/nodaDeDebito_copia', compact('numeroLetras','notaDebito', 'cliente', 'cai', 'montoConCentavos'))->setPaper('letter');

        return $pdf->stream("nota_debito_" . $notaDebito->factura_id.".pdf");

}

    public function anularNotaDebito($idNota){
        try {
            DB::beginTransaction();

                DB::update('
                update
                notadebito
                set estado_id = 2
                where estado_sumado = 2 id ='.$idNota);

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Nota Anulada con éxito!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al Anular la nota.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }
}
