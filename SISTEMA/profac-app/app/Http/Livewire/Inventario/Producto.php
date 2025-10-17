<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;


use App\Models\ModelUnidadMedida;
use App\Models\ModelCategoriaProducto;
use App\Models\ModelProducto;
use App\Models\ModelPrecio;
use App\Models\ModelImagenProducto;
use App\Models\ModelUnidadMedidaVenta;
use App\Models\ModelMarca;


use DataTables;
use Illuminate\Support\Facades\File;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;

class Producto extends Component
{
    public function render()
    {
        $categorias = ModelCategoriaProducto::all();
        $unidades = DB::SELECT("select id,nombre,simbolo from unidad_medida order by nombre asc");
       $marcas = DB::SELECT("select id,nombre from marca order by nombre asc");
        return view('livewire.inventario.producto',  compact("categorias", "unidades","marcas"));
    }

    public function crearProducto(Request $request)
    {
          //dd($request->all());
        $validator = Validator::make($request->all(), [
            'nombre_producto' => 'required',
            'descripcion_producto' => 'required',
            'isv_producto' => 'required',

            'sub_categoria_producto' => 'required',
            'unidad_producto' => 'required',
            'ultimo_costo_compra'=>'required',


        ], [
            'nombre_producto' => 'Nombre es requerido',
            'descripcion_producto' => 'Descripcion es requerido',
            'isv_producto' => 'ISV es requqerido',

            'sub_categoria_producto' => 'Categoria del producto es requerido',
            'unidad_producto' => 'La unidad de medida es requerida',
            'ultimo_costo_compra'=>'el ultimo costo de compra es requerido'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Ha ocurrido un error.',
                'errors' => $validator->errors()
            ], 402);
        }




        try {
            $codBarra = trim($request['cod_barra_producto']);

            if($codBarra != '' or !empty($codBarra)){
                $contadorCodBarra = DB::SELECTONE("select count(id) as contador from  producto where codigo_barra =".trim($request['cod_barra_producto']));
                if($contadorCodBarra->contador > 0){
                    return response()->json([
                        'icon'=>'error',
                        'title'=>'Error!',
                        'text' => 'El código de barra ya está registrado para otro producto.',
                        'errors' => $validator->errors()
                    ], 402);
                }
            }






            DB::beginTransaction();
            $url = "";

            $producto = new ModelProducto;
            $producto->nombre = trim($request['nombre_producto']);
            $producto->descripcion = trim($request['descripcion_producto']);
            $producto->isv = $request['isv_producto'];
            $producto->codigo_barra =  $codBarra;
            $producto->costo_promedio = trim($request['costo_promedio']);
            $producto->codigo_estatal = trim($request['cod_estatal_producto']);
            $producto->sub_categoria_id = $request['sub_categoria_producto'];
            $producto->precio_base = trim($request['precioBase']);
            $producto->ultimo_costo_compra = trim($request->ultimo_costo_compra);
            $producto->marca_id = $request->marca_producto;
            $producto->users_id = Auth::user()->id;
            $producto->estado_producto_id = 1;
            $producto->unidad_medida_compra_id = $request->unidad_producto;
            $producto->unidadad_compra = $request->unidades;
            $producto->precio1 = $request->precio1;
            $producto->precio2 = $request->precio2;
            $producto->precio3 = $request->precio3;
            $producto->precio4 = $request->precio4;
            $producto->save();

            //------------------------guardar precios------------//
            // $arrayPrecios = $request['precio'];




            //     for ($i = 0; $i < count($arrayPrecios); $i++) {
            //         if($arrayPrecios[$i] == null){
            //             continue;
            //         }
            //         $precio = new ModelPrecio;
            //         $precio->precio = trim($arrayPrecios[$i]);
            //         $precio->producto_id = $producto->id;
            //         $precio->users_id = Auth::user()->id;
            //         $precio->save();
            //     }

            //---------------------------guardar unidades de medida para venta de producto-------

            $unidadVenta = new ModelUnidadMedidaVenta;
            $unidadVenta->unidad_venta = $request->unidades_venta;
            $unidadVenta->unidad_medida_id = $request->unidad_producto_venta;
            $unidadVenta->producto_id = $producto->id;
            $unidadVenta->estado_id = 1;
            $unidadVenta->unidad_venta_defecto = 1;
            $unidadVenta->save();


            if($request->unidad_producto != $request->unidad_producto_venta){

                $unidadVenta2 = new ModelUnidadMedidaVenta;
                $unidadVenta2->unidad_venta = $request->unidades;
                $unidadVenta2->unidad_medida_id = $request->unidad_producto;
                $unidadVenta2->producto_id = $producto->id;
                $unidadVenta2->estado_id = 1;
                $unidadVenta2->unidad_venta_defecto = 0;
                $unidadVenta2->save();

            }








            //----------------------------------guardar imagen-------------------------//


             if ($request->file('files') <> null) {

                $URLs = [];
                $archivos = $request->file('files');
                $i = 1;

                foreach ($archivos as $file) {

                        $name = 'IMG_'. time()."-".$i. '.' . $file->getClientOriginalExtension();
                        $path = public_path() . '/catalogo';
                        $url =  $name;
                        array_push($URLs, ['producto_id' => $producto->id, 'url_img' =>  $url, 'users_id' =>  Auth::user()->id, 'created_at' => now()]);
                        $file->move($path, $name);
                        $i++;
                }
                //  $flight = URLfile::create($URLs);

                DB::table('img_producto')->insert($URLs);
            }


            DB::commit();
            return response()->json([
                "message" => "producto guardato con exito",
            ], 200);
        } catch (QueryException $e) {
            DB::rollback();

            // $carpetaPublic = public_path();
            // $path = $carpetaPublic.'/catalogo/'.$name;

            // File::delete($path);

            return response()->json([
                'message' => 'Ha ocurrido un error al crear el producto.',
                'errorTh' => $e,
            ], 402);
        }
    }


    public function listarProductos()
    {
        try {
            $listaProductos = DB::SELECT("

            select
            A.id as 'codigo',
            A.nombre,
            A.descripcion,
            A.isv as 'ISV',
            C.descripcion as 'categoria',
            @existenciaCompra := IFNULL ((select
            sum(cantidad_disponible)
            from recibido_bodega
            inner join compra
            on recibido_bodega.compra_id = compra.id
            where compra.estado_compra_id=1 and  producto_id = A.id), 0)  as 'existenciaCompra',
           @existenciaAjuste := IFNULL (
           (
            select
            sum(cantidad_disponible)
            from recibido_bodega  G
            where G.compra_id is null and G.cantidad_disponible <> 0 and G.producto_id = A.id),0 ) as 'existenciaAjuste',
            FORMAT(@existenciaCompra + @existenciaAjuste,0) as existencia,
            A.codigo_barra
            from producto A
            inner join sub_categoria B on A.sub_categoria_id = B.id
            inner join categoria_producto C on C.id = B.categoria_producto_id
            WHERE A.id not in (

                1157,
                1321,
                2665,
                2585,
                2409,
                2464,
                1569,
                1506,
                2708,
                2937,
                2645,
                1118,
                2652,
                3355,
                3356,
                3358,
                3359,
                3360,
                3361,
                3362,
                1259,
                1231,
                2452,
                3386,
                3387,
                3084,
                3391,
                3390,
                3077,
                3375,
                3378,
                3384,
                3383,
                3381,
                3382,
                2948,
                3554,
                2714,
                2021,
                2026,
                2469,
                2025,
                2470,
                2024,
                2471,
                2022,
                2473,
                2921,
                2023,
                2472,
                2597,
                2277,
                2252,
                2544,
                3389,
                3388,
                3385,
                3357,
                2417,
                3887,
                3888
                        )
            order by A.created_at DESC
                        ");

            return Datatables::of($listaProductos)
                ->addColumn('disponibilidad', function ($listaProductos) {

                    return

                        '
                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                    <li><a class="dropdown-item" href="/producto/detalle/' . $listaProductos->codigo . '" target="_blank"  > <i class="fa-solid fa-arrows-to-eye text-info"></i>
                            Ver detalles </a></li>

                </ul>
            </div>
                ';
                })

                ->rawColumns(['disponibilidad'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar los productos.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function guardarFoto(Request $request){

        //dd($request->all());
        $url = '';
        try{
                if ($request->file('files') <> null) {

                    $URLs = [];
                    $archivos = $request->file('files');
                    $i = 0;

                    foreach ($archivos as $file) {

                            $name = 'IMG_'. time()."-".$i. '.' . $file->getClientOriginalExtension();
                            $path = public_path() . '/catalogo';
                            $url =  $name;
                            array_push($URLs, ['producto_id' => $request->id_producto_edit_foto, 'url_img' =>  $url, 'users_id' =>  Auth::user()->id, 'created_at' => now()]);
                            $file->move($path, $name);
                            $i++;
                    }
                    //  $flight = URLfile::create($URLs);

                    DB::table('img_producto')->insert($URLs);
                }


                DB::commit();
                return response()->json([
                    "message" => "producto guardato con exito",
                ], 200);
            } catch (QueryException $e) {
                DB::rollback();

                return response()->json([
                    'message' => 'Ha ocurrido un error al crear el producto.',
                    'errorTh' => $e,
                ], 402);
            }
    }

    public function listarModalProductoEdit($id){

        try {

            $datosProducto = DB::SELECT("
            select
                id,
                nombre,
                descripcion,
                isv,
                precio_base,
                precio1,
                precio2,
                precio3,
                precio4,
                codigo_barra,
                codigo_estatal,
                sub_categoria_id,
                unidad_medida_compra_id,
                users_id,
                costo_promedio,
                marca_id,
                unidad_medida_compra_id,
                unidadad_compra,
                ultimo_costo_compra


            from producto where id =".$id);

            $preciosProducto = DB::SELECT("

            select
                id,
                precio,
                producto_id,
                users_id
            from precios_venta
            where producto_id = ".$id

            );

            $subCategoria = DB::selectOne("SELECT B.id, B.descripcion FROM producto as A
            INNER JOIN sub_categoria B ON (A.sub_categoria_id = B.id)
            where A.id =". $id);

            $categoria = DB::SELECTONE("SELECT C.id, C.descripcion FROM producto as A
            INNER JOIN sub_categoria B ON (A.sub_categoria_id = B.id)
            inner join categoria_producto C ON (B.categoria_producto_id = C.id )
            where A.id = " .$id);


            $categorias = DB::SELECT("select id, descripcion from categoria_producto");
            $subCategorias = DB::SELECT("select id, descripcion from sub_categoria where categoria_producto_id =".$categoria->id);

            $unidades = DB::SELECT("select id,nombre from unidad_medida order by nombre asc");
            $marcas =  DB::SELECT("select id,nombre from marca order by nombre asc");


            return response()->json([
            "datosProducto"=> $datosProducto[0],
            "preciosProducto" => $preciosProducto,
            "categorias"=>$categorias,
            "unidades" => $unidades,
            "marcas" => $marcas,
            "subCategoria"=>$subCategoria,
            "categoria"=>$categoria,
            "subCategorias"=>$subCategorias,


            ],200);

        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error al traer datos de producto.",
                "error" => $e
            ],402);

        }
    }

    public function editarProducto(Request $request){
        try {

            $codBarra = trim($request['cod_barra_producto_edit']);

            if($codBarra != '' or !empty($codBarra)){
                $contadorCodBarra = DB::SELECTONE("select count(id) as contador from  producto where codigo_barra =".trim($request['cod_barra_producto_edit'])." and id <> ".$request['id_producto_edit']);
                if($contadorCodBarra->contador > 0){
                    return response()->json([
                        'icon'=>'error',
                        'title'=>'Error!',
                        'text' => 'El código de barra ya está registrado para otro producto.'
                    ], 401);
                }
            }




            $producto = ModelProducto::find($request['id_producto_edit']);
            $producto->nombre = trim($request['nombre_producto_edit']);
            $producto->descripcion = trim($request['descripcion_producto_edit']);
            $producto->isv = trim($request['isv_producto_edit']);
            $producto->codigo_barra = $codBarra;
            $producto->codigo_estatal = trim($request['cod_estatal_producto_edit']);
            $producto->sub_categoria_id = $request['sub_categoria_producto_edit'];
            $producto->precio_base = trim($request['precioBase_edit']);
            $producto->costo_promedio = $request['costo_promedio_editar'];
            $producto->unidadad_compra = trim($request['unidades_editar']);
            $producto->ultimo_costo_compra = trim($request->ultimo_costo_compra_editar);
            $producto->unidad_medida_compra_id = $request['unidad_producto_editar'];
            $producto->precio1 = $request['precio1'];
            $producto->precio2 = $request['precio2'];
            $producto->precio3 = $request['precio3'];
            $producto->precio4 = $request['precio4'];
            $producto->marca_id= $request->marca_producto_editar;
            $producto->users_id = Auth::user()->id;
            $producto->save();


            return response()->json([
                "message" => "producto editado con exito",
            ], 200);
        } catch (QueryException $e) {


                return response()->json([
                    'message' => 'Ha ocurrido un error al editar el producto.',
                    'errorTh' => $e,
                ], 402);
        }
    }

    public function eliminarImagen(Request $request){



                //dd($request->urlImagen);
                try{
                    $user = ModelImagenProducto::where('url_img','=',$request->urlImagen);
                    $user->delete();

                    $carpetaPublic = public_path();
                    $path = $carpetaPublic.'/catalogo/'.$request->urlImagen;

                    File::delete($path);

                return 'exito';
                } catch (QueryException $e) {
                    return response()->json([
                        'message' => 'Ha ocurrido un error al eliminar la imagen.',
                        'errorTh' => $e,
                    ], 402);
                }
    }

    public function calcularCostos(Request $request){

        $primeraCompra = DB::SELECTONE("
        select
        B.precio_unidad + (B.precio_unidad*C.isv/100) as costo

        from compra A
        inner join compra_has_producto B
        on A.id = B.compra_id
        inner join producto C
        on C.id = B.producto_id
        where YEAR(A.fecha_emision)=YEAR(NOW()) and B.producto_id = ".$request->idProducto."
        order by A.fecha_emision ASC LIMIT 1
        ");

        $ultimaCompra = DB::SELECTONE("
        select
        B.precio_unidad + (B.precio_unidad*C.isv/100) as costo

        from compra A
        inner join compra_has_producto B
        on A.id = B.compra_id
        inner join producto C
        on C.id = B.producto_id
        where YEAR(A.fecha_emision)=YEAR(NOW()) and B.producto_id = ".$request->idProducto."
        order by A.fecha_emision DESC LIMIT 1
        ");
      //  DD($primeraCompra->costo,$ultimaCompra->costo);


        if(!empty($primeraCompra) and !empty($ultimaCompra)){
            $ultimaCompra =  $ultimaCompra->costo;
            $costoPromedio = round(($primeraCompra->costo + $ultimaCompra)/2,2);

            $producto = ModelProducto::find($request->idProducto);
            $producto->ultimo_costo_compra =  $ultimaCompra;
            $producto->costo_promedio =  $costoPromedio;
            $producto->precio_base=$ultimaCompra;
            $producto->save();

        }else{
            $ultimaCompra=0;
            $costoPromedio=0;

        }




        return response()->json([
            "costoPromedio"=>$costoPromedio,
            "ultimoCosto"=>$ultimaCompra,
        ],200);





    }

    public function export(){
        try {

            return Excel::download(new ProductosExport, 'DatosProductos.xlsx');

        } catch (QueryException $e) {
            return response()->json([

                'error' => $e,
                "text" => "Ha ocurrido un error.",
                "icon" => "error",
                "title"=>"Error!"
            ],402);
        }

    }


    public function listarSubcategorias($id){
        try {

            $sub_categorias = DB::table('sub_categoria')
            //->join('categoria_producto', 'sub_categoria.categoria_producto_id', '=', 'categoria_producto.id')
            ->select('sub_categoria.id', 'sub_categoria.descripcion')
            ->where('sub_categoria.categoria_producto_id', '=', $id)
            ->get();

        return response()->json([
         "sub_categorias"=>$sub_categorias,
        ],200);
        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
    }



}
