<div>

    @include('livewire.ventas.cai-credito')
    @include('livewire.ventas.cai-debito')
    @include('livewire.ventas.cai-devolucion')

    @push('styles')
        <style>
            @media (max-width: 600px) {
                .ancho-imagen {
                    max-width: 200px;
                }
            }

            @media (min-width: 601px) and (max-width:900px) {
                .ancho-imagen {
                    max-width: 300px;
                }
            }

            @media (min-width: 901px) {
                .ancho-imagen {
                    max-width: 12rem;
                }
            }

            /* a {
                    pointer-events: none;
                } */
            .loader,
            .loader:before,
            .loader:after {
                border-radius: 50%;
            }

            .loader {
                color: #0dc5c1;
                font-size: 11px;
                text-indent: -99999em;
                margin: 55px auto;
                position: relative;
                width: 10em;
                height: 10em;
                box-shadow: inset 0 0 0 1em;
                -webkit-transform: translateZ(0);
                -ms-transform: translateZ(0);
                transform: translateZ(0);
            }

            .loader:before,
            .loader:after {
                position: absolute;
                content: '';
            }

            .loader:before {
                width: 5.2em;
                height: 10.2em;
                background: #ffffff;
                border-radius: 10.2em 0 0 10.2em;
                top: -0.1em;
                left: -0.1em;
                -webkit-transform-origin: 5.1em 5.1em;
                transform-origin: 5.1em 5.1em;
                -webkit-animation: load2 2s infinite ease 1.5s;
                animation: load2 2s infinite ease 1.5s;
            }

            .loader:after {
                width: 5.2em;
                height: 10.2em;
                background: #ffffff;
                border-radius: 0 10.2em 10.2em 0;
                top: -0.1em;
                left: 4.9em;
                -webkit-transform-origin: 0.1em 5.1em;
                transform-origin: 0.1em 5.1em;
                -webkit-animation: load2 2s infinite ease;
                animation: load2 2s infinite ease;
            }

            @-webkit-keyframes load2 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }

            @keyframes load2 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }
        </style>
    @endpush
    <div class=" wrapper border-bottom white-bg page-heading">
        <div class="row">
            <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
                <h2>CAI </h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a>Listar</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a data-toggle="modal" data-target="#modal_cai_crear_facturacion">Facturación</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a data-toggle="modal" data-target="#modal_cai_crear_credito">Crédito</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a data-toggle="modal" data-target="#modal_cai_crear_debito">Débito</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a data-toggle="modal" data-target="#modal_cai_crear_devolucion">Crédito</a>
                    </li>

                </ol>
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-around mt-2">

            <div style="">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_cai_crear_facturacion"><i class="fa fa-plus"></i> Añadir CAI Facturación</a>
            </div>
            <div style="">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_cai_crear_credito"><i class="fa fa-plus"></i> Añadir CAI Nota Crédito</a>
            </div>
            <div style="">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_cai_crear_debito"><i class="fa fa-plus"></i> Añadir CAI Nota Débito</a>
            </div>
           {{--   <div style="">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_cai_crear_devolucion"><i class="fa fa-plus"></i> Añadir CAI Devolución Crédito</a>
            </div>  --}}

        </div>

    </div>




    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_cai_listar" class="table table-striped table-bordered table-hover col-md-10">
                                <thead class="">
                                    <tr>
                                        <th>Cod</th>
                                        <th>CAI</th>
                                        <th>Fecha Limite</th>
                                        <th>Documento Fiscal</th>
                                        <th>Cantidad Otorgada</th>
                                        <th>Numero Inicial</th>
                                        <th>Numero Final</th>
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

            <!-- Modal para registro de CAI Facturacion-->
            <div class="modal fade" id="modal_cai_crear_facturacion" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de CAI Facturacion</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="crearCAIFacturacionForm" name="crearCAIFacturacionForm" data-parsley-validate>
                                {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                <input id="tipo_documento_fiscal_id" name="tipo_documento_fiscal_id" type="hidden" value="1">
                                <div class="row" id="row_datos">

                                    <div class="col-md-12">
                                        <label for="cai" class="col-form-label focus-label">CAI:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="cai"
                                            name="cai" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="fecha_limite" class="col-form-label focus-label">Fecha Limite:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="date" id="fecha_limite"
                                            name="fecha_limite" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="cantidad_otorgada" class="col-form-label focus-label">Cantidad Otorgada:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="number" id="cantidad_otorgada"
                                            name="cantidad_otorgada" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="cantidad_solicitada" class="col-form-label focus-label">Cantidad Solicitada:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="number" id="cantidad_solicitada"
                                            name="cantidad_solicitada" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="numero_inicial" class="col-form-label focus-label">Numero Inicial:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="numero_inicial"
                                            name="numero_inicial" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="numero_final" class="col-form-label focus-label">Numero Final:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="numero_final"
                                            name="numero_final" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="punto_emision" class="col-form-label focus-label">Punto Emision:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="punto_emision"
                                            name="punto_emision" data-parsley-required>
                                    </div>

                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="crearCAIFacturacionForm" class="btn btn-primary">Guardar
                                CAI Facturacion</button>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Modal para editar CAI -->
                        <div class="modal fade" id="modal_cai_editar" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="exampleModalLabel">Editar CAI</h3>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <form id="editarProductoForm" name="editarProductoForm" data-parsley-validate>
                                        {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                        <input id="idCAI" name="idCAI" type="hidden" value="">
                                        <div class="row" id="row_datos">

                                            <div class="col-md-12">
                                                <label for="cai_editar" class="col-form-label focus-label">CAI:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="text" id="cai_editar" name="cai_editar" data-parsley-required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="fecha_limite_editar" class="col-form-label focus-label">Fecha Limite:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="date" id="fecha_limite_editar" name="fecha_limite_editar" data-parsley-required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="cantidad_otorgada_editar" class="col-form-label focus-label">Cantidad Otorgada:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="number" id="cantidad_otorgada_editar" name="cantidad_otorgada_editar" data-parsley-required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="cantidad_solicitada_editar" class="col-form-label focus-label">Cantidad Solicitada:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="number" id="cantidad_solicitada_editar" name="cantidad_solicitada_editar" data-parsley-required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="numero_inicial_editar" class="col-form-label focus-label">Numero Inicial:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="text" id="numero_inicial_editar" name="numero_inicial_editar" data-parsley-required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="numero_final_editar" class="col-form-label focus-label">Numero Final:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="text" id="numero_final_editar" name="numero_final_editar" data-parsley-required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="punto_emision_editar" class="col-form-label focus-label">Punto Emision:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="text" id="punto_emision_editar" name="punto_emision_editar" data-parsley-required>
                                            </div>


                                        </div>
                                    </form>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" form="editarProductoForm" class="btn btn-primary">Editar
                                        CAI</button>
                                </div>
                            </div>
                        </div>
                    </div>


        </div>



        <!-- Modal -->
        <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalSpinnerLoadingTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">

                    <div class="modal-body">
                        <h2 class="text-center">Espere un momento...</h2>
                        <div class="loader">Loading...</div>

                    </div>

                </div>
            </div>
        </div>






    </div>
    @push('scripts')
        <script>

         $(document).on('submit', '#crearCAIFacturacionForm', function(event) {
            event.preventDefault();
            guardarCaiFacturacion();
            {{--  $('#modal_cai_crear_facturacion').modal('hide');  --}}

        });

            function guardarCaiFacturacion() {



                $('#modalSpinnerLoading').modal('show');

                var datos = new FormData($('#crearCAIFacturacionForm').get(0));

                axios.post("/ventas/cai/guardar", datos)
                    .then(response => {

                            let data = response.data;

                        $('#modalSpinnerLoading').modal('hide');


                        $('#crearCAIFacturacionForm').parsley().reset();

                        document.getElementById("crearCAIFacturacionForm").reset();
                        $('#modal_cai_crear_facturacion').modal('hide');

                        $('#tbl_cai_listar').DataTable().ajax.reload();


                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })

                    })
                    .catch(err => {
                        console.log(err);
                        $('#modalSpinnerLoading').modal('hide');
                        $('#modal_cai_crear_facturacion').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'error!',
                            text: "Ha ocurrido un error al guardar cai"
                        })


                    })

            }

            $(document).ready(function() {
                $('#tbl_cai_listar').DataTable({
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
                    "ajax": "/ventas/cai/listar",
                    "columns": [{
                            data: 'id'
                        },
                        {
                            data: 'cai'
                        },
                        {
                            data: 'fecha_limite_emision'
                        },
                        {
                            data: 'descripcion'
                        },
                        {
                            data: 'cantidad_otorgada'
                        },
                        {
                            data: 'numero_inicial'
                        },
                        {
                            data: 'numero_final'
                        },
                        {
                            data: 'opciones'
                        }

                    ]


                });
            })

            function datosCAI(id){

                let data = {id:id}
                axios.post('/ventas/cai/datos',data)
                .then( response =>{

                    let datos = response.data.datos;

                    document.getElementById('cai_editar').value = datos.cai;
                    document.getElementById('fecha_limite_editar').value = datos.fecha_limite_emision;
                    document.getElementById('cantidad_otorgada_editar').value = datos.cantidad_otorgada;
                    document.getElementById('cantidad_solicitada_editar').value = datos.cantidad_solicitada;
                    document.getElementById('numero_inicial_editar').value = datos.numero_inicial;
                    document.getElementById('numero_final_editar').value = datos.numero_final;
                    document.getElementById('punto_emision_editar').value = datos.punto_de_emision;
                    document.getElementById('idCAI').value = datos.id;

                    $('#modal_cai_editar').modal('show');
                })
                .catch( err=>{
                    console.log(err)
                })
            }

            $(document).on('submit', '#modal_cai_editar', function(event) {

                    event.preventDefault();
                    editarCAI();

            });

             function editarCAI(){

                $('#modalSpinnerLoading').modal('show');
                var data = new FormData($('#editarProductoForm').get(0));


                axios.post('/ventas/cai/editar',data)
                .then( response =>{
                    $('#modalSpinnerLoading').modal('hide');


                    $('#editarProductoForm').parsley().reset();

                    document.getElementById("editarProductoForm").reset();
                    $('#modal_cai_editar').modal('hide');

                    $('#tbl_cai_listar').DataTable().ajax.reload();


                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "CAI editado con exito."
                    })

                })
                .catch( err=>{
                    let data = err.response.data;
                        $('#modalSpinnerLoading').modal('hide');
                        $('#modal_cai_editar').modal('hide');

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                        console.error(err);

                })
            }

            //////////////////////////////////////////////////////////////////////////////////
             function verificacionCAI(){


                axios.get('/cai/verificacion')
                .then( response =>{



                    return response;



                })
                .catch( err=>{
                    let data = err.response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                        console.error(err);

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
