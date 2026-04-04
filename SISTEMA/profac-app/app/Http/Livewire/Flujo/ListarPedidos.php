<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ListarPedidos extends Component
{
    // ── Filtros ────────────────────────────────────────────────────────────
    public $busquedaCliente = '';
    public $filtroEstado    = '';
    public $filtroFecha     = '';

    // ── Paginación simple ──────────────────────────────────────────────────
    public $pagina       = 1;
    public $porPagina    = 15;
    public $totalPedidos = 0;
    public $totalPaginas = 0;

    // ── Confirmación de cancelación ──────────────────────────────────────────
    public $pedidoAnularId   = null;
    public $showModalAnular  = false;

    // ── Modal de flujo del pedido ──────────────────────────────────────────
    public $showModalFlujo  = false;
    public $pedidoFlujoId   = null;
    public $pedidoFlujoData = null;

    // ── Mensaje ────────────────────────────────────────────────────────────
    public $mensajeExito = '';
    public $mensajeError = '';

    // ── Actualización de filtros → volver a página 1 ──────────────────────
    public function updatedBusquedaCliente() { $this->pagina = 1; }
    public function updatedFiltroEstado()    { $this->pagina = 1; }
    public function updatedFiltroFecha()     { $this->pagina = 1; }

    // ── Obtener pedidos filtrados ──────────────────────────────────────────
    private function query()
    {
        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->join('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id',
                'c.nombre as cliente',
                'c.rtn',
                'p.estado',
                'u.name as registrado_por',
                'p.observaciones',
                'p.created_at',
                DB::raw('(SELECT COUNT(*) FROM pedido_detalle pd WHERE pd.pedido_id = p.id) as total_productos')
            )
            ->orderByDesc('p.created_at');

        if (strlen(trim($this->busquedaCliente)) >= 2) {
            $term = '%' . trim($this->busquedaCliente) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('c.nombre', 'LIKE', $term)
                    ->orWhere('c.rtn', 'LIKE', $term);
            });
        }

        if ($this->filtroEstado !== '') {
            $q->where('p.estado', $this->filtroEstado);
        }

        if ($this->filtroFecha !== '') {
            $q->whereDate('p.created_at', $this->filtroFecha);
        }

        return $q;
    }

    public function getPedidosProperty()
    {
        $this->totalPedidos = $this->query()->count();
        $offset = ($this->pagina - 1) * $this->porPagina;
        return $this->query()->skip($offset)->take($this->porPagina)->get();
    }

    public function getTotalPaginasProperty(): int
    {
        return (int) ceil($this->totalPedidos / $this->porPagina);
    }

    // ── Paginación ─────────────────────────────────────────────────────────
    public function paginaAnterior() { if ($this->pagina > 1) $this->pagina--; }
    public function paginaSiguiente()
    {
        if ($this->pagina < $this->totalPaginas) $this->pagina++;
    }

    public function irPagina(int $p)
    {
        if ($p >= 1 && $p <= $this->totalPaginas) {
            $this->pagina = $p;
        }
    }

    // ── Limpiar filtros ────────────────────────────────────────────────────
    public function limpiarFiltros()
    {
        $this->busquedaCliente = '';
        $this->filtroEstado    = '';
        $this->filtroFecha     = '';
        $this->pagina          = 1;
    }

    // ── Anular pedido ──────────────────────────────────────────────────────
    public function confirmarAnular(int $id)
    {
        $this->pedidoAnularId  = $id;
        $this->showModalAnular = true;
    }

    public function cancelarAnular()
    {
        $this->pedidoAnularId  = null;
        $this->showModalAnular = false;
    }

    public function anularPedido()
    {
        if (!$this->pedidoAnularId) return;

        DB::table('pedido')
            ->where('id', $this->pedidoAnularId)
            ->update(['estado' => 'cancelado', 'updated_at' => now()]);

        $this->pedidoAnularId  = null;
        $this->showModalAnular = false;
        $this->mensajeExito    = 'Pedido cancelado correctamente.';
    }

    // ── Modal flujo del pedido ─────────────────────────────────────────────
    public function verFlujo(int $id)
    {
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->join('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id', 'p.estado', 'p.observaciones',
                'p.created_at', 'p.updated_at',
                'c.nombre as cliente',
                'u.name as registrado_por'
            )
            ->where('p.id', $id)
            ->first();

        if ($pedido) {
            $ofertas = DB::table('oferta')
                ->where('pedido_id', $id)
                ->select('id', 'nombre_cliente', 'total', 'created_at')
                ->orderBy('id')
                ->limit(10)
                ->get();

            $this->pedidoFlujoId   = $id;
            $this->pedidoFlujoData = array_merge((array) $pedido, [
                'ofertas' => $ofertas->toArray(),
            ]);
            $this->showModalFlujo  = true;
        }
    }

    public function cerrarFlujo()
    {
        $this->showModalFlujo  = false;
        $this->pedidoFlujoId   = null;
        $this->pedidoFlujoData = null;
    }

    // ── Render ─────────────────────────────────────────────────────────────
    public function render()
    {
        $this->totalPedidos  = $this->query()->count();
        $this->totalPaginas  = (int) ceil($this->totalPedidos / $this->porPagina);
        $offset  = ($this->pagina - 1) * $this->porPagina;
        $pedidos = $this->query()->skip($offset)->take($this->porPagina)->get();

        return view('livewire.flujo.listar-pedidos', [
            'pedidos'      => $pedidos,
            'totalPaginas' => $this->totalPaginas,
        ]);
    }
}
