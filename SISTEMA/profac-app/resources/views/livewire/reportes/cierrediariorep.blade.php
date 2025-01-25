<div>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
    </style>
    <h1>Reporte de Cierre Diario</h1>

    <div class="pb-0 wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">
    <div class="form-group">
        <label for="fecha_cierre">Fecha de Cierre:</label>
        <input type="date" id="fecha_cierre" class="form-control" onchange="cargaCierreDiario()">
    </div>
</div>
</div>
</div>
</div>
</div>
    <p> <b>Nota: </b> Se requiere de selección de una fechas para mostrar la información.</p>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
    <table id="tbl_cierre_diario" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>FECHA DE CIERRE</th>
                <th>REGISTRADO POR</th>
                <th>ESTADO DE CAJA</th>
                <th>ID FACTURA</th>
                <th>CAI</th>
                <th>FACTURA</th>
                <th>CLIENTE</th>
                <th>VENDEDOR</th>
                <th>SUBTOTAL FACTURADO</th>
                <th>ISV FACTURADO</th>
                <th>TOTAL FACTURADO</th>
                <th>CALIDAD DE FACTURA</th>
                <th>TIPO DE CLIENTE</th>
                <th>PAGO POR</th>
                <th>BANCO</th>
                <th>ABONO</th>
                <th>FECHA DE PAGO</th>
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
<script src="{{ asset('/js/js_proyecto/reportes/cierrediariorep.js') }}"></script>
@endpush
