<?php

namespace App\Http\Livewire\Ventas;

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

class LitsadoFacturasVendedor extends Component
{
    public function render()
    {
        return view('livewire.ventas.litsado-facturas-vendedor');
    }

    public function listarFacturasVendedor(){

        try {

            $listaFacturas = DB::SELECT("
            select
            factura.id as id,
            @i := @i + 1 as contador,
            numero_factura,
            cai,
            fecha_emision,
            factura.nombre_cliente as nombre,
            tipo_pago_venta.descripcion,
            fecha_vencimiento,
            sub_total,
            isv,
            total,
            factura.credito,
            users.name as creado_por,
            (select if(sum(monto) is null,0,sum(monto)) from pago_venta where estado_venta_id = 1   and factura_id = factura.id ) as monto_pagado,
            factura.estado_venta_id,
            factura.created_at as fecha_registro

        from factura
            inner join cliente
            on factura.cliente_id = cliente.id
            inner join tipo_pago_venta
            on factura.tipo_pago_id = tipo_pago_venta.id
            inner join users
            on factura.vendedor = users.id
            cross join (select @i := 0) r
        where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) ) and factura.estado_venta_id<>2 and (factura.tipo_venta_id = 1) and factura.vendedor = ".Auth::user()->id."
        order by factura.created_at desc
            ");

            return Datatables::of($listaFacturas)
            ->addColumn('opciones', function ($listaFacturas) {

                if($listaFacturas->estado_venta_id==2){
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" href="/detalle/venta/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/factura/cooporativo/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Factura </a>
                            </li>




                        </ul>
                    </div>';
                }else{
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" href="/detalle/venta/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                            </li>



                            <li>
                            <a class="dropdown-item" target="_blank"  href="/factura/cooporativo/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Factura </a>
                            </li>



                        </ul>
                    </div>';
                }
            })
            ->addColumn('estado_cobro', function ($listaFacturas) {
                /* if($listaFacturas->estado_venta_id==2){

                    return
                    '
                    <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Anulado</span></p>
                    ';

                }elseif(round($listaFacturas->monto_pagado,2) >= str_replace(",","",$listaFacturas->total)){

                    return
                    '

                    <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Completo</span></p>
                    ';

                }else{
                    return
                    '
                    <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Pendiente</span></p>
                    ';
                } */


                $revision = DB::SELECTONE("
                    select count(*) as valida
                    from aplicacion_pagos
                    where aplicacion_pagos.estado = 1
                    and aplicacion_pagos.estado_cerrado = 2
                    and aplicacion_pagos.factura_id =
                    ".$listaFacturas->id);


                    if(  $revision->valida == 1){

                        return
                        '

                        <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Cerrada</span></p>
                        ';

                    }else{
                        return
                        '
                        <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Pendiente</span></p>
                        ';
                    }
           })
            ->rawColumns(['opciones','estado_cobro'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las compras.',
                'errorTh' => $e,
            ], 402);

        }

    }
}
