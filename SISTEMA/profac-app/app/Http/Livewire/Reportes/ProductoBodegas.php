<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelCotizacion;
use App\Models\ModelCotizacionProducto;


class ProductoBodegas extends Component
{
    public function render()
    {
        return view('livewire.reportes.producto-bodegas');
    }



    public function consultaProducto($selectBodega){
        try {



            $consulta = DB::SELECT("
            select
            A.id as 'codigo',
            A.nombre as 'producto',
            A.descripcion as 'descripcion',
            A.isv as 'ISV',
            B.descripcion as 'categoria',
            (select bodega.nombre from bodega where id = ".$selectBodega.") as 'bodega',
            @existenciaCompra := IFNULL ((select
            sum(cantidad_disponible)
            from recibido_bodega
            inner join compra on recibido_bodega.compra_id = compra.id
            inner join seccion on seccion.id = recibido_bodega.seccion_id
            inner join segmento on segmento.id = seccion.segmento_id
            inner join bodega on bodega.id = segmento.bodega_id
            where compra.estado_compra_id=1 and  producto_id = A.id and  bodega.id = ".$selectBodega."), 0)  as 'existenciaCompra',
           @existenciaAjuste := IFNULL (
           (
            select
            sum(cantidad_disponible)
            from recibido_bodega  G
            inner join seccion on seccion.id = G.seccion_id
            inner join segmento on segmento.id = seccion.segmento_id
            inner join bodega on bodega.id = segmento.bodega_id
            where G.compra_id is null and G.cantidad_disponible <> 0 and G.producto_id = A.id and bodega.id = ".$selectBodega."),0 ) as 'existenciaAjuste',
            FORMAT(@existenciaCompra + @existenciaAjuste,0) as 'existencia',
            A.codigo_barra
            from producto A
            inner join sub_categoria B on A.sub_categoria_id = B.id
            order by A.created_at DESC
                ");


            return Datatables::of($consulta)
            ->rawColumns([])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e,
            ], 402);

        }

    }

}
