<?php

namespace App\Http\Livewire\Configuracion;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TiempoPrefacturacion extends Component
{
    public $diasValidez       = 7;
    public $descripcionValidez = '1 semana';
    public $mensajeExito      = '';
    public $mensajeError      = '';

    // Opciones predefinidas
    public $opciones = [
        ['dias' => 3,  'label' => '3 días'],
        ['dias' => 7,  'label' => '1 semana'],
        ['dias' => 15, 'label' => '15 días'],
        ['dias' => 30, 'label' => '1 mes'],
        ['dias' => 60, 'label' => '2 meses'],
        ['dias' => 0,  'label' => 'Personalizado'],
    ];

    public function mount(): void
    {
        $config = DB::table('configuracion_prefactura')->first();
        if ($config) {
            $this->diasValidez        = $config->dias_validez;
            $this->descripcionValidez = $config->descripcion_validez;
        }
    }

    public function seleccionarOpcion(int $dias, string $label): void
    {
        if ($dias > 0) {
            $this->diasValidez        = $dias;
            $this->descripcionValidez = $label;
        }
    }

    public function guardar(): void
    {
        $this->mensajeExito = '';
        $this->mensajeError = '';

        if ($this->diasValidez <= 0) {
            $this->mensajeError = 'Los días de validez deben ser mayor a 0.';
            return;
        }

        // Construir descripción si está vacía
        if (empty(trim($this->descripcionValidez))) {
            $this->descripcionValidez = $this->diasValidez . ' día(s)';
        }

        DB::table('configuracion_prefactura')->updateOrInsert(
            ['id' => 1],
            [
                'dias_validez'        => (int) $this->diasValidez,
                'descripcion_validez' => trim($this->descripcionValidez),
                'updated_by'          => Auth::id(),
                'updated_at'          => now(),
            ]
        );

        $this->mensajeExito = 'Configuración guardada correctamente.';
    }

    public function render()
    {
        return view('livewire.configuracion.tiempo-prefacturacion')
            ->layout('layouts.app', ['title' => 'Tiempo de Prefacturación']);
    }
}
