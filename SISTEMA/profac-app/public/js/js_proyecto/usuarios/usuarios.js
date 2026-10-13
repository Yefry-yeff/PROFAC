
$(document).on('submit', '#userEditForm', function(event) {
    event.preventDefault();
    actualizarUsuario();
});

var usrPermisos = {
    permisos: [],
    heredados: [],
    efectivos: []
};

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
        pageLength: 5,
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
            { data: 'telefono',      width: '10%',  visible: false },
            { data: 'email',         width: '18%' },
            { data: 'identidad',     width: '10%' },
            { data: 'fecha_nacimiento', width: '10%', responsivePriority: 4, visible: false },
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
            usrCargarRolesAdicionales(response.data[0].id);
            usrCargarPermisos(response.data[0].id, response.data[0].rol_id);

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

$(document).on('change', '#seleccionarRol', function () {
    var idUsuario = $('#id_usuario').val();
    if (idUsuario && this.value) {
        usrCargarPermisos(idUsuario, this.value);
    }
});

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
    var efectivos = usrPermisos.efectivos.map(Number);
    var heredados = usrPermisos.heredados.map(Number);
    var otorgados = efectivos.filter(function (id) { return !heredados.includes(id); });
    var revocados = heredados.filter(function (id) { return !efectivos.includes(id); });

    otorgados.forEach(function (id) { data.append('permisos_otorgados[]', id); });
    revocados.forEach(function (id) { data.append('permisos_revocados[]', id); });

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

function usrCargarPermisos(idUsuario, rolId) {
    usrPermisos = { permisos: [], heredados: [], efectivos: [] };
    $('#usr_permisos_lista').html('<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-1"></i>Cargando permisos...</div>');

    axios.get('/usuario/' + idUsuario + '/permisos', { params: { rol_id: rolId || '' } })
        .then(function (response) {
            usrPermisos = {
                permisos: response.data.permisos || [],
                heredados: (response.data.heredados || []).map(Number),
                efectivos: (response.data.efectivos || []).map(Number)
            };
            usrRenderPermisos();
        })
        .catch(function (error) {
            console.error(error);
            $('#usr_permisos_lista').html('<div class="alert alert-danger mb-0">No se pudieron cargar los permisos del usuario.</div>');
        });
}

function usrPermisoSeleccionado(id) {
    return usrPermisos.efectivos.includes(Number(id));
}

function usrEscapeHtml(value) {
    return $('<div>').text(value || '').html();
}

function usrRenderPermisos() {
    var termino = ($('#usr_permisos_buscar').val() || '').trim().toLowerCase();
    var grupos = {};

    usrPermisos.permisos.forEach(function (permiso) {
        var texto = ((permiso.menu_nombre || '') + ' ' + (permiso.nombre || '') + ' ' + (permiso.ruta || '')).toLowerCase();
        if (termino && texto.indexOf(termino) === -1) return;
        var grupo = permiso.menu_nombre || 'Sin menú';
        if (!grupos[grupo]) grupos[grupo] = [];
        grupos[grupo].push(permiso);
    });

    $('#usr_permisos_resumen').text(usrPermisos.efectivos.length + ' seleccionados');
    if (!Object.keys(grupos).length) {
        $('#usr_permisos_lista').html('<div class="text-center text-muted py-3"><i class="fa fa-search mr-1"></i>Sin resultados.</div>');
        return;
    }

    var html = '';
    Object.keys(grupos).sort().forEach(function (nombreGrupo, index) {
        html += '<section class="usr-permiso-grupo"><div class="usr-permiso-grupo-titulo">' + usrEscapeHtml(nombreGrupo) + '</div><div class="usr-permiso-grupo-items">';
        grupos[nombreGrupo].forEach(function (permiso) {
            var id = Number(permiso.id);
            var inputId = 'usr-permiso-' + id;
            var heredado = usrPermisos.heredados.includes(id);
            var otorgado = usrPermisos.efectivos.includes(id) && !heredado;
            var revocado = heredado && !usrPermisos.efectivos.includes(id);
            var origen = otorgado ? 'Directo' : (revocado ? 'Revocado' : (heredado ? 'Por rol' : ''));
            html += '<div class="usr-permiso-item custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input usr-permiso-toggle" id="' + inputId + '" data-id="' + id + '" ' + (usrPermisoSeleccionado(id) ? 'checked' : '') + '>' +
                '<label class="custom-control-label" for="' + inputId + '" title="' + usrEscapeHtml(permiso.ruta) + '">' + usrEscapeHtml(permiso.nombre) + ' <span class="usr-permiso-origen">' + origen + '</span></label>' +
                '</div>';
        });
        html += '</div></section>';
    });
    $('#usr_permisos_lista').html(html);
}

$(document).on('input', '#usr_permisos_buscar', usrRenderPermisos);
$(document).on('change', '.usr-permiso-toggle', function () {
    var id = Number($(this).data('id'));
    if (this.checked && !usrPermisos.efectivos.includes(id)) {
        usrPermisos.efectivos.push(id);
    } else if (!this.checked) {
        usrPermisos.efectivos = usrPermisos.efectivos.filter(function (permisoId) { return permisoId !== id; });
    }
    usrRenderPermisos();
});

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

// ======================================================================
// ROLES ADICIONALES (multi-rol) del usuario — NO afecta el rol principal.
// Cada Agregar/Quitar se guarda de inmediato (independiente del botón
// "Actualizar" del formulario principal).
// ======================================================================

function usrCargarRolesAdicionales(idUsuario) {
    document.getElementById('usr_roladd_usuario_id').value = idUsuario;

    var $sel = $('#usr_roladd_select');
    if ($sel.data('select2')) { $sel.select2('destroy'); }
    $sel.empty();
    $sel.select2({
        placeholder: 'Buscar rol para agregar...',
        width: '100%',
        dropdownParent: $sel.closest('.modal'),
        ajax: {
            url: '/usuario/' + idUsuario + '/roles-adicionales/buscar',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data.results || [] }; }
        }
    });

    axios.get('/usuario/' + idUsuario + '/roles-adicionales')
        .then(function (response) {
            usrRenderRolesAdicionales(response.data.data || []);
        })
        .catch(function (error) {
            console.error(error);
            usrRenderRolesAdicionales([]);
        });
}

function usrRenderRolesAdicionales(roles) {
    var $cont = $('#usr_roladd_lista').empty();
    if (!roles.length) {
        $cont.html('<span class="usr-chip-empty">Sin roles adicionales asignados.</span>');
        return;
    }
    roles.forEach(function (rol) {
        $cont.append(
            $('<span class="usr-chip" data-id="' + rol.id + '"></span>')
                .append($('<span></span>').text(rol.nombre))
                .append($('<i class="fa fa-times usr-chip-remove"></i>').on('click', function () { usrQuitarRolAdicional(rol.id); }))
        );
    });
}

function agregarRolAdicionalUsuario() {
    var idUsuario = document.getElementById('usr_roladd_usuario_id').value;
    var $sel = $('#usr_roladd_select');
    var data = $sel.select2('data');

    if (!data || !data.length) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione un rol para agregar.' });
        return;
    }
    var rolId = parseInt(data[0].id, 10);

    axios.post('/usuario/' + idUsuario + '/roles-adicionales/agregar', { rol_id: rolId })
        .then(function () {
            $sel.val(null).trigger('change');
            usrCargarRolesAdicionales(idUsuario);
        })
        .catch(function (error) {
            var d = error.response ? error.response.data : {};
            Swal.fire({ icon: d.icon || 'error', title: d.title || 'Error', text: d.text || 'No se pudo agregar el rol.' });
        });
}

function usrQuitarRolAdicional(rolId) {
    var idUsuario = document.getElementById('usr_roladd_usuario_id').value;
    Swal.fire({
        title: '¿Quitar rol adicional?',
        text: 'El usuario perderá los accesos que le otorgaba este rol adicional.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'usr-swal-over-modal' }
    }).then(function (result) {
        if (!result.isConfirmed) return;
        axios.post('/usuario/' + idUsuario + '/roles-adicionales/quitar', { rol_id: rolId })
            .then(function () { usrCargarRolesAdicionales(idUsuario); })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo quitar el rol.',
                    customClass: { container: 'usr-swal-over-modal' }
                });
                console.error(error);
            });
    });
}

