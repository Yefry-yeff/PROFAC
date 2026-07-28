/* ==========================================================================
   Cartera de Clientes — lógica de listado, filtros, agrupación, selección
   masiva, asignación de asesores/teleasesores e historial.
   ========================================================================== */

var cdcTable = null;
var cdcVista = 'zonificacion';
var cdcSeleccion = new Set();
var cdcFiltros = { nombre: '', asesor: '', teleasesor: '', estado_cliente_id: '', sin_asignar: 0 };
var cdcZonaCatalogos = { departamentos: [], zonas: [] };
var cdcZonaMiembros = [];
var cdcZonaActivaId = null;
var cdcZonaDepartamentoActivoId = null;
var cdcZonasCargadas = [];
var cdcGuardadoZonaPendiente = null;
var cdcAgrupadoTable = null;
var cdcAgrupadoDatos = [];
var cdcAgrupadoDepartamento = null;
var cdcAgrupadoMunicipio = null;
var cdcAgrupadoNivel = 'departamentos';

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

    $('#cdc_zona_buscar').on('keypress', function (e) {
        if (e.which === 13) { cdcCargarZonas(); }
    });
    $('#cdc_zona_detalle_filtro').on('input', cdcRenderDetalleZona);
    $('#cdc_zona_chk_all').on('change', function () {
        var marcar = this.checked;
        $('#tbl_cdc_zona_detalle tbody .cdc-chk-cliente:visible').each(function () {
            $(this).prop('checked', marcar);
            var id = parseInt($(this).val(), 10);
            if (marcar) { cdcSeleccion.add(id); } else { cdcSeleccion.delete(id); }
        });
        cdcActualizarBarraSeleccion();
    });
    $('#tbl_cdc_zona_detalle tbody').on('change', '.cdc-chk-cliente', function () {
        var id = parseInt($(this).val(), 10);
        if (this.checked) { cdcSeleccion.add(id); } else { cdcSeleccion.delete(id); }
        cdcActualizarBarraSeleccion();
    });

    cdcCargarCatalogosZonas(function () { cdcCargarZonas(); });
}

function cdcActualizarBarraSeleccion() {
    var n = cdcSeleccion.size;
    $('#cdc_seleccion_count').text(n);
    $('#cdc_seleccion_bar').toggleClass('show', n > 0);

    var $checksVisibles = $('.cdc-chk-cliente:visible');
    var todosMarcados = $checksVisibles.length > 0 && $checksVisibles.filter(':not(:checked)').length === 0;
    $('#cdc_chk_all').prop('checked', todosMarcados);
    $('#cdc_zona_chk_all').prop('checked', todosMarcados);
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
    cdcRestablecerBotonSeleccionFiltrada();

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
    cdcRestablecerBotonSeleccionFiltrada();
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

    $('#cdc_filtros_clientes').toggle(vista !== 'zonificacion');
    $('#cdc_btn_seleccionar_filtrados').toggle(vista === 'municipio' || vista === 'departamento');
    if (vista === 'zonificacion') {
        $('#cdc_vista_zonificacion').show();
        $('#cdc_vista_individual, #cdc_vista_agrupada').hide();
        cdcCerrarDetalleZona();
        cdcActualizarBarraSeleccion();
        cdcCargarZonas();
    } else if (vista === 'individual') {
        $('#cdc_vista_zonificacion').hide();
        $('#cdc_vista_individual').show();
        $('#cdc_vista_agrupada').hide();
        cdcCargarTabla();
    } else {
        $('#cdc_vista_zonificacion').hide();
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
    if (cdcAgrupadoTable) {
        cdcAgrupadoTable.destroy();
        cdcAgrupadoTable = null;
    }
    cdcAgrupadoDatos = [];
    cdcAgrupadoDepartamento = null;
    cdcAgrupadoMunicipio = null;
    cdcAgrupadoNivel = 'departamentos';
    $('#cdc_agrupado_head, #cdc_agrupado_clientes_wrap').hide();
    $('#cdc_agrupado_grid').show().html('<p class="text-muted text-center py-4"><i class="fa fa-spinner fa-spin mr-1"></i>Cargando...</p>');
    $.get(window.CDC_ROUTES.agrupado, {
        tipo: tipo,
        nombre: cdcFiltros.nombre,
        asesor: cdcFiltros.asesor,
        teleasesor: cdcFiltros.teleasesor,
        estado_cliente_id: cdcFiltros.estado_cliente_id,
        sin_asignar: cdcFiltros.sin_asignar
    }).done(function (resp) {
        var $cont = $('#cdc_agrupado_grid').empty();
        if (!resp.success || !resp.data.length) {
            $cont.html('<p class="text-muted text-center py-4">No se encontraron clientes con los filtros aplicados.</p>');
            return;
        }
        cdcAgrupadoDatos = resp.data;
        cdcRenderDepartamentosAgrupados();
    }).fail(function () {
        $('#cdc_agrupado_grid').html('<p class="text-danger text-center py-4">No se pudo cargar la agrupación.</p>');
    });
}

function cdcRenderDepartamentosAgrupados() {
    cdcAgrupadoNivel = 'departamentos';
    cdcAgrupadoDepartamento = null;
    cdcAgrupadoMunicipio = null;
    $('#cdc_agrupado_head, #cdc_agrupado_clientes_wrap').hide();
    var $grid = $('#cdc_agrupado_grid').empty().show();
    var departamentos = {};
    cdcAgrupadoDatos.forEach(function (grupo) {
        var departamentoId = cdcVista === 'departamento' ? grupo.id : grupo.departamento_id;
        var departamentoNombre = cdcVista === 'departamento' ? grupo.nombre : grupo.departamento_nombre;
        if (!departamentos[departamentoId]) departamentos[departamentoId] = { id: Number(departamentoId), nombre: departamentoNombre, total: 0, municipios: [] };
        departamentos[departamentoId].total += Number(grupo.total || 0);
        if (cdcVista === 'municipio') departamentos[departamentoId].municipios.push(grupo);
    });
    Object.keys(departamentos).forEach(function (id) {
        var departamento = departamentos[id];
        var nombres = departamento.municipios.map(function (m) { return m.nombre; });
        var detalle = cdcVista === 'municipio' ? nombres.join(', ') : '';
        var subtitulo = cdcVista === 'municipio'
            ? departamento.municipios.length + (departamento.municipios.length === 1 ? ' municipio' : ' municipios') + ' · ' + departamento.total + ' clientes'
            : departamento.total + (departamento.total === 1 ? ' cliente' : ' clientes');
        $grid.append(cdcCrearCardAgrupada(departamento.nombre, subtitulo, detalle, { departamento_id: departamento.id }, function () {
            cdcAgrupadoDepartamento = departamento;
            if (cdcVista === 'municipio') cdcRenderMunicipiosAgrupados(departamento); else cdcAbrirClientesAgrupados({ departamento_id: departamento.id }, departamento.nombre, subtitulo);
        }));
    });
}

function cdcRenderMunicipiosAgrupados(departamento) {
    cdcAgrupadoNivel = 'municipios';
    cdcAgrupadoDepartamento = departamento;
    cdcAgrupadoMunicipio = null;
    $('#cdc_agrupado_clientes_wrap').hide();
    cdcConfigurarCabeceraAgrupada(departamento.nombre, departamento.municipios.length + (departamento.municipios.length === 1 ? ' municipio' : ' municipios'), { departamento_id: departamento.id }, 'fa-folder-open');
    var $grid = $('#cdc_agrupado_grid').empty().show();
    departamento.municipios.forEach(function (municipio) {
        $grid.append(cdcCrearCardAgrupada(municipio.nombre, Number(municipio.total) + (Number(municipio.total) === 1 ? ' cliente' : ' clientes'), '', { municipio_id: Number(municipio.id) }, function () {
            cdcAgrupadoMunicipio = municipio;
            cdcAbrirClientesAgrupados({ municipio_id: Number(municipio.id) }, municipio.nombre, departamento.nombre + ' · ' + municipio.total + ' clientes');
        }, 'fa-map-marker-alt'));
    });
}

function cdcCrearCardAgrupada(nombre, subtitulo, detalle, filtro, alAbrir, icono) {
    var $card = $('<article class="cdc-agrupado-card" role="button" tabindex="0"></article>');
    $card.append('<span class="cdc-agrupado-card-icon"><i class="fa ' + (icono || 'fa-folder') + '"></i></span>');
    var $info = $('<span class="cdc-agrupado-card-info"><strong></strong><small></small></span>');
    $info.find('strong').text(nombre);
    $info.find('small').text(subtitulo);
    if (detalle) $info.append($('<span class="cdc-agrupado-subitems"></span>').text(detalle));
    $card.append($info).append('<i class="fa fa-chevron-right cdc-agrupado-enter"></i>');
    var $seleccionar = $('<label class="cdc-agrupado-select"><input type="checkbox"> Seleccionar todos</label>');
    $seleccionar.on('click', function (event) { event.stopPropagation(); });
    $seleccionar.find('input').on('change', function () { cdcSeleccionarFiltroAgrupado(filtro, this.checked, $(this)); });
    $card.append($seleccionar);
    $card.on('click', alAbrir).on('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); alAbrir(); } });
    return $card;
}

function cdcConfigurarCabeceraAgrupada(nombre, subtitulo, filtro, icono) {
    $('#cdc_agrupado_head').css('display', 'flex');
    $('#cdc_agrupado_head_nombre').text(nombre);
    $('#cdc_agrupado_head_subtitulo').text(subtitulo);
    $('#cdc_agrupado_head_icon').attr('class', 'fa ' + icono);
}

function cdcAbrirClientesAgrupados(filtro, nombre, subtitulo) {
    cdcAgrupadoNivel = 'clientes';
    $('#cdc_agrupado_grid').hide();
    $('#cdc_agrupado_clientes_wrap').show();
    cdcConfigurarCabeceraAgrupada(nombre, subtitulo, filtro, 'fa-users');
    if (cdcAgrupadoTable) cdcAgrupadoTable.destroy();
    $('#tbl_cdc_agrupado_clientes tbody').empty();
    cdcAgrupadoTable = $('#tbl_cdc_agrupado_clientes').DataTable({
        order: [[1, 'asc']], language: { url: '/js/plugins/dataTables/i18n/Spanish.json' }, pageLength: 25,
        ajax: { url: window.CDC_ROUTES.listar, type: 'GET', data: function (d) { $.extend(d, cdcParametrosSeleccion(filtro)); } },
        columns: [
            { data: 'seleccionar', orderable: false, searchable: false }, { data: 'nombre' }, { data: 'ubicacion', orderable: false },
            { data: 'asesores_html', orderable: false }, { data: 'teleasesores_html', orderable: false }, { data: 'estado_descripcion' },
            { data: 'acciones', orderable: false, searchable: false }
        ],
        drawCallback: function () {
            $('#tbl_cdc_agrupado_clientes .cdc-chk-cliente').each(function () { $(this).prop('checked', cdcSeleccion.has(Number(this.value))); });
            cdcActualizarSeleccionFiltradaTabla();
            cdcActualizarBarraSeleccion();
        }
    });
    $('#cdc_agrupado_chk_all').off('change').prop('checked', false).prop('indeterminate', false).on('change', function () {
        cdcSeleccionarFiltradosTablaAgrupada(this.checked);
    });
    $('#tbl_cdc_agrupado_clientes tbody').off('change', '.cdc-chk-cliente').on('change', '.cdc-chk-cliente', function () {
        var id = Number(this.value);
        if (this.checked) cdcSeleccion.add(id); else cdcSeleccion.delete(id);
        cdcActualizarSeleccionFiltradaTabla();
        cdcActualizarBarraSeleccion();
    });
}

function cdcIdsFiltradosTablaAgrupada() {
    if (!cdcAgrupadoTable) return [];
    return cdcAgrupadoTable.rows({ search: 'applied' }).data().toArray().map(function (cliente) { return Number(cliente.id); });
}

function cdcSeleccionarFiltradosTablaAgrupada(marcar) {
    cdcIdsFiltradosTablaAgrupada().forEach(function (id) {
        if (marcar) cdcSeleccion.add(id); else cdcSeleccion.delete(id);
    });
    $('#tbl_cdc_agrupado_clientes .cdc-chk-cliente').each(function () { $(this).prop('checked', cdcSeleccion.has(Number(this.value))); });
    cdcActualizarSeleccionFiltradaTabla();
    cdcActualizarBarraSeleccion();
}

function cdcActualizarSeleccionFiltradaTabla() {
    var ids = cdcIdsFiltradosTablaAgrupada();
    var seleccionados = ids.filter(function (id) { return cdcSeleccion.has(id); }).length;
    $('#cdc_agrupado_chk_all')
        .prop('checked', ids.length > 0 && seleccionados === ids.length)
        .prop('indeterminate', seleccionados > 0 && seleccionados < ids.length);
}

function cdcVolverAgrupado() {
    if (cdcAgrupadoTable) { cdcAgrupadoTable.destroy(); cdcAgrupadoTable = null; }
    if (cdcVista === 'municipio' && cdcAgrupadoNivel === 'clientes' && cdcAgrupadoDepartamento) {
        cdcRenderMunicipiosAgrupados(cdcAgrupadoDepartamento);
    } else {
        cdcRenderDepartamentosAgrupados();
    }
}

function cdcSeleccionarFiltroAgrupado(filtro, marcar, $checkbox) {
    $checkbox.prop('disabled', true);
    $.get(window.CDC_ROUTES.listarIds, cdcParametrosSeleccion(filtro)).done(function (resp) {
        (resp.ids || []).map(Number).forEach(function (id) { if (marcar) cdcSeleccion.add(id); else cdcSeleccion.delete(id); });
        $('.cdc-chk-cliente').each(function () { $(this).prop('checked', cdcSeleccion.has(Number(this.value))); });
        $checkbox.prop('checked', marcar);
        cdcActualizarBarraSeleccion();
    }).fail(function () {
        $checkbox.prop('checked', !marcar);
        Swal.fire('Error', 'No se pudo seleccionar la carpeta completa.', 'error');
    }).always(function () { $checkbox.prop('disabled', false); });
}

function cdcParametrosSeleccion(extra) {
    return $.extend({
        nombre: cdcFiltros.nombre,
        asesor: cdcFiltros.asesor,
        teleasesor: cdcFiltros.teleasesor,
        estado_cliente_id: cdcFiltros.estado_cliente_id,
        sin_asignar: cdcFiltros.sin_asignar
    }, extra || {});
}

function cdcSeleccionarGrupoCompleto($grupo, tipo, id, marcar) {
    var $checkbox = $grupo.find('.cdc-group-chk-all').prop('disabled', true);
    var extra = {};
    extra[tipo === 'departamento' ? 'departamento_id' : 'municipio_id'] = id;
    $.get(window.CDC_ROUTES.listarIds, cdcParametrosSeleccion(extra)).done(function (resp) {
        var ids = (resp.ids || []).map(Number);
        $grupo.data('clienteIds', ids);
        ids.forEach(function (clienteId) {
            if (marcar) { cdcSeleccion.add(clienteId); } else { cdcSeleccion.delete(clienteId); }
        });
        $grupo.find('.cdc-chk-cliente').each(function () { $(this).prop('checked', cdcSeleccion.has(Number(this.value))); });
        $checkbox.prop('checked', marcar);
        cdcActualizarBarraSeleccion();
    }).fail(function () {
        $checkbox.prop('checked', !marcar);
        Swal.fire('Error', 'No se pudo seleccionar el grupo completo.', 'error');
    }).always(function () { $checkbox.prop('disabled', false); });
}

function cdcActualizarCheckboxGrupo($grupo) {
    var ids = $grupo.data('clienteIds');
    if (!ids || !ids.length) return;
    $grupo.find('.cdc-group-chk-all').prop('checked', ids.every(function (id) { return cdcSeleccion.has(Number(id)); }));
}

function cdcAlternarSeleccionFiltrada() {
    var $boton = $('#cdc_btn_seleccionar_filtrados').prop('disabled', true);
    $.get(window.CDC_ROUTES.listarIds, cdcParametrosSeleccion()).done(function (resp) {
        var ids = (resp.ids || []).map(Number);
        var todosSeleccionados = ids.length > 0 && ids.every(function (id) { return cdcSeleccion.has(id); });
        ids.forEach(function (clienteId) {
            if (todosSeleccionados) { cdcSeleccion.delete(clienteId); } else { cdcSeleccion.add(clienteId); }
        });
        $('.cdc-chk-cliente').each(function () { $(this).prop('checked', cdcSeleccion.has(Number(this.value))); });
        $('.cdc-group').each(function () { cdcActualizarCheckboxGrupo($(this)); });
        $boton.find('span').text(todosSeleccionados ? 'Seleccionar resultados' : 'Quitar resultados');
        cdcActualizarBarraSeleccion();
    }).fail(function () { Swal.fire('Error', 'No se pudieron seleccionar los resultados filtrados.', 'error'); })
      .always(function () { $boton.prop('disabled', false); });
}

function cdcRestablecerBotonSeleccionFiltrada() {
    $('#cdc_btn_seleccionar_filtrados span').text('Seleccionar resultados');
}

function cdcDescargarExcel() {
    var parametros = cdcParametrosSeleccion({ vista: cdcVista });
    var busquedaTabla = '';

    if (cdcVista === 'zonificacion') {
        if (cdcZonaActivaId) {
            parametros.zona_id = cdcZonaActivaId;
            busquedaTabla = $('#cdc_zona_detalle_filtro').val() || '';
        } else if (cdcZonaDepartamentoActivoId) {
            parametros.departamento_id = cdcZonaDepartamentoActivoId;
        } else {
            parametros.busqueda_zona = $('#cdc_zona_buscar').val() || '';
        }
    } else if (cdcVista === 'individual' && cdcTable) {
        busquedaTabla = cdcTable.search() || '';
    } else if (cdcVista === 'departamento') {
        if (cdcAgrupadoDepartamento) parametros.departamento_id = cdcAgrupadoDepartamento.id;
        if (cdcAgrupadoNivel === 'clientes' && cdcAgrupadoTable) busquedaTabla = cdcAgrupadoTable.search() || '';
    } else if (cdcVista === 'municipio') {
        if (cdcAgrupadoMunicipio && cdcAgrupadoNivel === 'clientes') {
            parametros.municipio_id = cdcAgrupadoMunicipio.id;
        } else if (cdcAgrupadoDepartamento) {
            parametros.departamento_id = cdcAgrupadoDepartamento.id;
        }
        if (cdcAgrupadoNivel === 'clientes' && cdcAgrupadoTable) busquedaTabla = cdcAgrupadoTable.search() || '';
    }

    if (busquedaTabla) parametros.busqueda_tabla = busquedaTabla;
    window.location.href = window.CDC_ROUTES.exportarExcel + '?' + $.param(parametros);
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

    $.ajax({ url: window.CDC_ROUTES.asignarMasivo, method: 'POST', contentType: 'application/json', data: JSON.stringify(payload) })
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
    if (cdcVista === 'zonificacion') {
        if (cdcZonaActivaId) { cdcAbrirDetalleZona(cdcZonaActivaId); } else { cdcCargarZonas(); }
    } else if (cdcVista === 'individual') {
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

    $.ajax({ url: window.CDC_ROUTES.historialMasivo, method: 'POST', contentType: 'application/json', data: JSON.stringify({ _token: cdcToken(), cliente_ids: Array.from(cdcSeleccion) }) })
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

/* ---------------------------- Zonificación ---------------------------- */

function cdcEsc(valor) {
    return $('<div>').text(valor == null ? '' : String(valor)).html();
}

function cdcCargarCatalogosZonas(callback) {
    $.get(window.CDC_ROUTES.zonasCatalogos).done(function (resp) {
        cdcZonaCatalogos = resp;
        if (typeof callback === 'function') callback();
    }).fail(function () {
        Swal.fire('Error', 'No se pudieron cargar los catálogos de zonificación.', 'error');
    });
}

function cdcCargarZonas() {
    $.get(window.CDC_ROUTES.zonas, { q: $('#cdc_zona_buscar').val() || '' }).done(function (resp) {
        var $grid = $('#cdc_zona_grid').empty();
        var zonas = resp.data || [];
        cdcZonasCargadas = zonas;
        cdcZonaDepartamentoActivoId = null;
        $('#cdc_departamento_zonas_head').hide();
        $grid.removeClass('cdc-zona-grid').addClass('cdc-departamentos-grid');
        if (!zonas.length) {
            $grid.html('<div class="cdc-zona-empty">No hay zonas registradas.</div>');
            return;
        }
        var departamentos = {};
        zonas.forEach(function (z) {
            if (!departamentos[z.departamento_id]) {
                departamentos[z.departamento_id] = { nombre: z.departamento_nombre, zonas: [] };
            }
            departamentos[z.departamento_id].zonas.push(z);
        });
        Object.keys(departamentos).forEach(function (departamentoId) {
            var departamento = departamentos[departamentoId];
            var $departamento = $('<button type="button" class="cdc-departamento-card" data-departamento-id="' + departamentoId + '"><span class="cdc-departamento-folder"><i class="fa fa-folder"></i></span><span class="cdc-departamento-info"><strong></strong><small></small></span><i class="fa fa-chevron-right cdc-departamento-enter"></i></button>');
            $departamento.find('.cdc-departamento-info strong').text(departamento.nombre);
            $departamento.find('.cdc-departamento-info small').text(departamento.zonas.length + (departamento.zonas.length === 1 ? ' zona' : ' zonas'));
            $departamento.on('click', function () { cdcAbrirDepartamentoZonas(departamentoId); });
            $grid.append($departamento);
        });
    }).fail(function () { Swal.fire('Error', 'No se pudo cargar la zonificación.', 'error'); });
}

function cdcAbrirDepartamentoZonas(departamentoId) {
    var zonas = cdcZonasCargadas.filter(function (zona) { return Number(zona.departamento_id) === Number(departamentoId); });
    if (!zonas.length) return;
    cdcZonaDepartamentoActivoId = Number(departamentoId);

    var $grid = $('#cdc_zona_grid').empty().removeClass('cdc-departamentos-grid').addClass('cdc-zona-grid');
    $('#cdc_departamento_zonas_nombre').text(zonas[0].departamento_nombre);
    $('#cdc_departamento_zonas_count').text(zonas.length + (zonas.length === 1 ? ' zona' : ' zonas'));
    $('#cdc_departamento_zonas_head').css('display', 'flex');
    zonas.forEach(function (z) {
            var asesores = cdcRenderUsuarios(z.asesores_comerciales, 'asesor', 'Sin asesores');
            var teleasesores = cdcRenderUsuarios(z.teleasesores, 'teleasesor', 'Sin teleasesores');
            var estado = Number(z.activo) === 1 ? '' : '<span class="badge badge-secondary ml-1">Inactiva</span>';
            var totalClientes = Number(z.total_clientes || 0);
        $grid.append('<article class="cdc-zona-card" onclick="cdcAbrirDetalleZona(' + z.id + ')">' +
                '<div class="cdc-zona-card-head"><div class="cdc-zona-card-title"><strong>' + cdcEsc(z.nombre) + '</strong></div>' +
                '<div class="cdc-zona-card-actions">' + estado + '<button class="btn-cdc-accion" title="Editar zona" onclick="event.stopPropagation();cdcEditarZona(' + z.id + ')"><i class="fa fa-edit"></i></button>' +
                '<button class="btn-cdc-accion" title="Bitácora" onclick="event.stopPropagation();cdcHistorialZona(' + z.id + ')"><i class="fa fa-history"></i></button></div></div>' +
                '<div class="cdc-zona-card-body"><span class="cdc-zona-label">Asesores comerciales</span><div class="cdc-zona-resumen">' + asesores + '</div>' +
                '<span class="cdc-zona-label">Teleasesores</span><div class="cdc-zona-resumen">' + teleasesores + '</div>' +
                '<span class="cdc-zona-clientes-count"><i class="fa fa-users"></i><strong>' + totalClientes + '</strong> ' + (totalClientes === 1 ? 'cliente' : 'clientes') + '</span></div></article>');
    });
}

function cdcVolverDepartamentos() {
    cdcZonaDepartamentoActivoId = null;
    var $grid = $('#cdc_zona_grid').empty().removeClass('cdc-zona-grid').addClass('cdc-departamentos-grid');
    $('#cdc_departamento_zonas_head').hide();
    cdcCargarZonas();
}

function cdcRenderUsuarios(usuarios, tipo, vacio) {
    if (!usuarios || !usuarios.length) return '<span class="text-muted small">' + cdcEsc(vacio || 'Sin asignar') + '</span>';
    return usuarios.map(function (u) { return '<span class="cdc-chip cdc-chip-' + tipo + '">' + cdcEsc(u.name || u.text) + '</span>'; }).join('');
}

function cdcPrepararSelectUsuarioZona(selector, rolId, seleccionados) {
    var $sel = $(selector);
    if ($sel.data('select2')) $sel.select2('destroy');
    $sel.empty();
    (seleccionados || []).forEach(function (u) { $sel.append(new Option(u.name || u.text, u.id, true, true)); });
    $sel.select2({
        placeholder: 'Buscar y seleccionar...', multiple: true, width: '100%', dropdownParent: $('#modalZonaCdc'),
        ajax: { url: window.CDC_ROUTES.usuarios, dataType: 'json', delay: 250,
            data: function (p) { return { q: p.term, rol_id: rolId }; },
            processResults: function (d) { return { results: d.results || [] }; } }
    });
}

function cdcLlenarDepartamentos(seleccionado) {
    var $sel = $('#cdc_zona_departamento').empty().append('<option value="">Seleccione...</option>');
    (cdcZonaCatalogos.departamentos || []).forEach(function (d) { $sel.append(new Option(d.nombre, d.id)); });
    if (seleccionado) $sel.val(String(seleccionado));
}

function cdcNuevaZona() {
    $('#cdc_zona_id').val(''); $('#cdc_zona_nombre').val(''); $('#cdc_zona_activo').val('1'); $('#cdc_zona_observaciones').val('');
    $('#cdc_zona_modal_titulo').text('Nueva Zona'); $('#cdc_zona_clientes_wrap').hide();
    cdcLlenarDepartamentos(null);
    cdcPrepararSelectUsuarioZona('#cdc_zona_asesor', 2, []);
    cdcPrepararSelectUsuarioZona('#cdc_zona_teleasesor', 3, []);
    $('#modalZonaCdc').modal('show');
}

function cdcEditarZona(id) {
    $.get(window.CDC_ROUTES.zonaDatos + '/' + id).done(function (resp) {
        var z = resp.zona;
        $('#cdc_zona_id').val(z.id); $('#cdc_zona_nombre').val(z.nombre); $('#cdc_zona_activo').val(String(z.activo));
        $('#cdc_zona_observaciones').val(z.observaciones || ''); $('#cdc_zona_modal_titulo').text('Editar Zona');
        cdcLlenarDepartamentos(z.departamento_id);
        cdcPrepararSelectUsuarioZona('#cdc_zona_asesor', 2, z.asesores_comerciales || []);
        cdcPrepararSelectUsuarioZona('#cdc_zona_teleasesor', 3, z.teleasesores || []);
        cdcZonaMiembros = resp.miembros || [];
        $('#cdc_zona_miembros_filtro').val('');
        cdcRenderMiembrosZona(); cdcPrepararBuscadorClienteZona();
        $('#cdc_zona_clientes_wrap').show(); $('#modalZonaCdc').modal('show');
    });
}

function cdcGuardarZona() {
    var payload = { _token: cdcToken(), id: $('#cdc_zona_id').val() || null, departamento_id: $('#cdc_zona_departamento').val(),
        nombre: $('#cdc_zona_nombre').val(), activo: $('#cdc_zona_activo').val(), observaciones: $('#cdc_zona_observaciones').val(),
        asesores_comerciales: $('#cdc_zona_asesor').val() || [], teleasesores: $('#cdc_zona_teleasesor').val() || [] };
    $.post(window.CDC_ROUTES.zonaGuardar, payload).done(function (resp) {
        if (resp.requiere_decisiones) {
            cdcMostrarDecisionesResponsables(resp, payload);
            return;
        }
        $('#modalZonaCdc').modal('hide'); Swal.fire(resp.title || 'Éxito', resp.text, resp.icon || 'success');
        cdcCargarCatalogosZonas(function () { cdcCargarZonas(); });
    }).fail(function (xhr) { var r = xhr.responseJSON || {}; Swal.fire(r.title || 'Error', r.text || r.message || 'No se pudo guardar.', r.icon || 'error'); });
}

function cdcMostrarDecisionesResponsables(resp, payload) {
    cdcGuardadoZonaPendiente = payload;
    var $body = $('#tbl_cdc_responsables_decisiones tbody').empty();
    (resp.clientes || []).forEach(function (cliente) {
        $body.append('<tr data-cliente-id="' + cliente.id + '"><td><input type="checkbox" class="cdc-responsable-chk" checked></td>' +
            '<td><strong>' + cdcEsc(cliente.nombre) + '</strong><br><small class="text-muted">' + cdcEsc(cliente.rtn || '') + '</small></td>' +
            '<td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(cliente.asesores_actuales, 'asesor', 'Sin asignar') + '</td>' +
            '<td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(cliente.teleasesores_actuales, 'teleasesor', 'Sin asignar') + '</td>' +
            '<td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(cliente.asesores_nuevos, 'asesor', 'Sin asignar') + '</td>' +
            '<td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(cliente.teleasesores_nuevos, 'teleasesor', 'Sin asignar') + '</td>' +
            '<td><select class="form-control form-control-sm cdc-operacion-cliente"><option value="no_modificar">No modificar</option><option value="reemplazar">Reemplazar por zona</option><option value="agregar">Agregar a actuales</option></select></td></tr>');
    });
    $('#cdc_responsables_zona_nombre').text(resp.zona ? resp.zona.nombre : 'Zona');
    $('#cdc_responsables_total_clientes').text((resp.clientes || []).length);
    $('#cdc_responsables_chk_all').prop('checked', true);
    $('#cdc_responsables_operacion_masiva').val('no_modificar');
    $('#cdc_responsables_chk_all').off('change').on('change', function () { $('.cdc-responsable-chk').prop('checked', this.checked); });

    var $origen = $('#modalZonaCdc');
    var $modal = $('#modalResponsablesZonaCdc');
    var aplicado = false;
    $modal.one('hidden.bs.modal', function () {
        if (!aplicado && cdcGuardadoZonaPendiente) $origen.modal('show');
    });
    $modal.data('marcar-aplicado', function () { aplicado = true; });
    $origen.one('hidden.bs.modal', function () { $modal.modal('show'); }).modal('hide');
}

function cdcAplicarOperacionResponsablesSeleccionados() {
    var operacion = $('#cdc_responsables_operacion_masiva').val();
    $('#tbl_cdc_responsables_decisiones tbody tr').each(function () {
        if ($(this).find('.cdc-responsable-chk').is(':checked')) $(this).find('.cdc-operacion-cliente').val(operacion);
    });
}

function cdcConfirmarDecisionesResponsables() {
    if (!cdcGuardadoZonaPendiente) return;
    var decisiones = [];
    $('#tbl_cdc_responsables_decisiones tbody tr').each(function () {
        decisiones.push({ cliente_id: Number($(this).data('cliente-id')), operacion: $(this).find('.cdc-operacion-cliente').val() });
    });
    var payload = $.extend(true, {}, cdcGuardadoZonaPendiente, { decisiones: decisiones });
    var $boton = $('#cdc_guardar_decisiones_responsables').prop('disabled', true);
    $.post(window.CDC_ROUTES.zonaGuardar, payload).done(function (resp) {
        var marcarAplicado = $('#modalResponsablesZonaCdc').data('marcar-aplicado');
        if (typeof marcarAplicado === 'function') marcarAplicado();
        cdcGuardadoZonaPendiente = null;
        $('#modalResponsablesZonaCdc').modal('hide');
        Swal.fire(resp.title || 'Éxito', resp.text, resp.icon || 'success').then(function () {
            cdcCargarCatalogosZonas(function () { cdcCargarZonas(); });
            if (cdcZonaActivaId) cdcAbrirDetalleZona(cdcZonaActivaId);
        });
    }).fail(function (xhr) {
        var r = xhr.responseJSON || {};
        Swal.fire(r.title || 'Error', r.text || r.message || 'No se pudieron aplicar las decisiones.', r.icon || 'error');
    }).always(function () { $boton.prop('disabled', false); });
}

function cdcRenderMiembrosZona() {
    var $cont = $('#cdc_zona_miembros').empty();
    var filtro = ($('#cdc_zona_miembros_filtro').val() || '').toString().trim().toLowerCase();
    var miembros = cdcZonaMiembros.filter(function (c) {
        if (!filtro) return true;
        return [c.nombre, c.rtn, c.municipio_nombre].some(function (valor) {
            return (valor || '').toString().toLowerCase().indexOf(filtro) !== -1;
        });
    });
    if (!cdcZonaMiembros.length) { $cont.html('<div class="cdc-zona-vacia">Esta zona todavía no tiene clientes.</div>'); return; }
    if (!miembros.length) { $cont.html('<div class="cdc-zona-vacia">No hay clientes que coincidan con el filtro.</div>'); return; }
    miembros.forEach(function (c) {
        $cont.append('<div class="cdc-zona-miembro"><span><strong>' + cdcEsc(c.nombre) + '</strong><br><small class="text-muted">' + cdcEsc(c.rtn || '') + '</small></span><span>' + cdcEsc(c.municipio_nombre || '-') + '</span>' +
            '<button class="btn-cdc-accion text-danger" title="Quitar" onclick="cdcQuitarClienteZona(' + c.id + ')"><i class="fa fa-times"></i></button></div>');
    });
}

function cdcPrepararBuscadorClienteZona() {
    var $sel = $('#cdc_zona_buscar_cliente'); if ($sel.data('select2')) $sel.select2('destroy'); $sel.empty();
    $sel.select2({ placeholder: 'Buscar cliente del departamento...', width: '100%', dropdownParent: $('#modalZonaCdc'),
        ajax: { url: window.CDC_ROUTES.zonaBuscarClientes, dataType: 'json', delay: 250,
            data: function (p) { return { q: p.term, departamento_id: $('#cdc_zona_departamento').val() }; },
            processResults: function (d) { return { results: d.results || [] }; } } });
}

function cdcAgregarClienteDesdeZona() {
    var clienteId = $('#cdc_zona_buscar_cliente').val(); if (!clienteId) return;
    var zonaId = $('#cdc_zona_id').val();
    cdcEnviarClientesZona(zonaId, [clienteId], false, function () { cdcEditarZona(zonaId); });
}

function cdcQuitarClienteZona(clienteId) {
    Swal.fire({ title: '¿Quitar cliente?', text: 'Se retirarán únicamente las asignaciones heredadas de esta zona.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Quitar', cancelButtonText: 'Cancelar' }).then(function (r) {
        if (!r.isConfirmed) return;
        var zonaId = cdcZonaActivaId || $('#cdc_zona_id').val();
        $.post(window.CDC_ROUTES.zonaQuitarCliente, { _token: cdcToken(), zona_id: zonaId, cliente_id: clienteId }).done(function (resp) {
            Swal.fire(resp.title, resp.text, resp.icon); cdcAbrirDetalleZona(zonaId); cdcCargarZonas();
        });
    });
}

function cdcAbrirAgregarZona() {
    if (!cdcSeleccion.size) { Swal.fire('Atención', 'Seleccione al menos un cliente.', 'warning'); return; }
    cdcCargarCatalogosZonas(function () {
        var $sel = $('#cdc_agregar_zona_id').empty().append('<option value="">Seleccione...</option>');
        (cdcZonaCatalogos.zonas || []).forEach(function (z) { $sel.append(new Option(z.departamento_nombre + ' - ' + z.nombre, z.id)); });
        $('#cdc_agregar_zona_count').text(cdcSeleccion.size); $('#modalAgregarZonaCdc').modal('show');
    });
}

function cdcConfirmarAgregarZona(confirmar) {
    var zonaId = $('#cdc_agregar_zona_id').val(); if (!zonaId) { Swal.fire('Atención', 'Seleccione una zona.', 'warning'); return; }
    cdcEnviarClientesZona(zonaId, Array.from(cdcSeleccion), confirmar, function () {
        $('#modalAgregarZonaCdc').modal('hide'); cdcLimpiarSeleccion(); cdcRecargarVistaActual();
    });
}

function cdcEnviarClientesZona(zonaId, clienteIds, confirmar, terminado) {
    $.ajax({ url: window.CDC_ROUTES.zonaAsignarClientes, method: 'POST', contentType: 'application/json', data: JSON.stringify({ _token: cdcToken(), zona_id: zonaId, cliente_ids: clienteIds, confirmar_movimiento: confirmar ? 1 : 0 }) })
        .done(function (resp) {
            cdcCargarCatalogosZonas();
            Swal.fire(resp.title, resp.text, resp.icon).then(function () { if (terminado) terminado(); });
        })
        .fail(function (xhr) {
            var r = xhr.responseJSON || {};
            if (xhr.status === 409 && r.requiere_confirmacion) {
                cdcMostrarCambiosZona(r, function () { cdcEnviarClientesZona(zonaId, clienteIds, true, terminado); });
                return;
            }
            Swal.fire(r.title || 'Error', r.text || r.message || 'No se pudo completar la operación.', r.icon || 'error');
        });
}

function cdcMostrarCambiosZona(resp, confirmar) {
    var $body = $('#tbl_cdc_zona_cambios tbody').empty();
    (resp.cambios || []).forEach(function (c) {
        var nombre = '<strong>' + cdcEsc(c.nombre) + '</strong>' + (c.zona_actual ? '<br><small class="text-muted">Zona actual: ' + cdcEsc(c.zona_actual) + '</small>' : '');
        $body.append('<tr><td>' + nombre + '</td><td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(c.asesores_actuales, 'asesor', 'Sin asignar') + '</td>' +
            '<td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(c.asesores_nuevos, 'asesor', 'Sin asignar') + '</td>' +
            '<td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(c.teleasesores_actuales, 'teleasesor', 'Sin asignar') + '</td>' +
            '<td class="cdc-cambio-usuarios">' + cdcRenderUsuarios(c.teleasesores_nuevos, 'teleasesor', 'Sin asignar') + '</td></tr>');
    });
    $('#cdc_cambios_zona_nombre').text(resp.zona ? resp.zona.nombre : 'la zona seleccionada');
    var $modal = $('#modalCambiosZonaCdc');
    var $modalOrigen = $('.modal.show').not($modal).last();
    var confirmado = false;
    $('#cdc_confirmar_cambios_zona').off('click').on('click', function () {
        confirmado = true;
        $modal.modal('hide');
    });
    $modal.one('hidden.bs.modal', function () {
        if (confirmado) {
            confirmar();
        } else if ($modalOrigen.length) {
            $modalOrigen.modal('show');
        }
    });
    if ($modalOrigen.length) {
        $modalOrigen.one('hidden.bs.modal', function () { $modal.modal('show'); }).modal('hide');
    } else {
        $modal.modal('show');
    }
}

function cdcAbrirDetalleZona(id) {
    $.get(window.CDC_ROUTES.zonaDatos + '/' + id).done(function (resp) {
        cdcZonaActivaId = parseInt(id, 10);
        cdcZonaMiembros = resp.miembros || [];
        $('#cdc_zona_detalle_nombre').text(resp.zona.nombre);
        var departamento = (cdcZonaCatalogos.departamentos || []).find(function (d) { return Number(d.id) === Number(resp.zona.departamento_id); });
        $('#cdc_zona_detalle_departamento').text(departamento ? departamento.nombre : '');
        $('#cdc_zona_detalle_filtro').val('');
        $('#cdc_zona_cards_wrap').hide();
        $('#cdc_zona_detalle_wrap').show();
        cdcRenderDetalleZona();
        cdcActualizarBarraSeleccion();
    }).fail(function () { Swal.fire('Error', 'No se pudo cargar el detalle de la zona.', 'error'); });
}

function cdcCerrarDetalleZona() {
    cdcZonaActivaId = null;
    $('#cdc_zona_detalle_wrap').hide();
    $('#cdc_zona_cards_wrap').show();
}

function cdcRenderDetalleZona() {
    var filtro = ($('#cdc_zona_detalle_filtro').val() || '').trim().toLowerCase();
    var $body = $('#tbl_cdc_zona_detalle tbody').empty();
    var miembros = cdcZonaMiembros.filter(function (c) {
        return !filtro || [c.nombre, c.rtn, c.municipio_nombre, c.departamento_nombre].some(function (v) { return String(v || '').toLowerCase().indexOf(filtro) !== -1; });
    });
    if (!miembros.length) {
        $body.html('<tr><td colspan="7" class="text-center text-muted py-4">' + (cdcZonaMiembros.length ? 'No hay coincidencias.' : 'Esta zona todavía no tiene clientes.') + '</td></tr>');
        return;
    }
    miembros.forEach(function (c) {
        var checked = cdcSeleccion.has(Number(c.id)) ? ' checked' : '';
        var acciones = '<button class="btn-cdc-accion" title="Editar asignación" onclick="cdcAbrirAsignacion(' + c.id + ')"><i class="fa fa-user-tag"></i></button>' +
            '<button class="btn-cdc-accion" title="Historial" onclick="cdcAbrirHistorial(' + c.id + ', decodeURIComponent(\'' + encodeURIComponent(c.nombre) + '\'))"><i class="fa fa-history"></i></button>' +
            '<button class="btn-cdc-accion text-danger" title="Quitar de zona" onclick="cdcQuitarClienteZona(' + c.id + ')"><i class="fa fa-times"></i></button>';
        $body.append('<tr><td><input type="checkbox" class="cdc-chk-cliente" value="' + c.id + '"' + checked + '></td><td><strong>' + cdcEsc(c.nombre) + '</strong><br><small class="text-muted">' + cdcEsc(c.rtn || '') + '</small></td>' +
            '<td>' + cdcEsc((c.municipio_nombre || '-') + ', ' + (c.departamento_nombre || '-')) + '</td><td>' + cdcRenderUsuarios(c.asesores_comerciales, 'asesor', 'Sin asignar') + '</td>' +
            '<td>' + cdcRenderUsuarios(c.teleasesores, 'teleasesor', 'Sin asignar') + '</td><td>' + cdcEsc(c.estado_descripcion || '-') + '</td><td>' + acciones + '</td></tr>');
    });
    cdcActualizarBarraSeleccion();
}

function cdcHistorialZona(id) {
    $.get(window.CDC_ROUTES.zonaHistorial + '/' + id).done(function (resp) {
        var $body = $('#tbl_cdc_zona_historial tbody').empty(); var rows = resp.data || [];
        if (!rows.length) $body.html('<tr><td colspan="5" class="text-center text-muted">Sin movimientos.</td></tr>');
        rows.forEach(function (h) { $body.append('<tr><td>' + cdcEsc((h.created_at || '').substring(0, 16)) + '</td><td>' + cdcEsc(h.accion) + '</td><td>' + cdcEsc(h.cliente_nombre || '-') + '</td><td>' + cdcEsc(h.usuario_nombre || '-') + '</td><td>' + cdcEsc(h.detalle || '') + '</td></tr>'); });
        $('#modalHistorialZonaCdc').modal('show');
    });
}
