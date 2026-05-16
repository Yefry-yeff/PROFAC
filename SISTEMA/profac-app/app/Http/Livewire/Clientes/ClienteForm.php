<?php

namespace App\Http\Livewire\Clientes;

use Livewire\Component;

class ClienteForm extends Component
{
    public $clienteId = null;
    public $modo = 'crear';

    public function mount($id = null)
    {
        $this->clienteId = $id;
        $this->modo = $id ? 'editar' : 'crear';
    }

    public function render()
    {
        return view('livewire.clientes.cliente-form');
    }
}
