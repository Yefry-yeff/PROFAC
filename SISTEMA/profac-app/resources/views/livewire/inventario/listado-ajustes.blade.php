<div>
    @push('styles')
    <style>
    /* ── Variables PROFAC ── */
    :root {
        --pf-grad:     linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --pf-orange:   #e67e22;
        --pf-radius:   8px;
        --pf-shadow:   0 2px 8px rgba(0,0,0,.10);
    }
    .aj-card { border:1px solid #e8d5bf; border-radius:var(--pf-radius); box-shadow:var(--pf-shadow); background:#fff; overflow:visible; }
    .aj-card-header { background:var(--pf-grad); padding:12px 20px; border-radius:var(--pf-radius) var(--pf-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .aj-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .aj-card-body { padding:16px 20px; }
    .btn-aj-header { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-aj-header:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    .aj-stats { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
    .aj-stat-pill { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf; border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500; }
    .aj-stat-pill .stat-num { font-size:.9rem; font-weight:700; color:var(--pf-orange); }
    .aj-stat-pill.green { background:#f0fdf4; border-color:#bbf7d0; } .aj-stat-pill.green .stat-num { color:#1a7a4e; }
    .aj-stat-pill.red   { background:#fef2f2; border-color:#fecaca; } .aj-stat-pill.red   .stat-num { color:#b91c1c; }
    .filtros-bar { padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:center; gap:6px; font-size:.78rem; }
    .filtro-badge { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
    #tbl_listar_ajustes { width:100%!important; }
    #tbl_listar_ajustes thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_listar_ajustes tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_listar_ajustes tbody tr:hover { background:#fffcf5; }
    .badge-activo  { background:#dcfce7; color:#14532d; border:1px solid #86efac; font-weight:600; font-size:.75rem; padding:3px 10px; border-radius:12px; white-space:nowrap; display:inline-block; }
    .badge-anulado { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-weight:600; font-size:.75rem; padding:3px 10px; border-radius:12px; white-space:nowrap; display:inline-block; }
    .aj-dropdown { position:relative; display:inline-block; }
    .btn-aj-menu { display:inline-flex; align-items:center; justify-content:center; padding:4px 10px; height:30px; background:#fff; border:1.5px solid #e0cbb0; border-radius:7px; color:#c0622a; font-size:.80rem; font-weight:600; cursor:pointer; transition:background .15s,border-color .15s,box-shadow .15s; box-shadow:0 1px 3px rgba(0,0,0,.08); white-space:nowrap; }
    .btn-aj-menu:hover,.btn-aj-menu:focus { background:#fff8f0; border-color:#e67e22; box-shadow:0 2px 6px rgba(230,126,34,.25); outline:none; }
    .aj-dropdown .dropdown-menu { min-width:165px; border:1px solid #f0e0cc; border-radius:8px; padding:4px 0; font-size:.83rem; box-shadow:0 4px 16px rgba(0,0,0,.13)!important; }
    .aj-dropdown .dropdown-item { padding:7px 14px; font-weight:500; transition:background .12s; }
    .aj-dropdown .dropdown-item:hover { background:#fff8f0; color:#c0622a; }
    .aj-dropdown .dropdown-item.text-danger:hover { background:#fff0f0; }
    .aj-dropdown .dropdown-item i { opacity:.85; width:16px; }
    .modal-header-aj { background:var(--pf-grad); color:#fff; border-radius:var(--pf-radius) var(--pf-radius) 0 0; padding:14px 20px; }
    .modal-header-aj .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-aj .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-aj .close:hover { opacity:1; }
    .modal-header-danger { background:#dc3545; color:#fff; border-radius:var(--pf-radius) var(--pf-radius) 0 0; padding:14px 20px; }
    .modal-header-danger .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-danger .close { color:#fff; opacity:.8; text-shadow:none; }
    .modal-section-label { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
    .modal-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
    #modalFiltrosAj .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
    #modalFiltrosAj .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    #modalFiltrosAj .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
    #modalFiltrosAj .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    #modalFiltrosAj .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .date-input-icon { position:relative; }
    .date-input-icon i { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
    .date-input-icon input { padding-left:28px; }
    .tipo-filter-btn { font-size:.78rem; font-weight:600; padding:5px 16px; border-radius:20px!important; border:1.5px solid #dee2e6; background:#fff; color:#555; transition:all .15s; cursor:pointer; outline:none; }
    .tipo-filter-btn.active { background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%)!important; color:#fff!important; border-color:transparent!important; box-shadow:0 2px 6px rgba(230,126,34,.3)!important; }
    .tipo-filter-btn:hover:not(.active) { background:#fff8f0; border-color:#e67e22; color:#c0622a; }
    #page-wrapper { padding-left:0!important; padding-right:0!important; }
    .wrapper-content { padding-left:0!important; padding-right:0!important; }
    .wrapper-content>.row { margin-left:0!important; margin-right:0!important; }
    .wrapper-content>.row>[class*="col-"] { padding-left:0!important; padding-right:0!important; }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa-solid fa-sliders mr-2" style="color:#e67e22"></i>Historial de Ajustes</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Ajustes de Inventario</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12">
                <div class="aj-card">

                    {{-- Header --}}
                    <div class="aj-card-header">
                        <h5><i class="fa-solid fa-sliders"></i> Ajustes de Inventario</h5>
                        <button type="button" class="btn-aj-header" data-toggle="modal" data-target="#modalFiltrosAj">
                            <i class="fa fa-filter mr-1"></i> Filtros
                        </button>
                    </div>

                    {{-- Barra de filtros activos --}}
                    <div class="filtros-bar" id="filtrosBar" style="display:none">
                        <span class="text-muted mr-1" style="font-size:.75rem"><i class="fa fa-filter mr-1"></i>Filtros:</span>
                        <span class="filtro-badge" id="filtroFechasBadge"></span>
                    </div>

                    {{-- Placeholder --}}
                    <div id="aj-placeholder" class="text-center py-5" style="color:#aaa">
                        <i class="fa fa-filter" style="font-size:2.5rem;color:#e67e22;opacity:.45"></i>
                        <p class="mt-3 mb-0" style="font-size:1rem;font-weight:600">Aplique filtros para cargar los resultados</p>
                        <p class="small">Haga clic en <strong>Filtros</strong> para seleccionar el rango de fechas.</p>
                    </div>

                    {{-- Tabla --}}
                    <div class="aj-card-body" id="aj-table-wrapper" style="display:none">
                        <div class="aj-stats mb-3">
                            <div class="aj-stat-pill">
                                <i class="fa-solid fa-sliders" style="font-size:.78rem;color:var(--pf-orange)"></i>
                                <span>Total</span><span class="stat-num" id="statTotal">—</span>
                            </div>
                            <div class="aj-stat-pill green">
                                <i class="fa fa-check-circle" style="font-size:.78rem;color:#1a7a4e"></i>
                                <span>Activos</span><span class="stat-num" id="statActivos">—</span>
                            </div>
                            <div class="aj-stat-pill red">
                                <i class="fa fa-ban" style="font-size:.78rem;color:#b91c1c"></i>
                                <span>Anulados</span><span class="stat-num" id="statAnulados">—</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tbl_listar_ajustes" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:55px">Código</th>
                                        <th style="width:95px">Registro N°</th>
                                        <th>Comentario</th>
                                        <th style="width:130px">Motivo</th>
                                        <th style="width:95px">Fecha</th>
                                        <th style="width:130px">Registrado por</th>
                                        <th style="width:115px">Fecha registro</th>
                                        <th style="width:90px" class="text-center">Estado</th>
                                        <th style="width:80px" class="text-center">Opciones</th>
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

    {{-- ===== MODAL: Filtros ===== --}}
    <div class="modal fade" id="modalFiltrosAj" tabindex="-1" role="dialog" aria-labelledby="tituloFiltrosAj" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-aj">
                    <h5 class="modal-title" id="tituloFiltrosAj"><i class="fa fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body pb-2">
                    <p class="modal-section-label"><i class="fa fa-calendar"></i> Rango de fechas</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde <span class="text-danger">*</span></label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="filtroDesde" value="{{ $fechaInicio }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta <span class="text-danger">*</span></label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="filtroHasta" value="{{ date('Y-m-t') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="modal-section-label"><i class="fa fa-tag"></i> Estado</p>
                    <div class="modal-filter-grid">
                        <div class="d-flex flex-wrap" style="gap:8px">
                            <button type="button" class="tipo-filter-btn active" data-estado="todos">
                                <i class="fa fa-list mr-1" style="font-size:.65rem"></i> Todos
                            </button>
                            <button type="button" class="tipo-filter-btn" data-estado="activos">
                                <i class="fa fa-check-circle mr-1" style="font-size:.65rem"></i> Activos
                            </button>
                            <button type="button" class="tipo-filter-btn" data-estado="anulados">
                                <i class="fa fa-ban mr-1" style="font-size:.65rem"></i> Anulados
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f4ef;border-top:1px solid #ead9c8;padding:10px 20px">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="aplicarFiltros()">
                        <i class="fa fa-search mr-1"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        var fechaInicio  = "{{ $fechaInicio }}";
        var fechaFinal   = "{{ date('Y-m-t') }}";
        var filtroEstado = 'todos';
        var tablaAjustes = null;

        $(document).ready(function () {
            // Botones de estado en modal filtros
            $(document).on('click', '.tipo-filter-btn', function () {
                $('.tipo-filter-btn').removeClass('active');
                $(this).addClass('active');
                filtroEstado = $(this).data('estado');
            });

            // Cargar automáticamente con el rango del último mes
            aplicarFiltros();
        });

        function aplicarFiltros() {
            let desde = $('#filtroDesde').val();
            let hasta = $('#filtroHasta').val();
            if (!desde || !hasta) {
                Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Debe seleccionar la fecha de inicio y la fecha final.' });
                return;
            }
            if (desde > hasta) {
                Swal.fire({ icon: 'warning', title: 'Rango inválido', text: 'La fecha de inicio no puede ser mayor que la fecha final.' });
                return;
            }
            fechaInicio = desde;
            fechaFinal  = hasta;
            $('#modalFiltrosAj').modal('hide');
            $('#filtrosBar').show();
            let estadoLabel = filtroEstado !== 'todos'
                ? ' &nbsp;|&nbsp; <i class="fa fa-tag mr-1"></i>' + filtroEstado.charAt(0).toUpperCase() + filtroEstado.slice(1)
                : '';
            $('#filtroFechasBadge').html('<i class="fa fa-calendar-o mr-1"></i>' + desde + ' → ' + hasta + estadoLabel);
            cargarTabla();
        }

        function cargarTabla() {
            document.getElementById('aj-placeholder').style.display = 'none';
            document.getElementById('aj-table-wrapper').style.display = 'block';
            if (tablaAjustes) { tablaAjustes.destroy(); tablaAjustes = null; $('#tbl_listar_ajustes tbody').empty(); }

            tablaAjustes = $('#tbl_listar_ajustes').DataTable({
                order: [[1, 'desc']],
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
                pageLength: 15,
                responsive: true,
                ajax: {
                    url: '/obtener/listado/ajustes',
                    data: { fechaInicio: fechaInicio, fechaFinal: fechaFinal, _token: "{{ csrf_token() }}" },
                    type: 'POST',
                    dataSrc: function (json) {
                        let data = json.data;
                        if (filtroEstado === 'activos')  data = data.filter(r => parseInt(r.anulado) === 0);
                        if (filtroEstado === 'anulados') data = data.filter(r => parseInt(r.anulado) === 1);
                        let total    = json.data.length;
                        let activos  = json.data.filter(r => parseInt(r.anulado) === 0).length;
                        let anulados = json.data.filter(r => parseInt(r.anulado) === 1).length;
                        $('#statTotal').text(total);
                        $('#statActivos').text(activos);
                        $('#statAnulados').text(anulados);
                        return data;
                    }
                },
                columns: [
                    { data: 'codigo',       className: 'text-center' },
                    { data: 'numero_ajuste' },
                    { data: 'comentario' },
                    { data: 'motivo' },
                    { data: 'fecha',        className: 'text-center' },
                    { data: 'name' },
                    { data: 'created_at',   className: 'text-center' },
                    { data: 'estado',       className: 'text-center', orderable: false },
                    { data: 'opciones',     className: 'text-center', orderable: false },
                ]
            });
        }

        function confirmarAnularAjuste(idAjuste, numeroAjuste) {
            Swal.fire({
                title: 'Anular Ajuste',
                html:
                    '<div class="alert alert-warning text-left mb-3" style="font-size:.85rem">' +
                    '<i class="fa fa-exclamation-triangle mr-1"></i> ' +
                    'Esta acción <strong>revertirá todos los movimientos de inventario</strong> del ajuste <strong>' + numeroAjuste + '</strong>. No se puede deshacer.' +
                    '</div>',
                input: 'textarea',
                inputLabel: 'Motivo de anulación',
                inputPlaceholder: 'Describa el motivo de la anulación…',
                inputAttributes: {
                    'aria-label': 'Motivo de anulación',
                    'rows': 4
                },
                inputValidator: function (value) {
                    if (!value || !value.trim()) {
                        return 'El motivo de anulación es obligatorio.';
                    }
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-ban mr-1"></i> Confirmar Anulación',
                cancelButtonText: 'Cancelar',
                focusConfirm: false,
                preConfirm: function (motivo) {
                    return $.ajax({
                        url: '/ajuste/anular',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            idAjuste: idAjuste,
                            motivo: motivo.trim()
                        }
                    }).catch(function (xhr) {
                        let r = xhr.responseJSON || {};
                        Swal.showValidationMessage(r.text || 'Ha ocurrido un error al anular el ajuste.');
                    });
                },
                didOpen: function () {
                    const input = Swal.getInput();
                    if (input) {
                        input.style.resize = 'none';
                        input.style.fontSize = '.85rem';
                    }
                }
            }).then(function (result) {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        icon: result.value.icon,
                        title: result.value.title,
                        html: result.value.text
                    }).then(function () {
                        if (result.value.icon === 'success') {
                            cargarTabla();
                        }
                    });
                }
            });
        }
    </script>
    @endpush
</div>
