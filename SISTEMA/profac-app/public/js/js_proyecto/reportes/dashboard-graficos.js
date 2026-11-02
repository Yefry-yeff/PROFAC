function dashChart(elemento, opciones) {
    const region = elemento.closest('[data-dashboard-region]');
    const grafico = new ApexCharts(elemento, {
        noData: { text: 'Sin información para este período' },
        ...opciones,
    });
    region._dashboardCharts.push(grafico);
    return grafico;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('dashboardGraficos', (vista, oscuro, periodo) => ({
        error: '',
        init() {
            this.$el._dashboardCharts = [];
            this.$nextTick(() => {
                if (!this.$el.isConnected) return;
                window.DASH_DARK = oscuro;
                window.DASH_LABEL = periodo;
                if (typeof ApexCharts === 'undefined') {
                    this.error = 'No se pudieron cargar los gráficos. Recarga la página para reintentar.';
                    return;
                }
                const inicializar = {
                    'dashboard.gerencia': initGerenciaCharts,
                    'dashboard.vendedor': initVendedorCharts,
                    'dashboard.televendedor': initVendedorCharts,
                    'dashboard.cobros': initCobrosCharts,
                    'dashboard.aux-admin': initAuxAdmCharts,
                    'dashboard.aux-contable': initAuxCntCharts,
                    'dashboard.logistica': initLogisticaCharts,
                    'dashboard.rrhh': initRrhhCharts,
                    'dashboard.mercadeo': initMktCharts,
                    'dashboard.auditoria': initAuditoriaCharts,
                }[vista];
                if (inicializar) inicializar();
            });
        },
        destroy() {
            this.$el._dashboardCharts.forEach(grafico => grafico.destroy());
        },
    }));
});

function initGerenciaCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia     = JSON.parse(span.dataset.tendencia     || '{}');
    var productividad = JSON.parse(span.dataset.productividad || '[]');
    var participacion = JSON.parse(span.dataset.participacion || '{}');
    if (Array.isArray(tendencia))    tendencia = {};
    if (!Array.isArray(productividad)) productividad = [];
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // ── Tendencia 12 meses ──
    const elTend = document.querySelector('#chart-ger-tendencia');
    if (elTend) {
        elTend.innerHTML = '';
        dashChart(elTend, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'area', height:270 }),
            series: [
                { name:'Ventas (L.)', data: tendencia.montos || [] },
                { name:'Facturas',    data: tendencia.facturas || [] },
            ],
            xaxis:  { categories: tendencia.labels || [] },
            yaxis:  [
                { title: { text: 'L.' }, labels: { formatter: v => lpsK(v) } },
                { opposite: true, title: { text: 'Facturas' } },
            ],
            colors: ['#1ab394', '#f39c12'],
            stroke: { curve:'smooth', width:[2.5,2] },
            fill:   { type:['gradient','solid'], gradient:{ opacityFrom:.35, opacityTo:.05 }, opacity:[1,0] },
            dataLabels: { enabled: false },
        })).render();
    }

    // ── Productividad barras ──
    const elProd = document.querySelector('#chart-ger-productividad');
    if (elProd) {
        elProd.innerHTML = '';
        dashChart(elProd, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'bar', height:260 }),
            series: [{ name:'Ventas (L.)', data: productividad.map(p => p.venta_total) }],
            xaxis:  { categories: productividad.map(p => p.vendedor) },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#1ab394'],
            plotOptions: { bar: { borderRadius:6, horizontal:false } },
            dataLabels: { enabled: false },
        })).render();
    }

    // ── Participación pie ──
    const elPart = document.querySelector('#chart-ger-participacion');
    if (elPart) {
        elPart.innerHTML = '';
        dashChart(elPart, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'donut', height:270 }),
            series: participacion.series || [],
            labels: participacion.labels || [],
            colors: ['#1ab394','#f39c12','#e74c3c','#2980b9','#8e44ad','#27ae60','#e67e22','#c0392b'],
            legend: { position:'bottom' },
            tooltip: { y: { formatter: v => lps(v) } },
            dataLabels: { enabled: true, formatter: (v) => v.toFixed(1) + '%' },
        })).render();
    }

}

function initVendedorCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia = JSON.parse(span.dataset.tendencia || '{}');
    if (Array.isArray(tendencia)) tendencia = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // Tendencia líneas
    const elT = document.querySelector('#chart-tendencia-vendedor');
    if (elT) {
        elT.innerHTML = '';
        dashChart(elT, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'area', height:260 }),
            series: [{ name:'Ventas (L.)', data: tendencia.montos || [] }],
            xaxis:  { categories: tendencia.labels || [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#1ab394'],
            fill:   { type:'gradient', gradient:{ shadeIntensity:1, opacityFrom:.4, opacityTo:.05 } },
            stroke: { curve:'smooth', width:2.5 },
            dataLabels: { enabled: false },
        })).render();
    }
}

function initCobrosCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var kc   = JSON.parse(span.dataset.cobros || '{}');
    if (Array.isArray(kc)) kc = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // Abonos diarios (barras)
    const elD = document.querySelector('#chart-cobros-diarios');
    if (elD) {
        elD.innerHTML = '';
        dashChart(elD, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'bar', height:240 }),
            series: [{ name:'Abonado (L.)', data: (kc.cobros_diarios && kc.cobros_diarios.montos) ? kc.cobros_diarios.montos : [] }],
            xaxis:  { categories: (kc.cobros_diarios && kc.cobros_diarios.fechas) ? kc.cobros_diarios.fechas : [], labels:{ rotate:-45, style:{fontSize:'10px'} } },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            tooltip: { y: { formatter: v => lps(v) } },
            colors: ['#27ae60'],
            plotOptions: { bar: { borderRadius:3 } },
            dataLabels: { enabled: false },
        })).render();
    }

    // Tendencia 6 meses (area)
    const elT = document.querySelector('#chart-cobros-tendencia');
    if (elT) {
        elT.innerHTML = '';
        dashChart(elT, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'area', height:240 }),
            series: [{ name:'Abonos (L.)', data: (kc.tendencia_cobros && kc.tendencia_cobros.montos) ? kc.tendencia_cobros.montos : [] }],
            xaxis:  { categories: (kc.tendencia_cobros && kc.tendencia_cobros.labels) ? kc.tendencia_cobros.labels : [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            tooltip: { y: { formatter: v => lps(v) } },
            colors: ['#2980b9'],
            stroke: { curve:'smooth', width:2.5 },
            fill:   { type:'gradient', gradient:{ opacityFrom:.35, opacityTo:.05 } },
            dataLabels: { enabled: false },
        })).render();
    }

    // Antiguedad (donut)
    const elA = document.querySelector('#chart-cobros-antiguedad');
    if (elA && kc.antiguedad) {
        elA.innerHTML = '';
        dashChart(elA, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:260 }),
            series: [
                kc.antiguedad['al_dia']  || 0,
                kc.antiguedad['0_30']   || 0,
                kc.antiguedad['31_60']  || 0,
                kc.antiguedad['61_90']  || 0,
                kc.antiguedad['90_mas'] || 0,
            ],
            labels: ['Al dia','0-30 dias','31-60 dias','61-90 dias','> 90 dias'],
            colors: ['#27ae60','#2ecc71','#f39c12','#e67e22','#e74c3c'],
            legend: { position:'bottom' },
            tooltip: { y: { formatter: v => lps(v) } },
            dataLabels: { enabled: true, formatter: v => v.toFixed(1)+'%' },
            plotOptions: { pie: { donut: { size:'65%' } } },
        })).render();
    }
}

function initAuxAdmCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var cobros = JSON.parse(span.dataset.cobros || '{}');
    if (Array.isArray(cobros)) cobros = {};
    var dark   = window.DASH_DARK;
    var base   = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // Tendencia cobros
    const elT = document.querySelector('#chart-auxadm-tendencia-cobros');
    if (elT && cobros.tendencia_cobros) {
        elT.innerHTML = '';
        dashChart(elT, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'area', height:260 }),
            series: [{ name:'Cobrado (L.)', data: cobros.tendencia_cobros.montos || [] }],
            xaxis:  { categories: cobros.tendencia_cobros.labels || [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#1ab394'],
            stroke: { curve:'smooth', width:2.5 },
            fill:   { type:'gradient', gradient:{ opacityFrom:.35, opacityTo:.05 } },
            dataLabels: { enabled: false },
        })).render();
    }

    // Antigüedad donut
    const elA = document.querySelector('#chart-auxadm-antiguedad');
    if (elA && cobros.antiguedad) {
        elA.innerHTML = '';
        const ag = cobros.antiguedad;
        dashChart(elA, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:260 }),
            series: [ag.al_dia||0, ag['0_30']||0, ag['31_60']||0, ag['61_90']||0, ag['90_mas']||0],
            labels: ['Al día','0-30 días','31-60 días','61-90 días','+90 días'],
            colors: ['#1ab394','#f39c12','#e67e22','#e74c3c','#8e44ad'],
            legend: { position:'bottom' },
            tooltip: { y: { formatter: v => lps(v) } },
            dataLabels: { enabled: true, formatter: v => v.toFixed(1)+'%' },
        })).render();
    }
}

function initAuxCntCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia   = JSON.parse(span.dataset.tendencia || '{}');
    var kpisCobros  = JSON.parse(span.dataset.cobros    || '{}');
    if (Array.isArray(tendencia))  tendencia = {};
    if (Array.isArray(kpisCobros)) kpisCobros = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const elT = document.querySelector('#chart-auxcnt-tendencia');
    if (elT) {
        elT.innerHTML = '';
        dashChart(elT, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'area', height:240 }),
            series: [{ name:'Ventas (L.)', data: tendencia.montos || [] }],
            xaxis:  { categories: tendencia.labels || [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#1ab394'],
            stroke: { curve:'smooth', width:2.5 },
            fill:   { type:'gradient', gradient:{ opacityFrom:.35, opacityTo:.05 } },
            dataLabels: { enabled: false },
        })).render();
    }

    const elA = document.querySelector('#chart-auxcnt-antiguedad');
    if (elA && kpisCobros.antiguedad) {
        elA.innerHTML = '';
        dashChart(elA, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:240 }),
            series: [
                kpisCobros.antiguedad['0_30']   || 0,
                kpisCobros.antiguedad['31_60']  || 0,
                kpisCobros.antiguedad['61_90']  || 0,
                kpisCobros.antiguedad['90_mas'] || 0,
            ],
            labels: ['0-30 días','31-60 días','61-90 días','> 90 días'],
            colors: ['#27ae60','#f39c12','#e67e22','#e74c3c'],
            legend: { position:'bottom' },
        })).render();
    }
}

function initLogisticaCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia = JSON.parse(span.dataset.tendencia || '{}');
    if (Array.isArray(tendencia)) tendencia = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const el = document.querySelector('#chart-log-tendencia');
    if (el) {
        el.innerHTML = '';
        dashChart(el, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'bar', height:260 }),
            series: [
                { name:'Facturas', data: tendencia.facturas || [] },
                { name:'Clientes', data: tendencia.clientes || [] },
            ],
            xaxis:  { categories: tendencia.labels || [] },
            colors: ['#1ab394','#2980b9'],
            plotOptions: { bar: { borderRadius:4, columnWidth:'60%' } },
            dataLabels: { enabled: false },
        })).render();
    }
}

function initRrhhCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var productividad = JSON.parse(span.dataset.productividad || '[]');
    if (!Array.isArray(productividad)) productividad = [];
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const el = document.querySelector('#chart-rrhh-productividad');
    if (el) {
        el.innerHTML = '';
        dashChart(el, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'bar', height:280 }),
            series: [
                { name:'Ventas (L.)',  data: productividad.map(p => p.venta_total) },
                { name:'Facturas',     data: productividad.map(p => p.num_facturas) },
                { name:'Clientes',     data: productividad.map(p => p.clientes_atendidos) },
            ],
            xaxis:  { categories: productividad.map(p => p.vendedor) },
            yaxis:  [
                { title:{ text:'L.' }, labels:{ formatter: v => lpsK(v) } },
                { opposite:true, title:{ text:'Cant.' } },
                { show: false },
            ],
            colors: ['#1ab394','#f39c12','#2980b9'],
            plotOptions: { bar: { borderRadius:4, columnWidth:'70%' } },
            dataLabels: { enabled: false },
        })).render();
    }
}

function initMktCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var inv  = JSON.parse(span.dataset.inventario || '{}');
    if (Array.isArray(inv)) inv = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const elS = document.querySelector('#chart-mkt-stock');
    if (elS) {
        elS.innerHTML = '';
        const nombres = (inv.top_bajos_stock || []).map(p => p.nombre.length > 25 ? p.nombre.substring(0,25)+'\u2026' : p.nombre);
        const stocks  = (inv.top_bajos_stock || []).map(p => p.stock);
        dashChart(elS, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'bar', height:270 }),
            series: [{ name: 'Unidades', data: stocks }],
            xaxis:  { categories: nombres, labels: { style: { fontSize: '10px' } } },
            yaxis:  { title: { text: 'Unidades' } },
            colors: ['#e74c3c'],
            plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
            dataLabels: { enabled: true, style: { fontSize: '10px' } },
        })).render();
    }

    const elI = document.querySelector('#chart-mkt-imagenes');
    if (elI) {
        elI.innerHTML = '';
        const total  = inv.total_productos || 0;
        const sinImg = inv.sin_imagen      || 0;
        const conImg = Math.max(0, total - sinImg);
        dashChart(elI, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:270 }),
            series: [conImg, sinImg],
            labels: ['Con imagen', 'Sin imagen'],
            colors: ['#1ab394', '#f39c12'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true, formatter: v => v.toFixed(1) + '%' },
            tooltip: { y: { formatter: v => v + ' productos' } },
        })).render();
    }
}

function initAuditoriaCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var cobros = JSON.parse(span.dataset.cobros    || '{}');
    if (Array.isArray(cobros)) cobros = {};
    var dark   = window.DASH_DARK;
    var base   = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // Antigüedad donut
    const elA = document.querySelector('#chart-aud-antiguedad');
    if (elA && cobros.antiguedad) {
        elA.innerHTML = '';
        const ag = cobros.antiguedad;
        dashChart(elA, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:260 }),
            series: [ag.al_dia||0, ag['0_30']||0, ag['31_60']||0, ag['61_90']||0, ag['90_mas']||0],
            labels: ['Al día','0-30 días','31-60 días','61-90 días','+90 días'],
            colors: ['#1ab394','#f39c12','#e67e22','#e74c3c','#8e44ad'],
            legend: { position:'bottom' },
            tooltip: { y: { formatter: v => lps(v) } },
            dataLabels: { enabled: true, formatter: v => v.toFixed(1)+'%' },
        })).render();
    }
}