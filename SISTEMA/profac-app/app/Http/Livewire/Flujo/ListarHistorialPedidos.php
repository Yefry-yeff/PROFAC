<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Historial de pedidos registrados en el flujo de ventas.
 * – El usuario normal sólo ve sus propios pedidos.
 * – El administrador (rol_id = 1) ve todos.
 * – El estado se toma de flujo.tipo_tramite_id → tipos_tramites.nombre
 * – La cantidad de ofertas se cuenta desde historico_flujo (tipo_tramite_id = 2)
 */
class ListarHistorialPedidos extends Component
{
    // ── Filtros ────────────────────────────────────────────────────────────
    public $busqueda     = '';
    public $filtroEstado = '';

    // ── Ordenamiento ───────────────────────────────────────────────────────
    public $sortCol = 'created_at';
    public $sortDir = 'desc';

    // ── Datos ──────────────────────────────────────────────────────────────
    public $pedidos  = [];
    public $esAdmin  = false;

    protected $listeners = ['pedidoActualizado' => 'cargar'];
    protected $queryString = [];

    // ── Ciclo de vida ──────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->esAdmin = Auth::user()->rol_id === 1;
        $this->cargar();
    }

    public function updatedBusqueda():     void { $this->cargar(); }
    public function updatedFiltroEstado(): void { $this->cargar(); }

    public function sortBy(string $col): void
    {
        $this->sortDir = ($this->sortCol === $col && $this->sortDir === 'asc') ? 'desc' : 'asc';
        $this->sortCol = $col;
        $this->cargar();
    }

    // ── Consulta principal ─────────────────────────────────────────────────
    public function cargar(): void
    {
        $term   = trim($this->busqueda);
        $estado = $this->filtroEstado;

        /*
         * La pertenencia al flujo de ventas se valida mediante:
         *   historico_flujo.tipo_tramite_id = 1  (pedido)
         * El estado actual del pedido se lee de:
         *   flujo.tipo_tramite_id → tipos_tramites.nombre
         * Los pedidos cancelados mantienen p.estado = 'cancelado'.
         */
        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->join('users as u', 'u.id', '=', 'p.users_id')
            ->leftJoin('historico_flujo as hf', function ($join) {
                $join->on('hf.tramite_id', '=', 'p.id')
                     ->where('hf.tipo_tramite_id', '=', 1);
            })
            ->leftJoin('flujo as f', 'f.id', '=', 'hf.flujo_id')
            ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->select(
                'p.id',
                'p.estado as estado_pedido',
                'p.observaciones',
                'p.created_at',
                'c.nombre as cliente',
                'c.rtn',
                'u.name as registrado_por',
                'p.users_id',
                'f.id as flujo_id',
                DB::raw("CASE WHEN p.estado = 'cancelado'
                              THEN 'cancelado'
                              ELSE COALESCE(tt.nombre, 'sin_flujo')
                         END as estado_flujo"),
                DB::raw('COALESCE((SELECT COUNT(*) FROM historico_flujo hf2
                                    WHERE hf2.flujo_id = f.id
                                      AND hf2.tipo_tramite_id = 2), 0) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM pedido_detalle pd
                           WHERE pd.pedido_id = p.id) as total_productos')
            );

        // ── Filtro por usuario ─────────────────────────────────────────────
        // El administrador ve todo; los demás ven flujos propios, clientes
        // asignados como asesor/teleasesor o facturas donde son actores.
        if (!$this->esAdmin) {
            $q->where(function ($sub) {
                $sub->where('c.vendedor', Auth::id())
                    ->orWhere('p.users_id', Auth::id())
                    ->orWhereExists(function ($existe) {
                        $existe->select(DB::raw(1))
                            ->from('cliente_usuario as cu')
                            ->whereColumn('cu.cliente_id', 'c.id')
                            ->where('cu.usuario_id', Auth::id())
                            ->whereIn('cu.rol_id', [2, 3]);
                    })
                    ->orWhereExists(function ($invoiceActor) {
                        $invoiceActor->select(DB::raw(1))
                            ->from('historico_flujo as hff')
                            ->join('factura as fa', 'fa.id', '=', 'hff.tramite_id')
                            ->whereColumn('hff.flujo_id', 'f.id')
                            ->where('hff.tipo_tramite_id', 3)
                            ->where(function ($actor) {
                                $actor->where('fa.vendedor', Auth::id())
                                    ->orWhere('fa.users_id', Auth::id())
                                    ->orWhere('fa.gestor_entrega', Auth::id());
                            });
                    });
            });
        }

        // ── Filtro por estado ──────────────────────────────────────────────
        if ($estado !== '') {
            if ($estado === 'cancelado') {
                $q->where('p.estado', 'cancelado');
            } else {
                $q->where('p.estado', '!=', 'cancelado')
                  ->where('tt.nombre', $estado);
            }
        }

        // ── Búsqueda ───────────────────────────────────────────────────────
        if ($term !== '') {
            if (is_numeric($term)) {
                $q->where('p.id', (int) $term);
            } else {
                $like = '%' . $term . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('c.nombre', 'LIKE', $like)
                        ->orWhere('c.rtn',    'LIKE', $like);
                });
            }
        }

        // ── Ordenamiento ───────────────────────────────────────────────────
        $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';
        switch ($this->sortCol) {
            case 'id':
                $q->orderBy('p.id', $dir);
                break;
            case 'cliente':
                $q->orderBy('c.nombre', $dir);
                break;
            case 'total_productos':
                $q->orderBy(DB::raw('total_productos'), $dir);
                break;
            case 'total_ofertas':
                $q->orderBy(DB::raw('total_ofertas'), $dir);
                break;
            case 'estado_flujo':
                $q->orderBy(DB::raw("CASE WHEN p.estado = 'cancelado' THEN 'cancelado' ELSE COALESCE(tt.nombre, 'sin_flujo') END"), $dir);
                break;
            default:
                $q->orderBy('p.created_at', $dir);
        }

        $this->pedidos = $q->limit(300)->get()->toArray();
    }

    public function abrirModalPedido(int $pedidoId): void
    {
        $this->emit('abrirFlujoPedido', $pedidoId);
    }

    public function render()
    {
        return view('livewire.flujo.listar-historial-pedidos');
    }
}
