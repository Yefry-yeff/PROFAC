<?php

namespace App\Http\Livewire\Escalas;

use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;


class CategoriaClientes extends Component
{
    public function render()
    {
        return view('livewire.escalas.categoria-clientes');
    }

        public function guardarCtaegoria(Request $request){
            try {

                    DB::beginTransaction();

                        $categoriaCliente = new modelCategoriaCliente;
                        $categoriaCliente->nombre_categoria = TRIM($request->nombre_cat) ;
                        $categoriaCliente->descripcion_categoria = TRIM($request->descripcion_cat) ;
                        $categoriaCliente->comentario_cat_cliente = trim($request->comentario) ;
                        $categoriaCliente->estado_id = 1;
                        $categoriaCliente->users_id_creador = Auth::user()->id;
                        $categoriaCliente->save();

                    DB::commit();
                    return response()->json([
                        "icon" => "success",
                        "text" => "Registro exitoso!",
                        "title"=>"Exito!"
                    ],200);

                }catch (QueryException $e) {
                    DB::rollback();

                    return response()->json([
                        "icon" => "error",
                        "text" => "Ha ocurrido un error al registrar categoria",
                        "title"=>"Error!",
                        "error" => $e
                    ],402);
                }
        }

        public function listarCategorias(){
                try {

                        $datos = DB::SELECT("
                            SELECT
                                A.id as 'id',
                                A.nombre_categoria as 'categoria',
                                A.descripcion_categoria as 'descripcion',
                                A.comentario_cat_cliente as 'comentario',
                                A.estado_id as 'estado',
                                B.name AS 'registro',
                                A.created_at as 'creacion'
                            FROM cliente_categoria_escala as A
                                inner join users B on B.id = A.users_id_creador
                            order by A.id DESC;
                        ");


                        return Datatables::of($datos)
                        ->addColumn('estado', function ($datos) {
                            if ($datos->estado === 1) {
                                return '<td><span class="badge bg-primary">ACTIVO</span></td>';
                            } else {

                                return '<td><span class="badge bg-danger">INACTIVO</span></td>';
                            }
                        })
                        ->addColumn('opciones', function ($datos) {
                            if($datos->estado == 1){
                                return
                                '<div class="btn-group">
                                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                                        más</button>
                                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                                        <li>
                                            <a class="dropdown-item" onclick="desactivarCategoria('.$datos->id.')" > <i class="fa fa-times text-danger" aria-hidden="true"></i> Desactivar</a>
                                        </li>
                                    </ul>
                                </div>';
                            }else{
                                return '
                                        <span class="badge badge-secondary px-3 py-2 shadow-sm">
                                            <i class="fa fa-ban mr-1"></i> Sin acciones
                                        </span>';
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

        public function desactivarCategoria($idCategoria){
            try {


                    $Categoria =  modelCategoriaCliente::find($idCategoria);
                    $Categoria->estado_id =  2;
                    $Categoria->save();

                return response()->json([
                    "text" => "Desactivado con éxito.",
                    "icon" => "success",
                    "title"=>"Exito!"
                ],200);
            } catch (QueryException $e) {
                return response()->json([
                    'message' => 'Ha ocurrido un error',
                    'error' => $e,
                    "text" => "Ha ocurrido un error.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],402);
            }

        }
}
