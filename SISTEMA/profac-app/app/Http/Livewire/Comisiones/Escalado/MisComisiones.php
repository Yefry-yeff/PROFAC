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
        $info = DB::table('users as A')
            ->join('rol as B', 'B.id', '=', 'A.rol_id')
            ->select('A.name', 'A.id', 'B.nombre as rol')
            ->where('A.id', Auth::id())
            ->first();

        $meses = DB::table('comision_empleado as A')
            ->select(
                'A.mes_comision',
                'A.nombre_empleado',
                'A.users_comision',
                'A.comision_acumulada',
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
                "),
                DB::raw("
                    CASE
                        WHEN YEAR(A.mes_comision) = YEAR(CURDATE())
                        AND MONTH(A.mes_comision) = MONTH(CURDATE())
                        THEN 0
                        ELSE 1
                    END AS orden_mes_actual
                ")
            )
            ->where('A.users_comision', Auth::id())
            ->orderBy('orden_mes_actual') // 👈 mes actual primero
            ->orderByRaw('YEAR(A.mes_comision), MONTH(A.mes_comision)')
            ->get();


        return view(
            'livewire.comisiones.escalado.mis-comisiones',
            compact('info', 'meses')
        );
    }

    public function listarComisionesEmpleado(){
        try {

            $datos = DB::SELECT("
                SELECT
                    ce.users_comision          AS user_id,
                    ce.nombre_empleado        AS nombre_empleado,
                    ce.rol_id                 AS rol_id,

                    MONTH(ce.mes_comision)    AS mes,
                    YEAR(ce.mes_comision)     AS anio,

                    CASE MONTH(ce.mes_comision)
                        WHEN 1  THEN 'Enero'
                        WHEN 2  THEN 'Febrero'
                        WHEN 3  THEN 'Marzo'
                        WHEN 4  THEN 'Abril'
                        WHEN 5  THEN 'Mayo'
                        WHEN 6  THEN 'Junio'
                        WHEN 7  THEN 'Julio'
                        WHEN 8  THEN 'Agosto'
                        WHEN 9  THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre'
                        WHEN 11 THEN 'Noviembre'
                        WHEN 12 THEN 'Diciembre'
                    END AS mes_letra,

                    ce.comision_acumulada     AS comision_acumulada,
                    ce.fecha_ult_modificacion AS ultima_actualizacion,

                    COUNT(DISTINCT fc.id)     AS cantidad_facturas

                FROM comision_empleado ce

                LEFT JOIN facturas_comision fc
                    ON fc.rol_id = ce.rol_id
                AND fc.estado_id = 1
                AND MONTH(fc.fecha_cierre_factura) = MONTH(ce.mes_comision)
                AND YEAR(fc.fecha_cierre_factura)  = YEAR(ce.mes_comision)

                WHERE ce.users_comision = ".Auth::id()."

                GROUP BY
                    ce.users_comision,
                    ce.nombre_empleado,
                    ce.rol_id,
                    anio,
                    mes,
                    mes_letra,
                    ce.comision_acumulada,
                    ce.fecha_ult_modificacion

                ORDER BY
                    anio,
                    mes;
            ");


            return Datatables::of($datos)
                    ->rawColumns([])
                    ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ],402);
        }
    }
}
