/* ══════════════════════════════════════════════════════════════════════
   REPORTE BI DE EXPO — lógica de filtros, KPIs, gráficas (ApexCharts) y
   tablas (DataTables) con "click-to-filter" cruzado entre gráficas.
   Sigue el mismo patrón usado en dashboard-ventas.js: $.get() contra rutas
   dedicadas que devuelven JSON desde el componente Livewire ReporteExpo.
═════════════════════════════════════════════════════════════════════════ */
var reporteExpo = (function () {
    var charts = {};
    var dtProductos = null;
    var dtClientesOfertas = null;
    var ETIQUETAS_TIPO_OFERTA = {
        GANADORA: { texto: 'Ganadora', clase: 'badge-success' },
        NO_GANADORA: { texto: 'No ganadora', clase: 'badge-secondary' },
        NO_GANADORA_CON_SECCIONES: { texto: 'No ganadora con secciones', clase: 'badge-warning' }
    };
    var ofertaSeleccionada = null;
    var productoSeleccionado = null;
    var volverAProducto = false;
    var volverAOferta = false;
    var volverASecciones = false;
    var flujoSeleccionado = null;
    var ofertaAbiertaDesdeSecciones = false;
    var origenBuscadorProductos = null;
    var filtroNombres = {};

    /* Filtro activo compartido entre todas las gráficas/tablas */
    var filtro = {
        expo_id: null,
        marca_id: '',
        escala_id: '',
        vendedor_id: '',
        teleasesor_ids: [],
        cliente_id: '',
        estado: '',
        tipo_oferta: '',
        estado_entrega: '',
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

    function etiquetaEstadoSeccion(estado) {
        return ({
            EN_REVISION: 'En revisión',
            EN_REVISION_CREDITO: 'Revisión de crédito',
            EN_REVISION_INVENTARIO: 'Revisión de inventario',
            PREFACTURADA: 'Prefacturada',
            FACTURADA: 'Facturada',
            RECHAZADA_CREDITO: 'Rechazada en crédito',
            DEVUELTA_INVENTARIO: 'Devuelta de inventario',
            DEVUELTA_SECCION: 'Devuelta',
            ANULADA: 'Anulada',
        })[estado] || String(estado || '').replaceAll('_', ' ');
    }

    function clasesEstadoSeccion(estado) {
        if (estado === 'ANULADA' || estado === 'DEVUELTA_INVENTARIO' || estado === 'DEVUELTA_SECCION' || estado === 'RECHAZADA_CREDITO') {
            return 'bi-seccion-danger';
        }
        if (estado === 'FACTURADA' || estado === 'PREFACTURADA') {
            return 'bi-seccion-success';
        }
        return '';
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

    var ESTADOS_ENTREGA = {
        ENTREGADA: { texto: 'Entregada', grafica: 'Distribuidas', clase: 'badge-success', color: '#15906b', campo: 'num_entregadas' },
        PARCIALMENTE_DISTRIBUIDA: { texto: 'Parcialmente distribuida', grafica: 'Parcialmente distribuidas', clase: 'badge-warning', color: '#f0ad4e', campo: 'num_parciales' },
        NO_ENTREGADA: { texto: 'No entregada', grafica: 'Sin distribuir', clase: 'badge-danger', color: '#dc3545', campo: 'num_sin_entregar' },
    };

    function badgeEntrega(estado, r) {
        var e = ESTADOS_ENTREGA[estado];
        if (!e) return '<span class="text-muted" title="Sin facturas">—</span>';
        var detalle = r && r.num_facturas_entrega
            ? 'Facturas: ' + fmtN(r.num_entregadas) + ' distribuidas, ' + fmtN(r.num_parciales) + ' parciales, ' + fmtN(r.num_sin_entregar) + ' sin distribuir'
            : '';
        return '<span class="badge ' + e.clase + '" title="' + esc(detalle) + '">' + esc(e.texto) + '</span>' +
            (detalle ? ' <small class="text-muted">' + fmtN(r.num_entregadas) + '/' + fmtN(r.num_facturas_entrega) + '</small>' : '');
    }

    /* ─────────── Gráfica: entrega de facturas (dona, clic para filtrar) ──── */
    // Cuenta facturas; al hacer clic filtra las ofertas que tengan al menos
    // una factura en ese estado.
    function pintarGraficaEntregas(f) {
        destroyChart('chart-entregas-expo');
        var contenedor = get('chart-entregas-expo');
        if (!contenedor) return;
        $(contenedor).empty();
        var g = f.conjunto;
        var claves = Object.keys(ESTADOS_ENTREGA);
        if (!g.num_facturas_entrega) {
            $(contenedor).html('<div class="text-center text-muted py-5">Sin facturas para los filtros seleccionados.</div>');
            return;
        }

        charts['chart-entregas-expo'] = new ApexCharts(contenedor, {
            chart: {
                type: 'donut', height: 320,
                events: {
                    dataPointSelection: function (event, ctx, config) {
                        var estado = claves[config.dataPointIndex];
                        filtro.estado_entrega = filtro.estado_entrega === estado ? '' : estado;
                        if (filtro.estado_entrega) filtroNombres.estado_entrega = ESTADOS_ENTREGA[estado].grafica;
                        else delete filtroNombres.estado_entrega;
                        recargarTodo();
                    }
                }
            },
            series: claves.map(function (k) { return g[ESTADOS_ENTREGA[k].campo] || 0; }),
            labels: claves.map(function (k) { return ESTADOS_ENTREGA[k].grafica; }),
            colors: claves.map(function (k) { return ESTADOS_ENTREGA[k].color; }),
            legend: {
                position: 'bottom',
                formatter: function (nombre, opts) {
                    var valor = opts.w.globals.series[opts.seriesIndex];
                    return nombre + ': ' + fmtN(valor) + ' (' + (valor * 100 / g.num_facturas_entrega).toFixed(1) + '%)';
                }
            },
            dataLabels: { enabled: true, formatter: function (pct) { return pct.toFixed(1) + '%'; }, dropShadow: { enabled: false } },
            tooltip: { y: { formatter: function (valor) { return fmtN(valor) + ' facturas'; } } },
            plotOptions: { pie: { donut: { labels: { show: true, total: {
                show: true, label: fmtN(g.num_facturas_entrega) + ' facturas',
                formatter: function () { return g.entrega_pct + '% entregado'; }
            } } } } }
        });
        charts['chart-entregas-expo'].render();
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
            cliente_id: 'Cliente',
            estado: 'Estado',
            tipo_oferta: 'Tipo',
            estado_entrega: 'Entrega',
        };
        var campos = {
            marca_id: '#expo-f-marca', escala_id: '#expo-f-escala', vendedor_id: '#expo-f-vendedor',
            teleasesor_ids: '#expo-f-teleasesor', estado: '#expo-f-estado', tipo_oferta: '#expo-f-tipo-oferta'
        };
        var html = '';
        Object.keys(etiquetas).forEach(function (key) {
            var activo = Array.isArray(filtro[key]) ? filtro[key].length : filtro[key];
            if (activo) {
                var texto = campos[key]
                    ? ($(campos[key] + ' option:selected').map(function () { return $(this).text(); }).get().join(', ') || filtro[key])
                    : (filtroNombres[key] || filtro[key]);
                html += '<span class="badge badge-primary mr-1 mb-1" style="cursor:pointer" ' +
                        'onclick="reporteExpo.limpiarFiltro(\'' + key + '\')">' +
                        etiquetas[key] + ': ' + texto + ' &times;</span>';
            }
        });
        cont.innerHTML = html;
    }

    function limpiarFiltro(key) {
        filtro[key] = key === 'teleasesor_ids' ? [] : '';
        delete filtroNombres[key];
        var mapId = { marca_id: 'expo-f-marca', escala_id: 'expo-f-escala', vendedor_id: 'expo-f-vendedor', teleasesor_ids: 'expo-f-teleasesor', estado: 'expo-f-estado', tipo_oferta: 'expo-f-tipo-oferta' };
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
        filtro.tipo_oferta = $('#expo-f-tipo-oferta').val() || '';
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

    /* ──────────────── Gráfica: ofertas ganadoras vs no ganadoras (pie) ──── */
    // Se pinta con los segmentos de resumen-ofertas. "No ganadoras" ya incluye
    // las que perdieron la marca pero conservan secciones.
    function pintarGraficaTipos(f) {
        destroyChart('chart-estado');
        if (!get('chart-estado')) return;
        $('#chart-estado').empty();
        var conSecciones = f.no_ganadoras_con_secciones || { num_ofertas: 0, num_secciones: 0 };
        var nota = conSecciones.num_ofertas
            ? 'Incluye ' + fmtN(conSecciones.num_ofertas) + ' ofertas con ' + fmtN(conSecciones.num_secciones) + ' secciones'
            : '';
        var datos = [
            { tipo: 'GANADORA', nombre: 'Ofertas ganadoras', ofertas: f.ganadoras.num_ofertas,
              ofertado: f.ganadoras.total_ofertado, facturado: f.ganadoras.total_facturado, nota: '' },
            { tipo: 'NO_GANADORA', nombre: 'Ofertas no ganadoras', ofertas: f.no_ganadoras.num_ofertas,
              ofertado: f.no_ganadoras.total_ofertado, facturado: f.no_ganadoras.total_facturado, nota: nota },
        ];
        setText('chart-estado-nota', nota ? 'Ofertas no ganadoras: ' + nota.charAt(0).toLowerCase() + nota.slice(1) +
            ' (dejaron de ser ganadoras pero conservan secciones).' : '');
        var total = datos[0].ofertas + datos[1].ofertas;
        if (!total) {
            $('#chart-estado').html('<div class="text-center text-muted py-5">Sin ofertas para los filtros seleccionados.</div>');
            return;
        }
        charts['chart-estado'] = new ApexCharts(get('chart-estado'), {
            chart: {
                type: 'pie', height: 320,
                events: {
                    dataPointSelection: function (event, ctx, config) {
                        var tipo = datos[config.dataPointIndex].tipo;
                        filtro.tipo_oferta = filtro.tipo_oferta === tipo ? '' : tipo;
                        $('#expo-f-tipo-oferta').val(filtro.tipo_oferta);
                        recargarTodo();
                    }
                }
            },
            series: datos.map(function (d) { return d.ofertas; }),
            labels: datos.map(function (d) { return d.nombre; }),
            colors: ['#15906b', '#94a3b8'],
            legend: {
                position: 'bottom',
                formatter: function (nombre, opts) {
                    var d = datos[opts.seriesIndex];
                    return nombre + ': ' + fmtN(d.ofertas) + ' (' + (d.ofertas * 100 / total).toFixed(1) + '%)';
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (pct) { return pct.toFixed(1) + '%'; },
                style: { fontSize: '14px', fontWeight: 600 },
                dropShadow: { enabled: false }
            },
            tooltip: {
                custom: function (ctx) {
                    var d = datos[ctx.seriesIndex];
                    return '<div style="background:#fff;color:#1f2937;padding:8px 10px;font-size:12px;line-height:1.5;border-left:4px solid ' +
                        (ctx.seriesIndex === 0 ? '#15906b' : '#94a3b8') + ';"><strong>' + esc(d.nombre) + '</strong><br>' +
                        'Ofertas: ' + fmtN(d.ofertas) + ' (' + (d.ofertas * 100 / total).toFixed(1) + '%)' +
                        '<br>Ofertado: ' + fmt(d.ofertado) + '<br>Facturado: ' + fmt(d.facturado) +
                        (d.nota ? '<br><small style="color:#6b7280;">' + esc(d.nota) + '</small>' : '') + '</div>';
                }
            },
        });
        charts['chart-estado'].render();
    }

    /* ─────────────────────────── Gráfica: Ventas por marca (bar) ──────── */
    function cargarVentasPorMarca() {
        return $.get('/reporte/expo/ventas-por-marca', paramsActuales()).then(function (rows) {
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
                    { name: 'Ofertado sin ISV', data: top.map(function (r) { return r.ofertado; }) },
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
        return $.get('/reporte/expo/ventas-por-asesor', paramsActuales()).then(function (rows) {
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
                    { name: 'Ofertado sin ISV', data: top.map(function (r) { return r.ofertado; }) },
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
        return $.get('/reporte/expo/ventas-por-teleasesor', paramsActuales()).then(function (rows) {
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
                    { name: 'Ofertado sin ISV', data: top.map(function (r) { return r.ofertado; }) },
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

    function opcionesTopHorizontal(elemento, rows, etiqueta, alSeleccionar) {
        return {
            chart: {
                type: 'bar',
                height: 390,
                events: { dataPointSelection: alSeleccionar }
            },
            series: [
                { name: 'Ofertado sin ISV', data: rows.map(function (r) { return r.ofertado; }) },
                { name: 'Facturado', data: rows.map(function (r) { return r.facturado; }) },
            ],
            xaxis: {
                categories: rows.map(etiqueta),
                labels: { formatter: function (v) { return 'L.' + fmtN(v); } }
            },
            tooltip: { shared: false, intersect: true, y: { formatter: function (v) { return fmt(v); } } },
            dataLabels: { enabled: false },
            colors: ['#2878a9', '#15906b'],
            legend: { position: 'top' },
            plotOptions: { bar: { horizontal: true, barHeight: '64%' } },
            noData: { text: 'Sin datos para los filtros seleccionados' }
        };
    }

    function cargarTopClientes() {
        return $.get('/reporte/expo/top-clientes', paramsActuales()).then(function (rows) {
            destroyChart('chart-top-clientes');
            var elemento = get('chart-top-clientes');
            if (!elemento) return;

            charts['chart-top-clientes'] = new ApexCharts(elemento, opcionesTopHorizontal(
                elemento,
                rows,
                function (r) { return r.cliente; },
                function (event, ctx, config) {
                    var row = rows[config.dataPointIndex];
                    if (!row || !row.cliente_id) return;
                    var mismo = String(filtro.cliente_id) === String(row.cliente_id);
                    filtro.cliente_id = mismo ? '' : row.cliente_id;
                    if (mismo) delete filtroNombres.cliente_id;
                    else filtroNombres.cliente_id = row.cliente;
                    recargarTodo();
                }
            ));
            charts['chart-top-clientes'].render();
        });
    }

    function cargarTopProductos() {
        return $.get('/reporte/expo/top-productos', paramsActuales()).then(function (rows) {
            destroyChart('chart-top-productos');
            var elemento = get('chart-top-productos');
            if (!elemento) return;

            charts['chart-top-productos'] = new ApexCharts(elemento, opcionesTopHorizontal(
                elemento,
                rows,
                function (r) { return r.codigo ? r.codigo + ' · ' + r.producto : r.producto; },
                function (event, ctx, config) {
                    var row = rows[config.dataPointIndex];
                    if (row && row.producto_id) abrirProducto(row.producto_id, false);
                }
            ));
            charts['chart-top-productos'].render();
        });
    }

    /* ─────────────────────────── Gráfica: Evolución diaria (line) ─────── */
    function cargarEvolucionDiaria() {
        return $.get('/reporte/expo/evolucion-diaria', paramsActuales()).then(function (rows) {
            destroyChart('chart-evolucion-expo');
            if (!get('chart-evolucion-expo')) return;

            charts['chart-evolucion-expo'] = new ApexCharts(get('chart-evolucion-expo'), {
                chart: { type: 'line', height: 320, toolbar: { show: true } },
                series: [
                    { name: 'Oferta sin ISV', data: rows.map(function (r) { return r.ofertado; }) },
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
        return $.get('/reporte/expo/tabla-productos', paramsActuales()).then(function (rows) {
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
                var cantidad = esFactura ? r.cantidad_facturada : r.cantidad_ofertada;
                tbody.append('<tr class="bi-row-selectable" data-producto-id="' + r.producto_id + '" title="Abrir analítica del producto">' +
                    '<td>' + esc(r.codigo) + '</td>' +
                    '<td>' + esc(r.producto) + '</td>' +
                    '<td>' + esc(r.marca) + '</td>' +
                    '<td>' + esc(r.categoria) + '</td>' +
                    '<td class="text-right" data-order="' + r.numero_ofertas + '">' + fmtN(r.numero_ofertas) + '</td>' +
                    '<td class="text-right" data-order="' + cantidad + '">' + fmtQ(cantidad) + '</td>' +
                    '<td class="text-right" data-order="' + r.total_base + '">' + fmt(r.total_base) + '</td>' +
                    '<td class="text-right" data-order="' + r.descuento + '">' + fmt(r.descuento) + '</td>' +
                    '<td class="text-right" data-order="' + r.total_costo + '">' + fmt(r.total_costo) + '</td>' +
                    '<td class="text-right ' + (r.utilidad >= 0 ? 'bi-profit' : 'bi-loss') + '" data-order="' + r.utilidad + '">' + (r.utilidad >= 0 ? '+' : '-') + ' ' + fmt(Math.abs(r.utilidad)) + '</td>' +
                    '<td class="text-right" data-order="' + (r.margen_pct !== null ? r.margen_pct : '') + '">' + (r.margen_pct !== null ? r.margen_pct + '%' : 'N/D') + '</td>' +
                    '</tr>');
            });
            // El idioma español define la coma como decimal; se fuerza el tipo
            // numérico para que ordene por el valor crudo de data-order.
            dtProductos = $('#tabla-expo-productos').DataTable({
                pageLength: 10, lengthChange: true, order: [],
                columnDefs: [{ targets: [4, 5, 6, 7, 8, 9, 10], type: 'num' }],
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' }
            });
            $('#tabla-expo-productos tbody').off('click', 'tr').on('click', 'tr', function () {
                var id = $(this).data('producto-id');
                if (id) abrirProducto(id, false);
            });
        });
    }

    /* ──────────── Tabla unificada: cliente → ofertas → secciones (modal) ──── */
    var ofertasPorClave = {};
    var ofertasPorCliente = {};
    var SEGMENTOS_EXPO = ['ganadoras', 'no_ganadoras', 'conjunto'];
    var KPIS_SEGMENTO = ['num_ofertas', 'num_secciones', 'num_facturas', 'total_ofertado', 'total_facturado',
        'avance_pct', 'utilidad_a_ganar', 'total_pagado', 'utilidad_pagada',
        'num_entregadas', 'num_sin_entregar', 'num_parciales', 'entrega_pct'];

    // Misma llave que usa el backend para agrupar clientes en resumen-ofertas.
    function claveCliente(clienteId, nombre) {
        return clienteId !== null && clienteId !== undefined
            ? 'id_' + clienteId
            : 'nombre_' + String(nombre || '').trim().toUpperCase();
    }

    function pintarSegmento(clave, g) {
        var id = function (campo) { return 'seg-' + clave + '-' + campo; };
        setText(id('num_ofertas'), fmtN(g.num_ofertas));
        setText(id('num_secciones'), fmtN(g.num_secciones));
        setText(id('num_facturas'), fmtN(g.num_facturas));
        setText(id('total_ofertado'), fmt(g.total_ofertado));
        setText(id('total_facturado'), fmt(g.total_facturado));
        setText(id('avance_pct'), g.avance_pct + '%');
        setText(id('avance_pct-det'), 'facturado ÷ ofertado');
        setText(id('utilidad_a_ganar'), fmt(g.utilidad_a_ganar));
        setText(id('utilidad_a_ganar-det'), 'Facturada: ' + fmt(g.utilidad_facturada));
        setText(id('total_pagado'), fmt(g.total_pagado));
        setText(id('total_pagado-det'), g.pagado_pct + '% de lo facturado');
        setText(id('utilidad_pagada'), fmt(g.utilidad_pagada));
        setText(id('utilidad_pagada-det'), g.utilidad_ganada_pct + '% de la utilidad a ganar');
        var pctDe = function (n) { return g.num_facturas_entrega ? (n * 100 / g.num_facturas_entrega).toFixed(1) + '% de las facturas' : ''; };
        setText(id('num_entregadas'), fmtN(g.num_entregadas));
        setText(id('num_entregadas-det'), pctDe(g.num_entregadas));
        setText(id('num_sin_entregar'), fmtN(g.num_sin_entregar));
        setText(id('num_sin_entregar-det'), pctDe(g.num_sin_entregar));
        setText(id('num_parciales'), fmtN(g.num_parciales));
        setText(id('num_parciales-det'), pctDe(g.num_parciales));
        setText(id('entrega_pct'), g.entrega_pct + '%');
        setText(id('entrega_pct-det'), fmtN(g.num_entregadas) + ' de ' + fmtN(g.num_facturas_entrega) + ' facturas');
    }

    function pintarSegmentos(f) {
        SEGMENTOS_EXPO.forEach(function (clave) { pintarSegmento(clave, f[clave]); });
        // Con un tipo seleccionado solo se muestra su segmento (el resto
        // quedaría en cero o repetiría los mismos números).
        var visible = { GANADORA: 'ganadoras', NO_GANADORA: 'no_ganadoras', NO_GANADORA_CON_SECCIONES: 'no_ganadoras' }[filtro.tipo_oferta];
        SEGMENTOS_EXPO.forEach(function (clave) { $('#seg-' + clave).toggle(!visible || visible === clave); });
        var otras = f.no_ganadoras_con_secciones;
        setText('kpi-fact-otras', otras.num_ofertas
            ? 'Incluye ' + fmtN(otras.num_ofertas) + ' ofertas que dejaron de ser ganadoras pero conservan ' +
                fmtN(otras.num_secciones) + ' secciones (' + fmtN(otras.num_facturas) + ' facturas, ' +
                fmt(otras.total_facturado) + ' facturado de ' + fmt(otras.total_ofertado) + ').'
            : '');
    }

    function htmlOfertasCliente(clave) {
        var lista = ofertasPorCliente[clave] || [];
        if (!lista.length) return '<div class="bi-ofertas-cliente text-muted">Sin ofertas para este cliente.</div>';
        var filas = lista.map(function (r) {
            var utilidadClase = r.utilidad >= 0 ? 'bi-profit' : 'bi-loss';
            var tipo = ETIQUETAS_TIPO_OFERTA[r.tipo] || { texto: r.tipo, clase: 'badge-light' };
            var claseFact = r.estado_facturacion === 'FACTURADA' ? 'badge-success' : (r.estado_facturacion === 'PARCIALMENTE_FACTURADA' ? 'badge-warning' : 'badge-secondary');
            return '<tr class="bi-row-selectable bi-fila-oferta" data-clave="oferta-' + r.oferta_id + '" title="Ver secciones de esta oferta">' +
                '<td>' + r.oferta_id + '</td>' +
                '<td><span class="badge ' + tipo.clase + '">' + esc(tipo.texto) + '</span></td>' +
                '<td class="text-right">' + fmtN(r.num_secciones) + '</td>' +
                '<td>' + (r.flujo_id || '—') + '</td>' +
                '<td>' + esc(r.asesor) + '</td>' +
                '<td>' + esc(r.teleasesor) + '</td>' +
                '<td>' + esc(r.fecha_emision || '') + '</td>' +
                '<td><span class="badge ' + claseFact + '">' + esc(etiquetaEstadoFacturacion(r.estado_facturacion)) + '</span></td>' +
                '<td class="text-right">' + fmtN(r.num_facturas) + '</td>' +
                '<td class="text-right">' + fmt(r.total_ofertado) + '</td>' +
                '<td class="text-right">' + fmt(r.total_facturado) + '</td>' +
                '<td class="text-right">' + r.avance_pct + '%</td>' +
                '<td class="text-right font-weight-bold ' + utilidadClase + '">' + fmt(r.utilidad) + '</td>' +
                '<td class="text-right ' + utilidadClase + '">' + (r.margen_pct !== null ? r.margen_pct + '%' : 'N/D') + '</td>' +
                '<td class="text-right">' + fmt(r.total_pagado) + '</td>' +
                '<td class="text-right">' + fmt(r.utilidad_pagada) + '</td>' +
                '<td>' + badgeEntrega(r.estado_entrega, r) + '</td>' +
                '</tr>';
        }).join('');
        return '<div class="bi-ofertas-cliente"><table class="table table-sm table-bordered table-hover tabla-ofertas-cliente">' +
            '<thead class="thead-light"><tr><th>Oferta #</th><th>Tipo</th><th class="text-right"># Secciones</th><th>Flujo</th>' +
            '<th>Asesor</th><th>Teleasesor</th><th>Fecha</th><th>Facturación</th><th class="text-right">Facturas</th>' +
            '<th class="text-right">Ofertado</th><th class="text-right">Facturado</th><th class="text-right">Avance %</th>' +
            '<th class="text-right">Utilidad a ganar</th><th class="text-right">Margen %</th><th class="text-right">Pagado</th>' +
            '<th class="text-right">Utilidad ganada</th><th>Entrega</th></tr></thead><tbody>' + filas + '</tbody></table></div>';
    }

    function cargarOfertasPorCliente() {
        SEGMENTOS_EXPO.forEach(function (clave) {
            KPIS_SEGMENTO.forEach(function (campo) {
                setText('seg-' + clave + '-' + campo, '…');
                setText('seg-' + clave + '-' + campo + '-det', '');
            });
        });
        setText('kpi-fact-otras', '');

        return $.get('/reporte/expo/resumen-ofertas', paramsActuales()).then(function (d) {
            var rows = d.ofertas || [];
            pintarSegmentos(d.facturabilidad);
            pintarGraficaTipos(d.facturabilidad);
            pintarGraficaEntregas(d.facturabilidad);

            ofertasPorClave = {};
            ofertasPorCliente = {};
            rows.forEach(function (r) {
                ofertasPorClave['oferta-' + r.oferta_id] = r;
                var clave = claveCliente(r.cliente_id, r.cliente);
                (ofertasPorCliente[clave] = ofertasPorCliente[clave] || []).push(r);
            });

            if (dtClientesOfertas) { dtClientesOfertas.destroy(); }
            var tbody = $('#tabla-expo-clientes-ofertas tbody').empty();
            d.clientes.forEach(function (c) {
                var clave = claveCliente(c.cliente_id, c.cliente);
                // Permite buscar el cliente por número de oferta, flujo, asesor o teleasesor.
                var busqueda = [c.cliente || 'Sin nombre'].concat((ofertasPorCliente[clave] || []).map(function (r) {
                    return [r.oferta_id, r.flujo_id || '', r.asesor || '', r.teleasesor || ''].join(' ');
                })).join(' ');
                var utilidadClase = c.utilidad_a_ganar >= 0 ? 'bi-profit' : 'bi-loss';
                tbody.append('<tr class="bi-row-selectable bi-fila-cliente" data-cliente="' + esc(clave) + '" title="Ver ofertas de este cliente">' +
                    '<td class="text-center bi-toggle"><i class="fas fa-chevron-right"></i></td>' +
                    '<td data-search="' + esc(busqueda) + '">' + esc(c.cliente || 'Sin nombre') + '</td>' +
                    '<td class="text-right font-weight-bold" data-order="' + c.num_ofertas + '">' + fmtN(c.num_ofertas) + '</td>' +
                    '<td class="text-right" data-order="' + c.ganadoras + '">' + fmtN(c.ganadoras) + '</td>' +
                    '<td class="text-right" data-order="' + c.no_ganadoras + '">' + fmtN(c.no_ganadoras) + '</td>' +
                    '<td class="text-right" data-order="' + c.num_secciones + '">' + fmtN(c.num_secciones) + '</td>' +
                    '<td class="text-right" data-order="' + c.total_ofertado + '">' + fmt(c.total_ofertado) + '</td>' +
                    '<td class="text-right" data-order="' + c.total_facturado + '">' + fmt(c.total_facturado) + '</td>' +
                    '<td class="text-right" data-order="' + c.facturabilidad_pct + '">' + c.facturabilidad_pct + '%</td>' +
                    '<td class="text-right font-weight-bold ' + utilidadClase + '" data-order="' + c.utilidad_a_ganar + '">' + fmt(c.utilidad_a_ganar) + '</td>' +
                    '<td class="text-right" data-order="' + c.total_pagado + '">' + fmt(c.total_pagado) + '</td>' +
                    '<td class="text-right" data-order="' + c.utilidad_pagada + '">' + fmt(c.utilidad_pagada) + '</td>' +
                    '<td class="text-right text-success" data-order="' + c.num_entregadas + '">' + fmtN(c.num_entregadas) + '</td>' +
                    '<td class="text-right text-danger" data-order="' + c.num_sin_entregar + '">' + fmtN(c.num_sin_entregar) + '</td>' +
                    '<td class="text-right text-warning" data-order="' + c.num_parciales + '">' + fmtN(c.num_parciales) + '</td>' +
                    '<td class="text-right font-weight-bold" data-order="' + (c.num_facturas_entrega ? c.entrega_pct : -1) + '">' +
                        (c.num_facturas_entrega ? c.entrega_pct + '%' : '<span class="text-muted">—</span>') + '</td>' +
                    '</tr>');
            });
            dtClientesOfertas = $('#tabla-expo-clientes-ofertas').DataTable({
                pageLength: 10, lengthChange: true, order: [],
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false },
                    { targets: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15], type: 'num' }
                ],
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' }
            });

            $('#tabla-expo-clientes-ofertas tbody').off('click')
                .on('click', 'tr.bi-fila-cliente', function () {
                    var tr = $(this);
                    var fila = dtClientesOfertas.row(tr);
                    if (fila.child.isShown()) {
                        fila.child.hide();
                        tr.removeClass('bi-cliente-abierto');
                    } else {
                        fila.child(htmlOfertasCliente(tr.data('cliente'))).show();
                        tr.addClass('bi-cliente-abierto');
                    }
                })
                .on('click', 'tr.bi-fila-oferta', function (e) {
                    e.stopPropagation();
                    var oferta = ofertasPorClave[$(this).data('clave')];
                    if (oferta) abrirSecciones(oferta);
                });
        });
    }

    /* ───────────────────── Modal: secciones del flujo ──────────────────── */
    function abrirSecciones(oferta) {
        flujoSeleccionado = oferta;
        setText('modal-secciones-titulo', 'Flujo ' + (oferta.flujo_id || '—') + ' · ' + oferta.cliente);
        var estadoClase = oferta.estado_facturacion === 'FACTURADA' ? 'bi-profit' : (oferta.estado_facturacion === 'PARCIALMENTE_FACTURADA' ? 'text-warning' : '');
        var tipoOferta = ETIQUETAS_TIPO_OFERTA[oferta.tipo] || { texto: oferta.tipo || '—' };
        $('#modal-secciones-resumen').html(
            resumenItem('Oferta', '#' + oferta.oferta_id) +
            resumenItem('Tipo', esc(tipoOferta.texto)) +
            resumenItem('Secciones', fmtN(oferta.num_secciones)) +
            resumenItem('Facturas', fmtN(oferta.num_facturas)) +
            resumenItem('Total ofertado', fmt(oferta.total_ofertado)) +
            resumenItem('Total facturado', fmt(oferta.total_facturado)) +
            resumenItem('Facturación', etiquetaEstadoFacturacion(oferta.estado_facturacion), estadoClase) +
            resumenItem('Entrega', badgeEntrega(oferta.estado_entrega, oferta))
        );

        var tbody = $('#tabla-secciones-flujo tbody').empty();
        if (!oferta.secciones || !oferta.secciones.length) {
            tbody.append('<tr><td colspan="8" class="text-center text-muted py-3">No hay secciones registradas para esta oferta.</td></tr>');
        } else {
            oferta.secciones.forEach(function (s) {
                var esPendiente = !!s.es_pendiente;
                var esRaiz = !!s.es_raiz;
                var estadoTxt = etiquetaEstadoFacturacion(s.estado_facturacion);
                var fila = $('<tr>');
                if (esPendiente) {
                    fila.addClass('bi-seccion-pendiente').attr('title', 'Monto aún sin facturar');
                } else {
                    fila.addClass('bi-row-selectable').attr('data-oferta-id', s.oferta_id)
                        .attr('title', esRaiz ? 'Ver productos propios de la oferta' : 'Abrir detalle de esta sección');
                    var claseEstado = clasesEstadoSeccion(s.estado);
                    if (claseEstado) fila.addClass(claseEstado);
                    if (esRaiz) fila.addClass('bi-seccion-raiz');
                }
                var etiquetaSeccion = esRaiz
                    ? '<i class="fas fa-box mr-1"></i>Productos sin sección'
                    : (esPendiente ? '<i class="fas fa-exclamation-circle mr-1"></i>Productos sin factura'
                        : 'Sección ' + s.numero_seccion);
                fila.html(
                    '<td>' + etiquetaSeccion + '</td>' +
                    '<td>' + (s.estado ? esc(etiquetaEstadoSeccion(s.estado)) : '<span class="text-muted">—</span>') + '</td>' +
                    '<td><span class="badge ' + (s.estado_facturacion === 'FACTURADA' ? 'badge-success' : (s.estado_facturacion === 'PARCIALMENTE_FACTURADA' ? 'badge-warning' : 'badge-secondary')) + '">' + esc(estadoTxt) + '</span></td>' +
                    '<td class="text-right">' + fmt(s.total_ofertado) + '</td>' +
                    '<td class="text-right">' + fmt(s.total_facturado) + '</td>' +
                    '<td class="text-right">' + (esPendiente ? '—' : fmt(s.total_pagado)) + '</td>' +
                    '<td class="text-right ' + ((s.utilidad_pagada || 0) < 0 ? 'bi-loss' : 'bi-profit') + '">' + (esPendiente ? '—' : fmt(s.utilidad_pagada)) + '</td>' +
                    '<td>' + (esPendiente ? '<span class="text-muted">—</span>' : badgeEntrega(s.estado_entrega)) + '</td>'
                );
                tbody.append(fila);
            });
        }
        $('#tabla-secciones-flujo tbody').off('click', 'tr').on('click', 'tr.bi-row-selectable', function () {
            var id = $(this).data('oferta-id');
            if (!id) return;
            $('#modal-secciones-flujo').modal('hide');
            abrirOferta(id, false, true);
        });

        $('#modal-secciones-flujo').modal('show');
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
            infoOferta('Sección', o.nombre_seccion ? o.nombre_seccion + ' (#' + o.numero_seccion + ')' : 'Oferta principal') +
            infoOferta('Oferta origen', o.oferta_origen_id ? '#' + o.oferta_origen_id : '—') +
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
            var estadoEnvio = {
                no_enviada: { texto: 'Sin envío', clase: 'badge-secondary' },
                sin_entrega: { texto: 'Enviada · pendiente', clase: 'badge-primary' },
                parcial: { texto: 'Entrega parcial', clase: 'badge-warning' },
                entregado: { texto: 'Entregada', clase: 'badge-success' }
            }[f.envio_estado] || { texto: 'Sin envío', clase: 'badge-secondary' };
            var detalleEnvio = f.envio_estado === 'no_enviada'
                ? 'Sin envío asignado'
                : 'Envío: ' + esc(estadoEnvio.texto) +
                    (f.envio_equipo ? ' · ' + esc(f.envio_equipo) : '') +
                    (f.envio_fecha_programada ? ' · ' + esc(f.envio_fecha_programada) : '');
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
                '<span class="ml-auto badge ' + estadoEnvio.clase + '">' + esc(estadoEnvio.texto) + '</span></div>' +
                '<div id="' + collapseId + '" class="collapse ' + (indice === 0 ? 'show' : '') + '"><div class="card-body p-2">' +
                '<div class="small text-muted mb-2">' + detalleEnvio + '</div>' +
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

    function abrirOferta(ofertaId, desdeProducto, desdeSecciones) {
        ofertaAbiertaDesdeSecciones = !!desdeSecciones;
        if (desdeProducto) {
            volverAProducto = true;
            volverAOferta = false;
            $('#modal-producto-expo').modal('hide');
        }
        $('#modal-oferta-titulo').text('Cargando oferta #' + ofertaId + '…');
        $.get('/reporte/expo/detalle-oferta', paramsActuales({ oferta_id: ofertaId })).then(function (data) {
            renderOferta(data);
            $('#btn-modal-oferta-volver-secciones').toggle(ofertaAbiertaDesdeSecciones && !!flujoSeleccionado);
            actualizarUrl({ oferta_id: ofertaId, producto_id: desdeProducto ? productoSeleccionado : null });
            $('#modal-oferta-expo').modal('show');
        }).fail(function () {
            alert('No fue posible cargar el detalle de la oferta.');
        });
    }

    function volverASeccionesDesdeOferta() {
        if (!flujoSeleccionado) return;
        volverASecciones = true;
        $('#modal-oferta-expo').modal('hide');
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
    var spinnerPendientes = 0;
    var spinnerTimeoutId = null;
    function mostrarSpinnerCarga() {
        spinnerPendientes++;
        $('#reporte-expo-spinner').addClass('show');
        clearTimeout(spinnerTimeoutId);
        // Salvaguarda: si alguna petición nunca responde, no dejar el overlay bloqueado.
        spinnerTimeoutId = setTimeout(function () {
            spinnerPendientes = 0;
            $('#reporte-expo-spinner').removeClass('show');
        }, 20000);
    }
    function ocultarSpinnerCarga() {
        spinnerPendientes = Math.max(0, spinnerPendientes - 1);
        if (spinnerPendientes === 0) {
            clearTimeout(spinnerTimeoutId);
            $('#reporte-expo-spinner').removeClass('show');
        }
    }

    function recargarTodo() {
        renderChipsFiltro();
        actualizarUrl();
        mostrarSpinnerCarga();
        var promesas = [
            cargarVentasPorMarca(),
            cargarVentasPorAsesor(),
            cargarVentasPorTeleasesor(),
            cargarTopClientes(),
            cargarTopProductos(),
            cargarEvolucionDiaria(),
            cargarTablaProductos(),
            cargarOfertasPorCliente(),
        ];
        $.when.apply($, promesas).always(ocultarSpinnerCarga);
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
        filtro.cliente_id = query.get('cliente_id') || '';
        if (filtro.cliente_id) filtroNombres.cliente_id = 'Cliente #' + filtro.cliente_id;
        filtro.estado = query.get('estado') || '';
        filtro.tipo_oferta = ETIQUETAS_TIPO_OFERTA[query.get('tipo_oferta')] ? query.get('tipo_oferta') : '';
        if (filtro.tipo_oferta === 'NO_GANADORA_CON_SECCIONES') filtro.tipo_oferta = 'NO_GANADORA';
        filtro.fecha_desde = query.get('fecha_desde') || '';
        filtro.fecha_hasta = query.get('fecha_hasta') || '';
        filtro.estado_entrega = ESTADOS_ENTREGA[query.get('estado_entrega')] ? query.get('estado_entrega') : '';
        if (filtro.estado_entrega) filtroNombres.estado_entrega = ESTADOS_ENTREGA[filtro.estado_entrega].texto;
        filtro.rentabilidad_base = query.get('rentabilidad_base') === 'facturas' ? 'facturas' : 'ofertas';

        $('#expo-selector').val(filtro.expo_id);
        $('#expo-f-estado').val(filtro.estado);
        $('#expo-f-tipo-oferta').val(filtro.tipo_oferta);
        $('#expo-f-desde').val(filtro.fecha_desde);
        $('#expo-f-hasta').val(filtro.fecha_hasta);
        $('input[name="expo-f-rentabilidad-base"][value="' + filtro.rentabilidad_base + '"]').prop('checked', true).closest('label').addClass('active').siblings().removeClass('active');

        $('#modal-oferta-expo').on('hidden.bs.modal', function () {
            if (volverASecciones) {
                volverASecciones = false;
                ofertaSeleccionada = null;
                actualizarUrl({ oferta_id: null, producto_id: null });
                if (flujoSeleccionado) abrirSecciones(flujoSeleccionado);
                return;
            }
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

        mostrarSpinnerCarga();
        cargarCatalogoFiltros().always(ocultarSpinnerCarga).then(function () {
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
        volverASeccionesDesdeOferta: volverASeccionesDesdeOferta,
        cargarCatalogoFiltros: cargarCatalogoFiltros,
    };
})();
