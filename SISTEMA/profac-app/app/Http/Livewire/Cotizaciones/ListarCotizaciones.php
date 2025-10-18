<?php

namespace App\Http\Livewire\Cotizaciones;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;

class ListarCotizaciones extends Component


{
    public $tipoVenta=null;


    public function mount($id)
    {

        $this->tipoVenta = $id;
    }


    public function render()
    {

        $tipoVenta = $this->tipoVenta;
        $idTipoVenta = 0;

        switch($tipoVenta){
            case "corporativo":
                    $idTipoVenta = 1;
                    break;
            case "estatal":
                    $idTipoVenta = 2;
                    break;
            case "exonerado":
                    $idTipoVenta = 3;
                    break;

        }

        return view('livewire.cotizaciones.listar-cotizaciones',compact('idTipoVenta'));
    }

    public function listarCotizaciones(Request $request){
        if (Auth::user()->rol_id == '2') {
                $cotizaciones = DB::SELECT("
                select
                A.id,
                concat(YEAR(now()),'-',A.id)  as codigo,
                A.nombre_cliente,
                A.RTN,
                FORMAT(A.sub_total,2) as sub_total,
                FORMAT(A.isv,2) as isv,
                FORMAT(A.total,2) as total,
                B.name as cotizador,
                (select name from users where id = A.vendedor) as vendedor,
                A.created_at,
                A.tipo_venta_id
                from cotizacion A
                inner join users B
                on A.users_id = B.id
                where A.tipo_venta_id = ".$request->id."
                and A.vendedor =  ".Auth::user()->id."
                order by A.created_at desc
            ");
        }else{
                $cotizaciones = DB::SELECT("
                select
                A.id,
                concat(YEAR(now()),'-',A.id)  as codigo,
                A.nombre_cliente,
                A.RTN,
                FORMAT(A.sub_total,2) as sub_total,
                FORMAT(A.isv,2) as isv,
                FORMAT(A.total,2) as total,
                B.name as cotizador,
                (select name from users where id = A.vendedor) as vendedor,
                A.created_at,
                A.tipo_venta_id
                from cotizacion A
                inner join users B
                on A.users_id = B.id
                where A.tipo_venta_id = ".$request->id." AND A.id NOT IN (
                    24558,
                    24557,
                    24556,
                    24555,
                    24554,
                    24552,
                    24551,
                    24550,
                    24549,
                    24548,
                    24547,
                    24546,
                    24545,
                    24839,
                    24919,
                    24918,
                    24917,
                    24916,
                    24915,
                    24914,
                    24913,
                    24912,
                    24911,
                    24910,
                    24909,
                    24908,
                    24907,
                    24906,
                    24905,
                    24904,
                    24903,
                    24902,
                    24901,
                    24900,
                    24667,
                    24655,
                    24654,
                    24545,
                    24860,
                    24597,
                    25100,
                    25105,
                    25462,
                    24975,
                    24860,
                    24845,
                    24799,
                    24780,
                    24667,
                    24655,
                    24654,
                    24597,
                    25105,
                    25100,
                    25506,
                    25502,
                    25501,
                    25095,
                    24966,
                    25294,
                    24732,
                    24731,
                    24931,
                    24930,
                    24895,
                    24894,
                    24789,
                    24787,
                    24786,
                    24785,
                    25542,
                    25130,
                    25123,
                    25116,
                    25185,
                    25164,
                    25163,
                    25162,
                    25161,
                    25160,
                    25159,
                    25158,
                    25157,
                    25156,
                    25425,
                    25424,
                    25423,
                    24801,
                    26049
                )
                order by A.created_at desc
            ");

        }


        return Datatables::of($cotizaciones)
            ->addColumn('opciones', function ($cotizacion) {

                if($cotizacion->tipo_venta_id == 1){ //corporativo
                    return
                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                 <a class="dropdown-item" target="_blank"  href="/cotizacion/edicion/'.$cotizacion->id.'" > <i class="fa-solid fa-file-invoice text-info"></i> Editar </a>
                            </li>
                            <li>
                                <a class="dropdown-item" target="_blank"  href="/cotizacion/facturar/'.$cotizacion->id.'" > <i class="fa-solid fa-file-invoice text-info"></i> Facturar </a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/cotizacion/facturar/srp/corporativo/'.$cotizacion->id.'" > <i class="fa-solid fa-file-invoice text-info"></i> Facturar SR/P </a>
                            </li>



                            <li>
                                <a class="dropdown-item"  target="_blank" href="/cotizacion/imprimir/'.$cotizacion->id.'">  <i class="fa-solid fa-print text-success"></i> Imprimir Cotización </a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/proforma/imprimir/'.$cotizacion->id.'"> <i class="fa-solid fa-print text-success"></i> Imprimir Proforma </a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/cotizacion/imprimir/catalogo/'.$cotizacion->id.'"> <i class="fa-solid fa-print text-success"></i> Catálogo </a>
                            </li>



                        </ul>
                    </div>';
                }else{//estatal
                    return
                    '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                    <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                        <li>
                            <a class="dropdown-item" target="_blank"  href="/cotizacion/edicion/'.$cotizacion->id.'" > <i class="fa-solid fa-file-invoice text-info"></i> Editar </a>
                        </li>
                        <li>
                            <a class="dropdown-item" target="_blank"  href="/cotizacion/facturar/gobierno/'.$cotizacion->id.'" > <i class="fa-solid fa-file-invoice text-info"></i> Facturar </a>
                        </li>



                        <li>
                            <a class="dropdown-item"  target="_blank" href="/cotizacion/imprimir/'.$cotizacion->id.'">  <i class="fa-solid fa-print text-success"></i> Imprimir Cotización </a>
                        </li>

                        <li>
                        <a class="dropdown-item" target="_blank"  href="/proforma/imprimir/'.$cotizacion->id.'"> <i class="fa-solid fa-print text-success"></i> Imprimir Proforma </a>
                        </li>


                        <li>
                        <a class="dropdown-item" target="_blank"  href="/cotizacion/imprimir/catalogo/'.$cotizacion->id.'"> <i class="fa-solid fa-print text-success"></i> Catálogo </a>
                        </li>


                    </ul>
                </div>';
                }


            })

            ->rawColumns(['opciones'])
            ->make(true);
    }

}
