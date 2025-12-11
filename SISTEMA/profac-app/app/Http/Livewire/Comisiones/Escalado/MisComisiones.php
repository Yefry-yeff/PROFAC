<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class MisComisiones extends Component
{
    public function render()
    {
        $info = DB::selectone('select A.name, A.id, B.nombre as rol from users A inner join rol B on B.id = A.rol_id where A.id = '.Auth::user()->id);

        return view('livewire.comisiones.escalado.mis-comisiones', compact('info'));
    }
}
