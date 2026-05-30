/**
 * Dashboard de Logística — dashboard-logistica.js
 * Usa: ApexCharts (cargado en layout), jQuery/Axios, DataTables
 */
'use strict';

const dashLogistica = (function () {

    // ── ESTADO INTERNO ───────────────────────────────────────────
    let charts = {};
    let dtTabla = null;

    const TEAL   = '#0d9488';
    const GREEN  = '#1cc88a';
    const ORANGE = '#f6c23e';
    const RED    = '#e74a3b';
    const GREY   = '#858796';
    const BLUE   = '#36b9cc';

    const ESTADO_COLORS = {
        entregado:   GREEN,
        parcial:     ORANGE,
        sin_entrega: GREY,
        anulada:     RED
    };
    const ESTADO_LABELS = {
        entregado:   'Entregado',
        parcial:     'Parcial',
        sin_entrega: 'Sin Entregar',
        anulada:     'Anulada'
    };

    // Español DataTables
    const DT_ES = {
        sProcessing:   'Procesando…',
        sLengthMenu:   'Mostrar _MENU_ registros',
        sZeroRecords:  'No se encontraron resultados',
        sEmptyTable:   'Sin datos disponibles',
        sInfo:         'Mostrando _START_ - _END_ de _TOTAL_ registros',
        sInfoEmpty:    'Mostrando 0 registros',
        sInfoFiltered: '(filtrado de _MAX_ total)',
        sSearch:       'Buscar:',
        sLoadingRecords: 'Cargando…',
        oPaginate: { sFirst: '«', sPrevious: '‹', sNext: '›', sLast: '»' }
    };

    // ── HELPERS ──────────────────────────────────────────────────
    function isoHoy()          { return new Date().toISOString().split('T')[0]; }
    function isoMesInicio()    { const d = new Date(); d.setDate(1); return d.toISOString().split('T')[0]; }
    function numFmt(n)         { return Number(n || 0).toLocaleString('es-HN'); }
    function pct(n)            { return Number(n || 0).toFixed(1) + '%'; }

    function destroyChart(key) {
        if (charts[key]) { try { charts[key].destroy(); } catch(e){} delete charts[key]; }
    }

    function buildQS(extra) {
        const fi     = document.getElementById('l-fi')?.value || isoMesInicio();
        const ff     = document.getElementById('l-ff')?.value || isoHoy();
        const equipo = document.getElementById('l-equipo')?.value || '';
        const estado = document.getElementById('l-estado')?.value || '';
        const params = { fi, ff };
        if (equipo) params.equipo = equipo;
        if (estado) params.estado = estado;
        return Object.assign(params, extra || {});
    }

    function qs(obj) {
        return Object.entries(obj)
            .filter(([,v]) => v !== '' && v !== null && v !== undefined)
            .map(([k,v]) => `${k}=${encodeURIComponent(v)}`)
            .join('&');
    }

    function setKpi(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function showSpin(chartId) {
        const el = document.getElementById(chartId);
        if (el) el.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-teal"></i></div>';
    }

    // ── CATÁLOGOS (poblar selects) ────────────────────────────────
    async function cargarFiltros() {
        try {
            const res = await axios.get('/logistica/reportes/filtros');
            const { equipos } = res.data;
            const sel = document.getElementById('l-equipo');
            if (sel && equipos) {
                equipos.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = e.nombre_equipo;
                    sel.appendChild(opt);
                });
            }
        } catch (e) { console.error('Error cargando filtros', e); }
    }

    // ── KPIs ─────────────────────────────────────────────────────
    async function cargarKPIs() {
        ['kpi-dist','kpi-comp','kpi-fact','kpi-entr','kpi-pend','kpi-anul','kpi-efect']
            .forEach(id => setKpi(id, '…'));
        try {
            const res = await axios.get('/logistica/reportes/kpis?' + qs(buildQS()));
            const d = res.data;
            setKpi('kpi-dist',  numFmt(d.distribuciones));
            setKpi('kpi-comp',  numFmt(d.completadas));
            setKpi('kpi-fact',  numFmt(d.total_facturas));
            setKpi('kpi-entr',  numFmt(d.entregadas));
            setKpi('kpi-pend',  numFmt(d.pendientes));
            setKpi('kpi-anul',  numFmt(d.anuladas));
            setKpi('kpi-efect', pct(d.efectividad));
        } catch(e) { console.error('Error KPIs', e); }
    }

    // ── CHART EVOLUCIÓN ──────────────────────────────────────────
    async function cargarEvolucion() {
        showSpin('chart-evolucion');
        try {
            const res = await axios.get('/logistica/reportes/evolucion?' + qs(buildQS()));
            const rows = res.data;
            const cats   = rows.map(r => r.fecha);
            const entreg = rows.map(r => parseInt(r.entregadas || 0));
            const pend   = rows.map(r => parseInt(r.pendientes  || 0));
            const anuled = rows.map(r => parseInt(r.anuladas    || 0));

            destroyChart('evolucion');
            charts.evolucion = new ApexCharts(document.getElementById('chart-evolucion'), {
                chart: { type: 'area', height: 320, toolbar: { show: false },
                    fontFamily: "'Source Sans Pro', sans-serif" },
                series: [
                    { name: 'Entregadas', data: entreg },
                    { name: 'Pendientes', data: pend   },
                    { name: 'Anuladas',   data: anuled }
                ],
                colors: [GREEN, ORANGE, RED],
                xaxis: { categories: cats, labels: { rotate: -35, style: { fontSize: '11px' } } },
                yaxis: { labels: { formatter: v => Math.round(v) } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                legend: { position: 'top' },
                tooltip: { x: { format: 'yyyy-MM-dd' } },
                grid: { borderColor: '#f0f0f0' }
            });
            charts.evolucion.render();
        } catch(e) {
            console.error('Error evolución', e);
            document.getElementById('chart-evolucion').innerHTML =
                '<p class="text-center text-muted py-4">Sin datos para el período</p>';
        }
    }

    // ── CHART POR EQUIPO ─────────────────────────────────────────
    async function cargarPorEquipo() {
        showSpin('chart-equipos');
        try {
            const res = await axios.get('/logistica/reportes/por-equipo?' + qs(buildQS()));
            const rows = res.data;
            const cats   = rows.map(r => r.equipo);
            const entreg = rows.map(r => parseInt(r.entregadas || 0));
            const pend   = rows.map(r => parseInt(r.pendientes  || 0));
            const anuled = rows.map(r => parseInt(r.anuladas    || 0));

            destroyChart('equipos');
            charts.equipos = new ApexCharts(document.getElementById('chart-equipos'), {
                chart: { type: 'bar', height: 300, stacked: false,
                    toolbar: { show: false }, fontFamily: "'Source Sans Pro', sans-serif" },
                plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
                series: [
                    { name: 'Entregadas', data: entreg },
                    { name: 'Pendientes', data: pend   },
                    { name: 'Anuladas',   data: anuled }
                ],
                colors: [GREEN, ORANGE, RED],
                xaxis: { categories: cats, labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { formatter: v => Math.round(v) } },
                dataLabels: { enabled: false },
                legend: { position: 'top' },
                grid: { borderColor: '#f0f0f0' }
            });
            charts.equipos.render();
        } catch(e) {
            console.error('Error por equipo', e);
            document.getElementById('chart-equipos').innerHTML =
                '<p class="text-center text-muted py-4">Sin datos para el período</p>';
        }
    }

    // ── CHART ESTADOS DONUT ──────────────────────────────────────
    async function cargarEstados() {
        showSpin('chart-estados');
        try {
            const res = await axios.get('/logistica/reportes/estados?' + qs(buildQS()));
            const rows = res.data;
            const labels = rows.map(r => ESTADO_LABELS[r.estado_entrega] || r.estado_entrega);
            const values = rows.map(r => parseInt(r.total || 0));
            const colors = rows.map(r => ESTADO_COLORS[r.estado_entrega] || GREY);

            destroyChart('estados');
            charts.estados = new ApexCharts(document.getElementById('chart-estados'), {
                chart: { type: 'donut', height: 300,
                    fontFamily: "'Source Sans Pro', sans-serif" },
                series: values,
                labels: labels,
                colors: colors,
                plotOptions: { pie: { donut: { size: '65%',
                    labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px' } } } } },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
                legend: { position: 'bottom' }
            });
            charts.estados.render();
        } catch(e) {
            console.error('Error estados', e);
            document.getElementById('chart-estados').innerHTML =
                '<p class="text-center text-muted py-4">Sin datos para el período</p>';
        }
    }

    // ── TABLA POR FACTURA ────────────────────────────────────────
    let dtFacturas = null;

    async function cargarTablaFacturas() {
        const estadoFact = document.getElementById('l-estado-fact')?.value || '';
        const params = buildQS();
        // para la tabla por factura usamos estado_entrega, no estado_id
        delete params.estado;
        if (estadoFact) params.estado = estadoFact;

        try {
            const res = await axios.get('/logistica/reportes/tabla-facturas?' + qs(params));
            const rows = res.data;

            if (dtFacturas) { dtFacturas.destroy(); dtFacturas = null; }
            $('#tabla-facturas tbody').empty();

            const ESTADO_BADGE = {
                entregado:   { cls: 'badge-success',   lbl: 'Entregado'     },
                parcial:     { cls: 'badge-warning',   lbl: 'Parcial'       },
                sin_entrega: { cls: 'badge-secondary', lbl: 'Sin Entregar'  },
                anulada:     { cls: 'badge-danger',    lbl: 'Anulada'       }
            };

            rows.forEach(r => {
                const b = ESTADO_BADGE[r.estado_entrega] || { cls: 'badge-light', lbl: r.estado_entrega };
                const rowCls = r.estado_entrega === 'anulada' ? 'table-danger' : '';
                const motivoAnul = r.motivo_anulacion
                    ? `<span title="${escHtml(r.motivo_anulacion)}" class="text-danger" style="cursor:help">
                           <i class="fas fa-comment-alt mr-1"></i>${truncate(r.motivo_anulacion, 35)}
                       </span>`
                    : '<span class="text-muted">—</span>';
                const motivoConf = r.motivo_confirmacion
                    ? `<span title="${escHtml(r.motivo_confirmacion)}" class="text-success" style="cursor:help">
                           <i class="fas fa-comment-check mr-1"></i>${truncate(r.motivo_confirmacion, 35)}
                       </span>`
                    : '<span class="text-muted">—</span>';

                $('#tabla-facturas tbody').append(`
                    <tr class="${rowCls}">
                        <td class="text-center">${r.distribucion_id}</td>
                        <td>${r.fecha_programada}</td>
                        <td>${r.hora_salida ?? '<span class="text-muted">—</span>'}</td>
                        <td class="font-weight-bold">${escHtml(r.numero_factura)}</td>
                        <td>${escHtml(r.cliente)}</td>
                        <td class="text-right">L. ${r.total}</td>
                        <td>${escHtml(r.equipo)}</td>
                        <td class="text-center"><span class="badge ${b.cls}">${b.lbl}</span></td>
                        <td>${r.fecha_entrega_real ?? '<span class="text-muted">—</span>'}</td>
                        <td>${motivoAnul}</td>
                        <td>${motivoConf}</td>
                    </tr>`);
            });

            dtFacturas = $('#tabla-facturas').DataTable({
                language: DT_ES,
                order: [[0, 'desc']],
                pageLength: 20,
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
                responsive: true,
                columnDefs: [
                    { targets: [9, 10], orderable: false }
                ]
            });
        } catch(e) { console.error('Error tabla facturas', e); }
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    function truncate(str, n) {
        if (!str) return '';
        return str.length > n ? str.substring(0, n) + '…' : str;
    }

    // ── TABLA DETALLE (por distribución) ─────────────────────────
    async function cargarTabla() {
        try {
            const res = await axios.get('/logistica/reportes/tabla?' + qs(buildQS()));
            const rows = res.data;

            if (dtTabla) { dtTabla.destroy(); dtTabla = null; }
            $('#tabla-logistica tbody').empty();

            rows.forEach(r => {
                const estadoBadge = {
                    'Pendiente':   'badge-secondary',
                    'En Proceso':  'badge-warning',
                    'Completada':  'badge-success',
                    'Cancelada':   'badge-danger'
                }[r.estado_label] || 'badge-light';

                const ef = r.total_facturas > 0
                    ? Math.round((r.entregadas / (r.total_facturas - r.anuladas || 1)) * 100)
                    : 0;

                $('#tabla-logistica tbody').append(`
                    <tr>
                        <td>${r.id}</td>
                        <td>${r.fecha}</td>
                        <td>${r.nombre_equipo}</td>
                        <td>${r.creador}</td>
                        <td class="text-center">${r.total_facturas}</td>
                        <td class="text-center text-success font-weight-bold">${r.entregadas}</td>
                        <td class="text-center text-warning">${r.pendientes}</td>
                        <td class="text-center text-danger">${r.anuladas}</td>
                        <td class="text-center">
                            <div class="progress" style="height:16px;min-width:60px">
                                <div class="progress-bar bg-success" style="width:${ef}%">${ef}%</div>
                            </div>
                        </td>
                        <td><span class="badge ${estadoBadge}">${r.estado_label}</span></td>
                    </tr>`);
            });

            dtTabla = $('#tabla-logistica').DataTable({
                language: DT_ES,
                order: [[0, 'desc']],
                pageLength: 15,
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
                responsive: true
            });
        } catch(e) { console.error('Error tabla', e); }
    }

    // ── FUNCIÓN PRINCIPAL: CONSULTAR TODO ────────────────────────
    function consultar() {
        cargarKPIs();
        // Solo recargar charts/tabla de la pestaña activa
        const tabActiva = document.querySelector('#logTabs .nav-link.active')?.getAttribute('href');
        if (tabActiva === '#pane-detalle') {
            cargarTabla();
        } else if (tabActiva === '#pane-facturas') {
            cargarTablaFacturas();
        } else {
            // pestaña resumen (default)
            cargarEvolucion();
            cargarPorEquipo();
            cargarEstados();
        }
    }

    // ── INIT ─────────────────────────────────────────────────────
    function init() {
        // Fechas por defecto: mes actual
        const fi = document.getElementById('l-fi');
        const ff = document.getElementById('l-ff');
        if (fi && !fi.value) fi.value = isoMesInicio();
        if (ff && !ff.value) ff.value = isoHoy();

        // Cargar catálogos y luego disparar carga inicial
        cargarFiltros().then(() => {
            cargarKPIs();
            cargarEvolucion();
            cargarPorEquipo();
            cargarEstados();
        });

        // Cargar tabla al cambiar a pestaña detalle
        document.getElementById('tab-detalle')?.addEventListener('shown.bs.tab', () => cargarTabla());
        document.getElementById('tab-detalle')?.addEventListener('show.bs.tab', () => cargarTabla());

        // Compat Bootstrap 4 (jQuery)
        $('#tab-detalle').on('shown.bs.tab', () => cargarTabla());
        $('#tab-facturas').on('shown.bs.tab', () => cargarTablaFacturas());

        // Mostrar filtro de estado factura solo en pestaña Por Factura
        $('#logTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).attr('href');
            const wrap = document.getElementById('wrap-estado-factura');
            if (wrap) wrap.style.display = (target === '#pane-facturas') ? '' : 'none';
        });
    }

    // ── EXPORTAR CSV ─────────────────────────────────────────────
    async function exportarCSV() {
        const tabActiva = document.querySelector('#logTabs .nav-link.active')?.getAttribute('href');
        const esFact = tabActiva === '#pane-facturas';

        try {
            let rows, headers, csvRows;

            if (esFact) {
                const estadoFact = document.getElementById('l-estado-fact')?.value || '';
                const params = buildQS();
                delete params.estado;
                if (estadoFact) params.estado = estadoFact;
                const res = await axios.get('/logistica/reportes/tabla-facturas?' + qs(params));
                rows = res.data;
                headers = ['Dist.','Fecha Prog.','Hora Salida','Factura','Cliente','Total','Equipo','Estado','Fecha Entrega','Motivo Anulación','Motivo Confirmación'];
                csvRows = [headers.join(',')];
                rows.forEach(r => csvRows.push([
                    r.distribucion_id, r.fecha_programada,
                    r.hora_salida ?? '',
                    `"${r.numero_factura}"`, `"${r.cliente}"`,
                    r.total, `"${r.equipo}"`,
                    r.estado_entrega,
                    r.fecha_entrega_real ?? '',
                    `"${(r.motivo_anulacion  ?? '').replace(/"/g,'\'')}"`,
                    `"${(r.motivo_confirmacion ?? '').replace(/"/g,'\'')}"`,
                ].join(',')));
            } else {
                const res = await axios.get('/logistica/reportes/tabla?' + qs(buildQS()));
                rows = res.data;
                headers = ['ID','Fecha','Equipo','Creador','Total','Entregadas','Pendientes','Anuladas','Estado'];
                csvRows = [headers.join(',')];
                rows.forEach(r => csvRows.push([
                    r.id, r.fecha, `"${r.nombre_equipo}"`, `"${r.creador}"`,
                    r.total_facturas, r.entregadas, r.pendientes, r.anuladas,
                    r.estado_label
                ].join(',')));
            }

            const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url;
            a.download = esFact ? 'logistica_facturas.csv' : 'logistica_distribuciones.csv';
            a.click();
            URL.revokeObjectURL(url);
        } catch(e) { console.error('Error exportar', e); }
    }

    // ── API PÚBLICA ───────────────────────────────────────────────
    return { init, consultar, exportarCSV };

})();

document.addEventListener('DOMContentLoaded', () => dashLogistica.init());
