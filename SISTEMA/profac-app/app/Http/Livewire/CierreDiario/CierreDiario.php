<?php

namespace App\Http\Livewire\CierreDiario;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
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

use App\Models\CierreDiario as ModelCierreDiario;
use App\Models\bitacoraCierre;
use App\Models\tipo_cobro_cierre;


class CierreDiario extends Component
{
    public function render(){
        return view('livewire.cierre-diario.cierre-diario');
    }

    public function contado($fecha){
        try {

                //dd($fecha);
                /*CAMBIO 20230725 FORMAT(VALOR,4)*/
            $consulta = DB::SELECT("
                    select
                    A.created_at as 'fecha',
                    A.id as 'codigofactura',
                    (
                        IF  (
                             (
                                SELECT COUNT(*) FROM numero_orden_compra WHERE id = A.numero_orden_compra_id
                             ) = 0
                             , 'N/A'
                             ,(SELECT numero_orden FROM numero_orden_compra WHERE id = A.numero_orden_compra_id)

                            )

                        ) as 'numOrden',
                    A.cai as 'factura',
                    A.nombre_cliente as 'cliente',
                    (select name from users where id = A.vendedor) as 'vendedor',
                    FORMAT(A.sub_total,2) as 'subtotal',
                    IF(FORMAT(A.sub_total,2) = FORMAT(A.total,2), 0.00,FORMAT(A.isv,2)) as 'imp_venta',
                    FORMAT(A.total,2) as 'total',
                    (CASE A.estado_factura_id WHEN '1' THEN 'CLIENTE A' WHEN '2' THEN 'CLIENTE B' END) AS 'tipo',
                    'CONTADO' AS 'tipoFactura',
                IF(
                    (select COUNT(*) from tipo_cobro_cierre where idfactura = A.id) = 0, 'SIN ASIGNAR', (select tipo_cobro_cierre.textoCobro from tipo_cobro_cierre where idfactura = A.id AND estado = 1)
                    ) as 'PagoMediante'
                from factura A
                    inner join estado_venta B on A.estado_venta_id = B.id
                    inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                where
                    B.id = 1
                    and C.id = 1
                    and A.estado_venta_id = 1
                            and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                ");

               // dd($consulta);
            return Datatables::of($consulta)
            ->addColumn('acciones', function ($consulta) {
                $comillas = "'";
                //dd($consulta->factura);
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" onclick="cargarInputFactura('.$consulta->codigofactura.','.$comillas.''.$consulta->factura.''.$comillas.','.$comillas.''.$consulta->PagoMediante.''.$comillas.' )" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Ver Desglose </a>
                            </li>

                        </ul>
                    </div>';
            })
            ->rawColumns(['acciones'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e,
            ], 402);

        }




    }

    public function credito($fecha){
        try {
            /*CAMBIO 20230725 FORMAT(VALOR,4)*/
            $consulta = DB::SELECT("
                select
                    A.created_at as 'fecha',
                    A.id as 'codigofactura',
                    A.cai as 'factura',
                    A.nombre_cliente as 'cliente',
                    (select name from users where id = A.vendedor) as 'vendedor',
                    FORMAT(A.sub_total,2) as 'subtotal',
                    IF(FORMAT(A.sub_total,2) = FORMAT(A.total,2), 0.00, FORMAT(A.isv,2)) as 'imp_venta',
                    FORMAT(A.total,4) as 'total',
                    (CASE A.estado_factura_id WHEN '1' THEN 'CLIENTE A' WHEN '2' THEN 'CLIENTE B' END) AS 'tipo',
                    'CREDITO' AS 'tipoFactura'
                from factura A
                    inner join estado_venta B on A.estado_venta_id = B.id
                    inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                where
                    B.id = 1
                    and C.id = 2
                    and A.estado_venta_id = 1
                    and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                ");


            return Datatables::of($consulta)
            ->rawColumns([])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e,
            ], 402);

        }

    }

    public function anuladas($fecha){
        try {
            /*CAMBIO 20230725 FORMAT(VALOR,4)*/
            $consulta = DB::SELECT("
                select
                    A.created_at as 'fecha',
                    A.id as 'codigofactura',
                    A.cai as 'factura',
                    A.nombre_cliente as 'cliente',
                    (select name from users where id = A.vendedor) as 'vendedor',
                    FORMAT(A.sub_total,2) as 'subtotal',
                    IF(FORMAT(A.sub_total,2) = FORMAT(A.total,2), 0.00, FORMAT(A.isv,2)) as 'imp_venta',
                    FORMAT(A.total,4) as 'total',
                    (CASE A.estado_factura_id WHEN '1' THEN 'CLIENTE A' WHEN '2' THEN 'CLIENTE B' END) AS 'tipo',
                    'ANULADAS' AS 'tipoFactura'
                from factura A
                    inner join estado_venta B on A.estado_venta_id = B.id
                    inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                where
                    B.id = 2
                    and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                ");



            return Datatables::of($consulta)
            ->rawColumns([])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e,
            ], 402);

        }

    }

    public function cargaTotales($fecha){

        /*CAMBIO 20230725 FORMAT(VALOR,4)*/

        $anuladoTotal = DB::SELECTONE("
            select
                IF(SUM(A.total) IS NULL, 0.00, SUM(A.total)) as 'sumaTotalanulado'
            from factura A
                inner join estado_venta B on A.estado_venta_id = B.id
                inner join tipo_pago_venta C on A.tipo_pago_id = C.id
            where
                B.id = 1
                and A.estado_venta_id = 2
                and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');"
        );

        $creditoTotal = DB::SELECTONE("
                select
                    IF(SUM(A.total) IS NULL, 0.00, SUM(A.total)) as 'sumaTotalcredito'
                from factura A
                    inner join estado_venta B on A.estado_venta_id = B.id
                    inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                where
                    B.id = 1
                    and C.id = 2
                    and A.estado_venta_id = 1
                    and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                "
        );

        $contadoTotal = DB::SELECTONE("
                select
                    IF(SUM(A.total) IS NULL, 0.00, SUM(A.total)) as 'sumaTotalcontado'
                from factura A
                    inner join estado_venta B on A.estado_venta_id = B.id
                    inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                where
                    B.id = 1
                    and C.id = 1
                    and A.estado_venta_id = 1
                    and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                "
        );

        $existencia = DB::SELECTONE("
                select
                    count(*) as existe
                from bitacoracierre
                where
                    DATE(bitacoracierre.fechaCierre) = DATE_FORMAT('".$fecha."', '%Y-%m-%d')
                    AND bitacoracierre.estado_cierre = 1;
                "
        );


        $totalFacturado = $contadoTotal->sumaTotalcontado +$creditoTotal->sumaTotalcredito;
        return response()->json([
            'totalContado' => number_format($contadoTotal->sumaTotalcontado, 2, '.', ','),
            'totalCredito' => number_format($creditoTotal->sumaTotalcredito, 2, '.', ','),
            'totalAnulado' => number_format($totalFacturado, 2, '.', ','),
            'banderaCierre'=> $existencia->existe
        ], 200);
    }

    public function guardarCierre($fecha, Request $request){
        try {

            $existencia = DB::SELECTONE("
                select
                    count(*) as existe
                from bitacoracierre
                where
                    DATE(bitacoracierre.fechaCierre) = DATE_FORMAT('".$fecha."', '%Y-%m-%d')
                    AND bitacoracierre.estado_cierre = 1;
                "
            );

            if ($existencia->existe > 0) {
                return response()->json([
                    "icon" => "error",
                    "text" => "Existen registros de cierre de caja para la fecha ".$fecha,
                    "title"=>"Error!",
                    "error" => $e
                ],402);
            }

            if ($existencia->existe === 0) {
                $userName1 = Auth::user()->name;
                $estadoDescripcion1 = 'CERRADO';

                DB::beginTransaction();

                    $fbitacoraCierre = new bitacoraCierre;
                            $fbitacoraCierre->fechaCierre =  $fecha;
                            $fbitacoraCierre->user_cierre_id = Auth::user()->id;
                            $fbitacoraCierre->comentario = $request->comentario ;
                            $fbitacoraCierre->estado_cierre= 1 ;
                            $fbitacoraCierre->totalContado = $request->inputTotalContado ;
                            $fbitacoraCierre->totalCredito = $request->inputTotalCredito ;
                            $fbitacoraCierre->totalAnulado = $request->inputTotalAnulado ;
                    $fbitacoraCierre->save();


                    /*CAMBIO 20230725 FORMAT(VALOR,4)*/
                    $contado = DB::SELECT("
                        select
                            A.created_at as 'fecha',
                            A.id as 'codigofactura',
                            A.cai as 'factura',
                            A.nombre_cliente as 'cliente',
                            (select name from users where id = A.vendedor) as 'vendedor',
                            FORMAT(A.sub_total,4) as 'subtotal',
                            IF(FORMAT(A.sub_total,4) = FORMAT(A.total,4), 0.00,FORMAT(A.isv,4)) as 'imp_venta',
                            FORMAT(A.total,4) as 'total',
                            (CASE A.estado_factura_id WHEN '1' THEN 'CLIENTE A' WHEN '2' THEN 'CLIENTE B' END) AS 'tipo',
                            'CONTADO' AS 'tipoFactura',
                        IF(
                            (select COUNT(*) from tipo_cobro_cierre where idfactura = A.id) = 0, 'SIN ASIGNAR', (select tipo_cobro_cierre.textoCobro from tipo_cobro_cierre where idfactura = A.id AND estado = 1)
                            ) as 'PagoMediante'
                        from factura A
                            inner join estado_venta B on A.estado_venta_id = B.id
                            inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                        where
                            B.id = 1
                            and C.id = 1
                            and A.estado_venta_id = 1
                            and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                    ");


                    $credito = DB::SELECT("
                        select
                            A.created_at as 'fecha',
                            A.id as 'codigofactura',
                            A.cai as 'factura',
                            A.nombre_cliente as 'cliente',
                            (select name from users where id = A.vendedor) as 'vendedor',
                            FORMAT(A.sub_total,4) as 'subtotal',
                            IF(FORMAT(A.sub_total,4) = FORMAT(A.total,4), 0.00, FORMAT(A.isv,4)) as 'imp_venta',
                            FORMAT(A.total,4) as 'total',
                            (CASE A.estado_factura_id WHEN '1' THEN 'CLIENTE A' WHEN '2' THEN 'CLIENTE B' END) AS 'tipo',
                            'CREDITO' AS 'tipoFactura'
                        from factura A
                            inner join estado_venta B on A.estado_venta_id = B.id
                            inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                        where
                            B.id = 1
                            and C.id = 2
                            and A.estado_venta_id = 1
                            and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                    ");


                    $anuladas = DB::SELECT("
                        select
                            A.created_at as 'fecha',
                            A.id as 'codigofactura',
                            A.cai as 'factura',
                            A.nombre_cliente as 'cliente',
                            (select name from users where id = A.vendedor) as 'vendedor',
                            FORMAT(A.sub_total,4) as 'subtotal',
                            IF(FORMAT(A.sub_total,4) = FORMAT(A.total,4), 0.00, FORMAT(A.isv,4)) as 'imp_venta',
                            FORMAT(A.total,4) as 'total',
                            (CASE A.estado_factura_id WHEN '1' THEN 'CLIENTE A' WHEN '2' THEN 'CLIENTE B' END) AS 'tipo',
                            'ANULADAS' AS 'tipoFactura'
                        from factura A
                            inner join estado_venta B on A.estado_venta_id = B.id
                            inner join tipo_pago_venta C on A.tipo_pago_id = C.id
                        where
                            B.id = 1
                            and A.estado_venta_id = 2
                            and DATE(A.created_at) = DATE_FORMAT('".$fecha."', '%Y-%m-%d');
                    ");



                    foreach ($contado as $value) {
                        $fcontado = new ModelCierreDiario;

                            $fcontado->user_cierre_id = Auth::user()->id ;
                            $fcontado->estado_cierre = 1 ;
                            $fcontado->nombre_userCierre = TRIM($userName1) ;
                            $fcontado->estadoDescripcion = TRIM($estadoDescripcion1) ;
                            $fcontado->fecha = TRIM($value->fecha) ;
                            $fcontado->idfactura= TRIM($value->codigofactura) ;
                            $fcontado->factura= TRIM($value->factura) ;
                            $fcontado->cliente = TRIM($value->cliente) ;
                            $fcontado->vendedor = TRIM($value->vendedor) ;
                            $fcontado->subtotal = $value->subtotal ;
                            $fcontado->imp_venta = $value->imp_venta;
                            $fcontado->total = $value->total ;
                            $fcontado->tipo = TRIM($value->tipo) ;
                            $fcontado->tipoFactura = TRIM($value->tipoFactura) ;
                            $fcontado->bitacoraCierre_id =  $fbitacoraCierre->id ;
                            $fcontado->textoCobro =  TRIM($value->PagoMediante) ;


                        $fcontado->save();

                    };

                    foreach ($credito as $valuecredito) {

                        $fcredito = new ModelCierreDiario;
                            $fcredito->user_cierre_id = Auth::user()->id ;
                            $fcredito->estado_cierre = 1 ;
                            $fcredito->nombre_userCierre = TRIM($userName1) ;
                            $fcredito->estadoDescripcion = TRIM($estadoDescripcion1) ;
                            $fcredito->fecha = TRIM($valuecredito->fecha) ;
                            $fcontado->idfactura= TRIM($valuecredito->codigofactura) ;
                            $fcredito->factura= TRIM($valuecredito->factura) ;
                            $fcredito->cliente = TRIM($valuecredito->cliente) ;
                            $fcredito->vendedor = TRIM($valuecredito->vendedor) ;
                            $fcredito->subtotal = $valuecredito->subtotal ;
                            $fcredito->imp_venta = $valuecredito->imp_venta ;
                            $fcredito->total = $valuecredito->total ;
                            $fcredito->tipo = TRIM($valuecredito->tipo) ;
                            $fcredito->tipoFactura = TRIM($valuecredito->tipoFactura) ;
                            $fcredito->bitacoraCierre_id =  $fbitacoraCierre->id ;
                        $fcredito->save();

                    };

                    foreach ($anuladas as $valueanuladas) {
                        $fanuladas = new ModelCierreDiario;

                            $fanuladas->user_cierre_id = Auth::user()->id ;
                            $fanuladas->estado_cierre = 1 ;
                            $fanuladas->nombre_userCierre = TRIM($userName1) ;
                            $fanuladas->estadoDescripcion = TRIM($estadoDescripcion1) ;
                            $fanuladas->fecha = TRIM($valueanuladas->fecha) ;
                            $fcontado->idfactura= TRIM($valueanuladas->codigofactura) ;
                            $fanuladas->factura= TRIM($valueanuladas->factura) ;
                            $fanuladas->cliente = TRIM($valueanuladas->cliente) ;
                            $fanuladas->vendedor = TRIM($valueanuladas->vendedor) ;
                            $fanuladas->subtotal = $valueanuladas->subtotal ;
                            $fanuladas->imp_venta = $valueanuladas->imp_venta ;
                            $fanuladas->total = $valueanuladas->total ;
                            $fanuladas->tipo = TRIM($valueanuladas->tipo) ;
                            $fanuladas->tipoFactura = TRIM($valueanuladas->tipoFactura) ;
                            $fanuladas->bitacoraCierre_id =  $fbitacoraCierre->id ;

                        $fanuladas->save();


                    };

                DB::commit();
                return response()->json([
                    "icon" => "success",
                    "text" => "Se realizó correctamente la transacción!",
                    "title"=>"Exito!"
                ],200);
            }

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al realizar la transacción.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    public function guardarTipoCobro(Request $request){


        $factura = "'".$request->inputFactura."'";
        //$select = "'".$request->selectTipoCierre."'";
        try {


             $existencia = DB::SELECTONE("
                    select
                        count(*) as existe
                    from tipo_cobro_cierre
                    where
                        idfactura = '".$request->inputFacturaCodigo."'
                        and
                        factura = ".$factura
                );
                //dd($existencia);
                if ($existencia->existe > 0) {
                    DB::table('tipo_cobro_cierre')
                    ->where('factura', $request->inputFactura)
                    ->where('idfactura', $request->inputFacturaCodigo)
                    ->update(['textoCobro' => $request->selectTipoCierre]);

                    return response()->json([
                        "icon" => "success",
                        "text" => "Actualizó con éxito el tipo de cobro!",
                        "title"=>"Exito!"
                    ],200);
                }else{
                    $tipoCierre = new tipo_cobro_cierre;
                    $tipoCierre->textoCobro = TRIM($request->selectTipoCierre);
                    $tipoCierre->fecha = TRIM($request->fechaCierreC);
                    $tipoCierre->idfactura = $request->inputFacturaCodigo;
                    $tipoCierre->factura = TRIM($request->inputFactura);
                    $tipoCierre->estado= 1 ;
                    $tipoCierre->user_registra_id = Auth::user()->id ;
                    $tipoCierre->save();
                    return response()->json([
                        "icon" => "success",
                        "text" => "Inserto con éxito el tipo de cobro!",
                        "title"=>"Exito!"
                    ],200);
                }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al realizar la transacción.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }
}
