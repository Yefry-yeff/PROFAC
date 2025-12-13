<?php

namespace App\Http\Livewire\BodegaComponent;

use Livewire\Component;


use App\Models\User;
use App\Models\modelBodega;
use App\Models\Estante;
use App\Models\Repisa;
use App\Models\Seccion;
use App\Models\segmento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;

class BodegaEditar extends Component
{
    public function render()
    {
        return view('livewire.bodega-component.bodega-editar');
    }

    public function guardarSeccion(Request $request){
        DB::beginTransaction();
                $idBodega = $request['idBodega'];
                $idSegmento = $request['selectSegmento'];
                $numeracion = DB::selectOne("select numeracion from seccion where segmento_id = ".$idSegmento." order by numeracion desc");
                $letraSegmento = DB::selectOne("select descripcion from segmento where id = ".$idSegmento);

                if(empty($numeracion)){
                    $numero=0;
                }else{
                    $numero = $numeracion->numeracion;
                }


                $seccion = new Seccion;
                $seccion->descripcion = "Seccion ".$letraSegmento->descripcion.$numero+1;
                $seccion->numeracion = $numero+1;
                $seccion->estado_id = 1;
                $seccion->segmento_id = $idSegmento;
                $seccion->save();
        DB::commit();

        return response()->json([
            "message" => "Sección agregada con exito",

                ],200);
    }



    public function guardarSegmento(Request $request){
        DB::beginTransaction();
                $idBodega = $request['idBodegaS'];
                $LetraSegmento = $request['segmentoAdd'];


                $segmento = new segmento;
                $segmento->descripcion = $LetraSegmento;
                $segmento->bodega_id = $idBodega;
                $segmento->save();
        DB::commit();

        return response()->json([
            "message" => "Segmento agregado con exito",

                ],200);
    }

    public function listarBodegas(){
        try {
         $listaBodegas = DB::SELECT("
            select
                bodega.nombre as 'numero_bodega',
                bodega.id as 'codigo',
                bodega.direccion,
                users.name as 'encargado',
                estado.descripcion as 'estado',
                bodega.estado_id as 'estado_id'
                from bodega
                inner join users
                on users.id = bodega.encargado_bodega
                inner join estado
                on estado.id = bodega.estado_id

            ");

            return Datatables::of($listaBodegas)
            ->addColumn('opciones', function ($listaBodegas) {

                if($listaBodegas->estado_id == 1){

                    return '
                    <div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start"
                        style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                        <li><a class="dropdown-item" href="#" onclick="desactivarBodega('.$listaBodegas->codigo.')"> <i class="fa fa-times text-danger" aria-hidden="true"></i>
                                Desactivar </a></li>

                        <li><a class="dropdown-item" href="#" onclick="obtenerDatosBodega('.$listaBodegas->codigo.')"> <i
                            class="fa fa-pencil text-warning" aria-hidden="true"></i>
                        Editar </a></li>

                        <li><a class="dropdown-item" href="#" onclick="addSeccionJS('.$listaBodegas->codigo.')"> <i class="fa-solid fa-circle-plus text-success"></i>
                        Añadir Sección </a></li>

                        <li><a class="dropdown-item" href="#" onclick="addSegmentoJS('.$listaBodegas->codigo.')"> <i class="fa-solid fa-circle-plus text-success"></i>
                        Añadir Segmento </a></li>

                    </ul>
                </div>
                    ';

                }else{
                    return '
                    <div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start"
                        style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                        <li><a class="dropdown-item" href="#" onclick="activarBodega('.$listaBodegas->codigo.')"> <i class="fa fa-check-circle text-info" aria-hidden="true"></i>
                                Activar </a></li>

                                <li><a class="dropdown-item" href="#" onclick="obtenerDatosBodega('.$listaBodegas->codigo.')"> <i
                                class="fa fa-pencil text-warning" aria-hidden="true"></i>
                            Editar </a></li>

                            <li><a class="dropdown-item" href="#" onclick="addSeccionJS('.$listaBodegas->codigo.')"> <i class="fa-solid fa-circle-plus text-success"></i>
                        Añadir Sección </a></li>

                        <li><a class="dropdown-item" href="#" onclick="addSegmentoJS('.$listaBodegas->codigo.')"> <i class="fa-solid fa-circle-plus text-success"></i>
                        Añadir Segmento </a></li>

                    </ul>
                </div>
                    ';

                }


            })
            ->addColumn('estado_bodega', function ($listaBodegas) {
                if ($listaBodegas->estado === 'activo') {
                    return '<td><span class="badge bg-primary">ACTIVO</span></td>';
                } else {

                    return '<td><span class="badge bg-danger">INACTIVO</span></td>';
                }

                    })

            ->rawColumns(['opciones','estado_bodega'])
            ->make(true);


        } catch (QueryException $e) {

            return response()->json([
                'message' => 'Ha ocurrido un error, por favor intente de nuevo.',
                'exception' => $e,
            ],402);

        }
    }

    public function desactivarBodega(Request $request){
        try {

            $estadoBodega = modelBodega::find($request['id']);

            if($estadoBodega->estado_id == 1){

                $bodega = modelBodega::find($request['id']);
                $bodega->estado_id = 2;
                $bodega->save();

                return response()->json([
                    "message" => "Editado con exito"
                ],200);

            }else {

                $bodega = modelBodega::find($request['id']);
                $bodega->estado_id = 1;
                $bodega->save();

                return response()->json([
                    "message" => "Editado con exito"
                ],200);

            }




        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error",
                "error" => $e,
            ],402);


        }

    }

    public function obtenerDatos(Request $request){
        try {

            $datosBodega = DB::SELECT("
            select
                id,
                nombre,
                direccion,
                encargado_bodega
            from bodega where id =".$request['id']."
            ");

            $secciones = DB::SELECT("

            select
                seccion.id,
                seccion.descripcion,
                seccion.estado_id
            from segmento
            inner join seccion
            on segmento.id = seccion.segmento_id
            where segmento.bodega_id =  ".$request['id']."
            order by segmento.descripcion ASC
            ");

            $usuarios = DB::SELECT("
            select
                id,
                name
            from users
            ");

            return response()->json([
            "datosBodega"=> $datosBodega[0],
            "secciones" => $secciones,
            "usuarios" => $usuarios,
            ],200);

        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error.",
                "error" => $e
            ],402);

        }
    }

    public function editarBodega(Request $request){
            //dd($request->all());

            $validator = Validator::make($request->all(), [
                'idBodega' => 'required',
                'editBodegaNombre' => 'required',
                'editBodegaDireccion' => 'required',
                'editEncargadoBodega' => 'required',
            ], [
                'idBodega' => 'Id bodega requerido',
                'editBodegaNombre' => 'Nombre es requerido',
                'editBodegaDireccion' => 'Direccion es requerida',
                'editEncargadoBodega' => 'Encargado de bodega es requerido',
            ]);

            if($validator->fails()){
                return response()->json([
                        "message" => "Ha ocurrido un error",
                        "error" => $validator->errors()
                ],402);
            }

            try {
                DB::beginTransaction();

                $editarBodega = modelBodega::find($request['idBodega']);
                $editarBodega->nombre = $request['editBodegaNombre'];
                $editarBodega->direccion = $request['editBodegaDireccion'];
                $editarBodega->encargado_bodega = $request['editEncargadoBodega'];
                $editarBodega->save();


                //desactivo todas las secciones
                            // Seccion::where('id_bodega',"=", $request['idBodega'])
                            // ->update(['estado_id' => 2]);

                DB::UPDATE("
                update  segmento
                inner join seccion
                on segmento.id = seccion.segmento_id
                set seccion.estado_id = 2
                where segmento.bodega_id = ".$request['idBodega']

                );

                $arrayCheckbox =  $request['seccion'];

                if(!empty($arrayCheckbox)){
                    $longitudArreglo = count($arrayCheckbox);

                //dd($arrayCheckbox);

                    for ($i=0; $i <$longitudArreglo ; $i++) {
                    $editarEstadoSeccion = Seccion::find( $arrayCheckbox[$i]);
                    $editarEstadoSeccion->estado_id = 1;
                    $editarEstadoSeccion->save();

                    }

                }


                DB::commit();

                return response()->json([
                    "message" => "Bodega editada con exito",

                ],200);

            } catch (QueryException $e) {
                DB::rollback();

            return response()->json([
                'message' => 'Ha ocurrido un error al editar la bodega.',
                'error' => $e,

            ], 402);
            }


            return false;
    }

    public function obtenerSegmentoDeBodega($idBodega){
        try{

            $segmentosPorBodega = DB::SELECT("
            SELECT * FROM segmento
            WHERE segmento.bodega_id =
                ".$idBodega." ORDER BY descripcion asc");
                return response()->json(["segmentosPorBodega" => $segmentosPorBodega], 200);


        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Ha ocurrido un error al Mostrar segmento.',
                'error' => $e,

            ], 402);
        }
    }

}
