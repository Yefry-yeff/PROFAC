<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CreditoRevision;
use App\Models\CreditoRevisionHistorial;
use App\Services\CreditoService;
use App\Events\FlujoAvanzadoEvent;
use Carbon\Carbon;

/**
 * Bandeja de Revisión de Crédito.
 *
 * Gestiona el paso intermedio entre "Oferta Ganadora" y "Revisión de Inventario":
 *  – Pestaña "llegando"   → pendientes de revisión crediticia
 *  – Pestaña "aprobadas"  → créditos aprobados
 *  – Pestaña "rechazadas" → créditos rechazados (flujo cancelado)
 */
class RevisionCreditos extends Component
{
    // ── Bandeja ───────────────────────────────────────────────────────────
    public array  $bandejaLlegando   = [];
    public array  $bandejaAprobadas  = [];
    public array  $bandejaRechazadas = [];
    public string $busqueda          = '';
    public string $tabActiva         = 'llegando';

    // ── Paginación ────────────────────────────────────────────────────────
    public int   $perPage = 8;
    public array $paginas  = ['llegando' => 1, 'aprobadas' => 1, 'rechazadas' => 1];

    // ── Detalle del flujo seleccionado ────────────────────────────────────
    public ?int   $flujoId          = null;
    protected     $flujoData        = null;
    public ?int   $cotizacionId     = null;
    public ?int   $clienteId        = null;
    public string $tipoPagoSolicitud = 'contado';
    public ?string $fechaEmisionOferta = null;
    public ?string $fechaVencimientoOferta = null;
    public int    $diasSolicitadosCredito = 0;
    public float  $montoTotalOferta = 0.0;
    // Documentos de la oferta
    public ?string $numeroOrdenCompra    = null;
    public ?string $archivoOrdenCompra   = null;
    public ?string $numeroFormaF01       = null;
    public ?string $archivoFormaF01      = null;

    // ── Estado crédito actual ─────────────────────────────────────────────
    public ?string $estadoCredito           = null;
    public ?string $fechaAprobacionActual   = null;
    public ?string $fechaVencimientoActual  = null;
    public ?string $motivoRechazoActual     = null;
    public ?string $obsAprobacionActual      = null;
    public ?string $usuarioAprobadorActual   = null;
    public array   $historialCredito        = [];

    // ── Confirmación de acciones ──────────────────────────────────────────
    public ?string $confirmAccion    = null;
    public string  $fechaAprobacion  = '';
    public string  $fechaVencimiento = '';
    public string  $motivoRechazo    = '';
    public string  $observaciones    = '';

    // ── Datos crediticios actuales/edición ───────────────────────────────
    public float $montoCreditoActual      = 0.0;
    public float $montoDisponibleActual   = 0.0;
    public int   $diasCreditoActual       = 0;
    public float $montoCreditoEditable    = 0.0;
    public string $montoCreditoEditableTexto = '0.00';
    public int   $diasCreditoEditable     = 0;
    public bool  $puedeAutorizar          = false;
    public array $bloqueosAutorizacion    = [];

    // ── Mensajes ──────────────────────────────────────────────────────────
    public string $mensajeExito = '';
    public string $mensajeError = '';

    // ─────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->cargar();
        $flujoId = request()->integer('flujo_id');
        if ($flujoId > 0) {
            $this->seleccionarFlujo($flujoId);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // BANDEJA
    // ─────────────────────────────────────────────────────────────────────

    public function updatedBusqueda(): void
    {
        $this->paginas = ['llegando' => 1, 'aprobadas' => 1, 'rechazadas' => 1];
        $this->cargar();
    }

    public function updatedPerPage(): void
    {
        $this->paginas = ['llegando' => 1, 'aprobadas' => 1, 'rechazadas' => 1];
    }

    public function irPagina(string $tab, int $pagina): void
    {
        $total = count(match($tab) {
            'aprobadas'  => $this->bandejaAprobadas,
            'rechazadas' => $this->bandejaRechazadas,
            default      => $this->bandejaLlegando,
        });
        $maxPagina = max(1, (int) ceil($total / $this->perPage));
        $this->paginas[$tab] = max(1, min($pagina, $maxPagina));
    }

    public function cargar(): void
    {
        $term = trim($this->busqueda);
        $this->bandejaLlegando   = $this->buildBandejaQuery($term, 'llegando');
        $this->bandejaAprobadas  = $this->buildBandejaQuery($term, 'aprobadas');
        $this->bandejaRechazadas = $this->buildBandejaQuery($term, 'rechazadas');
    }

    public function cambiarTab(string $tab): void
    {
        $allowed = ['llegando', 'aprobadas', 'rechazadas'];
        $this->tabActiva = in_array($tab, $allowed) ? $tab : 'llegando';
    }

    private function buildBandejaQuery(string $term, string $tipo): array
    {
        // Subquery: registro MÁS RECIENTE de revisión de crédito (tipo=10) por flujo
        $latestRevSub = DB::table('historico_flujo')
            ->select('flujo_id', DB::raw('MAX(id) as max_id'))
            ->where('tipo_tramite_id', 10)
            ->groupBy('flujo_id');

        $q = DB::table('flujo as f')
            ->joinSub($latestRevSub, 'lrev', fn($j) => $j->on('lrev.flujo_id', '=', 'f.id'))
            ->join('historico_flujo as hf', 'hf.id', '=', 'lrev.max_id')
            ->leftJoin('historico_flujo as hfof', function ($j) {
                $j->on('hfof.flujo_id', '=', 'f.id')
                  ->where('hfof.tipo_tramite_id', 2)
                  ->where('hfof.observaciones', 'ganadora');
            })
            ->leftJoin('cotizacion as c', 'c.id', '=', 'hfof.tramite_id')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('credito_revision as cr', 'cr.flujo_id', '=', 'f.id')
            ->leftJoin('users as ur', 'ur.id', '=', 'cr.usuario_revision')
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'hf.created_at as fecha_revision',
                'hf.updated_at as fecha_accion',
                'hfof.tramite_id as cotizacion_id',
                'c.cliente_id',
                'c.fecha_emision as fecha_emision_oferta',
                'c.fecha_vencimiento as fecha_vencimiento_oferta',
                'c.total as monto_total_oferta',
                DB::raw('GREATEST(DATEDIFF(c.fecha_vencimiento, c.fecha_emision), 0) as dias_solicitados_credito'),
                DB::raw("COALESCE(c.nombre_cliente, p.observaciones, CONCAT('Flujo #', f.id)) as cliente"),
                DB::raw("COALESCE(c.RTN, '') as rtn"),
                'hf.observaciones as obs_revision',
                'hf.estado_id',
                'cr.estado as estado_credito',
                'cr.fecha_aprobacion',
                'cr.fecha_vencimiento_credito',
                'cr.motivo_rechazo',
                'cr.observaciones as obs_credito',
                'ur.name as usuario_aprobador'
            )
            ->groupBy(
                'f.id', 'f.identificacion', 'hf.created_at', 'hf.updated_at',
                'hfof.tramite_id', 'c.cliente_id', 'c.fecha_emision', 'c.fecha_vencimiento', 'c.total',
                'c.nombre_cliente', 'p.observaciones', 'c.RTN',
                'hf.observaciones', 'hf.estado_id', 'cr.estado',
                'cr.fecha_aprobacion', 'cr.fecha_vencimiento_credito', 'cr.motivo_rechazo',
                'cr.observaciones', 'ur.name'
            );

        switch ($tipo) {
            case 'llegando':
                // Flujos cuyo tipo actual es 10 y el historico pendiente (estado_id=5)
                $q->where('f.tipo_tramite_id', 10)->where('hf.estado_id', 5);
                break;
            case 'aprobadas':
                $q->where('hf.estado_id', 1);
                break;
            case 'rechazadas':
                $q->where('hf.estado_id', 3);
                break;
        }

        if ($term !== '') {
            $like = '%' . $term . '%';
            if (is_numeric($term)) {
                $q->where(function ($s) use ($term) {
                    $s->where('f.id', (int) $term)
                      ->orWhere('f.identificacion', $term)
                      ->orWhere('hfof.tramite_id', (int) $term);
                });
            } else {
                $q->where(function ($s) use ($like) {
                    $s->where('c.nombre_cliente', 'LIKE', $like)
                      ->orWhere('c.RTN', 'LIKE', $like)
                      ->orWhere('p.observaciones', 'LIKE', $like);
                });
            }
        }

        return $q->orderByDesc('hf.created_at')->get()->map(fn($r) => (array) $r)->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────
    // DETALLE DE FLUJO
    // ─────────────────────────────────────────────────────────────────────

    public function seleccionarFlujo(int $flujoId): void
    {
        $this->flujoId          = $flujoId;
        $this->confirmAccion    = null;
        $this->fechaAprobacion  = '';
        $this->fechaVencimiento = '';
        $this->motivoRechazo    = '';
        $this->observaciones    = '';
        $this->mensajeExito     = '';
        $this->mensajeError     = '';
        $this->numeroOrdenCompra  = null;
        $this->archivoOrdenCompra = null;
        $this->numeroFormaF01     = null;
        $this->archivoFormaF01    = null;

        // Info del flujo
        $flujoResult = DB::table('flujo as f')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'p.cliente_id')
            ->leftJoin('historico_flujo as hfof', function ($j) {
                $j->on('hfof.flujo_id', '=', 'f.id')
                  ->where('hfof.tipo_tramite_id', 2)
                  ->where('hfof.observaciones', 'ganadora');
            })
            ->leftJoin('cotizacion as c', 'c.id', '=', 'hfof.tramite_id')
            ->where('f.id', $flujoId)
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'p.id as pedido_id',
                DB::raw("COALESCE(c.nombre_cliente, cl.nombre, 'N/A') as cliente"),
                DB::raw('COALESCE(c.cliente_id, cl.id) as cliente_id'),
                'p.created_at as pedido_fecha',
                'p.observaciones as pedido_obs',
                'hfof.tramite_id as cotizacion_id',
                'c.total as monto_total_oferta',
                'c.fecha_emision as fecha_emision_oferta',
                'c.fecha_vencimiento as fecha_vencimiento_oferta',
                'f.numero_orden_compra',
                'f.archivo_orden_compra',
                'f.numero_forma_f01',
                'f.archivo_forma_f01'
            )
            ->first();
        $this->flujoData = $flujoResult ? (array) $flujoResult : null;
        $this->clienteId = $flujoResult && $flujoResult->cliente_id ? (int) $flujoResult->cliente_id : null;

        $this->fechaEmisionOferta = $flujoResult && $flujoResult->fecha_emision_oferta
            ? Carbon::parse($flujoResult->fecha_emision_oferta)->toDateString()
            : null;
        $this->fechaVencimientoOferta = $flujoResult && $flujoResult->fecha_vencimiento_oferta
            ? Carbon::parse($flujoResult->fecha_vencimiento_oferta)->toDateString()
            : null;
        $this->montoTotalOferta = (float) ($flujoResult->monto_total_oferta ?? 0);
        $this->numeroOrdenCompra  = $flujoResult->numero_orden_compra  ?? null;
        $this->archivoOrdenCompra = $flujoResult->archivo_orden_compra ?? null;
        $this->numeroFormaF01     = $flujoResult->numero_forma_f01     ?? null;
        $this->archivoFormaF01    = $flujoResult->archivo_forma_f01    ?? null;
        $this->diasSolicitadosCredito = $this->calcularDiasSolicitados(
            $this->fechaEmisionOferta,
            $this->fechaVencimientoOferta
        );
        $this->tipoPagoSolicitud = $this->diasSolicitadosCredito > 0 ? 'credito' : 'contado';

        // Oferta ganadora
        $hfGanadora = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)
            ->where('observaciones', 'ganadora')
            ->orderByDesc('id')
            ->first(['tramite_id']);

        $this->cotizacionId = $hfGanadora ? (int) $hfGanadora->tramite_id : null;
        $cotizacionIdCredito = DB::table('credito_revision')
            ->where('flujo_id', $flujoId)
            ->whereNotNull('cotizacion_id')
            ->orderByDesc('id')
            ->value('cotizacion_id');

        $cotizacionIdUltimaOferta = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)
            ->orderByDesc('id')
            ->value('tramite_id');

        $candidatasCotizacion = array_values(array_unique(array_filter([
            $this->cotizacionId,
            $cotizacionIdCredito ? (int) $cotizacionIdCredito : null,
            $cotizacionIdUltimaOferta ? (int) $cotizacionIdUltimaOferta : null,
        ])));

        $necesitaCompletarOferta =
            !$this->fechaEmisionOferta ||
            !$this->fechaVencimientoOferta ||
            $this->montoTotalOferta <= 0;

        if ($necesitaCompletarOferta && count($candidatasCotizacion) > 0) {
            foreach ($candidatasCotizacion as $cotizacionCandId) {
                $oferta = DB::table('cotizacion')
                    ->where('id', $cotizacionCandId)
                    ->first(['id', 'cliente_id', 'fecha_emision', 'fecha_vencimiento', 'total']);

                if (!$oferta) {
                    continue;
                }

                if (!$this->cotizacionId) {
                    $this->cotizacionId = (int) $oferta->id;
                }
                $this->clienteId = $this->clienteId ?: (int) ($oferta->cliente_id ?? 0);
                $this->fechaEmisionOferta = $this->fechaEmisionOferta ?: ($oferta->fecha_emision ? Carbon::parse($oferta->fecha_emision)->toDateString() : null);
                $this->fechaVencimientoOferta = $this->fechaVencimientoOferta ?: ($oferta->fecha_vencimiento ? Carbon::parse($oferta->fecha_vencimiento)->toDateString() : null);
                if ($this->montoTotalOferta <= 0) {
                    $this->montoTotalOferta = (float) ($oferta->total ?? 0);
                }
            }

            $this->diasSolicitadosCredito = $this->calcularDiasSolicitados($this->fechaEmisionOferta, $this->fechaVencimientoOferta);
            $this->tipoPagoSolicitud = $this->diasSolicitadosCredito > 0 ? 'credito' : 'contado';
        }

        $this->cargarDatosCreditoCliente();

        // Estado del crédito
        $cr = CreditoRevision::paraFlujo($flujoId);
        if ($cr) {
            $this->estadoCredito           = $cr->estado;
            $this->fechaAprobacionActual   = $cr->fecha_aprobacion
                ? Carbon::parse($cr->fecha_aprobacion)->format('Y-m-d') : null;
            $this->fechaVencimientoActual  = $cr->fecha_vencimiento_credito
                ? Carbon::parse($cr->fecha_vencimiento_credito)->format('Y-m-d') : null;
            $this->motivoRechazoActual     = $cr->motivo_rechazo;
            $this->obsAprobacionActual     = $cr->observaciones;
            $this->usuarioAprobadorActual  = $cr->usuario_revision
                ? DB::table('users')->where('id', $cr->usuario_revision)->value('name')
                : null;

            // Historial
            $this->historialCredito = DB::table('credito_revision_historial as crh')
                ->leftJoin('users as u', 'u.id', '=', 'crh.usuario_id')
                ->where('crh.credito_revision_id', $cr->id)
                ->orderByDesc('crh.id')
                ->select('crh.accion', 'crh.estado_anterior', 'crh.estado_nuevo',
                         'crh.descripcion', 'crh.fecha_evento', 'u.name as usuario_nombre')
                ->get()
                ->map(fn($r) => (array) $r)
                ->toArray();
        } else {
            $this->estadoCredito          = CreditoRevision::PENDIENTE;
            $this->fechaAprobacionActual  = null;
            $this->fechaVencimientoActual = null;
            $this->motivoRechazoActual    = null;
            $this->obsAprobacionActual    = null;
            $this->usuarioAprobadorActual = null;
            $this->historialCredito       = [];
        }
    }

    public function cerrarDetalle(): void
    {
        $this->flujoId                = null;
        $this->flujoData              = null;
        $this->cotizacionId           = null;
        $this->clienteId              = null;
        $this->tipoPagoSolicitud      = 'contado';
        $this->fechaEmisionOferta     = null;
        $this->fechaVencimientoOferta = null;
        $this->diasSolicitadosCredito = 0;
        $this->montoTotalOferta       = 0.0;
        $this->estadoCredito          = null;
        $this->fechaAprobacionActual  = null;
        $this->fechaVencimientoActual = null;
        $this->motivoRechazoActual    = null;
        $this->obsAprobacionActual    = null;
        $this->usuarioAprobadorActual = null;
        $this->historialCredito       = [];
        $this->confirmAccion          = null;
        $this->fechaAprobacion        = '';
        $this->fechaVencimiento       = '';
        $this->motivoRechazo          = '';
        $this->observaciones          = '';
        $this->montoCreditoActual     = 0.0;
        $this->montoDisponibleActual  = 0.0;
        $this->diasCreditoActual      = 0;
        $this->montoCreditoEditable   = 0.0;
        $this->montoCreditoEditableTexto = '0.00';
        $this->diasCreditoEditable    = 0;
        $this->puedeAutorizar         = false;
        $this->bloqueosAutorizacion   = [];
        $this->mensajeExito           = '';
        $this->mensajeError           = '';
    }

    private function calcularDiasSolicitados(?string $fechaEmision, ?string $fechaVencimiento): int
    {
        if (!$fechaEmision || !$fechaVencimiento) {
            return 0;
        }

        try {
            $emision = Carbon::createFromFormat('Y-m-d', $fechaEmision)->startOfDay();
            $vence   = Carbon::createFromFormat('Y-m-d', $fechaVencimiento)->startOfDay();
            return max(0, $emision->diffInDays($vence, false));
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function cargarDatosCreditoCliente(): void
    {
        if (!$this->clienteId) {
            $this->montoCreditoActual    = 0.0;
            $this->montoDisponibleActual = 0.0;
            $this->diasCreditoActual     = 0;
            $this->montoCreditoEditable  = 0.0;
            $this->diasCreditoEditable   = 0;
            $this->bloqueosAutorizacion  = ['No se encontró cliente vinculado para validar crédito.'];
            $this->puedeAutorizar        = false;
            return;
        }

        $cliente = DB::table('cliente')
            ->where('id', $this->clienteId)
            ->first(['credito_inicial', 'dias_credito', 'vendedor']);

        $creditoCliente = DB::table('cliente_credito')
            ->where('cliente_id', $this->clienteId)
            ->orderByDesc('id')
            ->first(['credito', 'dias_credito']);

        $this->montoCreditoActual = (float) ($creditoCliente->credito ?? $cliente->credito_inicial ?? 0);
        $this->diasCreditoActual  = (int) ($creditoCliente->dias_credito ?? $cliente->dias_credito ?? 0);

        $this->montoCreditoEditable = $this->montoCreditoActual;
        $this->montoCreditoEditableTexto = number_format($this->montoCreditoEditable, 2, '.', ',');
        $this->diasCreditoEditable  = $this->diasCreditoActual;
        $this->montoDisponibleActual = CreditoService::calcularDisponible((int) $this->clienteId, $this->montoCreditoEditable);

        $this->evaluarReglasAutorizacion();
    }

    public function updatedMontoCreditoEditable($value): void
    {
        $monto = is_numeric($value) ? (float) $value : 0.0;
        $this->montoCreditoEditable = max(0.0, $monto);
        if ($this->clienteId) {
            $this->montoDisponibleActual = CreditoService::calcularDisponible((int) $this->clienteId, $this->montoCreditoEditable);
        }
        $this->evaluarReglasAutorizacion();
    }

    public function updatedMontoCreditoEditableTexto($value): void
    {
        $valorLimpio = str_replace(',', '', (string) $value);
        $monto = is_numeric($valorLimpio) ? (float) $valorLimpio : 0.0;
        $this->montoCreditoEditable = max(0.0, $monto);
        $this->montoCreditoEditableTexto = number_format($this->montoCreditoEditable, 2, '.', ',');

        if ($this->clienteId) {
            $this->montoDisponibleActual = CreditoService::calcularDisponible((int) $this->clienteId, $this->montoCreditoEditable);
        }
        $this->evaluarReglasAutorizacion();
    }

    public function updatedDiasCreditoEditable($value): void
    {
        $dias = is_numeric($value) ? (int) $value : 0;
        $this->diasCreditoEditable = max(0, $dias);
        $this->evaluarReglasAutorizacion();
    }

    private function evaluarReglasAutorizacion(): void
    {
        // Facturas de contado no requieren validación de crédito
        if ($this->tipoPagoSolicitud === 'contado') {
            $this->bloqueosAutorizacion = [];
            $this->puedeAutorizar = true;
            return;
        }

        $bloqueos = [];

        if ($this->montoTotalOferta > $this->montoDisponibleActual) {
            $bloqueos[] = 'El monto de la factura excede el crédito disponible del cliente.';
        }

        if ($this->diasSolicitadosCredito > $this->diasCreditoEditable) {
            $bloqueos[] = 'Los días solicitados exceden los días de crédito permitidos para el cliente.';
        }

        $this->bloqueosAutorizacion = $bloqueos;
        $this->puedeAutorizar = empty($bloqueos);
    }

    // DEPRECATED: Esta función ya no debe usarse.
    // Los ajustes de crédito de un flujo se persisten SOLO en credito_revision,
    // sin modificar la tabla global cliente.
    // Mantenida solo como referencia histórica.
    /*
    private function aplicarAjustesCreditoCliente(): void
    {
        if (!$this->clienteId) {
            return;
        }

        $montoNuevo = (float) $this->montoCreditoEditable;
        $diasNuevo  = (int) $this->diasCreditoEditable;

        DB::table('cliente')
            ->where('id', $this->clienteId)
            ->update([
                'credito_inicial' => $montoNuevo,
                'dias_credito'    => $diasNuevo,
                'updated_at'      => now(),
            ]);

        DB::table('cliente_credito')
            ->where('cliente_id', $this->clienteId)
            ->update(['activo' => 0, 'updated_at' => now()]);

        $anterior = DB::table('cliente_credito')
            ->where('cliente_id', $this->clienteId)
            ->orderByDesc('id')
            ->first();

        DB::table('cliente_credito')->insert([
            'activo'                  => 1,
            'cliente_id'              => $this->clienteId,
            'credito_activo'          => 1,
            'credito'                 => $montoNuevo,
            'dias_credito'            => $diasNuevo,
            'fecha_vigencia'          => $anterior ? $anterior->fecha_vigencia : null,
            'vendedor_id'             => $anterior ? $anterior->vendedor_id : null,
            'referencias_bancarias'   => $anterior ? $anterior->referencias_bancarias : null,
            'referencias_comerciales' => $anterior ? $anterior->referencias_comerciales : null,
            'metodo_pago'             => $anterior ? $anterior->metodo_pago : null,
            'letra_cambio'            => $anterior ? $anterior->letra_cambio : 0,
            'obs_letra_cambio'        => $anterior ? $anterior->obs_letra_cambio : null,
            'aval_solidario'          => $anterior ? $anterior->aval_solidario : 0,
            'obs_aval_solidario'      => $anterior ? $anterior->obs_aval_solidario : null,
            'autorizacion_gerencia'   => $anterior ? $anterior->autorizacion_gerencia : null,
            'users_id'                => Auth::id(),
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);

        $this->montoDisponibleActual = CreditoService::actualizarDisponible((int) $this->clienteId, $montoNuevo);
        $this->montoCreditoActual = $montoNuevo;
        $this->montoCreditoEditableTexto = number_format($montoNuevo, 2, '.', ',');
        $this->diasCreditoActual  = $diasNuevo;
        $this->evaluarReglasAutorizacion();
    }
    */

    // ─────────────────────────────────────────────────────────────────────
    // ACCIONES
    // ─────────────────────────────────────────────────────────────────────

    public function confirmarAccion(string $accion): void
    {
        $this->confirmAccion    = $accion;
        $this->fechaAprobacion  = '';
        $this->fechaVencimiento = '';
        $this->motivoRechazo    = '';
        $this->observaciones    = '';
        $this->mensajeError     = '';

        if ($accion === 'aprobar') {
            $this->cargarDatosCreditoCliente();
        }
    }

    public function updatedFechaAprobacion(string $value): void
    {
        if ($value !== '') {
            try {
                $this->fechaVencimiento = Carbon::createFromFormat('Y-m-d', $value)
                    ->addDays(30)->toDateString();
            } catch (\Exception $e) {
                // fecha inválida, no actualizar
            }
        }
    }

    public function cancelarAccion(): void
    {
        $this->confirmAccion    = null;
        $this->fechaAprobacion  = '';
        $this->fechaVencimiento = '';
        $this->motivoRechazo    = '';
        $this->observaciones    = '';
        $this->mensajeError     = '';
    }

    /**
     * APROBAR crédito:
     * – Requiere fecha_aprobacion (obligatoria)
     * – fecha_vencimiento (opcional)
     * – Mueve el flujo a Revisión de Inventario (tipo 9)
     */
    public function aprobarCredito(): void
    {
        if (!$this->flujoId) return;

        $dtAprobacion = now();
        $dtVencimiento = $this->fechaVencimientoOferta
            ? Carbon::createFromFormat('Y-m-d', $this->fechaVencimientoOferta)
            : null;

        // Reglas obligatorias de autorización de crédito
        $this->evaluarReglasAutorizacion();
        if (!$this->puedeAutorizar) {
            $this->mensajeError = $this->bloqueosAutorizacion[0] ?? 'No es posible autorizar el crédito con los valores actuales.';
            return;
        }

        DB::beginTransaction();
        try {
            $ip = request()->ip();

            // Los datos de crédito editados en esta pantalla (monto, días) se persisten SOLO
            // en la tabla credito_revision de este flujo, sin modificar la configuración
            // global del cliente en la tabla cliente.

            $cr             = CreditoRevision::where('flujo_id', $this->flujoId)->latest('id')->first();
            $estadoAnterior = $cr ? $cr->estado : null;

            // Días de crédito aprobados por operación:
            // usan el valor editado en esta pantalla (puede diferir de cliente.dias_credito).
            // Para contado siempre es 0.
            $diasAprobados = ($this->tipoPagoSolicitud === 'contado')
                ? 0
                : max(0, (int) $this->diasCreditoEditable);

            if ($cr) {
                $cr->update([
                    'estado'                    => CreditoRevision::APROBADO,
                    'cotizacion_id'             => $this->cotizacionId,
                    'fecha_aprobacion'          => $dtAprobacion->toDateString(),
                    'fecha_vencimiento_credito' => $dtVencimiento ? $dtVencimiento->toDateString() : null,
                    'dias_credito_aprobados'    => $diasAprobados,
                    'motivo_rechazo'            => null,
                    'observaciones'             => trim($this->observaciones) ?: null,
                    'usuario_revision'          => Auth::id(),
                    'ip_revision'               => $ip,
                ]);
            } else {
                $cr = CreditoRevision::create([
                    'flujo_id'                  => $this->flujoId,
                    'cotizacion_id'             => $this->cotizacionId,
                    'estado'                    => CreditoRevision::APROBADO,
                    'fecha_aprobacion'          => $dtAprobacion->toDateString(),
                    'fecha_vencimiento_credito' => $dtVencimiento ? $dtVencimiento->toDateString() : null,
                    'dias_credito_aprobados'    => $diasAprobados,
                    'observaciones'             => trim($this->observaciones) ?: null,
                    'usuario_revision'          => Auth::id(),
                    'ip_revision'               => $ip,
                ]);
            }

            $cr->registrarHistorial(
                'aprobado',
                $estadoAnterior ?? CreditoRevision::PENDIENTE,
                CreditoRevision::APROBADO,
                'Crédito aprobado. Fecha: ' . $dtAprobacion->format('d/m/Y')
                . ($dtVencimiento ? '. Vence: ' . $dtVencimiento->format('d/m/Y') : '')
                . '. Oferta: L ' . number_format((float) $this->montoTotalOferta, 2)
                . '. Días solicitados: ' . (int) $this->diasSolicitadosCredito,
                $ip
            );

            // Cerrar historico_flujo tipo=10 como aprobado
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 10)
                ->where('estado_id', 5)
                ->update([
                    'estado_id'     => 1,
                    'observaciones' => 'Crédito aprobado. Fecha: ' . $dtAprobacion->format('d/m/Y'),
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            // Crear o reactivar Revisión de Inventario (tipo 9)
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
                        'observaciones' => 'Reactivado. Crédito aprobado por ' . Auth::user()->name,
                        'updated_by'    => Auth::id(),
                        'updated_at'    => now(),
                    ]);
            } else {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $this->flujoId,
                    'tipo_tramite_id' => 9,
                    'tramite_id'      => $this->cotizacionId,
                    'estado_id'       => 5,
                    'observaciones'   => 'En Revisión de Inventario. Crédito aprobado por ' . Auth::user()->name,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // Avanzar flujo a Revisión de Inventario
            DB::table('flujo')->where('id', $this->flujoId)->update([
                'tipo_tramite_id' => 9,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            $flujoIdCerrado = $this->flujoId;

            // Notificar al personal de logística/inventario
            try {
                $flujoCtx = DB::table('flujo')
                    ->where('id', $flujoIdCerrado)
                    ->select('nombre as cliente')
                    ->first();
                event(new FlujoAvanzadoEvent(
                    $flujoIdCerrado,
                    9,
                    ['cliente' => $flujoCtx?->cliente ?? 'N/A', 'monto' => $this->montoTotalOferta ?? null]
                ));
            } catch (\Throwable $notifEx) {
                \Log::error('NotificacionFlujo dispatch failed (RevisionCreditos tipo=9)', [
                    'flujo_id' => $flujoIdCerrado,
                    'error'    => $notifEx->getMessage(),
                ]);
            }

            $this->cerrarDetalle();
            $this->cargar();
            $this->mensajeExito = 'Flujo #' . $flujoIdCerrado . ': Crédito aprobado y enviado a Revisión de Inventario.';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al aprobar el crédito: ' . $e->getMessage();
        }
    }

    /**
     * RECHAZAR crédito:
     * – Requiere motivo (obligatorio)
     * – Cancela el flujo completamente
     */
    public function rechazarCredito(): void
    {
        if (!$this->flujoId) return;

        $motivo = trim($this->motivoRechazo);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo de rechazo.';
            return;
        }

        DB::beginTransaction();
        try {
            $ip = request()->ip();

            $cr             = CreditoRevision::where('flujo_id', $this->flujoId)->latest('id')->first();
            $estadoAnterior = $cr ? $cr->estado : null;

            if ($cr) {
                $cr->update([
                    'estado'           => CreditoRevision::RECHAZADO,
                    'cotizacion_id'    => $this->cotizacionId,
                    'motivo_rechazo'   => $motivo,
                    'observaciones'    => trim($this->observaciones) ?: null,
                    'usuario_revision' => Auth::id(),
                    'ip_revision'      => $ip,
                ]);
            } else {
                $cr = CreditoRevision::create([
                    'flujo_id'         => $this->flujoId,
                    'cotizacion_id'    => $this->cotizacionId,
                    'estado'           => CreditoRevision::RECHAZADO,
                    'motivo_rechazo'   => $motivo,
                    'observaciones'    => trim($this->observaciones) ?: null,
                    'usuario_revision' => Auth::id(),
                    'ip_revision'      => $ip,
                ]);
            }

            $cr->registrarHistorial(
                'rechazado',
                $estadoAnterior ?? CreditoRevision::PENDIENTE,
                CreditoRevision::RECHAZADO,
                'Crédito rechazado. Motivo: ' . $motivo,
                $ip
            );

            // Cerrar historico_flujo tipo=10 como rechazado (estado_id=3)
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 10)
                ->where('estado_id', 5)
                ->update([
                    'estado_id'     => 3,
                    'observaciones' => 'Crédito rechazado. Motivo: ' . $motivo,
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            // Cancelar el flujo
            $canceladoId = DB::table('estado_venta')
                ->where('descripcion', 'cancelado')
                ->value('id') ?? 4;

            DB::table('flujo')
                ->where('id', $this->flujoId)
                ->update([
                    'estado_id'       => $canceladoId,
                    'tipo_tramite_id' => 10,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);

            $cr->registrarHistorial(
                'cancelado',
                CreditoRevision::RECHAZADO,
                CreditoRevision::CANCELADO,
                'Flujo cancelado por rechazo de crédito.',
                $ip
            );

            DB::commit();

            $flujoIdCerrado = $this->flujoId;
            $this->cerrarDetalle();
            $this->cargar();
            $this->mensajeExito = 'Flujo #' . $flujoIdCerrado . ': Crédito rechazado. El flujo ha sido cancelado.';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al rechazar el crédito: ' . $e->getMessage();
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────

    public function render()
    {
        $paginaActual     = $this->paginas[$this->tabActiva] ?? 1;
        $registrosActivos = match($this->tabActiva) {
            'aprobadas'  => $this->bandejaAprobadas,
            'rechazadas' => $this->bandejaRechazadas,
            default      => $this->bandejaLlegando,
        };
        $totalRegistros  = count($registrosActivos);
        $totalPaginas    = max(1, (int) ceil($totalRegistros / $this->perPage));
        $offset          = ($paginaActual - 1) * $this->perPage;
        $registrosPagina = array_slice($registrosActivos, $offset, $this->perPage);

        return view('livewire.flujo.revisioncreditos', [
            'flujoData'       => $this->flujoData,
            'registrosPagina' => $registrosPagina,
            'totalRegistros'  => $totalRegistros,
            'totalPaginas'    => $totalPaginas,
            'paginaActual'    => $paginaActual,
        ]);
    }
}
