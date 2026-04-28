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
    public $busqueda              = '';
    public $filtroEstado          = '';
    public $pedidos               = [];
    public $cotizacionesSinPedido = [];
    public $cargando              = false;

    protected $queryString = [];

    protected $listeners = ['pedidoActualizado' => 'cargar'];

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
                // Estado actual del flujo: tipo_tramite_id → tipos_tramites.nombre
                DB::raw('(SELECT tt.nombre FROM flujo f INNER JOIN tipos_tramites tt ON tt.id = f.tipo_tramite_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 LIMIT 1) as estado_flujo'),
                // Ofertas vinculadas al pedido vía historico_flujo
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf INNER JOIN flujo f ON f.id = hf.flujo_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 AND hf.tipo_tramite_id = 2) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf INNER JOIN flujo f ON f.id = hf.flujo_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 AND hf.tipo_tramite_id = 2 AND hf.observaciones = \'ganadora\') as ofertas_ganadoras'),
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

        // Cotizaciones sin pedido: flujos de venta (tipo_flujo_id=1) sin historico de tipo=1 (pedido)
        $qSin = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('cotizacion as co', DB::raw('co.id'), '=', DB::raw('CAST(f.identificacion AS UNSIGNED)'))
            ->leftJoin('users as u', 'u.id', '=', 'co.users_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('historico_flujo as hf2')
                      ->whereColumn('hf2.flujo_id', 'f.id')
                      ->where('hf2.tipo_tramite_id', 1);
            })
            ->select(
                'co.id', 'co.nombre_cliente', 'co.RTN', 'co.total',
                'co.created_at', 'tt.nombre as estado_flujo',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM cotizacion_has_producto chp WHERE chp.cotizacion_id = co.id) as total_productos'),
                DB::raw('IF(f.tipo_tramite_id >= 3, 1, 0) as es_ganadora')
            )
            ->orderByDesc('f.id');

        if ($term !== '' && !is_numeric($term)) {
            $like = '%' . $term . '%';
            $qSin->where(function ($sub) use ($like) {
                $sub->where('co.nombre_cliente', 'LIKE', $like)
                    ->orWhere('co.RTN', 'LIKE', $like);
            });
        }

        $this->cotizacionesSinPedido = $qSin->limit(30)->get()->toArray();
    }

    public function nuevaOferta(int $pedidoId)
    {
        return $this->redirect('/proforma/cotizacion/2?from=flujo&pedidoId=' . $pedidoId);
    }

    public function nuevaOfertaSinPedido()
    {
        return $this->redirect('/proforma/cotizacion/2?from=flujo');
    }

    // ── Modal: abre el componente reutilizable ─────────────────────────────
    public function abrirModalPedido(int $pedidoId): void
    {
        $this->emit('abrirFlujoPedido', $pedidoId, 'ofertas');
    }

    public function render()
    {
        return view('livewire.flujo.listar-pedidos-para-ofertar');
    }
}
