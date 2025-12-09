<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportesComisionesGenerales extends Component
{
    public function render()
    {
        return view('livewire.comisiones.escalado.reportes-comisiones-generales');
    }
}
