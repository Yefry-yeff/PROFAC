<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;




use App\Models\ModelTipoPago;



class CompraProducto extends Component
{
    public function render()

    {


        $ordenNumero = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from compra");

        return view('livewire.inventario.compra-producto',compact( "ordenNumero"));
    }

    public function listarProveedores(Request $request){

        try {
            session()->save();
            $busqueda = trim((string) $request->get('search', ''));
            $proveedores = DB::table('proveedores')
                ->where('estado_id', 1)
                ->when($busqueda !== '', function ($query) use ($busqueda) {
                    $query->where(function ($subquery) use ($busqueda) {
                        $subquery->where('id', 'LIKE', "%{$busqueda}%")
                            ->orWhere('nombre', 'LIKE', "%{$busqueda}%");
                    });
                })
                ->orderBy('nombre')
                ->limit(15)
                ->get(['id', DB::raw("CONCAT(id, ' - ', nombre) as text")]);

            return response()->json([
                "results" => $proveedores,
            ], 200);
        } catch (QueryException $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Ha ocurrido un error al listar los proveedores.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarFormasPago(){
        try {

            $tipos = ModelTipoPago::all();

            return response()->json([
                "tipos" =>  $tipos,

            ],200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar los tipos de pago.',
                'errorTh' => $e,
            ], 402);
        }
    }


    public function listarProductos(Request $request){

        try {
            $busqueda = trim((string) $request->get('search', ''));
            $productos = DB::table('producto')
                ->where('estado_producto_id', 1)
                ->when($busqueda !== '', function ($query) use ($busqueda) {
                    $query->where(function ($subquery) use ($busqueda) {
                        $subquery->where('id', 'LIKE', "%{$busqueda}%")
                            ->orWhere('nombre', 'LIKE', "%{$busqueda}%")
                            ->orWhere('codigo_barra', 'LIKE', "%{$busqueda}%");
                    });
                })
                ->orderBy('nombre')
                ->limit(15)
                ->get(['id', DB::raw("CONCAT(id, ' - ', nombre, ' - ', COALESCE(codigo_barra, '')) as text")]);

            return response()->json([
                "results" => $productos,
            ], 200);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar los productos.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function obtenerImagenes(Request $request){
        try {
        $imagenes = DB::SELECT("

        select
            @i := @i + 1 as contador,
            id,
            url_img
        from
            img_producto
            cross join (select @i := 0) r
            where producto_id = ".$request['id']."

        ");

        return response()->json([
            "imagenes" => $imagenes,
        ], 200);


        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las imagenes.',
                'errorTh' => $e,
            ], 402);


        }
    }


    public function obtenerDatosProducto(Request $request){
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        try {
            $producto = DB::table('producto as p')
                ->join('unidad_medida as um', 'p.unidad_medida_compra_id', '=', 'um.id')
                ->where('p.id', $request->integer('id'))
                ->where('p.estado_producto_id', 1)
                ->select([
                    'p.id',
                    DB::raw("CONCAT(p.id, ' - ', p.nombre) as nombre"),
                    'p.isv',
                    DB::raw("CONCAT(um.nombre, ' - ', p.unidadad_compra) as unidad"),
                    'p.unidadad_compra',
                    'p.unidad_medida_compra_id',
                ])
                ->first();

            if (!$producto) {
                return response()->json([
                    'message' => 'El producto no existe o está inactivo.',
                ], 404);
            }

            return response()->json([
                'producto' => $producto,
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al obtener los datos del producto.',
                'error' => $e,
            ], 402);
        }

    }

    public function comprobarRetencion(Request $request){
        try {

            $retencion = DB::SELECTONE("
            select
                proveedores_id
            from
            retenciones_has_proveedores
            where proveedores_id = ".$request['idProveedor']." and retenciones_id = 2
            ");

           // dd($retencion);


            if(empty($retencion)){
                return response()->json([
                    'title' => '*No* se registra retencion del 1% para este proveedor.',
                    'text' => '¿Desea aplicar retención del 1% a esta compra?',
                    'retencion_id' => 1,
                ], 200);

            }else{
                return response()->json([
                    'title' => '*Se* registra retencion del 1% para este proveedor. ',
                    'text' => '¿Desea aplicar retención del 1% a esta compra?',
                    'retencion_id' => 2,
                ], 200);

            }

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al realizar la compra.',
                'error' => $e,
            ], 402);
        }

    }


    public function guardarCompra(Request $request){
        $validator = Validator::make($request->all(), [
            'numero_factura' => 'required|string|max:255',
            'cai' => 'nullable|string|max:255',
            'tipoPagoCompra' => 'required|integer',
            'fecha_vencimiento' => 'required|date',
            'fecha_emision' => 'required|date',
            'fecha_entrega' => 'required|date',
            'seleccionarProveedorId' => 'required|integer|exists:proveedores,id',
            'productos' => 'required|array|min:1|max:2000',
            'productos.*.producto_id' => 'required|integer|distinct',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.fecha_expiracion' => 'nullable|date',
        ],[
            'numero_factura.required' => 'Número Factura es requerido',
            'tipoPagoCompra.required' => 'Tipo de Pago es requerido',
            'fecha_vencimiento.required' => 'Fecha de Vencimiento es requerido',
            'fecha_emision.required' => 'Fecha de emisión es requerido',
            'fecha_entrega.required' => 'Fecha de entrega es requerido',
            'seleccionarProveedorId.required' => 'Proveedor es requerido',
            'productos.required' => 'Debe agregar al menos un producto a la compra.',
            'productos.min' => 'Debe agregar al menos un producto a la compra.',
            'productos.*.producto_id.distinct' => 'No se permiten productos duplicados.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error al crear la compra.',
                'errors' => $validator->errors()
            ], 406);
        }

        try {
            $resultado = DB::transaction(function () use ($request) {
                $items = collect($request->input('productos'));
                $ids = $items->pluck('producto_id')->map(fn ($id) => (int) $id)->all();
                $productos = DB::table('producto')
                    ->whereIn('id', $ids)
                    ->where('estado_producto_id', 1)
                    ->get(['id', 'isv', 'unidadad_compra', 'unidad_medida_compra_id'])
                    ->keyBy('id');

                if ($productos->count() !== count($ids)) {
                    abort(422, 'Uno o más productos no existen o están inactivos.');
                }

                $primerasCompras = DB::table('compra_has_producto as cp')
                    ->join('compra as c', 'c.id', '=', 'cp.compra_id')
                    ->whereIn('cp.producto_id', $ids)
                    ->whereYear('c.fecha_emision', now()->year)
                    ->orderBy('c.fecha_emision')
                    ->orderBy('c.id')
                    ->get(['cp.producto_id', 'cp.precio_unidad'])
                    ->unique('producto_id')
                    ->keyBy('producto_id');

                $lineas = [];
                $costos = [];
                $subtotalGeneral = 0;
                $isvGeneral = 0;
                $totalGeneral = 0;

                foreach ($items as $item) {
                    $productoId = (int) $item['producto_id'];
                    $producto = $productos[$productoId];
                    $precio = round((float) $item['precio'], 2);
                    $cantidad = (int) $item['cantidad'];
                    $unidadesCompra = (int) $producto->unidadad_compra;
                    $subtotal = round($precio * $cantidad * $unidadesCompra, 2);
                    $isv = round($subtotal * ((float) $producto->isv / 100), 2);
                    $total = round($subtotal + $isv, 2);
                    $costoActual = round($precio + ($precio * ((float) $producto->isv / 100)), 2);
                    $primeraCompra = $primerasCompras->get($productoId);
                    $costoPromedio = $costoActual;

                    if ($primeraCompra) {
                        $primerCosto = (float) $primeraCompra->precio_unidad;
                        $primerCosto += $primerCosto * ((float) $producto->isv / 100);
                        $costoPromedio = round(($primerCosto + $costoActual) / 2, 2);
                    }

                    $lineas[] = [
                        'producto_id' => $productoId,
                        'precio_unidad' => $precio,
                        'cantidad_ingresada' => $cantidad,
                        'cantidad_sin_asignar' => $cantidad,
                        'fecha_expiracion' => ($item['fecha_expiracion'] ?? null) ?: null,
                        'sub_total_producto' => $subtotal,
                        'isv' => $isv,
                        'precio_total' => $total,
                        'cantidad_disponible' => 0,
                        'unidades_compra' => $unidadesCompra,
                        'unidad_compra_id' => (int) $producto->unidad_medida_compra_id,
                    ];
                    $costos[$productoId] = [$costoActual, $costoPromedio];
                    $subtotalGeneral += $subtotal;
                    $isvGeneral += $isv;
                    $totalGeneral += $total;
                }

                $siguienteOrden = ((int) DB::table('compra')->count()) + 1;
                $ahora = now();
                $idCompra = DB::table('compra')->insertGetId([
                    'numero_factura' => trim($request->numero_factura),
                    'codigo_cai' => trim(strtoupper((string) $request->cai)),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'fecha_emision' => $request->fecha_emision,
                    'fecha_recepcion' => $request->fecha_entrega,
                    'isv_compra' => round($isvGeneral, 2),
                    'sub_total' => round($subtotalGeneral, 2),
                    'total' => round($totalGeneral, 2),
                    'debito' => round($totalGeneral, 2),
                    'proveedores_id' => (int) $request->seleccionarProveedorId,
                    'users_id' => Auth::id(),
                    'tipo_compra_id' => (int) $request->tipoPagoCompra,
                    'numero_orden' => date('Y') . '-' . $siguienteOrden,
                    'monto_retencion' => 0,
                    'estado_compra_id' => 1,
                    'retenciones_id' => 2,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);

                foreach ($lineas as &$linea) {
                    $linea['compra_id'] = $idCompra;
                    $linea['created_at'] = $ahora;
                    $linea['updated_at'] = $ahora;
                }
                unset($linea);

                foreach (array_chunk($lineas, 500) as $bloque) {
                    DB::table('compra_has_producto')->insert($bloque);
                }

                foreach (array_chunk($costos, 250, true) as $bloqueCostos) {
                    $ultimoCostoCase = 'CASE id ';
                    $costoPromedioCase = 'CASE id ';
                    foreach ($bloqueCostos as $productoId => [$ultimoCosto, $costoPromedio]) {
                        $ultimoCostoCase .= "WHEN {$productoId} THEN {$ultimoCosto} ";
                        $costoPromedioCase .= "WHEN {$productoId} THEN {$costoPromedio} ";
                    }
                    $ultimoCostoCase .= 'END';
                    $costoPromedioCase .= 'END';

                    DB::table('producto')
                        ->whereIn('id', array_keys($bloqueCostos))
                        ->update([
                            'ultimo_costo_compra' => DB::raw($ultimoCostoCase),
                            'costo_promedio' => DB::raw($costoPromedioCase),
                            'updated_at' => $ahora,
                        ]);
                }

                return [
                    'id' => $idCompra,
                    'subtotal' => round($subtotalGeneral, 2),
                    'isv' => round($isvGeneral, 2),
                    'total' => round($totalGeneral, 2),
                ];
            }, 3);

            return response()->json([
                'message' => 'Compra creada con éxito.',
                'compra' => $resultado,
            ], 200);

        } catch (\Throwable $e) {
            report($e);
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            return response()->json([
                'message' => 'Ha ocurrido un error al realizar la compra.',
            ], $status);
        }

    }




}
