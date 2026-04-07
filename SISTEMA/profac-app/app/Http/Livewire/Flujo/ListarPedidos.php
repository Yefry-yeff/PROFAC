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

    // ── Modal seleccionar oferta ganadora ──────────────────────────────────
    public $showModalGanadora = false;
    public $ofertaGanadoraId  = null;
    public $pedidoGanadoraId  = null;

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
                DB::raw('(SELECT COUNT(*) FROM pedido_detalle pd WHERE pd.pedido_id = p.id) as total_productos'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id) as has_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id AND o.estado = \'ganadora\') as has_ganadora')
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
                ->select('id', 'nombre_cliente', 'total', 'created_at', 'estado')
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

    // ── Seleccionar oferta ganadora ────────────────────────────────────────
    public function confirmarGanadora(int $ofertaId, int $pedidoId)
    {
        $this->ofertaGanadoraId  = $ofertaId;
        $this->pedidoGanadoraId  = $pedidoId;
        $this->showModalGanadora = true;
    }

    public function cancelarSeleccionGanadora()
    {
        $this->ofertaGanadoraId  = null;
        $this->pedidoGanadoraId  = null;
        $this->showModalGanadora = false;
    }

    public function seleccionarGanadora()
    {
        if (!$this->ofertaGanadoraId || !$this->pedidoGanadoraId) return;

        DB::beginTransaction();
        try {
            // Cancelar todas las demás ofertas del pedido
            DB::table('oferta')
                ->where('pedido_id', $this->pedidoGanadoraId)
                ->where('id', '!=', $this->ofertaGanadoraId)
                ->update(['estado' => 'cancelada', 'updated_at' => now()]);

            // Marcar la seleccionada como ganadora
            DB::table('oferta')
                ->where('id', $this->ofertaGanadoraId)
                ->update(['estado' => 'ganadora', 'updated_at' => now()]);

            // Avanzar pedido a cotizado (= paso Factura en flujo)
            DB::table('pedido')
                ->where('id', $this->pedidoGanadoraId)
                ->update(['estado' => 'cotizado', 'updated_at' => now()]);

            DB::commit();

            $this->showModalGanadora = false;
            $pedidoId = $this->pedidoGanadoraId;
            $this->ofertaGanadoraId  = null;
            $this->pedidoGanadoraId  = null;

            // Refrescar datos del modal de flujo
            $this->verFlujo($pedidoId);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError      = 'Error al seleccionar la oferta ganadora.';
            $this->showModalGanadora = false;
        }
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
