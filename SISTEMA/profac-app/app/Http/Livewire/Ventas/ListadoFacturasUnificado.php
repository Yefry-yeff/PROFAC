<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

class ListadoFacturasUnificado extends Component
{
    public $tipoVenta;
    public $nombreTipo;
    public $esVendedor;

    public function mount($tipo = null)
    {
        // Leer de route defaults si no viene como parámetro de URL
        $tipo = $tipo ?? request()->route()->defaults['tipo'] ?? 'corporativo';
        $this->tipoVenta = $tipo;
        $this->esVendedor = false;

        switch ($tipo) {
            case 'corporativo':
                $this->nombreTipo = 'Clientes B';
                break;
            case 'estatal':
                $this->nombreTipo = 'Clientes A';
                break;
            case 'exonerado':
                $this->nombreTipo = 'Clientes Exonerado';
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
            'esVendedor' => $this->esVendedor,
        ]);
    }

    
}


