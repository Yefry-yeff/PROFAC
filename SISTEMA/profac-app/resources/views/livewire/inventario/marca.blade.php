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
            <h2>Registro de Marcas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Listar</a>
                </li>
                <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_producto_crear">Registrar</a>
                </li>

            </ol>
        </div>


        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_producto_crear"><i class="fa fa-plus"></i> Añadir Marca</a>
            </div>
        </div>


    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_marcas_listar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod</th>
                                        <th>Nombre</th>
                                        <th>Registrado Por:</th>
                                        <th>Fecha de Registro</th>
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

            <!-- Modal para registro de marca-->
            <div class="modal fade" id="modal_producto_crear" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de Marca</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="crearProductoForm" name="crearProductoForm" data-parsley-validate>
                                {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                <div class="row" id="row_datos">
                                    <div class="col-md-12">
                                        <label for="nombre_producto" class="col-form-label focus-label">Nombre de la
                                            marca:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="nombre_producto"
                                            name="nombre_producto" data-parsley-required>
                                    </div>




                                    <div class="col-md-5">
                                        <label for="foto_producto" class="col-form-label focus-label">Fotografía:
                                        </label>
                                        <input class="" type="file" id="foto_producto" name="foto_producto"
                                            accept="image/png, image/gif, image/jpeg" multiple>

                                    </div>
                                    <div class=" col-md-7">
                                        <img id="imagenPrevisualizacion" class="ancho-imagen">

                                    </div>
                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="crearProductoForm" class="btn btn-primary">Guardar
                                Marca</button>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Modal para editar marca-->
                        <div class="modal fade" id="modal_producto_editar" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="exampleModalLabel">Editar Marca</h3>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <form id="editarProductoForm" name="editarProductoForm" data-parsley-validate>
                                        {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                        <input id="idMarca" name="idMarca" type="hidden" value="">
                                        <div class="row" id="row_datos">
                                            <div class="col-md-12">
                                                <label for="nombre_producto_editar" class="col-form-label focus-label">Nombre de la
                                                    marca:</label>
                                                <input class="form-control" required type="text" id="nombre_producto_editar"
                                                    name="nombre_producto_editar" data-parsley-required>
                                            </div>




                                            <div class="col-md-5">
                                                <label for="foto_producto_editar" class="col-form-label focus-label">Fotografía:
                                                </label>
                                                <input class="" type="file" id="foto_producto_editar" name="foto_producto_editar"
                                                    accept="image/png, image/gif, image/jpeg" multiple>

                                            </div>
                                            <div class=" col-md-7">
                                                <img id="imagenPrevisualizacion_editar" class="ancho-imagen">

                                            </div>
                                        </div>
                                    </form>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" form="editarProductoForm" class="btn btn-primary">Guardar
                                        Marca</button>
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
            const $foto_producto = document.querySelector("#foto_producto"),
                $imagenPrevisualizacion = document.querySelector("#imagenPrevisualizacion");

            // Escuchar cuando cambie
            $foto_producto.addEventListener("change", () => {
                // Los archivos seleccionados, pueden ser muchos o uno
                const archivos = $foto_producto.files;
                // Si no hay archivos salimos de la función y quitamos la imagen
                if (!archivos || !archivos.length) {
                    $imagenPrevisualizacion.src = "";
                    return;
                }
                // Ahora tomamos el primer archivo, el cual vamos a previsualizar
                const primerArchivo = archivos[0];
                // Lo convertimos a un objeto de tipo objectURL
                const objectURL = URL.createObjectURL(primerArchivo);
                // Y a la fuente de la imagen le ponemos el objectURL
                $imagenPrevisualizacion.src = objectURL;
            });

            $(document).on('submit', '#crearProductoForm', function(event) {

                event.preventDefault();
                guardarProducto();

            });

            function guardarProducto() {
                $('#modalSpinnerLoading').modal('show');

                var data = new FormData($('#crearProductoForm').get(0));

                var totalfiles = document.getElementById('foto_producto').files.length;
                for (var i = 0; i < totalfiles; i++) {
                    data.append("files[]", document.getElementById('foto_producto').files[i]);
                }

                axios.post("/producto/marca/guardar", data)
                    .then(response => {
                        $('#modalSpinnerLoading').modal('hide');


                        $('#crearProductoForm').parsley().reset();
                        img = document.getElementById('imagenPrevisualizacion');
                        img.src = "";
                        document.getElementById("crearProductoForm").reset();
                        $('#modal_producto_crear').modal('hide');

                        $('#tbl_marcas_listar').DataTable().ajax.reload();


                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Marca creado con exito."
                        })

                    })
                    .catch(err => {
                        let data = err.response.data;
                        $('#modalSpinnerLoading').modal('hide');
                        $('#modal_producto_crear').modal('hide');
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                        console.error(err);

                    })

            }

            $(document).ready(function() {
                $('#tbl_marcas_listar').DataTable({
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
                    "ajax": "/producto/marca/listar",
                    "columns": [
                        {
                            data: 'id'
                        },
                        {
                            data: 'nombre'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'opciones'
                        }

                    ]


                });
            })


            function datosMarca(id){

                let data = {id:id}
                axios.post('/producto/marca/datos',data)
                .then( response =>{

                    let datos = response.data.datos;

                    document.getElementById('nombre_producto_editar').value = datos.nombre;
                    document.getElementById('idMarca').value = datos.id;
                    if(datos.url_img){
                        document.getElementById('imagenPrevisualizacion_editar').src = '/marcas/'+datos.url_img;
                    }


                    $('#modal_producto_editar').modal('show');
                })
                .catch( err=>{
                    console.log(err)
                })
            }

            const $foto_producto_editar = document.querySelector("#foto_producto_editar"),
            $imagenPrevisualizacion_editar = document.querySelector("#imagenPrevisualizacion_editar");

            // Escuchar cuando cambie
            $foto_producto_editar.addEventListener("change", () => {
                // Los archivos seleccionados, pueden ser muchos o uno
                const archivos = $foto_producto_editar.files;
                // Si no hay archivos salimos de la función y quitamos la imagen
                if (!archivos || !archivos.length) {
                    $imagenPrevisualizacion_editar.src = "";
                    return;
                }
                // Ahora tomamos el primer archivo, el cual vamos a previsualizar
                const primerArchivo = archivos[0];
                // Lo convertimos a un objeto de tipo objectURL
                const objectURL = URL.createObjectURL(primerArchivo);
                // Y a la fuente de la imagen le ponemos el objectURL
                $imagenPrevisualizacion_editar.src = objectURL;
            });

            $(document).on('submit', '#modal_producto_editar', function(event) {

                    event.preventDefault();
                    editarMarca();

            });


            function editarMarca(){

                $('#modalSpinnerLoading').modal('show');
                var data = new FormData($('#editarProductoForm').get(0));
                var totalfiles = document.getElementById('foto_producto_editar').files.length;

                for (var i = 0; i < totalfiles; i++) {
                    data.append("files[]", document.getElementById('foto_producto_editar').files[i]);
                }

                axios.post('/producto/marca/editar',data)
                .then( response =>{
                    $('#modalSpinnerLoading').modal('hide');


                    $('#editarProductoForm').parsley().reset();
                    img = document.getElementById('imagenPrevisualizacion');
                    img.src = "";
                    document.getElementById("editarProductoForm").reset();
                    $('#modal_producto_editar').modal('hide');

                    $('#tbl_marcas_listar').DataTable().ajax.reload();


                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "Marca editada con exito."
                    })

                })
                .catch( err=>{
                    let data = err.response.data;
                        $('#modalSpinnerLoading').modal('hide');
                        $('#modal_producto_editar').modal('hide');

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
