/* ══════════════════════════════════════════════════════════════════════
   REPORTE BI DE EXPO — lógica de filtros, KPIs, gráficas (ApexCharts) y
   tablas (DataTables) con "click-to-filter" cruzado entre gráficas.
   Sigue el mismo patrón usado en dashboard-ventas.js: $.get() contra rutas
   dedicadas que devuelven JSON desde el componente Livewire ReporteExpo.
═════════════════════════════════════════════════════════════════════════ */
var reporteExpo = (function () {
    var charts = {};
    var dtProductos = null;
    var dtOfertas = null;
    var ofertaSeleccionada = null;
    var productoSeleccionado = null;
    var volverAProducto = false;
    var volverAOferta = false;
    var origenBuscadorProductos = null;

    /* Filtro activo compartido entre todas las gráficas/tablas */
    var filtro = {
        expo_id: null,
        marca_id: '',
        escala_id: '',
        vendedor_id: '',
        teleasesor_ids: [],
        estado: '',
        fecha_desde: '',
        fecha_hasta: '',
        rentabilidad_base: 'ofertas',
    };

    function get(id) { return document.getElementById(id); }

    function esc(valor) {
        return $('<div>').text(valor === null || valor === undefined ? '' : String(valor)).html();
    }

    function fmt(v) {
        v = parseFloat(v || 0);
        return 'L. ' + v.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtN(v) {
        return parseInt(v || 0, 10).toLocaleString('es-HN');
    }

    function fmtQ(v) {
        return parseFloat(v || 0).toLocaleString('es-HN', { maximumFractionDigits: 4 });
    }

    function etiquetaEstadoFacturacion(estado) {
        return ({
            NO_FACTURADA: 'No facturada',
            PARCIALMENTE_FACTURADA: 'Parcialmente facturada',
            FACTURADA: 'Facturada',
        })[estado] || String(estado || '').replaceAll('_', ' ');
    }

    function actualizarUrl(extra) {
        var query = paramsActuales(extra);
        Object.keys(query).forEach(function (key) {
            if (query[key] === '' || query[key] === null || (Array.isArray(query[key]) && !query[key].length)) delete query[key];
        });
        window.history.replaceState({}, '', window.location.pathname + ($.param(query) ? '?' + $.param(query) : ''));
    }

    function setText(id, txt) {
        var el = get(id);
        if (el) el.textContent = txt;
    }

    function destroyChart(id) {
        if (charts[id]) {
            try { charts[id].destroy(); } catch (e) {}
            delete charts[id];
        }
    }

    function paramsActuales(extra) {
        return $.extend({}, filtro, extra || {});
    }

    /* ─────────────────────────── Filtros (chips activos) ────────────── */
    function renderChipsFiltro() {
        var cont = get('expo-filtros-activos');
        if (!cont) return;
        var etiquetas = {
            marca_id: 'Marca',
            escala_id: 'Escala',
            vendedor_id: 'Asesor',
            teleasesor_ids: 'Teleasesor',
            estado: 'Estado',
        };
        var campos = {
            marca_id: '#expo-f-marca', escala_id: '#expo-f-escala', vendedor_id: '#expo-f-vendedor',
            teleasesor_ids: '#expo-f-teleasesor', estado: '#expo-f-estado'
        };
        var html = '';
        Object.keys(etiquetas).forEach(function (key) {
            var activo = Array.isArray(filtro[key]) ? filtro[key].length : filtro[key];
            if (activo) {
                var texto = $(campos[key] + ' option:selected').map(function () { return $(this).text(); }).get().join(', ') || filtro[key];
                html += '<span class="badge badge-primary mr-1 mb-1" style="cursor:pointer" ' +
                        'onclick="reporteExpo.limpiarFiltro(\'' + key + '\')">' +
                        etiquetas[key] + ': ' + texto + ' &times;</span>';
            }
        });
        cont.innerHTML = html;
    }

    function limpiarFiltro(key) {
        filtro[key] = key === 'teleasesor_ids' ? [] : '';
        var mapId = { marca_id: 'expo-f-marca', escala_id: 'expo-f-escala', vendedor_id: 'expo-f-vendedor', teleasesor_ids: 'expo-f-teleasesor', estado: 'expo-f-estado' };
        if (mapId[key]) $('#' + mapId[key]).val(key === 'teleasesor_ids' ? [] : '').trigger('change.select2');
        recargarTodo();
    }

    function aplicarFiltroDesdeForm() {
        filtro.expo_id = $('#expo-selector').val() || null;
        filtro.marca_id = $('#expo-f-marca').val() || '';
        filtro.escala_id = $('#expo-f-escala').val() || '';
        filtro.vendedor_id = $('#expo-f-vendedor').val() || '';
        filtro.teleasesor_ids = $('#expo-f-teleasesor').val() || [];
        filtro.estado = $('#expo-f-estado').val() || '';
        filtro.fecha_desde = $('#expo-f-desde').val() || '';
        filtro.fecha_hasta = $('#expo-f-hasta').val() || '';
        filtro.rentabilidad_base = $('input[name="expo-f-rentabilidad-base"]:checked').val() || 'ofertas';
        recargarTodo();
    }

    /* ─────────────────────────── Catálogo de filtros ─────────────────── */
    function cargarCatalogoFiltros() {
        return $.get('/reporte/expo/catalogo-filtros', { expo_id: filtro.expo_id }).then(function (d) {
            var $marca = $('#expo-f-marca').empty().append('<option value="">Todas</option>');
            (d.marcas || []).forEach(function (m) {
                $marca.append('<option value="' + m.id + '">' + m.nombre + '</option>');
            });
            var $escala = $('#expo-f-escala').empty().append('<option value="">Todas</option>');
            (d.escalas || []).forEach(function (e) {
                $escala.append('<option value="' + e.id + '">' + e.nombre + '</option>');
            });
            var $vend = $('#expo-f-vendedor').empty().append('<option value="">Todos</option>');
            (d.vendedores || []).forEach(function (v) {
                $vend.append('<option value="' + v.id + '">' + esc(v.nombre || ('Usuario #' + v.id)) + '</option>');
            });
            var $tele = $('#expo-f-teleasesor').empty();
            (d.teleasesores || []).forEach(function (v) {
                $tele.append('<option value="' + v.id + '">' + esc(v.nombre || ('Usuario #' + v.id)) + '</option>');
            });
            if ($.fn.select2) {
                if ($tele.hasClass('select2-hidden-accessible')) $tele.select2('destroy');
                $tele.select2({ width: '100%', placeholder: 'Todos', allowClear: true });
            }
            $('#expo-f-marca').val(filtro.marca_id);
            $('#expo-f-escala').val(filtro.escala_id);
            $('#expo-f-vendedor').val(filtro.vendedor_id);
            $tele.val(filtro.teleasesor_ids).trigger('change.select2');
        });
    }

    /* ─────────────────────────── KPIs ─────────────────────────────────── */
    function cargarKpis() {
        ['kpi-ofertado', 'kpi-avance', 'kpi-ofertas', 'kpi-clientes', 'kpi-utilidad', 'kpi-margen', 'kpi-facturas', 'kpi-descuento', 'kpi-costo']
            .forEach(function (id) { setText(id, '…'); });

        $.get('/reporte/expo/kpis', paramsActuales()).then(function (d) {
            setText('kpi-ofertado', fmt(d.total_ofertado));
            setText('kpi-avance', (d.avance_pct !== null ? d.avance_pct : 0) + '%');
            setText('kpi-ofertas', fmtN(d.num_ofertas));
            setText('kpi-clientes', fmtN(d.clientes_unicos));
            setText('kpi-facturas', fmtN(d.num_facturas));
            setText('kpi-utilidad', fmt(d.total_utilidad));
            setText('kpi-descuento', fmt(d.total_descuento));
            setText('kpi-costo', fmt(d.total_costo));
            setText('kpi-margen', (d.margen_pct !== null ? d.margen_pct : 'N/D') + (d.margen_pct !== null ? '%' : ''));
        });
    }

    /* ─────────────────────────── Gráfica: Estado de ofertas (donut) ──── */
    function cargarEstadoOfertas() {
        $.get('/reporte/expo/estado-ofertas', paramsActuales()).then(function (rows) {
            destroyChart('chart-estado');
            if (!get('chart-estado')) return;
            var labelsMap = {
                PENDIENTE_FACTURACION: 'Sin facturar',
                FACTURACION_PARCIAL: 'Factura parcial',
                PENDIENTE_LIQUIDACION: 'Pendiente liquidar',
                LIQUIDADA: 'Liquidada',
                SIN_REGISTRO: 'Sin registro',
            };
            var labels = rows.map(function (r) { return labelsMap[r.estado] || r.estado; });
            var serie = rows.map(function (r) { return parseInt(r.total, 10); });

            charts['chart-estado'] = new ApexCharts(get('chart-estado'), {
                chart: {
                    type: 'donut', height: 320,
                    events: {
                        dataPointSelection: function (event, ctx, config) {
                            var estado = rows[config.dataPointIndex].estado;
                            filtro.estado = (filtro.estado === estado) ? '' : estado;
                            $('#expo-f-estado').val(filtro.estado);
                            recargarTodo();
                        }
                    }
                },
                series: serie,
                labels: labels,
                legend: { position: 'bottom' },
                tooltip: { y: { formatter: function (v) { return fmtN(v) + ' ofertas'; } } },
            });
            charts['chart-estado'].render();
        });
    }

    /* ─────────────────────────── Gráfica: Ventas por marca (bar) ──────── */
    function cargarVentasPorMarca() {
        $.get('/reporte/expo/ventas-por-marca', paramsActuales()).then(function (rows) {
            destroyChart('chart-marca');
            if (!get('chart-marca')) return;
            var top = rows.slice(0, 12);

            charts['chart-marca'] = new ApexCharts(get('chart-marca'), {
                chart: {
                    type: 'bar', height: 380,
                    events: {
                        dataPointSelection: function (event, ctx, config) {
                            var marcaId = top[config.dataPointIndex].marca_id;
                            filtro.marca_id = (String(filtro.marca_id) === String(marcaId)) ? '' : marcaId;
                            $('#expo-f-marca').val(filtro.marca_id || '');
                            recargarTodo();
                        }
                    }
                },
                series: [
                    { name: 'Ofertado', data: top.map(function (r) { return r.ofertado; }) },
                    { name: 'Facturado', data: top.map(function (r) { return r.facturado; }) },
                ],
                xaxis: {
                    categories: top.map(function (r) { return r.marca; }),
                    labels: { formatter: function (v) { return 'L.' + fmtN(v); } }
                },
                tooltip: { y: { formatter: function (v) { return fmt(v); } } },
                colors: ['#4e73df', '#1cc88a'],
                legend: { position: 'top' },
                plotOptions: { bar: { horizontal: true } },
            });
            charts['chart-marca'].render();
        });
    }

    /* ─────────────────────────── Gráfica: Ventas por asesor (bar) ─────── */
    function cargarVentasPorAsesor() {
        $.get('/reporte/expo/ventas-por-asesor', paramsActuales()).then(function (rows) {
            destroyChart('chart-asesor');
            if (!get('chart-asesor')) return;
            var top = rows.slice(0, 12);

            charts['chart-asesor'] = new ApexCharts(get('chart-asesor'), {
                chart: {
                    type: 'bar', height: 380,
                    events: {
                        dataPointSelection: function (event, ctx, config) {
                            var vendedorId = top[config.dataPointIndex].vendedor_id;
                            filtro.vendedor_id = (String(filtro.vendedor_id) === String(vendedorId)) ? '' : vendedorId;
                            $('#expo-f-vendedor').val(filtro.vendedor_id || '');
                            recargarTodo();
                        }
                    }
                },
                series: [
                    { name: 'Ofertado', data: top.map(function (r) { return r.ofertado; }) },
                    { name: 'Facturado', data: top.map(function (r) { return r.facturado; }) },
                ],
                xaxis: { categories: top.map(function (r) { return r.asesor; }) },
                yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
                tooltip: { y: { formatter: function (v) { return fmt(v); } } },
                colors: ['#4e73df', '#1cc88a'],
                legend: { position: 'top' },
            });
            charts['chart-asesor'].render();
        });
    }

    function cargarVentasPorTeleasesor() {
        $.get('/reporte/expo/ventas-por-teleasesor', paramsActuales()).then(function (rows) {
            destroyChart('chart-teleasesor');
            if (!get('chart-teleasesor')) return;
            var top = rows.slice(0, 15);

            charts['chart-teleasesor'] = new ApexCharts(get('chart-teleasesor'), {
                chart: {
                    type: 'bar', height: 380,
                    events: {
                        dataPointSelection: function (event, ctx, config) {
                            var id = String(top[config.dataPointIndex].teleasesor_id || '');
                            var actuales = (filtro.teleasesor_ids || []).map(String);
                            filtro.teleasesor_ids = actuales.indexOf(id) >= 0
                                ? actuales.filter(function (valor) { return valor !== id; })
                                : actuales.concat([id]);
                            $('#expo-f-teleasesor').val(filtro.teleasesor_ids).trigger('change.select2');
                            recargarTodo();
                        }
                    }
                },
                series: [
                    { name: 'Ofertado', data: top.map(function (r) { return r.ofertado; }) },
                    { name: 'Facturado', data: top.map(function (r) { return r.facturado; }) },
                    { name: 'Utilidad', data: top.map(function (r) { return r.utilidad; }) },
                ],
                xaxis: { categories: top.map(function (r) { return r.teleasesor; }) },
                yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: { formatter: function (v) { return fmt(v); } },
                    custom: function (ctx) {
                        var r = top[ctx.dataPointIndex];
                        return '<div class="p-2"><strong>' + esc(r.teleasesor) + '</strong><br>' +
                            'Ofertado: ' + fmt(r.ofertado) + '<br>Facturado: ' + fmt(r.facturado) +
                            '<br>Utilidad: ' + fmt(r.utilidad) + '<br>Descuento: ' + fmt(r.descuento) +
                            '<br>Ofertas: ' + fmtN(r.ofertas) + ' · Ganadas: ' + fmtN(r.ofertas_ganadas) +
                            '<br>Conversión: ' + r.conversion_pct + '% · Margen: ' + (r.margen_pct === null ? 'N/D' : r.margen_pct + '%') + '</div>';
                    }
                },
                colors: ['#2f6f9f', '#15906b', '#d18b24'],
                legend: { position: 'top' },
                plotOptions: { bar: { columnWidth: '58%' } },
            });
            charts['chart-teleasesor'].render();
        });
    }

    /* ─────────────────────────── Gráfica: Evolución diaria (line) ─────── */
    function cargarEvolucionDiaria() {
        $.get('/reporte/expo/evolucion-diaria', paramsActuales()).then(function (rows) {
            destroyChart('chart-evolucion-expo');
            if (!get('chart-evolucion-expo')) return;

            charts['chart-evolucion-expo'] = new ApexCharts(get('chart-evolucion-expo'), {
                chart: { type: 'line', height: 320, toolbar: { show: true } },
                series: [
                    { name: 'Ofertado', data: rows.map(function (r) { return r.ofertado; }) },
                    { name: 'Facturado', data: rows.map(function (r) { return r.facturado; }) },
                ],
                xaxis: { categories: rows.map(function (r) { return r.fecha; }), type: 'datetime' },
                yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
                tooltip: { y: { formatter: function (v) { return fmt(v); } } },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#4e73df', '#1cc88a'],
                legend: { position: 'top' },
            });
            charts['chart-evolucion-expo'].render();
        });
    }

    /* ─────────────────────────── Tabla: productos ─────────────────────── */
    function cargarTablaProductos() {
        $.get('/reporte/expo/tabla-productos', paramsActuales()).then(function (rows) {
            var esFactura = filtro.rentabilidad_base === 'facturas';
            setText('titulo-analitica-productos', esFactura ? 'Analítica de Productos por Facturas' : 'Analítica de Productos por Ofertas');
            setText('th-productos-cantidad-base', esFactura ? 'Cant. Facturada' : 'Cant. Ofertada');
            setText('th-productos-venta-base', esFactura ? 'Venta Factura' : 'Venta Oferta');
            setText('th-productos-descuento-base', esFactura ? 'Descuento Factura' : 'Descuento Oferta');
            setText('th-productos-costo-base', esFactura ? 'Costo Factura' : 'Costo Oferta');
            setText('th-productos-utilidad-base', esFactura ? 'Utilidad Factura' : 'Utilidad Oferta');
            setText('th-productos-margen-base', esFactura ? 'Margen Factura %' : 'Margen Oferta %');
            if (dtProductos) { dtProductos.destroy(); $('#tabla-expo-productos tbody').empty(); }
            var tbody = $('#tabla-expo-productos tbody').empty();
            rows.forEach(function (r) {
                tbody.append('<tr class="bi-row-selectable" data-producto-id="' + r.producto_id + '" title="Abrir analítica del producto">' +
                    '<td>' + esc(r.codigo) + '</td>' +
                    '<td>' + esc(r.producto) + '</td>' +
                    '<td>' + esc(r.marca) + '</td>' +
                    '<td>' + esc(r.categoria) + '</td>' +
                    '<td class="text-right">' + fmtN(r.numero_ofertas) + '</td>' +
                    '<td class="text-right">' + fmtQ(esFactura ? r.cantidad_facturada : r.cantidad_ofertada) + '</td>' +
                    '<td class="text-right">' + fmt(r.total_base) + '</td>' +
                    '<td class="text-right">' + fmt(r.descuento) + '</td>' +
                    '<td class="text-right">' + fmt(r.total_costo) + '</td>' +
                    '<td class="text-right ' + (r.utilidad >= 0 ? 'bi-profit' : 'bi-loss') + '">' + (r.utilidad >= 0 ? '+' : '-') + ' ' + fmt(Math.abs(r.utilidad)) + '</td>' +
                    '<td class="text-right">' + (r.margen_pct !== null ? r.margen_pct + '%' : 'N/D') + '</td>' +
                    '</tr>');
            });
            dtProductos = $('#tabla-expo-productos').DataTable({
                pageLength: 10, lengthChange: true, order: [], language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
            });
            $('#tabla-expo-productos tbody').off('click', 'tr').on('click', 'tr', function () {
                var id = $(this).data('producto-id');
                if (id) abrirProducto(id, false);
            });
        });
    }

    /* ─────────────────────────── Tabla: ofertas ────────────────────────── */
    function cargarTablaOfertas() {
        $.get('/reporte/expo/tabla-ofertas', paramsActuales()).then(function (rows) {
            if (dtOfertas) { dtOfertas.destroy(); $('#tabla-expo-ofertas tbody').empty(); }
            var tbody = $('#tabla-expo-ofertas tbody').empty();
            rows.forEach(function (r) {
                var estado = etiquetaEstadoFacturacion(r.estado_facturacion);
                tbody.append('<tr class="bi-row-selectable" data-oferta-id="' + r.oferta_id + '" title="Abrir detalle completo de la oferta">' +
                    '<td>' + r.oferta_id + '</td>' +
                    '<td>' + (r.flujo_id || '—') + '</td>' +
                    '<td>' + esc(r.cliente) + '</td>' +
                    '<td>' + esc(r.asesor) + '</td>' +
                    '<td>' + esc(r.teleasesor) + '</td>' +
                    '<td>' + esc(r.fecha_emision || '') + '</td>' +
                    '<td><span class="badge ' + (r.estado_facturacion === 'FACTURADA' ? 'badge-success' : (r.estado_facturacion === 'PARCIALMENTE_FACTURADA' ? 'badge-warning' : 'badge-secondary')) + '">' + esc(estado) + '</span></td>' +
                    '<td class="text-right">' + fmt(r.total_ofertado) + '</td>' +
                    '<td class="text-right">' + fmt(r.total_facturado) + '</td>' +
                    '<td class="text-right">' + (r.margen_pct !== null ? r.margen_pct + '%' : 'N/D') + '</td>' +
                    '<td class="text-right">' + r.avance_pct + '%</td>' +
                    '</tr>');
            });
            dtOfertas = $('#tabla-expo-ofertas').DataTable({
                pageLength: 10, lengthChange: true, order: [], language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
            });
            $('#tabla-expo-ofertas tbody').off('click', 'tr').on('click', 'tr', function () {
                var id = $(this).data('oferta-id');
                if (id) abrirOferta(id, false);
            });
        });
    }

    function resumenItem(etiqueta, valor, clase) {
        return '<div class="bi-summary-item"><small>' + esc(etiqueta) + '</small><strong class="' + (clase || '') + '">' + valor + '</strong></div>';
    }

    function infoOferta(etiqueta, valor) {
        return '<div class="col-sm-6 col-lg-3 mb-2"><span class="text-muted d-block">' + esc(etiqueta) + '</span><strong>' + esc(valor || 'Sin asignar') + '</strong></div>';
    }

    function renderOferta(data) {
        var o = data.oferta;
        var r = data.resumen;
        ofertaSeleccionada = o.id;
        setText('modal-oferta-titulo', 'Oferta #' + o.id + ' · ' + o.cliente);
        setText('modal-oferta-num-facturas', data.facturas.length);

        $('#modal-oferta-general').html(
            infoOferta('Oferta', '#' + o.id) + infoOferta('Flujo', o.flujo_id || 'Sin flujo') +
            infoOferta('Expo', o.expo) + infoOferta('Cliente', o.cliente) +
            infoOferta('RTN', o.rtn || 'No registrado') + infoOferta('Asesor comercial', o.asesor) +
            infoOferta('Teleasesor', o.teleasesor) + infoOferta('Fecha / hora', o.fecha + (o.hora ? ' ' + o.hora : '')) +
            infoOferta('Estado', o.estado) + infoOferta('Tipo de venta', o.tipo_venta) +
            infoOferta('Condición de pago', o.condicion_pago) + infoOferta('Creada por', o.creado_por) +
            infoOferta('Modificada por', o.modificado_por || 'Sin modificación auditada')
        );

        var utilidadClase = r.utilidad >= 0 ? 'bi-profit' : 'bi-loss';
        $('#modal-oferta-resumen').html(
            resumenItem('Subtotal original', fmt(r.subtotal_original)) +
            resumenItem('Descuento otorgado', '- ' + fmt(r.descuento)) +
            resumenItem('Subtotal después de descuento', fmt(r.subtotal_final)) +
            resumenItem('ISV / Impuesto', fmt(r.isv)) +
            resumenItem('Total con impuesto', fmt(r.total)) +
            resumenItem('Costo total', fmt(r.costo)) +
            resumenItem(r.utilidad >= 0 ? 'Ganancia' : 'Pérdida', (r.utilidad >= 0 ? '+ ' : '- ') + fmt(Math.abs(r.utilidad)), utilidadClase) +
            resumenItem('Margen de utilidad', r.margen_pct === null ? 'N/D' : r.margen_pct + '%', utilidadClase)
        );

        var tbody = $('#modal-oferta-productos tbody').empty();
        data.productos.forEach(function (p) {
            tbody.append('<tr class="bi-row-selectable" data-producto-id="' + p.producto_id + '" title="Abrir analítica de este producto">' +
                '<td>' + esc(p.codigo) + '<br><strong>' + esc(p.producto) + '</strong></td>' +
                '<td>' + esc(p.marca) + '</td><td>' + esc(p.categoria) + '</td><td>' + esc(p.escala) + '</td>' +
                '<td class="text-right">' + fmtQ(p.cantidad) + '</td><td class="text-right">' + fmt(p.precio_base) + '</td>' +
                '<td class="text-right">' + fmt(p.precio_antes_descuento) + '</td>' +
                '<td class="text-right">' + fmt(p.descuento) + '<br><small>' + p.descuento_pct + '%</small></td>' +
                '<td class="text-right">' + fmt(p.precio_final) + '</td><td class="text-right">' + fmt(p.subtotal_final) + '</td>' +
                '<td class="text-right">' + fmt(p.isv) + '</td><td class="text-right">' + fmt(p.total) + '</td>' +
                '<td class="text-right">' + fmt(p.costo_total) + '</td>' +
                '<td class="text-right ' + (p.utilidad >= 0 ? 'bi-profit' : 'bi-loss') + '">' +
                    (p.utilidad >= 0 ? '+ ' : '- ') + fmt(Math.abs(p.utilidad)) + '<br><small>' + (p.margen_pct === null ? 'N/D' : p.margen_pct + '%') + '</small></td></tr>');
        });
        $('#modal-oferta-productos tfoot').html('<tr class="font-weight-bold"><td colspan="9">Totales consolidados</td>' +
            '<td class="text-right">' + fmt(r.subtotal_final) + '</td><td class="text-right">' + fmt(r.isv) + '</td>' +
            '<td class="text-right">' + fmt(r.total) + '</td><td class="text-right">' + fmt(r.costo) + '</td>' +
            '<td class="text-right">' + fmt(r.utilidad) + '<br><small>' + (r.margen_pct === null ? 'N/D' : r.margen_pct + '%') + '</small></td></tr>');
        tbody.off('click', 'tr').on('click', 'tr', function () {
            var productoId = $(this).data('producto-id');
            if (productoId) abrirProducto(productoId, true);
        });

        $('#modal-facturas-comparacion').html(
            resumenItem('Total ofertado neto', fmt(r.subtotal_final)) +
            resumenItem('Total facturado neto', fmt(r.total_facturado)) +
            resumenItem('Diferencia pendiente', fmt(r.diferencia_pendiente)) +
            resumenItem('Estado', etiquetaEstadoFacturacion(r.estado_facturacion))
        );
        renderFacturas(data.facturas);
    }

    function renderFacturas(facturas) {
        var cont = $('#modal-oferta-facturas-contenido').empty();
        if (!facturas.length) {
            cont.html('<div class="alert alert-secondary mb-0"><strong>No facturada.</strong> No existen facturas activas vinculadas a las líneas de esta oferta.</div>');
            return;
        }

        facturas.forEach(function (f, indice) {
            var lineas = '';
            f.productos.forEach(function (p) {
                lineas += '<tr><td>' + esc(p.codigo) + '</td><td>' + esc(p.producto) + '</td><td>' + esc(p.marca) + '</td>' +
                    '<td class="text-right">' + fmtQ(p.cantidad) + '</td><td class="text-right">' + fmt(p.precio) + '</td>' +
                    '<td class="text-right">' + fmt(p.descuento) + '</td><td class="text-right">' + fmt(p.isv) + '</td>' +
                    '<td class="text-right">' + fmt(p.total) + '</td><td class="text-right">' + fmt(p.costo) + '</td>' +
                    '<td class="text-right ' + (p.utilidad >= 0 ? 'bi-profit' : 'bi-loss') + '">' +
                    (p.utilidad >= 0 ? '+ ' : '- ') + fmt(Math.abs(p.utilidad)) + '<br><small>' + (p.margen_pct === null ? 'N/D' : p.margen_pct + '%') + '</small></td></tr>';
            });
            var collapseId = 'factura-expo-detalle-' + f.id;
            cont.append('<div class="card mb-2"><div class="card-header p-2 d-flex align-items-center">' +
                '<button class="btn btn-link text-left p-0 font-weight-bold" type="button" data-toggle="collapse" data-target="#' + collapseId + '">' +
                '<i class="fas fa-receipt mr-1"></i> Factura ' + esc(f.numero) + ' · ' + esc(f.fecha) + '</button>' +
                '<span class="ml-auto badge badge-success">' + esc(f.estado) + '</span></div>' +
                '<div id="' + collapseId + '" class="collapse ' + (indice === 0 ? 'show' : '') + '"><div class="card-body p-2">' +
                '<div class="row small mb-2">' + infoOferta('Cliente', f.cliente) + infoOferta('Asesor', f.asesor) +
                infoOferta('Teleasesor', f.teleasesor) + infoOferta('Total factura', fmt(f.total)) + '</div>' +
                '<div class="bi-modal-summary mb-2">' + resumenItem('Subtotal factura', fmt(f.subtotal)) +
                resumenItem('Descuento factura', fmt(f.descuento)) + resumenItem('ISV factura', fmt(f.isv)) +
                resumenItem('Total factura', fmt(f.total)) + resumenItem('Costo relacionado', fmt(f.costo)) +
                resumenItem(f.utilidad >= 0 ? 'Ganancia' : 'Pérdida', (f.utilidad >= 0 ? '+ ' : '- ') + fmt(Math.abs(f.utilidad)), f.utilidad >= 0 ? 'bi-profit' : 'bi-loss') +
                resumenItem('Margen', f.margen_pct === null ? 'N/D' : f.margen_pct + '%') + '</div>' +
                '<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr>' +
                '<th>Código</th><th>Producto</th><th>Marca</th><th class="text-right">Cantidad</th><th class="text-right">Precio</th>' +
                '<th class="text-right">Descuento</th><th class="text-right">ISV</th><th class="text-right">Total</th>' +
                '<th class="text-right">Costo</th><th class="text-right">Utilidad / Margen</th></tr></thead><tbody>' + lineas +
                '</tbody></table></div></div></div></div>');
        });
    }

    function abrirOferta(ofertaId, desdeProducto) {
        if (desdeProducto) {
            volverAProducto = true;
            volverAOferta = false;
            $('#modal-producto-expo').modal('hide');
        }
        $('#modal-oferta-titulo').text('Cargando oferta #' + ofertaId + '…');
        $.get('/reporte/expo/detalle-oferta', paramsActuales({ oferta_id: ofertaId })).then(function (data) {
            renderOferta(data);
            actualizarUrl({ oferta_id: ofertaId, producto_id: desdeProducto ? productoSeleccionado : null });
            $('#modal-oferta-expo').modal('show');
        }).fail(function () {
            alert('No fue posible cargar el detalle de la oferta.');
        });
    }

    function renderProducto(data) {
        var p = data.producto;
        var esFactura = data.rentabilidad_base === 'facturas';
        var entidad = esFactura ? 'Factura' : 'Oferta';
        productoSeleccionado = p.id;
        setText('modal-producto-titulo', (p.codigo ? p.codigo + ' · ' : '') + p.producto);
        setText('titulo-modal-producto-detalle', esFactura ? 'Facturación del producto por oferta' : 'Ofertas donde apareció');
        setText('th-modal-producto-cantidad', esFactura ? 'Cant. Facturada' : 'Cant. Ofertada');
        setText('th-modal-producto-precio', esFactura ? 'Precio factura' : 'Precio oferta');
        setText('th-modal-producto-subtotal', esFactura ? 'Subtotal factura' : 'Subtotal oferta');
        setText('th-modal-producto-total', esFactura ? 'ISV / Total factura' : 'ISV / Total oferta');
        setText('th-modal-producto-costo', 'Costo ' + entidad.toLowerCase());
        setText('th-modal-producto-utilidad', 'Utilidad / Margen ' + entidad.toLowerCase());
        $('#modal-producto-resumen').html(
            resumenItem('Marca', esc(p.marca)) + resumenItem('Categoría', esc(p.categoria)) +
            resumenItem('Cantidad ' + (esFactura ? 'facturada' : 'ofertada'), fmtQ(p.cantidad_base)) +
            resumenItem('Total ' + entidad, fmt(p.total_base)) +
            resumenItem('Descuento ' + entidad, fmt(p.descuento_base)) +
            resumenItem('Costo ' + entidad, fmt(p.costo_base)) +
            resumenItem(p.utilidad_base >= 0 ? 'Utilidad ' + entidad : 'Pérdida ' + entidad, (p.utilidad_base >= 0 ? '+ ' : '- ') + fmt(Math.abs(p.utilidad_base)), p.utilidad_base >= 0 ? 'bi-profit' : 'bi-loss') +
            resumenItem('Margen ' + entidad, p.margen_base_pct === null ? 'N/D' : p.margen_base_pct + '%') + resumenItem('Ofertas', fmtN(p.numero_ofertas))
        );

        var tbody = $('#modal-producto-ofertas tbody').empty();
        data.ofertas.forEach(function (o) {
            tbody.append('<tr class="bi-row-selectable" data-oferta-id="' + o.oferta_id + '" title="Abrir detalle de esta oferta">' +
                '<td><strong>#' + o.oferta_id + '</strong><br>Flujo ' + (o.flujo_id || '—') + '</td><td>' + esc(o.fecha) + '</td>' +
                '<td>' + esc(o.cliente) + '</td><td>' + esc(o.asesor) + '</td><td>' + esc(o.teleasesor) + '</td>' +
                '<td>' + esc(o.escala) + '</td><td class="text-right">' + fmtQ(o.cantidad_base) + '</td>' +
                '<td class="text-right">' + fmt(o.precio_base) + '</td><td class="text-right">' + fmt(o.precio_antes_descuento) + '</td>' +
                '<td class="text-right">' + fmt(o.descuento_base) + (esFactura ? '' : '<br><small>' + o.descuento_pct + '%</small>') + '</td>' +
                '<td class="text-right">' + fmt(o.precio_base_transaccion) + '</td><td class="text-right">' + fmt(o.total_base) + '</td>' +
                '<td class="text-right">' + fmt(o.isv_base) + '<br><strong>' + fmt(o.total_con_impuesto_base) + '</strong></td>' +
                '<td class="text-right">' + fmt(o.costo_base) + '</td><td class="text-right ' + (o.utilidad_base >= 0 ? 'bi-profit' : 'bi-loss') + '">' +
                (o.utilidad_base >= 0 ? '+ ' : '- ') + fmt(Math.abs(o.utilidad_base)) + '<br><small>' + (o.margen_base_pct === null ? 'N/D' : o.margen_base_pct + '%') + '</small></td>' +
                '<td>' + esc(etiquetaEstadoFacturacion(o.estado_facturacion)) + '</td></tr>');
        });
        tbody.off('click', 'tr').on('click', 'tr', function () {
            abrirOferta($(this).data('oferta-id'), true);
        });
    }

    function abrirProducto(productoId, desdeOferta) {
        if (desdeOferta) {
            volverAOferta = true;
            volverAProducto = false;
            $('#modal-oferta-expo').modal('hide');
        }
        $('#modal-producto-titulo').text('Cargando producto…');
        $.get('/reporte/expo/detalle-producto', paramsActuales({ producto_id: productoId })).then(function (data) {
            renderProducto(data);
            actualizarUrl({ producto_id: productoId, oferta_id: desdeOferta ? ofertaSeleccionada : null });
            $('#modal-producto-expo').modal('show');
        }).fail(function () {
            alert('No fue posible cargar la analítica del producto.');
        });
    }

    function exportarOfertaSeleccionada() {
        if (ofertaSeleccionada) window.location = '/reporte/expo/exportar-oferta?' + $.param({ oferta_id: ofertaSeleccionada });
    }

    function imprimirOfertaSeleccionada() {
        if (ofertaSeleccionada) window.open('/cotizacion/imprimir/' + ofertaSeleccionada, '_blank', 'noopener');
    }

    function abrirBuscadorProductos(origen) {
        origenBuscadorProductos = origen;
        var abrir = window.abrirBuscador_buscadorProductoReporteExpo;
        if (typeof abrir === 'function') abrir('', true);
    }

    function seleccionarProductoBuscador(producto) {
        if (!producto || !producto.id) return;
        abrirProducto(producto.id, origenBuscadorProductos === 'oferta');
    }

    function parametrosBuscadorProductos() {
        return paramsActuales();
    }

    /* ─────────────────────────── Exportar Excel ────────────────────────── */
    function exportarProductos() {
        window.location = '/reporte/expo/exportar-productos?' + $.param(paramsActuales());
    }
    function exportarOfertas() {
        window.location = '/reporte/expo/exportar-ofertas?' + $.param(paramsActuales());
    }

    /* ─────────────────────────── Orquestador ───────────────────────────── */
    function recargarTodo() {
        renderChipsFiltro();
        actualizarUrl();
        cargarKpis();
        cargarEstadoOfertas();
        cargarVentasPorMarca();
        cargarVentasPorAsesor();
        cargarVentasPorTeleasesor();
        cargarEvolucionDiaria();
        cargarTablaProductos();
        cargarTablaOfertas();
    }

    function init(expoIdInicial) {
        var query = new URLSearchParams(window.location.search);
        filtro.expo_id = query.get('expo_id') || expoIdInicial;
        filtro.marca_id = query.get('marca_id') || '';
        filtro.escala_id = query.get('escala_id') || '';
        filtro.vendedor_id = query.get('vendedor_id') || '';
        filtro.teleasesor_ids = query.getAll('teleasesor_ids[]');
        if (!filtro.teleasesor_ids.length && query.get('teleasesor_ids')) {
            filtro.teleasesor_ids = query.get('teleasesor_ids').split(',').filter(Boolean);
        }
        filtro.estado = query.get('estado') || '';
        filtro.fecha_desde = query.get('fecha_desde') || '';
        filtro.fecha_hasta = query.get('fecha_hasta') || '';
        filtro.rentabilidad_base = query.get('rentabilidad_base') === 'facturas' ? 'facturas' : 'ofertas';

        $('#expo-selector').val(filtro.expo_id);
        $('#expo-f-estado').val(filtro.estado);
        $('#expo-f-desde').val(filtro.fecha_desde);
        $('#expo-f-hasta').val(filtro.fecha_hasta);
        $('input[name="expo-f-rentabilidad-base"][value="' + filtro.rentabilidad_base + '"]').prop('checked', true).closest('label').addClass('active').siblings().removeClass('active');

        $('#modal-oferta-expo').on('hidden.bs.modal', function () {
            if (volverAOferta) return;
            if (volverAProducto && productoSeleccionado) {
                volverAProducto = false;
                actualizarUrl({ producto_id: productoSeleccionado, oferta_id: null });
                $('#modal-producto-expo').modal('show');
                return;
            }
            ofertaSeleccionada = null;
            actualizarUrl({ oferta_id: null, producto_id: productoSeleccionado });
        });
        $('#modal-producto-expo').on('hidden.bs.modal', function () {
            if (volverAProducto) return;
            if (volverAOferta && ofertaSeleccionada) {
                volverAOferta = false;
                actualizarUrl({ oferta_id: ofertaSeleccionada, producto_id: null });
                $('#modal-oferta-expo').modal('show');
                return;
            }
            productoSeleccionado = null;
            actualizarUrl({ producto_id: null, oferta_id: ofertaSeleccionada });
        });

        cargarCatalogoFiltros().then(function () {
            recargarTodo();
            var ofertaId = query.get('oferta_id');
            var productoId = query.get('producto_id');
            if (ofertaId) abrirOferta(ofertaId, false);
            else if (productoId) abrirProducto(productoId, false);
        });
    }

    return {
        init: init,
        aplicarFiltroDesdeForm: aplicarFiltroDesdeForm,
        limpiarFiltro: limpiarFiltro,
        recargarTodo: recargarTodo,
        exportarProductos: exportarProductos,
        exportarOfertas: exportarOfertas,
        exportarOfertaSeleccionada: exportarOfertaSeleccionada,
        imprimirOfertaSeleccionada: imprimirOfertaSeleccionada,
        abrirBuscadorProductos: abrirBuscadorProductos,
        seleccionarProductoBuscador: seleccionarProductoBuscador,
        parametrosBuscadorProductos: parametrosBuscadorProductos,
        abrirOferta: abrirOferta,
        abrirProducto: abrirProducto,
        cargarCatalogoFiltros: cargarCatalogoFiltros,
    };
})();
