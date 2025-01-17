<div>

    @push('styles')
        <link href="{{ asset('css/plugins/iCheck/custom.css') }}" rel="stylesheet">
        <link href="{{ asset('css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"
            rel="stylesheet">
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Proveedores</h2>

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
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_proveedores_crear"><i
                        class="fa fa-plus"></i> Registrar Proveedor</a>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_proveedoresListar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Dirreción</th>
                                        <th>Contacto</th>
                                        <th>Correo</th>
                                        <th>RTN</th>
                                        <th>Retencion 1%</th>
                                        <th>Estado</th>
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




    <!---MODAL PARA CREAR PROVEEDOR----->
    <div id="modal_proveedores_crear" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Regitro de Proveedores</h5>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="proveedorCreacionForm" name="proveedorCreacionForm" data-parsley-validate>
                        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                        <div class="row" id="row_datos">
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Código:<span class="text-danger">*</span></label>
                                <input class="form-control" required type="text" id="codigo_prov" name="codigo_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-8">
                                <label class="col-form-label focus-label">Nombre del proveedor:<span class="text-danger">*</span></label>
                                <input class="form-control" required type="text" id="nombre_prov" name="nombre_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-12">
                                <label class="col-form-label focus-label">Dirección:<span class="text-danger">*</span></label>
                                <textarea name="direccion_prov" placeholder="Escriba aquí..." required id="direccion_prov" cols="30" rows="3"
                                    class="form-group form-control" data-parsley-required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Contácto:<span class="text-danger">*</span></label>
                                <input class="form-control" required type="text" id="contacto_prov"
                                    name="contacto_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Teléfono:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" required type="text" name="telefono_prov"
                                    id="telefono_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Teléfono 2:</label>
                                <input class="form-group form-control" type="text" name="telefono_prov_2"
                                    id="telefono_prov_2">
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Correo electrónico:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="text" name="correo_prov" id="correo_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Correo electrónico 2:</label>
                                <input class="form-group form-control" type="text" name="correo_prov_2"
                                    id="correo_prov_2">
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">RTN:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" required type="text" name="rtn_prov"
                                    id="rtn_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">País:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="pais_prov" id="pais_prov"
                                    onchange="obtenerDepartamentos()">
                                    <option selected disabled>---Seleccione un país---</option>
                                    @foreach ($paises as $pais)
                                        <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Departamento:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="depto_prov" id="depto_prov"
                                    onchange="obtenerMunicipios()">
                                    <option selected disabled>---Seleccione un Departamento---</option>

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Municipio:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="municipio_prov" id="municipio_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione un municipio---</option>

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Tipo de Personalidad:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="giro_neg_prov" id="giro_neg_prov"
                                    data-parsley-required>
                                    <option disabled selected>---Seleccione una opción---</option>
                                    @foreach ($tipoPersonalidad as $user)
                                        <option value="{{ $user->id }}">{{ $user->nombre }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Categoría:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="categoria_prov" id="categoria_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione una opción---</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Retenciones:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="retencion_prov" id="retencion_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione una opción---</option>
                                    @foreach ($retenciones as $retencion)
                                        <option value="{{ $retencion->id }}">{{ $retencion->nombre }}</option>
                                    @endforeach


                                </select>
                            </div>
                        </div>
                    </form>
                    <button class="btn btn-sm btn-primary float-left m-t-n-xs"
                        form="proveedorCreacionForm"><strong>Crear
                            Proveedor</strong></button>
                </div>

            </div>
        </div>
    </div>

    <!------MODAL PARA EDITAR PROVEEDOR--->
    <div id="modal_proveedores_editar" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Editar Datos De Proveedores</h5>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="proveedorEditarForm" name="proveedorEditarForm" data-parsley-validate>
                        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                        <input type="hidden" id="idProveedor" name="idProveedor" value="0" data-parsley-required>
                        <div class="row" id="row_datos">
                            <div class="col-md-4">
                                <label for="editar_codigo_prov" class="col-form-label focus-label">Código:<span class="text-danger">*</span></label>
                                <input class="form-control" required type="text" id="editar_codigo_prov" name="editar_codigo_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-8">
                                <label for="editar_nombre_prov" class="col-form-label focus-label">Nombre del proveedor:<span class="text-danger">*</span></label>
                                <input class="form-control" required type="text" id="editar_nombre_prov" name="editar_nombre_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-12">
                                <label class="col-form-label focus-label">Dirección:<span class="text-danger">*</span></label>
                                <textarea for="editar_direccion_prov" placeholder="Escriba aquí..." required name="editar_direccion_prov" id="editar_direccion_prov" cols="30" rows="3"
                                    class="form-group form-control" data-parsley-required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_contacto_prov" class="col-form-label focus-label">Contácto:<span class="text-danger">*</span></label>
                                <input class="form-control" required type="text" id="editar_contacto_prov"
                                    name="editar_contacto_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_telefono_prov" class="col-form-label focus-label">Teléfono:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" required type="text" name="editar_telefono_prov"
                                    id="editar_telefono_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_telefono_prov_2" class="col-form-label focus-label">Teléfono 2:</label>
                                <input class="form-group form-control" type="text" name="editar_telefono_prov_2"
                                    id="editar_telefono_prov_2">
                            </div>
                            <div class="col-md-4">
                                <label for="editar_correo_prov" class="col-form-label focus-label">Correo electrónico:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="text" name="editar_correo_prov" id="editar_correo_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_correo_prov_2" class="col-form-label focus-label">Correo electrónico 2</label>
                                <input class="form-group form-control" type="text" name="editar_correo_prov_2"
                                    id="editar_correo_prov_2">
                            </div>
                            <div class="col-md-4">
                                <label for="editar_rtn_prov" class="col-form-label focus-label">RTN:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" required type="text" name="editar_rtn_prov"
                                    id="editar_rtn_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_pais_prov" class="col-form-label focus-label">País:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="editar_pais_prov" id="editar_pais_prov"
                                    onchange="editarObtenerDepartamentos()">
                                    <option selected disabled>---Seleccione un país---</option>


                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_depto_prov" class="col-form-label focus-label">Departamento:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="editar_depto_prov" id="editar_depto_prov"
                                    onchange="editarObtenerMunicipios()">
                                    <option selected disabled>---Seleccione un Departamento---</option>

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editar_municipio_prov" class="col-form-label focus-label">Municipio:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="editar_municipio_prov" id="editar_municipio_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione un municipio---</option>

                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_giro_neg_prov" class="col-form-label focus-label">Tipo de Personalidad:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="editar_giro_neg_prov" id="editar_giro_neg_prov"
                                    data-parsley-required>
                                    <option disabled selected>---Seleccione una opción---</option>


                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_categoria_prov" class="col-form-label focus-label">Categoría:<span class="text-danger">*</span></label>
                                <select class="form-group form-control" name="editar_categoria_prov" id="editar_categoria_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione una opción---</option>

                                </select>
                            </div>


                        </div>
                    </form>
                    <button class="btn btn-sm btn-primary float-left m-t-n-xs"
                        form="proveedorEditarForm"><strong>Guardar Cambios</strong></button>
                </div>

            </div>
        </div>
    </div>






</div>

@push('scripts')
    <script src="{{ asset('js/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('js/js_proyecto/banco-proveedores/proveedores.js') }}"></script>
@endpush

</div>
