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

use Illuminate\Support\Facades\File;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CuentasPorCobrarExport;
use App\Exports\CuentasPorCobrarInteresExport;
use App\Models\AplicacionPagos\Modelotros_movimientos;
use App\Models\AplicacionPagos\Modelabonos_creditos;
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
                estado = 1 and estado_cerrado <> 2;"
            );



        return Datatables::of($cuentas)
                ->addColumn('acciones', function ($cuenta) {



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


                                            <li>
                                                <a class="dropdown-item" onclick="modalcerrarFactura('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.')"> <i class="fa-solid fa-shield text-success"></i> Cerrar Factura </a>
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

                                            <li>
                                                <a class="dropdown-item" onclick="modalcerrarFactura('.$cuenta->codigoPago.' , '."'".$cuenta->codigoFactura."'".', '.$cuenta->idFactura.')"> <i class="fa-solid fa-shield text-success"></i> Cerrar Factura </a>
                                            </li>


                                        </ul>
                                    </div>
                            ';
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

         //dd($request);

        try {
            $cm = "'";

                        $otrosMovimientos = new Modelotros_movimientos;
                            $otrosMovimientos->aplicacion_pagos_id = $request->codAplicPagoom;
                            $otrosMovimientos->factura_id = $request->idFacturaom;
                            $otrosMovimientos->monto = $request->montoTM;
                            $otrosMovimientos->comentario = $cm.$request->motivoMovimiento.$cm;
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


                        //dd($cuentas2[0]->estado);

                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error en el procedimiento almacenado.",
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

    ///////////////////////////////GESTIONES DE creditos y abonos

    public function guardarCreditos( Request $request){

        //dd($request);

       try {
            $cm = "'";
            $name = '';
            $path = '';




            $saldoActual = DB::selectone('select saldo from aplicacion_pagos where id = '.$request->codAplicPagoAbono);
            // dd($saldoActual->saldo);
            if($saldoActual->saldo == 0){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"El Saldo de esta factura ya esta cobrado.",
                    "title"=>"Advertencia!"

                ],400);

            }

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
                        $abonos->monto_abonado = $request->montoAbono;
                        $abonos->usr_registro = Auth::user()->id;
                        $abonos->comentario = $cm.$request->comentarioAbono.$cm;
                        $abonos->url_documento = $path;
                        $abonos->fecha_pago = $request->fecha_pago;

                       $abonos->save();

                       $cuentas2 = DB::select("

                       CALL sp_aplicacion_pagos(
                           '8',
                           '0',
                           '".Auth::user()->id."',
                           '".$request->idFacturaAbono."',
                           '".$request->comentarioAbono."',
                           '".$request->codAplicPagoAbono."',
                           '0',
                           '".$request->montoAbono."',
                           @estado, @msjResultado);");


                       //dd($cuentas2[0]->estado);

                       if ($cuentas2[0]->estado == -1) {
                           return response()->json([
                               "text" => "Ha ocurrido un error en el procedimiento almacenado.",
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
