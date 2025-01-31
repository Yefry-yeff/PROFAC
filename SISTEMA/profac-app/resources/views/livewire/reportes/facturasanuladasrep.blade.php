<div>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }

        #fecha_facturas_anuladas {
            color: red;
            font-size: 12px;
            display: none; /* Por defecto está oculto */
            margin-top: 5px;
        }
    </style>

    <h1>Facturas anuladas</h1>

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

                            <!-- Botón centrado debajo de los inputs -->
                            <div class="col-12 text-center mt-3">
                                <button class="btn btn-primary btn-lg w-50" onclick="cargafacturasanuladasrep()">
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
                            <table id="tbl_facturas_anuladas" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>FECHA DE CREACION</th>
                                        <th>NUMERO FACTURA</th>
                                        <th>NOMBRE CLIENTE</th>
                                        <th>SUBTOTAL</th>
                                        <th>ISV</th>
                                        <th>TOTAL</th>
                                        <th>TIPO CLIENTE</th>
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
<script src="{{ asset('/js/js_proyecto/reportes/facturasanuladasrep.js') }}"></script>
@endpush
