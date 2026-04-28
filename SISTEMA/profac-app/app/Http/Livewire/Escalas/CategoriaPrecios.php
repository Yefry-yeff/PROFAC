<?php

namespace App\Http\Livewire\Escalas;

use Livewire\Component;

use App\Models\Escalas\modelCategoriaCliente;
use App\Models\Escalas\modelCategoriaPrecios;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;


class CategoriaPrecios extends Component
{
    public function render()
    {
        $categoriasClientes = modelCategoriaCliente::where('estado_id', 1)->get();
        return view('livewire.escalas.categoria-precios', compact('categoriasClientes'));
    }

        public function guardarCtaegoria(Request $request){
                try {

                    DB::beginTransaction();

                        $categoriaPrecio = new modelCategoriaPrecios;
                        $categoriaPrecio->nombre = TRIM($request->nombre_cat_precio) ;
                        $categoriaPrecio->comentario = TRIM($request->comentario_cat_precio) ;
                        $categoriaPrecio->estado_id = 1;
                        $categoriaPrecio->users_id_registro = Auth::user()->id;
                        $categoriaPrecio->cliente_categoria_escala_id = $request->categoria_cliente_id ?? null;
                        $categoriaPrecio->porc_precio_a = $request->porc_precio_a  ?? 0;
                        $categoriaPrecio->porc_precio_b = $request->porc_precio_b  ?? 0;
                        $categoriaPrecio->porc_precio_c = $request->porc_precio_c  ?? 0;
                        $categoriaPrecio->porc_precio_d = $request->porc_precio_d  ?? 0;
                        $categoriaPrecio->save();

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
                                A.nombre as 'categoria',
                                A.comentario as 'comentario',
                                A.estado_id as 'estado',
                                B.name AS 'registro',
                                C.nombre_categoria as 'categoriaCliente',
                                A.porc_precio_a as 'porc_a',
                                A.porc_precio_b as 'porc_b',
                                A.porc_precio_c as 'porc_c',
                                A.porc_precio_d as 'porc_d',
                                A.created_at as 'creacion'
                            FROM categoria_precios as A
                                inner join users B on B.id = A.users_id_registro
                                inner join cliente_categoria_escala C on C.id = A.cliente_categoria_escala_id
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
                DB::beginTransaction();
                
                // Desactivar la categoría de precios
                $Categoria = modelCategoriaPrecios::find($idCategoria);
                $Categoria->estado_id = 2;
                $Categoria->save();
                
                // Inactivar todos los precios de productos ligados a esta categoría
                $preciosInactivados = DB::table('precios_producto_carga')
                    ->where('categoria_precios_id', $idCategoria)
                    ->where('estado_id', 1) // Solo los activos
                    ->update([
                        'estado_id' => 2,
                        'updated_at' => now()
                    ]);
                
                DB::commit();
                
                return response()->json([
                    "text" => "Categoría desactivada con éxito. Se inactivaron {$preciosInactivados} precios de productos.",
                    "icon" => "success",
                    "title" => "Éxito!"
                ], 200);
                
            } catch (QueryException $e) {
                DB::rollback();
                
                return response()->json([
                    'message' => 'Ha ocurrido un error',
                    'error' => $e,
                    "text" => "Ha ocurrido un error al desactivar la categoría.",
                    "icon" => "error",
                    "title" => "Error!"
                ], 402);
            }
        }

        public function listaPrecioEscala(){





        }
}
