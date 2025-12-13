<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DetalleVentaVendedor extends Component
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

        
        return view('livewire.ventas.detalle-venta-vendedor', compact('detalleVenta','montoPagado','pendientePago','nombre'));
    }
}
