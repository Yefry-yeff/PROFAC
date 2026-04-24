<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Historial completo de pedidos (todos los estados).
 * Se embebe en la pantalla de Ventas → Pedido → Historial.
 */
class ListarHistorialPedidos extends Component
{
    public $busqueda     = '';
    public $filtroEstado = '';
    public $pedidos      = [];

    // ── Modal detalle / acciones de pedido ────────────────────────────────
    public $showModalPedido    = false;
    public $pedidoSeleccionado = null;
    public $pedidoDetalles     = [];
    public $confirmAccion      = null;
    public $mensajeExito       = '';
    public $mensajeError       = '';
    public $motivoAnulacion    = '';

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

    // ── Modal ─────────────────────────────────────────────────────────────
    public function abrirModalPedido(int $pedidoId): void
    {
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id', 'p.estado', 'p.observaciones', 'p.created_at',
                'c.nombre as cliente', 'c.rtn',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id AND o.estado = \'ganadora\') as has_ganadora')
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
            $this->confirmAccion   = null;
            $this->motivoAnulacion = '';
            $this->abrirModalPedido($pedidoId);
            $this->mensajeExito = 'Pedido #' . $pedidoId . ' anulado correctamente.';
            $this->cargar();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->confirmAccion = null;
            $this->mensajeError  = 'Error al anular: ' . $e->getMessage();
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

    public function render()
    {
        return view('livewire.flujo.listar-historial-pedidos');
    }
}
