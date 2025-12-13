<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\ModelCompra; 
use App\Models\ModelLogEstadoCompra;
use Auth;


class AnularCompra extends Component
{
    public function render()
    {
        return view('livewire.inventario.anular-compra');
    }

    public function anularCompraRegistro(Request $request){

       try {

        $numeroPagos = DB::SELECTONE("select count(id) as numero_pagos from pago_compra where estado_id = 1 and compra_id = ".$request->idCompra);

        if($numeroPagos->numero_pagos > 0)
        return response()->json([
            "text" =>"Esta factura no puede ser anulada, debido que ya cuenta con uno ó más registros de pagos.",
            "icon" => "warning",
            "title" => "Advertencia!",
        ],200);

        $estadoCompra = DB::SELECTONE("select estado_compra_id from compra where id =".$request->idCompra );

        $compra = ModelCompra::find($request->idCompra);
        $compra->estado_compra_id = 2;
        $compra->save();

        $logEstado = new ModelLogEstadoCompra;
        $logEstado->compra_id = $request->idCompra;
        $logEstado->estado_anterior_compra = $estadoCompra->estado_compra_id;
        $logEstado->users_id = Auth::user()->id;
        $logEstado->save();

        

       return response()->json([
           "text" =>"Factura anulada con exito",
           "icon" => "success",
           "title" => "Exito",
       ],200);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error', 
           'error' => $e
       ], 402);
       }

    }
}
