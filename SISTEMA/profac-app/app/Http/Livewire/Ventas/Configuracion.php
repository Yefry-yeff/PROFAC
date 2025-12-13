<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use Excel;

use App\Models\ModelParametro;


use App\Exports\declaracionesExport;

class Configuracion extends Component
{
    public function render()
    {
        return view('livewire.ventas.configuracion');
    }

    public function parametros(){
       try {

    $datos = DB::SELECTONE("select estado_encendido as st from parametro where id = 1");

       return response()->json([
        "datos"=>$datos
       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'message' => 'Ha ocurrido un error', 
        'error' => $e
       ],402);
       }
    }

    public function datosCompra(){
       try {

        $datosCai = DB::SELECTONE("
        select cai, numero_inicial, cantidad_otorgada, numero_actual, serie from cai where tipo_documento_fiscal_id = 1 and estado_id=1
        ");

        $comprasMesActual = DB::SELECTONE("
        select 
        sum(total) as total_mes_actual,
        DATE_FORMAT( fecha_emision,'%c') as 'mes_actual'
        from compra
        where DATE_FORMAT( fecha_emision, '%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m') 
        group by DATE_FORMAT( fecha_emision, '%Y-%m'),DATE_FORMAT( fecha_emision,'%c')
        ");
        
        if(empty($comprasMesActual->total_mes_actual)){
       
            
            $comprasMesActual= DB::SELECTONE("
            select 
            0 as total_mes_actual,
            DATE_FORMAT( NOW(),'%c') as 'mes_actual'
                ");
            
        }   
        
      

        $comprasMesAnterior = DB::SELECTONE("
        select 
        sum(total) as total_mes_anterior,
        DATE_FORMAT( fecha_emision,'%c') as 'mes_anterior'
        from compra
        where DATE_FORMAT( fecha_emision, '%Y-%c') = concat(DATE_FORMAT(CURDATE(),'%Y'),'-', (DATE_FORMAT(CURDATE(),'%c')-1) ) 
        group by DATE_FORMAT( fecha_emision, '%Y-%m'),DATE_FORMAT( fecha_emision,'%c')
        ");
       
        if(Empty($comprasMesAnterior->total_mes_anterior)){
            $comprasMesAnterior= DB::SELECTONE("
            select 
            0 as total_mes_anterior,
            (DATE_FORMAT( NOW(),'%c')-1) as 'mes_anterior'
                ");
        }       

        $mesAnteriorNombre = $this->obtenerMes($comprasMesAnterior->mes_anterior);
        $mesActualNombre = $this->obtenerMes($comprasMesActual->mes_actual);

        $arrayTotales = [$comprasMesAnterior->total_mes_anterior,$comprasMesActual->total_mes_actual];
        $arrayMes = [$mesAnteriorNombre,$mesActualNombre];

       return response()->json([
        'arrayTotales'=>$arrayTotales,
        'arrayMes'=>$arrayMes,
        'datosCai'=> $datosCai,
       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'message' => 'Ha ocurrido un error', 
        'error' => $e
       ],402);
       }
    }

    public function datosMesActual(){
       try {

            $ventasP = DB::SELECTONE("
            select 
            sum(total) total_venta_mes_actual     
            from factura
            where DATE_FORMAT(fecha_emision,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')
            and estado_factura_id = 1
            ");

            if(empty($ventasP->total_venta_mes_actual)){
                $ventasP = DB::SELECTONE("select 0 as total_venta_mes_actual");
            }
            
            $ventasNP = DB::SELECTONE("
            select 
            if(sum(total) is null, 0, sum(total)) as total_venta_mes_actual
            from factura
            where DATE_FORMAT(fecha_emision,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')
            and estado_factura_id = 2
            ");

            if(empty($ventasNP->total_venta_mes_actual)){
                $ventasNP = DB::SELECTONE("select 0  as total_venta_mes_actual");
            }



            $numeroMes = DB::SELECTONE("select  DATE_FORMAT( curdate(),'%c') as 'mes_actual'");

            $arrayTotales = [ $ventasP->total_venta_mes_actual,$ventasNP->total_venta_mes_actual];
            $nombreMes = $this->obtenerMes($numeroMes->mes_actual);

            return response()->json([
                    "arrayTotales"=>$arrayTotales,
                    "nombreMes"=>$nombreMes
            ],200);


       } catch (QueryException $e) {
       return response()->json([
        'message' => 'Ha ocurrido un error', 
        'error' => $e
       ],402);
       }
    }

    public function datosMesAnterior(){
       try {

       $ventasP = DB::SELECTONE("
       
       select 
       if(sum(total) is null, 0,sum(total) ) total_venta_mes_anterior     
       from factura
       where DATE_FORMAT(fecha_emision,'%Y-%c') = CONCAT(DATE_FORMAT(CURDATE(),'%Y'),'-',(DATE_FORMAT(CURDATE(),'%c')-1))
       and estado_factura_id = 1

       "); 

       $ventasNP = DB::SELECTONE("
       
       select 
       if(sum(total) is null, 0,sum(total) ) total_venta_mes_anterior     
       from factura
       where DATE_FORMAT(fecha_emision,'%Y-%c') = CONCAT(DATE_FORMAT(CURDATE(),'%Y'),'-',(DATE_FORMAT(CURDATE(),'%c')-1))
       and estado_factura_id = 2

       "); 

       $numeroMes = DB::SELECTONE("select  (DATE_FORMAT( curdate(),'%c')-1) as 'mes_anterior'");
       $arrayTotales = [$ventasP->total_venta_mes_anterior,$ventasNP->total_venta_mes_anterior];

       $nombreMes = $this->obtenerMes($numeroMes->mes_anterior);

       return response()->json([
        "arrayTotales"=>$arrayTotales,     
        "nombreMes"=>$nombreMes,
       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'message' => 'Ha ocurrido un error', 
        'error' => $e
       ],402);
       }
    }

    public function obtenerMes($numeroMes){

        if($numeroMes==1){
            return 'Enero';
        }

        if($numeroMes==2){
            return 'Febrero';
        }

        if($numeroMes==3){
            return 'Marzo';
        }

        if($numeroMes==4){
            return 'Abril';
        }

        if($numeroMes==5){
            return 'Mayo';
        }

        if($numeroMes==6){
            return 'Junio';
        }

        if($numeroMes==7){
            return 'Julio';
        }

        if($numeroMes==8){
            return 'Agosto';
        }

        if($numeroMes==9){
            return 'Septiembre';
        }

        if($numeroMes==10){
            return 'Octubre';
        }

        if($numeroMes==11){
            return 'Noviembre';
        }

        if($numeroMes==12){
            return 'Diciembre';
        }

        if($numeroMes <= 0){
            return 'Diciembre';
        }

        return 'Error';

    }

    public function editarEstado($estado){
       try {


            if($estado==1 || $estado==0){
                $config = ModelParametro::find('1');
                $config->estado_encendido = $estado;
                $config->save();    
            }else{
                return response()->json([
                    "text"=>"Valor incorrecto.",
                    "icon"=>"warning",
                    "title"=>"Advertencia!"
                   ],200);
            }


       return response()->json([
        "text"=>"editado con exito.",
        "icon"=>"success",
        "title"=>"exito"
       ],200);
       } catch (QueryException $e) {
       return response()->json([
        'message' => 'Ha ocurrido un error', 
        'error' => $e,
        "text"=>"Ha ocurrido un error.",
        "icon"=>"error",
        "title"=>"Error!"
       ],402);
       }
    }

    public function exportarExcel(){
        return Excel::download(new declaracionesExport, 'facturas.xlsx');
    }

}
