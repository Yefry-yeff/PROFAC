<?php

namespace App\Http\Livewire\ComprovanteEntrega;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;

class ListarComprovantesAnulados extends Component
{
    public function render()
    {
        return view('livewire.comprovante-entrega.listar-comprovantes-anulados');
    }

    public function listarComprovantesAnulados()
    {
        try {

            $listadoComprobantesActivos = DB::SELECT("
        select
        comprovante_entrega.id,
        numero_comprovante,
        nombre_cliente,
        RTN,
        fecha_emision,
        FORMAT(sub_total,2) as sub_total,
        FORMAT(isv,2) as isv,
        FORMAT(total,2) as total,
        name,
        comentarioAnulado,
        comprovante_entrega.created_at as fecha_creacion
        from comprovante_entrega
        inner join users
        on comprovante_entrega.users_id = users.id
        where estado_id = 2
        ");
            return Datatables::of($listadoComprobantesActivos)
                ->addColumn('opciones', function ($comprobante) {


                    return
                        '<div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                    <a class="dropdown-item" target="_blank"  href="/comprobante/imprimir/' . $comprobante->id . '"> <i class="fa-solid fa-print text-info"></i> Imprimir Comprobante </a>
                    </li>

                </ul>
            </div>';
                })
                ->addColumn('estado', function ($comprobante) {


                    return
                        '<p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Anulado</span></p>';
                })
                ->rawColumns(['opciones','estado'])
                ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'icon' => 'error',
                'text' => 'Ha ocurrido un error al listar los comprobantes de entrega.',
                'title' => 'Erro!',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }
}
