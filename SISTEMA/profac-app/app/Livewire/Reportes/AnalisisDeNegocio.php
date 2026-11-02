<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Contracts\View\View;

class AnalisisDeNegocio extends Component
{
    public string $titulo = 'Analisis de Negocio';

    public function render(): View
    {
        return view('livewire.reportes.analisisdenegocio');
    }
}
