<div>
    {{-- ════════════════════════════════════════════════════════════════
         LISTADO NOTAS DE DÉBITO — CLIENTES A (Gobierno/Estado)
         ════════════════════════════════════════════════════════════════ --}}

    @push('styles')
    <style>
    :root { --ndba-grad: linear-gradient(135deg,#f39c12 0%,#e05a00 100%); --ndba-orange:#e67e22; --ndba-radius:8px; --ndba-shadow:0 2px 8px rgba(0,0,0,.10); }
    .rndba-card { border:1px solid #e8d5bf; border-radius:var(--ndba-radius); box-shadow:var(--ndba-shadow); background:#fff; overflow:visible; }
    .rndba-card-header { background:var(--ndba-grad); padding:12px 20px; border-radius:var(--ndba-radius) var(--ndba-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .rndba-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .rndba-card-body { padding:16px 20px; }
    .btn-rndba-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-rndba-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    .rndba-stats { display:flex; gap:10px; flex-wrap:wrap; padding:10px 20px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
    .rndba-stat-pill { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf; border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500; }
    .rndba-stat-pill .pill-val { font-size:.9rem; font-weight:700; color:var(--ndba-orange); }
    .rndba-stat-pill.green { background:#f0fdf4; border-color:#bbf7d0; }
    .rndba-stat-pill.green .pill-val { color:#1a7a4e; }
    .filtros-bar-ndba { padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:center; gap:6px; font-size:.78rem; }
    .filtro-badge-ndba { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
    #tbl_nd_a { width:100%!important; }
    #tbl_nd_a thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_nd_a tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_nd_a tbody tr:hover>td { background:#fffcf5; }
    .modal-header-ndba { background:var(--ndba-grad); color:#fff; border-radius:var(--ndba-radius) var(--ndba-radius) 0 0; padding:14px 20px; }
    .modal-header-ndba .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-ndba .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-ndba .close:hover { opacity:1; }
    .modal-section-label-ndba { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
    #modalFiltrosNDBA .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
    #modalFiltrosNDBA .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    #modalFiltrosNDBA .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
    #modalFiltrosNDBA .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    #modalFiltrosNDBA .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .modal-filter-grid-ndba { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
    .date-input-icon { position:relative; }
    .date-input-icon i { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
    .date-input-icon input { padding-left:28px; }
    .select2-container--open { z-index:99999!important; }
    #page-wrapper { padding-left:0!important; padding-right:0!important; }
    .wrapper-content { padding-left:0!important; padding-right:0!important; }
    .wrapper-content>.row { margin-left:0!important; margin-right:0!important; }
    .wrapper-content>.row>[class*="col-"] { padding-left:0!important; padding-right:0!important; }
    </style>
    @endpush

    {{-- ── Page heading ── --}}
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-plus-circle mr-2" style="color:#e67e22"></i>Notas de Débito &mdash; Clientes A</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Notas de Débito</li>
                <li class="breadcrumb-item active"><strong>Clientes A</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="rndba-card">

                    <div class="rndba-card-header">
                        <h5><i class="fa fa-plus-circle"></i> Listado de Notas de Débito — Clientes A</h5>
                        <div class="d-flex" style="gap:8px">
                            <button type="button" class="btn-rndba-action" onclick="exportarExcelNDBA()">
                                <i class="fa fa-file-excel-o mr-1"></i>Excel
                            </button>
                            <button type="button" class="btn-rndba-action" data-toggle="modal" data-target="#modalFiltrosNDBA">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>

                    <div class="rndba-stats">
                        <div class="rndba-stat-pill">
                            <i class="fa fa-plus-circle" style="color:var(--ndba-orange)"></i>
                            <span class="pill-val" id="kpi_ndba_total">&#8212;</span>
                            <span>Notas</span>
                        </div>
                        <div class="rndba-stat-pill green">
                            <i class="fa fa-money" style="color:#1a7a4e"></i>
                            <span class="pill-val" id="kpi_ndba_monto">&#8212;</span>
                            <span>Monto Total</span>
                        </div>
                        <div class="rndba-stat-pill">
                            <i class="fa fa-check-circle" style="color:var(--ndba-orange)"></i>
                            <span class="pill-val" id="kpi_ndba_activas">&#8212;</span>
                            <span>Activas</span>
                        </div>
                        <div class="rndba-stat-pill">
                            <i class="fa fa-ban" style="color:#6b7280"></i>
                            <span class="pill-val" id="kpi_ndba_anuladas">&#8212;</span>
                            <span>Anuladas</span>
                        </div>
                    </div>

                    <div class="filtros-bar-ndba" id="ndba_filtros_bar" style="display:none;"></div>

                    <div class="rndba-card-body">
                        <div style="overflow-x:auto;">
                            <table id="tbl_nd_a" class="table table-hover table-bordered" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nota de Débito</th>
                                        <th class="text-right">Monto Asignado</th>
                                        <th>Código Factura</th>
                                        <th>Cliente</th>
                                        <th>Fecha Emisión</th>
                                        <th>Registrado por</th>
                                        <th>Estado</th>
                                        <th>Documento</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
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

    <div class="modal fade" id="modalFiltrosNDBA" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-ndba">
                    <h5 class="modal-title"><i class="fa fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body pb-2">
                    <p class="modal-section-label-ndba"><i class="fa fa-calendar"></i>Rango de fechas</p>
                    <div class="modal-filter-grid-ndba">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="ndba_fil_fecha_desde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="ndba_fil_fecha_hasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="modal-section-label-ndba"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                    <div class="modal-filter-grid-ndba">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select id="ndba_fil_cliente" class="form-control" style="width:100%">
                                        <option value=""></option>
                                        @foreach($clientes as $cl)
                                            <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select id="ndba_fil_estado" class="form-control form-control-sm">
                                        <option value="">— Todos —</option>
                                        <option value="1">Activo</option>
                                        <option value="2">Anulado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Registrado por</label>
                                    <select id="ndba_fil_usuario" class="form-control" style="width:100%">
                                        <option value=""></option>
                                        @foreach($usuarios as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ndbaLimpiarFiltros()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar filtros
                    </button>
                    <button type="button" class="btn btn-sm" onclick="ndbaAplicarFiltros()"
                        style="background:linear-gradient(135deg,#f39c12,#e05a00);color:#fff;border:none;font-weight:600;padding:6px 20px;border-radius:5px">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/nota-debito/listado-notas-debito-nd.js') }}"></script>
    <script>
    var ndbaTable = null;
    var ndbaFiltros = {
        fecha_desde: '{{ $fechaInicio }}',
        fecha_hasta: '{{ date("Y-m-t") }}',
        cliente_id:  '',
        estado_id:   '',
        user_id:     ''
    };

    $(document).ready(function() {
        $('#ndba_fil_cliente').select2({ dropdownParent: $('#modalFiltrosNDBA'), placeholder: '— Todos —', allowClear: true });
        $('#ndba_fil_usuario').select2({ dropdownParent: $('#modalFiltrosNDBA'), placeholder: '— Todos —', allowClear: true });
        $('#ndba_fil_fecha_desde').val('{{ $fechaInicio }}');
        $('#ndba_fil_fecha_hasta').val('{{ date("Y-m-t") }}');
        ndbaCargarTabla();
    });

    function ndbaCargarTabla() {
        if (ndbaTable) { ndbaTable.destroy(); }
        ndbaTable = $('#tbl_nd_a').DataTable({
            order: [[0, 'desc']],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            pageLength: 25,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            ajax: {
                url: '/listado/nota/debito/gobierno/' + ndbaFiltros.fecha_desde + '/' + ndbaFiltros.fecha_hasta,
                data: { cliente_id: ndbaFiltros.cliente_id, estado_id: ndbaFiltros.estado_id, user_id: ndbaFiltros.user_id }
            },
            columns: [
                { data: 'id' }, { data: 'correlativoND' },
                { data: 'monto_asignado', className: 'text-right' },
                { data: 'cai' }, { data: 'cliente' }, { data: 'fechaEmision' },
                { data: 'user' }, { data: 'estado', orderable: false },
                { data: 'file', orderable: false }, { data: 'created_at' },
                { data: 'acciones', orderable: false }
            ],
            initComplete: function() { ndbaActualizarKpis(); }
        });
    }

    function ndbaAplicarFiltros() {
        ndbaFiltros.fecha_desde = $('#ndba_fil_fecha_desde').val() || '{{ $fechaInicio }}';
        ndbaFiltros.fecha_hasta = $('#ndba_fil_fecha_hasta').val() || '{{ date("Y-m-t") }}';
        ndbaFiltros.cliente_id  = $('#ndba_fil_cliente').val()  || '';
        ndbaFiltros.estado_id   = $('#ndba_fil_estado').val()   || '';
        ndbaFiltros.user_id     = $('#ndba_fil_usuario').val()  || '';
        $('#modalFiltrosNDBA').modal('hide');
        ndbaCargarTabla();
        ndbaActualizarBadges();
    }

    function ndbaLimpiarFiltros() {
        $('#ndba_fil_fecha_desde').val('{{ $fechaInicio }}');
        $('#ndba_fil_fecha_hasta').val('{{ date("Y-m-t") }}');
        $('#ndba_fil_cliente').val(null).trigger('change');
        $('#ndba_fil_estado').val('');
        $('#ndba_fil_usuario').val(null).trigger('change');
        ndbaFiltros = { fecha_desde: '{{ $fechaInicio }}', fecha_hasta: '{{ date("Y-m-t") }}', cliente_id: '', estado_id: '', user_id: '' };
        ndbaActualizarBadges();
    }

    function ndbaActualizarKpis() {
        $.ajax({
            url: '/nota/debito/kpis', type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), fechaInicio: ndbaFiltros.fecha_desde,
                    fechaFinal: ndbaFiltros.fecha_hasta, cliente_id: ndbaFiltros.cliente_id,
                    estado_id: ndbaFiltros.estado_id, user_id: ndbaFiltros.user_id, tipo_cliente: 2 },
            success: function(r) {
                if (!r.success) return;
                $('#kpi_ndba_total').text(r.total + ' notas');
                $('#kpi_ndba_monto').text(ndbaFmt(r.monto_total));
                $('#kpi_ndba_activas').text(r.activas);
                $('#kpi_ndba_anuladas').text(r.anuladas);
            }
        });
    }

    function ndbaActualizarBadges() {
        var bar = $('#ndba_filtros_bar'); bar.empty();
        var badges = [];
        if (ndbaFiltros.fecha_desde) badges.push({ lbl:'Desde', val:ndbaFiltros.fecha_desde });
        if (ndbaFiltros.fecha_hasta) badges.push({ lbl:'Hasta', val:ndbaFiltros.fecha_hasta });
        if (ndbaFiltros.cliente_id)  badges.push({ lbl:'Cliente', val:$('#ndba_fil_cliente option:selected').text() });
        if (ndbaFiltros.estado_id)   badges.push({ lbl:'Estado',  val:$('#ndba_fil_estado option:selected').text() });
        if (ndbaFiltros.user_id)     badges.push({ lbl:'Usuario', val:$('#ndba_fil_usuario option:selected').text() });
        if (badges.length) { bar.show(); badges.forEach(function(b) { bar.append('<span class="filtro-badge-ndba"><strong>' + b.lbl + ':</strong> ' + b.val + '</span> '); }); }
        else { bar.hide(); }
    }

    function exportarExcelNDBA() {
        var tok = $('meta[name="csrf-token"]').attr('content');
        var form = $('<form method="POST" action="/nota/debito/gobierno/exportar-excel"></form>');
        var f = { _token: tok, fechaInicio: ndbaFiltros.fecha_desde, fechaFinal: ndbaFiltros.fecha_hasta,
                  cliente_id: ndbaFiltros.cliente_id, estado_id: ndbaFiltros.estado_id, user_id: ndbaFiltros.user_id, tipo_cliente: 2 };
        $.each(f, function(k,v){ form.append($('<input type="hidden">').attr('name',k).val(v)); });
        $('body').append(form); form.submit(); form.remove();
    }

    function ndbaFmt(v) {
        if (v === null || v === undefined) return '—';
        return 'L ' + parseFloat(v).toLocaleString('es-HN', { minimumFractionDigits:2, maximumFractionDigits:2 });
    }
    </script>
    @endpush
</div>
