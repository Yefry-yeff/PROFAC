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
    <h1>Reporte de LIBRO DE COBRO</h1>

    <div class="pb-0 wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="form-group col-lg-6 offset-lg-3"> <!-- Centrado del campo de fecha -->
                                <label for="fecha_venta">Fecha que se generara el Libro de Venta:</label>
                                <input type="date" id="fecha_venta" class="form-control" onchange="carga_libro_venta()">
                                <div id="fecha_venta_error" class="mt-2 text-danger" style="display: none;">Por favor, seleccione una fecha válida.</div>
                            </div>
                        </div>
                        <div class="mt-5 row justify-content-center"> <!-- Centrado del botón y margen amplio -->
                            <div class="col-auto">
                                <button id="btn_exportar_pdf" class="btn btn-primary" onclick="exportarPdf()">Exportar a PDF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p><b>Nota:</b> Se requiere la selección de una fecha para mostrar la información.</p>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_libro_venta" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>VENDEDOR</th>
                                        <th>CLIENTE</th>
                                        <th>FACTURA</th>
                                        <th>EXONERADO</th>
                                        <th>GRAVADO</th>
                                        <th>EXCENTO</th>
                                        <th>SUBTOTAL</th>
                                        <th>ISV</th>
                                        <th>TOTAL</th>
                                        <th>FECHA COMPRA</th>
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
<script src="{{ asset('/js/js_proyecto/reportes/libroventarep.js') }}"></script>
@endpush
