<div class="transfer-history">
    @push('styles')
    <style>
        .transfer-history { color: #334155; }
        .transfer-history .page-heading {
            margin: 0;
            padding: 24px 20px;
            background: linear-gradient(135deg, #f39c12, #e05a00);
            border: 0 !important;
        }
        .transfer-history .page-heading h2 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 8px;
        }
        .transfer-history .breadcrumb { background: transparent; color: #fff; padding: 0; margin: 0; }
        .transfer-history .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.65); }
        .transfer-history .history-main-tabs {
            margin: 20px 20px 0;
            padding: 6px;
            gap: 6px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 3px 14px rgba(15,23,42,.05);
        }
        .transfer-history .nav-tabs { border-bottom: 0; gap: 6px; }
        .transfer-history .nav-tabs .nav-link {
            color: #64748b;
            border: 0;
            border-radius: 8px;
            padding: 11px 16px;
            font-weight: 600;
        }
        .transfer-history .nav-tabs .nav-link:hover { color: #b45309; background: #fff7ed; }
        .transfer-history .nav-tabs .nav-link.active { background: #fff1df; color: #b45309; }
        .transfer-history .history-main-tabs .nav-link.active { background: linear-gradient(135deg, #f39c12, #e05a00); color: #fff; }
        .transfer-history .wrapper-content { padding: 18px 20px 0; }
        .transfer-history .ibox {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 3px 16px rgba(15,23,42,.06);
            overflow: hidden;
            margin-bottom: 0;
        }
        .transfer-history .ibox-title {
            background: #fff7ed;
            padding: 14px 18px;
            border: 0;
            border-bottom: 1px solid #ffedd5;
            min-height: auto;
        }
        .transfer-history .ibox-title h3 { color: #9a480d; font-size: 1rem; font-weight: 700; margin: 0; }
        .transfer-history .ibox-content { padding: 18px; border: 0; }
        .transfer-history label { color: #64748b; font-size: .85rem; font-weight: 600; }
        .transfer-history .form-control { border-radius: 8px; border-color: #dbe3ec; min-height: 38px; }
        .transfer-history .form-control:focus { border-color: #f39c12; box-shadow: 0 0 0 3px rgba(243,156,18,.12); }
        .transfer-history .btn { border-radius: 8px; font-weight: 600; }
        .transfer-history .btn-primary { background: #e98008; border-color: #e98008; }
        .transfer-history .btn-primary:hover { background: #cd6b00; border-color: #cd6b00; }
        .transfer-history .btn-warning { color: #a64b00; background: #fff1df; border-color: #ffd6a6; }
        .transfer-history .btn-warning:hover { background: #ffe2ba; border-color: #f39c12; }
        .transfer-history .table { border-color: #e2e8f0; margin-bottom: 12px; }
        .transfer-history .table thead th {
            color: #a64b00;
            background: #fff7ed;
            border-color: #ffedd5;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .3px;
            padding: 12px 10px;
            white-space: nowrap;
        }
        .transfer-history .table tbody td { border-color: #edf2f7; padding: 11px 10px; vertical-align: middle; }
        .transfer-history .table-striped tbody tr:nth-of-type(odd) { background: #fafbfc; }
        .transfer-history .table tbody tr:hover { background: #fffaf2; }
        .transfer-history .table tbody td:last-child .btn { white-space: nowrap; }
        .transfer-history .dataTables_wrapper .dataTables_filter input { border: 1px solid #dbe3ec; border-radius: 8px; min-height: 34px; }
        .transfer-history .dataTables_wrapper .dataTables_info { color: #64748b; }
        .transfer-history .pagination .page-link { border-color: #e2e8f0; color: #64748b; }
        .transfer-history .pagination .active .page-link { background: #e98008; border-color: #e98008; color: #fff; }
        .transfer-history .history-note {
            background: #f8fafc;
            border-left: 3px solid #f39c12;
            border-radius: 8px;
            padding: 12px 14px;
            color: #64748b;
        }
        .transfer-history .history-status { display: inline-block; border-radius: 20px; padding: 5px 11px; font-size: .8rem; font-weight: 600; white-space: nowrap; }
        .transfer-history .history-status-pending { background: #fff3cd; color: #856404; }
        .transfer-history .history-status-dispatched { background: #e0f2fe; color: #0369a1; }
        .transfer-history .history-status-received { background: #dcfce7; color: #166534; }
        .transfer-history .history-status-rejected { background: #fee2e2; color: #b91c1c; }
        .transfer-history .history-status-returned { background: #f1eafe; color: #6b21a8; }
        .transfer-history .history-status-other { background: #f1f5f9; color: #475569; }
        @media (max-width: 767px) {
            .transfer-history .page-heading { padding: 18px 8px; }
            .transfer-history .page-heading h2 { font-size: 1.25rem; }
            .transfer-history .history-main-tabs { margin: 14px 12px 0; }
            .transfer-history .history-main-tabs .nav-item { width: 100%; }
            .transfer-history .wrapper-content { padding: 14px 12px 0; }
            .transfer-history .ibox-content { padding: 14px; }
            .transfer-history .nav-tabs .nav-link { padding: 10px 12px; }
        }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fa fa-exchange mr-2"></i> Historial de Traslados de Bodega</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            Inventario
                        </li>
                        <li class="breadcrumb-item">Traslados y movimientos entre bodegas</li>
                    </ol>
                </div>


            </div>


        </div>
    </div>

    @php($mostrarPaperland = config('pos_integration.enabled') && auth()->user()->tienePermiso('translados/historial'))
    <ul class="nav nav-tabs history-main-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="historial-normal-link" data-toggle="tab" href="#historial-normal"
               role="tab" aria-controls="historial-normal" aria-selected="true"><i class="fa fa-history mr-2"></i> Historial de Traslados</a>
        </li>
        @if($mostrarPaperland)
        <li class="nav-item">
            <a class="nav-link" id="historial-paperland-link" data-toggle="tab" href="#historial-paperland"
               role="tab" aria-controls="historial-paperland" aria-selected="false"><i class="fa fa-exchange mr-2"></i> Historial de Traslados de Paperland</a>
        </li>
        @endif
    </ul>
    <div class="tab-content">
    <div class="tab-pane show active" id="historial-normal" role="tabpanel" aria-labelledby="historial-normal-link">
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3><i class="fa fa-calendar mr-2"></i> Seleccionar rango de fechas</h3>
                    </div>
                    <div class="ibox-content ">

                        <div class="row align-items-end">
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="fechaInicio" class="col-form-label focus-label">Fecha de inicio</label>
                                <input id="fechaInicio" type="date" value="{{ $fechaInicio }}" class="form-control mb-3 mb-md-0">
                            </div>

                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="fechaFinal" class="col-form-label focus-label">Fecha Final</label>
                                <input id="fechaFinal" type="date" value="{{ date('Y-m-t') }}" class="form-control mb-3 mb-md-0">
                            </div>

                            <div class="col-12 col-md-4">
                                <button onclick="ajustesPorfecha()" class="btn btn-primary"><i
                                        class="fa-solid fa-arrow-rotate-right mr-1"></i> Solicitar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <ul class="nav nav-tabs" id="historialTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-traslado-link"
                                   data-toggle="tab" href="#pane-traslado" role="tab">
                                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Por Traslado
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-producto-link"
                                   data-toggle="tab" href="#pane-producto" role="tab">
                                    <i class="fa-solid fa-box mr-1"></i> Por Producto
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="ibox-content">
                        <div class="tab-content mt-2">

                            {{-- Tab 1: Por Traslado --}}
                            <div class="tab-pane fade show active" id="pane-traslado" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="tbl_por_traslado"
                                           class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° Traslado</th>
                                                <th>Comentario / Motivo</th>
                                                <th>Usuario</th>
                                                <th>Fecha</th>
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab 2: Por Producto --}}
                            <div class="tab-pane fade" id="pane-producto" role="tabpanel">
                                <div class="row mb-3 align-items-end">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <label class="col-form-label">Buscar Producto</label>
                                        <input type="text" id="filtro_q" class="form-control"
                                               placeholder="Ej: bolsa concept (palabras separadas)">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <label class="col-form-label">N° Traslado</label>
                                        <input type="text" id="filtro_num_traslado" class="form-control"
                                               placeholder="Ej: 2026-5">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <label class="col-form-label">Bodega Origen</label>
                                        <select id="filtro_bodega_origen" class="form-control">
                                            <option value="">-- Todas --</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <label class="col-form-label">Bodega Destino</label>
                                        <select id="filtro_bodega_destino" class="form-control">
                                            <option value="">-- Todas --</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3" style="padding-top:28px; display:flex; gap:6px;">
                                        <button onclick="aplicarFiltrosProducto()" class="btn btn-info btn-sm">
                                            <i class="fa fa-filter"></i> Filtrar
                                        </button>
                                        <button onclick="limpiarFiltrosProducto()" class="btn btn-default btn-sm">
                                            <i class="fa fa-times"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="tbl_por_producto"
                                           class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° Traslado</th>
                                                <th>ID Producto</th>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Origen (Bodega / Segmento / Sección)</th>
                                                <th>Destino (Bodega / Segmento / Sección)</th>
                                                <th>Usuario</th>
                                                <th>Fecha</th>
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}
                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>
    </div>





    </div>
    @if($mostrarPaperland)
    <div class="tab-pane" id="historial-paperland" role="tabpanel" aria-labelledby="historial-paperland-link">
        @include('pos-integration.history')
    </div>
    @endif
    </div>

    @push('scripts')
    <script>
        var fechaInicio         = "{{ $fechaInicio }}";
        var fechaFinal          = "{{ date('Y-m-t') }}";
        var tblTraslado         = null;
        var tblProducto         = null;
        var filtroQ             = '';
        var filtroNumTraslado   = '';
        var filtroBodegaOrigen  = '';
        var filtroBodegaDestino = '';

        $(document).ready(function () {
            cargarBodegas();
            tablaTraslado();
        });

        $('#tab-producto-link').on('shown.bs.tab', function () {
            if (!tblProducto) tablaProducto();
        });
        $('#historial-normal-link').on('shown.bs.tab', function () {
            [tblTraslado, tblProducto].forEach(function (table) {
                if (!table) return;
                table.columns.adjust();
                if (table.responsive) table.responsive.recalc();
            });
        });

        function cargarBodegas() {
            axios.get('/translados/bodegas').then(function (r) {
                var opts = '<option value="">-- Todas --<\/option>';
                r.data.forEach(function (b) {
                    opts += '<option value="' + b.id + '">' + b.nombre + '<\/option>';
                });
                document.getElementById('filtro_bodega_origen').innerHTML  = opts;
                document.getElementById('filtro_bodega_destino').innerHTML = opts;
            }).catch(function () {});
        }

        function tablaTraslado() {
            if ($.fn.DataTable.isDataTable('#tbl_por_traslado')) {
                $('#tbl_por_traslado').DataTable().clear().destroy();
            }
            tblTraslado = $('#tbl_por_traslado').DataTable({
                order: [[3, 'desc']],
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
                pageLength: 15,
                responsive: true,
                ajax: {
                    url: '/translados/obtener/por-traslado',
                    type: 'POST',
                    data: function (d) {
                        d.fechaInicio = fechaInicio;
                        d.fechaFinal  = fechaFinal;
                        d._token      = "{{ csrf_token() }}";
                    },
                    error: function (xhr) {
                        console.error('Error tblTraslado:', xhr.status, xhr.responseText);
                    }
                },
                columns: [
                    { data: 'codigo' },
                    { data: 'comentario' },
                    { data: 'name' },
                    { data: 'fecha' },
                    { data: 'opciones', orderable: false, searchable: false },
                ]
            });
        }

        function tablaProducto() {
            if ($.fn.DataTable.isDataTable('#tbl_por_producto')) {
                $('#tbl_por_producto').DataTable().clear().destroy();
            }
            tblProducto = $('#tbl_por_producto').DataTable({
                order: [[7, 'desc']],
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
                pageLength: 5,
                dom: 'rtip',
                responsive: true,
                ajax: {
                    url: '/translados/obtener/listado',
                    type: 'POST',
                    data: function (d) {
                        d.fechaInicio       = fechaInicio;
                        d.fechaFinal        = fechaFinal;
                        d.q                 = filtroQ;
                        d.num_traslado      = filtroNumTraslado;
                        d.bodega_origen_id  = filtroBodegaOrigen;
                        d.bodega_destino_id = filtroBodegaDestino;
                        d._token            = "{{ csrf_token() }}";
                    },
                    error: function (xhr) {
                        console.error('Error tblProducto:', xhr.status, xhr.responseText);
                    }
                },
                columns: [
                    { data: 'num_traslado' },
                    { data: 'id_producto' },
                    { data: 'nombre' },
                    { data: 'cantidad' },
                    { data: 'origen_completo' },
                    { data: 'destino_completo' },
                    { data: 'name' },
                    { data: 'created_at' },
                    { data: 'opciones', orderable: false, searchable: false },
                ]
            });
        }

        function ajustesPorfecha() {
            var inicio = document.getElementById('fechaInicio').value;
            var fin    = document.getElementById('fechaFinal').value;
            if (!inicio || !fin) { alert('Seleccione un rango de fechas válido.'); return; }
            fechaInicio = inicio;
            fechaFinal  = fin;
            tablaTraslado();
            if (tblProducto) { tblProducto = null; tablaProducto(); }
        }

        function aplicarFiltrosProducto() {
            filtroQ             = document.getElementById('filtro_q').value.trim();
            filtroNumTraslado   = document.getElementById('filtro_num_traslado').value.trim();
            filtroBodegaOrigen  = document.getElementById('filtro_bodega_origen').value;
            filtroBodegaDestino = document.getElementById('filtro_bodega_destino').value;
            tblProducto = null;
            tablaProducto();
        }

        function limpiarFiltrosProducto() {
            document.getElementById('filtro_q').value              = '';
            document.getElementById('filtro_num_traslado').value   = '';
            document.getElementById('filtro_bodega_origen').value  = '';
            document.getElementById('filtro_bodega_destino').value = '';
            filtroQ = ''; filtroNumTraslado = ''; filtroBodegaOrigen = ''; filtroBodegaDestino = '';
            tblProducto = null;
            tablaProducto();
        }
    </script>
    @endpush
</div>
