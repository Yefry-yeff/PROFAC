<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Histórico de comisiones. </h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Lista de las facturas y vendedor comisionadas. </a>
                </li>


            </ol>
        </div>
        <br>

    </div>

    <input type="hidden" id="modalx" name="modalx" />
    <input type="hidden" id="formx" name="formx" />
    <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalSpinnerLoadingTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-body">
                    <h2 class="text-center">Espere un momento...</h2>
                    <div class="loader">Loading...</div>

                </div>

            </div>
        </div>
    </div>

        <div class="wrapper wrapper-content animated fadeInRight">
            <br>
             <label for="" class="col-form-label focus-label"><b> Lista Comisiones por Mes:</b></label>
             <div class="row">
                 <div class="col-lg-12">
                     <div class="ibox ">
                         <div class="ibox-content">
                             <div class="table-responsive">
                                 <table name="tbl_historico_comisionesMes" id="tbl_historico_comisionesMes" class="table table-striped table-bordered table-hover">
                                     <thead class="">
                                         <tr>
                                            <th>Código de Vendedor</th>
                                            <th>Vendedor</th>
                                            <th>Mes de comisión</th>
                                            <th>Cantidad de facturas comisionadas</th>
                                            <th>Techo asignado</th>
                                            <th>Ganancia total del Mes</th>
                                            <th>Monto Asignado</th>
                                            <th>Estado de pago</th>
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
           <br>
            <label for="" class="col-form-label focus-label"><b> Lista Comisiones por factura:</b></label>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox ">
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table name="tbl_historico_comisones" id="tbl_historico_comisones" class="table table-striped table-bordered table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>Código de comisión</th>
                                            <th>Código de Factura</th>
                                            <th>Número de Factura</th>
                                            <th>Código de vendedor</th>
                                            <th>Vendedor</th>
                                            <th>Mes de facturación</th>
                                            <th>Ganancia de Factura x vendedor</th>
                                            <th>Procentaje Asignado</th>
                                            <th>Monto de ganancia asignado</th>
                                            <th>Usuario de Registro</th>
                                            <th>Fecha de Registro</th>
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
            <br>
            <label for="" class="col-form-label focus-label"><b> Lista Comisiones Pagadas:</b></label>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox ">
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table name="tbl_historico_comisionesPagadas" id="tbl_historico_comisionesPagadas" class="table table-striped table-bordered table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>Código de vendedor</th>
                                            <th>Vendedor</th>
                                            <th>Mes de comisión</th>
                                            <th>Código de mes</th>
                                            <th>Cantidad de facturas comisionadas</th>
                                            <th>Techo asignado</th>
                                            <th>Ganancia total de las facturas del mes</th>
                                            <th>Monto asignado y pagado</th>
                                            <th>Usuario de registro de pago</th>
                                            <th>Fecha del registro de pago</th>
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


 @push('scripts')

    <script src="{{ asset('js/js_proyecto/comisiones/comisiones-historico.js') }}"></script>
@endpush
</div>
