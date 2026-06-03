/**
 * dashboard-ventas.js
 * PROFAC — Dashboard de Ventas BI
 * Pestaña 1: Histórico & Comparativo
 * Pestaña 2: Reporte Semanal
 * Pestaña 3: Analítica Avanzada
 */
var dashboardVentas = (function () {
    'use strict';

    /* ─── Estado interno ──────────────────────────────────────────────────── */
    var charts        = {};
    var tablas        = {};
    var _todosAnios   = [];
    var _aniosSel     = [];
    var _filtroSemDia    = null;
    var _filtroAdvVend   = null;
    var _facturasCliData = [];   /* cache para exportación con totales */
    var _dts          = {};   /* DataTables instances */

    /* ─── DataTables helpers ──────────────────────────────────────────────── */
    var DT_LANG = {
        decimal: ',',
        thousands: '.',
        processing: 'Procesando...',
        search: 'Buscar:',
        lengthMenu: 'Mostrar _MENU_ registros',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
        infoFiltered: '(filtrado de _MAX_ registros totales)',
        loadingRecords: 'Cargando...',
        zeroRecords: 'No se encontraron resultados',
        emptyTable: 'No hay datos disponibles en la tabla',
        paginate: {
            first: 'Primero',
            previous: 'Anterior',
            next: 'Siguiente',
            last: 'Último'
        },
        aria: {
            sortAscending: ': activar para ordenar la columna de manera ascendente',
            sortDescending: ': activar para ordenar la columna de manera descendente'
        }
    };
    var DT_DOM  = '<"row mb-1"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-12"t>><"row mt-1"<"col-sm-5"i><"col-sm-7"p>>';

    function _dtDestroy(id) {
        if (_dts[id]) { try { _dts[id].destroy(); } catch (e) {} _dts[id] = null; }
    }
    function _dtInit(id, orderCol) {
        _dts[id] = $('#' + id).DataTable({
            pageLength: 8,
            lengthMenu: [[8, 15, 25, 50, -1], [8, 15, 25, 50, 'Todos']],
            language:   DT_LANG,
            dom:        DT_DOM,
            responsive: true,
            autoWidth:  false,
            order:      orderCol !== undefined ? [[orderCol, 'desc']] : [],
            initComplete: function () { this.api().columns.adjust(); }
        });
    }

    /* ─── Helpers ─────────────────────────────────────────────────────────── */
    var MESES   = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    var DIAS_EN = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    var DIAS_ES = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
    var COLORES = ['#EC401B','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#4e73df','#fd7e14','#20c997','#6f42c1'];

    /* Genera una paleta de exactamente n colores distintos.
       Para n <= 10 usa COLORES; para n > 10 genera colores HSL uniformemente distribuidos. */
    function _palette(n) {
        if (n <= COLORES.length) return COLORES.slice(0, n);
        var cols = [];
        for (var i = 0; i < n; i++) {
            var h = Math.round((i * 360) / n);
            cols.push('hsl(' + h + ',65%,48%)');
        }
        return cols;
    }

    function fmt(n) {
        return 'L. ' + parseFloat(n || 0).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function fmtN(n) {
        return parseFloat(n || 0).toLocaleString('es-HN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    function get(id) { return document.getElementById(id); }
    function setText(id, txt) { var el = get(id); if (el) el.textContent = txt; }
    function skeleton(id) {
        var el = get(id);
        if (el) el.innerHTML = '<div class="skeleton-block" style="height:30px;width:80%;"></div>';
    }
    function destroyChart(id) {
        if (charts[id]) { try { charts[id].destroy(); } catch (e) {} charts[id] = null; }
        var el = get(id); if (el) el.innerHTML = '';
    }
    function todayStr() { return new Date().toISOString().slice(0, 10); }
    function primerDiaMes() {
        var h = new Date();
        return h.getFullYear() + '-' + String(h.getMonth() + 1).padStart(2, '0') + '-01';
    }
    function primerDiaMesAnterior() {
        var d = new Date(); d.setDate(1); d.setMonth(d.getMonth() - 1);
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-01';
    }
    function ultimoDiaMesAnterior() {
        var d = new Date(); d.setDate(0);
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function _activarBuscadorSelect($el, placeholder) {
        if (!$el || !$el.length) return;
        if (!$.fn || !$.fn.select2) return;

        if ($el.hasClass('select2-hidden-accessible')) {
            try { $el.select2('destroy'); } catch (e) {}
        }

        $el.select2({
            width: '100%',
            placeholder: placeholder || ($el.find('option:first').text() || 'Buscar...'),
            allowClear: true,
            minimumResultsForSearch: 0
        });
    }

    function initFiltrosProductosBuscables() {
        _activarBuscadorSelect($('#prod-filtro-producto'), 'Buscar por codigo o nombre...');
        _activarBuscadorSelect($('#cli-cliente'), 'Buscar por nombre de cliente...');
        _activarBuscadorSelect($('#cli-producto'), 'Buscar producto...');
        _activarBuscadorSelect($('#marc-cliente'), 'Buscar cliente...');
        _activarBuscadorSelect($('#marc-producto'), 'Buscar producto...');
    }

    function syncFiltrosAnaliticaPorPestana() {
        var activePill = $('#adv-pills .nav-link.active').attr('href') || '#pill-pane-vend';
        var ocultarGlobal = activePill === '#pill-pane-prod' || activePill === '#pill-pane-cli' || activePill === '#pill-pane-marc';

        $('#adv-global-filtros').toggleClass('d-none', ocultarGlobal);

        if (ocultarGlobal) {
            $('#adv-active-filters').addClass('d-none');
        } else if ($('#adv-filter-badge-vend').is(':visible')) {
            $('#adv-active-filters').removeClass('d-none');
        }
    }

    /* ─── Parámetros de cada pestaña ──────────────────────────────────────── */
    function getP1() {
        return {
            anios: _aniosSel,
            vendedor: $('#h-vendedor').val() || '',
            tipo_cliente: $('#h-tipo-cliente').val() || ''
        };
    }
    function getP2() {
        return {
            fecha_inicio: $('#s-fi').val() || primerDiaMes(),
            fecha_final:  $('#s-ff').val() || todayStr(),
            vendedor:     $('#s-vendedor').val() || '',
            tipo_cliente: $('#s-tipo-cliente').val() || '',
            dia_semana:   _filtroSemDia || ''
        };
    }
    function getP3() {
        return {
            fecha_inicio: $('#a-fi').val() || primerDiaMesAnterior(),
            fecha_final:  $('#a-ff').val() || ultimoDiaMesAnterior(),
            vendedor:     _filtroAdvVend ? _filtroAdvVend : ($('#a-vendedor').val() || ''),
            tipo_cliente: $('#a-tipo-cliente').val() || ''
        };
    }
    function getCliFilters() {
        return {
            fecha_inicio: $('#cli-fi').val() || primerDiaMesAnterior(),
            fecha_final:  $('#cli-ff').val() || ultimoDiaMesAnterior(),
            cliente:  $('#cli-cliente').val() || '',
            producto: $('#cli-producto').val() || '',
            marca:    $('#cli-marca').val() || ''
        };
    }
    function getMarkFilters() {
        return {
            fecha_inicio: $('#marc-fi').val() || primerDiaMesAnterior(),
            fecha_final:  $('#marc-ff').val() || ultimoDiaMesAnterior(),
            cliente:  $('#marc-cliente').val() || '',
            producto: $('#marc-producto').val() || ''
        };
    }

    /* ══════════════════════════════════════════════════════════════════════
       INIT
    ══════════════════════════════════════════════════════════════════════ */
    function init() {
        cargarCatalogos().then(function () {
            /* Año actual seleccionado por defecto */
            var anioActual = new Date().getFullYear();
            if (_aniosSel.length === 0) {
                _aniosSel = [anioActual];
            }
            renderAniosPills();

            /* Fechas por defecto P2 y P3 */
            $('#s-fi').val(primerDiaMes());
            $('#s-ff').val(todayStr());
            $('#a-fi').val(primerDiaMesAnterior());
            $('#a-ff').val(ultimoDiaMesAnterior());
            $('#prod-fi').val(new Date().getFullYear() + '-01-01');
            $('#prod-ff').val(todayStr());
            /* Fechas por defecto pestaña Clientes */
            $('#cli-fi').val(primerDiaMesAnterior());
            $('#cli-ff').val(ultimoDiaMesAnterior());
            /* Fechas por defecto pestaña Marcas */
            $('#marc-fi').val(primerDiaMesAnterior());
            $('#marc-ff').val(ultimoDiaMesAnterior());

            /* Auto-carga pestaña 1 */
            cargarHistorico();
        });

        /* Cuando se cambia a la pestaña Analítica, cargar datos automáticamente */
        $('#tab-adv').on('shown.bs.tab', function () { cargarAnalitica(); });

        /* Redimensionar charts cuando se cambia de sub-pill (Analítica) */
        $('#adv-pills a[data-toggle="pill"]').on('shown.bs.tab', function () {
            syncFiltrosAnaliticaPorPestana();
            var href = $(this).attr('href');
            if (href === '#pill-pane-cli') {
                /* Recalcular anchos de columnas DataTables al hacer visible el tab */
                setTimeout(function () {
                    Object.keys(_dts).forEach(function (id) {
                        if (_dts[id]) { try { _dts[id].columns.adjust().draw(false); } catch (e) {} }
                    });
                }, 50);
            } else if (href === '#pill-pane-marc') {
                cargarMarcas();
            }
            setTimeout(function () {
                Object.keys(charts).forEach(function (id) {
                    if (charts[id]) { try { charts[id].updateOptions({}, false, false); } catch (e) {} }
                });
            }, 100);
        });

        syncFiltrosAnaliticaPorPestana();

        $('#prod-filtro-producto').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                cargarDashboardProductos();
            }
        });
    }

    /* ══════════════════════════════════════════════════════════════════════
       CATÁLOGOS
    ══════════════════════════════════════════════════════════════════════ */
    function cargarCatalogos() {
        return $.get('/reporte/dashboard/catalogo-filtros').then(function (data) {
            data = data || {};
            var aniosData = Array.isArray(data.anios) ? data.anios : [];
            var tiposData = Array.isArray(data.tiposCliente) ? data.tiposCliente : [];
            var vendsData = Array.isArray(data.vendedores) ? data.vendedores : [];
            var productosData = Array.isArray(data.productos) ? data.productos : [];
            /* Años */
            _todosAnios = aniosData.map(function (a) { return parseInt(a.anio); }).filter(function (n) { return !isNaN(n); });

            /* Tipos de cliente */
            var tcOpts = '<option value="">Todos</option>';
            tiposData.forEach(function (tc) {
                tcOpts += '<option value="' + tc.id + '">' + tc.descripcion + '</option>';
            });
            $('#h-tipo-cliente, #s-tipo-cliente, #a-tipo-cliente').html(tcOpts);

            /* Checkboxes de vendedores para comparación */
            _renderVendChecks(vendsData);

            /* Fechas por defecto comparación: primer día del mes actual → hoy */
            var _now = new Date();
            var _firstOfMonth = _now.getFullYear() + '-' + String(_now.getMonth() + 1).padStart(2, '0') + '-01';
            $('#cmp-fi').val(_firstOfMonth);
            $('#cmp-ff').val(todayStr());

            /* Checkboxes tele-asesores (misma lista de usuarios que vendedores) */
            _renderTlaChecks(vendsData);
            $('#tla-fi').val(_firstOfMonth);
            $('#tla-ff').val(todayStr());

            var prodOpts = '<option value="">— Seleccione un producto —</option>';
            productosData.forEach(function (p) { prodOpts += '<option value="' + p.id + '">' + p.nombre + '</option>'; });
            $('#prod-filtro-producto').html(prodOpts);

            /* Productos para el filtro de la pestaña Clientes */
            var cliProdOpts = '<option value="">Todos los productos</option>';
            productosData.forEach(function (p) { cliProdOpts += '<option value="' + p.id + '">' + p.nombre + '</option>'; });
            $('#cli-producto').html(cliProdOpts);
            /* Productos para el filtro de la pestaña Marcas */
            var marcProdOpts = '<option value="">Todos los productos</option>';
            productosData.forEach(function (p) { marcProdOpts += '<option value="' + p.id + '">' + p.nombre + '</option>'; });
            $('#marc-producto').html(marcProdOpts);

            /* Marcas para el filtro de la pestaña Analítica */
            var marcasData = Array.isArray(data.marcas) ? data.marcas : [];
            var marcaOpts = '<option value="">Todas las marcas</option>';
            marcasData.forEach(function (m) { marcaOpts += '<option value="' + m.id + '">' + m.nombre + '</option>'; });
            $('#cli-marca').html(marcaOpts);

            /* Clientes para el filtro de la pestaña Clientes */
            var clientesData = Array.isArray(data.clientes) ? data.clientes : [];
            var cliOpts = '<option value="">Todos los clientes</option>';
            clientesData.forEach(function (c) { cliOpts += '<option value="' + c.nombre + '">' + c.nombre + '</option>'; });
            $('#cli-cliente').html(cliOpts);
            /* Clientes para el filtro de Marcas */
            var marcCliOpts = '<option value="">Todos los clientes</option>';
            clientesData.forEach(function (c) { marcCliOpts += '<option value="' + c.nombre + '">' + c.nombre + '</option>'; });
            $('#marc-cliente').html(marcCliOpts);

            initFiltrosProductosBuscables();
        }).fail(function () {
            console.error('[DashboardVentas] Error al cargar catálogos');
        });
    }

    /* Píldoras de año (P1) */
    function renderAniosPills() {
        var $cont = $('#h-anios-pills');
        $cont.empty();
        _todosAnios.forEach(function (a) {
            var activo = _aniosSel.indexOf(a) >= 0;
            var $btn = $('<button type="button"></button>')
                .addClass('btn year-pill ' + (activo ? 'btn-primary' : 'btn-outline-primary'))
                .text(a)
                .on('click', function () {
                    var idx = _aniosSel.indexOf(a);
                    if (idx >= 0) {
                        if (_aniosSel.length > 1) _aniosSel.splice(idx, 1);
                    } else {
                        _aniosSel.push(a);
                        _aniosSel.sort();
                    }
                    renderAniosPills();
                });
            $cont.append($btn);
        });
    }

    /* ══════════════════════════════════════════════════════════════════════
       PESTAÑA 1 — Histórico & Comparativo
    ══════════════════════════════════════════════════════════════════════ */
    function cargarHistorico() {
        var p = getP1();

        /* Skeleton KPIs */
        ['kpi-total','kpi-facturas','kpi-sin-isv','kpi-clientes',
         'kpi-crecimiento','kpi-mejor-mes','kpi-mejor-vend','kpi-descuentos']
            .forEach(skeleton);

        /* KPIs */
        $.get('/reporte/dashboard/kpis', p).then(function (d) {
            setText('kpi-total',      fmt(d.total_ventas));
            setText('kpi-facturas',   fmtN(d.total_facturas));
            setText('kpi-sin-isv',    fmt(d.total_sin_isv));
            setText('kpi-clientes',   fmtN(d.clientes_unicos));
            setText('kpi-descuentos', fmt(d.total_descuentos));
            setText('kpi-mejor-mes',  d.mejor_mes || '-');
            setText('kpi-mejor-vend', d.mejor_vendedor || '-');

            var el = get('kpi-crecimiento');
            if (el) {
                var crec = (d.crecimiento !== null && d.crecimiento !== undefined)
                    ? (d.crecimiento >= 0 ? '+' : '') + d.crecimiento + '%'
                    : 'N/D';
                el.textContent = crec;
                el.style.color = (d.crecimiento >= 0) ? '#1cc88a' : '#e74a3b';
            }
        }).fail(function () {
            ['kpi-total','kpi-facturas','kpi-ticket','kpi-clientes',
             'kpi-crecimiento','kpi-mejor-mes','kpi-mejor-vend','kpi-descuentos']
                .forEach(function (id) { setText(id, '-'); });
        });

        /* Evolución + Barras */
        $.get('/reporte/dashboard/ventas-por-mes', p).then(function (rows) {
            renderEvolucion(rows);
        });

        /* Heatmap (todos los años disponibles) */
        $.get('/reporte/dashboard/heatmap').then(function (rows) {
            renderHeatmap(rows);
        });
    }

    function renderEvolucion(rows) {
        destroyChart('chart-evolucion');
        destroyChart('chart-crecimiento');
        destroyChart('chart-barras');
        if (!rows || !rows.length) return;

        /* Agrupar por año */
        var byAnio = {};
        rows.forEach(function (r) {
            if (!byAnio[r.anio]) byAnio[r.anio] = Array(12).fill(0);
            byAnio[r.anio][r.mes - 1] = parseFloat(r.total);
        });
        var sortedAnios = Object.keys(byAnio).sort();
        setText('evol-anios-badge', sortedAnios.join(' · '));

        var series = sortedAnios.map(function (a) {
            return { name: String(a), data: byAnio[a] };
        });

        /* Línea evolución */
        charts['chart-evolucion'] = new ApexCharts(get('chart-evolucion'), {
            chart: { type: 'line', height: 420, toolbar: { show: true }, zoom: { enabled: true } },
            series: series,
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            stroke: { curve: 'smooth', width: 2 },
            colors: COLORES,
            legend: { position: 'top' },
            markers: { size: 4 }
        });
        charts['chart-evolucion'].render();

        /* % Crecimiento mensual (diferencia absoluta entre 1er y último año) */
        if (sortedAnios.length >= 2) {
            var base = byAnio[sortedAnios[0]];
            var comp = byAnio[sortedAnios[sortedAnios.length - 1]];
            var difSeries = [{
                name: sortedAnios[sortedAnios.length - 1] + ' vs ' + sortedAnios[0],
                data: MESES.map(function (_, i) {
                    return base[i] > 0 ? Math.round(comp[i] - base[i]) : 0;
                })
            }];
            charts['chart-crecimiento'] = new ApexCharts(get('chart-crecimiento'), {
                chart: { type: 'bar', height: 340, toolbar: { show: false } },
                series: difSeries,
                xaxis: { categories: MESES },
                yaxis: { labels: { formatter: function (v) { return (v >= 0 ? '+' : '') + 'L.' + fmtN(v); } } },
                tooltip: { y: { formatter: function (v) { return (v >= 0 ? '+' : '') + fmt(v); } } },
                colors: [function (opts) { return opts.value >= 0 ? '#1cc88a' : '#e74a3b'; }],
                plotOptions: { bar: { distributed: true, borderRadius: 3 } },
                legend: { show: false }
            });
            charts['chart-crecimiento'].render();
        }

        /* Barras agrupadas */
        charts['chart-barras'] = new ApexCharts(get('chart-barras'), {
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            series: series,
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES,
            plotOptions: { bar: { columnWidth: '65%', borderRadius: 3 } },
            legend: { position: 'top' }
        });
        charts['chart-barras'].render();
    }

    function renderHeatmap(rows) {
        destroyChart('chart-heatmap');
        if (!rows || !rows.length) return;

        var byAnio = {};
        rows.forEach(function (r) {
            if (!byAnio[r.anio]) byAnio[r.anio] = Array(12).fill(0);
            byAnio[r.anio][r.mes - 1] = parseFloat(r.total);
        });

        var series = Object.keys(byAnio).sort().reverse().map(function (a) {
            return {
                name: String(a),
                data: byAnio[a].map(function (v, i) { return { x: MESES[i], y: Math.round(v) }; })
            };
        });

        charts['chart-heatmap'] = new ApexCharts(get('chart-heatmap'), {
            chart: { type: 'heatmap', height: 260, toolbar: { show: false } },
            series: series,
            colors: ['#EC401B'],
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            dataLabels: { enabled: false }
        });
        charts['chart-heatmap'].render();
    }

    /* ══════════════════════════════════════════════════════════════════════
       PESTAÑA 2 — Reporte Semanal
    ══════════════════════════════════════════════════════════════════════ */
    function cargarSemanal() {
        var p = getP2();

        ['s-kpi-total','s-kpi-facturas','s-kpi-sin-isv','s-kpi-mejor-dia','s-kpi-vend','s-kpi-cliente']
            .forEach(skeleton);

        /* Resumen KPIs + gráfica por día */
        $.get('/reporte/dashboard/resumen-semanal', p).then(function (d) {
            setText('s-kpi-total',    fmt(d.total));
            setText('s-kpi-facturas', fmtN(d.facturas));
            setText('s-kpi-sin-isv',  fmt(d.total_sin_isv));
            setText('s-kpi-mejor-dia', d.mejor_dia || '-');
            setText('s-kpi-vend',     d.mejor_vendedor || '-');
            setText('s-kpi-cliente',  d.mejor_cliente || '-');
            renderPorDia(d.por_dia);
        });

        /* Participación tipo cliente */
        $.get('/reporte/dashboard/participacion-tipo-cliente', p).then(function (rows) {
            renderTipoClienteSem(rows);
        });

        /* Top vendedores del período */
        $.get('/reporte/dashboard/top-vendedores', p).then(function (rows) {
            renderRankingVendSem(rows);
            recalcularCrecimiento();      /* también actualiza chart crecimiento */
        });

        /* Top 5 clientes */
        $.get('/reporte/dashboard/top-clientes-vendedor', $.extend({}, p, { limite: 5 })).then(function (rows) {
            renderTopCliSem(rows, p.vendedor);
        });

        /* Tabla server-side */
        _cargarTablaSemanal(p);
    }

    function renderPorDia(rows) {
        destroyChart('chart-por-dia');
        if (!rows || !rows.length) return;

        var data = DIAS_EN.map(function (d) {
            var r = rows.find(function (x) { return x.dia === d; });
            return r ? parseFloat(r.total) : 0;
        });

        charts['chart-por-dia'] = new ApexCharts(get('chart-por-dia'), {
            chart: {
                type: 'bar', height: 300, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        _filtroSemDia = DIAS_EN[cfg.dataPointIndex];
                        $('#filter-badge-dia').text('Día: ' + DIAS_ES[cfg.dataPointIndex]).show();
                        $('#sem-active-filters').removeClass('d-none');
                        cargarSemanal();
                    }
                }
            },
            series: [{ name: 'Ventas', data: data }],
            xaxis: { categories: DIAS_ES },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#EC401B'],
            plotOptions: { bar: { borderRadius: 4 } }
        });
        charts['chart-por-dia'].render();
    }

    function renderTipoClienteSem(rows) {
        destroyChart('chart-tipo-cliente-sem');
        if (!rows || !rows.length) return;

        charts['chart-tipo-cliente-sem'] = new ApexCharts(get('chart-tipo-cliente-sem'), {
            chart: {
                type: 'donut', height: 300,
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        $('#s-tipo-cliente').val(rows[cfg.dataPointIndex].tipo_id).trigger('change');
                        cargarSemanal();
                    }
                }
            },
            series: rows.map(function (r) { return parseFloat(r.total); }),
            labels:  rows.map(function (r) { return r.tipo_cliente; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES
        });
        charts['chart-tipo-cliente-sem'].render();
    }

    function renderRankingVendSem(rows) {
        destroyChart('chart-ranking-vend-sem');
        if (!rows || !rows.length) return;

        var sorted = rows.slice().sort(function (a, b) {
            return parseFloat(b.total_ventas) - parseFloat(a.total_ventas);
        });

        charts['chart-ranking-vend-sem'] = new ApexCharts(get('chart-ranking-vend-sem'), {
            chart: {
                type: 'bar', height: 300, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var vend = sorted[cfg.dataPointIndex];
                        $('#s-vendedor').val(vend.vendedor_id).trigger('change');
                        $('#filter-badge-vend').text('Asesor Comercial: ' + vend.vendedor).show();
                        $('#sem-active-filters').removeClass('d-none');
                        cargarSemanal();
                    }
                }
            },
            series: [{ name: 'Total Ventas', data: sorted.map(function (r) { return parseFloat(r.total_ventas); }) }],
            xaxis: { categories: sorted.map(function (r) { return r.vendedor; }), labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#EC401B'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } }
        });
        charts['chart-ranking-vend-sem'].render();
    }

    function renderTopCliSem(rows, vendedor) {
        destroyChart('chart-top-cli-sem');
        setText('top-cli-sem-label', vendedor ? 'Asesor comercial seleccionado' : 'Todos los asesores comerciales');
        if (!rows || !rows.length) return;

        charts['chart-top-cli-sem'] = new ApexCharts(get('chart-top-cli-sem'), {
            chart: { type: 'bar', height: 280, toolbar: { show: false } },
            series: [{ name: 'Ventas', data: rows.map(function (r) { return parseFloat(r.total_comprado); }) }],
            xaxis: { categories: rows.map(function (r) { return r.cliente; }) },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#1cc88a'],
            plotOptions: { bar: { borderRadius: 4 } }
        });
        charts['chart-top-cli-sem'].render();
    }

    function _cargarTablaSemanal(p) {
        if (tablas['tabla-semanal']) {
            try { tablas['tabla-semanal'].destroy(); } catch (e) {}
            tablas['tabla-semanal'] = null;
            $('#tabla-semanal tbody').empty();
        }

        tablas['tabla-semanal'] = $('#tabla-semanal').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url:  '/reporte/dashboard/ventas-semanales',
                type: 'GET',
                data: function (d) { return $.extend(d, p); }
            },
            columns: [
                { data: 'fecha' },
                { data: 'dia_semana' },
                { data: 'semana_iso' },
                {
                    data: 'documento',
                    render: function (data, type, row) {
                        if (type !== 'display') return data || '';
                        return '<a href="/factura/cooporativo/' + row.id + '" target="_blank" style="font-weight:700; color:#EC401B;">'
                             + (data || '—') + '</a>';
                    }
                },
                { data: 'cliente' },
                { data: 'vendedor' },
                { data: 'tipo_cliente' },
                { data: 'subtotal',  className: 'text-right' },
                { data: 'impuesto',  className: 'text-right' },
                { data: 'descuento', className: 'text-right' },
                { data: 'total',     className: 'text-right font-weight-bold' }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            language: DT_LANG
        });
    }

    /* Crecimiento por vendedor (comparar con período anterior o personalizado) */
    function recalcularCrecimiento() {
        var p = getP2();
        var fi2 = $('#crec-fi').val();
        var ff2 = $('#crec-ff').val();

        if (!fi2 || !ff2) {
            /* Calcular período anterior de igual duración */
            var d1 = new Date(p.fecha_inicio);
            var d2 = new Date(p.fecha_final);
            var dias = Math.max(1, Math.round((d2 - d1) / 86400000) + 1);
            var fi2d = new Date(d1 - dias * 86400000);
            var ff2d = new Date(d1 - 86400000);
            fi2 = fi2d.toISOString().slice(0, 10);
            ff2 = ff2d.toISOString().slice(0, 10);
            $('#crec-fi').val(fi2);
            $('#crec-ff').val(ff2);
        }

        var pBase = $.extend({}, p, { fecha_inicio: fi2, fecha_final: ff2 });

        Promise.all([
            $.get('/reporte/dashboard/top-vendedores', p),
            $.get('/reporte/dashboard/top-vendedores', pBase)
        ]).then(function (res) {
            setText('crec-vend-periodo-label', 'vs. ' + fi2 + ' al ' + ff2);
            _renderCrecimientoVend(res[0], res[1]);
        });
    }

    function _renderCrecimientoVend(actual, anterior) {
        destroyChart('chart-crec-vend-sem');
        if (!actual || !actual.length) return;

        var anteriorMap = {};
        anterior.forEach(function (r) { anteriorMap[r.vendedor] = parseFloat(r.total_ventas); });

        var sorted = actual.slice().sort(function (a, b) {
            return parseFloat(b.total_ventas) - parseFloat(a.total_ventas);
        });

        charts['chart-crec-vend-sem'] = new ApexCharts(get('chart-crec-vend-sem'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [
                { name: 'Período actual',   data: sorted.map(function (r) { return parseFloat(r.total_ventas); }) },
                { name: 'Período anterior', data: sorted.map(function (r) { return anteriorMap[r.vendedor] || 0; }) }
            ],
            xaxis: { categories: sorted.map(function (r) { return r.vendedor; }) },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#EC401B', '#858796'],
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
            legend: { position: 'top' }
        });
        charts['chart-crec-vend-sem'].render();
    }

    function limpiarFiltrosSem() {
        _filtroSemDia = null;
        $('#s-vendedor').val('').trigger('change');
        $('#s-tipo-cliente').val('');
        $('#filter-badge-dia, #filter-badge-vend').hide();
        $('#sem-active-filters').addClass('d-none');
        cargarSemanal();
    }

    /* ══════════════════════════════════════════════════════════════════════
       PESTAÑA 3 — Analítica Avanzada
    ══════════════════════════════════════════════════════════════════════ */
    function cargarAnalitica() {
        var p = getP3();
        var activePill = $('#adv-pills .nav-link.active').attr('href') || '#pill-pane-vend';

        if (activePill === '#pill-pane-prod') {
            cargarDashboardProductos(false);
            return;
        }

        if (activePill === '#pill-pane-comp') {
            return;
        }

        /* Vendedores */
        if (activePill === '#pill-pane-vend') {
            $.get('/reporte/dashboard/top-vendedores', p).then(function (rows) {
                _renderRankVend(rows);
                _renderTablaVendedores(rows);
            });
            return;
        }

        /* Clientes */
        if (activePill === '#pill-pane-cli') {
            cargarCli();
        }
    }

    function cargarCli() {
        var cliFilters = getCliFilters();
        var cliParams  = $.extend({}, cliFilters, { limite: 9999 });

        _actualizarBadgesCli(cliFilters);

        $.get('/reporte/dashboard/top-clientes', cliParams).then(function (rows) {
            _renderTopCli(rows);
            _renderFreqCli(rows);
            _renderEstadoCli(rows);
            _renderTablaClientes(rows);
        });
        $.get('/reporte/dashboard/top-productos-cli', $.extend({}, cliParams, { limite: 5 })).then(function (rows) {
            _renderTopProdCli(rows, cliFilters.cliente);
        });
        $.get('/reporte/dashboard/evolucion-clientes', cliParams).then(function (d) {
            _renderEvolCli(d);
        });
        $.get('/reporte/dashboard/evolucion-cantidad-cli', cliParams).then(function (d) {
            _renderEvolCantidadCli(d);
        });
        $.get('/reporte/dashboard/productos-x-cliente', cliParams).then(function (rows) {
            _renderTablaProductosCli(rows);
        });
        $.get('/reporte/dashboard/facturas-x-cliente', cliParams).then(function (rows) {
            _renderTablaFacturasCli(rows);
        });
    }

    function _actualizarBadgesCli(f) {
        var hayFiltro = !!(f.cliente || f.producto || f.marca);
        $('#cli-filtros-activos').toggleClass('d-none', !hayFiltro);
        if (f.cliente) { $('#cli-badge-cliente').text('Cliente: ' + f.cliente).show(); }
        else            { $('#cli-badge-cliente').hide(); }
        if (f.producto) {
            var prodTxt = $('#cli-producto option:selected').text() || f.producto;
            $('#cli-badge-producto').text('Producto: ' + prodTxt).show();
        } else { $('#cli-badge-producto').hide(); }
        if (f.marca)    $('#cli-badge-marca').text('Marca: ' + $('#cli-marca option:selected').text()).show();
        else            $('#cli-badge-marca').hide();
    }

    function cargarMarcas() {
        var p = getMarkFilters();
        $.get('/reporte/dashboard/top-marcas-cli', p).then(function (rows) {
            _renderChartMarcasCli(rows);
            _renderTablaMarcasCli(rows);
        }).fail(function () { console.error('[Marcas] Error al cargar datos'); });
    }

    function limpiarFiltroMarc() {
        $('#marc-cliente').val('').trigger('change');
        $('#marc-producto').val('').trigger('change');
        $('#marc-fi').val(primerDiaMesAnterior());
        $('#marc-ff').val(ultimoDiaMesAnterior());
        cargarMarcas();
    }

    function _renderChartMarcasCli(rows) {
        destroyChart('chart-marc-bar');
        destroyChart('chart-marc-donut');
        if (!rows || !rows.length) return;

        /* Bar chart — top 15 marcas por total */
        var top = rows.slice(0, 15);
        charts['chart-marc-bar'] = new ApexCharts(get('chart-marc-bar'), {
            chart: { type: 'bar', height: 340, toolbar: { show: false } },
            series: [{ name: 'Total Vendido', data: top.map(function (r) { return parseFloat(r.total_vendido); }) }],
            xaxis: {
                categories: top.map(function (r) { return r.marca; }),
                labels: { formatter: function (v) { return 'L.' + fmtN(v); } }
            },
            yaxis: {},
            tooltip: {
                custom: function (opts) {
                    var r = top[opts.dataPointIndex];
                    if (!r) return '';
                    return '<div style="padding:8px 12px">' +
                        '<strong>' + r.marca + '</strong><br>' +
                        'Total: ' + fmt(r.total_vendido) + '<br>' +
                        'Unidades: <b>' + fmtN(r.unidades) + '</b><br>' +
                        'Clientes: ' + r.clientes + '<br>' +
                        'Facturas: ' + r.facturas +
                        '</div>';
                }
            },
            colors: ['#f6c23e'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            dataLabels: {
                enabled: true,
                formatter: function (v, opts) {
                    var r = top[opts.dataPointIndex];
                    return r ? fmtN(r.unidades) + ' u.' : '';
                },
                style: { fontSize: '11px', colors: ['#333'] }
            }
        });
        charts['chart-marc-bar'].render();

        /* Donut — participación, top 8 + Otros */
        var donutRows = rows.slice(0, 8);
        var otrosTotal = rows.slice(8).reduce(function (s, r) { return s + parseFloat(r.total_vendido); }, 0);
        var series = donutRows.map(function (r) { return parseFloat(r.total_vendido); });
        var labels = donutRows.map(function (r) { return r.marca; });
        if (otrosTotal > 0) { series.push(otrosTotal); labels.push('Otros'); }

        charts['chart-marc-donut'] = new ApexCharts(get('chart-marc-donut'), {
            chart: { type: 'donut', height: 340 },
            series: series,
            labels: labels,
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { formatter: function (val) { return val.toFixed(1) + '%'; } }
        });
        charts['chart-marc-donut'].render();
    }

    function _renderTablaMarcasCli(rows) {
        _dtDestroy('tabla-marcas-cli');
        var $tbody = $('#tbody-marcas-cli').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + r.marca + '</td>' +
                '<td class="text-right">' + r.clientes + '</td>' +
                '<td class="text-right">' + r.facturas + '</td>' +
                '<td class="text-right">' + r.productos + '</td>' +
                '<td class="text-right">' + fmtN(r.unidades) + '</td>' +
                '<td class="text-right">' + fmt(r.total_vendido) + '</td>' +
                '<td class="text-right">' + r.participacion + '%</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-marcas-cli', 6);
        setTimeout(function () {
            if (_dts['tabla-marcas-cli']) { try { _dts['tabla-marcas-cli'].columns.adjust().draw(false); } catch (e) {} }
        }, 50);
    }

    function limpiarFiltroCli(campo) {
        if (!campo) {
            $('#cli-cliente').val('').trigger('change');
            $('#cli-producto').val('').trigger('change');
            $('#cli-marca').val('');
            $('#cli-fi').val(primerDiaMesAnterior());
            $('#cli-ff').val(ultimoDiaMesAnterior());
        } else if (campo === 'cliente')  { $('#cli-cliente').val('').trigger('change'); }
        else if (campo === 'producto') { $('#cli-producto').val('').trigger('change'); }
        else if (campo === 'marca')    { $('#cli-marca').val(''); }
        cargarCli();
    }

    function _renderRankVend(rows) {
        destroyChart('chart-rank-vend');
        destroyChart('chart-part-vend');
        if (!rows || !rows.length) return;

        var sorted = rows.slice().sort(function (a, b) {
            return parseFloat(b.total_ventas) - parseFloat(a.total_ventas);
        });

        /* Barra horizontal */
        charts['chart-rank-vend'] = new ApexCharts(get('chart-rank-vend'), {
            chart: {
                type: 'bar', height: 300, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var vend = sorted[cfg.dataPointIndex];
                        _filtroAdvVend = vend.vendedor_id;
                        $('#adv-filter-badge-vend').text('Asesor Comercial: ' + vend.vendedor).show();
                        $('#adv-active-filters').removeClass('d-none');
                        cargarAnalitica();
                    }
                }
            },
            series: [{ name: 'Total', data: sorted.map(function (r) { return parseFloat(r.total_ventas); }) }],
            xaxis: { categories: sorted.map(function (r) { return r.vendedor; }), labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#EC401B'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } }
        });
        charts['chart-rank-vend'].render();

        /* Donut participación */
        charts['chart-part-vend'] = new ApexCharts(get('chart-part-vend'), {
            chart: { type: 'donut', height: 300 },
            series: sorted.map(function (r) { return parseFloat(r.total_ventas); }),
            labels:  sorted.map(function (r) { return r.vendedor; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES
        });
        charts['chart-part-vend'].render();
    }

    function _renderTopCli(rows) {
        destroyChart('chart-top-cli');
        if (!rows || !rows.length) return;

        /* Agregar por cliente y ordenar por total desc */
        var byClient = {};
        rows.forEach(function (r) {
            var k = r.cliente_id || r.cliente;
            if (!byClient[k]) { byClient[k] = { cliente: r.cliente, total_comprado: 0 }; }
            byClient[k].total_comprado += parseFloat(r.total_comprado || 0);
        });
        var agg = Object.values(byClient).sort(function (a, b) { return b.total_comprado - a.total_comprado; });
        var top15 = agg.slice(0, 15);

        charts['chart-top-cli'] = new ApexCharts(get('chart-top-cli'), {
            chart: {
                type: 'bar', height: 320, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top15[cfg.dataPointIndex];
                        if (!r) return;
                        $('#cli-cliente').val(r.cliente).trigger('change');
                        cargarCli();
                    }
                }
            },
            series: [{ name: 'Total Facturado', data: top15.map(function (r) { return parseFloat(r.total_comprado); }) }],
            xaxis: { categories: top15.map(function (r) { return r.cliente; }), labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#36b9cc'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            dataLabels: { enabled: false }
        });
        charts['chart-top-cli'].render();
    }

    function _renderAbcCli(rows) {
        destroyChart('chart-abc-cli');
        if (!rows || !rows.length) return;

        var abc = { A: 0, B: 0, C: 0 };
        rows.forEach(function (r) { abc[r.clasificacion_abc] = (abc[r.clasificacion_abc] || 0) + parseFloat(r.total_comprado); });

        charts['chart-abc-cli'] = new ApexCharts(get('chart-abc-cli'), {
            chart: { type: 'donut', height: 320 },
            series: [abc.A, abc.B, abc.C],
            labels: ['A — Top 70%', 'B — 70-90%', 'C — 90-100%'],
            colors: ['#1cc88a', '#f6c23e', '#e74a3b'],
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            legend: { position: 'bottom' }
        });
        charts['chart-abc-cli'].render();
    }

    function _renderFreqCli(rows) {
        destroyChart('chart-freq-cli');
        if (!rows || !rows.length) return;
        var top10 = rows.slice(0, 10).sort(function (a, b) {
            return parseInt(b.facturas) - parseInt(a.facturas);
        });
        charts['chart-freq-cli'] = new ApexCharts(get('chart-freq-cli'), {
            chart: {
                type: 'bar', height: 300, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top10[cfg.dataPointIndex];
                        if (!r) return;
                        $('#cli-cliente').val(r.cliente).trigger('change');
                        cargarCli();
                    }
                }
            },
            series: [{ name: 'Nro. Facturas', data: top10.map(function (r) { return parseInt(r.facturas); }) }],
            xaxis: { categories: top10.map(function (r) { return r.cliente; }) },
            yaxis: { labels: { formatter: function (v) { return v; } } },
            tooltip: { y: { formatter: function (v) { return v + ' facturas'; } } },
            colors: ['#1cc88a'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            dataLabels: { enabled: true, formatter: function (v) { return v; } }
        });
        charts['chart-freq-cli'].render();
    }

    function _renderEvolCli(d) {
        destroyChart('chart-evol-cli');
        if (!d || !d.series || !d.series.length) {
            var el = get('chart-evol-cli');
            if (el) el.innerHTML = '<p class="text-center text-muted mt-4">Sin datos en el rango seleccionado</p>';
            return;
        }
        var meses = (d.meses || []).map(function (m) {
            var parts = m.split('-'); return (parts[1] || m) + '/' + (parts[0] || '');
        });
        charts['chart-evol-cli'] = new ApexCharts(get('chart-evol-cli'), {
            chart: { type: 'line', height: 300, toolbar: { show: false } },
            series: d.series.map(function (s) {
                return { name: s.name, data: s.data.map(function (v) { return parseFloat(v); }) };
            }),
            xaxis: { categories: meses, labels: { rotate: -30 } },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            stroke: { width: 2, curve: 'smooth' },
            markers: { size: 3 },
            legend: { position: 'top' }
        });
        charts['chart-evol-cli'].render();
    }

    function _renderEstadoCli(rows) {
        destroyChart('chart-estado-cli');
        if (!rows || !rows.length) return;
        var activos   = rows.filter(function (r) { return !r.inactivo && !r.recurrente; }).length;
        var recurrentes = rows.filter(function (r) { return !!r.recurrente; }).length;
        var inactivos = rows.filter(function (r) { return !!r.inactivo;    }).length;
        charts['chart-estado-cli'] = new ApexCharts(get('chart-estado-cli'), {
            chart: { type: 'donut', height: 260 },
            series: [recurrentes, activos, inactivos],
            labels: ['Recurrente', 'Activo', 'Inactivo >60 dias'],
            colors: ['#1cc88a', '#36b9cc', '#e74a3b'],
            tooltip: { y: { formatter: function (v) { return v + ' clientes'; } } },
            legend: { position: 'bottom' }
        });
        charts['chart-estado-cli'].render();
    }

    function _renderTicketCli(rows) {
        /* reemplazado por _renderEvolCantidadCli — mantenido para no romper posibles llamadas ext. */
    }

    function _renderEvolCantidadCli(d) {
        destroyChart('chart-evol-cant-cli');
        if (!d || !d.series || !d.series.length) {
            var el = get('chart-evol-cant-cli');
            if (el) el.innerHTML = '<p class="text-center text-muted mt-4">Sin datos en el rango seleccionado</p>';
            return;
        }
        var meses = (d.meses || []).map(function (m) {
            var parts = m.split('-'); return (parts[1] || m) + '/' + (parts[0] || '');
        });
        charts['chart-evol-cant-cli'] = new ApexCharts(get('chart-evol-cant-cli'), {
            chart: { type: 'line', height: 260, toolbar: { show: false } },
            series: d.series.map(function (s) {
                return { name: s.name, data: s.data.map(function (v) { return parseFloat(v); }) };
            }),
            xaxis: { categories: meses, labels: { rotate: -30 } },
            yaxis: { labels: { formatter: function (v) { return fmtN(v) + ' u.'; } } },
            tooltip: { y: { formatter: function (v) { return fmtN(v) + ' unidades'; } } },
            stroke: { width: 2, curve: 'smooth' },
            markers: { size: 3 },
            legend: { position: 'top' }
        });
        charts['chart-evol-cant-cli'].render();
    }

    function _renderTopProdCli(rows, cliNombre) {
        destroyChart('chart-top-prod-cli');
        var titulo = cliNombre ? 'Top 5 Productos \u2014 ' + cliNombre : 'Top 5 Productos m\u00e1s Vendidos';
        var elTit = get('cli-prod-titulo');
        if (elTit) elTit.textContent = titulo;
        if (!rows || !rows.length) return;

        charts['chart-top-prod-cli'] = new ApexCharts(get('chart-top-prod-cli'), {
            chart: {
                type: 'bar', height: 320, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = rows[cfg.dataPointIndex];
                        if (!r) return;
                        $('#cli-producto').val(r.producto_id).trigger('change');
                        cargarCli();
                    }
                }
            },
            series: [{ name: 'Total Vendido', data: rows.map(function (r) { return parseFloat(r.total_vendido); }) }],
            xaxis: {
                categories: rows.map(function (r) {
                    return r.producto.length > 25 ? r.producto.substring(0, 25) + '\u2026' : r.producto;
                }),
                labels: { formatter: function (v) { return 'L.' + fmtN(v); } }
            },
            yaxis: {},
            tooltip: {
                y: { formatter: function (v) { return fmt(v); } },
                x: { formatter: function (v, opts) {
                    var idx = opts && opts.dataPointIndex !== undefined ? opts.dataPointIndex : 0;
                    return rows[idx] ? rows[idx].producto : v;
                }},
                custom: function (opts) {
                    var r = rows[opts.dataPointIndex];
                    if (!r) return '';
                    return '<div class="apexcharts-tooltip-box" style="padding:8px 12px">' +
                        '<strong>' + r.producto + '</strong><br>' +
                        'Total: ' + fmt(r.total_vendido) + '<br>' +
                        'Unidades: <b>' + fmtN(r.unidades) + '</b>' +
                        '</div>';
                }
            },
            colors: ['#4e73df'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            dataLabels: {
                enabled: true,
                formatter: function (v, opts) {
                    var r = rows[opts.dataPointIndex];
                    return r ? fmtN(r.unidades) + ' u.' : '';
                },
                style: { fontSize: '11px', colors: ['#fff'] }
            }
        });
        charts['chart-top-prod-cli'].render();
    }

    function _renderTablaProductosCli(rows) {
        _dtDestroy('tabla-prod-cli');
        var $tbody = $('#tbody-prod-cli').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (r.mes || '-') + '</td>' +
                '<td>' + r.cliente + '</td>' +
                '<td>' + r.tipo_cliente + '</td>' +
                '<td>' + r.producto + '</td>' +
                '<td>' + r.marca + '</td>' +
                '<td>' + r.categoria + '</td>' +
                '<td class="text-right">' + r.facturas + '</td>' +
                '<td class="text-right">' + parseFloat(r.unidades).toLocaleString('es-HN', { minimumFractionDigits: 2 }) + '</td>' +
                '<td class="text-right">' + fmt(r.total_comprado) + '</td>' +
                '<td>' + (r.ultima_compra || '-') + '</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-prod-cli', 9);
        setTimeout(function () {
            if (_dts['tabla-prod-cli']) { try { _dts['tabla-prod-cli'].columns.adjust().draw(false); } catch (e) {} }
        }, 50);
    }

    function _renderTablaFacturasCli(rows) {
        _facturasCliData = rows || [];   /* guardar para exportación */
        _dtDestroy('tabla-facturas-cli');
        var $tbody = $('#tbody-facturas-cli').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (r.fecha || '-') + '</td>' +
                '<td>' + (r.numero_factura || '-') + '</td>' +
                '<td>' + (r.cliente || '-') + '</td>' +
                '<td>' + (r.asesor_comercial || '-') + '</td>' +
                '<td>' + (r.tele_asesor || '-') + '</td>' +
                '<td class="text-right">' + fmt(r.subtotal) + '</td>' +
                '<td class="text-right">' + fmt(r.isv) + '</td>' +
                '<td class="text-right font-weight-bold">' + fmt(r.total) + '</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-facturas-cli', 8);
        setTimeout(function () {
            if (_dts['tabla-facturas-cli']) { try { _dts['tabla-facturas-cli'].columns.adjust().draw(false); } catch (e) {} }
        }, 50);
    }

    /* ─── Exportación dedicada: Detalle por Factura (con totales, info header, hoja protegida) ── */
    function exportarFacturasCliExcel() {
        var rows = _facturasCliData;
        if (!rows || !rows.length) { alert('No hay datos para exportar.'); return; }

        /* Ordenar de mayor a menor fecha (dd/mm/yyyy → yyyy-mm-dd para comparar) */
        var sorted = rows.slice().sort(function (a, b) {
            function toISO(s) {
                if (!s) return '';
                var p = s.split('/');
                return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : s;
            }
            return toISO(b.fecha) > toISO(a.fecha) ? 1 : -1;
        });

        /* Totales */
        var totSub = 0, totIsv = 0, totTotal = 0;
        sorted.forEach(function (r) {
            totSub   += parseFloat(r.subtotal || 0);
            totIsv   += parseFloat(r.isv      || 0);
            totTotal += parseFloat(r.total    || 0);
        });

        /* Fecha/hora de descarga */
        var now    = new Date();
        var pad    = function (n) { return n < 10 ? '0' + n : n; };
        var fechaDl = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear() +
                      ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
        var usuario = window._profacAuthUser || 'Usuario';

        function escXml(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function numCell(n, style) {
            return '<Cell ss:StyleID="' + style + '"><Data ss:Type="Number">' + parseFloat(n||0).toFixed(2) + '</Data></Cell>';
        }
        function strCell(s, style) {
            return '<Cell ss:StyleID="' + (style||'sStr') + '"><Data ss:Type="String">' + escXml(s) + '</Data></Cell>';
        }

        var COLS = [7, 22, 24, 16, 22, 18, 16, 12, 14]; /* anchos columnas */
        var colW = COLS.map(function(w){ return '<Column ss:Width="' + (w*7) + '"/>'; }).join('');

        var xml = '<?xml version="1.0" encoding="UTF-8"?><?mso-application progid="Excel.Sheet"?>' +
            '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ' +
            'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ' +
            'xmlns:x="urn:schemas-microsoft-com:office:excel">' +
            '<Styles>' +
              /* Empresa — grande, negrita, naranja */
              '<Style ss:ID="sEmp">' +
                '<Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="14"/>' +
                '<Interior ss:Color="#EC401B" ss:Pattern="Solid"/>' +
                '<Alignment ss:Horizontal="Left" ss:Vertical="Center"/>' +
                '<Protection ss:Protected="1"/>' +
              '</Style>' +
              /* Info (fecha/usuario) — fondo gris claro */
              '<Style ss:ID="sInfo">' +
                '<Font ss:Color="#333333" ss:Size="10"/>' +
                '<Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/>' +
                '<Alignment ss:Horizontal="Left" ss:Vertical="Center"/>' +
                '<Protection ss:Protected="1"/>' +
              '</Style>' +
              /* Encabezado columnas */
              '<Style ss:ID="sHdr">' +
                '<Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/>' +
                '<Interior ss:Color="#2F4050" ss:Pattern="Solid"/>' +
                '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' +
                '<Protection ss:Protected="1"/>' +
              '</Style>' +
              /* Celda de texto normal */
              '<Style ss:ID="sStr">' +
                '<Alignment ss:Horizontal="Left" ss:Vertical="Center"/>' +
                '<Protection ss:Protected="0"/>' +
              '</Style>' +
              /* Número entero */
              '<Style ss:ID="sInt">' +
                '<NumberFormat ss:Format="#,##0"/>' +
                '<Alignment ss:Horizontal="Right"/>' +
                '<Protection ss:Protected="0"/>' +
              '</Style>' +
              /* Moneda L. */
              '<Style ss:ID="sCur">' +
                '<NumberFormat ss:Format="&quot;L. &quot;#,##0.00"/>' +
                '<Alignment ss:Horizontal="Right"/>' +
                '<Protection ss:Protected="0"/>' +
              '</Style>' +
              /* Fila de totales */
              '<Style ss:ID="sTot">' +
                '<Font ss:Bold="1" ss:Size="11"/>' +
                '<Interior ss:Color="#FFF3E0" ss:Pattern="Solid"/>' +
                '<NumberFormat ss:Format="&quot;L. &quot;#,##0.00"/>' +
                '<Alignment ss:Horizontal="Right"/>' +
                '<Protection ss:Protected="1"/>' +
              '</Style>' +
              '<Style ss:ID="sTotLbl">' +
                '<Font ss:Bold="1" ss:Size="11"/>' +
                '<Interior ss:Color="#FFF3E0" ss:Pattern="Solid"/>' +
                '<Alignment ss:Horizontal="Right"/>' +
                '<Protection ss:Protected="1"/>' +
              '</Style>' +
            '</Styles>' +
            '<Worksheet ss:Name="Detalle por Factura"><Table>' + colW;

        /* Fila 1: Empresa (merge visual usando celda amplia) */
        xml += '<Row ss:Height="28">' +
            '<Cell ss:StyleID="sEmp" ss:MergeAcross="8"><Data ss:Type="String">Distribuciones Valencia — Detalle por Factura</Data></Cell>' +
        '</Row>';

        /* Fila 2: Fecha de descarga */
        xml += '<Row ss:Height="18">' +
            '<Cell ss:StyleID="sInfo" ss:MergeAcross="8"><Data ss:Type="String">Fecha de descarga: ' + escXml(fechaDl) + '</Data></Cell>' +
        '</Row>';

        /* Fila 3: Descargado por */
        xml += '<Row ss:Height="18">' +
            '<Cell ss:StyleID="sInfo" ss:MergeAcross="8"><Data ss:Type="String">Descargado por: ' + escXml(usuario) + '</Data></Cell>' +
        '</Row>';

        /* Fila 4: vacía de separación */
        xml += '<Row ss:Height="8"><Cell><Data ss:Type="String"> </Data></Cell></Row>';

        /* Fila 5: Encabezados */
        var headers = ['#','Fecha','N° Factura','Cliente','Asesor Comercial','Tele Asesor','Subtotal (Sin ISV)','ISV','Total'];
        xml += '<Row ss:Height="22">';
        headers.forEach(function(h){ xml += '<Cell ss:StyleID="sHdr"><Data ss:Type="String">' + escXml(h) + '</Data></Cell>'; });
        xml += '</Row>';

        /* Filas de datos */
        sorted.forEach(function (r, i) {
            xml += '<Row ss:Height="18">' +
                strCell(i + 1, 'sInt') +
                strCell(r.fecha    || '-') +
                strCell(r.numero_factura || '-') +
                strCell(r.cliente  || '-') +
                strCell(r.asesor_comercial || '-') +
                strCell(r.tele_asesor || '-') +
                numCell(r.subtotal, 'sCur') +
                numCell(r.isv,      'sCur') +
                numCell(r.total,    'sCur') +
            '</Row>';
        });

        /* Fila de totales */
        xml += '<Row ss:Height="22">' +
            '<Cell ss:StyleID="sTotLbl" ss:MergeAcross="5"><Data ss:Type="String">TOTAL</Data></Cell>' +
            '<Cell ss:StyleID="sTot"><Data ss:Type="Number">' + totSub.toFixed(2)   + '</Data></Cell>' +
            '<Cell ss:StyleID="sTot"><Data ss:Type="Number">' + totIsv.toFixed(2)   + '</Data></Cell>' +
            '<Cell ss:StyleID="sTot"><Data ss:Type="Number">' + totTotal.toFixed(2) + '</Data></Cell>' +
        '</Row>';

        xml += '</Table>' +
            '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">' +
              '<ProtectContents/>' +
              '<ProtectObjects/>' +
              '<ProtectScenarios/>' +
            '</WorksheetOptions>' +
        '</Worksheet></Workbook>';

        var blob = new Blob([xml], { type: 'application/vnd.ms-excel' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'Facturas_x_Cliente.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function exportarTablaExcel(tableId, filename) {
        var $table = $('#' + tableId);
        if (!$table.length) { alert('Tabla no encontrada'); return; }
        var dt = _dts[tableId];

        /* Encabezados */
        var headers = [];
        $table.find('thead tr:first th').each(function () { headers.push($(this).text().trim()); });

        /* Filas: usa data() de DataTables para obtener TODAS las filas (no sólo la página actual) */
        var rows = [];
        if (dt) {
            dt.rows({ search: 'applied' }).every(function () {
                var d = this.data();
                var cells = [];
                for (var ci = 0; ci < d.length; ci++) {
                    cells.push(String(d[ci] || '').replace(/<[^>]*>/g, '').trim());
                }
                rows.push(cells);
            });
        } else {
            $table.find('tbody tr').each(function () {
                var cells = [];
                $(this).find('td').each(function () { cells.push($(this).text().trim()); });
                rows.push(cells);
            });
        }

        function escXml(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function cellXml(val) {
            /* Moneda: empieza con "L. " */
            if (/^L\.\s/.test(val)) {
                var nc = parseFloat(val.replace(/^L\.\s*/, '').replace(/,/g, ''));
                if (!isNaN(nc)) return '<Cell ss:StyleID="sCur"><Data ss:Type="Number">' + nc + '</Data></Cell>';
            }
            var cleaned = val.replace(/,/g, '');
            /* Decimal puro (ej: 600.00) */
            if (/^\d+\.\d+$/.test(cleaned)) {
                return '<Cell ss:StyleID="sDec"><Data ss:Type="Number">' + parseFloat(cleaned) + '</Data></Cell>';
            }
            /* Entero puro */
            if (/^\d+$/.test(cleaned) && val !== '') {
                return '<Cell ss:StyleID="sInt"><Data ss:Type="Number">' + parseInt(cleaned, 10) + '</Data></Cell>';
            }
            return '<Cell><Data ss:Type="String">' + escXml(val) + '</Data></Cell>';
        }

        var xml = '<?xml version="1.0" encoding="UTF-8"?><?mso-application progid="Excel.Sheet"?>' +
            '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ' +
            'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' +
            '<Styles>' +
            '<Style ss:ID="sHdr">' +
              '<Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/>' +
              '<Interior ss:Color="#2F4050" ss:Pattern="Solid"/>' +
              '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' +
            '</Style>' +
            '<Style ss:ID="sCur">' +
              '<NumberFormat ss:Format="&quot;L. &quot;#,##0.00"/>' +
              '<Alignment ss:Horizontal="Right"/>' +
            '</Style>' +
            '<Style ss:ID="sDec">' +
              '<NumberFormat ss:Format="#,##0.00"/>' +
              '<Alignment ss:Horizontal="Right"/>' +
            '</Style>' +
            '<Style ss:ID="sInt">' +
              '<NumberFormat ss:Format="#,##0"/>' +
              '<Alignment ss:Horizontal="Right"/>' +
            '</Style>' +
            '</Styles>' +
            '<Worksheet ss:Name="Datos"><Table>';

        /* Anchos de columna proporcionales al header */
        headers.forEach(function (h) {
            xml += '<Column ss:Width="' + Math.max(60, h.length * 9) + '"/>';
        });

        /* Fila de encabezados */
        xml += '<Row ss:Height="22">';
        headers.forEach(function (h) {
            xml += '<Cell ss:StyleID="sHdr"><Data ss:Type="String">' + escXml(h) + '</Data></Cell>';
        });
        xml += '</Row>';

        /* Filas de datos */
        rows.forEach(function (cells) {
            xml += '<Row>';
            cells.forEach(function (c) { xml += cellXml(c); });
            xml += '</Row>';
        });

        xml += '</Table></Worksheet></Workbook>';

        var blob = new Blob([xml], { type: 'application/vnd.ms-excel' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = (filename || 'tabla') + '.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function _renderTopProd(rows) {
        destroyChart('chart-top-prod');
        if (!rows || !rows.length) return;
        var top20 = rows.slice(0, 20);

        charts['chart-top-prod'] = new ApexCharts(get('chart-top-prod'), {
            chart: {
                type: 'bar', height: 320, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top20[cfg.dataPointIndex];
                        /* Buscar el ID de categoría por texto en el select */
                        var catVal = $('#a-categoria option').filter(function () {
                            return $.trim($(this).text()) === $.trim(r.categoria);
                        }).val();
                        if (catVal) {
                            $('#a-categoria').val(catVal);
                        }
                        /* Mostrar badge de producto activo */
                        $('#cli-prod-filtro').text('Cat: ' + r.categoria).removeClass('d-none');
                        cargarAnalitica();
                    }
                }
            },
            series: [{ name: 'Ingresos', data: top20.map(function (r) { return parseFloat(r.ingresos); }) }],
            xaxis: { categories: top20.map(function (r) { return r.producto; }), labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#4e73df'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } }
        });
        charts['chart-top-prod'].render();
    }

    function _renderPareto(rows) {
        destroyChart('chart-pareto');
        if (!rows || !rows.length) return;
        var top20 = rows.slice(0, 20);

        charts['chart-pareto'] = new ApexCharts(get('chart-pareto'), {
            chart: {
                type: 'line', height: 320, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top20[cfg.dataPointIndex];
                        var catVal = $('#a-categoria option').filter(function () {
                            return $.trim($(this).text()) === $.trim(r.categoria);
                        }).val();
                        if (catVal) { $('#a-categoria').val(catVal); }
                        $('#cli-prod-filtro').text('Cat: ' + r.categoria).removeClass('d-none');
                        cargarAnalitica();
                    }
                }
            },
            series: [
                { name: 'Ingresos', type: 'column', data: top20.map(function (r) { return parseFloat(r.ingresos); }) },
                { name: 'Pareto %', type: 'line',   data: top20.map(function (r) { return parseFloat(r.pareto); }) }
            ],
            xaxis: { categories: top20.map(function (r) { return r.producto; }) },
            yaxis: [
                { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
                { opposite: true, min: 0, max: 100, labels: { formatter: function (v) { return v + '%'; } } }
            ],
            tooltip: { shared: true },
            colors: ['#4e73df', '#EC401B'],
            stroke: { width: [0, 2] }
        });
        charts['chart-pareto'].render();
    }

    function _renderTablaVendedores(rows) {
        _dtDestroy('tabla-vendedores');
        var $tbody = $('#tbody-vendedores').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + r.vendedor + '</td>' +
                '<td class="text-right">' + r.facturas + '</td>' +
                '<td class="text-right">' + r.clientes_atendidos + '</td>' +
                '<td class="text-right">' + fmt(r.total_ventas) + '</td>' +
                '<td class="text-right">' + fmt(r.ticket_promedio) + '</td>' +
                '<td class="text-right">' + r.participacion + '%</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-vendedores', 4);
    }

    function _renderTablaClientes(rows) {
        _dtDestroy('tabla-clientes');
        var $tbody = $('#tbody-clientes').empty();
        rows.forEach(function (r, i) {
            var estado = r.inactivo   ? '<span class="badge badge-danger">Inactivo</span>'
                       : r.recurrente ? '<span class="badge badge-success">Recurrente</span>'
                                      : '<span class="badge badge-secondary">Activo</span>';
            var cliEsc = r.cliente.replace(/'/g, "\\'");
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (r.mes || '-') + '</td>' +
                '<td><a href="#" onclick="$(\"#cli-cliente\").val(\'' + cliEsc + '\').trigger(\'change\'); dashboardVentas.cargarCli(); return false;" class="text-primary">' + r.cliente + '</a></td>' +
                '<td>' + r.tipo_cliente + '</td>' +
                '<td class="text-right">' + r.facturas + '</td>' +
                '<td class="text-right">' + fmt(r.total_comprado) + '</td>' +
                '<td class="text-right">' + (r.total_unidades || 0).toLocaleString('es-HN') + '</td>' +
                '<td>' + (r.ultima_compra || '-') + '</td>' +
                '<td class="text-right">' + (r.dias_sin_comprar || 0) + '</td>' +
                '<td>' + estado + '</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-clientes', 5);
        setTimeout(function () {
            if (_dts['tabla-clientes']) { try { _dts['tabla-clientes'].columns.adjust().draw(false); } catch (e) {} }
        }, 50);
    }

    function _renderTablaProductos(rows) {
        _dtDestroy('tabla-productos');
        var $tbody = $('#tbody-productos').empty();
        rows.forEach(function (r, i) {
            var facturaHtml = '<a href="#" onclick="dashboardVentas.imprimirFactura(' + r.factura_id + '); return false;" style="font-weight:700;color:#1a7efb;">' + (r.numero_factura || ('FAC-' + r.factura_id)) + '</a>';
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + facturaHtml + '</td>' +
                '<td>' + (r.fecha || '-') + '</td>' +
                '<td>' + (r.cliente || '-') + '</td>' +
                '<td>' + (r.escala || '-') + '</td>' +
                '<td>' + (r.vendedor || '-') + '</td>' +
                '<td>' + (r.codigo || '-') + '</td>' +
                '<td>' + r.producto + '</td>' +
                '<td class="text-right">' + fmtN(r.cantidad) + '</td>' +
                '<td class="text-right">' + fmt(r.precio_base_venta) + '</td>' +
                '<td class="text-right">' + fmt(r.precio_unitario) + '</td>' +
                '<td class="text-right">' + fmt(r.venta_factura) + '</td>' +
                '<td class="text-right">' + fmt(r.costo_total) + '</td>' +
                '<td class="text-right">' + fmt(r.utilidad_bruta) + '</td>' +
                '<td>' + (r.estado || '-') + '</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-productos', 1);
    }

    /* ─── MARCAS ─────────────────────────────────────────────────────────── */
    function _renderTopMarcas(rows) {
        destroyChart('chart-top-marcas');
        destroyChart('chart-part-marcas');
        if (!rows || !rows.length) return;

        var top15 = rows.slice(0, 15);

        charts['chart-top-marcas'] = new ApexCharts(get('chart-top-marcas'), {
            chart: {
                type: 'bar', height: 300, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top15[cfg.dataPointIndex];
                        _filtrarMarca(r.marca_id, r.marca);
                    }
                }
            },
            series: [{ name: 'Ingresos', data: top15.map(function (r) { return parseFloat(r.ingresos); }) }],
            xaxis: { categories: top15.map(function (r) { return r.marca; }), labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#f6c23e'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } }
        });
        charts['chart-top-marcas'].render();

        var top10 = rows.slice(0, 10);
        charts['chart-part-marcas'] = new ApexCharts(get('chart-part-marcas'), {
            chart: {
                type: 'donut', height: 300,
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top10[cfg.dataPointIndex];
                        _filtrarMarca(r.marca_id, r.marca);
                    }
                }
            },
            series: top10.map(function (r) { return parseFloat(r.ingresos); }),
            labels:  top10.map(function (r) { return r.marca; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES,
            legend: { position: 'right' }
        });
        charts['chart-part-marcas'].render();
    }

    function _filtrarMarca(marcaId, marcaNombre) {
        $('#prod-filtro-marca').val(marcaId).trigger('change');
        cargarDashboardProductos(false);
    }

    function _renderTablaMarcas(rows) {
        _dtDestroy('tabla-marcas');
        var $tbody = $('#tbody-marcas').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td class="font-weight-bold" style="cursor:pointer" onclick="dashboardVentas._filtrarMarca(' + r.marca_id + ',\'' + r.marca.replace(/'/g,'') + '\')">' +
                '<i class="fas fa-filter text-warning mr-1" style="font-size:.7rem"></i>' + r.marca + '</td>' +
                '<td class="text-right">' + fmt(r.ingresos) + '</td>' +
                '<td class="text-right">' + fmt(r.utilidad) + '</td>' +
                '<td class="text-right">' + r.participacion + '%</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-marcas', 2);
    }

    function _filtrarCategoria(catId) {
        $('#prod-filtro-categoria').val(catId).trigger('change');
        cargarDashboardProductos(false);
    }

    function _renderTopCategorias(rows) {
        destroyChart('chart-top-categorias');
        destroyChart('chart-part-categorias');
        if (!rows || !rows.length) return;

        var top15 = rows.slice(0, 15);
        charts['chart-top-categorias'] = new ApexCharts(get('chart-top-categorias'), {
            chart: {
                type: 'bar', height: 300, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top15[cfg.dataPointIndex];
                        _filtrarCategoria(r.categoria_id);
                    }
                }
            },
            series: [{ name: 'Ingresos', data: top15.map(function (r) { return parseFloat(r.ingresos || 0); }) }],
            xaxis: { categories: top15.map(function (r) { return r.categoria; }), labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#4e73df'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } }
        });
        charts['chart-top-categorias'].render();

        var top10 = rows.slice(0, 10);
        charts['chart-part-categorias'] = new ApexCharts(get('chart-part-categorias'), {
            chart: {
                type: 'donut', height: 300,
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top10[cfg.dataPointIndex];
                        _filtrarCategoria(r.categoria_id);
                    }
                }
            },
            series: top10.map(function (r) { return parseFloat(r.ingresos || 0); }),
            labels: top10.map(function (r) { return r.categoria; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES,
            legend: { position: 'right' }
        });
        charts['chart-part-categorias'].render();
    }

    function _renderTablaCategorias(rows) {
        _dtDestroy('tabla-categorias');
        var $tbody = $('#tbody-categorias').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td class="font-weight-bold" style="cursor:pointer" onclick="dashboardVentas._filtrarCategoria(' + r.categoria_id + ')">' +
                '<i class="fas fa-filter text-primary mr-1" style="font-size:.7rem"></i>' + (r.categoria || '-') + '</td>' +
                '<td class="text-right">' + fmt(r.ingresos) + '</td>' +
                '<td class="text-right">' + fmt(r.utilidad) + '</td>' +
                '<td class="text-right">' + fmtN(r.participacion) + '%</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-categorias', 2);
    }

    function _renderRotacion(rows) {
        _dtDestroy('tabla-prod-alta-rotacion');
        _dtDestroy('tabla-prod-baja-rotacion');

        var alta = (rows || []).slice().sort(function (a, b) {
            return parseFloat(b.unidades_vendidas || 0) - parseFloat(a.unidades_vendidas || 0);
        }).slice(0, 20);
        var baja = (rows || []).slice().sort(function (a, b) {
            return parseFloat(a.unidades_vendidas || 0) - parseFloat(b.unidades_vendidas || 0);
        }).slice(0, 20);

        var $alta = $('#tbody-prod-alta-rotacion').empty();
        alta.forEach(function (r, i) {
            $alta.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (r.producto || '-') + '</td>' +
                '<td class="text-right">' + fmt(r.ingresos) + '</td>' +
                '<td class="text-right">' + fmtN(r.unidades_vendidas) + '</td>' +
                '<td class="text-right">' + fmtN(r.apariciones) + '</td>' +
                '</tr>'
            );
        });

        var $baja = $('#tbody-prod-baja-rotacion').empty();
        baja.forEach(function (r, i) {
            $baja.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (r.producto || '-') + '</td>' +
                '<td class="text-right">' + fmt(r.ingresos) + '</td>' +
                '<td class="text-right">' + fmtN(r.unidades_vendidas) + '</td>' +
                '<td class="text-right">' + (r.ultima_venta || '-') + '</td>' +
                '</tr>'
            );
        });

        _dtInit('tabla-prod-alta-rotacion', 2);
        _dtInit('tabla-prod-baja-rotacion', 2);
    }

    /* ─── CLIENTES EN PESTAÑA PRODUCTOS ──────────────────────────────────── */
    function _renderCliProd(rows) {
        destroyChart('chart-cli-prod');
        if (!rows || !rows.length) return;
        var top10 = rows.slice(0, 10);

        charts['chart-cli-prod'] = new ApexCharts(get('chart-cli-prod'), {
            chart: {
                type: 'bar', height: 280, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        /* Al hacer clic en un cliente, ir a la pestaña Clientes */
                        $('#pill-cli').tab('show');
                    }
                }
            },
            series: [{ name: 'Total Comprado', data: top10.map(function (r) { return parseFloat(r.total_comprado); }) }],
            xaxis: {
                categories: top10.map(function (r) { return r.cliente; }),
                labels: { formatter: function (v) { return 'L.' + fmtN(v); } }
            },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#36b9cc'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } }
        });
        charts['chart-cli-prod'].render();
    }

    function _renderTablaCliProd(rows) {
        _dtDestroy('tabla-cli-prod');
        var $tbody = $('#tbody-cli-prod').empty();
        rows.slice(0, 20).forEach(function (r, i) {
            var abcColor = r.clasificacion_abc === 'A' ? 'success' : (r.clasificacion_abc === 'B' ? 'warning' : 'danger');
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + r.cliente + '</td>' +
                '<td>' + r.tipo_cliente + '</td>' +
                '<td class="text-center"><span class="badge badge-' + abcColor + '">' + r.clasificacion_abc + '</span></td>' +
                '<td class="text-right">' + r.facturas + '</td>' +
                '<td class="text-right">' + fmt(r.total_comprado) + '</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-cli-prod', 5);
    }

    function getProdFilters() {
        return {
            fecha_inicio:   $('#prod-fi').val() || (new Date().getFullYear() + '-01-01'),
            fecha_final:    $('#prod-ff').val() || todayStr(),
            producto_id:    $('#prod-filtro-producto').val() || ''
        };
    }

    function cargarDashboardProductos(mostrarValidacion) {
        if (typeof mostrarValidacion === 'undefined') mostrarValidacion = true;

        var p = getProdFilters();

        if (!p.producto_id) {
            _renderProdKpis({});
            _renderProdResumenSeleccionado(null);
            _renderProdEvolucion({});
            _renderProdTopClientes([]);
            _renderProdFacturas([]);
            _renderProdProductosCliente([]);
            _renderProdRankingClientes([]);
            _renderProdIndicadoresClientes({});
            _renderProdTopVendedores([]);
            _renderTablaProductos([]);
            setText('prod-escala', 'Seleccione un producto');
            if (mostrarValidacion) alert('Seleccione un producto para cargar la informacion.');
            return;
        }

        $.when(
            $.get('/reporte/dashboard/productos-analitica', p),
            $.get('/reporte/dashboard/top-productos', $.extend({}, p, { limite: 0 }))
        ).then(function (analiticaRes, productosRes) {
            var resp = analiticaRes[0] || {};
            var rowsProd = productosRes[0] || [];

            _renderProdKpis(resp.resumen_general || {});
            _renderProdResumenSeleccionado(resp.resumen_producto || null);
            _renderProdEvolucion(resp.evolucion || {});
            _renderProdTopClientes(resp.top_clientes || []);
            _renderProdFacturas(resp.facturas || []);
            _renderProdProductosCliente(resp.productos_cliente || []);
            _renderProdRankingClientes(resp.ranking_clientes || []);
            _renderProdIndicadoresClientes(resp.indicadores_clientes || {});
            _renderProdTopVendedores(resp.top_vendedores || []);
            _renderTablaProductos(rowsProd || []);
            setText('prod-escala', resp.escala_seleccionada || 'Sin aplicar');
        }).fail(function () {
            _renderProdKpis({});
            _renderProdResumenSeleccionado(null);
            _renderProdEvolucion({});
            _renderProdTopClientes([]);
            _renderProdFacturas([]);
            _renderProdProductosCliente([]);
            _renderProdRankingClientes([]);
            _renderProdIndicadoresClientes({});
            _renderProdTopVendedores([]);
            _renderTablaProductos([]);
            setText('prod-escala', 'Sin datos');
        });
    }

    function _renderProdKpis(r) {
        setText('prod-kpi-cantidad', fmtN(r.unidades_vendidas || 0));
        setText('prod-kpi-facturas', fmtN(r.total_facturas || 0));
        setText('prod-kpi-clientes', fmtN(r.total_clientes || 0));
        setText('prod-kpi-costo-total', fmt(r.costo_total || 0));
        setText('prod-kpi-venta-total', fmt(r.total_vendido || 0));
        setText('prod-kpi-utilidad-bruta', fmt(r.utilidad_bruta || 0));
    }

    function _renderProdResumenSeleccionado(r) {
        setText('prod-res-nombre', r && r.producto ? r.producto : '-');
        setText('prod-res-codigo', r && r.codigo ? r.codigo : '-');
        setText('prod-res-marca', r && r.marca ? r.marca : '-');
        setText('prod-res-categoria', r && r.categoria ? r.categoria : '-');
        setText('prod-res-precio-costo', fmt(r && r.precio_costo ? r.precio_costo : 0));
        setText('prod-res-existencia', fmtN(r && r.existencia ? r.existencia : 0));
        setText('prod-res-total', fmt(r && r.total_vendido ? r.total_vendido : 0));
        setText('prod-res-unidades', fmtN(r && r.unidades_vendidas ? r.unidades_vendidas : 0));
        setText('prod-res-clientes', fmtN(r && r.clientes_compraron ? r.clientes_compraron : 0));
        setText('prod-res-ultima', r && r.ultima_venta ? r.ultima_venta : '-');
        setText('prod-res-promedio', fmt(r && r.promedio_mensual ? r.promedio_mensual : 0));
    }

    function _renderProdEvolucion(obj) {
        destroyChart('chart-prod-evolucion');
        var dia = obj.dia || [];
        var semana = obj.semana || [];
        var mes = obj.mes || [];

        charts['chart-prod-evolucion'] = new ApexCharts(get('chart-prod-evolucion'), {
            chart: { type: 'line', height: 340, toolbar: { show: true } },
            series: [
                { name: 'Ventas por día', data: dia.map(function (r) { return parseFloat(r.total || 0); }) },
                { name: 'Ventas por semana', data: semana.map(function (r) { return parseFloat(r.total || 0); }) },
                { name: 'Ventas por mes', data: mes.map(function (r) { return parseFloat(r.total || 0); }) }
            ],
            xaxis: { categories: dia.map(function (r) { return r.periodo; }) },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            stroke: { curve: 'smooth', width: 2 },
            colors: ['#EC401B', '#1cc88a', '#4e73df']
        });
        charts['chart-prod-evolucion'].render();
    }

    function _renderProdTopClientes(rows) {
        destroyChart('chart-prod-top-clientes');
        if (!rows.length) return;
        charts['chart-prod-top-clientes'] = new ApexCharts(get('chart-prod-top-clientes'), {
            chart: {
                type: 'bar', height: 320, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        /* Sin accion: el filtro cliente ya no existe en la vista simplificada. */
                    }
                }
            },
            series: [{ name: 'Monto', data: rows.map(function (r) { return parseFloat(r.monto || 0); }) }],
            xaxis: {
                categories: rows.map(function (r) { return r.cliente; }),
                labels: { formatter: function (v) { return 'L.' + fmtN(v); } }
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            colors: ['#36b9cc']
        });
        charts['chart-prod-top-clientes'].render();
    }

    function _renderProdParticipacion(p) {
        destroyChart('chart-prod-participacion');
        charts['chart-prod-participacion'] = new ApexCharts(get('chart-prod-participacion'), {
            chart: { type: 'donut', height: 340 },
            series: [parseFloat(p.producto || 0), parseFloat(p.resto || 0)],
            labels: ['Producto seleccionado', 'Resto de ventas'],
            colors: ['#EC401B', '#858796'],
            tooltip: { y: { formatter: function (v) { return fmt(v); } } }
        });
        charts['chart-prod-participacion'].render();
    }

    function _renderProdComparativo(rows) {
        destroyChart('chart-prod-comparativo');
        if (!rows.length) return;
        var top3 = rows.slice(0, 3);
        charts['chart-prod-comparativo'] = new ApexCharts(get('chart-prod-comparativo'), {
            chart: {
                type: 'bar', height: 320, toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var r = top3[cfg.dataPointIndex];
                        if (r && r.producto_id) {
                            $('#prod-filtro-producto').val(r.producto_id).trigger('change');
                            cargarDashboardProductos(false);
                        }
                    }
                }
            },
            series: [{ name: 'Total', data: top3.map(function (r) { return parseFloat(r.total || 0); }) }],
            xaxis: {
                categories: top3.map(function (r) { return r.producto; }),
                labels: { formatter: function (v) { return 'L.' + fmtN(v); } }
            },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            plotOptions: { bar: { distributed: true, borderRadius: 4, horizontal: true } },
            colors: ['#EC401B', '#1cc88a', '#36b9cc'],
            legend: { show: false },
            dataLabels: { enabled: true, formatter: function (v) { return fmt(v); }, style: { fontSize: '11px' } }
        });
        charts['chart-prod-comparativo'].render();
    }

    function _renderProdTopVendedores(rows) {
        destroyChart('chart-prod-top-vendedores');
        if (!rows || !rows.length) {
            var el = get('chart-prod-top-vendedores');
            if (el) el.innerHTML = '<p class="text-muted text-center py-4">Sin datos para los filtros seleccionados.</p>';
            return;
        }
        charts['chart-prod-top-vendedores'] = new ApexCharts(get('chart-prod-top-vendedores'), {
            chart: { type: 'bar', height: 320, toolbar: { show: false } },
            series: [
                { name: 'Monto (L.)', data: rows.map(function (r) { return parseFloat(r.monto || 0); }) },
                { name: 'Unidades',   data: rows.map(function (r) { return parseFloat(r.unidades || 0); }) }
            ],
            xaxis: { categories: rows.map(function (r) { return r.vendedor; }) },
            yaxis: [
                { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
                { opposite: true, labels: { formatter: function (v) { return fmtN(v) + ' u.'; } } }
            ],
            tooltip: {
                y: [
                    { formatter: function (v) { return fmt(v); } },
                    { formatter: function (v) { return fmtN(v) + ' unidades'; } }
                ]
            },
            colors: ['#EC401B', '#36b9cc'],
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
            legend: { position: 'top' },
            dataLabels: { enabled: false }
        });
        charts['chart-prod-top-vendedores'].render();
    }

    function _renderProdTendencia(rows) {
        destroyChart('chart-prod-tendencia');
        if (!rows.length) return;
        charts['chart-prod-tendencia'] = new ApexCharts(get('chart-prod-tendencia'), {
            chart: { type: 'area', height: 280, toolbar: { show: false } },
            series: [{ name: 'Unidades', data: rows.map(function (r) { return parseFloat(r.unidades || 0); }) }],
            xaxis: { categories: rows.map(function (r) { return r.periodo; }) },
            colors: ['#1cc88a'],
            stroke: { curve: 'smooth' }
        });
        charts['chart-prod-tendencia'].render();
    }

    function _renderProdFacturas(rows) {
        _dtDestroy('tabla-prod-fact-det');
        var $tbody = $('#tbody-prod-fact-det').empty();
        rows.forEach(function (r, i) {
            var facturaHtml = '<a href="#" onclick="dashboardVentas.imprimirFactura(' + r.factura_id + '); return false;" style="font-weight:700;color:#1a7efb;">' + (r.numero_factura || ('FAC-' + r.factura_id)) + '</a>';
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + facturaHtml + '</td>' +
                '<td>' + (r.fecha || '-') + '</td>' +
                '<td>' + (r.cliente || '-') + '</td>' +
                '<td>' + (r.escala || '-') + '</td>' +
                '<td>' + (r.vendedor || '-') + '</td>' +
                '<td>' + (r.producto || '-') + '</td>' +
                '<td class="text-right">' + fmtN(r.cantidad) + '</td>' +
                '<td class="text-right">' + fmt(r.precio_base_venta) + '</td>' +
                '<td class="text-right">' + fmt(r.precio_unitario) + '</td>' +
                '<td class="text-right">' + fmt(r.descuento) + '</td>' +
                '<td class="text-right">' + fmt(r.subtotal) + '</td>' +
                '<td class="text-right">' + fmt(r.costo_total) + '</td>' +
                '<td class="text-right">' + fmt(r.utilidad_bruta) + '</td>' +
                '<td class="text-right">' + fmt(r.total_factura) + '</td>' +
                '<td>' + (r.estado || '-') + '</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-prod-fact-det', 1);
    }

    function _renderProdProductosCliente(rows) {
        /* Tabla removida — el filtro de cliente fue eliminado */
    }

    function _renderProdRankingClientes(rows) {
        _dtDestroy('tabla-prod-ranking-cli');
        var $tbody = $('#tbody-prod-ranking-cli').empty();
        if (!rows || !rows.length) {
            $tbody.append('<tr><td colspan="6" class="text-center text-muted">Sin datos para los filtros seleccionados.</td></tr>');
            return;
        }
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (r.cliente || '-') + '</td>' +
                '<td class="text-right">' + fmtN(r.compras) + '</td>' +
                '<td class="text-right font-weight-bold">' + fmt(r.monto) + '</td>' +
                '<td class="text-right">' + fmtN(r.unidades) + '</td>' +
                '<td>' + (r.ultima_compra || '-') + '</td>' +
                '</tr>'
            );
        });
        _dts['tabla-prod-ranking-cli'] = $('#tabla-prod-ranking-cli').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            language:   DT_LANG,
            dom:        DT_DOM,
            responsive: true,
            order:      [[3, 'desc']]
        });
    }

    function _renderProdIndicadoresClientes(ind) {
        var a = ind.cliente_mas_compra ? (ind.cliente_mas_compra.cliente + ' (' + fmt(ind.cliente_mas_compra.monto) + ')') : '-';
        var b = ind.cliente_mayor_frecuencia ? (ind.cliente_mayor_frecuencia.cliente + ' (' + fmtN(ind.cliente_mayor_frecuencia.compras) + ' compras)') : '-';
        var c = ind.cliente_mayor_volumen ? (ind.cliente_mayor_volumen.cliente + ' (' + fmtN(ind.cliente_mayor_volumen.unidades) + ' unidades)') : '-';
        setText('prod-ind-mas-compra', a);
        setText('prod-ind-frecuencia', b);
        setText('prod-ind-volumen', c);
    }

    function _renderProdRelacionados(rows) {
        _dtDestroy('tabla-prod-rel');
        var $tbody = $('#tbody-prod-rel').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (r.producto || '-') + '</td>' +
                '<td class="text-right">' + fmtN(r.veces_juntos) + '</td>' +
                '<td class="text-right">' + fmt(r.total_generado) + '</td>' +
                '<td class="text-right">' + fmtN(r.porcentaje_coincidencia) + '%</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-prod-rel', 2);
    }

    function limpiarDashboardProductos() {
        $('#prod-filtro-producto').val('').trigger('change');
        $('#prod-fi').val(new Date().getFullYear() + '-01-01');
        $('#prod-ff').val(todayStr());
        _renderProdKpis({});
        _renderProdResumenSeleccionado(null);
        _renderProdEvolucion({});
        _renderProdTopClientes([]);
        _renderProdFacturas([]);
        _renderProdProductosCliente([]);
        _renderProdRankingClientes([]);
        _renderProdIndicadoresClientes({});
        _renderProdTopVendedores([]);
        _renderTablaProductos([]);
        setText('prod-escala', 'Sin aplicar');
    }

    function exportarTablaProductosExcel(tableId, fileName, sheetName) {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no esta disponible.'); return; }

        var HNL_FMT = '"L." #,##0.00';
        var INT_FMT = '#,##0';

        function _parseNumCell(txt) {
            var m = String(txt || '').match(/-?\d[\d,]*\.?\d*/);
            if (!m) return null;
            var n = parseFloat(m[0].replace(/,/g, ''));
            return isNaN(n) ? null : n;
        }
        function _isMoneyHeader(h) {
            var t = String(h || '').toLowerCase();
            return t.indexOf('precio') >= 0 ||
                t.indexOf('subtotal') >= 0 ||
                t.indexOf('total') >= 0 ||
                t.indexOf('monto') >= 0 ||
                t.indexOf('costo') >= 0 ||
                t.indexOf('utilidad') >= 0 ||
                t.indexOf('ticket') >= 0 ||
                t.indexOf('ingresos') >= 0 ||
                t.indexOf('comprado') >= 0;
        }
        function _isIntegerHeader(h) {
            var t = String(h || '').toLowerCase();
            return t.indexOf('cantidad') >= 0 ||
                t.indexOf('unidades') >= 0 ||
                t.indexOf('compras') >= 0 ||
                t.indexOf('facturas') >= 0 ||
                t.indexOf('existencia') >= 0 ||
                t.indexOf('dias') >= 0;
        }
        function _getRowsForExport(id) {
            var dt = _dts[id] || null;
            if (!dt && $.fn.dataTable && $.fn.dataTable.isDataTable('#' + id)) {
                dt = $('#' + id).DataTable();
            }
            if (dt) {
                return dt.rows({ search: 'applied' }).nodes().toArray();
            }
            return $('#' + id + ' tbody tr').toArray();
        }

        var table = document.getElementById(tableId);
        if (!table) { alert('No se encontro la tabla a exportar.'); return; }

        var wb = new ExcelJS.Workbook();
        var ws = wb.addWorksheet(sheetName || 'Datos');

        var headers = [];
        $(table).find('thead th').each(function () {
            headers.push($(this).text().trim());
        });
        if (headers.length) ws.addRow(headers);

        var moneyCols = [];
        var intCols = [];
        headers.forEach(function (h, i) {
            if (_isMoneyHeader(h)) moneyCols.push(i + 1);
            else if (_isIntegerHeader(h)) intCols.push(i + 1);
        });

        $(_getRowsForExport(tableId)).each(function () {
            var row = [];
            $(this).find('td').each(function (idx) {
                var txt = $(this).text().trim();
                if (moneyCols.indexOf(idx + 1) >= 0 || intCols.indexOf(idx + 1) >= 0) {
                    var n = _parseNumCell(txt);
                    row.push(n === null ? txt : n);
                } else {
                    row.push(txt);
                }
            });
            var excelRow = ws.addRow(row);
            moneyCols.forEach(function (colNum) {
                var cell = excelRow.getCell(colNum);
                if (typeof cell.value === 'number') {
                    cell.numFmt = HNL_FMT;
                }
            });
            intCols.forEach(function (colNum) {
                var cell = excelRow.getCell(colNum);
                if (typeof cell.value === 'number') {
                    cell.numFmt = INT_FMT;
                }
            });
        });

        ws.columns.forEach(function (col) { col.width = 22; });
        _descargar(wb, fileName || 'tabla-productos.xlsx');
    }

    function exportarVistaProductosExcel() {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no esta disponible.'); return; }

        var HNL_FMT = '"L." #,##0.00';
        var INT_FMT = '#,##0';

        function _parseNumCell(txt) {
            var m = String(txt || '').match(/-?\d[\d,]*\.?\d*/);
            if (!m) return null;
            var n = parseFloat(m[0].replace(/,/g, ''));
            return isNaN(n) ? null : n;
        }
        function _isMoneyHeader(h) {
            var t = String(h || '').toLowerCase();
            return t.indexOf('precio') >= 0 ||
                t.indexOf('subtotal') >= 0 ||
                t.indexOf('total') >= 0 ||
                t.indexOf('monto') >= 0 ||
                t.indexOf('costo') >= 0 ||
                t.indexOf('utilidad') >= 0 ||
                t.indexOf('ticket') >= 0 ||
                t.indexOf('ingresos') >= 0 ||
                t.indexOf('comprado') >= 0;
        }
        function _isIntegerHeader(h) {
            var t = String(h || '').toLowerCase();
            return t.indexOf('cantidad') >= 0 ||
                t.indexOf('unidades') >= 0 ||
                t.indexOf('compras') >= 0 ||
                t.indexOf('facturas') >= 0 ||
                t.indexOf('existencia') >= 0 ||
                t.indexOf('dias') >= 0;
        }
        function _getRowsForExport(id) {
            var dt = _dts[id] || null;
            if (!dt && $.fn.dataTable && $.fn.dataTable.isDataTable('#' + id)) {
                dt = $('#' + id).DataTable();
            }
            if (dt) {
                return dt.rows({ search: 'applied' }).nodes().toArray();
            }
            return $('#' + id + ' tbody tr').toArray();
        }

        var wb = new ExcelJS.Workbook();
        var tablas = [
            { id: 'tabla-prod-fact-det', sheet: 'Ultimas Facturas' },
            { id: 'tabla-prod-ranking-cli', sheet: 'Historico Clientes' },
            { id: 'tabla-productos', sheet: 'Tabla Producto Completa' }
        ];

        tablas.forEach(function (cfg) {
            var table = document.getElementById(cfg.id);
            if (!table) return;

            var ws = wb.addWorksheet(cfg.sheet);
            var headers = [];
            $(table).find('thead th').each(function () {
                headers.push($(this).text().trim());
            });
            if (headers.length) ws.addRow(headers);

            var moneyCols = [];
            var intCols = [];
            headers.forEach(function (h, i) {
                if (_isMoneyHeader(h)) moneyCols.push(i + 1);
                else if (_isIntegerHeader(h)) intCols.push(i + 1);
            });

            $(_getRowsForExport(cfg.id)).each(function () {
                var row = [];
                $(this).find('td').each(function (idx) {
                    var txt = $(this).text().trim();
                    if (moneyCols.indexOf(idx + 1) >= 0 || intCols.indexOf(idx + 1) >= 0) {
                        var n = _parseNumCell(txt);
                        row.push(n === null ? txt : n);
                    } else {
                        row.push(txt);
                    }
                });
                var excelRow = ws.addRow(row);
                moneyCols.forEach(function (colNum) {
                    var cell = excelRow.getCell(colNum);
                    if (typeof cell.value === 'number') {
                        cell.numFmt = HNL_FMT;
                    }
                });
                    intCols.forEach(function (colNum) {
                        var cell = excelRow.getCell(colNum);
                        if (typeof cell.value === 'number') {
                            cell.numFmt = INT_FMT;
                        }
                    });
            });

            ws.columns.forEach(function (col) { col.width = 22; });
        });

        _descargar(wb, 'vista-completa-productos.xlsx');
    }

    function exportarFacturasProductoExcel() {
        exportarTablaProductosExcel('tabla-prod-fact-det', 'dashboard-productos-facturas.xlsx', 'Facturas Producto');
    }

    function exportarFacturasProductoPDF() {
        var html = '<html><head><title>Facturas Producto</title><style>table{width:100%;border-collapse:collapse;font-size:11px}th,td{border:1px solid #ddd;padding:4px}th{background:#f3f3f3}</style></head><body>' +
                   '<h3>Detalle de Facturas de Producto</h3>' +
                   document.getElementById('tabla-prod-fact-det').outerHTML +
                   '</body></html>';
        var w = window.open('', '_blank');
        w.document.write(html);
        w.document.close();
        w.focus();
        w.print();
    }

    function imprimirFactura(idFactura) {
        if (!idFactura) return;
        window.open('/factura/cooporativo/' + idFactura, '_blank');
    }

    /* ═══════════════════════════════════════════════════════════════════════
       COMPARAR VENDEDORES
    ═══════════════════════════════════════════════════════════════════════ */
    var _vendedoresCatalogo = [];

    function _renderVendChecks(vendedores) {
        _vendedoresCatalogo = vendedores;
        var $cont = $('#cmp-vend-checks').empty();
        vendedores.forEach(function (v) {
            $cont.append(
                '<span>' +
                '<input type="checkbox" class="cmp-vend-check" id="cmp-v-' + v.id + '" value="' + v.id + '">' +
                '<label class="cmp-vend-label mb-0" for="cmp-v-' + v.id + '">' + v.name + '</label>' +
                '</span>'
            );
        });
    }

    function _getVendsSeleccionados() {
        var ids = [];
        $('#cmp-vend-checks input:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function cargarComparacion() {
        var ids = _getVendsSeleccionados();
        if (!ids.length) {
            alert('Seleccione al menos un asesor comercial para comparar.');
            return;
        }

        var _n = new Date();
        var fi = $('#cmp-fi').val() || (_n.getFullYear() + '-' + String(_n.getMonth() + 1).padStart(2, '0') + '-01');
        var ff = $('#cmp-ff').val() || todayStr();

        /* KPI por cada vendedor */
        $.get('/reporte/dashboard/top-vendedores', { fecha_inicio: fi, fecha_final: ff }).then(function (allRows) {
            var rows = allRows.filter(function (r) { return ids.indexOf(String(r.vendedor_id)) >= 0; });
            _renderCmpKpis(rows);
            _renderCmpTotal(rows);
            _renderCmpPart(rows);
        });

        /* Evolución mensual */
        $.get('/reporte/dashboard/ventas-mes-vendedores', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedores:   ids
        }).then(function (rows) {
            _renderCmpEvolucion(rows, ids);
        });

        /* Escalas por vendedor */
        $.get('/reporte/dashboard/escalas-comparacion', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedores:   ids.join(',')
        }).then(function (vendors) {
            _renderResumenEscalas(vendors, fi, ff);
        });
    }

    function _renderResumenEscalas(vendors, fi, ff) {
        var $tabs    = $('#cmp-esc-tabs').empty();
        var $content = $('#cmp-esc-content').empty();
        var $empty   = $('#cmp-esc-empty');

        if (!vendors || !vendors.length) {
            $empty.show();
            return;
        }
        $empty.hide();

        var palette = _palette(vendors.length);
        vendors.forEach(function (vd, i) {
            var color   = palette[i];
            var tabId   = 'cmp-esc-tab-' + i;
            var paneId  = 'cmp-esc-pane-' + i;
            var active  = i === 0 ? ' active show' : '';
            var ariaSelected = i === 0 ? 'true' : 'false';

            /* Tab */
            $tabs.append(
                '<li class="nav-item">' +
                '<a class="nav-link' + (i === 0 ? ' active' : '') + '" id="' + tabId + '"' +
                ' data-toggle="tab" href="#' + paneId + '" role="tab" aria-selected="' + ariaSelected + '"' +
                ' style="font-weight:700; color:' + color + '">' +
                vd.vendedor +
                '</a></li>'
            );

            /* Tabla de escalas */
            var rows = '';
            vd.escalas.forEach(function (esc) {
                var barWidth = Math.max(esc.pct, 2);
                rows +=
                    '<tr>' +
                    '<td class="font-weight-bold">' + esc.escala + '</td>' +
                    '<td class="text-right">' +
                    '<a href="#" class="cmp-esc-fact-link font-weight-bold" style="color:#EC401B"' +
                    ' data-fi="' + fi + '" data-ff="' + ff + '"' +
                    ' data-vend-id="' + vd.vendedor_id + '" data-esc-id="' + esc.escala_id + '"' +
                    ' data-vend-nombre="' + vd.vendedor + '" data-esc-nombre="' + esc.escala + '">' +
                    esc.facturas + '</a></td>' +
                    '<td class="text-right font-weight-bold">' + fmt(esc.total_sin_isv) + '</td>' +
                    '<td style="min-width:120px">' +
                    '<div style="background:#e9ecef;border-radius:4px;overflow:hidden;height:16px">' +
                    '<div style="width:' + barWidth + '%;background:' + color + ';height:100%;border-radius:4px;transition:width .4s"></div>' +
                    '</div>' +
                    '<small class="text-muted ml-1">' + esc.pct + '%</small>' +
                    '</td>' +
                    '</tr>';
            });

            /* Total row */
            rows +=
                '<tr class="font-weight-bold" style="border-top:2px solid ' + color + '">' +
                '<td>TOTAL</td><td class="text-right">' + vd.escalas.reduce(function(s,e){return s+e.facturas;},0) + '</td>' +
                '<td class="text-right">' + fmt(vd.total) + '</td>' +
                '<td><small class="text-muted">100%</small></td>' +
                '</tr>';

            $content.append(
                '<div class="tab-pane fade' + active + '" id="' + paneId + '" role="tabpanel">' +
                '<div class="table-responsive mt-3">' +
                '<table class="table table-sm table-bordered">' +
                '<thead class="thead-dark"><tr>' +
                '<th>Escala de Precio</th>' +
                '<th class="text-right">Facturas</th>' +
                '<th class="text-right">Facturación sin ISV</th>' +
                '<th>Participación</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '</table></div></div>'
            );
        });

        /* Delegación: click en nº de facturas → modal facturas */
        $content.off('click', '.cmp-esc-fact-link').on('click', '.cmp-esc-fact-link', function (e) {
            e.preventDefault();
            var $a = $(this);
            _abrirFacturasComparacion(
                $a.data('fi'), $a.data('ff'),
                $a.data('vend-id'), $a.data('esc-id'),
                $a.data('vend-nombre'), $a.data('esc-nombre')
            );
        });
    }

    function _renderCmpKpis(rows) {
        var $cont = $('#cmp-kpi-cards').empty();
        var colors = _palette(rows.length);
        rows.forEach(function (r, i) {
            var color = colors[i];
            $cont.append(
                '<div class="mb-2 col-sm-6 col-md-4 col-lg-3">' +
                '<div class="card h-100" style="border-left:4px solid ' + color + '">' +
                '<div class="px-3 py-2 card-body">' +
                '<div class="mb-1 text-xs font-weight-bold text-uppercase text-truncate" style="color:' + color + '">' + r.vendedor + '</div>' +
                '<div class="mb-0 h5 font-weight-bold">' + fmt(r.total_sin_isv) + '</div>' +
                '<small class="text-muted">Facturación sin ISV</small><br>' +
                '<small class="text-muted">' + r.facturas + ' facturas · ' + r.clientes_atendidos + ' clientes</small>' +
                '</div></div></div>'
            );
        });
    }

    function _renderCmpEvolucion(rows, ids) {
        destroyChart('chart-cmp-evolucion');
        if (!rows || !rows.length) return;

        /* Agrupar por vendedor manteniendo orden de ids seleccionados */
        var byVend = {};
        var vendOrder = [];
        rows.forEach(function (r) {
            if (!byVend[r.vendedor]) { byVend[r.vendedor] = Array(12).fill(0); vendOrder.push(r.vendedor); }
            byVend[r.vendedor][r.mes - 1] = parseFloat(r.total);
        });

        var series = vendOrder.map(function (name) {
            return { name: name, data: byVend[name] };
        });

        charts['chart-cmp-evolucion'] = new ApexCharts(get('chart-cmp-evolucion'), {
            chart: { type: 'line', height: 380, toolbar: { show: true }, zoom: { enabled: true } },
            series: series,
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            stroke: { curve: 'smooth', width: 2 },
            colors: _palette(series.length),
            markers: { size: 4 },
            legend: { position: 'top' }
        });
        charts['chart-cmp-evolucion'].render();
    }

    function _renderCmpTotal(rows) {
        destroyChart('chart-cmp-total');
        if (!rows || !rows.length) return;

        charts['chart-cmp-total'] = new ApexCharts(get('chart-cmp-total'), {
            chart: { type: 'bar', height: Math.max(300, rows.length * 40), toolbar: { show: false } },
            series: [{ name: 'Facturación sin ISV', data: rows.map(function (r) { return parseFloat(r.total_sin_isv); }) }],
            xaxis: { categories: rows.map(function (r) { return r.vendedor; }) },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: _palette(rows.length),
            plotOptions: { bar: { borderRadius: 4, distributed: true, columnWidth: '55%' } },
            legend: { show: false }
        });
        charts['chart-cmp-total'].render();
    }

    function _renderCmpPart(rows) {
        destroyChart('chart-cmp-part');
        if (!rows || !rows.length) return;

        charts['chart-cmp-part'] = new ApexCharts(get('chart-cmp-part'), {
            chart: { type: 'donut', height: 300 },
            series: rows.map(function (r) { return parseFloat(r.total_sin_isv); }),
            labels:  rows.map(function (r) { return r.vendedor; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: _palette(rows.length),
            legend: { position: 'right' }
        });
        charts['chart-cmp-part'].render();
    }

    function _renderTablaComparacion(rows, fi, ff) {
        _dtDestroy('tabla-comparacion');
        var $tbody = $('#tbody-comparacion').empty();
        rows.forEach(function (r, i) {
            var rowColor = COLORES[i % COLORES.length];
            $tbody.append(
                '<tr>' +
                '<td><span class="font-weight-bold" style="color:' + rowColor + '">' + r.vendedor + '</span></td>' +
                '<td class="text-right">' + r.facturas + '</td>' +
                '<td class="text-right">' + r.clientes_atendidos + '</td>' +
                '<td class="text-right font-weight-bold">' + fmt(r.total_ventas) + '</td>' +
                '<td class="text-right">' + fmt(r.ticket_promedio) + '</td>' +
                '<td class="text-right">' + r.participacion + '%</td>' +
                '<td>—</td>' +
                '</tr>'
            );
        });
        _dtInit('tabla-comparacion');
    }

    /* ─── Comparar Vendedores: Modal Facturas ──────────────────────────── */
    var _cmpFactParams = {};   // fi, ff, vendedor_id, escala_id guardados para el back
    var _cmpFactAllRows = [];  // todas las filas para paginación
    var _cmpFactPage = 1;
    var _cmpFactPerPage = 6;

    function _renderFactPage(page) {
        _cmpFactPage = page;
        var rows = _cmpFactAllRows;
        var total = rows.length;
        var pages = Math.ceil(total / _cmpFactPerPage);
        var start = (page - 1) * _cmpFactPerPage;
        var slice = rows.slice(start, start + _cmpFactPerPage);

        var $tbody = $('#tbody-cmp-facturas').empty();
        slice.forEach(function (r) {
            $tbody.append(
                '<tr>' +
                '<td><a href="#" class="font-weight-bold cmp-ver-productos" style="color:#EC401B"' +
                ' data-factura-id="' + r.factura_id + '"' +
                ' data-documento="' + (r.documento || r.factura_id) + '">' +
                (r.documento || 'FAC-' + r.factura_id) + '</a></td>' +
                '<td>' + r.fecha + '</td>' +
                '<td>' + r.cliente + '</td>' +
                '<td><span class="badge badge-secondary">' + r.cat_cliente + '</span></td>' +
                '<td>' + r.tipo_cliente + '</td>' +
                '<td class="text-right">' + r.lineas + '</td>' +
                '<td class="text-right font-weight-bold">' + fmt(r.total_sin_isv) + '</td>' +
                '<td class="text-right text-muted">' + fmt(r.isv) + '</td>' +
                '<td class="text-right font-weight-bold">' + fmt(r.total_con_isv) + '</td>' +
                '</tr>'
            );
        });

        /* Info */
        $('#cmp-fact-pag-info').text('Mostrando ' + (start + 1) + '-' + Math.min(start + _cmpFactPerPage, total) + ' de ' + total + ' facturas');

        /* Paginación */
        var $links = $('#cmp-fact-pag-links').empty();
        $links.append(
            '<li class="page-item' + (page === 1 ? ' disabled' : '') + '">' +
            '<a class="page-link" href="#" data-p="' + (page - 1) + '">&laquo;</a></li>'
        );
        var from = Math.max(1, page - 2);
        var to   = Math.min(pages, page + 2);
        for (var p = from; p <= to; p++) {
            $links.append(
                '<li class="page-item' + (p === page ? ' active' : '') + '">' +
                '<a class="page-link" href="#" data-p="' + p + '">' + p + '</a></li>'
            );
        }
        $links.append(
            '<li class="page-item' + (page === pages ? ' disabled' : '') + '">' +
            '<a class="page-link" href="#" data-p="' + (page + 1) + '">&raquo;</a></li>'
        );

        $('#cmp-fact-pagination').css('display', 'flex');
    }

    function _abrirFacturasComparacion(fi, ff, vendedorId, escalaId, vendedorNombre, escalaNombre) {
        _cmpFactParams = { fi: fi, ff: ff, vendedor_id: vendedorId, escala_id: escalaId };
        _cmpFactAllRows = [];
        _cmpFactPage = 1;

        $('#modal-cmp-facturas-title').html(
            '<i class="fas fa-file-invoice mr-2"></i>' + vendedorNombre +
            ' &mdash; <small>' + escalaNombre + '</small>'
        );
        $('#modal-cmp-fact-kpis').html('<span class="text-muted small">Cargando...</span>');
        $('#tbody-cmp-facturas').html('<tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        $('#cmp-fact-pagination').css('display', 'none');
        $('#modal-cmp-facturas').modal('show');

        $.get('/reporte/dashboard/facturas-comparacion', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedor_id:  vendedorId,
            escala_id:    escalaId
        }).then(function (rows) {
            _cmpFactAllRows = rows;
            var totalSinIsv = rows.reduce(function (s, r) { return s + r.total_sin_isv; }, 0);
            var totalCon    = rows.reduce(function (s, r) { return s + r.total_con_isv; }, 0);

            $('#modal-cmp-fact-kpis').html(
                '<span class="font-weight-bold mr-3"><i class="fas fa-file-invoice text-muted mr-1"></i>' + rows.length + ' facturas</span>' +
                '<span class="font-weight-bold mr-3 text-info"><i class="fas fa-dollar-sign mr-1"></i>Sin ISV: ' + fmt(totalSinIsv) + '</span>' +
                '<span class="font-weight-bold text-secondary"><i class="fas fa-dollar-sign mr-1"></i>Con ISV: ' + fmt(totalCon) + '</span>'
            );

            _renderFactPage(1);

            /* Click paginación */
            $('#cmp-fact-pag-links').off('click').on('click', 'a.page-link', function (e) {
                e.preventDefault();
                var p = parseInt($(this).data('p'));
                var pages = Math.ceil(_cmpFactAllRows.length / _cmpFactPerPage);
                if (p >= 1 && p <= pages) _renderFactPage(p);
            });

            /* Click en documento → modal productos */
            $('#tbody-cmp-facturas').off('click', '.cmp-ver-productos').on('click', '.cmp-ver-productos', function (e) {
                e.preventDefault();
                _abrirProductosFactura($(this).data('factura-id'), $(this).data('documento'));
            });

            /* Botón Excel facturas — exporta TODAS las filas, no sólo la página visible */
            $('#btn-cmp-fact-excel').off('click').on('click', function () {
                _exportarFacturasComparacionExcel();
            });
        }).catch(function () {
            $('#tbody-cmp-facturas').html('<tr><td colspan="9" class="text-center text-danger">Error al cargar facturas.</td></tr>');
        });
    }

    function _exportarFacturasComparacionExcel() {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no está disponible.'); return; }
        var rows = _cmpFactAllRows;
        if (!rows || !rows.length) { alert('No hay datos para exportar.'); return; }

        var HNL_FMT = '"L." #,##0.00';
        var titulo = $('#modal-cmp-facturas-title').text().replace(/\s+/g, ' ').trim();
        var wb = new ExcelJS.Workbook();
        var ws = wb.addWorksheet('Facturas');

        var headers = ['Documento','Fecha','Cliente','Cat. Cliente','Tipo Cliente','Líneas','Sin ISV','ISV','Total'];
        var headerRow = ws.addRow(headers);
        headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF343A40' } };

        rows.forEach(function (r) {
            var row = ws.addRow([
                r.documento || ('FAC-' + r.factura_id),
                r.fecha,
                r.cliente,
                r.cat_cliente,
                r.tipo_cliente,
                r.lineas,
                r.total_sin_isv,
                r.isv,
                r.total_con_isv
            ]);
            [7, 8, 9].forEach(function (c) {
                var cell = row.getCell(c);
                if (typeof cell.value === 'number') cell.numFmt = HNL_FMT;
            });
        });

        ws.columns.forEach(function (col, i) {
            col.width = [18, 12, 30, 16, 16, 8, 14, 12, 14][i] || 16;
        });

        var sinISVTot = rows.reduce(function (s, r) { return s + r.total_sin_isv; }, 0);
        var isvTot    = rows.reduce(function (s, r) { return s + r.isv; }, 0);
        var conTot    = rows.reduce(function (s, r) { return s + r.total_con_isv; }, 0);
        var totRow = ws.addRow(['', '', '', '', 'TOTAL', rows.length, sinISVTot, isvTot, conTot]);
        totRow.font = { bold: true };
        [7, 8, 9].forEach(function (c) {
            var cell = totRow.getCell(c);
            if (typeof cell.value === 'number') cell.numFmt = HNL_FMT;
        });

        var fecha = new Date().toISOString().slice(0, 10);
        _descargar(wb, 'facturas-comparacion-' + fecha + '.xlsx');
    }

    function _abrirProductosFactura(facturaId, docNombre) {
        $('#modal-cmp-prod-title').html('<i class="fas fa-boxes mr-2"></i>Productos — ' + docNombre);
        $('#modal-cmp-prod-header').html('<span class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando...</span>');
        $('#tbody-cmp-productos').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        $('#tfoot-cmp-total').text('');
        $('#btn-cmp-ver-factura').attr('href', '/factura/cooporativo/' + facturaId);

        /* Esperar que Modal 1 cierre completamente antes de abrir Modal 2
           para evitar el backdrop negro doble */
        $('#modal-cmp-facturas').one('hidden.bs.modal', function () {
            $('#modal-cmp-productos').modal('show');
        });
        $('#modal-cmp-facturas').modal('hide');

        $.get('/reporte/dashboard/productos-factura-comparacion', { factura_id: facturaId }).then(function (data) {
            var h = data.header || {};
            $('#modal-cmp-prod-header').html(
                '<span><i class="fas fa-calendar-alt text-muted mr-1"></i><strong>' + (h.fecha || '') + '</strong></span>' +
                '<span><i class="fas fa-user mr-1 text-muted"></i>' + (h.cliente || '') + '</span>' +
                '<span><span class="badge badge-secondary mr-1">' + (h.cat_cliente || '') + '</span>' + (h.tipo_cliente || '') + '</span>' +
                '<span><i class="fas fa-user-tie text-muted mr-1"></i>' + (h.vendedor || '') + '</span>'
            );

            var $tbody = $('#tbody-cmp-productos').empty();
            var total = 0;
            (data.productos || []).forEach(function (p) {
                total += p.subtotal_sin_isv;
                $tbody.append(
                    '<tr>' +
                    '<td><code>' + (p.codigo || '—') + '</code></td>' +
                    '<td>' + p.producto + '</td>' +
                    '<td><span class="badge badge-info">' + p.escala_precio + '</span></td>' +
                    '<td><span class="badge badge-secondary">' + p.cat_cliente + '</span></td>' +
                    '<td>' + p.tipo_cliente + '</td>' +
                    '<td class="text-right">' + fmt(p.precio_unitario) + '</td>' +
                    '<td class="text-right">' + p.cantidad + '</td>' +
                    '<td class="text-right font-weight-bold">' + fmt(p.subtotal_sin_isv) + '</td>' +
                    '</tr>'
                );
            });
            $('#tfoot-cmp-total').text(fmt(total));
        }).catch(function () {
            $('#tbody-cmp-productos').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar productos.</td></tr>');
        });

        /* Botón volver y X — ambos regresan a Modal 1 */
        var _volverAFacturas = function () {
            $('#modal-cmp-productos').one('hidden.bs.modal', function () {
                $('#modal-cmp-facturas').modal('show');
            });
            $('#modal-cmp-productos').modal('hide');
        };
        $('#btn-cmp-prod-back').off('click').on('click', _volverAFacturas);
        $('#btn-cmp-prod-x').off('click').on('click', _volverAFacturas);

        /* Botón Excel productos */
        $('#btn-cmp-prod-excel').off('click').on('click', function () {
            _exportarProductosFacturaExcel(docNombre);
        });
    }

    function _exportarProductosFacturaExcel(docNombre) {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no está disponible.'); return; }
        var HNL_FMT = '"L." #,##0.00';
        var wb = new ExcelJS.Workbook();
        var ws = wb.addWorksheet('Productos');

        var headers = ['Código','Producto','Escala de Precio','Cat. Cliente','Tipo Cliente','Precio Unit.','Cantidad','Subtotal sin ISV'];
        var headerRow = ws.addRow(headers);
        headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEC401B' } };

        function _parseMoney(txt) {
            /* fmt() produces "L. 1,234.56" — extract the numeric part directly */
            var m = String(txt).match(/-?[\d,]+\.?\d*/);
            if (!m) return 0;
            var n = parseFloat(m[0].replace(/,/g, ''));
            return isNaN(n) ? 0 : n;
        }

        var total = 0;
        $('#tbody-cmp-productos tr').each(function () {
            var cells = $(this).find('td');
            if (!cells.length) return;
            var subtotal = _parseMoney(cells.eq(7).text());
            total += subtotal;
            var row = ws.addRow([
                cells.eq(0).text().trim(),
                cells.eq(1).text().trim(),
                cells.eq(2).text().trim(),
                cells.eq(3).text().trim(),
                cells.eq(4).text().trim(),
                _parseMoney(cells.eq(5).text()),
                _parseMoney(cells.eq(6).text()),
                subtotal
            ]);
            [6, 8].forEach(function (c) {
                var cell = row.getCell(c);
                if (typeof cell.value === 'number') cell.numFmt = HNL_FMT;
            });
        });

        var totRow = ws.addRow(['', '', '', '', '', '', 'TOTAL', total]);
        totRow.font = { bold: true };
        totRow.getCell(8).numFmt = HNL_FMT;

        ws.columns.forEach(function (col, i) {
            col.width = [10, 34, 18, 16, 14, 14, 10, 16][i] || 14;
        });

        var safeName = (docNombre || 'factura').replace(/[\\\/\*\?\[\]]/g, '-');
        _descargar(wb, 'productos-' + safeName + '.xlsx');
    }

    /* ═══════════════════════════════════════════════════════════════════════
       TELE-ASESOR (clon de Comparar, filtro por users_id)
    ═══════════════════════════════════════════════════════════════════════ */
    var _tlasCatalogo = [];

    function _renderTlaChecks(usuarios) {
        _tlasCatalogo = usuarios;
        var $cont = $('#tla-vend-checks').empty();
        usuarios.forEach(function (v) {
            $cont.append(
                '<span>' +
                '<input type="checkbox" class="cmp-vend-check tla-vend-check" id="tla-v-' + v.id + '" value="' + v.id + '">' +
                '<label class="cmp-vend-label mb-0" for="tla-v-' + v.id + '">' + v.name + '</label>' +
                '</span>'
            );
        });
    }

    function _getTlasSeleccionados() {
        var ids = [];
        $('#tla-vend-checks input:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function cargarComparacionTla() {
        var ids = _getTlasSeleccionados();
        if (!ids.length) {
            alert('Seleccione al menos un tele-asesor para comparar.');
            return;
        }

        var _n = new Date();
        var fi = $('#tla-fi').val() || (_n.getFullYear() + '-' + String(_n.getMonth() + 1).padStart(2, '0') + '-01');
        var ff = $('#tla-ff').val() || todayStr();

        /* KPI por cada tele-asesor (users_id) */
        $.get('/reporte/dashboard/top-tele-asesores', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedores:   ids.join(',')
        }).then(function (rows) {
            _renderTlaKpis(rows);
            _renderTlaTotal(rows);
            _renderTlaPart(rows);
        });

        /* Evolución mensual (users_id) */
        $.get('/reporte/dashboard/ventas-mes-tele-asesores', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedores:   ids
        }).then(function (rows) {
            _renderTlaEvolucion(rows, ids);
        });

        /* Escalas por tele-asesor */
        $.get('/reporte/dashboard/escalas-comparacion-tla', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedores:   ids.join(',')
        }).then(function (vendors) {
            _renderResumenEscalasTla(vendors, fi, ff);
        });
    }

    function _renderResumenEscalasTla(vendors, fi, ff) {
        var $tabs    = $('#tla-esc-tabs').empty();
        var $content = $('#tla-esc-content').empty();
        var $empty   = $('#tla-esc-empty');

        if (!vendors || !vendors.length) {
            $empty.show();
            return;
        }
        $empty.hide();

        var palette = _palette(vendors.length);
        vendors.forEach(function (vd, i) {
            var color   = palette[i];
            var tabId   = 'tla-esc-tab-' + i;
            var paneId  = 'tla-esc-pane-' + i;
            var active  = i === 0 ? ' active show' : '';
            var ariaSelected = i === 0 ? 'true' : 'false';

            $tabs.append(
                '<li class="nav-item">' +
                '<a class="nav-link' + (i === 0 ? ' active' : '') + '" id="' + tabId + '"' +
                ' data-toggle="tab" href="#' + paneId + '" role="tab" aria-selected="' + ariaSelected + '"' +
                ' style="font-weight:700; color:' + color + '">' +
                vd.vendedor +
                '</a></li>'
            );

            var rows = '';
            vd.escalas.forEach(function (esc) {
                var barWidth = Math.max(esc.pct, 2);
                rows +=
                    '<tr>' +
                    '<td class="font-weight-bold">' + esc.escala + '</td>' +
                    '<td class="text-right">' +
                    '<a href="#" class="tla-esc-fact-link font-weight-bold" style="color:#EC401B"' +
                    ' data-fi="' + fi + '" data-ff="' + ff + '"' +
                    ' data-vend-id="' + vd.vendedor_id + '" data-esc-id="' + esc.escala_id + '"' +
                    ' data-vend-nombre="' + vd.vendedor + '" data-esc-nombre="' + esc.escala + '">' +
                    esc.facturas + '</a></td>' +
                    '<td class="text-right font-weight-bold">' + fmt(esc.total_sin_isv) + '</td>' +
                    '<td style="min-width:120px">' +
                    '<div style="background:#e9ecef;border-radius:4px;overflow:hidden;height:16px">' +
                    '<div style="width:' + barWidth + '%;background:' + color + ';height:100%;border-radius:4px;transition:width .4s"></div>' +
                    '</div>' +
                    '<small class="text-muted ml-1">' + esc.pct + '%</small>' +
                    '</td>' +
                    '</tr>';
            });

            rows +=
                '<tr class="font-weight-bold" style="border-top:2px solid ' + color + '">' +
                '<td>TOTAL</td><td class="text-right">' + vd.escalas.reduce(function(s,e){return s+e.facturas;},0) + '</td>' +
                '<td class="text-right">' + fmt(vd.total) + '</td>' +
                '<td><small class="text-muted">100%</small></td>' +
                '</tr>';

            $content.append(
                '<div class="tab-pane fade' + active + '" id="' + paneId + '" role="tabpanel">' +
                '<div class="table-responsive mt-3">' +
                '<table class="table table-sm table-bordered">' +
                '<thead class="thead-dark"><tr>' +
                '<th>Escala de Precio</th>' +
                '<th class="text-right">Facturas</th>' +
                '<th class="text-right">Facturación sin ISV</th>' +
                '<th>Participación</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '</table></div></div>'
            );
        });

        $content.off('click', '.tla-esc-fact-link').on('click', '.tla-esc-fact-link', function (e) {
            e.preventDefault();
            var $a = $(this);
            _abrirFacturasComparacionTla(
                $a.data('fi'), $a.data('ff'),
                $a.data('vend-id'), $a.data('esc-id'),
                $a.data('vend-nombre'), $a.data('esc-nombre')
            );
        });
    }

    function _renderTlaKpis(rows) {
        var $cont = $('#tla-kpi-cards').empty();
        var colors = _palette(rows.length);
        rows.forEach(function (r, i) {
            var color = colors[i];
            $cont.append(
                '<div class="mb-2 col-sm-6 col-md-4 col-lg-3">' +
                '<div class="card h-100" style="border-left:4px solid ' + color + '">' +
                '<div class="px-3 py-2 card-body">' +
                '<div class="mb-1 text-xs font-weight-bold text-uppercase text-truncate" style="color:' + color + '">' + r.vendedor + '</div>' +
                '<div class="mb-0 h5 font-weight-bold">' + fmt(r.total_sin_isv) + '</div>' +
                '<small class="text-muted">Facturación sin ISV</small><br>' +
                '<small class="text-muted">' + r.facturas + ' facturas · ' + r.clientes_atendidos + ' clientes</small>' +
                '</div></div></div>'
            );
        });
    }

    function _renderTlaEvolucion(rows, ids) {
        destroyChart('chart-tla-evolucion');
        if (!rows || !rows.length) return;

        var byVend = {};
        var vendOrder = [];
        rows.forEach(function (r) {
            if (!byVend[r.vendedor]) { byVend[r.vendedor] = Array(12).fill(0); vendOrder.push(r.vendedor); }
            byVend[r.vendedor][r.mes - 1] = parseFloat(r.total);
        });

        var series = vendOrder.map(function (name) {
            return { name: name, data: byVend[name] };
        });

        charts['chart-tla-evolucion'] = new ApexCharts(get('chart-tla-evolucion'), {
            chart: { type: 'line', height: 380, toolbar: { show: true }, zoom: { enabled: true } },
            series: series,
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            stroke: { curve: 'smooth', width: 2 },
            colors: _palette(series.length),
            markers: { size: 4 },
            legend: { position: 'top' }
        });
        charts['chart-tla-evolucion'].render();
    }

    function _renderTlaTotal(rows) {
        destroyChart('chart-tla-total');
        if (!rows || !rows.length) return;

        charts['chart-tla-total'] = new ApexCharts(get('chart-tla-total'), {
            chart: { type: 'bar', height: Math.max(300, rows.length * 40), toolbar: { show: false } },
            series: [{ name: 'Facturación sin ISV', data: rows.map(function (r) { return parseFloat(r.total_sin_isv); }) }],
            xaxis: { categories: rows.map(function (r) { return r.vendedor; }) },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: _palette(rows.length),
            plotOptions: { bar: { borderRadius: 4, distributed: true, columnWidth: '55%' } },
            legend: { show: false }
        });
        charts['chart-tla-total'].render();
    }

    function _renderTlaPart(rows) {
        destroyChart('chart-tla-part');
        if (!rows || !rows.length) return;

        charts['chart-tla-part'] = new ApexCharts(get('chart-tla-part'), {
            chart: { type: 'donut', height: 300 },
            series: rows.map(function (r) { return parseFloat(r.total_sin_isv); }),
            labels:  rows.map(function (r) { return r.vendedor; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: _palette(rows.length),
            legend: { position: 'right' }
        });
        charts['chart-tla-part'].render();
    }

    /* ─── Tele-Asesor: Modal Facturas ──────────────────────────────────── */
    var _tlaFactParams = {};
    var _tlaFactAllRows = [];
    var _tlaFactPage = 1;
    var _tlaFactPerPage = 6;

    function _renderTlaFactPage(page) {
        _tlaFactPage = page;
        var rows = _tlaFactAllRows;
        var total = rows.length;
        var pages = Math.ceil(total / _tlaFactPerPage);
        var start = (page - 1) * _tlaFactPerPage;
        var slice = rows.slice(start, start + _tlaFactPerPage);

        var $tbody = $('#tbody-tla-facturas').empty();
        slice.forEach(function (r) {
            $tbody.append(
                '<tr>' +
                '<td><a href="#" class="font-weight-bold tla-ver-productos" style="color:#EC401B"' +
                ' data-factura-id="' + r.factura_id + '"' +
                ' data-documento="' + (r.documento || r.factura_id) + '">' +
                (r.documento || 'FAC-' + r.factura_id) + '</a></td>' +
                '<td>' + r.fecha + '</td>' +
                '<td>' + r.cliente + '</td>' +
                '<td><span class="badge badge-secondary">' + r.cat_cliente + '</span></td>' +
                '<td>' + r.tipo_cliente + '</td>' +
                '<td class="text-right">' + r.lineas + '</td>' +
                '<td class="text-right font-weight-bold">' + fmt(r.total_sin_isv) + '</td>' +
                '<td class="text-right text-muted">' + fmt(r.isv) + '</td>' +
                '<td class="text-right font-weight-bold">' + fmt(r.total_con_isv) + '</td>' +
                '</tr>'
            );
        });

        $('#tla-fact-pag-info').text('Mostrando ' + (start + 1) + '-' + Math.min(start + _tlaFactPerPage, total) + ' de ' + total + ' facturas');

        var $links = $('#tla-fact-pag-links').empty();
        $links.append(
            '<li class="page-item' + (page === 1 ? ' disabled' : '') + '">' +
            '<a class="page-link" href="#" data-p="' + (page - 1) + '">&laquo;</a></li>'
        );
        var from = Math.max(1, page - 2);
        var to   = Math.min(pages, page + 2);
        for (var p = from; p <= to; p++) {
            $links.append(
                '<li class="page-item' + (p === page ? ' active' : '') + '">' +
                '<a class="page-link" href="#" data-p="' + p + '">' + p + '</a></li>'
            );
        }
        $links.append(
            '<li class="page-item' + (page === pages ? ' disabled' : '') + '">' +
            '<a class="page-link" href="#" data-p="' + (page + 1) + '">&raquo;</a></li>'
        );

        $('#tla-fact-pagination').css('display', 'flex');
    }

    function _abrirFacturasComparacionTla(fi, ff, userId, escalaId, userNombre, escalaNombre) {
        _tlaFactParams = { fi: fi, ff: ff, vendedor_id: userId, escala_id: escalaId };
        _tlaFactAllRows = [];
        _tlaFactPage = 1;

        $('#modal-tla-facturas-title').html(
            '<i class="fas fa-file-invoice mr-2"></i>' + userNombre +
            ' &mdash; <small>' + escalaNombre + '</small>'
        );
        $('#modal-tla-fact-kpis').html('<span class="text-muted small">Cargando...</span>');
        $('#tbody-tla-facturas').html('<tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        $('#tla-fact-pagination').css('display', 'none');
        $('#modal-tla-facturas').modal('show');

        $.get('/reporte/dashboard/facturas-comparacion-tla', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedor_id:  userId,
            escala_id:    escalaId
        }).then(function (rows) {
            _tlaFactAllRows = rows;
            var totalSinIsv = rows.reduce(function (s, r) { return s + r.total_sin_isv; }, 0);
            var totalCon    = rows.reduce(function (s, r) { return s + r.total_con_isv; }, 0);

            $('#modal-tla-fact-kpis').html(
                '<span class="font-weight-bold mr-3"><i class="fas fa-file-invoice text-muted mr-1"></i>' + rows.length + ' facturas</span>' +
                '<span class="font-weight-bold mr-3 text-info"><i class="fas fa-dollar-sign mr-1"></i>Sin ISV: ' + fmt(totalSinIsv) + '</span>' +
                '<span class="font-weight-bold text-secondary"><i class="fas fa-dollar-sign mr-1"></i>Con ISV: ' + fmt(totalCon) + '</span>'
            );

            _renderTlaFactPage(1);

            $('#tla-fact-pag-links').off('click').on('click', 'a.page-link', function (e) {
                e.preventDefault();
                var p = parseInt($(this).data('p'));
                var pages = Math.ceil(_tlaFactAllRows.length / _tlaFactPerPage);
                if (p >= 1 && p <= pages) _renderTlaFactPage(p);
            });

            $('#tbody-tla-facturas').off('click', '.tla-ver-productos').on('click', '.tla-ver-productos', function (e) {
                e.preventDefault();
                _abrirProductosFacturaTla($(this).data('factura-id'), $(this).data('documento'));
            });

            $('#btn-tla-fact-excel').off('click').on('click', function () {
                _exportarFacturasTlaExcel();
            });
        }).catch(function () {
            $('#tbody-tla-facturas').html('<tr><td colspan="9" class="text-center text-danger">Error al cargar facturas.</td></tr>');
        });
    }

    function _exportarFacturasTlaExcel() {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no está disponible.'); return; }
        var rows = _tlaFactAllRows;
        if (!rows || !rows.length) { alert('No hay datos para exportar.'); return; }

        var HNL_FMT = '"L." #,##0.00';
        var titulo = $('#modal-tla-facturas-title').text().replace(/\s+/g, ' ').trim();
        var wb = new ExcelJS.Workbook();
        var ws = wb.addWorksheet('Facturas');

        var headers = ['Documento','Fecha','Cliente','Cat. Cliente','Tipo Cliente','Líneas','Sin ISV','ISV','Total'];
        var headerRow = ws.addRow(headers);
        headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF343A40' } };

        rows.forEach(function (r) {
            var row = ws.addRow([
                r.documento || ('FAC-' + r.factura_id),
                r.fecha,
                r.cliente,
                r.cat_cliente,
                r.tipo_cliente,
                r.lineas,
                r.total_sin_isv,
                r.isv,
                r.total_con_isv
            ]);
            [7, 8, 9].forEach(function (c) {
                var cell = row.getCell(c);
                if (typeof cell.value === 'number') cell.numFmt = HNL_FMT;
            });
        });

        ws.columns.forEach(function (col, i) {
            col.width = [18, 12, 30, 16, 16, 8, 14, 12, 14][i] || 16;
        });

        var sinISVTot = rows.reduce(function (s, r) { return s + r.total_sin_isv; }, 0);
        var isvTot    = rows.reduce(function (s, r) { return s + r.isv; }, 0);
        var conTot    = rows.reduce(function (s, r) { return s + r.total_con_isv; }, 0);
        var totRow = ws.addRow(['', '', '', '', 'TOTAL', rows.length, sinISVTot, isvTot, conTot]);
        totRow.font = { bold: true };
        [7, 8, 9].forEach(function (c) {
            var cell = totRow.getCell(c);
            if (typeof cell.value === 'number') cell.numFmt = HNL_FMT;
        });

        var fecha = new Date().toISOString().slice(0, 10);
        _descargar(wb, 'facturas-tele-asesor-' + fecha + '.xlsx');
    }

    function _abrirProductosFacturaTla(facturaId, docNombre) {
        $('#modal-tla-prod-title').html('<i class="fas fa-boxes mr-2"></i>Productos — ' + docNombre);
        $('#modal-tla-prod-header').html('<span class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando...</span>');
        $('#tbody-tla-productos').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        $('#tfoot-tla-total').text('');
        $('#btn-tla-ver-factura').attr('href', '/factura/cooporativo/' + facturaId);

        $('#modal-tla-facturas').one('hidden.bs.modal', function () {
            $('#modal-tla-productos').modal('show');
        });
        $('#modal-tla-facturas').modal('hide');

        $.get('/reporte/dashboard/productos-factura-comparacion', { factura_id: facturaId }).then(function (data) {
            var h = data.header || {};
            $('#modal-tla-prod-header').html(
                '<span><i class="fas fa-calendar-alt text-muted mr-1"></i><strong>' + (h.fecha || '') + '</strong></span>' +
                '<span><i class="fas fa-user mr-1 text-muted"></i>' + (h.cliente || '') + '</span>' +
                '<span><span class="badge badge-secondary mr-1">' + (h.cat_cliente || '') + '</span>' + (h.tipo_cliente || '') + '</span>' +
                '<span><i class="fas fa-user-tie text-muted mr-1"></i>' + (h.vendedor || '') + '</span>'
            );

            var $tbody = $('#tbody-tla-productos').empty();
            var total = 0;
            (data.productos || []).forEach(function (p) {
                total += p.subtotal_sin_isv;
                $tbody.append(
                    '<tr>' +
                    '<td><code>' + (p.codigo || '—') + '</code></td>' +
                    '<td>' + p.producto + '</td>' +
                    '<td><span class="badge badge-info">' + p.escala_precio + '</span></td>' +
                    '<td><span class="badge badge-secondary">' + p.cat_cliente + '</span></td>' +
                    '<td>' + p.tipo_cliente + '</td>' +
                    '<td class="text-right">' + fmt(p.precio_unitario) + '</td>' +
                    '<td class="text-right">' + p.cantidad + '</td>' +
                    '<td class="text-right font-weight-bold">' + fmt(p.subtotal_sin_isv) + '</td>' +
                    '</tr>'
                );
            });
            $('#tfoot-tla-total').text(fmt(total));
        }).catch(function () {
            $('#tbody-tla-productos').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar productos.</td></tr>');
        });

        var _volverATlaFacturas = function () {
            $('#modal-tla-productos').one('hidden.bs.modal', function () {
                $('#modal-tla-facturas').modal('show');
            });
            $('#modal-tla-productos').modal('hide');
        };
        $('#btn-tla-prod-back').off('click').on('click', _volverATlaFacturas);
        $('#btn-tla-prod-x').off('click').on('click', _volverATlaFacturas);

        $('#btn-tla-prod-excel').off('click').on('click', function () {
            _exportarProductosTlaExcel(docNombre);
        });
    }

    function _exportarProductosTlaExcel(docNombre) {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no está disponible.'); return; }
        var HNL_FMT = '"L." #,##0.00';
        var wb = new ExcelJS.Workbook();
        var ws = wb.addWorksheet('Productos');

        var headers = ['Código','Producto','Escala de Precio','Cat. Cliente','Tipo Cliente','Precio Unit.','Cantidad','Subtotal sin ISV'];
        var headerRow = ws.addRow(headers);
        headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEC401B' } };

        function _parseMoney(txt) {
            var m = String(txt).match(/-?[\d,]+\.?\d*/);
            if (!m) return 0;
            var n = parseFloat(m[0].replace(/,/g, ''));
            return isNaN(n) ? 0 : n;
        }

        var total = 0;
        $('#tbody-tla-productos tr').each(function () {
            var cells = $(this).find('td');
            if (!cells.length) return;
            var subtotal = _parseMoney(cells.eq(7).text());
            total += subtotal;
            var row = ws.addRow([
                cells.eq(0).text().trim(),
                cells.eq(1).text().trim(),
                cells.eq(2).text().trim(),
                cells.eq(3).text().trim(),
                cells.eq(4).text().trim(),
                _parseMoney(cells.eq(5).text()),
                _parseMoney(cells.eq(6).text()),
                subtotal
            ]);
            [6, 8].forEach(function (c) {
                var cell = row.getCell(c);
                if (typeof cell.value === 'number') cell.numFmt = HNL_FMT;
            });
        });

        var totRow = ws.addRow(['', '', '', '', '', '', 'TOTAL', total]);
        totRow.font = { bold: true };
        totRow.getCell(8).numFmt = HNL_FMT;

        ws.columns.forEach(function (col, i) {
            col.width = [10, 34, 18, 16, 14, 14, 10, 16][i] || 14;
        });

        var safeName = (docNombre || 'factura').replace(/[\\\/\*\?\[\]]/g, '-');
        _descargar(wb, 'productos-tla-' + safeName + '.xlsx');
    }

    function limpiarFiltrosAdv() {
        _filtroAdvVend = null;
        $('#a-vendedor').val('').trigger('change');
        $('#a-tipo-cliente').val('');
        $('#prod-filtro-producto').val('');
        $('#prod-filtro-cliente').val('');
        $('#prod-filtro-marca').val('');
        $('#prod-filtro-categoria').val('');
        $('#prod-filtro-vendedor').val('');
        $('#prod-filtro-sucursal').val('');
        $('#prod-filtro-canal').val('');
        $('#prod-filtro-anio').val('');
        $('#prod-filtro-mes').val('');
        $('#prod-fi').val(new Date().getFullYear() + '-01-01');
        $('#prod-ff').val(todayStr());
        $('#adv-filter-badge-vend').hide();
        $('#adv-active-filters').addClass('d-none');
        $('#cli-prod-filtro').addClass('d-none').text('');
        setText('prod-escala', 'Sin aplicar');
        cargarAnalitica();
    }

    /* ══════════════════════════════════════════════════════════════════════
       EXPORTAR EXCEL
    ══════════════════════════════════════════════════════════════════════ */
    function exportarExcel() {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no está disponible.'); return; }

        var activeTab  = $('#dashTabs .nav-link.active').attr('id');   // tab-hist | tab-sem | tab-adv
        var activePill = $('#adv-pills .nav-link.active').attr('href') || ''; // #pill-pane-vend | -cli | -prod | -comp

        var wb = new ExcelJS.Workbook();
        wb.creator = window._profacAuthUser || 'PROFAC';
        wb.created = new Date();

        /* ─── colours ─── */
        var DARK_HDR  = '343A40';
        var ORANGE    = 'FD7E14';
        var LIGHT_ROW = 'F8F9FA';
        var CUR_FMT   = '"L."#,##0.00';
        var NUM_FMT   = '#,##0';

        /* Adds title + metadata rows, returns next empty row index (1-based) */
        function _setupSheet(ws, title, headers, colWidths) {
            /* Row 1 – title */
            ws.addRow([title]);
            var titleCell = ws.getCell('A1');
            titleCell.font  = { bold: true, size: 13, color: { argb: 'FF' + ORANGE } };
            ws.mergeCells(1, 1, 1, headers.length);

            /* Row 2 – generated by */
            ws.addRow(['Generado por: ' + (window._profacAuthUser || 'PROFAC')]);
            ws.mergeCells(2, 1, 2, headers.length);

            /* Row 3 – date */
            ws.addRow(['Fecha: ' + new Date().toLocaleDateString('es-HN')]);
            ws.mergeCells(3, 1, 3, headers.length);

            /* Row 4 – blank */
            ws.addRow([]);

            /* Row 5 – dark header */
            var hdrRow = ws.addRow(headers);
            hdrRow.eachCell(function (cell) {
                cell.fill  = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + DARK_HDR } };
                cell.font  = { bold: true, color: { argb: 'FFFFFFFF' } };
                cell.alignment = { horizontal: 'center', vertical: 'middle' };
                cell.border = { bottom: { style: 'thin', color: { argb: 'FF666666' } } };
            });

            /* Column widths */
            (colWidths || []).forEach(function (w, i) { ws.getColumn(i + 1).width = w; });
            ws.getRow(5).height = 22;

            return 6; /* first data row */
        }

        /* Style data rows: alternating bg, borders, currency formatting */
        function _styleData(ws, fromRow, count, currencyCols) {
            for (var r = fromRow; r < fromRow + count; r++) {
                var row = ws.getRow(r);
                var even = (r - fromRow) % 2 === 0;
                row.eachCell({ includeEmpty: true }, function (cell, colNum) {
                    if (even) {
                        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + LIGHT_ROW } };
                    }
                    cell.border = {
                        top:    { style: 'hair', color: { argb: 'FFCCCCCC' } },
                        bottom: { style: 'hair', color: { argb: 'FFCCCCCC' } },
                        left:   { style: 'hair', color: { argb: 'FFCCCCCC' } },
                        right:  { style: 'hair', color: { argb: 'FFCCCCCC' } }
                    };
                    if ((currencyCols || []).indexOf(colNum) !== -1) {
                        cell.numFmt = CUR_FMT;
                        cell.alignment = { horizontal: 'right' };
                    }
                });
            }
        }

        /* ── detect what to export ───────────────────────────────────────── */
        if (activeTab === 'tab-hist') {
            /* Histórico: KPIs + Ventas mensuales */
            $.get('/reporte/dashboard/kpis', getP1()).then(function (d) {
                var ws = wb.addWorksheet('KPIs Histórico');
                var from = _setupSheet(ws, 'Dashboard Histórico — KPIs', ['Indicador', 'Valor'], [35, 20]);
                var kpis = [
                    ['Total Vendido', parseFloat(d.total_ventas) || 0],
                    ['Facturas',      d.total_facturas || 0],
                    ['Ticket Promedio', parseFloat(d.ticket_promedio) || 0],
                    ['Clientes Únicos', d.clientes_unicos || 0],
                    ['Total Descuentos', parseFloat(d.total_descuentos) || 0],
                    ['Mejor Mes',      d.mejor_mes || '-'],
                    ['Mejor Asesor Comercial', d.mejor_vendedor || '-']
                ];
                kpis.forEach(function (row) { ws.addRow(row); });
                _styleData(ws, from, kpis.length, [2]);
                _descargar(wb, 'dashboard-historico.xlsx');
            });

        } else if (activeTab === 'tab-sem') {
            /* Semanal: KPIs + tabla de facturas */
            $.get('/reporte/dashboard/resumen-semanal', getP2()).then(function (d) {
                var ws = wb.addWorksheet('KPIs Semanal');
                var from = _setupSheet(ws, 'Dashboard Semanal — KPIs', ['Indicador', 'Valor'], [35, 20]);
                var kpis = [
                    ['Total Período',   parseFloat(d.total) || 0],
                    ['Facturas',        d.facturas || 0],
                    ['Ticket Promedio', parseFloat(d.ticket_promedio) || 0],
                    ['Mejor Asesor Comercial',  d.mejor_vendedor || '-'],
                    ['Mejor Cliente',   d.mejor_cliente || '-'],
                    ['Mejor Día',       d.mejor_dia || '-']
                ];
                kpis.forEach(function (row) { ws.addRow(row); });
                _styleData(ws, from, kpis.length, [2]);
                _descargar(wb, 'dashboard-semanal.xlsx');
            });

        } else {
            /* Analítica Avanzada: depends on active pill */
            if (activePill === '#pill-pane-vend' || activePill === '') {
                $.get('/reporte/dashboard/top-vendedores', getP3()).then(function (rows) {
                    var ws = wb.addWorksheet('Vendedores');
                    var headers = ['#', 'Vendedor', 'Facturas', 'Clientes', 'Total Ventas', 'Ticket Prom.', 'Participación %'];
                    var widths  = [5, 28, 10, 10, 18, 16, 14];
                    var from    = _setupSheet(ws, 'Analítica — Vendedores', headers, widths);
                    rows.forEach(function (r, i) {
                        ws.addRow([i + 1, r.vendedor, r.facturas, r.clientes_atendidos,
                                   parseFloat(r.total_ventas), parseFloat(r.ticket_promedio),
                                   parseFloat(r.participacion)]);
                    });
                    _styleData(ws, from, rows.length, [5, 6]);
                    /* % format */
                    for (var rx = from; rx < from + rows.length; rx++) {
                        ws.getRow(rx).getCell(7).numFmt = '0.00"%"';
                    }
                    _descargar(wb, 'analitica-vendedores.xlsx');
                });

            } else if (activePill === '#pill-pane-cli') {
                $.get('/reporte/dashboard/top-clientes', getP3()).then(function (rows) {
                    var ws = wb.addWorksheet('Clientes');
                    var headers = ['#', 'Cliente', 'Tipo', 'ABC', 'Facturas', 'Total Comprado', 'Ticket Prom.', 'Última Compra', 'Días sin Comprar'];
                    var widths  = [5, 35, 18, 6, 10, 18, 16, 16, 14];
                    var from    = _setupSheet(ws, 'Analítica — Clientes', headers, widths);
                    rows.forEach(function (r, i) {
                        ws.addRow([i + 1, r.cliente, r.tipo_cliente, r.clasificacion_abc,
                                   r.facturas, parseFloat(r.total_comprado), parseFloat(r.ticket_promedio),
                                   r.ultima_compra || '-', r.dias_sin_comprar || 0]);
                    });
                    _styleData(ws, from, rows.length, [6, 7]);
                    _descargar(wb, 'analitica-clientes.xlsx');
                });

            } else if (activePill === '#pill-pane-prod') {
                /* Productos + Marcas in two sheets */
                $.when(
                    $.get('/reporte/dashboard/top-productos', getProdFilters()),
                    $.get('/reporte/dashboard/top-marcas',   getProdFilters())
                ).then(function (prodRes, marcaRes) {
                    var prodRows  = prodRes[0];
                    var marcaRows = marcaRes[0];

                    var ws1 = wb.addWorksheet('Productos');
                    var h1  = ['#', 'Producto', 'Categoría', 'Subcategoría', 'Unidades', 'Ingresos', 'P.Prom.', 'Facturas', 'Pareto %'];
                    var from1 = _setupSheet(ws1, 'Analítica — Top Productos', h1, [5,40,18,18,12,16,14,10,10]);
                    prodRows.forEach(function (r, i) {
                        ws1.addRow([i + 1, r.producto, r.categoria, r.subcategoria,
                                    r.unidades_vendidas, parseFloat(r.ingresos), parseFloat(r.precio_promedio),
                                    r.apariciones, parseFloat(r.pareto)]);
                    });
                    _styleData(ws1, from1, prodRows.length, [6, 7]);

                    var ws2 = wb.addWorksheet('Por Marca');
                    var h2  = ['#', 'Marca', 'Productos', 'Unidades', 'Ingresos', 'P.Prom.', 'Facturas', 'Participación %'];
                    var from2 = _setupSheet(ws2, 'Analítica — Por Marca', h2, [5,28,12,12,16,14,10,14]);
                    marcaRows.forEach(function (r, i) {
                        ws2.addRow([i + 1, r.marca, r.productos, r.unidades_vendidas,
                                    parseFloat(r.ingresos), parseFloat(r.precio_promedio),
                                    r.facturas, parseFloat(r.participacion)]);
                    });
                    _styleData(ws2, from2, marcaRows.length, [5, 6]);

                    _descargar(wb, 'analitica-productos.xlsx');
                });

            } else if (activePill === '#pill-pane-comp') {
                var vends = _getVendsSeleccionados();
                if (!vends.length) { alert('Seleccione al menos un vendedor para exportar.'); return; }
                $.get('/reporte/dashboard/top-vendedores', $.extend({}, getP3(), { vendedores: vends.join(',') }))
                 .then(function (rows) {
                    var ws = wb.addWorksheet('Comparación');
                    var headers = ['Vendedor', 'Facturas', 'Clientes', 'Total Ventas', 'Ticket Prom.', 'Participación %'];
                    var widths  = [28, 10, 10, 18, 16, 14];
                    var from    = _setupSheet(ws, 'Analítica — Comparar Vendedores', headers, widths);
                    rows.forEach(function (r) {
                        ws.addRow([r.vendedor, r.facturas, r.clientes_atendidos,
                                   parseFloat(r.total_ventas), parseFloat(r.ticket_promedio),
                                   parseFloat(r.participacion)]);
                    });
                    _styleData(ws, from, rows.length, [4, 5]);
                    _descargar(wb, 'analitica-comparacion.xlsx');
                });
            } else if (activePill === '#pill-pane-tla') {
                var tlaIds = _getTlasSeleccionados();
                if (!tlaIds.length) { alert('Seleccione al menos un tele-asesor para exportar.'); return; }
                $.get('/reporte/dashboard/top-tele-asesores', {
                    fecha_inicio: (getP3().fecha_inicio || ''),
                    fecha_final:  (getP3().fecha_final  || ''),
                    vendedores:   tlaIds.join(',')
                }).then(function (rows) {
                    var ws = wb.addWorksheet('Tele-Asesor');
                    var headers = ['Tele-Asesor', 'Facturas', 'Clientes', 'Total Ventas', 'Ticket Prom.', 'Participación %'];
                    var widths  = [28, 10, 10, 18, 16, 14];
                    var from    = _setupSheet(ws, 'Analítica — Tele-Asesor', headers, widths);
                    rows.forEach(function (r) {
                        ws.addRow([r.vendedor, r.facturas, r.clientes_atendidos,
                                   parseFloat(r.total_ventas), parseFloat(r.ticket_promedio),
                                   parseFloat(r.participacion)]);
                    });
                    _styleData(ws, from, rows.length, [4, 5]);
                    _descargar(wb, 'analitica-tele-asesor.xlsx');
                });
            }
        }
    }

    function exportarDetalleSemanal() {
        if (typeof ExcelJS === 'undefined') { alert('ExcelJS no está disponible.'); return; }

        var p = getP2();

        $.get('/reporte/dashboard/ventas-semanales/export', p).then(function (rows) {
            var wb = new ExcelJS.Workbook();
            wb.creator = window._profacAuthUser || 'PROFAC';
            wb.created = new Date();

            var ws      = wb.addWorksheet('Detalle Facturas');
            var headers = ['Fecha', 'Día', 'Semana', 'Documento', 'Cliente', 'Vendedor', 'Tipo', 'Subtotal', 'ISV', 'Descuento', 'Total'];
            var widths  = [14, 14, 10, 22, 35, 22, 18, 14, 14, 14, 14];

            /* Título y encabezados */
            ws.addRow(['Detalle de Facturas — Reporte Semanal']);
            ws.getCell('A1').font = { bold: true, size: 13, color: { argb: 'FFFD7E14' } };
            ws.mergeCells(1, 1, 1, headers.length);

            ws.addRow(['Período: ' + (p.fecha_inicio || '') + ' al ' + (p.fecha_final || '')]);
            ws.mergeCells(2, 1, 2, headers.length);

            ws.addRow(['Generado: ' + new Date().toLocaleDateString('es-HN')]);
            ws.mergeCells(3, 1, 3, headers.length);

            ws.addRow([]);

            var hdrRow = ws.addRow(headers);
            hdrRow.eachCell(function (cell) {
                cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF343A40' } };
                cell.font      = { bold: true, color: { argb: 'FFFFFFFF' } };
                cell.alignment = { horizontal: 'center', vertical: 'middle' };
            });
            widths.forEach(function (w, i) { ws.getColumn(i + 1).width = w; });
            ws.getRow(5).height = 22;

            /* Columnas monetarias (índice 1-based): Subtotal=8, ISV=9, Descuento=10, Total=11 */
            var CUR_FMT = '"L."#,##0.00';

            rows.forEach(function (r, idx) {
                var dataRow = ws.addRow([
                    r.fecha, r.dia_semana, r.semana_iso, r.documento,
                    r.cliente, r.vendedor, r.tipo_cliente,
                    parseFloat(r.subtotal)   || 0,
                    parseFloat(r.impuesto)   || 0,
                    parseFloat(r.descuento)  || 0,
                    parseFloat(r.total)      || 0
                ]);

                var even = idx % 2 === 0;
                dataRow.eachCell({ includeEmpty: true }, function (cell, colNum) {
                    if (even) cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8F9FA' } };
                    cell.border = {
                        top:    { style: 'hair', color: { argb: 'FFCCCCCC' } },
                        bottom: { style: 'hair', color: { argb: 'FFCCCCCC' } },
                        left:   { style: 'hair', color: { argb: 'FFCCCCCC' } },
                        right:  { style: 'hair', color: { argb: 'FFCCCCCC' } }
                    };
                    if ([8, 9, 10, 11].indexOf(colNum) !== -1) {
                        cell.numFmt    = CUR_FMT;
                        cell.alignment = { horizontal: 'right' };
                    }
                });
            });

            /* Fila de totales */
            var totRow = ws.addRow([
                'TOTAL', '', '', '', '', '', '',
                rows.reduce(function (s, r) { return s + (parseFloat(r.subtotal)  || 0); }, 0),
                rows.reduce(function (s, r) { return s + (parseFloat(r.impuesto)  || 0); }, 0),
                rows.reduce(function (s, r) { return s + (parseFloat(r.descuento) || 0); }, 0),
                rows.reduce(function (s, r) { return s + (parseFloat(r.total)     || 0); }, 0)
            ]);
            totRow.eachCell({ includeEmpty: true }, function (cell, colNum) {
                cell.font = { bold: true };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFE0B2' } };
                if ([8, 9, 10, 11].indexOf(colNum) !== -1) {
                    cell.numFmt    = CUR_FMT;
                    cell.alignment = { horizontal: 'right' };
                }
            });

            _descargar(wb, 'detalle-facturas-semanal.xlsx');
        }).fail(function () {
            alert('Error al obtener los datos para exportar.');
        });
    }

    function _descargar(wb, filename) {
        wb.xlsx.writeBuffer().then(function (buffer) {
            var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var url  = URL.createObjectURL(blob);
            var a    = document.createElement('a');
            a.href   = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    }

    /* ══════════════════════════════════════════════════════════════════════
       RECARGAR TODO
    ══════════════════════════════════════════════════════════════════════ */
    function recargarTodo() {
        var activeTab = $('#dashTabs .nav-link.active').attr('id');
        if (activeTab === 'tab-hist')      cargarHistorico();
        else if (activeTab === 'tab-sem')  cargarSemanal();
        else if (activeTab === 'tab-adv')  cargarAnalitica();
    }

    /* ── API pública ────────────────────────────────────────────────────── */
    return {
        init:                  init,
        cargarHistorico:       cargarHistorico,
        cargarSemanal:         cargarSemanal,
        cargarAnalitica:       cargarAnalitica,
        cargarDashboardProductos: cargarDashboardProductos,
        cargarComparacion:     cargarComparacion,
        cargarComparacionTla:  cargarComparacionTla,
        recalcularCrecimiento: recalcularCrecimiento,
        limpiarFiltrosSem:     limpiarFiltrosSem,
        limpiarFiltrosAdv:     limpiarFiltrosAdv,
        limpiarFiltroCli:      limpiarFiltroCli,
        cargarCli:             cargarCli,
        cargarMarcas:          cargarMarcas,
        limpiarFiltroMarc:     limpiarFiltroMarc,
        limpiarDashboardProductos: limpiarDashboardProductos,
        exportarExcel:         exportarExcel,
        exportarTablaExcel:    exportarTablaExcel,
        exportarFacturasCliExcel: exportarFacturasCliExcel,
        exportarDetalleSemanal: exportarDetalleSemanal,
        exportarTablaProductosExcel: exportarTablaProductosExcel,
        exportarVistaProductosExcel: exportarVistaProductosExcel,
        exportarFacturasProductoExcel: exportarFacturasProductoExcel,
        exportarFacturasProductoPDF: exportarFacturasProductoPDF,
        recargarTodo:          recargarTodo,
        _filtrarMarca:         _filtrarMarca,
        _filtrarCategoria:     _filtrarCategoria,
        imprimirFactura:       imprimirFactura,
        _renderProdTopVendedores: _renderProdTopVendedores
    };
})();
