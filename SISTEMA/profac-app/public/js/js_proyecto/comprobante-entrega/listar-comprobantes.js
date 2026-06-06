var cmpFiltros = {
    numero: '',
    cliente: '',
    usuario: '',
    desde: '',
    hasta: ''
};

function cmpS2opts(url, placeholder) {
    return {
        ajax: {
            url: url,
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term || '' };
            },
            processResults: function(data) {
                return { results: data.results || [] };
            },
            cache: true
        },
        placeholder: placeholder,
        allowClear: true,
        minimumInputLength: 2,
        width: '100%',
        dropdownParent: $('body')
    };
}

function cmpActualizarBarraFiltros() {
    var bar = document.getElementById('cmpFiltrosBar');
    if (!bar) return;

    var parts = [];
    var labels = {
        numero: 'N° Comprobante',
        cliente: 'Cliente',
        usuario: 'Registrado por',
        desde: 'Desde',
        hasta: 'Hasta'
    };

    Object.keys(labels).forEach(function(key) {
        if (!cmpFiltros[key]) return;
        var shown = cmpFiltros[key];
        if (key === 'cliente') {
            shown = $('#cmpFiltroCliente option:selected').text() || shown;
        }
        if (key === 'usuario') {
            shown = $('#cmpFiltroUsuario option:selected').text() || shown;
        }
        parts.push('<span class="filtro-badge">' + labels[key] + ': <strong>' + shown + '</strong>' +
            '<span class="filtro-remove" onclick="cmpQuitarFiltro(\'' + key + '\')">✕</span></span>');
    });

    if (!parts.length) {
        bar.style.display = 'none';
        bar.innerHTML = '';
        return;
    }

    bar.innerHTML = parts.join('');
    bar.style.display = 'flex';
}

function cmpQuitarFiltro(key) {
    cmpFiltros[key] = '';

    if (key === 'numero') document.getElementById('cmpFiltroNumero').value = '';
    if (key === 'desde') document.getElementById('cmpFiltroDesde').value = '';
    if (key === 'hasta') document.getElementById('cmpFiltroHasta').value = '';
    if (key === 'cliente') $('#cmpFiltroCliente').val(null).trigger('change');
    if (key === 'usuario') $('#cmpFiltroUsuario').val(null).trigger('change');

    cmpActualizarBarraFiltros();

    if ($.fn.DataTable.isDataTable('#tbl_listar_comprobantes')) {
        $('#tbl_listar_comprobantes').DataTable().ajax.reload();
    }
}

function initDataTableComprobantes() {
    $('#tbl_listar_comprobantes').DataTable({
        order: [9, 'desc'],
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excel',
                title: 'Comprobantes_Activos',
                className: 'btn btn-success btn-sm'
            }
        ],
        ajax: {
            url: '/comprovante/entrega/listado/activos',
            data: function(d) {
                d.filtroNumero = cmpFiltros.numero;
                d.filtroCliente = cmpFiltros.cliente;
                d.filtroUsuario = cmpFiltros.usuario;
                d.filtroDesde = cmpFiltros.desde;
                d.filtroHasta = cmpFiltros.hasta;
            }
        },
        columns: [
            { data: 'numero_comprovante' },
            { data: 'nombre_cliente' },
            { data: 'RTN' },
            { data: 'fecha_emision' },
            { data: 'sub_total' },
            { data: 'isv' },
            { data: 'total' },
            { data: 'estado' },
            { data: 'name' },
            { data: 'fecha_creacion' },
            { data: 'opciones', orderable: false, searchable: false }
        ],
        initComplete: function() {
            document.getElementById('tbl_loading_overlay').style.display = 'none';
        }
    });
}

function aplicarFiltrosComprobantes() {
    cmpFiltros.numero = document.getElementById('cmpFiltroNumero').value.trim();
    cmpFiltros.cliente = $('#cmpFiltroCliente').val() || '';
    cmpFiltros.usuario = $('#cmpFiltroUsuario').val() || '';
    cmpFiltros.desde = document.getElementById('cmpFiltroDesde').value || '';
    cmpFiltros.hasta = document.getElementById('cmpFiltroHasta').value || '';

    $('#modalFiltrosComprobantes').modal('hide');

    document.getElementById('cmp-placeholder').style.display = 'none';
    document.getElementById('cmp-table-wrapper').style.display = '';
    document.getElementById('tbl_loading_overlay').style.display = '';

    cmpActualizarBarraFiltros();

    if ($.fn.DataTable.isDataTable('#tbl_listar_comprobantes')) {
        $('#tbl_listar_comprobantes').DataTable().ajax.reload(function() {
            document.getElementById('tbl_loading_overlay').style.display = 'none';
        });
    } else {
        initDataTableComprobantes();
    }
}

function limpiarFiltrosComprobantes() {
    document.getElementById('cmpFiltroNumero').value = '';
    document.getElementById('cmpFiltroDesde').value = '';
    document.getElementById('cmpFiltroHasta').value = '';
    $('#cmpFiltroCliente').val(null).trigger('change');
    $('#cmpFiltroUsuario').val(null).trigger('change');

    cmpFiltros = {
        numero: '',
        cliente: '',
        usuario: '',
        desde: '',
        hasta: ''
    };

    cmpActualizarBarraFiltros();
}

$(document).ready(function() {
    $('#cmpFiltroCliente').select2(cmpS2opts('/filtros/facturas/clientes', 'Buscar cliente...'));
    $('#cmpFiltroUsuario').select2(cmpS2opts('/filtros/facturas/usuarios', 'Buscar usuario...'));

    $(document).on('select2:open', function() {
        $(document).off('focusin.modal');
        var campo = document.querySelector('.select2-container--open .select2-search__field');
        if (campo) campo.focus();
    });

    setTimeout(function() {
        $('#modalFiltrosComprobantes').modal('show');
    }, 400);
});

function anularComprobanteConfirmar(idComprobante) {
    Swal.fire({
        title: '¿Está seguro de anular este comprobante?',
        html: '<textarea rows="4" placeholder="Es obligatorio describir el motivo." required id="comentarion" class="form-group form-control"></textarea>',
        showDenyButton: true,
        showCancelButton: false,
        confirmButtonText: 'Si, Anular Comprobante',
        denyButtonText: 'Cancelar',
        confirmButtonColor: '#19A689',
        denyButtonColor: '#676A6C'
    }).then((result) => {
        var motivo = document.getElementById('comentarion').value;
        if (result.isConfirmed && motivo) {
            anularComprobante(idComprobante, motivo);
        }
    });
}

function anularComprobante(idComprobante, motivo) {
    axios.post('/comprobante/entrega/anular', { idComprobante: idComprobante, motivo: motivo })
        .then(response => {
            var data = response.data;
            Swal.fire({ icon: data.icon, title: data.title, html: data.text });
            if ($.fn.DataTable.isDataTable('#tbl_listar_comprobantes')) {
                $('#tbl_listar_comprobantes').DataTable().ajax.reload();
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Ha ocurrido un error al anular el comprobante.'
            });
        });
}
