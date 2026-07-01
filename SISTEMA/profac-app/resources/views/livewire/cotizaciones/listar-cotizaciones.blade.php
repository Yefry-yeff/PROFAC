<div>
    @push('styles')
    <style>
    :root {
        --pf-grad:   linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --pf-orange: #e67e22;
        --pf-radius: 8px;
        --pf-shadow: 0 2px 8px rgba(0,0,0,.10);
    }
    .cot-card {
        border: 1px solid #e8d5bf;
        border-radius: var(--pf-radius);
        box-shadow: var(--pf-shadow);
        background: #fff;
    }
    .cot-card-header {
        background: var(--pf-grad);
        padding: 12px 20px;
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
        display: flex; align-items: center;
        justify-content: space-between;
        gap: 8px; flex-wrap: wrap;
    }
    .cot-card-header h5 {
        margin: 0; color: #fff;
        font-size: .85rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase;
        display: flex; align-items: center; gap: 8px;
    }
    .cot-card-body { padding: 12px 16px; }
    .tipo-filter-btn {
        font-size: .80rem; font-weight: 600;
        padding: 5px 18px; border-radius: 20px !important;
        border: 1.5px solid rgba(255,255,255,.5);
        background: rgba(255,255,255,.15); color: #fff;
        transition: all .15s; cursor: pointer; outline: none;
    }
    .tipo-filter-btn.active {
        background: #fff !important;
        color: #c0622a !important;
        border-color: transparent !important;
    }
    .tipo-filter-btn:hover:not(.active) { background: rgba(255,255,255,.30); }
    #tbl_listar_cotizaciones { width: 100% !important; }
    #tbl_listar_cotizaciones thead th {
        background: #fdf4e7; color: #7d3f00;
        font-size: .70rem; font-weight: 700;
        letter-spacing: .04em; text-transform: uppercase;
        border-bottom: 2px solid #f2d49a;
        white-space: nowrap; padding: 7px 8px; vertical-align: middle;
    }
    #tbl_listar_cotizaciones tbody td {
        font-size: .80rem; vertical-align: middle; padding: 6px 8px;
    }
    #tbl_listar_cotizaciones tbody tr:hover { background: #fffcf5; }
    /* -- Barra de filtros activos -- */
    .filtros-bar {
        padding: 8px 16px;
        background: #fdfaf5;
        border-bottom: 1px solid #e8d5bf;
        display: flex; flex-wrap: wrap;
        align-items: center; gap: 6px;
        font-size: .78rem;
    }
    .filtro-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #fff8ee; border: 1px solid #f2d49a;
        border-radius: 12px; padding: 2px 10px;
        font-size: .75rem; color: #7d3f00;
    }
    .filtro-badge .filtro-remove { cursor: pointer; color: #c0622a; font-weight: 700; margin-left: 3px; }
    .filtro-badge .filtro-remove:hover { color: #e74c3c; }
    .btn-fact-filter {
        background: rgba(255,255,255,.18) !important;
        color: #fff !important;
        border: 1.5px solid rgba(255,255,255,.5) !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        font-size: .78rem; padding: 5px 14px;
        transition: background .18s; white-space: nowrap; cursor: pointer;
    }
    .btn-fact-filter:hover { background: rgba(255,255,255,.30) !important; }
    .modal-header-fact {
        background: var(--pf-grad);
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
        padding: 12px 20px;
    }
    .modal-header-fact .modal-title { color: #fff; font-size: .85rem; font-weight: 700; letter-spacing:.05em; text-transform:uppercase; }
    .modal-header-fact .close { color: #fff; opacity: .8; text-shadow: none; font-size: 1.3rem; }
    .modal-header-fact .close:hover { opacity: 1; }
    .modal-section-label {
        font-size: .70rem; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
        color: #6c757d; border-bottom: 1px solid #e9ecef;
        padding-bottom: 4px; margin-bottom: 12px; margin-top: 4px;
    }
    .modal-content .form-control:focus {
        border-color: #e67e22;
        box-shadow: 0 0 0 .18rem rgba(230,126,34,.2);
    }
    .select2-container--open { z-index: 99999 !important; }
    </style>
    @endpush

    {{-- Loading Overlay --}}
    <div id="tbl_loading_overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%; display:none;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
        <p class="mt-3" style="color:#555; font-size:1rem;">Cargando datos...</p>
    </div>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Cotizaciones</h2>
            <ol class="breadcrumb">

                @switch(  $idTipoVenta )
                @case(1)
                    <li class="breadcrumb-item active">
                        <a>Coorporativo</a>
                    </li>
                    @break
                @case(2)
                    <li class="breadcrumb-item active">
                        <a>Gobierno</a>
                    </li>
                    @break
                @case(3)
                    <li class="breadcrumb-item active">
                        <a>Exonerado</a>
                    </li>
                    @break
                @endswitch



                <li class="breadcrumb-item">
                    <a>Imprimir Cotización</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Imprimir Factura</a>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="cot-card">
                    <div class="cot-card-header">
                        <h5><i class="fa fa-file-text"></i> Listado de Cotizaciones</h5>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <button type="button"
                                    class="tipo-filter-btn {{ $idTipoVenta == 2 ? 'active' : '' }}"
                                    data-idtipo="2" onclick="cambiarTipoCotizacion(2, this)">
                                    <i class="fa fa-circle" style="font-size:.6rem;vertical-align:middle;"></i> Clientes A
                                </button>
                                <button type="button"
                                    class="tipo-filter-btn {{ $idTipoVenta == 1 ? 'active' : '' }}"
                                    data-idtipo="1" onclick="cambiarTipoCotizacion(1, this)">
                                    <i class="fa fa-circle" style="font-size:.6rem;vertical-align:middle;"></i> Clientes B
                                </button>
                                <button type="button"
                                    class="tipo-filter-btn {{ $idTipoVenta == 3 ? 'active' : '' }}"
                                    data-idtipo="3" onclick="cambiarTipoCotizacion(3, this)">
                                    <i class="fa fa-circle" style="font-size:.6rem;vertical-align:middle;"></i> Exoneradas
                                </button>
                            </div>
                            <button class="btn-fact-filter" data-toggle="modal" data-target="#modalFiltrosCot">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>
                    {{-- Barra de filtros activos --}}
                    <div class="filtros-bar" id="filtrosBarCot" style="display:none;"></div>

                    {{-- Placeholder --}}
                    <div id="cot-placeholder" class="text-center py-5" style="color:#aaa">
                        <i class="fa fa-filter" style="font-size:2.5rem; color:#e67e22; opacity:.45"></i>
                        <p class="mt-3 mb-0" style="font-size:1rem; font-weight:600">Aplique filtros para cargar los resultados</p>
                        <p class="small">Haga clic en <strong>Filtros</strong> para definir los criterios de b&uacute;squeda.</p>
                    </div>

                    <div class="cot-card-body" id="cot-table-wrapper" style="display:none;">
                        <div class="table-responsive">
                            <table id="tbl_listar_cotizaciones" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Vendedor</th>
                                        <th>Cotizador</th>
                                        <th>Fecha de Registro</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Filtros Cotizaciones -->
    <div class="modal fade" id="modalFiltrosCot" tabindex="-1" role="dialog" aria-labelledby="tituloFiltrosCot" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-fact">
                    <h5 class="modal-title" id="tituloFiltrosCot">
                        <i class="fa fa-filter mr-2"></i>Filtros de B&uacute;squeda
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2">
                    <p class="modal-section-label"><i class="fa fa-tag mr-1"></i>Tipo de cotizaci&oacute;n</p>
                    <div class="mb-3 d-flex flex-wrap" style="gap:8px">
                        <button type="button" class="tipo-filter-btn {{ $idTipoVenta == 2 ? 'active' : '' }}" data-idtipo="2">
                            <i class="fa fa-circle mr-1" style="font-size:.65rem"></i>Clientes A
                        </button>
                        <button type="button" class="tipo-filter-btn {{ $idTipoVenta == 1 ? 'active' : '' }}" data-idtipo="1">
                            <i class="fa fa-circle mr-1" style="font-size:.65rem"></i>Clientes B
                        </button>
                        <button type="button" class="tipo-filter-btn {{ $idTipoVenta == 3 ? 'active' : '' }}" data-idtipo="3">
                            <i class="fa fa-circle mr-1" style="font-size:.65rem"></i>Exoneradas
                        </button>
                    </div>
                    <p class="modal-section-label"><i class="fa fa-search mr-1"></i>Criterios de b&uacute;squeda</p>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">Cliente</label>
                                <select id="cotFiltroCliente" class="form-control" style="width:100%">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">Vendedor</label>
                                <select id="cotFiltroVendedor" class="form-control" style="width:100%">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="limpiarFiltrosCot()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="aplicarFiltrosCot()">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ── Configuración ────────────────────────────────────────────────
            var usuarioDescargaExcel = @json(optional(Auth::user())->name ?? 'Sistema');
            var nombresTipoCotiz = { 1: 'Coorporativo', 2: 'Gobierno', 3: 'Exonerado' };
            var urlHistoryCotiz  = { 1: '/cotizacion/listado/corporativo', 2: '/cotizacion/listado/estatal', 3: '/cotizacion/listado/exonerado' };
            var cotFiltros = {
                idTipo:   {{ $idTipoVenta }},
                cliente:  '',
                vendedor: ''
            };

            function fechaHoraDescargaExcel() {
                var now = new Date();
                return now.toLocaleDateString('es-HN', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                    ' ' + now.toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }

            function normalizarNumeroExcel(valor) {
                if (valor === null || valor === undefined) return 0;
                var limpio = String(valor)
                    .replace(/L\.|HNL|,/gi, '')
                    .replace(/\s+/g, '')
                    .trim();
                var n = parseFloat(limpio);
                return isNaN(n) ? 0 : n;
            }

            function buildExcelButton(config) {
                return {
                    extend: 'excelHtml5',
                    title: '',
                    filename: function() {
                        return config.fileName;
                    },
                    messageTop: function() {
                        return '';
                    },
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        format: {
                            body: function(data, row, column) {
                                if (config.numberColumns.indexOf(column) >= 0) {
                                    return normalizarNumeroExcel(data);
                                }
                                return $('<div>').html(data).text();
                            }
                        }
                    },
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var $sheet = $(sheet);
                        var styles = xlsx.xl['styles.xml'];
                        var $styles = $(styles);

                        function escapeXml(text) {
                            return String(text || '')
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&apos;');
                        }

                        function colFromRef(ref) {
                            return String(ref || '').replace(/[0-9]/g, '');
                        }

                        var sheetData = $sheet.find('sheetData');
                        var horaDescarga = fechaHoraDescargaExcel();
                        var encabezado1 = 'Distribuciones Valencia';
                        var encabezado2 = config.reportTitle;
                        var encabezado3 = 'Hora de descarga: ' + horaDescarga + '  |  Descargado por: ' + (usuarioDescargaExcel || 'Sistema');

                        sheetData.find('row').each(function() {
                            var $row = $(this);
                            var oldRow = parseInt($row.attr('r') || '0', 10);
                            var newRow = oldRow + 4;
                            $row.attr('r', String(newRow));
                            $row.find('c').each(function() {
                                var $cell = $(this);
                                var oldRef = $cell.attr('r') || '';
                                var col = colFromRef(oldRef);
                                if (col) $cell.attr('r', col + newRow);
                            });
                        });

                        var filasHeader = '' +
                            '<row r="1"><c r="A1" t="inlineStr"><is><t>' + escapeXml(encabezado1) + '</t></is></c></row>' +
                            '<row r="2"><c r="A2" t="inlineStr"><is><t>' + escapeXml(encabezado2) + '</t></is></c></row>' +
                            '<row r="3"><c r="A3" t="inlineStr"><is><t>' + escapeXml(encabezado3) + '</t></is></c></row>' +
                            '<row r="4"><c r="A4" t="inlineStr"><is><t></t></is></c></row>';
                        sheetData.prepend(filasHeader);

                        var $dimension = $sheet.find('dimension');
                        if ($dimension.length) {
                            var ref = $dimension.attr('ref') || 'A1:J1';
                            var parts = ref.split(':');
                            if (parts.length === 2) {
                                var endCol = colFromRef(parts[1]) || 'J';
                                var endRow = parseInt(String(parts[1]).replace(/[^0-9]/g, '') || '1', 10) + 4;
                                $dimension.attr('ref', 'A1:' + endCol + endRow);
                            }
                        }

                        var $numFmts = $styles.find('numFmts');
                        if (!$numFmts.length) {
                            $styles.find('styleSheet').prepend('<numFmts count="0"></numFmts>');
                            $numFmts = $styles.find('numFmts');
                        }
                        if (!$numFmts.find('numFmt[numFmtId="300"]').length) {
                            $numFmts.append('<numFmt numFmtId="300" formatCode="&quot;L &quot;#,##0.00"/>');
                            $numFmts.attr('count', parseInt($numFmts.attr('count') || '0', 10) + 1);
                        }

                        var $cellXfs = $styles.find('cellXfs');
                        var xfCount = parseInt($cellXfs.attr('count') || '0', 10);

                        var estiloTextoEditable = xfCount;
                        $cellXfs.append('<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyProtection="1"><protection locked="0"/></xf>');
                        xfCount += 1;

                        var estiloMonedaEditable = xfCount;
                        $cellXfs.append('<xf numFmtId="300" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyProtection="1"><protection locked="0"/></xf>');
                        xfCount += 1;

                        $cellXfs.attr('count', xfCount);

                        if ($sheet.find('sheetProtection').length === 0) {
                            $sheet.find('worksheet').append('<sheetProtection sheet="1" objects="1" scenarios="1" selectLockedCells="1" selectUnlockedCells="1"/>');
                        }

                        sheetData.find('row').each(function() {
                            var $row = $(this);
                            var rowNum = parseInt($row.attr('r') || '0', 10);
                            if (rowNum >= 5) {
                                $row.find('c').each(function() {
                                    var $cell = $(this);
                                    var ref = $cell.attr('r') || '';
                                    var col = colFromRef(ref);
                                    if (config.moneyColumns.indexOf(col) >= 0 && rowNum >= 6) {
                                        $cell.attr('s', String(estiloMonedaEditable));
                                    } else {
                                        $cell.attr('s', String(estiloTextoEditable));
                                    }
                                });
                            }
                        });
                    }
                };
            }

            // ── Tipo buttons en modal ────────────────────────────────────────
            $(document).on('click', '#modalFiltrosCot .tipo-filter-btn', function() {
                $('#modalFiltrosCot .tipo-filter-btn').removeClass('active');
                $(this).addClass('active');
            });

            // ── Aplicar filtros ──────────────────────────────────────────────
            function aplicarFiltrosCot() {
                var activeBtn = document.querySelector('#modalFiltrosCot .tipo-filter-btn.active');
                if (activeBtn) cotFiltros.idTipo = parseInt(activeBtn.dataset.idtipo);
                cotFiltros.cliente  = $('#cotFiltroCliente').val()  || '';
                cotFiltros.vendedor = $('#cotFiltroVendedor').val() || '';

                $('#modalFiltrosCot').modal('hide');
                document.getElementById('cot-placeholder').style.display  = 'none';
                document.getElementById('cot-table-wrapper').style.display = '';
                document.getElementById('tbl_loading_overlay').style.display = '';

                // Breadcrumb + history
                var bcEl = document.querySelector('.breadcrumb-item.active a');
                if (bcEl) bcEl.textContent = nombresTipoCotiz[cotFiltros.idTipo] || '';
                history.pushState({ tipo: cotFiltros.idTipo }, '', urlHistoryCotiz[cotFiltros.idTipo]);

                // Sync header tipo buttons
                document.querySelectorAll('.cot-card-header .tipo-filter-btn').forEach(function(b) { b.classList.remove('active'); });
                var hdrBtn = document.querySelector('.cot-card-header .tipo-filter-btn[data-idtipo="' + cotFiltros.idTipo + '"]');
                if (hdrBtn) hdrBtn.classList.add('active');

                renderBadgesCot();

                if ($.fn.DataTable.isDataTable('#tbl_listar_cotizaciones')) {
                    $('#tbl_listar_cotizaciones').DataTable().ajax.reload(function() {
                        document.getElementById('tbl_loading_overlay').style.display = 'none';
                    });
                } else {
                    initDataTableCot();
                }
            }

            // ── Limpiar filtros ──────────────────────────────────────────────
            function limpiarFiltrosCot() {
                $('#cotFiltroCliente').val(null).trigger('change');
                $('#cotFiltroVendedor').val(null).trigger('change');
                cotFiltros.cliente  = '';
                cotFiltros.vendedor = '';
                $('#modalFiltrosCot .tipo-filter-btn').removeClass('active');
                $('#modalFiltrosCot .tipo-filter-btn[data-idtipo="{{ $idTipoVenta }}"]').addClass('active');
            }

            // ── Badges de filtros activos ────────────────────────────────────
            function renderBadgesCot() {
                var bar  = document.getElementById('filtrosBarCot');
                var html = '<span class="filtro-badge"><i class="fa fa-tag mr-1"></i>Tipo: ' + (nombresTipoCotiz[cotFiltros.idTipo] || '') + '</span>';
                if (cotFiltros.cliente)
                    html += '<span class="filtro-badge">Cliente: ' + ($('#cotFiltroCliente option:selected').text() || cotFiltros.cliente) + ' <span class="filtro-remove" onclick="quitarFiltroCot(\'cliente\')">×</span></span>';
                if (cotFiltros.vendedor)
                    html += '<span class="filtro-badge">Vendedor: ' + ($('#cotFiltroVendedor option:selected').text() || cotFiltros.vendedor) + ' <span class="filtro-remove" onclick="quitarFiltroCot(\'vendedor\')">×</span></span>';
                bar.innerHTML = html;
                bar.style.display = '';
            }

            function quitarFiltroCot(key) {
                if (key === 'cliente')  { cotFiltros.cliente  = ''; $('#cotFiltroCliente').val(null).trigger('change'); }
                if (key === 'vendedor') { cotFiltros.vendedor = ''; $('#cotFiltroVendedor').val(null).trigger('change'); }
                renderBadgesCot();
                if ($.fn.DataTable.isDataTable('#tbl_listar_cotizaciones'))
                    $('#tbl_listar_cotizaciones').DataTable().ajax.reload();
            }

            // ── Inicializar DataTable ────────────────────────────────────────
            function initDataTableCot() {
                $('#tbl_listar_cotizaciones').DataTable({
                    "order": [8, 'desc'],
                    "language": { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
                    pageLength: 10,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [buildExcelButton({
                        fileName: 'Listado_Cotizaciones',
                        reportTitle: 'Reporte de cotizaciones',
                        numberColumns: [3, 4, 5],
                        moneyColumns: ['D', 'E', 'F']
                    })],
                    "ajax": {
                        'url':  '/cotizacion/obtener/listado',
                        'type': 'post',
                        'headers': { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        'data': function(d) {
                            d.id             = cotFiltros.idTipo;
                            d.filtroCliente  = cotFiltros.cliente;
                            d.filtroVendedor = cotFiltros.vendedor;
                        }
                    },
                    "columns": [
                        { data: 'codigo' },
                        { data: 'nombre_cliente' },
                        { data: 'RTN' },
                        { data: 'sub_total' },
                        { data: 'isv' },
                        { data: 'total' },
                        { data: 'vendedor' },
                        { data: 'cotizador' },
                        { data: 'created_at' },
                        { data: 'opciones', orderable: false }
                    ],
                    "initComplete": function() {
                        document.getElementById('tbl_loading_overlay').style.display = 'none';
                    }
                });
            }

            // ── Header tipo buttons ──────────────────────────────────────────
            function cambiarTipoCotizacion(nuevoIdTipo, btnElement) {
                document.querySelectorAll('.cot-card-header .tipo-filter-btn').forEach(function(b) { b.classList.remove('active'); });
                btnElement.classList.add('active');
                $('#modalFiltrosCot .tipo-filter-btn').removeClass('active');
                $('#modalFiltrosCot .tipo-filter-btn[data-idtipo="' + nuevoIdTipo + '"]').addClass('active');
                cotFiltros.idTipo = nuevoIdTipo;
                var bcEl = document.querySelector('.breadcrumb-item.active a');
                if (bcEl) bcEl.textContent = nombresTipoCotiz[nuevoIdTipo];
                history.pushState({ tipo: nuevoIdTipo }, '', urlHistoryCotiz[nuevoIdTipo]);
                if (document.getElementById('cot-table-wrapper').style.display !== 'none') {
                    document.getElementById('tbl_loading_overlay').style.display = '';
                    renderBadgesCot();
                    if ($.fn.DataTable.isDataTable('#tbl_listar_cotizaciones')) {
                        $('#tbl_listar_cotizaciones').DataTable().ajax.reload(function() {
                            document.getElementById('tbl_loading_overlay').style.display = 'none';
                        });
                    }
                }
            }

            // ── Select2 + fix focusin.modal ──────────────────────────────────
            $(document).ready(function() {
                function s2opts(url, placeholder) {
                    return {
                        ajax: {
                            url: url, dataType: 'json', delay: 300,
                            data: function(p) { return { q: p.term || '' }; },
                            processResults: function(data) { return { results: data.results }; },
                            cache: true
                        },
                        placeholder: placeholder, allowClear: true, minimumInputLength: 2,
                        language: {
                            inputTooShort: function() { return 'Ingrese al menos 2 caracteres...'; },
                            searching: function() { return 'Buscando...'; },
                            noResults: function() { return 'Sin resultados'; }
                        },
                        width: '100%', dropdownParent: $('body')
                    };
                }
                $('#cotFiltroCliente').select2(s2opts('/filtros/cotizaciones/clientes', 'Buscar cliente...'));
                $('#cotFiltroVendedor').select2(s2opts('/filtros/facturas/usuarios', 'Buscar vendedor...'));

                $(document).on('select2:open', function() {
                    $(document).off('focusin.modal');
                    var campo = document.querySelector('.select2-container--open .select2-search__field');
                    if (campo) campo.focus();
                });

                setTimeout(function() { $('#modalFiltrosCot').modal('show'); }, 400);
            });
        </script>
        <script src="{{ asset('js/js_proyecto/cotizaciones/listar-cotizaciones.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function imprimirProformaConValidacion(event, idCotizacion) {
                event.preventDefault();
                axios.get('/cotizacion/validar-proforma/' + idCotizacion)
                    .then(function(response) {
                        var data = response.data;
                        if (data.valido) {
                            window.open('/proforma/imprimir/' + idCotizacion, '_blank');
                        } else {
                            Swal.fire({
                                icon: data.icon,
                                title: data.titulo,
                                text: data.mensaje,
                            });
                        }
                    })
                    .catch(function(err) {
                        var mensaje = 'Ha ocurrido un error al validar la proforma.';
                        if (err.response && err.response.data && err.response.data.mensaje) {
                            mensaje = err.response.data.mensaje;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: mensaje,
                        });
                    });
            }
        </script>
    @endpush
</div>
