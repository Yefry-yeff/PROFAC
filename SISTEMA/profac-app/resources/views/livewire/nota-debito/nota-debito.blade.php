<div>
    @if($cai_nd_existencia->existe == 0)
    <div class="alert alert-warning">
        Cuidado! no puede generar una nota de débito, debido a que no ha creado un CAI para nota de débito o el CAI autorizado no tiene unidades disponibles para utilizar. <br>
        Crear CAI de débito para continuar. <a href="/ventas/cai">Enlace para crear Cai</a>
    </div>

    @else
        <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <h2>Nota de Débito</h2>

                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html">Lista</a>
                    </li>


                </ol>
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

            <!-- Modal para registro de Monto débito-->
                <div class="modal fade" id="modal_monto_crear" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Registro de Monto de nota de débito</h3>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="montoAddForm" name="montoAddForm" data-parsley-validate>
                                    <div class="row" id="row_datos">

                                        <div class="col-md-12">
                                            <label for="monto" class="col-form-label focus-label">Ingrese Monto de Nota de Débito:<span class="text-danger">*</span></label>
                                            <input class="form-control" required type="number" step="0.01" id="monto" name="monto" data-parsley-required>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="descripcion" class="col-form-label focus-label">Descripción del monto de Nota de Débito:<span class="text-danger">*</span></label>
                                            <input class="form-control" required type="text" id="descripcion" name="descripcion" data-parsley-required>
                                        </div>

                                    </div>
                                </form>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                <button type="submit" form="montoAddForm" class="btn btn-primary">Guardar Monto</button>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Modal para registro de nota de débito-->
                <div class="modal fade" id="modal_nota_debito_crear" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Registro de Nota de débito</h3>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="ndAddForm" name="ndAddForm" data-parsley-validate>
                                    <div class="row" id="row_datos">

                                        <div class="col-md-12">
                                            <label for="factura_id" class="col-form-label focus-label">Código de factura:<span class="text-danger">*</span></label>
                                            <input class="form-control" readonly required type="number" id="factura_id" name="factura_id" data-parsley-required>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="montoNotaDebito_id" class="col-form-label focus-label">Código de Monto Activo de Débito:<span class="text-danger">*</span></label>
                                            <input class="form-control" readonly required  type="number" step="0.01" id="montoNotaDebito_id" name="montoNotaDebito_id" data-parsley-required>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="Monto" class="col-form-label focus-label">Monto a Asignar:<span class="text-danger">*</span></label>
                                            <input class="form-control" readonly required type="number" step="0.01" id="monto_" name="monto_" data-parsley-required>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="fechaEmision" class="col-form-label focus-label">Asignar fecha de Nota de débito:<span class="text-danger">*</span></label>
                                            <input class="form-control" required type="date" id="fechaEmision" name="fechaEmision" data-parsley-required>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="motivoDescripcion" class="col-form-label focus-label">Motivo o descripción de nota de débito:<span class="text-danger">*</span></label>
                                            <textarea class="form-control" required id="motivoDescripcion" name="motivoDescripcion" data-parsley-required rows="4" cols="50"></textarea>
                                        </div>

                                    </div>
                                </form>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                <button type="submit" form="ndAddForm" class="btn btn-primary">Guardar Monto</button>
                            </div>
                        </div>
                    </div>
                </div></div>

                <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
                    <div style="margin-top: 1.5rem">
                        <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                            data-target="#modal_monto_crear"><i class="fa fa-plus"></i>Registrar Monto de Nota de Débito</a>
                    </div>
                </div>

                <div class="wrapper wrapper-content animated fadeInRight">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox ">
                                <div class="ibox-content">
                                    <div class="table-responsive">
                                        <table id="tbl_listar_monto_debito" class="table table-striped table-bordered table-hover">
                                            <thead class="">
                                                <tr>

                                                    <th>Código Monto</th>
                                                    <th>Monto</th>
                                                    <th>Registrado por</th>
                                                    <th>Fecha creación</th>
                                                    <th>Estado</th>

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

            <label for="">Nota: Lista de facturas. Se enlistan todas las facturas activas para realizarse una nota de débito.
                Para revisar una lista separada de notas coorporativas y de gobierno, ingrese al menú y encontrará un enlace específico para cada una.
            </label>

            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table id="tbl_listar_facturas" class="table table-striped table-bordered table-hover">
                                        <thead class="">
                                            <tr>

                                                <th>N° Factura</th>
                                                <th>Fecha de Emision</th>
                                                <th>Cliente</th>
                                                <th>Tipo de Pago</th>
                                                <th>Fecha de Vencimiento</th>
                                                <th>Sub Total Lps.</th>
                                                <th>ISV en Lps.</th>
                                                <th>Total en Lps.</th>
                                                <th>Esto de Cobro</th>
                                                <th>Vendedor</th>
                                                <th>Nota débito</th>
                                                <th>Opciones</th>

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


            <hr>



            <hr>
            <label for="">Nota: Se enlistan todas las notas de débito generadas y listas para descargar sus pdf</label>

            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table id="tbl_listar_notas_debito" class="table table-striped table-bordered table-hover">
                                        <thead class="">
                                            <tr>

                                                <th>Código de nota de débito</th>
                                                <th>Código de Factura</th>
                                                <th>Monto Asignado</th>
                                                <th>Fecha de Emisión</th>
                                                <th>Motivo de Nota de débito</th>
                                                <th>código CAI</th>
                                                <th>Número CAI</th>
                                                <th>Correlativo Nota de débito</th>
                                                <th>Registrado por</th>
                                                <th>Estado</th>
                                                <th>Documento</th>
                                                <th>Fecha de creación de nota de débito</th>
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

        </div>
    @endif
</div>
@push('scripts')


<script src="{{ asset('js/js_proyecto/nota-debito/nota-debito.js') }}"></script>

@endpush
