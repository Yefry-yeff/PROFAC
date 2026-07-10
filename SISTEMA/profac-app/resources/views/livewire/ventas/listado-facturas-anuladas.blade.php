<div>
    @push('styles')
    <style>
    :root {
        --pf-grad:   linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --pf-orange: #e67e22;
        --pf-radius: 8px;
        --pf-shadow: 0 2px 8px rgba(0,0,0,.10);
    }
    .anul-card {
        border: 1px solid #e8d5bf;
        border-radius: var(--pf-radius);
        box-shadow: var(--pf-shadow);
        background: #fff;
    }
    .anul-card-header {
        background: var(--pf-grad);
        padding: 12px 20px;
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
        display: flex; align-items: center;
        justify-content: space-between;
        gap: 8px; flex-wrap: wrap;
    }
    .anul-card-header h5 {
        margin: 0; color: #fff;
        font-size: .85rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase;
        display: flex; align-items: center; gap: 8px;
    }
    .anul-card-body { padding: 12px 16px; }
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
    #tbl_listar_compras { width: 100% !important; }
    #tbl_listar_compras thead th {
        background: #fdf4e7; color: #7d3f00;
        font-size: .70rem; font-weight: 700;
        letter-spacing: .04em; text-transform: uppercase;
        border-bottom: 2px solid #f2d49a;
        white-space: nowrap; padding: 7px 8px; vertical-align: middle;
    }
    #tbl_listar_compras tbody td {
        font-size: .80rem; vertical-align: middle; padding: 6px 8px;
    }
    .factura-link {
        color: #e67e22;
        font-weight: 600;
        text-decoration: underline;
        cursor: pointer;
    }
    .factura-link:hover { color: #c76305; }
    #tbl_listar_compras tbody tr:hover { background: #fffcf5; }
    .modal-header-fact {
        background: var(--pf-grad); color: #fff;
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    }
    .modal-header-fact .modal-title { color: #fff; font-size: .95rem; }
    .modal-header-fact .close { color: #fff; opacity: .8; text-shadow: none; }
    .modal-header-fact .close:hover { opacity: 1; }
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
    #modalDetalleFacturaAnul .modal-body {
        background: #fffdf9;
        padding: 16px 18px;
    }
    #modalDetalleFacturaAnul .table thead th {
        background: #fdf4e7;
        color: #7d3f00;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 2px solid #f2d49a;
    }
    #modalDetalleFacturaAnul .table tbody td {
        font-size: .82rem;
        vertical-align: middle;
    }
    .detalle-tools {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .detalle-tools .form-control {
        max-width: 260px;
        height: 32px;
        font-size: .80rem;
    }
    </style>
    @endpush

    {{-- Loading Overlay --}}
    <div id="tbl_loading_overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%; display:none;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
        <p class="mt-3" style="color:#555; font-size:1rem;">Cargando datos...</p>
    </div>

    <!-- Modal Detalle Productos/Escala -->
    <div class="modal fade" id="modalDetalleFacturaAnul" tabindex="-1" role="dialog" aria-labelledby="tituloModalDetalleFacturaAnul" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-fact">
                    <h5 class="modal-title" id="tituloModalDetalleFacturaAnul">
                        <i class="fa fa-list-alt mr-2"></i>Detalle de productos por escala
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:8px">
                        <small class="text-muted">Factura: <strong id="detalleFacturaNumeroAnul">-</strong></small>
                    </div>

                    <div id="detalleFacturaLoadingAnul" class="text-center py-3" style="display:none">
                        <i class="fa fa-spinner fa-spin"></i> Cargando detalle...
                    </div>

                    <div class="detalle-tools">
                        <input type="text" id="detalleFacturaBuscarAnul" class="form-control form-control-sm" placeholder="Buscar producto o escala...">
                        <button type="button" id="btnDetalleFacturaExcelAnul" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o mr-1"></i>Excel
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="tblDetalleFacturaProductosAnul">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Cantidad</th>
                                    <th>Escala</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Seleccione una factura para ver el detalle.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Facturas Anuladas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a>{{$nombreTipo}}</a>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="anul-card">
                    <div class="anul-card-header">
                        <h5><i class="fa fa-ban"></i> Facturas Anuladas</h5>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <button type="button"
                                    class="tipo-filter-btn {{ $idTipoVenta == 2 ? 'active' : '' }}"
                                    data-idtipo="2" onclick="cambiarTipoAnulada(2, this)">
                                    <i class="fa fa-circle" style="font-size:.6rem;vertical-align:middle;"></i> Clientes A
                                </button>
                                <button type="button"
                                    class="tipo-filter-btn {{ $idTipoVenta == 1 ? 'active' : '' }}"
                                    data-idtipo="1" onclick="cambiarTipoAnulada(1, this)">
                                    <i class="fa fa-circle" style="font-size:.6rem;vertical-align:middle;"></i> Clientes B
                                </button>
                                <button type="button"
                                    class="tipo-filter-btn {{ $idTipoVenta == 3 ? 'active' : '' }}"
                                    data-idtipo="3" onclick="cambiarTipoAnulada(3, this)">
                                    <i class="fa fa-circle" style="font-size:.6rem;vertical-align:middle;"></i> Exoneradas
                                </button>
                            </div>
                            <button class="btn-fact-filter" data-toggle="modal" data-target="#modalFiltrosAnul">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>
                    {{-- Barra de filtros activos --}}
                    <div class="filtros-bar" id="filtrosBarAnul" style="display:none;"></div>

                    {{-- Placeholder --}}
                    <div id="anul-placeholder" class="text-center py-5" style="color:#aaa">
                        <i class="fa fa-filter" style="font-size:2.5rem; color:#e67e22; opacity:.45"></i>
                        <p class="mt-3 mb-0" style="font-size:1rem; font-weight:600">Aplique filtros para cargar los resultados</p>
                        <p class="small">Haga clic en <strong>Filtros</strong> para definir los criterios de b&uacute;squeda.</p>
                    </div>

                    <div class="anul-card-body" id="anul-table-wrapper" style="display:none;">
                        <div class="table-responsive">
                            <table id="tbl_listar_compras" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Codigo Interno</th>
                                        <th>N° Factura</th>
                                        <th>Fecha de Emision</th>
                                        <th>Cliente</th>
                                        <th>Tipo de Pago</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV en Lps.</th>
                                        <th>Total en Lps.</th>
                                        <th>Estado de Cobro</th>
                                        <th>Vendedor</th>
                                        <th>Facturador</th>
                                        <th>Fecha Registro</th>
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

    <!-- Modal de Filtros -->
    <div class="modal fade" id="modalFiltrosAnul" tabindex="-1" role="dialog" aria-labelledby="tituloFiltrosAnul" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-fact">
                    <h5 class="modal-title" id="tituloFiltrosAnul">
                        <i class="fa fa-filter mr-2"></i>Filtros de B&uacute;squeda
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2">
                    <p class="modal-section-label"><i class="fa fa-tag mr-1"></i>Tipo de factura</p>
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
                    <p class="modal-section-label"><i class="fa fa-calendar mr-1"></i>Rango de fechas</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Desde</label>
                                <input type="date" class="form-control form-control-sm" id="anulFiltroDesde">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Hasta</label>
                                <input type="date" class="form-control form-control-sm" id="anulFiltroHasta">
                            </div>
                        </div>
                    </div>

                    <p class="modal-section-label"><i class="fa fa-search mr-1"></i>Criterios de b&uacute;squeda</p>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                    <label class="font-weight-bold small">CAI</label>
                                <input type="text" class="form-control form-control-sm" id="anulFiltroCai"
                                        placeholder="Ej: 000-001-01-00041992">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">Cliente</label>
                                <select id="anulFiltroCliente" class="form-control" style="width:100%">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">Vendedor</label>
                                <select id="anulFiltroVendedor" class="form-control" style="width:100%">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">Facturador</label>
                                <select id="anulFiltroFacturador" class="form-control" style="width:100%">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="limpiarFiltrosAnul()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="aplicarFiltrosAnul()">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registro de marca-->
    <div class="modal fade" id="modal_detalle_anular" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header modal-header-fact">
                            <h5 class="modal-title"><i class="fa fa-info-circle"></i> Detalle de Anulación</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="crearProductoForm" name="crearProductoForm" data-parsley-validate>
                                {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                <div class="row" id="row_datos">
                                    <div class="col-md-12">
                                        <label for="codigo" class="col-form-label focus-label">Código
                                            de factua:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="codigo"
                                            name="codigo" data-parsley-required readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="cai" class="col-form-label focus-label">CAI:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="cai"
                                            name="cai" data-parsley-required readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="motivo" class="col-form-label focus-label">Motivo:<span class="text-danger">*</span></label>
                                         <textarea class="form-control" required name="motivo" id="motivo"  rows="4" readonly></textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="usuario" class="col-form-label focus-label">Anulado por:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="usuario"
                                            name="usuario" data-parsley-required readonly>
                                    </div>






                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>


                        </div>
                    </div>
                </div>
            </div>

    @push('scripts')
        <script>
            // ── Configuración ────────────────────────────────────────────────
            var usuarioDescargaExcel = @json(optional(Auth::user())->name ?? 'Sistema');
            var anulNombresTipo = { 1: 'Clientes B', 2: 'Clientes A', 3: 'Exoneradas' };
            var anulUrlHistory  = { 1: '/ventas/anulado/corporativo', 2: '/ventas/anulado/estatal', 3: '/ventas/anulado/exonerado' };
            var anulFiltros = {
                idTipo:   {{ $idTipoVenta }},
                cai:      '',
                cliente:  '',
                vendedor: '',
                facturador: '',
                desde: '',
                hasta: ''
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
                            var ref = $dimension.attr('ref') || 'A1:O1';
                            var parts = ref.split(':');
                            if (parts.length === 2) {
                                var endCol = colFromRef(parts[1]) || 'O';
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
            $(document).on('click', '#modalFiltrosAnul .tipo-filter-btn', function() {
                $('#modalFiltrosAnul .tipo-filter-btn').removeClass('active');
                $(this).addClass('active');
            });

            // ── Aplicar filtros ──────────────────────────────────────────────
            function aplicarFiltrosAnul() {
                var activeBtn = document.querySelector('#modalFiltrosAnul .tipo-filter-btn.active');
                if (activeBtn) anulFiltros.idTipo = parseInt(activeBtn.dataset.idtipo);
                anulFiltros.cai      = document.getElementById('anulFiltroCai').value.trim();
                anulFiltros.cliente  = $('#anulFiltroCliente').val()  || '';
                anulFiltros.vendedor = $('#anulFiltroVendedor').val() || '';
                anulFiltros.facturador = $('#anulFiltroFacturador').val() || '';
                anulFiltros.desde    = document.getElementById('anulFiltroDesde').value || '';
                anulFiltros.hasta    = document.getElementById('anulFiltroHasta').value || '';

                $('#modalFiltrosAnul').modal('hide');
                document.getElementById('anul-placeholder').style.display  = 'none';
                document.getElementById('anul-table-wrapper').style.display = '';
                document.getElementById('tbl_loading_overlay').style.display = '';

                // Breadcrumb + history
                var bcEl = document.querySelector('.breadcrumb-item.active a');
                if (bcEl) bcEl.textContent = anulNombresTipo[anulFiltros.idTipo] || '';
                history.pushState({ tipo: anulFiltros.idTipo }, '', anulUrlHistory[anulFiltros.idTipo]);

                // Sync header tipo buttons
                document.querySelectorAll('.anul-card-header .tipo-filter-btn').forEach(function(b) { b.classList.remove('active'); });
                var hdrBtn = document.querySelector('.anul-card-header .tipo-filter-btn[data-idtipo="' + anulFiltros.idTipo + '"]');
                if (hdrBtn) hdrBtn.classList.add('active');

                renderBadgesAnul();

                if ($.fn.DataTable.isDataTable('#tbl_listar_compras')) {
                    $('#tbl_listar_compras').DataTable().ajax.reload(function() {
                        document.getElementById('tbl_loading_overlay').style.display = 'none';
                    });
                } else {
                    initDataTableAnul();
                }
            }

            // ── Limpiar filtros ──────────────────────────────────────────────
            function limpiarFiltrosAnul() {
                document.getElementById('anulFiltroCai').value = '';
                document.getElementById('anulFiltroDesde').value = '';
                document.getElementById('anulFiltroHasta').value = '';
                $('#anulFiltroCliente').val(null).trigger('change');
                $('#anulFiltroVendedor').val(null).trigger('change');
                $('#anulFiltroFacturador').val(null).trigger('change');
                anulFiltros.cai      = '';
                anulFiltros.cliente  = '';
                anulFiltros.vendedor = '';
                anulFiltros.facturador = '';
                anulFiltros.desde = '';
                anulFiltros.hasta = '';
                $('#modalFiltrosAnul .tipo-filter-btn').removeClass('active');
                $('#modalFiltrosAnul .tipo-filter-btn[data-idtipo="{{ $idTipoVenta }}"]').addClass('active');
            }

            // ── Badges de filtros activos ────────────────────────────────────
            function renderBadgesAnul() {
                var bar  = document.getElementById('filtrosBarAnul');
                var html = '<span class="filtro-badge"><i class="fa fa-tag mr-1"></i>Tipo: ' + (anulNombresTipo[anulFiltros.idTipo] || '') + '</span>';
                if (anulFiltros.cai)
                    html += '<span class="filtro-badge">CAI: ' + anulFiltros.cai + ' <span class="filtro-remove" onclick="quitarFiltroAnul(\'cai\')">×</span></span>';
                if (anulFiltros.cliente)
                    html += '<span class="filtro-badge">Cliente: ' + ($('#anulFiltroCliente option:selected').text() || anulFiltros.cliente) + ' <span class="filtro-remove" onclick="quitarFiltroAnul(\'cliente\')">×</span></span>';
                if (anulFiltros.vendedor)
                    html += '<span class="filtro-badge">Vendedor: ' + ($('#anulFiltroVendedor option:selected').text() || anulFiltros.vendedor) + ' <span class="filtro-remove" onclick="quitarFiltroAnul(\'vendedor\')">×</span></span>';
                if (anulFiltros.facturador)
                    html += '<span class="filtro-badge">Facturador: ' + ($('#anulFiltroFacturador option:selected').text() || anulFiltros.facturador) + ' <span class="filtro-remove" onclick="quitarFiltroAnul(\'facturador\')">×</span></span>';
                if (anulFiltros.desde)
                    html += '<span class="filtro-badge">Desde: ' + anulFiltros.desde + ' <span class="filtro-remove" onclick="quitarFiltroAnul(\'desde\')">×</span></span>';
                if (anulFiltros.hasta)
                    html += '<span class="filtro-badge">Hasta: ' + anulFiltros.hasta + ' <span class="filtro-remove" onclick="quitarFiltroAnul(\'hasta\')">×</span></span>';
                bar.innerHTML = html;
                bar.style.display = '';
            }

            function quitarFiltroAnul(key) {
                if (key === 'cai')      { anulFiltros.cai      = ''; document.getElementById('anulFiltroCai').value = ''; }
                if (key === 'cliente')  { anulFiltros.cliente  = ''; $('#anulFiltroCliente').val(null).trigger('change'); }
                if (key === 'vendedor') { anulFiltros.vendedor = ''; $('#anulFiltroVendedor').val(null).trigger('change'); }
                if (key === 'facturador') { anulFiltros.facturador = ''; $('#anulFiltroFacturador').val(null).trigger('change'); }
                if (key === 'desde')    { anulFiltros.desde    = ''; document.getElementById('anulFiltroDesde').value = ''; }
                if (key === 'hasta')    { anulFiltros.hasta    = ''; document.getElementById('anulFiltroHasta').value = ''; }
                renderBadgesAnul();
                if ($.fn.DataTable.isDataTable('#tbl_listar_compras'))
                    $('#tbl_listar_compras').DataTable().ajax.reload();
            }

            // ── Inicializar DataTable ────────────────────────────────────────
            function initDataTableAnul() {
                $('#tbl_listar_compras').DataTable({
                    "order": [0, 'desc'],
                    "language": { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
                    pageLength: 10,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [buildExcelButton({
                        fileName: 'Facturas_Anuladas',
                        reportTitle: 'Reporte de facturas anuladas',
                        numberColumns: [6, 7, 8],
                        moneyColumns: ['G', 'H', 'I']
                    })],
                    "ajax": {
                        'url':  '/ventas/anulado/listado',
                        'type': 'post',
                        'headers': { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        'data': function(d) {
                            d.idTipo         = anulFiltros.idTipo;
                            d.filtroCai      = anulFiltros.cai;
                            d.filtroCliente  = anulFiltros.cliente;
                            d.filtroVendedor = anulFiltros.vendedor;
                            d.filtroFacturador = anulFiltros.facturador;
                            d.filtroDesde    = anulFiltros.desde;
                            d.filtroHasta    = anulFiltros.hasta;
                        }
                    },
                    "columns": [
                        { data: 'id' },
                        {
                            data: 'cai',
                            render: function(d, type, row) {
                                if (type !== 'display') return d;
                                var label = d || '';
                                if (!row || !row.id) return label;
                                return '<a href="javascript:void(0)" class="factura-link anul-factura-link" data-factura-id="' + row.id + '" data-factura-cai="' + label + '">' + label + '</a>';
                            }
                        },
                        { data: 'fecha_emision' },
                        { data: 'nombre' },
                        { data: 'descripcion' },
                        { data: 'fecha_vencimiento' },
                        { data: 'sub_total' },
                        { data: 'isv' },
                        { data: 'total' },
                        { data: 'estado_cobro' },
                        { data: 'vendedor' },
                        { data: 'facturador' },
                        { data: 'fecha_registro' },
                        { data: 'opciones', orderable: false }
                    ],
                    "initComplete": function() {
                        document.getElementById('tbl_loading_overlay').style.display = 'none';
                    }
                });
            }

            // ── Header tipo buttons ──────────────────────────────────────────
            function cambiarTipoAnulada(nuevoIdTipo, btnElement) {
                document.querySelectorAll('.anul-card-header .tipo-filter-btn').forEach(function(b) { b.classList.remove('active'); });
                btnElement.classList.add('active');
                $('#modalFiltrosAnul .tipo-filter-btn').removeClass('active');
                $('#modalFiltrosAnul .tipo-filter-btn[data-idtipo="' + nuevoIdTipo + '"]').addClass('active');
                anulFiltros.idTipo = nuevoIdTipo;
                var bcEl = document.querySelector('.breadcrumb-item.active a');
                if (bcEl) bcEl.textContent = anulNombresTipo[nuevoIdTipo];
                history.pushState({ tipo: nuevoIdTipo }, '', anulUrlHistory[nuevoIdTipo]);
                if (document.getElementById('anul-table-wrapper').style.display !== 'none') {
                    document.getElementById('tbl_loading_overlay').style.display = '';
                    renderBadgesAnul();
                    if ($.fn.DataTable.isDataTable('#tbl_listar_compras')) {
                        $('#tbl_listar_compras').DataTable().ajax.reload(function() {
                            document.getElementById('tbl_loading_overlay').style.display = 'none';
                        });
                    }
                }
            }

            // ── Select2 + fix focusin.modal ──────────────────────────────────
            $(document).ready(function() {
                var detalleFacturaAnulTable = null;
                var detalleFacturaAnulActual = '';

                function formatoNumeroModal(n) {
                    var num = Number(n || 0);
                    return num.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function renderDetalleFacturaAnul(productos) {
                    var rows = [];
                    (productos || []).forEach(function(item) {
                        rows.push({
                            producto: item.producto || '-',
                            cantidad: Number(item.cantidad || 0),
                            escala: item.escala || 'Sin escala',
                            monto: Number(item.monto || 0)
                        });
                    });

                    if (!detalleFacturaAnulTable) {
                        detalleFacturaAnulTable = $('#tblDetalleFacturaProductosAnul').DataTable({
                            data: rows,
                            paging: true,
                            pageLength: 10,
                            ordering: true,
                            info: true,
                            searching: true,
                            dom: 'Brtip',
                            language: { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
                            buttons: [{
                                extend: 'excelHtml5',
                                title: function() {
                                    return 'Detalle de productos por escala';
                                },
                                filename: function() {
                                    var num = (detalleFacturaAnulActual || 'factura').replace(/[^0-9A-Za-z_-]/g, '_');
                                    return 'Distribuciones_Valencia_Detalle_' + num;
                                },
                                className: 'd-none'
                            }],
                            columns: [
                                { data: 'producto' },
                                { data: 'cantidad', className: 'text-right', render: function(d, t) { return t === 'display' ? formatoNumeroModal(d) : d; } },
                                { data: 'escala' },
                                { data: 'monto', className: 'text-right', render: function(d, t) { return t === 'display' ? ('L. ' + formatoNumeroModal(d)) : d; } }
                            ]
                        });
                    } else {
                        detalleFacturaAnulTable.clear().rows.add(rows).draw();
                    }

                    $('#detalleFacturaBuscarAnul').val('');
                    detalleFacturaAnulTable.search('').draw();
                }

                function abrirDetalleFacturaAnul(idFactura, numeroFactura) {
                    var loading = document.getElementById('detalleFacturaLoadingAnul');
                    var numero  = document.getElementById('detalleFacturaNumeroAnul');
                    detalleFacturaAnulActual = numeroFactura || ('#' + idFactura);

                    if (numero) numero.textContent = detalleFacturaAnulActual;
                    if (detalleFacturaAnulTable) detalleFacturaAnulTable.clear().draw();
                    if (loading) loading.style.display = '';

                    $('#modalDetalleFacturaAnul').modal('show');

                    axios.get('/factura/detalle-productos-escala/' + idFactura)
                        .then(function(response) {
                            var data = response.data || {};
                            renderDetalleFacturaAnul(data.productos || []);
                        })
                        .catch(function() {
                            if (detalleFacturaAnulTable) detalleFacturaAnulTable.clear().draw();
                        })
                        .finally(function() {
                            if (loading) loading.style.display = 'none';
                        });
                }

                $(document).on('click', '.anul-factura-link', function() {
                    var idFactura = this.getAttribute('data-factura-id');
                    var numeroFac = this.getAttribute('data-factura-cai');
                    if (!idFactura) return;
                    abrirDetalleFacturaAnul(idFactura, numeroFac);
                });

                $('#detalleFacturaBuscarAnul').on('keyup', function() {
                    if (!detalleFacturaAnulTable) return;
                    detalleFacturaAnulTable.search(this.value || '').draw();
                });

                $('#btnDetalleFacturaExcelAnul').on('click', function() {
                    if (!detalleFacturaAnulTable) return;
                    detalleFacturaAnulTable.button(0).trigger();
                });

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
                $('#anulFiltroCliente').select2(s2opts('/filtros/facturas/clientes', 'Buscar cliente...'));
                $('#anulFiltroVendedor').select2(s2opts('/filtros/facturas/usuarios', 'Buscar vendedor...'));
                $('#anulFiltroFacturador').select2(s2opts('/filtros/facturas/usuarios', 'Buscar facturador...'));

                $(document).on('select2:open', function() {
                    $(document).off('focusin.modal');
                    var campo = document.querySelector('.select2-container--open .select2-search__field');
                    if (campo) campo.focus();
                });

                setTimeout(function() { $('#modalFiltrosAnul').modal('show'); }, 400);
            });

            // ── Funciones de acción ─────────────────────────────────────────
            function anularVentaConfirmar(idFactura) {
                Swal.fire({
                    title: '¿Está seguro de anular esta factura?',
                    text: 'Una vez anulada la factura el producto será devuelto al inventario.',
                    showCancelButton: true,
                    confirmButtonText: 'Si, Anular',
                    cancelButtonText: 'Cancelar',
                }).then(function(result) {
                    if (result.isConfirmed) anularVenta(idFactura);
                });
            }

            function anularVenta(idFactura) {
                axios.post('/factura/corporativo/anular', { idFactura: idFactura })
                .then(function(response) {
                    var data = response.data;
                    Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                    if ($.fn.DataTable.isDataTable('#tbl_listar_compras'))
                        $('#tbl_listar_compras').DataTable().ajax.reload();
                })
                .catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Ha ocurrido un error al anular la compra.' });
                });
            }

            function detallesDeAnulacion(idFactura) {
                axios.post('/ventas/anulado/detalle', { id: idFactura })
                .then(function(response) {
                    var data = response.data.datos;
                    document.getElementById('codigo').value  = data.codigo_factura;
                    document.getElementById('cai').value     = data.cai;
                    document.getElementById('motivo').value  = data.motivo;
                    document.getElementById('usuario').value = data.usuario;
                    $('#modal_detalle_anular').modal('show');
                })
                .catch(function(err) { console.log(err); });
            }
        </script>
    @endpush

    {{-- Footer: fecha y copyright --}}
    <?php
        date_default_timezone_set('America/Tegucigalpa');
        $act_fecha = date('Y-m-d');
        $year      = date('Y');
    ?>
    <div style="padding:8px 16px; font-size:.78rem; color:#777; display:flex; justify-content:space-between; flex-wrap:wrap; border-top:1px solid #e8ecef; margin-top:12px;">
        <div><strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo $year; ?></div>
        <div><?php echo $act_fecha; ?> <strong id="reloj"></strong></div>
    </div>
    <script>
        (function() {
            function mostrarHora() {
                var el = document.getElementById('reloj');
                if (!el) return;
                var f = new Date();
                var h = f.getHours(), m = f.getMinutes(), s = f.getSeconds();
                h = h < 10 ? '0'+h : h;
                m = m < 10 ? '0'+m : m;
                s = s < 10 ? '0'+s : s;
                el.textContent = h + ':' + m + ':' + s;
            }
            setInterval(mostrarHora, 1000);
        })();
    </script>
</div>
