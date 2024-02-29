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

use App\Models\ModelFactura;
use App\Models\ModelLogEstadoFactura;
use App\Models\ModelRecibirBodega;
use App\Models\ModelLogTranslados;

class ListadoFacturasND extends Component
{
    public function render()
    {
        return view('livewire.ventas.listado-facturas-nd');
    }

    public function listarFacturas(){

        try {

            $listaFacturas = DB::SELECT("
            select
                factura.id as id,
                @i := @i + 1 as contador,
                numero_factura,
                factura.cai as correlativo,
                A.cai as cai,
                fecha_emision,
                factura.nombre_cliente as nombre,
                tipo_pago_venta.descripcion,
                fecha_vencimiento,
                FORMAT(sub_total,2) as sub_total,
                FORMAT(isv,2) as isv,
                FORMAT(total,2) as total,
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
                inner join cai A
                on factura.cai_id= A.id
                cross join (select @i := 0) r
            where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) )and factura.estado_factura_id=2 and factura.estado_venta_id<>2 and (factura.tipo_venta_id = 1)
            order by factura.created_at desc
            ");

            return Datatables::of($listaFacturas)
            ->addColumn('opciones', function ($listaFacturas) {


                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" href="/detalle/venta/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                            </li>


                            <li>
                            <a class="dropdown-item" target="_blank"  href="/factura/cooporativo/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Factura Original </a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/factura/cooporativoCopia/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Factura Copia </a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/facturaCoor/actaRec/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Acta de Recepción </a>
                            </li>

                            <li>
                            <a class="dropdown-item"  onclick="anularVentaConfirmar('.$listaFacturas->id.')" > <i class="fa-solid fa-ban text-danger"></i> Anular Factura </a>

                            </li>

                            <li>
                            <a class="dropdown-item" href="/crear/vale/'.$listaFacturas->id.'" > <i class="fa-solid fa-calendar-days text-success"></i> Agendar Entrega </a>
                            </li>


                        </ul>
                    </div>';

            })
            ->addColumn('estado_cobro', function ($listaFacturas) {


                /*   if(round($listaFacturas->monto_pagado,2) >= str_replace(",","",$listaFacturas->total)){

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
