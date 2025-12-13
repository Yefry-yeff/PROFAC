<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Cuentas por Cobrar</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Listar</a>
                </li>


            </ol>
        </div>

    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <label for="cliente" class="col-form-label focus-label">Seleccionar Cliente:<span class="text-danger">*</span></label>
                                <select id="cliente" name="cliente" class="form-group form-control" style=""
                                    data-parsley-required >
                                    <option value="" selected disabled>--Seleccionar un Cliente--</option>
                                </select>
                            </div>

                            <div class="col-6 col-sm-6 col-md-6 " id="btnEC" name="btnEC" style="display: none">
                                <label for="cliente" class="alert alert-warning"> <b>Estado de cuenta</b> <span class="text-danger"></span></label>
                                <button class="btn btn-primary btn-block" onclick="pdfEstadoCuenta()"><i class="fa-solid fa-paper-plane text-white"></i> Visualizar </button>
                            </div>



                        </div>
                        <button class="btn btn-primary mt-2" onclick=" llamarTablas(), mostrarExports()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="wrapper wrapper-content animated fadeInRight">
        {{--  <div class="mb-2"  id="cuentas_excel">
            <!-- <a href="/ventas/cuentas_por_cobrar/excel_cuentas" class="btn-seconary"><i class="fa fa-plus"></i> Exportar Excel Cuentas Por Cobrar</a> -->
        </div>  --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_cuentas_por_cobrar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>No. Factura</th>
                                        <th>Orden de Compra</th>
                                        <th>Cliente</th>
                                        <th>Fecha Emision</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Cargo</th>
                                        <th>Credito</th>
                                        <th>Notas Crédito</th>
                                        <th>Notas Débito</th>
                                        <th>Saldo</th>
                                        <th>Acumulado</th>
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
        <div class="mb-2" id="cuentas_excel_intereses">
            <!-- <a href="/ventas/cuentas_por_cobrar/excel_intereses" class="btn-seconary"><i class="fa fa-plus"></i>Excel Cuentas Por Cobrar Intereses</a> -->
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_cuentas_por_cobrar_intereses" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Numero Factura</th>
                                        <th>Correlativo</th>
                                        <th>ID Cliente</th>
                                        <th>Cliente</th>
                                        <th>Documento</th>
                                        <th>Fecha Emision</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Cargo</th>
                                        <th>Abonos</th>
                                        <th>Dias</th>
                                        <th>Interes Inicia</th>
                                        <th>Interes Diario</th>
                                        <th>Acumulado</th>

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




</div>
@push('scripts')
<script>
        $('#btnEC').css('display','none');
        $('#btnEC').hide();
        {{--  $('#divcierre').hide();  --}}
        {{--  $('#btn_cierreCaja').css('display','none');  --}}
        {{--  $('#btn_cierreCaja').hide();  --}}

        $('#cliente').select2({
            ajax: {
                url: '/ventas/cuentas_por_cobrar/clientes',
                data: function(params) {
                    var query = {
                        search: params.term,
                        type: 'public',
                        page: params.page || 1
                    }


                    return query;
                }
            }
        });

    function llamarTablas(){

        $("#tbl_cuentas_por_cobrar").dataTable().fnDestroy();
        $("#tbl_cuentas_por_cobrar_intereses").dataTable().fnDestroy();

        this.listarCuentasPorCobrar();
        this.listarCuentasPorCobrarInteres();

    }

    function pdfEstadoCuenta(){

        var idClientepdf = document.getElementById('cliente').value;
        window.open('/estadoCuenta/imprimir/'+idClientepdf, '_blank');
    }

    function listarCuentasPorCobrar() {

        var idCliente = document.getElementById('cliente').value;
        $('#tbl_cuentas_por_cobrar').DataTable({
                    "order": [0, 'desc'],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    pageLength: 10,
                    responsive: true,
                    dom: "Bfrtip",
                    buttons: [
                        {
                            extend: 'excel',
                            title: 'ESTADO-CUENTA-'+idCliente,
                            className: 'btn btn-success',
                            excelStyles: {
                                cells: "1",
                                style: {
                                    font:{
                                        name:'Arial',
                                        size: "16",
                                        color: 'FFFFFF',
                                        b: true
                                    },
                                    fill:{
                                        pattern: {
                                            color: 'ff7961'
                                        }
                                    }
                                }
                            }
                        },

                        {
                            extend: 'print',
                            customize: function(win) {
                                $(win.document.body).addClass('white-bg');
                                $(win.document.body).css('font-size', '10px');

                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                            }
                        }
                    ],
                    "ajax": "/ventas/cuentas_por_cobrar/listar/"+idCliente,
                    "columns": [

                        {
                            data: 'correlativo'
                        },
                        {
                            data: 'numOrden'
                        },
                        {
                            data: 'cliente'
                        },
                        {
                            data: 'fecha_emision'
                        },
                        {
                            data: 'fecha_vencimiento'
                        },
                        {
                            data: 'cargo'
                        },
                        {
                            data: 'credito'
                        },
                        {
                            data: 'notaCredito'
                        },
                        {
                            data: 'notaDebito'
                        },
                        {
                            data: 'saldo'
                        },
                        {
                            data: 'Acumulado'
                        },
                        {
                            data: 'opciones'
                        }


                    ]


                });
                $('#btnEC').css('display','block');
                $('#btnEC').show();
    }


    //////////////////////////////////////////////////////////////////////////////////

    function listarCuentasPorCobrarInteres() {

        var idCliente = document.getElementById('cliente').value;
        $('#tbl_cuentas_por_cobrar_intereses').DataTable({
                    "order": [0, 'desc'],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    pageLength: 10,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [{
                            extend: 'copy'
                        },
                        {
                            extend: 'csv'
                        },
                        {
                            extend: 'excel',
                            title: 'ExampleFile'
                        },
                        {
                            extend: 'pdf',
                            title: 'ExampleFile'
                        },

                        {
                            extend: 'print',
                            customize: function(win) {
                                $(win.document.body).addClass('white-bg');
                                $(win.document.body).css('font-size', '10px');

                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                            }
                        }
                    ],
                    "ajax": "/ventas/cuentas_por_cobrar/listar_intereses/"+idCliente,
                    "columns": [
                        {
                            data: 'numero_factura'
                        },
                        {
                            data: 'correlativo'
                        },
                        {
                            data: 'id_cliente'
                        },
                        {
                            data: 'cliente'
                        },
                        {
                            data: 'documento'
                        },
                        {
                            data: 'fecha_emision'
                        },
                        {
                            data: 'fecha_vencimiento'
                        },
                        {
                            data: 'cargo'
                        },
                        {
                            data: 'abonos'
                        },
                        {
                            data: 'dias'
                        },
                        {
                            data: 'interesInicia'
                        },
                        {
                            data: 'interesDiario'
                        },
                        {
                            data: 'acumulado'
                        }
                    ]


                });
    }

    //////////////////////////////////////////////////////////////////////////////////

    function mostrarExports() {

        var cliente = document.getElementById('cliente').value;

                let htmlSelect1 = ''

                htmlSelect1 =   `
                        <a href="/ventas/cuentas_por_cobrar/excel_cuentas/${cliente}" class="btn btn-primary"><i class="fa fa-plus"></i> Exportar Excel Cuentas Por Cobrar</a>

                                `

                document.getElementById('cuentas_excel').innerHTML = htmlSelect1;

                //////////////////////////////////////////////////////////////////////////////
                let htmlSelect2 = ''

                htmlSelect2 =   `
                        <a href="/ventas/cuentas_por_cobrar/excel_intereses/${cliente}" class="btn btn-primary"><i class="fa fa-plus"></i>Excel Cuentas Por Cobrar Intereses</a>

                                `

                document.getElementById('cuentas_excel_intereses').innerHTML = htmlSelect2;

    }


    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

@endpush
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
        var fecha = new Date(); // Obtener la fecha y hora actual
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();

        // A単adir un 0 delante si los minutos o segundos son menores a 10
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;

        // Mostrar la hora actual en el elemento con el id "reloj"
        document.getElementById("reloj").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    // Actualizar el reloj cada segundo
    setInterval(mostrarHora, 1000);
</script>
<div class="float-right">
    <?php echo "$act_fecha";  ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year";  ?>
</div>
<p id="reloj"></p>
