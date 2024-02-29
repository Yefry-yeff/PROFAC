<?php

namespace App\Http\Livewire\Vale;

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

class ListadoFacturasVale extends Component
{
    public function render()
    {
        return view('livewire.vale.listado-facturas');
    }

    public function listarFacturasVale(){

        try {

            $listaFacturas = DB::SELECT("
            select
            factura.id as id,
            @i := @i + 1 as contador,
            numero_factura,
            factura.cai as correlativo,
            A.cai as cai,
            fecha_emision,
            cliente.nombre,
            tipo_pago_venta.descripcion,
            fecha_vencimiento,
            FORMAT(factura.sub_total,2) as sub_total,
            FORMAT(factura.isv,2) as isv,
            FORMAT(factura.total,2) as total,
            factura.credito,
            users.name as creado_por,
            (select if(sum(monto) is null,0,sum(monto)) from pago_venta where estado_venta_id = 1   and factura_id = factura.id ) as monto_pagado,
            factura.estado_venta_id,
            factura.estado_id_vale

        from (
            select factura.id, factura.cai,factura.sub_total,factura.isv,factura.total,
              factura.credito,factura.estado_venta_id, factura.cliente_id,factura.tipo_pago_id,
              factura.vendedor,factura.cai_id,factura.created_at,numero_factura,fecha_emision, fecha_vencimiento, vale.estado_id as estado_id_vale
            from factura
              left join vale
              on factura.id = vale.factura_id

            ) as factura
            inner join cliente
            on factura.cliente_id = cliente.id
            inner join tipo_pago_venta
            on factura.tipo_pago_id = tipo_pago_venta.id
            inner join users
            on factura.vendedor = users.id
            inner join cai A
            on factura.cai_id= A.id
            cross join (select @i := 0) r
        where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) ) and factura.estado_venta_id<>2 and (factura.estado_id_vale = 5 or factura.estado_id_vale IS NULL)
        order by factura.id desc
            ");

            return Datatables::of($listaFacturas)
            ->addColumn('opciones', function ($listaFacturas) {


                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" href="/crear/vale/lista/espera/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Agregar vale a factura </a>
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
