<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Usuarios</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Lista</a>
                </li>


            </ol>
        </div>

       {{--   <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_usuario_crear"><i class="fa fa-plus"></i>Registrar Usuarios</a>
            </div>
        </div>  --}}

        <!-- Modal para cambiar rol de usuario-->
        <div class="modal fade" id="modal_usuario_rol" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de Usuarios</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="userAddForm" name="userAddForm" data-parsley-validate>
                                <div class="row" id="row_datos">

                                    <div class="col-md-12">
                                        <label for="nombre" class="col-form-label focus-label">Nombre Usuario:<span class="text-danger">*</span></label>

                                        <input required type="hide" id="id_usuario" name="id_usuario" data-parsley-required>
                                        <input class="form-control" required type="text" id="nombre_usuario" name="nombre_usuario" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="correo" class="col-form-label focus-label">Correo de usuario:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="correo_usuario" name="correo_usuario" data-parsley-required>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="identidad_user" class="col-form-label focus-label">Rol Actuál:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="rol_actual" name="rol_actual" data-parsley-required>
                                    </div>


                                    <div class="col-md-12">
                                        <label for="identidad_user" class="col-form-label focus-label">Seleccione nuevo rol:<span class="text-danger">*</span></label>
                                        <select id="seleccionarRol" name="seleccionarRol"class="form-group form-control" style="" onchange="obtenerRoles()">
                                        <option value="" selected disabled>--Seleccione un Rol--</option>
                                        </select>

                                    </div>
                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="userAddForm" class="btn btn-primary">Guardar Usuario</button>
                        </div>
                    </div>
                </div>
            </div>

    </div>


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_usuariosListar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Telefono</th>
                                        <th>Correo</th>
                                        <th>Identidad</th>
                                        <th>Fecha de Nacimiento</th>
                                        <th>tipo</th>
                                        <th>Fecha Ingreso</th>
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




</div>
@push('scripts')

<script>
    $(document).on('submit', '#userAddForm', function(event) {
        event.preventDefault();
        guardarUsuario();
    });

        function guardarUsuario() {
            $('#modalSpinnerLoading').modal('show');

            var data = new FormData($('#userAddForm').get(0));

                axios.post("/usuario/guardar", data)
                    .then(response => {


                        $('#userAddForm').parsley().reset();

                        document.getElementById("userAddForm").reset();
                        $('#modal_usuario_crear').modal('hide');

                        $('#tbl_usuariosListar').DataTable().ajax.reload();


                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Usuario Creado con exito."
                        })

                })
                .catch(err => {
                    let data = err.response.data;
                    $('#modal_usuario_crear').modal('hide');
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text
                    })
                    console.error(err);

                })

        }



            $(document).ready(function() {
            $('#tbl_usuariosListar').DataTable({
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
                "ajax": "/usuarios/listar/usuarios",
                "columns": [
                    {
                        data: 'contador'
                    },
                    {
                        data: 'id'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'telefono'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'identidad'
                    },
                    {
                        data: 'fecha_nacimiento'
                    },
                    {
                        data: 'tipo_usuario'
                    },
                    {
                        data: 'fecha_registro'
                    },
                    {
                        data: 'opciones'
                    }

                ]


            });
        })
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
