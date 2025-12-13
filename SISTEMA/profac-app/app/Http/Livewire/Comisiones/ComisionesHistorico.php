<?php

namespace App\Http\Livewire\Comisiones;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use App\Models\Comisiones\desglose;
use App\Models\Comisiones\facturas_pagadas;
use App\Models\Comisiones\pago_comision;
use App\Models\Comisiones\comision;

class ComisionesHistorico extends Component
{
    public function render()
    {
        return view('livewire.comisiones.comisiones-historico');
    }

    public function listarHistorico(){

        //dd("llego al historico");
        try {
            $listaComisiones = DB::SELECT("

                SELECT
                    co.comision_techo_id as codigoComision,
                    co.factura_id as idFactura,
                    co.vendedor_id,
                    f.numero_factura,
                    us.name as vendedor,
                    (select meses.nombre from meses where id = (
                        select comision_techo.meses_id from comision_techo
                        where  comision_techo.vendedor_id = co.vendedor_id
                        and comision_techo.id = co.comision_techo_id
                                                                        )
                    ) as mes,
                    FORMAT(co.gananciaTotal,2) as gananciaFactura,
                    co.porcentaje,
                    FORMAT(co.monto_comison,2) as montoAsignado,
                    usA.name as userRegistro,
                    co.created_at as fechaRegistro
                FROM comision co
                    inner join users us on (us.id = co.vendedor_id)
                    inner join users usA on (usA.id = co.users_registro_id)
                    inner join factura f on (f.id = co.factura_id)
                where co.estado_id = 1;

            ");
            return Datatables::of($listaComisiones)
            ->addColumn('acciones', function ($listaComisiones) {
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" href="/desglose/factura/'.$listaComisiones->idFactura.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Ver Desglose </a>
                            </li>

                        </ul>
                    </div>';
            })
            ->rawColumns(['acciones'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las comisones.',
                'errorTh' => $e,
            ], 402);

        }

    }

    public function historicoMes(){



        try {
            $listaComisiones = DB::SELECT("

            select
                codigoVendedor,
                vendedor,
                mes,
                facturasComisionadas,
                montotecho as montotecho,
                sum(gananciaTotal) as gananciatotalMes,
                sum(montoAsignado)  as montoAsignado from (
                                select
                                    co.id as codigoComision,
                                    co.vendedor_id as codigoVendedor,
                                    us.name as vendedor,
                                    co.comision_techo_id as techo,
                                    SUM(co.monto_comison) as montoAsignado,
                                    co.gananciaTotal as gananciaTotal,
                                    (
                                        select count(comision.factura_id) from comision
                                        inner join comision_techo on (comision_techo.id = comision.comision_techo_id)
                                        where comision_techo.meses_id = m.id
                                        and comision_techo.vendedor_id = co.vendedor_id

                                    ) as facturasComisionadas,
                                    m.nombre as mes,
                                    ct.monto_techo as montotecho
                                from comision co
                                inner join users us on (us.id = co.vendedor_id)
                                inner join comision_techo ct on (ct.id = co.comision_techo_id)
                                inner join meses m on (m.id = ct.meses_id)
                                where co.estado_id = 1 and ct.estado_id = 1 and co.vendedor_id NOT IN (select vendedor_id from pago_comision where meses_id = ct.meses_id)
                                group by co.id, us.name, co.comision_techo_id, co.monto_comison,facturasComisionadas, m.nombre, ct.monto_techo
                            ) as comisiones
                            group by codigoVendedor, vendedor, mes, facturasComisionadas,montotecho

            ");
            return Datatables::of($listaComisiones)
            ->addColumn('estadoPago', function ($listaComisiones) {
                return

                '<span class="badge badge-primary">No pagado</span>';
            })
            ->addColumn('acciones', function ($listaComisiones) {

                $idMes = DB::SELECTONE("select id from meses where nombre = '".$listaComisiones->mes."'");
                //dd($listaComisiones->vendedor);
                $comilla = "'";
                return

                '
                <div class="modal fade" id="modal_pago_vendedor_'.$listaComisiones->codigoVendedor.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Registro de Techos de Comisiones</h3>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="pagarComisionForm_'.$listaComisiones->codigoVendedor.'" name="pagarComisionForm_'.$listaComisiones->codigoVendedor.'" data-parsley-validate>
                                    <Label>Nota: Al registrar éste pago, se debe registrar un sólo pago, el pago completo del monto indicado en la comisión asignada.</Label>
                                    <div class="row" id="row_datos">
                                            <div class="col-md-12">
                                                <label  class="col-form-label focus-label">Código Vendedor: <span class="text-danger">*</span></label>
                                                <input readonly class="form-control" required type="number" id="idVendedor" name="idVendedor" value="'.$listaComisiones->codigoVendedor.'"  data-parsley-required >
                                            </div>
                                            <div class="col-md-12">
                                                <label  class="col-form-label focus-label">Vendedor: <span class="text-danger">*</span></label>
                                                <input readonly class="form-control" required type="text" id="vendedor" name="vendedor" value="'.$listaComisiones->vendedor.'" data-parsley-required >
                                            </div>
                                            <div class="col-md-12">
                                                <label  class="col-form-label focus-label">Mes de Comisión: <span class="text-danger">*</span></label>
                                                <input readonly class="form-control" required type="text" id="mes" name="mes" value="'.$listaComisiones->mes.'" data-parsley-required >
                                                <input  type="hidden"  id="idMes" name="idMes" value="'.$idMes->id.'" >
                                            </div>
                                            <div class="col-md-12">
                                                <label  class="col-form-label focus-label">Cantidad de facturas comisionadas: <span class="text-danger">*</span></label>
                                                <input readonly class="form-control" required type="text"  id="facturasComisionadas" name="facturasComisionadas" value="'.$listaComisiones->facturasComisionadas.'" data-parsley-required >
                                            </div>
                                            <div class="col-md-12">
                                                <label  class="col-form-label focus-label">Techo de comisión asignado: <span class="text-danger">*</span></label>
                                                <input readonly class="form-control" required type="text"  id="montotecho" name="montotecho" value="'.$listaComisiones->montotecho.'" data-parsley-required >
                                            </div>
                                            <div class="col-md-12">
                                                <label  class="col-form-label focus-label">Ganancia total de facturas cerradas: <span class="text-danger">*</span></label>
                                                <input readonly class="form-control" required type="text"  id="gananciatotalMes" name="gananciatotalMes" value="'.$listaComisiones->gananciatotalMes.'"  data-parsley-required >
                                            </div>
                                            <div class="col-md-12">
                                                <label  class="col-form-label focus-label">Monto asignado de comisión a pagar: <span class="text-danger">*</span></label>
                                                <input readonly class="form-control" required type="text"  id="montoAsignado" name="montoAsignado" value="'.$listaComisiones->montoAsignado.'" data-parsley-required >
                                            </div>
                                    </div>
                                </form>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                <button type="button" onclick="registrarPago()" class="btn btn-primary">Guardar pago</button>
                            </div>
                        </div>
                    </div>
                </div>











                <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" data-toggle="modal" data-target="#modal_pago_vendedor_'.$listaComisiones->codigoVendedor.'" onclick="asignacion('.$comilla.'modal_pago_vendedor_'.$listaComisiones->codigoVendedor.''.$comilla.','.$comilla.'pagarComisionForm_'.$listaComisiones->codigoVendedor.''.$comilla.')" > <i class="fa-solid fa-cash-register text-info"></i> Pago de comisión </a>
                            </li>

                        </ul>
                </div>';
            })
            ->rawColumns(['estadoPago','acciones'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las comisones.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function pagoComision(Request $request){
        try {
            //dd($request);
            DB::beginTransaction();

                        $pago_comision = new pago_comision;
                        $pago_comision->vendedor_id = $request->idVendedor;
                        $pago_comision->nombre_vendedor = $request->vendedor;
                        $pago_comision->mes_comision = $request->mes;
                        $pago_comision->meses_id = $request->idMes;
                        $pago_comision->cantidad_facturas = $request->facturasComisionadas;
                        $pago_comision->techo_asignado = $request->montotecho;
                        $pago_comision->ganancia_total = $request->gananciatotalMes;
                        $pago_comision->monto_asignado = $request->montoAsignado;
                        $pago_comision->estado_pago = 1;
                        $pago_comision->url_comprobante = "url";
                        $pago_comision->users_registra_id = Auth::user()->id;
                        $pago_comision->save();


                $facturasComisionadas = DB::SELECT("
                    select comision.factura_id as idFactura, comision.id as idComision from comision
                    inner join comision_techo on (comision.comision_techo_id = comision_techo.id)
                    where comision.vendedor_id = ".$request->idVendedor." and comision_techo.meses_id = ".$request->idMes."
                ");

                foreach ($facturasComisionadas  as $value) {
                    $facturas_pagadas = new facturas_pagadas;
                    $facturas_pagadas->factura_id = $value->idFactura;
                    $facturas_pagadas->comision_id = $value->idComision;
                    $facturas_pagadas->estado_pagado = 1;
                    $facturas_pagadas->save();
                }
            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Pago registrado con éxito!",
                "title"=>"Exito!"
            ],200);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al registrar el pago completo.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

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

            ");
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
}
