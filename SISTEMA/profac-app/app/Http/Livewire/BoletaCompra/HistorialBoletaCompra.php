<?php

namespace App\Http\Livewire\BoletaCompra;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DataTables;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

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
        try {
            $listado = DB::SELECT("
                SELECT
                    b.id,
                    b.numero_boleta,
                    b.cliente,
                    b.direccion,
                    DATE_FORMAT(b.fecha, '%d/%m/%Y') AS fecha,
                    FORMAT(b.total, 2) AS total,
                    b.estado,
                    u.name AS registrado_por,
                    b.created_at AS fecha_registro
                FROM boleta_compra b
                INNER JOIN users u ON b.users_id = u.id
                WHERE b.estado = 1
                  AND b.fecha BETWEEN '" . $request->fechaInicio . "' AND '" . $request->fechaFinal . "'
                ORDER BY b.id DESC
            ");

            return Datatables::of($listado)
                ->addColumn('opciones', function ($boleta) {
                    return
                        '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                        <ul class="dropdown-menu" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                            <li><a class="dropdown-item" href="/boleta/compra/editar/' . $boleta->id . '"><i class="fa-solid fa-pen-to-square"></i> Editar</a></li>
                            <li><a class="dropdown-item" onclick="anularBoleta(' . $boleta->id . ')"><i class="fa-solid fa-trash"></i> Anular</a></li>
                            <li><a class="dropdown-item" href="/boleta/compra/imprimir/' . $boleta->id . '" target="_blank"><i class="fa-solid fa-file-invoice"></i> Imprimir Original</a></li>
                            <li><a class="dropdown-item" href="/boleta/compra/imprimir/copia/' . $boleta->id . '" target="_blank"><i class="fa-solid fa-file-invoice"></i> Imprimir Copia</a></li>
                        </ul>
                        </div>';
                })
                ->rawColumns(['opciones'])
                ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e,
            ], 402);
        }
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
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar la boleta.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
