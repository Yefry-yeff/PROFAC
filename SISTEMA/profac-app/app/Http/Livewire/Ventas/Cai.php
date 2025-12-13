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
use App\Models\ModelCAI;


class Cai extends Component
{
    public function render()
    {
        return view('livewire.ventas.cai');
    }

    public function listarCAI ()
    {

        try{

            $cais = DB::SELECT("select
            cai.id,
            cai.cai,
            cai.fecha_limite_emision,
            tipo_documento_fiscal.descripcion,
            cai.cantidad_otorgada,
            cai.numero_inicial,
            cai.numero_final
            from cai
            inner join tipo_documento_fiscal
            on cai.tipo_documento_fiscal_id = tipo_documento_fiscal.id
            where cai.estado_id = 1
            ");

            return Datatables::of($cais)
                    ->addColumn('opciones', function ($cai) {

                        return

                        '
                        <div class="btn-group ">
                            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                                <ul class="dropdown-menu" x-placement="bottom-start"
                                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                                    <li>
                                        <a class="dropdown-item" onclick="datosCAI('.$cai->id.')" >
                                            <i class="fa-solid fa-arrows-to-eye text-info"></i> Editar
                                        </a>
                                    </li>
                                    <li>
                                    <a class="dropdown-item"   ><i class="fa-solid fa-xmark text-danger"></i> Desactivar </a>
                                    </li>

                                </ul>
                        </div>
                        ';
                    })


                    ->rawColumns(['opciones'])
                    ->make(true);
            } catch (QueryException $e) {


                return response()->json([
                    'message' => 'Ha ocurrido un error al listar los CAI.',
                    'errorTh' => $e,
                ], 402);
            }

    }

    public function guardarCAI(Request $request){

        try {

            $validator = Validator::make($request->all(), [
                'tipo_documento_fiscal_id' => 'required',
                'cai' => 'required',
                'fecha_limite' => 'required',
                'cantidad_otorgada' => 'required',
                'cantidad_solicitada' => 'required',
                'numero_inicial' => 'required',
                'numero_final' => 'required',
                'punto_emision' => 'required',


            ], [
                'tipo_documento_fiscal_id' => 'Tipo Documento es requerido',
                'cai' => 'CAI es requerido',
                'fecha_limite' => 'Fecha Limite es requerida',
                'cantidad_otorgada'=>'Cantidad obligatoria',
                'cantidad_solicitada' => 'Cantidad obligatoria',
                'numero_inicial'=>'Numero Inicial obligatorio',
                'numero_final' => 'Numero Final obligatorio',
                'punto_emision'=>'Punto de Emision obligatorio'


            ]);


            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',
                    'errors' => $validator->errors()
                ], 402);
            }

            $verifica = DB::select("select
            cai
            from factura
            where estado_venta_id = 1 and estado_editar=1");
            //dd($verifica);
            // if (empty($verifica)) {

            // } else {
            //     return response()->json([
            //         'icon'=>'error',
            //         'title'=>'Error!',
            //         'text'=>'Aún hay facturas pendientes de cierre.',
            //         'message' => 'Ha ocurrido un error'
            //        ],402);
            // }


            //////////////////////Establecer el Cai activo a inactivo///////////////////

            $getCai = DB::SELECTONE("select
            id
            from cai
            where estado_id = 1 and tipo_documento_fiscal_id=".$request->tipo_documento_fiscal_id);

            if ( empty($getCai->id) ) {

            } else {

                $setCai = ModelCAI::find($getCai->id);
                $setCai->estado_id = 2;

                $setCai->update();
            }

            ////////////////////////////Explode NumeroInicial Seteo/////////////////
            $arrayNumeroInicial = explode('-', $request->numero_inicial);
            $numero_inicial_set= $arrayNumeroInicial[3];

            ///////////////////////////Explode NumeroFinal Seteo////////////////////
            $arrayNumeroFinal = explode('-', $request->numero_final);
            $numero_final_set= $arrayNumeroFinal[3];
            ////////////////////////////////////////////////////////////////////////

            ////////////////////////////Explode NumeroBase//////////////////////////
            $arrayCai = explode('-', $request->numero_final);
            $numero_base= $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2];
            ////////////////////////////////////////////////////////////////////////
            //Resta de rangos = cantidad no utilizada

            $cantidad_no_utilizada_set = (string)((int)($numero_final_set)) - (string)((int)($numero_inicial_set));

            ////////////////////////////////////////////////////////////////////////


            $cai = new ModelCAI;

            $cai->cai = $request->cai;
            $cai->punto_de_emision = $request->punto_emision;
            $cai->cantidad_solicitada = $request->cantidad_solicitada;
            $cai->cantidad_otorgada = $request->cantidad_otorgada;
            $cai->numero_actual = (string)((int)($numero_inicial_set)); //ltrim($request->numero_inicial, '0');
            $cai->serie = (string)((int)($numero_inicial_set)); //ltrim($request->numero_inicial, '0');
            $cai->cantidad_no_utilizada = $cantidad_no_utilizada_set;
            $cai->numero_inicial = $request->numero_inicial;
            $cai->numero_final = $request->numero_final;


            //dd($request->numero_final);
            ////////////////////////Numero Base//////////////////////////////////////////
            $cai->numero_base = $numero_base;
            /////////////////////////////////////////////////////////////////////////////
            $cai->fecha_limite_emision = $request->fecha_limite;
            $cai->tipo_documento_fiscal_id =$request->tipo_documento_fiscal_id;
            $cai->estado_id = 1;
            $cai->users_id = Auth::user()->id;

            $cai->save();


            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'CAI guardado con exito.'
            ],200);

        } catch (QueryException $e) {

        return response()->json([
         'icon'=>'error',
         'title'=>'Error!',
         'text'=>'Ha ocurrido un error, intente de nuevo.',
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }

    }

    public function datosCAI( Request $request){
        try {

         $datos = DB::SELECTONE("select id, cai, fecha_limite_emision, cantidad_otorgada, cantidad_solicitada, numero_inicial, numero_final, punto_de_emision from cai where id=".$request->id);

        return response()->json([
         "datos"=>$datos,
        ],200);
        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
     }

     public function editarCAI(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'idCAI' => 'required',
                'cai_editar' => 'required',
                'fecha_limite_editar' => 'required',
                'cantidad_otorgada_editar' => 'required',
                'cantidad_solicitada_editar' => 'required',
                'numero_inicial_editar' => 'required',
                'numero_final_editar' => 'required',
                'punto_emision_editar' => 'required',


            ], [
                'idCAI' => 'ID es requerido',
                'cai_editar' => 'CAI es requerido',
                'fecha_limite_editar' => 'Fecha Limite es requerida',
                'cantidad_otorgada_editar'=>'Cantidad obligatoria',
                'cantidad_solicitada_editar' => 'Cantidad obligatoria',
                'numero_inicial_editar'=>'Numero Inicial obligatorio',
                'numero_final_editar' => 'Numero Final obligatorio',
                'punto_emision_editar'=>'Punto de Emision obligatorio'


            ]);


            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',
                    'errors' => $validator->errors()
                ], 402);
            }

            ///////////////////////////Explode NumeroFinal Seteo
            $arrayNumeroFinal_update = explode('-', $request->numero_final_editar);
            $numero_final_set= $arrayNumeroFinal_update[3];

            ////////////////////////////Explode NumeroInicial Seteo//////////////////////////
            $arrayNumeroInicial_update = explode('-', $request->numero_inicial_editar);
            $numero_inicial_set= $arrayNumeroInicial_update[3];
            ////////////////////////////////////////////////////////////////////////

            ////////////////////////////////////////////////////////////////////////
            //Resta de rangos = cantidad no utilizada

            $cantidad_no_utilizada_set_update = (string)((int)($numero_final_set)) - (string)((int)($numero_inicial_set));

            ////////////////////////////////////////////////////////////////////////

            $cai_update =  ModelCAI::find($request->idCAI);




            switch ($cai_update->tipo_documento_fiscal_id) {
                case 1:
                    $consultaFacturas = DB::SELECTONE("select count(id) as contador from factura where cai_id = ".$request->idCAI);
                    if($consultaFacturas->contador > 0){
                        return response()->json([
                            'icon'=>'error',
                            'title'=>'Error!',
                            'text'=>'Ya exiten documentos con este número de cai.'
                       ],401);
                    }

                    break;
                case 3:
                    $contadorNotaCredito = DB::SELECTONE("select count(id) as contador from nota_credito where cai_id =".$request->idCAI);
                    if($contadorNotaCredito->contador > 0){
                        return response()->json([
                            'icon'=>'error',
                            'title'=>'Error!',
                            'text'=>'Ya exiten documentos con este número de cai.'
                       ],401);
                    }
                    break;
                case 4:
                    $contadorNotaDebito = DB::SELECTONE("select count(id) as contador  from notadebito where cai_ndebito =".$request->idCAI);
                    if($contadorNotaDebito->contador > 0){
                        return response()->json([
                            'icon'=>'error',
                            'title'=>'Error!',
                            'text'=>'Ya exiten documentos con este número de cai.'
                       ],401);
                    }
                    break;
            }

            $cai_update->cai = $request->cai_editar;
            $cai_update->punto_de_emision = $request->punto_emision_editar;
            $cai_update->cantidad_solicitada = $request->cantidad_solicitada_editar;
            $cai_update->cantidad_otorgada = $request->cantidad_otorgada_editar;
            $cai_update->cantidad_no_utilizada = $cantidad_no_utilizada_set_update;
            $cai_update->numero_inicial = $request->numero_inicial_editar;
            $cai_update->numero_final = $request->numero_final_editar;
            $cai_update->numero_actual = (string)((int)($numero_inicial_set));
            $cai_update->serie = (string)((int)($numero_inicial_set));
            /////////////////////////////////Numero Base/////////////////////////////
            $arrayCai = explode('-', $request->numero_final_editar);
            $numero_base= $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2];
            /////////////////////////////////////////////////////////////////////////
            $cai_update->numero_base = $numero_base;
            /////////////////////////////////////////////////////////////////////////////
            $cai_update->fecha_limite_emision = $request->fecha_limite_editar;
            $cai_update->users_id = Auth::user()->id;

            $cai_update->update();


            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'CAI actualizado con exito.'
            ],200);

        } catch (QueryException $e) {

        return response()->json([
         'icon'=>'error',
         'title'=>'Error!',
         'text'=>'Ha ocurrido un error, intente de nuevo.',
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }

    }

    public function verificacionEstadosCai( Request $request){
        try {

            $verifica = DB::table('factura')
                            ->select('factura.cai', )
                            ->where('factura.estado_venta_id', '=', 1)
                            ->where('factura.estado_editar', '=', 1)
                            ->get();

            if (empty($verifica)) {
                $permiso = true;
            } else {
                $permiso = false;
            }


         //$verificacion = DB::SELECTONE("select id, cai, fecha_limite_emision, cantidad_otorgada, cantidad_solicitada, numero_inicial, numero_final, punto_de_emision from cai where id=".$request->id);

        return response()->json([
         "permiso"=>$permiso,
        ],200);
        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
     }

}
