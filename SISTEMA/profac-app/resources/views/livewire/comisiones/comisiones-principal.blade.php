<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Módulo de Comsiones</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a >Gestiones de búsqueda</a>
                </li>


            </ol>
        </div>

            {{--          <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_usuario_crear"><i class="fa fa-plus"></i>Asignar Comisión</a>
            </div>
        </div>  --}}


    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <label for="seleccionar" class="col-form-label focus-label">Seleccionar mes para revisión de facturas:<span class="text-danger">*</span></label>
                                <select id="mes" name="mes" class="form-group form-control" style=""
                                    data-parsley-required >
                                    <option value="" selected disabled>--Seleccione--</option>
                                        <option value="01">ENERO</option>
                                        <option value="02">FEBRERO</option>
                                        <option value="03">MARZO</option>
                                        <option value="04">ABRIL</option>
                                        <option value="05">MAYO</option>
                                        <option value="06">JUNIO</option>
                                        <option value="07">JULIO</option>
                                        <option value="08">AGOSTO</option>
                                        <option value="09">SEPTIEMBRE</option>
                                        <option value="10">OCTUBRE</option>
                                        <option value="11">NOVIEMBRE</option>
                                        <option value="12">DICIEMBRE</option>
                                </select>
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="seleccionar" class="col-form-label focus-label">Seleccionar Vendedor a comisionar:<span class="text-danger">*</span></label>
                                <select id="vendedorSelect" name="vendedorSelect" class="form-group form-control" style=""
                                    data-parsley-required >
                                    <option value="" selected disabled>--Seleccione--</option>
                                </select>
                            </div>

                        </div>
                        <button class="btn btn-primary btn-block" onclick="validarTecho()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <hr>
        <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <h2>Comisión masiva</h2>
                <button disabled id="btnComiMasivo" data-toggle="modal" data-target="#modal_comision_crearMasivo" type="button" class="btn btn-info btn-block" onclick="buscarFacturas()"><i class="fa-solid fa-paper-plane text-white"  ></i> Comisionar todas las facturas cerradas en lista </button>

            </div>
        </div>
    <hr>
    <div class="wrapper wrapper-content animated fadeInRight">
        <label for="" class="col-form-label focus-label"><b> Lista de facturas cerradas:</b></label>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_facturasVendedor_cerradas" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>


                                        <th>Código Factura</th>
                                        <th>Nº Factura</th>
                                        <th>Fecha de emisión</th>
                                        <th>Fecha de vencimiento</th>
                                        <th>Fecha Máxima de gracia</th>
                                        <th>Cliente</th>
                                        <th>Total </th>
                                        <th>Estado de pago</th>
                                        <th>Comisión</th>
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

    <div class="wrapper wrapper-content animated fadeInRight">
        <label for="" class="col-form-label focus-label">  <b> Lista de facturas sin cerrar:</b></label>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_facturasVendedor_sinCerrar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>

                                        <th>Código Factura</th>
                                        <th>Nº Factura</th>
                                        <th>Fecha de emisión</th>
                                        <th>Fecha de vencimiento</th>
                                        <th>Fecha Máxima de gracia</th>
                                        <th>Cliente</th>
                                        <th>Total </th>
                                        <th>Estado de pago</th>
                                        <th>Comisión</th>
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


    <div class="modal fade" id="modal_comision_crearMasivo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Registro de Techos de Comisiones</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="comisionFormMasivo" name="comisionFormMasivo" data-parsley-validate>
                        <Label>Nota: El porcentaje de comisión que se coloque aquí, es el porcentaje que se le aplicará a <b>Todas</b> las facturas cerradas que se le acaban de enlistar.</Label>
                        <div class="row" id="row_datos">
                                <div class="col-md-12">
                                    <label  class="col-form-label focus-label">Porcentaje de 0 a 100 (Ejemplo: 50 - equivaliendo al 50%): <span class="text-danger">*</span></label>
                                    <input  class="form-control" required type="number" min="0" id="porcentaje" name="porcentaje"  data-parsley-required >
                                </div>
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="comisionFormMasivo" class="btn btn-primary">Guardar Comisión</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalSpinnerLoadingTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-body">
                    <h2 class="text-center">Espere un momento, estamos generando las comisiones...</h2>
                    <div class="loader">Loading...</div>

                </div>

            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('js/js_proyecto/comisiones/comisiones-principal.js') }}"></script>
@endpush
