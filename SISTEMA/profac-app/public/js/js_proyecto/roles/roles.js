// ======================================================================
// GESTIÓN DE ROLES - JavaScript
// ======================================================================

$(document).ready(function() {
    // Inicializar DataTable
    inicializarDataTable();

    // Manejar envío de formulario
    $('#formRol').on('submit', function(e) {
        e.preventDefault();
        if ($(this).parsley().isValid()) {
            guardarRol();
        }
    });
});

/**
 * Inicializar DataTable con datos dinámicos
 */
function inicializarDataTable() {
    $('#tablaRoles').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/roles/listar',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('Error al cargar datos:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos de roles'
                });
            }
        },
        columns: [
            { data: 'id', name: 'id', width: '5%' },
            { data: 'nombre', name: 'nombre', width: '25%' },
            { data: 'estado_badge', name: 'estado_badge', orderable: false, searchable: false, width: '10%' },
            { data: 'total_usuarios', name: 'total_usuarios', width: '10%', className: 'text-center' },
            { data: 'total_permisos', name: 'total_permisos', width: '10%', className: 'text-center' },
            { data: 'fecha', name: 'fecha', width: '15%', className: 'text-center' },
            { data: 'opciones', name: 'opciones', orderable: false, searchable: false, width: '15%', className: 'text-center' }
        ],
        order: [[1, 'asc']], // Ordenar por nombre
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
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
    $('#formRol').parsley().reset();
    $('#modalRol').modal('show');
}

/**
 * Editar rol existente
 */
function editarRol(idRol) {
    $('#modalSpinnerLoading').modal('show');
    
    axios.get(`/roles/obtener/${idRol}`)
        .then(response => {
            const rol = response.data.data;
            
            $('#rolId').val(rol.id);
            $('#rolNombre').val(rol.nombre);
            $('#rolEstado').val(rol.estado_id);
            $('#tituloModalRol').text('Editar Rol');
            $('#formRol').parsley().reset();
            
            $('#modalSpinnerLoading').modal('hide');
            $('#modalRol').modal('show');
        })
        .catch(error => {
            $('#modalSpinnerLoading').modal('hide');
            console.error('Error al cargar rol:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.mensaje || 'No se pudo cargar el rol'
            });
        });
}

/**
 * Guardar o actualizar rol
 */
function guardarRol() {
    const rolId = $('#rolId').val();
    const datos = {
        nombre: $('#rolNombre').val().trim(),
        estado_id: $('#rolEstado').val()
    };

    const url = rolId ? `/roles/actualizar/${rolId}` : '/roles/guardar';
    const metodo = rolId ? 'put' : 'post';

    $('#modalRol').modal('hide');
    $('#modalSpinnerLoading').modal('show');

    axios[metodo](url, datos)
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
            
            // Limpiar formulario
            $('#formRol')[0].reset();
            $('#formRol').parsley().reset();
        })
        .catch(error => {
            $('#modalSpinnerLoading').modal('hide');
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
