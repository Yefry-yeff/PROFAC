<?php

namespace App\Http\Livewire\Comisiones;

use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use App\Models\usuario;

use App\Models\Comisiones\desglose;
use App\Models\Comisiones\desglose_temp;

use DataTables;

class ComisionesPrincipal extends Component
{
    public function render()
    {
        return view('livewire.comisiones.comisiones-principal');
    }

    public function existenciaTecho($mest,$idVendedort){

        $existenciaTecho = DB::SELECTONE("
        select users.name as vendedor, meses.nombre as mes, count(*) as canTecho from comision_techo
        inner join users on (users.id = comision_techo.vendedor_id)
        inner join meses on (meses.id = comision_techo.meses_id)
        where comision_techo.estado_id = 1 and comision_techo.vendedor_id = ".$idVendedort." and comision_techo.meses_id = ".$mest."");

        if ($existenciaTecho->canTecho == 0) {

            return response()->json([
                'message' => 'No se puede comisionar,el vendedor '.$existenciaTecho->vendedor.' no tiene un techo de comisión para el mes de '.$existenciaTecho->mes.'.',
                'permiso' => 0
            ], 200);
        }else{
            return response()->json([
                'message' => 'Si tiene techo.',
                'permiso' => 1
            ], 200);
        }
    }

    public function obtenerFacturas($mes,$idVendedor){
        //dd($mes,$idVendedor);


        DB::table('desglose_temp')->truncate();

            try {
                $listaFacturas = DB::SELECT("
                    select
                        factura.id as 'id',
                        factura.numero_factura as 'numero_factura',
                        factura.fecha_emision as 'fecha_emision',
                        factura.fecha_vencimiento as 'fecha_vencimiento',
                        date_add(factura.fecha_vencimiento, interval 30 day) as 'fechaGracia',
                        cliente.nombre as 'nombre',
                        factura.total as 'total'
                    from factura
                        inner join cliente
                        on factura.cliente_id = cliente.id
                        inner join tipo_pago_venta
                        on factura.tipo_pago_id = tipo_pago_venta.id
                        inner join users
                        on factura.vendedor = users.id
                        cross join (select @i := 0) r
                    where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) )
                    and (DATE_FORMAT(factura.fecha_emision,'%m') = ".$mes.")
                    and factura.pendiente_cobro <= 0
                    and factura.estado_venta_id = 1
                    and factura.comision_estado_pagado = 0
                    and factura.vendedor = ".$idVendedor."
                    order by factura.created_at desc;
                ");

                 foreach ($listaFacturas as $value) {
                    $listaProd = DB::SELECT("
                        select
                        A.id as idFactura,
                        A.numero_factura,
                        C.id as idProducto,
                        C.nombre as producto,
                        C.precio_base,
                        C.ultimo_costo_compra,
                        E.nombre as unidad_venta,
                        B.cantidad,
                        B.precio_unidad,
                        (B.precio_unidad - C.ultimo_costo_compra) as gananciaUnidad,
                        ((B.precio_unidad - C.ultimo_costo_compra) * B.cantidad) as gananciatotal,
                        B.total,
                        B.sub_total,
                        B.isv,
                        B.seccion_id,
                        F.descripcion as seccion,
                        H.nombre as bodega
                        from factura A
                        inner join venta_has_producto B
                        on A.id = B.factura_id
                        inner join producto C
                        on B.producto_id = C.id
                        inner join unidad_medida_venta D
                        on B.unidad_medida_venta_id = D.id
                        inner join unidad_medida E
                        on D.unidad_medida_id = E.id
                        inner join seccion F
                        on B.seccion_id = F.id
                        inner join segmento G
                        on G.id = F.segmento_id
                        inner join bodega H
                        on G.bodega_id = H.id
                        where A.id = ".$value->id."
                        group by A.id, A.numero_factura, C.id, C.nombre, C.precio_base, C.ultimo_costo_compra, E.nombre,  B.precio_unidad,B.cantidad, B.total, B.sub_total, B.isv, B.seccion_id, F.descripcion,  H.nombre

                    ");
                    //dd($listaProd);
                    $existencia = DB::SELECTONE("select count(*) AS existencia FROM desglose WHERE idFactura in (SELECT idFactura FROM desglose WHERE idFactura = ".$value->id.")");
                    //dd($existencia->existencia);
                    if ($existencia->existencia == 0) {
                        //DB::table('desglose_temp')->truncate();
                        foreach ($listaProd as $values) {

                            $desglose = new desglose;
                            $desglose->idFactura = $values->idFactura;
                            $desglose->numero_factura = $values->numero_factura;
                            $desglose->idProducto= $values->idProducto;
                            $desglose->producto= $values->producto;
                            $desglose->precio_base= $values->precio_base;
                            $desglose->ultimo_costo_compra= $values->ultimo_costo_compra;
                            $desglose->unidad_venta= $values->unidad_venta;
                            $desglose->cantidad= $values->cantidad;
                            $desglose->precio_unidad= $values->precio_unidad;
                            $desglose->gananciaUnidad= $values->gananciaUnidad;
                            $desglose->gananciatotal= $values->gananciatotal;
                            $desglose->total= $values->total;
                            $desglose->sub_total= $values->sub_total;
                            $desglose->isv= $values->isv;
                            $desglose->seccion_id= $values->seccion_id;
                            $desglose->seccion= $values->seccion;
                            $desglose->bodega= $values->bodega;
                            $desglose->vendedor_id = $idVendedor;
                            $desglose->estadoComisionado = 0;
                            $desglose->save();
                        }

                    }


                    foreach ($listaProd as $values) {
                        $desgloseTemporal = new desglose_temp;
                        $desgloseTemporal->idFactura = $values->idFactura;
                        $desgloseTemporal->numero_factura = $values->numero_factura;
                        $desgloseTemporal->idProducto= $values->idProducto;
                        $desgloseTemporal->producto= $values->producto;
                        $desgloseTemporal->precio_base= $values->precio_base;
                        $desgloseTemporal->ultimo_costo_compra= $values->ultimo_costo_compra;
                        $desgloseTemporal->unidad_venta= $values->unidad_venta;
                        $desgloseTemporal->cantidad= $values->cantidad;
                        $desgloseTemporal->precio_unidad= $values->precio_unidad;
                        $desgloseTemporal->gananciaUnidad= $values->gananciaUnidad;
                        $desgloseTemporal->gananciatotal= $values->gananciatotal;
                        $desgloseTemporal->total= $values->total;
                        $desgloseTemporal->sub_total= $values->sub_total;
                        $desgloseTemporal->isv= $values->isv;
                        $desgloseTemporal->seccion_id= $values->seccion_id;
                        $desgloseTemporal->seccion= $values->seccion;
                        $desgloseTemporal->bodega= $values->bodega;
                        $desgloseTemporal->vendedor_id = $idVendedor;
                        $desgloseTemporal->estadoComisionado = 0;
                        $desgloseTemporal->save();
                    }

                }
                //dd($listaFacturas);
                return Datatables::of($listaFacturas)
                ->addColumn('estadoPago', function ($listaFacturas) {
                    return

                    '<span class="badge badge-success">CERRADA</span>';
                })
                ->addColumn('comision', function ($listaFacturas) {
                    return

                    '<span class="badge badge-danger">Sin comisionar</span>';
                })
                ->addColumn('acciones', function ($listaFacturas) {
                        return

                        '<div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                <li>
                                    <a class="dropdown-item" href="/detalle/venta/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de Factura </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/desglose/factura/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Desglose  </a>
                                </li>

                            </ul>
                        </div>';
                })
                ->rawColumns(['estadoPago','comision','acciones'])
                ->make(true);
            } catch (QueryException $e) {
                return response()->json([
                    'message' => 'Ha ocurrido un error al listar las facturas.',
                    'errorTh' => $e,
                ], 402);

            }

    }

    public function obtenerFacturasSinCerrar($mes,$idVendedor){
        //dd($mes,$idVendedor);

            try {
                $listaFacturas = DB::SELECT("
                    select
                        factura.id as 'id',
                        factura.numero_factura as 'numero_factura',
                        factura.fecha_emision as 'fecha_emision',
                        factura.fecha_vencimiento as 'fecha_vencimiento',
                        date_add(factura.fecha_vencimiento, interval 30 day) as 'fechaGracia',
                        cliente.nombre as 'nombre',
                        factura.total as 'total'
                    from factura
                        inner join cliente
                        on factura.cliente_id = cliente.id
                        inner join tipo_pago_venta
                        on factura.tipo_pago_id = tipo_pago_venta.id
                        inner join users
                        on factura.vendedor = users.id
                        cross join (select @i := 0) r
                    where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) )
                    and (DATE_FORMAT(factura.fecha_emision,'%m') = ".$mes.")
                    and factura.pendiente_cobro > 0
                    and factura.estado_venta_id = 1
                    and factura.vendedor = ".$idVendedor."
                    order by factura.created_at desc;
                ");
                //dd($listaFacturas);
                return Datatables::of($listaFacturas)
                ->addColumn('estadoPago', function ($listaFacturas) {
                    return

                    '<span class="badge badge-danger">SIN CERRAR</span>';
                })
                ->addColumn('comision', function ($listaFacturas) {
                    return

                    '<span class="badge badge-danger">Sin comisionar</span>';
                })
                ->addColumn('acciones', function ($listaFacturas) {
                        return

                        '<div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                <li>
                                    <a class="dropdown-item" href="/detalle/venta/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de Factura </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/desglose/factura/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Desglose  </a>
                                </li>

                            </ul>
                        </div>';
                })
                ->rawColumns(['estadoPago','comision','acciones'])
                ->make(true);

            } catch (QueryException $e) {
                return response()->json([
                    'message' => 'Ha ocurrido un error al listar las facturas.',
                    'errorTh' => $e,
                ], 402);

            }

    }
}
