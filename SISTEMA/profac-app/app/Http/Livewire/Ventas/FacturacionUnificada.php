<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use App\Models\TipoFactura;
use Illuminate\Support\Facades\DB;

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
            ->select('p.id', 'p.estado', 'p.created_at', 'c.nombre as cliente', 'c.rtn')
            ->first();
        if ($p) {
            $this->pedidoId        = $p->id;
            $this->pedidoVinculado = (array) $p;
        }
        $this->busquedaPedido     = '';
        $this->pedidosEncontrados = [];
    }

    public function desvincularPedido()
    {
        $this->pedidoId           = null;
        $this->pedidoVinculado    = null;
        $this->busquedaPedido     = '';
        $this->pedidosEncontrados = [];
    }

    public function render()
    {
        return view('livewire.ventas.facturacion-unificada', [
            'tiposFactura' => $this->tiposFactura,
            'config'       => $this->tipoFactura,
        ]);
    }
}
