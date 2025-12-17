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
        $meses = DB::table('comision_empleado as A')
                ->select(
                    'A.mes_comision',
                    'A.nombre_empleado',
                    'A.users_comision',
                    'A.comision_acumulada',
                    'A.mes_comision',
                    'A.fecha_ult_modificacion',
                    DB::raw("
                        CONCAT(
                            CASE MONTH(A.mes_comision)
                                WHEN 1 THEN 'Enero'
                                WHEN 2 THEN 'Febrero'
                                WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril'
                                WHEN 5 THEN 'Mayo'
                                WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio'
                                WHEN 8 THEN 'Agosto'
                                WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre'
                                WHEN 11 THEN 'Noviembre'
                                WHEN 12 THEN 'Diciembre'
                            END,
                            ' - ',
                            YEAR(A.mes_comision)
                        ) AS mes_anio
                    ")
                )
                ->where('A.users_comision', Auth::user()->id)
                ->orderBy('A.mes_comision', 'desc')
                ->get();



        return view('livewire.comisiones.escalado.mis-comisiones', compact('info', 'meses'));
    }
}
