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

SELECT
    DATE_FORMAT(A.created_at, '%d-%m-%Y') AS 'FECHA',
    DATE_FORMAT(A.fecha_vencimiento, '%d-%m-%Y') AS 'FECHA VENCIMIENTO',
    UPPER(tpv.descripcion) AS 'CRÉDITO/CONTADO',

    CASE A.estado_factura_id
        WHEN 1 THEN 'CLIENTE A'
        WHEN 2 THEN 'CLIENTE B'
    END AS 'TIPO CLIENTE (AoB)',

    UPPER(us.name) AS 'VENDEDOR',
    RIGHT(A.cai, 5) AS 'FACTURA',
    cli.nombre AS 'CLIENTE',

    C.id AS 'CÓDIGO',
    C.nombre AS 'PRODUCTO',

    B.precio_unidad AS 'PRECIO PRODUCTO',
    B.numero_unidades_resta_inventario AS 'CANTIDAD',
    FORMAT(B.sub_total_s, 2) AS 'SUB TOTAL PRODUCTO',
    FORMAT(B.isv_s, 2) AS 'ISV',
    B.total_s AS 'TOTAL PRODUCTO',

    FORMAT((A.total / 1.15), 2) AS 'SUB TOTAL FACTURA',
    FORMAT(A.total, 2) AS 'TOTAL FACTURA',

    FORMAT(((A.total / 1.15) - B.sub_total_s), 2) AS 'SUB TOTAL DIFERENCIA',

    -- CONTADO 1.75% SOLO PARA LISTA
    CASE
        WHEN C.id IN (
            4863,4864,4862,4861,4860,4859,4858,4857,4856,4855,4854,4853,4852,4851,4850,4849,4848,4847,4846,4845,
            4844,4843,4842,4841,4840,4839,4838,4837,4836,4835,4586,4537,4536,4535,4534,4533,4532,4531,4530,
            4316,4315,4314,4313,4312,4311,4310,4309,4308,4307,4227,3908,3706,3705,3676,3675,3638,3634,3633,
            3632,3631,3630,3627,3562,3507,3504,3308,3263,3262,3260,3259,3257,3256,3254,3253,3199,3196,3192,
            3189,3185,3184,3183,3179,2936,2935,2928,2927,2911,2906,2905,2904,2903,2735,2734,2733,2732,2731,
            2730,2685,2681,2601,2463,2462,2461,2460,2459,2458,2455,2454,2420,2419,2413,2408,2407,2384,2383,
            2370,2369,2368,2367,2366,2365,2364,2349,2285,2267,2266
        )
        AND tpv.descripcion = 'CONTADO'
        THEN FORMAT((B.sub_total_s * 0.0175), 2)
        ELSE 'N/A'
    END AS 'CONTADO_175_PORC',

    -- CREDITO 1.5% SOLO PARA LISTA
    CASE
        WHEN C.id IN (
            4863,4864,4862,4861,4860,4859,4858,4857,4856,4855,4854,4853,4852,4851,4850,4849,4848,4847,4846,4845,
            4844,4843,4842,4841,4840,4839,4838,4837,4836,4835,4586,4537,4536,4535,4534,4533,4532,4531,4530,
            4316,4315,4314,4313,4312,4311,4310,4309,4308,4307,4227,3908,3706,3705,3676,3675,3638,3634,3633,
            3632,3631,3630,3627,3562,3507,3504,3308,3263,3262,3260,3259,3257,3256,3254,3253,3199,3196,3192,
            3189,3185,3184,3183,3179,2936,2935,2928,2927,2911,2906,2905,2904,2903,2735,2734,2733,2732,2731,
            2730,2685,2681,2601,2463,2462,2461,2460,2459,2458,2455,2454,2420,2419,2413,2408,2407,2384,2383,
            2370,2369,2368,2367,2366,2365,2364,2349,2285,2267,2266
        )
        AND tpv.descripcion = 'CREDITO'
        THEN FORMAT((B.sub_total_s * 0.015), 2)
        ELSE 'N/A'
    END AS 'CREDITO_15_PORC',

    -- COMISION 3% SOLO PARA LOS QUE NO ESTÁN EN LA LISTA
    CASE
        WHEN C.id NOT IN (
            4863,4864,4862,4861,4860,4859,4858,4857,4856,4855,4854,4853,4852,4851,4850,4849,4848,4847,4846,4845,
            4844,4843,4842,4841,4840,4839,4838,4837,4836,4835,4586,4537,4536,4535,4534,4533,4532,4531,4530,
            4316,4315,4314,4313,4312,4311,4310,4309,4308,4307,4227,3908,3706,3705,3676,3675,3638,3634,3633,
            3632,3631,3630,3627,3562,3507,3504,3308,3263,3262,3260,3259,3257,3256,3254,3253,3199,3196,3192,
            3189,3185,3184,3183,3179,2936,2935,2928,2927,2911,2906,2905,2904,2903,2735,2734,2733,2732,2731,
            2730,2685,2681,2601,2463,2462,2461,2460,2459,2458,2455,2454,2420,2419,2413,2408,2407,2384,2383,
            2370,2369,2368,2367,2366,2365,2364,2349,2285,2267,2266
        )
        THEN FORMAT((B.sub_total_s * 0.03), 2)
        ELSE NULL
    END AS 'COMISION_MISELANEOS'

FROM factura A
INNER JOIN venta_has_producto B ON A.id = B.factura_id
INNER JOIN producto C ON B.producto_id = C.id
INNER JOIN cliente cli ON cli.id = A.cliente_id
INNER JOIN tipo_pago_venta tpv ON tpv.id = A.tipo_pago_id
INNER JOIN users us ON us.id = A.vendedor

WHERE
    A.estado_venta_id = 1
    AND A.created_at BETWEEN '".$fecha_inicio." 00:00:00'
                          AND '".$fecha_final." 23:59:59'

ORDER BY A.created_at DESC;
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
