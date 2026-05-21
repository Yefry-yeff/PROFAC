function cargarCategoriasClienteEnModal(selected = null) {
  const $sel = $('#categoria_cliente_id');
  const url  = $sel.data('url');

  $sel.empty().append('<option value="">Seleccione una categoría...</option>');

  $.getJSON(url)
    .done(res => {
      (res.categorias || []).forEach(c => {
        $sel.append(`<option value="${c.id}">${c.nombre_categoria}</option>`);
      });

      // SI VIENES EDITANDO → marcar la opción correcta
      if (selected) {
        $sel.val(selected);
      }

    })
    .fail(() => {
      Swal.fire({
        icon:'error',
        title:'Error',
        text:'No se pudo cargar Categoría de Cliente.'
      });
    });
}


function cargarRolesEnModal(selected = null) {
  const $sel = $('#rol_id');
  const url  = $sel.data('url');

  $sel.empty().append('<option value="">Seleccione un rol...</option>');

  $.getJSON(url)
    .done(res => {
      (res.roles || []).forEach(c => {
        $sel.append(`<option value="${c.id}">${c.nombre}</option>`);
      });

      if (selected) {
        $sel.val(selected);
      }

    })
    .fail(() => {
      Swal.fire({
        icon:'error',
        title:'Error',
        text:'No se pudo cargar roles de usuario.'
      });
    });
}


$('#modalParamComision').on('shown.bs.modal', function () {
    // Cargar opciones siempre (editar ahora usa #modalEditarPct)
    cargarCategoriasClienteEnModal();
    cargarRolesEnModal();
});


/*Registro de parametro de comisión — recoge filas de la tabla dinámica */
function registrarParametroComision() {
  var $btn  = $('#btn_guardar_parametro_comision');
  var cat   = $('#categoria_cliente_id').val();
  var rol   = $('#rol_id').val();
  var nombre = $('#nombre_comescala').val().trim();

  if (!cat || !rol) {
    Swal.fire({ icon:'warning', title:'Faltan datos', text:'Seleccione rol y categoría de cliente.' });
    return;
  }
  if (!nombre) {
    Swal.fire({ icon:'warning', title:'Faltan datos', text:'Ingrese un título para la configuración.' });
    return;
  }

  // Recoger filas con % ingresado
  var filas = [];
  $('#tbody_categorias_precio tr').each(function() {
    var catPreId = $(this).data('cat-id');
    var pct      = $(this).find('.pct-input').val();
    if (catPreId && pct !== '' && parseFloat(pct) > 0) {
      filas.push({ categoria_precios_id: catPreId, porcentaje: pct });
    }
  });

  if (!filas.length) {
    Swal.fire({ icon:'warning', title:'Sin porcentajes', text:'Ingrese al menos un porcentaje mayor a 0.' });
    return;
  }

  var payload = new FormData();
  payload.append('nombre_comescala',    nombre);
  payload.append('categoria_cliente_id', cat);
  payload.append('rol_id',              rol);
  filas.forEach(function(f, i) {
    payload.append('filas[' + i + '][categoria_precios_id]', f.categoria_precios_id);
    payload.append('filas[' + i + '][porcentaje]',           f.porcentaje);
  });

  $btn.prop('disabled', true);

  axios.post('/guardar/parametro/comision', payload)
    .then(function(res) {
      $('#modalParamComision').modal('hide');
      $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);
      if (typeof cargarStats === 'function') { cargarStats(); }
      _resumenCargado = false;
      Swal.fire({ icon: res.data.icon, title: res.data.title, text: res.data.text });
    })
    .catch(function(err) {
      var d = err.response?.data || { icon:'error', title:'Error', text:'Ocurrió un error.' };
      Swal.fire({ icon: d.icon, title: d.title, text: d.text });
    })
    .finally(function() { $btn.prop('disabled', false); });
}

$(document).on('submit', '#paramComisionForm', function(event) {
  event.preventDefault();
  registrarParametroComision();
});

listaParametroComision();

/*Listando los registros */
function listaParametroComision() {
  $('#tbl_listaParametroComision').DataTable({
    destroy: true,
    order: [0, 'desc'],
    language: { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
    pageLength: 10,
    responsive: true,
    deferRender: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [{ extend: 'excel', title: 'Parametrizacion_comisiones' }],
    ajax: {
      url: "/listar/parametros/comision",
      dataSrc: 'data',
      error: function() {
        Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar la tabla.' });
      }
    },
    columns: [
        { data: 'id' },
        { data: 'nombre' },
        { data: 'porcentaje_comision', render: function(d) { return d + '%'; } },
        { data: 'rol' },
        { data: 'cliente_cat_escala' },
        { data: 'categoria_precio' },
        { data: 'userRegistro' },
        { data: 'fechaRegistro' },
        { data: 'estado', orderable: false },
        { data: 'opciones', orderable: false }
    ]
  });
}

function desactivarCategoria(id, rol) {
    Swal.fire({
        title: '¿Desactivar este parámetro?',
        html: '<b>Advertencia:</b><br>Al desactivar, <span class="text-danger font-weight-bold">el rol ' + rol + ' dejará de recibir comisión por esta categoría de precio.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then(function(result) {
        if (result.isConfirmed) {
            axios.post('/desactivar/parametro-comision/' + id)
                .then(function(res) {
                    Swal.fire({ icon: res.data.icon, title: res.data.title, text: res.data.text });
                    $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);
                    if (typeof cargarStats === 'function') { cargarStats(); }
                    _resumenCargado = false;
                })
                .catch(function() {
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudo desactivar el parámetro.' });
                });
        }
    });
}

/* ============================================================
 * CONTROL DE CÁLCULO DE COMISIONES POR ROL
 * ============================================================ */

/**
 * Carga la tabla de roles con su estado de cálculo.
 */
function cargarRolesCalculo() {
    $('#rc-loading').show();
    $('#rc-grid').hide();

    $.getJSON('/comisiones/configuracion/roles-calculo')
        .done(function(res) {
            renderRolesCalculo(res.roles || []);
        })
        .fail(function() {
            $('#rc-loading').html(
                '<p style="color:#ef4444;font-size:12px;">' +
                '<i class="fa fa-exclamation-triangle mr-1"></i>Error al cargar los roles.</p>'
            );
        });
}

// Datos globales para filtrado/paginación sin recargar desde el servidor
var _rolesCalculo   = [];
var _rolesFiltrados = [];
var _rcPage         = 1;
var _rcPageSize     = 5;

/**
 * Genera las filas de la tabla de roles.
 */
function renderRolesCalculo(roles) {
    _rolesCalculo   = roles;
    _rolesFiltrados = roles;
    _rcPage         = 1;

    if (!roles.length) {
        $('#rc-loading').html('<p style="font-size:12px;color:#94a3b8;">No hay roles disponibles.</p>');
        return;
    }

    _buildRowsRoles();
    $('#rc-loading').hide();
    $('#rc-grid').show();
}

function _buildRowsRoles() {
    var roles    = _rolesFiltrados;
    var total    = roles.length;
    var totalPag = Math.max(1, Math.ceil(total / _rcPageSize));
    if (_rcPage > totalPag) _rcPage = totalPag;

    var desde = (_rcPage - 1) * _rcPageSize;
    var hasta = Math.min(desde + _rcPageSize, total);
    var paged = roles.slice(desde, hasta);

    // Contador
    var activos = _rolesCalculo.filter(function(r){ return parseInt(r.calcular)===1; }).length;
    var textoFiltro = (_rolesFiltrados.length < _rolesCalculo.length)
        ? _rolesFiltrados.length + ' encontrados &nbsp;/&nbsp; '
        : '';
    $('#rc-contador').html(
        '<span style="font-weight:700;color:#15803d;">' + activos + ' activos</span>' +
        ' &nbsp;/&nbsp; ' + textoFiltro + _rolesCalculo.length + ' roles'
    );

    // Filas
    var tbody = '';
    paged.forEach(function(r) {
        var activo      = parseInt(r.calcular) === 1;
        var tieneEscala = parseInt(r.tiene_escala) === 1;
        var checked     = activo ? 'checked' : '';
        var rowClass    = activo ? '' : 'rc-row-off';
        var inicial     = r.nombre.charAt(0).toUpperCase();
        var modInfo     = r.modificado_por
            ? '<i class="fa fa-user-o mr-1"></i>' + r.modificado_por
            : '<span style="color:#cbd5e1;">Sin cambios</span>';

        var badgeEstado = activo
            ? '<span class="rc-badge rc-badge-on"><i class="fa fa-circle"></i> Activo</span>'
            : '<span class="rc-badge rc-badge-off"><i class="fa fa-circle"></i> Inactivo</span>';

        var badgeEscala = tieneEscala
            ? '<span class="rc-badge rc-badge-escala"><i class="fa fa-percent"></i> Con escala</span>'
            : '<span class="rc-badge rc-badge-sin"><i class="fa fa-minus"></i> Sin escala</span>';

        tbody +=
            '<tr id="rc-row-' + r.id + '" class="' + rowClass + '" data-nombre="' + r.nombre.toLowerCase() + '">' +
                '<td>' +
                    '<div class="rc-td-nombre">' +
                        '<span class="rc-avatar">' + inicial + '</span>' +
                        r.nombre +
                    '</div>' +
                '</td>' +
                '<td>' + badgeEstado + '</td>' +
                '<td>' + badgeEscala + '</td>' +
                '<td class="rc-td-meta">' + modInfo + '</td>' +
                '<td class="text-center">' +
                    '<label class="rc-toggle" title="' + (activo ? 'Desactivar' : 'Activar') + '">' +
                        '<input type="checkbox" ' + checked + ' onchange="toggleCalculoRol(' + r.id + ', this)">' +
                        '<span class="rc-slider"></span>' +
                    '</label>' +
                '</td>' +
            '</tr>';
    });
    document.getElementById('tbl-roles-calculo-body').innerHTML = tbody;

    // Paginación
    _renderPaginacionRoles(desde, hasta, total, totalPag);
}

function _renderPaginacionRoles(desde, hasta, total, totalPag) {
    var info = '<span style="font-size:11px;color:#94a3b8;">' +
        'Mostrando <strong style="color:#334155;">' + (desde + 1) + '–' + hasta + '</strong>' +
        ' de <strong style="color:#334155;">' + total + '</strong> roles' +
        '</span>';

    var btns = '<div style="display:flex;gap:4px;align-items:center;">';

    // Anterior
    btns += '<button onclick="_rcIrPag(' + (_rcPage - 1) + ')" ' +
        (_rcPage <= 1 ? 'disabled' : '') +
        ' style="width:30px;height:30px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;' +
        'color:#64748b;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;' +
        'transition:all .15s;" ' +
        'onmouseover="if(!this.disabled)this.style.borderColor=\'#3b82f6\'" ' +
        'onmouseout="this.style.borderColor=\'#e2e8f0\'">' +
        '<i class="fa fa-chevron-left"></i></button>';

    // Números de página
    for (var p = 1; p <= totalPag; p++) {
        var esCurrent = p === _rcPage;
        btns += '<button onclick="_rcIrPag(' + p + ')" ' +
            'style="width:30px;height:30px;border-radius:7px;border:1.5px solid ' +
            (esCurrent ? '#3b82f6' : '#e2e8f0') + ';' +
            'background:' + (esCurrent ? '#3b82f6' : '#fff') + ';' +
            'color:' + (esCurrent ? '#fff' : '#64748b') + ';' +
            'cursor:pointer;font-size:12px;font-weight:' + (esCurrent ? '800' : '500') + ';' +
            'display:flex;align-items:center;justify-content:center;transition:all .15s;">' +
            p + '</button>';
    }

    // Siguiente
    btns += '<button onclick="_rcIrPag(' + (_rcPage + 1) + ')" ' +
        (_rcPage >= totalPag ? 'disabled' : '') +
        ' style="width:30px;height:30px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;' +
        'color:#64748b;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;' +
        'transition:all .15s;" ' +
        'onmouseover="if(!this.disabled)this.style.borderColor=\'#3b82f6\'" ' +
        'onmouseout="this.style.borderColor=\'#e2e8f0\'">' +
        '<i class="fa fa-chevron-right"></i></button>';

    btns += '</div>';

    document.getElementById('rc-pagination').innerHTML = info + btns;
}

function _rcIrPag(p) {
    var totalPag = Math.max(1, Math.ceil(_rolesFiltrados.length / _rcPageSize));
    if (p < 1 || p > totalPag) return;
    _rcPage = p;
    _buildRowsRoles();
}

/**
 * Filtra las filas visibles de la tabla según texto.
 */
function filtrarTablaRoles(q) {
    q = (q || '').toLowerCase().trim();
    _rolesFiltrados = q
        ? _rolesCalculo.filter(function(r) { return r.nombre.toLowerCase().includes(q); })
        : _rolesCalculo;
    _rcPage = 1;
    _buildRowsRoles();
}

/**
 * Envía el toggle al servidor y actualiza la fila visualmente.
 */
function toggleCalculoRol(rolId, checkbox) {
    checkbox.disabled = true;

    axios.post('/comisiones/configuracion/roles-calculo/toggle', { rol_id: rolId })
        .then(function(res) {
            var d      = res.data;
            var activo = d.calcular === 1;
            var row    = document.getElementById('rc-row-' + rolId);

            if (row) {
                row.className = activo ? '' : 'rc-row-off';

                // Columna Estado (índice 1)
                row.cells[1].innerHTML = activo
                    ? '<span class="rc-badge rc-badge-on"><i class="fa fa-circle"></i> Activo</span>'
                    : '<span class="rc-badge rc-badge-off"><i class="fa fa-circle"></i> Inactivo</span>';

                // Columna Último Cambio (índice 3) — actualizar sin recargar
                if (d.modificado_por) {
                    row.cells[3].innerHTML = '<i class="fa fa-user-o mr-1"></i>' + d.modificado_por;
                }

                // Corregir estado del checkbox y su tooltip
                checkbox.checked  = activo;
                checkbox.disabled = false;
                checkbox.closest('label').title = activo ? 'Desactivar' : 'Activar';
            }

            // Actualizar el array en memoria para que filtro/paginación sean consistentes
            var idx = _rolesCalculo.findIndex(function(r){ return parseInt(r.id) === rolId; });
            if (idx !== -1) {
                _rolesCalculo[idx].calcular      = d.calcular;
                _rolesCalculo[idx].modificado_por = d.modificado_por;
                // Sincronizar también en _rolesFiltrados si aplica
                var idxF = _rolesFiltrados.findIndex(function(r){ return parseInt(r.id) === rolId; });
                if (idxF !== -1) {
                    _rolesFiltrados[idxF].calcular      = d.calcular;
                    _rolesFiltrados[idxF].modificado_por = d.modificado_por;
                }
            }

            // Actualizar el contador de activos
            var activos2 = _rolesCalculo.filter(function(r){ return parseInt(r.calcular)===1; }).length;
            var textoF   = (_rolesFiltrados.length < _rolesCalculo.length)
                ? _rolesFiltrados.length + ' encontrados &nbsp;/&nbsp; ' : '';
            $('#rc-contador').html(
                '<span style="font-weight:700;color:#15803d;">' + activos2 + ' activos</span>' +
                ' &nbsp;/&nbsp; ' + textoF + _rolesCalculo.length + ' roles'
            );

            Swal.fire({
                icon:  activo ? 'success' : 'warning',
                title: activo ? 'Cálculo activado' : 'Cálculo desactivado',
                text:  'Rol "' + d.rol_nombre + '" ' + (activo ? 'ahora recibe comisiones.' : 'ya no recibirá comisiones al cerrar factura.'),
                timer: 2500, showConfirmButton: false, toast: true, position: 'top-end'
            });
        })
        .catch(function() {
            checkbox.checked  = !checkbox.checked;
            checkbox.disabled = false;
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el estado del rol.' });
        });
}
