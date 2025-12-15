
$(document).on('submit', '#userEditForm', function(event) {
    event.preventDefault();
    actualizarUsuario();
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

$(document).ready(function()
{
    $('#tbl_usuariosListar').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excel',
                title: 'Usuarios'
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
                data: 'estado',
                render: function(data, type, row) {
                    if (row.estado_id == 1) {
                        return '<span class="badge badge-success">'+data+'</span>';
                    } else {
                        return '<span class="badge badge-danger">'+data+'</span>';
                    }
                }
            },
            {
                data: 'fecha_registro'
            },
            {
                data: 'opciones'
            }

        ]


    });
});


function infoUsuario(idUsuario){
        axios.get('/usuario/info/'+idUsuario).then(function(response) {
            document.getElementById('id_usuario').value = response.data[0].id;
            document.getElementById('nombre_usuario').value = response.data[0].name;
            document.getElementById('identidad_usuario').value = response.data[0].identidad;
            document.getElementById('correo_usuario').value = response.data[0].email;
            document.getElementById('fenacimiento_usuario').value = response.data[0].fecha_nacimiento;




            selectRoles(response.data[0].rol_id, response.data[0].rol);


            $("#modal_usuario_rol").modal("show");
        })
        .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error"
            })
        });
}

function selectRoles(idRol, rol){
    axios.get('/usuario/roles/'+idRol).then(function(response) {

        //console.log(response.data);
                            let array = response.data;
                            let html = '<option selected value="'+idRol+'"> '+rol+' - Actuál</option>';

                            array.forEach(rol => {

                                html +=
                                    `
                            <option value="${ rol.id }">${rol.nombre}</option>
                        `
                            });

                            //console.log(html);

                            document.getElementById("seleccionarRol").innerHTML = html;

    })
    .catch(function(error) {
        console.log(error);
        Swal.fire({
            icon: 'error',
            title: 'Error...',
            text: "Ha ocurrido un error"
        })
    });
}

function actualizarUsuario() {
   // $('#modalSpinnerLoading').modal('show');

    var data = new FormData($('#userEditForm').get(0));

        axios.post("/usuario/actualizar", data)
            .then(response => {


                $('#userEditForm').parsley().reset();

                document.getElementById("userEditForm").reset();
                $('#modal_usuario_rol').modal('hide');

                $('#tbl_usuariosListar').DataTable().ajax.reload();


                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Usuario Actualizado con exito."
                });
                location.reload()

        }).catch(err => {
            let data = err.response.data;
            $('#modal_usuario_crear').modal('hide');
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text
            })
            console.error(err);

        });

}

function baja(idUsuario){
    Swal.fire({
        title: '¿Está seguro?',
        text: "¿Desea dar de baja a este usuario?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.get('/usuario/baja/'+idUsuario).then(function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Usuario dado de baja con éxito."
                });
                $('#tbl_usuariosListar').DataTable().ajax.reload();
            })
            .catch(function(error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "Ha ocurrido un error al dar de baja el usuario."
                });
                console.log(error);
            });
        }
    });
}

function activar(idUsuario){
    Swal.fire({
        title: '¿Está seguro?',
        text: "¿Desea activar a este usuario?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.get('/usuario/activar/'+idUsuario).then(function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Usuario activado con éxito."
                });
                $('#tbl_usuariosListar').DataTable().ajax.reload();
            })
            .catch(function(error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "Ha ocurrido un error al activar el usuario."
                });
                console.log(error);
            });
        }
    });
}
