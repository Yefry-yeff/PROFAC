<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Contracts\View\View;

class CarteraDeCobros extends Component
{
    public string $titulo = 'Cartera de Cobros';

    public function render(): View
    {
        return view('livewire.reportes.carteradecobros');
    }
}
