<div>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }

        #fecha_cobro_error {
            color: red;
            font-size: 12px;
            display: none;
            margin-top: 5px;
        }
    </style>
    <h1>Reporte de Comisiones</h1>

    <div class="pb-0 wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="fecha_inicio" class="col-form-label focus-label">Fecha de inicio:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_inicio" name="fecha_inicio">
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="fecha_final" class="col-form-label focus-label">Fecha final:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_final" name="fecha_final">
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="vendedor">Seleccionar Vendedor:<span class="text-danger"></span> </label>
                                    <select name="vendedor" id="vendedor" class="form-group form-control" data-parsley-required>
                                      <option value="" selected disabled>--Seleccionar un vendedor--</option>
                                    </select>
                            </div>


                            <!-- Botón centrado debajo de los inputs -->
                            <div class="mt-3 text-center col-12">
                                <button class="btn btn-primary btn-lg w-50" onclick="carga_comision()">
                                    <i class="text-white fa-solid fa-paper-plane"></i> Solicitar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p><b>Nota:</b> Se requiere de selección de fechas para mostrar la información.</p>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_comisiones" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Vendedor</th>
                                        <th>Cliente</th>
                                        <th>Factura</th>
                                        <th>Cod. Prod</th>
                                        <th>Observacion</th>
                                        <th>Exonerado</th>
                                        <th>Excento</th>
                                        <th>Abono</th>
                                        <th>Base Comisionable</th>
                                        <th>Isv</th>
                                        <th>Total</th>
                                        <th>Fecha Pago.</th>
                                        <th>Fecha Entrega</th>
                                        <th>Fecha Vencimiento</th>

                                        <th>(BA-30)</th>
                                        <th>(BA-60)</th>
                                        <th>(BA-90)</th>

                                        <th>(PA-30)</th>
                                        <th>(PA-60)</th>
                                        <th>(PA-90)</th>

                                        <th>(PB-30)</th>
                                        <th>(PB-60)</th>
                                        <th>(PB-90)</th>


                                        <th>(PC-30)</th>
                                        <th>(PC-60)</th>
                                        <th>(PC-90)</th>

                                        <th>(PD-30)</th>
                                        <th>(PD-60)</th>
                                        <th>(PD-90)</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="{{ asset('/js/js_proyecto/reportes/comisiones.js') }}"></script>
@endpush
