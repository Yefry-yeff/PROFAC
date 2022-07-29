<div>
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
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Tipo de Ajuste</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Listar</a>
                </li>
                <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_tipo_ajuste_crear">Tipo de Ajuste</a>
                </li>
                
            </ol>
        </div>


        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_tipo_ajuste_crear"><i class="fa fa-plus"></i> Añadir Tipo de Ajuste</a>
            </div>
            
        </div>


    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_tipos_listar" class="table table-striped table-bordered table-hover col-md-10">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Ajuste</th>
                                        <th>Registrado Por</th>
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

            <!-- Modal para registro de Tipo de Ajuste-->
            <div class="modal fade" id="modal_tipo_ajuste_crear" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de Tipo de Ajuste</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="crearTipoAjusteForm" name="crearTipoAjusteForm" data-parsley-validate>
                                {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                
                                <div class="row" id="row_datos">

                                    <div class="col-md-12">
                                        <label for="nombre" class="col-form-label focus-label">Tipo de Ajuste:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="nombre"
                                            name="nombre" data-parsley-required>
                                    </div>

                                    
                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="crearTipoAjusteForm" class="btn btn-primary">Guardar
                                Tipo de Ajuste</button>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Modal para editar Tipo de Ajuste-->
                        <div class="modal fade" id="modal_tipo_ajuste_editar" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="exampleModalLabel">Editar Tipo de Ajuste</h3>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
        
                                <div class="modal-body">
                                    <form id="editarTipoAjusteForm" name="editarTipoAjusteForm" data-parsley-validate>
                                        {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                        <input id="idAjuste" name="idAjuste" type="hidden" value="">
                                        <div class="row" id="row_datos">

                                            <div class="col-md-12">
                                                <label for="nombre_editar" class="col-form-label focus-label">Tipo de Ajuste:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="text" id="nombre_editar" name="nombre_editar" data-parsley-required>
                                            </div> 
        

                                        </div>
                                    </form>
        
                                </div>
        
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" form="editarTipoAjusteForm" class="btn btn-primary">Editar
                                        Tipo de Ajuste</button>
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
         
         $(document).on('submit', '#crearTipoAjusteForm', function(event) {
            event.preventDefault();
            guardarTipoAjuste();
        });

            function guardarTipoAjuste() {
                $('#modalSpinnerLoading').modal('show');

                var data = new FormData($('#crearTipoAjusteForm').get(0));
                
                axios.post("/inventario/tipoajuste/guardar", data)
                    .then(response => {
                        $('#modalSpinnerLoading').modal('hide');


                        $('#crearTipoAjusteForm').parsley().reset();
                        
                        document.getElementById("crearTipoAjusteForm").reset();
                        $('#modal_tipo_ajuste_crear').modal('hide');

                        $('#tbl_tipos_listar').DataTable().ajax.reload();


                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Tipo de Ajuste guardado con exito."
                        })

                    })
                    .catch(err => {
                        let data = err.response.data;
                        $('#modalSpinnerLoading').modal('hide');
                        $('#modal_tipo_ajuste_crear').modal('hide');
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                        console.error(err);

                    })

            }

            $(document).ready(function() {
                $('#tbl_tipos_listar').DataTable({
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
                    "ajax": "/inventario/tipoajuste/listar",
                    "columns": [{
                            data: 'id'
                        },
                        {
                            data: 'nombre'
                        },
                        {
                            data: 'name'
                        },                        
                        {
                            data: 'opciones'
                        }

                    ]


                });
            })

            function datosTipoAjuste(id){

                let data = {id:id}
                axios.post('/inventario/tipoajuste/datos',data)
                .then( response =>{
                  
                    let datos = response.data.datos;

                    document.getElementById('nombre_editar').value = datos.nombre;
                    document.getElementById('idAjuste').value = datos.id;
                                      
                    $('#modal_tipo_ajuste_editar').modal('show');
                })
                .catch( err=>{
                    console.log(err)
                })
            }

            $(document).on('submit', '#modal_tipo_ajuste_editar', function(event) {

                event.preventDefault();
                editarTipoAjuste();

            });

            function editarTipoAjuste(){

                $('#modalSpinnerLoading').modal('show');
                var data = new FormData($('#editarTipoAjusteForm').get(0));
                
            
                axios.post('/inventario/tipoajuste/editar',data)
                .then( response =>{
                    $('#modalSpinnerLoading').modal('hide');


                    $('#editarTipoAjusteForm').parsley().reset();
                    
                    document.getElementById("editarTipoAjusteForm").reset();
                    $('#modal_tipo_ajuste_editar').modal('hide');

                    $('#tbl_tipos_listar').DataTable().ajax.reload();


                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "Tipo de Ajuste editado con exito."
                    })

                })
                .catch( err=>{
                    let data = err.response.data;
                        $('#modalSpinnerLoading').modal('hide');
                        $('#modal_tipo_ajuste_editar').modal('hide');
                        
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                        console.error(err);

                })
            }

            /*function desactivarTipoAjuste(id){

                let data = {id:id}
                axios.post('/inventario/tipoajuste/desactivar',data)
                .then( response =>{
                    $('#tbl_tipos_listar').DataTable().ajax.reload();
                })
                .catch( err=>{
                    console.log(err)
                })
            }*/

        </script>
    @endpush
</div>
