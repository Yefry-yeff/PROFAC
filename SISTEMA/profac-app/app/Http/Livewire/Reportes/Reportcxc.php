<?php

namespace App\Http\Livewire\Reportes;
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

class Reportcxc extends Component
{
    public function render()
    {
        return view('livewire.reportes.reportcxc');
    }

    
    public function consulta($fecha_inicio, $fecha_final){
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
                            id = A.vendedor
                        )
                    ) as 'VENDEDOR',
                    RIGHT(A.cai, 5) as 'FACTURA',
                    UPPER(cli.nombre) as 'CLIENTE',
                    (
                        CASE cli.tipo_cliente_id WHEN '1' THEN 'CLIENTE B' WHEN '2' THEN 'CLIENTE A' END
                    ) AS 'TIPO CLIENTE (AoB)',
                    (
                        CASE A.tipo_pago_id WHEN '1' THEN 'CONTADO' WHEN '2' THEN 'CRÉDITO' END
                    ) AS 'TIPO CRÉDITO/CONTADO',
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
                    REPLACE(
                        REPLACE(F.descripcion, 'Seccion', ''),
                        ' ',
                        ''
                    ) as 'SECCION',
                    FORMAT(
                        TRUNCATE(B.precio_unidad, 2),
                        2
                    ) as 'PRECIO',
                    sum(B.cantidad_s) as 'UNIDADES VENDIDAS',
                    FORMAT(
                        sum(B.sub_total_s),
                        2
                    ) as 'SUBTOTAL PRODUCTO',
                    FORMAT(
                        sum(B.isv_s),
                        2
                    ) as 'ISV PRODUCTO',
                    FORMAT(
                        sum(B.total_s),
                        2
                    ) as 'TOTAL PRODUCTO',
                    FORMAT(
                        SUM(A.sub_total),
                        2
                    ) as 'SUB TOTAL FACTURA',
                    FORMAT(
                        SUM(A.isv),
                        2
                    ) as 'ISV FACTURA',
                    FORMAT(
                        SUM(A.total),
                        2
                    ) as 'TOTAL FACTURA'
                from factura A
                    inner join venta_has_producto B on A.id = B.factura_id
                    inner join producto C on B.producto_id = C.id
                    inner join marca ma on ma.id = C.marca_id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join recibido_bodega E on B.lote = E.id
                    inner join seccion F on E.seccion_id = F.id
                    inner join segmento G on F.segmento_id = G.id
                    inner join bodega H on G.bodega_id = H.id
                    inner join cliente cli on cli.id = A.cliente_id
                    inner join sub_categoria on sub_categoria.id = C.sub_categoria_id
                    inner join categoria_producto on categoria_producto.id = sub_categoria.categoria_producto_id
                where
                    A.estado_venta_id = 1
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
                    F.descripcion,
                    B.precio_unidad,
                    B.cantidad_s,
                    B.sub_total_s,
                    B.isv_s,
                    B.total_s,
                    A.vendedor,
                    A.sub_total,
                    A.isv,
                    A.total,
                    A.cai,cli.nombre,A.tipo_venta_id,A.tipo_pago_id ,cli.tipo_cliente_id
                order by
                    A.fecha_emision asc

            ");


                        //dd($consulta);


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

    public function catalogoProductos(){
        try {



            $consulta = DB::SELECT("
                select
                    A.id as 'CODIGO',
                    A.codigo_barra AS 'CODIGO DE BARRA',
                    A.nombre AS 'PRODUCTO',
                    marca.nombre as 'MARCA',
                    ##A.descripcion,
                    IF (A.isv = 0, 'NO', 'SI') as 'ISV',
                    CA.descripcion as 'CATEGORIA',
                    B.descripcion as 'SUB CATEGORIA',
                    @existenciaCompra := IFNULL (
                        ( select sum(cantidad_disponible) from recibido_bodega inner join compra on recibido_bodega.compra_id = compra.id where compra.estado_compra_id = 1 and producto_id = A.id), 0 ) as 'EXISTENCIA POR COMPRA',
                    @existenciaAjuste := IFNULL (
                        ( select sum(cantidad_disponible) from recibido_bodega G where G.compra_id is null and G.cantidad_disponible <> 0 and G.producto_id = A.id ), 0
                    ) as 'EXISTENCIA POR AJUSTE',
                    FORMAT(
                        @existenciaCompra + @existenciaAjuste,
                        0
                    ) as 'EXISTENCIA TOTAL',
                    A.precio_base as 'PRECIO BASE'
                    from
                    producto A
                    inner join sub_categoria B on A.sub_categoria_id = B.id
                    inner join categoria_producto CA on CA.id = B.categoria_producto_id
                    inner join marca on marca.id = A.marca_id
                    order by
                    A.created_at DESC

            ");


            //dd($consulta);


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

    public function consultaClientes(){
        try {
            $consulta = DB::SELECT("
            select
            c.id as 'CODIGO',
            (
            CASE c.tipo_cliente_id WHEN '1' THEN 'CLIENTE B' WHEN '2' THEN 'CLIENTE A' END
            ) AS 'TIPO CLIENTE (AoB)',
                    UPPER(c.nombre) AS 'CLIENTE',
                    UPPER(pais.nombre) AS 'PAIS',
                    UPPER(departamento.nombre) AS 'DEPARTAMENTO',
                    UPPER(municipio.nombre) as 'MUNICIPIO',
                        UPPER(c.direccion) AS 'DIRECCION',
                        c.telefono_empresa AS 'TELEFONO',
                        c.correo AS 'CORREO',
                        c.rtn as 'RTN',
                        UPPER(users.name) as 'VENDEDOR',
                        c.created_at as 'REGISTRO'
                    from cliente c
                    inner join users on users.id = c.vendedor
                    inner join municipio on municipio.id = c.municipio_id
                    inner join departamento on departamento.id = municipio.departamento_id
                    inner join pais on pais.id = departamento.pais_id
                    where c.estado_cliente_id = 1

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

    public function consultaVentasAnuladas($fecha_inicio, $fecha_final){
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
                            id = A.vendedor
                        )
                    ) as 'VENDEDOR',
                    RIGHT(A.cai, 5) as 'FACTURA',
                    UPPER(cli.nombre) as 'CLIENTE',
                    (
                        CASE cli.tipo_cliente_id WHEN '1' THEN 'CLIENTE B' WHEN '2' THEN 'CLIENTE A' END
                    ) AS 'TIPO CLIENTE (AoB)',
                    (
                        CASE A.tipo_pago_id WHEN '1' THEN 'CONTADO' WHEN '2' THEN 'CRÉDITO' END
                    ) AS 'TIPO CRÉDITO/CONTADO',
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
                    REPLACE(
                        REPLACE(F.descripcion, 'Seccion', ''),
                        ' ',
                        ''
                    ) as 'SECCION',
                    FORMAT(
                        TRUNCATE(B.precio_unidad, 2),
                        2
                    ) as 'PRECIO',
                    sum(B.cantidad_s) as 'UNIDADES VENDIDAS',
                    FORMAT(
                        sum(B.sub_total_s),
                        2
                    ) as 'SUBTOTAL PRODUCTO',
                    FORMAT(
                        sum(B.isv_s),
                        2
                    ) as 'ISV PRODUCTO',
                    FORMAT(
                        sum(B.total_s),
                        2
                    ) as 'TOTAL PRODUCTO',
                    FORMAT(
                        SUM(A.sub_total),
                        2
                    ) as 'SUB TOTAL FACTURA',
                    FORMAT(
                        SUM(A.isv),
                        2
                    ) as 'ISV FACTURA',
                    FORMAT(
                        SUM(A.total),
                        2
                    ) as 'TOTAL FACTURA'
                from factura A
                    inner join venta_has_producto B on A.id = B.factura_id
                    inner join producto C on B.producto_id = C.id
                    inner join marca ma on ma.id = C.marca_id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join recibido_bodega E on B.lote = E.id
                    inner join seccion F on E.seccion_id = F.id
                    inner join segmento G on F.segmento_id = G.id
                    inner join bodega H on G.bodega_id = H.id
                    inner join cliente cli on cli.id = A.cliente_id
                    inner join sub_categoria on sub_categoria.id = C.sub_categoria_id
                    inner join categoria_producto on categoria_producto.id = sub_categoria.categoria_producto_id
                where
                    A.estado_venta_id = 2
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
                    F.descripcion,
                    B.precio_unidad,
                    B.cantidad_s,
                    B.sub_total_s,
                    B.isv_s,
                    B.total_s,
                    A.vendedor,
                    A.sub_total,
                    A.isv,
                    A.total,
                    A.cai,cli.nombre,A.tipo_venta_id,A.tipo_pago_id ,cli.tipo_cliente_id
                order by
                    A.fecha_emision asc

            ");


                        //dd($consulta);


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

    //INVENTARIOS
    
    public function productosExistencia(){
        try {
            $consulta = DB::SELECT("
            select
            A.id as 'CODIGO',
            A.nombre as 'PRODUCTO',
            A.descripcion as 'DESCRIPCION PRODUCTO' ,
            marca.nombre as 'MARCA',
            A.isv as 'ISV',
			A.precio_base AS 'PRECIO BASE',
			A.ultimo_costo_compra AS 'ULTIMO COSTO DE COMPRA',
            A.costo_promedio AS 'COSTO PROMEDIO',
            categoria_producto.descripcion as 'CATEGORIA',
            B.descripcion as 'SUBCATEGORIA',
			unidad_medida.nombre as 'UNIDAD DE MEDIDA EN LA COMPRA',
            @existenciaCompra := IFNULL ((select
            sum(cantidad_disponible)
            from recibido_bodega
            inner join compra
            on recibido_bodega.compra_id = compra.id
            where compra.estado_compra_id=1 and  producto_id = 				A.id), 0)  as 'EXISTENCIA POR COMPRA',
           @existenciaAjuste := IFNULL (
           (
            select
            sum(cantidad_disponible)
            from recibido_bodega  G
            where G.compra_id is null and G.cantidad_disponible <> 0 and G.producto_id = A.id),0 ) as 'EXISTENCIA POR AJUSTE',
            FORMAT(@existenciaCompra + @existenciaAjuste,0) as 'EXISTENCIA',
            A.codigo_barra
            from producto A
            inner join sub_categoria B on A.sub_categoria_id = B.id 
			INNER JOIN categoria_producto on categoria_producto.id = B.id
            INNER JOIN unidad_medida on unidad_medida.id = A.unidad_medida_compra_id
			INNER JOIN marca on marca.id = A.marca_id
            where A.estado_producto_id = 1
            order by A.created_at DESC

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

    public function consultaVales($fecha_inicio, $fecha_final){
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
                            id = A.vendedor
                        )
                    ) as 'VENDEDOR',
                    RIGHT(A.cai, 5) as 'FACTURA',
                    UPPER(cli.nombre) as 'CLIENTE',
                    (
                        CASE cli.tipo_cliente_id WHEN '1' THEN 'CLIENTE B' WHEN '2' THEN 'CLIENTE A' END
                    ) AS 'TIPO CLIENTE (AoB)',
                    (
                        CASE A.tipo_pago_id WHEN '1' THEN 'CONTADO' WHEN '2' THEN 'CRÉDITO' END
                    ) AS 'TIPO CRÉDITO/CONTADO',
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
                    REPLACE(
                        REPLACE(F.descripcion, 'Seccion', ''),
                        ' ',
                        ''
                    ) as 'SECCION',
                    FORMAT(
                        TRUNCATE(B.precio_unidad, 2),
                        2
                    ) as 'PRECIO',
                    sum(B.cantidad_s) as 'UNIDADES VENDIDAS',
                    FORMAT(
                        sum(B.sub_total_s),
                        2
                    ) as 'SUBTOTAL PRODUCTO',
                    FORMAT(
                        sum(B.isv_s),
                        2
                    ) as 'ISV PRODUCTO',
                    FORMAT(
                        sum(B.total_s),
                        2
                    ) as 'TOTAL PRODUCTO',
                    FORMAT(
                        SUM(A.sub_total),
                        2
                    ) as 'SUB TOTAL FACTURA',
                    FORMAT(
                        SUM(A.isv),
                        2
                    ) as 'ISV FACTURA',
                    FORMAT(
                        SUM(A.total),
                        2
                    ) as 'TOTAL FACTURA'
                from factura A
                    inner join venta_has_producto B on A.id = B.factura_id
                    inner join producto C on B.producto_id = C.id
                    inner join marca ma on ma.id = C.marca_id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join recibido_bodega E on B.lote = E.id
                    inner join seccion F on E.seccion_id = F.id
                    inner join segmento G on F.segmento_id = G.id
                    inner join bodega H on G.bodega_id = H.id
                    inner join cliente cli on cli.id = A.cliente_id
                    inner join sub_categoria on sub_categoria.id = C.sub_categoria_id
                    inner join categoria_producto on categoria_producto.id = sub_categoria.categoria_producto_id
                where
                    A.estado_venta_id = 1
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
                    F.descripcion,
                    B.precio_unidad,
                    B.cantidad_s,
                    B.sub_total_s,
                    B.isv_s,
                    B.total_s,
                    A.vendedor,
                    A.sub_total,
                    A.isv,
                    A.total,
                    A.cai,cli.nombre,A.tipo_venta_id,A.tipo_pago_id ,cli.tipo_cliente_id
                order by
                    A.fecha_emision asc

            ");


                        //dd($consulta);


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
