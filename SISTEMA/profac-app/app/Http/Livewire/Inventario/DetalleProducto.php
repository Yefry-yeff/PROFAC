<?php

namespace App\Http\Livewire\Inventario;


use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ModelUnidadMedida;
use App\Models\ModelCategoriaProducto;
use App\Models\ModelMarca;
use App\Models\ModelUnidadMedidaVenta;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use DataTables;
use Auth;


class DetalleProducto extends Component
{

    public $idProducto;
    public function mount($id)
    {
        $this->idProducto = $id;
    }

    public function render()
    {

        $id = $this->idProducto;

        $producto = DB::SELECTONE("
        select
            A.id,
            A.nombre,
            A.descripcion,
            A.codigo_estatal,
            A.codigo_barra,
            B.descripcion as 'sub_categoria',
            E.descripcion as 'categoria',
            A.precio_base,
            A.precio1,
            A.precio2,
            A.precio3,
            A.precio4,
            A.costo_promedio,
            A.ultimo_costo_compra,
            A.isv,
            C.nombre as 'unidad_medida',
            A.created_at as 'fecha_registro',
            D.name as 'registrado_por',
            A.marca_id,
            A.sub_categoria_id,
            unidad_medida_compra_id,
            unidadad_compra,
            F.nombre as 'marca',
            A.tiempo_recuperacion_meses,
            A.origen

        from  producto A
        inner join sub_categoria B
        on A.sub_categoria_id = B.id
        inner join categoria_producto E
        on E.id = B.categoria_producto_id
        inner join unidad_medida C
        on A.unidad_medida_compra_id = C.id
        inner join users D
        on A.users_id = D.id
        left join marca F
        on A.marca_id = F.id
        where A.id = " . $id . "
        ");



        $precios = DB::SELECT("

        select
            @i := @i + 1 as contador,
            A.precio,
            B.name

        from precios_venta A
        inner join users B
        on A.users_id = B.id
        cross join (select @i := 0) r
        where A.producto_id = " . $id . "
        order by A.id ASC

        ");

        $imagenes = DB::SELECT("
      select
        @i := @i + 1 as contador,
        A.url_img,
        A.id
      from
        img_producto A
        cross join (select @i := 0) r
        where producto_id =  " . $id . "

        ");

        //dd($imagenes);

        $lotes = DB::SELECT("
        select
        *,
        @i := @i + 1 as contador
        from  (
                select
                    B.id,
                    B.nombre,
                    G.nombre as 'departamento',
                    F.nombre as 'municipio',
                    E.nombre as 'bodega',
                    E.direccion,
                    D.descripcion as 'seccion',
                    C.numeracion,
                    H.cantidad_disponible,
                    H.created_at,
                    H.id as idRecibido,
                    H.seccion_id
                from compra_has_producto A
                inner join producto B
                on A.producto_id = B.id
                inner join recibido_bodega H
                on A.compra_id = H.compra_id and A.producto_id = H.producto_id
                inner join seccion C
                on H.seccion_id = C.id
                inner join segmento D
                on C.segmento_id = D.id
                inner join bodega E
                on D.bodega_id = E.id
                inner join municipio F
                on E.municipio_id = F.id
                inner join departamento G
                on F.departamento_id = G.id
                inner join compra
                on A.compra_id = compra.id
                where B.id = ".$id."  and H.cantidad_disponible <> 0 and H.estado_recibido = 4 and compra.estado_compra_id =1

                union

                select
                    B.id,
                    B.nombre,
                    G.nombre as 'departamento',
                    F.nombre as 'municipio',
                    E.nombre as 'bodega',
                    E.direccion,
                    D.descripcion as 'seccion',
                    C.numeracion,
                    H.cantidad_disponible,
                    H.created_at,
                    H.id as idRecibido,
                    H.seccion_id
                from  producto B
                inner join recibido_bodega H
                on  B.id = H.producto_id
                inner join seccion C
                on H.seccion_id = C.id
                inner join segmento D
                on C.segmento_id = D.id
                inner join bodega E
                on D.bodega_id = E.id
                inner join municipio F
                on E.municipio_id = F.id
                inner join departamento G
                on F.departamento_id = G.id
                where B.id = ".$id." and H.cantidad_disponible <> 0 and H.estado_recibido = 4
        ) listado
        cross join (select @i := 0) r
        order by idRecibido ASC
        ");


        $categorias = ModelCategoriaProducto::all();
        $unidades = ModelUnidadMedida::all();
        $marcas = ModelMarca::all();

        // Vigencia de reserva: fecha de creación de prefactura + días configurados.
        $diasValidezReserva = (int) (DB::table('configuracion_prefactura')
            ->orderByDesc('id')
            ->value('dias_validez') ?? 7);
        $diasValidezReserva = max(0, $diasValidezReserva);

        // ── Reservas por sección (FIFO) ──────────────────────────────────
        // Total reservado por seccion_id para este producto
        $reservasPorSeccion = DB::table('prefactura_has_producto as php')
            ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
            ->where('pf.estado', 'activo')
            ->whereRaw(
                "TIMESTAMPADD(DAY, ?, COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()",
                [$diasValidezReserva]
            )
            ->where('php.producto_id', $id)
            ->where('php.resta_inventario', 1)
            ->selectRaw('php.seccion_id, SUM(php.cantidad) as total')
            ->groupBy('php.seccion_id')
            ->pluck('total', 'seccion_id')
            ->map(fn($v) => (float) $v)
            ->toArray();

        // Detalle de prefacturas reservadas por seccion_id (para modal)
        $detalleReservasRaw = DB::table('prefactura_has_producto as php')
            ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
            ->where('pf.estado', 'activo')
            ->whereRaw(
                "TIMESTAMPADD(DAY, ?, COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()",
                [$diasValidezReserva]
            )
            ->where('php.producto_id', $id)
            ->where('php.resta_inventario', 1)
            ->select('php.seccion_id', 'pf.id as prefactura_id', 'pf.flujo_id',
                     'pf.nombre_cliente', 'php.cantidad', 'pf.fecha_emision', 'pf.fecha_vencimiento')
            ->get()
            ->groupBy('seccion_id')
            ->map(fn($rows) => $rows->map(fn($r) => (array) $r)->values()->toArray())
            ->toArray();

        // Aplicar FIFO: distribuir la reserva desde el lote más antiguo
        $remainingBySeccion = $reservasPorSeccion;
        foreach ($lotes as $lote) {
            $sid      = $lote->seccion_id ?? null;
            $rawStock = (float) $lote->cantidad_disponible;
            $remaining   = $sid !== null ? ($remainingBySeccion[$sid] ?? 0.0) : 0.0;
            $reservaFila = min($remaining, $rawStock);
            if ($sid !== null) {
                $remainingBySeccion[$sid] = max(0.0, $remaining - $reservaFila);
            }
            $lote->rawStock            = $rawStock;
            $lote->reservado_fila      = $reservaFila;
            $lote->disponible_fila     = max(0.0, $rawStock - $reservaFila);
            $lote->reservas_detalle    = $sid !== null ? ($detalleReservasRaw[$sid] ?? []) : [];
        }

        $esAdmin = Auth::user()->rol_id == 1;

        return view('livewire.inventario.detalle-producto',  compact('producto', 'precios', 'imagenes', 'lotes', 'categorias', 'unidades', 'marcas', 'esAdmin'));
    }

    public function unidadesVenta($id){
       try {



        $unidades = DB::SELECT("
        select
        @i := @i + 1 as contador,
        A.id,
        B.nombre,
        A.unidad_venta,
        A.unidad_medida_id
        from unidad_medida_venta A
        inner join unidad_medida B
        on A.unidad_medida_id = B.id
        CROSS JOIN (select @i := 0) r
        where A.producto_id =".$id
        );


        return Datatables::of($unidades)
        // ->addColumn('eliminar', function ($unidad) {
        //         return

        //         '<div class="text-center">  <button class="btn btn-danger  btn-dim" type="button"><i class="fa-solid fa-trash-can"></i></button></div>';

        // })
        ->addColumn('editar', function ($unidad) {

            $cadena = "";
            if(Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5'){
                $cadena =   '<div class="text-center">  <button onclick="modalEditarUnidades('.$unidad->id.','.$unidad->unidad_venta.','.$unidad->unidad_medida_id.')" class="btn btn-warning  btn-dim" type="button"><i class="fa-solid fa-pencil"></i></button></div>';
            }

            return  $cadena;



    })

        ->rawColumns(['editar'])
        ->make(true);



       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }

    public function obtenerUnidadesMedida(){
       try {

        $unidades = ModelUnidadMedida::all();


       return response()->json([
           'unidades'=>$unidades,
       ],200);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }
    }

    public function editarUnidadesVenta(Request $request){
       try {

        //dd($request->idUniadVenta);
        $unidad = ModelUnidadMedidaVenta::find($request->idUniadVenta);
        $unidad->unidad_venta = $request->unidades_venta_editar;
        $unidad->unidad_medida_id = $request->unidad_venta_editar;
        $unidad->save();



       return response()->json([
           "message" => "exito",
       ],200);
       } catch (QueryException $e) {
       return response()->json([
           'message' => 'Ha ocurrido un error',
           'error' => $e
       ],402);
       }

    }
}
