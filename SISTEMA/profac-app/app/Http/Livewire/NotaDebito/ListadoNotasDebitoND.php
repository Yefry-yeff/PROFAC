<?php

namespace App\Http\Livewire\NotaDebito;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Database\QueryException;
use App\Models\NotaDebito\notaDebito;

class ListadoNotasDebitoND extends Component
{
    public function render()
    {
        $fechaActual = date('n');
        $resta = $fechaActual - 2;

        $mesActual =0;
        $AnioActual = date('Y');

        if($resta<=0){
            $mesActual=12;
            $AnioActual = $AnioActual - 1;
        }elseif($resta>0 && $resta<10){
            $mesActual = '0'.$resta;
        }else{
            $mesActual = date('m');
        }


        $fechaInicio = $AnioActual.'-'.$mesActual.'-01';

        return view('livewire.nota-debito.listado-notas-debito-nd',compact('fechaInicio'));
    }

    public function listarnotasDebito($fechaInicio,$fechaFinal){
        try {

            $listanotaDebito = DB::SELECT("
            select
            notadebito.id
            ,factura_id
            ,factura.cai
            ,cli.nombre as cliente
            ,monto_asignado
            ,fechaEmision
            ,motivoDescripcion
            ,cai_ndebito
            ,numeroCai
            ,correlativoND
            ,(select name from users where id = notadebito.users_registra_id) as 'user'
            ,notadebito.created_at
            from notadebito
            inner join factura
            on notadebito.factura_id = factura.id
            inner join cliente cli on cli.id = factura.cliente_id
            where
            cli.tipo_cliente_id = 2
            and notadebito.created_at >= '".$fechaInicio."' and notadebito.created_at <= '".$fechaFinal."' "
            );


            /* estado_nota_dec = 1
            and factura.tipo_venta_id = 2 */


            return Datatables::of($listanotaDebito)
            ->addColumn('estado', function ($listanotaDebito) {
                $ESTADO = DB::SELECTONE("select estado_id from notadebito where id = ".$listanotaDebito->id);
                if( $ESTADO->estado_id == 1 ){

                    return
                    '
                    <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Activo</span></p>
                    ';

                }else if($ESTADO->estado_id == 2) {
                    return
                    '
                    <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Anulada</span></p>
                    ';
                }

           })
           ->addColumn('file', function ($listanotaDebito) {

            return

            '<div class="btn-group">
            <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                más</button>
                <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li><a class="dropdown-item" href="/debito/imprimir/'.$listanotaDebito->factura_id.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Orginal</a></li>

                    <li><a class="dropdown-item" href="/debito/imprimir/copia/'.$listanotaDebito->factura_id.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Copia</a></li>

                </ul>


            </div>';

            })
            ->addColumn('acciones', function ($listanotaDebito) {
                $ESTADO = DB::SELECTONE("select estado_id, estado_sumado from notadebito where id = ".$listanotaDebito->id);
                if( $ESTADO->estado_id == 1 && $ESTADO->estado_sumado == 2){

                    return '
                    <div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start"
                        style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                        <li><a class="dropdown-item" href="#" onclick="anularnd('.$listanotaDebito->id.')"> <i class="fa fa-check-circle text-danger" aria-hidden="true"></i>
                                Anular</a></li>

                    </ul>
                </div>
                    ';

                }else if($ESTADO->estado_id == 2 || $ESTADO->estado_sumado == 1) {
                    return
                    '
                    <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Sin Acciones</span></p>
                    ';
                }




                    ;

             })
            ->rawColumns(['estado','file','acciones'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las notas de debito.',
                'errorTh' => $e,
            ], 402);

        }
    }


    public function anularNota($idNota){


        try {
            DB::beginTransaction();

            $notaAnular = notaDebito::find($idNota);
            $notaAnular->estado_id = 2;
            $notaAnular->save();

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Nota de débito anulada con éxito!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al anular nota débito.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }
}
