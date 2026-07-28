<div class="reporteria-page">
    @push('styles')
    <style>
        :root {
            --rep-grad: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            --rep-orange: #e67e22;
            --rep-border: #e8d5bf;
            --rep-radius: 8px;
            --rep-shadow: 0 2px 8px rgba(0, 0, 0, .10);
        }
        .rep-panel { background:#fff; border:1px solid var(--rep-border); border-radius:var(--rep-radius); box-shadow:var(--rep-shadow); overflow:hidden; margin-bottom:22px; }
        .rep-panel-header { min-height:50px; padding:11px 18px; background:var(--rep-grad); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .rep-panel-title { display:flex; align-items:center; gap:9px; color:#fff; font-size:13px; font-weight:700; text-transform:uppercase; margin:0; }
        .rep-panel-title i { width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.18); border-radius:6px; }
        .rep-panel-action { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.18)!important; color:#fff!important; border:1px solid rgba(255,255,255,.55)!important; border-radius:5px!important; padding:6px 13px; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; }
        .rep-panel-action:hover, .rep-panel-action:focus { background:rgba(255,255,255,.30)!important; color:#fff!important; }
        .rep-filter-bar { padding:14px 18px; background:#fdfaf5; border-bottom:1px solid var(--rep-border); }
        .rep-filter-grid { display:grid; grid-template-columns:minmax(180px, 260px) minmax(180px, 260px) auto; align-items:end; gap:12px; }
        .rep-field label { display:block; margin:0 0 5px; color:#555; font-size:12px; font-weight:700; }
        .rep-field .form-control { height:34px; border:1px solid #d9d9d9; border-radius:5px; font-size:12px; }
        .rep-field .form-control:focus { border-color:var(--rep-orange); box-shadow:0 0 0 .15rem rgba(230,126,34,.15); }
        .rep-run-btn { height:34px; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:0 16px; background:var(--rep-grad)!important; color:#fff!important; border:0!important; border-radius:5px!important; font-size:12px; font-weight:700; cursor:pointer; }
        .rep-run-btn:hover, .rep-run-btn:focus { filter:brightness(.96); color:#fff!important; }
        .rep-table-body { padding:16px 18px; }
        .rep-table-scroll { width:100%; overflow-x:auto; }
        .rep-table { width:100%!important; margin:0!important; }
        .rep-table thead th { background:#fdf4e7; color:#7d3f00; border-top:0!important; border-bottom:2px solid #f2d49a!important; padding:8px 10px!important; font-size:11px; font-weight:800; text-transform:uppercase; white-space:nowrap; vertical-align:middle; }
        .rep-table tbody td { padding:8px 10px!important; font-size:12px; color:#374151; vertical-align:middle; white-space:nowrap; }
        .rep-table tbody tr:nth-child(even) { background:#fcfcfd; }
        .rep-table tbody tr:hover td { background:#fff8ec!important; }
        .rep-table tfoot input, .rep-table thead input { width:100%; min-width:90px; height:27px; box-sizing:border-box; border:1px solid #e0c9ae; border-radius:4px; padding:3px 7px; font-size:10px; font-weight:400; color:#555; background:#fff; text-transform:none; }
        .rep-table tfoot input:focus, .rep-table thead input:focus { border-color:var(--rep-orange); outline:none; box-shadow:0 0 0 2px rgba(230,126,34,.12); }
        .reporteria-page .dataTables_wrapper { width:100%!important; padding-bottom:4px; }
        .reporteria-page .dataTables_wrapper.form-inline { display:block!important; }
        .reporteria-page .dataTables_length, .reporteria-page .dataTables_filter, .reporteria-page .dt-buttons { margin-bottom:9px; }
        .reporteria-page .dataTables_filter input, .reporteria-page .dataTables_length select { border:1px solid #d9d9d9; border-radius:5px; font-size:12px; }
        .reporteria-page .html5buttons .btn, .reporteria-page .dt-buttons .btn { border:1px solid #b7dfc5!important; background:#f0fdf4!important; color:#19733d!important; border-radius:5px!important; font-size:12px; font-weight:700; }
        .reporteria-page .dataTables_processing { z-index:4; border:1px solid var(--rep-border); box-shadow:var(--rep-shadow); color:#7d3f00; }
        #page-wrapper { padding-left:0!important; padding-right:0!important; }
        .reporteria-page .wrapper-content { padding-left:0!important; padding-right:0!important; }
        .reporteria-page .wrapper-content > .row { margin-left:0!important; margin-right:0!important; }
        .reporteria-page .wrapper-content > .row > [class*="col-"] { padding-left:0!important; padding-right:0!important; }
        @media (max-width:767px) {
            .rep-filter-grid { grid-template-columns:1fr; }
            .rep-run-btn { width:100%; }
            .rep-panel-header { padding:10px 12px; }
            .rep-table-body { padding:10px; }
        }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-bar-chart mr-2" style="color:#e67e22"></i>Reportería General</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Reportes</li>
                <li class="breadcrumb-item active"><strong>Reportería General</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <section class="rep-panel">
                    <div class="rep-panel-header">
                        <h5 class="rep-panel-title"><i class="fa fa-line-chart"></i>Ventas por producto</h5>
                    </div>
                    <div class="rep-filter-bar">
                        <div class="rep-filter-grid">
                            <div class="rep-field">
                                <label for="fecha_inicio">Fecha de inicio <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="rep-field">
                                <label for="fecha_final">Fecha final <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" id="fecha_final" name="fecha_final" value="{{ date('Y-m-t') }}">
                            </div>
                            <button type="button" class="rep-run-btn" onclick="cargaConsulta()">
                                <i class="fa fa-search"></i>Consultar ventas
                            </button>
                        </div>
                    </div>
                    <div class="rep-table-body">
                        <div class="rep-table-scroll">
                            <table id="tbl_facdia" class="table table-bordered table-hover rep-table">
                                <thead><tr>
                                    <th>Fecha de venta</th><th>Fecha de vencimiento</th><th>Vendedor</th><th>Factura</th><th>Cliente</th>
                                    <th>Tipo cliente (A o B)</th><th>Crédito / contado</th><th>Código producto</th><th>Producto</th><th>Marca</th>
                                    <th>Categoría</th><th>Subcategoría</th><th>Unidad de medida</th><th>Exento</th><th>Bodega</th><th>Sección</th>
                                    <th>Unidades vendidas</th><th>Subtotal producto</th><th>ISV producto</th><th>Total producto</th>
                                    <th>Subtotal factura</th><th>ISV factura</th><th>Total factura</th>
                                </tr></thead>
                                <tbody></tbody>
                                <tfoot><tr>
                                    <th>Fecha de venta</th><th>Fecha de vencimiento</th><th>Vendedor</th><th>Factura</th><th>Cliente</th>
                                    <th>Tipo cliente</th><th>Crédito / contado</th><th>Código producto</th><th>Producto</th><th>Marca</th>
                                    <th>Categoría</th><th>Subcategoría</th><th>Unidad de medida</th><th>Exento</th><th>Bodega</th><th>Sección</th>
                                    <th>Unidades</th><th>Subtotal producto</th><th>ISV producto</th><th>Total producto</th>
                                    <th>Subtotal factura</th><th>ISV factura</th><th>Total factura</th>
                                </tr></tfoot>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="rep-panel">
                    <div class="rep-panel-header">
                        <h5 class="rep-panel-title"><i class="fa fa-cubes"></i>Catálogo de productos en existencia</h5>
                        <button type="button" class="rep-panel-action" onclick="cargaProductos()">
                            <i class="fa fa-refresh"></i>Cargar catálogo
                        </button>
                    </div>
                    <div class="rep-table-body">
                        <div class="rep-table-scroll">
                            <table id="tbl_productos" class="table table-bordered table-hover rep-table">
                                <thead><tr>
                                    <th>Código</th><th>Código de barra</th><th>Producto</th><th>Marca</th><th>ISV</th>
                                    <th>Categoría</th><th>Subcategoría</th><th>Existencia total</th><th>Precio base</th>
                                </tr></thead>
                                <tbody></tbody>
                                <tfoot><tr>
                                    <th>Código</th><th>Código de barra</th><th>Producto</th><th>Marca</th><th>ISV</th>
                                    <th>Categoría</th><th>Subcategoría</th><th>Existencia total</th><th>Precio base</th>
                                </tr></tfoot>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="rep-panel">
                    <div class="rep-panel-header">
                        <h5 class="rep-panel-title"><i class="fa fa-users"></i>Clientes activos e inactivos</h5>
                        <button type="button" class="rep-panel-action" onclick="cargaClientes()">
                            <i class="fa fa-refresh"></i>Cargar clientes
                        </button>
                    </div>
                    <div class="rep-table-body">
                        <div class="rep-table-scroll">
                            <table id="tbl_clientes" class="table table-bordered table-hover rep-table">
                                <thead><tr>
                                    <th>Código</th><th>Tipo cliente (A o B)</th><th>Estado</th><th>RTN</th><th>Cliente</th><th>Dirección</th>
                                    <th>Correo</th><th>Teléfono</th><th>País</th><th>Departamento</th><th>Municipio</th>
                                    <th>Contacto 1</th><th>Teléfono contacto 1</th><th>Contacto 2</th><th>Teléfono contacto 2</th><th>Vendedor</th><th>Registro</th>
                                </tr></thead>
                                <tbody></tbody>
                                <tfoot><tr>
                                    <th>Código</th><th>Tipo cliente</th><th>Estado</th><th>RTN</th><th>Cliente</th><th>Dirección</th>
                                    <th>Correo</th><th>Teléfono</th><th>País</th><th>Departamento</th><th>Municipio</th>
                                    <th>Contacto 1</th><th>Teléfono 1</th><th>Contacto 2</th><th>Teléfono 2</th><th>Vendedor</th><th>Registro</th>
                                </tr></tfoot>
                            </table>
                        </div>
                    </div>
                </section>
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
            "processing": true,
            "serverSide": true,
            "searchDelay": 500,
            "language": {
                "url": "//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"
            },
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [

                {
                    extend: 'excel',
                    title: 'VENTA_PRODUCTO_MARCA',
                    className:'btn btn-success',
                    exportOptions: {
                        modifier: {
                            page: 'all'
                        }
                    },
                    // Con "serverSide": true el botón de excel solo exporta la página
                    // actual (10 filas). Este action fuerza a traer TODAS las filas
                    // del servidor antes de exportar y luego restaura la paginación.
                    action: function (e, dt, button, config) {
                        var self = this;
                        var displayStart = dt.page.info().start;

                        dt.one('preXhr', function (e, settings, data) {
                            data.start = 0;
                            data.length = 2147483647;

                            dt.one('preDraw', function (e, settings) {
                                $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);

                                dt.one('preXhr', function (e, settings, data) {
                                    settings._iDisplayStart = displayStart;
                                    data.start = displayStart;
                                });

                                setTimeout(function () { dt.ajax.reload(); }, 0);
                                return false;
                            });
                        });

                        dt.ajax.reload();
                    }
                }
            ],
            "ajax": "/reporte/reporteria/consulta/"+fecha_inicio+"/"+fecha_final,
            "columns": [
                {data: 'FECHA DE VENTA'},
                {data: 'FECHA DE VENCIMIENTO'},
                {data: 'VENDEDOR'},
                {data: 'FACTURA'},
                {data: 'CLIENTE'},
                {data: 'TIPO CLIENTE (AoB)'},
                {data: 'TIPO CRÉDITO/CONTADO'},
                {data: 'CODIGO PRODUCTO'},
                {data: 'PRODUCTO'},
                {data: 'MARCA'},
                {data: 'CATEGORIA'},
                {data: 'SUB CATEGORIA'},
                {data: 'UNIDAD DE MEDIDA'},
                {data: 'EXCENTO'},
                {data: 'BODEGA'},
                {data: 'SECCION'},
                {data: 'UNIDADES VENDIDAS'},
                {data: 'SUBTOTAL PRODUCTO'},
                {data: 'ISV PRODUCTO'},
                {data: 'TOTAL PRODUCTO'},
                {data: 'SUB TOTAL FACTURA'},
                {data: 'ISV FACTURA'},
                {data: 'TOTAL FACTURA' }
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

    function cargaProductos(){

        $("#tbl_productos").dataTable().fnDestroy();


        $('#tbl_productos').DataTable({
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
                    title: 'CATALOGO_PRODUCTOS',
                    className:'btn btn-success'
                }
            ],
            "ajax": "/reporte/reporteria/productos",
            "columns": [
                {data: 'CODIGO'},
                {data: 'CODIGO DE BARRA'},
                {data: 'PRODUCTO'},
                {data: 'MARCA'},
                {data: 'ISV'},
                {data: 'CATEGORIA'},
                {data: 'SUB CATEGORIA'},
                {data: 'EXISTENCIA TOTAL'},
                {data: 'PRECIO BASE'}
            ],initComplete: function () {
                var r = $('#tbl_productos tfoot tr');
                r.find('th').each(function(){
                  $(this).css('padding', 8);
                });
                $('#tbl_productos thead').append(r);
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

    function cargaClientes(){

        $("#tbl_clientes").dataTable().fnDestroy();


        $('#tbl_clientes').DataTable({
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
                    title: 'CLIENTES_ACTIVOS',
                    className:'btn btn-success'
                }
            ],
            "ajax": "/reporte/reporteria/clientes",
            "columns": [

                {data: 'CODIGO'},
                {data: 'TIPO'},
                {data: 'ESTADO'},
                {data: 'RTN'},
                {data: 'NOMBRE'},
                {data: 'DIRECCION'},
                {data: 'CORREO'},
                {data: 'TELEFONO'},
                {data: 'PAIS'},
                {data: 'DEPARTAMENTO'},
                {data: 'MUNICIPIO'},
                {data: 'NCONT1'},
                {data: 'TCONT1'},
                {data: 'NCONT2'},
                {data: 'TCONT2'},
                {data: 'VENDEDOR'},
                {data: 'REGISTRO'}
            ],initComplete: function () {
                var r = $('#tbl_clientes tfoot tr');
                r.find('th').each(function(){
                  $(this).css('padding', 8);
                });
                $('#tbl_clientes thead').append(r);
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


