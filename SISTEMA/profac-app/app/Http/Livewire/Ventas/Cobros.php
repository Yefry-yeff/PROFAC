<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use Validator;
use Illuminate\Support\Facades\File;
use PDF;

use App\Models\ModelpagoVenta;
use App\Models\ModelFactura;
use App\Models\ModelBanco;
use App\Models\ModelTipoPagoCobro;
use App\Models\ModelCliente;
class Cobros extends Component
{
    public $idFactura;
    public function mount($id)
    {

        $this->idFactura = $id;
    }
    public function render()
    {
        $idFactura = $this->idFactura;
        $datosFactura = DB::SELECTONE(
            "select
            numero_factura,
            cai,
            nombre_cliente
            from factura
            where id = ".$idFactura
        );

        $bancos = ModelBanco::all();
        return view('livewire.ventas.cobros',compact('datosFactura','idFactura','bancos'));
    }

    public function registrarPago(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'numero_factura' => 'required',
                'cai' => 'required',
                'cliente' => 'required',
                'monto' => 'required',
                'fecha_pago' => 'required',
                'metoPago'=>'required'


            ], [
                'numero_factura' => 'Numero de factura es requerido',
                'cai' => 'Numero de compra es requerido',
                'cliente' => 'El cliente es requerido',
                'monto' => 'El monto es requerido',
                'fecha_pago' => 'La fecha de pago es requerida',
                'metoPago'=>'El metodo de pago es requerido'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'text'=>'Datos ó formato de imagen invalido.',
                    'title'=>'Error!',
                    'mensaje' => 'Ha ocurrido un error.',
                    'errors' => $validator->errors()
                ], 200);
            }



            $totalCompra = DB::SELECTONE("select total from factura where estado_venta_id=1 and id=".$request->idFactura);
           // $subTotalCompra = DB::SELECTONE("select sub_total from factura where id=".$request->idFactura);
            $totalPagos = DB::SELECTONE("select sum(monto) as monto from pago_venta where estado_venta_id=1 and factura_id = ".$request->idFactura);



            $faltantePago = round($totalCompra->total,2) - round($totalPagos->monto,2);

            if($faltantePago <= 0){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"El total de la factura ya ha sido cobrado. no puede realizar mas cobros a la deuda.",
                    "title"=>"Advertencia!"

                ],200);

            }

            $comprobarPago= $faltantePago - round($request->monto,2);

            if($comprobarPago < -1){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"El monto de pago, supera la deuda existente, el registro de pago no puede ser procesado.",
                    "title"=>"Advertencia!"

                ],200);

            }

            if($request->metoPago ==2 and !($request->banco)){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"Por favor, seleccionar un Banco y Número de cuenta.",
                    "title"=>"Advertencia!"

                ],200);
            }

            DB::beginTransaction();

            $file = $request->file('img_pago');
            if($file != NULL){

            $name = 'IMG_'. time()."-". '.' . $file->getClientOriginalExtension();
            $path = public_path() . '/documentos_ventas';
            $file->move($path, $name);
            }else{
                $name = '';
            }

                if($request->metoPago ==1 ){
                    $registroPago = new ModelpagoVenta;
                    $registroPago->monto = $request->monto;
                    $registroPago->fecha = $request->fecha_pago;
                    $registroPago->users_id = Auth::user()->id;
                    $registroPago->estado_venta_id =1;
                    $registroPago->factura_id = $request->idFactura;
                    $registroPago->url_img =  $name;
                    $registroPago->tipo_pago_id = 1;
                    $registroPago->save();
                }else{
                    $registroPago = new ModelpagoVenta;
                    $registroPago->monto = $request->monto;
                    $registroPago->fecha = $request->fecha_pago;
                    $registroPago->users_id = Auth::user()->id;
                    $registroPago->estado_venta_id =1;
                    $registroPago->factura_id = $request->idFactura;
                    $registroPago->url_img =  $name;
                    $registroPago->tipo_pago_id = 2;
                    $registroPago->banco_id=$request->banco;
                    $registroPago->save();
                }



                $totalCompra = DB::SELECTONE("select total from factura where id=".$request->idFactura);
                $totalPagos = DB::SELECTONE("select sum(monto) as monto from pago_venta where estado_venta_id = 1 and factura_id = ".$request->idFactura);

                $faltantePago = round($totalCompra->total,2) - round($totalPagos->monto,2);

                    $compra = ModelFactura::find($request->idFactura);
                    $compra->pendiente_cobro = round($faltantePago,2);
                    $compra->save();

             /*comprueba si es de credito para devolver el credito */
            if($compra->tipo_pago_id == 2){
                $cliente = ModelCliente::find($compra->cliente_id);
                $cliente->credito = $cliente->credito + $request->monto;
                $cliente->save();
            }

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Registro de pago realizo con exito!",
                "title"=>"Exito!"


            ],200);
        } catch (QueryException $e) {
            DB::rollback();

            $carpetaPublic = public_path();
            $path = $carpetaPublic.'/documentos_ventas/'.$name;

            File::delete($path);

            return response()->json([
                "message" => "Ha ocurrido un error",
                "error" => $e
            ],402);
        }
    }

    public function DatosCompra(Request $request){
        try {

         $factura = DB::SELECTONE("select total, pendiente_cobro from factura where id=".$request->idFactura);

        return response()->json([
            "factura"=>$factura,

        ]);

        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
        ]);
        }
     }

     public function listarPagos($id){


        try {

         $listaPagos = DB::SELECT("
         select
         @i := @i + 1 as contador,
         B.id,
         B.url_img,
         format(B.monto,2) as monto,
         C.descripcion as 'tipo_pago',
         (select concat(nombre,'-',cuenta) from banco where id = B.banco_id ) as banco,
         B.fecha,
         users.name,
         B.created_at,
         A.cai,
         A.numero_factura,
         A.id as idFactura

        from factura A
        inner join pago_venta B
        on A.id = B.factura_id
        inner join users
        on B.users_id = users.id
        inner join tipo_pago_cobro C
        on C.id = B.tipo_pago_id
        CROSS JOIN (select @i := 0) compra
        where A.estado_venta_id = 1 and B.estado_venta_id= 1 and A.id = ".$id
        );

         return Datatables::of($listaPagos)
         ->addColumn('opciones', function ($listaPagos) {

             return
             '
             <div class="text-center">
                 <button class="btn btn-danger " onclick="confirmarEliminarPago('.$listaPagos->id.')"><i class="fa-solid fa-trash-can "></i></button>
             </div>




         ';
         })
         ->addColumn('documento', function ($listaPagos) {

             return
             '
             <div class="text-center ">
                     <a href="/documentos_ventas/'.$listaPagos->url_img.'" target="_blank" class=""><i class="fa-solid fa-file-pdf text-danger" style="font-size: 2rem;"></i></a>
             </div>




         ';
         })

         ->rawColumns(['opciones','documento'])
         ->make(true);

        } catch (QueryException $e) {
        return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
        ]);
        }

     }

     public function eliminarPago(Request $request){
        try {

            DB::beginTransaction();

            $pago = ModelPagoVenta::find($request->idPago);
            $pago->estado_venta_id=2;
            $pago->users_id_elimina = Auth::user()->id;
            $pago->fecha_eliminado = now();
            $pago->save();

            $idFactura = $pago->factura_id;

            $totalCompra = DB::SELECTONE("select total from factura where id=".$idFactura);
            $totalPagos = DB::SELECTONE("select sum(monto) as monto from pago_venta where estado_venta_id = 1 and factura_id = ".$idFactura);
             // conteo de cuantos pagos activos existen
           // $numeroPagos = DB::SELECTONE("select count(id) as 'numero_pagos' from pago_compra where estado_id=1 and compra_id = ".$pago->idFactura);

            $faltantePago = round($totalCompra->total,2) - round($totalPagos->monto,2);


                $compra = ModelFactura::find($idFactura);
                $compra->pendiente_cobro = round( $faltantePago,2);
                $compra->save();


                DB::commit();
        return response()->json([
            "message" => "Eliminado con exito!"
        ]);
        } catch (QueryException $e) {
            DB::rollback();
        return response()->json([
            'message' => 'Ha ocurrido un error al eliminar el pago.',
            'error' => $e
        ]);
        }

    }

}
