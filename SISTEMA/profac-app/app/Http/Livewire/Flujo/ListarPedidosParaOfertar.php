<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Lista los pedidos listos para recibir una oferta y las ofertas listas para prefacturar.
 * Dos secciones:
 *   1. Pedidos con historico_flujo tipo_tramite_id=1 (originados desde pedido)
 *   2. Ofertas sin pedido (flujo sin historico de tipo=1)
 *
 * Se embebe en la pantalla de Ventas → Oferta.
 */
class ListarPedidosParaOfertar extends Component
{
    // ── Filtros & sort ────────────────────────────────────────────────────
    public $busqueda     = '';
    public $filtroEstado = '';   // '' | 'pedido' | 'Ofertas' | 'prefactura'
    public $sortCol      = 'created_at';
    public $sortDir      = 'desc';

    // ── Datos ─────────────────────────────────────────────────────────────
    public $pedidos               = [];
    public $cotizacionesSinPedido = [];

    // ── Sort: cotizaciones sin pedido ─────────────────────────────────────
    public $sortColSin = 'created_at';
    public $sortDirSin = 'desc';

    // ── Mensajes flash ────────────────────────────────────────────────────
    public $mensajeExito = '';
    public $mensajeError = '';

    // ── Listeners ─────────────────────────────────────────────────────────
    protected $listeners = ['pedidoActualizado' => 'cargar'];

    // ─────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->cargar();
    }

    public function updatedBusqueda(): void  { $this->cargar(); }
    public function updatedFiltroEstado(): void { $this->cargar(); }

    // ─────────────────────────────────────────────────────────────────────
    // SORT
    // ─────────────────────────────────────────────────────────────────────

    public function sortBy(string $col): void
    {
        $this->sortDir = ($this->sortCol === $col && $this->sortDir === 'asc') ? 'desc' : 'asc';
        $this->sortCol = $col;
        $this->cargar();
    }

    public function sortBySin(string $col): void
    {
        $this->sortDirSin = ($this->sortColSin === $col && $this->sortDirSin === 'asc') ? 'desc' : 'asc';
        $this->sortColSin = $col;
        $this->cargar();
    }

    // ─────────────────────────────────────────────────────────────────────
    // CARGAR DATOS
    // ─────────────────────────────────────────────────────────────────────

    public function cargar(): void
    {
        $term         = trim($this->busqueda);
        $filtroEstado = $this->filtroEstado;

        // ── Sección 1: pedidos con historico de tipo pedido ───────────────
        // Muestra flujos que tienen un historico_flujo con tipo_tramite_id=1 (pedido).
        // Estado filtrable por: pedido (tipo_tramite_id=1), Ofertas (=2), prefactura (=4)
        $sortAllowed = ['id', 'cliente', 'total_productos', 'total_ofertas', 'estado_flujo', 'created_at'];
        $col  = in_array($this->sortCol, $sortAllowed) ? $this->sortCol : 'created_at';
        $dir  = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->whereNotIn('p.estado', ['cancelado'])
            ->select(
                'p.id',
                'p.estado',
                'p.observaciones',
                'p.created_at',
                'c.nombre as cliente',
                'c.rtn',
                'u.name as registrado_por',
                // Estado actual del flujo
                DB::raw('(SELECT tt.nombre FROM flujo f
                           INNER JOIN tipos_tramites tt ON tt.id = f.tipo_tramite_id
                           WHERE f.identificacion = CAST(p.id AS CHAR)
                             AND f.tipo_flujo_id = 1
                           LIMIT 1) as estado_flujo'),
                // Ofertas vinculadas vía historico_flujo
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf
                           INNER JOIN flujo f ON f.id = hf.flujo_id
                           WHERE f.identificacion = CAST(p.id AS CHAR)
                             AND f.tipo_flujo_id = 1
                             AND hf.tipo_tramite_id = 2) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf
                           INNER JOIN flujo f ON f.id = hf.flujo_id
                           WHERE f.identificacion = CAST(p.id AS CHAR)
                             AND f.tipo_flujo_id = 1
                             AND hf.tipo_tramite_id = 2
                             AND hf.observaciones = \'ganadora\') as ofertas_ganadoras'),
                DB::raw('(SELECT COUNT(*) FROM pedido_detalle pd WHERE pd.pedido_id = p.id) as total_productos')
            );

        // Filtro de estado: compara contra tipos_tramites.nombre del flujo
        if ($filtroEstado !== '') {
            $q->whereExists(function ($sub) use ($filtroEstado) {
                $sub->select(DB::raw(1))
                    ->from('flujo as f2')
                    ->join('tipos_tramites as tt2', 'tt2.id', '=', 'f2.tipo_tramite_id')
                    ->whereColumn('f2.identificacion', DB::raw('CAST(p.id AS CHAR)'))
                    ->where('f2.tipo_flujo_id', 1)
                    ->where('tt2.nombre', $filtroEstado);
            });
        }

        if ($term !== '') {
            if (is_numeric($term)) {
                $q->where('p.id', (int) $term);
            } else {
                $like = '%' . $term . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('c.nombre', 'LIKE', $like)
                        ->orWhere('c.rtn', 'LIKE', $like);
                });
            }
        }

        // Sort por columnas escalares directo; columnas calculadas: wrap en raw
        $scalarCols = ['id', 'cliente', 'created_at'];
        if (in_array($col, $scalarCols)) {
            $prefix = match($col) {
                'id'         => 'p.id',
                'cliente'    => 'c.nombre',
                'created_at' => 'p.created_at',
                default      => 'p.created_at',
            };
            $q->orderBy(DB::raw($prefix), $dir);
        } else {
            $q->orderBy(DB::raw($col), $dir);
        }

        $this->pedidos = $q->limit(100)->get()->toArray();

        // ── Sección 2: ofertas sin pedido ─────────────────────────────────
        // Flujos de venta SIN historico de tipo=1 (pedido); usa tabla oferta
        $sortAllowedSin = ['id', 'nombre_cliente', 'total_productos', 'estado_flujo', 'created_at'];
        $colSin = in_array($this->sortColSin, $sortAllowedSin) ? $this->sortColSin : 'created_at';
        $dirSin = $this->sortDirSin === 'asc' ? 'asc' : 'desc';

        // Filtro de estado para la sección sin pedido
        // Si el filtro es 'pedido', esta sección queda vacía (no tiene pedido)
        if ($filtroEstado === 'pedido') {
            $this->cotizacionesSinPedido = [];
            return;
        }

        $qSin = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('cotizacion as o', DB::raw('o.id'), '=', DB::raw('CAST(f.identificacion AS UNSIGNED)'))
            ->leftJoin('users as u', 'u.id', '=', 'o.users_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('historico_flujo as hf2')
                      ->whereColumn('hf2.flujo_id', 'f.id')
                      ->where('hf2.tipo_tramite_id', 1);
            })
            ->select(
                'o.id',
                'f.id as flujo_id',
                'o.nombre_cliente',
                'o.RTN',
                DB::raw('FORMAT(o.total, 2) as total'),
                'o.created_at',
                'tt.nombre as estado_flujo',
                'f.tipo_tramite_id as flujo_tipo_tramite_id',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM cotizacion_has_producto chp WHERE chp.cotizacion_id = o.id) as total_productos'),
                // es_ganadora: flujo tiene tipo_tramite_id = 4 (prefactura)
                DB::raw('IF(f.tipo_tramite_id = 4, 1, 0) as es_ganadora')
            );

        if ($filtroEstado !== '') {
            $qSin->where('tt.nombre', $filtroEstado);
        }

        if ($term !== '' && !is_numeric($term)) {
            $like = '%' . $term . '%';
            $qSin->where(function ($sub) use ($like) {
                $sub->where('o.nombre_cliente', 'LIKE', $like)
                    ->orWhere('o.RTN', 'LIKE', $like);
            });
        }

        $scalarColsSin = ['id', 'nombre_cliente', 'created_at'];
        if (in_array($colSin, $scalarColsSin)) {
            $prefix = match($colSin) {
                'id'             => 'o.id',
                'nombre_cliente' => 'o.nombre_cliente',
                'created_at'     => 'o.created_at',
                default          => 'o.created_at',
            };
            $qSin->orderBy(DB::raw($prefix), $dirSin);
        } else {
            $qSin->orderBy(DB::raw($colSin), $dirSin);
        }

        $this->cotizacionesSinPedido = $qSin->limit(100)->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCIONES OFERTA: GANADORA / ANULAR GANADORA
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Marca la oferta como ganadora:
     *  - oferta.estado = 'ganadora'
     *  - flujo.tipo_tramite_id = 4 (prefactura)
     *  - historico_flujo: actualiza observaciones = 'ganadora' en el registro de tipo=2
     */
    public function marcarGanadora(int $flujoId, int $ofertaId): void
    {
        DB::beginTransaction();
        try {
            // Actualizar flujo → prefactura (id=4)
            DB::table('flujo')->where('id', $flujoId)
                ->update([
                    'tipo_tramite_id' => 4,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);

            // Actualizar cotizacion → ganadora
            DB::table('cotizacion')->where('id', $ofertaId)
                ->update(['updated_at' => now()]);

            // Marcar en historico_flujo observaciones = 'ganadora' para el registro de tipo=2 de esta oferta
            DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $ofertaId)
                ->update([
                    'observaciones' => 'ganadora',
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            DB::commit();
            $this->mensajeExito = 'Oferta #' . $ofertaId . ' marcada como ganadora. Flujo actualizado a prefactura.';
            $this->mensajeError = '';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al marcar ganadora: ' . $e->getMessage();
            $this->mensajeExito = '';
        }
        $this->cargar();
    }

    /**
     * Anula la selección de oferta ganadora:
     *  - flujo.tipo_tramite_id = 2 (Ofertas)
     *  - historico_flujo: limpia observaciones = 'ganadora'
     */
    public function anularGanadora(int $flujoId, int $ofertaId): void
    {
        DB::beginTransaction();
        try {
            // Revertir flujo → Ofertas (id=2)
            DB::table('flujo')->where('id', $flujoId)
                ->update([
                    'tipo_tramite_id' => 2,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);

            // Limpiar observación 'ganadora' en historico_flujo
            DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $ofertaId)
                ->update([
                    'observaciones' => null,
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            DB::commit();
            $this->mensajeExito = 'Oferta #' . $ofertaId . ' anulada como ganadora. Flujo revertido a Ofertas.';
            $this->mensajeError = '';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al anular ganadora: ' . $e->getMessage();
            $this->mensajeExito = '';
        }
        $this->cargar();
    }

    // ─────────────────────────────────────────────────────────────────────
    // MODAL
    // ─────────────────────────────────────────────────────────────────────

    /** Abre ModalFlujoPedido para el pedido (sección 1) */
    public function abrirModalPedido(int $pedidoId): void
    {
        $this->emit('abrirFlujoPedido', $pedidoId, 'ofertas');
    }

    /** Abre ModalFlujoPedido para una oferta sin pedido (sección 2) — pasa el id de oferta */
    public function abrirModalOferta(int $ofertaId): void
    {
        // Para ofertas sin pedido, el flujo existe pero identificacion = ofertaId
        // El modal espera un pedidoId; usamos el flujo_id directamente vía evento con id negativo
        // convención: si pedidoId < 0, se interpreta como ofertaId sin pedido
        // En su lugar, simplemente abrimos con el ofertaId buscado en flujo.identificacion
        $this->emit('abrirFlujoPedido', $ofertaId);
    }

    public function nuevaOfertaSinPedido(): void
    {
        $this->redirect('/proforma/cotizacion/2?from=flujo');
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.flujo.listar-pedidos-para-ofertar');
    }
}
