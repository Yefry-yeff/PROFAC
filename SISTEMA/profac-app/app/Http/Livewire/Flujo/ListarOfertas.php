<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ListarOfertas extends Component
{
    // ── Filtros ────────────────────────────────────────────────────────────
    public $busquedaCliente = '';
    public $filtroFecha     = '';
    public $filtroPedido    = '';

    // ── Paginación ─────────────────────────────────────────────────────────
    public $pagina       = 1;
    public $porPagina    = 15;
    public $totalOfertas = 0;
    public $totalPaginas = 0;

    // ── Filtros → reset pagina ─────────────────────────────────────────────
    public function updatedBusquedaCliente() { $this->pagina = 1; }
    public function updatedFiltroFecha()     { $this->pagina = 1; }
    public function updatedFiltroPedido()    { $this->pagina = 1; }

    // ── Query base ─────────────────────────────────────────────────────────
    private function query()
    {
        $q = DB::table('oferta as o')
            ->join('cliente as c', 'c.id', '=', 'o.cliente_id')
            ->join('users as u', 'u.id', '=', 'o.users_id')
            ->select(
                'o.id',
                'o.pedido_id',
                'o.nombre_cliente',
                'o.RTN',
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

        if ($this->filtroFecha !== '') {
            $q->whereDate('o.created_at', $this->filtroFecha);
        }

        return $q;
    }

    // ── Paginación ─────────────────────────────────────────────────────────
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
        $this->pagina          = 1;
    }

    // ── Render ─────────────────────────────────────────────────────────────
    public function render()
    {
        $this->totalOfertas  = $this->query()->count();
        $this->totalPaginas  = (int) ceil($this->totalOfertas / $this->porPagina);
        $offset  = ($this->pagina - 1) * $this->porPagina;
        $ofertas = $this->query()->skip($offset)->take($this->porPagina)->get();

        return view('livewire.flujo.listar-ofertas', [
            'ofertas'      => $ofertas,
            'totalPaginas' => $this->totalPaginas,
        ]);
    }
}
