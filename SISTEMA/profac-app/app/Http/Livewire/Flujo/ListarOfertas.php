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

    // ── Control de acceso ─────────────────────────────────────────────────
    public $esAdmin = false;

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

    // ── Modal detalle / acciones de pedido ────────────────────────────────
    public $showModalPedido    = false;
    public $pedidoSeleccionado = null;   // array
    public $pedidoDetalles     = [];     // array of arrays
    public $confirmAccion      = null;   // 'anular' | 'duplicar'
    public $mensajeExito       = '';
    public $mensajeError       = '';
    public $motivoAnulacion    = '';

    // ── Ciclo de vida ──────────────────────────────────────────────────────
    public function mount()
    {
        $rolId = (int) (Auth::user()->rol_id ?? 0);
        $this->esAdmin = in_array($rolId, [1, 16], true);
    }

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
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf INNER JOIN flujo f ON f.id = hf.flujo_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 AND hf.tipo_tramite_id = 2) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf INNER JOIN flujo f ON f.id = hf.flujo_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 AND hf.tipo_tramite_id = 2 AND hf.observaciones = \'ganadora\') as has_ganadora')
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

        // Solo mostrar ofertas donde el usuario es actor involucrado
        if (!$this->esAdmin) {
            $q->where(function ($sub) {
                $sub->where('o.users_id', Auth::id())
                    ->orWhere('o.vendedor', Auth::id())
                    ->orWhereExists(function ($sq) {
                        // Creador del pedido relacionado
                        $sq->select(DB::raw(1))
                           ->from('pedido as p2')
                           ->whereColumn('p2.id', 'o.pedido_id')
                           ->where('p2.users_id', Auth::id());
                    })
                    ->orWhereExists(function ($sq) {
                        // Actor en la factura del flujo al que pertenece esta oferta
                        $sq->select(DB::raw(1))
                           ->from('historico_flujo as hfo')
                           ->join('historico_flujo as hff', 'hff.flujo_id', '=', 'hfo.flujo_id')
                           ->join('factura as fa', 'fa.id', '=', 'hff.tramite_id')
                           ->whereColumn('hfo.tramite_id', 'o.id')
                           ->where('hfo.tipo_tramite_id', 2)
                           ->where('hff.tipo_tramite_id', 3)
                           ->where(function ($sfa) {
                               $sfa->where('fa.vendedor', Auth::id())
                                   ->orWhere('fa.users_id', Auth::id());
                           });
                    });
            });
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

    // ── Modal: abrir con detalle del pedido ───────────────────────────────
    public function abrirModalPedido(int $pedidoId): void
    {
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id', 'p.estado', 'p.observaciones', 'p.created_at',
                'c.nombre as cliente', 'c.rtn',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf INNER JOIN flujo f ON f.id = hf.flujo_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 AND hf.tipo_tramite_id = 2) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf INNER JOIN flujo f ON f.id = hf.flujo_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 AND hf.tipo_tramite_id = 2 AND hf.observaciones = \'ganadora\') as has_ganadora'),
                DB::raw('(SELECT tt.nombre FROM flujo f INNER JOIN tipos_tramites tt ON tt.id = f.tipo_tramite_id WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 LIMIT 1) as estatus_flujo')
            )
            ->where('p.id', $pedidoId)
            ->first();

        if (!$pedido) return;

        $this->pedidoSeleccionado = (array) $pedido;
        $this->pedidoDetalles     = DB::table('pedido_detalle')
            ->where('pedido_id', $pedidoId)
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
        $this->confirmAccion   = null;
        $this->mensajeExito    = '';
        $this->mensajeError    = '';
        $this->motivoAnulacion = '';
        $this->showModalPedido = true;
    }

    public function cerrarModalPedido(): void
    {
        $this->showModalPedido    = false;
        $this->pedidoSeleccionado = null;
        $this->pedidoDetalles     = [];
        $this->confirmAccion      = null;
        $this->mensajeExito       = '';
        $this->mensajeError       = '';
        $this->motivoAnulacion    = '';
    }

    public function confirmarAccion(string $accion): void
    {
        $this->confirmAccion = $accion;
    }

    public function cancelarConfirmacion(): void
    {
        $this->confirmAccion   = null;
        $this->motivoAnulacion = '';
    }

    public function anularPedido(): void
    {
        if (!$this->pedidoSeleccionado) return;

        $this->motivoAnulacion = trim($this->motivoAnulacion);
        if ($this->motivoAnulacion === '') {
            $this->mensajeError = 'Debe indicar el motivo de anulación.';
            return;
        }

        $pedidoId = $this->pedidoSeleccionado['id'];

        DB::beginTransaction();
        try {
            DB::table('pedido')->where('id', $pedidoId)
                ->update(['estado' => 'cancelado', 'updated_at' => now()]);

            $canceladoId = DB::table('estado_venta')->where('descripcion', 'cancelado')->value('id');
            $hf = DB::table('historico_flujo')
                ->where('tipo_tramite_id', 1)
                ->where('tramite_id', $pedidoId)
                ->first();
            if ($hf) {
                DB::table('historico_flujo')->where('id', $hf->id)
                    ->update([
                        'estado_id'     => $canceladoId,
                        'observaciones' => 'Anulado: ' . $this->motivoAnulacion,
                        'updated_by'    => Auth::id(),
                        'updated_at'    => now(),
                    ]);
                DB::table('flujo')->where('id', $hf->flujo_id)
                    ->update(['estado_id' => $canceladoId, 'updated_by' => Auth::id(), 'updated_at' => now()]);
            }

            DB::commit();
            $this->confirmAccion = null;
            $this->motivoAnulacion = '';
            $this->abrirModalPedido($pedidoId); // recarga datos actualizados, resetea mensajes
            $this->mensajeExito = 'Pedido #'.$pedidoId.' anulado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->confirmAccion = null;
            $this->mensajeError  = 'Error al anular: '.$e->getMessage();
        }
    }

    public function duplicarPedido(): void
    {
        if (!$this->pedidoSeleccionado) return;
        $pedidoId = $this->pedidoSeleccionado['id'];

        $productos = DB::table('pedido_detalle')
            ->where('pedido_id', $pedidoId)
            ->get(['nombre_producto', 'cantidad'])
            ->map(fn($r) => ['nombre_producto' => $r->nombre_producto, 'cantidad' => $r->cantidad])
            ->toArray();

        $param = base64_encode(json_encode($productos));
        $url   = route('flujo.pedido') . '?productos=' . urlencode($param);
        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
        $this->cerrarModalPedido();
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
