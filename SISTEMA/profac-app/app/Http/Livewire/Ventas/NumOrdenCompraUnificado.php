<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

class NumOrdenCompraUnificado extends Component
{
    public $tipoCliente;
    public $nombreTipo;

    public function mount($tipo = null)
    {
        $tipo = $tipo ?? request()->route()->defaults['tipo'] ?? 'estatal';
        $this->tipoCliente = $tipo;

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
        return view('livewire.ventas.num-orden-compra-unificado', [
            'tipoCliente' => $this->tipoCliente,
            'nombreTipo' => $this->nombreTipo,
        ]);
    }
}
