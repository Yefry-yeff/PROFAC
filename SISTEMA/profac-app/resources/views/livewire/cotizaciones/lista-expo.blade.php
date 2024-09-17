<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Cotizaciones Expor Ventas</h2>
            <ol class="breadcrumb">

                @switch(  $idTipoVenta )
                @case(1)
                    <li class="breadcrumb-item active">
                        <a>Coorporativo</a>
                    </li>
                    @break
                @case(2)
                    <li class="breadcrumb-item active">
                        <a>Gobierno</a>
                    </li>
                    @break
                @case(3)
                    <li class="breadcrumb-item active">
                        <a>Exonerado</a>
                    </li>
                    @break
                    @case(4)
                    <li class="breadcrumb-item active">
                        <a>Expo</a>
                    </li>
                    @break
                @endswitch



                <li class="breadcrumb-item">
                    <a>Imprimir Cotización</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Imprimir Factura</a>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_listar_cotizaciones" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Registrado por:</th>
                                        <th>Fecha de registro:</th>
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
              //var varToken = {{ csrf_token() }};
              var idTipoVenta = {{$idTipoVenta}};

            $(document).ready(function() {
            $('#tbl_listar_cotizaciones').DataTable({
                "order": [7, 'desc'],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },

                pageLength: 10,
                responsive: true,


                "ajax":{
                    'url':"/cotizacion/obtener/listado/expo",
                    'data' : {'id' : idTipoVenta },
                    'type' : 'post',
                    'headers': {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }


                     },
                "columns": [

                    {
                        data: 'codigo'
                    },
                    {
                        data: 'nombre_cliente'
                    },
                    {
                        data: 'RTN'
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
                        data: 'name'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'opciones'
                    },



                ]


            });
            })

        function anularVentaConfirmar(idFactura){

            Swal.fire({
            title: '¿Está seguro de anular esta factura?',
            text:'Una vez que ha sido anulada la factura el producto registrado en la misma sera devuelto al inventario.',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Si, Anular Compra',
            cancelButtonText: `Cancelar`,
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {

                //Swal.fire('Saved!', '', 'success')
                anularVenta(idFactura);

            }
            })
        }

        function anularVenta(idFactura){

            axios.post("/factura/corporativo/anular", {idFactura:idFactura})
            .then( response =>{


                let data = response.data;
                Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,
                        });
                        $('#tbl_listar_compras').DataTable().ajax.reload();

            })
            .catch( err => {

                Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Ha ocurrido un error al anular la compra.',
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
