<div>
    @push('styles')



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
          </style>






    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Clientes</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Lista</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Edicion</a>
                </li>

            </ol>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-success" data-toggle="modal" data-target="#modal_clientes_crear"><i
                        class="fa fa-plus"></i> Registrar Cliente</a>
            </div>
            <div style="margin-top: 1.5rem">
                <a href="/cliente/excel" class="btn-seconary"><i class="fa fa-plus"></i> Exportar Excel</a>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_ClientesLista" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Dirreción</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>RTN</th>
                                        <th>Estado</th>
                                        <th>Registrado Por:</th>
                                        <th>Fecha </th>
                                        <th>Acciones</th>
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


        <!---MODAL PARA CREAR CLIENTES----->
        <div id="modal_clientes_crear" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-success">Registro de Cliente</h5>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="clientesCreacionForm" name="clientesCreacionForm" data-parsley-validate>
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                            <div class="row" id="row_datos">

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Nombre del cliente<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_cliente" name="nombre_cliente"
                                        data-parsley-required maxlength="60">
                                </div>
                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Dirección<span class="text-danger">*</span></label>
                                    <textarea name="direccion_cliente" placeholder="Escriba aquí..." required id="direccion_cliente" cols="30" rows="3"
                                        class="form-group form-control" data-parsley-required maxlength="142"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Credito Disponible<span class="text-danger">*</span></label>
                                    <input data-type="currency"  id="credito" name="credito" type="text"  step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label" for="dias_credito">Dias de credito<span class="text-danger">*</span></label>
                                    <input   id="dias_credito" name="dias_credito" type="number"  min="0" max="120" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">RTN<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" required type="text" name="rtn_cliente"
                                        id="rtn_cliente" data-parsley-required pattern="[0-9]{14}">
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Correo electrónico<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" type="text" name="correo_cliente" id="correo_cliente"
                                        data-parsley-required>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Teléfono del cliente<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" type="text" name="telefono_cliente" id="telefono_cliente"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 1<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="contacto[]"
                                        name="contacto[]" data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 1<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" required type="text" name="telefono[]" id="telefono[]" data-parsley-required pattern="[0-9]{8}">
                                </div>

                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 2</label>
                                    <input class="form-control"  type="text" id="contacto[]"
                                        name="contacto[]" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 2</label>
                                    <input class="form-group form-control"  type="text" name="telefono[]"
                                        id="telefono[]" pattern="[0-9]{8}">
                                </div>


                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Longitud</label>
                                    <input class="form-group form-control"  type="text" name="longitud_cliente"
                                        id="longitud_cliente" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Latitud</label>
                                    <input class="form-group form-control"  type="text" name="latitud_cliente"
                                        id="latitud_clientee" >
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Pais<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="pais_cliente" id="pais_cliente"
                                    onchange="obtenerDepartamentos()">
                                        <option selected disabled>---Seleccione un pais---</option>

                                    </select>
                                </div>



                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Departamento<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="departamento_cliente" id="departamento_cliente"
                                        onchange="obtenerMunicipios()">
                                        <option selected disabled>---Seleccione un departamento---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Municipio<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="municipio_cliente" id="municipio_cliente"
                                        data-parsley-required >
                                        <option selected disabled>---Seleccione un municipio---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de Personalidad<span class="text-danger">*</span> </label>
                                    <select class="form-group form-control" name="tipo_personalidad" id="tipo_personalidad"
                                        data-parsley-required>
                                        <option disabled selected>---Seleccione una opción---</option>


                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de cliente<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="categoria_cliente" id="categoria_cliente"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>

                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Vendedor<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="vendedor_cliente" id="vendedor_cliente"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>

                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="foto_cliente" class="col-form-label focus-label">Fotografía: </label>
                                    <input class="" type="file" id="foto_cliente" name="foto_cliente" accept="image/png, image/gif, image/jpeg, image/jpg" >

                                </div>
                                <div class=" col-md-7">
                                    <img id="imagenPrevisualizacion" class="ancho-imagen">

                                </div>
                            </div>
                        </form>
                        <button id="btn_crear_cliente" type="submit" class="btn btn-sm btn-primary float-left mt-4"
                            form="clientesCreacionForm"><strong>Crear
                               Cliente</strong></button>
                    </div>

                </div>
            </div>
        </div>

        <!---MODAL PARA EDITAR CLIENTES----->
        <div id="modal_clientes_editar" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-success">Editar datos del Cliente</h5>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="clientesCreacionForm_editar" name="clientesCreacionForm" data-parsley-validate>

                            <div class="row" id="row_datos">
                                <input id="idCliente" name="idCliente" type="hidden" >

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Nombre del cliente</label>
                                    <input class="form-control" required type="text" id="nombre_cliente_editar" name="nombre_cliente_editar"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Dirección</label>
                                    <textarea name="direccion_cliente_editar" placeholder="Escriba aquí..." required id="direccion_cliente_editar" cols="30" rows="3"
                                        class="form-group form-control" data-parsley-required maxlength="142"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Credito Inicial</label>
                                    <input  id="credito_inicial_editar" name="credito_inicial_editar" type="number" step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Credito Disponible</label>
                                    <input  id="credito_editar" name="credito_editar" type="number" step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label" for="dias_credito_editar">Dias de credito<span class="text-danger">*</span></label>
                                    <input   id="dias_credito_editar" name="dias_credito_editar" type="number"  min="0" max="120" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">RTN</label>
                                    <input class="form-group form-control" required type="text" name="rtn_cliente_editar"
                                        id="rtn_cliente_editar" data-parsley-required pattern="[0-9]{14}">
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Correo electrónico</label>
                                    <input class="form-group form-control" type="text" name="correo_cliente_editar" id="correo_cliente_editar"
                                        data-parsley-required>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Teléfono del cliente</label>
                                    <input class="form-group form-control" type="text" name="telefono_cliente_editar" id="telefono_cliente_editar"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 1</label>
                                    <input class="form-control" required type="text" id="contacto_1_editar"
                                        name="contacto_1_editar" data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 1</label>
                                    <input class="form-group form-control" required type="text" name="telefono_1_editar"
                                        id="telefono_1_editar" data-parsley-required pattern="[0-9]{8}">
                                </div>

                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 2</label>
                                    <input class="form-control"  type="text" id="contacto_2_editar"
                                        name="contacto_2_editar" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 2</label>
                                    <input class="form-group form-control"  type="text" name="telefono_2_editar"
                                        id="telefono_2_editar" pattern="[0-9]{8}">
                                </div>


                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Longitud</label>
                                    <input class="form-group form-control"  type="text" name="longitud_cliente_editar"
                                        id="longitud_cliente_editar" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Latitud</label>
                                    <input class="form-group form-control"  type="text" name="latitud_cliente_editar"
                                        id="latitud_cliente_editar" >
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Pais</label>
                                    <select class="form-group form-control" name="pais_cliente_editar" id="pais_cliente_editar"
                                    onchange="obtenerDepartamentosEditar()">
                                        <option selected disabled>---Seleccione un pais---</option>

                                    </select>
                                </div>



                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Departamento</label>
                                    <select class="form-group form-control" name="departamento_cliente_editar" id="departamento_cliente_editar"
                                        onchange="obtenerMunicipiosEditar()">
                                        <option selected disabled>---Seleccione un departamento---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Municipio</label>
                                    <select class="form-group form-control" name="municipio_cliente_editar" id="municipio_cliente_editar"
                                        data-parsley-required >
                                        <option selected disabled>---Seleccione un municipio---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de Personalidad </label>
                                    <select class="form-group form-control" name="tipo_personalidad_editar" id="tipo_personalidad_editar"
                                        data-parsley-required>
                                        <option disabled selected>---Seleccione una opción---</option>


                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de cliente</label>
                                    <select class="form-group form-control" name="categoria_cliente_editar" id="categoria_cliente_editar"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>

                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Vendedor</label>
                                    <select class="form-group form-control" name="vendedor_cliente_editar" id="vendedor_cliente_editar"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>
                                        @foreach ($clientes as $cliente)
                                        <option value="{{$cliente->id}}" >{{$cliente->name}}</option>
                                        @endforeach

                                    </select>
                                </div>

                            </div>
                        </form>

                        <button id="btn_crear_cliente_editar" type="submit" class="btn btn-primary  mt-4"
                            form="clientesCreacionForm_editar"><strong>Editar
                               Cliente</strong></button>
                               <button type="button" class="btn btn-default  mt-4" data-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>

        <!---MODAL PARA EDITAR FOTOGRAFIA----->
        <div id="modal_fotografia_editar" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-success">Editar fotografia del cliente</h5>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form_img_edit" name="form_img_edit" data-parsley-validate>
                        <input type="hidden" id="clienteId" name="clienteId">

                        <div class="col-md-5">
                            <label for="foto_cliente_editar" class="col-form-label focus-label">Fotografía: </label>
                            <input class="" type="file" id="foto_cliente_editar" name="foto_cliente_editar" accept="image/png, image/gif, image/jpeg, image/jpg" >

                        </div>
                        <div class=" col-md-7 mt-2">
                            <img id="imagenPrevisualizacion_editar" class="ancho-imagen">

                        </div>
                        </form>



                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button id="btn_img_editar" type="submit" class="btn btn-primary  "
                            form="form_img_edit"><strong>
                               Cambiar Imagen</strong></button>
                    </div>



                </div>
            </div>
        </div>

@push('scripts')

    <script src="{{ asset('js/js_proyecto/cliente/cliente.js') }}"></script>
@endpush
