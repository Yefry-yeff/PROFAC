<div>
    {{-- ════════════════════════════════════════════════════════════════
         LISTADO NOTAS DE DÉBITO — CLIENTES B (Corporativo)
         ════════════════════════════════════════════════════════════════ --}}

    @push('styles')
    <style>
    :root { --ndb-grad: linear-gradient(135deg,#f39c12 0%,#e05a00 100%); --ndb-orange:#e67e22; --ndb-radius:8px; --ndb-shadow:0 2px 8px rgba(0,0,0,.10); }
    .rndb-card { border:1px solid #e8d5bf; border-radius:var(--ndb-radius); box-shadow:var(--ndb-shadow); background:#fff; overflow:visible; }
    .rndb-card-header { background:var(--ndb-grad); padding:12px 20px; border-radius:var(--ndb-radius) var(--ndb-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .rndb-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .rndb-card-body { padding:16px 20px; }
    .btn-rndb-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-rndb-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    .rndb-stats { display:flex; gap:10px; flex-wrap:wrap; padding:10px 20px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
    .rndb-stat-pill { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf; border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500; }
    .rndb-stat-pill .pill-val { font-size:.9rem; font-weight:700; color:var(--ndb-orange); }
    .rndb-stat-pill.green { background:#f0fdf4; border-color:#bbf7d0; }
    .rndb-stat-pill.green .pill-val { color:#1a7a4e; }
    .filtros-bar-ndb { padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:center; gap:6px; font-size:.78rem; }
    .filtro-badge-ndb { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
    #tbl_nd_b { width:100%!important; }
    #tbl_nd_b thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_nd_b tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_nd_b tbody tr:hover>td { background:#fffcf5; }
    .modal-header-ndb { background:var(--ndb-grad); color:#fff; border-radius:var(--ndb-radius) var(--ndb-radius) 0 0; padding:14px 20px; }
    .modal-header-ndb .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-ndb .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-ndb .close:hover { opacity:1; }
    .modal-section-label-ndb { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
    #modalFiltrosNDB .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
    #modalFiltrosNDB .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    #modalFiltrosNDB .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
    #modalFiltrosNDB .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    #modalFiltrosNDB .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .modal-filter-grid-ndb { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
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
            <h2><i class="fa fa-plus-circle mr-2" style="color:#e67e22"></i>Notas de Débito &mdash; Clientes B</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Notas de Débito</li>
                <li class="breadcrumb-item active"><strong>Clientes B</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="rndb-card">

                    {{-- ── Header gradiente naranja ── --}}
                    <div class="rndb-card-header">
                        <h5><i class="fa fa-plus-circle"></i> Listado de Notas de Débito — Clientes B</h5>
                        <div class="d-flex" style="gap:8px">
                            <button type="button" class="btn-rndb-action" onclick="exportarExcelNDB()">
                                <i class="fa fa-file-excel-o mr-1"></i>Excel
                            </button>
                            <button type="button" class="btn-rndb-action" data-toggle="modal" data-target="#modalFiltrosNDB">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>

                    {{-- ── KPI pills ── --}}
                    <div class="rndb-stats">
                        <div class="rndb-stat-pill">
                            <i class="fa fa-plus-circle" style="color:var(--ndb-orange)"></i>
                            <span class="pill-val" id="kpi_ndb_total">&#8212;</span>
                            <span>Notas</span>
                        </div>
                        <div class="rndb-stat-pill green">
                            <i class="fa fa-money" style="color:#1a7a4e"></i>
                            <span class="pill-val" id="kpi_ndb_monto">&#8212;</span>
                            <span>Monto Total</span>
                        </div>
                        <div class="rndb-stat-pill">
                            <i class="fa fa-check-circle" style="color:var(--ndb-orange)"></i>
                            <span class="pill-val" id="kpi_ndb_activas">&#8212;</span>
                            <span>Activas</span>
                        </div>
                        <div class="rndb-stat-pill">
                            <i class="fa fa-ban" style="color:#6b7280"></i>
                            <span class="pill-val" id="kpi_ndb_anuladas">&#8212;</span>
                            <span>Anuladas</span>
                        </div>
                    </div>

                    {{-- ── Barra filtros activos ── --}}
                    <div class="filtros-bar-ndb" id="ndb_filtros_bar" style="display:none;"></div>

                    {{-- ── Tabla ── --}}
                    <div class="rndb-card-body">
                        <div style="overflow-x:auto;">
                            <table id="tbl_nd_b" class="table table-hover table-bordered" style="width:100%;">
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

    {{-- ══ Modal Filtros NDB ══ --}}
    <div class="modal fade" id="modalFiltrosNDB" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-ndb">
                    <h5 class="modal-title"><i class="fa fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body pb-2">

                    <p class="modal-section-label-ndb"><i class="fa fa-calendar"></i>Rango de fechas</p>
                    <div class="modal-filter-grid-ndb">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="ndb_fil_fecha_desde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="ndb_fil_fecha_hasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="modal-section-label-ndb"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                    <div class="modal-filter-grid-ndb">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select id="ndb_fil_cliente" class="form-control" style="width:100%">
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
                                    <select id="ndb_fil_estado" class="form-control form-control-sm">
                                        <option value="">— Todos —</option>
                                        <option value="1">Activo</option>
                                        <option value="2">Anulado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Registrado por</label>
                                    <select id="ndb_fil_usuario" class="form-control" style="width:100%">
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
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ndbLimpiarFiltros()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar filtros
                    </button>
                    <button type="button" class="btn btn-sm" onclick="ndbAplicarFiltros()"
                        style="background:linear-gradient(135deg,#f39c12,#e05a00);color:#fff;border:none;font-weight:600;padding:6px 20px;border-radius:5px">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/nota-debito/listado-notas-debito.js') }}"></script>
    <script>
    var ndbTable = null;
    var ndbFiltros = {
        fecha_desde: '{{ $fechaInicio }}',
        fecha_hasta: '{{ date("Y-m-t") }}',
        cliente_id:  '',
        estado_id:   '',
        user_id:     ''
    };

    $(document).ready(function() {
        $('#ndb_fil_cliente').select2({ dropdownParent: $('#modalFiltrosNDB'), placeholder: '— Todos —', allowClear: true });
        $('#ndb_fil_usuario').select2({ dropdownParent: $('#modalFiltrosNDB'), placeholder: '— Todos —', allowClear: true });
        $('#ndb_fil_fecha_desde').val('{{ $fechaInicio }}');
        $('#ndb_fil_fecha_hasta').val('{{ date("Y-m-t") }}');
        ndbCargarTabla();
    });

    function ndbCargarTabla() {
        if (ndbTable) { ndbTable.destroy(); }
        ndbTable = $('#tbl_nd_b').DataTable({
            order: [[0, 'desc']],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            pageLength: 25,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            ajax: {
                url: '/listado/nota/debito/corporativo/' + ndbFiltros.fecha_desde + '/' + ndbFiltros.fecha_hasta,
                data: {
                    cliente_id: ndbFiltros.cliente_id,
                    estado_id:  ndbFiltros.estado_id,
                    user_id:    ndbFiltros.user_id
                }
            },
            columns: [
                { data: 'id' },
                { data: 'correlativoND' },
                { data: 'monto_asignado', className: 'text-right' },
                { data: 'cai' },
                { data: 'cliente' },
                { data: 'fechaEmision' },
                { data: 'user' },
                { data: 'estado',   orderable: false },
                { data: 'file',     orderable: false },
                { data: 'created_at' },
                { data: 'acciones', orderable: false }
            ],
            initComplete: function() { ndbActualizarKpis(); }
        });
    }

    function ndbAplicarFiltros() {
        ndbFiltros.fecha_desde = $('#ndb_fil_fecha_desde').val() || '{{ $fechaInicio }}';
        ndbFiltros.fecha_hasta = $('#ndb_fil_fecha_hasta').val() || '{{ date("Y-m-t") }}';
        ndbFiltros.cliente_id  = $('#ndb_fil_cliente').val()  || '';
        ndbFiltros.estado_id   = $('#ndb_fil_estado').val()   || '';
        ndbFiltros.user_id     = $('#ndb_fil_usuario').val()  || '';
        $('#modalFiltrosNDB').modal('hide');
        ndbCargarTabla();
        ndbActualizarBadges();
    }

    function ndbLimpiarFiltros() {
        $('#ndb_fil_fecha_desde').val('{{ $fechaInicio }}');
        $('#ndb_fil_fecha_hasta').val('{{ date("Y-m-t") }}');
        $('#ndb_fil_cliente').val(null).trigger('change');
        $('#ndb_fil_estado').val('');
        $('#ndb_fil_usuario').val(null).trigger('change');
        ndbFiltros = { fecha_desde: '{{ $fechaInicio }}', fecha_hasta: '{{ date("Y-m-t") }}', cliente_id: '', estado_id: '', user_id: '' };
        ndbActualizarBadges();
    }

    function ndbActualizarKpis() {
        $.ajax({
            url: '/nota/debito/kpis', type: 'POST',
            data: {
                _token:      $('meta[name="csrf-token"]').attr('content'),
                fechaInicio: ndbFiltros.fecha_desde,
                fechaFinal:  ndbFiltros.fecha_hasta,
                cliente_id:  ndbFiltros.cliente_id,
                estado_id:   ndbFiltros.estado_id,
                user_id:     ndbFiltros.user_id,
                tipo_cliente: 1
            },
            success: function(r) {
                if (!r.success) return;
                $('#kpi_ndb_total').text(r.total + ' notas');
                $('#kpi_ndb_monto').text(ndbFmt(r.monto_total));
                $('#kpi_ndb_activas').text(r.activas);
                $('#kpi_ndb_anuladas').text(r.anuladas);
            }
        });
    }

    function ndbActualizarBadges() {
        var bar = $('#ndb_filtros_bar');
        bar.empty();
        var badges = [];
        if (ndbFiltros.fecha_desde) badges.push({ lbl:'Desde', val:ndbFiltros.fecha_desde });
        if (ndbFiltros.fecha_hasta) badges.push({ lbl:'Hasta', val:ndbFiltros.fecha_hasta });
        if (ndbFiltros.cliente_id)  badges.push({ lbl:'Cliente', val:$('#ndb_fil_cliente option:selected').text() });
        if (ndbFiltros.estado_id)   badges.push({ lbl:'Estado',  val:$('#ndb_fil_estado option:selected').text() });
        if (ndbFiltros.user_id)     badges.push({ lbl:'Usuario', val:$('#ndb_fil_usuario option:selected').text() });
        if (badges.length) {
            bar.show();
            badges.forEach(function(b) { bar.append('<span class="filtro-badge-ndb"><strong>' + b.lbl + ':</strong> ' + b.val + '</span> '); });
        } else { bar.hide(); }
    }

    function exportarExcelNDB() {
        var tok  = $('meta[name="csrf-token"]').attr('content');
        var form = $('<form method="POST" action="/nota/debito/exportar-excel"></form>');
        var f = { _token: tok, fechaInicio: ndbFiltros.fecha_desde, fechaFinal: ndbFiltros.fecha_hasta,
                  cliente_id: ndbFiltros.cliente_id, estado_id: ndbFiltros.estado_id,
                  user_id: ndbFiltros.user_id, tipo_cliente: 1 };
        $.each(f, function(k,v){ form.append($('<input type="hidden">').attr('name',k).val(v)); });
        $('body').append(form); form.submit(); form.remove();
    }

    function ndbFmt(v) {
        if (v === null || v === undefined) return '—';
        return 'L ' + parseFloat(v).toLocaleString('es-HN', { minimumFractionDigits:2, maximumFractionDigits:2 });
    }
    </script>
    @endpush
</div>

