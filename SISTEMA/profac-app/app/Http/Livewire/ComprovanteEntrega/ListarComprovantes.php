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

use App\Models\ModelRecibirBodega;
use App\Models\ModelComprovanteEntrega;
use App\Models\ModelLogTranslados;
class ListarComprovantes extends Component
{

    public function render()
    {
        return view('livewire.comprovante-entrega.listar-comprovantes');
    }

    public function listarComprovantesActivos()
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
        comprovante_entrega.created_at as fecha_creacion
        from comprovante_entrega
        inner join users
        on comprovante_entrega.users_id = users.id
        where estado_id = 1
        ");
            return Datatables::of($listadoComprobantesActivos)
                ->addColumn('opciones', function ($comprobante) {


                    return
                        '<div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li>
                    <a class="dropdown-item" target="_blank"  href="/orden/entrega/facturar/' . $comprobante->id . '"> <i class="fa-solid fa-file-invoice text-info"></i> Facturar Comprobante </a>
                    </li>

                    <li>
                    <a class="dropdown-item" target="_blank"  href="/comprobante/imprimir/' . $comprobante->id . '"> <i class="fa-solid fa-print text-success"></i> Imprimir Comprobante Original</a>
                    </li>


                    <li>
                    <a class="dropdown-item" target="_blank"  href="/comprobante/imprimir/copia/' . $comprobante->id . '"> <i class="fa-solid fa-print text-success"></i> Imprimir Comprobante Copia</a>
                    </li>

                    <li>
                    <a class="dropdown-item" href="#" onclick="anularComprobanteConfirmar('.$comprobante->id.')"> <i class="fa-solid fa-ban text-danger"></i> Anular Comprobante </a>
                    </li>




                </ul>
            </div>';
                })
                ->addColumn('estado', function ($comprobante) {


                    return
                        '<p class="text-center"><span class="badge badge-primary p-2" style="font-size:0.75rem">Activo</span></p>';
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

    public function anularComprobante(Request $request){

           try {
            DB::beginTransaction();
            $arrayLogs=[];

            $listaProductos = DB::SELECT("
            select
            B.lote_id,
            B.numero_unidades_resta_inventario,
            B.producto_id,
            B.unidad_medida_venta_id
            from comprovante_entrega A
            inner join comprovante_has_producto B
            on A.id = B.comprovante_id
            where A.estado_id = 1 and A.id = ".$request->idComprobante
            );

            foreach ($listaProductos as $producto){
                $lote = ModelRecibirBodega::find($producto->lote_id);
                $lote->cantidad_disponible = $lote->cantidad_disponible + $producto->numero_unidades_resta_inventario;
                $lote->save();

                array_push($arrayLogs, [
                    "origen" => $producto->lote_id,
                    "destino" => $producto->lote_id,
                    "comprovante_entrega_id" => $request->idComprobante,
                    "cantidad" => $producto->numero_unidades_resta_inventario,
                    "unidad_medida_venta_id" => $producto->unidad_medida_venta_id,
                    "users_id" => Auth::user()->id,
                    "descripcion" => "Orden de Entrega - Anulado",
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);

            }
                //dd($request);
            //ModelLogTranslados::insert($arrayLogs);
            DB::table('log_translado')->insert($arrayLogs);

            //dd("llega despues de log traslado");
            $comprobante = ModelComprovanteEntrega::find($request->idComprobante);
            $comprobante->estado_id = 2;
            $comprobante->comentarioAnulado = 'Anulado por '.Auth::user()->name.' Motivo: '.$request->motivo;
            $comprobante->save();


            DB::commit();
           return response()->json([
            'icon' => 'success',
            'text' => 'Comprobante anulado con éxito!',
            'title' => 'Exito',
           ],200);
           } catch (QueryException $e) {
            DB::rollback();
           return response()->json([
            'icon' => 'error',
            'text' => 'Ha ocurrido un error al anular el comprobante',
            'title' => 'Error',
            'message' => 'Ha ocurrido un error',
            'error' => $e,
           ],402);
           }
    }
}
