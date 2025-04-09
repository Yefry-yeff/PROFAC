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

        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5' || Auth::user()->rol_id == '7' || Auth::user()->rol_id == '9')
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

                                        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5' || Auth::user()->rol_id == '7' || Auth::user()->rol_id == '9')
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

                                        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5'  || Auth::user()->rol_id == '9')
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
        <script src="{{ asset('js/js_proyecto/inventario/detalle-producto.js') }}"></script>
    @endpush


</div>
