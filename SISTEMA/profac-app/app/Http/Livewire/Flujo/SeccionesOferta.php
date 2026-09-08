<?php

namespace App\Http\Livewire\Flujo;

use App\Services\Expo\SeccionadorOfertaExpo;
use App\Support\ExpoStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SeccionesOferta extends Component
{
    public array $bandeja = [];
    public ?int $flujoId = null;
    public ?int $cotizacionOrigenId = null;
    public array $oferta = [];
    public array $productos = [];
    public array $cantidades = [];
    public array $seleccionados = [];
    public bool $seleccionarTodos = false;
    public array $secciones = [];
    public array $condicionPago = [];
    public int $tipoPagoId = 1;
    public string $fechaEmision = '';
    public string $fechaPago = '';
    public ?int $ultimaSeccionId = null;
    public string $ultimaSeccionNombre = '';
    public ?int $editarSeccionId = null;
    public ?string $editarEstadoSeccion = null;
    public bool $puedeCrear = false;
    public bool $finalizaSeccionado = false;
    public string $comentarioCredito = '';
    public string $comentarioInventarioGeneral = '';
    public array $comentariosInventarioProductos = [];
    public string $busqueda = '';
    public string $busquedaProducto = '';
    public string $mensajeExito = '';
    public string $mensajeError = '';

    public function mount(): void
    {
        $this->cargar();
        $flujoId = request()->integer('flujo_id');
        if ($flujoId > 0) {
            $this->seleccionarFlujo($flujoId);
            $seccionId = request()->integer('seccion_id');
            if ($seccionId > 0) {
                $this->cargarSeccionParaEditar($seccionId);
            }
        }
    }

    public function updatedBusqueda(): void
    {
        $this->cargar();
    }

    public function cargar(): void
    {
        $term = trim($this->busqueda);
        $this->bandeja = DB::table('flujo as f')
            ->join('historico_flujo as hf', function ($join) {
                $join->on('hf.flujo_id', '=', 'f.id')
                    ->where('hf.tipo_tramite_id', '=', 11)
                    ->where('hf.estado_id', '=', 5);
            })
            ->join('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->join('expo_cotizacion as ec', 'ec.cotizacion_id', '=', 'c.id')
            ->where('f.tipo_tramite_id', 11)
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('c.nombre_cliente', 'like', '%' . $term . '%')
                        ->orWhere('c.id', 'like', '%' . $term . '%')
                        ->orWhere('f.id', 'like', '%' . $term . '%');
                });
            })
            ->whereRaw('hf.id = (SELECT MAX(hf2.id) FROM historico_flujo hf2 WHERE hf2.flujo_id = f.id AND hf2.tipo_tramite_id = 11 AND hf2.estado_id = 5)')
            ->orderByDesc('hf.id')
            ->select('f.id as flujo_id', 'c.id as cotizacion_id', 'c.nombre_cliente', 'c.total', 'c.fecha_emision')
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    public function seleccionarFlujo(int $flujoId): void
    {
        $registro = DB::table('flujo as f')
            ->join('historico_flujo as hf', 'hf.flujo_id', '=', 'f.id')
            ->join('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->join('expo_cotizacion as ec', 'ec.cotizacion_id', '=', 'c.id')
            ->leftJoin('tipo_pago_venta as tp', 'tp.id', '=', 'c.tipo_pago_id')
            ->where('f.id', $flujoId)
            ->where('hf.tipo_tramite_id', 11)
            ->orderByDesc('hf.id')
            ->select('f.id as flujo_id', 'f.tipo_tramite_id as tramite_actual', 'c.*', 'tp.descripcion as tipo_pago')
            ->first();

        if (!$registro) {
            $this->mostrarError('El flujo no está disponible para seccionar una oferta Expo.');
            return;
        }

        $this->flujoId = (int) $registro->flujo_id;
        $this->cotizacionOrigenId = (int) $registro->id;
        $this->oferta = (array) $registro;
        $this->puedeCrear = true;
        $emision = \Carbon\Carbon::parse($registro->fecha_emision);
        $vencimiento = \Carbon\Carbon::parse($registro->fecha_vencimiento ?: $registro->fecha_emision);
        $esCredito = (int) $registro->tipo_pago_id === 2
            || strtolower((string) $registro->tipo_pago) === 'credito';
        $this->condicionPago = [
            'tipo' => $esCredito ? 'Crédito' : 'Contado',
            'fecha_emision' => $emision->format('d/m/Y'),
            'fecha_vencimiento' => ($esCredito ? $vencimiento : $emision)->format('d/m/Y'),
            'dias' => $esCredito ? max(0, $emision->diffInDays($vencimiento, false)) : 0,
        ];
        $this->tipoPagoId = $esCredito ? 2 : 1;
        $this->fechaEmision = $emision->toDateString();
        $this->fechaPago = ($esCredito ? $vencimiento : $emision)->toDateString();
        $this->ultimaSeccionId = null;
        $this->ultimaSeccionNombre = '';
        $this->editarSeccionId = null;
        $this->editarEstadoSeccion = null;
        $this->finalizaSeccionado = false;
        $this->comentarioCredito = '';
        $this->comentarioInventarioGeneral = '';
        $this->comentariosInventarioProductos = [];
        $this->busquedaProducto = '';
        $this->mensajeError = '';
        $this->cargarDetalle();
    }

    public function cerrarDetalle(): void
    {
        $this->flujoId = null;
        $this->cotizacionOrigenId = null;
        $this->oferta = [];
        $this->productos = [];
        $this->cantidades = [];
        $this->seleccionados = [];
        $this->seleccionarTodos = false;
        $this->secciones = [];
        $this->condicionPago = [];
        $this->tipoPagoId = 1;
        $this->fechaEmision = '';
        $this->fechaPago = '';
        $this->ultimaSeccionId = null;
        $this->ultimaSeccionNombre = '';
        $this->puedeCrear = false;
        $this->editarEstadoSeccion = null;
        $this->finalizaSeccionado = false;
        $this->comentarioCredito = '';
        $this->comentarioInventarioGeneral = '';
        $this->comentariosInventarioProductos = [];
        $this->busquedaProducto = '';
        $this->mensajeError = '';
    }

    public function quitarProducto(int $lineaId): void
    {
        $this->seleccionados[$lineaId] = false;
        $this->cantidades[$lineaId] = 0;
        $this->sincronizarSeleccionTodos();
    }

    public function updatedSeleccionarTodos(bool $seleccionar): void
    {
        foreach ($this->productos as $producto) {
            $lineaId = (int) $producto['id'];
            $saldo = (int) $producto['cantidad_pendiente'];
            $this->seleccionados[$lineaId] = $seleccionar && $saldo > 0;
            $this->cantidades[$lineaId] = $seleccionar && $saldo > 0 ? $saldo : 0;
        }
    }

    public function updatedSeleccionados($seleccionado, $lineaId): void
    {
        $lineaId = (int) $lineaId;
        if ($seleccionado) {
            $producto = collect($this->productos)->firstWhere('id', $lineaId);
            $this->cantidades[$lineaId] = (int) ($producto['cantidad_pendiente'] ?? 0);
        } else {
            $this->cantidades[$lineaId] = 0;
        }
        $this->sincronizarSeleccionTodos();
    }

    public function updatedCantidades($cantidad, $lineaId): void
    {
        $lineaId = (int) $lineaId;
        $this->seleccionados[$lineaId] = (int) $cantidad > 0;
        $this->sincronizarSeleccionTodos();
    }

    private function sincronizarSeleccionTodos(): void
    {
        $seleccionables = collect($this->productos)
            ->filter(fn ($producto) => (float) $producto['cantidad_pendiente'] > 0)
            ->pluck('id');
        $this->seleccionarTodos = $seleccionables->isNotEmpty()
            && $seleccionables->every(fn ($id) => !empty($this->seleccionados[(int) $id]));
    }

    public function updatedTipoPagoId($value): void
    {
        $this->tipoPagoId = (int) $value;
        if ($this->tipoPagoId === 1) {
            $this->fechaPago = $this->fechaEmision;
        } elseif ($this->fechaPago === '') {
            $this->establecerFechaPagoPorDefecto();
        }
    }

    public function updatedFechaEmision(string $value): void
    {
        if ($this->tipoPagoId === 1) {
            $this->fechaPago = $value;
        }
    }

    public function nuevaSeccion(): void
    {
        $this->dispatchBrowserEvent('cerrar-modal-seccion-guardada');
        $this->editarSeccionId = null;
        $this->editarEstadoSeccion = null;
        $this->mensajeExito = '';
        $this->mensajeError = '';
        $this->comentarioCredito = '';
        $this->comentarioInventarioGeneral = '';
        $this->comentariosInventarioProductos = [];
        $this->cargarDetalle();
    }

    public function guardar()
    {
        if (!$this->flujoId || !$this->cotizacionOrigenId) {
            return;
        }

        if (!$this->puedeCrear && !$this->editarSeccionId) {
            $this->mostrarError('La oferta ya no tiene saldo disponible para crear otra sección.');
            return;
        }

        if ($this->fechaPago === '') {
            $this->establecerFechaPagoPorDefecto();
        }

        try {
            $this->validate([
                'tipoPagoId' => 'required|integer|in:1,2',
                'fechaEmision' => 'required|date',
                'fechaPago' => 'required|date|after_or_equal:fechaEmision',
                'comentarioCredito' => 'nullable|string|max:1000',
                'cantidades.*' => 'nullable|integer|min:0',
            ], [
                'tipoPagoId.required' => 'Seleccione una condición de pago.',
                'fechaEmision.required' => 'Seleccione la fecha de emisión.',
                'fechaEmision.date' => 'La fecha de emisión no es válida.',
                'fechaPago.required' => 'Seleccione la fecha de pago o vencimiento.',
                'fechaPago.date' => 'La fecha de pago o vencimiento no es válida.',
                'fechaPago.after_or_equal' => 'La fecha de pago no puede ser anterior a la fecha de emisión.',
                'cantidades.*.integer' => 'Las cantidades deben ser números enteros.',
                'cantidades.*.min' => 'Las cantidades no pueden ser negativas.',
            ]);
            $cantidadesSeleccionadas = collect($this->cantidades)
                ->filter(fn ($cantidad) => (int) $cantidad > 0)
                ->all();

            $cotizacionHijaId = $this->editarSeccionId
                ? app(SeccionadorOfertaExpo::class)->actualizarRechazadaCredito(
                    $this->editarSeccionId,
                    $cantidadesSeleccionadas,
                    $this->tipoPagoId,
                    $this->fechaEmision,
                    $this->fechaPago,
                    $this->finalizaSeccionado,
                    (int) Auth::id(),
                    $this->comentarioCredito
                )
                : app(SeccionadorOfertaExpo::class)->crear(
                    $this->flujoId,
                    $this->cotizacionOrigenId,
                    $cantidadesSeleccionadas,
                    $this->tipoPagoId,
                    $this->fechaEmision,
                    $this->fechaPago,
                    $this->finalizaSeccionado,
                    (int) Auth::id(),
                    $this->comentarioCredito
                );

            $seccion = DB::table('expo_oferta_seccion')
                ->where('cotizacion_id', $cotizacionHijaId)
                ->first(['nombre']);
            $this->ultimaSeccionId = $cotizacionHijaId;
            $this->ultimaSeccionNombre = (string) ($seccion->nombre ?? ('Oferta #' . $cotizacionHijaId));
            $this->editarSeccionId = null;
            $this->editarEstadoSeccion = null;
            $this->comentarioInventarioGeneral = '';
            $this->comentariosInventarioProductos = [];
            $this->mensajeExito = '';
            $this->mensajeError = '';
            $this->cargarDetalle();
            $this->dispatchBrowserEvent('mostrar-modal-seccion-guardada');
        } catch (ValidationException $e) {
            $this->mostrarError(collect($e->errors())->flatten()->first() ?? 'No fue posible guardar la sección.');
        } catch (\Throwable $e) {
            report($e);
            $this->mostrarError('No fue posible guardar la sección de la oferta Expo.');
        }
    }

    private function cargarDetalle(): void
    {
        $productos = app(SeccionadorOfertaExpo::class)
            ->pendientes($this->cotizacionOrigenId)
            ->filter(fn ($linea) => (float) $linea->cantidad_pendiente > 0)
            ->values();

        $expoId = (int) DB::table('expo_cotizacion')
            ->where('cotizacion_id', $this->cotizacionOrigenId)
            ->value('expo_id');
        $bodegaIds = DB::table('expo_bodega')
            ->where('expo_id', $expoId)
            ->pluck('bodega_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $stock = $productos->pluck('producto_id')
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($productoId) => [
                (int) $productoId => ExpoStock::resumen((int) $productoId, $bodegaIds),
            ])
            ->all();

        $this->productos = $productos
            ->map(function ($linea) use ($stock) {
                $resumen = $stock[(int) $linea->producto_id] ?? ['existencia' => 0.0, 'reservado' => 0.0, 'disponible' => 0.0];
                $linea->existencia_expo = $resumen['existencia'];
                $linea->reservado_expo = $resumen['reservado'];
                $linea->disponible_bodega = $resumen['disponible'];
                return $linea;
            })
            ->map(fn ($linea) => (array) $linea)
            ->all();
        $this->puedeCrear = collect($this->productos)->contains(
            fn ($linea) => (float) $linea['cantidad_pendiente'] > 0
        ) && !DB::table('expo_oferta_seccion')
            ->where('cotizacion_origen_id', $this->cotizacionOrigenId)
            ->where('finaliza_seccionado', 1)
            ->whereNotIn('estado', ['ANULADA', 'RECHAZADA_CREDITO', 'DEVUELTA_INVENTARIO'])
            ->exists();
        $this->cantidades = collect($this->productos)
            ->mapWithKeys(fn ($linea) => [(int) $linea['id'] => 0])
            ->all();
        $this->seleccionados = collect($this->productos)
            ->mapWithKeys(fn ($linea) => [(int) $linea['id'] => false])
            ->all();
        $this->seleccionarTodos = false;
        $secciones = DB::table('expo_oferta_seccion as eos')
            ->join('cotizacion as c', 'c.id', '=', 'eos.cotizacion_id')
            ->leftJoin('tipo_pago_venta as tp', 'tp.id', '=', 'c.tipo_pago_id')
            ->where('eos.cotizacion_origen_id', $this->cotizacionOrigenId)
            ->orderBy('eos.numero')
            ->get([
                'eos.id', 'eos.numero', 'eos.nombre', 'eos.estado', 'eos.cotizacion_id', 'eos.prefactura_id',
                'c.total', 'c.fecha_emision', 'c.fecha_vencimiento', 'c.tipo_pago_id', 'tp.descripcion as tipo_pago',
            ])
            ->map(fn ($fila) => (array) $fila);
        $productosSeccion = DB::table('cotizacion_has_producto')
            ->whereIn('cotizacion_id', $secciones->pluck('cotizacion_id'))
            ->orderBy('indice')
            ->get(['cotizacion_id', 'producto_id', 'nombre_producto', 'cantidad'])
            ->groupBy('cotizacion_id');
        $this->secciones = $secciones
            ->map(function ($seccion) use ($productosSeccion) {
                $seccion['productos'] = $productosSeccion->get($seccion['cotizacion_id'], collect())
                    ->map(fn ($producto) => (array) $producto)
                    ->all();
                return $seccion;
            })
            ->all();
    }

    private function cargarSeccionParaEditar(int $seccionId): void
    {
        $seccion = DB::table('expo_oferta_seccion as eos')
            ->join('cotizacion as c', 'c.id', '=', 'eos.cotizacion_id')
            ->where('eos.id', $seccionId)
            ->where('eos.flujo_id', $this->flujoId)
            ->where('eos.cotizacion_origen_id', $this->cotizacionOrigenId)
            ->whereIn('eos.estado', ['RECHAZADA_CREDITO', 'DEVUELTA_INVENTARIO'])
            ->first([
                'eos.id', 'eos.cotizacion_id', 'eos.estado', 'eos.finaliza_seccionado',
                'c.tipo_pago_id', 'c.fecha_emision', 'c.fecha_vencimiento',
            ]);

        if (!$seccion) {
            $this->mostrarError('La sección solicitada no está disponible para edición.');
            return;
        }

        $lineasHija = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $seccion->cotizacion_id)
            ->get(['indice', 'cantidad'])
            ->keyBy('indice');

        foreach ($this->productos as $producto) {
            $lineaHija = $lineasHija->get($producto['indice']);
            if ($lineaHija) {
                $lineaId = (int) $producto['id'];
                $this->seleccionados[$lineaId] = true;
                $this->cantidades[$lineaId] = (int) $lineaHija->cantidad;
            }
        }

        $this->editarSeccionId = (int) $seccion->id;
        $this->editarEstadoSeccion = (string) $seccion->estado;
        $this->tipoPagoId = (int) ($seccion->tipo_pago_id ?: 1);
        $this->fechaEmision = \Carbon\Carbon::parse($seccion->fecha_emision)->toDateString();
        $this->fechaPago = \Carbon\Carbon::parse($seccion->fecha_vencimiento ?: $seccion->fecha_emision)->toDateString();
        $this->finalizaSeccionado = (bool) $seccion->finaliza_seccionado;
        $this->comentarioCredito = (string) (DB::table('flujo_oferta_credito_comentarios')
            ->where('flujo_id', $this->flujoId)
            ->where('tramite_id', $seccion->cotizacion_id)
            ->latest('id')
            ->value('observacion') ?? '');
        if ($this->editarEstadoSeccion === 'DEVUELTA_INVENTARIO') {
            $this->cargarObservacionesInventario((int) $seccion->cotizacion_id);
        }
        $this->puedeCrear = true;
        $this->sincronizarSeleccionTodos();
    }

    private function cargarObservacionesInventario(int $cotizacionId): void
    {
        $this->comentarioInventarioGeneral = '';
        $this->comentariosInventarioProductos = [];

        $observacion = (string) (DB::table('historico_flujo')
            ->where('flujo_id', $this->flujoId)
            ->where('tipo_tramite_id', 9)
            ->where('tramite_id', $cotizacionId)
            ->where('estado_id', 7)
            ->latest('id')
            ->value('observaciones') ?? '');
        $detalle = trim((string) preg_replace('/^Devuelto a Oferta:\s*/i', '', $observacion));
        if ($detalle === '') {
            return;
        }

        $inicioNotas = strpos($detalle, ' | [');
        $this->comentarioInventarioGeneral = trim(
            $inicioNotas === false ? $detalle : substr($detalle, 0, $inicioNotas)
        );

        if ($inicioNotas !== false) {
            preg_match_all('/\|\s*\[([^\]]+)\]:\s*([^|]+)/', substr($detalle, $inicioNotas), $coincidencias, PREG_SET_ORDER);
            $this->comentariosInventarioProductos = collect($coincidencias)
                ->map(fn ($coincidencia) => [
                    'producto' => trim($coincidencia[1]),
                    'comentario' => trim($coincidencia[2]),
                ])
                ->all();
        }
    }

    public function render()
    {
        $term = mb_strtolower(trim($this->busquedaProducto));
        $productosFiltrados = collect($this->productos)
            ->filter(function ($producto) use ($term) {
                return $term === ''
                    || str_contains(mb_strtolower((string) $producto['nombre_producto']), $term)
                    || str_contains((string) $producto['producto_id'], $term);
            })
            ->values()
            ->all();

        return view('livewire.flujo.secciones-oferta', compact('productosFiltrados'));
    }

    public function cerrarMensajeError(): void
    {
        $this->mensajeError = '';
    }

    private function establecerFechaPagoPorDefecto(): void
    {
        if ($this->fechaEmision === '') {
            return;
        }

        $diasCredito = $this->tipoPagoId === 2
            ? max(0, (int) ($this->condicionPago['dias'] ?? 0))
            : 0;
        $this->fechaPago = \Carbon\Carbon::parse($this->fechaEmision)
            ->addDays($diasCredito)
            ->toDateString();
    }

    private function mostrarError(string $mensaje): void
    {
        $this->mensajeError = $mensaje;
        $this->dispatchBrowserEvent('mostrar-modal-error-seccion');
    }
}