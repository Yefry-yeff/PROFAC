<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Models\PrefacturaAuditoria;
use App\Models\CreditoRevision;
use App\Models\ModelCodigoAutorizacion;
use App\Models\ConfiguracionCodigoAutorizacion;
use App\Models\ModelRecibirBodega;
use App\Models\ModelCliente;
use App\Models\ModelLogTranslados;
use App\Services\Expo\LiquidacionOfertaExpo;
use App\Services\Expo\SaldoLineasOferta;

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
    public $comentarioCreditoGanadora = '';
    public bool $revisionInventarioActiva = false;
    public bool $modalSinExistenciaVisible = false;
    public array $productosSinExistenciaModal = [];
    public string $motivoEdicionSinExistencia = '';
    public string $mensajeErrorSinExistencia = '';

    // ── Revisión de Crédito ───────────────────────────────────────────────
    public array  $creditoRevisionData   = [];    // datos del registro credito_revision activo
    public bool   $creditoVigente        = false; // true si hay aprobación no vencida

    // ── Revisión de Inventario: historial de ciclos ──────────────────────────────────────
    public array $revisionHistorial = [];   // ciclos con datos de revisor/aprobador
    public bool  $revisionDevuelta  = false; // true si el último ciclo fue devuelto a oferta

    // ── Revisión de Crédito: estado del ciclo activo ──────────────────────
    public bool $revisionCreditoPendiente = false; // hay un historico_flujo tipo=10 con estado_id=5
    public bool $flujoCancelado          = false; // el flujo fue cancelado por rechazo de crédito

    // ── Selector de cliente para "Otro cliente" al duplicar ──────────────
    public bool   $mostrarSelectorClienteDuplicar  = false;
    public string $busquedaClienteDuplicar         = '';
    public array  $resultadosClienteDuplicar       = [];
    public ?int   $clienteDuplicarId               = null;
    public string $clienteDuplicarNombre           = '';
    public string $clienteDuplicarError            = '';
    public array  $clienteDuplicarEscalaConflicto  = [];  // [['nombre'=>..,'categoria'=>..], ...]
    public array  $productosPrecioEscalaCambiado   = [];
    public bool   $preciosCambioMostrado          = false;  // warning visible → user can confirm
    public bool   $duplicarMismoClienteAlmacenado = false;  // stored choice for confirm

    // ── Acciones sobre el pedido ──────────────────────────────────────────
    public $confirmAccion    = null;  // null|'anular'|'duplicar'
    public $motivoAnulacion  = '';

    // ── Mensajes ──────────────────────────────────────────────────────────
    public $mensajeExito = '';
    public $mensajeError = '';

    // ── Prefactura del flujo activo ───────────────────────────────────────
    public $prefacturaData          = null;
    public array $prefacturasData   = [];
    public $stockErrors             = [];  // errores de inventario al crear prefactura
    public $confirmAccionPrefactura = null; // null | 'revertir' | 'anular'
    public $vencimientoProcesado    = false; // true cuando se procesó el vencimiento en esta carga
    public bool $prefacturaVencida       = false;
    public bool $prefacturaPuedeFacturar = true;
    public array $prefacturaStockFaltante = [];
    public bool $prefacturaReservaCompleta = true;
    public array $prefacturaReservaFaltante = [];
    public bool $expoConSaldoPendiente = false;
    public $mostrarAutorizacionPrefactura = false;
    public $accionAutorizacionPrefactura  = null;
    public $codigoAutorizacion            = '';
    public $autorizacionId                = null;
    public $autorizadorId                 = null;
    public $motivoAutorizacion            = '';

    // ── Factura del flujo activo ─────────────────────────────────────────
    public $facturaData          = null;
    public array $facturasData   = [];
    public array $notasCreditoData = [];
    public ?array $liquidacionExpoPendiente = null;
    public ?int $facturaSeleccionadaId = null;
    public $confirmAccionFactura = null; // null | 'anular'
    public $motivoAnulacionFactura = '';
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
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'c.categoria_precios_id')
            ->select(
                'p.id', 'p.estado', 'p.observaciones', 'p.created_at',
                'c.nombre as cliente', 'c.rtn', 'c.id as cliente_id',
                'u.name as registrado_por',
                'cp.nombre as nombre_escala',
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
            10 => 'revision_credito',     // Revisión de Crédito (nuevo)
            9  => 'revision_inventario',   // Revision de Inventario
            4  => 'prefactura',
            3  => 'factura',
            5  => 'entrega',
            8  => 'finalizado',
            'pedido'                 => 'pedido',
            'Ofertas'                => 'ofertas',
            'Revision de Credito'    => 'revision_credito',
            'Revision de Inventario' => 'revision_inventario',
            'prefactura'             => 'prefactura',
            'factura'                => 'factura',
            'finalizado'             => 'finalizado',
            'Entrega Cobro'          => 'entrega',
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
                ->where(function ($q) {
                    // Incluir activos + registros de revisión aunque estén inactivados (devueltos)
                    $q->where('estado_id', '!=', 7)
                      ->orWhereIn('tipo_tramite_id', [9, 10]);
                })
                ->pluck('tipo_tramite_id')
                ->unique()
                ->values()
                ->toArray()
            : [];

        $this->cargarEstadosEntregaCobro();
        $this->cargarRevisionHistorial();
        $this->cargarOfertasPedido();

        // Config revisión de inventario
        $cfgRev = DB::table('configuracion_revision_inventario')->first();
        $this->revisionInventarioActiva = $cfgRev && (bool) $cfgRev->activo;

        // Crédito vigente para este flujo
        $this->creditoVigente = $this->flujoId
            ? CreditoRevision::creditoVigenteParaFlujo($this->flujoId)
            : false;
        $cr = $this->flujoId ? CreditoRevision::paraFlujo($this->flujoId) : null;
        $this->creditoRevisionData = $this->buildCreditoData($cr);
        $this->flujoCancelado = ($this->creditoRevisionData['estado'] ?? '') === CreditoRevision::RECHAZADO;
        // Detectar si hay un ciclo de Revisión de Crédito pendiente en historico_flujo
        $this->revisionCreditoPendiente = $this->flujoId
            ? DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 10)
                ->where('estado_id', 5)
                ->exists()
            : false;

        // Resetear estado
        $this->pasoActivo              = $pasoFinal;
        $this->ofertaSeleccionada      = null;
        $this->confirmAccion           = null;
        $this->confirmAccionOferta     = null;
        $this->confirmAccionPrefactura = null;
        $this->motivoAnulacion         = '';
        $this->motivoAnulOferta        = '';
        $this->comentarioCreditoGanadora = '';
        $this->mensajeExito            = '';
        $this->mensajeError            = '';
        $this->stockErrors             = [];
        $this->prefacturaData          = null;
        $this->facturaData             = null;
        $this->notasCreditoData        = [];
        $this->confirmAccionFactura    = null;
        if ($pasoFinal === 'prefactura') {
            $this->cargarPrefactura();
        }
        if ($pasoFinal === 'factura') {
            $this->cargarFactura();
        } elseif (in_array(3, $this->flujoTipos) || in_array(5, $this->flujoTipos)) {
            $this->cargarFactura(); // cargar para detectar estado anulada en stepper
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
        $this->prefacturaReservaCompleta = true;
        $this->prefacturaReservaFaltante = [];

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
        // flujo.nombre tiene prioridad (fue actualizado al confirmar la factura)
        $clienteNombre = $flujo->nombre
            ?? ($cotizacion ? ($cotizacion->nombre_cliente ?? null) : null)
            ?? ($facturaDirecta ? ($facturaDirecta->nombre_cliente ?? null) : null)
            ?? '—';
        $clienteRtn = $flujo->cliente_rtn
            ?? ($cotizacion ? ($cotizacion->RTN ?? null) : null)
            ?? ($facturaDirecta ? ($facturaDirecta->rtn ?? null) : null);

        $this->pedidoData = [
            'id'             => (int) $flujo->identificacion,
            'flujo_id'       => (int) $flujoId,
            'estado'         => 'activo',
            'observaciones'  => $cotizacion ? ($cotizacion->observaciones ?? null) : null,
            'created_at'     => $cotizacion ? $cotizacion->created_at : ($facturaDirecta ? $facturaDirecta->created_at : $flujo->created_at),
            'cliente'        => $clienteNombre,
            'rtn'            => $clienteRtn,
            'cliente_id'     => $cotizacion ? ($cotizacion->cliente_id ?? null) : ($facturaDirecta ? ($facturaDirecta->cliente_id ?? null) : null),
            'registrado_por' => null,
            'total_ofertas'  => $totalOfertas,
            'has_ganadora'   => $hasGanadora,
            'sin_pedido'     => true,   // ← indica que no tiene pedido vinculado
        ];

        $this->pedidoDetalles = [];
        $this->flujoId        = $flujoId;

        $this->flujoTipos = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where(function ($q) {
                // Incluir activos + registros de revisión aunque estén inactivados (devueltos)
                $q->where('estado_id', '!=', 7)
                  ->orWhereIn('tipo_tramite_id', [9, 10]);
            })
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
            10 => 'revision_credito',      // Revisión de Crédito (nuevo)
            9  => 'revision_inventario',   // Revision de Inventario
            4  => 'prefactura',
            3  => 'factura',
            5  => 'entrega',
            6  => 'cobro',
            7  => 'entrega',    // Flujo conjunto (Entrega + Cobro)
            8  => 'finalizado',
            'pedido'                 => 'ofertas',   // sin pedido, arrancamos en ofertas
            'Ofertas'                => 'ofertas',
            'Revision de Credito'    => 'revision_credito',
            'Revision de Inventario' => 'revision_inventario',
            'prefactura'             => 'prefactura',
            'factura'                => 'factura',
            'finalizado'             => 'finalizado',
            'Entrega Cobro'          => 'entrega',
        ];

        $this->cargarRevisionHistorial();
        $this->cargarOfertasPedido();

        // Config revisión de inventario
        $cfgRev = DB::table('configuracion_revision_inventario')->first();
        $this->revisionInventarioActiva = $cfgRev && (bool) $cfgRev->activo;

        // Crédito vigente
        $this->creditoVigente = CreditoRevision::creditoVigenteParaFlujo($flujoId);
        $cr = CreditoRevision::paraFlujo($flujoId);
        $this->creditoRevisionData = $this->buildCreditoData($cr);
        $this->flujoCancelado = ($this->creditoRevisionData['estado'] ?? '') === CreditoRevision::RECHAZADO;
        // Detectar si hay un ciclo de Revisión de Crédito pendiente en historico_flujo
        $this->revisionCreditoPendiente = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 10)
            ->where('estado_id', 5)
            ->exists();

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
        $this->comentarioCreditoGanadora = '';
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
        } elseif (in_array(3, $this->flujoTipos) || in_array(5, $this->flujoTipos)) {
            $this->cargarFactura(); // cargar para detectar estado anulada en stepper
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

    /** Convierte un registro CreditoRevision en array con nombre del revisor incluido. */
    private function buildCreditoData(?CreditoRevision $cr): array
    {
        if (!$cr) return [];
        $data = $cr->toArray();
        if ($cr->usuario_revision) {
            $data['usuario_revision_nombre'] = DB::table('users')
                ->where('id', $cr->usuario_revision)
                ->value('name') ?? '—';
        } else {
            $data['usuario_revision_nombre'] = '—';
        }
        return $data;
    }

    private function diasVigenciaPrefactura(int $flujoId): int
    {
        $credito = DB::table('credito_revision')
            ->where('flujo_id', $flujoId)
            ->where('estado', CreditoRevision::APROBADO)
            ->latest('id')
            ->first(['dias_credito_aprobados', 'fecha_aprobacion', 'fecha_vencimiento_credito']);

        if ($credito && !is_null($credito->dias_credito_aprobados)) {
            return max(0, (int) $credito->dias_credito_aprobados);
        }

        if ($credito && $credito->fecha_aprobacion && $credito->fecha_vencimiento_credito) {
            return max(0, (int) \Carbon\Carbon::parse($credito->fecha_aprobacion)
                ->diffInDays(\Carbon\Carbon::parse($credito->fecha_vencimiento_credito), false));
        }

        return max(0, (int) (DB::table('configuracion_prefactura')
            ->orderByDesc('id')->value('dias_validez') ?? 7));
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
        $this->comentarioCreditoGanadora = '';
        $this->mensajeExito            = '';
        $this->mensajeError            = '';
        $this->prefacturaData          = null;
        $this->prefacturaVencida       = false;
        $this->prefacturaPuedeFacturar = true;
        $this->prefacturaStockFaltante = [];
        $this->stockErrors             = [];
        $this->confirmAccionPrefactura = null;
        $this->facturaData             = null;
        $this->confirmAccionFactura    = null;
        $this->motivoAnulacionFactura  = '';
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
        $this->revisionHistorial       = [];
        $this->revisionDevuelta        = false;
        $this->creditoRevisionData     = [];
        $this->creditoVigente          = false;
        $this->revisionCreditoPendiente = false;
        $this->flujoCancelado          = false;
        $this->mostrarSelectorClienteDuplicar  = false;
        $this->busquedaClienteDuplicar         = '';
        $this->resultadosClienteDuplicar       = [];
        $this->clienteDuplicarId               = null;
        $this->clienteDuplicarNombre           = '';
        $this->clienteDuplicarError            = '';
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
        $this->comentarioCreditoGanadora = '';
        $this->mensajeExito         = '';
        $this->mensajeError         = '';

        if ($paso === 'ofertas') {
            $this->cargarOfertasPedido();
        }
        if ($paso === 'revision_credito') {
            // Solo lectura en el modal: recargar datos del crédito
            if ($this->flujoId) {
                $cr = CreditoRevision::paraFlujo($this->flujoId);
                $this->creditoRevisionData      = $this->buildCreditoData($cr);
                $this->creditoVigente           = CreditoRevision::creditoVigenteParaFlujo($this->flujoId);
                $this->revisionCreditoPendiente = DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 10)
                    ->where('estado_id', 5)
                    ->exists();
            }
        }
        if ($paso === 'revision_inventario') {
            $this->cargarRevisionHistorial();
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
        $this->confirmAccion                 = $accion;
        $this->motivoAnulacion               = '';
        $this->mensajeError                  = '';
        $this->productosPrecioEscalaCambiado = [];
        $this->preciosCambioMostrado         = false;

        if ($accion === 'duplicar') {
            $this->cargarPreciosDuplicarPedido();
        }
    }

    public function cancelarConfirmacion(): void
    {
        $this->confirmAccion                 = null;
        $this->motivoAnulacion               = '';
        $this->mensajeError                  = '';
        $this->productosPrecioEscalaCambiado = [];
        $this->preciosCambioMostrado         = false;
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

        $facturaCompletada = in_array(3, $this->flujoTipos) || in_array(5, $this->flujoTipos);

        $productos = DB::table('pedido_detalle')
            ->where('pedido_id', $pedidoId)
            ->get(['nombre_producto', 'cantidad'])
            ->map(fn($r) => [
                'nombre_producto' => $r->nombre_producto,
                'cantidad'        => $r->cantidad,
            ])
            ->toArray();

        $param = base64_encode(json_encode($productos));

        if ($facturaCompletada) {
            // Nuevo flujo: abrir formulario de pedido (crea nuevo pedido → nuevo flujo)
            $url = route('flujo.pedido') . '?productos=' . urlencode($param)
                 . '&cliente_id=' . $this->pedidoData['cliente_id'];
        } else {
            // Mismo flujo: abrir formulario de oferta vinculado al pedido actual
            $url = route('flujo.oferta') . '?pedido_id=' . $pedidoId
                 . '&productos=' . urlencode($param);
        }

        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
        $this->cerrar();
    }

    /**
     * Compara los precios del último/ganador cotizacion del pedido
     * contra los precios de la escala actual del cliente.
     * Llena $productosPrecioEscalaCambiado con las diferencias encontradas.
     */
    private function cargarPreciosDuplicarPedido(): void
    {

        $clienteId        = (int) $this->pedidoData['cliente_id'];
        $nuevaCategoriaId = DB::table('cliente')
            ->where('id', $clienteId)
            ->value('categoria_precios_id');

        if (!$nuevaCategoriaId) return;

        // Obtener la cotizacion ganadora o la más reciente del flujo
        $cotizacion = DB::table('historico_flujo as hf')
            ->join('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->where('hf.tipo_tramite_id', 2)
            ->orderByRaw("CASE WHEN hf.observaciones = 'ganadora' THEN 0 ELSE 1 END")
            ->orderByDesc('hf.id')
            ->select('c.id as cotizacion_id')
            ->first();

        if (!$cotizacion) return;

        // Productos de esa cotizacion con su precio original
        $productos = DB::table('cotizacion_has_producto as chp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->where('chp.cotizacion_id', $cotizacion->cotizacion_id)
            ->select(
                'chp.nombre_producto',
                'chp.precio_unidad as precio_original',
                'chp.idPrecioSeleccionado',
                'ppc.producto_id',
                'ppc.categoria_precios_id as categoria_orig_id'
            )
            ->get();

        $diferencias = [];
        foreach ($productos as $prod) {
            if (!$prod->producto_id) continue;

            // Buscar el precio para este producto en la escala actual del cliente
            $ppcNuevo = DB::table('precios_producto_carga')
                ->where('categoria_precios_id', $nuevaCategoriaId)
                ->where('producto_id', $prod->producto_id)
                ->first(['precio_a', 'precio_b', 'precio_c', 'precio_d', 'precio_base_venta']);

            if (!$ppcNuevo) continue;

            $col = strtolower(trim($prod->idPrecioSeleccionado ?? ''));
            $precioNuevo = match($col) {
                'p1', 'a' => $ppcNuevo->precio_a,
                'p2', 'b' => $ppcNuevo->precio_b,
                'p3', 'c' => $ppcNuevo->precio_c,
                'p4', 'd' => $ppcNuevo->precio_d,
                default   => $ppcNuevo->precio_base_venta,
            };

            $precioOrig  = round((float) $prod->precio_original, 4);
            $precioNuevo = round((float) ($precioNuevo ?? 0), 4);

            if (abs($precioOrig - $precioNuevo) > 0.001) {
                $diferencias[] = [
                    'nombre'          => $prod->nombre_producto,
                    'precio_original' => $precioOrig,
                    'precio_nuevo'    => $precioNuevo,
                ];
            }
        }

        $this->productosPrecioEscalaCambiado = $diferencias;
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
                'c.estado_id as cotizacion_estado_id',   // 1=activo, 2=inactivo por precios
                DB::raw("(SELECT cce.nombre_categoria
                          FROM cotizacion_has_producto chp
                          INNER JOIN precios_producto_carga ppc ON ppc.id = chp.precios_producto_carga_id
                          INNER JOIN categoria_precios cp ON cp.id = ppc.categoria_precios_id
                          INNER JOIN cliente_categoria_escala cce ON cce.id = cp.cliente_categoria_escala_id
                          WHERE chp.cotizacion_id = c.id
                          LIMIT 1) as categoria_producto_nombre")
            )
            ->orderByDesc('hf.id')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    /**
     * Carga el historial de ciclos de Revisión de Inventario para el flujo activo.
     * Incluye nombre del revisor (created_by) y del autorizador (updated_by).
     */
    private function cargarRevisionHistorial(): void
    {
        if (!$this->flujoId) {
            $this->revisionHistorial = [];
            $this->revisionDevuelta  = false;
            return;
        }

        $records = DB::table('historico_flujo as hf')
            ->leftJoin('users as rev', 'rev.id', '=', 'hf.created_by')
            ->leftJoin('users as apr', 'apr.id', '=', 'hf.updated_by')
            ->where('hf.flujo_id', $this->flujoId)
            ->where('hf.tipo_tramite_id', 9)
            ->orderBy('hf.id')
            ->select(
                'hf.id',
                'hf.estado_id',
                'hf.observaciones',
                'hf.tramite_id',
                'hf.created_at',
                'hf.updated_at',
                'hf.created_by',
                'hf.updated_by',
                'rev.name as revisor_nombre',
                'apr.name as aprobador_nombre'
            )
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->revisionHistorial = $records;

        // Devuelta si el ciclo más reciente tiene estado_id = 7 (inactivado/devuelto)
        $ultima = collect($records)->sortByDesc('id')->first();
        $this->revisionDevuelta = $ultima !== null && (int) ($ultima['estado_id'] ?? 0) === 7;
    }

    public function verOferta(int $cotizacionId): void
    {
        try {
            $row = DB::table('cotizacion as c')
                ->leftJoin('historico_flujo as hf', function ($j) {
                    $j->on('hf.tramite_id', '=', 'c.id')
                      ->where('hf.tipo_tramite_id', 2);
                })
                ->where('c.id', $cotizacionId)
                ->select(
                    'c.id', 'c.nombre_cliente', 'c.RTN', 'c.total', 'c.isv',
                    'c.estado_id as cotizacion_estado_id',
                    'hf.observaciones as hf_observaciones'
                )
                ->orderByDesc('hf.id')
                ->first();

            if (!$row) {
                $this->mensajeError = 'No se encontró la oferta #' . $cotizacionId . '.';
                return;
            }

            $productos = DB::table('cotizacion_has_producto as chp')
                ->leftJoin('seccion as s', 's.id', '=', 'chp.seccion_id')
                ->leftJoin('segmento as sg', 'sg.id', '=', 's.segmento_id')
                ->leftJoin('bodega as b', 'b.id', '=', 'sg.bodega_id')
                ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
                ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
                ->where('chp.cotizacion_id', $cotizacionId)
                ->select(
                    'chp.indice', 'chp.producto_id', 'chp.nombre_producto', 'chp.cantidad', 'chp.precio_unidad', 'chp.total',
                    'chp.bodega_id', 'chp.seccion_id', 'chp.nombre_bodega', 'chp.resta_inventario',
                    'b.nombre as bodega_actual_nombre', 's.descripcion as seccion_actual_descripcion',
                    'chp.idPrecioSeleccionado', 'chp.precios_producto_carga_id',
                    'cp.nombre as nombre_categoria_precio',
                    // Escala Selec.: precio_a del ppc vinculado al momento de la oferta
                    'ppc.precio_a as precio_escala_seleccionada',
                    // Escala Act.: precio_a vigente (estado_id=1) para la misma categoria+producto
                    DB::raw("(SELECT ppc2.precio_a
                              FROM precios_producto_carga ppc2
                              WHERE ppc2.categoria_precios_id = ppc.categoria_precios_id
                                AND ppc2.producto_id          = ppc.producto_id
                                AND ppc2.estado_id            = 1
                              LIMIT 1) as precio_escala_actual")
                )
                ->get()
                ->map(fn($r) => (array) $r)
                ->toArray();

            $productoIds = collect($productos)
                ->pluck('producto_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $stockGlobalPorProducto = [];
            if (!empty($productoIds)) {
                $stockGlobalPorProducto = DB::table('recibido_bodega')
                    ->whereIn('producto_id', $productoIds)
                    ->where('cantidad_disponible', '>', 0)
                    ->select('producto_id', DB::raw('SUM(cantidad_disponible) as stock_total'))
                    ->groupBy('producto_id')
                    ->pluck('stock_total', 'producto_id')
                    ->map(fn ($stock) => (float) $stock)
                    ->toArray();
            }

            $productos = collect($productos)
                ->map(function (array $prod) use ($stockGlobalPorProducto) {
                    $productoId = (int) ($prod['producto_id'] ?? 0);
                    $stockGlobal = (float) ($stockGlobalPorProducto[$productoId] ?? 0);
                    $nombreBodegaLinea = strtoupper(trim((string) ($prod['nombre_bodega'] ?? '')));
                    $sinExistenciaLinea = !((float) ($prod['resta_inventario'] ?? 0) > 0)
                        || str_contains($nombreBodegaLinea, 'SIN EXISTENCIA');
                    $prod['stock_total_global'] = $stockGlobal;
                    $prod['sin_existencia_global'] = $stockGlobal <= 0;
                    $prod['sin_existencia_linea'] = $sinExistenciaLinea;
                    return $prod;
                })
                ->toArray();

            $this->ofertaSeleccionada  = array_merge((array) $row, ['productos' => $productos]);
            $this->pasoActivo          = 'ofertas';
            $this->confirmAccionOferta = null;
            $this->motivoAnulOferta    = '';
            $this->modalSinExistenciaVisible = false;
            $this->productosSinExistenciaModal = [];
            $this->motivoEdicionSinExistencia = '';
            $this->mensajeExito        = '';
            $this->mensajeError        = '';
        } catch (\Throwable $e) {
            $this->mensajeError = 'Error al cargar la oferta: ' . $e->getMessage();
            \Illuminate\Support\Facades\Log::error('verOferta error', [
                'cotizacion_id' => $cotizacionId,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    public function cerrarOferta(): void
    {
        $this->ofertaSeleccionada  = null;
        $this->confirmAccionOferta = null;
        $this->motivoAnulOferta    = '';
        $this->modalSinExistenciaVisible = false;
        $this->productosSinExistenciaModal = [];
        $this->motivoEdicionSinExistencia = '';
        $this->mensajeExito        = '';
        $this->mensajeError        = '';
    }

    public function abrirEdicionProductosSinExistencia(): void
    {
        $this->mensajeErrorSinExistencia = '';

        if (!$this->ofertaSeleccionada || empty($this->ofertaSeleccionada['id'])) {
            $this->mensajeErrorSinExistencia = 'No hay una oferta seleccionada.';
            return;
        }

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];
        $sinExistencia = collect($this->ofertaSeleccionada['productos'] ?? [])
            ->filter(fn (array $prod) => (bool) ($prod['sin_existencia_linea'] ?? false))
            ->values();

        if ($sinExistencia->isEmpty()) {
            $this->mensajeErrorSinExistencia = 'La oferta no tiene productos marcados como sin existencia.';
            return;
        }

        $productoIds = $sinExistencia->pluck('producto_id')->filter()->unique()->values()->all();
        $destinosPorProducto = $this->obtenerDestinosDisponiblesPorProducto($productoIds);

        $this->productosSinExistenciaModal = $sinExistencia->map(function (array $prod) use ($destinosPorProducto) {
            $destinos = $destinosPorProducto[$prod['producto_id']] ?? [];
            return [
                'indice' => $prod['indice'] ?? null,
                'producto_id' => $prod['producto_id'] ?? null,
                'nombre_producto' => $prod['nombre_producto'] ?? 'Producto',
                'cantidad' => $prod['cantidad'] ?? 0,
                'bodega_actual_id' => $prod['bodega_id'] ?? null,
                'bodega_actual_nombre' => $prod['bodega_actual_nombre'] ?? ($prod['nombre_bodega'] ?? 'SIN EXISTENCIA'),
                'seccion_actual_id' => $prod['seccion_id'] ?? null,
                'seccion_actual_descripcion' => $prod['seccion_actual_descripcion'] ?? null,
                'destino_seleccionado' => '',
                'destinos' => $destinos,
            ];
        })->toArray();

        $this->motivoEdicionSinExistencia = '';
        $this->modalSinExistenciaVisible = true;
        $this->dispatchBrowserEvent('modal-sin-existencia-show');
    }

    public function guardarEdicionProductosSinExistencia(): void
    {
        $this->mensajeErrorSinExistencia = '';

        if (!$this->ofertaSeleccionada || empty($this->ofertaSeleccionada['id'])) {
            $this->mensajeErrorSinExistencia = 'No hay una oferta seleccionada.';
            return;
        }

        if (empty($this->productosSinExistenciaModal)) {
            $this->mensajeErrorSinExistencia = 'No hay productos para actualizar.';
            return;
        }

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];
        $motivo = trim($this->motivoEdicionSinExistencia);
        $actualizados = 0;
        $historialDisponible = Schema::hasTable('historico_cotizacion_producto_sin_existencia');
        if (!$historialDisponible) {
            Log::warning('No existe la tabla historico_cotizacion_producto_sin_existencia. Se omite auditoria de edicion sin existencia.', [
                'cotizacion_id' => $cotizacionId,
                'user_id' => Auth::id(),
            ]);
        }

        // Validar que la cantidad total solicitada por destino no exceda el stock disponible.
        $demandaPorDestino = [];
        foreach ($this->productosSinExistenciaModal as $linea) {
            $seleccion = trim((string) ($linea['destino_seleccionado'] ?? ''));
            if ($seleccion === '') {
                continue;
            }

            [$bodegaDestinoId, $seccionDestinoId] = array_pad(explode('|', $seleccion, 2), 2, null);
            $bodegaDestinoId = (int) $bodegaDestinoId;
            $seccionDestinoId = (int) $seccionDestinoId;
            if ($bodegaDestinoId <= 0 || $seccionDestinoId <= 0) {
                continue;
            }

            $opcionDestino = collect($linea['destinos'] ?? [])->firstWhere('value', $seleccion);
            if (!$opcionDestino) {
                continue;
            }

            $productoId = (int) ($linea['producto_id'] ?? 0);
            $cantidadSolicitada = (float) ($linea['cantidad'] ?? 0);
            $stockDisponible = (float) ($opcionDestino['stock'] ?? 0);
            $destinoTexto = (string) ($opcionDestino['text'] ?? 'Destino');
            $key = $productoId . '|' . $bodegaDestinoId . '|' . $seccionDestinoId;

            if (!isset($demandaPorDestino[$key])) {
                $demandaPorDestino[$key] = [
                    'solicitado' => 0.0,
                    'stock' => $stockDisponible,
                    'producto' => (string) ($linea['nombre_producto'] ?? 'Producto'),
                    'destino' => $destinoTexto,
                ];
            }

            $demandaPorDestino[$key]['solicitado'] += $cantidadSolicitada;
            if ($demandaPorDestino[$key]['solicitado'] > $demandaPorDestino[$key]['stock']) {
                $this->mensajeErrorSinExistencia = 'No se puede realizar la edición. La cantidad solicitada para '
                    . $demandaPorDestino[$key]['producto']
                    . ' supera el stock acumulado en '
                    . $demandaPorDestino[$key]['destino']
                    . ' (Solicitado: ' . (int) $demandaPorDestino[$key]['solicitado']
                    . ', Disponible: ' . (int) $demandaPorDestino[$key]['stock'] . ').';
                return;
            }
        }

        DB::beginTransaction();
        try {
            foreach ($this->productosSinExistenciaModal as $linea) {
                $seleccion = trim((string) ($linea['destino_seleccionado'] ?? ''));
                if ($seleccion === '') {
                    continue;
                }

                [$bodegaDestinoId, $seccionDestinoId] = array_pad(explode('|', $seleccion, 2), 2, null);
                $bodegaDestinoId = (int) $bodegaDestinoId;
                $seccionDestinoId = (int) $seccionDestinoId;
                if ($bodegaDestinoId <= 0 || $seccionDestinoId <= 0) {
                    continue;
                }

                $opcionDestino = collect($linea['destinos'] ?? [])->firstWhere('value', $seleccion);
                if (!$opcionDestino) {
                    continue;
                }

                $nombreBodegaDestino = (string) ($opcionDestino['bodega_nombre'] ?? '');

                $afectados = DB::table('cotizacion_has_producto')
                    ->where('cotizacion_id', $cotizacionId)
                    ->where('indice', (int) ($linea['indice'] ?? 0))
                    ->update([
                        'Bodega_id' => $bodegaDestinoId,
                        'seccion_id' => $seccionDestinoId,
                        'nombre_bodega' => $nombreBodegaDestino,
                        'resta_inventario' => 1,
                        'updated_at' => now(),
                    ]);

                if ($afectados > 0) {
                    if ($historialDisponible) {
                        DB::table('historico_cotizacion_producto_sin_existencia')->insert([
                            'id_cotizacion' => $cotizacionId,
                            'id_producto' => (int) ($linea['producto_id'] ?? 0),
                            'indice_linea' => (int) ($linea['indice'] ?? 0),
                            'nombre_producto' => $linea['nombre_producto'] ?? null,
                            'id_bodega_origen' => (int) ($linea['bodega_actual_id'] ?? 0) ?: null,
                            'id_seccion_origen' => (int) ($linea['seccion_actual_id'] ?? 0) ?: null,
                            'id_bodega_actualizacion' => $bodegaDestinoId,
                            'id_seccion_actualizacion' => $seccionDestinoId,
                            'nombre_bodega_origen' => $linea['bodega_actual_nombre'] ?? 'SIN EXISTENCIA',
                            'nombre_bodega_destino' => $nombreBodegaDestino,
                            'motivo' => $motivo !== '' ? $motivo : 'Reasignación de producto sin existencia',
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $actualizados++;
                }
            }

            if ($actualizados === 0) {
                DB::rollBack();
                $this->mensajeErrorSinExistencia = 'Seleccione al menos un destino válido para actualizar.';
                return;
            }

            DB::commit();

            $this->modalSinExistenciaVisible = false;
            $this->productosSinExistenciaModal = [];
            $this->motivoEdicionSinExistencia = '';
            $this->mensajeErrorSinExistencia = '';
            $this->dispatchBrowserEvent('modal-sin-existencia-hide');

            $this->verOferta($cotizacionId);
            $this->mensajeExito = 'Se actualizaron ' . $actualizados . ' producto(s) sin existencia.';
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensajeErrorSinExistencia = 'No se pudo actualizar la relación de productos sin existencia: ' . $e->getMessage();
        }
    }

    /**
     * @param array<int> $productoIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function obtenerDestinosDisponiblesPorProducto(array $productoIds): array
    {
        if (empty($productoIds)) {
            return [];
        }

        $rows = DB::table('recibido_bodega as rb')
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->whereIn('rb.producto_id', $productoIds)
            ->where('rb.cantidad_disponible', '>', 0)
            ->select(
                'rb.producto_id',
                'sg.bodega_id',
                's.id as seccion_id',
                'b.nombre as bodega_nombre',
                's.descripcion as seccion_descripcion',
                DB::raw('SUM(rb.cantidad_disponible) as stock')
            )
            ->groupBy('rb.producto_id', 'sg.bodega_id', 's.id', 'b.nombre', 's.descripcion')
            ->orderBy('b.nombre')
            ->orderBy('s.descripcion')
            ->get();

        $destinos = [];
        foreach ($rows as $row) {
            $destinos[(int) $row->producto_id][] = [
                'value' => (int) $row->bodega_id . '|' . (int) $row->seccion_id,
                'bodega_id' => (int) $row->bodega_id,
                'seccion_id' => (int) $row->seccion_id,
                'bodega_nombre' => (string) $row->bodega_nombre,
                'seccion_descripcion' => (string) $row->seccion_descripcion,
                'stock' => (float) $row->stock,
                'text' => trim((string) $row->bodega_nombre . ' - ' . (string) $row->seccion_descripcion . ' (Stock: ' . (int) $row->stock . ')'),
            ];
        }

        return $destinos;
    }

    public function confirmarAccionOferta(string $accion): void
    {
        $this->confirmAccionOferta             = $accion;
        $this->motivoAnulOferta                = '';
        $this->comentarioCreditoGanadora       = '';
        $this->mensajeError                    = '';
        $this->clienteDuplicarError            = '';
        $this->productosPrecioEscalaCambiado   = [];
        $this->preciosCambioMostrado           = false;
        $this->duplicarMismoClienteAlmacenado  = false;
        if (in_array($accion, ['anular_oferta', 'quitar_ganadora'])) {
            $this->dispatchBrowserEvent('focus-motivo-oferta');
        }
    }

    public function cancelarConfirmOferta(): void
    {
        $this->confirmAccionOferta             = null;
        $this->motivoAnulOferta                = '';
        $this->comentarioCreditoGanadora       = '';
        $this->mensajeError                    = '';
        $this->mostrarSelectorClienteDuplicar  = false;
        $this->busquedaClienteDuplicar         = '';
        $this->resultadosClienteDuplicar       = [];
        $this->clienteDuplicarId               = null;
        $this->clienteDuplicarNombre           = '';
        $this->clienteDuplicarError            = '';
        $this->clienteDuplicarEscalaConflicto  = [];
        $this->productosPrecioEscalaCambiado   = [];
        $this->preciosCambioMostrado           = false;
        $this->duplicarMismoClienteAlmacenado  = false;
    }

    // ─────────────────────────────────────────────────────────────────────
    // DUPLICAR OFERTA → OTRO CLIENTE
    // ─────────────────────────────────────────────────────────────────────

    public function iniciarDuplicarOtroCliente(): void
    {
        $this->mostrarSelectorClienteDuplicar  = true;
        $this->busquedaClienteDuplicar         = '';
        $this->resultadosClienteDuplicar       = [];
        $this->clienteDuplicarId               = null;
        $this->clienteDuplicarNombre           = '';
        $this->clienteDuplicarError            = '';
        $this->clienteDuplicarEscalaConflicto  = [];
        $this->mensajeError                    = '';
        $this->productosPrecioEscalaCambiado   = [];
    }

    public function updatedBusquedaClienteDuplicar(): void
    {
        $term = trim($this->busquedaClienteDuplicar);
        if (strlen($term) < 2) {
            $this->resultadosClienteDuplicar = [];
            return;
        }

        $like  = '%' . $term . '%';
        $rolId = Auth::user()->rol_id;

        $query = DB::table('cliente')
            ->where('estado_cliente_id', 1)
            ->where('id', '!=', 1)
            ->where(function ($q) use ($like) {
                $q->where('id', 'LIKE', $like)
                  ->orWhere('nombre', 'LIKE', $like);
            });

        // Solo Admin (1) ve todos los clientes; los demás solo sus asignados.
        if ($rolId !== 1) {
            $query->where(function ($access) {
                $access->where('cliente.vendedor', Auth::id())
                    ->orWhereExists(function ($assigned) {
                        $assigned->select(DB::raw(1))
                            ->from('cliente_usuario as cu')
                            ->whereColumn('cu.cliente_id', 'cliente.id')
                            ->where('cu.usuario_id', Auth::id())
                            ->whereIn('cu.rol_id', [2, 3]);
                    });
            });
        }

        $this->resultadosClienteDuplicar = $query
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->limit(15)
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre])
            ->toArray();
    }

    public function seleccionarClienteDuplicar(int $clienteId, string $clienteNombre): void
    {
        $this->clienteDuplicarId               = $clienteId;
        $this->clienteDuplicarNombre           = $clienteNombre;
        $this->busquedaClienteDuplicar         = $clienteNombre;
        $this->resultadosClienteDuplicar       = [];
        $this->clienteDuplicarError            = '';
        $this->clienteDuplicarEscalaConflicto  = [];
        $this->productosPrecioEscalaCambiado   = [];
    }

    public function confirmarDuplicarOtroCliente(): void
    {
        if (!$this->clienteDuplicarId) {
            $this->clienteDuplicarError = 'Debe seleccionar un cliente.';
            return;
        }

        $clientePermitido = DB::table('cliente')
            ->where('cliente.id', $this->clienteDuplicarId)
            ->where('cliente.estado_cliente_id', 1)
            ->where('cliente.id', '!=', 1);

        if ((int) Auth::user()->rol_id !== 1) {
            $clientePermitido->where(function ($access) {
                $access->where('cliente.vendedor', Auth::id())
                    ->orWhereExists(function ($assigned) {
                        $assigned->select(DB::raw(1))
                            ->from('cliente_usuario as cu')
                            ->whereColumn('cu.cliente_id', 'cliente.id')
                            ->where('cu.usuario_id', Auth::id())
                            ->whereIn('cu.rol_id', [2, 3]);
                    });
            });
        }

        if (!$clientePermitido->exists()) {
            $this->clienteDuplicarId = null;
            $this->clienteDuplicarNombre = '';
            $this->clienteDuplicarError = 'El cliente seleccionado no está asignado a su usuario.';
            return;
        }

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];

        // Validar que el nuevo cliente tenga la misma categoría de precios que el cliente original
        $originalCategoriaId = DB::table('cotizacion as co')
            ->join('cliente as cl', 'cl.id', '=', 'co.cliente_id')
            ->where('co.id', $cotizacionId)
            ->value('cl.categoria_precios_id');

        $nuevaCategoriaId = DB::table('cliente')
            ->where('id', $this->clienteDuplicarId)
            ->value('categoria_precios_id');

        if ((int) $originalCategoriaId !== (int) $nuevaCategoriaId) {
            // Datos del cliente original de la oferta
            $clienteOriginal = DB::table('cotizacion as co')
                ->join('cliente as cl', 'cl.id', '=', 'co.cliente_id')
                ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'cl.categoria_precios_id')
                ->where('co.id', $cotizacionId)
                ->select('cl.nombre as nombre_cliente', 'cp.nombre as nombre_categoria')
                ->first();

            // Datos del cliente destino seleccionado
            $clienteDestino = DB::table('cliente as cl')
                ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'cl.categoria_precios_id')
                ->where('cl.id', $this->clienteDuplicarId)
                ->select('cl.nombre as nombre_cliente', 'cp.nombre as nombre_categoria')
                ->first();

            $nombreClienteOrigen   = $clienteOriginal->nombre_cliente   ?? 'desconocido';
            $nombreCatOrigen       = $clienteOriginal->nombre_categoria  ?? 'sin categoría';
            $nombreClienteDestino  = $clienteDestino->nombre_cliente     ?? 'desconocido';
            $nombreCatDestino      = $clienteDestino->nombre_categoria   ?? 'sin categoría';

            // Obtener la escala (cliente_categoria_escala) de cada cliente
            $escalaOrigen = DB::table('cliente as cl')
                ->leftJoin('cliente_categoria_escala as cce', 'cce.id', '=', 'cl.cliente_categoria_escala_id')
                ->join('cotizacion as co', 'co.cliente_id', '=', 'cl.id')
                ->where('co.id', $cotizacionId)
                ->select('cl.nombre as nombre_cliente', 'cce.nombre_categoria')
                ->first();

            $escalaDestino = DB::table('cliente as cl')
                ->leftJoin('cliente_categoria_escala as cce', 'cce.id', '=', 'cl.cliente_categoria_escala_id')
                ->where('cl.id', $this->clienteDuplicarId)
                ->select('cl.nombre as nombre_cliente', 'cce.nombre_categoria')
                ->first();

            $this->clienteDuplicarEscalaConflicto = [
                [
                    'nombre'    => $escalaOrigen->nombre_cliente  ?? $nombreClienteOrigen,
                    'categoria' => $escalaOrigen->nombre_categoria ?? 'Sin categoría',
                ],
                [
                    'nombre'    => $escalaDestino->nombre_cliente  ?? $nombreClienteDestino,
                    'categoria' => $escalaDestino->nombre_categoria ?? 'Sin categoría',
                ],
            ];
            $this->clienteDuplicarError = 'escala_diferente';
            return;
        }

        // Todo válido: abrir formulario de nueva oferta con el cliente pre-seleccionado
        $url = '/proforma/cotizacion/2?from=flujo&cotizacionId=' . $cotizacionId
             . '&clienteId=' . $this->clienteDuplicarId;

        $this->mostrarSelectorClienteDuplicar = false;
        $this->clienteDuplicarError           = '';
        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
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
            // ── NUEVO FLUJO: Oferta Ganadora → Revisión de Crédito → Revisión de Inventario ──
            //
            // Excepción: si ya existe un crédito APROBADO y VIGENTE para este flujo,
            // saltarse la Revisión de Crédito e ir directamente a Revisión de Inventario.
            $creditoVigente = CreditoRevision::creditoVigenteParaFlujo($this->flujoId);

            DB::beginTransaction();
            try {
                $comentarioCredito = trim((string) $this->comentarioCreditoGanadora);
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

                DB::table('flujo_oferta_credito_comentarios')->insert([
                    'flujo_id'    => $this->flujoId,
                    'tramite_id'  => $cotizacionId,
                    'observacion' => $comentarioCredito !== '' ? $comentarioCredito : null,
                    'created_by'  => Auth::id(),
                    'updated_by'  => Auth::id(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                // 3. Auditoría cotizacion_estado
                if ($creditoVigente) {
                    $comentarioGanadora = 'Marcada como ganadora. Crédito vigente — enviada a Revisión de Inventario.';
                } else {
                    $comentarioGanadora = 'Marcada como ganadora. Enviada a Revisión de Crédito.';
                }
                DB::table('cotizacion_estado')->insert([
                    'cotizacion_id' => $cotizacionId,
                    'flujo_id'      => $this->flujoId,
                    'ganadora'      => 1,
                    'comentario'    => $comentarioGanadora,
                    'estado_id'     => 1,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                if ($creditoVigente) {
                    // ── Crédito vigente: ir directamente a Revisión de Inventario ──
                    $revInvDevuelto = DB::table('historico_flujo')
                        ->where('flujo_id', $this->flujoId)
                        ->where('tipo_tramite_id', 9)
                        ->where('estado_id', 7)
                        ->exists();

                    if ($revInvDevuelto) {
                        DB::table('historico_flujo')
                            ->where('flujo_id', $this->flujoId)
                            ->where('tipo_tramite_id', 9)
                            ->where('estado_id', 7)
                            ->update([
                                'estado_id'     => 5,
                                'tramite_id'    => $cotizacionId,
                                'observaciones' => 'Reactivado. Crédito vigente — oferta #' . $cotizacionId,
                                'updated_by'    => Auth::id(),
                                'updated_at'    => now(),
                            ]);
                    } else {
                        DB::table('historico_flujo')->insert([
                            'flujo_id'        => $this->flujoId,
                            'tipo_tramite_id' => 9,
                            'tramite_id'      => $cotizacionId,
                            'estado_id'       => 5,
                            'observaciones'   => 'En Revisión de Inventario. Crédito vigente. Oferta #' . $cotizacionId,
                            'created_by'      => Auth::id(),
                            'updated_by'      => Auth::id(),
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }

                    DB::table('flujo')->where('id', $this->flujoId)->update([
                        'tipo_tramite_id' => 9,
                        'updated_by'      => Auth::id(),
                        'updated_at'      => now(),
                    ]);

                    DB::commit();
                    $this->stockErrors         = [];
                    $this->confirmAccionOferta = null;
                    $this->ofertaSeleccionada  = null;
                    $this->comentarioCreditoGanadora = '';
                    $this->mensajeExito = 'Oferta #' . $cotizacionId . ' marcada como ganadora. Crédito vigente — enviada a Revisión de Inventario.';

                } else {
                    // ── Sin crédito vigente: ir a Revisión de Crédito ────────
                    $fechaEmisionOferta = !empty($cotizacion->fecha_emision)
                        ? \Carbon\Carbon::parse($cotizacion->fecha_emision)
                        : null;
                    $fechaVencimientoOferta = !empty($cotizacion->fecha_vencimiento)
                        ? \Carbon\Carbon::parse($cotizacion->fecha_vencimiento)
                        : null;
                    $diasSolicitados = ($fechaEmisionOferta && $fechaVencimientoOferta)
                        ? max(0, $fechaEmisionOferta->diffInDays($fechaVencimientoOferta, false))
                        : 0;

                    $obsRevisionCredito = 'En Revisión de Crédito. Oferta #' . $cotizacionId;
                    if ($fechaEmisionOferta && $fechaVencimientoOferta) {
                        $obsRevisionCredito .= ' | Emisión: ' . $fechaEmisionOferta->format('Y-m-d')
                            . ' | Vence: ' . $fechaVencimientoOferta->format('Y-m-d')
                            . ' | Días solicitados: ' . $diasSolicitados;
                    }

                    // 4. Crear registro en historico_flujo tipo=10 (Revisión de Crédito)
                    DB::table('historico_flujo')->insert([
                        'flujo_id'        => $this->flujoId,
                        'tipo_tramite_id' => 10,
                        'tramite_id'      => $cotizacionId,
                        'estado_id'       => 5,   // Pendiente
                        'observaciones'   => $obsRevisionCredito,
                        'created_by'      => Auth::id(),
                        'updated_by'      => Auth::id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    // 4.1 Crear/actualizar registro pendiente de credito_revision para trazabilidad
                    $crPendiente = CreditoRevision::where('flujo_id', $this->flujoId)
                        ->where('estado', CreditoRevision::PENDIENTE)
                        ->latest('id')
                        ->first();

                    $obsCredito = trim(
                        'Solicitud oferta #' . $cotizacionId
                        . ($fechaEmisionOferta ? ' | Emisión: ' . $fechaEmisionOferta->format('Y-m-d') : '')
                        . ($fechaVencimientoOferta ? ' | Vence: ' . $fechaVencimientoOferta->format('Y-m-d') : '')
                        . ' | Días solicitados: ' . $diasSolicitados
                        . ' | Total oferta: L ' . number_format((float) ($cotizacion->total ?? 0), 2, '.', ',')
                    );

                    if ($crPendiente) {
                        $crPendiente->update([
                            'cotizacion_id'    => $cotizacionId,
                            'observaciones'    => $obsCredito,
                            'usuario_revision' => Auth::id(),
                            'ip_revision'      => request()->ip(),
                        ]);
                    } else {
                        CreditoRevision::create([
                            'flujo_id'         => $this->flujoId,
                            'cotizacion_id'    => $cotizacionId,
                            'estado'           => CreditoRevision::PENDIENTE,
                            'observaciones'    => $obsCredito,
                            'usuario_revision' => Auth::id(),
                            'ip_revision'      => request()->ip(),
                        ]);
                    }

                    // 5. Avanzar flujo al paso Revisión de Crédito
                    DB::table('flujo')->where('id', $this->flujoId)->update([
                        'tipo_tramite_id' => 10,
                        'updated_by'      => Auth::id(),
                        'updated_at'      => now(),
                    ]);

                    DB::commit();
                    $this->stockErrors         = [];
                    $this->confirmAccionOferta = null;
                    $this->ofertaSeleccionada  = null;
                    $this->comentarioCreditoGanadora = '';
                    $this->mensajeExito = 'Oferta #' . $cotizacionId . ' marcada como ganadora y enviada a Revisión de Crédito.';
                }

                $this->emit('pedidoActualizado');
                $this->recargar();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->mensajeError = 'Error al procesar la oferta ganadora: ' . $e->getMessage();
            }
            return;
        }

        // ── FLUJO ORIGINAL: Oferta Ganadora → Prefactura ─────────────────

        $productos = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionId)
            ->get();

        $sinExistencia = [];
        foreach ($productos as $prod) {
            if (!((float) ($prod->resta_inventario ?? 0) > 0)) {
                $sinExistencia[] = [
                    'producto'   => (string) ($prod->nombre_producto ?? ('Producto #' . ($prod->producto_id ?? 'N/A'))),
                    'solicitado' => (int) ($prod->cantidad ?? 0),
                    'disponible' => 0,
                ];
            }
        }

        if (!empty($sinExistencia)) {
            $this->stockErrors = $sinExistencia;
            $this->mensajeError = 'No se puede generar Prefactura porque hay productos marcados como sin existencia en la oferta.';
            return;
        }

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
                    ->whereRaw("TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()")
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

        $diasValidez = $this->diasVigenciaPrefactura((int) $this->flujoId);

        DB::beginTransaction();
        try {
            $comentarioCredito = trim((string) $this->comentarioCreditoGanadora);
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

            DB::table('flujo_oferta_credito_comentarios')->insert([
                'flujo_id'    => $this->flujoId,
                'tramite_id'  => $cotizacionId,
                'observacion' => $comentarioCredito !== '' ? $comentarioCredito : null,
                'created_by'  => Auth::id(),
                'updated_by'  => Auth::id(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

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
            $this->comentarioCreditoGanadora = '';
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
        $clienteId    = (int) ($this->ofertaSeleccionada['cliente_id'] ?? $this->pedidoData['cliente_id'] ?? 0);

        $this->mensajeError                  = '';
        $this->clienteDuplicarError          = '';
        $this->productosPrecioEscalaCambiado = [];
        $this->preciosCambioMostrado         = false;

        $facturaCompletada = in_array(3, $this->flujoTipos) || in_array(5, $this->flujoTipos);

        // Construir URL base
        $url = '/proforma/cotizacion/2?from=flujo&cotizacionId=' . $cotizacionId;

        if ($mismoCliente) {
            if ($facturaCompletada) {
                // Flujo con factura: nuevo flujo para el mismo cliente con escala actual
                $url .= '&clienteId=' . $clienteId;
            } else {
                // Sin factura: agregar oferta al mismo flujo
                if (!empty($this->pedidoData) && empty($this->pedidoData['sin_pedido'])) {
                    $url .= '&pedidoId=' . (int) $this->pedidoData['id'];
                } elseif ($this->flujoId) {
                    $url .= '&flujoId=' . $this->flujoId;
                }
            }
        }

        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
        $this->confirmAccionOferta = null;
    }

    public function confirmarDuplicarConNuevosPrecios(): void
    {
        $mismoCliente = $this->duplicarMismoClienteAlmacenado;
        $this->preciosCambioMostrado         = false;
        $this->productosPrecioEscalaCambiado = [];
        $this->duplicarOferta($mismoCliente);
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
     * Mantiene compatibilidad histórica al detectar una prefactura vencida.
     * Requerimiento actual: NO inactivar ni mover estado de la prefactura,
     * solo liberar su reserva por fecha en los cálculos de disponibilidad.
     */
    private function procesarVencimientoPrefactura(object $pref): void
    {
        $this->vencimientoProcesado = $this->prefacturaVencio($pref);
    }

    private function prefacturaVencio(?object $pref): bool
    {
        if (!$pref) {
            return false;
        }

        try {
            $diasValidez = (int) (DB::table('configuracion_prefactura')
                ->orderByDesc('id')
                ->value('dias_validez') ?? 7);
            $diasValidez = max(0, $diasValidez);

            if (!empty($pref->created_at)) {
                $base = \Carbon\Carbon::parse($pref->created_at);
            } elseif (!empty($pref->fecha_emision)) {
                $base = \Carbon\Carbon::parse($pref->fecha_emision)->startOfDay();
            } else {
                return false;
            }

            return now()->gt($base->copy()->addDays($diasValidez));
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function obtenerFaltantesInventarioPrefactura(int $prefacturaId, bool $ignorarReservaPropia): array
    {
        $faltantes = [];
        $productos = DB::table('prefactura_has_producto')
            ->where('prefactura_id', $prefacturaId)
            ->where('resta_inventario', 1)
            ->whereNotNull('producto_id')
            ->whereNotNull('seccion_id')
            ->get(['producto_id', 'seccion_id', 'nombre_producto', 'cantidad']);

        foreach ($productos as $prod) {
            $rawStock = (float) DB::table('recibido_bodega')
                ->where('producto_id', $prod->producto_id)
                ->where('seccion_id', $prod->seccion_id)
                ->where('cantidad_disponible', '>', 0)
                ->sum('cantidad_disponible');

            $reservadoQuery = DB::table('prefactura_has_producto as php')
                ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
                ->where('pf.estado', 'activo')
                ->whereRaw("TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()")
                ->where('php.producto_id', $prod->producto_id)
                ->where('php.seccion_id', $prod->seccion_id)
                ->where('php.resta_inventario', 1);

            if ($ignorarReservaPropia) {
                $reservadoQuery->where('pf.id', '!=', $prefacturaId);
            }

            $reservado = (float) $reservadoQuery->sum('php.cantidad');
            $disponible = max(0.0, $rawStock - $reservado);
            $solicitado = (float) ($prod->cantidad ?? 0);

            if ($disponible + 0.0001 < $solicitado) {
                $faltantes[] = [
                    'producto'   => (string) ($prod->nombre_producto ?? ('Producto #' . $prod->producto_id)),
                    'solicitado' => round($solicitado, 2),
                    'disponible' => round($disponible, 2),
                ];
            }
        }

        return $faltantes;
    }

    /**
     * Compara los precios de cada producto de la cotización con la escala ACTUAL del cliente.
     * Devuelve un array de productos donde el precio difiere entre la escala original y la actual.
     */
    private function obtenerProductosCambioEscalaCliente(int $cotizacionId, int $clienteId): array
    {
        // Comparar precio_a del ppc original vs precio_a vigente (estado_id=1)
        // dentro de la misma categoria_precios y producto.
        // Alerta si el precio subió (Escala Selec. < Escala Act.).
        $lineas = DB::table('cotizacion_has_producto as chp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->where('chp.cotizacion_id', $cotizacionId)
            ->whereNotNull('chp.precios_producto_carga_id')
            ->whereNotNull('ppc.producto_id')
            ->whereNotNull('ppc.categoria_precios_id')
            ->select(
                'chp.nombre_producto',
                'ppc.precio_a as precio_escala_seleccionada',
                'ppc.producto_id',
                'ppc.categoria_precios_id'
            )
            ->get();

        if ($lineas->isEmpty()) return [];

        $cambios = [];

        foreach ($lineas as $l) {
            $precioSel = round((float) ($l->precio_escala_seleccionada ?? 0), 4);
            if ($precioSel <= 0) continue;

            // Precio vigente para la misma escala y producto
            $precioAct = DB::table('precios_producto_carga')
                ->where('categoria_precios_id', (int) $l->categoria_precios_id)
                ->where('producto_id', (int) $l->producto_id)
                ->where('estado_id', 1)
                ->value('precio_a');

            if ($precioAct === null) continue;

            $precioAct = round((float) $precioAct, 4);

            // Solo alertar si el precio subió (Escala Selec. < Escala Act.)
            if ($precioSel < $precioAct - 0.0001) {
                $cambios[] = [
                    'nombre_producto' => (string) ($l->nombre_producto ?? 'Sin nombre'),
                    'precio_original' => $precioSel,
                    'precio_nuevo'    => $precioAct,
                ];
            }
        }

        return $cambios;
    }

    /**
     * Compara los precios de cada producto de la cotización con los precios actuales
     * en precios_producto_carga. Devuelve true si al menos un producto cambió de precio.
     */
    private function verificarCambioPrecios(int $cotizacionId): bool
    {
        return !empty($this->obtenerProductosConPrecioEscalaCambiado($cotizacionId));
    }

    /**
     * Devuelve el detalle de productos donde la escala vigente es mayor
     * al precio guardado en la cotización.
     */
    private function obtenerProductosConPrecioEscalaCambiado(int $cotizacionId): array
    {
        $lineas = DB::table('cotizacion_has_producto as chp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->where('chp.cotizacion_id', $cotizacionId)
            ->whereNotNull('chp.precios_producto_carga_id')
            ->select(
                'chp.nombre_producto',
                'chp.precio_unidad',
                'chp.precioSeleccionado',
                'chp.idPrecioSeleccionado',
                'ppc.precio_a', 'ppc.precio_b', 'ppc.precio_c', 'ppc.precio_d',
                'ppc.precio_base_venta'
            )
            ->get();

        $productosConCambio = [];

        foreach ($lineas as $l) {
            // Precio de escala vigente hoy
            $selector = strtolower(trim((string) $l->idPrecioSeleccionado));
            $precioEscalaActual = match($selector) {
                'p1', 'a' => (float) $l->precio_a,
                'p2', 'b' => (float) $l->precio_b,
                'p3', 'c' => (float) $l->precio_c,
                'p4', 'd' => (float) $l->precio_d,
                default   => (float) $l->precio_base_venta,
            };

            // Precio de escala guardado al momento de crear la cotización.
            // Si no existe, usar precio_unidad como respaldo.
            $precioOfertaEscala = (float) ($l->precioSeleccionado ?? 0);
            if ($precioOfertaEscala <= 0) {
                $precioOfertaEscala = (float) ($l->precio_unidad ?? 0);
            }

            // Bloquear si la escala actual es mayor al precio que cobró el vendedor.
            // Si la escala bajó o es igual, se permite duplicar.
            if ($precioEscalaActual > $precioOfertaEscala + 0.0001) {
                $escala = match($selector) {
                    'p1', 'a' => 'A',
                    'p2', 'b' => 'B',
                    'p3', 'c' => 'C',
                    'p4', 'd' => 'D',
                    default   => 'BASE',
                };

                $productosConCambio[] = [
                    'nombre_producto'      => (string) ($l->nombre_producto ?? 'Producto sin nombre'),
                    'escala'               => $escala,
                    'precio_oferta'        => round($precioOfertaEscala, 4),
                    'precio_escala_actual' => round($precioEscalaActual, 4),
                ];
            }
        }

        return $productosConCambio;
    }

    private function cargarPrefactura(): void
    {
        if (!$this->flujoId) {
            $this->prefacturaData = null;
            $this->expoConSaldoPendiente = false;
            $this->prefacturaVencida = false;
            $this->prefacturaPuedeFacturar = true;
            $this->prefacturaStockFaltante = [];
            $this->prefacturaReservaCompleta = true;
            $this->prefacturaReservaFaltante = [];
            return;
        }
        $pref = DB::table('prefactura')
            ->where('flujo_id', $this->flujoId)
            ->whereIn('estado', ['activo', 'convertida'])
            ->orderByDesc('id')
            ->first();

        $this->prefacturasData = DB::table('prefactura')
            ->where('flujo_id', $this->flujoId)
            ->orderBy('id')
            ->get(['id', 'estado', 'fecha_emision', 'fecha_vencimiento', 'total'])
            ->map(fn($row) => (array) $row)->toArray();

        if (!$pref) {
            $this->prefacturaData = null;
            $this->expoConSaldoPendiente = false;
            $this->prefacturaVencida = false;
            $this->prefacturaPuedeFacturar = true;
            $this->prefacturaStockFaltante = [];
            $this->prefacturaReservaCompleta = true;
            $this->prefacturaReservaFaltante = [];
            return;
        }

        $this->prefacturaVencida = $this->prefacturaVencio($pref);
        $this->vencimientoProcesado = false;

        $productos = DB::table('prefactura_has_producto')
            ->where('prefactura_id', $pref->id)
            ->select('nombre_producto', 'cantidad', 'precio_unidad', 'total')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->prefacturaData = array_merge((array) $pref, ['productos' => $productos]);
        $cotizacionId = (int) ($pref->cotizacion_id ?? 0);
        $this->expoConSaldoPendiente = $cotizacionId > 0
            && DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacionId)->exists()
            && app(SaldoLineasOferta::class)->pendientes($cotizacionId)
                ->contains(fn($linea) => (float) $linea->cantidad_pendiente > 0);

        // Regla todo-o-nada de reserva: si no cubre cantidades completas, no debe apartar.
        $this->prefacturaReservaFaltante = $this->obtenerFaltantesInventarioPrefactura((int) $pref->id, true);
        $this->prefacturaReservaCompleta = empty($this->prefacturaReservaFaltante);

        if ($this->prefacturaVencida) {
            // Al vencer, la reserva de esta prefactura se considera liberada.
            // Para facturar se requiere revalidar stock disponible actual.
            $this->prefacturaStockFaltante = $this->obtenerFaltantesInventarioPrefactura((int) $pref->id, true);
            $this->prefacturaPuedeFacturar = empty($this->prefacturaStockFaltante);
        } else {
            if ($this->prefacturaReservaCompleta) {
                $this->prefacturaStockFaltante = [];
                $this->prefacturaPuedeFacturar = true;
            } else {
                $this->prefacturaStockFaltante = $this->prefacturaReservaFaltante;
                $this->prefacturaPuedeFacturar = false;
            }
        }
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

    public function enviarCodigoPrefactura(): void
    {
        $accion   = $this->accionAutorizacionPrefactura ?? 'desconocida';
        $flujoId  = $this->flujoId;
        $usuario  = Auth::user()->name ?? 'N/A';

        $accionLabel = match($accion) {
            'editar_factura'       => 'Editar Prefactura/Factura',
            'anular_prefactura'    => 'Anular Prefactura',
            'revertir_prefactura'  => 'Pasar Prefactura a Oferta',
            default                => ucfirst(str_replace('_', ' ', $accion)),
        };

        $config   = ConfiguracionCodigoAutorizacion::obtener();
        $codigo   = rand(1000, 9999);

        $autorizacion = new ModelCodigoAutorizacion;
        $autorizacion->codigo           = $codigo;
        $autorizacion->users_id         = Auth::user()->id;
        $autorizacion->estado_id        = 1;
        $autorizacion->flujo_id         = $flujoId;
        $autorizacion->tipo_tramite     = $accion;  // editar_factura | anular_prefactura | revertir_prefactura
        $autorizacion->estado_codigo_id = 1; // Pendiente
        $autorizacion->fecha_expiracion = $config->expiracion_activa
            ? now()->addMinutes($config->tiempo_expiracion_minutos)
            : null;
        $autorizacion->save();

        $viewData = [
            'codigo'       => $codigo,
            'accionLabel'  => $accionLabel,
            'flujoId'      => $flujoId,
            'usuario'      => $usuario,
        ];

        // Preview en log
        try {
            $html = view('email.solicitud-flujo', $viewData)->render();
            Log::info("=== EMAIL PREVIEW solicitud flujo ({$accionLabel}) ===\n" . strip_tags($html, '<table><tr><td><th><b><strong>'));
        } catch (\Throwable $e) {
            Log::warning('No se pudo previsualizar email flujo: ' . $e->getMessage());
        }

        $for = ['autorizaciones@distribucionesvalencia.hn'];
        Mail::send('email.solicitud-flujo', $viewData, function ($msj) use ($accionLabel, $for) {
            $msj->from(env('MAIL_FROM_ADRESS'), 'Soporte Técnico Distribuciones Valencia');
            $msj->subject('Solicitud de autorización – ' . $accionLabel);
            $msj->to($for);
        });

        $this->dispatchBrowserEvent('flujo-codigo-enviado');
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

        $autorizacion = ModelCodigoAutorizacion::where('estado_id', 1)
            ->where('estado_codigo_id', 1) // Pendiente
            ->where('codigo', $codigo)
            ->first(['id', 'users_id', 'flujo_id', 'tipo_tramite', 'fecha_expiracion', 'estado_codigo_id']);

        if (!$autorizacion || !$autorizacion->esValido((int) $this->flujoId, $this->accionAutorizacionPrefactura)) {
            $this->mensajeError = 'El código de autorización no es válido o ha expirado.';
            return;
        }

        $this->autorizacionId = (int) $autorizacion->id;
        $this->autorizadorId = (int) $autorizacion->users_id;

        $motivo = trim((string) $this->motivoAutorizacion);
        if ($motivo === '') {
            $this->mensajeError = 'El motivo es obligatorio.';
            return;
        }

        // Marcar el código como utilizado
        $autorizacion->marcarUtilizado();

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
        $this->mensajeError = '';

        $cotizacionId = (int) ($this->prefacturaData['cotizacion_id'] ?? 0);
        $esOfertaExpo = $cotizacionId > 0 && DB::table('expo_cotizacion')
            ->where('cotizacion_id', $cotizacionId)
            ->exists();

        if ($esOfertaExpo) {
            $tipoVentaFiscal = (int) DB::table('cotizacion as c')
                ->join('cliente as cl', 'cl.id', '=', 'c.cliente_id')
                ->where('c.id', $cotizacionId)
                ->value('cl.tipo_cliente_id');

            $tipoFactura = DB::table('tipo_factura')
                ->where('estado', 1)
                ->where('codigo', '!=', 'cotizacion_clientes_a')
                ->where('tipo_venta_id', $tipoVentaFiscal)
                ->orderBy('orden')
                ->first(['ruta_menu']);

            if (!$tipoFactura) {
                $this->mensajeError = 'No hay un tipo de facturación disponible para esta Oferta Expo.';
                return;
            }

            $urlBase = '/' . ltrim($tipoFactura->ruta_menu, '/')
                . '?from=prefactura'
                . '&prefactura_id=' . (int) $this->prefacturaData['id']
                . '&flujoId=' . (int) $this->flujoId
                . '&cotizacionId=' . $cotizacionId;

            $this->dispatchBrowserEvent('fmp-facturar-expo', [
                'url_completa' => $urlBase,
                'url_parcial' => $urlBase . '&expo_parcial=1',
            ]);
            return;
        }

        if ($this->prefacturaVencida) {
            $faltantes = $this->obtenerFaltantesInventarioPrefactura((int) $this->prefacturaData['id'], true);
            $this->prefacturaStockFaltante = $faltantes;
            $this->prefacturaPuedeFacturar = empty($faltantes);

            if (!$this->prefacturaPuedeFacturar) {
                $this->mensajeError = 'No es posible generar la factura porque uno o más productos ya no cuentan con inventario disponible. Actualice la prefactura antes de continuar.';
                return;
            }
        }

        // Determinar tipo_pago con la misma lógica de prioridades que el backend:
        // 1. credito_revision aprobado:
        //    - dias_credito_aprobados > 0  → Crédito
        //    - dias_credito_aprobados = 0  → Contado (venta contado aprobada en revisión)
        //    - dias_credito_aprobados NULL → mantener comportamiento anterior (Crédito)
        // 2. Cotización ganadora tiene días de crédito (fecha_vencimiento > fecha_emision) → Crédito
        // 3. Sin señal de crédito → Contado
        $tipoPago = 1;

        if (!empty($this->creditoRevisionData['estado']) &&
            $this->creditoRevisionData['estado'] === 'aprobado') {
            $diasAprobados = $this->creditoRevisionData['dias_credito_aprobados'] ?? null;
            if (!is_null($diasAprobados)) {
                // Registro nuevo: 0 = contado, > 0 = crédito
                $tipoPago = ((int) $diasAprobados > 0) ? 2 : 1;
            } else {
                // Registro antiguo sin dias_credito_aprobados: preservar comportamiento anterior
                $tipoPago = 2;
            }
        } else {
            // Verificar tipo_pago_id de la cotización vinculada a la prefactura
            $cotizacionId = (int) ($this->prefacturaData['cotizacion_id'] ?? 0);
            if ($cotizacionId) {
                $cot = DB::table('cotizacion')
                    ->where('id', $cotizacionId)
                    ->first(['fecha_emision', 'fecha_vencimiento', 'tipo_pago_id']);
                if ($cot) {
                    if (!is_null($cot->tipo_pago_id)) {
                        // tipo_pago_id explícito: usar directamente (1=contado, 2=crédito)
                        $tipoPago = (int) $cot->tipo_pago_id;
                    } elseif ($cot->fecha_emision && $cot->fecha_vencimiento) {
                        // Retrocompatibilidad: registros anteriores a la migración (tipo_pago_id NULL)
                        $dias = (int) \Carbon\Carbon::parse($cot->fecha_emision)
                            ->diffInDays(\Carbon\Carbon::parse($cot->fecha_vencimiento), false);
                        if ($dias > 0) {
                            $tipoPago = 2;
                        }
                    }
                }
            }
        }

        $this->dispatchBrowserEvent('fmp-facturar-directo', [
            'url'       => '/prefactura/' . (int) $this->prefacturaData['id'] . '/facturar-directo',
            'tipo_pago' => $tipoPago,
            'cliente_id' => (int) ($this->pedidoData['cliente_id'] ?? 0),
            'tele_asesor_id' => Auth::id(),
            'tele_asesor_nombre' => Auth::user()->name ?? '',
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
        $this->cargarPrefactura();
        $this->cargarLiquidacionExpoPendiente();
        $facturaIds = $this->obtenerFacturaIdsFlujo();
        if (empty($facturaIds)) {
            $this->facturaData = null;
            $this->facturasData = [];
            $this->notasCreditoData = [];
            return;
        }

        $this->facturasData = collect($facturaIds)
            ->map(fn($facturaId) => $this->construirFacturaData((int) $facturaId))
            ->filter()
            ->values()
            ->all();
        $this->facturaData = empty($this->facturasData) ? null : end($this->facturasData);

        $this->saldoPendienteFactura = isset($this->facturaData['pendiente_cobro'])
            ? (float) $this->facturaData['pendiente_cobro']
            : null;
        $this->cargarNotasCreditoFlujo();
    }

    private function cargarLiquidacionExpoPendiente(): void
    {
        $this->liquidacionExpoPendiente = null;
        if (!$this->flujoId) {
            return;
        }

        $cotizacionId = DB::table('expo_cotizacion')
            ->where('flujo_id', $this->flujoId)
            ->where('estado', 'PENDIENTE_LIQUIDACION')
            ->value('cotizacion_id');
        if (!$cotizacionId) {
            return;
        }

        try {
            $this->liquidacionExpoPendiente = app(LiquidacionOfertaExpo::class)
                ->previsualizar((int) $cotizacionId, (int) $this->flujoId);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function cargarNotasCreditoFlujo(): void
    {
        $facturaIds = $this->obtenerFacturaIdsFlujo();
        if (empty($facturaIds)) {
            $this->notasCreditoData = [];
            return;
        }

        $notaIdsExpo = DB::table('expo_cotizacion as ec')
            ->join('historico_flujo as hf', 'hf.tramite_id', '=', 'ec.cotizacion_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->where('hf.tipo_tramite_id', 2)
            ->whereNotNull('ec.nota_credito_id')
            ->pluck('ec.nota_credito_id');

        $notas = DB::table('nota_credito as nc')
            ->leftJoin('nota_credito_creditos as ncc', 'ncc.nota_credito_id', '=', 'nc.id')
            ->where(function ($query) use ($facturaIds, $notaIdsExpo) {
                $query->whereIn('nc.factura_id', $facturaIds);
                if ($notaIdsExpo->isNotEmpty()) {
                    $query->orWhereIn('nc.id', $notaIdsExpo);
                }
            })
            ->orderBy('nc.fecha')
            ->orderBy('nc.id')
            ->get([
                'nc.id', 'nc.numero_nota', 'nc.cai', 'nc.fecha', 'nc.total', 'nc.estado_nota_id',
                'ncc.id as credito_id', 'ncc.monto_aplicado', 'ncc.saldo_disponible', 'ncc.estado as estado_credito',
            ]);

        $creditoIds = $notas->pluck('credito_id')->filter()->all();
        $aplicaciones = DB::table('nota_credito_movimientos as ncm')
            ->leftJoin('factura as f', 'f.id', '=', 'ncm.factura_id')
            ->whereIn('ncm.credito_id', $creditoIds)
            ->where('ncm.tipo', 'aplicacion')
            ->orderBy('ncm.fecha_movimiento')
            ->orderBy('ncm.id')
            ->get(['ncm.credito_id', 'ncm.factura_id', 'f.cai as factura', 'ncm.monto', 'ncm.fecha_movimiento'])
            ->groupBy('credito_id');

        $this->notasCreditoData = $notas->map(function ($nota) use ($aplicaciones) {
            $data = (array) $nota;
            $data['aplicaciones'] = collect($aplicaciones[$nota->credito_id] ?? [])
                ->map(fn($movimiento) => (array) $movimiento)->all();
            return $data;
        })->all();
    }

    private function construirFacturaData(int $facturaId): ?array
    {
        $factura = DB::table('factura')->where('id', $facturaId)->first();
        if (!$factura) {
            return null;
        }

        $productos = DB::table('venta_has_producto as vhp')
            ->leftJoin('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->where('vhp.factura_id', $facturaId)
            ->select(
                DB::raw('COALESCE(p.nombre, CONCAT("Producto #", vhp.producto_id)) as nombre_producto'),
                'vhp.cantidad',
                'vhp.precio_unidad',
                DB::raw('COALESCE(vhp.total, vhp.total_s) as total')
            )
            ->orderBy('vhp.indice')
            ->get()->map(fn($row) => (array) $row)->toArray();
        $vale = DB::table('vale')->where('factura_id', $facturaId)->whereNotIn('estado_id', [7])
            ->orderByDesc('id')->first(['id', 'numero_vale']);
        $esExonerada = (int) ($factura->tipo_venta_id ?? 0) === 3;

        return array_merge((array) $factura, [
            'productos' => $productos,
            'print_url' => $esExonerada ? '/exonerado/factura/' . $facturaId : '/factura/cooporativo/' . $facturaId,
            'print_copia_url' => $esExonerada ? '/exonerado/facturaCopia/' . $facturaId : '/factura/cooporativoCopia/' . $facturaId,
            'print_acta_rec_url' => $esExonerada ? '/exonerado/actaRec/' . $facturaId : '/facturaCoor/actaRec/' . $facturaId,
            'vale_id' => $vale?->id,
            'vale_numero' => $vale?->numero_vale,
        ]);
    }

    /** @return array<int, int> */
    private function obtenerFacturaIdsFlujo(): array
    {
        if (!$this->flujoId) {
            return [];
        }

        return DB::table('historico_flujo as hf')
            ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->whereIn('hf.tipo_tramite_id', [3, 5])
            ->orderBy('f.fecha_emision')
            ->orderBy('f.id')
            ->pluck('f.id')
            ->map(fn($id) => (int) $id)
            ->unique()->values()->all();
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

        $facturaIds = $this->obtenerFacturaIdsFlujo();
        if (empty($facturaIds)) {
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
            ->whereIn('def.factura_id', $facturaIds)
            ->orderBy('de.fecha_programada')
            ->orderBy('de.id')
            ->get([
                'de.id as distribucion_id',
                'def.factura_id',
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

        $facturaIds = $this->obtenerFacturaIdsFlujo();
        if (empty($facturaIds)) {
            $this->saldoPendienteFactura = null;
            $this->cobroFacturaData = null;
            $this->historialPagosFactura = [];
            $this->aplicacionPagoId = null;
            return;
        }

        $facturas = DB::table('factura')->whereIn('id', $facturaIds)->where('estado_venta_id', 1)
            ->orderBy('fecha_emision')->orderBy('id')
            ->get(['id', 'cai', 'nombre_cliente', 'total', 'fecha_emision', 'created_at', 'pendiente_cobro']);
        if ($facturas->isEmpty()) {
            $this->saldoPendienteFactura = null;
            $this->cobroFacturaData = null;
            $this->historialPagosFactura = [];
            $this->aplicacionPagoId = null;
            return;
        }
        $aplicaciones = DB::table('aplicacion_pagos')->whereIn('factura_id', $facturaIds)->where('estado', 1)
            ->orderBy('id')->get(['id', 'factura_id', 'saldo', 'credito_abonos', 'created_at', 'updated_at']);

        $this->aplicacionPagoId = $aplicaciones->count() === 1 ? (int) $aplicaciones->first()->id : null;
        $this->saldoPendienteFactura = round((float) $aplicaciones->sum('saldo'), 2);
        $factura = $facturas->first();

        $this->cobroFacturaData = [
            'id'           => $facturas->pluck('id')->implode(', '),
            'cai'          => $facturas->pluck('cai')->implode(', '),
            'nombre'       => $factura->nombre_cliente,
            'total'        => (float) $facturas->sum('total'),
            'fecha_emision'=> $factura->fecha_emision ?? $factura->created_at,
        ];

        $historial = DB::table('abonos_creditos as ac')
            ->leftJoin('users as u', 'u.id', '=', 'ac.usr_registro')
            ->leftJoin('tipo_pago_cobro as tpc', 'tpc.id', '=', 'ac.id_tipo_pago_cobro')
            ->leftJoin('banco as b', 'b.id', '=', 'ac.banco_id')
            ->whereIn('ac.factura_id', $facturaIds)
            ->where('ac.estado_abono', 1)
            ->orderByDesc('ac.fecha_pago')
            ->orderByDesc('ac.id')
            ->get([
                'ac.id',
                'ac.factura_id',
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
                $actualizarCobro['observaciones'] = 'Cobro completado: todas las facturas activas del flujo tienen saldo <= 0';
            }

            if (!empty($actualizarCobro)) {
                $actualizarCobro['updated_at'] = now();
                DB::table('historico_flujo')
                    ->where('id', $cobroHist->id)
                    ->update($actualizarCobro);
            }
        }

        // Finalizar solo cuando todas las condiciones del flujo completo estén resueltas.
        if ((float) $this->saldoPendienteFactura <= 0) {
            $entregaCompletada = DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 5)
                ->where('estado_id', 1)
                ->where('estado_id', '!=', 7)
                ->exists();

            $ofertaExpo = DB::table('expo_cotizacion as ec')
                ->join('historico_flujo as hf', 'hf.tramite_id', '=', 'ec.cotizacion_id')
                ->where('hf.flujo_id', $this->flujoId)
                ->where('hf.tipo_tramite_id', 2)
                ->first(['ec.estado']);
            $liquidacionCompletada = !$ofertaExpo || $ofertaExpo->estado === 'LIQUIDADA';

            $entregasPendientes = DB::table('distribuciones_entrega_facturas as def')
                ->join('distribuciones_entrega as de', 'de.id', '=', 'def.distribucion_entrega_id')
                ->whereIn('def.factura_id', $facturaIds)
                ->whereNotIn('de.estado_id', [3, 4])
                ->exists();

            if ($entregaCompletada && !$entregasPendientes && $liquidacionCompletada) {
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

    public function confirmarAccionFactura(string $accion, ?int $facturaId = null): void
    {
        $this->confirmAccionFactura    = $accion;
        $this->facturaSeleccionadaId   = $facturaId;
        $this->confirmAccionPrefactura = null;
        $this->mensajeError            = '';
    }

    public function cancelarConfirmFactura(): void
    {
        $this->confirmAccionFactura   = null;
        $this->facturaSeleccionadaId  = null;
        $this->motivoAnulacionFactura = '';
        $this->mensajeError           = '';
    }

    public function anularFactura(): void
    {
        if (!$this->facturaSeleccionadaId || !$this->flujoId) return;

        $motivo = trim($this->motivoAnulacionFactura);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo de anulación.';
            return;
        }

        $facturaId = (int) $this->facturaSeleccionadaId;
        $perteneceAlFlujo = DB::table('historico_flujo as hf')
            ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->whereIn('hf.tipo_tramite_id', [3, 5])
            ->where('f.id', $facturaId)
            ->where('f.estado_venta_id', 1)
            ->exists();
        if (!$perteneceAlFlujo) {
            $this->mensajeError = 'La factura seleccionada no está activa en este flujo.';
            return;
        }

        $ofertaExpo = DB::table('expo_cotizacion as ec')
            ->join('historico_flujo as hf', 'hf.tramite_id', '=', 'ec.cotizacion_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->where('hf.tipo_tramite_id', 2)
            ->first(['ec.estado', 'ec.nota_credito_id']);
        $notaAplicada = DB::table('nota_credito_movimientos')->where('factura_id', $facturaId)->where('tipo', 'aplicacion')->exists();
        if ($notaAplicada || ($ofertaExpo && !in_array($ofertaExpo->estado, ['PENDIENTE_FACTURACION', 'FACTURACION_PARCIAL'], true))) {
            $this->mensajeError = 'La factura requiere reversión o validación contable antes de anularse porque la Oferta Expo está cerrada o tiene una nota aplicada.';
            return;
        }

        if (DB::table('abonos_creditos')->where('factura_id', $facturaId)->where('estado_abono', 1)->exists()) {
            $this->mensajeError = 'La factura tiene cobros activos. Contabilidad debe revertirlos antes de anularla.';
            return;
        }

        DB::beginTransaction();
        try {
            // 0) Marcar la factura como anulada
            DB::table('factura')
                ->where('id', $facturaId)
                ->update([
                    'estado_venta_id' => 2,
                    'updated_at'      => now(),
                ]);

            // 0.1) Devolver unidades al inventario (inverso de restarUnidadesInventario)
            $lotes = DB::select(
                'SELECT lote, numero_unidades_resta_inventario, unidad_medida_venta_id
                 FROM venta_has_producto
                 WHERE factura_id = ?',
                [$facturaId]
            );

            $logInventario = [];
            foreach ($lotes as $lote) {
                $recibidoBodega = ModelRecibirBodega::find($lote->lote);
                if ($recibidoBodega) {
                    $recibidoBodega->cantidad_disponible += $lote->numero_unidades_resta_inventario;
                    $recibidoBodega->save();

                    $logInventario[] = [
                        'origen'                 => $lote->lote,
                        'destino'                => $lote->lote,
                        'factura_id'             => $facturaId,
                        'cantidad'               => $lote->numero_unidades_resta_inventario,
                        'unidad_medida_venta_id' => $lote->unidad_medida_venta_id,
                        'users_id'               => Auth::id(),
                        'descripcion'            => 'Factura Anulada',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];
                }
            }

            if (!empty($logInventario)) {
                ModelLogTranslados::insert($logInventario);
            }

            // 0.2) Restaurar crédito del cliente
            $facturaRow = DB::table('factura')->where('id', $facturaId)->first();
            if ($facturaRow) {
                $cliente = ModelCliente::find($facturaRow->cliente_id);
                if ($cliente) {
                    $cliente->credito = $cliente->credito + $facturaRow->total;
                    $cliente->save();
                }
            }

            // 0.3) Inactivar aplicaciones de pago de esta factura
            DB::table('aplicacion_pagos')
                ->where('factura_id', $facturaId)
                ->update(['estado' => 2, 'updated_at' => now()]);

            DB::table('distribuciones_entrega_facturas')
                ->where('factura_id', $facturaId)
                ->where('estado_entrega', '!=', 'anulada')
                ->update([
                    'estado_entrega' => 'anulada',
                    'motivo_anulacion' => 'Factura #' . $facturaId . ' anulada: ' . $motivo,
                    'updated_at' => now(),
                ]);

            // 1) Inactivar registro de factura en historico_flujo
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->whereIn('tipo_tramite_id', [3, 5])
                ->where('tramite_id', $facturaId)
                ->update([
                    'estado_id'     => 7,
                    'observaciones' => 'Anulada: Factura #' . $facturaId . '. Motivo: ' . $motivo,
                    'updated_at'    => now(),
                ]);

            DB::commit();
            $this->confirmAccionFactura   = null;
            $this->facturaSeleccionadaId  = null;
            $this->motivoAnulacionFactura = '';
            $this->mensajeExito = 'Factura #' . $facturaId . ' anulada sin afectar las demás facturas ni la oferta ganadora.';
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
    protected function revertirPrefacturaAOferta(): void
    {
        if (!$this->prefacturaData || !$this->flujoId) return;
        if (!$this->autorizacionId) return;

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

            // Eliminar ciclo de Rev. Inventario para que al re-seleccionar ganadora empiece desde cero
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 9)
                ->delete();

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
    protected function anularPrefactura(): void
    {
        if (!$this->prefacturaData || !$this->flujoId) return;
        if (!$this->autorizacionId) return;

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

            // Eliminar ciclo de Rev. Inventario para que al re-seleccionar ganadora empiece desde cero
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 9)
                ->delete();

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
