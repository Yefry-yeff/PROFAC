<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use App\Models\Retenciones as ModelRetenciones;
use App\Models\User;
use App\Models\Modelproveedores;
use App\Models\TipoPersonalidad;
use App\Models\Categoria; 
use App\Models\TipoRetencion;

use App\Models\RetencionesProveedores;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;


use DataTables;

class Retenciones extends Component{

    public function render()
    {
        $listaRetenciones = TipoRetencion::all();
        return view('livewire.inventario.retenciones',  compact("listaRetenciones"));
    }

    public function listarRetenciones(){
        try {

            $listaRetenciones = DB::SELECT("
            select
            A.id as 'codigo',
            A.nombre as 'descripcion',
            A.valor,
            B.nombre as 'tipo',
            C.name,
            A.updated_at as 'fecha'

            from retenciones A
            inner join tipo_retencion B
            on A.tipo_retencion_id = B.id
            inner join users C
            on A.users_id = C.id
                        
            ");

            return Datatables::of($listaRetenciones)
            ->addColumn('opciones', function ($listaRetenciones) {
                   
                    
                   

                        return

                        '
                    <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start"
                            style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                            <li><a class="dropdown-item" href="#" > <i class="fa fa-pencil m-r-5 text-warning"></i> Editar</a></li>        
                        </ul>
                    </div>
                        ';


                    
                    

            })
 
           
            ->rawColumns(['opciones'])         
            ->make(true);


           
            
          
        } catch (QueryException $e) {
            return response()->json([
                "message" => "Ha ocurrido un error al listar las retenciones",
                "error" => $e
            ],402);
        }
    }

    public function registrarRetencion(Request $request){
        try {

            $guardarRetencion = new ModelRetenciones();
            $guardarRetencion->nombre = $request['nombre_retencion'];
            $guardarRetencion->valor = $request['valor_retencion'];
            $guardarRetencion->tipo_retencion_id = $request['tipo_retencion']; 
            $guardarRetencion->users_id = Auth::user()->id;
            $guardarRetencion->save();



            return response()->json([
                "message"=>"Registro realizado con exito!",
                
            ],200);
            
        } catch (QueryException $e) {
            return response()->json([
                "message" => "Ha ocurrido un error al crear la retencion",
                "error" => $e
            ],402);            
           
        }
    }
}
