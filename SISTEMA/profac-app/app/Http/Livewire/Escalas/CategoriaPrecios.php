<?php

namespace App\Http\Livewire\Escalas;

use Livewire\Component;

use App\Models\Escalas\modelCategoriaCliente;
use App\Models\Escalas\modelCategoriaPrecios;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;


class CategoriaPrecios extends Component
{
    public function render()
    {
        $categoriasClientes  = modelCategoriaCliente::where('estado_id', 1)->get();
        $categoriasPrecios   = modelCategoriaPrecios::where('estado_id', 1)
                                    ->orderBy('nombre')->get();
        return view('livewire.escalas.categoria-precios', compact('categoriasClientes', 'categoriasPrecios'));
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

                // Limpiar cachés para que cotizaciones/facturas reflejen el cambio inmediatamente
                Cache::forget('filtros_categoria_precios');
                $catCliente = DB::table('categoria_precios')->where('id', $idCategoria)->value('cliente_categoria_escala_id');
                if ($catCliente) {
                    Cache::forget('filtros_cat_precio_' . $catCliente);
                }

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

        public function reactivarCategoria($idCategoria){
            try {
                DB::beginTransaction();

                $Categoria = modelCategoriaPrecios::find($idCategoria);
                if (!$Categoria) {
                    return response()->json([
                        "text" => "No se encontró la categoría.",
                        "icon" => "error",
                        "title" => "Error!"
                    ], 404);
                }

                $Categoria->estado_id = 1;
                $Categoria->save();

                DB::commit();

                // Limpiar cachés
                Cache::forget('filtros_categoria_precios');
                $catCliente = DB::table('categoria_precios')->where('id', $idCategoria)->value('cliente_categoria_escala_id');
                if ($catCliente) {
                    Cache::forget('filtros_cat_precio_' . $catCliente);
                }

                return response()->json([
                    "text" => "Categoría reactivada con éxito. Recuerda re-cargar sus precios de producto si es necesario.",
                    "icon" => "success",
                    "title" => "Éxito!"
                ], 200);

            } catch (QueryException $e) {
                DB::rollback();

                return response()->json([
                    'message' => 'Ha ocurrido un error',
                    'error' => $e,
                    "text" => "Ha ocurrido un error al reactivar la categoría.",
                    "icon" => "error",
                    "title" => "Error!"
                ], 402);
            }
        }

        public function listaPrecioEscala(){





        }

        public function listarCategoriasPorCliente($id){
            try {
                $datos = DB::select("
                    SELECT
                        cp.id,
                        cp.nombre,
                        cp.comentario,
                        cp.estado_id,
                        cp.porc_precio_a,
                        cp.porc_precio_b,
                        cp.porc_precio_c,
                        cp.porc_precio_d,
                        cp.created_at,
                        cp.fecha_ultima_actualizacion,
                        u.name AS nombre_actualizador
                    FROM categoria_precios cp
                    LEFT JOIN users u ON u.id = cp.users_id_actualizador
                    WHERE cp.cliente_categoria_escala_id = ?
                    ORDER BY cp.id DESC
                ", [$id]);

                // Cargar comisiones activas por categoría de precio
                $comisiones = DB::table('comision_escala as ce')
                    ->join('rol as r', 'r.id', '=', 'ce.rol_id')
                    ->where('ce.cliente_categoria_escala_id', $id)
                    ->where('ce.estado_id', 1)
                    ->select('ce.id as escala_id', 'ce.categoria_precios_id', 'ce.rol_id', 'r.nombre as rol_nombre', 'ce.porcentaje_comision')
                    ->orderBy('r.nombre')
                    ->get()
                    ->groupBy('categoria_precios_id');

                // Adjuntar comisiones a cada categoría de precio
                foreach ($datos as $row) {
                    $row->comisiones = $comisiones->get($row->id, collect())->values()->all();
                }

                return response()->json(['categorias' => $datos]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function actualizarComisionCatPrecio(Request $request){
            try {
                $catPrecioId  = (int) $request->cat_precio_id;
                $comisiones   = $request->comisiones ?? []; // [{escala_id, porcentaje_comision}]

                if (!$catPrecioId) {
                    return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'ID de categoría requerido.'], 422);
                }

                DB::beginTransaction();

                foreach ($comisiones as $item) {
                    $escalId  = (int) ($item['escala_id'] ?? 0);
                    $porcento = (float) ($item['porcentaje_comision'] ?? 0);

                    if ($escalId) {
                        DB::table('comision_escala')
                            ->where('id', $escalId)
                            ->where('estado_id', 1)
                            ->update([
                                'porcentaje_comision'      => $porcento,
                                'usermodifico'             => Auth::id(),
                                'fechaultimamodificacion'  => now(),
                                'updated_at'               => now(),
                            ]);
                    }
                }

                DB::commit();
                return response()->json([
                    'icon'  => 'success',
                    'title' => 'Éxito!',
                    'text'  => 'Comisiones actualizadas correctamente.'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'No se pudieron actualizar las comisiones.'
                ], 500);
            }
        }

        public function actualizarCategoria(Request $request){
            try {
                DB::beginTransaction();

                $cat = modelCategoriaPrecios::findOrFail($request->id);
                $cat->nombre                   = trim($request->nombre);
                $cat->comentario               = trim($request->comentario ?? '');
                $cat->porc_precio_a            = $request->porc_precio_a ?? 0;
                $cat->porc_precio_b            = $request->porc_precio_b ?? 0;
                $cat->porc_precio_c            = $request->porc_precio_c ?? 0;
                $cat->porc_precio_d            = $request->porc_precio_d ?? 0;
                $cat->users_id_actualizador    = Auth::user()->id;
                $cat->fecha_ultima_actualizacion = now();
                $cat->save();

                // Eliminar duplicados: si un producto tiene más de un registro activo escalable
                // para esta categoría, inactivar los más antiguos y conservar solo el más reciente.
                // Se incluye tipo_categoria_precio_id IS NULL para cubrir registros históricos
                // anteriores a la existencia del campo.
                $duplicados = DB::select("
                    SELECT producto_id, MAX(id) as max_id
                    FROM precios_producto_carga
                    WHERE categoria_precios_id = ?
                      AND estado_id = 1
                      AND (tipo_categoria_precio_id = 1 OR tipo_categoria_precio_id IS NULL)
                    GROUP BY producto_id
                    HAVING COUNT(*) > 1
                ", [$cat->id]);

                foreach ($duplicados as $dup) {
                    DB::table('precios_producto_carga')
                        ->where('categoria_precios_id', $cat->id)
                        ->where('producto_id', $dup->producto_id)
                        ->where('estado_id', 1)
                        ->where(function($q) {
                            $q->where('tipo_categoria_precio_id', 1)
                              ->orWhereNull('tipo_categoria_precio_id');
                        })
                        ->where('id', '!=', $dup->max_id)
                        ->update(['estado_id' => 2, 'updated_at' => now()]);
                }

                // Contar productos escalables activos (tipo 1 o NULL histórico)
                $productosActualizados = DB::table('precios_producto_carga')
                    ->where('categoria_precios_id', $cat->id)
                    ->where('estado_id', 1)
                    ->where(function($q) {
                        $q->where('tipo_categoria_precio_id', 1)
                          ->orWhereNull('tipo_categoria_precio_id');
                    })
                    ->count();

                // Recalcular precio_a/b/c/d usando precio_base_venta y los nuevos porcentajes
                if ($productosActualizados > 0) {
                    DB::update("
                        UPDATE precios_producto_carga
                        SET
                            precio_a                  = precio_base_venta + ((? / 100) * precio_base_venta),
                            precio_b                  = precio_base_venta + ((? / 100) * precio_base_venta),
                            precio_c                  = precio_base_venta + ((? / 100) * precio_base_venta),
                            precio_d                  = precio_base_venta + ((? / 100) * precio_base_venta),
                            users_id_actualizador     = ?,
                            fecha_ultima_actualizacion = NOW(),
                            updated_at                = NOW()
                        WHERE categoria_precios_id = ?
                          AND estado_id = 1
                          AND (tipo_categoria_precio_id = 1 OR tipo_categoria_precio_id IS NULL)
                    ", [
                        $cat->porc_precio_a,
                        $cat->porc_precio_b,
                        $cat->porc_precio_c,
                        $cat->porc_precio_d,
                        Auth::user()->id,
                        $cat->id,
                    ]);
                }

                DB::commit();
                return response()->json([
                    "icon"                   => "success",
                    "title"                  => "Éxito!",
                    "text"                   => "Categoría actualizada correctamente.",
                    "productos_actualizados" => $productosActualizados,
                ], 200);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    "icon"  => "error",
                    "title" => "Error!",
                    "text"  => "No se pudo actualizar la categoría."
                ], 402);
            }
        }

        public function listarProductosPrecios(Request $request)
        {
            try {
                $categoriaId = (int) $request->categoria_id;
                $buscar      = trim($request->buscar ?? '');
                $start       = max(0, (int) ($request->start ?? 0));
                $length      = min(100, max(10, (int) ($request->length ?? 25)));

                if (!$categoriaId) {
                    return response()->json(['error' => 'Categoría requerida'], 422);
                }

                $params = [$categoriaId];
                $where  = "p.categoria_precios_id = ? AND p.estado_id = 1";

                if ($buscar !== '') {
                    $where   .= " AND (prod.nombre LIKE ? OR CAST(prod.id AS CHAR) LIKE ?)";
                    $params[] = '%' . $buscar . '%';
                    $params[] = '%' . $buscar . '%';
                }

                $total = DB::selectOne("
                    SELECT COUNT(*) AS total
                    FROM precios_producto_carga p
                    JOIN producto prod ON prod.id = p.producto_id
                    WHERE {$where}
                ", $params)->total;

                $datos = DB::select("
                    SELECT
                        p.id,
                        prod.id          AS codigo,
                        prod.nombre,
                        p.precio_base_venta,
                        p.precio_a,
                        p.precio_b,
                        p.precio_c,
                        p.precio_d,
                        cp.porc_precio_a,
                        cp.porc_precio_b,
                        cp.porc_precio_c,
                        cp.porc_precio_d
                    FROM precios_producto_carga p
                    JOIN producto prod ON prod.id = p.producto_id
                    JOIN categoria_precios cp ON cp.id = p.categoria_precios_id
                    WHERE {$where}
                    ORDER BY prod.nombre ASC
                    LIMIT ? OFFSET ?
                ", array_merge($params, [$length, $start]));

                $porc = null;
                if (count($datos)) {
                    $porc = [
                        'a' => $datos[0]->porc_precio_a,
                        'b' => $datos[0]->porc_precio_b,
                        'c' => $datos[0]->porc_precio_c,
                        'd' => $datos[0]->porc_precio_d,
                    ];
                }

                return response()->json([
                    'recordsTotal'    => (int) $total,
                    'recordsFiltered' => (int) $total,
                    'data'            => $datos,
                    'porc'            => $porc,
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function actualizarPrecioBase(Request $request)
        {
            try {
                $precioId   = (int) $request->precio_id;
                $precioBase = (float) $request->precio_base;

                if (!$precioId || $precioBase <= 0) {
                    return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Datos inválidos.'], 422);
                }

                $reg = DB::selectOne("
                    SELECT p.id, cp.porc_precio_a, cp.porc_precio_b, cp.porc_precio_c, cp.porc_precio_d
                    FROM precios_producto_carga p
                    JOIN categoria_precios cp ON cp.id = p.categoria_precios_id
                    WHERE p.id = ? AND p.estado_id = 1
                ", [$precioId]);

                if (!$reg) {
                    return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Registro no encontrado.'], 404);
                }

                $precioA = round($precioBase * (1 + $reg->porc_precio_a / 100), 2);
                $precioB = round($precioBase * (1 + $reg->porc_precio_b / 100), 2);
                $precioC = round($precioBase * (1 + $reg->porc_precio_c / 100), 2);
                $precioD = round($precioBase * (1 + $reg->porc_precio_d / 100), 2);

                DB::table('precios_producto_carga')->where('id', $precioId)->update([
                    'precio_base_venta'           => $precioBase,
                    'precio_a'                    => $precioA,
                    'precio_b'                    => $precioB,
                    'precio_c'                    => $precioC,
                    'precio_d'                    => $precioD,
                    'users_id_actualizador'       => Auth::id(),
                    'fecha_ultima_actualizacion'  => now(),
                    'updated_at'                  => now(),
                ]);

                return response()->json([
                    'icon'     => 'success',
                    'title'    => '¡Guardado!',
                    'text'     => 'Precio actualizado.',
                    'precio_a' => $precioA,
                    'precio_b' => $precioB,
                    'precio_c' => $precioC,
                    'precio_d' => $precioD,
                ]);
            } catch (\Exception $e) {
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()], 500);
            }
        }
}
