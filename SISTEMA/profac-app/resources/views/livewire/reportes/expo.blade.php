<div>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>EXPO FERIA 2025</h2>
        </div>
    </div>


    <p> <b>Nota: </b> Se requiere de selección de un rango de fechas para mostrar la información.</p>

    <h3>Pedidos totales por producto pedido en expo sala de ventas</h3>
    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <label for="fecha_inicio" class="col-form-label focus-label">Fecha de inicio:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="{{date('Y-m-01')}}">
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="fecha_final" class="col-form-label focus-label">Fecha final:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_final" name="fecha_final" value="{{date('Y-m-t')}}">
                            </div>

                        </div>
                        <button class="btn btn-primary" onclick="cargaConsulta()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <label for="">RECUERDE QUE ENTRE MAS PROLONGADA LA FECHA, MAS TIEMPO TARDARA EN RESPONDER POR LA CARGA DE DATA</label>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_facdia" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>FECHA DE VENTA</th>
                                        <th>FECHA DE VENCIMIENTO</th>
                                            <th>VENDEDOR</th>
                                            <th>COTIZADOR</th>
                                        <th>COTIZACIÓN</th>
                                        <th>CLIENTE</th>
                                        <th>TIPO CLIENTE (AoB)</th>
                                        <th>CODIGO PRODUCTO</th>
                                        <th>PRODUCTO</th>
                                        <th>MARCA</th>
                                        <th>CATEGORIA</th>
                                        <th>SUB CATEGORIA</th>
                                        <th>UNIDAD DE MEDIDA</th>
                                        <th>EXCENTO</th>
                                        <th>BODEGA</th>
                                        <th>PRECIO</th>
                                        <th>UNIDADES VENDIDAS</th>
                                        <th>SUBTOTAL PRODUCTO</th>
                                        <th>ISV PRODUCTO</th>
                                        <th>TOTAL PRODUCTO</th>
                                        <th>SUB TOTAL PEDIDO</th>
                                        <th>ISV PEDIDO</th>
                                        <th>TOTAL PEDIDO</th>
                                    </tr>
                                </thead>
                                <tbody>                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>FECHA DE VENTA</th>
                                            <th>FECHA DE VENCIMIENTO</th>
                                            <th>VENDEDOR</th>
                                            <th>COTIZADOR</th>
                                            <th>COTIZACIÓN</th>
                                            <th>CLIENTE</th>
                                            <th>TIPO CLIENTE (AoB)</th>
                                            <th>CODIGO PRODUCTO</th>
                                            <th>PRODUCTO</th>
                                            <th>MARCA</th>
                                            <th>CATEGORIA</th>
                                            <th>SUB CATEGORIA</th>
                                            <th>UNIDAD DE MEDIDA</th>
                                            <th>EXCENTO</th>
                                            <th>BODEGA</th>
                                            <th>PRECIO</th>
                                            <th>UNIDADES VENDIDAS</th>
                                            <th>SUBTOTAL PRODUCTO</th>
                                            <th>ISV PRODUCTO</th>
                                            <th>TOTAL PRODUCTO</th>
                                            <th>SUB TOTAL PEDIDO</th>
                                            <th>ISV PEDIDO</th>
                                            <th>TOTAL PEDIDO</th>
                                        </tr>
                                    </tfoot>

                                </tbody>

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

<script>
    function cargaConsulta(){

        $("#tbl_facdia").dataTable().fnDestroy();

        var fecha_inicio = document.getElementById('fecha_inicio').value;
        var fecha_final = document.getElementById('fecha_final').value;

        $('#tbl_facdia').DataTable({
            "order": ['0', 'desc'],
            "paging": true,
            "language": {
                "url": "//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"
            },
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [

                {
                    extend: 'excel',
                    title: 'PEDIDOS_EXPO_2025',
                    className:'btn btn-success'
                }
            ],
            "ajax": "/reporte/expo/pedidos/"+fecha_inicio+"/"+fecha_final,
            "columns": [
                {data: 'FECHA DE VENTA'},
                {data: 'FECHA DE VENCIMIENTO'},
                {data: 'VENDEDOR'},
                {data: 'COTIZADOR'},
                {data: 'COTIZACION'},
                {data: 'CLIENTE'},
                {data: 'TIPO CLIENTE (AoB)'},
                {data: 'CODIGO PRODUCTO'},
                {data: 'PRODUCTO'},
                {data: 'MARCA'},
                {data: 'CATEGORIA'},
                {data: 'SUB CATEGORIA'},
                {data: 'UNIDAD DE MEDIDA'},
                {data: 'EXCENTO'},
                {data: 'BODEGA'},
                {data: 'PRECIO'},
                {data: 'UNIDADES VENDIDAS'},
                {data: 'SUBTOTAL PRODUCTO'},
                {data: 'ISV PRODUCTO'},
                {data: 'TOTAL PRODUCTO'},
                {data: 'SUB TOTAL PEDIDO'},
                {data: 'ISV PEDIDO'},
                {data: 'TOTAL PEDIDO' }
            ],initComplete: function () {
                var r = $('#tbl_facdia tfoot tr');
                r.find('th').each(function(){
                  $(this).css('padding', 8);
                });
                $('#tbl_facdia thead').append(r);
                $('#search_0').css('text-align', 'center');
                this.api()
                    .columns()
                    .every(function () {
                        let column = this;
                        let title = column.footer().textContent;

                        // Create input element
                        let input = document.createElement('input');
                        input.placeholder = title;
                        column.footer().replaceChildren(input);

                        // Event listener for user input
                        input.addEventListener('keyup', () => {
                            if (column.search() !== this.value) {
                                column.search(input.value).draw();
                            }
                        });
                    });




            }


        });
    }

</script>

@endpush

