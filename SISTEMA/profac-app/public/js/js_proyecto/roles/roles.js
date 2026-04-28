// ======================================================================
// GESTIÓN DE ROLES - JavaScript
// ======================================================================

// Variables globales para cambios pendientes de usuarios
let usuariosOriginales = [];
let usuariosActuales = [];
let usuariosAgregar = [];
let usuariosQuitar = [];

// Variables globales para cambios pendientes de permisos
let permisosOriginales = [];
let permisosActuales = [];
let permisosAgregar = [];
let permisosQuitar = [];

$(document).ready(function() {
    // Inicializar DataTable
    inicializarDataTable();

    // Manejar envío de formulario
    $('#formRol').on('submit', function(e) {
        e.preventDefault();
        guardarRol();
    });
});

/**
 * Inicializar DataTable con datos dinámicos
 */
function inicializarDataTable() {
    $('#tablaRoles').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        autoWidth: false,
        dom: '<"row align-items-center mb-3"<"col-6 col-sm-4"l><"col-6 col-sm-4"B><"col-12 col-sm-4 mt-2 mt-sm-0"f>>' +
             '<"row"<"col-12"tr>>' +
             '<"row mt-3"<"col-12 col-sm-5 text-center text-sm-left mb-2 mb-sm-0"i><"col-12 col-sm-7"p>>',
        buttons: [
            {
                extend: 'excel',
                title: 'Roles',
                className: 'btn btn-sm btn-light border'
            }
        ],
        "ajax": "/roles/listar",
        "columns": [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'nivel_badge',   orderable: false, searchable: false },
            { data: 'area_badge',    orderable: false, searchable: false },
            { data: 'estado_badge',  orderable: false, searchable: false },
            { data: 'total_usuarios',  className: 'text-center' },
            { data: 'total_permisos',  className: 'text-center' },
            { data: 'fecha',           className: 'text-center' },
            { data: 'opciones',        orderable: false, searchable: false, className: 'text-center' }
        ],
        "drawCallback": function(settings) {
            // Actualizar pills de estadísticas
            const api    = this.api();
            const data   = api.data().toArray();
            const total  = data.length;
            const activos   = data.filter(r => r.estado_id == 1).length;
            const inactivos = total - activos;
            $('#statTotal').text(total);
            $('#statActivos').text(activos);
            $('#statInactivos').text(inactivos);
        }
    });
}

/**
 * Abrir modal para crear nuevo rol
 */
function abrirModalRol() {
    $('#rolId').val('');
    $('#rolNombre').val('');
    $('#rolEstado').val('1');
    $('#rolNivel').val('');
    $('#rolArea').val('');
    $('#tituloModalRol').text('Nuevo Rol');
    $('#seccionTabs').hide();

    // Limpiar cambios pendientes
    usuariosOriginales = [];
    usuariosActuales   = [];
    usuariosAgregar    = [];
    usuariosQuitar     = [];
    permisosOriginales = [];
    permisosActuales   = [];
    permisosAgregar    = [];
    permisosQuitar     = [];

    $('#modalRol').modal('show');
}

/**
 * Editar rol existente
 */
function editarRol(idRol) {
    // Limpiar estado previo
    $('#rolId').val('');
    $('#rolNombre').val('');
    $('#rolEstado').val('');
    $('#rolNivel').val('');
    $('#rolArea').val('');
    $('#seccionTabs').hide();
    $('#tituloModalRol').html('<i class="fa fa-spinner fa-spin mr-2"></i>Cargando...');

    // Limpiar cambios pendientes
    usuariosOriginales = [];
    usuariosActuales   = [];
    usuariosAgregar    = [];
    usuariosQuitar     = [];
    permisosOriginales = [];
    permisosActuales   = [];
    permisosAgregar    = [];
    permisosQuitar     = [];

    // Abrir modal inmediatamente — sin spinner separado
    $('#modalRol').modal('show');

    axios.get(`/roles/obtener/${idRol}`)
        .then(response => {
            const rol = response.data.data;

            $('#rolId').val(rol.id);
            $('#rolNombre').val(rol.nombre);
            $('#rolEstado').val(rol.estado_id);
            $('#rolNivel').val(rol.nivel_id || '');
            $('#rolArea').val(rol.area_id || '');
            $('#tituloModalRol').html('<i class="fa fa-edit mr-2"></i>Editar Rol');

            $('#seccionTabs').show();
            $('#tab-usuarios-link').tab('show');
            cargarUsuariosDelRol(idRol);
            cargarUsuariosDisponibles();
            cargarPermisosDelRol(idRol);
            cargarSubmenusDisponibles();
        })
        .catch(error => {
            $('#modalRol').modal('hide');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.mensaje || 'No se pudo cargar el rol'
            });
        });
}

/**
 * Cargar usuarios del rol
 */
function cargarUsuariosDelRol(rolId) {
    axios.get(`/roles/${rolId}/usuarios`)
        .then(response => {
            usuariosOriginales = response.data.data;
            usuariosActuales = [...usuariosOriginales];
            usuariosAgregar = [];
            usuariosQuitar = [];

            mostrarUsuariosEnTabla();
        })
        .catch(error => {
        });
}

/**
 * Mostrar usuarios en la tabla
 */
function mostrarUsuariosEnTabla() {

    let html = '';

    if (usuariosActuales.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-muted"><i class="fa fa-info-circle"></i> No hay usuarios asignados</td></tr>';
    } else {
        usuariosActuales.forEach(usuario => {
            const esNuevo = usuariosAgregar.includes(usuario.id);
            const rowClass = esNuevo ? 'table-success' : '';
            const rolAnterior = usuario.rol_anterior_nombre || 'Ninguno';

            html += `
                <tr class="${rowClass}">
                    <td>${usuario.id}</td>
                    <td>${usuario.name} ${esNuevo ? '<span class="badge badge-success">Nuevo</span>' : ''}</td>
                    <td>${usuario.email}</td>
                    <td>${rolAnterior}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-xs btn-quitar-usuario" data-usuario-id="${usuario.id}" title="Quitar">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    $('#listaUsuariosRol').html(html);

    // Asignar eventos a los botones después de crear el HTML
    $('.btn-quitar-usuario').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const usuarioId = $(this).data('usuario-id');
        solicitarQuitarUsuario(usuarioId);
        return false;
    });
}

/**
 * Cargar usuarios disponibles para agregar
 */
function cargarUsuariosDisponibles() {
    axios.get('/usuarios/todos')
        .then(response => {
            const usuarios = response.data.data;
            let opciones = '<option value="">Seleccione un usuario para agregar...</option>';

            usuarios.forEach(usuario => {
                opciones += `<option value="${usuario.id}">${usuario.name} - ${usuario.email}</option>`;
            });

            $('#selectUsuarioAgregar').html(opciones);
        })
        .catch(error => {
        });
}

/**
 * Agregar usuario al rol (temporalmente)
 */
function agregarUsuarioAlRol(event) {

    const usuarioId = parseInt($('#selectUsuarioAgregar').val());

    if (!usuarioId) {
        alert('Debe seleccionar un usuario');
        return;
    }

    // Verificar si el usuario ya está en la lista
    if (usuariosActuales.find(u => u.id === usuarioId)) {
        alert('El usuario ya está asignado a este rol');
        return;
    }

    // Buscar el usuario en la lista de todos los usuarios
    const selectUsuario = $('#selectUsuarioAgregar option:selected');
    const usuarioTexto = selectUsuario.text();
    const [nombre, email] = usuarioTexto.split(' - ');

    // Obtener el rol anterior del usuario
    axios.get(`/usuarios/${usuarioId}/rol-anterior`)
        .then(response => {
            const nuevoUsuario = {
                id: usuarioId,
                name: nombre,
                email: email,
                rol_anterior_id: response.data.rol_anterior_id,
                rol_anterior_nombre: response.data.rol_anterior_nombre || 'Ninguno'
            };

            // Agregar a la lista actual
            usuariosActuales.push(nuevoUsuario);
            usuariosAgregar.push(usuarioId);

            // Actualizar la vista
            mostrarUsuariosEnTabla();
            $('#selectUsuarioAgregar').val('');
        })
        .catch(error => {
            alert('Error al agregar usuario: ' + (error.response?.data?.mensaje || error.message));
        });
}

/**
 * Quitar usuario del rol
 */
/**
 * Aplicar cambios de usuarios al rol
 */
function aplicarCambiosUsuarios(rolId) {
    const promesas = [];

    // Agregar usuarios
    usuariosAgregar.forEach(usuarioId => {
        promesas.push(axios.post(`/roles/${rolId}/agregar-usuario`, { usuario_id: usuarioId }));
    });

    // Quitar usuarios
    usuariosQuitar.forEach(usuarioId => {
        promesas.push(axios.post(`/roles/${rolId}/quitar-usuario`, { usuario_id: usuarioId }));
    });

    return Promise.all(promesas);
}

/**
 * Solicitar confirmación para quitar usuario
 */
function solicitarQuitarUsuario(usuarioId) {

    $('#usuarioQuitarId').val(usuarioId);
    $('#modalConfirmarQuitarUsuario').modal('show');
}

/**
 * Confirmar y quitar usuario del rol (temporalmente)
 */
function confirmarQuitarUsuarioDelRol() {
    const usuarioId = parseInt($('#usuarioQuitarId').val());

    // Remover de la lista actual
    usuariosActuales = usuariosActuales.filter(u => u.id !== usuarioId);

    // Si estaba en la lista de agregar, quitarlo de ahí
    const indexAgregar = usuariosAgregar.indexOf(usuarioId);

    if (indexAgregar > -1) {
        usuariosAgregar.splice(indexAgregar, 1);
    } else {
        // Si no estaba en agregar, agregarlo a la lista de quitar
        if (!usuariosQuitar.includes(usuarioId)) {
            usuariosQuitar.push(usuarioId);
        }
    }

    // Actualizar la vista
    mostrarUsuariosEnTabla();

    // Cerrar modal de confirmación y restaurar el modal principal
    $('#modalConfirmarQuitarUsuario').one('hidden.bs.modal', function() {
        // Restaurar estado modal-open para que #modalRol siga activo
        $('body').addClass('modal-open');
    });
    $('#modalConfirmarQuitarUsuario').modal('hide');
}

/**
 * Guardar o actualizar rol
 */
function guardarRol() {
    const rolId = $('#rolId').val();
    const datos = {
        nombre:          $('#rolNombre').val().trim(),
        estado_id:       $('#rolEstado').val(),
        nivel_id:        $('#rolNivel').val() || null,
        area_id:         $('#rolArea').val()  || null,
        usuarios_agregar: usuariosAgregar,
        usuarios_quitar:  usuariosQuitar,
        permisos_agregar: permisosAgregar,
        permisos_quitar:  permisosQuitar
    };

    const url    = rolId ? `/roles/actualizar/${rolId}` : '/roles/guardar';
    const metodo = rolId ? 'put' : 'post';

    $('#modalRol').modal('hide');
    $('#modalSpinnerLoading').modal('show');

    axios[metodo](url, datos)
        .then(response => {
            // Si el rol fue creado, ahora aplicar los cambios de usuarios
            const rolIdFinal = rolId || response.data.data.id;

            // Si hay cambios de usuarios y es un rol existente, aplicarlos
            if (rolId && (usuariosAgregar.length > 0 || usuariosQuitar.length > 0)) {
                return aplicarCambiosUsuarios(rolIdFinal).then(() => response);
            }

            return response;
        })
        .then(response => {
            // Forzar cierre del spinner
            $('#modalSpinnerLoading').modal('hide');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();

            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: response.data.mensaje,
                timer: 2000,
                showConfirmButton: false
            });

            // Recargar tabla
            $('#tablaRoles').DataTable().ajax.reload(null, false);

            // Limpiar formulario y variables
            $('#formRol')[0].reset();
            usuariosOriginales = [];
            usuariosActuales = [];
            usuariosAgregar = [];
            usuariosQuitar = [];
            permisosOriginales = [];
            permisosActuales = [];
            permisosAgregar = [];
            permisosQuitar = [];
        })
        .catch(error => {
            // Forzar cierre del spinner
            $('#modalSpinnerLoading').modal('hide');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();

            let mensajeError = 'No se pudo guardar el rol';
            if (error.response?.data?.mensaje) {
                mensajeError = error.response.data.mensaje;
            } else if (error.response?.data?.errors) {
                const errores = Object.values(error.response.data.errors).flat();
                mensajeError = errores.join('\n');
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensajeError
            });
        });
}

/**
 * Cambiar estado del rol (Activar/Desactivar)
 */
function cambiarEstadoRol(idRol, estadoActual) {
    const accion = estadoActual == 1 ? 'desactivar' : 'activar';
    const titulo = estadoActual == 1 ? 'Desactivar Rol' : 'Activar Rol';
    const texto = `¿Está seguro que desea ${accion} este rol?`;

    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#modalSpinnerLoading').modal('show');

            axios.post(`/roles/cambiar-estado/${idRol}`)
                .then(response => {
                    $('#modalSpinnerLoading').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.data.mensaje,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Recargar tabla
                    $('#tablaRoles').DataTable().ajax.reload(null, false);
                })
                .catch(error => {
                    $('#modalSpinnerLoading').modal('hide');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.response?.data?.mensaje || 'No se pudo cambiar el estado del rol'
                    });
                });
        }
    });
}

/**
 * Eliminar rol (muestra modal de confirmación)
 */
function eliminarRol(idRol) {
    $('#rolEliminarId').val(idRol);
    $('#modalConfirmarEliminar').modal('show');
}

/**
 * Confirmar eliminación del rol
 */
function confirmarEliminarRol() {
    const idRol = $('#rolEliminarId').val();

    $('#modalConfirmarEliminar').modal('hide');
    $('#modalSpinnerLoading').modal('show');

    axios.delete(`/roles/eliminar/${idRol}`)
        .then(response => {
            $('#modalSpinnerLoading').modal('hide');

            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: response.data.mensaje,
                timer: 2000,
                showConfirmButton: false
            });

            // Recargar tabla
            $('#tablaRoles').DataTable().ajax.reload(null, false);
        })
        .catch(error => {
            $('#modalSpinnerLoading').modal('hide');

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.mensaje || 'No se pudo eliminar el rol'
            });
        });
}

/**
 * Validar nombre de rol en tiempo real
 */
$('#rolNombre').on('blur', function() {
    const nombre = $(this).val().trim();
    const rolId = $('#rolId').val();

    if (nombre && nombre.length >= 3) {
        // Aquí podrías agregar validación ajax para verificar duplicados
        // axios.get('/roles/validar-nombre', { params: { nombre, id: rolId } })
    }
});

// ======================================================================
// GESTIÓN DE PERMISOS (SUBMENUS) DEL ROL
// ======================================================================

/**
 * Cargar permisos (submenus) del rol
 */
function cargarPermisosDelRol(rolId) {

    axios.get(`/roles/${rolId}/permisos`)
        .then(response => {
            permisosOriginales = response.data.data || [];
            permisosActuales = [...permisosOriginales];
            permisosAgregar = [];
            permisosQuitar = [];

            mostrarPermisosEnTabla();
        })
        .catch(error => {
            alert('Error al cargar los permisos del rol');
        });
}

/**
 * Cargar lista de todos los submenus disponibles
 */
function cargarSubmenusDisponibles() {

    axios.get('/submenus/todos')
        .then(response => {
            const submenus = response.data.data || [];
            const $select = $('#selectSubmenuAgregar');

            $select.empty();
            $select.append('<option value="">Seleccione un submenu para agregar...</option>');

            submenus.forEach(submenu => {
                $select.append(`<option value="${submenu.id}" data-menu="${submenu.menu_nombre}" data-ruta="${submenu.ruta}">${submenu.menu_nombre} - ${submenu.nombre}</option>`);
            });
        })
        .catch(error => {
        });
}

/**
 * Mostrar permisos en la tabla
 */
function mostrarPermisosEnTabla() {

    const $tbody = $('#listaPermisosRol');
    $tbody.empty();

    if (permisosActuales.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <i class="fa fa-info-circle"></i> No hay permisos asignados
                </td>
            </tr>
        `);
    } else {
        permisosActuales.forEach(permiso => {
            const esNuevo = permisosAgregar.includes(permiso.id);
            const claseNuevo = esNuevo ? 'table-success' : '';

            $tbody.append(`
                <tr class="${claseNuevo}">
                    <td>${permiso.id}</td>
                    <td>${permiso.menu_nombre || '-'}</td>
                    <td>${permiso.submenu_nombre}</td>
                    <td><small>${permiso.ruta || '-'}</small></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-quitar-permiso" data-permiso-id="${permiso.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        // Event delegation para botones de quitar
        $tbody.off('click', '.btn-quitar-permiso');
        $tbody.on('click', '.btn-quitar-permiso', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const permisoId = parseInt($(this).data('permiso-id'));
            solicitarQuitarPermiso(permisoId);
        });
    }
}

/**
 * Agregar permiso al rol
 */
function agregarPermisoAlRol(event) {

    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const submenuId = parseInt($('#selectSubmenuAgregar').val());

    if (!submenuId) {
        alert('Debe seleccionar un submenu');
        return;
    }

    // Verificar si ya existe
    const yaExiste = permisosActuales.find(p => p.id === submenuId);
    if (yaExiste) {
        alert('El permiso ya está asignado al rol');
        return;
    }

    // Obtener datos del option seleccionado
    const $option = $('#selectSubmenuAgregar option:selected');
    const menuNombre = $option.data('menu');
    const submenuNombre = $option.text().split(' - ')[1];
    const ruta = $option.data('ruta');

    // Agregar a lista de cambios
    if (!permisosAgregar.includes(submenuId)) {
        permisosAgregar.push(submenuId);
    }

    // Quitar de lista de eliminados si estaba
    const indexQuitar = permisosQuitar.indexOf(submenuId);
    if (indexQuitar > -1) {
        permisosQuitar.splice(indexQuitar, 1);
    }

    // Agregar a permisos actuales
    permisosActuales.push({
        id: submenuId,
        menu_nombre: menuNombre,
        submenu_nombre: submenuNombre,
        ruta: ruta
    });

    // Actualizar vista
    mostrarPermisosEnTabla();
    $('#selectSubmenuAgregar').val('');
}

/**
 * Solicitar confirmación para quitar permiso
 */
function solicitarQuitarPermiso(permisoId) {

    $('#permisoQuitarId').val(permisoId);
    $('#modalConfirmarQuitarPermiso').modal('show');
}

/**
 * Confirmar quitar permiso del rol
 */
function confirmarQuitarPermisoDelRol() {

    const permisoId = parseInt($('#permisoQuitarId').val());

    // Actualizar listas
    permisosActuales = permisosActuales.filter(p => p.id !== permisoId);

    // Si estaba en la lista de agregar, quitarlo
    const indexAgregar = permisosAgregar.indexOf(permisoId);

    if (indexAgregar > -1) {
        permisosAgregar.splice(indexAgregar, 1);
    } else {
        // Si no estaba en la lista de agregar, agregarlo a la lista de quitar
        if (!permisosQuitar.includes(permisoId)) {
            permisosQuitar.push(permisoId);
        }
    }

    // Actualizar vista
    mostrarPermisosEnTabla();

    // Cerrar modal de confirmación y restaurar el modal principal
    $('#modalConfirmarQuitarPermiso').one('hidden.bs.modal', function() {
        $('body').addClass('modal-open');
    });
    $('#modalConfirmarQuitarPermiso').modal('hide');
}
