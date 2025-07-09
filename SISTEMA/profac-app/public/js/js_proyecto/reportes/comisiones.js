
/* COMISIONES */
 $('#vendedor').select2({
                ajax:{
                    url:'/ventas/corporativo/vendedores',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    }

                }
            });
/* */



function exportarPdf() {
    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFinal = document.getElementById('fecha_final').value;

    if (!fechaInicio || !fechaFinal) {
        document.getElementById('fecha_inicio').style.borderColor = 'red';
        document.getElementById('fecha_final').style.borderColor = 'red';
        return;
    }

    document.getElementById('fecha_inicio').style.borderColor = '';
    document.getElementById('fecha_final').style.borderColor = '';

    var fechaInicioFormat = new Date(fechaInicio).toISOString().split('T')[0];
    var fechaFinalFormat = new Date(fechaFinal).toISOString().split('T')[0];

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        console.error("No se encontró el token CSRF.");
        return;
    }

    var csrfToken = csrfMeta.getAttribute('content');

    // Configurar el formulario de envío POST
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Librocobrosrep/exportar-pdf/3/' + encodeURIComponent(fechaInicioFormat) + '/' + encodeURIComponent(fechaFinalFormat);

    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}

function exportarExcel() {
    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFinal = document.getElementById('fecha_final').value;

    if (!fechaInicio || !fechaFinal) {
        document.getElementById('fecha_inicio').style.borderColor = 'red';
        document.getElementById('fecha_final').style.borderColor = 'red';
        return;
    }

    document.getElementById('fecha_inicio').style.borderColor = '';
    document.getElementById('fecha_final').style.borderColor = '';

    var fechaInicioFormat = new Date(fechaInicio).toISOString().split('T')[0];
    var fechaFinalFormat = new Date(fechaFinal).toISOString().split('T')[0];

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        console.error("No se encontró el token CSRF.");
        return;
    }

    var csrfToken = csrfMeta.getAttribute('content');

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Librocobrosrep/exportar-excel/3/' + encodeURIComponent(fechaInicioFormat) + '/' + encodeURIComponent(fechaFinalFormat);

    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}
