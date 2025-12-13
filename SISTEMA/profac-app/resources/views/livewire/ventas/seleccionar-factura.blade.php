<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Facturas </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    {{--  <a>Clientes A</a>  --}}
                    <a>Clientes B</a>
                </li>

                <li class="breadcrumb-item">
                    {{--  <a>Estatal</a>  --}}
                </li>

            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div>

                            <button id="btn_mayor" type="button" class="btn btn-w-m btn-warning "
                                onclick="seleccionarMayor()">Seleccionar monto mayor</button>
                            <button id="btn_menor" type="button" class="btn btn-w-m btn-success ml-4 "
                                onclick="seleccionarMenor()">Seleccionar
                                monto menor</button>
                        </div>
                        <div class="table-responsive">
                            <table id="tbl_lista_ventas" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod CAI</th>
                                        <th>CAI</th>
                                        <th>N° Factura</th>
                                        <th>D/C</th>
                                        <th>N/D</th>
                                        <th>Opciones</th>


                                    </tr>
                                </thead>
                                <tbody>


                                </tbody>
                            </table>

                        </div>
                        <div>
                            <button id="btn_guardar" type="button" onclick="confirmarGuardar()"
                                class="btn btn-w-m btn-primary">Guardar</button>
                            <p class="mt-2"><strong>Nota:</strong>Una vez se guarden los cambios efectuados, estos ya
                                no se podrán modificar de ninguna forma posible.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#tbl_lista_ventas').DataTable({
                    "order": [0, 'asc'],
                    "paging": false,
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },

                    pageLength: 10,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [

                        {
                            extend: 'copy'
                        },
                        {
                            extend: 'excel',
                            title: 'Listado de declaraciones '
                        },



                    ],


                    "ajax": "/ventas/lista/seleccionar",
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
                            data: 'DC'
                        },
                        {
                            data: 'ND'
                        },
                        {
                            data: 'opciones'
                        }


                    ]


                });
            })

            function modalTranslado(cai, caidID) {

                Swal.fire({
                    title: 'Está suguro(a) de realizar este cambio?',

                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    confirmButtonColor: '#19A689',
                    cancelButtonText: `Cancelar`,
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        actualizarEstado(cai,caidID);
                    }
                })
            }

            function actualizarEstado(cai, caidID) {
                let data = {
                    cai: cai,
                    caidID: caidID
                };
                axios.post('/ventas/cambio', data)
                    .then(response => {
                        let data = response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,

                        })

                        $('#tbl_lista_ventas').DataTable().ajax.reload();
                    })
                    .catch(err => {

                        let data = err.response.data;
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })
                    })



            }

            function guardarEstado() {

                document.getElementById('btn_guardar').disabled = true;

                var table = $('#tbl_lista_ventas').DataTable();

                var plainArray = table.column(2).data().toArray();
                var plainArray2 = table.column(0).data().toArray();


                const formData = new FormData();


                for (let i = 0; i < plainArray.length; i++) {

                    formData.append('arregloCAI[]', plainArray[i]);
                    formData.append('arregloCAI_ID[]', plainArray2[i]);
                }

                axios.post('/ventas/bloquear/estado', formData)
                    .then(response => {
                        let data = response.data;


                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })

                        $('#tbl_lista_ventas').DataTable().ajax.reload();

                        document.getElementById('btn_guardar').disabled = false;
                    })
                    .catch(err => {
                        document.getElementById('btn_guardar').disabled = false;
                        let data = err.response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })

                    })

            }

            function seleccionarMayor() {

                document.getElementById('btn_mayor').disabled = false;

                var table = $('#tbl_lista_ventas').DataTable();
                var plainArray = table.column(2).data().toArray();
                var plainArray2 = table.column(0).data().toArray();


                const formData = new FormData();


                for (let i = 0; i < plainArray.length; i++) {
                    formData.append('arregloCAI[]', plainArray[i]);
                    formData.append('arregloCAIID[]', plainArray2[i]);
                }

                axios.post('/ventas/seleccionar/mayor', formData)
                    .then(response => {

                        let data = response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })

                        $('#tbl_lista_ventas').DataTable().ajax.reload();

                        document.getElementById('btn_mayor').disabled = false;
                    })
                    .catch(err => {
                        document.getElementById('btn_mayor').disabled = false;

                        let data = err.response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })

                    })




            }



            function seleccionarMenor() {

                document.getElementById('btn_menor').disabled = false;

                var table = $('#tbl_lista_ventas').DataTable();
                var plainArray = table.column(2).data().toArray();
                var plainArray2 = table.column(0).data().toArray();


                const formData = new FormData();


                for (let i = 0; i < plainArray.length; i++) {
                    formData.append('arregloCAI[]', plainArray[i]);
                    formData.append('arregloCAIID[]', plainArray2[i]);
                }

                axios.post('/ventas/seleccionar/menor', formData)
                    .then(response => {

                        let data = response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })

                        $('#tbl_lista_ventas').DataTable().ajax.reload();

                        document.getElementById('btn_menor').disabled = false;
                    })
                    .catch(err => {
                        document.getElementById('btn_menor').disabled = false;

                        let data = err.response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })

                    })




            }

            function confirmarGuardar() {
                Swal.fire({
                    icon:'warning',
                    title: '¿Ésta seguro de guardar los cambios?',
                    text:'Una vez se guarden los cambios efectuados, estos ya no se podrán modificar de ninguna forma posible.',
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor:'#1AA689'
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        guardarEstado()
                    }
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
