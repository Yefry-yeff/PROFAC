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
            <h2>Reporte de comision</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">/ Comision</a>
                </li>


            </ol>
        </div>

    </div>


    <p> <b>Nota: </b> Se requiere de selección de un rango de fechas para mostrar la información.</p>

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

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_facdia" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>FECHA</th>
                                        <th>FECHA VENCIMIENTO</th>
                                        <th>CRÉDITO/CONTADO</th>
                                        <th>TIPO CLIENTE (AoB)</th>
                                        <th>VENDEDOR</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>CÓDIGO</th>
                                        <th>PRODUCTO</th>
                                        <th>SUB TOTAL PRODUCTO</th>
                                        <th>ISV</th>
                                        <th>TOTAL PRODUCTO</th>
                                        <th>CONTADO 1.75%</th>
                                        <th>CREDITO 1.5%</th>
                                        <th>COMISION MISELANEOS 3%</th>
                                    </tr>
                                </thead>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>FECHA</th>
                                        <th>FECHA VENCIMIENTO</th>
                                        <th>CRÉDITO/CONTADO</th>
                                        <th>TIPO CLIENTE (AoB)</th>
                                        <th>VENDEDOR</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>CÓDIGO</th>
                                        <th>PRODUCTO</th>
                                        <th>SUB TOTAL PRODUCTO</th>
                                        <th>ISV</th>
                                        <th>TOTAL PRODUCTO</th>
                                        <th>CONTADO 1.75%</th>
                                        <th>CREDITO 1.5%</th>
                                        <th>COMISION MISELANEOA 3%</th>
                                    </tr>
                                </tfoot>
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

        // Si la tabla ya está inicializada, mover la fila de filtros de vuelta
        // al tfoot antes de destruirla (fue movida al thead en la primera carga)
        if ($.fn.DataTable.isDataTable('#tbl_facdia')) {
            var searchRow = $('#tbl_facdia thead tr:eq(1)');
            if (searchRow.length) {
                $('#tbl_facdia tfoot').append(searchRow);
            }
            $('#tbl_facdia').DataTable().destroy();
        }

        var fecha_inicio = document.getElementById('fecha_inicio').value;
        var fecha_final = document.getElementById('fecha_final').value;

        $('#tbl_facdia').DataTable({
            "order": ['0', 'desc'],
            "paging": true,
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lfgitp',
            buttons: [
                {
                    extend: 'excel',
                    title: 'COMISIONES',
                    className:'btn btn-success'
                }
            ],
            "ajax": "/consultaComision/"+fecha_inicio+"/"+fecha_final,
            "columns": [
                {data: 'FECHA'},
                {data: 'FECHA VENCIMIENTO'},
                {data: 'CRÉDITO/CONTADO'},
                {data: 'TIPO CLIENTE (AoB)'},
                {data: 'VENDEDOR'},
                {data: 'FACTURA'},
                {data: 'CLIENTE'},
                {data: 'CÓDIGO'},
                {data: 'PRODUCTO'},
               // {data: 'PRECIO PRODUCTO'},
               // {data: 'CANTIDAD'},
                {data: 'SUB TOTAL PRODUCTO'},
                {data: 'ISV'},
                {data: 'TOTAL PRODUCTO'},
                //{data: 'SUB TOTAL FACTURA'},
                //{data: 'TOTAL FACTURA'},
                //{data: 'SUB TOTAL DIFERENCIA'},
                {data: 'CONTADO_175_PORC'},
                {data: 'CREDITO_15_PORC'},
                {data: 'COMISION_MISELANEOS'}
            ],
            initComplete: function () {
                // Recoger títulos de la primera fila del thead (siempre disponibles)
                var headerTitles = [];
                $('#tbl_facdia thead tr:first th').each(function () {
                    headerTitles.push($(this).text());
                });

                // Mover la fila de filtros del tfoot al thead (segunda fila de búsqueda)
                var r = $('#tbl_facdia tfoot tr');
                r.find('th').each(function () {
                    $(this).css('padding', 8);
                });
                $('#tbl_facdia thead').append(r);

                this.api().columns().every(function () {
                    let column = this;
                    let footer = column.footer();
                    if (!footer) return; // protección: tfoot puede no existir

                    let title = headerTitles[column.index()] || '';

                    let input = document.createElement('input');
                    input.placeholder = title;
                    $(footer).empty().append(input);

                    input.addEventListener('keyup', () => {
                        if (column.search() !== input.value) {
                            column.search(input.value).draw();
                        }
                    });
                });
            }
        });
    }
</script>

@endpush


