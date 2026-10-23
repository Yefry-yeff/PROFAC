var tablaBoletas = null;
var seleccionBoletas = new Set();
var filasBoletas = [];
var accionBoletas = null;

function errorBoleta(error) {
    var datos = error.response && error.response.data;
    return datos && datos.errors ? Object.values(datos.errors).flat().join(' ')
        : (datos && datos.message ? datos.message : 'No se pudo completar la operacion.');
}

function actualizarSeleccionBoletas() {
    var activas = filasBoletas.filter(b => Number(b.estado) === 1);
    var seleccionadas = activas.filter(b => seleccionBoletas.has(String(b.id)));
    $('#boletasSeleccionadas').text(seleccionadas.length + ' seleccionadas');
    $('#incluirBoletas').prop('disabled', !seleccionadas.some(b => !Number(b.incluir_reporte_compras)));
    $('#quitarBoletas').prop('disabled', !seleccionadas.some(b => Number(b.incluir_reporte_compras)));
    $('#seleccionarBoletas').prop('disabled', !activas.length)
        .prop('checked', activas.length > 0 && seleccionadas.length === activas.length)
        .prop('indeterminate', seleccionadas.length > 0 && seleccionadas.length < activas.length);
    $('.boleta-check').each(function () {
        $(this).prop('checked', seleccionBoletas.has(this.value));
    });
}

function buscarBoletas() {
    var filtros = {
        fechaInicio: $('#fechaInicio').val(), fechaFinal: $('#fechaFinal').val(),
        buscar: $('#buscarBoletasTexto').val()
    };
    if (!filtros.fechaInicio || !filtros.fechaFinal || filtros.fechaInicio > filtros.fechaFinal) {
        $('#errorBoletas').removeClass('d-none').text('Seleccione un rango de fechas valido.');
        return;
    }
    $('#excelBoletas').addClass('disabled').attr('aria-disabled', 'true');
    $('#incluirBoletas, #quitarBoletas, #seleccionarBoletas').prop('disabled', true);
    axios.post('/boleta/compra/listar', filtros).then(function (response) {
        filasBoletas = response.data.data;
        seleccionBoletas.clear();
        tablaBoletas.clear().rows.add(filasBoletas).draw();
        actualizarSeleccionBoletas();
        $('#errorBoletas').addClass('d-none').text('');
        $('#excelBoletas').attr('href', '/boleta/compra/excel?' + $.param(filtros))
            .removeClass('disabled').removeAttr('aria-disabled');
    }).catch(function (error) {
        filasBoletas = [];
        seleccionBoletas.clear();
        tablaBoletas.clear().draw();
        actualizarSeleccionBoletas();
        $('#errorBoletas').removeClass('d-none').text(errorBoleta(error));
    });
}

function anularBoleta(id) {
    Swal.fire({
        title: '¿Anular boleta?', text: 'Esta accion no se puede deshacer.',
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Si, anular', cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            axios.post('/boleta/compra/anular', {id: id}).then(function () {
                Swal.fire('Anulada', 'La boleta fue anulada correctamente.', 'success');
                buscarBoletas();
            }).catch(error => Swal.fire('Error', errorBoleta(error), 'error'));
        }
    });
}

$(function () {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');
    var texto = $.fn.dataTable.render.text();
    tablaBoletas = $('#tbl_boletas').DataTable({
        language: {url: '/js/plugins/dataTables/i18n/Spanish.json'},
        pageLength: 5, searching: false, order: [], data: [],
        columns: [
            {data: 'id', orderable: false, render: function (id, type, row) {
                return '<input type="checkbox" class="boleta-check" aria-label="Seleccionar boleta ' + Number(id)
                    + '" value="' + Number(id) + '"' + (Number(row.estado) !== 1 ? ' disabled' : '') + '>';
            }},
            {data: 'numero_boleta', render: texto},
            {data: 'cliente', render: texto}, {data: 'direccion', render: texto},
            {data: 'fecha', render: texto}, {data: 'total', render: texto},
            {data: 'registrado_por', render: texto}, {data: 'estado_reporte', render: texto},
            {data: 'opciones', orderable: false}
        ],
        drawCallback: function () {
            $('#tbl_boletas tbody tr').attr({tabindex: '0', 'aria-expanded': 'false'});
            actualizarSeleccionBoletas();
        }
    });
    $('#tbl_boletas').on('change', '#seleccionarBoletas', function () {
        seleccionBoletas.clear();
        if (this.checked) {
            filasBoletas.filter(b => Number(b.estado) === 1).forEach(b => seleccionBoletas.add(String(b.id)));
        }
        actualizarSeleccionBoletas();
    });
    $('#tbl_boletas').on('change', '.boleta-check', function () {
        if (this.checked) seleccionBoletas.add(this.value);
        else seleccionBoletas.delete(this.value);
        actualizarSeleccionBoletas();
    }).on('click keydown', 'tbody tr', function (event) {
        if ($(event.target).closest('input, a, button, .btn-group').length) return;
        if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
        var fila = tablaBoletas.row(this);
        var datos = fila.data();
        if (!datos) return;
        event.preventDefault();
        if (fila.child.isShown()) {
            fila.child.hide();
            $(this).attr('aria-expanded', 'false');
            return;
        }
        var contenido = $('<div class="p-3"></div>');
        (datos.historial || []).forEach(function (cambio) {
            $('<p class="mb-1"></p>').text(
                (cambio.accion === 'anular' ? 'Anulada'
                    : (cambio.accion === 'incluir_compras' ? 'Incluida en Compras' : 'Retirada de Compras'))
                + ' · ' + (cambio.usuario || 'Usuario #' + cambio.usuario_id) + ' · ' + cambio.created_at
            ).appendTo(contenido);
        });
        if (!datos.historial.length) contenido.text('Sin cambios de inclusion registrados.');
        fila.child(contenido).show();
        $(this).attr('aria-expanded', 'true');
    });
    $('#incluirBoletas, #quitarBoletas').on('click', function () {
        accionBoletas = this.id === 'incluirBoletas' ? 'incluir' : 'quitar';
        $('#confirmacionReporteBoletas').text(
            accionBoletas === 'incluir' ? '¿Incluir las boletas seleccionadas en el reporte de compras?'
                : '¿Quitar las boletas seleccionadas del reporte de compras?'
        );
        $('#errorAccionBoletas').addClass('d-none').text('');
        $('#modalReporteBoletas').modal('show');
    });
    $('#confirmarReporteBoletas').on('click', function () {
        var boton = $(this).prop('disabled', true);
        axios.post('/boleta/compra/reporte-compras', {
            seleccionados: Array.from(seleccionBoletas), accion: accionBoletas
        }).then(function (response) {
            $('#modalReporteBoletas').modal('hide');
            Swal.fire('Actualizado', response.data.message, 'success');
            buscarBoletas();
        }).catch(function (error) {
            $('#errorAccionBoletas').removeClass('d-none').text(errorBoleta(error));
        }).finally(() => boton.prop('disabled', false));
    });
    buscarBoletas();
});
