<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

/**
 * Lista los pedidos activos/pendientes que pueden recibir una oferta.
 * Se embebe en la pantalla de Ventas → Oferta.
 */
class ListarPedidosParaOfertar extends Component
{
    public $busqueda  = '';
    public $filtroEstado = '';
    public $pedidos   = [];
    public $cargando  = false;

    protected $queryString = [];

    public function mount()
    {
        $this->cargar();
    }

    public function updatedBusqueda()
    {
        $this->cargar();
    }

    public function updatedFiltroEstado()
    {
        $this->cargar();
    }

    public function cargar()
    {
        $term   = trim($this->busqueda);
        $estado = $this->filtroEstado;

        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->whereNotIn('p.estado', ['cancelado'])
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

        $this->pedidos = $q->limit(50)->get()->toArray();
    }

    public function nuevaOferta(int $pedidoId)
    {
        return $this->redirect('/proforma/cotizacion/2?from=flujo&pedidoId=' . $pedidoId);
    }

    public function nuevaOfertaSinPedido()
    {
        return $this->redirect('/proforma/cotizacion/2?from=flujo');
    }

    public function render()
    {
        return view('livewire.flujo.listar-pedidos-para-ofertar');
    }
}
