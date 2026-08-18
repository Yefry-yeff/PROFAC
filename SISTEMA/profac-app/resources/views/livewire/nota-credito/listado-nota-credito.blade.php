<div>
    {{-- ════════════════════════════════════════════════════════════════
         LISTADO NOTAS DE CRÉDITO — CLIENTES A (Corporativo/Privado)
         ════════════════════════════════════════════════════════════════ --}}

    @push('styles')
    <style>
    :root { --nc-grad: linear-gradient(135deg,#f39c12 0%,#e05a00 100%); --nc-red:#e67e22; --nc-radius:8px; --nc-shadow:0 2px 8px rgba(0,0,0,.10); }
    .rnc-card { border:1px solid #e8d5bf; border-radius:var(--nc-radius); box-shadow:var(--nc-shadow); background:#fff; overflow:visible; }
    .rnc-card-header { background:var(--nc-grad); padding:12px 20px; border-radius:var(--nc-radius) var(--nc-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .rnc-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .rnc-card-body { padding:16px 20px; }
    .btn-rnc-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-rnc-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    .rnc-stats { display:flex; gap:10px; flex-wrap:wrap; padding:10px 20px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
    .rnc-stat-pill { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf; border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500; }
    .rnc-stat-pill .pill-val { font-size:.9rem; font-weight:700; color:var(--nc-red); }
    .rnc-stat-pill.green { background:#f0fdf4; border-color:#bbf7d0; }
    .rnc-stat-pill.green .pill-val { color:#1a7a4e; }
    .rnc-stat-pill .pill-sub { font-size:.70rem; color:#9ca3af; margin-left:2px; }
    .filtros-bar-nc { padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:center; gap:6px; font-size:.78rem; }
    .filtro-badge-nc { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
    #tbl_nc_a { width:100%!important; }
    #tbl_nc_a thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_nc_a tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_nc_a tbody tr:hover>td { background:#fffcf5; }
    .modal-header-nc { background:var(--nc-grad); color:#fff; border-radius:var(--nc-radius) var(--nc-radius) 0 0; padding:14px 20px; }
    .modal-header-nc .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-nc .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-nc .close:hover { opacity:1; }
    .modal-section-label-nc { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
    #modalFiltrosNC .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
    #modalFiltrosNC .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    #modalFiltrosNC .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
    #modalFiltrosNC .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    #modalFiltrosNC .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .modal-filter-grid-nc { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
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
            <h2><i class="fa fa-file-text-o mr-2" style="color:#e67e22"></i>Notas de Crédito &mdash; Clientes A</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Notas de Crédito</li>
                <li class="breadcrumb-item active"><strong>Clientes A</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="rnc-card">

                    {{-- ── Header gradiente rojo ── --}}
                    <div class="rnc-card-header">
                        <h5><i class="fa fa-file-text-o"></i> Listado de Notas de Crédito — Clientes A</h5>
                        <div class="d-flex" style="gap:8px">
                            <button type="button" class="btn-rnc-action" onclick="exportarExcelNC()">
                                <i class="fa fa-file-excel-o mr-1"></i>Excel
                            </button>
                            <button type="button" class="btn-rnc-action" data-toggle="modal" data-target="#modalFiltrosNC">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>

                    {{-- ── KPI pills ── --}}
                    <div class="rnc-stats">
                        <div class="rnc-stat-pill">
                            <i class="fa fa-file-text-o" style="color:var(--nc-red)"></i>
                            <span class="pill-val" id="kpi_nc_total">&#8212;</span>
                            <span>Notas</span>
                        </div>
                        <div class="rnc-stat-pill">
                            <i class="fa fa-minus-circle" style="color:var(--nc-red)"></i>
                            <span class="pill-val" id="kpi_nc_subtotal">&#8212;</span>
                            <span>Sub Total</span>
                        </div>
                        <div class="rnc-stat-pill">
                            <i class="fa fa-percent" style="color:#7d3900"></i>
                            <span class="pill-val" id="kpi_nc_isv">&#8212;</span>
                            <span>ISV</span>
                        </div>
                        <div class="rnc-stat-pill green">
                            <i class="fa fa-money" style="color:#1a7a4e"></i>
                            <span class="pill-val" id="kpi_nc_monto">&#8212;</span>
                            <span>Total</span>
                        </div>
                    </div>

                    {{-- ── Barra filtros activos ── --}}
                    <div class="filtros-bar-nc" id="nc_filtros_bar" style="display:none;"></div>

                    {{-- ── Tabla ── --}}
                    <div class="rnc-card-body">
                        <div style="overflow-x:auto;">
                            <table id="tbl_nc_a" class="table table-hover table-bordered" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Registro N°</th>
                                        <th>Cliente</th>
                                        <th>N° Factura</th>
                                        <th>Motivo</th>
                                        <th>Comentario</th>
                                        <th class="text-right">Sub Total</th>
                                        <th class="text-right">ISV</th>
                                        <th class="text-right">Total fiscal</th>
                                        <th class="text-right">Aplicado</th>
                                        <th class="text-right">Reembolsado</th>
                                        <th class="text-right">Disponible</th>
                                        <th>Estado del crédito</th>
                                        <th>Fecha</th>
                                        <th>Registrado por</th>
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

    {{-- ══ Modal Filtros NC-A ══ --}}
    <div class="modal fade" id="modalFiltrosNC" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-nc">
                    <h5 class="modal-title"><i class="fa fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body pb-2">

                    <p class="modal-section-label-nc"><i class="fa fa-calendar"></i>Rango de fechas</p>
                    <div class="modal-filter-grid-nc">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="nc_fil_fecha_desde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="nc_fil_fecha_hasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="modal-section-label-nc"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                    <div class="modal-filter-grid-nc">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select id="nc_fil_cliente" class="form-control" style="width:100%">
                                        <option value=""></option>
                                        @foreach($clientes as $cl)
                                            <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Motivo</label>
                                    <select id="nc_fil_motivo" class="form-control form-control-sm">
                                        <option value="">— Todos —</option>
                                        @foreach($motivos as $mot)
                                            <option value="{{ $mot->id }}">{{ $mot->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Registrado por</label>
                                    <select id="nc_fil_usuario" class="form-control" style="width:100%">
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
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ncLimpiarFiltros()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar filtros
                    </button>
                    <button type="button" class="btn btn-sm" onclick="ncAplicarFiltros()"
                        style="background:linear-gradient(135deg,#f39c12,#e05a00);color:#fff;border:none;font-weight:600;padding:6px 20px;border-radius:5px">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
    <script src="{{ asset('js/js_proyecto/nota-credito/listado-nota-credito.js') }}"></script>
    <script>
    var ncTable = null;
    var ncFiltros = {
        fecha_desde: '{{ $fechaInicio }}',
        fecha_hasta: '{{ date("Y-m-t") }}',
        cliente_id:  '',
        motivo_id:   '',
        user_id:     ''
    };

    $(document).ready(function() {
        $('#nc_fil_cliente').select2({ dropdownParent: $('#modalFiltrosNC'), placeholder: '— Todos —', allowClear: true });
        $('#nc_fil_usuario').select2({ dropdownParent: $('#modalFiltrosNC'), placeholder: '— Todos —', allowClear: true });
        $('#nc_fil_fecha_desde').val('{{ $fechaInicio }}');
        $('#nc_fil_fecha_hasta').val('{{ date("Y-m-t") }}');
        ncCargarTabla();
    });

    function ncCargarTabla() {
        if (ncTable) { ncTable.destroy(); }
        ncTable = $('#tbl_nc_a').DataTable({
            order: [[0, 'desc']],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            pageLength: 25,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            ajax: {
                url: '/nota/credito/listar', type: 'POST',
                data: function(d) {
                    d._token      = $('meta[name="csrf-token"]').attr('content');
                    d.fechaInicio = ncFiltros.fecha_desde;
                    d.fechaFinal  = ncFiltros.fecha_hasta;
                    d.cliente_id  = ncFiltros.cliente_id;
                    d.motivo_id   = ncFiltros.motivo_id;
                    d.user_id     = ncFiltros.user_id;
                }
            },
            columns: [
                { data: 'codigo' },
                { data: 'cai' },
                { data: 'cliente' },
                { data: 'factura' },
                { data: 'motivo' },
                { data: 'comentario' },
                { data: 'sub_total',    className: 'text-right' },
                { data: 'isv',          className: 'text-right' },
                { data: 'total',        className: 'text-right' },
                { data: 'monto_aplicado', className: 'text-right' },
                { data: 'monto_reembolsado', className: 'text-right' },
                { data: 'saldo_disponible', className: 'text-right' },
                { data: 'estado_credito' },
                { data: 'fecha_registro' },
                { data: 'registrado_por' },
                { data: 'opciones', orderable: false, searchable: false }
            ],
            initComplete: function() { ncActualizarKpis(); }
        });
    }

    function ncAplicarFiltros() {
        ncFiltros.fecha_desde = $('#nc_fil_fecha_desde').val() || '{{ $fechaInicio }}';
        ncFiltros.fecha_hasta = $('#nc_fil_fecha_hasta').val() || '{{ date("Y-m-t") }}';
        ncFiltros.cliente_id  = $('#nc_fil_cliente').val()  || '';
        ncFiltros.motivo_id   = $('#nc_fil_motivo').val()   || '';
        ncFiltros.user_id     = $('#nc_fil_usuario').val()  || '';
        $('#modalFiltrosNC').modal('hide');
        ncCargarTabla();
        ncActualizarBadges();
    }

    function ncLimpiarFiltros() {
        $('#nc_fil_fecha_desde').val('{{ $fechaInicio }}');
        $('#nc_fil_fecha_hasta').val('{{ date("Y-m-t") }}');
        $('#nc_fil_cliente').val(null).trigger('change');
        $('#nc_fil_motivo').val('');
        $('#nc_fil_usuario').val(null).trigger('change');
        ncFiltros = { fecha_desde: '{{ $fechaInicio }}', fecha_hasta: '{{ date("Y-m-t") }}', cliente_id: '', motivo_id: '', user_id: '' };
        ncActualizarBadges();
    }

    function ncActualizarKpis() {
        $.ajax({
            url: '/nota/credito/kpis', type: 'POST',
            data: {
                _token:      $('meta[name="csrf-token"]').attr('content'),
                fechaInicio: ncFiltros.fecha_desde,
                fechaFinal:  ncFiltros.fecha_hasta,
                cliente_id:  ncFiltros.cliente_id,
                motivo_id:   ncFiltros.motivo_id,
                user_id:     ncFiltros.user_id,
                tipo_venta:  2
            },
            success: function(r) {
                if (!r.success) return;
                $('#kpi_nc_total').text(r.total + ' notas');
                $('#kpi_nc_subtotal').text(ncFmt(r.sub_total));
                $('#kpi_nc_isv').text(ncFmt(r.isv));
                $('#kpi_nc_monto').text(ncFmt(r.total_monto));
            }
        });
    }

    function ncActualizarBadges() {
        var bar = $('#nc_filtros_bar');
        bar.empty();
        var badges = [];
        if (ncFiltros.fecha_desde) badges.push({ lbl:'Desde', val:ncFiltros.fecha_desde });
        if (ncFiltros.fecha_hasta) badges.push({ lbl:'Hasta', val:ncFiltros.fecha_hasta });
        if (ncFiltros.cliente_id)  badges.push({ lbl:'Cliente', val:$('#nc_fil_cliente option:selected').text() });
        if (ncFiltros.motivo_id)   badges.push({ lbl:'Motivo',  val:$('#nc_fil_motivo option:selected').text() });
        if (ncFiltros.user_id)     badges.push({ lbl:'Usuario', val:$('#nc_fil_usuario option:selected').text() });
        if (badges.length) {
            bar.show();
            badges.forEach(function(b) { bar.append('<span class="filtro-badge-nc"><strong>' + b.lbl + ':</strong> ' + b.val + '</span> '); });
        } else { bar.hide(); }
    }

    function exportarExcelNC() {
        var tok  = $('meta[name="csrf-token"]').attr('content');
        var form = $('<form method="POST" action="/nota/credito/exportar-excel"></form>');
        var f = { _token: tok, fechaInicio: ncFiltros.fecha_desde, fechaFinal: ncFiltros.fecha_hasta,
                  cliente_id: ncFiltros.cliente_id, motivo_id: ncFiltros.motivo_id, user_id: ncFiltros.user_id };
        $.each(f, function(k,v){ form.append($('<input type="hidden">').attr('name',k).val(v)); });
        $('body').append(form); form.submit(); form.remove();
    }

    function ncFmt(v) {
        if (v === null || v === undefined) return '—';
        return 'L ' + parseFloat(v).toLocaleString('es-HN', { minimumFractionDigits:2, maximumFractionDigits:2 });
    }
    </script>
    @endpush
</div>
