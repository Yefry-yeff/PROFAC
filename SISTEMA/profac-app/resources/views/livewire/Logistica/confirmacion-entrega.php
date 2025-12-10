<div class="logistica-confirmacion">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Confirmación de entregas</h4>
                <p class="text-muted mb-0">Selecciona el equipo, luego la factura para validar los productos entregados.</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-right mr-3">
                    <span class="d-block text-uppercase small text-muted">Fecha programada</span>
                    <input type="date" class="form-control form-control-sm" id="fechaConfirmacion" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="row">
                <div class="col-lg-4 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-uppercase text-muted mb-0">Equipos programados</h6>
                        <span class="badge badge-light" id="totalEquipos">0</span>
                    </div>
                    <div id="listaDistribuciones" style="min-height:250px;" class="pr-lg-3"></div>
                </div>
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-uppercase text-muted mb-0">Detalle de confirmación</h6>
                    </div>
                    <div id="contenedorFacturas" class="border rounded bg-light p-4" style="min-height:320px;">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-truck-loading fa-2x mb-3"></i>
                            <p class="mb-0">Selecciona un equipo para ver sus facturas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalIncidencia">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar incidencia</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formIncidencia">
                        <input type="hidden" id="productoIncidenciaId">
                        <div class="form-group">
                            <label class="small text-muted">Tipo de incidencia</label>
                            <select class="form-control" id="tipoIncidencia">
                                <option value="producto_danado">Producto dañado</option>
                                <option value="cantidad_incorrecta">Cantidad incorrecta</option>
                                <option value="cliente_rechazo">Cliente rechazó</option>
                                <option value="direccion_incorrecta">Dirección incorrecta</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small text-muted">Descripción</label>
                            <textarea class="form-control" id="descripcionIncidencia" rows="4" placeholder="Describe lo sucedido..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarIncidencia()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const confirmacionState = {
            distribucionActual: null,
            facturaSeleccionada: null,
            facturas: []
        };

        $(document).ready(function () {
            $('#fechaConfirmacion').on('change', cargarDistribucionesFecha);
            cargarDistribucionesFecha();
        });

        function plantillaVacia(mensaje) {
            return `<div class="text-center text-muted py-5">
                <i class="fas fa-clipboard-list fa-2x mb-3"></i>
                <p class="mb-0">${mensaje}</p>
            </div>`;
        }

        function skeleton(texto) {
            return `<div class="text-center text-muted py-5">
                <div class="spinner-border spinner-border-sm mr-2"></div>${texto}
            </div>`;
        }

        function cargarDistribucionesFecha() {
            const fecha = $('#fechaConfirmacion').val();
            $('#listaDistribuciones').html(skeleton('Cargando equipos...'));
            $('#contenedorFacturas').html(plantillaVacia('Selecciona un equipo para ver sus facturas.'));
            confirmacionState.distribucionActual = null;
            confirmacionState.facturaSeleccionada = null;
            confirmacionState.facturas = [];

            $.get("<?= route('logistica.confirmacion.distribuciones') ?>", { fecha })
                .done(resp => {
                    const distribuciones = resp.distribuciones || [];
                    $('#totalEquipos').text(distribuciones.length);
                    renderDistribuciones(distribuciones);
                })
                .fail(() => {
                    $('#listaDistribuciones').html('<div class="alert alert-danger">No se pudieron cargar los equipos.</div>');
                    $('#totalEquipos').text('0');
                });
        }

        function renderDistribuciones(distribuciones) {
            if (!distribuciones.length) {
                $('#listaDistribuciones').html('<div class="alert alert-light">No hay distribuciones para la fecha seleccionada.</div>');
                return;
            }

            let html = '<div class="list-group list-group-flush shadow-sm rounded">';
            distribuciones.forEach(d => {
                const progreso = d.total_facturas ? Math.round((d.facturas_entregadas / d.total_facturas) * 100) : 0;
                html += `<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-distribucion ${confirmacionState.distribucionActual === d.id ? 'active' : ''}" onclick="seleccionarDistribucion(${d.id}, this)">
                    <div>
                        <div class="font-weight-bold mb-0">${d.nombre_equipo}</div>
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
            confirmacionState.distribucionActual = distribucionId;
            confirmacionState.facturaSeleccionada = null;
            confirmacionState.facturas = [];
            $('.btn-distribucion').removeClass('active');
            $(element).addClass('active');
            cargarConfirmacion(distribucionId);
        }

        function cargarConfirmacion(distribucionId) {
            $('#contenedorFacturas').html(skeleton('Cargando facturas...'));
            $.get("<?= url('/logistica/confirmacion/facturas') ?>/" + distribucionId)
                .done(resp => {
                    confirmacionState.facturas = resp.facturas || [];
                    confirmacionState.facturaSeleccionada = confirmacionState.facturas.length ? confirmacionState.facturas[0].distribucion_factura_id : null;
                    renderFacturasPanel();
                })
                .fail(() => {
                    $('#contenedorFacturas').html('<div class="alert alert-danger">No se pudieron cargar las facturas.</div>');
                });
        }

        function renderFacturasPanel() {
            if (!confirmacionState.facturas.length) {
                $('#contenedorFacturas').html(plantillaVacia('El equipo seleccionado no tiene facturas pendientes.'));
                return;
            }

            const layout = `
                <div class="row">
                    <div class="col-lg-5 mb-3 mb-lg-0">
                        <div class="border rounded h-100 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-uppercase small text-muted">Facturas asignadas</span>
                                <span class="badge badge-light">${confirmacionState.facturas.length}</span>
                            </div>
                            <div id="listaFacturas" class="list-group list-group-flush"></div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="border rounded h-100 p-3 bg-white">
                            <div id="detalleFactura"></div>
                        </div>
                    </div>
                </div>
                <div class="text-right mt-3">
                    <button type="button" class="btn btn-primary" id="btnConfirmarEntrega" onclick="guardarConfirmacion()">
                        <i class="fas fa-save mr-1"></i>Confirmar entrega
                    </button>
                </div>`;

            $('#contenedorFacturas').html(layout);
            renderFacturasList();
            renderDetalleFactura(confirmacionState.facturaSeleccionada);
        }

        function renderFacturasList() {
            if (!confirmacionState.facturas.length) {
                $('#listaFacturas').html('<p class="text-muted mb-0">Sin facturas.</p>');
                return;
            }

            let html = '';
            confirmacionState.facturas.forEach(f => {
                const isActive = confirmacionState.facturaSeleccionada === f.distribucion_factura_id;
                const progreso = calcularProgresoFactura(f);
                html += `<button type="button" class="list-group-item list-group-item-action factura-item ${isActive ? 'active' : ''}" data-factura="${f.distribucion_factura_id}" onclick="seleccionarFactura(${f.distribucion_factura_id})">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="font-weight-bold">Factura #${f.numero_factura}</div>
                            <small class="d-block text-muted">${f.cliente}</small>
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
            const factura = confirmacionState.facturas.find(f => f.distribucion_factura_id === distribucionFacturaId);
            if (!factura) {
                $('#detalleFactura').html('<p class="text-muted mb-0">Selecciona una factura para revisar sus productos.</p>');
                return;
            }

            const productos = factura.productos || [];
            const articulosEntregados = productos.filter(p => Number(p.entregado) === 1).length;
            const progreso = productos.length ? Math.round((articulosEntregados / productos.length) * 100) : 0;

            let filas = '';
            productos.forEach((p, index) => {
                const tieneIncidencia = Number(p.tiene_incidencia) === 1;
                filas += `<tr>
                    <td>${index + 1}</td>
                    <td>
                        <div class="font-weight-bold">${p.nombre_producto}</div>
                        <small class="text-muted">ID #${p.producto_id}</small>
                    </td>
                    <td class="text-center">${p.cantidad_facturada}</td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input position-static chk-producto" data-producto="${p.id}" data-factura="${factura.distribucion_factura_id}" data-cantidad="${p.cantidad_facturada}" ${p.entregado ? 'checked' : ''} ${tieneIncidencia ? 'disabled' : ''}>
                    </td>
                    <td class="text-center">
                        ${tieneIncidencia
                            ? '<span class="badge badge-danger">Incidencia</span>'
                            : `<button type="button" class="btn btn-outline-warning btn-sm" onclick="abrirIncidencia(${p.id})"><i class="fas fa-exclamation-triangle mr-1"></i>Reportar</button>`}
                    </td>
                </tr>`;
            });

            const tabla = productos.length ? `<div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Factura #${factura.numero_factura}</h5>
                        <small class="text-muted d-block">${factura.cliente} ${factura.telefono_empresa ? '· ' + factura.telefono_empresa : ''}</small>
                        <small class="text-muted">${factura.direccion || 'Sin dirección registrada'}</small>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-outline-success btn-sm mb-2" onclick="marcarTodosEntregados(${factura.distribucion_factura_id})">
                            <i class="fas fa-check-double mr-1"></i>Marcar todos
                        </button>
                        <div class="small text-muted">${articulosEntregados}/${productos.length} productos · ${progreso}%</div>
                    </div>
                </div>
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
                : '<p class="text-muted mb-0">La factura no tiene productos asociados.</p>';

            $('#detalleFactura').html(tabla);
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
            const estado = factura.estado_entrega || 'sin_entrega';
            const mapa = {
                entregado: 'success',
                parcial: 'warning',
                sin_entrega: 'secondary'
            };
            const clase = mapa[estado] || 'secondary';
            return `<span class="badge badge-${clase} text-uppercase">${estado.replace('_', ' ')}</span>`;
        }

        function marcarTodosEntregados(distribucionFacturaId) {
            confirmacionState.facturaSeleccionada = distribucionFacturaId;
            const token = $('meta[name="csrf-token"]').attr('content');
            $.post("<?= url('/logistica/confirmacion/marcar-todos') ?>/" + distribucionFacturaId, {_token: token})
                .done(resp => {
                    Swal.fire(resp.title, resp.text, resp.icon);
                    cargarConfirmacion(confirmacionState.distribucionActual);
                })
                .fail(() => Swal.fire('Error', 'No se pudieron actualizar los productos.', 'error'));
        }

        function guardarConfirmacion() {
            if (!confirmacionState.facturas.length) {
                Swal.fire('Sin facturas', 'Selecciona un equipo válido antes de confirmar.', 'info');
                return;
            }

            const productos = [];
            $('.chk-producto').each(function () {
                const productoId = $(this).data('producto');
                if (!productoId) {
                    return;
                }
                productos.push({
                    id: productoId,
                    entregado: $(this).is(':checked') ? 1 : 0,
                    cantidad_entregada: $(this).is(':checked') ? $(this).data('cantidad') : 0
                });
            });

            if (!productos.length) {
                Swal.fire('Sin productos', 'No hay productos para confirmar.', 'info');
                return;
            }

            const boton = $('#btnConfirmarEntrega');
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span>Guardando...');

            $.ajax({
                url: "<?= route('logistica.confirmacion.guardar') ?>",
                type: 'POST',
                data: {
                    productos,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            })
                .done(resp => {
                    Swal.fire(resp.title, resp.text, resp.icon);
                    cargarConfirmacion(confirmacionState.distribucionActual);
                })
                .fail(x => {
                    const r = x.responseJSON || {};
                    Swal.fire(r.title || 'Error', r.text || 'No se pudo guardar la confirmación.', r.icon || 'error');
                })
                .always(() => {
                    boton.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Confirmar entrega');
                });
        }

        function abrirIncidencia(productoId) {
            $('#productoIncidenciaId').val(productoId);
            $('#tipoIncidencia').val('producto_danado');
            $('#descripcionIncidencia').val('');
            $('#modalIncidencia').modal('show');
        }

        function guardarIncidencia() {
            const productoId = $('#productoIncidenciaId').val();
            $('#modalIncidencia').modal('hide');
            if (!productoId) {
                return;
            }

            const $checkbox = $(`.chk-producto[data-producto='${productoId}']`);
            if ($checkbox.length) {
                $checkbox.prop('checked', false).prop('disabled', true);
                $checkbox.closest('tr').find('td:last-child').html('<span class="badge badge-danger">Incidencia</span>');
            }

            Swal.fire('Incidencia registrada', 'Recuerda capturar la evidencia y actualizar esta factura antes de confirmar.', 'success');
        }
    </script>
</div>