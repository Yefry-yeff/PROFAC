<div>
    @push('styles')
    <style>
    :root { --cdc-grad: linear-gradient(135deg,#f39c12 0%,#e05a00 100%); --cdc-accent:#e67e22; --cdc-radius:8px; --cdc-shadow:0 2px 8px rgba(0,0,0,.10); }
    .cdc-card { border:1px solid #e8d5bf; border-radius:var(--cdc-radius); box-shadow:var(--cdc-shadow); background:#fff; overflow:visible; }
    .cdc-card-header { background:var(--cdc-grad); padding:12px 20px; border-radius:var(--cdc-radius) var(--cdc-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .cdc-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .cdc-card-body { padding:16px 20px; }
    .btn-cdc-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-cdc-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    .btn-cdc-action.active { background:#fff!important; color:var(--cdc-accent)!important; }
    .btn-cdc-primary { background:var(--cdc-grad)!important; color:#fff!important; border:none!important; font-weight:600; padding:6px 20px; border-radius:5px; font-size:.85rem; }
    .btn-cdc-primary:hover { color:#fff!important; opacity:.92; }
    .btn-cdc-primary:disabled { opacity:.5; cursor:not-allowed; }
    .cdc-filtros-bar { padding:12px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:end; gap:10px; }
    .cdc-filtros-bar .form-group { margin-bottom:0; min-width:190px; flex:1; }
    .cdc-filtros-bar label { font-size:.72rem; font-weight:700; color:#7d3f00; text-transform:uppercase; letter-spacing:.03em; margin-bottom:3px; }
    .cdc-seleccion-bar { padding:8px 16px; background:#fff8ee; border-bottom:1px solid #f2d49a; display:none; align-items:center; gap:12px; flex-wrap:wrap; }
    .cdc-seleccion-bar.show { display:flex; }
    .cdc-seleccion-count { font-size:.85rem; font-weight:700; color:#7d3f00; }
    #tbl_cdc thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.03em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_cdc tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_cdc tbody tr:hover>td { background:#fffcf5; }
    .cdc-chip { display:inline-block; padding:2px 9px; border-radius:12px; font-size:.72rem; font-weight:600; margin:1px 3px 1px 0; }
    .cdc-chip-asesor { background:#fdebd0; color:#8a5000; }
    .cdc-chip-teleasesor { background:#e3f2ff; color:#0d5da6; }
    .btn-cdc-accion { background:none; border:none; color:#7d3f00; font-size:.95rem; padding:2px 6px; cursor:pointer; }
    .btn-cdc-accion:hover { color:var(--cdc-accent); }
    .cdc-group { border:1px solid #e8d5bf; border-radius:6px; margin-bottom:10px; overflow:hidden; }
    .cdc-group-header { background:#fdf4e7; padding:10px 16px; display:flex; align-items:center; justify-content:between; gap:10px; cursor:pointer; }
    .cdc-group-header:hover { background:#fbe9d0; }
    .cdc-group-title { font-weight:700; color:#7d3f00; font-size:.9rem; flex:1; }
    .cdc-group-badge { background:var(--cdc-grad); color:#fff; border-radius:12px; padding:2px 12px; font-size:.75rem; font-weight:700; }
    .cdc-group-chevron { transition:transform .2s; color:#7d3f00; }
    .cdc-group.open .cdc-group-chevron { transform:rotate(90deg); }
    .cdc-group-body { display:none; padding:8px 16px 14px; }
    .cdc-group.open .cdc-group-body { display:block; }
    .cdc-mini-row { display:flex; align-items:center; gap:10px; padding:8px 6px; border-bottom:1px solid #f3ecdf; font-size:.83rem; flex-wrap:wrap; }
    .cdc-mini-row:last-child { border-bottom:none; }
    .cdc-mini-nombre { font-weight:600; min-width:200px; flex:1; }
    .modal-header-cdc { background:var(--cdc-grad); color:#fff; border-radius:var(--cdc-radius) var(--cdc-radius) 0 0; padding:14px 20px; }
    .modal-header-cdc .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-cdc .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-cdc .close:hover { opacity:1; }
    .cdc-modal-section { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--cdc-accent); border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:12px; margin-top:10px; }
    .cdc-tipo-box { background:#fdfaf6; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px; margin-bottom:14px; }
    .cdc-tipo-box .form-check-inline { margin-right:14px; }
    .cdc-buscar-agregar { display:flex; gap:8px; align-items:center; margin-bottom:12px; }
    .cdc-buscar-agregar .select2-container { flex:1 1 auto; min-width:0; width:100% !important; }
    .cdc-buscar-agregar .btn { flex:0 0 auto; white-space:nowrap; }
    .cdc-lista-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
    .cdc-lista-header span { font-size:.72rem; font-weight:700; color:#7d3f00; text-transform:uppercase; letter-spacing:.03em; }
    .cdc-lista-asignados { display:flex; flex-wrap:wrap; gap:6px; min-height:36px; padding:6px 2px; }
    .cdc-lista-asignados .cdc-vacio { color:#a89686; font-size:.8rem; font-style:italic; padding:6px 4px; }
    .cdc-chip-editable { display:inline-flex; align-items:center; gap:7px; padding:4px 6px 4px 12px; font-size:.78rem; }
    .cdc-chip-remove-icon { cursor:pointer; opacity:.65; font-size:.7rem; padding:3px; }
    .cdc-chip-remove-icon:hover { opacity:1; }
    #tbl_cdc_historial thead th, #tbl_cdc_historial_masivo thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; text-transform:uppercase; }
    .badge-cdc-insert { background:#d4edda; color:#155724; }
    .badge-cdc-delete { background:#f8d7da; color:#721c24; }
    .select2-container--open { z-index:99999!important; }
    .swal2-container { z-index:99999!important; }
    #modalAsignacionCdc .modal-body, #modalAsignacionMasivaCdc .modal-body { max-height:calc(100vh - 210px); overflow-y:auto; }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-address-book mr-2" style="color:#e67e22"></i>Cartera de Clientes</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Flujo de Venta</li>
                <li class="breadcrumb-item active"><strong>Cartera de Clientes</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="cdc-card">

                    <div class="cdc-card-header">
                        <h5><i class="fa fa-address-book"></i> Cartera de Clientes</h5>
                        <div class="d-flex" style="gap:6px">
                            <button type="button" class="btn-cdc-action active" id="btn_vista_individual" onclick="cdcCambiarVista('individual')">
                                <i class="fa fa-list mr-1"></i>Individual
                            </button>
                            <button type="button" class="btn-cdc-action" id="btn_vista_municipio" onclick="cdcCambiarVista('municipio')">
                                <i class="fa fa-map-marker mr-1"></i>Por Municipio
                            </button>
                            <button type="button" class="btn-cdc-action" id="btn_vista_departamento" onclick="cdcCambiarVista('departamento')">
                                <i class="fa fa-globe mr-1"></i>Por Departamento
                            </button>
                        </div>
                    </div>

                    <div class="cdc-filtros-bar">
                        <div class="form-group">
                            <label>Nombre del cliente</label>
                            <input type="text" id="cdc_fil_nombre" class="form-control form-control-sm" placeholder="Buscar por nombre...">
                        </div>
                        <div class="form-group">
                            <label>Asesor Comercial</label>
                            <select id="cdc_fil_asesor" class="form-control form-control-sm" style="width:100%"></select>
                        </div>
                        <div class="form-group">
                            <label>Tele Asesor</label>
                            <select id="cdc_fil_teleasesor" class="form-control form-control-sm" style="width:100%"></select>
                        </div>
                        <div class="form-group" style="min-width:140px;flex:0 0 140px;">
                            <label>Estado</label>
                            <select id="cdc_fil_estado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="1">Activo</option>
                                <option value="2">Inactivo</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width:170px;flex:0 0 170px;">
                            <label>&nbsp;</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="cdc_fil_sin_asignar">
                                <label class="custom-control-label" for="cdc_fil_sin_asignar" style="font-size:.78rem;font-weight:500;text-transform:none;">Solo sin asignar</label>
                            </div>
                        </div>
                        <div class="form-group" style="flex:0 0 auto;">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAplicarFiltros()"><i class="fa fa-search mr-1"></i>Buscar</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcLimpiarFiltros()"><i class="fa fa-eraser mr-1"></i>Limpiar</button>
                            </div>
                        </div>
                    </div>

                    <div class="cdc-seleccion-bar" id="cdc_seleccion_bar">
                        <span class="cdc-seleccion-count"><i class="fa fa-check-square mr-1"></i><span id="cdc_seleccion_count">0</span> cliente(s) seleccionado(s)</span>
                        <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAbrirAsignacionMasiva()"><i class="fa fa-user-tag mr-1"></i>Asignar en lote</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcAbrirHistorialMasivo()"><i class="fa fa-history mr-1"></i>Ver historial</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcLimpiarSeleccion()"><i class="fa fa-times mr-1"></i>Quitar selección</button>
                    </div>

                    <div class="cdc-card-body">

                        {{-- Vista Individual --}}
                        <div id="cdc_vista_individual">
                            <div style="overflow-x:auto;">
                                <table id="tbl_cdc" class="table table-hover table-bordered" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th style="width:30px;"><input type="checkbox" id="cdc_chk_all"></th>
                                            <th>Cliente</th>
                                            <th>Ubicación</th>
                                            <th>Asesores Comerciales</th>
                                            <th>Teleasesores</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Vista Agrupada (Municipio/Departamento) --}}
                        <div id="cdc_vista_agrupada" style="display:none;">
                            <div id="cdc_grupos"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Asignación Individual ══ --}}
    <div class="modal fade" id="modalAsignacionCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-user-tag mr-2"></i>Editar Asignación — <span id="cdc_asig_nombre_cliente"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cdc_asig_cliente_id">

                    <p class="cdc-modal-section"><i class="fa fa-briefcase mr-1"></i>Asesores Comerciales</p>
                    <div class="cdc-tipo-box">
                        <div class="cdc-buscar-agregar">
                            <select id="cdc_asig_buscar_asesores" class="form-control"></select>
                            <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAgregarUsuario('asesores')"><i class="fa fa-plus mr-1"></i>Agregar</button>
                        </div>
                        <div class="cdc-lista-header">
                            <span>Usuarios asignados</span>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0" onclick="cdcEliminarTodos('asesores')"><i class="fa fa-trash mr-1"></i>Eliminar todos</button>
                        </div>
                        <div id="cdc_asig_lista_asesores" class="cdc-lista-asignados"></div>
                    </div>

                    <p class="cdc-modal-section"><i class="fa fa-headset mr-1"></i>Teleasesores</p>
                    <div class="cdc-tipo-box">
                        <div class="cdc-buscar-agregar">
                            <select id="cdc_asig_buscar_teleasesores" class="form-control"></select>
                            <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAgregarUsuario('teleasesores')"><i class="fa fa-plus mr-1"></i>Agregar</button>
                        </div>
                        <div class="cdc-lista-header">
                            <span>Usuarios asignados</span>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0" onclick="cdcEliminarTodos('teleasesores')"><i class="fa fa-trash mr-1"></i>Eliminar todos</button>
                        </div>
                        <div id="cdc_asig_lista_teleasesores" class="cdc-lista-asignados"></div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcGuardarAsignacion()"><i class="fa fa-save mr-1"></i>Guardar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Asignación Masiva ══ --}}
    <div class="modal fade" id="modalAsignacionMasivaCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-users mr-2"></i>Asignación Masiva (<span id="cdc_asig_masiva_count">0</span> clientes)</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">

                    <p class="cdc-modal-section"><i class="fa fa-briefcase mr-1"></i>Asesores Comerciales</p>
                    <div class="cdc-tipo-box">
                        <div class="form-group">
                            <select id="cdc_masiva_asesores" class="form-control" multiple style="width:100%"></select>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_asesores" id="cdc_masiva_modo_asesores_sin" value="sin_cambios" checked>
                            <label class="form-check-label" for="cdc_masiva_modo_asesores_sin">No modificar</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_asesores" id="cdc_masiva_modo_asesores_agregar" value="agregar">
                            <label class="form-check-label" for="cdc_masiva_modo_asesores_agregar">Agregar a los actuales</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_asesores" id="cdc_masiva_modo_asesores_reemplazar" value="reemplazar">
                            <label class="form-check-label" for="cdc_masiva_modo_asesores_reemplazar">Reemplazar por estos</label>
                        </div>
                    </div>

                    <p class="cdc-modal-section"><i class="fa fa-headset mr-1"></i>Teleasesores</p>
                    <div class="cdc-tipo-box">
                        <div class="form-group">
                            <select id="cdc_masiva_teleasesores" class="form-control" multiple style="width:100%"></select>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_teleasesores" id="cdc_masiva_modo_tele_sin" value="sin_cambios" checked>
                            <label class="form-check-label" for="cdc_masiva_modo_tele_sin">No modificar</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_teleasesores" id="cdc_masiva_modo_tele_agregar" value="agregar">
                            <label class="form-check-label" for="cdc_masiva_modo_tele_agregar">Agregar a los actuales</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_teleasesores" id="cdc_masiva_modo_tele_reemplazar" value="reemplazar">
                            <label class="form-check-label" for="cdc_masiva_modo_tele_reemplazar">Reemplazar por estos</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcGuardarAsignacionMasiva()"><i class="fa fa-save mr-1"></i>Aplicar a todos</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Historial Individual ══ --}}
    <div class="modal fade" id="modalHistorialCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-history mr-2"></i>Historial — <span id="cdc_hist_nombre_cliente"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div style="overflow-x:auto;max-height:60vh;">
                        <table id="tbl_cdc_historial" class="table table-sm table-bordered">
                            <thead>
                                <tr><th>Fecha</th><th>Tipo</th><th>Acción</th><th>Persona</th><th>Realizado por</th><th>Comentario</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Historial Masivo ══ --}}
    <div class="modal fade" id="modalHistorialMasivoCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-history mr-2"></i>Historial de clientes seleccionados</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div style="overflow-x:auto;max-height:60vh;">
                        <table id="tbl_cdc_historial_masivo" class="table table-sm table-bordered">
                            <thead>
                                <tr><th>Fecha</th><th>Cliente</th><th>Tipo</th><th>Acción</th><th>Persona</th><th>Realizado por</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/flujodeventa/cartera-de-clientes.js') }}"></script>
<script>
    window.CDC_ROUTES = {
        listar: '{{ route('cartera_clientes.listar') }}',
        agrupado: '{{ route('cartera_clientes.agrupado') }}',
        usuarios: '{{ route('cartera_clientes.usuarios') }}',
        datos: '{{ url('/flujo_de_venta/cartera_de_clientes/datos') }}',
        historial: '{{ url('/flujo_de_venta/cartera_de_clientes/historial') }}',
        historialMasivo: '{{ route('cartera_clientes.historial_masivo') }}',
        asignar: '{{ route('cartera_clientes.asignar') }}',
        asignarMasivo: '{{ route('cartera_clientes.asignar_masivo') }}',
    };
    $(document).ready(function () {
        cdcInit();
    });
</script>
@endpush

