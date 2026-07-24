/* ==========================================================================
   Cartera de Clientes — lógica de listado, filtros, agrupación, selección
   masiva, asignación de asesores/teleasesores e historial.
   ========================================================================== */

var cdcTable = null;
var cdcVista = 'individual';
var cdcSeleccion = new Set();
var cdcFiltros = { nombre: '', asesor: '', teleasesor: '', estado_cliente_id: '', sin_asignar: 0 };

function cdcToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

function cdcInit() {
    $('#cdc_fil_asesor').select2({
        placeholder: '— Todos —',
        allowClear: true,
        ajax: {
            url: window.CDC_ROUTES.usuarios,
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term, rol_id: 2 }; },
            processResults: function (data) { return { results: data.results }; }
        }
    });
    $('#cdc_fil_teleasesor').select2({
        placeholder: '— Todos —',
        allowClear: true,
        ajax: {
            url: window.CDC_ROUTES.usuarios,
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term, rol_id: 3 }; },
            processResults: function (data) { return { results: data.results }; }
        }
    });

    $('#cdc_chk_all').on('change', function () {
        var marcar = this.checked;
        $('#tbl_cdc tbody .cdc-chk-cliente').each(function () {
            $(this).prop('checked', marcar);
            var id = parseInt($(this).val(), 10);
            if (marcar) { cdcSeleccion.add(id); } else { cdcSeleccion.delete(id); }
        });
        cdcActualizarBarraSeleccion();
    });

    $('#tbl_cdc tbody').on('change', '.cdc-chk-cliente', function () {
        var id = parseInt($(this).val(), 10);
        if (this.checked) { cdcSeleccion.add(id); } else { cdcSeleccion.delete(id); }
        cdcActualizarBarraSeleccion();
    });

    $('#cdc_fil_nombre').on('keypress', function (e) {
        if (e.which === 13) { cdcAplicarFiltros(); }
    });

    cdcCargarTabla();
}

function cdcActualizarBarraSeleccion() {
    var n = cdcSeleccion.size;
    $('#cdc_seleccion_count').text(n);
    $('#cdc_seleccion_bar').toggleClass('show', n > 0);

    var todosMarcados = $('#tbl_cdc tbody .cdc-chk-cliente').length > 0 &&
        $('#tbl_cdc tbody .cdc-chk-cliente:not(:checked)').length === 0;
    $('#cdc_chk_all').prop('checked', todosMarcados);
}

function cdcLimpiarSeleccion() {
    cdcSeleccion.clear();
    $('.cdc-chk-cliente').prop('checked', false);
    $('#cdc_chk_all').prop('checked', false);
    cdcActualizarBarraSeleccion();
}

function cdcAplicarFiltros() {
    cdcFiltros.nombre = $('#cdc_fil_nombre').val() || '';
    cdcFiltros.asesor = $('#cdc_fil_asesor').val() ? $('#cdc_fil_asesor').select2('data')[0].text : '';
    cdcFiltros.teleasesor = $('#cdc_fil_teleasesor').val() ? $('#cdc_fil_teleasesor').select2('data')[0].text : '';
    cdcFiltros.estado_cliente_id = $('#cdc_fil_estado').val() || '';
    cdcFiltros.sin_asignar = $('#cdc_fil_sin_asignar').is(':checked') ? 1 : 0;

    if (cdcVista === 'individual') {
        cdcCargarTabla();
    } else {
        cdcCargarAgrupado(cdcVista);
    }
}

function cdcLimpiarFiltros() {
    $('#cdc_fil_nombre').val('');
    $('#cdc_fil_asesor').val(null).trigger('change');
    $('#cdc_fil_teleasesor').val(null).trigger('change');
    $('#cdc_fil_estado').val('');
    $('#cdc_fil_sin_asignar').prop('checked', false);
    cdcFiltros = { nombre: '', asesor: '', teleasesor: '', estado_cliente_id: '', sin_asignar: 0 };
    if (cdcVista === 'individual') {
        cdcCargarTabla();
    } else {
        cdcCargarAgrupado(cdcVista);
    }
}

function cdcCambiarVista(vista) {
    cdcVista = vista;
    $('.btn-cdc-action').removeClass('active');
    $('#btn_vista_' + vista).addClass('active');

    if (vista === 'individual') {
        $('#cdc_vista_individual').show();
        $('#cdc_vista_agrupada').hide();
        cdcCargarTabla();
    } else {
        $('#cdc_vista_individual').hide();
        $('#cdc_vista_agrupada').show();
        cdcCargarAgrupado(vista);
    }
}

function cdcCargarTabla() {
    if (cdcTable) { cdcTable.destroy(); $('#tbl_cdc tbody').empty(); }
    cdcTable = $('#tbl_cdc').DataTable({
        order: [[1, 'asc']],
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 25,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        ajax: {
            url: window.CDC_ROUTES.listar,
            type: 'GET',
            data: function (d) {
                d.nombre = cdcFiltros.nombre;
                d.asesor = cdcFiltros.asesor;
                d.teleasesor = cdcFiltros.teleasesor;
                d.estado_cliente_id = cdcFiltros.estado_cliente_id;
                d.sin_asignar = cdcFiltros.sin_asignar;
            }
        },
        columns: [
            { data: 'seleccionar', orderable: false, searchable: false },
            { data: 'nombre' },
            { data: 'ubicacion', orderable: false },
            { data: 'asesores_html', orderable: false },
            { data: 'teleasesores_html', orderable: false },
            { data: 'estado_descripcion' },
            { data: 'acciones', orderable: false, searchable: false }
        ],
        drawCallback: function () {
            $('#tbl_cdc tbody .cdc-chk-cliente').each(function () {
                var id = parseInt($(this).val(), 10);
                $(this).prop('checked', cdcSeleccion.has(id));
            });
            cdcActualizarBarraSeleccion();
        }
    });
}

function cdcCargarAgrupado(tipo) {
    $.get(window.CDC_ROUTES.agrupado, {
        tipo: tipo,
        nombre: cdcFiltros.nombre,
        asesor: cdcFiltros.asesor,
        teleasesor: cdcFiltros.teleasesor,
        estado_cliente_id: cdcFiltros.estado_cliente_id,
        sin_asignar: cdcFiltros.sin_asignar
    }).done(function (resp) {
        var $cont = $('#cdc_grupos').empty();
        if (!resp.success || !resp.data.length) {
            $cont.html('<p class="text-muted text-center py-4">No se encontraron clientes con los filtros aplicados.</p>');
            return;
        }
        resp.data.forEach(function (g) {
            var subtitulo = (tipo === 'municipio' && g.departamento_nombre) ? ' <small class="text-muted">(' + g.departamento_nombre + ')</small>' : '';
            var $grupo = $(
                '<div class="cdc-group" data-tipo="' + tipo + '" data-id="' + g.id + '">' +
                    '<div class="cdc-group-header">' +
                        '<i class="fa fa-chevron-right cdc-group-chevron"></i>' +
                        '<span class="cdc-group-title">' + g.nombre + subtitulo + '</span>' +
                        '<span class="cdc-group-badge">' + g.total + '</span>' +
                    '</div>' +
                    '<div class="cdc-group-body"></div>' +
                '</div>'
            );
            $grupo.find('.cdc-group-header').on('click', function () { cdcToggleGrupo($grupo, tipo, g.id); });
            $cont.append($grupo);
        });
    });
}

function cdcToggleGrupo($grupo, tipo, id) {
    var abierto = $grupo.hasClass('open');
    if (abierto) { $grupo.removeClass('open'); return; }

    $grupo.addClass('open');
    var $body = $grupo.find('.cdc-group-body');
    if ($body.data('cargado')) { return; }

    $body.html('<p class="text-muted small py-2"><i class="fa fa-spinner fa-spin mr-1"></i>Cargando...</p>');

    var params = {
        draw: 1, start: 0, length: 500,
        nombre: cdcFiltros.nombre, asesor: cdcFiltros.asesor, teleasesor: cdcFiltros.teleasesor,
        estado_cliente_id: cdcFiltros.estado_cliente_id, sin_asignar: cdcFiltros.sin_asignar
    };
    params[tipo === 'departamento' ? 'departamento_id' : 'municipio_id'] = id;

    $.get(window.CDC_ROUTES.listar, params).done(function (resp) {
        $body.data('cargado', true);
        var rows = resp.data || [];
        if (!rows.length) {
            $body.html('<p class="text-muted small py-2">Sin clientes en este grupo.</p>');
            return;
        }
        var html = '';
        rows.forEach(function (c) {
            html += '<div class="cdc-mini-row">' +
                        c.seleccionar +
                        '<span class="cdc-mini-nombre">' + c.nombre + '</span>' +
                        '<span>' + c.asesores_html + '</span>' +
                        '<span>' + c.teleasesores_html + '</span>' +
                        '<span>' + c.acciones + '</span>' +
                    '</div>';
        });
        $body.html(html);
        $body.find('.cdc-chk-cliente').each(function () {
            var id2 = parseInt($(this).val(), 10);
            $(this).prop('checked', cdcSeleccion.has(id2));
        });
        $body.on('change', '.cdc-chk-cliente', function () {
            var cid = parseInt($(this).val(), 10);
            if (this.checked) { cdcSeleccion.add(cid); } else { cdcSeleccion.delete(cid); }
            cdcActualizarBarraSeleccion();
        });
    });
}

/* ---------------------------- Asignación individual ---------------------------- */

var cdcAsigStaged = { asesores: [], teleasesores: [] };
var cdcAsigRolPorTipo = { asesores: 2, teleasesores: 3 };

function cdcAbrirAsignacion(clienteId) {
    $.get(window.CDC_ROUTES.datos + '/' + clienteId).done(function (resp) {
        if (!resp.success) { Swal.fire('Error', resp.mensaje || 'No se pudo cargar el cliente.', 'error'); return; }

        $('#cdc_asig_cliente_id').val(clienteId);
        $('#cdc_asig_nombre_cliente').text(resp.cliente.nombre);

        cdcAsigStaged.asesores = (resp.asesores_comerciales || []).map(function (u) { return { id: u.id, text: u.text }; });
        cdcAsigStaged.teleasesores = (resp.teleasesores || []).map(function (u) { return { id: u.id, text: u.text }; });

        cdcRenderListaAsignados('asesores');
        cdcRenderListaAsignados('teleasesores');

        cdcPrepararBuscarAsignacion('#cdc_asig_buscar_asesores', 2, 'asesores');
        cdcPrepararBuscarAsignacion('#cdc_asig_buscar_teleasesores', 3, 'teleasesores');

        $('#modalAsignacionCdc').modal('show');
    });
}

function cdcPrepararBuscarAsignacion(selector, rolId, tipo) {
    var $sel = $(selector);
    if ($sel.data('select2')) { $sel.select2('destroy'); }
    $sel.empty();
    $sel.select2({
        placeholder: 'Buscar usuario...',
        width: '100%',
        dropdownParent: $sel.closest('.modal'),
        ajax: {
            url: window.CDC_ROUTES.usuarios,
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term, rol_id: rolId }; },
            processResults: function (data) {
                var yaAsignados = cdcAsigStaged[tipo].map(function (u) { return u.id; });
                var results = (data.results || []).filter(function (u) { return yaAsignados.indexOf(parseInt(u.id, 10)) === -1; });
                return { results: results };
            }
        }
    });
}

function cdcRenderListaAsignados(tipo) {
    var $cont = $('#cdc_asig_lista_' + tipo).empty();
    var lista = cdcAsigStaged[tipo];
    if (!lista.length) {
        $cont.html('<span class="cdc-vacio">Sin usuarios asignados.</span>');
        return;
    }
    var claseChip = tipo === 'asesores' ? 'cdc-chip-asesor' : 'cdc-chip-teleasesor';
    lista.forEach(function (u) {
        $cont.append(
            $('<span class="cdc-chip ' + claseChip + ' cdc-chip-editable" data-id="' + u.id + '"></span>')
                .append($('<span></span>').text(u.text))
                .append($('<i class="fa fa-times cdc-chip-remove-icon"></i>').on('click', function () { cdcEliminarDeLista(tipo, u.id); }))
        );
    });
}

function cdcAgregarUsuario(tipo) {
    var selector = tipo === 'asesores' ? '#cdc_asig_buscar_asesores' : '#cdc_asig_buscar_teleasesores';
    var $sel = $(selector);
    var data = $sel.select2('data');
    if (!data || !data.length) { return; }
    var elegido = data[0];
    var id = parseInt(elegido.id, 10);

    var yaExiste = cdcAsigStaged[tipo].some(function (u) { return u.id === id; });
    if (!yaExiste) {
        cdcAsigStaged[tipo].push({ id: id, text: elegido.text });
        cdcRenderListaAsignados(tipo);
    }

    $sel.val(null).trigger('change');
}

function cdcEliminarDeLista(tipo, userId) {
    cdcAsigStaged[tipo] = cdcAsigStaged[tipo].filter(function (u) { return u.id !== userId; });
    cdcRenderListaAsignados(tipo);
}

function cdcEliminarTodos(tipo) {
    if (!cdcAsigStaged[tipo].length) { return; }
    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar todos?',
        text: 'Se quitarán todos los usuarios de esta lista (los cambios se guardan al presionar Guardar).',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar todos',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            cdcAsigStaged[tipo] = [];
            cdcRenderListaAsignados(tipo);
        }
    });
}

function cdcGuardarAsignacion() {
    var payload = {
        _token: cdcToken(),
        cliente_id: $('#cdc_asig_cliente_id').val(),
        asesores_comerciales: cdcAsigStaged.asesores.map(function (u) { return u.id; }),
        teleasesores: cdcAsigStaged.teleasesores.map(function (u) { return u.id; })
    };

    $.post(window.CDC_ROUTES.asignar, payload)
        .done(function (resp) {
            $('#modalAsignacionCdc').modal('hide');
            Swal.fire({ icon: resp.icon || 'success', title: resp.title || 'Éxito', text: resp.text });
            cdcRecargarVistaActual();
        })
        .fail(function (xhr) {
            var resp = xhr.responseJSON || {};
            Swal.fire('Error', resp.text || resp.message || 'No se pudo guardar la asignación.', 'error');
        });
}

/* ---------------------------- Asignación masiva ---------------------------- */

function cdcPrepararSelectAsignacionMasiva(selector, rolId) {
    var $sel = $(selector);
    $sel.empty();
    if ($sel.data('select2')) { $sel.select2('destroy'); }
    $sel.select2({
        placeholder: 'Buscar usuario...',
        multiple: true,
        dropdownParent: $sel.closest('.modal'),
        ajax: {
            url: window.CDC_ROUTES.usuarios,
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term, rol_id: rolId }; },
            processResults: function (data) { return { results: data.results }; }
        }
    });
}

function cdcAbrirAsignacionMasiva() {
    if (cdcSeleccion.size === 0) { Swal.fire('Atención', 'Seleccione al menos un cliente.', 'warning'); return; }

    $('#cdc_asig_masiva_count').text(cdcSeleccion.size);
    $('input[name="cdc_masiva_modo_asesores"]').prop('checked', false);
    $('#cdc_masiva_modo_asesores_sin').prop('checked', true);
    $('input[name="cdc_masiva_modo_teleasesores"]').prop('checked', false);
    $('#cdc_masiva_modo_tele_sin').prop('checked', true);

    cdcPrepararSelectAsignacionMasiva('#cdc_masiva_asesores', 2);
    cdcPrepararSelectAsignacionMasiva('#cdc_masiva_teleasesores', 3);

    $('#modalAsignacionMasivaCdc').modal('show');
}

function cdcGuardarAsignacionMasiva() {
    var payload = {
        _token: cdcToken(),
        cliente_ids: Array.from(cdcSeleccion),
        modo_asesores: $('input[name="cdc_masiva_modo_asesores"]:checked').val(),
        modo_teleasesores: $('input[name="cdc_masiva_modo_teleasesores"]:checked').val(),
        asesores_comerciales: $('#cdc_masiva_asesores').val() || [],
        teleasesores: $('#cdc_masiva_teleasesores').val() || []
    };

    $.post(window.CDC_ROUTES.asignarMasivo, payload)
        .done(function (resp) {
            $('#modalAsignacionMasivaCdc').modal('hide');
            Swal.fire({ icon: resp.icon || 'success', title: resp.title || 'Éxito', text: resp.text });
            cdcRecargarVistaActual();
        })
        .fail(function (xhr) {
            var resp = xhr.responseJSON || {};
            Swal.fire('Error', resp.text || resp.message || 'No se pudo aplicar la asignación masiva.', 'error');
        });
}

function cdcRecargarVistaActual() {
    if (cdcVista === 'individual') {
        cdcCargarTabla();
    } else {
        $('#cdc_grupos .cdc-group-body').data('cargado', false);
        cdcCargarAgrupado(cdcVista);
    }
}

/* ---------------------------- Historial ---------------------------- */

function cdcRenderFilaHistorial(h, conCliente) {
    var badge = h.accion === 'INSERT'
        ? '<span class="badge badge-cdc-insert">Asignado</span>'
        : '<span class="badge badge-cdc-delete">Removido</span>';
    var fecha = h.fecha ? h.fecha.replace('T', ' ').substring(0, 16) : '';
    var fila = '<tr>' +
        '<td>' + fecha + '</td>' +
        (conCliente ? '<td>' + (h.cliente_nombre || '') + '</td>' : '') +
        '<td>' + (h.tipo || '') + '</td>' +
        '<td>' + badge + '</td>' +
        '<td>' + (h.asesor_nombre || '') + '</td>' +
        '<td>' + (h.usuario_nombre || '-') + '</td>' +
        (conCliente ? '' : '<td>' + (h.comentario || '') + '</td>') +
        '</tr>';
    return fila;
}

function cdcAbrirHistorial(clienteId, nombreCliente) {
    $.get(window.CDC_ROUTES.historial + '/' + clienteId).done(function (resp) {
        var $tbody = $('#tbl_cdc_historial tbody').empty();
        var rows = resp.data || [];
        if (!rows.length) {
            $tbody.html('<tr><td colspan="6" class="text-center text-muted">Sin historial de cambios.</td></tr>');
        } else {
            rows.forEach(function (h) { $tbody.append(cdcRenderFilaHistorial(h, false)); });
        }
        $('#cdc_hist_nombre_cliente').text(nombreCliente || '');
        $('#modalHistorialCdc').modal('show');
    });
}

function cdcAbrirHistorialMasivo() {
    if (cdcSeleccion.size === 0) { Swal.fire('Atención', 'Seleccione al menos un cliente.', 'warning'); return; }

    $.post(window.CDC_ROUTES.historialMasivo, { _token: cdcToken(), cliente_ids: Array.from(cdcSeleccion) })
        .done(function (resp) {
            var $tbody = $('#tbl_cdc_historial_masivo tbody').empty();
            var rows = resp.data || [];
            if (!rows.length) {
                $tbody.html('<tr><td colspan="6" class="text-center text-muted">Sin historial de cambios para los clientes seleccionados.</td></tr>');
            } else {
                rows.forEach(function (h) { $tbody.append(cdcRenderFilaHistorial(h, true)); });
            }
            $('#modalHistorialMasivoCdc').modal('show');
        });
}
