<?php

namespace App\Livewire\BodegaComponent;

use Livewire\Component;
use App\Models\User;

class BodegaGestiones extends Component
{
    public function render()
    {
        $users = User::all();
        return view('livewire.bodega-component.bodega-gestiones');
    }
}
