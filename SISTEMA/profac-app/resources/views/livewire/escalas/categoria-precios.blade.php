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

    <div class="border-bottom white-bg page-heading align-items-center"><h4><b>Categoría de Precios</b></h4></div>

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

@push('scripts')

    <script src="{{ asset('js/js_proyecto/cliente/cliente.js') }}"></script>
@endpush

