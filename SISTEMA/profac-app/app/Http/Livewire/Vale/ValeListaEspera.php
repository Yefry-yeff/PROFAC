<?php

namespace App\Http\Livewire\Vale;

use Livewire\Component;

use App\Http\Livewire\Ventas\FacturacionCorporativa;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelFactura;

use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\User;

use App\Models\ModelVale;
use App\Models\ModelValeHasProducto; 
use App\Models\ModelEsperaProducto;



class ValeListaEspera extends Component
{


    public $idFactura;
    public function mount($id)
    {

        $this->idFactura = $id;
    }
    public function render()
    {
       $idFactura = $this->idFactura;

       $datosFactura = DB::SELECTONE("select numero_factura, porc_descuento  from factura where id =".$idFactura);


        return view('livewire.vale.vale-lista-espera',compact("idFactura","datosFactura"));
    }

    public function obtenerProductosVale(Request $request){
       try {

      
        $productos = DB::SELECT("
        select 
        id,
        concat('cod ',id,' - ',nombre) as text
        from producto
        where nombre like '%". $request->search ."%' or id like '%" . $request->search ."%' 
        limit 15
        ");

        return response()->json([
            "results" => $productos
        ], 200);
     
       } catch (QueryException $e) {
       return response()->json([
        'icon' => 'error',
        'text' => 'Ha ocurrido un error al listar los productos',
        'title' => 'Error!',
        'message' => 'Ha ocurrido un error', 
        'error' => $e,
       ],402);
       }
    }
    
    public function guardarVentaVale(Request $request){
       try{

        $comprobarValeExiste = DB::SELECTONE("select count(id) as contador from vale where (estado_id =1 or estado_id =2) and  factura_id = ".$request->idFactura);

        if($comprobarValeExiste->contador > 0){
            return response()->json([
                'icon' => "warning",
                'text' => 'El vale no puede ser realizado, dado que la factura seleccionada ya cuenta con un vale pendiente o anulado',
                'title' => 'Advertencia!',
                'estadoBorrar' => 'true'
            ], 200);
        }

        $factura = ModelFactura::find($request->idFactura);

        $cliente = ModelCliente::find($factura->cliente_id);

        if(round($request->totalGeneralVP,2) > round($cliente->credito,2)){
            return response()->json([
                'icon' => "warning",
                'text' => 'El vale no puede ser realizado, dado que la factura seleccionada es de tipo “crédito” y el valor del vale excede el crédito disponible del cliente.',
                'title' => 'Advertencia!',
                'estadoBorrar' => 'true'
            ], 200);
        }


        $arrayTemporal = $request->arregloIdInputsVP;            
        $arrayInputs = explode(',', $arrayTemporal);

        $flagProductoExiste = false;
        $mensaje ="El producto o productos:";
        for ($i = 0; $i < count($arrayInputs); $i++) {

          

            $keyIdProducto = "idProductoVP" . $arrayInputs[$i];


            $contadorProducto = DB::SELECTONE("
            select 
                count(producto_id) as contador,
                B.nombre
            from venta_has_producto A
            inner join producto B
            on A.producto_id = B.id where producto_id =".$request->$keyIdProducto." and factura_id =".$request->idFactura."  limit 1");
            
            if($contadorProducto->contador > 0){
                $flagProductoExiste = true;
                $mensaje = $mensaje . " <br><b>Cod." .  $request->$keyIdProducto ." - ". $contadorProducto->nombre . ".</b>";
            }

        }

        if($flagProductoExiste){
            $mensaje = $mensaje . "<br> Ya existe en factura, no se puede agregar a vale.";
            return response()->json([
                'icon' => "warning",
                'text' =>  $mensaje,
                'title' => 'Advertencia!',
                'estadoBorrar' => 'true'
            ], 200);
        }
        
     //    dd($request->all());
        DB::beginTransaction();

        ////Verficar si es factura de credito, para umentar credito y disminuir credito disponible 

       $idVale = $this->guardarVale($request);



        $factura = ModelFactura::find($request->idFactura);        
        $factura->total = ROUND($factura->total + $request->totalGeneralVP,2);
        $factura->isv = Round($factura->isv +  $request->isvGeneralVP,2);
        $factura->sub_total = ROUND($factura->sub_total + $request->subTotalGeneralVP,2);
        $factura->pendiente_cobro = ROUND( $factura->pendiente_cobro + $request->totalGeneralVP,2);

        if($factura->tipo_pago_id == 2){
            $factura->credito = ROUND(($factura->credito + $request->totalGeneralVP),2);

            $cliente = ModelCliente::find($factura->cliente_id);
            $cliente->credito = ROUND($cliente->credito - $request->totalGeneralVP,2);
            
            $cliente->save();


            $credito = new logCredito();
            $credito->descripcion = "Reducción de credito por vale agregado a factura.";
            $credito->monto =  $request->totalGeneralVP;
            $credito->users_id = Auth::user()->id;
            $credito->factura_id = $factura->id;
            $credito->cliente_id = $factura->cliente_id;
            $credito->save();          

        }

        $factura->save();



        $numeroVenta = DB::selectOne("select concat(YEAR(NOW()),'-',count(id)+1)  as 'numero' from factura");
        DB::commit();

        return response()->json([
            'icon' => "success",
            'text' => '
            <div class="d-flex justify-content-between">
                <a href="/vale/imprimir/'.$idVale .'" target="_blank" class="btn btn-sm btn-warning"><i class="fa-solid fa-coins"></i> Imprimir Vale</a>
                <a href="/detalle/venta/' . $request->idFactura . '" target="_blank" class="btn btn-sm btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Detalle de Factura</a>
            </div>',
            'title' => 'Exito!',
            'idFactura' => $request->idFactura,
            'numeroVenta' => '',
            'estadoBorrar' => 'false'

        ], 200);


       }catch(QueryException $e){
        DB::rollback();

        return response()->json([
            'error' => 'Ha ocurrido un error al realizar la factura.',
            'icon' => "error",
            'text' => 'Ha ocurrido un error.',
            'title' => 'Error!',
            'idFactura' => $request->idFactura,
            'mensajeError'=>$e
        ], 402);
       }
    }

    public function guardarVale($request){     
        
        $arrayTemporal = $request->arregloIdInputsVP;            
        $arrayInputs = explode(',', $arrayTemporal);
    
     
        $arrayProductosVale =[];
        $idVale = DB::selectOne("  select id  from vale order by id desc");
        $anio = DB::SELECTONE("select year(now()) as anio");
        $numero_vale = "";

        if (empty($idVale->id)) {
            $numero_vale = $anio->anio . '-' . '1';
        } else {
            $numero_vale = $anio->anio . '-' .($idVale->id + 1) ;
        }


        $vale = new ModelVale;
        $vale->numero_vale = $numero_vale;
        $vale->sub_total = $request->subTotalGeneral;
        $vale->sub_total_grabado=$request->subTotalGeneralGrabado;
        $vale->sub_total_excento=$request->subTotalGeneralExcento;
        $vale->isv = $request->isvGeneral;
        $vale->total = $request->totalGeneral;
        $vale->factura_id = $request->idFactura;
        $vale->users_id = Auth::user()->id;
        $vale->notas = $request->comentario;
        $vale->estado_id = 1;
        $vale->notas = $request->comentario;
        $vale->porc_descuento = $request->porDescuento;
        $vale->monto_descuento = bcdiv($request->descuentoGeneral, '1', 2);
        $vale->save();


        for ($i = 0; $i < count($arrayInputs); $i++) {


            $keyIdProducto = "idProductoVP" . $arrayInputs[$i];
            $keyCantidad = "cantidadVP" . $arrayInputs[$i];
            $keyPrecio = "precioVP" . $arrayInputs[$i];
            $keySubTotal = "subTotalVP" . $arrayInputs[$i];
            $keyIsv = "isvProductoVP" . $arrayInputs[$i];//valor
            $keyTotal = "totalVP" . $arrayInputs[$i];
            $keyRestaInventario = "restaInventarioVP" . $arrayInputs[$i];
            $keyunidad = 'idUnidadVentaVP' . $arrayInputs[$i];
            
       
      

            array_push($arrayProductosVale,[
                'vale_id'=> $vale->id,
                'producto_id'=>$request->$keyIdProducto,
                'index' =>  $arrayInputs[$i],
                'cantidad'=>$request->$keyCantidad,
                'cantidad_pendiente'=>$request->$keyCantidad,
                'precio'=>$request->$keyPrecio,
                'unidad_medida_venta_id'=>$request->$keyunidad,
                'sub_total'=>$request->$keySubTotal,
                'isv'=>$request->$keyIsv,
                'total'=>$request->$keyTotal,
                'resta_inventario_total'=>$request->$keyRestaInventario,
                'created_at'=>now(),
                'updated_at'=>now()

            ]);
        };

        ModelEsperaProducto::insert($arrayProductosVale);


       return  $vale->id;

    }

}
