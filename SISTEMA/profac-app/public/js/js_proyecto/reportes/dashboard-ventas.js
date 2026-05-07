/**
 * Dashboard de Ventas BI — dashboard-ventas.js
 * Usa: ApexCharts (CDN en layout), Axios / jQuery, DataTables + Buttons
 */
'use strict';

const dashboardVentas = (function () {

    // ── ESTADO INTERNO ───────────────────────────────────────────────────────
    let charts = {};          // instancias ApexCharts activas
    let dtSemanal = null;     // DataTable instancia pestaña 2
    let dtVendedores = null;
    let dtClientes = null;
    let dtProductos = null;
    let catalogos = {};       // vendedores, tipos, categorias, anios

    // ── DATOS EN MEMORIA (para exportar) ─────────────────────────────────────
    let lastKpis = null;
    let lastMensual = [];
    let lastVendedores = [];
    let lastClientes = [];
    let lastProductos = [];

    const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

    const COLORS_LINE = ['#4e73df','#1cc88a','#f6c23e','#e74a3b','#858796','#36b9cc'];

    // ── HELPERS ──────────────────────────────────────────────────────────────
    function fmtMoney(n) {
        return 'L. ' + Number(n).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function fmtNum(n) {
        return Number(n).toLocaleString('es-HN');
    }
    function showLoader(id) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>';
    }
    function isoHoy() { return new Date().toISOString().split('T')[0]; }
    function isoInicio(offsetDays) {
        const d = new Date();
        d.setDate(d.getDate() - offsetDays);
        return d.toISOString().split('T')[0];
    }
    function destroyChart(key) {
        if (charts[key]) { try { charts[key].destroy(); } catch(e){} delete charts[key]; }
    }
    function buildParams(obj) {
        return Object.entries(obj).filter(([,v]) => v !== '' && v !== null && v !== undefined)
            .map(([k,v]) => Array.isArray(v) ? v.map(i => `${k}[]=${i}`).join('&') : `${k}=${encodeURIComponent(v)}`)
            .join('&');
    }

    // ── CATÁLOGOS ─────────────────────────────────────────────────────────────
    async function cargarCatalogos() {
        try {
            const resp = await axios.get('/reporte/dashboard/catalogo-filtros');
            catalogos = resp.data;

            const { vendedores, tiposCliente, categorias, anios } = catalogos;

            // Año toggle pills (P1)
            const pillsContainer = document.getElementById('h-anios-pills');
            anios.forEach(a => {
                const btn = document.createElement('button');
                btn.type = 'button';
                const isCurrentYear = a.anio == new Date().getFullYear();
                btn.className = 'btn btn-sm year-pill ' + (isCurrentYear ? 'btn-primary active' : 'btn-outline-primary');
                btn.textContent = a.anio;
                btn.dataset.year = a.anio;
                btn.addEventListener('click', function () {
                    this.classList.toggle('active');
                    this.classList.toggle('btn-primary');
                    this.classList.toggle('btn-outline-primary');
                });
                pillsContainer.appendChild(btn);
            });

            // Todos los selects de vendedor y tipo cliente
            [['h-vendedor','s-vendedor','a-vendedor'], ['h-tipo-cliente','s-tipo-cliente','a-tipo-cliente']].forEach(([...ids], idx) => {
                const data = idx === 0 ? vendedores : tiposCliente;
                const label = idx === 0 ? 'name' : 'descripcion';
                ids.forEach(id => {
                    const sel = document.getElementById(id);
                    if (!sel) return;
                    data.forEach(item => {
                        sel.appendChild(new Option(item[label], item.id));
                    });
                });
            });

            // Categorías (P3)
            const selCat = document.getElementById('a-categoria');
            categorias.forEach(c => selCat.appendChild(new Option(c.descripcion, c.id)));

        } catch(e) { console.error('Error cargando catálogos', e); }
    }

    // ── PESTAÑA 1: HISTÓRICO ─────────────────────────────────────────────────
    async function cargarHistorico() {
        const vendedor  = document.getElementById('h-vendedor').value;
        const tc        = document.getElementById('h-tipo-cliente').value;
        const aniosSel  = Array.from(document.querySelectorAll('#h-anios-pills .year-pill.active')).map(b => b.dataset.year);
        const aniosEfectivos = aniosSel.length ? aniosSel : [String(new Date().getFullYear())];

        const kpiParams = buildParams({ anios: aniosEfectivos, vendedor, tipo_cliente: tc });

        // KPIs
        try {
            const r = await axios.get('/reporte/dashboard/kpis?' + kpiParams);
            const d = r.data;
            lastKpis = d;
            document.getElementById('kpi-total').textContent      = fmtMoney(d.total_ventas);
            document.getElementById('kpi-facturas').textContent   = fmtNum(d.total_facturas);
            document.getElementById('kpi-ticket').textContent     = fmtMoney(d.ticket_promedio);
            document.getElementById('kpi-clientes').textContent   = fmtNum(d.clientes_unicos);
            document.getElementById('kpi-descuentos').textContent = fmtMoney(d.total_descuentos);
            document.getElementById('kpi-mejor-mes').textContent  = d.mejor_mes ?? '—';
            document.getElementById('kpi-mejor-vend').textContent = d.mejor_vendedor ?? '—';

            const crec = d.crecimiento;
            const kpiCrec = document.getElementById('kpi-crecimiento');
            if (crec === null) { kpiCrec.textContent = 'N/D'; kpiCrec.className = 'h5 mb-0 font-weight-bold text-secondary'; }
            else if (crec >= 0) { kpiCrec.textContent = '+' + crec + '%'; kpiCrec.className = 'h5 mb-0 font-weight-bold text-success'; }
            else { kpiCrec.textContent = crec + '%'; kpiCrec.className = 'h5 mb-0 font-weight-bold text-danger'; }
        } catch(e) { console.error('KPIs error', e); }

        // Ventas por mes
        const aniosParam = buildParams({ anios: aniosEfectivos, vendedor, tipo_cliente: tc });
        try {
            const r = await axios.get('/reporte/dashboard/ventas-por-mes?' + aniosParam);
            lastMensual = r.data;
            dibujarLineaMensual(r.data, aniosSel);
            dibujarBarrasAgrupadas(r.data, aniosSel);
            dibujarCrecimientoMensual(r.data, aniosSel);
        } catch(e) { console.error('Ventas por mes error', e); }

        // Heatmap (todos los años disponibles)
        try {
            const minAnio = catalogos.anios && catalogos.anios.length ? Math.min(...catalogos.anios.map(a => a.anio)) : 2022;
            const r = await axios.get('/reporte/dashboard/heatmap?' + buildParams({ fecha_inicio: minAnio + '-01-01', fecha_final: isoHoy() }));
            dibujarHeatmap(r.data);
        } catch(e) { console.error('Heatmap error', e); }
    }

    function dibujarLineaMensual(data, anios) {
        destroyChart('evolucion');
        const aniosUnicos = [...new Set(data.map(d => String(d.anio)))];
        const series = aniosUnicos.map((anio, i) => ({
            name: anio,
            data: MESES.map((_, m) => {
                const fila = data.find(d => String(d.anio) === anio && Number(d.mes) === m + 1);
                return fila ? parseFloat(fila.total) : 0;
            })
        }));

        charts.evolucion = new ApexCharts(document.getElementById('chart-evolucion'), {
            series,
            chart: { type: 'line', height: 280, toolbar: { show: true }, zoom: { enabled: true } },
            stroke: { width: 3, curve: 'smooth' },
            colors: COLORS_LINE,
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: v => 'L.' + (v/1000).toFixed(0) + 'K' } },
            tooltip: { y: { formatter: v => fmtMoney(v) } },
            legend: { position: 'top' },
            grid: { borderColor: '#e3e6f0' },
            markers: { size: 4 },
            dataLabels: {
                enabled: true,
                formatter: v => v === 0 ? '' : 'L.' + (v/1000).toFixed(0) + 'K',
                style: { fontSize: '9px' },
                background: { enabled: true, borderRadius: 2, padding: 2, opacity: 0.8 }
            }
        });
        charts.evolucion.render();
    }

    function dibujarBarrasAgrupadas(data, anios) {
        destroyChart('barras');
        const aniosUnicos = [...new Set(data.map(d => String(d.anio)))];
        const series = aniosUnicos.map((anio, i) => ({
            name: anio,
            data: MESES.map((_, m) => {
                const fila = data.find(d => String(d.anio) === anio && Number(d.mes) === m + 1);
                return fila ? parseFloat(fila.total) : 0;
            })
        }));

        charts.barras = new ApexCharts(document.getElementById('chart-barras'), {
            series,
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 2, columnWidth: '70%' } },
            colors: COLORS_LINE,
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: v => 'L.' + (v/1000).toFixed(0) + 'K' } },
            tooltip: { y: { formatter: v => fmtMoney(v) } },
            legend: { position: 'top' },
            dataLabels: {
                enabled: true,
                formatter: v => v === 0 ? '' : 'L.' + (v/1000).toFixed(0) + 'K',
                style: { fontSize: '8px' },
                offsetY: -4
            }
        });
        charts.barras.render();
    }

    function dibujarCrecimientoMensual(data, anios) {
        destroyChart('crecimiento');
        const aniosUnicos = [...new Set(data.map(d => String(d.anio)))].sort();
        if (aniosUnicos.length < 2) {
            document.getElementById('chart-crecimiento').innerHTML =
                '<p class="text-center text-muted small pt-4">Selecciona al menos 2 años para comparar crecimiento.</p>';
            return;
        }
        const anioBase = aniosUnicos[aniosUnicos.length - 2];
        const anioAct  = aniosUnicos[aniosUnicos.length - 1];

        const crecData = MESES.map((_, m) => {
            const base = data.find(d => String(d.anio) === anioBase && Number(d.mes) === m + 1);
            const act  = data.find(d => String(d.anio) === anioAct  && Number(d.mes) === m + 1);
            if (!base || !act || parseFloat(base.total) === 0) return null;
            return parseFloat(((parseFloat(act.total) - parseFloat(base.total)) / parseFloat(base.total) * 100).toFixed(2));
        });

        const diffData = MESES.map((_, m) => {
            const base = data.find(d => String(d.anio) === anioBase && Number(d.mes) === m + 1);
            const act  = data.find(d => String(d.anio) === anioAct  && Number(d.mes) === m + 1);
            if (!base || !act) return null;
            return parseFloat(parseFloat(act.total) - parseFloat(base.total));
        });

        function fmtDiff(v) {
            if (v === null) return 'N/D';
            const sign = v >= 0 ? '+' : '';
            if (Math.abs(v) >= 1000000) return sign + 'L.' + (v/1000000).toFixed(1) + 'M';
            return sign + 'L.' + (v/1000).toFixed(0) + 'K';
        }

        charts.crecimiento = new ApexCharts(document.getElementById('chart-crecimiento'), {
            series: [{ name: 'Crecimiento %', data: crecData }],
            chart: { type: 'bar', height: 280, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 3, colors: { ranges: [{ from: -9999, to: 0, color: '#e74a3b' }, { from: 0.001, to: 9999, color: '#1cc88a' }] } } },
            xaxis: { categories: MESES },
            yaxis: { labels: { formatter: v => (v === null ? 'N/D' : v + '%') } },
            tooltip: {
                y: {
                    formatter: (v, { dataPointIndex }) => {
                        const diff = diffData[dataPointIndex];
                        return (v === null ? 'N/D' : v + '%') + ' (' + fmtDiff(diff) + ')';
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: (v, opts) => {
                    const diff = diffData[opts.dataPointIndex];
                    if (v === null || diff === null) return '';
                    return fmtDiff(diff);
                },
                style: { fontSize: '9px', colors: ['#fff'] },
                dropShadow: { enabled: false }
            },
            title: { text: `${anioBase} → ${anioAct}`, align: 'center', style: { fontSize: '11px' } }
        });
        charts.crecimiento.render();
    }

    function dibujarHeatmap(data) {
        destroyChart('heatmap');
        const aniosUnicos = [...new Set(data.map(d => String(d.anio)))].sort();
        const series = aniosUnicos.map(anio => ({
            name: anio,
            data: MESES.map((mes, m) => {
                const fila = data.find(d => String(d.anio) === anio && Number(d.mes) === m + 1);
                return { x: mes, y: fila ? Math.round(parseFloat(fila.total)) : 0 };
            })
        }));

        charts.heatmap = new ApexCharts(document.getElementById('chart-heatmap'), {
            series,
            chart: { type: 'heatmap', height: 260, toolbar: { show: false } },
            dataLabels: {
                enabled: true,
                formatter: v => v === 0 ? '' : 'L.' + (v/1000).toFixed(0) + 'K',
                style: { fontSize: '8px', colors: ['#fff'] }
            },
            colors: ['#4e73df'],
            tooltip: { y: { formatter: v => fmtMoney(v) } },
            xaxis: { type: 'category' }
        });
        charts.heatmap.render();
    }

    // ── PESTAÑA 2: SEMANAL ───────────────────────────────────────────────────
    async function cargarSemanal() {
        const fi  = document.getElementById('s-fi').value;
        const ff  = document.getElementById('s-ff').value;
        const vend = document.getElementById('s-vendedor').value;
        const tc  = document.getElementById('s-tipo-cliente').value;
        const params = buildParams({ fecha_inicio: fi, fecha_final: ff, vendedor: vend, tipo_cliente: tc });

        // KPIs resumen
        try {
            const r = await axios.get('/reporte/dashboard/resumen-semanal?' + params);
            const d = r.data;
            document.getElementById('s-kpi-total').textContent    = fmtMoney(d.total);
            document.getElementById('s-kpi-facturas').textContent = fmtNum(d.facturas);
            document.getElementById('s-kpi-ticket').textContent   = fmtMoney(d.ticket_promedio);
            document.getElementById('s-kpi-vend').textContent     = d.mejor_vendedor;
            document.getElementById('s-kpi-cliente').textContent  = d.mejor_cliente;
            document.getElementById('s-kpi-mejor-dia').textContent= d.mejor_dia;
            dibujarPorDia(d.por_dia);
        } catch(e) { console.error('Resumen semanal error', e); }

        // Tipo cliente donut
        try {
            const r2 = await axios.get('/reporte/dashboard/participacion-tipo-cliente?' + buildParams({ fecha_inicio: fi, fecha_final: ff }));
            dibujarTipoClienteDonut(r2.data, 'chart-tipo-cliente-sem');
        } catch(e) {}

        // Ranking vendedores período
        try {
            const r3 = await axios.get('/reporte/dashboard/top-vendedores?' + params);
            dibujarRankingVendedoresPeriodo(r3.data.slice(0,8));
        } catch(e) {}

        // Tabla
        cargarTablaDetalle(params);
    }

    function dibujarPorDia(data) {
        destroyChart('por-dia');
        const labels = data.map(d => d.dia);
        const valores = data.map(d => parseFloat(d.total));
        charts['por-dia'] = new ApexCharts(document.getElementById('chart-por-dia'), {
            series: [{ name: 'Ventas', data: valores }],
            chart: { type: 'bar', height: 240, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, distributed: true } },
            colors: COLORS_LINE,
            xaxis: { categories: labels },
            yaxis: { labels: { formatter: v => 'L.' + (v/1000).toFixed(0) + 'K' } },
            tooltip: { y: { formatter: v => fmtMoney(v) } },
            legend: { show: false },
            dataLabels: {
                enabled: true,
                formatter: v => v === 0 ? '' : 'L.' + (v/1000).toFixed(0) + 'K',
                style: { fontSize: '9px' },
                offsetY: -4
            }
        });
        charts['por-dia'].render();
    }

    function dibujarTipoClienteDonut(data, elId) {
        destroyChart(elId);
        const labels  = data.map(d => d.tipo_cliente);
        const valores = data.map(d => parseFloat(d.total));
        charts[elId] = new ApexCharts(document.getElementById(elId), {
            series: valores,
            chart: { type: 'donut', height: 240 },
            labels,
            colors: ['#4e73df','#1cc88a','#f6c23e','#e74a3b'],
            tooltip: { y: { formatter: v => fmtMoney(v) } },
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { formatter: (v) => v.toFixed(1) + '%' }
        });
        charts[elId].render();
    }

    function dibujarRankingVendedoresPeriodo(data) {
        destroyChart('ranking-vend-sem');
        const cats = data.map(d => d.vendedor.split(' ')[0]);
        const vals = data.map(d => parseFloat(d.total_ventas));
        charts['ranking-vend-sem'] = new ApexCharts(document.getElementById('chart-ranking-vend-sem'), {
            series: [{ name: 'Total', data: vals }],
            chart: { type: 'bar', height: 240, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 3 } },
            xaxis: { categories: cats, labels: { formatter: v => 'L.' + (v/1000).toFixed(0) + 'K' } },
            tooltip: { y: { formatter: v => fmtMoney(v) } },
            dataLabels: {
                enabled: true,
                formatter: v => 'L.' + (v/1000).toFixed(0) + 'K',
                style: { fontSize: '9px' }
            },
            colors: ['#4e73df']
        });
        charts['ranking-vend-sem'].render();
    }

    function cargarTablaDetalle(params) {
        if (dtSemanal) { dtSemanal.destroy(); dtSemanal = null; }
        dtSemanal = $('#tabla-semanal').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/reporte/dashboard/ventas-semanales?' + params,
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                { data: 'fecha' },
                { data: 'dia_semana' },
                { data: 'semana_iso' },
                { data: 'documento', defaultContent: '—' },
                { data: 'cliente' },
                { data: 'vendedor' },
                { data: 'tipo_cliente' },
                { data: 'subtotal',  className: 'text-right' },
                { data: 'impuesto', className: 'text-right' },
                { data: 'descuento', className: 'text-right' },
                { data: 'total',     className: 'text-right font-weight-bold' }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i>', className: 'd-none buttons-excel' },
                { extend: 'csv',   text: '<i class="fas fa-file-csv"></i>',  className: 'd-none' },
                { extend: 'pdf',   text: '<i class="fas fa-file-pdf"></i>',  className: 'd-none' }
            ]
        });
    }

    // ── PESTAÑA 3: ANALÍTICA ─────────────────────────────────────────────────
    async function cargarAnalitica() {
        const fi  = document.getElementById('a-fi').value;
        const ff  = document.getElementById('a-ff').value;
        const vend = document.getElementById('a-vendedor').value;
        const tc  = document.getElementById('a-tipo-cliente').value;
        const cat = document.getElementById('a-categoria').value;
        const params = buildParams({ fecha_inicio: fi, fecha_final: ff, vendedor: vend, tipo_cliente: tc });
        const paramsProducto = buildParams({ fecha_inicio: fi, fecha_final: ff, categoria: cat });

        await Promise.all([
            cargarVendedores(params),
            cargarClientes(params),
            cargarProductos(paramsProducto)
        ]);
    }

    async function cargarVendedores(params) {
        try {
            const r = await axios.get('/reporte/dashboard/top-vendedores?' + params);
            const data = r.data;

            // Gráfica barra horizontal
            destroyChart('rank-vend');
            const names = data.map(d => d.vendedor.split(' ')[0] + ' ' + (d.vendedor.split(' ')[1] || ''));
            const totals = data.map(d => d.total_ventas);
            charts['rank-vend'] = new ApexCharts(document.getElementById('chart-rank-vend'), {
                series: [{ name: 'Total', data: totals }],
                chart: { type: 'bar', height: 300, toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 3, distributed: true } },
                xaxis: { categories: names, labels: { formatter: v => 'L.' + (v/1000000).toFixed(1) + 'M' } },
                tooltip: { y: { formatter: v => fmtMoney(v) } },
                dataLabels: {
                    enabled: true,
                    formatter: v => 'L.' + (v/1000000).toFixed(2) + 'M',
                    style: { fontSize: '9px' }
                },
                legend: { show: false }
            });
            charts['rank-vend'].render();

            // Donut participación
            destroyChart('part-vend');
            charts['part-vend'] = new ApexCharts(document.getElementById('chart-part-vend'), {
                series: totals,
                chart: { type: 'pie', height: 300 },
                labels: names,
                colors: COLORS_LINE,
                tooltip: { y: { formatter: v => fmtMoney(v) } },
                legend: { position: 'right', fontSize: '11px' },
                dataLabels: { formatter: (v) => v.toFixed(1) + '%' }
            });
            charts['part-vend'].render();

            // Tabla (data API — evita cabeceras en blanco)
            lastVendedores = data;
            if (dtVendedores) { dtVendedores.destroy(); dtVendedores = null; }
            dtVendedores = $('#tabla-vendedores').DataTable({
                data: data,
                columns: [
                    { title: '#',               render: (d,t,r,m) => m.row + 1 },
                    { title: 'Vendedor',         data: 'vendedor' },
                    { title: 'Facturas',         data: 'facturas',          className: 'text-center', render: v => fmtNum(v) },
                    { title: 'Clientes',         data: 'clientes_atendidos', className: 'text-center', render: v => fmtNum(v) },
                    { title: 'Total Ventas',     data: 'total_ventas',      className: 'text-right',  render: v => fmtMoney(v) },
                    { title: 'Ticket Promedio',  data: 'ticket_promedio',   className: 'text-right',  render: v => fmtMoney(v) },
                    { title: 'Participación %',  data: 'participacion',     className: 'text-center',
                      render: v => `<div class="progress d-inline-flex" style="height:16px;min-width:80px"><div class="progress-bar bg-primary" style="width:${v}%"></div></div><small class="ml-1">${v}%</small>` }
                ],
                paging: false, info: false, searching: false, order: [], ordering: false
            });

        } catch(e) { console.error('Vendedores error', e); }
    }

    async function cargarClientes(params) {
        try {
            const r = await axios.get('/reporte/dashboard/top-clientes?' + params + '&limite=50');
            const data = r.data;

            // Top 15 barra horizontal
            destroyChart('top-cli');
            const top15 = data.slice(0, 15);
            charts['top-cli'] = new ApexCharts(document.getElementById('chart-top-cli'), {
                series: [{ name: 'Total', data: top15.map(d => d.total_comprado) }],
                chart: { type: 'bar', height: 300, toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 3 } },
                xaxis: { categories: top15.map(d => d.cliente.substring(0,25)), labels: { formatter: v => 'L.' + (v/1000).toFixed(0) + 'K' } },
                tooltip: { y: { formatter: v => fmtMoney(v) } },
                dataLabels: {
                    enabled: true,
                    formatter: v => 'L.' + (v/1000).toFixed(0) + 'K',
                    style: { fontSize: '9px' }
                },
                colors: ['#1cc88a']
            });
            charts['top-cli'].render();

            // ABC donut
            destroyChart('abc-cli');
            const abc = { A: 0, B: 0, C: 0 };
            data.forEach(d => { if (d.clasificacion_abc in abc) abc[d.clasificacion_abc] += d.total_comprado; });
            charts['abc-cli'] = new ApexCharts(document.getElementById('chart-abc-cli'), {
                series: [abc.A, abc.B, abc.C],
                labels: ['Clase A (top 70%)', 'Clase B (70–90%)', 'Clase C (90–100%)'],
                chart: { type: 'donut', height: 300 },
                colors: ['#1cc88a','#f6c23e','#e74a3b'],
                tooltip: { y: { formatter: v => fmtMoney(v) } },
                legend: { position: 'bottom' }
            });
            charts['abc-cli'].render();

            // Tabla (data API — evita cabeceras en blanco)
            lastClientes = data;
            if (dtClientes) { dtClientes.destroy(); dtClientes = null; }
            dtClientes = $('#tabla-clientes').DataTable({
                data: data,
                columns: [
                    { title: '#',               render: (d,t,r,m) => m.row + 1 },
                    { title: 'Cliente',          data: 'cliente' },
                    { title: 'Tipo',             data: 'tipo_cliente' },
                    { title: 'ABC',              data: 'clasificacion_abc', render: v => { const c = v==='A'?'success':(v==='B'?'warning':'danger'); return `<span class="badge badge-${c}">${v}</span>`; } },
                    { title: 'Facturas',         data: 'facturas',        className: 'text-center', render: v => fmtNum(v) },
                    { title: 'Total Comprado',   data: 'total_comprado',  className: 'text-right',  render: v => fmtMoney(v) },
                    { title: 'Ticket Prom.',     data: 'ticket_promedio', className: 'text-right',  render: v => fmtMoney(v) },
                    { title: 'Última Compra',    data: 'ultima_compra' },
                    { title: 'Días sin comprar', data: 'dias_sin_comprar', className: 'text-center' },
                    { title: 'Estado', data: null, render: (d,t,r) => r.inactivo ? '<span class="badge badge-danger">Inactivo</span>' : (r.recurrente ? '<span class="badge badge-success">Recurrente</span>' : '<span class="badge badge-info">Activo</span>') }
                ],
                paging: true, pageLength: 15, info: false, searching: true, order: [], ordering: false
            });

        } catch(e) { console.error('Clientes error', e); }
    }

    async function cargarProductos(params) {
        try {
            const r = await axios.get('/reporte/dashboard/top-productos?' + params + '&limite=30');
            const data = r.data;

            // Top 20 barra
            destroyChart('top-prod');
            const top20 = data.slice(0, 20);
            charts['top-prod'] = new ApexCharts(document.getElementById('chart-top-prod'), {
                series: [{ name: 'Ingresos', data: top20.map(d => d.ingresos) }],
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 3 } },
                xaxis: { categories: top20.map(d => d.producto.substring(0,28)), labels: { formatter: v => 'L.' + (v/1000).toFixed(0) + 'K' } },
                tooltip: { y: { formatter: v => fmtMoney(v) } },
                dataLabels: {
                    enabled: true,
                    formatter: v => 'L.' + (v/1000).toFixed(0) + 'K',
                    style: { fontSize: '9px' }
                },
                colors: ['#36b9cc']
            });
            charts['top-prod'].render();

            // Pareto (línea+barra combinada)
            destroyChart('pareto');
            const top30 = data.slice(0, 30);
            charts['pareto'] = new ApexCharts(document.getElementById('chart-pareto'), {
                series: [
                    { name: 'Ingresos', type: 'column', data: top30.map(d => d.ingresos) },
                    { name: 'Pareto %', type: 'line',   data: top30.map(d => d.pareto) }
                ],
                chart: { height: 320, toolbar: { show: false } },
                xaxis: { categories: top30.map(d => d.producto.substring(0,15)), labels: { rotate: -45, style: { fontSize: '9px' } } },
                yaxis: [
                    { title: { text: 'Ingresos' }, labels: { formatter: v => 'L.' + (v/1000).toFixed(0) + 'K' } },
                    { opposite: true, min: 0, max: 100, title: { text: 'Pareto %' }, labels: { formatter: v => v + '%' } }
                ],
                colors: ['#4e73df','#f6c23e'],
                tooltip: { shared: true, intersect: false },
                dataLabels: {
                    enabled: true,
                    formatter: (v, { seriesIndex }) => {
                        if (seriesIndex === 0) return v === 0 ? '' : 'L.' + (v/1000).toFixed(0) + 'K';
                        return v + '%';
                    },
                    style: { fontSize: '8px', colors: ['#fff', '#f6c23e'] },
                    background: { enabled: false }
                },
                markers: { size: [0, 3] },
                annotations: { yaxis: [{ y: 80, borderColor: '#e74a3b', label: { text: '80%', style: { color: '#fff', background: '#e74a3b' } }, yAxisIndex: 1 }] }
            });
            charts['pareto'].render();

            // Tabla (data API — evita cabeceras en blanco)
            lastProductos = data;
            if (dtProductos) { dtProductos.destroy(); dtProductos = null; }
            dtProductos = $('#tabla-productos').DataTable({
                data: data,
                columns: [
                    { title: '#',             render: (d,t,r,m) => m.row + 1 },
                    { title: 'Producto',       data: 'producto' },
                    { title: 'Categoría',     data: 'categoria' },
                    { title: 'Subcategoría',  data: 'subcategoria' },
                    { title: 'Unidades',       data: 'unidades_vendidas', className: 'text-right', render: v => fmtNum(v) },
                    { title: 'Ingresos',       data: 'ingresos',          className: 'text-right', render: v => fmtMoney(v) },
                    { title: 'Precio Prom.',   data: 'precio_promedio',   className: 'text-right', render: v => fmtMoney(v) },
                    { title: 'Facturas',       data: 'apariciones',       className: 'text-center' },
                    { title: 'Pareto %',       data: 'pareto',            className: 'text-center',
                      render: v => { const c = v<=80?'success':'secondary'; return `<span class="badge badge-${c}">${v}%</span>`; } }
                ],
                paging: true, pageLength: 15, info: false, searching: true, order: [], ordering: false
            });

        } catch(e) { console.error('Productos error', e); }
    }
    // ── EXCEL CON GRÁFICAS ───────────────────────────────────────────────
    function saveExcel(buffer, filename) {
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = filename;
        document.body.appendChild(a); a.click();
        setTimeout(() => { document.body.removeChild(a); URL.revokeObjectURL(url); }, 200);
    }

    async function captureChart(key) {
        if (!charts[key]) return null;
        try { const { imgURI } = await charts[key].dataURI(); return imgURI.split(',')[1]; }
        catch(e) { console.warn('Chart capture failed:', key, e); return null; }
    }

    function excelHeader(ws, text, cols) {
        ws.mergeCells(`A1:${String.fromCharCode(64 + cols)}1`);
        const c = ws.getCell('A1');
        c.value = text; c.font = { bold: true, size: 13, color: { argb: 'FF4E73DF' } };
        c.alignment = { horizontal: 'center' }; ws.getRow(1).height = 22;
    }

    function excelTableHeader(row, color) {
        row.eachCell(c => {
            c.font = { bold: true, color: { argb: 'FFFFFFFF' } };
            c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: color || 'FF4E73DF' } };
            c.alignment = { horizontal: 'center', vertical: 'middle' };
        });
    }

    async function _insertarGraficas(wb, ws, chartDefs, startRow) {
        let row = startRow;
        for (const { key, label, w, h } of chartDefs) {
            const b64 = await captureChart(key);
            if (!b64) continue;
            ws.getCell(`A${row}`).value = label;
            ws.getCell(`A${row}`).font = { bold: true };
            row++;
            const imgId = wb.addImage({ base64: b64, extension: 'png' });
            ws.addImage(imgId, { tl: { col: 0, row: row - 1 }, ext: { width: w, height: h } });
            const skip = Math.ceil(h / 18) + 3;
            for (let i = 0; i < skip; i++) ws.addRow([]);
            row += skip + 1;
        }
        return row;
    }

    async function _exportarTab1() {
        const wb = new ExcelJS.Workbook(); wb.creator = 'PROFAC';
        // Hoja Datos
        const ws1 = wb.addWorksheet('Datos');
        excelHeader(ws1, 'DASHBOARD DE VENTAS — HISTÓRICO & COMPARATIVO', 3);
        ws1.addRow([]);
        const hdr1 = ws1.addRow(['INDICADOR', 'VALOR']); excelTableHeader(hdr1);
        if (lastKpis) {
            [
                ['Total Vendido',    fmtMoney(lastKpis.total_ventas)],
                ['Facturas',         fmtNum(lastKpis.total_facturas)],
                ['Ticket Promedio',  fmtMoney(lastKpis.ticket_promedio)],
                ['Clientes Únicos',  fmtNum(lastKpis.clientes_unicos)],
                ['Total Descuentos', fmtMoney(lastKpis.total_descuentos)],
                ['Mejor Mes',        lastKpis.mejor_mes || '—'],
                ['Mejor Vendedor',   lastKpis.mejor_vendedor || '—'],
                ['Crecimiento %',    lastKpis.crecimiento !== null ? lastKpis.crecimiento + '%' : 'N/D'],
            ].forEach(r => ws1.addRow(r));
        }
        ws1.addRow([]);
        const hdr2 = ws1.addRow(['AÑO', 'MES', 'TOTAL VENTAS']); excelTableHeader(hdr2);
        lastMensual.forEach(row => {
            const r = ws1.addRow([String(row.anio), MESES[Number(row.mes)-1], parseFloat(row.total)]);
            r.getCell(3).numFmt = '#,##0.00';
        });
        ws1.columns = [{ width: 28 }, { width: 18 }, { width: 22 }];

        // Hoja Gráficas
        const ws2 = wb.addWorksheet('Gráficas');
        await _insertarGraficas(wb, ws2, [
            { key: 'evolucion',   label: 'Evolución Mensual por Año',    w: 680, h: 280 },
            { key: 'barras',      label: 'Barras Agrupadas por Mes/Año', w: 580, h: 260 },
            { key: 'crecimiento', label: '% Crecimiento Mensual',         w: 480, h: 280 },
            { key: 'heatmap',     label: 'Mapa de Calor (Año × Mes)',     w: 580, h: 260 },
        ], 1);

        saveExcel(await wb.xlsx.writeBuffer(), 'historico_ventas.xlsx');
    }

    async function _exportarTab2() {
        const wb = new ExcelJS.Workbook(); wb.creator = 'PROFAC';
        // Hoja Resumen
        const ws1 = wb.addWorksheet('Resumen');
        excelHeader(ws1, 'REPORTE SEMANAL DE VENTAS', 2);
        ws1.addRow([]);
        const hdr1 = ws1.addRow(['INDICADOR', 'VALOR']); excelTableHeader(hdr1);
        [
            ['Total Período',    document.getElementById('s-kpi-total').textContent],
            ['Facturas',           document.getElementById('s-kpi-facturas').textContent],
            ['Ticket Promedio',    document.getElementById('s-kpi-ticket').textContent],
            ['Mejor Día',          document.getElementById('s-kpi-mejor-dia').textContent],
            ['Mejor Vendedor',     document.getElementById('s-kpi-vend').textContent],
            ['Mejor Cliente',      document.getElementById('s-kpi-cliente').textContent],
        ].forEach(r => ws1.addRow(r));
        ws1.columns = [{ width: 25 }, { width: 38 }];

        // Hoja Detalle
        const ws2 = wb.addWorksheet('Detalle');
        const detHdr = ws2.addRow(['Fecha','Día','Semana','Documento','Cliente','Vendedor','Tipo','Subtotal','ISV','Descuento','Total']);
        excelTableHeader(detHdr);
        const fi = document.getElementById('s-fi').value, ff = document.getElementById('s-ff').value;
        const vend = document.getElementById('s-vendedor').value, tc = document.getElementById('s-tipo-cliente').value;
        const params = buildParams({ fecha_inicio: fi, fecha_final: ff, vendedor: vend, tipo_cliente: tc });
        try {
            const r = await axios.get('/reporte/dashboard/ventas-semanales?' + params + '&start=0&length=-1');
            (r.data.data || []).forEach(row => {
                ws2.addRow([row.fecha, row.dia_semana, row.semana_iso, row.documento||'', row.cliente, row.vendedor, row.tipo_cliente, row.subtotal, row.impuesto, row.descuento, row.total]);
            });
        } catch(e) { console.warn('Detalle export error', e); }
        ws2.columns = [8,12,8,18,32,22,15,14,10,12,14].map(w => ({ width: w }));

        // Hoja Gráficas
        const ws3 = wb.addWorksheet('Gráficas');
        await _insertarGraficas(wb, ws3, [
            { key: 'por-dia',              label: 'Ventas por Día de la Semana', w: 500, h: 250 },
            { key: 'chart-tipo-cliente-sem', label: 'Tipo de Cliente',            w: 450, h: 250 },
            { key: 'ranking-vend-sem',     label: 'Top Vendedores (período)',    w: 500, h: 250 },
        ], 1);

        saveExcel(await wb.xlsx.writeBuffer(), 'semanal_ventas.xlsx');
    }

    async function _exportarTab3() {
        const wb = new ExcelJS.Workbook(); wb.creator = 'PROFAC';

        // Hoja Vendedores
        const wsV = wb.addWorksheet('Vendedores');
        const vHdr = wsV.addRow(['#','Vendedor','Facturas','Clientes','Total Ventas','Ticket Promedio','Participación %']);
        excelTableHeader(vHdr);
        lastVendedores.forEach((v, i) => {
            const r = wsV.addRow([i+1, v.vendedor, Number(v.facturas), Number(v.clientes_atendidos), parseFloat(v.total_ventas), parseFloat(v.ticket_promedio), parseFloat(v.participacion)]);
            r.getCell(5).numFmt = '#,##0.00'; r.getCell(6).numFmt = '#,##0.00'; r.getCell(7).numFmt = '0.00';
        });
        wsV.columns = [4,30,10,10,20,20,14].map(w => ({ width: w }));
        await _insertarGraficas(wb, wsV, [
            { key: 'rank-vend',  label: 'Ranking Vendedores',       w: 600, h: 300 },
            { key: 'part-vend',  label: 'Participación de Mercado',  w: 500, h: 300 },
        ], lastVendedores.length + 4);

        // Hoja Clientes
        const wsC = wb.addWorksheet('Clientes');
        const cHdr = wsC.addRow(['#','Cliente','Tipo','ABC','Facturas','Total Comprado','Ticket Prom.','Última Compra','Días sin comprar','Estado']);
        excelTableHeader(cHdr, 'FF1CC88A');
        lastClientes.forEach((c, i) => {
            const r = wsC.addRow([i+1, c.cliente, c.tipo_cliente, c.clasificacion_abc, Number(c.facturas), parseFloat(c.total_comprado), parseFloat(c.ticket_promedio), c.ultima_compra, c.dias_sin_comprar, c.inactivo ? 'Inactivo' : (c.recurrente ? 'Recurrente' : 'Activo')]);
            r.getCell(6).numFmt = '#,##0.00'; r.getCell(7).numFmt = '#,##0.00';
        });
        wsC.columns = [4,35,15,6,10,20,18,14,18,12].map(w => ({ width: w }));
        await _insertarGraficas(wb, wsC, [
            { key: 'top-cli',  label: 'Top 15 Clientes por Facturación', w: 600, h: 300 },
            { key: 'abc-cli',  label: 'Clasificación ABC',               w: 500, h: 300 },
        ], lastClientes.length + 4);

        // Hoja Productos
        const wsP = wb.addWorksheet('Productos');
        const pHdr = wsP.addRow(['#','Producto','Categoría','Subcategoría','Unidades','Ingresos','Precio Prom.','Facturas','Pareto %']);
        excelTableHeader(pHdr, 'FF36B9CC');
        lastProductos.forEach((p, i) => {
            const r = wsP.addRow([i+1, p.producto, p.categoria, p.subcategoria, Number(p.unidades_vendidas), parseFloat(p.ingresos), parseFloat(p.precio_promedio), p.apariciones, parseFloat(p.pareto)]);
            r.getCell(6).numFmt = '#,##0.00'; r.getCell(7).numFmt = '#,##0.00';
        });
        wsP.columns = [4,36,20,20,12,18,15,10,10].map(w => ({ width: w }));
        await _insertarGraficas(wb, wsP, [
            { key: 'top-prod', label: 'Top 20 Productos por Ingresos', w: 600, h: 320 },
            { key: 'pareto',   label: 'Pareto 80/20',                  w: 600, h: 320 },
        ], lastProductos.length + 4);

        saveExcel(await wb.xlsx.writeBuffer(), 'analitica_ventas.xlsx');
    }

    async function exportarExcel(tab) {
        if (typeof ExcelJS === 'undefined') {
            alert('La librería Excel aún no ha cargado. Por favor espere unos segundos y vuelva a intentar.');
            return;
        }
        const btn = (window.event || {}).currentTarget || null;
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando…'; }
        try {
            if (tab === 'hist')     await _exportarTab1();
            else if (tab === 'sem') await _exportarTab2();
            else if (tab === 'adv') await _exportarTab3();
        } catch(e) {
            console.error('Error exportando Excel:', e);
            alert('Error al generar el archivo Excel. Revise la consola para más detalles.');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
        }
    }
    // ── RECARGA TODO ─────────────────────────────────────────────────────────
    function recargarTodo() {
        const tabActiva = document.querySelector('#dashTabs .nav-link.active').id;
        if (tabActiva === 'tab-hist') cargarHistorico();
        else if (tabActiva === 'tab-sem') cargarSemanal();
        else cargarAnalitica();
    }

    // ── INIT ─────────────────────────────────────────────────────────────────
    async function init() {
        const hoy = isoHoy();
        const iniAnio = new Date().getFullYear() + '-01-01';

        // Fechas por defecto P2: mes actual
        const iniMes = hoy.substring(0, 7) + '-01';
        document.getElementById('s-fi').value = iniMes;
        document.getElementById('s-ff').value = hoy;

        // Fechas por defecto P3: año actual
        document.getElementById('a-fi').value = iniAnio;
        document.getElementById('a-ff').value = hoy;

        await cargarCatalogos();
        await cargarHistorico();

        // Al activar tabs cargar su contenido si aún no se cargó
        document.getElementById('tab-sem').addEventListener('click', () => {
            if (!dtSemanal) cargarSemanal();
        });
        document.getElementById('tab-adv').addEventListener('click', () => {
            if (!dtVendedores) cargarAnalitica();
        });
    }

    // ── API PÚBLICA ──────────────────────────────────────────────────────────
    return { init, cargarHistorico, cargarSemanal, cargarAnalitica, recargarTodo, exportarExcel };

})();
