<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PrefacturaAuditoria;

/**
 * Modal reutilizable "Flujo del Pedido".
 * Incluir con @livewire('flujo.modal-flujo-pedido') en cualquier vista.
 * Para abrirlo emitir: $this->emit('abrirFlujoPedido', $pedidoId)
 */
class ModalFlujoPedido extends Component
{
    // ── Estado del modal ──────────────────────────────────────────────────
    public $showModal       = false;
    public $pedidoData      = null;   // array del pedido + cliente + conteos
    public $pedidoDetalles  = [];     // productos del pedido
    public $flujoId         = null;   // flujo.id del pedido
    public $flujoTipos      = [];     // tipo_tramite_id[] presentes en historico_flujo

    // ── Paso activo en el stepper ─────────────────────────────────────────
    public $pasoActivo      = 'pedido'; // 'pedido'|'ofertas'|'prefactura'|'factura'|'entrega'|'cobro'

    // ── Gestión de ofertas ────────────────────────────────────────────────
    public $ofertasPedido       = [];
    public $ofertaSeleccionada  = null;
    public $confirmAccionOferta = null;  // null|'ganadora'|'anular_oferta'|'duplicar_oferta'
    public $motivoAnulOferta    = '';
    public bool $revisionInventarioActiva = false;

    // ── Acciones sobre el pedido ──────────────────────────────────────────
    public $confirmAccion    = null;  // null|'anular'|'duplicar'
    public $motivoAnulacion  = '';

    // ── Mensajes ──────────────────────────────────────────────────────────
    public $mensajeExito = '';
    public $mensajeError = '';

    // ── Prefactura del flujo activo ───────────────────────────────────────
    public $prefacturaData          = null;
    public $stockErrors             = [];  // errores de inventario al crear prefactura
    public $confirmAccionPrefactura = null; // null | 'revertir' | 'anular'
    public $vencimientoProcesado    = false; // true cuando se procesó el vencimiento en esta carga
    public $mostrarAutorizacionPrefactura = false;
    public $accionAutorizacionPrefactura  = null;
    public $codigoAutorizacion            = '';
    public $autorizacionId                = null;
    public $autorizadorId                 = null;
    public $motivoAutorizacion            = '';

    // ── Factura del flujo activo ─────────────────────────────────────────
    public $facturaData          = null;
    public $confirmAccionFactura = null; // null | 'anular'
    public $saldoPendienteFactura = null;
    public $estadoEntrega         = null;
    public $estadoCobro           = null;
    public $cobroFacturaData      = null;
    public $historialPagosFactura = [];
    public $historialEntregasFactura = [];
    public $aplicacionPagoId      = null;

    // ── Listeners ─────────────────────────────────────────────────────────
    protected $listeners = ['abrirFlujoPedido' => 'abrir', 'abrirFlujoCotizacion' => 'abrirDesdeFlujo', 'recargarFlujo' => 'recargarDesdeJS'];

    // ─────────────────────────────────────────────────────────────────────
    // ABRIR / CERRAR
    // ─────────────────────────────────────────────────────────────────────

    public function abrir(int $pedidoId, string $pasoInicial = 'pedido'): void
    {
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id', 'p.estado', 'p.observaciones', 'p.created_at',
                'c.nombre as cliente', 'c.rtn', 'c.id as cliente_id',
                'u.name as registrado_por',
                DB::raw("(SELECT COUNT(*) FROM historico_flujo hf
                           INNER JOIN flujo f ON f.id = hf.flujo_id
                           WHERE f.identificacion = CAST(p.id AS CHAR)
                             AND f.tipo_flujo_id = 1
                             AND hf.tipo_tramite_id = 2) as total_ofertas"),
                DB::raw("(SELECT COUNT(*) FROM historico_flujo hf
                           INNER JOIN flujo f ON f.id = hf.flujo_id
                           WHERE f.identificacion = CAST(p.id AS CHAR)
                             AND f.tipo_flujo_id = 1
                             AND hf.tipo_tramite_id = 2
                             AND hf.observaciones = 'ganadora') as has_ganadora")
            )
            ->where('p.id', $pedidoId)
            ->first();

        if (!$pedido) return;

        $this->pedidoData     = (array) $pedido;

        $this->pedidoDetalles = DB::table('pedido_detalle')
            ->where('pedido_id', $pedidoId)
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->flujoId = DB::table('flujo')
            ->where('identificacion', (string) $pedidoId)
            ->where('tipo_flujo_id', 1)
            ->value('id');

        // Derivar el paso activo del estado actual del flujo
        $flujoInfo = $this->flujoId
            ? DB::table('flujo as f')
                ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
                ->where('f.id', $this->flujoId)
                ->select('f.tipo_tramite_id', 'tt.nombre as tramite_nombre')
                ->first()
            : null;
        $tramiteStepMap = [
            1  => 'pedido',
            2  => 'ofertas',
            9  => 'revision_inventario',   // Revision de Inventario
            4  => 'prefactura',
            3  => 'factura',
            5  => 'entrega',
            8  => 'finalizado',
            'pedido'                => 'pedido',
            'Ofertas'               => 'ofertas',
            'Revision de Inventario'=> 'revision_inventario',
            'prefactura'            => 'prefactura',
            'factura'               => 'factura',
            'finalizado'            => 'finalizado',
            'Entrega Cobro'         => 'entrega',
        ];
        // Si el paso inicial es 'pedido' (default), auto-derivamos del flujo;
        // si se pasó explícitamente otro paso, lo respetamos
        $autoPaso = 'pedido';
        if ($flujoInfo && isset($tramiteStepMap[(int) $flujoInfo->tipo_tramite_id])) {
            $autoPaso = $tramiteStepMap[(int) $flujoInfo->tipo_tramite_id];
        } elseif ($flujoInfo && isset($tramiteStepMap[$flujoInfo->tramite_nombre])) {
            $autoPaso = $tramiteStepMap[$flujoInfo->tramite_nombre];
        }
        $pasoFinal = ($pasoInicial === 'pedido') ? $autoPaso : $pasoInicial;

        $this->flujoTipos = $this->flujoId
            ? DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('estado_id', '!=', 7)   // excluir registros inactivados
                ->pluck('tipo_tramite_id')
                ->unique()
                ->values()
                ->toArray()
            : [];

        $this->cargarEstadosEntregaCobro();
        $this->cargarOfertasPedido();

        // Config revisión de inventario
        $cfgRev = DB::table('configuracion_revision_inventario')->first();
        $this->revisionInventarioActiva = $cfgRev && (bool) $cfgRev->activo;

        // Resetear estado
        $this->pasoActivo              = $pasoFinal;
        $this->ofertaSeleccionada      = null;
        $this->confirmAccion           = null;
        $this->confirmAccionOferta     = null;
        $this->confirmAccionPrefactura = null;
        $this->motivoAnulacion         = '';
        $this->motivoAnulOferta        = '';
        $this->mensajeExito            = '';
        $this->mensajeError            = '';
        $this->stockErrors             = [];
        $this->prefacturaData          = null;
        $this->facturaData             = null;
        $this->confirmAccionFactura    = null;
        if ($pasoFinal === 'prefactura') {
            $this->cargarPrefactura();
        }
        if ($pasoFinal === 'factura') {
            $this->cargarFactura();
        }
        if ($pasoFinal === 'entrega') {
            $this->cargarHistorialEntregasFactura();
        }
        $this->cargarEstadoCobroFactura();
        $this->showModal               = true;
        $this->dispatchBrowserEvent('fmp-show');
    }

    /**
     * Abre el modal para un flujo cuyo origen es una cotización directa (sin pedido previo).
     * Listener: abrirFlujoCotizacion
     */
    public function abrirDesdeFlujo(int $flujoId): void
    {
        $flujo = DB::table('flujo')->where('id', $flujoId)->first();
        if (!$flujo) return;

        $cotizacion = DB::table('cotizacion')->where('id', $flujo->identificacion)->first();

        // Si no hay cotizacion, puede ser un flujo creado directamente desde una factura
        $facturaDirecta = null;
        if (!$cotizacion) {
            $facturaDirecta = DB::table('factura')
                ->where('id', $flujo->identificacion)
                ->first(['nombre_cliente', 'rtn', 'created_at', 'cliente_id']);
            if (!$facturaDirecta) return;
        }

        // Conteos de ofertas en el flujo
        $totalOfertas = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)
            ->count();

        $hasGanadora = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)
            ->where('observaciones', 'ganadora')
            ->count();

        // Construir pedidoData compatible con el blade del modal
        $this->pedidoData = [
            'id'             => (int) $flujo->identificacion,
            'estado'         => 'activo',
            'observaciones'  => $cotizacion ? ($cotizacion->observaciones ?? null) : null,
            'created_at'     => $cotizacion ? $cotizacion->created_at : $facturaDirecta->created_at,
            'cliente'        => $cotizacion ? ($cotizacion->nombre_cliente ?? '—') : ($facturaDirecta->nombre_cliente ?? '—'),
            'rtn'            => $cotizacion ? ($cotizacion->RTN ?? null) : ($facturaDirecta->rtn ?? null),
            'cliente_id'     => $cotizacion ? ($cotizacion->cliente_id ?? null) : ($facturaDirecta->cliente_id ?? null),
            'registrado_por' => null,
            'total_ofertas'  => $totalOfertas,
            'has_ganadora'   => $hasGanadora,
            'sin_pedido'     => true,   // ← indica que no tiene pedido vinculado
        ];

        $this->pedidoDetalles = [];
        $this->flujoId        = $flujoId;

        $this->flujoTipos = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('estado_id', '!=', 7)   // excluir registros inactivados
            ->pluck('tipo_tramite_id')
            ->unique()
            ->values()
            ->toArray();

        $this->cargarEstadosEntregaCobro();

        // Determinar paso activo desde el estado actual del flujo
        $flujoInfo = DB::table('flujo as f')
            ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->where('f.id', $flujoId)
            ->select('f.tipo_tramite_id', 'tt.nombre as tramite_nombre')
            ->first();

        $tramiteStepMap = [
            1  => 'ofertas',
            2  => 'ofertas',
            9  => 'revision_inventario',   // Revision de Inventario
            4  => 'prefactura',
            3  => 'factura',
            5  => 'entrega',
            6  => 'cobro',
            7  => 'entrega',    // Flujo conjunto (Entrega + Cobro)
            8  => 'finalizado',
            'pedido'                => 'ofertas',   // sin pedido, arrancamos en ofertas
            'Ofertas'               => 'ofertas',
            'Revision de Inventario'=> 'revision_inventario',
            'prefactura'            => 'prefactura',
            'factura'               => 'factura',
            'finalizado'            => 'finalizado',
            'Entrega Cobro'         => 'entrega',
        ];

        $this->cargarOfertasPedido();

        // Config revisión de inventario
        $cfgRev = DB::table('configuracion_revision_inventario')->first();
        $this->revisionInventarioActiva = $cfgRev && (bool) $cfgRev->activo;

        // Flujos de factura directa (sin cotizacion) arrancan en el paso de entrega/factura
        $pasoAbierto = $facturaDirecta ? 'factura' : 'ofertas';
        if ($flujoInfo && isset($tramiteStepMap[(int) $flujoInfo->tipo_tramite_id])) {
            $pasoAbierto = $tramiteStepMap[(int) $flujoInfo->tipo_tramite_id];
        } elseif ($flujoInfo && isset($tramiteStepMap[$flujoInfo->tramite_nombre])) {
            $pasoAbierto = $tramiteStepMap[$flujoInfo->tramite_nombre];
        }
        $this->pasoActivo              = $pasoAbierto;
        $this->ofertaSeleccionada      = null;
        $this->confirmAccion           = null;
        $this->confirmAccionOferta     = null;
        $this->confirmAccionPrefactura = null;
        $this->motivoAnulacion         = '';
        $this->motivoAnulOferta        = '';
        $this->mensajeExito            = '';
        $this->mensajeError            = '';
        $this->stockErrors             = [];
        $this->prefacturaData          = null;
        $this->facturaData             = null;
        $this->confirmAccionFactura    = null;
        if ($pasoAbierto === 'prefactura') {
            $this->cargarPrefactura();
        }
        if ($pasoAbierto === 'factura') {
            $this->cargarFactura();
        }
        if ($pasoAbierto === 'entrega') {
            $this->cargarHistorialEntregasFactura();
        }
        $this->cargarEstadoCobroFactura();
        $this->showModal               = true;
        $this->dispatchBrowserEvent('fmp-show');
    }

    /** Recarga los datos del pedido preservando el paso activo */
    public function recargarDesdeJS(): void
    {
        $this->recargar();
        $this->emit('pedidoActualizado');
    }

    private function recargar(): void
    {
        if (!$this->pedidoData) return;

        if (!empty($this->pedidoData['sin_pedido'])) {
            // Flujo originado desde cotización directa
            $this->abrirDesdeFlujo((int) $this->flujoId);
        } else {
            // Flujo originado desde pedido — abrir() deriva pasoActivo del DB
            $this->abrir((int) $this->pedidoData['id']);
        }
        // NO restaurar pasoActivo: abrir()/abrirDesdeFlujo() lo calculan
        // correctamente desde flujo.tipo_tramite_id en la DB
    }

    public function cerrar(): void
    {
        $this->emit('pedidoActualizado');
        $this->showModal            = false;
        $this->pedidoData           = null;
        $this->pedidoDetalles       = [];
        $this->flujoId              = null;
        $this->flujoTipos           = [];
        $this->pasoActivo           = 'pedido';
        $this->ofertasPedido        = [];
        $this->ofertaSeleccionada   = null;
        $this->confirmAccion        = null;
        $this->confirmAccionOferta  = null;
        $this->motivoAnulacion         = '';
        $this->motivoAnulOferta        = '';
        $this->mensajeExito            = '';
        $this->mensajeError            = '';
        $this->prefacturaData          = null;
        $this->stockErrors             = [];
        $this->confirmAccionPrefactura = null;
        $this->facturaData             = null;
        $this->confirmAccionFactura    = null;
        $this->saldoPendienteFactura   = null;
        $this->cobroFacturaData        = null;
        $this->historialPagosFactura   = [];
        $this->historialEntregasFactura = [];
        $this->aplicacionPagoId        = null;
        $this->tiposFacturacion        = [];
        $this->facturacionActiva       = false;
        $this->vencimientoProcesado    = false;
        $this->estadoEntrega           = null;
        $this->estadoCobro             = null;
    }

    // ─────────────────────────────────────────────────────────────────────
    // NAVEGACIÓN DE PASOS
    // ─────────────────────────────────────────────────────────────────────

    public function seleccionarPaso(string $paso): void
    {
        $this->pasoActivo           = $paso;
        $this->ofertaSeleccionada   = null;
        $this->confirmAccion        = null;
        $this->confirmAccionOferta  = null;
        $this->motivoAnulacion      = '';
        $this->motivoAnulOferta     = '';
        $this->mensajeExito         = '';
        $this->mensajeError         = '';

        if ($paso === 'ofertas') {
            $this->cargarOfertasPedido();
        }
        if ($paso === 'revision_inventario') {
            // Paso informativo; no requiere carga extra
        }
        if ($paso === 'prefactura') {
            $this->cargarPrefactura();
        }
        if ($paso === 'factura') {
            $this->cargarFactura();
        }
        if ($paso === 'cobro') {
            $this->cargarEstadoCobroFactura();
        }
        if ($paso === 'entrega') {
            $this->cargarHistorialEntregasFactura();
        }
        $this->tiposFacturacion  = [];
        $this->facturacionActiva = false;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCIONES SOBRE EL PEDIDO
    // ─────────────────────────────────────────────────────────────────────

    public function confirmarAccion(string $accion): void
    {
        $this->confirmAccion    = $accion;
        $this->motivoAnulacion  = '';
        $this->mensajeError     = '';
    }

    public function cancelarConfirmacion(): void
    {
        $this->confirmAccion    = null;
        $this->motivoAnulacion  = '';
        $this->mensajeError     = '';
    }

    public function anularPedido(): void
    {
        if (!$this->pedidoData) return;

        $motivo = trim($this->motivoAnulacion);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo de anulación.';
            return;
        }

        $pedidoId = (int) $this->pedidoData['id'];

        DB::beginTransaction();
        try {
            DB::table('pedido')
                ->where('id', $pedidoId)
                ->update(['estado' => 'cancelado', 'updated_at' => now()]);

            $canceladoId = DB::table('estado_venta')
                ->where('descripcion', 'cancelado')
                ->value('id');

            $hf = DB::table('historico_flujo')
                ->where('tipo_tramite_id', 1)
                ->where('tramite_id', $pedidoId)
                ->first();

            if ($hf) {
                DB::table('historico_flujo')
                    ->where('id', $hf->id)
                    ->update([
                        'estado_id'     => $canceladoId,
                        'observaciones' => 'Anulado: ' . $motivo,
                        'updated_by'    => Auth::id(),
                        'updated_at'    => now(),
                    ]);
                DB::table('flujo')
                    ->where('id', $hf->flujo_id)
                    ->update([
                        'estado_id'  => $canceladoId,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();
            $this->emit('pedidoActualizado');
            $this->confirmAccion = null;
            $this->recargar();
            $this->mensajeExito = 'Pedido #' . $pedidoId . ' anulado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al anular: ' . $e->getMessage();
        }
    }

    public function duplicarPedido(): void
    {
        if (!$this->pedidoData) return;
        $pedidoId = (int) $this->pedidoData['id'];

        $productos = DB::table('pedido_detalle')
            ->where('pedido_id', $pedidoId)
            ->get(['nombre_producto', 'cantidad'])
            ->map(fn($r) => [
                'nombre_producto' => $r->nombre_producto,
                'cantidad'        => $r->cantidad,
            ])
            ->toArray();

        $param = base64_encode(json_encode($productos));
        $url   = route('flujo.pedido') . '?productos=' . urlencode($param);
        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
        $this->cerrar();
    }

    // ─────────────────────────────────────────────────────────────────────
    // GESTIÓN DE OFERTAS
    // ─────────────────────────────────────────────────────────────────────

    public function cargarOfertasPedido(): void
    {
        if (!$this->flujoId) {
            $this->ofertasPedido = [];
            return;
        }

        $this->ofertasPedido = DB::table('historico_flujo as hf')
            ->join('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->where('hf.tipo_tramite_id', 2)
            ->select(
                'hf.id as hf_id',
                'hf.tramite_id as cotizacion_id',
                'hf.observaciones as hf_observaciones',
                'hf.created_at as hf_fecha',
                'c.nombre_cliente',
                'c.total',
                'c.cliente_id',
                'c.estado_id as cotizacion_estado_id'   // 1=activo, 2=inactivo por precios
            )
            ->orderByDesc('hf.id')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    public function verOferta(int $cotizacionId): void
    {
        $row = DB::table('cotizacion as c')
            ->leftJoin('historico_flujo as hf', function ($j) {
                $j->on('hf.tramite_id', '=', 'c.id')
                  ->where('hf.tipo_tramite_id', 2);
            })
            ->where('c.id', $cotizacionId)
            ->select(
                'c.id', 'c.nombre_cliente', 'c.RTN', 'c.total', 'c.isv',
                'c.sub_total', 'c.porc_descuento', 'c.monto_descuento',
                'c.fecha_emision', 'c.created_at', 'c.cliente_id',
                'c.estado_id as cotizacion_estado_id',
                'hf.observaciones as hf_observaciones'
            )
            ->first();

        if (!$row) return;

        $productos = DB::table('cotizacion_has_producto as chp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->where('chp.cotizacion_id', $cotizacionId)
            ->select(
                'chp.nombre_producto', 'chp.cantidad', 'chp.precio_unidad', 'chp.total',
                'chp.idPrecioSeleccionado', 'chp.precios_producto_carga_id',
                // precio actual del sistema para el tipo elegido
                DB::raw('CASE LOWER(TRIM(chp.idPrecioSeleccionado))
                    WHEN "p1" THEN ppc.precio_a
                    WHEN "p2" THEN ppc.precio_b
                    WHEN "p3" THEN ppc.precio_c
                    WHEN "p4" THEN ppc.precio_d
                    WHEN "a" THEN ppc.precio_a
                    WHEN "b" THEN ppc.precio_b
                    WHEN "c" THEN ppc.precio_c
                    WHEN "d" THEN ppc.precio_d
                    ELSE ppc.precio_base_venta
                END as precio_actual')
            )
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->ofertaSeleccionada  = array_merge((array) $row, ['productos' => $productos]);
        $this->confirmAccionOferta = null;
        $this->motivoAnulOferta    = '';
        $this->mensajeExito        = '';
        $this->mensajeError        = '';
    }

    public function cerrarOferta(): void
    {
        $this->ofertaSeleccionada  = null;
        $this->confirmAccionOferta = null;
        $this->motivoAnulOferta    = '';
        $this->mensajeExito        = '';
        $this->mensajeError        = '';
    }

    public function confirmarAccionOferta(string $accion): void
    {
        $this->confirmAccionOferta = $accion;
        $this->motivoAnulOferta    = '';
        $this->mensajeError        = '';
    }

    public function cancelarConfirmOferta(): void
    {
        $this->confirmAccionOferta = null;
        $this->motivoAnulOferta    = '';
        $this->mensajeError        = '';
    }

    public function ganadoraOferta(): void
    {
        if (!$this->ofertaSeleccionada || !$this->flujoId) return;

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];
        $cotizacion   = DB::table('cotizacion')->where('id', $cotizacionId)->first();
        if (!$cotizacion) { $this->mensajeError = 'Oferta no encontrada.'; return; }

        // ── Verificar si la revisión de inventario está activada ──────────
        $configRevision = DB::table('configuracion_revision_inventario')->first();
        $revisionActiva = $configRevision && (bool) $configRevision->activo;

        if ($revisionActiva) {
            // ── NUEVO FLUJO: Oferta Ganadora → Revisión de Inventario ─────
            DB::beginTransaction();
            try {
                // 1. Quitar ganadora anterior si existe
                DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 2)
                    ->where('observaciones', 'ganadora')
                    ->update(['observaciones' => null, 'updated_at' => now()]);

                // 2. Marcar esta oferta como ganadora
                DB::table('historico_flujo')
                    ->where('tramite_id', $cotizacionId)
                    ->where('tipo_tramite_id', 2)
                    ->where('flujo_id', $this->flujoId)
                    ->update(['observaciones' => 'ganadora', 'updated_at' => now()]);

                // 3. Auditoría cotizacion_estado
                DB::table('cotizacion_estado')->insert([
                    'cotizacion_id' => $cotizacionId,
                    'flujo_id'      => $this->flujoId,
                    'ganadora'      => 1,
                    'comentario'    => 'Marcada como ganadora. Enviada a Revisión de Inventario.',
                    'estado_id'     => 1,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 4. Crear registro en historico_flujo para el paso de Revisión de Inventario
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $this->flujoId,
                    'tipo_tramite_id' => 9,
                    'tramite_id'      => $cotizacionId,
                    'estado_id'       => 5,   // Pendiente
                    'observaciones'   => 'En Revisión de Inventario. Oferta #' . $cotizacionId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // 5. Avanzar flujo al paso Revisión de Inventario
                DB::table('flujo')->where('id', $this->flujoId)->update([
                    'tipo_tramite_id' => 9,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);

                DB::commit();
                $this->stockErrors         = [];
                $this->confirmAccionOferta = null;
                $this->ofertaSeleccionada  = null;
                $this->mensajeExito = 'Oferta #' . $cotizacionId . ' marcada como ganadora y enviada a Revisión de Inventario.';
                $this->emit('pedidoActualizado');
                $this->recargar();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->mensajeError = 'Error al enviar a Revisión de Inventario: ' . $e->getMessage();
            }
            return;
        }

        // ── FLUJO ORIGINAL: Oferta Ganadora → Prefactura ─────────────────

        $productos = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionId)
            ->get();

        // ── 1. Validar inventario (stock_real - reservado en prefacturas activas) ─
        $stockErrors = [];
        foreach ($productos as $prod) {
            if ($prod->resta_inventario && $prod->producto_id && $prod->seccion_id) {
                $rawStock  = (float) DB::table('recibido_bodega')
                    ->where('producto_id', $prod->producto_id)
                    ->where('seccion_id',  $prod->seccion_id)
                    ->where('cantidad_disponible', '>', 0)
                    ->sum('cantidad_disponible');
                $reservado = (float) DB::table('prefactura_has_producto as php')
                    ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
                    ->where('pf.estado', 'activo')
                    ->where('php.producto_id', $prod->producto_id)
                    ->where('php.seccion_id',  $prod->seccion_id)
                    ->where('php.resta_inventario', 1)
                    ->sum('php.cantidad');
                $disponible = max(0.0, $rawStock - $reservado);
                if ($disponible < $prod->cantidad) {
                    $stockErrors[] = [
                        'producto'   => $prod->nombre_producto,
                        'solicitado' => (int) $prod->cantidad,
                        'disponible' => (int) $disponible,
                    ];
                }
            }
        }

        if (!empty($stockErrors)) {
            $this->stockErrors  = $stockErrors;
            $this->mensajeError = 'No hay suficiente inventario para algunos productos.';
            return;
        }

        $config      = DB::table('configuracion_prefactura')->first();
        $diasValidez = $config ? (int) $config->dias_validez : 7;

        DB::beginTransaction();
        try {
            // ── 2. Quitar ganadora anterior si existe ──────────────────────
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('observaciones', 'ganadora')
                ->update(['observaciones' => null, 'updated_at' => now()]);

            // ── 3. Marcar esta oferta como ganadora ────────────────────────
            DB::table('historico_flujo')
                ->where('tramite_id', $cotizacionId)
                ->where('tipo_tramite_id', 2)
                ->where('flujo_id', $this->flujoId)
                ->update(['observaciones' => 'ganadora', 'updated_at' => now()]);

            // ── 4. Auditoría cotizacion_estado ─────────────────────────────
            DB::table('cotizacion_estado')->insert([
                'cotizacion_id' => $cotizacionId,
                'flujo_id'      => $this->flujoId,
                'ganadora'      => 1,
                'comentario'    => 'Marcada como ganadora',
                'estado_id'     => 1,
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // ── 5. Crear prefactura ────────────────────────────────────────
            $prefacturaId = DB::table('prefactura')->insertGetId([
                'cotizacion_id'     => $cotizacionId,
                'flujo_id'          => $this->flujoId,
                'cliente_id'        => $cotizacion->cliente_id,
                'nombre_cliente'    => $cotizacion->nombre_cliente,
                'RTN'               => $cotizacion->RTN,
                'fecha_emision'     => now()->toDateString(),
                'fecha_vencimiento' => now()->addDays($diasValidez)->toDateString(),
                'sub_total'         => $cotizacion->sub_total,
                'sub_total_grabado' => $cotizacion->sub_total_grabado,
                'sub_total_excento' => $cotizacion->sub_total_excento,
                'isv'               => $cotizacion->isv,
                'total'             => $cotizacion->total,
                'porc_descuento'    => $cotizacion->porc_descuento ?? 0,
                'monto_descuento'   => $cotizacion->monto_descuento ?? 0,
                'tipo_venta_id'     => $cotizacion->tipo_venta_id,
                'vendedor'          => $cotizacion->vendedor,
                'nota'              => $cotizacion->nota,
                'arregloIdInputs'   => $cotizacion->arregloIdInputs,
                'numeroInputs'      => $cotizacion->numeroInputs,
                'estado'            => 'activo',
                'users_id'          => Auth::id(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // ── 6. Insertar productos de la prefactura ─────────────────────
            $prefProds = [];
            foreach ($productos as $prod) {
                $prefProds[] = [
                    'prefactura_id'            => $prefacturaId,
                    'producto_id'              => $prod->producto_id,
                    'indice'                   => $prod->indice,
                    'nombre_producto'          => $prod->nombre_producto,
                    'nombre_bodega'            => $prod->nombre_bodega,
                    'precio_unidad'            => $prod->precio_unidad,
                    'cantidad'                 => $prod->cantidad,
                    'sub_total'                => $prod->sub_total,
                    'isv'                      => $prod->isv,
                    'total'                    => $prod->total,
                    'isv_producto'             => $prod->isv_producto,
                    'Bodega_id'                => $prod->bodega_id,
                    'seccion_id'               => $prod->seccion_id,
                    'unidad_medida_venta_id'   => $prod->unidad_medida_venta_id,
                    'monto_descProducto'       => $prod->monto_descProducto ?? 0,
                    'idPrecioSeleccionado'      => $prod->idPrecioSeleccionado ?? null,
                    'precioSeleccionado'        => $prod->precioSeleccionado ?? null,
                    'precios_producto_carga_id' => $prod->precios_producto_carga_id ?? null,
                    'resta_inventario'          => $prod->resta_inventario,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ];
            }
            if (!empty($prefProds)) {
                DB::table('prefactura_has_producto')->insert($prefProds);
            }

            // (inventario se calcula dinámicamente: stock_real - reservado en prefacturas activas)
            // ── 8. Historico flujo para prefactura ─────────────────────────
            DB::table('historico_flujo')->insert([
                'flujo_id'        => $this->flujoId,
                'tipo_tramite_id' => 4,
                'tramite_id'      => $prefacturaId,
                'estado_id'       => 1,
                'observaciones'   => 'Prefactura #' . $prefacturaId . ' creada desde oferta #' . $cotizacionId,
                'created_by'      => Auth::id(),
                'updated_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // ── 9. Avanzar flujo a prefactura ──────────────────────────────
            DB::table('flujo')->where('id', $this->flujoId)->update([
                'tipo_tramite_id' => 4,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            DB::commit();
            $this->stockErrors         = [];
            $this->confirmAccionOferta = null;
            $this->ofertaSeleccionada  = null;
            $this->mensajeExito = 'Prefactura #' . $prefacturaId . ' generada. Válida por ' . $diasValidez . ' día(s).';
            $this->emit('pedidoActualizado');
            $this->recargar();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al crear prefactura: ' . $e->getMessage();
        }
    }

    public function quitarGanadora(): void
    {
        if (!$this->ofertaSeleccionada || !$this->flujoId) return;

        $motivo = trim($this->motivoAnulOferta);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo para quitar la ganadora.';
            return;
        }

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];

        DB::beginTransaction();
        try {
            // 1. Marcar oferta como QuitadaGanadora
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $cotizacionId)
                ->update(['observaciones' => 'QuitadaGanadora: ' . $motivo, 'updated_at' => now()]);

            // 2. Auditoría
            DB::table('cotizacion_estado')->insert([
                'cotizacion_id' => $cotizacionId,
                'flujo_id'      => $this->flujoId,
                'ganadora'      => 2,
                'comentario'    => 'Quitada ganadora: ' . $motivo,
                'estado_id'     => 1,
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // 3. Inactivar prefactura activa vinculada a este flujo/cotización
            $prefactura = DB::table('prefactura')
                ->where('flujo_id', $this->flujoId)
                ->where('cotizacion_id', $cotizacionId)
                ->where('estado', 'activo')
                ->first();

            if ($prefactura) {
                DB::table('prefactura')
                    ->where('id', $prefactura->id)
                    ->update(['estado' => 'inactive', 'updated_at' => now()]);

                DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 4)
                    ->where('tramite_id', $prefactura->id)
                    ->update(['estado_id' => 7, 'updated_at' => now()]);
            }

            // 4. Retroceder flujo a Ofertas
            DB::table('flujo')
                ->where('id', $this->flujoId)
                ->update(['tipo_tramite_id' => 2, 'updated_by' => Auth::id(), 'updated_at' => now()]);

            DB::commit();
            $this->mensajeExito = 'Oferta #' . $cotizacionId . ' ya no es ganadora.';
            $this->emit('pedidoActualizado');
            $this->recargar();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error: ' . $e->getMessage();
        }
    }

    public function anularOferta(): void
    {
        if (!$this->ofertaSeleccionada) return;

        $motivo = trim($this->motivoAnulOferta);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo de anulación.';
            return;
        }

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];

        DB::table('historico_flujo')
            ->where('tramite_id', $cotizacionId)
            ->where('tipo_tramite_id', 2)
            ->update([
                'observaciones' => 'Anulado: ' . $motivo,
                'updated_by'    => Auth::id(),
                'updated_at'    => now(),
            ]);

        // Auditoría: registrar en cotizacion_estado como anulada
        DB::table('cotizacion_estado')->insert([
            'cotizacion_id' => $cotizacionId,
            'flujo_id'      => $this->flujoId,
            'ganadora'      => 3,
            'comentario'    => 'Anulada: ' . $motivo,
            'estado_id'     => 1,
            'created_by'    => Auth::id(),
            'updated_by'    => Auth::id(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->mensajeExito = 'Oferta #' . $cotizacionId . ' anulada correctamente.';
        $this->emit('pedidoActualizado');
        $this->recargar();
    }

    public function duplicarOferta(bool $mismoCliente): void
    {
        if (!$this->ofertaSeleccionada) return;

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];

        // ── Validar que los precios no hayan cambiado ─────────────────────
        if ($this->verificarCambioPrecios($cotizacionId)) {
            $this->mensajeError = 'No se puede duplicar: uno o más precios de esta oferta han cambiado. Crea una nueva oferta con los precios actualizados.';
            $this->confirmAccionOferta = null;
            return;
        }

        // Construir URL base
        $url = '/proforma/cotizacion/2?from=flujo&cotizacionId=' . $cotizacionId;

        if ($mismoCliente) {
            if (!empty($this->pedidoData) && empty($this->pedidoData['sin_pedido'])) {
                $url .= '&pedidoId=' . (int) $this->pedidoData['id'];
            } elseif ($this->flujoId) {
                $url .= '&flujoId=' . $this->flujoId;
            }
        }

        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
        $this->confirmAccionOferta = null;
    }

    public function nuevaOferta(): void
    {
        if (!$this->pedidoData) return;

        if (!empty($this->pedidoData['sin_pedido'])) {
            // Flujo originado desde cotización, sin pedido vinculado
            $this->redirect('/proforma/cotizacion/2?from=flujo&flujoId=' . $this->flujoId);
        } else {
            $pedidoId = (int) $this->pedidoData['id'];
            $this->redirect('/proforma/cotizacion/2?from=flujo&pedidoId=' . $pedidoId);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // GESTIÓN DE PREFACTURA
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Procesa el vencimiento de una prefactura:
     * 1. Inactiva la prefactura.
     * 2. Marca historico_flujo de la prefactura con estado_id=4 (Vencido).
     * 3. Retrocede el flujo a Ofertas (tipo_tramite_id=2).
     * 4. Por cada cotización del flujo, compara precios:
     *    - Precios cambiaron → inactiva cotizacion (estado_id=2) + cotizacion_estado.
     *    - Precios no cambiaron → cotizacion queda activa, cotizacion_estado con observación.
     */
    private function procesarVencimientoPrefactura(object $pref): void
    {
        DB::beginTransaction();
        try {
            // 1. Inactivar prefactura
            DB::table('prefactura')
                ->where('id', $pref->id)
                ->update(['estado' => 'inactive', 'updated_at' => now()]);

            // 2. Marcar historico_flujo de la prefactura como Vencido (estado_id=4)
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 4)          // tipo_tramite_id=4 = Prefactura
                ->where('tramite_id', $pref->id)
                ->update(['estado_id' => 4, 'updated_at' => now()]);

            // 3. Retroceder flujo a Ofertas
            DB::table('flujo')
                ->where('id', $this->flujoId)
                ->update([
                    'tipo_tramite_id' => 2,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);

            // 4. Validar precios por cada cotización del flujo
            $cotizaciones = DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 2)      // tipo_tramite_id=2 = Ofertas
                ->whereNotIn('observaciones', ['ganadora'])
                ->whereRaw('(observaciones NOT LIKE ? OR observaciones IS NULL)', ['Anulado:%'])
                ->pluck('tramite_id')
                ->unique();

            foreach ($cotizaciones as $cotId) {
                $preciosCambiaron = $this->verificarCambioPrecios((int) $cotId);

                if ($preciosCambiaron) {
                    // Inactivar cotización por precios desactualizados
                    DB::table('cotizacion')
                        ->where('id', $cotId)
                        ->update(['estado_id' => 2, 'updated_at' => now()]);

                    // Marcar en historico_flujo la oferta como VencidaPrecios
                    DB::table('historico_flujo')
                        ->where('flujo_id', $this->flujoId)
                        ->where('tipo_tramite_id', 2)
                        ->where('tramite_id', $cotId)
                        ->update([
                            'observaciones' => 'VencidaPrecios: Prefactura vencida, precios cambiaron',
                            'updated_at'    => now(),
                        ]);

                    DB::table('cotizacion_estado')->insert([
                        'cotizacion_id' => $cotId,
                        'flujo_id'      => $this->flujoId,
                        'ganadora'      => 4,  // 4 = Vencida / inactiva por precios
                        'comentario'    => 'Oferta inactivada: prefactura #' . $pref->id . ' venció y los precios cambiaron',
                        'estado_id'     => 1,
                        'created_by'    => Auth::id(),
                        'updated_by'    => Auth::id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                } else {
                    // Precios sin cambio: oferta vuelve a quedar disponible
                    DB::table('historico_flujo')
                        ->where('flujo_id', $this->flujoId)
                        ->where('tipo_tramite_id', 2)
                        ->where('tramite_id', $cotId)
                        ->where('observaciones', 'ganadora')   // si estaba marcada como ganadora, limpiar
                        ->update(['observaciones' => null, 'updated_at' => now()]);

                    DB::table('cotizacion_estado')->insert([
                        'cotizacion_id' => $cotId,
                        'flujo_id'      => $this->flujoId,
                        'ganadora'      => 2,
                        'comentario'    => 'Oferta reactivada: prefactura #' . $pref->id . ' venció, precios sin cambio',
                        'estado_id'     => 1,
                        'created_by'    => Auth::id(),
                        'updated_by'    => Auth::id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            DB::commit();
            $this->mensajeError = ''; // limpio para que el blade muestre el aviso de vencimiento
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al procesar vencimiento: ' . $e->getMessage();
        }
    }

    /**
     * Compara los precios de cada producto de la cotización con los precios actuales
     * en precios_producto_carga. Devuelve true si al menos un producto cambió de precio.
     */
    private function verificarCambioPrecios(int $cotizacionId): bool
    {
        $lineas = DB::table('cotizacion_has_producto as chp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->where('chp.cotizacion_id', $cotizacionId)
            ->whereNotNull('chp.precios_producto_carga_id')
            ->select(
                'chp.precio_unidad',
                'chp.idPrecioSeleccionado',
                'ppc.precio_a', 'ppc.precio_b', 'ppc.precio_c', 'ppc.precio_d',
                'ppc.precio_base_venta'
            )
            ->get();

        foreach ($lineas as $l) {
            $selector = strtolower(trim((string) $l->idPrecioSeleccionado));
            $precioActual = match($selector) {
                'p1', 'a' => (float) $l->precio_a,
                'p2', 'b' => (float) $l->precio_b,
                'p3', 'c' => (float) $l->precio_c,
                'p4', 'd' => (float) $l->precio_d,
                default => (float) $l->precio_base_venta,
            };
            // Comparar con 4 decimales de tolerancia
            if (abs((float)$l->precio_unidad - $precioActual) > 0.0001) {
                return true;
            }
        }
        return false;
    }

    private function cargarPrefactura(): void
    {
        if (!$this->flujoId) {
            $this->prefacturaData = null;
            return;
        }
        $pref = DB::table('prefactura')
            ->where('flujo_id', $this->flujoId)
            ->where('estado', 'activo')
            ->orderByDesc('id')
            ->first();

        if (!$pref) {
            $this->prefacturaData = null;
            return;
        }

        // ── Verificar vencimiento ──────────────────────────────────────────
        if ($pref->fecha_vencimiento && now()->startOfDay()->gt(
                \Carbon\Carbon::parse($pref->fecha_vencimiento)->startOfDay()
            )) {
            $this->procesarVencimientoPrefactura($pref);
            $this->prefacturaData    = null;
            $this->vencimientoProcesado = true;
            // Recargar las ofertas para que reflejen los nuevos estados
            $this->cargarOfertasPedido();
            return;
        }

        $this->vencimientoProcesado = false;

        $productos = DB::table('prefactura_has_producto')
            ->where('prefactura_id', $pref->id)
            ->select('nombre_producto', 'cantidad', 'precio_unidad', 'total')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->prefacturaData = array_merge((array) $pref, ['productos' => $productos]);
    }

    public function confirmarAccionPrefactura(string $accion): void
    {
        $this->confirmAccionPrefactura = $accion;
        $this->confirmAccionFactura    = null;
        $this->mensajeError            = '';
    }

    public function cancelarConfirmPrefactura(): void
    {
        $this->confirmAccionPrefactura = null;
        $this->mensajeError            = '';
    }

    public function solicitarAutorizacionPrefactura(string $accion): void
    {
        $this->accionAutorizacionPrefactura = $accion;
        $this->mostrarAutorizacionPrefactura = true;
        $this->codigoAutorizacion = '';
        $this->autorizacionId = null;
        $this->autorizadorId = null;
        $this->motivoAutorizacion = '';
        $this->mensajeError = '';
    }

    public function cancelarAutorizacionPrefactura(): void
    {
        $this->mostrarAutorizacionPrefactura = false;
        $this->accionAutorizacionPrefactura = null;
        $this->codigoAutorizacion = '';
        $this->autorizacionId = null;
        $this->autorizadorId = null;
        $this->motivoAutorizacion = '';
        $this->mensajeError = '';
    }

    public function validarCodigoAutorizacionPrefactura(): void
    {
        $codigo = trim((string) $this->codigoAutorizacion);
        if ($codigo === '') {
            $this->mensajeError = 'Debe ingresar un código de autorización válido.';
            return;
        }

        $autorizacion = DB::table('codigo_autorizacion')
            ->where('estado_id', 1)
            ->where('codigo', $codigo)
            ->first(['id', 'users_id']);

        if (!$autorizacion) {
            $this->mensajeError = 'El código de autorización es inválido o ya fue desactivado.';
            return;
        }

        $this->autorizacionId = (int) $autorizacion->id;
        $this->autorizadorId = (int) $autorizacion->users_id;

        $motivo = trim((string) $this->motivoAutorizacion);
        if ($motivo === '') {
            $this->mensajeError = 'El motivo es obligatorio.';
            return;
        }

        // Desactivar el código para que no pueda reutilizarse
        DB::table('codigo_autorizacion')
            ->where('id', $this->autorizacionId)
            ->update(['estado_id' => 2, 'updated_at' => now()]);

        if ($this->accionAutorizacionPrefactura === 'editar_factura') {
            $this->redireccionarEdicionFacturaAutorizada();
            return;
        }

        if ($this->accionAutorizacionPrefactura === 'anular_prefactura') {
            $this->anularPrefactura();
            return;
        }

        if ($this->accionAutorizacionPrefactura === 'revertir_prefactura') {
            $this->revertirPrefacturaAOferta();
            return;
        }

        $this->mensajeError = 'Acción de autorización no reconocida.';
    }

    public function facturarPrefacturaDirecta(): void
    {
        if (!$this->prefacturaData || !$this->flujoId) return;

        $this->dispatchBrowserEvent('fmp-facturar-directo', [
            'url' => '/prefactura/' . (int) $this->prefacturaData['id'] . '/facturar-directo',
        ]);
    }

    private function redireccionarEdicionFacturaAutorizada(): void
    {
        if (!$this->prefacturaData || !$this->flujoId) return;

        $tipoFactura = DB::table('tipo_factura')
            ->where('estado', 1)
            ->where('codigo', '!=', 'cotizacion_clientes_a')
            ->orderBy('orden')
            ->first(['ruta_menu']);

        if (!$tipoFactura) {
            $this->mensajeError = 'No hay tipos de facturación disponibles.';
            return;
        }

        $prefacturaId = (int) $this->prefacturaData['id'];
        $url = '/' . ltrim($tipoFactura->ruta_menu, '/')
             . '?from=prefactura&prefactura_id=' . $prefacturaId
             . '&flujoId=' . $this->flujoId
             . '&modo=editar_factura'
             . '&autorizacion_id=' . $this->autorizacionId
             . '&autorizador_id=' . $this->autorizadorId;

        $this->dispatchBrowserEvent('fmp-redirigir', ['url' => $url]);
    }

    private function cargarFactura(): void
    {
        $facturaId = $this->obtenerFacturaIdFlujo();
        if (!$facturaId) {
            $this->facturaData = null;
            return;
        }

        $factura = DB::table('factura')
            ->where('id', $facturaId)
            ->first();

        if (!$factura) {
            $this->facturaData = null;
            return;
        }

        $productos = DB::table('venta_has_producto as vhp')
            ->leftJoin('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->where('vhp.factura_id', $factura->id)
            ->select(
                DB::raw('COALESCE(p.nombre, CONCAT("Producto #", vhp.producto_id)) as nombre_producto'),
                'vhp.cantidad',
                'vhp.precio_unidad',
                DB::raw('COALESCE(vhp.total, vhp.total_s) as total')
            )
            ->orderBy('vhp.indice')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->facturaData = array_merge((array) $factura, [
            'productos'      => $productos,
            'historico_id'   => null,
            'tramite_tipo_id'=> 3,
            'print_url'      => '/factura/cooporativo/' . $factura->id,
        ]);

        $this->saldoPendienteFactura = isset($factura->pendiente_cobro)
            ? (float) $factura->pendiente_cobro
            : null;
    }

    private function obtenerFacturaIdFlujo(): ?int
    {
        if (!$this->flujoId) {
            return null;
        }

        // Regla principal: la factura real vive en tipo_tramite_id = 3.
        $histFactura = DB::table('historico_flujo')
            ->where('flujo_id', $this->flujoId)
            ->where('tipo_tramite_id', 3)
            ->whereNotNull('tramite_id')
            ->where('estado_id', '!=', 7)
            ->orderByDesc('id')
            ->first(['tramite_id']);

        if ($histFactura) {
            return (int) $histFactura->tramite_id;
        }

        // Compatibilidad legacy: algunos flujos guardaron la factura en tipo 5.
        // Se valida que tramite_id exista en tabla factura para no confundirlo
        // con un id de distribución de entrega.
        $histLegacy = DB::table('historico_flujo as hf')
            ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->where('hf.tipo_tramite_id', 5)
            ->whereNotNull('hf.tramite_id')
            ->where('hf.estado_id', '!=', 7)
            ->orderByDesc('hf.id')
            ->first(['hf.tramite_id']);

        return $histLegacy ? (int) $histLegacy->tramite_id : null;
    }

    /**
     * Carga el estado_id real de los registros Entrega (tipo 5) y Cobro (tipo 6)
     * desde historico_flujo para reflejar pendiente (5) o completado (1) en el stepper.
     */
    private function cargarEstadosEntregaCobro(): void
    {
        if (!$this->flujoId) {
            $this->estadoEntrega = null;
            $this->estadoCobro   = null;
            return;
        }

        $entrega = DB::table('historico_flujo')
            ->where('flujo_id', $this->flujoId)
            ->where('tipo_tramite_id', 5)
            ->where('estado_id', '!=', 7)
            ->orderByDesc('id')
            ->first(['estado_id', 'tramite_id']);

        // Compatibilidad: si el tipo 5 más reciente apunta a una factura histórica,
        // usar el registro del modelo nuevo (tramite_id NULL o distribución válida).
        if ($entrega && !empty($entrega->tramite_id)) {
            $esDistribucion = DB::table('distribuciones_entrega')
                ->where('id', (int) $entrega->tramite_id)
                ->exists();

            if (!$esDistribucion) {
                $entrega = DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 5)
                    ->where('estado_id', '!=', 7)
                    ->whereNull('tramite_id')
                    ->orderByDesc('id')
                    ->first(['estado_id', 'tramite_id']);
            }
        }

        $cobro = DB::table('historico_flujo')
            ->where('flujo_id', $this->flujoId)
            ->where('tipo_tramite_id', 6)
            ->where('estado_id', '!=', 7)
            ->orderByDesc('id')
            ->value('estado_id');

        $this->estadoEntrega = $entrega ? (int) $entrega->estado_id : null;
        $this->estadoCobro   = $cobro   ? (int) $cobro   : null;
    }

    private function cargarHistorialEntregasFactura(): void
    {
        if (!$this->flujoId) {
            $this->historialEntregasFactura = [];
            return;
        }

        $facturaId = $this->obtenerFacturaIdFlujo();
        if (!$facturaId) {
            $this->historialEntregasFactura = [];
            return;
        }

        $miembrosSnapshot = DB::table('distribuciones_entrega_miembros as dem')
            ->leftJoin('users as u', 'u.id', '=', 'dem.user_id')
            ->select(
                'dem.distribucion_entrega_id',
                DB::raw("GROUP_CONCAT(DISTINCT TRIM(COALESCE(u.name, '')) ORDER BY u.name SEPARATOR ', ') as miembros")
            )
            ->groupBy('dem.distribucion_entrega_id');

        $this->historialEntregasFactura = DB::table('distribuciones_entrega_facturas as def')
            ->join('distribuciones_entrega as de', 'de.id', '=', 'def.distribucion_entrega_id')
            ->leftJoin('equipos_entrega as ee', 'ee.id', '=', 'de.equipo_entrega_id')
            ->leftJoinSub($miembrosSnapshot, 'ms', function ($join) {
                $join->on('ms.distribucion_entrega_id', '=', 'de.id');
            })
            ->where('def.factura_id', $facturaId)
            ->orderBy('de.fecha_programada')
            ->orderBy('de.id')
            ->get([
                'de.id as distribucion_id',
                'de.fecha_programada',
                'de.estado_id',
                'ee.nombre_equipo',
                'ms.miembros as equipo_miembros',
                'def.orden_entrega',
                'def.estado_entrega',
                'def.fecha_entrega_real',
                'def.observaciones',
            ])
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    private function cargarEstadoCobroFactura(): void
    {
        if (!$this->flujoId) {
            $this->saldoPendienteFactura = null;
            $this->cobroFacturaData = null;
            $this->historialPagosFactura = [];
            $this->aplicacionPagoId = null;
            return;
        }

        $facturaId = $this->obtenerFacturaIdFlujo();
        if (!$facturaId) {
            $this->saldoPendienteFactura = null;
            $this->cobroFacturaData = null;
            $this->historialPagosFactura = [];
            $this->aplicacionPagoId = null;
            return;
        }

        $factura = DB::table('factura')
            ->where('id', $facturaId)
            ->first(['id', 'cai', 'nombre_cliente', 'total', 'fecha_emision', 'created_at', 'pendiente_cobro']);

        $ap = DB::table('aplicacion_pagos')
            ->where('factura_id', $facturaId)
            ->where('estado', 1)
            ->orderByDesc('id')
            ->first(['id', 'saldo', 'credito_abonos', 'created_at', 'updated_at']);

        if (!$ap) {
            $ap = DB::table('aplicacion_pagos')
                ->where('factura_id', $facturaId)
                ->orderByDesc('id')
                ->first(['id', 'saldo', 'credito_abonos', 'created_at', 'updated_at']);
        }

        $this->aplicacionPagoId = $ap ? (int) $ap->id : null;
        $this->saldoPendienteFactura = $ap
            ? (float) $ap->saldo
            : (float) ($factura->pendiente_cobro ?? 0);

        $this->cobroFacturaData = [
            'id'           => (int) $factura->id,
            'cai'          => $factura->cai,
            'nombre'       => $factura->nombre_cliente,
            'total'        => (float) ($factura->total ?? 0),
            'fecha_emision'=> $factura->fecha_emision ?? $factura->created_at,
        ];

        $historial = DB::table('abonos_creditos as ac')
            ->leftJoin('users as u', 'u.id', '=', 'ac.usr_registro')
            ->leftJoin('tipo_pago_cobro as tpc', 'tpc.id', '=', 'ac.id_tipo_pago_cobro')
            ->leftJoin('banco as b', 'b.id', '=', 'ac.banco_id')
            ->where('ac.factura_id', $facturaId)
            ->where('ac.estado_abono', 1)
            ->orderByDesc('ac.fecha_pago')
            ->orderByDesc('ac.id')
            ->get([
                'ac.id',
                'ac.monto_abonado',
                'ac.fecha_pago',
                'ac.numero_recibo',
                'ac.comentario',
                'ac.url_documento',
                'tpc.descripcion as tipo_pago',
                DB::raw("CONCAT(COALESCE(b.nombre,''), CASE WHEN b.cuenta IS NOT NULL AND b.cuenta <> '' THEN CONCAT(' - ', b.cuenta) ELSE '' END) as banco"),
                'u.name as usuario',
            ])
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->historialPagosFactura = $historial;

        // Sincronizar Cobro en historico_flujo con aplicacion_pagos:
        // - tramite_id del Cobro = aplicacion_pagos.id
        // - si saldo <= 0 => estado_id del Cobro pasa a 1 (completado)
        $cobroHist = DB::table('historico_flujo')
            ->where('flujo_id', $this->flujoId)
            ->where('tipo_tramite_id', 6)
            ->where('estado_id', '!=', 7)
            ->orderByDesc('id')
            ->first(['id', 'tramite_id', 'estado_id']);

        $actualizarCobro = [];
        if ($cobroHist) {
            if (empty($cobroHist->tramite_id) && $this->aplicacionPagoId) {
                $actualizarCobro['tramite_id'] = $this->aplicacionPagoId;
            }

            if ((float) $this->saldoPendienteFactura <= 0 && (int) $cobroHist->estado_id !== 1) {
                $actualizarCobro['estado_id'] = 1;
                $actualizarCobro['observaciones'] = 'Cobro completado por saldo <= 0 (Factura #' . $facturaId . ')';
            }

            if (!empty($actualizarCobro)) {
                $actualizarCobro['updated_at'] = now();
                DB::table('historico_flujo')
                    ->where('id', $cobroHist->id)
                    ->update($actualizarCobro);
            }
        }

        // Si Cobro quedó completado y Entrega está completada, mover flujo a Finalizado (tipo 8)
        if ((float) $this->saldoPendienteFactura <= 0) {
            $entregaCompletada = DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 5)
                ->where('estado_id', 1)
                ->where('estado_id', '!=', 7)
                ->exists();

            if ($entregaCompletada) {
                DB::table('flujo')
                    ->where('id', $this->flujoId)
                    ->update([
                        'tipo_tramite_id' => 8,
                        'updated_by'      => Auth::id(),
                        'updated_at'      => now(),
                    ]);
            }
        }

        $this->cargarEstadosEntregaCobro();
    }

    public function confirmarAccionFactura(string $accion): void
    {
        $this->confirmAccionFactura    = $accion;
        $this->confirmAccionPrefactura = null;
        $this->mensajeError            = '';
    }

    public function cancelarConfirmFactura(): void
    {
        $this->confirmAccionFactura = null;
        $this->mensajeError         = '';
    }

    public function anularFactura(): void
    {
        if (!$this->facturaData || !$this->flujoId) return;

        $facturaId = (int) $this->facturaData['id'];

        DB::beginTransaction();
        try {
            // 1) Inactivar registro de factura (tipo 3 / legacy tipo 5 con tramite_id)
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->whereIn('tipo_tramite_id', [3, 5])
                ->where('tramite_id', $facturaId)
                ->update([
                    'estado_id'     => 7,
                    'observaciones' => 'Anulada: Factura #' . $facturaId,
                    'updated_at'    => now(),
                ]);

            // 1b) Inactivar registros de Entrega (tipo 5) y Cobro (tipo 6) del nuevo modelo
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->whereIn('tipo_tramite_id', [5, 6])
                ->whereIn('estado_id', [1, 5])
                ->update([
                    'estado_id'     => 7,
                    'observaciones' => 'Anulado por anulación de Factura #' . $facturaId,
                    'updated_at'    => now(),
                ]);

            // 2) Regla de negocio:
            //    - prefactura vigente -> volver a Prefactura
            //    - prefactura vencida/no activa -> volver a Ofertas y validar precios
            $prefActiva = DB::table('prefactura')
                ->where('flujo_id', $this->flujoId)
                ->where('estado', 'activo')
                ->orderByDesc('id')
                ->first();

            $prefVigente = false;
            if ($prefActiva && !empty($prefActiva->fecha_vencimiento)) {
                $prefVigente = now()->startOfDay()->lte(
                    \Carbon\Carbon::parse($prefActiva->fecha_vencimiento)->startOfDay()
                );
            }

            if ($prefActiva && $prefVigente) {
                DB::table('flujo')->where('id', $this->flujoId)->update([
                    'tipo_tramite_id' => 4,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);
                $this->mensajeExito = 'Factura #' . $facturaId . ' anulada. El flujo volvió a Prefactura.';
            } else {
                // Regresar a ofertas y validar vigencia de precios
                DB::table('flujo')->where('id', $this->flujoId)->update([
                    'tipo_tramite_id' => 2,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);
                $cotizaciones = DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 2)
                    ->whereNotIn('observaciones', ['ganadora'])
                    ->whereRaw('(observaciones NOT LIKE ? OR observaciones IS NULL)', ['Anulado:%'])
                    ->pluck('tramite_id')
                    ->unique();

                foreach ($cotizaciones as $cotId) {
                    $preciosCambiaron = $this->verificarCambioPrecios((int) $cotId);

                    if ($preciosCambiaron) {
                        DB::table('cotizacion')
                            ->where('id', $cotId)
                            ->update(['estado_id' => 2, 'updated_at' => now()]);

                        DB::table('historico_flujo')
                            ->where('flujo_id', $this->flujoId)
                            ->where('tipo_tramite_id', 2)
                            ->where('tramite_id', $cotId)
                            ->update([
                                'observaciones' => 'VencidaPrecios: Factura anulada, precios cambiaron',
                                'updated_at'    => now(),
                            ]);
                    } else {
                        DB::table('cotizacion_estado')->insert([
                            'cotizacion_id' => $cotId,
                            'flujo_id'      => $this->flujoId,
                            'ganadora'      => 2,
                            'comentario'    => 'Oferta activa tras anulación de factura, precios sin cambio',
                            'estado_id'     => 1,
                            'created_by'    => Auth::id(),
                            'updated_by'    => Auth::id(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }

                $this->mensajeExito = 'Factura #' . $facturaId . ' anulada. El flujo volvió a Ofertas con validación de precios.';
            }

            DB::commit();
            $this->confirmAccionFactura = null;
            $this->facturaData          = null;
            $this->saldoPendienteFactura = null;
            $this->emit('pedidoActualizado');
            $this->recargar();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al anular factura: ' . $e->getMessage();
        }
    }

    /**
     * Revierte la prefactura a estado de oferta:
     * inactiva prefactura + historico, retrocede flujo a tipo_tramite_id=2,
     * restaura inventario y desmarca la oferta como ganadora.
     */
    public function revertirPrefacturaAOferta(): void
    {
        if (!$this->prefacturaData || !$this->flujoId) return;

        $prefacturaId = (int) $this->prefacturaData['id'];
        $cotizacionId = (int) ($this->prefacturaData['cotizacion_id'] ?? 0);

        DB::beginTransaction();
        try {
            // 1. Inactivar prefactura
            DB::table('prefactura')
                ->where('id', $prefacturaId)
                ->update(['estado' => 'inactive', 'updated_at' => now()]);

            // 2. estado_id=7 en historico_flujo de la prefactura
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 4)
                ->where('tramite_id', $prefacturaId)
                ->update(['estado_id' => 7, 'updated_at' => now()]);

            // (inventario calculado dinámicamente — no hay decremento que revertir)

            // 4. Marcar oferta como QuitadaGanadora
            if ($cotizacionId) {
                DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 2)
                    ->where('tramite_id', $cotizacionId)
                    ->update(['observaciones' => 'QuitadaGanadora: Revertida a oferta', 'updated_at' => now()]);

                DB::table('cotizacion_estado')->insert([
                    'cotizacion_id' => $cotizacionId,
                    'flujo_id'      => $this->flujoId,
                    'ganadora'      => 2,
                    'comentario'    => 'Revertida a oferta desde prefactura',
                    'estado_id'     => 1,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 5. Retroceder flujo a Ofertas
            DB::table('flujo')->where('id', $this->flujoId)->update([
                'tipo_tramite_id' => 2,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            \App\Models\PrefacturaAuditoria::registrar(
                'reversion_prefactura',
                $prefacturaId,
                null,
                ['estado_prefactura' => 'active', 'cotizacion_id' => $cotizacionId],
                ['estado_prefactura' => 'inactive', 'flujo_paso' => 'ofertas'],
                $this->motivoAutorizacion ?: null,
                $this->autorizacionId
            );

            DB::commit();
            $this->prefacturaData          = null;
            $this->confirmAccionPrefactura = null;
            $this->mostrarAutorizacionPrefactura = false;
            $this->accionAutorizacionPrefactura = null;
            $this->codigoAutorizacion = '';
            $this->autorizacionId = null;
            $this->autorizadorId = null;
            $this->motivoAutorizacion = '';
            $this->mensajeExito = 'Prefactura #' . $prefacturaId . ' revertida. El flujo volvió a Ofertas.';
            $this->emit('pedidoActualizado');
            $this->recargar();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Anula la prefactura: la inactiva + inactiva su registro en historico_flujo
     * y retrocede el flujo a Ofertas. No restaura inventario (stock perdido).
     */
    public function anularPrefactura(): void
    {
        if (!$this->prefacturaData || !$this->flujoId) return;

        $prefacturaId = (int) $this->prefacturaData['id'];
        $cotizacionId = (int) ($this->prefacturaData['cotizacion_id'] ?? 0);

        DB::beginTransaction();
        try {
            DB::table('prefactura')
                ->where('id', $prefacturaId)
                ->update(['estado' => 'inactive', 'updated_at' => now()]);

            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 4)
                ->where('tramite_id', $prefacturaId)
                ->update(['estado_id' => 7, 'updated_at' => now()]);

            if ($cotizacionId) {
                DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 2)
                    ->where('tramite_id', $cotizacionId)
                    ->update(['observaciones' => 'QuitadaGanadora: Prefactura anulada', 'updated_at' => now()]);

                DB::table('cotizacion_estado')->insert([
                    'cotizacion_id' => $cotizacionId,
                    'flujo_id'      => $this->flujoId,
                    'ganadora'      => 2,
                    'comentario'    => 'Prefactura anulada',
                    'estado_id'     => 1,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::table('flujo')->where('id', $this->flujoId)->update([
                'tipo_tramite_id' => 2,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            PrefacturaAuditoria::registrar(
                'anulacion_prefactura',
                $prefacturaId,
                null,
                $this->prefacturaData,
                ['estado' => 'inactive', 'flujo_id' => $this->flujoId],
                $this->motivoAutorizacion,
                $this->autorizacionId
            );

            DB::commit();
            $this->prefacturaData          = null;
            $this->confirmAccionPrefactura = null;
            $this->mostrarAutorizacionPrefactura = false;
            $this->accionAutorizacionPrefactura = null;
            $this->mensajeExito = 'Prefactura #' . $prefacturaId . ' anulada. El flujo volvió a Ofertas.';
            $this->emit('pedidoActualizado');
            $this->recargar();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error: ' . $e->getMessage();
        }
    }

    // ─────────────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────────────
    // FACTURACIÓN DESDE PREFACTURA (pasar prefactura → factura)
    // ─────────────────────────────────────────────────────────────────────

    public $tiposFacturacion  = [];   // tipos disponibles para el cliente
    public $facturacionActiva = false;

    /** Solo redirige a la vista de facturación. El estado del flujo NO cambia aquí. */
    public function iniciarFacturacion(): void
    {
        if (!$this->prefacturaData || !$this->flujoId) return;

        $tipoFactura = DB::table('tipo_factura')
            ->where('estado', 1)
            ->where('codigo', '!=', 'cotizacion_clientes_a')
            ->orderBy('orden')
            ->first(['ruta_menu']);

        if (!$tipoFactura) {
            $this->mensajeError = 'No hay tipos de facturación disponibles.';
            return;
        }

        $prefacturaId = (int) $this->prefacturaData['id'];
        $url = '/' . ltrim($tipoFactura->ruta_menu, '/')
             . '?from=prefactura&prefactura_id=' . $prefacturaId
             . '&flujoId=' . $this->flujoId;

        $this->dispatchBrowserEvent('fmp-redirigir', ['url' => $url]);
    }

    public function cancelarFacturacion(): void
    {
        $this->facturacionActiva = false;
        $this->tiposFacturacion  = [];
        $this->mensajeError      = '';
    }

    // ─────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.flujo.modal-flujo-pedido');
    }
}
