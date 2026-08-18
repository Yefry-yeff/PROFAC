/**
 * cliente-form.js — Formulario completo de creación/edición de clientes
 * Tabs: Datos Principales | Contacto | Dirección | Crédito | Observaciones
 * Repositorio de documentos (sticky)
 */

var clienteIdActual = null;   // se establece al crear o al cargar edición
var modoEdicion     = false;
var refEntradas     = [];     // lista de entradas de referencia (objetos completos)
var clienteFormPerms = {
    puedeVerCreditoYReferencias: true,
    puedeEditarObservacionesGerencia: true,
};

$(document).ready(function () {
    clienteFormPerms = Object.assign(clienteFormPerms, window._clienteFormPerms || {});

    var rawId = document.getElementById('cliente_id_form').value;
    clienteIdActual = (rawId && rawId !== '' && rawId !== 'null') ? parseInt(rawId, 10) : null;
    modoEdicion     = (clienteIdActual !== null);

    if (modoEdicion) {
        // En edición, los catálogos y ubicación vienen del endpoint de datos.
        // Evita condiciones de carrera que podían sobrescribir valores cargados.
        cargarDatosCliente(clienteIdActual);
        cargarRepositorioDocumentos(clienteIdActual);
        cargarHistorialCambios(clienteIdActual);
    } else {
        // Modo crear: cargar catálogos base para poblar selects vacíos.
        cargarCatalogos();
        cargarPaises();
        // Modo crear: repositorio deshabilitado hasta tener ID
        mostrarAvisoRepo(true);
        toggleCreditoCampos();
    }

    // Enter en campo de comentario de referencia


    // Formato moneda en campo crédito y límite referencia
    $('#cred_monto, #ref_limite_credito').on('keyup blur', function () {
        formatCurrencyInput($(this));
    });

    $('#clienteTabs a[data-toggle="tab"]').on('shown.bs.tab', function () {
        actualizarBotonGuardarUnificado();
    });
    actualizarBotonGuardarUnificado();
});

function actualizarBotonGuardarUnificado() {
    var btn = $('#btn_guardar_unificado');
    if (!btn.length) return;

    var tab = ($('#clienteTabs .nav-link.active').attr('href') || '').trim();
    var label = 'Guardar';
    var disabled = false;

    if (tab === '#tab-datos' || tab === '#tab-contacto' || tab === '#tab-direccion') {
        label = modoEdicion ? 'Guardar Cambios del Cliente' : 'Registrar Cliente';
    } else if (tab === '#tab-credito') {
        label = 'Guardar Crédito';
        if (!clienteFormPerms.puedeVerCreditoYReferencias) disabled = true;
    } else if (tab === '#tab-refs') {
        label = 'Guardar Referencias';
        if (!clienteFormPerms.puedeVerCreditoYReferencias) disabled = true;
    } else if (tab === '#tab-obs') {
        label = clienteFormPerms.puedeEditarObservacionesGerencia ? 'Guardar Observación' : 'Solo lectura';
        if (!clienteFormPerms.puedeEditarObservacionesGerencia) disabled = true;
    } else if (tab === '#tab-og') {
        label = clienteFormPerms.puedeEditarObservacionesGerencia ? 'Guardar Observación Gerencia' : 'Solo lectura';
        if (!clienteFormPerms.puedeEditarObservacionesGerencia) disabled = true;
    }

    btn.html('<i class="fa fa-save"></i> ' + label);
    btn.prop('disabled', disabled);
}

function guardarClienteUnificado() {
    var tab = ($('#clienteTabs .nav-link.active').attr('href') || '').trim();

    if (tab === '#tab-datos' || tab === '#tab-contacto' || tab === '#tab-direccion') {
        guardarDatosPrincipales();
        return;
    }

    if (tab === '#tab-credito') {
        if (!clienteFormPerms.puedeVerCreditoYReferencias) {
            mostrarAlerta('info', 'Solo visualización', 'No tiene permisos para modificar Crédito.');
            return;
        }
        guardarCredito();
        return;
    }

    if (tab === '#tab-refs') {
        if (!clienteFormPerms.puedeVerCreditoYReferencias) {
            mostrarAlerta('info', 'Solo visualización', 'No tiene permisos para modificar Comentarios/Referencias.');
            return;
        }
        guardarReferencias();
        return;
    }

    if (tab === '#tab-obs') {
        if (!clienteFormPerms.puedeEditarObservacionesGerencia) {
            mostrarAlerta('info', 'Solo visualización', 'No tiene permisos para modificar Observaciones.');
            return;
        }
        guardarObservacion();
        return;
    }

    if (tab === '#tab-og') {
        if (!clienteFormPerms.puedeEditarObservacionesGerencia) {
            mostrarAlerta('info', 'Solo visualización', 'No tiene permisos para modificar Observación Gerencia.');
            return;
        }
        guardarAutorizacionGerencia();
        return;
    }

    guardarDatosPrincipales();
}

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
    // Escala/categoría: opciones cargadas server-side en el blade
    // Vendedores en datos principales (usa datos blade)
    if (window._vendedoresData) {
        // Options already rendered server-side in select#dp_vendedor
    }
    // Métodos de pago (options rendered server-side; sync both selects)
    // cred_metodo_pago options also rendered server-side
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
    document.getElementById('form_loading_overlay').style.display = '';
    axios.get('/clientes/form/datos/' + id + '?_=' + Date.now())
        .then(r => {
            var d   = r.data;
            var cli = d.datosCliente;

            // Tab Datos Principales
            $('#dp_nombre').val(cli.nombre);
            $('#dp_rtn').val(cli.rtn);
            $('#dp_ano_operacion').val(cli.ano_operacion);
            $('#dp_dni').val(cli.dni_representante_legal);
            $('#dp_estado').prop('checked', cli.estado_cliente_id == 1);
            $('#dp_vendedor').val(cli.vendedor);
            $('#dp_metodo_pago').val(cli.metodo_pago);
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

            // Tab Crédito (solo si la sección está disponible para este rol)
            if (d.credito && clienteFormPerms.puedeVerCreditoYReferencias) {
                var cr = d.credito;
                var creditoActivo = cr.credito_activo == 1;
                if ($('#cred_activo').length) $('#cred_activo').prop('checked', creditoActivo);
                if (typeof toggleCreditoCampos === 'function') toggleCreditoCampos();
                if ($('#cred_monto').length) { $('#cred_monto').val(parseFloat(cr.credito || 0).toFixed(2)); formatCurrencyInput($('#cred_monto')); }
                if ($('#cred_monto_disponible').length) {
                    var montoDisponible = Number.isFinite(parseFloat(d.monto_disponible)) ? parseFloat(d.monto_disponible) : parseFloat(cr.credito || 0);
                    $('#cred_monto_disponible').val(montoDisponible.toFixed(2));
                    formatCurrencyInput($('#cred_monto_disponible'));
                }
                if ($('#cred_dias').length) $('#cred_dias').val(cr.dias_credito);
                if ($('#cred_fecha_vigencia').length) $('#cred_fecha_vigencia').val(cr.fecha_vigencia || '');
                if ($('#cred_ref_bancarias').length) $('#cred_ref_bancarias').val(cr.referencias_bancarias);
                if ($('#cred_ref_comerciales').length) $('#cred_ref_comerciales').val(cr.referencias_comerciales);
                var letraMarcada = cr.letra_cambio == 1;
                var avalMarcado  = cr.aval_solidario == 1;
                if ($('#cred_letra_cambio').length) { $('#cred_letra_cambio').prop('checked', letraMarcada); toggleObs('obs_letra_cambio_wrap', letraMarcada); }
                if ($('#obs_letra_cambio').length) $('#obs_letra_cambio').val(cr.obs_letra_cambio || '');
                if ($('#cred_aval_solidario').length) { $('#cred_aval_solidario').prop('checked', avalMarcado); toggleObs('obs_aval_solidario_wrap', avalMarcado); }
                if ($('#obs_aval_solidario').length) $('#obs_aval_solidario').val(cr.obs_aval_solidario || '');
            }
            // Autorización en tab OG (siempre cargar si el campo existe)
            if (d.credito && $('#og_autorizacion').length) {
                $('#og_autorizacion').val(d.credito.autorizacion_gerencia || '');
            }

            // Tab Comentarios Referencias
            var cli2 = d.datosCliente;
            try {
                var rawRefs = (cli2.ref_referencias || '').trim();
                refEntradas = (rawRefs && rawRefs.charAt(0) === '[') ? JSON.parse(rawRefs) : [];
            } catch(e) { refEntradas = []; }
            renderRefEntradas();

            // Historial crédito
            renderHistoricoCredito(d.historicoCredito || []);

            // Tab Observaciones
            renderObservaciones(d.observaciones || []);

            // Tab Observación Gerencia — historial
            renderOgHistorial(d.historicoCredito || []);

            // Repositorio
            renderDocumentos(d.documentos || []);
            renderDocFisico(d.doc_fisico || []);
        })
        .catch(err => {
            console.error(err);
            mostrarAlerta('error', 'Error', 'No se pudieron cargar los datos del cliente.');
        })
        .finally(() => {
            document.getElementById('form_loading_overlay').style.display = 'none';
        });
}

/* ============================================================
   GUARDAR DATOS PRINCIPALES (+ crear cliente si modo nuevo)
   ============================================================ */
function guardarDatosPrincipales() {
    // Limpiar estado previo
    $('.form-control').removeClass('is-invalid');

    // === Tab 1: Datos Principales ===
    var nombre   = $('#dp_nombre').val().trim();
    var rtn      = $('#dp_rtn').val().trim();
    var escala   = $('#dp_escala').val();
    var tipoCli  = $('#dp_tipo_cliente').val();
    var tipoPers = $('#dp_tipo_personalidad').val();
    var vendedor = $('#dp_vendedor').val();
    var errDatos = [];
    if (!nombre)   { $('#dp_nombre').addClass('is-invalid');            errDatos.push('Nombre del Cliente'); }
    if (!rtn)      { $('#dp_rtn').addClass('is-invalid');               errDatos.push('RTN'); }
    if (!escala)   { $('#dp_escala').addClass('is-invalid');            errDatos.push('Categoría'); }
    if (!tipoCli)  { $('#dp_tipo_cliente').addClass('is-invalid');      errDatos.push('Tipo de Cliente'); }
    if (!tipoPers) { $('#dp_tipo_personalidad').addClass('is-invalid'); errDatos.push('Tipo de Personalidad'); }
    if (!vendedor) { $('#dp_vendedor').addClass('is-invalid');          errDatos.push('Vendedor'); }
    if (errDatos.length) {
        $('#tab-datos-tab').tab('show');
        mostrarAlerta('warning', 'Datos Principales incompletos', 'Complete los siguientes campos: ' + errDatos.join(', ') + '.');
        return;
    }

    // === Tab 2: Contacto ===
    var telEmpresa = $('#ct_telefono').val().trim();
    var nombre1    = $('#ct_nombre1').val().trim();
    var tel1       = $('#ct_telefono1').val().trim();
    var errCont = [];
    if (!telEmpresa) { $('#ct_telefono').addClass('is-invalid');  errCont.push('Teléfono Empresa'); }
    if (!nombre1)    { $('#ct_nombre1').addClass('is-invalid');   errCont.push('Nombre Contacto 1'); }
    if (!tel1)       { $('#ct_telefono1').addClass('is-invalid'); errCont.push('Teléfono Contacto 1'); }
    if (errCont.length) {
        $('#tab-contacto-tab').tab('show');
        mostrarAlerta('warning', 'Contacto incompleto', 'Complete los siguientes campos: ' + errCont.join(', ') + '.');
        return;
    }

    // === Tab 3: Dirección ===
    var pais      = $('#dir_pais').val();
    var depto     = $('#dir_depto').val();
    var municipio = $('#dir_municipio').val();
    var direccion = $('#dir_direccion').val().trim();
    var errDir = [];
    if (!pais)      { $('#dir_pais').addClass('is-invalid');      errDir.push('País'); }
    if (!depto)     { $('#dir_depto').addClass('is-invalid');     errDir.push('Departamento'); }
    if (!municipio) { $('#dir_municipio').addClass('is-invalid'); errDir.push('Municipio'); }
    if (!direccion) { $('#dir_direccion').addClass('is-invalid'); errDir.push('Dirección Completa'); }
    if (errDir.length) {
        $('#tab-direccion-tab').tab('show');
        mostrarAlerta('warning', 'Dirección incompleta', 'Complete los siguientes campos: ' + errDir.join(', ') + '.');
        return;
    }

    var creditoRaw = '';
    if ($('#cred_monto').length) {
        creditoRaw = ($('#cred_monto').val() || '').toString();
    }

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
        dp_vendedor_id:            $('#dp_vendedor').val(),
        dp_metodo_pago:            $('#dp_metodo_pago').val(),
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
        // crédito (sólo usado en modo crear)
        vendedor_id:               $('#dp_vendedor').val(),
        credito:                   creditoRaw.replace(/,/g, ''),
        dias_credito:              $('#cred_dias').val(),
        credito_activo:            $('#cred_activo').is(':checked') ? 1 : 0,
        referencias_bancarias:     ($('#cred_ref_bancarias').val() || '').trim(),
        referencias_comerciales:   ($('#cred_ref_comerciales').val() || '').trim(),
        letra_cambio:              $('#cred_letra_cambio').is(':checked') ? 1 : 0,
        obs_letra_cambio:          ($('#obs_letra_cambio').val() || '').trim(),
        aval_solidario:            $('#cred_aval_solidario').is(':checked') ? 1 : 0,
        obs_aval_solidario:        ($('#obs_aval_solidario').val() || '').trim(),
        autorizacion_gerencia:     ($('#cred_autorizacion').val() || '').trim(),
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
            }            if (clienteIdActual) cargarHistorialCambios(clienteIdActual);        })
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
        dp_vendedor_id:     $('#dp_vendedor').val(),
        dp_metodo_pago:     $('#dp_metodo_pago').val(),
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
    };
    $('#btn_guardar_contacto').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/editar-completo', payload)
        .then(r => { mostrarAlerta(r.data.icon, r.data.title, r.data.text); if (clienteIdActual) cargarHistorialCambios(clienteIdActual); })
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
        dp_vendedor_id:   $('#dp_vendedor').val(),
        dp_metodo_pago:   $('#dp_metodo_pago').val(),
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
    };
    $('#btn_guardar_direccion').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/editar-completo', payload)
        .then(r => { mostrarAlerta(r.data.icon, r.data.title, r.data.text); if (clienteIdActual) cargarHistorialCambios(clienteIdActual); })
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

    var creditoActivo = $('#cred_activo').is(':checked');
    if (!creditoActivo) {
        mostrarAlerta('warning', 'Crédito no habilitado', 'Debe marcar la casilla "Crédito Disponible" para guardar.');
        return;
    }
    var monto = parseFloat($('#cred_monto').val().replace(/,/g, ''));
    if (!monto || monto <= 0) { mostrarAlerta('warning', 'Falta', 'El monto de crédito no puede ser 0 o estar vacío.'); return; }
    var dias = parseInt($('#cred_dias').val());
    if (!dias || dias <= 0) { mostrarAlerta('warning', 'Falta', 'Los días de crédito no pueden ser 0 o estar vacíos.'); return; }

    var payload = {
        _token:                   document.getElementById('csrf_token').value,
        cliente_id:               clienteIdActual,
        credito_activo:           1,
        credito:                  $('#cred_monto').val().replace(/,/g, ''),
        dias_credito:             $('#cred_dias').val(),
        fecha_vigencia:           $('#cred_fecha_vigencia').val(),
        vendedor_id:              $('#dp_vendedor').val(),
        referencias_bancarias:    $('#cred_ref_bancarias').val().trim(),
        referencias_comerciales:  $('#cred_ref_comerciales').val().trim(),
        letra_cambio:             $('#cred_letra_cambio').is(':checked') ? 1 : 0,
        obs_letra_cambio:         $('#obs_letra_cambio').val().trim(),
        aval_solidario:           $('#cred_aval_solidario').is(':checked') ? 1 : 0,
        obs_aval_solidario:       $('#obs_aval_solidario').val().trim(),
        autorizacion_gerencia:    ($('#og_autorizacion').val() || '').trim(),
    };

    $('#btn_guardar_credito').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/credito/guardar', payload)
        .then(r => {
            mostrarAlerta(r.data.icon, r.data.title, r.data.text);
            // Actualizar monto disponible con el valor recalculado por el servidor
            if (r.data.monto_disponible !== undefined) {
                $('#cred_monto_disponible').val(parseFloat(r.data.monto_disponible).toFixed(2));
                formatCurrencyInput($('#cred_monto_disponible'));
            }
            recargarHistoricoCredito();
            if (clienteIdActual) cargarHistorialCambios(clienteIdActual);
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
        .then(r => {
            renderHistoricoCredito(r.data.historico || []);
            renderOgHistorial(r.data.historico || []);
        });
}

function renderHistoricoCredito(rows) {
    var cont  = document.getElementById('historico_credito_container');
    var empty = document.getElementById('historico_credito_empty');
    if (!cont) return;
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
        var letraTxt = h.letra_cambio == 1
            ? '<span class="badge badge-success">Sí</span>' + (h.obs_letra_cambio ? ' <small>' + escapeHtml(h.obs_letra_cambio) + '</small>' : '')
            : '<span class="badge badge-secondary">No</span>';
        var avalTxt = h.aval_solidario == 1
            ? '<span class="badge badge-success">Sí</span>' + (h.obs_aval_solidario ? ' <small>' + escapeHtml(h.obs_aval_solidario) + '</small>' : '')
            : '<span class="badge badge-secondary">No</span>';
        var activoBadge = h.activo == 1
            ? '<span class="badge" style="background:#1ab394;color:#fff">Vigente</span>'
            : '<span class="badge badge-light text-muted">Histórico</span>';
        html += '<div class="historico-item">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div><strong>L ' + parseFloat(h.credito || 0).toLocaleString('es-HN', {minimumFractionDigits:2}) + '</strong> &nbsp;' + badge + ' &nbsp;' + activoBadge + '</div>' +
            '</div>' +
            '<div class="mt-1" style="font-size:0.85rem">' +
            (h.autorizacion_gerencia ? '<em>"' + escapeHtml(h.autorizacion_gerencia) + '"</em>' : '') +
            '</div>' +
            '<div class="mt-1" style="font-size:0.82rem">Letra de cambio: ' + letraTxt + ' &nbsp;|&nbsp; Aval solidario: ' + avalTxt + '</div>' +
            '<div class="hi-meta">' +
            'Días crédito: ' + (h.dias_credito || 0) +
            (h.fecha_vigencia ? ' · Vigente hasta: ' + h.fecha_vigencia : '') +
            ' · Vendedor: ' + escapeHtml(h.nombre_vendedor || ('ID ' + (h.vendedor_id || '—'))) +
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
    if (!clienteFormPerms.puedeEditarObservacionesGerencia) {
        mostrarAlerta('info', 'Solo visualización', 'No tiene permisos para modificar Observaciones.');
        return;
    }
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
   COMENTARIOS / REFERENCIAS
   ============================================================ */
function guardarReferencias() {
    if (!checkClienteCreado()) return;
    if (!clienteFormPerms.puedeVerCreditoYReferencias) {
        mostrarAlerta('info', 'Solo visualización', 'No tiene permisos para modificar Comentarios/Referencias.');
        return;
    }
    var payload = {
        _token:              document.getElementById('csrf_token').value,
        cliente_id:          clienteIdActual,
        ref_referencias:     JSON.stringify(refEntradas),
        ref_tiempo_relacion: '',
        ref_tiempo_credito:  '',
        ref_limite_credito:  null,
        ref_observaciones:   '',
    };
    $('#btn_guardar_refs').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/referencias/guardar', payload)
        .then(r => {
            mostrarAlerta(r.data.icon, r.data.title, r.data.text);
            if (clienteIdActual) cargarHistorialCambios(clienteIdActual);
        })
        .catch(err => {
            var d = err.response ? err.response.data : {};
            mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al guardar.');
        })
        .finally(() => $('#btn_guardar_refs').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Comentarios/Referencias'));
}

/* ============================================================
   ENTRADAS DE REFERENCIA (multi-bloque completo)
   ============================================================ */
function renderRefEntradas() {
    var cont = document.getElementById('ref_entradas_list');
    if (!cont) return;
    if (!refEntradas.length) {
        cont.innerHTML = '<p class="text-muted mb-0" style="font-size:.82rem">Sin referencias registradas. Use el formulario de arriba para agregar.</p>';
        return;
    }
    var html = '';
    refEntradas.forEach(function (e, i) {
        html += '<div class="ref-entry-card">' +
            '<div class="ref-entry-header">' +
            '<span class="ref-entry-num">Referencia #' + (i + 1) + '</span>' +
            '<button class="btn-ref-del" title="Eliminar" onclick="eliminarRefEntrada(' + i + ')">' +
            '<i class="fa fa-times"></i></button>' +
            '</div>';
        if (e.comentario) {
            html += '<div class="ref-entry-comentario">' + escapeHtml(e.comentario) + '</div>';
        }
        html += '<div class="ref-entry-fields">';
        if (e.tiempo_relacion) {
            html += '<div class="ref-entry-field"><span class="ref-field-label">T. Relaci\u00f3n</span>' +
                '<span class="ref-field-val">' + escapeHtml(e.tiempo_relacion) + '</span></div>';
        }
        if (e.tiempo_credito) {
            html += '<div class="ref-entry-field"><span class="ref-field-label">T. Cr\u00e9dito</span>' +
                '<span class="ref-field-val">' + escapeHtml(e.tiempo_credito) + '</span></div>';
        }
        if (e.limite_credito) {
            html += '<div class="ref-entry-field"><span class="ref-field-label">L\u00edmite</span>' +
                '<span class="ref-field-val">L ' + escapeHtml(String(e.limite_credito)) + '</span></div>';
        }
        if (e.observaciones) {
            html += '<div class="ref-entry-field"><span class="ref-field-label">Obs.</span>' +
                '<span class="ref-field-val">' + escapeHtml(e.observaciones) + '</span></div>';
        }
        html += '</div></div>';
    });
    cont.innerHTML = html;
}

function agregarRefEntrada() {
    var comentario    = (document.getElementById('ref_comentario_input').value    || '').trim();
    var tRelacion     = (document.getElementById('ref_tiempo_relacion_input').value || '').trim();
    var tCredito      = (document.getElementById('ref_tiempo_credito_input').value  || '').trim();
    var limiteRaw     = (document.getElementById('ref_limite_credito_input').value  || '').replace(/,/g, '').trim();
    var observaciones = (document.getElementById('ref_observaciones_input').value   || '').trim();
    if (!comentario && !tRelacion && !tCredito && !limiteRaw && !observaciones) {
        mostrarAlerta('warning', 'Atenci\u00f3n', 'Ingrese al menos un dato para la referencia.');
        return;
    }
    refEntradas.push({
        comentario:      comentario,
        tiempo_relacion: tRelacion,
        tiempo_credito:  tCredito,
        limite_credito:  limiteRaw,
        observaciones:   observaciones,
    });
    renderRefEntradas();
    document.getElementById('ref_comentario_input').value    = '';
    document.getElementById('ref_tiempo_relacion_input').value = '';
    document.getElementById('ref_tiempo_credito_input').value  = '';
    document.getElementById('ref_limite_credito_input').value  = '';
    document.getElementById('ref_observaciones_input').value   = '';
    document.getElementById('ref_comentario_input').focus();
}

function eliminarRefEntrada(idx) {
    refEntradas.splice(idx, 1);
    renderRefEntradas();
}

/* ============================================================
   OBSERVACIÓN GERENCIA
   ============================================================ */
function guardarAutorizacionGerencia() {
    if (!checkClienteCreado()) return;
    if (!clienteFormPerms.puedeEditarObservacionesGerencia) {
        mostrarAlerta('info', 'Solo visualización', 'No tiene permisos para modificar Observación Gerencia.');
        return;
    }
    var texto = $('#og_autorizacion').val().trim();
    if (!texto) { mostrarAlerta('warning', 'Falta', 'Escriba la autorización o comentario de gerencia.'); return; }

    $('#btn_guardar_og').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/autorizacion/guardar', {
        _token:                  document.getElementById('csrf_token').value,
        cliente_id:              clienteIdActual,
        autorizacion_gerencia:   texto,
    })
    .then(r => {
        mostrarAlerta(r.data.icon, r.data.title, r.data.text);
        recargarHistoricoCredito();
        if (clienteIdActual) cargarHistorialCambios(clienteIdActual);
    })
    .catch(err => {
        var d = err.response ? err.response.data : {};
        mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al guardar.');
    })
    .finally(() => $('#btn_guardar_og').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Autorización Gerencia'));
}

function renderOgHistorial(rows) {
    var cont = document.getElementById('og_historial_container');
    if (!cont) return;
    var conAuth = (rows || []).filter(function (h) { return h.autorizacion_gerencia && h.autorizacion_gerencia.trim() !== ''; });
    if (conAuth.length === 0) {
        cont.innerHTML = '<p class="text-muted text-center" style="font-size:0.85rem">Sin historial disponible.</p>';
        return;
    }
    var html = '';
    conAuth.forEach(function (h) {
        var badge = h.activo == 1
            ? '<span class="badge" style="background:#1ab394;color:#fff;font-size:0.7rem">Vigente</span>'
            : '<span class="badge badge-light text-muted" style="font-size:0.7rem">Histórico</span>';
        html += '<div class="obs-item">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<span>' + badge + '</span>' +
            '</div>' +
            '<div style="font-size:0.9rem; margin-top:6px;">' + escapeHtml(h.autorizacion_gerencia) + '</div>' +
            '<div class="obs-meta">' +
            'Registrado por: ' + escapeHtml(h.usuario || '—') + ' · ' + formatFecha(h.created_at) +
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
        .then(r => {
            renderDocumentos(r.data.documentos || []);
            renderDocFisico(r.data.doc_fisico || []);
        });
}

function renderDocFisico(tipos) {
    var slugs = ['escritura_empresa','dni_representante','rtn','permiso_operacion','croquis','contrato_arrendamiento','foto_establecimiento'];
    slugs.forEach(function (slug) {
        var chk = document.getElementById('chk_fisico_' + slug);
        var lbl = document.getElementById('lbl_fisico_' + slug);
        if (!chk) return;
        var activo = tipos.indexOf(slug) !== -1;
        chk.checked = activo;
        if (lbl) {
            if (activo) lbl.classList.add('activo'); else lbl.classList.remove('activo');
        }
    });
}

function toggleDocFisico(slug, checkbox) {
    if (!checkClienteCreado()) {
        checkbox.checked = !checkbox.checked; // revert
        return;
    }
    var formData = new FormData();
    formData.append('_token',         document.getElementById('csrf_token').value);
    formData.append('cliente_id',     clienteIdActual);
    formData.append('tipo_documento', slug);

    axios.post('/clientes/documento/fisico/toggle', formData)
        .then(function (r) {
            var lbl = document.getElementById('lbl_fisico_' + slug);
            if (lbl) {
                if (r.data.activo) lbl.classList.add('activo'); else lbl.classList.remove('activo');
            }
            if (clienteIdActual) cargarHistorialCambios(clienteIdActual);
        })
        .catch(function () {
            checkbox.checked = !checkbox.checked; // revert on error
        });
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
        if (docs.length === 0) {
            cont.innerHTML = '<span class="text-muted" style="font-size:0.78rem">Sin documento cargado</span>';
            return;
        }
        var html = '';
        docs.forEach(function (doc) {
            var ext = (doc.ruta_archivo || '').split('.').pop().toLowerCase();
            var iconClass = ['pdf'].includes(ext) ? 'fa-file-pdf-o text-danger' :
                           ['png','jpg','jpeg','gif'].includes(ext) ? 'fa-file-image-o text-success' :
                           'fa-file-o text-secondary';
            html += '<div class="doc-item" style="cursor:pointer;" onclick="abrirPreviewDoc(' + doc.id + ',\'' + slug + '\',\'' + escapeAttr(doc.nombre_original) + '\',\'' + ext + '\')">' +
                '<span class="doc-name"><i class="fa ' + iconClass + ' mr-1"></i>' + escapeHtml(doc.nombre_original) + '</span>' +
                '<div class="doc-actions">' +
                '<a href="/clientes/documento/descargar/' + doc.id + '" class="btn btn-xs btn-success" title="Descargar" target="_blank" onclick="event.stopPropagation()">' +
                '<i class="fa fa-download"></i></a>' +
                '<button class="btn btn-xs btn-info" title="Ver" onclick="event.stopPropagation();abrirPreviewDoc(' + doc.id + ',\'' + slug + '\',\'' + escapeAttr(doc.nombre_original) + '\',\'' + ext + '\')"><i class="fa fa-eye"></i></button>' +
                '<button class="btn btn-xs btn-danger" title="Eliminar" onclick="event.stopPropagation();confirmarEliminarDoc(' + doc.id + ')"><i class="fa fa-trash"></i></button>' +
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
        if (clienteIdActual) cargarHistorialCambios(clienteIdActual);
    })
    .catch(err => {
        var d = err.response ? err.response.data : {};
        mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al eliminar.');
    });
}

/* ============================================================
   VISTA PREVIA DE DOCUMENTO
   ============================================================ */
var _docPreviewTipo = null;

function abrirPreviewDoc(docId, tipo, nombreOriginal, extension) {
    _docPreviewTipo = tipo;
    document.getElementById('modalDocPreviewLabel').textContent = nombreOriginal;
    var previewArea = document.getElementById('doc_preview_area');
    previewArea.innerHTML = '<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>';

    var url = '/clientes/documento/ver/' + docId;
    var ext = (extension || '').toLowerCase();

    if (ext === 'pdf') {
        previewArea.innerHTML = '<iframe src="' + url + '" width="100%" height="520" style="border:none; display:block;"></iframe>';
    } else if (['png','jpg','jpeg','gif'].includes(ext)) {
        previewArea.innerHTML = '<img src="' + url + '" class="img-fluid" style="max-height:520px; display:block; margin:0 auto; padding:10px;">';
    } else {
        previewArea.innerHTML = '<div class="text-center p-4">' +
            '<i class="fa fa-file fa-3x text-muted mb-3 d-block"></i>' +
            '<p class="text-muted">Vista previa no disponible para este tipo de archivo.</p>' +
            '<a href="/clientes/documento/descargar/' + docId + '" class="btn btn-primary" target="_blank">' +
            '<i class="fa fa-download"></i> Descargar archivo</a></div>';
    }

    document.getElementById('btn_modal_descargar').href = '/clientes/documento/descargar/' + docId;
    document.getElementById('btn_modal_eliminar').onclick = function () {
        Swal.fire({
            title: '¿Eliminar documento?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#modalDocPreview').modal('hide');
                eliminarDocumento(docId);
            }
        });
    };
    document.getElementById('file_modal_reemplazar').value = '';
    document.getElementById('btn_modal_reemplazar').onclick = function () {
        subirDocumentoDesdeModal(tipo);
    };
    $('#modalDocPreview').modal('show');
}

function subirDocumentoDesdeModal(tipo) {
    if (!checkClienteCreado()) return;
    var fileInput = document.getElementById('file_modal_reemplazar');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        mostrarAlerta('warning', 'Falta', 'Seleccione un archivo para subir.');
        return;
    }
    var formData = new FormData();
    formData.append('_token',         document.getElementById('csrf_token').value);
    formData.append('cliente_id',     clienteIdActual);
    formData.append('tipo_documento', tipo);
    formData.append('documento',      fileInput.files[0]);
    $('#btn_modal_reemplazar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    axios.post('/clientes/documento/subir', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(function (r) {
            mostrarAlerta(r.data.icon, r.data.title, r.data.text);
            $('#modalDocPreview').modal('hide');
            cargarRepositorioDocumentos(clienteIdActual);
            if (clienteIdActual) cargarHistorialCambios(clienteIdActual);
        })
        .catch(function (err) {
            var d = err.response ? err.response.data : {};
            mostrarAlerta(d.icon || 'error', d.title || 'Error', d.text || 'Error al subir.');
        })
        .finally(function () {
            $('#btn_modal_reemplazar').prop('disabled', false).html('<i class="fa fa-upload"></i> Reemplazar');
        });
}

/* ============================================================
   HISTORIAL DE CAMBIOS
   ============================================================ */
function cargarHistorialCambios(id) {
    if (!id) return;
    axios.get('/clientes/historial/' + id)
        .then(function (r) { renderHistorialCambios(r.data.historial || []); })
        .catch(function () {});
}

function renderHistorialCambios(rows) {
    var cont = document.getElementById('historial_cambios_container');
    if (!cont) return;
    if (!rows || rows.length === 0) {
        cont.innerHTML = '<p class="text-muted text-center" style="font-size:0.8rem">Sin historial de cambios.</p>';
        return;
    }
    var html = '';
    rows.forEach(function (h) {
        html += '<div class="historico-item mb-2">' +
            '<div style="font-size:0.82rem;font-weight:600;">' + escapeHtml(h.accion) + '</div>' +
            (h.descripcion ? '<div style="font-size:0.78rem;color:#555;margin-top:2px;">' + escapeHtml(h.descripcion) + '</div>' : '') +
            '<div class="hi-meta">' + escapeHtml(h.usuario || '—') + ' · ' + formatFecha(h.created_at) + '</div>' +
            '</div>';
    });
    cont.innerHTML = html;
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

/* muestra/oculta el textarea de observación vinculado a un checkbox */
function toggleObs(wrapId, show) {
    var el = document.getElementById(wrapId);
    if (el) el.style.display = show ? '' : 'none';
}

function toggleCreditoCampos() {
    var activo = $('#cred_activo').is(':checked');
    var wrap = document.getElementById('credito_campos_condicionales');
    if (wrap) wrap.style.display = activo ? '' : 'none';
    // El boton guardar solo enabled cuando activo
    var btn = document.getElementById('btn_guardar_credito');
    if (btn) btn.disabled = !activo;
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

function escapeAttr(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

function mostrarAlerta(icon, title, text) {
    Swal.fire({
        icon: icon || 'info',
        title: title || '',
        text: text || '',
        customClass: { container: 'swal-over-modal' }
    });
}

function formatCurrencyInput($input) {
    if (!$input || !$input.length) return;
    var raw = $input.val();
    if (raw === undefined || raw === null) return;
    var val = raw.toString().replace(/[^0-9.]/g, '');
    var parts = val.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    $input.val(parts.join('.'));
}
