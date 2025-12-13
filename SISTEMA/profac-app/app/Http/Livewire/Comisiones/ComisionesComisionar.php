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

use App\Models\Comisiones\comision;
use App\Models\Comisiones\comision_temp;



class ComisionesComisionar extends Component
{
    public $idVenta;
    public function mount($id)
    {

        $this->idVenta = $id;
    }
    public function render()
    {
        $idFactura = $this->idVenta;

        $gananciaTotal = DB::SELECTONE("SELECT SUM(desglose.gananciatotal) AS gananciaTotal FROM desglose where estadoComisionado = 0 and idFactura = ".$idFactura);


        $idVendedor = DB::SELECTONE("SELECT desglose.vendedor_id as id FROM desglose where estadoComisionado = 0 and idFactura = ".$idFactura);

        $mesFactura = DB::SELECTONE("SELECT DATE_FORMAT(fecha_emision,'%m') as mes  from factura where id =".$idFactura);
        //dd();
        $centComisionado = DB::SELECTONE("SELECT factura.comision_estado_pagado as estado FROM factura where id =".$idFactura);
        //dd($idVendedor);

        return view('livewire.comisiones.comisiones-comisionar', compact('idFactura', 'gananciaTotal','idVendedor', 'mesFactura', 'centComisionado'));
        //return view('livewire.comisiones.comisiones-comisionar');
    }



    public function obtenerDesglose($id){
        //dd($id);
        try {
            $listaProd = DB::SELECT("
            SELECT * FROM desglose where idFactura = ".$id."
            ");
            return Datatables::of($listaProd)
            ->addColumn('acciones', function ($listaProd) {
                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" href="ir('.$listaProd->idFactura.')" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Ver </a>
                            </li>

                        </ul>
                    </div>';
            })
            ->rawColumns(['acciones'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar los productos.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function guardarComision(Request $request){
        try {

            DB::beginTransaction();
                //dd($request);

                $comisionMonto = (($request->porcentaje)/100)*$request->gananciaTotal;


                $idComisionTecho = DB::SELECTONE("select id from comision_techo where comision_techo.vendedor_id = ".$request->idVendedor." and comision_techo.meses_id = ".$request->mesFactura." and comision_techo.estado_id = 1");
                //dd($idComisionTecho);
                //dd($idComisionTecho);
                $comision = new comision;
                $comision->comision_techo_id = $idComisionTecho->id;
                $comision->factura_id = $request->factura;
                $comision->vendedor_id = $request->idVendedor;
                $comision->gananciaTotal = $request->gananciaTotal;
                $comision->porcentaje = $request->porcentaje;
                $comision->monto_comison = $comisionMonto;
                $comision->estado_id = 1;
                $comision->users_registro_id = Auth::user()->id;
                $comision->save();

                DB::update('
                    update
                    factura
                    set comision_estado_pagado = 1, monto_comision = '.$comisionMonto.'
                    where id = '.$request->factura.'
                ');

                DB::update('
                    update
                    desglose
                    set estadoComisionado = 1
                    where id = '.$request->factura.'
                ');

                DB::update('
                    update
                    desglose_temp
                    set estadoComisionado = 1
                    where id = '.$request->factura.'
                ');

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Registro de comisión hecho con éxito!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al registrar la comisión.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }


    public function guardarComisionMasivo(Request $request){
        try {

            DB::table('comision_temp')->truncate();

            DB::beginTransaction();
                $facturas_desglose_temp = DB::SELECT("select idFactura from desglose_temp
                group by idFactura;");

                $porcentaje = floatval((intval($request->porcentaje))/100);

                //dd($facturas_desglose_temp);
                foreach ($facturas_desglose_temp  as $value) {

                    $gananciaTotal_temp = DB::SELECTONE("SELECT SUM(desglose_temp.gananciatotal) AS gananciaTotal FROM desglose_temp where idFactura = ".$value->idFactura);

                    $idVendedor_temp = DB::SELECTONE("SELECT desglose_temp.vendedor_id as id FROM desglose_temp where idFactura = ".$value->idFactura);

                    $mesFactura_temp = DB::SELECTONE("SELECT DATE_FORMAT(fecha_emision,'%m') as mes  from factura where id =".$value->idFactura);

                    $centComisionado_temp = DB::SELECTONE("SELECT factura.comision_estado_pagado as estado FROM factura where id =".$value->idFactura);

                    $ganancia = floatval($gananciaTotal_temp->gananciaTotal);


                    $comisionMonto = $porcentaje*$ganancia;

                    $idComisionTecho = DB::SELECTONE("select id from comision_techo where comision_techo.vendedor_id = ".$idVendedor_temp->id." and comision_techo.meses_id = ".$mesFactura_temp->mes." and comision_techo.estado_id = 1");



                    //dd($idComisionTecho);


                    $comision_temp = new comision_temp;
                    $comision_temp->comision_techo_id = $idComisionTecho->id;
                    $comision_temp->factura_id = $value->idFactura;
                    $comision_temp->vendedor_id = $idVendedor_temp->id;
                    $comision_temp->gananciaTotal = $ganancia;
                    $comision_temp->porcentaje = $porcentaje;
                    $comision_temp->monto_comison = $comisionMonto;
                    $comision_temp->estado_id = 1;
                    $comision_temp->users_registro_id = Auth::user()->id;
                    $comision_temp->save();

                }

                $listaComisiones = DB::SELECTONE("

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
                                from comision_temp co
                                inner join users us on (us.id = co.vendedor_id)
                                inner join comision_techo ct on (ct.id = co.comision_techo_id)
                                inner join meses m on (m.id = ct.meses_id)
                                where co.estado_id = 1 and ct.estado_id = 1
                                group by co.id, us.name, co.comision_techo_id, co.monto_comison,facturasComisionadas, m.nombre, ct.monto_techo
                            ) as comisiones
                            group by codigoVendedor, vendedor, mes, facturasComisionadas,montotecho

                ");

                if( $listaComisiones->montoAsignado >  $listaComisiones->montotecho ){
                    return response()->json([
                        'message' => 'El monto total asignado a la comision para el vendedor '.$listaComisiones->vendedor.', es de L. '.$listaComisiones->montoAsignado.', lo que supera al techo asignado de L.'.$listaComisiones->montotecho.' para el mes de '.$listaComisiones->mes.'. Se le sugiere cambiar el monto de techo o disminuir el porcentaje de comisión',
                        'permiso' => 0
                    ], 200);
                }else{

                    foreach ($facturas_desglose_temp  as $value) {

                        $gananciaTotal_temp = DB::SELECTONE("SELECT SUM(desglose_temp.gananciatotal) AS gananciaTotal FROM desglose_temp where idFactura = ".$value->idFactura);

                        $idVendedor_temp = DB::SELECTONE("SELECT desglose_temp.vendedor_id as id FROM desglose_temp where idFactura = ".$value->idFactura);

                        $mesFactura_temp = DB::SELECTONE("SELECT DATE_FORMAT(fecha_emision,'%m') as mes  from factura where id =".$value->idFactura);

                        $centComisionado_temp = DB::SELECTONE("SELECT factura.comision_estado_pagado as estado FROM factura where id =".$value->idFactura);

                        $ganancia = floatval($gananciaTotal_temp->gananciaTotal);


                        $comisionMonto = $porcentaje*$ganancia;

                        $idComisionTecho = DB::SELECTONE("select id from comision_techo where comision_techo.vendedor_id = ".$idVendedor_temp->id." and comision_techo.meses_id = ".$mesFactura_temp->mes." and comision_techo.estado_id = 1");

                        $comision = new comision;
                        $comision->comision_techo_id = $idComisionTecho->id;
                        $comision->factura_id = $value->idFactura;
                        $comision->vendedor_id = $idVendedor_temp->id;
                        $comision->gananciaTotal = $ganancia;
                        $comision->porcentaje = $porcentaje;
                        $comision->monto_comison = $comisionMonto;
                        $comision->estado_id = 1;
                        $comision->users_registro_id = Auth::user()->id;
                        $comision->save();

                        DB::update('
                        update
                        factura
                        set comision_estado_pagado = 1, monto_comision = '.$comisionMonto.'
                        where id = '.$value->idFactura.'
                        ');

                        DB::update('
                        update
                        desglose
                        set estadoComisionado = 1
                        where id = '.$value->idFactura.'
                        ');

                        DB::update('
                            update
                            desglose_temp
                            set estadoComisionado = 1
                            where id = '.$value->idFactura.'
                        ');

                    }

                }

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Registro de comisión hecho con éxito!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al registrar la comisión.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }
}
