<div class="logistica-confirmacion">
    <div class="border-0 shadow-sm card">
        <div class="flex-wrap bg-white border-0 card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Confirmación de entregas</h4>
                <p class="mb-0 text-muted">Selecciona el equipo, luego la factura para validar los productos entregados.</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="mr-3 text-right">
                    <span class="d-block text-uppercase small text-muted">Fecha programada</span>
                    <input type="date" class="form-control form-control-sm" id="fechaConfirmacion" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>
        <div class="pt-0 card-body">
            <div class="row">
                <div class="col-lg-4 border-right">
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-uppercase text-muted">Equipos programados</h6>
                        <span class="badge badge-light" id="totalEquipos">0</span>
                    </div>
                    <div id="listaDistribuciones" style="min-height:250px;" class="pr-lg-3"></div>
                </div>
                <div class="col-lg-8">
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-uppercase text-muted">Detalle de confirmación</h6>
                    </div>
                    <div id="contenedorFacturas" class="p-4 border rounded bg-light" style="min-height:320px;">
                        <div class="py-5 text-center text-muted">
                            <i class="mb-3 fas fa-truck-loading fa-2x"></i>
                            <p class="mb-0">Selecciona un equipo para ver sus facturas.</p>
                        </div>
                    </div>
                </div>


            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const rutasConfirmacion = {
                    distribuciones: "<?= route('logistica.confirmacion.distribuciones') ?>",
                    facturas: "<?= url('/logistica/confirmacion/facturas') ?>",
                    marcarTodos: "<?= url('/logistica/confirmacion/marcar-todos') ?>",
                    guardar: "<?= route('logistica.confirmacion.guardar') ?>",
                    incidencias: "<?= url('/logistica/confirmacion/productos') ?>"
                };

                const confirmacionState = {
                    distribucionActual: null,
                    facturaSeleccionada: null,
                    facturas: [],
                    productosPendientes: [],
                    productoIncidencia: null,
                    productoIncidenciaNombre: ''
                };

                $(function () {
                    $('#fechaConfirmacion').on('change', cargarDistribucionesFecha);
                    $('#formIncidencia').on('submit', function (event) {
                        event.preventDefault();
                        registrarIncidencia();
                    });
                    $('#modalIncidencia').on('hidden.bs.modal', function () {
                        confirmacionState.productoIncidencia = null;
                        confirmacionState.productoIncidenciaNombre = '';
                        const form = document.getElementById('formIncidencia');
                        if (form) {
                            form.reset();
                        }
                        $('#listaIncidenciasProducto').html('<p class="mb-0 text-muted">Selecciona un producto para ver sus incidencias.</p>');
                    });
                    $('#modalHoraEntrega').on('hidden.bs.modal', function () {
                        confirmacionState.productosPendientes = [];
                        $('#horaEntregaInput').val('');
                    });
                    $(document).on('click', '.btn-distribucion', function () {
                        const distribucionId = Number($(this).data('distribucion'));
                        if (!distribucionId) {
                            return;
                        }
                        seleccionarDistribucion(distribucionId, this);
                    });
                    $(document).on('click', '.factura-item', function () {
                        const facturaId = Number($(this).data('factura'));
                        if (!facturaId) {
                            return;
                        }
                        seleccionarFactura(facturaId);
                    });
                    $(document).on('click', '.btn-incidencia', function () {
                        if (this.disabled) {
                            return;
                        }
                        abrirIncidencia(this);
                    });
                    $(document).on('click', '.btn-marcar-todos', function () {
                        const facturaId = Number($(this).data('factura'));
                        if (facturaId) {
                            marcarTodosEntregados(facturaId);
                        }
                    });
                    $('#contenedorFacturas').on('click', '#btnConfirmarEntrega', guardarConfirmacion);
                    $('#modalHoraEntrega').on('click', '#btnConfirmarHora', confirmarHoraEntrega);
                    cargarDistribucionesFecha();
                });

                function obtenerHoraActual() {
                    const fecha = new Date();
                    const horas = String(fecha.getHours()).padStart(2, '0');
                    const minutos = String(fecha.getMinutes()).padStart(2, '0');
                    return `${horas}:${minutos}`;
                }

                function plantillaVacia(mensaje) {
                    return `<div class="py-5 text-center text-muted">
                        <i class="mb-3 fas fa-clipboard-list fa-2x"></i>
                        <p class="mb-0">${mensaje}</p>
                    </div>`;
                }

                function skeleton(texto) {
                    return `<div class="py-5 text-center text-muted">
                        <div class="mr-2 spinner-border spinner-border-sm"></div>${texto}
                    </div>`;
                }

                function cargarDistribucionesFecha() {
                    const fecha = $('#fechaConfirmacion').val();
                    $('#listaDistribuciones').html(skeleton('Cargando equipos...'));
                    $('#contenedorFacturas').html(plantillaVacia('Selecciona un equipo para ver sus facturas.'));
                    confirmacionState.distribucionActual = null;
                    confirmacionState.facturaSeleccionada = null;
                    confirmacionState.facturas = [];

                    $.get(rutasConfirmacion.distribuciones, { fecha })
                        .done(resp => {
                            const distribuciones = resp.distribuciones || [];
                            $('#totalEquipos').text(distribuciones.length);
                            renderDistribuciones(distribuciones);
                        })
                        .fail(() => {
                            $('#listaDistribuciones').html('<div class="mb-0 alert alert-danger">No se pudieron cargar los equipos.</div>');
                            $('#totalEquipos').text('0');
                        });
                }

                function renderDistribuciones(distribuciones) {
                    if (!distribuciones.length) {
                        $('#listaDistribuciones').html('<div class="mb-0 alert alert-light">No hay distribuciones para la fecha seleccionada.</div>');
                        return;
                    }

                    let html = '<div class="rounded shadow-sm list-group list-group-flush">';
                    distribuciones.forEach(d => {
                        const progreso = d.total_facturas ? Math.round((d.facturas_entregadas / d.total_facturas) * 100) : 0;
                        html += `<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-distribucion ${confirmacionState.distribucionActual === d.id ? 'active' : ''}" data-distribucion="${d.id}">
                            <div>
                                <div class="mb-0 font-weight-bold">${d.nombre_equipo}</div>
                                <small class="text-muted">${d.facturas_entregadas}/${d.total_facturas} facturas</small>
                            </div>
                            <div class="text-right" style="min-width:80px;">
                                <span class="badge badge-${progreso === 100 ? 'success' : 'secondary'}">${progreso}%</span>
                            </div>
                        </button>`;
                    });
                    html += '</div>';
                    $('#listaDistribuciones').html(html);
                }

                function seleccionarDistribucion(distribucionId, element) {
                    const facturaAnterior = confirmacionState.facturaSeleccionada;
                    confirmacionState.distribucionActual = distribucionId;
                    $('.btn-distribucion').removeClass('active');
                    $(element).addClass('active');
                    cargarConfirmacion(distribucionId, facturaAnterior);
                }

                function cargarConfirmacion(distribucionId, facturaAnterior = null) {
                    $('#contenedorFacturas').html(skeleton('Cargando facturas...'));
                    $.get(`${rutasConfirmacion.facturas}/${distribucionId}`)
                        .done(resp => {
                            confirmacionState.facturas = resp.facturas || [];
                            const candidata = facturaAnterior ?? confirmacionState.facturaSeleccionada;
                            if (confirmacionState.facturas.some(f => f.distribucion_factura_id === candidata)) {
                                confirmacionState.facturaSeleccionada = candidata;
                            } else if (confirmacionState.facturas.length) {
                                confirmacionState.facturaSeleccionada = confirmacionState.facturas[0].distribucion_factura_id;
                            } else {
                                confirmacionState.facturaSeleccionada = null;
                            }
                            renderFacturasPanel();
                        })
                        .fail(() => {
                            $('#contenedorFacturas').html('<div class="mb-0 alert alert-danger">No se pudieron cargar las facturas.</div>');
                        });
                }

                function renderFacturasPanel() {
                    if (!confirmacionState.facturas.length) {
                        $('#contenedorFacturas').html(plantillaVacia('El equipo seleccionado no tiene facturas pendientes.'));
                        return;
                    }

                    const layout = `
                        <div class="row">
                            <div class="mb-3 col-lg-5 mb-lg-0">
                                <div class="p-3 bg-white border rounded h-100">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <span class="text-uppercase small text-muted">Facturas asignadas</span>
                                        <span class="badge badge-light">${confirmacionState.facturas.length}</span>
                                    </div>
                                    <div id="listaFacturas" class="list-group list-group-flush"></div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="p-3 bg-white border rounded h-100">
                                    <div id="detalleFactura"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="button" class="btn btn-primary" id="btnConfirmarEntrega">
                                <i class="mr-1 fas fa-save"></i>Confirmar entrega
                            </button>
                        </div>`;
                    $('#contenedorFacturas').html(layout);
                    renderFacturasList();
                    renderDetalleFactura(confirmacionState.facturaSeleccionada);
                }

                function renderFacturasList() {
                    if (!confirmacionState.facturas.length) {
                        $('#listaFacturas').html('<p class="mb-0 text-muted">Sin facturas.</p>');
                        return;
                    }

                    let html = '';
                    confirmacionState.facturas.forEach(f => {
                        const isActive = confirmacionState.facturaSeleccionada === f.distribucion_factura_id;
                        const progreso = calcularProgresoFactura(f);
                        const incidencias = (f.productos || []).filter(p => Number(p.tiene_incidencia) === 1).length;
                        html += `<button type="button" class="list-group-item list-group-item-action factura-item ${isActive ? 'active' : ''}" data-factura="${f.distribucion_factura_id}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="font-weight-bold">Factura #${f.numero_factura}</div>
                                    <small class="d-block text-muted">${f.cliente}</small>
                                    ${incidencias ? `<small class="text-danger">${incidencias} incidencia${incidencias > 1 ? 's' : ''}</small>` : ''}
                                </div>
                                <div class="text-right">
                                    ${badgeEstado(f)}
                                    <div class="small text-muted">${progreso}%</div>
                                </div>
                            </div>
                        </button>`;
                    });
                    $('#listaFacturas').html(html);
                }

                function seleccionarFactura(distribucionFacturaId) {
                    confirmacionState.facturaSeleccionada = distribucionFacturaId;
                    $('.factura-item').removeClass('active');
                    $(`.factura-item[data-factura='${distribucionFacturaId}']`).addClass('active');
                    renderDetalleFactura(distribucionFacturaId);
                }

                function renderDetalleFactura(distribucionFacturaId) {
                    const contenedor = $('#detalleFactura');
                    if (!distribucionFacturaId) {
                        contenedor.html('<p class="mb-0 text-muted">Selecciona una factura para revisar sus productos.</p>');
                        return;
                    }

                    const factura = confirmacionState.facturas.find(f => f.distribucion_factura_id === distribucionFacturaId);
                    if (!factura) {
                        contenedor.html('<p class="mb-0 text-muted">No se encontró la factura seleccionada.</p>');
                        return;
                    }

                    const productos = factura.productos || [];
                    const articulosEntregados = productos.filter(p => Number(p.entregado) === 1).length;
                    const progreso = productos.length ? Math.round((articulosEntregados / productos.length) * 100) : 0;
                    const estado = (factura.estado_entrega || '').toLowerCase();
                    const facturaBloqueada = estado === 'entregado';

                    let filas = '';
                    productos.forEach((p, index) => {
                        const tieneIncidencia = Number(p.tiene_incidencia) === 1;
                        const checkboxDeshabilitado = facturaBloqueada || tieneIncidencia;
                        const incidenciasRegistradas = Number(p.incidencias_registradas) || 0;
                        const nombreSafe = encodeURIComponent(p.nombre_producto || '');
                        filas += `<tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="font-weight-bold">${p.nombre_producto}</div>
                                <small class="text-muted">ID #${p.producto_id}</small>
                                ${incidenciasRegistradas ? `<small class="text-danger d-block">${incidenciasRegistradas} incidencia${incidenciasRegistradas > 1 ? 's' : ''}</small>` : ''}
                            </td>
                            <td class="text-center">${p.cantidad_facturada}</td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input position-static chk-producto" data-producto="${p.id}" data-factura="${factura.distribucion_factura_id}" data-cantidad="${p.cantidad_facturada}" ${p.entregado ? 'checked' : ''} ${checkboxDeshabilitado ? 'disabled' : ''}>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    ${tieneIncidencia ? '<span class="mb-1 badge badge-danger">Incidencia</span>' : ''}
                                    <button type="button" class="btn btn-outline-warning btn-sm btn-incidencia" data-producto="${p.id}" data-nombre="${nombreSafe}" ${facturaBloqueada ? 'disabled' : ''}>
                                        <i class="mr-1 fas fa-exclamation-triangle"></i>${tieneIncidencia ? 'Gestionar' : 'Reportar'}
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });

                    const header = `
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Factura #${factura.numero_factura}</h5>
                                <small class="text-muted d-block">${factura.cliente} ${factura.telefono_empresa ? '· ' + factura.telefono_empresa : ''}</small>
                                <small class="text-muted">${factura.direccion || 'Sin dirección registrada'}</small>
                            </div>
                            <div class="text-right">
                                <button type="button" class="mb-2 btn btn-outline-success btn-sm btn-marcar-todos" data-factura="${factura.distribucion_factura_id}" ${facturaBloqueada ? 'disabled' : ''}>
                                    <i class="mr-1 fas fa-check-double"></i>Marcar todos
                                </button>
                                <div class="small text-muted">${articulosEntregados}/${productos.length || 0} productos · ${progreso}%</div>
                            </div>
                        </div>
                        ${facturaBloqueada ? '<div class="py-2 alert alert-info"><i class="mr-2 fas fa-lock"></i>Esta factura ya fue confirmada. Solo puedes consultar su historial.</div>' : ''}`;

                    const tabla = productos.length ? `${header}
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Entregado</th>
                                            <th class="text-center">Incidencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>${filas}</tbody>
                                </table>
                            </div>`
                        : '<p class="mb-0 text-muted">La factura no tiene productos asociados.</p>';

                    contenedor.html(tabla);
                }

                function calcularProgresoFactura(factura) {
                    const productos = factura.productos || [];
                    if (!productos.length) {
                        return 0;
                    }
                    const entregados = productos.filter(p => Number(p.entregado) === 1).length;
                    return Math.round((entregados / productos.length) * 100);
                }

                function badgeEstado(factura) {
                    const estado = (factura.estado_entrega || 'sin_entrega').toLowerCase();
                    const mapa = {
                        entregado: 'success',
                        parcial: 'warning',
                        sin_entrega: 'secondary'
                    };
                    const clase = mapa[estado] || 'secondary';
                    return `<span class="badge badge-${clase} text-uppercase">${estado.replace('_', ' ')}</span>`;
                }

                function marcarTodosEntregados(distribucionFacturaId) {
                    $.post(`${rutasConfirmacion.marcarTodos}/${distribucionFacturaId}`, { _token: csrfToken })
                        .done(resp => {
                            Swal.fire(resp.title, resp.text, resp.icon);
                            if (confirmacionState.distribucionActual) {
                                cargarConfirmacion(confirmacionState.distribucionActual, confirmacionState.facturaSeleccionada);
                            }
                        })
                        .fail(xhr => {
                            const r = xhr.responseJSON || {};
                            Swal.fire(r.title || 'Error', r.text || 'No se pudieron actualizar los productos.', r.icon || 'error');
                        });
                }

                function guardarConfirmacion() {
                    if (!confirmacionState.distribucionActual) {
                        Swal.fire('Selecciona un equipo', 'Primero debes elegir la ruta a confirmar.', 'info');
                        return;
                    }

                    const productos = recolectarProductosSeleccionados();
                    if (!productos.length) {
                        Swal.fire('Sin cambios', 'No hay productos habilitados para confirmar.', 'info');
                        return;
                    }

                    confirmacionState.productosPendientes = productos;
                    $('#horaEntregaInput').val(obtenerHoraActual());
                    $('#modalHoraEntrega').modal('show');
                }

                function recolectarProductosSeleccionados() {
                    const productos = [];
                    $('.chk-producto').each(function () {
                        if (this.disabled) {
                            return;
                        }
                        const id = $(this).data('producto');
                        if (!id) {
                            return;
                        }
                        productos.push({
                            id: id,
                            entregado: $(this).is(':checked') ? 1 : 0,
                            cantidad_entregada: $(this).is(':checked') ? $(this).data('cantidad') : 0
                        });
                    });
                    return productos;
                }

                function confirmarHoraEntrega() {
                    const hora = $('#horaEntregaInput').val();
                    if (!hora) {
                        Swal.fire('Hora requerida', 'Debes indicar la hora de entrega.', 'warning');
                        return;
                    }
                    $('#modalHoraEntrega').modal('hide');
                    enviarConfirmacion(hora);
                }

                function enviarConfirmacion(horaEntrega) {
                    const productos = confirmacionState.productosPendientes.slice();
                    if (!productos.length) {
                        return;
                    }
                    const boton = $('#btnConfirmarEntrega');
                    boton.prop('disabled', true).html('<span class="mr-2 spinner-border spinner-border-sm"></span>Guardando...');

                    $.ajax({
                        url: rutasConfirmacion.guardar,
                        type: 'POST',
                        data: {
                            productos,
                            hora_entrega: horaEntrega,
                            _token: csrfToken
                        }
                    })
                        .done(resp => {
                            Swal.fire(resp.title, resp.text, resp.icon);
                            if (confirmacionState.distribucionActual) {
                                cargarConfirmacion(confirmacionState.distribucionActual, confirmacionState.facturaSeleccionada);
                            }
                        })
                        .fail(xhr => {
                            const r = xhr.responseJSON || {};
                            Swal.fire(r.title || 'Error', r.text || 'No se pudo guardar la confirmación.', r.icon || 'error');
                        })
                        .always(() => {
                            confirmacionState.productosPendientes = [];
                            boton.prop('disabled', false).html('<i class="mr-1 fas fa-save"></i>Confirmar entrega');
                        });
                }

                function abrirIncidencia(button) {
                    const productoId = Number($(button).data('producto'));
                    const nombreCodificado = $(button).data('nombre') || '';
                    confirmacionState.productoIncidencia = productoId;
                    confirmacionState.productoIncidenciaNombre = decodeURIComponent(nombreCodificado);
                    $('#productoIncidenciaId').val(productoId);
                    $('#tituloProductoIncidencia').text(confirmacionState.productoIncidenciaNombre || `Producto #${productoId}`);
                    $('#modalIncidencia').modal('show');
                    cargarIncidenciasProducto(productoId);
                }

                function cargarIncidenciasProducto(productoId) {
                    $('#listaIncidenciasProducto').html(skeleton('Cargando incidencias...'));
                    $.get(`${rutasConfirmacion.incidencias}/${productoId}/incidencias`)
                        .done(resp => {
                            renderListaIncidencias(resp.incidencias || []);
                        })
                        .fail(() => {
                            $('#listaIncidenciasProducto').html('<div class="mb-0 alert alert-danger">No se pudieron cargar las incidencias.</div>');
                        });
                }

                function renderListaIncidencias(incidencias) {
                    if (!incidencias.length) {
                        $('#listaIncidenciasProducto').html('<p class="mb-0 text-muted">No hay incidencias registradas para este producto.</p>');
                        return;
                    }

                    let filas = '';
                    incidencias.forEach((inc, index) => {
                        filas += `<tr>
                            <td>${index + 1}</td>
                            <td>${inc.tipo}</td>
                            <td>${inc.descripcion}</td>
                            <td>${formatearFecha(inc.created_at)}</td>
                        </tr>`;
                    });

                    const tabla = `<div class="table-responsive">
                        <table class="table mb-0 table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Registrado</th>
                                </tr>
                            </thead>
                            <tbody>${filas}</tbody>
                        </table>
                    </div>`;
                    $('#listaIncidenciasProducto').html(tabla);
                }

                function registrarIncidencia() {
                    const productoId = confirmacionState.productoIncidencia;
                    if (!productoId) {
                        Swal.fire('Selecciona un producto', 'Abre el modal desde la tabla antes de registrar.', 'info');
                        return;
                    }

                    const tipo = $('#tipoIncidencia').val();
                    const descripcion = $('#descripcionIncidencia').val().trim();
                    if (!descripcion.length) {
                        Swal.fire('Descripción requerida', 'Escribe los detalles de la incidencia.', 'warning');
                        return;
                    }

                    const boton = $('#btnIncidenciaGuardar');
                    boton.prop('disabled', true).html('<span class="mr-1 spinner-border spinner-border-sm"></span>Guardando');

                    $.ajax({
                        url: `${rutasConfirmacion.incidencias}/${productoId}/incidencias`,
                        type: 'POST',
                        data: {
                            tipo,
                            descripcion,
                            _token: csrfToken
                        }
                    })
                        .done(resp => {
                            $('#descripcionIncidencia').val('');
                            renderListaIncidencias(resp.incidencias || []);
                            actualizarProductoEnState(productoId, resp.incidencias ? resp.incidencias.length : 1);
                            renderDetalleFactura(confirmacionState.facturaSeleccionada);
                            Swal.fire(resp.title || 'Incidencia registrada', resp.text || 'La incidencia se guardó correctamente.', resp.icon || 'success');
                        })
                        .fail(xhr => {
                            const r = xhr.responseJSON || {};
                            Swal.fire(r.title || 'Error', r.text || 'No se pudo registrar la incidencia.', r.icon || 'error');
                        })
                        .always(() => {
                            boton.prop('disabled', false).html('<i class="mr-1 fas fa-plus-circle"></i>Agregar incidencia');
                        });
                }

                function actualizarProductoEnState(productoId, totalIncidencias) {
                    confirmacionState.facturas.forEach(factura => {
                        (factura.productos || []).forEach(producto => {
                            if (Number(producto.id) === Number(productoId)) {
                                producto.tiene_incidencia = 1;
                                producto.incidencias_registradas = totalIncidencias;
                            }
                        });
                    });
                }

                function formatearFecha(valor) {
                    if (!valor) {
                        return '--';
                    }
                    const fecha = new Date(valor);
                    if (Number.isNaN(fecha.getTime())) {
                        return valor;
                    }
                    return fecha.toLocaleString('es-HN', { hour12: true });
                }
            </script>
