$(document).ready(function () {
    obtenerListaEmpleados();
});

function obtenerListaEmpleados() {
    $('#empleado').select2({
        placeholder: 'Seleccione un empleado',
        allowClear: true,
        ajax: {
            url: '/comision/reporte/empleados-lista',
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

function obtenerListaRoles() {
    $('#rol').select2({
        placeholder: 'Seleccione un rol',
        allowClear: true,
        ajax: {
            url: '/comision/reporte/rol',
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
                        results: data.name.map(function (item) {
                            return {
                                id: item.id,
                                text: item.nombre
                            };
                        })
                    };
                }
        }
    });
}
