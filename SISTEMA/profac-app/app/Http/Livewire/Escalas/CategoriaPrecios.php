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
                                A.nombre_categoria as 'categoria',
                                A.estado_id as 'estado',
                                B.name AS 'registro',
                                (
                                    SELECT COUNT(*)
                                    FROM categoria_precios sub
                                    WHERE sub.cliente_categoria_escala_id = A.id
                                      AND sub.estado_id = 1
                                ) as 'total_cat',
                                A.created_at as 'creacion'
                            FROM cliente_categoria_escala as A
                                inner join users B on B.id = A.users_id_creador
                            order by A.id DESC;
                        ");


                        return Datatables::of($datos)
                        ->addColumn('estado', function ($datos) {
                            if ($datos->estado === 1) {
                                return '<span class="badge badge-success" style="font-size:.78rem;padding:4px 10px;">ACTIVO</span>';
                            } else {
                                return '<span class="badge badge-danger" style="font-size:.78rem;padding:4px 10px;">INACTIVO</span>';
                            }
                        })
                        ->addColumn('opciones', function ($datos) {
                            if($datos->estado == 1){
                                return
                                '<div class="btn-group">
                                    <button data-toggle="dropdown" class="btn btn-sm dropdown-toggle" aria-expanded="false"
                                        style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);color:#fff;border:none;font-size:.78rem;padding:4px 12px;border-radius:5px;font-weight:600;">
                                        <i class="fa fa-ellipsis-v mr-1"></i>Acciones
                                    </button>
                                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                                        <li>
                                            <a class="dropdown-item" onclick="verCategoriasPrecio('.$datos->id.',\''.addslashes($datos->categoria).'\')">
                                                <i class="fa fa-list text-primary mr-1" aria-hidden="true"></i> Ver categorías de precio
                                            </a>
                                        </li>
                                        <li role="separator" class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" onclick="desactivarCategoria('.$datos->id.')" > <i class="fa fa-times text-danger" aria-hidden="true"></i> Desactivar</a>
                                        </li>
                                    </ul>
                                </div>';
                            }else{
                                return '<span class="badge badge-secondary" style="font-size:.78rem;padding:4px 10px;">
                                            <i class="fa fa-ban mr-1"></i> Sin acciones
                                        </span>';
                            }
                        })
                        ->addColumn('total_cat', function ($datos) {
                            $n = (int) $datos->total_cat;
                            $color = $n === 0 ? '#6c757d' : '#e67e22';
                            return '<span style="display:inline-flex;align-items:center;gap:5px;font-weight:600;color:'.$color.';">
                                        <i class="fa fa-tags"></i> '.$n.' '.($n === 1 ? 'categoría' : 'categorías').
                                    '</span>';
                        })
                        ->rawColumns(['opciones','estado','total_cat'])
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

        public function listarCategoriasPorCliente($id){
            try {
                $datos = DB::SELECT("
                    SELECT
                        cp.id,
                        cp.nombre,
                        cp.comentario,
                        cp.estado_id,
                        cp.porc_precio_a,
                        cp.porc_precio_b,
                        cp.porc_precio_c,
                        cp.porc_precio_d,
                        cp.created_at
                    FROM categoria_precios cp
                    WHERE cp.cliente_categoria_escala_id = ?
                    ORDER BY cp.id DESC
                ", [$id]);

                return response()->json(['categorias' => $datos]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function actualizarCategoria(Request $request){
            try {
                DB::beginTransaction();

                $cat = modelCategoriaPrecios::findOrFail($request->id);
                $cat->nombre        = trim($request->nombre);
                $cat->comentario    = trim($request->comentario ?? '');
                $cat->porc_precio_a = $request->porc_precio_a ?? 0;
                $cat->porc_precio_b = $request->porc_precio_b ?? 0;
                $cat->porc_precio_c = $request->porc_precio_c ?? 0;
                $cat->porc_precio_d = $request->porc_precio_d ?? 0;
                $cat->save();

                DB::commit();
                return response()->json([
                    "icon"  => "success",
                    "title" => "\u00c9xito!",
                    "text"  => "Categor\u00eda actualizada correctamente."
                ], 200);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    "icon"  => "error",
                    "title" => "Error!",
                    "text"  => "No se pudo actualizar la categor\u00eda."
                ], 402);
            }
        }
}
