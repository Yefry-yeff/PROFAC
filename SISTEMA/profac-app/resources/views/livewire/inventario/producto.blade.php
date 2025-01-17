<div>
    @push("styles")
    <style>
        @media (max-width: 600px) {
            .ancho-imagen {
                max-width: 200px;
            }
            }

         @media (min-width: 601px ) and (max-width:900px){
            .ancho-imagen {
                max-width: 300px;
            }
            }

            @media (min-width: 901px) {
            .ancho-imagen {
                max-width: 300px;
            }
            }

            /* a {
                pointer-events: none;
            } */
            .loader,
.loader:before,
.loader:after {
  border-radius: 50%;
}
.loader {
  color: #0dc5c1;
  font-size: 11px;
  text-indent: -99999em;
  margin: 55px auto;
  position: relative;
  width: 10em;
  height: 10em;
  box-shadow: inset 0 0 0 1em;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
}
.loader:before,
.loader:after {
  position: absolute;
  content: '';
}
.loader:before {
  width: 5.2em;
  height: 10.2em;
  background: #ffffff;
  border-radius: 10.2em 0 0 10.2em;
  top: -0.1em;
  left: -0.1em;
  -webkit-transform-origin: 5.1em 5.1em;
  transform-origin: 5.1em 5.1em;
  -webkit-animation: load2 2s infinite ease 1.5s;
  animation: load2 2s infinite ease 1.5s;
}
.loader:after {
  width: 5.2em;
  height: 10.2em;
  background: #ffffff;
  border-radius: 0 10.2em 10.2em 0;
  top: -0.1em;
  left: 4.9em;
  -webkit-transform-origin: 0.1em 5.1em;
  transform-origin: 0.1em 5.1em;
  -webkit-animation: load2 2s infinite ease;
  animation: load2 2s infinite ease;
}
@-webkit-keyframes load2 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes load2 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}



    </style>


    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Productos</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a >Listar</a>
                </li>
                <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_producto_crear">Registrar</a>
                </li>

            </ol>
        </div>


        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5' || Auth::user()->rol_id == '7')
        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_producto_crear"><i
                        class="fa fa-plus"></i> Registrar Producto</a>
            </div>
            <div style="margin-top: 1.5rem">
                <a href="/producto/excel" class="btn add-btn btn-success"><i class="fa fa-plus"></i> Exportar Excel</a>
            </div>
        </div>
        @endif




    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_productosListar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod</th>
                                        <th>Nombre</th>
                                        <th>Descripcion</th>
                                        <th>Cod. Barra</th>
                                        <th>ISV</th>
                                        <th>Cateogria</th>
                                        <th>Existencia</th>
                                        <th>Disponibilidad</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>


                    </div>
                </div>
            </div>

            <!-- Modal para registro de producto-->
            <div class="modal fade" id="modal_producto_crear" tabindex="-1" role="dialog"
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
                                <form id="crearProductoForm" name="crearProductoForm" data-parsley-validate>
                                    {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                    <div class="row" id="row_datos">
                                        <div class="col-md-12">
                                            <label for="nombre_producto" class="col-form-label focus-label">Nombre del producto:<span class="text-danger">*</span>  </label>
                                            <input class="form-control" required type="text" id="nombre_producto" name="nombre_producto"
                                                data-parsley-required>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="descripcion_producto" class="col-form-label focus-label">Descripción  del producto:<span class="text-danger">*</span></label>
                                            <textarea  placeholder="Escriba aquí..." required id="descripcion_producto" name ="descripcion_producto" cols="30" rows="3"
                                                class="form-group form-control" data-parsley-required></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="isv_producto" class="col-form-label focus-label">ISV en %:<span class="text-danger">*</span></label>
                                            <select class="form-group form-control" name="isv_producto" id="isv_producto" data-parsley-required>

                                                <option value="0">Excento de impuestos</option>
                                                <option value="15" selected>15% de ISV</option>
                                                <option value="18">18% de ISV</option>



                                            </select>

                                        </div>
                                        <div class="col-md-4">
                                            <label for="cod_barra_producto" class="col-form-label focus-label">Codigo de barra:</label>
                                            <input class="form-group form-control"  type="number" name="cod_barra_producto"
                                                id="cod_barra_producto"  min="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="cod_estatal_producto" class="col-form-label focus-label">Codigo Estatal:</label>
                                            <input class="form-group form-control" type="number" name="cod_estatal_producto"
                                                id="cod_estatal_producto" min="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="precioBase" class="col-form-label focus-label">Precio de venta base:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control" min="0" type="number" name="precioBase" id="precioBase"
                                                data-parsley-required step="any" onchange="validacionPrecio()">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="costo_promedio" class="col-form-label focus-label">Costo de compra promedio:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control" min="0" type="number" name="costo_promedio" id="costo_promedio"
                                                data-parsley-required step="any">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="ultimo_costo_compra" class="col-form-label focus-label">Ultimo costo de compra:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control" min="0" type="number" name="ultimo_costo_compra" id="ultimo_costo_compra"
                                                data-parsley-required step="any">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="precio1" class="col-form-label focus-label">Precio <b>A</b>:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control" type="number" name="precio1" id="precio1"
                                                data-parsley-required step="any" disabled >
                                        </div>

                                        <div class="col-md-4">
                                            <label for="precio2" class="col-form-label focus-label">Precio <b>B</b>:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control" type="number" name="precio2" id="precio2"
                                                data-parsley-required step="any" disabled >
                                        </div>

                                        <div class="col-md-4">
                                            <label for="precio3" class="col-form-label focus-label">Precio <b>C</b>:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control"  type="number" name="precio3" id="precio3"
                                                data-parsley-required step="any" disabled >
                                        </div>


                                        <div class="col-md-4">
                                            <label for="precio4" class="col-form-label focus-label">Precio <b>D</b>:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control" type="number" name="precio4" id="precio4"
                                                data-parsley-required step="any" disabled >
                                        </div>

                                        {{-- <div class="col-md-4">
                                            <label class="col-form-label focus-label" for="precio2">Precio de venta 2:</label>
                                            <input class="form-group form-control" min="1" type="number" name="precio[]"
                                                id="precio2" step="any">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="precio3" class="col-form-label focus-label">Precio de venta 3:</label>
                                            <input class="form-group form-control"  min="1" type="number" name="precio[]"
                                                id="precio3" step="any">
                                        </div> --}}
                                        <div class="col-md-4">
                                            <label for="categoria_producto" class="col-form-label focus-label">Marca de producto:<span class="text-danger">*</span></label>
                                            <select class="form-group form-control" name="marca_producto" id="marca_producto"
                                                data-parsley-required>
                                                <option selected disabled>---Seleccione una marca---</option>
                                                @foreach ($marcas as $marca)
                                                <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                                @endforeach


                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="categoria_producto" class="col-form-label focus-label">Categoria de producto:<span class="text-danger">*</span></label>
                                            <select class="form-group form-control" name="categoria_producto" id="categoria_producto"
                                                data-parsley-required onchange="listarSubCategorias()">
                                                <option selected disabled>---Seleccione una categoria---</option>
                                                @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}</option>
                                                @endforeach


                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="sub_categoria_producto" class="col-form-label focus-label">Subcategoria :<span class="text-danger">*</span></label>
                                            <select class="form-group form-control" name="sub_categoria_producto" id="sub_categoria_producto" data-parsley-required>
                                                <option selected disabled>---Seleccione una Subcategoria---</option>



                                            </select>
                                        </div>


                                        <div class="text-center col-md-12 mt-2">
                                            <p class="font-weight-bold text-center">Unidades De Medida Para Compra y Venta</p>
                                            <hr>
                                        </div>


                                        <div class="col-md-6">
                                            <label for="unidad_producto" class="col-form-label focus-label">Seleccione la unidad de medida para compra:<span class="text-danger">*</span></label>
                                            <select class="form-group form-control" name="unidad_producto" id="unidad_producto"
                                                data-parsley-required>
                                                <option selected disabled>---Seleccione una unidad---</option>
                                                @foreach ($unidades as $unidad)
                                                <option value="{{ $unidad->id }}">{{ $unidad->nombre }}-{{ $unidad->simbolo }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="precio3" class="col-form-label focus-label">cantidad de "unidades" para compra:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control"  min="1" type="number" name="unidades"
                                                id="unidades" step="any" required>
                                        </div>


                                        <div class="col-md-6">
                                            <label for="unidad_producto_venta" class="col-form-label focus-label">Seleccione la unidad de medida para venta:<span class="text-danger">*</span></label>
                                            <select class="form-group form-control" name="unidad_producto_venta" id="unidad_producto_venta"
                                                data-parsley-required>
                                                <option selected disabled>---Seleccione una unidad---</option>
                                                @foreach ($unidades as $unidad)
                                                <option value="{{ $unidad->id }}">{{ $unidad->nombre }}-{{ $unidad->simbolo }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="_venta" class="col-form-label focus-label">cantidad de "unidades" para venta:<span class="text-danger">*</span></label>
                                            <input class="form-group form-control"  min="1" type="number" name="unidades_venta"
                                                id="unidades_venta" step="any" required>
                                        </div>

                                        {{-- <div class="col-md-6">
                                            <label for="unidad_producto_venta" class="col-form-label focus-label">Seleccione una segunda unidad de medida para venta</label>
                                            <select class="form-group form-control" name="unidad_producto_venta2" id="unidad_producto_venta2"
                                                >
                                                <option selected disabled>---Seleccione una unidad---</option>
                                                @foreach ($unidades as $unidad)
                                                <option value="{{ $unidad->id }}">{{ $unidad->nombre }}-{{ $unidad->simbolo }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="_venta" class="col-form-label focus-label">cantidad de "unidades" para venta:</label>
                                            <input class="form-group form-control"  min="1" type="number" name="unidades_venta2"
                                                id="unidades_venta2" step="any" >
                                        </div> --}}






                                        <div class="col-md-5">
                                            <label for="foto_producto" class="col-form-label focus-label">Fotografía: </label>
                                            <input  class="" type="file" id="foto_producto" name="foto_producto" accept="image/png, image/gif, image/jpeg" multiple>

                                        </div>
                                        <div class=" col-md-7">
                                            <img id="imagenPrevisualizacion" class="ancho-imagen">

                                        </div>
                                    </div>
                                </form>

                            </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="crearProductoForm" class="btn btn-primary" >Guardar producto</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>



  <!-- Modal -->
  <div class="modal" id="modalSpinnerLoading" data-backdrop="static"  tabindex="-1" role="dialog" aria-labelledby="modalSpinnerLoadingTitle" aria-hidden="true" >
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" >
      <div class="modal-content" >

        <div class="modal-body">
            <h2 class="text-center">Espere un momento...</h2>
            <div class="loader">Loading...</div>

        </div>

      </div>
    </div>
  </div>






    </div>
    @push('scripts')

    <script src="{{ asset('js/js_proyecto/inventario/producto.js') }}"></script>

    @endpush
</div>
