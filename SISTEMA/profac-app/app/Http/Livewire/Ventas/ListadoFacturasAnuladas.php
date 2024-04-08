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


class ListadoFacturasAnuladas extends Component
{
    public $tipoFactura;

    public function mount($id)
    {

        $this->tipoFactura = $id;
    }

    public function render()

    {
        $tipoFactura = $this->tipoFactura;
        $nombreTipo = "";
        $idTipoVenta = null;

        switch ($tipoFactura) {
            case "corporativo":
                $nombreTipo = 'Cliente Corporativo';
                $idTipoVenta = 1;
                break;
            case 'estatal':
                $nombreTipo = 'Cliente Estatatal';
                $idTipoVenta = 2;
                break;
            case 'exonerado':
                $nombreTipo = 'Cliente Exonerado';
                $idTipoVenta = 3;
                break;       
            

        }

        return view('livewire.ventas.listado-facturas-anuladas',compact('nombreTipo','idTipoVenta'));
    }

    public function listarFacturas(Request $request){

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
                format(sub_total,2) as sub_total,
                format(isv,2) as isv,
                format(total,2) as total,
                factura.credito,
                users.name as creado_por,
                (select if(sum(monto) is null,0,sum(monto)) from pago_venta where estado_venta_id = 1   and factura_id = factura.id ) as monto_pagado,
                factura.estado_venta_id,
                factura.tipo_venta_id,
                factura.created_at as fecha_registro
            from factura
                inner join cliente
                on factura.cliente_id = cliente.id
                inner join tipo_pago_venta
                on factura.tipo_pago_id = tipo_pago_venta.id
                inner join users
                on factura.vendedor = users.id
                cross join (select @i := 0) r
            where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) ) and estado_venta_id=2  and (factura.tipo_venta_id = ".$request->idTipo.")
            order by factura.created_at desc
            ");

            return Datatables::of($listaFacturas)
            ->addColumn('opciones', function ($listaFacturas) {

                if($listaFacturas->tipo_venta_id==3){
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
    
                            <li>
                                <a class="dropdown-item" href="/detalle/venta/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                            </li>
    

                            
                            <li>
                            <a class="dropdown-item" target="_blank"  href="/exonerado/factura/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-success"></i> Imprimir Factura </a>
                            </li>   
                            <li>
                            <a class="dropdown-item" target="_blank"  onclick="detallesDeAnulacion('.$listaFacturas->id.')"> <i class="fa-solid fa-magnifying-glass-plus text-warning"></i> Detalle de Anulación </a>
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
                            <a class="dropdown-item" target="_blank"  href="/factura/cooporativo/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-success"></i> Imprimir Factura </a>
                            </li>   
                            <li>
                            <a class="dropdown-item" target="_blank"  onclick="detallesDeAnulacion('.$listaFacturas->id.')"> <i class="fa-solid fa-magnifying-glass-plus text-warning"></i> Detalle de Anulación </a>
                            </li>     
      
                          
                        </ul>
                    </div>';
                }
              


            })
            ->addColumn('estado_cobro', function ($listaFacturas) {
               

                    return
                    '
                    <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Anulado</span></p>
                    ';

                

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

    public function detalleFacturaAnulada(Request $request){
        try {

            $datos = DB::SELECTONE("
            select 
            A.cai,
            A.id as codigo_factura,
            B.motivo,
            CONCAT(users.name,' en fecha ',B.created_at) as usuario
            from factura A
            inner join log_estado_factura B
            on A.id = B.factura_id
            inner join users 
            ON users.id = B.users_id
            where A.id =".$request->id 
        );
            

        return response()->json([
            'datos' => $datos,

        ],200);
        } catch (QueryException $e) {
        return response()->json([
            'icon' => 'error',
            'text' => 'Ha ocurrido un error al obtener los detalles de la anulacion',
            'title' => 'Error!',
            'message' => 'Ha ocurrido un error', 
            'error' => $e,
        ],402);
        }
    }
}
