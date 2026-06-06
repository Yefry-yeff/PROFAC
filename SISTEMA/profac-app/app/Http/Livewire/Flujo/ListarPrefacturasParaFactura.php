<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

/**
 * Lista las prefacturas (ofertas ganadoras) que pueden convertirse en factura.
 * Se embebe en la pantalla de Ventas → Factura.
 */
class ListarPrefacturasParaFactura extends Component
{
    public $busqueda    = '';
    public $prefacturas = [];

    // Subtipo de factura seleccionado antes de redirigir
    public $subtipoSeleccionado = null;
    public $ofertaParaFacturar  = null;

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
            ->leftJoin('users as v', 'v.id', '=', 'o.vendedor')
            ->where('o.estado', 'ganadora')
            ->select(
                'o.id',
                'o.pedido_id',
                'o.nombre_cliente',
                'o.RTN',
                DB::raw('FORMAT(o.total, 2) as total'),
                'o.fecha_emision',
                'o.created_at',
                'u.name as registrado_por',
                'v.name as vendedor_nombre',
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

        $this->prefacturas = $q->limit(50)->get()->toArray();
    }

    /**
     * Muestra el selector de tipo de factura para una prefactura.
     */
    public function seleccionarParaFacturar(int $id)
    {
        $this->ofertaParaFacturar  = $id;
        $this->subtipoSeleccionado = null;
    }

    public function cancelarSeleccion()
    {
        $this->ofertaParaFacturar  = null;
        $this->subtipoSeleccionado = null;
    }

    /**
     * Redirige a la factura del tipo elegido, pasando la oferta como referencia.
     */
    public function crearFactura(string $subtipo)
    {
        $rutas = [
            'clientes_a'    => '/ventas/estatal',
            'clientes_b'    => '/ventas/coporativo',
            'sr_clientes_a' => '/ventas/sin/restriccion/gobierno',
            'sr_clientes_b' => '/ventas/sin/restriccion/precio',
            'exonerada'     => '/ventas/exonerado/factura',
        ];

        $base = $rutas[$subtipo] ?? '/ventas/estatal';
        $url  = $base . '?from=flujo&ofertaId=' . $this->ofertaParaFacturar;

        return $this->redirect($url);
    }

    public function render()
    {
        return view('livewire.flujo.listar-prefacturas-para-factura');
    }
}
