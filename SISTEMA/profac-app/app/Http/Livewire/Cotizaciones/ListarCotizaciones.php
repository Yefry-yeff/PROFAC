<?php

namespace App\Http\Livewire\Cotizaciones;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;

class ListarCotizaciones extends Component
{
    public function render()
    {
        return view('livewire.cotizaciones.listar-cotizaciones');
    }

    public function listarCotizaciones(){
        $cotizaciones = DB::SELECT("
        select 
        concat(YEAR(now()),'-',A.id)  as codigo,
        A.nombre_cliente,
        A.RTN,
        A.sub_total,
        A.isv,
        A.total,
        B.name,
        A.created_at
        from cotizacion A
        inner join users B
        on A.users_id = B.id
        ");
    }
    
}
