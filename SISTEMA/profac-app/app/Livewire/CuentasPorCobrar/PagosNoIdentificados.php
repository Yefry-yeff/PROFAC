<?php

namespace App\Livewire\CuentasPorCobrar;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class PagosNoIdentificados extends Component
{
    public string $titulo = 'Pagos no Identificados';

    public function render(): View
    {
        return view('livewire.cuentasporcobrar.pagosnoidentificados');
    }
}
