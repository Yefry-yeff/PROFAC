<div class="logistica-confirmacion">
    <style>
        /* Estilos personalizados para scroll en listas */
        #listaFacturas::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }

        #listaFacturas::-webkit-scrollbar-track,
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #listaFacturas::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        #listaFacturas::-webkit-scrollbar-thumb:hover,
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Animación suave para filtros */
        .factura-item, #tablaProductos tbody tr {
            transition: opacity 0.2s ease-in-out;
        }
    </style>
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
                    <div id="listaDistribuciones" class="pr-lg-3" style="min-height:250px;"></div>
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
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalIncidencia" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="mb-0 modal-title">Incidencias del producto</h5>
                        <small class="text-muted" id="tituloProductoIncidencia">Selecciona un producto de la tabla.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        Cada incidencia registrada bloquea el producto hasta que logística la gestione.
                    </div>
                    <form id="formIncidencia" class="mb-3">
                        <input type="hidden" id="productoIncidenciaId">
                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label class="small text-muted">Tipo de incidencia</label>
                                <select class="form-control" id="tipoIncidencia">
                                    <option value="producto_danado">Producto dañado</option>
                                    <option value="cantidad_incorrecta">Cantidad incorrecta</option>
                                    <option value="cliente_rechazo">Cliente rechazó</option>
                                    <option value="direccion_incorrecta">Dirección incorrecta</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="form-group col-md-7">
                                <label class="small text-muted">Descripción</label>
                                <textarea class="form-control" id="descripcionIncidencia" rows="4" placeholder="Describe lo sucedido..." required minlength="5"></textarea>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <div class="mb-3">
                        <h6 class="mb-2 text-uppercase small text-muted">Evidencia fotográfica</h6>
                        <div class="mb-2 custom-file">
                            <input type="file" class="custom-file-input" id="inputEvidencias" accept="image/*" multiple>
                            <label class="custom-file-label" for="inputEvidencias">Seleccionar imágenes...</label>
                        </div>

                        <div id="previewEvidencias" class="flex-wrap d-flex"></div>
                    </div>
                    <div class="text-right">
                        <button type="button" class="mr-2 btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnIncidenciaGuardar" form="formIncidencia">
                            <i class="mr-1 fas fa-plus-circle"></i>Agregar incidencia
                        </button>
                    </div>
                    <hr>
                    <h6 class="text-uppercase small text-muted">Incidencias registradas</h6>
                    <div id="listaIncidenciasProducto">
                        <p class="mb-0 text-muted">No hay incidencias para este producto.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHoraEntrega" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="mb-0 modal-title">Hora de entrega</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Confirma la hora a la que se completó la entrega. Se registrará en el historial.</p>
                    <input type="time" class="form-control" id="horaEntregaInput" step="60">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarHora">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImagenesIncidencia" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="mb-0 modal-title">
                        <i class="fas fa-images"></i> Evidencias Fotográficas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="bodyImagenesIncidencia">
                    <div class="py-4 text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Cargando imágenes...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const rutasConfirmacion = {
                    distribuciones: "<?= route('logistica.confirmacion.distribuciones') ?>",
                    facturas: "<?= url('/logistica/confirmacion/facturas') ?>",
                    marcarTodos: "<?= url('/logistica/confirmacion/marcar-todos') ?>",
                    guardar: "<?= route('logistica.confirmacion.guardar') ?>",
                    incidencias: "<?= url('/logistica/confirmacion/productos') ?>",
                    evidencia: "<?= url('/logistica/confirmacion/evidencia') ?>",
                    evidencias: "<?= url('/logistica/confirmacion/evidencias') ?>",
                    evidenciasIncidencia: "<?= url('/logistica/confirmacion/incidencias') ?>"
                };

                const confirmacionState = {
                    distribucionActual: null,
                    facturaSeleccionada: null,
                    facturas: [],
                    productosPendientes: [],
                    productoIncidencia: null,
                    productoIncidenciaNombre: '',
                    incidenciasPendientes: [], // Incidencias que se guardarán al confirmar
                    incidenciaEditando: null // ID temporal de incidencia en edición
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
                    // Event handler para actualizar el estado cuando se marca/desmarca un checkbox
                    $(document).on('change', '.chk-producto', function () {
                        const productoId = $(this).data('producto');
                        const checked = $(this).is(':checked');
                        // Actualizar el estado en memoria
                        confirmacionState.facturas.forEach(factura => {
                            (factura.productos || []).forEach(producto => {
                                if (Number(producto.id) === Number(productoId)) {
                                    producto.entregado = checked ? 1 : 0;
                                }
                            });
                        });
                        // Actualizar contador
                        actualizarContadorProductos();
                    });
                    $('#contenedorFacturas').on('click', '#btnConfirmarEntrega', guardarConfirmacion);
                    $('#contenedorFacturas').on('input', '#filtroFacturas', function() {
                        renderFacturasList($(this).val());
                    });
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

                function actualizarDistribuciones() {
                    // Recargar solo la lista de distribuciones sin resetear la selección actual
                    const fecha = $('#fechaConfirmacion').val();
                    $.get(rutasConfirmacion.distribuciones, { fecha })
                        .done(resp => {
                            const distribuciones = resp.distribuciones || [];
                            $('#totalEquipos').text(distribuciones.length);
                            renderDistribuciones(distribuciones);
                            // Mantener activo el equipo seleccionado
                            if (confirmacionState.distribucionActual) {
                                $(`.btn-distribucion[data-distribucion='${confirmacionState.distribucionActual}']`).addClass('active');
                            }
                        })
                        .fail(() => {
                            console.error('No se pudieron actualizar las distribuciones');
                        });
                }

                function seleccionarDistribucion(distribucionId, element) {
                    const facturaAnterior = confirmacionState.facturaSeleccionada;
                    confirmacionState.distribucionActual = distribucionId;
                    $('.btn-distribucion').removeClass('active');
                    $(element).addClass('active');
                    cargarConfirmacion(distribucionId, facturaAnterior);
                }

                function cargarConfirmacion(distribucionId, facturaAnterior = null) {
                    // Limpiar incidencias pendientes al cambiar de distribuci\u00f3n
                    confirmacionState.incidenciasPendientes = [];
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
                                        <span class="badge badge-light" id="contadorFacturas">${confirmacionState.facturas.length}</span>
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" id="filtroFacturas" class="form-control form-control-sm" placeholder="Buscar por número de factura...">
                                    </div>
                                    <div id="listaFacturas" class="list-group list-group-flush" style="max-height: 450px; overflow-y: auto;"></div>
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

                function renderFacturasList(filtro = '') {
                    if (!confirmacionState.facturas.length) {
                        $('#listaFacturas').html('<p class="mb-0 text-muted">Sin facturas.</p>');
                        $('#contadorFacturas').text('0');
                        return;
                    }

                    const filtroLower = filtro.toLowerCase().trim();
                    const facturasFiltradas = filtro ? confirmacionState.facturas.filter(f =>
                        f.numero_factura.toLowerCase().includes(filtroLower)
                    ) : confirmacionState.facturas;

                    if (!facturasFiltradas.length) {
                        $('#listaFacturas').html('<p class="mb-0 text-muted">No se encontraron facturas con ese número.</p>');
                        $('#contadorFacturas').text('0');
                        return;
                    }

                    let html = '';
                    facturasFiltradas.forEach(f => {
                        const isActive = confirmacionState.facturaSeleccionada === f.distribucion_factura_id;
                        const progreso = calcularProgresoFactura(f);
                        const incidencias = (f.productos || []).filter(p => Number(p.tiene_incidencia) === 1).length;
                        html += `<button type="button" class="list-group-item list-group-item-action factura-item ${isActive ? 'active' : ''}" data-factura="${f.distribucion_factura_id}" data-numero="${f.numero_factura}">
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
                    $('#contadorFacturas').text(facturasFiltradas.length);
                }

                function seleccionarFactura(distribucionFacturaId) {
                    confirmacionState.facturaSeleccionada = distribucionFacturaId;
                    $('.factura-item').removeClass('active');
                    $(`.factura-item[data-factura='${distribucionFacturaId}']`).addClass('active');
                    renderDetalleFactura(distribucionFacturaId);
                }

                function actualizarContadorProductos() {
                    const facturaId = confirmacionState.facturaSeleccionada;
                    if (!facturaId) return;

                    const factura = confirmacionState.facturas.find(f => f.distribucion_factura_id === facturaId);
                    if (!factura) return;

                    const productos = factura.productos || [];
                    const articulosEntregados = productos.filter(p => Number(p.entregado) === 1).length;
                    const progreso = productos.length ? Math.round((articulosEntregados / productos.length) * 100) : 0;

                    $('#contadorProductos').text(articulosEntregados);
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

                    // Guardar el estado actual de los checkboxes antes de re-renderizar
                    const estadoCheckboxes = {};
                    $('.chk-producto').each(function() {
                        const productoId = $(this).data('producto');
                        estadoCheckboxes[productoId] = $(this).is(':checked');
                    });

                    const productos = factura.productos || [];
                    const articulosEntregados = productos.filter(p => Number(p.entregado) === 1).length;
                    const progreso = productos.length ? Math.round((articulosEntregados / productos.length) * 100) : 0;
                    const estado = (factura.estado_entrega || '').toLowerCase();
                    // Bloquear si está entregada, parcial, o si ya se ha confirmado al menos una vez
                    const facturaBloqueada = estado === 'entregado' || estado === 'parcial' || (Number(factura.confirmada) === 1);

                    let filas = '';
                    productos.forEach((p, index) => {
                        const tieneIncidencia = Number(p.tiene_incidencia) === 1;
                        const checkboxDeshabilitado = facturaBloqueada || tieneIncidencia;
                        const incidenciasRegistradas = Number(p.incidencias_registradas) || 0;
                        const nombreSafe = encodeURIComponent(p.nombre_producto || '');
                        // Si tiene incidencia, siempre debe estar desmarcado
                        // Si no tiene incidencia, usar el estado guardado o p.entregado
                        const estaChecked = tieneIncidencia ? false : (estadoCheckboxes.hasOwnProperty(p.id) ? estadoCheckboxes[p.id] : p.entregado);
                        // Actualizar el estado en memoria también
                        if (tieneIncidencia) {
                            p.entregado = 0;
                        }
                        filas += `<tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="font-weight-bold">${p.nombre_producto}</div>
                                <small class="text-muted">ID #${p.producto_id}</small>
                                ${incidenciasRegistradas ? `<small class="text-danger d-block">${incidenciasRegistradas} incidencia${incidenciasRegistradas > 1 ? 's' : ''}</small>` : ''}
                            </td>
                            <td class="text-center">${p.cantidad_facturada}</td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input position-static chk-producto" data-producto="${p.id}" data-factura="${factura.distribucion_factura_id}" data-cantidad="${p.cantidad_facturada}" ${estaChecked ? 'checked' : ''} ${checkboxDeshabilitado ? 'disabled' : ''}>
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
                                <div class="small text-muted"><span id="contadorProductos">${articulosEntregados}</span>/${productos.length || 0} productos · ${progreso}%</div>
                            </div>
                        </div>`;

                    // Mensaje dinámico según el estado
                    let mensajeBloqueo = '';
                    if (facturaBloqueada) {
                        if (estado === 'entregado') {
                            mensajeBloqueo = '<div class="py-2 alert alert-success"><i class="mr-2 fas fa-lock"></i>Esta factura fue confirmada como <strong>entregada completamente</strong> y está bloqueada.</div>';
                        } else if (estado === 'parcial') {
                            mensajeBloqueo = '<div class="py-2 alert alert-warning"><i class="mr-2 fas fa-lock"></i>Esta factura fue confirmada con <strong>entrega parcial</strong> y está bloqueada. Algunos productos tienen incidencias o no fueron entregados.</div>';
                        } else {
                            mensajeBloqueo = '<div class="py-2 alert alert-info"><i class="mr-2 fas fa-lock"></i>Esta factura ya fue confirmada y está bloqueada.</div>';
                        }
                    }

                    const filtroProductos = `${mensajeBloqueo}
                        <div class="mb-2">
                            <input type="text" id="filtroProductos" class="form-control form-control-sm" placeholder="Buscar producto por nombre...">
                        </div>`;

                    const tabla = productos.length ? `${header}${filtroProductos}
                            <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                <table class="table table-sm table-hover" id="tablaProductos">
                                    <thead class="thead-light" style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
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

                    // Configurar filtro de productos
                    $('#filtroProductos').off('input').on('input', function() {
                        filtrarProductos($(this).val());
                    });
                }

                function filtrarProductos(filtro) {
                    const filtroLower = filtro.toLowerCase().trim();
                    let contadorVisibles = 0;

                    $('#tablaProductos tbody tr:not(.no-results)').each(function() {
                        const nombreProducto = $(this).find('td:eq(1) .font-weight-bold').text().toLowerCase();

                        if (!filtro || nombreProducto.includes(filtroLower)) {
                            $(this).fadeIn(200);
                            contadorVisibles++;
                        } else {
                            $(this).fadeOut(200);
                        }
                    });

                    // Mostrar mensaje si no hay resultados
                    if (contadorVisibles === 0 && filtro) {
                        if ($('#tablaProductos tbody .no-results').length === 0) {
                            $('#tablaProductos tbody').append('<tr class="no-results"><td colspan="5" class="py-3 text-center text-muted"><i class="mr-2 fas fa-search"></i>No se encontraron productos con ese nombre</td></tr>');
                        }
                        $('#tablaProductos tbody .no-results').show();
                    } else {
                        $('#tablaProductos tbody .no-results').remove();
                    }
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
                    // Verificar si la factura está bloqueada antes de marcar todos
                    const factura = confirmacionState.facturas.find(f => f.distribucion_factura_id === distribucionFacturaId);
                    if (factura) {
                        const estado = (factura.estado_entrega || '').toLowerCase();
                        const yaConfirmada = Number(factura.confirmada) === 1;
                        if (estado === 'entregado' || estado === 'parcial' || yaConfirmada) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Factura ya confirmada',
                                text: 'Esta factura ya fue confirmada y no se puede modificar.',
                                confirmButtonText: 'Entendido'
                            });
                            return;
                        }
                    }

                    $.post(`${rutasConfirmacion.marcarTodos}/${distribucionFacturaId}`, { _token: csrfToken })
                        .done(resp => {
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

                    // Obtener factura actual y validar
                    const facturaActual = confirmacionState.facturas.find(f => f.distribucion_factura_id === confirmacionState.facturaSeleccionada);
                    if (!facturaActual) {
                        Swal.fire('Error', 'No se encontró la factura seleccionada.', 'error');
                        return;
                    }
                    
                    // Verificar si la factura ya está confirmada
                    const estado = (facturaActual.estado_entrega || '').toLowerCase();
                    const yaConfirmada = Number(facturaActual.confirmada) === 1;
                    if (estado === 'entregado' || estado === 'parcial' || yaConfirmada) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Factura ya confirmada',
                            text: 'Esta factura ya fue confirmada y no se puede modificar. Solo puedes consultar su historial.',
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }
                    
                    const todosLosProductos = facturaActual.productos || [];
                    
                    // Validar que TODOS los productos estén marcados O tengan incidencia
                    const productosSinGestionar = [];
                    todosLosProductos.forEach(producto => {
                        const checkbox = $(`.chk-producto[data-producto="${producto.id}"]`);
                        const estaMarcado = checkbox.is(':checked');
                        const tieneIncidenciaGuardada = Number(producto.tiene_incidencia) === 1;
                        const tieneIncidenciaPendiente = confirmacionState.incidenciasPendientes.some(i => i.producto_id === producto.id);
                        
                        // Si no está marcado Y no tiene incidencia guardada Y no tiene incidencia pendiente
                        if (!estaMarcado && !tieneIncidenciaGuardada && !tieneIncidenciaPendiente) {
                            productosSinGestionar.push(producto.nombre_producto);
                        }
                    });
                    
                    // Si hay productos sin gestionar, no permitir confirmar
                    if (productosSinGestionar.length > 0) {
                        const listaProductos = productosSinGestionar.map(p => `• ${p}`).join('<br>');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Productos pendientes',
                            html: `Los siguientes productos no están marcados como entregados ni tienen incidencias registradas:<br><br>${listaProductos}<br><br>Debes marcar cada producto como entregado o registrar una incidencia antes de confirmar.`,
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }
                    
                    // Recolectar productos marcados
                    const incidenciasPendientes = confirmacionState.incidenciasPendientes.length;
                    const productos = recolectarProductosSeleccionados(incidenciasPendientes > 0);
                    
                    // Si hay incidencias pero no productos habilitados, asegurarse de enviar todos los productos
                    if (!productos.length && incidenciasPendientes > 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Solo incidencias',
                            text: 'Se confirmarán únicamente las incidencias registradas.',
                            confirmButtonText: 'Continuar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                confirmacionState.productosPendientes = [];
                                $('#horaEntregaInput').val(obtenerHoraActual());
                                $('#modalHoraEntrega').modal('show');
                            }
                        });
                        return;
                    }

                    confirmacionState.productosPendientes = productos;
                    $('#horaEntregaInput').val(obtenerHoraActual());
                    $('#modalHoraEntrega').modal('show');
                }

                function recolectarProductosSeleccionados(incluirDeshabilitados = false) {
                    const productos = [];
                    $('.chk-producto').each(function () {
                        // Si incluirDeshabilitados es true, procesar todos los productos
                        // Si es false, solo procesar productos habilitados (comportamiento original)
                        if (!incluirDeshabilitados && this.disabled) {
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

                async function enviarConfirmacion(horaEntrega) {
                    const productos = confirmacionState.productosPendientes.slice();
                    const incidenciasPendientes = confirmacionState.incidenciasPendientes.length;
                    
                    // Permitir envío si hay productos O incidencias
                    if (!productos.length && !incidenciasPendientes) {
                        return;
                    }
                    
                    const boton = $('#btnConfirmarEntrega');
                    boton.prop('disabled', true).html('<span class="mr-2 spinner-border spinner-border-sm"></span>Guardando...');

                    // Preparar incidencias con evidencias en base64
                    const incidenciasPrepararadas = [];
                    for (const inc of confirmacionState.incidenciasPendientes) {
                        const incidenciaData = {
                            producto_id: inc.producto_id,
                            tipo: inc.tipo,
                            descripcion: inc.descripcion,
                            evidencias: []
                        };

                        // Convertir evidencias a base64
                        if (inc.evidencias && inc.evidencias.length > 0) {
                            for (const file of inc.evidencias) {
                                try {
                                    const base64 = await convertirArchivoABase64(file);
                                    incidenciaData.evidencias.push(base64);
                                } catch (error) {
                                    console.error('Error al convertir evidencia:', error);
                                }
                            }
                        }

                        incidenciasPrepararadas.push(incidenciaData);
                    }

                    $.ajax({
                        url: rutasConfirmacion.guardar,
                        type: 'POST',
                        data: {
                            productos,
                            hora_entrega: horaEntrega,
                            incidencias: incidenciasPrepararadas,
                            _token: csrfToken
                        }
                    })
                        .done(resp => {
                            Swal.fire(resp.title, resp.text, resp.icon);
                            // Limpiar incidencias pendientes
                            confirmacionState.incidenciasPendientes = [];
                            if (confirmacionState.distribucionActual) {
                                // Recargar la lista de distribuciones para actualizar los porcentajes
                                actualizarDistribuciones();
                                // Recargar las facturas del equipo actual
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

                function convertirArchivoABase64(file) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = reject;
                        reader.readAsDataURL(file);
                    });
                }

                let evidenciasPendientes = [];

                function abrirIncidencia(button) {
                    // Verificar si la factura actual está bloqueada
                    const facturaActual = confirmacionState.facturas.find(f => f.distribucion_factura_id === confirmacionState.facturaSeleccionada);
                    let facturaBloqueada = false;

                    if (facturaActual) {
                        const estado = (facturaActual.estado_entrega || '').toLowerCase();
                        const yaConfirmada = Number(facturaActual.confirmada) === 1;
                        facturaBloqueada = (estado === 'entregado' || estado === 'parcial' || yaConfirmada);
                    }

                    const productoId = Number($(button).data('producto'));
                    const nombreCodificado = $(button).data('nombre') || '';
                    confirmacionState.productoIncidencia = productoId;
                    confirmacionState.productoIncidenciaNombre = decodeURIComponent(nombreCodificado);
                    $('#productoIncidenciaId').val(productoId);
                    $('#tituloProductoIncidencia').text(confirmacionState.productoIncidenciaNombre || `Producto #${productoId}`);

                    // Deshabilitar el formulario si la factura está bloqueada
                    if (facturaBloqueada) {
                        $('#tipoIncidencia').prop('disabled', true);
                        $('#descripcionIncidencia').prop('disabled', true);
                        $('#btnIncidenciaGuardar').prop('disabled', true);
                        $('.alert-warning').html('<i class="mr-2 fas fa-lock"></i>Esta factura ya fue confirmada. Solo puedes consultar las incidencias existentes.').removeClass('alert-warning').addClass('alert-info');
                    } else {
                        $('#tipoIncidencia').prop('disabled', false);
                        $('#descripcionIncidencia').prop('disabled', false);
                        $('#btnIncidenciaGuardar').prop('disabled', false);
                        $('.alert-info').html('Cada incidencia registrada bloquea el producto hasta que logística la gestione.').removeClass('alert-info').addClass('alert-warning');
                    }

                    $('#modalIncidencia').modal('show');
                    evidenciasPendientes = [];
                    // Limpiar previews
                    $('#previewEvidencias').html('');
                    // Resetear botón si estaba en modo edición
                    confirmacionState.incidenciaEditando = null;
                    $('#btnIncidenciaGuardar').html('<i class="mr-1 fas fa-plus-circle"></i>Agregar incidencia');
                    // Cargar incidencias existentes de la BD + las pendientes locales
                    window.cargarIncidenciasProducto(productoId);
                }

                function renderListaIncidencias(incidencias) {
                    if (!incidencias.length) {
                        $('#listaIncidenciasProducto').html('<p class="mb-0 text-muted">No hay incidencias registradas para este producto.</p>');
                        return;
                    }

                    let filas = '';
                    incidencias.forEach((inc, index) => {
                        const imagenCount = inc.evidencias_count || 0;
                        const btnImagenes = imagenCount > 0
                            ? `<button type="button" class="btn btn-sm btn-info" onclick="verImagenesIncidencia(${inc.id})" title="Ver imágenes">
                                <i class="fas fa-images"></i> ${imagenCount}
                               </button>`
                            : '<span class="text-muted"><i class="fas fa-image-slash"></i> Sin imágenes</span>';

                        filas += `<tr>
                            <td>${index + 1}</td>
                            <td>${inc.tipo}</td>
                            <td>${inc.descripcion}</td>
                            <td>${formatearFecha(inc.created_at)}</td>
                            <td class="text-center">${btnImagenes}</td>
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
                                    <th class="text-center" width="120">Imágenes</th>
                                </tr>
                            </thead>
                            <tbody>${filas}</tbody>
                        </table>
                    </div>`;
                    $('#listaIncidenciasProducto').html(tabla);
                }

                // ==================== EVIDENCIAS (FOTOS) ====================
                // Vista previa y subida automática
                $(document).off('change', '#inputEvidencias').on('change', '#inputEvidencias', function() {
                    const files = Array.from(this.files || []);
                    if (!files.length) return;
                    const preview = $('#previewEvidencias');
                    files.forEach(f => {
                        evidenciasPendientes.push(f);
                        const reader = new FileReader();
                        reader.onload = e => {
                            const el = `<div class="mb-2 mr-2 border rounded" style="width:90px;height:90px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f8f9fa;">
                                <img src="${e.target.result}" style="max-width:100%;max-height:100%;"/>
                            </div>`;
                            preview.append(el);
                        };
                        reader.readAsDataURL(f);
                    });
                    // Update label with cumulative count
                    $(this).next('.custom-file-label').text(`${evidenciasPendientes.length} archivo(s) acumulado(s)`);
                });

                function subirEvidenciasPendientes(incidenciaId, callback) {
                    const dfId = confirmacionState.facturaSeleccionada;
                    const descripcion = $('#descripcionIncidencia').val().trim();
                    if (!evidenciasPendientes.length) {
                        callback && callback({ subidas: 0, fallidas: 0 });
                        return;
                    }
                    let subidas = 0, fallidas = 0;
                    const total = evidenciasPendientes.length;
                    const subir = (file) => {
                        const fd = new FormData();
                        fd.append('incidencia_id', incidenciaId);
                        fd.append('archivo', file);
                        if (descripcion) fd.append('descripcion', descripcion);
                        $.ajax({
                            url: rutasConfirmacion.evidencia,
                            type: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                        }).done(() => { subidas++; })
                          .fail(() => { fallidas++; })
                          .always(() => {
                              if (subidas + fallidas === total) {
                                  callback && callback({ subidas, fallidas });
                                  evidenciasPendientes = [];
                                  $('#inputEvidencias').val('');
                                  $('.custom-file-label[for="inputEvidencias"]').text('Seleccionar imágenes...');
                                  $('#previewEvidencias').html('');
                              }
                          });
                    };
                    evidenciasPendientes.forEach(subir);
                }

                function registrarIncidencia() {
                    const productoId = confirmacionState.productoIncidencia;
                    if (!productoId) {
                        Swal.fire('Selecciona un producto', 'Abre el modal desde la tabla antes de registrar.', 'info');
                        return;
                    }

                    // Verificar si la factura está confirmada antes de permitir agregar incidencias
                    const facturaActual = confirmacionState.facturas.find(f => f.distribucion_factura_id === confirmacionState.facturaSeleccionada);
                    if (facturaActual) {
                        const estado = (facturaActual.estado_entrega || '').toLowerCase();
                        const yaConfirmada = Number(facturaActual.confirmada) === 1;
                        if (estado === 'entregado' || estado === 'parcial' || yaConfirmada) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Factura ya confirmada',
                                text: 'No puedes agregar incidencias a una factura que ya fue confirmada.',
                                confirmButtonText: 'Entendido'
                            });
                            return;
                        }
                    }

                    const tipo = $('#tipoIncidencia').val();
                    const descripcion = $('#descripcionIncidencia').val().trim();
                    if (!descripcion.length) {
                        Swal.fire('Descripción requerida', 'Escribe los detalles de la incidencia.', 'warning');
                        return;
                    }

                    // Guardar localmente en lugar de enviar al servidor
                    const incidencia = {
                        id: confirmacionState.incidenciaEditando || Date.now(), // ID temporal
                        producto_id: productoId,
                        producto_nombre: confirmacionState.productoIncidenciaNombre,
                        tipo: tipo,
                        descripcion: descripcion,
                        evidencias: evidenciasPendientes.slice(), // Clonar array de evidencias
                        estado: 'pendiente' // Marca que está pendiente de guardar
                    };

                    if (confirmacionState.incidenciaEditando) {
                        // Actualizar incidencia existente
                        const index = confirmacionState.incidenciasPendientes.findIndex(i => i.id === confirmacionState.incidenciaEditando);
                        if (index !== -1) {
                            confirmacionState.incidenciasPendientes[index] = incidencia;
                            toastr.success('Incidencia actualizada (pendiente de guardar)');
                        }
                        confirmacionState.incidenciaEditando = null;
                    } else {
                        // Agregar nueva incidencia
                        confirmacionState.incidenciasPendientes.push(incidencia);
                        toastr.success('Incidencia agregada (pendiente de guardar)');
                    }

                    // Limpiar formulario
                    $('#descripcionIncidencia').val('');
                    $('#tipoIncidencia').val('producto_danado');
                    evidenciasPendientes = [];
                    $('#previewEvidencias').empty();
                    $('#inputEvidencias').val('');
                    $('.custom-file-label').text('Seleccionar imágenes...');
                    $('#btnIncidenciaGuardar').html('<i class="mr-1 fas fa-plus-circle"></i>Agregar incidencia');

                    // Recargar todas las incidencias (guardadas + pendientes)
                    window.cargarIncidenciasProducto(productoId);
                    
                    // Actualizar estado visual del producto
                    actualizarEstadoProducto(productoId);
                    
                    // Actualizar contador en la tabla
                    actualizarContadorIncidencias(productoId);
                }

                function renderIncidenciasPendientes(productoId) {
                    const incidencias = confirmacionState.incidenciasPendientes.filter(i => i.producto_id === productoId);
                    
                    if (incidencias.length === 0) {
                        $('#listaIncidenciasProducto').html('<p class="mb-0 text-muted">No hay incidencias agregadas para este producto.</p>');
                        return;
                    }

                    let html = '<div class="list-group">';
                    incidencias.forEach((inc, index) => {
                        const tipoLabel = {
                            'producto_danado': 'Producto da\u00f1ado',
                            'cantidad_incorrecta': 'Cantidad incorrecta',
                            'cliente_rechazo': 'Cliente rechaz\u00f3',
                            'direccion_incorrecta': 'Direcci\u00f3n incorrecta',
                            'otro': 'Otro'
                        }[inc.tipo] || inc.tipo;

                        html += `<div class="mb-2 list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="badge badge-warning">${tipoLabel}</span>
                                    <span class="badge badge-info">Pendiente de guardar</span>
                                    <p class="mt-2 mb-1">${inc.descripcion}</p>
                                    ${inc.evidencias.length > 0 ? `<small class="text-muted"><i class="fas fa-images"></i> ${inc.evidencias.length} evidencia(s)</small>` : ''}
                                </div>
                                <div class="ml-2 btn-group-vertical btn-group-sm">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarIncidenciaPendiente(${inc.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarIncidenciaPendiente(${inc.id})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    });
                    html += '</div>';
                    $('#listaIncidenciasProducto').html(html);
                }

                window.editarIncidenciaPendiente = function(incidenciaId) {
                    const incidencia = confirmacionState.incidenciasPendientes.find(i => i.id === incidenciaId);
                    if (!incidencia) return;

                    // Cargar datos en el formulario
                    $('#tipoIncidencia').val(incidencia.tipo);
                    $('#descripcionIncidencia').val(incidencia.descripcion);
                    evidenciasPendientes = incidencia.evidencias.slice();
                    
                    // Marcar como editando
                    confirmacionState.incidenciaEditando = incidenciaId;
                    
                    // Actualizar bot\u00f3n
                    $('#btnIncidenciaGuardar').html('<i class="mr-1 fas fa-save"></i>Actualizar incidencia');
                    
                    // Mostrar preview de evidencias
                    $('#previewEvidencias').empty();
                    evidenciasPendientes.forEach((ev, idx) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = `<div class="m-1 position-relative" style="width:80px;height:80px;">
                                <img src="${e.target.result}" class="rounded" style="width:100%;height:100%;object-fit:cover;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute" 
                                        style="top:2px;right:2px;padding:2px 6px;" 
                                        onclick="window.eliminarEvidenciaTemporal(${idx})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>`;
                            $('#previewEvidencias').append(preview);
                        };
                        reader.readAsDataURL(ev);
                    });

                    toastr.info('Modifica la incidencia y presiona \"Actualizar incidencia\"');
                }

                window.eliminarEvidenciaTemporal = function(index) {
                    if (evidenciasPendientes[index]) {
                        evidenciasPendientes.splice(index, 1);
                        // Re-renderizar previews
                        $('#previewEvidencias').empty();
                        evidenciasPendientes.forEach((ev, idx) => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const preview = `<div class="m-1 position-relative" style="width:80px;height:80px;">
                                    <img src="${e.target.result}" class="rounded" style="width:100%;height:100%;object-fit:cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute" 
                                            style="top:2px;right:2px;padding:2px 6px;" 
                                            onclick="window.eliminarEvidenciaTemporal(${idx})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>`;
                                $('#previewEvidencias').append(preview);
                            };
                            reader.readAsDataURL(ev);
                        });
                        toastr.info('Evidencia eliminada');
                    }
                }

                window.eliminarIncidenciaPendiente = function(incidenciaId) {
                    Swal.fire({
                        title: '\u00bfEliminar incidencia?',
                        text: 'Esta acci\u00f3n no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'S\u00ed, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const index = confirmacionState.incidenciasPendientes.findIndex(i => i.id === incidenciaId);
                            if (index !== -1) {
                                const incidencia = confirmacionState.incidenciasPendientes[index];
                                const productoId = incidencia.producto_id;
                                confirmacionState.incidenciasPendientes.splice(index, 1);
                                toastr.success('Incidencia eliminada');
                                
                                // Recargar todas las incidencias
                                window.cargarIncidenciasProducto(productoId);
                                
                                // Actualizar estado visual del producto
                                actualizarEstadoProducto(productoId);
                                
                                // Actualizar contador
                                actualizarContadorIncidencias(productoId);
                            }
                        }
                    });
                }

                function actualizarEstadoProducto(productoId) {
                    const checkbox = $(`.chk-producto[data-producto="${productoId}"]`);
                    if (!checkbox.length) return;
                    
                    const countPendientes = confirmacionState.incidenciasPendientes.filter(i => i.producto_id === productoId).length;
                    
                    // Obtener incidencias guardadas
                    $.get(`${rutasConfirmacion.incidencias}/${productoId}/incidencias`).done(resp => {
                        const incidenciasGuardadas = (resp.incidencias || []).length;
                        const totalIncidencias = countPendientes + incidenciasGuardadas;
                        
                        const celdaAcciones = checkbox.closest('tr').find('td:last');
                        const badgeIncidencia = celdaAcciones.find('.badge-danger');
                        
                        if (totalIncidencias > 0) {
                            // Tiene incidencias: desmarcar y deshabilitar checkbox
                            checkbox.prop('checked', false).prop('disabled', true);
                            
                            // Agregar badge si no existe
                            if (badgeIncidencia.length === 0) {
                                celdaAcciones.find('.btn-incidencia').before('<span class="mb-1 badge badge-danger">Incidencia</span><br>');
                            }
                        } else {
                            // No tiene incidencias: habilitar checkbox
                            checkbox.prop('disabled', false);
                            
                            // Remover badge
                            badgeIncidencia.next('br').remove();
                            badgeIncidencia.remove();
                        }
                    });
                }

                function actualizarContadorIncidencias(productoId) {
                    // Contar incidencias pendientes locales
                    const countPendientes = confirmacionState.incidenciasPendientes.filter(i => i.producto_id === productoId).length;
                    
                    // Obtener incidencias guardadas del servidor
                    $.get(`${rutasConfirmacion.incidencias}/${productoId}/incidencias`)
                        .done(resp => {
                            const countGuardadas = (resp.incidencias || []).length;
                            const total = countGuardadas + countPendientes;
                            
                            const badge = $(`.btn-incidencia[data-producto="${productoId}"] .badge`);
                            if (total > 0) {
                                badge.text(total).removeClass('badge-secondary').addClass(countPendientes > 0 ? 'badge-warning' : 'badge-info');
                            } else {
                                badge.text('0').removeClass('badge-warning badge-info').addClass('badge-secondary');
                            }
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

                // Handle modal close properly to avoid black screen
                $('#modalImagenesIncidencia').on('hidden.bs.modal', function () {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                });

                window.verImagenesIncidencia = function(incidenciaId) {
                    const modal = $('#modalImagenesIncidencia');
                    modal.modal('show');
                    $('#bodyImagenesIncidencia').html('<div class="py-4 text-center"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Cargando imágenes...</p></div>');

                    $.get(`${rutasConfirmacion.evidenciasIncidencia}/${incidenciaId}/evidencias`)
                        .done(resp => {
                            const evidencias = resp.evidencias || [];
                            if (!evidencias.length) {
                                $('#bodyImagenesIncidencia').html('<div class="mb-0 alert alert-info"><i class="fas fa-info-circle"></i> Esta incidencia no tiene evidencias fotográficas.</div>');
                                return;
                            }

                            let grid = '<div class="row">';
                            evidencias.forEach(e => {
                                grid += `<div class="mb-3 col-6 col-md-4">
                                    <div class="p-2 border rounded" style="height:200px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f8f9fa;">
                                        <a href="${e.url}" target="_blank" title="Ver imagen completa">
                                            <img src="${e.url}" alt="evidencia" class="img-fluid" style="max-height:180px;max-width:100%;object-fit:contain;">
                                        </a>
                                    </div>
                                    ${e.descripcion ? `<small class="mt-1 text-muted d-block">${e.descripcion}</small>` : ''}
                                </div>`;
                            });
                            grid += '</div>';
                            $('#bodyImagenesIncidencia').html(grid);
                        })
                        .fail(() => {
                            $('#bodyImagenesIncidencia').html('<div class="mb-0 alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar las imágenes.</div>');
                        });
                }

                // Handle modal close properly to avoid black screen
                $('#modalImagenesIncidencia').on('hidden.bs.modal', function () {
                    // Remove any lingering backdrops
                    $('.modal-backdrop').remove();
                    // Restore body scroll
                    $('body').removeClass('modal-open').css('padding-right', '');
                });

                // Redefinir cargarIncidenciasProducto para incluir guardadas
                window.cargarIncidenciasProducto = function(productoId) {
                    $('#listaIncidenciasProducto').html(skeleton('Cargando incidencias...'));
                    
                    $.get(`${rutasConfirmacion.incidencias}/${productoId}/incidencias`)
                        .done(resp => {
                            const incidenciasGuardadas = resp.incidencias || [];
                            renderTodasIncidencias(productoId, incidenciasGuardadas);
                        })
                        .fail(() => {
                            $('#listaIncidenciasProducto').html('<div class="mb-0 alert alert-danger">No se pudieron cargar las incidencias.</div>');
                        });
                };

                // Nueva función para renderizar todas las incidencias
                window.renderTodasIncidencias = function(productoId, incidenciasGuardadas = []) {
                    const incidenciasPendientes = confirmacionState.incidenciasPendientes.filter(i => i.producto_id === productoId);
                    
                    const facturaActual = confirmacionState.facturas.find(f => f.distribucion_factura_id === confirmacionState.facturaSeleccionada);
                    const puedeEditar = facturaActual && facturaActual.estado_entrega === 'sin_entrega';
                    
                    if (incidenciasGuardadas.length === 0 && incidenciasPendientes.length === 0) {
                        $('#listaIncidenciasProducto').html('<p class="mb-0 text-muted">No hay incidencias para este producto.</p>');
                        return;
                    }

                    let html = '<div class="list-group">';
                    
                    // Renderizar incidencias guardadas (solo lectura)
                    incidenciasGuardadas.forEach((inc) => {
                        const tipoLabel = {
                            'producto_danado': 'Producto dañado',
                            'cantidad_incorrecta': 'Cantidad incorrecta',
                            'cliente_rechazo': 'Cliente rechazó',
                            'direccion_incorrecta': 'Dirección incorrecta',
                            'otro': 'Otro'
                        }[inc.tipo] || inc.tipo;

                        const evidenciasCount = inc.evidencias_count || 0;
                        
                        html += `<div class="mb-2 list-group-item bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="badge badge-success">Guardada</span>
                                    <span class="badge badge-warning">${tipoLabel}</span>
                                    <small class="ml-2 text-muted"><i class="fas fa-lock"></i> No editable</small>
                                    <p class="mt-2 mb-1">${inc.descripcion || ''}</p>
                                    ${evidenciasCount > 0 ? `<a href="#" onclick="window.verImagenesIncidencia(${inc.id}); return false;" class="btn btn-sm btn-link p-0"><i class="fas fa-images"></i> ${evidenciasCount} evidencia(s)</a>` : '<small class="text-muted">Sin evidencias</small>'}
                                </div>
                            </div>
                        </div>`;
                    });
                    
                    // Renderizar incidencias pendientes
                    incidenciasPendientes.forEach((inc) => {
                        const tipoLabel = {
                            'producto_danado': 'Producto dañado',
                            'cantidad_incorrecta': 'Cantidad incorrecta',
                            'cliente_rechazo': 'Cliente rechazó',
                            'direccion_incorrecta': 'Dirección incorrecta',
                            'otro': 'Otro'
                        }[inc.tipo] || inc.tipo;
                        
                        html += `<div class="mb-2 list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="badge badge-info">Pendiente</span>
                                    <span class="badge badge-warning">${tipoLabel}</span>
                                    <p class="mt-2 mb-1">${inc.descripcion}</p>
                                    ${inc.evidencias && inc.evidencias.length > 0 ? `<small class="text-muted"><i class="fas fa-images"></i> ${inc.evidencias.length} evidencia(s)</small>` : ''}
                                </div>
                                <div class="ml-2 btn-group-vertical btn-group-sm">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.editarIncidenciaPendiente(${inc.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.eliminarIncidenciaPendiente(${inc.id})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    });
                    
                    html += '</div>';
                    $('#listaIncidenciasProducto').html(html);
                };

                // Función para eliminar incidencias guardadas
                window.eliminarIncidenciaGuardada = function(incidenciaId, productoId) {
                    const facturaActual = confirmacionState.facturas.find(f => f.distribucion_factura_id === confirmacionState.facturaSeleccionada);
                    if (!facturaActual || facturaActual.estado_entrega !== 'sin_entrega') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No permitido',
                            text: 'Solo se pueden eliminar incidencias cuando la factura está en estado "Sin Entrega"',
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '¿Eliminar incidencia guardada?',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `${rutasConfirmacion.incidencias}/${incidenciaId}/eliminar`,
                                type: 'POST',
                                data: {
                                    _token: csrfToken
                                },
                                success: function(resp) {
                                    toastr.success('Incidencia eliminada correctamente');
                                    window.cargarIncidenciasProducto(productoId);
                                    actualizarContadorIncidencias(productoId);
                                },
                                error: function(xhr) {
                                    const r = xhr.responseJSON || {};
                                    Swal.fire('Error', r.message || 'No se pudo eliminar la incidencia', 'error');
                                }
                            });
                        }
                    });
                };
        });
    </script>
</div>
