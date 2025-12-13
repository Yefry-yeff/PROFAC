<div>
    @push('styles')
        <link href="{{ asset('css/plugins/switchery/switchery.css') }}" rel="stylesheet">

        <style>
            .apexcharts-canvas: {
                background: '#6C4034'
            }

            .espacio-inputs {}

            @media only screen and (min-width: 600px) {
                .espacio-inputs {}
            }
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Listado de facturación</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a>Cotejamiento </a>
                        </li>


                    </ol>
                </div>


            </div>


        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Seleccionar rango de Fechas</h3>
                    </div>
                    <div class="ibox-content ">

                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                <label for="_venta" class="col-form-label focus-label">Fecha de inicio</label>
                                <input id="fechaInicio" type="date" value="{{ date('Y-01-01') }}" class="form-group form-control">
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                <label for="_venta" class="col-form-label focus-label">Fecha Final</label>
                                <input id="fechaFinal" type="date" value="{{ date('Y-m-t') }}" class="form-group form-control">
                            </div>


                        </div>
                        <div>
                            <button onclick="facturasPorfecha()" class="btn btn-primary mt-3"><i
                                    class="fa-solid fa-arrow-rotate-right"></i> Solicitar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-cart-shopping"></i> Ventas D/C</h3>
                    </div>
                    <div class="ibox-content ">
                        <div class="table-responsive">
                            <table id="tbl_listar_ventas_uno" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod CAI</th>
                                        <th>CAI</th>
                                        <th>N° Factura</th>
                                        <th>Correlativo</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV Lps</th>
                                        <th>Total Lps.</th>
                                        <th>Fecha Emision.</th>
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

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-cart-shopping"></i> Ventas N/D</h3>
                    </div>
                    <div class="ibox-content ">
                        <div class="table-responsive">
                            <table id="tbl_listar_ventas_dos" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod CAI</th>
                                        <th>CAI</th>
                                        <th>N° Factura</th>
                                        <th>Correlativo</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV Lps</th>
                                        <th>Total Lps.</th>
                                        <th>Fecha Emision.</th>
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
                var fechaInicio = "{{ date('Y-01-01') }}";
                var fechaFinal = "{{ date('Y-m-t') }}";

            $(document).ready(function() {

                tablas();
            })

            function tablas()
            {

                $('#tbl_listar_ventas_uno').DataTable({

                    "order": [1, 'desc'],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },

                    pageLength: 10,
                    responsive: true,



                    'ajax': {
                        'url': "/ventas/listado/uno",
                        'data': {
                            'fechaInicio': fechaInicio,
                            'fechaFinal': fechaFinal,
                            "_token": "{{ csrf_token() }}"
                        },
                        'type': 'post'
                    },
                    "columns": [
                        {
                            data: 'cod_cai'
                        },
                        {
                            data: 'cai'
                        },

                        {
                            data: 'correlativo'
                        },
                        {
                            data: 'numero_secuencia_cai'
                        },
                        {
                            data: 'nombre_cliente'
                        },
                        {
                            data: 'rtn'
                        },
                        {
                            data: 'sub_total'
                        },
                        {
                            data: 'isv'
                        },
                        {
                            data: 'total'
                        },
                        {
                            data: 'fecha_emision'
                        },
                        {
                            data: 'opciones'
                        },


                    ]


                });


                $('#tbl_listar_ventas_dos').DataTable({
                    "order": [1, 'desc'],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },

                    pageLength: 10,
                    responsive: true,

                    'ajax': {
                        'url': '/ventas/listado/dos',
                        'data': {
                            'fechaInicio': fechaInicio,
                            'fechaFinal': fechaFinal,
                            "_token": "{{ csrf_token() }}"
                        },
                        'type': 'post'
                    },
                    "columns": [
                        {
                            data: 'cod_cai'
                        },

                        {
                            data: 'cai'
                        },

                        {
                            data: 'correlativo'
                        },
                        {
                            data: 'numero_secuencia_cai'
                        },
                        {
                            data: 'nombre_cliente'
                        },
                        {
                            data: 'rtn'
                        },
                        {
                            data: 'sub_total'
                        },
                        {
                            data: 'isv'
                        },
                        {
                            data: 'total'
                        },
                        {
                            data: 'fecha_emision'
                        },
                        {
                            data: 'opciones'
                        },


                    ]


                });

            }


            function facturasPorfecha(){
                let inicio = document.getElementById('fechaInicio').value;
                let final = document.getElementById('fechaFinal').value;

                console.log("entro")

                fechaInicio = inicio;
                fechaFinal = final;

                $('#tbl_listar_ventas_uno').DataTable().clear().destroy();
                $('#tbl_listar_ventas_dos').DataTable().clear().destroy();

                this.tablas();

                //$('#tbl_listar_ventas_uno').DataTable().ajax.reload();
                //$('#tbl_listar_ventas_dos').DataTable().ajax.reload();
            }

            function modalTransladoND(idFactura){

                Swal.fire({
                title: 'Está suguro(a) de realizar este cambio?',

                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                confirmButtonColor:'#19A689',
                cancelButtonText: `Cancelar`,
                }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    actualizarEstadoND(idFactura);
                }
                })
            }

            function actualizarEstadoND(idFactura){
                axios.get("/ventas/estado/nd/"+idFactura)
                .then( response=>{
                    let data = response.data;

                    Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,

                    })

                    $('#tbl_listar_ventas_uno').DataTable().ajax.reload();
                    $('#tbl_listar_ventas_dos').DataTable().ajax.reload();

                }).catch( err=>{
                    let data = err.response.data;
                    Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,

                    })
                })
            }

            function modalTransladoDC(idFactura){

                 Swal.fire({
                 title: 'Está suguro(a) de realizar este cambio?',

                 showCancelButton: true,
                 confirmButtonText: 'Confirmar',
                 confirmButtonColor:'#19A689',
                 cancelButtonText: `Cancelar`,
                 }).then((result) => {
                 /* Read more about isConfirmed, isDenied below */
                 if (result.isConfirmed) {
                    actualizarEstadoDC(idFactura);
                 }
                 })
             }

             function actualizarEstadoDC(idFactura){
                axios.get("/ventas/estado/dc/"+idFactura)
                .then( response=>{
                    let data = response.data;

                    Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,

                    })

                    $('#tbl_listar_ventas_uno').DataTable().ajax.reload();
                    $('#tbl_listar_ventas_dos').DataTable().ajax.reload();

                }).catch( err=>{
                    let data = err.response.data;
                    Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,

                    })
                })
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
