<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ListadoFacturasUnificado extends Component
{
    public $tipoVenta;
    public $nombreTipo;
    public $esVendedor;

    /** Roles considerados administrador/supervisor */
    const ADMIN_ROLES = [1, 3, 5];

    public function mount($tipo = null)
    {
        // Leer de route defaults si no viene como parámetro de URL
        $tipo = $tipo ?? request()->route()->defaults['tipo'] ?? 'corporativo';
        $this->tipoVenta = $tipo;
        $this->esVendedor = !in_array(Auth::user()->rol_id, self::ADMIN_ROLES);

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
