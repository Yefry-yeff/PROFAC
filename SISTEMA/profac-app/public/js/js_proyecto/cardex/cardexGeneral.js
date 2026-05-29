
var cdxFiltros = {
    desde: '',
    hasta: '',
    producto: '',
    cai: '',
    tipoDocumento: '',
    idDocumento: '',
    usuario: '',
    bodegaOrigen: '',
    bodegaDestino: ''
};

function s2opts(url, placeholder) {
    return {
        ajax: {
            url: url,
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term || '',
                    q: params.term || '',
                    type: 'public',
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                return { results: data.results || [] };
            },
            cache: true
        },
        placeholder: placeholder,
        allowClear: true,
        minimumInputLength: 1,
        width: '100%',
        dropdownParent: $('body')
    };
}

function cdxRenderBadges() {
    var bar = document.getElementById('cdxFiltrosBar');
    if (!bar) return;

    var parts = [];
    var labels = {
        desde: 'Desde',
        hasta: 'Hasta',
        producto: 'Producto',
        cai: 'Factura (CAI)',
        tipoDocumento: 'Documento',
        idDocumento: 'ID Documento',
        usuario: 'Usuario',
        bodegaOrigen: 'Bodega Origen',
        bodegaDestino: 'Bodega Destino'
    };

    Object.keys(labels).forEach(function(key) {
        if (!cdxFiltros[key]) return;
        var shown = cdxFiltros[key];
        if (key === 'usuario') {
            shown = $('#cdxFiltroUsuario option:selected').text() || shown;
        }
        if (key === 'bodegaOrigen') {
            shown = $('#cdxFiltroBodegaOrigen option:selected').text() || shown;
        }
        if (key === 'bodegaDestino') {
            shown = $('#cdxFiltroBodegaDestino option:selected').text() || shown;
        }
        parts.push('<span class="filtro-badge">' + labels[key] + ': <strong>' + shown + '</strong>' +
            '<span class="filtro-remove" onclick="quitarFiltroCardex(\'' + key + '\')">✕</span></span>');
    });

    if (parts.length === 0) {
        bar.style.display = 'none';
        bar.innerHTML = '';
        return;
    }

    bar.innerHTML = parts.join('');
    bar.style.display = 'flex';
}

function quitarFiltroCardex(key) {
    cdxFiltros[key] = '';
    if (key === 'desde') document.getElementById('cdxFiltroDesde').value = '';
    if (key === 'hasta') document.getElementById('cdxFiltroHasta').value = '';
    if (key === 'producto') document.getElementById('cdxFiltroProducto').value = '';
    if (key === 'cai') document.getElementById('cdxFiltroCai').value = '';
    if (key === 'tipoDocumento') document.getElementById('cdxTipoDocumento').value = '';
    if (key === 'idDocumento') document.getElementById('cdxIdDocumento').value = '';
    if (key === 'usuario') $('#cdxFiltroUsuario').val(null).trigger('change');
    if (key === 'bodegaOrigen') $('#cdxFiltroBodegaOrigen').val(null).trigger('change');
    if (key === 'bodegaDestino') $('#cdxFiltroBodegaDestino').val(null).trigger('change');
    cdxRenderBadges();
    if ($.fn.DataTable.isDataTable('#tbl_cardex')) {
        $('#tbl_cardex').DataTable().ajax.reload();
    }
}

function cdxBuildUrl() {
    var desde = cdxFiltros.desde || document.getElementById('cdxFiltroDesde').value;
    var hasta = cdxFiltros.hasta || document.getElementById('cdxFiltroHasta').value;
    if (!desde || !hasta) {
        var now = new Date();
        var m = String(now.getMonth() + 1).padStart(2, '0');
        var d = String(now.getDate()).padStart(2, '0');
        var y = now.getFullYear();
        if (!hasta) hasta = y + '-' + m + '-' + d;
        if (!desde) desde = y + '-' + m + '-01';
    }
    return '/listado/cardex/general/' + desde + '/' + hasta;
}

function initDataTableCardex() {
    $('#tbl_cardex').DataTable({
        paging: true,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 5,
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            { extend: 'copy' },
            { extend: 'csv' },
            { extend: 'excel', title: 'Cardex' },
            { extend: 'pdf', title: 'Cardex' },
            {
                extend: 'print',
                title: '',
                customize: function(win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');
                    $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                }
            }
        ],
        ajax: {
            url: cdxBuildUrl(),
            data: function(d) {
                d.filtroDesde = cdxFiltros.desde;
                d.filtroHasta = cdxFiltros.hasta;
                d.filtroProducto = cdxFiltros.producto;
                d.filtroCai = cdxFiltros.cai;
                d.tipoDocumento = cdxFiltros.tipoDocumento;
                d.idDocumento = cdxFiltros.idDocumento;
                d.filtroUsuario = cdxFiltros.usuario;
                d.filtroBodegaOrigen = cdxFiltros.bodegaOrigen;
                d.filtroBodegaDestino = cdxFiltros.bodegaDestino;
            }
        },
        columns: [
            { data: 'fechaIngreso' },
            { data: 'producto' },
            { data: 'codigoProducto' },
            { data: 'doc_factura' },
            { data: 'doc_ajuste' },
            { data: 'detalleCompra' },
            { data: 'comprobante_entrega' },
            { data: 'vale_tipo_1' },
            { data: 'vale_tipo_2' },
            { data: 'nota_credito' },
            { data: 'descripcion' },
            { data: 'origen' },
            { data: 'destino' },
            { data: 'cantidad' },
            { data: 'usuario' }
        ]
    });
}

function aplicarFiltrosCardex() {
    cdxFiltros.desde = document.getElementById('cdxFiltroDesde').value || '';
    cdxFiltros.hasta = document.getElementById('cdxFiltroHasta').value || '';
    cdxFiltros.producto = document.getElementById('cdxFiltroProducto').value.trim();
    cdxFiltros.cai = document.getElementById('cdxFiltroCai').value.trim();
    cdxFiltros.tipoDocumento = document.getElementById('cdxTipoDocumento').value || '';
    cdxFiltros.idDocumento = document.getElementById('cdxIdDocumento').value.trim();
    cdxFiltros.usuario = $('#cdxFiltroUsuario').val() || '';
    cdxFiltros.bodegaOrigen = $('#cdxFiltroBodegaOrigen').val() || '';
    cdxFiltros.bodegaDestino = $('#cdxFiltroBodegaDestino').val() || '';

    if (document.activeElement && typeof document.activeElement.blur === 'function') {
        document.activeElement.blur();
    }
    $('#modalFiltrosCardex').modal('hide');
    cdxRenderBadges();

    if ($.fn.DataTable.isDataTable('#tbl_cardex')) {
        var dt = $('#tbl_cardex').DataTable();
        dt.ajax.url(cdxBuildUrl()).load();
    } else {
        initDataTableCardex();
    }
}

function limpiarFiltrosCardex() {
    document.getElementById('cdxFiltroDesde').value = '';
    document.getElementById('cdxFiltroHasta').value = '';
    document.getElementById('cdxFiltroProducto').value = '';
    document.getElementById('cdxFiltroCai').value = '';
    document.getElementById('cdxTipoDocumento').value = '';
    document.getElementById('cdxIdDocumento').value = '';
    $('#cdxFiltroUsuario').val(null).trigger('change');
    $('#cdxFiltroBodegaOrigen').val(null).trigger('change');
    $('#cdxFiltroBodegaDestino').val(null).trigger('change');

    cdxFiltros = {
        desde: '',
        hasta: '',
        producto: '',
        cai: '',
        tipoDocumento: '',
        idDocumento: '',
        usuario: '',
        bodegaOrigen: '',
        bodegaDestino: ''
    };
    cdxRenderBadges();
}

function cargaCardex() {
    aplicarFiltrosCardex();
}

$(document).ready(function() {
    $('#cdxFiltroUsuario').select2(s2opts('/filtros/facturas/usuarios', 'Buscar usuario...'));
    $('#cdxFiltroBodegaOrigen').select2(s2opts('/cardex/listar/bodega', 'Buscar bodega origen...'));
    $('#cdxFiltroBodegaDestino').select2(s2opts('/cardex/listar/bodega', 'Buscar bodega destino...'));

    $(document).on('select2:open', function() {
        $(document).off('focusin.modal');
        var campo = document.querySelector('.select2-container--open .select2-search__field');
        if (campo) campo.focus();
    });

    cdxFiltros.desde = document.getElementById('cdxFiltroDesde').value || '';
    cdxFiltros.hasta = document.getElementById('cdxFiltroHasta').value || '';

    $('#modalFiltrosCardex').on('hide.bs.modal', function() {
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
    });

    initDataTableCardex();
    cdxRenderBadges();
});
