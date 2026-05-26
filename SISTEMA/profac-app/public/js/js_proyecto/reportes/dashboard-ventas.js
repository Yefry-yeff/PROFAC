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
    var _filtroSemDia = null;
    var _filtroAdvVend = null;

    /* ─── Helpers ─────────────────────────────────────────────────────────── */
    var MESES   = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    var DIAS_EN = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    var DIAS_ES = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
    var COLORES = ['#EC401B','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#4e73df','#fd7e14','#20c997','#6f42c1'];

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
            fecha_inicio: $('#a-fi').val() || (new Date().getFullYear() + '-01-01'),
            fecha_final:  $('#a-ff').val() || todayStr(),
            vendedor:     _filtroAdvVend ? _filtroAdvVend : ($('#a-vendedor').val() || ''),
            tipo_cliente: $('#a-tipo-cliente').val() || '',
            categoria:    $('#a-categoria').val() || ''
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
            $('#a-fi').val(new Date().getFullYear() + '-01-01');
            $('#a-ff').val(todayStr());

            /* Auto-carga pestaña 1 */
            cargarHistorico();
        });
    }

    /* ══════════════════════════════════════════════════════════════════════
       CATÁLOGOS
    ══════════════════════════════════════════════════════════════════════ */
    function cargarCatalogos() {
        return $.get('/reporte/dashboard/catalogo-filtros').then(function (data) {
            /* Años */
            _todosAnios = data.anios.map(function (a) { return parseInt(a.anio); });

            /* Tipos de cliente */
            var tcOpts = '<option value="">Todos</option>';
            data.tiposCliente.forEach(function (tc) {
                tcOpts += '<option value="' + tc.id + '">' + tc.descripcion + '</option>';
            });
            $('#h-tipo-cliente, #s-tipo-cliente, #a-tipo-cliente').html(tcOpts);

            /* Categorías (P3) */
            var catOpts = '<option value="">Todas</option>';
            data.categorias.forEach(function (c) {
                catOpts += '<option value="' + c.id + '">' + c.descripcion + '</option>';
            });
            $('#a-categoria').html(catOpts);

            /* Checkboxes de vendedores para comparación */
            _renderVendChecks(data.vendedores);

            /* Fechas por defecto comparación */
            $('#cmp-fi').val(new Date().getFullYear() + '-01-01');
            $('#cmp-ff').val(todayStr());
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
        ['kpi-total','kpi-facturas','kpi-ticket','kpi-clientes',
         'kpi-crecimiento','kpi-mejor-mes','kpi-mejor-vend','kpi-descuentos']
            .forEach(skeleton);

        /* KPIs */
        $.get('/reporte/dashboard/kpis', p).then(function (d) {
            setText('kpi-total',      fmt(d.total_ventas));
            setText('kpi-facturas',   fmtN(d.total_facturas));
            setText('kpi-ticket',     fmt(d.ticket_promedio));
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

        ['s-kpi-total','s-kpi-facturas','s-kpi-ticket','s-kpi-mejor-dia','s-kpi-vend','s-kpi-cliente']
            .forEach(skeleton);

        /* Resumen KPIs + gráfica por día */
        $.get('/reporte/dashboard/resumen-semanal', p).then(function (d) {
            setText('s-kpi-total',    fmt(d.total));
            setText('s-kpi-facturas', fmtN(d.facturas));
            setText('s-kpi-ticket',   fmt(d.ticket_promedio));
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
                        $('#filter-badge-vend').text('Vendedor: ' + vend.vendedor).show();
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
        setText('top-cli-sem-label', vendedor ? 'Vendedor seleccionado' : 'Todos los vendedores');
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
                { data: 'documento' },
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
            language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' }
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

        /* Vendedores */
        $.get('/reporte/dashboard/top-vendedores', p).then(function (rows) {
            _renderRankVend(rows);
            _renderTablaVendedores(rows);
        });

        /* Clientes */
        $.get('/reporte/dashboard/top-clientes', $.extend({}, p, { limite: 20 })).then(function (rows) {
            _renderTopCli(rows);
            _renderAbcCli(rows);
            _renderTablaClientes(rows);
        });

        /* Productos */
        $.get('/reporte/dashboard/top-productos', $.extend({}, p, { limite: 20 })).then(function (rows) {
            _renderTopProd(rows);
            _renderPareto(rows);
            _renderTablaProductos(rows);
        });

        /* Marcas */
        $.get('/reporte/dashboard/top-marcas', p).then(function (rows) {
            _renderTopMarcas(rows);
            _renderTablaMarcas(rows);
        });
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
                        $('#adv-filter-badge-vend').text('Vendedor: ' + vend.vendedor).show();
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
        var top15 = rows.slice(0, 15);

        charts['chart-top-cli'] = new ApexCharts(get('chart-top-cli'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Total', data: top15.map(function (r) { return parseFloat(r.total_comprado); }) }],
            xaxis: { categories: top15.map(function (r) { return r.cliente; }), labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            yaxis: {},
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: ['#36b9cc'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } }
        });
        charts['chart-top-cli'].render();
    }

    function _renderAbcCli(rows) {
        destroyChart('chart-abc-cli');
        if (!rows || !rows.length) return;

        var abc = { A: 0, B: 0, C: 0 };
        rows.forEach(function (r) { abc[r.clasificacion_abc] = (abc[r.clasificacion_abc] || 0) + parseFloat(r.total_comprado); });

        charts['chart-abc-cli'] = new ApexCharts(get('chart-abc-cli'), {
            chart: { type: 'donut', height: 300 },
            series: [abc.A, abc.B, abc.C],
            labels: ['A — Top 70%', 'B — 70–90%', 'C — 90–100%'],
            colors: ['#1cc88a', '#f6c23e', '#e74a3b'],
            tooltip: { y: { formatter: function (v) { return fmt(v); } } }
        });
        charts['chart-abc-cli'].render();
    }

    function _renderTopProd(rows) {
        destroyChart('chart-top-prod');
        if (!rows || !rows.length) return;
        var top20 = rows.slice(0, 20);

        charts['chart-top-prod'] = new ApexCharts(get('chart-top-prod'), {
            chart: { type: 'bar', height: 320, toolbar: { show: false } },
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
            chart: { type: 'line', height: 320, toolbar: { show: false } },
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
        var $tbody = $('#tbody-vendedores').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + r.vendedor + '</td>' +
                '<td>' + r.facturas + '</td>' +
                '<td>' + r.clientes_atendidos + '</td>' +
                '<td class="text-right">' + fmt(r.total_ventas) + '</td>' +
                '<td class="text-right">' + fmt(r.ticket_promedio) + '</td>' +
                '<td class="text-right">' + r.participacion + '%</td>' +
                '</tr>'
            );
        });
    }

    function _renderTablaClientes(rows) {
        var $tbody = $('#tbody-clientes').empty();
        rows.forEach(function (r, i) {
            var abcColor = r.clasificacion_abc === 'A' ? 'success' : (r.clasificacion_abc === 'B' ? 'warning' : 'danger');
            var estado   = r.inactivo   ? '<span class="badge badge-danger">Inactivo</span>'
                         : r.recurrente ? '<span class="badge badge-success">Recurrente</span>'
                                        : '<span class="badge badge-secondary">Activo</span>';
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + r.cliente + '</td>' +
                '<td>' + r.tipo_cliente + '</td>' +
                '<td><span class="badge badge-' + abcColor + '">' + r.clasificacion_abc + '</span></td>' +
                '<td>' + r.facturas + '</td>' +
                '<td class="text-right">' + fmt(r.total_comprado) + '</td>' +
                '<td class="text-right">' + fmt(r.ticket_promedio) + '</td>' +
                '<td>' + (r.ultima_compra || '-') + '</td>' +
                '<td class="text-right">' + (r.dias_sin_comprar || 0) + '</td>' +
                '<td>' + estado + '</td>' +
                '</tr>'
            );
        });
    }

    function _renderTablaProductos(rows) {
        var $tbody = $('#tbody-productos').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + r.producto + '</td>' +
                '<td>' + r.categoria + '</td>' +
                '<td>' + r.subcategoria + '</td>' +
                '<td class="text-right">' + fmtN(r.unidades_vendidas) + '</td>' +
                '<td class="text-right">' + fmt(r.ingresos) + '</td>' +
                '<td class="text-right">' + fmt(r.precio_promedio) + '</td>' +
                '<td>' + r.apariciones + '</td>' +
                '<td class="text-right">' + r.pareto + '%</td>' +
                '</tr>'
            );
        });
    }

    /* ─── MARCAS ─────────────────────────────────────────────────────────── */
    function _renderTopMarcas(rows) {
        destroyChart('chart-top-marcas');
        destroyChart('chart-part-marcas');
        if (!rows || !rows.length) return;

        var top15 = rows.slice(0, 15);

        charts['chart-top-marcas'] = new ApexCharts(get('chart-top-marcas'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
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
            chart: { type: 'donut', height: 300 },
            series: top10.map(function (r) { return parseFloat(r.ingresos); }),
            labels:  top10.map(function (r) { return r.marca; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES,
            legend: { position: 'right' }
        });
        charts['chart-part-marcas'].render();
    }

    function _renderTablaMarcas(rows) {
        var $tbody = $('#tbody-marcas').empty();
        rows.forEach(function (r, i) {
            $tbody.append(
                '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td class="font-weight-bold">' + r.marca + '</td>' +
                '<td class="text-right">' + fmtN(r.productos) + '</td>' +
                '<td class="text-right">' + fmtN(r.unidades_vendidas) + '</td>' +
                '<td class="text-right">' + fmt(r.ingresos) + '</td>' +
                '<td class="text-right">' + fmt(r.precio_promedio) + '</td>' +
                '<td class="text-right">' + r.facturas + '</td>' +
                '<td class="text-right">' + r.participacion + '%</td>' +
                '</tr>'
            );
        });
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
            alert('Seleccione al menos un vendedor para comparar.');
            return;
        }

        var fi = $('#cmp-fi').val() || (new Date().getFullYear() + '-01-01');
        var ff = $('#cmp-ff').val() || todayStr();

        /* KPI por cada vendedor */
        $.get('/reporte/dashboard/top-vendedores', { fecha_inicio: fi, fecha_final: ff }).then(function (allRows) {
            var rows = allRows.filter(function (r) { return ids.indexOf(String(r.vendedor_id)) >= 0; });
            _renderCmpKpis(rows);
            _renderCmpTotal(rows);
            _renderCmpPart(rows);
            _renderTablaComparacion(rows, fi, ff);
        });

        /* Evolución mensual */
        $.get('/reporte/dashboard/ventas-mes-vendedores', {
            fecha_inicio: fi,
            fecha_final:  ff,
            vendedores:   ids
        }).then(function (rows) {
            _renderCmpEvolucion(rows, ids);
        });
    }

    function _renderCmpKpis(rows) {
        var $cont = $('#cmp-kpi-cards').empty();
        var colors = COLORES;
        rows.forEach(function (r, i) {
            var color = colors[i % colors.length];
            $cont.append(
                '<div class="mb-2 col-sm-6 col-md-4 col-lg-3">' +
                '<div class="card h-100" style="border-left:4px solid ' + color + '">' +
                '<div class="px-3 py-2 card-body">' +
                '<div class="mb-1 text-xs font-weight-bold text-uppercase text-truncate" style="color:' + color + '">' + r.vendedor + '</div>' +
                '<div class="mb-0 h5 font-weight-bold">' + fmt(r.total_ventas) + '</div>' +
                '<small class="text-muted">' + r.facturas + ' facturas · ' + r.clientes_atendidos + ' clientes</small>' +
                '</div></div></div>'
            );
        });
    }

    function _renderCmpEvolucion(rows, ids) {
        destroyChart('chart-cmp-evolucion');
        if (!rows || !rows.length) return;

        /* Agrupar por vendedor */
        var byVend = {};
        rows.forEach(function (r) {
            if (!byVend[r.vendedor]) byVend[r.vendedor] = Array(12).fill(0);
            byVend[r.vendedor][r.mes - 1] = parseFloat(r.total);
        });

        var series = Object.keys(byVend).map(function (name) {
            return { name: name, data: byVend[name] };
        });

        charts['chart-cmp-evolucion'] = new ApexCharts(get('chart-cmp-evolucion'), {
            chart: { type: 'line', height: 380, toolbar: { show: true }, zoom: { enabled: true } },
            series: series,
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            stroke: { curve: 'smooth', width: 2 },
            colors: COLORES,
            markers: { size: 4 },
            legend: { position: 'top' }
        });
        charts['chart-cmp-evolucion'].render();
    }

    function _renderCmpTotal(rows) {
        destroyChart('chart-cmp-total');
        if (!rows || !rows.length) return;

        charts['chart-cmp-total'] = new ApexCharts(get('chart-cmp-total'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Total Ventas', data: rows.map(function (r) { return parseFloat(r.total_ventas); }) }],
            xaxis: { categories: rows.map(function (r) { return r.vendedor; }) },
            yaxis: { labels: { formatter: function (v) { return 'L.' + fmtN(v); } } },
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES.slice(0, rows.length),
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
            series: rows.map(function (r) { return parseFloat(r.total_ventas); }),
            labels:  rows.map(function (r) { return r.vendedor; }),
            tooltip: { y: { formatter: function (v) { return fmt(v); } } },
            colors: COLORES,
            legend: { position: 'right' }
        });
        charts['chart-cmp-part'].render();
    }

    function _renderTablaComparacion(rows, fi, ff) {
        var $tbody = $('#tbody-comparacion').empty();

        /* Calcular mejor mes: necesitamos los datos mensuales ya cargados */
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
    }

    function limpiarFiltrosAdv() {
        $('#a-vendedor').val('').trigger('change');
        $('#adv-filter-badge-vend').hide();
        $('#adv-active-filters').addClass('d-none');
        cargarAnalitica();
    }

    /* ══════════════════════════════════════════════════════════════════════
       EXPORTAR EXCEL
    ══════════════════════════════════════════════════════════════════════ */
    function exportarExcel(tab) {
        if (typeof ExcelJS === 'undefined') {
            alert('ExcelJS no está disponible. Intente nuevamente.');
            return;
        }

        var wb = new ExcelJS.Workbook();
        wb.creator = window._profacAuthUser || 'PROFAC';
        wb.created = new Date();

        function _header(ws) {
            ws.addRow(['DISTRIBUCIONES VALENCIA — Dashboard de Ventas']);
            ws.addRow(['Generado por: ' + (window._profacAuthUser || 'Usuario')]);
            ws.addRow(['Fecha: ' + new Date().toLocaleDateString('es-HN')]);
            ws.addRow([]);
        }

        if (tab === 'hist') {
            var ws = wb.addWorksheet('Histórico');
            _header(ws);
            ws.addRow(['KPI', 'Valor']);
            $.get('/reporte/dashboard/kpis', getP1()).then(function (d) {
                [
                    ['Total Vendido', fmt(d.total_ventas)],
                    ['Facturas', d.total_facturas],
                    ['Ticket Promedio', fmt(d.ticket_promedio)],
                    ['Clientes Únicos', d.clientes_unicos],
                    ['Total Descuentos', fmt(d.total_descuentos)],
                    ['Mejor Mes', d.mejor_mes || '-'],
                    ['Mejor Vendedor', d.mejor_vendedor || '-']
                ].forEach(function (row) { ws.addRow(row); });
                _descargar(wb, 'dashboard-historico.xlsx');
            });

        } else if (tab === 'sem') {
            var ws2 = wb.addWorksheet('Semanal');
            _header(ws2);
            $.get('/reporte/dashboard/resumen-semanal', getP2()).then(function (d) {
                ws2.addRow(['Métrica', 'Valor']);
                [
                    ['Total Período', fmt(d.total)],
                    ['Facturas', d.facturas],
                    ['Ticket Promedio', fmt(d.ticket_promedio)],
                    ['Mejor Vendedor', d.mejor_vendedor || '-'],
                    ['Mejor Cliente', d.mejor_cliente || '-'],
                    ['Mejor Día', d.mejor_dia || '-']
                ].forEach(function (row) { ws2.addRow(row); });
                _descargar(wb, 'dashboard-semanal.xlsx');
            });

        } else {
            var ws3 = wb.addWorksheet('Vendedores');
            _header(ws3);
            ws3.addRow(['#', 'Vendedor', 'Facturas', 'Clientes', 'Total Ventas', 'Ticket Prom.', 'Participación %']);
            $.get('/reporte/dashboard/top-vendedores', getP3()).then(function (rows) {
                rows.forEach(function (r, i) {
                    ws3.addRow([i + 1, r.vendedor, r.facturas, r.clientes_atendidos,
                                r.total_ventas, r.ticket_promedio, r.participacion]);
                });
                _descargar(wb, 'dashboard-analitica.xlsx');
            });
        }
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
        cargarComparacion:     cargarComparacion,
        recalcularCrecimiento: recalcularCrecimiento,
        limpiarFiltrosSem:     limpiarFiltrosSem,
        limpiarFiltrosAdv:     limpiarFiltrosAdv,
        exportarExcel:         exportarExcel,
        recargarTodo:          recargarTodo
    };
})();
