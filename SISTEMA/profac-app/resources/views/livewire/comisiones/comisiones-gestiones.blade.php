<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Módulo de Comsiones</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Gestiones generales</a>
                </li>
            </ol>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_techo_crear"><i class="fa fa-plus"></i>Asignar techo de comisión</a>
            </div>
        </div>

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
        <!-- Modal para registro de techo-->
            <div class="modal fade" id="modal_techo_crear" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de Techos de Comisiones</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="techoAddForm" name="techoAddForm" data-parsley-validate>
                                <Label>Nota: Al insertar un techo de comisión en ésta ventana, usted está asignando un techo general a <b>Todos</b> los colaboradores con rol de Vendedor. <br> A su vez, si se registra un nuevo vendedor, deberá volver a asignar el techo general, para que éste sea aplicado a los vendedores nuevos.</Label>
                                <div class="row" id="row_datos">

                                    <div class="col-md-12">
                                        <label  class="col-form-label focus-label">Ingrese un techo General de comisiones (ejemplo: 15000):<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="number" min="0" id="techo" name="techo" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="seleccionar" class="col-form-label focus-label">Seleccionar mes para revisión de facturas:<span class="text-danger">*</span></label>
                                        <select id="mes" name="mes" class="form-group form-control" style=""
                                            data-parsley-required >
                                            <option value="" selected disabled>--Seleccione--</option>
                                                <option value="1">ENERO</option>
                                                <option value="2">FEBRERO</option>
                                                <option value="3">MARZO</option>
                                                <option value="4">ABRIL</option>
                                                <option value="5">MAYO</option>
                                                <option value="6">JUNIO</option>
                                                <option value="7">JULIO</option>
                                                <option value="8">AGOSTO</option>
                                                <option value="9">SEPTIEMBRE</option>
                                                <option value="10">OCTUBRE</option>
                                                <option value="11">NOVIEMBRE</option>
                                                <option value="12">DICIEMBRE</option>
                                        </select>

                                    </div>

                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="techoAddForm" class="btn btn-primary">Guardar Techo</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal_techo_editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de Techos de Comisiones</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="techoeditform" name="techoeditform" data-parsley-validate>
                                <Label>Nota: El porcentaje aplicado será guardado unicamente a ésta factura, de no ser lo que necesita, regresar a la pantalla anterior a comisionar masivamente.</Label>
                                <div class="row" id="row_datos">
                                        <div class="col-md-12">
                                            <label  class="col-form-label focus-label">Código de vendedor: <span class="text-danger">*</span></label>
                                            <input  class="form-control" required type="number" min="0" id="idVendedor"  name="idVendedor" data-parsley-required>
                                        </div>
                                        <div class="col-md-12">
                                            <label  class="col-form-label focus-label">Mes de asignación: <span class="text-danger">*</span></label>
                                            <input  class="form-control" required type="text" id="mesL" name="mesL"  data-parsley-required>
                                        </div>
                                        <div class="col-md-12">
                                            <label  class="col-form-label focus-label">Techo actuál: <span class="text-danger">*</span></label>
                                            <input  class="form-control" required type="text" min="0" id="techoAct" name="techoAct"  data-parsley-required >
                                        </div>
                                        <div class="col-md-12">
                                            <label  class="col-form-label focus-label">Nuevo techo a asignar: <span class="text-danger">*</span></label>
                                            <input  class="form-control" required type="number" min="0" id="nuevoTecho" name="nuevoTecho"  data-parsley-required >
                                        </div>
                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="techoeditform" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <label for="" class="col-form-label focus-label"><b> Nota: Para filtros de techos de comision, se tomará en cuenta el último registrado </b></label>
       <br>
        <label for="" class="col-form-label focus-label"><b> Lista de techos de comisiones generales:</b></label>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table name="tbl_techos_guardados" id="tbl_techos_guardados" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Código Vendedor</th>
                                        <th>Mes Correspondiente</th>
                                        <th>Vendedor</th>
                                        <th>Techo Asignado</th>
                                        <th>Fecha de Registro</th>
                                        <th>User de Registro</th>
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


    @push('scripts')


     <script src="{{ asset('js/js_proyecto/comisiones/comisiones-gestiones.js') }}"></script>

    @endpush
</div>
