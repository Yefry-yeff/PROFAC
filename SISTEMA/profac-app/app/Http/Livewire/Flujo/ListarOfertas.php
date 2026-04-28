<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ListarOfertas extends Component
{
    // ── Filtros tabla de ofertas ───────────────────────────────────────────
    public $busquedaCliente = '';
    public $filtroFecha     = '';
    public $filtroPedido    = '';
    public $filtroEstado    = '';

    // ── Estadísticas ──────────────────────────────────────────────────────
    public $statsTotal      = 0;
    public $statsGanadoras  = 0;
    public $statsActivas    = 0;
    public $statsCanceladas = 0;

    // ── Paginación ────────────────────────────────────────────────────────
    public $pagina       = 1;
    public $porPagina    = 15;
    public $totalOfertas = 0;
    public $totalPaginas = 0;

    // ── Panel: buscar pedido para crear oferta ────────────────────────────
    public $showPanelPedido    = false;
    public $busquedaPedido     = '';
    public $pedidosEncontrados = [];

    // ── Reset paginación al cambiar filtros ───────────────────────────────
    public function updatedBusquedaCliente() { $this->pagina = 1; }
    public function updatedFiltroFecha()     { $this->pagina = 1; }
    public function updatedFiltroPedido()    { $this->pagina = 1; }
    public function updatedFiltroEstado()    { $this->pagina = 1; }

    // ── Búsqueda reactiva de pedidos ──────────────────────────────────────
    public function updatedBusquedaPedido()
    {
        $term = trim($this->busquedaPedido);
        if ($term === '' || strlen($term) < 2) {
            $this->pedidosEncontrados = [];
            return;
        }
        $this->buscarPedido();
    }

    public function buscarPedido()
    {
        $term = trim($this->busquedaPedido);
        if ($term === '') { $this->pedidosEncontrados = []; return; }

        $esNumero = is_numeric($term);

        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->whereNotIn('p.estado', ['cancelado'])
            ->select(
                'p.id',
                'p.estado',
                'p.created_at',
                'c.nombre as cliente',
                'c.rtn',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id AND o.estado = \'ganadora\') as has_ganadora')
            )
            ->orderByDesc('p.created_at')
            ->limit(10);

        if ($esNumero) {
            $q->where('p.id', (int) $term);
        } else {
            $termLike = '%'.$term.'%';
            $q->where(function ($sub) use ($termLike) {
                $sub->where('c.nombre', 'LIKE', $termLike)
                    ->orWhere('c.rtn', 'LIKE', $termLike);
            });
        }

        $this->pedidosEncontrados = $q->get()->toArray();
    }

    public function togglePanelPedido()
    {
        $this->showPanelPedido = !$this->showPanelPedido;
        if (!$this->showPanelPedido) {
            $this->busquedaPedido     = '';
            $this->pedidosEncontrados = [];
        }
    }

    // ── Query base de ofertas ─────────────────────────────────────────────
    private function query()
    {
        $q = DB::table('oferta as o')
            ->leftJoin('cliente as c', 'c.id', '=', 'o.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.users_id')
            ->select(
                'o.id',
                'o.pedido_id',
                'o.nombre_cliente',
                'o.RTN',
                'o.estado',
                DB::raw('FORMAT(o.sub_total,2) as sub_total'),
                DB::raw('FORMAT(o.isv,2) as isv'),
                DB::raw('FORMAT(o.total,2) as total'),
                'o.porc_descuento',
                'o.nota',
                'o.fecha_emision',
                'o.created_at',
                'u.name as registrado_por',
                DB::raw('(SELECT name FROM users WHERE id = o.vendedor) as vendedor'),
                DB::raw('(SELECT COUNT(*) FROM oferta_has_producto ohp WHERE ohp.oferta_id = o.id) as total_productos')
            )
            ->orderByDesc('o.created_at');

        if (strlen(trim($this->busquedaCliente)) >= 2) {
            $term = '%'.trim($this->busquedaCliente).'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('c.nombre', 'LIKE', $term)
                    ->orWhere('c.rtn', 'LIKE', $term)
                    ->orWhere('o.nombre_cliente', 'LIKE', $term);
            });
        }

        if ($this->filtroPedido !== '') {
            $q->where('o.pedido_id', (int) $this->filtroPedido);
        }

        if ($this->filtroEstado !== '') {
            $q->where('o.estado', $this->filtroEstado);
        }

        if ($this->filtroFecha !== '') {
            $q->whereDate('o.created_at', $this->filtroFecha);
        }

        return $q;
    }

    // ── Paginación ────────────────────────────────────────────────────────
    public function paginaAnterior() { if ($this->pagina > 1) $this->pagina--; }

    public function paginaSiguiente()
    {
        if ($this->pagina < $this->totalPaginas) $this->pagina++;
    }

    public function irPagina(int $p)
    {
        if ($p >= 1 && $p <= $this->totalPaginas) $this->pagina = $p;
    }

    public function limpiarFiltros()
    {
        $this->busquedaCliente = '';
        $this->filtroFecha     = '';
        $this->filtroPedido    = '';
        $this->filtroEstado    = '';
        $this->pagina          = 1;
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $this->statsTotal      = DB::table('oferta')->count();
        $this->statsGanadoras  = DB::table('oferta')->where('estado', 'ganadora')->count();
        $this->statsActivas    = DB::table('oferta')->where('estado', 'activa')->count();
        $this->statsCanceladas = DB::table('oferta')->where('estado', 'cancelada')->count();

        $this->totalOfertas = $this->query()->count();
        $this->totalPaginas = (int) ceil($this->totalOfertas / $this->porPagina);
        $offset  = ($this->pagina - 1) * $this->porPagina;
        $ofertas = $this->query()->skip($offset)->take($this->porPagina)->get();

        return view('livewire.flujo.listar-ofertas', [
            'ofertas'      => $ofertas,
            'totalPaginas' => $this->totalPaginas,
        ]);
    }
}
