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
        dom: '<"row mb-3"<"col-sm-4"l><"col-sm-4"B><"col-sm-4"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',
        buttons: [
            {
                extend: 'excel',
                title: 'Roles'
            }
        ],
        "ajax": "/roles/listar",
        "columns": [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'nivel_badge',   orderable: false, searchable: false },
            { data: 'area_badge',    orderable: false, searchable: false },
            { data: 'estado_badge',  orderable: false, searchable: false },
            { data: 'total_usuarios', className: 'text-center' },
            { data: 'total_permisos', className: 'text-center' },
            { data: 'fecha', className: 'text-center' },
            { data: 'opciones', orderable: false, searchable: false, className: 'text-center' }
        ]
    });
}

/**
 * Abrir modal para crear nuevo rol
 */
function abrirModalRol() {
    $('#rolId').val('');
    $('#rolNombre').val('');
    $('#rolEstado').val('1');
    $('#tituloModalRol').text('Nuevo Rol');
    $('#seccionTabs').hide();

    // Limpiar cambios pendientes
    usuariosOriginales = [];
    usuariosActuales = [];
    usuariosAgregar = [];
    usuariosQuitar = [];
    permisosOriginales = [];
    permisosActuales = [];
    permisosAgregar = [];
    permisosQuitar = [];

    $('#modalRol').modal('show');
}

/**
 * Editar rol existente
 */
function editarRol(idRol) {
    // Asegurar que el modal de spinner esté limpio antes de abrirlo
    $('#modalSpinnerLoading').modal('hide');

    // Pequeño delay para asegurar que el modal anterior esté cerrado
    setTimeout(() => {
        $('#modalSpinnerLoading').modal('show');

        axios.get(`/roles/obtener/${idRol}`)
            .then(response => {
                const rol = response.data.data;

                $('#rolId').val(rol.id);
                $('#rolNombre').val(rol.nombre);
                $('#rolEstado').val(rol.estado_id);
                $('#tituloModalRol').text('Editar Rol');

                // Mostrar sección de tabs y cargar datos
                $('#seccionTabs').show();
                $('#tab-usuarios-link').tab('show'); // Activar tab de usuarios por defecto
                cargarUsuariosDelRol(idRol);
                cargarUsuariosDisponibles();
                cargarPermisosDelRol(idRol);
                cargarSubmenusDisponibles();
                cargarUsuariosAdicionalesDelRol(idRol);

                // Forzar cierre del spinner
                $('#modalSpinnerLoading').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // Abrir modal de edición
                setTimeout(() => {
                    $('#modalRol').modal('show');
                }, 300);
            })
            .catch(error => {
                // Forzar cierre del spinner en caso de error
                $('#modalSpinnerLoading').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                console.error('Error al cargar rol:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.mensaje || 'No se pudo cargar el rol'
                });
            });
    }, 100);
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
            console.error('Error al cargar usuarios del rol:', error);
        });
}

/**
 * Mostrar usuarios en la tabla
 */
function mostrarUsuariosEnTabla() {
    console.log('=== mostrarUsuariosEnTabla INICIO ===');
    console.log('Usuarios actuales a mostrar:', usuariosActuales);

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
        console.log('Click en botón quitar usuario, ID:', usuarioId);
        solicitarQuitarUsuario(usuarioId);
        return false;
    });

    console.log('Tabla actualizada con', usuariosActuales.length, 'usuarios');
    console.log('=== mostrarUsuariosEnTabla FIN ===');
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
            console.error('Error al cargar usuarios:', error);
        });
}

/**
 * Agregar usuario al rol (temporalmente)
 */
function agregarUsuarioAlRol(event) {
    console.log('=== agregarUsuarioAlRol INICIO ===');
    console.log('Evento recibido:', event);

    const usuarioId = parseInt($('#selectUsuarioAgregar').val());
    console.log('Usuario ID a agregar:', usuarioId);

    if (!usuarioId) {
        console.log('No se seleccionó usuario');
        alert('Debe seleccionar un usuario');
        return;
    }

    // Verificar si el usuario ya está en la lista
    if (usuariosActuales.find(u => u.id === usuarioId)) {
        console.log('Usuario ya está en la lista');
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

            console.log('Nuevo usuario agregado:', nuevoUsuario);

            // Agregar a la lista actual
            usuariosActuales.push(nuevoUsuario);
            usuariosAgregar.push(usuarioId);

            console.log('Usuario agregado a lista temporal');

            // Actualizar la vista
            mostrarUsuariosEnTabla();
            $('#selectUsuarioAgregar').val('');

            console.log('=== agregarUsuarioAlRol FIN ===');
        })
        .catch(error => {
            console.error('Error al obtener rol anterior:', error);
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
    console.log('=== solicitarQuitarUsuario INICIO ===');
    console.log('Usuario ID a quitar:', usuarioId);
    console.log('Estado actual de usuarios:', usuariosActuales);

    $('#usuarioQuitarId').val(usuarioId);
    $('#modalConfirmarQuitarUsuario').modal('show');

    console.log('Modal de confirmación mostrado');
    console.log('=== solicitarQuitarUsuario FIN ===');
}

/**
 * Confirmar y quitar usuario del rol (temporalmente)
 */
function confirmarQuitarUsuarioDelRol() {
    console.log('=== confirmarQuitarUsuarioDelRol INICIO ===');
    const usuarioId = parseInt($('#usuarioQuitarId').val());
    console.log('Usuario ID a quitar:', usuarioId);
    console.log('Usuarios actuales ANTES:', JSON.stringify(usuariosActuales));
    console.log('Usuarios a agregar ANTES:', usuariosAgregar);
    console.log('Usuarios a quitar ANTES:', usuariosQuitar);

    // Remover de la lista actual
    usuariosActuales = usuariosActuales.filter(u => u.id !== usuarioId);
    console.log('Usuarios actuales DESPUÉS de filtrar:', JSON.stringify(usuariosActuales));

    // Si estaba en la lista de agregar, quitarlo de ahí
    const indexAgregar = usuariosAgregar.indexOf(usuarioId);
    console.log('Index en lista de agregar:', indexAgregar);

    if (indexAgregar > -1) {
        usuariosAgregar.splice(indexAgregar, 1);
        console.log('Usuario removido de lista de agregar');
    } else {
        // Si no estaba en agregar, agregarlo a la lista de quitar
        if (!usuariosQuitar.includes(usuarioId)) {
            usuariosQuitar.push(usuarioId);
            console.log('Usuario agregado a lista de quitar');
        }
    }

    console.log('Usuarios a agregar DESPUÉS:', usuariosAgregar);
    console.log('Usuarios a quitar DESPUÉS:', usuariosQuitar);

    // Cerrar modal de confirmación
    console.log('Cerrando modal de confirmación...');
    $('#modalConfirmarQuitarUsuario').modal('hide');

    // Limpiar backdrops
    setTimeout(() => {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        $('#modalRol').modal('show');
    }, 300);

    // Actualizar la vista
    console.log('Actualizando vista de tabla...');
    mostrarUsuariosEnTabla();

    console.log('=== confirmarQuitarUsuarioDelRol FIN ===');
}

/**
 * Guardar o actualizar rol
 */
function guardarRol() {
    console.log('=== guardarRol INICIO ===');
    console.log('FUNCIÓN guardarRol LLAMADA');
    console.trace('Stack trace de la llamada a guardarRol');

    const rolId = $('#rolId').val();
    const datos = {
        nombre: $('#rolNombre').val().trim(),
        estado_id: $('#rolEstado').val(),
        usuarios_agregar: usuariosAgregar,
        usuarios_quitar: usuariosQuitar,
        permisos_agregar: permisosAgregar,
        permisos_quitar: permisosQuitar
    };

    console.log('Rol ID:', rolId);
    console.log('Datos a guardar:', JSON.stringify(datos));
    console.log('Usuarios a agregar:', usuariosAgregar);
    console.log('Usuarios a quitar:', usuariosQuitar);
    console.log('Permisos a agregar:', permisosAgregar);
    console.log('Permisos a quitar:', permisosQuitar);

    const url = rolId ? `/roles/actualizar/${rolId}` : '/roles/guardar';
    const metodo = rolId ? 'put' : 'post';

    console.log('URL:', url);
    console.log('Método:', metodo);

    console.log('Ocultando modal de rol...');
    $('#modalRol').modal('hide');
    console.log('Mostrando modal de spinner...');
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

            console.error('Error al guardar rol:', error);

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
                    console.error('Error al cambiar estado:', error);

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
            console.error('Error al eliminar rol:', error);

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
    console.log('=== cargarPermisosDelRol INICIO ===');
    console.log('Rol ID:', rolId);

    axios.get(`/roles/${rolId}/permisos`)
        .then(response => {
            console.log('Permisos cargados:', response.data);
            permisosOriginales = response.data.data || [];
            permisosActuales = [...permisosOriginales];
            permisosAgregar = [];
            permisosQuitar = [];

            mostrarPermisosEnTabla();
            console.log('=== cargarPermisosDelRol FIN ===');
        })
        .catch(error => {
            console.error('Error al cargar permisos:', error);
            alert('Error al cargar los permisos del rol');
        });
}

/**
 * Cargar lista de todos los submenus disponibles
 */
function cargarSubmenusDisponibles() {
    console.log('=== cargarSubmenusDisponibles INICIO ===');

    axios.get('/submenus/todos')
        .then(response => {
            console.log('Submenus disponibles:', response.data);
            const submenus = response.data.data || [];
            const $select = $('#selectSubmenuAgregar');

            $select.empty();
            $select.append('<option value="">Seleccione un submenu para agregar...</option>');

            submenus.forEach(submenu => {
                $select.append(`<option value="${submenu.id}" data-menu="${submenu.menu_nombre}" data-ruta="${submenu.ruta}">${submenu.menu_nombre} - ${submenu.nombre}</option>`);
            });

            console.log('=== cargarSubmenusDisponibles FIN ===');
        })
        .catch(error => {
            console.error('Error al cargar submenus:', error);
        });
}

/**
 * Mostrar permisos en la tabla
 */
function mostrarPermisosEnTabla() {
    console.log('=== mostrarPermisosEnTabla INICIO ===');
    console.log('Permisos actuales a mostrar:', permisosActuales);

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
            console.log('Click en botón quitar permiso, ID:', permisoId);
            solicitarQuitarPermiso(permisoId);
        });
    }

    console.log('Tabla actualizada con', permisosActuales.length, 'permisos');
    console.log('=== mostrarPermisosEnTabla FIN ===');
}

/**
 * Agregar permiso al rol
 */
function agregarPermisoAlRol(event) {
    console.log('=== agregarPermisoAlRol INICIO ===');

    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const submenuId = parseInt($('#selectSubmenuAgregar').val());
    console.log('Submenu ID seleccionado:', submenuId);

    if (!submenuId) {
        alert('Debe seleccionar un submenu');
        return;
    }

    // Verificar si ya existe
    const yaExiste = permisosActuales.find(p => p.id === submenuId);
    if (yaExiste) {
        alert('El permiso ya está asignado al rol');
        console.log('=== agregarPermisoAlRol FIN (ya existe) ===');
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

    console.log('Permiso agregado:', { submenuId, menuNombre, submenuNombre });
    console.log('Permisos a agregar:', permisosAgregar);
    console.log('Permisos a quitar:', permisosQuitar);

    // Actualizar vista
    mostrarPermisosEnTabla();
    $('#selectSubmenuAgregar').val('');

    console.log('=== agregarPermisoAlRol FIN ===');
}

/**
 * Solicitar confirmación para quitar permiso
 */
function solicitarQuitarPermiso(permisoId) {
    console.log('=== solicitarQuitarPermiso INICIO ===');
    console.log('Permiso ID a quitar:', permisoId);
    console.log('Estado actual de permisos:', permisosActuales);

    $('#permisoQuitarId').val(permisoId);
    $('#modalConfirmarQuitarPermiso').modal('show');

    console.log('Modal de confirmación mostrado');
    console.log('=== solicitarQuitarPermiso FIN ===');
}

/**
 * Confirmar quitar permiso del rol
 */
function confirmarQuitarPermisoDelRol() {
    console.log('=== confirmarQuitarPermisoDelRol INICIO ===');

    const permisoId = parseInt($('#permisoQuitarId').val());
    console.log('Permiso ID a quitar:', permisoId);
    console.log('Permisos actuales ANTES:', permisosActuales);
    console.log('Permisos a agregar ANTES:', permisosAgregar);
    console.log('Permisos a quitar ANTES:', permisosQuitar);

    // Actualizar listas
    permisosActuales = permisosActuales.filter(p => p.id !== permisoId);
    console.log('Permisos actuales DESPUÉS de filtrar:', permisosActuales);

    // Si estaba en la lista de agregar, quitarlo
    const indexAgregar = permisosAgregar.indexOf(permisoId);
    console.log('Index en lista de agregar:', indexAgregar);

    if (indexAgregar > -1) {
        permisosAgregar.splice(indexAgregar, 1);
        console.log('Permiso quitado de lista de agregar');
    } else {
        // Si no estaba en la lista de agregar, agregarlo a la lista de quitar
        if (!permisosQuitar.includes(permisoId)) {
            permisosQuitar.push(permisoId);
            console.log('Permiso agregado a lista de quitar');
        }
    }

    console.log('Permisos a agregar DESPUÉS:', permisosAgregar);
    console.log('Permisos a quitar DESPUÉS:', permisosQuitar);

    // Cerrar modal de confirmación
    console.log('Cerrando modal de confirmación...');
    $('#modalConfirmarQuitarPermiso').modal('hide');

    // Delay para asegurar cierre limpio
    setTimeout(() => {
        console.log('Limpiando backdrops...');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        console.log('Mostrando modal principal...');
        $('#modalRol').modal('show');
    }, 300);

    // Actualizar vista
    console.log('Actualizando vista de tabla...');
    mostrarPermisosEnTabla();

    console.log('=== confirmarQuitarPermisoDelRol FIN ===');
}

// ======================================================================
// REPORTE DE ACCESOS POR ROL
// ======================================================================

/**
 * Abrir el modal de reporte de accesos
 */
function abrirReporteAccesos() {
    $('#reporteAccesosBuscar').val('');
    $('#modalReporteAccesos').modal('show');
    cargarReporteAccesos();
}

/**
 * Llamar al endpoint y renderizar el reporte
 */
function cargarReporteAccesos() {
    const cuerpo = document.getElementById('reporteAccesosCuerpo');
    cuerpo.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div>'
                     + '<p class="mt-2 text-muted small">Cargando accesos…</p></div>';
    document.getElementById('reporteAccesosTotalRoles').textContent = '-';

    axios.get('/roles/reporte-accesos')
        .then(function(response) {
            renderizarReporteAccesos(response.data.data || []);
        })
        .catch(function() {
            cuerpo.innerHTML = '<div class="alert alert-danger m-3">Error al cargar el reporte. Intente nuevamente.</div>';
        });
}

/**
 * Agrupar filas y construir el HTML del reporte
 */
function renderizarReporteAccesos(rows) {
    // Agrupar: roles[rol_id] = { nombre, menus[menu_id] = { nombre, icon, items[] } }
    const rolesMap = {};
    rows.forEach(function(row) {
        if (!rolesMap[row.rol_id]) {
            rolesMap[row.rol_id] = { nombre: row.rol_nombre, menus: {} };
        }
        if (!row.submenu_id) return;
        const mk = row.menu_id || '__sin__';
        if (!rolesMap[row.rol_id].menus[mk]) {
            rolesMap[row.rol_id].menus[mk] = {
                nombre : row.nombre_menu || '—',
                icon   : row.menu_icon  || 'fa-folder',
                items  : []
            };
        }
        rolesMap[row.rol_id].menus[mk].items.push({
            nombre : row.submenu_nombre,
            url    : row.submenu_url
        });
    });

    const roles = Object.values(rolesMap);
    document.getElementById('reporteAccesosTotalRoles').textContent = roles.length;

    let html = '';
    roles.forEach(function(rol, idx) {
        const totalAccesos = Object.values(rol.menus).reduce(function(s, m) { return s + m.items.length; }, 0);
        const cid = 'cRol' + idx;

        html += '<div class="rpt-rol-card mb-2">'
              + '  <div class="rpt-rol-header" data-toggle="collapse" data-target="#' + cid + '">'
              + '    <div class="d-flex align-items-center justify-content-between">'
              + '      <span><i class="fa fa-shield mr-2" style="color:#e67e22"></i><strong>' + rol.nombre + '</strong></span>'
              + '      <span class="badge badge-warning text-dark" style="border-radius:10px;font-size:.73rem">'
              +            totalAccesos + ' acceso' + (totalAccesos !== 1 ? 's' : '')
              + '      </span>'
              + '    </div>'
              + '  </div>'
              + '  <div class="collapse show" id="' + cid + '">'
              + '    <div class="rpt-rol-body">';

        if (totalAccesos === 0) {
            html += '<p class="text-muted small mb-0"><i class="fa fa-ban mr-1"></i>Sin accesos asignados</p>';
        } else {
            Object.values(rol.menus).forEach(function(menu) {
                html += '<div class="rpt-menu-section mb-2">'
                      + '  <div class="rpt-menu-label"><i class="fa ' + menu.icon + ' mr-1"></i>' + menu.nombre + '</div>'
                      + '  <div class="rpt-submenu-chips">';
                menu.items.forEach(function(item) {
                    html += '<span class="rpt-chip" title="/' + item.url + '">'
                          + '<i class="fa fa-link mr-1" style="font-size:.63rem;opacity:.6"></i>' + item.nombre
                          + '</span>';
                });
                html += '  </div></div>';
            });
        }

        html += '    </div></div></div>';
    });

    const cuerpo = document.getElementById('reporteAccesosCuerpo');
    cuerpo.innerHTML = html || '<p class="text-muted p-3">Sin datos disponibles.</p>';

    // Filtro de búsqueda
    const buscar = document.getElementById('reporteAccesosBuscar');
    buscar.oninput = null;
    buscar.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        cuerpo.querySelectorAll('.rpt-rol-card').forEach(function(card) {
            card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

/**
 * Imprimir el reporte en ventana nueva
 */
function imprimirReporteAccesos() {
    const contenido = document.getElementById('reporteAccesosCuerpo').innerHTML;
    const win = window.open('', '_blank', 'width=900,height=700');
    win.document.write('<html><head><title>Reporte de Accesos por Rol</title>'
        + '<style>'
        + 'body{font-family:Arial,sans-serif;font-size:12px;margin:20px}'
        + 'h2{color:#e05a00;margin-bottom:16px}'
        + '.rpt-rol-card{border:1px solid #ccc;border-radius:6px;margin-bottom:10px;page-break-inside:avoid}'
        + '.rpt-rol-header{background:#fdf4e7;padding:7px 12px;font-weight:bold}'
        + '.rpt-rol-body{padding:8px 12px}'
        + '.rpt-menu-label{font-size:10px;font-weight:bold;color:#7d3f00;text-transform:uppercase;margin-bottom:4px}'
        + '.rpt-submenu-chips{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:6px}'
        + '.rpt-chip{background:#fff8ee;border:1px solid #f2d49a;border-radius:10px;padding:1px 8px;font-size:11px}'
        + '.collapse{display:block!important}'
        + '@media print{.no-print{display:none}}'
        + '</style></head><body>'
        + '<h2>Reporte de Accesos por Rol &mdash; ' + new Date().toLocaleDateString('es-HN') + '</h2>'
        + contenido
        + '</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 400);
}

// ======================================================================
// REPORTE DE USUARIOS ACTIVOS POR ROL
// ======================================================================

/**
 * Abrir el modal de usuarios por rol
 */
function abrirReporteUsuarios() {
    $('#reporteUsuariosBuscar').val('');
    $('#modalReporteUsuarios').modal('show');
    cargarReporteUsuarios();
}

/**
 * Llamar al endpoint y renderizar el reporte
 */
function cargarReporteUsuarios() {
    const cuerpo = document.getElementById('reporteUsuariosCuerpo');
    cuerpo.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div>'
                     + '<p class="mt-2 text-muted small">Cargando usuarios…</p></div>';
    document.getElementById('reporteUsuariosTotalUsuarios').textContent = '-';
    document.getElementById('reporteUsuariosTotalRoles').textContent    = '-';

    axios.get('/roles/reporte-usuarios')
        .then(function(response) {
            renderizarReporteUsuarios(response.data.data || []);
        })
        .catch(function() {
            cuerpo.innerHTML = '<div class="alert alert-danger m-3">Error al cargar el reporte. Intente nuevamente.</div>';
        });
}

/**
 * Agrupar filas por rol y construir el HTML
 */
function renderizarReporteUsuarios(rows) {
    // Agrupar: rolesMap[rol_id] = { nombre, usuarios[] }
    const rolesMap = {};
    rows.forEach(function(row) {
        if (!rolesMap[row.rol_id]) {
            rolesMap[row.rol_id] = { nombre: row.rol_nombre, usuarios: [] };
        }
        rolesMap[row.rol_id].usuarios.push({
            id      : row.usuario_id,
            nombre  : row.usuario_nombre,
            email   : row.email,
            identidad: row.identidad || '—',
            telefono : row.telefono  || '—',
            fecha   : row.created_at ? row.created_at.substring(0, 10) : '—'
        });
    });

    const roles = Object.values(rolesMap);
    const totalUsuarios = rows.length;

    document.getElementById('reporteUsuariosTotalUsuarios').textContent = totalUsuarios;
    document.getElementById('reporteUsuariosTotalRoles').textContent    = roles.length;

    let html = '';
    roles.forEach(function(rol, idx) {
        const cid = 'cUsrRol' + idx;
        html += '<div class="rpt-rol-card mb-2">'
              + '  <div class="rpt-rol-header" data-toggle="collapse" data-target="#' + cid + '">'
              + '    <div class="d-flex align-items-center justify-content-between">'
              + '      <span><i class="fa fa-shield mr-2" style="color:#e67e22"></i><strong>' + rol.nombre + '</strong></span>'
              + '      <span class="badge badge-primary" style="border-radius:10px;font-size:.73rem">'
              +            rol.usuarios.length + ' usuario' + (rol.usuarios.length !== 1 ? 's' : '')
              + '      </span>'
              + '    </div>'
              + '  </div>'
              + '  <div class="collapse show" id="' + cid + '">'
              + '    <div class="rpt-rol-body p-0">'
              + '      <table class="table table-sm table-hover mb-0" style="font-size:.78rem">'
              + '        <thead class="thead-light"><tr>'
              + '          <th style="width:40px">#</th>'
              + '          <th>Nombre</th>'
              + '          <th>Correo</th>'
              + '          <th style="width:120px">Identidad</th>'
              + '          <th style="width:110px">Teléfono</th>'
              + '          <th style="width:100px">Ingreso</th>'
              + '        </tr></thead>'
              + '        <tbody>';

        rol.usuarios.forEach(function(u) {
            html += '<tr>'
                  + '<td class="text-muted">' + u.id + '</td>'
                  + '<td><i class="fa fa-user mr-1 text-muted" style="font-size:.7rem"></i>' + u.nombre + '</td>'
                  + '<td><small>' + u.email + '</small></td>'
                  + '<td class="text-center">' + u.identidad + '</td>'
                  + '<td class="text-center">' + u.telefono  + '</td>'
                  + '<td class="text-center">' + u.fecha     + '</td>'
                  + '</tr>';
        });

        html += '        </tbody></table>'
              + '    </div>'
              + '  </div>'
              + '</div>';
    });

    const cuerpo = document.getElementById('reporteUsuariosCuerpo');
    cuerpo.innerHTML = html || '<p class="text-muted p-3">Sin usuarios activos.</p>';

    // Filtro de búsqueda en tiempo real
    const buscar = document.getElementById('reporteUsuariosBuscar');
    buscar.oninput = null;
    buscar.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        cuerpo.querySelectorAll('.rpt-rol-card').forEach(function(card) {
            card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

// ======================================================================
// USUARIOS ADICIONALES DEL ROL (multi-rol) — NO afecta la asignación de
// rol principal (agregarUsuarioAlRol / mostrarUsuariosEnTabla arriba).
// Cada Agregar/Quitar se guarda de inmediato.
// ======================================================================

/**
 * Cargar usuarios adicionales del rol y preparar el buscador select2.
 */
function cargarUsuariosAdicionalesDelRol(rolId) {
    var $sel = $('#selectUsuarioAdicionalAgregar');
    if ($sel.data('select2')) { $sel.select2('destroy'); }
    $sel.empty();
    $sel.select2({
        placeholder: 'Buscar usuario para agregar...',
        width: '100%',
        dropdownParent: $sel.closest('.modal'),
        ajax: {
            url: '/roles/' + rolId + '/usuarios-adicionales/buscar',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data.results || [] }; }
        }
    });

    axios.get('/roles/' + rolId + '/usuarios-adicionales')
        .then(function (response) {
            mostrarUsuariosAdicionalesEnTabla(rolId, response.data.data || []);
        })
        .catch(function (error) {
            console.error('Error al cargar usuarios adicionales:', error);
        });
}

function mostrarUsuariosAdicionalesEnTabla(rolId, usuarios) {
    var $tbody = $('#listaUsuariosAdicionalesRol').empty();

    if (!usuarios.length) {
        $tbody.html('<tr><td colspan="4" class="text-center text-muted py-3"><i class="fa fa-user-tag mr-1"></i>Sin usuarios adicionales</td></tr>');
        return;
    }

    usuarios.forEach(function (u) {
        $tbody.append(
            '<tr>'
            + '<td>' + u.id + '</td>'
            + '<td>' + u.name + '</td>'
            + '<td>' + u.email + '</td>'
            + '<td class="text-center">'
            + '  <button type="button" class="btn btn-danger btn-xs btn-quitar-usuario-adicional" data-usuario-id="' + u.id + '" title="Quitar">'
            + '    <i class="fa fa-times"></i>'
            + '  </button>'
            + '</td>'
            + '</tr>'
        );
    });

    $tbody.find('.btn-quitar-usuario-adicional').off('click').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var usuarioId = $(this).data('usuario-id');
        quitarUsuarioAdicionalDelRol(rolId, usuarioId);
    });
}

function agregarUsuarioAdicionalAlRol() {
    var rolId = $('#rolId').val();
    var $sel = $('#selectUsuarioAdicionalAgregar');
    var data = $sel.select2('data');

    if (!rolId) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Guarde el rol antes de agregar usuarios adicionales.' });
        return;
    }
    if (!data || !data.length) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione un usuario para agregar.' });
        return;
    }
    var usuarioId = parseInt(data[0].id, 10);

    axios.post('/roles/' + rolId + '/usuarios-adicionales/agregar', { usuario_id: usuarioId })
        .then(function () {
            $sel.val(null).trigger('change');
            cargarUsuariosAdicionalesDelRol(rolId);
        })
        .catch(function (error) {
            var mensaje = error.response?.data?.mensaje || 'No se pudo agregar el usuario.';
            Swal.fire({ icon: 'error', title: 'Error', text: mensaje });
        });
}

function quitarUsuarioAdicionalDelRol(rolId, usuarioId) {
    Swal.fire({
        title: '¿Quitar usuario adicional?',
        text: 'El usuario perderá los accesos que le otorgaba este rol adicional.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;
        axios.post('/roles/' + rolId + '/usuarios-adicionales/quitar', { usuario_id: usuarioId })
            .then(function () { cargarUsuariosAdicionalesDelRol(rolId); })
            .catch(function (error) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo quitar el usuario.' });
                console.error(error);
            });
    });
}

