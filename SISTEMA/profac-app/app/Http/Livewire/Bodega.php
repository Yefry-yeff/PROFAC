<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\modelBodega;
use App\Models\Estante;
use App\Models\Repisa;
use App\Models\Seccion;
use App\Models\Pais;
use App\Models\ModelSegmento;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;


class Bodega extends Component
{
    public function render()
    {
        $users = User::all();
        $paises = Pais::all();

        return view('livewire.bodega-component.bodega-crear', compact("users","paises"));
    }

    public function crearBodega(Request $request){
        //dd($request->all());

        $validator = Validator::make($request->all(), [
            'bodega' => 'required',
            'direccionBodega' => 'required',
            'encargadoBodega' => 'required',          
         
            'municipio_bodega' => 'required',
        ], [
            'bodega' => 'Bodega es requerida',
            'direccionBodega' => 'Direccion es requerido',
            'encargadoBodega' => 'Encargado es requerido',
           
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $arregloIdInputsId = [];
            $arregloIdInputsId = $request->arregloIdInputs;
            
            DB::beginTransaction();

            $crearBodega = new modelBodega;
            $crearBodega->nombre = $request->bodega;
            $crearBodega->direccion = $request->direccionBodega;
            $crearBodega->encargado_bodega = $request->encargadoBodega;
            $crearBodega->estado_id = 1;
            $crearBodega->municipio_id = $request->municipio_bodega;
            $crearBodega->save();

   
            $numeroSegmetnos = count($request->arregloIdInputs);

            for ($i=0; $i < $numeroSegmetnos ; $i++) { 
                $idSegemetno = "segmento".$arregloIdInputsId[$i];
                $numeroSecciones = "seccion".$arregloIdInputsId[$i];

                $crearSegemtno = new ModelSegmento;
                $crearSegemtno->descripcion =$request[$idSegemetno];
                $crearSegemtno->bodega_id =$crearBodega->id;
                $crearSegemtno->save();

                    for ($j=1; $j <= $request[$numeroSecciones] ; $j++) { 
                        $crearSeccion = new Seccion;
                        $crearSeccion->descripcion = "Seccion ".$request[$idSegemetno]." ".$j;
                        $crearSeccion->numeracion = $j;
                        $crearSeccion->estado_id = 1;
                        $crearSeccion->segmento_id = $crearSegemtno->id;
                        $crearSeccion->save();

                    }
                
            }

            // for ($i=1; $i <= $request['bodegaNumSec']; $i++) { 

            //     $crearEstante = new Seccion;
            //     $crearEstante->descripcion = $i;
            //     $crearEstante->estado_id = '1';
            //     $crearEstante->id_bodega=  $crearBodega->id;
            //     $crearEstante->save();
                

                     

            // }

            DB::commit();

            return response()->json([
                'message' => 'Creado con exito.',
                
            ], 200);  



        } catch (QueryException $th) {
            DB::rollback(); 
         
            return response()->json([
                'message' => 'Ha ocurrido un error.',
                'errorTh' => $th,
              
              
            ], 402);  

        }   

    }

    public function export(){
        try {
            
            return Excel::download(new BodegaExport, 'DatosBodega.xlsx');
            //return (new InvoicesExport)->download('invoices.xlsx');

        } catch (QueryException $e) {
            return response()->json([
             
                'error' => $e,
                "text" => "Ha ocurrido un error.",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
        }

    }

}
