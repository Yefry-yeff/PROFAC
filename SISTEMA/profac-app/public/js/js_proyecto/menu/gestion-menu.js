// Gestión de Menús y Submenus
$(document).ready(function() {
    // Inicializar DataTables
    $('#tablaMenus').DataTable({
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        "order": [[3, 'asc']], // Ordenar por columna Orden
        "pageLength": 5,
        "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]]
    });

    $('#tablaSubmenus').DataTable({
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        "order": [[1, 'asc'], [5, 'asc']], // Ordenar por Menú y Orden
        "pageLength": 5,
        "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]]
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

    // Mostrar/ocultar campo URL según checkbox
    $('#generarArchivos').on('change', function() {
        if ($(this).is(':checked')) {
            $('#campoUrlRuta').show();
            if (!$('#submenuUrl').val()) {
                autoGenerarUrl();
            }
        } else {
            $('#campoUrlRuta').hide();
        }
    });

    // Auto-generar URL cuando cambia el menú principal
    $('#submenuMenuId').on('change', function() {
        if ($('#generarArchivos').is(':checked') && !$('#submenuUrl').data('editado')) {
            autoGenerarUrl();
        }
    });

    // Auto-generar URL desde el nombre si checkbox activo y URL no fue editada manualmente
    $('#submenuNombre').on('input', function() {
        if ($('#generarArchivos').is(':checked') && !$('#submenuUrl').data('editado')) {
            autoGenerarUrl();
        }
    });

    // Marcar URL como editada manualmente
    $('#submenuUrl').on('input', function() {
        $(this).data('editado', $(this).val() !== '' && $(this).val() !== autoGenerarSlug($('#submenuNombre').val()));
    });

    // Preview de icono en tiempo real
    $('#submenuIcono').on('input', function() {
        actualizarPreviewIcono(this.value, 'previewSubmenuIcono');
        var estilosPro = ['fa-duotone', 'fa-light', 'fa-thin'];
        var valor = extraerClaseIcono(this.value);
        var esPro = estilosPro.some(function(e) { return valor.indexOf(e) !== -1; });
        $('#alertaIconoPro').toggle(esPro);
    });
    $('#menuIcono').on('input', function() {
        actualizarPreviewIcono(this.value, 'previewMenuIcono');
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
    actualizarPreviewIcono('', 'previewMenuIcono');
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
            actualizarPreviewIcono(menu.icon || '', 'previewMenuIcono');
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
        icon: extraerClaseIcono($('#menuIcono').val()),
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
    $('#submenuUrl').val('').data('editado', false).prop('readonly', false).removeClass('bg-light');
    $('#submenuIcono').val('');
    $('#submenuOrden').val('');
    $('#submenuEstado').val('1');
    $('.rol-checkbox').prop('checked', false);
    $('#generarArchivos').prop('checked', true);
    $('#campoUrlRuta').show();
    $('#iconoBloqueoUrl').hide();
    $('#hintUrlNuevo').show();
    $('#hintUrlEdicion').hide();
    actualizarPreviewIcono('', 'previewSubmenuIcono');
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
            
            $('#generarArchivos').prop('checked', false);
            $('#campoUrlRuta').show();
            $('#submenuUrl').prop('readonly', true).addClass('bg-light').data('editado', true);
            $('#iconoBloqueoUrl').show();
            $('#hintUrlNuevo').hide();
            $('#hintUrlEdicion').show();
            actualizarPreviewIcono(submenu.icono || '', 'previewSubmenuIcono');
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
        icono: extraerClaseIcono($('#submenuIcono').val()),
        orden: $('#submenuOrden').val(),
        estado_id: $('#submenuEstado').val(),
        roles: rolesSeleccionados,
        generar_archivos: $('#generarArchivos').is(':checked')
    };

    const url = submenuId ? `/submenu/actualizar/${submenuId}` : '/submenu/guardar';
    const metodo = submenuId ? 'put' : 'post';

    axios[metodo](url, datos)
        .then(response => {
            // Cerrar el modal primero
            $('#modalSubmenu').modal('hide');
            
            // Esperar a que el modal se cierre completamente antes de mostrar SweetAlert
            $('#modalSubmenu').on('hidden.bs.modal', function () {
                let mensajeCompleto = response.data.mensaje;
                
                // Si se generaron archivos, mostrar información adicional
                if (response.data.generacion) {
                    const gen = response.data.generacion;
                    
                    if (gen.archivos_creados && gen.archivos_creados.length > 0) {
                        mensajeCompleto += '\n\n📁 Archivos creados:\n' + gen.archivos_creados.map(f => '✓ ' + f).join('\n');
                    }
                    
                    if (gen.ruta_generada) {
                        mensajeCompleto += '\n\n🔗 Agrega esta ruta a routes/web.php:\n' + gen.ruta_generada;
                    }
                    
                    if (gen.errores && gen.errores.length > 0) {
                        mensajeCompleto += '\n\n⚠️ Advertencias:\n' + gen.errores.join('\n');
                    }
                }
                
                Swal.fire({
                    title: 'Éxito',
                    text: mensajeCompleto,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    width: '600px'
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

/**
 * Extrae la clase CSS de un icono: acepta clase directa ("fa fa-home")
 * o HTML completo ('<i class="fa-duotone fa-light fa-home"></i>')
 */
function extraerClaseIcono(valor) {
    if (!valor) return '';
    var match = valor.match(/<i[^>]*class="([^"]+)"/);
    return match ? match[1].trim() : valor.trim();
}

/**
 * Actualiza el preview del icono en el recuadro correspondiente.
 * Espera que existan: #{previewId}El (el <i>) y #{previewId}Clase (el <code>)
 */
function actualizarPreviewIcono(valor, previewId) {
    var clase = extraerClaseIcono(valor);
    if (clase) {
        $('#' + previewId + 'El').attr('class', clase);
        $('#' + previewId + 'Clase').text(clase);
    } else {
        $('#' + previewId + 'El').attr('class', 'fa fa-question-circle text-muted');
        $('#' + previewId + 'Clase').text('escribe un icono…');
    }
}

/**
 * Genera un slug URL a partir de texto (sin acentos, minúsculas, guiones bajos)
 */
function autoGenerarSlug(texto) {
    return (texto || '').toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s\/]/g, '')
        .trim()
        .replace(/\s+/g, '_');
}

/**
 * Auto-rellena el campo URL desde menú + nombre del submenú
 */
function autoGenerarUrl() {
    var menuTexto = $('#submenuMenuId option:selected').text().replace('\u2014 Seleccione \u2014', '').trim();
    var slugMenu   = autoGenerarSlug(menuTexto);
    var slugNombre = autoGenerarSlug($('#submenuNombre').val());
    var url = (slugMenu && slugNombre) ? slugMenu + '/' + slugNombre : slugNombre;
    $('#submenuUrl').val(url);
}
