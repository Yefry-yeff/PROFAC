<?php

namespace App\Http\Livewire\NotaCredito;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotasCreditoExport;
use Livewire\Component;

class ListadoNotasND extends Component
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

        $clientes = DB::select("SELECT id, nombre FROM cliente ORDER BY nombre ASC");
        $motivos  = DB::select("SELECT id, descripcion FROM motivo_nota_credito ORDER BY descripcion ASC");
        $usuarios = DB::select("SELECT id, name FROM users WHERE estado_id = 1 ORDER BY name ASC");

        return view('livewire.nota-credito.listado-notas-nd', compact('fechaInicio', 'clientes', 'motivos', 'usuarios'));
    }

    public function listadoNotaCreditoND(Request $request){
        try{
            $listado = DB::SELECT("
            select
            fa.id as idFactura,
            A.id as codigo,
            A.cai,
            cli.nombre as cliente,
            fa.cai as factura,
            B.descripcion as motivo,
            A.comentario,
            A.sub_total as sub_total,
            A.isv as isv,
            A.total as total,
            A.created_at as fecha_registro,
            name as registrado_por
            from nota_credito A
            inner join motivo_nota_credito B on A.motivo_nota_credito_id = B.id
            inner join users on A.users_id = users.id
            inner join factura fa on fa.id = A.factura_id
            inner join cliente cli on cli.id = fa.cliente_id
            where
            fa.tipo_venta_id = 1
            and fa.estado_venta_id <> 2
            and estado_nota_id <> 2
            and A.fecha BETWEEN '".$request->fechaInicio."' and '".$request->fechaFinal."'"
            . ($request->cliente_id ? " and cli.id = " . intval($request->cliente_id) : "")
            . ($request->motivo_id  ? " and A.motivo_nota_credito_id = " . intval($request->motivo_id) : "")
            . ($request->user_id    ? " and A.users_id = " . intval($request->user_id) : "")
            );

            return Datatables::of($listado)
            ->addColumn('opciones', function ($nota) {
                    return
                    '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                        <li><a class="dropdown-item" onclick="anularNota('.$nota->codigo.','.$nota->idFactura.' )" class="btn btn-sm btn-warning "><i class="fa-solid fa-trash"></i> Anular</a></li>
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Original</a></li>
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/copia/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Copia</a></li>
                        </ul>
                    </div>';
            })
            ->rawColumns(['opciones'])
            ->make(true);

           } catch (QueryException $e) {
           return response()->json([
            'icon' => '', 'text' => '', 'title' => '',
            'message' => 'Ha ocurrido un error', 'error' => $e,
           ],402);
           }
    }

    public function exportarExcel(Request $request)
    {
        try {
            $listado = DB::SELECT("
                SELECT A.id as codigo, A.cai, cli.nombre as cliente, fa.cai as factura,
                    B.descripcion as motivo, A.comentario, A.sub_total, A.isv, A.total,
                    A.created_at as fecha_registro, users.name as registrado_por
                FROM nota_credito A
                INNER JOIN motivo_nota_credito B ON A.motivo_nota_credito_id = B.id
                INNER JOIN users ON A.users_id = users.id
                INNER JOIN factura fa ON fa.id = A.factura_id
                INNER JOIN cliente cli ON cli.id = fa.cliente_id
                WHERE fa.tipo_venta_id = 1
                AND fa.estado_venta_id <> 2
                AND estado_nota_id <> 2
                AND A.fecha BETWEEN '".$request->fechaInicio."' AND '".$request->fechaFinal."'"
                . ($request->cliente_id ? " AND cli.id = " . intval($request->cliente_id) : "")
                . ($request->motivo_id  ? " AND A.motivo_nota_credito_id = " . intval($request->motivo_id) : "")
                . ($request->user_id    ? " AND A.users_id = " . intval($request->user_id) : "")
                . " ORDER BY A.fecha DESC"
            );
            $usuario = Auth::check() ? Auth::user()->name : 'Sistema';
            return Excel::download(
                new NotasCreditoExport($listado, $usuario, 'Notas de Crédito — Clientes B'),
                'NotasCreditoB_' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
