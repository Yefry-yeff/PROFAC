
    <div>
        <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <h2>Desgloce de comisiones Personales de:  {{ Auth::user()->name }}</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html"></a>
                    </li>
                </ol>
            </div>
        </div>





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
                                            <th>Mes de Factura</th>
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
                                            <th>Mes de Factura</th>
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





        @push('scripts')
            <script src="{{ asset('js/js_proyecto/comisiones/comisiones-vendedor.js') }}"></script>
        @endpush

    </div>



