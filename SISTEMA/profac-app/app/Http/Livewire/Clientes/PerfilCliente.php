<?php

namespace App\Http\Livewire\Clientes;

use Livewire\Component;
use App\Models\User;

class PerfilCliente extends Component
{
    public function render()
    {
        $users = User::all();
        return view('livewire.clientes.perfil-cliente');
    }
}
