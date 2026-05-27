<div>
    @push('styles')
    <style>
/* ── Variables PROFAC ─────────────────────────────────────────────── */
:root {
    --pf-grad:       linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
    --pf-grad-hover: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
    --pf-orange:     #e67e22;
    --pf-green:      #27ae60;
    --pf-radius:     8px;
    --pf-shadow:     0 2px 8px rgba(0,0,0,.10);
}

/* ── Card ─────────────────────────────────────────────────────────── */
.cli-card {
    border: 1px solid #e8d5bf;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    overflow: visible;
}
.cli-card-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.cli-card-header h5 {
    margin: 0;
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cli-card-body { padding: 16px 20px; }

/* ── Botones del header ───────────────────────────────────────────── */
.btn-cli-header {
    background: rgba(255,255,255,.18) !important;
    color: #fff !important;
    border: 1.5px solid rgba(255,255,255,.5) !important;
    border-radius: 5px !important;
    font-weight: 600 !important;
    font-size: .78rem;
    padding: 5px 14px;
    transition: background .18s;
    white-space: nowrap;
}
.btn-cli-header:hover {
    background: rgba(255,255,255,.30) !important;
    color: #fff !important;
    text-decoration: none;
}

/* ── Stat pills ───────────────────────────────────────────────────── */
.cli-stats { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.cli-stat-pill {
    display: flex;
    align-items: center;
    gap: 7px;
    background: #fdf6ee;
    border: 1px solid #e8d5bf;
    border-radius: 20px;
    padding: 4px 14px 4px 10px;
    font-size: .78rem;
    color: #555;
    font-weight: 500;
}
.cli-stat-pill .stat-num { font-size: .9rem; font-weight: 700; color: var(--pf-orange); }
.cli-stat-pill.green { background: #f0fdf4; border-color: #bbf7d0; }
.cli-stat-pill.green .stat-num { color: #1a7a4e; }
.cli-stat-pill.red   { background: #fef2f2; border-color: #fecaca; }
.cli-stat-pill.red   .stat-num { color: #b91c1c; }

/* ── Tabla ────────────────────────────────────────────────────────── */
#tbl_ClientesLista { width: 100% !important; }
#tbl_ClientesLista thead th {
    background: #fdf4e7;
    color: #7d3f00;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    border-bottom: 2px solid #f2d49a;
    white-space: nowrap;
    padding: 8px 10px;
    vertical-align: middle;
}
#tbl_ClientesLista tbody td { font-size: .83rem; vertical-align: middle; padding: 7px 10px; }
#tbl_ClientesLista tbody tr:hover { background: #fffcf5; }

/* ── Badge estado ─────────────────────────────────────────────────── */
.badge-activo   { background:#dcfce7; color:#14532d; border:1px solid #86efac; font-weight:600; }
.badge-inactivo { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; font-weight:600; }

/* ── Dropdown acciones ────────────────────────────────────────────── */
.cli-dropdown { position: relative; }
.btn-cli-menu {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #fff;
    border: 1.5px solid #e0cbb0;
    border-radius: 7px;
    color: #c0622a;
    font-size: .88rem;
    cursor: pointer;
    transition: background .15s, border-color .15s, box-shadow .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}
.btn-cli-menu:hover, .btn-cli-menu:focus {
    background: #fff8f0;
    border-color: #e67e22;
    box-shadow: 0 2px 6px rgba(230,126,34,.25);
    outline: none;
}
.cli-dropdown .dropdown-menu {
    min-width: 170px;
    border: 1px solid #f0e0cc;
    border-radius: 8px;
    padding: 4px 0;
    font-size: .83rem;
    box-shadow: 0 4px 16px rgba(0,0,0,.13) !important;
}
.cli-dropdown .dropdown-item { padding: 7px 14px; font-weight: 500; transition: background .12s; }
.cli-dropdown .dropdown-item:hover { background: #fff8f0; color: #c0622a; }
.cli-dropdown .dropdown-item i { opacity: .85; }

/* ── Modal header ─────────────────────────────────────────────────── */
.modal-header-cli {
    background: var(--pf-grad);
    color: #fff;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
}
.modal-header-cli .modal-title { color: #fff; font-size: .95rem; }
.modal-header-cli .close       { color: #fff; opacity: .8; text-shadow: none; }
.modal-header-cli .close:hover { opacity: 1; }

/* ── Secciones del modal crear/editar ────────────────────────────── */
.modal-sec-title {
    background: #fdf4e7;
    border-left: 3px solid #e67e22;
    padding: 5px 10px;
    font-size: .74rem;
    font-weight: 700;
    color: #7d3f00;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-radius: 0 4px 4px 0;
    margin-bottom: 2px;
}

/* ── Tabs del modal crear ─────────────────────────────────────────── */
.nav-cli-crear { border-bottom: 2px solid #e8d5bf; margin-bottom: 0; }
.nav-cli-crear .nav-link { color:#7d3f00; font-size:.8rem; font-weight:600; padding:7px 16px; border-radius:6px 6px 0 0; border:1px solid transparent; transition:background .15s,color .15s; }
.nav-cli-crear .nav-link.active { background:#fdf4e7; border-color:#e8d5bf #e8d5bf #fdf4e7; color:#e67e22; }
.nav-cli-crear .nav-link:hover:not(.active) { background:#fff8f0; border-color:#e8d5bf #e8d5bf transparent; color:#c0622a; }
.tab-err-badge { display:inline-block; background:#dc3545; color:#fff; border-radius:50%; font-size:.63rem; width:15px; height:15px; line-height:15px; text-align:center; margin-left:4px; vertical-align:middle; font-weight:700; }

/* ── Select2 ──────────────────────────────────────────────────────── */
.select2-container .select2-dropdown { z-index: 2055 !important; }
.select2-dropdown { z-index: 3050 !important; max-height: 200px; overflow-y: auto; scroll-behavior: smooth; }
.select2-hidden-accessible {
    border: 0 !important; clip: rect(0 0 0 0) !important; height: 1px !important;
    margin: -1px !important; overflow: hidden !important; padding: 0 !important;
    position: absolute !important; width: 1px !important;
}
.select2-container { z-index: 999 !important; width: 100% !important; font-size: 0.9rem; }
.select2-container--bootstrap4 .select2-selection--single {
    height: 38px; padding: 6px 12px; border-radius: 0.35rem; border: 1px solid #ced4da;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 28px; padding-left: 0.5rem; padding-right: 2rem;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
}
.select2-container--bootstrap4 .select2-selection__arrow { height: 34px; right: 8px; }
.select2-container--bootstrap4 .select2-selection__placeholder { color: #6c757d; }

/* ── Imagen previsualización ──────────────────────────────────────── */
.ancho-imagen { max-width: 300px; }
@media (max-width: 600px) { .ancho-imagen { max-width: 200px; } }

/* ── Layout sin doble padding ─────────────────────────────────────── */
#page-wrapper { padding-left: 0 !important; padding-right: 0 !important; }
.wrapper-content { padding-left: 0 !important; padding-right: 0 !important; }
.wrapper-content > .row { margin-left: 0 !important; margin-right: 0 !important; }
.wrapper-content > .row > [class*="col-"] { padding-left: 0 !important; padding-right: 0 !important; }

/* ── DataTables controles ─────────────────────────────────────────── */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter { margin-bottom: 8px; }

/* ── Responsive ocultar columnas ──────────────────────────────────── */
@media (max-width: 767px) {
    #tbl_ClientesLista thead th:nth-child(4),
    #tbl_ClientesLista tbody td:nth-child(4),
    #tbl_ClientesLista thead th:nth-child(6),
    #tbl_ClientesLista tbody td:nth-child(6),
    #tbl_ClientesLista thead th:nth-child(7),
    #tbl_ClientesLista tbody td:nth-child(7)  { display: none; }
    .cli-card-body { padding: 10px; }
}
@media (max-width: 575px) {
    #tbl_ClientesLista thead th:nth-child(9),
    #tbl_ClientesLista tbody td:nth-child(9),
    #tbl_ClientesLista thead th:nth-child(10),
    #tbl_ClientesLista tbody td:nth-child(10) { display: none; }
    .modal-dialog.modal-lg { max-width: calc(100vw - 1rem); }
    .wrapper-content { padding: 10px 8px !important; }
}
    </style>

/* Placeholder gris más suave */
.select2-container--bootstrap4 .select2-selection__placeholder {
    color: #6c757d;
}
          </style>






    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa fa-users mr-2" style="color:#e67e22"></i>Clientes</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Clientes</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12">
                <div class="cli-card">

                    <div class="cli-card-header">
                        <h5><i class="fa fa-users"></i> Clientes</h5>
                        <div class="d-flex" style="gap:8px;flex-wrap:wrap">
                            <a href="/cliente/excel" class="btn btn-cli-header">
                                <i class="fa fa-file-excel-o mr-1"></i> Exportar Excel
                            </a>
                            <button type="button" class="btn btn-cli-header" data-toggle="modal" data-target="#modal_clientes_crear">
                                <i class="fa fa-plus mr-1"></i> Nuevo Cliente
                            </button>
                        </div>
                    </div>

                    <div class="cli-card-body">

                        <div class="cli-stats">
                            <div class="cli-stat-pill">
                                <i class="fa fa-users" style="font-size:.78rem;color:var(--pf-orange)"></i>
                                <span>Total</span>
                                <span class="stat-num" id="cli-stat-total">-</span>
                            </div>
                            <div class="cli-stat-pill green">
                                <i class="fa fa-check-circle" style="font-size:.78rem;color:#1a7a4e"></i>
                                <span>Activos</span>
                                <span class="stat-num" id="cli-stat-activos">-</span>
                            </div>
                            <div class="cli-stat-pill red">
                                <i class="fa fa-times-circle" style="font-size:.78rem;color:#b91c1c"></i>
                                <span>Inactivos</span>
                                <span class="stat-num" id="cli-stat-inactivos">-</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tbl_ClientesLista" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:60px">Código</th>
                                        <th>Categoría Precio</th>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th style="width:110px">Teléfono</th>
                                        <th>Correo</th>
                                        <th style="width:130px">RTN</th>
                                        <th style="width:85px" class="text-center">Estado</th>
                                        <th>Registrado Por</th>
                                        <th style="width:95px" class="text-center">Fecha</th>
                                        <th style="width:70px" class="text-center">Acciones</th>
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


        <!---MODAL PARA CREAR CLIENTES----->
        <div id="modal_clientes_crear" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-cli">
                        <h5 class="modal-title"><i class="fa fa-user-plus mr-2"></i>Registro de Cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="clientesCreacionForm" name="clientesCreacionForm" novalidate>
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                            <input type="hidden" name="credito" value="0">
                            <input type="hidden" name="dias_credito" value="0">

                            <!-- ── Pestañas ──────────────────────────────── -->
                            <ul class="nav nav-cli-crear mb-0" id="tabsCrearCliente" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-crear-datos-tab" data-toggle="tab"
                                        href="#tab-crear-datos" role="tab">
                                        <i class="fa fa-user mr-1"></i>Datos
                                        <span class="tab-err-badge d-none" id="badge-tab-crear-datos">!</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-crear-contacto-tab" data-toggle="tab"
                                        href="#tab-crear-contacto" role="tab">
                                        <i class="fa fa-phone mr-1"></i>Contacto
                                        <span class="tab-err-badge d-none" id="badge-tab-crear-contacto">!</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-crear-ubicacion-tab" data-toggle="tab"
                                        href="#tab-crear-ubicacion" role="tab">
                                        <i class="fa fa-map-marker mr-1"></i>Ubicación
                                        <span class="tab-err-badge d-none" id="badge-tab-crear-ubicacion">!</span>
                                    </a>
                                </li>
                            </ul>

                            <!-- ── Contenido de pestañas ──────────────────── -->
                            <div class="tab-content border border-top-0 rounded-bottom p-3 mb-2">

                                <!-- TAB 1: Datos del Cliente -->
                                <div class="tab-pane fade show active" id="tab-crear-datos" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Categoría / Escala de precios <span class="text-danger">*</span></label>
                                                <select class="form-control"
                                                        id="cliente_categoria_escala_id_crear"
                                                        name="cliente_categoria_escala_id_crear"
                                                        data-url="{{ route('clientes.categorias.escala') }}">
                                                    <option value="" selected disabled>--- Seleccione una categoría ---</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Nombre del cliente <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" id="nombre_cliente" name="nombre_cliente" maxlength="60">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">RTN <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" name="rtn_cliente" id="rtn_cliente" maxlength="14" placeholder="14 dígitos">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Tipo de Personalidad <span class="text-danger">*</span></label>
                                                <select class="form-control" name="tipo_personalidad" id="tipo_personalidad">
                                                    <option disabled selected>---Seleccione---</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Tipo de cliente <span class="text-danger">*</span></label>
                                                <select class="form-control" name="categoria_cliente" id="categoria_cliente">
                                                    <option selected disabled>---Seleccione---</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Vendedor <span class="text-danger">*</span></label>
                                                <select class="form-control" name="vendedor_cliente" id="vendedor_cliente">
                                                    <option selected disabled>---Seleccione---</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>{{-- /tab-crear-datos --}}

                                <!-- TAB 2: Contacto -->
                                <div class="tab-pane fade" id="tab-crear-contacto" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Correo electrónico <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" name="correo_cliente" id="correo_cliente">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Teléfono del cliente <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" name="telefono_cliente" id="telefono_cliente">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Nombre de contácto 1 <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" id="contacto[]" name="contacto[]">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Teléfono contacto 1 <span class="text-danger">*</span></label>
                                                <input class="form-control" type="text" name="telefono[]" id="telefono[]">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Nombre de contácto 2 <small class="text-muted">(opcional)</small></label>
                                                <input class="form-control" type="text" id="contacto[]" name="contacto[]">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Teléfono contacto 2 <small class="text-muted">(opcional)</small></label>
                                                <input class="form-control" type="text" name="telefono[]" id="telefono[]">
                                            </div>
                                        </div>
                                    </div>
                                </div>{{-- /tab-crear-contacto --}}

                                <!-- TAB 3: Ubicación -->
                                <div class="tab-pane fade" id="tab-crear-ubicacion" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">País <span class="text-danger">*</span></label>
                                                <select class="form-control" name="pais_cliente" id="pais_cliente" onchange="obtenerDepartamentos()">
                                                    <option selected disabled>---Seleccione un país---</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Departamento <span class="text-danger">*</span></label>
                                                <select class="form-control" name="departamento_cliente" id="departamento_cliente" onchange="obtenerMunicipios()">
                                                    <option selected disabled>---Seleccione un departamento---</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Municipio <span class="text-danger">*</span></label>
                                                <select class="form-control" name="municipio_cliente" id="municipio_cliente">
                                                    <option selected disabled>---Seleccione un municipio---</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Dirección <span class="text-danger">*</span></label>
                                                <textarea name="direccion_cliente" placeholder="Escriba aquí..." id="direccion_cliente"
                                                    cols="30" rows="2" class="form-control" maxlength="142"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Latitud</label>
                                                <input class="form-control" type="text" name="latitud_cliente" id="latitud_clientee">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="col-form-label focus-label">Longitud</label>
                                                <input class="form-control" type="text" name="longitud_cliente" id="longitud_cliente">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group mb-2">
                                                <label for="foto_cliente" class="col-form-label focus-label">Fotografía:</label>
                                                <input type="file" id="foto_cliente" name="foto_cliente" class="form-control-file"
                                                    accept="image/png, image/gif, image/jpeg, image/jpg">
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <img id="imagenPrevisualizacion" class="ancho-imagen">
                                        </div>
                                    </div>
                                </div>{{-- /tab-crear-ubicacion --}}

                            </div>{{-- /tab-content --}}
                        </form>
                        <button id="btn_crear_cliente" type="button" class="btn btn-sm btn-primary mt-2" onclick="registrarCliente()">
                            <i class="fa fa-check mr-1"></i><strong>Crear Cliente</strong>
                        </button>
                    </div>
                    </div>

                </div>
            </div>
        </div>

        <!---MODAL PARA EDITAR CLIENTES----->
        <div id="modal_clientes_editar" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-cli">
                        <h5 class="modal-title"><i class="fa fa-pencil mr-2"></i>Editar datos del Cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="clientesCreacionForm_editar" name="clientesCreacionForm" data-parsley-validate>

                            <div class="row" id="row_datos">
                                <input id="idCliente" name="idCliente" type="hidden" >

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">
                                        Categoría de cliente / Escala de precios
                                    </label>

                                    <select class="form-control" id="categoria_cliente_escala_editar"
                                            name="categoria_cliente_escala_editar">
                                        <!-- Lo llenamos por JS -->
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Nombre del cliente</label>
                                    <input class="form-control" required type="text" id="nombre_cliente_editar" name="nombre_cliente_editar"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Dirección</label>
                                    <textarea name="direccion_cliente_editar" placeholder="Escriba aquí..." required id="direccion_cliente_editar" cols="30" rows="3"
                                        class="form-group form-control" data-parsley-required maxlength="142"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Credito Inicial</label>
                                    <input  id="credito_inicial_editar" name="credito_inicial_editar" type="number" step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Credito Disponible</label>
                                    <input  id="credito_editar" name="credito_editar" type="number" step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label" for="dias_credito_editar">Dias de credito<span class="text-danger">*</span></label>
                                    <input   id="dias_credito_editar" name="dias_credito_editar" type="number"  min="0" max="120" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">RTN</label>
                                    <input class="form-group form-control" required type="text" name="rtn_cliente_editar"
                                        id="rtn_cliente_editar" data-parsley-required pattern="[0-9]{14}">
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Correo electrónico</label>
                                    <input class="form-group form-control" type="text" name="correo_cliente_editar" id="correo_cliente_editar"
                                        data-parsley-required>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Teléfono del cliente</label>
                                    <input class="form-group form-control" type="text" name="telefono_cliente_editar" id="telefono_cliente_editar"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 1</label>
                                    <input class="form-control" required type="text" id="contacto_1_editar"
                                        name="contacto_1_editar" data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 1</label>
                                    <input class="form-group form-control" required type="text" name="telefono_1_editar"
                                        id="telefono_1_editar" data-parsley-required pattern="[0-9]{8}">
                                </div>

                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 2</label>
                                    <input class="form-control"  type="text" id="contacto_2_editar"
                                        name="contacto_2_editar" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 2</label>
                                    <input class="form-group form-control"  type="text" name="telefono_2_editar"
                                        id="telefono_2_editar" pattern="[0-9]{8}">
                                </div>


                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Longitud</label>
                                    <input class="form-group form-control"  type="text" name="longitud_cliente_editar"
                                        id="longitud_cliente_editar" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Latitud</label>
                                    <input class="form-group form-control"  type="text" name="latitud_cliente_editar"
                                        id="latitud_cliente_editar" >
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Pais</label>
                                    <select class="form-group form-control" name="pais_cliente_editar" id="pais_cliente_editar"
                                    onchange="obtenerDepartamentosEditar()">
                                        <option selected disabled>---Seleccione un pais---</option>

                                    </select>
                                </div>



                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Departamento</label>
                                    <select class="form-group form-control" name="departamento_cliente_editar" id="departamento_cliente_editar"
                                        onchange="obtenerMunicipiosEditar()">
                                        <option selected disabled>---Seleccione un departamento---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Municipio</label>
                                    <select class="form-group form-control" name="municipio_cliente_editar" id="municipio_cliente_editar"
                                        data-parsley-required >
                                        <option selected disabled>---Seleccione un municipio---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de Personalidad </label>
                                    <select class="form-group form-control" name="tipo_personalidad_editar" id="tipo_personalidad_editar"
                                        data-parsley-required>
                                        <option disabled selected>---Seleccione una opción---</option>


                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de cliente</label>
                                    <select class="form-group form-control" name="categoria_cliente_editar" id="categoria_cliente_editar"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>

                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Vendedor</label>
                                    <select class="form-group form-control" name="vendedor_cliente_editar" id="vendedor_cliente_editar"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>
                                        @foreach ($clientes as $cliente)
                                        <option value="{{$cliente->id}}" >{{$cliente->name}}</option>
                                        @endforeach

                                    </select>
                                </div>

                            </div>
                        </form>

                        <button id="btn_crear_cliente_editar" type="submit" class="btn btn-primary  mt-4"
                            form="clientesCreacionForm_editar"><strong>Editar
                               Cliente</strong></button>
                               <button type="button" class="btn btn-default  mt-4" data-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>

        <!---MODAL PARA EDITAR FOTOGRAFIA----->
        <div id="modal_fotografia_editar" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-cli">
                        <h5 class="modal-title"><i class="fa fa-camera mr-2"></i>Editar fotografía del cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form_img_edit" name="form_img_edit" data-parsley-validate>
                        <input type="hidden" id="clienteId" name="clienteId">

                        <div class="col-md-5">
                            <label for="foto_cliente_editar" class="col-form-label focus-label">Fotografía: </label>
                            <input class="" type="file" id="foto_cliente_editar" name="foto_cliente_editar" accept="image/png, image/gif, image/jpeg, image/jpg" >

                        </div>
                        <div class=" col-md-7 mt-2">
                            <img id="imagenPrevisualizacion_editar" class="ancho-imagen">

                        </div>
                        </form>



                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button id="btn_img_editar" type="submit" class="btn btn-primary  "
                            form="form_img_edit"><strong>
                               Cambiar Imagen</strong></button>
                    </div>



                </div>
            </div>
        </div>

@push('scripts')

    <script src="{{ asset('js/js_proyecto/cliente/cliente.js') }}"></script>
@endpush
