// Gestión de Menús y Submenus
$(document).ready(function() {
    // Inicializar DataTables
    $('#tablaMenus').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[3, 'asc']], // Ordenar por columna Orden
        "pageLength": 25
    });

    $('#tablaSubmenus').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[1, 'asc'], [5, 'asc']], // Ordenar por Menú y Orden
        "pageLength": 25
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
});

/**
 * Abrir modal para crear nuevo menú
 */
function abrirModalMenu() {
    $('#menuId').val('');
    $('#menuNombre').val('');
    $('#menuIcono').val('');
    $('#menuOrden').val('');
    $('#menuEstado').val('1');
    $('#tituloModalMenu').text('Nuevo Menú');
    $('#modalMenu').modal('show');
}

/**
 * Editar menú existente
 */
function editarMenu(idMenu) {
    axios.get(`/menu/obtener/${idMenu}`)
        .then(response => {
            const menu = response.data;
            $('#menuId').val(menu.id);
            $('#menuNombre').val(menu.nombre_menu);
            $('#menuIcono').val(menu.icon);
            $('#menuOrden').val(menu.orden);
            $('#menuEstado').val(menu.estado_id);
            $('#tituloModalMenu').text('Editar Menú');
            $('#modalMenu').modal('show');
        })
        .catch(error => {
            console.error('Error al cargar menú:', error);
            Swal.fire('Error', 'No se pudo cargar el menú', 'error');
        });
}

/**
 * Guardar o actualizar menú
 */
function guardarMenu() {
    const menuId = $('#menuId').val();
    const datos = {
        nombre_menu: $('#menuNombre').val(),
        icon: $('#menuIcono').val(),
        orden: $('#menuOrden').val(),
        estado_id: $('#menuEstado').val()
    };

    const url = menuId ? `/menu/actualizar/${menuId}` : '/menu/guardar';
    const metodo = menuId ? 'put' : 'post';

    axios[metodo](url, datos)
        .then(response => {
            // Cerrar el modal primero
            $('#modalMenu').modal('hide');
            
            // Esperar a que el modal se cierre completamente antes de mostrar SweetAlert
            $('#modalMenu').on('hidden.bs.modal', function () {
                Swal.fire({
                    title: 'Éxito',
                    text: response.data.mensaje || 'Menú guardado correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    location.reload();
                });
                // Remover el event listener para evitar duplicados
                $(this).off('hidden.bs.modal');
            });
        })
        .catch(error => {
            console.error('Error al guardar menú:', error);
            Swal.fire('Error', error.response?.data?.mensaje || 'No se pudo guardar el menú', 'error');
        });
}

/**
 * Abrir modal para crear nuevo submenu
 */
function abrirModalSubmenu() {
    $('#submenuId').val('');
    $('#submenuMenuId').val('');
    $('#submenuNombre').val('');
    $('#submenuUrl').val('');
    $('#submenuIcono').val('');
    $('#submenuOrden').val('');
    $('#submenuEstado').val('1');
    $('.rol-checkbox').prop('checked', false);
    $('#tituloModalSubmenu').text('Nuevo Submenu');
    $('#modalSubmenu').modal('show');
}

/**
 * Editar submenu existente
 */
function editarSubmenu(idSubmenu) {
    axios.get(`/submenu/obtener/${idSubmenu}`)
        .then(response => {
            const submenu = response.data;
            $('#submenuId').val(submenu.id);
            $('#submenuMenuId').val(submenu.menu_id);
            $('#submenuNombre').val(submenu.nombre);
            $('#submenuUrl').val(submenu.url);
            $('#submenuIcono').val(submenu.icono);
            $('#submenuOrden').val(submenu.orden);
            $('#submenuEstado').val(submenu.estado_id);
            
            // Marcar roles asociados
            $('.rol-checkbox').prop('checked', false);
            if (submenu.roles && Array.isArray(submenu.roles)) {
                submenu.roles.forEach(rol => {
                    $(`#rol${rol.id}`).prop('checked', true);
                });
            }
            
            $('#tituloModalSubmenu').text('Editar Submenu');
            $('#modalSubmenu').modal('show');
        })
        .catch(error => {
            console.error('Error al cargar submenu:', error);
            Swal.fire('Error', 'No se pudo cargar el submenu', 'error');
        });
}

/**
 * Guardar o actualizar submenu
 */
function guardarSubmenu() {
    const submenuId = $('#submenuId').val();
    
    // Obtener roles seleccionados
    const rolesSeleccionados = [];
    $('.rol-checkbox:checked').each(function() {
        rolesSeleccionados.push($(this).val());
    });

    if (rolesSeleccionados.length === 0) {
        Swal.fire('Advertencia', 'Debe seleccionar al menos un rol', 'warning');
        return;
    }

    const datos = {
        menu_id: $('#submenuMenuId').val(),
        nombre: $('#submenuNombre').val(),
        url: $('#submenuUrl').val(),
        icono: $('#submenuIcono').val(),
        orden: $('#submenuOrden').val(),
        estado_id: $('#submenuEstado').val(),
        roles: rolesSeleccionados
    };

    const url = submenuId ? `/submenu/actualizar/${submenuId}` : '/submenu/guardar';
    const metodo = submenuId ? 'put' : 'post';

    axios[metodo](url, datos)
        .then(response => {
            // Cerrar el modal primero
            $('#modalSubmenu').modal('hide');
            
            // Esperar a que el modal se cierre completamente antes de mostrar SweetAlert
            $('#modalSubmenu').on('hidden.bs.modal', function () {
                Swal.fire({
                    title: 'Éxito',
                    text: response.data.mensaje || 'Submenu guardado correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    location.reload();
                });
                // Remover el event listener para evitar duplicados
                $(this).off('hidden.bs.modal');
            });
        })
        .catch(error => {
            console.error('Error al guardar submenu:', error);
            Swal.fire('Error', error.response?.data?.mensaje || 'No se pudo guardar el submenu', 'error');
        });
}
