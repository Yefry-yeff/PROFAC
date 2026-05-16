/* ====================== DASHBOARD COMISIONES EMPLEADO ====================== */

let mcChart      = null;
let tblHistorico = null;
let tblDetalle   = null;

/* --------------------------------------------------------------------------
 * INIT
 * -------------------------------------------------------------------------- */
$(document).ready(function () {
    initChartHistorico();
    initTablaHistorico();
    cargarTopProductos('todo');
});

/* --------------------------------------------------------------------------
 * CHART.JS - Historico mensual
 * -------------------------------------------------------------------------- */
function initChartHistorico() {
    $.get('/comision/empleado/chart-mensual', function (res) {
        var items = res.data || [];
        if (!items.length) {
            $('#chart-periodo-label').text('Sin datos');
            return;
        }

        var labels = items.map(function (i) { return i.etiqueta; });
        var values = items.map(function (i) { return parseFloat(i.comision_acumulada); });

        $('#chart-periodo-label').text('Ultimos ' + items.length + ' meses');

        var ctx = document.getElementById('mcChartHistorico').getContext('2d');
        var grad = ctx.createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, 'rgba(16,185,129,.35)');
        grad.addColorStop(1, 'rgba(16,185,129,.02)');

        if (mcChart) mcChart.destroy();

        mcChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Comision (L)',
                    data: values,
                    borderColor: '#10b981',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return 'L ' + ctx.parsed.y.toLocaleString('es-HN', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 11 },
                            callback: function (v) {
                                return 'L ' + v.toLocaleString('es-HN', { maximumFractionDigits: 0 });
                            }
                        }
                    }
                }
            }
        });
    }).fail(function () {
        $('#chart-periodo-label').text('Error al cargar');
    });
}

/* --------------------------------------------------------------------------
 * DATATABLE - Historial mensual
 * -------------------------------------------------------------------------- */

/* Devuelve colores según nombre del rol */
function rolBadge(nombre) {
    var n = (nombre || '').toLowerCase();
    if (n.indexOf('admin')       !== -1) return { bg: '#dbeafe', text: '#1d4ed8', icon: 'fa-user-shield' };
    if (n.indexOf('gerente')     !== -1) return { bg: '#ede9fe', text: '#6d28d9', icon: 'fa-crown' };
    if (n.indexOf('facturador')  !== -1) return { bg: '#fef3c7', text: '#b45309', icon: 'fa-file-invoice' };
    if (n.indexOf('televendedor') !== -1) return { bg: '#d1fae5', text: '#065f46', icon: 'fa-headset' };
    if (n.indexOf('vendedor')    !== -1) return { bg: '#dcfce7', text: '#15803d', icon: 'fa-user-tie' };
    return { bg: '#f1f5f9', text: '#475569', icon: 'fa-user-tag' };
}

function initTablaHistorico() {
    tblHistorico = $('#tbl_comisiones_empleado').DataTable({
        destroy: true,
        order: [[0, 'desc']],
        pageLength: 15,
        responsive: true,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        dom: '<"html5buttons"B>ltp',
        buttons: [{
            extend: 'excel',
            title: 'mis_comisiones',
            text: '<i class="fa fa-file-excel mr-1"></i>Exportar',
            className: 'btn btn-sm btn-outline-secondary'
        }],
        ajax: {
            url: '/listar/empleado/comision',
            dataSrc: 'data',
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la tabla.' });
            }
        },
        columns: [
            /* 0 — oculto, para ordenar por fecha real */
            { data: 'mes_comision', visible: false, searchable: false },
            /* 1 — Rol */
            {
                data: 'rol',
                defaultContent: '---',
                render: function (v) {
                    var c = rolBadge(v);
                    return '<span style="display:inline-flex;align-items:center;gap:5px;' +
                        'background:' + c.bg + ';color:' + c.text + ';' +
                        'padding:4px 11px;border-radius:20px;font-size:.76rem;font-weight:700;white-space:nowrap;">' +
                        '<i class="fa ' + c.icon + '" style="font-size:.7rem;"></i>' + (v || '---') +
                        '</span>';
                }
            },
            /* 2 — Comisión */
            {
                data: 'comision_acumulada',
                className: 'text-right',
                render: function (v) {
                    var n = parseFloat(v || 0);
                    var color = n > 0 ? '#059669' : '#94a3b8';
                    return '<span style="font-size:1rem;font-weight:800;color:' + color + ';letter-spacing:-.3px;">' +
                        'L&nbsp;' + n.toLocaleString('es-HN', { minimumFractionDigits: 2 }) +
                        '</span>';
                }
            },
            /* 3 — Facturas */
            {
                data: 'cantidad_facturas',
                className: 'text-center',
                render: function (v) {
                    return '<span style="display:inline-block;min-width:28px;background:#dbeafe;' +
                        'color:#1d4ed8;padding:2px 8px;border-radius:12px;font-size:.8rem;font-weight:700;">' +
                        (v || 0) + '</span>';
                }
            },
            /* 4 — Última actualización */
            {
                data: 'fecha_ult_modificacion',
                render: function (v) {
                    if (!v) return '<span style="color:#94a3b8;">---</span>';
                    var parts = v.substring(0, 16).split(' ');
                    return '<span style="font-size:.8rem;color:#374151;">' +
                        '<i class="fa fa-clock mr-1" style="color:#94a3b8;font-size:.7rem;"></i>' +
                        parts[0] + ' <span style="color:#94a3b8;">' + (parts[1] || '') + '</span>' +
                        '</span>';
                }
            },
            /* 5 — Estado */
            {
                data: 'es_mes_actual',
                orderable: false,
                className: 'text-center',
                render: function (v, type, row) {
                    if (parseInt(v) === 1) {
                        return '<span style="background:linear-gradient(90deg,#10b981,#059669);color:#fff;' +
                            'padding:3px 10px;border-radius:12px;font-size:.72rem;font-weight:700;' +
                            'display:inline-flex;align-items:center;gap:4px;white-space:nowrap;">' +
                            '<i class="fa fa-star" style="font-size:.62rem;"></i>MES ACTUAL</span>';
                    }
                    return '<span style="background:#f1f5f9;color:#64748b;' +
                        'padding:3px 10px;border-radius:12px;font-size:.72rem;font-weight:600;">Cerrado</span>';
                }
            }
        ],
        drawCallback: function () {
            var api   = this.api();
            var rows  = api.rows({ page: 'current' }).nodes();
            var last  = null;
            var loop  = 0;

            /* Pre-calcular totales por período en la página actual */
            var totalesPeriodo = {};
            api.rows({ page: 'current' }).data().each(function (d) {
                var key = d.mes_comision ? d.mes_comision.substring(0, 7) : '';
                totalesPeriodo[key] = (totalesPeriodo[key] || 0) + parseFloat(d.comision_acumulada || 0);
            });

            api.rows({ page: 'current' }).every(function () {
                var d   = this.data();
                var key = d.mes_comision ? d.mes_comision.substring(0, 7) : '';
                var lbl = d.mes_letra + ' ' + d.anio;

                if (last !== key) {
                    var total = (totalesPeriodo[key] || 0)
                        .toLocaleString('es-HN', { minimumFractionDigits: 2 });

                    $(rows).eq(loop).before(
                        '<tr class="mc-group-row"><td colspan="6">' +
                        '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<span style="display:flex;align-items:center;gap:8px;">' +
                        '<span style="display:inline-flex;align-items:center;justify-content:center;' +
                        'width:28px;height:28px;background:#1e3a5f;border-radius:8px;">' +
                        '<i class="fa fa-calendar-alt" style="color:#7dd3fc;font-size:.75rem;"></i></span>' +
                        '<strong style="font-size:.88rem;color:#0f172a;">' + lbl + '</strong>' +
                        '</span>' +
                        '<span style="font-size:.85rem;font-weight:800;color:#059669;">' +
                        '<i class="fa fa-sigma mr-1" style="font-size:.7rem;opacity:.7;"></i>' +
                        'Total del mes:&nbsp;L&nbsp;' + total +
                        '</span>' +
                        '</div></td></tr>'
                    );
                    last = key;
                }
                loop++;
            });
        },
        createdRow: function (row, data) {
            $(row).css({ cursor: 'pointer' });
            $(row).on('click', function () {
                var periodo = data.mes_comision ? data.mes_comision.substring(0, 7) : null;
                if (periodo) abrirDetalleMes(periodo, data.mes_letra + ' ' + data.anio);
            });
        }
    });
}

/* --------------------------------------------------------------------------
 * TOP PRODUCTOS
 * -------------------------------------------------------------------------- */
function topPeriodo(el, periodo) {
    $('.mc-period-pill').removeClass('active');
    $(el).addClass('active');
    cargarTopProductos(periodo);
}

function cargarTopProductos(periodo) {
    var $body = $('#top-productos-body');
    $body.html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin text-muted"></i></div>');

    $.get('/comision/empleado/top-productos', { periodo: periodo }, function (res) {
        var items = res.data || [];

        if (!items.length) {
            $body.html('<div class="text-center py-4 text-muted"><i class="fa fa-box-open fa-2x mb-2 d-block"></i>Sin datos para este periodo</div>');
            return;
        }

        var maxMonto = Math.max.apply(null, items.map(function (i) { return parseFloat(i.monto_total); }));
        var html = '';

        items.forEach(function (item, idx) {
            var pct   = maxMonto > 0 ? ((parseFloat(item.monto_total) / maxMonto) * 100).toFixed(1) : 0;
            var monto = parseFloat(item.monto_total).toLocaleString('es-HN', { minimumFractionDigits: 2 });

            html += '<div style="margin-bottom:14px;">' +
                '<div class="d-flex justify-content-between align-items-center mb-1">' +
                    '<span style="font-size:.82rem;font-weight:600;color:#0f172a;max-width:60%;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" title="' + item.producto + '">' +
                        '<span style="display:inline-block;width:20px;height:20px;background:#0f172a;color:#fff;border-radius:50%;font-size:.65rem;font-weight:800;text-align:center;line-height:20px;margin-right:6px;">' + (idx + 1) + '</span>' +
                        item.producto +
                    '</span>' +
                    '<span style="font-size:.8rem;font-weight:700;color:#059669;">L ' + monto + '</span>' +
                '</div>' +
                '<div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;margin-bottom:4px;">' +
                    '<span><i class="fa fa-cube mr-1"></i>' + item.unidades + ' unidades</span>' +
                    '<span><i class="fa fa-receipt mr-1"></i>' + item.en_facturas + ' facturas</span>' +
                '</div>' +
                '<div class="mc-prog-bar"><div class="mc-prog-fill" style="width:' + pct + '%;"></div></div>' +
                '</div>';
        });

        $body.html(html);
    }).fail(function () {
        $body.html('<div class="text-center py-3 text-danger"><i class="fa fa-exclamation-circle mr-1"></i>Error al cargar</div>');
    });
}

/* --------------------------------------------------------------------------
 * MODAL DETALLE MES
 * -------------------------------------------------------------------------- */
function abrirDetalleMes(periodo, label) {
    $('#mcModalTitle').html('<i class="fa fa-receipt mr-2"></i>Facturas de ' + label);
    $('#mcModalDetalle').addClass('show');

    if (tblDetalle) { tblDetalle.destroy(); tblDetalle = null; }

    tblDetalle = $('#tbl_detalle_mes').DataTable({
        destroy: true,
        order: [[1, 'desc']],
        pageLength: 10,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        dom: 'lftp',
        ajax: {
            url: '/comision/empleado/detalle-mes',
            data: { periodo: periodo },
            dataSrc: 'data',
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el detalle.' });
            }
        },
        columns: [
            { data: 'factura_id', className: 'text-center font-weight-bold' },
            { data: 'fecha_cierre_factura' },
            { data: 'cliente', defaultContent: '---' },
            { data: 'rol', defaultContent: '---' },
            {
                data: 'monto_rol',
                render: function (v) {
                    return '<strong style="color:#059669;">L ' +
                        parseFloat(v).toLocaleString('es-HN', { minimumFractionDigits: 2 }) +
                        '</strong>';
                }
            },
            { data: 'productos', className: 'text-center' },
            { data: 'unidades', className: 'text-center' }
        ]
    });
}

function cerrarModal() {
    $('#mcModalDetalle').removeClass('show');
    if (tblDetalle) { tblDetalle.destroy(); tblDetalle = null; }
}

// Cerrar modal al click en backdrop
$(document).on('click', '#mcModalDetalle', function (e) {
    if (e.target === this) cerrarModal();
});