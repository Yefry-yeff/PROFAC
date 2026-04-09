<div>
    @push('styles')
        <style>
            .tipo-selector .btn { margin: 2px 4px; }
            .tipo-selector .btn.active { box-shadow: 0 0 0 3px rgba(0,123,255,.5); }
        </style>
    @endpush

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
                            @if($esVendedor)
                                <button type="button" class="btn btn-sm {{ $tipoVenta == 'corporativo' ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                    onclick="window.location.href='/facturas/corporativo/vendedor'">Clientes B</button>
                                <button type="button" class="btn btn-sm {{ $tipoVenta == 'estatal' ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                    onclick="window.location.href='/ventas/estatal/vendedor'">Clientes A</button>
                            @else
                                <button type="button" class="btn btn-sm {{ $tipoVenta == 'corporativo' ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                    onclick="window.location.href='/facturas/corporativo'">Clientes B</button>
                                <button type="button" class="btn btn-sm {{ $tipoVenta == 'estatal' ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                    onclick="window.location.href='/facturas/estatal'">Clientes A</button>
                            @endif
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
                }
            };

            var tipoVenta = @json($tipoVenta);
            var esVendedor = @json($esVendedor);
            var config = configPorTipo[tipoVenta] || configPorTipo['corporativo'];

            // Columnas dinámicas según tipo y rol
            var columnas = [];

            if (esVendedor) {
                columnas.push({ data: 'id' });
            }

            columnas.push({ data: 'numero_factura' });

            if (!esVendedor && tipoVenta === 'corporativo') {
                columnas.push({ data: 'correlativo' });
                columnas.push({ data: 'cai' });
            } else if (!esVendedor) {
                columnas.push({ data: 'cai' });
            }

            columnas.push({ data: 'fecha_emision' });
            columnas.push({ data: 'nombre' });
            columnas.push({ data: 'descripcion' });
            columnas.push({ data: 'fecha_vencimiento' });
            columnas.push({ data: 'sub_total' });
            columnas.push({ data: 'isv' });
            columnas.push({ data: 'total' });
            columnas.push({ data: 'estado_cobro' });

            if (esVendedor) {
                columnas.push({ data: 'creado_por' });
                if (tipoVenta === 'corporativo') {
                    columnas.push({ data: 'fecha_registro' });
                }
            } else {
                columnas.push({ data: 'vendedor' });
                columnas.push({ data: 'facturador' });
                columnas.push({ data: 'fecha_registro' });
            }

            columnas.push({ data: 'opciones' });

            // Calcular índice de columna para ordenamiento (fecha_registro o última columna)
            var orderCol = columnas.length - 2;

            $(document).ready(function() {
                var ajaxUrl = esVendedor ? config.listarVendedor : config.listar;

                $('#tbl_listar_compras').DataTable({
                    "language": {
                        "url": "/js/plugins/dataTables/i18n/Spanish.json"
                    },
                    "order": [orderCol, 'desc'],
                    pageLength: 10,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [
                        {
                            extend: 'excel',
                            title: config.excelTitle,
                            className: 'btn btn-success'
                        }
                    ],
                    "ajax": ajaxUrl,
                    "columns": columnas
                });
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
