<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use DataTables;
use Throwable;
use PDF;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CuentasPorCobrarExport;
use App\Exports\CuentasPorCobrarInteresExport;

class CuentasPorCobrar extends Component
{
    public function render()
    {
        return view('livewire.ventas.cuentas-por-cobrar');
    }

    public function listarClientes(Request $request){
        try {

         //$clientes = DB::SELECT("select id, nombre as text from cliente where estado_cliente_id = 1");//Clientes Activos
         $clientes = DB::SELECT("select id, concat(id,' - ',nombre) as text from cliente where (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15");//Todos los Clientes

        return response()->json([
            'results'=>$clientes,
        ],200);

        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
    }


    public function listarCuentasPorCobrar($id){
        try{

            $cuentas = DB::select("

            CALL cuentasx2('".$id."');");
                //dd($cuentas);
        return Datatables::of($cuentas)
                ->addColumn('opciones', function ($cuenta) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">


                    <li>
                        <a class="dropdown-item" href="/detalle/venta/'.$cuenta->codigoFactura.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="/venta/cobro/'.$cuenta->codigoFactura.'"> <i class="fa-solid fa-cash-register text-success"></i> Pagos </a>
                    </li>

                </ul>
            </div>
                ';
                })


                ->rawColumns(['opciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarCuentasPorCobrarInteres($id){
        try{

            $interes_cuentas = DB::SELECT("
            select
            factura.numero_factura as numero_factura,
            factura.cai as correlativo,
            cliente_id as id_cliente,
            factura.nombre_cliente as 'cliente',
            factura.numero_factura as 'documento',
            factura.fecha_emision as 'fecha_emision',
            factura.fecha_vencimiento as 'fecha_vencimiento',
            factura.total as 'cargo',
            (factura.total-factura.pendiente_cobro) as 'abonos',
            @numeroDias := TIMESTAMPDIFF(DAY, fecha_vencimiento, DATE(NOW()) ) as dias,
            @interesDiario:=0 as interesInicia,
            if(@numeroDias < 0, @interesDiario:=0, FORMAT(@interesDiario:= @numeroDias*0.1083333333,2)) as interesDiario,
            FORMAT((factura.pendiente_cobro + @interesDiario),2) as acumulado

        from factura
        inner join cliente on (factura.cliente_id = cliente.id)
        where   factura.pendiente_cobro <> 0 and cliente_id = '". $id."'");

        return Datatables::of($interes_cuentas)
                ->addColumn('opciones', function ($interes_cuenta) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">


                    <li>
                    <a class="dropdown-item"   ><i class="fa-solid fa-xmark text-danger"></i> Desactivar </a>
                    </li>

                </ul>
            </div>
                ';
                })


                ->rawColumns(['opciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function exportCuentasPorCobrar($cliente){
        try {


            return Excel::download(new CuentasPorCobrarExport($cliente), 'CuentasPorCobrarCliente.xlsx');

        } catch (QueryException $e) {
            return response()->json([

                'error' => $e,
                "text" => "Ha ocurrido un error.",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
        }
    }

    public function exportCuentasPorCobrarInteres($cliente){
        try {


            return Excel::download(new CuentasPorCobrarInteresExport($cliente), 'CuentasPorCobrarInteresCliente.xlsx');

        } catch (QueryException $e) {
            return response()->json([

                'error' => $e,
                "text" => "Ha ocurrido un error.",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
        }
    }

    public function imprimirEstadoCuenta($idClientepdf){
        $estadoCuenta = DB::select("CALL cuentasx2('".$idClientepdf."');");
        // dd($estadoCuenta[0]->cliente);
        $pdf = PDF::loadView('/pdf/estadocuenta', compact('estadoCuenta'))->setPaper('letter')->setPaper("A4", "landscape");

        return $pdf->stream("estado_cuenta.pdf");
    }


}
