<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Livewire page component for the "Crear Prefactura" form.
 * Accessible via GET /flujo/prefactura/crear?cotizacionId=X[&flujoId=Y|&pedidoId=Z]
 *
 * The form re-uses the same cart UX as FacturacionUnificada (cotizacion_clientes_a)
 * but POSTs to /flujo/prefactura/guardar instead of /guardar/cotizacion.
 */
class CrearPrefactura extends Component
{
    // ── Client/flujo context loaded from query-string ──────────────────────
    public $cotizacionId     = null;   // source winning offer
    public $flujoId          = null;
    public $pedidoId         = null;

    // ── Pre-loaded client data (if cotizacionId present) ──────────────────
    public $clientePrecargado  = null;  // ['id','nombre','rtn']
    public $productosParaCarrito = [];  // rows for auto-cart
    public $vendedorDefault    = [];

    // ── Config: validez ───────────────────────────────────────────────────
    public $diasValidez        = 7;
    public $descripcionValidez = '1 semana';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->vendedorDefault = [
                'id'   => Auth::id(),
                'name' => Auth::user()->name,
            ];
        }

        // Load validity config
        $cfg = DB::table('configuracion_prefactura')->first();
        if ($cfg) {
            $this->diasValidez        = $cfg->dias_validez;
            $this->descripcionValidez = $cfg->descripcion_validez;
        }

        // Resolve query params
        $this->cotizacionId = request()->get('cotizacionId') ? (int) request()->get('cotizacionId') : null;
        $this->flujoId      = request()->get('flujoId')      ? (int) request()->get('flujoId')      : null;
        $this->pedidoId     = request()->get('pedidoId')     ? (int) request()->get('pedidoId')     : null;

        // If pedidoId, resolve flujoId from pedido
        if ($this->pedidoId && !$this->flujoId) {
            $this->flujoId = DB::table('flujo')
                ->where('identificacion', (string) $this->pedidoId)
                ->where('tipo_flujo_id', 1)
                ->value('id');
        }

        // Load winning offer data
        if ($this->cotizacionId) {
            $cot = DB::table('cotizacion as c')
                ->leftJoin('cliente as cl', 'cl.id', '=', 'c.cliente_id')
                ->where('c.id', $this->cotizacionId)
                ->select('c.cliente_id', 'c.nombre_cliente', 'c.RTN', 'cl.rtn as cliente_rtn')
                ->first();

            if ($cot) {
                $this->clientePrecargado = [
                    'id'     => $cot->cliente_id,
                    'nombre' => $cot->nombre_cliente,
                    'rtn'    => $cot->RTN ?: $cot->cliente_rtn,
                ];
            }

            // Load products for auto-cart
            $prods = DB::table('cotizacion_has_producto')
                ->where('cotizacion_id', $this->cotizacionId)
                ->orderBy('indice')
                ->get([
                    'producto_id', 'nombre_producto', 'nombre_bodega',
                    'precio_unidad', 'cantidad', 'isv_producto',
                    'unidad_medida_venta_id', 'Bodega_id', 'seccion_id',
                    'precios_producto_carga_id',
                ])
                ->toArray();

            foreach ($prods as $p) {
                $this->productosParaCarrito[] = (array) $p;
            }
        }
    }

    public function render()
    {
        return view('livewire.flujo.crear-prefactura');
    }
}
