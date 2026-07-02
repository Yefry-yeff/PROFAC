<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Database\QueryException;

use App\Models\ModelCategoriaProducto;
use App\Models\ModelProducto;
use App\Models\ModelUnidadMedidaVenta;
use App\Models\ModelMarca;

use DataTables;

class ProductoApoyo extends Component
{
    public function render()
    {
        $categorias = ModelCategoriaProducto::all();
        $unidades   = DB::SELECT("select id,nombre,simbolo from unidad_medida order by nombre asc");
        $marcas     = DB::SELECT("select id,nombre from marca order by nombre asc");
        return view('livewire.inventario.producto-apoyo', compact('categorias', 'unidades', 'marcas'));
    }

    /**
     * Listar productos para DataTable — igual que Producto::listarProductos
     * pero la columna de acciones solo tiene Editar (ir al detalle) e Inactivar.
     * Sin precios ni costos en la respuesta.
     */
    public function listarProductos(Request $request)
    {
        try {
            $query = DB::table('producto as A')
                ->select(
                    'A.id as codigo',
                    'A.nombre',
                    'A.descripcion',
                    'C.descripcion as categoria',
                    'A.codigo_barra',
                    'A.estado_producto_id'
                )
                ->join('sub_categoria as B', 'A.sub_categoria_id', '=', 'B.id')
                ->join('categoria_producto as C', 'C.id', '=', 'B.categoria_producto_id')
                ->leftJoin('marca as M', 'M.id', '=', 'A.marca_id');

            return Datatables::of($query)
                ->addColumn('existencia', function ($p) {
                    $e1 = DB::table('recibido_bodega')
                        ->join('compra', 'recibido_bodega.compra_id', '=', 'compra.id')
                        ->where('compra.estado_compra_id', 1)
                        ->where('recibido_bodega.producto_id', $p->codigo)
                        ->sum('recibido_bodega.cantidad_disponible');
                    $e2 = DB::table('recibido_bodega')
                        ->whereNull('compra_id')
                        ->where('cantidad_disponible', '<>', 0)
                        ->where('producto_id', $p->codigo)
                        ->sum('cantidad_disponible');
                    return number_format($e1 + $e2, 0);
                })
                ->addColumn('estado', function ($p) {
                    return $p->estado_producto_id == 1
                        ? '<span class="badge-activo">Activo</span>'
                        : '<span class="badge-inactivo">Inactivo</span>';
                })
                ->addColumn('acciones', function ($p) {
                    $detalle = '<a class="dropdown-item" href="/producto/detalle/' . $p->codigo . '"><i class="fa fa-search mr-1"></i> Ver detalle</a>';
                    if ($p->estado_producto_id == 1) {
                        $toggle = '<a class="dropdown-item text-danger" href="#" onclick="cambiarEstado(' . $p->codigo . ', 2)"><i class="fa fa-ban mr-1"></i> Inactivar</a>';
                    } else {
                        $toggle = '<a class="dropdown-item text-success" href="#" onclick="cambiarEstado(' . $p->codigo . ', 1)"><i class="fa fa-check-circle mr-1"></i> Activar</a>';
                    }
                    return '
                    <div class="prod-dropdown dropdown">
                        <button class="btn-prod-menu" data-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">' . $detalle . $toggle . '</div>
                    </div>';
                })
                ->filter(function ($query) use ($request) {
                    $q           = trim($request->get('filtro_q', ''));
                    $desc        = trim($request->get('filtro_descripcion', ''));
                    $categoriaId = (int) $request->get('filtro_categoria_id', 0);
                    $marcaId     = (int) $request->get('filtro_marca_id', 0);
                    $estadoId    = (int) $request->get('filtro_estado', 0);
                    $productoId  = (int) $request->get('filtro_producto_id', 0);

                    if ($productoId > 0) {
                        $query->where('A.id', $productoId);
                    }

                    if ($q !== '') {
                        $words = array_values(array_filter(array_map('trim', explode(' ', $q))));
                        foreach ($words as $word) {
                            $query->where(function ($wq) use ($word) {
                                $wq->where('A.nombre', 'LIKE', "%{$word}%")
                                   ->orWhere('A.codigo_barra', 'LIKE', "%{$word}%")
                                   ->orWhere('A.codigo_estatal', 'LIKE', "%{$word}%");
                                if (is_numeric($word) && ctype_digit($word)) {
                                    $wq->orWhere('A.id', (int) $word);
                                }
                            });
                        }
                    }
                    if ($desc !== '') {
                        $query->where('A.descripcion', 'LIKE', "%{$desc}%");
                    }
                    if ($categoriaId) {
                        $query->where('C.id', $categoriaId);
                    }
                    if ($marcaId) {
                        $query->where('A.marca_id', $marcaId);
                    }
                    if ($estadoId) {
                        $query->where('A.estado_producto_id', $estadoId);
                    }
                })
                ->orderColumn('codigo',      fn($q, $o) => $q->orderBy('A.id', $o))
                ->orderColumn('nombre',      fn($q, $o) => $q->orderBy('A.nombre', $o))
                ->orderColumn('descripcion', fn($q, $o) => $q->orderBy('A.descripcion', $o))
                ->orderColumn('categoria',   fn($q, $o) => $q->orderBy('C.descripcion', $o))
                ->rawColumns(['acciones', 'estado'])
                ->make(true);

        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al listar productos.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear producto — igual que Producto::crearProducto pero sin campos de precio.
     * Precio base y costos se guardan a 0 para no romper integridad.
     */
    public function crearProducto(Request $request)
    {
        try {
            $codBarra = trim($request['cod_barra_producto'] ?? '');
            if ($codBarra !== '') {
                $dup = DB::selectOne("select count(id) as c from producto where codigo_barra = ? ", [$codBarra]);
                if ($dup->c > 0) {
                    return response()->json(['icon' => 'error', 'title' => 'Error!', 'text' => 'El código de barra ya está registrado.'], 402);
                }
            }

            DB::beginTransaction();

            $producto = new ModelProducto;
            $producto->nombre                  = trim($request['nombre_producto']);
            $producto->descripcion             = trim($request['descripcion_producto']);
            $producto->isv                     = $request['isv_producto'];
            $producto->codigo_barra            = $codBarra;
            $producto->codigo_estatal          = trim($request['cod_estatal_producto'] ?? '');
            $producto->sub_categoria_id        = $request['sub_categoria_producto'];
            $producto->precio_base             = 0;
            $producto->costo_promedio          = 0;
            $producto->ultimo_costo_compra     = 0;
            $producto->precio1                 = 0;
            $producto->precio2                 = 0;
            $producto->precio3                 = 0;
            $producto->precio4                 = 0;
            $producto->marca_id                = $request->marca_producto ?? null;
            $producto->tiempo_recuperacion_meses = $request->tiempo_recuperacion_meses ?: null;
            $producto->origen                  = trim($request->origen ?? '') ?: null;
            $producto->users_id                = Auth::user()->id;
            $producto->estado_producto_id      = 1;
            $producto->unidad_medida_compra_id = $request->unidad_producto;
            $producto->unidadad_compra         = $request->unidades;
            $producto->save();

            // unidad de venta por defecto (misma que compra)
            $uv = new ModelUnidadMedidaVenta;
            $uv->unidad_venta         = $request->unidades;
            $uv->unidad_medida_id     = $request->unidad_producto;
            $uv->producto_id          = $producto->id;
            $uv->estado_id            = 1;
            $uv->unidad_venta_defecto = 1;
            $uv->save();

            // fotos opcionales
            if ($request->file('files')) {
                $imgs = [];
                $i = 1;
                foreach ($request->file('files') as $file) {
                    $name = 'IMG_' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('/catalogo'), $name);
                    $imgs[] = ['producto_id' => $producto->id, 'url_img' => $name, 'users_id' => Auth::user()->id, 'created_at' => now()];
                    $i++;
                }
                DB::table('img_producto')->insert($imgs);
            }

            DB::commit();
            return response()->json(['message' => 'Producto creado con éxito.'], 200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json(['message' => 'Error al crear el producto.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function inactivarProducto(Request $request)
    {
        try {
            $id     = (int) $request->id;
            $estado = (int) $request->estado;
            if (!$id || !in_array($estado, [1, 2])) {
                return response()->json(['message' => 'Parámetros inválidos.'], 422);
            }
            DB::table('producto')->where('id', $id)->update(['estado_producto_id' => $estado]);
            return response()->json(['message' => $estado === 1 ? 'Producto activado.' : 'Producto inactivado.'], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al cambiar estado.'], 500);
        }
    }
}
