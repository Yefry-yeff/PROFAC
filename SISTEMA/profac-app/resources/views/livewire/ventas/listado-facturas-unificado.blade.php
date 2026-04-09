<div>
    @push('styles')
        <style>
            .tipo-selector .btn { margin: 2px 4px; }
            .tipo-selector .btn.active { box-shadow: 0 0 0 3px rgba(0,123,255,.5); }
        </style>
    @endpush

    {{-- Loading Overlay --}}
    <div id="tbl_loading_overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
        <p class="mt-3" style="color:#555; font-size:1rem;">Cargando datos...</p>
    </div>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Facturas </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a>{{ $nombreTipo }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Detalle de factura</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Entregas Programadas</a>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- SELECTOR DE TIPO --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="ibox">
                    <div class="ibox-content py-2">
                        <div class="d-flex align-items-center flex-wrap tipo-selector">
                            <strong class="mr-3">Tipo:</strong>
                            <button type="button" class="btn btn-sm {{ $tipoVenta == 'estatal' ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                onclick="cambiarTipo('estatal', this)">Clientes A</button>
                            <button type="button" class="btn btn-sm {{ $tipoVenta == 'corporativo' ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                onclick="cambiarTipo('corporativo', this)">Clientes B</button>
                            <button type="button" class="btn btn-sm {{ $tipoVenta == 'exonerado' ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                onclick="cambiarTipo('exonerado', this)">Exoneradas</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_listar_compras" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        @if($esVendedor)
                                            <th>Codigo Interno</th>
                                        @endif
                                        <th>N° Factura</th>
                                        @if(!$esVendedor && $tipoVenta == 'corporativo')
                                            <th>Correlativo</th>
                                            <th>CAI</th>
                                        @elseif(!$esVendedor)
                                            <th>CAI</th>
                                        @endif
                                        <th>Fecha de Emision</th>
                                        <th>Cliente</th>
                                        <th>Tipo de Pago</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV en Lps.</th>
                                        <th>Total en Lps.</th>
                                        <th>Esto de Cobro</th>
                                        <th>Vendedor</th>
                                        @if(!$esVendedor)
                                            <th>Facturador</th>
                                            <th>Fecha Registro</th>
                                        @else
                                            @if($tipoVenta == 'corporativo')
                                                <th>Fecha Registro</th>
                                            @endif
                                        @endif
                                        <th>Opciones</th>
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
        <script>
            // Configuración por tipo de venta
            var configPorTipo = {
                corporativo: {
                    listar: '/lista/facturas/corporativo',
                    listarVendedor: '/lista/facturas/corporativo/vendedor',
                    anular: '/factura/corporativo/anular',
                    excelTitle: 'Facturas_corporativo'
                },
                estatal: {
                    listar: '/lista/facturas/estatal',
                    listarVendedor: '/listado/ventas/estatal/vendedor',
                    anular: '/factura/estatal/anular',
                    excelTitle: 'Facturas_estatal'
                },
                exonerado: {
                    listar: '/exonerado/listas/facturas',
                    listarVendedor: '/exonerado/listas/facturas',
                    anular: '/factura/corporativo/anular',
                    excelTitle: 'Facturas_exonerado'
                }
            };

            var tipoVenta = @json($tipoVenta);
            var esVendedor = @json($esVendedor);
            var config = configPorTipo[tipoVenta] || configPorTipo['corporativo'];

            var nombresTipo = { corporativo: 'Clientes B', estatal: 'Clientes A', exonerado: 'Clientes Exonerado' };
            var urlHistoryAdmin = { corporativo: '/facturas/corporativo', estatal: '/facturas/estatal', exonerado: '/exonerado/ventas/lista' };
            var urlHistoryVendedor = { corporativo: '/facturas/corporativo/vendedor', estatal: '/ventas/estatal/vendedor', exonerado: '/exonerado/ventas/lista' };

            function buildColumnas(tipo, esVend) {
                var cols = [];
                if (esVend) cols.push({ data: 'id' });
                cols.push({ data: 'numero_factura' });
                if (!esVend && tipo === 'corporativo') {
                    cols.push({ data: 'correlativo' });
                    cols.push({ data: 'cai' });
                } else if (!esVend) {
                    cols.push({ data: 'cai' });
                }
                cols.push({ data: 'fecha_emision' });
                cols.push({ data: 'nombre' });
                cols.push({ data: 'descripcion' });
                cols.push({ data: 'fecha_vencimiento' });
                cols.push({ data: 'sub_total' });
                cols.push({ data: 'isv' });
                cols.push({ data: 'total' });
                cols.push({ data: 'estado_cobro' });
                if (esVend) {
                    cols.push({ data: 'creado_por' });
                    if (tipo === 'corporativo') cols.push({ data: 'fecha_registro' });
                } else {
                    cols.push({ data: 'vendedor' });
                    cols.push({ data: 'facturador' });
                    cols.push({ data: 'fecha_registro' });
                }
                cols.push({ data: 'opciones' });
                return cols;
            }

            function buildTheadHtml(tipo, esVend) {
                var headers = [];
                if (esVend) headers.push('Codigo Interno');
                headers.push('N° Factura');
                if (!esVend && tipo === 'corporativo') {
                    headers.push('Correlativo');
                    headers.push('CAI');
                } else if (!esVend) {
                    headers.push('CAI');
                }
                headers.push('Fecha de Emision', 'Cliente', 'Tipo de Pago', 'Fecha de Vencimiento',
                             'Sub Total Lps.', 'ISV en Lps.', 'Total en Lps.', 'Esto de Cobro');
                if (esVend) {
                    headers.push('Vendedor');
                    if (tipo === 'corporativo') headers.push('Fecha Registro');
                } else {
                    headers.push('Vendedor', 'Facturador', 'Fecha Registro');
                }
                headers.push('Opciones');
                var html = '<tr>';
                headers.forEach(function(h) { html += '<th>' + h + '</th>'; });
                html += '</tr>';
                return html;
            }

            function initDataTable(tipo, esVend) {
                var currentConfig = configPorTipo[tipo] || configPorTipo['corporativo'];
                var columnas = buildColumnas(tipo, esVend);
                var ajaxUrl = esVend ? currentConfig.listarVendedor : currentConfig.listar;
                var orderCol = columnas.length - 2;
                return $('#tbl_listar_compras').DataTable({
                    "language": { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
                    "order": [orderCol, 'desc'],
                    pageLength: 5,
                    "processing": true,
                    "serverSide": true,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [{ extend: 'excel', title: currentConfig.excelTitle, className: 'btn btn-success' }],
                    "ajax": ajaxUrl,
                    "columns": columnas,
                    "initComplete": function() {
                        document.getElementById('tbl_loading_overlay').style.display = 'none';
                    }
                });
            }

            function cambiarTipo(nuevoTipo, btnElement) {
                if (nuevoTipo === tipoVenta) return;
                document.getElementById('tbl_loading_overlay').style.display = '';
                document.querySelectorAll('.tipo-selector .btn').forEach(function(btn) {
                    btn.classList.remove('btn-primary', 'active');
                    btn.classList.add('btn-outline-secondary');
                });
                btnElement.classList.remove('btn-outline-secondary');
                btnElement.classList.add('btn-primary', 'active');
                tipoVenta = nuevoTipo;
                config = configPorTipo[nuevoTipo] || configPorTipo['corporativo'];
                document.querySelector('.breadcrumb-item.active a').textContent = nombresTipo[nuevoTipo];
                var historyUrls = esVendedor ? urlHistoryVendedor : urlHistoryAdmin;
                history.pushState({ tipo: nuevoTipo }, '', historyUrls[nuevoTipo]);
                if ($.fn.DataTable.isDataTable('#tbl_listar_compras')) {
                    $('#tbl_listar_compras').DataTable().destroy();
                    $('#tbl_listar_compras tbody').empty();
                }
                $('#tbl_listar_compras thead').html(buildTheadHtml(nuevoTipo, esVendedor));
                initDataTable(nuevoTipo, esVendedor);
            }

            $(document).ready(function() {
                initDataTable(tipoVenta, esVendedor);
            });

            function anularVentaConfirmar(idFactura) {
                Swal.fire({
                    title: '¿Está seguro de anular esta factura?',
                    html: '<p>Una vez que ha sido anulada la factura el producto registrado en la misma sera devuelto al inventario.</p> <textarea rows="4" placeholder="Es obligatorio describir el motivo." required id="comentario" class="form-group form-control" data-parsley-required></textarea>',
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: 'Si, Anular Factura',
                    denyButtonText: 'Cancelar',
                    confirmButtonColor: '#19A689',
                    denyButtonColor: '#676A6C',
                }).then((result) => {
                    let motivo = document.getElementById("comentario").value;
                    if (result.isConfirmed && motivo) {
                        anularVenta(idFactura, motivo);
                    }
                });
            }

            function anularVenta(idFactura, motivo) {
                axios.post(config.anular, { 'idFactura': idFactura, 'motivo': motivo })
                    .then(response => {
                        let data = response.data;
                        Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                        $('#tbl_listar_compras').DataTable().ajax.reload();
                    })
                    .catch(err => {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Ha ocurrido un error al anular la compra.' });
                    });
            }
        </script>
    @endpush
</div>

<?php
    date_default_timezone_set('America/Tegucigalpa');
    $act_fecha=date("Y-m-d");
    $act_hora=date("H:i:s");
    $mes=date("m");
    $year=date("Y");
    $datetim=$act_fecha." ".$act_hora;
?>
<script>
    function mostrarHora() {
        var fecha = new Date();
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();
        hora = (hora < 10) ? "0" + hora : hora;
        minutos = (minutos < 10) ? "0" + minutos : minutos;
        segundos = (segundos < 10) ? "0" + segundos : segundos;
        document.getElementById("hora").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    setInterval(mostrarHora, 1000);
</script>
