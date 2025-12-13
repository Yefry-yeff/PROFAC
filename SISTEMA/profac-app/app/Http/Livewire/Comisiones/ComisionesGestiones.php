<?php

namespace App\Http\Livewire\Comisiones;

use Livewire\Component;
use App\Models\Comisiones\comision_techo;


use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;

class ComisionesGestiones extends Component
{
    public function render()
    {
        return view('livewire.comisiones.comisiones-gestiones');
    }

    public function obtenerVendedores(){
        //dd($mes,$idVendedor);
            try {
                $listaVendedores = DB::SELECT("
                    select
                    users.id as 'id',
                    meses.nombre as 'mes',
                    users.name as 'vendedor',
                    comision_techo.monto_techo as 'techo',
                    comision_techo.created_at as 'fechaRegistro',
                    usuario.name as 'userRegistro'
                    from comision_techo
                        inner join users on (users.id = comision_techo.vendedor_id)
                        inner join meses on (meses.id = comision_techo.meses_id)
                        inner join users as usuario on (usuario.id = comision_techo.userRegistro)
                    where comision_techo.estado_id = 1 order by comision_techo.created_at desc;
                ");
               // dd($listaVendedores);
                return Datatables::of($listaVendedores)
                ->addColumn('acciones', function ($listaVendedores) {
                        $mesL = "'".$listaVendedores->mes."'";
                        return

                        '<div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                más</button>
                            <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                <li>
                                    <a class="dropdown-item" onclick="editarTecho('.$listaVendedores->id.','.$mesL.','.$listaVendedores->techo.')"> <i class="fa-solid fa-arrows-to-eye text-info"></i> Editar Techo </a>
                                </li>

                            </ul>
                        </div>';
                })
                ->rawColumns(['estadoPago','acciones'])
                ->make(true);

            } catch (QueryException $e) {
                return response()->json([
                    'message' => 'Ha ocurrido un error al listar las los vendedores.',
                    'errorTh' => $e,
                ], 402);

            }
    }


    public function guardarTechoMasivo(Request $request){
        try {
            //dd();
            DB::beginTransaction();

                $techo = floatval($request->techo);
                $mes = strval($request->mes);
                //dd($techo);
                DB::update('
                    update
                        comision_techo
                    set estado_id = 2
                    where estado_id = 1 and meses_id= '.$mes.'
                ');
                /*
                $vendedores = DB::SELECT('
                SELECT
                        '.$techo.',
                        '.$request->mes.',
                        id,
                        1
                    FROM users
                    WHERE rol_id = 2
                '); */

                $idVendedores = DB::table('users')
                                    ->where('rol_id', 2)
                                    ->pluck('id');

                foreach ($idVendedores as $value) {

                    $techoComision = new comision_techo;
                    $techoComision->monto_techo = $techo;
                    $techoComision->vendedor_id =$value;
                    $techoComision->meses_id = $mes;
                    $techoComision->userRegistro = Auth::user()->id;
                    $techoComision->estado_id = 1;
                    $techoComision->save();
                }
                //dd($titles);
                //comision_techo::INSERT($vendedores);


            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Registro Actualización de techo correcto!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al registrar el techo masivo",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    public function editarTecho(Request $request){
        try {
            //dd();
            DB::beginTransaction();

                //dd($request);
                /*                 $prueba = '
                update
                    comision_techo
                set monto_techo = '.$request->nuevoTecho.'
                where comision_techo.vendedor_id = '.$request->idVendedor.'
                and comision_techo.meses_id = (select id from meses where meses.nombre ="'.$request->mesL.'")
                and estado_id = 1
            '; */
                $idmes = DB::SELECTONE('select id from meses where meses.nombre ="'.$request->mesL.'"');
                DB::update('
                    update
                        comision_techo
                    set monto_techo = '.$request->nuevoTecho.'
                    where comision_techo.vendedor_id = '.$request->idVendedor.'
                    and comision_techo.meses_id = '.$idmes->id.'
                    and estado_id = 1
                ');





                //dd($prueba);




            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Registro Actualización de techo correcto!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al editar el techo ",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }
}
