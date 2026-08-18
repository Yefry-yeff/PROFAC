/**
 * Dashboard de Logística — dashboard-logistica.js
 * Usa: ApexCharts (cargado en layout), jQuery/Axios, DataTables
 */
'use strict';

const dashLogistica = (function () {

    // ── ESTADO INTERNO ───────────────────────────────────────────
    let charts = {};
    let dtTabla = null;

    // Filtro activo por clic en un gráfico del Resumen (estado/equipo/fecha).
    let clickFilter = { estado: null, estadoLabel: null, equipo: null, equipoNombre: null, fecha: null };

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

    // ── FILTRO POR CLIC EN GRÁFICO ────────────────────────────────
    function clickFilterParams() {
        const p = {};
        if (clickFilter.estado) p.cf_estado = clickFilter.estado;
        if (clickFilter.equipo) p.cf_equipo = clickFilter.equipo;
        if (clickFilter.fecha)  p.cf_fecha  = clickFilter.fecha;
        return p;
    }

    function buildQSFiltrado(extra) {
        return Object.assign(buildQS(extra), clickFilterParams());
    }

    function estadoTokenLabel(token) {
        return {
            entregado:   'Entregado',
            pendientes:  'Pendiente',
            parcial:     'Parcial',
            sin_entrega: 'Sin Entregar',
            anulada:     'Anulada'
        }[token] || token;
    }

    function actualizarChipFiltro() {
        const wrap = document.getElementById('resumen-filtro-chip');
        const txt  = document.getElementById('resumen-filtro-txt');
        if (!wrap || !txt) return;

        const partes = [];
        if (clickFilter.estadoLabel) partes.push('Estado: ' + clickFilter.estadoLabel);
        if (clickFilter.equipoNombre) partes.push('Equipo: ' + clickFilter.equipoNombre);
        if (clickFilter.fecha) partes.push('Fecha: ' + clickFilter.fecha);

        if (partes.length) {
            txt.textContent = 'Filtro activo (clic en gráfico) → ' + partes.join(' • ');
            wrap.style.display = '';
        } else {
            wrap.style.display = 'none';
        }
    }

    function refrescarResumenFiltrado() {
        cargarKPIs();
        cargarEvolucion();
        cargarPorEquipo();
        cargarEstados();
        cargarTablaResumen();
    }

    function aplicarClickFiltro(nuevo) {
        clickFilter = Object.assign(
            { estado: null, estadoLabel: null, equipo: null, equipoNombre: null, fecha: null },
            nuevo
        );
        actualizarChipFiltro();
        refrescarResumenFiltrado();
    }

    function limpiarFiltroClick() {
        clickFilter = { estado: null, estadoLabel: null, equipo: null, equipoNombre: null, fecha: null };
        actualizarChipFiltro();
        refrescarResumenFiltrado();
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

    // ── KPIs (grupo Distribución) ─────────────────────────────────
    async function cargarKPIs() {
        ['kpi-dist','kpi-comp','kpi-fact','kpi-entr','kpi-anul']
            .forEach(id => setKpi(id, '…'));
        try {
            const res = await axios.get('/logistica/reportes/kpis?' + qs(buildQSFiltrado()));
            const d = res.data;
            setKpi('kpi-dist',  numFmt(d.distribuciones));
            setKpi('kpi-comp',  numFmt(d.completadas));
            setKpi('kpi-fact',  numFmt(d.total_facturas));
            setKpi('kpi-entr',  numFmt(d.entregadas));
            setKpi('kpi-anul',  numFmt(d.anuladas));
        } catch(e) { console.error('Error KPIs', e); }
    }

    // ── KPIs (grupo Facturación + Efectividad, según fi/ff del reporte) ──
    async function cargarPendientesReales() {
        ['kpi-fact-gen','kpi-pend-asignadas','kpi-pend-sin-asignar','kpi-efect'].forEach(id => setKpi(id, '…'));
        try {
            const res = await axios.get('/logistica/reportes/pendientes-reales?' + qs(buildQS()));
            const d = res.data;
            setKpi('kpi-fact-gen',        numFmt(d.total_generadas));
            setKpi('kpi-pend-asignadas',  numFmt(d.asignadas_pendientes));
            setKpi('kpi-pend-sin-asignar', numFmt(d.sin_asignar));
            setKpi('kpi-efect',           pct(d.efectividad));
            const subGen = document.getElementById('kpi-fact-gen-sub');
            if (subGen) subGen.textContent = `emitidas del ${d.fi} al ${d.ff}`;
            const subEfect = document.getElementById('kpi-efect-sub');
            if (subEfect) subEfect.textContent = `${numFmt(d.total_generadas)} generadas − ${numFmt(d.total)} pendientes reales`;
        } catch(e) { console.error('Error pendientes reales', e); }
    }

    // ── CHART EVOLUCIÓN ──────────────────────────────────────────
    async function cargarEvolucion() {
        showSpin('chart-evolucion');
        try {
            const res = await axios.get('/logistica/reportes/evolucion?' + qs(buildQSFiltrado()));
            const rows = res.data;
            const cats   = rows.map(r => r.fecha);
            const entreg = rows.map(r => parseInt(r.entregadas || 0));
            const pend   = rows.map(r => parseInt(r.pendientes  || 0));
            const anuled = rows.map(r => parseInt(r.anuladas    || 0));
            const ESTADOS_SERIE = ['entregado', 'pendientes', 'anulada'];

            destroyChart('evolucion');
            document.getElementById('chart-evolucion').innerHTML = '';
            charts.evolucion = new ApexCharts(document.getElementById('chart-evolucion'), {
                chart: { type: 'area', height: 320, toolbar: { show: false },
                    fontFamily: "'Source Sans Pro', sans-serif",
                    events: {
                        dataPointSelection: (event, chartContext, config) => {
                            const estado = ESTADOS_SERIE[config.seriesIndex];
                            const fecha  = cats[config.dataPointIndex];
                            if (!estado || !fecha) return;
                            aplicarClickFiltro({ estado, estadoLabel: estadoTokenLabel(estado), fecha });
                        }
                    }
                },
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
            const res = await axios.get('/logistica/reportes/por-equipo?' + qs(buildQSFiltrado()));
            const rows = res.data;
            const cats   = rows.map(r => r.equipo);
            const entreg = rows.map(r => parseInt(r.entregadas || 0));
            const pend   = rows.map(r => parseInt(r.pendientes  || 0));
            const anuled = rows.map(r => parseInt(r.anuladas    || 0));
            const ESTADOS_SERIE = ['entregado', 'pendientes', 'anulada'];

            destroyChart('equipos');
            document.getElementById('chart-equipos').innerHTML = '';
            charts.equipos = new ApexCharts(document.getElementById('chart-equipos'), {
                chart: { type: 'bar', height: 300, stacked: false,
                    toolbar: { show: false }, fontFamily: "'Source Sans Pro', sans-serif",
                    events: {
                        dataPointSelection: (event, chartContext, config) => {
                            const estado = ESTADOS_SERIE[config.seriesIndex];
                            const row    = rows[config.dataPointIndex];
                            if (!estado || !row) return;
                            aplicarClickFiltro({
                                estado, estadoLabel: estadoTokenLabel(estado),
                                equipo: row.equipo_id, equipoNombre: row.equipo
                            });
                        }
                    }
                },
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
            const res = await axios.get('/logistica/reportes/estados?' + qs(buildQSFiltrado()));
            const rows = res.data;
            const labels = rows.map(r => ESTADO_LABELS[r.estado_entrega] || r.estado_entrega);
            const values = rows.map(r => parseInt(r.total || 0));
            const colors = rows.map(r => ESTADO_COLORS[r.estado_entrega] || GREY);

            destroyChart('estados');
            document.getElementById('chart-estados').innerHTML = '';
            charts.estados = new ApexCharts(document.getElementById('chart-estados'), {
                chart: { type: 'donut', height: 300,
                    fontFamily: "'Source Sans Pro', sans-serif",
                    events: {
                        dataPointSelection: (event, chartContext, config) => {
                            const row = rows[config.dataPointIndex];
                            if (!row) return;
                            aplicarClickFiltro({
                                estado: row.estado_entrega,
                                estadoLabel: ESTADO_LABELS[row.estado_entrega] || row.estado_entrega
                            });
                        }
                    }
                },
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

    // ── TABLA RESUMEN (pestaña Resumen: pendientes/entregadas/anuladas/sin asignar) ──
    let dtResumen = null;

    async function cargarTablaResumen() {
        try {
            const params = buildQSFiltrado();
            const [resAsig, resSin] = await Promise.all([
                axios.get('/logistica/reportes/tabla-facturas?' + qs(params)),
                axios.get('/logistica/reportes/facturas-sin-asignar?' + qs(params)),
            ]);
            const rows       = resAsig.data;
            const sinAsignar = resSin.data;

            if (dtResumen) { dtResumen.destroy(); dtResumen = null; }
            $('#tabla-resumen-detalle tbody').empty();

            const ESTADO_BADGE = {
                entregado:   { cls: 'badge-success',   lbl: 'Entregado'     },
                parcial:     { cls: 'badge-warning',   lbl: 'Parcial'       },
                sin_entrega: { cls: 'badge-secondary', lbl: 'Sin Entregar'  },
                anulada:     { cls: 'badge-danger',    lbl: 'Anulada'       }
            };

            rows.forEach(r => {
                const b = ESTADO_BADGE[r.estado_entrega] || { cls: 'badge-light', lbl: r.estado_entrega };
                const rowCls = r.estado_entrega === 'anulada' ? 'table-danger' : '';

                $('#tabla-resumen-detalle tbody').append(`
                    <tr class="${rowCls}">
                        <td class="font-weight-bold">${escHtml(r.numero_factura)}</td>
                        <td>${escHtml(r.cliente)}</td>
                        <td>${escHtml(r.equipo)}</td>
                        <td>${r.fecha_programada}</td>
                        <td class="text-center"><span class="badge ${b.cls}">${b.lbl}</span></td>
                        <td>${r.fecha_entrega_real ?? '<span class="text-muted">—</span>'}</td>
                    </tr>`);
            });

            sinAsignar.forEach(r => {
                $('#tabla-resumen-detalle tbody').append(`
                    <tr class="table-warning">
                        <td class="font-weight-bold">${escHtml(r.numero_factura)}</td>
                        <td>${escHtml(r.cliente)}</td>
                        <td><span class="text-muted font-italic">Sin asignar</span></td>
                        <td>${r.fecha_emision}</td>
                        <td class="text-center"><span class="badge" style="background:#dc6803;color:#fff">Sin Asignar</span></td>
                        <td><span class="text-muted">—</span></td>
                    </tr>`);
            });

            dtResumen = $('#tabla-resumen-detalle').DataTable({
                language: DT_ES,
                order: [[3, 'desc']],
                pageLength: 10,
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
                responsive: true
            });
        } catch(e) { console.error('Error tabla resumen', e); }
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

    // ── TABLA POR EQUIPO ─────────────────────────────────────────
    let dtEquipos = null;

    async function cargarTablaEquipos() {
        const params = buildQS();
        delete params.estado; // esta pestaña no usa el filtro de estado de distribución

        try {
            const res = await axios.get('/logistica/reportes/tabla-equipos?' + qs(params));
            const rows = res.data;

            if (dtEquipos) { dtEquipos.destroy(); dtEquipos = null; }
            $('#tabla-equipos tbody').empty();

            rows.forEach(r => {
                const miembrosHtml = (r.miembros && r.miembros.length)
                    ? r.miembros.map(m =>
                        `<div>${escHtml(m.name)} <span class="badge badge-light border">${pct(m.porcentaje_comision)}</span></div>`
                      ).join('')
                    : '<span class="text-muted">— Sin actividad en el período —</span>';

                $('#tabla-equipos tbody').append(`
                    <tr>
                        <td class="font-weight-bold">${escHtml(r.equipo)}</td>
                        <td>${r.fecha_fmt}</td>
                        <td class="text-center">${r.hora_salida ? escHtml(r.hora_salida) : '<span class="text-muted">—</span>'}</td>
                        <td class="text-center">${r.hora_ultima_entrega ? escHtml(r.hora_ultima_entrega) : '<span class="text-muted">—</span>'}</td>
                        <td class="text-center">${r.hora_llegada ? escHtml(r.hora_llegada) : '<span class="text-muted">—</span>'}</td>
                        <td>${miembrosHtml}</td>
                        <td class="text-center">
                            <a href="javascript:void(0)" class="font-weight-bold text-success fact-entregadas-link"
                               data-dist-ids="${r.dist_ids}" data-equipo-nombre="${escHtml(r.equipo)}" data-fecha="${r.fecha_fmt}"
                               title="Ver facturas entregadas">
                                ${numFmt(r.facturas_entregadas)}
                            </a>
                        </td>
                    </tr>`);
            });

            dtEquipos = $('#tabla-equipos').DataTable({
                language: DT_ES,
                order: [[1, 'desc']],
                pageLength: 15,
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
                responsive: true,
                columnDefs: [
                    { targets: [5], orderable: false }
                ]
            });
        } catch(e) { console.error('Error tabla equipos', e); }
    }

    async function verDetalleEquipo(distIds, nombreEquipo, fecha) {
        document.getElementById('detEquipoNombre').textContent =
            (nombreEquipo || '') + (fecha ? ` — ${fecha}` : '');
        $('#detEquipoTablaBody').html(
            '<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>'
        );
        $('#modalDetalleEquipo').modal('show');

        try {
            const res = await axios.get('/logistica/reportes/detalle-equipo?' + qs({ dist_ids: distIds }));
            const rows = res.data;

            if (!rows.length) {
                $('#detEquipoTablaBody').html(
                    '<tr><td colspan="5" class="text-center text-muted py-4">Sin facturas entregadas en el período</td></tr>'
                );
                return;
            }

            $('#detEquipoTablaBody').html(rows.map(r => `
                <tr>
                    <td class="font-weight-bold">${escHtml(r.numero_factura)}</td>
                    <td>${escHtml(r.cliente)}</td>
                    <td>${escHtml(r.direccion_entrega) || '<span class="text-muted">—</span>'}</td>
                    <td class="text-center">${r.hora_entrega ? escHtml(r.hora_entrega) : '<span class="text-muted">—</span>'}</td>
                    <td class="text-center">
                        ${Number(r.tiene_hallazgo)
                            ? '<span class="badge badge-danger">Sí</span>'
                            : '<span class="badge badge-success">No</span>'}
                    </td>
                </tr>`).join(''));
        } catch(e) {
            console.error('Error detalle equipo', e);
            $('#detEquipoTablaBody').html(
                '<tr><td colspan="5" class="text-center text-danger py-4">Error al cargar el detalle</td></tr>'
            );
        }
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
        // Un nuevo "Consultar" global limpia cualquier filtro de clic pendiente
        clickFilter = { estado: null, estadoLabel: null, equipo: null, equipoNombre: null, fecha: null };
        actualizarChipFiltro();

        cargarKPIs();
        cargarPendientesReales();
        // Solo recargar charts/tabla de la pestaña activa
        const tabActiva = document.querySelector('#logTabs .nav-link.active')?.getAttribute('href');
        if (tabActiva === '#pane-detalle') {
            cargarTabla();
        } else if (tabActiva === '#pane-facturas') {
            cargarTablaFacturas();
        } else if (tabActiva === '#pane-equipos') {
            cargarTablaEquipos();
        } else {
            // pestaña resumen (default)
            cargarEvolucion();
            cargarPorEquipo();
            cargarEstados();
            cargarTablaResumen();
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
            cargarPendientesReales();
            cargarEvolucion();
            cargarPorEquipo();
            cargarEstados();
            cargarTablaResumen();
        });

        // Cargar tabla al cambiar a pestaña detalle
        document.getElementById('tab-detalle')?.addEventListener('shown.bs.tab', () => cargarTabla());
        document.getElementById('tab-detalle')?.addEventListener('show.bs.tab', () => cargarTabla());

        // Compat Bootstrap 4 (jQuery)
        $('#tab-detalle').on('shown.bs.tab', () => cargarTabla());
        $('#tab-facturas').on('shown.bs.tab', () => cargarTablaFacturas());
        $('#tab-equipos').on('shown.bs.tab', () => cargarTablaEquipos());

        // Ver detalle de entregas de un equipo (delegado, la tabla se re-renderiza)
        $(document).on('click', '.fact-entregadas-link', function () {
            const distIds  = $(this).data('dist-ids');
            const nombre   = $(this).data('equipo-nombre');
            const fecha    = $(this).data('fecha');
            verDetalleEquipo(distIds, nombre, fecha);
        });

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

    // ── EXPORTAR EXCEL DETALLADO (Por Equipo, por factura) ────────
    function exportarExcelEquipos() {
        const params = buildQS();
        delete params.estado;
        window.open('/logistica/reportes/exportar-excel-equipos?' + qs(params), '_blank');
    }

    // ── EXPORTAR EXCEL (Por Distribución) ─────────────────────────
    function exportarExcelDistribucion() {
        window.open('/logistica/reportes/exportar-excel-distribucion?' + qs(buildQS()), '_blank');
    }

    // ── EXPORTAR EXCEL (Por Factura) ──────────────────────────────
    function exportarExcelFacturas() {
        const estadoFact = document.getElementById('l-estado-fact')?.value || '';
        const params = buildQS();
        delete params.estado;
        if (estadoFact) params.estado = estadoFact;
        window.open('/logistica/reportes/exportar-excel-facturas?' + qs(params), '_blank');
    }

    // ── EXPORTAR EXCEL (Resumen → Detalle de Facturas, incluye sin asignar y respeta el filtro de clic) ──
    function exportarExcelResumenDetalle() {
        window.open('/logistica/reportes/exportar-excel-resumen-detalle?' + qs(buildQSFiltrado()), '_blank');
    }

    // ── API PÚBLICA ───────────────────────────────────────────────
    return {
        init, consultar, exportarCSV, limpiarFiltroClick,
        exportarExcelEquipos, exportarExcelDistribucion, exportarExcelFacturas, exportarExcelResumenDetalle
    };

})();

document.addEventListener('DOMContentLoaded', () => dashLogistica.init());
