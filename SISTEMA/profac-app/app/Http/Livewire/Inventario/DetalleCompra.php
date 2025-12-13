<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DetalleCompra extends Component

{
    public $idCompra;
    public function mount($id)
    {

        $this->idCompra = $id;
    }

    public function render()
    {
        $id = $this->idCompra;

        $detalleCompra = DB::SELECTONE("
        select
            compra.id,
            numero_orden,
            numero_factura,
            fecha_emision,
            fecha_vencimiento,
            fecha_recepcion,
            total,
            debito,
            tipo_compra.descripcion,
            monto_retencion,
            IF(retenciones_id is null,'SIN RETENCION','CON RETENCION') as 'estado_retencion',
            proveedores.nombre,
            users.name as usuario,
            compra.created_at as fecha_registro,
            isv_compra,
            sub_total,
            total,
            debito
        from compra
        inner join users
        on compra.users_id = users.id
        inner join proveedores
        on compra.proveedores_id = proveedores.id
        inner join tipo_compra
        on compra.tipo_compra_id = tipo_compra.id
        where compra.id = ".$id."
        ");

        $listaCompra = DB::SELECT("
        select

        compra.numero_factura,
        A.producto_id as 'producto_id',
        producto.nombre as nombre,
        A.precio_unidad,
        A.cantidad_ingresada,
        A.sub_total_producto,
        A.isv,
        A.precio_total,
        if(A.fecha_expiracion is null, 'No definido',A.fecha_expiracion) as fecha_expiracion,
        if((select estado_recibido from recibido_bodega where recibido_bodega.compra_id = A.compra_id and recibido_bodega.producto_id =A.producto_id limit 1 ) is null, 'NO RECIBIDO', 'RECIBIDO') as 'estado_recibido'

    from
        compra_has_producto A
        inner join producto
        on producto.id = A.producto_id
        inner join compra
        on A.compra_id = compra.id
        where A.compra_id =   ".$id."
        ");

        $listaPagos = DB::SELECT("
        select
        compra.id,
        compra.numero_factura,
        compra.numero_orden,
        format(pago_compra.monto,2) as monto,
        pago_compra.fecha,
        users.name,
        pago_compra.created_at as 'fecha_sistema'

        FROM compra
        inner join pago_compra
        on pago_compra.compra_id = compra.id
        inner join users
        on pago_compra.users_id = users.id
        where pago_compra.estado_id = 1 and compra.id = ".$id."
        ");


        $sumaPagos = DB::SELECTONE("select sum(monto) as monto from pago_compra where pago_compra.estado_id = 1 and compra_id = ".$id);
        $deudaRestante = round($detalleCompra->total, 2) -  round($sumaPagos->monto,2);




        //dd($jsonListaCompra);



        return view('livewire.inventario.detalle-compra', compact('detalleCompra','listaCompra','listaPagos', 'deudaRestante', 'sumaPagos'));
    }


}
