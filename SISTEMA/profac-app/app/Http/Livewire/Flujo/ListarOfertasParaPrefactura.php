<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Lista las ofertas activas que pueden ser aprobadas como prefactura (estado → 'ganadora').
 * Se embebe en la pantalla de Ventas → Prefactura.
 */
class ListarOfertasParaPrefactura extends Component
{
    public $busqueda = '';
    public $ofertas  = [];

    public $confirmandoId   = null;   // id de oferta pendiente de confirmar
    public $mensajeExito    = null;

    public function mount()
    {
        $this->cargar();
    }

    public function updatedBusqueda()
    {
        $this->cargar();
    }

    public function cargar()
    {
        $term = trim($this->busqueda);

        $q = DB::table('oferta as o')
            ->leftJoin('cliente as c', 'c.id', '=', 'o.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.users_id')
            ->leftJoin('pedido as p', 'p.id', '=', 'o.pedido_id')
            ->where('o.estado', 'activa')
            ->select(
                'o.id',
                'o.pedido_id',
                'o.nombre_cliente',
                'o.RTN',
                DB::raw('FORMAT(o.total, 2) as total'),
                'o.fecha_emision',
                'o.created_at',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM oferta_has_producto ohp WHERE ohp.oferta_id = o.id) as total_productos')
            )
            ->orderByDesc('o.created_at');

        if ($term !== '') {
            if (is_numeric($term)) {
                $q->where('o.id', (int) $term);
            } else {
                $like = '%' . $term . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('o.nombre_cliente', 'LIKE', $like)
                        ->orWhere('c.nombre', 'LIKE', $like)
                        ->orWhere('o.RTN', 'LIKE', $like);
                });
            }
        }

        $this->ofertas = $q->limit(50)->get()->toArray();
    }

    public function confirmarAprobar(int $id)
    {
        $this->confirmandoId = $id;
        $this->mensajeExito  = null;
    }

    public function cancelarConfirmacion()
    {
        $this->confirmandoId = null;
    }

    public function aprobarOferta(int $id)
    {
        DB::table('oferta')
            ->where('id', $id)
            ->where('estado', 'activa')
            ->update(['estado' => 'ganadora', 'updated_at' => now()]);

        $this->confirmandoId = null;
        $this->mensajeExito  = 'Oferta #' . $id . ' aprobada como prefactura correctamente.';
        $this->cargar();
    }

    public function verDetallePrefactura()
    {
        return $this->redirect(route('flujo.prefactura'));
    }

    public function render()
    {
        return view('livewire.flujo.listar-ofertas-para-prefactura');
    }
}
