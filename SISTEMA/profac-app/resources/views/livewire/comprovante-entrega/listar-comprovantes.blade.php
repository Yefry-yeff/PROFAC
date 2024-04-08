<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2> Comprobantes De Entrega </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a>Activos</a>
                </li>
                <li class="breadcrumb-item active">
                    <a>Listado</a>
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
                            <table id="tbl_listar_comprobantes" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>

                                        <th>N° Comprobante</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Fecha de Emision</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV en Lps.</th>
                                        <th>Total en Lps.</th>
                                        <th>Estado</th>
                                        <th>Registrado Por:</th>
                                        <th>Fecha de registro</th>
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
            $(document).ready(function() {
            $('#tbl_listar_comprobantes').DataTable({
                "order": [3, 'desc'],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "order": [9, 'desc'],
                pageLength: 10,
                responsive: true,


                "ajax": "/comprovante/entrega/listado/activos",
                "columns": [
                    {
                        data: 'numero_comprovante'
                    },
                    {
                        data: 'nombre_cliente'
                    },
                    {
                        data: 'RTN'
                    },
                    {
                        data: 'fecha_emision'
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
                        data:'estado'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data:'fecha_creacion'
                    },

                    {
                        data: 'opciones'
                    }

                ]


            });
            })


            function anularComprobanteConfirmar(idComprobante){

                Swal.fire({
                title: '¿Está seguro de anular este comprobante?',


                    // --------------^-- define html element with id
                html: ' <textarea rows="4" placeholder="Es obligatorio describir el motivo." required id="comentarion"     class="form-group form-control" data-parsley-required></textarea>',
                showDenyButton: false,
                showCancelButton: false,
                showDenyButton:true,
                confirmButtonText: 'Si, Anular Comprobante',
                denyButtonText: `Cancelar`,
                confirmButtonColor:'#19A689',
                denyButtonColor:'#676A6C',
                }).then((result) => {

                    let motivo = document.getElementById("comentarion").value

                if (result.isConfirmed && motivo ) {


                    anularComprobante(idComprobante,motivo);

                }else if(result.isDenied){
                    Swal.close()
                }else{
                    Swal.close()
                }
                })
            }

        function anularComprobante(idComprobante,motivo){
                   axios.post('/comprobante/entrega/anular', {'idComprobante':idComprobante,'motivo':motivo})
                   .then(response=>{
                    let data = response.data;
                        Swal.fire({
                                    icon: data.icon,
                                    title: data.title,
                                    html: data.text,
                                });
                                $('#tbl_listar_comprobantes').DataTable().ajax.reload();
                   })
                   .catch(err=>{
                    Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Ha ocurrido un error al anular el comprobante.',
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
