<?php

namespace App\Http\Livewire\Comisiones\Escalado;


use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use App\Models\Comisiones\Escalado\modelcomision_escala;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class Configuracion extends Component
{
    public function render()
    {
        return view('livewire.comisiones.escalado.configuracion');
    }

    public function listaRolesUsuario(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $roles = \DB::table('rol')
            ->select('id', 'nombre')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('nombre', 'like', '%'.$q.'%');
            })
            ->orderBy('nombre')
            ->limit(50)
            ->get();
            //dd($roles);

        return response()->json(['roles' => $roles], 200);
    }
}
