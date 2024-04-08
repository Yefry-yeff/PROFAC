<?php

namespace App\Http\Livewire\Cotizaciones;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class FacturarCotizacionCorporativoSrp extends Component
{
    public $idCotizacion;

    public function mount($id)
    {

        $this->idCotizacion = $id;
    }
    public function render()
    {

        $idCotizacion = $this->idCotizacion;
        $char = '"';
        $char2 = "'";

        $cotizacion = DB::SELECTONE('
        select
        A.id,
        A.nombre_cliente,
        A.RTN,
        A.fecha_emision,
        A.fecha_vencimiento,
        A.sub_total,
        A.sub_total_grabado,
        A.sub_total_excento,
        A.isv,
        A.total,
        A.cliente_id,
        A.tipo_venta_id,
        A.users_id,
        A.numeroInputs,
        A.porc_descuento,
        A.monto_descuento,
        A.created_at,
        A.updated_at,
        B.dias_credito,
        REPLACE(A.arregloIdInputs,' . $char2 . $char . $char2 . ',' . $char2 . $char . $char2 . ')  as "arregloIdInputs"
        from cotizacion A
        inner join cliente B
        on A.cliente_id = B.id
        where A.id =' . $idCotizacion);




        $htmlProductos =  $this->generarHTML($idCotizacion);

        $urlGuardarVenta = $this->obtenerURL($cotizacion->tipo_venta_id);

 
        return view('livewire.cotizaciones.facturar-cotizacion-corporativo-srp', compact('cotizacion', 'htmlProductos', 'urlGuardarVenta'));
    }

    public function generarHTML($idCotizacion)
    {

        $html = '';
        $htmlSelectUnidadVenta = '';
        $j = 0;

        $productos = DB::SELECT("
        select
        A.cotizacion_id,
        A.producto_id,
        A.nombre_producto,
        A.nombre_bodega,
        A.precio_unidad as precio_unidad,
        A.cantidad,
        A.sub_total,
        A.isv,
        A.total,
        A.bodega_id,
        A.seccion_id,
        A.resta_inventario,
        A.isv_producto,
        A.unidad_medida_venta_id,
        B.ultimo_costo_compra,
        B.precio_base as precio_base,
        B.isv as isvTblProducto,
        C.arregloIdInputs,
        A.monto_descProducto
        from cotizacion_has_producto A
        inner join producto B
        on A.producto_id = B.id
        inner join cotizacion C
        on A.cotizacion_id = C.id
        where cotizacion_id =  " . $idCotizacion . "
        order by A.indice asc
        ");

        $arregloInputs = $productos[0]->arregloIdInputs;
        $arregloInputs = str_replace('"', '', $arregloInputs);
        $arregloInputs = explode(",", $arregloInputs);




        foreach ($productos as $producto) {


            $unidadesVenta = DB::SELECT(
                "
                select
                A.unidad_venta as unidades,
                A.id as idUnidadVenta,
                concat(B.nombre,'-',A.unidad_venta ) as nombre
                from unidad_medida_venta A
                inner join unidad_medida B
                on A.unidad_medida_id = B.id
                where A.producto_id = " . $producto->producto_id
            );

            foreach ($unidadesVenta as $unidad) {

                if ($producto->unidad_medida_venta_id == $unidad->idUnidadVenta) {
                    $htmlSelectUnidadVenta = $htmlSelectUnidadVenta . '<option selected value="' . $unidad->unidades . '" data-id="' . $unidad->idUnidadVenta . '">' . $unidad->nombre . '</option>';
                } else {
                    $htmlSelectUnidadVenta = $htmlSelectUnidadVenta . '<option  value="' . $unidad->unidades . '" data-id="' . $unidad->idUnidadVenta . '">' . $unidad->nombre . '</option>';
                }
            }


            $i = $arregloInputs[$j];

            $html = $html .
                '<div id="' . $i . '" class="row no-gutters">
                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                        <div class="d-flex">

                            <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(' . $i . ')"><i
                                    class="fa-regular fa-rectangle-xmark"></i>
                            </button>

                            <input id="idProducto' . $i . '" name="idProducto' . $i . '" type="hidden" value="' . $producto->producto_id . '">

                            <div style="width:100%">
                                <label for="nombre' . $i . '" class="sr-only">Nombre del producto</label>
                                <input type="text" placeholder="Nombre del producto" id="nombre' . $i . '"
                                    name="nombre' . $i . '" class="form-control"
                                    data-parsley-required "
                                    autocomplete="off"
                                    readonly
                                    value="' . $producto->nombre_producto . '"

                                    >
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="" class="sr-only">cantidad</label>
                        <input type="text" value="' . $producto->nombre_bodega . '" placeholder="bodega-seccion" id="bodega' . $i . '"
                            name="bodega' . $i . '" class="form-control"
                            autocomplete="off"  readonly  >
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="precio' . $i . '" class="sr-only">Precio</label>
                        <input value="' . $producto->precio_base . '" type="number" placeholder="Precio Unidad" id="precio' . $i . '"
                            name="precio' . $i . '" class="form-control"  data-parsley-required step="any"
                            autocomplete="off" min="' . $producto->ultimo_costo_compra . '" onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')">
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="cantidad' . $i . '" class="sr-only">cantidad</label>
                        <input value="' . $producto->cantidad . '" type="number" placeholder="Cantidad" id="cantidad' . $i . '"
                            name="cantidad' . $i . '" class="form-control" min="0" data-parsley-required
                            autocomplete="off" onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')">
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label for="" class="sr-only">unidad</label>
                        <select class="form-control" name="unidad' . $i . '" id="unidad' . $i . '"
                            data-parsley-required style="height:35.7px;"
                            onchange="calcularTotales(precio' . $i . ',cantidad' . $i . ',' . $producto->isvTblProducto . ',unidad' . $i . ',' . $i . ',restaInventario' . $i . ')">
                                    ' . $htmlSelectUnidadVenta . '
                        </select>


                    </div>


                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                    <label for="subTotalMostrar' . $i . '" class="sr-only">Sub Total</label>
                    <input type="text" placeholder="Sub total producto" id="subTotalMostrar' . $i . '"
                        value="' . $producto->sub_total . '"
                        name="subTotalMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >

                    <input id="subTotal' . $i . '" name="subTotal' . $i . '" type="hidden" value="' . $producto->sub_total . '" required>
                </div>



                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                    <label for="isvProductoMostrar' . $i . '" class="sr-only">ISV</label>
                    <input type="text" value="' . $producto->isv . '" placeholder="ISV" id="isvProductoMostrar' . $i . '"
                        name="isvProductoMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >

                        <input id="isvProducto' . $i . '" name="isvProducto' . $i . '" type="hidden" value="' . $producto->isv . '" required>
                        <input type="hidden" id="acumuladoDescuento'.$i.'" name="acumuladoDescuento'.$i.'" value="' . $producto->monto_descProducto . '" >
                        </div>


                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                    <label for="totalMostrar' . $i . '" class="sr-only">Total</label>
                    <input type="text"  value="' . $producto->total . '" placeholder="Total del producto" id="totalMostrar' . $i . '"
                        name="totalMostrar' . $i . '" class="form-control"
                        autocomplete="off"
                        readonly >

                        <input id="total' . $i . '" name="total' . $i . '" type="hidden"  value="' . $producto->total . '" required>


                </div>

                    <input id="idBodega' . $i . '" name="idBodega' . $i . '" type="hidden" value="' . $producto->bodega_id . '">
                    <input id="idSeccion' . $i . '" name="idSeccion' . $i . '" type="hidden" value="' . $producto->seccion_id . '">
                    <input id="restaInventario' . $i . '" name="restaInventario' . $i . '" type="hidden" value="' . $producto->resta_inventario . '">
                    <input id="isv' . $i . '" name="isv' . $i . '" type="hidden" value="' . $producto->isvTblProducto . '">

                    </div>';
            $htmlSelectUnidadVenta = '';
            $j++;
        }

        return  $html;
    }

    public function obtenerURL($tipoVenta)
    {
        $url = '';

        switch ($tipoVenta) {
            case 1:
                $url = '/ventas/corporativo/guardar';
                break;
            case 2:
                $url = '/ventas/estatal/guardar';
                break;
            case 3:
                $url = '/exonerado/venta/guardar';
                break;
        }

        return  $url;
    }
}
