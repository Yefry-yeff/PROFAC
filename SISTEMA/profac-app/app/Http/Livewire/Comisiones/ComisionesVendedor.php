<?php

namespace App\Http\Livewire\Comisiones;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;

class ComisionesVendedor extends Component
{
    public function render()
    {
        return view('livewire.comisiones.comisiones-vendedor');
    }

    public function historicoPagos(){
        //dd("entro al historico pago");
        try {
            $listaPagosComisiones = DB::SELECT("

                select
                    vendedor_id,
                    nombre_vendedor,
                    mes_comision,
                    meses_id,
                    cantidad_facturas,
                    techo_asignado,
                    ganancia_total,
                    monto_asignado,
                    (select name from users WHERE id = users_registra_id ) as users_registra_id,
                    created_at
                from pago_comision
                where estado_pago = 1
                and  vendedor_id =".Auth::user()->id);
            return Datatables::of($listaPagosComisiones)
            ->rawColumns([])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las comisones.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function obtenerFacturas(){
        //dd($mes,$idVendedor);



            try {
                $listaFacturas = DB::SELECT("

                    select
                    factura.id as 'id',
                    (select nombre from meses where id = DATE_FORMAT(factura.fecha_emision,'%m')) as mesFactura,
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
                and factura.pendiente_cobro <= 0
                and factura.estado_venta_id = 1
                and factura.comision_estado_pagado = 0
                and factura.vendedor = ".Auth::user()->id."
                order by factura.created_at desc;


                ");
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

    public function obtenerFacturasSinCerrar(){
        try {
            $listaFacturas = DB::SELECT("

                select
                factura.id as 'id',
                (select nombre from meses where id = DATE_FORMAT(factura.fecha_emision,'%m')) as mesFactura,
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
            and factura.pendiente_cobro > 0
            and factura.estado_venta_id = 1
            and factura.vendedor = ".Auth::user()->id."
            order by factura.created_at desc;

            ");
            return Datatables::of($listaFacturas)
            ->addColumn('estadoPago', function ($listaFacturas) {
                return

                '<span class="badge badge-danger">SIN CERRADA</span>';
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

