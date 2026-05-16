<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Prefactura extends Component
{
    // ── Búsqueda ───────────────────────────────────────────────────────────
    public $busquedaCliente = '';
    public $busquedaNumero  = '';

    // ── Resultados ─────────────────────────────────────────────────────────
    public $resultados      = [];
    public $totalResultados = 0;
    public $buscado         = false;

    // ── Oferta seleccionada ────────────────────────────────────────────────
    public $ofertaSeleccionada = null;  // array con datos cabecera
    public $productosOferta    = [];    // array de líneas de producto

    // ── Reactive: disparar búsqueda al escribir ────────────────────────────
    public function updatedBusquedaCliente()
    {
        $this->ofertaSeleccionada = null;
        $this->productosOferta    = [];
        $this->buscarOfertas();
    }

    public function updatedBusquedaNumero()
    {
        $this->ofertaSeleccionada = null;
        $this->productosOferta    = [];
        $this->buscarOfertas();
    }

    // ── Búsqueda de ofertas ganadoras ──────────────────────────────────────
    public function buscarOfertas()
    {
        $cliente = trim($this->busquedaCliente);
        $numero  = trim($this->busquedaNumero);

        // Mínimo un criterio de búsqueda
        if ($numero === '' && strlen($cliente) < 2) {
            $this->resultados      = [];
            $this->totalResultados = 0;
            $this->buscado         = false;
            return;
        }

        $this->buscado = true;

        $q = DB::table('oferta as o')
            ->leftJoin('cliente as c', 'c.id', '=', 'o.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.users_id')
            ->where('o.estado', 'ganadora')
            ->select(
                'o.id',
                'o.pedido_id',
                'o.nombre_cliente',
                'o.RTN',
                'o.fecha_emision',
                DB::raw('FORMAT(o.total, 2) as total'),
                'u.name as registrado_por',
                'o.created_at',
                DB::raw('(SELECT COUNT(*) FROM oferta_has_producto ohp WHERE ohp.oferta_id = o.id) as total_productos')
            )
            ->orderByDesc('o.created_at');

        if ($numero !== '') {
            $q->where('o.id', (int) $numero);
        } else {
            $term = '%' . $cliente . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('o.nombre_cliente', 'LIKE', $term)
                    ->orWhere('c.nombre', 'LIKE', $term)
                    ->orWhere('o.RTN', 'LIKE', $term);
            });
        }

        $results               = $q->limit(30)->get();
        $this->resultados      = $results->toArray();
        $this->totalResultados = count($this->resultados);
    }

    // ── Cargar detalle completo de la oferta ganadora ──────────────────────
    public function seleccionarOferta(int $id)
    {
        $oferta = DB::table('oferta as o')
            ->leftJoin('cliente as c', 'c.id', '=', 'o.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.users_id')
            ->leftJoin('users as v', 'v.id', '=', 'o.vendedor')
            ->where('o.id', $id)
            ->where('o.estado', 'ganadora')
            ->select(
                'o.id',
                'o.pedido_id',
                'o.nombre_cliente',
                'o.RTN',
                'o.fecha_emision',
                'o.fecha_vencimiento',
                DB::raw('FORMAT(o.sub_total, 2) as sub_total'),
                DB::raw('FORMAT(o.sub_total_grabado, 2) as sub_total_grabado'),
                DB::raw('FORMAT(o.sub_total_excento, 2) as sub_total_excento'),
                DB::raw('FORMAT(o.isv, 2) as isv'),
                DB::raw('FORMAT(o.total, 2) as total'),
                'o.porc_descuento',
                DB::raw('FORMAT(o.monto_descuento, 2) as monto_descuento'),
                'o.nota',
                'o.estado',
                'o.created_at',
                'u.name as registrado_por',
                'v.name as vendedor_nombre',
                'c.nombre as cliente_nombre',
                'c.rtn as cliente_rtn'
            )
            ->first();

        if (!$oferta) return;

        $this->ofertaSeleccionada = (array) $oferta;

        $this->productosOferta = DB::table('oferta_has_producto')
            ->where('oferta_id', $id)
            ->select(
                'id',
                'indice',
                'nombre_producto',
                'nombre_bodega',
                DB::raw('FORMAT(cantidad, 4) as cantidad'),
                DB::raw('FORMAT(precio_unidad, 4) as precio_unidad'),
                'tipo_precio',
                DB::raw('FORMAT(sub_total, 2) as sub_total'),
                DB::raw('FORMAT(isv, 2) as isv'),
                DB::raw('FORMAT(total, 2) as total'),
                DB::raw('FORMAT(monto_descProducto, 2) as descuento')
            )
            ->orderBy('indice')
            ->get()
            ->toArray();
    }

    // ── Limpiar todo ───────────────────────────────────────────────────────
    public function limpiar()
    {
        $this->busquedaCliente    = '';
        $this->busquedaNumero     = '';
        $this->resultados         = [];
        $this->totalResultados    = 0;
        $this->buscado            = false;
        $this->ofertaSeleccionada = null;
        $this->productosOferta    = [];
    }

    public function volverResultados()
    {
        $this->ofertaSeleccionada = null;
        $this->productosOferta    = [];
    }

    public function render()
    {
        return view('livewire.flujo.prefactura');
    }
}
