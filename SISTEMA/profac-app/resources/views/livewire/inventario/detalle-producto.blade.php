<div>
    @push('styles')
        <style>
            .img-width {
                width: 20rem;
                height: 8rem;
                margin: 0 auto;

            }

            @media (min-width: 350px) and (max-width: 768px) {

                .img-width {
                    width: 20rem;
                    height: 10rem;
                    margin: 0 auto;

                }

            }

            @media (min-width: 769px) {
                .img-width {
                    width: 45rem;
                    height: 27rem;
                    margin: 0 auto;

                }
            }
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2> Producto </h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Información detallada</a>
                </li>
                <li class="breadcrumb-item">
                    <a> {{ ucwords(strtolower($producto->nombre)) }}</a>
                </li>


            </ol>
        </div>

        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5' || Auth::user()->rol_id == '7')
            <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
                <div style="margin-top: 1.5rem" mr-auto>
                    <a href="#" class="btn add-btn btn-warning" data-toggle="modal"
                        data-target="#modal_producto_editar"><i class="fa fa-plus"></i>Editar Producto</a>
                </div>
            </div>

            <div style="margin-top: 1.5rem; margin-left:auto; ">
                <a href="#" class="btn add-btn btn-info" data-toggle="modal" data-target="#modal_foto_producto"><i
                        class="fa fa-plus"></i>Subir Fotografía</a>
            </div>
        @endif




    </div>





    <div class="row mt-6 wrapper white-bg animated fadeInRight  ">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3>Fotografías</h3>

                </div>
                <div class="ibox-content">

                    <div id="carouselExampleBigIndicators" class="carousel slide" data-ride="carousel">
                        <ol class="carousel-indicators">

                            @for ($i = 0; $i < count($imagenes); $i++)
                                @if ($i == 0)
                                    <li data-target="#carouselExampleBigIndicators" data-slide-to="{{ $i }}"
                                        class="active">
                                    </li>
                                @else
                                    <li data-target="#carouselExampleBigIndicators" data-slide-to="{{ $i }}"
                                        class=""></li>
                                @endif
                            @endfor

                        </ol>
                        <div class="carousel-inner ">
                            @php
                                $comillas = '"';
                            @endphp


                            @foreach ($imagenes as $imagen)
                                @if ($imagen->contador == 1)
                                    <div class="carousel-item active row w-100 align-items-center">

                                        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5' || Auth::user()->rol_id == '7')
                                            <div class="col text-center">
                                                <button class="btn btn-danger regular-button "
                                                    onclick="eliminar({{ $comillas . $imagen->url_img . $comillas }})"
                                                    type="button">Eliminar imagen</button>
                                            </div>
                                            <br>
                                        @endif


                                        <img class="d-block img-width" src="{{ asset('catalogo/' . $imagen->url_img) }}"
                                            alt="imagen {{ $imagen->contador }}">
                                    </div>
                                @else
                                    <div class="carousel-item row w-100 align-items-center">

                                        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5')
                                            <div class="col text-center">
                                                <button class="btn btn-danger regular-button "
                                                    onclick="eliminar({{ $comillas . $imagen->url_img . $comillas }})"
                                                    type="button">Eliminar imagen</button>
                                            </div>
                                            <br>
                                        @endif

                                        <img class="d-block img-width" src="{{ asset('catalogo/' . $imagen->url_img) }}"
                                            alt="imagen {{ $imagen->contador }} ">
                                    </div>
                                @endif
                            @endforeach


                        </div>
                        <a class="carousel-control-prev" href="#carouselExampleBigIndicators" role="button"
                            data-slide="prev">
                            <span class="" aria-hidden="true"><i class="fa-solid fa-angle-left "
                                    style="font-size: 5rem; color:#9C321A"></i></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselExampleBigIndicators" role="button"
                            data-slide="next">
                            <span class="" aria-hidden="true"><i class="fa-solid fa-angle-right "
                                    style="font-size: 5rem; color:#9C321A"></i></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>




    <div class="row mt-2">
        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="wrapper wrapper-content animated fadeInRight">


                <div class="ibox mb-0">
                    <div class="ibox-title">
                        <h3>Informacion General <i class="fa-solid fa-pen-to-square"></i></h3>
                    </div>
                    <div class="ibox-content"
                        style="height: 18.5rem;  display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Código interno:
                                </strong> {{ $producto->id }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Nombre de producto:
                                </strong> {{ $producto->nombre }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Descripción
                                    :</strong> {{ $producto->descripcion }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Codigo
                                    Estatal:</strong> {{ $producto->codigo_estatal }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Codigo de
                                    Barra:</strong> {{ $producto->codigo_barra }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Categoría :</strong>
                                {{ $producto->categoria }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Sub Categoría
                                    :</strong>
                                {{ $producto->sub_categoria }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Unidad de
                                    medida:</strong> {{ $producto->unidad_medida }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Fecha de
                                    registro:</strong> {{ $producto->fecha_registro }}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Registrado
                                    por:</strong> {{ $producto->registrado_por }}</small></p>
                        </div>

                    </div>
                </div>


            </div>
        </div>


        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="wrapper wrapper-content animated fadeInRight">


                <div class="ibox mb-0">
                    <div class="ibox-title">
                        <h3>Precios e Impuestos <i class="fa-solid fa-sack-dollar"></i></h3>

                    </div>
                    <div class="ibox-content "
                        style="height: 18.5rem; display: flex; flex-direction: column; justify-content: space-between;  ">

                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Impuesto sobre
                                la
                                venta </strong> {{ $producto->isv }}%</small></p>
                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Precio base de
                                venta:
                            </strong> {{ $producto->precio_base }} Lps.</small></p>


                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Precio A:
                        </strong> {{ $producto->precio1 }} Lps.</small></p>
                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Precio B:
                            </strong> {{ $producto->precio2 }} Lps.</small></p>
                            <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Precio C:
                                </strong> {{ $producto->precio3}} Lps.</small></p>
                                <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Precio D:
                                    </strong> {{ $producto->precio4 }} Lps.</small></p>





                        @if (Auth::user()->rol_id != '2' && Auth::user()->rol_id != '3')
                            @foreach ($precios as $precio)
                                <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Precio
                                        {{ $precio->contador }} de venta :</strong> {{ $precio->precio }} Lps</small>
                                </p>
                            @endforeach
                            <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Costo
                                    Promedio:
                                </strong> {{ $producto->costo_promedio }} Lps.</small></p>

                            <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Ultimo
                                    Costo de compra:
                                </strong> {{ $producto->ultimo_costo_compra }} Lps.</small></p>
                        @endif

                    </div>
                </div>


            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pt-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Disponibilidad de producto <i class="fa-solid fa-boxes-packing"></i> </h3>
                    </div>
                    <div class="ibox-content">
                        <h3 class=""><i class="fa-solid fa-warehouse  m-0 p-0" style="color: #1AA689"></i>
                            <span id="total_lotes"></span></h3>
                        <div class="table-responsive">
                            <table id="tbl_lotes_listar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo Producto</th>
                                        <th>Nombre de producto</th>
                                        <th>Departamento</th>
                                        <th>Municipio</th>
                                        <th>Bodega</th>
                                        <th>Dirección</th>
                                        <th>Seccion</th>
                                        <th>Numero</th>
                                        <th>Cantidad Disponible</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lotes as $lote)
                                        <tr>
                                            <td>{{ $lote->contador }}</td>
                                            <td>{{ $lote->id }}</td>
                                            <td>{{ $lote->nombre }}</td>
                                            <td>{{ $lote->departamento }}</td>
                                            <td>{{ $lote->municipio }}</td>
                                            <td>{{ $lote->bodega }}</td>
                                            <td>{{ $lote->direccion }}</td>
                                            <td>{{ $lote->seccion }}</td>
                                            <td>{{ $lote->numeracion }}</td>
                                            <td>{{ $lote->cantidad_disponible }}</td>


                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pt-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Unidades de medida para venta de producto <i class="fa-solid fa-scale-unbalanced"></i>
                        </h3>
                    </div>
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_unidades_listar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Unidad de medicion</th>
                                        <th>Cantidad de unidades</th>
                                        <th>Editar</th>

                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>







    <div class="modal fade" id="modal_producto_editar" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Editar información del producto</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="editarProductoForm" name="editarProductoForm" data-parsley-validate>
                        {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                        <div class="row" id="row_datos">
                            <div class="col-md-12">
                                <input type="hidden" id="id_producto_edit" name="id_producto_edit"
                                    value="{{ $producto->id }}">
                                <label for="nombre_producto_edit" class="col-form-label focus-label">Nombre del
                                    producto:<span class="text-danger">*</span></label>
                                <input class="form-control" required type="text" id="nombre_producto_edit"
                                    name="nombre_producto_edit" data-parsley-required>
                            </div>

                            <div class="col-md-12">
                                <label for="descripcion_producto" class="col-form-label focus-label">Descripción del
                                    producto:<span class="text-danger">*</span></label>
                                <textarea placeholder="Escriba aquí..." required id="descripcion_producto_edit" name="descripcion_producto_edit"
                                    cols="30" rows="3" class="form-group form-control" data-parsley-required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="isv_producto" class="col-form-label focus-label">ISV en %:<span
                                        class="text-danger">*</span></label>
                                <select class="form-group form-control" name="isv_producto_edit"
                                    id="isv_producto_edit" data-parsley-required>

                                    <option value="0">Excento de impuestos</option>
                                    <option value="15" selected>15% de ISV</option>
                                    <option value="18">18% de ISV</option>



                                </select>

                            </div>
                            <div class="col-md-4">
                                <label for="cod_barra_producto" class="col-form-label focus-label">Codigo de
                                    barra:</label>
                                <input class="form-group form-control" type="number" name="cod_barra_producto_edit"
                                    id="cod_barra_producto_edit" min="0">
                            </div>
                            <div class="col-md-4">
                                <label for="cod_estatal_producto" class="col-form-label focus-label">Codigo
                                    Estatal:</label>
                                <input class="form-group form-control" type="number"
                                    name="cod_estatal_producto_edit" id="cod_estatal_producto_edit" min="0">
                            </div>
                            <div class="col-md-4">
                                <label for="precioBase_edit" class="col-form-label focus-label">Precio de venta
                                    base:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" step="any" min="0.000001" type="number"
                                    name="precioBase_edit" id="precioBase_edit" data-parsley-required onchange="validacionPrecio()">
                            </div>
                            <div class="col-md-4">
                                <label for="costo_promedio_editar" class="col-form-label focus-label">Costo
                                    promedio:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" step="any" min="0.000001" type="number"
                                    name="costo_promedio_editar" id="costo_promedio_editar" data-parsley-required>
                            </div>

                            <div class="col-md-4">
                                <label for="ultimo_costo_compra_editar" class="col-form-label focus-label">Ultimo
                                    costo de compra:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" step="any" min="0.000001" type="number"
                                    name="ultimo_costo_compra_editar" id="ultimo_costo_compra_editar"
                                    data-parsley-required>
                            </div>





                            <div class="col-md-4">
                                <label for="precio1" class="col-form-label focus-label">Precio A:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" step="any"  type="number" name="precio1" id="precio1" data-parsley-required disabled >
                            </div>
                            <div class="col-md-4">
                                <label for="precio2" class="col-form-label focus-label">Precio B:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" step="any"  type="number" name="precio2" id="precio2" data-parsley-required disabled >
                            </div>
                            <div class="col-md-4">
                                <label for="precio3" class="col-form-label focus-label">Precio C:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" step="any"  type="number" name="precio3" id="precio3" data-parsley-required disabled >
                            </div>
                            <div class="col-md-4">
                                <label for="precio4" class="col-form-label focus-label">Precio D:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" step="any"  type="number" name="precio4" id="precio4" data-parsley-required disabled >
                            </div>







                            <div class="col-md-4">
                                <label for="marca_producto_editar" class="col-form-label focus-label">Marca de
                                    producto:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="marca_producto_editar"
                                    id="marca_producto_editar" data-parsley-required>
                                    <option selected disabled>---Seleccione una marca de producto---</option>


                                </select>
                            </div>


                            <div class="col-md-4">
                                <label for="categoria_producto_edit" class="col-form-label focus-label">Categoria de
                                    producto:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="categoria_producto_edit"
                                    id="categoria_producto_edit" data-parsley-required
                                    onchange="listarSubCategorias()">





                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="sub_categoria_producto_edit"
                                    class="col-form-label focus-label">Subcategoria :<span
                                        class="text-danger">*</span></label>
                                <select class="form-group form-control" name="sub_categoria_producto_edit"
                                    id="sub_categoria_producto_edit" data-parsley-required>




                                </select>
                            </div>
                            {{-- <div class="col-md-4">
                                <label class="col-form-label focus-label" for="precio2">Precio de venta 2:</label>
                                <input class="form-group form-control" min="1" type="number" name="precio_edit[]" id="precio2_edit">
                            </div>
                            <div class="col-md-4">
                                <label for="precio3" class="col-form-label focus-label">Precio de venta 3:</label>
                                <input class="form-group form-control" required min="1" type="number" name="precio_edit[]" id="precio3_edit">
                            </div> --}}

                            <div class="text-center col-md-12 mt-2">
                                <p class="font-weight-bold text-center">Unidades De Medida Para Compra</p>
                                <hr>
                            </div>


                            <div class="col-md-6">
                                <label for="unidad_producto_editar" class="col-form-label focus-label">Seleccione la
                                    unidad de medida para compra:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="unidad_producto_editar"
                                    id="unidad_producto_editar" data-parsley-required>
                                    <option selected disabled>---Seleccione una unidad---</option>


                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="precio3" class="col-form-label focus-label">cantidad de "unidades" para
                                    compra:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" min="1" type="number"
                                    name="unidades_editar" id="unidades_editar" step="any" required>
                            </div>




                        </div>
                    </form>

                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" onclick="actualizarCostos({{ $producto->id }})"
                        class="btn btn-warning">Calcular Costos</button>
                    <div>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" form="editarProductoForm" class="btn btn-primary">Guardar
                            producto</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalSpinnerLoadingTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-body">
                    <h2 class="text-center">Espere un momento...</h2>
                    <div class="loader">Loading...</div>

                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_foto_producto" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Registro de Productos</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="foto_productoForm" name="foto_productoForm" data-parsley-validate>

                        <div class="row">
                            <div class="col-md-5">
                                <label for="foto_producto_edit" class="col-form-label focus-label">Fotografía:
                                </label>
                                <input type="hidden" id="id_producto_edit_foto" name="id_producto_edit_foto"
                                    value="{{ $producto->id }}">
                                <input class="" type="file" id="foto_producto_edit"
                                    name="foto_producto_edit" accept="image/png, image/gif, image/jpeg" multiple>

                            </div>
                            <div class=" col-md-7">
                                <img id="imagenPrevisualizacion" class="ancho-imagen">

                            </div>
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="foto_productoForm" class="btn btn-primary">Guardar Imgaen</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_editar_unidades" tabindex="-1" role="dialog"
        aria-labelledby="modal_editar_unidades" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Registro de Productos</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="form_editar_unidades" name="form_editar_unidades" data-parsley-validate>
                        <input id="idUniadVenta" name="idUniadVenta" type="hidden">

                        <div class="row">
                            <div class="col-md-6">
                                <label for="unidad_venta_editar" class="col-form-label focus-label">Seleccione la
                                    unidad de medida para venta</label>
                                <select class="form-group form-control" name="unidad_venta_editar"
                                    id="unidad_venta_editar" data-parsley-required>
                                    <option selected disabled>---Seleccione una unidad---</option>


                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="unidades_venta_editar" class="col-form-label focus-label">cantidad de
                                    "unidades" para venta:</label>
                                <input class="form-group form-control" min="1" type="number"
                                    name="unidades_venta_editar" id="unidades_venta_editar" step="any" required>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="form_editar_unidades" class="btn btn-primary">Guardar
                        Cambios</button>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            const $foto_producto = document.querySelector("#foto_producto_edit"),
                $imagenPrevisualizacion = document.querySelector("#imagenPrevisualizacion");

            // Escuchar cuando cambie
            $foto_producto.addEventListener("change", () => {
                // Los archivos seleccionados, pueden ser muchos o uno
                const archivos = $foto_producto.files;
                // Si no hay archivos salimos de la función y quitamos la imagen
                if (!archivos || !archivos.length) {
                    $imagenPrevisualizacion.src = "";
                    return;
                }
                // Ahora tomamos el primer archivo, el cual vamos a previsualizar
                const primerArchivo = archivos[0];
                // Lo convertimos a un objeto de tipo objectURL
                const objectURL = URL.createObjectURL(primerArchivo);
                // Y a la fuente de la imagen le ponemos el objectURL
                $imagenPrevisualizacion.src = objectURL;
            });

            $(document).on('submit', '#foto_productoForm', function(event) {

                event.preventDefault();
                guardarFoto();

            });

            function guardarFoto() {
                $('#modal_foto_producto').modal('hide');
                $('#modalSpinnerLoading').modal('show');

                let data = new FormData($('#foto_productoForm').get(0));

                let totalfiles = document.getElementById('foto_producto_edit').files.length;
                for (var i = 0; i < totalfiles; i++) {
                    data.append("files[]", document.getElementById('foto_producto_edit').files[i]);
                };

                console.log(data);
                axios.post('/ruta/imagen/edit', data)
                    .then(response => {


                        $('#modalSpinnerLoading').modal('hide');


                        $('#foto_productoForm').parsley().reset();
                        img = document.getElementById('imagenPrevisualizacion');
                        img.src = "";
                        document.getElementById("foto_productoForm").reset();
                        $('#modal_foto_producto').modal('hide');


                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Imagen guardada con exito."
                        });

                        location.reload();

                    })
                    .catch(err => {
                        console.error(err);

                    })
            }

            $(document).ready(function() {

                var idProducto_edit = document.getElementById('id_producto_edit').value;
                obtenerDatosProductoEditar(idProducto_edit);

                $('#tbl_lotes_listar').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    pageLength: 10,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [


                    ],
                    drawCallback: function() {
                        var sum = $('#tbl_lotes_listar').DataTable().column(9).data().sum();
                        let html = 'Cantidad Total en Bodega: ' + sum
                        $('#total_lotes').html(html);
                    }



                });

                $('#tbl_unidades_listar').DataTable({

                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },

                    pageLength: 10,
                    responsive: true,
                    "ajax": "/detalle/producto/unidad/" + idProducto_edit,
                    "columns": [{
                            data: 'contador'
                        },
                        {
                            data: 'nombre'
                        },
                        {
                            data: 'unidad_venta'
                        },
                        // {
                        //     data: 'eliminar'
                        // },
                        {
                            data: 'editar'
                        },
                    ]


                });

            });


            function obtenerDatosProductoEditar(id) {
                var idProducto = document.getElementById('id_producto_edit').value;
                axios.get("/producto/datos/" + idProducto)
                    .then(response => {
                        let datos = response.data;


                        document.getElementById("nombre_producto_edit").value = datos.datosProducto.nombre;
                        document.getElementById("descripcion_producto_edit").value = datos.datosProducto.descripcion;
                        document.getElementById("isv_producto_edit").value = datos.datosProducto.isv;
                        document.getElementById("isv_producto_edit").innerHTML += '<option selected value="' + datos
                            .datosProducto.isv + '">' + datos.datosProducto.isv + ' % de ISV</option>';
                        document.getElementById("cod_barra_producto_edit").value = datos.datosProducto.codigo_barra;
                        document.getElementById("cod_estatal_producto_edit").value = datos.datosProducto.codigo_estatal;
                        document.getElementById("precioBase_edit").value = datos.datosProducto.precio_base;
                        document.getElementById("costo_promedio_editar").value = datos.datosProducto.costo_promedio;
                        document.getElementById("unidades_editar").value = datos.datosProducto.unidadad_compra;
                        document.getElementById("ultimo_costo_compra_editar").value = datos.datosProducto.ultimo_costo_compra;


                        document.getElementById("precio1").value = datos.datosProducto.precio1;
                        document.getElementById("precio2").value = datos.datosProducto.precio2;
                        document.getElementById("precio3").value = datos.datosProducto.precio3;
                        document.getElementById("precio4").value = datos.datosProducto.precio4;



                        if (datos.preciosProducto.length != 0) {
                            document.getElementById("precio2_edit").value = datos.preciosProducto[1].precio;
                            document.getElementById("precio3_edit").value = datos.preciosProducto[2].precio;
                        }





                        let arrayMarcas = datos.marcas;
                        let htmlMarca = "<option selected disabled>---Seleccione una marca de producto---</option>  ";

                        arrayMarcas.forEach(marca => {
                            if (marca.id == datos.datosProducto.marca_id) {
                                htmlMarca += `<option selected value="${marca.id}">${marca.nombre}</option>`;
                            } else {
                                htmlMarca += `<option  value="${marca.id}">${marca.nombre}</option>`;
                            }

                        });

                        let arrayCategorias = datos.categorias;
                        let htmlCategorias = "<option selected disabled>---Seleccione una categoria---</option>"

                        arrayCategorias.forEach(categoria => {
                            if (categoria.id == datos.categoria.id) {
                                htmlCategorias +=
                                    `<option selected value="${categoria.id}">${categoria.descripcion}</option>`;
                            } else {
                                htmlCategorias +=
                                    `<option  value="${categoria.id}">${categoria.descripcion}</option>`;
                            }

                        });


                        let arrayUnidades = datos.unidades;
                        let htmlUnidades = "<option selected disabled>---Seleccione una unidad---</option>"

                        arrayUnidades.forEach(unidad => {
                            if (unidad.id == datos.datosProducto.unidad_medida_compra_id) {
                                htmlUnidades += `<option selected value="${unidad.id}">${unidad.nombre}</option>`;
                            } else {
                                htmlUnidades += `<option  value="${unidad.id}">${unidad.nombre}</option>`;
                            }

                        });



                        let arraySubcategorias = datos.subCategorias;

                        let htmlSubCategorias = "<option selected disabled>---Seleccione una sub categoria---</option>"

                        arraySubcategorias.forEach(unidad => {
                            if (unidad.id == datos.subCategoria.id) {
                                htmlSubCategorias +=
                                    `<option selected value="${unidad.id}">${unidad.descripcion}</option>`;
                            } else {
                                htmlSubCategorias += `<option  value="${unidad.id}">${unidad.descripcion}</option>`;
                            }

                        });









                        document.getElementById('marca_producto_editar').innerHTML = htmlMarca;
                        document.getElementById('categoria_producto_edit').innerHTML = htmlCategorias;
                        document.getElementById('unidad_producto_editar').innerHTML = htmlUnidades;
                        document.getElementById('sub_categoria_producto_edit').innerHTML = htmlSubCategorias;




                        $('#exampleModal').modal('show');

                    });


            }

            $(document).on('submit', '#editarProductoForm', function(event) {

                event.preventDefault();
                editarProducto();

            });

            function validacionPrecio(){


                precioBase = document.getElementById('precioBase_edit').value;

                document.getElementById('precio1').setAttribute("min",precioBase);
                document.getElementById('precio2').setAttribute("min",precioBase);
                document.getElementById('precio3').setAttribute("min",precioBase);
                document.getElementById('precio4').setAttribute("min",precioBase);


                precio1 = Number(precioBase) + (precioBase*0.03);
                precio2 = Number(precioBase) + (precioBase*0.06);
                precio3 = Number(precioBase) + (precioBase*0.10);
                precio4 = Number(precioBase) + (precioBase*0.3);

                document.getElementById('precio1').value = precio1.toFixed(2);
                document.getElementById('precio2').value = precio2.toFixed(2);
                document.getElementById('precio3').value = precio3.toFixed(2);
                document.getElementById('precio4').value = precio4.toFixed(2);





                /*if(precio1<precioBase || precio2<precioBase  || precio3<precioBase  || precio4<precioBase ){
                    Swal.fire({
                        icon: 'Info',
                        title: 'Cuidado!',
                        text: "PAsegurese de que los precios A, B, C y D no sean menores que el precio base del producto."
                    })

                }*/




            }
            function editarProducto() {
                $('#modalSpinnerLoading').modal('show');

                var data = new FormData($('#editarProductoForm').get(0));

                let precio1 = document.getElementById('precio1').value;
                let precio2 =  document.getElementById('precio2').value
                let precio3 =  document.getElementById('precio3').value
                let precio4 = document.getElementById('precio4').value
                data.append('precio1', precio1);
                data.append('precio2', precio2);
                data.append('precio3', precio3);
                data.append('precio4', precio4);
                axios.post("/producto/editar", data)
                    .then(response => {
                        $('#modalSpinnerLoading').modal('hide');


                        $('#editarProductoForm').parsley().reset();
                        document.getElementById("editarProductoForm").reset();
                        $('#modal_producto_editar').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Producto Editado con exito."
                        })

                        location.reload();

                    })
                    .catch(err => {
                        $('#modalSpinnerLoading').modal('hide');
                        $('#modal_producto_editar').modal('hide');

                        console.error(err);
                        let data = err.response.data;
                        if (data.icon) {
                            Swal.fire({
                                icon: data.icon,
                                title: data.title,
                                text: data.text,
                            })
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: "Ha ocurrido un error.",
                            })
                        }

                    })

            }

            function eliminar(urlImagen) {
                //console.log("Esto es una URL --->     "+urlImagen)
                axios.post("/producto/eliminar", {
                        "urlImagen": urlImagen
                    })
                    .then(response => {

                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Imagen eliminada con exito."
                        })
                        location.reload();

                    })
                    .catch(err => {
                        console.error(err);

                    });

            }

            function modalEditarUnidades(idVentas, unidadesVentas, idUnidadVentas) {
                let id = idVentas;
                let unidadesVenta = unidadesVentas;
                let idUnidad = idUnidadVentas
                $('#modal_editar_unidades').modal('show');

                axios.get('/detalle/unidades/venta')
                    .then(response => {

                        let unidades = response.data.unidades;

                        let htmlSelect = "<option selected disabled>---Seleccione una unidad---</option>";

                        unidades.forEach(unidad => {
                            if (unidad.id == idUnidad) {
                                htmlSelect += `<option selected value="${unidad.id}">${unidad.nombre}</option>`;
                            } else {
                                htmlSelect += `<option  value="${unidad.id}">${unidad.nombre}</option>`;
                            }
                        });

                        document.getElementById("unidad_venta_editar").innerHTML = htmlSelect;
                        document.getElementById("unidades_venta_editar").value = unidadesVenta;
                        document.getElementById('idUniadVenta').value = id;



                    })
                    .catch(err => {
                        console.log(err);
                    })

            }

            $(document).on('submit', '#form_editar_unidades', function(event) {

                event.preventDefault();
                editarUnidadesVenta();

            });

            function editarUnidadesVenta() {
                var data = new FormData($('#form_editar_unidades').get(0));

                axios.post("/detalle/unidades/editar", data)
                    .then(response => {
                        $("#modal_editar_unidades").modal("hide");
                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Producto Editado con exito."
                        })

                        location.reload();
                    })
                    .catch(err => {
                        console.log(err);
                        $("#modal_editar_unidades").modal("hide");
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: "Ha ocurrido un error."
                        })

                    })
            }


            function actualizarCostos(idProducto) {

                axios.post('/producto/actualizar/costos', {
                        idProducto: idProducto
                    })
                    .then(response => {
                        let data = response.data;

                        if (data.ultimoCosto != 0 && data.costoPromedio != 0) {
                            document.getElementById('ultimo_costo_compra_editar').value = data.ultimoCosto;
                            document.getElementById('costo_promedio_editar').value = data.costoPromedio;
                        }




                    }).catch(err => {
                        console.error(err);

                    })

            }

            function listarSubCategorias() {

                var categoria_produ = document.getElementById('categoria_producto_edit').value;
                axios.get("/producto/sub_categoria/listar/" + categoria_produ)
                    .then(response => {
                        let data = response.data.sub_categorias;

                        let htmlSelect = '<option disabled selected>--Seleccione una Subcategoria--</option>'

                        data.forEach(element => {
                            htmlSelect += `<option value="${element.id}">${element.descripcion}</option>`
                        });

                        document.getElementById('sub_categoria_producto_edit').innerHTML = htmlSelect;
                    })
                    .catch(err => {
                        console.log(err.response.data)
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Ha ocurrido un error',
                        })
                    })
            }

            ///////////////////////////////////////////////////////////////////


            ///////////////////////////////////////////////////////////////////
        </script>
    @endpush


</div>
<?php
date_default_timezone_set('America/Tegucigalpa');
$act_fecha = date('Y-m-d');
$act_hora = date('H:i:s');
$mes = date('m');
$year = date('Y');
$datetim = $act_fecha . ' ' . $act_hora;
?>
<script>
    function mostrarHora() {
        var fecha = new Date(); // Obtener la fecha y hora actual
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();

        // Añadir un 0 delante si los minutos o segundos son menores a 10
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;

        // Mostrar la hora actual en el elemento con el id "reloj"
        document.getElementById("reloj").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    // Actualizar el reloj cada segundo
    setInterval(mostrarHora, 1000);
</script>
<div class="float-right">
    <?php echo "$act_fecha"; ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year"; ?>
</div>
<p id="reloj"></p>
