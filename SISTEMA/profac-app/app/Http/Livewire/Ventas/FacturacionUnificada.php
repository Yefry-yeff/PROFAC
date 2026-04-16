<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use App\Models\TipoFactura;

class FacturacionUnificada extends Component
{
    public $tipoFacturaId;
    public $tipoFactura;
    public $tiposFactura;
    public $fromFlujo = false;

    public function mount($codigo = null)
    {
        $this->fromFlujo = request()->get('from') === 'flujo';
        $this->tiposFactura = TipoFactura::activos()->get();

        if ($codigo) {
            $this->tipoFactura = TipoFactura::where('codigo', $codigo)->first();
        }

        // Por defecto: Facturación Clientes A (código = 'estatal')
        if (!$this->tipoFactura) {
            $this->tipoFactura = TipoFactura::where('codigo', 'estatal')->first();
        }

        $this->tipoFacturaId = $this->tipoFactura->id ?? null;
    }

    public function render()
    {
        return view('livewire.ventas.facturacion-unificada', [
            'tiposFactura' => $this->tiposFactura,
            'config' => $this->tipoFactura,
        ]);
    }
}
