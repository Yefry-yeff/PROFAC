// Variable global para la tabla
let tablaComisiones = null;

$(document).ready(function () {
    // Configuración de axios
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
    }

    // Inicializar Select2 para tipo de reporte
    $('#tipoReporte').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione tipo de reporte',
        allowClear: true
    });

    // Establecer fechas por defecto (último mes)
    const hoy = new Date();
    const hace30Dias = new Date();
    hace30Dias.setDate(hoy.getDate() - 30);
    
    $('#fechaFin').val(hoy.toISOString().split('T')[0]);
    $('#fechaInicio').val(hace30Dias.toISOString().split('T')[0]);

    // Evento cuando cambia el tipo de reporte
    $('#tipoReporte').on('change', function() {
        const tipo = $(this).val();
        const container = $('#containerFiltroEspecifico');
        const label = $('#labelFiltroEspecifico');
        const select = $('#filtroEspecifico');
        
        // Limpiar select
        select.empty().trigger('change');
        
        if (tipo === 'empleado') {
            // Mostrar selector de empleados
            label.text('Seleccionar Empleado');
            container.show();
            cargarSelectEmpleados();
        } else if (tipo === 'rol') {
            // Mostrar selector de roles
            label.text('Seleccionar Rol');
            container.show();
            cargarSelectRoles();
        } else {
            // Ocultar selector para reportes generales
            container.hide();
        }
    });

    // Botón filtrar
    $('#btnFiltrar').on('click', function() {
        const tipoReporte = $('#tipoReporte').val();
        
        if (!tipoReporte) {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione un tipo de reporte',
                text: 'Debe seleccionar el tipo de reporte que desea generar'
            });
            return;
        }
        
        cargarReporte();
    });

    // Botón descargar
    $('#btnDescargar').on('click', function() {
        const tipoReporte = $('#tipoReporte').val();
        
        if (!tipoReporte) {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione un tipo de reporte',
                text: 'Debe seleccionar el tipo de reporte que desea descargar'
            });
            return;
        }
        
        descargarExcel();
    });
});

// Cargar selector de empleados
function cargarSelectEmpleados() {
    $('#filtroEspecifico').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un empleado',
        allowClear: true,
        ajax: {
            url: '/comision/empleados/lista',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.name
                        };
                    })
                };
            }
        }
    });
}

// Cargar selector de roles
function cargarSelectRoles() {
    $('#filtroEspecifico').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un rol',
        allowClear: true,
        ajax: {
            url: '/comision/roles/lista',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.name
                        };
                    })
                };
            }
        }
    });
}

// Cargar reporte según tipo
function cargarReporte() {
    const tipoReporte = $('#tipoReporte').val();
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const filtroEspecifico = $('#filtroEspecifico').val();
    
    // Validar fechas
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas requeridas',
            text: 'Debe seleccionar fecha de inicio y fin'
        });
        return;
    }
    
    // Destruir tabla existente completamente
    if (tablaComisiones) {
        tablaComisiones.destroy();
        tablaComisiones = null;
    }
    
    // Limpiar completamente el tbody
    $('#tbl_comisiones tbody').empty();
    
    // Configurar encabezados según tipo
    let columns = [];
    let titulo = '';
    let endpoint = '';
    
    switch(tipoReporte) {
        case 'empleado':
            titulo = 'Comisiones por Empleado';
            endpoint = '/comision/reporte/empleado';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'empleado' },
                { data: 'factura' },
                { data: 'producto' },
                { data: 'cantidad', className: 'text-right' },
                { data: 'monto_comision', className: 'text-right' },
                { data: 'fecha' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Factura</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Comisión</th>
                    <th>Fecha</th>
                </tr>
            `);
            break;
            
        case 'rol':
            titulo = 'Comisiones por Rol';
            endpoint = '/comision/reporte/rol';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'rol' },
                { data: 'empleado' },
                { data: 'total_comisiones', className: 'text-right' },
                { data: 'num_facturas', className: 'text-center' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Rol</th>
                    <th>Empleado</th>
                    <th>Total Comisiones</th>
                    <th># Facturas</th>
                </tr>
            `);
            break;
            
        case 'usuarios':
            titulo = 'General de Usuarios';
            endpoint = '/comision/reporte/usuarios';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'usuario' },
                { data: 'rol' },
                { data: 'total_comisiones', className: 'text-right' },
                { data: 'num_facturas', className: 'text-center' },
                { data: 'num_productos', className: 'text-center' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Total Comisiones</th>
                    <th># Facturas</th>
                    <th># Productos</th>
                </tr>
            `);
            break;
            
        case 'productos':
            titulo = 'General por Producto';
            endpoint = '/comision/reporte/productos';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'producto' },
                { data: 'codigo_barra' },
                { data: 'cantidad_vendida', className: 'text-right' },
                { data: 'total_comisiones', className: 'text-right' },
                { data: 'num_empleados', className: 'text-center' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Código Barra</th>
                    <th>Cantidad Vendida</th>
                    <th>Total Comisiones</th>
                    <th># Empleados</th>
                </tr>
            `);
            break;
            
        case 'facturas':
            titulo = 'General por Factura';
            endpoint = '/comision/reporte/facturas';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'factura' },
                { data: 'cliente' },
                { data: 'empleado' },
                { data: 'total_venta', className: 'text-right' },
                { data: 'total_comision', className: 'text-right' },
                { data: 'fecha' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Empleado</th>
                    <th>Total Venta</th>
                    <th>Total Comisión</th>
                    <th>Fecha</th>
                </tr>
            `);
            break;
    }
    
    $('#tituloTabla').text(titulo);
    
    // Asegurarse de que el DOM está listo antes de inicializar DataTable
    setTimeout(function() {
        // Inicializar DataTable
        tablaComisiones = $('#tbl_comisiones').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            destroy: true, // Asegurar destrucción automática
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>'
            },
            ajax: {
            url: endpoint,
            type: 'GET',
            data: function(d) {
                d.fechaInicio = fechaInicio;
                d.fechaFin = fechaFin;
                d.filtroEspecifico = filtroEspecifico;
            },
            error: function(xhr, error, thrown) {
                console.error('Error al cargar reporte:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el reporte'
                });
            }
        },
        columns: columns,
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        responsive: true
        });
    }, 100); // Pequeño delay para asegurar que el thead se actualice
}

// Descargar Excel
function descargarExcel() {
    const tipoReporte = $('#tipoReporte').val();
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const filtroEspecifico = $('#filtroEspecifico').val();
    
    // Validar fechas
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas requeridas',
            text: 'Debe seleccionar fecha de inicio y fin'
        });
        return;
    }
    
    // Construir URL con parámetros
    const params = new URLSearchParams({
        tipoReporte: tipoReporte,
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        filtroEspecifico: filtroEspecifico || ''
    });
    
    const fecha = new Date().toISOString().split('T')[0];
    const url = `/comision/reporte/excel?${params.toString()}`;
    
    // Crear enlace temporal y descargar
    const link = document.createElement('a');
    link.href = url;
    link.download = `reporte_comisiones_${tipoReporte}_${fecha}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
        icon: 'success',
        title: 'Descargando...',
        text: 'Se está descargando el archivo Excel',
        timer: 2000,
        showConfirmButton: false
    });
}
