<div>


    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Retenciones</h2>

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
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_retenciones_crear"><i
                        class="fa fa-plus"></i> Agregar Retencion</a>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_retenciones" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th>Tipo de Retencion</th>
                                        <th>Registrado por:</th>
                                        <th>Fecha de registro</th>
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

            <!----MODAL PARA CREAR RETENCIONES---->
    <div id="modal_retenciones_crear" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Regitro de Retenciones</h5>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="retencionCreacionForm" name="retencionCreacionForm" data-parsley-validate>

                        <div class="row" id="row_datos">
                            <div class="col-md-12">
                                <label for="nombre_retencion" class="col-form-label focus-label">Descripción:</label>
                                <input class="form-control" required type="text" id="nombre_retencion" name="nombre_retencion"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-12">
                                <label for="valor_retencion" class="col-form-label focus-label">Valor:</label>
                                <input class="form-control" required type="number" id="valor_retencion" name="valor_retencion"
                                    data-parsley-required>
                            </div>

                            <div class="col-md-12">
                                <label for="tipo_retencion" class="col-form-label focus-label">Tipo de Retencion</label>
                                <select class="form-group form-control" name="tipo_retencion" id="tipo_retencion">
                                    <option selected disabled>---Seleccione un tipo---</option>
                                    @foreach ($listaRetenciones as $retencion)
                                    <option value="{{ $retencion->id }}">{{ $retencion->nombre }}</option>
                                    @endforeach



                                </select>
                            </div>

                        </div>
                    </form>
                    <button class="btn btn-sm btn-primary float-left m-t-n-xs"
                        form="retencionCreacionForm"><strong>Crear
                            Retencion</strong></button>
                </div>

            </div>
        </div>
    </div>

    </div>




    @push('scripts')
        <script src="{{ asset('js/js_proyecto/inventario/retenciones.js') }}"></script>
    @endpush
</div>
