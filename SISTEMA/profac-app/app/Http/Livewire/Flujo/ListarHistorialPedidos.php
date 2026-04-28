<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

/**
 * Historial completo de pedidos (todos los estados).
 * Se embebe en la pantalla de Ventas → Pedido → Historial.
 */
class ListarHistorialPedidos extends Component
{
    public $busqueda     = '';
    public $filtroEstado = '';
    public $pedidos      = [];

    protected $listeners = ['pedidoActualizado' => 'cargar'];

    protected $queryString = [];

    public function mount(): void
    {
        $this->cargar();
    }

    public function updatedBusqueda():     void { $this->cargar(); }
    public function updatedFiltroEstado(): void { $this->cargar(); }

    public function cargar(): void
    {
        $term   = trim($this->busqueda);
        $estado = $this->filtroEstado;

        // Sin filtro de estado → muestra TODOS los pedidos
        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id', 'p.estado', 'p.observaciones', 'p.created_at',
                'c.nombre as cliente', 'c.rtn',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id AND o.estado = \'ganadora\') as ofertas_ganadoras'),
                DB::raw('(SELECT COUNT(*) FROM pedido_detalle pd WHERE pd.pedido_id = p.id) as total_productos')
            )
            ->orderByDesc('p.created_at');

        if ($estado !== '') {
            $q->where('p.estado', $estado);
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

        $this->pedidos = $q->limit(100)->get()->toArray();
    }

    public function nuevaOferta(int $pedidoId): mixed
    {
        return $this->redirect('/proforma/cotizacion/2?from=flujo&pedidoId=' . $pedidoId);
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
