<?php

namespace App\Http\Livewire\ComprovanteEntrega;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use DataTables;
use App\Models\ModelRecibirBodega;
use App\Models\ModelComprovanteEntrega;

class ListarComprovantes extends Component
{
    public function render()
    {
        return view('livewire.comprovante-entrega.listar-comprovantes');
    }

    public function listarComprovantesActivos(Request $request)
    {
        try {
            $filtroNumero  = trim($request->input('filtroNumero', ''));
            $filtroCliente = trim($request->input('filtroCliente', ''));
            $filtroUsuario = trim($request->input('filtroUsuario', ''));
            $filtroDesde   = trim($request->input('filtroDesde', ''));
            $filtroHasta   = trim($request->input('filtroHasta', ''));

            $listadoComprobantesActivos = DB::table('comprovante_entrega')
                ->join('users', 'comprovante_entrega.users_id', '=', 'users.id')
                ->select([
                    'comprovante_entrega.id',
                    'comprovante_entrega.numero_comprovante',
                    'comprovante_entrega.nombre_cliente',
                    'comprovante_entrega.RTN',
                    DB::raw("DATE_FORMAT(comprovante_entrega.fecha_emision, '%Y-%m-%d') as fecha_emision"),
                    DB::raw('FORMAT(comprovante_entrega.sub_total,2) as sub_total'),
                    DB::raw('FORMAT(comprovante_entrega.isv,2) as isv'),
                    DB::raw('FORMAT(comprovante_entrega.total,2) as total'),
                    'users.name',
                    DB::raw("DATE_FORMAT(comprovante_entrega.created_at, '%Y-%m-%d %H:%i:%s') as fecha_creacion"),
                ])
                ->where('comprovante_entrega.estado_id', 1);

            if ($filtroNumero !== '') {
                $listadoComprobantesActivos->where('comprovante_entrega.numero_comprovante', 'like', "%{$filtroNumero}%");
            }

            if ($filtroCliente !== '') {
                $listadoComprobantesActivos->where('comprovante_entrega.cliente_id', $filtroCliente);
            }

            if ($filtroUsuario !== '') {
                $listadoComprobantesActivos->where('comprovante_entrega.users_id', $filtroUsuario);
            }

            if ($filtroDesde !== '' && $filtroHasta !== '') {
                $listadoComprobantesActivos->whereBetween(DB::raw('DATE(comprovante_entrega.fecha_emision)'), [$filtroDesde, $filtroHasta]);
            } elseif ($filtroDesde !== '') {
                $listadoComprobantesActivos->whereDate('comprovante_entrega.fecha_emision', '>=', $filtroDesde);
            } elseif ($filtroHasta !== '') {
                $listadoComprobantesActivos->whereDate('comprovante_entrega.fecha_emision', '<=', $filtroHasta);
            }

            return Datatables::of($listadoComprobantesActivos)
                ->addColumn('opciones', function ($comprobante) {
                    return '<div class="btn-group">
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
                    <a class="dropdown-item" href="#" onclick="anularComprobanteConfirmar(' . $comprobante->id . ')"> <i class="fa-solid fa-ban text-danger"></i> Anular Comprobante </a>
                    </li>

                </ul>
            </div>';
                })
                ->addColumn('estado', function () {
                    return '<p class="text-center"><span class="badge badge-primary p-2" style="font-size:0.75rem">Activo</span></p>';
                })
                ->rawColumns(['opciones', 'estado'])
                ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Erro!',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }

    public function anularComprobante(Request $request)
    {
        try {
            DB::beginTransaction();
            $arrayLogs = [];

            $listaProductos = DB::select(
                'select
                B.lote_id,
                B.numero_unidades_resta_inventario,
                B.producto_id,
                B.unidad_medida_venta_id
                from comprovante_entrega A
                inner join comprovante_has_producto B
                on A.id = B.comprovante_id
                where A.estado_id = 1 and A.id = ?',
                [$request->idComprobante]
            );

            foreach ($listaProductos as $producto) {
                $lote = ModelRecibirBodega::find($producto->lote_id);
                $lote->cantidad_disponible = $lote->cantidad_disponible + $producto->numero_unidades_resta_inventario;
                $lote->save();

                $arrayLogs[] = [
                    'origen' => $producto->lote_id,
                    'destino' => $producto->lote_id,
                    'comprovante_entrega_id' => $request->idComprobante,
                    'cantidad' => $producto->numero_unidades_resta_inventario,
                    'unidad_medida_venta_id' => $producto->unidad_medida_venta_id,
                    'users_id' => Auth::user()->id,
                    'descripcion' => 'Orden de Entrega - Anulado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('log_translado')->insert($arrayLogs);

            $comprobante = ModelComprovanteEntrega::find($request->idComprobante);
            $comprobante->estado_id = 2;
            $comprobante->comentarioAnulado = 'Anulado por ' . Auth::user()->name . ' Motivo: ' . $request->motivo;
            $comprobante->save();

            DB::commit();
            return response()->json([
                'icon' => 'success',
                'text' => 'Comprobante anulado con éxito!',
                'title' => 'Exito',
            ], 200);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'icon' => 'error',
                'text' => 'Ha ocurrido un error al anular el comprobante',
                'title' => 'Error',
                'message' => 'Ha ocurrido un error',
                'error' => $e,
            ], 402);
        }
    }
}
