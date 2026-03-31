/**
 * cliente-form.js — Formulario completo de creación/edición de clientes
 * Tabs: Datos Principales | Contacto | Dirección | Crédito | Observaciones
 * Repositorio de documentos (sticky)
 */

var clienteIdActual = null;   // se establece al crear o al cargar edición
var modoEdicion     = false;

$(document).ready(function () {
    var rawId = document.getElementById('cliente_id_form').value;
    clienteIdActual = (rawId && rawId !== '' && rawId !== 'null') ? parseInt(rawId, 10) : null;
    modoEdicion     = (clienteIdActual !== null);

    // Cargar catálogos siempre
    cargarCatalogos();
    cargarPaises();

    if (modoEdicion) {
        cargarDatosCliente(clienteIdActual);
        cargarRepositorioDocumentos(clienteIdActual);
    } else {
        // Modo crear: repositorio deshabilitado hasta tener ID
        mostrarAvisoRepo(true);
    }

    // Formato moneda en campo crédito
    $('#cred_monto').on('keyup blur', function () {
        formatCurrencyInput($(this));
    });
});

/* ============================================================
   CATÁLOGOS
   ============================================================ */
function cargarCatalogos() {
    axios.get('/cliente/tipo/personalidad').then(r => {
        llenarSelect('dp_tipo_personalidad', r.data.tipoPersonalidad, 'id', 'nombre');
    });
    axios.get('/cliente/tipo/cliente').then(r => {
        llenarSelect('dp_tipo_cliente', r.data.tipoCliente, 'id', 'descripcion');
    });
    // Escala/categoría (con paginación ligera)
    $.getJSON('/clientes/categorias-escala', function (res) {
        var sel = document.getElementById('dp_escala');
        (res.categorias || []).forEach(c => {
            sel.appendChild(new Option(c.nombre_categoria, c.id));
        });
    });
}

function cargarPaises() {
    axios.get('/cliente/pais').then(r => {
        llenarSelect('dir_pais', r.data.listaPais, 'id', 'nombre', '-- Seleccione País --');
    });
}

function cargarDeptosForm() {
    var paisId = document.getElementById('dir_pais').value;
    document.getElementById('dir_depto').innerHTML    = '<option value="" disabled selected>-- Seleccione --</option>';
    document.getElementById('dir_municipio').innerHTML = '<option value="" disabled selected>-- Seleccione --</option>';
    if (!paisId) return;
    axios.post('/cliente/departamento', { id: paisId }).then(r => {
        llenarSelect('dir_depto', r.data.listaDeptos, 'id', 'nombre', '-- Seleccione Departamento --');
    });
}

function cargarMunicipiosForm() {
    var deptoId = document.getElementById('dir_depto').value;
    document.getElementById('dir_municipio').innerHTML = '<option value="" disabled selected>-- Seleccione --</option>';
    if (!deptoId) return;
    axios.post('/cliente/municipio', { id: deptoId }).then(r => {
        llenarSelect('dir_municipio', r.data.listaMunicipios, 'id', 'nombre', '-- Seleccione Municipio --');
    });
}

/* ============================================================
   CARGAR DATOS (MODO EDICIÓN)
   ============================================================ */
function cargarDatosCliente(id) {
    axios.get('/clientes/form/datos/' + id)
        .then(r => {
            var d   = r.data;
            var cli = d.datosCliente;

            // Tab Datos Principales
            $('#dp_nombre').val(cli.nombre);
            $('#dp_rtn').val(cli.rtn);
            $('#dp_ano_operacion').val(cli.ano_operacion);
            $('#dp_dni').val(cli.dni_representante_legal);
            $('#dp_estado').prop('checked', cli.estado_cliente_id == 1);
            setSelectValue('dp_tipo_personalidad', cli.tipo_personalidad_id, d.tipoPersonalidad, 'id', 'nombre');
            setSelectValue('dp_tipo_cliente', cli.tipo_cliente_id, d.tipoCliente, 'id', 'descripcion');
            setSelectValById('dp_escala', cli.cliente_categoria_escala_id, cli.nombre_cat_escala);

            // Tab Contacto
            $('#ct_correo').val(cli.correo);
            $('#ct_telefono').val(cli.telefono_empresa);
            if (d.contactos && d.contactos.length > 0) {
                $('#ct_nombre1').val(d.contactos[0].nombre);
                $('#ct_telefono1').val(d.contactos[0].telefono);
            }
            if (d.contactos && d.contactos.length > 1) {
                $('#ct_nombre2').val(d.contactos[1].nombre);
                $('#ct_telefono2').val(d.contactos[1].telefono);
            }

            // Tab Dirección
            $('#dir_direccion').val(cli.direccion);
            $('#dir_latitud').val(cli.latitud);
            $('#dir_longitud').val(cli.longitud);
            llenarSelectConSelected('dir_pais', d.paises, 'id', 'nombre', d.ubicacion.idPais, '-- Seleccione País --');
            llenarSelectConSelected('dir_depto', d.deptos, 'id', 'nombre', d.ubicacion.idDepto, '-- Seleccione Departamento --');
            llenarSelectConSelected('dir_municipio', d.municipios, 'id', 'nombre', d.ubicacion.idMunicipio, '-- Seleccione Municipio --');

            // Tab Crédito
            if (d.credito) {
                var cr = d.credito;
                $('#cred_activo').prop('checked', cr.credito_activo == 1);
                $('#cred_monto').val(parseFloat(cr.credito || 0).toFixed(2));
                $('#cred_dias').val(cr.dias_credito);
                $('#cred_vendedor').val(cr.vendedor_id);
                $('#cred_metodo_pago').val(cr.metodo_pago);
                $('#cred_ref_bancarias').val(cr.referencias_bancarias);
                $('#cred_ref_comerciales').val(cr.referencias_comerciales);
                $('#cred_letra_cambio').val(cr.letra_cambio);
                $('#cred_aval_solidario').val(cr.aval_solidario);
                $('#cred_autorizacion').val(cr.autorizacion_gerencia);
            }

            // Historial crédito
            renderHistoricoCredito(d.historicoCredito || []);

            // Tab Observaciones
            renderObservaciones(d.observaciones || []);

            // Repositorio
            renderDocumentos(d.documentos || []);
        })
        .catch(err => {
            console.error(err);
            mostrarAlerta('error', 'Error', 'No se pudieron cargar los datos del cliente.');
        });
}

/* ============================================================
   GUARDAR DATOS PRINCIPALES (+ crear cliente si modo nuevo)
   ============================================================ */
function guardarDatosPrincipales() {
    var nombre = $('#dp_nombre').val().trim();
    if (!nombre) { mostrarAlerta('warning', 'Falta', 'Ingrese el nombre del cliente.'); return; }

    var payload = {
        _token:                    document.getElementById('csrf_token').value,
        nombre_cliente:            nombre,
        rtn_cliente:               $('#dp_rtn').val().trim(),
        tipo_personalidad_id:      $('#dp_tipo_personalidad').val(),
        tipo_cliente_id:           $('#dp_tipo_cliente').val(),
        cliente_categoria_escala_id: $('#dp_escala').val(),
        ano_operacion:             $('#dp_ano_operacion').val(),
        dni_representante:         $('#dp_dni').val().trim(),
        estado_activo:             $('#dp_estado').is(':checked') ? 1 : 0,
        // contacto (tab 2) – se incluye si ya fue llenado
        correo:                    $('#ct_correo').val().trim(),
        telefono:                  $('#ct_telefono').val().trim(),
        nombre_contacto1:          $('#ct_nombre1').val().trim(),
        telefono_contacto1:        $('#ct_telefono1').val().trim(),
        nombre_contacto2:          $('#ct_nombre2').val().trim(),
        telefono_contacto2:        $('#ct_telefono2').val().trim(),
        // dirección
        municipio_id:              $('#dir_municipio').val(),
        direccion:                 $('#dir_direccion').val().trim(),
        latitud:                   $('#dir_latitud').val().trim(),
        longitud:                  $('#dir_longitud').val().trim(),
        vendedor_id:               $('#cred_vendedor').val(),
        credito:                   $('#cred_monto').val().replace(/,/g, ''),
        dias_credito:              $('#cred_dias').val(),
        credito_activo:            $('#cred_activo').is(':checked') ? 1 : 0,
        referencias_bancarias:     $('#cred_ref_bancarias').val().trim(),
        referencias_comerciales:   $('#cred_ref_comerciales').val().trim(),
        metodo_pago:               $('#cred_metodo_pago').val().trim(),
        letra_cambio:              $('#cred_letra_cambio').val().trim(),
        aval_solidario:            $('#cred_aval_solidario').val().trim(),
        autorizacion_gerencia:     $('#cred_autorizacion').val().trim(),
    };

    $('#btn_guardar_datos').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

    var url = modoEdicion ? '/clientes/editar-completo' : '/clientes/crear-completo';
    if (modoEdicion) payload.cliente_id = clienteIdActual;

    axios.post(url, payload)
        .then(r => {
            var data = r.data;
            mostrarAlerta(data.icon, data.title, data.text);
            if (!modoEdicion && data.id) {
                // Ahora tenemos un cliente recién creado
                clienteIdActual = data.id;
                modoEdicion     = true;
                document.getElementById('cliente_id_form').value = clienteIdActual;
                mostrarAvisoRepo(false);
                cargarRepositorioDocumentos(clienteIdActual);
                // Actualizar URL sin recargar
                history.replaceState(null, '', '/clientes/form/' + clienteIdActual);
                $('#page-title').text('Editar Cliente');
                $('#btn_guardar_datos').html('<i class="fa fa-save"></i> Guardar Cambios');
            }
        })
        .catch(err => {
            var data = err.response ? err.response.data : {};
            mostrarAlerta(data.icon || 'error', data.title || 'Error', data.text || 'Error al guardar.');
        })
        .finally(() => {
            $('#btn_guardar_datos').prop('disabled', false).html('<i class="fa fa-save"></i> ' + (modoEdicion ? 'Guardar Cambios' : 'Registrar Cliente'));
        });
}

/* ============================================================
   GUARDAR CONTACTO (edición rápida)
   ============================================================ */
function guardarContacto() {
    if (!checkClienteCreado()) return;
    var payload = {
        _token:             document.getElementById('csrf_token').value,
        cliente_id:         clienteIdActual,
        nombre_cliente:     $('#dp_nombre').val().trim(),
        rtn_cliente:        $('#dp_rtn').val().trim(),
        tipo_personalidad_id: $('#dp_tipo_personalidad').val(),
        tipo_cliente_id:    $('#dp_tipo_cliente').val(),
        cliente_categoria_escala_id: $('#dp_escala').val(),
        ano_operacion:      $('#dp_ano_operacion').val(),
        dni_representante:  $('#dp_dni').val().trim(),
        estado_activo:      $('#dp_estado').is(':checked') ? 1 : 0,
        correo:             $('#ct_correo').val().trim(),
        telefono:           $('#ct_telefono').val().trim(),
        nombre_contacto1:   $('#ct_nombre1').val().trim(),
        telefono_contacto1: $('#ct_telefono1').val().trim(),
        nombre_contacto2:   $('#ct_nombre2').val().trim(),
        telefono_contacto2: $('#ct_telefono2').val().trim(),
        municipio_id:       $('#dir_municipio').val(),
        direccion:          $('#dir_direccion').val().trim(),
        latitud:            $('#dir_latitud').val().trim(),
        longitud:           $('#dir_longitud').val().trim(),
        vendedor_id:        $('#cred_vendedor').val(),
    };
    $('#btn_guardar_contacto').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/editar-completo', payload)
        .then(r => mostrarAlerta(r.data.icon, r.data.title, r.data.text))
        .catch(err => {
            var d = err.response ? err.response.data : {};
            mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al guardar.');
        })
        .finally(() => $('#btn_guardar_contacto').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Contacto'));
}

/* ============================================================
   GUARDAR DIRECCIÓN
   ============================================================ */
function guardarDireccion() {
    if (!checkClienteCreado()) return;
    var payload = {
        _token:           document.getElementById('csrf_token').value,
        cliente_id:       clienteIdActual,
        nombre_cliente:   $('#dp_nombre').val().trim(),
        rtn_cliente:      $('#dp_rtn').val().trim(),
        tipo_personalidad_id: $('#dp_tipo_personalidad').val(),
        tipo_cliente_id:  $('#dp_tipo_cliente').val(),
        cliente_categoria_escala_id: $('#dp_escala').val(),
        ano_operacion:    $('#dp_ano_operacion').val(),
        dni_representante: $('#dp_dni').val().trim(),
        estado_activo:    $('#dp_estado').is(':checked') ? 1 : 0,
        correo:           $('#ct_correo').val().trim(),
        telefono:         $('#ct_telefono').val().trim(),
        nombre_contacto1: $('#ct_nombre1').val().trim(),
        telefono_contacto1: $('#ct_telefono1').val().trim(),
        nombre_contacto2: $('#ct_nombre2').val().trim(),
        telefono_contacto2: $('#ct_telefono2').val().trim(),
        municipio_id:     $('#dir_municipio').val(),
        direccion:        $('#dir_direccion').val().trim(),
        latitud:          $('#dir_latitud').val().trim(),
        longitud:         $('#dir_longitud').val().trim(),
        vendedor_id:      $('#cred_vendedor').val(),
    };
    $('#btn_guardar_direccion').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/editar-completo', payload)
        .then(r => mostrarAlerta(r.data.icon, r.data.title, r.data.text))
        .catch(err => {
            var d = err.response ? err.response.data : {};
            mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error.');
        })
        .finally(() => $('#btn_guardar_direccion').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Dirección'));
}

/* ============================================================
   GUARDAR CRÉDITO
   ============================================================ */
function guardarCredito() {
    if (!checkClienteCreado()) return;
    var monto = $('#cred_monto').val().replace(/,/g, '');
    if (!monto || isNaN(monto)) { mostrarAlerta('warning', 'Falta', 'Ingrese el monto de crédito.'); return; }

    var payload = {
        _token:                   document.getElementById('csrf_token').value,
        cliente_id:               clienteIdActual,
        credito_activo:           $('#cred_activo').is(':checked') ? 1 : 0,
        credito:                  monto,
        dias_credito:             $('#cred_dias').val(),
        vendedor_id:              $('#cred_vendedor').val(),
        referencias_bancarias:    $('#cred_ref_bancarias').val().trim(),
        referencias_comerciales:  $('#cred_ref_comerciales').val().trim(),
        metodo_pago:              $('#cred_metodo_pago').val().trim(),
        letra_cambio:             $('#cred_letra_cambio').val().trim(),
        aval_solidario:           $('#cred_aval_solidario').val().trim(),
        autorizacion_gerencia:    $('#cred_autorizacion').val().trim(),
    };

    $('#btn_guardar_credito').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/credito/guardar', payload)
        .then(r => {
            mostrarAlerta(r.data.icon, r.data.title, r.data.text);
            recargarHistoricoCredito();
        })
        .catch(err => {
            var d = err.response ? err.response.data : {};
            mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al guardar crédito.');
        })
        .finally(() => $('#btn_guardar_credito').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Crédito'));
}

function recargarHistoricoCredito() {
    if (!clienteIdActual) return;
    axios.get('/clientes/credito/historico/' + clienteIdActual)
        .then(r => renderHistoricoCredito(r.data.historico || []));
}

function renderHistoricoCredito(rows) {
    var cont  = document.getElementById('historico_credito_container');
    var empty = document.getElementById('historico_credito_empty');
    if (!rows || rows.length === 0) {
        if (empty) empty.style.display = '';
        cont.innerHTML = '<p class="text-muted text-center" id="historico_credito_empty">Sin historial.</p>';
        return;
    }
    var html = '';
    rows.forEach(function (h) {
        var badge = h.credito_activo == 1
            ? '<span class="credito-badge credito-activo">Activo</span>'
            : '<span class="credito-badge credito-inactivo">Inactivo</span>';
        html += '<div class="historico-item">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div><strong>L ' + parseFloat(h.credito || 0).toLocaleString('es-HN', {minimumFractionDigits:2}) + '</strong> &nbsp;' + badge + '</div>' +
            '</div>' +
            '<div class="mt-1" style="font-size:0.85rem">' +
            (h.autorizacion_gerencia ? '<em>"' + escapeHtml(h.autorizacion_gerencia) + '"</em>' : '') +
            '</div>' +
            '<div class="hi-meta">' +
            'Días crédito: ' + (h.dias_credito || 0) +
            ' · Vendedor ID: ' + (h.vendedor_id || '—') +
            ' · Por: ' + escapeHtml(h.usuario || '—') +
            ' · ' + formatFecha(h.created_at) +
            '</div>' +
            '</div>';
    });
    cont.innerHTML = html;
}

/* ============================================================
   OBSERVACIONES
   ============================================================ */
function guardarObservacion() {
    if (!checkClienteCreado()) return;
    var texto = $('#obs_texto').val().trim();
    if (!texto) { mostrarAlerta('warning', 'Falta', 'Escriba una observación.'); return; }

    $('#btn_guardar_obs').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/observacion/guardar', {
        _token:      document.getElementById('csrf_token').value,
        cliente_id:  clienteIdActual,
        observacion: texto,
    })
    .then(r => {
        mostrarAlerta(r.data.icon, r.data.title, r.data.text);
        $('#obs_texto').val('');
        recargarObservaciones();
    })
    .catch(err => {
        var d = err.response ? err.response.data : {};
        mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al guardar.');
    })
    .finally(() => $('#btn_guardar_obs').prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar Observación'));
}

function recargarObservaciones() {
    if (!clienteIdActual) return;
    axios.get('/clientes/observaciones/' + clienteIdActual)
        .then(r => renderObservaciones(r.data.observaciones || []));
}

function renderObservaciones(rows) {
    var cont = document.getElementById('observaciones_container');
    if (!rows || rows.length === 0) {
        cont.innerHTML = '<p class="text-muted text-center" id="obs_empty">Sin observaciones registradas.</p>';
        return;
    }
    var html = '';
    rows.forEach(function (o) {
        html += '<div class="obs-item">' +
            '<div style="font-size:0.9rem">' + escapeHtml(o.observacion) + '</div>' +
            '<div class="obs-meta">' +
            'Por: ' + escapeHtml(o.usuario || '—') + ' · ' + formatFecha(o.created_at) +
            '</div>' +
            '</div>';
    });
    cont.innerHTML = html;
}

/* ============================================================
   REPOSITORIO DE DOCUMENTOS
   ============================================================ */
function subirDocumento(tipo) {
    if (!checkClienteCreado()) return;
    var fileInput = document.getElementById('file_' + tipo);
    if (!fileInput.files || fileInput.files.length === 0) {
        mostrarAlerta('warning', 'Falta', 'Seleccione un archivo para subir.'); return;
    }

    var formData = new FormData();
    formData.append('_token',        document.getElementById('csrf_token').value);
    formData.append('cliente_id',    clienteIdActual);
    formData.append('tipo_documento', tipo);
    formData.append('documento',     fileInput.files[0]);

    axios.post('/clientes/documento/subir', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(r => {
        mostrarAlerta(r.data.icon, r.data.title, r.data.text);
        fileInput.value = '';
        cargarRepositorioDocumentos(clienteIdActual);
    })
    .catch(err => {
        var d = err.response ? err.response.data : {};
        mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al subir.');
    });
}

function cargarRepositorioDocumentos(id) {
    axios.get('/clientes/documentos/' + id)
        .then(r => renderDocumentos(r.data.documentos || []));
}

function renderDocumentos(rows) {
    // Agrupar por tipo
    var grupos = {};
    rows.forEach(function (doc) {
        if (!grupos[doc.tipo_documento]) grupos[doc.tipo_documento] = [];
        grupos[doc.tipo_documento].push(doc);
    });

    // Limpiar todos los contenedores de documentos
    var slugs = ['escritura_empresa','dni_representante','rtn','permiso_operacion','croquis','contrato_arrendamiento','foto_establecimiento'];
    slugs.forEach(function (slug) {
        var cont = document.getElementById('docs_list_' + slug);
        if (!cont) return;
        var docs = grupos[slug] || [];
        if (docs.length === 0) { cont.innerHTML = ''; return; }
        var html = '';
        docs.forEach(function (doc) {
            html += '<div class="doc-item">' +
                '<span class="doc-name" title="' + escapeHtml(doc.nombre_original) + '">' +
                '<i class="fa fa-file-o mr-1"></i>' + escapeHtml(doc.nombre_original) +
                '</span>' +
                '<div class="doc-actions">' +
                '<a href="/clientes/documento/descargar/' + doc.id + '" class="btn btn-xs btn-success" title="Descargar" target="_blank">' +
                '<i class="fa fa-download"></i></a>' +
                '<button class="btn btn-xs btn-danger" title="Eliminar" onclick="confirmarEliminarDoc(' + doc.id + ')">' +
                '<i class="fa fa-trash"></i></button>' +
                '</div>' +
                '</div>';
        });
        cont.innerHTML = html;
    });
}

function confirmarEliminarDoc(docId) {
    Swal.fire({
        title: '¿Eliminar documento?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (result.isConfirmed) eliminarDocumento(docId);
    });
}

function eliminarDocumento(docId) {
    axios.delete('/clientes/documento/' + docId, {
        headers: { 'X-CSRF-TOKEN': document.getElementById('csrf_token').value }
    })
    .then(r => {
        mostrarAlerta(r.data.icon, r.data.title, r.data.text);
        cargarRepositorioDocumentos(clienteIdActual);
    })
    .catch(err => {
        var d = err.response ? err.response.data : {};
        mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al eliminar.');
    });
}

function mostrarAvisoRepo(show) {
    var aviso = document.getElementById('repo_aviso');
    if (aviso) aviso.style.display = show ? '' : 'none';
}

/* ============================================================
   HELPERS
   ============================================================ */
function checkClienteCreado() {
    if (!clienteIdActual) {
        mostrarAlerta('warning', 'Atención', 'Primero guarde los datos principales para poder usar esta sección.');
        return false;
    }
    return true;
}

function llenarSelect(id, array, valKey, labelKey, placeholder) {
    var sel = document.getElementById(id);
    if (!sel) return;
    var html = placeholder
        ? '<option value="" disabled selected>' + escapeHtml(placeholder) + '</option>'
        : '<option value="" disabled selected>-- Seleccione --</option>';
    (array || []).forEach(function (item) {
        html += '<option value="' + item[valKey] + '">' + escapeHtml(item[labelKey]) + '</option>';
    });
    sel.innerHTML = html;
}

function llenarSelectConSelected(id, array, valKey, labelKey, selectedVal, placeholder) {
    var sel = document.getElementById(id);
    if (!sel) return;
    var html = placeholder
        ? '<option value="" disabled' + (!selectedVal ? ' selected' : '') + '>' + escapeHtml(placeholder) + '</option>'
        : '';
    (array || []).forEach(function (item) {
        var sel_ = String(item[valKey]) === String(selectedVal) ? ' selected' : '';
        html += '<option value="' + item[valKey] + '"' + sel_ + '>' + escapeHtml(item[labelKey]) + '</option>';
    });
    sel.innerHTML = html;
}

function setSelectValue(id, selectedVal, array, valKey, labelKey) {
    var sel = document.getElementById(id);
    if (!sel) return;
    llenarSelect(id, array, valKey, labelKey);
    sel.value = selectedVal;
}

function setSelectValById(id, selectedVal, currentText) {
    var sel = document.getElementById(id);
    if (!sel) return;

    // Intentar seleccionar si ya está cargada la opción
    if (selectedVal) {
        var opt = sel.querySelector('option[value="' + selectedVal + '"]');
        if (opt) {
            sel.value = selectedVal;
        } else {
            // Agregar la opción seleccionada al inicio
            var newOpt = new Option(currentText || 'ID ' + selectedVal, selectedVal, true, true);
            sel.insertBefore(newOpt, sel.firstChild);
            sel.value = selectedVal;
        }
    }
}

function formatFecha(dateStr) {
    if (!dateStr) return '—';
    try {
        var d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('es-HN', {day:'2-digit', month:'2-digit', year:'numeric'}) +
               ' ' + d.toLocaleTimeString('es-HN', {hour:'2-digit', minute:'2-digit'});
    } catch (e) { return dateStr; }
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function mostrarAlerta(icon, title, text) {
    Swal.fire({ icon: icon || 'info', title: title || '', text: text || '' });
}

function formatCurrencyInput($input) {
    var val = $input.val().replace(/[^0-9.]/g, '');
    var parts = val.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    $input.val(parts.join('.'));
}
