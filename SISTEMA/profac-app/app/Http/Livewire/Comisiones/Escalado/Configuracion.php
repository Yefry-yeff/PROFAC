<?php

namespace App\Http\Livewire\Comisiones\Escalado;


use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use App\Models\Comisiones\Escalado\modelcomision_escala;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class Configuracion extends Component
{
    public function render()
    {
        return view('livewire.comisiones.escalado.configuracion');
    }

    public function listaRolesUsuario(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $roles = \DB::table('rol')
            ->select('id', 'nombre')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('nombre', 'like', '%'.$q.'%');
            })
            ->orderBy('nombre')
            ->limit(50)
            ->get();
            //dd($roles);

        return response()->json(['roles' => $roles], 200);
    }

    public function guardarParametroComision(Request $request){
        try {
                //$existencia = DB::SELECTONE("select count(*) from comision_escala where estado_id=1 and rol_id=".$request->rol_id." and cliente_categoria_escala_id=".$request->categoria_cliente_id);
                $existe = modelcomision_escala::where('rol_id', $request->rol_id)
                   ->where('estado_id', 1)
                   ->where('cliente_categoria_escala_id', $request->categoria_cliente_id)
                   ->exists();

                if ($existe) {
                    return response()->json([
                        "icon" => "warning",
                        "text" => "Ya existe un parámetro activo con la categoría de cliente y rol seleccionado, inactive primero antes de crear para estos parámetros.",
                        "title"=> "Advertencia"
                    ], 409);
                }

                DB::beginTransaction();

                    $parametroComision = new modelcomision_escala;
                    $parametroComision->nombre = TRIM($request->nombre_comescala) ;
                    $parametroComision->descripcion = TRIM($request->nombre_comescala) ;
                    $parametroComision->rango_inicial = $request->rango_inicial_comescala ;
                    $parametroComision->rango_final = $request->rango_final_comescala;
                    $parametroComision->cliente_categoria_escala_id = $request->categoria_cliente_id;
                    $parametroComision->rol_id = $request->rol_id;
                    $parametroComision->porcentaje_comision = $request->porcentaje_comision;
                    $parametroComision->estado_id = 1;
                    $parametroComision->users_registro = Auth::user()->id;
                    $parametroComision->save();

                DB::commit();
                return response()->json([
                    "icon" => "success",
                    "text" => "Registro de Parametro de comisión exitoso",
                    "title"=>"Exito!"
                ],200);

        }catch (QueryException $e) {
            DB::rollback();
            return response()->json([
               "icon" => "error",
                "text" => "Error al realizar registro",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    public function listarParametroComision(){
        try {

            $datos = DB::SELECT("
                select
                    A.id,
                    A.nombre,
                    A.descripcion,
                    A.porcentaje_comision,
                    FORMAT(A.rango_inicial, 2) as rango_inicial,
                    FORMAT(A.rango_final, 2) as rango_final,
                    B.nombre as 'rol',
                    C.nombre_categoria as 'cliente_cat_escala',
                    D.name as 'userRegistro',
                    A.created_at as 'fechaRegistro',
                    A.estado_id
                from comision_escala A
                inner join rol B on B.id = A.rol_id
                inner join cliente_categoria_escala C on C.id = A.cliente_categoria_escala_id
                inner join users D on D.id = A.users_registro
                order by A.id DESC;
            ");


            return Datatables::of($datos)
                    ->addColumn('estado', function ($datos) {
                        if ($datos->estado_id === 1) {
                            return '<td><span class="badge bg-primary">ACTIVO</span></td>';
                        } else {
                            return '<td><span class="badge bg-danger">INACTIVO</span></td>';
                        }
                    })
                    ->addColumn('opciones', function ($datos) {
                        if($datos->estado_id == 1){
                            return
                                '<div class="btn-group">
                                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                        más</button>
                                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                                                        <li>
                                            <a class="dropdown-item" onclick="editarParametro('.$datos->id.')">
                                                <i class="fa fa-edit text-primary" aria-hidden="true"></i> Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" onclick="desactivarCategoria('.$datos->id.', \''.$datos->rol.'\')" > <i class="fa fa-times text-danger" aria-hidden="true"></i> Desactivar</a>
                                        </li>
                                    </ul>
                                </div>';
                        }else{
                            return ' <span class="badge badge-secondary px-3 py-2 shadow-sm"><i class="fa fa-ban mr-1"></i> Sin acciones</span>';
                        }
                    })
                    ->rawColumns(['opciones','estado'])
                    ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ],402);
        }
    }

    public function desactivarParametro($id){
        try {
                // Buscar registro
                $parametro = DB::table('comision_escala')->where('id', $id)->first();

                if (!$parametro) {
                    return response()->json([
                        "icon"  => "error",
                        "title" => "No encontrado",
                        "text"  => "El parámetro que intenta desactivar no existe."
                    ], 404);
                }

                // No desactivar si ya está inactivo
                if ($parametro->estado_id == 2) {
                    return response()->json([
                        "icon"  => "warning",
                        "title" => "Ya estaba desactivado",
                        "text"  => "Este parámetro ya no se encuentra activo."
                    ], 200);
                }

                DB::beginTransaction();

                // Actualizar estado
                DB::table('comision_escala')
                    ->where('id', $id)
                    ->update([
                        'estado_id' => 2,
                        'fechadesactivo' => now(),
                        'userdesactivo' =>  Auth::user()->id, // INACTIVO
                        'updated_at' => now(),
                    ]);

                DB::commit();

                return response()->json([
                    "icon"  => "success",
                    "title" => "Desactivado",
                    "text"  => "El parámetro de comisión fue desactivado correctamente."
                ], 200);

            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([
                    "icon"  => "error",
                    "title" => "Error",
                    "text"  => "No se pudo desactivar el parámetro. Intente nuevamente.",
                    "error" => $e->getMessage()
                ], 500);
            }
    }

    public function obtenerParametro($id)
    {
        $parametro = DB::table('comision_escala')
            ->select(
                'id',
                'nombre',
                'descripcion',
                'rango_inicial',
                'rango_final',
                'porcentaje_comision',
                'cliente_categoria_escala_id',
                'rol_id'
            )
            ->where('id', $id)
            ->first();

        if (!$parametro) {
            return response()->json([
                "icon"  => "error",
                "title" => "No encontrado",
                "text"  => "No se encontró el parámetro solicitado."
            ], 404);
        }

        return response()->json($parametro, 200);
    }

    public function actualizarParametro(Request $request, $id)
    {
        try {
            $parametro = DB::table('comision_escala')->where('id', $id)->first();

            if (!$parametro) {
                return response()->json([
                    "icon"  => "error",
                    "title" => "No encontrado",
                    "text"  => "El parámetro que intenta editar no existe."
                ], 404);
            }

            // Validar que no duplique otro activo con misma categoría y rol
            $existe = DB::table('comision_escala')
                ->where('rol_id', $request->rol_id)
                ->where('cliente_categoria_escala_id', $request->categoria_cliente_id)
                ->where('estado_id', 1)
                ->where('id', '<>', $id)
                ->exists();

            if ($existe) {
                return response()->json([
                    "icon"  => "warning",
                    "title" => "Advertencia",
                    "text"  => "Ya existe otro parámetro activo con la categoría de cliente y rol seleccionados."
                ], 409);
            }

            DB::beginTransaction();

            DB::table('comision_escala')
                ->where('id', $id)
                ->update([
                    'nombre'                    => trim($request->nombre_comescala),
                    'descripcion'               => trim($request->descripcion_comescala),
                    'rango_inicial'             => $request->rango_inicial_comescala,
                    'rango_final'               => $request->rango_final_comescala,
                    'cliente_categoria_escala_id'=> $request->categoria_cliente_id,
                    'rol_id'                    => $request->rol_id,
                    'porcentaje_comision'       => $request->porcentaje_comision,
                    'fechaultimamodificacion'       =>  now(),
                    'usermodifico'       =>  Auth::user()->id,
                    'updated_at'                => now(),
                ]);

            DB::commit();

            return response()->json([
                "icon"  => "success",
                "title" => "Actualizado",
                "text"  => "El parámetro de comisión fue actualizado correctamente."
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "icon"  => "error",
                "title" => "Error",
                "text"  => "No se pudo actualizar el parámetro. Intente nuevamente.",
                "error" => $e->getMessage()
            ], 500);
        }
    }



}
