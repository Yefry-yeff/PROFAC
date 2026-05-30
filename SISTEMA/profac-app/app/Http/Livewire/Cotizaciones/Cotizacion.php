<?php

namespace App\Http\Livewire\Cotizaciones;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelCotizacion;
use App\Models\ModelCotizacionProducto;

class Cotizacion extends Component

{

    public $tipoCotizacion;
    public $fromFlujo = false;

    // ── Buscador de pedido ────────────────────────────────────────────────
    public $busquedaPedido     = '';
    public $pedidosEncontrados = [];
    public $pedidoVinculado    = null;   // array con datos del pedido elegido
    public $pedidoId           = null;   // id que se inyecta en el form hidden

    public function mount($id)
    {
        $this->tipoCotizacion = $id;
        $this->fromFlujo = request()->get('from') === 'flujo';

        // Si vienen con pedidoId por query-string lo pre-selecciona
        $pid = request()->get('pedidoId');
        if ($pid) {
            $this->seleccionarPedido((int) $pid);
        }
    }

    public function updatedBusquedaPedido()
    {
        $term = trim($this->busquedaPedido);
        if (strlen($term) < 2) {
            $this->pedidosEncontrados = [];
            return;
        }
        $esNum = is_numeric($term);
        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->whereNotIn('p.estado', ['cancelado'])
            ->select(
                'p.id', 'p.estado', 'p.created_at',
                'c.nombre as cliente', 'c.rtn',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id AND o.estado = \'ganadora\') as has_ganadora')
            )
            ->orderByDesc('p.created_at')
            ->limit(8);

        if ($esNum) {
            $q->where('p.id', (int) $term);
        } else {
            $like = '%' . $term . '%';
            $q->where(function ($sub) use ($like) {
                $sub->where('c.nombre', 'LIKE', $like)
                    ->orWhere('c.rtn', 'LIKE', $like);
            });
        }
        $this->pedidosEncontrados = $q->get()->toArray();
    }

    public function seleccionarPedido(int $id)
    {
        $p = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->where('p.id', $id)
            ->select('p.id', 'p.estado', 'p.created_at', 'c.nombre as cliente', 'c.rtn')
            ->first();
        if ($p) {
            $this->pedidoId       = $p->id;
            $this->pedidoVinculado = (array) $p;
        }
        $this->busquedaPedido     = '';
        $this->pedidosEncontrados = [];
    }

    public function desvincularPedido()
    {
        $this->pedidoId        = null;
        $this->pedidoVinculado = null;
        $this->busquedaPedido  = '';
        $this->pedidosEncontrados = [];
    }

    public function render()
    {
        $tipoCotizacion = $this->tipoCotizacion;
        $fromFlujo      = $this->fromFlujo;
        return view('livewire.cotizaciones.cotizacion', compact('tipoCotizacion', 'fromFlujo'));
    }


    public function subirAdjunto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archivo' => 'required|file|mimes:pdf,jpeg,jpg,png,gif|max:5120',
                'tipo'    => 'required|in:orden_compra,forma_f01',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Archivo inválido',
                    'text'  => $validator->errors()->first('archivo'),
                ], 422);
            }

            $archivo   = $request->file('archivo');
            $tipo      = $request->input('tipo');
            $ext       = $archivo->getClientOriginalExtension();
            $nombre    = 'oferta_' . $tipo . '_' . time() . '_' . uniqid() . '.' . $ext;
            $carpeta   = public_path('adjuntos_oferta');

            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            $archivo->move($carpeta, $nombre);

            return response()->json([
                'ruta'   => 'adjuntos_oferta/' . $nombre,
                'nombre' => $archivo->getClientOriginalName(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['title' => 'Error', 'text' => 'No se pudo subir el archivo.'], 500);
        }
    }

    public function listarClientes(Request $request)
    {
        try {
            $rolId   = Auth::user()->rol_id;
            $like    = '%' . $request->search . '%';

            // Administrador (1), Televendedor/Facturador (3) y Mercadeo (9)
            // ven TODOS los clientes activos sin restricción
            if (in_array($rolId, [1, 3, 9])) {
                $listaClientes = DB::select("
                    SELECT id, nombre AS text
                    FROM cliente
                    WHERE estado_cliente_id = 1
                      AND id <> 1
                      AND (id LIKE ? OR nombre LIKE ?)
                    ORDER BY nombre
                    LIMIT 20
                ", [$like, $like]);

                return response()->json(["results" => $listaClientes], 200);
            }

            // Asesor Comercial (2) y cualquier otro rol:
            // solo los clientes que tienen asignado al usuario como vendedor
            $listaClientes = DB::select("
                SELECT id, nombre AS text
                FROM cliente
                WHERE estado_cliente_id = 1
                  AND id <> 1
                  AND vendedor = ?
                  AND (id LIKE ? OR nombre LIKE ?)
                ORDER BY nombre
                LIMIT 20
            ", [Auth::id(), $like, $like]);

            return response()->json(["results" => $listaClientes], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

    public function clientesCorporativo(Request $request)
    {

        if (Auth::user()->rol_id == 1 || Auth::user()->rol_id == 9) {
            $listaClientes = DB::SELECT("
            select
                id,
                nombre as text
            from cliente
                where estado_cliente_id = 1
                and tipo_cliente_id=1
                and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                    ");
        } elseif (Auth::user()->rol_id == 3) {
            $listaClientes = DB::SELECT("
            select
                id,
                nombre as text
            from cliente
                where estado_cliente_id = 1
                and tipo_cliente_id=1
                and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                    ");
        }else {
            $listaClientes = DB::SELECT("
            select
                id,
                nombre as text
            from cliente
                where estado_cliente_id = 1
                and tipo_cliente_id=1
                and vendedor =" . Auth::user()->id . "
                and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                    ");
        }

        return $listaClientes;
    }

    public function clientesEstatal(Request $request)
    {

        if (Auth::user()->rol_id == 1 || Auth::user()->rol_id == 3 || Auth::user()->rol_id == 9) {
            $listaClientes = DB::SELECT("
                    select
                        id,
                        nombre as text
                    from cliente
                        where estado_cliente_id = 1
                        and tipo_cliente_id=2
                        and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                            ");
        } else {
            $listaClientes = DB::SELECT("
                    select
                        id,
                        nombre as text
                    from cliente
                        where estado_cliente_id = 1
                        and tipo_cliente_id=2
                        and vendedor =" . Auth::user()->id . "
                        and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                            ");
        }

        return $listaClientes;
    }

    public function clientesExonerados(Request $request)
    {


        if (Auth::user()->rol_id == 1 || Auth::user()->rol_id == 9) {
            $listaClientes = DB::SELECT("
                    select
                        id,
                        nombre as text
                    from cliente
                        where estado_cliente_id = 1
                        and id<>1
                        and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                            ");
        } else {
            $listaClientes = DB::SELECT("
                    select
                        id,
                        nombre as text
                    from cliente
                        where estado_cliente_id = 1
                        and id<>1
                        and vendedor =" . Auth::user()->id . "
                        and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                            ");
        }
        return $listaClientes;
    }

    public function guardarCotizacion(Request $request){
       try {

        //dd($request);

        $validator = Validator::make($request->all(), [



            'subTotalGeneralGrabado' => 'required',
            'subTotalGeneral' => 'required',
            'isvGeneral' => 'required',
            'totalGeneral' => 'required',
            'numeroInputs' => 'required',
            'seleccionarCliente' => 'required',
            'nombre_cliente_ventas' => 'required',
            // bodega y seleccionarProducto son campos del buscador de productos,
            // no son datos a guardar — los productos reales vienen en bodega{idx}, idProducto{idx}, etc.


        ]);



        if ($validator->fails()) {
            return response()->json([
                'icon' => 'error',
                'title' => 'error',
                'text' => 'Por favor, verificar que todos los campos esten completados.',
                'mensaje' => 'Ha ocurrido un error al crear la compra.',
                'errors' => $validator->errors()
            ], 401);
        }


        $arrayTemporal = $request->arregloIdInputs;
        $arrayInputs = explode(',', $arrayTemporal);
        $arrayProductos = [];

        DB::beginTransaction();

            $cotizacion = new ModelCotizacion();
            $cotizacion->nombre_cliente = $request->nombre_cliente_ventas;
            $cotizacion->RTN = $request->rtn_ventas;
            $cotizacion->fecha_emision = $request->fecha_emision;
            $cotizacion->fecha_vencimiento = $request->fecha_vencimiento ?: $request->fecha_emision;
            $cotizacion->sub_total = $request->subTotalGeneral;
            $cotizacion->sub_total_grabado=$request->subTotalGeneralGrabado;
            $cotizacion->sub_total_excento=$request->subTotalGeneralExcento;
            $cotizacion->isv= $request->isvGeneral;
            $cotizacion->total = $request->totalGeneral;
            $cotizacion->cliente_id = $request->seleccionarCliente;
            $cotizacion->tipo_venta_id = $request->tipo_venta_id;
            $cotizacion->vendedor = $request->vendedor;
            $cotizacion->users_id = Auth::user()->id;
            $cotizacion->arregloIdInputs = json_encode($request->arregloIdInputs);
            $cotizacion->numeroInputs = $request->numeroInputs;
            $cotizacion->porc_descuento = $request->porDescuento;
            $cotizacion->monto_descuento = $request->descuentoGeneral;
            $cotizacion->nota = $request->nota_comen ?? $request->nota;
            $cotizacion->tipo_pago_id = $request->tipoPagoVenta ?: null;
            $cotizacion->estado_id  = 1;
            $cotizacion->created_by = Auth::id();
            $cotizacion->save();

            $numeroOrdenCompra = $request->numero_orden_compra ?: null;
            $archivoOrdenCompra = $request->archivo_orden_compra ?: null;
            $numeroFormaF01 = $request->numero_forma_f01 ?: null;
            $archivoFormaF01 = $request->archivo_forma_f01 ?: null;
            $numeroExoneracion = $request->numero_exoneracion
                ?: ($request->codigo_exoneracion
                    ?: ($request->codigo
                        ?: $request->codigoExoneracion));
            $archivoExoneracion = $request->archivo_exoneracion ?: null;

            // ── Registrar en historico_flujo / crear flujo según si hay pedido/flujo vinculado ──
            $pedidoIdVinculado = $request->pedido_id ? (int) $request->pedido_id : null;
            $flujoIdDirecto    = $request->flujo_id  ? (int) $request->flujo_id  : null;

            // ID del estado "cancelado" para verificar flujos cancelados
            $canceladoEstadoId = (int) (DB::table('estado_venta')->where('descripcion', 'cancelado')->value('id') ?? 4);

            if ($pedidoIdVinculado) {
                // Flujo con pedido: buscar el flujo del pedido y agregar historico
                $flujoExistente = DB::table('flujo')
                    ->where('identificacion', (string) $pedidoIdVinculado)
                    ->where('tipo_flujo_id', 1)
                    ->first(['id', 'estado_id']);
                $flujoIdVinculado = $flujoExistente->id ?? null;

                if ($flujoIdVinculado && (int) $flujoExistente->estado_id !== $canceladoEstadoId) {
                    // Flujo activo: agregar oferta al flujo existente
                    DB::table('historico_flujo')->insert([
                        'flujo_id'        => $flujoIdVinculado,
                        'tipo_tramite_id' => 2, // 'Ofertas'
                        'tramite_id'      => $cotizacion->id,
                        'estado_id'       => 1,
                        'observaciones'   => 'Oferta registrada para pedido #' . $pedidoIdVinculado,
                        'created_by'      => Auth::id(),
                        'updated_by'      => Auth::id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    DB::table('flujo')->where('id', $flujoIdVinculado)
                        ->update([
                            'tipo_tramite_id'      => 2,
                            'numero_orden_compra'  => $numeroOrdenCompra,
                            'archivo_orden_compra' => $archivoOrdenCompra,
                            'numero_forma_f01'     => $numeroFormaF01,
                            'archivo_forma_f01'    => $archivoFormaF01,
                            'numero_exoneracion'   => $numeroExoneracion,
                            'archivo_exoneracion'  => $archivoExoneracion,
                            'updated_by'           => Auth::id(),
                            'updated_at'           => now(),
                        ]);
                } else {
                    // Flujo cancelado o inexistente: crear nuevo flujo para esta cotización
                    $flujoNuevo = DB::table('flujo')->insertGetId([
                        'tipo_flujo_id'   => 1,
                        'identificacion'  => (string) $cotizacion->id,
                        'nombre'          => $cotizacion->nombre_cliente ?? ('Cotizacion #' . $cotizacion->id),
                        'cliente_rtn'     => $request->rtn_ventas ?? null,
                        'numero_orden_compra'  => $numeroOrdenCompra,
                        'archivo_orden_compra' => $archivoOrdenCompra,
                        'numero_forma_f01'     => $numeroFormaF01,
                        'archivo_forma_f01'    => $archivoFormaF01,
                        'numero_exoneracion'   => $numeroExoneracion,
                        'archivo_exoneracion'  => $archivoExoneracion,
                        'tipo_tramite_id' => 2,
                        'estado_id'       => 1,
                        'created_by'      => Auth::id(),
                        'updated_by'      => Auth::id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    DB::table('historico_flujo')->insert([
                        'flujo_id'        => $flujoNuevo,
                        'tipo_tramite_id' => 2,
                        'tramite_id'      => $cotizacion->id,
                        'estado_id'       => 1,
                        'observaciones'   => 'Oferta duplicada desde flujo cancelado (pedido #' . $pedidoIdVinculado . ')',
                        'created_by'      => Auth::id(),
                        'updated_by'      => Auth::id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    $flujoIdVinculado = null; // el flujo del pedido ya no aplica
                }
            } elseif ($flujoIdDirecto) {
                // Flujo sin pedido ya existente: verificar si está cancelado
                $flujoDirecto = DB::table('flujo')->where('id', $flujoIdDirecto)->first(['estado_id']);

                if ($flujoDirecto && (int) $flujoDirecto->estado_id !== $canceladoEstadoId) {
                    // Flujo activo: agregar nueva oferta al mismo flujo
                    DB::table('historico_flujo')->insert([
                        'flujo_id'        => $flujoIdDirecto,
                        'tipo_tramite_id' => 2,
                        'tramite_id'      => $cotizacion->id,
                        'estado_id'       => 1,
                        'observaciones'   => 'Oferta adicional #' . $cotizacion->id . ' registrada en flujo existente',
                        'created_by'      => Auth::id(),
                        'updated_by'      => Auth::id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    DB::table('flujo')->where('id', $flujoIdDirecto)
                        ->update([
                            'numero_orden_compra'  => $numeroOrdenCompra,
                            'archivo_orden_compra' => $archivoOrdenCompra,
                            'numero_forma_f01'     => $numeroFormaF01,
                            'archivo_forma_f01'    => $archivoFormaF01,
                            'numero_exoneracion'   => $numeroExoneracion,
                            'archivo_exoneracion'  => $archivoExoneracion,
                            'updated_by'           => Auth::id(),
                            'updated_at'           => now(),
                        ]);
                } else {
                    // Flujo cancelado: crear nuevo flujo para esta cotización
                    $flujoNuevo = DB::table('flujo')->insertGetId([
                        'tipo_flujo_id'   => 1,
                        'identificacion'  => (string) $cotizacion->id,
                        'nombre'          => $cotizacion->nombre_cliente ?? ('Cotizacion #' . $cotizacion->id),
                        'cliente_rtn'     => $request->rtn_ventas ?? null,
                        'numero_orden_compra'  => $numeroOrdenCompra,
                        'archivo_orden_compra' => $archivoOrdenCompra,
                        'numero_forma_f01'     => $numeroFormaF01,
                        'archivo_forma_f01'    => $archivoFormaF01,
                        'numero_exoneracion'   => $numeroExoneracion,
                        'archivo_exoneracion'  => $archivoExoneracion,
                        'tipo_tramite_id' => 2,
                        'estado_id'       => 1,
                        'created_by'      => Auth::id(),
                        'updated_by'      => Auth::id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    DB::table('historico_flujo')->insert([
                        'flujo_id'        => $flujoNuevo,
                        'tipo_tramite_id' => 2,
                        'tramite_id'      => $cotizacion->id,
                        'estado_id'       => 1,
                        'observaciones'   => 'Oferta duplicada desde flujo cancelado #' . $flujoIdDirecto,
                        'created_by'      => Auth::id(),
                        'updated_by'      => Auth::id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    $flujoIdDirecto = null; // el flujo directo ya no aplica
                }
            } else {
                // Sin pedido ni flujo vinculado: crear un nuevo flujo para esta cotizacion
                $flujoNuevo = DB::table('flujo')->insertGetId([
                    'tipo_flujo_id'   => 1,
                    'identificacion'  => (string) $cotizacion->id,
                    'nombre'          => $cotizacion->nombre_cliente ?? ('Cotizacion #' . $cotizacion->id),
                    'cliente_rtn'     => $request->rtn_ventas ?? null,
                    'numero_orden_compra'  => $numeroOrdenCompra,
                    'archivo_orden_compra' => $archivoOrdenCompra,
                    'numero_forma_f01'     => $numeroFormaF01,
                    'archivo_forma_f01'    => $archivoFormaF01,
                    'numero_exoneracion'   => $numeroExoneracion,
                    'archivo_exoneracion'  => $archivoExoneracion,
                    'tipo_tramite_id' => 2,
                    'estado_id'       => 1,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoNuevo,
                    'tipo_tramite_id' => 2,
                    'tramite_id'      => $cotizacion->id,
                    'estado_id'       => 1,
                    'observaciones'   => 'Oferta sin pedido',
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            for ($i = 0; $i < count($arrayInputs); $i++) {

                $keyRestaInventario = "restaInventario" . $arrayInputs[$i];
                $keyIdSeccion = "idSeccion" . $arrayInputs[$i];
                $keyIdProducto = "idProducto" . $arrayInputs[$i];
                $keyIdUnidadVenta = "idUnidadVenta" . $arrayInputs[$i];
                $keyPrecio = "precio" . $arrayInputs[$i];
                $keyCantidad = "cantidad" . $arrayInputs[$i];
                $keySubTotal = "subTotal" . $arrayInputs[$i];
                $keyIsvPagar = "isvProducto" . $arrayInputs[$i];
                $keyTotal = "total" . $arrayInputs[$i];
                $keyIsvAsigando = "isv" . $arrayInputs[$i];
                $keyunidad = 'unidad' . $arrayInputs[$i];
                $keyidBodega = 'idBodega'.$arrayInputs[$i];
                $keyidPrecioSeleccionado = 'idPrecioSeleccionado'.$arrayInputs[$i];
                $keyprecioSeleccionado = 'precios'.$arrayInputs[$i];
                $keyNombreProducto = 'nombre'.$arrayInputs[$i];
                $keyBodegaNombre = 'bodega'.$arrayInputs[$i];
                $keymonto_descProducto = 'acumuladoDescuento'.$arrayInputs[$i];
                $keyprecios_producto_carga_id = 'precios_producto_carga_id'.$arrayInputs[$i];



                $restaInventario = $request->$keyRestaInventario;
                $idSeccion = $request->$keyIdSeccion;
                $idProducto = $request->$keyIdProducto;
                $idUnidadVenta = $request->$keyIdUnidadVenta;
                $isvProductoPagar = $request->$keyIsvPagar;
                $idPrecioSeleccionado = $request->$keyidPrecioSeleccionado;
                $precioSeleccionado = $request->$keyprecioSeleccionado;

                $precios_producto_carga_id = $request->$keyprecios_producto_carga_id;
                //$unidad = $request->$keyunidad;
                $precio = $request->$keyPrecio;
                $cantidad = $request->$keyCantidad;
                $subTotal = $request->$keySubTotal;

                $total = $request->$keyTotal;
                $idBodega = $request->$keyidBodega;
                $ivsProductoAsignado = $request->$keyIsvAsigando;
                $nombreProducto = $request->$keyNombreProducto;
                $nombreBodega = $request->$keyBodegaNombre;
                $monto_descProducto = $request->$keymonto_descProducto;


                array_push($arrayProductos,[
                'cotizacion_id'=> $cotizacion->id,
                'producto_id'=> $idProducto,
                'indice'=>$arrayInputs[$i],
                'nombre_producto'=>$nombreProducto,
                'nombre_bodega'=> $nombreBodega,
                'precio_unidad'=>$precio,
                'cantidad'=>$cantidad,
                'sub_total'=>$subTotal,
                'isv'=> $isvProductoPagar,
                'total'=> $total,
                'Bodega_id'=>$idBodega,
                'seccion_id'=>$idSeccion,
                'resta_inventario'=>$restaInventario,
                'isv_producto'=>$ivsProductoAsignado,
                'unidad_medida_venta_id'=>$idUnidadVenta,
                'monto_descProducto'=>$monto_descProducto,
                'idPrecioSeleccionado'=>$idPrecioSeleccionado,
                'precioSeleccionado'=>$precioSeleccionado,
                'precios_producto_carga_id'=>$precios_producto_carga_id,
                'created_at'=>now(),
                'updated_at'=>now()

                ]);

            };

            //dd($arrayProductos);
        ModelCotizacionProducto::insert($arrayProductos);




        DB::commit();
        return response()->json([
            'icon'      => 'success',
            'text'      => 'Cotización guardada con éxito.',
            'title'     => 'Exito!',
            'idFactura' => $cotizacion->id,
            'pedidoId'  => $pedidoIdVinculado ?: null,
            'flujoId'   => $flujoIdVinculado ?? $flujoIdDirecto ?? $flujoNuevo ?? null,
        ],200);

        } catch (QueryException $e) {
        DB::rollback();
        return response()->json([
            'icon'=>'error',
            'text'=>'Ha ocurrido un error al guardar la cotización.',
            'title'=>'Error!',
            'message' => $e,
            'error' => $e
        ],402);
        }
    }

    public function ofertasPorPedido($pedidoId)
    {
        $flujoId = DB::table('flujo')
            ->where('identificacion', (string) (int) $pedidoId)
            ->where('tipo_flujo_id', 1)
            ->value('id');

        if (!$flujoId) {
            return response()->json([]);
        }

        $ofertas = DB::table('historico_flujo as hf')
            ->join('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $flujoId)
            ->where('hf.tipo_tramite_id', 2)
            ->select('c.id', 'c.nombre_cliente', 'c.total', 'c.created_at',
                     DB::raw("IF(hf.observaciones = 'ganadora', 1, 0) as es_ganadora"))
            ->orderByDesc('c.id')
            ->get();

        // Incluir productos de cada oferta para mostrar el detalle en el modal
        $ofertasConProductos = $ofertas->map(function ($o) {
            $o->productos = DB::table('cotizacion_has_producto as chp')
                ->join('producto as p', 'p.id', '=', 'chp.producto_id')
                ->where('chp.cotizacion_id', $o->id)
                ->select('p.nombre', 'chp.cantidad', 'chp.precio_unidad', 'chp.total')
                ->orderBy('chp.indice')
                ->get();
            return $o;
        });

        return response()->json($ofertasConProductos);
    }

    public function marcarGanadora(Request $request)
    {
        $id = (int) $request->input('cotizacion_id');
        if (!$id) {
            return response()->json(['error' => 'ID requerido'], 422);
        }

        // Marcar en historico_flujo: esta oferta como ganadora, el resto como no-ganadora
        $hf = DB::table('historico_flujo')
            ->where('tramite_id', $id)
            ->where('tipo_tramite_id', 2)
            ->first();

        if ($hf) {
            // Quitar 'ganadora' de las otras ofertas del mismo flujo
            DB::table('historico_flujo')
                ->where('flujo_id', $hf->flujo_id)
                ->where('tipo_tramite_id', 2)
                ->where('observaciones', 'ganadora')
                ->update(['observaciones' => null, 'updated_at' => now()]);

            // Marcar esta como ganadora
            DB::table('historico_flujo')
                ->where('id', $hf->id)
                ->update(['observaciones' => 'ganadora', 'updated_at' => now()]);

            // Avanzar el flujo al estado "Prefactura" (tipo_tramite_id=3)
            DB::table('flujo')->where('id', $hf->flujo_id)
                ->update(['tipo_tramite_id' => 3, 'updated_by' => Auth::id(), 'updated_at' => now()]);
        }

        return response()->json(['success' => true, 'cotizacion_id' => $id]);
    }

    public function imprimirCotizacion($idFactura)
    {

        $datos = DB::SELECTONE("
            select
            A.cliente_id as clienteId,
            concat(YEAR(NOW()),'-',A.id) as codigo,
            B.nombre,
            B.direccion,
            B.correo,
            B.telefono_empresa,
            A.fecha_emision,
            time(A.created_at) as hora,
            A.fecha_vencimiento,
            B.rtn,
            users.name as cotizador,
            (select name from users where id = A.vendedor) as vendedor,
            A.nota,
            (select hf.flujo_id from historico_flujo hf where hf.tramite_id = A.id and hf.tipo_tramite_id = 2 limit 1) as flujo_id,
            IFNULL(TP.descripcion, 'contado') as tipo_pago
            from cotizacion A
            inner join cliente B
            on A.cliente_id = B.id
            inner join users
            ON users.id = A.users_id
            left join tipo_pago_venta TP on TP.id = A.tipo_pago_id
            where A.id =".$idFactura
        );

        $productos = DB::SELECT("
            select
            C.id as codigo,
            C.nombre,
            C.descripcion,
            if(C.isv = 0, 'SI' , 'NO' ) as excento,
            FORMAT(B.precio_unidad,2) as precio,
            FORMAT(B.cantidad,2) as cantidad,
            FORMAT(B.sub_total,2) as importe,
            J.nombre as medida

            from cotizacion A
            inner join cotizacion_has_producto B
            on A.id=B.cotizacion_id
            inner join producto C
            on B.producto_id = C.id
            inner join unidad_medida_venta D
            on B.unidad_medida_venta_id = D.id
            inner join unidad_medida J
            on J.id = D.unidad_medida_id
            where A.id = ".$idFactura."
            order by B.indice asc
            "
        );

        $importes = DB::SELECTONE("
            select
            porc_descuento,
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento,
            monto_descuento
            from cotizacion
            where id = ".$idFactura
        );


        $importesConCentavos= DB::SELECTONE("
            select
            FORMAT(monto_descuento,2) as monto_descuento,
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from cotizacion where id = ".$idFactura
        );


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }
         $tipoCot = 2;
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/cotizacion',compact('datos','productos','importes','importesConCentavos','flagCentavos','numeroLetras', 'tipoCot'))->setPaper('letter');

        return $pdf->stream("Cotizacion_NO_".$datos->codigo.".pdf");


    }

    public function imprimirCatalogo($idCotizacion)
    {
        $datos = DB::SELECT(
            "
                select
                      C.id as codigoProducto,
                    C.nombre as nombre1,
                    C.descripcion as nombre,
                    if(C.isv = 0, 'SI' , 'NO' ) as excento,
                    FORMAT(B.precio_unidad,2) as precio,
                    B.cantidad as cantidad,
                    FORMAT(B.sub_total,2) as importe,
                    J.nombre as medida,
                    C.codigo_barra,
                    E.descripcion as 'subcategoria',
                    F.descripcion as 'categoria',
                    G.nombre as 'marca',
                    imagen.url_img as 'imagen',
                    A.nombre_cliente,
                    A.fecha_emision,
                    A.RTN,
                    A.id,
                    CONCAT(YEAR(A.fecha_emision),'-',A.id) as 'cotizacion'

                from cotizacion A
                    inner join cotizacion_has_producto B on A.id=B.cotizacion_id
                    inner join producto C on B.producto_id = C.id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join sub_categoria E on E.id = C.sub_categoria_id
                    inner join categoria_producto F on F.id = E.categoria_producto_id
                    inner join marca G on G.id = C.marca_id
                    inner join img_producto imagen on imagen.producto_id = C.id
                where A.id = ".$idCotizacion."
                order by B.indice asc
            "
        );
        $pdf = PDF::loadView('/pdf/catalogo',compact('datos'))->setPaper("A4", "portrait");

        return $pdf->stream("catalogo.pdf");

    }

    public function fichaProductosPdf($ofertaId)
    {
        if (!Auth::check()) { abort(403); }

        $id = (int) $ofertaId;
        if ($id <= 0) { abort(404); }

        $oferta = DB::selectOne("
            SELECT c.id, c.nombre_cliente, c.RTN, c.fecha_emision, c.fecha_vencimiento,
                   c.sub_total, c.isv, c.total, c.monto_descuento, c.porc_descuento,
                   u.name as registrado_por
            FROM cotizacion c
            LEFT JOIN users u ON u.id = c.users_id
            WHERE c.id = ?
        ", [$id]);

        if (!$oferta) { abort(404); }

        $productos = DB::select("
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.codigo_barra,
                p.codigo_estatal,
                FORMAT(chp.cantidad, 0) as cantidad,
                FORMAT(chp.precio_unidad, 2) as precio,
                FORMAT(chp.sub_total, 2) as sub_total,
                IF(p.isv = 0, 'Exento', 'Gravado') as tipo_isv,
                um.nombre as medida,
                sc.descripcion as sub_categoria,
                cp.descripcion as categoria,
                m.nombre as marca
            FROM cotizacion_has_producto chp
            INNER JOIN producto p ON chp.producto_id = p.id
            INNER JOIN unidad_medida_venta umv ON chp.unidad_medida_venta_id = umv.id
            INNER JOIN unidad_medida um ON um.id = umv.unidad_medida_id
            INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id
            INNER JOIN categoria_producto cp ON cp.id = sc.categoria_producto_id
            INNER JOIN marca m ON m.id = p.marca_id
            WHERE chp.cotizacion_id = ?
            ORDER BY chp.indice ASC
        ", [$id]);

        // Fetch images per product (max 2 per product, lightweight)
        $imagenes = [];
        if (!empty($productos)) {
            $productoIds = array_map(fn($p) => $p->id, $productos);
            $imgs = DB::table('img_producto')
                ->select('producto_id', 'url_img')
                ->whereIn('producto_id', $productoIds)
                ->orderBy('producto_id')->orderBy('id')
                ->get();
            foreach ($imgs as $img) {
                if (!isset($imagenes[$img->producto_id]) || count($imagenes[$img->producto_id]) < 2) {
                    $imagenes[$img->producto_id][] = $img->url_img;
                }
            }
        }

        $descargadoPor = Auth::user()->name;

        $pdf = PDF::loadView('pdf/ficha-productos-oferta', compact('oferta', 'productos', 'imagenes', 'descargadoPor'))
            ->setPaper('letter', 'portrait');

        return $pdf->download("Catalogo_Oferta_{$id}.pdf");
    }

    public function imprimirProforma($idFactura)
    {

        $datos = DB::SELECTONE("
            select
            A.cliente_id AS clienteId,
            concat(YEAR(NOW()),'-',A.id) as codigo,
            B.nombre,
            B.direccion,
            B.correo,
            B.telefono_empresa,
            A.fecha_emision,
            time(A.created_at) as hora,
            A.fecha_vencimiento,
            B.rtn,
            users.name as cotizador,
            (select name from users where id = A.vendedor) as vendedor,
            A.nota
            from cotizacion A
            inner join cliente B
            on A.cliente_id = B.id
            inner join users
            ON users.id = A.users_id
            where A.id =".$idFactura
        );

            $productos = DB::SELECT("
                select
                C.id as codigo,
                C.nombre,
                C.descripcion,
                H.nombre as bodega,
                F.descripcion as seccion,
                if(C.isv = 0, 'SI' , 'NO' ) as excento,
                FORMAT(B.precio_unidad,2) as precio,
                FORMAT(B.cantidad,2) as cantidad,
                FORMAT(B.sub_total,2) as importe,
                J.nombre as medida

                from cotizacion A
                    inner join cotizacion_has_producto B on A.id=B.cotizacion_id
                    inner join producto C on B.producto_id = C.id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join seccion F on B.seccion_id = F.id
                    inner join segmento G on F.segmento_id = G.id
                    inner join bodega H on G.bodega_id = H.id
                where A.id = ".$idFactura."
                order by B.indice asc
                "
            );


            $importes = DB::SELECTONE("
            select
            porc_descuento,
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento,
            monto_descuento
            from cotizacion
            where id = ".$idFactura
        );


        $importesConCentavos= DB::SELECTONE("
            select
            FORMAT(monto_descuento,2) as monto_descuento,
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from cotizacion where id = ".$idFactura
        );


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/proforma',compact('datos','productos','importes','importesConCentavos','flagCentavos','numeroLetras'))->setPaper('letter');

        return $pdf->stream("proforma_NO_".$datos->codigo.".pdf");


    }

    public function validarProforma(Request $request, $id)
    {
        try {
            $idCotizacion = (int) $id;

            $cotizacion = DB::selectOne(
                "SELECT cliente_id, total FROM cotizacion WHERE id = ?",
                [$idCotizacion]
            );

            if (!$cotizacion) {
                return response()->json([
                    'valido' => false,
                    'icon'   => 'error',
                    'titulo' => 'Error',
                    'mensaje' => 'La cotización no fue encontrada.',
                ], 404);
            }

            $idCliente = (int) $cotizacion->cliente_id;
            $total     = $cotizacion->total;

            $cliente = DB::selectOne(
                "SELECT nombre, credito FROM cliente WHERE id = ?",
                [$idCliente]
            );

            

            $nombreCliente = $cliente ? $cliente->nombre : '';

            // Validación 1: facturas vencidas (misma lógica que comprobarFacturaVencida en FacturacionEstatal)
            $facturasVencidas = DB::select(
                "SELECT fa.id
                 FROM factura fa
                 INNER JOIN aplicacion_pagos ap ON ap.factura_id = fa.id
                 WHERE ap.estado_cerrado <> 2
                   AND ap.saldo <> 0
                   AND ap.estado = 1
                   AND fa.fecha_vencimiento < CURDATE()
                   AND fa.estado_venta_id = 1
                   AND fa.tipo_pago_id = 2
                   AND fa.cliente_id = ?",
                [$idCliente]
            );

            if (!empty($facturasVencidas)) {
                return response()->json([
                    'valido'  => false,
                    'icon'    => 'warning',
                    'titulo'  => 'Advertencia!',
                    'mensaje' => 'El cliente ' . $nombreCliente . ' cuenta con facturas vencidas. Por el momento no se puede imprimir la proforma.',
                ]);
            }

            // Validación 2: crédito insuficiente — omitida intencionalmente (la proforma sí se puede imprimir)

            return response()->json(['valido' => true]);

        } catch (QueryException $e) {
            return response()->json([
                'valido'  => false,
                'icon'    => 'error',
                'titulo'  => 'Error',
                'mensaje' => 'Ha ocurrido un error al validar la proforma.',
            ], 500);
        }
    }

    public function listarBodegas(Request $request)
    {
        try {

            // Stock neto = cantidad_disponible - reservado en prefacturas activas
            $results = DB::SELECT("
        SELECT
            A.seccion_id as id,
            D.id as 'idBodega',
            CONCAT(D.nombre,'',REPLACE(B.descripcion,'Seccion','')) as 'bodegaSeccion',
            concat(D.nombre,' - ', REPLACE(B.descripcion,'Seccion',''),' - cantidad ',
                GREATEST(0,
                    sum(A.cantidad_disponible) - COALESCE((
                        SELECT SUM(php2.cantidad)
                        FROM prefactura_has_producto php2
                        INNER JOIN prefactura pf2 ON pf2.id = php2.prefactura_id
                        WHERE pf2.estado = 'activo'
                          AND php2.producto_id = A.producto_id
                          AND php2.seccion_id  = A.seccion_id
                          AND php2.resta_inventario = 1
                    ), 0)
                )
            ) as 'text'
        FROM recibido_bodega A
            INNER JOIN seccion B
            ON A.seccion_id = B.id
            INNER JOIN segmento C
            ON B.segmento_id = C.id
            INNER JOIN bodega D
            ON C.bodega_id = D.id
        WHERE  producto_id = " . (int)$request->idProducto . "
        AND (D.nombre LIKE '%" . addslashes($request->search) . "%' OR B.descripcion LIKE '%" . addslashes($request->search) . "%')
        GROUP BY A.seccion_id
            ");

            return response()->json([
                "results" => $results
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

}

