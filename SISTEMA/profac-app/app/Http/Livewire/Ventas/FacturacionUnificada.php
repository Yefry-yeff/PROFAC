<?php

namespace App\Http\Livewire\Ventas;

use App\Support\ExpoConfig;
use Livewire\Component;
use App\Models\TipoFactura;
use App\Services\Expo\CalculadorDescuentosExpo;
use App\Services\Expo\SaldoLineasOferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FacturacionUnificada extends Component
{
    public $tipoFacturaId;
    public $tipoFactura;
    public $tiposFactura;
    public $fromFlujo = false;
    public $fromPrefactura = false;
    public $expoConfig = null;
    public $esOfertaExpo = false;
    public bool $duplicandoOferta = false;
    public $filtrarProductosExpo = false;
    public array $reglasExpoOferta = [];
    public array $atribucionesDescuentoExpo = [];

    public function capturaRapidaExpo(Request $request, string $identificador)
    {
        $expo = ExpoConfig::detalleActivaParaUsuario((int) $request->input('expo_id'), Auth::id());
        abort_unless($expo && count($expo['bodegas']) === 1, 422, 'La captura rápida requiere una Expo activa con una sola bodega.');

        $productoBase = DB::table('producto')
            ->where('estado_producto_id', 1)
            ->where(function ($query) use ($identificador) {
                if (ctype_digit($identificador)) {
                    $query->where('id', (int) $identificador);
                }
                $query->orWhere('codigo_barra', $identificador)
                    ->orWhere('codigo_estatal', $identificador);
            })
            ->when(ctype_digit($identificador), fn ($query) => $query->orderByRaw('id = ? DESC', [(int) $identificador]))
            ->first(['id']);
        abort_unless($productoBase, 404, 'No se encontró un producto activo con el código escaneado.');
        $productoId = (int) $productoBase->id;

        $categorias = DB::table('categoria_precios as cp')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->join('precios_producto_carga as ppc', function ($join) use ($productoId) {
                $join->on('ppc.categoria_precios_id', '=', 'cp.id')
                    ->where('ppc.producto_id', $productoId)
                    ->where('ppc.estado_id', 1);
            })
            ->whereIn('cp.id', $expo['escalas'])
            ->where('cp.estado_id', 1)
            ->orderByDesc('ppc.precio_a')
            ->get(['cp.id', DB::raw("CONCAT(cce.nombre_categoria, ' - ', cp.nombre) as nombre_categoria"), 'ppc.precio_a']);

        abort_if($categorias->isEmpty(), 422, 'El producto no tiene una escala de precio permitida en la Expo.');
        $categoriaPreferidaId = (int) $request->input('categoria_precio_id', 0);
        $categoriaSeleccionada = $categorias->first(fn ($categoria) => (int) $categoria->id === $categoriaPreferidaId)
            ?? $categorias->first();
        $categoriaId = (int) $categoriaSeleccionada->id;
        $bodegaId = (int) $expo['bodegas'][0];

        $ubicaciones = DB::table('recibido_bodega as rb')
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->where('rb.producto_id', $productoId)
            ->where('sg.bodega_id', $bodegaId)
            ->groupBy('rb.seccion_id', 'b.id', 'b.nombre', 's.descripcion')
            ->orderBy('rb.seccion_id')
            ->get([
                'rb.seccion_id',
                'b.id as bodega_id',
                'b.nombre as bodega_nombre',
                's.descripcion as seccion_nombre',
                DB::raw('SUM(rb.cantidad_disponible) as disponible'),
            ]);

        $reservas = DB::table('prefactura_has_producto as php')
            ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
            ->where('pf.estado', 'activo')
            ->whereRaw("TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()")
            ->where('php.producto_id', $productoId)
            ->where('php.resta_inventario', 1)
            ->whereIn('php.seccion_id', $ubicaciones->pluck('seccion_id'))
            ->groupBy('php.seccion_id')
            ->pluck(DB::raw('SUM(php.cantidad)'), 'php.seccion_id');

        $ubicacion = $ubicaciones->first(function ($item) use ($reservas) {
            $item->stock_neto = max(0, (float) $item->disponible - (float) ($reservas[$item->seccion_id] ?? 0));
            return $item->stock_neto > 0;
        });
        abort_unless($ubicacion, 422, 'El producto ya no tiene existencia disponible en la bodega de la Expo.');

        $producto = DB::table('producto as p')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->join('precios_producto_carga as ppc', function ($join) use ($categoriaId) {
                $join->on('ppc.producto_id', '=', 'p.id')
                    ->where('ppc.categoria_precios_id', $categoriaId)
                    ->where('ppc.estado_id', 1);
            })
            ->where('p.id', $productoId)
            ->first([
                'p.id', DB::raw("CONCAT(p.id, ' - ', p.nombre) as nombre"), 'p.marca_id',
                DB::raw("COALESCE(m.nombre, 'SIN MARCA') as marca"), 'p.isv',
                'p.ultimo_costo_compra', 'ppc.precio_base_venta as precio_base',
                'ppc.precio_a as precio1', 'ppc.precio_b as precio2', 'ppc.precio_c as precio3',
                'ppc.precio_d as precio4', 'ppc.id as precios_producto_carga_id',
            ]);
        abort_unless($producto, 422, 'No se encontró el precio activo del producto para la Expo.');

        $unidades = DB::table('unidad_medida_venta as uv')
            ->join('unidad_medida as um', 'um.id', '=', 'uv.unidad_medida_id')
            ->where('uv.estado_id', 1)
            ->where('uv.producto_id', $productoId)
            ->get([
                'uv.unidad_venta as id', DB::raw("CONCAT(um.nombre, '-', uv.unidad_venta) as nombre"),
                'uv.unidad_venta_defecto as valor_defecto', 'uv.id as idUnidadVenta',
            ]);

        return response()->json([
            'categorias' => $categorias,
            'categoria_id' => $categoriaId,
            'bodega' => [
                'id' => (int) $ubicacion->seccion_id,
                'idBodega' => (int) $ubicacion->bodega_id,
                'bodegaSeccion' => $ubicacion->bodega_nombre . ' ' . str_replace('Seccion', '', $ubicacion->seccion_nombre),
                'text' => $ubicacion->bodega_nombre . ' - ' . str_replace('Seccion', '', $ubicacion->seccion_nombre) . ' - cantidad ' . floor($ubicacion->stock_neto),
                'esSinExistencia' => 0,
            ],
            'producto' => $producto,
            'unidades' => $unidades,
        ]);
    }

    private function cargarAtribucionesDescuentoExpo(int $cotizacionId): void
    {
        $lineas = DB::table('cotizacion_has_producto as chp')
            ->join('producto as p', 'p.id', '=', 'chp.producto_id')
            ->where('chp.cotizacion_id', $cotizacionId)
            ->get(['chp.id', 'chp.cantidad', 'chp.precio_unidad', 'chp.monto_descProducto', 'p.marca_id']);

        $calculo = app(CalculadorDescuentosExpo::class)->calcular(
            $lineas->map(fn ($linea) => [
                'marca_id' => (int) $linea->marca_id,
                'subtotal_bruto' => round((float) $linea->precio_unidad * (float) $linea->cantidad, 2),
            ])->all(),
            $this->reglasExpoOferta
        );

        $this->atribucionesDescuentoExpo = $lineas->mapWithKeys(function ($linea) use ($calculo) {
            $descuentoFirmado = (float) $linea->monto_descProducto;
            $porcentajeMarca = (float) ($calculo['porcentajes_marca'][(int) $linea->marca_id] ?? 0);
            $descuentoMarca = round(
                (float) $linea->precio_unidad * (float) $linea->cantidad * $porcentajeMarca / 100,
                2
            );

            return [(int) $linea->id => [
                'porcentaje_marca' => (float) ($calculo['porcentajes_marca'][(int) $linea->marca_id] ?? 0),
                'porcentaje_general' => (float) ($calculo['porcentaje_general'] ?? 0),
                'proporcion_marca' => $descuentoFirmado > 0
                    ? min(max($descuentoMarca / $descuentoFirmado, 0), 1)
                    : 0,
            ]];
        })->all();
    }

    // ── Buscador de prefactura ───────────────────────────────────────────
    public $busquedaPrefactura     = '';
    public $prefacturasEncontradas = [];
    public $prefacturaVinculada    = null;
    public $prefacturaVinculadaId  = null;
    public $diasCreditoAprobados   = null;

// ── Buscador de flujo ─────────────────────────────────────────────────
    public $busquedaFlujo      = '';
    public $flujoEncontrados   = [];
    public $flujoVinculado     = null;
    public $flujoVinculadoId   = null;   // flujo.id del flujo vinculado
    public $pedidoId           = null;   // pedido.id (null para flujos sin pedido)

    // ── Datos pre-cargados del pedido (solo modo oferta) ─────────────────
    public $clientePedido         = null;   // ['id','nombre','rtn','cliente_id']
    public $productosSugeridos    = [];     // [['nombre_pedido','cantidad','similares':[...]]]
    public $productosParaCarrito  = [];     // Productos del duplicado para auto-agregar al carrito
    public $datosOfertaDuplicada  = null;   // ['tipo_pago_id','fecha_vencimiento','porc_descuento','nota']
    public $errorEscalaDuplicado  = null;   // Mensaje cuando un producto no tiene escala activa

    // ── Vendedor actual ──────────────────────────────────────────────────
    public $vendedorDefault    = [];

    // ── Pedido preview (para modal de detalle) ────────────────────────────
    public $pedidoDetalle      = null;
    public $documentosComerciales = [
        'numero_orden_compra'  => null,
        'archivo_orden_compra' => null,
        'numero_forma_f01'     => null,
        'archivo_forma_f01'    => null,
    ];

    private function cargarDocumentosComercialesDesdeFlujo(?int $flujoId): void
    {
        $this->documentosComerciales = [
            'numero_orden_compra'  => null,
            'archivo_orden_compra' => null,
            'numero_forma_f01'     => null,
            'archivo_forma_f01'    => null,
        ];

        if (empty($flujoId)) {
            return;
        }

        $doc = DB::table('flujo')
            ->where('id', (int) $flujoId)
            ->first([
                'numero_orden_compra',
                'archivo_orden_compra',
                'numero_forma_f01',
                'archivo_forma_f01',
            ]);

        if ($doc) {
            $this->documentosComerciales = [
                'numero_orden_compra'  => $doc->numero_orden_compra ?? null,
                'archivo_orden_compra' => $doc->archivo_orden_compra ?? null,
                'numero_forma_f01'     => $doc->numero_forma_f01 ?? null,
                'archivo_forma_f01'    => $doc->archivo_forma_f01 ?? null,
            ];
        }
    }

    private function obtenerDiasCreditoAprobados(?int $flujoId): ?int
    {
        if (!$flujoId) {
            return null;
        }

        $credito = DB::table('credito_revision')
            ->where('flujo_id', $flujoId)
            ->where('estado', 'aprobado')
            ->latest('id')
            ->first(['dias_credito_aprobados', 'fecha_aprobacion', 'fecha_vencimiento_credito']);

        if (!$credito) {
            return null;
        }

        if (!is_null($credito->dias_credito_aprobados)) {
            return max(0, (int) $credito->dias_credito_aprobados);
        }

        if ($credito->fecha_aprobacion && $credito->fecha_vencimiento_credito) {
            return max(0, (int) \Carbon\Carbon::parse($credito->fecha_aprobacion)
                ->diffInDays(\Carbon\Carbon::parse($credito->fecha_vencimiento_credito), false));
        }

        return null;
    }

    public function mount($codigo = null)
    {
        $from = request()->get('from');
        $this->fromFlujo = $from === 'flujo';
        $this->fromPrefactura = $from === 'prefactura';
        $this->duplicandoOferta = request()->boolean('duplicar');
        $this->tiposFactura = TipoFactura::activos()->where('codigo', '!=', 'cotizacion_clientes_a')->get();

        if ($codigo) {
            $this->tipoFactura = TipoFactura::where('codigo', $codigo)->first();
        }

        if (!$this->tipoFactura) {
            $this->tipoFactura = TipoFactura::where('codigo', 'estatal')->first();
        }

        $this->tipoFacturaId = $this->tipoFactura->id ?? null;

        $expoId = (int) request()->get('expo', 0);
        if ($expoId > 0) {
            abort_unless(($this->tipoFactura->codigo ?? '') === 'cotizacion_clientes_a', 404);
            $this->expoConfig = ExpoConfig::detalleActivaParaUsuario($expoId, Auth::id());
            abort_unless($this->expoConfig, 403, 'No tiene autorización para acceder a esta Expo.');
            $this->esOfertaExpo = true;
            $this->filtrarProductosExpo = true;

            $tipoVentaExpo = ExpoConfig::tipoVentaId();
            abort_unless($tipoVentaExpo, 500, 'No existe el tipo de venta Expo. Ejecute las migraciones.');
            $this->tipoFactura->tipo_venta_id = $tipoVentaExpo;
        }

        // Vendedor = usuario autenticado por defecto
        if (Auth::check()) {
            $this->vendedorDefault = [
                'id'   => Auth::id(),
                'name' => Auth::user()->name,
            ];
        }

        // Pre-seleccionar flujo si viene por query string (pedidoId)
        $pid = request()->get('pedidoId');
        if ($pid) {
            $flujoId = DB::table('flujo')
                ->where('identificacion', (string) $pid)
                ->where('tipo_flujo_id', 1)
                ->value('id');
            if ($flujoId) {
                $this->seleccionarFlujo((int) $flujoId);
            }
        }

        // Pre-seleccionar flujo si viene por flujoId directo (flujos sin pedido)
        $fid = request()->get('flujoId');
        if ($fid && !$pid && !$this->fromPrefactura) {
            $this->seleccionarFlujo((int) $fid);
        }

        // Pre-seleccionar prefactura si viene por query string
        $prefId = request()->get('prefactura_id');
        if ($prefId && $this->fromPrefactura) {
            $this->seleccionarPrefactura((int) $prefId);
        }

        // Cliente destino cuando se duplica para "Otro cliente"
        $clienteIdParam = request()->get('clienteId');

        // Si se pasa clienteId, obtener su categoria_precios_id actual para re-resolver precios
        $clienteCategoriaActualId = null;
        if ($clienteIdParam) {
            $clienteCategoriaActualId = DB::table('cliente')
                ->where('id', (int) $clienteIdParam)
                ->value('categoria_precios_id');
        }

        // Cargar productos del duplicado para auto-agregar al carrito (cotizacionId)
        $cotizId = request()->get('cotizacionId');
        if ($cotizId) {
            $expoCotizacion = DB::table('expo_cotizacion')
                ->where('cotizacion_id', (int) $cotizId)
                ->first(['expo_id', 'reglas_descuento_snapshot']);
            $expoCotizacionId = $expoCotizacion?->expo_id;
            $esExpo = !empty($expoCotizacionId);
            $this->esOfertaExpo = $esExpo;
            if ($esExpo) {
                $this->expoConfig = $this->duplicandoOferta
                    ? ExpoConfig::detalleActivaParaUsuario((int) $expoCotizacionId, Auth::id())
                    : ExpoConfig::detalleParaFacturacion((int) $expoCotizacionId, (int) $cotizId, Auth::id());
                abort_unless($this->expoConfig, 403, $this->duplicandoOferta
                    ? 'No tiene autorización para duplicar esta Oferta Expo.'
                    : 'No tiene autorización para facturar esta Oferta Expo.');
                $tipoVentaExpo = ExpoConfig::tipoVentaId();
                abort_unless($tipoVentaExpo, 500, 'No existe el tipo de venta Expo. Ejecute las migraciones.');
                $this->tipoFactura->tipo_venta_id = $tipoVentaExpo;
                $snapshot = json_decode((string) ($expoCotizacion->reglas_descuento_snapshot ?? ''), true) ?: [];
                $this->reglasExpoOferta = array_key_exists('generales', $snapshot)
                    ? $snapshot
                    : ['version' => 1, 'generales' => $snapshot, 'marcas' => [], 'lineas' => []];
                $this->cargarAtribucionesDescuentoExpo((int) $cotizId);
            }
            $prods = $esExpo && !$this->duplicandoOferta
                ? app(SaldoLineasOferta::class)->pendientes((int) $cotizId)
                    ->filter(fn($linea) => (float) $linea->cantidad_pendiente > 0)
                    ->map(function ($linea) {
                        $linea->cotizacion_has_producto_id = $linea->id;
                        $linea->cantidad_ofertada = $linea->cantidad;
                        $linea->cantidad = $linea->cantidad_pendiente;
                        return $linea;
                    })->values()->all()
                : DB::table('cotizacion_has_producto')
                    ->where('cotizacion_id', (int) $cotizId)
                    ->orderBy('indice')
                    ->get([
                    'cotizacion_has_producto.id as cotizacion_has_producto_id',
                    'cotizacion_has_producto.producto_id',
                    'nombre_producto',
                    'nombre_bodega',
                    'precio_unidad',
                    'precioSeleccionado',
                    'idPrecioSeleccionado',
                    'cantidad',
                    'isv_producto',
                    'unidad_medida_venta_id',
                    'Bodega_id',
                    'seccion_id',
                    'resta_inventario',
                    'precios_producto_carga_id',
                    'monto_descProducto',
                    ])->all();

            $productosResueltos = [];
            $productosSugeridos = [];
            $productosSinEscala = [];
            $marcasPorProducto = DB::table('producto as p')
                ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
                ->whereIn('p.id', collect($prods)->pluck('producto_id')->filter()->unique()->all())
                ->get(['p.id', 'p.marca_id', 'm.nombre as marca_nombre'])
                ->keyBy('id');
            $marcasSnapshot = collect($this->reglasExpoOferta['lineas'] ?? [])->keyBy('linea_id');

            foreach ($prods as $p) {
                $prod = (array) $p;
                $lineaExpoOrigenId = (int) ($prod['cotizacion_has_producto_id'] ?? 0);
                $marcaCongelada = $marcasSnapshot[$lineaExpoOrigenId] ?? [];
                if ($this->duplicandoOferta) {
                    $prod['linea_expo_origen_id'] = $lineaExpoOrigenId;
                    $prod['cotizacion_has_producto_id'] = null;
                }
                $marca = $marcasPorProducto[(int) ($prod['producto_id'] ?? 0)] ?? null;
                $prod['marca_id'] = (int) ($marcaCongelada['marca_id'] ?? $marca->marca_id ?? 0);
                $prod['marca_nombre'] = $marcaCongelada['marca'] ?? $marca->marca_nombre ?? 'SIN MARCA';

                $ppcActivo = null;
                $precioCargaId = isset($prod['precios_producto_carga_id']) ? (int) $prod['precios_producto_carga_id'] : 0;

                if ($precioCargaId > 0) {
                    // 1) Intentar con el mismo precios_producto_carga_id en estado activo.
                    $ppcActivo = DB::table('precios_producto_carga as ppc')
                        ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
                        ->where('ppc.id', $precioCargaId)
                        ->where('ppc.estado_id', 1)
                        ->select('ppc.id', 'ppc.precio_a', 'ppc.producto_id', 'ppc.categoria_precios_id', 'cp.nombre as categoria_nombre')
                        ->first();

                    // 2) Si no esta activo, buscar activo por misma categoria y producto.
                    if (!$ppcActivo) {
                        $ppcReferencia = DB::table('precios_producto_carga')
                            ->where('id', $precioCargaId)
                            ->select('producto_id', 'categoria_precios_id')
                            ->first();

                        if ($ppcReferencia) {
                            $ppcActivo = DB::table('precios_producto_carga as ppc')
                                ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
                                ->where('ppc.producto_id', (int) $ppcReferencia->producto_id)
                                ->where('ppc.categoria_precios_id', (int) $ppcReferencia->categoria_precios_id)
                                ->where('ppc.estado_id', 1)
                                ->orderByDesc('ppc.id')
                                ->select('ppc.id', 'ppc.precio_a', 'ppc.producto_id', 'ppc.categoria_precios_id', 'cp.nombre as categoria_nombre')
                                ->first();
                        }
                    }
                }

                if (!$ppcActivo) {
                    $productosSinEscala[] = $prod['nombre_producto'] ?? ('Producto ID ' . ($prod['producto_id'] ?? 'N/A'));
                    continue;
                }

                // Se usa siempre la categoría seleccionada en la oferta original.
                // El precio OPC refleja el precio_a vigente (estado_id=1) para esa misma categoría,
                // sin sobreescribirse con la categoría actual del cliente.

                $precioA = (float) ($ppcActivo->precio_a ?? 0);
                $precioUnidadOriginal = isset($prod['precio_unidad']) ? (float) $prod['precio_unidad'] : 0;
                $prod['precios_producto_carga_id'] = (int) $ppcActivo->id;
                $prod['idPrecioSeleccionado'] = 'p1';
                $prod['precioSeleccionado'] = $precioA;
                $prod['precio_unidad'] = $precioUnidadOriginal;
                $prod['categoria_precios_id'] = (int) $ppcActivo->categoria_precios_id;
                $prod['categoria_precios_nombre'] = $ppcActivo->categoria_nombre ?? 'Categoria sin nombre';

                $productosResueltos[] = $prod;
                $productosSugeridos[] = [
                    'nombre_pedido' => $p->nombre_producto,
                    'cantidad'      => $p->cantidad,
                    'similares'     => $this->buscarSimilares($p->nombre_producto),
                ];
            }

            if (!empty($productosSinEscala)) {
                $this->errorEscalaDuplicado = 'Uno de los productos ya no cuenta con una escala de precios asignada.';
                $this->productosParaCarrito = [];
                $this->productosSugeridos = [];
            } else {
                $this->errorEscalaDuplicado = null;
                $this->productosParaCarrito = $productosResueltos;
                $this->productosSugeridos = $productosSugeridos;
            }

            // Cargar datos de cabecera de la oferta original para pre-llenar el formulario
            $cotizOrig = DB::table('cotizacion as c')
                ->leftJoin('users as uv', 'uv.id', '=', 'c.vendedor')
                ->where('c.id', (int) $cotizId)
                ->select('c.tipo_pago_id', 'c.fecha_vencimiento', 'c.porc_descuento', 'c.nota',
                         'c.vendedor', 'uv.name as vendedor_nombre')
                ->first();
            if ($cotizOrig) {
                $this->datosOfertaDuplicada = [
                    'tipo_pago_id'             => $cotizOrig->tipo_pago_id,
                    'fecha_vencimiento'        => $cotizOrig->fecha_vencimiento
                        ? \Carbon\Carbon::parse($cotizOrig->fecha_vencimiento)->format('Y-m-d')
                        : null,
                    'porc_descuento'           => $esExpo ? 0.0 : (float) ($cotizOrig->porc_descuento ?? 0),
                    'nota'                     => $cotizOrig->nota ?? '',
                    'vendedor_id'              => $cotizOrig->vendedor,
                    'vendedor_nombre'          => $cotizOrig->vendedor_nombre ?? '',
                    'gestor_entrega_id'        => null,
                    'gestor_entrega_nombre'    => '',
                ];
                // Override vendedorDefault so the blade pre-selection uses the original Asesor Comercial
                if ($cotizOrig->vendedor) {
                    $this->vendedorDefault = [
                        'id'   => $cotizOrig->vendedor,
                        'name' => $cotizOrig->vendedor_nombre ?? '',
                    ];
                }
            }
        }

        // Pre-seleccionar cliente cuando se duplica para "Otro cliente" (clienteId sin pedidoId/flujoId)
        if ($clienteIdParam && !$pid && !$fid) {
            $cliente = DB::table('cliente')
                ->where('id', (int) $clienteIdParam)
                ->select('id', 'nombre', 'rtn')
                ->first();
            if ($cliente) {
                $this->clientePedido = [
                    'id'     => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'rtn'    => $cliente->rtn ?? '',
                ];
            }
        }
    }

    public function updatedBusquedaFlujo()
    {
        $term = trim($this->busquedaFlujo);
        if (strlen($term) < 2) {
            $this->flujoEncontrados = [];
            return;
        }

        $esNum = is_numeric($term);
        $num   = $esNum ? (int) $term : 0;
        $like  = '%' . $term . '%';

        $selectComun = [
            'f.id as flujo_id',
            'tt.nombre as flujo_estado',
            'f.created_at',
            'c.id as cliente_id',
            'c.nombre as cliente',
            'c.rtn',
            DB::raw('(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2) as total_ofertas'),
            DB::raw('(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2 AND hf.observaciones LIKE \'%ganadora%\') as has_ganadora'),
        ];

        // ── A: flujos CON pedido ─────────────────────────────────────────
        $qA = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotIn('p.estado', ['cancelado'])
            ->whereExists(function ($s) {
                $s->from('historico_flujo as hf')
                  ->whereColumn('hf.flujo_id', 'f.id')
                  ->where('hf.tipo_tramite_id', 1);
            })
            ->select(array_merge($selectComun, [
                'p.id as pedido_id',
                DB::raw("NULL as cotizacion_id"),
                DB::raw("'pedido' as tipo_origen"),
            ]))
            ->orderByDesc('f.id')
            ->limit(8);

        // ── B: flujos SIN pedido (cotizacion directa) ────────────────────
        $qB = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('cotizacion as co', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'co.id')
            ->join('cliente as c', 'c.id', '=', 'co.cliente_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotExists(function ($s) {
                $s->from('historico_flujo as hf')
                  ->whereColumn('hf.flujo_id', 'f.id')
                  ->where('hf.tipo_tramite_id', 1);
            })
            ->select(array_merge($selectComun, [
                DB::raw("NULL as pedido_id"),
                'co.id as cotizacion_id',
                DB::raw("'cotizacion' as tipo_origen"),
            ]))
            ->orderByDesc('f.id')
            ->limit(8);

        // ── Filtros ──────────────────────────────────────────────────────
        if ($esNum) {
            $qA->where(function ($s) use ($num) {
                $s->where('f.id', $num)
                  ->orWhere('p.id', $num)
                  ->orWhereExists(fn ($e) => $e->from('historico_flujo as hf')
                      ->whereColumn('hf.flujo_id', 'f.id')
                      ->where('hf.tramite_id', $num)
                      ->where('hf.tipo_tramite_id', 2));
            });
            $qB->where(function ($s) use ($num) {
                $s->where('f.id', $num)
                  ->orWhere('co.id', $num)
                  ->orWhereExists(fn ($e) => $e->from('historico_flujo as hf')
                      ->whereColumn('hf.flujo_id', 'f.id')
                      ->where('hf.tramite_id', $num)
                      ->where('hf.tipo_tramite_id', 2));
            });
        } else {
            $qA->where(fn ($s) => $s->where('c.nombre', 'LIKE', $like)->orWhere('c.rtn', 'LIKE', $like));
            $qB->where(fn ($s) => $s->where('c.nombre', 'LIKE', $like)->orWhere('c.rtn', 'LIKE', $like));
        }

        $this->aplicarAlcanceUsuarioFlujo($qA);
        $this->aplicarAlcanceUsuarioFlujo($qB);

        $this->flujoEncontrados = array_slice(
            array_merge($qA->get()->toArray(), $qB->get()->toArray()),
            0, 8
        );
    }

    public function updatedBusquedaPrefactura()
    {
        $term = trim($this->busquedaPrefactura);
        if (strlen($term) < 2) {
            $this->prefacturasEncontradas = [];
            return;
        }

        $esNum = is_numeric($term);
        $like  = '%' . $term . '%';

        $q = DB::table('prefactura as p')
            ->where('p.estado', 'activo')
            ->select(
                'p.id',
                'p.flujo_id',
                'p.cliente_id',
                'p.nombre_cliente',
                'p.RTN',
                'p.total',
                'p.fecha_emision',
                'p.fecha_vencimiento'
            )
            ->orderByDesc('p.id')
            ->limit(8);

        if ($esNum) {
            $q->where(function ($s) use ($term) {
                $num = (int) $term;
                $s->where('p.id', $num)->orWhere('p.flujo_id', $num);
            });
        } else {
            $q->where(function ($s) use ($like) {
                $s->where('p.nombre_cliente', 'LIKE', $like)
                  ->orWhere('p.RTN', 'LIKE', $like);
            });
        }

        $this->prefacturasEncontradas = $q->get()->map(fn($r) => (array) $r)->toArray();
    }

    public function seleccionarFlujo(int $flujoId)
    {
        abort_unless($this->usuarioPuedeAccederFlujo($flujoId), 403, 'No tiene acceso a este flujo de venta.');
        $hasPedido = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 1)
            ->exists();

        if ($hasPedido) {
            $f = DB::table('flujo as f')
                ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
                ->join('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
                ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
                ->where('f.id', $flujoId)
                ->select('f.id as flujo_id', 'tt.nombre as flujo_estado',
                         'p.id as pedido_id', DB::raw('NULL as cotizacion_id'),
                         'p.created_at', 'c.id as cliente_id', 'c.nombre as cliente', 'c.rtn',
                         DB::raw("'pedido' as tipo_origen"))
                ->first();

            if (!$f) return;

            $this->pedidoId = $f->pedido_id;

            $detalles = DB::table('pedido_detalle')
                ->where('pedido_id', $f->pedido_id)
                ->select('id', 'nombre_producto', 'cantidad')
                ->get();

            $this->productosSugeridos = [];
            foreach ($detalles as $det) {
                $this->productosSugeridos[] = [
                    'nombre_pedido' => $det->nombre_producto,
                    'cantidad'      => $det->cantidad,
                    'similares'     => $this->buscarSimilares($det->nombre_producto),
                ];
            }
        } else {
            $f = DB::table('flujo as f')
                ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
                ->join('cotizacion as co', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'co.id')
                ->join('cliente as c', 'c.id', '=', 'co.cliente_id')
                ->where('f.id', $flujoId)
                ->select('f.id as flujo_id', 'tt.nombre as flujo_estado',
                         DB::raw('NULL as pedido_id'), 'co.id as cotizacion_id',
                         'co.created_at', 'c.id as cliente_id', 'c.nombre as cliente', 'c.rtn',
                         DB::raw("'cotizacion' as tipo_origen"))
                ->first();

            if (!$f) return;

            $this->pedidoId           = null;
            $this->productosSugeridos = [];
        }

        $this->flujoVinculadoId = $flujoId;
        $this->flujoVinculado   = (array) $f;
        $this->cargarDocumentosComercialesDesdeFlujo((int) $flujoId);
        $this->clientePedido    = [
            'id'     => $f->cliente_id,
            'nombre' => $f->cliente,
            'rtn'    => $f->rtn,
        ];

        $this->busquedaFlujo    = '';
        $this->flujoEncontrados = [];

        $this->dispatchBrowserEvent('pedido-seleccionado', [
            'clienteId'      => $this->clientePedido['id'],
            'clienteNombre'  => $this->clientePedido['nombre'],
            'vendedorId'     => $this->vendedorDefault['id'] ?? null,
            'vendedorNombre' => $this->vendedorDefault['name'] ?? null,
            'flujoId'        => $this->flujoVinculadoId,
            'numeroOrdenCompra' => $this->documentosComerciales['numero_orden_compra'] ?? null,
            'archivoOrdenCompra' => $this->documentosComerciales['archivo_orden_compra'] ?? null,
            'numeroFormaF01' => $this->documentosComerciales['numero_forma_f01'] ?? null,
            'archivoFormaF01' => $this->documentosComerciales['archivo_forma_f01'] ?? null,
        ]);
    }

    public function seleccionarFlujoDesdePedido(int $pedidoId)
    {
        $flujoId = DB::table('flujo')
            ->where('identificacion', (string) $pedidoId)
            ->where('tipo_flujo_id', 1)
            ->value('id');

        if ($flujoId) {
            $this->seleccionarFlujo((int) $flujoId);
        }
    }

    public function seleccionarPrefactura(int $prefacturaId)
    {
        $pref = DB::table('prefactura')
            ->where('id', $prefacturaId)
            ->where('estado', 'activo')
            ->first();

        if (!$pref) return;

        $this->prefacturaVinculadaId = (int) $pref->id;
        $this->prefacturaVinculada   = (array) $pref;

        // Ligado de flujo (solo prefacturas)
        $this->flujoVinculadoId = $pref->flujo_id ? (int) $pref->flujo_id : null;
        $this->diasCreditoAprobados = $this->obtenerDiasCreditoAprobados($this->flujoVinculadoId);
        $this->cargarDocumentosComercialesDesdeFlujo($this->flujoVinculadoId);
        $this->flujoVinculado   = $pref->flujo_id ? [
            'flujo_id'  => (int) $pref->flujo_id,
            'cliente'   => $pref->nombre_cliente,
            'pedido_id' => null,
        ] : null;

        // Datos cliente desde prefactura
        $this->clientePedido = [
            'id'     => $pref->cliente_id,
            'nombre' => $pref->nombre_cliente,
            'rtn'    => $pref->RTN,
        ];

        $expoCotizacion = $pref->cotizacion_id
            ? DB::table('expo_cotizacion')->where('cotizacion_id', (int) $pref->cotizacion_id)
                ->first(['expo_id', 'reglas_descuento_snapshot'])
            : null;
        $this->esOfertaExpo = !empty($expoCotizacion);
        if ($expoCotizacion) {
            $this->expoConfig = ExpoConfig::detalleParaFacturacion(
                (int) $expoCotizacion->expo_id,
                (int) $pref->cotizacion_id,
                Auth::id()
            );
            abort_unless($this->expoConfig, 403, 'No tiene autorización para facturar esta Oferta Expo.');
            $snapshot = json_decode((string) ($expoCotizacion->reglas_descuento_snapshot ?? ''), true) ?: [];
            $this->reglasExpoOferta = array_key_exists('generales', $snapshot)
                ? $snapshot
                : ['version' => 1, 'generales' => $snapshot, 'marcas' => [], 'lineas' => []];
            $this->cargarAtribucionesDescuentoExpo((int) $pref->cotizacion_id);
        }
        $marcasSnapshot = collect($this->reglasExpoOferta['lineas'] ?? [])->keyBy('linea_id');

        // Carrito exacto desde prefactura (sin recalcular valores)
        $this->productosParaCarrito = DB::table('prefactura_has_producto as php')
            ->leftJoin('cotizacion_has_producto as chp', 'chp.id', '=', 'php.cotizacion_has_producto_id')
            ->where('php.prefactura_id', $prefacturaId)
            ->orderBy('php.indice')
            ->get([
                'php.cotizacion_has_producto_id',
                'php.producto_id',
                'php.nombre_producto',
                'php.nombre_bodega',
                'php.precio_unidad',
                'php.cantidad',
                'php.sub_total',
                'php.isv',
                'php.total',
                'php.isv_producto',
                'php.unidad_medida_venta_id',
                'php.Bodega_id',
                'php.seccion_id',
                'php.resta_inventario',
                'php.precios_producto_carga_id',
                'chp.cantidad as cantidad_ofertada',
                'chp.monto_descProducto',
            ])
            ->map(function ($r) use ($marcasSnapshot) {
                $producto = (array) $r;
                $marca = $marcasSnapshot[(int) ($r->cotizacion_has_producto_id ?? 0)] ?? [];
                $producto['marca_id'] = (int) ($marca['marca_id'] ?? 0);
                $producto['marca_nombre'] = $marca['marca'] ?? 'SIN MARCA';
                return $producto;
            })
            ->toArray();

        $this->busquedaPrefactura     = '';
        $this->prefacturasEncontradas = [];

        // Usar el vendedor guardado en la prefactura (viene de la oferta ganadora)
        $vendedorId     = $this->vendedorDefault['id'] ?? null;
        $vendedorNombre = $this->vendedorDefault['name'] ?? null;
        if (!empty($pref->vendedor)) {
            $userPref = DB::table('users')->where('id', $pref->vendedor)->first(['id', 'name']);
            if ($userPref) {
                $vendedorId     = $userPref->id;
                $vendedorNombre = $userPref->name;
                $this->vendedorDefault = ['id' => $userPref->id, 'name' => $userPref->name];
            }
        } elseif (!empty($pref->cotizacion_id)) {
            // Fallback: leer vendedor desde la cotización vinculada
            $cotVend = DB::table('cotizacion as c')
                ->join('users as u', 'u.id', '=', 'c.vendedor')
                ->where('c.id', (int) $pref->cotizacion_id)
                ->select('u.id', 'u.name')
                ->first();
            if ($cotVend) {
                $vendedorId     = $cotVend->id;
                $vendedorNombre = $cotVend->name;
                $this->vendedorDefault = ['id' => $cotVend->id, 'name' => $cotVend->name];
            }
        }

        $this->dispatchBrowserEvent('pedido-seleccionado', [
            'clienteId'      => $this->clientePedido['id'],
            'clienteNombre'  => $this->clientePedido['nombre'],
            'vendedorId'     => $vendedorId,
            'vendedorNombre' => $vendedorNombre,
            'flujoId'        => $this->flujoVinculadoId,
            'diasCreditoAprobados' => $this->diasCreditoAprobados,
            'numeroOrdenCompra' => $this->documentosComerciales['numero_orden_compra'] ?? null,
            'archivoOrdenCompra' => $this->documentosComerciales['archivo_orden_compra'] ?? null,
            'numeroFormaF01' => $this->documentosComerciales['numero_forma_f01'] ?? null,
            'archivoFormaF01' => $this->documentosComerciales['archivo_forma_f01'] ?? null,
        ]);
    }
    /**
     * Carga el detalle de un pedido para mostrar en modal de preview.
     */
    public function verDetallePedido(int $id)
    {
        $p = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->where('p.id', $id)
            ->select(
                'p.id', 'p.estado', 'p.created_at', 'p.observaciones',
                'c.nombre as cliente', 'c.rtn',
                'u.name as vendedor_registra'
            )
            ->first();

        if (!$p) return;

        $detalles = DB::table('pedido_detalle')
            ->where('pedido_id', $id)
            ->select('nombre_producto', 'cantidad')
            ->get()
            ->toArray();

        $this->pedidoDetalle = [
            'pedido'    => (array) $p,
            'productos' => $detalles,
        ];

        $this->dispatchBrowserEvent('mostrar-modal-detalle-pedido');
    }

    /**
     * Busca hasta $limit productos reales cuyo nombre se asemeje al nombre del pedido.
     */
    private function buscarSimilares(string $nombre, int $limit = 3): array
    {
        // Tokenizar: palabras de 3+ letras
        $palabras = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9\s]/u', ' ', $nombre)), fn($w) => strlen($w) >= 3);

        if (empty($palabras)) {
            return DB::table('producto')
                ->where('nombre', 'LIKE', '%' . $nombre . '%')
                ->whereRaw('id IN (SELECT producto_id FROM inventario WHERE cantidad > 0)')
                ->where('estado_producto_id', 1)
                ->select('id', 'nombre', 'precio_base as precio', 'isv')
                ->limit($limit)->get()->toArray();
        }

        // Construir CASE para scoring por coincidencia de palabras
        $q = DB::table('producto');
        $cases  = [];
        $params = [];
        foreach ($palabras as $w) {
            $cases[]  = 'IF(nombre LIKE ?, 1, 0)';
            $params[] = '%' . $w . '%';
        }
        $score = 'IF(nombre LIKE ?, 10, 0) + ' . implode(' + ', $cases);
        $params = array_merge(['%' . $nombre . '%'], $params);

        $results = $q->where('estado_producto_id', 1)
            ->selectRaw('id, nombre, precio_base as precio, isv, (' . $score . ') as score', $params)
            ->having('score', '>', 0)
            ->orderByDesc('score')
            ->limit($limit)
            ->get()->toArray();

        // Fallback si no hay resultados con scoring
        if (empty($results)) {
            $results = DB::table('producto')
                ->where('nombre', 'LIKE', '%' . array_values($palabras)[0] . '%')
                ->where('estado_producto_id', 1)
                ->select('id', 'nombre', 'precio_base as precio', 'isv')
                ->limit($limit)->get()->toArray();
        }

        return $results;
    }

    public function desvincularFlujo()
    {
        $this->pedidoId            = null;
        $this->flujoVinculadoId    = null;
        $this->flujoVinculado      = null;
        $this->busquedaFlujo       = '';
        $this->flujoEncontrados    = [];
        $this->clientePedido       = null;
        $this->productosSugeridos  = [];
        $this->pedidoDetalle       = null;

        $this->dispatchBrowserEvent('pedido-desvinculado', [
            'vendedorId'     => $this->vendedorDefault['id'] ?? null,
            'vendedorNombre' => $this->vendedorDefault['name'] ?? null,
        ]);
    }

    public function desvincularPrefactura()
    {
        $this->prefacturaVinculadaId  = null;
        $this->prefacturaVinculada    = null;
        $this->diasCreditoAprobados   = null;
        $this->busquedaPrefactura     = '';
        $this->prefacturasEncontradas = [];
        $this->flujoVinculadoId       = null;
        $this->flujoVinculado         = null;
        $this->clientePedido          = null;
        $this->productosParaCarrito   = [];

        $this->dispatchBrowserEvent('pedido-desvinculado', [
            'vendedorId'     => $this->vendedorDefault['id'] ?? null,
            'vendedorNombre' => $this->vendedorDefault['name'] ?? null,
        ]);
    }

    private function aplicarAlcanceUsuarioFlujo($query): void
    {
        $usuarioId = (int) Auth::id();

        $query->where(function ($alcance) use ($usuarioId) {
            $alcance->whereExists(function ($pedido) use ($usuarioId) {
                $pedido->select(DB::raw(1))
                    ->from('historico_flujo as hf_perm_p')
                    ->join('pedido as p_perm', 'p_perm.id', '=', 'hf_perm_p.tramite_id')
                    ->join('cliente as c_perm_p', 'c_perm_p.id', '=', 'p_perm.cliente_id')
                    ->whereColumn('hf_perm_p.flujo_id', 'f.id')
                    ->where('hf_perm_p.tipo_tramite_id', 1)
                    ->where(function ($actor) use ($usuarioId) {
                        $actor->where('p_perm.users_id', $usuarioId)
                            ->orWhere('c_perm_p.vendedor', $usuarioId)
                            ->orWhereExists(function ($asignado) use ($usuarioId) {
                                $asignado->select(DB::raw(1))
                                    ->from('cliente_usuario as cu_perm_p')
                                    ->whereColumn('cu_perm_p.cliente_id', 'c_perm_p.id')
                                    ->where('cu_perm_p.usuario_id', $usuarioId)
                                    ->whereIn('cu_perm_p.rol_id', [2, 3]);
                            });
                    });
            })->orWhereExists(function ($oferta) use ($usuarioId) {
                $oferta->select(DB::raw(1))
                    ->from('historico_flujo as hf_perm_o')
                    ->join('cotizacion as co_perm', 'co_perm.id', '=', 'hf_perm_o.tramite_id')
                    ->join('cliente as c_perm_o', 'c_perm_o.id', '=', 'co_perm.cliente_id')
                    ->whereColumn('hf_perm_o.flujo_id', 'f.id')
                    ->where('hf_perm_o.tipo_tramite_id', 2)
                    ->where(function ($actor) use ($usuarioId) {
                        $actor->where('co_perm.users_id', $usuarioId)
                            ->orWhere('co_perm.vendedor', $usuarioId)
                            ->orWhere('c_perm_o.vendedor', $usuarioId)
                            ->orWhereExists(function ($asignado) use ($usuarioId) {
                                $asignado->select(DB::raw(1))
                                    ->from('cliente_usuario as cu_perm_o')
                                    ->whereColumn('cu_perm_o.cliente_id', 'c_perm_o.id')
                                    ->where('cu_perm_o.usuario_id', $usuarioId)
                                    ->whereIn('cu_perm_o.rol_id', [2, 3]);
                            });
                    });
            })->orWhereExists(function ($factura) use ($usuarioId) {
                $factura->select(DB::raw(1))
                    ->from('historico_flujo as hf_perm_f')
                    ->join('factura as fa_perm', 'fa_perm.id', '=', 'hf_perm_f.tramite_id')
                    ->whereColumn('hf_perm_f.flujo_id', 'f.id')
                    ->where('hf_perm_f.tipo_tramite_id', 3)
                    ->where(function ($actor) use ($usuarioId) {
                        $actor->where('fa_perm.vendedor', $usuarioId)
                            ->orWhere('fa_perm.users_id', $usuarioId)
                            ->orWhere('fa_perm.gestor_entrega', $usuarioId);
                    });
            });
        });
    }

    private function usuarioPuedeAccederFlujo(int $flujoId): bool
    {
        if (in_array((int) (Auth::user()->rol_id ?? 0), [1, 3, 5, 16], true)) {
            return DB::table('flujo')->where('id', $flujoId)->exists();
        }

        $query = DB::table('flujo as f')->where('f.id', $flujoId);
        $query->where(function ($acceso) {
            $acceso->where('f.created_by', Auth::id())
                ->orWhere('f.updated_by', Auth::id())
                ->orWhere(function ($alcance) {
                    $this->aplicarAlcanceUsuarioFlujo($alcance);
                });
        });

        return $query->exists();
    }

    public function render()
    {
        return view('livewire.ventas.facturacion-unificada', [
            'tiposFactura' => $this->tiposFactura,
            'config'       => $this->tipoFactura,
            'expoConfig'   => $this->expoConfig,
        ]);
    }
}
