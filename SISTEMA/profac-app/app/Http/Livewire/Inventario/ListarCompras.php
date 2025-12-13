<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComprasMesExport;

class ListarCompras extends Component
{
    public function render()
    {
        return view('livewire.inventario.listar-compras');
    }

    public function listarCompras(){

        try {

            $listarCompras = DB::SELECT("
            select
                compra.id as id,
                numero_orden,
                numero_factura,
                fecha_emision,
                fecha_vencimiento,
                total,
                IF(monto_retencion = 0,'SIN RETENCION','CON RETENCION') as 'estado_retencion',
                proveedores.nombre,
                users.name as usuario,
                compra.created_at as fecha_registro,
                (select count(compra_id) as contador from recibido_bodega where compra_id = compra.id  ) as 'conteo_bodega'
            from compra
            inner join users
            on compra.users_id = users.id
            inner join proveedores
            on compra.proveedores_id = proveedores.id
            where compra.estado_compra_id = 1
            order by compra.id DESC 
            ");

            return Datatables::of($listarCompras)
            ->addColumn('opciones', function ($listarCompras) {

                return

                '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                        <li>
                            <a class="dropdown-item" href="/producto/compras/detalle/'.$listarCompras->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de compra </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/producto/compra/recibir/'.$listarCompras->id.'" > <i class="fa-solid fa-cart-arrow-down text-warning"></i> Recepción de producto </a>
                        </li> 
                        <li>
                            <a class="dropdown-item" href="/producto/compra/pagos/'.$listarCompras->id.'"> <i class="fa-solid fa-cash-register text-success"></i> Pagos </a>
                        </li>


                        <li>
                            <a class="dropdown-item" href="/inventario/compras/incidencias/'.$listarCompras->id.'" > <i class="fa-solid fa-triangle-exclamation text-warning"></i> Lista de Incidencias </a>
                        </li>                        

                        
                    </ul>
                </div>';
            })
            ->addColumn('anular', function ($listarCompras) {

                if($listarCompras->conteo_bodega > 0){

                    return

                    '<div class="text-center">
                        <button disabled  class="btn btn-danger " onclick="anularCompra('.$listarCompras->id.')">
                            Anular compra
                        </button>
                        
                    </div>';

                }else{
                    return

                    '<div class="text-center">
                    <button  class="btn btn-danger" onclick="anularCompra('.$listarCompras->id.')">
                        Anular compra
                    </button>
                    
                </div>';

                }


            })
            ->rawColumns(['opciones','anular'])
            ->make(true);
           
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las compras.',
                'errorTh' => $e,
            ], 402);
           
        }

    }

    public function export($month){
        try {

            $arrayMes = explode('-', $month);
            $mes= $arrayMes[1];
            
            return Excel::download(new ComprasMesExport($mes), 'DatosComprasMes.xlsx');

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
