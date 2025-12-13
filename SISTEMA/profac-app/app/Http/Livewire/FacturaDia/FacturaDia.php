<?php

namespace App\Http\Livewire\FacturaDia;

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

class FacturaDia extends Component
{
    public function render()
    {
        return view('livewire.factura-dia.factura-dia');
    }

    public function consulta($fecha_inicio, $fecha_final){
        try {



            $consulta = DB::SELECT("
            select
            A.created_at as 'fecha',
            (CASE DATE_FORMAT(A.created_at, '%m') WHEN '01' THEN 'ENERO' WHEN '02' THEN 'FEBRERO' WHEN '03' THEN 'MARZO' WHEN '04' THEN 'ABRIL' WHEN '05' THEN 'MAYO' WHEN '06' THEN 'JUNIO' WHEN '07' THEN 'JULIO' WHEN '08' THEN 'AGOSTO' WHEN '09' THEN 'SEPTIEMBRE' WHEN '10' THEN 'OCTUBRE' WHEN '11' THEN 'NOVIEMBRE' WHEN '12' THEN 'DICIEMBRE' END) AS 'mes',
            A.cai as 'factura',
            A.nombre_cliente as 'cliente',
            (select name from users where id = A.vendedor) as 'vendedor',
            format(A.sub_total,2) as 'subtotal',
            IF(A.sub_total = A.total, 0.00, format(A.isv,2)) as 'imp_venta',
            format(A.total,2) as 'total',
            CASE A.estado_factura_id WHEN 1 THEN 'CLIENTE A' WHEN 2 THEN 'CLIENTE B' END AS 'tipo'


            from factura A
            inner join estado_venta B
            on A.estado_venta_id = B.id
            inner join tipo_pago_venta C
            on A.tipo_pago_id = C.id
            where B.id = 1 and
            DATE(A.created_at) >= DATE_FORMAT('".$fecha_inicio."', '%Y-%m-%d') and  DATE(A.created_at) <= DATE_FORMAT('".$fecha_final."', '%Y-%m-%d');
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


    /*Borrador de consulta para el reporte de comisiones*/

    /*

                    select
                    date_format(A.created_at, "%d-%m-%Y") as 'FECHA',
                    date_format(A.fecha_vencimiento, "%d-%m-%Y") as 'FECHA VENCIMIENTO',
                    UPPER(tpv.descripcion) as 'CRÉDITO/CONTADO',
                    (
                        CASE A.estado_factura_id WHEN '1' THEN 'CLIENTE A' WHEN '2' THEN 'CLIENTE B' END
                    ) AS 'TIPO CLIENTE (AoB)',
                    UPPER(us.name) as 'VENDEDOR',
                    (
                        RIGHT(A.cai, 5)
                    ) as 'FACTURA',
                    cli.nombre as 'CLIENTE',
                    C.id as 'CÓDIGO',
                    C.nombre as 'PRODUCTO',
                    B.precio_unidad as 'PRECIO PRODUCTO',
                    B.numero_unidades_resta_inventario as 'CANTIDAD',
                    FORMAT(B.sub_total_s, 2) as 'SUB TOTAL',
                    FORMAT(B.isv_s, 2) as 'ISV',
                    B.total_s as 'TOTAL PRODUCTO',
                    FORMAT((A.total/1.15), 2) as 'SUB TOTAL FACTURA',
                    FORMAT(A.total, 2) as 'TOTAL FACTURA' ,
                    FORMAT((A.total-B.sub_total_s), 2) as 'SUB TOTAL DIFERENCIA',
                    (
                        CASE tpv.descripcion WHEN 'CREDITO' THEN 'N/A' WHEN 'CONTADO' THEN FORMAT((B.sub_total_s*0.0175), 2) END
                    ) AS 'CONTADO 1.75%',
                    (
                        CASE tpv.descripcion WHEN 'CREDITO' THEN FORMAT((B.sub_total_s*0.0150), 2) WHEN 'CONTADO' THEN 'N/A' END
                    ) AS 'CREDITO 1.5%',
                    (
                        IF(
                            (select count(*) from venta_has_producto where factura_id = A.id and producto_id not in (1006,
                        1944,
                        1945,
                        1946,
                        1947,
                        1948,
                        1949,
                        1950,
                        1951,
                        1952,
                        1953,
                        1954,
                        1955,
                        1956,
                        1957,
                        1958,
                        1959,
                        1960,
                        1961,
                        1962,
                        1963,
                        2223,
                        2244,
                        2300,
                        2301,
                        2396,
                        2397,
                        2404,
                        2474,
                        2527,
                        2547,
                        2699,
                        2723,
                        2884,
                        2901,
                        2880,
                        2879,
                        2878,
                        2877,
                        2876,
                        2875,
                        2874,
                        2873,
                        2872,
                        2871,
                        2870,
                        2869,
                        2868,
                        2867,
                        2866,
                        2865,
                        2864,
                        2541,
                        2509,
                        2508,
                        2507,
                        2506,
                        2505,
                        2504,
                        2503,
                        2502,
                        2501,
                        2500,
                        2499,
                        2498,
                        2497,
                        2496,
                        2495,
                        2494,
                        2493,
                        2492,
                        2491,
                        2490,
                        2452,
                        2427,
                        2426,
                        2410,
                        2392,
                        2391,
                        2390,
                        2389,
                        2353,
                        2303,
                        2302,
                        2271,
                        2230,
                        2229,
                        2228,
                        1997,
                        1968,
                        1268,
                        1267,
                        1266,
                        1265,
                        1264,
                        1263,
                        1262,
                        1261,
                        1260,
                        1259,
                        1258,
                        1257,
                        1256,
                        1255,
                        1254,
                        1253,
                        1252,
                        1251,
                        1250,
                        1249,
                        1248,
                        1247,
                        1246,
                        1245,
                        1244,
                        1243,
                        1242,
                        1241,
                        1240,
                        1239,
                        1238,
                        1237,
                        1236,
                        1235,
                        1234,
                        1233,
                        1232,
                        1231,
                        1230,
                        1229,
                        1228,
                        1227,
                        1226,
                        1225,
                        1224,
                        1223,
                        1222,
                        1221,
                        1220,
                        1219,
                        1218,
                        1217,
                        1200,
                        1199,
                        1132,
                        1131,
                        1130,
                        1004,
                        2525,
                        2524,
                        2523,
                        2522,
                        2521,
                        2520,
                        2519,
                        2518,
                        2517,
                        2516,
                        2515,
                        2514,
                        2513,
                        2512,
                        2511,
                        2510,
                        2406,
                        2021,
                        2020,
                        2019,
                        2018,
                        2017,
                        2016,
                        2015) ) = 0,
                            'N/A',
                            FORMAT(((A.total-B.sub_total_s)*0.02),2)
                        )
                    ) AS 'COMISION OTROS PRUEBA'


                    from
                    factura A
                    inner join venta_has_producto B on A.id = B.factura_id
                    inner join producto C on B.producto_id = C.id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida E on E.id = D.unidad_medida_id
                    inner join sub_categoria sc on sc.id = C.sub_categoria_id
                    inner join categoria_producto cp on cp.id = sc.categoria_producto_id
                    inner join cliente cli on cli.id = A.cliente_id
                    inner join tipo_pago_venta tpv on tpv.id = A.tipo_pago_id
                    inner join users us on us.id = A.vendedor
                    where
                    A.estado_venta_id = 1
                    and C.id in (
                        1006,
                        1944,
                        1945,
                        1946,
                        1947,
                        1948,
                        1949,
                        1950,
                        1951,
                        1952,
                        1953,
                        1954,
                        1955,
                        1956,
                        1957,
                        1958,
                        1959,
                        1960,
                        1961,
                        1962,
                        1963,
                        2223,
                        2244,
                        2300,
                        2301,
                        2396,
                        2397,
                        2404,
                        2474,
                        2527,
                        2547,
                        2699,
                        2723,
                        2884,
                        2901,
                        2880,
                        2879,
                        2878,
                        2877,
                        2876,
                        2875,
                        2874,
                        2873,
                        2872,
                        2871,
                        2870,
                        2869,
                        2868,
                        2867,
                        2866,
                        2865,
                        2864,
                        2541,
                        2509,
                        2508,
                        2507,
                        2506,
                        2505,
                        2504,
                        2503,
                        2502,
                        2501,
                        2500,
                        2499,
                        2498,
                        2497,
                        2496,
                        2495,
                        2494,
                        2493,
                        2492,
                        2491,
                        2490,
                        2452,
                        2427,
                        2426,
                        2410,
                        2392,
                        2391,
                        2390,
                        2389,
                        2353,
                        2303,
                        2302,
                        2271,
                        2230,
                        2229,
                        2228,
                        1997,
                        1968,
                        1268,
                        1267,
                        1266,
                        1265,
                        1264,
                        1263,
                        1262,
                        1261,
                        1260,
                        1259,
                        1258,
                        1257,
                        1256,
                        1255,
                        1254,
                        1253,
                        1252,
                        1251,
                        1250,
                        1249,
                        1248,
                        1247,
                        1246,
                        1245,
                        1244,
                        1243,
                        1242,
                        1241,
                        1240,
                        1239,
                        1238,
                        1237,
                        1236,
                        1235,
                        1234,
                        1233,
                        1232,
                        1231,
                        1230,
                        1229,
                        1228,
                        1227,
                        1226,
                        1225,
                        1224,
                        1223,
                        1222,
                        1221,
                        1220,
                        1219,
                        1218,
                        1217,
                        1200,
                        1199,
                        1132,
                        1131,
                        1130,
                        1004,
                        2525,
                        2524,
                        2523,
                        2522,
                        2521,
                        2520,
                        2519,
                        2518,
                        2517,
                        2516,
                        2515,
                        2514,
                        2513,
                        2512,
                        2511,
                        2510,
                        2406,
                        2021,
                        2020,
                        2019,
                        2018,
                        2017,
                        2016,
                        2015
                    )
                    and DATE(A.created_at) >= DATE_FORMAT('2023-02-01', '%Y-%m-%d') and  DATE(A.created_at) <= DATE_FORMAT('2023-08-31', '%Y-%m-%d')
                    order by
                    A.created_at asc


    */

}
