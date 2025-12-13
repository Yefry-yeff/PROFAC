<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;

class DetalleVenta extends Component
{
    public $idVenta;
    public function mount($id)
    {

        $this->idVenta = $id;
    }
    public function render()

    {
      $idFactura = $this->idVenta;
        $detalleVenta=DB::SELECTONE("
        select
        A.id,
        A.numero_factura,
        A.cai,
        A.nombre_cliente,
        A.rtn,
        A.sub_total,
        A.sub_total_grabado,
        A.sub_total_excento,
        A.isv,
        A.total,
        A.fecha_emision,
        A.fecha_vencimiento,
        B.descripcion as tipo_pago,
        C.descripcion as estado_venta,
        users.name,
        A.monto_comision,
        E.descripcion as tipo_venta,
        A.comision_estado_pagado,
        F.numero_inicial,
        F.numero_final,
        F.fecha_limite_emision,
        A.tipo_venta_id,
        A.created_at
      from factura A
      inner join tipo_pago_venta B
      on A.tipo_pago_id = B.id
      inner join estado_venta C
      on A.estado_venta_id = C.id
      inner join cliente D
      on A.cliente_id = D.id
      inner join users
      on users.id = A.vendedor
      inner join tipo_venta E
      on A.tipo_venta_id = E.id
      inner join cai F
      on A.cai_id = F.id
      where A.id = ".$idFactura);

      $nombre = DB::SELECTONE("
      select
      name
      from factura
      inner join users
      on factura.users_id = users.id
      where factura.id=".$idFactura
      );

      $montoPagado = DB::SELECTONE("
      select
        sum(monto) as monto
        from
        pago_venta
        where  pago_venta.estado_venta_id=1 and  factura_id = ".$idFactura);

      $pendientePago = $detalleVenta->total -  $montoPagado->monto;

        return view('livewire.ventas.detalle-venta', compact('detalleVenta','montoPagado','pendientePago','nombre'));
    }

    public function listarProductosFactura($id){
       try {

        $listaProductos = DB::SELECT("

     select
        A.id as idFactura,
        C.id as idProducto,
        C.nombre,
        E.nombre as unidad,
        B.precio_unidad,
        sum(B.cantidad) as cantidad,
        sum(B.numero_unidades_resta_inventario )as unidades_vendidas,
        sum(B.sub_total_s) as sub_total,
        sum(B.isv_s) as isv,
        sum(B.total_s) as total

      from factura A
        inner join venta_has_producto B
        on A.id = B.factura_id
        inner join producto C
        on B.producto_id = C.id
        inner join unidad_medida_venta D
        on B.unidad_medida_venta_id = D.id
        inner join unidad_medida E
        on E.id = D.unidad_medida_id
        where A.id = ".$id."
      group by B.producto_id,E.nombre,B.precio_unidad, B.cantidad,B.sub_total, B.isv, B.total

        ");
        return Datatables::of($listaProductos)
        ->make(true);

       return response()->json([
       ],200);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }

    public function ubicacionProductos($id){
       try {

        $listado = DB::SELECT("
        select

        B.id as idProducto,
        B.nombre as producto,
        H.nombre as marca,
        B.descripcion,
        G.nombre as unidad_venta,
        A.cantidad_s as cantidad_venta,
        E.nombre as bodega,
        E.direccion,
        C.descripcion as seccion,

        (select
        y.nombre
        from unidad_medida_venta x
        inner join unidad_medida y
        on x.unidad_medida_id=y.id
        where  x.unidad_venta_defecto = 1 and x.producto_id = B.id) as unidad_venta_base,

        A.numero_unidades_resta_inventario as cantidad_base,
        J.numero_factura

        from venta_has_producto A
        inner join producto B
        on A.producto_id = B.id
        inner join marca H
        on B.marca_id = H.id
        inner join unidad_medida_venta F
        on A.unidad_medida_venta_id = F.id
        inner join unidad_medida G
        on F.unidad_medida_id = G.id
        inner join seccion C
        on A.seccion_id = C.id
        inner join segmento D
        on C.segmento_id = D.id
        inner join bodega E
        on D.bodega_id = E.id
        inner join factura J
        on A.factura_id = J.id
        where A.factura_id = ".$id
    );

    return Datatables::of($listado)
    ->make(true);

       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }

    public function pagosVenta($id){
       try {

        $listaPagos = DB::SELECT("
        select
        @i := @i + 1 as contador,
        factura.numero_factura,
        pago_venta.monto,
        pago_venta.url_img,
        pago_venta.fecha,
        name,
        factura.created_at
        from pago_venta
        inner join factura
        on pago_venta.factura_id = factura.id
        inner join users
        on pago_venta.users_id = users.id
        CROSS JOIN (select @i := 0) r
        where pago_venta.estado_venta_id=1 and factura.id =  ".$id
        );
        return Datatables::of($listaPagos)
        ->make(true);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }
}
