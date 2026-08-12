var ndTablaFacturas = null;
var ndTablaNotas = null;
var ndTablaMontos = null;

function ndFechaLocal(fecha) {
    var anio = fecha.getFullYear();
    var mes = String(fecha.getMonth() + 1).padStart(2, '0');
    var dia = String(fecha.getDate()).padStart(2, '0');
    return anio + '-' + mes + '-' + dia;
}

var ndHoy = ndFechaLocal(new Date());
var ndSeisMesesAtras = new Date();
ndSeisMesesAtras.setMonth(ndSeisMesesAtras.getMonth() - 6);
var ndDesdePredeterminado = ndFechaLocal(ndSeisMesesAtras);

var ndFiltrosFacturas = {
    fecha_desde: ndDesdePredeterminado,
    fecha_hasta: ndHoy,
    factura: '',
    cliente: '',
    vendedor: '',
    estado_nota: '',
    estado_cobro: ''
};

var ndFiltrosNotas = {
    fecha_desde: ndDesdePredeterminado,
    fecha_hasta: ndHoy,
    factura: '',
    cliente: '',
    usuario: '',
    estado: ''
};

$(document).on('submit', '#montoAddForm', function(event) {
    event.preventDefault();
    ndGuardarMonto();
});

$(document).on('submit', '#ndAddForm', function(event) {
    event.preventDefault();
    ndGuardarNotaDebito();
});

$(document).on('show.bs.modal', '.nd-modal', function() {
    document.body.classList.add('nd-modal-open');
});

$(document).on('hidden.bs.modal', '.nd-modal', function() {
    if (!$('.nd-modal.show').length) {
        document.body.classList.remove('nd-modal-open');
    }
});

$(document).ready(function() {
    if (!document.getElementById('tbl_listar_facturas')) return;

    $('#ndf_desde, #ndn_desde').val(ndDesdePredeterminado);
    $('#ndf_hasta, #ndn_hasta').val(ndHoy);
    ndInicializarSelectores();
    ndInicializarTablas();
    ndActualizarChipsFacturas();
    ndActualizarChipsNotas();

    $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });
});

function ndInicializarSelectores() {
    function opcionesSelect2(url, placeholder, modal) {
        return {
            ajax: {
                url: url,
                dataType: 'json',
                delay: 300,
                data: function(params) { return { q: params.term || '' }; },
                processResults: function(data) { return { results: data.results || [] }; },
                cache: true
            },
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: 2,
            width: '100%',
            dropdownParent: $(modal),
            language: {
                inputTooShort: function() { return 'Ingrese al menos 2 caracteres...'; },
                searching: function() { return 'Buscando...'; },
                noResults: function() { return 'Sin resultados'; }
            }
        };
    }

    $('#ndf_cliente').select2(opcionesSelect2('/filtros/facturas/clientes', 'Buscar cliente...', '#modal_filtros_facturas_nd'));
    $('#ndf_vendedor').select2(opcionesSelect2('/filtros/facturas/usuarios', 'Buscar vendedor...', '#modal_filtros_facturas_nd'));
    $('#ndn_cliente').select2(opcionesSelect2('/filtros/facturas/clientes', 'Buscar cliente...', '#modal_filtros_notas_nd'));
    $('#ndn_usuario').select2(opcionesSelect2('/filtros/facturas/usuarios', 'Buscar usuario...', '#modal_filtros_notas_nd'));

}

function ndInicializarTablas() {
    ndTablaFacturas = $('#tbl_listar_facturas').DataTable({
        order: [[1, 'desc']],
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 25,
        processing: true,
        serverSide: true,
        responsive: true,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        ajax: {
            url: '/debito/lista/facturas',
            data: function(datos) { return $.extend(datos, ndFiltrosFacturas); }
        },
        columns: [
            { data: 'cai' }, { data: 'fecha_emision' }, { data: 'nombre' },
            { data: 'descripcion' }, { data: 'fecha_vencimiento' },
            { data: 'sub_total', className: 'text-right' },
            { data: 'isv', className: 'text-right' },
            { data: 'total', className: 'text-right' },
            { data: 'estado_cobro', orderable: false, className: 'text-center' },
            { data: 'creado_por' },
            { data: 'estado_ndebito', orderable: false, className: 'text-center' },
            { data: 'opciones', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    ndTablaMontos = $('#tbl_listar_monto_debito').DataTable({
        order: [[3, 'desc']],
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 10,
        responsive: true,
        ajax: '/debito/lista/montos',
        columns: [
            { data: 'id' }, { data: 'monto', className: 'text-right' },
            { data: 'user' }, { data: 'created_at' },
            { data: 'estado_monto', orderable: false, className: 'text-center' }
        ]
    });

    ndTablaNotas = $('#tbl_listar_notas_debito').DataTable({
        order: [[0, 'desc']],
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 25,
        processing: true,
        serverSide: true,
        responsive: true,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        ajax: {
            url: '/debito/lista/notas',
            data: function(datos) { return $.extend(datos, ndFiltrosNotas); }
        },
        columns: [
            { data: 'id' }, { data: 'factura_cai' },
            { data: 'monto_asignado', className: 'text-right' },
            { data: 'fechaEmision' }, { data: 'motivoDescripcion' },
            { data: 'numeroCai' }, { data: 'correlativoND' },
            { data: 'user' }, { data: 'estado', orderable: false, className: 'text-center' },
            { data: 'file', orderable: false, searchable: false, className: 'text-center' },
            { data: 'created_at' }
        ]
    });
}

function llenadoModalDebito(facturaId, monto, montoId) {
    $('#factura_id').val(facturaId);
    $('#montoNotaDebito_id').val(montoId);
    $('#monto_').val(Number(monto).toFixed(2));
    $('#fechaEmision').val(ndHoy);
    $('#modal_nota_debito_crear').modal('show');
}

function ndAplicarFiltrosFacturas() {
    ndFiltrosFacturas = {
        fecha_desde: $('#ndf_desde').val() || '',
        fecha_hasta: $('#ndf_hasta').val() || '',
        factura: $('#ndf_factura').val().trim(),
        cliente: $('#ndf_cliente').val() || '',
        vendedor: $('#ndf_vendedor').val() || '',
        estado_nota: $('#ndf_estado_nota').val() || '',
        estado_cobro: $('#ndf_estado_cobro').val() || ''
    };
    $('#modal_filtros_facturas_nd').modal('hide');
    ndTablaFacturas.ajax.reload();
    ndActualizarChipsFacturas();
}

function ndLimpiarFiltrosFacturas() {
    $('#ndf_desde').val(ndDesdePredeterminado);
    $('#ndf_hasta').val(ndHoy);
    $('#ndf_factura').val('');
    $('#ndf_cliente, #ndf_vendedor').val(null).trigger('change');
    $('#ndf_estado_nota, #ndf_estado_cobro').val('');
    ndAplicarFiltrosFacturas();
}

function ndAplicarFiltrosNotas() {
    ndFiltrosNotas = {
        fecha_desde: $('#ndn_desde').val() || '',
        fecha_hasta: $('#ndn_hasta').val() || '',
        factura: $('#ndn_factura').val().trim(),
        cliente: $('#ndn_cliente').val() || '',
        usuario: $('#ndn_usuario').val() || '',
        estado: $('#ndn_estado').val() || ''
    };
    $('#modal_filtros_notas_nd').modal('hide');
    ndTablaNotas.ajax.reload();
    ndActualizarChipsNotas();
}

function ndLimpiarFiltrosNotas() {
    $('#ndn_desde').val(ndDesdePredeterminado);
    $('#ndn_hasta').val(ndHoy);
    $('#ndn_factura').val('');
    $('#ndn_cliente, #ndn_usuario').val(null).trigger('change');
    $('#ndn_estado').val('');
    ndAplicarFiltrosNotas();
}

function ndQuitarFiltroFacturas(clave) {
    if (clave === 'fecha_desde') $('#ndf_desde').val('');
    if (clave === 'fecha_hasta') $('#ndf_hasta').val('');
    if (clave === 'factura') $('#ndf_factura').val('');
    if (clave === 'cliente') $('#ndf_cliente').val(null).trigger('change');
    if (clave === 'vendedor') $('#ndf_vendedor').val(null).trigger('change');
    if (clave === 'estado_nota') $('#ndf_estado_nota').val('');
    if (clave === 'estado_cobro') $('#ndf_estado_cobro').val('');
    ndAplicarFiltrosFacturas();
}

function ndQuitarFiltroNotas(clave) {
    if (clave === 'fecha_desde') $('#ndn_desde').val('');
    if (clave === 'fecha_hasta') $('#ndn_hasta').val('');
    if (clave === 'factura') $('#ndn_factura').val('');
    if (clave === 'cliente') $('#ndn_cliente').val(null).trigger('change');
    if (clave === 'usuario') $('#ndn_usuario').val(null).trigger('change');
    if (clave === 'estado') $('#ndn_estado').val('');
    ndAplicarFiltrosNotas();
}

function ndEscape(texto) {
    return $('<div>').text(texto || '').html();
}

function ndPintarChips(selector, filtros, etiquetas, quitar) {
    var barra = $(selector).empty();
    Object.keys(etiquetas).forEach(function(clave) {
        if (!filtros[clave]) return;
        var valor = filtros[clave];
        if (clave === 'cliente') valor = $(selector.indexOf('facturas') >= 0 ? '#ndf_cliente option:selected' : '#ndn_cliente option:selected').text() || valor;
        if (clave === 'vendedor') valor = $('#ndf_vendedor option:selected').text() || valor;
        if (clave === 'usuario') valor = $('#ndn_usuario option:selected').text() || valor;
        if (clave === 'estado_nota') valor = $('#ndf_estado_nota option:selected').text();
        if (clave === 'estado_cobro') valor = $('#ndf_estado_cobro option:selected').text();
        if (clave === 'estado') valor = $('#ndn_estado option:selected').text();
        barra.append('<span class="nd-filter-chip"><strong>' + etiquetas[clave] + ':</strong> ' + ndEscape(valor)
            + ' <button type="button" onclick="' + quitar + '(\'' + clave + '\')">&times;</button></span>');
    });
    barra.toggle(barra.children().length > 0);
}

function ndActualizarChipsFacturas() {
    ndPintarChips('#nd_facturas_filtros_bar', ndFiltrosFacturas, {
        fecha_desde: 'Desde', fecha_hasta: 'Hasta', factura: 'Factura', cliente: 'Cliente',
        vendedor: 'Vendedor', estado_nota: 'Nota', estado_cobro: 'Cobro'
    }, 'ndQuitarFiltroFacturas');
}

function ndActualizarChipsNotas() {
    ndPintarChips('#nd_notas_filtros_bar', ndFiltrosNotas, {
        fecha_desde: 'Desde', fecha_hasta: 'Hasta', factura: 'Factura', cliente: 'Cliente',
        usuario: 'Usuario', estado: 'Estado'
    }, 'ndQuitarFiltroNotas');
}

function ndMostrarCarga(modalOrigen) {
    $(modalOrigen).modal('hide');
    $('#modalSpinnerLoading').modal('show');
}

function ndOcultarCarga() {
    $('#modalSpinnerLoading').modal('hide');
}

function ndGuardarMonto() {
    if (!$('#montoAddForm').parsley().validate()) return;
    var boton = $('#montoAddForm').find('[type="submit"]').add('[form="montoAddForm"]');
    boton.prop('disabled', true);
    ndMostrarCarga('#modal_monto_crear');
    axios.post('/debito/monto/guardar', new FormData($('#montoAddForm').get(0)))
        .then(function(response) {
            ndOcultarCarga();
            $('#montoAddForm').parsley().reset();
            document.getElementById('montoAddForm').reset();
            boton.prop('disabled', false);
            ndTablaMontos.ajax.reload(null, false);
            ndTablaFacturas.ajax.reload(null, false);
            Swal.fire({ icon:'success', title:response.data.title || 'Éxito', text:response.data.text, confirmButtonColor:'#e67e22' });
        })
        .catch(function(error) { boton.prop('disabled', false); ndManejarError(error); });
}

function ndGuardarNotaDebito() {
    if (!$('#ndAddForm').parsley().validate()) return;
    var boton = $('#ndAddForm').find('[type="submit"]').add('[form="ndAddForm"]');
    boton.prop('disabled', true);
    ndMostrarCarga('#modal_nota_debito_crear');
    axios.post('/debito/notad/guardar', new FormData($('#ndAddForm').get(0)))
        .then(function(response) {
            ndOcultarCarga();
            $('#ndAddForm').parsley().reset();
            document.getElementById('ndAddForm').reset();
            boton.prop('disabled', false);
            $('#fechaEmision').val(ndHoy);
            ndTablaNotas.ajax.reload(null, false);
            ndTablaFacturas.ajax.reload(null, false);
            Swal.fire({ icon:'success', title:response.data.title || 'Éxito', text:response.data.text, confirmButtonColor:'#e67e22' });
        })
        .catch(function(error) { boton.prop('disabled', false); ndManejarError(error); });
}

function ndManejarError(error) {
    ndOcultarCarga();
    var data = error.response && error.response.data ? error.response.data : {};
    Swal.fire({
        icon: data.icon || 'error',
        title: data.title || 'Error',
        text: data.text || 'No se pudo completar la operación.',
        confirmButtonColor: '#c9381b'
    });
}