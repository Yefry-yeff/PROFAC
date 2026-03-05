
$(document).on('submit', '#userEditForm', function(event) {
    event.preventDefault();
    actualizarUsuario();
});

function guardarUsuario() {
    // Validar que las contraseñas coincidan
    var pass = document.getElementById('pass_user').value;
    var confirmPass = document.getElementById('confirmar_pass').value;

    if (pass !== confirmPass) {
        document.getElementById('msg_pass_no_coincide').style.display = 'block';
        Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden.' });
        return;
    }
    document.getElementById('msg_pass_no_coincide').style.display = 'none';

    if (pass.length < 8) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'La contraseña debe tener al menos 8 caracteres.' });
        return;
    }

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
                    text: response.data.text
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
    // Cargar roles cuando se abre el modal de crear usuario
    $('#modal_usuario_crear').on('show.bs.modal', function () {
        cargarRolesParaNuevoUsuario();
    });

    $('#tbl_usuariosListar').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        autoWidth: false,
        scrollX: false,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excel',
                title: 'Usuarios'
            }
        ],
        "ajax": "/usuarios/listar/usuarios",
        "columns": [
            { data: 'contador',      width: '4%'  },
            { data: 'id',            width: '5%'  },
            { data: 'nombre',        width: '18%' },
            { data: 'telefono',      width: '10%' },
            { data: 'email',         width: '18%' },
            { data: 'identidad',     width: '10%' },
            { data: 'fecha_nacimiento', width: '10%', responsivePriority: 4 },
            { data: 'tipo_usuario',  width: '8%'  },
            {
                data: 'estado',
                width: '7%',
                render: function(data, type, row) {
                    if (row.estado_id == 1) {
                        return '<span class="badge badge-success">'+data+'</span>';
                    } else {
                        return '<span class="badge badge-danger">'+data+'</span>';
                    }
                }
            },
            { data: 'fecha_registro', width: '10%', responsivePriority: 5 },
            { data: 'opciones',       width: '10%', orderable: false }
        ]


    });
});


function infoUsuario(idUsuario){
        axios.get('/usuario/info/'+idUsuario).then(function(response) {
            document.getElementById('id_usuario').value = response.data[0].id;
            document.getElementById('nombre_usuario').value = response.data[0].name;
            document.getElementById('identidad_usuario').value = response.data[0].identidad ?? '';
            document.getElementById('correo_usuario').value = response.data[0].email;
            document.getElementById('fenacimiento_usuario').value = response.data[0].fecha_nacimiento ?? '';
            document.getElementById('telefono_usuario').value = response.data[0].telefono ?? '';

            selectRoles(response.data[0].rol_id, response.data[0].rol);

            $("#modal_usuario_rol").modal("show");
        })
        .catch(function(error) {
            console.log(error);
            Swal.fire({ icon: 'error', title: 'Error...', text: "Ha ocurrido un error" });
        });
}

function abrirModalContrasena(idUsuario) {
    document.getElementById('id_usuario_pwd').value = idUsuario;
    document.getElementById('nueva_contrasena').value = '';
    document.getElementById('confirmar_contrasena').value = '';
    document.getElementById('msg_pwd_no_coincide').style.display = 'none';
    $('#modal_cambiar_contrasena').modal('show');
}

function guardarContrasena() {
    var nueva    = document.getElementById('nueva_contrasena').value;
    var confirmar = document.getElementById('confirmar_contrasena').value;
    var msg = document.getElementById('msg_pwd_no_coincide');

    if (!nueva || nueva.length < 8) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'La contraseña debe tener al menos 8 caracteres.' });
        return;
    }
    if (nueva !== confirmar) {
        msg.style.display = 'block';
        return;
    }
    msg.style.display = 'none';

    var data = new FormData();
    data.append('id_usuario', document.getElementById('id_usuario_pwd').value);
    data.append('nueva_contrasena', nueva);
    data.append('confirmar_contrasena', confirmar);

    axios.post('/usuario/cambiar-contrasena', data)
        .then(response => {
            $('#modal_cambiar_contrasena').modal('hide');
            document.getElementById('formCambiarContrasena').reset();
            Swal.fire({ icon: 'success', title: 'Éxito!', text: response.data.text });
        })
        .catch(err => {
            let d = err.response ? err.response.data : {};
            $('#modal_cambiar_contrasena').modal('hide');
            Swal.fire({ icon: d.icon || 'error', title: d.title || 'Error', text: d.text || 'Ha ocurrido un error.' });
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

function cargarRolesParaNuevoUsuario(){
    axios.get('/usuario/roles/todos').then(function(response) {
        let array = response.data;
        let html = '<option value="" selected>-- Seleccione un rol --</option>';

        array.forEach(rol => {
            html += `<option value="${rol.id}">${rol.nombre}</option>`;
        });

        document.getElementById("rol_user").innerHTML = html;
    })
    .catch(function(error) {
        console.log(error);
        Swal.fire({
            icon: 'error',
            title: 'Error...',
            text: "Ha ocurrido un error al cargar los roles"
        })
    });
}

function actualizarUsuario() {
    var data = new FormData($('#userEditForm').get(0));

    axios.post("/usuario/actualizar", data)
        .then(response => {
            $('#userEditForm').parsley().reset();
            document.getElementById("userEditForm").reset();
            $('#modal_usuario_rol').modal('hide');
            $('#tbl_usuariosListar').DataTable().ajax.reload();
            Swal.fire({ icon: 'success', title: 'Exito!', text: response.data.text });
        }).catch(err => {
            let data = err.response.data;
            $('#modal_usuario_rol').modal('hide');
            Swal.fire({ icon: data.icon, title: data.title, text: data.text });
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
