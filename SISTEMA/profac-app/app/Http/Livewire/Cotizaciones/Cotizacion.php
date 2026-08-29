<?php

namespace App\Http\Livewire\Cotizaciones;

use App\Support\ClienteActoresAsignados;
use App\Support\ExpoConfig;
use App\Support\ExpoStock;
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

    private function obtenerUbicacionSinExistencia(): array
    {
        $nombreTecnico = 'SIN EXISTENCIA COTIZACION';

        $bodegaId = (int) (DB::table('bodega')
            ->whereRaw('UPPER(TRIM(nombre)) = ?', [strtoupper($nombreTecnico)])
            ->value('id') ?? 0);

        if ($bodegaId <= 0) {
            $estado = (int) (DB::table('bodega')->whereNotNull('estado_id')->orderBy('id')->value('estado_id') ?? 1);
            $municipio = (int) (DB::table('bodega')->whereNotNull('municipio_id')->orderBy('id')->value('municipio_id') ?? 1);
            $encargado = (int) (DB::table('bodega')->whereNotNull('encargado_bodega')->orderBy('id')->value('encargado_bodega') ?? 1);

            $bodegaId = (int) DB::table('bodega')->insertGetId([
                'nombre' => $nombreTecnico,
                'direccion' => 'Bodega tecnica para productos sin existencia en cotizacion',
                'estado_id' => $estado,
                'municipio_id' => $municipio,
                'encargado_bodega' => $encargado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($bodegaId <= 0) {
            throw new \RuntimeException('No existe ninguna bodega para asignar productos sin existencia.');
        }

        $segmentoId = DB::table('segmento')
            ->where('bodega_id', (int) $bodegaId)
            ->whereRaw('UPPER(TRIM(descripcion)) = ?', [strtoupper($nombreTecnico)])
            ->value('id');

        if (!$segmentoId) {
            $segmentoId = DB::table('segmento')->insertGetId([
                'descripcion' => $nombreTecnico,
                'bodega_id' => (int) $bodegaId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $seccionId = DB::table('seccion')
            ->where('segmento_id', (int) $segmentoId)
            ->whereRaw('UPPER(TRIM(descripcion)) = ?', [strtoupper($nombreTecnico)])
            ->value('id');

        if (!$seccionId) {
            $estadoSeccion = (int) (DB::table('seccion')->whereNotNull('estado_id')->orderBy('id')->value('estado_id') ?? 1);
            $numeracion = (int) (DB::table('seccion')->where('segmento_id', (int) $segmentoId)->max('numeracion') ?? 0) + 1;

            $seccionId = DB::table('seccion')->insertGetId([
                'descripcion' => $nombreTecnico,
                'numeracion' => $numeracion,
                'estado_id' => $estadoSeccion,
                'segmento_id' => (int) $segmentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'bodega_id' => (int) $bodegaId,
            'seccion_id' => (int) $seccionId,
            'nombre_bodega' => 'SIN EXISTENCIA',
        ];
    }

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

            $query = DB::table('cliente')
                ->select('id', 'nombre as text')
                ->where('estado_cliente_id', 1)
                ->where('id', '<>', 1)
                ->where(function ($q) use ($like) {
                    $q->where('id', 'LIKE', $like)
                      ->orWhere('nombre', 'LIKE', $like);
                });

            // Los roles comerciales ven clientes asignados en cualquiera de sus funciones comerciales.
            $specialUsers = [121, 122];
            if (in_array((int) $rolId, [
                ClienteActoresAsignados::ROL_ASESOR_COMERCIAL,
                ClienteActoresAsignados::ROL_TELE_ASESOR,
            ], true)) {
                $query->whereExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('cliente_usuario as cu')
                        ->whereColumn('cu.cliente_id', 'cliente.id')
                        ->where('cu.usuario_id', Auth::id())
                        ->whereIn('cu.rol_id', [
                            ClienteActoresAsignados::ROL_ASESOR_COMERCIAL,
                            ClienteActoresAsignados::ROL_TELE_ASESOR,
                        ]);
                });
            } elseif ($rolId !== 1 && !in_array(Auth::id(), $specialUsers, true)) {
                $query->where('vendedor', Auth::id());
            }

            $listaClientes = $query->orderBy('nombre')->limit(20)->get();

            return response()->json(['results' => $listaClientes], 200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

    public function listadoActoresAsignados(Request $request)
    {
        $datos = $request->validate([
            'cliente_id' => 'required|integer|exists:cliente,id',
            'rol_id' => 'required|integer|in:2,3',
            'search' => 'nullable|string|max:150',
        ]);

        $usuarios = ClienteActoresAsignados::usuarios((int) $datos['cliente_id'], (int) $datos['rol_id']);
        $busqueda = trim((string) ($datos['search'] ?? ''));
        if ($busqueda !== '') {
            $usuarios = $usuarios->filter(function ($usuario) use ($busqueda) {
                return stripos($usuario->text, $busqueda) !== false;
            })->values();
        }

        return response()->json([
            'results' => $usuarios,
            'bloqueado' => $usuarios->count() === 1,
        ]);
    }

    public function listadoVendedoresAsignados(Request $request)
    {
        $request->merge(['rol_id' => ClienteActoresAsignados::ROL_ASESOR_COMERCIAL]);
        return $this->listadoActoresAsignados($request);
    }

    public function obtenerAsesorAsignado(Request $request)
    {
        $request->merge(['rol_id' => ClienteActoresAsignados::ROL_ASESOR_COMERCIAL]);
        $response = $this->listadoActoresAsignados($request);
        $datos = $response->getData(true);
        $asesores = $datos['results'] ?? [];

        return response()->json([
            'asesor' => count($asesores) === 1 ? $asesores[0] : null,
            'asesores' => $asesores,
            'puede_editar' => count($asesores) > 1,
        ]);
    }

    public function clientesCorporativo(Request $request)
    {
        $specialUsers = [121, 122];
        if (Auth::user()->rol_id == 1 || Auth::user()->rol_id == 9 || in_array(Auth::id(), $specialUsers, true)) {
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
        $specialUsers = [121, 122];
        if (Auth::user()->rol_id == 1 || Auth::user()->rol_id == 3 || Auth::user()->rol_id == 9 || in_array(Auth::id(), $specialUsers, true)) {
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
        $specialUsers = [121, 122];
        if (Auth::user()->rol_id == 1 || Auth::user()->rol_id == 9 || in_array(Auth::id(), $specialUsers, true)) {
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
            'vendedor' => 'required|integer|exists:users,id',
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

        ClienteActoresAsignados::validar(
            (int) $request->seleccionarCliente,
            (int) $request->vendedor,
            ClienteActoresAsignados::ROL_ASESOR_COMERCIAL,
            'vendedor'
        );


        $arrayTemporal = $request->arregloIdInputs;
        $arrayInputs = explode(',', $arrayTemporal);
        $arrayProductos = [];

        $expoId = (int) $request->input('expo_id', 0);
        $tipoVentaExpoId = ExpoConfig::tipoVentaId();
        $expoConfig = null;

        if ($expoId <= 0 && $tipoVentaExpoId && (int) $request->tipo_venta_id === $tipoVentaExpoId) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Expo no válida',
                'text' => 'Toda Oferta de Expo debe estar vinculada a una configuración vigente.',
            ], 422);
        }

        if ($expoId > 0) {
            $expoConfig = ExpoConfig::detalleActivaParaUsuario(
                $expoId,
                Auth::id(),
                (int) $request->seleccionarCliente
            );
            if (!$expoConfig || !$tipoVentaExpoId) {
                return response()->json([
                    'icon' => 'error',
                    'title' => 'Expo no disponible',
                    'text' => 'La Expo ya no está activa o está fuera de vigencia.',
                ], 422);
            }

            $ventaBruta = 0.0;
            $cantidadesExpo = [];
            foreach ($arrayInputs as $indice) {
                $precioCargaId = (int) $request->input('precios_producto_carga_id' . $indice, 0);
                $escalaId = (int) (DB::table('precios_producto_carga')->where('id', $precioCargaId)->value('categoria_precios_id') ?? 0);
                if (!in_array($escalaId, $expoConfig['escalas'], true)) {
                    return response()->json([
                        'icon' => 'error', 'title' => 'Escala no permitida',
                        'text' => 'Uno de los productos utiliza una escala que no pertenece a la Expo.',
                    ], 422);
                }

                $productoId = (int) $request->input('idProducto' . $indice, 0);
                $cantidadBase = (float) $request->input('cantidad' . $indice, 0)
                    * (float) $request->input('unidad' . $indice, 0);
                if ($productoId <= 0 || $cantidadBase <= 0) {
                    return response()->json([
                        'icon' => 'error', 'title' => 'Cantidad no válida',
                        'text' => 'Todos los productos de la Oferta Expo deben tener una cantidad válida.',
                    ], 422);
                }
                $cantidadesExpo[$productoId] = ($cantidadesExpo[$productoId] ?? 0) + $cantidadBase;

                $ventaBruta += (float) $request->input('precio' . $indice, 0)
                    * $cantidadBase;
            }

            foreach ($cantidadesExpo as $productoId => $cantidadSolicitada) {
                if ($cantidadSolicitada > ExpoStock::disponible((int) $productoId, $expoConfig['bodegas']) + 0.00001) {
                    return response()->json([
                        'icon' => 'error', 'title' => 'Existencia insuficiente',
                        'text' => 'La cantidad solicitada supera la existencia agrupada disponible en las bodegas de la Expo.',
                    ], 422);
                }
            }

            $ubicacionVirtualExpo = ExpoStock::ubicacionVirtual();
            foreach ($arrayInputs as $indice) {
                $request->merge([
                    'idBodega' . $indice => $ubicacionVirtualExpo['bodega_id'],
                    'idSeccion' . $indice => $ubicacionVirtualExpo['seccion_id'],
                    'bodega' . $indice => $ubicacionVirtualExpo['nombre_bodega'],
                    'restaInventario' . $indice => 1,
                ]);
            }

            $porcentajeExpo = 0.0;
            foreach ($expoConfig['descuentos'] as $regla) {
                if ($ventaBruta >= $regla['venta_minima']) {
                    $porcentajeExpo = $regla['porcentaje_descuento'];
                }
            }

            $request->merge([
                'tipo_venta_id' => $tipoVentaExpoId,
                'porDescuento' => $porcentajeExpo,
            ]);

            $lineasDescuento = [];
            foreach ($arrayInputs as $indice) {
                $productoId = (int) $request->input('idProducto' . $indice, 0);
                $marcaId = (int) (DB::table('producto')->where('id', $productoId)->value('marca_id') ?? 0);
                $lineasDescuento[$indice] = [
                    'marca_id' => $marcaId,
                    'subtotal_bruto' => (float) $request->input('precio' . $indice, 0)
                        * (float) $request->input('cantidad' . $indice, 0)
                        * (float) $request->input('unidad' . $indice, 0),
                ];
            }
            $calculoServidor = app(\App\Services\Expo\CalculadorDescuentosExpo::class)->calcular(
                array_values($lineasDescuento),
                ['generales' => $expoConfig['descuentos'], 'marcas' => $expoConfig['descuentos_marca']]
            );
            $porcentajesMarca = $calculoServidor['porcentajes_marca'];
            foreach ($lineasDescuento as $indice => $linea) {
                $descuentoMarca = round($linea['subtotal_bruto'] * ($porcentajesMarca[$linea['marca_id']] ?? 0) / 100, 2);
                $descuentoGeneral = round(($linea['subtotal_bruto'] - $descuentoMarca) * $calculoServidor['porcentaje_general'] / 100, 2);
                $maximoPermitido = $descuentoMarca + $descuentoGeneral;
                $descuentoEnviado = (float) $request->input('acumuladoDescuento' . $indice, 0);
                if ($descuentoEnviado > $maximoPermitido + 0.01) {
                    return response()->json([
                        'icon' => 'error',
                        'title' => 'Descuento Expo no permitido',
                        'text' => 'La oferta contiene un descuento que el cliente no cumple. Verifique monto, marca y asistencia al evento.',
                    ], 422);
                }
            }
        }

        DB::beginTransaction();

            if ($expoConfig && !DB::table('expo')->where('id', $expoId)->where('estado', 'Activo')
                ->where('fecha_inicio', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
                })->lockForUpdate()->exists()) {
                DB::rollBack();
                return response()->json([
                    'icon' => 'error', 'title' => 'Expo no disponible',
                    'text' => 'La Expo dejó de estar vigente antes de guardar la oferta.',
                ], 422);
            }

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

            if ($expoConfig) {
                DB::table('expo_cotizacion')->insert([
                    'expo_id' => $expoId,
                    'cotizacion_id' => $cotizacion->id,
                    'created_by' => Auth::id(),
                    'estado' => 'PENDIENTE_FACTURACION',
                    'reglas_descuento_snapshot' => json_encode([
                        'version' => 2,
                        'generales' => $expoConfig['descuentos'],
                        'marcas' => $expoConfig['descuentos_marca'],
                    ]),
                    'created_at' => now(),
                ]);
            }

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

                $idSeccion = is_numeric($idSeccion) ? (int) $idSeccion : null;
                $idBodega = is_numeric($idBodega) ? (int) $idBodega : null;
                $restaInventario = ((float) $restaInventario > 0) ? 1 : 0;

                if ($restaInventario === 0) {
                    $ubicacionSinExistencia = $this->obtenerUbicacionSinExistencia();
                    $idBodega = (int) $ubicacionSinExistencia['bodega_id'];
                    $idSeccion = (int) $ubicacionSinExistencia['seccion_id'];
                    $nombreBodega = (string) $ubicacionSinExistencia['nombre_bodega'];
                }


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
                'tipo_precio'=>($ivsProductoAsignado > 0) ? '2' : '1', // '1' = Excento (producto sin ISV, isv = 0) | '2' = Gravado (producto con ISV, isv > 0)
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

        if ($expoConfig) {
            $lineasSnapshot = DB::table('cotizacion_has_producto as chp')
                ->join('producto as p', 'p.id', '=', 'chp.producto_id')
                ->join('marca as m', 'm.id', '=', 'p.marca_id')
                ->where('chp.cotizacion_id', $cotizacion->id)
                ->orderBy('chp.indice')
                ->get(['chp.id as linea_id', 'chp.producto_id', 'p.marca_id', 'm.nombre as marca'])
                ->map(fn ($linea) => [
                    'linea_id' => (int) $linea->linea_id,
                    'producto_id' => (int) $linea->producto_id,
                    'marca_id' => (int) $linea->marca_id,
                    'marca' => $linea->marca,
                ])->all();

            DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacion->id)->update([
                'reglas_descuento_snapshot' => json_encode([
                    'version' => 2,
                    'generales' => $expoConfig['descuentos'],
                    'marcas' => $expoConfig['descuentos_marca'],
                    'lineas' => $lineasSnapshot,
                ]),
            ]);
        }




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

        $codigoDb = (int) ($e->errorInfo[1] ?? 0);
        $detalleDb = (string) ($e->errorInfo[2] ?? '');
        $mensajeError = 'Ha ocurrido un error al guardar la cotización.';
        $icono = 'error';
        $titulo = 'Error!';

        if ($codigoDb === 1062 && stripos($detalleDb, 'cotizacion_has_producto.PRIMARY') !== false) {
            $mensajeError = 'No se ha logrado procesar la oferta debido a que se agrego dos veces el mismo producto.';
            $icono = 'info';
            $titulo = 'Información';
        }

        return response()->json([
            'icon'=>$icono,
            'text'=>$mensajeError,
            'title'=>$titulo,
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
            IF(COALESCE(NULLIF(B.tipo_precio,''), IF(B.isv_producto > 0,'2','1')) = '1', 'SI', 'NO') as excento,
            FORMAT(B.precio_unidad,2) as precio,
            FORMAT(B.cantidad,2) as cantidad,
            FORMAT(GREATEST((B.precio_unidad * B.cantidad) - B.sub_total,0),2) as descuento,
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

        $esExpo = DB::table('expo_cotizacion')->where('cotizacion_id', $idFactura)->exists();
        if ($esExpo) {
            $descuentoExpo = (float) DB::table('cotizacion_has_producto')
                ->where('cotizacion_id', $idFactura)
                ->selectRaw('COALESCE(SUM(GREATEST((precio_unidad * cantidad) - sub_total, 0)), 0) as descuento')
                ->value('descuento');
            $importes->monto_descuento = $descuentoExpo;
            $importesConCentavos->monto_descuento = number_format($descuentoExpo, 2);
        }


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }
         $tipoCot = 2;
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/cotizacion',compact('datos','productos','importes','importesConCentavos','flagCentavos','numeroLetras', 'tipoCot', 'esExpo'))->setPaper('letter');

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

    public function listarBodegas(Request $request, int $idProducto)
    {
        try {
            $idProducto = $idProducto > 0 ? $idProducto : (int) ($request->idProducto ?? 0);
            $search = addslashes((string) ($request->search ?? ''));

            $netStockExpr = "GREATEST(0,
                    sum(A.cantidad_disponible) - COALESCE((
                        SELECT SUM(php2.cantidad)
                        FROM prefactura_has_producto php2
                        INNER JOIN prefactura pf2 ON pf2.id = php2.prefactura_id
                        WHERE pf2.estado = 'activo'
                            AND TIMESTAMPADD(
                                DAY,
                                COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7),
                                COALESCE(pf2.created_at, CONCAT(COALESCE(pf2.fecha_emision, CURDATE()), ' 00:00:00'))
                            ) > NOW()
                            AND php2.producto_id = {$idProducto}
                            AND php2.seccion_id  = A.seccion_id
                            AND php2.resta_inventario = 1
                    ), 0)
                )";

            // Stock neto = cantidad_disponible - reservado en prefacturas activas
            $results = DB::SELECT("
        SELECT
            A.seccion_id as id,
            D.id as 'idBodega',
            CONCAT(D.nombre,'',REPLACE(B.descripcion,'Seccion','')) as 'bodegaSeccion',
            concat(D.nombre,' - ', REPLACE(B.descripcion,'Seccion',''),' - cantidad ',
                {$netStockExpr}
            ) as 'text'
        FROM recibido_bodega A
            INNER JOIN seccion B
            ON A.seccion_id = B.id
            INNER JOIN segmento C
            ON B.segmento_id = C.id
            INNER JOIN bodega D
            ON C.bodega_id = D.id
        WHERE  producto_id = " . $idProducto . "
        AND (D.nombre LIKE '%" . $search . "%' OR B.descripcion LIKE '%" . $search . "%')
        GROUP BY A.seccion_id
        HAVING {$netStockExpr} > 0
            ");

            $hayStockGlobal = count($results) > 0 || !empty(DB::SELECT(" 
                SELECT A.seccion_id
                FROM recibido_bodega A
                WHERE A.producto_id = " . $idProducto . "
                GROUP BY A.seccion_id
                HAVING {$netStockExpr} > 0
                LIMIT 1
            "));

            $results = array_values(array_filter($results, function ($item) {
                $texto = (string) (($item->text ?? '') ?: '');
                return stripos($texto, 'SIN EXISTENCIA - Cotizar sin reserva de inventario') === false;
            }));

            if (!$hayStockGlobal) {
                $ubicacionSinExistencia = $this->obtenerUbicacionSinExistencia();
                $results[] = (object) [
                    'id' => (int) $ubicacionSinExistencia['seccion_id'],
                    'idBodega' => (int) $ubicacionSinExistencia['bodega_id'],
                    'bodegaSeccion' => 'SIN EXISTENCIA',
                    'text' => 'SIN EXISTENCIA - Cotizar sin reserva de inventario',
                ];
            }

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

