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

class Expo extends Component
{
    public function render()
    {
        return view('livewire.reportes.expo');
    }

    public function consultaProductoPedido($fecha_inicio, $fecha_final){
        try {
            $consulta = DB::SELECT("
                select
                    A.fecha_emision as 'FECHA DE VENTA',
                    A.fecha_vencimiento as 'FECHA DE VENCIMIENTO',
                    UPPER(
                        (
                        select
                            name
                        from
                            users
                        where
                            id = A.users_id
                        )
                    ) as 'VENDEDOR',
                    A.id as 'COTIZACION',
                    UPPER(cli.nombre) as 'CLIENTE',
                    (
                        CASE cli.tipo_cliente_id WHEN '1' THEN 'CLIENTE B' WHEN '2' THEN 'CLIENTE A' END
                    ) AS 'TIPO CLIENTE (AoB)',
                    B.producto_id as 'CODIGO PRODUCTO',
                    UPPER(
                        concat(C.nombre)
                    ) as 'PRODUCTO',
                    UPPER(ma.nombre) as 'MARCA',
                    UPPER(categoria_producto.descripcion) as 'CATEGORIA',
                    UPPER(sub_categoria.descripcion) as 'SUB CATEGORIA',
                    UPPER(J.nombre) as 'UNIDAD DE MEDIDA',
                    if(C.isv = 0, 'SI', 'NO') as 'EXCENTO',
                    H.nombre as 'BODEGA',
                    FORMAT(
                        TRUNCATE(B.precio_unidad, 2),
                        2
                    ) as 'PRECIO',
                    sum(B.cantidad) as 'UNIDADES VENDIDAS',
                    FORMAT(
                        sum(B.cantidad),
                        2
                    ) as 'SUBTOTAL PRODUCTO',
                    FORMAT(
                        sum(B.isv),
                        2
                    ) as 'ISV PRODUCTO',
                    FORMAT(
                        sum(B.total),
                        2
                    ) as 'TOTAL PRODUCTO',
                    FORMAT(
                        SUM(A.sub_total),
                        2
                    ) as 'SUB TOTAL PEDIDO',
                    FORMAT(
                        SUM(A.isv),
                        2
                    ) as 'ISV PEDIDO',
                    FORMAT(
                        SUM(A.total),
                        2
                    ) as 'TOTAL PEDIDO'
                from cotizacion A
                    inner join cotizacion_has_producto B on A.id = B.cotizacion_id
                    inner join producto C on B.producto_id = C.id
                    inner join marca ma on ma.id = C.marca_id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join bodega H on B.bodega_id = H.id
                    inner join cliente cli on cli.id = A.cliente_id
                    inner join sub_categoria on sub_categoria.id = C.sub_categoria_id
                    inner join categoria_producto on categoria_producto.id = sub_categoria.categoria_producto_id
                where
                A.tipo_venta_id = 4
                    AND DATE_FORMAT(A.fecha_emision, '%Y-%m-%d') >= '".$fecha_inicio."'
                    AND DATE_FORMAT(A.fecha_emision, '%Y-%m-%d') <= '".$fecha_final."'
                group by
                    A.fecha_emision,
                    A.fecha_vencimiento,
                    B.producto_id,
                    C.nombre,
                    ma.nombre,
                    categoria_producto.descripcion,
                    sub_categoria.descripcion,
                    J.nombre,
                    C.isv,
                    H.nombre,
                    B.precio_unidad,
                    B.cantidad,
                    B.sub_total,
                    B.isv,
                    B.total,
                    A.vendedor,
                    A.sub_total,
                    A.isv,
                    A.total,A.id,cli.nombre,A.tipo_venta_id,cli.tipo_cliente_id
                order by
                    A.fecha_emision asc

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
