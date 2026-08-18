<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ListadoFacturasVendedorUnificado extends Component
{
    public $tipoVenta;
    public $nombreTipo;

    /** Roles que ven todas las facturas (no filtradas por vendedor) */
    const ADMIN_ROLES = [1, 3, 5, 16];

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
        $esVendedor = !in_array(Auth::user()->rol_id, self::ADMIN_ROLES);

        return view('livewire.ventas.listado-facturas-unificado', [
            'tipoVenta'  => $this->tipoVenta,
            'nombreTipo' => $this->nombreTipo,
            'esVendedor' => $esVendedor,
        ]);
    }
}
