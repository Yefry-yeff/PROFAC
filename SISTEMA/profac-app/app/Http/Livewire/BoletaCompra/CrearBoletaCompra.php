<?php

namespace App\Http\Livewire\BoletaCompra;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Auth;
use Validator;

class CrearBoletaCompra extends Component
{
    public function render()
    {
        return view('livewire.boletacompra.crearboletacompra');
    }

    public function guardarBoletaCompra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente'   => 'required|string|max:255',
            'fecha'     => 'required|date',
            'conceptos' => 'required|string',
            'total'     => 'required|numeric|min:0.01',
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

        DB::beginTransaction();
        try {
            $idBoleta = DB::table('boleta_compra')->insertGetId([
                'numero_boleta' => '',
                'cliente'       => $request->cliente,
                'direccion'     => $request->direccion ?? '',
                'fecha'         => $request->fecha,
                'sub_total'     => $request->total,
                'total'         => $request->total,
                'estado'        => 1,
                'users_id'      => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $numeroBoleta = 'BC-' . str_pad($idBoleta, 4, '0', STR_PAD_LEFT);
            DB::table('boleta_compra')
                ->where('id', $idBoleta)
                ->update(['numero_boleta' => $numeroBoleta]);

            foreach ($conceptos as $index => $concepto) {
                DB::table('boleta_compra_detalle')->insert([
                    'boleta_compra_id' => $idBoleta,
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

            return response()->json([
                'status'        => 'success',
                'message'       => 'Boleta de compra registrada correctamente.',
                'id'            => $idBoleta,
                'numero_boleta' => $numeroBoleta,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al guardar la boleta de compra.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function anularBoletaCompra(Request $request)
    {
        try {
            DB::table('boleta_compra')
                ->where('id', $request->id)
                ->update(['estado' => 2, 'updated_at' => now()]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Boleta anulada correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al anular la boleta.',
            ], 500);
        }
    }
}
