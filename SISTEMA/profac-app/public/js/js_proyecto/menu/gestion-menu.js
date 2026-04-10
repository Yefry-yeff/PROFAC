// Gestión de Menús y Submenus
$(document).ready(function() {
    // Inicializar DataTables
    $('#tablaMenus').DataTable({
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        "order": [[3, 'asc']],
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    $('#tablaSubmenus').DataTable({
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        "order": [[1, 'asc'], [5, 'asc']],
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    // Manejar envío de formulario de Menú
    $('#formMenu').on('submit', function(e) {
        e.preventDefault();
        guardarMenu();
    });

    // Manejar envío de formulario de Submenu
    $('#formSubmenu').on('submit', function(e) {
        e.preventDefault();
        guardarSubmenu();
    });

    // Toggle campo URL según checkbox "Generar archivos"
    $('#generarArchivos').on('change', function() {
        if ($(this).is(':checked')) {
            $('#campoUrlRuta').show();
            $('#submenuUrl').prop('required', true);
        } else {
            $('#campoUrlRuta').hide();
            $('#submenuUrl').prop('required', false).val('');
        }
    });
});

// ==================== MENÚ ====================

function abrirModalMenu() {
    $('#menuId').val('');
    $('#menuNombre').val('');
    $('#menuIcono').val('');
    $('#menuOrden').val('');
    $('#menuEstado').val('1');
    $('#seccionSubmenusMenu').hide();
    $('#tbodySubmenusDelMenu').empty();
    $('#tituloModalMenu').text('Nuevo Menú');
    $('#modalMenu').modal('show');
}

function editarMenu(idMenu) {
    axios.get('/menu/obtener/' + idMenu)
        .then(function(response) {
            var menu = response.data;
            $('#menuId').val(menu.id);
            $('#menuNombre').val(menu.nombre_menu);
            $('#menuIcono').val(menu.icon);
            $('#menuOrden').val(menu.orden);
            $('#menuEstado').val(menu.estado_id);
            $('#tituloModalMenu').text('Editar Menú');

            // Cargar submenus de este menú
            cargarSubmenusDelMenu(menu.id);

            $('#modalMenu').modal('show');
        })
        .catch(function(error) {
            console.error('Error al cargar menú:', error);
            Swal.fire('Error', 'No se pudo cargar el menú', 'error');
        });
}

function cargarSubmenusDelMenu(menuId) {
    axios.get('/menu/obtener/' + menuId + '/submenus')
        .then(function(response) {
            var submenus = response.data;
            var tbody = $('#tbodySubmenusDelMenu');
            tbody.empty();

            if (submenus.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center text-muted">Sin submenús</td></tr>');
            } else {
                submenus.forEach(function(sub) {
                    var estadoBadge = sub.estado_id == 1
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-danger">Inactivo</span>';
                    var btnTexto = sub.estado_id == 1 ? 'Inactivar' : 'Activar';
                    var btnClase = sub.estado_id == 1 ? 'btn-danger' : 'btn-success';

                    tbody.append(
                        '<tr>' +
                        '<td>' + sub.nombre + '</td>' +
                        '<td><code>' + (sub.url || '') + '</code></td>' +
                        '<td>' + sub.orden + '</td>' +
                        '<td>' + estadoBadge + '</td>' +
                        '<td><button class="btn ' + btnClase + ' btn-xs" onclick="toggleEstadoSubmenuDesdeMenu(' + sub.id + ', ' + menuId + ')">' + btnTexto + '</button></td>' +
                        '</tr>'
                    );
                });
            }
            $('#seccionSubmenusMenu').show();
        })
        .catch(function(error) {
            console.error('Error al cargar submenus:', error);
            $('#seccionSubmenusMenu').hide();
        });
}

function toggleEstadoSubmenuDesdeMenu(submenuId, menuId) {
    axios.post('/submenu/toggle-estado/' + submenuId)
        .then(function(response) {
            cargarSubmenusDelMenu(menuId);
            Swal.fire({ icon: 'success', title: 'Estado actualizado', timer: 1200, showConfirmButton: false });
        })
        .catch(function(error) {
            Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
        });
}

function guardarMenu() {
    var menuId = $('#menuId').val();
    var datos = {
        nombre_menu: $('#menuNombre').val(),
        icon: $('#menuIcono').val(),
        orden: $('#menuOrden').val(),
        estado_id: $('#menuEstado').val()
    };

    var url = menuId ? '/menu/actualizar/' + menuId : '/menu/guardar';
    var metodo = menuId ? 'put' : 'post';

    axios[metodo](url, datos)
        .then(function(response) {
            $('#modalMenu').modal('hide');
            $('#modalMenu').on('hidden.bs.modal', function () {
                Swal.fire({
                    title: 'Éxito',
                    text: response.data.mensaje || 'Menú guardado correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(function() {
                    location.reload();
                });
                $(this).off('hidden.bs.modal');
            });
        })
        .catch(function(error) {
            console.error('Error al guardar menú:', error);
            Swal.fire('Error', error.response?.data?.mensaje || 'No se pudo guardar el menú', 'error');
        });
}

// ==================== SUBMENU ====================

var modoEdicionSubmenu = false;

function abrirModalSubmenu() {
    modoEdicionSubmenu = false;
    $('#submenuId').val('');
    $('#submenuMenuId').val('').prop('disabled', false);
    $('#submenuNombre').val('').prop('readonly', false);
    $('#submenuUrl').val('');
    $('#submenuIcono').val('').prop('readonly', false);
    $('#submenuOrden').val('').prop('readonly', false);
    $('#submenuEstado').val('1').prop('disabled', false);
    $('.rol-checkbox').prop('checked', false).prop('disabled', false);
    $('#generarArchivos').prop('checked', false).parent().parent().show();
    $('#campoUrlRuta').hide();
    $('#submenuUrl').prop('required', false);

    // Reset a primera pestaña
    $('#tab-datos-generales').tab('show');

    // Habilitar campos de Datos Técnicos
    $('#submenuIcono').prop('readonly', false);

    $('#tituloModalSubmenu').text('Nuevo Submenu');
    $('#modalSubmenu').modal('show');
}

function editarSubmenu(idSubmenu) {
    modoEdicionSubmenu = true;
    axios.get('/submenu/obtener/' + idSubmenu)
        .then(function(response) {
            var submenu = response.data;
            $('#submenuId').val(submenu.id);
            $('#submenuMenuId').val(submenu.menu_id).prop('disabled', false);
            $('#submenuNombre').val(submenu.nombre).prop('readonly', false);
            $('#submenuOrden').val(submenu.orden).prop('readonly', false);
            $('#submenuEstado').val(submenu.estado_id).prop('disabled', false);

            // Marcar roles asociados
            $('.rol-checkbox').prop('checked', false).prop('disabled', false);
            if (submenu.roles && Array.isArray(submenu.roles)) {
                submenu.roles.forEach(function(rol) {
                    $('#rol' + rol.id).prop('checked', true);
                });
            }

            // Datos Técnicos — solo lectura en edición
            $('#submenuIcono').val(submenu.icono || '').prop('readonly', true);
            $('#generarArchivos').prop('checked', false).parent().parent().hide();
            $('#campoUrlRuta').hide();

            // Si tiene URL, mostrarla como referencia
            if (submenu.url) {
                $('#campoUrlRuta').show();
                $('#submenuUrl').val(submenu.url).prop('readonly', true).prop('required', false);
            }

            // Reset a primera pestaña
            $('#tab-datos-generales').tab('show');

            $('#tituloModalSubmenu').text('Editar Submenu');
            $('#modalSubmenu').modal('show');
        })
        .catch(function(error) {
            console.error('Error al cargar submenu:', error);
            Swal.fire('Error', 'No se pudo cargar el submenu', 'error');
        });
}

function guardarSubmenu() {
    var submenuId = $('#submenuId').val();

    // Obtener roles seleccionados
    var rolesSeleccionados = [];
    $('.rol-checkbox:checked').each(function() {
        rolesSeleccionados.push($(this).val());
    });

    if (rolesSeleccionados.length === 0) {
        Swal.fire('Advertencia', 'Debe seleccionar al menos un rol', 'warning');
        return;
    }

    var datos = {
        menu_id: $('#submenuMenuId').val(),
        nombre: $('#submenuNombre').val(),
        url: $('#submenuUrl').val() || null,
        icono: $('#submenuIcono').val(),
        orden: $('#submenuOrden').val(),
        estado_id: $('#submenuEstado').val(),
        roles: rolesSeleccionados,
        generar_archivos: !modoEdicionSubmenu && $('#generarArchivos').is(':checked')
    };

    var url = submenuId ? '/submenu/actualizar/' + submenuId : '/submenu/guardar';
    var metodo = submenuId ? 'put' : 'post';

    axios[metodo](url, datos)
        .then(function(response) {
            $('#modalSubmenu').modal('hide');

            $('#modalSubmenu').on('hidden.bs.modal', function () {
                var mensajeCompleto = response.data.mensaje;

                if (response.data.generacion) {
                    var gen = response.data.generacion;

                    if (gen.archivos_creados && gen.archivos_creados.length > 0) {
                        mensajeCompleto += '\n\n Archivos creados:\n' + gen.archivos_creados.map(function(f) { return '- ' + f; }).join('\n');
                    }

                    if (gen.ruta_generada) {
                        mensajeCompleto += '\n\n Ruta agregada a web.php:\n' + gen.ruta_generada;
                    }

                    if (gen.errores && gen.errores.length > 0) {
                        mensajeCompleto += '\n\n Advertencias:\n' + gen.errores.join('\n');
                    }
                }

                Swal.fire({
                    title: 'Éxito',
                    text: mensajeCompleto,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    width: '600px'
                }).then(function() {
                    location.reload();
                });
                $(this).off('hidden.bs.modal');
            });
        })
        .catch(function(error) {
            console.error('Error al guardar submenu:', error);
            Swal.fire('Error', error.response?.data?.mensaje || 'No se pudo guardar el submenu', 'error');
        });
}
