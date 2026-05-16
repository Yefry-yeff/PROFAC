<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CreditoRevision;
use App\Models\CreditoRevisionHistorial;
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

    // ── Detalle del flujo seleccionado ────────────────────────────────────
    public ?int   $flujoId          = null;
    protected     $flujoData        = null;
    public ?int   $cotizacionId     = null;
    public array  $productos        = [];

    // ── Estado crédito actual ─────────────────────────────────────────────
    public ?string $estadoCredito           = null;
    public ?string $fechaAprobacionActual   = null;
    public ?string $fechaVencimientoActual  = null;
    public ?string $motivoRechazoActual     = null;
    public array   $historialCredito        = [];

    // ── Confirmación de acciones ──────────────────────────────────────────
    public ?string $confirmAccion    = null;
    public string  $fechaAprobacion  = '';
    public string  $fechaVencimiento = '';
    public string  $motivoRechazo    = '';
    public string  $observaciones    = '';

    // ── Mensajes ──────────────────────────────────────────────────────────
    public string $mensajeExito = '';
    public string $mensajeError = '';

    // ─────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->cargar();
    }

    // ─────────────────────────────────────────────────────────────────────
    // BANDEJA
    // ─────────────────────────────────────────────────────────────────────

    public function updatedBusqueda(): void
    {
        $this->cargar();
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
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'hf.created_at as fecha_revision',
                'hf.updated_at as fecha_accion',
                'hfof.tramite_id as cotizacion_id',
                DB::raw("COALESCE(c.nombre_cliente, p.observaciones, CONCAT('Flujo #', f.id)) as cliente"),
                DB::raw("COALESCE(c.RTN, '') as rtn"),
                DB::raw('(SELECT COUNT(*) FROM cotizacion_has_producto chp WHERE chp.cotizacion_id = hfof.tramite_id) as total_productos'),
                'hf.observaciones as obs_revision',
                'hf.estado_id',
                'cr.estado as estado_credito',
                'cr.fecha_aprobacion',
                'cr.fecha_vencimiento_credito',
                'cr.motivo_rechazo'
            )
            ->groupBy(
                'f.id', 'f.identificacion', 'hf.created_at', 'hf.updated_at',
                'hfof.tramite_id', 'c.nombre_cliente', 'p.observaciones', 'c.RTN',
                'hf.observaciones', 'hf.estado_id', 'cr.estado',
                'cr.fecha_aprobacion', 'cr.fecha_vencimiento_credito', 'cr.motivo_rechazo'
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

        // Info del flujo
        $flujoResult = DB::table('flujo as f')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'p.cliente_id')
            ->where('f.id', $flujoId)
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'p.id as pedido_id',
                DB::raw("COALESCE(cl.nombre, 'N/A') as cliente"),
                'p.created_at as pedido_fecha',
                'p.observaciones as pedido_obs'
            )
            ->first();
        $this->flujoData = $flujoResult ? (array) $flujoResult : null;

        // Oferta ganadora
        $hfGanadora = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)
            ->where('observaciones', 'ganadora')
            ->orderByDesc('id')
            ->first(['tramite_id']);

        $this->cotizacionId = $hfGanadora ? (int) $hfGanadora->tramite_id : null;

        // Productos de la oferta (solo nombre + cantidad, sin precios)
        $this->productos = [];
        if ($this->cotizacionId) {
            $this->productos = DB::table('cotizacion_has_producto')
                ->where('cotizacion_id', $this->cotizacionId)
                ->select('nombre_producto', 'cantidad')
                ->get()
                ->map(fn($r) => (array) $r)
                ->toArray();
        }

        // Estado del crédito
        $cr = CreditoRevision::paraFlujo($flujoId);
        if ($cr) {
            $this->estadoCredito           = $cr->estado;
            $this->fechaAprobacionActual   = $cr->fecha_aprobacion
                ? Carbon::parse($cr->fecha_aprobacion)->format('Y-m-d') : null;
            $this->fechaVencimientoActual  = $cr->fecha_vencimiento_credito
                ? Carbon::parse($cr->fecha_vencimiento_credito)->format('Y-m-d') : null;
            $this->motivoRechazoActual     = $cr->motivo_rechazo;

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
            $this->historialCredito       = [];
        }
    }

    public function cerrarDetalle(): void
    {
        $this->flujoId                = null;
        $this->flujoData              = null;
        $this->cotizacionId           = null;
        $this->productos              = [];
        $this->estadoCredito          = null;
        $this->fechaAprobacionActual  = null;
        $this->fechaVencimientoActual = null;
        $this->motivoRechazoActual    = null;
        $this->historialCredito       = [];
        $this->confirmAccion          = null;
        $this->fechaAprobacion        = '';
        $this->fechaVencimiento       = '';
        $this->motivoRechazo          = '';
        $this->observaciones          = '';
        $this->mensajeExito           = '';
        $this->mensajeError           = '';
    }

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

        $fechaAprobacion = trim($this->fechaAprobacion);
        if ($fechaAprobacion === '') {
            $this->mensajeError = 'Debe indicar la fecha de autorización del crédito.';
            return;
        }
        try {
            $dtAprobacion = Carbon::createFromFormat('Y-m-d', $fechaAprobacion);
        } catch (\Exception $e) {
            $this->mensajeError = 'Fecha de autorización inválida.';
            return;
        }

        $fechaVencimiento = trim($this->fechaVencimiento);
        $dtVencimiento    = null;
        if ($fechaVencimiento !== '') {
            try {
                $dtVencimiento = Carbon::createFromFormat('Y-m-d', $fechaVencimiento);
            } catch (\Exception $e) {
                $this->mensajeError = 'Fecha de vencimiento inválida.';
                return;
            }
        }

        DB::beginTransaction();
        try {
            $ip = request()->ip();

            $cr             = CreditoRevision::where('flujo_id', $this->flujoId)->latest('id')->first();
            $estadoAnterior = $cr ? $cr->estado : null;

            if ($cr) {
                $cr->update([
                    'estado'                    => CreditoRevision::APROBADO,
                    'cotizacion_id'             => $this->cotizacionId,
                    'fecha_aprobacion'          => $dtAprobacion->toDateString(),
                    'fecha_vencimiento_credito' => $dtVencimiento ? $dtVencimiento->toDateString() : null,
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
                . ($dtVencimiento ? '. Vence: ' . $dtVencimiento->format('d/m/Y') : ''),
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
        return view('livewire.flujo.revisioncreditos', [
            'flujoData' => $this->flujoData,
        ]);
    }
}
