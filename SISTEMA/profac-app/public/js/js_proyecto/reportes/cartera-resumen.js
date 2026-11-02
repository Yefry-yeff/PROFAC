document.addEventListener('alpine:init', () => {
    Alpine.data('carteraResumen', (datos, oscuro) => {
        const graficos = [];
        return {
            error: '',
            init() {
                this.$nextTick(() => this.dibujar());
            },
            dibujar() {
                if (typeof ApexCharts === 'undefined') {
                    this.error = 'No se pudieron cargar los gráficos. Recarga la página para reintentar.';
                    return;
                }
                const base = dashBaseOpts(oscuro);
                const tendencia = datos.tendencia_cobros || { labels: [], montos: [] };
                const totalMeses = tendencia.montos.reduce((suma, monto) => suma + Number(monto), 0);
                const porcentaje = (monto, total) => (total > 0 ? monto / total * 100 : 0).toFixed(2) + '%';
                const mensual = new ApexCharts(this.$refs.mensual, {
                    ...base,
                    chart: { ...base.chart, type: 'bar', height: 275, toolbar: { show: false } },
                    series: [{ name: 'Abonos (L.)', data: tendencia.montos }],
                    xaxis: { categories: tendencia.labels },
                    yaxis: { labels: { formatter: lpsK } },
                    colors: ['#f39c12'],
                    plotOptions: { bar: { borderRadius: 5, columnWidth: '42%' } },
                    tooltip: { y: { formatter: monto => lps(monto) + ' · ' + porcentaje(monto, totalMeses) + ' del total de los 6 meses' } },
                    noData: { text: 'Sin abonos registrados' },
                    dataLabels: { enabled: false },
                });
                graficos.push(mensual);
                mensual.render();

                if (!this.$refs.antiguedad) return;
                const saldos = ['al_dia', '0_30', '31_60', '61_90', '90_mas']
                    .map(clave => Number(datos.antiguedad?.[clave] || 0));
                const saldo = Number(datos.saldo_pendiente || 0);
                saldos.push(Math.max(0, Math.round((saldo - saldos.reduce((suma, monto) => suma + monto, 0)) * 100) / 100));
                const antiguedad = new ApexCharts(this.$refs.antiguedad, {
                    ...base,
                    chart: { ...base.chart, type: 'donut', height: 230, toolbar: { show: false } },
                    series: saldos,
                    labels: ['Al día', '1–30 días', '31–60 días', '61–90 días', 'Más de 90 días', 'Sin clasificar'],
                    colors: ['#27ae60', '#f1c40f', '#f39c12', '#e67e22', '#e74c3c', '#94a3b8'],
                    legend: { show: false },
                    stroke: { width: 3, colors: [oscuro ? '#242830' : '#ffffff'] },
                    plotOptions: { pie: { donut: { size: '76%', labels: {
                        show: true,
                        value: { formatter: valor => lpsK(Number(valor)) },
                        total: { show: true, showAlways: true, label: 'Saldo actual', formatter: () => lpsK(saldo) },
                    } } } },
                    tooltip: { y: { formatter: monto => lps(monto) + ' · ' + porcentaje(monto, saldo) + ' del saldo actual' } },
                    dataLabels: { enabled: false },
                });
                graficos.push(antiguedad);
                antiguedad.render();
            },
            destroy() {
                graficos.forEach(grafico => grafico.destroy());
            },
        };
    });
});
