<?php

namespace App\Http\Livewire\Clientes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

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
        $categoriasEscala = DB::table('cliente_categoria_escala')
            ->select('id', 'nombre_categoria')
            ->where('estado_id', 1)
            ->orderBy('nombre_categoria')
            ->get();

        return view('livewire.clientes.cliente-form', compact('categoriasEscala'));
    }
}
