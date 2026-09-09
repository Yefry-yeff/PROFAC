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
    public ?array $flujoData        = null;
    public ?int   $cotizacionId     = null;
    public bool   $esSeccionExpo    = false;
    public ?int   $clienteId        = null;
    public string $tipoPagoSolicitud = 'contado';
    public ?string $fechaEmisionOferta = null;
    public ?string $fechaVencimientoOferta = null;
    public int    $diasSolicitadosCredito = 0;
    public float  $montoTotalOferta = 0.0;
    public ?string $comentarioOferta = null;
    public ?string $comentarioCreditoOferta = null;
    // Documentos de la oferta
    public ?string $numeroOrdenCompra    = null;
    public ?string $archivoOrdenCompra   = null;
    public ?string $numeroFormaF01       = null;
    public ?string $archivoFormaF01      = null;

    // ── Estado crédito actual ─────────────────────────────────────────────
    public ?string $estadoCredito           = null;
    public ?string $fechaAprobacionActual   = null;
    public ?string $fechaVencimientoActual  = null;
    public ?int    $diasCreditoAprobadosActual = null;
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
    public float $montoDisponibleProyectado = 0.0;
    public int   $diasCreditoActual       = 0;
    public float $montoCreditoEditable    = 0.0;
    public string $montoCreditoEditableTexto = '0.00';
    public int   $diasCreditoEditable     = 0;
    public bool  $puedeAutorizar          = false;
    public array $bloqueosAutorizacion    = [];
    public bool  $modalMovimientosCreditoVisible = false;
    public array $detalleMovimientosCredito = [];
    public bool  $modalAjusteCreditoVisible = false;
    public string $nuevoCreditoClienteTexto = '0.00';
    public int $nuevosDiasCreditoCliente = 0;
    public string $motivoAjusteCredito = '';

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
            $cotizacionId = request()->integer('cotizacion_id') ?: null;
            $this->seleccionarFlujo($flujoId, $cotizacionId);
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
        $latestRevSub = DB::table('historico_flujo')
            ->select('flujo_id', DB::raw('MAX(id) as max_id'))
            ->where('tipo_tramite_id', 10)
            ->groupBy('flujo_id');
        $latestOfertaSub = DB::table('historico_flujo')
            ->select('flujo_id', DB::raw('MAX(id) as max_id'))
            ->where('tipo_tramite_id', 2)
            ->groupBy('flujo_id');
        $latestCreditoNormalSub = DB::table('credito_revision')
            ->select('flujo_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('flujo_id');

        $normal = DB::table('flujo as f')
            ->joinSub($latestRevSub, 'lrev', fn ($join) => $join->on('lrev.flujo_id', '=', 'f.id'))
            ->join('historico_flujo as hf', 'hf.id', '=', 'lrev.max_id')
            ->leftJoinSub($latestOfertaSub, 'lof', fn ($join) => $join->on('lof.flujo_id', '=', 'f.id'))
            ->leftJoin('historico_flujo as hfof', 'hfof.id', '=', 'lof.max_id')
            ->leftJoin('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->leftJoin('cotizacion as co', 'co.id', '=', 'hfof.tramite_id')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoinSub($latestCreditoNormalSub, 'lcr', fn ($join) => $join->on('lcr.flujo_id', '=', 'f.id'))
            ->leftJoin('credito_revision as cr', 'cr.id', '=', 'lcr.max_id')
            ->leftJoin('users as ur', 'ur.id', '=', 'cr.usuario_revision')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('expo_oferta_seccion as eos_normal')
                    ->whereColumn('eos_normal.flujo_id', 'f.id');
            })
            ->select(
                'f.id as flujo_id', 'f.identificacion', 'hf.created_at as fecha_revision',
                'hf.updated_at as fecha_accion',
                DB::raw('COALESCE(hf.tramite_id, hfof.tramite_id, cr.cotizacion_id) as cotizacion_id'),
                DB::raw('COALESCE(c.cliente_id, co.cliente_id) as cliente_id'),
                DB::raw('COALESCE(c.fecha_emision, co.fecha_emision, cr.fecha_emision_solicitada) as fecha_emision_oferta'),
                DB::raw('COALESCE(c.fecha_vencimiento, co.fecha_vencimiento, cr.fecha_vencimiento_solicitada) as fecha_vencimiento_oferta'),
                DB::raw('COALESCE(c.total, co.total, 0) as monto_total_oferta'),
                DB::raw('COALESCE(cr.dias_credito_solicitados, GREATEST(DATEDIFF(COALESCE(c.fecha_vencimiento, co.fecha_vencimiento), COALESCE(c.fecha_emision, co.fecha_emision)), 0), 0) as dias_solicitados_credito'),
                DB::raw("COALESCE(c.nombre_cliente, co.nombre_cliente, p.observaciones, CONCAT('Flujo #', f.id)) as cliente"),
                DB::raw("COALESCE(c.RTN, co.RTN, '') as rtn"),
                DB::raw('0 as es_expo'),
                DB::raw('NULL as seccion_numero'), DB::raw('NULL as seccion_nombre'),
                DB::raw('NULL as seccion_estado'), 'cr.estado as estado_credito',
                'cr.fecha_aprobacion', 'cr.fecha_vencimiento_credito', 'cr.motivo_rechazo',
                'cr.observaciones as obs_credito', 'ur.name as usuario_aprobador'
            );

        if ($tipo === 'llegando') {
            $normal->where('f.tipo_tramite_id', 10)->where('hf.estado_id', 5);
        } elseif ($tipo === 'aprobadas') {
            $normal->where('hf.estado_id', 1);
        } else {
            $normal->where('hf.estado_id', 3);
        }

        if ($term !== '') {
            $like = '%' . $term . '%';
            $normal->where(function ($query) use ($term, $like) {
                if (is_numeric($term)) {
                    $query->where('f.id', (int) $term)->orWhere('f.identificacion', $term)
                        ->orWhere('hf.tramite_id', (int) $term)->orWhere('hfof.tramite_id', (int) $term);
                } else {
                    $query->where('c.nombre_cliente', 'LIKE', $like)->orWhere('co.nombre_cliente', 'LIKE', $like)
                        ->orWhere('c.RTN', 'LIKE', $like)->orWhere('co.RTN', 'LIKE', $like)
                        ->orWhere('p.observaciones', 'LIKE', $like);
                }
            });
        }

        $latestCreditoExpoSub = DB::table('credito_revision')
            ->select('flujo_id', 'cotizacion_id', DB::raw('MAX(id) as max_id'))
            ->whereNotNull('cotizacion_id')
            ->groupBy('flujo_id', 'cotizacion_id');

        $expo = DB::table('credito_revision as cr')
            ->joinSub($latestCreditoExpoSub, 'lcr', fn ($join) => $join->on('lcr.max_id', '=', 'cr.id'))
            ->join('flujo as f', 'f.id', '=', 'cr.flujo_id')
            ->join('cotizacion as c', 'c.id', '=', 'cr.cotizacion_id')
            ->join('expo_oferta_seccion as eos', function ($join) {
                $join->on('eos.cotizacion_id', '=', 'c.id')->on('eos.flujo_id', '=', 'f.id');
            })
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('users as ur', 'ur.id', '=', 'cr.usuario_revision')
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'cr.created_at as fecha_revision',
                'cr.updated_at as fecha_accion',
                'cr.cotizacion_id',
                'c.cliente_id',
                DB::raw('COALESCE(cr.fecha_emision_solicitada, c.fecha_emision) as fecha_emision_oferta'),
                DB::raw('COALESCE(cr.fecha_vencimiento_solicitada, c.fecha_vencimiento) as fecha_vencimiento_oferta'),
                DB::raw('COALESCE(c.total, 0) as monto_total_oferta'),
                DB::raw('COALESCE(cr.dias_credito_solicitados, 0) as dias_solicitados_credito'),
                DB::raw("COALESCE(c.nombre_cliente, p.observaciones, CONCAT('Flujo #', f.id)) as cliente"),
                DB::raw("COALESCE(c.RTN, '') as rtn"),
                DB::raw('1 as es_expo'),
                'eos.numero as seccion_numero',
                'eos.nombre as seccion_nombre',
                'eos.estado as seccion_estado',
                'cr.estado as estado_credito',
                'cr.fecha_aprobacion',
                'cr.fecha_vencimiento_credito',
                'cr.motivo_rechazo',
                'cr.observaciones as obs_credito',
                'ur.name as usuario_aprobador'
            );

        switch ($tipo) {
            case 'llegando':
                $expo->where('cr.estado', CreditoRevision::PENDIENTE);
                break;
            case 'aprobadas':
                $expo->where('cr.estado', CreditoRevision::APROBADO);
                break;
            case 'rechazadas':
                $expo->whereIn('cr.estado', [CreditoRevision::RECHAZADO, CreditoRevision::CANCELADO]);
                break;
        }

        if ($term !== '') {
            $like = '%' . $term . '%';
            if (is_numeric($term)) {
                $expo->where(function ($s) use ($term) {
                    $s->where('f.id', (int) $term)
                      ->orWhere('f.identificacion', $term)
                                            ->orWhere('cr.cotizacion_id', (int) $term)
                                            ->orWhere('eos.numero', (int) $term);
                });
            } else {
                $expo->where(function ($s) use ($like) {
                    $s->where('c.nombre_cliente', 'LIKE', $like)
                      ->orWhere('c.RTN', 'LIKE', $like)
                                            ->orWhere('eos.nombre', 'LIKE', $like)
                      ->orWhere('p.observaciones', 'LIKE', $like);
                });
            }
        }

        return $normal->get()->concat($expo->get())
            ->sortByDesc('fecha_revision')
            ->map(fn ($registro) => (array) $registro)
            ->values()
            ->all();
    }

    // ─────────────────────────────────────────────────────────────────────
    // DETALLE DE FLUJO
    // ─────────────────────────────────────────────────────────────────────

    public function seleccionarFlujo(int $flujoId, ?int $cotizacionId = null): void
    {
        $this->flujoId          = $flujoId;
        $this->confirmAccion    = null;
        $this->fechaAprobacion  = '';
        $this->fechaVencimiento = '';
        $this->motivoRechazo    = '';
        $this->observaciones    = '';
        $this->mensajeExito     = '';
        $this->mensajeError     = '';
        $this->comentarioOferta = null;
        $this->comentarioCreditoOferta = null;
        $this->numeroOrdenCompra  = null;
        $this->archivoOrdenCompra = null;
        $this->numeroFormaF01     = null;
        $this->archivoFormaF01    = null;

        $this->esSeccionExpo = $cotizacionId && DB::table('expo_oferta_seccion')
            ->where('flujo_id', $flujoId)
            ->where('cotizacion_id', $cotizacionId)
            ->exists();
        if ($this->esSeccionExpo) {
            $this->cotizacionId = $cotizacionId;
        } else {
            $this->cotizacionId = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('observaciones', 'ganadora')
                ->latest('id')
                ->value('tramite_id');
            $this->cotizacionId = $this->cotizacionId ?: DB::table('credito_revision')
                ->where('flujo_id', $flujoId)->whereNotNull('cotizacion_id')->latest('id')->value('cotizacion_id');
            $this->cotizacionId = $this->cotizacionId ?: DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)->where('tipo_tramite_id', 2)->latest('id')->value('tramite_id');
        }

        // Info del flujo y de la sección seleccionada
        $flujoResult = DB::table('flujo as f')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'p.cliente_id')
                        ->leftJoin('cotizacion as c', fn ($join) => $join->on('c.id', '=', DB::raw((int) ($this->cotizacionId ?? 0))))
            ->where('f.id', $flujoId)
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'p.id as pedido_id',
                DB::raw("COALESCE(c.nombre_cliente, cl.nombre, 'N/A') as cliente"),
                DB::raw("COALESCE(c.RTN, cl.rtn, '') as rtn"),
                DB::raw('COALESCE(c.cliente_id, cl.id) as cliente_id'),
                'p.created_at as pedido_fecha',
                'p.observaciones as pedido_obs',
                'c.id as cotizacion_id',
                'c.total as monto_total_oferta',
                'c.nota as comentario_oferta',
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
        $this->comentarioOferta = trim((string) ($flujoResult->comentario_oferta ?? '')) ?: null;
        $this->numeroOrdenCompra  = $flujoResult->numero_orden_compra  ?? null;
        $this->archivoOrdenCompra = $flujoResult->archivo_orden_compra ?? null;
        $this->numeroFormaF01     = $flujoResult->numero_forma_f01     ?? null;
        $this->archivoFormaF01    = $flujoResult->archivo_forma_f01    ?? null;
        $this->diasSolicitadosCredito = $this->calcularDiasSolicitados(
            $this->fechaEmisionOferta,
            $this->fechaVencimientoOferta
        );
        $this->tipoPagoSolicitud = $this->diasSolicitadosCredito > 0 ? 'credito' : 'contado';

        $comentarioCreditoOferta = DB::table('flujo_oferta_credito_comentarios')
            ->where('flujo_id', $flujoId)
            ->when($this->cotizacionId, fn($q) => $q->where('tramite_id', $this->cotizacionId))
            ->orderByDesc('id')
            ->value('observacion');

        if (!$comentarioCreditoOferta && !$this->esSeccionExpo) {
            $comentarioCreditoOferta = DB::table('flujo_oferta_credito_comentarios')
                ->where('flujo_id', $flujoId)
                ->orderByDesc('id')
                ->value('observacion');
        }

        $this->comentarioCreditoOferta = trim((string) ($comentarioCreditoOferta ?? '')) ?: null;

        $this->cargarDatosCreditoCliente();

        // Estado del crédito
        $cr = $this->esSeccionExpo
            ? CreditoRevision::paraSeccion($flujoId, (int) $this->cotizacionId)
            : CreditoRevision::paraFlujo($flujoId);
        if ($cr) {
            $this->estadoCredito           = $cr->estado;
            $this->fechaAprobacionActual   = $cr->fecha_aprobacion
                ? Carbon::parse($cr->fecha_aprobacion)->format('Y-m-d') : null;
            $this->fechaVencimientoActual  = $cr->fecha_vencimiento_credito
                ? Carbon::parse($cr->fecha_vencimiento_credito)->format('Y-m-d') : null;
            $this->diasCreditoAprobadosActual = !is_null($cr->dias_credito_aprobados)
                ? (int) $cr->dias_credito_aprobados
                : $this->calcularDiasSolicitados($this->fechaAprobacionActual, $this->fechaVencimientoActual);
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
            $this->diasCreditoAprobadosActual = null;
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
        $this->esSeccionExpo          = false;
        $this->clienteId              = null;
        $this->tipoPagoSolicitud      = 'contado';
        $this->fechaEmisionOferta     = null;
        $this->fechaVencimientoOferta = null;
        $this->diasSolicitadosCredito = 0;
        $this->montoTotalOferta       = 0.0;
        $this->comentarioOferta       = null;
        $this->comentarioCreditoOferta = null;
        $this->estadoCredito          = null;
        $this->fechaAprobacionActual  = null;
        $this->fechaVencimientoActual = null;
        $this->diasCreditoAprobadosActual = null;
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
        $this->montoDisponibleProyectado = 0.0;
        $this->diasCreditoActual      = 0;
        $this->montoCreditoEditable   = 0.0;
        $this->montoCreditoEditableTexto = '0.00';
        $this->diasCreditoEditable    = 0;
        $this->puedeAutorizar         = false;
        $this->bloqueosAutorizacion   = [];
        $this->modalMovimientosCreditoVisible = false;
        $this->detalleMovimientosCredito = [];
        $this->modalAjusteCreditoVisible = false;
        $this->nuevoCreditoClienteTexto = '0.00';
        $this->nuevosDiasCreditoCliente = 0;
        $this->motivoAjusteCredito = '';
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
            $this->montoDisponibleProyectado = -$this->montoTotalOferta;
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
        $detalleCredito = CreditoService::obtenerDetalleMovimientos((int) $this->clienteId);
        $this->montoDisponibleActual = (float) $detalleCredito['saldo_pendiente'];
        $this->montoDisponibleProyectado = $this->montoDisponibleActual - $this->montoTotalOferta;

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
        if (!$this->clienteId) {
            $this->bloqueosAutorizacion = ['No se encontró cliente vinculado para validar crédito.'];
            $this->puedeAutorizar = false;
            return;
        }

        // Facturas de contado no requieren validación de crédito
        if ($this->tipoPagoSolicitud === 'contado') {
            $this->bloqueosAutorizacion = [];
            $this->puedeAutorizar = true;
            return;
        }

        $bloqueos = [];

        if ($this->diasCreditoEditable > $this->diasSolicitadosCredito) {
            $bloqueos[] = 'Los días aprobados no pueden ser mayores que los días solicitados en el flujo.';
        }

        $this->bloqueosAutorizacion = $bloqueos;
        $this->puedeAutorizar = empty($bloqueos);
    }

    public function abrirMovimientosCredito(): void
    {
        if (!$this->clienteId) {
            return;
        }

        $this->detalleMovimientosCredito = CreditoService::obtenerDetalleMovimientos($this->clienteId);
        $this->modalMovimientosCreditoVisible = true;
    }

    public function cerrarMovimientosCredito(): void
    {
        $this->modalMovimientosCreditoVisible = false;
    }

    public function abrirAjusteCredito(): void
    {
        if (!$this->clienteId) {
            $this->mensajeError = 'No se encontró cliente vinculado para modificar el crédito.';
            return;
        }

        $this->nuevoCreditoClienteTexto = number_format($this->montoCreditoActual, 2, '.', ',');
        $this->nuevosDiasCreditoCliente = $this->diasCreditoActual;
        $this->motivoAjusteCredito = '';
        $this->mensajeError = '';
        $this->modalAjusteCreditoVisible = true;
    }

    public function cerrarAjusteCredito(): void
    {
        $this->modalAjusteCreditoVisible = false;
        $this->motivoAjusteCredito = '';
    }

    public function guardarAjusteCredito(): void
    {
        if (!$this->clienteId || !$this->flujoId || !$this->cotizacionId) {
            $this->mensajeError = 'No se encontró el cliente, flujo u oferta para registrar el ajuste.';
            return;
        }

        $valorLimpio = str_replace(',', '', trim($this->nuevoCreditoClienteTexto));
        if ($valorLimpio === '' || !is_numeric($valorLimpio) || (float) $valorLimpio < 0) {
            $this->mensajeError = 'Ingrese un monto de crédito válido, igual o mayor que cero.';
            return;
        }

        if ($this->nuevosDiasCreditoCliente < 0 || $this->nuevosDiasCreditoCliente > 365) {
            $this->mensajeError = 'Los días de crédito deben estar entre 0 y 365.';
            return;
        }

        $motivo = trim($this->motivoAjusteCredito);
        if ($motivo === '') {
            $this->mensajeError = 'Indique el motivo del cambio de crédito.';
            return;
        }

        $nuevoCredito = round((float) $valorLimpio, 2);
        $nuevosDiasCredito = (int) $this->nuevosDiasCreditoCliente;

        DB::beginTransaction();
        try {
            $cliente = DB::table('cliente')
                ->where('id', $this->clienteId)
                ->lockForUpdate()
                ->first(['credito_inicial', 'dias_credito', 'vendedor']);

            if (!$cliente) {
                throw new \RuntimeException('El cliente ya no existe.');
            }

            $creditoAnterior = DB::table('cliente_credito')
                ->where('cliente_id', $this->clienteId)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();
            $limiteAnterior = (float) ($creditoAnterior->credito ?? $cliente->credito_inicial ?? 0);
            $diasAnteriores = (int) ($creditoAnterior->dias_credito ?? $cliente->dias_credito ?? 0);
            $detalleAnterior = CreditoService::obtenerDetalleMovimientos($this->clienteId);
            $disponibleAnterior = (float) $detalleAnterior['saldo_pendiente'];

            DB::table('cliente')->where('id', $this->clienteId)->update([
                'credito_inicial' => $nuevoCredito,
                'dias_credito' => $nuevosDiasCredito,
                'updated_at' => now(),
            ]);

            DB::table('cliente_credito')
                ->where('cliente_id', $this->clienteId)
                ->update(['activo' => 0, 'updated_at' => now()]);

            DB::table('cliente_credito')->insert([
                'cliente_id' => $this->clienteId,
                'activo' => 1,
                'credito_activo' => $creditoAnterior->credito_activo ?? ($nuevoCredito > 0 ? 1 : 0),
                'credito' => $nuevoCredito,
                'dias_credito' => $nuevosDiasCredito,
                'fecha_vigencia' => $creditoAnterior->fecha_vigencia ?? null,
                'vendedor_id' => $creditoAnterior->vendedor_id ?? $cliente->vendedor ?? null,
                'referencias_bancarias' => $creditoAnterior->referencias_bancarias ?? null,
                'referencias_comerciales' => $creditoAnterior->referencias_comerciales ?? null,
                'metodo_pago' => $creditoAnterior->metodo_pago ?? null,
                'letra_cambio' => $creditoAnterior->letra_cambio ?? 0,
                'obs_letra_cambio' => $creditoAnterior->obs_letra_cambio ?? null,
                'aval_solidario' => $creditoAnterior->aval_solidario ?? 0,
                'obs_aval_solidario' => $creditoAnterior->obs_aval_solidario ?? null,
                'autorizacion_gerencia' => $creditoAnterior->autorizacion_gerencia ?? null,
                'users_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $detalleNuevo = CreditoService::obtenerDetalleMovimientos($this->clienteId);
            $disponibleNuevo = (float) $detalleNuevo['saldo_pendiente'];
            DB::table('cliente')->where('id', $this->clienteId)->update([
                'credito' => $disponibleNuevo,
                'updated_at' => now(),
            ]);

            $descripcion = 'Ajuste de crédito desde oferta #' . $this->cotizacionId
                . ': L ' . number_format($limiteAnterior, 2)
                . ' a L ' . number_format($nuevoCredito, 2)
                . '; días ' . $diasAnteriores . ' a ' . $nuevosDiasCredito
                . '. Motivo: ' . $motivo;
            DB::table('log_credito')->insert([
                'descripcion' => mb_substr($descripcion, 0, 200),
                'tipo' => 'ajuste_credito_cliente',
                'monto' => abs($nuevoCredito - $limiteAnterior),
                'users_id' => Auth::id(),
                'factura_id' => null,
                'flujo_id' => $this->flujoId,
                'cotizacion_id' => $this->cotizacionId,
                'saldo_anterior' => $disponibleAnterior,
                'saldo_resultante' => $disponibleNuevo,
                'cliente_id' => $this->clienteId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->modalAjusteCreditoVisible = false;
            $this->motivoAjusteCredito = '';
            $this->cargarDatosCreditoCliente();
            $this->mensajeError = '';
            $this->mensajeExito = 'Crédito del cliente actualizado de L '
                . number_format($limiteAnterior, 2) . ' a L ' . number_format($nuevoCredito, 2)
                . '. El cambio quedó auditado.';
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensajeError = 'No se pudo actualizar el crédito del cliente: ' . $e->getMessage();
        }
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

        // Reglas obligatorias de autorización de crédito
        $this->evaluarReglasAutorizacion();
        if (!$this->puedeAutorizar) {
            $this->mensajeError = $this->bloqueosAutorizacion[0] ?? 'No es posible autorizar el crédito con los valores actuales.';
            return;
        }

        DB::beginTransaction();
        try {
            $ip = request()->ip();
            $saldoAnterior = (float) CreditoService::obtenerDetalleMovimientos(
                (int) $this->clienteId
            )['saldo_pendiente'];
            $saldoResultante = $saldoAnterior - (float) $this->montoTotalOferta;

            // Los datos de crédito editados en esta pantalla (monto, días) se persisten SOLO
            // en la tabla credito_revision de este flujo, sin modificar la configuración
            // global del cliente en la tabla cliente.

            $cr             = $this->esSeccionExpo
                ? CreditoRevision::paraSeccion($this->flujoId, (int) $this->cotizacionId)
                : CreditoRevision::paraFlujo($this->flujoId);
            $estadoAnterior = $cr ? $cr->estado : null;

            // Días de crédito aprobados por operación:
            // usan el valor editado en esta pantalla (puede ser menor o igual al solicitado).
            // Para contado siempre es 0.
            $diasAprobados = ($this->tipoPagoSolicitud === 'contado')
                ? 0
                : max(0, (int) $this->diasCreditoEditable);

            $dtVencimiento = null;
            if ($this->tipoPagoSolicitud !== 'contado') {
                $dtVencimiento = $dtAprobacion->copy()->addDays($diasAprobados);
            }

            if ($cr) {
                $cr->update([
                    'estado'                    => CreditoRevision::APROBADO,
                    'cotizacion_id'             => $this->cotizacionId,
                    'fecha_aprobacion'          => $dtAprobacion->toDateString(),
                    'fecha_emision_solicitada'  => $this->fechaEmisionOferta,
                    'fecha_vencimiento_solicitada' => $this->fechaVencimientoOferta,
                    'dias_credito_solicitados'  => max(0, (int) $this->diasSolicitadosCredito),
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
                    'fecha_emision_solicitada'  => $this->fechaEmisionOferta,
                    'fecha_vencimiento_solicitada' => $this->fechaVencimientoOferta,
                    'dias_credito_solicitados'  => max(0, (int) $this->diasSolicitadosCredito),
                    'fecha_vencimiento_credito' => $dtVencimiento ? $dtVencimiento->toDateString() : null,
                    'dias_credito_aprobados'    => $diasAprobados,
                    'observaciones'             => trim($this->observaciones) ?: null,
                    'usuario_revision'          => Auth::id(),
                    'ip_revision'               => $ip,
                ]);
            }

            if ($this->cotizacionId) {
                $fechaEmisionOferta = DB::table('cotizacion')
                    ->where('id', $this->cotizacionId)
                    ->value('fecha_emision');

                if ($fechaEmisionOferta) {
                    DB::table('cotizacion')
                        ->where('id', $this->cotizacionId)
                        ->update([
                            'fecha_vencimiento' => Carbon::parse($fechaEmisionOferta)
                                ->addDays($diasAprobados)
                                ->toDateString(),
                            'updated_at' => now(),
                        ]);
                }
            }

            $cr->registrarHistorial(
                'aprobado',
                $estadoAnterior ?? CreditoRevision::PENDIENTE,
                CreditoRevision::APROBADO,
                'Crédito aprobado. Fecha: ' . $dtAprobacion->format('d/m/Y')
                . '. Oferta: L ' . number_format((float) $this->montoTotalOferta, 2)
                . '. Días aprobados: ' . (int) $diasAprobados,
                $ip
            );

            DB::table('log_credito')->insert([
                'descripcion' => $saldoResultante < 0
                    ? 'Saldo negativo por aprobación de oferta #' . $this->cotizacionId
                    : 'Aprobación de crédito para oferta #' . $this->cotizacionId,
                'tipo' => 'aprobacion_oferta',
                'monto' => $this->montoTotalOferta,
                'users_id' => Auth::id(),
                'factura_id' => null,
                'flujo_id' => $this->flujoId,
                'cotizacion_id' => $this->cotizacionId,
                'saldo_anterior' => $saldoAnterior,
                'saldo_resultante' => $saldoResultante,
                'cliente_id' => $this->clienteId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Cerrar historico_flujo tipo=10 como aprobado
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 10)
                ->when($this->esSeccionExpo, fn ($query) => $query->where('tramite_id', $this->cotizacionId))
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
                ->when($this->esSeccionExpo, fn ($query) => $query->where('tramite_id', $this->cotizacionId))
                ->where('estado_id', 7)
                ->exists();

            if ($revInvDevuelto) {
                DB::table('historico_flujo')
                    ->where('flujo_id', $this->flujoId)
                    ->where('tipo_tramite_id', 9)
                    ->when($this->esSeccionExpo, fn ($query) => $query->where('tramite_id', $this->cotizacionId))
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

            DB::table('expo_oferta_seccion')
                ->where('cotizacion_id', $this->cotizacionId)
                ->update([
                    'estado' => 'EN_REVISION_INVENTARIO',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            // Avanzar flujo a Revisión de Inventario
            DB::table('flujo')->where('id', $this->flujoId)->update([
                'tipo_tramite_id' => $this->esSeccionExpo ? 11 : 9,
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
            $this->mensajeExito = 'Flujo #' . $flujoIdCerrado . ': Crédito aprobado y enviado a Revisión de Inventario.'
                . ($saldoResultante < 0
                    ? ' El saldo disponible proyectado del cliente quedó en L ' . number_format($saldoResultante, 2) . '.'
                    : '');

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

            $cr             = $this->esSeccionExpo
                ? CreditoRevision::paraSeccion($this->flujoId, (int) $this->cotizacionId)
                : CreditoRevision::paraFlujo($this->flujoId);
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
                ->when($this->esSeccionExpo, fn ($query) => $query->where('tramite_id', $this->cotizacionId))
                ->where('estado_id', 5)
                ->update([
                    'estado_id'     => 3,
                    'observaciones' => 'Crédito rechazado. Motivo: ' . $motivo,
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            $esSeccionExpo = $this->esSeccionExpo;

            if ($esSeccionExpo) {
                $seccionExpo = DB::table('expo_oferta_seccion')
                    ->where('cotizacion_id', $this->cotizacionId)
                    ->first(['cotizacion_origen_id']);
                DB::table('expo_oferta_seccion')
                    ->where('cotizacion_id', $this->cotizacionId)
                    ->update([
                        'estado' => 'RECHAZADA_CREDITO',
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);

                if ($seccionExpo) {
                    DB::table('historico_flujo')->insert([
                        'flujo_id' => $this->flujoId,
                        'tipo_tramite_id' => 11,
                        'tramite_id' => $seccionExpo->cotizacion_origen_id,
                        'estado_id' => 5,
                        'observaciones' => 'Sección rechazada en Crédito. La oferta puede volver a seccionarse.',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('flujo')->where('id', $this->flujoId)->update([
                        'tipo_tramite_id' => 11,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Las ofertas normales cancelan el flujo; una sección Expo sólo se rechaza a sí misma.
            $canceladoId = DB::table('estado_venta')
                ->where('descripcion', 'cancelado')
                ->value('id') ?? 4;

            if (!$esSeccionExpo) {
                DB::table('flujo')
                    ->where('id', $this->flujoId)
                    ->update([
                        'estado_id'       => $canceladoId,
                        'tipo_tramite_id' => 10,
                        'updated_by'      => Auth::id(),
                        'updated_at'      => now(),
                    ]);
            }

            if (!$esSeccionExpo) {
                $cr->registrarHistorial(
                    'cancelado',
                    CreditoRevision::RECHAZADO,
                    CreditoRevision::CANCELADO,
                    'Flujo cancelado por rechazo de crédito.',
                    $ip
                );
            }

            DB::commit();

            $flujoIdCerrado = $this->flujoId;
            $this->cerrarDetalle();
            $this->cargar();
            $this->mensajeExito = $esSeccionExpo
                ? 'Flujo #' . $flujoIdCerrado . ': la sección fue rechazada en Crédito; las demás continúan su proceso.'
                : 'Flujo #' . $flujoIdCerrado . ': Crédito rechazado. El flujo ha sido cancelado.';

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
