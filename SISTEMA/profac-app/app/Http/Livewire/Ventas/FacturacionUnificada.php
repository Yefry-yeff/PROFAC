<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use App\Models\TipoFactura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FacturacionUnificada extends Component
{
    public $tipoFacturaId;
    public $tipoFactura;
    public $tiposFactura;
    public $fromFlujo = false;

// ── Buscador de pedido ────────────────────────────────────────────────
    public $busquedaPedido     = '';
    public $pedidosEncontrados = [];
    public $pedidoVinculado    = null;
    public $pedidoId           = null;

    // ── Datos pre-cargados del pedido (solo modo oferta) ─────────────────
    public $clientePedido      = null;   // ['id','nombre','rtn','cliente_id']
    public $productosSugeridos = [];     // [['nombre_pedido','cantidad','similares':[...]]]

    // ── Vendedor actual ──────────────────────────────────────────────────
    public $vendedorDefault    = [];

    // ── Pedido preview (para modal de detalle) ────────────────────────────
    public $pedidoDetalle      = null;

    public function mount($codigo = null)
    {
        $this->fromFlujo = request()->get('from') === 'flujo';
        $this->tiposFactura = TipoFactura::activos()->get();

        if ($codigo) {
            $this->tipoFactura = TipoFactura::where('codigo', $codigo)->first();
        }

        if (!$this->tipoFactura) {
            $this->tipoFactura = TipoFactura::where('codigo', 'estatal')->first();
        }

        $this->tipoFacturaId = $this->tipoFactura->id ?? null;

        // Vendedor = usuario autenticado por defecto
        if (Auth::check()) {
            $this->vendedorDefault = [
                'id'   => Auth::id(),
                'name' => Auth::user()->name,
            ];
        }

        // Pre-seleccionar pedido si viene por query string
        $pid = request()->get('pedidoId');
        if ($pid) {
            $this->seleccionarPedido((int) $pid);
        }
    }

    public function updatedBusquedaPedido()
    {
        $term = trim($this->busquedaPedido);
        if (strlen($term) < 2) {
            $this->pedidosEncontrados = [];
            return;
        }
        $esNum = is_numeric($term);
        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->whereNotIn('p.estado', ['cancelado'])
            ->select(
                'p.id', 'p.estado', 'p.created_at',
                'c.nombre as cliente', 'c.rtn',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id) as total_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id AND o.estado = \'ganadora\') as has_ganadora')
            )
            ->orderByDesc('p.created_at')
            ->limit(8);

        if ($esNum) {
            $q->where('p.id', (int) $term);
        } else {
            $like = '%' . $term . '%';
            $q->where(function ($sub) use ($like) {
                $sub->where('c.nombre', 'LIKE', $like)
                    ->orWhere('c.rtn', 'LIKE', $like);
            });
        }
        $this->pedidosEncontrados = $q->get()->toArray();
    }

    public function seleccionarPedido(int $id)
    {
        $p = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->where('p.id', $id)
            ->select(
                'p.id', 'p.estado', 'p.created_at',
                'c.id as cliente_id', 'c.nombre as cliente', 'c.rtn'
            )
            ->first();

        if (!$p) return;

        $this->pedidoId        = $p->id;
        $this->pedidoVinculado = (array) $p;

        // Pre-cargar datos del cliente
        $this->clientePedido = [
            'id'     => $p->cliente_id,
            'nombre' => $p->cliente,
            'rtn'    => $p->rtn,
        ];

        // Cargar detalles del pedido y buscar productos similares
        $detalles = DB::table('pedido_detalle')
            ->where('pedido_id', $id)
            ->select('id', 'nombre_producto', 'cantidad')
            ->get();

        $this->productosSugeridos = [];
        foreach ($detalles as $det) {
            $this->productosSugeridos[] = [
                'nombre_pedido' => $det->nombre_producto,
                'cantidad'      => $det->cantidad,
                'similares'     => $this->buscarSimilares($det->nombre_producto),
            ];
        }

        $this->busquedaPedido     = '';
        $this->pedidosEncontrados = [];

        // Notificar al JS para re-llenar Select2 de cliente y vendedor
        $this->dispatchBrowserEvent('pedido-seleccionado', [
            'clienteId'      => $this->clientePedido['id'],
            'clienteNombre'  => $this->clientePedido['nombre'],
            'vendedorId'     => $this->vendedorDefault['id'] ?? null,
            'vendedorNombre' => $this->vendedorDefault['name'] ?? null,
        ]);
    }

    /**
     * Carga el detalle de un pedido para mostrar en modal de preview.
     */
    public function verDetallePedido(int $id)
    {
        $p = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->where('p.id', $id)
            ->select(
                'p.id', 'p.estado', 'p.created_at', 'p.observaciones',
                'c.nombre as cliente', 'c.rtn',
                'u.name as vendedor_registra'
            )
            ->first();

        if (!$p) return;

        $detalles = DB::table('pedido_detalle')
            ->where('pedido_id', $id)
            ->select('nombre_producto', 'cantidad')
            ->get()
            ->toArray();

        $this->pedidoDetalle = [
            'pedido'    => (array) $p,
            'productos' => $detalles,
        ];

        $this->dispatchBrowserEvent('mostrar-modal-detalle-pedido');
    }

    /**
     * Busca hasta $limit productos reales cuyo nombre se asemeje al nombre del pedido.
     */
    private function buscarSimilares(string $nombre, int $limit = 3): array
    {
        // Tokenizar: palabras de 3+ letras
        $palabras = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9\s]/u', ' ', $nombre)), fn($w) => strlen($w) >= 3);

        if (empty($palabras)) {
            return DB::table('producto')
                ->where('nombre', 'LIKE', '%' . $nombre . '%')
                ->whereRaw('id IN (SELECT producto_id FROM inventario WHERE cantidad > 0)')
                ->select('id', 'nombre', 'precio_base as precio', 'isv')
                ->limit($limit)->get()->toArray();
        }

        // Construir CASE para scoring por coincidencia de palabras
        $q = DB::table('producto');
        $cases  = [];
        $params = [];
        foreach ($palabras as $w) {
            $cases[]  = 'IF(nombre LIKE ?, 1, 0)';
            $params[] = '%' . $w . '%';
        }
        $score = 'IF(nombre LIKE ?, 10, 0) + ' . implode(' + ', $cases);
        $params = array_merge(['%' . $nombre . '%'], $params);

        $results = $q->selectRaw('id, nombre, precio_base as precio, isv, (' . $score . ') as score', $params)
            ->having('score', '>', 0)
            ->orderByDesc('score')
            ->limit($limit)
            ->get()->toArray();

        // Fallback si no hay resultados con scoring
        if (empty($results)) {
            $results = DB::table('producto')
                ->where('nombre', 'LIKE', '%' . array_values($palabras)[0] . '%')
                ->select('id', 'nombre', 'precio_base as precio', 'isv')
                ->limit($limit)->get()->toArray();
        }

        return $results;
    }

    public function desvincularPedido()
    {
        $this->pedidoId            = null;
        $this->pedidoVinculado     = null;
        $this->busquedaPedido      = '';
        $this->pedidosEncontrados  = [];
        $this->clientePedido       = null;
        $this->productosSugeridos  = [];
        $this->pedidoDetalle       = null;

        // Notificar al JS para restaurar campos
        $this->dispatchBrowserEvent('pedido-desvinculado', [
            'vendedorId'     => $this->vendedorDefault['id'] ?? null,
            'vendedorNombre' => $this->vendedorDefault['name'] ?? null,
        ]);
    }

    public function render()
    {
        return view('livewire.ventas.facturacion-unificada', [
            'tiposFactura' => $this->tiposFactura,
            'config'       => $this->tipoFactura,
        ]);
    }
}
