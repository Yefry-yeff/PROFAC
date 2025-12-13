<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use DataTables;
use Throwable;
use PDF;
use Carbon\Carbon;

use Illuminate\Support\Facades\File;

use App\Models\ModelCliente;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CuentasPorCobrarExport;
use App\Exports\CuentasPorCobrarInteresExport;
use App\Models\AplicacionPagos\Modelotros_movimientos;
use App\Models\AplicacionPagos\Modelabonos_creditos;
use App\Models\Comisiones\Escalado\modelproducto_comision;
use App\Models\Comisiones\Escalado\modelfacturas_comision;
use App\Models\Comisiones\Escalado\modelcomision_empleado;
use App\Models\Comisiones\Escalado\modelcomision_escala;
class Pagos extends Component
{
    public function render()
    {
        return view('livewire.cuentas-por-cobrar.pagos');
    }




    public function listarClientes(Request $request){
        try {

         //$clientes = DB::SELECT("select id, nombre as text from cliente where estado_cliente_id = 1");//Clientes Activos
         $clientes = DB::SELECT("select id, concat(id,' - ',nombre) as text from cliente where (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15");//Todos los Clientes

         return response()->json([
            'results'=>$clientes,
        ],200);

        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
    }


    public function listarCuentasPorCobrar($id){
        try{

            /* VALIDANDO EXISTENCIA DE FACTURAS DE ESTE CLIENTE PARA CLIENTES VIEJOS*/
            $existenciaAplicacion = DB::SELECTONE("

                SELECT COUNT(*) AS existe FROM aplicacion_pagos ap
                inner join factura fa on fa.id = ap.factura_id
                inner join cliente cli on cli.id = fa.cliente_id
                where ap.estado = 1 and cli.id = ".$id."


            ");

            $facturasActivas = DB::SELECTONE("

                SELECT COUNT(*) as num
                FROM factura fa
                inner join cliente cli on cli.id = fa.cliente_id
                where fa.estado_venta_id = 1 and cli.id = ".$id."


            ");

            $facturasEnPagos = DB::SELECTONE("

                SELECT COUNT(*) as num
                    from aplicacion_pagos
                where
                    aplicacion_pagos.factura_id in (
                        SELECT
                            fa.id
                        FROM factura fa
                            inner join cliente cli on cli.id = fa.cliente_id
                        where
                            fa.estado_venta_id = 1
                            and cli.id = ".$id."

                )");


            if ($existenciaAplicacion->existe == 0) {

                $cuentas2 = DB::select("

                CALL sp_aplicacion_pagos('1','".$id."', '".Auth::user()->id."', '0','na','0','0','0', @estado, @msjResultado);");

                //dd($cuentas2[0]->estado);

                if ($cuentas2[0]->estado == -1) {
                    return response()->json([
                        "text" => "Ha ocurrido un error al insertar facturas en aplicacion de pagos.",
                        "icon" => "error",
                        "title"=>"Error!"
                    ],402);
                }

            }else if ($facturasActivas->num > $facturasEnPagos->num) {
                //este es el caso de un cliente nuevo o de una factura creada
                //antes de ir al modulo de pagos


                $cuentas3 = DB::select("

                CALL sp_aplicacion_pagos('3','".$id."', '".Auth::user()->id."', '0','na','0','0','0', @estado, @msjResultado);");

                //dd($cuentas2[0]->estado);

                if ($cuentas3[0]->estado == -1) {
                    return response()->json([
                        "text" => "Ha ocurrido un error al insertar facturas en aplicacion de pagos.",
                        "icon" => "error",
                        "title"=>"Error!"
                    ],402);
                }
            }

            $cuentas = DB::select("
                select
                id as                      'codigoPago',
                factura_id as              'idFactura',
                (select cai
                from factura
                where id = factura_id) as  'codigoFactura',
                total_factura_cargo as     'cargo',
                total_notas_credito as     'notasCredito',
                total_nodas_debito as      'notasDebito',
                credito_abonos as          'abonosCargo',
                movimiento_suma as         'movSuma',
                movimiento_resta as        'movResta',
                retencion_isv_factura as   'isv',
                saldo as                   'saldo',
                estado_retencion_isv as    'estadoRetencion',
                retencion_aplicada as      'retencion_aplicada',
                estado as                  'estado',
                estado_cerrado as          'estadoCierre',
                usr_cerro as               'usrCierre',
                created_at as              'fechaRegistro',
                updated_at  as             'ultimoRegistro',
                IF(
                   (
                    select
                       COUNT(*)
                    from nota_credito
                    where nota_credito.factura_id = idFactura
                    ) > 0, 1, 0
                ) as                       'tieneNC',
                IF(
                   (
                    select
                       COUNT(*)
                    from notadebito
                    where notadebito.factura_id = idFactura
                    ) > 0, 1, 0
                ) as                       'tieneND'

                from aplicacion_pagos
                where
                cliente_id = ".$id."
                and
                estado = 1 and estado_cerrado <> 2 and saldo <> 0;"
            );



        return Datatables::of($cuentas)
                ->addColumn('acciones', function ($cuenta) {

                    if (Auth::user()->rol_id == '2') {
                        return '<span class="badge badge-success">Sin Acciones</span>';
                    }else {
                        if ($cuenta->estadoCierre) {
                            return '<span class="badge badge-success">Factura cerrada</span>';
                        }else{

                            //dd($cuenta);
                            if ($cuenta->retencion_aplicada == 0) {
                                return
                                    '
                                        <div class="btn-group">
                                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                                            <ul class="dropdown-menu" x-placement="bottom-start"
                                                style="position: absolute; top: 33px; left: 0px; will-change: top, left;">


                                                <li>
                                                    <a class="dropdown-item" href="/detalle/venta/'.$cuenta->idFactura.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalRetencion('.$cuenta->codigoPago.' , '.$cuenta->isv.', '.$cuenta->estadoRetencion.', '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.')">  <i class="fa-solid fa-cash-register text-success"></i> Gestionar retencion </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalNotaCredito('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.', '.$cuenta->tieneNC.')"> <i class="fa-solid fa-cash-register text-success"></i> Notas de credito </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalNotaDebito('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.', '.$cuenta->tieneND.')"> <i class="fa-solid fa-cash-register text-success"></i> Notas de debito </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalOtrosMovimientos('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.')"> <i class="fa-solid fa-cash-register text-success"></i> Otros movimientos </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalAbonos('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.')"> <i class="fa-solid fa-cash-register text-success"></i> Creditos/Pago </a>
                                                </li>



                                            </ul>
                                        </div>
                                ';
                            }else{

                                return
                                    '
                                        <div class="btn-group">
                                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                                            <ul class="dropdown-menu" x-placement="bottom-start"
                                                style="position: absolute; top: 33px; left: 0px; will-change: top, left;">


                                                <li>
                                                    <a class="dropdown-item" href="/detalle/venta/'.$cuenta->idFactura.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" >  <i class="fa-solid fa-check text-success"></i> Retencion Gestionada </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalNotaCredito('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.', '.$cuenta->tieneNC.')"> <i class="fa-solid fa-cash-register text-success"></i> Notas de credito </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalNotaDebito('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.', '.$cuenta->tieneND.')"> <i class="fa-solid fa-cash-register text-success"></i> Notas de debito </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalOtrosMovimientos('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.')"> <i class="fa-solid fa-cash-register text-success"></i> Otros movimientos </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" onclick="modalAbonos('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.')"> <i class="fa-solid fa-cash-register text-success"></i> Creditos/Pago </a>
                                                </li>



                                            </ul>
                                        </div>
                                ';
                            }
                        }
                    }

                })


                ->rawColumns(['acciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarMovimientos($id){
        try{

            $consulta = DB::select("

            select
            ot.id as 'codigoMovimiento',
            ot.aplicacion_pagos_id as 'codigoPago',
            (select cai from factura where id = ot.factura_id) as correlativo,
            FORMAT(ot.monto, 2) as monto,
            ot.tipo_movimiento,
            ot.comentario,
            ot.estado as estadoMov,
            (select name from users where id = ot.usr_registro) as userRegistro,
            ot.created_at as fechaRegistro,
            ot.factura_id
                from otros_movimientos ot
                inner join aplicacion_pagos ap on ap.id = ot.aplicacion_pagos_id
                where
                ap.cliente_id = ".$id."
                and ap.estado = 1
                and ot.estado = 1
                ;"
            );



        return Datatables::of($consulta)
                ->addColumn('acciones', function ($consulta) {


                    return
                        '
                                <span class="badge badge-info">Sin Acciones</span>
                        ';
                })


                ->rawColumns(['acciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarAbonos($id){
        try{

            $consulta = DB::select("

            select
            ac.id as 'codigoAbono',
            ac.aplicacion_pagos_id as 'codigoPago',
            (select cai from factura where id = ac.factura_id) as correlativo,
            FORMAT(ac.monto_abonado, 2) as monto,
            ac.comentario as 'comentarioabono',
            ac.estado_abono as 'estadoAbono',
            (select name from users where id = ac.usr_registro) as 'userRegistro',
            ac.created_at as 'fechaRegistro',
            ac.factura_id
                from abonos_creditos ac
                inner join aplicacion_pagos ap on ap.id = ac.aplicacion_pagos_id
                where
                ap.cliente_id = ".$id."
                and ap.estado = 1
                and ac.estado_abono = 1
                ;"
            );



        return Datatables::of($consulta)
                ->addColumn('acciones', function ($consulta) {

                    return
                        '
                                <span class="badge badge-info">Sin Acciones</span>
                        ';
                })


                ->rawColumns(['acciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarNotasCredito($idFactura){

        try {
                $notasCredito = DB::select("
                    select
                    id as 'idNotaCredito',
                    cai as 'correlativo'
                    from nota_credito where estado_rebajado = 2 and estado_nota_id = 1 and factura_id =
                ".$idFactura);
            return response()->json([
                'results'=>$notasCredito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    public function datosNotasCredito($idNotaCredito){

        try {
                $notaCredito = DB::select("
                    select
                    comentario,
                    total AS total,
                    estado_rebajado
                    from nota_credito where id =
                ".$idNotaCredito);
            return response()->json([
                'result'=>$notaCredito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    public function listarNotasDebito($idFactura){



        try {
                    $notasDebito = DB::select("
                    select
                    id as 'idNotaDebito',
                    numeroCai as 'correlativo'
                    from notadebito where estado_sumado = 2 and  estado_id = 1 and factura_id =
                ".$idFactura);
            return response()->json([
                'results'=>$notasDebito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    public function datosNotasDebito($idNotaDebito){

        try {
                $notaDebito = DB::select("
                    select
                    motivoDescripcion AS 'comentario',
                    monto_asignado AS 'total',
                    estado_sumado
                    from notadebito where id =
                ".$idNotaDebito);
            return response()->json([
                'result'=>$notaDebito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////!SECTION
    ///////////////////////////////GESTIONES DE RETENCION DE ISV

    public function gestionRetencion( Request $request){

        try {


                         $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '4',
                            '0',
                            '".Auth::user()->id."',
                            '".$request->idFacturaRetencion."',
                            '".$request->comentario_retencion."',
                            '".$request->codAplicPago."',
                            '".$request->selectTiporetencion."',
                            '".$request->montoRetencion."',
                            @estado, @msjResultado);");


                        //dd($cuentas2[0]->estado);

                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error al insertar facturas en aplicacion de pagos.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }

   ///////////////////////////////GESTIONES DE notas nde credito

    public function gestionNC( Request $request){

        //dd($request);

        try {


                        $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '5',
                            '".$request->selectNotaCredito."',
                            '".Auth::user()->id."',
                            '".$request->idFacturaNC."',
                            '".$request->comentarioRebaja."',
                            '".$request->codAplicPagonc."',
                            '".$request->selectAplicado."',
                            '".$request->totalNotaCredito."',
                            @estado, @msjResultado);");


                        //dd($cuentas2[0]->estado);

                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }



   ///////////////////////////////GESTIONES DE notas nde debito

    public function gestionND( Request $request){

       // dd($request);

        try {


                        $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '6',
                            '".$request->selectNotaDebito."',
                            '".Auth::user()->id."',
                            '".$request->idFacturaND."',
                            '".$request->comentarioSuma."',
                            '".$request->codAplicPagond."',
                            '".$request->selectAplicadond."',
                            '".$request->totalNotaDebito."',
                            @estado, @msjResultado);");


                        //dd($cuentas2[0]->estado);

                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }


    ///////////////////////////////GESTIONES DE OTRO MOVIMIENTO

    public function guardarOtroMov( Request $request){


        try {
            $cm = "'";

                        $otrosMovimientos = new Modelotros_movimientos;
                            $otrosMovimientos->aplicacion_pagos_id = $request->codAplicPagoom;
                            $otrosMovimientos->factura_id = $request->idFacturaom;
                            $otrosMovimientos->monto = $request->montoTM;
                            $otrosMovimientos->comentario = $request->motivoMovimiento;
                            $otrosMovimientos->usr_registro = Auth::user()->id;
                            $otrosMovimientos->estado = 1;
                            $otrosMovimientos->tipo_movimiento = $request->selecttipoMovimiento;
                        $otrosMovimientos->save();



                        $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '7',
                            '0',
                            '".Auth::user()->id."',
                            '".$request->idFacturaom."',
                            '".$request->motivoMovimiento."',
                            '".$request->codAplicPagoom."',
                            '".$request->selecttipoMovimiento."',
                            '".$request->montoTM."',
                            @estado, @msjResultado);");


                        if ($request->selecttipoMovimiento=2) {


                            $cliente = DB::SELECTONE("select cliente_id from factura where id=".$request->idFacturaom);
                            $creditoCli = DB::SELECTONE("select credito from cliente where id=".$cliente->cliente_id);


                            $homologoCredito = $creditoCli->credito + $request->montoAbono;

                            $clienteCredito =  ModelCliente::find($cliente->cliente_id);
                            $clienteCredito->credito = trim($homologoCredito);
                            $clienteCredito->save();

                        }

                        //dd($cuentas2[0]->estado);


                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

                       $saldoActual2 = DB::selectone("select saldo from aplicacion_pagos where id = ".$request->codAplicPagoom);

                      // dd($request);
                       if($saldoActual2->saldo == 0){
                            //dd("Prueba de que llega aqui esta mierda");
                           $cuentas22 = DB::select("
                               CALL sp_aplicacion_pagos(
                                   '9',
                                   '0',
                                   '".Auth::user()->id."',
                                   '0',
                                   'CIERRE POR SALDO 0',
                                   '".$request->codAplicPagoAbono."',
                                   '0',
                                   '0',
                                   @estado,
                                   @msjResultado);");

                           if ($cuentas22[0]->estado == -1) {
                               return response()->json([
                                   "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                                   "icon" => "error",
                                   "title"=>"Error!"
                               ],402);
                           }

                           /* Me sale más facil procesarlo aqui */

                           /* Distribución de facturas comision */

                       }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }

    ///////////////////////////////GESTIONES DE creditos y abonos

    public function guardarCreditos( Request $request){

        //dd($request);

       try {
            $cm = "'";
            $name = '';
            $path = '';




            $saldoActual = DB::selectone('select saldo from aplicacion_pagos where id = '.$request->codAplicPagoAbono);

            if($request->montoAbono > $saldoActual->saldo){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"No se puede registrar un monto mayor al saldo actual.",
                    "title"=>"Advertencia!"

                ],400);

            }

                        $file = $request->file('doc_pago');
                        if($file != NULL){

                            $name = 'doc_'. time()."-". '.' . $file->getClientOriginalExtension();
                            $path = public_path() . '/documentos_aplicacion_pagos';
                            $file->move($path, $name);
                        }else{
                            $name = '';
                        }

                       $abonos = new Modelabonos_creditos;
                        $abonos->aplicacion_pagos_id = $request->codAplicPagoAbono;
                        $abonos->factura_id = $request->idFacturaAbono;
                        $abonos->banco_id = $request->selectBanco;
                        $abonos->estado_abono= 1;
                        $abonos->id_tipo_pago_cobro= $request->selectMetodoPago;
                        $abonos->monto_abonado = $request->montoAbono;
                        $abonos->usr_registro = Auth::user()->id;
                        $abonos->comentario = $request->comentarioAbono;
                        $abonos->url_documento = $path;
                        $abonos->fecha_pago = $request->fecha_pago;

                       $abonos->save();

                       $cuentas2 = DB::select("

                       CALL sp_aplicacion_pagos(
                           '8',
                           '0',
                           '".Auth::user()->id."',
                           '".$request->idFacturaAbono."',
                           '.$request->comentarioAbono.',
                           '".$request->codAplicPagoAbono."',
                           '0',
                           '".$request->montoAbono."',
                           @estado, @msjResultado);");


                       //dd($cuentas2[0]->estado);

                       $cliente = DB::SELECTONE("select cliente_id from factura where id=".$request->idFacturaAbono);

                       $creditoCli = DB::SELECTONE("select credito_inicial, credito, cliente_categoria_escala_id from cliente where id=".$cliente->cliente_id);

                       if ($creditoCli->credito_inicial !=0) {
                        $homologoCredito = $creditoCli->credito + $request->montoAbono;

                        $clienteCredito =  ModelCliente::find($cliente->cliente_id);
                        $clienteCredito->credito = trim($homologoCredito);
                        $clienteCredito->save();
                       }


                       if ($cuentas2[0]->estado == -1) {
                           return response()->json([
                               "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                               "icon" => "error",
                               "title"=>"Error!"
                           ],402);
                       }


                       $saldoActual2 = DB::selectone('select saldo from aplicacion_pagos where id = '.$request->codAplicPagoAbono);

                       if($saldoActual2->saldo == 0){
                            //dd("Prueba de que llega aqui esta mierda");
                           $cuentas22 = DB::select("

                               CALL sp_aplicacion_pagos(
                                   '9',
                                   '0',
                                   '".Auth::user()->id."',
                                   '0',
                                   'CIERRE POR SALDO 0',
                                   '".$request->codAplicPagoAbono."',
                                   '0',
                                   '0',
                                   @estado,
                                   @msjResultado);");

                             //gestionComision($creditoCli->cliente_categoria_escala_id,$request->idFacturaAbono.$request->codAplicPagoAbono);


                                                              /*Ejecución de Llenado de facturas comisión*/
                            $arrayfacturas_comision = [];
                            $arrayproducto_comision = [];

                           $parametros_comision = DB::SELECT("select * from comision_escala where estado_id = 1 and cliente_categoria_escala_id = ". $creditoCli->cliente_categoria_escala_id);
                           $productos_factura = DB::SELECT(" select * from venta_has_producto where factura_id = ".$request->idFacturaAbono);
                           /* recorriendo los parametros para comisionar de ese cliente, en esta factura */


                           $monto_rol_factura = 0;
                           foreach ($parametros_comision as $param) {

                                // Aquí accedés a cada campo del registro
                                $comision_escala_id     = $param->id;
                                $rol_id                 = $param->rol_id;
                                $porcentaje_comision    = $param->porcentaje_comision;

                                foreach ($productos_factura as $producto) {
                                    $cantidad = $producto->cantidad;
                                    $precio_venta = $producto->precio_unidad;
                                    $precios_producto_carga_id  = $producto->precios_producto_carga_id;
                                    $idproducto =  $producto->producto_id;
                                    $monto_comision = ((($porcentaje_comision/100) * $precio_venta));
                                    array_push($arrayproducto_comision, [
                                        "cantidad" => $cantidad,
                                        "precio_venta" => $precio_venta,
                                        "monto_comision" => $monto_comision,
                                        "precios_producto_carga_id" => $precios_producto_carga_id,
                                        "factura_id" => $request->idFacturaAbono,
                                        "producto_id" => $idproducto,
                                        "rol_id" => $rol_id,
                                        "estado_id" => 1,
                                        "created_at" => NOW(),
                                        "updated_at" => NOW()

                                    ]);
                                }
                                /*Inserto todos los productos según yo */


                                array_push($arrayfacturas_comision, [
                                    "fecha_cierre_factura" => NOW(),
                                    "monto_rol" => 0,
                                    "factura_id" => $request->idFacturaAbono,
                                    "comision_escala_id" => $comision_escala_id,
                                    "aplicacion_pagos_id" => $request->codAplicPagoAbono,
                                    "rol_id" => $rol_id,
                                    "estado_id" => 1
                                ]);



                            }
                                $totalesPorRol = [];
                                foreach ($arrayproducto_comision as $p) {
                                    // soporta tanto arrays asociativos como objetos stdClass
                                    $rol = isset($p['rol_id']) ? $p['rol_id'] : (isset($p->rol_id) ? $p->rol_id : null);
                                    $monto = isset($p['monto_comision']) ? $p['monto_comision'] : (isset($p->monto_comision) ? $p->monto_comision : 0);

                                    if ($rol === null) continue;

                                    // forzamos a float por seguridad
                                    $monto = (float) $monto;

                                    if (!isset($totalesPorRol[$rol])) $totalesPorRol[$rol] = 0.0;
                                    $totalesPorRol[$rol] += $monto;
                                }

                                // 2) Actualizar $arrayfacturas_comision usando los totales por rol
                                // (se asume que cada elemento tiene 'rol_id' y queremos setear/actualizar 'monto_rol')
                                foreach ($arrayfacturas_comision as &$facturaRol) {

                                $rol = $facturaRol['rol_id'];
                                $totalRol = 0;

                                foreach ($arrayproducto_comision as $prod) {

                                    if ($prod['rol_id'] == $rol) {
                                        // multiplicar monto * cantidad ANTES de sumar
                                        $totalRol += ($prod['monto_comision'] * $prod['cantidad']);
                                    }

                                }

                                // asignar el total calculado
                                $facturaRol['monto_rol'] = $totalRol;
                            }
                            unset($facturaRol);


                                modelproducto_comision::insert($arrayproducto_comision);
                                modelfacturas_comision::insert($arrayfacturas_comision);

                                /*recuperar factura, vendedor y teleoperacior del id factura*/


                                $datos_factura = DB::SELECTONE("select users_id as 'teleoperador', vendedor from factura where id =".$request->idFacturaAbono);

                                    /*Variables constantes porque es la estructura en duro de cualquier factura */

                                    $idTeleoperador = $datos_factura->teleoperador;
                                    $idVendedor = $datos_factura->vendedor;
                                foreach ($arrayfacturas_comision as $factura) {

                                    //----------------------------------------TELEOPERADOR
                                    if ($factura['rol_id'] == 3 ) {

                                        $com_empleado_teleoperador = DB::SELECT("select *
                                        from comision_empleado where estado_id = 1
                                        and users_comision = ".$idTeleoperador);
                                         dd($com_empleado_teleoperador);
                                        $nuevacomision = ($com_empleado_teleoperador->comision_acumulada + $factura['monto_rol']);

                                        modelcomision_empleado::where('id', $com_empleado_teleoperador->id)
                                        ->update([
                                            'comision_acumulada'      => $nuevacomision,
                                            'fecha_ult_modificacion'  => now()
                                        ]);
                                    }

                                    //----------------------------------------VENDEDOR
                                   /*  if ($factura['rol_id'] == 2 ) {

                                        $com_empleado = DB::SELECT("select * from comision_empleado where estado_id = 1 and users_comision = ".$idVendedor." and mes_comision = ".$factura['fecha_cierre_factura']);


                                        $nuevacomision = ($com_empleado->comision_acumulada + $factura['monto_rol']);

                                        modelcomision_empleado::where('users_comision', $com_empleado->users_comision)
                                        ->where('rol_id', $com_empleado->rol_id)
                                        ->update([
                                            'comision_acumulada'      => $nuevacomision,
                                            'fecha_ult_modificacion'  => now()
                                        ]);
                                    } */

                                    //---------------------------------------RESTO DE COMISIONES PARAMETRIZADAS A EXCEPCIÓN DE LOGISTICA Y EQUIPO DE ENTREGA



                                 /*    $com_empleado = DB::SELECT("select * from comision_empleado where estado_id = 1 and rol_id not in (2,3) and mes_comision = ".$factura['fecha_cierre_factura']);

                                    if ($factura['rol_id'] = $com_empleado->rol_id ) {
                                        $nuevacomision = ($com_empleado->comision_acumulada + $factura['monto_rol']);

                                        modelcomision_empleado::where('users_comision', $com_empleado->users_comision)
                                        ->where('rol_id', $com_empleado->rol_id)
                                        ->update([
                                            'comision_acumulada'      => $nuevacomision,
                                            'fecha_ult_modificacion'  => now()
                                        ]);

                                    } */
                                }



                                modelcomision_empleado::where('users_comision', $userId)
                                ->where('rol_id', $rolId)
                                ->update([
                                    'comision_acumulada'      => $nuevacomision,
                                    'fecha_ult_modificacion'  => now(),
                                    'mes_comision'            => $mesComision,
                                    'nombre_empleado'         => $nombreEmpleado,
                                    'estado_id'               => 1
                                ]);

                           if ($cuentas22[0]->estado == -1) {

                               return response()->json([
                                   "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                                   "icon" => "error",
                                   "title"=>"Error!"
                               ],402);
                           }



                       }


           }catch (QueryException $e) {

           return response()->json([
               "icon" => "error",
               "text" => "Ha ocurrido un error: ".$e,
               "title"=>"Error!",
               "error" => $e
           ],402);
       }

    }

    public function gestionComision($cliente_categoria_escala_id,$idFacturaAbono ,$codAplicPagoAbono){
        try{
            $arrayfacturas_comision = [];
            $arrayproducto_comision = [];

            $parametros_comision = DB::SELECT("select * from comision_escala where estado_id = 1 and cliente_categoria_escala_id = ". $cliente_categoria_escala_id);
            $productos_factura = DB::SELECT(" select * from venta_has_producto where factura_id = ".$idFacturaAbono);
                            /* recorriendo los parametros para comisionar de ese cliente, en esta factura */
            $monto_rol_factura = 0;
            foreach ($parametros_comision as $param) {

                // Aquí accedés a cada campo del registro
                $comision_escala_id     = $param->id;
                $rol_id                 = $param->rol_id;
                $porcentaje_comision    = $param->porcentaje_comision;

                foreach ($productos_factura as $producto) {
                    $precio_venta = $producto->precio_unidad;
                    $cantidad = $producto->cantidad;
                    $idproducto =  $producto->producto_id;
                    $precios_producto_carga_id  = $producto->precios_producto_carga_id;
                    $monto_comision = ((($porcentaje_comision/100) * $precio_venta));
                    array_push($arrayproducto_comision, [
                        "cantidad" => $cantidad,
                        "precio_venta" => $precio_venta,
                        "monto_comision" => $monto_comision,
                        "precios_producto_carga_id" => $precios_producto_carga_id,
                        "producto_id" => $idproducto,
                        "rol_id" => $rol_id,
                        "estado_id" => 1,
                        "created_at" => NOW(),
                        "updated_at" => NOW()

                    ]);
                }
                    /*Inserto todos los productos según yo */


                array_push($arrayfacturas_comision, [
                    "fecha_cierre_factura" => NOW(),
                    "monto_rol" => 0,
                    "factura_id" => $idFacturaAbono,
                    "comision_escala_id" => $comision_escala_id,
                    "aplicacion_pagos_id" => $codAplicPagoAbono,
                    "rol_id" => $rol_id,
                    "estado_id" => 1
                ]);



            }
            $totalesPorRol = [];
            foreach ($arrayproducto_comision as $p) {
                // soporta tanto arrays asociativos como objetos stdClass
                $rol = isset($p['rol_id']) ? $p['rol_id'] : (isset($p->rol_id) ? $p->rol_id : null);
                $monto = isset($p['monto_comision']) ? $p['monto_comision'] : (isset($p->monto_comision) ? $p->monto_comision : 0);

                if ($rol === null) continue;

                // forzamos a float por seguridad
                $monto = (float) $monto;

                if (!isset($totalesPorRol[$rol])) $totalesPorRol[$rol] = 0.0;
                $totalesPorRol[$rol] += $monto;
            }

                // 2) Actualizar $arrayfacturas_comision usando los totales por rol
                // (se asume que cada elemento tiene 'rol_id' y queremos setear/actualizar 'monto_rol')
            foreach ($arrayfacturas_comision as &$facturaRol) {

                $rol = $facturaRol['rol_id'];
                $totalRol = 0;

                foreach ($arrayproducto_comision as $prod) {

                    if ($prod['rol_id'] == $rol) {
                        // multiplicar monto * cantidad ANTES de sumar
                        $totalRol += ($prod['monto_comision'] * $prod['cantidad']);
                    }

                }

                // asignar el total calculado
                $facturaRol['monto_rol'] = $totalRol;
            }
            unset($facturaRol);

            modelproducto_comision::insert($arrayproducto_comision);
            modelfacturas_comision::insert($arrayfacturas_comision);

            /*recuperar factura, vendedor y teleoperacior del id factura*/

            $datos_factura = DB::SELECTONE("select user_id as 'teleoperador', vendedor from factura where id = ".$idFacturaAbono);

        }catch (QueryException $e) {

           return response()->json([
               "icon" => "error",
               "text" => "Ha ocurrido un error: ".$e,
               "title"=>"Error!",
               "error" => $e
           ],402);
       }

    }

    public function datosBanco(){
        try {
            $bancos = DB::select("
                select CONCAT(nombre, ' - ', cuenta) as banco, id as idBanco from banco;
            ");
            return response()->json([
                'result'=>$bancos,
            ],200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al buscar bancos',
                'error' => $e
            ],402);
        }
    }

    public function cerrarFactura(Request $request){
        try {

            $revision = DB::SELECTONE("
            select aplicacion_pagos.saldo as saldo
            from aplicacion_pagos
            where aplicacion_pagos.estado <> 1
            and aplicacion_pagos.id =
            ".$request->codAplicCierre);
            if ( !is_null($revision)) {
                if ($revision->saldo > 0 ) {
                    return response()->json([
                        "text" => "No es posible cerrar la factura, Saldo del estado de cuenta, no es 0.",
                        "icon" => "error",
                        "title"=>"Error!"
                    ],402);
                }
            }


            $cuentas2 = DB::select("

            CALL sp_aplicacion_pagos(
                '9',
                '0',
                '".Auth::user()->id."',
                '0',
                '".$request->comentarioCierre."',
                '".$request->codAplicCierre."',
                '0',
                '0',
                @estado,
                @msjResultado);");


            //dd($cuentas2[0]->estado);

            if ($cuentas2[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],402);
            }


        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al cerrar la factura.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    public function imprimirEstadoCuenta($idClientepdf){
        $estadoCuenta = DB::select("CALL estadoCuenta_sp('".$idClientepdf."');");
        // dd($estadoCuenta[0]->cliente);
        $pdf = PDF::loadView('/pdf/estadocuentaAplicacion', compact('estadoCuenta'))->setPaper('letter')->setPaper("A4", "landscape");

        return $pdf->stream("ESTADO_CUENTA.pdf");
    }




    }
