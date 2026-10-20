<?php

namespace App\Livewire\BoletaCompra;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DataTables;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;
use App\Support\BoletasCompras;
use App\Exports\BoletasComprasExport;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class HistorialBoletaCompra extends Component
{
    public function render()
    {
        $fechaActual = date('n');
        $resta       = $fechaActual - 2;
        $mesActual   = 0;
        $AnioActual  = date('Y');

        if ($resta <= 0) {
            $mesActual  = 12;
            $AnioActual = $AnioActual - 1;
        } elseif ($resta > 0 && $resta < 10) {
            $mesActual = '0' . $resta;
        } else {
            $mesActual = date('m');
        }

        $fechaInicio = $AnioActual . '-' . $mesActual . '-01';

        return view('livewire.boletacompra.historialboletacompra', compact('fechaInicio'));
    }

    public function listadoBoletaCompra(Request $request)
    {
        $filtros = BoletasCompras::filtros($request->only('fechaInicio', 'fechaFinal', 'buscar'));
        try {
            $listado = BoletasCompras::historial($filtros)
                ->join('users as u', 'u.id', '=', 'b.users_id')
                ->select('b.*', 'u.name as registrado_por')->orderByDesc('b.id')->get()
                ->map(function ($boleta) {
                    $boleta->estado_reporte = BoletasCompras::estado($boleta);
                    $boleta->fecha = \Carbon\Carbon::parse($boleta->fecha)->format('d/m/Y');
                    $boleta->total = number_format((float) $boleta->total, 2);
                    return $boleta;
                });
            $auditorias = DB::table('boleta_compra_auditoria as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.usuario_id')
                ->whereIn('a.boleta_compra_id', $listado->pluck('id'))
                ->select('a.*', 'u.name as usuario')->orderByDesc('a.id')->get()->groupBy('boleta_compra_id');
            foreach ($listado as $boleta) {
                $boleta->historial = $auditorias->get($boleta->id, collect())->values();
            }

            return Datatables::of($listado)
                ->addColumn('opciones', function ($boleta) {
                    $acciones = (int) $boleta->estado === 1 && !$boleta->incluir_reporte_compras
                        ? '<li><a class="dropdown-item" href="/boleta/compra/editar/' . $boleta->id . '">Editar</a></li>
                           <li><a class="dropdown-item" onclick="anularBoleta(' . $boleta->id . ')">Anular</a></li>' : '';
                    return
                        '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                        <ul class="dropdown-menu" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                            ' . $acciones . '
                            <li><a class="dropdown-item" href="/boleta/compra/imprimir/' . $boleta->id . '" target="_blank"><i class="fa-solid fa-file-invoice"></i> Imprimir Original</a></li>
                            <li><a class="dropdown-item" href="/boleta/compra/imprimir/copia/' . $boleta->id . '" target="_blank"><i class="fa-solid fa-file-invoice"></i> Imprimir Copia</a></li>
                        </ul>
                        </div>';
                })
                ->rawColumns(['opciones'])
                ->make(true);
        } catch (QueryException $e) {
            report($e);
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e,
            ], 402);
        }
    }

    public function cambiarReporteCompras(Request $request)
    {
        abort_unless(auth()->check(), 401);
        $datos = $request->validate([
            'seleccionados' => 'required|array|min:1',
            'seleccionados.*' => 'required|integer|min:1|distinct',
            'accion' => 'required|in:incluir,quitar',
        ]);
        $cantidad = DB::transaction(function () use ($datos) {
            $boletas = DB::table('boleta_compra')->whereIn('id', $datos['seleccionados'])
                ->orderBy('id')->lockForUpdate()->get();
            if ($boletas->count() !== count($datos['seleccionados']) || $boletas->contains(fn ($b) => (int) $b->estado !== 1)) {
                throw ValidationException::withMessages(['seleccionados' => 'Seleccione solo boletas activas existentes.']);
            }
            $incluir = $datos['accion'] === 'incluir';
            $cambios = $boletas->filter(fn ($b) => (bool) $b->incluir_reporte_compras !== $incluir);
            if ($cambios->isEmpty()) {
                throw ValidationException::withMessages(['seleccionados' => 'Las boletas seleccionadas ya tienen ese estado.']);
            }
            foreach ($cambios as $boleta) {
                DB::table('boleta_compra')->where('id', $boleta->id)
                    ->update(['incluir_reporte_compras' => $incluir, 'updated_at' => now()]);
                DB::table('boleta_compra_auditoria')->insert([
                    'boleta_compra_id' => $boleta->id, 'usuario_id' => auth()->id(),
                    'accion' => $incluir ? 'incluir_compras' : 'quitar_compras',
                    'antes' => json_encode(['incluir_reporte_compras' => (bool) $boleta->incluir_reporte_compras]),
                    'despues' => json_encode(['incluir_reporte_compras' => $incluir]),
                    'created_at' => now(),
                ]);
            }
            return $cambios->count();
        });
        return response()->json(['message' => "Se actualizaron {$cantidad} boletas.", 'cantidad' => $cantidad]);
    }

    public function exportar(Request $request)
    {
        abort_unless(auth()->check(), 401);
        return Excel::download(new BoletasComprasExport(
            BoletasCompras::filtros($request->only('fechaInicio', 'fechaFinal', 'buscar'))
        ), 'BoletasCompras.xlsx');
    }

    private function _datosBoleta($id)
    {
        $boleta = DB::SELECTONE("
            SELECT b.*, u.name AS registrado_por
            FROM boleta_compra b
            INNER JOIN users u ON b.users_id = u.id
            WHERE b.id = " . (int)$id);

        $detalles = DB::SELECT("
            SELECT linea, descripcion,
                   FORMAT(precio,   2) AS precio,
                   FORMAT(cantidad, 2) AS cantidad,
                   FORMAT(importe,  2) AS importe
            FROM boleta_compra_detalle
            WHERE boleta_compra_id = " . (int)$id . "
            ORDER BY linea ASC");

        $caiBoleta = null;
        if (!empty($boleta->cai_boleta_id)) {
            $caiBoleta = DB::SELECTONE("
                SELECT cai,
                       DATE_FORMAT(fecha_limite_emision, '%d/%m/%Y') AS fecha_limite_emision,
                       numero_inicial,
                       numero_final,
                       prefijo,
                       punto_de_emision
                FROM cai_boleta_compra
                WHERE id = " . (int)$boleta->cai_boleta_id);
        }

        $total        = (float)$boleta->total;
        $flagCentavos = (fmod($total, 1) != 0.0);

        $formatter          = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras       = $formatter->toMoney($total, 2, 'LEMPIRAS', 'CENTAVOS');

        return compact('boleta', 'detalles', 'caiBoleta', 'numeroLetras', 'flagCentavos');
    }

    public function imprimirOriginal($id)
    {
        $data = $this->_datosBoleta($id);
        $pdf  = PDF::loadView('/pdf/boletaCompra', $data)->setPaper('letter');
        return $pdf->stream('boleta_compra_' . $data['boleta']->numero_boleta . '.pdf');
    }

    public function imprimirCopia($id)
    {
        $data = $this->_datosBoleta($id);
        $pdf  = PDF::loadView('/pdf/boletaCompra_copia', $data)->setPaper('letter');
        return $pdf->stream('boleta_compra_copia_' . $data['boleta']->numero_boleta . '.pdf');
    }

    public function actualizarBoletaCompra(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id'         => 'required|integer',
            'cliente'    => 'required|string|max:255',
            'fecha'      => 'required|date',
            'conceptos'  => 'required|string',
            'total'      => 'required|numeric|min:0.01',
            'rtn_dni'    => 'nullable|string|max:50',
            'telefono'   => 'nullable|string|max:50',
            'comentario' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $conceptos = json_decode($request->conceptos, true);

        if (empty($conceptos)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Debe agregar al menos un concepto de compra.',
            ], 422);
        }

        $id = (int)$request->id;

        DB::beginTransaction();
        try {
            $boletaActual = DB::table('boleta_compra')->where('id', $id)->lockForUpdate()->first();
            if (!$boletaActual || (int) $boletaActual->estado !== 1 || $boletaActual->incluir_reporte_compras) {
                DB::rollBack();
                return response()->json(['message' => 'No se puede editar una boleta anulada o incluida en Compras. Primero retirela del reporte.'], 422);
            }
            DB::table('boleta_compra')->where('id', $id)->update([
                'cliente'     => $request->cliente,
                'direccion'   => $request->direccion ?? '',
                'rtn_dni'     => $request->rtn_dni ?: null,
                'telefono'    => $request->telefono ?: null,
                'comentario'  => $request->comentario ?: null,
                'fecha'       => $request->fecha,
                'sub_total'   => $request->total,
                'total'       => $request->total,
                'editado_por' => \Auth::user()->name,
                'editado_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('boleta_compra_detalle')->where('boleta_compra_id', $id)->delete();

            foreach ($conceptos as $index => $concepto) {
                DB::table('boleta_compra_detalle')->insert([
                    'boleta_compra_id' => $id,
                    'linea'            => $index + 1,
                    'descripcion'      => $concepto['descripcion'],
                    'precio'           => $concepto['precio'],
                    'cantidad'         => $concepto['cantidad'],
                    'importe'          => $concepto['importe'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            $boleta = DB::table('boleta_compra')->where('id', $id)->first();

            return response()->json([
                'status'        => 'success',
                'message'       => 'Boleta actualizada correctamente.',
                'id'            => $id,
                'numero_boleta' => $boleta->numero_boleta,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar la boleta.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
