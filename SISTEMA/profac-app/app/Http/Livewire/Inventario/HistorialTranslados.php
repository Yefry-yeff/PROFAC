<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;

class HistorialTranslados extends Component
{
    public function render()
    {

        $fechaActual = date('n');
        $resta = $fechaActual - 2;
        if($resta<=0){
            $resta=12;
        }

        if($resta<10){
            $resta = '0'.$resta;
        }

        $fechaInicio = date('Y').'-'.$resta.'-01';
        return view('livewire.inventario.historial-translados',compact('fechaInicio'));
    }

    public function historialTranslados(Request $request){
        try {
            session()->save();
            $fechaInicio   = $request->fechaInicio;
            $fechaFinal    = $request->fechaFinal;
            $q             = trim($request->get('q', ''));
            $numTraslado   = trim($request->get('num_traslado', ''));
            $bodegaOrigen  = (int) $request->get('bodega_origen_id', 0);
            $bodegaDestino = (int) $request->get('bodega_destino_id', 0);

            if (empty($fechaInicio) || empty($fechaFinal)) {
                return response()->json(['error' => 'Fechas requeridas'], 400);
            }

            $query = DB::table('translado as t')
                ->join('log_translado as lt',    'lt.translado_id', '=', 't.id')
                ->join('recibido_bodega as rbo',  'rbo.id',          '=', 'lt.origen')
                ->join('producto as p',           'p.id',            '=', 'rbo.producto_id')
                ->join('users as u',              'u.id',            '=', 'lt.users_id')
                ->join('seccion as sco',          'sco.id',          '=', 'rbo.seccion_id')
                ->join('segmento as sgo',         'sgo.id',          '=', 'sco.segmento_id')
                ->join('bodega as bo',            'bo.id',           '=', 'sgo.bodega_id')
                ->leftJoin('recibido_bodega as rbd', 'rbd.id',       '=', 'lt.destino')
                ->leftJoin('seccion as scd',      'scd.id',          '=', 'rbd.seccion_id')
                ->leftJoin('segmento as sgd',     'sgd.id',          '=', 'scd.segmento_id')
                ->leftJoin('bodega as bd',        'bd.id',           '=', 'sgd.bodega_id')
                ->select([
                    't.id as translado_id',
                    't.codigo as num_traslado',
                    'p.id as id_producto',
                    'p.nombre',
                    'lt.cantidad',
                    DB::raw("CONCAT(bo.nombre, ' / ', sgo.descripcion, ' / ', sco.descripcion) as origen_completo"),
                    DB::raw("CONCAT(IFNULL(bd.nombre,'-'), ' / ', IFNULL(sgd.descripcion,'-'), ' / ', IFNULL(scd.descripcion,'-')) as destino_completo"),
                    'u.name',
                    'lt.created_at',
                ])
                ->where('lt.descripcion', 'Translado de bodega')
                ->whereRaw('DATE(lt.created_at) BETWEEN ? AND ?', [$fechaInicio, $fechaFinal]);

            // Búsqueda multi-palabra: cada palabra debe aparecer en al menos uno de los campos
            if ($q !== '') {
                $words = array_values(array_filter(array_map('trim', explode(' ', $q))));
                foreach ($words as $word) {
                    $query->where(function ($wq) use ($word) {
                        $wq->where('p.nombre', 'LIKE', "%{$word}%")
                           ->orWhere('p.codigo_barra', 'LIKE', "%{$word}%")
                           ->orWhere('p.codigo_estatal', 'LIKE', "%{$word}%");
                        if (is_numeric($word) && ctype_digit($word)) {
                            $wq->orWhere('p.id', (int) $word);
                        }
                    });
                }
            }
            if ($numTraslado !== '') $query->where('t.codigo', 'LIKE', "%{$numTraslado}%");
            if ($bodegaOrigen)       $query->where('bo.id', $bodegaOrigen);
            if ($bodegaDestino)      $query->where('bd.id', $bodegaDestino);

            $listado = $query->get();

            return Datatables::of($listado)
                ->addColumn('opciones', function ($row) {
                    return '<div class="text-center">
                        <a href="/translado/imprimir/' . $row->translado_id . '" target="_blank"
                           class="btn btn-sm btn-warning">
                            <i class="fa-solid fa-file-invoice"></i> Imprimir
                        </a></div>';
                })
                ->rawColumns(['opciones'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Error historialTranslados: ' . $e->getMessage());
            return response()->json([
                'error'   => $e->getMessage(),
                'message' => 'Ha ocurrido un error al cargar el historial de traslados',
            ], 500);
        }
    }

    public function historialPorTraslado(Request $request){
        try {
            session()->save();
            $fechaInicio = $request->fechaInicio;
            $fechaFinal  = $request->fechaFinal;

            if (empty($fechaInicio) || empty($fechaFinal)) {
                return response()->json(['error' => 'Fechas requeridas'], 400);
            }

            $listado = DB::table('translado as t')
                ->join('log_translado as lt', 'lt.translado_id', '=', 't.id')
                ->join('users as u',          'u.id',            '=', 'lt.users_id')
                ->select([
                    't.id',
                    't.codigo',
                    DB::raw('IFNULL(t.comentario, "") as comentario'),
                    'u.name',
                    DB::raw('MIN(lt.created_at) as fecha'),
                ])
                ->where('lt.descripcion', 'Translado de bodega')
                ->whereRaw('DATE(lt.created_at) BETWEEN ? AND ?', [$fechaInicio, $fechaFinal])
                ->groupBy('t.id', 't.codigo', 't.comentario', 'u.name')
                ->get();

            return Datatables::of($listado)
                ->addColumn('opciones', function ($row) {
                    return '<div class="text-center">
                        <a href="/translado/imprimir/' . $row->id . '" target="_blank"
                           class="btn btn-sm btn-warning">
                            <i class="fa-solid fa-file-invoice"></i> Imprimir
                        </a></div>';
                })
                ->rawColumns(['opciones'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Error historialPorTraslado: ' . $e->getMessage());
            return response()->json([
                'error'   => $e->getMessage(),
                'message' => 'Ha ocurrido un error',
            ], 500);
        }
    }

    public function listarBodegas(){
        session()->save();
        $bodegas = DB::table('bodega')->select('id', 'nombre')->orderBy('nombre')->get();
        return response()->json($bodegas);
    }

}
