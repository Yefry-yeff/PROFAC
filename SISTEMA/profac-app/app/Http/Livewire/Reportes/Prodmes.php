<?php

namespace App\Http\Livewire\Reportes;

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

class Prodmes extends Component
{
    public function render()
    {
        return view('livewire.reportes.prodmes');
    }

    public function consultaComision($fecha_inicio, $fecha_final){
        try {



            $consulta = DB::SELECT("

                select
                date_format(A.created_at, '%d-%m-%Y') as 'FECHA',
                date_format(A.fecha_vencimiento, '%d-%m-%Y') as 'FECHA VENCIMIENTO',
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
                FORMAT(B.sub_total_s, 2) as 'SUB TOTAL PRODUCTO',
                FORMAT(B.isv_s, 2) as 'ISV',
                B.total_s as 'TOTAL PRODUCTO',
                FORMAT(
                    (A.total / 1.15),
                    2
                ) as 'SUB TOTAL FACTURA',
                FORMAT(A.total, 2) as 'TOTAL FACTURA',
                FORMAT(
                    (
                    (A.total / 1.15)- B.sub_total_s
                    ),
                    2
                ) as 'SUB TOTAL DIFERENCIA',
                (
                    CASE tpv.descripcion WHEN 'CREDITO' THEN 'N/A' WHEN 'CONTADO' THEN FORMAT(
                    (B.sub_total_s * 0.0175),
                    2
                    ) END
                ) AS 'CONTADO 1.75%',
                (
                    CASE tpv.descripcion WHEN 'CREDITO' THEN FORMAT(
                    (B.sub_total_s * 0.0150),
                    2
                    ) WHEN 'CONTADO' THEN 'N/A' END
                ) AS 'CREDITO 1.5%',
                (
                    IF(
                    (
                        select
                        count(*)
                        from
                        venta_has_producto
                        where
                        factura_id = A.id
                        and producto_id not in (
                            1006, 1035, 1940, 1942, 1943, 1944, 1945,
                            1946, 1947, 1948, 1949, 1950, 1951,
                            1952, 1953, 1954, 1955, 1956, 1957,
                            1958, 1959, 1960, 1961, 1962, 1963,
                            1964, 1965, 2029, 2030, 2223, 2244,
                            2300, 2301, 2396, 2397, 2404, 2474,
                            2527, 2547, 2699, 2723, 2884, 2901
                        )
                    ) = 0,
                    'N/A',
                    FORMAT(
                        (
                        (A.total - B.sub_total_s)* 0.02
                        ),
                        2
                    )
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
                    1006, 1035, 1940, 1942, 1943, 1944, 1945,
                    1946, 1947, 1948, 1949, 1950, 1951,
                    1952, 1953, 1954, 1955, 1956, 1957,
                    1958, 1959, 1960, 1961, 1962, 1963,
                    1964, 1965, 2029, 2030, 2223, 2244,
                    2300, 2301, 2396, 2397, 2404, 2474,
                    2527, 2547, 2699, 2723, 2884, 2901,1007,1035,1134,1188,1189,1218,1220,1221,1223,1226,1227,1228,1229,1230,1232,1233,1234,1235,1236,1237,1238,1239,1240,1242,1243,1244,1245,1246,1247,1248,1249,1250,1251,1252,1253,1254,1255,1257,1258,1260,1261,1263,1264,1265,1266,1292,1293,1294,1295,1297,1298,1299,1301,1302,1303,1304,1305,1306,1307,1308,1309,1310,1311,1312,1313,1314,1315,1316,1317,1318,1319,1320,1323,1325,1326,1327,1328,1329,1330,1331,1334,1335,1336,1337,1339,1340,1341,1342,1343,1344,1345,1346,1347,1348,1349,1350,1351,1352,1353,1354,1355,1356,1357,1358,1359,1360,1361,1362,1363,1364,1365,1366,1367,1368,1370,1371,1372,1373,1374,1375,1376,1377,1378,1379,1380,1381,1382,1383,1384,1385,1386,1387,1388,1389,1390,1391,1392,1393,1394,1395,1396,1397,1398,1400,1401,1402,1403,1404,1405,1406,1407,1408,1409,1410,1411,1412,1413,1414,1415,1416,1417,1418,1419,1420,1421,1422,1423,1424,1425,1426,1427,1430,1431,1432,1434,1435,1436,1437,1551,1556,1557,1558,1559,1688,1689,1690,1691,1692,1693,1694,1695,1696,1697,1698,1699,1700,1701,1702,1703,1940,1945,1954,1955,1960,1961,1962,1963,2029,2030,2223,2224,2228,2229,2230,2243,2260,2266,2267,2285,2300,2301,2303,2349,2353,2364,2365,2366,2368,2369,2370,2383,2384,2389,2390,2391,2392,2401,2404,2407,2408,2410,2413,2419,2420,2454,2455,2458,2460,2461,2462,2474,2492,2493,2500,2505,2509,2583,2598,2601,2699,2701,2723,2903,2904,2905,2906,2911,2927,2928,2930,2947,3146,3147,3148,3152,3155,3161,3163,3164,3165,3166,3168,3171,3173,3179,3183,3184,3185,3189,3192,3196,3199,3253,3254,3256,3257,3259,3260,3262,3263,3308,3408,3414,3417,3478,3507,3562,3565,3577,3578,3634,3706,3907,3908,3924,3925,3926,3927,3928,3929,3930,3931,3932,3933,3934,3935,3936,3937,3938,3939,3940,3941,3942,3943,4022,4030,4068,4070,4071,4072,4073,4074,4081,4084,4085,4087,4088,4089,4090,4091,4092,4101
                )
                and DATE(A.created_at) >= DATE_FORMAT('".$fecha_inicio."', '%Y-%m-%d')
                and DATE(A.created_at) <= DATE_FORMAT('".$fecha_final."', '%Y-%m-%d')
                order by
                A.created_at DESC
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
}
