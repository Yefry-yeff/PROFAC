<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

class ListadoFacturasVendedorUnificado extends Component
{
    public $tipoVenta;
    public $nombreTipo;

    public function mount($tipo = null)
    {


    
        $tipo = $tipo ?? request()->route()->defaults['tipo'] ?? 'corporativo';
        $this->tipoVenta = $tipo;

        switch ($tipo) {
            case 'corporativo':
                $this->nombreTipo = 'Clientes B';
                break;
            case 'estatal':
                $this->nombreTipo = 'Clientes A';
                break;
            default:
                $this->nombreTipo = 'Clientes';
                break;
        }
    }

    public function render()
    {
        return view('livewire.ventas.listado-facturas-unificado', [
            'tipoVenta' => $this->tipoVenta,
            'nombreTipo' => $this->nombreTipo,
            'esVendedor' => true,
        ]);
    }
}
