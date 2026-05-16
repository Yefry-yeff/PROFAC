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
function initTablaHistorico() {
    tblHistorico = $('#tbl_comisiones_empleado').DataTable({
        destroy: true,
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        dom: '<"html5buttons"B>lftp',
        buttons: [{
            extend: 'excel',
            title: 'mis_comisiones',
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
            { data: 'mes_letra' },
            { data: 'anio' },
            { data: 'rol', defaultContent: '---' },
            {
                data: 'comision_acumulada',
                render: function (v) {
                    return '<strong style="color:#059669;">L ' +
                        parseFloat(v).toLocaleString('es-HN', { minimumFractionDigits: 2 }) +
                        '</strong>';
                }
            },
            { data: 'cantidad_facturas', className: 'text-center' },
            {
                data: 'fecha_ult_modificacion',
                render: function (v) { return v ? v.substring(0, 16) : '---'; }
            },
            { data: 'badge_mes', orderable: false }
        ],
        createdRow: function (row, data) {
            $(row).css({ cursor: 'pointer' });
            $(row).on('click', function () {
                var periodo = data.mes_comision ? data.mes_comision.substring(0, 7) : null;
                if (periodo) abrirDetalleMes(periodo, data.mes_letra + ' ' + data.anio, data.rol, data.rol_id || 0);
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
function abrirDetalleMes(periodo, label, rol, rolId) {
    $('#mcModalTitle').html('<i class="fa fa-receipt mr-2"></i>Facturas de ' + label +
        (rol ? ' <small style="opacity:.7;">(' + rol + ')</small>' : ''));
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
            data: { periodo: periodo, rol_id: rolId || 0 },
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
