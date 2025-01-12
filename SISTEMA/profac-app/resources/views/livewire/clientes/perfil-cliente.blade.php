<div>

    @push('styles')
    <link href="{{ asset('css/plugins/iCheck/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"
        rel="stylesheet">
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Perfil de Clientes</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Descripción</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Edicion</a>
                </li>

            </ol>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-warning" data-toggle="modal" data-target="#modal_clientes_editar"><i
                        class="fa fa-plus"></i> Editar información del Cliente</a>
            </div>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-info" data-toggle="modal" data-target="#modal_agregar_foto"><i
                        class="fa fa-plus"></i>Agregar imagen del Cliente</a>
            </div>
        </div>
    </div>
            <!------MODAL PARA EDITAR Clientes--->
            <div id="modal_clientes_editarr" class="modal custom-modal fade" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-success">Editar Datos De Clientes</h5>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="clientes_editaForm" name="clientes_editaForm" data-parsley-validate>
                                <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                                <div class="row" id="row_datos">
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Código:</label>
                                        <input class="form-control" required type="text" id="codigo_cliente_edita" name="codigo_cliente_edita"
                                            data-parsley-required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="col-form-label focus-label">Nombre del cliente</label>
                                        <input class="form-control" required type="text" id="nombre_cliente_edita" name="nombre_cliente_edita"
                                            data-parsley-required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="col-form-label focus-label">Dirección</label>
                                        <textarea name="direccion_cliente_edita" placeholder="Escriba aquí..." required id="direccion_cliente_edita" cols="30" rows="3"
                                            class="form-group form-control" data-parsley-required></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Longitud</label>
                                        <input class="form-group form-control" required type="text" name="longitud_cliente_edita"
                                            id="longitud_cliente_edita" data-parsley-required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Latitud</label>
                                        <input class="form-group form-control" required type="text" name="latitud_cliente_edita"
                                            id="latitud_cliente_edita" data-parsley-required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">RTN</label>
                                        <input class="form-group form-control" required type="text" name="rtn_cliente_edita"
                                            id="rtn_cliente_edita" data-parsley-required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Correo electrónico</label>
                                        <input class="form-group form-control" type="text" name="correo_cliente_edita" id="correo_cliente_edita"
                                            data-parsley-required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Contácto</label>
                                        <input class="form-control" required type="text" id="contacto_cliente_edita"
                                            name="contacto_cliente_edita" data-parsley-required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Teléfono</label>
                                        <input class="form-group form-control" required type="text" name="telefono_cliente_edita"
                                            id="telefono_cliente_edita" data-parsley-required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Teléfono 2</label>
                                        <input class="form-group form-control" type="text" name="telefono_cliente_2_edita"
                                            id="telefono_cliente_2_edita">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">País</label>
                                        <select class="form-group form-control" name="pais_cliente_edita" id="pais_cliente_edita"
                                            onchange="obtenerDepartamentos()">
                                            <option selected disabled>---Seleccione un país---</option>
                                            {{-- @foreach ($paises as $pais)
                                                <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                            @endforeach --}}

                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Departamento</label>
                                        <select class="form-group form-control" name="depto_cliente_edita" id="depto_cliente_edita"
                                            onchange="obtenerMunicipios()">
                                            <option selected disabled>---Seleccione un Departamento---</option>

                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Municipio</label>
                                        <select class="form-group form-control" name="municipio_cliente_edita" id="municipio_cliente_edita"
                                            data-parsley-required>
                                            <option selected disabled>---Seleccione un municipio---</option>

                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Tipo de Personalidad </label>
                                        <select class="form-group form-control" name="giro_neg_cliente_edita" id="giro_neg_cliente_edita"
                                            data-parsley-required>
                                            <option disabled selected>---Seleccione una opción---</option>
                                           {{--  @foreach ($tipoPersonalidad as $user)
                                                <option value="{{ $user->id }}">{{ $user->nombre }}</option>
                                            @endforeach --}}

                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Categoría</label>
                                        <select class="form-group form-control" name="categoria_cliente_edita" id="categoria_cliente_edita"
                                            data-parsley-required>
                                            <option selected disabled>---Seleccione una opción---</option>
                                            {{-- @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                            @endforeach --}}
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="col-form-label focus-label">Vendedor</label>
                                        <select class="form-group form-control" name="vendedor_cliente_edita" id="vendedor_cliente_edita"
                                            data-parsley-required>
                                            <option selected disabled>---Seleccione una opción---</option>
                                            {{-- @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                            @endforeach --}}
                                        </select>
                                    </div>
                                </div>
                            </form>
                            <button class="btn btn-sm btn-primary float-left m-t-n-xs"
                                form="clientesCreacionForm"><strong>Crear
                                   Cliente</strong></button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal_agregar_foto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Agregar imagen de cliente</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="foto_productoForm" name="foto_productoForm" data-parsley-validate>

                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="foto_cliente_edit" class="col-form-label focus-label">Fotografía: </label>
                                        <input class="" type="file" id="foto_cliente_edit" name="foto_cliente_edit" accept="image/png, image/gif, image/jpeg" multiple>

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
</div>
